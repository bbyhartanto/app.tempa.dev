<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'tenant_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_address',
        'status',
        'payment_status',
        'original_subtotal',
        'adjusted_subtotal',
        'shipping_cost',
        'total',
        'payment_notes',
        'shipping_receipt',
        'adjustment_notes',
    ];

    protected $casts = [
        'original_subtotal' => 'decimal:2',
        'adjusted_subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        // Generate order number on creation
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
            if (empty($order->adjusted_subtotal)) {
                $order->adjusted_subtotal = $order->original_subtotal;
            }
            if (empty($order->total)) {
                $order->total = $order->adjusted_subtotal + ($order->shipping_cost ?? 0);
            }
        });

        // Log order creation to history
        static::created(function ($order) {
            OrderHistory::create([
                'order_id' => $order->id,
                'action' => 'order_created',
                'new_values' => [
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                    'original_subtotal' => $order->original_subtotal,
                    'items_count' => $order->items()->count(),
                ],
            ]);
        });

        // Recalculate total when shipping cost changes
        static::saving(function ($order) {
            if ($order->isDirty('shipping_cost') || $order->isDirty('adjusted_subtotal')) {
                $order->total = $order->adjusted_subtotal + $order->shipping_cost;
            }
        });
    }

    /**
     * Generate unique order number.
     * Format: ORD-YYYYMMDD-XXXX
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = static::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastOrder ? (intval(substr($lastOrder->order_number, -4)) + 1) : 1;
        
        return 'ORD-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the tenant that owns the order.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get all order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get only active (non-removed) items.
     */
    public function activeItems(): HasMany
    {
        return $this->hasMany(OrderItem::class)->where('is_removed', false);
    }

    /**
     * Get order history.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Scope: orders for specific tenant.
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: orders by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: recent orders (last 30 days).
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope: search by order number or customer name.
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('order_number', 'like', "%{$keyword}%")
              ->orWhere('customer_name', 'like', "%{$keyword}%")
              ->orWhere('customer_phone', 'like', "%{$keyword}%");
        });
    }

    /**
     * Get formatted total with currency.
     */
    public function getFormattedTotalAttribute(): string
    {
        $currency = $this->items->first()?->currency ?? 'IDR';
        return $this->formatCurrency($this->total, $currency);
    }

    /**
     * Format currency helper.
     */
    protected function formatCurrency(float $amount, string $currency): string
    {
        if (strtoupper($currency) === 'IDR') {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }
        return $currency . ' ' . number_format($amount, 2);
    }

    /**
     * Check if order has been modified by tenant.
     */
    public function getIsModifiedAttribute(): bool
    {
        return $this->original_subtotal != $this->adjusted_subtotal ||
               $this->items()->where('is_removed', true)->exists() ||
               $this->items()->whereColumn('original_quantity', '!=', 'current_quantity')->exists();
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'confirmed' => 'blue',
            'paid' => 'green',
            default => 'gray',
        };
    }

    /**
     * Recalculate adjusted_subtotal based on current items.
     */
    public function recalculateAdjustedSubtotal(): void
    {
        $newSubtotal = $this->items()
            ->where('is_removed', false)
            ->get()
            ->sum(function ($item) {
                return $item->current_quantity * $item->unit_price;
            });

        $oldValues = [
            'adjusted_subtotal' => $this->adjusted_subtotal,
        ];

        $this->update(['adjusted_subtotal' => $newSubtotal]);

        // Log the recalculation
        OrderHistory::create([
            'order_id' => $this->id,
            'action' => 'subtotal_recalculated',
            'old_values' => $oldValues,
            'new_values' => [
                'adjusted_subtotal' => $newSubtotal,
            ],
        ]);
    }
}
