<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Cleans absolute URLs from product images table, keeping only relative paths.
     * This prevents broken images when IP/domain changes.
     */
    public function up(): void
    {
        DB::table('products')->orderBy('id')->chunk(100, function ($products) {
            foreach ($products as $product) {
                $images = json_decode($product->images, true) ?: [];
                
                $cleaned = array_map(function ($img) {
                    // Remove http:// or https:// followed by domain and /storage/ prefix
                    return preg_replace('#^https?://[^/]+/storage/#', '', $img);
                }, $images);
                
                DB::table('products')->where('id', $product->id)->update([
                    'images' => json_encode($cleaned)
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse - original absolute URLs are lost
    }
};
