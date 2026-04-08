<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add subscription request tracking fields to tenants table.
     * Allows tenants to request subscription activation and admins to approve/reject.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('subscription_request_status', ['none', 'pending', 'approved', 'rejected'])
                ->default('none')
                ->after('subscription_status');
            
            $table->foreignId('requested_plan_id')
                ->nullable()
                ->constrained('subscription_plans')
                ->nullOnDelete()
                ->after('subscription_request_status');
            
            $table->string('requested_billing_cycle', 20)->nullable()->after('requested_plan_id');
            
            $table->timestamp('subscription_requested_at')->nullable()->after('requested_billing_cycle');
            
            // Indexes for performance
            $table->index('subscription_request_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex(['subscription_request_status']);
            $table->dropForeign(['requested_plan_id']);
            $table->dropColumn([
                'subscription_request_status',
                'requested_plan_id',
                'requested_billing_cycle',
                'subscription_requested_at',
            ]);
        });
    }
};
