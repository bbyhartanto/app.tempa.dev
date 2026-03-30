<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tenant Resolution Middleware
 * 
 * Resolves tenant from URL path: /{store_link}/*
 * Shares tenant data with Inertia views.
 * 
 * Tradeoffs:
 * - Uses path-based resolution (store_link) for SEO-friendly URLs
 * - Caches tenant in request attribute to avoid multiple lookups
 * - Returns 404 for inactive/suspended tenants (security)
 */
class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract store_link from first path segment
        // URL pattern: /{store_link}/* or /{store_link}
        $storeLink = $request->route('store_link') ?? $request->segment(1);

        if (!$storeLink) {
            return $next($request);
        }

        // Resolve tenant by store_link
        // Only active tenants can be accessed
        $tenant = Tenant::where('store_link', $storeLink)
            ->active()
            ->first();

        if (!$tenant) {
            // Tenant not found or not active
            // Return 404 to prevent enumeration of tenant slugs
            abort(404, 'Store not found');
        }

        // Share tenant with the request for later use
        $request->merge(['tenant' => $tenant]);
        
        // Store in request attribute for controllers to access
        $request->attributes->set('tenant', $tenant);

        // Share with Inertia views globally
        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            // For API/Inertia requests, tenant will be shared via controller
        }

        return $next($request);
    }
}
