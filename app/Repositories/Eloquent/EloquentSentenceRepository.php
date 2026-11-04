<?php

namespace App\Repositories\Eloquent;

use App\Models\Sentence;
use App\Repositories\Contracts\SentenceRepositoryInterface;

/**
 * @extends EloquentRepository<Sentence>
 */
class EloquentSentenceRepository extends EloquentRepository implements SentenceRepositoryInterface
{
    protected function modelClass(): string
    {
        return Sentence::class;
    }
}
