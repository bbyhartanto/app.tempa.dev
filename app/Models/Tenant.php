<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'enabled_modules',
        'onboarding_completed',
        'store_links',
        'status',
        'subscription_status',
        'subscription_request_status',
        'trial_started_at',
        'trial_ends_at',
        'current_subscription_id',
        'requested_plan_id',
        'requested_billing_cycle',
        'subscription_requested_at',
        'item_limit',
        'approved_at',
    ];

    protected $casts = [
        'opening_schedule' => 'array',
        'settings' => 'array',
        'enabled_modules' => 'array',
        'store_links' => 'array',
        'approved_at' => 'datetime',
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'subscription_requested_at' => 'datetime',
        'item_limit' => 'integer',
        'onboarding_completed' => 'boolean',
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
     * Get services for this tenant.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get available services only (for storefront).
     */
    public function availableServices(): HasMany
    {
        return $this->hasMany(Service::class)->where('is_available', true)->orderBy('sort_order');
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
     * Get all subscriptions for this tenant.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    /**
     * Get the current active subscription.
     */
    public function currentSubscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'current_subscription_id');
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

    // ============================================================
    // Module Management Helpers
    // ============================================================

    /**
     * Get enabled modules as array.
     */
    public function getModulesAttribute(): array
    {
        return $this->enabled_modules ?? ['catalog'];
    }

    /**
     * Check if a specific module is enabled.
     */
    public function hasModule(string $module): bool
    {
        return in_array($module, $this->modules, true);
    }

    /**
     * Check if catalog module is enabled.
     */
    public function hasCatalog(): bool
    {
        return $this->hasModule('catalog');
    }

    /**
     * Check if dine-in menu module is enabled.
     * Requires catalog to be enabled first.
     */
    public function hasDineIn(): bool
    {
        return $this->hasModule('catalog') && $this->hasModule('dine_in');
    }

    /**
     * Check if booking module is enabled.
     */
    public function hasBooking(): bool
    {
        return $this->hasModule('booking');
    }

    public function canEnableModule(string $module): bool
    {
        if ($this->hasModule($module)) {
            return true; // Already enabled
        }

        // Module exclusivity is enforced at the controller validation level
        return true;
    }

    /**
     * Enable a module for this tenant.
     */
    public function enableModule(string $module): bool
    {
        if (!$this->canEnableModule($module)) {
            return false;
        }

        $modules = $this->modules;
        if (!in_array($module, $modules, true)) {
            $modules[] = $module;
            $this->update(['enabled_modules' => $modules]);
        }

        return true;
    }

    /**
     * Disable a module for this tenant (cannot disable last module).
     */
    public function disableModule(string $module): bool
    {
        $modules = $this->modules;

        // Cannot disable the last module
        if (count($modules) <= 1 && in_array($module, $modules, true)) {
            return false;
        }

        $modules = array_values(array_diff($modules, [$module]));
        $this->update(['enabled_modules' => $modules]);

        return true;
    }



    /**
     * Get the primary (first enabled) module type.
     */
    public function getPrimaryModule(): ?string
    {
        return $this->modules[0] ?? 'catalog';
    }
}
