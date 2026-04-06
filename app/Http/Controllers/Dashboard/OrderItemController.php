<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Tenant;
use App\Http\Requests\UpdateOrderItemQuantityRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Dashboard Order Item Controller
 *
 * Tenant order item management - update quantity, remove, restore.
 */
class OrderItemController extends Controller
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
     * Update item quantity.
     */
    public function updateQuantity(UpdateOrderItemQuantityRequest $request, int $orderId, int $itemId)
    {
        $tenant = $this->getCurrentTenant();

        // Verify order belongs to tenant
        $order = Order::where('tenant_id', $tenant->id)->findOrFail($orderId);

        // Get item
        $item = OrderItem::where('order_id', $order->id)
            ->findOrFail($itemId);

        // Update quantity
        $item->updateQuantity($request->quantity, $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Quantity updated successfully',
            'item' => [
                'id' => $item->id,
                'current_quantity' => $item->current_quantity,
                'subtotal' => $item->subtotal,
                'formatted_subtotal' => $item->formatted_subtotal,
            ],
            'order' => [
                'adjusted_subtotal' => $order->adjusted_subtotal,
                'total' => $order->total,
            ],
        ]);
    }

    /**
     * Remove item from order (soft delete).
     */
    public function destroy(int $orderId, int $itemId)
    {
        $tenant = $this->getCurrentTenant();

        // Verify order belongs to tenant
        $order = Order::where('tenant_id', $tenant->id)->findOrFail($orderId);

        // Get item
        $item = OrderItem::where('order_id', $order->id)
            ->findOrFail($itemId);

        // Mark as removed
        $notes = request('notes', 'Item removed by store');
        $item->markAsRemoved($notes);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from order',
            'item' => [
                'id' => $item->id,
                'is_removed' => $item->is_removed,
                'current_quantity' => $item->current_quantity,
            ],
            'order' => [
                'adjusted_subtotal' => $order->adjusted_subtotal,
                'total' => $order->total,
            ],
        ]);
    }

    /**
     * Restore removed item.
     */
    public function restore(int $orderId, int $itemId)
    {
        $tenant = $this->getCurrentTenant();

        // Verify order belongs to tenant
        $order = Order::where('tenant_id', $tenant->id)->findOrFail($orderId);

        // Get item
        $item = OrderItem::where('order_id', $order->id)
            ->findOrFail($itemId);

        // Restore
        $notes = request('notes', 'Item restored by store');
        $item->restore($notes);

        return response()->json([
            'success' => true,
            'message' => 'Item restored successfully',
            'item' => [
                'id' => $item->id,
                'is_removed' => $item->is_removed,
                'current_quantity' => $item->current_quantity,
                'subtotal' => $item->subtotal,
                'formatted_subtotal' => $item->formatted_subtotal,
            ],
            'order' => [
                'adjusted_subtotal' => $order->adjusted_subtotal,
                'total' => $order->total,
            ],
        ]);
    }
}
