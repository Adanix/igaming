<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_profile_id')->constrained('affiliate_profiles')->onDelete('cascade');
            $table->foreignId('referred_user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->timestamp('attributed_at');
            $table->decimal('total_bets_usd', 18, 2)->default(0.00);
            $table->decimal('total_wins_usd', 18, 2)->default(0.00);
            $table->decimal('net_ggr_usd', 18, 2)->default(0.00);
            $table->decimal('bonus_deductions_usd', 18, 2)->default(0.00);
            $table->decimal('net_ngr_usd', 18, 2)->default(0.00);
            $table->decimal('commission_generated_usd', 18, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_referrals');
    }
};
