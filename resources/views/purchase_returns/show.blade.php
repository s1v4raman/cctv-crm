<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchase-returns.index') }}" class="btn-secondary text-xs">
                    &larr; Returns
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">
                            Purchase Return {{ $purchaseReturn->return_number }}
                        </h2>
                        @if($purchaseReturn->status === 'confirmed')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                Confirmed
                            </span>
                        @elseif($purchaseReturn->status === 'cancelled')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400">
                                Cancelled
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">
                                Draft
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Created on {{ $purchaseReturn->created_at?->format('d M Y, h:i A') }} by {{ $purchaseReturn->createdBy?->name ?? 'System' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if($purchaseReturn->status === 'draft' && auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('purchase-returns.confirm', $purchaseReturn) }}" onsubmit="return confirm('Confirm this return? This will deduct warehouse stock and generate a supplier debit note.')">
                        @csrf
                        <button type="submit" class="btn-primary">
                            Confirm Return & Issue Debit Note &rarr;
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
            </div>
        @endif

        {{-- Overview Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Supplier / Vendor</div>
                <div class="font-bold text-slate-900 dark:text-white text-base">
                    {{ $purchaseReturn->supplier?->company_name ?: $purchaseReturn->supplier?->name }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    GSTIN: {{ $purchaseReturn->supplier?->gst_number ?? '—' }}<br>
                    State: {{ $purchaseReturn->supplier?->state ?? 'Tamil Nadu' }}
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Receipt & Order Reference</div>
                <div class="text-xs space-y-1 mt-1 text-slate-700 dark:text-slate-300">
                    <div>
                        GRN:
                        @if($purchaseReturn->goodsReceipt)
                            <a href="{{ route('goods-receipts.show', $purchaseReturn->goodsReceipt) }}" class="font-semibold text-brand-600 hover:underline">
                                {{ $purchaseReturn->goodsReceipt->grn_number }}
                            </a>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </div>
                    <div>
                        Purchase Order:
                        @if($purchaseReturn->purchaseOrder)
                            <a href="{{ route('purchase-orders.show', $purchaseReturn->purchaseOrder) }}" class="font-semibold text-brand-600 hover:underline">
                                {{ $purchaseReturn->purchaseOrder->po_number }}
                            </a>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </div>
                    <div>Return Date: <strong>{{ $purchaseReturn->return_date?->format('d M Y') }}</strong></div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Debit Note Status</div>
                @if($purchaseReturn->debit_note_number)
                    <div class="inline-flex items-center px-2.5 py-1 rounded-lg font-mono font-bold text-sm bg-amber-50 text-amber-900 dark:bg-amber-900/30 dark:text-amber-200 border border-amber-200 dark:border-amber-800 mt-1">
                        {{ $purchaseReturn->debit_note_number }}
                    </div>
                    <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium mt-1.5 flex items-center gap-1">
                        &check; Active (deducted from balance payable)
                    </div>
                @else
                    <div class="text-xs text-slate-400 italic mt-2">
                        Debit note will be automatically issued upon confirmation.
                    </div>
                @endif
            </div>
        </div>

        {{-- Reason / Notes --}}
        @if($purchaseReturn->reason || $purchaseReturn->notes)
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm text-xs space-y-1">
                @if($purchaseReturn->reason)
                    <div><span class="font-semibold text-slate-600 dark:text-slate-400">Reason:</span> {{ $purchaseReturn->reason }}</div>
                @endif
                @if($purchaseReturn->notes)
                    <div><span class="font-semibold text-slate-600 dark:text-slate-400">Notes:</span> {{ $purchaseReturn->notes }}</div>
                @endif
            </div>
        @endif

        {{-- Returned Items --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Returned Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/80 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Product Name</th>
                            <th class="px-4 py-3 text-center">Returned Qty</th>
                            <th class="px-4 py-3 text-right">Unit Rate</th>
                            <th class="px-4 py-3 text-right">Tax Rate</th>
                            <th class="px-4 py-3 text-right">Tax (₹)</th>
                            <th class="px-4 py-3 text-right">Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($purchaseReturn->items as $idx => $item)
                            <tr>
                                <td class="px-4 py-3 text-xs text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ $item->product?->name }}</div>
                                    @if($item->reason)
                                        <div class="text-xs text-slate-500 mt-0.5">Note: {{ $item->reason }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-bold text-slate-900 dark:text-white">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono">
                                    ₹ {{ number_format($item->unit_cost, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs text-slate-500">
                                    {{ (float) $item->tax_rate }}%
                                </td>
                                <td class="px-4 py-3 text-right font-mono">
                                    ₹ {{ number_format($item->tax_amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900 dark:text-white">
                                    ₹ {{ number_format($item->total_amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40 font-semibold text-slate-900 dark:text-white">
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right">Subtotal:</td>
                            <td colspan="2" class="px-4 py-2 text-right font-mono">₹ {{ number_format($purchaseReturn->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right">GST Total:</td>
                            <td colspan="2" class="px-4 py-2 text-right font-mono">₹ {{ number_format($purchaseReturn->tax_amount, 2) }}</td>
                        </tr>
                        <tr class="text-base text-brand-600 dark:text-brand-400">
                            <td colspan="5" class="px-4 py-3 text-right">Total Debit Note Amount:</td>
                            <td colspan="2" class="px-4 py-3 text-right font-mono font-bold">₹ {{ number_format($purchaseReturn->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
