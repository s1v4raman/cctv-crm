<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Client Installation Sites</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('sites.create') }}" class="btn-primary">
                    New Site
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-4">

        @if (session('status'))
            <div class="p-3 rounded border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs">
                {{ session('status') }}
            </div>
        @endif



        {{-- Filters Bar: One Simple Row, 38px Height, 4px Radius --}}
        <form method="GET" action="{{ route('sites.index') }}" class="flex items-center gap-2 flex-wrap">
            <div class="relative flex-1 min-w-[220px]">
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Search site code, client, city, phone…" 
                       style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                       class="crm-input w-full text-sm border border-[#ddd] dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 focus:outline-none focus:border-slate-400">
            </div>

            <select name="city" onchange="this.form.submit()" 
                    style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                    class="crm-select text-sm border border-[#ddd] dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 focus:outline-none focus:border-slate-400">
                <option value="all">All Cities ({{ $cities->count() }})</option>
                @foreach($cities as $c)
                    <option value="{{ $c }}" {{ $cityFilter === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>

            <button type="submit" 
                    style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                    class="crm-btn-primary px-4 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white transition">
                Search
            </button>

            @if($search || $cityFilter !== 'all')
                <a href="{{ route('sites.index') }}" 
                   style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                   class="crm-btn-secondary px-3 border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition inline-flex items-center">
                    Reset
                </a>
            @endif
        </form>

        {{-- Sites Table with 1px Solid Border & 4px Radius --}}
        <div class="overflow-x-auto w-full border border-[#ddd] dark:border-slate-800 rounded-[4px] bg-white dark:bg-[#0f172a]">
            <table class="leads-table w-full text-left text-sm text-slate-700 dark:text-slate-200">
                <thead>
                    <tr>
                        <th style="min-width: 220px;">Site Code &amp; Name</th>
                        <th style="min-width: 180px;">Client &amp; Contact</th>
                        <th style="min-width: 220px;">Physical Address</th>
                        <th style="min-width: 130px;">Location</th>
                        <th class="text-center" style="min-width: 90px;">Projects</th>
                        <th class="text-right" style="min-width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sites as $site)
                        <tr>
                            <td>
                                <div>
                                    <a href="{{ route('sites.show', $site) }}" class="font-bold text-slate-900 dark:text-white hover:underline text-xs">
                                        {{ $site->name }}
                                    </a>
                                </div>
                                <div class="text-[11px] font-mono text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $site->site_code }}
                                </div>
                            </td>
                            <td>
                                <div class="font-medium text-xs text-slate-900 dark:text-white">{{ $site->client_name ?? '—' }}</div>
                                @if($site->client_phone)
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 whitespace-nowrap mt-0.5">
                                        <a href="tel:{{ $site->client_phone }}" class="hover:underline">{{ $site->client_phone }}</a>
                                    </div>
                                @endif
                            </td>
                            <td class="max-w-xs">
                                <div class="text-xs text-slate-900 dark:text-white" title="{{ $site->address }}">{{ $site->address }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $site->city }}{{ $site->pincode ? ' - ' . $site->pincode : '' }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap text-xs">
                                @if($site->has_coordinates || $site->google_maps_url)
                                    <a href="{{ $site->navigate_url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline font-medium">
                                        Open Map
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="text-center text-xs font-bold text-slate-900 dark:text-white">
                                {{ $site->projects_count }}
                            </td>
                            <td class="text-right whitespace-nowrap text-sm table-actions">
                                <a href="{{ route('sites.show', $site) }}" class="action-link">View</a>
                                <a href="{{ route('sites.edit', $site) }}" class="action-link">Edit</a>
                                @if(auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('sites.destroy', $site) }}" class="inline" onsubmit="return confirm('Delete site {{ addslashes($site->site_name) }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="danger-link">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-6 text-center text-slate-500 text-sm">
                                No installation sites yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{-- Total count shown below the table --}}
            <div class="px-4 py-2.5 border-t border-[#ddd] dark:border-slate-800 bg-[#fbfbfb] dark:bg-slate-900/60 flex items-center justify-between text-xs text-slate-500">
                <span class="font-medium">Total: {{ $sites->total() }}</span>
                @if($sites->hasPages())
                    <div class="text-xs">
                        {{ $sites->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
