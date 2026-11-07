<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('referral_code', 40)->unique();
            $table->decimal('revshare_percentage', 5, 2)->default(30.00);
            $table->decimal('commission_balance_usd', 18, 2)->default(0.00);
            $table->decimal('lifetime_earned_usd', 18, 2)->default(0.00);
            $table->decimal('lifetime_paid_usd', 18, 2)->default(0.00);
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_profiles');
    }
};
