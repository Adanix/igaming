<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_profile_id')->constrained('affiliate_profiles')->onDelete('cascade');
            $table->string('period_label', 40);
            $table->decimal('gross_ggr_usd', 18, 2)->default(0.00);
            $table->decimal('net_ngr_usd', 18, 2)->default(0.00);
            $table->decimal('revshare_pct', 5, 2)->default(30.00);
            $table->decimal('commission_amount_usd', 18, 2)->default(0.00);
            $table->enum('status', ['credited', 'paid_out'])->default('credited');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
