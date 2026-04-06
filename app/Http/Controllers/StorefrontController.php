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
            ],
            'products' => $products,
            'templateConfig' => $templateConfig,
        ]);
    }

    /**
     * Catalog Page - Product Grid with Cart
     * Full product catalog with WhatsApp ordering
     * Supports pagination for large product sets (12 per page)
     */
    public function catalog(Request $request): Response
    {
        $tenant = $request->attributes->get('tenant');
        $perPage = 10; // Load 10 products at a time for infinite scroll

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

        $templateConfig = $this->templateEngine->getConfig($tenant);

        return Inertia::render('Storefront/Catalog', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'store_link' => $tenant->store_link,
                'whatsapp_number' => $tenant->whatsapp_number,
                'formatted_whatsapp_number' => $tenant->formatted_whatsapp_number,
            ],
            'products' => $products,
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
}
