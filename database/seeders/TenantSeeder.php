<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Tenant Seeder
 *
 * Creates sample tenant stores for testing.
 */
class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Sample Store 1: Coffee Shop
        $coffeeShop = Tenant::create([
            'name' => 'Kopi Senja',
            'slug' => 'kopi-senja',
            'store_link' => 'kopi-senja',
            'email' => 'hello@kopisenja.com',
            'phone' => '+6281234567890',
            'whatsapp_number' => '+6281234567890',
            'logo_url' => null,
            'background_image' => null,
            'description' => 'Premium local coffee beans, roasted to perfection. Order directly from our roastery.',
            'address' => 'Jl. Kopi No. 123',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'streetname' => 'Kebayoran Baru',
            'latitude' => -6.2297465,
            'longitude' => 106.8261039,
            'google_maps_link' => 'https://maps.google.com',
            'opening_schedule' => [
                'monday' => ['open' => '08:00', 'close' => '20:00', 'closed' => false],
                'tuesday' => ['open' => '08:00', 'close' => '20:00', 'closed' => false],
                'wednesday' => ['open' => '08:00', 'close' => '20:00', 'closed' => false],
                'thursday' => ['open' => '08:00', 'close' => '20:00', 'closed' => false],
                'friday' => ['open' => '08:00', 'close' => '22:00', 'closed' => false],
                'saturday' => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
                'sunday' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            ],
            'template_slug' => 'minimal',
            'settings' => [
                'colors' => ['primary' => '#78350F'],
            ],
            'status' => 'active',
            'approved_at' => now(),
        ]);

        // Create user account for coffee shop owner
        User::create([
            'name' => 'Kopi Senja Owner',
            'email' => 'hello@kopisenja.com',
            'password' => Hash::make('password'),
            'role' => 'tenant_owner',
            'email_verified_at' => now(),
        ]);

        // Add products for coffee shop
        Product::create([
            'tenant_id' => $coffeeShop->id,
            'title' => 'Arabica Gayo',
            'slug' => 'arabica-gayo',
            'description' => 'Premium single-origin Arabica from Aceh Gayo. Notes of chocolate and citrus.',
            'price' => 85000,
            'currency' => 'IDR',
            'images' => [],
            'is_available' => true,
            'sort_order' => 1,
        ]);

        Product::create([
            'tenant_id' => $coffeeShop->id,
            'title' => 'Robusta Lampung',
            'slug' => 'robusta-lampung',
            'description' => 'Bold and strong Robusta from Lampung. Perfect for espresso blend.',
            'price' => 55000,
            'currency' => 'IDR',
            'images' => [],
            'is_available' => true,
            'sort_order' => 2,
        ]);

        Product::create([
            'tenant_id' => $coffeeShop->id,
            'title' => 'House Blend',
            'slug' => 'house-blend',
            'description' => 'Our signature blend of Arabica and Robusta. Balanced and smooth.',
            'price' => 70000,
            'currency' => 'IDR',
            'images' => [],
            'is_available' => true,
            'sort_order' => 3,
        ]);

        // Sample Store 2: Fashion Boutique
        $boutique = Tenant::create([
            'name' => 'BajuKita',
            'slug' => 'bajukita',
            'store_link' => 'bajukita',
            'email' => 'hello@bajukita.com',
            'phone' => '+6281987654321',
            'whatsapp_number' => '+6281987654321',
            'logo_url' => null,
            'background_image' => null,
            'description' => 'Trendy local fashion for everyday wear. Made with love in Indonesia.',
            'address' => 'Jl. Fashion No. 45',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'streetname' => 'Dago',
            'latitude' => -6.8915,
            'longitude' => 107.6107,
            'google_maps_link' => 'https://maps.google.com',
            'opening_schedule' => [
                'monday' => ['open' => '10:00', 'close' => '21:00', 'closed' => false],
                'tuesday' => ['open' => '10:00', 'close' => '21:00', 'closed' => false],
                'wednesday' => ['open' => '10:00', 'close' => '21:00', 'closed' => false],
                'thursday' => ['open' => '10:00', 'close' => '21:00', 'closed' => false],
                'friday' => ['open' => '10:00', 'close' => '21:00', 'closed' => false],
                'saturday' => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
                'sunday' => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
            ],
            'template_slug' => 'modern',
            'settings' => null,
            'status' => 'active',
            'approved_at' => now(),
        ]);

        // Create user account for boutique owner
        User::create([
            'name' => 'BajuKita Owner',
            'email' => 'hello@bajukita.com',
            'password' => Hash::make('password'),
            'role' => 'tenant_owner',
            'email_verified_at' => now(),
        ]);

        Product::create([
            'tenant_id' => $boutique->id,
            'title' => 'Kemeja Flanel Classic',
            'slug' => 'kemeja-flanel-classic',
            'description' => 'Classic flannel shirt, perfect for casual and semi-formal occasions.',
            'price' => 199000,
            'currency' => 'IDR',
            'images' => [],
            'is_available' => true,
            'sort_order' => 1,
        ]);

        Product::create([
            'tenant_id' => $boutique->id,
            'title' => 'Kaos Polos Premium',
            'slug' => 'kaos-polos-premium',
            'description' => 'Premium cotton t-shirt, comfortable for all-day wear.',
            'price' => 79000,
            'currency' => 'IDR',
            'images' => [],
            'is_available' => true,
            'sort_order' => 2,
        ]);

        // Sample Store 3: Pending tenant (awaiting approval)
        Tenant::create([
            'name' => 'Toko Baru',
            'slug' => 'toko-baru',
            'store_link' => 'toko-baru',
            'email' => 'new@store.com',
            'phone' => '+6281111111111',
            'whatsapp_number' => '+6281111111111',
            'template_slug' => 'minimal',
            'status' => 'pending',
            'approved_at' => null,
        ]);
    }
}
