<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Purchase Orders</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('suppliers.index') }}" class="px-3.5 py-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    Suppliers
                </a>
                <a href="{{ route('purchase-orders.create') }}" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                    + New Purchase Order
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 1024px) {
            .stat-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        .stat-card {
            background: #ffffff;
            border-radius: 4px;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
        }
        html.dark .stat-card {
            background: #0f172a;
            border-color: #1e293b;
        }
        .stat-label { font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 0.25rem; }
        html.dark .stat-label { color: #94a3b8; }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: #0f172a; line-height: 1; }
        html.dark .stat-value { color: #ffffff; }

        .pg-card {
            background: #ffffff;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        html.dark .pg-card {
            background: #0f172a;
            border-color: #1e293b;
        }

        .filter-bar {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; flex-wrap: wrap;
            background: #ffffff;
        }
        html.dark .filter-bar {
            background: #0b1120;
            border-color: #1e293b;
        }
        .filter-input, .filter-select {
            border: 1px solid #cbd5e1; border-radius: 4px;
            height: 38px; padding: 0 0.75rem; font-size: 0.85rem; color: #0f172a;
            background: #ffffff; outline: none;
        }
        html.dark .filter-input, html.dark .filter-select {
            background: #0b1120; border-color: #334155; color: #ffffff;
        }
        .filter-input:focus, .filter-select:focus { border-color: #2563eb; }

        .p-table { width: 100%; border-collapse: collapse; }
        .p-table thead { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        html.dark .p-table thead { background: #0b1120; border-color: #1e293b; }
        .p-table thead th {
            padding: 0.75rem 1rem;
            font-size: 0.8rem; font-weight: 600; text-transform: none;
            color: #475569; text-align: left; white-space: nowrap;
        }
        html.dark .p-table thead th { color: #94a3b8; }
        .p-table thead th.right { text-align: right; }
        .p-table tbody tr { border-bottom: 1px solid #e2e8f0; background: #ffffff; }
        html.dark .p-table tbody tr { border-bottom-color: #1e293b; background: #0f172a; }
        .p-table tbody tr:last-child { border-bottom: none; }
        .p-table tbody tr:hover { background: #f8fafc; }
        html.dark .p-table tbody tr:hover { background: #1e293b; }
        .p-table tbody td { padding: 0.75rem 1rem; vertical-align: middle; font-size: 0.85rem; color: #475569; }
        html.dark .p-table tbody td { color: #cbd5e1; }
        .p-table tbody td.right { text-align: right; }

        /* Status: Plain bold colored text */
        .badge-status { font-size: 0.85rem; font-weight: 700; background: transparent !important; border: none !important; padding: 0 !important; }
        .status-draft              { color: #64748b !important; }
        .status-ordered            { color: #2563eb !important; }
        .status-partially_received { color: #d97706 !important; }
        .status-received           { color: #16a34a !important; }
        .status-cancelled          { color: #dc2626 !important; }

        .badge-payment { font-size: 0.85rem; font-weight: 700; background: transparent !important; border: none !important; padding: 0 !important; }
        .pay-unpaid         { color: #dc2626 !important; }
        .pay-partially_paid { color: #d97706 !important; }
        .pay-paid           { color: #16a34a !important; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Stat Cards --}}
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value">{{ $totalOrders ?? $totalPOs ?? 0 }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Awaiting Delivery</div>
                    <div class="stat-value">{{ $pendingDeliveryCount ?? $pendingPOs ?? 0 }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Total Spend</div>
                    <div class="stat-value">₹{{ number_format($totalSpend ?? 0, 0) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Accounts Payable</div>
                    <div class="stat-value">₹{{ number_format($unpaidBalance ?? $unpaidAmount ?? 0, 0) }}</div>
                </div>
            </div>

            <div class="pg-card">
                {{-- Filter Bar --}}
                <form method="GET" action="{{ route('purchase-orders.index') }}" class="filter-bar">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search PO #, supplier..." class="filter-input" style="min-width: 220px;" />
                    
                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="ordered" {{ request('status') === 'ordered' ? 'selected' : '' }}>Ordered</option>
                        <option value="partially_received" {{ request('status') === 'partially_received' ? 'selected' : '' }}>Partially Received</option>
                        <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>

                    <select name="payment_status" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Payments</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partially_paid" {{ request('payment_status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>

                    <button type="submit" class="h-[38px] px-4 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'payment_status', 'supplier_id']))
                        <a href="{{ route('purchase-orders.index') }}" class="h-[38px] px-3.5 inline-flex items-center rounded border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium transition">Clear</a>
                    @endif
                </form>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="p-table">
                        <thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Supplier</th>
                                <th>Order Date</th>
                                <th>Receiving Progress</th>
                                <th class="right">Total Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th class="right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrders as $po)
                            <tr>
                                <td>
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="font-mono font-semibold text-blue-600 hover:underline">
                                        {{ $po->po_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ $po->supplier->name }}</div>
                                    @if($po->supplier->phone)
                                        <div class="text-xs text-slate-500">{{ $po->supplier->phone }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-xs text-slate-700 dark:text-slate-300">{{ $po->order_date->format('d M Y') }}</div>
                                </td>
                                <td>
                                    @php
                                        $totOrd = $po->items->sum('quantity_ordered');
                                        $totRec = $po->items->sum('quantity_received');
                                        $pct = $totOrd > 0 ? min(100, round(($totRec / $totOrd) * 100)) : 0;
                                    @endphp
                                    <div class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $totRec }} / {{ $totOrd }} Units</div>
                                    <div class="w-24 bg-slate-200 dark:bg-slate-700 rounded h-1.5 mt-1 overflow-hidden">
                                        <div class="bg-blue-600 h-1.5" style="width: {{ $pct }}%"></div>
                                    </div>
                                </td>
                                <td class="right">
                                    <div class="font-semibold text-slate-900 dark:text-white">₹{{ number_format($po->total, 2) }}</div>
                                    @if($po->balanceDue() > 0)
                                        <div class="text-xs text-red-600">Due: ₹{{ number_format($po->balanceDue(), 2) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge-status status-{{ $po->status }}">
                                        {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-payment pay-{{ $po->payment_status }}">
                                        {{ ucfirst(str_replace('_', ' ', $po->payment_status)) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap text-right text-sm" style="text-align:right;">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <a href="{{ route('purchase-orders.show', $po) }}" class="text-blue-600 hover:underline">View</a>
                                        @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                            <span class="text-slate-300">|</span>
                                            <a href="{{ route('purchase-orders.edit', $po) }}" class="text-blue-600 hover:underline">Edit</a>
                                        @endif
                                        @if(auth()->user()->isAdmin())
                                            <span class="text-slate-300">|</span>
                                            <form method="POST" action="{{ route('purchase-orders.destroy', $po) }}" class="inline" onsubmit="return confirm('Delete purchase order {{ addslashes($po->po_number) }}? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline cursor-pointer bg-transparent border-0 p-0 font-medium">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-sm text-slate-500 font-medium">
                                    No purchase orders found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($purchaseOrders->hasPages())
                <div class="p-3 border-t border-slate-200 dark:border-slate-800">
                    {{ $purchaseOrders->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
