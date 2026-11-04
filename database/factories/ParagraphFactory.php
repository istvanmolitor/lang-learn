<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Paragraph;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paragraph>
 */
class ParagraphFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'position' => 0,
        ];
    }
}
