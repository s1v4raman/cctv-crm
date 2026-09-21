<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-white tracking-tight font-heading flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-sm shadow-amber-400"></span>
                    Create Quotation
                </h1>

                <p class="mt-1 text-xs text-slate-400">
                    Customer: <strong class="text-white">{{ $lead->customer_name }}</strong> · <span class="text-slate-300 font-mono">{{ $lead->phone }}</span>
                </p>
            </div>

            <a href="{{ route('leads.show', $lead) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-bold transition shadow-sm">
                ← Back to Lead
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-visible bg-[#0f172a] border border-slate-800/80 shadow-2xl rounded-2xl">
                <form method="POST"
                      action="{{ route('quotations.store', $lead) }}"
                      class="p-6">
                    @csrf

                    @if ($errors->any())
                        <div class="mb-6 rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-rose-300">
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
                                   class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                Quotation Date
                            </label>

                            <input type="date"
                                   id="quotation_date"
                                   name="quotation_date"
                                   value="{{ old('quotation_date', now()->format('Y-m-d')) }}"
                                   required
                                   class="mt-1 block w-full rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-sm px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition">

                            @error('quotation_date')
                                <p class="mt-1 text-xs font-semibold text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="valid_until"
                                   class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                Valid Until
                            </label>

                            <input type="date"
                                   id="valid_until"
                                   name="valid_until"
                                   value="{{ old('valid_until', now()->addDays(15)->format('Y-m-d')) }}"
                                   class="mt-1 block w-full rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-sm px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition">

                            @error('valid_until')
                                <p class="mt-1 text-xs font-semibold text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tax_percent"
                                   class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                GST / Tax (%)
                            </label>

                            <input type="number"
                                   id="tax_percent"
                                   name="tax_percent"
                                   min="0"
                                   max="100"
                                   step="0.01"
                                   value="{{ old('tax_percent', 18) }}"
                                   class="mt-1 block w-full rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-sm px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono">

                            @error('tax_percent')
                                <p class="mt-1 text-xs font-semibold text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                            <div>
                                <h3 class="text-base font-extrabold text-white font-heading">
                                    Quotation Items
                                </h3>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    Type a product name in Item / Service to search catalogue, or enter a custom specification.
                                </p>
                            </div>

                            <button type="button"
                                    id="add-row"
                                    class="btn-amber inline-flex items-center gap-1.5 px-3.5 py-2 text-xs">
                                + Add Custom Item
                            </button>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-800">
                            <table class="min-w-full border-collapse">
                                <thead class="bg-slate-900/90 border-b border-slate-800">
                                    <tr>
                                        <th class="min-w-64 px-3 py-3 text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                                            Item / Service
                                        </th>
                                        <th class="min-w-56 px-3 py-3 text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                                            Description
                                        </th>
                                        <th class="px-3 py-3 text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                                            Qty
                                        </th>
                                        <th class="px-3 py-3 text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                                            Unit
                                        </th>
                                        <th class="px-3 py-3 text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                                            Unit Price
                                        </th>
                                        <th class="px-3 py-3 text-right text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
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
                                        <tr class="item-row border-b border-slate-800/80 hover:bg-slate-800/20 transition">
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
                                                       class="item-name block w-full rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-xs px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 placeholder-slate-500">

                                                <div class="product-results absolute left-2 right-2 z-50 mt-1 hidden max-h-64 overflow-y-auto rounded-xl border border-slate-700 bg-[#0b1120] shadow-2xl divide-y divide-slate-800">
                                                </div>
                                            </td>

                                            <td class="p-2 align-top">
                                                <input type="text"
                                                       name="items[{{ $index }}][description]"
                                                       value="{{ $item['description'] ?? '' }}"
                                                       placeholder="Optional specification"
                                                       class="item-description block w-full rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-xs px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 placeholder-slate-500">
                                            </td>

                                            <td class="p-2 align-top">
                                                <input type="number"
                                                       name="items[{{ $index }}][quantity]"
                                                       value="{{ $item['quantity'] ?? 1 }}"
                                                       min="0.01"
                                                       step="0.01"
                                                       required
                                                       class="quantity w-24 rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-xs px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 font-mono">
                                            </td>

                                            <td class="p-2 align-top">
                                                <select name="items[{{ $index }}][unit]"
                                                        class="item-unit w-24 rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-xs px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
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
                                                       class="unit-price w-32 rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-xs px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 font-mono">
                                            </td>

                                            <td class="line-total p-2 text-right text-xs font-bold text-amber-400 font-mono align-middle">
                                                ₹0.00
                                            </td>

                                            <td class="p-2 text-right align-middle">
                                                <button type="button"
                                                        class="remove-row rounded-lg bg-rose-500/10 border border-rose-500/20 px-2.5 py-1.5 text-xs font-bold text-rose-400 hover:bg-rose-500/20 transition">
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
                                   class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                Notes / Terms & Conditions
                            </label>

                            <textarea id="notes"
                                      name="notes"
                                      rows="6"
                                      placeholder="Installation terms, warranty details, payment schedule..."
                                      class="mt-1 block w-full rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-xs p-3 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 placeholder-slate-500 leading-relaxed">{{ old('notes') }}</textarea>

                            @error('notes')
                                <p class="mt-1 text-xs font-semibold text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-2xl bg-[#060913] border border-slate-800 p-5 space-y-3">
                            <div class="flex justify-between items-center text-xs text-slate-400">
                                <span>Subtotal</span>
                                <span id="subtotal-display" class="font-bold text-slate-200 font-mono text-sm">₹0.00</span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs text-slate-400">
                                <label for="discount">Discount (₹)</label>

                                <input type="number"
                                       id="discount"
                                       name="discount"
                                       min="0"
                                       step="0.01"
                                       value="{{ old('discount', 0) }}"
                                       class="w-32 rounded-lg border border-slate-700/80 bg-[#0b1120] text-slate-100 text-right text-xs px-2.5 py-1.5 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 font-mono">
                            </div>

                            <div class="flex justify-between items-center text-xs text-slate-400">
                                <span>GST Amount</span>
                                <span id="tax-display" class="font-bold text-slate-200 font-mono text-sm">₹0.00</span>
                            </div>

                            <div class="mt-2 flex justify-between items-center border-t border-slate-800 pt-3 text-sm font-extrabold text-white">
                                <span>Grand Total</span>
                                <span id="grand-total-display" class="text-amber-400 font-mono text-xl font-black">₹0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="sticky bottom-0 z-40 mt-8 flex items-center justify-end gap-3 border-t border-slate-800 bg-[#0b1120]/95 backdrop-blur-md px-6 py-4 rounded-b-2xl shadow-2xl">
                        <a href="{{ route('leads.show', $lead) }}"
                           class="inline-flex items-center justify-center px-5 py-2.5 bg-slate-800/90 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs uppercase tracking-wider rounded-xl border border-slate-700 shadow-sm transition">
                            Cancel
                        </a>

                        <button type="submit"
                                class="btn-amber min-w-40 py-2.5">
                            Save Quotation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="item-row-template">
        <tr class="item-row border-b border-slate-800/80 hover:bg-slate-800/20 transition">
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
                       class="item-name block w-full rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-xs px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 placeholder-slate-500">

                <div class="product-results absolute left-2 right-2 z-50 mt-1 hidden max-h-64 overflow-y-auto rounded-xl border border-slate-700 bg-[#0b1120] shadow-2xl divide-y divide-slate-800">
                </div>
            </td>

            <td class="p-2 align-top">
                <input type="text"
                       name="items[__INDEX__][description]"
                       placeholder="Optional specification"
                       class="item-description block w-full rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-xs px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 placeholder-slate-500">
            </td>

            <td class="p-2 align-top">
                <input type="number"
                       name="items[__INDEX__][quantity]"
                       value="1"
                       min="0.01"
                       step="0.01"
                       required
                       class="quantity w-24 rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-xs px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 font-mono">
            </td>

            <td class="p-2 align-top">
                <select name="items[__INDEX__][unit]"
                        class="item-unit w-24 rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-xs px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
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
                       class="unit-price w-32 rounded-xl border border-slate-700/80 bg-[#060913] text-slate-100 text-xs px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 font-mono">
            </td>

            <td class="line-total p-2 text-right text-xs font-bold text-amber-400 font-mono align-middle">
                ₹0.00
            </td>

            <td class="p-2 text-right align-middle">
                <button type="button"
                        class="remove-row rounded-lg bg-rose-500/10 border border-rose-500/20 px-2.5 py-1.5 text-xs font-bold text-rose-400 hover:bg-rose-500/20 transition">
                    Remove
                </button>
            </td>
        </tr>
    </template>

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

        function hideAllProductResults() {
            document.querySelectorAll('.product-results').forEach((resultsBox) => {
                resultsBox.innerHTML = '';
                resultsBox.classList.add('hidden');
            });
        }

        function escapeHtml(value) {
            const element = document.createElement('div');
            element.textContent = value || '';

            return element.innerHTML;
        }

        function selectProduct(row, product) {
            row.querySelector('.product-id').value = product.id;
            row.querySelector('.item-name').value = product.name;
            row.querySelector('.item-description').value = product.description || '';
            row.querySelector('.item-unit').value = product.unit;
            row.querySelector('.unit-price').value = product.unit_price;

            const resultsBox = row.querySelector('.product-results');
            resultsBox.innerHTML = '';
            resultsBox.classList.add('hidden');

            calculateTotals();

            row.querySelector('.quantity').focus();
            row.querySelector('.quantity').select();
        }

        async function showProductResults(row, searchValue) {
            const resultsBox = row.querySelector('.product-results');
            const searchText = searchValue.trim();

            if (searchText.length < 1) {
                resultsBox.innerHTML = '';
                resultsBox.classList.add('hidden');
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
                        headers: {
                            Accept: 'application/json'
                        },
                        signal: searchController.signal
                    }
                );

                if (!response.ok) {
                    throw new Error('Product search failed.');
                }

                const products = await response.json();

                if (products.length === 0) {
                    resultsBox.innerHTML = `
                        <div class="px-3 py-3 text-xs text-slate-400">
                            No catalogue product found. You can enter a custom item manually.
                        </div>
                    `;

                    resultsBox.classList.remove('hidden');
                    return;
                }

                resultsBox.innerHTML = '';

                products.forEach((product) => {
                    const button = document.createElement('button');

                    button.type = 'button';
                    button.className = 'block w-full border-b border-slate-800 px-3 py-2.5 text-left hover:bg-slate-800 transition last:border-b-0';

                    button.innerHTML = `
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-bold text-xs text-white">${escapeHtml(product.name)}</div>
                                <div class="mt-0.5 text-[11px] text-slate-400">${escapeHtml(product.description || '')}</div>
                            </div>

                            <div class="whitespace-nowrap text-xs font-extrabold text-amber-400 font-mono">
                                ${money(product.unit_price)}
                            </div>
                        </div>
                    `;

                    button.addEventListener('mousedown', (event) => {
                        event.preventDefault();
                        selectProduct(row, product);
                    });

                    resultsBox.appendChild(button);
                });

                resultsBox.classList.remove('hidden');
            } catch (error) {
                if (error.name !== 'AbortError') {
                    resultsBox.innerHTML = `
                        <div class="px-3 py-3 text-xs text-rose-400">
                            Unable to search products. Please try again.
                        </div>
                    `;

                    resultsBox.classList.remove('hidden');
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
                hideAllProductResults();

                searchTimer = setTimeout(() => {
                    showProductResults(row, itemNameInput.value);
                }, 250);
            });

            itemNameInput.addEventListener('focus', () => {
                if (itemNameInput.value.trim() !== '') {
                    hideAllProductResults();
                    showProductResults(row, itemNameInput.value);
                }
            });

            itemNameInput.addEventListener('blur', () => {
                setTimeout(() => {
                    row.querySelector('.product-results').classList.add('hidden');
                }, 150);
            });

            quantityInput.addEventListener('input', calculateTotals);
            unitPriceInput.addEventListener('input', calculateTotals);

            removeButton.addEventListener('click', () => {
                const rows = document.querySelectorAll('.item-row');

                if (rows.length === 1) {
                    alert('A quotation must contain at least one item.');
                    return;
                }

                row.remove();
                calculateTotals();
            });
        }

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

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.item-row')) {
                hideAllProductResults();
            }
        });

        calculateTotals();
    </script>
</x-app-layout>