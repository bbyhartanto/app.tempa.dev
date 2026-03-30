<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Template extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'is_active',
        'is_default',
        'config_schema',
        'preview_image',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'config_schema' => 'array',
    ];

    /**
     * Get tenants using this template.
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'template_slug', 'slug');
    }

    /**
     * Scope for active templates only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the default template.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Get available templates for selection.
     */
    public static function getAvailableTemplates(): array
    {
        return static::active()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn($t) => [$t->slug => $t->name])
            ->toArray();
    }
}
