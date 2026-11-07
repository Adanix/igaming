<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_provider_id')->constrained('game_providers')->onDelete('cascade');
            $table->string('slug', 120)->unique();
            $table->string('game_code', 60)->unique();
            $table->string('title', 120);
            $table->enum('category', ['slots', 'live_casino', 'table_games', 'crash_instant'])->index();
            $table->decimal('rtp_percentage', 5, 2)->default(96.00);
            $table->enum('volatility', ['low', 'medium', 'high'])->default('medium');
            $table->decimal('min_bet_usd', 18, 2)->default(0.20);
            $table->decimal('max_bet_usd', 18, 2)->default(500.00);
            $table->decimal('wagering_contribution_pct', 5, 2)->default(100.00);
            $table->boolean('is_featured')->default(false)->index();
            $table->enum('status', ['active', 'maintenance'])->default('active')->index();
            $table->string('thumbnail_gradient', 120)->nullable();
            $table->timestamps();

            $table->index(['category', 'status', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
