<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

/**
 * Template Seeder
 * 
 * Creates default storefront templates.
 * Each template defines a config_schema for customization options.
 */
class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Minimal Template - Clean, simple layout
        Template::create([
            'slug' => 'minimal',
            'name' => 'Minimal',
            'is_active' => true,
            'is_default' => true,
            'config_schema' => [
                'colors' => [
                    'primary' => '#3B82F6',
                    'secondary' => '#6B7280',
                    'accent' => '#10B981',
                ],
                'layout' => [
                    'show_prices' => true,
                    'show_description' => true,
                    'products_per_row' => 2,
                    'show_product_images' => true,
                ],
            ],
            'preview_image' => null,
        ]);

        // Modern Template - Bold, contemporary design
        Template::create([
            'slug' => 'modern',
            'name' => 'Modern',
            'is_active' => true,
            'is_default' => false,
            'config_schema' => [
                'colors' => [
                    'primary' => '#7C3AED',
                    'secondary' => '#4B5563',
                    'accent' => '#F59E0B',
                ],
                'layout' => [
                    'show_prices' => true,
                    'show_description' => true,
                    'products_per_row' => 3,
                    'show_product_images' => true,
                    'large_hero' => true,
                ],
            ],
            'preview_image' => null,
        ]);

        // Elegant Template - Premium, sophisticated look
        Template::create([
            'slug' => 'elegant',
            'name' => 'Elegant',
            'is_active' => true,
            'is_default' => false,
            'config_schema' => [
                'colors' => [
                    'primary' => '#1F2937',
                    'secondary' => '#9CA3AF',
                    'accent' => '#D4AF37', // Gold
                ],
                'layout' => [
                    'show_prices' => true,
                    'show_description' => true,
                    'products_per_row' => 2,
                    'show_product_images' => true,
                    'serif_fonts' => true,
                ],
            ],
            'preview_image' => null,
        ]);
    }
}
