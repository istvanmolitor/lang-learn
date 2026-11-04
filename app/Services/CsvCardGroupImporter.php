<?php

namespace App\Services;

use App\Repositories\Contracts\CardGroupRepositoryInterface;
use App\Repositories\Contracts\CardRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CsvCardGroupImporter
{
    public function __construct(
        private readonly CardRepositoryInterface $cards,
        private readonly CardGroupRepositoryInterface $cardGroups,
        private readonly LanguageRepositoryInterface $languages,
    ) {}

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
        $group = $this->cardGroups->firstOrCreateByName($groupName);

        if (! $group->wasRecentlyCreated) {
            return;
        }

        $handle = fopen($csvPath, 'r');

        throw_if($handle === false, new \RuntimeException("Unable to open {$csvPath}."));

        $header = array_map(trim(...), fgetcsv($handle) ?: []);

        $languageIdsByColumn = collect($header)
            ->map(fn (string $code) => $this->languages->findIdByCode($code))
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
                    $card = $this->cards->findByTranslationText($englishLanguageId, $englishText);
                }

                $card ??= $this->cards->create();

                $this->cards->syncTranslations($card, $translations);
                $this->cardGroups->attachCard($group, $card);
            }
        });

        fclose($handle);
    }
}
