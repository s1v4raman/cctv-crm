<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_10px_#f59e0b]"></span>
                    Product Catalogue & Price Book
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">CCTV cameras, NVRs, optics, mounting gear, cables & standard installation services</p>
            </div>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('products.create') }}"
               class="btn-amber">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Product
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        .pg-card {
            background:#0f172a;
            border-radius:1rem;
            border:1px solid rgba(255,255,255,0.07);
            box-shadow:0 10px 25px -5px rgba(0,0,0,0.5);
            overflow:hidden;
        }

        .btn-amber {
            display:inline-flex; align-items:center; gap:.45rem;
            padding:.55rem 1.25rem; font-size:.82rem; font-weight:700;
            background:linear-gradient(135deg, #f59e0b, #d97706); color:#020617 !important; border-radius:.65rem;
            text-decoration:none; transition:all .2s; box-shadow:0 4px 14px rgba(245,158,11,0.25);
        }
        .btn-amber:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(245,158,11,0.4); color:#020617 !important; }

        /* Filter bar */
        .filter-bar {
            display:flex; align-items:center; gap:.75rem;
            padding:1rem 1.25rem; border-bottom:1px solid rgba(255,255,255,0.06); flex-wrap:wrap;
            background:#0b1120;
        }
        .filter-bar input, .filter-bar select {
            border:1px solid #334155; border-radius:.5rem;
            padding:.5rem .85rem; font-size:.82rem; color:#f8fafc;
            outline:none; transition:border-color .15s;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color:#f59e0b; }
        .filter-bar input { min-width:220px; }

        html:not(.dark) .pg-card { background:#ffffff; border-color:#e2e8f0; box-shadow:0 4px 16px rgba(0,0,0,0.05); }
        html:not(.dark) .filter-bar { background:#ffffff; border-bottom:1px solid #e2e8f0; }
        html:not(.dark) .filter-bar input, html:not(.dark) .filter-bar select { border-color:#cbd5e1; color:#0f172a; background:#ffffff; }
        html:not(.dark) .filter-bar input:focus, html:not(.dark) .filter-bar select:focus { border-color:#2563eb; }

        /* Table */
        .p-table { width:100%; border-collapse:collapse; }
        .p-table thead { background:#0b1120; }
        .p-table thead th {
            padding:.85rem 1.25rem;
            font-size:.72rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.05em; color:#94a3b8; text-align:left; white-space:nowrap;
            border-bottom:1px solid rgba(255,255,255,0.06);
        }
        .p-table thead th.right { text-align:right; }
        .p-table thead th.center { text-align:center; }
        .p-table tbody tr { border-bottom:1px solid rgba(255,255,255,0.04); transition:background .12s; }
        .p-table tbody tr:last-child { border-bottom:none; }
        .p-table tbody tr:hover { background:rgba(30,41,59,0.5); }
        .p-table tbody td { padding:1rem 1.25rem; vertical-align:middle; font-size:.85rem; }
        .p-table tbody td.right { text-align:right; }
        .p-table tbody td.center { text-align:center; }

        .cell-name { font-size:.9rem; font-weight:700; color:#f8fafc; }
        .cell-desc { font-size:.75rem; color:#64748b; margin-top:.2rem; max-width:280px; }
        .cell-sku  { font-size:.78rem; color:#38bdf8; font-family:ui-monospace,monospace; font-weight:600; }
        .cell-unit { font-size:.8rem; color:#94a3b8; font-weight:500; }
        .cell-cost { font-size:.85rem; color:#94a3b8; }
        .cell-price { font-size:.95rem; font-weight:800; color:#fbbf24; font-family:'Outfit',sans-serif; }

        /* Margin badge */
        .margin-chip {
            display:inline-block; padding:.2rem .55rem; border-radius:.4rem;
            font-size:.72rem; font-weight:700;
            background:rgba(16,185,129,0.15); color:#34d399; border:1px solid rgba(16,185,129,0.3);
        }
        .margin-chip.neg { background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3); }

        /* Active/Inactive */
        .badge-active   { display:inline-flex; padding:.2rem .65rem; border-radius:999px; font-size:.7rem; font-weight:700; background:rgba(16,185,129,0.15); color:#34d399; border:1px solid rgba(16,185,129,0.3); }
        .badge-inactive { display:inline-flex; padding:.2rem .65rem; border-radius:999px; font-size:.7rem; font-weight:700; background:rgba(148,163,184,0.1); color:#94a3b8; border:1px solid rgba(148,163,184,0.2); }

        /* Action buttons */
        .btn-edit {
            display:inline-flex; align-items:center; gap:.3rem;
            padding:.4rem .8rem; border-radius:.5rem;
            font-size:.75rem; font-weight:600;
            background:rgba(56,189,248,0.1); color:#38bdf8;
            border:1px solid rgba(56,189,248,0.3); text-decoration:none;
            transition:all .15s;
        }
        .btn-edit:hover { background:rgba(56,189,248,0.2); color:#7dd3fc; }
        .btn-del {
            display:inline-flex; align-items:center; gap:.3rem;
            padding:.4rem .8rem; border-radius:.5rem;
            font-size:.75rem; font-weight:600;
            background:rgba(239,68,68,0.1); color:#f87171;
            border:1px solid rgba(239,68,68,0.3); cursor:pointer;
            transition:all .15s; font-family:inherit;
        }
        .btn-del:hover { background:rgba(239,68,68,0.2); color:#fca5a5; }

        .pg-links { padding:1rem 1.25rem; border-top:1px solid rgba(255,255,255,0.06); }
        .empty-state { text-align:center; padding:3rem 1rem; color:#64748b; font-size:.85rem; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div style="margin-bottom:1.25rem;padding:.85rem 1.25rem;border-radius:.75rem;background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);color:#34d399;font-size:.85rem;">
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
                    <button type="submit" class="px-3 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg transition shadow">
                        Search
                    </button>
                    @if(request('search') || request('category'))
                        <a href="{{ route('products.index') }}" class="px-2.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-lg transition">
                            Clear
                        </a>
                    @endif
                    <span id="p-count" style="margin-left:auto;font-size:.75rem;color:#64748b;"></span>
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
                                        <span class="margin-chip {{ $margin < 0 ? 'neg' : '' }}">
                                            {{ $margin }}%
                                        </span>
                                    </td>

                                    <td class="center">
                                        @if($product->is_active)
                                            <span class="badge-active">Active</span>
                                        @else
                                            <span class="badge-inactive">Inactive</span>
                                        @endif
                                    </td>

                                    <td class="right">
                                        @if(auth()->user()->isAdmin())
                                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:.5rem">
                                                <a href="{{ route('products.edit', $product) }}" class="btn-edit">
                                                    <svg style="width:.8rem;height:.8rem" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                                    </svg>
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('products.destroy', $product) }}"
                                                      onsubmit="return confirm('Delete {{ addslashes($product->name) }}? This cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-del">
                                                        <svg style="width:.8rem;height:.8rem" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="cell-text" style="color:#64748b;font-size:0.8rem;">Read-only</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <svg style="width:2.5rem;height:2.5rem;color:#475569;margin:0 auto .75rem" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                            </svg>
                                            No products yet. 
                                            @if(auth()->user()->isAdmin())
                                                <a href="{{ route('products.create') }}" style="color:#f59e0b;font-weight:600;">Add the first product →</a>
                                            @endif
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
        const status = document.getElementById('p-status').value;
        const rows   = document.querySelectorAll('.p-row');
        let visible  = 0;

        rows.forEach(row => {
            const sm = row.dataset.search.includes(q);
            const st = status === '' || row.dataset.active === status;
            const ok = sm && st;
            row.style.display = ok ? '' : 'none';
            if (ok) visible++;
        });

        const total = rows.length;
        document.getElementById('p-count').textContent =
            visible === total ? `${total} product${total !== 1 ? 's' : ''}`
                              : `${visible} of ${total} products`;
    }
    filterProducts();
    </script>
</x-app-layout>