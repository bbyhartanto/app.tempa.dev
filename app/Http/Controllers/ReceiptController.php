<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Receipt Controller (Public)
 *
 * Displays public receipt page for orders.
 * Accessible to anyone with the order number.
 */
class ReceiptController extends Controller
{
    /**
     * Display public receipt page.
     */
    public function show(string $order_number): Response
    {
        $order = Order::with(['tenant', 'items', 'histories.changedBy'])
            ->where('order_number', $order_number)
            ->firstOrFail();

        // Get all items (including removed ones for transparency)
        $items = $order->items()->get();

        return Inertia::render('Receipt/Show', [
            'tenant' => [
                'name' => $order->tenant->name,
                'logo_url' => $order->tenant->logo_url,
            ],
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_address' => $order->customer_address,
                'status' => $order->status,
                'status_color' => $order->status_color,
                'payment_status' => $order->payment_status,
                'original_subtotal' => $order->original_subtotal,
                'adjusted_subtotal' => $order->adjusted_subtotal,
                'shipping_cost' => $order->shipping_cost,
                'total' => $order->total,
                'formatted_total' => $order->formatted_total,
                'payment_notes' => $order->payment_notes,
                'adjustment_notes' => $order->adjustment_notes,
                'shipping_receipt' => $order->shipping_receipt,
                'is_modified' => $order->is_modified,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'created_at_formatted' => $order->created_at->format('F j, Y g:i A'),
            ],
            'items' => $items->map(function ($item) {
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
                    'is_removed' => $item->is_removed,
                    'removed_at' => $item->removed_at?->format('Y-m-d H:i:s'),
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
        ]);
    }
}
