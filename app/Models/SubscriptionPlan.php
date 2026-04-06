<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'tier',
        'billing_cycle',
        'price',
        'item_limit',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'item_limit' => 'integer',
    ];

    /**
     * Get all tenant subscriptions using this plan.
     */
    public function tenantSubscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'plan_id');
    }

    /**
     * Get formatted price for display.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->price, 0, ',', '.');
    }

    /**
     * Get billing cycle display text.
     */
    public function getBillingCycleLabelAttribute(): string
    {
        return match ($this->billing_cycle) {
            '3_months' => '3 Bulan',
            '1_year' => '1 Tahun',
            default => $this->billing_cycle,
        };
    }

    /**
     * Get tier display text.
     */
    public function getTierLabelAttribute(): string
    {
        return match ($this->tier) {
            'basic' => 'Basic (0-25 items)',
            'standard' => 'Standard (0-60 items)',
            default => $this->tier,
        };
    }

    /**
     * Scope: Only active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by tier.
     */
    public function scopeByTier($query, string $tier)
    {
        return $query->where('tier', $tier);
    }

    /**
     * Scope: Filter by billing cycle.
     */
    public function scopeByBillingCycle($query, string $cycle)
    {
        return $query->where('billing_cycle', $cycle);
    }

    /**
     * Get duration in months for this billing cycle.
     */
    public function getDurationMonths(): int
    {
        return match ($this->billing_cycle) {
            '3_months' => 3,
            '1_year' => 12,
            default => 0,
        };
    }
}
