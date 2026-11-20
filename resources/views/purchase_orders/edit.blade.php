<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Purchase Order #{{ $purchaseOrder->po_number }}</h2>
    </x-slot>

    @php
        $existingItems = $purchaseOrder->items->map(function($i) {
            return [
                'product_id' => $i->product_id,
                'item_name' => $i->item_name,
                'sku' => $i->sku,
                'hsn_code' => $i->hsn_code,
                'unit_cost' => (float) $i->unit_cost,
                'quantity_ordered' => (int) $i->quantity_ordered,
                'tax_rate' => (float) ($i->tax_rate ?: 18.00),
            ];
        })->toArray();
    @endphp

    <div class="py-5" x-data="purchaseOrderBuilder(@js($products), @js(old('items', $existingItems)), @js($suppliers), @js(old('supplier_id', $purchaseOrder->supplier_id)))">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="back-link">&larr; Back to Order</a>

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Edit Purchase Order #{{ $purchaseOrder->po_number }}</h2>
                <span class="text-xs uppercase font-bold px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200">
                    Status: {{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}
                </span>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-[4px] bg-red-50 border border-red-200 text-[#dc2626] text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('purchase-orders.update', $purchaseOrder) }}">
                @csrf
                @method('PUT')

                {{-- Supplier & Procurement Schedule --}}
                <div class="crm-section mb-4">
                    <h3>Supplier & Order Details</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="po-supplier" class="crm-label">Select Supplier <span class="required-marker">*</span></label>
                            <select id="po-supplier" name="supplier_id" required class="crm-select" x-model="selectedSupplierId">
                                <option value="">-- Choose Hardware Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id)>
                                        {{ $supplier->name }}{{ $supplier->company_name ? " ({$supplier->company_name})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Order Date <span class="required-marker">*</span></label>
                            <input aria-label="Order Date" type="date" name="order_date" value="{{ old('order_date', $purchaseOrder->order_date?->format('Y-m-d')) }}" required class="crm-input">
                            @error('order_date') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Expected Delivery Date</label>
                            <input aria-label="Expected Delivery Date" type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date', $purchaseOrder->expected_delivery_date?->format('Y-m-d')) }}" class="crm-input">
                            @error('expected_delivery_date') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="crm-label">Procurement / Delivery Notes</label>
                            <input aria-label="Procurement / Delivery Notes" type="text" name="notes" value="{{ old('notes', $purchaseOrder->notes) }}" placeholder="e.g. Delivery to Central Warehouse" class="crm-input">
                        </div>
                    </div>
                </div>

                {{-- Line Items --}}
                <div class="crm-section mb-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#ddd] dark:border-slate-800 mb-4 flex-wrap gap-2">
                        <div>
                            <h3 style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">Ordered Line Items</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Modify line items, quantities, and rates before approval.</p>
                        </div>
                        <button type="button" @click="addItem()" class="btn-primary text-xs">Add Item</button>
                    </div>

                    <div class="overflow-x-auto mb-4">
                        <table class="crm-table">
                            <thead>
                                <tr>
                                    <th style="min-width: 280px;">Item / Description</th>
                                    <th style="min-width: 140px;">SKU</th>
                                    <th class="text-right" style="min-width: 120px;">Unit Cost (₹)</th>
                                    <th class="text-center" style="min-width: 100px;">Qty</th>
                                    <th class="text-right" style="min-width: 130px;">Total Cost (₹)</th>
                                    <th class="text-center" style="width: 50px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td>
                                            <input type="text" :name="`items[${index}][item_name]`" x-model="item.item_name" required class="crm-input text-xs w-full" placeholder="Item name">
                                            <input type="hidden" :name="`items[${index}][product_id]`" x-model="item.product_id">
                                            <input type="hidden" :name="`items[${index}][hsn_code]`" x-model="item.hsn_code">
                                        </td>
                                        <td>
                                            <input type="text" :name="`items[${index}][sku]`" x-model="item.sku" class="crm-input text-xs font-mono w-full" placeholder="SKU">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" :name="`items[${index}][unit_cost]`" x-model="item.unit_cost" required class="crm-input text-xs text-right w-full">
                                        </td>
                                        <td>
                                            <input type="number" min="1" :name="`items[${index}][quantity_ordered]`" x-model="item.quantity_ordered" required class="crm-input text-xs text-center w-full">
                                        </td>
                                        <td class="text-right font-mono font-bold text-xs text-slate-900 dark:text-white" x-text="'₹ ' + formatNumber((parseFloat(item.unit_cost) || 0) * (parseInt(item.quantity_ordered) || 0))"></td>
                                        <td class="text-center">
                                            <button type="button" @click="removeItem(index)" class="danger-link text-xs p-1 cursor-pointer" title="Remove">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals calculation --}}
                    <div class="flex justify-end">
                        <div class="w-full sm:w-80 space-y-2 p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200 dark:border-slate-800 text-xs">
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>Subtotal:</span>
                                <span class="font-mono font-bold text-slate-900 dark:text-white" x-text="'₹ ' + formatNumber(subtotal())"></span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                <span>Tax Rate (%):</span>
                                <input type="number" step="0.01" min="0" max="100" name="tax_percent" x-model="taxPercent" class="crm-input text-right w-20 py-1 text-xs">
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>Tax Amount:</span>
                                <span class="font-mono text-slate-900 dark:text-white" x-text="'₹ ' + formatNumber(taxAmount())"></span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                <span>Shipping (₹):</span>
                                <input type="number" step="0.01" min="0" name="shipping_cost" x-model="shippingCost" class="crm-input text-right w-24 py-1 text-xs">
                            </div>
                            <div class="flex justify-between font-bold text-sm pt-2 border-t border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                                <span>Grand Total:</span>
                                <span class="font-mono text-blue-600 dark:text-blue-400" x-text="'₹ ' + formatNumber(grandTotal())"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function purchaseOrderBuilder(products, initialItems, suppliers, initialSupplierId) {
            return {
                products: products || [],
                suppliersList: suppliers || [],
                selectedSupplierId: initialSupplierId ? String(initialSupplierId) : '',
                taxPercent: {{ (float) ($purchaseOrder->tax_percent ?: 18.0) }},
                shippingCost: {{ (float) ($purchaseOrder->shipping_cost ?: 0.0) }},
                items: initialItems && initialItems.length ? initialItems : [
                    { product_id: '', item_name: '', sku: '', hsn_code: '', unit_cost: 0, quantity_ordered: 1, tax_rate: 18.0 }
                ],

                addItem() {
                    this.items.push({ product_id: '', item_name: '', sku: '', hsn_code: '', unit_cost: 0, quantity_ordered: 1, tax_rate: 18.0 });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                subtotal() {
                    return this.items.reduce((sum, it) => sum + ((parseFloat(it.unit_cost) || 0) * (parseInt(it.quantity_ordered) || 0)), 0);
                },

                taxAmount() {
                    return this.subtotal() * ((parseFloat(this.taxPercent) || 0) / 100.0);
                },

                grandTotal() {
                    return this.subtotal() + this.taxAmount() + (parseFloat(this.shippingCost) || 0);
                },

                formatNumber(val) {
                    const num = parseFloat(val) || 0;
                    return num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            };
        }
    </script>
</x-app-layout>
