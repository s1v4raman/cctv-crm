<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Suppliers</h2>
            </div>
            <a href="{{ route('suppliers.create') }}" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                + Register Supplier
            </a>
        </div>
    </x-slot>

    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 640px) {
            .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
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
        .filter-bar input {
            border: 1px solid #cbd5e1; border-radius: 4px;
            height: 38px; padding: 0 0.75rem; font-size: 0.85rem; color: #0f172a;
            background: #ffffff; outline: none; min-width: 260px;
        }
        html.dark .filter-bar input {
            background: #0b1120; border-color: #334155; color: #ffffff;
        }
        .filter-bar input:focus { border-color: #2563eb; }

        .p-table { width: 100%; border-collapse: collapse; }
        .p-table thead { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        html.dark .p-table thead { background: #0b1120; border-color: #1e293b; }
        .p-table thead th {
            padding: 0.75rem 1rem;
            font-size: 0.8rem; font-weight: 600; text-transform: none;
            color: #475569; text-align: left; white-space: nowrap;
        }
        html.dark .p-table thead th { color: #94a3b8; }
        .p-table thead th.center { text-align: center; }
        .p-table thead th.right { text-align: right; }
        .p-table tbody tr { border-bottom: 1px solid #e2e8f0; background: #ffffff; }
        html.dark .p-table tbody tr { border-bottom-color: #1e293b; background: #0f172a; }
        .p-table tbody tr:last-child { border-bottom: none; }
        .p-table tbody tr:hover { background: #f8fafc; }
        html.dark .p-table tbody tr:hover { background: #1e293b; }
        .p-table tbody td { padding: 0.75rem 1rem; vertical-align: middle; font-size: 0.85rem; color: #475569; }
        html.dark .p-table tbody td { color: #cbd5e1; }
        .p-table tbody td.center { text-align: center; }
        .p-table tbody td.right { text-align: right; }

        .cell-name { font-weight: 600; color: #0f172a; font-size: 0.85rem; }
        html.dark .cell-name { color: #ffffff; }
        .cell-name a { color: #2563eb; text-decoration: none; }
        .cell-name a:hover { text-decoration: underline; }
        .cell-sub  { font-size: 0.75rem; color: #64748b; margin-top: 0.15rem; }
        html.dark .cell-sub { color: #94a3b8; }
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
                    <div class="stat-label">Total Suppliers</div>
                    <div class="stat-value">{{ $totalSuppliers }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Active Vendors</div>
                    <div class="stat-value">{{ $activeSuppliers }}</div>
                </div>
            </div>

            <div class="pg-card">
                {{-- Filter Bar --}}
                <form method="GET" action="{{ route('suppliers.index') }}" class="filter-bar">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search supplier, company, contact, GSTIN..." />
                    <button type="submit" class="h-[38px] px-4 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                        Filter
                    </button>
                    @if(request('search'))
                        <a href="{{ route('suppliers.index') }}" class="h-[38px] px-3.5 inline-flex items-center rounded border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium transition">Clear</a>
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
                                        <a href="{{ route('suppliers.show', $supplier) }}">
                                            {{ $supplier->name }}
                                        </a>
                                    </div>
                                    @if($supplier->company_name)
                                        <div class="cell-sub">{{ $supplier->company_name }}</div>
                                    @endif
                                    @if($supplier->city)
                                        <div class="text-[11px] text-slate-500">{{ $supplier->city }}{{ $supplier->state ? ", {$supplier->state}" : '' }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $supplier->contact_person ?: '—' }}</span>
                                </td>
                                <td>
                                    @if($supplier->phone)
                                        <div class="text-xs text-slate-700 dark:text-slate-300">{{ $supplier->phone }}</div>
                                    @endif
                                    @if($supplier->email)
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $supplier->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->gst_number)
                                        <span class="font-mono text-xs block mb-0.5">{{ $supplier->gst_number }}</span>
                                    @endif
                                    <span class="text-xs text-slate-500">{{ $supplier->payment_terms ?: 'Standard' }}</span>
                                </td>
                                <td class="center">
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        {{ $supplier->purchase_orders_count }} Orders
                                    </span>
                                </td>
                                <td class="center">
                                    @if($supplier->is_active)
                                        <span class="text-xs font-bold text-emerald-600">Active</span>
                                    @else
                                        <span class="text-xs font-bold text-slate-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="right whitespace-nowrap text-sm" style="text-align:right;">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="text-blue-600 hover:underline">View</a>
                                        <span class="text-slate-300">|</span>
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="text-blue-600 hover:underline">Edit</a>
                                        @if(auth()->user()->isAdmin())
                                            <span class="text-slate-300">|</span>
                                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Are you sure you want to delete this supplier?');" class="inline">
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
                                <td colspan="7" class="text-center py-8 text-sm text-slate-500 font-medium">
                                    No suppliers registered yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($suppliers->hasPages())
                <div class="p-3 border-t border-slate-200 dark:border-slate-800">
                    {{ $suppliers->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
