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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');

            // Basic info
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency')->default('IDR');

            // Service configuration
            $table->unsignedInteger('duration_min')->default(30); // Session duration
            $table->unsignedInteger('buffer_min')->default(0); // Buffer between sessions
            $table->json('time_slots')->nullable(); // Custom time slots override
            $table->json('available_days')->nullable(); // [1,2,3,4,5] (Mon-Fri default)
            $table->time('default_start')->default('09:00:00');
            $table->time('default_end')->default('18:00:00');

            // Visibility
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            // Images
            $table->json('image_urls')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
