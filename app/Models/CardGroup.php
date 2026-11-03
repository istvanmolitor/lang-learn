<?php

namespace App\Models;

use Database\Factories\CardGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name'])]
class CardGroup extends Model
{
    /** @use HasFactory<CardGroupFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<Card, $this>
     */
    public function cards(): BelongsToMany
    {
        return $this->belongsToMany(Card::class, 'card_card_group');
    }

    /**
     * Get this group's cards, with each card's latest review by the given user eager loaded.
     *
     * @return Collection<int, Card>
     */
    public function cardsWithLatestReviewFor(User $user): Collection
    {
        return $this->cards()
            ->with(['reviews' => fn ($query) => $query->where('user_id', $user->id)->latest('id')->limit(1)])
            ->get();
    }

    /**
     * Calculate the percentage of this group's cards the given user currently knows,
     * i.e. whose latest review was answered correctly (medium or easy).
     */
    public function progressPercentageFor(User $user): int
    {
        return self::progressPercentage($this->cardsWithLatestReviewFor($user));
    }

    /**
     * Calculate the percentage of the given cards whose latest review was answered correctly.
     *
     * @param  Collection<int, Card>  $cards
     */
    public static function progressPercentage(Collection $cards): int
    {
        if ($cards->isEmpty()) {
            return 0;
        }

        $known = $cards->filter(
            fn (Card $card) => ($card->reviews->first()?->repetitions ?? 0) >= 1
        )->count();

        return (int) round($known / $cards->count() * 100);
    }
}
