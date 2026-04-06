<?php

use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Update all existing tenants to have trial status if they don't have subscription data yet.
     */
    public function up(): void
    {
        // Update all tenants that have null subscription_status
        Tenant::whereNull('subscription_status')
            ->orWhere('subscription_status', '')
            ->update([
                'subscription_status' => 'trial',
                'trial_started_at' => now(),
                'trial_ends_at' => now()->addDays(7),
                'item_limit' => 25,
            ]);

        DB::table('tenants')
            ->whereNull('subscription_status')
            ->orWhere('subscription_status', '')
            ->update([
                'subscription_status' => 'trial',
                'trial_started_at' => now(),
                'trial_ends_at' => now()->addDays(7),
                'item_limit' => 25,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed
    }
};
