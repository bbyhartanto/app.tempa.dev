<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Registered Tenant Controller
 * 
 * Handles new tenant registration (store owners).
 * Creates both tenant record and user account.
 */
class RegisteredTenantController extends Controller
{
    /**
     * Show registration form
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle registration request
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenants',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'store_name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string|max:20',
            'phone' => 'nullable|string|max:20',
        ]);

        // Create tenant (status: pending approval)
        $tenant = Tenant::create([
            'name' => $validated['store_name'],
            'email' => $validated['email'],
            'whatsapp_number' => $validated['whatsapp_number'],
            'phone' => $validated['phone'] ?? null,
            'status' => 'pending', // Requires super admin approval
            'template_slug' => 'minimal',
        ]);

        // Create user account linked to tenant
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'tenant_owner',
        ]);

        // Link tenant to user (store tenant_id in session for now)
        // In production, use proper multi-tenancy package or tenant_users pivot

        event(new Registered($user));

        Auth::login($user);

        // Store tenant context in session
        session(['current_tenant_id' => $tenant->id]);

        return redirect()->route('dashboard.home', ['store_link' => $tenant->store_link]);
    }
}
