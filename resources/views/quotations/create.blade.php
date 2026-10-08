<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight font-heading flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-sm shadow-amber-500"></span>
                    Create Quotation
                </h1>

                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Customer: <strong class="text-slate-900 dark:text-white font-semibold">{{ $lead->customer_name }}</strong> · <span class="text-slate-600 dark:text-slate-300 font-mono">{{ $lead->phone }}</span>
                </p>
            </div>

            <a href="{{ route('leads.show', $lead) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 text-xs font-bold transition shadow-xs">
                ← Back to Lead
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-visible bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm rounded-2xl">
                <form method="POST"
                      action="{{ route('quotations.store', $lead) }}"
                      class="p-6">
                    @csrf

                    @if ($errors->any())
                        <div class="mb-6 rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-rose-700 dark:text-rose-300">
                            <p class="font-bold text-xs">
                                Please correct the highlighted quotation fields.
                            </p>

                            <ul class="mt-2 list-disc pl-5 text-xs">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <label for="quotation_date"
                                   class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Quotation Date
                            </label>

                            <input type="date"
                                   id="quotation_date"
                                   name="quotation_date"
                                   value="{{ old('quotation_date', now()->format('Y-m-d')) }}"
                                   required
                                   class="mt-1 block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm px-3.5 py-2.5 min-h-[44px] shadow-xs focus:bg-white dark:focus:bg-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">

                            @error('quotation_date')
                                <p class="mt-1 text-xs font-semibold text-rose-500 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="valid_until"
                                   class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Valid Until
                            </label>

                            <input type="date"
                                   id="valid_until"
                                   name="valid_until"
                                   value="{{ old('valid_until', now()->addDays(15)->format('Y-m-d')) }}"
                                   class="mt-1 block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm px-3.5 py-2.5 min-h-[44px] shadow-xs focus:bg-white dark:focus:bg-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">

                            @error('valid_until')
                                <p class="mt-1 text-xs font-semibold text-rose-500 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tax_percent"
                                   class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                GST / Tax (%)
                            </label>

                            <input type="number"
                                   id="tax_percent"
                                   name="tax_percent"
                                   min="0"
                                   max="100"
                                   step="0.01"
                                   value="{{ old('tax_percent', 18) }}"
                                   class="mt-1 block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm px-3.5 py-2.5 min-h-[44px] shadow-xs focus:bg-white dark:focus:bg-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition font-mono">

                            @error('tax_percent')
                                <p class="mt-1 text-xs font-semibold text-rose-500 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white font-heading">
                                    Quotation Items
                                </h3>

                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    Type a product name in Item / Service to search catalogue, or enter a custom specification.
                                </p>
                            </div>

                            <button type="button"
                                    id="add-row"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Custom Item</span>
                            </button>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                            <table class="min-w-full border-collapse">
                                <thead class="bg-slate-50 dark:bg-slate-800/90 border-b border-slate-200 dark:border-slate-800">
                                    <tr>
                                        <th class="min-w-64 px-3 py-3 text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                            Item / Service
                                        </th>
                                        <th class="min-w-56 px-3 py-3 text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                            Description
                                        </th>
                                        <th class="px-3 py-3 text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                            Qty
                                        </th>
                                        <th class="px-3 py-3 text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                            Unit
                                        </th>
                                        <th class="px-3 py-3 text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                            Unit Price
                                        </th>
                                        <th class="px-3 py-3 text-right text-[11px] font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                            Total
                                        </th>
                                        <th class="px-3 py-3"></th>
                                    </tr>
                                </thead>

                                <tbody id="items-body">
                                    @php
                                        $items = old('items', [[
                                            'item_name' => '',
                                            'description' => '',
                                            'quantity' => 1,
                                            'unit' => 'Nos',
                                            'unit_price' => 0,
                                        ]]);
                                    @endphp

                                    @foreach ($items as $index => $item)
                                        <tr class="item-row border-b border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/20 transition">
                                            <td class="relative p-2 align-top">
                                                <input type="hidden"
                                                       class="product-id"
                                                       name="items[{{ $index }}][product_id]"
                                                       value="{{ $item['product_id'] ?? '' }}">

                                                <input type="text"
                                                       name="items[{{ $index }}][item_name]"
                                                       value="{{ $item['item_name'] ?? '' }}"
                                                       required
                                                       autocomplete="off"
                                                       placeholder="Search camera, NVR, cable..."
                                                       class="item-name block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-slate-400">
                                            </td>

                                            <td class="p-2 align-top">
                                                <input type="text"
                                                       name="items[{{ $index }}][description]"
                                                       value="{{ $item['description'] ?? '' }}"
                                                       placeholder="Optional specification"
                                                       class="item-description block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-slate-400">
                                            </td>

                                            <td class="p-2 align-top">
                                                <input type="number"
                                                       name="items[{{ $index }}][quantity]"
                                                       value="{{ $item['quantity'] ?? 1 }}"
                                                       min="0.01"
                                                       step="0.01"
                                                       required
                                                       class="quantity w-24 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono">
                                            </td>

                                            <td class="p-2 align-top">
                                                <select name="items[{{ $index }}][unit]"
                                                        class="item-unit w-24 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                                    <option value="Nos" @selected(($item['unit'] ?? 'Nos') === 'Nos')>Nos</option>
                                                    <option value="Mtr" @selected(($item['unit'] ?? '') === 'Mtr')>Mtr</option>
                                                    <option value="Box" @selected(($item['unit'] ?? '') === 'Box')>Box</option>
                                                    <option value="Set" @selected(($item['unit'] ?? '') === 'Set')>Set</option>
                                                    <option value="Job" @selected(($item['unit'] ?? '') === 'Job')>Job</option>
                                                </select>
                                            </td>

                                            <td class="p-2 align-top">
                                                <input type="number"
                                                       name="items[{{ $index }}][unit_price]"
                                                       value="{{ $item['unit_price'] ?? 0 }}"
                                                       min="0"
                                                       step="0.01"
                                                       required
                                                       class="unit-price w-32 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono">
                                            </td>

                                            <td class="line-total p-2 text-right text-xs font-bold text-slate-800 dark:text-amber-400 font-mono align-middle">
                                                ₹0.00
                                            </td>

                                            <td class="p-2 text-right align-middle">
                                                <button type="button"
                                                        class="remove-row inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-rose-600 dark:text-rose-400 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 border border-rose-200/80 dark:border-rose-800/60 font-bold text-xs transition cursor-pointer">
                                                    Remove
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="notes"
                                   class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Notes / Terms & Conditions
                            </label>

                            <textarea id="notes"
                                      name="notes"
                                      rows="6"
                                      placeholder="Installation terms, warranty details, payment schedule..."
                                      class="mt-1 block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs p-3 shadow-xs focus:bg-white dark:focus:bg-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-slate-400 leading-relaxed">{{ old('notes') }}</textarea>

                            @error('notes')
                                <p class="mt-1 text-xs font-semibold text-rose-500 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/90 dark:border-slate-700/80 p-5 space-y-3">
                            <div class="flex justify-between items-center text-xs text-slate-600 dark:text-slate-400">
                                <span>Subtotal</span>
                                <span id="subtotal-display" class="font-bold text-slate-900 dark:text-slate-100 font-mono text-sm">₹0.00</span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs text-slate-600 dark:text-slate-400">
                                <label for="discount">Discount (₹)</label>

                                <input type="number"
                                       id="discount"
                                       name="discount"
                                       min="0"
                                       step="0.01"
                                       value="{{ old('discount', 0) }}"
                                       class="w-32 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-right text-xs px-2.5 py-1.5 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono">
                            </div>

                            <div class="flex justify-between items-center text-xs text-slate-600 dark:text-slate-400">
                                <span>GST Amount</span>
                                <span id="tax-display" class="font-bold text-slate-900 dark:text-slate-100 font-mono text-sm">₹0.00</span>
                            </div>

                            <div class="mt-2 flex justify-between items-center border-t border-slate-200 dark:border-slate-700 pt-3 text-sm font-extrabold text-slate-900 dark:text-white">
                                <span>Grand Total</span>
                                <span id="grand-total-display" class="text-blue-600 dark:text-amber-400 font-mono text-xl font-black">₹0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="sticky bottom-0 z-30 mt-8 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md px-6 py-4 rounded-b-2xl shadow-lg">
                        <a href="{{ route('leads.show', $lead) }}"
                           class="inline-flex items-center justify-center px-6 py-2.5 bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-sm rounded-xl min-h-[44px] border border-slate-300 dark:border-slate-600 shadow-xs transition cursor-pointer">
                            Cancel
                        </a>

                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-7 py-2.5 rounded-xl text-white font-bold text-sm bg-blue-600 hover:bg-blue-700 active:scale-[0.99] shadow-md shadow-blue-500/20 transition-all min-h-[44px] min-w-44 cursor-pointer"
                                style="background-color: var(--crm-accent, #2563eb) !important; color: #ffffff !important;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Save Quotation</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="item-row-template">
        <tr class="item-row border-b border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/20 transition">
            <td class="relative p-2 align-top">
                <input type="hidden"
                       class="product-id"
                       name="items[__INDEX__][product_id]"
                       value="">

                <input type="text"
                       name="items[__INDEX__][item_name]"
                       required
                       autocomplete="off"
                       placeholder="Search camera, NVR, cable..."
                       class="item-name block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-slate-400">
            </td>

            <td class="p-2 align-top">
                <input type="text"
                       name="items[__INDEX__][description]"
                       placeholder="Optional specification"
                       class="item-description block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-slate-400">
            </td>

            <td class="p-2 align-top">
                <input type="number"
                       name="items[__INDEX__][quantity]"
                       value="1"
                       min="0.01"
                       step="0.01"
                       required
                       class="quantity w-24 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono">
            </td>

            <td class="p-2 align-top">
                <select name="items[__INDEX__][unit]"
                        class="item-unit w-24 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="Nos">Nos</option>
                    <option value="Mtr">Mtr</option>
                    <option value="Box">Box</option>
                    <option value="Set">Set</option>
                    <option value="Job">Job</option>
                </select>
            </td>

            <td class="p-2 align-top">
                <input type="number"
                       name="items[__INDEX__][unit_price]"
                       value="0"
                       min="0"
                       step="0.01"
                       required
                       class="unit-price w-32 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm px-3 py-2 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono">
            </td>

            <td class="line-total p-2 text-right text-xs font-bold text-slate-800 dark:text-amber-400 font-mono align-middle">
                ₹0.00
            </td>

            <td class="p-2 text-right align-middle">
                <button type="button"
                        class="remove-row inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-rose-600 dark:text-rose-400 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 border border-rose-200/80 dark:border-rose-800/60 font-bold text-xs transition cursor-pointer">
                    Remove
                </button>
            </td>
        </tr>
    </template>

    {{-- Floating Portal for Quotation Product Autocomplete Search (Theme-adaptive & never clipped by table overflow) --}}
    <div id="quotation-product-dropdown" 
         class="fixed z-[99999] hidden max-h-72 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-2xl divide-y divide-slate-100 dark:divide-slate-800">
    </div>

    <script>
        const itemsBody = document.getElementById('items-body');
        const template = document.getElementById('item-row-template').innerHTML;
        const addRowButton = document.getElementById('add-row');
        const taxPercentInput = document.getElementById('tax_percent');
        const discountInput = document.getElementById('discount');

        let nextItemIndex = document.querySelectorAll('.item-row').length;
        let searchTimer = null;
        let searchController = null;

        function money(value) {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        }

        function calculateTotals() {
            let subtotal = 0;

            document.querySelectorAll('.item-row').forEach((row) => {
                const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
                const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
                const lineTotal = quantity * unitPrice;

                row.querySelector('.line-total').textContent = money(lineTotal);
                subtotal += lineTotal;
            });

            const discount = parseFloat(discountInput.value) || 0;
            const taxPercent = parseFloat(taxPercentInput.value) || 0;
            const taxableAmount = Math.max(subtotal - discount, 0);
            const taxAmount = taxableAmount * (taxPercent / 100);
            const grandTotal = taxableAmount + taxAmount;

            document.getElementById('subtotal-display').textContent = money(subtotal);
            document.getElementById('tax-display').textContent = money(taxAmount);
            document.getElementById('grand-total-display').textContent = money(grandTotal);
        }

        const productDropdown = document.getElementById('quotation-product-dropdown');
        let activeRow = null;
        let activeInput = null;
        let highlightedIndex = -1;
        let currentProducts = [];

        function hideAllProductResults() {
            if (productDropdown) {
                productDropdown.innerHTML = '';
                productDropdown.classList.add('hidden');
            }
            activeRow = null;
            activeInput = null;
            highlightedIndex = -1;
            currentProducts = [];
        }

        function positionDropdown(input) {
            if (!input || !productDropdown) return;
            const rect = input.getBoundingClientRect();
            const availableWidth = Math.max(0, window.innerWidth - 32);
            const targetWidth = Math.min(Math.max(rect.width, 380), availableWidth);

            // Compute space below vs above
            const spaceBelow = window.innerHeight - rect.bottom;
            const dropdownHeight = Math.min(productDropdown.scrollHeight || 260, 280);

            if (spaceBelow < dropdownHeight + 10 && rect.top > dropdownHeight + 10) {
                productDropdown.style.top = `${Math.max(10, rect.top - dropdownHeight - 6)}px`;
            } else {
                productDropdown.style.top = `${rect.bottom + 6}px`;
            }

            let left = rect.left;
            if (left + targetWidth > window.innerWidth - 16) {
                left = window.innerWidth - targetWidth - 16;
            }
            productDropdown.style.left = `${Math.max(16, left)}px`;
            productDropdown.style.width = `${targetWidth}px`;
        }

        function updateHighlight() {
            if (!productDropdown) return;
            const buttons = productDropdown.querySelectorAll('.product-item-btn');
            buttons.forEach((btn, idx) => {
                if (idx === highlightedIndex) {
                    btn.classList.add('bg-blue-50/90', 'dark:bg-slate-800', 'border-l-blue-600', 'dark:border-l-blue-400');
                    btn.classList.remove('border-l-transparent');
                    btn.scrollIntoView({ block: 'nearest' });
                } else {
                    btn.classList.remove('bg-blue-50/90', 'dark:bg-slate-800', 'border-l-blue-600', 'dark:border-l-blue-400');
                    btn.classList.add('border-l-transparent');
                }
            });
        }

        function escapeHtml(value) {
            const element = document.createElement('div');
            element.textContent = value || '';
            return element.innerHTML;
        }

        function selectProduct(row, product) {
            if (!row || !product) return;
            row.querySelector('.product-id').value = product.id;
            row.querySelector('.item-name').value = product.name;
            row.querySelector('.item-description').value = product.description || '';
            row.querySelector('.item-unit').value = product.unit;
            row.querySelector('.unit-price').value = product.unit_price;

            hideAllProductResults();
            calculateTotals();

            const quantityInput = row.querySelector('.quantity');
            if (quantityInput) {
                quantityInput.focus();
                quantityInput.select();
            }
        }

        async function showProductResults(row, searchValue) {
            activeRow = row;
            activeInput = row.querySelector('.item-name');
            const searchText = searchValue.trim();

            if (searchText.length < 1) {
                hideAllProductResults();
                return;
            }

            if (searchController) {
                searchController.abort();
            }
            searchController = new AbortController();

            try {
                const response = await fetch(
                    `{{ route('products.search') }}?q=${encodeURIComponent(searchText)}`,
                    {
                        headers: { Accept: 'application/json' },
                        signal: searchController.signal
                    }
                );

                if (!response.ok) {
                    throw new Error('Product search failed.');
                }

                const products = await response.json();
                currentProducts = products;
                highlightedIndex = -1;

                if (products.length === 0) {
                    productDropdown.innerHTML = `
                        <div class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400 text-center font-medium">
                            No catalogue product found for "<span class="text-slate-800 dark:text-slate-200 font-bold">${escapeHtml(searchText)}</span>". Enter custom specification manually.
                        </div>
                    `;
                    positionDropdown(activeInput);
                    productDropdown.classList.remove('hidden');
                    return;
                }

                productDropdown.innerHTML = '';

                products.forEach((product, idx) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.index = idx;
                    button.className = 'product-item-btn w-full px-4 py-3 text-left transition-colors flex items-start justify-between gap-3 cursor-pointer bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/80 border-l-4 border-l-transparent group';

                    button.innerHTML = `
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                ${escapeHtml(product.name)}
                            </div>
                            ${product.description ? `
                                <div class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                                    ${escapeHtml(product.description)}
                                </div>
                            ` : ''}
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/50 shadow-2xs">
                                ${money(product.unit_price)}
                            </span>
                        </div>
                    `;

                    button.addEventListener('mousedown', (event) => {
                        event.preventDefault();
                        selectProduct(row, product);
                    });

                    productDropdown.appendChild(button);
                });

                positionDropdown(activeInput);
                productDropdown.classList.remove('hidden');
            } catch (error) {
                if (error.name !== 'AbortError') {
                    productDropdown.innerHTML = `
                        <div class="px-4 py-3 text-xs text-rose-500 dark:text-rose-400 text-center">
                            Unable to search catalogue products.
                        </div>
                    `;
                    positionDropdown(activeInput);
                    productDropdown.classList.remove('hidden');
                }
            }
        }

        function bindRowEvents(row) {
            const itemNameInput = row.querySelector('.item-name');
            const productIdInput = row.querySelector('.product-id');
            const quantityInput = row.querySelector('.quantity');
            const unitPriceInput = row.querySelector('.unit-price');
            const removeButton = row.querySelector('.remove-row');

            itemNameInput.addEventListener('input', () => {
                productIdInput.value = '';
                clearTimeout(searchTimer);

                searchTimer = setTimeout(() => {
                    showProductResults(row, itemNameInput.value);
                }, 200);
            });

            itemNameInput.addEventListener('focus', () => {
                if (itemNameInput.value.trim() !== '') {
                    showProductResults(row, itemNameInput.value);
                }
            });

            itemNameInput.addEventListener('blur', () => {
                setTimeout(() => {
                    hideAllProductResults();
                }, 200);
            });

            itemNameInput.addEventListener('keydown', (e) => {
                if (!productDropdown || productDropdown.classList.contains('hidden') || currentProducts.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    highlightedIndex = (highlightedIndex + 1) % currentProducts.length;
                    updateHighlight();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    highlightedIndex = (highlightedIndex - 1 + currentProducts.length) % currentProducts.length;
                    updateHighlight();
                } else if (e.key === 'Enter') {
                    if (highlightedIndex >= 0 && highlightedIndex < currentProducts.length) {
                        e.preventDefault();
                        selectProduct(row, currentProducts[highlightedIndex]);
                    }
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    hideAllProductResults();
                }
            });

            quantityInput.addEventListener('input', calculateTotals);
            unitPriceInput.addEventListener('input', calculateTotals);

            removeButton.addEventListener('click', () => {
                const rows = document.querySelectorAll('.item-row');

                if (rows.length === 1) {
                    alert('A quotation must contain at least one item.');
                    return;
                }

                hideAllProductResults();
                row.remove();
                calculateTotals();
            });
        }

        // Global listeners for positioning and click away
        window.addEventListener('scroll', () => {
            if (activeInput && productDropdown && !productDropdown.classList.contains('hidden')) {
                positionDropdown(activeInput);
            }
        }, true);

        window.addEventListener('resize', () => {
            if (activeInput && productDropdown && !productDropdown.classList.contains('hidden')) {
                positionDropdown(activeInput);
            }
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('#quotation-product-dropdown') && !event.target.closest('.item-name')) {
                hideAllProductResults();
            }
        });

        function addNewItemRow() {
            const index = nextItemIndex++;

            itemsBody.insertAdjacentHTML(
                'beforeend',
                template.replaceAll('__INDEX__', index)
            );

            const row = itemsBody.lastElementChild;
            bindRowEvents(row);
            return row;
        }

        addRowButton.addEventListener('click', () => {
            const row = addNewItemRow();
            row.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            row.querySelector('.item-name').focus();
            calculateTotals();
        });

        document.querySelectorAll('.item-row').forEach((row) => {
            bindRowEvents(row);
        });

        taxPercentInput.addEventListener('input', calculateTotals);
        discountInput.addEventListener('input', calculateTotals);

        calculateTotals();
    </script>
</x-app-layout>