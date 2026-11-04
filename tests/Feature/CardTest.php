<?php

use App\Models\Card;
use App\Models\CardGroup;
use App\Models\Language;
use App\Models\User;

test('guests cannot view the cards page', function () {
    $this->get(route('cards.index'))->assertRedirect(route('login'));
});

test('users can create a card with translations in multiple languages', function () {
    $user = User::factory()->create();
    $english = Language::factory()->create(['code' => 'en']);
    $hungarian = Language::factory()->create(['code' => 'hu']);
    $group = CardGroup::factory()->create();

    $response = $this->actingAs($user)->post(route('cards.store'), [
        'translations' => [
            ['language_id' => $english->id, 'text' => 'dog'],
            ['language_id' => $hungarian->id, 'text' => 'kutya'],
        ],
        'groups' => [$group->id],
    ]);

    $response->assertRedirect(route('cards.index'));

    $card = Card::query()->first();
    expect($card->translations()->count())->toBe(2);
    expect($card->translations()->where('language_id', $english->id)->first()->text)->toBe('dog');
    expect($card->groups()->pluck('card_groups.id'))->toContain($group->id);
});

test('blank translations are not stored', function () {
    $user = User::factory()->create();
    $english = Language::factory()->create(['code' => 'en']);
    $hungarian = Language::factory()->create(['code' => 'hu']);

    $this->actingAs($user)->post(route('cards.store'), [
        'translations' => [
            ['language_id' => $english->id, 'text' => 'dog'],
            ['language_id' => $hungarian->id, 'text' => ''],
        ],
        'groups' => [],
    ]);

    $card = Card::query()->first();
    expect($card->translations()->count())->toBe(1);
});

test('users can update a card and change its groups', function () {
    $user = User::factory()->create();
    $english = Language::factory()->create(['code' => 'en']);
    $firstGroup = CardGroup::factory()->create();
    $secondGroup = CardGroup::factory()->create();

    $card = Card::factory()->create();
    $card->translations()->create(['language_id' => $english->id, 'text' => 'dog']);
    $card->groups()->attach($firstGroup);

    $response = $this->actingAs($user)->put(route('cards.update', $card), [
        'translations' => [
            ['language_id' => $english->id, 'text' => 'cat'],
        ],
        'groups' => [$secondGroup->id],
    ]);

    $response->assertRedirect(route('cards.index'));

    expect($card->translations()->first()->text)->toBe('cat');
    expect($card->groups()->pluck('card_groups.id')->all())->toBe([$secondGroup->id]);
});

test('a card can belong to multiple groups', function () {
    $user = User::factory()->create();
    $firstGroup = CardGroup::factory()->create();
    $secondGroup = CardGroup::factory()->create();
    $card = Card::factory()->create();

    $this->actingAs($user)->put(route('cards.update', $card), [
        'translations' => [],
        'groups' => [$firstGroup->id, $secondGroup->id],
    ]);

    expect($card->groups()->count())->toBe(2);
});

test('the cards index is paginated', function () {
    $user = User::factory()->create();
    Card::factory()->count(25)->create();

    $response = $this->actingAs($user)->get(route('cards.index'));

    $response->assertInertia(fn ($page) => $page
        ->component('cards/Index')
        ->has('cards.data', 20)
        ->where('cards.total', 25)
    );
});

test('the cards index can be searched by translation text', function () {
    $user = User::factory()->create();
    $english = Language::factory()->create(['code' => 'en']);

    $dog = Card::factory()->create();
    $dog->translations()->create(['language_id' => $english->id, 'text' => 'dog']);

    $cat = Card::factory()->create();
    $cat->translations()->create(['language_id' => $english->id, 'text' => 'cat']);

    $response = $this->actingAs($user)->get(route('cards.index', ['search' => 'dog']));

    $response->assertInertia(fn ($page) => $page
        ->component('cards/Index')
        ->has('cards.data', 1)
        ->where('cards.data.0.id', $dog->id)
    );
});

test('users can delete a card', function () {
    $user = User::factory()->create();
    $card = Card::factory()->create();

    $response = $this->actingAs($user)->delete(route('cards.destroy', $card));

    $response->assertRedirect(route('cards.index'));
    expect(Card::query()->find($card->id))->toBeNull();
});
