<?php

namespace Database\Factories;

use App\Models\Paragraph;
use App\Models\Sentence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sentence>
 */
class SentenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'paragraph_id' => Paragraph::factory(),
            'position' => 0,
            'text' => fake()->sentence(),
        ];
    }
}
