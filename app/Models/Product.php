<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'description',
        'price',
        'currency',
        'images',
        'is_available',
        'sort_order',
    ];

    protected $casts = [
        'images' => 'array',
        'is_available' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            // Auto-generate slug from title if not provided
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->title);
            }
        });
    }

    /**
     * Get the tenant that owns the product.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope for tenant's products only.
     * CRITICAL: This scope enforces tenant isolation at query level.
     * All product queries should use this to prevent data leakage.
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope for available products only.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope for ordered products.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Get formatted price with currency.
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->currency === 'IDR') {
            return 'Rp ' . number_format($this->price, 0, ',', '.');
        }
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    /**
     * Get first image URL.
     */
    public function getFirstImageAttribute(): ?string
    {
        return $this->images[0] ?? null;
    }
}
