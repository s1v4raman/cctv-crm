<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">
                        Goods Receipt Note: {{ $goodsReceipt->grn_number }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                        Inwarded
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Received on {{ $goodsReceipt->received_date?->format('d M Y') }} by {{ $goodsReceipt->receivedBy?->name ?? 'System' }}
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('goods-receipts.index') }}" class="btn-secondary">
                    &larr; Back to GRN List
                </a>
                @if($goodsReceipt->purchaseOrder)
                    <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}" class="btn-secondary">
                        View PO #{{ $goodsReceipt->purchaseOrder->po_number }}
                    </a>
                @endif
                <a href="{{ route('goods-receipts.pdf', $goodsReceipt) }}" target="_blank" class="btn-primary">
                    <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Print / PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm">
                {{ session('status') }}
            </div>
        @endif

        {{-- Meta Information Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Receipt Metadata</div>
                <div class="space-y-1.5 text-sm">
                    <div><span class="text-slate-500">GRN Number:</span> <strong class="font-mono text-slate-900 dark:text-white">{{ $goodsReceipt->grn_number }}</strong></div>
                    <div><span class="text-slate-500">PO Number:</span> <strong class="font-mono text-slate-900 dark:text-white">{{ $goodsReceipt->purchaseOrder?->po_number ?? '-' }}</strong></div>
                    <div><span class="text-slate-500">Delivery Note / DC:</span> <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $goodsReceipt->delivery_note_no ?: 'None' }}</span></div>
                    <div><span class="text-slate-500">Received Date:</span> <span>{{ $goodsReceipt->received_date?->format('d F Y') }}</span></div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Supplier Details</div>
                <div class="space-y-1.5 text-sm">
                    <div class="font-bold text-slate-900 dark:text-white">{{ $goodsReceipt->supplier?->company_name ?: ($goodsReceipt->supplier?->name ?? 'Vendor') }}</div>
                    @if($goodsReceipt->supplier?->gst_number)
                        <div class="text-xs"><span class="text-slate-500">GSTIN:</span> <strong class="font-mono">{{ $goodsReceipt->supplier->gst_number }}</strong></div>
                    @endif
                    @if($goodsReceipt->supplier?->phone)
                        <div class="text-xs"><span class="text-slate-500">Phone:</span> {{ $goodsReceipt->supplier->phone }}</div>
                    @endif
                    <div class="text-xs text-slate-500">{{ $goodsReceipt->supplier?->address }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Warehouse Verification</div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Accepted Units:</span>
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $goodsReceipt->totalReceivedQty() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Rejected Units:</span>
                        <span class="text-lg font-bold {{ $goodsReceipt->totalRejectedQty() > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">{{ $goodsReceipt->totalRejectedQty() }}</span>
                    </div>
                    @if($goodsReceipt->notes)
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400">
                            <strong>Notes:</strong> {{ $goodsReceipt->notes }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-slate-900 dark:text-white">Inwarded Line Items</h3>
                <span class="text-xs text-slate-500">{{ $goodsReceipt->items->count() }} item line(s)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="crm-table w-full">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-xs text-slate-500 font-semibold">
                            <th class="py-3 px-4 text-left">#</th>
                            <th class="py-3 px-4 text-left">Item Description</th>
                            <th class="py-3 px-4 text-left">SKU</th>
                            <th class="py-3 px-4 text-center">Accepted</th>
                            <th class="py-3 px-4 text-center">Rejected</th>
                            @if(auth()->user()->isOffice())
                                <th class="py-3 px-4 text-right">Unit Rate</th>
                                <th class="py-3 px-4 text-right">Taxable Value</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                        @foreach($goodsReceipt->items as $index => $item)
                            <tr class="align-top">
                                <td class="py-3 px-4 text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">
                                        {{ $item->product?->name ?: ($item->purchaseOrderItem?->item_name ?? 'Item') }}
                                    </div>
                                    @if($item->qty_rejected > 0 && $item->reject_reason)
                                        <div class="text-xs text-rose-600 dark:text-rose-400 mt-1">
                                            <strong>Rejection reason:</strong> {{ $item->reject_reason }}
                                        </div>
                                    @endif

                                    {{-- Serial Numbers Display --}}
                                    @if($item->serials->isNotEmpty())
                                        <div class="mt-2 p-2 bg-slate-50 dark:bg-slate-800/60 rounded border border-slate-200 dark:border-slate-700 text-xs">
                                            <div class="font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Registered Serial Numbers ({{ $item->serials->count() }} units):
                                            </div>
                                            <div class="flex flex-wrap gap-1.5 font-mono">
                                                @foreach($item->serials as $s)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                        {{ $s->serial_no }}
                                                        @if($s->mac_address)
                                                            <span class="text-slate-400 text-[10px] ml-1">(MAC: {{ $s->mac_address }})</span>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-xs text-slate-600 dark:text-slate-400">
                                    {{ $item->product?->sku ?: ($item->purchaseOrderItem?->sku ?? '-') }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $item->qty_received }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold {{ $item->qty_rejected > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                                    {{ $item->qty_rejected }}
                                </td>
                                @if(auth()->user()->isOffice())
                                    <td class="py-3 px-4 text-right font-mono">
                                        ₹{{ number_format((float) $item->unit_cost, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                        ₹{{ number_format((float) ($item->qty_received * $item->unit_cost), 2) }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
