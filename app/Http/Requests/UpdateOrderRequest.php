<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'exists:customers,id',
            'coupon_id' => 'nullable|exists:coupons,id',
            'subtotal' => 'numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total' => 'numeric|min:0',
            'status' => 'in:pending,confirmed,shipped,delivered,cancelled',
            'shipping_full_name' => 'string|max:255',
            'shipping_phone' => 'string|max:20',
            'shipping_address_line_1' => 'string|max:255',
            'shipping_address_line_2' => 'nullable|string|max:255',
            'shipping_city' => 'string|max:100',
            'shipping_state' => 'string|max:100',
            'shipping_postal_code' => 'string|max:20',
            'shipping_country' => 'string|max:100',
        ];
    }
}
