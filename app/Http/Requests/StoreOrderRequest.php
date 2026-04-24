<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $moduleType = $this->input('module_type', 'catalog');

        // Catalog orders require items
        if ($moduleType === 'catalog') {
            return [
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:50',
                'customer_address' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.product_name' => 'required|string|max:255',
                'items.*.product_sku' => 'nullable|string|max:100',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.currency' => 'nullable|string|max:10',
            ];
        }

        // Booking orders require service details
        return [
            'module_type' => 'required|string|in:booking',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_instagram' => 'nullable|string|max:255',
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date',
            'booking_time_slot' => 'required|string',
            'booking_duration_min' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'service_name' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required to create an order.',
            'items.*.product_id.exists' => 'One or more products do not exist.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_price.min' => 'Price must be greater than or equal to 0.',
        ];
    }
}
