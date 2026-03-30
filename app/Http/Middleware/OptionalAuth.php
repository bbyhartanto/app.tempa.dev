<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Optional Auth Middleware
 * 
 * Makes auth optional - sets auth in Inertia shared data if logged in,
 * but doesn't redirect. Used for landing pages that need to show
 * different content based on auth status.
 */
class OptionalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Don't redirect, just ensure auth is available
        return $next($request);
    }
}
