<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English'],
            ['code' => 'hu', 'name' => 'Hungarian', 'native_name' => 'Magyar'],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch'],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français'],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español'],
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano'],
        ];

        foreach ($languages as $language) {
            Language::query()->updateOrCreate(['code' => $language['code']], $language);
        }
    }
}
