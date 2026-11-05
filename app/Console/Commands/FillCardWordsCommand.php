<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Repositories\Contracts\CardRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class FillCardWordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cards:fill-words';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link each card that has no words yet to the words its translations contain';

    public function handle(CardRepositoryInterface $cardRepository): int
    {
        $updated = 0;

        Card::query()
            ->doesntHave('words')
            ->chunkById(200, function (Collection $cards) use (&$updated, $cardRepository) {
                foreach ($cards as $card) {
                    $cardRepository->syncWords($card);
                    $updated++;
                }
            });

        $this->info("Updated {$updated} card(s).");

        return self::SUCCESS;
    }
}
