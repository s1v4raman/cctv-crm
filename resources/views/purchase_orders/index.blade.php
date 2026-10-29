<x-app-layout>
    @php
        $statuses = ['draft' => 'Draft', 'ordered' => 'Ordered', 'partially_received' => 'Partly Received', 'received' => 'Received', 'cancelled' => 'Cancelled'];
        $payments = ['unpaid' => 'Unpaid', 'partially_paid' => 'Partly Paid', 'paid' => 'Paid'];
        $views = collect(['' => 'All Purchase Orders'] + $statuses)->map(fn ($label, $value) => [
            'label' => $value === '' ? $label : $label . ' Orders',
            'url' => route('purchase-orders.index', array_filter(['status' => $value ?: null])),
            'active' => (string) request('status', '') === (string) $value,
        ])->values()->all();
        $filterCount = collect(['search', 'status', 'payment_status', 'supplier_id'])->filter(fn ($f) => request()->filled($f))->count();
    @endphp

    <x-list-page key="purchase-orders" :paginator="$purchaseOrders" :views="$views"
        :sorts="['date_desc' => 'Order date, newest', 'date_asc' => 'Order date, oldest', 'total_desc' => 'Total, high to low', 'po_number' => 'PO number']"
        :create-url="auth()->user()->isOffice() ? route('purchase-orders.create') : null" create-label="New Purchase Order"
        filter-title="Filter Orders by" :filter-action="route('purchase-orders.index')" :keep="['sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('purchase-orders.index')"
        :columns="['date' => 'Order Date', 'received' => 'Received', 'total' => 'Total', 'status' => 'Status', 'payment' => 'Payment']"
        empty-text="No purchase orders yet." filtered-text="No purchase orders match these filters.">

        <x-slot name="actions">
            <a href="{{ route('suppliers.index') }}" class="ld-outline plain-link">Suppliers</a>
        </x-slot>

        <x-slot name="filters">
            <div class="ld-sub">PO no, supplier or item</div>
            <input type="text" name="search" value="{{ request('search') }}" class="ld-input" aria-label="PO number, supplier or item">

            <div class="ld-sub">Status</div>
            <select name="status" class="ld-input" aria-label="Status">
                <option value="">Any status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="ld-sub">Payment</div>
            <select name="payment_status" class="ld-input" aria-label="Payment">
                <option value="">Any payment</option>
                @foreach($payments as $value => $label)
                    <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="ld-sub">Supplier</div>
            <select name="supplier_id" class="ld-input" aria-label="Supplier">
                <option value="">Any supplier</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" @selected((string) request('supplier_id') === (string) $sup->id)>{{ $sup->name }}</option>
                @endforeach
            </select>
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 150px">PO No</th>
            <th style="min-width: 220px">Supplier</th>
            <th x-show="cols.date" style="min-width: 120px">Order Date</th>
            <th x-show="cols.received" style="min-width: 110px">Received</th>
            <th x-show="cols.total" style="min-width: 130px; text-align: right">Total</th>
            <th x-show="cols.status" style="min-width: 130px">Status</th>
            <th x-show="cols.payment" style="min-width: 110px">Payment</th>
        </x-slot>

        @foreach($purchaseOrders as $po)
            @php
                $ordered = $po->items->sum('quantity_ordered');
                $received = $po->items->sum('quantity_received');
                $due = $po->balanceDue();
            @endphp
            <tr>
                <td class="whitespace-nowrap"><a href="{{ route('purchase-orders.show', $po) }}" class="ld-name plain-link">{{ $po->po_number }}</a></td>
                <td>
                    <div>{{ $po->supplier->name ?? '—' }}</div>
                    @if($po->supplier?->phone)<div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $po->supplier->phone }}</div>@endif
                </td>
                <td x-show="cols.date" class="whitespace-nowrap">{{ $po->order_date?->format('d/m/Y') }}</td>
                <td x-show="cols.received" class="whitespace-nowrap">{{ $received + 0 }} of {{ $ordered + 0 }}</td>
                <td x-show="cols.total" class="whitespace-nowrap" style="text-align: right">
                    <div>{{ \App\Support\Inr::money($po->total) }}</div>
                    @if($due > 0)<div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ \App\Support\Inr::money($due) }} due</div>@endif
                </td>
                <td x-show="cols.status"><span class="{{ $po->status === 'cancelled' ? 'text-rose-700 dark:text-rose-400' : '' }}">{{ $statuses[$po->status] ?? ucfirst(str_replace('_', ' ', $po->status)) }}</span></td>
                <td x-show="cols.payment"><span class="{{ $po->payment_status === 'unpaid' && $po->status !== 'draft' ? 'text-amber-700 dark:text-amber-400' : '' }}">{{ $payments[$po->payment_status] ?? ucfirst(str_replace('_', ' ', $po->payment_status)) }}</span></td>
                <td>
                    <div class="ld-row-actions">
                        @if(auth()->user()->isOffice())
                            <a href="{{ route('purchase-orders.edit', $po) }}" class="plain-link" title="Edit" aria-label="Edit {{ $po->po_number }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.2 5.2l3.6 3.6M4 20l4.5-1 10.3-10.3a2.5 2.5 0 00-3.5-3.5L5 15.5 4 20z"/></svg>
                            </a>
                        @endif
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('purchase-orders.destroy', $po) }}" data-confirm="Delete purchase order {{ $po->po_number }}? This cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-cancel" title="Delete" aria-label="Delete {{ $po->po_number }}">
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
