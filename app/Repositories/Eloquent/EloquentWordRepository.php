<?php

namespace App\Repositories\Eloquent;

use App\Models\Word;
use App\Repositories\Contracts\WordRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends EloquentRepository<Word>
 */
class EloquentWordRepository extends EloquentRepository implements WordRepositoryInterface
{
    protected function modelClass(): string
    {
        return Word::class;
    }

    /**
     * @return LengthAwarePaginator<int, Word>
     */
    public function paginateSearch(string $search, int $perPage = 20): LengthAwarePaginator
    {
        $likeSearch = str_replace(['%', '_'], ['\%', '\_'], $search);

        return Word::query()
            ->withCount(['sentences', 'cards'])
            ->when($search !== '', fn ($query) => $query->where('word', 'like', '%'.$likeSearch.'%'))
            ->orderByDesc('sentences_count')
            ->orderBy('word')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function resolveIds(int $languageId, array $words): array
    {
        $unique = array_unique($words);

        $ids = Word::query()
            ->where('language_id', $languageId)
            ->whereIn('word', $unique)
            ->pluck('id', 'word')
            ->all();

        foreach (array_diff($unique, array_keys($ids)) as $word) {
            $ids[$word] = Word::query()->firstOrCreate([
                'word' => $word,
                'language_id' => $languageId,
            ])->id;
        }

        return array_map(fn (string $word) => $ids[$word], $words);
    }
}
