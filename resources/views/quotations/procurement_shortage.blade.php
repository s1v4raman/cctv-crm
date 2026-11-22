<x-app-layout>
    @php
        $isAdmin = auth()->user()?->isAdmin();
        $isOffice = auth()->user()?->isOffice();
    @endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <a href="{{ route('quotations.show', $quotation) }}" class="back-link">&larr; Back to Quotation</a>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1 flex items-center gap-2">
                    <span>{{ __('Quotation Stock Shortage & Procurement Review') }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                        {{ __('Accepted Quotation') }}
                    </span>
                </h2>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ __('Procurement deficit analysis for Quotation') }} <strong class="text-slate-800 dark:text-slate-200">#{{ $quotation->quotation_no }}</strong> &bull; {{ $quotation->lead->customer_name }}
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('quotations.show', $quotation) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>{{ __('View Quotation') }}</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. Prominently Highlighted Quoted Customer & Deal Header --}}
            <div class="rounded-2xl border border-blue-200 dark:border-blue-900/60 bg-gradient-to-r from-blue-50/80 via-white to-blue-50/50 dark:from-slate-900 dark:via-slate-900 dark:to-blue-950/30 p-5 sm:p-6 shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-black bg-blue-600 text-white shadow-2xs">
                                {{ $quotation->quotation_no }}
                            </span>
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                Accepted on {{ $quotation->accepted_at ? $quotation->accepted_at->format('d M Y, h:i A') : $quotation->updated_at->format('d M Y') }}
                            </span>
                        </div>

                        <h3 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white leading-snug">
                            {{ $quotation->lead->customer_name }}
                        </h3>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-600 dark:text-slate-400">
                            @if($quotation->lead->phone)
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $quotation->lead->phone }}
                                </span>
                            @endif
                            @if($quotation->lead->site_address)
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $quotation->lead->site_address }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Highlight Stats Pills --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 shrink-0">
                        <div class="p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Quotation Total') }}</span>
                            <span class="text-sm sm:text-base font-black text-slate-900 dark:text-white font-mono mt-0.5 block">
                                ₹{{ number_format((float)$quotation->total, 2) }}
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-rose-50/80 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 shadow-2xs">
                            <span class="block text-[10px] font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider">{{ __('Shortage Items') }}</span>
                            <span class="text-sm sm:text-base font-black text-rose-700 dark:text-rose-300 font-mono mt-0.5 block" id="header-shortage-count">
                                {{ count($shortageItems) }} {{ __('Items') }}
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 shadow-2xs col-span-2 sm:col-span-1">
                            <span class="block text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider">{{ __('Est. Purchase Cost') }}</span>
                            <span class="text-sm sm:text-base font-black text-amber-700 dark:text-amber-300 font-mono mt-0.5 block" id="header-estimated-total">
                                ₹{{ number_format((float)$totalEstimatedCost, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Role Guidance & Control Notice --}}
            @if($isAdmin)
                <div class="rounded-xl border border-emerald-300 dark:border-emerald-800 bg-emerald-50/90 dark:bg-emerald-950/40 p-4 text-xs text-emerald-900 dark:text-emerald-200 flex items-start gap-3 shadow-2xs">
                    <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5 font-bold">
                        ✓
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-emerald-950 dark:text-emerald-100">
                            {{ __('Admin Procurement Analysis & Purchase Control') }}
                        </h4>
                        <p class="mt-0.5 leading-relaxed text-emerald-900/90 dark:text-emerald-300/90">
                            {{ __('Review the zero-stock and low-stock products needed for this accepted quotation. You can analyze and edit the order quantities and purchase rates, choose suppliers, or remove items. When ready, click "Generate Purchase Order & Proceed to PDF" to create the official PO for immediate purchasing.') }}
                        </p>
                    </div>
                </div>
            @else
                <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50/90 dark:bg-blue-950/40 p-4 text-xs text-blue-900 dark:text-blue-200 flex items-start gap-3 shadow-2xs">
                    <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5 font-bold">
                        ℹ
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-blue-950 dark:text-blue-100">
                            {{ __('Employee Visibility Mode') }}
                        </h4>
                        <p class="mt-0.5 leading-relaxed text-blue-900/90 dark:text-blue-300/90">
                            {{ __('You are viewing the zero-stock and low-stock hardware required for this accepted installation. Generating and purchasing official Purchase Orders is restricted to Administrators.') }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- 3. Shortage Data Table & Purchase Order Form --}}
            @if(count($shortageItems) > 0)
                <form id="procurement-form" method="POST" action="{{ route('quotations.procurement-po', $quotation) }}">
                    @csrf

                    <div class="overflow-visible bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm rounded-2xl">
                        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h4 class="font-extrabold text-sm text-slate-900 dark:text-white uppercase tracking-wider">
                                    {{ __('Zero & Low Stock Quoted Products') }}
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ __('Only items with inventory shortage are extracted for procurement.') }}
                                </p>
                            </div>
                            <span class="text-xs font-mono font-bold text-slate-500">
                                {{ __('Count:') }} <span id="items-badge-count">{{ count($shortageItems) }}</span>
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs" id="shortage-table">
                                <thead class="bg-slate-50 dark:bg-slate-800/80 text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                    <tr>
                                        <th class="py-3 px-4">#</th>
                                        <th class="py-3 px-4 min-w-56">{{ __('Product / Quoted Item') }}</th>
                                        <th class="py-3 px-3 text-center">{{ __('Quoted Qty') }}</th>
                                        <th class="py-3 px-3 text-center">{{ __('Warehouse Stock') }}</th>
                                        <th class="py-3 px-3 min-w-28 text-center">{{ __('Order Qty') }}</th>
                                        <th class="py-3 px-3 min-w-28 text-right">{{ __('Unit Cost') }}</th>
                                        <th class="py-3 px-3 text-right">{{ __('Line Total') }}</th>
                                        <th class="py-3 px-4 min-w-44">{{ __('Supplier') }}</th>
                                        @if($isAdmin)
                                            <th class="py-3 px-3 text-center">{{ __('Action') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60" id="shortage-tbody">
                                    @foreach($shortageItems as $idx => $item)
                                        <tr class="shortage-row hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition" data-index="{{ $idx }}">
                                            <td class="py-3.5 px-4 font-mono text-slate-400 align-middle">
                                                <span class="row-num">{{ $loop->iteration }}</span>
                                                <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item['item_id'] }}">
                                                <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $item['product_id'] }}">
                                            </td>

                                            <td class="py-3.5 px-4 align-middle">
                                                <div class="font-bold text-slate-900 dark:text-white leading-snug">
                                                    {{ $item['item_name'] }}
                                                </div>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                                    <span class="font-mono">{{ $item['sku'] }}</span>
                                                    <span>&bull;</span>
                                                    <span>{{ $item['category'] }}</span>
                                                </div>
                                            </td>

                                            {{-- Highlight Quoted Quantity --}}
                                            <td class="py-3.5 px-3 text-center align-middle">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 font-mono shadow-2xs" title="{{ __('Quoted in Customer Proposal') }}">
                                                    {{ $item['quoted_quantity'] }} {{ $item['unit'] }}
                                                </span>
                                            </td>

                                            {{-- Warehouse Available Stock --}}
                                            <td class="py-3.5 px-3 text-center align-middle">
                                                @if($item['is_zero_stock'])
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-black bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900 font-mono" title="{{ __('Zero Stock: Out of Stock') }}">
                                                        <span>🚨</span>
                                                        <span>{{ __('0 Available') }}</span>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900 font-mono" title="{{ __('Low Stock: Deficit for this quote') }}">
                                                        <span>⚠️</span>
                                                        <span>{{ $item['stock_on_hand'] }} {{ $item['unit'] }}</span>
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Deficit Order Quantity --}}
                                            <td class="py-3.5 px-3 align-middle text-center">
                                                @if($isAdmin)
                                                    <div class="inline-flex items-center">
                                                        <input type="number" 
                                                               name="items[{{ $idx }}][quantity]" 
                                                               value="{{ $item['order_quantity'] }}" 
                                                               min="1" 
                                                               step="1" 
                                                               required
                                                               class="order-qty-input w-24 text-center rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-bold text-xs py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                                               title="{{ __('Edit purchase quantity') }}">
                                                    </div>
                                                @else
                                                    <span class="font-mono font-bold text-xs text-slate-800 dark:text-slate-100">
                                                        {{ $item['order_quantity'] }} {{ $item['unit'] }}
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Unit Cost --}}
                                            <td class="py-3.5 px-3 align-middle text-right">
                                                @if($isAdmin)
                                                    <div class="inline-flex items-center justify-end">
                                                        <input type="number" 
                                                               name="items[{{ $idx }}][unit_cost]" 
                                                               value="{{ $item['unit_cost'] }}" 
                                                               min="0" 
                                                               step="0.01" 
                                                               required
                                                               class="unit-cost-input w-28 text-right rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono text-xs py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                                               title="{{ __('Edit unit purchase cost') }}">
                                                    </div>
                                                @else
                                                    <span class="font-mono text-slate-700 dark:text-slate-300">
                                                        ₹{{ number_format((float)$item['unit_cost'], 2) }}
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Line Total --}}
                                            <td class="py-3.5 px-3 align-middle text-right font-mono font-bold text-slate-900 dark:text-white text-xs">
                                                <span class="line-total-display">₹{{ number_format((float)$item['total_cost'], 2) }}</span>
                                            </td>

                                            {{-- Supplier Selector --}}
                                            <td class="py-3.5 px-4 align-middle">
                                                @if($isAdmin)
                                                    <select name="items[{{ $idx }}][supplier_id]" 
                                                            required
                                                            class="supplier-select block w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs py-1.5 px-2.5 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                                        @foreach($suppliers as $sup)
                                                            <option value="{{ $sup->id }}" @selected($sup->id == $item['suggested_supplier_id'])>
                                                                {{ $sup->company_name ?: $sup->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    @php
                                                        $matchedSup = $suppliers->firstWhere('id', $item['suggested_supplier_id']);
                                                    @endphp
                                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                        {{ $matchedSup ? ($matchedSup->company_name ?: $matchedSup->name) : 'Default Supplier' }}
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Action: Delete / Remove Row (Admin Only) --}}
                                            @if($isAdmin)
                                                <td class="py-3.5 px-3 text-center align-middle">
                                                    <button type="button" 
                                                            class="btn-remove-row text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 text-xs font-bold px-2 py-1 rounded cursor-pointer transition hover:bg-rose-50 dark:hover:bg-rose-950/60"
                                                            title="{{ __('Remove from purchase list') }}">
                                                        {{ __('Remove') }}
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-slate-50 dark:bg-slate-800/90 font-bold border-t border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white">
                                    <tr>
                                        <td colspan="4" class="py-3 px-4 text-right uppercase tracking-wider">
                                            {{ __('Total Estimated Procurement:') }}
                                        </td>
                                        <td class="py-3 px-3 text-center font-mono font-black" id="total-units-footer">
                                            {{ $totalUnitsToOrder }} {{ __('Units') }}
                                        </td>
                                        <td></td>
                                        <td class="py-3 px-3 text-right font-mono font-black text-sm text-blue-600 dark:text-blue-400" id="total-cost-footer">
                                            ₹{{ number_format((float)$totalEstimatedCost, 2) }}
                                        </td>
                                        <td colspan="{{ $isAdmin ? 2 : 1 }}"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Admin Controls Bar & Proceed to Purchase Order --}}
                        <div class="p-5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 rounded-b-2xl">
                            @if($isAdmin)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                                    <div>
                                        <label for="expected_delivery_date" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('Expected Delivery Date') }}
                                        </label>
                                        <input type="date" 
                                               id="expected_delivery_date" 
                                               name="expected_delivery_date" 
                                               value="{{ now()->addDays(5)->format('Y-m-d') }}" 
                                               class="rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs px-3 py-2 w-full shadow-2xs">
                                    </div>
                                    <div>
                                        <label for="po_notes" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('Order Notes / Reference') }}
                                        </label>
                                        <input type="text" 
                                               id="po_notes" 
                                               name="notes" 
                                               placeholder="e.g. Urgent stock order for site installation" 
                                               class="rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs px-3 py-2 w-full shadow-2xs">
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ __('Generating Purchase Order will group items by supplier and open the PO with ready-to-print PDF.') }}
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('quotations.show', $quotation) }}" class="btn-secondary text-xs">
                                            {{ __('Return to Quotation') }}
                                        </a>

                                        <button type="submit" 
                                                id="btn-create-po"
                                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white shadow-md transition cursor-pointer hover:opacity-95 active:scale-[0.98]"
                                                style="background-color: #059669 !important; color: #ffffff !important;">
                                            <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                            <span style="color: #ffffff !important;">{{ __('Create Purchase Order & Open PDF') }}</span>
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-500">
                                        {{ __('Employee mode: Inform your administrator to finalize purchase orders.') }}
                                    </span>
                                    <a href="{{ route('quotations.show', $quotation) }}" class="btn-secondary text-xs">
                                        {{ __('Back to Quotation') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            @else
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-xl font-bold">
                        ✓
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                        {{ __('No Inventory Shortage Detected') }}
                    </h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        {{ __('All hardware and items quoted for this installation are fully available in warehouse stock. No zero-stock or low-stock orders are required.') }}
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('quotations.show', $quotation) }}" class="btn-primary text-xs">
                            {{ __('Return to Quotation') }}
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>

    @if($isAdmin)
    <script>
        function recalculateShortageTotals() {
            let totalCost = 0;
            let totalUnits = 0;
            const rows = document.querySelectorAll('.shortage-row');

            rows.forEach((row, idx) => {
                const qtyInput = row.querySelector('.order-qty-input');
                const costInput = row.querySelector('.unit-cost-input');
                const lineTotalEl = row.querySelector('.line-total-display');
                const rowNum = row.querySelector('.row-num');

                if (rowNum) rowNum.textContent = (idx + 1);

                const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
                const cost = parseFloat(costInput ? costInput.value : 0) || 0;
                const lineTotal = qty * cost;

                if (lineTotalEl) {
                    lineTotalEl.textContent = '₹' + lineTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                totalUnits += qty;
                totalCost += lineTotal;
            });

            const countBadge = document.getElementById('items-badge-count');
            if (countBadge) countBadge.textContent = rows.length;

            const headerCount = document.getElementById('header-shortage-count');
            if (headerCount) headerCount.textContent = rows.length + ' Items';

            const headerTotal = document.getElementById('header-estimated-total');
            if (headerTotal) headerTotal.textContent = '₹' + totalCost.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const unitsFooter = document.getElementById('total-units-footer');
            if (unitsFooter) unitsFooter.textContent = totalUnits + ' Units';

            const costFooter = document.getElementById('total-cost-footer');
            if (costFooter) costFooter.textContent = '₹' + totalCost.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const createBtn = document.getElementById('btn-create-po');
            if (createBtn) {
                if (rows.length === 0) {
                    createBtn.disabled = true;
                    createBtn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    createBtn.disabled = false;
                    createBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const tbody = document.getElementById('shortage-tbody');

            if (tbody) {
                tbody.addEventListener('input', (e) => {
                    if (e.target.classList.contains('order-qty-input') || e.target.classList.contains('unit-cost-input')) {
                        recalculateShortageTotals();
                    }
                });

                tbody.addEventListener('click', (e) => {
                    const btn = e.target.closest('.btn-remove-row');
                    if (!btn) return;

                    const row = btn.closest('.shortage-row');
                    if (!row) return;

                    row.style.transition = 'all 0.2s ease-out';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';

                    setTimeout(() => {
                        row.remove();
                        recalculateShortageTotals();

                        if (document.querySelectorAll('.shortage-row').length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="9" class="text-center py-8 text-xs text-slate-400 italic">
                                        All shortage items removed from order. No Purchase Order will be generated.
                                    </td>
                                </tr>
                            `;
                        }
                    }, 200);
                });
            }

            const form = document.getElementById('procurement-form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    const rows = document.querySelectorAll('.shortage-row');
                    if (rows.length === 0) {
                        e.preventDefault();
                        if (typeof crmAlert === 'function') {
                            crmAlert('Please keep at least one shortage item to generate a Purchase Order.');
                        } else {
                            alert('Please keep at least one shortage item to generate a Purchase Order.');
                        }
                        return;
                    }
                });
            }
        });
    </script>
    @endif
</x-app-layout>
