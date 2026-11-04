<?php

namespace App\Models;

use Database\Factories\ParagraphFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $article_id
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['article_id', 'position'])]
class Paragraph extends Model
{
    /** @use HasFactory<ParagraphFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Article, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * @return HasMany<Sentence, $this>
     */
    public function sentences(): HasMany
    {
        return $this->hasMany(Sentence::class)->orderBy('position');
    }
}
