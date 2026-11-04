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
