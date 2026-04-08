<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    const TRIAL_DURATION_DAYS = 7;
    const GRACE_PERIOD_DAYS = 7;

    /**
     * Start trial period for a tenant.
     */
    public function startTrial(Tenant $tenant): void
    {
        $tenant->update([
            'subscription_status' => 'trial',
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays(self::TRIAL_DURATION_DAYS),
            'item_limit' => 25, // Default basic tier during trial
        ]);
    }

    /**
     * Activate subscription for a tenant.
     * 
     * @param Tenant $tenant
     * @param int $planId Subscription plan ID
     * @param Carbon|null $startDate Custom start date (defaults to now)
     * @return TenantSubscription
     */
    public function activateSubscription(Tenant $tenant, int $planId, ?Carbon $startDate = null): TenantSubscription
    {
        $plan = SubscriptionPlan::findOrFail($planId);
        $start = $startDate ?? now();
        $end = $start->copy()->addMonths($plan->getDurationMonths());

        return DB::transaction(function () use ($tenant, $plan, $start, $end) {
            // Expire any existing active subscriptions
            $tenant->subscriptions()
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            // Create new subscription
            $subscription = TenantSubscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'start_date' => $start,
                'end_date' => $end,
                'status' => 'active',
            ]);

            // Update tenant subscription status
            $tenant->update([
                'subscription_status' => 'subscribed',
                'current_subscription_id' => $subscription->id,
                'item_limit' => $plan->item_limit,
            ]);

            return $subscription;
        });
    }

    /**
     * Renew an expired/expiring subscription.
     */
    public function renewSubscription(Tenant $tenant, int $planId): TenantSubscription
    {
        return $this->activateSubscription($tenant, $planId);
    }

    /**
     * Cancel subscription (immediately expire).
     */
    public function cancelSubscription(Tenant $tenant): void
    {
        if (!$tenant->current_subscription_id) {
            return;
        }

        DB::transaction(function () use ($tenant) {
            $subscription = $tenant->currentSubscription;
            if ($subscription) {
                $subscription->update(['status' => 'expired']);
            }

            $tenant->update([
                'subscription_status' => 'expired',
                'current_subscription_id' => null,
            ]);
        });
    }

    /**
     * Check and update all expired subscriptions.
     * Moves subscribed → grace_period → expired.
     */
    public function processExpiredSubscriptions(): int
    {
        $processed = 0;
        $now = Carbon::now();

        // Find subscriptions that ended today or before
        $expiredSubscriptions = TenantSubscription::where('status', 'active')
            ->where('end_date', '<', $now)
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $tenant = $subscription->tenant;

            // Skip if already processed
            if ($tenant->subscription_status === 'expired') {
                continue;
            }

            // If in grace period and grace period expired
            if ($tenant->subscription_status === 'grace_period') {
                $gracePeriodEnd = $subscription->end_date->copy()->addDays(self::GRACE_PERIOD_DAYS);
                
                if ($now->greaterThan($gracePeriodEnd)) {
                    $tenant->update([
                        'subscription_status' => 'expired',
                        'current_subscription_id' => null,
                        'item_limit' => 0, // No items allowed when expired
                    ]);
                    $subscription->update(['status' => 'expired']);
                    $processed++;
                }
            }
            // If still subscribed, move to grace period
            elseif ($tenant->subscription_status === 'subscribed') {
                $tenant->update([
                    'subscription_status' => 'grace_period',
                ]);
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Check and expire trials that have ended without subscription.
     */
    public function processExpiredTrials(): int
    {
        $now = Carbon::now();
        
        $expiredTrials = Tenant::where('subscription_status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', $now)
            ->get();

        $processed = 0;
        foreach ($expiredTrials as $tenant) {
            $tenant->update([
                'subscription_status' => 'expired',
                'item_limit' => 0,
            ]);
            $processed++;
        }

        return $processed;
    }

    /**
     * Get subscription summary for a tenant.
     */
    public function getSubscriptionSummary(Tenant $tenant): array
    {
        $subscription = $tenant->currentSubscription;

        return [
            'status' => $tenant->subscription_status,
            'status_label' => $this->getStatusLabel($tenant),
            'current_subscription' => $subscription ? [
                'id' => $subscription->id,
                'plan' => [
                    'tier' => $subscription->plan->tier,
                    'tier_label' => $subscription->plan->tier_label,
                    'billing_cycle' => $subscription->plan->billing_cycle,
                    'billing_cycle_label' => $subscription->plan->billing_cycle_label,
                ],
                'start_date' => $subscription->start_date->format('Y-m-d'),
                'end_date' => $subscription->end_date->format('Y-m-d'),
                'days_remaining' => $subscription->daysRemaining(),
                'is_expired' => $subscription->isExpired(),
            ] : null,
            'trial_ends_at' => $tenant->trial_ends_at?->format('Y-m-d'),
            'trial_days_remaining' => $tenant->trial_ends_at 
                ? max(0, now()->diffInDays($tenant->trial_ends_at, false))
                : null,
            'item_limit' => $tenant->item_limit,
        ];
    }

    /**
     * Get display label for subscription status.
     */
    public function getLabelClass(Tenant $tenant): string
    {
        return match ($tenant->subscription_status) {
            'trial' => 'bg-blue-100 text-blue-800',
            'subscribed' => 'bg-green-100 text-green-800',
            'grace_period' => 'bg-orange-100 text-orange-800',
            'expired' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabel(Tenant $tenant): string
    {
        return match ($tenant->subscription_status) {
            'trial' => 'Trial',
            'subscribed' => 'Subscribed',
            'grace_period' => 'Grace Period',
            'expired' => 'Expired',
            default => $tenant->subscription_status,
        };
    }

    /**
     * Check if tenant can add more products based on item limit.
     */
    public function canAddProduct(Tenant $tenant): bool
    {
        $currentItemCount = $tenant->products()->count();
        return $currentItemCount < $tenant->item_limit;
    }

    /**
     * Get remaining item slots.
     */
    public function getRemainingItemSlots(Tenant $tenant): int
    {
        $currentItemCount = $tenant->products()->count();
        return max(0, $tenant->item_limit - $currentItemCount);
    }

    /**
     * Get all available plans for selection.
     */
    public function getAvailablePlans(): array
    {
        return SubscriptionPlan::active()
            ->get()
            ->map(fn($plan) => [
                'id' => $plan->id,
                'tier' => $plan->tier,
                'tier_label' => $plan->tier_label,
                'billing_cycle' => $plan->billing_cycle,
                'billing_cycle_label' => $plan->billing_cycle_label,
                'price' => (float) $plan->price,
                'formatted_price' => $plan->formatted_price,
                'item_limit' => $plan->item_limit,
                'duration_months' => $plan->getDurationMonths(),
            ])
            ->toArray();
    }

    /**
     * Update pricing for a plan.
     */
    public function updatePlanPrice(int $planId, float $price): SubscriptionPlan
    {
        $plan = SubscriptionPlan::findOrFail($planId);
        $plan->update(['price' => $price]);
        return $plan;
    }

    /**
     * Tenant requests a subscription activation.
     */
    public function requestSubscription(Tenant $tenant, int $planId, string $billingCycle): void
    {
        $tenant->update([
            'subscription_request_status' => 'pending',
            'requested_plan_id' => $planId,
            'requested_billing_cycle' => $billingCycle,
            'subscription_requested_at' => now(),
        ]);
    }
}
