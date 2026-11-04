<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * @extends EloquentRepository<User>
 */
class EloquentUserRepository extends EloquentRepository implements UserRepositoryInterface
{
    protected function modelClass(): string
    {
        return User::class;
    }

    public function learnableLanguagesFor(User $user, array $columns = ['*']): Collection
    {
        return $user->languages()
            ->when($user->language_id, fn ($query, $languageId) => $query->where('languages.id', '!=', $languageId))
            ->orderBy('languages.name')
            ->get($columns);
    }

    public function knownLanguagesFor(User $user, array $columns = ['*']): Collection
    {
        return $user->languages()->get($columns);
    }

    public function knownLanguageIdsFor(User $user): Collection
    {
        return $user->languages()->pluck('languages.id');
    }

    public function syncLanguages(User $user, array $languages): void
    {
        $user->languages()->sync(
            collect($languages)->mapWithKeys(fn (array $row) => [$row['language_id'] => ['level' => $row['level']]])
        );
    }

    public function updateNativeLanguage(User $user, ?int $languageId): void
    {
        $user->update(['language_id' => $languageId]);
    }

    public function updateSelectedLanguage(User $user, ?int $languageId): void
    {
        $user->update(['selected_language_id' => $languageId]);
    }
}
