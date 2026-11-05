<?php

use App\Models\Card;
use App\Models\Language;
use App\Models\Word;
use App\Repositories\Contracts\CardRepositoryInterface;

test('syncing translations links the card to the words in each side', function () {
    $english = Language::factory()->create();
    $hungarian = Language::factory()->create();

    $card = app(CardRepositoryInterface::class)->create();
    app(CardRepositoryInterface::class)->syncTranslations($card, [
        ['language_id' => $english->id, 'text' => 'the dog'],
        ['language_id' => $hungarian->id, 'text' => 'a kutya'],
    ]);

    expect($card->words()->pluck('word')->sort()->values()->all())->toBe(['a', 'dog', 'kutya', 'the']);
    expect(Word::query()->where('language_id', $english->id)->pluck('word')->sort()->values()->all())
        ->toBe(['dog', 'the']);
    expect(Word::query()->where('language_id', $hungarian->id)->pluck('word')->sort()->values()->all())
        ->toBe(['a', 'kutya']);
});

test('re-syncing translations drops words that no longer appear on the card', function () {
    $english = Language::factory()->create();
    $cards = app(CardRepositoryInterface::class);

    $card = $cards->create();
    $cards->syncTranslations($card, [
        ['language_id' => $english->id, 'text' => 'dog'],
    ]);

    $cards->syncTranslations($card, [
        ['language_id' => $english->id, 'text' => 'cat'],
    ]);

    expect($card->words()->pluck('word')->all())->toBe(['cat']);
});

test('two cards sharing a word are both linked to it', function () {
    $english = Language::factory()->create();
    $cards = app(CardRepositoryInterface::class);

    $dog = $cards->create();
    $cards->syncTranslations($dog, [['language_id' => $english->id, 'text' => 'big dog']]);

    $cat = $cards->create();
    $cards->syncTranslations($cat, [['language_id' => $english->id, 'text' => 'big cat']]);

    $big = Word::query()->where('language_id', $english->id)->where('word', 'big')->firstOrFail();

    expect($big->cards()->pluck('cards.id')->sort()->values()->all())
        ->toBe(Card::query()->orderBy('id')->pluck('id')->all());
});
