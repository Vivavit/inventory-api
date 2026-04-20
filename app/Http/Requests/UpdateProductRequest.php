<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'exists:categories,id',
            'brand_id' => 'exists:brands,id',
            'name' => 'string|max:255',
            'slug' => 'string|unique:products,slug,'.$this->product->id,
            'sku' => 'string|unique:products,sku,'.$this->product->id,
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'manage_stock' => 'boolean',
            'stock_status' => 'in:in_stock,out_of_stock,low_stock',
            'is_active' => 'boolean',
        ];
    }
}
