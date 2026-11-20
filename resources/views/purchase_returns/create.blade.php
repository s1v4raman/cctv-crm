<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Record Purchase Return</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Return accepted items from a Goods Receipt Note back to the supplier and issue a debit note.</p>
            </div>
            <div>
                <a href="{{ route('purchase-returns.index') }}" class="btn-secondary">
                    &larr; Back to Returns
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm">
                <p class="font-semibold mb-1">Please correct the following errors:</p>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Select GRN Form if none selected --}}
        @if(!$selectedReceipt)
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-2">Step 1: Select Goods Receipt Note (GRN)</h3>
                <p class="text-xs text-slate-500 mb-4">Choose the inward receipt against which items are being returned to the vendor.</p>

                <form method="GET" action="{{ route('purchase-returns.create') }}" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <select name="goods_receipt_id" class="crm-input w-full" required>
                            <option value="">-- Choose Goods Receipt Note --</option>
                            @foreach($goodsReceipts as $grn)
                                <option value="{{ $grn->id }}">
                                    {{ $grn->grn_number }} — {{ $grn->supplier?->company_name ?: $grn->supplier?->name }} (PO: {{ $grn->purchaseOrder?->po_number }}, Received: {{ $grn->received_date?->format('d M Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-primary justify-center">
                        Select Receipt &rarr;
                    </button>
                </form>
            </div>
        @else
            {{-- GRN Summary Card --}}
            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700/60 p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Returning against Receipt:</div>
                        <div class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                            <span>{{ $selectedReceipt->grn_number }}</span>
                            <span class="text-xs font-normal text-slate-500">(Received: {{ $selectedReceipt->received_date?->format('d M Y') }})</span>
                        </div>
                        <div class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                            Supplier: <strong class="text-slate-900 dark:text-white">{{ $selectedReceipt->supplier?->company_name ?: $selectedReceipt->supplier?->name }}</strong>
                            &bull; PO #: <strong class="text-slate-900 dark:text-white">{{ $selectedReceipt->purchaseOrder?->po_number }}</strong>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('purchase-returns.create') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">
                            Change Receipt &rarr;
                        </a>
                    </div>
                </div>
            </div>

            {{-- Return Creation Form --}}
            <form method="POST" action="{{ route('purchase-returns.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="goods_receipt_id" value="{{ $selectedReceipt->id }}">

                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Return Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="return_date" value="{{ old('return_date', now()->format('Y-m-d')) }}" required class="crm-input w-full">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Primary Reason for Return</label>
                            <input type="text" name="reason" value="{{ old('reason') }}" placeholder="e.g. Defective units, incorrect model, transit damage" class="crm-input w-full">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Internal Notes</label>
                        <textarea name="notes" rows="2" placeholder="Any additional return authorization details, courier tracking, RMA reference..." class="crm-input w-full">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Items to Return</h3>
                        <p class="text-xs text-slate-500">Specify return quantity (must be &le; received quantity). Set to 0 if not returning that item.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-4 py-3">Product</th>
                                    <th class="px-4 py-3 text-center">Qty Received</th>
                                    <th class="px-4 py-3 text-center">Warehouse Stock</th>
                                    <th class="px-4 py-3 w-32 text-center">Return Qty</th>
                                    <th class="px-4 py-3 w-32 text-right">Unit Rate (₹)</th>
                                    <th class="px-4 py-3">Item Return Note</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($selectedReceipt->items as $idx => $item)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="hidden" name="items[{{ $idx }}][goods_receipt_item_id]" value="{{ $item->id }}">
                                            <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $item->product_id }}">
                                            <div class="font-semibold text-slate-900 dark:text-white">{{ $item->product?->name }}</div>
                                            <div class="text-xs text-slate-400">SKU: {{ $item->product?->sku ?? '—' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-mono font-medium text-slate-700 dark:text-slate-300">
                                            {{ $item->qty_received }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-mono text-xs text-slate-500">
                                            {{ (int) $item->product?->stock_quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="number"
                                                   name="items[{{ $idx }}][quantity]"
                                                   min="0"
                                                   max="{{ $item->qty_received }}"
                                                   value="{{ old('items.'.$idx.'.quantity', 0) }}"
                                                   class="crm-input w-24 text-center font-semibold">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   name="items[{{ $idx }}][unit_cost]"
                                                   value="{{ old('items.'.$idx.'.unit_cost', $item->unit_cost) }}"
                                                   class="crm-input w-28 text-right font-mono">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   name="items[{{ $idx }}][reason]"
                                                   value="{{ old('items.'.$idx.'.reason') }}"
                                                   placeholder="Specific defect or reason"
                                                   class="crm-input w-full text-xs">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('purchase-returns.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" name="action" value="draft" class="btn-secondary">
                        Save as Draft
                    </button>
                    @if(auth()->user()->isAdmin())
                        <button type="submit" name="action" value="confirm" class="btn-primary">
                            Save & Confirm Return Now &rarr;
                        </button>
                    @endif
                </div>
            </form>
        @endif
    </div>
</x-app-layout>
