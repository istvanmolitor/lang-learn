<?php

namespace App\Repositories\Eloquent;

use App\Models\CardType;
use App\Repositories\Contracts\CardTypeRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * @extends EloquentRepository<CardType>
 */
class EloquentCardTypeRepository extends EloquentRepository implements CardTypeRepositoryInterface
{
    protected function modelClass(): string
    {
        return CardType::class;
    }

    public function allOrderedByName(array $columns = ['*']): Collection
    {
        return CardType::query()->orderBy('name')->get($columns);
    }

    public function allWithCardsCountOrderedByName(): Collection
    {
        return CardType::query()->withCount('cards')->orderBy('name')->get();
    }

    public function findByCode(string $code): ?CardType
    {
        return CardType::query()->where('code', $code)->first();
    }
}
