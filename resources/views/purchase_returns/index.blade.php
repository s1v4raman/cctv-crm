<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Purchase Returns & Debit Notes</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Return items against Goods Receipt Notes, deduct warehouse stock, and issue vendor debit notes.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('goods-receipts.index') }}" class="btn-secondary">
                    Goods Receipts
                </a>
                <a href="{{ route('purchase-orders.index') }}" class="btn-secondary">
                    Purchase Orders
                </a>
                <a href="{{ route('purchase-returns.create') }}" class="btn-primary">
                    + New Purchase Return
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
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

        {{-- Filters & Search --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
            <form method="GET" action="{{ route('purchase-returns.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Return #, Debit Note #, GRN..." class="crm-input w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Supplier</label>
                    <select name="supplier_id" class="crm-input w-full">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->company_name ?: $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Status</label>
                    <select name="status" class="crm-input w-full">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary w-full justify-center">Filter</button>
                    @if(request()->anyFilled(['search', 'supplier_id', 'status']))
                        <a href="{{ route('purchase-returns.index') }}" class="btn-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Return #</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Supplier</th>
                            <th class="px-4 py-3">Reference GRN & PO</th>
                            <th class="px-4 py-3">Debit Note #</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($purchaseReturns as $ret)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="px-4 py-3 font-semibold text-brand-600 dark:text-brand-400">
                                    <a href="{{ route('purchase-returns.show', $ret) }}" class="hover:underline">
                                        {{ $ret->return_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                                    {{ $ret->return_date?->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-slate-900 dark:text-white">
                                        {{ $ret->supplier?->company_name ?: $ret->supplier?->name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    @if($ret->goodsReceipt)
                                        <div class="font-medium text-slate-800 dark:text-slate-200">
                                            GRN: <a href="{{ route('goods-receipts.show', $ret->goodsReceipt) }}" class="text-brand-600 hover:underline">{{ $ret->goodsReceipt->grn_number }}</a>
                                        </div>
                                    @endif
                                    @if($ret->purchaseOrder)
                                        <div class="text-slate-400">
                                            PO: <a href="{{ route('purchase-orders.show', $ret->purchaseOrder) }}" class="hover:underline">{{ $ret->purchaseOrder->po_number }}</a>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    @if($ret->debit_note_number)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded font-mono font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                            {{ $ret->debit_note_number }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                                    ₹ {{ number_format($ret->total_amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($ret->status === 'confirmed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            Confirmed
                                        </span>
                                    @elseif($ret->status === 'cancelled')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400">
                                            Cancelled
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">
                                            Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('purchase-returns.show', $ret) }}" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                                        View &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-slate-400 text-sm">
                                    No purchase returns recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($purchaseReturns->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
                    {{ $purchaseReturns->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
