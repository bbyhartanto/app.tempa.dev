<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization checked in controller (tenant ownership)
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => 'nullable|in:pending,confirmed,paid',
            'payment_status' => 'nullable|in:unpaid,paid',
            'shipping_cost' => 'nullable|numeric|min:0',
            'payment_notes' => 'nullable|string|max:1000',
            'adjustment_notes' => 'nullable|string|max:1000',
            'shipping_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Invalid order status.',
            'payment_status.in' => 'Invalid payment status.',
            'shipping_cost.numeric' => 'Shipping cost must be a number.',
            'shipping_cost.min' => 'Shipping cost must be greater than or equal to 0.',
            'shipping_receipt.file' => 'Shipping receipt must be a file.',
            'shipping_receipt.mimes' => 'Shipping receipt must be jpg, jpeg, png, or pdf.',
            'shipping_receipt.max' => 'Shipping receipt must not exceed 2MB.',
        ];
    }
}
