<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Product;
use App\Models\Service;
use App\Services\TemplateEngine;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Storefront Controller
 * 
 * Handles public storefront pages:
 * - Home: Link aggregator (GrabFood, Shopee, etc.)
 * - Catalog: Product grid with cart
 */
class StorefrontController extends Controller
{
    public function __construct(
        private TemplateEngine $templateEngine
    ) {}

    /**
     * Home Page - Link Aggregator
     * First landing page with store info and external platform links
     */
    public function home(Request $request): Response
    {
        $tenant = $request->attributes->get('tenant');

        $templateConfig = $this->templateEngine->getConfig($tenant);

        // Get featured products for home page (max 4)
        $products = $tenant->availableProducts()
            ->limit(4)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'description' => $p->description,
                'price' => (float) $p->price,
                'formatted_price' => $p->formatted_price,
                'currency' => $p->currency,
                'images' => $p->image_urls,
                'first_image' => $p->first_image,
                'is_available' => $p->is_available,
            ]);

        // Get featured services (if booking enabled)
        $services = [];
        $totalServices = 0;
        if ($tenant->hasBooking()) {
            $totalServices = $tenant->availableServices()->count();
            $services = $tenant->availableServices()
                ->limit(4)
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'slug' => $s->slug,
                    'description' => $s->description,
                    'price' => (float) $s->price,
                    'formatted_price' => $s->formatted_price,
                    'currency' => $s->currency,
                    'duration_min' => $s->duration_min,
                    'images' => $s->image_urls,
                    'first_image' => $s->first_image,
                    'is_available' => $s->is_available,
                ]);
        }

        return Inertia::render('Storefront/Home', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'store_link' => $tenant->store_link,
                'logo_url' => $tenant->logo_url,
                'description' => $tenant->description,
                'store_links' => $tenant->store_links ?? [],
                'whatsapp_number' => $tenant->whatsapp_number,
                'formatted_whatsapp_number' => $tenant->formatted_whatsapp_number,
                'modules' => $tenant->modules,
            ],
            'products' => $products,
            'services' => $services,
            'totalServices' => $totalServices,
            'templateConfig' => $templateConfig,
        ]);
    }

    /**
     * Catalog Page - Product Grid with Cart
     * Full product catalog with WhatsApp ordering
     * Supports pagination for large product sets (12 per page)
     * If both modules enabled, returns both products and services.
     */
    public function catalog(Request $request): Response
    {
        $tenant = $request->attributes->get('tenant');
        $perPage = 10; // Load 10 products at a time for infinite scroll

        $products = null;
        $services = null;

        if ($tenant->hasCatalog()) {
            $products = $tenant->availableProducts()
                ->paginate($perPage)
                ->through(fn($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'description' => $p->description,
                    'price' => (float) $p->price,
                    'formatted_price' => $p->formatted_price,
                    'currency' => $p->currency,
                    'images' => $p->image_urls,
                    'first_image' => $p->first_image,
                    'is_available' => $p->is_available,
                ]);
        }

        if ($tenant->hasBooking()) {
            $services = $tenant->availableServices()
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'slug' => $s->slug,
                    'description' => $s->description,
                    'price' => (float) $s->price,
                    'formatted_price' => $s->formatted_price,
                    'currency' => $s->currency,
                    'duration_min' => $s->duration_min,
                    'buffer_min' => $s->buffer_min,
                    'default_start' => $s->default_start,
                    'default_end' => $s->default_end,
                    'available_days' => $s->available_days,
                    'images' => $s->image_urls,
                    'first_image' => $s->first_image,
                    'is_available' => $s->is_available,
                ]);
        }

        $templateConfig = $this->templateEngine->getConfig($tenant);

        return Inertia::render('Storefront/Catalog', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'store_link' => $tenant->store_link,
                'whatsapp_number' => $tenant->whatsapp_number,
                'formatted_whatsapp_number' => $tenant->formatted_whatsapp_number,
                'modules' => $tenant->modules,
            ],
            'products' => $products,
            'services' => $services,
            'templateConfig' => $templateConfig,
        ]);
    }

    /**
     * Load More Products (for infinite scroll)
     * Returns JSON response with next page of products
     */
    public function loadMoreProducts(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $page = $request->query('page', 1);
        $perPage = 10;

        $products = $tenant->availableProducts()
            ->paginate($perPage, ['*'], 'page', $page);

        $formattedProducts = $products->through(fn($p) => [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'description' => $p->description,
            'price' => (float) $p->price,
            'formatted_price' => $p->formatted_price,
            'currency' => $p->currency,
            'images' => $p->image_urls,
            'first_image' => $p->first_image,
            'is_available' => $p->is_available,
        ]);

        return response()->json([
            'products' => $formattedProducts->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'has_more' => $products->hasMorePages(),
        ]);
    }

    /**
     * Dine-In Menu Page - Display Only (No Cart)
     * Shows only products with dine_in_enabled = true
     */
    public function dineInMenu(Request $request): Response
    {
        $tenant = $request->attributes->get('tenant');

        // Check if dine-in is enabled (requires catalog)
        if (!$tenant->hasDineIn()) {
            abort(404, 'Dine-in menu not available');
        }

        $perPage = 10;

        $products = $tenant->availableProducts()
            ->dineInEnabled()
            ->paginate($perPage)
            ->through(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'description' => $p->description,
                'price' => (float) $p->price,
                'formatted_price' => $p->formatted_price,
                'currency' => $p->currency,
                'images' => $p->image_urls,
                'first_image' => $p->first_image,
                'is_available' => $p->is_available,
            ]);

        $templateConfig = $this->templateEngine->getConfig($tenant);

        return Inertia::render('Storefront/DineInMenu', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'store_link' => $tenant->store_link,
                'whatsapp_number' => $tenant->whatsapp_number,
                'formatted_whatsapp_number' => $tenant->formatted_whatsapp_number,
                'modules' => $tenant->modules,
            ],
            'products' => $products,
            'templateConfig' => $templateConfig,
        ]);
    }

    /**
     * Load More Dine-In Products (for infinite scroll)
     * Returns JSON response with next page of dine-in enabled products
     */
    public function loadMoreDineInProducts(Request $request)
    {
        $tenant = $request->attributes->get('tenant');

        // Check if dine-in is enabled
        if (!$tenant->hasDineIn()) {
            return response()->json(['error' => 'Dine-in menu not available'], 404);
        }

        $page = $request->query('page', 1);
        $perPage = 10;

        $products = $tenant->availableProducts()
            ->dineInEnabled()
            ->paginate($perPage, ['*'], 'page', $page);

        $formattedProducts = $products->through(fn($p) => [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'description' => $p->description,
            'price' => (float) $p->price,
            'formatted_price' => $p->formatted_price,
            'currency' => $p->currency,
            'images' => $p->image_urls,
            'first_image' => $p->first_image,
            'is_available' => $p->is_available,
        ]);

        return response()->json([
            'products' => $formattedProducts->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'has_more' => $products->hasMorePages(),
        ]);
    }

    /**
     * Product Detail Page
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
                'modules' => $tenant->modules,
            ],
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => (float) $product->price,
                'formatted_price' => $product->formatted_price,
                'currency' => $product->currency,
                'images' => $product->image_urls,
                'is_available' => $product->is_available,
            ],
            'templateConfig' => $templateConfig,
        ]);
    }

    /**
     * Service Detail Page (booking)
     */
    public function showService(Request $request, string $serviceSlug): Response
    {
        $tenant = $request->attributes->get('tenant');

        $service = $tenant->services()
            ->where('slug', $serviceSlug)
            ->first();

        if (!$service || !$service->is_available) {
            abort(404, 'Service not found');
        }

        $templateConfig = $this->templateEngine->getConfig($tenant);

        return Inertia::render('Storefront/Service', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'store_link' => $tenant->store_link,
                'whatsapp_number' => $tenant->whatsapp_number,
                'formatted_whatsapp_number' => $tenant->formatted_whatsapp_number,
                'modules' => $tenant->modules,
            ],
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
                'description' => $service->description,
                'price' => (float) $service->price,
                'formatted_price' => $service->formatted_price,
                'currency' => $service->currency,
                'duration_min' => $service->duration_min,
                'buffer_min' => $service->buffer_min,
                'default_start' => $service->default_start,
                'default_end' => $service->default_end,
                'available_days' => $service->available_days,
                'images' => $service->image_urls,
                'first_image' => $service->first_image,
                'is_available' => $service->is_available,
            ],
            'templateConfig' => $templateConfig,
        ]);
    }
}
