<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Products</h2>
            </div>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('products.create') }}" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                + Add Product
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        .pg-card {
            background:#ffffff;
            border-radius:4px;
            border:1px solid #e2e8f0;
            overflow:hidden;
        }
        html.dark .pg-card {
            background:#0f172a;
            border-color:#1e293b;
        }

        /* Filter bar */
        .filter-bar {
            display:flex; align-items:center; gap:.75rem;
            padding:.75rem 1rem; border-bottom:1px solid #e2e8f0; flex-wrap:wrap;
            background:#ffffff;
        }
        html.dark .filter-bar {
            background:#0b1120;
            border-color:#1e293b;
        }
        .filter-bar input, .filter-bar select {
            border:1px solid #cbd5e1; border-radius:4px;
            height:38px; padding:0 .75rem; font-size:.85rem; color:#0f172a;
            background:#ffffff; outline:none;
        }
        html.dark .filter-bar input, html.dark .filter-bar select {
            background:#0b1120; border-color:#334155; color:#ffffff;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color:#2563eb; }
        .filter-bar input { min-width:220px; }

        /* Table */
        .p-table { width:100%; border-collapse:collapse; }
        .p-table thead { background:#f8fafc; border-bottom:1px solid #e2e8f0; }
        html.dark .p-table thead { background:#0b1120; border-color:#1e293b; }
        .p-table thead th {
            padding:.75rem 1rem;
            font-size:.8rem; font-weight:600; text-transform:none;
            color:#475569; text-align:left; white-space:nowrap;
        }
        html.dark .p-table thead th { color:#94a3b8; }
        .p-table thead th.right { text-align:right; }
        .p-table thead th.center { text-align:center; }
        .p-table tbody tr { border-bottom:1px solid #e2e8f0; background:#ffffff; }
        html.dark .p-table tbody tr { border-bottom-color:#1e293b; background:#0f172a; }
        .p-table tbody tr:last-child { border-bottom:none; }
        .p-table tbody tr:hover { background:#f8fafc; }
        html.dark .p-table tbody tr:hover { background:#1e293b; }
        .p-table tbody td { padding:.75rem 1rem; vertical-align:middle; font-size:.85rem; color:#475569; }
        html.dark .p-table tbody td { color:#cbd5e1; }
        .p-table tbody td.right { text-align:right; }
        .p-table tbody td.center { text-align:center; }

        .cell-name { font-size:.85rem; font-weight:600; color:#0f172a; }
        html.dark .cell-name { color:#ffffff; }
        .cell-desc { font-size:.75rem; color:#64748b; margin-top:.15rem; max-width:280px; }
        .cell-sku  { font-size:.8rem; font-family:ui-monospace,monospace; font-weight:500; color:#475569; }
        html.dark .cell-sku { color:#94a3b8; }
        .cell-unit { font-size:.85rem; color:#64748b; }
        .cell-cost { font-size:.85rem; color:#475569; }
        html.dark .cell-cost { color:#94a3b8; }
        .cell-price { font-size:.85rem; font-weight:700; color:#0f172a; }
        html.dark .cell-price { color:#ffffff; }

        .pg-links { padding:.75rem 1rem; border-top:1px solid #e2e8f0; }
        html.dark .pg-links { border-top-color:#1e293b; }
        .empty-state { text-align:center; padding:3rem 1rem; color:#64748b; font-size:.85rem; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            <div class="pg-card">

                <form method="GET" action="{{ route('products.index') }}" class="filter-bar flex items-center gap-2 flex-wrap">
                    <div class="relative flex-1 min-w-[220px]">
                        <input type="text" name="search" id="p-search" value="{{ request('search') }}" placeholder="Search product name, SKU, model, brand…" oninput="filterProducts()" class="w-full">
                    </div>
                    <select name="category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="camera" @selected(request('category') === 'camera')>Camera</option>
                        <option value="recorder" @selected(request('category') === 'recorder')>Recorder / NVR</option>
                        <option value="storage" @selected(request('category') === 'storage')>Storage / HDD</option>
                        <option value="cable" @selected(request('category') === 'cable')>Cables & Wire</option>
                        <option value="power" @selected(request('category') === 'power')>Power Supply / SMPS</option>
                        <option value="accessory" @selected(request('category') === 'accessory')>Accessories</option>
                        <option value="service" @selected(request('category') === 'service')>Labor / Service</option>
                    </select>
                    <button type="submit" class="h-[38px] px-4 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                        Search
                    </button>
                    @if(request('search') || request('category'))
                        <a href="{{ route('products.index') }}" class="h-[38px] px-3.5 inline-flex items-center rounded border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium transition">
                            Clear
                        </a>
                    @endif
                    <span id="p-count" style="margin-left:auto;font-size:.8rem;color:#64748b;"></span>
                </form>

                <div style="overflow-x:auto">
                    <table class="p-table">
                        <thead>
                            <tr>
                                <th>Product / Service</th>
                                <th>SKU</th>
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
                                    $margin = $product->unit_price > 0
                                        ? round((($product->unit_price - $product->cost_price) / $product->unit_price) * 100, 1)
                                        : 0;
                                @endphp
                                <tr class="p-row"
                                    data-search="{{ strtolower($product->name . ' ' . $product->sku) }}"
                                    data-active="{{ $product->is_active ? '1' : '0' }}">

                                    <td>
                                        <div class="cell-name">{{ $product->name }}</div>
                                        @if($product->description)
                                            <div class="cell-desc">{{ Str::limit($product->description, 55) }}</div>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="cell-sku">{{ $product->sku ?: '—' }}</span>
                                    </td>

                                    <td>
                                        <span class="cell-unit">{{ $product->unit }}</span>
                                    </td>

                                    <td class="right">
                                        <span class="cell-cost">₹{{ number_format((float)$product->cost_price, 0) }}</span>
                                    </td>

                                    <td class="right">
                                        <span class="cell-price">₹{{ number_format((float)$product->unit_price, 0) }}</span>
                                    </td>

                                    <td class="right">
                                        <span class="text-xs font-semibold {{ $margin < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                            {{ $margin }}%
                                        </span>
                                    </td>

                                    <td class="center">
                                        @if($product->is_active)
                                            <span class="text-xs font-bold text-emerald-600">Active</span>
                                        @else
                                            <span class="text-xs font-bold text-slate-400">Inactive</span>
                                        @endif
                                    </td>

                                    <td class="right whitespace-nowrap text-sm" style="text-align:right;">
                                        @if(auth()->user()->isAdmin())
                                            <div class="inline-flex items-center gap-1.5 justify-end">
                                                <a href="{{ route('products.edit', $product) }}" class="text-blue-600 hover:underline">Edit</a>
                                                <span class="text-slate-300">|</span>
                                                <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline"
                                                      onsubmit="return confirm('Delete {{ addslashes($product->name) }}? This cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:underline cursor-pointer bg-transparent border-0 p-0 font-medium">Delete</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            No products yet.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                    <div class="pg-links">{{ $products->links() }}</div>
                @endif
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
        document.getElementById('p-count').textContent =
            visible === total ? `${total} product${total !== 1 ? 's' : ''}`
                              : `${visible} of ${total} products`;
    }
    </script>
</x-app-layout>