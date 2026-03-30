<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tenant Role Middleware
 * 
 * Ensures only tenant_owner users can access tenant dashboard.
 */
class TenantRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->role !== 'tenant_owner') {
            // Super admin trying to access tenant dashboard
            abort(403, 'Access denied. Super admins cannot access tenant dashboards.');
        }

        return $next($request);
    }
}
