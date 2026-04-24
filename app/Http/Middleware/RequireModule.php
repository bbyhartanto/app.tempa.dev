<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;

/**
 * Require Module Access Middleware
 *
 * Ensures tenant has the requested module enabled.
 * Redirects to settings if module is not enabled.
 */
class RequireModule
{
    public function handle(Request $request, Closure $next, string $module)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        $tenant = Tenant::where('email', $user->email)->first();

        if (!$tenant) {
            abort(404);
        }

        if (!$tenant->hasModule($module)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Module '{$module}' is not enabled for your store.",
                    'requires_upgrade' => !$tenant->canEnableModule($module),
                ], 403);
            }

            return redirect()->route('dashboard.settings')
                ->with('error', "The {$module} module is not enabled for your store.");
        }

        return $next($request);
    }
}
