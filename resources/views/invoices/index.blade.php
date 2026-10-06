<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Invoices &amp; Collections</h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-500/30">Billing Ledger</span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Track client billing, GST invoices, recorded online transactions &amp; outstanding balance ledgers</p>
            </div>
            <a href="{{ route('jobs.index') }}" class="btn-dark">
                <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Jobs to Invoice
            </a>
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

        .p-table { width:100%; border-collapse:collapse; }
        .p-table thead th.right { text-align:right; }
        .p-table tbody td.right { text-align:right; }

        .cell-no { font-weight:800; font-family:ui-monospace,monospace; color:#2563eb; text-decoration:none; }
        .cell-no:hover { color:#1d4ed8; text-decoration:underline; }
        html.dark .cell-no { color:#f59e0b; }
        html.dark .cell-no:hover { color:#fbbf24; }
        .cell-name { font-weight:700; color:#0f172a; }
        html.dark .cell-name { color:#ffffff; }

        .badge { display:inline-flex; padding:.25rem .75rem; border-radius:9999px; font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
        .badge-unpaid         { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
        .badge-partially_paid { background:#fffbeb; color:#d97706; border:1px solid #fde68a; }
        .badge-paid           { background:#ecfdf5; color:#059669; border:1px solid #d1fae5; }
        .badge-overdue        { background:#fef2f2; color:#dc2626; border:1px solid #fee2e2; }

        html.dark .badge-unpaid         { background:rgba(148,163,184,.15); color:#cbd5e1; border-color:rgba(148,163,184,.3); }
        html.dark .badge-partially_paid { background:rgba(245,158,11,.15); color:#fbbf24; border-color:rgba(245,158,11,.3); }
        html.dark .badge-paid           { background:rgba(16,185,129,.15); color:#34d399; border-color:rgba(16,185,129,.3); }
        html.dark .badge-overdue        { background:rgba(244,63,94,.15); color:#fb7185; border-color:rgba(244,63,94,.3); }

        .btn-view {
            display:inline-flex; align-items:center; gap:0.3rem;
            padding:0.35rem 0.75rem; border-radius:0.5rem;
            font-size:0.75rem; font-weight:700;
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

            {{-- Finance Category Sub-Navigation --}}
            <x-finance-subnav active="invoices" />

            {{-- Stat Summary Cards --}}
            @php
                $totalInvoiced = \App\Models\Invoice::sum('total');
                $totalCollected = \App\Models\Invoice::sum('amount_paid');
                $totalOutstanding = max($totalInvoiced - $totalCollected, 0);
                $overdueCount = \App\Models\Invoice::where('status', 'overdue')->count();
            @endphp
            <div class="stat-grid">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Invoiced</div>
                        <div class="stat-value text-indigo-600">₹{{ number_format($totalInvoiced, 2) }}</div>
                        <span class="text-xs text-gray-500 font-medium">Billed revenue</span>
                    </div>
                    <div class="stat-icon bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Collected</div>
                        <div class="stat-value text-emerald-600">₹{{ number_format($totalCollected, 2) }}</div>
                        <span class="text-xs text-emerald-600 font-medium">Received payments</span>
                    </div>
                    <div class="stat-icon bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Outstanding Balance</div>
                        <div class="stat-value text-amber-600">₹{{ number_format($totalOutstanding, 2) }}</div>
                        <span class="text-xs text-amber-600 font-medium">Pending receivables</span>
                    </div>
                    <div class="stat-icon bg-amber-50 dark:bg-amber-950/50 text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Overdue Invoices</div>
                        <div class="stat-value text-red-600">{{ $overdueCount }}</div>
                        <span class="text-xs text-red-500 font-medium">Past due date</span>
                    </div>
                    <div class="stat-icon bg-red-50 dark:bg-red-950/50 text-red-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="pg-card">
                {{-- Search & filter bar --}}
                <form method="GET" action="{{ route('invoices.index') }}" class="p-4 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b1120] flex items-center gap-2 flex-wrap">
                    <div class="relative flex-1 min-w-[220px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoice #, customer, phone, address…" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 py-2">
                    </div>
                    <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 py-2">
                        <option value="">All Statuses</option>
                        <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                        <option value="partially_paid" @selected(request('status') === 'partially_paid')>Partially Paid</option>
                        <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                        <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
                    </select>
                    <button type="submit" class="px-3 py-2 text-white font-bold text-xs rounded-xl transition shadow" style="background-color: var(--crm-accent, #2563eb);">
                        Search
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('invoices.index') }}" class="px-2.5 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                            Clear
                        </a>
                    @endif
                </form>

                @if($invoices->isEmpty())
                    <div class="p-12 text-center">
                        <p class="font-bold text-slate-900 dark:text-white text-base mb-1">No Invoices Generated Yet</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Complete an installation job to generate customer invoices.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="p-table">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Customer / Premise</th>
                                    <th>Invoice & Due Date</th>
                                    <th class="right">Total (Incl. Tax)</th>
                                    <th class="right">Amount Paid</th>
                                    <th class="right">Balance Due</th>
                                    <th>Status</th>
                                    <th class="right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr>
                                        <td>
                                            <a href="{{ route('invoices.show', $invoice) }}" class="cell-no">{{ $invoice->invoice_no }}</a>
                                        </td>
                                        <td>
                                            <span class="cell-name">{{ $invoice->quotation->lead->customer_name ?? '—' }}</span>
                                            @if($invoice->installationJob)
                                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Job: {{ $invoice->installationJob->job_no }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $invoice->invoice_date->format('d M Y') }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Due: {{ $invoice->due_date?->format('d M Y') ?? 'Immediate' }}</div>
                                        </td>
                                        <td class="right font-bold text-slate-900 dark:text-white">
                                            ₹{{ number_format($invoice->total, 2) }}
                                        </td>
                                        <td class="right font-bold text-emerald-600">
                                            ₹{{ number_format($invoice->amount_paid, 2) }}
                                        </td>
                                        <td class="right font-extrabold" style="color:{{ $invoice->balanceDue() > 0 ? '#b91c1c' : '#059669' }}">
                                            ₹{{ number_format($invoice->balanceDue(), 2) }}
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $invoice->status }}">
                                                {{ ucfirst(str_replace('_',' ',$invoice->status)) }}
                                            </span>
                                        </td>
                                        <td class="right">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="btn-view">
                                                View & Record &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($invoices->hasPages())
                        <div class="pg-links">{{ $invoices->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
