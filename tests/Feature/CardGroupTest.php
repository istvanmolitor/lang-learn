<?php

use App\Models\Card;
use App\Models\CardGroup;
use App\Models\CardReview;
use App\Models\Language;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('guests cannot view the card groups page', function () {
    $this->get(route('card-groups.index'))->assertRedirect(route('login'));
});

test('the card groups page reports the percentage of cards the user currently knows in each group', function () {
    $user = User::factory()->create();
    $group = CardGroup::factory()->create();

    $known = Card::factory()->create();
    $known->groups()->attach($group);
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $known->id,
        'repetitions' => 1,
    ]);

    $unknown = Card::factory()->create();
    $unknown->groups()->attach($group);

    $response = $this->actingAs($user)->get(route('card-groups.index'));

    $response->assertInertia(fn ($page) => $page
        ->component('card-groups/Index')
        ->where('groups.0.progress', 50)
    );
});

test('users can create a card group', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('card-groups.store'), [
        'name' => 'Animals',
    ]);

    $response->assertRedirect(route('card-groups.index'));
    expect(CardGroup::query()->where('name', 'Animals')->exists())->toBeTrue();
});

test('a card group name is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('card-groups.store'), [
        'name' => '',
    ]);

    $response->assertInvalid(['name']);
});

test('users can rename a card group', function () {
    $user = User::factory()->create();
    $group = CardGroup::factory()->create(['name' => 'Old name']);

    $response = $this->actingAs($user)->put(route('card-groups.update', $group), [
        'name' => 'New name',
    ]);

    $response->assertRedirect(route('card-groups.index'));
    expect($group->refresh()->name)->toBe('New name');
});

test('deleting a card group removes it from its cards without deleting the cards', function () {
    $user = User::factory()->create();
    $group = CardGroup::factory()->create();
    $card = Card::factory()->create();
    $card->groups()->attach($group);

    $response = $this->actingAs($user)->delete(route('card-groups.destroy', $group));

    $response->assertRedirect(route('card-groups.index'));
    expect(CardGroup::query()->find($group->id))->toBeNull();
    expect(Card::query()->find($card->id))->not->toBeNull();
    expect($card->groups()->count())->toBe(0);
});

test('users can export a card group as csv', function () {
    $user = User::factory()->create();
    $english = Language::factory()->create(['code' => 'en']);
    $hungarian = Language::factory()->create(['code' => 'hu']);
    $group = CardGroup::factory()->create();

    $card = Card::factory()->create();
    $card->translations()->create(['language_id' => $english->id, 'text' => 'dog']);
    $card->translations()->create(['language_id' => $hungarian->id, 'text' => 'kutya']);
    $card->groups()->attach($group);

    $response = $this->actingAs($user)->get(route('card-groups.export', $group));

    $response->assertOk();
    expect($response->streamedContent())->toBe("en,hu\ndog,kutya\n");
});

test('users can import cards into a card group from csv', function () {
    $user = User::factory()->create();
    $english = Language::factory()->create(['code' => 'en']);
    $hungarian = Language::factory()->create(['code' => 'hu']);
    $group = CardGroup::factory()->create();

    $file = UploadedFile::fake()->createWithContent(
        'cards.csv',
        "en,hu\ndog,kutya\ncat,macska\n",
    );

    $response = $this->actingAs($user)->post(route('card-groups.import', $group), [
        'file' => $file,
    ]);

    $response->assertRedirect(route('card-groups.edit', $group));
    expect($group->cards()->count())->toBe(2);

    $dog = $group->cards()->whereHas(
        'translations',
        fn ($query) => $query->where('language_id', $english->id)->where('text', 'dog'),
    )->first();

    expect($dog)->not->toBeNull();
    expect($dog->translations()->where('language_id', $hungarian->id)->first()->text)->toBe('kutya');
});
