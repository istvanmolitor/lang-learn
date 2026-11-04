<?php

namespace Database\Seeders;

use App\Services\CsvCardGroupImporter;
use Illuminate\Database\Seeder;

class TopicCardGroupSeeder extends Seeder
{
    /**
     * CSV file name (in database/seeders/data) mapped to the card group name it seeds.
     *
     * @var array<string, string>
     */
    private const TOPICS = [
        'topic-emberek-es-foglalkozasok.csv' => 'Emberek és foglalkozások',
        'topic-uzlet-munka-es-penz.csv' => 'Üzlet, munka és pénz',
        'topic-otthon-targyak-es-oltozkodes.csv' => 'Otthon, tárgyak és öltözködés',
        'topic-etkezes-egeszseg-es-test.csv' => 'Étkezés, egészség és test',
        'topic-termeszet-allatok-es-idojaras.csv' => 'Természet, állatok és időjárás',
        'topic-utazas-kozlekedes-es-helyek.csv' => 'Utazás, közlekedés és helyek',
        'topic-technologia-media-es-kommunikacio.csv' => 'Technológia, média és kommunikáció',
        'topic-oktatas-tudomany-es-gondolkodas.csv' => 'Oktatás, tudomány és gondolkodás',
        'topic-erzelmek-es-tulajdonsagok.csv' => 'Érzelmek és tulajdonságok',
        'topic-hatarozoszok-es-nyelvtani-szavak.csv' => 'Határozószók és nyelvtani szavak',
    ];

    /**
     * Run the database seeds.
     */
    public function run(CsvCardGroupImporter $importer): void
    {
        foreach (self::TOPICS as $file => $groupName) {
            $importer->import(database_path("seeders/data/{$file}"), $groupName);
        }
    }
}
