<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('sites.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">{{ $site->name }}</h2>
                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                        {{ $site->site_code }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $site->address }}, {{ $site->city }}</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('sites.edit', $site) }}" class="btn-secondary text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Edit Site
                </a>
                <a href="{{ route('projects.create', ['site_id' => $site->id]) }}" class="btn-primary text-xs shadow-md">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    New Project on this Site
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left 2 Cols: Site Info & Map --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Details Card --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Site Information</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-xs text-slate-400 block">Client / Organization</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $site->client_name ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block">Contact Phone</span>
                            @if($site->client_phone)
                                <a href="tel:{{ $site->client_phone }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $site->client_phone }}
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block">Contact Person</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $site->contact_person ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block">Email Address</span>
                            @if($site->client_email)
                                <a href="mailto:{{ $site->client_email }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $site->client_email }}
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-xs text-slate-400 block">Complete Physical Address</span>
                            <span class="text-slate-800 dark:text-slate-200">{{ $site->address }}, {{ $site->city }} {{ $site->state }} {{ $site->pincode }}</span>
                        </div>
                        @if($site->notes)
                            <div class="sm:col-span-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                                <span class="text-xs text-slate-400 block">Special Access Notes</span>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 whitespace-pre-line">{{ $site->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Map & Navigation Card --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            GPS Geolocation &amp; Directions
                        </h3>

                        <a href="{{ $site->navigate_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            Open in Google Maps
                        </a>
                    </div>

                    @if($site->has_coordinates)
                        <div class="text-xs font-mono text-slate-500 flex items-center gap-3">
                            <span>Lat: <strong class="text-slate-800 dark:text-slate-200">{{ $site->latitude }}</strong></span>
                            <span>Lng: <strong class="text-slate-800 dark:text-slate-200">{{ $site->longitude }}</strong></span>
                        </div>

                        {{-- Leaflet OpenStreetMap Interactive Embed --}}
                        <div class="h-64 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 relative z-0" id="site-map">
                            <iframe 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                scrolling="no" 
                                marginheight="0" 
                                marginwidth="0" 
                                src="https://www.openstreetmap.org/export/embed.html?bbox={{ $site->longitude - 0.005 }}%2C{{ $site->latitude - 0.005 }}%2C{{ $site->longitude + 0.005 }}%2C{{ $site->latitude + 0.005 }}&amp;layer=mapnik&amp;marker={{ $site->latitude }}%2C{{ $site->longitude }}">
                            </iframe>
                        </div>
                    @else
                        <div class="p-8 text-center rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-dashed border-slate-200 dark:border-slate-700 text-slate-400 text-xs">
                            <p class="text-2xl mb-1">📍</p>
                            <p class="font-bold">No GPS coordinates recorded for this site</p>
                            <a href="{{ route('sites.edit', $site) }}" class="text-blue-500 hover:underline mt-2 inline-block">
                                Edit site to add GPS coordinates or Google Maps link
                            </a>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Right 1 Col: Linked Projects on this Site --}}
            <div class="space-y-6">
                <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Site Projects ({{ $site->projects->count() }})
                        </h3>
                    </div>

                    <div class="space-y-3">
                        @forelse($site->projects as $prj)
                            <a href="{{ route('projects.show', $prj) }}" class="block p-3.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:border-blue-400 transition group">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-mono font-bold text-slate-400">{{ $prj->project_code }}</span>
                                    {!! $prj->status_badge !!}
                                </div>
                                <h4 class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-blue-600 transition truncate">
                                    {{ $prj->title }}
                                </h4>
                                <div class="flex items-center justify-between text-xs text-slate-400 mt-2">
                                    <span>{{ $prj->company?->name ?? 'Unassigned Co.' }}</span>
                                    <span>₹{{ number_format($prj->budget, 0) }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-6 text-slate-400 text-xs">
                                <p>No projects linked to this site yet.</p>
                                <a href="{{ route('projects.create', ['site_id' => $site->id]) }}" class="text-blue-500 hover:underline mt-2 inline-block font-semibold">
                                    + Start first project
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
