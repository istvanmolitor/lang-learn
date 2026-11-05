<?php

use App\Models\Card;
use App\Models\CardTranslation;
use App\Models\Language;
use App\Models\Word;

test('it links cards that have no words yet to the words their translations contain', function () {
    $english = Language::factory()->create();
    $card = Card::factory()->create();
    CardTranslation::factory()->create([
        'card_id' => $card->id,
        'language_id' => $english->id,
        'text' => 'Big Dog',
    ]);

    $this->artisan('cards:fill-words')
        ->expectsOutputToContain('Updated 1 card(s).')
        ->assertExitCode(0);

    expect(Word::query()->where('language_id', $english->id)->count())->toBe(2);
    expect($card->words()->pluck('word')->sort()->values()->all())->toBe(['big', 'dog']);
});

test('it does not touch cards that already have words', function () {
    $english = Language::factory()->create();
    $card = Card::factory()->create();
    CardTranslation::factory()->create([
        'card_id' => $card->id,
        'language_id' => $english->id,
        'text' => 'dog',
    ]);

    $word = Word::factory()->create(['language_id' => $english->id, 'word' => 'existing']);
    $card->words()->attach($word);

    $this->artisan('cards:fill-words')
        ->expectsOutputToContain('Updated 0 card(s).')
        ->assertExitCode(0);

    expect($card->words()->pluck('word')->all())->toBe(['existing']);
});
