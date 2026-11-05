<?php

use App\Models\Book;
use App\Models\BookPage;
use App\Models\Language;
use App\Models\Paragraph;
use App\Models\Sentence;
use App\Models\Word;

test('it links sentences that have no words yet to the words they contain', function () {
    $english = Language::factory()->create();
    $book = Book::factory()->create(['language_id' => $english->id]);
    $page = BookPage::factory()->create(['book_id' => $book->id]);
    $paragraph = Paragraph::factory()->create(['page_id' => $page->id]);

    $sentence = Sentence::factory()->create([
        'paragraph_id' => $paragraph->id,
        'text' => 'Cat and Dog.',
    ]);

    $this->artisan('sentences:fill-words')
        ->expectsOutputToContain('Updated 1 sentence(s).')
        ->assertExitCode(0);

    expect(Word::query()->where('language_id', $english->id)->count())->toBe(3);
    expect($sentence->words()->pluck('word')->sort()->values()->all())->toBe(['and', 'cat', 'dog']);
});

test('it does not touch sentences that already have words', function () {
    $book = Book::factory()->create();
    $page = BookPage::factory()->create(['book_id' => $book->id]);
    $paragraph = Paragraph::factory()->create(['page_id' => $page->id]);

    $word = Word::factory()->create(['language_id' => $book->language_id, 'word' => 'existing']);

    $sentence = Sentence::factory()->create([
        'paragraph_id' => $paragraph->id,
        'text' => 'Existing word sentence.',
    ]);
    $sentence->words()->attach($word);

    $this->artisan('sentences:fill-words')
        ->expectsOutputToContain('Updated 0 sentence(s).')
        ->assertExitCode(0);

    expect($sentence->words()->pluck('word')->all())->toBe(['existing']);
});
