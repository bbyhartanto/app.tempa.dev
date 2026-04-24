<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Dashboard Service Controller
 * Handles tenant CRUD for services (booking module).
 */
class ServiceController extends Controller
{
    /**
     * List services for current tenant.
     */
    public function index(Request $request): Response
    {
        $tenant = $this->resolveTenant();

        $services = $tenant->services()
            ->orderBy('sort_order')
            ->get()
            ->map(fn($s) => $this->formatService($s));

        return Inertia::render('Dashboard/Services/Index', [
            'services' => $services,
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): Response
    {
        return Inertia::render('Dashboard/Services/Create', [
            'defaultSchedule' => [
                'start' => '09:00',
                'end' => '18:00',
                'duration' => 30,
                'buffer' => 0,
                'available_days' => [1, 2, 3, 4, 5],
            ],
        ]);
    }

    /**
     * Store a new service.
     */
    public function store(Request $request)
    {
        $tenant = $this->resolveTenant();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:services,slug,NULL,id,tenant_id,' . $tenant->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'duration_min' => 'required|integer|min:1',
            'buffer_min' => 'nullable|integer|min:0',
            'default_start' => 'nullable|date_format:H:i',
            'default_end' => 'nullable|date_format:H:i',
            'available_days' => 'nullable|array',
            'available_days.*' => 'integer|between:0,6',
            'time_slots' => 'nullable|array',
            'image_urls' => 'nullable|array',
            'is_available' => 'boolean',
        ]);

        $service = $tenant->services()->create([
            ...$validated,
            'currency' => $validated['currency'] ?? 'IDR',
            'buffer_min' => $validated['buffer_min'] ?? 0,
            'default_start' => $validated['default_start'] ?? '09:00',
            'default_end' => $validated['default_end'] ?? '18:00',
            'is_available' => $validated['is_available'] ?? true,
        ]);

        return redirect()->route('dashboard.services.index')
            ->with('success', 'Service created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(Service $service): Response
    {
        $tenant = $this->resolveTenant();

        if ($service->tenant_id !== $tenant->id) {
            abort(403);
        }

        return Inertia::render('Dashboard/Services/Edit', [
            'service' => $this->formatService($service),
        ]);
    }

    /**
     * Update a service.
     */
    public function update(Request $request, Service $service)
    {
        $tenant = $this->resolveTenant();

        if ($service->tenant_id !== $tenant->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:services,slug,' . $service->id . ',id,tenant_id,' . $tenant->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'duration_min' => 'required|integer|min:1',
            'buffer_min' => 'nullable|integer|min:0',
            'default_start' => 'nullable|date_format:H:i',
            'default_end' => 'nullable|date_format:H:i',
            'available_days' => 'nullable|array',
            'available_days.*' => 'integer|between:0,6',
            'time_slots' => 'nullable|array',
            'image_urls' => 'nullable|array',
            'is_available' => 'boolean',
        ]);

        $service->update($validated);

        return redirect()->route('dashboard.services.index')
            ->with('success', 'Service updated successfully.');
    }

    /**
     * Delete a service.
     */
    public function destroy(Service $service)
    {
        $tenant = $this->resolveTenant();

        if ($service->tenant_id !== $tenant->id) {
            abort(403);
        }

        $service->delete();

        return redirect()->route('dashboard.services.index')
            ->with('success', 'Service deleted successfully.');
    }

    /**
     * Toggle service availability.
     */
    public function toggleAvailability(Service $service)
    {
        $tenant = $this->resolveTenant();

        if ($service->tenant_id !== $tenant->id) {
            abort(403);
        }

        $service->update(['is_available' => !$service->is_available]);

        return redirect()->back()
            ->with('success', 'Service availability updated.');
    }

    /**
     * Format service data for frontend.
     */
    protected function formatService(Service $service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug,
            'description' => $service->description,
            'price' => (float) $service->price,
            'formatted_price' => $service->formatted_price,
            'currency' => $service->currency,
            'duration_min' => $service->duration_min,
            'buffer_min' => $service->buffer_min,
            'default_start' => $service->default_start,
            'default_end' => $service->default_end,
            'available_days' => $service->available_days,
            'time_slots' => $service->time_slots,
            'image_urls' => $service->image_urls ?? [],
            'first_image' => $service->first_image,
            'is_available' => $service->is_available,
            'sort_order' => $service->sort_order,
        ];
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
