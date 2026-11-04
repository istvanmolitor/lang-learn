<?php

namespace App\Repositories\Contracts;

use App\Models\Card;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<Card>
 */
interface CardRepositoryInterface extends RepositoryInterface
{
    /**
     * Paginate cards, optionally filtered by a search term matched against their translations.
     *
     * @return LengthAwarePaginator<int, Card>
     */
    public function paginateSearch(string $search, int $perPage = 20): LengthAwarePaginator;

    /**
     * Eager load the relations needed to edit the given card.
     */
    public function loadForEdit(Card $card): Card;

    /**
     * @param  array<int, array{language_id: int, text: string|null}>  $translations
     */
    public function syncTranslations(Card $card, array $translations): void;

    /**
     * @param  array<int, int>  $groupIds
     */
    public function syncGroups(Card $card, array $groupIds): void;

    /**
     * Find a card whose translation in the given language matches the given text exactly (case-insensitively).
     */
    public function findByTranslationText(int $languageId, string $text): ?Card;
}
