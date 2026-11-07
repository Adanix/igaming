<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained('game_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->string('round_code', 64)->unique();
            $table->string('idempotency_key', 100)->unique();
            $table->decimal('bet_amount_usd', 18, 2);
            $table->decimal('cash_bet_component_usd', 18, 2)->default(0.00);
            $table->decimal('bonus_bet_component_usd', 18, 2)->default(0.00);
            $table->decimal('payout_amount_usd', 18, 2)->default(0.00);
            $table->decimal('multiplier', 10, 2)->default(0.00);
            $table->enum('outcome_type', ['loss', 'win', 'big_win', 'rolled_back'])->default('loss');
            $table->enum('status', ['bet_placed', 'settled', 'rolled_back'])->default('bet_placed')->index();
            $table->string('provider_signature', 128)->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_rounds');
    }
};
