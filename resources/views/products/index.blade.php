<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Products &amp; Inventory</h2>
                @if($pendingCount > 0)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        {{ $pendingCount }} Pending Review
                    </span>
                @endif
            </div>
            @if(auth()->user()->isOffice())
            <a href="{{ route('products.create') }}" class="btn-primary">
                + Add Product
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        .pg-card {
            background:#ffffff;
            border-radius:6px;
            border:1px solid #e2e8f0;
            overflow:hidden;
        }
        html.dark .pg-card {
            background:#0f172a;
            border-color: var(--text-main);
        }

        /* Status Tabs */
        .status-tabs {
            display:flex;
            align-items:center;
            gap:.5rem;
            padding:.5rem 1rem;
            background:#f8fafc;
            border-bottom:1px solid var(--card-border);
            overflow-x:auto;
        }
        html.dark .status-tabs {
            background:#0b1120;
            border-color:var(--card-border);
        }
        .status-tab {
            display:inline-flex;
            align-items:center;
            gap:.375rem;
            padding:.375rem .75rem;
            border-radius:6px;
            font-size:12px;
            font-weight:600;
            color:#64748b;
            text-decoration:none;
            transition:all .15s ease;
        }
        .status-tab:hover {
            color:#0f172a;
            background:#e2e8f0;
        }
        html.dark .status-tab:hover {
            color:#f8fafc;
            background:#1e293b;
        }
        .status-tab.active {
            background:#2563eb;
            color:#ffffff;
        }
        html.dark .status-tab.active {
            background:#2563eb;
            color:#ffffff;
        }
        .status-tab .badge {
            font-size:10px;
            font-weight:700;
            padding:1px 6px;
            border-radius:999px;
            background:rgba(0,0,0,0.08);
        }
        .status-tab.active .badge {
            background:rgba(255,255,255,0.25);
            color:#ffffff;
        }

        /* Filter bar */
        .filter-bar {
            display:flex; align-items:center; gap:.75rem;
            padding:.75rem 1rem; border-bottom:1px solid var(--card-border); flex-wrap:wrap;
            background:#ffffff;
        }
        html.dark .filter-bar {
            background:#0b1120;
            border-color:var(--card-border);
        }
        .filter-bar input, .filter-bar select {
            border:1px solid var(--card-border); border-radius:6px;
            height:36px !important; min-height:36px !important; max-height:36px !important;
            padding:0 .75rem; font-size:12px !important; color: var(--text-main);
            background:#ffffff; outline:none;
        }
        html.dark .filter-bar input, html.dark .filter-bar select {
            background:#0b1120; border-color:#475569; color:#ffffff;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color:#2563eb; }
        .filter-bar input { min-width:220px; }

        /* Table */
        .p-table { width:100%; border-collapse:collapse; }
        .p-table thead { background:#f8fafc; border-bottom:1px solid var(--card-border); }
        html.dark .p-table thead { background:#0b1120; border-color: var(--text-main); }
        .p-table thead th {
            border-right: 1px solid var(--card-border) !important;
            border-bottom: 1px solid var(--card-border) !important;
            padding:.75rem 1rem;
            font-size:.8rem; font-weight:600; text-transform:none;
            color:#475569; text-align:left; white-space:nowrap;
        }
        html.dark .p-table thead th {
            border-right: 1px solid var(--card-border) !important;
            border-bottom: 1px solid var(--card-border) !important; color: var(--text-muted); }
        .p-table thead th.right { text-align:right; }
        .p-table thead th.center { text-align:center; }
        .p-table tbody tr { border-bottom:1px solid var(--card-border); background:#ffffff; }
        html.dark .p-table tbody tr { border-bottom:1px solid var(--card-border); background:#0f172a; }
        .p-table tbody tr:last-child { border-bottom:none; }
        .p-table tbody tr:hover { background:#f8fafc; }
        html.dark .p-table tbody tr:hover { background:var(--card-border); }
        .p-table tbody td {
            border-right: 1px solid var(--card-border) !important;
            border-bottom: 1px solid var(--card-border) !important; padding:.75rem 1rem; vertical-align:middle; font-size:.85rem; color:#475569; }
        html.dark .p-table tbody td {
            border-right: 1px solid var(--card-border) !important;
            border-bottom: 1px solid var(--card-border) !important; color: var(--text-muted); }
        .p-table tbody td.right { text-align:right; }
        .p-table tbody td.center { text-align:center; }

        .cell-name { font-size:.85rem; font-weight:600; color: var(--text-main); }
        html.dark .cell-name { color:#ffffff; }
        .cell-desc { font-size:.75rem; color:#64748b; margin-top:.15rem; max-width:280px; }
        .cell-sku  { font-size:.8rem; font-family:ui-monospace,monospace; font-weight:500; color:#475569; }
        html.dark .cell-sku { color: var(--text-muted); }
        .cell-unit { font-size:.85rem; color:#64748b; }
        .cell-cost { font-size:.85rem; color:#475569; }
        html.dark .cell-cost { color: var(--text-muted); }
        .cell-price { font-size:.85rem; font-weight:700; color: var(--text-main); }
        html.dark .cell-price { color:#ffffff; }

        .pg-links { padding:.75rem 1rem; border-top:1px solid #e2e8f0; }
        html.dark .pg-links { border-top-color: var(--text-main); }
        .empty-state { text-align:center; padding:3rem 1rem; color:#64748b; font-size:.85rem; }
    </style>

    <div class="pg-wrap" x-data="{
        approveModalOpen: false,
        mergeModalOpen: false,
        selectedProduct: { id: null, name: '', category: '', cost_price: 0, unit_price: 0, unit: 'Nos' },
        
        openApprove(p) {
            this.selectedProduct = Object.assign({}, p);
            this.approveModalOpen = true;
        },
        openMerge(p) {
            this.selectedProduct = Object.assign({}, p);
            this.mergeModalOpen = true;
        }
    }">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between">
                    <span>{{ session('status') }}</span>
                    <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-semibold">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="pg-card">

                {{-- Status Filter Tabs --}}
                <div class="status-tabs">
                    <a href="{{ route('products.index', array_merge(request()->except('status'), ['status' => 'all'])) }}"
                       class="status-tab {{ ($statusFilter ?? 'all') === 'all' ? 'active' : '' }}">
                        <span>All Items</span>
                        <span class="badge">{{ $totalCount }}</span>
                    </a>
                    <a href="{{ route('products.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}"
                       class="status-tab {{ ($statusFilter ?? '') === 'pending' ? 'active' : '' }}">
                        <span class="inline-flex items-center gap-1.5">
                            @if($pendingCount > 0)
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                            @endif
                            Pending Review
                        </span>
                        <span class="badge {{ $pendingCount > 0 && ($statusFilter ?? '') !== 'pending' ? 'bg-amber-100 text-amber-800 font-bold' : '' }}">
                            {{ $pendingCount }}
                        </span>
                    </a>
                    <a href="{{ route('products.index', array_merge(request()->except('status'), ['status' => 'approved'])) }}"
                       class="status-tab {{ ($statusFilter ?? '') === 'approved' ? 'active' : '' }}">
                        <span>Approved</span>
                        <span class="badge">{{ $approvedCount }}</span>
                    </a>
                </div>

                {{-- Search & Category Filter --}}
                <form data-auto-submit method="GET" action="{{ route('products.index') }}" class="filter-bar flex items-center gap-2 flex-wrap">
                    <input type="hidden" name="status" value="{{ $statusFilter ?? 'all' }}">
                    <div class="relative flex-1 min-w-[220px]">
                        <input aria-label="Search product name, SKU, model, brand" type="text" name="search" id="p-search" value="{{ request('search') }}" placeholder="Search product name, SKU, model, brand…" oninput="filterProducts()" class="w-full">
                    </div>
                    <select aria-label="Category" name="category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="camera" @selected(request('category') === 'camera')>Camera</option>
                        <option value="recorder" @selected(request('category') === 'recorder')>Recorder / NVR</option>
                        <option value="storage" @selected(request('category') === 'storage')>Storage / HDD</option>
                        <option value="cable" @selected(request('category') === 'cable')>Cables & Wire</option>
                        <option value="power" @selected(request('category') === 'power')>Power Supply / SMPS</option>
                        <option value="accessory" @selected(request('category') === 'accessory')>Accessories</option>
                        <option value="networking" @selected(request('category') === 'networking')>Networking</option>
                        <option value="service" @selected(request('category') === 'service')>Labor / Service</option>
                    </select>
                    <button type="submit" style="height:36px !important; min-height:36px !important; max-height:36px !important;" class="h-[36px] px-4 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition shadow-xs inline-flex items-center justify-center cursor-pointer">
                        Search
                    </button>
                    @if(request('search') || request('category'))
                        <a href="{{ route('products.index', ['status' => $statusFilter ?? 'all']) }}" style="height:36px !important; min-height:36px !important; max-height:36px !important;" class="h-[36px] px-3.5 inline-flex items-center rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 text-xs font-medium transition">
                            Clear
                        </a>
                    @endif
                    <span id="p-count" style="margin-left:auto;font-size:.8rem;color:#64748b;"></span>
                </form>

                <div style="overflow-x:auto">
                    <table class="p-table crm-responsive">
                        <thead>
                            <tr>
                                <th>Product / Service</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th class="right">Cost Price</th>
                                <th class="right">Sale Price</th>
                                <th class="right">Margin</th>
                                <th class="center">Status</th>
                                <th class="right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                @php
                                    $isPending = ($product->status === 'pending');
                                    $margin = $product->unit_price > 0
                                        ? round((($product->unit_price - $product->cost_price) / $product->unit_price) * 100, 1)
                                        : 0;
                                @endphp
                                <tr class="p-row {{ $isPending ? 'bg-amber-50/40 dark:bg-amber-950/20' : '' }}"
                                    data-search="{{ strtolower($product->name . ' ' . $product->sku . ' ' . ($product->category ?? '')) }}"
                                    data-active="{{ $product->is_active ? '1' : '0' }}">

                                    <td>
                                        <div class="cell-name flex items-center gap-1.5">
                                            <span>{{ $product->name }}</span>
                                            @if($isPending)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                                                    Auto-Learned
                                                </span>
                                            @endif
                                        </div>
                                        @if($product->description)
                                            <div class="cell-desc">{{ Str::limit($product->description, 55) }}</div>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="cell-sku">{{ $product->sku ?: '—' }}</span>
                                    </td>

                                    <td>
                                        <span class="text-xs text-slate-600 dark:text-slate-300 font-medium capitalize">
                                            {{ $product->category ?: 'Uncategorized' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="cell-unit">{{ $product->unit ?: 'Nos' }}</span>
                                    </td>

                                    <td class="right">
                                        <span class="cell-cost">₹{{ number_format((float)$product->cost_price, 2) }}</span>
                                    </td>

                                    <td class="right">
                                        <span class="cell-price">₹{{ number_format((float)$product->unit_price, 2) }}</span>
                                    </td>

                                    <td class="right">
                                        <span class="text-xs font-semibold {{ $margin < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                            {{ $margin }}%
                                        </span>
                                    </td>

                                    <td class="center">
                                        @if($isPending)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Pending Review
                                            </span>
                                        @elseif($product->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                Approved
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>

                                    <td class="right whitespace-nowrap text-sm table-actions" style="text-align:right;">
                                        @if(auth()->user()->isOffice())
                                            <div class="inline-flex items-center gap-1.5">
                                                @if($isPending)
                                                    <button type="button"
                                                            @click="openApprove({
                                                                id: {{ $product->id }},
                                                                name: '{{ addslashes($product->name) }}',
                                                                category: '{{ addslashes($product->category ?? '') }}',
                                                                cost_price: {{ (float)$product->cost_price }},
                                                                unit_price: {{ (float)$product->unit_price }},
                                                                unit: '{{ $product->unit ?: 'Nos' }}'
                                                            })"
                                                            class="inline-flex items-center px-2 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-2xs">
                                                        Approve / Set Price
                                                    </button>
                                                @endif

                                                @if(auth()->user()->isAdmin())
                                                <button type="button"
                                                        @click="openMerge({
                                                            id: {{ $product->id }},
                                                            name: '{{ addslashes($product->name) }}',
                                                            sku: '{{ addslashes($product->sku ?? '') }}'
                                                        })"
                                                        title="Merge into another product to eliminate duplicate"
                                                        class="inline-flex items-center px-2 py-1 rounded border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold transition">
                                                    Merge
                                                </button>
                                                @endif

                                                <a href="{{ route('products.edit', $product) }}" class="action-link text-xs">Edit</a>

                                                @if(auth()->user()->isAdmin())
                                                <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline"
                                                      data-confirm="Delete {{ $product->name }}? This cannot be undone.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="danger-link text-xs">Delete</button>
                                                </form>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
                                        <div class="empty-state">
                                            <x-empty-message empty="No products yet" filtered="No products match these filters." :filters="['search', 'category']" />
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 text-sm font-medium text-slate-600 dark:text-slate-400 flex items-center justify-between">
                    <span>Showing {{ $products->count() }} of {{ $products->total() }} items</span>
                    @if($pendingCount > 0)
                        <span class="text-xs text-amber-700 dark:text-amber-400 font-semibold">
                            <svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $pendingCount }} item(s) awaiting admin review and pricing
                        </span>
                    @endif
                </div>

                @if($products->hasPages())
                    <div class="pg-links">{{ $products->links() }}</div>
                @endif
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- MODAL 1: APPROVE PRODUCT & SET PRICE / CATEGORY                 --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div x-show="approveModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="approveModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Approve Product</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Assign category, cost price, and sale price to approve this auto-learned item</p>
                    </div>
                    <button type="button" @click="approveModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form :action="selectedProduct?.id ? `/products/${selectedProduct.id}/approve` : '#'" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Product Name</label>
                        <div x-text="selectedProduct?.name" class="font-semibold text-slate-900 dark:text-white text-sm px-3 py-2 bg-slate-50 dark:bg-slate-800/80 rounded-lg border border-slate-200 dark:border-slate-700"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Category *</label>
                            <select aria-label="Category" name="category" x-model="selectedProduct.category" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                                <option value="">-- Select Category --</option>
                                <option value="camera">Camera</option>
                                <option value="recorder">Recorder / NVR</option>
                                <option value="storage">Storage / HDD</option>
                                <option value="cable">Cables & Wire</option>
                                <option value="power">Power Supply / SMPS</option>
                                <option value="accessory">Accessories / Rack / Conduit</option>
                                <option value="networking">Networking (Switches, Routers)</option>
                                <option value="service">Labor / Service</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Unit of Measure *</label>
                            <select aria-label="Unit of Measure" name="unit" x-model="selectedProduct.unit" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                                <option value="Nos">Nos</option>
                                <option value="Mtr">Mtr</option>
                                <option value="Box">Box</option>
                                <option value="Set">Set</option>
                                <option value="Job">Job</option>
                                <option value="Pkt">Pkt</option>
                                <option value="Roll">Roll</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Cost Price (₹) *</label>
                            <input aria-label="Cost Price (₹)" type="number" step="0.01" min="0" name="cost_price" x-model="selectedProduct.cost_price" required placeholder="0.00" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg font-mono font-bold outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Sale / Unit Price (₹) *</label>
                            <input aria-label="Sale / Unit Price (₹)" type="number" step="0.01" min="0" name="unit_price" x-model="selectedProduct.unit_price" required placeholder="0.00" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg font-mono font-bold outline-none">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="approveModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Approve &amp; Save</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- MODAL 2: MERGE DUPLICATE PRODUCT                                --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div x-show="mergeModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="mergeModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Merge Duplicate Product</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Combine duplicate records into a master product</p>
                    </div>
                    <button type="button" @click="mergeModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form :action="selectedProduct?.id ? `/products/${selectedProduct.id}/merge` : '#'" method="POST" class="space-y-4"
                      data-confirm="Are you sure you want to merge this product? All existing references will point to the target product and this duplicate will be deleted.">
                    @csrf

                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs leading-relaxed">
                        <span>Merging will reassign all Bill of Materials (BOM) items, quotation lines, and stock records referencing</span>
                        <strong x-text="` '${selectedProduct?.name}' `"></strong>
                        <span>to the master product selected below, then delete this duplicate.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Duplicate to Remove</label>
                        <div x-text="selectedProduct?.name" class="font-semibold text-rose-600 dark:text-rose-400 text-sm px-3 py-2 bg-slate-50 dark:bg-slate-800/80 rounded-lg border border-slate-200 dark:border-slate-700"></div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Target Master Product to Keep *</label>
                        <select aria-label="Target Master Product to Keep" name="target_product_id" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none font-medium">
                            <option value="">-- Choose Target Master Product --</option>
                            @foreach($allProducts as $ap)
                                <option value="{{ $ap->id }}" x-show="selectedProduct?.id !== {{ $ap->id }}">
                                    {{ $ap->name }} ({{ $ap->sku ?: 'No SKU' }}) [{{ ucfirst($ap->status ?: 'approved') }}]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="mergeModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md transition">
                            Merge &amp; Delete Duplicate
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
    function filterProducts() {
        const q      = document.getElementById('p-search').value.toLowerCase();
        const rows   = document.querySelectorAll('.p-row');
        let visible  = 0;

        rows.forEach(row => {
            const sm = row.dataset.search.includes(q);
            row.style.display = sm ? '' : 'none';
            if (sm) visible++;
        });

        const total = rows.length;
        const countEl = document.getElementById('p-count');
        if (countEl) {
            countEl.textContent =
                visible === total ? `${total} product${total !== 1 ? 's' : ''}`
                                  : `${visible} of ${total} products`;
        }
    }
    </script>
</x-app-layout>