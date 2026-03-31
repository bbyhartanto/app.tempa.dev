# Template Engine Documentation

## Overview

The Template Engine is a core feature of the Multi-tenant E-Catalog SaaS that allows tenants to customize the appearance and behavior of their storefronts without code changes.

```
┌─────────────────────────────────────────────────────────────┐
│                    Template Engine Flow                      │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐      ┌──────────────┐      ┌───────────┐ │
│  │  Template    │      │   Tenant     │      │   Vue     │ │
│  │  (Database)  │─────▶│  (Database)  │─────▶│  Frontend │ │
│  │              │      │              │      │           │ │
│  │ - slug       │      │ - template_  │      │ Receives  │ │
│  │ - name       │      │   slug       │      │ merged    │ │
│  │ - config_    │      │ - settings   │      │ config    │ │
│  │   schema     │      │   (JSON)     │      │ props     │ │
│  └──────────────┘      └──────────────┘      └───────────┘ │
│         │                     │                    │        │
│         └─────────────────────┴────────────────────┘        │
│                           │                                  │
│                    ┌──────▼──────┐                          │
│                    │ Template    │                          │
│                    │ Engine      │                          │
│                    │ Service     │                          │
│                    │             │                          │
│                    │ Merges:     │                          │
│                    │ 1. Defaults │                          │
│                    │ 2. Template │                          │
│                    │ 3. Tenant   │                          │
│                    └─────────────┘                          │
└─────────────────────────────────────────────────────────────┘
```

---

## Architecture

### Design Principles

1. **Database-Driven**: Templates are stored in the database, not code
2. **JSON Configuration**: Flexible schema for customization options
3. **Merge Priority**: Tenant settings override template defaults
4. **Cacheable**: Configuration can be cached per tenant
5. **No Code Changes**: Add new templates without deploying code

---

## Database Structure

### Templates Table

Stores available templates and their configuration schemas.

```php
Schema::create('templates', function (Blueprint $table) {
    $table->id();
    $table->string('slug')->unique();        // Template identifier
    $table->string('name');                   // Display name
    $table->boolean('is_active')->default(true);
    $table->boolean('is_default')->default(false);
    $table->json('config_schema')->nullable(); // Available options
    $table->string('preview_image')->nullable();
    $table->timestamps();
});
```

### Tenants Table

Stores tenant-specific template selection and customizations.

```php
Schema::create('tenants', function (Blueprint $table) {
    // ... other fields
    
    $table->string('template_slug')->default('minimal');  // Selected template
    $table->json('settings')->nullable();                  // Custom values
    $table->timestamps();
});
```

---

## Data Structure

### Template Record Example

```php
// From: database/seeders/TemplateSeeder.php

Template::create([
    'slug' => 'minimal',
    'name' => 'Minimal',
    'is_active' => true,
    'is_default' => true,
    'config_schema' => [
        'colors' => [
            'primary' => '#3B82F6',    // Default blue
            'secondary' => '#6B7280',
            'accent' => '#10B981',
        ],
        'layout' => [
            'show_prices' => true,
            'show_description' => true,
            'products_per_row' => 2,
            'show_product_images' => true,
        ],
    ],
    'preview_image' => null,
]);
```

### Tenant Record Example

```php
Tenant::create([
    'name' => 'Kopi Senja',
    'template_slug' => 'minimal',  // Uses 'minimal' template
    'settings' => [
        // Override only what you want to change
        'colors' => [
            'primary' => '#78350F',  // Brown instead of blue
        ],
    ],
]);
```

---

## Template Engine Service

### Location
`app/Services/TemplateEngine.php`

### Implementation

```php
<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Template;
use Illuminate\Support\Facades\Cache;

class TemplateEngine
{
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
    ];

    /**
     * Get merged template configuration for a tenant
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

        // Merge tenant's custom settings (highest priority)
        if ($tenant->settings) {
            $config = array_merge_recursive($config, $tenant->settings);
        }

        // Cache for 1 hour
        Cache::put($cacheKey, $config, 3600);

        return $config;
    }

    /**
     * Invalidate cache when tenant updates settings
     */
    public function invalidateCache(Tenant $tenant): void
    {
        Cache::forget(self::CACHE_PREFIX . $tenant->id);
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
}
```

---

## Configuration Merge Priority

```
┌──────────────────────────────────────────────────────────┐
│              Configuration Merge Order                    │
├──────────────────────────────────────────────────────────┤
│                                                           │
│  1. Default Config (lowest priority)                      │
│     └─ Always applied first                               │
│     └─ Ensures all keys exist                             │
│                                                           │
│  2. Template Schema (medium priority)                     │
│     └─ Defines template-specific defaults                 │
│     └─ Can override defaults                              │
│                                                           │
│  3. Tenant Settings (⭐ highest priority)                 │
│     └─ Tenant's customizations                            │
│     └─ Overrides everything                               │
│                                                           │
└──────────────────────────────────────────────────────────┘
```

