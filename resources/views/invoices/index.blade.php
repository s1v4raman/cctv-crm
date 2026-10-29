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

        @foreach ($invoices as $invoice)
            @php
                $balance = $invoice->balanceDue();
                $isOverdue = $balance > 0 && $invoice->due_date && $invoice->due_date->isPast();
                $statusLabel = $statuses[strtolower($invoice->status)] ?? ucfirst(str_replace('_', ' ', $invoice->status));
            @endphp
            <tr>
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
