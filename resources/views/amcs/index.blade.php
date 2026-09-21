<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">AMC &amp; Maintenance Contracts</h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-500/30">Recurring SLA</span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Manage client annual maintenance contracts, warranty renewals &amp; quarterly routine servicing visits</p>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('amcs.create') }}" class="btn-amber">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Create AMC
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }
        @media (min-width: 640px) { .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .stat-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .stat-label { font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:#64748b; margin-bottom:0.25rem; }
        html.dark .stat-label { color:#94a3b8; }
        .stat-value { font-size:1.7rem; font-weight:800; color:#0f172a; font-family:'Outfit',sans-serif; }
        html.dark .stat-value { color:#ffffff; }
        .stat-icon { width:48px; height:48px; border-radius:0.75rem; display:flex; align-items:center; justify-content:center; }

        /* AMC Table */
        .p-table { width:100%; border-collapse:collapse; }
        .p-table thead th.right { text-align:right; }
        .p-table tbody td.right { text-align:right; }

        .cell-no { font-weight:800; font-family:ui-monospace,monospace; color:#2563eb; text-decoration:none; }
        .cell-no:hover { color:#1d4ed8; text-decoration:underline; }
        html.dark .cell-no { color:#f59e0b; }
        html.dark .cell-no:hover { color:#fbbf24; }
        .cell-name { font-weight:700; color:#0f172a; }
        html.dark .cell-name { color:#ffffff; }

        /* Badges */
        .badge { display:inline-flex; padding:.25rem .75rem; border-radius:9999px; font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
        .badge-pending   { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
        .badge-active    { background:#ecfdf5; color:#059669; border:1px solid #d1fae5; }
        .badge-expired   { background:#fef2f2; color:#dc2626; border:1px solid #fee2e2; }
        .badge-cancelled { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }

        html.dark .badge-pending   { background:rgba(148,163,184,.15); color:#cbd5e1; border-color:rgba(148,163,184,.3); }
        html.dark .badge-active    { background:rgba(16,185,129,.15); color:#34d399; border-color:rgba(16,185,129,.3); }
        html.dark .badge-expired   { background:rgba(244,63,94,.15); color:#fb7185; border-color:rgba(244,63,94,.3); }
        html.dark .badge-cancelled { background:rgba(249,115,22,.15); color:#fb923c; border-color:rgba(249,115,22,.3); }

        .btn-repair {
            display:inline-flex; align-items:center; gap:0.3rem;
            padding:0.35rem 0.7rem; border-radius:0.45rem;
            font-size:0.72rem; font-weight:700;
            background:#fef2f2; color:#dc2626 !important;
            border:1px solid #fca5a5; text-decoration:none; transition:all .15s;
        }
        .btn-repair:hover { background:#dc2626; color:#ffffff !important; }
        html.dark .btn-repair { background:rgba(244,63,94,.15); color:#fb7185 !important; border-color:rgba(244,63,94,.3); }

        .btn-view {
            display:inline-flex; align-items:center; gap:0.3rem;
            padding:0.35rem 0.7rem; border-radius:0.45rem;
            font-size:0.72rem; font-weight:700;
            background:#eff6ff; color:#2563eb !important;
            border:1px solid #dbeafe; text-decoration:none; transition:all .15s;
        }
        .btn-view:hover { background:#2563eb; color:#ffffff !important; }
        html.dark .btn-view { background:rgba(59,130,246,.15); color:#60a5fa !important; border-color:rgba(59,130,246,.3); }

        .pg-links { padding:1rem 1.25rem; border-top:1px solid #e2e8f0; }
        html.dark .pg-links { border-top-color:#1e293b; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">
            
            @if (session('status'))
                <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Stat Cards --}}
            @php
                $totalContracts = $amcs->total();
                $activeCount = \App\Models\AmcContract::where('status', 'active')->count();
                $totalAmcValue = \App\Models\AmcContract::where('status', 'active')->sum('value');
                $pendingVisits = \App\Models\AmcVisit::where('status', 'pending')->count();
            @endphp
            <div class="stat-grid">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Contracts</div>
                        <div class="stat-value">{{ $totalContracts }}</div>
                        <span class="text-xs text-gray-500 font-medium">All registered agreements</span>
                    </div>
                    <div class="stat-icon bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Active Contracts</div>
                        <div class="stat-value text-emerald-600">{{ $activeCount }}</div>
                        <span class="text-xs text-emerald-600 font-medium">Under active servicing</span>
                    </div>
                    <div class="stat-icon bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Pending Routine Visits</div>
                        <div class="stat-value text-amber-600">{{ $pendingVisits }}</div>
                        <span class="text-xs text-amber-600 font-medium">Scheduled for servicing</span>
                    </div>
                    <div class="stat-icon bg-amber-50 dark:bg-amber-950/50 text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Active AMC Revenue</div>
                        <div class="stat-value text-indigo-600">₹{{ number_format($totalAmcValue, 2) }}</div>
                        <span class="text-xs text-indigo-600 font-medium">Annualized recurring value</span>
                    </div>
                    <div class="stat-icon bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="pg-card">
                {{-- Search & filter bar --}}
                <form method="GET" action="{{ route('amcs.index') }}" class="p-4 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b1120] flex items-center gap-2 flex-wrap">
                    <div class="relative flex-1 min-w-[220px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search contract #, customer, phone, plan…" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 py-2">
                    </div>
                    <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 py-2">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                    <button type="submit" class="px-3 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl transition shadow">
                        Search
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('amcs.index') }}" class="px-2.5 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                            Clear
                        </a>
                    @endif
                </form>

                @if($amcs->isEmpty())
                    <div class="p-12 text-center">
                        <p class="font-bold text-slate-900 dark:text-white text-base mb-1">No AMC Contracts Found</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Add maintenance agreements to auto-schedule periodic servicing routines.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="p-table">
                            <thead>
                                <tr>
                                    <th>Contract No</th>
                                    <th>Customer / Premise</th>
                                    <th>Contract Period</th>
                                    <th>Servicing Frequency</th>
                                    <th class="right">Contract Value</th>
                                    <th>Status</th>
                                    <th class="right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($amcs as $amc)
                                    <tr>
                                        <td>
                                            <a href="{{ route('amcs.show', $amc) }}" class="cell-no">{{ $amc->contract_no }}</a>
                                        </td>
                                        <td>
                                            <span class="cell-name">{{ $amc->lead->customer_name }}</span>
                                            @if($amc->lead->phone)
                                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $amc->lead->phone }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-medium text-slate-700 dark:text-slate-300">
                                                {{ $amc->start_date->format('d M Y') }} &rarr; {{ $amc->end_date->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 capitalize">
                                                {{ str_replace('_', ' ', $amc->frequency) }}
                                            </span>
                                        </td>
                                        <td class="right font-bold text-slate-900 dark:text-white">
                                            ₹{{ number_format($amc->value, 2) }}
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $amc->status }}">
                                                {{ ucfirst($amc->status) }}
                                            </span>
                                        </td>
                                        <td class="right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <a href="{{ route('service-tickets.create', ['lead_id' => $amc->lead_id, 'amc_contract_id' => $amc->id]) }}" 
                                                   class="btn-repair" title="Log Breakdown Ticket under AMC">
                                                    🚨 Repair
                                                </a>
                                                <a href="{{ route('amcs.show', $amc) }}" class="btn-view">
                                                    View Details
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($amcs->hasPages())
                        <div class="pg-links">{{ $amcs->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
