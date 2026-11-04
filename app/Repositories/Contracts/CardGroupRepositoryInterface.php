<?php

namespace App\Repositories\Contracts;

use App\Models\Card;
use App\Models\CardGroup;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * @extends RepositoryInterface<CardGroup>
 */
interface CardGroupRepositoryInterface extends RepositoryInterface
{
    /**
     * @return Collection<int, CardGroup>
     */
    public function allWithCardsCountOrderedByName(): Collection;

    /**
     * @param  array<int, string>  $columns
     * @return Collection<int, CardGroup>
     */
    public function allOrderedByName(array $columns = ['*']): Collection;

    /**
     * Get the group's cards, with each card's latest review by the given user eager loaded.
     *
     * @return Collection<int, Card>
     */
    public function cardsWithLatestReviewFor(CardGroup $group, User $user): Collection;

    /**
     * Calculate the percentage of the group's cards the given user currently knows.
     */
    public function progressPercentageFor(CardGroup $group, User $user): int;

    /**
     * @return Collection<int, Card>
     */
    public function cardsWithTranslationsOrderedById(CardGroup $group): Collection;

    public function findCardInGroupOrFail(CardGroup $group, int $cardId): Card;

    public function firstOrCreateByName(string $name): CardGroup;

    public function attachCard(CardGroup $group, Card $card): void;
}
