<x-app-layout>
    @php
        $statuses = \App\Models\Project::statusOptions();
        $priorities = \App\Models\Project::priorityOptions();
        $views = collect(['all' => __('All Projects')] + $statuses)->map(fn ($label, $value) => [
            'label' => $value === 'all' ? $label : __($label),
            'url' => route('projects.index', array_filter(['status' => $value === 'all' ? null : $value])),
            'active' => $statusFilter === $value,
        ])->values()->all();
        $filterCount = collect([$search !== '' , $statusFilter !== 'all', $priorityFilter !== 'all', $typeFilter !== 'all', $companyFilter !== 'all', (bool) $assignedFilter])->filter()->count();
        $canCreate = auth()->user()->can('create', \App\Models\Project::class);
        $typeShort = ['hardware_cctv' => 'CCTV Hardware', 'hardware_attendance' => 'Terminal Attendance', 'networking' => 'Networking', 'access_control' => 'Access Control', 'software_web' => 'Webpage & Apps', 'hybrid' => 'Hybrid Turnkey'];
    @endphp

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- MOBILE & TABLET CENTERED CARD VIEW (SMARTPHONES & FIELD TABLETS)   --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div class="block lg:hidden max-w-xl mx-auto space-y-4 px-3 sm:px-4 pt-2 pb-28">
        {{-- Header & Create Button --}}
        <div class="flex items-center justify-between gap-2 px-1">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ __('Projects') }}
                </h1>
                <span class="text-xs text-slate-400">
                    {{ __(':count total records', ['count' => $projects->total()]) }}
                </span>
            </div>

            @if($canCreate)
                <a href="{{ route('projects.create') }}" 
                   class="btn-md inline-flex items-center gap-1 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition">
                    <span class="text-base leading-none">+</span>
                    <span>{{ __('New Project') }}</span>
                </a>
            @endif
        </div>

        {{-- Mobile Search & Quick Status Filters --}}
        <div class="space-y-2.5">
            <form method="GET" action="{{ route('projects.index') }}">
                @if($statusFilter && $statusFilter !== 'all')
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                <div class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-700 shadow-2xs focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20 transition">
                    <svg class="w-4 h-4 text-slate-400 shrink-0 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('Search projects, codes, customers...') }}"
                           class="w-full !border-0 !p-0 !bg-transparent font-sans text-xs sm:text-sm font-medium text-slate-900 dark:text-white placeholder:text-slate-400 placeholder:font-normal focus:outline-none focus:ring-0 !shadow-none !h-auto !min-h-0">
                    @if($search)
                        <a href="{{ route('projects.index') }}" class="text-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 shrink-0 p-1 font-bold leading-none">
                            &times;
                        </a>
                    @endif
                </div>
            </form>

            {{-- Quick Filter Pills --}}
            <div class="flex items-center gap-1.5 overflow-x-auto py-1 no-scrollbar text-xs">
                @foreach($views as $v)
                    <a href="{{ $v['url'] }}" 
                       class="px-3 py-1.5 rounded-xl font-bold whitespace-nowrap transition {{ $v['active'] ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                        {{ $v['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Mobile Centered Project Cards --}}
        <div class="space-y-3">
            @forelse($projects as $project)
                <div class="bg-white dark:bg-slate-900 rounded-[22px] p-4 border border-slate-100 dark:border-slate-800 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] space-y-3 relative overflow-hidden">
                    
                    {{-- Left vertical accent stripe --}}
                    <div class="w-1.5 absolute left-0 top-3 bottom-3 rounded-r-full {{ $project->status === 'completed' ? 'bg-emerald-500' : ($project->status === 'in_progress' ? 'bg-blue-600' : 'bg-amber-500') }}"></div>

                    {{-- Top Row: Code, Title, Status --}}
                    <div class="flex items-start justify-between gap-2 pl-2">
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-wider block">
                                {{ $project->project_code }}
                            </span>
                            <a href="{{ route('projects.show', $project) }}" class="text-sm font-bold text-slate-900 dark:text-white hover:text-blue-600 transition block truncate">
                                {{ $project->title }}
                            </a>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full capitalize shrink-0 {{ $project->status === 'completed' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : ($project->status === 'in_progress' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400') }}">
                            {{ str_replace('_', ' ', $project->status) }}
                        </span>
                    </div>

                    {{-- Customer & Address --}}
                    <div class="text-xs text-slate-500 dark:text-slate-400 pl-2 space-y-0.5">
                        <div class="font-semibold text-slate-700 dark:text-slate-300 truncate">
                            {{ $project->company_name ?: ($project->company?->name ?? '—') }}
                        </div>
                        @if($project->site_address)
                            <div class="text-[11px] text-slate-400 flex items-center gap-1 truncate">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                <span class="truncate">{{ $project->site_address }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Progress Bar --}}
                    <div class="pl-2 space-y-1">
                        <div class="flex items-center justify-between text-[10px] font-bold text-slate-400">
                            <span>{{ __('Progress') }}</span>
                            <span>{{ $project->progress_percentage }}%</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-blue-600 transition-all" style="width: {{ $project->progress_percentage }}%"></div>
                        </div>
                    </div>

                    {{-- Metadata Footer: Team, Budget & Action Link --}}
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2 pl-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center font-bold text-[10px] shrink-0">
                                {{ strtoupper(substr($project->leadTechnician?->name ?? 'T', 0, 1)) }}
                            </div>
                            <span class="text-xs text-slate-600 dark:text-slate-300 font-medium truncate">
                                {{ $project->leadTechnician?->name ?? __('No tech') }}
                            </span>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            @if($project->budget)
                                <span class="text-xs font-bold text-slate-900 dark:text-white">
                                    {{ \App\Support\Inr::money($project->budget) }}
                                </span>
                            @endif

                            <a href="{{ route('projects.show', $project) }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                {{ __('Details') }} &rarr;
                            </a>
                        </div>
                    </div>

                </div>
            @empty
                <div class="bg-white dark:bg-slate-900 rounded-[22px] p-8 text-center border border-slate-100 dark:border-slate-800 text-xs text-slate-400">
                    {{ __('No projects match these filters.') }}
                </div>
            @endforelse
        </div>

        {{-- Mobile Pagination --}}
        @if($projects->hasPages())
            <div class="pt-2 flex items-center justify-between">
                @if($projects->onFirstPage())
                    <span class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 text-xs font-semibold">&larr; {{ __('Prev') }}</span>
                @else
                    <a href="{{ $projects->previousPageUrl() }}" class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-blue-600 dark:text-blue-400 text-xs font-bold shadow-2xs">&larr; {{ __('Prev') }}</a>
                @endif

                <span class="text-xs text-slate-400">
                    {{ $projects->currentPage() }} / {{ $projects->lastPage() }}
                </span>

                @if($projects->hasMorePages())
                    <a href="{{ $projects->nextPageUrl() }}" class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-blue-600 dark:text-blue-400 text-xs font-bold shadow-2xs">{{ __('Next') }} &rarr;</a>
                @else
                    <span class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 text-xs font-semibold">{{ __('Next') }} &rarr;</span>
                @endif
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- DESKTOP FULL TABLE VIEW (LG+ SCREENS & LAPTOPS)                     --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div class="hidden lg:block">
        <x-list-page key="projects" :paginator="$projects" :views="$views"
            :sorts="['status' => 'Open work first', 'newest' => 'Newest first', 'updated' => 'Recently updated', 'deadline_asc' => 'Deadline, soonest', 'budget_desc' => 'Budget, high to low']"
            :create-url="$canCreate ? route('projects.create') : null" :create-label="__('New Project')"
            :export-url="route('projects.export-csv', request()->all())"
            :filter-title="__('Filter Projects by')" :filter-action="route('projects.index')" :keep="['sort' => request('sort'), 'per_page' => request('per_page')]"
            :filter-count="$filterCount" :clear-url="route('projects.index')"
            :columns="['customer' => __('Customer'), 'type' => __('Type'), 'team' => __('Team'), 'progress' => __('Progress'), 'budget' => __('Budget / due'), 'priority' => __('Priority'), 'status' => __('Status')]"
            :empty-text="__('No projects yet.')" :filtered-text="__('No projects match these filters.')">

            <x-slot name="filters">
                <div class="ld-sub">Project, code, customer or site</div>
                <input type="text" name="search" value="{{ $search }}" class="ld-input" aria-label="Project, code, customer or site">

                <div class="ld-sub">Status</div>
                <select name="status" class="ld-input" aria-label="Status">
                    <option value="all">Any status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="ld-sub">Priority</div>
                <select name="priority" class="ld-input" aria-label="Priority">
                    <option value="all">Any priority</option>
                    @foreach($priorities as $value => $label)
                        <option value="{{ $value }}" @selected($priorityFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="ld-sub">Type</div>
                <select name="type" class="ld-input" aria-label="Project type">
                    <option value="all">Any type</option>
                    @foreach($projectTypes as $value => $label)
                        <option value="{{ $value }}" @selected($typeFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                @if($companies->isNotEmpty())
                    <div class="ld-sub">Executing company</div>
                    <select name="company" class="ld-input" aria-label="Executing company">
                        <option value="all">Any company</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" @selected((string) $companyFilter === (string) $comp->id)>{{ $comp->name }}</option>
                        @endforeach
                    </select>
                @endif

                <div class="ld-sub">Team member</div>
                <select name="assigned" class="ld-input" aria-label="Team member">
                    <option value="">Anyone</option>
                    @foreach($teamMembers as $tm)
                        <option value="{{ $tm->id }}" @selected((string) $assignedFilter === (string) $tm->id)>{{ $tm->name }}</option>
                    @endforeach
                </select>
            </x-slot>

            <x-slot name="head">
                <th style="min-width: 240px">{{ __('Project') }}</th>
                <th x-show="cols.customer" style="min-width: 200px">{{ __('Customer') }}</th>
                <th x-show="cols.type" style="min-width: 190px">{{ __('Type') }}</th>
                <th x-show="cols.team" style="min-width: 160px">{{ __('Team') }}</th>
                <th x-show="cols.progress" style="min-width: 110px">{{ __('Progress') }}</th>
                <th x-show="cols.budget" style="min-width: 130px; text-align: right">{{ __('Budget / due') }}</th>
                <th x-show="cols.priority" style="min-width: 90px">{{ __('Priority') }}</th>
                <th x-show="cols.status" style="min-width: 150px">{{ __('Status') }}</th>
            </x-slot>

            @foreach($projects as $project)
                @php $canEdit = auth()->user()->isAdmin() || ($project->created_by === auth()->id() && in_array($project->status, ['draft', 'pending_approval'])); @endphp
                <tr>
                    <x-list-pick :id="$project->id" :label="$project->project_code" />
                    <td>
                        <a href="{{ route('projects.show', $project) }}" class="ld-name plain-link">{{ $project->title }}</a>
                        <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $project->project_code }} · {{ $project->documents_count }} {{ Str::plural('document', $project->documents_count) }}</div>
                    </td>
                    <td x-show="cols.customer">
                        <div>{{ $project->company_name ?: '—' }}</div>
                        @if($project->site_address)<div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ Str::limit($project->site_address, 50) }}</div>@endif
                    </td>
                    <td x-show="cols.type">
                        <div>{{ $typeShort[$project->project_type] ?? ($projectTypes[$project->project_type] ?? $project->project_type_label) }}</div>
                        @if(!empty($project->hardware_specs['device_brand']))
                            <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $project->hardware_specs['device_brand'] }}</div>
                        @elseif(!empty($project->software_specs['webpage_url']))
                            <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ Str::limit($project->software_specs['webpage_url'], 30) }}</div>
                        @endif
                    </td>
                    <td x-show="cols.team">
                        <div>{{ $project->leadTechnician?->name ?? 'No technician' }}</div>
                        <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $project->assignedUser?->name ?? '—' }}</div>
                    </td>
                    <td x-show="cols.progress">
                        <div class="flex items-center gap-2">
                            <div class="h-1 w-14 rounded-full overflow-hidden" style="background: var(--ld-line)"><div class="h-full" style="width: {{ $project->progress_percentage }}%; background: var(--ld-accent)"></div></div>
                            <span>{{ $project->progress_percentage }}%</span>
                        </div>
                    </td>
                    <td x-show="cols.budget" class="whitespace-nowrap" style="text-align: right">
                        @if($project->budget)
                            {{ \App\Support\Inr::money($project->budget) }}
                        @elseif($project->deadline)
                            <span style="color: var(--ld-muted)">due {{ \Carbon\Carbon::parse($project->deadline)->format('d/m/Y') }}</span>
                        @else
                            <span style="color: var(--ld-muted)">—</span>
                        @endif
                    </td>
                    <td x-show="cols.priority">
                        <span class="{{ $project->priority === 'urgent' ? 'text-rose-700 dark:text-rose-400 font-medium' : ($project->priority === 'high' ? 'text-amber-700 dark:text-amber-400' : '') }}">{{ $priorities[$project->priority] ?? ucfirst($project->priority) }}</span>
                    </td>
                    <td x-show="cols.status">
                        @if(auth()->user()->isAdmin())
                            <form action="{{ route('projects.updateStatus', $project) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="ld-input" style="height: 30px; min-width: 140px" aria-label="Status of {{ $project->title }}">
                                    @foreach(['pending_approval', 'approved', 'in_progress', 'completed', 'on_hold'] as $st)
                                        <option value="{{ $st }}" @selected($project->status === $st)>{{ $statuses[$st] }}</option>
                                    @endforeach
                                    @unless(in_array($project->status, ['pending_approval', 'approved', 'in_progress', 'completed', 'on_hold']))
                                        <option value="{{ $project->status }}" selected disabled>{{ $project->status_label }}</option>
                                    @endunless
                                </select>
                            </form>
                        @else
                            {{ $project->status_label }}
                        @endif
                    </td>
                    <td>
                        <div class="ld-row-actions">
                            @if($canEdit)
                                <a href="{{ route('projects.edit', $project) }}" class="plain-link" title="Edit" aria-label="Edit {{ $project->title }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.2 5.2l3.6 3.6M4 20l4.5-1 10.3-10.3a2.5 2.5 0 00-3.5-3.5L5 15.5 4 20z"/></svg>
                                </a>
                            @endif
                            @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('projects.destroy', $project) }}" data-confirm="Delete project {{ $project->project_code }}? This cannot be undone.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-cancel" title="Delete" aria-label="Delete {{ $project->title }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-list-page>
    </div>
</x-app-layout>