### Example Merge Result

```json
{
  "colors": {
    "primary": "#78350F",    // ← From tenant (overrides template)
    "secondary": "#6B7280",  // ← From template (tenant didn't override)
    "accent": "#10B981"      // ← From defaults
  },
  "layout": {
    "show_prices": true,     // ← From template
    "products_per_row": 2    // ← From defaults
  }
}
```

---

## Usage in Controllers

### Storefront Controller

```php
// app/Http/Controllers/StorefrontController.php

public function index(Request $request): Response
{
    $tenant = $request->attributes->get('tenant');

    // Get merged template configuration
    $templateConfig = $this->templateEngine->getConfig($tenant);

    return Inertia::render('Storefront/Home', [
        'tenant' => [
            'id' => $tenant->id,
            'name' => $tenant->name,
            // ...
        ],
        'products' => $products,
        'templateConfig' => $templateConfig,  // ⭐ Passed to Vue
    ]);
}
```

### Dashboard Controller

```php
// app/Http/Controllers/DashboardController.php

public function settings(Request $request): Response
{
    $tenant = $this->getCurrentTenant();

    $availableTemplates = $this->templateEngine->getAvailableTemplates();
    $currentTemplateConfig = $this->templateEngine->getConfig($tenant);

    return Inertia::render('Dashboard/Settings', [
        'tenant' => $tenant,
        'availableTemplates' => $availableTemplates,
        'templateConfig' => $currentTemplateConfig,
    ]);
}
```

---

## Usage in Vue Components

### Storefront Home

```vue
<!-- resources/js/Pages/Storefront/Home.vue -->

<script setup>
const props = defineProps({
    templateConfig: {
        type: Object,
        required: true,
    },
    products: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div class="storefront">
        <!-- Use template colors -->
        <h1 :style="{ color: props.templateConfig.colors.primary }">
            {{ tenant.name }}
        </h1>
        
        <!-- Use template layout settings -->
        <div 
            :class="`grid grid-cols-${props.templateConfig.layout.products_per_row}`"
        >
            <div v-for="product in products" :key="product.id">
                <h3>{{ product.title }}</h3>
                
                <!-- Conditionally show prices -->
                <p v-if="props.templateConfig.layout.show_prices">
                    {{ product.formatted_price }}
                </p>
                
                <!-- Conditionally show description -->
                <p v-if="props.templateConfig.layout.show_description">
                    {{ product.description }}
                </p>
            </div>
        </div>
    </div>
</template>
```

### Dashboard Settings

```vue
<!-- resources/js/Pages/Dashboard/Settings/Index.vue -->

<script setup>
const props = defineProps({
    availableTemplates: Array,
    templateConfig: Object,
    tenant: Object,
});

const form = ref({
    template_slug: props.tenant.template_slug,
    settings: props.tenant.settings || {},
});
</script>

<template>
    <div class="settings-page">
        <h2>Store Template</h2>
        
        <select v-model="form.template_slug">
            <option 
                v-for="template in availableTemplates" 
                :key="template.slug" 
                :value="template.slug"
            >
                {{ template.name }}
            </option>
        </select>
        
        <!-- Preview current template config -->
        <div class="preview">
            <h4>Current Template Preview</h4>
            <p>Primary Color: 
                <span 
                    class="color-swatch" 
                    :style="{ backgroundColor: templateConfig.colors?.primary }"
                ></span>
            </p>
            <p>Products per row: {{ templateConfig.layout?.products_per_row }}</p>
            <p>Show prices: {{ templateConfig.layout?.show_prices ? 'Yes' : 'No' }}</p>
        </div>
    </div>
</template>
```

---

## Adding New Templates

### Step 1: Create Template in Database

```php
use App\Models\Template;

Template::create([
    'slug' => 'dark-mode',
    'name' => 'Dark Mode',
    'is_active' => true,
    'is_default' => false,
    'config_schema' => [
        'colors' => [
            'primary' => '#FFFFFF',    // White text
            'secondary' => '#1F2937',  // Dark background
            'accent' => '#F59E0B',     // Amber accent
        ],
        'layout' => [
            'show_prices' => true,
            'products_per_row' => 3,
            'dark_mode' => true,       // Custom flag
        ],
    ],
    'preview_image' => '/images/templates/dark-mode.jpg',
]);
```

### Step 2: Tenant Selects Template

```php
// Via Dashboard Settings
$tenant->update([
    'template_slug' => 'dark-mode',
    'settings' => [
        // Optional: further customize
        'colors' => [
            'accent' => '#EF4444',  // Change accent to red
        ],
    ],
]);
```

### Step 3: Vue Handles New Config

```vue
<template>
    <div :class="{ 'bg-gray-900 text-white': templateConfig.layout.dark_mode }">
        <!-- Automatically applies dark mode styles -->
    </div>
</template>
```

---

## Available Templates (Seed Data)

### 1. Minimal (Default)

