<?php

namespace App\Repositories\Eloquent;

use App\Models\CardTranslation;
use App\Repositories\Contracts\CardTranslationRepositoryInterface;

/**
 * @extends EloquentRepository<CardTranslation>
 */
class EloquentCardTranslationRepository extends EloquentRepository implements CardTranslationRepositoryInterface
{
    protected function modelClass(): string
    {
        return CardTranslation::class;
    }
}
