<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class OrderHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'changed_by',
        'action',
        'old_values',
        'new_values',
        'notes',
        'created_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($history) {
            $history->created_at = $history->created_at ?? now();
        });
    }

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the order that owns this history.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who made the change.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope: histories for specific order.
     */
    public function scopeForOrder(Builder $query, int $orderId): Builder
    {
        return $query->where('order_id', $orderId)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope: filter by action type.
     */
    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Get human-readable action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'order_created' => 'Order Created',
            'item_quantity_updated' => 'Item Quantity Updated',
            'item_removed' => 'Item Removed',
            'item_restored' => 'Item Restored',
            'shipping_added' => 'Shipping Cost Added',
            'shipping_updated' => 'Shipping Cost Updated',
            'payment_notes_added' => 'Payment Notes Added',
            'payment_notes_updated' => 'Payment Notes Updated',
            'status_updated' => 'Status Updated',
            'receipt_uploaded' => 'Shipping Receipt Uploaded',
            'adjustment_notes_added' => 'Adjustment Notes Added',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Get formatted change summary.
     */
    public function getChangeSummaryAttribute(): string
    {
        if (!$this->old_values || !$this->new_values) {
            return $this->action_label;
        }

        $changes = [];
        foreach ($this->new_values as $key => $value) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue != $value) {
                $changes[] = "{$key}: {$oldValue} → {$value}";
            }
        }

        return implode(', ', $changes) ?: $this->action_label;
    }
}
