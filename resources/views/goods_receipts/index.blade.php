<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Goods Receipt Notes (GRN)</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Central warehouse receiving log, verification records, and item serial number registrations.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('purchase-orders.index') }}" class="btn-secondary">
                    Purchase Orders
                </a>
                <a href="{{ route('inventory.ledger') }}" class="btn-secondary">
                    Stock Ledger
                </a>
                <a href="{{ route('inventory.index') }}" class="btn-primary">
                    Inventory Stock
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        {{-- Filters & Search --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
            <form method="GET" action="{{ route('goods-receipts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="GRN, PO, DC #, Supplier..." class="crm-input w-full">
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
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="crm-input w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="crm-input w-full">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary w-full justify-center">Filter</button>
                    @if(request()->anyFilled(['search', 'supplier_id', 'date_from', 'date_to']))
                        <a href="{{ route('goods-receipts.index') }}" class="btn-secondary justify-center">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Receipts Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="crm-table w-full">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-xs text-slate-500 font-semibold">
                            <th class="py-3 px-4 text-left">GRN Number</th>
                            <th class="py-3 px-4 text-left">PO Reference</th>
                            <th class="py-3 px-4 text-left">Supplier</th>
                            <th class="py-3 px-4 text-left">Received Date</th>
                            <th class="py-3 px-4 text-left">Delivery Note #</th>
                            <th class="py-3 px-4 text-left">Received By</th>
                            <th class="py-3 px-4 text-center">Accepted</th>
                            <th class="py-3 px-4 text-center">Rejected</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                        @forelse($goodsReceipts as $receipt)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                    <a href="{{ route('goods-receipts.show', $receipt) }}" class="text-blue-600 hover:underline">
                                        {{ $receipt->grn_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 font-mono">
                                    @if($receipt->purchaseOrder)
                                        <a href="{{ route('purchase-orders.show', $receipt->purchaseOrder) }}" class="text-slate-700 dark:text-slate-300 hover:underline">
                                            {{ $receipt->purchaseOrder->po_number }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    {{ $receipt->supplier?->company_name ?: ($receipt->supplier?->name ?? '-') }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $receipt->received_date?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="py-3 px-4 font-mono text-xs text-slate-600 dark:text-slate-400">
                                    {{ $receipt->delivery_note_no ?: '-' }}
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-400">
                                    {{ $receipt->receivedBy?->name ?? 'System' }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $receipt->totalReceivedQty() }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold {{ $receipt->totalRejectedQty() > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                                    {{ $receipt->totalRejectedQty() }}
                                </td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <a href="{{ route('goods-receipts.show', $receipt) }}" class="text-xs font-semibold text-slate-600 dark:text-slate-300 hover:underline">
                                        View
                                    </a>
                                    <a href="{{ route('goods-receipts.pdf', $receipt) }}" target="_blank" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                        PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                    No goods receipts found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($goodsReceipts->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $goodsReceipts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
