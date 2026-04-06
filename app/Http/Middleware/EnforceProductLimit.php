<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Product Limit Enforcement Middleware
 * 
 * Prevents tenants from adding products beyond their subscription tier limit.
 */
class EnforceProductLimit
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to product creation
        if ($request->is('dashboard/products') && $request->isMethod('POST')) {
            $user = Auth::user();
            
            if ($user && $user->role === 'tenant_owner') {
                // Get tenant from session
                $tenantId = session('current_tenant_id');
                
                if ($tenantId) {
                    $tenant = \App\Models\Tenant::find($tenantId);
                    
                    if ($tenant && !$this->subscriptionService->canAddProduct($tenant)) {
                        return redirect()->back()
                            ->with('error', 'Product limit reached. Please upgrade your subscription to add more products.');
                    }
                }
            }
        }

        return $next($request);
    }
}
