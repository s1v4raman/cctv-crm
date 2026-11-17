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
            <div class="ld-sub">Search by Name, Company, Contact, Phone, GSTIN or Brands</div>
            <input type="text" name="search" value="{{ request('search') }}" class="ld-input" placeholder="e.g. Hikvision, Dahua, Chennai..." aria-label="Name, company, contact, phone, GSTIN or brands">
            <div class="flex items-center gap-1.5 flex-wrap mt-2 text-xs">
                <span class="text-slate-500 font-medium">Quick search:</span>
                @foreach(['Hikvision', 'Dahua', 'CP Plus', 'Western Digital', 'Seagate', 'PoE Switch'] as $tag)
                    <a href="{{ route('suppliers.index', array_merge(request()->query(), ['search' => $tag])) }}"
                       class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-950/60 hover:text-blue-600 dark:hover:text-blue-400 border border-slate-200 dark:border-slate-700 transition">
                        {{ $tag }}
                    </a>
                @endforeach
            </div>
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
                    @if($supplier->phone)
                        <div class="whitespace-nowrap flex items-center gap-1.5">
                            <span>{{ $supplier->phone }}</span>
                            <a href="{{ $supplier->getWhatsAppUrl() }}" target="_blank" title="WhatsApp Supplier" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
                                WhatsApp
                            </a>
                        </div>
                    @endif
                    @if($supplier->email)
                        <div class="text-xs mt-0.5 flex items-center gap-1.5" style="color: var(--ld-muted)">
                            <a href="mailto:{{ $supplier->email }}" class="hover:underline">
                                {{ $supplier->email }}
                            </a>
                        </div>
                    @endif
                </td>
                <td x-show="cols.gst">
                    @if($supplier->gst_number)<div class="whitespace-nowrap font-mono text-xs">{{ $supplier->gst_number }}</div>@endif
                    <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $supplier->payment_terms ?: 'Standard terms' }}</div>
                </td>
                <td x-show="cols.orders" style="text-align: right font-mono">{{ $supplier->purchase_orders_count }}</td>
                <td x-show="cols.status">{!! $supplier->is_active ? '<span class="status-won">Active</span>' : '<span class="status-lost">Inactive</span>' !!}</td>
                <td>
                    <div class="ld-row-actions">
                        @if(auth()->user()->isOffice())
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="plain-link text-xs font-semibold" title="Edit" aria-label="Edit {{ $supplier->name }}">
                                Edit
                            </a>
                        @endif
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm="Delete supplier {{ $supplier->name }}?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-cancel text-xs font-semibold" title="Delete" aria-label="Delete {{ $supplier->name }}">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-list-page>
</x-app-layout>
