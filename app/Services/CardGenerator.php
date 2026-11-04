<?php

namespace App\Services;

use App\Models\Card;
use App\Models\CardGroup;
use App\Models\Language;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class CardGenerator
{
    /**
     * Generate English-Hungarian vocabulary cards for the given topic and attach them to the group.
     *
     * Cards that already exist (matched by their English text) are reused and simply
     * attached to the group instead of being duplicated.
     *
     * @return array{created: int, reused: int}
     */
    public function generateInto(CardGroup $group, string $topic, int $count): array
    {
        $english = Language::query()->where('code', 'en')->firstOrFail();
        $hungarian = Language::query()->where('code', 'hu')->firstOrFail();

        $schema = new ObjectSchema(
            name: 'card_list',
            description: 'A list of English-Hungarian vocabulary flashcards',
            properties: [
                new ArraySchema(
                    name: 'cards',
                    description: 'The generated flashcards',
                    items: new ObjectSchema(
                        name: 'card',
                        description: 'A single flashcard',
                        properties: [
                            new StringSchema('en', 'The word or short phrase in English'),
                            new StringSchema('hu', 'The Hungarian translation of the English word or phrase'),
                        ],
                        requiredFields: ['en', 'hu'],
                    ),
                ),
            ],
            requiredFields: ['cards'],
        );

        $response = Prism::structured()
            ->using(Provider::OpenAI, 'gpt-4o-mini')
            ->withSchema($schema)
            ->withPrompt(
                "Generate {$count} unique English-Hungarian vocabulary flashcard pairs for language learners ".
                "about the topic: \"{$topic}\". Keep each word or phrase short and avoid duplicates."
            )
            ->asStructured();

        $created = 0;
        $reused = 0;

        foreach ($response->structured['cards'] ?? [] as $item) {
            $englishText = trim((string) ($item['en'] ?? ''));
            $hungarianText = trim((string) ($item['hu'] ?? ''));

            if ($englishText === '' || $hungarianText === '') {
                continue;
            }

            $card = Card::query()
                ->whereHas('translations', fn ($query) => $query
                    ->where('language_id', $english->id)
                    ->whereRaw('LOWER(text) = ?', [Str::lower($englishText)]))
                ->first();

            if ($card) {
                $reused++;
            } else {
                $card = Card::create();
                $card->syncTranslations([
                    ['language_id' => $english->id, 'text' => $englishText],
                    ['language_id' => $hungarian->id, 'text' => $hungarianText],
                ]);
                $created++;
            }

            $group->cards()->syncWithoutDetaching($card->id);
        }

        return ['created' => $created, 'reused' => $reused];
    }
}
