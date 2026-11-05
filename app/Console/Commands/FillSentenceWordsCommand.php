<?php

namespace App\Console\Commands;

use App\Models\Sentence;
use App\Repositories\Contracts\SentenceRepositoryInterface;
use App\Services\WordExtractor;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class FillSentenceWordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sentences:fill-words';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link each sentence that has no words yet to the words it contains';

    public function handle(WordExtractor $wordExtractor, SentenceRepositoryInterface $sentenceRepository): int
    {
        $updated = 0;

        Sentence::query()
            ->doesntHave('words')
            ->with('paragraph.page.book')
            ->chunkById(200, function (Collection $sentences) use (&$updated, $wordExtractor, $sentenceRepository) {
                foreach ($sentences as $sentence) {
                    $languageId = $sentence->paragraph->page->book->language_id;

                    $wordIds = $wordExtractor->extract($sentence->text, $languageId);
                    $sentenceRepository->syncWords($sentence, array_unique($wordIds));

                    $updated++;
                }
            });

        $this->info("Updated {$updated} sentence(s).");

        return self::SUCCESS;
    }
}
