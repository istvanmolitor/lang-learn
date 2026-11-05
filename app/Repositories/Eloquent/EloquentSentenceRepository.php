<?php

namespace App\Repositories\Eloquent;

use App\Models\Card;
use App\Models\Sentence;
use App\Repositories\Contracts\SentenceRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @extends EloquentRepository<Sentence>
 */
class EloquentSentenceRepository extends EloquentRepository implements SentenceRepositoryInterface
{
    protected function modelClass(): string
    {
        return Sentence::class;
    }

    public function syncWords(Sentence $sentence, array $wordIds): void
    {
        $sentence->words()->sync($wordIds);
    }

    public function cardsBySentenceId(Collection $sentences): Collection
    {
        $links = DB::table('sentence_word')
            ->join('card_word', 'card_word.word_id', '=', 'sentence_word.word_id')
            ->whereIn('sentence_word.sentence_id', $sentences->pluck('id'))
            ->select('sentence_word.sentence_id', 'card_word.card_id')
            ->distinct()
            ->get();

        $cardsById = Card::query()
            ->whereIn('id', $links->pluck('card_id')->unique())
            ->with('translations.language')
            ->get()
            ->keyBy('id');

        return $links->groupBy('sentence_id')
            ->map(fn (Collection $rows) => $rows->map(fn ($row) => $cardsById->get($row->card_id))->filter()->values());
    }

    public function examplesForCard(Card $card, int $languageId, int $limit = 3): Collection
    {
        return Sentence::query()
            ->whereIn('id', function ($query) use ($card, $languageId) {
                $query->select('sentence_word.sentence_id')
                    ->from('sentence_word')
                    ->join('card_word', 'card_word.word_id', '=', 'sentence_word.word_id')
                    ->join('words', 'words.id', '=', 'sentence_word.word_id')
                    ->where('card_word.card_id', $card->id)
                    ->where('words.language_id', $languageId);
            })
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
