<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Client Installation Sites</h2>
                    <span class="text-xs font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2.5 py-1 rounded border border-emerald-200 dark:border-emerald-500/30">Locations Registry</span>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage customer premises, physical addresses, GPS coordinates and active site installations</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('sites.create') }}" class="btn-primary shadow-md hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>New Site</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 dark:hover:text-white">&times;</button>
            </div>
        @endif

        {{-- Top KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Registered Sites</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white font-heading">{{ $totalSitesCount }}</div>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Linked Projects</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white font-heading">{{ $totalProjectsOnSites }}</div>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Active Cities</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white font-heading">{{ $cities->count() }}</div>
                </div>
            </div>
        </div>

        {{-- Filters & Search Bar --}}
        <div class="p-4 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <form method="GET" action="{{ route('sites.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="relative w-full sm:w-96">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search site code, client, city, phone..." class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800 dark:text-slate-100 placeholder-slate-400 outline-none">
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <select name="city" onchange="this.form.submit()" class="px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="all">All Cities ({{ $cities->count() }})</option>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" {{ $cityFilter === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn-secondary text-xs py-2 px-4">Filter</button>
                    @if($search || $cityFilter !== 'all')
                        <a href="{{ route('sites.index') }}" class="text-xs text-rose-500 hover:underline">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Sites Table --}}
        <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 dark:text-slate-400 tracking-wider border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">Site Code &amp; Name</th>
                            <th class="px-5 py-3.5">Client &amp; Contact</th>
                            <th class="px-5 py-3.5">Physical Address</th>
                            <th class="px-5 py-3.5">GPS / Location</th>
                            <th class="px-5 py-3.5 text-center">Projects</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-sans">
                        @forelse($sites as $site)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">
                                            🏢
                                        </div>
                                        <div>
                                            <a href="{{ route('sites.show', $site) }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 transition">
                                                {{ $site->name }}
                                            </a>
                                            <div class="text-xs font-mono font-bold text-slate-400 mt-0.5">{{ $site->site_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">{{ $site->client_name ?? '—' }}</div>
                                    @if($site->client_phone)
                                        <div class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <a href="tel:{{ $site->client_phone }}" class="hover:text-blue-600">{{ $site->client_phone }}</a>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 max-w-xs">
                                    <div class="text-xs text-slate-600 dark:text-slate-300 truncate" title="{{ $site->address }}">{{ $site->address }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $site->city }}{{ $site->pincode ? ' - ' . $site->pincode : '' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($site->has_coordinates || $site->google_maps_url)
                                        <a href="{{ $site->navigate_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 transition border border-emerald-200 dark:border-emerald-800">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>Open Map</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400 italic">No GPS set</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-bold {{ $site->projects_count > 0 ? 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                                        {{ $site->projects_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('sites.show', $site) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="View Site Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('sites.edit', $site) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Site">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400 dark:text-slate-500">
                                    <div class="text-4xl mb-2">📍</div>
                                    <p class="font-bold text-sm">No installation sites found</p>
                                    <p class="text-xs mt-1">Get started by creating your first client installation site.</p>
                                    <a href="{{ route('sites.create') }}" class="btn-primary inline-flex items-center gap-1.5 mt-4 text-xs">
                                        + Add New Site
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sites->hasPages())
                <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $sites->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
