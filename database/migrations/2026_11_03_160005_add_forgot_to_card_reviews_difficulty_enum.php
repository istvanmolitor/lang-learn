<?php

use App\Enums\CardDifficulty;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('card_reviews', function (Blueprint $table) {
            $table->enum('difficulty', array_column(CardDifficulty::cases(), 'value'))->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_reviews', function (Blueprint $table) {
            $table->enum('difficulty', ['hard', 'medium', 'easy'])->change();
        });
    }
};
