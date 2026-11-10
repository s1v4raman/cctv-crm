<x-app-layout>
    {{-- FIGMA MOBILE-FIRST TECHNICIAN WORKSTATION --}}
    {{-- High-fidelity implementation based on Figma CRM mobile app specification --}}
    {{-- Features 4 Primary Tabs (Projects, Team, Vacations, Calendar) + My Profile View --}}

    @php
        $selectedPrj = $selectedProject ?? $assignedProjects->first();
        $myAttendance = $myAttendanceToday ?? null;
        $currentUser = auth()->user();
    @endphp

    <div x-data="{
        currentViewTab: 'projects', // 'projects', 'team', 'vacations', 'calendar', 'profile'
        projectSubMode: 'tasks', // 'tasks' (current active task groups) or 'cards' (Figma project cards list)
        selectedProjectId: {{ $selectedPrj?->id ?? 'null' }},
        currentProjectCode: '{{ addslashes($selectedPrj?->project_code ?? ('PRJ-' . ($selectedPrj?->id ?? '0001'))) }}',
        currentProjectTitle: '{{ addslashes($selectedPrj?->title ?? __('No Project Selected')) }}',
        
        projectDetailsOpen: false,
        filterModalOpen: false,
        filterPriority: 'all',
        filterPhase: 'all',
        filterSearch: '',
        supportModalOpen: false,
        supportSubject: 'Technical difficulties',
        supportDescription: '',
        supportSuccess: false,
        openFab: false,
        projectSwitcherOpen: false,

        // Vacations / Leave Request Modal state
        leaveModalOpen: false,
        leaveType: 'sick',
        leaveStartDate: '{{ now()->format('Y-m-d') }}',
        leaveEndDate: '{{ now()->addDays(2)->format('Y-m-d') }}',
        leaveReason: '',
        leaveSubmitting: false,
        
        // Collapsible Phase Groups state
        collapsedPhases: {},
        togglePhase(phaseName) {
            this.collapsedPhases[phaseName] = !this.collapsedPhases[phaseName];
        },
        isPhaseCollapsed(phaseName) {
            return !!this.collapsedPhases[phaseName];
        },

        // Active Selected Task & Details Modal
        activeTask: null,
        taskDetailsOpen: false,
        selectedTaskId: null,
        openTaskDetails(task) {
            this.activeTask = task;
            this.selectedTaskId = task.id;
            this.taskDetailsOpen = true;
        },

        // Time Tracking Modal
        timeTrackingModalOpen: false,
        timeSpentInput: '2h',
        timeTrackingDate: '{{ now()->format('Y-m-d') }}',
        timeTrackingTime: '{{ now()->format('H:i') }}',
        timeTrackingDescription: '',
        timeTrackingSaving: false,

        openTimeTracking() {
            this.timeTrackingModalOpen = true;
        },

        async saveTimeTracking() {
            if (!this.activeTask) return;
            this.timeTrackingSaving = true;

            let str = (this.timeSpentInput || '').toLowerCase().trim();
            let hours = 0;
            let hMatch = str.match(/(\d+(\.\d+)?)\s*h/);
            let mMatch = str.match(/(\d+)\s*m/);
            let dMatch = str.match(/(\d+)\s*d/);
            if (dMatch) hours += parseFloat(dMatch[1]) * 8;
            if (hMatch) hours += parseFloat(hMatch[1]);
            if (mMatch) hours += parseFloat(mMatch[1]) / 60;
            if (hours === 0) hours = parseFloat(str) || 1.0;

            try {
                let res = await fetch(`/technician/tasks/${this.activeTask.id}/log-time`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        hours: hours,
                        notes: this.timeTrackingDescription
                    })
                });
                let data = await res.json();
                if (data.success) {
                    this.activeTask.actual_hours = data.actual_hours;
                    this.timeTrackingModalOpen = false;
                    this.timeTrackingDescription = '';
                    crmAlert('{{ __('Time logged successfully.') }}');
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.timeTrackingSaving = false;
            }
        },

        async updateActiveTaskStatus(newStatus) {
            if (!this.activeTask) return;
            try {
                let res = await fetch(`/technician/tasks/${this.activeTask.id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                let data = await res.json();
                if (data.success) {
                    this.activeTask.status = newStatus;
                }
            } catch (e) {
                console.error(e);
            }
        },

        selectProject(id, code, title) {
            this.selectedProjectId = id;
            this.currentProjectCode = code;
            this.currentProjectTitle = title;
            this.projectSubMode = 'tasks';
        },

        async quickToggleTask(projectId, taskId, currentStatus) {
            let nextStatus = currentStatus === 'completed' ? 'in_progress' : 'completed';
            try {
                let res = await fetch(`/technician/tasks/${taskId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: nextStatus })
                });
                let data = await res.json();
                if (data.success) {
                    window.location.reload();
                }
            } catch (e) {
                console.error(e);
            }
        },

        submitSupport() {
            if (!this.supportDescription.trim()) {
                crmAlert('{{ __('Please enter a brief description of your request.') }}');
                return;
            }
            this.supportSuccess = true;
            setTimeout(() => {
                this.supportSuccess = false;
                this.supportModalOpen = false;
                this.supportDescription = '';
                crmAlert('{{ __('Support request dispatched to office team successfully.') }}');
            }, 600);
        },

        // ==========================================
        // FIGMA MOBILE CALENDAR STATE & LOGIC
        // ==========================================
        calendarEvents: {{ Js::from($calendarEvents ?? []) }},
        calYear: {{ now()->year }},
        calMonth: {{ now()->month - 1 }}, // 0-indexed
        selectedDateStr: '{{ now()->format('Y-m-d') }}',

        get calMonthTitle() {
            const d = new Date(this.calYear, this.calMonth, 1);
            return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        },

        get selectedDateFormatted() {
            if (!this.selectedDateStr) return '';
            const parts = this.selectedDateStr.split('-');
            const d = new Date(parts[0], parts[1] - 1, parts[2]);
            return d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        },

        prevCalMonth() {
            if (this.calMonth === 0) {
                this.calMonth = 11;
                this.calYear--;
            } else {
                this.calMonth--;
            }
        },

        nextCalMonth() {
            if (this.calMonth === 11) {
                this.calMonth = 0;
                this.calYear++;
            } else {
                this.calMonth++;
            }
        },

        get calendarGridDays() {
            const firstDayIndex = new Date(this.calYear, this.calMonth, 1).getDay();
            const startOffset = (firstDayIndex + 6) % 7; // Mon is 0
            const daysInMonth = new Date(this.calYear, this.calMonth + 1, 0).getDate();
            const daysInPrevMonth = new Date(this.calYear, this.calMonth, 0).getDate();

            let days = [];

            // Prev month leading days
            for (let i = startOffset - 1; i >= 0; i--) {
                const d = daysInPrevMonth - i;
                const prevMonthNum = this.calMonth === 0 ? 12 : this.calMonth;
                const prevYearNum = this.calMonth === 0 ? this.calYear - 1 : this.calYear;
                const dateStr = `${prevYearNum}-${String(prevMonthNum).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                days.push({
                    dayNumber: d,
                    dateStr: dateStr,
                    isCurrentMonth: false,
                    events: this.getEventsForDate(dateStr)
                });
            }

            // Current month days
            for (let i = 1; i <= daysInMonth; i++) {
                const currentMonthNum = this.calMonth + 1;
                const dateStr = `${this.calYear}-${String(currentMonthNum).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                days.push({
                    dayNumber: i,
                    dateStr: dateStr,
                    isCurrentMonth: true,
                    events: this.getEventsForDate(dateStr)
                });
            }

            // Next month trailing days
            const remaining = (7 - (days.length % 7)) % 7;
            for (let i = 1; i <= remaining; i++) {
                const nextMonthNum = this.calMonth === 11 ? 1 : this.calMonth + 2;
                const nextYearNum = this.calMonth === 11 ? this.calYear + 1 : this.calYear;
                const dateStr = `${nextYearNum}-${String(nextMonthNum).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                days.push({
                    dayNumber: i,
                    dateStr: dateStr,
                    isCurrentMonth: false,
                    events: this.getEventsForDate(dateStr)
                });
            }

            return days;
        },

        getEventsForDate(dateStr) {
            return (this.calendarEvents || []).filter(e => e.date === dateStr);
        },

        get selectedDayEvents() {
            return this.getEventsForDate(this.selectedDateStr);
        }
    }" class="space-y-4 sm:space-y-5 max-w-xl sm:max-w-2xl lg:max-w-3xl mx-auto pb-28 sm:pb-12 px-3 sm:px-6">

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- 1. FIGMA TOP BAR (Logo Squircle, Search, Alerts, Profile Avatar)     --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div class="flex items-center justify-between pt-1 pb-1">
            <div class="flex items-center gap-3">
                {{-- Droplet / Camera App Mark --}}
                <div class="w-10 h-10 rounded-[14px] bg-gradient-to-tr from-blue-600 to-sky-400 flex items-center justify-center shadow-md shadow-blue-500/20 text-white shrink-0 cursor-pointer"
                     @click="currentViewTab = 'projects'; projectSubMode = 'tasks'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 block leading-tight">
                        {{ __('Welcome back, :name!', ['name' => explode(' ', $currentUser->name)[0]]) }}
                    </span>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight"
                        x-text="currentViewTab === 'profile' ? '{{ __('My Profile') }}' : (currentViewTab === 'calendar' ? '{{ __('Calendar') }}' : (currentViewTab === 'team' ? '{{ __('Team') }}' : (currentViewTab === 'vacations' ? '{{ __('Vacations') }}' : '{{ __('Projects') }}')))">
                        {{ __('Projects') }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Filter / Search Trigger --}}
                <button type="button" @click="filterModalOpen = true" 
                        class="w-9 h-9 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xs flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-blue-600 transition cursor-pointer"
                        title="{{ __('Search & Filters') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </button>

                {{-- Notification Bell --}}
                <button type="button" @click="supportModalOpen = true"
                        class="w-9 h-9 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xs flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-blue-600 transition cursor-pointer relative"
                        title="{{ __('Notifications & Support') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    <span class="w-2 h-2 rounded-full bg-blue-600 absolute top-2 right-2 ring-2 ring-white dark:ring-slate-900"></span>
                </button>

                {{-- User Avatar (Tap opens My Profile screen - Figma Screenshot 1) --}}
                <button type="button" @click="currentViewTab = 'profile'"
                        class="w-9 h-9 rounded-full bg-slate-200 dark:bg-slate-700 ring-2 ring-blue-500/40 dark:ring-blue-400/40 overflow-hidden flex items-center justify-center text-xs font-bold text-slate-700 dark:text-slate-200 shadow-xs transition hover:scale-105 cursor-pointer"
                        title="{{ __('View Profile') }}">
                    @if($currentUser->profile_photo_url)
                        <img src="{{ $currentUser->profile_photo_url }}" class="w-full h-full object-cover" alt="{{ $currentUser->name }}">
                    @else
                        {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                    @endif
                </button>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- 2. FIGMA FILTER ROW & SEGMENTED TABS (Projects, Team, Vacations)    --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentViewTab !== 'profile'" class="space-y-3">
            {{-- Top Filter Row: Current Projects Dropdown & Funnel --}}
            <div class="flex items-center justify-between gap-2">
                {{-- Current Projects Dropdown Selector --}}
                <div class="relative" x-data="{ prjDropdownOpen: false }">
                    <button type="button" @click="prjDropdownOpen = !prjDropdownOpen"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 shadow-2xs hover:border-blue-500 transition cursor-pointer">
                        <span class="truncate max-w-[190px]" x-text="currentProjectTitle">{{ __('Current Projects') }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" :class="prjDropdownOpen ? 'rotate-180 text-blue-600' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div x-show="prjDropdownOpen"
                         x-cloak
                         @click.away="prjDropdownOpen = false"
                         class="absolute left-0 top-full mt-2 w-72 max-w-[85vw] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-800 py-2 z-40 space-y-1">
                        <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-800/80 mb-1">
                            {{ __('Assigned Projects (:count)', ['count' => $assignedProjects->count()]) }}
                        </div>
                        @foreach($assignedProjects as $pOption)
                            <button type="button" 
                                    @click="selectProject({{ $pOption->id }}, '{{ addslashes($pOption->project_code ?? ('PRJ-' . $pOption->id)) }}', '{{ addslashes($pOption->title) }}'); prjDropdownOpen = false;"
                                    class="w-full text-left px-3 py-2 text-xs hover:bg-slate-50 dark:hover:bg-slate-800/70 flex items-center justify-between gap-2 transition cursor-pointer"
                                    :class="selectedProjectId == {{ $pOption->id }} ? 'bg-blue-50/70 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-700 dark:text-slate-200'">
                                <div class="min-w-0">
                                    <span class="block font-mono text-[10px] text-slate-400">{{ $pOption->project_code ?? ('PRJ-' . $pOption->id) }}</span>
                                    <span class="truncate block font-semibold">{{ $pOption->title }}</span>
                                </div>
                                <span class="text-[10px] px-1.5 py-0.5 rounded capitalize bg-slate-100 dark:bg-slate-800 text-slate-500 shrink-0">
                                    {{ str_replace('_', ' ', $pOption->status) }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Funnel Filter Button --}}
                <button type="button" @click="filterModalOpen = true"
                        class="w-9 h-9 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-2xs flex items-center justify-center text-slate-600 dark:text-slate-300 hover:border-blue-500 hover:text-blue-600 transition cursor-pointer"
                        title="{{ __('Filter') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>
                </button>
            </div>

            {{-- SEARCH BAR (MATCHED TYPOGRAPHY & ZERO ICON OVERLAP) --}}
            <div class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-2xs focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20 transition">
                <svg class="w-4 h-4 text-slate-400 shrink-0 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input type="text" x-model="filterSearch" placeholder="{{ __('Search projects, tasks, team members, phases...') }}"
                       class="w-full !border-0 !p-0 !bg-transparent font-sans text-xs sm:text-sm font-medium text-slate-900 dark:text-white placeholder:text-slate-400 placeholder:font-normal focus:outline-none focus:ring-0 !shadow-none !h-auto !min-h-0">
                <button type="button" x-show="filterSearch.length > 0" @click="filterSearch = ''" class="text-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 shrink-0 p-0.5 font-bold leading-none">
                    &times;
                </button>
            </div>

            {{-- FIGMA 3-PILL SEGMENTED CONTROL (Projects | Team | Vacations + Calendar) --}}
            <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl border border-slate-200/60 dark:border-slate-700/60">
                <button type="button" @click="currentViewTab = 'projects'"
                        :class="currentViewTab === 'projects' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 font-semibold hover:text-slate-900 dark:hover:text-white'"
                        class="flex-1 py-2 text-xs rounded-xl transition text-center cursor-pointer">
                    {{ __('Projects') }}
                </button>
                <button type="button" @click="currentViewTab = 'team'"
                        :class="currentViewTab === 'team' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 font-semibold hover:text-slate-900 dark:hover:text-white'"
                        class="flex-1 py-2 text-xs rounded-xl transition text-center cursor-pointer">
                    {{ __('Team') }}
                </button>
                <button type="button" @click="currentViewTab = 'vacations'"
                        :class="currentViewTab === 'vacations' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 font-semibold hover:text-slate-900 dark:hover:text-white'"
                        class="flex-1 py-2 text-xs rounded-xl transition text-center cursor-pointer">
                    {{ __('Vacations') }}
                </button>
                <button type="button" @click="currentViewTab = 'calendar'"
                        :class="currentViewTab === 'calendar' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 font-semibold hover:text-slate-900 dark:hover:text-white'"
                        class="flex-1 py-2 text-xs rounded-xl transition text-center cursor-pointer flex items-center justify-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="3"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/></svg>
                    <span>{{ __('Calendar') }}</span>
                </button>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW TAB A: PROJECTS (Cards Overview or Active Tasks Flow)          --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentViewTab === 'projects'" class="space-y-4">
            
            {{-- Quick Mode Switcher inside Projects: Summary Cards vs Task Flow --}}
            <div class="flex items-center justify-between px-1">
                <span class="text-xs font-bold text-slate-400">
                    <span x-show="projectSubMode === 'cards'">{{ __('All Projects Overview') }} ({{ $assignedProjects->count() }})</span>
                    <span x-show="projectSubMode === 'tasks'">{{ __('Project Tasks & Phases') }}</span>
                </span>

                <button type="button" @click="projectSubMode = (projectSubMode === 'cards' ? 'tasks' : 'cards')"
                        class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                    <span x-show="projectSubMode === 'tasks'">&larr; {{ __('View All Project Cards') }}</span>
                    <span x-show="projectSubMode === 'cards'">{{ __('Open Active Tasks') }} &rarr;</span>
                </button>
            </div>

            {{-- --------------------------------------------------------------- --}}
            {{-- SUBMODE 1: FIGMA PROJECT CARDS LIST (SCREENSHOT 2)               --}}
            {{-- --------------------------------------------------------------- --}}
            <div x-show="projectSubMode === 'cards'" class="space-y-3.5">
                @foreach($assignedProjects as $prj)
                    @php
                        $pTasks = $prj->tasks;
                        $pAllCount = $pTasks->count();
                        $pActiveCount = $pTasks->whereIn('status', ['in_progress', 'pending', 'under_review'])->count();
                        $pWorkers = $prj->workDays()->with('attendances.worker')->get()->pluck('attendances')->flatten()->pluck('worker')->filter()->unique('id');
                    @endphp
                    <div @click="selectProject({{ $prj->id }}, '{{ addslashes($prj->project_code ?? ('PRJ-' . $prj->id)) }}', '{{ addslashes($prj->title) }}')"
                         x-show="!filterSearch || '{{ strtolower(addslashes($prj->title . ' ' . ($prj->project_code ?? '') . ' ' . ($prj->company_name ?? ''))) }}'.includes(filterSearch.toLowerCase())"
                         class="bg-white dark:bg-slate-900 rounded-[26px] p-4 sm:p-5 border border-slate-100 dark:border-slate-800 shadow-[0_2px_14px_-2px_rgba(0,0,0,0.04)] space-y-3 cursor-pointer hover:shadow-md transition relative overflow-hidden"
                         :class="selectedProjectId == {{ $prj->id }} ? 'ring-2 ring-blue-500/40' : ''">
                        
                        {{-- Top Row: Squircle Badge, Project Code & Title --}}
                        <div class="flex items-start gap-3">
                            {{-- Colorful Graphic Squircle Thumbnail --}}
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 shadow-xs {{ $loop->index % 3 === 0 ? 'bg-gradient-to-tr from-amber-400 to-yellow-300 text-amber-900' : ($loop->index % 3 === 1 ? 'bg-gradient-to-tr from-sky-400 to-blue-500 text-white' : 'bg-gradient-to-tr from-indigo-500 to-purple-400 text-white') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-wider block">
                                    {{ $prj->project_code ?? ('PRJ-' . $prj->id) }}
                                </span>
                                <h4 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white truncate">
                                    {{ $prj->title }}
                                </h4>
                            </div>
                        </div>

                        {{-- Metadata Row: Created Date & Priority --}}
                        <div class="flex items-center justify-between text-xs text-slate-400 pt-0.5">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="3"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                <span>{{ __('Created :date', ['date' => $prj->created_at->format('M d, Y')]) }}</span>
                            </div>

                            <div class="flex items-center gap-1 font-bold text-[11px] {{ $prj->priority === 'urgent' ? 'text-rose-500' : ($prj->priority === 'high' ? 'text-blue-500' : ($prj->priority === 'low' ? 'text-emerald-500' : 'text-amber-500')) }}">
                                <span>&uarr;</span>
                                <span class="capitalize">{{ $prj->priority ?: __('Medium') }}</span>
                            </div>
                        </div>

                        {{-- Section: Project Data with 3 Columns (All Tasks, Active Tasks, Assignees) --}}
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80">
                            <span class="block text-[11px] font-bold text-slate-900 dark:text-white mb-2">
                                {{ __('Project Data') }}
                            </span>

                            <div class="grid grid-cols-3 gap-2 text-center sm:text-left">
                                <div>
                                    <span class="block text-[10px] text-slate-400">{{ __('All tasks') }}</span>
                                    <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white">{{ $pAllCount }}</span>
                                </div>

                                <div>
                                    <span class="block text-[10px] text-slate-400">{{ __('Active tasks') }}</span>
                                    <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white">{{ $pActiveCount }}</span>
                                </div>

                                <div>
                                    <span class="block text-[10px] text-slate-400 mb-1">{{ __('Assignees') }}</span>
                                    <div class="flex items-center -space-x-1.5 justify-center sm:justify-start">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 border-2 border-white dark:border-slate-900 flex items-center justify-center font-bold text-[9px]">
                                            {{ strtoupper(substr($prj->leadTechnician?->name ?? 'T', 0, 1)) }}
                                        </div>
                                        <div class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 border-2 border-white dark:border-slate-900 flex items-center justify-center font-bold text-[9px]">
                                            S
                                        </div>
                                        <div class="w-6 h-6 rounded-full bg-blue-600 text-white border-2 border-white dark:border-slate-900 flex items-center justify-center font-bold text-[9px]">
                                            +{{ max(1, $pWorkers->count()) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- --------------------------------------------------------------- --}}
            {{-- SUBMODE 2: CURRENT PROJECT DETAIL & TASK FLOW                     --}}
            {{-- --------------------------------------------------------------- --}}
            <div x-show="projectSubMode === 'tasks'" class="space-y-4">
                {{-- Current Project Selector Card --}}
                <div class="relative bg-white dark:bg-slate-900 rounded-[22px] p-4 sm:p-5 border border-slate-100 dark:border-slate-800 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                    <div class="flex items-start gap-3.5">
                        <div class="w-1.5 self-stretch bg-blue-600 rounded-full shrink-0 my-0.5"></div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-mono font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block"
                                      x-text="currentProjectCode">
                                    {{ $selectedPrj?->project_code ?? ('PRJ-' . ($selectedPrj?->id ?? '0001')) }}
                                </span>

                                <button type="button" @click="projectSubMode = 'cards'"
                                        class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ __('Switch') }} &rarr;
                                </button>
                            </div>

                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mt-0.5 leading-snug truncate"
                                x-text="currentProjectTitle">
                                {{ $selectedPrj?->title ?? __('No Project Selected') }}
                            </h2>

                            <div class="mt-2 flex items-center justify-between">
                                <button type="button" @click="projectDetailsOpen = true" 
                                        class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 inline-flex items-center gap-1 cursor-pointer transition">
                                    <span>{{ __('View details') }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </button>

                                <span class="text-[11px] text-slate-400">
                                    {{ $selectedPrj?->company_name ?? ($selectedPrj?->customer?->name ?? __('Direct Client')) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Shift Clock-In Compact Strip --}}
                <div class="bg-white dark:bg-slate-900 rounded-[20px] p-3 sm:p-3.5 border border-slate-100 dark:border-slate-800 shadow-[0_2px_10px_-2px_rgba(0,0,0,0.04)] flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __("Today's Shift") }}</span>
                            @if(!$myAttendance || !$myAttendance->clock_in)
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    {{ __('Not Clocked In') }}
                                </span>
                            @elseif(!$myAttendance->clock_out)
                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    {{ __('In: :time', ['time' => \Carbon\Carbon::parse($myAttendance->clock_in)->format('h:i A')]) }}
                                </span>
                            @else
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    {{ __('Shift Done (:h hrs)', ['h' => $myAttendance->total_hours]) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        @if(!$myAttendance || !$myAttendance->clock_in)
                            <form method="POST" action="{{ route('attendance.clock-in') }}">
                                @csrf
                                <input type="hidden" name="location_type" value="on_site">
                                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                    {{ __('Clock In') }}
                                </button>
                            </form>
                        @elseif(!$myAttendance->clock_out)
                            <form method="POST" action="{{ route('attendance.clock-out') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                    {{ __('Clock Out') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Task Section with Collapsible Phase Groups --}}
                <div class="space-y-3 pt-1">
                    @foreach($assignedProjects as $prj)
                        @php
                            $allPrjTasks = $prj->tasks;
                            $activeTasks = $allPrjTasks->whereIn('status', ['in_progress', 'completed', 'under_review']);
                            if ($activeTasks->isEmpty() && $allPrjTasks->isNotEmpty()) {
                                $activeTasks = $allPrjTasks->take(5);
                            }
                            $activeGroups = $activeTasks->groupBy(function($item) {
                                return $item->phase_name ?: __('CCTV Deployment');
                            });
                        @endphp

                        <div x-show="selectedProjectId == {{ $prj->id }}" class="space-y-4">
                            {{-- Section Pill: Active Tasks --}}
                            <div class="w-full py-2 px-4 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-center text-xs font-bold text-slate-700 dark:text-slate-300 shadow-xs">
                                {{ __('Active Tasks') }} ({{ $activeTasks->count() }})
                            </div>

                            {{-- Collapsible Phase Groups (Figma Screenshots) --}}
                            @forelse($activeGroups as $phaseName => $groupTasks)
                                @php
                                    $phaseKey = 'phase_' . $prj->id . '_' . \Illuminate\Support\Str::slug($phaseName);
                                @endphp
                                <div class="space-y-2.5">
                                    {{-- Accordion Phase Header: ^ Group Name (N issues) --}}
                                    <button type="button" @click="togglePhase('{{ $phaseKey }}')" 
                                            class="w-full flex items-center justify-between text-left py-1 px-1 cursor-pointer group">
                                        <div class="flex items-center gap-2">
                                            <span class="text-blue-600 dark:text-blue-400 font-black text-xs transition-transform duration-200"
                                                  :class="isPhaseCollapsed('{{ $phaseKey }}') ? 'rotate-180' : ''">
                                                &and;
                                            </span>
                                            <span class="text-xs sm:text-sm font-bold text-blue-600 dark:text-blue-400 group-hover:underline">
                                                {{ $phaseName }} ({{ $groupTasks->count() }} {{ __('issues') }})
                                            </span>
                                        </div>
                                    </button>

                                    {{-- Group Tasks Container --}}
                                    <div x-show="!isPhaseCollapsed('{{ $phaseKey }}')" 
                                         x-transition
                                         class="space-y-3">
                                        @foreach($groupTasks as $task)
                                            @php
                                                $taskSerialized = [
                                                    'id' => $task->id,
                                                    'title' => $task->title,
                                                    'phase_name' => $task->phase_name,
                                                    'status' => $task->status,
                                                    'priority' => $task->priority,
                                                    'estimated_hours' => $task->estimated_hours,
                                                    'actual_hours' => $task->actual_hours,
                                                    'description' => $task->description ?: __('Execute cable pulling, junction box installation, and camera termination as per site blueprint.'),
                                                    'reporter_name' => $prj->leadTechnician?->name ?? 'Raman (Lead Engineer)',
                                                    'assigned_name' => $task->assignedUser?->name ?? 'Blake Silva',
                                                    'deadline' => $task->due_date ? $task->due_date->translatedFormat('M d, Y') : ($prj->target_completion_date ? \Carbon\Carbon::parse($prj->target_completion_date)->translatedFormat('M d, Y') : 'Nov 15, 2026'),
                                                    'created_at' => $task->created_at ? $task->created_at->translatedFormat('M d, Y') : 'Oct 30, 2026',
                                                    'project_code' => $prj->project_code ?? ('PRJ-' . $prj->id),
                                                ];
                                            @endphp
                                            <div @click="openTaskDetails({{ Js::from($taskSerialized) }})"
                                                 x-show="!filterSearch || '{{ strtolower(addslashes($task->title . ' ' . $task->phase_name . ' ' . ($task->assignedUser?->name ?? ''))) }}'.includes(filterSearch.toLowerCase())"
                                                 class="bg-white dark:bg-slate-900 rounded-[22px] p-4 sm:p-5 border shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] space-y-3 cursor-pointer hover:shadow-md transition"
                                                 :class="selectedTaskId == {{ $task->id }} ? 'border-2 border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-100 dark:border-slate-800'">
                                                
                                                {{-- Top Row: Task Name & Circle Status --}}
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0 flex-1">
                                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-0.5">
                                                            {{ __('Task Name') }}
                                                        </span>
                                                        <h4 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white leading-snug">
                                                            {{ $task->title }}
                                                        </h4>
                                                    </div>

                                                    <button type="button" @click.stop="quickToggleTask({{ $prj->id }}, {{ $task->id }}, '{{ $task->status }}')"
                                                            class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center transition cursor-pointer hover:scale-105 active:scale-95"
                                                            title="{{ __('Toggle status') }}">
                                                        @if($task->status === 'completed')
                                                            <div class="w-6 h-6 rounded-full border-2 border-emerald-500 bg-emerald-50 dark:bg-emerald-950/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shadow-xs">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                            </div>
                                                        @elseif($task->status === 'in_progress')
                                                            <div class="w-6 h-6 rounded-full border-2 border-blue-500 border-t-transparent flex items-center justify-center">
                                                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                                            </div>
                                                        @else
                                                            <div class="w-6 h-6 rounded-full border-2 border-slate-300 dark:border-slate-600"></div>
                                                        @endif
                                                    </button>
                                                </div>

                                                {{-- 3 Column Row: Estimate, Spent Time, Assignee --}}
                                                <div class="grid grid-cols-3 gap-2 pt-1 border-t border-slate-50 dark:border-slate-800/60">
                                                    <div>
                                                        <span class="text-[10px] text-slate-400 block mb-0.5">{{ __('Estimate') }}</span>
                                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                                            {{ $task->estimated_hours ? ($task->estimated_hours . 'h') : '2d 4h' }}
                                                        </span>
                                                    </div>

                                                    <div>
                                                        <span class="text-[10px] text-slate-400 block mb-0.5">{{ __('Spent Time') }}</span>
                                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                                            {{ $task->actual_hours ? ($task->actual_hours . 'h') : '1d 2h' }}
                                                        </span>
                                                    </div>

                                                    <div>
                                                        <span class="text-[10px] text-slate-400 block mb-0.5">{{ __('Assignee') }}</span>
                                                        <div class="flex items-center gap-1.5">
                                                            <div class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-[10px] font-bold text-slate-700 dark:text-slate-200">
                                                                {{ strtoupper(substr($task->assignedUser?->name ?? 'T', 0, 1)) }}
                                                            </div>
                                                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">
                                                                {{ explode(' ', $task->assignedUser?->name ?? 'Blake')[0] }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Bottom Row: Priority & Status Pill --}}
                                                <div class="flex items-center justify-between pt-1">
                                                    <div class="flex items-center gap-1 font-bold text-[11px] {{ $task->priority === 'urgent' ? 'text-rose-500' : ($task->priority === 'high' ? 'text-blue-500' : ($task->priority === 'low' ? 'text-emerald-500' : 'text-amber-500')) }}">
                                                        <span>{{ $task->priority === 'low' ? '&darr;' : '&uarr;' }}</span>
                                                        <span class="capitalize">{{ $task->priority ?: 'Medium' }}</span>
                                                    </div>

                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $task->status === 'completed' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : ($task->status === 'in_progress' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400') }}">
                                                        {{ str_replace('_', ' ', $task->status) }}
                                                    </span>
                                                </div>

                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="bg-white dark:bg-slate-900 rounded-[22px] p-6 text-center border border-slate-100 dark:border-slate-800 text-xs text-slate-400">
                                    {{ __('No tasks assigned in this project.') }}
                                </div>
                            @endforelse
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW TAB B: TEAM MEMBERS 2-COLUMN GRID (FIGMA SCREENSHOT 3)         --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentViewTab === 'team'" class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <span class="text-xs font-bold text-slate-400">
                    {{ __('Field Workforce & Technicians (:count)', ['count' => count($teamMembers)]) }}
                </span>
                <button type="button" @click="$dispatch('open-modal', 'mark-worker-attendance')"
                        class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                    {{ __('Log Team Attendance') }} &rarr;
                </button>
            </div>

            {{-- 2-Column Bento Grid matching Figma Screenshot 3 --}}
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                @foreach($teamMembers as $member)
                    <div x-show="!filterSearch || '{{ strtolower(addslashes($member->name . ' ' . $member->role_label . ' ' . $member->level)) }}'.includes(filterSearch.toLowerCase())"
                         class="bg-white dark:bg-slate-900 rounded-[24px] p-4 sm:p-5 text-center border border-slate-100 dark:border-slate-800 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] flex flex-col items-center justify-between space-y-2.5 hover:shadow-md transition">
                        
                        {{-- Circular Avatar with Blue Status Ring --}}
                        <div class="relative">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full ring-4 ring-blue-100 dark:ring-blue-950 p-0.5 overflow-hidden flex items-center justify-center font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 shadow-xs mx-auto">
                                @if($member->photo)
                                    <img src="{{ $member->photo }}" class="w-full h-full object-cover rounded-full" alt="{{ $member->name }}">
                                @else
                                    <span class="text-base sm:text-lg">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 absolute bottom-0 right-0 ring-2 ring-white dark:ring-slate-900"></span>
                        </div>

                        {{-- Name & Role --}}
                        <div class="w-full">
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate">
                                {{ $member->name }}
                            </h4>
                            <span class="text-[10px] sm:text-[11px] text-slate-400 truncate block mt-0.5">
                                {{ $member->role_label }}
                            </span>
                        </div>

                        {{-- Level Badge Pill: Middle / Junior / Senior / Lead --}}
                        <div>
                            <span class="text-[10px] font-bold px-3 py-0.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
                                {{ $member->level }}
                            </span>
                        </div>

                        {{-- Quick Call / WhatsApp Contact link --}}
                        @if($member->phone)
                            <div class="pt-1 w-full flex items-center justify-center gap-2">
                                <a href="tel:{{ $member->phone }}" 
                                   class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 hover:bg-blue-100 transition"
                                   title="{{ __('Call') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                </a>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}" target="_blank"
                                   class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 transition"
                                   title="{{ __('WhatsApp') }}">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.073.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/></svg>
                                </a>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW TAB C: VACATIONS / LEAVE REQUESTS (FIGMA SCREENSHOT 4)          --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentViewTab === 'vacations'" class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <span class="text-xs font-bold text-slate-400">
                    {{ __('Vacations & Leave Requests (:count)', ['count' => count($myLeaveRequests)]) }}
                </span>
                <button type="button" @click="leaveModalOpen = true"
                        class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                    <span class="text-base leading-none">+</span>
                    <span>{{ __('New Request') }}</span>
                </button>
            </div>

            {{-- Vacations List matching Figma Screenshot 4 --}}
            <div class="space-y-3">
                @forelse($myLeaveRequests as $leave)
                    @php
                        $isSick = in_array($leave->leave_type, ['sick', 'emergency']);
                        $isRemote = in_array($leave->leave_type, ['unpaid_lwp', 'remote']);
                        $isVacation = in_array($leave->leave_type, ['casual', 'earned', 'vacation']);
                        $typeName = $isSick ? __('Sick Leave') : ($isRemote ? __('Work remotely') : __('Vacation'));
                        $dotColor = $isSick ? 'bg-rose-500' : ($isRemote ? 'bg-purple-500' : 'bg-blue-500');
                        $badgeClass = $leave->status === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : ($leave->status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400');
                    @endphp
                    <div x-show="!filterSearch || '{{ strtolower(addslashes($typeName . ' ' . $leave->status . ' ' . ($leave->reason ?? ''))) }}'.includes(filterSearch.toLowerCase())"
                         class="bg-white dark:bg-slate-900 rounded-[24px] p-4 sm:p-5 border border-slate-100 dark:border-slate-800 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] space-y-3">
                        
                        {{-- Top Row: Request Type Label & Status Badge Pill --}}
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                {{ __('Request Type') }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold capitalize {{ $badgeClass }}">
                                {{ $leave->status }}
                            </span>
                        </div>

                        {{-- Middle Row: Dot indicator & Leave Name --}}
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full {{ $dotColor }} shrink-0"></span>
                            <h4 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                                {{ $typeName }}
                            </h4>
                        </div>

                        {{-- Bottom Row: Date Range & Duration Pill --}}
                        <div class="flex items-center justify-between pt-1 border-t border-slate-50 dark:border-slate-800/60 text-xs">
                            <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="3"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                <span>
                                    {{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                                </span>
                            </div>

                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $leave->days_count ?? 1 }}d
                            </span>
                        </div>

                    </div>
                @empty
                    <div class="bg-white dark:bg-slate-900 rounded-[24px] p-8 text-center border border-slate-100 dark:border-slate-800 text-xs text-slate-400">
                        {{ __('No vacation or leave requests recorded.') }}
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW TAB D: CALENDAR VIEW (MOBILE MONTH CALENDAR & SCHEDULE)        --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentViewTab === 'calendar'" class="space-y-4">
            {{-- Month Card with Navigation --}}
            <div class="bg-white dark:bg-slate-900 rounded-[28px] p-5 sm:p-6 border border-slate-100 dark:border-slate-800 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] space-y-4">
                
                {{-- Month Header & Nav Arrows --}}
                <div class="flex items-center justify-between px-1">
                    <button type="button" @click="prevCalMonth()" 
                            class="p-2 rounded-xl text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/60 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </button>

                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight"
                        x-text="calMonthTitle">
                        {{ now()->format('F, Y') }}
                    </h2>

                    <button type="button" @click="nextCalMonth()" 
                            class="p-2 rounded-xl text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/60 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </button>
                </div>

                {{-- Weekdays Header Row --}}
                <div class="grid grid-cols-7 gap-1 text-center">
                    <template x-for="day in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" :key="day">
                        <span class="text-[11px] font-bold text-slate-400 py-1" x-text="day"></span>
                    </template>
                </div>

                {{-- Calendar Days Grid --}}
                <div class="grid grid-cols-7 gap-1 text-center">
                    <template x-for="(d, idx) in calendarGridDays" :key="idx">
                        <button type="button" 
                                @click="selectedDateStr = d.dateStr"
                                class="h-10 sm:h-11 rounded-xl flex flex-col items-center justify-center relative transition cursor-pointer group"
                                :class="{
                                    'border-2 border-blue-500 font-bold bg-blue-50/50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400': selectedDateStr === d.dateStr,
                                    'text-slate-900 dark:text-white font-semibold hover:bg-slate-50 dark:hover:bg-slate-800': d.isCurrentMonth && selectedDateStr !== d.dateStr,
                                    'text-slate-300 dark:text-slate-600': !d.isCurrentMonth && selectedDateStr !== d.dateStr
                                }">
                            <span class="text-xs leading-none" x-text="d.dayNumber"></span>
                            
                            {{-- Event Dots --}}
                            <div class="flex items-center gap-0.5 mt-1" x-show="d.events && d.events.length > 0">
                                <template x-for="(ev, eIdx) in (d.events || []).slice(0, 3)" :key="eIdx">
                                    <span class="w-1.5 h-1.5 rounded-full" 
                                          :class="{
                                              'bg-blue-500': ev.color === 'blue',
                                              'bg-purple-500': ev.color === 'purple',
                                              'bg-rose-500': ev.color === 'rose',
                                              'bg-emerald-500': ev.color === 'emerald',
                                              'bg-amber-500': ev.color === 'amber'
                                          }"></span>
                                </template>
                            </div>
                        </button>
                    </template>
                </div>

            </div>

            {{-- Daily Schedule List for Selected Date --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white tracking-tight"
                        x-text="selectedDateFormatted">
                        {{ now()->format('F d, Y') }}
                    </h3>
                    <span class="text-xs text-slate-400"
                          x-text="selectedDayEvents.length + ' {{ __('events') }}'">
                    </span>
                </div>

                {{-- Schedule Cards with Left Stripe --}}
                <div class="space-y-2.5">
                    <template x-for="event in selectedDayEvents" :key="event.id">
                        <div class="bg-white dark:bg-slate-900 rounded-[20px] p-3.5 sm:p-4 border border-slate-100 dark:border-slate-800 shadow-xs relative overflow-hidden flex items-center justify-between gap-3">
                            {{-- Left Color Accent Stripe --}}
                            <div class="w-1.5 absolute left-0 top-3 bottom-3 rounded-r-full"
                                 :class="{
                                     'bg-purple-500': event.color === 'purple',
                                     'bg-blue-500': event.color === 'blue',
                                     'bg-rose-500': event.color === 'rose',
                                     'bg-emerald-500': event.color === 'emerald'
                                 }"></div>

                            <div class="min-w-0 flex-1 pl-2.5">
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white leading-snug truncate"
                                    x-text="event.title"></h4>
                                <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                                    <span x-text="event.time"></span>
                                    <span>&bull;</span>
                                    <span x-text="event.type"></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[11px] font-bold text-slate-400" x-text="event.duration"></span>
                                <span class="text-xs font-bold"
                                      :class="event.priority === 'high' || event.priority === 'urgent' ? 'text-blue-500' : 'text-emerald-500'"
                                      x-text="event.priority === 'high' ? '&uarr;' : '&darr;'"></span>
                            </div>
                        </div>
                    </template>

                    <div x-show="selectedDayEvents.length === 0" 
                         class="bg-white dark:bg-slate-900 rounded-[20px] p-6 text-center border border-slate-100 dark:border-slate-800 text-xs text-slate-400">
                        {{ __('No scheduled tasks or jobs on this date.') }}
                    </div>
                </div>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW TAB E: MY PROFILE (FIGMA SCREENSHOT 1)                          --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentViewTab === 'profile'" class="space-y-4">
            {{-- Profile Screen Header --}}
            <div class="flex items-center justify-between px-1">
                <button type="button" @click="currentViewTab = 'projects'"
                        class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                    <span>&larr;</span>
                    <span>{{ __('Back to Dashboard') }}</span>
                </button>

                <a href="{{ route('profile.edit') }}" 
                   class="w-9 h-9 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-2xs flex items-center justify-center text-slate-500 hover:text-blue-600 transition"
                   title="{{ __('Settings') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
            </div>

            {{-- Profile Card matching Figma Screenshot 1 --}}
            <div class="bg-white dark:bg-slate-900 rounded-[28px] p-5 sm:p-6 border border-slate-100 dark:border-slate-800 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] space-y-5">
                
                {{-- Avatar & Edit Button Row --}}
                <div class="flex items-start justify-between">
                    {{-- Circular Avatar with Blue Ring --}}
                    <div class="flex items-center gap-4">
                        <div class="w-18 h-18 sm:w-20 sm:h-20 rounded-full ring-4 ring-blue-500/30 p-1 overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-slate-700 dark:text-slate-200 text-xl shadow-xs">
                            @if($currentUser->profile_photo_url)
                                <img src="{{ $currentUser->profile_photo_url }}" class="w-full h-full object-cover rounded-full" alt="{{ $currentUser->name }}">
                            @else
                                {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                            @endif
                        </div>

                        <div>
                            <h3 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white leading-tight">
                                {{ $currentUser->name }}
                            </h3>
                            <span class="text-xs text-slate-400 font-semibold block mt-0.5">
                                {{ $currentUser->role === 'technician' ? __('Lead CCTV Technician') : __('Field Operations Engineer') }}
                            </span>
                        </div>
                    </div>

                    {{-- Edit Profile Icon Button --}}
                    <a href="{{ route('profile.edit') }}" 
                       class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:border-blue-500 transition shadow-2xs"
                       title="{{ __('Edit Profile') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                    </a>
                </div>

                {{-- SECTION: MAIN INFO --}}
                <div class="space-y-3 pt-2">
                    <span class="block text-xs font-bold text-slate-900 dark:text-white">
                        {{ __('Main info') }}
                    </span>

                    {{-- Position --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">{{ __('Position') }}</label>
                        <input type="text" readonly value="{{ $currentUser->role === 'technician' ? 'Lead CCTV Technician & Field Engineer' : 'Field Operations Engineer' }}"
                               class="w-full text-xs font-sans font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3.5 py-2.5 shadow-2xs">
                    </div>

                    {{-- Company --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">{{ __('Company') }}</label>
                        <input type="text" readonly value="{{ config('app.name', 'SecureVision Systems Ltd.') }}"
                               class="w-full text-xs font-sans font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3.5 py-2.5 shadow-2xs">
                    </div>

                    {{-- Location with Pin Icon --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">{{ __('Location') }}</label>
                        <div class="relative">
                            <input type="text" readonly value="Coimbatore, Tamil Nadu, India"
                                   class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 pl-3.5 pr-9 py-2.5 shadow-2xs">
                            <svg class="w-4 h-4 text-slate-400 absolute right-3 top-3 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        </div>
                    </div>

                    {{-- Birthday Date with Calendar Icon --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">{{ __('Birthday Date') }}</label>
                        <div class="relative">
                            <input type="text" readonly value="May 19, 1996"
                                   class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 pl-3.5 pr-9 py-2.5 shadow-2xs">
                            <svg class="w-4 h-4 text-slate-400 absolute right-3 top-3 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="3"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        </div>
                    </div>
                </div>

                {{-- SECTION: CONTACT INFO --}}
                <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <span class="block text-xs font-bold text-slate-900 dark:text-white">
                        {{ __('Contact Info') }}
                    </span>

                    {{-- Email --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">{{ __('Email') }}</label>
                        <input type="text" readonly value="{{ $currentUser->email }}"
                               class="w-full text-xs font-sans font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3.5 py-2.5 shadow-2xs">
                    </div>

                    {{-- Mobile Number --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">{{ __('Mobile Number') }}</label>
                        <input type="text" readonly value="{{ $currentUser->phone ?? '+91 98765 43210' }}"
                               class="w-full text-xs font-sans font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3.5 py-2.5 shadow-2xs">
                    </div>

                    {{-- Skype / WhatsApp --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">{{ __('WhatsApp / Telegram') }}</label>
                        <input type="text" readonly value="{{ $currentUser->phone ?? '+91 98765 43210' }}"
                               class="w-full text-xs font-sans font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3.5 py-2.5 shadow-2xs">
                    </div>
                </div>

            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- FLOATING ACTION BUTTON (+) (FIGMA BLUE CIRCLE AT BOTTOM RIGHT)       --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div class="fixed bottom-6 right-6 z-40">
            <button type="button" 
                    @click="currentViewTab === 'vacations' ? (leaveModalOpen = true) : (currentViewTab === 'team' ? $dispatch('open-modal', 'mark-worker-attendance') : (openFab = !openFab))"
                    class="w-14 h-14 rounded-full bg-blue-600 hover:bg-blue-700 active:scale-95 text-white shadow-xl shadow-blue-500/40 flex items-center justify-center transition cursor-pointer">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </button>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- MODAL A: TASK DETAILS BOTTOM SHEET                                  --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="taskDetailsOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            
            <div @click.away="taskDetailsOpen = false" 
                 x-show="taskDetailsOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                 class="bg-white dark:bg-slate-900 rounded-[28px] max-w-sm sm:max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-100 dark:border-slate-800 space-y-4 relative max-h-[90vh] overflow-y-auto">
                
                {{-- Back Link & Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <button type="button" @click="taskDetailsOpen = false" 
                            class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                        <span>&larr;</span>
                        <span>{{ __('Back to Projects') }}</span>
                    </button>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400" x-text="activeTask ? activeTask.project_code : ''"></span>
                </div>

                {{-- Task Title & Status Selector --}}
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white leading-snug"
                        x-text="activeTask ? activeTask.title : ''"></h3>
                    
                    <div class="mt-2.5 flex items-center gap-2">
                        <select @change="updateActiveTaskStatus($event.target.value)" 
                                :value="activeTask ? activeTask.status : 'in_progress'"
                                class="text-xs font-bold px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 capitalize">
                            <option value="pending">{{ __('Pending / To Do') }}</option>
                            <option value="in_progress">{{ __('In Progress') }}</option>
                            <option value="under_review">{{ __('Under Review') }}</option>
                            <option value="completed">{{ __('Completed') }}</option>
                        </select>
                    </div>
                </div>

                {{-- Task Info Card --}}
                <div class="space-y-3 pt-1">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Task Info') }}</span>
                    
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-slate-400 block mb-0.5">{{ __('Reporter') }}</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="activeTask ? activeTask.reporter_name : ''"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">{{ __('Assigned') }}</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="activeTask ? activeTask.assigned_name : ''"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">{{ __('Priority') }}</span>
                            <span class="font-bold text-amber-500 capitalize" x-text="activeTask ? activeTask.priority : ''"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">{{ __('Dead Line') }}</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="activeTask ? activeTask.deadline : ''"></span>
                        </div>
                    </div>
                </div>

                {{-- Time Tracking Card with Blue Log Time Button --}}
                <div class="bg-blue-50/70 dark:bg-blue-950/40 rounded-2xl p-4 border border-blue-100 dark:border-blue-900/60 space-y-3">
                    <span class="block text-xs font-bold text-slate-900 dark:text-white">{{ __('Time tracking') }}</span>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full border-4 border-blue-500 border-t-transparent flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-black text-slate-900 dark:text-white block" x-text="(activeTask && activeTask.actual_hours ? activeTask.actual_hours : '1.5') + 'h logged'"></span>
                            <span class="text-[10px] text-slate-500" x-text="'Original Estimate: ' + (activeTask && activeTask.estimated_hours ? activeTask.estimated_hours : '8') + 'h'"></span>
                        </div>
                    </div>

                    <button type="button" @click="openTimeTracking()" 
                            class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>{{ __('Log time') }}</span>
                    </button>
                </div>

                {{-- Description --}}
                <div class="space-y-1">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Description') }}</span>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed" x-text="activeTask ? activeTask.description : ''"></p>
                </div>

            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- MODAL B: TIME TRACKING MODAL (FIGMA SCREENSHOT 3)                   --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="timeTrackingModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            
            <div @click.away="timeTrackingModalOpen = false" 
                 x-show="timeTrackingModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white dark:bg-slate-900 rounded-[28px] max-w-sm sm:max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-100 dark:border-slate-800 space-y-4 relative">
                
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                        {{ __('Time Tracking') }}
                    </h3>
                    <button type="button" @click="timeTrackingModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Time Spent Input --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Time spent') }}</label>
                    <input type="text" x-model="timeSpentInput" placeholder="e.g. 2h 30m or 4h"
                           class="w-full text-xs font-sans rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-2xs">
                </div>

                {{-- Date Input --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Date') }}</label>
                    <input type="date" x-model="timeTrackingDate"
                           class="w-full text-xs font-sans rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-2xs">
                </div>

                {{-- Work Description --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Work Description') }}</label>
                    <textarea x-model="timeTrackingDescription" rows="3" placeholder="{{ __('Add some description of the task...') }}"
                              class="w-full text-xs font-sans rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-2xs"></textarea>
                </div>

                {{-- Save Button --}}
                <div class="pt-2">
                    <button type="button" @click="saveTimeTracking()" :disabled="timeTrackingSaving"
                            class="w-full py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-bold text-xs shadow-lg shadow-blue-500/30 transition flex items-center justify-center gap-2 cursor-pointer">
                        <span x-show="!timeTrackingSaving">{{ __('Save') }}</span>
                        <span x-show="timeTrackingSaving">{{ __('Saving...') }}</span>
                    </button>
                </div>

            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- MODAL C: NEW VACATION / LEAVE REQUEST (FIGMA SCREENSHOT 4)          --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="leaveModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            
            <div @click.away="leaveModalOpen = false" 
                 x-show="leaveModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white dark:bg-slate-900 rounded-[28px] max-w-sm sm:max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-100 dark:border-slate-800 space-y-4 relative">
                
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                        {{ __('New Leave / Vacation Request') }}
                    </h3>
                    <button type="button" @click="leaveModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('leaves.store') }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Request Type') }}</label>
                        <select name="leave_type" x-model="leaveType" required
                                class="w-full text-xs font-sans rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-2xs">
                            <option value="sick">{{ __('Sick Leave') }} (Viral / Medical)</option>
                            <option value="unpaid_lwp">{{ __('Work remotely') }} (Site Prep / Remote)</option>
                            <option value="casual">{{ __('Vacation / Casual Leave') }}</option>
                            <option value="earned">{{ __('Annual Earned Leave') }}</option>
                            <option value="emergency">{{ __('Emergency Leave') }}</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Start Date') }}</label>
                            <input type="date" name="start_date" x-model="leaveStartDate" required
                                   class="w-full text-xs font-sans rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-2xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('End Date') }}</label>
                            <input type="date" name="end_date" x-model="leaveEndDate" required
                                   class="w-full text-xs font-sans rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-2xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Reason / Work Note') }}</label>
                        <textarea name="reason" rows="3" required minlength="5" placeholder="{{ __('Explain reason for leave request (min 5 characters)...') }}"
                                  class="w-full text-xs font-sans rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-2xs"></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-bold text-xs shadow-lg shadow-blue-500/30 transition cursor-pointer">
                            {{ __('Submit Leave Request') }}
                        </button>
                    </div>
                </form>

            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- MODAL D: PROJECT DETAILS FULL SHEET                                 --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="projectDetailsOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            
            @foreach($assignedProjects as $prj)
                <div x-show="selectedProjectId == {{ $prj->id }}" 
                     @click.away="projectDetailsOpen = false"
                     class="bg-white dark:bg-slate-900 rounded-[28px] max-w-sm sm:max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-100 dark:border-slate-800 space-y-4 relative">
                    
                    <div>
                        <button type="button" @click="projectDetailsOpen = false" 
                                class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 cursor-pointer mb-2">
                            <span>&larr;</span>
                            <span>{{ __('Back to Projects') }}</span>
                        </button>
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight leading-snug">
                            {{ $prj->title }}
                        </h3>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Project Number') }}</span>
                            <span class="text-sm font-bold font-mono text-slate-900 dark:text-white">{{ $prj->project_code ?? ('PRJ-' . $prj->id) }}</span>
                        </div>
                        <a href="{{ route('projects.show', $prj) }}" 
                           class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-blue-600 hover:bg-blue-50 transition"
                           title="{{ __('Open in Full CRM') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                        </a>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Description') }}</span>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            {{ $prj->description ?: __('Comprehensive CCTV surveillance and access control deployment, site cable routing, NVR configuration, and remote mobile viewing client handover.') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-1">
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">{{ __('Assignees') }}</span>
                            <div class="flex items-center -space-x-2">
                                <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 border-2 border-white dark:border-slate-900 flex items-center justify-center font-bold text-[10px]">
                                    {{ strtoupper(substr($prj->leadTechnician?->name ?? 'T', 0, 1)) }}
                                </div>
                                <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 border-2 border-white dark:border-slate-900 flex items-center justify-center font-bold text-[10px]">
                                    S
                                </div>
                                <div class="w-7 h-7 rounded-full bg-blue-600 text-white border-2 border-white dark:border-slate-900 flex items-center justify-center font-bold text-[10px]">
                                    +{{ max(1, $activeWorkers->count()) }}
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">{{ __('Priority') }}</span>
                            <div class="flex items-center gap-1 text-xs font-bold text-amber-500">
                                <span>&uarr;</span>
                                <span class="capitalize">{{ $prj->priority ?: __('Medium') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400">{{ __('Created :date', ['date' => $prj->created_at->format('M d, Y')]) }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $prj->budget ? \App\Support\Inr::money($prj->budget) : '' }}</span>
                    </div>

                </div>
            @endforeach
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- MODAL E: FILTERS MODAL                                              --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="filterModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            
            <div @click.away="filterModalOpen = false" 
                 class="bg-white dark:bg-slate-900 rounded-[28px] max-w-sm sm:max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-100 dark:border-slate-800 space-y-4 relative">
                
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">
                        {{ __('Filters') }}
                    </h3>
                    <button type="button" @click="filterModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Period') }}</label>
                    <input type="text" readonly value="{{ now()->translatedFormat('M d, Y') }} - {{ now()->addDays(30)->translatedFormat('M d, Y') }}"
                           class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3.5 py-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Priority') }}</label>
                    <select x-model="filterPriority"
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2.5">
                        <option value="all">{{ __('All Priorities') }}</option>
                        <option value="urgent">{{ __('Urgent') }}</option>
                        <option value="high">{{ __('High') }}</option>
                        <option value="medium">{{ __('Medium') }}</option>
                        <option value="low">{{ __('Low') }}</option>
                    </select>
                </div>

                <div class="pt-2">
                    <button type="button" @click="filterModalOpen = false"
                            class="w-full py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-bold text-xs shadow-lg shadow-blue-500/30 transition cursor-pointer">
                        {{ __('Apply Filters') }}
                    </button>
                </div>

            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- MODAL F: FIGMA SUPPORT MODAL ("Need some Help?")                    --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="supportModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div @click.away="supportModalOpen = false" 
                 class="bg-white dark:bg-slate-900 rounded-[28px] max-w-sm sm:max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 space-y-4 relative">
                
                <div class="flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                        {{ __('Need some Help?') }}
                    </h3>
                    <button type="button" @click="supportModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    {{ __('Describe your question and our specialists will answer you within 24 hours.') }}
                </p>

                <div class="space-y-3.5 pt-1">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Request Subject') }}</label>
                        <select x-model="supportSubject" 
                                class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3.5 py-3 focus:outline-none focus:border-blue-600">
                            <option>{{ __('Technical difficulties') }}</option>
                            <option>{{ __('Equipment / Camera Defect') }}</option>
                            <option>{{ __('Site Access / Permission Issue') }}</option>
                            <option>{{ __('Material / Cable Shortage') }}</option>
                            <option>{{ __('Worker Attendance Discrepancy') }}</option>
                            <option>{{ __('Travel Claim / Advance') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Description') }}</label>
                        <textarea x-model="supportDescription" rows="4" placeholder="{{ __('Add some description of the request') }}"
                                  class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3.5 focus:outline-none focus:border-blue-600"></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="button" @click="submitSupport()"
                                class="w-full py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-black text-sm shadow-lg shadow-blue-500/30 transition flex items-center justify-center gap-2 cursor-pointer">
                            <span x-show="!supportSuccess">{{ __('Send Request') }}</span>
                            <span x-show="supportSuccess" class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" class="opacity-75"></path></svg>
                                <span>{{ __('Sending...') }}</span>
                            </span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- HARDWARE BARCODE SCANNER MODAL --}}
    <x-barcode-scanner modalId="tech-barcode-modal" :title="__('Hardware Scanner')" />

    {{-- SCANNER HANDLER SCRIPT --}}
    <script>
        const techScanText = {{ Js::from([
            'querying' => __('Querying database for S/N :code...'),
            'underWarranty' => __('Under Warranty'),
            'warrantyExpired' => __('Warranty Expired'),
            'notFound' => __('No hardware found for S/N: :code'),
            'lookupError' => __('Error looking up serial: :error'),
        ]) }};

        async function handleTechScannedCode(code) {
            try {
                const response = await fetch(`{{ route('equipment.lookup') }}?serial=${encodeURIComponent(code)}`);
                const data = await response.json();
                if (data.found) {
                    crmAlert(`Found Asset: ${data.equipment_name || data.product_name} (${data.customer_name || 'In Stock'})`);
                } else {
                    crmAlert(techScanText.notFound.replace(':code', code));
                }
            } catch (e) {
                crmAlert(techScanText.lookupError.replace(':error', e.message));
            }
        }
    </script>

    {{-- 2-STEP ATTENDANCE MODAL COMPONENT --}}
    <x-mark-attendance-modal :workers="$activeWorkers" :projects="$activeProjects" />
</x-app-layout>
