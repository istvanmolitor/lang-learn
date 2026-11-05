<?php

namespace App\Models;

use Database\Factories\SentenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $paragraph_id
 * @property int $position
 * @property string $text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['paragraph_id', 'position', 'text'])]
class Sentence extends Model
{
    /** @use HasFactory<SentenceFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Paragraph, $this>
     */
    public function paragraph(): BelongsTo
    {
        return $this->belongsTo(Paragraph::class);
    }

    /**
     * @return HasMany<SentenceTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(SentenceTranslation::class);
    }

    /**
     * @return BelongsToMany<Word, $this>
     */
    public function words(): BelongsToMany
    {
        return $this->belongsToMany(Word::class, 'sentence_word');
    }
}
