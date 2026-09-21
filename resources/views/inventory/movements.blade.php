<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Inventory Movement & Audit Logs</h2>
                <p class="mt-1 text-sm text-gray-500">Chronological ledger of stock additions, dispatches, and adjustments</p>
            </div>
            <a href="{{ route('inventory.index') }}" class="btn-head-secondary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Inventory
            </a>
        </div>
    </x-slot>

    <style>
        .pg-wrap  { background:#f1f5f9; min-height:100vh; padding:1.75rem 0 3rem; }
        .pg-inner { max-width:1280px; margin:0 auto; padding:0 1.25rem; }

        .btn-head-secondary {
            display: inline-flex; align-items: center;
            background-color: #ffffff; color: #334155 !important;
            padding: 0.55rem 1rem; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600; text-decoration: none;
            border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.15s ease-in-out;
        }
        .btn-head-secondary:hover { background-color: #f8fafc; color: #0f172a !important; border-color: #94a3b8; }

        .pg-card {
            background:#fff;
            border-radius:1rem;
            border:1px solid rgba(99,102,241,.08);
            box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(99,102,241,.06);
            overflow:hidden;
        }

        .filter-bar {
            display:flex; align-items:center; gap:.75rem;
            padding:1rem 1.25rem; border-bottom:1px solid #f1f5f9; flex-wrap:wrap;
        }
        .filter-bar select {
            border:1px solid #cbd5e1; border-radius:.5rem;
            padding:.5rem .85rem; font-size:.85rem; color:#1e293b;
            background:#ffffff; outline:none; transition: all .15s;
        }
        .filter-bar select:focus { border-color:#4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15); }

        .btn-filter {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: .5rem 1.1rem; border-radius: .5rem;
            background-color: #4f46e5; color: #ffffff !important;
            font-size: .85rem; font-weight: 700;
            border: 1px solid #4338ca; cursor: pointer;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all .15s ease-in-out;
        }
        .btn-filter:hover { background-color: #4338ca; }

        .btn-clear {
            display: inline-flex; align-items: center;
            font-size: .85rem; color: #64748b !important; text-decoration: none; font-weight: 600;
            padding: .5rem .75rem; border-radius: .5rem; border: 1px solid #e2e8f0; background: #fff;
            transition: all .15s;
        }
        .btn-clear:hover { background: #f1f5f9; color: #1e293b !important; }

        .p-table { width:100%; border-collapse:collapse; }
        .p-table thead { background:#f8fafc; border-bottom: 1px solid #e2e8f0; }
        .p-table thead th {
            padding:.75rem 1.25rem;
            font-size:.67rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.06em; color:#64748b; text-align:left; white-space:nowrap;
        }
        .p-table thead th.center { text-align:center; }
        .p-table tbody tr { border-bottom:1px solid #f1f5f9; transition:background .12s; }
        .p-table tbody tr:hover { background:#f8fafc; }
        .p-table tbody td { padding:.85rem 1.25rem; vertical-align:middle; }
        .p-table tbody td.center { text-align:center; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">
            <div class="pg-card">
                {{-- Filters --}}
                <form method="GET" action="{{ route('inventory.movements') }}" class="filter-bar">
                    <select name="product_id" class="min-w-[220px]">
                        <option value="">All Products</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>
                                {{ $p->name }} {{ $p->sku ? "({$p->sku})" : '' }}
                            </option>
                        @endforeach
                    </select>

                    <select name="type">
                        <option value="">All Movement Types</option>
                        <option value="in" @selected(request('type') == 'in')>Stock IN (Purchase/Add)</option>
                        <option value="out" @selected(request('type') == 'out')>Stock OUT (Manual Dispatch)</option>
                        <option value="job_installation" @selected(request('type') == 'job_installation')>Installation Deployment</option>
                        <option value="adjustment" @selected(request('type') == 'adjustment')>Adjustment/Correction</option>
                        <option value="return" @selected(request('type') == 'return')>Customer/Vendor Return</option>
                    </select>

                    <button type="submit" class="btn-filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filter Logs
                    </button>
                    @if(request()->hasAny(['product_id', 'type']))
                        <a href="{{ route('inventory.movements') }}" class="btn-clear">Clear</a>
                    @endif
                </form>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="p-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Product Item</th>
                                <th>Movement Type</th>
                                <th class="center">Quantity</th>
                                <th class="center">Balance After</th>
                                <th>Notes / Reason</th>
                                <th>Logged By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $mov)
                            <tr>
                                <td class="text-xs text-gray-500 whitespace-nowrap">
                                    {{ $mov->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td>
                                    <div class="font-bold text-sm text-gray-900">{{ $mov->product?->name ?? 'Deleted Product' }}</div>
                                    @if($mov->product?->sku)
                                        <div class="text-[11px] font-mono text-gray-500">SKU: {{ $mov->product->sku }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($mov->type === 'in')
                                        <span class="px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Stock IN</span>
                                    @elseif($mov->type === 'out')
                                        <span class="px-2.5 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">Stock OUT</span>
                                    @elseif($mov->type === 'job_installation')
                                        <span class="px-2.5 py-1 rounded text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">Job Installation</span>
                                    @elseif($mov->type === 'return')
                                        <span class="px-2.5 py-1 rounded text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">Return</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">Adjustment</span>
                                    @endif
                                </td>
                                <td class="center font-extrabold text-sm {{ $mov->quantity >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $mov->quantity > 0 ? '+' : '' }}{{ $mov->quantity }}
                                </td>
                                <td class="center font-bold text-sm text-slate-800">
                                    {{ $mov->balance_after }} {{ $mov->product?->unit ?? '' }}
                                </td>
                                <td class="text-xs text-gray-700 font-medium">
                                    {{ $mov->notes ?: '—' }}
                                </td>
                                <td class="text-xs text-gray-500 whitespace-nowrap font-medium">
                                    {{ $mov->user?->name ?? 'System' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-sm text-gray-500 font-medium">
                                    No stock movements recorded yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($movements->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $movements->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
