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
 * Supports both catalog (product) and booking (service) orders.
 */
class OrderController extends Controller
{
    /**
     * Store a new order from checkout.
     * Handles both catalog orders and booking orders.
     */
    public function store(StoreOrderRequest $request, string $store_link): JsonResponse
    {
        $tenant = Tenant::where('store_link', $store_link)->firstOrFail();

        // Determine module type
        $moduleType = $request->input('module_type', 'catalog');

        if ($moduleType === 'booking') {
            return $this->storeBooking($request, $tenant);
        }

        return $this->storeCatalogOrder($request, $tenant);
    }

    /**
     * Store a catalog order (products).
     */
    protected function storeCatalogOrder(StoreOrderRequest $request, Tenant $tenant): JsonResponse
    {
        // Calculate original subtotal from items
        $originalSubtotal = collect($request->items)->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'tenant_id' => $tenant->id,
                'module_type' => 'catalog',
                'orderable_type' => 'App\Models\Product',
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'shipping_address' => $request->customer_address,
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
                    'orderable_type' => 'App\Models\Product',
                    'orderable_id' => $itemData['product_id'],
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

    /**
     * Store a booking order (service appointment).
     */
    protected function storeBooking(StoreOrderRequest $request, Tenant $tenant): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_instagram' => 'nullable|string|max:255',
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date',
            'booking_time_slot' => 'required|string',
            'booking_duration_min' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'service_name' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'tenant_id' => $tenant->id,
                'module_type' => 'booking',
                'orderable_type' => 'App\Models\Service',
                'orderable_id' => $validated['service_id'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_instagram' => $validated['customer_instagram'] ?? null,
                'booking_date' => $validated['booking_date'],
                'booking_time_slot' => $validated['booking_time_slot'],
                'booking_duration_min' => $validated['booking_duration_min'],
                'original_subtotal' => $validated['price'],
                'adjusted_subtotal' => $validated['price'],
                'shipping_cost' => 0,
                'total' => $validated['price'],
            ]);

            // Create single order item for the service
            OrderItem::create([
                'order_id' => $order->id,
                'orderable_type' => 'App\Models\Service',
                'orderable_id' => $validated['service_id'],
                'product_name' => $validated['service_name'],
                'product_sku' => null,
                'original_quantity' => 1,
                'current_quantity' => 1,
                'unit_price' => $validated['price'],
                'currency' => 'IDR',
            ]);

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
                'message' => 'Failed to create booking. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
