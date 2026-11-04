<?php

namespace App\Repositories\Contracts;

use App\Models\Language;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * @extends RepositoryInterface<User>
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * The languages the given user can currently learn, i.e. the languages they know
     * excluding their native language.
     *
     * @param  array<int, string>  $columns
     * @return Collection<int, Language>
     */
    public function learnableLanguagesFor(User $user, array $columns = ['*']): Collection;

    /**
     * The languages the given user knows, with their pivot proficiency level loaded.
     *
     * @param  array<int, string>  $columns
     * @return Collection<int, Language>
     */
    public function knownLanguagesFor(User $user, array $columns = ['*']): Collection;

    /**
     * @return Collection<int, int>
     */
    public function knownLanguageIdsFor(User $user): Collection;

    /**
     * @param  array<int, array{language_id: int, level: mixed}>  $languages
     */
    public function syncLanguages(User $user, array $languages): void;

    public function updateNativeLanguage(User $user, ?int $languageId): void;

    public function updateSelectedLanguage(User $user, ?int $languageId): void;
}
