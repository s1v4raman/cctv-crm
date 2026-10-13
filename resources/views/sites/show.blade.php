<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <a href="{{ route('sites.index') }}" class="back-link">
                    &larr; Back
                </a>
                <div class="flex items-center gap-3 mt-1">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        {{ $site->name }}
                    </h2>
                    <span class="text-sm font-bold text-blue-600">
                        {{ $site->site_code }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('sites.edit', $site) }}" class="btn-secondary text-xs">
                    Edit Details
                </a>
                <a href="{{ route('projects.create', ['site_id' => $site->id]) }}" class="btn-primary text-xs">
                    New Project
                </a>
                @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('sites.destroy', $site) }}" onsubmit="return confirm('Are you sure you want to delete this installation site?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="danger-link p-0 bg-transparent border-0 cursor-pointer text-xs ml-2">Delete</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="pg-wrap py-6">
        <div class="pg-inner space-y-6">

            @if (session('status'))
                <div class="p-3 rounded border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs">
                    {{ session('status') }}
                </div>
            @endif

            {{-- 1. Site Information: Two-column table inside standard section --}}
            <div class="crm-section">
                <h3>Site Information</h3>
                
                <table class="crm-table w-full">
                    <tbody>
                        <tr>
                            <td style="width: 25%; color: #555; font-weight: 500;">Client / Organization</td>
                            <td style="color: #222; font-weight: 500;">{{ $site->client_name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Contact Phone</td>
                            <td style="color: #222; font-weight: 500;">
                                @if($site->client_phone)
                                    <a href="tel:{{ $site->client_phone }}" class="action-link">
                                        {{ $site->client_phone }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Contact Person</td>
                            <td style="color: #222; font-weight: 500;">{{ $site->contact_person ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Email Address</td>
                            <td style="color: #222; font-weight: 500;">
                                @if($site->client_email)
                                    <a href="mailto:{{ $site->client_email }}" class="action-link">
                                        {{ $site->client_email }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Physical Address</td>
                            <td style="color: #222; font-weight: 500;">{{ $site->address }}, {{ $site->city }} {{ $site->state }} {{ $site->pincode }}</td>
                        </tr>
                        @if($site->notes)
                        <tr>
                            <td style="color: #555; font-weight: 500;">Access Notes</td>
                            <td style="color: #222; font-weight: 500;">{{ $site->notes }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- 2. Map & Coordinates --}}
            <div class="crm-section">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-[#ddd]">
                    <h3 class="border-b-0 pb-0 mb-0">GPS Geolocation</h3>
                    @if($site->navigate_url)
                        <a href="{{ $site->navigate_url }}" target="_blank" rel="noopener noreferrer" class="btn-secondary text-xs">
                            Open in Maps
                        </a>
                    @endif
                </div>

                @if($site->has_coordinates)
                    <div class="text-xs text-slate-500 mb-3">
                        Latitude: <strong class="text-slate-800">{{ $site->latitude }}</strong> &bull;
                        Longitude: <strong class="text-slate-800">{{ $site->longitude }}</strong>
                    </div>

                    <div class="h-64 rounded overflow-hidden border border-[#ddd] relative z-0">
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
                    <p class="text-xs text-slate-500">
                        No GPS coordinates recorded for this site.
                    </p>
                @endif
            </div>

            {{-- 3. Projects on this Site --}}
            <div class="crm-section">
                <h3>Site Projects ({{ $site->projects->count() }})</h3>
                
                <div class="overflow-x-auto">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th>Project Code</th>
                                <th>Title</th>
                                <th>Budget</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($site->projects as $prj)
                                <tr>
                                    <td class="font-mono font-semibold" style="white-space: nowrap;">
                                        <a href="{{ route('projects.show', $prj) }}" class="action-link">
                                            {{ $prj->project_code }}
                                        </a>
                                    </td>
                                    <td class="font-semibold text-slate-900">{{ $prj->title }}</td>
                                    <td style="white-space: nowrap;">₹{{ number_format($prj->budget, 2) }}</td>
                                    <td>
                                        <span class="status-{{ strtolower($prj->status) }}">
                                            {{ ucfirst(str_replace('_', ' ', $prj->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-right" style="white-space: nowrap;">
                                        <a href="{{ route('projects.show', $prj) }}" class="action-link">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-slate-500 text-sm">
                                        No projects linked to this site yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
