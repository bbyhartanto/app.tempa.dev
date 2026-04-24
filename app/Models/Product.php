<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

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
        'dine_in_enabled',
        'sort_order',
    ];

    protected $casts = [
        'images' => 'array',
        'is_available' => 'boolean',
        'dine_in_enabled' => 'boolean',
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
     * Scope for dine-in enabled products only.
     */
    public function scopeDineInEnabled(Builder $query): Builder
    {
        return $query->where('dine_in_enabled', true);
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
        if (empty($this->images) || empty($this->images[0])) {
            return null;
        }

        $path = $this->images[0];

        // If it's an external URL (e.g. S3), return as is
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // For local storage, generate URL dynamically based on current APP_URL
        return asset("storage/{$path}");
    }

    /**
     * Get all image URLs.
     */
    public function getImageUrlsAttribute(): array
    {
        if (empty($this->images)) {
            return [];
        }

        return array_map(function ($path) {
            // If it's an external URL (e.g. S3), return as is
            if (filter_var($path, FILTER_VALIDATE_URL)) {
                return $path;
            }

            // For local storage, generate URL dynamically based on current APP_URL
            return asset("storage/{$path}");
        }, $this->images);
    }
}
