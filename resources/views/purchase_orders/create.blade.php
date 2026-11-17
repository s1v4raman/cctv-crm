<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Create Purchase Order</h2>
    </x-slot>

    <div class="py-5" x-data="purchaseOrderBuilder(@js($products), @js(old('items')))" @supplier-added="supplierAdded($event.detail)">
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
                            <div class="flex flex-wrap items-center justify-between gap-x-2">
                                <label for="po-supplier" class="crm-label whitespace-nowrap">Select Supplier <span class="required-marker">*</span></label>
                                <button type="button" @click="$dispatch('open-modal', 'add-supplier')" class="mb-[0.45rem] inline-flex items-center gap-1 whitespace-nowrap text-xs font-semibold hover:underline" style="color: var(--link-color);">
                                    Add new supplier
                                </button>
                            </div>
                            <select id="po-supplier" x-ref="supplierSelect" name="supplier_id" required class="crm-select" @change="supplierNote = ''">
                                <option value="">-- Choose Hardware Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $selectedSupplierId) == $supplier->id)>
                                        {{ $supplier->name }}{{ $supplier->company_name ? " ({$supplier->company_name})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <p x-show="supplierNote" x-text="supplierNote" x-cloak role="status" class="mt-1 text-xs font-semibold text-emerald-600"></p>
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
                    <div class="flex items-center justify-between pb-3 border-b border-[#ddd] dark:border-slate-800 mb-4 flex-wrap gap-2">
                        <div>
                            <h3 style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">Ordered Line Items</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Select existing products from catalog or enter custom non-catalog items.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="openNewProductModal()" class="btn-secondary text-xs inline-flex items-center font-bold text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition cursor-pointer">
                                <span>{{ __('Add New Product') }}</span>
                            </button>
                            <button type="button" @click="addItem()" class="btn-primary text-xs">Add Item</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto mb-4">
                        <table class="crm-table">
                            <thead>
                                <tr>
                                    <th style="min-width: 280px;">Item / Service</th>
                                    <th style="min-width: 200px;">Description</th>
                                    <th class="text-right" style="min-width: 120px;">Unit Cost (₹)</th>
                                    <th class="text-right" style="min-width: 90px;">Qty</th>
                                    <th class="text-right" style="min-width: 120px;">Total (₹)</th>
                                    <th class="text-center" style="width: 70px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                                        <td class="relative">
                                            <input type="hidden" :name="`items[${index}][product_id]`" x-model="item.product_id">
                                            <input aria-label="Item / Service"
                                                   type="text"
                                                   :name="`items[${index}][item_name]`"
                                                   x-model="item.item_name"
                                                   @input="onItemInput(index, $event)"
                                                   @focus="onItemFocus(index, $event)"
                                                   @keydown.down.prevent="navigateDropdown(1)"
                                                   @keydown.up.prevent="navigateDropdown(-1)"
                                                   @keydown.enter.prevent="selectHighlighted(index)"
                                                   @keydown.escape="closeDropdown()"
                                                   required
                                                   autocomplete="off"
                                                   placeholder="Search camera, NVR, cable..."
                                                   class="item-name-input block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-slate-400">
                                        </td>
                                        <td>
                                            <input aria-label="Description"
                                                   type="text"
                                                   :name="`items[${index}][sku]`"
                                                   x-model="item.sku"
                                                   placeholder="Optional specification"
                                                   class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-slate-400">
                                        </td>
                                        <td class="text-right">
                                            <input aria-label="Unit Cost (₹)"
                                                   type="number"
                                                   step="0.01"
                                                   min="0"
                                                   :name="`items[${index}][unit_cost]`"
                                                   x-model.number="item.unit_cost"
                                                   required
                                                   placeholder="0.00"
                                                   class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono text-right">
                                        </td>
                                        <td class="text-right">
                                            <input aria-label="Qty"
                                                   type="number"
                                                   min="1"
                                                   :name="`items[${index}][quantity_ordered]`"
                                                   x-model.number="item.quantity_ordered"
                                                   required
                                                   class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono text-right">
                                        </td>
                                        <td class="text-right font-bold text-slate-900 dark:text-slate-100 font-mono">
                                            ₹<span x-text="formatNumber((parseFloat(item.unit_cost) || 0) * (parseInt(item.quantity_ordered) || 0))"></span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                    @click="removeItem(index)"
                                                    class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 cursor-pointer"
                                                    style="background: none; border: none;">
                                                Delete
                                            </button>
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

            {{-- Add a supplier without leaving the order; kept outside the order form so the two never mix. --}}
            <x-modal name="add-supplier" title="Add new supplier" maxWidth="xl">
                <form x-data="newSupplierForm(@js(route('suppliers.store')))" @submit.prevent="save($event.target)" class="p-6 space-y-4" novalidate>
                    @csrf
                    <p x-show="errorMessage" x-text="errorMessage" x-cloak role="alert" class="p-3 rounded-[4px] bg-red-50 border border-red-200 text-[#dc2626] text-sm"></p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="ns-name" class="crm-label">Supplier Name <span class="required-marker">*</span></label>
                            <input id="ns-name" type="text" name="name" required maxlength="255" placeholder="e.g. Hikvision India Central" class="crm-input">
                            <p x-show="errors.name" x-text="errors.name?.[0]" class="error-text"></p>
                        </div>
                        <div>
                            <label for="ns-company" class="crm-label">Company Name</label>
                            <input id="ns-company" type="text" name="company_name" maxlength="255" placeholder="e.g. Prama Hikvision Pvt Ltd" class="crm-input">
                            <p x-show="errors.company_name" x-text="errors.company_name?.[0]" class="error-text"></p>
                        </div>
                        <div>
                            <label for="ns-contact" class="crm-label">Contact Person</label>
                            <input id="ns-contact" type="text" name="contact_person" maxlength="255" placeholder="e.g. Ramesh Sharma" class="crm-input">
                        </div>
                        <div>
                            <label for="ns-phone" class="crm-label">Phone</label>
                            <input id="ns-phone" type="tel" name="phone" maxlength="50" placeholder="e.g. 9876543210" class="crm-input">
                            <p x-show="errors.phone" x-text="errors.phone?.[0]" class="error-text"></p>
                        </div>
                        <div>
                            <label for="ns-email" class="crm-label">Email</label>
                            <input id="ns-email" type="email" name="email" maxlength="255" placeholder="e.g. orders@supplier.com" class="crm-input">
                            <p x-show="errors.email" x-text="errors.email?.[0]" class="error-text"></p>
                        </div>
                        <div>
                            <label for="ns-gst" class="crm-label">GSTIN</label>
                            <input id="ns-gst" type="text" name="gst_number" maxlength="50" placeholder="e.g. 33AAAAA0000A1Z5" class="crm-input font-mono">
                            <p x-show="errors.gst_number" x-text="errors.gst_number?.[0]" class="error-text"></p>
                        </div>
                        <div>
                            <label for="ns-terms" class="crm-label">Payment Terms</label>
                            <input id="ns-terms" type="text" name="payment_terms" value="Net 30 Days" maxlength="100" class="crm-input">
                        </div>
                        <div>
                            <label for="ns-city" class="crm-label">City</label>
                            <input id="ns-city" type="text" name="city" maxlength="100" placeholder="e.g. Chennai" class="crm-input">
                        </div>
                        <div>
                            <label for="ns-state" class="crm-label">State</label>
                            <input id="ns-state" type="text" name="state" maxlength="100" placeholder="e.g. Tamil Nadu" class="crm-input">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="ns-address" class="crm-label">Address</label>
                            <textarea id="ns-address" name="address" rows="3" class="crm-input" style="height: auto;" placeholder="e.g. 42 Industrial Estate, Guindy, Chennai"></textarea>
                            <p x-show="errors.address" x-text="errors.address?.[0]" class="error-text"></p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="$dispatch('close-modal', 'add-supplier')" class="btn-secondary">Cancel</button>
                        <button type="submit" :disabled="saving" class="btn-primary" x-text="saving ? 'Saving…' : 'Save supplier'">Save supplier</button>
                    </div>
                </form>
            </x-modal>

            {{-- Floating Dropdown for Product Catalog Autocomplete (Strict downward opening matching Image 1) --}}
            <div id="po-product-dropdown"
                 x-show="isDropdownOpen"
                 x-cloak
                 class="fixed z-[99999] max-h-72 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-2xl divide-y divide-slate-100 dark:divide-slate-800/80"
                 style="display: none;"
                 @mousedown.stop>
                <template x-if="searchResults.length === 0">
                    <div class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400 text-center font-medium">
                        No catalogue product found for "<span class="text-slate-800 dark:text-slate-200 font-bold" x-text="currentSearchText"></span>". Enter custom specification manually.
                    </div>
                </template>

                <template x-for="(prod, idx) in searchResults" :key="prod.id">
                    <button type="button"
                            class="product-item-btn w-full px-4 py-3 text-left transition-colors flex items-start justify-between gap-3 cursor-pointer bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/80 border-l-4 group"
                            :class="highlightedIndex === idx ? 'bg-blue-50/90 dark:bg-slate-800 border-l-blue-600 dark:border-l-blue-400' : 'border-l-transparent'"
                            @mousedown.prevent="selectProduct(activeItemIndex, prod)"
                            @mouseenter="highlightedIndex = idx">
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors"
                                 x-text="prod.name"></div>
                            <template x-if="prod.description">
                                <div class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed"
                                     x-text="prod.description"></div>
                            </template>
                            <template x-if="!prod.description && (prod.brand || prod.model_no || prod.sku)">
                                <div class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed"
                                     x-text="[prod.brand, prod.model_no || prod.sku].filter(Boolean).join(' • ')"></div>
                            </template>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/50 shadow-2xs"
                                  x-text="'₹' + formatNumber(prod.cost_price || prod.unit_price || 0)"></span>
                        </div>
                    </button>
                </template>
            </div>

        </div>
    </div>

    <script>
        function newSupplierForm(url) {
            return {
                saving: false,
                errors: {},
                errorMessage: '',
                async save(form) {
                    this.errors = {};
                    this.errorMessage = '';
                    if (!form.elements.name.value.trim()) {
                        this.errors = { name: ['Enter the supplier name.'] };
                        form.elements.name.focus();
                        return;
                    }
                    this.saving = true;
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            body: new FormData(form),
                        });
                        const data = await response.json().catch(() => ({}));
                        if (response.status === 201) {
                            this.$dispatch('supplier-added', data);
                            this.$dispatch('close-modal', 'add-supplier');
                            form.reset();
                        } else if (response.status === 422) {
                            this.errors = data.errors || {};
                            this.errorMessage = 'Please check the highlighted fields.';
                        } else if (response.status === 419) {
                            this.errorMessage = 'Your session expired. Reload the page and try again.';
                        } else {
                            this.errorMessage = data.message || 'The supplier could not be saved. Please try again.';
                        }
                    } catch (e) {
                        this.errorMessage = 'Could not reach the server. Check the connection and try again.';
                    } finally {
                        this.saving = false;
                    }
                },
            };
        }

        function purchaseOrderBuilder(catalogProducts, initialItems = null) {
            return {
                catalogProducts: catalogProducts || [],
                supplierNote: '',
                taxPercent: 18.0,
                shippingCost: 0.0,
                activeProductIndex: null,

                // Autocomplete Dropdown State (matching Image 1)
                isDropdownOpen: false,
                activeItemIndex: null,
                activeInputElement: null,
                highlightedIndex: -1,
                searchResults: [],
                currentSearchText: '',
                searchAbortController: null,

                items: initialItems && initialItems.length > 0
                    ? initialItems.map(it => ({
                        product_id: it.product_id || '',
                        item_name: it.item_name || '',
                        sku: it.sku || '',
                        unit_cost: parseFloat(it.unit_cost) || 0,
                        quantity_ordered: parseInt(it.quantity_ordered) || 1
                    }))
                    : [
                        { product_id: '', item_name: '', sku: '', unit_cost: 0, quantity_ordered: 1 }
                    ],

                init() {
                    window.addEventListener('product-created-in-catalog', (e) => {
                        const newProd = e.detail;
                        if (!newProd || !newProd.id) return;

                        // Insert into catalogProducts list if not existing
                        if (!this.catalogProducts.some(p => p.id == newProd.id)) {
                            this.catalogProducts.unshift(newProd);
                        }

                        if (this.activeProductIndex !== null && this.items[this.activeProductIndex]) {
                            const idx = this.activeProductIndex;
                            this.items[idx].product_id = newProd.id;
                            this.items[idx].item_name = newProd.name;
                            this.items[idx].sku = newProd.description || [newProd.brand, newProd.model_no || newProd.sku].filter(Boolean).join(' • ') || newProd.sku || '';
                            this.items[idx].unit_cost = parseFloat(newProd.cost_price || newProd.unit_price) || 0;
                        } else {
                            let targetIdx = this.items.findIndex(it => !it.product_id && !it.item_name);
                            if (targetIdx === -1) {
                                this.items.push({
                                    product_id: newProd.id,
                                    item_name: newProd.name,
                                    sku: newProd.description || [newProd.brand, newProd.model_no || newProd.sku].filter(Boolean).join(' • ') || newProd.sku || '',
                                    unit_cost: parseFloat(newProd.cost_price || newProd.unit_price) || 0,
                                    quantity_ordered: 1
                                });
                            } else {
                                this.items[targetIdx].product_id = newProd.id;
                                this.items[targetIdx].item_name = newProd.name;
                                this.items[targetIdx].sku = newProd.description || [newProd.brand, newProd.model_no || newProd.sku].filter(Boolean).join(' • ') || newProd.sku || '';
                                this.items[targetIdx].unit_cost = parseFloat(newProd.cost_price || newProd.unit_price) || 0;
                            }
                        }
                        this.activeProductIndex = null;
                    });

                    // Reposition dropdown on window scroll/resize
                    const handleReposition = () => {
                        if (this.isDropdownOpen && this.activeInputElement) {
                            this.positionDropdown(this.activeInputElement);
                        }
                    };
                    window.addEventListener('scroll', handleReposition, true);
                    window.addEventListener('resize', handleReposition);

                    // Click outside to dismiss
                    document.addEventListener('click', (e) => {
                        if (!e.target.closest('#po-product-dropdown') && !e.target.closest('.item-name-input')) {
                            this.closeDropdown();
                        }
                    });
                },

                onItemFocus(index, event) {
                    this.activeItemIndex = index;
                    this.activeInputElement = event.target;
                    this.currentSearchText = (this.items[index]?.item_name || '').trim();
                    this.filterCatalog(this.currentSearchText);
                    this.positionDropdown(event.target);
                    this.isDropdownOpen = true;
                    this.highlightedIndex = -1;
                },

                onItemInput(index, event) {
                    this.activeItemIndex = index;
                    this.activeInputElement = event.target;
                    this.currentSearchText = (event.target.value || '').trim();

                    // If user alters name manually, treat as custom until selected
                    this.items[index].product_id = '';

                    this.filterCatalog(this.currentSearchText);
                    this.positionDropdown(event.target);
                    this.isDropdownOpen = true;
                    this.highlightedIndex = -1;
                },

                async filterCatalog(query) {
                    const q = (query || '').toLowerCase().trim();
                    if (!q) {
                        this.searchResults = this.catalogProducts.slice(0, 10);
                        return;
                    }

                    // Fast local filter
                    const localMatches = this.catalogProducts.filter(p => {
                        const name = (p.name || '').toLowerCase();
                        const sku = (p.sku || '').toLowerCase();
                        const model = (p.model_no || '').toLowerCase();
                        const brand = (p.brand || '').toLowerCase();
                        const desc = (p.description || '').toLowerCase();
                        const cat = (p.category || '').toLowerCase();
                        return name.includes(q) || sku.includes(q) || model.includes(q) || brand.includes(q) || desc.includes(q) || cat.includes(q);
                    });
                    this.searchResults = localMatches.slice(0, 12);

                    // Background remote search fallback/sync
                    if (this.searchAbortController) {
                        this.searchAbortController.abort();
                    }
                    this.searchAbortController = new AbortController();

                    try {
                        const res = await fetch(`{{ route('products.search') }}?q=${encodeURIComponent(q)}`, {
                            headers: { 'Accept': 'application/json' },
                            signal: this.searchAbortController.signal
                        });
                        if (res.ok) {
                            const remoteProducts = await res.json();
                            if (remoteProducts && remoteProducts.length > 0) {
                                const ids = new Set(this.searchResults.map(p => p.id));
                                remoteProducts.forEach(rp => {
                                    if (!ids.has(rp.id)) {
                                        this.searchResults.push(rp);
                                        ids.add(rp.id);
                                        if (!this.catalogProducts.some(cp => cp.id == rp.id)) {
                                            this.catalogProducts.push(rp);
                                        }
                                    }
                                });
                            }
                        }
                    } catch (e) {
                        // AbortError is expected
                    }
                },

                positionDropdown(input) {
                    if (!input) return;
                    const dropdown = document.getElementById('po-product-dropdown');
                    if (!dropdown) return;

                    const rect = input.getBoundingClientRect();
                    const availableWidth = Math.max(0, window.innerWidth - 32);
                    const targetWidth = Math.min(Math.max(rect.width, 440), availableWidth);

                    // Strict downward placement per design specification
                    dropdown.style.top = `${rect.bottom + 4}px`;
                    dropdown.style.bottom = 'auto';

                    let left = rect.left;
                    if (left + targetWidth > window.innerWidth - 16) {
                        left = window.innerWidth - targetWidth - 16;
                    }
                    dropdown.style.left = `${Math.max(16, left)}px`;
                    dropdown.style.width = `${targetWidth}px`;
                },

                selectProduct(index, prod) {
                    if (index === null || !this.items[index] || !prod) return;
                    this.items[index].product_id = prod.id;
                    this.items[index].item_name = prod.name;
                    this.items[index].sku = prod.description || [prod.brand, prod.model_no || prod.sku].filter(Boolean).join(' • ') || prod.sku || '';
                    this.items[index].unit_cost = parseFloat(prod.cost_price || prod.unit_price) || 0;
                    if (!this.items[index].quantity_ordered || this.items[index].quantity_ordered < 1) {
                        this.items[index].quantity_ordered = 1;
                    }

                    this.closeDropdown();

                    this.$nextTick(() => {
                        const rows = document.querySelectorAll('tbody tr');
                        if (rows[index]) {
                            const qtyInput = rows[index].querySelector('input[name*="quantity_ordered"]');
                            if (qtyInput) {
                                qtyInput.focus();
                                qtyInput.select();
                            }
                        }
                    });
                },

                navigateDropdown(step) {
                    if (!this.isDropdownOpen || this.searchResults.length === 0) return;
                    this.highlightedIndex += step;
                    if (this.highlightedIndex < 0) {
                        this.highlightedIndex = this.searchResults.length - 1;
                    } else if (this.highlightedIndex >= this.searchResults.length) {
                        this.highlightedIndex = 0;
                    }
                    this.$nextTick(() => {
                        const dropdown = document.getElementById('po-product-dropdown');
                        const buttons = dropdown?.querySelectorAll('.product-item-btn');
                        if (buttons && buttons[this.highlightedIndex]) {
                            buttons[this.highlightedIndex].scrollIntoView({ block: 'nearest' });
                        }
                    });
                },

                selectHighlighted(index) {
                    if (this.isDropdownOpen && this.highlightedIndex >= 0 && this.searchResults[this.highlightedIndex]) {
                        this.selectProduct(index, this.searchResults[this.highlightedIndex]);
                    }
                },

                closeDropdown() {
                    this.isDropdownOpen = false;
                    this.highlightedIndex = -1;
                    this.activeInputElement = null;
                },

                openNewProductModal(index = null) {
                    this.activeProductIndex = index;
                    const prefill = (index !== null && this.items[index]?.item_name) ? this.items[index].item_name : '';
                    if (typeof window.openQuickAddProductModal === 'function') {
                        window.openQuickAddProductModal({ prefillName: prefill });
                    } else {
                        window.dispatchEvent(new CustomEvent('open-quick-product-modal', { detail: { prefillName: prefill } }));
                    }
                },

                supplierAdded(supplier) {
                    const select = this.$refs.supplierSelect;
                    select.add(new Option(supplier.label, supplier.id, true, true));
                    select.value = String(supplier.id);
                    this.supplierNote = supplier.message;
                },

                addItem() {
                    this.items.push({ product_id: '', item_name: '', sku: '', unit_cost: 0, quantity_ordered: 1 });
                    this.$nextTick(() => {
                        const inputs = document.querySelectorAll('.item-name-input');
                        if (inputs.length) {
                            inputs[inputs.length - 1].focus();
                        }
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                        if (this.activeItemIndex === index) {
                            this.closeDropdown();
                        }
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
