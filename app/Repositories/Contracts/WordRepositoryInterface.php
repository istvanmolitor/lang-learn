<?php

namespace App\Repositories\Contracts;

use App\Models\Word;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<Word>
 */
interface WordRepositoryInterface extends RepositoryInterface
{
    /**
     * Resolve the id of each given word in the given language, creating any word that
     * doesn't exist yet. The returned array preserves the order and duplicates of $words.
     *
     * @param  array<int, string>  $words
     * @return array<int, int>
     */
    public function resolveIds(int $languageId, array $words): array;

    /**
     * Paginate words matching the given search term, including their sentence count,
     * ordered with the most-used words first.
     *
     * @return LengthAwarePaginator<int, Word>
     */
    public function paginateSearch(string $search, int $perPage = 20): LengthAwarePaginator;
}
