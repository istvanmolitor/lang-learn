<?php

namespace App\Repositories\Eloquent;

use App\Models\Card;
use App\Repositories\Contracts\CardRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * @extends EloquentRepository<Card>
 */
class EloquentCardRepository extends EloquentRepository implements CardRepositoryInterface
{
    protected function modelClass(): string
    {
        return Card::class;
    }

    public function paginateSearch(string $search, int $perPage = 20): LengthAwarePaginator
    {
        $likeSearch = str_replace(['%', '_'], ['\%', '\_'], $search);

        return Card::query()
            ->with(['translations.language', 'groups', 'cardType'])
            ->when($search !== '', fn ($query) => $query->whereHas(
                'translations',
                fn ($translationQuery) => $translationQuery->where('text', 'like', '%'.$likeSearch.'%')
            ))
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function loadForEdit(Card $card): Card
    {
        return $card->load(['translations', 'groups', 'cardType']);
    }

    public function syncTranslations(Card $card, array $translations): void
    {
        $card->syncTranslations($translations);
    }

    public function syncGroups(Card $card, array $groupIds): void
    {
        $card->groups()->sync($groupIds);
    }

    public function findByTranslationText(int $languageId, string $text): ?Card
    {
        return Card::query()
            ->whereHas('translations', fn ($query) => $query
                ->where('language_id', $languageId)
                ->whereRaw('LOWER(text) = ?', [Str::lower($text)]))
            ->first();
    }
}
