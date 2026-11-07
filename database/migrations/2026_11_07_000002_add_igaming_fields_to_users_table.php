<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['player', 'affiliate', 'ops_staff', 'super_admin'])->default('player')->index()->after('email');
            $table->string('persona_key', 50)->nullable()->unique()->after('role');
            $table->string('locale', 5)->default('en')->after('persona_key');
            $table->enum('kyc_status', ['unverified', 'pending', 'verified', 'rejected'])->default('verified')->after('locale');
            $table->enum('account_status', ['active', 'suspended', 'self_excluded'])->default('active')->after('kyc_status');
            $table->foreignId('vip_tier_id')->nullable()->constrained('vip_tiers')->nullOnDelete()->after('account_status');
            $table->decimal('vip_points', 18, 2)->default(0.00)->after('vip_tier_id');
            $table->decimal('daily_deposit_limit_usd', 18, 2)->nullable()->after('vip_points');
            $table->unsignedBigInteger('referred_by_affiliate_id')->nullable()->index()->after('daily_deposit_limit_usd');
            $table->boolean('mfa_enabled')->default(false)->after('referred_by_affiliate_id');
            $table->timestamp('last_login_at')->nullable()->after('mfa_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['vip_tier_id']);
            $table->dropColumn([
                'role',
                'persona_key',
                'locale',
                'kyc_status',
                'account_status',
                'vip_tier_id',
                'vip_points',
                'daily_deposit_limit_usd',
                'referred_by_affiliate_id',
                'mfa_enabled',
                'last_login_at',
            ]);
        });
    }
};
