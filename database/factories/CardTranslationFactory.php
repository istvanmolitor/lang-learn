<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\CardTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CardTranslation>
 */
class CardTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'card_id' => Card::factory(),
            'language_id' => Language::factory(),
            'text' => fake()->word(),
        ];
    }
}
