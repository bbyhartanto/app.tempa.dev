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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Order identification
            $table->string('order_number')->unique();
            
            // Customer information
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address')->nullable();
            
            // Status tracking
            $table->string('status')->default('pending'); // pending, confirmed, processing, shipped, completed, cancelled
            $table->string('payment_status')->default('unpaid'); // unpaid, paid
            
            // Pricing (mutable totals)
            $table->decimal('original_subtotal', 10, 2); // Customer's original cart total (immutable)
            $table->decimal('adjusted_subtotal', 10, 2); // After tenant modifications
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2); // adjusted_subtotal + shipping_cost
            
            // Tenant additions
            $table->text('payment_notes')->nullable();
            $table->string('shipping_receipt')->nullable(); // File path to uploaded receipt
            $table->text('adjustment_notes')->nullable(); // Why items were changed
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['tenant_id', 'status']);
            $table->index('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
