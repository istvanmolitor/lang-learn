<?php

namespace App\Repositories\Contracts;

use App\Models\Article;
use Illuminate\Support\Collection;

/**
 * @extends RepositoryInterface<Article>
 */
interface ArticleRepositoryInterface extends RepositoryInterface
{
    /**
     * @return Collection<int, Article>
     */
    public function allWithLanguageOrderedByTitle(): Collection;

    /**
     * Eager load the relations needed to read the given article.
     */
    public function loadForShow(Article $article): Article;
}
