<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">AMC Contracts</h2>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('amcs.create') }}" class="btn-primary">
                Create AMC
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 1024px) { .stat-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .stat-card {
            background: #ffffff;
            border-radius: 4px;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
        }
        html.dark .stat-card {
            background: #0f172a;
            border-color: var(--text-main);
        }
        .stat-label { font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 0.25rem; }
        html.dark .stat-label { color: var(--text-muted); }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: var(--text-main); line-height: 1; }
        html.dark .stat-value { color: #ffffff; }

        .pg-card {
            background: #ffffff;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        html.dark .pg-card {
            background: #0f172a;
            border-color: var(--text-main);
        }

        .p-table { width: 100%; border-collapse: collapse; }
        .p-table thead { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        html.dark .p-table thead { background: #0b1120; border-color: var(--text-main); }
        .p-table thead th {
            padding: 0.75rem 1rem;
            font-size: 0.8rem; font-weight: 600; text-transform: none;
            color: #475569; text-align: left; white-space: nowrap;
        }
        html.dark .p-table thead th { color: var(--text-muted); }
        .p-table thead th.right { text-align: right; }
        .p-table tbody tr { border-bottom: 1px solid #e2e8f0; background: #ffffff; }
        html.dark .p-table tbody tr { border-bottom-color: var(--text-main); background: #0f172a; }
        .p-table tbody tr:last-child { border-bottom: none; }
        .p-table tbody tr:hover { background: #f8fafc; }
        html.dark .p-table tbody tr:hover { background: var(--card-border); }
        .p-table tbody td { padding: 0.75rem 1rem; vertical-align: middle; font-size: 0.85rem; color: #475569; }
        html.dark .p-table tbody td { color: var(--text-muted); }
        .p-table tbody td.right { text-align: right; }

        .cell-no { font-weight: 600; font-family: ui-monospace,monospace; color: #2563eb; text-decoration: none; }
        .cell-no:hover { text-decoration: underline; }
        .cell-name { font-weight: 600; color: var(--text-main); }
        html.dark .cell-name { color: #ffffff; }

        /* Status: Plain bold colored text */
        .badge { font-size: 0.85rem; font-weight: 700; background: transparent !important; border: none !important; padding: 0 !important; }
        .badge-pending   { color: #64748b !important; }
        .badge-active    { color: #16a34a !important; }
        .badge-expired   { color: #dc2626 !important; }
        .badge-cancelled { color: #ea580c !important; }

        .pg-links { padding: 0.75rem 1rem; border-top: 1px solid #e2e8f0; }
        html.dark .pg-links { border-top-color: var(--text-main); }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">
            
            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            <div class="pg-card">
                {{-- Search & filter bar (Medium 38px Height Level) --}}
                <form method="GET" action="{{ route('amcs.index') }}" class="p-3 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b1120] flex items-center gap-2.5 flex-wrap text-xs">
                    <div class="relative flex-1 min-w-[220px] max-w-md flex items-center">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input aria-label="Search contract #, customer, phone" type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search contract #, customer, phone…" 
                               style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important; padding-left: 36px !important;"
                               class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 focus:outline-none focus:border-blue-500">
                    </div>
                    <select aria-label="Status" name="status" onchange="this.form.submit()" 
                            style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important; min-width: 140px;"
                            class="text-xs px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-800 dark:text-white focus:outline-none focus:border-blue-500">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                    <button type="submit" 
                            style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important;"
                            class="px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition inline-flex items-center justify-center cursor-pointer shadow-xs">
                        Filter
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('amcs.index') }}" 
                           style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important;"
                           class="px-3.5 inline-flex items-center rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 text-xs font-medium transition">
                            Clear
                        </a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="p-table">
                        <thead>
                            <tr>
                                <th>Contract No</th>
                                <th class="col-name" style="min-width: 200px;">Customer</th>
                                <th class="col-date whitespace-nowrap">Contract Period</th>
                                <th>Frequency</th>
                                <th class="right">Value</th>
                                <th>Status</th>
                                <th class="right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($amcs as $amc)
                                @php
                                    $amcStatusColors = [
                                        'active'    => '#16a34a',
                                        'pending'   => '#d97706',
                                        'expired'   => '#dc2626',
                                        'cancelled' => '#dc2626',
                                    ];
                                    $sColor = $amcStatusColors[strtolower($amc->status)] ?? '#d97706';
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('amcs.show', $amc) }}" class="cell-no">{{ $amc->contract_no }}</a>
                                    </td>
                                    <td class="col-name" style="min-width: 200px;">
                                        <div class="cell-name">{{ $amc->lead->customer_name }}</div>
                                        @if($amc->lead->phone)
                                            <div class="text-xs text-slate-500 mt-0.5 whitespace-nowrap">{{ $amc->lead->phone }}</div>
                                        @endif
                                    </td>
                                    <td class="col-date whitespace-nowrap">
                                        <span class="text-xs text-slate-700 dark:text-slate-300">
                                            {{ $amc->start_date->format('d M Y') }} – {{ $amc->end_date->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-xs text-slate-700 dark:text-slate-300">
                                            {{ ucfirst(str_replace('_', ' ', $amc->frequency)) }}
                                        </span>
                                    </td>
                                    <td class="right font-semibold text-slate-900 dark:text-white">
                                        ₹{{ number_format($amc->value, 0) }}
                                    </td>
                                    <td>
                                        <span class="status-text" style="color: {{ $sColor }}; font-weight: bold;">
                                            {{ ucfirst($amc->status) }}
                                        </span>
                                    </td>
                                    <td class="right whitespace-nowrap text-sm table-actions" style="text-align:right;">
                                        <a href="{{ route('amcs.show', $amc) }}" class="action-link">View</a>
                                        @if(auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('amcs.destroy', $amc) }}" class="inline"
                                                  data-confirm="Delete AMC contract {{ $amc->contract_no }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="danger-link">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-6 text-slate-500">
                                        No AMC contracts yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 text-sm font-medium text-slate-600 dark:text-slate-400">
                    Total: {{ $amcs->total() }}
                </div>
                    
                @if($amcs->hasPages())
                    <div class="pg-links">{{ $amcs->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
