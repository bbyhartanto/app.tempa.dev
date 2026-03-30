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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // URL-friendly identifier
            $table->string('store_link')->unique(); // For IG bio links (e.g., /mystore)
            $table->string('email')->unique();
            $table->string('phone')->nullable(); // E.164 format
            $table->string('whatsapp_number')->nullable(); // E.164 format
            $table->string('logo_url')->nullable();
            $table->string('background_image')->nullable();
            $table->text('description')->nullable();
            $table->string('google_maps_link')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('streetname')->nullable();
            $table->decimal('latitude', 10, 8)->nullable(); // Coordinates
            $table->decimal('longitude', 11, 8)->nullable();
            // Opening schedule: {mon: {open: "09:00", close: "18:00", closed: false}, ...}
            $table->json('opening_schedule')->nullable();
            $table->string('template_slug')->default('minimal');
            $table->json('settings')->nullable(); // Template-specific settings
            $table->enum('status', ['pending', 'active', 'suspended'])->default('pending');
            $table->timestamp('approved_at')->nullable(); // For super admin approval
            $table->rememberToken();
            $table->timestamps();

            // Indexes for tenant resolution
            $table->index('store_link');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