```json
{
  "slug": "minimal",
  "name": "Minimal",
  "config_schema": {
    "colors": {
      "primary": "#3B82F6",
      "secondary": "#6B7280",
      "accent": "#10B981"
    },
    "layout": {
      "show_prices": true,
      "show_description": true,
      "products_per_row": 2
    }
  }
}
```

### 2. Modern

```json
{
  "slug": "modern",
  "name": "Modern",
  "config_schema": {
    "colors": {
      "primary": "#7C3AED",
      "secondary": "#4B5563",
      "accent": "#F59E0B"
    },
    "layout": {
      "show_prices": true,
      "show_description": true,
      "products_per_row": 3,
      "large_hero": true
    }
  }
}
```

### 3. Elegant

```json
{
  "slug": "elegant",
  "name": "Elegant",
  "config_schema": {
    "colors": {
      "primary": "#1F2937",
      "secondary": "#9CA3AF",
      "accent": "#D4AF37"
    },
    "layout": {
      "show_prices": true,
      "show_description": true,
      "products_per_row": 2,
      "serif_fonts": true
    }
  }
}
```

---

## Service Provider Registration

```php
// app/Providers/TemplateEngineServiceProvider.php

namespace App\Providers;

use App\Services\TemplateEngine;
use Illuminate\Support\ServiceProvider;

class TemplateEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TemplateEngine::class, function ($app) {
            return new TemplateEngine();
        });
    }

    public function boot(): void
    {
        //
    }
}
```

---

## Caching Strategy

### Cache Key Format
```
template_config:{tenant_id}
```

### Cache Duration
- **Default**: 1 hour (3600 seconds)
- **Invalidation**: When tenant updates settings

### Manual Cache Invalidation
```php
// In DashboardController after settings update
$this->templateEngine->invalidateCache($tenant);
```

---

## Current Limitations (MVP)

| Limitation | Description |
|------------|-------------|
| **No Template Inheritance** | Can't extend another template |
| **No Partial Overrides** | Can't override just header/footer |
| **No Visual Editor** | Tenants edit via database/settings form |
| **No Schema Validation** | No validation on tenant settings |
| **Global Templates** | Same template for entire store |
| **No Versioning** | Can't rollback template changes |

---

## Future Enhancements

### 1. Visual Template Editor

```vue
<template>
    <div class="template-editor">
        <!-- Color picker -->
        <color-picker 
            v-model="settings.colors.primary" 
            label="Primary Color" 
        />
        
        <!-- Toggle switches -->
        <toggle 
            v-model="settings.layout.show_prices" 
            label="Show Prices" 
        />
        
        <!-- Slider -->
        <slider 
            v-model="settings.layout.products_per_row" 
            :min="1" 
            :max="4" 
        />
        
        <!-- Live preview -->
        <preview :config="settings" />
    </div>
</template>
```

### 2. Template Inheritance

```php
Template::create([
    'slug' => 'modern-dark',
    'name' => 'Modern Dark',
    'parent_slug' => 'modern',  // Extend 'modern' template
    'config_schema' => [
        'colors' => [
            'secondary' => '#1F2937',  // Override only this
        ],
    ],
]);
```

### 3. Schema Validation

```php
public function validateSettings(Tenant $tenant, array $settings): array
{
    $schema = $this->getSchema($tenant->template_slug);
    $errors = [];

    if (isset($schema['required'])) {
        foreach ($schema['required'] as $field) {
            if (!isset($settings[$field])) {
                $errors[] = "Field '{$field}' is required";
            }
        }
    }

    return ['valid' => empty($errors), 'errors' => $errors];
}
```

### 4. Cloud Integration

```vue
<script setup>
function openCloudinary() {
    const widget = cloudinary.createUploadWidget({
        cloudName: 'your-cloud',
        uploadPreset: 'your-preset'
    }, (error, result) => {
        if (result.event === 'success') {
            form.value.images.push(result.info.secure_url);
        }
    });
    widget.open();
}
</script>
```

---

## Files Reference

| File | Purpose |
|------|---------|
| `app/Services/TemplateEngine.php` | Core service logic |
| `app/Models/Template.php` | Template model |
| `app/Models/Tenant.php` | Tenant model with template relation |
| `app/Providers/TemplateEngineServiceProvider.php` | Service registration |
| `database/seeders/TemplateSeeder.php` | Default templates |
| `resources/js/Pages/Storefront/Home.vue` | Frontend usage example |
| `resources/js/Pages/Dashboard/Settings/Index.vue` | Settings page |

---

## Summary

The Template Engine provides a flexible, database-driven theming system that allows:

1. **Multiple Templates**: Define unlimited templates in the database
2. **Tenant Customization**: Each tenant can override template defaults
3. **No Code Changes**: Add/modify templates without deploying code
4. **Performance**: Cached configuration for fast page loads
5. **Scalability**: Easy to add new customization options

This MVP implementation provides a solid foundation for a full-featured theme marketplace in the future.
