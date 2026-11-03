<?php

namespace App\Services;

use App\Enums\CardDifficulty;
use App\Models\CardReview;
use Carbon\CarbonImmutable;

class SpacedRepetitionScheduler
{
    private const DEFAULT_EASE_FACTOR = 2.5;

    private const MIN_EASE_FACTOR = 1.3;

    private const MAX_EASE_FACTOR = 3.0;

    /**
     * Work out the next ease factor, interval, repetitions, and due date for a card,
     * given the previous review (if any) and how difficult the latest answer was.
     *
     * @return array{ease_factor: float, interval_days: int, repetitions: int, due_at: CarbonImmutable}
     */
    public function schedule(?CardReview $previous, CardDifficulty $difficulty): array
    {
        $easeFactor = $previous === null ? self::DEFAULT_EASE_FACTOR : $previous->ease_factor;
        $intervalDays = $previous === null ? 0 : $previous->interval_days;
        $repetitions = $previous === null ? 0 : $previous->repetitions;

        [$easeFactor, $intervalDays, $repetitions] = match ($difficulty) {
            CardDifficulty::Hard => [
                max(self::MIN_EASE_FACTOR, $easeFactor - 0.2),
                1,
                0,
            ],
            CardDifficulty::Medium => [
                $easeFactor,
                match (true) {
                    $repetitions === 0 => 1,
                    $repetitions === 1 => 3,
                    default => (int) ceil($intervalDays * $easeFactor),
                },
                $repetitions + 1,
            ],
            CardDifficulty::Easy => [
                min(self::MAX_EASE_FACTOR, $easeFactor + 0.15),
                $repetitions === 0 ? 3 : (int) ceil(max($intervalDays, 1) * $easeFactor * 1.3),
                $repetitions + 1,
            ],
        };

        return [
            'ease_factor' => $easeFactor,
            'interval_days' => $intervalDays,
            'repetitions' => $repetitions,
            'due_at' => now()->addDays($intervalDays),
        ];
    }
}
