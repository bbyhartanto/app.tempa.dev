<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Product;
use App\Services\TemplateEngine;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Storefront Controller
 * 
 * Handles public storefront pages accessed via /{store_link}
 * 
 * Security: Tenant is resolved by ResolveTenant middleware
 * and passed via request attribute.
 */
class StorefrontController extends Controller
{
    public function __construct(
        private TemplateEngine $templateEngine
    ) {}

    /**
     * Display storefront home page
     */
    public function index(Request $request): Response
    {
        $tenant = $request->attributes->get('tenant');

        // Get available products for this tenant
        $products = $tenant->availableProducts()
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'description' => $p->description,
                'price' => (float) $p->price,
                'formatted_price' => $p->formatted_price,
                'currency' => $p->currency,
                'images' => $p->images ?? [],
                'first_image' => $p->first_image,
                'is_available' => $p->is_available,
            ]);

        // Get template configuration
        $templateConfig = $this->templateEngine->getConfig($tenant);

        return Inertia::render('Storefront/Home', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'store_link' => $tenant->store_link,
                'logo_url' => $tenant->logo_url,
                'background_image' => $tenant->background_image,
                'description' => $tenant->description,
                'whatsapp_number' => $tenant->whatsapp_number,
                'formatted_whatsapp_number' => $tenant->formatted_whatsapp_number,
                'address' => $tenant->address,
                'city' => $tenant->city,
                'province' => $tenant->province,
                'google_maps_link' => $tenant->google_maps_link,
                'latitude' => $tenant->latitude,
                'longitude' => $tenant->longitude,
                'opening_schedule' => $tenant->opening_schedule,
                'is_open_now' => $tenant->isOpenNow(),
            ],
            'products' => $products,
            'templateConfig' => $templateConfig,
        ]);
    }

    /**
     * Display product detail page
     */
    public function showProduct(Request $request, string $productSlug): Response
    {
        $tenant = $request->attributes->get('tenant');

        $product = $tenant->products()
            ->where('slug', $productSlug)
            ->first();

        if (!$product || !$product->is_available) {
            abort(404, 'Product not found');
        }

        $templateConfig = $this->templateEngine->getConfig($tenant);

        return Inertia::render('Storefront/Product', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'store_link' => $tenant->store_link,
                'whatsapp_number' => $tenant->whatsapp_number,
                'formatted_whatsapp_number' => $tenant->formatted_whatsapp_number,
            ],
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => (float) $product->price,
                'formatted_price' => $product->formatted_price,
                'currency' => $product->currency,
                'images' => $product->images ?? [],
                'is_available' => $product->is_available,
            ],
            'templateConfig' => $templateConfig,
        ]);
    }
}
