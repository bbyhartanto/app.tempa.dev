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
        Schema::table('orders', function (Blueprint $table) {
            // Module discriminator
            $table->string('module_type')->default('catalog')->after('tenant_id'); // 'catalog' | 'booking'

            // Polymorphic relation to Product or Service
            $table->string('orderable_type')->nullable()->after('module_type'); // 'App\Models\Product' | 'App\Models\Service'
            $table->unsignedBigInteger('orderable_id')->nullable()->after('orderable_type');

            // Booking-specific fields (nullable, only used when module_type = 'booking')
            $table->date('booking_date')->nullable()->after('shipping_receipt');
            $table->string('booking_time_slot')->nullable()->after('booking_date'); // e.g. "14:00-14:30"
            $table->unsignedInteger('booking_duration_min')->nullable()->after('booking_time_slot');

            // Index for module_type filtering
            $table->index('module_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['module_type']);
            $table->dropColumn([
                'module_type',
                'orderable_type',
                'orderable_id',
                'booking_date',
                'booking_time_slot',
                'booking_duration_min',
            ]);
        });
    }
};
