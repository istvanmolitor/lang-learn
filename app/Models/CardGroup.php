<?php

namespace App\Models;

use Database\Factories\CardGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

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
}
