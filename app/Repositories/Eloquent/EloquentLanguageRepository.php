<?php

namespace App\Repositories\Eloquent;

use App\Models\Language;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * @extends EloquentRepository<Language>
 */
class EloquentLanguageRepository extends EloquentRepository implements LanguageRepositoryInterface
{
    protected function modelClass(): string
    {
        return Language::class;
    }

    public function allOrderedByName(array $columns = ['*']): Collection
    {
        return Language::query()->orderBy('name')->get($columns);
    }

    public function allOrderedByCode(array $columns = ['*']): Collection
    {
        return Language::query()->orderBy('code')->get($columns);
    }

    public function findByCode(string $code): ?Language
    {
        return Language::query()->where('code', $code)->first();
    }

    public function findByCodeOrFail(string $code): Language
    {
        return Language::query()->where('code', $code)->firstOrFail();
    }

    public function findIdByCode(string $code): ?int
    {
        return Language::query()->where('code', $code)->value('id');
    }
}
