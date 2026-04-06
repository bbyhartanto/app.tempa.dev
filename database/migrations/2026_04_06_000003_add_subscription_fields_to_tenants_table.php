<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('subscription_status', ['trial', 'subscribed', 'grace_period', 'expired'])
                ->default('trial')
                ->after('status');
            
            $table->timestamp('trial_started_at')->nullable()->after('subscription_status');
            $table->timestamp('trial_ends_at')->nullable()->after('trial_started_at');
            
            $table->foreignId('current_subscription_id')
                ->nullable()
                ->constrained('tenant_subscriptions')
                ->nullOnDelete()
                ->after('trial_ends_at');
            
            $table->integer('item_limit')->default(25)->after('current_subscription_id');
            
            // Indexes for performance
            $table->index('subscription_status');
            $table->index('trial_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex(['subscription_status']);
            $table->dropIndex(['trial_ends_at']);
            
            $table->dropForeign(['current_subscription_id']);
            $table->dropColumn([
                'subscription_status',
                'trial_started_at',
                'trial_ends_at',
                'current_subscription_id',
                'item_limit',
            ]);
        });
    }
};
