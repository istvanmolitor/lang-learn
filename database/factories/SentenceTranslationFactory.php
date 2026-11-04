<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\Sentence;
use App\Models\SentenceTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SentenceTranslation>
 */
class SentenceTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sentence_id' => Sentence::factory(),
            'language_id' => Language::factory(),
            'text' => fake()->sentence(),
        ];
    }
}
