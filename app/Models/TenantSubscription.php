<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSubscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the tenant that owns this subscription.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the plan for this subscription.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * Check if subscription is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date->isFuture();
    }

    /**
     * Check if subscription has expired.
     */
    public function isExpired(): bool
    {
        return $this->end_date->isPast();
    }

    /**
     * Get days remaining until expiration.
     */
    public function daysRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return now()->diffInDays($this->end_date, false);
    }

    /**
     * Get formatted date range for display.
     */
    public function getDateRangeLabel(): string
    {
        $start = $this->start_date->format('d M Y');
        $end = $this->end_date->format('d M Y');
        return "{$start} - {$end}";
    }

    /**
     * Scope: Only active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Subscriptions expiring within given days.
     */
    public function scopeExpiringWithin($query, int $days)
    {
        $endDate = Carbon::now()->addDays($days)->endOfDay();
        return $query->where('status', 'active')
            ->where('end_date', '<=', $endDate);
    }

    /**
     * Scope: Subscriptions that have expired.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
            ->where('end_date', '<', Carbon::now());
    }

    /**
     * Scope: For a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
