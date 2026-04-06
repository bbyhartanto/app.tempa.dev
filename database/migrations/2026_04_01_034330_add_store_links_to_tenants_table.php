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
            // Store external platform links (GrabFood, ShopeeFood, etc.)
            // Structure: [
            //   ['label' => 'GrabFood', 'url' => 'https://...', 'icon' => 'grabfood', 'order' => 1],
            //   ['label' => 'ShopeeFood', 'url' => 'https://...', 'icon' => 'shopee', 'order' => 2]
            // ]
            $table->json('store_links')->nullable()->after('settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('store_links');
        });
    }
};
