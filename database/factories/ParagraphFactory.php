<?php

namespace Database\Factories;

use App\Models\BookPage;
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
            'page_id' => BookPage::factory(),
            'position' => 0,
        ];
    }
}
