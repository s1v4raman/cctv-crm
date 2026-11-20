<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Vendor Bills & 3-Way Matching</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Supplier invoices, 3-way match validation against POs and Goods Receipts, and disbursement ledger.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('finance.payables.index') }}" class="btn-secondary">
                    Payables & Aging
                </a>
                <a href="{{ route('purchase-orders.index') }}" class="btn-secondary">
                    Purchase Orders
                </a>
                <a href="{{ route('vendor-bills.create') }}" class="btn-primary">
                    <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Record Vendor Bill
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

        @if (session('warning'))
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-sm flex items-center justify-between">
                <span>{{ session('warning') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-amber-600 hover:text-amber-900">&times;</button>
            </div>
        @endif

        {{-- Filters & Search --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
            <form method="GET" action="{{ route('vendor-bills.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Bill #, Inv #, PO #, Supplier..." class="crm-input w-full text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Supplier</label>
                    <select name="supplier_id" class="crm-input w-full text-xs">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->company_name ?: $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Match Status</label>
                    <select name="match_status" class="crm-input w-full text-xs">
                        <option value="">All Match Statuses</option>
                        <option value="matched" {{ request('match_status') == 'matched' ? 'selected' : '' }}>Matched (Verified)</option>
                        <option value="mismatch" {{ request('match_status') == 'mismatch' ? 'selected' : '' }}>Mismatch (Discrepancy)</option>
                        <option value="not_checked" {{ request('match_status') == 'not_checked' ? 'selected' : '' }}>Not Checked</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Bill Status</label>
                    <select name="status" class="crm-input w-full text-xs">
                        <option value="">All Statuses</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved (Ready)</option>
                        <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="crm-input w-full text-xs">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary w-full justify-center text-xs">Filter</button>
                    @if(request()->anyFilled(['search', 'supplier_id', 'match_status', 'status', 'date_from']))
                        <a href="{{ route('vendor-bills.index') }}" class="btn-secondary justify-center text-xs">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Bills Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="crm-table w-full">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-xs text-slate-500 font-semibold">
                            <th class="py-3 px-4 text-left">Bill Number</th>
                            <th class="py-3 px-4 text-left">Vendor Invoice #</th>
                            <th class="py-3 px-4 text-left">Supplier</th>
                            <th class="py-3 px-4 text-left">PO Reference</th>
                            <th class="py-3 px-4 text-left">Due Date</th>
                            <th class="py-3 px-4 text-center">3-Way Match</th>
                            <th class="py-3 px-4 text-right">Bill Total</th>
                            <th class="py-3 px-4 text-right">Balance Due</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                        @forelse($bills as $bill)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition">
                                <td class="py-3 px-4 font-mono font-bold">
                                    <a href="{{ route('vendor-bills.show', $bill) }}" class="text-blue-600 hover:underline">
                                        {{ $bill->bill_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 font-mono text-xs text-slate-800 dark:text-slate-200">
                                    {{ $bill->vendor_invoice_no }}
                                    @if($bill->invoice_file_path)
                                        <a href="{{ route('vendor-bills.invoice-file', $bill) }}" target="_blank" title="Download Invoice Attachment" class="text-slate-400 hover:text-blue-600 ml-1">
                                            📎
                                        </a>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs font-semibold">
                                    {{ $bill->supplier?->company_name ?: ($bill->supplier?->name ?? '-') }}
                                </td>
                                <td class="py-3 px-4 font-mono text-xs">
                                    @if($bill->purchaseOrder)
                                        <a href="{{ route('purchase-orders.show', $bill->purchaseOrder) }}" class="text-slate-700 dark:text-slate-300 hover:underline">
                                            {{ $bill->purchaseOrder->po_number }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs whitespace-nowrap {{ $bill->isOverdue() ? 'text-rose-600 font-bold' : 'text-slate-600 dark:text-slate-400' }}">
                                    {{ $bill->due_date?->format('d M Y') }}
                                    @if($bill->isOverdue())
                                        <div class="text-[10px] text-rose-500 font-normal">({{ $bill->daysOverdue() }}d overdue)</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if($bill->match_status === 'matched')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                                            ✓ Matched
                                        </span>
                                    @elseif($bill->match_status === 'mismatch')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200 animate-pulse">
                                            ⚠ Mismatch
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                            Not Checked
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                    ₹{{ number_format((float) $bill->total, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold {{ $bill->balanceDue() > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    ₹{{ number_format((float) $bill->balanceDue(), 2) }}
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if($bill->status === 'paid')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                                            Paid
                                        </span>
                                    @elseif($bill->status === 'partially_paid')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">
                                            Partially Paid
                                        </span>
                                    @elseif($bill->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300">
                                            Approved
                                        </span>
                                    @elseif($bill->status === 'on_hold')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">
                                            On Hold
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                            {{ ucfirst($bill->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <a href="{{ route('vendor-bills.show', $bill) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-12 text-center text-slate-400">
                                    No vendor bills found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bills->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $bills->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
