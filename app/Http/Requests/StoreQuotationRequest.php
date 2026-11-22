<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items', []);
        if (is_array($items)) {
            $updated = false;
            foreach ($items as $idx => $item) {
                $price = isset($item['unit_price']) && is_numeric($item['unit_price']) ? (float) $item['unit_price'] : 0.0;
                if ($price <= 0) {
                    $prod = null;
                    if (!empty($item['product_id'])) {
                        $prod = \App\Models\Product::find($item['product_id']);
                    } elseif (!empty($item['item_name'])) {
                        $prod = \App\Models\Product::where('name', trim($item['item_name']))
                            ->orWhere('sku', trim($item['item_name']))
                            ->first();
                    }
                    if ($prod && (float) $prod->unit_price > 0) {
                        $items[$idx]['unit_price'] = (float) $prod->unit_price;
                        if (empty($item['product_id'])) {
                            $items[$idx]['product_id'] = $prod->id;
                        }
                        $updated = true;
                    }
                }
            }
            if ($updated) {
                $this->merge(['items' => $items]);
            }
        }
    }

    public function rules(): array
    {
        $isTech = $this->user()?->isTechnician();

        return [
            'quotation_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'pdf_format' => ['nullable', 'string', 'in:1,2,3,4'],
            'pricing_mode' => ['nullable', 'string', 'in:itemized,total_only'],

            // Email dispatch fields
            'action' => ['nullable', 'string', 'in:save,save_and_send'],
            'send_email_now' => ['nullable', 'boolean'],
            'customer_email' => ['nullable', 'email'],
            'email_subject' => ['nullable', 'string', 'max:255'],
            'email_custom_message' => ['nullable', 'string', 'max:2000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit' => ['required', 'string', 'max:30'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            if (!$user || !$user->isTechnician()) {
                return;
            }

            // Technicians cannot apply additional arbitrary discounts to undercut base product rates
            $discount = (float) $this->input('discount', 0);
            if ($discount > 0) {
                $validator->errors()->add('discount', 'Technicians are not authorized to apply additional quotation discounts. Discounts must be set or approved by Admin.');
            }

            $items = $this->input('items', []);
            if (!is_array($items)) {
                return;
            }

            $productIds = collect($items)->pluck('product_id')->filter()->unique()->all();
            $productsById = !empty($productIds)
                ? \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id')
                : collect();

            $itemNames = collect($items)
                ->filter(fn($it) => empty($it['product_id']) && !empty($it['item_name']))
                ->pluck('item_name')
                ->map(fn($n) => trim($n))
                ->unique()
                ->all();

            $productsByName = !empty($itemNames)
                ? \App\Models\Product::whereIn('name', $itemNames)->get()->keyBy(fn($p) => trim(strtolower($p->name)))
                : collect();

            foreach ($items as $index => $item) {
                $productId = $item['product_id'] ?? null;
                $product = $productId && isset($productsById[$productId])
                    ? $productsById[$productId]
                    : ($productsByName[trim(strtolower($item['item_name'] ?? ''))] ?? null);

                if (!$product) {
                    continue;
                }

                $adminPrice = (float) ($product->unit_price ?? 0);

                if ($adminPrice > 0) {
                    $enteredPrice = isset($item['unit_price']) && is_numeric($item['unit_price'])
                        ? (float) $item['unit_price']
                        : 0.0;

                    // Technician can only quote same or above range: enteredPrice >= adminPrice
                    if ($enteredPrice < ($adminPrice - 0.001)) {
                        $validator->errors()->add(
                            "items.{$index}.unit_price",
                            "Unit price for '{$product->name}' (₹" . number_format($enteredPrice, 2) . ") cannot be less than the Admin scheduled price of ₹" . number_format($adminPrice, 2) . ". You can only quote the same or a higher price."
                        );
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Add at least one quotation item.',
            'items.min' => 'Add at least one quotation item.',
            'items.*.item_name.required' => 'Every item needs a product or service name.',
            'items.*.quantity.gt' => 'Item quantity must be greater than zero.',
            'items.*.unit_price.required' => 'Unit price is required for each item.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',
            'items.*.product_id.exists' => 'The selected product is invalid.',
        ];
    }
}