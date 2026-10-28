<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Purchase Orders</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('suppliers.index') }}" class="btn-secondary">
                    Suppliers
                </a>
                <a href="{{ route('purchase-orders.create') }}" class="btn-primary">
                    New Purchase Order
                </a>
            </div>
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
            <form method="GET" action="{{ route('purchase-orders.index') }}" class="flex items-center gap-3 flex-wrap">
                <input aria-label="Search" type="text" name="search" value="{{ request('search') }}" placeholder="e.g. PO-2026-001" class="crm-input" style="width: 240px;">

                <select aria-label="Status" name="status" class="crm-select" style="width: 180px;">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="ordered" {{ request('status') === 'ordered' ? 'selected' : '' }}>Ordered</option>
                    <option value="partially_received" {{ request('status') === 'partially_received' ? 'selected' : '' }}>Partially Received</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                <select aria-label="Payment status" name="payment_status" class="crm-select" style="width: 180px;">
                    <option value="">All Payments</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partially_paid" {{ request('payment_status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>

                <button type="submit" class="btn-primary">
                    Search
                </button>

                @if(request()->hasAny(['search', 'status', 'payment_status', 'supplier_id']))
                    <a href="{{ route('purchase-orders.index') }}" class="btn-secondary">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Table Section (5.3) --}}
        <div class="crm-section p-0 overflow-x-auto">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th class="nowrap">PO Number</th>
                        <th class="col-name">Supplier</th>
                        <th class="nowrap">Order Date</th>
                        <th class="nowrap">Receiving Progress</th>
                        <th>Total Amount</th>
                        <th class="nowrap">Status</th>
                        <th class="nowrap">Payment</th>
                        <th class="nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                    <tr>
                        <td class="nowrap">
                            <a href="{{ route('purchase-orders.show', $po) }}" class="action-link font-mono">
                                {{ $po->po_number }}
                            </a>
                        </td>
                        <td class="col-name">
                            <div class="font-medium text-slate-900 dark:text-white">{{ $po->supplier->name }}</div>
                            @if($po->supplier->phone)
                                <div class="text-xs text-slate-500">{{ $po->supplier->phone }}</div>
                            @endif
                        </td>
                        <td class="nowrap">
                            {{ $po->order_date->format('d M Y') }}
                        </td>
                        <td class="nowrap">
                            @php
                                $totOrd = $po->items->sum('quantity_ordered');
                                $totRec = $po->items->sum('quantity_received');
                            @endphp
                            {{ $totRec }} / {{ $totOrd }} Units
                        </td>
                        <td class="font-bold text-slate-900 dark:text-white">
                            &#8377;{{ number_format($po->total, 2) }}
                            @if($po->balanceDue() > 0)
                                <div class="text-xs font-normal text-red-600">Due: &#8377;{{ number_format($po->balanceDue(), 2) }}</div>
                            @endif
                        </td>
                        <td class="nowrap">
                            <span class="status-{{ $po->status === 'received' ? 'completed' : ($po->status === 'cancelled' ? 'cancelled' : 'progress') }}">
                                {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                            </span>
                        </td>
                        <td class="nowrap">
                            <span class="status-{{ $po->payment_status === 'paid' ? 'completed' : ($po->payment_status === 'unpaid' ? 'cancelled' : 'progress') }}">
                                {{ ucfirst(str_replace('_', ' ', $po->payment_status)) }}
                            </span>
                        </td>
                        <td class="nowrap">
                            <a href="{{ route('purchase-orders.show', $po) }}" class="action-link">View</a>
                            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                <a href="{{ route('purchase-orders.edit', $po) }}" class="action-link">Edit</a>
                            @endif
                            @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('purchase-orders.destroy', $po) }}" class="inline" data-confirm="Delete purchase order {{ $po->po_number }}? This cannot be undone.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="danger-link p-0 bg-transparent border-0 cursor-pointer text-xs">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-6 text-slate-500 text-sm">
                            No purchase orders yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 text-sm font-medium text-slate-600 dark:text-slate-400">
                Total: {{ $purchaseOrders->total() }}
            </div>
        </div>

        @if($purchaseOrders->hasPages())
            <div class="mt-4">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
