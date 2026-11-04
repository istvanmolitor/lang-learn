<?php

namespace App\Services;

use App\Models\CardGroup;
use App\Repositories\Contracts\CardGroupRepositoryInterface;
use App\Repositories\Contracts\CardRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class CardGenerator
{
    public function __construct(
        private readonly CardRepositoryInterface $cards,
        private readonly CardGroupRepositoryInterface $cardGroups,
        private readonly LanguageRepositoryInterface $languages,
    ) {}

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
        $english = $this->languages->findByCodeOrFail('en');
        $hungarian = $this->languages->findByCodeOrFail('hu');

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

            $card = $this->cards->findByTranslationText($english->id, $englishText);

            if ($card) {
                $reused++;
            } else {
                $card = $this->cards->create();
                $this->cards->syncTranslations($card, [
                    ['language_id' => $english->id, 'text' => $englishText],
                    ['language_id' => $hungarian->id, 'text' => $hungarianText],
                ]);
                $created++;
            }

            $this->cardGroups->attachCard($group, $card);
        }

        return ['created' => $created, 'reused' => $reused];
    }
}
