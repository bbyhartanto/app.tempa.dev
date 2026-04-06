<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PricingController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display pricing configuration page.
     */
    public function index()
    {
        $plans = SubscriptionPlan::all()->map(fn($plan) => [
            'id' => $plan->id,
            'tier' => $plan->tier,
            'tier_label' => $plan->tier_label,
            'billing_cycle' => $plan->billing_cycle,
            'billing_cycle_label' => $plan->billing_cycle_label,
            'price' => (float) $plan->price,
            'formatted_price' => $plan->formatted_price,
            'item_limit' => $plan->item_limit,
            'is_active' => $plan->is_active,
        ]);

        return Inertia::render('Admin/Pricing/Index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Update pricing for a plan.
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'item_limit' => 'sometimes|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        $plan->update($validated);

        return redirect()->back()->with('success', 'Pricing updated successfully!');
    }

    /**
     * Bulk update pricing.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'plans' => 'required|array',
            'plans.*.id' => 'required|exists:subscription_plans,id',
            'plans.*.price' => 'required|numeric|min:0',
            'plans.*.item_limit' => 'sometimes|integer|min:1',
            'plans.*.is_active' => 'sometimes|boolean',
        ]);

        foreach ($validated['plans'] as $planData) {
            $plan = SubscriptionPlan::findOrFail($planData['id']);
            $updateData = ['price' => $planData['price']];
            
            if (isset($planData['item_limit'])) {
                $updateData['item_limit'] = $planData['item_limit'];
            }
            
            if (isset($planData['is_active'])) {
                $updateData['is_active'] = $planData['is_active'];
            }
            
            $plan->update($updateData);
        }

        return redirect()->back()->with('success', 'All pricing updated successfully!');
    }
}
