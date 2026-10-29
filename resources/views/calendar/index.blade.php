<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-[24px] font-semibold text-slate-900 dark:text-white leading-tight">Calendar and Tasks</h1>
            </div>
            
            <div class="flex items-center gap-2 flex-wrap">
                @if($canManageTasks)
                    <button type="button" @click="window.dispatchEvent(new CustomEvent('open-create-task-modal'))" 
                            class="inline-flex items-center justify-center px-3.5 py-2 rounded-[4px] text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition active:scale-95 cursor-pointer">
                        Create Task
                    </button>
                    <a href="{{ route('site-surveys.create') }}" class="inline-flex items-center justify-center px-3.5 py-2 rounded-[4px] text-xs font-semibold bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/90 dark:border-slate-700 shadow-2xs transition active:scale-95">
                        Schedule Survey
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    {{-- Safe JavaScript Data Initialization --}}
    <script>
        window.crmCalendarConfig = {
            initialMonth: '{{ $currentMonth->format("Y-m") }}',
            initialEvents: {!! json_encode($isWorker ? $myEvents : $overallEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
            overallEvents: {!! json_encode($overallEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
            myEvents: {!! json_encode($myEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
            technicians: {!! json_encode($assignees, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
            todayDate: '{{ now()->format("Y-m-d") }}',
            activeTab: '{{ $activeTab }}',
            isWorker: {{ $isWorker ? 'true' : 'false' }},
            canManageTasks: {{ $canManageTasks ? 'true' : 'false' }}
        };
    </script>

    <style>
        .crm-calendar-app,
        .crm-calendar-app * {
            font-family: 'Roboto', sans-serif !important;
        }
        .crm-calendar-app {
            font-size: 14px;
        }

        .crm-cal-input {
            background-color: #ffffff;
            color: var(--text-main);
            border: 1px solid var(--card-border) !important;
            padding-left: 36px !important;
            height: 38px !important;
            font-size: 14px !important;
            border-radius: 4px !important;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .crm-cal-select {
            background-color: #ffffff;
            color: var(--text-main);
            border: 1px solid var(--card-border) !important;
            width: auto !important;
            min-width: 140px !important;
            height: 38px !important;
            font-size: 14px !important;
            border-radius: 4px !important;
            display: inline-block !important;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .crm-cal-input:focus,
        .crm-cal-select:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.28) !important;
            outline: none !important;
        }
        html.dark .crm-cal-input {
            background-color: #0b132b !important;
            color: #f8fafc !important;
            border: 1px solid #475569 !important;
        }
        html.dark .crm-cal-select {
            background-color: #0b132b !important;
            color: #f8fafc !important;
            border: 1px solid #475569 !important;
        }
        html.dark .crm-cal-select option {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }

        /* ══════════════════════════════════════════════════════════════════════
           FOUR PLAIN TEXT TABS (No pills, active tab: 2px #2563eb bottom border)
           ══════════════════════════════════════════════════════════════════════ */
        .crm-tab-container {
            border-bottom: 1px solid var(--card-border, #cbd5e1);
        }
        html.dark .crm-tab-container {
            border-bottom: 1px solid #334155;
        }
        .crm-plain-tab,
        .crm-plain-tab.active,
        .crm-plain-tab:hover,
        .crm-plain-tab:focus,
        .crm-plain-tab:focus-visible,
        .crm-plain-tab:active {
            outline: none !important;
            outline-offset: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            background-color: transparent !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            border-radius: 0 !important;
        }
        .crm-plain-tab {
            position: relative;
            border-bottom: 2px solid transparent !important;
            padding: 8px 6px 12px 6px;
            font-size: 14px;
            font-weight: 500;
            color: #64748b;
            cursor: pointer;
            white-space: nowrap;
            transition: color 0.15s ease, border-color 0.15s ease;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            -webkit-tap-highlight-color: transparent;
        }
        .crm-plain-tab:hover {
            color: #0f172a;
        }
        html.dark .crm-plain-tab {
            color: #94a3b8;
        }
        html.dark .crm-plain-tab:hover {
            color: #f8fafc;
        }
        .crm-plain-tab.active {
            color: #2563eb !important;
            font-weight: 600 !important;
            border-bottom: 2px solid #2563eb !important;
        }
        html.dark .crm-plain-tab.active {
            color: #3b82f6 !important;
            border-bottom: 2px solid #2563eb !important;
        }
    </style>

    <div class="space-y-4 crm-calendar-app" x-data="calendarHubApp(window.crmCalendarConfig)" @open-create-task-modal.window="openCreateTaskModal = true">

        {{-- Flash Notification Alerts --}}
        @if (session('status'))
            <div class="p-3.5 rounded-[4px] bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 cursor-pointer">&times;</button>
            </div>
        @endif

        @if (session('error') || $errors->any())
            <div class="p-3.5 rounded-[4px] bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-semibold space-y-1">
                @if(session('error'))
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @foreach ($errors->all() as $err)
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $err }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════
             FOUR PLAIN TEXT TABS (No pills, active tab: 2px #2563eb bottom border)
             Workers must NOT see Overall tabs (server-side gated)
             ══════════════════════════════════════════════════════════════ --}}
        <div class="crm-tab-container">
            <div class="flex items-center gap-8 -mb-px overflow-x-auto scrollbar-none">
                
                {{-- 1. Overall Calendar --}}
                @if(!$isWorker)
                    <button type="button" 
                            @click="switchTab('overall_calendar')" 
                            class="crm-plain-tab"
                            :class="activeTab === 'overall_calendar' ? 'active' : ''">
                        Overall Calendar
                    </button>
                @endif

                {{-- 2. My Calendar --}}
                <button type="button" 
                        @click="switchTab('my_calendar')" 
                        class="crm-plain-tab"
                        :class="activeTab === 'my_calendar' ? 'active' : ''">
                    My Calendar
                </button>

                {{-- 3. Overall Tasks --}}
                @if(!$isWorker)
                    <button type="button" 
                            @click="switchTab('overall_tasks')" 
                            class="crm-plain-tab"
                            :class="activeTab === 'overall_tasks' ? 'active' : ''">
                        Overall Tasks
                    </button>
                @endif

                {{-- 4. My Tasks --}}
                <button type="button" 
                        @click="switchTab('my_tasks')" 
                        class="crm-plain-tab"
                        :class="activeTab === 'my_tasks' ? 'active' : ''">
                    My Tasks
                </button>

            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════
             TAB 1 & TAB 2: CALENDAR VIEWS (Overall Calendar & My Calendar)
             ══════════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'overall_calendar' || activeTab === 'my_calendar'" class="space-y-4">
            
            {{-- Stat Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
                <div class="stat-card bg-white dark:bg-[#0f172a] rounded-[4px] border border-[var(--card-border)] dark:border-slate-700 p-4 shadow-xs">
                    <span class="text-xs font-normal text-gray-500 dark:text-slate-400 block" x-text="activeTab === 'my_calendar' ? 'My Scheduled Items' : 'Scheduled Operations'">Scheduled Operations</span>
                    <div class="text-[24px] font-semibold tabular-nums text-slate-900 dark:text-white mt-1" x-text="events.length">
                        {{ $stats['total'] }}
                    </div>
                </div>

                <div class="stat-card bg-white dark:bg-[#0f172a] rounded-[4px] border border-[var(--card-border)] dark:border-slate-700 p-4 shadow-xs">
                    <span class="text-xs font-normal text-gray-500 dark:text-slate-400 block">Scheduled Today</span>
                    <div class="text-[24px] font-semibold tabular-nums text-slate-900 dark:text-white mt-1" x-text="todayEvents.length">
                        {{ $stats['today'] }}
                    </div>
                </div>

                <div class="stat-card bg-white dark:bg-[#0f172a] rounded-[4px] border border-[var(--card-border)] dark:border-slate-700 p-4 shadow-xs">
                    <span class="text-xs font-normal text-gray-500 dark:text-slate-400 block">My Assigned Tasks</span>
                    <div class="text-[24px] font-semibold tabular-nums text-slate-900 dark:text-white mt-1">
                        {{ $stats['my_tasks_total'] }}
                    </div>
                </div>

                <div class="stat-card bg-white dark:bg-[#0f172a] rounded-[4px] border border-[var(--card-border)] dark:border-slate-700 p-4 shadow-xs">
                    <span class="text-xs font-normal text-gray-500 dark:text-slate-400 block">Pending Actions</span>
                    <div class="text-[24px] font-semibold tabular-nums text-slate-900 dark:text-white mt-1">
                        {{ $stats['my_pending'] }}
                    </div>
                </div>
            </div>

            {{-- Toolbar --}}
            <div class="bg-white dark:bg-[#0f172a] rounded-[4px] border border-[var(--card-border)] dark:border-slate-700 p-2.5 shadow-xs flex flex-wrap items-center justify-between gap-2.5">
                <div class="flex items-center gap-2 flex-wrap flex-1 min-w-0">
                    <div class="relative w-64 min-w-[180px] shrink-0">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7"/><line x1="21" x2="16.65" y1="21" y2="16.65"/>
                        </svg>
                        <input aria-label="Filter events" type="text" x-model="searchQuery" placeholder="Filter events..." 
                               class="crm-cal-input w-full h-9 pl-9 pr-3 text-xs rounded-[4px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:border-blue-500 transition"
                               style="padding-left: 36px !important;">
                    </div>

                    @if(!$isWorker)
                        <select aria-label="Tech" x-model="filterTech"
                                class="crm-cal-select h-9 px-3 text-xs rounded-[4px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500 transition cursor-pointer shrink-0">
                            <option value="all">All Assignees</option>
                            @foreach($assignees as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }} ({{ ucfirst($tech->role) }})</option>
                            @endforeach
                        </select>
                    @endif

                    <select aria-label="Status" x-model="filterStatus"
                            class="crm-cal-select h-9 px-3 text-xs rounded-[4px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500 transition cursor-pointer shrink-0">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="rejected">Rejected</option>
                    </select>

                    <button type="button" 
                            x-show="searchQuery !== '' || filterTech !== 'all' || filterStatus !== 'all'"
                            @click="searchQuery = ''; filterTech = 'all'; filterStatus = 'all'"
                            class="h-9 px-3 text-xs rounded-[4px] border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 transition shrink-0 inline-flex items-center gap-1 font-semibold cursor-pointer">
                        <span>Reset</span>
                    </button>
                </div>

                <div class="inline-flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-[4px] border border-slate-200/60 dark:border-slate-700 shrink-0">
                    <button type="button" @click="currentView = 'month'"
                            :class="currentView === 'month' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 font-medium'"
                            class="h-7 px-3 text-xs rounded-[4px] transition cursor-pointer">
                        Month
                    </button>
                    <button type="button" @click="currentView = 'week'"
                            :class="currentView === 'week' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 font-medium'"
                            class="h-7 px-3 text-xs rounded-[4px] transition cursor-pointer">
                        Week
                    </button>
                    <button type="button" @click="currentView = 'agenda'"
                            :class="currentView === 'agenda' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 font-medium'"
                            class="h-7 px-3 text-xs rounded-[4px] transition cursor-pointer">
                        Agenda
                    </button>
                </div>
            </div>

            {{-- Main Split View: Calendar Views + Right Side Today's Jobs Panel --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                
                {{-- Main Calendar Column (8 cols on lg) --}}
                <div class="lg:col-span-8 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2.5 px-0.5">
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="prevMonth()" class="h-8 px-2.5 rounded-[4px] border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1 cursor-pointer" title="Previous Month">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                <span>Prev</span>
                            </button>
                            <button type="button" @click="goToToday()" class="h-8 px-3 rounded-[4px] border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                                Today
                            </button>
                            <button type="button" @click="nextMonth()" class="h-8 px-2.5 rounded-[4px] border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1 cursor-pointer" title="Next Month">
                                <span>Next</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>

                        <h2 class="text-base font-bold text-slate-900 dark:text-white" x-text="currentView === 'week' ? weekRangeText : monthTitle"></h2>
                    </div>

                    {{-- Month View Grid --}}
                    <div x-show="currentView === 'month'" class="bg-white dark:bg-[#0f172a] rounded-[4px] border border-[var(--card-border)] dark:border-slate-700 overflow-hidden shadow-xs">
                        <div class="grid grid-cols-7 border-b border-slate-200/80 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 text-center">
                            <template x-for="day in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" :key="day">
                                <div class="py-2 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider" x-text="day"></div>
                            </template>
                        </div>
                        <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-slate-100 dark:divide-slate-800">
                            <template x-for="(cell, index) in calendarDays" :key="index">
                                <div class="min-h-[92px] p-1.5 flex flex-col justify-between transition-colors relative"
                                     :class="{
                                         'bg-[#fefce8] dark:bg-amber-950/30': cell.isToday,
                                         'bg-white dark:bg-[#0f172a]': cell.isCurrentMonth && !cell.isToday,
                                         'bg-slate-50/60 dark:bg-slate-900/40 text-slate-400 dark:text-slate-600': !cell.isCurrentMonth && !cell.isToday
                                     }">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold leading-none"
                                              :class="cell.isCurrentMonth ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400 dark:text-slate-600'"
                                              x-text="cell.dayNumber">
                                        </span>
                                        <template x-if="getDayEvents(cell.date).length > 2">
                                            <span class="text-[11px] font-bold text-slate-400 cursor-pointer"
                                                  @click="openDaySummary(cell)"
                                                  x-text="'+' + (getDayEvents(cell.date).length - 2)">
                                            </span>
                                        </template>
                                    </div>

                                    <div class="space-y-1 mt-1 overflow-hidden">
                                        <template x-for="(event, eIndex) in getDayEvents(cell.date).slice(0, 2)" :key="eIndex">
                                            <div @click="openDrawer(event)"
                                                 class="px-1.5 py-0.5 text-[11px] font-medium truncate cursor-pointer transition block border-l-2 border-l-blue-600 bg-blue-50 dark:bg-blue-950/60 text-blue-900 dark:text-blue-200 rounded-none leading-snug"
                                                 :title="event.title">
                                                <span class="truncate block" x-text="event.title"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Agenda View --}}
                    <div x-show="currentView === 'agenda'" class="bg-white dark:bg-[#0f172a] rounded-[4px] border border-[var(--card-border)] dark:border-slate-700 p-4 shadow-xs space-y-3">
                        <template x-if="filteredEventsList.length === 0">
                            <div class="py-12 text-center text-slate-400 text-xs font-medium">
                                No scheduled operations match the current filter.
                            </div>
                        </template>
                        <template x-for="event in filteredEventsList" :key="event.id">
                            <div @click="openDrawer(event)" 
                                 class="p-3 rounded-[4px] border border-slate-100 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-600 bg-slate-50/50 dark:bg-slate-800/40 transition cursor-pointer flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="text-center w-14 shrink-0 py-1 px-2 rounded-[4px] bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600">
                                        <div class="text-[11px] font-bold text-slate-400 uppercase" x-text="new Date(event.date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short' })"></div>
                                        <div class="text-base font-extrabold text-slate-900 dark:text-white" x-text="new Date(event.date + 'T00:00:00').getDate()"></div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-white" x-text="event.title"></div>
                                        <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                            <span x-text="event.time"></span>
                                            <span x-show="event.customer">&bull;</span>
                                            <span x-show="event.customer" x-text="event.customer"></span>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-[4px] border uppercase" :class="getEventChipClasses(event)" x-text="event.status"></span>
                            </div>
                        </template>
                    </div>

                </div>

                {{-- Right Column: Today's Schedule Panel (4 cols on lg) --}}
                <div class="lg:col-span-4 space-y-3">
                    <div class="bg-white dark:bg-[#0f172a] rounded-[4px] border border-[var(--card-border)] dark:border-slate-700 p-4 shadow-xs">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Today's Schedule</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ now()->format('l, d M Y') }}</p>
                            </div>
                        </div>

                        <div class="mt-3 space-y-2.5 max-h-[500px] overflow-y-auto">
                            <template x-if="todayEvents.length === 0">
                                <div class="py-10 text-center text-slate-400 text-xs">
                                    No items scheduled for today.
                                </div>
                            </template>
                            <template x-for="event in todayEvents" :key="event.id">
                                <div @click="openDrawer(event)" 
                                     class="p-3 rounded-[4px] border border-slate-200/80 dark:border-slate-700/80 hover:border-blue-500 transition cursor-pointer bg-slate-50/40 dark:bg-slate-800/30">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-bold text-blue-600 dark:text-blue-400" x-text="event.time"></span>
                                        <span class="font-semibold uppercase tracking-wider text-[11px] px-1.5 py-0.5 rounded-[4px]" :class="getEventChipClasses(event)" x-text="event.status"></span>
                                    </div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white mt-1" x-text="event.title"></div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-start gap-1" x-show="event.address">
                                        <svg class="w-3.5 h-3.5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                        <span class="truncate" x-text="event.address"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════════════
             TAB 3 & TAB 4: TASKS HUB (Overall Tasks & My Tasks)
             Grouped by Date: Overdue, Today, Tomorrow, This week, Later
             With filters for search, status, category, assignee, from & to date
             ══════════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'overall_tasks' || activeTab === 'my_tasks'" class="space-y-4">
            
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white" x-text="activeTab === 'overall_tasks' ? 'Company Tasks Workflow' : 'My Assigned Tasks'"></h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="activeTab === 'overall_tasks' ? 'Grouped by scheduled date with full operations filtering' : 'Tasks assigned directly to you — accept or reject work items'"></p>
                </div>
                @if($canManageTasks)
                    <button type="button" @click="openCreateTaskModal = true" 
                            class="inline-flex items-center justify-center px-3.5 py-2 rounded-[4px] text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition active:scale-95 cursor-pointer">
                        Create Task
                    </button>
                @endif
            </div>

            {{-- Tasks Filter Bar --}}
            <div class="bg-white dark:bg-[#0f172a] rounded-[4px] border border-[var(--card-border)] dark:border-slate-700 p-4 shadow-xs">
                <form method="GET" action="{{ route('calendar.index') }}" class="flex items-end gap-3 flex-wrap">
                    <input type="hidden" name="tab" :value="activeTab">

                    {{-- Search filter --}}
                    <div class="flex-1 min-w-[220px]">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Search</label>
                        <div class="relative">
                            <input aria-label="Search" type="text" name="search" value="{{ $filters['search'] }}" placeholder="Task, scope, or location..."
                                   class="crm-cal-input w-full h-9 pl-9 pr-3 text-xs rounded-[4px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:border-blue-500 transition"
                                   style="padding-left: 36px !important;">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><line x1="21" x2="16.65" y1="21" y2="16.65"/></svg>
                        </div>
                    </div>

                    {{-- Status filter --}}
                    <div class="w-36 shrink-0">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Status</label>
                        <select aria-label="Status" name="status" class="crm-cal-select w-full h-9 px-3 text-xs rounded-[4px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500 transition">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $k => $label)
                                <option value="{{ $k }}" {{ $filters['status'] === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Category / Phase filter --}}
                    <div class="w-36 shrink-0">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Category</label>
                        <select aria-label="Category" name="category" class="crm-cal-select w-full h-9 px-3 text-xs rounded-[4px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500 transition">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ $filters['category'] === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Assignee filter (hidden in My Tasks) --}}
                    <div x-show="activeTab === 'overall_tasks'" class="w-44 shrink-0">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Assignee</label>
                        <select aria-label="Assignee" name="assignee" class="crm-cal-select w-full h-9 px-3 text-xs rounded-[4px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500 transition">
                            <option value="">All Team Members</option>
                            @foreach($assignees as $assignee)
                                <option value="{{ $assignee->id }}" {{ $filters['assignee'] == $assignee->id ? 'selected' : '' }}>
                                    {{ $assignee->name }} ({{ ucfirst($assignee->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- From Date --}}
                    <div class="w-36 shrink-0">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">From Date</label>
                        <input aria-label="From Date" type="date" name="from_date" value="{{ $filters['from_date'] }}" class="crm-cal-input w-full h-9 px-2 text-xs rounded-[4px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-800 dark:text-white">
                    </div>

                    {{-- To Date --}}
                    <div class="w-36 shrink-0">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">To Date</label>
                        <input aria-label="To Date" type="date" name="to_date" value="{{ $filters['to_date'] }}" class="crm-cal-input w-full h-9 px-2 text-xs rounded-[4px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-800 dark:text-white">
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="submit" class="h-9 px-4 rounded-[4px] bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition shadow-xs cursor-pointer inline-flex items-center justify-center">
                            Filter
                        </button>
                        <a :href="'{{ route('calendar.index') }}?tab=' + activeTab" class="h-9 px-3.5 rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center justify-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- Task Groups Renderer (Overall Tasks & My Tasks) --}}
            @if(!$isWorker)
                <div x-show="activeTab === 'overall_tasks'">
                    @include('calendar.partials.task-group-list', [
                        'groupList' => $overallTasksGrouped,
                        'tabName'   => 'overall_tasks',
                        'canManageTasks'   => $canManageTasks,
                        'isWorker'  => false,
                    ])
                </div>
            @endif

            <div x-show="activeTab === 'my_tasks'">
                @include('calendar.partials.task-group-list', [
                    'groupList' => $myTasksGrouped,
                    'tabName'   => 'my_tasks',
                    'canManageTasks'   => $canManageTasks,
                    'isWorker'  => $isWorker,
                ])
            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════════════
             MODALS: REJECT TASK (REASON REQUIRED)
             ══════════════════════════════════════════════════════════════ --}}
        <div x-show="rejectModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="rejectModalOpen = false" 
                 class="bg-white dark:bg-[#0f172a] rounded-[4px] border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-md overflow-hidden">
                
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="text-rose-500"><svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                        <span>Reject Task &amp; Return to Admin</span>
                    </h3>
                    <button type="button" @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
                </div>

                <form :action="'/calendar/tasks/' + (activeRejectTask ? activeRejectTask.id : '') + '/reject'" method="POST" class="p-6 space-y-4">
                    @csrf

                    <div>
                        <div class="text-xs font-semibold text-slate-500 mb-1">Task Title</div>
                        <div class="text-sm font-bold text-slate-900 dark:text-white" x-text="activeRejectTask ? activeRejectTask.title : ''"></div>
                    </div>

                    <div>
                        <label for="rejection_reason" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Reason for Rejection <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="rejection_reason" 
                                  name="rejection_reason" 
                                  rows="3" 
                                  required 
                                  minlength="3" 
                                  placeholder="Provide clear reason (e.g. Schedule conflict with another installation, tool missing, medical leave)..."
                                  class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-3 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500"></textarea>
                        <p class="text-[11px] text-slate-400 mt-1">This reason will be logged and the task will be sent back to the administrator for reassignment.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 rounded-[4px] border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-[4px] bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                            Confirm Rejection &amp; Return
                        </button>
                    </div>
                </form>

            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════
             MODALS: ADMIN REASSIGN TASK
             ══════════════════════════════════════════════════════════════ --}}
        @if($canManageTasks)
            <div x-show="reassignModalOpen" 
                 x-cloak 
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div @click.away="reassignModalOpen = false" 
                     class="bg-white dark:bg-[#0f172a] rounded-[4px] border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-md overflow-hidden">
                    
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span><svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                            <span>Assign / Reassign Task</span>
                        </h3>
                        <button type="button" @click="reassignModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
                    </div>

                    <form :action="'/calendar/tasks/' + (activeReassignTask ? activeReassignTask.id : '') + '/assign'" method="POST" class="p-6 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <div class="text-xs font-semibold text-slate-500 mb-0.5">Task</div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white" x-text="activeReassignTask ? activeReassignTask.title : ''"></div>
                        </div>

                        <template x-if="activeReassignTask && activeReassignTask.rejection_reason">
                            <div class="p-3 rounded-[4px] bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-xs text-rose-800 dark:text-rose-200">
                                <span class="font-bold">Previous Rejection Reason:</span>
                                <p class="mt-0.5 italic" x-text="activeReassignTask.rejection_reason"></p>
                            </div>
                        </template>

                        <div>
                            <label for="assignee_select" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Assignee (Technician, Employee, Worker)
                            </label>
                            <select id="assignee_select" name="assigned_to" required
                                    class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5 focus:outline-none focus:border-blue-500">
                                <option value="">-- Choose Assignee --</option>
                                @foreach($assignees as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="shift_select" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Shift</label>
                            <select id="shift_select" name="shift"
                                    class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5 focus:outline-none focus:border-blue-500">
                                @foreach($shifts as $k => $label)
                                    <option value="{{ $k }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="loc_input" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Location</label>
                            <input id="loc_input" type="text" name="location" :value="activeReassignTask ? activeReassignTask.location : ''" placeholder="Site location / work floor..."
                                   class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5 focus:outline-none focus:border-blue-500">
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-2">
                            <button type="button" @click="reassignModalOpen = false" class="px-4 py-2 rounded-[4px] border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-[4px] bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                                Save Assignment
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════════
                 MODALS: ADMIN CREATE TASK
                 ══════════════════════════════════════════════════════════════ --}}
            <div x-show="openCreateTaskModal" 
                 x-cloak 
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div @click.away="openCreateTaskModal = false" 
                     class="bg-white dark:bg-[#0f172a] rounded-[4px] border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                    
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between sticky top-0 bg-white dark:bg-[#0f172a] z-10">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>✨</span>
                            <span>Create Company Task</span>
                        </h3>
                        <button type="button" @click="openCreateTaskModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
                    </div>

                    <form action="{{ route('calendar.tasks.store') }}" method="POST" class="p-6 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Project <span class="text-rose-500">*</span>
                            </label>
                            <select aria-label="Project" name="project_id" required class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5">
                                <option value="">-- Choose Associated Project --</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->company_name ?? 'Client' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Work Title <span class="text-rose-500">*</span>
                            </label>
                            <input aria-label="Work Title" type="text" name="title" required placeholder="e.g. Cat6 Cabling & Conduit Routing on 2nd Floor"
                                   class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Category / Phase</label>
                            <input aria-label="Category / Phase" type="text" name="phase_name" placeholder="e.g. Cabling, Installation, NVR Setup, Testing..."
                                   class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Work Description / Instructions</label>
                            <textarea aria-label="Work Description / Instructions" name="description" rows="2" placeholder="Specific technical guidelines, tools required, or scope details..."
                                      class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Assignee</label>
                                <select aria-label="Assignee" name="assigned_to" class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5">
                                    <option value="">-- Unassigned --</option>
                                    @foreach($assignees as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Shift</label>
                                <select aria-label="Shift" name="shift" class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5">
                                    @foreach($shifts as $k => $label)
                                        <option value="{{ $k }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Start Date</label>
                                <input aria-label="Start Date" type="date" name="start_date" value="{{ now()->format('Y-m-d') }}"
                                       class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Due Date</label>
                                <input aria-label="Due Date" type="date" name="due_date" value="{{ now()->format('Y-m-d') }}"
                                       class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Estimated Hours</label>
                                <input aria-label="Estimated Hours" type="number" step="0.5" name="estimated_hours" value="4.0"
                                       class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Priority</label>
                                <select aria-label="Priority" name="priority" class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                                <select aria-label="Status" name="status" class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5">
                                    <option value="pending" selected>Pending</option>
                                    <option value="in_progress">In Progress</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Location / Site Address</label>
                            <input aria-label="Location / Site Address" type="text" name="location" placeholder="e.g. Server Room, Ground Floor, Main Block"
                                   class="w-full text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white p-2.5">
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="openCreateTaskModal = false" class="px-4 py-2 rounded-[4px] border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-[4px] bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                                Create &amp; Dispatch Task
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════
             EVENT DETAILS SLIDE-OVER DRAWER
             ══════════════════════════════════════════════════════════════ --}}
        <div x-show="drawerOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex justify-end bg-slate-900/40 backdrop-blur-2xs"
             @keydown.window.escape="closeDrawer()">
            
            <div @click.away="closeDrawer()" 
                 class="w-full max-w-md bg-white dark:bg-[#0f172a] h-full shadow-2xl border-l border-slate-200 dark:border-slate-800 flex flex-col justify-between">
                
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider" x-text="selectedEvent ? selectedEvent.badge : ''"></span>
                    <button aria-label="Close" type="button" @click="closeDrawer()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 overflow-y-auto flex-1" x-show="selectedEvent">
                    <div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white" x-text="selectedEvent ? selectedEvent.title : ''"></h4>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 font-semibold text-slate-700 dark:text-slate-300">
                                <span x-text="selectedEvent ? selectedEvent.date : ''"></span>
                            </span>
                            <span>&bull;</span>
                            <span x-text="selectedEvent ? selectedEvent.time : ''"></span>
                        </div>
                    </div>

                    {{-- Customer details (HIDDEN for workers) --}}
                    <div class="p-4 rounded-[4px] border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 text-xs space-y-2"
                         x-show="selectedEvent && selectedEvent.customer && !isWorker">
                        <div class="font-bold text-slate-500 uppercase text-[11px]">Client Information</div>
                        <div class="font-bold text-slate-900 dark:text-white text-sm" x-text="selectedEvent ? selectedEvent.customer : ''"></div>
                        <div class="text-slate-600 dark:text-slate-300" x-show="selectedEvent && selectedEvent.phone" x-text="selectedEvent ? selectedEvent.phone : ''"></div>
                    </div>

                    {{-- Location / Site Address --}}
                    <div class="p-4 rounded-[4px] border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 text-xs space-y-1.5" x-show="selectedEvent && selectedEvent.address">
                        <div class="font-bold text-slate-500 uppercase text-[11px]">Site Location</div>
                        <div class="text-slate-700 dark:text-slate-200 font-medium" x-text="selectedEvent ? selectedEvent.address : ''"></div>
                    </div>

                    {{-- Notes / Work Scope --}}
                    <div class="p-4 rounded-[4px] border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 text-xs space-y-1.5" x-show="selectedEvent && selectedEvent.notes">
                        <div class="font-bold text-slate-500 uppercase text-[11px]">Work Scope &amp; Notes</div>
                        <p class="text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed" x-text="selectedEvent ? selectedEvent.notes : ''"></p>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/90 flex items-center justify-between gap-3">
                    <button type="button" @click="closeDrawer()" class="px-3.5 py-2 rounded-[4px] border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-800 transition cursor-pointer">
                        Close
                    </button>
                    <template x-if="selectedEvent && selectedEvent.url">
                        <a :href="selectedEvent.url" class="px-4 py-2 rounded-[4px] bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-sm transition inline-flex items-center gap-1.5">
                            <span>Open Details</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </template>
                </div>

            </div>
        </div>

    </div>

    {{-- Alpine.js Application Logic --}}
    <script>
        function calendarHubApp(config) {
            return {
                activeTab: config ? config.activeTab : 'overall_calendar',
                isWorker: config ? config.isWorker : false,
                canManageTasks: config ? config.canManageTasks : false,
                
                currentMonth: config ? config.initialMonth : '{{ now()->format("Y-m") }}',
                overallEvents: (config && config.overallEvents) ? config.overallEvents : [],
                myEvents: (config && config.myEvents) ? config.myEvents : [],
                events: (config && config.initialEvents) ? config.initialEvents : [],
                technicians: (config && config.technicians) ? config.technicians : [],
                todayDate: config ? config.todayDate : '{{ now()->format("Y-m-d") }}',
                
                currentView: 'month',
                filterCategory: 'all',
                filterTech: 'all',
                filterStatus: 'all',
                searchQuery: '',
                
                monthTitle: '',
                calendarDays: [],
                activeWeekDays: [],
                filteredEventsList: [],
                todayEvents: [],
                
                drawerOpen: false,
                selectedEvent: null,

                // Task Modals
                openCreateTaskModal: false,
                reassignModalOpen: false,
                activeReassignTask: null,
                rejectModalOpen: false,
                activeRejectTask: null,

                init() {
                    this.updateEventsForCurrentTab();
                    this.recalculateAll();
                    this.$watch('filterCategory', () => this.filterEvents());
                    this.$watch('filterTech', () => this.filterEvents());
                    this.$watch('filterStatus', () => this.filterEvents());
                    this.$watch('searchQuery', () => this.filterEvents());
                },

                switchTab(tab) {
                    if (this.isWorker && (tab === 'overall_calendar' || tab === 'overall_tasks')) {
                        return;
                    }
                    this.activeTab = tab;
                    this.updateEventsForCurrentTab();
                    this.recalculateAll();

                    if (window.history.replaceState) {
                        const url = new URL(window.location);
                        url.searchParams.set('tab', tab);
                        window.history.replaceState({}, '', url);
                    }
                },

                updateEventsForCurrentTab() {
                    if (this.activeTab === 'my_calendar' || this.activeTab === 'my_tasks' || this.isWorker) {
                        this.events = this.myEvents;
                    } else {
                        this.events = this.overallEvents;
                    }
                },

                recalculateAll() {
                    this.updateMonthTitle();
                    this.updateCalendarDays();
                    this.updateActiveWeekDays();
                    this.filterEvents();
                    this.todayEvents = this.events.filter(e => e.date === this.todayDate);
                },

                updateMonthTitle() {
                    if (!this.currentMonth) return;
                    const parts = this.currentMonth.split('-');
                    const date = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, 1);
                    this.monthTitle = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                },

                updateCalendarDays() {
                    if (!this.currentMonth) return;
                    const parts = this.currentMonth.split('-');
                    const year = parseInt(parts[0]);
                    const month = parseInt(parts[1]) - 1;

                    const firstDayOfMonth = new Date(year, month, 1);
                    const startDayOffset = (firstDayOfMonth.getDay() + 6) % 7;

                    const days = [];
                    const startDate = new Date(year, month, 1 - startDayOffset);

                    for (let i = 0; i < 35; i++) {
                        const cellDate = new Date(startDate);
                        cellDate.setDate(startDate.getDate() + i);

                        const y = cellDate.getFullYear();
                        const m = String(cellDate.getMonth() + 1).padStart(2, '0');
                        const d = String(cellDate.getDate()).padStart(2, '0');
                        const dateStr = `${y}-${m}-${d}`;

                        days.push({
                            date: dateStr,
                            dayNumber: cellDate.getDate(),
                            isCurrentMonth: cellDate.getMonth() === month,
                            isToday: dateStr === this.todayDate,
                            dayName: cellDate.toLocaleDateString('en-US', { weekday: 'short' }),
                        });
                    }

                    this.calendarDays = days;
                },

                updateActiveWeekDays() {
                    const baseDate = new Date();
                    const dayOffset = (baseDate.getDay() + 6) % 7;
                    const monday = new Date(baseDate);
                    monday.setDate(baseDate.getDate() - dayOffset);

                    const days = [];
                    for (let i = 0; i < 7; i++) {
                        const d = new Date(monday);
                        d.setDate(monday.getDate() + i);

                        const y = d.getFullYear();
                        const m = String(d.getMonth() + 1).padStart(2, '0');
                        const dayNum = String(d.getDate()).padStart(2, '0');
                        const dateStr = `${y}-${m}-${dayNum}`;

                        days.push({
                            date: dateStr,
                            dayNumber: d.getDate(),
                            dayName: d.toLocaleDateString('en-US', { weekday: 'short' }),
                            formattedDate: d.toLocaleDateString('en-US', { day: 'numeric', month: 'short' }),
                            isToday: dateStr === this.todayDate,
                        });
                    }
                    this.activeWeekDays = days;
                },

                get weekRangeText() {
                    if (!this.activeWeekDays || !this.activeWeekDays.length) return '';
                    return this.activeWeekDays[0].formattedDate + ' — ' + this.activeWeekDays[6].formattedDate;
                },

                filterEvents() {
                    this.filteredEventsList = this.events.filter(e => {
                        if (this.filterCategory !== 'all' && e.type !== this.filterCategory) {
                            return false;
                        }

                        if (this.filterTech !== 'all' && String(e.tech_id) !== String(this.filterTech) && String(e.assigned_to) !== String(this.filterTech)) {
                            return false;
                        }

                        if (this.filterStatus !== 'all') {
                            if (this.filterStatus === 'pending' && !['pending', 'scheduled', 'assigned', 'open', 'new'].includes(e.status)) return false;
                            if (this.filterStatus === 'in_progress' && !['in_progress', 'working', 'dispatched'].includes(e.status)) return false;
                            if (this.filterStatus === 'completed' && !['completed', 'resolved', 'closed'].includes(e.status)) return false;
                            if (this.filterStatus === 'rejected' && e.status !== 'rejected') return false;
                        }

                        if (this.searchQuery.trim() !== '') {
                            const q = this.searchQuery.toLowerCase();
                            const match = (e.title && e.title.toLowerCase().includes(q)) ||
                                          (e.customer && e.customer.toLowerCase().includes(q)) ||
                                          (e.address && e.address.toLowerCase().includes(q)) ||
                                          (e.tech && e.tech.toLowerCase().includes(q)) ||
                                          (e.badge && e.badge.toLowerCase().includes(q));
                            if (!match) return false;
                        }

                        return true;
                    });
                },

                getDayEvents(dateStr) {
                    if (!this.filteredEventsList) return [];
                    return this.filteredEventsList.filter(e => e.date === dateStr);
                },

                getEventChipClasses(event) {
                    if (event.type === 'survey') return 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/80';
                    if (event.type === 'job') return 'bg-blue-50 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 border-blue-200/80 dark:border-blue-800/80';
                    if (event.type === 'task') return 'bg-sky-50 dark:bg-sky-950/70 text-sky-800 dark:text-sky-300 border-sky-200/80 dark:border-sky-800/80';
                    return 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border-slate-200 dark:border-slate-700';
                },

                getEventDotColor(event) {
                    if (event.type === 'survey') return 'bg-emerald-500';
                    if (event.type === 'job') return 'bg-blue-500';
                    if (event.type === 'task') return 'bg-sky-500';
                    return 'bg-slate-400';
                },

                prevMonth() {
                    const parts = this.currentMonth.split('-');
                    let year = parseInt(parts[0]);
                    let month = parseInt(parts[1]) - 1;
                    if (month === 0) {
                        month = 12;
                        year--;
                    }
                    this.currentMonth = `${year}-${String(month).padStart(2, '0')}`;
                    this.recalculateAll();
                },

                nextMonth() {
                    const parts = this.currentMonth.split('-');
                    let year = parseInt(parts[0]);
                    let month = parseInt(parts[1]) + 1;
                    if (month > 12) {
                        month = 1;
                        year++;
                    }
                    this.currentMonth = `${year}-${String(month).padStart(2, '0')}`;
                    this.recalculateAll();
                },

                goToToday() {
                    const parts = this.todayDate.split('-');
                    this.currentMonth = `${parts[0]}-${parts[1]}`;
                    this.recalculateAll();
                },

                openDrawer(event) {
                    this.selectedEvent = event;
                    this.drawerOpen = true;
                },

                closeDrawer() {
                    this.drawerOpen = false;
                },

                openDaySummary(day) {
                    const events = this.getDayEvents(day.date);
                    if (events.length > 0) {
                        this.openDrawer(events[0]);
                    }
                },

                openRejectModal(task) {
                    this.activeRejectTask = task;
                    this.rejectModalOpen = true;
                },

                openReassignModal(task) {
                    this.activeReassignTask = task;
                    this.reassignModalOpen = true;
                }
            };
        }
    </script>
</x-app-layout>
