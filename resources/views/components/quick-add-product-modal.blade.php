<div x-data="quickAddProductModalHandler()"
     x-init="initModal()"
     x-show="isOpen"
     x-cloak
     class="fixed inset-0 z-[99999] flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
     role="dialog"
     aria-modal="true"
     aria-labelledby="quick-product-modal-title">

    <div @click.away="closeModal()" 
         class="relative w-full max-w-lg bg-white dark:bg-[#0f172a] rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 my-8 space-y-4 text-slate-800 dark:text-slate-100">
        
        {{-- Header --}}
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                </span>
                <div>
                    <h3 id="quick-product-modal-title" class="font-black text-base text-slate-900 dark:text-white">
                        {{ __('Add New Product to Catalog') }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ __('Saves into Products & Pricing so it can be searched and used in all operations.') }}
                    </p>
                </div>
            </div>
            <x-close-button @click="closeModal()" />
        </div>

        {{-- Error Banner --}}
        <template x-if="errorMessage">
            <div class="p-3 text-xs rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 flex items-start gap-2">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span x-text="errorMessage"></span>
            </div>
        </template>

        {{-- Form Fields --}}
        <form @submit.prevent="submitProduct()" class="space-y-3.5 text-xs">
            {{-- Product Name --}}
            <div>
                <label for="qp-name" class="block font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                    {{ __('Product / Item Name') }} <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="qp-name"
                       x-model="form.name" 
                       required
                       placeholder="e.g. 4TB Surveillance HDD, 2MP Dome Camera, Cat6 Cable" 
                       class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-semibold focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            {{-- Category & Measurement Unit --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="qp-category" class="block font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                        {{ __('Category') }} <span class="text-rose-500">*</span>
                    </label>
                    <select id="qp-category" x-model="form.category" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-medium">
                        <option value="camera">Camera</option>
                        <option value="recorder">Recorder / NVR / DVR</option>
                        <option value="storage">Storage / HDD / SSD</option>
                        <option value="cable">Cables & Wire</option>
                        <option value="power">Power Supply / SMPS / UPS</option>
                        <option value="networking">Networking & PoE Switches</option>
                        <option value="accessory">Accessories & Connectors</option>
                        <option value="service">Labor / Installation Service</option>
                    </select>
                </div>
                <div>
                    <label for="qp-unit" class="block font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                        {{ __('Unit of Measurement') }} <span class="text-rose-500">*</span>
                    </label>
                    <select id="qp-unit" x-model="form.unit" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-medium">
                        <option value="Nos">Nos (Units / Pieces)</option>
                        <option value="Mtr">Mtr (Meters)</option>
                        <option value="Box">Box</option>
                        <option value="Set">Set</option>
                        <option value="Roll">Roll</option>
                        <option value="Pkt">Pkt (Packets)</option>
                        <option value="Job">Job (Service / Lumpsum)</option>
                    </select>
                </div>
            </div>

            {{-- SKU Code & Brand --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="qp-sku" class="block font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                        {{ __('Product SKU / Code') }} <span class="text-slate-400 text-[10px] font-normal">(Optional)</span>
                    </label>
                    <input type="text" 
                           id="qp-sku"
                           x-model="form.sku" 
                           placeholder="Auto-generated if empty" 
                           class="w-full px-3 py-2 text-xs font-mono bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl outline-none uppercase">
                </div>
                <div>
                    <label for="qp-brand" class="block font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                        {{ __('Brand / Manufacturer') }} <span class="text-slate-400 text-[10px] font-normal">(Optional)</span>
                    </label>
                    <input type="text" 
                           id="qp-brand"
                           x-model="form.brand" 
                           placeholder="e.g. Hikvision, CP Plus, Dahua" 
                           class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                </div>
            </div>

            {{-- Cost Price & Selling Price --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="qp-cost" class="block font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                        {{ __('Cost / Purchase Price (₹)') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" 
                           id="qp-cost"
                           step="any" 
                           min="0" 
                           x-model="form.cost_price" 
                           required 
                           placeholder="0.00" 
                           @input="if(!form.unit_price || form.unit_price == 0) form.unit_price = Math.round(form.cost_price * 1.25)"
                           class="w-full px-3 py-2 text-xs font-mono font-bold bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl outline-none text-slate-900 dark:text-white">
                </div>
                <div>
                    <label for="qp-price" class="block font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                        {{ __('Selling Price / Catalog Rate (₹)') }}
                    </label>
                    <input type="number" 
                           id="qp-price"
                           step="any" 
                           min="0" 
                           x-model="form.unit_price" 
                           placeholder="0.00" 
                           class="w-full px-3 py-2 text-xs font-mono font-bold bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl outline-none text-blue-600 dark:text-blue-400">
                </div>
            </div>

            {{-- Opening Stock & Description --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label for="qp-stock" class="block font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                        {{ __('Opening Stock') }}
                    </label>
                    <input type="number" 
                           id="qp-stock"
                           step="any" 
                           min="0" 
                           x-model="form.stock_quantity" 
                           placeholder="0" 
                           class="w-full px-3 py-2 text-xs font-mono bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label for="qp-model" class="block font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                        {{ __('Model No. / Specs') }} <span class="text-slate-400 text-[10px] font-normal">(Optional)</span>
                    </label>
                    <input type="text" 
                           id="qp-model"
                           x-model="form.model_no" 
                           placeholder="e.g. DS-2CE16D0T-IRPF" 
                           class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                </div>
            </div>

            {{-- Footer Buttons --}}
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" @click="closeModal()" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold transition">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" 
                        :disabled="isSubmitting"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold transition shadow-sm disabled:opacity-50">
                    <template x-if="isSubmitting">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Saving to Catalog...' : 'Save & Select Product'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function quickAddProductModalHandler() {
        return {
            isOpen: false,
            isSubmitting: false,
            errorMessage: null,
            targetSelectId: null,
            nameInputId: null,
            unitInputId: null,
            costInputId: null,
            form: {
                name: '',
                category: 'accessory',
                unit: 'Nos',
                sku: '',
                brand: '',
                model_no: '',
                cost_price: '',
                unit_price: '',
                stock_quantity: '0',
            },

            initModal() {
                window.openQuickAddProductModal = (options = {}) => {
                    this.resetForm();
                    this.targetSelectId = options.targetSelectId || null;
                    this.nameInputId = options.nameInputId || null;
                    this.unitInputId = options.unitInputId || null;
                    this.costInputId = options.costInputId || null;
                    if (options.prefillName) {
                        this.form.name = options.prefillName;
                    }
                    this.isOpen = true;
                    this.$nextTick(() => {
                        const nameEl = document.getElementById('qp-name');
                        if (nameEl) nameEl.focus();
                    });
                };

                window.addEventListener('open-quick-product-modal', (e) => {
                    window.openQuickAddProductModal(e.detail || {});
                });
            },

            resetForm() {
                this.errorMessage = null;
                this.isSubmitting = false;
                this.form = {
                    name: '',
                    category: 'accessory',
                    unit: 'Nos',
                    sku: '',
                    brand: '',
                    model_no: '',
                    cost_price: '',
                    unit_price: '',
                    stock_quantity: '0',
                };
            },

            closeModal() {
                this.isOpen = false;
                this.resetForm();
            },

            async submitProduct() {
                if (!this.form.name || !this.form.name.trim()) {
                    this.errorMessage = 'Please enter a product name.';
                    return;
                }

                this.isSubmitting = true;
                this.errorMessage = null;

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                        || document.querySelector('input[name="_token"]')?.value;

                    const res = await fetch("{{ route('products.quick-store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token || ''
                        },
                        body: JSON.stringify(this.form)
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        const err = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Failed to save product.');
                        this.errorMessage = err;
                        this.isSubmitting = false;
                        return;
                    }

                    const prod = data.product;

                    // 1. If target select element is provided, append option and select it
                    if (this.targetSelectId) {
                        const selectEl = document.getElementById(this.targetSelectId);
                        if (selectEl) {
                            // Check if option already exists
                            let opt = Array.from(selectEl.options).find(o => o.value == prod.id);
                            if (!opt) {
                                opt = document.createElement('option');
                                opt.value = prod.id;
                                opt.dataset.name = prod.name;
                                opt.dataset.unit = prod.unit.toLowerCase();
                                opt.dataset.cost = prod.cost_price;
                                opt.dataset.price = prod.unit_price;
                                opt.textContent = `[${prod.sku}] ${prod.name}`;
                                selectEl.appendChild(opt);
                            }
                            selectEl.value = prod.id;
                            selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }

                    // 2. If companion inputs are specified, auto-populate them
                    if (this.nameInputId) {
                        const nameEl = document.getElementById(this.nameInputId);
                        if (nameEl) {
                            nameEl.value = prod.name;
                            nameEl.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    }
                    if (this.unitInputId) {
                        const unitEl = document.getElementById(this.unitInputId);
                        if (unitEl) {
                            const val = prod.unit.toLowerCase();
                            // If select has this value or matching option
                            const matchingOpt = Array.from(unitEl.options || []).find(o => o.value.toLowerCase().includes(val) || val.includes(o.value.toLowerCase()));
                            if (matchingOpt) {
                                unitEl.value = matchingOpt.value;
                            } else {
                                unitEl.value = val;
                            }
                            unitEl.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                    if (this.costInputId && prod.cost_price > 0) {
                        const costEl = document.getElementById(this.costInputId);
                        if (costEl) {
                            costEl.value = prod.cost_price;
                            costEl.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    }

                    // 3. Dispatch global product creation event for any listeners
                    window.dispatchEvent(new CustomEvent('product-created-in-catalog', { detail: prod }));

                    // 4. Close modal
                    this.closeModal();

                } catch (e) {
                    this.errorMessage = e.message || 'Network error while creating product.';
                    this.isSubmitting = false;
                }
            }
        };
    }
</script>