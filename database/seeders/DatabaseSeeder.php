<?php

namespace Database\Seeders;

use App\Enums\LanguageLevel;
use App\Models\Language;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(LanguageSeeder::class);

        $hungarian = Language::query()->where('code', 'hu')->first();
        $english = Language::query()->where('code', 'en')->first();

        $admin = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'language_id' => $hungarian?->id,
            'selected_language_id' => $english?->id,
        ]);

        if ($english) {
            $admin->languages()->attach($english->id, ['level' => LanguageLevel::B1]);
        }

        $this->call(CardGroupSeeder::class);
    }
}
