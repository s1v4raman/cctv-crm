<x-app-layout>
    @php
        $statuses = ['active' => 'Active', 'pending' => 'Pending', 'expired' => 'Expired', 'cancelled' => 'Cancelled'];
        $views = collect(['' => 'All AMC Contracts'] + $statuses)->map(fn ($label, $value) => [
            'label' => $value === '' ? $label : $label . ' Contracts',
            'url' => route('amcs.index', array_filter(['status' => $value ?: null])),
            'active' => (string) request('status', '') === (string) $value,
        ])->values()->all();
        $filterCount = collect(['search', 'status'])->filter(fn ($f) => request()->filled($f))->count();
    @endphp

    <x-list-page key="amcs" :paginator="$amcs" :views="$views"
        :sorts="['date_desc' => 'Newest first', 'renewal_asc' => 'Ends soonest', 'value_desc' => 'Value, high to low', 'contract_no' => 'Contract number']"
        :create-url="auth()->user()->isOffice() ? route('amcs.create') : null" create-label="New AMC"
        filter-title="Filter AMCs by" :filter-action="route('amcs.index')" :keep="['sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('amcs.index')"
        :columns="['period' => 'Contract Period', 'frequency' => 'Frequency', 'value' => 'Value', 'status' => 'Status']"
        empty-text="No AMC contracts yet." filtered-text="No AMC contracts match these filters.">

        <x-slot name="filters">
            <div class="ld-sub">Contract no, plan or customer</div>
            <input type="text" name="search" value="{{ request('search') }}" class="ld-input" aria-label="Contract number, plan or customer">

            <div class="ld-sub">Status</div>
            <select name="status" class="ld-input" aria-label="Status">
                <option value="">Any status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 150px">Contract No</th>
            <th style="min-width: 230px">Customer</th>
            <th x-show="cols.period" style="min-width: 200px">Contract Period</th>
            <th x-show="cols.frequency" style="min-width: 120px">Frequency</th>
            <th x-show="cols.value" style="min-width: 110px; text-align: right">Value</th>
            <th x-show="cols.status" style="min-width: 110px">Status</th>
        </x-slot>

        <x-slot:mobileCards>
            @foreach ($amcs as $amc)
                @php $endsSoon = $amc->status === 'active' && $amc->end_date && $amc->end_date->isBetween(now(), now()->addDays(30)); @endphp
                <div class="p-3.5 space-y-2.5" :class="selected.includes({{ $amc->id }}) && 'bg-blue-50/40 dark:bg-blue-950/20'">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <input type="checkbox" value="{{ $amc->id }}" x-model.number="selected" aria-label="Select {{ $amc->contract_no }}" class="mt-1 rounded">
                            <div class="min-w-0">
                                <a href="{{ route('amcs.show', $amc) }}" class="ld-name plain-link text-sm font-bold block truncate">{{ $amc->contract_no }}</a>
                                <div class="text-xs text-slate-600 dark:text-slate-300 font-medium truncate">{{ $amc->lead->customer_name ?? '—' }}</div>
                            </div>
                        </div>
                        <span class="shrink-0 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ in_array($amc->status, ['expired', 'cancelled']) ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}">
                            {{ $statuses[$amc->status] ?? ucfirst($amc->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-slate-600 dark:text-slate-300 pl-6">
                        <div><span class="text-slate-400">Value:</span> <span class="font-bold text-slate-900 dark:text-white">{{ \App\Support\Inr::money($amc->value) }}</span></div>
                        <div><span class="text-slate-400">Freq:</span> {{ ucfirst(str_replace('_', ' ', $amc->frequency)) }}</div>
                        <div class="col-span-2 truncate {{ $endsSoon ? 'text-amber-700 dark:text-amber-400' : '' }}"><span class="text-slate-400">Period:</span> {{ $amc->start_date?->format('d/m/Y') }} – {{ $amc->end_date?->format('d/m/Y') }}</div>
                    </div>

                    <div class="flex items-center justify-between gap-2 pl-6 pt-0.5">
                        <a href="{{ route('amcs.show', $amc) }}" class="text-xs font-semibold hover:underline plain-link" style="color: var(--link-color)">View Contract &rarr;</a>
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('amcs.destroy', $amc) }}" data-confirm="Delete AMC contract {{ $amc->contract_no }}?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-rose-600 dark:text-rose-400 hover:underline cursor-pointer">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </x-slot:mobileCards>

        @foreach ($amcs as $amc)
            @php $endsSoon = $amc->status === 'active' && $amc->end_date && $amc->end_date->isBetween(now(), now()->addDays(30)); @endphp
            <tr>
                <x-list-pick :id="$amc->id" :label="$amc->contract_no" />
                <td class="whitespace-nowrap"><a href="{{ route('amcs.show', $amc) }}" class="ld-name plain-link">{{ $amc->contract_no }}</a></td>
                <td>
                    <div>{{ $amc->lead->customer_name ?? '—' }}</div>
                    @if($amc->lead?->phone)<div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $amc->lead->phone }}</div>@endif
                </td>
                <td x-show="cols.period" class="whitespace-nowrap">
                    {{ $amc->start_date?->format('d/m/Y') }} – <span class="{{ $endsSoon ? 'text-amber-700 dark:text-amber-400' : '' }}">{{ $amc->end_date?->format('d/m/Y') }}</span>
                </td>
                <td x-show="cols.frequency">{{ ucfirst(str_replace('_', ' ', $amc->frequency)) }}</td>
                <td x-show="cols.value" class="whitespace-nowrap" style="text-align: right">{{ \App\Support\Inr::money($amc->value) }}</td>
                <td x-show="cols.status"><span class="{{ in_array($amc->status, ['expired', 'cancelled']) ? 'text-rose-700 dark:text-rose-400' : '' }}">{{ $statuses[$amc->status] ?? ucfirst($amc->status) }}</span></td>
                <td>
                    <div class="ld-row-actions">
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('amcs.destroy', $amc) }}" data-confirm="Delete AMC contract {{ $amc->contract_no }}?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-cancel" title="Delete" aria-label="Delete {{ $amc->contract_no }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-list-page>
</x-app-layout>
