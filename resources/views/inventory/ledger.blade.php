<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Stock Movement Ledger</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Continuous double-entry inventory movement ledger, receipts, issues, adjustments and audit logs.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('inventory.index') }}" class="btn-secondary">
                    Warehouse Stock
                </a>
                @if(auth()->user()->isOffice())
                    <a href="{{ route('goods-receipts.index') }}" class="btn-secondary">
                        Goods Receipts (GRN)
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Filters & Search --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
            <form method="GET" action="{{ route('inventory.ledger') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Product</label>
                    <select name="product_id" class="crm-input w-full text-xs">
                        <option value="">All Products</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                                {{ $prod->name }} ({{ $prod->sku ?: 'No SKU' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Project Site</label>
                    <select name="project_id" class="crm-input w-full text-xs">
                        <option value="">All Projects</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                {{ $proj->project_code }} - {{ $proj->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Movement Type</label>
                    <select name="movement_type" class="crm-input w-full text-xs">
                        <option value="">All Types</option>
                        <option value="receipt" {{ request('movement_type') == 'receipt' ? 'selected' : '' }}>Receipt (GRN Inward)</option>
                        <option value="issue" {{ request('movement_type') == 'issue' ? 'selected' : '' }}>Issue (Site / Task)</option>
                        <option value="return_to_vendor" {{ request('movement_type') == 'return_to_vendor' ? 'selected' : '' }}>Return to Vendor</option>
                        <option value="adjustment" {{ request('movement_type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        <option value="opening" {{ request('movement_type') == 'opening' ? 'selected' : '' }}>Opening Balance</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="crm-input w-full text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="crm-input w-full text-xs">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary w-full justify-center text-xs">Filter</button>
                    @if(request()->anyFilled(['product_id', 'project_id', 'movement_type', 'date_from', 'date_to']))
                        <a href="{{ route('inventory.ledger') }}" class="btn-secondary justify-center text-xs">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Ledger Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="crm-table w-full">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-xs text-slate-500 font-semibold">
                            <th class="py-3 px-4 text-left">Date & Time</th>
                            <th class="py-3 px-4 text-left">Product</th>
                            <th class="py-3 px-4 text-left">Type</th>
                            <th class="py-3 px-4 text-center">Movement Qty</th>
                            <th class="py-3 px-4 text-center">Balance After</th>
                            @if($canViewCosts)
                                <th class="py-3 px-4 text-right">Unit Cost</th>
                                <th class="py-3 px-4 text-right">Valuation Change</th>
                            @endif
                            <th class="py-3 px-4 text-left">Project / Ref</th>
                            <th class="py-3 px-4 text-left">Notes</th>
                            <th class="py-3 px-4 text-left">Logged By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                        @forelse($movements as $m)
                            @php
                                $isPositive = ($m->qty > 0) || ($m->movement_type === 'receipt') || ($m->movement_type === 'opening') || ($m->type === 'in');
                                $displayQty = $m->qty !== 0 ? $m->qty : ($isPositive ? $m->quantity : -$m->quantity);
                                $unitCost = (float) ($m->unit_cost ?? $m->product?->cost_price ?? 0);
                                $valChange = $displayQty * $unitCost;
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition text-xs">
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ $m->created_at?->format('d M Y, h:i A') }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">
                                        {{ $m->product?->name ?? 'Unknown Product' }}
                                    </div>
                                    @if($m->product?->sku)
                                        <div class="font-mono text-[10px] text-slate-400">SKU: {{ $m->product->sku }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if(in_array($m->movement_type, ['receipt', 'opening']) || $m->type === 'in')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                                            {{ ucfirst(str_replace('_', ' ', $m->movement_type ?: 'Receipt')) }}
                                        </span>
                                    @elseif(in_array($m->movement_type, ['issue', 'job_installation']) || $m->type === 'out')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200">
                                            {{ ucfirst(str_replace('_', ' ', $m->movement_type ?: 'Issue')) }}
                                        </span>
                                    @elseif($m->movement_type === 'adjustment' || $m->type === 'adjustment')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">
                                            Adjustment
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">
                                            {{ ucfirst(str_replace('_', ' ', $m->movement_type ?: $m->type)) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-bold whitespace-nowrap">
                                    @if($displayQty > 0)
                                        <span class="text-emerald-600 dark:text-emerald-400">+{{ $displayQty }}</span>
                                    @elseif($displayQty < 0)
                                        <span class="text-rose-600 dark:text-rose-400">{{ $displayQty }}</span>
                                    @else
                                        <span class="text-slate-400">0</span>
                                    @endif
                                    <span class="text-[10px] text-slate-400 font-normal ml-0.5">{{ $m->product?->unit ?? 'nos' }}</span>
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-bold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                    {{ $m->balance_after }} <span class="text-[10px] text-slate-400 font-normal">{{ $m->product?->unit ?? 'nos' }}</span>
                                </td>
                                @if($canViewCosts)
                                    <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                        ₹{{ number_format($unitCost, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold whitespace-nowrap {{ $valChange >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $valChange >= 0 ? '+' : '' }}₹{{ number_format($valChange, 2) }}
                                    </td>
                                @endif
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400 text-xs">
                                    @if($m->project)
                                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $m->project->project_code }}</span>
                                    @elseif($m->reference_type && $m->reference_id)
                                        <span class="font-mono text-[10px]">{{ class_basename($m->reference_type) }} #{{ $m->reference_id }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-500 max-w-xs truncate" title="{{ $m->note ?: $m->notes }}">
                                    {{ $m->note ?: ($m->notes ?: '-') }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                                    {{ $m->creator?->name ?: ($m->user?->name ?? 'System') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canViewCosts ? 10 : 8 }}" class="py-12 text-center text-slate-400">
                                    No stock ledger records found matching your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($movements->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $movements->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
