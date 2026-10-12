<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">RMA Claims</h2>
            </div>
            <a href="{{ route('rma.create') }}"
               class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition"
               style="border-radius: 4px;">
                + Raise New RMA Claim
            </a>
        </div>
    </x-slot>

    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 1024px) {
            .stat-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        }

        .stat-card {
            background: #ffffff;
            border-radius: 4px;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
        }
        html.dark .stat-card {
            background: #0f172a;
            border-color: #1e293b;
        }
        .stat-label { font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 0.25rem; }
        html.dark .stat-label { color: #94a3b8; }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: #0f172a; line-height: 1; }
        html.dark .stat-value { color: #ffffff; }

        .pg-card {
            background: #ffffff;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        html.dark .pg-card {
            background: #0f172a;
            border-color: #1e293b;
        }

        .filter-bar {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; flex-wrap: wrap;
            background: #ffffff;
        }
        html.dark .filter-bar {
            background: #0b1120;
            border-color: #1e293b;
        }
        .filter-input {
            height: 38px;
            padding: 0 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 14px;
            background: #ffffff;
            color: #1e293b;
        }
        html.dark .filter-input {
            background: #060913;
            border-color: #334155;
            color: #f1f5f9;
        }
    </style>

    <div class="py-6 bg-slate-50 dark:bg-[#060913] min-h-screen">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-5 p-4 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-sm font-medium" style="border-radius: 4px;">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Claims</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active In-Process</div>
                    <div class="stat-value text-amber-600 dark:text-amber-400">{{ $stats['active_in_process'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Shipped to Vendors</div>
                    <div class="stat-value text-blue-600 dark:text-sky-400">{{ $stats['shipped'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">In Vendor Repair</div>
                    <div class="stat-value text-purple-600 dark:text-purple-400">{{ $stats['with_vendor'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Resolved / Closed</div>
                    <div class="stat-value text-emerald-600 dark:text-emerald-400">{{ $stats['resolved'] }}</div>
                </div>
            </div>

            {{-- Table & Filters Card --}}
            <div class="pg-card">
                <form method="GET" action="{{ route('rma.index') }}" class="filter-bar">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search RMA #, Serial #, Vendor..." class="filter-input flex-1 min-w-[200px]">
                    
                    <select name="supplier_id" class="filter-input">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="filter-input">
                        <option value="">All Statuses</option>
                        @foreach(\App\Models\RmaClaim::statusLabels() as $key => $label)
                            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-4 text-white text-sm font-semibold rounded bg-blue-600 hover:bg-blue-700 transition" style="height: 38px; border-radius: 4px;">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'supplier_id', 'warranty']))
                        <a href="{{ route('rma.index') }}" class="px-3 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center" style="height: 38px; border-radius: 4px;">
                            Reset
                        </a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">RMA Number</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Vendor / Supplier</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Faulty Item & S/N</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Customer / Site</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Fault Category</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Status</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                            @forelse($claims as $claim)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a href="{{ route('rma.show', $claim) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ $claim->rma_no }}
                                        </a>
                                        <div class="text-xs text-slate-500">{{ $claim->created_at->format('d M Y') }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-medium text-slate-900 dark:text-slate-200">{{ $claim->supplier->name }}</div>
                                        @if($claim->vendor_rma_ref)
                                            <div class="text-xs text-slate-500 font-mono">Ref: {{ $claim->vendor_rma_ref }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-900 dark:text-slate-200">{{ $claim->product?->name ?? 'Hardware Asset' }}</div>
                                        <div class="font-mono text-xs text-slate-600 dark:text-slate-400">S/N: {{ $claim->faulty_serial_number }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($claim->lead)
                                            <div class="font-medium text-slate-900 dark:text-slate-200">{{ $claim->lead->customer_name }}</div>
                                            <div class="text-xs text-slate-500 truncate max-w-xs">{{ $claim->lead->site_address ?? $claim->lead->phone }}</div>
                                        @else
                                            <span class="text-slate-400 italic">Warehouse / Stock Item</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-slate-900 dark:text-slate-200">{{ $claim->fault_category_label }}</span>
                                        <div class="text-xs font-semibold {{ $claim->warranty_status_at_claim === 'under_warranty' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ ucwords(str_replace('_', ' ', $claim->warranty_status_at_claim)) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $st = strtolower($claim->status ?? '');
                                            $color = 'text-slate-700 dark:text-slate-300';
                                            if (in_array($st, ['draft', 'created'])) $color = 'text-blue-600 dark:text-blue-400';
                                            elseif (in_array($st, ['shipped', 'in_transit'])) $color = 'text-sky-600 dark:text-sky-400';
                                            elseif (in_array($st, ['with_vendor', 'diagnosing'])) $color = 'text-amber-600 dark:text-amber-400';
                                            elseif (in_array($st, ['resolved', 'closed', 'replaced'])) $color = 'text-emerald-600 dark:text-emerald-400';
                                            elseif (in_array($st, ['rejected'])) $color = 'text-rose-600 dark:text-rose-400';
                                        @endphp
                                        <span class="font-bold {{ $color }}">
                                            {{ $claim->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right font-medium text-sm">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <a href="{{ route('rma.show', $claim) }}" class="text-blue-600 dark:text-blue-400 hover:underline">View</a>
                                            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <a href="{{ route('rma.edit', $claim) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                            @endif
                                            @if(auth()->user()->isAdmin())
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <form method="POST" action="{{ route('rma.destroy', $claim) }}" class="inline" onsubmit="return confirm('Delete RMA claim {{ addslashes($claim->rma_number) }}? This cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline cursor-pointer bg-transparent border-0 p-0 font-medium">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                        No RMA claims found matching your filter criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $claims->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
