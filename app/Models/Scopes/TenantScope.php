<?php

namespace App\Models\Scopes;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Request;

/**
 * Tenant Scope - Optional Global Tenant Isolation
 * 
 * SECURITY NOTE: This scope is NOT applied globally by default.
 * 
 * Reason: In multi-tenant apps, you often need cross-tenant queries:
 * - Super admin dashboard
 * - System-wide reports
 * - Tenant migration tools
 * 
 * Instead, use EXPLICIT scoping in controllers:
 *   Product::forTenant($tenantId)->get();
 * 
 * If you want to enable global scoping:
 * 1. Add to Product model boot(): static::addGlobalScope(new TenantScope);
 * 2. Ensure tenant context is always set via app()->singleton('current_tenant')
 * 
 * Tradeoff: Explicit > Implicit for security-critical operations
 */
class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = $this->getCurrentTenantId();
        
        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        }
    }

    /**
     * Get current tenant ID from context.
     * Returns null if no tenant context (allows cross-tenant queries).
     */
    private function getCurrentTenantId(): ?int
    {
        // Check request attribute (set by ResolveTenant middleware)
        $tenant = request()->attributes->get('tenant');
        
        if ($tenant instanceof Tenant) {
            return $tenant->id;
        }

        // Check app singleton (for API/console context)
        $tenant = app('current_tenant') ?? null;
        
        if ($tenant instanceof Tenant) {
            return $tenant->id;
        }

        return null;
    }
}
