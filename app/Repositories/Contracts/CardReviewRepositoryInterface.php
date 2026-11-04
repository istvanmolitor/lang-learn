<?php

namespace App\Repositories\Contracts;

use App\Models\CardReview;

/**
 * @extends RepositoryInterface<CardReview>
 */
interface CardReviewRepositoryInterface extends RepositoryInterface
{
    /**
     * Find the given user's latest review of the given card, if any.
     */
    public function latestForUserAndCard(int $userId, int $cardId): ?CardReview;
}
