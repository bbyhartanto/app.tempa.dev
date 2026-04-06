<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'original_quantity',
        'current_quantity',
        'unit_price',
        'currency',
        'is_removed',
        'removed_at',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'original_quantity' => 'integer',
        'current_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'is_removed' => 'boolean',
        'removed_at' => 'datetime',
    ];

    /**
     * Get the order that owns this item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product (may be null if deleted).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get subtotal for this item (current_quantity * unit_price).
     */
    public function getSubtotalAttribute(): float
    {
        return $this->current_quantity * $this->unit_price;
    }

    /**
     * Get original subtotal (what customer ordered).
     */
    public function getOriginalSubtotalAttribute(): float
    {
        return $this->original_quantity * $this->unit_price;
    }

    /**
     * Get formatted subtotal.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        $currency = $this->currency ?? 'IDR';
        if (strtoupper($currency) === 'IDR') {
            return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
        }
        return $currency . ' ' . number_format($this->subtotal, 2);
    }

    /**
     * Mark item as removed (soft delete).
     */
    public function markAsRemoved(?string $notes = null): void
    {
        $oldValues = [
            'is_removed' => $this->is_removed,
            'current_quantity' => $this->current_quantity,
        ];

        $this->update([
            'is_removed' => true,
            'current_quantity' => 0,
            'removed_at' => now(),
        ]);

        // Log to history
        OrderHistory::create([
            'order_id' => $this->order_id,
            'action' => 'item_removed',
            'old_values' => $oldValues,
            'new_values' => [
                'is_removed' => true,
                'current_quantity' => 0,
            ],
            'notes' => $notes,
        ]);

        // Recalculate order adjusted_subtotal
        $this->order->recalculateAdjustedSubtotal();
    }

    /**
     * Restore a removed item.
     */
    public function restore(?string $notes = null): void
    {
        $oldValues = [
            'is_removed' => $this->is_removed,
            'current_quantity' => $this->current_quantity,
        ];

        $this->update([
            'is_removed' => false,
            'current_quantity' => $this->original_quantity,
            'removed_at' => null,
        ]);

        // Log to history
        OrderHistory::create([
            'order_id' => $this->order_id,
            'action' => 'item_restored',
            'old_values' => $oldValues,
            'new_values' => [
                'is_removed' => false,
                'current_quantity' => $this->original_quantity,
            ],
            'notes' => $notes,
        ]);

        // Recalculate order adjusted_subtotal
        $this->order->recalculateAdjustedSubtotal();
    }

    /**
     * Update quantity and log the change.
     */
    public function updateQuantity(int $newQuantity, ?string $notes = null): void
    {
        $oldValues = [
            'current_quantity' => $this->current_quantity,
        ];

        $this->update(['current_quantity' => $newQuantity]);

        // Log to history
        OrderHistory::create([
            'order_id' => $this->order_id,
            'action' => 'item_quantity_updated',
            'old_values' => $oldValues,
            'new_values' => [
                'current_quantity' => $newQuantity,
            ],
            'notes' => $notes,
        ]);

        // Recalculate order adjusted_subtotal
        $this->order->recalculateAdjustedSubtotal();
    }

    /**
     * Scope: only active (non-removed) items.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_removed', false);
    }

    /**
     * Scope: only removed items.
     */
    public function scopeRemoved(Builder $query): Builder
    {
        return $query->where('is_removed', true);
    }
}
