<?php

namespace App\Models;

use Database\Factories\WordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $word
 * @property int $language_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['word', 'language_id'])]
class Word extends Model
{
    /** @use HasFactory<WordFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Language, $this>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return BelongsToMany<Sentence, $this>
     */
    public function sentences(): BelongsToMany
    {
        return $this->belongsToMany(Sentence::class, 'sentence_word');
    }

    /**
     * @return BelongsToMany<Card, $this>
     */
    public function cards(): BelongsToMany
    {
        return $this->belongsToMany(Card::class, 'card_word');
    }
}
