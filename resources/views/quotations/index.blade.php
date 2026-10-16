<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Quotations</h2>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('quotations.create-general') }}" class="btn-primary">
                Create Quotation
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        /* Pipeline summary strip */
        .pip-grid {
            display:grid;
            grid-template-columns:repeat(2,1fr);
            gap:.75rem;
            margin-bottom:1.5rem;
        }
        @media(min-width:640px)  { .pip-grid { grid-template-columns:repeat(3,1fr); } }
        @media(min-width:1024px) { .pip-grid { grid-template-columns:repeat(5,1fr); } }

        .pip-card {
            background:#ffffff;
            border-radius:4px;
            border:1px solid #e2e8f0;
            padding:10px 14px;
        }
        html.dark .pip-card {
            background:#0f172a;
            border-color: var(--text-main);
        }
        .pip-label { font-size:14px; font-weight:500; text-transform:none; color:#64748b; margin-bottom:.25rem; }
        html.dark .pip-label { color: var(--text-muted); }
        .pip-count { font-size:1.6rem; font-weight:700; color: var(--text-main); line-height:1; }
        html.dark .pip-count { color:#ffffff; }

        /* Main table card */
        .pg-card {
            background:#ffffff;
            border-radius:4px;
            border:1px solid #e2e8f0;
            overflow:hidden;
        }
        html.dark .pg-card {
            background:#0f172a;
            border-color: var(--text-main);
        }

        /* Filter bar */
        .filter-bar {
            display:flex; align-items:center; gap:.75rem;
            padding:.75rem 1rem; border-bottom:1px solid var(--card-border);
            background:#ffffff; flex-wrap:wrap;
        }
        html.dark .filter-bar {
            background:#0b1120; border-color:var(--card-border);
        }
        .filter-bar input, .filter-bar select {
            border:1px solid var(--card-border); border-radius:6px;
            height:36px !important; min-height:36px !important; max-height:36px !important;
            padding:0 .75rem; font-size:12px !important; color: var(--text-main);
            background:#ffffff; outline:none;
        }
        html.dark .filter-bar input, html.dark .filter-bar select {
            background:#0b1120; border-color:#475569; color:#ffffff;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color:#2563eb; }
        .filter-bar input { min-width:180px; }

        /* Table */
        .q-table { width:100%; border-collapse:collapse; border:none !important; }
        .q-table thead { background:#f8fafc; }
        html.dark .q-table thead { background:#0b1120; }
        .q-table thead th {
            padding:.75rem 1rem;
            font-size:.8rem; font-weight:600; text-transform:none;
            color:#475569; text-align:left; white-space:nowrap;
            border-top:none !important;
            border-bottom:1px solid var(--card-border) !important;
            border-right:1px solid var(--card-border) !important;
        }
        html.dark .q-table thead th {
            color: var(--text-muted);
            border-top:none !important;
            border-bottom:1px solid var(--card-border) !important;
            border-right:1px solid var(--card-border) !important;
        }
        .q-table thead th.right { text-align:right; }
        .q-table tbody tr { border-bottom:none !important; background:#ffffff; }
        html.dark .q-table tbody tr { border-bottom:none !important; background:#0f172a; }
        .q-table tbody tr:hover { background:#f8fafc; }
        html.dark .q-table tbody tr:hover { background:var(--card-border); }
        .q-table tbody td {
            padding:.75rem 1rem;
            vertical-align:middle;
            font-size:.85rem;
            border-bottom:1px solid var(--card-border) !important;
            border-right:1px solid var(--card-border) !important;
        }
        html.dark .q-table tbody td {
            border-bottom:1px solid var(--card-border) !important;
            border-right:1px solid var(--card-border) !important;
        }
        .q-table tbody td.right { text-align:right; }
        .q-table th:first-child,
        .q-table td:first-child {
            border-left:none !important;
        }
        .q-table th:last-child,
        .q-table td:last-child {
            border-right:none !important;
        }
        .q-table tbody tr:last-child td {
            border-bottom:none !important;
        }

        .cell-no { font-size:.85rem; font-weight:600; color: var(--text-main); }
        html.dark .cell-no { color:#ffffff; }
        .cell-no a { color:#2563eb; text-decoration:none; }
        .cell-no a:hover { text-decoration:underline; }
        .cell-sub { font-size:.75rem; color:#64748b; margin-top:.15rem; }
        html.dark .cell-sub { color: var(--text-muted); }
        .cell-text { font-size:.85rem; color:#475569; }
        html.dark .cell-text { color: var(--text-muted); }
        .cell-amount { font-size:.9rem; font-weight:700; color: var(--text-main); }
        html.dark .cell-amount { color:#ffffff; }
        .cell-date { font-size:.85rem; color:#475569; white-space:nowrap; }
        html.dark .cell-date { color: var(--text-muted); }

        /* Status: Plain bold colored text */
        .badge {
            display:inline-block;
            font-size:.85rem;
            font-weight:700;
            background:transparent !important;
            border:none !important;
            padding:0 !important;
        }
        .badge-draft    { color:#64748b !important; }
        .badge-sent     { color:#2563eb !important; }
        .badge-accepted { color:#16a34a !important; }
        .badge-rejected { color:#dc2626 !important; }
        .badge-expired  { color:#ea580c !important; }

        .pg-links { padding:.75rem 1rem; border-top:1px solid #e2e8f0; }
        html.dark .pg-links { border-top-color: var(--text-main); }
        .empty-state { text-align:center; padding:3rem 1rem; color:#64748b; font-size:.85rem; }
        html.dark .empty-state { color: var(--text-muted); }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Table Card --}}
            <div class="pg-card">

                <form method="GET" action="{{ route('quotations.index') }}" class="filter-bar flex items-center gap-2.5 flex-wrap p-2.5 text-xs">
                    <div class="relative w-72 min-w-[220px] flex items-center shrink-0">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" id="q-search" value="{{ request('search') }}" 
                               placeholder="Search quotation no, customer, phone…" 
                               oninput="filterQuotes()" 
                               style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important; padding-left: 36px !important;"
                               class="w-full text-xs">
                    </div>
                    <select name="status" id="q-status" onchange="this.form.submit()" 
                            style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important; min-width: 140px;"
                            class="text-xs shrink-0">
                        <option value="">All Statuses</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                        <option value="accepted" @selected(request('status') === 'accepted')>Accepted</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                    </select>
                    <button type="submit" 
                            style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important;"
                            class="px-4 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition shadow-xs inline-flex items-center justify-center shrink-0 cursor-pointer">
                        Filter
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('quotations.index') }}" 
                           style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important;"
                           class="px-3.5 text-xs font-medium border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 inline-flex items-center shrink-0">
                            Clear
                        </a>
                    @endif
                    <span id="q-count" style="margin-left:auto;font-size:.75rem;color: var(--text-muted);"></span>
                </form>

                <div style="overflow-x:auto">
                    <table class="q-table">
                        <thead>
                            <tr>
                                <th>Quotation No</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Valid Until</th>
                                <th class="right">Total</th>
                                <th>Status</th>
                                <th class="right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($quotations as $quotation)
                                <tr class="q-row"
                                    data-search="{{ strtolower($quotation->quotation_no . ' ' . ($quotation->lead->customer_name ?? '')) }}"
                                    data-status="{{ $quotation->status }}">

                                    <td>
                                        <div class="cell-no">
                                            <a href="{{ route('quotations.show', $quotation) }}">
                                                {{ $quotation->quotation_no }}
                                            </a>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="cell-text font-semibold text-slate-800 dark:text-slate-100">
                                            {{ $quotation->lead->customer_name ?? '—' }}
                                        </div>
                                        @if($quotation->lead?->phone)
                                            <div class="cell-sub">{{ $quotation->lead->phone }}</div>
                                        @endif
                                    </td>

                                    <td class="cell-date">
                                        {{ $quotation->quotation_date?->format('d M Y') ?? $quotation->created_at->format('d M Y') }}
                                    </td>

                                    <td>
                                        @if($quotation->valid_until)
                                            @php $expired = $quotation->valid_until->isPast() && !in_array($quotation->status, ['accepted','rejected']); @endphp
                                            <span class="cell-date" style="{{ $expired ? 'color:#ef4444' : '' }}">
                                                {{ $quotation->valid_until->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="cell-date" style="color:#cbd5e1">—</span>
                                        @endif
                                    </td>

                                    <td class="right">
                                        <span class="cell-amount">₹{{ number_format($quotation->total, 0) }}</span>
                                    </td>

                                    <td>
                                        @php
                                            $qStatusColors = [
                                                'accepted' => '#16a34a',
                                                'sent'     => '#d97706',
                                                'draft'    => '#555555',
                                                'rejected' => '#dc2626',
                                                'declined' => '#dc2626',
                                                'expired'  => '#dc2626',
                                            ];
                                            $sColor = $qStatusColors[strtolower($quotation->status)] ?? '#555555';
                                        @endphp
                                        <span class="status-text" style="color: {{ $sColor }}; font-weight: bold;">
                                            {{ ucfirst($quotation->status) }}
                                        </span>
                                    </td>
                                    <td class="right whitespace-nowrap text-sm table-actions" style="text-align:right;">
                                        <a href="{{ route('quotations.show', $quotation) }}" class="action-link">View</a>
                                        <a href="{{ route('quotations.edit', $quotation) }}" class="action-link">Edit</a>
                                        @if($quotation->status === 'draft')
                                            <form method="POST" action="{{ route('quotations.destroy', $quotation) }}" class="inline"
                                                  onsubmit="return confirm('Delete {{ addslashes($quotation->quotation_no) }}? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="danger-link">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            No quotations yet
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Total count shown below the table --}}
                <div class="px-4 py-2.5 border-t border-[#ddd] dark:border-slate-800 bg-[#fbfbfb] dark:bg-slate-900/60 flex items-center justify-between text-xs text-slate-500">
                    <span class="font-medium">Total: {{ $quotations->total() }}</span>
                    @if($quotations->hasPages())
                        <div class="text-xs">
                            {{ $quotations->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script>
    function filterQuotes() {
        const q      = document.getElementById('q-search').value.toLowerCase();
        const status = document.getElementById('q-status').value;
        const rows   = document.querySelectorAll('.q-row');
        let visible  = 0;

        rows.forEach(row => {
            const sm = row.dataset.search.includes(q);
            const st = !status || row.dataset.status === status;
            const ok = sm && st;
            row.style.display = ok ? '' : 'none';
            if (ok) visible++;
        });

        const total = rows.length;
        document.getElementById('q-count').textContent =
            visible === total ? `${total} quotation${total !== 1 ? 's' : ''}`
                              : `${visible} of ${total} quotations`;
    }
    filterQuotes();
    </script>
</x-app-layout>