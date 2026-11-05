<?php

use App\Models\Card;
use App\Models\CardType;
use App\Models\User;

test('guests cannot view the card types page', function () {
    $this->get(route('card-types.index'))->assertRedirect(route('login'));
});

test('the card types index lists the seeded types with their card counts', function () {
    $user = User::factory()->create();
    $cardType = CardType::query()->where('code', 'n')->firstOrFail();
    Card::factory()->create(['card_type_id' => $cardType->id]);

    $response = $this->actingAs($user)->get(route('card-types.index'));

    $response->assertInertia(fn ($page) => $page
        ->component('card-types/Index')
        ->where('cardTypes', fn ($cardTypes) => collect($cardTypes)
            ->firstWhere('id', $cardType->id)['cards_count'] === 1)
    );
});

test('users can create a card type', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('card-types.store'), [
        'code' => 'num',
        'name' => 'Számnév',
    ]);

    $response->assertRedirect(route('card-types.index'));
    expect(CardType::query()->where('code', 'num')->exists())->toBeTrue();
});

test('a card type code must be unique', function () {
    $user = User::factory()->create();
    CardType::factory()->create(['code' => 'dup']);

    $response = $this->actingAs($user)->post(route('card-types.store'), [
        'code' => 'dup',
        'name' => 'Duplikátum',
    ]);

    $response->assertInvalid(['code']);
});

test('users can update a card type', function () {
    $user = User::factory()->create();
    $cardType = CardType::factory()->create(['code' => 'old', 'name' => 'Régi']);

    $response = $this->actingAs($user)->put(route('card-types.update', $cardType), [
        'code' => 'new',
        'name' => 'Új',
    ]);

    $response->assertRedirect(route('card-types.index'));
    expect($cardType->refresh())->code->toBe('new')->name->toBe('Új');
});

test('updating a card type can keep its own code', function () {
    $user = User::factory()->create();
    $cardType = CardType::factory()->create(['code' => 'same']);

    $response = $this->actingAs($user)->put(route('card-types.update', $cardType), [
        'code' => 'same',
        'name' => 'Átnevezve',
    ]);

    $response->assertRedirect(route('card-types.index'));
});

test('users can delete a card type and cards keep existing without it', function () {
    $user = User::factory()->create();
    $cardType = CardType::factory()->create();
    $card = Card::factory()->create(['card_type_id' => $cardType->id]);

    $response = $this->actingAs($user)->delete(route('card-types.destroy', $cardType));

    $response->assertRedirect(route('card-types.index'));
    expect(CardType::query()->find($cardType->id))->toBeNull();
    expect($card->refresh()->card_type_id)->toBeNull();
});
