<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;
use Inertia\Inertia;

/**
 * Onboarding Middleware
 *
 * Redirects tenants to the onboarding wizard if they haven't completed it.
 * Skips if they're already on a dashboard route or logging out.
 */
class RequireOnboarding
{
    /**
     * Routes that skip onboarding check.
     */
    protected array $skipRoutes = [
        'dashboard.onboarding',
        'dashboard.onboarding.store',
        'logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Skip if already on onboarding route
        $currentRoute = $request->route()?->getName();
        if ($currentRoute && in_array($currentRoute, $this->skipRoutes)) {
            return $next($request);
        }

        // Resolve tenant
        $tenant = Tenant::where('email', $user->email)->first();

        if (!$tenant) {
            return $next($request);
        }

        // Redirect to onboarding if not completed
        if (!$tenant->onboarding_completed) {
            if ($request->expectsJson()) {
                return response()->json([
                    'requires_onboarding' => true,
                    'redirect' => route('dashboard.onboarding'),
                ], 403);
            }

            return redirect()->route('dashboard.onboarding');
        }

        return $next($request);
    }
}
