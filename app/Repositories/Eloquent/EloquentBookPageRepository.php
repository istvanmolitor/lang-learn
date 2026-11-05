<?php

namespace App\Repositories\Eloquent;

use App\Models\Book;
use App\Models\BookPage;
use App\Repositories\Contracts\BookPageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends EloquentRepository<BookPage>
 */
class EloquentBookPageRepository extends EloquentRepository implements BookPageRepositoryInterface
{
    protected function modelClass(): string
    {
        return BookPage::class;
    }

    public function paginateForBook(Book $book, int $page, int $perPage = 1): LengthAwarePaginator
    {
        return $book->pages()
            ->with('paragraphs.sentences.translations')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
