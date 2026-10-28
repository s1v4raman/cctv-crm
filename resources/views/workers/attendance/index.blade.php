<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('workers.index') }}" class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300 transition-colors">&larr; Workers</a>
                    <span class="text-slate-300 dark:text-slate-600">/</span>
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Attendance</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">Worker Attendance Maintenance</h2>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" onclick="openAttendanceModal()" class="btn-primary flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Mark Attendance</span>
                </button>
                <a href="{{ route('wages.index') }}" class="btn-secondary">
                    Sunday Payday Console
                </a>
                <a href="{{ route('workers.index') }}" class="btn-secondary">
                    Workers List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-[1300px] mx-auto space-y-5 py-4 px-2 sm:px-4">
        @if (session('status'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded text-xs flex items-center justify-between shadow-sm">
                <span>{{ session('status') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 font-bold ml-2">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-red-500 text-red-700 dark:text-red-300 rounded text-xs flex items-center justify-between shadow-sm">
                <span>{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-800 font-bold ml-2">&times;</button>
            </div>
        @endif

        {{-- Top Navigation Bar: View Mode Switcher + Date Navigation + Filters --}}
        <div class="crm-section p-3 shadow-sm bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 space-y-3">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                {{-- View Mode Pills --}}
                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 self-start">
                    <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'day'])) }}"
                       class="px-3.5 py-1.5 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5 {{ $viewMode === 'day' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Per-Day (Daily Roster)</span>
                    </a>
                    <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'week'])) }}"
                       class="px-3.5 py-1.5 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5 {{ $viewMode === 'week' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        <span>Per-Week (Weekly Matrix)</span>
                    </a>
                    <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'month'])) }}"
                       class="px-3.5 py-1.5 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5 {{ $viewMode === 'month' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Per-Month (Monthly Timesheet)</span>
                    </a>
                </div>

                {{-- Date / Period Selector with Prev/Next buttons --}}
                <div class="flex items-center gap-1.5 flex-wrap">
                    @if($viewMode === 'day')
                        @php
                            $prevDay = $selectedDate->copy()->subDay()->toDateString();
                            $nextDay = $selectedDate->copy()->addDay()->toDateString();
                            $isToday = $selectedDate->isToday();
                        @endphp
                        <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'day', 'date' => $prevDay])) }}" class="btn-secondary py-1 px-2.5 text-xs" title="Previous Day">&larr; Prev</a>
                        
                        <form method="GET" action="{{ route('workers.attendance.index') }}" class="inline-flex items-center gap-1">
                            <input type="hidden" name="view" value="day">
                            @if(request('worker_id')) <input type="hidden" name="worker_id" value="{{ request('worker_id') }}"> @endif
                            @if(request('project_id')) <input type="hidden" name="project_id" value="{{ request('project_id') }}"> @endif
                            <input type="date" name="date" value="{{ $selectedDate->toDateString() }}" onchange="this.form.submit()" class="crm-input py-1 px-2 text-xs font-semibold rounded w-36">
                        </form>

                        <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'day', 'date' => $nextDay])) }}" class="btn-secondary py-1 px-2.5 text-xs" title="Next Day">Next &rarr;</a>

                        @if(!$isToday)
                            <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'day', 'date' => now()->toDateString()])) }}" class="btn-secondary py-1 px-2 text-[11px]">Today</a>
                        @endif
                    @elseif($viewMode === 'week')
                        @php
                            $prevWeek = $weekStart->copy()->subWeek()->toDateString();
                            $nextWeek = $weekStart->copy()->addWeek()->toDateString();
                            $isCurrentWeek = $weekStart->isCurrentWeek();
                        @endphp
                        <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'week', 'week' => $prevWeek])) }}" class="btn-secondary py-1 px-2.5 text-xs">&larr; Prev Week</a>
                        <span class="text-xs font-bold px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700">
                            {{ $weekStart->format('d M') }} &ndash; {{ $weekEnd->format('d M Y') }}
                        </span>
                        <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'week', 'week' => $nextWeek])) }}" class="btn-secondary py-1 px-2.5 text-xs">Next Week &rarr;</a>
                        @if(!$isCurrentWeek)
                            <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'week', 'week' => now()->startOfWeek(\Carbon\Constants\UnitValue::MONDAY)->toDateString()])) }}" class="btn-secondary py-1 px-2 text-[11px]">Current Week</a>
                        @endif
                    @else
                        @php
                            $prevMonth = $monthStart->copy()->subMonth()->format('Y-m');
                            $nextMonth = $monthStart->copy()->addMonth()->format('Y-m');
                            $isCurrentMonth = $monthStart->isCurrentMonth();
                        @endphp
                        <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'month', 'month' => $prevMonth])) }}" class="btn-secondary py-1 px-2.5 text-xs">&larr; Prev Month</a>
                        <form method="GET" action="{{ route('workers.attendance.index') }}" class="inline-flex items-center gap-1">
                            <input type="hidden" name="view" value="month">
                            @if(request('worker_id')) <input type="hidden" name="worker_id" value="{{ request('worker_id') }}"> @endif
                            @if(request('project_id')) <input type="hidden" name="project_id" value="{{ request('project_id') }}"> @endif
                            <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()" class="crm-input py-1 px-2 text-xs font-semibold rounded w-36">
                        </form>
                        <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'month', 'month' => $nextMonth])) }}" class="btn-secondary py-1 px-2.5 text-xs">Next Month &rarr;</a>
                        @if(!$isCurrentMonth)
                            <a href="{{ route('workers.attendance.index', array_merge(request()->query(), ['view' => 'month', 'month' => now()->format('Y-m')])) }}" class="btn-secondary py-1 px-2 text-[11px]">Current Month</a>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Filter Inputs Row --}}
            <form method="GET" action="{{ route('workers.attendance.index') }}" class="flex items-center gap-2 flex-wrap pt-2 border-t border-slate-100 dark:border-slate-800">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                @if($viewMode === 'day')
                    <input type="hidden" name="date" value="{{ $selectedDate->toDateString() }}">
                @elseif($viewMode === 'week')
                    <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                @else
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                @endif

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search worker name, phone or notes…" 
                       class="crm-input flex-1 min-w-[200px] text-xs py-1.5">

                <select name="worker_id" class="crm-select min-w-[160px] text-xs py-1.5" onchange="this.form.submit()">
                    <option value="">All Workers</option>
                    @foreach($activeWorkers as $w)
                        <option value="{{ $w->id }}" @selected(request('worker_id') == $w->id)>{{ $w->name }}</option>
                    @endforeach
                </select>

                <select name="project_id" class="crm-select min-w-[180px] text-xs py-1.5" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($activeProjects as $p)
                        <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ Str::limit($p->title, 26) }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn-primary py-1.5 px-3 text-xs">Filter</button>
                @if(request()->hasAny(['search', 'worker_id', 'project_id']))
                    <a href="{{ route('workers.attendance.index', ['view' => $viewMode]) }}" class="btn-secondary py-1.5 px-2.5 text-xs">Reset</a>
                @endif
            </form>
        </div>

        {{-- Metrics KPI Banner --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @if($viewMode === 'day')
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Workers Present</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $metrics['total_present'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $metrics['full_day_count'] }} Full &bull; {{ $metrics['half_day_count'] }} Half</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Cabling Run</div>
                    <div class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ number_format($metrics['total_cabling'], 1) }} <span class="text-xs font-normal text-slate-500">m</span></div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Physical cable meters</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Cameras Mounted</div>
                    <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $metrics['total_cameras'] }} <span class="text-xs font-normal text-slate-500">units</span></div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Installed today</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Total Labor Wage</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">&#8377;{{ number_format($metrics['total_labor_cost'], 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Daily wages incurred</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Photos Logged</div>
                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $attendances->whereNotNull('photo_path')->count() }} / {{ $metrics['total_present'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">With selfie/punch photo</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Total Active Pool</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $activeWorkers->count() }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Registered workers</div>
                </div>
            @elseif($viewMode === 'week')
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Weekly Shifts</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $metrics['total_shifts'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Effective worker days</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Workers Deployed</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $metrics['workers_count'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">On weekly sites</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Weekly Cabling</div>
                    <div class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ number_format($metrics['total_cabling'], 1) }} <span class="text-xs font-normal text-slate-500">m</span></div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Total cable pulled</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Weekly Cameras</div>
                    <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $metrics['total_cameras'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Mounted this week</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Gross Labor Cost</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">&#8377;{{ number_format($metrics['total_labor_cost'], 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Total weekly payable</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm flex flex-col justify-center items-start">
                    <a href="{{ route('wages.index', ['week' => $weekStart->toDateString()]) }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        <span>Open Sunday Payday &rarr;</span>
                    </a>
                    <div class="text-[11px] text-slate-500 mt-1">Settle weekly disbursements</div>
                </div>
            @else
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Monthly Shifts</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $metrics['total_shifts'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Worker days logged</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Monthly Cabling</div>
                    <div class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ number_format($metrics['total_cabling'], 1) }} <span class="text-xs font-normal text-slate-500">m</span></div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Total metres installed</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Monthly Cameras</div>
                    <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $metrics['total_cameras'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Cameras configured</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Gross Labor Cost</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">&#8377;{{ number_format($metrics['total_gross'], 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Monthly wages incurred</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Settled / Paid</div>
                    <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">&#8377;{{ number_format($metrics['total_paid'], 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Cleared disbursements</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-3 shadow-sm">
                    <div class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Outstanding</div>
                    <div class="text-xl font-bold {{ $metrics['total_unpaid'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }} mt-1">&#8377;{{ number_format($metrics['total_unpaid'], 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Pending settlement</div>
                </div>
            @endif
        </div>

        {{-- Main Table View Depending on View Mode --}}
        @if($viewMode === 'day')
            {{-- PER-DAY VIEW --}}
            <div class="crm-section overflow-hidden p-0 bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm text-slate-900 dark:text-white">Daily Attendance Roster & Photo Verification</span>
                        <span class="text-xs text-slate-500">&bull; {{ $selectedDate->format('l, d F Y') }}</span>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded border border-indigo-200 dark:border-indigo-800">
                        {{ $attendances->count() }} Workers Punched
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="crm-table w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100/70 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3">Worker (Profile Photo)</th>
                                <th class="py-2.5 px-3">Project / Site</th>
                                <th class="py-2.5 px-3">Check-in Time</th>
                                <th class="py-2.5 px-3 text-center">Punch Photo</th>
                                <th class="py-2.5 px-3 text-center">Visual Verification</th>
                                <th class="py-2.5 px-3">Shift Type</th>
                                <th class="py-2.5 px-3 text-right">Cabling (m)</th>
                                <th class="py-2.5 px-3 text-center">Cameras</th>
                                <th class="py-2.5 px-3 text-right">Daily Wage</th>
                                <th class="py-2.5 px-3 text-center">Status</th>
                                <th class="py-2.5 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($attendances as $att)
                                @php
                                    $w = $att->worker;
                                    $p = $att->workDay?->project;
                                @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                                    {{-- Worker Column with Master Profile Photo --}}
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-2.5">
                                            @if($w && $w->photo_url)
                                                <img src="{{ $w->photo_url }}" alt="{{ $w->name }}" 
                                                     class="w-9 h-9 rounded-full object-cover border border-slate-300 dark:border-slate-600 shrink-0 shadow-xs cursor-pointer hover:opacity-90" 
                                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($w->name) }}&background=6366f1&color=fff&size=72';"
                                                     onclick="showLightbox('{{ $w->name }}', '{{ $w->photo_url }}', '{{ $att->photo_url }}', '{{ $selectedDate->format('d M Y') }}')">
                                            @else
                                                <div class="w-9 h-9 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-300 dark:border-slate-600">
                                                    {{ strtoupper(substr($w?->name ?? 'W', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    @if($w)
                                                        <a href="{{ route('workers.show', $w) }}" class="font-bold text-slate-900 dark:text-white hover:text-indigo-600 transition-colors">
                                                            {{ $w->name }}
                                                        </a>
                                                        @if(method_exists($w, 'trashed') && $w->trashed())
                                                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-medium" title="This worker profile is archived/deleted">Archived</span>
                                                        @endif
                                                        @if(!$w->photo_url && auth()->user()->isAdmin())
                                                            <a href="{{ route('workers.edit', $w) }}" class="text-[10px] text-indigo-500 hover:underline" title="Upload master profile photo">+ Photo</a>
                                                        @endif
                                                    @else
                                                        <span class="font-bold text-slate-600 dark:text-slate-400">
                                                            {{ $att->worker_id ? 'Worker #' . $att->worker_id : 'Unknown Worker' }}
                                                        </span>
                                                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 font-medium">Deleted</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-500">{{ $w?->phone ?? 'No phone' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Project Site --}}
                                    <td class="py-2.5 px-3">
                                        @if($p)
                                            <a href="{{ route('projects.show', $p) }}" class="font-medium text-slate-800 dark:text-slate-200 hover:text-indigo-600">
                                                {{ Str::limit($p->title, 24) }}
                                            </a>
                                            <div class="text-[11px] text-slate-500">{{ $p->site?->site_name ?? 'CCTV Site' }}</div>
                                        @else
                                            <span class="text-slate-400">Direct Labor Shift</span>
                                        @endif
                                    </td>

                                    {{-- Check-in Time --}}
                                    <td class="py-2.5 px-3 nowrap text-slate-700 dark:text-slate-300">
                                        @if($att->check_in_at)
                                            <div class="font-semibold">{{ $att->check_in_at->format('h:i A') }}</div>
                                            <div class="text-[10px] text-slate-400">Punched in</div>
                                        @else
                                            <span class="text-slate-400 text-xs">Standard Day</span>
                                        @endif
                                    </td>

                                    {{-- Punch Photo Column --}}
                                    <td class="py-2.5 px-3 text-center">
                                        @if($att->photo_url)
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
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                                No photo
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Visual Verification Match Badge --}}
                                    <td class="py-2.5 px-3 text-center">
                                        @if($w?->photo_url && $att->photo_url)
                                            <button type="button" 
                                                    onclick="showLightbox('{{ $w?->name }}', '{{ $w?->photo_url }}', '{{ $att->photo_url }}', '{{ $selectedDate->format('d M Y') }}')"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 hover:bg-emerald-200 transition-colors cursor-pointer">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Verify Both</span>
                                            </button>
                                        @elseif($att->photo_url)
                                            <button type="button"
                                                    onclick="showLightbox('{{ $w?->name }}', '{{ $w?->photo_url }}', '{{ $att->photo_url }}', '{{ $selectedDate->format('d M Y') }}')"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 hover:bg-amber-100 transition-colors cursor-pointer" title="Click to inspect punch photo">
                                                <span>Punch only</span>
                                            </button>
                                        @else
                                            <span class="text-[10px] text-slate-400">Manual Entry</span>
                                        @endif
                                    </td>

                                    {{-- Shift Type --}}
                                    <td class="py-2.5 px-3 nowrap">
                                        @if($att->attendance_type === 'half')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                                Half Day (0.5x)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                Full Day (1.0x)
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Cabling --}}
                                    <td class="py-2.5 px-3 text-right font-medium text-slate-900 dark:text-white">
                                        @if($att->cabling_metres > 0)
                                            <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ number_format($att->cabling_metres, 1) }}</span> m
                                        @else
                                            <span class="text-slate-400">&mdash;</span>
                                        @endif
                                    </td>

                                    {{-- Cameras --}}
                                    <td class="py-2.5 px-3 text-center font-medium text-slate-900 dark:text-white">
                                        @if($att->cameras_installed > 0)
                                            <span class="px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-bold rounded">
                                                {{ $att->cameras_installed }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">&mdash;</span>
                                        @endif
                                    </td>

                                    {{-- Wage Amount --}}
                                    <td class="py-2.5 px-3 text-right font-bold text-slate-900 dark:text-white">
                                        &#8377;{{ number_format($att->total_amount, 2) }}
                                        @if($att->extra_amount > 0)
                                            <div class="text-[10px] text-emerald-600">+&#8377;{{ number_format($att->extra_amount, 2) }} extra</div>
                                        @endif
                                    </td>

                                    {{-- Paid/Settled Status --}}
                                    <td class="py-2.5 px-3 text-center">
                                        @if($att->is_paid)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                &#10003; Paid
                                            </span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                                Pending
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="py-2.5 px-3 text-right nowrap">
                                        @if(!$att->is_paid && auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('workers.attendance.destroy', $att) }}" class="inline" onsubmit="return confirm('Remove attendance record for {{ addslashes($w?->name ?? 'worker') }} on this date?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="danger-link text-xs p-0 bg-transparent border-0 cursor-pointer">
                                                    Remove
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-400 text-[11px]">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-8 text-slate-500">
                                        <div class="text-sm font-semibold">No worker attendances logged for {{ $selectedDate->format('d M Y') }}</div>
                                        <div class="text-xs text-slate-400 mt-1">Click the button below to mark attendance with photo or webcam.</div>
                                        <button type="button" onclick="openAttendanceModal('{{ $selectedDate->toDateString() }}')" class="btn-primary mt-3 text-xs">
                                            + Mark Attendance for {{ $selectedDate->format('d M') }}
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($attendances->isNotEmpty())
                            <tfoot>
                                <tr class="bg-slate-100/90 dark:bg-slate-800/90 font-bold text-slate-900 dark:text-white border-t-2 border-slate-300 dark:border-slate-700">
                                    <td colspan="6" class="py-2.5 px-3 text-right uppercase text-[11px] tracking-wider">Day Totals:</td>
                                    <td class="py-2.5 px-3 text-right text-indigo-600 dark:text-indigo-400">{{ number_format($metrics['total_cabling'], 1) }} m</td>
                                    <td class="py-2.5 px-3 text-center text-emerald-600 dark:text-emerald-400">{{ $metrics['total_cameras'] }}</td>
                                    <td class="py-2.5 px-3 text-right">&#8377;{{ number_format($metrics['total_labor_cost'], 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

        @elseif($viewMode === 'week')
            {{-- PER-WEEK MATRIX VIEW --}}
            <div class="crm-section overflow-hidden p-0 bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-sm text-slate-900 dark:text-white">Weekly Attendance Matrix (Monday &ndash; Sunday)</span>
                        <span class="text-xs text-slate-500 block sm:inline sm:ml-2">&bull; Week: {{ $weekStart->format('d M') }} to {{ $weekEnd->format('d M Y') }}</span>
                    </div>
                    <a href="{{ route('wages.index', ['week' => $weekStart->toDateString()]) }}" class="btn-primary text-xs py-1 px-2.5">
                        Open Sunday Payday &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="crm-table w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100/70 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3 min-w-[200px]">Worker Name</th>
                                @foreach($weekDays as $wd)
                                    <th class="py-2.5 px-2 text-center {{ $wd['is_today'] ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-extrabold' : '' }}">
                                        <div>{{ $wd['day_name'] }}</div>
                                        <div class="text-[10px] font-normal opacity-75">{{ substr($wd['date'], 8, 2) }}</div>
                                    </th>
                                @endforeach
                                <th class="py-2.5 px-3 text-center">Days Worked</th>
                                <th class="py-2.5 px-3 text-right">Cabling (m)</th>
                                <th class="py-2.5 px-3 text-center">Cameras</th>
                                <th class="py-2.5 px-3 text-right">Weekly Wage</th>
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
                                                    {{ strtoupper(substr($w?->name ?? 'W', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                @if($w)
                                                    <a href="{{ route('workers.show', $w) }}" class="font-bold text-slate-900 dark:text-white hover:text-indigo-600">
                                                        {{ $w->name }}
                                                    </a>
                                                    @if(method_exists($w, 'trashed') && $w->trashed())
                                                        <span class="text-[9px] px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-medium">Archived</span>
                                                    @endif
                                                    <div class="text-[10px] text-slate-500">&#8377;{{ number_format($w->daily_rate, 2) }}/day</div>
                                                @else
                                                    <span class="font-bold text-slate-500">Deleted Worker</span>
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
                                                                {{ $att->attendance_type === 'half' ? 'H' : 'P' }} 📷
                                                            </span>
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
                                        No workers scheduled or logged for this week.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(!empty($matrixData))
                            <tfoot>
                                <tr class="bg-slate-100/90 dark:bg-slate-800/90 font-bold text-slate-900 dark:text-white border-t-2 border-slate-300 dark:border-slate-700">
                                    <td colspan="8" class="py-2.5 px-3 text-right uppercase text-[11px] tracking-wider">Weekly Totals:</td>
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
            <div class="crm-section overflow-hidden p-0 bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-sm text-slate-900 dark:text-white">Monthly Worker Attendance & Productivity Timesheet</span>
                        <span class="text-xs text-slate-500 block sm:inline sm:ml-2">&bull; {{ $monthStart->format('F Y') }}</span>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded border border-indigo-200 dark:border-indigo-800">
                        {{ count($workerSummary) }} Active Workers
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="crm-table w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100/70 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3">Worker Name</th>
                                <th class="py-2.5 px-3 text-center">Full Days</th>
                                <th class="py-2.5 px-3 text-center">Half Days</th>
                                <th class="py-2.5 px-3 text-center">Total Shifts</th>
                                <th class="py-2.5 px-3 text-right">Total Cabling (m)</th>
                                <th class="py-2.5 px-3 text-center">Total Cameras</th>
                                <th class="py-2.5 px-3 text-right">Gross Wages</th>
                                <th class="py-2.5 px-3 text-right">Paid / Settled</th>
                                <th class="py-2.5 px-3 text-right">Unpaid Balance</th>
                                <th class="py-2.5 px-3 text-center">Monthly Shifts</th>
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
                                                    {{ strtoupper(substr($w?->name ?? 'W', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                @if($w)
                                                    <a href="{{ route('workers.show', $w) }}" class="font-bold text-slate-900 dark:text-white hover:text-indigo-600">
                                                        {{ $w->name }}
                                                    </a>
                                                    @if(method_exists($w, 'trashed') && $w->trashed())
                                                        <span class="text-[9px] px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-medium">Archived</span>
                                                    @endif
                                                    <div class="text-[10px] text-slate-500">&#8377;{{ number_format($w->daily_rate, 2) }}/day</div>
                                                @else
                                                    <span class="font-bold text-slate-500">Deleted Worker</span>
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
                                            View Logs ({{ $sum['attendances']->count() }})
                                        </button>
                                    </td>
                                </tr>
                                {{-- Expandable worker shifts breakdown row --}}
                                <tr id="worker-shifts-{{ $w->id }}" class="hidden bg-slate-50/50 dark:bg-slate-950/30">
                                    <td colspan="10" class="p-3">
                                        <div class="border border-slate-200 dark:border-slate-800 rounded bg-white dark:bg-slate-900 p-3 shadow-xs">
                                            <div class="font-bold text-xs text-slate-800 dark:text-slate-200 mb-2">
                                                Detailed Shifts for {{ $w->name }} &mdash; {{ $monthStart->format('F Y') }}
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
                                                            <span class="text-[10px] text-slate-400">No pic</span>
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
                                        No monthly records found for {{ $monthStart->format('F Y') }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(!empty($workerSummary))
                            <tfoot>
                                <tr class="bg-slate-100/90 dark:bg-slate-800/90 font-bold text-slate-900 dark:text-white border-t-2 border-slate-300 dark:border-slate-700">
                                    <td colspan="3" class="py-2.5 px-3 text-right uppercase text-[11px] tracking-wider">Month Totals:</td>
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

    {{-- SIDE-BY-SIDE VISUAL VERIFICATION LIGHTBOX MODAL --}}
    <div id="photoLightboxModal" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 hidden">
        <div class="bg-white dark:bg-slate-900 rounded-lg max-w-2xl w-full p-5 shadow-2xl border border-slate-200 dark:border-slate-800 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>Visual Identity Verification</span>
                        <span class="text-xs font-normal text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">&#10003; Dual Photo Inspection</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5" id="lightbox-worker-title">Worker Name</p>
                </div>
                <button type="button" onclick="closeLightbox()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 my-4">
                {{-- Master Profile Photo --}}
                <div class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded border border-slate-200 dark:border-slate-700 text-center">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-2 flex items-center justify-center gap-1">
                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Registered Profile Photo</span>
                    </div>
                    <div class="w-full h-56 bg-slate-200 dark:bg-slate-800 rounded flex items-center justify-center overflow-hidden border border-slate-300 dark:border-slate-600">
                        <img id="lightbox-profile-img" src="#" alt="Profile Photo" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); document.getElementById('lightbox-profile-empty').classList.remove('hidden');">
                        <span id="lightbox-profile-empty" class="text-xs text-slate-400 hidden">No master profile photo registered</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2">Master worker portrait on file</p>
                </div>

                {{-- Daily Attendance Punch Photo --}}
                <div class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded border border-slate-200 dark:border-slate-700 text-center">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-2 flex items-center justify-center gap-1">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Daily Punch Photo</span>
                    </div>
                    <div class="w-full h-56 bg-slate-200 dark:bg-slate-800 rounded flex items-center justify-center overflow-hidden border border-slate-300 dark:border-slate-600">
                        <img id="lightbox-punch-img" src="#" alt="Punch Photo" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); document.getElementById('lightbox-punch-empty').classList.remove('hidden');">
                        <span id="lightbox-punch-empty" class="text-xs text-slate-400 hidden">No punch photo recorded</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2" id="lightbox-punch-date">Taken on shift check-in</p>
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="closeLightbox()" class="btn-secondary text-xs">Close</button>
            </div>
        </div>
    </div>

    {{-- MARK ATTENDANCE MODAL WITH FILE UPLOAD & WEBCAM CAPTURE --}}
    <div id="markAttendanceModal" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4 hidden overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 rounded-lg max-w-lg w-full p-5 shadow-2xl border border-slate-200 dark:border-slate-800 my-8">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Mark Worker Attendance</h3>
                    <p class="text-xs text-slate-500">Record daily shift with photo verification & work output metrics.</p>
                </div>
                <button type="button" onclick="closeAttendanceModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('workers.attendance.store') }}" enctype="multipart/form-data" class="space-y-4 mt-4">
                @csrf
                <input type="hidden" name="photo_data" id="webcam-photo-data">

                {{-- Worker & Project Pickers --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="crm-label text-xs">Worker <span class="required-marker">*</span></label>
                        <select name="worker_id" id="modal-worker-select" onchange="onWorkerSelectChange(this)" required class="crm-select text-xs w-full">
                            <option value="">Select Worker...</option>
                            @foreach($activeWorkers as $w)
                                <option value="{{ $w->id }}" data-photo="{{ $w->photo_url ?? '' }}" data-name="{{ $w->name }}" @selected(request('worker_id') == $w->id)>
                                    {{ $w->name }} (&#8377;{{ number_format($w->daily_rate, 0) }})
                                </option>
                            @endforeach
                        </select>
                        <div id="modal-worker-preview-box" class="flex items-center gap-2 mt-1.5 p-1.5 rounded bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hidden">
                            <img id="modal-worker-img" src="#" alt="Worker" class="w-6 h-6 rounded-full object-cover border border-slate-300">
                            <span class="text-[11px] text-slate-700 dark:text-slate-300 font-semibold" id="modal-worker-name-preview"></span>
                        </div>
                    </div>

                    <div>
                        <label class="crm-label text-xs">Project Site <span class="required-marker">*</span></label>
                        <select name="project_id" required class="crm-select text-xs w-full">
                            <option value="">Select Project Site...</option>
                            @foreach($activeProjects as $p)
                                <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>
                                    {{ Str::limit($p->title, 26) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Date, Shift Type, Time --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="crm-label text-xs">Date <span class="required-marker">*</span></label>
                        <input type="date" name="work_date" id="modal-work-date" value="{{ $viewMode === 'day' ? $selectedDate->toDateString() : now()->toDateString() }}" required class="crm-input text-xs w-full">
                    </div>

                    <div>
                        <label class="crm-label text-xs">Shift Type <span class="required-marker">*</span></label>
                        <select name="attendance_type" required class="crm-select text-xs w-full">
                            <option value="full" selected>Full Day (1.0x)</option>
                            <option value="half">Half Day (0.5x)</option>
                        </select>
                    </div>

                    <div>
                        <label class="crm-label text-xs">Punch Time</label>
                        <input type="time" name="check_in_time" value="{{ now()->format('H:i') }}" class="crm-input text-xs w-full">
                    </div>
                </div>

                {{-- PHOTO CAPTURE SECTION (Tabs: File Upload / Live Camera) --}}
                <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded border border-slate-200 dark:border-slate-700">
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide mb-1.5 flex items-center justify-between">
                        <span>Attendance Photo (Selfie / Work Site)</span>
                        <span class="text-[10px] text-slate-400 font-normal">Optional but Recommended</span>
                    </label>

                    {{-- Capture Mode Buttons --}}
                    <div class="flex items-center gap-2 mb-2">
                        <button type="button" onclick="setPhotoTab('file')" id="tab-btn-file" class="px-2.5 py-1 text-xs font-semibold rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-indigo-600 dark:text-indigo-400 shadow-xs">
                            📁 Upload File
                        </button>
                        <button type="button" onclick="setPhotoTab('camera')" id="tab-btn-camera" class="px-2.5 py-1 text-xs font-semibold rounded bg-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900">
                            📷 Live Camera
                        </button>
                    </div>

                    {{-- Mode 1: File Input --}}
                    <div id="photo-tab-file">
                        <input type="file" name="photo" id="modal-photo-input" accept="image/*" class="text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border file:border-slate-300 file:text-xs file:font-semibold file:bg-white hover:file:bg-slate-50 cursor-pointer w-full">
                        <div id="file-preview-wrap" class="mt-2 hidden">
                            <img id="file-preview-img" src="#" alt="Preview" class="w-24 h-24 object-cover rounded border border-slate-300">
                        </div>
                    </div>

                    {{-- Mode 2: Live Camera Video Stream --}}
                    <div id="photo-tab-camera" class="hidden">
                        <div class="relative bg-black rounded overflow-hidden h-48 flex items-center justify-center">
                            <video id="webcam-video" autoplay playsinline class="w-full h-full object-cover"></video>
                            <canvas id="webcam-canvas" class="hidden"></canvas>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <button type="button" onclick="startWebcam()" id="btn-start-camera" class="btn-secondary py-1 px-2.5 text-xs">Start Camera</button>
                            <button type="button" onclick="snapWebcamPhoto()" id="btn-snap-camera" class="btn-primary py-1 px-3 text-xs hidden">📸 Snap Photo</button>
                            <span id="camera-status-text" class="text-[11px] text-slate-500">Click start camera to use webcam</span>
                        </div>
                        <div id="camera-snapped-preview" class="mt-2 hidden">
                            <div class="text-[10px] text-emerald-600 font-bold mb-1">&#10003; Photo Snapped!</div>
                            <img id="camera-snapped-img" src="#" alt="Snapped" class="w-24 h-24 object-cover rounded border border-emerald-500">
                        </div>
                    </div>
                </div>

                {{-- Output Metrics: Cabling Meters & Cameras Installed --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="crm-label text-xs">Cabling Metres Done</label>
                        <input type="number" step="0.1" min="0" name="cabling_metres" placeholder="e.g. 75.0" class="crm-input text-xs w-full">
                        <p class="text-[10px] text-slate-400 mt-0.5">Physical cable pulled today</p>
                    </div>

                    <div>
                        <label class="crm-label text-xs">Cameras Installed</label>
                        <input type="number" min="0" name="cameras_installed" placeholder="e.g. 4" class="crm-input text-xs w-full">
                        <p class="text-[10px] text-slate-400 mt-0.5">Units mounted/configured</p>
                    </div>
                </div>

                {{-- Extra Allowance & Notes --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="crm-label text-xs">Extra Allowance (₹)</label>
                        <input type="number" step="any" min="0" name="extra_amount" placeholder="e.g. 150" class="crm-input text-xs w-full">
                    </div>
                    <div>
                        <label class="crm-label text-xs">Extra Reason</label>
                        <input type="text" name="extra_description" placeholder="e.g. Night shift / Travel allowance" class="crm-input text-xs w-full">
                    </div>
                </div>

                <div>
                    <label class="crm-label text-xs">Work Notes / Summary</label>
                    <textarea name="notes" rows="2" placeholder="Summary of work performed..." class="crm-input text-xs w-full"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" onclick="closeAttendanceModal()" class="btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn-primary text-xs">✓ Save Attendance</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Interactive JavaScript for Lightbox, Webcam, and View Helpers --}}
    <script>
        let webcamStream = null;

        function showLightbox(workerName, profileUrl, punchUrl, dateStr) {
            document.getElementById('lightbox-worker-title').textContent = workerName + ' &bull; ' + dateStr;
            
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

        function openAttendanceModal(dateOverride) {
            if (dateOverride) {
                document.getElementById('modal-work-date').value = dateOverride;
            }
            document.getElementById('markAttendanceModal').classList.remove('hidden');
        }

        function closeAttendanceModal() {
            document.getElementById('markAttendanceModal').classList.add('hidden');
            stopWebcam();
        }

        function onWorkerSelectChange(select) {
            const opt = select.options[select.selectedIndex];
            const photoUrl = opt ? opt.getAttribute('data-photo') : '';
            const workerName = opt ? opt.getAttribute('data-name') : '';
            const box = document.getElementById('modal-worker-preview-box');
            const img = document.getElementById('modal-worker-img');
            const nameEl = document.getElementById('modal-worker-name-preview');

            if (select.value && workerName) {
                box.classList.remove('hidden');
                nameEl.textContent = workerName;
                if (photoUrl && photoUrl !== '') {
                    img.src = photoUrl;
                    img.classList.remove('hidden');
                } else {
                    img.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(workerName) + '&background=6366f1&color=fff&size=64';
                    img.classList.remove('hidden');
                }
            } else {
                box.classList.add('hidden');
            }
        }

        function toggleWorkerShifts(workerId) {
            const row = document.getElementById('worker-shifts-' + workerId);
            if (row) {
                row.classList.toggle('hidden');
            }
        }

        function setPhotoTab(mode) {
            const tabFile = document.getElementById('photo-tab-file');
            const tabCamera = document.getElementById('photo-tab-camera');
            const btnFile = document.getElementById('tab-btn-file');
            const btnCamera = document.getElementById('tab-btn-camera');

            if (mode === 'file') {
                tabFile.classList.remove('hidden');
                tabCamera.classList.add('hidden');
                btnFile.className = "px-2.5 py-1 text-xs font-semibold rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-indigo-600 dark:text-indigo-400 shadow-xs";
                btnCamera.className = "px-2.5 py-1 text-xs font-semibold rounded bg-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900";
                stopWebcam();
            } else {
                tabFile.classList.add('hidden');
                tabCamera.classList.remove('hidden');
                btnCamera.className = "px-2.5 py-1 text-xs font-semibold rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-indigo-600 dark:text-indigo-400 shadow-xs";
                btnFile.className = "px-2.5 py-1 text-xs font-semibold rounded bg-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900";
                startWebcam();
            }
        }

        // File upload live preview & client-side compression for cross-server synchronization
        document.getElementById('modal-photo-input')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    const tempImg = new Image();
                    tempImg.onload = function() {
                        const canvas = document.createElement('canvas');
                        const maxDim = 800;
                        let w = tempImg.width;
                        let h = tempImg.height;
                        if (w > maxDim || h > maxDim) {
                            if (w > h) {
                                h = Math.round((h * maxDim) / w);
                                w = maxDim;
                            } else {
                                w = Math.round((w * maxDim) / h);
                                h = maxDim;
                            }
                        }
                        canvas.width = w;
                        canvas.height = h;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(tempImg, 0, 0, w, h);
                        const compressedBase64 = canvas.toDataURL('image/jpeg', 0.85);

                        const dataInput = document.getElementById('webcam-photo-data');
                        if (dataInput) {
                            dataInput.value = compressedBase64;
                        }

                        const wrap = document.getElementById('file-preview-wrap');
                        const img = document.getElementById('file-preview-img');
                        if (img && wrap) {
                            img.src = compressedBase64;
                            wrap.classList.remove('hidden');
                        }
                    };
                    tempImg.src = evt.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Webcam functions
        async function startWebcam() {
            const video = document.getElementById('webcam-video');
            const status = document.getElementById('camera-status-text');
            const startBtn = document.getElementById('btn-start-camera');
            const snapBtn = document.getElementById('btn-snap-camera');

            try {
                webcamStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                video.srcObject = webcamStream;
                status.textContent = 'Camera ready';
                startBtn.classList.add('hidden');
                snapBtn.classList.remove('hidden');
            } catch (err) {
                status.textContent = 'Unable to access camera: ' + err.message;
            }
        }

        function snapWebcamPhoto() {
            const video = document.getElementById('webcam-video');
            const canvas = document.getElementById('webcam-canvas');
            const dataInput = document.getElementById('webcam-photo-data');
            const previewWrap = document.getElementById('camera-snapped-preview');
            const previewImg = document.getElementById('camera-snapped-img');

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const base64 = canvas.toDataURL('image/jpeg', 0.85);
            dataInput.value = base64;
            previewImg.src = base64;
            previewWrap.classList.remove('hidden');

            stopWebcam();
        }

        function stopWebcam() {
            if (webcamStream) {
                webcamStream.getTracks().forEach(t => t.stop());
                webcamStream = null;
            }
            const startBtn = document.getElementById('btn-start-camera');
            const snapBtn = document.getElementById('btn-snap-camera');
            if (startBtn) startBtn.classList.remove('hidden');
            if (snapBtn) snapBtn.classList.add('hidden');
        }
    </script>
</x-app-layout>
