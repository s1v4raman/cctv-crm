<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Procurement & Inventory Reports</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Centralized visibility into warehouse stock levels, purchase spend, vendor ageing, and project material costs.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('inventory.index') }}" class="btn-secondary">
                    Warehouse Inventory
                </a>
                <a href="{{ route('purchase-orders.index') }}" class="btn-secondary">
                    Purchase Orders
                </a>
                <a href="{{ route('finance.payables.index') }}" class="btn-secondary">
                    Accounts Payable
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Navigation Tabs --}}
        <div class="border-b border-slate-200 dark:border-slate-800">
            <nav class="flex space-x-6">
                <a href="{{ route('reports.procurement.index', ['tab' => 'stock']) }}"
                   class="pb-3 text-sm font-semibold transition border-b-2 {{ $tab === 'stock' ? 'border-brand-600 text-brand-600 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                    1. Stock on Hand & Low Stock
                </a>
                <a href="{{ route('reports.procurement.index', ['tab' => 'purchases']) }}"
                   class="pb-3 text-sm font-semibold transition border-b-2 {{ $tab === 'purchases' ? 'border-brand-600 text-brand-600 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                    2. Purchases by Dimension
                </a>
                <a href="{{ route('reports.procurement.index', ['tab' => 'payables']) }}"
                   class="pb-3 text-sm font-semibold transition border-b-2 {{ $tab === 'payables' ? 'border-brand-600 text-brand-600 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                    3. Vendor Outstanding & Ageing
                </a>
                <a href="{{ route('reports.procurement.index', ['tab' => 'projects']) }}"
                   class="pb-3 text-sm font-semibold transition border-b-2 {{ $tab === 'projects' ? 'border-brand-600 text-brand-600 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                    4. Project Material Cost
                </a>
            </nav>
        </div>

        {{-- TAB 1: Stock on Hand & Low Stock --}}
        @if($tab === 'stock')
            {{-- Metrics --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Warehouse Valuation</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">₹ {{ number_format($stockData['totalValuation'], 2) }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">Weighted average cost basis</div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Physical Units</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($stockData['totalUnits']) }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">Across all tracked product items</div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Low Stock SKU Alerts</div>
                    <div class="text-2xl font-bold {{ $stockData['lowStockCount'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }} mt-1">
                        {{ $stockData['lowStockCount'] }}
                    </div>
                    <div class="text-xs text-slate-400 mt-0.5">At or below reorder threshold</div>
                </div>
            </div>

            {{-- Filters & CSV Export --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <form method="GET" action="{{ route('reports.procurement.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
                    <input type="hidden" name="tab" value="stock">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search SKU, item name..." class="crm-input w-48 text-xs">
                    <select name="category" class="crm-input text-xs">
                        <option value="">All Categories</option>
                        @foreach($stockData['categories'] as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    <label class="flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="low_stock_only" value="1" {{ request('low_stock_only') ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600">
                        <span>Low Stock Only</span>
                    </label>
                    <button type="submit" class="btn-primary text-xs">Filter</button>
                    @if(request()->anyFilled(['search', 'category', 'low_stock_only']))
                        <a href="{{ route('reports.procurement.index', ['tab' => 'stock']) }}" class="btn-secondary text-xs">Reset</a>
                    @endif
                </form>

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('reports.procurement.stock-on-hand.csv', request()->all()) }}" class="btn-secondary text-xs flex items-center gap-1.5 self-start md:self-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Export CSV (Admin)</span>
                    </a>
                @endif
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3">Product</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3 text-center">Stock Qty</th>
                                <th class="px-4 py-3 text-center">Min Level</th>
                                <th class="px-4 py-3 text-right">Avg Unit Cost</th>
                                <th class="px-4 py-3 text-right">Total Valuation</th>
                                <th class="px-4 py-3 text-center">Stock Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($stockData['products'] as $prod)
                                @php
                                    $isLow = $prod->stock_quantity <= ($prod->min_stock_level ?: 5);
                                    $val = (float) $prod->stock_quantity * (float) $prod->cost_price;
                                @endphp
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-900 dark:text-white">{{ $prod->name }}</div>
                                        <div class="text-xs text-slate-400">SKU: {{ $prod->sku ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-500">{{ $prod->category ?? 'General' }}</td>
                                    <td class="px-4 py-3 text-center font-mono font-bold text-slate-900 dark:text-white">{{ $prod->stock_quantity }}</td>
                                    <td class="px-4 py-3 text-center font-mono text-xs text-slate-400">{{ $prod->min_stock_level ?: 5 }}</td>
                                    <td class="px-4 py-3 text-right font-mono">₹ {{ number_format($prod->cost_price, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900 dark:text-white">₹ {{ number_format($val, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($prod->stock_quantity <= 0)
                                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400">Out of Stock</span>
                                        @elseif($isLow)
                                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Low Stock</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">In Stock</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-sm">No products found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($stockData['products']->hasPages())
                    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
                        {{ $stockData['products']->links() }}
                    </div>
                @endif
            </div>
        @endif

        {{-- TAB 2: Purchases by Dimension --}}
        @if($tab === 'purchases')
            {{-- Spend Metric --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Purchase Volume</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">₹ {{ number_format($purchasesData['totalSpend'], 2) }}</div>
                </div>
                <div class="text-xs text-slate-400 text-right">
                    Grouped by: <span class="font-bold text-slate-700 dark:text-slate-300 uppercase">{{ $purchasesData['dimension'] }}</span>
                </div>
            </div>

            {{-- Filters & Dimension Selector --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <form method="GET" action="{{ route('reports.procurement.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
                    <input type="hidden" name="tab" value="purchases">
                    <div class="flex items-center rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden text-xs">
                        <a href="{{ route('reports.procurement.index', array_merge(request()->all(), ['tab' => 'purchases', 'dimension' => 'supplier'])) }}" class="px-3 py-1.5 {{ $purchasesData['dimension'] === 'supplier' ? 'bg-brand-600 text-white font-semibold' : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">Supplier</a>
                        <a href="{{ route('reports.procurement.index', array_merge(request()->all(), ['tab' => 'purchases', 'dimension' => 'product'])) }}" class="px-3 py-1.5 {{ $purchasesData['dimension'] === 'product' ? 'bg-brand-600 text-white font-semibold' : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">Product</a>
                        <a href="{{ route('reports.procurement.index', array_merge(request()->all(), ['tab' => 'purchases', 'dimension' => 'project'])) }}" class="px-3 py-1.5 {{ $purchasesData['dimension'] === 'project' ? 'bg-brand-600 text-white font-semibold' : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">Project</a>
                    </div>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="crm-input text-xs" title="Date From">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="crm-input text-xs" title="Date To">
                    <input type="hidden" name="dimension" value="{{ $purchasesData['dimension'] }}">
                    <button type="submit" class="btn-primary text-xs">Apply Date</button>
                </form>

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('reports.procurement.purchases.csv', request()->all()) }}" class="btn-secondary text-xs flex items-center gap-1.5 self-start md:self-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Export CSV (Admin)</span>
                    </a>
                @endif
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3">{{ ucfirst($purchasesData['dimension']) }}</th>
                                <th class="px-4 py-3">Details / Reference</th>
                                @if($purchasesData['dimension'] === 'product')
                                    <th class="px-4 py-3 text-center">Ordered Qty</th>
                                    <th class="px-4 py-3 text-center">Received Qty</th>
                                @else
                                    <th class="px-4 py-3 text-center">PO Count</th>
                                @endif
                                <th class="px-4 py-3 text-right">Total Procurement Spend</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($purchasesData['rows'] as $r)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $r['name'] }}</td>
                                    <td class="px-4 py-3 text-xs text-slate-500">{{ $r['secondary'] }}</td>
                                    @if($purchasesData['dimension'] === 'product')
                                        <td class="px-4 py-3 text-center font-mono">{{ $r['ordered_qty'] }}</td>
                                        <td class="px-4 py-3 text-center font-mono">{{ $r['received_qty'] }}</td>
                                    @else
                                        <td class="px-4 py-3 text-center font-mono font-medium">{{ $r['po_count'] }}</td>
                                    @endif
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                        ₹ {{ number_format($r['total_spend'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-slate-400 text-sm">No procurement spend found for selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- TAB 3: Vendor Outstanding & Ageing --}}
        @if($tab === 'payables')
            {{-- AP Metrics --}}
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Net Outstanding Payables</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">₹ {{ number_format($payablesData['summary']['total_payable_balance'], 2) }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Current (0–30 Days)</div>
                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">₹ {{ number_format($payablesData['summary']['aging_0_30'], 2) }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">31–60 Days Overdue</div>
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">₹ {{ number_format($payablesData['summary']['aging_31_60'], 2) }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">61+ Days Critical</div>
                    <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">₹ {{ number_format($payablesData['summary']['aging_61_90'] + $payablesData['summary']['aging_90_plus'], 2) }}</div>
                </div>
            </div>

            <div class="flex justify-end">
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('reports.procurement.vendor-outstanding.csv') }}" class="btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Export AP Ageing CSV (Admin)</span>
                    </a>
                @endif
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3">Supplier Name</th>
                                <th class="px-4 py-3 text-right">Invoiced (₹)</th>
                                <th class="px-4 py-3 text-right">Paid (₹)</th>
                                <th class="px-4 py-3 text-right">Debit Notes (₹)</th>
                                <th class="px-4 py-3 text-right">Net Payable (₹)</th>
                                <th class="px-4 py-3 text-center">Ageing Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($payablesData['suppliers'] as $s)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                        <a href="{{ route('suppliers.show', $s['supplier_id']) }}" class="hover:underline text-brand-600 dark:text-brand-400">
                                            {{ $s['supplier_name'] }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono">₹ {{ number_format($s['total_ordered_volume'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono">₹ {{ number_format($s['total_paid_volume'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-amber-600 dark:text-amber-400">
                                        ₹ {{ number_format($s['debit_notes_total'] ?? 0.0, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                        ₹ {{ number_format($s['balance_payable'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded {{ $s['aging_badge_class'] ?? 'bg-slate-100 text-slate-800' }}">
                                            {{ $s['aging_status'] ?? 'Current' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-sm">No outstanding vendor balances.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- TAB 4: Project Material Cost --}}
        @if($tab === 'projects')
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <form method="GET" action="{{ route('reports.procurement.index') }}" class="flex items-center gap-2 flex-1">
                    <input type="hidden" name="tab" value="projects">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search project, client..." class="crm-input w-64 text-xs">
                    <button type="submit" class="btn-primary text-xs">Search</button>
                    @if(request('search'))
                        <a href="{{ route('reports.procurement.index', ['tab' => 'projects']) }}" class="btn-secondary text-xs">Reset</a>
                    @endif
                </form>

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('reports.procurement.project-material-cost.csv', request()->all()) }}" class="btn-secondary text-xs flex items-center gap-1.5 self-start md:self-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Export CSV (Admin)</span>
                    </a>
                @endif
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3">Project</th>
                                <th class="px-4 py-3">Client</th>
                                <th class="px-4 py-3 text-right">Est. Budget (₹)</th>
                                <th class="px-4 py-3 text-right">Issued Stock Cost (₹)</th>
                                <th class="px-4 py-3 text-right">PO Spend (₹)</th>
                                <th class="px-4 py-3 text-right">Variance (₹)</th>
                                <th class="px-4 py-3 text-center">Budget Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($projectsData['rows'] as $row)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                        <a href="{{ route('projects.show', $row['project']) }}" class="hover:underline text-brand-600 dark:text-brand-400">
                                            {{ $row['name'] }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-500">{{ $row['client_name'] }}</td>
                                    <td class="px-4 py-3 text-right font-mono">₹ {{ number_format($row['budget'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono">₹ {{ number_format($row['issued_cost'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono">₹ {{ number_format($row['po_spend'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-semibold {{ $row['variance'] < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        ₹ {{ number_format($row['variance'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($row['is_over_budget'])
                                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400">Over Budget</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">Normal</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-sm">No projects found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($projectsData['projects']->hasPages())
                    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
                        {{ $projectsData['projects']->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-app-layout>
