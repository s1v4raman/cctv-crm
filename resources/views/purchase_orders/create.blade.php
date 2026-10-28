<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Create Purchase Order</h2>
    </x-slot>

    <div class="py-5" x-data="purchaseOrderBuilder(@js($products))">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('purchase-orders.index') }}" class="back-link">&larr; Back</a>

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Create Purchase Order</h2>
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

            <form method="POST" action="{{ route('purchase-orders.store') }}">
                @csrf

                {{-- Supplier & Procurement Schedule --}}
                <div class="crm-section mb-4">
                    <h3>Supplier & Order Details</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Select Supplier <span class="required-marker">*</span></label>
                            <select aria-label="Select Supplier" name="supplier_id" required class="crm-select">
                                <option value="">-- Choose Hardware Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $selectedSupplierId) == $supplier->id)>
                                        {{ $supplier->name }}{{ $supplier->company_name ? " ({$supplier->company_name})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Order Date <span class="required-marker">*</span></label>
                            <input aria-label="Order Date" type="date" name="order_date" value="{{ old('order_date', now()->format('Y-m-d')) }}" required class="crm-input">
                            @error('order_date') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Expected Delivery Date</label>
                            <input aria-label="Expected Delivery Date" type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date', now()->addDays(5)->format('Y-m-d')) }}" class="crm-input">
                            @error('expected_delivery_date') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="crm-label">Initial PO Status</label>
                            <select aria-label="Initial PO Status" name="status" class="crm-select">
                                <option value="ordered" @selected(old('status', 'ordered') === 'ordered')>Ordered</option>
                                <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                            </select>
                        </div>
                        <div>
                            <label class="crm-label">Procurement / Delivery Notes</label>
                            <input aria-label="Procurement / Delivery Notes" type="text" name="notes" value="{{ old('notes') }}" placeholder="e.g. Delivery to Central Warehouse" class="crm-input">
                        </div>
                    </div>
                </div>

                {{-- Line Items --}}
                <div class="crm-section mb-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#ddd] mb-4">
                        <h3 style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">Ordered Line Items</h3>
                        <button type="button" @click="addItem()" class="btn-secondary">Add Item</button>
                    </div>

                    <div class="overflow-x-auto mb-4">
                        <table class="crm-table">
                            <thead>
                                <tr>
                                    <th>Catalog Product</th>
                                    <th style="min-width: 180px;">Item Description</th>
                                    <th style="min-width: 110px;">SKU / Model</th>
                                    <th class="text-right" style="min-width: 120px;">Unit Cost (₹)</th>
                                    <th class="text-right" style="min-width: 100px;">Qty</th>
                                    <th class="text-right" style="min-width: 120px;">Total (₹)</th>
                                    <th class="text-center" style="width: 60px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td>
                                            <select aria-label="Item product" :name="`items[${index}][product_id]`" x-model="item.product_id" @change="onProductSelect(index)" class="crm-select">
                                                <option value="">-- Custom / Non-Catalog --</option>
                                                <template x-for="prod in catalogProducts" :key="prod.id">
                                                    <option :value="prod.id" x-text="`${prod.name} (${prod.sku})`"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td>
                                            <input aria-label="Item item name" type="text" :name="`items[${index}][item_name]`" x-model="item.item_name" required placeholder="e.g. 4MP Dome Camera" class="crm-input">
                                        </td>
                                        <td>
                                            <input aria-label="Item sku" type="text" :name="`items[${index}][sku]`" x-model="item.sku" placeholder="e.g. DS-2CD2143G2" class="crm-input">
                                        </td>
                                        <td class="text-right">
                                            <input type="number" step="0.01" min="0" :name="`items[${index}][unit_cost]`" x-model.number="item.unit_cost" required class="crm-input text-right">
                                        </td>
                                        <td class="text-right">
                                            <input type="number" min="1" :name="`items[${index}][quantity_ordered]`" x-model.number="item.quantity_ordered" required class="crm-input text-right">
                                        </td>
                                        <td class="text-right font-bold text-gray-900">
                                            ₹<span x-text="formatNumber(item.unit_cost * item.quantity_ordered)"></span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" @click="removeItem(index)" class="danger-link" style="background: none; border: none; cursor: pointer;">Delete</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Financials Summary Box --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-[#ddd] items-end">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="crm-label">GST Tax (%)</label>
                                    <input aria-label="GST Tax (%)" type="number" step="0.01" min="0" max="100" name="tax_percent" x-model.number="taxPercent" class="crm-input" placeholder="e.g. 18">
                                </div>
                                <div>
                                    <label class="crm-label">Shipping / Freight (₹)</label>
                                    <input aria-label="Shipping / Freight (₹)" type="number" step="0.01" min="0" name="shipping_cost" x-model.number="shippingCost" class="crm-input" placeholder="e.g. 250">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="crm-section" style="background: #f8fafc; margin-bottom: 0;">
                                <div class="flex justify-between py-1 text-sm text-gray-700">
                                    <span>Subtotal:</span>
                                    <span class="font-bold text-gray-900">₹<span x-text="formatNumber(subtotal())"></span></span>
                                </div>
                                <div class="flex justify-between py-1 text-sm text-gray-700">
                                    <span>GST Tax (<span x-text="taxPercent"></span>%):</span>
                                    <span class="font-bold text-gray-900">₹<span x-text="formatNumber(taxAmount())"></span></span>
                                </div>
                                <div class="flex justify-between py-1 text-sm text-gray-700">
                                    <span>Shipping Cost:</span>
                                    <span class="font-bold text-gray-900">₹<span x-text="formatNumber(shippingCost)"></span></span>
                                </div>
                                <div class="flex justify-between py-2 text-base font-bold text-gray-900 border-t border-[#ddd] mt-2">
                                    <span>Grand Total:</span>
                                    <span>₹<span x-text="formatNumber(grandTotal())"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('purchase-orders.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>

        </div>
    </div>

    <script>
        function purchaseOrderBuilder(catalogProducts) {
            return {
                catalogProducts: catalogProducts || [],
                taxPercent: 18.0,
                shippingCost: 0.0,
                items: [
                    { product_id: '', item_name: '', sku: '', unit_cost: 0, quantity_ordered: 1 }
                ],
                addItem() {
                    this.items.push({ product_id: '', item_name: '', sku: '', unit_cost: 0, quantity_ordered: 1 });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },
                onProductSelect(index) {
                    const pid = this.items[index].product_id;
                    if (!pid) return;
                    const prod = this.catalogProducts.find(p => p.id == pid);
                    if (prod) {
                        this.items[index].item_name = prod.name;
                        this.items[index].sku = prod.sku || prod.model_no || '';
                        this.items[index].unit_cost = parseFloat(prod.cost_price) || 0;
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
