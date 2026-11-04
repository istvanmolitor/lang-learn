<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articles,
    ) {}

    /**
     * Display a listing of the articles.
     */
    public function index(): Response
    {
        $articles = $this->articles->allWithLanguageOrderedByTitle()
            ->map(fn (Article $article) => [
                'id' => $article->id,
                'title' => $article->title,
                'language' => $article->language->name,
            ]);

        return Inertia::render('articles/Index', [
            'articles' => $articles,
        ]);
    }

    /**
     * Display the given article so it can be read.
     */
    public function show(Article $article): Response
    {
        $this->articles->loadForShow($article);

        $user = request()->user();

        return Inertia::render('articles/Show', [
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'language' => $article->language->name,
                'paragraphs' => $article->paragraphs->map(fn ($paragraph) => [
                    'id' => $paragraph->id,
                    'sentences' => $paragraph->sentences->map(fn ($sentence) => [
                        'id' => $sentence->id,
                        'text' => $sentence->text,
                        'translation' => $sentence->translations
                            ->firstWhere('language_id', $user->language_id)
                            ?->text,
                    ]),
                ]),
            ],
        ]);
    }
}
