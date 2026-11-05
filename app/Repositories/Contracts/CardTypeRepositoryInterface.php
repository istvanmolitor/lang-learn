<?php

namespace App\Repositories\Contracts;

use App\Models\CardType;
use Illuminate\Support\Collection;

/**
 * @extends RepositoryInterface<CardType>
 */
interface CardTypeRepositoryInterface extends RepositoryInterface
{
    /**
     * @param  array<int, string>  $columns
     * @return Collection<int, CardType>
     */
    public function allOrderedByName(array $columns = ['*']): Collection;

    /**
     * @return Collection<int, CardType>
     */
    public function allWithCardsCountOrderedByName(): Collection;

    public function findByCode(string $code): ?CardType;
}
