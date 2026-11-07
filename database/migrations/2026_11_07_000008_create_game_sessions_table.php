<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->string('session_token', 80)->unique();
            $table->char('currency', 3)->default('USD');
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->timestamp('started_at');
            $table->timestamp('last_activity_at');
            $table->timestamps();

            $table->index(['user_id', 'game_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
