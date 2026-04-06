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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Product information (snapshot)
            $table->foreignId('product_id')->nullable(); // NULL if product deleted
            $table->string('product_name'); // Immutable snapshot
            $table->string('product_sku')->nullable(); // Immutable snapshot
            
            // Quantity tracking
            $table->unsignedInteger('original_quantity'); // What customer ordered (immutable)
            $table->unsignedInteger('current_quantity'); // After tenant adjustment
            
            // Pricing (immutable)
            $table->decimal('unit_price', 10, 2); // Price at order time
            $table->string('currency')->default('IDR');
            
            // Soft delete for audit trail
            $table->boolean('is_removed')->default(false);
            $table->timestamp('removed_at')->nullable();
            
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
