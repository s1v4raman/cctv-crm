<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Leads & Prospects</h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border"
                          style="background-color: color-mix(in srgb, var(--crm-accent, #be123c) 15%, transparent); color: var(--crm-accent, #be123c); border-color: color-mix(in srgb, var(--crm-accent, #be123c) 30%, transparent);">
                        Zoho CRM Custom View
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Manage client security inquiries, site survey bookings, and conversions</p>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('leads.create') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-white font-bold text-xs shadow-md transition-all hover:scale-[1.02] active:scale-[0.98]"
               style="background-color: var(--crm-accent, #be123c); box-shadow: 0 4px 12px -2px color-mix(in srgb, var(--crm-accent, #be123c) 40%, transparent);">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                New Lead
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        .pg-card {
            background: #ffffff;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: background-color 0.2s, border-color 0.2s;
        }
        .dark .pg-card {
            background: #0f172a;
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .btn-edit {
            display:inline-flex; align-items:center; gap:.25rem;
            padding:.3rem .7rem; border-radius:.5rem;
            font-size:.72rem; font-weight:700;
            background:rgba(59, 130, 246, 0.12); color:#2563eb;
            border:1px solid rgba(59, 130, 246, 0.25); text-decoration:none;
            transition:all .15s;
        }
        .dark .btn-edit {
            background:rgba(59, 130, 246, 0.15); color:#60a5fa;
            border-color:rgba(59, 130, 246, 0.3);
        }
        .btn-edit:hover { background:rgba(59, 130, 246, 0.25); }

        .btn-ticket {
            display:inline-flex; align-items:center; gap:.25rem;
            padding:.3rem .7rem; border-radius:.5rem;
            font-size:.72rem; font-weight:700;
            background:rgba(234, 88, 12, 0.12); color:#ea580c;
            border:1px solid rgba(234, 88, 12, 0.25); text-decoration:none;
            transition:all .15s;
        }
        .dark .btn-ticket {
            background:rgba(234, 88, 12, 0.18); color:#fb923c;
            border-color:rgba(234, 88, 12, 0.35);
        }

        .btn-del {
            display:inline-flex; align-items:center; gap:.25rem;
            padding:.3rem .7rem; border-radius:.5rem;
            font-size:.72rem; font-weight:700;
            background:rgba(244, 63, 94, 0.12); color:#e11d48;
            border:1px solid rgba(244, 63, 94, 0.25); cursor:pointer;
            transition:all .15s; font-family:inherit;
        }
        .dark .btn-del {
            background:rgba(244, 63, 94, 0.15); color:#fb7185;
            border-color:rgba(244, 63, 94, 0.3);
        }
        .btn-del:hover { background:rgba(244, 63, 94, 0.25); }

        /* Filter toolbar */
        .filter-bar {
            display:flex;
            align-items:center;
            gap:.75rem;
            padding:1rem 1.25rem;
            border-bottom:1px solid #e2e8f0;
            background:#f8fafc;
            flex-wrap:wrap;
        }
        .dark .filter-bar {
            border-bottom-color: rgba(255, 255, 255, 0.08);
            background:#0b1120;
        }

        .filter-bar input, .filter-bar select {
            border:1px solid #cbd5e1;
            border-radius:.6rem;
            padding:.5rem .9rem;
            font-size:.82rem;
            color:#0f172a;
            background:#ffffff;
            outline:none;
            transition:border-color .15s;
        }
        .dark .filter-bar input, .dark .filter-bar select {
            border-color:#334155;
            color:#ffffff;
            background:#0f172a;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color:var(--crm-accent, #be123c); }
        .filter-bar input { min-width:220px; }

        /* Table */
        .leads-table { width:100%; border-collapse:collapse; }
        .leads-table thead { background:#f1f5f9; }
        .dark .leads-table thead { background:#0b1120; }

        .leads-table thead th {
            padding:.75rem 1.25rem;
            font-size:.68rem;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.06em;
            color:#64748b;
            text-align:left;
            white-space:nowrap;
            border-bottom:1px solid #e2e8f0;
        }
        .dark .leads-table thead th {
            color:#94a3b8;
            border-bottom-color:rgba(255, 255, 255, 0.05);
        }

        .leads-table tbody tr {
            border-bottom:1px solid #f1f5f9;
            transition:background .12s;
        }
        .dark .leads-table tbody tr {
            border-bottom-color:rgba(255, 255, 255, 0.05);
        }
        .leads-table tbody tr:last-child { border-bottom:none; }
        .leads-table tbody tr:hover { background:#f8fafc; }
        .dark .leads-table tbody tr:hover { background:#1e293b; }
        .leads-table tbody td { padding:.9rem 1.25rem; vertical-align:middle; }

        .cell-name { font-size:.85rem; font-weight:700; color:#0f172a; }
        .dark .cell-name { color:#ffffff; }
        .cell-name a { color:var(--crm-accent, #be123c); text-decoration:none; transition:opacity .15s; }
        .cell-name a:hover { opacity:0.85; text-decoration:underline; }
        .cell-sub  { font-size:.72rem; color:#64748b; margin-top:.1rem; }
        .dark .cell-sub { color:#94a3b8; }
        .cell-text { font-size:.82rem; color:#334155; }
        .dark .cell-text { color:#cbd5e1; }

        /* Badges */
        .badge {
            display:inline-flex; align-items:center; gap:.3rem;
            padding:.2rem .65rem; border-radius:999px;
            font-size:.68rem; font-weight:800; line-height:1.5;
            text-transform:uppercase; letter-spacing:.04em;
        }
        .badge-new       { background:rgba(100, 116, 139, 0.12); color:#475569; border:1px solid rgba(100, 116, 139, 0.25); }
        .dark .badge-new { background:rgba(148, 163, 184, 0.15); color:#cbd5e1; border-color:rgba(148, 163, 184, 0.3); }

        .badge-contacted { background:rgba(59, 130, 246, 0.12); color:#2563eb; border:1px solid rgba(59, 130, 246, 0.25); }
        .dark .badge-contacted { background:rgba(59, 130, 246, 0.15); color:#60a5fa; border-color:rgba(59, 130, 246, 0.3); }

        .badge-quoted    { background:rgba(217, 119, 6, 0.12); color:#d97706; border:1px solid rgba(217, 119, 6, 0.25); }
        .dark .badge-quoted { background:rgba(245, 158, 11, 0.15); color:#fbbf24; border-color:rgba(245, 158, 11, 0.3); }

        .badge-won       { background:rgba(16, 185, 129, 0.12); color:#059669; border:1px solid rgba(16, 185, 129, 0.25); }
        .dark .badge-won { background:rgba(16, 185, 129, 0.15); color:#34d399; border-color:rgba(16, 185, 129, 0.3); }

        .badge-lost      { background:rgba(225, 29, 72, 0.12); color:#e11d48; border:1px solid rgba(225, 29, 72, 0.25); }
        .dark .badge-lost { background:rgba(244, 63, 94, 0.15); color:#fb7185; border-color:rgba(244, 63, 94, 0.3); }

        /* Pagination */
        .pg-links { padding:1rem 1.25rem; border-top:1px solid #e2e8f0; }
        .dark .pg-links { border-top-color:rgba(255, 255, 255, 0.08); }

        /* Source badge */
        .source-tag {
            display:inline-block;
            padding:.2rem .55rem;
            border-radius:.4rem;
            font-size:.7rem;
            font-weight:700;
            background:#f1f5f9;
            color:#475569;
            border:1px solid #e2e8f0;
            text-transform:capitalize;
        }
        .dark .source-tag {
            background:#1e293b;
            color:#94a3b8;
            border-color:rgba(255, 255, 255, 0.05);
        }

        /* Empty */
        .empty-state { text-align:center; padding:3rem 1rem; color:#64748b; font-size:.85rem; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="pg-card">

                {{-- Search & filter bar --}}
                <form method="GET" action="{{ route('leads.index') }}" class="filter-bar flex items-center gap-2 flex-wrap">
                    <div class="relative flex-1 min-w-[220px]">
                        <input type="text" name="search" id="lead-search" value="{{ request('search') }}" placeholder="Search customer, phone, address, company…" oninput="filterLeads()" class="w-full">
                    </div>
                    <select name="status" id="lead-status-filter" onchange="filterLeads()" class="hidden sm:inline-block">
                        <option value="">All Statuses</option>
                        <option value="new" @selected(request('status') === 'new')>New</option>
                        <option value="contacted" @selected(request('status') === 'contacted')>Contacted</option>
                        <option value="quoted" @selected(request('status') === 'quoted')>Quoted</option>
                        <option value="won" @selected(request('status') === 'won')>Won</option>
                        <option value="lost" @selected(request('status') === 'lost')>Lost</option>
                    </select>
                    <button type="button" onclick="filterLeads()" 
                            class="px-3 py-2 text-white font-bold text-xs rounded-lg transition shadow-xs"
                            style="background-color: var(--crm-accent, #be123c);">
                        Filter
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('leads.index') }}" class="px-2.5 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition">
                            Reset
                        </a>
                    @endif
                    <span id="lead-count" class="text-xs text-slate-500 dark:text-slate-400 ml-auto"></span>
                </form>

                {{-- Zoho CRM Custom View Quick Filter Tabs --}}
                <div class="px-4 py-2 bg-slate-50 dark:bg-[#0b1120] border-b border-slate-200 dark:border-slate-800/80 flex items-center gap-1.5 overflow-x-auto text-xs font-semibold">
                    <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider mr-1">Views:</span>
                    <button type="button" onclick="setCustomViewFilter('')" 
                            id="view-tab-all"
                            class="custom-view-pill px-3 py-1 rounded-full text-xs font-bold transition-all"
                            style="background-color: var(--crm-accent, #be123c); color: #ffffff;">
                        All Leads ({{ $leads->total() }})
                    </button>
                    <button type="button" onclick="setCustomViewFilter('new')" 
                            id="view-tab-new"
                            class="custom-view-pill px-3 py-1 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">
                        New
                    </button>
                    <button type="button" onclick="setCustomViewFilter('contacted')" 
                            id="view-tab-contacted"
                            class="custom-view-pill px-3 py-1 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">
                        Contacted
                    </button>
                    <button type="button" onclick="setCustomViewFilter('quoted')" 
                            id="view-tab-quoted"
                            class="custom-view-pill px-3 py-1 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">
                        Quoted
                    </button>
                    <button type="button" onclick="setCustomViewFilter('won')" 
                            id="view-tab-won"
                            class="custom-view-pill px-3 py-1 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">
                        Won
                    </button>
                    <button type="button" onclick="setCustomViewFilter('lost')" 
                            id="view-tab-lost"
                            class="custom-view-pill px-3 py-1 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">
                        Lost
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="leads-table" id="leads-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Added</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="leads-tbody">
                            @forelse ($leads as $lead)
                                <tr class="lead-row"
                                    data-name="{{ strtolower($lead->customer_name) }}"
                                    data-phone="{{ $lead->phone }}"
                                    data-status="{{ $lead->status }}">

                                    <td>
                                        <div class="cell-name">
                                            <a href="{{ route('leads.show', $lead) }}">{{ $lead->customer_name }}</a>
                                        </div>
                                        @if($lead->site_address)
                                            <div class="cell-sub">{{ Str::limit($lead->site_address, 40) }}</div>
                                        @endif
                                    </td>

                                    <td class="cell-text">{{ $lead->phone }}</td>

                                    <td class="cell-text">{{ $lead->email ?? '—' }}</td>

                                    <td>
                                        @if($lead->source)
                                            <span class="source-tag">{{ $lead->source }}</span>
                                        @else
                                            <span class="cell-text">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge badge-{{ $lead->status }}">{{ ucfirst($lead->status) }}</span>
                                    </td>

                                    <td class="cell-sub whitespace-nowrap">
                                        {{ $lead->created_at->format('d M Y') }}
                                    </td>
                                    <td class="text-right whitespace-nowrap">
                                         <div class="flex items-center justify-end gap-1.5">
                                             <a href="{{ route('service-tickets.create', ['lead_id' => $lead->id]) }}" 
                                                title="Log Breakdown / Service Ticket"
                                                class="btn-ticket">
                                                 🚨 Ticket
                                             </a>
                                             @if(auth()->user()->isAdmin())
                                                 <a href="{{ route('leads.edit', $lead) }}" class="btn-edit">
                                                     <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                                     Edit
                                                 </a>
                                                 <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                                                       onsubmit="return confirm('Delete lead {{ addslashes($lead->customer_name) }}? This will also delete all quotations and jobs linked to this lead.')">
                                                     @csrf
                                                     @method('DELETE')
                                                     <button type="submit" class="btn-del">
                                                         <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                                         Delete
                                                     </button>
                                                 </form>
                                             @endif
                                         </div>
                                     </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <svg class="w-10 h-10 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                            </svg>
                                            No leads found.
                                            @if(auth()->user()->isAdmin())
                                                <a href="{{ route('leads.create') }}" class="text-indigo-400 font-semibold hover:underline">Create the first lead →</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($leads->hasPages())
                    <div class="pg-links">{{ $leads->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    <script>
    function setCustomViewFilter(status) {
        document.getElementById('lead-status-filter').value = status;
        document.querySelectorAll('.custom-view-pill').forEach(el => {
            el.style.backgroundColor = '';
            el.style.color = '';
            el.className = 'custom-view-pill px-3 py-1 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all';
        });
        const activeTab = document.getElementById('view-tab-' + (status || 'all'));
        if (activeTab) {
            activeTab.className = 'custom-view-pill px-3 py-1 rounded-full text-xs font-bold transition-all';
            activeTab.style.backgroundColor = 'var(--crm-accent, #be123c)';
            activeTab.style.color = '#ffffff';
        }
        filterLeads();
    }

    function filterLeads() {
        const q      = (document.getElementById('lead-search').value || '').toLowerCase();
        const status = document.getElementById('lead-status-filter').value;
        const rows   = document.querySelectorAll('.lead-row');
        let visible  = 0;

        rows.forEach(row => {
            const nameMatch   = (row.dataset.name || '').includes(q) || (row.dataset.phone || '').includes(q);
            const statusMatch = !status || row.dataset.status === status;
            const show        = nameMatch && statusMatch;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        const total = rows.length;
        document.getElementById('lead-count').textContent =
            visible === total ? `${total} lead${total !== 1 ? 's' : ''}`
                              : `${visible} of ${total} leads`;
    }

    // Initialize custom view pill from current URL param if present
    const urlStatus = "{{ request('status') }}";
    if (urlStatus) {
        setCustomViewFilter(urlStatus);
    } else {
        filterLeads();
    }
    </script>
</x-app-layout>