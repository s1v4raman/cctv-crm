<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Invoices</h2>
            </div>
            <a href="{{ route('jobs.index') }}" class="btn-secondary">
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

        .cell-no { font-weight: 500; color: #2563eb; text-decoration: none; }
        .cell-no:hover { text-decoration: underline; }
        .cell-name { font-weight: 600; color: var(--text-main); }
        html.dark .cell-name { color: #ffffff; }

        /* Status: Plain bold colored text */
        .badge { font-size: 0.85rem; font-weight: 700; background: transparent !important; border: none !important; padding: 0 !important; }

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

            {{-- Finance Category Sub-Navigation --}}
            <x-finance-subnav active="invoices" />

            <div class="pg-card mt-4">
                {{-- Search & filter bar - Uniform 36px Height Level --}}
                <form data-auto-submit method="GET" action="{{ route('invoices.index') }}" class="p-2 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b1120] flex items-center gap-2 flex-wrap text-xs">
                    <div class="relative w-60 min-w-[180px] shrink-0">
                        <input aria-label="Search invoice #, customer, phone" type="text" name="search" value="{{ request('search') }}" placeholder="Search invoice #, customer, phone…" class="w-full text-xs h-[36px] rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 focus:outline-none focus:border-blue-500">
                    </div>
                    <select aria-label="Status" name="status" onchange="this.form.submit()" class="text-xs h-[36px] rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 focus:outline-none focus:border-blue-500 shrink-0">
                        <option value="">All Statuses</option>
                        <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                        <option value="partially_paid" @selected(request('status') === 'partially_paid')>Partially Paid</option>
                        <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                        <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
                    </select>
                    <button type="submit" style="height: 36px !important; min-height: 36px !important; max-height: 36px !important;" class="px-4 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition inline-flex items-center justify-center shrink-0 shadow-xs cursor-pointer">
                        Search
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('invoices.index') }}" style="height: 36px !important; min-height: 36px !important; max-height: 36px !important;" class="px-3 inline-flex items-center rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-medium transition shrink-0">
                            Clear
                        </a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="crm-table p-table crm-responsive">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th class="col-name" style="min-width: 200px;">Customer</th>
                                <th class="col-date whitespace-nowrap">Date</th>
                                <th class="right">Total</th>
                                <th class="right">Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Rupees without paise unless the amount has some --}}
                            @php $money = fn ($v) => \App\Support\Inr::format($v, fmod((float) $v, 1) != 0.0 ? 2 : 0); @endphp
                            @forelse ($invoices as $invoice)
                                @php
                                    $balance = $invoice->balanceDue();
                                    $isOverdue = $balance > 0 && $invoice->due_date && $invoice->due_date->isPast();
                                    $statusLabel = match (strtolower($invoice->status)) {
                                        'partially_paid' => 'Partly paid',
                                        default => ucfirst(str_replace('_', ' ', $invoice->status)),
                                    };
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="cell-no">{{ $invoice->invoice_no }}</a>
                                    </td>
                                    <td class="col-name" style="min-width: 200px;">
                                        <span class="cell-name">{{ $invoice->quotation->lead->customer_name ?? '—' }}</span>
                                        @if($invoice->installationJob)
                                            <div class="text-xs text-slate-500 mt-0.5">Job {{ $invoice->installationJob->job_no }}</div>
                                        @endif
                                    </td>
                                    <td class="col-date whitespace-nowrap">
                                        <div class="text-slate-800 dark:text-slate-200">{{ $invoice->invoice_date->format('j M Y') }}</div>
                                        <div class="text-xs {{ $isOverdue ? 'text-amber-700 dark:text-amber-400' : 'text-slate-500' }}">
                                            {{ $invoice->due_date ? ($isOverdue ? 'was due ' : 'due ') . $invoice->due_date->format('j M') : 'due on receipt' }}
                                        </div>
                                    </td>
                                    <td class="right whitespace-nowrap text-slate-900 dark:text-white">{{ $money($invoice->total) }}</td>
                                    <td class="right whitespace-nowrap {{ $balance > 0 ? 'font-medium text-slate-900 dark:text-white' : 'text-slate-400' }}">
                                        {{ $balance > 0 ? $money($balance) : '—' }}
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <span class="{{ $isOverdue ? 'text-amber-700 dark:text-amber-400' : 'text-slate-600 dark:text-slate-300' }}">{{ $isOverdue && strtolower($invoice->status) !== 'overdue' ? $statusLabel . ', overdue' : $statusLabel }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-slate-500 text-sm">
                                        <x-empty-message empty="No invoices yet" filtered="No invoices match these filters." :filters="['search', 'status']" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Total count shown below the table --}}
                <div class="px-4 py-2.5 border-t border-[#ddd] dark:border-slate-800 bg-[#fbfbfb] dark:bg-slate-900/60 flex items-center justify-between text-xs text-slate-500">
                    <span class="font-medium">Total: {{ $invoices->total() }}</span>
                    @if($invoices->hasPages())
                        <div class="text-xs">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
