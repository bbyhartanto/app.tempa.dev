<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Product Controller
 *
 * CRUD operations for tenant products.
 * All operations are scoped to the current tenant for security.
 */
class ProductController extends Controller
{
    /**
     * Get current tenant from authenticated user
     */
    private function getCurrentTenant(): Tenant
    {
        $user = Auth::user();
        $tenant = Tenant::where('email', $user->email)->first();
        
        if (!$tenant) {
            abort(403, 'No store associated with this account');
        }
        
        return $tenant;
    }

    /**
     * Display listing of tenant's products
     */
    public function index(Request $request): Response
    {
        $tenant = $this->getCurrentTenant();

        $products = $tenant->products()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'price' => (float) $p->price,
                'formatted_price' => $p->formatted_price,
                'currency' => $p->currency,
                'is_available' => $p->is_available,
                'dine_in_enabled' => $p->dine_in_enabled,
                'sort_order' => $p->sort_order,
                'first_image' => $p->first_image,
                'created_at' => $p->created_at->toIso8601String(),
            ]);

        return Inertia::render('Dashboard/Products/Index', [
            'products' => $products,
        ]);
    }

    /**
     * Show form for creating new product
     */
    public function create(): Response
    {
        $tenant = $this->getCurrentTenant();
        $subscriptionService = app(\App\Services\SubscriptionService::class);
        
        $currentProductCount = $tenant->products()->count();
        $itemLimit = $tenant->item_limit;
        $remainingSlots = max(0, $itemLimit - $currentProductCount);
        
        return Inertia::render('Dashboard/Products/Create', [
            'subscription' => [
                'can_add_products' => $remainingSlots > 0,
                'current_product_count' => $currentProductCount,
                'item_limit' => $itemLimit,
                'remaining_slots' => $remainingSlots,
                'subscription_status' => $tenant->subscription_status,
            ],
        ]);
    }

    /**
     * Store newly created product
     */
    public function store(Request $request)
    {
        $tenant = $this->getCurrentTenant();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,NULL,id,tenant_id,' . $tenant->id,
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'images' => 'nullable|array|max:1',
            'images.*' => 'image|mimes:jpeg,png,webp|max:10240', // Max 10MB per image
            'is_available' => 'boolean',
            'dine_in_enabled' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $baseSlug = $validated['slug'];
        $counter = 1;
        while ($tenant->products()->where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter;
            $counter++;
        }

        $validated['tenant_id'] = $tenant->id;
        $validated['is_available'] = $validated['is_available'] ?? true;
        $validated['dine_in_enabled'] = $validated['dine_in_enabled'] ?? false;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['currency'] = $validated['currency'] ?? 'IDR';

        // Handle file uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            $uploadDir = 'products/' . $tenant->id;
            foreach ($request->file('images') as $image) {
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs($uploadDir, $filename, 'public');
                $imagePaths[] = $path;
            }
        }
        $validated['images'] = $imagePaths;

        Product::create($validated);

        return redirect()->route('dashboard.products.index')
            ->with('success', 'Product created successfully');
    }

    /**
     * Display the specified product
     */
    public function show(Request $request, int $id): Response
    {
        $tenant = $this->getCurrentTenant();
        $product = $tenant->products()->findOrFail($id);

        return Inertia::render('Dashboard/Products/Show', [
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => (float) $product->price,
                'currency' => $product->currency,
                'images' => $product->image_urls,
                'is_available' => $product->is_available,
                'dine_in_enabled' => $product->dine_in_enabled,
                'sort_order' => $product->sort_order,
                'created_at' => $product->created_at->toIso8601String(),
                'updated_at' => $product->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Show form for editing the specified product
     */
    public function edit(Request $request, int $id): Response
    {
        $tenant = $this->getCurrentTenant();
        $product = $tenant->products()->findOrFail($id);

        return Inertia::render('Dashboard/Products/Edit', [
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => (float) $product->price,
                'currency' => $product->currency,
                'images' => $product->images ?? [], // Raw storage paths for editing
                'is_available' => $product->is_available,
                'dine_in_enabled' => $product->dine_in_enabled,
                'sort_order' => $product->sort_order,
            ],
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, int $id)
    {
        $tenant = $this->getCurrentTenant();
        $product = $tenant->products()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $id . ',id,tenant_id,' . $tenant->id,
            'description' => 'nullable|string|max:5000',
            'price' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'images' => 'nullable|array|max:1',
            'images.*' => 'image|mimes:jpeg,png,webp|max:10240', // Max 10MB per image
            'is_available' => 'boolean',
            'dine_in_enabled' => 'boolean',
            'sort_order' => 'integer|min:0',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'string',
        ]);

        // Handle image removal FIRST
        $currentImages = $product->images ?? [];

        if (isset($validated['remove_images'])) {
            $currentImages = array_diff($currentImages, $validated['remove_images']);

            // Delete removed images from storage
            foreach ($validated['remove_images'] as $imagePath) {
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            unset($validated['remove_images']);
        }

        // Handle file uploads (append to cleaned image list)
        if ($request->hasFile('images')) {
            $uploadDir = 'products/' . $tenant->id;

            foreach ($request->file('images') as $image) {
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs($uploadDir, $filename, 'public');
                $currentImages[] = $path;
            }
        }

        $validated['images'] = array_values($currentImages);

        $product->update($validated);

        return redirect()->route('dashboard.products.index')
            ->with('success', 'Product updated successfully');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Request $request, int $id)
    {
        $tenant = $this->getCurrentTenant();
        $product = $tenant->products()->findOrFail($id);
        
        // Delete product images from storage
        if ($product->images) {
            foreach ($product->images as $imagePath) {
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
        }
        
        $product->delete();

        return redirect()->route('dashboard.products.index')
            ->with('success', 'Product deleted successfully');
    }
}
