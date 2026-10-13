<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#222222]">Operations Calendar</h1>
                <div class="text-xs text-[#555555]">Unified Schedule</div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('site-surveys.create') }}" class="px-3 py-1.5 rounded text-xs font-normal border border-[#cccccc] bg-white text-[#222222] hover:bg-[#f5f5f5] transition">
                    + Survey
                </a>
                <a href="{{ route('jobs.create-general') }}" class="px-3 py-1.5 rounded text-xs font-normal border border-[#cccccc] bg-white text-[#222222] hover:bg-[#f5f5f5] transition">
                    + Installation
                </a>
                <a href="{{ route('service-tickets.create') }}" class="px-3 py-1.5 rounded text-xs font-normal border border-[#cccccc] bg-white text-[#222222] hover:bg-[#f5f5f5] transition">
                    + Ticket
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

    <div class="pg-wrap" x-data="calendarApp(window.crmCalendarConfig)">
        <div class="pg-inner space-y-4">

            {{-- 1. Stat cards: 4 plain cards (gray label + big number, 4px radius, 10px 14px padding) --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                <div class="stat-card rounded-[4px]">
                    <div class="text-xs font-medium text-slate-500">Scheduled</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5" x-text="events.length">
                        {{ $stats['total'] }}
                    </div>
                </div>

                <div class="stat-card rounded-[4px]">
                    <div class="text-xs font-medium text-slate-500">Surveys</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5" x-text="countByType('survey')">
                        {{ $stats['surveys'] }}
                    </div>
                </div>

                <div class="stat-card rounded-[4px]">
                    <div class="text-xs font-medium text-slate-500">Installations</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5" x-text="countByType('job')">
                        {{ $stats['jobs'] }}
                    </div>
                </div>

                <div class="stat-card rounded-[4px]">
                    <div class="text-xs font-medium text-slate-500">Tickets</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5" x-text="countByType('ticket')">
                        {{ $stats['tickets'] }}
                    </div>
                </div>
            </div>

            {{-- 2. Filters: Plain row without outer container box, 36px inputs, 4px corners, 25px select padding-right --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                
                {{-- Left Filters: Search, Category, Technician, Status --}}
                <div class="flex flex-wrap items-center gap-2.5 flex-1">
                    {{-- Search box --}}
                    <div class="relative w-full sm:w-56">
                        <input type="text" x-model="searchQuery" placeholder="Search..." 
                               class="w-full h-[38px] px-3 text-xs rounded-[4px] border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-slate-400">
                    </div>

                    {{-- Category Dropdown --}}
                    <select x-model="filterCategory" style="padding-right: 25px;"
                            class="h-[38px] pl-2.5 text-xs rounded-[4px] border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                        <option value="all">All Categories</option>
                        <option value="survey">Surveys</option>
                        <option value="job">Installations</option>
                        <option value="ticket">Tickets</option>
                        <option value="amc">AMC Visits</option>
                    </select>

                    {{-- Technician Dropdown --}}
                    <select x-model="filterTech" style="padding-right: 25px;"
                            class="h-[38px] pl-2.5 text-xs rounded-[4px] border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                        <option value="all">All Technicians</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>

                    {{-- Status Dropdown --}}
                    <select x-model="filterStatus" style="padding-right: 25px;"
                            class="h-[38px] pl-2.5 text-xs rounded-[4px] border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                {{-- Right View Mode: 3 Plain Buttons/Links (Month, Week, Agenda) --}}
                <div class="flex items-center gap-1.5 self-start lg:self-auto">
                    <button type="button" @click="currentView = 'month'"
                            :class="currentView === 'month' ? 'bg-[#ebebed] text-slate-900 font-semibold border-slate-300' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-[#ddd] dark:border-slate-700 hover:bg-slate-50'"
                            class="h-[38px] px-3 text-xs rounded-[4px] border transition">
                        Month
                    </button>
                    <button type="button" @click="currentView = 'week'"
                            :class="currentView === 'week' ? 'bg-[#ebebed] text-slate-900 font-semibold border-slate-300' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-[#ddd] dark:border-slate-700 hover:bg-slate-50'"
                            class="h-[38px] px-3 text-xs rounded-[4px] border transition">
                        Week
                    </button>
                    <button type="button" @click="currentView = 'agenda'"
                            :class="currentView === 'agenda' ? 'bg-[#ebebed] text-slate-900 font-semibold border-slate-300' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-[#ddd] dark:border-slate-700 hover:bg-slate-50'"
                            class="h-[38px] px-3 text-xs rounded-[4px] border transition">
                        Agenda
                    </button>
                </div>

            </div>

            {{-- 3. Main Split View: Calendar Views + Right Side Today's Jobs Panel --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                
                {{-- Main Calendar Column (8 cols on lg) --}}
                <div class="lg:col-span-8 space-y-3">
                    
                    {{-- Month Navigator --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="prevMonth()" class="px-2.5 py-1 text-xs font-semibold rounded border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 text-slate-700 dark:text-slate-200" title="Previous Month">
                                &larr; Prev
                            </button>
                            <span class="text-sm font-bold text-slate-900 dark:text-white min-w-[130px] text-center" x-text="monthTitle">
                                {{ $currentMonth->format('F Y') }}
                            </span>
                            <button type="button" @click="nextMonth()" class="px-2.5 py-1 text-xs font-semibold rounded border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 text-slate-700 dark:text-slate-200" title="Next Month">
                                Next &rarr;
                            </button>
                            <button type="button" @click="goToToday()" class="px-2.5 py-1 text-xs font-semibold rounded border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 text-slate-700 dark:text-slate-200 ml-1">
                                Today
                            </button>
                        </div>
                    </div>

                    {{-- ── MONTH VIEW: Plain table with 1px solid #ddd borders, gray headers, empty outside cells ── --}}
                    <div x-show="currentView === 'month'" class="border border-[#ddd] dark:border-slate-800 rounded-[4px] overflow-hidden bg-white dark:bg-[#0f172a]">
                        
                        {{-- 7-Day Header: Gray cells, same color for all weekdays --}}
                        <div class="grid grid-cols-7 border-b border-[#ddd] dark:border-slate-800 bg-[#f8f9fa] dark:bg-slate-900 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 py-2 select-none">
                            <div class="border-r border-[#ddd] dark:border-slate-800">Mon</div>
                            <div class="border-r border-[#ddd] dark:border-slate-800">Tue</div>
                            <div class="border-r border-[#ddd] dark:border-slate-800">Wed</div>
                            <div class="border-r border-[#ddd] dark:border-slate-800">Thu</div>
                            <div class="border-r border-[#ddd] dark:border-slate-800">Fri</div>
                            <div class="border-r border-[#ddd] dark:border-slate-800">Sat</div>
                            <div>Sun</div>
                        </div>

                        {{-- Month Grid Days --}}
                        <div class="grid grid-cols-7">
                            <template x-for="(day, dIndex) in calendarDays" :key="day.date">
                                <div class="border-b border-[#ddd] dark:border-slate-800 min-h-[105px] transition-colors"
                                     :class="{
                                         'border-r border-[#ddd] dark:border-slate-800': (dIndex + 1) % 7 !== 0,
                                         'bg-[#fff8dc] dark:bg-yellow-950/30': day.isCurrentMonth && day.isToday,
                                         'bg-white dark:bg-[#0f172a]': day.isCurrentMonth && !day.isToday,
                                         'bg-[#fafafa] dark:bg-slate-900/30': !day.isCurrentMonth
                                     }">
                                    
                                    {{-- Outside Current Month: Cell is left completely empty as requested --}}
                                    <template x-if="!day.isCurrentMonth">
                                        <div class="h-full w-full"></div>
                                    </template>

                                    {{-- Current Month: Show date number and events --}}
                                    <template x-if="day.isCurrentMonth">
                                        <div class="p-1.5 h-full flex flex-col justify-between">
                                            
                                            {{-- Date number --}}
                                            <div class="flex items-center justify-between mb-1 select-none">
                                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300"
                                                      :class="day.isToday ? 'font-bold text-slate-900 dark:text-white' : ''"
                                                      x-text="day.dayNumber">
                                                </span>
                                                <span x-show="getDayEvents(day.date).length > 0" 
                                                      class="text-[10px] text-slate-400 font-medium"
                                                      x-text="getDayEvents(day.date).length">
                                                </span>
                                            </div>

                                            {{-- Event List: 1 short clean line per event with full text in title attribute (tooltip) --}}
                                            <div class="space-y-1 overflow-y-auto max-h-[75px] flex-1">
                                                <template x-for="event in getDayEvents(day.date).slice(0, 3)" :key="event.id">
                                                    <div @click="openDrawer(event)"
                                                         :title="`${event.time} - ${event.title}\nCustomer: ${event.customer}\nAddress: ${event.address}\nTechnician: ${event.tech}`"
                                                         class="cursor-pointer text-[11px] leading-tight px-1.5 py-0.5 rounded-[3px] border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 truncate hover:bg-[#ebebed] dark:hover:bg-slate-700 transition"
                                                         :class="getEventTextColor(event)"
                                                         x-text="getShortEventTitle(event)">
                                                    </div>
                                                </template>

                                                {{-- +N more popover button --}}
                                                <div x-show="getDayEvents(day.date).length > 3" 
                                                     @click="openDaySummary(day)"
                                                     class="cursor-pointer text-[10px] font-semibold text-blue-600 dark:text-blue-400 hover:underline pt-0.5">
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
                    <div x-show="currentView === 'week'" class="border border-[#ddd] dark:border-slate-800 rounded-[4px] overflow-hidden bg-white dark:bg-[#0f172a]">
                        <div class="p-3 border-b border-[#ddd] dark:border-slate-800 bg-[#f8f9fa] dark:bg-slate-900 flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <span>Week Schedule</span>
                            <span class="text-slate-400 font-normal" x-text="weekRangeText"></span>
                        </div>
                        <div class="divide-y divide-[#eee] dark:divide-slate-800">
                            <template x-for="day in activeWeekDays" :key="day.date">
                                <div class="p-3 transition" :class="day.isToday ? 'bg-[#fefce8] dark:bg-yellow-950/20' : ''">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="day.dayName + ', ' + day.formattedDate"></span>
                                            <span x-show="day.isToday" class="px-1.5 py-0.5 text-[10px] font-bold rounded-[3px] bg-amber-200 text-amber-900">Today</span>
                                        </div>
                                        <span class="text-xs text-slate-400" x-text="getDayEvents(day.date).length + ' jobs'"></span>
                                    </div>
                                    
                                    <div x-show="getDayEvents(day.date).length === 0" class="text-xs text-slate-400 italic">
                                        No scheduled operations for this date.
                                    </div>

                                    <div class="space-y-1.5" x-show="getDayEvents(day.date).length > 0">
                                        <template x-for="event in getDayEvents(day.date)" :key="event.id">
                                            <div @click="openDrawer(event)" 
                                                 class="cursor-pointer p-2 rounded-[4px] border border-[#ddd] dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-between gap-3 text-xs">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-slate-500 whitespace-nowrap" x-text="event.time"></span>
                                                        <span class="font-semibold truncate" :class="getEventTextColor(event)" x-text="event.title"></span>
                                                    </div>
                                                    <div class="text-[11px] text-slate-500 truncate" x-text="event.address"></div>
                                                </div>
                                                <div class="text-[11px] text-slate-400 shrink-0">
                                                    Tech: <span class="text-slate-700 dark:text-slate-300 font-medium" x-text="event.tech"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ── AGENDA VIEW ── --}}
                    <div x-show="currentView === 'agenda'" class="border border-[#ddd] dark:border-slate-800 rounded-[4px] overflow-hidden bg-white dark:bg-[#0f172a]">
                        <div class="p-3 border-b border-[#ddd] dark:border-slate-800 bg-[#f8f9fa] dark:bg-slate-900 flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <span>Operations List</span>
                            <span class="text-slate-400 font-normal" x-text="filteredEventsList.length + ' operations'"></span>
                        </div>

                        <div x-show="filteredEventsList.length === 0" class="p-8 text-center text-xs text-slate-400">
                            No scheduled operations match your filters.
                        </div>

                        <div class="divide-y divide-[#eee] dark:divide-slate-800" x-show="filteredEventsList.length > 0">
                            <template x-for="event in filteredEventsList" :key="event.id">
                                <div @click="openDrawer(event)" class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition flex items-center justify-between gap-3 text-xs">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-500 whitespace-nowrap" x-text="event.date + ' ' + event.time"></span>
                                            <span class="font-bold text-slate-800 dark:text-slate-100 truncate" x-text="event.title"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 truncate mt-0.5" x-text="event.customer + ' • ' + event.address"></div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            Tech: <span class="text-slate-700 dark:text-slate-300 font-medium" x-text="event.tech"></span>
                                        </div>
                                    </div>
                                    <button type="button" class="text-blue-600 hover:underline font-semibold shrink-0">
                                        View
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>

                {{-- 5. Right Column: Today's Jobs Panel (4px corners, time on own line with nowrap, full job title wrap) --}}
                <div class="lg:col-span-4 space-y-4">
                    <div class="bg-white dark:bg-[#0f172a] border border-[#ddd] dark:border-slate-800 rounded-[4px] p-4">
                        <div class="flex items-center justify-between pb-3 border-b border-[#ddd] dark:border-slate-800">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Today's Jobs</h3>
                            <span class="text-xs text-slate-400">{{ now()->format('d M Y') }}</span>
                        </div>

                        <div class="divide-y divide-[#eee] dark:divide-slate-800 max-h-[480px] overflow-y-auto">
                            <template x-for="event in todayEvents" :key="event.id">
                                <div class="py-3 text-xs flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        {{-- Time on its own line with white-space: nowrap --}}
                                        <div class="text-[11px] font-bold text-slate-500 whitespace-nowrap" x-text="event.time"></div>
                                        {{-- Job title wrapping fully --}}
                                        <div class="font-semibold mt-0.5 break-words" :class="getEventTextColor(event)" x-text="event.title"></div>
                                        <div class="text-slate-500 text-[11px] mt-0.5 break-words" x-text="event.address"></div>
                                        <div class="text-slate-400 text-[11px] mt-0.5">
                                            Technician: <span class="text-slate-700 dark:text-slate-300 font-medium" x-text="event.tech"></span>
                                        </div>
                                    </div>
                                    <button type="button" @click="openDrawer(event)" class="text-blue-600 hover:underline font-semibold text-xs shrink-0 pt-0.5">
                                        View
                                    </button>
                                </div>
                            </template>

                            <div class="py-6 text-center text-xs text-slate-400" x-show="todayEvents.length === 0">
                                No jobs scheduled today.
                            </div>
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
             class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-2xs" 
             style="display: none;"></div>

        <div x-show="drawerOpen" 
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white dark:bg-[#0f172a] shadow-xl border-l border-[#ddd] dark:border-slate-800 flex flex-col justify-between"
             style="display: none;">
            
            {{-- Drawer Header --}}
            <div>
                <div class="p-4 border-b border-[#ddd] dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white" x-text="selectedEvent ? selectedEvent.badge : 'Job Details'"></h3>
                    <button type="button" @click="closeDrawer()" class="p-1 rounded text-slate-400 hover:text-slate-700 dark:hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-4 space-y-3.5 overflow-y-auto max-h-[calc(100vh-140px)]" x-show="selectedEvent">
                    <div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white" x-text="selectedEvent ? selectedEvent.title : ''"></h4>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            <span x-text="selectedEvent ? selectedEvent.date : ''"></span> &bull; <span x-text="selectedEvent ? selectedEvent.time : ''"></span>
                        </div>
                    </div>

                    {{-- Customer details --}}
                    <div class="p-3 rounded border border-[#ddd] dark:border-slate-800 bg-[#f8f9fa] dark:bg-slate-900 text-xs space-y-1">
                        <div class="font-semibold text-slate-700 dark:text-slate-300">Customer</div>
                        <div class="font-bold text-slate-900 dark:text-white" x-text="selectedEvent ? selectedEvent.customer : ''"></div>
                        <div class="text-slate-500" x-text="'Phone: ' + (selectedEvent ? selectedEvent.phone : '—')"></div>
                        <div class="text-slate-500" x-text="'Address: ' + (selectedEvent ? selectedEvent.address : '—')"></div>
                    </div>

                    {{-- Technician --}}
                    <div class="p-3 rounded border border-[#ddd] dark:border-slate-800 bg-[#f8f9fa] dark:bg-slate-900 text-xs space-y-1">
                        <div class="font-semibold text-slate-700 dark:text-slate-300">Technician</div>
                        <div class="font-bold text-slate-900 dark:text-white" x-text="selectedEvent ? selectedEvent.tech : 'Unassigned'"></div>
                    </div>

                    {{-- Notes --}}
                    <div class="p-3 rounded border border-[#ddd] dark:border-slate-800 bg-[#f8f9fa] dark:bg-slate-900 text-xs space-y-1" x-show="selectedEvent && selectedEvent.notes">
                        <div class="font-semibold text-slate-700 dark:text-slate-300">Notes</div>
                        <p class="text-slate-600 dark:text-slate-400 whitespace-pre-line" x-text="selectedEvent ? selectedEvent.notes : ''"></p>
                    </div>
                </div>
            </div>

            {{-- Drawer Footer --}}
            <div class="p-3.5 border-t border-[#ddd] dark:border-slate-800 bg-[#f8f9fa] dark:bg-slate-900 flex items-center justify-between gap-3">
                <button type="button" @click="closeDrawer()" class="px-3 py-1.5 rounded border border-[#ddd] dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 transition">
                    Close
                </button>
                <template x-if="selectedEvent && selectedEvent.url">
                    <a :href="selectedEvent.url" class="px-3 py-1.5 rounded bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition">
                        Open Details &rarr;
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
                    if (event.type === 'amc') return 'AMC';
                    if (event.type === 'ticket') return 'Ticket';
                    return 'Job';
                },

                getEventTextColor(event) {
                    if (event.type === 'survey') return 'text-emerald-600 dark:text-emerald-400 font-bold';
                    if (event.type === 'job') return 'text-blue-600 dark:text-blue-400 font-bold';
                    if (event.type === 'ticket') return 'text-amber-600 dark:text-amber-400 font-bold';
                    if (event.type === 'amc') return 'text-purple-600 dark:text-purple-400 font-bold';
                    return 'text-slate-800 dark:text-slate-200 font-semibold';
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
