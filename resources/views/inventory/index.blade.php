<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Warehouse & Inventory Management</h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-orange-500/10 text-orange-600 dark:text-orange-400 px-2 py-0.5 rounded border border-orange-200 dark:border-orange-500/30">Live Stock</span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Live CCTV hardware quantities, serial numbers, threshold alerts &amp; valuation</p>
            </div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('inventory.movements') }}" class="btn-dark">
                    <svg class="h-4 w-4 mr-1 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Audit Log
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('products.create') }}" class="btn-amber">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Product
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }
        @media (min-width: 640px) { .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .stat-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .stat-label { font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:#64748b; margin-bottom:0.25rem; }
        html.dark .stat-label { color:#94a3b8; }
        .stat-value { font-size:1.7rem; font-weight:800; color:#0f172a; font-family:'Outfit',sans-serif; }
        html.dark .stat-value { color:#ffffff; }
        .stat-icon { width:48px; height:48px; border-radius:0.75rem; display:flex; align-items:center; justify-content:center; }

        .p-table { width:100%; border-collapse:collapse; }
        .p-table thead th.right { text-align:right; }
        .p-table thead th.center { text-align:center; }
        .p-table tbody td.right { text-align:right; }
        .p-table tbody td.center { text-align:center; }

        .cell-name { font-size:.875rem; font-weight:800; color:#0f172a; font-family:'Outfit',sans-serif; }
        html.dark .cell-name { color:#ffffff; }
        .cell-sub  { font-size:.75rem; color:#64748b; margin-top:.15rem; }
        html.dark .cell-sub { color:#94a3b8; }
        .cell-sku  { font-size:.76rem; color:#475569; font-family:ui-monospace,monospace; background:#f1f5f9; padding:.2rem .45rem; border-radius:.35rem; border:1px solid #e2e8f0; }
        html.dark .cell-sku { color:#cbd5e1; background:#1e293b; border-color:rgba(255,255,255,.08); }
    </style>
    <style>
        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .stock-ok  { background:#ecfdf5; color:#059669; border:1px solid #d1fae5; }
        .stock-low { background:#fffbeb; color:#d97706; border:1px solid #fde68a; }
        .stock-out { background:#fef2f2; color:#dc2626; border:1px solid #fee2e2; }
        html.dark .stock-ok  { background:rgba(16,185,129,.15); color:#34d399; border-color:rgba(16,185,129,.3); }
        html.dark .stock-low { background:rgba(245,158,11,.15); color:#fbbf24; border-color:rgba(245,158,11,.3); }
        html.dark .stock-out { background:rgba(244,63,94,.15); color:#fb7185; border-color:rgba(244,63,94,.3); }

        /* Action Buttons */
        .btn-adjust {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.45rem 0.85rem; border-radius: 0.5rem;
            font-size: 0.78rem; font-weight: 800;
            background: #2563eb !important;
            color: #ffffff !important;
            border: none; cursor: pointer;
            box-shadow: 0 4px 12px -2px rgba(37, 99, 235, 0.35);
            transition: all .15s ease-in-out;
        }
        .btn-adjust:hover { background: #1d4ed8 !important; transform: translateY(-1px); }

        .btn-filter {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: .5rem 1.1rem; border-radius: .6rem;
            background: #2563eb !important;
            color: #ffffff !important;
            font-size: .82rem; font-weight: 800;
            border: none; cursor: pointer;
            box-shadow: 0 4px 12px -2px rgba(37, 99, 235, 0.35);
        }
        .btn-filter:hover { background: #1d4ed8 !important; }

        .btn-clear {
            display: inline-flex; align-items: center;
            font-size: .82rem; color: #475569 !important; text-decoration: none; font-weight: 700;
            padding: .5rem .85rem; border-radius: .6rem; border: 1px solid #e2e8f0; background: #f8fafc;
            transition: all .15s;
        }
        .btn-clear:hover { background: #e2e8f0; color: #0f172a !important; }
        html.dark .btn-clear { color: #94a3b8 !important; border-color: #334155; background: #1e293b; }
        html.dark .btn-clear:hover { background: #334155; color: #ffffff !important; }

        /* Modal custom tabs */
        .type-option-in  { background: rgba(16,185,129,.15); border-color:#10b981; color:#059669; }
        .type-option-out { background: rgba(244,63,94,.15); border-color:#f43f5e; color:#dc2626; }
        .type-option-adj { background: rgba(245,158,11,.15); border-color:#f59e0b; color:#d97706; }
        .type-option-inactive { background: #f8fafc; border-color: #e2e8f0; color: #64748b; }
        html.dark .type-option-in  { color: #34d399; }
        html.dark .type-option-out { color: #fb7185; }
        html.dark .type-option-adj { color: #fbbf24; }
        html.dark .type-option-inactive { background: #060913; border-color: #334155; color: #94a3b8; }
    </style>

    <div class="pg-wrap" x-data="inventoryManager()">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Stat Cards --}}
            <div class="stat-grid">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total SKUs</div>
                        <div class="stat-value">{{ $totalProducts }}</div>
                        <span class="text-xs text-gray-500 font-medium">{{ $totalUnits }} units in warehouse</span>
                    </div>
                    <div class="stat-icon bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Low Stock Warnings</div>
                        <div class="stat-value text-amber-600">{{ $lowStockCount }}</div>
                        <span class="text-xs text-amber-600 font-medium">At or below threshold</span>
                    </div>
                    <div class="stat-icon bg-amber-50 dark:bg-amber-950/50 text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Out of Stock</div>
                        <div class="stat-value text-red-600">{{ $outOfStockCount }}</div>
                        <span class="text-xs text-red-500 font-medium">Zero warehouse balance</span>
                    </div>
                    <div class="stat-icon bg-red-50 dark:bg-red-950/50 text-red-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Inventory Valuation</div>
                        <div class="stat-value text-emerald-600">₹{{ number_format($totalValuation, 2) }}</div>
                        <span class="text-xs text-emerald-600 font-medium">Based on cost price</span>
                    </div>
                    <div class="stat-icon bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            {{-- Main Inventory Card --}}
            <div class="pg-card">
                {{-- Filters --}}
                <form method="GET" action="{{ route('inventory.index') }}" class="filter-bar">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, SKU, brand, model..." />
                    
                    @if($categories->count())
                    <select name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('category') == $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                    @endif

                    <select name="status">
                        <option value="">All Stock Levels</option>
                        <option value="in_stock" @selected(request('status') == 'in_stock')>🟢 Healthy Stock (> Alert Level)</option>
                        <option value="low_stock" @selected(request('status') == 'low_stock')>🟡 Low Stock (≤ Alert Level)</option>
                        <option value="out_of_stock" @selected(request('status') == 'out_of_stock')>🔴 Out of Stock (0)</option>
                    </select>

                    <button type="submit" class="btn-filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'category', 'status']))
                        <a href="{{ route('inventory.index') }}" class="btn-clear">Clear Filters</a>
                    @endif
                </form>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="p-table">
                        <thead>
                            <tr>
                                <th>Product / Model</th>
                                <th>SKU</th>
                                <th>Category & Brand</th>
                                <th class="center">Stock Level</th>
                                <th class="right">Cost Price</th>
                                <th class="right">Stock Value</th>
                                <th class="center">Warranty</th>
                                @if(auth()->user()->isAdmin())
                                <th class="center">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $prod)
                            <tr>
                                <td>
                                    <div class="cell-name">{{ $prod->name }}</div>
                                    @if($prod->model_no)
                                        <div class="cell-sub">Model: <span class="font-medium text-gray-700 dark:text-slate-300">{{ $prod->model_no }}</span></div>
                                    @endif
                                </td>
                                <td>
                                    <span class="cell-sku">{{ $prod->sku ?: '—' }}</span>
                                </td>
                                <td>
                                    <div class="text-sm font-semibold text-gray-800 dark:text-slate-200">{{ $prod->category ?: 'General' }}</div>
                                    <div class="cell-sub">{{ $prod->brand ?: 'Unbranded' }}</div>
                                </td>
                                <td class="center">
                                    @if($prod->stock_quantity <= 0)
                                        <span class="stock-badge stock-out">
                                            <span>●</span> Out of Stock (0 {{ $prod->unit }})
                                        </span>
                                    @elseif($prod->isLowStock())
                                        <span class="stock-badge stock-low">
                                            <span>●</span> Low: {{ $prod->stock_quantity }} {{ $prod->unit }}
                                        </span>
                                        <div class="text-[10px] text-amber-700 dark:text-amber-400 mt-0.5">Alert limit: {{ $prod->min_stock_alert }}</div>
                                    @else
                                        <span class="stock-badge stock-ok">
                                            <span>●</span> {{ $prod->stock_quantity }} {{ $prod->unit }}
                                        </span>
                                    @endif
                                </td>
                                <td class="right">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-slate-200">₹{{ number_format($prod->cost_price, 2) }}</span>
                                </td>
                                <td class="right">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">₹{{ number_format($prod->cost_price * $prod->stock_quantity, 2) }}</span>
                                </td>
                                <td class="center">
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $prod->default_warranty_months ?: 24 }} Mo
                                    </span>
                                </td>
                                @if(auth()->user()->isAdmin())
                                <td class="center">
                                    <button type="button" 
                                            @click="openModal({{ $prod->id }}, {{ json_encode($prod->name) }}, {{ json_encode($prod->sku ?? '') }}, {{ $prod->stock_quantity }}, {{ json_encode($prod->unit ?? 'pcs') }})"
                                            class="btn-adjust text-white">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        Stock In / Adjust
                                    </button>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ auth()->user()->isAdmin() ? 8 : 7 }}" class="text-center py-8 text-gray-500 text-sm">
                                    No products found matching the criteria.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $products->links() }}
                </div>
                @endif
            </div>

            {{-- Recent Movements Activity Feed --}}
            @if($recentMovements->count())
            <div class="pg-card">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wide">Recent Stock Activity Trail</h3>
                    <a href="{{ route('inventory.movements') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">View All Logs &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="p-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Product</th>
                                <th>Movement Type</th>
                                <th class="center">Qty</th>
                                <th class="center">Balance After</th>
                                <th>Notes / Reference</th>
                                <th>Logged By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentMovements as $mov)
                            <tr>
                                <td class="text-xs text-gray-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ $mov->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td>
                                    <span class="font-bold text-xs text-gray-900 dark:text-white">{{ $mov->product?->name ?? 'Unknown Product' }}</span>
                                </td>
                                <td>
                                    @if($mov->type === 'in')
                                        <span class="px-2.5 py-1 rounded text-[11px] font-bold bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">Stock IN (+)</span>
                                    @elseif($mov->type === 'out')
                                        <span class="px-2.5 py-1 rounded text-[11px] font-bold bg-rose-100 dark:bg-rose-500/20 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30">Stock OUT (-)</span>
                                    @elseif($mov->type === 'job_installation')
                                        <span class="px-2.5 py-1 rounded text-[11px] font-bold bg-indigo-100 dark:bg-indigo-500/20 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">Job Deployment</span>
                                    @elseif($mov->type === 'return')
                                        <span class="px-2.5 py-1 rounded text-[11px] font-bold bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">Customer Return</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Adjustment</span>
                                    @endif
                                </td>
                                <td class="center font-bold text-xs {{ $mov->quantity >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                    {{ $mov->quantity > 0 ? '+' : '' }}{{ $mov->quantity }}
                                </td>
                                <td class="center font-semibold text-xs text-gray-700 dark:text-slate-300">
                                    {{ $mov->balance_after }}
                                </td>
                                <td class="text-xs text-gray-600 dark:text-slate-400 max-w-xs truncate">
                                    {{ $mov->notes ?: '—' }}
                                </td>
                                <td class="text-xs text-gray-500 dark:text-slate-400 font-medium">
                                    {{ $mov->user?->name ?? 'System' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>

        {{-- Stock Adjustment Modal --}}
        <div x-show="adjustModal" 
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
             x-cloak>
            <div class="bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-700 text-slate-200" @click.outside="adjustModal = false" style="background-color: #0f172a;">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="text-base font-extrabold text-white font-heading">Update Stock Level</h3>
                    <button @click="adjustModal = false" class="text-slate-400 hover:text-white text-2xl font-bold leading-none">&times;</button>
                </div>

                <template x-if="selectedProductId">
                    <form :action="`/inventory/${selectedProductId}/adjust`" method="POST">
                        @csrf
                        <div class="mb-4 p-3.5 bg-[#060913] rounded-xl border border-slate-800">
                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Product Master</div>
                            <div class="text-sm font-extrabold text-white mt-0.5" x-text="productName"></div>
                            <div class="text-xs text-slate-300 mt-1.5 flex items-center justify-between">
                                <span>Current Stock: <strong class="text-amber-400 font-bold font-mono" x-text="productStock + ' ' + productUnit"></strong></span>
                                <span x-show="productSku" class="font-mono text-slate-400" x-text="'SKU: ' + productSku"></span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Movement Action</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex items-center justify-center p-2.5 rounded-xl border text-xs font-bold cursor-pointer text-center transition-all"
                                       :class="adjustType === 'in' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 shadow-sm' : 'bg-[#060913] text-slate-400 border-slate-800 hover:border-slate-700'">
                                    <input type="radio" name="type" value="in" x-model="adjustType" class="hidden">
                                    <span>➕ Stock In</span>
                                </label>
                                <label class="flex items-center justify-center p-2.5 rounded-xl border text-xs font-bold cursor-pointer text-center transition-all"
                                       :class="adjustType === 'out' ? 'bg-rose-500/20 text-rose-300 border-rose-500/40 shadow-sm' : 'bg-[#060913] text-slate-400 border-slate-800 hover:border-slate-700'">
                                    <input type="radio" name="type" value="out" x-model="adjustType" class="hidden">
                                    <span>➖ Stock Out</span>
                                </label>
                                <label class="flex items-center justify-center p-2.5 rounded-xl border text-xs font-bold cursor-pointer text-center transition-all"
                                       :class="adjustType === 'adjustment' ? 'bg-amber-500/20 text-amber-300 border-amber-500/40 shadow-sm' : 'bg-[#060913] text-slate-400 border-slate-800 hover:border-slate-700'">
                                    <input type="radio" name="type" value="adjustment" x-model="adjustType" class="hidden">
                                    <span>⚙️ Set Exact</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1"
                                   x-text="adjustType === 'adjustment' ? 'New Exact Stock Count' : 'Quantity'"></label>
                            <input type="number" name="quantity" min="0" required x-model="adjustQty"
                                   class="w-full border border-slate-700 bg-[#060913] rounded-xl px-3.5 py-2.5 text-sm font-semibold text-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-400">
                        </div>

                        <div class="mb-5">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Reason / Notes</label>
                            <input type="text" name="notes" placeholder="e.g., Vendor Invoice #4812, Physical audit, Damaged" x-model="adjustNotes"
                                   class="w-full border border-slate-700 bg-[#060913] rounded-xl px-3.5 py-2 text-sm text-slate-200 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-400">
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                            <button type="button" @click="adjustModal = false"
                                    class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white rounded-xl border border-slate-700 hover:bg-slate-800 transition">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="btn-amber">
                                Save Stock Movement
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>

    <script>
        function inventoryManager() {
            return {
                adjustModal: false,
                selectedProductId: null,
                productName: '',
                productSku: '',
                productStock: 0,
                productUnit: 'pcs',
                adjustType: 'in',
                adjustQty: 1,
                adjustNotes: '',
                openModal(id, name, sku, stock, unit) {
                    this.selectedProductId = id;
                    this.productName = name;
                    this.productSku = sku || '';
                    this.productStock = stock;
                    this.productUnit = unit || 'pcs';
                    this.adjustType = 'in';
                    this.adjustQty = 1;
                    this.adjustNotes = '';
                    this.adjustModal = true;
                }
            };
        }
    </script>
</x-app-layout>
