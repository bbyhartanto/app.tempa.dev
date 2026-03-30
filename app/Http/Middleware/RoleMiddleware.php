<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role Middleware
 * 
 * Checks if authenticated user has required role.
 * Usage: Route::middleware(['role:super_admin'])
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  $role  Required role (e.g., 'super_admin')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return redirect()->route('login')
                ->with('error', 'Please log in to access this page');
        }

        if ($request->user()->role !== $role) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
