<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Template;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Tenant Management Controller
 *
 * Super admin functions for managing tenants.
 * Requires super_admin role.
 */
class TenantManagementController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display listing of all tenants
     */
    public function index(Request $request): Response
    {
        $status = $request->get('status', 'all');

        $query = Tenant::query();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $tenants = $query->with('currentSubscription.plan')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'store_link' => $t->store_link,
                'email' => $t->email,
                'status' => $t->status,
                'subscription_status' => $t->subscription_status,
                'trial_ends_at' => $t->trial_ends_at?->toIso8601String(),
                'item_limit' => $t->item_limit,
                'current_subscription' => $t->currentSubscription ? [
                    'id' => $t->currentSubscription->id,
                    'end_date' => $t->currentSubscription->end_date->format('Y-m-d'),
                    'days_remaining' => $t->currentSubscription->daysRemaining(),
                    'plan' => [
                        'tier' => $t->currentSubscription->plan->tier,
                        'tier_label' => $t->currentSubscription->plan->tier_label,
                        'billing_cycle_label' => $t->currentSubscription->plan->billing_cycle_label,
                    ]
                ] : null,
                'template_slug' => $t->template_slug,
                'created_at' => $t->created_at->toIso8601String(),
                'approved_at' => $t->approved_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants,
            'filters' => [
                'status' => $status,
            ],
            'availablePlans' => $this->subscriptionService->getAvailablePlans(),
        ]);
    }

    /**
     * Display tenant details
     */
    public function show(int $id): Response
    {
        $tenant = Tenant::with(['products', 'orderLogs'])->findOrFail($id);

        return Inertia::render('Admin/Tenants/Show', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'store_link' => $tenant->store_link,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'whatsapp_number' => $tenant->whatsapp_number,
                'logo_url' => $tenant->logo_url,
                'description' => $tenant->description,
                'status' => $tenant->status,
                'template_slug' => $tenant->template_slug,
                'settings' => $tenant->settings,
                'created_at' => $tenant->created_at->toIso8601String(),
                'approved_at' => $tenant->approved_at?->toIso8601String(),
                'products_count' => $tenant->products->count(),
                'orders_count' => $tenant->orderLogs->count(),
            ],
        ]);
    }

    /**
     * Approve a pending tenant
     */
    public function approve(Request $request, int $id)
    {
        $tenant = Tenant::findOrFail($id);

        if ($tenant->status === 'active') {
            return redirect()->back()->with('error', 'Tenant is already active');
        }

        $tenant->update([
            'status' => 'active',
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Tenant approved successfully');
    }

    /**
     * Suspend a tenant
     */
    public function suspend(Request $request, int $id)
    {
        $tenant = Tenant::findOrFail($id);

        $tenant->update([
            'status' => 'suspended',
        ]);

        return redirect()->back()->with('success', 'Tenant suspended');
    }

    /**
     * Reactivate a suspended tenant
     */
    public function reactivate(Request $request, int $id)
    {
        $tenant = Tenant::findOrFail($id);

        $tenant->update([
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Tenant reactivated');
    }

    /**
     * Delete a tenant (and all associated data)
     */
    public function destroy(int $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant deleted successfully');
    }

    /**
     * Show form for creating default templates
     */
    public function templates(): Response
    {
        $templates = Template::orderBy('name')->get()->map(fn($t) => [
            'id' => $t->id,
            'slug' => $t->slug,
            'name' => $t->name,
            'is_active' => $t->is_active,
            'is_default' => $t->is_default,
            'config_schema' => $t->config_schema,
            'preview_image' => $t->preview_image,
        ]);

        return Inertia::render('Admin/Templates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Store a new template
     */
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|unique:templates,slug',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'config_schema' => 'nullable|array',
            'preview_image' => 'nullable|url|max:500',
        ]);

        // If default, unset other defaults
        if ($validated['is_default'] ?? false) {
            Template::where('is_default', true)->update(['is_default' => false]);
        }

        Template::create($validated);

        return redirect()->back()->with('success', 'Template created successfully');
    }

    /**
     * Update a template
     */
    public function updateTemplate(Request $request, int $id)
    {
        $template = Template::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'config_schema' => 'nullable|array',
            'preview_image' => 'nullable|url|max:500',
        ]);

        // If default, unset other defaults
        if (($validated['is_default'] ?? false) && !$template->is_default) {
            Template::where('is_default', true)->update(['is_default' => false]);
        }

        $template->update($validated);

        return redirect()->back()->with('success', 'Template updated successfully');
    }
}
