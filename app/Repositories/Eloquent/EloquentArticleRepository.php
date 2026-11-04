<?php

namespace App\Repositories\Eloquent;

use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * @extends EloquentRepository<Article>
 */
class EloquentArticleRepository extends EloquentRepository implements ArticleRepositoryInterface
{
    protected function modelClass(): string
    {
        return Article::class;
    }

    public function allWithLanguageOrderedByTitle(): Collection
    {
        return Article::query()
            ->with('language')
            ->orderBy('title')
            ->get();
    }

    public function loadForShow(Article $article): Article
    {
        return $article->load(['language', 'paragraphs.sentences.translations']);
    }
}
