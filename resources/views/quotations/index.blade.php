<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-white font-heading tracking-tight">Quotations & Proposals</h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded border border-blue-500/30">BOM Estimator</span>
                </div>
                <p class="mt-1 text-xs text-slate-400">Manage client proposals, auto-pricing calculators, and digital approvals</p>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('quotations.create-general') }}" class="btn-amber">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                New Quotation
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        /* .pg-wrap uses global app.css */
        /* .pg-inner uses global app.css */

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
            background:#0f172a;
            border-radius:.875rem;
            border:1px solid rgba(255, 255, 255, 0.08);
            box-shadow:0 10px 25px -5px rgba(0, 0, 0, 0.5);
            padding:.95rem 1.1rem .85rem;
            position:relative; overflow:hidden;
            transition:all .2s ease;
        }
        .pip-card::after {
            content:'';
            position:absolute; bottom:0; left:0; right:0;
            height:3px;
            background:var(--pip-color,#334155);
        }
        .pip-card:hover { border-color:var(--pip-color,#f59e0b); transform:translateY(-2px); }
        .pip-label { font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--pip-color,#94a3b8); margin-bottom:.35rem; }
        .pip-dot   { display:inline-block; width:.45rem; height:.45rem; border-radius:50%; background:var(--pip-color,#94a3b8); margin-right:.35rem; vertical-align:middle; }
        .pip-count { font-size:2rem; font-weight:800; color:#ffffff; font-family:'Outfit', sans-serif; letter-spacing:-.04em; line-height:1; }

        /* Main table card */
        .pg-card {
            background:#0f172a;
            border-radius:1rem;
            border:1px solid rgba(255, 255, 255, 0.08);
            box-shadow:0 10px 25px -5px rgba(0, 0, 0, 0.5);
            overflow:hidden;
        }

        /* Filter bar */
        .filter-bar {
            display:flex; align-items:center; gap:.75rem;
            padding:1rem 1.25rem; border-bottom:1px solid rgba(255, 255, 255, 0.08);
            background:#0b1120; flex-wrap:wrap;
        }
        .filter-bar input, .filter-bar select {
            border:1px solid #334155; border-radius:.6rem;
            padding:.5rem .9rem; font-size:.82rem; color:#ffffff;
            outline:none; transition:border-color .15s;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color:#f59e0b; }
        .filter-bar input { min-width:220px; }

        /* Table */
        .q-table { width:100%; border-collapse:collapse; }
        .q-table thead { background:#0b1120; }
        .q-table thead th {
            padding:.75rem 1.25rem;
            font-size:.68rem; font-weight:800; text-transform:uppercase;
            letter-spacing:.06em; color:#94a3b8; text-align:left; white-space:nowrap;
            border-bottom:1px solid rgba(255, 255, 255, 0.05);
        }
        .q-table thead th.right { text-align:right; }
        .q-table tbody tr { border-bottom:1px solid rgba(255, 255, 255, 0.05); transition:background .12s; }
        .q-table tbody tr:last-child { border-bottom:none; }
        .q-table tbody tr:hover { background:#1e293b; }
        .q-table tbody td { padding:.9rem 1.25rem; vertical-align:middle; }
        .q-table tbody td.right { text-align:right; }

        .cell-no  { font-size:.85rem; font-weight:700; color:#ffffff; }
        .cell-no a { color:#f59e0b; text-decoration:none; transition:color .15s; }
        .cell-no a:hover { color:#fbbf24; }
        .cell-sub { font-size:.72rem; color:#94a3b8; margin-top:.1rem; }
        .cell-text { font-size:.83rem; color:#cbd5e1; }
        .cell-amount { font-size:.9rem; font-weight:800; color:#f59e0b; font-family:'Outfit', sans-serif; }
        .cell-date { font-size:.75rem; color:#94a3b8; white-space:nowrap; font-weight:600; }

        /* Status badges */
        .badge {
            display:inline-flex; align-items:center;
            padding:.2rem .65rem; border-radius:999px;
            font-size:.68rem; font-weight:800;
            text-transform:uppercase; letter-spacing:.04em;
        }
        .badge-draft    { background:rgba(148, 163, 184, 0.15); color:#cbd5e1; border:1px solid rgba(148, 163, 184, 0.3); }
        .badge-sent     { background:rgba(59, 130, 246, 0.15); color:#60a5fa; border:1px solid rgba(59, 130, 246, 0.3); }
        .badge-accepted { background:rgba(16, 185, 129, 0.15); color:#34d399; border:1px solid rgba(16, 185, 129, 0.3); }
        .badge-rejected { background:rgba(244, 63, 94, 0.15); color:#fb7185; border:1px solid rgba(244, 63, 94, 0.3); }
        .badge-expired  { background:rgba(249, 115, 22, 0.15); color:#fb923c; border:1px solid rgba(249, 115, 22, 0.3); }

        .btn-view {
            display:inline-flex; align-items:center; gap:.25rem;
            padding:.25rem .6rem; border-radius:.45rem;
            font-size:.7rem; font-weight:600;
            background:#f5f3ff; color:#6d28d9;
            border:1px solid #c4b5fd; text-decoration:none;
            transition:background .15s;
        }
        .btn-view:hover { background:#ede9fe; }
        .btn-del-q {
            display:inline-flex; align-items:center; gap:.25rem;
            padding:.25rem .6rem; border-radius:.45rem;
            font-size:.7rem; font-weight:600;
            background:#fef2f2; color:#b91c1c;
            border:1px solid #fca5a5; cursor:pointer;
            transition:background .15s; font-family:inherit;
        }
        .btn-del-q:hover { background:#fee2e2; }

        .pg-links { padding:1rem 1.25rem; border-top:1px solid #f1f5f9; }
        .empty-state { text-align:center; padding:3rem 1rem; color:#94a3b8; font-size:.85rem; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div style="margin-bottom:1rem;padding:.85rem 1.25rem;border-radius:.75rem;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;font-size:.85rem;">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Pipeline Summary Strip --}}
            @php
                $statusCounts = $quotations->getCollection()
                    ->groupBy('status')
                    ->map->count();
                $allCounts = $quotations->total() > 0
                    ? \App\Models\Quotation::selectRaw('status, count(*) as total')
                        ->groupBy('status')->pluck('total','status')
                    : collect();
            @endphp

            <div class="pip-grid">
                <div class="pip-card" style="--pip-color:#64748b">
                    <div class="pip-label"><span class="pip-dot"></span>Draft</div>
                    <div class="pip-count">{{ $allCounts['draft'] ?? 0 }}</div>
                </div>
                <div class="pip-card" style="--pip-color:#3b82f6">
                    <div class="pip-label"><span class="pip-dot"></span>Sent</div>
                    <div class="pip-count">{{ $allCounts['sent'] ?? 0 }}</div>
                </div>
                <div class="pip-card" style="--pip-color:#10b981">
                    <div class="pip-label"><span class="pip-dot"></span>Accepted</div>
                    <div class="pip-count">{{ $allCounts['accepted'] ?? 0 }}</div>
                </div>
                <div class="pip-card" style="--pip-color:#ef4444">
                    <div class="pip-label"><span class="pip-dot"></span>Rejected</div>
                    <div class="pip-count">{{ $allCounts['rejected'] ?? 0 }}</div>
                </div>
                <div class="pip-card" style="--pip-color:#f97316">
                    <div class="pip-label"><span class="pip-dot"></span>Expired</div>
                    <div class="pip-count">{{ $allCounts['expired'] ?? 0 }}</div>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="pg-card">

                <form method="GET" action="{{ route('quotations.index') }}" class="filter-bar flex items-center gap-2 flex-wrap">
                    <div class="relative flex-1 min-w-[220px]">
                        <input type="text" name="search" id="q-search" value="{{ request('search') }}" placeholder="Search quotation no, customer, phone…" oninput="filterQuotes()" class="w-full">
                    </div>
                    <select name="status" id="q-status" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                        <option value="accepted" @selected(request('status') === 'accepted')>Accepted</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                    </select>
                    <button type="submit" class="px-3 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg transition shadow">
                        Search
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('quotations.index') }}" class="px-2.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-lg transition">
                            Clear
                        </a>
                    @endif
                    <span id="q-count" style="margin-left:auto;font-size:.75rem;color:#94a3b8;"></span>
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
                                        <div class="cell-text" style="font-weight:600;color:#1e293b">
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
                                        <span class="badge badge-{{ $quotation->status }}">
                                            {{ ucfirst($quotation->status) }}
                                        </span>
                                    </td>
                                    <td class="right">
                                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:.4rem">
                                            <a href="{{ route('quotations.send-whatsapp', $quotation) }}" target="_blank" title="Send Quotation on WhatsApp"
                                               style="display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .6rem;border-radius:.45rem;font-size:.7rem;font-weight:700;background:#dcfce7;color:#15803d;border:1px solid #86efac;text-decoration:none;transition:background .15s;">
                                                <svg style="width:.8rem;height:.8rem" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.458 5.704 1.459h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413"/>
                                                </svg>
                                                WhatsApp
                                            </a>
                                            <a href="{{ route('quotations.show', $quotation) }}" class="btn-view" style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;">
                                                <svg style="width:.75rem;height:.75rem" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                                Email PDF
                                            </a>
                                            <a href="{{ route('quotations.show', $quotation) }}" class="btn-view">
                                                <svg style="width:.75rem;height:.75rem" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                View
                                            </a>
                                            @if($quotation->status === 'draft')
                                            <form method="POST" action="{{ route('quotations.destroy', $quotation) }}"
                                                  onsubmit="return confirm('Delete {{ addslashes($quotation->quotation_no) }}? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-del-q">
                                                    <svg style="width:.75rem;height:.75rem" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
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
                                            <svg style="width:2.5rem;height:2.5rem;color:#cbd5e1;margin:0 auto .75rem" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                                            </svg>
                                            No quotations yet. <a href="{{ route('leads.index') }}" style="color:#6366f1;font-weight:600;">Go to Leads to create one →</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($quotations->hasPages())
                    <div class="pg-links">{{ $quotations->links() }}</div>
                @endif
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