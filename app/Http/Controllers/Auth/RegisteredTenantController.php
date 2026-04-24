<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Show registration pending approval page.
     */
    public function pending(Request $request): Response|RedirectResponse
    {
        $registration = $request->session()->get('pending_registration');

        if (!$registration) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/RegistrationPending', [
            'registration' => $registration,
        ]);
    }

    /**
     * Handle registration request
     */
    public function store(Request $request, SubscriptionService $subscriptionService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenants,email|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'store_name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string|max:20',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = null;
        $tenant = null;

        DB::transaction(function () use ($validated, $subscriptionService, &$user, &$tenant) {
            // Create tenant (status: pending approval)
            $tenant = Tenant::create([
                'name' => $validated['store_name'],
                'email' => $validated['email'],
                'whatsapp_number' => $validated['whatsapp_number'],
                'phone' => $validated['phone'] ?? null,
                'status' => 'pending',
                'template_slug' => 'minimal',
            ]);

            // Start trial period (7 days)
            $subscriptionService->startTrial($tenant);

            // Create user account linked to tenant
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'tenant_owner',
            ]);
        });

        event(new Registered($user));

        $request->session()->put('pending_registration', [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'store_name' => $tenant->name,
            'store_link' => $tenant->store_link,
        ]);

        return redirect()->route('register.pending');
    }
}
