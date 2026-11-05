<?php

namespace App\Console\Commands;

use App\Models\CardTranslation;
use App\Repositories\Contracts\CardTypeRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Services\CardTextTypeExtractor;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class NormalizeCardTypesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cards:normalize-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Strip the parenthesised part-of-speech tag (e.g. "(n.)") from existing English card text and set the card\'s type from it';

    public function handle(
        CardTextTypeExtractor $extractor,
        CardTypeRepositoryInterface $cardTypes,
        LanguageRepositoryInterface $languages,
    ): int {
        $english = $languages->findByCode('en');

        if (! $english) {
            $this->error('No English language found.');

            return self::FAILURE;
        }

        $cardTypeIdsByCode = $cardTypes->allOrderedByName()->pluck('id', 'code');
        $updated = 0;

        CardTranslation::query()
            ->where('language_id', $english->id)
            ->with('card')
            ->chunkById(200, function (Collection $translations) use (&$updated, $cardTypeIdsByCode, $extractor) {
                foreach ($translations as $translation) {
                    $extracted = $extractor->extract($translation->text);

                    if ($extracted['text'] === $translation->text && $extracted['code'] === null) {
                        continue;
                    }

                    $translation->update(['text' => $extracted['text']]);

                    if ($extracted['code'] !== null && $translation->card && $translation->card->card_type_id === null) {
                        $translation->card->update(['card_type_id' => $cardTypeIdsByCode->get($extracted['code'])]);
                    }

                    $updated++;
                }
            });

        $this->info("Updated {$updated} card translation(s).");

        return self::SUCCESS;
    }
}
