<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_10px_#f59e0b]"></span>
                    Suppliers & Hardware Distributors
                </h2>
                <p class="mt-1 text-sm text-slate-400">Manage CCTV vendors, procurement contacts, GSTIN numbers & purchase history</p>
            </div>
            <a href="{{ route('suppliers.create') }}" class="btn-amber">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Register Supplier
            </a>
        </div>
    </x-slot>

    <style>
        /* .pg-wrap uses global app.css */
        /* .pg-inner uses global app.css */

        .btn-amber {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background-color: var(--crm-accent, #2563eb); color: #ffffff !important;
            padding: 0.55rem 1.15rem; border-radius: 0.65rem;
            font-size: 0.85rem; font-weight: 700; text-decoration: none;
            box-shadow: 0 4px 14px var(--crm-accent-shadow, rgba(37, 99, 235, 0.3));
            transition: all 0.2s ease-in-out;
        }
        .btn-amber:hover { transform: translateY(-1px); box-shadow: 0 6px 20px var(--crm-accent-shadow, rgba(37, 99, 235, 0.45)); color: #ffffff !important; }
        .btn-amber * { color: #ffffff !important; }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }
        @media (min-width: 640px) {
            .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
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
        .filter-bar input {
            border:1px solid #334155; border-radius:.5rem;
            padding:.5rem .85rem; font-size:.85rem; color:#f8fafc;
            outline:none; transition: all .15s; min-width: 260px;
        }
        .filter-bar input:focus { border-color:var(--crm-accent, #2563eb); }

        .btn-filter {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: .5rem 1.1rem; border-radius: .5rem;
            background-color: var(--crm-accent, #2563eb); color: #ffffff !important;
            font-size: .85rem; font-weight: 700;
            border: none; cursor: pointer;
            box-shadow: 0 4px 12px var(--crm-accent-shadow, rgba(37, 99, 235, 0.3));
            transition: all .15s ease-in-out;
        }
        .btn-filter:hover { background: var(--crm-accent-hover, #1d4ed8); }

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
        .p-table thead th.center { text-align:center; }
        .p-table thead th.right { text-align:right; }
        .p-table tbody tr { border-bottom:1px solid rgba(255,255,255,0.04); transition:background .12s; }
        .p-table tbody tr:last-child { border-bottom:none; }
        .p-table tbody tr:hover { background:rgba(30,41,59,0.5); }
        .p-table tbody td { padding:1rem 1.25rem; vertical-align:middle; font-size:.85rem; color:#cbd5e1; }
        .p-table tbody td.center { text-align:center; }
        .p-table tbody td.right { text-align:right; }

        .cell-name { font-weight:700; color:#f8fafc; font-size:.9rem; }
        .cell-sub  { font-size:.75rem; color:#64748b; margin-top:.15rem; }

        .btn-action-view {
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 0.45rem;
            background-color: rgba(56,189,248,0.1); color: #38bdf8 !important;
            border: 1px solid rgba(56,189,248,0.3); transition: all .15s;
        }
        .btn-action-view:hover { background-color: rgba(56,189,248,0.2); color: #7dd3fc !important; }

        .btn-action-edit {
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 0.45rem;
            background-color: rgba(16,185,129,0.1); color: #34d399 !important;
            border: 1px solid rgba(16,185,129,0.3); transition: all .15s;
        }
        .btn-action-edit:hover { background-color: rgba(16,185,129,0.2); color: #6ee7b7 !important; }

        .btn-action-delete {
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 0.45rem;
            background-color: rgba(239,68,68,0.1); color: #f87171 !important;
            border: 1px solid rgba(239,68,68,0.3); transition: all .15s; cursor: pointer;
        }
        .btn-action-delete:hover { background-color: rgba(239,68,68,0.2); color: #fca5a5 !important; }

        .btn-create-po {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.35rem 0.75rem; border-radius: 0.45rem;
            font-size: 0.75rem; font-weight: 700;
            background: rgba(245,158,11,0.15); color: #fbbf24 !important;
            border: 1px solid rgba(245,158,11,0.3); text-decoration: none;
            transition: all .15s;
        }
        .btn-create-po:hover { background: #f59e0b; color: #020617 !important; }
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
                        <div class="stat-label">Total Suppliers</div>
                        <div class="stat-value">{{ $totalSuppliers }}</div>
                        <span class="text-xs text-slate-400 font-medium">Registered distributors</span>
                    </div>
                    <div class="stat-icon bg-blue-500/10 text-blue-400 border border-blue-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Active Vendors</div>
                        <div class="stat-value text-emerald-400">{{ $activeSuppliers }}</div>
                        <span class="text-xs text-emerald-400/80 font-medium">Available for POs</span>
                    </div>
                    <div class="stat-icon bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="pg-card">
                {{-- Filter Bar --}}
                <form method="GET" action="{{ route('suppliers.index') }}" class="filter-bar">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search supplier, company, contact, GSTIN..." />
                    <button type="submit" class="btn-filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filter
                    </button>
                    @if(request('search'))
                        <a href="{{ route('suppliers.index') }}" class="btn-clear">Clear</a>
                    @endif
                </form>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="p-table">
                        <thead>
                            <tr>
                                <th>Supplier / Distributor</th>
                                <th>Contact Person</th>
                                <th>Phone & Email</th>
                                <th>GSTIN & Terms</th>
                                <th class="center">Total POs</th>
                                <th class="center">Status</th>
                                <th class="right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $supplier)
                            <tr>
                                <td>
                                    <div class="cell-name">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="text-sky-400 hover:underline">
                                            {{ $supplier->name }}
                                        </a>
                                    </div>
                                    @if($supplier->company_name)
                                        <div class="cell-sub">{{ $supplier->company_name }}</div>
                                    @endif
                                    @if($supplier->city)
                                        <div class="text-[11px] text-slate-500">📍 {{ $supplier->city }}{{ $supplier->state ? ", {$supplier->state}" : '' }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-semibold text-slate-200">{{ $supplier->contact_person ?: '—' }}</span>
                                </td>
                                <td>
                                    @if($supplier->phone)
                                        <div class="font-medium text-slate-300 text-xs">📞 {{ $supplier->phone }}</div>
                                    @endif
                                    @if($supplier->email)
                                        <div class="text-xs text-slate-500 mt-0.5">✉️ {{ $supplier->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->gst_number)
                                        <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-900 text-slate-300 border border-slate-700 block w-fit mb-1">{{ $supplier->gst_number }}</span>
                                    @endif
                                    <span class="text-xs text-slate-400">{{ $supplier->payment_terms ?: 'Standard' }}</span>
                                </td>
                                <td class="center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-400 border border-sky-500/30">
                                        {{ $supplier->purchase_orders_count }} Orders
                                    </span>
                                </td>
                                <td class="center">
                                    @if($supplier->is_active)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">Active</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-800 text-slate-400 border border-slate-700">Inactive</span>
                                    @endif
                                </td>
                                <td class="right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}" class="btn-create-po" title="Create Purchase Order">
                                            + PO
                                        </a>
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn-action-view" title="View details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-action-edit" title="Edit supplier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        @if(auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Are you sure you want to delete this supplier?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action-delete" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-sm text-slate-500 font-medium">
                                    No suppliers registered yet. Click "Register Supplier" to add hardware distributors.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($suppliers->hasPages())
                <div class="p-4 border-t border-white/5">
                    {{ $suppliers->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
