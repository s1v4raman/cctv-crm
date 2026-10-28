<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Suppliers</h2>
            </div>
            <a href="{{ route('suppliers.create') }}" class="btn-primary">
                Register Supplier
            </a>
        </div>
    </x-slot>

    <div class="max-w-[1200px] mx-auto space-y-6">
        @if (session('status'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded text-xs">
                {{ session('status') }}
            </div>
        @endif

        {{-- Filter Section --}}
        <div class="crm-section">
            <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center gap-3 flex-wrap">
                <input aria-label="Search" type="text" name="search" value="{{ request('search') }}" placeholder="e.g. Hikvision India" class="crm-input" style="width: 280px;">
                <button type="submit" class="btn-primary">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('suppliers.index') }}" class="btn-secondary">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Table Section (5.3) --}}
        <div class="crm-section p-0 overflow-x-auto">
            <table class="crm-table crm-responsive">
                <thead>
                    <tr>
                        <th class="col-name">Supplier / Distributor</th>
                        <th>Contact Person</th>
                        <th class="nowrap">Phone & Email</th>
                        <th>GSTIN & Terms</th>
                        <th class="nowrap">Total POs</th>
                        <th class="nowrap">Status</th>
                        <th class="nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    <tr>
                        <td class="col-name">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="action-link font-bold">
                                {{ $supplier->name }}
                            </a>
                            @if($supplier->company_name)
                                <div class="text-xs text-slate-500">{{ $supplier->company_name }}</div>
                            @endif
                            @if($supplier->city)
                                <div class="text-xs text-slate-400">{{ $supplier->city }}{{ $supplier->state ? ", {$supplier->state}" : '' }}</div>
                            @endif
                        </td>
                        <td>
                            {{ $supplier->contact_person ?: 'Not specified' }}
                        </td>
                        <td class="nowrap">
                            @if($supplier->phone)
                                <div><a href="tel:{{ $supplier->phone }}" class="action-link">{{ $supplier->phone }}</a></div>
                            @endif
                            @if($supplier->email)
                                <div class="text-xs text-slate-500">{{ $supplier->email }}</div>
                            @endif
                        </td>
                        <td>
                            @if($supplier->gst_number)
                                <span class="font-mono text-xs block">{{ $supplier->gst_number }}</span>
                            @endif
                            <span class="text-xs text-slate-500">{{ $supplier->payment_terms ?: 'Standard' }}</span>
                        </td>
                        <td class="nowrap">
                            {{ $supplier->purchase_orders_count }} Orders
                        </td>
                        <td class="nowrap">
                            @if($supplier->is_active)
                                <span class="status-won">Active</span>
                            @else
                                <span class="status-lost">Inactive</span>
                            @endif
                        </td>
                        <td class="nowrap">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="action-link">View</a>
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="action-link">Edit</a>
                            @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm="Are you sure you want to delete this supplier?" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="danger-link p-0 bg-transparent border-0 cursor-pointer text-xs">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-6 text-slate-500 text-sm">
                            No suppliers yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 text-sm font-medium text-slate-600 dark:text-slate-400">
                Total: {{ $suppliers->total() }}
            </div>
        </div>

        @if($suppliers->hasPages())
            <div class="mt-4">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
