<?php

namespace App\Models;

use Database\Factories\CardTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $card_id
 * @property int $language_id
 * @property string $text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['language_id', 'text'])]
class CardTranslation extends Model
{
    /** @use HasFactory<CardTranslationFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Card, $this>
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * @return BelongsTo<Language, $this>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
