<?php

namespace App\Repositories\Eloquent;

use App\Models\CardReview;
use App\Repositories\Contracts\CardReviewRepositoryInterface;

/**
 * @extends EloquentRepository<CardReview>
 */
class EloquentCardReviewRepository extends EloquentRepository implements CardReviewRepositoryInterface
{
    protected function modelClass(): string
    {
        return CardReview::class;
    }

    public function latestForUserAndCard(int $userId, int $cardId): ?CardReview
    {
        return CardReview::query()
            ->where('user_id', $userId)
            ->where('card_id', $cardId)
            ->latest('id')
            ->first();
    }
}
