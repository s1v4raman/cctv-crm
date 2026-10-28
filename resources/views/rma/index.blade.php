<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">RMA &amp; Warranty Claims</h2>
            </div>
            <a href="{{ route('rma.create') }}" class="btn-primary">
                New RMA Claim
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
            <form data-auto-submit method="GET" action="{{ route('rma.index') }}" class="flex items-center gap-3 flex-wrap">
                <input aria-label="Search" type="text" name="search" value="{{ request('search') }}" placeholder="e.g. RMA-2026-001" class="crm-input" style="width: 240px;">

                <select aria-label="Status" name="status" class="crm-select" style="width: 180px;">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\RmaClaim::statusLabels() as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn-primary">
                    Search
                </button>
                @if(request()->anyFilled(['search', 'status', 'supplier_id', 'warranty']))
                    <a href="{{ route('rma.index') }}" class="btn-secondary">
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
                        <th class="nowrap">RMA Number</th>
                        <th class="nowrap">Supplier</th>
                        <th>Faulty Item & S/N</th>
                        <th class="col-name">Customer / Site</th>
                        <th class="nowrap">Fault Category</th>
                        <th class="nowrap">Status</th>
                        <th class="nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $claim)
                    <tr>
                        <td class="nowrap">
                            <a href="{{ route('rma.show', $claim) }}" class="action-link font-mono">
                                {{ $claim->rma_no }}
                            </a>
                            <div class="text-xs text-slate-500">{{ $claim->created_at->format('d M Y') }}</div>
                        </td>
                        <td class="nowrap">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $claim->supplier->name }}</div>
                            @if($claim->vendor_rma_ref)
                                <div class="text-xs text-slate-500 font-mono">Ref: {{ $claim->vendor_rma_ref }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="font-bold text-slate-900 dark:text-white">{{ $claim->product?->name ?? 'Hardware Asset' }}</div>
                            <div class="font-mono text-xs text-slate-500">S/N: {{ $claim->faulty_serial_number }}</div>
                        </td>
                        <td class="col-name">
                            @if($claim->lead)
                                <a href="{{ route('leads.show', $claim->lead) }}" class="action-link">
                                    {{ $claim->lead->customer_name }}
                                </a>
                                <div class="text-xs text-slate-500">{{ $claim->lead->site_address ?? $claim->lead->phone }}</div>
                            @else
                                <span class="text-slate-400">Warehouse Stock</span>
                            @endif
                        </td>
                        <td class="nowrap">
                            <div>{{ $claim->fault_category_label }}</div>
                            <div class="text-xs {{ $claim->warranty_status_at_claim === 'under_warranty' ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ ucwords(str_replace('_', ' ', $claim->warranty_status_at_claim)) }}
                            </div>
                        </td>
                        <td class="nowrap">
                            @php
                                $st = strtolower($claim->status ?? '');
                                $statusClass = 'status-quoted';
                                if (in_array($st, ['resolved', 'closed', 'replaced'])) $statusClass = 'status-completed';
                                elseif (in_array($st, ['rejected', 'cancelled'])) $statusClass = 'status-cancelled';
                                elseif (in_array($st, ['with_vendor', 'diagnosing', 'in_transit'])) $statusClass = 'status-progress';
                            @endphp
                            <span class="{{ $statusClass }}">
                                {{ $claim->status_label }}
                            </span>
                        </td>
                        <td class="nowrap">
                            <a href="{{ route('rma.show', $claim) }}" class="action-link">View</a>
                            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                <a href="{{ route('rma.edit', $claim) }}" class="action-link">Edit</a>
                            @endif
                            @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('rma.destroy', $claim) }}" class="inline" data-confirm="Delete RMA claim {{ $claim->rma_number }}? This cannot be undone.">
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
                            <x-empty-message empty="No RMA claims yet" filtered="No RMA claims match these filters." :filters="['search', 'status']" />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 text-sm font-medium text-slate-600 dark:text-slate-400">
                Total: {{ $claims->total() }}
            </div>
        </div>

        @if($claims->hasPages())
            <div class="mt-4">
                {{ $claims->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
