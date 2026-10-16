<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Project Handling &amp; Tracking</h2>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('projects.export-csv', request()->all()) }}" class="btn-secondary">
                    Export CSV
                </a>
                @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                <a href="{{ route('projects.create') }}" class="btn-primary">
                    New Project
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-5">

        {{-- Flash Alerts --}}
        @if (session('status'))
            <div class="p-3 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 dark:hover:text-white">&times;</button>
            </div>
        @endif
        @if (session('error'))
            <div class="p-3 rounded bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 dark:hover:text-white">&times;</button>
            </div>
        @endif

        {{-- Horizontal Filter Row - Medium 38px Height Level --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-2.5 shadow-xs">
            <form method="GET" action="{{ route('projects.index') }}" class="flex flex-wrap items-center gap-2.5 text-xs">
                {{-- Search Box --}}
                <div class="relative flex-1 min-w-[220px] max-w-xs flex items-center shrink-0">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" name="search" value="{{ $search }}"
                           placeholder="Search projects..." 
                           style="padding-left: 36px !important; height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important;"
                           class="w-full text-xs border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-blue-500">
                </div>

                {{-- Company Filter --}}
                <select name="company" class="crm-select text-xs shrink-0" style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important; min-width: 140px;">
                    <option value="all">All Companies</option>
                    @foreach($companies as $comp)
                        <option value="{{ $comp->id }}" {{ $companyFilter == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
                    @endforeach
                </select>

                {{-- Assignee Filter --}}
                <select name="assigned" class="crm-select text-xs shrink-0" style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important; min-width: 130px;">
                    <option value="">All Assignees</option>
                    @foreach($teamMembers as $tm)
                        <option value="{{ $tm->id }}" {{ $assignedFilter == $tm->id ? 'selected' : '' }}>
                            {{ $tm->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Status Filter --}}
                <select name="status" class="crm-select text-xs shrink-0" style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important; min-width: 120px;">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending_approval" {{ $statusFilter === 'pending_approval' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="on_hold" {{ $statusFilter === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                    <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                {{-- Priority Filter --}}
                <select name="priority" class="crm-select text-xs shrink-0" style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important; min-width: 120px;">
                    <option value="all" {{ $priorityFilter === 'all' ? 'selected' : '' }}>All Priorities</option>
                    <option value="urgent" {{ $priorityFilter === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ $priorityFilter === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ $priorityFilter === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ $priorityFilter === 'low' ? 'selected' : '' }}>Low</option>
                </select>

                {{-- Type Filter --}}
                <select name="type" class="crm-select text-xs shrink-0" style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important; min-width: 120px;">
                    <option value="all">All Types</option>
                    <option value="hardware_attendance" {{ $typeFilter === 'hardware_attendance' ? 'selected' : '' }}>Attendance</option>
                    <option value="hardware_cctv" {{ $typeFilter === 'hardware_cctv' ? 'selected' : '' }}>CCTV</option>
                    <option value="software_web" {{ $typeFilter === 'software_web' ? 'selected' : '' }}>Web Apps</option>
                    <option value="hybrid" {{ $typeFilter === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                </select>

                {{-- Submit and Reset Buttons --}}
                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" 
                            style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important;"
                            class="px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition inline-flex items-center justify-center cursor-pointer">
                        Filter
                    </button>
                    @if($search || $statusFilter !== 'all' || $priorityFilter !== 'all' || ($typeFilter && $typeFilter !== 'all') || $assignedFilter || $companyFilter !== 'all')
                    <a href="{{ route('projects.index') }}" 
                       style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important;"
                       class="px-3.5 inline-flex items-center border border-slate-300 dark:border-slate-700 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold transition" 
                       title="Clear all filters">
                        Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- 3. Projects List / Grid --}}
        @if($projects->isEmpty())
            <div class="bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 p-12 text-center">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-white">No projects found</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    @if($search)
                        No project matches your search for "{{ $search }}". Try clearing filters.
                    @else
                        No projects match the selected criteria.
                    @endif
                </p>
                <div class="mt-4 flex justify-center gap-2">
                    @if($search || $statusFilter !== 'all' || $priorityFilter !== 'all' || ($typeFilter && $typeFilter !== 'all'))
                        <a href="{{ route('projects.index') }}" class="px-3 py-1.5 rounded border border-slate-300 bg-white text-slate-700 text-xs font-medium">Reset Filter</a>
                    @endif
                    <a href="{{ route('projects.create') }}" class="px-3 py-1.5 rounded bg-blue-600 text-white text-xs font-semibold">
                        Create Project
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                @foreach($projects as $project)
                    <div class="bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 flex flex-col justify-between overflow-hidden">
                        {{-- Card Header --}}
                        <div class="p-4 pb-3">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-mono font-semibold text-slate-600 dark:text-slate-400">
                                        {{ $project->project_code }}
                                    </span>
                                    <span class="text-xs font-bold" style="color: {{ in_array($project->status, ['completed', 'approved']) ? '#16a34a' : (in_array($project->status, ['rejected', 'cancelled']) ? '#dc2626' : ($project->status === 'in_progress' ? '#2563eb' : '#d97706')) }};">
                                        {{ $project->status_label }}
                                    </span>
                                </div>
                                <span class="text-xs font-semibold" style="color: {{ $project->priority === 'urgent' ? '#dc2626' : ($project->priority === 'high' ? '#ea580c' : '#64748b') }};">
                                    {{ ucfirst($project->priority) }}
                                </span>
                            </div>

                            {{-- Project Type & Device Info --}}
                            <div class="my-1.5 flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">
                                    @if($project->project_type === 'hardware_attendance')
                                        Terminal Attendance
                                    @elseif($project->project_type === 'software_web')
                                        Webpage &amp; Apps
                                    @elseif($project->project_type === 'hybrid')
                                        Hybrid Turnkey
                                    @else
                                        CCTV Hardware
                                    @endif
                                </span>
                                @if(!empty($project->hardware_specs['device_brand']))
                                    <span class="text-xs text-slate-500 font-medium">{{ $project->hardware_specs['device_brand'] }}</span>
                                @elseif(!empty($project->software_specs['webpage_url']))
                                    <span class="text-xs text-slate-500 font-medium">{{ Str::limit($project->software_specs['webpage_url'], 24) }}</span>
                                @endif
                            </div>

                            {{-- Project Title --}}
                            <div>
                                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    {{ $project->company_name }}
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 line-clamp-1">
                                    <a href="{{ route('projects.show', $project) }}" class="hover:text-blue-600">{{ $project->title }}</a>
                                </h3>
                                @if($project->site_address)
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-1">
                                        {{ $project->site_address }}
                                    </p>
                                @endif
                            </div>

                            {{-- Technical / Lead Tech Info --}}
                            <div class="mt-2 text-xs text-slate-600 dark:text-slate-300 flex items-center justify-between">
                                <span>Tech: {{ $project->leadTechnician?->name ?? 'Unassigned' }}</span>
                                <span>{{ $project->assignedUser?->name ?? 'Staff' }}</span>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-slate-500">Progress</span>
                                    <span class="text-slate-900 dark:text-white font-semibold">{{ $project->progress_percentage }}%</span>
                                </div>
                                <div class="w-full h-1.5 rounded bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div class="h-full bg-blue-600" style="width: {{ $project->progress_percentage }}%"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Metadata --}}
                        <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                            <span class="text-slate-500">
                                {{ $project->documents_count }} {{ Str::plural('Doc', $project->documents_count) }}
                            </span>
                            <div class="text-right">
                                @if($project->budget)
                                    <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($project->budget, 0) }}</span>
                                @elseif($project->deadline)
                                    <span class="text-slate-500">Due {{ \Carbon\Carbon::parse($project->deadline)->format('M d') }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </div>
                        </div>

                        {{-- Card Actions Footer --}}
                        <div class="p-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2 bg-white dark:bg-slate-900">
                            @if(auth()->user()->isAdmin())
                                <form action="{{ route('projects.updateStatus', $project) }}" method="POST" class="inline-flex">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="text-xs h-[30px] px-2 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                                        <option value="pending_approval" {{ $project->status === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                        <option value="approved" {{ $project->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="in_progress" {{ $project->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="on_hold" {{ $project->status === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                    </select>
                                </form>
                            @else
                                <span class="text-xs text-slate-500">
                                    {{ $project->status_label }}
                                </span>
                            @endif

                            <div class="table-actions inline-flex items-center gap-2 justify-end text-sm">
                                <a href="{{ route('projects.show', $project) }}" class="action-link">View</a>
                                @if(auth()->user()->isAdmin() || ($project->created_by === auth()->id() && in_array($project->status, ['draft', 'pending_approval'])))
                                    <a href="{{ route('projects.edit', $project) }}" class="action-link">Edit</a>
                                @endif
                                @if(auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('projects.destroy', $project) }}" class="inline" onsubmit="return confirm('Delete project {{ addslashes($project->project_code) }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="danger-link">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Total count shown below the table/grid --}}
            <div class="mt-4 px-4 py-2.5 rounded border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between text-xs text-slate-500">
                <span class="font-medium">Total: {{ $projects->total() }}</span>
                @if($projects->hasPages())
                    <div>
                        {{ $projects->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-app-layout>
