<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\BookPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookPage>
 */
class BookPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'position' => 0,
        ];
    }
}
