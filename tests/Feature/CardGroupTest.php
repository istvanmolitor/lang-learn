<?php

use App\Models\Card;
use App\Models\CardGroup;
use App\Models\User;

test('guests cannot view the card groups page', function () {
    $this->get(route('card-groups.index'))->assertRedirect(route('login'));
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
