<x-app-layout>
    @php
        $views = [
            ['label' => 'All Suppliers (' . $totalSuppliers . ')', 'url' => route('suppliers.index'), 'active' => !in_array(request('active'), ['yes', 'no'], true)],
            ['label' => 'Active Suppliers (' . $activeSuppliers . ')', 'url' => route('suppliers.index', ['active' => 'yes']), 'active' => request('active') === 'yes'],
            ['label' => 'Inactive Suppliers (' . ($totalSuppliers - $activeSuppliers) . ')', 'url' => route('suppliers.index', ['active' => 'no']), 'active' => request('active') === 'no'],
        ];
        $filterCount = collect(['search'])->filter(fn ($f) => request()->filled($f))->count();
    @endphp

    <x-list-page key="suppliers" :paginator="$suppliers" :views="$views"
        :sorts="['date_desc' => 'Newest first', 'name' => 'Name, A to Z', 'orders_desc' => 'Most orders']"
        :create-url="auth()->user()->isOffice() ? route('suppliers.create') : null" create-label="New Supplier"
        filter-title="Filter Suppliers by" :filter-action="route('suppliers.index')" :keep="['active' => request('active'), 'sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('suppliers.index', array_filter(['active' => request('active')]))"
        :columns="['contact' => 'Contact Person', 'phone' => 'Phone & Email', 'gst' => 'GSTIN & Terms', 'orders' => 'Orders', 'status' => 'Status']"
        empty-text="No suppliers yet." filtered-text="No suppliers match these filters.">

        <x-slot name="actions">
            <a href="{{ route('purchase-orders.index') }}" class="ld-outline plain-link">Purchase Orders</a>
        </x-slot>

        <x-slot name="filters">
            <div class="ld-sub">Name, company, contact, phone or GSTIN</div>
            <input type="text" name="search" value="{{ request('search') }}" class="ld-input" aria-label="Name, company, contact, phone or GSTIN">
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 240px">Supplier</th>
            <th x-show="cols.contact" style="min-width: 160px">Contact Person</th>
            <th x-show="cols.phone" style="min-width: 200px">Phone & Email</th>
            <th x-show="cols.gst" style="min-width: 190px">GSTIN & Terms</th>
            <th x-show="cols.orders" style="min-width: 80px; text-align: right">Orders</th>
            <th x-show="cols.status" style="min-width: 90px">Status</th>
        </x-slot>

        @foreach($suppliers as $supplier)
            <tr>
                <x-list-pick :id="$supplier->id" :label="$supplier->name" />
                <td>
                    <a href="{{ route('suppliers.show', $supplier) }}" class="ld-name plain-link">{{ $supplier->name }}</a>
                    @if($supplier->company_name || $supplier->city)
                        <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ collect([$supplier->company_name, trim(($supplier->city ?? '') . ($supplier->state ? ', ' . $supplier->state : ''), ', ')])->filter()->implode(' · ') }}</div>
                    @endif
                </td>
                <td x-show="cols.contact">{{ $supplier->contact_person ?: '—' }}</td>
                <td x-show="cols.phone">
                    @if($supplier->phone)<div class="whitespace-nowrap">{{ $supplier->phone }}</div>@endif
                    @if($supplier->email)<div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $supplier->email }}</div>@endif
                </td>
                <td x-show="cols.gst">
                    @if($supplier->gst_number)<div class="whitespace-nowrap">{{ $supplier->gst_number }}</div>@endif
                    <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $supplier->payment_terms ?: 'Standard terms' }}</div>
                </td>
                <td x-show="cols.orders" style="text-align: right">{{ $supplier->purchase_orders_count }}</td>
                <td x-show="cols.status">{!! $supplier->is_active ? 'Active' : '<span style="color: var(--ld-muted)">Inactive</span>' !!}</td>
                <td>
                    <div class="ld-row-actions">
                        @if(auth()->user()->isOffice())
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="plain-link" title="Edit" aria-label="Edit {{ $supplier->name }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.2 5.2l3.6 3.6M4 20l4.5-1 10.3-10.3a2.5 2.5 0 00-3.5-3.5L5 15.5 4 20z"/></svg>
                            </a>
                        @endif
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm="Delete supplier {{ $supplier->name }}?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-cancel" title="Delete" aria-label="Delete {{ $supplier->name }}">
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
