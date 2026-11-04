<?php

namespace App\Providers;

use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\CardGroupRepositoryInterface;
use App\Repositories\Contracts\CardRepositoryInterface;
use App\Repositories\Contracts\CardReviewRepositoryInterface;
use App\Repositories\Contracts\CardTranslationRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\ParagraphRepositoryInterface;
use App\Repositories\Contracts\SentenceRepositoryInterface;
use App\Repositories\Contracts\SentenceTranslationRepositoryInterface;
use App\Repositories\Contracts\UserLanguageRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentArticleRepository;
use App\Repositories\Eloquent\EloquentCardGroupRepository;
use App\Repositories\Eloquent\EloquentCardRepository;
use App\Repositories\Eloquent\EloquentCardReviewRepository;
use App\Repositories\Eloquent\EloquentCardTranslationRepository;
use App\Repositories\Eloquent\EloquentLanguageRepository;
use App\Repositories\Eloquent\EloquentParagraphRepository;
use App\Repositories\Eloquent\EloquentSentenceRepository;
use App\Repositories\Eloquent\EloquentSentenceTranslationRepository;
use App\Repositories\Eloquent\EloquentUserLanguageRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * The repository interface to implementation bindings.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        ArticleRepositoryInterface::class => EloquentArticleRepository::class,
        CardGroupRepositoryInterface::class => EloquentCardGroupRepository::class,
        CardRepositoryInterface::class => EloquentCardRepository::class,
        CardReviewRepositoryInterface::class => EloquentCardReviewRepository::class,
        CardTranslationRepositoryInterface::class => EloquentCardTranslationRepository::class,
        LanguageRepositoryInterface::class => EloquentLanguageRepository::class,
        ParagraphRepositoryInterface::class => EloquentParagraphRepository::class,
        SentenceRepositoryInterface::class => EloquentSentenceRepository::class,
        SentenceTranslationRepositoryInterface::class => EloquentSentenceTranslationRepository::class,
        UserLanguageRepositoryInterface::class => EloquentUserLanguageRepository::class,
        UserRepositoryInterface::class => EloquentUserRepository::class,
    ];
}
