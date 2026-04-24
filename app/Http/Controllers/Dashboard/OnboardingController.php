<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Onboarding Controller
 *
 * Handles the initial tenant setup wizard where they choose their business type.
 */
class OnboardingController extends Controller
{
    /**
     * Show onboarding wizard.
     */
    public function create(): Response
    {
        $tenant = $this->resolveTenant();

        return Inertia::render('Dashboard/Onboarding', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'onboarding_completed' => $tenant->onboarding_completed,
            ],
        ]);
    }

    /**
     * Store onboarding selection.
     */
    public function store(Request $request)
    {
        $tenant = $this->resolveTenant();

        $validated = $request->validate([
            'business_type' => 'required|in:catalog,booking',
        ]);

        $modules = match ($validated['business_type']) {
            'catalog' => ['catalog'],
            'booking' => ['booking'],
        };

        $tenant->update([
            'enabled_modules' => $modules,
            'onboarding_completed' => true,
        ]);

        return redirect('/dashboard');
    }

    /**
     * Resolve the current tenant from authenticated user.
     */
    protected function resolveTenant(): Tenant
    {
        $user = Auth::user();
        return Tenant::where('email', $user->email)->firstOrFail();
    }
}
