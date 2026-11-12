<x-app-layout>
    @php
        $currSearch = request('search');
        $currWorker = request('worker_id');
        $currProject = request('project_id');
        $currStatus = request('status');
        $filterCount = ($currSearch ? 1 : 0) + ($currWorker ? 1 : 0) + ($currProject ? 1 : 0) + ($currStatus && $currStatus !== 'all' ? 1 : 0);
        $here = fn (array $change) => request()->fullUrlWithQuery(array_merge(['page' => null], $change));
        // Only the day roster lists single punches; the week and month views pass none
        $attendances = $attendances ?? collect();

        $viewTitle = __('Per-Day (Daily Roster)');
        if ($viewMode === 'week') { $viewTitle = __('Per-Week (Weekly Matrix)'); }
        elseif ($viewMode === 'month') { $viewTitle = __('Per-Month (Monthly Timesheet)'); }
    @endphp

    <x-list-page-assets />

    <div class="ld-root" x-data="listPage(@js($attendances->pluck('id')), 'attendance', {}, @js($filterCount > 0))" @keydown.escape.window="menu = null">

        @if (session('status'))
            <div class="px-4 py-2.5 text-sm bg-emerald-50 text-emerald-800 border-b border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900 flex items-center justify-between">
                <span>{{ session('status') }}</span>
                <x-close-button size="sm" tone="inherit" label="Dismiss message" @click="$el.parentElement.remove()" />
            </div>
        @endif

        @if (session('error'))
            <div class="px-4 py-2.5 text-sm bg-rose-50 text-rose-800 border-b border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900 flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <x-close-button size="sm" tone="inherit" label="Dismiss message" @click="$el.parentElement.remove()" />
            </div>
        @endif

        {{-- Top Band: Saved Views (Day/Week/Month) + Date Nav & Action Toolbar --}}
        <div class="ld-band">
            {{-- Desktop / Laptop: View Chip + Three Dots Menu --}}
            <div class="hidden sm:flex ld-views items-center justify-start gap-2">
                <span class="ld-chip">{{ $viewTitle }}</span>
                <div class="relative" @click.outside="menu === 'views' && (menu = null)">
                    <button type="button" class="ld-icon-btn" title="Switch View" aria-label="Other attendance views" :aria-expanded="menu === 'views'" @click="open('views')">
                        <svg class="icon-solid" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><circle cx="4" cy="10" r="1.6"/><circle cx="10" cy="10" r="1.6"/><circle cx="16" cy="10" r="1.6"/></svg>
                    </button>
                    <div class="ld-menu left-0" x-show="menu === 'views'" x-cloak style="width: 250px;">
                        <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'day'])) }}" class="plain-link {{ $viewMode === 'day' ? 'is-current' : '' }}">
                            <span>{{ __('Per-Day (Daily Roster)') }}</span>
                            @if($viewMode === 'day')<svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                        </a>
                        <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'week'])) }}" class="plain-link {{ $viewMode === 'week' ? 'is-current' : '' }}">
                            <span>{{ __('Per-Week (Weekly Matrix)') }}</span>
                            @if($viewMode === 'week')<svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                        </a>
                        <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'month'])) }}" class="plain-link {{ $viewMode === 'month' ? 'is-current' : '' }}">
                            <span>{{ __('Per-Month (Monthly Timesheet)') }}</span>
                            @if($viewMode === 'month')<svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                        </a>
                    </div>
                </div>
            </div>

            {{-- Mobile App View: Exact Technician Dashboard Card Design --}}
            <div class="sm:hidden p-2 m-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 text-xs font-bold">
                    <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'day'])) }}" 
                       class="flex-1 text-center py-2 px-3 rounded-xl transition-all border {{ $viewMode === 'day' ? 'bg-[#65a30d] text-white border-[#65a30d] shadow-sm font-black' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50' }}"
                       @if($viewMode === 'day') style="color: #ffffff !important;" @endif>
                        {{ __('Day') }}
                    </a>
                    <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'week'])) }}" 
                       class="flex-1 text-center py-2 px-3 rounded-xl transition-all border {{ $viewMode === 'week' ? 'bg-[#65a30d] text-white border-[#65a30d] shadow-sm font-black' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50' }}"
                       @if($viewMode === 'week') style="color: #ffffff !important;" @endif>
                        {{ __('Week') }}
                    </a>
                    <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'month'])) }}" 
                       class="flex-1 text-center py-2 px-3 rounded-xl transition-all border {{ $viewMode === 'month' ? 'bg-[#65a30d] text-white border-[#65a30d] shadow-sm font-black' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50' }}"
                       @if($viewMode === 'month') style="color: #ffffff !important;" @endif>
                        {{ __('Month') }}
                    </a>
                </div>
            </div>

            <div class="ld-toolbar p-3 sm:px-4 sm:py-2.5">
                {{-- Left: Filter & Date Controls --}}
                <div class="flex items-center gap-1.5 flex-wrap w-full sm:w-auto">
                    {{-- Slide-out Filter toggle button --}}
                    <button type="button" class="ld-tool shrink-0" :class="showFilter && 'is-on'" @click="showFilter = !showFilter" :aria-pressed="showFilter">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18l-7 8.5V19l-4 2v-7.5L3 5z"/></svg>
                        <span>{{ __('Filter') }}</span>
                        @if($filterCount > 0)<span class="text-xs font-semibold" style="color: var(--ld-accent)">{{ $filterCount }}</span>@endif
                    </button>

                    {{-- Date Navigation Group (Kept together without breaking) --}}
                    <div class="inline-flex items-center gap-1 bg-slate-50 dark:bg-slate-800/60 p-0.5 rounded-lg border border-slate-200 dark:border-slate-700/80 shrink-0">
                        @if($viewMode === 'day')
                            @php
                                $prevDay = $selectedDate->copy()->subDay()->toDateString();
                                $nextDay = $selectedDate->copy()->addDay()->toDateString();
                                $isToday = $selectedDate->isToday();
                            @endphp
                            <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'day', 'date' => $prevDay])) }}" class="ld-tool plain-link !h-7 !px-2" title="{{ __('Previous Day') }}">&larr;</a>
                            
                            <form method="GET" action="{{ route('workers.attendance.index') }}" class="inline-flex items-center">
                                <input type="hidden" name="view" value="day">
                                @if(request('worker_id')) <input type="hidden" name="worker_id" value="{{ request('worker_id') }}"> @endif
                                @if(request('project_id')) <input type="hidden" name="project_id" value="{{ request('project_id') }}"> @endif
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                <input aria-label="Attendance date" type="date" name="date" value="{{ $selectedDate->toDateString() }}" onchange="this.form.submit()" class="ld-input text-xs font-semibold !h-7 !py-0" style="width: 125px; padding: 0 4px;">
                            </form>

                            <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'day', 'date' => $nextDay])) }}" class="ld-tool plain-link !h-7 !px-2" title="{{ __('Next Day') }}">&rarr;</a>

                            @if(!$isToday)
                                <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'day', 'date' => now()->toDateString()])) }}" class="ld-tool plain-link font-bold !h-7 !px-2" style="color: var(--ld-accent)">{{ __('Today') }}</a>
                            @endif
                        @elseif($viewMode === 'week')
                            @php
                                $prevWeek = $weekStart->copy()->subWeek()->toDateString();
                                $nextWeek = $weekStart->copy()->addWeek()->toDateString();
                                $isCurrentWeek = $weekStart->isCurrentWeek();
                            @endphp
                            <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'week', 'week' => $prevWeek])) }}" class="ld-tool plain-link !h-7 !px-2">&larr;</a>
                            <span class="px-2 text-xs font-bold text-slate-800 dark:text-slate-200">
                                {{ $weekStart->format('d M') }} &ndash; {{ $weekEnd->format('d M') }}
                            </span>
                            <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'week', 'week' => $nextWeek])) }}" class="ld-tool plain-link !h-7 !px-2">&rarr;</a>
                        @else
                            @php
                                $prevMonth = $monthStart->copy()->subMonth()->format('Y-m');
                                $nextMonth = $monthStart->copy()->addMonth()->format('Y-m');
                            @endphp
                            <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'month', 'month' => $prevMonth])) }}" class="ld-tool plain-link !h-7 !px-2">&larr;</a>
                            <form method="GET" action="{{ route('workers.attendance.index') }}" class="inline-flex items-center">
                                <input type="hidden" name="view" value="month">
                                @if(request('worker_id')) <input type="hidden" name="worker_id" value="{{ request('worker_id') }}"> @endif
                                @if(request('project_id')) <input type="hidden" name="project_id" value="{{ request('project_id') }}"> @endif
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()" class="ld-input text-xs font-semibold !h-7 !py-0" style="width: 125px; padding: 0 4px;">
                            </form>
                            <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'month', 'month' => $nextMonth])) }}" class="ld-tool plain-link !h-7 !px-2">&rarr;</a>
                        @endif
                    </div>

                    {{-- Secondary Links --}}
                    <div class="hidden md:inline-flex items-center gap-1">
                        <span class="ld-sep" aria-hidden="true"></span>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('wages.index') }}" class="ld-tool plain-link text-xs" title="{{ __('Sunday Payday Console') }}">
                                <span>{{ __('Wages') }}</span>
                            </a>
                        @endif
                        <a href="{{ route('workers.index') }}" class="ld-tool plain-link text-xs" title="{{ __('Workers') }}">
                            <span>{{ __('Workers') }}</span>
                        </a>
                    </div>
                </div>

                {{-- Right: Primary Action Buttons (Mark & Bulk Attendance) + More Menu --}}
                <div class="flex items-center gap-2 w-full sm:w-auto sm:ml-auto mt-2 sm:mt-0">
                    <button type="button" onclick="openAttendanceModal()" class="ld-accent flex-1 sm:flex-initial !inline-flex items-center justify-center gap-2 whitespace-nowrap shrink-0 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer" style="min-height: 38px;">
                        <svg class="w-4 h-4 shrink-0 !text-white" fill="none" stroke="#ffffff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span class="whitespace-nowrap !text-white" style="color: #ffffff !important;">{{ __('Mark Attendance') }}</span>
                    </button>

                    <button type="button" onclick="openBulkAttendanceModal('{{ $viewMode === 'day' ? $selectedDate->toDateString() : now()->toDateString() }}')" class="ld-outline flex-1 sm:flex-initial !inline-flex items-center justify-center gap-2 whitespace-nowrap shrink-0 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer" style="min-height: 38px;">
                        <svg class="w-4 h-4 shrink-0 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                        <span class="whitespace-nowrap">{{ __('Bulk Attendance') }}</span>
                    </button>

                    <div class="relative" @click.outside="menu === 'more' && (menu = null)">
                        <button type="button" class="ld-outline !inline-flex items-center justify-center p-2 rounded-xl shrink-0 cursor-pointer" style="min-height: 38px; width: 38px;" title="More options" aria-label="More actions" :aria-expanded="menu === 'more'" @click="open('more')">
                            <svg class="w-4 h-4 icon-solid shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><circle cx="4" cy="10" r="1.6"/><circle cx="10" cy="10" r="1.6"/><circle cx="16" cy="10" r="1.6"/></svg>
                        </button>
                        <div class="ld-menu right-0" x-show="menu === 'more'" x-cloak>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('wages.index') }}" class="plain-link">{{ __('Open Sunday Payday Console') }}</a>
                            @endif
                            <a href="{{ route('workers.index') }}" class="plain-link">{{ __('View All Registered Workers') }}</a>
                            <button type="button" @click="menu = null; $nextTick(() => window.print())">{{ __('Print View') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- Body: Filter Panel + Main Content --}}
        <div class="ld-body" x-ref="body">

            {{-- Slide-out Left Filter Drawer --}}
            <div class="ld-card ld-filter" role="search" x-show="showFilter" x-cloak aria-label="Filter attendance">
                <form method="GET" action="{{ route('workers.attendance.index') }}" class="flex flex-col h-full min-h-0">
                    <input type="hidden" name="view" value="{{ $viewMode }}">
                    @if($viewMode === 'day')
                        <input type="hidden" name="date" value="{{ $selectedDate->toDateString() }}">
                    @elseif($viewMode === 'week')
                        <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                    @else
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    @endif

                    <div class="ld-filter-scroll">
                        <h2>{{ __('Filter Attendance by') }}</h2>
                        <div class="relative">
                            <svg class="w-4 h-4 absolute left-2.5 top-2.5" style="color: var(--ld-muted)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                            <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Search worker name, phone or notes…') }}" aria-label="Search worker notes" class="ld-input" style="padding-left: 2.75rem !important;">
                        </div>

                        <details open>
                            <summary><span class="ld-caret ld-caret-sm" aria-hidden="true"></span><span>{{ __('Assigned Worker') }}</span></summary>
                            <div class="space-y-1">
                                <select name="worker_id" class="ld-input" aria-label="Assigned Worker">
                                    <option value="">{{ __('All Workers (:count)', ['count' => $activeWorkers->count()]) }}</option>
                                    @foreach($activeWorkers as $w)
                                        <option value="{{ $w->id }}" @selected(request('worker_id') == $w->id)>{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </details>

                        <details open>
                            <summary><span class="ld-caret ld-caret-sm" aria-hidden="true"></span><span>{{ __('Project / Site') }}</span></summary>
                            <div class="space-y-1">
                                <select name="project_id" class="ld-input" aria-label="Project / Site">
                                    <option value="">{{ __('All Projects') }}</option>
                                    @foreach($activeProjects as $p)
                                        <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ Str::limit($p->title, 26) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </details>
                    </div>

                    <div class="ld-filter-actions">
                        <button type="submit" id="ld-apply" class="ld-accent" style="border-radius: 4px; height: 30px; padding: 0 12px">{{ __('Apply Filter') }}</button>
                        @if($filterCount > 0)
                            <a href="{{ route('workers.attendance.index', ['view' => $viewMode]) }}" class="ld-outline plain-link" style="height: 30px">{{ __('Clear') }}</a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Main Content Area --}}
            <section class="ld-main" aria-label="Attendance">
{{-- Metrics KPI Banner --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @if($viewMode === 'day')
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Workers Present') }}</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $metrics['total_present'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $metrics['full_day_count'] }} {{ __('Full') }} &bull; {{ $metrics['half_day_count'] }} {{ __('Half') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Cabling Run') }}</div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-white mt-1">{{ number_format($metrics['total_cabling'], 1) }} <span class="text-xs font-normal text-slate-500">m</span></div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Physical cable meters') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Cameras Mounted') }}</div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-white mt-1">{{ $metrics['total_cameras'] }} <span class="text-xs font-normal text-slate-500">units</span></div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Installed today') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Total Labor Wage') }}</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">&#8377;{{ number_format($metrics['total_labor_cost'], 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Daily wages incurred') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Photos Logged') }}</div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-white mt-1">{{ $attendances->whereNotNull('photo_path')->count() }} / {{ $metrics['total_present'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('With selfie/punch photo') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Total Active Pool') }}</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $activeWorkers->count() }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Registered workers') }}</div>
                </div>
            @elseif($viewMode === 'week')
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Weekly Shifts') }}</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $metrics['total_shifts'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Effective worker days') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Workers Deployed') }}</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $metrics['workers_count'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('On weekly sites') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Weekly Cabling') }}</div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-white mt-1">{{ number_format($metrics['total_cabling'], 1) }} <span class="text-xs font-normal text-slate-500">m</span></div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Total cable pulled') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Weekly Cameras') }}</div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-white mt-1">{{ $metrics['total_cameras'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Mounted this week') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Gross Labor Cost') }}</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">&#8377;{{ number_format($metrics['total_labor_cost'], 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Total weekly payable') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm flex flex-col justify-center items-start">
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('wages.index', ['week' => $weekStart->toDateString()]) }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        <span>{{ __('Open Sunday Payday') }} &rarr;</span>
                    </a>
                    @endif
                    <div class="text-[11px] text-slate-500 mt-1">{{ __('Settle weekly disbursements') }}</div>
                </div>
            @else
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Monthly Shifts') }}</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $metrics['total_shifts'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Worker days logged') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Monthly Cabling') }}</div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-white mt-1">{{ number_format($metrics['total_cabling'], 1) }} <span class="text-xs font-normal text-slate-500">m</span></div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Total metres installed') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Monthly Cameras') }}</div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-white mt-1">{{ $metrics['total_cameras'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Cameras configured') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Gross Labor Cost') }}</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">&#8377;{{ number_format($metrics['total_gross'], 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Monthly wages incurred') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Settled / Paid') }}</div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-white mt-1">&#8377;{{ number_format($metrics['total_paid'], 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Cleared disbursements') }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{{ __('Outstanding') }}</div>
                    <div class="text-xl font-bold {{ $metrics['total_unpaid'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }} mt-1">&#8377;{{ number_format($metrics['total_unpaid'], 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Pending settlement') }}</div>
                </div>
            @endif
        </div>

        {{-- Main Table View Depending on View Mode --}}
        @if($viewMode === 'day')
            {{-- PER-DAY VIEW --}}
            <div class="ld-card flex flex-col flex-1 min-h-0 overflow-hidden mb-6">
                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm text-slate-900 dark:text-white">{{ __('Daily Attendance Roster & Photo Verification') }}</span>
                        <span class="text-xs text-slate-500">&bull; {{ $selectedDate->format('l, d F Y') }}</span>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- CSV Export for Data Analysis --}}
                        <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['export' => 'csv'])) }}" 
                           class="text-xs font-semibold px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 transition flex items-center gap-1.5 shadow-2xs"
                           title="{{ __('Download attendance data in CSV format for spreadsheet analysis') }}">
                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12L12 16.5m0 0L16.5 12M12 16.5V3"/></svg>
                            <span>{{ __('Export CSV') }}</span>
                        </a>

                        @if(isset($unpunchedWorkers) && $unpunchedWorkers->isNotEmpty())
                            <button type="button" onclick="openBulkAttendanceModal('{{ $selectedDate->toDateString() }}', {{ Js::from($unpunchedWorkers->pluck('id')->values()) }})" class="text-xs font-bold px-2.5 py-1 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 transition cursor-pointer flex items-center gap-1 shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                                <span>{{ __('+ Bulk Punch Unpunched (:count)', ['count' => $unpunchedWorkers->count()]) }}</span>
                            </button>
                        @endif

                        <span class="text-xs font-semibold px-2 py-0.5 rounded border" style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.12); color: var(--link-color); border-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.3);">
                            {{ __(':count Workers Punched', ['count' => $attendances->count()]) }}
                        </span>
                    </div>
                </div>

                {{-- Status Pills & Quick Filter Bar --}}
                <div class="px-3 py-2 bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2 text-xs">
                    {{-- Status Filter Tabs --}}
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <a href="{{ $here(['status' => 'all']) }}" 
                           class="px-2.5 py-1 rounded text-xs font-semibold transition {{ ($statusFilter ?? 'all') === 'all' ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-200/80 dark:bg-slate-700/80 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600' }}">
                            {{ __('All Workers (:count)', ['count' => $statusCounts['all'] ?? $roster->total()]) }}
                        </a>
                        <a href="{{ $here(['status' => 'present']) }}" 
                           class="px-2.5 py-1 rounded text-xs font-semibold transition {{ ($statusFilter ?? '') === 'present' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-emerald-100/70 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-200 dark:hover:bg-emerald-900/60' }}">
                            {{ __('Present (:count)', ['count' => $statusCounts['present'] ?? $metrics['total_present']]) }}
                        </a>
                        <a href="{{ $here(['status' => 'unpunched']) }}" 
                           class="px-2.5 py-1 rounded text-xs font-semibold transition {{ in_array(($statusFilter ?? ''), ['unpunched', 'absent'], true) ? 'bg-rose-600 text-white shadow-2xs' : 'bg-rose-100/70 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300 hover:bg-rose-200 dark:hover:bg-rose-900/60' }}">
                            {{ __('Absent / Not Punched (:count)', ['count' => $statusCounts['unpunched'] ?? 0]) }}
                        </a>
                    </div>

                    {{-- Quick Search & Project Filter --}}
                    <form method="GET" action="{{ route('workers.attendance.index') }}" class="flex items-center gap-2 flex-wrap">
                        <input type="hidden" name="view" value="day">
                        <input type="hidden" name="date" value="{{ $selectedDate->toDateString() }}">
                        @if(request('status') && request('status') !== 'all')
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif

                        <div class="relative">
                            <input type="search" name="search" value="{{ request('search') }}" 
                                   placeholder="{{ __('Search worker or phone…') }}" 
                                   class="h-7 text-xs px-2.5 py-0.5 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500 w-44">
                        </div>

                        <select name="project_id" onchange="this.form.submit()" class="h-7 text-xs px-2 py-0.5 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500 max-w-[180px]">
                            <option value="">{{ __('All Projects') }}</option>
                            @foreach($activeProjects as $p)
                                <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ Str::limit($p->title, 22) }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="h-7 px-2.5 text-xs font-semibold rounded bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 transition cursor-pointer">
                            {{ __('Filter') }}
                        </button>

                        @if(request('search') || request('project_id') || (request('status') && request('status') !== 'all'))
                            <a href="{{ route('workers.attendance.index', ['view' => 'day', 'date' => $selectedDate->toDateString()]) }}" class="text-[11px] text-indigo-600 hover:underline">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </form>
                </div>

                {{-- Table with proper flex scroll container --}}
                <div class="ld-scroll flex-1 overflow-auto min-h-0">
                    <table class="ld-table w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-100/90 dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px] sticky top-0 z-10">
                                <th class="py-2.5 px-3">{{ __('Worker (Profile Photo)') }}</th>
                                <th class="py-2.5 px-3">{{ __('Project / Site') }}</th>
                                <th class="py-2.5 px-3">{{ __('Check-in Time') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('Punch Photo') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('Visual Verification') }}</th>
                                <th class="py-2.5 px-3">{{ __('Shift Type') }}</th>
                                <th class="py-2.5 px-3 text-right">{{ __('Cabling (m)') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('Cameras') }}</th>
                                <th class="py-2.5 px-3 text-right">{{ __('Daily Wage') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('Status') }}</th>
                                <th class="py-2.5 px-3 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($roster as $item)
                                @php
                                    $w = $item->worker;
                                    $att = $item->attendance;
                                    $p = $item->project;
                                    $isPresent = $item->is_present;
                                @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors {{ !$isPresent ? 'bg-slate-50/30 dark:bg-slate-900/30' : '' }}">
                                    {{-- Worker Column with Master Profile Photo --}}
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-2.5">
                                            @if($w && $w->photo_url)
                                                <img src="{{ $w->photo_url }}" alt="{{ $w->name }}" 
                                                     class="w-9 h-9 rounded-full object-cover border border-slate-300 dark:border-slate-600 shrink-0 shadow-xs cursor-pointer hover:opacity-90" 
                                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($w->name) }}&background=6366f1&color=fff&size=72';"
                                                     onclick="showLightbox('{{ $w->name }}', '{{ $w->photo_url }}', '{{ $att?->photo_url ?? '' }}', '{{ $selectedDate->format('d M Y') }}')">
                                            @else
                                                <div class="w-9 h-9 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-300 dark:border-slate-600">
                                                    {{ strtoupper(mb_substr($w?->name ?? 'W', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    @if($w)
                                                        <a href="{{ route('workers.show', $w) }}" class="font-bold text-slate-900 dark:text-white hover:text-indigo-600 transition-colors">
                                                            {{ $w->name }}
                                                        </a>
                                                        @if(method_exists($w, 'trashed') && $w->trashed())
                                                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-medium" title="This worker profile is archived/deleted">{{ __('Archived') }}</span>
                                                        @endif
                                                        @if(!$w->photo_url && auth()->user()->isOffice())
                                                            <a href="{{ route('workers.edit', $w) }}" class="inline-flex items-center gap-0.5 text-[10px] text-indigo-500 hover:underline" title="Upload master profile photo"><x-icon.plus class="w-2.5 h-2.5 shrink-0" />{{ __('Photo') }}</a>
                                                        @endif
                                                    @else
                                                        <span class="font-bold text-slate-600 dark:text-slate-400">
                                                            {{ $item->worker_id ? 'Worker #' . $item->worker_id : 'Unknown Worker' }}
                                                        </span>
                                                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 font-medium">{{ __('Deleted') }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-500">
                                                    {{ $w?->phone ?? __('No phone') }}
                                                    @if($w?->daily_rate)
                                                        &bull; &#8377;{{ number_format($w->daily_rate, 0) }}/day
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Project Site --}}
                                    <td class="py-2.5 px-3">
                                        @if($isPresent && $p)
                                            <a href="{{ route('projects.show', $p) }}" class="font-medium text-slate-800 dark:text-slate-200 hover:text-indigo-600">
                                                {{ Str::limit($p->title, 24) }}
                                            </a>
                                            <div class="text-[11px] text-slate-500">{{ $p->site?->site_name ?? 'CCTV Site' }}</div>
                                        @elseif($isPresent)
                                            <span class="text-slate-400">{{ __('Direct Labor Shift') }}</span>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">{{ __('Not Assigned') }}</span>
                                        @endif
                                    </td>

                                    {{-- Check-in Time --}}
                                    <td class="py-2.5 px-3 nowrap text-slate-700 dark:text-slate-300">
                                        @if($isPresent && $att?->check_in_at)
                                            <div class="font-semibold">{{ $att->check_in_at->format('h:i A') }}</div>
                                            <div class="text-[10px] text-slate-400">{{ __('Punched in') }}</div>
                                        @elseif($isPresent)
                                            <span class="text-slate-400 text-xs">{{ __('Standard Day') }}</span>
                                        @else
                                            <span class="text-slate-400 text-xs">&mdash;</span>
                                        @endif
                                    </td>

                                    {{-- Punch Photo Column --}}
                                    <td class="py-2.5 px-3 text-center">
                                        @if($isPresent && $att?->photo_url)
                                            <button type="button" 
                                                    onclick="showLightbox('{{ $w?->name }}', '{{ $w?->photo_url }}', '{{ $att->photo_url }}', '{{ $selectedDate->format('d M Y') }}')"
                                                    class="inline-block relative group cursor-pointer focus:outline-none">
                                                <img src="{{ $att->photo_url }}" alt="Punch Selfie" 
                                                     class="w-10 h-10 rounded object-cover border border-indigo-200 dark:border-indigo-800 group-hover:ring-2 group-hover:ring-indigo-500 shadow-xs transition-all"
                                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Punch+Photo&background=4f46e5&color=fff&size=80';">
                                                <span class="absolute -top-1 -right-1 bg-indigo-600 text-white rounded-full p-0.5 text-[9px] shadow-sm">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </span>
                                            </button>
                                        @elseif($isPresent)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                                {{ __('No photo') }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-xs">&mdash;</span>
                                        @endif
                                    </td>

                                    {{-- Visual Verification Match Badge --}}
                                    <td class="py-2.5 px-3 text-center">
                                        @if($isPresent && $w?->photo_url && $att?->photo_url)
                                            <button type="button" 
                                                    onclick="showLightbox('{{ $w?->name }}', '{{ $w?->photo_url }}', '{{ $att->photo_url }}', '{{ $selectedDate->format('d M Y') }}')"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 hover:bg-emerald-200 transition-colors cursor-pointer">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>{{ __('Verify Both') }}</span>
                                            </button>
                                        @elseif($isPresent && $att?->photo_url)
                                            <button type="button"
                                                    onclick="showLightbox('{{ $w?->name }}', '{{ $w?->photo_url }}', '{{ $att->photo_url }}', '{{ $selectedDate->format('d M Y') }}')"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 hover:bg-amber-100 transition-colors cursor-pointer" title="Click to inspect punch photo">
                                                <span>{{ __('Punch only') }}</span>
                                            </button>
                                        @elseif($isPresent)
                                            <span class="text-[10px] text-slate-400">{{ __('Manual Entry') }}</span>
                                        @else
                                            <span class="text-slate-400 text-xs">&mdash;</span>
                                        @endif
                                    </td>

                                    {{-- Shift Type --}}
                                    <td class="py-2.5 px-3 nowrap">
                                        @if($isPresent && $att?->attendance_type === 'half')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                                {{ __('Half Day (0.5x)') }}
                                            </span>
                                        @elseif($isPresent)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                {{ __('Full Day (1.0x)') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-900">
                                                {{ __('Absent / Not Punched') }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Cabling --}}
                                    <td class="py-2.5 px-3 text-right font-medium text-slate-900 dark:text-white">
                                        @if($isPresent && $att?->cabling_metres > 0)
                                            <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ number_format($att->cabling_metres, 1) }}</span> m
                                        @elseif($isPresent)
                                            <span class="text-slate-400">&mdash;</span>
                                        @else
                                            <span class="text-slate-400">&mdash;</span>
                                        @endif
                                    </td>

                                    {{-- Cameras --}}
                                    <td class="py-2.5 px-3 text-center font-medium text-slate-900 dark:text-white">
                                        @if($isPresent && $att?->cameras_installed > 0)
                                            <span class="px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-bold rounded">
                                                {{ $att->cameras_installed }}
                                            </span>
                                        @elseif($isPresent)
                                            <span class="text-slate-400">&mdash;</span>
                                        @else
                                            <span class="text-slate-400">&mdash;</span>
                                        @endif
                                    </td>

                                    {{-- Wage Amount --}}
                                    <td class="py-2.5 px-3 text-right font-bold text-slate-900 dark:text-white">
                                        @if($isPresent && $att)
                                            &#8377;{{ number_format($att->total_amount, 2) }}
                                            @if($att->extra_amount > 0)
                                                <div class="text-[10px] text-emerald-600">+&#8377;{{ number_format($att->extra_amount, 2) }} extra</div>
                                            @endif
                                        @else
                                            <span class="text-slate-400 font-normal">&mdash;</span>
                                        @endif
                                    </td>

                                    {{-- Paid/Settled Status --}}
                                    <td class="py-2.5 px-3 text-center">
                                        @if($isPresent && $att?->is_paid)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                &#10003; {{ __('Paid') }}
                                            </span>
                                        @elseif($isPresent)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                                {{ __('Pending') }}
                                            </span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                                {{ __('Unpunched') }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="py-2.5 px-3 text-right nowrap">
                                        @if($isPresent && $att && !$att->is_paid && auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('workers.attendance.destroy', $att) }}" class="inline" data-confirm="Remove attendance record for {{ $w?->name ?? 'worker' }} on this date?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="danger-link text-xs p-0 bg-transparent border-0 cursor-pointer">
                                                    {{ __('Remove') }}
                                                </button>
                                            </form>
                                        @elseif(!$isPresent && $w)
                                            <button type="button" 
                                                    onclick="openAttendanceModal('{{ $selectedDate->toDateString() }}', {{ $w->id }})" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900 transition cursor-pointer shadow-2xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                <span>{{ __('+ Punch') }}</span>
                                            </button>
                                        @else
                                            <span class="text-slate-400 text-[11px]">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-8 text-slate-500">
                                        <div class="text-sm font-semibold">{{ __('No workers match current criteria for :date', ['date' => $selectedDate->format('d M Y')]) }}</div>
                                        <div class="text-xs text-slate-400 mt-1">{{ __('Click the buttons below to mark attendance or clear applied filters.') }}</div>
                                        <div class="mt-3 flex items-center justify-center gap-2">
                                            <button type="button" onclick="openAttendanceModal('{{ $selectedDate->toDateString() }}')" class="btn-primary text-xs">
                                                <x-icon.plus />
                                                <span>{{ __('Mark Attendance for :date', ['date' => $selectedDate->format('d M')]) }}</span>
                                            </button>
                                            @if(isset($unpunchedWorkers) && $unpunchedWorkers->isNotEmpty())
                                                <button type="button" onclick="openBulkAttendanceModal('{{ $selectedDate->toDateString() }}', {{ Js::from($unpunchedWorkers->pluck('id')->values()) }})" class="btn-secondary text-xs inline-flex items-center gap-1.5 whitespace-nowrap shrink-0">
                                                    <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                                                    <span>{{ __('Bulk Attendance') }}</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($attendances->isNotEmpty())
                            <tfoot>
                                <tr class="bg-slate-100/90 dark:bg-slate-800/90 font-bold text-slate-900 dark:text-white border-t-2 border-slate-300 dark:border-slate-700">
                                    <td colspan="6" class="py-2.5 px-3 text-right uppercase text-[11px] tracking-wider">{{ __('Day Totals:') }}</td>
                                    <td class="py-2.5 px-3 text-right text-indigo-600 dark:text-indigo-400">{{ number_format($metrics['total_cabling'], 1) }} m</td>
                                    <td class="py-2.5 px-3 text-center text-emerald-600 dark:text-emerald-400">{{ $metrics['total_cameras'] }}</td>
                                    <td class="py-2.5 px-3 text-right">&#8377;{{ number_format($metrics['total_labor_cost'], 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                {{-- Pagination / Records Footer for Large Datasets --}}
                <div class="ld-foot">
                    <div>
                        {{ __('Total Records') }} <strong>{{ $roster->total() }}</strong>
                        @if(($statusFilter ?? 'all') !== 'all')
                            <span class="text-xs text-slate-500">({{ ucfirst($statusFilter) }})</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4">
                        <form method="GET" action="{{ route('workers.attendance.index') }}">
                            @foreach(request()->except('per_page', 'page') as $k => $v)
                                @foreach((array) $v as $item)
                                    <input type="hidden" name="{{ is_array($v) ? $k . '[]' : $k }}" value="{{ $item }}">
                                @endforeach
                            @endforeach
                            <select name="per_page" onchange="this.form.submit()" aria-label="{{ __('Records Per Page') }}">
                                @foreach([10, 20, 50, 100] as $n)
                                    <option value="{{ $n }}" @selected(($perPage ?? 20) === $n)>{{ $n }} {{ __('Records Per Page') }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div class="flex items-center gap-1">
                            @if($roster->onFirstPage())
                                <span class="ld-page-arrow" aria-hidden="true"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></span>
                            @else
                                <a href="{{ $roster->previousPageUrl() }}" class="ld-page-arrow plain-link" aria-label="Previous page"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a>
                            @endif
                            <span class="px-1 text-xs">
                                @if($roster->total() > 0)
                                    <strong>{{ $roster->firstItem() }}</strong> {{ __('to') }} <strong>{{ $roster->lastItem() }}</strong>
                                @else
                                    0
                                @endif
                            </span>
                            @if($roster->hasMorePages())
                                <a href="{{ $roster->nextPageUrl() }}" class="ld-page-arrow plain-link" aria-label="Next page"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
                            @else
                                <span class="ld-page-arrow" aria-hidden="true"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        @elseif($viewMode === 'week')
            {{-- PER-WEEK MATRIX VIEW --}}
            <div class="ld-card overflow-hidden p-0 mb-6">
                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-sm text-slate-900 dark:text-white">{{ __('Weekly Attendance Matrix (Monday – Sunday)') }}</span>
                        <span class="text-xs text-slate-500 block sm:inline sm:ml-2">&bull; {{ __('Week:') }} {{ $weekStart->format('d M') }} {{ __('to') }} {{ $weekEnd->format('d M Y') }}</span>
                    </div>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('wages.index', ['week' => $weekStart->toDateString()]) }}" class="btn-primary text-xs py-1 px-2.5">
                        {{ __('Open Sunday Payday') }} &rarr;
                    </a>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="ld-table w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-100/70 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3 min-w-[200px]">{{ __('Worker Name') }}</th>
                                @foreach($weekDays as $wd)
                                    <th class="py-2.5 px-2 text-center {{ $wd['is_today'] ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-extrabold' : '' }}">
                                        <div>{{ __($wd['day_name']) }}</div>
                                        <div class="text-[10px] font-normal opacity-75">{{ substr($wd['date'], 8, 2) }}</div>
                                    </th>
                                @endforeach
                                <th class="py-2.5 px-3 text-center">{{ __('Days Worked') }}</th>
                                <th class="py-2.5 px-3 text-right">{{ __('Cabling (m)') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('Cameras') }}</th>
                                <th class="py-2.5 px-3 text-right">{{ __('Weekly Wage') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($matrixData as $row)
                                @php $w = $row['worker']; @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-2">
                                            @if($w?->photo_url)
                                                <img src="{{ $w->photo_url }}" alt="{{ $w->name }}" class="w-7 h-7 rounded-full object-cover border border-slate-300 dark:border-slate-600 shrink-0">
                                            @else
                                                <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-[11px] flex items-center justify-center shrink-0">
                                                    {{ strtoupper(mb_substr($w?->name ?? 'W', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                @if($w)
                                                    <a href="{{ route('workers.show', $w) }}" class="font-bold text-slate-900 dark:text-white hover:text-indigo-600">
                                                        {{ $w->name }}
                                                    </a>
                                                    @if(method_exists($w, 'trashed') && $w->trashed())
                                                        <span class="text-[9px] px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-medium">{{ __('Archived') }}</span>
                                                    @endif
                                                    <div class="text-[10px] text-slate-500">&#8377;{{ number_format($w->daily_rate, 2) }}/{{ __('day') }}</div>
                                                @else
                                                    <span class="font-bold text-slate-500">{{ __('Deleted Worker') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 7 Day Badges --}}
                                    @foreach($weekDays as $wd)
                                        @php
                                            $att = $row['days'][$wd['date']] ?? null;
                                        @endphp
                                        <td class="py-2.5 px-2 text-center {{ $wd['is_today'] ? 'bg-indigo-50/40 dark:bg-indigo-950/20' : '' }}">
                                            @if($att)
                                                <div class="inline-flex flex-col items-center">
                                                    @if($att->photo_url)
                                                        <button type="button" 
                                                                onclick="showLightbox('{{ $w->name }}', '{{ $w->photo_url }}', '{{ $att->photo_url }}', '{{ $wd['label'] }}')"
                                                                class="relative group cursor-pointer focus:outline-none mb-0.5" title="View Punch Selfie">
                                                            <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold {{ $att->attendance_type === 'half' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                                                                {{ $att->attendance_type === 'half' ? 'H' : 'P' }}                                                             </span>
                                                        </button>
                                                    @else
                                                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold {{ $att->attendance_type === 'half' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                                                            {{ $att->attendance_type === 'half' ? 'H' : 'P' }}
                                                        </span>
                                                    @endif

                                                    @if($att->cameras_installed > 0 || $att->cabling_metres > 0)
                                                        <span class="text-[9px] text-slate-500">
                                                            {{ $att->cameras_installed > 0 ? $att->cameras_installed . 'c' : '' }}
                                                            {{ $att->cabling_metres > 0 ? $att->cabling_metres . 'm' : '' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-slate-300 dark:text-slate-600">&ndash;</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="py-2.5 px-3 text-center font-bold text-slate-900 dark:text-white">
                                        {{ $row['total_shifts'] }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-medium text-indigo-600 dark:text-indigo-400">
                                        {{ number_format($row['total_cabling'], 1) }} m
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-medium text-emerald-600 dark:text-emerald-400">
                                        {{ $row['total_cameras'] }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-bold text-slate-900 dark:text-white">
                                        &#8377;{{ number_format($row['total_wages'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-8 text-slate-500">
                                        {{ __('No workers scheduled or logged for this week.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(!empty($matrixData))
                            <tfoot>
                                <tr class="bg-slate-100/90 dark:bg-slate-800/90 font-bold text-slate-900 dark:text-white border-t-2 border-slate-300 dark:border-slate-700">
                                    <td colspan="8" class="py-2.5 px-3 text-right uppercase text-[11px] tracking-wider">{{ __('Weekly Totals:') }}</td>
                                    <td class="py-2.5 px-3 text-center">{{ $metrics['total_shifts'] }}</td>
                                    <td class="py-2.5 px-3 text-right text-indigo-600 dark:text-indigo-400">{{ number_format($metrics['total_cabling'], 1) }} m</td>
                                    <td class="py-2.5 px-3 text-center text-emerald-600 dark:text-emerald-400">{{ $metrics['total_cameras'] }}</td>
                                    <td class="py-2.5 px-3 text-right">&#8377;{{ number_format($metrics['total_labor_cost'], 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

        @else
            {{-- PER-MONTH SUMMARY & TIMESHEET VIEW --}}
            <div class="ld-card overflow-hidden p-0 mb-6">
                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-sm text-slate-900 dark:text-white">{{ __('Monthly Worker Attendance & Productivity Timesheet') }}</span>
                        <span class="text-xs text-slate-500 block sm:inline sm:ml-2">&bull; {{ $monthStart->format('F Y') }}</span>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded border border-indigo-200 dark:border-indigo-800">
                        {{ __(':count Active Workers', ['count' => count($workerSummary)]) }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="ld-table w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-100/70 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3">{{ __('Worker Name') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('Full Days') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('Half Days') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('Total Shifts') }}</th>
                                <th class="py-2.5 px-3 text-right">{{ __('Total Cabling (m)') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('Total Cameras') }}</th>
                                <th class="py-2.5 px-3 text-right">{{ __('Gross Wages') }}</th>
                                <th class="py-2.5 px-3 text-right">{{ __('Paid / Settled') }}</th>
                                <th class="py-2.5 px-3 text-right">{{ __('Unpaid Balance') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('Monthly Shifts') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($workerSummary as $sum)
                                @php $w = $sum['worker']; @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-2">
                                            @if($w?->photo_url)
                                                <img src="{{ $w->photo_url }}" alt="{{ $w->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-300 dark:border-slate-600 shrink-0">
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center shrink-0">
                                                    {{ strtoupper(mb_substr($w?->name ?? 'W', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                @if($w)
                                                    <a href="{{ route('workers.show', $w) }}" class="font-bold text-slate-900 dark:text-white hover:text-indigo-600">
                                                        {{ $w->name }}
                                                    </a>
                                                    @if(method_exists($w, 'trashed') && $w->trashed())
                                                        <span class="text-[9px] px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-medium">{{ __('Archived') }}</span>
                                                    @endif
                                                    <div class="text-[10px] text-slate-500">&#8377;{{ number_format($w->daily_rate, 2) }}/{{ __('day') }}</div>
                                                @else
                                                    <span class="font-bold text-slate-500">{{ __('Deleted Worker') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-medium">{{ $sum['full_days'] }}</td>
                                    <td class="py-2.5 px-3 text-center font-medium">{{ $sum['half_days'] }}</td>
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-900 dark:text-white">{{ $sum['total_shifts'] }}</td>
                                    <td class="py-2.5 px-3 text-right font-medium text-indigo-600 dark:text-indigo-400">
                                        {{ number_format($sum['cabling'], 1) }} m
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-medium text-emerald-600 dark:text-emerald-400">
                                        {{ $sum['cameras'] }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-bold text-slate-900 dark:text-white">
                                        &#8377;{{ number_format($sum['gross_earned'], 2) }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-emerald-600 dark:text-emerald-400 font-medium">
                                        &#8377;{{ number_format($sum['paid_amount'], 2) }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-bold {{ $sum['unpaid_amount'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-500' }}">
                                        &#8377;{{ number_format($sum['unpaid_amount'], 2) }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <button type="button" onclick="toggleWorkerShifts({{ $w->id }})" class="btn-secondary py-0.5 px-2 text-[11px]">
                                            {{ __('View Logs (:count)', ['count' => $sum['attendances']->count()]) }}
                                        </button>
                                    </td>
                                </tr>
                                {{-- Expandable worker shifts breakdown row --}}
                                <tr id="worker-shifts-{{ $w->id }}" class="hidden bg-slate-50/50 dark:bg-slate-950/30">
                                    <td colspan="10" class="p-3">
                                        <div class="border border-slate-200 dark:border-slate-800 rounded bg-white dark:bg-slate-900 p-3 shadow-xs">
                                            <div class="font-bold text-xs text-slate-800 dark:text-slate-200 mb-2">
                                                {{ __('Detailed Shifts for :name — :month', ['name' => $w->name, 'month' => $monthStart->format('F Y')]) }}
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                                @foreach($sum['attendances'] as $sh)
                                                    @php
                                                        $shiftDate = is_string($sh->work_date) ? substr($sh->work_date, 0, 10) : $sh->work_date->format('Y-m-d');
                                                    @endphp
                                                    <div class="p-2 border border-slate-100 dark:border-slate-800 rounded flex items-center justify-between text-xs bg-slate-50/60 dark:bg-slate-800/40">
                                                        <div>
                                                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ \Carbon\Carbon::parse($shiftDate)->format('d M (D)') }}</div>
                                                            <div class="text-[10px] text-slate-500">{{ Str::limit($sh->workDay?->project?->title ?? 'Site', 18) }}</div>
                                                            <div class="text-[10px] font-bold text-slate-700 dark:text-slate-300">&#8377;{{ number_format($sh->total_amount, 2) }} &bull; {{ ucfirst($sh->attendance_type) }}</div>
                                                        </div>
                                                        @if($sh->photo_url)
                                                            <img src="{{ $sh->photo_url }}" alt="Selfie" class="w-8 h-8 rounded object-cover border border-slate-300 dark:border-slate-600 cursor-pointer" onclick="showLightbox('{{ $w->name }}', '{{ $w->photo_url }}', '{{ $sh->photo_url }}', '{{ $shiftDate }}')">
                                                        @else
                                                            <span class="text-[10px] text-slate-400">{{ __('No photo') }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-8 text-slate-500">
                                        {{ __('No monthly records found for :month.', ['month' => $monthStart->format('F Y')]) }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(!empty($workerSummary))
                            <tfoot>
                                <tr class="bg-slate-100/90 dark:bg-slate-800/90 font-bold text-slate-900 dark:text-white border-t-2 border-slate-300 dark:border-slate-700">
                                    <td colspan="3" class="py-2.5 px-3 text-right uppercase text-[11px] tracking-wider">{{ __('Month Totals:') }}</td>
                                    <td class="py-2.5 px-3 text-center">{{ $metrics['total_shifts'] }}</td>
                                    <td class="py-2.5 px-3 text-right text-indigo-600 dark:text-indigo-400">{{ number_format($metrics['total_cabling'], 1) }} m</td>
                                    <td class="py-2.5 px-3 text-center text-emerald-600 dark:text-emerald-400">{{ $metrics['total_cameras'] }}</td>
                                    <td class="py-2.5 px-3 text-right">&#8377;{{ number_format($metrics['total_gross'], 2) }}</td>
                                    <td class="py-2.5 px-3 text-right text-emerald-600 dark:text-emerald-400">&#8377;{{ number_format($metrics['total_paid'], 2) }}</td>
                                    <td class="py-2.5 px-3 text-right {{ $metrics['total_unpaid'] > 0 ? 'text-red-600' : '' }}">&#8377;{{ number_format($metrics['total_unpaid'], 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        @endif
    </div>

    
            </section>
        </div>
    </div>
{{-- SIDE-BY-SIDE VISUAL VERIFICATION LIGHTBOX MODAL --}}
    <div id="photoLightboxModal" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 hidden">
        <div class="bg-white dark:bg-slate-900 rounded-lg max-w-2xl w-full p-5 shadow-2xl border border-slate-200 dark:border-slate-800 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>{{ __('Visual Identity Verification') }}</span>
                        <span class="text-xs font-normal text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">&#10003; {{ __('Dual Photo Inspection') }}</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5" id="lightbox-worker-title">Worker Name</p>
                </div>
                <x-close-button onclick="closeLightbox()" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 my-4">
                {{-- Master Profile Photo --}}
                <div class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded border border-slate-200 dark:border-slate-700 text-center">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-2 flex items-center justify-center gap-1">
                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>{{ __('Registered Profile Photo') }}</span>
                    </div>
                    <div class="w-full h-56 bg-slate-200 dark:bg-slate-800 rounded flex items-center justify-center overflow-hidden border border-slate-300 dark:border-slate-600">
                        <img id="lightbox-profile-img" alt="Profile Photo" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); document.getElementById('lightbox-profile-empty').classList.remove('hidden');">
                        <span id="lightbox-profile-empty" class="text-xs text-slate-400 hidden">{{ __('No master profile photo registered') }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2">{{ __('Master worker portrait on file') }}</p>
                </div>

                {{-- Daily Attendance Punch Photo --}}
                <div class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded border border-slate-200 dark:border-slate-700 text-center">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-2 flex items-center justify-center gap-1">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ __('Daily Punch Photo') }}</span>
                    </div>
                    <div class="w-full h-56 bg-slate-200 dark:bg-slate-800 rounded flex items-center justify-center overflow-hidden border border-slate-300 dark:border-slate-600">
                        <img id="lightbox-punch-img" alt="Punch Photo" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); document.getElementById('lightbox-punch-empty').classList.remove('hidden');">
                        <span id="lightbox-punch-empty" class="text-xs text-slate-400 hidden">{{ __('No punch photo recorded') }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2" id="lightbox-punch-date">{{ __('Taken on shift check-in') }}</p>
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="closeLightbox()" class="btn-secondary text-xs">{{ __('Close') }}</button>
            </div>
        </div>
    </div>

    {{-- TWO-STEP SPLIT GUIDED ATTENDANCE MODAL COMPONENT --}}
    <x-mark-attendance-modal :workers="$activeWorkers" :projects="$activeProjects" :selected-date="$viewMode === 'day' ? $selectedDate->toDateString() : now()->toDateString()" />

    {{-- Interactive JavaScript for Lightbox & View Helpers --}}
    <script>
        function showLightbox(workerName, profileUrl, punchUrl, dateStr) {
            document.getElementById('lightbox-worker-title').textContent = workerName + ' • ' + dateStr;
            
            const profileImg = document.getElementById('lightbox-profile-img');
            const profileEmpty = document.getElementById('lightbox-profile-empty');
            if (profileUrl && profileUrl !== '') {
                profileImg.src = profileUrl;
                profileImg.classList.remove('hidden');
                profileEmpty.classList.add('hidden');
            } else {
                profileImg.classList.add('hidden');
                profileEmpty.classList.remove('hidden');
            }

            const punchImg = document.getElementById('lightbox-punch-img');
            const punchEmpty = document.getElementById('lightbox-punch-empty');
            if (punchUrl && punchUrl !== '') {
                punchImg.src = punchUrl;
                punchImg.classList.remove('hidden');
                punchEmpty.classList.add('hidden');
            } else {
                punchImg.classList.add('hidden');
                punchEmpty.classList.remove('hidden');
            }

            document.getElementById('lightbox-punch-date').textContent = 'Punched on: ' + dateStr;
            document.getElementById('photoLightboxModal').classList.remove('hidden');
        }

        function closeLightbox() {
            document.getElementById('photoLightboxModal').classList.add('hidden');
        }

        function toggleWorkerShifts(workerId) {
            const row = document.getElementById('worker-shifts-' + workerId);
            if (row) {
                row.classList.toggle('hidden');
            }
        }
    </script>
</x-app-layout>