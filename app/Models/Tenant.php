<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'store_link',
        'email',
        'phone',
        'whatsapp_number',
        'logo_url',
        'background_image',
        'description',
        'google_maps_link',
        'address',
        'city',
        'province',
        'streetname',
        'latitude',
        'longitude',
        'opening_schedule',
        'template_slug',
        'settings',
        'store_links',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'opening_schedule' => 'array',
        'settings' => 'array',
        'store_links' => 'array',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenant) {
            // Auto-generate slug from name if not provided
            if (empty($tenant->slug)) {
                $tenant->slug = Str::slug($tenant->name);
            }
            // Auto-generate store_link from slug if not provided
            if (empty($tenant->store_link)) {
                $tenant->store_link = $tenant->slug;
            }
        });
    }

    /**
     * Get the products for this tenant.
     * Scope to only available products by default for storefront use.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get available products only (for storefront).
     */
    public function availableProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('is_available', true)->orderBy('sort_order');
    }

    /**
     * Get order logs for this tenant.
     */
    public function orderLogs(): HasMany
    {
        return $this->hasMany(OrderLog::class);
    }

    /**
     * Get orders for this tenant.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the template for this tenant.
     */
    public function template(): HasMany
    {
        return $this->hasMany(Template::class, 'slug', 'template_slug');
    }

    /**
     * Scope for active tenants only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for pending tenants (awaiting approval).
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if tenant is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get formatted WhatsApp number (remove + and spaces).
     */
    public function getFormattedWhatsAppNumberAttribute(): ?string
    {
        if (!$this->whatsapp_number) {
            return null;
        }
        // Remove +, spaces, dashes for wa.me link
        return preg_replace('/[^0-9]/', '', $this->whatsapp_number);
    }

    /**
     * Check if store is currently open based on opening schedule.
     * Tradeoff: Simple implementation, doesn't handle timezone edge cases.
     * For production, consider using spatie/opening-hours package.
     */
    public function isOpenNow(): bool
    {
        if (!$this->opening_schedule) {
            return true; // Assume always open if no schedule set
        }

        $now = now();
        $day = strtolower($now->format('l')); // monday, tuesday, etc.
        
        if (!isset($this->opening_schedule[$day])) {
            return true;
        }

        $schedule = $this->opening_schedule[$day];
        
        if (isset($schedule['closed']) && $schedule['closed']) {
            return false;
        }

        $currentTime = $now->format('H:i');
        $openTime = $schedule['open'] ?? '00:00';
        $closeTime = $schedule['close'] ?? '23:59';

        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }
}
