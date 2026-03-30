<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Authenticated Session Controller
 *
 * Handles tenant login/logout.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Show login form
     */
    public function create(): Response
    {
        // Redirect if already logged in
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'super_admin') {
                return redirect()->route('admin.tenants.index');
            }
            return redirect()->route('dashboard.home');
        }

        return Inertia::render('Auth/Login');
    }

    /**
     * Handle login request
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        // Role-based redirect
        if ($user->role === 'super_admin') {
            return redirect()->intended(route('admin.tenants.index'));
        }

        if ($user->role === 'tenant_owner') {
            $tenant = Tenant::where('email', $user->email)->first();
            if ($tenant) {
                if ($tenant->status === 'pending') {
                    Auth::logout();
                    throw ValidationException::withMessages([
                        'email' => 'Your store is pending approval. Please wait for admin approval.',
                    ]);
                }
                if ($tenant->status === 'suspended') {
                    Auth::logout();
                    throw ValidationException::withMessages([
                        'email' => 'Your store has been suspended. Please contact support.',
                    ]);
                }
            }
            return redirect()->intended(route('dashboard.home'));
        }

        return redirect()->intended('/');
    }

    /**
     * Handle logout request
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
