<?php

namespace App\Repositories\Contracts;

use App\Models\Book;
use Illuminate\Support\Collection;

/**
 * @extends RepositoryInterface<Book>
 */
interface BookRepositoryInterface extends RepositoryInterface
{
    /**
     * @return Collection<int, Book>
     */
    public function allWithLanguageOrderedByTitle(): Collection;

    /**
     * Eager load the book's language.
     */
    public function loadForShow(Book $book): Book;
}
