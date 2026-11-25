<x-app-layout>
    <div class="max-w-5xl mx-auto p-4 sm:p-6 space-y-5">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Scanned Product Aliases') }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Approve or merge scan-learned EANs and model codes into catalog products.') }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="ld-outline plain-link text-xs font-semibold">{{ __('Back to Products') }}</a>
        </div>

        <div class="crm-card bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/70 text-slate-600 dark:text-slate-300 uppercase">
                        <tr>
                            <th class="px-4 py-3">{{ __('Type') }}</th>
                            <th class="px-4 py-3">{{ __('Scanned Value') }}</th>
                            <th class="px-4 py-3">{{ __('Linked Product') }}</th>
                            <th class="px-4 py-3">{{ __('Source / Status') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($aliases as $alias)
                            <tr>
                                <td class="px-4 py-3 font-mono font-bold uppercase">{{ $alias->alias_type }}</td>
                                <td class="px-4 py-3 font-mono text-slate-900 dark:text-white">{{ $alias->value }}</td>
                                <td class="px-4 py-3">{{ $alias->product?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $alias->status === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' }}">
                                        {{ ucfirst($alias->status) }} ({{ $alias->source }})
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2 flex-wrap justify-end">
                                        @if($alias->status === 'pending')
                                            <form method="POST" action="{{ route('admin.product-aliases.approve', $alias) }}">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold cursor-pointer">
                                                    {{ __('Approve') }}
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.product-aliases.merge', $alias) }}" class="inline-flex items-center gap-1">
                                            @csrf
                                            <select name="target_product_id" class="text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2 py-1">
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}" @selected($p->id === $alias->product_id)>{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold cursor-pointer">
                                                {{ __('Merge') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.product-aliases.destroy', $alias) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-800 dark:bg-rose-900/50 dark:text-rose-200 font-bold cursor-pointer">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">{{ __('No product aliases recorded yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $aliases->links() }}
    </div>
</x-app-layout>
