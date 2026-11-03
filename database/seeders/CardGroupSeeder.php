<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\CardGroup;
use App\Models\Language;
use Illuminate\Database\Seeder;

class CardGroupSeeder extends Seeder
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

        $groups = [
            'Állatok' => [
                ['en' => 'dog', 'hu' => 'kutya'],
                ['en' => 'cat', 'hu' => 'macska'],
                ['en' => 'horse', 'hu' => 'ló'],
                ['en' => 'bird', 'hu' => 'madár'],
                ['en' => 'fish', 'hu' => 'hal'],
            ],
            'Étel és ital' => [
                ['en' => 'apple', 'hu' => 'alma'],
                ['en' => 'bread', 'hu' => 'kenyér'],
                ['en' => 'water', 'hu' => 'víz'],
                ['en' => 'milk', 'hu' => 'tej'],
                ['en' => 'cheese', 'hu' => 'sajt'],
            ],
            'Számok' => [
                ['en' => 'one', 'hu' => 'egy'],
                ['en' => 'two', 'hu' => 'kettő'],
                ['en' => 'three', 'hu' => 'három'],
                ['en' => 'four', 'hu' => 'négy'],
                ['en' => 'five', 'hu' => 'öt'],
            ],
        ];

        foreach ($groups as $groupName => $cards) {
            $group = CardGroup::query()->updateOrCreate(['name' => $groupName]);

            foreach ($cards as $translations) {
                $card = Card::create();
                $card->syncTranslations([
                    ['language_id' => $english->id, 'text' => $translations['en']],
                    ['language_id' => $hungarian->id, 'text' => $translations['hu']],
                ]);
                $group->cards()->attach($card);
            }
        }
    }
}
