<x-app-layout>
    @php
        $statuses = ['unpaid' => 'Unpaid', 'partially_paid' => 'Partly Paid', 'paid' => 'Paid', 'overdue' => 'Overdue'];
        $views = collect(['' => 'All Invoices'] + $statuses)->map(fn ($label, $value) => [
            'label' => $value === '' ? $label : $label . ' Invoices',
            'url' => route('invoices.index', array_filter(['status' => $value ?: null])),
            'active' => (string) request('status', '') === (string) $value,
        ])->values()->all();
        $filterCount = collect(['search', 'status'])->filter(fn ($f) => request()->filled($f))->count();
    @endphp

    <x-list-page key="invoices" :paginator="$invoices" :views="$views"
        :sorts="['date_desc' => 'Invoice date, newest', 'date_asc' => 'Invoice date, oldest', 'due_asc' => 'Due date, soonest', 'total_desc' => 'Total, high to low']"
        filter-title="Filter Invoices by" :filter-action="route('invoices.index')" :keep="['sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('invoices.index')"
        :columns="['date' => 'Date', 'total' => 'Total', 'balance' => 'Balance', 'status' => 'Status']"
        empty-text="No invoices yet." filtered-text="No invoices match these filters.">

        <x-slot name="above"><x-finance-subnav active="invoices" /></x-slot>

        <x-slot name="actions">
            <a href="{{ route('jobs.index') }}" class="ld-outline plain-link">Jobs to Invoice</a>
        </x-slot>

        <x-slot name="filters">
            <div class="ld-sub">Invoice no, customer or phone</div>
            <input type="text" name="search" value="{{ request('search') }}" class="ld-input" aria-label="Invoice number, customer or phone">

            <div class="ld-sub">Status</div>
            <select name="status" class="ld-input" aria-label="Status">
                <option value="">Any status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 150px">Invoice</th>
            <th style="min-width: 230px">Customer</th>
            <th x-show="cols.date" style="min-width: 130px">Date</th>
            <th x-show="cols.total" style="min-width: 120px; text-align: right">Total</th>
            <th x-show="cols.balance" style="min-width: 120px; text-align: right">Balance</th>
            <th x-show="cols.status" style="min-width: 150px">Status</th>
        </x-slot>

        <x-slot:mobileCards>
            @foreach ($invoices as $invoice)
                @php
                    $balance = $invoice->balanceDue();
                    $isOverdue = $balance > 0 && $invoice->due_date && $invoice->due_date->isPast();
                    $statusLabel = $statuses[strtolower($invoice->status)] ?? ucfirst(str_replace('_', ' ', $invoice->status));
                @endphp
                <div class="p-3.5 space-y-2.5" :class="selected.includes({{ $invoice->id }}) && 'bg-blue-50/40 dark:bg-blue-950/20'">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <input type="checkbox" value="{{ $invoice->id }}" x-model.number="selected" aria-label="Select {{ $invoice->invoice_no }}" class="mt-1 rounded">
                            <div class="min-w-0">
                                <a href="{{ route('invoices.show', $invoice) }}" class="ld-name plain-link text-sm font-bold block truncate">{{ $invoice->invoice_no }}</a>
                                <div class="text-xs text-slate-600 dark:text-slate-300 font-medium truncate">{{ $invoice->quotation->lead->customer_name ?? '—' }}</div>
                            </div>
                        </div>
                        <span class="shrink-0 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $isOverdue ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}">
                            {{ $isOverdue && strtolower($invoice->status) !== 'overdue' ? $statusLabel . ' · Overdue' : $statusLabel }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-slate-600 dark:text-slate-300 pl-6">
                        <div><span class="text-slate-400">Total:</span> <span class="font-bold text-slate-900 dark:text-white">{{ \App\Support\Inr::money($invoice->total) }}</span></div>
                        <div><span class="text-slate-400">Balance:</span> <span class="font-semibold {{ $balance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $balance > 0 ? \App\Support\Inr::money($balance) : 'Paid' }}</span></div>
                        <div class="col-span-2 truncate"><span class="text-slate-400">Date:</span> {{ $invoice->invoice_date->format('d/m/Y') }}@if($invoice->due_date) · Due {{ $invoice->due_date->format('d/m/Y') }}@endif</div>
                    </div>

                    <div class="flex items-center justify-end pl-6 pt-0.5">
                        <a href="{{ route('invoices.show', $invoice) }}" class="text-xs font-semibold hover:underline plain-link" style="color: var(--link-color)">View Invoice &rarr;</a>
                    </div>
                </div>
            @endforeach
        </x-slot:mobileCards>

        @foreach ($invoices as $invoice)
            @php
                $balance = $invoice->balanceDue();
                $isOverdue = $balance > 0 && $invoice->due_date && $invoice->due_date->isPast();
                $statusLabel = $statuses[strtolower($invoice->status)] ?? ucfirst(str_replace('_', ' ', $invoice->status));
            @endphp
            <tr>
                <x-list-pick :id="$invoice->id" :label="$invoice->invoice_no" />
                <td class="whitespace-nowrap"><a href="{{ route('invoices.show', $invoice) }}" class="ld-name plain-link">{{ $invoice->invoice_no }}</a></td>
                <td>
                    <div>{{ $invoice->quotation->lead->customer_name ?? '—' }}</div>
                    @if($invoice->installationJob)<div class="text-xs mt-0.5" style="color: var(--ld-muted)">Job {{ $invoice->installationJob->job_no }}</div>@endif
                </td>
                <td x-show="cols.date" class="whitespace-nowrap">
                    <div>{{ $invoice->invoice_date->format('d/m/Y') }}</div>
                    <div class="text-xs {{ $isOverdue ? 'text-amber-700 dark:text-amber-400' : '' }}" style="{{ $isOverdue ? '' : 'color: var(--ld-muted)' }}">
                        {{ $invoice->due_date ? ($isOverdue ? 'was due ' : 'due ') . $invoice->due_date->format('d/m/Y') : 'due on receipt' }}
                    </div>
                </td>
                <td x-show="cols.total" class="whitespace-nowrap" style="text-align: right">{{ \App\Support\Inr::money($invoice->total) }}</td>
                <td x-show="cols.balance" class="whitespace-nowrap" style="text-align: right">
                    @if($balance > 0){{ \App\Support\Inr::money($balance) }}@else<span style="color: var(--ld-muted)">—</span>@endif
                </td>
                <td x-show="cols.status">
                    <span class="{{ $isOverdue ? 'text-amber-700 dark:text-amber-400' : '' }}">{{ $isOverdue && strtolower($invoice->status) !== 'overdue' ? $statusLabel . ', overdue' : $statusLabel }}</span>
                </td>
                <td></td>
            </tr>
        @endforeach
    </x-list-page>
</x-app-layout>
