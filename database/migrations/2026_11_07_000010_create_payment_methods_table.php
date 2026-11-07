<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name_en', 100);
            $table->string('name_id', 100);
            $table->enum('channel_type', ['crypto', 'card', 'bank_wire', 'ewallet'])->index();
            $table->boolean('supports_deposit')->default(true);
            $table->boolean('supports_withdrawal')->default(true);
            $table->decimal('min_deposit_usd', 18, 2)->default(10.00);
            $table->decimal('max_deposit_usd', 18, 2)->default(50000.00);
            $table->decimal('min_withdrawal_usd', 18, 2)->default(20.00);
            $table->decimal('max_withdrawal_usd', 18, 2)->default(25000.00);
            $table->enum('status', ['active', 'maintenance'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
