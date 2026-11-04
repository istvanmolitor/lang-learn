<?php

namespace App\Repositories\Eloquent;

use App\Models\SentenceTranslation;
use App\Repositories\Contracts\SentenceTranslationRepositoryInterface;

/**
 * @extends EloquentRepository<SentenceTranslation>
 */
class EloquentSentenceTranslationRepository extends EloquentRepository implements SentenceTranslationRepositoryInterface
{
    protected function modelClass(): string
    {
        return SentenceTranslation::class;
    }
}
