<x-app-layout>
    @php
        $views = [
            ['label' => 'All Suppliers (' . $totalSuppliers . ')', 'url' => route('suppliers.index'), 'active' => !in_array(request('active'), ['yes', 'no'], true)],
            ['label' => 'Active Suppliers (' . $activeSuppliers . ')', 'url' => route('suppliers.index', ['active' => 'yes']), 'active' => request('active') === 'yes'],
            ['label' => 'Inactive Suppliers (' . ($totalSuppliers - $activeSuppliers) . ')', 'url' => route('suppliers.index', ['active' => 'no']), 'active' => request('active') === 'no'],
        ];
        $filterCount = collect(['search', 'city', 'payment_terms', 'active'])->filter(fn ($f) => request()->filled($f))->count();
        $commonBrands = ['Hikvision', 'Dahua', 'Western Digital', 'CP Plus', 'Seagate', 'PoE Switch', 'Matrix Comsec', 'Honeywell'];
    @endphp

    <x-list-page key="suppliers" :paginator="$suppliers" :views="$views"
        :sorts="['date_desc' => 'Newest first', 'name' => 'Name, A to Z', 'orders_desc' => 'Most orders']"
        :create-url="auth()->user()->isOffice() ? route('suppliers.create') : null" create-label="New Supplier"
        filter-title="Filter Suppliers by" :filter-action="route('suppliers.index')" 
        :keep="['sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" 
        :clear-url="route('suppliers.index')"
        :columns="['contact' => 'Contact Person', 'phone' => 'Phone & Email', 'gst' => 'GSTIN & Terms', 'orders' => 'Orders', 'status' => 'Status']"
        empty-text="No suppliers yet." filtered-text="No suppliers match these filters.">

        {{-- Standardized Search Bar & Filter Layout (Medium-Sized Labels & Downward Dropdowns) --}}
        <x-slot name="above">
            <div class="mb-4 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs">
                <form method="GET" action="{{ route('suppliers.index') }}" class="space-y-3">
                    @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                    @if(request('per_page'))<input type="hidden" name="per_page" value="{{ request('per_page') }}">@endif

                    {{-- Search Input & Downward Dropdown Filters Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                        {{-- Medium-sized label layout: Supplier Search --}}
                        <div class="sm:col-span-2 lg:col-span-4">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Search Supplier
                            </label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Search by name, company, contact, phone, GSTIN or brand..."
                                   class="w-full px-3.5 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        </div>

                        {{-- Downward dropdown: City / Location --}}
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                City / Location
                            </label>
                            <select name="city" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option value="">All Locations</option>
                                @foreach($cities as $cityOption)
                                    <option value="{{ $cityOption }}" @selected(request('city') === $cityOption)>{{ $cityOption }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Downward dropdown: Active Status --}}
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Active Status
                            </label>
                            <select name="active" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option value="" @selected(!in_array(request('active'), ['yes', 'no']))>All Statuses</option>
                                <option value="yes" @selected(request('active') === 'yes')>Active Only</option>
                                <option value="no" @selected(request('active') === 'no')>Inactive Only</option>
                            </select>
                        </div>

                        {{-- Downward dropdown: Payment Terms --}}
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Payment Terms
                            </label>
                            <select name="payment_terms" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option value="">All Payment Terms</option>
                                @foreach($paymentTermsList as $termOption)
                                    <option value="{{ $termOption }}" @selected(request('payment_terms') === $termOption)>{{ $termOption }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- High-Contrast Action Buttons --}}
                        <div class="lg:col-span-2 flex items-center gap-2">
                            <button type="submit" class="flex-1 py-2 px-3 rounded-lg text-xs font-bold shadow-xs cursor-pointer text-center inline-flex items-center justify-center transition bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white" style="background-color: #2563eb !important; color: #ffffff !important; border: 1px solid #1d4ed8 !important;">
                                <span style="color: #ffffff !important; font-weight: 700;">Filter</span>
                            </button>
                            @if(request()->filled('search') || request()->filled('city') || request()->filled('payment_terms') || in_array(request('active'), ['yes', 'no']))
                                <a href="{{ route('suppliers.index') }}" class="btn-secondary py-2 px-3 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition text-center" title="Reset all filters">
                                    <span>Reset</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Clickable Quick-Search Tags for Common Vendors / Brands --}}
                    <div class="flex items-center gap-1.5 flex-wrap pt-2 border-t border-slate-100 dark:border-slate-800/80 text-xs">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mr-1">Quick Brand Tags:</span>
                        @foreach($commonBrands as $tag)
                            @php
                                $isActiveTag = request('search') === $tag;
                            @endphp
                            <a href="{{ route('suppliers.index', array_merge(request()->query(), ['search' => $isActiveTag ? null : $tag, 'page' => null])) }}"
                               class="px-2.5 py-1 rounded-md text-xs font-semibold transition border cursor-pointer {{ $isActiveTag ? 'bg-blue-600 text-white border-blue-600 shadow-xs font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-950/60 hover:text-blue-600 dark:hover:text-blue-400 border-slate-200 dark:border-slate-700' }}">
                                <span>{{ $tag }}</span>
                                @if($isActiveTag) <span class="font-bold ml-0.5">&times;</span> @endif
                            </a>
                        @endforeach
                        @if(request()->filled('search') && !in_array(request('search'), $commonBrands))
                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 flex items-center gap-1">
                                <span>"{{ request('search') }}"</span>
                                <a href="{{ route('suppliers.index', array_merge(request()->query(), ['search' => null, 'page' => null])) }}" class="text-blue-500 hover:text-blue-700 font-bold">&times;</a>
                            </span>
                        @endif
                    </div>
                </form>
            </div>
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('purchase-orders.index') }}" class="ld-outline plain-link font-semibold">Purchase Orders</a>
        </x-slot>

        {{-- Synchronized Slide-Out Filter Panel --}}
        <x-slot name="filters">
            <div class="ld-sub">Search Supplier</div>
            <input type="text" name="search" value="{{ request('search') }}" class="ld-input" placeholder="e.g. Hikvision, Dahua, Chennai..." aria-label="Search Supplier">

            <div class="ld-sub">City / Location</div>
            <select name="city" class="ld-input" aria-label="City / Location">
                <option value="">All Locations</option>
                @foreach($cities as $cityOption)
                    <option value="{{ $cityOption }}" @selected(request('city') === $cityOption)>{{ $cityOption }}</option>
                @endforeach
            </select>

            <div class="ld-sub">Active Status</div>
            <select name="active" class="ld-input" aria-label="Active Status">
                <option value="" @selected(!in_array(request('active'), ['yes', 'no']))>All Statuses</option>
                <option value="yes" @selected(request('active') === 'yes')>Active Only</option>
                <option value="no" @selected(request('active') === 'no')>Inactive Only</option>
            </select>

            <div class="ld-sub">Payment Terms</div>
            <select name="payment_terms" class="ld-input" aria-label="Payment Terms">
                <option value="">All Payment Terms</option>
                @foreach($paymentTermsList as $termOption)
                    <option value="{{ $termOption }}" @selected(request('payment_terms') === $termOption)>{{ $termOption }}</option>
                @endforeach
            </select>

            <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Quick Brand Tags</div>
                <div class="flex items-center gap-1.5 flex-wrap text-xs">
                    @foreach($commonBrands as $tag)
                        <a href="{{ route('suppliers.index', array_merge(request()->query(), ['search' => $tag, 'page' => null])) }}"
                           class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-950/60 hover:text-blue-600 dark:hover:text-blue-400 border border-slate-200 dark:border-slate-700 transition">
                            {{ $tag }}
                        </a>
                    @endforeach
                </div>
            </div>
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 240px">Supplier</th>
            <th x-show="cols.contact" style="min-width: 160px">Contact Person</th>
            <th x-show="cols.phone" style="min-width: 210px">Phone & Email</th>
            <th x-show="cols.gst" style="min-width: 190px">GSTIN & Terms</th>
            <th x-show="cols.orders" style="min-width: 80px; text-align: right">Orders</th>
            <th x-show="cols.status" style="min-width: 90px">Status</th>
        </x-slot>

        @foreach($suppliers as $supplier)
            <tr>
                <x-list-pick :id="$supplier->id" :label="$supplier->name" />
                <td>
                    <a href="{{ route('suppliers.show', $supplier) }}" class="ld-name plain-link font-bold text-slate-900 dark:text-white">{{ $supplier->name }}</a>
                    @if($supplier->company_name || $supplier->city)
                        <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ collect([$supplier->company_name, trim(($supplier->city ?? '') . ($supplier->state ? ', ' . $supplier->state : ''), ', ')])->filter()->implode(' · ') }}</div>
                    @endif
                </td>
                <td x-show="cols.contact">{{ $supplier->contact_person ?: '—' }}</td>
                <td x-show="cols.phone">
                    <div class="space-y-1.5 py-0.5">
                        @if($supplier->phone)
                            <div class="whitespace-nowrap flex items-center gap-1.5">
                                <span class="font-mono text-xs text-slate-900 dark:text-slate-200">{{ $supplier->phone }}</span>
                                <a href="{{ $supplier->getWhatsAppUrl() }}" target="_blank" title="Chat on WhatsApp ({{ $supplier->phone }})" class="inline-flex items-center justify-center w-6 h-6 rounded-md hover:bg-emerald-50 dark:hover:bg-emerald-950/60 transition cursor-pointer text-emerald-600 dark:text-emerald-400" aria-label="WhatsApp {{ $supplier->phone }}">
                                    <x-icon.whatsapp class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                </a>
                            </div>
                        @endif
                        @if($supplier->email)
                            <div class="whitespace-nowrap flex items-center gap-1.5 text-xs text-slate-500">
                                <a href="mailto:{{ $supplier->email }}" class="hover:underline truncate max-w-[150px] font-mono text-[11px] text-slate-600 dark:text-slate-400">
                                    {{ $supplier->email }}
                                </a>
                                <a href="mailto:{{ $supplier->email }}" title="Send Email ({{ $supplier->email }})" class="inline-flex items-center justify-center w-6 h-6 rounded-md hover:bg-sky-50 dark:hover:bg-sky-950/60 transition cursor-pointer text-sky-600 dark:text-sky-400" aria-label="Email {{ $supplier->email }}">
                                    <x-icon.email class="w-4 h-4 text-sky-600 dark:text-sky-400" />
                                </a>
                            </div>
                        @endif
                    </div>
                </td>
                <td x-show="cols.gst">
                    @if($supplier->gst_number)<div class="whitespace-nowrap font-mono text-xs font-semibold">{{ $supplier->gst_number }}</div>@endif
                    <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $supplier->payment_terms ?: 'Standard terms' }}</div>
                </td>
                <td x-show="cols.orders" style="text-align: right font-mono font-semibold">{{ $supplier->purchase_orders_count }}</td>
                <td x-show="cols.status">{!! $supplier->is_active ? '<span class="status-won">Active</span>' : '<span class="status-lost">Inactive</span>' !!}</td>
                <td>
                    {{-- Clean, High-Contrast Text Buttons Across All Supplier Actions (Strictly NO icons) --}}
                    <div class="flex items-center gap-1.5 justify-end">
                        <a href="{{ route('suppliers.show', $supplier) }}" class="px-2.5 py-1 rounded text-xs font-bold bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 text-white transition" title="View Supplier Profile">
                            View
                        </a>
                        @if(auth()->user()->isOffice())
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="px-2.5 py-1 rounded text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 transition" title="Edit Supplier">
                                Edit
                            </a>
                        @endif
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm="Delete supplier {{ $supplier->name }}?" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 rounded text-xs font-bold bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900 transition cursor-pointer" title="Delete Supplier">
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
