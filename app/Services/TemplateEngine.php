<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Template;
use Illuminate\Support\Facades\Cache;

/**
 * Template Engine Loader
 * 
 * Loads and merges template configurations with tenant settings.
 * Provides Vue-ready props for frontend rendering.
 * 
 * Architecture:
 * - Template defines available customization fields (config_schema)
 * - Tenant stores their chosen values (settings JSON)
 * - This service merges them for consumption
 * 
 * Tradeoffs:
 * - JSON-based config (simple, no extra tables)
 * - Cached for performance (invalidate on tenant update)
 * - MVP: No template inheritance, no partial overrides
 */
class TemplateEngine
{
    /**
     * Cache key prefix
     */
    private const CACHE_PREFIX = 'template_config:';

    /**
     * Default template configuration
     */
    private const DEFAULT_CONFIG = [
        'colors' => [
            'primary' => '#3B82F6',
            'secondary' => '#6B7280',
            'accent' => '#10B981',
        ],
        'layout' => [
            'show_prices' => true,
            'show_description' => true,
            'products_per_row' => 2,
        ],
        'typography' => [
            'heading_font' => 'sans',
            'body_font' => 'sans',
        ],
    ];

    /**
     * Get merged template configuration for a tenant
     * 
     * @return array Template props ready for Vue
     */
    public function getConfig(Tenant $tenant): array
    {
        // Try cache first
        $cacheKey = self::CACHE_PREFIX . $tenant->id;
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        // Load template schema
        $template = Template::where('slug', $tenant->template_slug)
            ->active()
            ->first();

        // Start with default config
        $config = $this->getDefaultConfig();

        // Merge template-specific schema if exists
        if ($template && $template->config_schema) {
            $config = array_merge_recursive($config, $template->config_schema);
        }

        // Merge tenant's custom settings
        if ($tenant->settings) {
            $config = array_merge_recursive($config, $tenant->settings);
        }

        // Cache for 1 hour (or until tenant update)
        Cache::put($cacheKey, $config, 3600);

        return $config;
    }

    /**
     * Get default template configuration
     */
    public function getDefaultConfig(): array
    {
        return self::DEFAULT_CONFIG;
    }

    /**
     * Get available templates for selection
     */
    public function getAvailableTemplates(): array
    {
        return Template::active()
            ->orderBy('name')
            ->get()
            ->map(fn($t) => [
                'slug' => $t->slug,
                'name' => $t->name,
                'preview_image' => $t->preview_image,
                'is_default' => $t->is_default,
            ])
            ->toArray();
    }

    /**
     * Invalidate template cache for a tenant
     * Call this when tenant settings or template changes
     */
    public function invalidateCache(Tenant $tenant): void
    {
        Cache::forget(self::CACHE_PREFIX . $tenant->id);
    }

    /**
     * Get template schema (for admin form generation)
     */
    public function getSchema(string $templateSlug): ?array
    {
        $template = Template::where('slug', $templateSlug)->first();
        
        if (!$template) {
            return null;
        }

        return $template->config_schema ?? [];
    }

    /**
     * Validate tenant settings against template schema
     * 
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateSettings(Tenant $tenant, array $settings): array
    {
        $schema = $this->getSchema($tenant->template_slug);
        $errors = [];

        if (!$schema) {
            return ['valid' => true, 'errors' => []];
        }

        // Basic validation - check required fields
        if (isset($schema['required']) && is_array($schema['required'])) {
            foreach ($schema['required'] as $field) {
                if (!isset($settings[$field]) || $settings[$field] === null) {
                    $errors[] = "Field '{$field}' is required";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
