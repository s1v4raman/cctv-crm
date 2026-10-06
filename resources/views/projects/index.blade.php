<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Project Handling & Tracking</h2>
                    <span class="text-xs font-bold uppercase tracking-wider bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-2.5 py-1 rounded border border-indigo-200 dark:border-indigo-500/30">Operations Hub</span>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Track ongoing, completed, and pending company projects with attached blueprints and documentation</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('projects.export-csv', request()->all()) }}" class="btn-secondary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export CSV
                </a>
                @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                <a href="{{ route('projects.create') }}" class="btn-amber">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    + New Project
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

        {{-- Flash Alerts --}}
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 dark:hover:text-white">&times;</button>
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 dark:hover:text-white">&times;</button>
            </div>
        @endif

        {{-- 1. KPI Metrics Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5 sm:gap-4">
            {{-- Total Projects --}}
            <a href="{{ route('projects.index') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:border-blue-500/50 transition-all group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Projects</span>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-slate-900 dark:text-white font-heading">{{ $totalProjectsCount }}</span>
                    <span class="text-xs text-slate-400">All registered</span>
                </div>
            </a>

            {{-- In Progress --}}
            <a href="{{ route('projects.index', ['status' => 'in_progress']) }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:border-amber-500/50 transition-all group {{ $statusFilter === 'in_progress' ? 'ring-2 ring-amber-500/50' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">In Progress</span>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-400 font-heading">{{ $inProgressCount }}</span>
                    <span class="text-xs text-slate-400">Active Work</span>
                </div>
            </a>

            {{-- Done / Completed --}}
            <a href="{{ route('projects.index', ['status' => 'completed']) }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:border-emerald-500/50 transition-all group {{ $statusFilter === 'completed' ? 'ring-2 ring-emerald-500/50' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Done / Completed</span>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-heading">{{ $completedCount }}</span>
                    <span class="text-xs text-slate-400">Delivered</span>
                </div>
            </a>

            {{-- Incomplete / Pending --}}
            <a href="{{ route('projects.index', ['status' => 'incompleted']) }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:border-rose-500/50 transition-all group {{ $statusFilter === 'incompleted' ? 'ring-2 ring-rose-500/50' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Incomplete</span>
                    <span class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-rose-600 dark:text-rose-400 font-heading">{{ $incompletedCount }}</span>
                    <span class="text-xs text-slate-400">Needs Action</span>
                </div>
            </a>

            {{-- Total Budget Valuation --}}
            <div class="col-span-2 lg:col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Valuation</span>
                    <span class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <span class="text-sm font-extrabold">₹</span>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-purple-600 dark:text-purple-400 font-heading">₹{{ number_format($totalValuation, 0) }}</span>
                </div>
            </div>
        </div>

        {{-- 2. Search & Filter Bar (Company Search prioritized) --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 sm:p-6">
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-3">Search & Filter Projects</p>
            <form method="GET" action="{{ route('projects.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-end gap-4">
                {{-- Search Box --}}
                <div class="relative flex-1">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Search by Company / Title / Code</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="search" name="search" value="{{ $search }}"
                               placeholder="e.g. TechPark, Apex Logistics, PRJ-2026-0001..." 
                               class="w-full pl-12 pr-4 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 transition-all">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="w-full sm:w-56">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select name="status" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Done / Completed</option>
                        <option value="incompleted" {{ $statusFilter === 'incompleted' ? 'selected' : '' }}>Incompleted / Pending</option>
                        <option value="on_hold" {{ $statusFilter === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                {{-- Priority Filter --}}
                <div class="w-full sm:w-44">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Priority</label>
                    <select name="priority" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2">
                        <option value="all" {{ $priorityFilter === 'all' ? 'selected' : '' }}>All Priorities</option>
                        <option value="urgent" {{ $priorityFilter === 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                        <option value="high" {{ $priorityFilter === 'high' ? 'selected' : '' }}>🟠 High</option>
                        <option value="medium" {{ $priorityFilter === 'medium' ? 'selected' : '' }}>🟡 Medium</option>
                        <option value="low" {{ $priorityFilter === 'low' ? 'selected' : '' }}>🟢 Low</option>
                    </select>
                </div>

                {{-- Submit and Reset Buttons --}}
                <div class="flex items-center gap-3 pb-0">
                    <button type="submit" class="btn-amber flex-1 sm:flex-none justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Search
                    </button>
                    @if($search || $statusFilter !== 'all' || $priorityFilter !== 'all')
                    <a href="{{ route('projects.index') }}" class="btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- 3. Projects List / Grid --}}
        @if($projects->isEmpty())
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800 p-12 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 dark:text-indigo-400 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800 dark:text-white">No projects found</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    @if($search)
                        No project matches your search for "<span class="font-semibold text-slate-700 dark:text-slate-300">{{ $search }}</span>". Try searching by another company name or clear filters.
                    @else
                        No projects match the selected criteria. Start by registering your first installation project.
                    @endif
                </p>
                <div class="mt-5 flex justify-center gap-3">
                    @if($search || $statusFilter !== 'all' || $priorityFilter !== 'all')
                        <a href="{{ route('projects.index') }}" class="btn-secondary text-xs py-2 px-4">Reset Filter</a>
                    @endif
                    <a href="{{ route('projects.create') }}" class="btn-amber text-xs py-2 px-4">+ Create Project</a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($projects as $project)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all flex flex-col justify-between overflow-hidden group">
                        {{-- Card Header --}}
                        <div class="p-5 pb-3">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $project->project_code }}
                                    </span>
                                    <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full border {{ $project->status_badge_classes }}">
                                        {{ $project->status_label }}
                                    </span>
                                </div>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md border {{ $project->priority_badge_classes }}">
                                    {{ ucfirst($project->priority) }}
                                </span>
                            </div>

                            {{-- Company & Project Title --}}
                            <div class="mt-2">
                                <div class="flex items-center gap-1.5 text-xs font-extrabold text-blue-600 dark:text-blue-400">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span class="truncate" title="{{ $project->company_name }}">{{ $project->company_name }}</span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white mt-0.5 line-clamp-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    <a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a>
                                </h3>
                                @if($project->site_address)
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-1 flex items-center gap-1">
                                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                        {{ $project->site_address }}
                                    </p>
                                @endif
                            </div>

                            {{-- Progress Bar --}}
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-xs mb-1 font-semibold">
                                    <span class="text-slate-600 dark:text-slate-400">Completion</span>
                                    <span class="text-slate-900 dark:text-white font-bold">{{ $project->progress_percentage }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300 {{ $project->progress_bar_color }}" style="width: {{ $project->progress_percentage }}%"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Metadata & Documents badge --}}
                        <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                            {{-- Documents Pill --}}
                            <div class="flex items-center gap-1.5 font-semibold {{ $project->documents_count > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>{{ $project->documents_count }} {{ Str::plural('Document', $project->documents_count) }}</span>
                            </div>

                            {{-- Budget or Deadline --}}
                            <div class="text-right">
                                @if($project->budget)
                                    <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($project->budget, 0) }}</span>
                                @elseif($project->deadline)
                                    <span class="text-slate-500 dark:text-slate-400">Due {{ \Carbon\Carbon::parse($project->deadline)->format('M d') }}</span>
                                @else
                                    <span class="text-slate-400">No Budget</span>
                                @endif
                            </div>
                        </div>

                        {{-- Card Actions Footer --}}
                        <div class="p-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2 bg-white dark:bg-slate-900">
                            {{-- Quick 1-click status dropdown --}}
                            <form action="{{ route('projects.updateStatus', $project) }}" method="POST" class="inline-flex">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="text-xs py-1.5 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold cursor-pointer focus:ring-1 focus:ring-blue-500">
                                    <option value="in_progress" {{ $project->status === 'in_progress' ? 'selected' : '' }}>• In Progress</option>
                                    <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>✓ Done / Completed</option>
                                    <option value="incompleted" {{ $project->status === 'incompleted' ? 'selected' : '' }}>! Incompleted</option>
                                    <option value="on_hold" {{ $project->status === 'on_hold' ? 'selected' : '' }}>⏸ On Hold</option>
                                </select>
                            </form>

                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('projects.show', $project) }}" class="btn-secondary text-xs py-1.5 px-3 font-bold" title="Open Project & View Documents">
                                    View & Files
                                </a>
                                @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                <a href="{{ route('projects.edit', $project) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Edit Project">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $projects->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
