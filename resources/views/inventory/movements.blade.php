<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Inventory Movement & Audit Logs</h2>
    </x-slot>

    <div class="py-5">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('inventory.index') }}" class="back-link">&larr; Back</a>

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Inventory Movement & Audit Logs</h2>
            </div>

            {{-- Filter Bar --}}
            <div class="crm-section mb-4">
                <form method="GET" action="{{ route('inventory.movements') }}" class="flex flex-wrap items-center gap-3">
                    <div>
                        <select name="product_id" class="crm-select min-w-[220px]">
                            <option value="">All Products</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>
                                    {{ $p->name }} {{ $p->sku ? "({$p->sku})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select name="type" class="crm-select min-w-[200px]">
                            <option value="">All Movement Types</option>
                            <option value="in" @selected(request('type') == 'in')>Stock IN (Purchase/Add)</option>
                            <option value="out" @selected(request('type') == 'out')>Stock OUT (Manual Dispatch)</option>
                            <option value="job_installation" @selected(request('type') == 'job_installation')>Installation Deployment</option>
                            <option value="adjustment" @selected(request('type') == 'adjustment')>Adjustment/Correction</option>
                            <option value="return" @selected(request('type') == 'return')>Customer/Vendor Return</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary">Filter</button>
                    @if(request()->hasAny(['product_id', 'type']))
                        <a href="{{ route('inventory.movements') }}" class="btn-secondary">Clear</a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="crm-section p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Product Item</th>
                                <th>Movement Type</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Balance After</th>
                                <th>Notes / Reason</th>
                                <th>Logged By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $mov)
                            <tr>
                                <td class="whitespace-nowrap text-gray-600">
                                    {{ $mov->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td>
                                    <div class="font-bold text-gray-900">{{ $mov->product?->name ?? 'Deleted Product' }}</div>
                                    @if($mov->product?->sku)
                                        <div class="text-xs text-gray-500">SKU: {{ $mov->product->sku }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($mov->type === 'in')
                                        <span class="status-won">Stock In</span>
                                    @elseif($mov->type === 'out')
                                        <span class="status-cancelled">Stock Out</span>
                                    @elseif($mov->type === 'job_installation')
                                        <span class="status-progress">Job Installation</span>
                                    @elseif($mov->type === 'return')
                                        <span class="status-quoted">Return</span>
                                    @else
                                        <span class="status-quoted">Adjustment</span>
                                    @endif
                                </td>
                                <td class="text-center font-bold {{ $mov->quantity >= 0 ? 'text-[#16a34a]' : 'text-[#dc2626]' }}">
                                    {{ $mov->quantity > 0 ? '+' : '' }}{{ $mov->quantity }}
                                </td>
                                <td class="text-center font-bold text-gray-900">
                                    {{ $mov->balance_after }} {{ $mov->product?->unit ?? '' }}
                                </td>
                                <td class="text-gray-700">
                                    {{ $mov->notes ?: '—' }}
                                </td>
                                <td class="whitespace-nowrap text-gray-600">
                                    {{ $mov->user?->name ?? 'System' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-gray-500">
                                    No stock movements recorded yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($movements->hasPages())
                <div class="p-4 border-t border-[#ddd]">
                    {{ $movements->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
