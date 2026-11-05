<?php

use App\Models\Language;
use App\Models\Word;
use App\Services\WordExtractor;

test('it splits text into words and returns their ids in order', function () {
    $english = Language::factory()->create();

    $ids = app(WordExtractor::class)->extract('cat sat on mat', $english->id);

    expect(Word::query()->count())->toBe(4);
    expect(Word::query()->find($ids)->pluck('word')->all())->toEqualCanonicalizing([
        'cat', 'sat', 'on', 'mat',
    ]);
    expect(Word::find($ids[0])->word)->toBe('cat');
});

test('it reuses existing words instead of duplicating them', function () {
    $english = Language::factory()->create();

    app(WordExtractor::class)->extract('dog cat dog', $english->id);
    $ids = app(WordExtractor::class)->extract('dog cat dog', $english->id);

    expect(Word::query()->count())->toBe(2);
    expect($ids[0])->toBe($ids[2]);
    expect($ids[0])->not->toBe($ids[1]);
});

test('it lowercases words before storing them', function () {
    $english = Language::factory()->create();

    $ids = app(WordExtractor::class)->extract('The Cat', $english->id);

    expect(Word::query()->count())->toBe(2);
    expect(Word::query()->find($ids)->pluck('word')->all())->toEqualCanonicalizing(['the', 'cat']);
});

test('it treats words that only differ by case as the same word', function () {
    $english = Language::factory()->create();

    $ids = app(WordExtractor::class)->extract('The cat and the dog', $english->id);

    expect(Word::query()->count())->toBe(4);
    expect($ids[0])->toBe($ids[3]);
});

test('it scopes words by language', function () {
    $english = Language::factory()->create();
    $hungarian = Language::factory()->create();

    $extractor = app(WordExtractor::class);
    $extractor->extract('dog', $english->id);
    $extractor->extract('dog', $hungarian->id);

    expect(Word::query()->count())->toBe(2);
});
