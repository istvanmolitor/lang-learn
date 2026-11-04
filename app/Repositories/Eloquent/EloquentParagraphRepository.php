<?php

namespace App\Repositories\Eloquent;

use App\Models\Paragraph;
use App\Repositories\Contracts\ParagraphRepositoryInterface;

/**
 * @extends EloquentRepository<Paragraph>
 */
class EloquentParagraphRepository extends EloquentRepository implements ParagraphRepositoryInterface
{
    protected function modelClass(): string
    {
        return Paragraph::class;
    }
}
