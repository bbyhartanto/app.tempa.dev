<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Tenant;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Order Controller (Public)
 *
 * Handles order creation from storefront checkout.
 */
class OrderController extends Controller
{
    /**
     * Store a new order from checkout.
     */
    public function store(StoreOrderRequest $request, string $store_link): JsonResponse
    {
        $tenant = Tenant::where('store_link', $store_link)->firstOrFail();

        // Calculate original subtotal from items
        $originalSubtotal = collect($request->items)->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'tenant_id' => $tenant->id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'original_subtotal' => $originalSubtotal,
                'adjusted_subtotal' => $originalSubtotal,
                'shipping_cost' => 0,
                'total' => $originalSubtotal,
            ]);

            // Create order items
            foreach ($request->items as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'product_name' => $itemData['product_name'],
                    'product_sku' => $itemData['product_sku'] ?? null,
                    'original_quantity' => $itemData['quantity'],
                    'current_quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'currency' => $itemData['currency'] ?? 'IDR',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'receipt_url' => route('receipt.show', $order->order_number),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
