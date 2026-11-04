<?php

namespace App\Repositories\Contracts;

use App\Models\Language;
use Illuminate\Support\Collection;

/**
 * @extends RepositoryInterface<Language>
 */
interface LanguageRepositoryInterface extends RepositoryInterface
{
    /**
     * @param  array<int, string>  $columns
     * @return Collection<int, Language>
     */
    public function allOrderedByName(array $columns = ['*']): Collection;

    /**
     * @param  array<int, string>  $columns
     * @return Collection<int, Language>
     */
    public function allOrderedByCode(array $columns = ['*']): Collection;

    public function findByCode(string $code): ?Language;

    public function findByCodeOrFail(string $code): Language;

    public function findIdByCode(string $code): ?int;
}
