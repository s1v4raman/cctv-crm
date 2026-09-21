<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-white font-heading tracking-tight">Leads & Prospects</h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded border border-amber-500/30">Pipeline</span>
                </div>
                <p class="mt-1 text-xs text-slate-400">Manage client security inquiries, site survey bookings, and conversions</p>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('leads.create') }}" class="btn-amber">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                New Lead
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        /* .pg-wrap uses global app.css */
        /* .pg-inner uses global app.css */

        .pg-card {
            background:#0f172a;
            border-radius:1rem;
            border:1px solid rgba(255, 255, 255, 0.08);
            box-shadow:0 10px 25px -5px rgba(0, 0, 0, 0.5);
            overflow:hidden;
        }

        .btn-edit {
            display:inline-flex; align-items:center; gap:.25rem;
            padding:.3rem .7rem; border-radius:.5rem;
            font-size:.72rem; font-weight:700;
            background:rgba(59, 130, 246, 0.15); color:#60a5fa;
            border:1px solid rgba(59, 130, 246, 0.3); text-decoration:none;
            transition:all .15s;
        }
        .btn-edit:hover { background:rgba(59, 130, 246, 0.25); color:#93c5fd; }
        .btn-del {
            display:inline-flex; align-items:center; gap:.25rem;
            padding:.3rem .7rem; border-radius:.5rem;
            font-size:.72rem; font-weight:700;
            background:rgba(244, 63, 94, 0.15); color:#fb7185;
            border:1px solid rgba(244, 63, 94, 0.3); cursor:pointer;
            transition:all .15s; font-family:inherit;
        }
        .btn-del:hover { background:rgba(244, 63, 94, 0.25); color:#fda4af; }

        /* Filter toolbar */
        .filter-bar {
            display:flex;
            align-items:center;
            gap:.75rem;
            padding:1rem 1.25rem;
            border-bottom:1px solid rgba(255, 255, 255, 0.08);
            background:#0b1120;
            flex-wrap:wrap;
        }
        .filter-bar input, .filter-bar select {
            border:1px solid #334155;
            border-radius:.6rem;
            padding:.5rem .9rem;
            font-size:.82rem;
            color:#ffffff;
            outline:none;
            transition:border-color .15s;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color:#f59e0b; }
        .filter-bar input { min-width:220px; }

        /* Table */
        .leads-table { width:100%; border-collapse:collapse; }
        .leads-table thead { background:#0b1120; }
        .leads-table thead th {
            padding:.75rem 1.25rem;
            font-size:.68rem;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.06em;
            color:#94a3b8;
            text-align:left;
            white-space:nowrap;
            border-bottom:1px solid rgba(255, 255, 255, 0.05);
        }
        .leads-table tbody tr {
            border-bottom:1px solid rgba(255, 255, 255, 0.05);
            transition:background .12s;
        }
        .leads-table tbody tr:last-child { border-bottom:none; }
        .leads-table tbody tr:hover { background:#1e293b; }
        .leads-table tbody td { padding:.9rem 1.25rem; vertical-align:middle; }

        .cell-name { font-size:.85rem; font-weight:700; color:#ffffff; }
        .cell-name a { color:#f59e0b; text-decoration:none; transition:color .15s; }
        .cell-name a:hover { color:#fbbf24; }
        .cell-sub  { font-size:.72rem; color:#94a3b8; margin-top:.1rem; }
        .cell-text { font-size:.82rem; color:#cbd5e1; }

        /* Badges */
        .badge {
            display:inline-flex; align-items:center; gap:.3rem;
            padding:.2rem .65rem; border-radius:999px;
            font-size:.68rem; font-weight:800; line-height:1.5;
            text-transform:uppercase; letter-spacing:.04em;
        }
        .badge-new       { background:rgba(148, 163, 184, 0.15); color:#cbd5e1; border:1px solid rgba(148, 163, 184, 0.3); }
        .badge-contacted { background:rgba(59, 130, 246, 0.15); color:#60a5fa; border:1px solid rgba(59, 130, 246, 0.3); }
        .badge-quoted    { background:rgba(245, 158, 11, 0.15); color:#fbbf24; border:1px solid rgba(245, 158, 11, 0.3); }
        .badge-won       { background:rgba(16, 185, 129, 0.15); color:#34d399; border:1px solid rgba(16, 185, 129, 0.3); }
        .badge-lost      { background:rgba(244, 63, 94, 0.15); color:#fb7185; border:1px solid rgba(244, 63, 94, 0.3); }

        /* Pagination */
        .pg-links { padding:1rem 1.25rem; border-top:1px solid rgba(255, 255, 255, 0.08); }

        /* Source badge */
        .source-tag {
            display:inline-block;
            padding:.2rem .55rem;
            border-radius:.4rem;
            font-size:.7rem;
            font-weight:700;
            background:#1e293b;
            color:#94a3b8;
            border:1px solid rgba(255, 255, 255, 0.05);
            text-transform:capitalize;
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
                    <select name="status" id="lead-status-filter" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="new" @selected(request('status') === 'new')>New</option>
                        <option value="contacted" @selected(request('status') === 'contacted')>Contacted</option>
                        <option value="quoted" @selected(request('status') === 'quoted')>Quoted</option>
                        <option value="won" @selected(request('status') === 'won')>Won</option>
                        <option value="lost" @selected(request('status') === 'lost')>Lost</option>
                    </select>
                    <button type="submit" class="px-3 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg transition shadow">
                        Search
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('leads.index') }}" class="px-2.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-lg transition">
                            Clear
                        </a>
                    @endif
                    <span id="lead-count" class="text-xs text-slate-400 ml-auto"></span>
                </form>

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
    function filterLeads() {
        const q      = document.getElementById('lead-search').value.toLowerCase();
        const status = document.getElementById('lead-status-filter').value;
        const rows   = document.querySelectorAll('.lead-row');
        let visible  = 0;

        rows.forEach(row => {
            const nameMatch   = row.dataset.name.includes(q) || row.dataset.phone.includes(q);
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

    // init count
    filterLeads();
    </script>
</x-app-layout>