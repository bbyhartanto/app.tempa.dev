<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionManagementController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Get tenant subscription details.
     */
    public function show(Tenant $tenant)
    {
        $summary = $this->subscriptionService->getSubscriptionSummary($tenant);
        $availablePlans = $this->subscriptionService->getAvailablePlans();

        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'subscription_status' => $tenant->subscription_status,
            ],
            'summary' => $summary,
            'available_plans' => $availablePlans,
        ]);
    }

    /**
     * Activate or update subscription for a tenant.
     */
    public function activate(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'start_date' => 'nullable|date',
        ]);

        $startDate = isset($validated['start_date']) 
            ? Carbon::parse($validated['start_date']) 
            : null;

        $subscription = $this->subscriptionService->activateSubscription(
            $tenant,
            $validated['plan_id'],
            $startDate
        );

        return redirect()->back()->with('success', 'Subscription activated successfully!');
    }

    /**
     * Cancel subscription for a tenant.
     */
    public function cancel(Tenant $tenant)
    {
        $this->subscriptionService->cancelSubscription($tenant);

        return redirect()->back()->with('success', 'Subscription cancelled successfully!');
    }

    /**
     * Extend trial period for a tenant.
     */
    public function extendTrial(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:30',
        ]);

        $tenant->update([
            'trial_ends_at' => $tenant->trial_ends_at 
                ? $tenant->trial_ends_at->addDays($validated['days'])
                : now()->addDays($validated['days']),
        ]);

        return redirect()->back()->with('success', "Trial extended by {$validated['days']} days!");
    }

    /**
     * Renew subscription for a tenant.
     */
    public function renew(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $subscription = $this->subscriptionService->renewSubscription(
            $tenant,
            $validated['plan_id']
        );

        return redirect()->back()->with('success', 'Subscription renewed successfully!');
    }
}
