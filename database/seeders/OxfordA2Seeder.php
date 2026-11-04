<?php

namespace Database\Seeders;

use App\Services\CsvCardGroupImporter;
use Illuminate\Database\Seeder;

class OxfordA2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(CsvCardGroupImporter $importer): void
    {
        $importer->import(
            database_path('seeders/data/oxford-3000-a2.csv'),
            'Oxford 3000 - A2',
        );
    }
}
