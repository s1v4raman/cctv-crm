<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_10px_#f59e0b]"></span>
                    Purchase Orders & Vendor Procurement
                </h2>
                <p class="mt-1 text-sm text-slate-400">Manage hardware POs, warehouse stock inwarding, vendor invoices & accounts payable</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('suppliers.index') }}" class="btn-head-secondary">
                    🏢 Suppliers
                </a>
                <a href="{{ route('purchase-orders.create') }}" class="btn-amber">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    New Purchase Order
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        /* .pg-wrap uses global app.css */
        /* .pg-inner uses global app.css */

        .btn-amber {
            display: inline-flex; align-items: center;
            background-color: var(--crm-accent, #2563eb); color: #ffffff !important;
            padding: 0.55rem 1.15rem; border-radius: 0.65rem;
            font-size: 0.85rem; font-weight: 700; text-decoration: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease-in-out;
        }
        .btn-amber:hover { background-color: var(--crm-accent-hover, #1d4ed8); color: #ffffff !important; }

        .btn-head-secondary {
            display: inline-flex; align-items: center;
            background-color: #0f172a; color: #cbd5e1 !important;
            padding: 0.55rem 1rem; border-radius: 0.65rem;
            font-size: 0.85rem; font-weight: 600; text-decoration: none;
            border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 1px 2px rgba(0,0,0,0.5);
            transition: all 0.15s ease-in-out;
        }
        .btn-head-secondary:hover { background-color: #1e293b; color: #ffffff !important; border-color: rgba(255,255,255,0.2); }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }
        @media (min-width: 640px) {
            .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .stat-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        .stat-card {
            background: #0f172a;
            border-radius: 1rem;
            padding: 1.25rem;
            border: 1px solid rgba(255,255,255,0.07);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 0.25rem; }
        .stat-value { font-size: 1.5rem; font-weight: 800; color: #ffffff; font-family:'Outfit',sans-serif; }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 0.75rem;
            display: flex; align-items: center; justify-content: center;
        }

        .pg-card {
            background:#0f172a;
            border-radius:1rem;
            border:1px solid rgba(255,255,255,0.07);
            box-shadow:0 10px 25px -5px rgba(0,0,0,0.5);
            overflow:hidden;
        }

        .filter-bar {
            display:flex; align-items:center; gap:.75rem;
            padding:1rem 1.25rem; border-bottom:1px solid rgba(255,255,255,0.06); flex-wrap:wrap;
            background:#0b1120;
        }
        .filter-input, .filter-select {
            border:1px solid #334155; border-radius:.5rem;
            padding:.5rem .85rem; font-size:.85rem; color:#f8fafc;
            outline:none; transition: all .15s;
        }
        .filter-input:focus, .filter-select:focus { border-color: var(--crm-accent, #2563eb); }

        .btn-filter {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: .5rem 1.1rem; border-radius: .5rem;
            background-color: var(--crm-accent, #2563eb); color: #ffffff !important;
            font-size: .85rem; font-weight: 700;
            border: none; cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all .15s ease-in-out;
        }
        .btn-filter:hover { background-color: var(--crm-accent-hover, #1d4ed8); }

        .btn-clear {
            display: inline-flex; align-items: center;
            font-size: .85rem; color: #94a3b8 !important; text-decoration: none; font-weight: 600;
            padding: .5rem .75rem; border-radius: .5rem; border: 1px solid rgba(255,255,255,0.1); background: #0f172a;
            transition: all .15s;
        }
        .btn-clear:hover { background: #1e293b; color: #ffffff !important; }

        .p-table { width:100%; border-collapse:collapse; }
        .p-table thead { background:#0b1120; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .p-table thead th {
            padding:.85rem 1.25rem;
            font-size:.72rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.05em; color:#94a3b8; text-align:left; white-space:nowrap;
        }
        .p-table thead th.right { text-align:right; }
        .p-table tbody tr { border-bottom:1px solid rgba(255,255,255,0.04); transition:background .12s; }
        .p-table tbody tr:last-child { border-bottom:none; }
        .p-table tbody tr:hover { background:rgba(30,41,59,0.5); }
        .p-table tbody td { padding:1rem 1.25rem; vertical-align:middle; font-size:.85rem; color:#cbd5e1; }
        .p-table tbody td.right { text-align:right; }

        .badge-status {
            display:inline-flex; padding:.25rem .65rem; border-radius:9999px;
            font-size:.72rem; font-weight:700; text-transform:capitalize;
        }
        .status-draft              { background:rgba(148,163,184,0.1); color:#94a3b8; border:1px solid rgba(148,163,184,0.2); }
        .status-ordered            { background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); }
        .status-partially_received { background:rgba(245,158,11,0.15); color:#fbbf24; border:1px solid rgba(245,158,11,0.3); }
        .status-received           { background:rgba(16,185,129,0.15); color:#34d399; border:1px solid rgba(16,185,129,0.3); }
        .status-cancelled          { background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3); }

        .badge-payment {
            display:inline-flex; padding:.2rem .55rem; border-radius:9999px;
            font-size:.7rem; font-weight:700; text-transform:capitalize;
        }
        .pay-unpaid         { background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3); }
        .pay-partially_paid { background:rgba(245,158,11,0.15); color:#fbbf24; border:1px solid rgba(245,158,11,0.3); }
        .pay-paid           { background:rgba(16,185,129,0.15); color:#34d399; border:1px solid rgba(16,185,129,0.3); }

        .btn-view {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.4rem 0.85rem; border-radius: 0.5rem;
            font-size: 0.78rem; font-weight: 700;
            background: rgba(56,189,248,0.1); color: #38bdf8 !important;
            border: 1px solid rgba(56,189,248,0.3); text-decoration: none;
            transition: all .15s;
        }
        .btn-view:hover { background: #38bdf8; color: #020617 !important; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/30 text-emerald-300 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Stat Cards --}}
            <div class="stat-grid">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total POs</div>
                        <div class="stat-value">{{ $totalOrders }}</div>
                        <span class="text-xs text-slate-400 font-medium">Orders created</span>
                    </div>
                    <div class="stat-icon bg-blue-500/10 text-blue-400 border border-blue-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Procurement</div>
                        <div class="stat-value text-amber-400">₹{{ number_format($totalSpend, 2) }}</div>
                        <span class="text-xs text-amber-500/80 font-medium">Committed hardware spend</span>
                    </div>
                    <div class="stat-icon bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Pending Deliveries</div>
                        <div class="stat-value text-sky-400">{{ $pendingDeliveryCount }}</div>
                        <span class="text-xs text-sky-400 font-medium">Awaiting warehouse inward</span>
                    </div>
                    <div class="stat-icon bg-sky-500/10 text-sky-400 border border-sky-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Unpaid Balance</div>
                        <div class="stat-value text-rose-400">₹{{ number_format($unpaidBalance, 2) }}</div>
                        <span class="text-xs text-rose-400/80 font-medium">Vendor accounts payable</span>
                    </div>
                    <div class="stat-icon bg-rose-500/10 text-rose-400 border border-rose-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="pg-card">
                {{-- Filter Bar --}}
                <form method="GET" action="{{ route('purchase-orders.index') }}" class="filter-bar">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search PO #, supplier, product item..." class="filter-input min-w-[240px]" />
                    
                    <select name="supplier_id" class="filter-select">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supp)
                            <option value="{{ $supp->id }}" @selected(request('supplier_id') == $supp->id)>{{ $supp->name }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="ordered" @selected(request('status') === 'ordered')>Ordered</option>
                        <option value="partially_received" @selected(request('status') === 'partially_received')>Partially Received</option>
                        <option value="received" @selected(request('status') === 'received')>Received</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>

                    <button type="submit" class="btn-filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'supplier_id', 'status']))
                        <a href="{{ route('purchase-orders.index') }}" class="btn-clear">Clear</a>
                    @endif
                </form>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="p-table">
                        <thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Supplier / Vendor</th>
                                <th>Order & Expected Date</th>
                                <th>Items & Receiving</th>
                                <th class="right">Total Amount</th>
                                <th>PO Status</th>
                                <th>Payment</th>
                                <th class="right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrders as $po)
                            <tr>
                                <td>
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="font-mono font-bold text-sky-400 hover:underline">
                                        {{ $po->po_number }}
                                    </a>
                                    <div class="text-[11px] text-slate-500 mt-0.5">By {{ $po->createdBy->name ?? 'System' }}</div>
                                </td>
                                <td>
                                    <div class="font-bold text-slate-200">{{ $po->supplier->name }}</div>
                                    @if($po->supplier->phone)
                                        <div class="text-xs text-slate-500">📞 {{ $po->supplier->phone }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-xs font-semibold text-slate-300">{{ $po->order_date->format('d M Y') }}</div>
                                    @if($po->expected_delivery_date)
                                        <div class="text-[11px] text-slate-500">Exp: {{ $po->expected_delivery_date->format('d M Y') }}</div>
                                    @else
                                        <div class="text-[11px] text-slate-500">Immediate</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $totOrd = $po->items->sum('quantity_ordered');
                                        $totRec = $po->items->sum('quantity_received');
                                    @endphp
                                    <div class="text-xs font-bold text-slate-200">{{ $totRec }} / {{ $totOrd }} Units Received</div>
                                    <div class="w-28 bg-slate-800 rounded-full h-1.5 mt-1 overflow-hidden border border-white/5">
                                        <div class="bg-amber-400 h-1.5 rounded-full shadow-[0_0_8px_#f59e0b]" style="width: {{ $totOrd > 0 ? min(100, round(($totRec / $totOrd) * 100)) : 0 }}%"></div>
                                    </div>
                                </td>
                                <td class="right">
                                    <div class="font-extrabold text-white font-mono">₹{{ number_format($po->total, 2) }}</div>
                                    @if($po->balanceDue() > 0)
                                        <div class="text-[11px] text-rose-400 font-semibold">Due: ₹{{ number_format($po->balanceDue(), 2) }}</div>
                                    @else
                                        <div class="text-[11px] text-emerald-400 font-semibold">Settled</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge-status status-{{ $po->status }}">
                                        {{ str_replace('_', ' ', $po->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-payment pay-{{ $po->payment_status }}">
                                        {{ str_replace('_', ' ', $po->payment_status) }}
                                    </span>
                                </td>
                                <td class="right">
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="btn-view">
                                        View & Inward &rarr;
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-sm text-slate-500 font-medium">
                                    No purchase orders found. Click "New Purchase Order" to create procurement orders.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($purchaseOrders->hasPages())
                <div class="p-4 border-t border-white/5">
                    {{ $purchaseOrders->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
