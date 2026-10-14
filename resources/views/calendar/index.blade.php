<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Operations Calendar</h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/70">
                        Unified Schedule
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Unified Schedule & dispatch for site surveys, CCTV installations, and field maintenance</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('site-surveys.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Schedule Survey</span>
                </a>
                <a href="{{ route('jobs.create-general') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/90 shadow-2xs transition active:scale-95">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    <span>Installation Job</span>
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Safe JavaScript Data Initialization --}}
    <script>
        window.crmCalendarConfig = {
            initialMonth: '{{ $currentMonth->format("Y-m") }}',
            initialEvents: {!! json_encode($events, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
            technicians: {!! json_encode($technicians, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
            todayDate: '{{ now()->format("Y-m-d") }}'
        };
    </script>

    <style>
        /* Responsive cohesive theming on Operations Calendar */
        .crm-cal-input,
        .crm-cal-select {
            background-color: #ffffff;
            color: #0f172a;
            border-color: #cbd5e1;
        }
        .crm-cal-input::placeholder {
            color: #94a3b8;
        }
        .crm-cal-select option {
            background-color: #ffffff;
            color: #0f172a;
        }
        html.dark .crm-cal-input,
        html.dark .crm-cal-select {
            background-color: #0b132b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }
        html.dark .crm-cal-input::placeholder {
            color: #64748b !important;
        }
        html.dark .crm-cal-select option {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }
    </style>

    <div class="space-y-4" x-data="calendarApp(window.crmCalendarConfig)">

        {{-- 1. Stat cards: 4 clean, cohesive cards with refined metrics --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
            {{-- Total Operations --}}
            <div class="bg-white dark:bg-[#0f172a] rounded-xl border border-slate-200/80 dark:border-slate-800 p-4 shadow-[0_1px_3px_rgba(0,0,0,0.03)] hover:border-slate-300 dark:hover:border-slate-700 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Scheduled Operations</span>
                    <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-2" x-text="events.length">
                    {{ $stats['total'] }}
                </div>
                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Active this month</div>
            </div>

            {{-- Site Surveys --}}
            <div class="bg-white dark:bg-[#0f172a] rounded-xl border border-slate-200/80 dark:border-slate-800 p-4 shadow-[0_1px_3px_rgba(0,0,0,0.03)] hover:border-emerald-200 dark:hover:border-emerald-800/60 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Site Surveys</span>
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-2" x-text="countByType('survey')">
                    {{ $stats['surveys'] }}
                </div>
                <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-0.5">Pre-quote site visits</div>
            </div>

            {{-- Installations --}}
            <div class="bg-white dark:bg-[#0f172a] rounded-xl border border-slate-200/80 dark:border-slate-800 p-4 shadow-[0_1px_3px_rgba(0,0,0,0.03)] hover:border-blue-200 dark:hover:border-blue-800/60 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Installations</span>
                    <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-2" x-text="countByType('job')">
                    {{ $stats['jobs'] }}
                </div>
                <div class="text-[11px] text-blue-600 dark:text-blue-400 font-medium mt-0.5">Field deployment jobs</div>
            </div>

            {{-- Due Today --}}
            <div class="bg-white dark:bg-[#0f172a] rounded-xl border border-slate-200/80 dark:border-slate-800 p-4 shadow-[0_1px_3px_rgba(0,0,0,0.03)] hover:border-amber-200 dark:hover:border-amber-800/60 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Due Today</span>
                    <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-2" x-text="todayEvents.length">
                    {{ $stats['today'] }}
                </div>
                <div class="text-[11px] text-amber-600 dark:text-amber-400 font-medium mt-0.5">Scheduled for today</div>
            </div>
        </div>

        {{-- 2. Integrated Filter & View Toolbar --}}
        <div class="bg-white dark:bg-[#0f172a] rounded-xl border border-slate-200/80 dark:border-slate-800 p-3 shadow-[0_1px_3px_rgba(0,0,0,0.02)] flex flex-col md:flex-row md:items-center justify-between gap-3">
            
            {{-- Left Filters: Search, Category, Technician, Status --}}
            <div class="flex flex-wrap items-center gap-2 flex-1">
                {{-- Search box --}}
                <div class="relative w-full sm:w-60">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"/>
                        <line x1="21" x2="16.65" y1="21" y2="16.65"/>
                    </svg>
                    <input type="text" x-model="searchQuery" placeholder="Filter by customer, job, site..." 
                           class="crm-cal-input w-full h-9 pl-9 pr-3 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-blue-500 transition">
                </div>

                {{-- Category Dropdown --}}
                <select x-model="filterCategory"
                        class="crm-cal-select h-9 px-3 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500 transition cursor-pointer">
                    <option value="all">All Operations</option>
                    <option value="survey">Surveys</option>
                    <option value="job">Installations</option>
                </select>

                {{-- Technician Dropdown --}}
                <select x-model="filterTech"
                        class="crm-cal-select h-9 px-3 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500 transition cursor-pointer">
                    <option value="all">All Technicians</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                    @endforeach
                </select>

                {{-- Status Dropdown --}}
                <select x-model="filterStatus"
                        class="crm-cal-select h-9 px-3 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500 transition cursor-pointer">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending / Assigned</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            {{-- Right View Mode: Segmented Pill Switcher (Month, Week, Agenda) --}}
            <div class="inline-flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-lg border border-slate-200/60 dark:border-slate-700 self-start md:self-auto shrink-0">
                <button type="button" @click="currentView = 'month'"
                        :class="currentView === 'month' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="h-7 px-3 text-xs rounded-md transition cursor-pointer">
                    Month
                </button>
                <button type="button" @click="currentView = 'week'"
                        :class="currentView === 'week' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="h-7 px-3 text-xs rounded-md transition cursor-pointer">
                    Week
                </button>
                <button type="button" @click="currentView = 'agenda'"
                        :class="currentView === 'agenda' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="h-7 px-3 text-xs rounded-md transition cursor-pointer">
                    Agenda
                </button>
            </div>

        </div>

        {{-- 3. Main Split View: Calendar Views + Right Side Today's Jobs Panel --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            
            {{-- Main Calendar Column (8 cols on lg) --}}
            <div class="lg:col-span-8 space-y-3">
                
                {{-- Month Navigator Bar & Visual Legend --}}
                <div class="flex flex-wrap items-center justify-between gap-2.5 px-0.5">
                    {{-- Nav Buttons --}}
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="prevMonth()" class="h-8 px-2.5 rounded-lg border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1" title="Previous Month">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            <span>Prev</span>
                        </button>
                        <button type="button" @click="goToToday()" class="h-8 px-3 rounded-lg border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition">
                            Today
                        </button>
                        <button type="button" @click="nextMonth()" class="h-8 px-2.5 rounded-lg border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1" title="Next Month">
                            <span>Next</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <span class="text-base font-bold text-slate-900 dark:text-white ml-2 tracking-tight" x-text="monthTitle">
                            {{ $currentMonth->format('F Y') }}
                        </span>
                    </div>

                    {{-- Visual Category Legend --}}
                    <div class="hidden sm:flex items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                        <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Surveys</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Installations</span>
                    </div>
                </div>

                {{-- ── MONTH VIEW: Clean table with soft borders, rounded corners, and clear day cards ── --}}
                <div x-show="currentView === 'month'" class="border border-slate-200/80 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-[#0f172a] shadow-[0_1px_3px_rgba(0,0,0,0.03)]">
                    
                    {{-- 7-Day Weekday Header --}}
                    <div class="grid grid-cols-7 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/80 text-center text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-2.5 select-none">
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                        <div>Sun</div>
                    </div>

                    {{-- Month Grid Days --}}
                    <div class="grid grid-cols-7">
                        <template x-for="(day, dIndex) in calendarDays" :key="day.date">
                            <div class="border-b border-slate-100 dark:border-slate-800/80 min-h-[105px] transition-colors"
                                 :class="{
                                     'border-r border-slate-100 dark:border-slate-800/80': (dIndex + 1) % 7 !== 0,
                                     'bg-blue-50/25 dark:bg-blue-950/30': day.isCurrentMonth && day.isToday,
                                     'bg-white dark:bg-[#0f172a] hover:bg-slate-50/50 dark:hover:bg-slate-800/50': day.isCurrentMonth && !day.isToday,
                                     'bg-slate-50/40 dark:bg-slate-900/50 text-slate-300 dark:text-slate-600': !day.isCurrentMonth
                                 }">
                                
                                {{-- Outside Current Month --}}
                                <template x-if="!day.isCurrentMonth">
                                    <div class="p-1.5 h-full opacity-40">
                                        <span class="text-xs font-normal text-slate-400 select-none" x-text="day.dayNumber"></span>
                                    </div>
                                </template>

                                {{-- Current Month Day Cell --}}
                                <template x-if="day.isCurrentMonth">
                                    <div class="p-1.5 h-full flex flex-col justify-between">
                                        
                                        {{-- Date Number & Count Badge --}}
                                        <div class="flex items-center justify-between mb-1 select-none">
                                            <span class="inline-flex items-center justify-center text-xs font-semibold"
                                                  :class="day.isToday ? 'w-6 h-6 rounded-full bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200'"
                                                  x-text="day.dayNumber">
                                            </span>
                                            <span x-show="getDayEvents(day.date).length > 0" 
                                                  class="text-[10px] text-slate-400 font-medium px-1 rounded bg-slate-100 dark:bg-slate-800"
                                                  x-text="getDayEvents(day.date).length">
                                            </span>
                                        </div>

                                        {{-- Event Chips List --}}
                                        <div class="space-y-1 overflow-y-auto max-h-[75px] flex-1">
                                            <template x-for="event in getDayEvents(day.date).slice(0, 3)" :key="event.id">
                                                <div @click="openDrawer(event)"
                                                     :title="`${event.time} - ${event.title}\nCustomer: ${event.customer}\nAddress: ${event.address}\nTechnician: ${event.tech}`"
                                                     class="cursor-pointer text-[11px] leading-tight px-1.5 py-0.5 rounded-md truncate transition border shadow-2xs hover:shadow-xs flex items-center gap-1"
                                                     :class="getEventChipClasses(event)">
                                                    <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="getEventDotColor(event)"></span>
                                                    <span class="truncate" x-text="getShortEventTitle(event) + ' ' + (event.customer ? '• ' + event.customer : '')"></span>
                                                </div>
                                            </template>

                                            {{-- +N more popover button --}}
                                            <div x-show="getDayEvents(day.date).length > 3" 
                                                 @click="openDaySummary(day)"
                                                 class="cursor-pointer text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline pt-0.5 select-none">
                                                +<span x-text="getDayEvents(day.date).length - 3"></span> more
                                            </div>
                                        </div>

                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ── WEEK VIEW ── --}}
                <div x-show="currentView === 'week'" class="border border-slate-200/80 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-[#0f172a] shadow-[0_1px_3px_rgba(0,0,0,0.03)]">
                    <div class="p-3.5 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/80 flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-slate-200">
                        <span class="font-bold">Weekly Operations Schedule</span>
                        <span class="text-slate-500 dark:text-slate-400 font-medium" x-text="weekRangeText"></span>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="day in activeWeekDays" :key="day.date">
                            <div class="p-3.5 transition" :class="day.isToday ? 'bg-blue-50/20 dark:bg-blue-950/30' : 'hover:bg-slate-50/50 dark:hover:bg-slate-800/50'">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100" x-text="day.dayName + ', ' + day.formattedDate"></span>
                                        <span x-show="day.isToday" class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-600 text-white">Today</span>
                                    </div>
                                    <span class="text-xs text-slate-400 font-medium" x-text="getDayEvents(day.date).length + ' operations'"></span>
                                </div>
                                
                                <div x-show="getDayEvents(day.date).length === 0" class="text-xs text-slate-400 dark:text-slate-500 italic py-1">
                                    No scheduled operations for this date.
                                </div>

                                <div class="space-y-1.5" x-show="getDayEvents(day.date).length > 0">
                                    <template x-for="event in getDayEvents(day.date)" :key="event.id">
                                        <div @click="openDrawer(event)" 
                                             class="cursor-pointer p-2.5 rounded-lg border border-slate-200/80 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-between gap-3 text-xs transition">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[11px]" x-text="event.time"></span>
                                                    <span class="font-semibold text-slate-900 dark:text-white truncate" x-text="event.title"></span>
                                                </div>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5" x-text="event.customer + (event.address ? ' • ' + event.address : '')"></div>
                                            </div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 shrink-0 flex items-center gap-2">
                                                <span class="text-slate-600 dark:text-slate-300 font-medium" x-text="event.tech"></span>
                                                <span class="text-blue-600 dark:text-blue-400 font-bold hover:underline">View &rarr;</span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ── AGENDA VIEW ── --}}
                <div x-show="currentView === 'agenda'" class="border border-slate-200/80 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-[#0f172a] shadow-[0_1px_3px_rgba(0,0,0,0.03)]">
                    <div class="p-3.5 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/80 flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-slate-200">
                        <span class="font-bold">Operations Agenda List</span>
                        <span class="text-slate-500 dark:text-slate-400 font-medium" x-text="filteredEventsList.length + ' operations found'"></span>
                    </div>

                    <div x-show="filteredEventsList.length === 0" class="p-8 text-center text-xs text-slate-400 dark:text-slate-500">
                        No scheduled operations match your filters.
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800" x-show="filteredEventsList.length > 0">
                        <template x-for="event in filteredEventsList" :key="event.id">
                            <div @click="openDrawer(event)" class="p-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition flex items-center justify-between gap-3 text-xs">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[11px]" x-text="event.date + ' • ' + event.time"></span>
                                        <span class="font-bold text-slate-900 dark:text-white truncate" x-text="event.title"></span>
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" :class="getEventChipClasses(event)" x-text="event.badge"></span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-1" x-text="event.customer + (event.address ? ' • ' + event.address : '')"></div>
                                    <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                        Technician: <span class="text-slate-700 dark:text-slate-300 font-medium" x-text="event.tech"></span>
                                    </div>
                                </div>
                                <button type="button" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline font-semibold text-xs shrink-0 inline-flex items-center gap-1">
                                    <span>Details</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            {{-- 5. Right Column: Today's Jobs Panel --}}
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 rounded-xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.03)]">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Today's Jobs</h3>
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ now()->format('d M Y') }}</span>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-[520px] overflow-y-auto">
                        <template x-for="event in todayEvents" :key="event.id">
                            <div class="py-3 text-xs flex items-start justify-between gap-3 hover:bg-slate-50/60 dark:hover:bg-slate-800/60 p-2 rounded-lg transition">
                                <div class="min-w-0 flex-1">
                                    {{-- Time badge --}}
                                    <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 whitespace-nowrap" x-text="event.time"></div>
                                    {{-- Job title --}}
                                    <div class="font-semibold text-slate-900 dark:text-white mt-0.5 break-words" x-text="event.title"></div>
                                    <div class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5 break-words" x-text="event.customer + (event.address ? ' • ' + event.address : '')"></div>
                                    <div class="text-slate-400 dark:text-slate-500 text-[11px] mt-0.5">
                                        Technician: <span class="text-slate-700 dark:text-slate-300 font-medium" x-text="event.tech"></span>
                                    </div>
                                </div>
                                <button type="button" @click="openDrawer(event)" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline font-semibold text-xs shrink-0 pt-0.5">
                                    View
                                </button>
                            </div>
                        </template>

                        <div class="py-10 text-center text-slate-400 dark:text-slate-500" x-show="todayEvents.length === 0">
                            <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 mx-auto flex items-center justify-center mb-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="text-xs font-medium text-slate-600 dark:text-slate-300">All caught up</div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">No operations scheduled for today.</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Slide-Out Work Order Details Drawer --}}
        <div x-show="drawerOpen" 
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeDrawer()"
             class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-2xs" 
             style="display: none;"></div>

        <div x-show="drawerOpen" 
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white dark:bg-[#0f172a] shadow-2xl border-l border-slate-200 dark:border-slate-800 flex flex-col justify-between"
             style="display: none;">
            
            {{-- Drawer Header --}}
            <div>
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" :class="selectedEvent ? getEventDotColor(selectedEvent) : 'bg-slate-400'"></span>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white" x-text="selectedEvent ? selectedEvent.badge : 'Job Details'"></h3>
                    </div>
                    <button type="button" @click="closeDrawer()" class="p-1 rounded text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 overflow-y-auto max-h-[calc(100vh-140px)]" x-show="selectedEvent">
                    <div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white" x-text="selectedEvent ? selectedEvent.title : ''"></h4>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 font-semibold text-slate-700 dark:text-slate-300">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                <span x-text="selectedEvent ? selectedEvent.date : ''"></span>
                            </span>
                            <span>&bull;</span>
                            <span class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-400">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span x-text="selectedEvent ? selectedEvent.time : ''"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Customer Details Card --}}
                    <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 text-xs space-y-2">
                        <div class="font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Client Information</div>
                        <div class="font-bold text-slate-900 dark:text-white text-sm" x-text="selectedEvent ? selectedEvent.customer : ''"></div>
                        <div class="text-slate-600 dark:text-slate-300 flex items-center gap-1.5" x-show="selectedEvent && selectedEvent.phone">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span x-text="selectedEvent ? selectedEvent.phone : ''"></span>
                        </div>
                        <div class="text-slate-600 dark:text-slate-300 flex items-start gap-1.5" x-show="selectedEvent && selectedEvent.address">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span x-text="selectedEvent ? selectedEvent.address : ''"></span>
                        </div>
                    </div>

                    {{-- Technician Card --}}
                    <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 text-xs space-y-1.5">
                        <div class="font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Assigned Engineer</div>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-bold flex items-center justify-center text-xs">
                                <span x-text="selectedEvent && selectedEvent.tech ? selectedEvent.tech.charAt(0) : 'T'"></span>
                            </div>
                            <div class="font-bold text-slate-900 dark:text-white" x-text="selectedEvent ? selectedEvent.tech : 'Unassigned'"></div>
                        </div>
                    </div>

                    {{-- Notes Card --}}
                    <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 text-xs space-y-1.5" x-show="selectedEvent && selectedEvent.notes">
                        <div class="font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Field / Visit Notes</div>
                        <p class="text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed" x-text="selectedEvent ? selectedEvent.notes : ''"></p>
                    </div>
                </div>
            </div>

            {{-- Drawer Footer --}}
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/90 flex items-center justify-between gap-3">
                <button type="button" @click="closeDrawer()" class="px-3.5 py-2 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-800 transition">
                    Close
                </button>
                <template x-if="selectedEvent && selectedEvent.url">
                    <a :href="selectedEvent.url" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-sm transition inline-flex items-center gap-1.5">
                        <span>Open Work Order</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </template>
            </div>

        </div>

    </div>

    {{-- Calendar Alpine.js Application Logic --}}
    <script>
        function calendarApp(config) {
            return {
                currentMonth: config ? config.initialMonth : '{{ now()->format("Y-m") }}',
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

                init() {
                    this.recalculateAll();
                    this.$watch('filterCategory', () => this.filterEvents());
                    this.$watch('filterTech', () => this.filterEvents());
                    this.$watch('filterStatus', () => this.filterEvents());
                    this.$watch('searchQuery', () => this.filterEvents());
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

                        if (this.filterTech !== 'all' && String(e.tech_id) !== String(this.filterTech)) {
                            return false;
                        }

                        if (this.filterStatus !== 'all') {
                            if (this.filterStatus === 'pending' && !['pending', 'scheduled', 'assigned', 'open', 'new'].includes(e.status)) return false;
                            if (this.filterStatus === 'in_progress' && !['in_progress', 'working', 'dispatched'].includes(e.status)) return false;
                            if (this.filterStatus === 'completed' && !['completed', 'resolved', 'closed'].includes(e.status)) return false;
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

                countByType(type) {
                    if (!this.events) return 0;
                    return this.events.filter(e => e.type === type).length;
                },

                getShortEventTitle(event) {
                    if (event.type === 'survey') return 'Survey';
                    if (event.type === 'job') return 'Install';
                    return 'Job';
                },

                getEventChipClasses(event) {
                    if (event.type === 'survey') return 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/80 hover:bg-emerald-100/70 dark:hover:bg-emerald-900/60';
                    if (event.type === 'job') return 'bg-blue-50 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 border-blue-200/80 dark:border-blue-800/80 hover:bg-blue-100/70 dark:hover:bg-blue-900/60';
                    return 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700';
                },

                getEventDotColor(event) {
                    if (event.type === 'survey') return 'bg-emerald-500';
                    if (event.type === 'job') return 'bg-blue-500';
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
                    this.fetchEventsForMonth();
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
                    this.fetchEventsForMonth();
                },

                goToToday() {
                    const parts = this.todayDate.split('-');
                    this.currentMonth = `${parts[0]}-${parts[1]}`;
                    this.recalculateAll();
                    this.fetchEventsForMonth();
                },

                fetchEventsForMonth() {
                    const parts = this.currentMonth.split('-');
                    const year = parseInt(parts[0]);
                    const month = parseInt(parts[1]) - 1;

                    const start = new Date(year, month, 1 - 7);
                    const end = new Date(year, month + 1, 7);

                    const startStr = `${start.getFullYear()}-${String(start.getMonth() + 1).padStart(2, '0')}-${String(start.getDate()).padStart(2, '0')}`;
                    const endStr = `${end.getFullYear()}-${String(end.getMonth() + 1).padStart(2, '0')}-${String(end.getDate()).padStart(2, '0')}`;

                    fetch(`/calendar/events?start=${startStr}&end=${endStr}`)
                        .then(r => r.json())
                        .then(data => {
                            this.events = data;
                            this.recalculateAll();
                            if (window.history.replaceState) {
                                const newUrl = window.location.protocol + '//' + window.location.host + window.location.pathname + '?month=' + this.currentMonth;
                                window.history.replaceState({ path: newUrl }, '', newUrl);
                            }
                        })
                        .catch(err => console.error('Error fetching calendar events:', err));
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
                }
            };
        }
    </script>
</x-app-layout>
