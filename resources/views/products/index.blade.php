<x-app-layout>
    @php
        $categories = ['camera' => 'Camera', 'recorder' => 'Recorder / NVR', 'storage' => 'Storage / HDD', 'cable' => 'Cables & Wire', 'power' => 'Power Supply / SMPS', 'accessory' => 'Accessories', 'networking' => 'Networking', 'service' => 'Labor / Service'];
        $views = [
            ['label' => 'All Products (' . $totalCount . ')', 'url' => route('products.index'), 'active' => ($statusFilter ?? 'all') === 'all'],
            ['label' => 'Pending Review (' . $pendingCount . ')', 'url' => route('products.index', ['status' => 'pending']), 'active' => $statusFilter === 'pending'],
            ['label' => 'Approved (' . $approvedCount . ')', 'url' => route('products.index', ['status' => 'approved']), 'active' => $statusFilter === 'approved'],
        ];
        $filterCount = collect(['search', 'category'])->filter(fn ($f) => request()->filled($f))->count();
    @endphp

    <div x-data="{
        approveModalOpen: false,
        mergeModalOpen: false,
        selectedProduct: { id: null, name: '', category: '', cost_price: 0, unit_price: 0, unit: 'Nos' },
        openApprove(p) { this.selectedProduct = Object.assign({}, p); this.approveModalOpen = true; },
        openMerge(p) { this.selectedProduct = Object.assign({}, p); this.mergeModalOpen = true; }
    }">
        @if ($errors->any())
            <div class="px-4 py-2 text-sm bg-rose-50 text-rose-800 border-b border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <x-list-page key="products" :paginator="$products" :views="$views"
            :sorts="['review' => 'Waiting for review first', 'name' => 'Name, A to Z', 'price_desc' => 'Sale price, high to low', 'price_asc' => 'Sale price, low to high', 'newest' => 'Newest first']"
            :create-url="auth()->user()->isOffice() ? route('products.create') : null" create-label="Add Product"
            filter-title="Filter Products by" :filter-action="route('products.index')"
            :keep="['status' => ($statusFilter ?? 'all') === 'all' ? null : $statusFilter, 'sort' => request('sort'), 'per_page' => request('per_page')]"
            :filter-count="$filterCount" :clear-url="route('products.index', array_filter(['status' => ($statusFilter ?? 'all') === 'all' ? null : $statusFilter]))"
            :columns="['sku' => 'SKU', 'category' => 'Category', 'unit' => 'Unit', 'cost' => 'Cost Price', 'price' => 'Sale Price', 'margin' => 'Margin', 'status' => 'Status']"
            empty-text="No products yet." filtered-text="No products match these filters.">

            <x-slot name="filters">
                <div class="ld-sub">Name, SKU, brand or model</div>
                <input type="text" name="search" value="{{ request('search') }}" class="ld-input" aria-label="Name, SKU, brand or model">

                <div class="ld-sub">Category</div>
                <select name="category" class="ld-input" aria-label="Category">
                    <option value="">Any category</option>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </x-slot>

            <x-slot name="head">
                <th style="min-width: 240px">Product / Service</th>
                <th x-show="cols.sku" style="min-width: 120px">SKU</th>
                <th x-show="cols.category" style="min-width: 130px">Category</th>
                <th x-show="cols.unit" style="min-width: 70px">Unit</th>
                <th x-show="cols.cost" style="min-width: 110px; text-align: right">Cost Price</th>
                <th x-show="cols.price" style="min-width: 110px; text-align: right">Sale Price</th>
                <th x-show="cols.margin" style="min-width: 80px; text-align: right">Margin</th>
                <th x-show="cols.status" style="min-width: 120px">Status</th>
            </x-slot>

            @foreach ($products as $product)
                @php
                    $isPending = $product->status === 'pending';
                    $margin = $product->unit_price > 0 ? round((($product->unit_price - $product->cost_price) / $product->unit_price) * 100, 1) : 0;
                @endphp
                <tr>
                    <x-list-pick :id="$product->id" :label="$product->name" />
                    <td>
                        <div>{{ $product->name }}@if($isPending) <span class="text-xs" style="color: var(--ld-muted)">· added from a job</span>@endif</div>
                        @if($product->description)<div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ Str::limit($product->description, 60) }}</div>@endif
                    </td>
                    <td x-show="cols.sku" class="whitespace-nowrap">{{ $product->sku ?: '—' }}</td>
                    <td x-show="cols.category">{{ $categories[$product->category] ?? ($product->category ? ucfirst($product->category) : 'Uncategorized') }}</td>
                    <td x-show="cols.unit">{{ $product->unit ?: 'Nos' }}</td>
                    <td x-show="cols.cost" class="whitespace-nowrap" style="text-align: right">{{ \App\Support\Inr::money($product->cost_price) }}</td>
                    <td x-show="cols.price" class="whitespace-nowrap" style="text-align: right">{{ \App\Support\Inr::money($product->unit_price) }}</td>
                    <td x-show="cols.margin" style="text-align: right"><span class="{{ $margin < 0 ? 'text-rose-700 dark:text-rose-400' : '' }}">{{ $margin }}%</span></td>
                    <td x-show="cols.status">
                        @if($isPending)
                            <span class="text-amber-700 dark:text-amber-400">Pending Review</span>
                        @elseif($product->is_active)
                            Approved
                        @else
                            <span style="color: var(--ld-muted)">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @if(auth()->user()->isOffice())
                            <div class="ld-row-actions" @if($isPending) style="opacity: 1" @endif>
                                @if($isPending)
                                    <button type="button" title="Approve and set price" aria-label="Approve {{ $product->name }}" style="width: auto !important; padding: 0 8px !important; color: var(--ld-accent) !important; font-size: 13px; font-weight: 600"
                                            @click="openApprove({ id: {{ $product->id }}, name: @js($product->name), category: @js($product->category ?? ''), cost_price: {{ (float) $product->cost_price }}, unit_price: {{ (float) $product->unit_price }}, unit: @js($product->unit ?: 'Nos') })">
                                        Approve
                                    </button>
                                @endif
                                @if(auth()->user()->isAdmin())
                                    <button type="button" title="Merge into another product" aria-label="Merge {{ $product->name }}"
                                            @click="openMerge({ id: {{ $product->id }}, name: @js($product->name), sku: @js($product->sku ?? '') })">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 4v6a4 4 0 004 4h6m0 0l-3-3m3 3l-3 3M7 20v-4"/></svg>
                                    </button>
                                @endif
                                <a href="{{ route('products.edit', $product) }}" class="plain-link" title="Edit" aria-label="Edit {{ $product->name }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.2 5.2l3.6 3.6M4 20l4.5-1 10.3-10.3a2.5 2.5 0 00-3.5-3.5L5 15.5 4 20z"/></svg>
                                </a>
                                @if(auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm="Delete {{ $product->name }}? This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-cancel" title="Delete" aria-label="Delete {{ $product->name }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-list-page>

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
                    <x-close-button @click="approveModalOpen = false" />
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
                    <x-close-button @click="mergeModalOpen = false" />
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
</x-app-layout>
