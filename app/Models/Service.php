<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Service extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'duration_min',
        'buffer_min',
        'time_slots',
        'available_days',
        'default_start',
        'default_end',
        'is_available',
        'sort_order',
        'image_urls',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_min' => 'integer',
        'buffer_min' => 'integer',
        'time_slots' => 'array',
        'available_days' => 'array',
        'image_urls' => 'array',
        'is_available' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
            if (empty($service->available_days)) {
                // Default: Monday-Friday (1-5)
                $service->available_days = [1, 2, 3, 4, 5];
            }
        });

        static::updating(function ($service) {
            if ($service->isDirty('name') && !$service->isDirty('slug')) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    /**
     * Get the tenant that owns the service.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get bookings for this service.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Order::class, 'orderable_id')->where('orderable_type', self::class);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        if (strtoupper($this->currency) === 'IDR') {
            return 'Rp ' . number_format((float) $this->price, 0, ',', '.');
        }
        return $this->currency . ' ' . number_format((float) $this->price, 2);
    }

    /**
     * Get first image URL.
     */
    public function getFirstImageAttribute(): ?string
    {
        return $this->image_urls[0] ?? null;
    }

    /**
     * Get available days as day names.
     */
    public function getAvailableDayNamesAttribute(): array
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return array_map(fn($d) => $days[$d], $this->available_days ?? []);
    }

    /**
     * Generate time slots for a given date.
     * Returns array of "HH:MM-HH:MM" strings.
     */
    public function generateTimeSlotsForDate(\Carbon\Carbon $date): array
    {
        // Check if this day is available
        $dayOfWeek = (int) $date->format('w'); // 0=Sunday, 1=Monday, etc.
        if (!in_array($dayOfWeek, $this->available_days ?? [])) {
            return [];
        }

        // Use custom slots or default range
        if ($this->time_slots) {
            return $this->time_slots;
        }

        $slots = [];
        $interval = $this->duration_min + $this->buffer_min;
        $start = \Carbon\Carbon::parse($this->default_start);
        $end = \Carbon\Carbon::parse($this->default_end);

        while ($start->copy()->addMinutes($this->duration_min)->lte($end)) {
            $slotEnd = $start->copy()->addMinutes($this->duration_min);
            $slots[] = $start->format('H:i') . '-' . $slotEnd->format('H:i');
            $start->addMinutes($interval);
        }

        return $slots;
    }

    /**
     * Scope: for specific tenant.
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: available services only.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }
}
