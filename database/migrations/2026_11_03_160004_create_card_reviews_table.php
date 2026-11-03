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
        Schema::create('card_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->enum('difficulty', array_column(CardDifficulty::cases(), 'value'));
            $table->decimal('ease_factor', 4, 2);
            $table->unsignedInteger('interval_days');
            $table->unsignedInteger('repetitions');
            $table->timestamp('due_at');
            $table->timestamps();

            $table->index(['user_id', 'card_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_reviews');
    }
};
