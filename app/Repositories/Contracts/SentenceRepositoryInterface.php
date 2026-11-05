<?php

namespace App\Repositories\Contracts;

use App\Models\Card;
use App\Models\Sentence;
use Illuminate\Support\Collection;

/**
 * @extends RepositoryInterface<Sentence>
 */
interface SentenceRepositoryInterface extends RepositoryInterface
{
    /**
     * @param  array<int, int>  $wordIds
     */
    public function syncWords(Sentence $sentence, array $wordIds): void;

    /**
     * Find the cards whose words appear in each given sentence, via the sentence_word and
     * card_word tables, keyed by sentence id. Sentences with no matching cards are omitted.
     *
     * @param  Collection<int, Sentence>  $sentences
     * @return Collection<int, Collection<int, Card>>
     */
    public function cardsBySentenceId(Collection $sentences): Collection;

    /**
     * Find up to $limit random sentences, in the given language, that use one of the card's
     * words, via the card_word and sentence_word tables.
     *
     * @return Collection<int, Sentence>
     */
    public function examplesForCard(Card $card, int $languageId, int $limit = 3): Collection;
}
