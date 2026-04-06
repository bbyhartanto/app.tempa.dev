<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\OrderLog;
use App\Services\TemplateEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Dashboard Controller
 *
 * Tenant dashboard for managing store, products, and settings.
 * Requires tenant authentication.
 */
class DashboardController extends Controller
{
    public function __construct(
        private TemplateEngine $templateEngine
    ) {}

    /**
     * Get current tenant from authenticated user
     */
    private function getCurrentTenant(): Tenant
    {
        $user = Auth::user();
        
        // Get tenant by user's email
        $tenant = Tenant::where('email', $user->email)->first();
        
        if (!$tenant) {
            abort(403, 'No store associated with this account');
        }
        
        return $tenant;
    }

    /**
     * Display dashboard home
     */
    public function index(Request $request): Response
    {
        $tenant = $this->getCurrentTenant();
        $subscriptionService = app(\App\Services\SubscriptionService::class);

        // Get stats
        $productCount = $tenant->products()->count();
        $availableProductCount = $tenant->products()->available()->count();
        $orderCount = $tenant->orderLogs()->recent()->count();
        
        // Get subscription info
        $subscriptionSummary = $subscriptionService->getSubscriptionSummary($tenant);
        $remainingSlots = $subscriptionService->getRemainingItemSlots($tenant);

        return Inertia::render('Dashboard/Home', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'store_link' => $tenant->store_link,
                'status' => $tenant->status,
                'subscription_status' => $tenant->subscription_status,
                'item_limit' => $tenant->item_limit,
                'logo_url' => $tenant->logo_url,
            ],
            'stats' => [
                'total_products' => $productCount,
                'available_products' => $availableProductCount,
                'recent_orders' => $orderCount,
            ],
            'subscription' => [
                'summary' => $subscriptionSummary,
                'remaining_slots' => $remainingSlots,
                'status_label' => $subscriptionService->getStatusLabel($tenant),
                'badge_class' => $subscriptionService->getLabelClass($tenant),
            ],
        ]);
    }

    /**
     * Display store settings page
     */
    public function settings(Request $request): Response
    {
        $tenant = $this->getCurrentTenant();

        $availableTemplates = $this->templateEngine->getAvailableTemplates();
        $currentTemplateConfig = $this->templateEngine->getConfig($tenant);

        return Inertia::render('Dashboard/Settings', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'whatsapp_number' => $tenant->whatsapp_number,
                'logo_url' => $tenant->logo_url,
                'background_image' => $tenant->background_image,
                'description' => $tenant->description,
                'address' => $tenant->address,
                'city' => $tenant->city,
                'province' => $tenant->province,
                'streetname' => $tenant->streetname,
                'google_maps_link' => $tenant->google_maps_link,
                'latitude' => $tenant->latitude,
                'longitude' => $tenant->longitude,
                'template_slug' => $tenant->template_slug,
                'settings' => $tenant->settings,
                'store_links' => $tenant->store_links ?? [],
                'status' => $tenant->status,
                'store_link' => $tenant->store_link,
            ],
            'availableTemplates' => $availableTemplates,
            'templateConfig' => $currentTemplateConfig,
        ]);
    }

    /**
     * Update store settings
     */
    public function updateSettings(Request $request)
    {
        $tenant = $this->getCurrentTenant();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:tenants,email,' . $tenant->id,
            'phone' => 'sometimes|nullable|string|max:20',
            'whatsapp_number' => 'sometimes|nullable|string|max:20',
            'logo_url' => 'sometimes|nullable|url|max:500',
            'background_image' => 'sometimes|nullable|url|max:500',
            'description' => 'sometimes|nullable|string|max:1000',
            'address' => 'sometimes|nullable|string|max:255',
            'city' => 'sometimes|nullable|string|max:100',
            'province' => 'sometimes|nullable|string|max:100',
            'streetname' => 'sometimes|nullable|string|max:255',
            'google_maps_link' => 'sometimes|nullable|url|max:500',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
            'opening_schedule' => 'sometimes|nullable|array',
            'template_slug' => 'sometimes|string|exists:templates,slug',
            'settings' => 'sometimes|nullable|array',
            'store_links' => 'sometimes|nullable|array',
        ]);

        $tenant->update($validated);

        // Invalidate template cache
        $this->templateEngine->invalidateCache($tenant);

        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    /**
     * Log order from WhatsApp redirect
     */
    public function logOrder(Request $request)
    {
        $tenant = $this->getCurrentTenant();

        $validated = $request->validate([
            'product_snapshot' => 'required|array',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'shipping_address' => 'nullable|string',
            'message_sent_to_wa' => 'required|string',
            'wa_link' => 'nullable|url|max:500',
        ]);

        $orderLog = $tenant->orderLogs()->create($validated);

        return response()->json([
            'success' => true,
            'order_id' => $orderLog->id,
        ]);
    }

    /**
     * Display links management page
     */
    public function links(Request $request): Response
    {
        $tenant = $this->getCurrentTenant();

        return Inertia::render('Dashboard/Links/Index', [
            'storeLinks' => $tenant->store_links ?? [],
        ]);
    }

    /**
     * Update store links
     */
    public function updateLinks(Request $request)
    {
        $tenant = $this->getCurrentTenant();

        $validated = $request->validate([
            'store_links' => 'nullable|array',
            'store_links.*.label' => 'required|string|max:100',
            'store_links.*.url' => 'required|url|max:500',
            'store_links.*.order' => 'integer|min:0',
        ]);

        $tenant->update($validated);

        return redirect()->back()->with('success', 'Links updated successfully');
    }
}
