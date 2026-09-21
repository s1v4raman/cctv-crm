<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Create Vendor Purchase Order (PO)</h2>
                <p class="mt-1 text-sm text-gray-500">Order cameras, NVRs, HDDs, cables & accessories from hardware distributors</p>
            </div>
            <a href="{{ route('purchase-orders.index') }}" class="btn-head-secondary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Purchase Orders
            </a>
        </div>
    </x-slot>

    <style>
        .pg-wrap  { background:#f1f5f9; min-height:100vh; padding:1.75rem 0 3rem; }
        .pg-inner { max-width:1080px; margin:0 auto; padding:0 1.25rem; }

        .btn-head-secondary {
            display: inline-flex; align-items: center;
            background-color: #ffffff; color: #334155 !important;
            padding: 0.5rem 1rem; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600; text-decoration: none;
            border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.15s ease-in-out;
        }
        .btn-head-secondary:hover { background-color: #f8fafc; color: #0f172a !important; border-color: #94a3b8; }

        .pg-card {
            background:#fff;
            border-radius:1rem;
            border:1px solid rgba(99,102,241,.08);
            box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(99,102,241,.06);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block; font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.05em;
            color: #475569; margin-bottom: 0.4rem;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%; border: 1px solid #cbd5e1; border-radius: 0.6rem;
            padding: 0.65rem 0.85rem; font-size: 0.875rem; color: #1e293b;
            background: #fff; outline: none; transition: all 0.15s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .btn-add-item {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.5rem 1rem; border-radius: 0.5rem;
            font-size: 0.82rem; font-weight: 700;
            background-color: #eef2ff; color: #4f46e5 !important;
            border: 1px solid #c7d2fe; cursor: pointer;
            transition: all 0.15s;
        }
        .btn-add-item:hover { background-color: #4f46e5; color: #ffffff !important; }

        .btn-remove-row {
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 0.45rem;
            background-color: #fef2f2; color: #dc2626 !important;
            border: 1px solid #fecaca; cursor: pointer; transition: all .15s;
        }
        .btn-remove-row:hover { background-color: #dc2626; color: #ffffff !important; }

        .btn-submit-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.75rem 1.75rem; border-radius: 0.5rem;
            font-size: 0.9rem; font-weight: 700;
            background-color: #4f46e5; color: #ffffff !important;
            border: 1px solid #4338ca; cursor: pointer;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.25);
            transition: all 0.15s ease-in-out;
        }
        .btn-submit-primary:hover { background-color: #4338ca; transform: translateY(-1px); }

        .btn-cancel {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.75rem 1.25rem; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600;
            background-color: #ffffff; color: #475569 !important;
            border: 1px solid #cbd5e1; text-decoration: none;
            transition: all 0.15s ease-in-out;
        }
        .btn-cancel:hover { background-color: #f8fafc; color: #0f172a !important; }

        .summary-box {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem;
            padding: 1.25rem; width: 100%; max-width: 380px; margin-left: auto;
        }
        .summary-row {
            display: flex; justify-content: space-between; font-size: 0.875rem; color: #475569; padding: 0.35rem 0;
        }
        .summary-row.total {
            font-size: 1.15rem; font-weight: 800; color: #0f172a; border-top: 1px solid #cbd5e1; margin-top: 0.5rem; padding-top: 0.75rem;
        }

        .error-msg { color: #dc2626; font-size: 0.78rem; margin-top: 0.35rem; font-weight: 600; }
    </style>

    <div class="pg-wrap" x-data="purchaseOrderBuilder(@js($products))">
        <div class="pg-inner">

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('purchase-orders.store') }}">
                @csrf

                {{-- Header & Supplier Selection --}}
                <div class="pg-card">
                    <h3 class="text-base font-bold text-gray-900 mb-4 pb-3 border-b border-gray-100 flex items-center gap-2">
                        <span>🏢</span> 1. Supplier & Procurement Schedule
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="form-label">Select Supplier <span class="text-red-500">*</span></label>
                            <select name="supplier_id" required class="form-select font-bold">
                                <option value="">-- Choose Hardware Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $selectedSupplierId) == $supplier->id)>
                                        {{ $supplier->name }}{{ $supplier->company_name ? " ({$supplier->company_name})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Order Date <span class="text-red-500">*</span></label>
                            <input type="date" name="order_date" value="{{ old('order_date', now()->format('Y-m-d')) }}" required class="form-input font-medium">
                            @error('order_date') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Expected Delivery Date</label>
                            <input type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date', now()->addDays(5)->format('Y-m-d')) }}" class="form-input font-medium">
                            @error('expected_delivery_date') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Initial PO Status</label>
                            <select name="status" class="form-select">
                                <option value="ordered" @selected(old('status', 'ordered') === 'ordered')>Ordered (Issued to Vendor)</option>
                                <option value="draft" @selected(old('status') === 'draft')>Draft (Under Review)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Procurement / Delivery Notes</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" placeholder="e.g. Delivery to Central Warehouse, Attention: Warehouse Manager" class="form-input">
                        </div>
                    </div>
                </div>

                {{-- Line Items --}}
                <div class="pg-card">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <span>📦</span> 2. Ordered Line Items
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Select products from catalog to automatically pull cost price, or enter custom line items</p>
                        </div>
                        <button type="button" @click="addItem()" class="btn-add-item">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            + Add Item
                        </button>
                    </div>

                    <div class="overflow-x-auto mb-4">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold text-gray-600 uppercase tracking-wider">
                                    <th class="py-2.5 px-3">Catalog Product</th>
                                    <th class="py-2.5 px-3 min-w-[180px]">Item Description <span class="text-red-500">*</span></th>
                                    <th class="py-2.5 px-3 min-w-[110px]">SKU / Model</th>
                                    <th class="py-2.5 px-3 text-right min-w-[120px]">Unit Cost (₹) <span class="text-red-500">*</span></th>
                                    <th class="py-2.5 px-3 text-right min-w-[100px]">Qty <span class="text-red-500">*</span></th>
                                    <th class="py-2.5 px-3 text-right min-w-[120px]">Total (₹)</th>
                                    <th class="py-2.5 px-2 text-center w-12"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="border-b border-gray-100">
                                        <td class="py-2 px-2">
                                            <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="onProductSelect(index)" class="form-select text-xs">
                                                <option value="">-- Custom / Non-Catalog --</option>
                                                <template x-for="prod in catalogProducts" :key="prod.id">
                                                    <option :value="prod.id" x-text="`${prod.name} (${prod.sku})`"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="py-2 px-2">
                                            <input type="text" :name="`items[${index}][item_name]`" x-model="item.item_name" required placeholder="Item description" class="form-input text-xs font-semibold">
                                        </td>
                                        <td class="py-2 px-2">
                                            <input type="text" :name="`items[${index}][sku]`" x-model="item.sku" placeholder="SKU/Model" class="form-input text-xs font-mono">
                                        </td>
                                        <td class="py-2 px-2 text-right">
                                            <input type="number" step="0.01" min="0" :name="`items[${index}][unit_cost]`" x-model.number="item.unit_cost" required class="form-input text-xs text-right font-bold">
                                        </td>
                                        <td class="py-2 px-2 text-right">
                                            <input type="number" min="1" :name="`items[${index}][quantity_ordered]`" x-model.number="item.quantity_ordered" required class="form-input text-xs text-right font-bold">
                                        </td>
                                        <td class="py-2 px-2 text-right font-extrabold text-xs text-gray-900">
                                            ₹<span x-text="formatNumber(item.unit_cost * item.quantity_ordered)"></span>
                                        </td>
                                        <td class="py-2 px-2 text-center">
                                            <button type="button" @click="removeItem(index)" class="btn-remove-row" title="Remove row">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Financials Summary Box --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-gray-100 items-end">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">GST Tax (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="tax_percent" x-model.number="taxPercent" class="form-input font-bold">
                                </div>
                                <div>
                                    <label class="form-label">Shipping / Freight (₹)</label>
                                    <input type="number" step="0.01" min="0" name="shipping_cost" x-model.number="shippingCost" class="form-input font-bold">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="summary-box">
                                <div class="summary-row">
                                    <span>Subtotal:</span>
                                    <span class="font-bold text-gray-900">₹<span x-text="formatNumber(subtotal())"></span></span>
                                </div>
                                <div class="summary-row">
                                    <span>GST Tax (<span x-text="taxPercent"></span>%):</span>
                                    <span class="font-bold text-gray-900">₹<span x-text="formatNumber(taxAmount())"></span></span>
                                </div>
                                <div class="summary-row">
                                    <span>Shipping Cost:</span>
                                    <span class="font-bold text-gray-900">₹<span x-text="formatNumber(shippingCost)"></span></span>
                                </div>
                                <div class="summary-row total">
                                    <span>Grand Total:</span>
                                    <span class="text-indigo-600">₹<span x-text="formatNumber(grandTotal())"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('purchase-orders.index') }}" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Issue Purchase Order
                    </button>
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
