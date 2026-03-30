<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class OrderLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'product_snapshot',
        'customer_name',
        'customer_phone',
        'shipping_address',
        'message_sent_to_wa',
        'wa_link',
    ];

    protected $casts = [
        'product_snapshot' => 'array',
    ];

    /**
     * Get the tenant that owns the order log.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope for tenant's order logs only.
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope for recent orders (last 30 days).
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }
}
