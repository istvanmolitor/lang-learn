<?php

namespace App\Repositories\Eloquent;

use App\Models\Card;
use App\Models\CardGroup;
use App\Models\User;
use App\Repositories\Contracts\CardGroupRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * @extends EloquentRepository<CardGroup>
 */
class EloquentCardGroupRepository extends EloquentRepository implements CardGroupRepositoryInterface
{
    protected function modelClass(): string
    {
        return CardGroup::class;
    }

    public function allWithCardsCountOrderedByName(): Collection
    {
        return CardGroup::query()
            ->withCount('cards')
            ->orderBy('name')
            ->get();
    }

    public function allOrderedByName(array $columns = ['*']): Collection
    {
        return CardGroup::query()->orderBy('name')->get($columns);
    }

    public function cardsWithLatestReviewFor(CardGroup $group, User $user): Collection
    {
        return $group->cards()
            ->with(['reviews' => fn ($query) => $query->where('user_id', $user->id)->latest('id')->limit(1)])
            ->get();
    }

    public function progressPercentageFor(CardGroup $group, User $user): int
    {
        return CardGroup::progressPercentage($this->cardsWithLatestReviewFor($group, $user));
    }

    public function cardsWithTranslationsOrderedById(CardGroup $group): Collection
    {
        return $group->cards()->with('translations')->orderBy('id')->get();
    }

    public function findCardInGroupOrFail(CardGroup $group, int $cardId): Card
    {
        return $group->cards()->whereKey($cardId)->firstOrFail();
    }

    public function firstOrCreateByName(string $name): CardGroup
    {
        return CardGroup::query()->firstOrCreate(['name' => $name]);
    }

    public function attachCard(CardGroup $group, Card $card): void
    {
        $group->cards()->syncWithoutDetaching($card->id);
    }
}
