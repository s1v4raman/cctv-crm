<x-app-layout>
    @php
        $statuses = \App\Models\RmaClaim::statusLabels();
        $warranties = ['under_warranty' => 'Under warranty', 'out_of_warranty' => 'Out of warranty', 'extended_warranty' => 'Extended warranty'];
        $views = [
            ['label' => 'All Claims (' . $stats['total'] . ')', 'url' => route('rma.index'), 'active' => !request()->filled('status')],
            ['label' => 'Shipped to Vendor (' . $stats['shipped'] . ')', 'url' => route('rma.index', ['status' => 'shipped_to_vendor']), 'active' => request('status') === 'shipped_to_vendor'],
            ['label' => 'In Vendor Repair (' . $stats['with_vendor'] . ')', 'url' => route('rma.index', ['status' => 'in_vendor_repair']), 'active' => request('status') === 'in_vendor_repair'],
            ['label' => 'Draft Claims', 'url' => route('rma.index', ['status' => 'draft']), 'active' => request('status') === 'draft'],
            ['label' => 'Closed Claims', 'url' => route('rma.index', ['status' => 'closed']), 'active' => request('status') === 'closed'],
        ];
        $filterCount = collect(['search', 'status', 'supplier_id', 'warranty'])->filter(fn ($f) => request()->filled($f))->count();
    @endphp

    <x-list-page key="rma" :paginator="$claims" :views="$views"
        :sorts="['date_desc' => 'Newest first', 'date_asc' => 'Oldest first', 'rma_no' => 'RMA number']"
        :create-url="route('rma.create')" create-label="New RMA Claim"
        filter-title="Filter Claims by" :filter-action="route('rma.index')" :keep="['sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('rma.index')"
        :columns="['vendor' => 'Vendor', 'item' => 'Faulty item', 'customer' => 'Customer', 'fault' => 'Fault & warranty', 'status' => 'Status']"
        empty-text="No RMA claims yet." filtered-text="No RMA claims match these filters.">

        <x-slot name="filters">
            <div class="ld-sub">RMA no, serial, vendor ref, customer</div>
            <input type="text" name="search" value="{{ request('search') }}" class="ld-input" aria-label="RMA number, serial, vendor reference or customer">

            <div class="ld-sub">Status</div>
            <select name="status" class="ld-input" aria-label="Status">
                <option value="">Any status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="ld-sub">Warranty at claim</div>
            <select name="warranty" class="ld-input" aria-label="Warranty at claim">
                <option value="">Any</option>
                @foreach($warranties as $value => $label)
                    <option value="{{ $value }}" @selected(request('warranty') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="ld-sub">Vendor</div>
            <select name="supplier_id" class="ld-input" aria-label="Vendor">
                <option value="">Any vendor</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" @selected((string) request('supplier_id') === (string) $sup->id)>{{ $sup->name }}</option>
                @endforeach
            </select>
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 150px">RMA No</th>
            <th x-show="cols.vendor" style="min-width: 180px">Vendor</th>
            <th x-show="cols.item" style="min-width: 200px">Faulty item</th>
            <th x-show="cols.customer" style="min-width: 200px">Customer</th>
            <th x-show="cols.fault" style="min-width: 170px">Fault & warranty</th>
            <th x-show="cols.status" style="min-width: 150px">Status</th>
        </x-slot>

        @foreach($claims as $claim)
            <tr>
                <td class="whitespace-nowrap">
                    <a href="{{ route('rma.show', $claim) }}" class="ld-name plain-link">{{ $claim->rma_no }}</a>
                    <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $claim->created_at->format('d/m/Y') }}</div>
                </td>
                <td x-show="cols.vendor">
                    <div>{{ $claim->supplier->name ?? '—' }}</div>
                    @if($claim->vendor_rma_ref)<div class="text-xs mt-0.5" style="color: var(--ld-muted)">Ref {{ $claim->vendor_rma_ref }}</div>@endif
                </td>
                <td x-show="cols.item">
                    <div>{{ $claim->product?->name ?? 'Hardware item' }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--ld-muted)">S/N {{ $claim->faulty_serial_number }}</div>
                </td>
                <td x-show="cols.customer">
                    @if($claim->lead)
                        <a href="{{ route('leads.show', $claim->lead) }}" class="ld-name plain-link">{{ $claim->lead->customer_name }}</a>
                    @else
                        <span style="color: var(--ld-muted)">Warehouse stock</span>
                    @endif
                </td>
                <td x-show="cols.fault">
                    <div>{{ $claim->fault_category_label }}</div>
                    <div class="text-xs mt-0.5 {{ $claim->warranty_status_at_claim === 'out_of_warranty' ? 'text-amber-700 dark:text-amber-400' : '' }}" style="{{ $claim->warranty_status_at_claim === 'out_of_warranty' ? '' : 'color: var(--ld-muted)' }}">{{ $warranties[$claim->warranty_status_at_claim] ?? ucwords(str_replace('_', ' ', $claim->warranty_status_at_claim)) }}</div>
                </td>
                <td x-show="cols.status"><span class="{{ $claim->status === 'rejected' ? 'text-rose-700 dark:text-rose-400' : '' }}">{{ $claim->status_label }}</span></td>
                <td>
                    <div class="ld-row-actions">
                        @if(auth()->user()->isOffice())
                            <a href="{{ route('rma.edit', $claim) }}" class="plain-link" title="Edit" aria-label="Edit {{ $claim->rma_no }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.2 5.2l3.6 3.6M4 20l4.5-1 10.3-10.3a2.5 2.5 0 00-3.5-3.5L5 15.5 4 20z"/></svg>
                            </a>
                        @endif
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('rma.destroy', $claim) }}" data-confirm="Delete RMA claim {{ $claim->rma_no }}? This cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-cancel" title="Delete" aria-label="Delete {{ $claim->rma_no }}">
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
