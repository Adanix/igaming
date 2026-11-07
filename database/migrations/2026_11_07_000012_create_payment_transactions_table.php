<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
            $table->string('reference', 64)->unique();
            $table->string('idempotency_key', 100)->unique();
            $table->enum('type', ['deposit', 'withdrawal', 'affiliate_payout'])->index();
            $table->char('currency', 3)->default('USD');
            $table->decimal('amount_usd', 18, 2);
            $table->decimal('fee_usd', 18, 2)->default(0.00);
            $table->decimal('net_amount_usd', 18, 2);
            $table->enum('status', ['pending', 'pending_review', 'paid', 'failed', 'expired', 'cancelled', 'rejected'])->default('pending')->index();
            $table->string('destination_account', 180)->nullable();
            $table->boolean('is_high_value')->default(false)->index();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('review_note', 255)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
