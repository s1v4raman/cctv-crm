<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Invoices</h2>
            </div>
            <a href="{{ route('jobs.index') }}" class="px-3.5 py-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                Jobs to Invoice
            </a>
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

        .p-table { width: 100%; border-collapse: collapse; }
        .p-table thead { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        html.dark .p-table thead { background: #0b1120; border-color: #1e293b; }
        .p-table thead th {
            padding: 0.75rem 1rem;
            font-size: 0.8rem; font-weight: 600; text-transform: none;
            color: #475569; text-align: left; white-space: nowrap;
        }
        html.dark .p-table thead th { color: #94a3b8; }
        .p-table thead th.right { text-align: right; }
        .p-table tbody tr { border-bottom: 1px solid #e2e8f0; background: #ffffff; }
        html.dark .p-table tbody tr { border-bottom-color: #1e293b; background: #0f172a; }
        .p-table tbody tr:last-child { border-bottom: none; }
        .p-table tbody tr:hover { background: #f8fafc; }
        html.dark .p-table tbody tr:hover { background: #1e293b; }
        .p-table tbody td { padding: 0.75rem 1rem; vertical-align: middle; font-size: 0.85rem; color: #475569; }
        html.dark .p-table tbody td { color: #cbd5e1; }
        .p-table tbody td.right { text-align: right; }

        .cell-no { font-weight: 600; font-family: ui-monospace,monospace; color: #2563eb; text-decoration: none; }
        .cell-no:hover { text-decoration: underline; }
        .cell-name { font-weight: 600; color: #0f172a; }
        html.dark .cell-name { color: #ffffff; }

        /* Status: Plain bold colored text */
        .badge { font-size: 0.85rem; font-weight: 700; background: transparent !important; border: none !important; padding: 0 !important; }
        .badge-unpaid         { color: #64748b !important; }
        .badge-partially_paid { color: #d97706 !important; }
        .badge-paid           { color: #16a34a !important; }
        .badge-overdue        { color: #dc2626 !important; }

        .pg-links { padding: 0.75rem 1rem; border-top: 1px solid #e2e8f0; }
        html.dark .pg-links { border-top-color: #1e293b; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">
            
            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold">
                    {{ session('status') }}
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
            <div class="stat-grid mt-4">
                <div class="stat-card">
                    <div class="stat-label">Total Invoiced</div>
                    <div class="stat-value">₹{{ number_format($totalInvoiced, 2) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Total Collected</div>
                    <div class="stat-value text-emerald-600">₹{{ number_format($totalCollected, 2) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Outstanding Balance</div>
                    <div class="stat-value text-amber-600">₹{{ number_format($totalOutstanding, 2) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Overdue Invoices</div>
                    <div class="stat-value text-red-600">{{ $overdueCount }}</div>
                </div>
            </div>

            <div class="pg-card">
                {{-- Search & filter bar --}}
                <form method="GET" action="{{ route('invoices.index') }}" class="p-3.5 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b1120] flex items-center gap-2 flex-wrap">
                    <div class="relative flex-1 min-w-[220px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoice #, customer, phone…" class="w-full text-sm h-[38px] rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 focus:outline-none focus:border-blue-500">
                    </div>
                    <select name="status" onchange="this.form.submit()" class="text-sm h-[38px] rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 focus:outline-none focus:border-blue-500">
                        <option value="">All Statuses</option>
                        <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                        <option value="partially_paid" @selected(request('status') === 'partially_paid')>Partially Paid</option>
                        <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                        <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
                    </select>
                    <button type="submit" class="h-[38px] px-4 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                        Search
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('invoices.index') }}" class="h-[38px] px-3.5 inline-flex items-center rounded border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium transition">
                            Clear
                        </a>
                    @endif
                </form>

                @if($invoices->isEmpty())
                    <div class="p-12 text-center">
                        <p class="font-semibold text-slate-800 dark:text-white text-sm mb-1">No Invoices Generated Yet</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Complete an installation job to generate customer invoices.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="p-table">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Customer</th>
                                    <th>Invoice &amp; Due Date</th>
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
                                                <div class="text-xs text-slate-500 mt-0.5">Job: {{ $invoice->installationJob->job_no }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-xs text-slate-800 dark:text-slate-200">{{ $invoice->invoice_date->format('d M Y') }}</div>
                                            <div class="text-[11px] text-slate-500">Due: {{ $invoice->due_date?->format('d M Y') ?? 'Immediate' }}</div>
                                        </td>
                                        <td class="right font-semibold text-slate-900 dark:text-white">
                                            ₹{{ number_format($invoice->total, 2) }}
                                        </td>
                                        <td class="right font-semibold text-emerald-600">
                                            ₹{{ number_format($invoice->amount_paid, 2) }}
                                        </td>
                                        <td class="right font-semibold" style="color:{{ $invoice->balanceDue() > 0 ? '#dc2626' : '#16a34a' }}">
                                            ₹{{ number_format($invoice->balanceDue(), 2) }}
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $invoice->status }}">
                                                {{ ucfirst(str_replace('_',' ',$invoice->status)) }}
                                            </span>
                                        </td>
                                        <td class="right whitespace-nowrap text-sm" style="text-align:right;">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:underline">View</a>
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
