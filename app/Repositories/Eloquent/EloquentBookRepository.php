<?php

namespace App\Repositories\Eloquent;

use App\Models\Book;
use App\Repositories\Contracts\BookRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * @extends EloquentRepository<Book>
 */
class EloquentBookRepository extends EloquentRepository implements BookRepositoryInterface
{
    protected function modelClass(): string
    {
        return Book::class;
    }

    public function allWithLanguageOrderedByTitle(): Collection
    {
        return Book::query()
            ->with('language')
            ->orderBy('title')
            ->get();
    }

    public function loadForShow(Book $book): Book
    {
        return $book->load('language');
    }
}
