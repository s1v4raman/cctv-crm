<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Operations Calendar &amp; Dispatch</h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-blue-500/20 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded border border-blue-500/30">Unified Schedule</span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Live operational timetable for site surveys, CCTV installations, AMC maintenance visits, and SLA service tickets</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('site-surveys.create') }}" class="crm-btn-primary px-3 py-1.5 rounded-xl text-white font-bold text-xs shadow-xs transition flex items-center gap-1" style="background: linear-gradient(135deg, var(--crm-accent, #2563eb), var(--crm-accent-hover, #1d4ed8)); border: 1px solid var(--crm-accent, #2563eb);">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Survey</span>
                </a>
                <a href="{{ route('jobs.create-general') }}" class="crm-btn-primary px-3 py-1.5 rounded-xl text-white font-bold text-xs shadow-xs transition flex items-center gap-1" style="background: linear-gradient(135deg, var(--crm-accent, #2563eb), var(--crm-accent-hover, #1d4ed8)); border: 1px solid var(--crm-accent, #2563eb);">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Installation</span>
                </a>
                <a href="{{ route('service-tickets.create') }}" class="crm-btn-primary px-3 py-1.5 rounded-xl text-white font-bold text-xs shadow-xs transition flex items-center gap-1" style="background: linear-gradient(135deg, var(--crm-accent, #2563eb), var(--crm-accent-hover, #1d4ed8)); border: 1px solid var(--crm-accent, #2563eb);">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Ticket</span>
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Safe JavaScript Data Initialization (Prevents HTML Attribute Quote Collision) --}}
    <script>
        window.crmCalendarConfig = {
            initialMonth: '{{ $currentMonth->format("Y-m") }}',
            initialEvents: {!! json_encode($events, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
            technicians: {!! json_encode($technicians, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
            todayDate: '{{ now()->format("Y-m-d") }}'
        };
    </script>

    <div class="pg-wrap" x-data="calendarApp(window.crmCalendarConfig)">
        <div class="pg-inner space-y-5">

            {{-- 1. Operations KPI Overview Bar --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                {{-- Total Operations --}}
                <div class="saas-card p-3.5 flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Scheduled</div>
                        <div class="text-xl font-extrabold text-slate-900 dark:text-white font-heading mt-0.5" x-text="events.length">
                            {{ $stats['total'] }}
                        </div>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>

                {{-- Site Surveys --}}
                <div @click="toggleType('survey')" class="saas-card p-3.5 flex items-center justify-between cursor-pointer hover:border-amber-400 transition" :class="filterTypes.includes('survey') ? 'ring-1 ring-amber-400/50' : 'opacity-60'">
                    <div>
                        <div class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Surveys
                        </div>
                        <div class="text-xl font-extrabold text-slate-900 dark:text-white font-heading mt-0.5" x-text="countByType('survey')">
                            {{ $stats['surveys'] }}
                        </div>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    </div>
                </div>

                {{-- CCTV Installations --}}
                <div @click="toggleType('job')" class="saas-card p-3.5 flex items-center justify-between cursor-pointer hover:border-blue-400 transition" :class="filterTypes.includes('job') ? 'ring-1 ring-blue-400/50' : 'opacity-60'">
                    <div>
                        <div class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                            CCTV Jobs
                        </div>
                        <div class="text-xl font-extrabold text-slate-900 dark:text-white font-heading mt-0.5" x-text="countByType('job')">
                            {{ $stats['jobs'] }}
                        </div>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                </div>

                {{-- AMC Maintenance --}}
                <div @click="toggleType('amc')" class="saas-card p-3.5 flex items-center justify-between cursor-pointer hover:border-emerald-400 transition" :class="filterTypes.includes('amc') ? 'ring-1 ring-emerald-400/50' : 'opacity-60'">
                    <div>
                        <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            AMC Visits
                        </div>
                        <div class="text-xl font-extrabold text-slate-900 dark:text-white font-heading mt-0.5" x-text="countByType('amc')">
                            {{ $stats['amcs'] }}
                        </div>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                </div>

                {{-- Service Tickets --}}
                <div @click="toggleType('ticket')" class="saas-card p-3.5 flex items-center justify-between cursor-pointer hover:border-rose-400 transition" :class="filterTypes.includes('ticket') ? 'ring-1 ring-rose-400/50' : 'opacity-60'">
                    <div>
                        <div class="text-[11px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            Tickets
                        </div>
                        <div class="text-xl font-extrabold text-slate-900 dark:text-white font-heading mt-0.5" x-text="countByType('ticket')">
                            {{ $stats['tickets'] }}
                        </div>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                </div>

                {{-- Today's Active Dispatches --}}
                <div class="saas-card p-3.5 flex items-center justify-between border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5" style="color: var(--crm-accent, #2563eb);">
                            <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: var(--crm-accent, #2563eb);"></span>
                            Today's Active
                        </div>
                        <div class="text-xl font-extrabold font-heading mt-0.5" style="color: var(--crm-accent, #2563eb);" x-text="todayEvents.length">
                            {{ $stats['today'] }}
                        </div>
                    </div>
                    <div class="w-9 h-9 rounded-xl text-white flex items-center justify-center shadow-xs" style="background-color: var(--crm-accent, #2563eb);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>
            </div>

            {{-- 2. Calendar Action Bar & Controls --}}
            <div class="saas-card p-4">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    
                    {{-- Left: Month / Period Navigator --}}
                    <div class="flex items-center gap-2">
                        <button type="button" @click="prevMonth()" class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition" title="Previous Month">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        
                        <div class="min-w-[190px] text-center">
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white font-heading" x-text="monthTitle">
                                {{ $currentMonth->format('F Y') }}
                            </h3>
                            <span class="text-[11px] font-medium text-slate-400" x-text="periodSubtext"></span>
                        </div>

                        <button type="button" @click="nextMonth()" class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition" title="Next Month">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <button type="button" @click="goToToday()" class="ml-2 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition shadow-2xs">
                            Today
                        </button>
                    </div>

                    {{-- Center: Live Search --}}
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" x-model="searchQuery" placeholder="Filter by customer, site address, ticket #..." 
                               style="padding-left: 2.75rem !important;"
                               class="w-full pl-11 pr-8 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-blue-500 transition">
                        <button x-show="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Right: View Mode Toggle (Month / Week / Agenda) --}}
                    <div class="flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl self-start sm:self-auto">
                        <button type="button" @click="currentView = 'month'" 
                                :style="currentView === 'month' ? { backgroundColor: 'var(--crm-accent, #2563eb)', color: '#ffffff', boxShadow: '0 2px 8px -1px var(--crm-accent, #2563eb)' } : {}"
                                :class="currentView !== 'month' ? 'text-slate-600 dark:text-slate-400 font-medium hover:text-slate-900 dark:hover:text-white' : 'font-bold shadow-xs'"
                                class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                            Month
                        </button>
                        <button type="button" @click="currentView = 'week'" 
                                :style="currentView === 'week' ? { backgroundColor: 'var(--crm-accent, #2563eb)', color: '#ffffff', boxShadow: '0 2px 8px -1px var(--crm-accent, #2563eb)' } : {}"
                                :class="currentView !== 'week' ? 'text-slate-600 dark:text-slate-400 font-medium hover:text-slate-900 dark:hover:text-white' : 'font-bold shadow-xs'"
                                class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                            Week
                        </button>
                        <button type="button" @click="currentView = 'agenda'" 
                                :style="currentView === 'agenda' ? { backgroundColor: 'var(--crm-accent, #2563eb)', color: '#ffffff', boxShadow: '0 2px 8px -1px var(--crm-accent, #2563eb)' } : {}"
                                :class="currentView !== 'agenda' ? 'text-slate-600 dark:text-slate-400 font-medium hover:text-slate-900 dark:hover:text-white' : 'font-bold shadow-xs'"
                                class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                            Agenda
                        </button>
                    </div>
                </div>

                {{-- Secondary Filters Row: Technician & Category Pills --}}
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                    
                    {{-- Category filter pills --}}
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-slate-400 font-semibold mr-1">Categories:</span>
                        
                        <button type="button" @click="toggleType('survey')" 
                                :class="filterTypes.includes('survey') ? 'bg-amber-100 text-amber-900 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'"
                                class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Surveys
                        </button>

                        <button type="button" @click="toggleType('job')" 
                                :class="filterTypes.includes('job') ? 'bg-blue-100 text-blue-900 border-blue-300 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-700' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'"
                                class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            Installations
                        </button>

                        <button type="button" @click="toggleType('amc')" 
                                :class="filterTypes.includes('amc') ? 'bg-emerald-100 text-emerald-900 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-700' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'"
                                class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            AMC Visits
                        </button>

                        <button type="button" @click="toggleType('ticket')" 
                                :class="filterTypes.includes('ticket') ? 'bg-rose-100 text-rose-900 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-700' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'"
                                class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            Tickets
                        </button>

                        <button type="button" @click="resetFilters()" 
                                x-show="filterTypes.length < 4 || filterTech !== 'all' || filterStatus !== 'all' || searchQuery !== ''"
                                class="text-[11px] text-blue-600 dark:text-blue-400 hover:underline font-semibold ml-1">
                            Reset Filters
                        </button>
                    </div>

                    {{-- Technician filter & quick status --}}
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1.5">
                            <label class="text-slate-300 font-semibold text-sm">Technician:</label>
                            <select x-model="filterTech" class="text-xs py-1 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-blue-500">
                                <option value="all">All Field Staff</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }} ({{ ucfirst($tech->role) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <label class="text-slate-300 font-semibold text-sm">Status:</label>
                            <select x-model="filterStatus" class="text-xs py-1 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-blue-500">
                                <option value="all">All Statuses</option>
                                <option value="pending">Pending / Scheduled</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed / Resolved</option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>

            {{-- 3. Main Split View: Calendar Views + Right Side Agenda --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                
                {{-- Main Calendar Grid Column (8 cols on large screens) --}}
                <div class="lg:col-span-8 space-y-4">
                    
                    {{-- ── MONTH VIEW ── --}}
                    <div x-show="currentView === 'month'" class="saas-card overflow-hidden">
                        {{-- 7-Day Header --}}
                        <div class="grid grid-cols-7 border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/60 text-center text-[11px] font-bold text-slate-500 dark:text-slate-400 py-2.5 uppercase tracking-wider select-none">
                            <div>Mon</div>
                            <div>Tue</div>
                            <div>Wed</div>
                            <div>Thu</div>
                            <div>Fri</div>
                            <div class="text-blue-600 dark:text-blue-400">Sat</div>
                            <div class="text-rose-600 dark:text-rose-400">Sun</div>
                        </div>

                        {{-- Month Grid Days --}}
                        <div class="grid grid-cols-7 divide-x divide-y divide-slate-100 dark:divide-slate-800/80 bg-slate-100/40 dark:bg-slate-900/40">
                            <template x-for="day in calendarDays" :key="day.date">
                                <div class="min-h-[110px] sm:min-h-[125px] p-1.5 sm:p-2 bg-white dark:bg-[#0f172a] transition-colors relative flex flex-col justify-between group"
                                     :class="{
                                         'opacity-40 bg-slate-50/50 dark:bg-slate-950/40': !day.isCurrentMonth,
                                         'ring-2 ring-inset ring-blue-500/80 bg-blue-50/20 dark:bg-blue-950/20': day.isToday
                                     }">
                                    
                                    {{-- Day Header: Date Number & Today badge --}}
                                    <div class="flex items-center justify-between mb-1 select-none">
                                        <span class="text-xs font-bold leading-none"
                                              :class="day.isToday ? 'w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center font-extrabold shadow-xs' : (day.isCurrentMonth ? 'text-slate-800 dark:text-slate-200 group-hover:text-blue-600' : 'text-slate-400 dark:text-slate-600')"
                                              x-text="day.dayNumber">
                                        </span>

                                        <span x-show="getDayEvents(day.date).length > 0" 
                                              class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400"
                                              x-text="getDayEvents(day.date).length">
                                        </span>
                                    </div>

                                    {{-- Event Pills List --}}
                                    <div class="space-y-1 overflow-y-auto max-h-[85px] flex-1">
                                        <template x-for="(event, idx) in getDayEvents(day.date).slice(0, 3)" :key="event.id">
                                            <div @click="openDrawer(event)"
                                                 class="cursor-pointer rounded-md px-1.5 py-1 text-[11px] font-semibold leading-tight border transition truncate flex items-center gap-1 shadow-2xs hover:scale-[1.02]"
                                                 :class="getEventPillClass(event)">
                                                <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="getEventDotClass(event)"></span>
                                                <span class="font-bold text-[10px] shrink-0" x-text="event.time"></span>
                                                <span class="truncate" x-text="event.customer || event.title"></span>
                                            </div>
                                        </template>

                                        {{-- +N more popover button --}}
                                        <div x-show="getDayEvents(day.date).length > 3" 
                                             @click="openDaySummary(day)"
                                             class="cursor-pointer text-[10px] font-extrabold text-blue-600 dark:text-blue-400 hover:underline pt-0.5 px-1">
                                            +<span x-text="getDayEvents(day.date).length - 3"></span> more...
                                        </div>
                                    </div>

                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ── WEEK VIEW ── --}}
                    <div x-show="currentView === 'week'" class="saas-card overflow-hidden">
                        <div class="p-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Active 7-Day Operations Schedule</span>
                            <span class="text-slate-400" x-text="weekRangeText"></span>
                        </div>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            <template x-for="day in activeWeekDays" :key="day.date">
                                <div class="p-3 sm:p-4 transition hover:bg-slate-50/40 dark:hover:bg-slate-800/30"
                                     :class="day.isToday ? 'bg-blue-50/30 dark:bg-blue-950/20 border-l-4 border-l-blue-600' : ''">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500" x-text="day.dayName"></span>
                                            <span class="text-sm font-extrabold text-slate-900 dark:text-white" x-text="day.formattedDate"></span>
                                            <span x-show="day.isToday" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-600 text-white uppercase tracking-wider">Today</span>
                                        </div>
                                        <span class="text-xs text-slate-400" x-text="getDayEvents(day.date).length + ' operations scheduled'"></span>
                                    </div>

                                    <div x-show="getDayEvents(day.date).length === 0" class="text-xs text-slate-400 italic py-1 pl-1">
                                        No scheduled fieldwork operations for this date.
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" x-show="getDayEvents(day.date).length > 0">
                                        <template x-for="event in getDayEvents(day.date)" :key="event.id">
                                            <div @click="openDrawer(event)" 
                                                 class="cursor-pointer p-2.5 rounded-xl border transition shadow-2xs hover:shadow-xs flex items-start justify-between gap-2"
                                                 :class="getEventPillClass(event)">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-1.5 mb-1">
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider" :class="getEventBadgeClass(event)" x-text="event.badge"></span>
                                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="event.time"></span>
                                                    </div>
                                                    <div class="font-bold text-xs text-slate-900 dark:text-white truncate" x-text="event.title"></div>
                                                    <div class="text-[11px] text-slate-600 dark:text-slate-400 truncate mt-0.5" x-text="'👤 ' + event.customer + ' • 📍 ' + event.address"></div>
                                                    <div class="text-[10px] text-slate-500 dark:text-slate-500 mt-1 flex items-center gap-1">
                                                        <span>Tech:</span>
                                                        <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="event.tech"></span>
                                                    </div>
                                                </div>
                                                <div class="shrink-0">
                                                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded" :class="getStatusBadgeClass(event.status)" x-text="formatStatus(event.status)"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ── AGENDA VIEW ── --}}
                    <div x-show="currentView === 'agenda'" class="saas-card overflow-hidden">
                        <div class="p-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Chronological Operations Timeline</span>
                            <span class="text-slate-400" x-text="filteredEventsList.length + ' operations matched'"></span>
                        </div>

                        <div x-show="filteredEventsList.length === 0" class="p-10 text-center">
                            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">No scheduled operations found</p>
                            <p class="text-xs text-slate-400 mt-1">Try changing category filters, clear search query, or select another period.</p>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800" x-show="filteredEventsList.length > 0">
                            <template x-for="event in filteredEventsList" :key="event.id">
                                <div @click="openDrawer(event)" class="p-3 sm:p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 cursor-pointer transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <div class="w-12 h-12 rounded-xl flex flex-col items-center justify-center shrink-0 border" :class="getEventPillClass(event)">
                                            <span class="text-[9px] font-bold uppercase tracking-wider" x-text="formatDateShort(event.date).month"></span>
                                            <span class="text-base font-black leading-none" x-text="formatDateShort(event.date).day"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider" :class="getEventBadgeClass(event)" x-text="event.badge"></span>
                                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400" x-text="event.time"></span>
                                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded" :class="getStatusBadgeClass(event.status)" x-text="formatStatus(event.status)"></span>
                                            </div>
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate" x-text="event.title"></h4>
                                            <div class="text-xs text-slate-600 dark:text-slate-400 flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
                                                <span>👤 <strong class="text-slate-800 dark:text-slate-200" x-text="event.customer"></strong></span>
                                                <span>📍 <span x-text="event.address"></span></span>
                                                <span>📞 <span x-text="event.phone"></span></span>
                                                <span>🔧 Tech: <strong class="text-slate-800 dark:text-slate-200" x-text="event.tech"></strong></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="shrink-0 sm:text-right">
                                        <button type="button" class="btn-primary text-xs py-1.5 px-3 rounded-lg flex items-center gap-1">
                                            <span>Dispatch</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>

                {{-- Right Sidebar: Today's Dispatches & Roster (4 cols on large screens) --}}
                <div class="lg:col-span-4 space-y-4">
                    
                    {{-- Card 1: Today's Field Dispatches --}}
                    <div class="saas-card overflow-hidden">
                        <div class="p-3.5 border-b border-slate-100 dark:border-slate-800 text-white flex items-center justify-between"
                             style="background-color: var(--crm-accent, #2563eb);">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-sm leading-tight text-white">Today's Field Dispatches</h4>
                                    <span class="text-[10px] text-white/80 font-medium">{{ now()->format('l, d M Y') }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-white shadow-2xs" style="color: var(--crm-accent, #2563eb);" x-text="todayEvents.length">
                                {{ count($todayEvents) }}
                            </span>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-[380px] overflow-y-auto">
                            <template x-for="event in todayEvents" :key="event.id">
                                <div @click="openDrawer(event)" class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition">
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider" :class="getEventBadgeClass(event)" x-text="event.badge"></span>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="event.time"></span>
                                    </div>
                                    <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="event.title"></h5>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5" x-text="'📍 ' + event.address"></p>
                                    <div class="flex items-center justify-between mt-2 pt-1 border-t border-slate-100 dark:border-slate-800/60 text-[10px]">
                                        <span class="text-slate-500">Tech: <strong class="text-slate-700 dark:text-slate-300" x-text="event.tech"></strong></span>
                                        <span class="text-blue-600 dark:text-blue-400 font-bold hover:underline">View →</span>
                                    </div>
                                </div>
                            </template>

                            <div class="p-6 text-center" x-show="todayEvents.length === 0">
                                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">No dispatches scheduled today</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Field operations are clear for today.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Card 2: Operations Color Reference --}}
                    <div class="saas-card p-4">
                        <h4 class="text-xs font-extrabold text-slate-900 dark:text-white font-heading uppercase tracking-wider mb-3">Color Code Legend</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between p-2 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                    <span class="font-bold text-amber-900 dark:text-amber-300">Site Survey</span>
                                </div>
                                <span class="text-[10px] font-semibold text-amber-700 dark:text-amber-400">Premises Audit</span>
                            </div>

                            <div class="flex items-center justify-between p-2 rounded-lg bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/40">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                    <span class="font-bold text-blue-900 dark:text-blue-300">Installation Job</span>
                                </div>
                                <span class="text-[10px] font-semibold text-blue-700 dark:text-blue-400">Camera Deployment</span>
                            </div>

                            <div class="flex items-center justify-between p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <span class="font-bold text-emerald-900 dark:text-emerald-300">AMC Maintenance</span>
                                </div>
                                <span class="text-[10px] font-semibold text-emerald-700 dark:text-emerald-400">Routine Audit</span>
                            </div>

                            <div class="flex items-center justify-between p-2 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/40">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                    <span class="font-bold text-rose-900 dark:text-rose-300">Service Ticket</span>
                                </div>
                                <span class="text-[10px] font-semibold text-rose-700 dark:text-rose-400">Repair &amp; SLA</span>
                            </div>
                        </div>
                    </div>

                    {{-- Card 3: Active Field Technicians Roster --}}
                    <div class="saas-card p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-extrabold text-slate-900 dark:text-white font-heading uppercase tracking-wider">Field Personnel</h4>
                            <span class="text-[10px] font-bold text-slate-400">{{ $technicians->count() }} Available</span>
                        </div>
                        <div class="space-y-2 max-h-[220px] overflow-y-auto">
                            @foreach($technicians as $tech)
                                <div class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition cursor-pointer"
                                     @click="filterTech = (filterTech == '{{ $tech->id }}' ? 'all' : '{{ $tech->id }}')">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center justify-center uppercase">
                                            {{ substr($tech->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $tech->name }}</div>
                                            <div class="text-[10px] text-slate-400 capitalize">{{ $tech->role }}</div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md"
                                          :class="filterTech == '{{ $tech->id }}' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'">
                                        <span x-text="countByTech({{ $tech->id }})"></span> jobs
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

            </div>

        </div>

        {{-- 4. Slide-Out Work Order Dispatch Drawer --}}
        <div x-show="drawerOpen" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeDrawer()"
             class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs" 
             style="display: none;"></div>

        <div x-show="drawerOpen" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white dark:bg-[#0f172a] shadow-2xl border-l border-slate-200 dark:border-slate-800 flex flex-col justify-between"
             style="display: none;">
            
            {{-- Drawer Header --}}
            <div>
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider" 
                              :class="selectedEvent ? getEventBadgeClass(selectedEvent) : ''" 
                              x-text="selectedEvent ? selectedEvent.badge : ''"></span>
                        <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded"
                              :class="selectedEvent ? getStatusBadgeClass(selectedEvent.status) : ''"
                              x-text="selectedEvent ? formatStatus(selectedEvent.status) : ''"></span>
                    </div>
                    <button type="button" @click="closeDrawer()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 overflow-y-auto max-h-[calc(100vh-160px)]" x-show="selectedEvent">
                    
                    {{-- Title & Scheduled Time --}}
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900 dark:text-white font-heading" x-text="selectedEvent ? selectedEvent.title : ''"></h3>
                        <div class="flex items-center gap-2 mt-1.5 text-xs text-slate-500 dark:text-slate-400 font-semibold">
                            <span>📅 <strong class="text-slate-700 dark:text-slate-300" x-text="selectedEvent ? selectedEvent.date : ''"></strong></span>
                            <span>•</span>
                            <span>⏰ <strong class="text-slate-700 dark:text-slate-300" x-text="selectedEvent ? selectedEvent.time : ''"></strong></span>
                        </div>
                    </div>

                    {{-- Customer Details Card --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-2">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Customer &amp; Site Details</div>
                        <div class="text-sm font-bold text-slate-900 dark:text-white" x-text="selectedEvent ? selectedEvent.customer : ''"></div>
                        
                        <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                            <span>📞</span>
                            <span x-text="selectedEvent ? selectedEvent.phone : ''"></span>
                            <template x-if="selectedEvent && selectedEvent.phone && selectedEvent.phone !== '—'">
                                <a :href="'tel:' + selectedEvent.phone" class="ml-1 text-[11px] text-blue-600 dark:text-blue-400 font-bold hover:underline">Call</a>
                            </template>
                        </div>

                        <div class="flex items-start gap-2 text-xs text-slate-600 dark:text-slate-300">
                            <span>📍</span>
                            <span class="flex-1" x-text="selectedEvent ? selectedEvent.address : ''"></span>
                        </div>
                    </div>

                    {{-- Assigned Technician Card --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Assigned Field Specialist</div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center uppercase">
                                <span x-text="selectedEvent && selectedEvent.tech ? selectedEvent.tech.substring(0, 2) : 'FS'"></span>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white" x-text="selectedEvent ? selectedEvent.tech : 'Unassigned'"></div>
                                <div class="text-[10px] text-slate-400">Lead Field Technician</div>
                            </div>
                        </div>
                    </div>

                    {{-- Operational Notes --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80" x-show="selectedEvent && selectedEvent.notes">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Work Order Instructions / Scope</div>
                        <p class="text-xs text-slate-700 dark:text-slate-300 whitespace-pre-line" x-text="selectedEvent ? selectedEvent.notes : ''"></p>
                    </div>

                </div>
            </div>

            {{-- Drawer Footer Actions --}}
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between gap-3">
                <button type="button" @click="closeDrawer()" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Close
                </button>
                <template x-if="selectedEvent && selectedEvent.url">
                    <a :href="selectedEvent.url" class="btn-primary text-xs py-2 px-4 rounded-xl flex items-center gap-1.5">
                        <span>Open Work Order Details</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </template>
            </div>

        </div>

    </div>

    {{-- Calendar Alpine.js Pure Reactive Function --}}
    <script>
        function calendarApp(config) {
            return {
                currentMonth: config ? config.initialMonth : '{{ now()->format("Y-m") }}',
                events: (config && config.initialEvents) ? config.initialEvents : [],
                technicians: (config && config.technicians) ? config.technicians : [],
                todayDate: config ? config.todayDate : '{{ now()->format("Y-m-d") }}',
                
                currentView: 'month',
                filterTypes: ['survey', 'job', 'amc', 'ticket'],
                filterTech: 'all',
                filterStatus: 'all',
                searchQuery: '',
                
                monthTitle: '',
                periodSubtext: '',
                calendarDays: [],
                activeWeekDays: [],
                filteredEventsList: [],
                todayEvents: [],
                
                drawerOpen: false,
                selectedEvent: null,

                init() {
                    this.recalculateAll();
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
                    this.periodSubtext = this.events.length + ' scheduled field tasks';
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
                        if (!this.filterTypes.includes(e.type)) return false;

                        if (this.filterTech !== 'all' && String(e.tech_id) !== String(this.filterTech)) return false;

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

                countByTech(techId) {
                    if (!this.events) return 0;
                    return this.events.filter(e => String(e.tech_id) === String(techId)).length;
                },

                toggleType(type) {
                    if (this.filterTypes.includes(type)) {
                        if (this.filterTypes.length > 1) {
                            this.filterTypes = this.filterTypes.filter(t => t !== type);
                        }
                    } else {
                        this.filterTypes.push(type);
                    }
                    this.filterEvents();
                },

                resetFilters() {
                    this.filterTypes = ['survey', 'job', 'amc', 'ticket'];
                    this.filterTech = 'all';
                    this.filterStatus = 'all';
                    this.searchQuery = '';
                    this.filterEvents();
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
                },

                formatDateShort(dateStr) {
                    if (!dateStr) return { month: '', day: '' };
                    const parts = dateStr.split('-');
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                    return {
                        month: d.toLocaleDateString('en-US', { month: 'short' }),
                        day: d.getDate()
                    };
                },

                formatStatus(status) {
                    if (!status) return 'Scheduled';
                    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                },

                getEventPillClass(event) {
                    switch (event.color) {
                        case 'amber':
                            return 'bg-amber-50 text-amber-900 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60';
                        case 'blue':
                            return 'bg-blue-50 text-blue-900 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/60';
                        case 'emerald':
                            return 'bg-emerald-50 text-emerald-900 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60';
                        case 'rose':
                            return 'bg-rose-50 text-rose-900 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60';
                        default:
                            return 'bg-slate-50 text-slate-800 border-slate-200 dark:bg-slate-800 dark:text-slate-200';
                    }
                },

                getEventDotClass(event) {
                    switch (event.color) {
                        case 'amber': return 'bg-amber-500';
                        case 'blue': return 'bg-blue-600';
                        case 'emerald': return 'bg-emerald-500';
                        case 'rose': return 'bg-rose-500';
                        default: return 'bg-slate-400';
                    }
                },

                getEventBadgeClass(event) {
                    switch (event.color) {
                        case 'amber': return 'bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/30';
                        case 'blue': return 'bg-blue-500/20 text-blue-700 dark:text-blue-400 border border-blue-500/30';
                        case 'emerald': return 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30';
                        case 'rose': return 'bg-rose-500/20 text-rose-700 dark:text-rose-400 border border-rose-500/30';
                        default: return 'bg-slate-500/20 text-slate-700 dark:text-slate-300';
                    }
                },

                getStatusBadgeClass(status) {
                    switch (status) {
                        case 'completed':
                        case 'resolved':
                        case 'closed':
                            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-400';
                        case 'in_progress':
                        case 'working':
                        case 'dispatched':
                            return 'bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-400';
                        case 'pending':
                        case 'scheduled':
                        case 'assigned':
                        case 'new':
                            return 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-400';
                        default:
                            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400';
                    }
                }
            };
        }
    </script>
</x-app-layout>
