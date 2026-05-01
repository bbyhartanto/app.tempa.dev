<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Tenant;
use App\Http\Requests\UpdateOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Dashboard Order Controller
 *
 * Tenant order management - list and view orders.
 */
class OrderController extends Controller
{
    /**
     * Get current tenant from authenticated user.
     */
    private function getCurrentTenant(): Tenant
    {
        $user = Auth::user();
        $tenant = Tenant::where('email', $user->email)->first();

        if (!$tenant) {
            abort(403, 'No store associated with this account');
        }

        return $tenant;
    }

    /**
     * Display order list.
     */
    public function index(Request $request): Response
    {
        $tenant = $this->getCurrentTenant();

        $query = $tenant->orders()->with(['items']);

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $orders = $query->paginate(20)->withQueryString();

        return Inertia::render('Dashboard/Orders/Index', [
            'orders' => [
                'data' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'module_type' => $order->module_type,
                        'customer_name' => $order->customer_name,
                        'customer_phone' => $order->customer_phone,
                        'status' => $order->status,
                        'status_color' => $order->status_color,
                        'payment_status' => $order->payment_status,
                        'total' => $order->total,
                        'formatted_total' => $order->formatted_total,
                        'booking_date' => $order->booking_date?->format('Y-m-d'),
                        'booking_time_slot' => $order->booking_time_slot,
                        'booking_duration_min' => $order->booking_duration_min,
                        'items_count' => $order->items->count(),
                        'active_items_count' => $order->items->where('is_removed', false)->count(),
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'created_at_formatted' => $order->created_at->format('M j, Y g:i A'),
                        'is_modified' => $order->is_modified,
                    ];
                }),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
            ],
            'statusOptions' => [
                'pending' => 'Waiting merchant to confirm',
                'confirmed' => 'Confirmed',
                'paid' => 'Paid',
            ],
        ]);
    }

    /**
     * Display order detail.
     */
    public function show(int $id): Response
    {
        $tenant = $this->getCurrentTenant();

        $order = Order::with(['items.product', 'histories.changedBy'])
            ->where('tenant_id', $tenant->id)
            ->findOrFail($id);

        return Inertia::render('Dashboard/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'module_type' => $order->module_type,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_address' => $order->customer_address,
                'shipping_address' => $order->shipping_address,
                'status' => $order->status,
                'status_color' => $order->status_color,
                'payment_status' => $order->payment_status,
                'original_subtotal' => $order->original_subtotal,
                'adjusted_subtotal' => $order->adjusted_subtotal,
                'shipping_cost' => $order->shipping_cost,
                'total' => $order->total,
                'payment_notes' => $order->payment_notes,
                'adjustment_notes' => $order->adjustment_notes,
                'shipping_receipt' => $order->shipping_receipt,
                'booking_date' => $order->booking_date?->format('Y-m-d'),
                'booking_formatted_date' => $order->formatted_booking_date,
                'booking_time_slot' => $order->booking_time_slot,
                'booking_duration_min' => $order->booking_duration_min,
                'google_calendar_link' => $order->google_calendar_link,
                'is_modified' => $order->is_modified,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'created_at_formatted' => $order->created_at->format('F j, Y g:i A'),
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku,
                    'original_quantity' => $item->original_quantity,
                    'current_quantity' => $item->current_quantity,
                    'unit_price' => $item->unit_price,
                    'currency' => $item->currency,
                    'subtotal' => $item->subtotal,
                    'formatted_subtotal' => $item->formatted_subtotal,
                    'original_subtotal' => $item->original_subtotal,
                    'is_removed' => $item->is_removed,
                    'removed_at' => $item->removed_at?->format('Y-m-d H:i:s'),
                    'product_exists' => $item->product !== null,
                ];
            }),
            'histories' => $order->histories->map(function ($history) {
                return [
                    'id' => $history->id,
                    'action' => $history->action,
                    'action_label' => $history->action_label,
                    'change_summary' => $history->change_summary,
                    'notes' => $history->notes,
                    'changed_by' => $history->changedBy?->name ?? 'System',
                    'created_at' => $history->created_at->format('Y-m-d H:i:s'),
                    'created_at_formatted' => $history->created_at->format('M j, Y g:i A'),
                ];
            }),
            'statusOptions' => [
                'pending' => 'Waiting merchant to confirm',
                'confirmed' => 'Confirmed',
                'paid' => 'Paid',
            ],
        ]);
    }

    /**
     * Update order (shipping, notes, status, receipt).
     */
    public function update(UpdateOrderRequest $request, int $id)
    {
        $tenant = $this->getCurrentTenant();

        $order = Order::where('tenant_id', $tenant->id)->findOrFail($id);

        $oldValues = $order->only([
            'status', 'payment_status', 'shipping_cost', 'payment_notes', 'adjustment_notes'
        ]);

        $updateData = $request->only([
            'status', 'payment_status', 'shipping_cost', 'payment_notes', 'adjustment_notes'
        ]);

        // Handle file upload
        if ($request->hasFile('shipping_receipt')) {
            $file = $request->file('shipping_receipt');
            $path = $file->store('shipping-receipts', 'public');
            $updateData['shipping_receipt'] = $path;
        }

        // Filter out null values
        $updateData = array_filter($updateData, function ($value) {
            return $value !== null;
        });

        $order->update($updateData);

        // Log changes to history
        $newValues = $order->only(array_keys($updateData));

        foreach ($updateData as $field => $value) {
            $action = match($field) {
                'shipping_cost' => $oldValues['shipping_cost'] == 0 ? 'shipping_added' : 'shipping_updated',
                'payment_notes' => $oldValues['payment_notes'] == null ? 'payment_notes_added' : 'payment_notes_updated',
                'status' => 'status_updated',
                'shipping_receipt' => 'receipt_uploaded',
                'adjustment_notes' => 'adjustment_notes_added',
                default => 'order_updated',
            };

            \App\Models\OrderHistory::create([
                'order_id' => $order->id,
                'changed_by' => Auth::id(),
                'action' => $action,
                'old_values' => [$field => $oldValues[$field] ?? null],
                'new_values' => [$field => $value],
                'notes' => $request->notes ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Order updated successfully');
    }

    /**
     * Accept order (change status from pending to confirmed).
     */
    public function accept(int $id)
    {
        $tenant = $this->getCurrentTenant();

        $order = Order::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($order->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending orders can be accepted');
        }

        $order->update(['status' => 'confirmed']);

        \App\Models\OrderHistory::create([
            'order_id' => $order->id,
            'changed_by' => Auth::id(),
            'action' => 'order_accepted',
            'old_values' => ['status' => 'pending'],
            'new_values' => ['status' => 'confirmed'],
            'notes' => 'Order accepted by merchant',
        ]);

        return redirect()->back()->with('success', 'Order accepted successfully');
    }

    /**
     * Mark order as paid (change status from confirmed to paid).
     */
    public function markPaid(int $id)
    {
        $tenant = $this->getCurrentTenant();

        $order = Order::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($order->status !== 'confirmed') {
            return redirect()->back()->with('error', 'Only confirmed orders can be marked as paid');
        }

        $order->update(['status' => 'paid']);

        \App\Models\OrderHistory::create([
            'order_id' => $order->id,
            'changed_by' => Auth::id(),
            'action' => 'order_paid',
            'old_values' => ['status' => 'confirmed'],
            'new_values' => ['status' => 'paid'],
            'notes' => 'Payment received and order marked as paid',
        ]);

        return redirect()->back()->with('success', 'Order marked as paid successfully');
    }
}
