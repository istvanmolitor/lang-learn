<?php

namespace App\Models;

use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Card extends Model
{
    /** @use HasFactory<CardFactory> */
    use HasFactory;

    /**
     * @return HasMany<CardTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CardTranslation::class);
    }

    /**
     * @return BelongsToMany<CardGroup, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(CardGroup::class, 'card_card_group');
    }

    /**
     * @return HasMany<CardReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(CardReview::class);
    }

    /**
     * Create, update, or remove the card's translations based on the given rows, one per language.
     *
     * @param  array<int, array{language_id: int, text: string|null}>  $translations
     */
    public function syncTranslations(array $translations): void
    {
        foreach ($translations as $translation) {
            if (filled($translation['text'] ?? null)) {
                $this->translations()->updateOrCreate(
                    ['language_id' => $translation['language_id']],
                    ['text' => $translation['text']],
                );
            } else {
                $this->translations()->where('language_id', $translation['language_id'])->delete();
            }
        }
    }
}
