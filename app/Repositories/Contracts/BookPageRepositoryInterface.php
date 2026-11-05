<?php

namespace App\Repositories\Contracts;

use App\Models\Book;
use App\Models\BookPage;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<BookPage>
 */
interface BookPageRepositoryInterface extends RepositoryInterface
{
    /**
     * Paginate the given book's pages one at a time, with each page's
     * paragraphs, sentences, and translations eager loaded.
     *
     * @return LengthAwarePaginator<int, BookPage>
     */
    public function paginateForBook(Book $book, int $page, int $perPage = 1): LengthAwarePaginator;
}
