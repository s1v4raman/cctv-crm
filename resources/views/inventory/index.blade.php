<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Warehouse & Inventory Management</h2>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('inventory.movements') }}" class="btn-secondary">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>Audit Log</span>
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('products.create') }}" class="btn-primary">
                    Add Product
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 1024px) { .stat-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .stat-card {
            background: #ffffff;
            border-radius: 4px;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
        }
        html.dark .stat-card {
            background: #0f172a;
            border-color: var(--text-main);
        }
        .stat-label { font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 0.25rem; }
        html.dark .stat-label { color: var(--text-muted); }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: var(--text-main); line-height: 1; }
        html.dark .stat-value { color: #ffffff; }

        .pg-card {
            background: #ffffff;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        html.dark .pg-card {
            background: #0f172a;
            border-color: var(--text-main);
        }

        .filter-bar {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1rem; border-bottom: 1px solid var(--card-border); flex-wrap: wrap;
            background: #ffffff;
        }
        html.dark .filter-bar {
            background: #0b1120;
            border-color: var(--card-border);
        }
        .filter-bar input, .filter-bar select {
            border: 1px solid var(--card-border); border-radius: 6px;
            height: 36px !important; min-height: 36px !important; max-height: 36px !important;
            padding: 0 0.75rem; font-size: 12px !important; color: var(--text-main);
            background: #ffffff; outline: none;
        }
        html.dark .filter-bar input, html.dark .filter-bar select {
            background: #0b1120; border-color: #475569; color: #ffffff;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color: #2563eb; }

        .p-table { width: 100%; border-collapse: collapse; }
        .p-table thead { background: #f8fafc; border-bottom: 1px solid var(--card-border); }
        html.dark .p-table thead { background: #0b1120; border-color: var(--text-main); }
        .p-table thead th {
            border-right: 1px solid var(--card-border) !important;
            border-bottom: 1px solid var(--card-border) !important;
            padding: 0.75rem 1rem;
            font-size: 0.8rem; font-weight: 600; text-transform: none;
            color: #475569; text-align: left; white-space: nowrap;
        }
        html.dark .p-table thead th {
            border-right: 1px solid var(--card-border) !important;
            border-bottom: 1px solid var(--card-border) !important; color: var(--text-muted); }
        .p-table thead th.right { text-align: right; }
        .p-table thead th.center { text-align: center; }
        .p-table tbody tr { border-bottom: none !important; background: #ffffff; }
        html.dark .p-table tbody tr { border-bottom: 1px solid var(--card-border); background: #0f172a; }
        .p-table tbody tr:last-child { border-bottom: none; }
        .p-table tbody tr:hover { background: #f8fafc; }
        html.dark .p-table tbody tr:hover { background: var(--card-border); }
        .p-table tbody td {
            border-right: 1px solid var(--card-border) !important;
            border-bottom: 1px solid var(--card-border) !important; padding: 0.75rem 1rem; vertical-align: middle; font-size: 0.85rem; color: #475569; }
        html.dark .p-table tbody td {
            border-right: 1px solid var(--card-border) !important;
            border-bottom: 1px solid var(--card-border) !important; color: var(--text-muted); }
        .p-table tbody td.right { text-align: right; }
        .p-table tbody td.center { text-align: center; }

        .cell-name { font-size: 0.85rem; font-weight: 600; color: var(--text-main); }
        html.dark .cell-name { color: #ffffff; }
        .cell-sub  { font-size: 0.75rem; color: #64748b; margin-top: 0.15rem; }
        html.dark .cell-sub { color: var(--text-muted); }
        .cell-sku  { font-size: 0.8rem; font-family: ui-monospace,monospace; color: #475569; }
        html.dark .cell-sku { color: var(--text-muted); }
    </style>

    <div class="pg-wrap" x-data="inventoryManager()">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 rounded bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-semibold">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="pg-card">
                {{-- Filter Bar (Medium 38px Height Level) --}}
                <form method="GET" action="{{ route('inventory.index') }}" class="filter-bar flex items-center gap-2.5 flex-wrap p-2.5 text-xs">
                    <div class="relative w-72 min-w-[220px] flex items-center shrink-0">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search product name, SKU..." 
                               style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important; padding-left: 36px !important;"
                               class="w-full text-xs" />
                    </div>

                    @if(count($categories) > 0)
                    <select name="category" onchange="this.form.submit()" 
                            style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important; min-width: 140px;"
                            class="text-xs shrink-0">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('category') == $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                    @endif

                    <select name="status" onchange="this.form.submit()" 
                            style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important; min-width: 140px;"
                            class="text-xs shrink-0">
                        <option value="">All Stock Levels</option>
                        <option value="in_stock" @selected(request('status') == 'in_stock')>In Stock</option>
                        <option value="low_stock" @selected(request('status') == 'low_stock')>Low Stock</option>
                        <option value="out_of_stock" @selected(request('status') == 'out_of_stock')>Out of Stock</option>
                    </select>

                    <button type="submit" 
                            style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important;" 
                            class="px-4 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition shadow-xs inline-flex items-center justify-center cursor-pointer">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'category', 'status']))
                        <a href="{{ route('inventory.index') }}" 
                           style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important;" 
                           class="px-3.5 inline-flex items-center rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 text-xs font-medium transition">
                            Clear
                        </a>
                    @endif
                </form>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="p-table">
                        <thead>
                            <tr>
                                <th class="col-name" style="min-width: 200px;">Product / Model</th>
                                <th>SKU</th>
                                <th>Category & Brand</th>
                                <th class="center">Stock Level</th>
                                <th class="right">Cost Price</th>
                                <th class="right">Stock Value</th>
                                <th class="center">Warranty</th>
                                @if(auth()->user()->isAdmin())
                                <th class="right">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $prod)
                            <tr>
                                <td class="col-name" style="min-width: 200px;">
                                    <div class="cell-name">{{ $prod->name }}</div>
                                    @if($prod->model_no)
                                        <div class="cell-sub">Model: {{ $prod->model_no }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="cell-sku">{{ $prod->sku ?: '—' }}</span>
                                </td>
                                <td>
                                    <div class="font-medium text-slate-800 dark:text-slate-200 text-xs">{{ $prod->category ?: 'General' }}</div>
                                    <div class="cell-sub">{{ $prod->brand ?: 'Unbranded' }}</div>
                                </td>
                                <td class="center">
                                    @if($prod->stock_quantity <= 0)
                                        <span class="status-text" style="color: #dc2626; font-weight: bold;">
                                            Out of Stock (0 {{ $prod->unit }})
                                        </span>
                                    @elseif($prod->isLowStock())
                                        <span class="status-text" style="color: #d97706; font-weight: bold;">
                                            Low: {{ $prod->stock_quantity }} {{ $prod->unit }}
                                        </span>
                                    @else
                                        <span class="status-text" style="color: #16a34a; font-weight: bold;">
                                            {{ $prod->stock_quantity }} {{ $prod->unit }}
                                        </span>
                                    @endif
                                </td>
                                <td class="right">
                                    <span class="text-xs font-medium text-slate-700 dark:text-slate-200">₹{{ number_format($prod->cost_price, 2) }}</span>
                                </td>
                                <td class="right">
                                    <span class="text-xs font-semibold text-slate-900 dark:text-white">₹{{ number_format($prod->cost_price * $prod->stock_quantity, 2) }}</span>
                                </td>
                                <td class="center">
                                    <span class="text-xs text-slate-600 dark:text-slate-400">
                                        {{ $prod->default_warranty_months ?: 24 }} Mo
                                    </span>
                                </td>
                                @if(auth()->user()->isAdmin())
                                <td class="whitespace-nowrap text-right text-sm table-actions" style="text-align:right;">
                                    <button type="button" 
                                            @click="openModal({{ $prod->id }}, {{ json_encode($prod->name) }}, {{ json_encode($prod->sku ?? '') }}, {{ $prod->stock_quantity }}, {{ json_encode($prod->unit ?? 'pcs') }})"
                                            class="action-link">
                                        Stock Adjust
                                    </button>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ auth()->user()->isAdmin() ? 8 : 7 }}" class="text-center py-6 text-slate-500 text-sm">
                                    No products yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 text-sm font-medium text-slate-600 dark:text-slate-400">
                    Total: {{ $products->total() }}
                </div>

                @if($products->hasPages())
                <div class="p-3 border-t border-slate-200 dark:border-slate-800">
                    {{ $products->links() }}
                </div>
                @endif
            </div>

            {{-- Recent Movements Activity Feed --}}
            @if($recentMovements->count())
            <div class="pg-card">
                <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 dark:text-white text-sm">Recent Stock Movements</h3>
                    <a href="{{ route('inventory.movements') }}" class="text-xs font-semibold text-blue-600 hover:underline">View All Logs &rarr;</a>
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
                                <td class="text-xs text-slate-500 whitespace-nowrap">
                                    {{ $mov->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td>
                                    <span class="font-medium text-xs text-slate-900 dark:text-white">{{ $mov->product?->name ?? 'Unknown Product' }}</span>
                                </td>
                                <td>
                                    @if($mov->type === 'in')
                                        <span class="text-xs font-bold text-emerald-600">Stock IN (+)</span>
                                    @elseif($mov->type === 'out')
                                        <span class="text-xs font-bold text-red-600">Stock OUT (-)</span>
                                    @elseif($mov->type === 'job_installation')
                                        <span class="text-xs font-bold text-blue-600">Job Deployment</span>
                                    @elseif($mov->type === 'return')
                                        <span class="text-xs font-bold text-amber-600">Customer Return</span>
                                    @else
                                        <span class="text-xs font-bold text-slate-600">Adjustment</span>
                                    @endif
                                </td>
                                <td class="center font-semibold text-xs {{ $mov->quantity >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                    {{ $mov->quantity > 0 ? '+' : '' }}{{ $mov->quantity }}
                                </td>
                                <td class="center text-xs text-slate-700 dark:text-slate-300">
                                    {{ $mov->balance_after }}
                                </td>
                                <td class="text-xs text-slate-500 max-w-xs truncate">
                                    {{ $mov->notes ?: '—' }}
                                </td>
                                <td class="text-xs text-slate-500">
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
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70"
             x-cloak>
            <div class="bg-white dark:bg-[#0f172a] rounded-[4px] max-w-md w-full p-5 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200" @click.outside="adjustModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Update Stock Level</h3>
                    <button @click="adjustModal = false" class="text-slate-400 hover:text-slate-700 text-2xl font-bold leading-none">&times;</button>
                </div>

                <template x-if="selectedProductId">
                    <form :action="`/inventory/${selectedProductId}/adjust`" method="POST">
                        @csrf
                        <div class="mb-4 p-3 bg-slate-50 dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800">
                            <div class="text-xs font-medium text-slate-500">Product</div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white mt-0.5" x-text="productName"></div>
                            <div class="text-xs text-slate-600 dark:text-slate-300 mt-1 flex items-center justify-between">
                                <span>Current Stock: <strong class="font-bold" x-text="productStock + ' ' + productUnit"></strong></span>
                                <span x-show="productSku" class="font-mono text-slate-500" x-text="'SKU: ' + productSku"></span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Movement Action</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex items-center justify-center p-2 rounded border text-xs font-semibold cursor-pointer text-center"
                                       :class="adjustType === 'in' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700'">
                                    <input type="radio" name="type" value="in" x-model="adjustType" class="hidden">
                                    <span>Stock In</span>
                                </label>
                                <label class="flex items-center justify-center p-2 rounded border text-xs font-semibold cursor-pointer text-center"
                                       :class="adjustType === 'out' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700'">
                                    <input type="radio" name="type" value="out" x-model="adjustType" class="hidden">
                                    <span>Stock Out</span>
                                </label>
                                <label class="flex items-center justify-center p-2 rounded border text-xs font-semibold cursor-pointer text-center"
                                       :class="adjustType === 'adjustment' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700'">
                                    <input type="radio" name="type" value="adjustment" x-model="adjustType" class="hidden">
                                    <span>Set Exact</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                                   x-text="adjustType === 'adjustment' ? 'New Exact Stock Count' : 'Quantity'"></label>
                            <input type="number" name="quantity" min="0" required x-model="adjustQty"
                                   class="w-full border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 rounded px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Reason / Notes</label>
                            <input type="text" name="notes" placeholder="e.g., Vendor Invoice, Physical audit" x-model="adjustNotes"
                                   class="w-full border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 rounded px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="adjustModal = false"
                                    class="px-3.5 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 rounded border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-semibold rounded bg-blue-600 hover:bg-blue-700 text-white transition">
                                Save Movement
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
