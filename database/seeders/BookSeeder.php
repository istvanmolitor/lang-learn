<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Language;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $english = Language::query()->where('code', 'en')->first();
        $hungarian = Language::query()->where('code', 'hu')->first();

        if (! $english || ! $hungarian) {
            return;
        }

        $books = [
            'A Walk in the Park' => [
                [
                    ['en' => 'The sun was shining brightly.', 'hu' => 'A nap fényesen sütött.'],
                    ['en' => 'Birds were singing in the trees.', 'hu' => 'A madarak a fákon énekeltek.'],
                ],
                [
                    ['en' => 'Anna decided to go for a walk.', 'hu' => 'Anna elhatározta, hogy elmegy sétálni.'],
                    ['en' => 'She took her dog with her.', 'hu' => 'Magával vitte a kutyáját.'],
                    ['en' => 'They walked along the river.', 'hu' => 'A folyó mentén sétáltak.'],
                ],
            ],
            'The Old Market' => [
                [
                    ['en' => 'The market opens early in the morning.', 'hu' => 'A piac korán reggel nyit.'],
                    ['en' => 'Farmers sell fresh fruit and vegetables.', 'hu' => 'A gazdák friss gyümölcsöt és zöldséget árulnak.'],
                ],
                [
                    ['en' => 'People come from all over the city.', 'hu' => 'Emberek jönnek a város minden részéről.'],
                    ['en' => 'The prices are usually very good.', 'hu' => 'Az árak általában nagyon jók.'],
                ],
            ],
        ];

        foreach ($books as $title => $paragraphs) {
            $book = Book::query()->updateOrCreate(
                ['title' => $title],
                ['language_id' => $english->id],
            );

            $book->pages()->delete();

            $page = $book->pages()->create(['position' => 0]);

            foreach ($paragraphs as $paragraphPosition => $sentences) {
                $paragraph = $page->paragraphs()->create(['position' => $paragraphPosition]);

                foreach ($sentences as $sentencePosition => $translations) {
                    $sentence = $paragraph->sentences()->create([
                        'position' => $sentencePosition,
                        'text' => $translations['en'],
                    ]);

                    $sentence->translations()->create([
                        'language_id' => $hungarian->id,
                        'text' => $translations['hu'],
                    ]);
                }
            }
        }
    }
}
