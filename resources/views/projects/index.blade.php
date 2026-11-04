<x-app-layout>
    @php
        $statuses = \App\Models\Project::statusOptions();
        $priorities = \App\Models\Project::priorityOptions();
        $views = collect(['all' => 'All Projects'] + $statuses)->map(fn ($label, $value) => [
            'label' => $value === 'all' ? $label : $label,
            'url' => route('projects.index', array_filter(['status' => $value === 'all' ? null : $value])),
            'active' => $statusFilter === $value,
        ])->values()->all();
        $filterCount = collect([$search !== '' , $statusFilter !== 'all', $priorityFilter !== 'all', $typeFilter !== 'all', $companyFilter !== 'all', (bool) $assignedFilter])->filter()->count();
        $canCreate = auth()->user()->can('create', \App\Models\Project::class);
        $typeShort = ['hardware_cctv' => 'CCTV Hardware', 'hardware_attendance' => 'Terminal Attendance', 'networking' => 'Networking', 'access_control' => 'Access Control', 'software_web' => 'Webpage & Apps', 'hybrid' => 'Hybrid Turnkey'];
    @endphp

    <x-list-page key="projects" :paginator="$projects" :views="$views"
        :sorts="['status' => 'Open work first', 'newest' => 'Newest first', 'updated' => 'Recently updated', 'deadline_asc' => 'Deadline, soonest', 'budget_desc' => 'Budget, high to low']"
        :create-url="$canCreate ? route('projects.create') : null" create-label="New Project"
        :export-url="route('projects.export-csv', request()->all())"
        filter-title="Filter Projects by" :filter-action="route('projects.index')" :keep="['sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('projects.index')"
        :columns="['customer' => 'Customer', 'type' => 'Type', 'team' => 'Team', 'progress' => 'Progress', 'budget' => 'Budget / due', 'priority' => 'Priority', 'status' => 'Status']"
        empty-text="No projects yet." filtered-text="No projects match these filters.">

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
            <th style="min-width: 240px">Project</th>
            <th x-show="cols.customer" style="min-width: 200px">Customer</th>
            <th x-show="cols.type" style="min-width: 190px">Type</th>
            <th x-show="cols.team" style="min-width: 160px">Team</th>
            <th x-show="cols.progress" style="min-width: 110px">Progress</th>
            <th x-show="cols.budget" style="min-width: 130px; text-align: right">Budget / due</th>
            <th x-show="cols.priority" style="min-width: 90px">Priority</th>
            <th x-show="cols.status" style="min-width: 150px">Status</th>
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
</x-app-layout>
