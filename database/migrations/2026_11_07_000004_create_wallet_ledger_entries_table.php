<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('entry_reference', 64)->unique();
            $table->string('idempotency_key', 100)->unique();
            $table->string('entry_type', 60)->index();
            $table->enum('balance_bucket', ['cash', 'bonus', 'locked_withdrawal'])->index();
            $table->decimal('amount_usd', 18, 2);
            $table->decimal('cash_balance_before_usd', 18, 2)->default(0.00);
            $table->decimal('cash_balance_after_usd', 18, 2)->default(0.00);
            $table->decimal('bonus_balance_before_usd', 18, 2)->default(0.00);
            $table->decimal('bonus_balance_after_usd', 18, 2)->default(0.00);
            $table->decimal('locked_balance_before_usd', 18, 2)->default(0.00);
            $table->decimal('locked_balance_after_usd', 18, 2)->default(0.00);
            $table->string('related_type', 60)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('description_en', 255);
            $table->string('description_id', 255);
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->index();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_ledger_entries');
    }
};
