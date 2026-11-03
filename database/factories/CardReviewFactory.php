<?php

namespace Database\Factories;

use App\Enums\CardDifficulty;
use App\Models\Card;
use App\Models\CardReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CardReview>
 */
class CardReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'card_id' => Card::factory(),
            'difficulty' => CardDifficulty::Medium,
            'ease_factor' => 2.5,
            'interval_days' => 1,
            'repetitions' => 1,
            'due_at' => now(),
        ];
    }
}
