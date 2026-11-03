<?php

use App\Enums\CardDifficulty;
use App\Models\Card;
use App\Models\CardGroup;
use App\Models\CardReview;
use App\Models\Language;
use App\Models\User;

test('guests cannot view the learn page', function () {
    $group = CardGroup::factory()->create();

    $this->get(route('card-groups.learn', $group))->assertRedirect(route('login'));
});

test('the learn page shows the card text in the languages the user knows and is learning', function () {
    $english = Language::factory()->create(['code' => 'en']);
    $hungarian = Language::factory()->create(['code' => 'hu']);
    $user = User::factory()->create([
        'language_id' => $hungarian->id,
        'selected_language_id' => $english->id,
    ]);

    $group = CardGroup::factory()->create();
    $card = Card::factory()->create();
    $card->translations()->create(['language_id' => $english->id, 'text' => 'dog']);
    $card->translations()->create(['language_id' => $hungarian->id, 'text' => 'kutya']);
    $card->groups()->attach($group);

    $response = $this->actingAs($user)->get(route('card-groups.learn', $group));

    $response->assertInertia(fn ($page) => $page
        ->component('learn/Show')
        ->where('card.id', $card->id)
        ->where('card.front', 'dog')
        ->where('card.frontLanguage', $english->name)
        ->where('card.back', 'kutya')
        ->where('card.backLanguage', $hungarian->name)
    );
});

test('the learn page reports the percentage of cards the user currently knows', function () {
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
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $unknown->id,
        'repetitions' => 0,
    ]);

    $neverReviewed = Card::factory()->create();
    $neverReviewed->groups()->attach($group);

    $response = $this->actingAs($user)->get(route('card-groups.learn', $group));

    $response->assertInertia(fn ($page) => $page
        ->component('learn/Show')
        ->where('progress', 33)
    );
});

test('the learn page reports when a group has no cards', function () {
    $user = User::factory()->create();
    $group = CardGroup::factory()->create();

    $response = $this->actingAs($user)->get(route('card-groups.learn', $group));

    $response->assertInertia(fn ($page) => $page
        ->component('learn/Show')
        ->where('card', null)
    );
});

test('the learn page prioritizes the card that is due soonest', function () {
    $user = User::factory()->create();
    $group = CardGroup::factory()->create();

    $dueCard = Card::factory()->create();
    $dueCard->groups()->attach($group);
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $dueCard->id,
        'due_at' => now()->subDay(),
    ]);

    $notDueCard = Card::factory()->create();
    $notDueCard->groups()->attach($group);
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $notDueCard->id,
        'due_at' => now()->addDays(5),
    ]);

    $response = $this->actingAs($user)->get(route('card-groups.learn', $group));

    $response->assertInertia(fn ($page) => $page
        ->component('learn/Show')
        ->where('card.id', $dueCard->id)
    );
});

test('answering a card records a review and schedules the next due date', function () {
    $user = User::factory()->create();
    $group = CardGroup::factory()->create();
    $card = Card::factory()->create();
    $card->groups()->attach($group);

    $response = $this->actingAs($user)->post(route('card-groups.learn.answer', $group), [
        'card_id' => $card->id,
        'difficulty' => 'easy',
    ]);

    $response->assertRedirect(route('card-groups.learn', $group));

    $review = CardReview::query()->where('card_id', $card->id)->where('user_id', $user->id)->first();

    expect($review)->not->toBeNull();
    expect($review->difficulty)->toBe(CardDifficulty::Easy);
    expect($review->due_at->isAfter(now()))->toBeTrue();
});

test('answering hard resets the interval back to one day', function () {
    $user = User::factory()->create();
    $group = CardGroup::factory()->create();
    $card = Card::factory()->create();
    $card->groups()->attach($group);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'interval_days' => 10,
        'repetitions' => 3,
        'due_at' => now()->subDay(),
    ]);

    $this->actingAs($user)->post(route('card-groups.learn.answer', $group), [
        'card_id' => $card->id,
        'difficulty' => 'hard',
    ]);

    $review = CardReview::query()->where('card_id', $card->id)->latest('id')->first();

    expect($review->interval_days)->toBe(1);
    expect($review->repetitions)->toBe(0);
});

test('answering forgot resets the interval to zero days and lowers the ease factor more than hard', function () {
    $user = User::factory()->create();
    $group = CardGroup::factory()->create();
    $card = Card::factory()->create();
    $card->groups()->attach($group);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'ease_factor' => 2.5,
        'interval_days' => 10,
        'repetitions' => 3,
        'due_at' => now()->subDay(),
    ]);

    $this->actingAs($user)->post(route('card-groups.learn.answer', $group), [
        'card_id' => $card->id,
        'difficulty' => 'forgot',
    ]);

    $review = CardReview::query()->where('card_id', $card->id)->latest('id')->first();

    expect($review->interval_days)->toBe(0);
    expect($review->repetitions)->toBe(0);
    expect($review->ease_factor)->toBe(2.2);
    expect($review->due_at->isAfter(now()))->toBeFalse();
});

test('a card not belonging to the group cannot be answered', function () {
    $user = User::factory()->create();
    $group = CardGroup::factory()->create();
    $card = Card::factory()->create();

    $response = $this->actingAs($user)->post(route('card-groups.learn.answer', $group), [
        'card_id' => $card->id,
        'difficulty' => 'easy',
    ]);

    $response->assertNotFound();
});
