<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\OrderLog;
use App\Services\SubscriptionService;
use App\Services\TemplateEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        
        // Get current active subscription plan details
        $currentPlanInfo = null;
        if ($tenant->currentSubscription && $tenant->currentSubscription->plan) {
            $plan = $tenant->currentSubscription->plan;
            $currentPlanInfo = [
                'name' => $plan->tier_label,
                'price' => $plan->formatted_price,
                'billing_cycle' => $plan->billing_cycle_label,
                'item_limit' => $plan->item_limit,
                'end_date' => $tenant->currentSubscription->end_date->format('d M Y'),
                'days_remaining' => $tenant->currentSubscription->daysRemaining(),
            ];
        }
        
        // Get requested plan details if pending
        $requestedPlanInfo = null;
        if ($tenant->subscription_request_status === 'pending' && $tenant->requested_plan_id) {
            $requestedPlan = \App\Models\SubscriptionPlan::find($tenant->requested_plan_id);
            if ($requestedPlan) {
                $requestedPlanInfo = [
                    'name' => $requestedPlan->tier_label,
                    'price' => $requestedPlan->formatted_price,
                    'billing_cycle' => $requestedPlan->billing_cycle_label,
                    'item_limit' => $requestedPlan->item_limit,
                ];
            }
        }

        return Inertia::render('Dashboard/Home', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'store_link' => $tenant->store_link,
                'status' => $tenant->status,
                'subscription_status' => $tenant->subscription_status,
                'subscription_request_status' => $tenant->subscription_request_status ?? 'none',
                'requested_plan' => $requestedPlanInfo,
                'current_plan' => $currentPlanInfo,
                'item_limit' => $tenant->item_limit,
                'logo_url' => $tenant->logo_url,
                'enabled_modules' => $tenant->enabled_modules ?? ['catalog'],
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
            'availablePlans' => $subscriptionService->getAvailablePlans(),
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

        return Inertia::render('Dashboard/Settings/Index', [
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
                'enabled_modules' => $tenant->enabled_modules ?? ['catalog'],
                'status' => $tenant->status,
                'store_link' => $tenant->store_link,
                'subscription_status' => $tenant->subscription_status,
            ],
            'availableTemplates' => $availableTemplates,
            'templateConfig' => $currentTemplateConfig,
        ]);
    }

    /**
     * Display location page
     */
    public function locationPage(Request $request): Response
    {
        $tenant = $this->getCurrentTenant();

        return Inertia::render('Dashboard/Location/Index', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'address' => $tenant->address,
                'city' => $tenant->city,
                'province' => $tenant->province,
                'google_maps_link' => $tenant->google_maps_link,
            ],
        ]);
    }

    /**
     * Display template page
     */
    public function templatePage(Request $request): Response
    {
        $tenant = $this->getCurrentTenant();

        $availableTemplates = $this->templateEngine->getAvailableTemplates();
        $currentTemplateConfig = $this->templateEngine->getConfig($tenant);

        return Inertia::render('Dashboard/Template/Index', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'template_slug' => $tenant->template_slug,
                'settings' => $tenant->settings,
                'background_image' => $tenant->background_image,
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
            'logo_url' => 'sometimes|nullable|string|max:500',
            'logo_file' => 'sometimes|nullable|image|mimes:jpeg,png,webp,svg|max:2048',
            'remove_logo' => 'sometimes|boolean',
            'background_image' => 'sometimes|nullable|string|max:500',
            'background_file' => 'sometimes|nullable|image|mimes:jpeg,png,webp|max:5120',
            'remove_background' => 'sometimes|boolean',
            'description' => 'sometimes|nullable|string|max:1000',
            'address' => 'sometimes|nullable|string|max:255',
            'city' => 'sometimes|nullable|string|max:100',
            'province' => 'sometimes|nullable|string|max:100',
            'streetname' => 'sometimes|nullable|string|max:255',
            'google_maps_link' => 'sometimes|nullable|string|max:500',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
            'opening_schedule' => 'sometimes|nullable|array',
            'template_slug' => 'sometimes|string|exists:templates,slug',
            'settings' => 'sometimes|nullable|array',
            'store_links' => 'sometimes|nullable|array',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo_file')) {
            // Delete old logo if it's a stored file (not a URL)
            if ($tenant->logo_url && !str_starts_with($tenant->logo_url, 'http')) {
                Storage::disk('public')->delete($tenant->logo_url);
            }

            $path = $request->file('logo_file')->store('logos', 'public');
            $validated['logo_url'] = Storage::url($path);
        }

        // Handle logo removal
        if (!empty($validated['remove_logo'])) {
            if ($tenant->logo_url && !str_starts_with($tenant->logo_url, 'http')) {
                Storage::disk('public')->delete($tenant->logo_url);
            }
            $validated['logo_url'] = null;
        }

        // Handle background upload
        if ($request->hasFile('background_file')) {
            if ($tenant->background_image && !str_starts_with($tenant->background_image, 'http')) {
                Storage::disk('public')->delete($tenant->background_image);
            }
            $path = $request->file('background_file')->store('backgrounds', 'public');
            $validated['background_image'] = Storage::url($path);
        }

        // Handle background removal
        if (!empty($validated['remove_background'])) {
            if ($tenant->background_image && !str_starts_with($tenant->background_image, 'http')) {
                Storage::disk('public')->delete($tenant->background_image);
            }
            $validated['background_image'] = null;
        }

        unset($validated['logo_file']);
        unset($validated['remove_logo']);
        unset($validated['background_file']);
        unset($validated['remove_background']);

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

    /**
     * Tenant requests subscription activation
     */
    public function requestSubscription(Request $request, SubscriptionService $subscriptionService)
    {
        $tenant = $this->getCurrentTenant();

        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:3_months,1_year',
        ]);

        $subscriptionService->requestSubscription($tenant, $validated['plan_id'], $validated['billing_cycle']);

        return redirect()->back()->with('success', 'Subscription request submitted! Admin will review your request.');
    }

    /**
     * Update enabled modules for tenant.
     */
    public function updateModules(Request $request)
    {
        $tenant = $this->getCurrentTenant();

        $validated = $request->validate([
            'modules' => 'required|array|min:0',
            'modules.*' => 'required|string|in:catalog,booking,dine_in',
        ]);

        $modules = array_values(array_unique($validated['modules']));

        // Enforce mutually exclusive main modules
        $mainModules = array_filter($modules, fn($m) => $m !== 'dine_in');
        if (count($mainModules) > 1) {
            return redirect()->back()
                ->with('error', 'You can only have one primary module active at a time (Catalog OR Booking).');
        }

        $tenant->update([
            'enabled_modules' => $modules,
            'onboarding_completed' => true, // Mark as completed when they manually set modules
        ]);

        return redirect()->back()->with('success', 'Modules updated successfully.');
    }
}
