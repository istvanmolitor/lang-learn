<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Standard part-of-speech types, keyed by the abbreviation used in parenthesised
     * annotations like "ability (n.)" or "after (conj., adv.)".
     *
     * @var array<int, array{code: string, name: string}>
     */
    private const TYPES = [
        ['code' => 'n', 'name' => 'Főnév'],
        ['code' => 'v', 'name' => 'Ige'],
        ['code' => 'adj', 'name' => 'Melléknév'],
        ['code' => 'adv', 'name' => 'Határozószó'],
        ['code' => 'prep', 'name' => 'Elöljárószó'],
        ['code' => 'conj', 'name' => 'Kötőszó'],
        ['code' => 'pron', 'name' => 'Névmás'],
        ['code' => 'det', 'name' => 'Névelő'],
        ['code' => 'exclam', 'name' => 'Indulatszó'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('card_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();

        DB::table('card_types')->insert(array_map(
            fn (array $type) => [...$type, 'created_at' => $now, 'updated_at' => $now],
            self::TYPES,
        ));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_types');
    }
};
