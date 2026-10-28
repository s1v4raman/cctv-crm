<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('suppliers.index') }}" class="back-link">&larr; Back</a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-1">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $supplier->name }}</h2>
                    <div class="flex items-center gap-2 text-xs mt-1">
                        @if($supplier->is_active)
                            <span class="status-won">Active</span>
                        @else
                            <span class="status-lost">Inactive</span>
                        @endif
                        @if($supplier->company_name)
                            <span class="text-slate-400">&bull;</span>
                            <span class="text-slate-600 dark:text-slate-300">{{ $supplier->company_name }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-secondary">
                        Edit
                    </a>
                    <a href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}" class="btn-primary">
                        New Purchase Order
                    </a>
                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm="Are you sure you want to delete supplier {{ $supplier->name }}?" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger-link p-0 bg-transparent border-0 cursor-pointer text-xs ml-2">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div class="max-w-[1200px] mx-auto space-y-6">
        @if (session('status'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded text-xs">
                {{ session('status') }}
            </div>
        @endif

        {{-- Section 1: Two-column table profile (5.5) --}}
        <div class="crm-section">
            <h3>Supplier Information</h3>
            <table class="crm-table">
                <tbody>
                    <tr>
                        <td class="w-1/3 text-slate-500 font-bold">Supplier Name</td>
                        <td class="w-2/3 text-slate-900 dark:text-white">{{ $supplier->name }}</td>
                    </tr>
                    @if($supplier->company_name)
                        <tr>
                            <td class="text-slate-500 font-bold">Company Name</td>
                            <td class="text-slate-900 dark:text-white">{{ $supplier->company_name }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-slate-500 font-bold">Contact Person</td>
                        <td class="text-slate-900 dark:text-white">{{ $supplier->contact_person ?: 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Phone Number</td>
                        <td class="text-slate-900 dark:text-white">
                            @if($supplier->phone)
                                <a href="tel:{{ $supplier->phone }}" class="action-link">{{ $supplier->phone }}</a>
                            @else
                                Not specified
                            @endif
                        </td>
                    </tr>
                    @if($supplier->email)
                        <tr>
                            <td class="text-slate-500 font-bold">Email Address</td>
                            <td class="text-slate-900 dark:text-white">{{ $supplier->email }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-slate-500 font-bold">GSTIN / Tax ID</td>
                        <td class="text-slate-900 dark:text-white font-mono">{{ $supplier->gst_number ?: 'Unregistered' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Payment Terms</td>
                        <td class="text-slate-900 dark:text-white">{{ $supplier->payment_terms ?: 'Standard' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Total Procurement Volume</td>
                        <td class="text-slate-900 dark:text-white font-bold">&#8377;{{ number_format($supplier->totalPurchaseVolume(), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Warehouse Address</td>
                        <td class="text-slate-900 dark:text-white">
                            {{ $supplier->address ?: 'Not specified' }}{{ $supplier->city ? ", {$supplier->city}" : '' }}{{ $supplier->state ? ", {$supplier->state}" : '' }}
                        </td>
                    </tr>
                    @if($supplier->notes)
                        <tr>
                            <td class="text-slate-500 font-bold">Notes & Bank Details</td>
                            <td class="text-slate-900 dark:text-white whitespace-pre-line">{{ $supplier->notes }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Section 2: Procurement History Table (5.3) --}}
        <div class="crm-section">
            <h3>Procurement History</h3>
            <div class="overflow-x-auto">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th class="nowrap">PO Number</th>
                            <th class="nowrap">Order Date</th>
                            <th>Line Items</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th class="nowrap">Status</th>
                            <th class="nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier->purchaseOrders as $po)
                        <tr>
                            <td class="nowrap">
                                <a href="{{ route('purchase-orders.show', $po) }}" class="action-link font-mono">
                                    {{ $po->po_number }}
                                </a>
                            </td>
                            <td class="nowrap">
                                {{ $po->order_date->format('d M Y') }}
                            </td>
                            <td>
                                {{ $po->items->count() }} items ({{ $po->items->pluck('item_name')->take(2)->join(', ') }})
                            </td>
                            <td class="font-bold text-slate-900 dark:text-white">
                                &#8377;{{ number_format($po->total, 2) }}
                            </td>
                            <td class="text-slate-900 dark:text-white">
                                &#8377;{{ number_format($po->amount_paid, 2) }}
                            </td>
                            <td class="nowrap">
                                <span class="status-{{ $po->status === 'received' ? 'completed' : ($po->status === 'cancelled' ? 'cancelled' : 'progress') }}">
                                    {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                                </span>
                            </td>
                            <td class="nowrap">
                                <a href="{{ route('purchase-orders.show', $po) }}" class="action-link">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-slate-500 text-sm">
                                No purchase orders yet
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
