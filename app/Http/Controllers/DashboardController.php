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

        // Get stats
        $productCount = $tenant->products()->count();
        $availableProductCount = $tenant->products()->available()->count();
        $orderCount = $tenant->orderLogs()->recent()->count();

        return Inertia::render('Dashboard/Home', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'store_link' => $tenant->store_link,
                'status' => $tenant->status,
                'logo_url' => $tenant->logo_url,
            ],
            'stats' => [
                'total_products' => $productCount,
                'available_products' => $availableProductCount,
                'recent_orders' => $orderCount,
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
            'tenant' => $tenant,
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
}
