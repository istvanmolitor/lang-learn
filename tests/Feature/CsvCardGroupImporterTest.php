<?php

use App\Models\Card;
use App\Models\CardGroup;
use App\Models\Language;
use App\Services\CsvCardGroupImporter;

function writeImportCsv(array $rows): string
{
    $path = tempnam(sys_get_temp_dir(), 'csv-card-group-importer-test-');

    $handle = fopen($path, 'w');
    foreach ($rows as $row) {
        fputcsv($handle, $row);
    }
    fclose($handle);

    return $path;
}

test('it creates a card group with cards from a csv file', function () {
    $english = Language::factory()->create(['code' => 'en']);
    $hungarian = Language::factory()->create(['code' => 'hu']);

    $path = writeImportCsv([
        ['en', 'hu'],
        ['dog', 'kutya'],
        ['cat', 'macska'],
    ]);

    app(CsvCardGroupImporter::class)->import($path, 'Animals');

    $group = CardGroup::query()->where('name', 'Animals')->firstOrFail();
    expect($group->cards()->count())->toBe(2);
    expect(Card::query()->count())->toBe(2);
});

test('it reuses an existing card instead of duplicating it across groups', function () {
    $english = Language::factory()->create(['code' => 'en']);
    $hungarian = Language::factory()->create(['code' => 'hu']);

    $firstPath = writeImportCsv([
        ['en', 'hu'],
        ['dog', 'kutya'],
    ]);
    $secondPath = writeImportCsv([
        ['en', 'hu'],
        ['dog', 'kutya'],
        ['cat', 'macska'],
    ]);

    $importer = app(CsvCardGroupImporter::class);
    $importer->import($firstPath, 'Pets A');
    $importer->import($secondPath, 'Pets B');

    expect(Card::query()->count())->toBe(2);

    $dog = Card::query()->whereHas('translations', fn ($q) => $q->where('text', 'dog'))->firstOrFail();
    expect($dog->groups()->pluck('name')->sort()->values()->all())->toBe(['Pets A', 'Pets B']);
});

test('it strips the part-of-speech tag from english text and assigns the matching card type', function () {
    $english = Language::factory()->create(['code' => 'en']);
    $hungarian = Language::factory()->create(['code' => 'hu']);

    $path = writeImportCsv([
        ['en', 'hu'],
        ['ability (n.)', 'képesség'],
        ['bear (animal) (n.)', 'medve'],
    ]);

    app(CsvCardGroupImporter::class)->import($path, 'Vocabulary');

    $ability = Card::query()->whereHas('translations', fn ($q) => $q->where('text', 'ability'))->firstOrFail();
    expect($ability->cardType?->code)->toBe('n');

    $bear = Card::query()->whereHas('translations', fn ($q) => $q->where('text', 'bear (animal)'))->firstOrFail();
    expect($bear->cardType?->code)->toBe('n');
});

test('it does not reimport a group that already exists', function () {
    $english = Language::factory()->create(['code' => 'en']);
    $hungarian = Language::factory()->create(['code' => 'hu']);

    $path = writeImportCsv([
        ['en', 'hu'],
        ['dog', 'kutya'],
    ]);

    $importer = app(CsvCardGroupImporter::class);
    $importer->import($path, 'Animals');
    $importer->import($path, 'Animals');

    expect(CardGroup::query()->where('name', 'Animals')->count())->toBe(1);
    expect(Card::query()->count())->toBe(1);
});
