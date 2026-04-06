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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->enum('tier', ['basic', 'standard']);
            $table->enum('billing_cycle', ['3_months', '1_year']);
            $table->decimal('price', 12, 2);
            $table->integer('item_limit');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Ensure unique combination of tier and billing_cycle
            $table->unique(['tier', 'billing_cycle']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
