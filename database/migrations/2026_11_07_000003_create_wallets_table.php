<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->char('currency', 3)->default('USD');
            $table->decimal('cash_balance_usd', 18, 2)->default(0.00);
            $table->decimal('bonus_balance_usd', 18, 2)->default(0.00);
            $table->decimal('locked_withdrawal_usd', 18, 2)->default(0.00);
            $table->decimal('lifetime_deposited_usd', 18, 2)->default(0.00);
            $table->decimal('lifetime_withdrawn_usd', 18, 2)->default(0.00);
            $table->decimal('lifetime_wagered_usd', 18, 2)->default(0.00);
            $table->decimal('lifetime_won_usd', 18, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
