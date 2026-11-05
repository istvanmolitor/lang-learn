<?php

namespace App\Repositories\Contracts;

use App\Models\Word;

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
}
