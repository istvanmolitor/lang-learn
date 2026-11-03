<?php

namespace App\Models;

use App\Enums\CardDifficulty;
use Database\Factories\CardReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $card_id
 * @property CardDifficulty $difficulty
 * @property float $ease_factor
 * @property int $interval_days
 * @property int $repetitions
 * @property Carbon $due_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'card_id', 'difficulty', 'ease_factor', 'interval_days', 'repetitions', 'due_at'])]
class CardReview extends Model
{
    /** @use HasFactory<CardReviewFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'difficulty' => CardDifficulty::class,
            'ease_factor' => 'float',
            'due_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Card, $this>
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
