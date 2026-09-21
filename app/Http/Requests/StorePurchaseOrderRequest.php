<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isStaff());
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:draft,ordered'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
        ];
    }
}
