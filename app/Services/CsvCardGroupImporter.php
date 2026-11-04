<?php

namespace App\Services;

use App\Models\Card;
use App\Models\CardGroup;
use App\Models\Language;
use Illuminate\Support\Facades\DB;

class CsvCardGroupImporter
{
    /**
     * Import the given CSV file into a card group with the given name.
     *
     * The CSV's header row must contain language codes. Cards are matched by
     * their English text first, so a word already seeded into another group
     * is reused (and simply attached) instead of being duplicated.
     *
     * Does nothing if a group with this name already exists, so the import
     * stays safe to run repeatedly.
     */
    public function import(string $csvPath, string $groupName): void
    {
        $group = CardGroup::query()->firstOrCreate(['name' => $groupName]);

        if (! $group->wasRecentlyCreated) {
            return;
        }

        $handle = fopen($csvPath, 'r');

        throw_if($handle === false, new \RuntimeException("Unable to open {$csvPath}."));

        $header = array_map(trim(...), fgetcsv($handle) ?: []);

        $languageIdsByColumn = collect($header)
            ->map(fn (string $code) => Language::query()->where('code', $code)->value('id'))
            ->all();

        $englishColumn = array_search('en', $header, true);

        DB::transaction(function () use ($handle, $languageIdsByColumn, $englishColumn, $group) {
            while (($row = fgetcsv($handle)) !== false) {
                $translations = [];

                foreach ($languageIdsByColumn as $column => $languageId) {
                    if ($languageId !== null) {
                        $translations[] = [
                            'language_id' => $languageId,
                            'text' => $row[$column] ?? null,
                        ];
                    }
                }

                $card = null;
                $englishLanguageId = $englishColumn !== false ? $languageIdsByColumn[$englishColumn] : null;
                $englishText = $englishColumn !== false ? ($row[$englishColumn] ?? null) : null;

                if ($englishLanguageId !== null && filled($englishText)) {
                    $card = Card::query()
                        ->whereHas('translations', fn ($query) => $query
                            ->where('language_id', $englishLanguageId)
                            ->where('text', $englishText))
                        ->first();
                }

                $card ??= Card::create();

                $card->syncTranslations($translations);
                $group->cards()->syncWithoutDetaching([$card->id]);
            }
        });

        fclose($handle);
    }
}
