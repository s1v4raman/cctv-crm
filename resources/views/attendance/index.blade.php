<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-[var(--text-main)]">Attendance</h1>
                <p class="text-sm text-[var(--text-muted)] mt-0.5">{{ $totalEmployees }} {{ Str::plural('person', $totalEmployees) }} on the roster</p>
            </div>
            <button type="button" onclick="openCreateModal()" class="btn-primary">Add entry</button>
        </div>
    </x-slot>

    <style>
        .pg-wrap { background:#f8fafc; min-height:100vh; padding:1.5rem 0 3rem; }
        .dark .pg-wrap { background:#060913; }
        .pg-inner { max-width:1440px; margin:0 auto; padding:0 1.25rem; }
        .pg-card {
            background:#ffffff; border-radius:4px; border:1px solid #e2e8f0;
            box-shadow:none !important;
        }
        .dark .pg-card { background:#0f172a; border-color: var(--text-main); }
        .custom-scrollbar::-webkit-scrollbar { height: 7px; width: 7px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--card-border); }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner space-y-6">

            {{-- Flash Messages --}}
            @if(session('status'))
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ session('status') }}
                    </span>
                    <x-close-button size="sm" tone="inherit" label="Dismiss message" onclick="this.parentElement.remove()" />
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        {{ session('error') }}
                    </span>
                    <x-close-button size="sm" tone="inherit" label="Dismiss message" onclick="this.parentElement.remove()" />
                </div>
            @endif

            {{-- Employee Category Sub-Navigation --}}
            <x-employee-subnav active="attendance" />

            {{-- Your own clock-in for today --}}
            <div class="pg-card px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <p class="text-sm text-slate-700 dark:text-slate-300">
                    @if(!$myAttendanceToday)
                        You haven't clocked in today.
                    @elseif(!$myAttendanceToday->clock_in)
                        Today you're marked <span class="font-medium text-slate-900 dark:text-white">{{ strtolower($myAttendanceToday->status_label) }}</span>.
                    @elseif(!$myAttendanceToday->clock_out)
                        You clocked in at <span class="font-medium text-slate-900 dark:text-white">{{ $myAttendanceToday->formatted_clock_in }}</span>.
                    @else
                        Your shift today: {{ $myAttendanceToday->formatted_clock_in }} to {{ $myAttendanceToday->formatted_clock_out }} ({{ $myAttendanceToday->total_hours }} hrs).
                    @endif
                </p>
                @if(!$myAttendanceToday || !$myAttendanceToday->clock_in)
                    <form method="POST" action="{{ route('attendance.clock-in') }}" class="flex items-center gap-2">
                        @csrf
                        <select aria-label="Where you are working" name="location_type" class="text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-1.5 text-slate-700 dark:text-slate-200">
                            <option value="office">Office</option>
                            <option value="on_site">Client site</option>
                            <option value="remote">Remote / field</option>
                        </select>
                        <button type="submit" class="btn-primary">Clock in</button>
                    </form>
                @elseif(!$myAttendanceToday->clock_out)
                    <form method="POST" action="{{ route('attendance.clock-out') }}">
                        @csrf
                        <button type="submit" class="btn-secondary">Clock out</button>
                    </form>
                @endif
            </div>

            {{-- 3 Dedicated Modes / Tabs Switcher --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-2">
                <div class="inline-flex p-1 rounded bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 gap-1" style="border-radius: 4px;">
                    <a href="{{ route('attendance.index', ['tab' => 'daily', 'date' => $selectedDate, 'role' => $roleFilter, 'search' => $search]) }}"
                       class="px-3.5 py-1.5 rounded text-xs font-semibold transition {{ $tab === 'daily' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}" style="border-radius: 4px;">
                        Day
                    </a>
                    <a href="{{ route('attendance.index', ['tab' => 'weekly', 'week_date' => $weekInput, 'role' => $roleFilter, 'search' => $search]) }}"
                       class="px-3.5 py-1.5 rounded text-xs font-semibold transition {{ $tab === 'weekly' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}" style="border-radius: 4px;">
                        Week
                    </a>
                    <a href="{{ route('attendance.index', ['tab' => 'monthly', 'month' => $selectedMonth, 'role' => $roleFilter, 'search' => $search]) }}"
                       class="px-3.5 py-1.5 rounded text-xs font-semibold transition {{ $tab === 'monthly' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}" style="border-radius: 4px;">
                        Month
                    </a>
                </div>

                {{-- Global Filters (Role & Search) --}}
                <form method="GET" action="{{ route('attendance.index') }}" class="flex items-center gap-2 flex-wrap">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    @if($tab === 'daily')
                        <input type="hidden" name="date" value="{{ $selectedDate }}">
                    @elseif($tab === 'weekly')
                        <input type="hidden" name="week_date" value="{{ $weekInput }}">
                    @else
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    @endif

                    <select aria-label="Role" name="role" onchange="this.form.submit()" class="text-xs font-bold rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-slate-800 dark:text-slate-200">
                        <option value="">All Roles ({{ $totalEmployees }})</option>
                        <option value="technician" @selected($roleFilter === 'technician')>Technicians</option>
                        <option value="staff" @selected($roleFilter === 'staff')>Staff / Employees</option>
                        <option value="admin" @selected($roleFilter === 'admin')>Admins</option>
                    </select>

                    <div class="relative flex items-center">
                        <svg class="w-3.5 h-3.5 absolute left-2.5 pointer-events-none text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        <input aria-label="Search employee" type="text" name="search" value="{{ $search }}" placeholder="Search employee..."
                               style="padding-left: 2.25rem !important;"
                               class="text-xs rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 pr-3 py-1.5 text-slate-800 dark:text-slate-200 w-44 focus:w-56 transition-all">
                    </div>

                    @if($roleFilter || $search)
                        <a href="{{ route('attendance.index', ['tab' => $tab, 'date' => $selectedDate, 'week_date' => $weekInput, 'month' => $selectedMonth]) }}"
                           class="text-xs font-bold text-rose-500 hover:underline">Clear</a>
                    @endif
                </form>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 1: PER-DAY ATTENDANCE & ANALYSIS                     --}}
            {{-- ======================================================== --}}
            @if($tab === 'daily')

                {{-- The day in numbers --}}
                <dl class="grid grid-cols-3 sm:grid-cols-6 gap-px rounded border border-slate-200 dark:border-slate-800 bg-slate-200 dark:bg-slate-800 overflow-hidden">
                    @foreach([
                        ['Present',    $presentCount],
                        ['Late',       $lateCount],
                        ['Half day',   $halfDayCount],
                        ['Absent',     $absentCount - $unmarkedCount],
                        ['On leave',   $leaveCount],
                        ['Not marked', $unmarkedCount],
                    ] as [$label, $value])
                        <div class="bg-white dark:bg-slate-900 px-4 py-3">
                            <dt class="text-xs text-slate-500 dark:text-slate-400">{{ $label }}</dt>
                            <dd class="text-xl font-semibold text-slate-900 dark:text-white mt-0.5">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                {{-- Day picker and marking everyone left over --}}
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <form method="GET" action="{{ route('attendance.index') }}" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="daily">
                        <input type="hidden" name="role" value="{{ $roleFilter }}">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <a href="{{ route('attendance.index', ['tab' => 'daily', 'date' => \Carbon\Carbon::parse($selectedDate)->subDay()->toDateString(), 'role' => $roleFilter, 'search' => $search]) }}"
                           class="btn-secondary px-2" title="Previous day" aria-label="Previous day">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <input aria-label="Date" type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                               class="text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-1.5 text-slate-800 dark:text-slate-200">
                        <a href="{{ route('attendance.index', ['tab' => 'daily', 'date' => \Carbon\Carbon::parse($selectedDate)->addDay()->toDateString(), 'role' => $roleFilter, 'search' => $search]) }}"
                           class="btn-secondary px-2" title="Next day" aria-label="Next day">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        @if($selectedDate !== now()->toDateString())
                            <a href="{{ route('attendance.index', ['tab' => 'daily', 'role' => $roleFilter, 'search' => $search]) }}" class="text-sm action-link ml-1">Today</a>
                        @endif
                    </form>

                    @if($unmarkedCount > 0)
                    <div class="flex items-center gap-2 flex-wrap">
                        <form method="POST" action="{{ route('attendance.batch-store') }}" data-confirm="Mark the {{ $unmarkedCount }} unmarked {{ Str::plural('person', $unmarkedCount) }} present (full day, 8h) for {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}?">
                            @csrf
                            <input type="hidden" name="mode" value="bulk_day">
                            <input type="hidden" name="date" value="{{ $selectedDate }}">
                            <input type="hidden" name="status" value="present">
                            <input type="hidden" name="target" value="unmarked">
                            <button type="submit" class="btn-secondary">Mark unmarked present</button>
                        </form>
                        <form method="POST" action="{{ route('attendance.batch-store') }}" data-confirm="Mark the {{ $unmarkedCount }} unmarked {{ Str::plural('person', $unmarkedCount) }} absent for {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}?">
                            @csrf
                            <input type="hidden" name="mode" value="bulk_day">
                            <input type="hidden" name="date" value="{{ $selectedDate }}">
                            <input type="hidden" name="status" value="absent">
                            <input type="hidden" name="target" value="unmarked">
                            <button type="submit" class="btn-secondary">Mark unmarked absent</button>
                        </form>
                    </div>
                    @endif
                </div>

                {{-- Daily Attendance Roster Table --}}
                <div class="pg-card overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Daily roster <span class="font-normal text-slate-500 dark:text-slate-400">· {{ \Carbon\Carbon::parse($selectedDate)->format('l, j F Y') }}</span></h3>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $employees->count() }} {{ Str::plural('person', $employees->count()) }} · {{ round($dailyTotalHours, 1) }}h logged @if($dailyTotalOvertime > 0) · {{ round($dailyTotalOvertime, 1) }}h overtime @endif
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                                    <th class="py-3 px-4">Employee</th>
                                    <th class="py-3 px-4">Role</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4">Clock In</th>
                                    <th class="py-3 px-4">Clock Out</th>
                                    <th class="py-3 px-4">Hours</th>
                                    <th class="py-3 px-4">Overtime</th>
                                    <th class="py-3 px-4">Location</th>
                                    <th class="py-3 px-4">Quick Mark</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($employees as $emp)
                                    @php
                                        $att = $attendances->get($emp->id);
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                                                    {{ strtoupper(substr($emp->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $emp->name }}</span>
                                                    <span class="text-[11px] text-slate-400">{{ $emp->email }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-bold uppercase
                                                {{ $emp->role === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : '' }}
                                                {{ $emp->role === 'technician' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300' : '' }}
                                                {{ $emp->role === 'staff' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                            ">
                                                {{ ucfirst($emp->role) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($att)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $att->status_badge_class }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $att->status === 'present' ? 'bg-emerald-500' : ($att->status === 'late' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                                    {{ $att->status_label }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                                    Not Marked
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 font-mono font-semibold text-slate-700 dark:text-slate-300">
                                            {{ $att ? $att->formatted_clock_in : '—' }}
                                        </td>
                                        <td class="py-3 px-4 font-mono font-semibold text-slate-700 dark:text-slate-300">
                                            {{ $att ? $att->formatted_clock_out : '—' }}
                                        </td>
                                        <td class="py-3 px-4 font-semibold">
                                            @if($att && $att->total_hours > 0)
                                                <span class="text-slate-900 dark:text-white font-mono">{{ $att->total_hours }} hrs</span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 font-semibold">
                                            @if($att && $att->overtime_hours > 0)
                                                <span class="text-emerald-600 dark:text-emerald-400 font-mono font-bold">+{{ $att->overtime_hours }} hrs</span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-slate-500 dark:text-slate-400 capitalize">
                                            {{ $att ? str_replace('_', ' ', $att->location_type) : '—' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            {{-- Inline 1-Click Quick Mark Buttons --}}
                                            <div class="flex items-center gap-1">
                                                <form method="POST" action="{{ route('attendance.quick-mark') }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $emp->id }}">
                                                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                                                    <input type="hidden" name="status" value="present">
                                                    <button type="submit" title="Mark Present (8h)"
                                                            class="w-6 h-6 rounded-md text-[11px] font-bold flex items-center justify-center transition {{ ($att && $att->status === 'present') ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-emerald-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 hover:text-emerald-700' }}">
                                                        P
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('attendance.quick-mark') }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $emp->id }}">
                                                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                                                    <input type="hidden" name="status" value="late">
                                                    <button type="submit" title="Mark Late Arrival (8h)"
                                                            class="w-6 h-6 rounded-md text-[11px] font-bold flex items-center justify-center transition {{ ($att && $att->status === 'late') ? 'bg-amber-500 text-white' : 'bg-slate-100 hover:bg-amber-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 hover:text-amber-700' }}">
                                                        L
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('attendance.quick-mark') }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $emp->id }}">
                                                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                                                    <input type="hidden" name="status" value="half_day">
                                                    <button type="submit" title="Mark Half Day (4h)"
                                                            class="w-6 h-6 rounded-md text-[11px] font-bold flex items-center justify-center transition {{ ($att && $att->status === 'half_day') ? 'bg-sky-600 text-white' : 'bg-slate-100 hover:bg-sky-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 hover:text-sky-700' }}">
                                                        H
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('attendance.quick-mark') }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $emp->id }}">
                                                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                                                    <input type="hidden" name="status" value="absent">
                                                    <button type="submit" title="Mark Absent (0h)"
                                                            class="w-6 h-6 rounded-md text-[11px] font-bold flex items-center justify-center transition {{ ($att && $att->status === 'absent') ? 'bg-rose-600 text-white' : 'bg-slate-100 hover:bg-rose-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 hover:text-rose-700' }}">
                                                        A
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('attendance.quick-mark') }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $emp->id }}">
                                                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                                                    <input type="hidden" name="status" value="on_leave">
                                                    <button type="submit" title="Mark On Leave"
                                                            class="w-6 h-6 rounded-md text-[11px] font-bold flex items-center justify-center transition {{ ($att && $att->status === 'on_leave') ? 'bg-purple-600 text-white' : 'bg-slate-100 hover:bg-purple-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 hover:text-purple-700' }}">
                                                        V
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <button type="button" 
                                                    onclick="openEditModal({{ json_encode($emp) }}, {{ json_encode($att) }}, '{{ $selectedDate }}')"
                                                    class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-blue-600 text-slate-600 dark:text-slate-300 font-bold text-[11px] transition">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="py-8 text-center text-slate-400">
                                            No internal employees found matching your filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            {{-- ======================================================== --}}
            {{-- TAB 2: WEEKLY ATTENDANCE & ANALYSIS                      --}}
            {{-- ======================================================== --}}
            @elseif($tab === 'weekly')

                {{-- The week in numbers --}}
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-px rounded border border-slate-200 dark:border-slate-800 bg-slate-200 dark:bg-slate-800 overflow-hidden">
                    @foreach([
                        ['Week', $weekStart->format('j M') . ' – ' . $weekEnd->format('j M Y'), 'week ' . $weekStart->weekOfYear],
                        ['Hours logged', round($weeklyTotalHours, 1) . 'h', $weeklyTotalOvertime > 0 ? round($weeklyTotalOvertime, 1) . 'h overtime' : 'no overtime'],
                        ['Average turnout', $weeklyAvgTurnout . '%', 'of the roster each day'],
                    ] as [$label, $value, $note])
                        <div class="bg-white dark:bg-slate-900 px-4 py-3">
                            <dt class="text-xs text-slate-500 dark:text-slate-400">{{ $label }}</dt>
                            <dd class="text-xl font-semibold text-slate-900 dark:text-white mt-0.5">{{ $value }}</dd>
                            <dd class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $note }}</dd>
                        </div>
                    @endforeach
                </dl>

                {{-- Week picker --}}
                <form method="GET" action="{{ route('attendance.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="tab" value="weekly">
                    <input type="hidden" name="role" value="{{ $roleFilter }}">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <a href="{{ route('attendance.index', ['tab' => 'weekly', 'week_date' => $weekStart->copy()->subWeek()->toDateString(), 'role' => $roleFilter, 'search' => $search]) }}"
                       class="btn-secondary px-2" title="Previous week" aria-label="Previous week">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <input aria-label="Any day in the week" type="date" name="week_date" value="{{ $weekInput }}" onchange="this.form.submit()"
                           class="text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-1.5 text-slate-800 dark:text-slate-200">
                    <a href="{{ route('attendance.index', ['tab' => 'weekly', 'week_date' => $weekStart->copy()->addWeek()->toDateString(), 'role' => $roleFilter, 'search' => $search]) }}"
                       class="btn-secondary px-2" title="Next week" aria-label="Next week">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    @unless($weekStart->isCurrentWeek())
                        <a href="{{ route('attendance.index', ['tab' => 'weekly', 'week_date' => now()->toDateString(), 'role' => $roleFilter, 'search' => $search]) }}" class="text-sm action-link ml-1">This week</a>
                    @endunless
                </form>

                {{-- Day-by-Day Turnout Analysis (Mon - Sun) --}}
                <div class="pg-card p-4">
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3">Hours this week</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-7 gap-2 text-center">
                        @foreach($weekDays as $wDay)
                            @php
                                $dTurnout = $weeklyDailyTurnout[$wDay['date']] ?? ['count' => 0, 'rate' => 0, 'hours' => 0];
                            @endphp
                            <div class="p-3 rounded-xl border {{ $wDay['is_today'] ? 'border-blue-500 bg-blue-50/40 dark:bg-blue-950/30' : ($wDay['is_sunday'] ? 'border-dashed border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30' : 'border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900/50') }}">
                                <span class="text-[11px] font-bold {{ $wDay['is_sunday'] ? 'text-rose-500' : 'text-slate-700 dark:text-slate-300' }} block">
                                    {{ $wDay['day_name'] }}
                                </span>
                                <span class="text-[11px] text-slate-400 block">{{ $wDay['day_num'] }}</span>
                                <div class="my-2">
                                    <span class="text-lg font-black text-slate-900 dark:text-white block">{{ $dTurnout['count'] }} / {{ $totalEmployees }}</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block">{{ $dTurnout['rate'] }}% turnout</span>
                                </div>
                                <span class="inline-block px-2 py-0.5 rounded-md text-[11px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    {{ $dTurnout['hours'] }}h total
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Weekly Interactive Timesheet Matrix Table --}}
                <div class="pg-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Timesheet for the week</h3>
                            <p class="text-[11px] text-slate-400">Click a cell to change that day</p>
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                            <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> P=Present</span>
                            <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> L=Late</span>
                            <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span> H=Half</span>
                            <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> A=Absent</span>
                            <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> V=Leave</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                                    <th class="py-3 px-4 min-w-[200px]">Employee</th>
                                    @foreach($weekDays as $wDay)
                                        <th class="py-3 px-2 text-center min-w-[95px] {{ $wDay['is_today'] ? 'bg-blue-50/50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400' : '' }} {{ $wDay['is_sunday'] ? 'text-rose-500' : '' }}">
                                            <div>{{ $wDay['day_name'] }}</div>
                                            <div class="text-[11px] font-normal opacity-80">{{ $wDay['day_num'] }}</div>
                                        </th>
                                    @endforeach
                                    <th class="py-3 px-3 text-center min-w-[85px]">Worked</th>
                                    <th class="py-3 px-3 text-center min-w-[95px]">Total Hrs</th>
                                    <th class="py-3 px-3 text-center min-w-[85px]">Overtime</th>
                                    <th class="py-3 px-3 text-right min-w-[100px]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($employees as $emp)
                                    @php
                                        $empStats = $weeklyEmployeeStats[$emp->id] ?? ['worked_days' => 0, 'total_hours' => 0, 'overtime_hours' => 0, 'leave_days' => 0];
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                                                    {{ strtoupper(substr($emp->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $emp->name }}</span>
                                                    <span class="text-[11px] text-slate-400 uppercase font-semibold">{{ $emp->role }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- 7 Days Cells --}}
                                        @foreach($weekDays as $wDay)
                                            @php
                                                $dayRecord = $weeklyMatrix[$emp->id][$wDay['date']] ?? null;
                                            @endphp
                                            <td class="py-2.5 px-2 text-center {{ $wDay['is_today'] ? 'bg-blue-50/20 dark:bg-blue-950/20' : '' }}">
                                                @if($dayRecord)
                                                    <button type="button" 
                                                            onclick="openEditModal({{ json_encode($emp) }}, {{ json_encode($dayRecord) }}, '{{ $wDay['date'] }}')"
                                                            title="Click to edit {{ $wDay['day_full'] }} ({{ $dayRecord->status_label }})"
                                                            class="w-full py-1.5 px-1.5 rounded-lg border text-center transition flex flex-col items-center justify-center {{ $dayRecord->status_badge_class }} hover:shadow-xs">
                                                        <span class="font-bold text-[11px]">
                                                            @if($dayRecord->status === 'present') P
                                                            @elseif($dayRecord->status === 'late') L
                                                            @elseif($dayRecord->status === 'half_day') H
                                                            @elseif($dayRecord->status === 'absent') A
                                                            @elseif($dayRecord->status === 'on_leave') V
                                                            @else ? @endif
                                                        </span>
                                                        <span class="text-[11px] font-mono opacity-90">{{ $dayRecord->total_hours }}h</span>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            onclick="openEditModal({{ json_encode($emp) }}, null, '{{ $wDay['date'] }}')"
                                                            title="Click to mark {{ $wDay['day_full'] }}"
                                                            class="w-full py-2 px-1 rounded-lg border border-dashed border-slate-200 dark:border-slate-800 hover:border-blue-400 hover:bg-blue-50/50 dark:hover:bg-blue-950/30 text-slate-300 dark:text-slate-600 hover:text-blue-600 text-center transition">
                                                        <span class="text-[11px] font-bold block">+</span>
                                                    </button>
                                                @endif
                                            </td>
                                        @endforeach

                                        {{-- Weekly Totals --}}
                                        <td class="py-3 px-3 text-center font-bold text-slate-800 dark:text-slate-200">
                                            {{ $empStats['worked_days'] }} d
                                        </td>
                                        <td class="py-3 px-3 text-center font-mono font-bold text-slate-900 dark:text-white">
                                            {{ round($empStats['total_hours'], 1) }}h
                                        </td>
                                        <td class="py-3 px-3 text-center font-mono font-bold">
                                            @if($empStats['overtime_hours'] > 0)
                                                <span class="text-emerald-600 dark:text-emerald-400">+{{ round($empStats['overtime_hours'], 1) }}h</span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-right">
                                            <button type="button"
                                                    onclick="openQuickWeekFillModal({{ json_encode($emp) }}, '{{ $weekStart->toDateString() }}', '{{ $weekEnd->toDateString() }}')"
                                                    class="px-2 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/60 font-bold text-[11px] transition">
                                                Fill Week
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="py-8 text-center text-slate-400">
                                            No employees found for weekly timesheet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            {{-- ======================================================== --}}
            {{-- TAB 3: MONTHLY MATRIX & ANALYTICS                        --}}
            {{-- ======================================================== --}}
            @elseif($tab === 'monthly')

                {{-- The month in numbers --}}
                <dl class="grid grid-cols-2 sm:grid-cols-5 gap-px rounded border border-slate-200 dark:border-slate-800 bg-slate-200 dark:bg-slate-800 overflow-hidden">
                    @foreach([
                        ['Working days', $workingDaysInMonth, \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y')],
                        ['Turnout', $companyMonthlyAttendanceRate . '%', 'of working days'],
                        ['Hours logged', round($monthlyTotalHours, 1) . 'h', $monthlyTotalOvertime > 0 ? round($monthlyTotalOvertime, 1) . 'h overtime' : 'no overtime'],
                        ['Late arrivals', $monthlyLatePunches, $punctualityRate . '% on time'],
                        ['Leave and absence', $monthlyLeaves + $monthlyAbsents, $monthlyLeaves . ' leave · ' . $monthlyAbsents . ' absent'],
                    ] as [$label, $value, $note])
                        <div class="bg-white dark:bg-slate-900 px-4 py-3">
                            <dt class="text-xs text-slate-500 dark:text-slate-400">{{ $label }}</dt>
                            <dd class="text-xl font-semibold text-slate-900 dark:text-white mt-0.5">{{ $value }}</dd>
                            <dd class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $note }}</dd>
                        </div>
                    @endforeach
                </dl>

                {{-- Month picker --}}
                @php
                    $prevMonth = \Carbon\Carbon::parse($selectedMonth . '-01')->subMonth()->format('Y-m');
                    $nextMonth = \Carbon\Carbon::parse($selectedMonth . '-01')->addMonth()->format('Y-m');
                @endphp
                <form method="GET" action="{{ route('attendance.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="tab" value="monthly">
                    <input type="hidden" name="role" value="{{ $roleFilter }}">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <a href="{{ route('attendance.index', ['tab' => 'monthly', 'month' => $prevMonth, 'role' => $roleFilter, 'search' => $search]) }}"
                       class="btn-secondary px-2" title="Previous month" aria-label="Previous month">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <input aria-label="Month" type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()"
                           class="text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-1.5 text-slate-800 dark:text-slate-200">
                    <a href="{{ route('attendance.index', ['tab' => 'monthly', 'month' => $nextMonth, 'role' => $roleFilter, 'search' => $search]) }}"
                       class="btn-secondary px-2" title="Next month" aria-label="Next month">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    @if($selectedMonth !== now()->format('Y-m'))
                        <a href="{{ route('attendance.index', ['tab' => 'monthly', 'month' => now()->format('Y-m'), 'role' => $roleFilter, 'search' => $search]) }}" class="text-sm action-link ml-1">This month</a>
                    @endif
                </form>

                {{-- Full Monthly Heatmap / Calendar Matrix (Days 1 to End of Month) --}}
                <div class="pg-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Timesheet for the month</h3>
                            <p class="text-[11px] text-slate-400">Days 1 through {{ count($monthDays) }} for {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}. Click any square to record or update attendance.</p>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] font-semibold text-slate-500">
                            <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span> Present</span>
                            <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-amber-500"></span> Late</span>
                            <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-sky-500"></span> Half</span>
                            <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-rose-500"></span> Absent</span>
                            <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-purple-500"></span> Leave</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                                    <th class="py-3 px-3 min-w-[180px] sticky left-0 bg-slate-50 dark:bg-slate-900 z-10 shadow-xs">Employee</th>
                                    @foreach($monthDays as $mDay)
                                        <th class="py-2.5 px-1 text-center min-w-[32px] {{ $mDay['is_today'] ? 'bg-blue-100/60 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 font-black' : '' }} {{ $mDay['is_sunday'] ? 'text-rose-500' : '' }}">
                                            <span class="block text-[11px]">{{ $mDay['day_num'] }}</span>
                                            <span class="block text-[11px] font-normal opacity-70">{{ substr($mDay['day_name'], 0, 1) }}</span>
                                        </th>
                                    @endforeach
                                    <th class="py-3 px-3 text-center min-w-[80px]">Days</th>
                                    <th class="py-3 px-3 text-center min-w-[80px]">Hours</th>
                                    <th class="py-3 px-3 text-center min-w-[80px]">Overtime</th>
                                    <th class="py-3 px-3 text-center min-w-[80px]">Turnout</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($employees as $emp)
                                    @php
                                        $mStats = $monthlyEmployeeStats[$emp->id] ?? [
                                            'present_days' => 0, 'late_days' => 0, 'half_days' => 0,
                                            'absent_days' => 0, 'leave_days' => 0, 'effective_days' => 0,
                                            'total_hours' => 0, 'overtime_hours' => 0, 'attendance_rate' => 0,
                                        ];
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-2.5 px-3 sticky left-0 bg-white dark:bg-[#0f172a] z-10 shadow-xs">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-md bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-[11px]">
                                                    {{ strtoupper(substr($emp->name, 0, 1)) }}
                                                </div>
                                                <div class="truncate max-w-[120px]">
                                                    <span class="font-bold text-slate-900 dark:text-white block truncate">{{ $emp->name }}</span>
                                                    <span class="text-[11px] text-slate-400 uppercase font-semibold">{{ $emp->role }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Days 1..31 Cells --}}
                                        @foreach($monthDays as $mDay)
                                            @php
                                                $rec = $monthlyMatrix[$emp->id][$mDay['date']] ?? null;
                                            @endphp
                                            <td class="py-2 px-0.5 text-center {{ $mDay['is_today'] ? 'bg-blue-50/30 dark:bg-blue-950/20' : '' }} {{ $mDay['is_sunday'] ? 'bg-slate-50/40 dark:bg-slate-900/30' : '' }}">
                                                @if($rec)
                                                    <button type="button"
                                                            onclick="openEditModal({{ json_encode($emp) }}, {{ json_encode($rec) }}, '{{ $mDay['date'] }}')"
                                                            title="{{ $mDay['date'] }}: {{ $rec->status_label }} ({{ $rec->total_hours }}h)"
                                                            class="w-6 h-6 mx-auto rounded text-[11px] font-bold flex items-center justify-center transition
                                                                {{ $rec->status === 'present' ? 'bg-emerald-500 hover:bg-emerald-600 text-white' : '' }}
                                                                {{ $rec->status === 'late' ? 'bg-amber-400 hover:bg-amber-500 text-slate-900 font-black' : '' }}
                                                                {{ $rec->status === 'half_day' ? 'bg-sky-500 hover:bg-sky-600 text-white' : '' }}
                                                                {{ $rec->status === 'absent' ? 'bg-rose-500 hover:bg-rose-600 text-white' : '' }}
                                                                {{ $rec->status === 'on_leave' ? 'bg-purple-500 hover:bg-purple-600 text-white' : '' }}
                                                            ">
                                                        @if($rec->status === 'present') P
                                                        @elseif($rec->status === 'late') L
                                                        @elseif($rec->status === 'half_day') H
                                                        @elseif($rec->status === 'absent') A
                                                        @elseif($rec->status === 'on_leave') V
                                                        @else ? @endif
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            onclick="openEditModal({{ json_encode($emp) }}, null, '{{ $mDay['date'] }}')"
                                                            title="Add attendance for {{ $mDay['date'] }}"
                                                            class="w-6 h-6 mx-auto rounded border border-transparent hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-slate-300 dark:text-slate-600 hover:text-blue-500 flex items-center justify-center text-[11px] transition">
                                                        ·
                                                    </button>
                                                @endif
                                            </td>
                                        @endforeach

                                        {{-- Monthly Totals --}}
                                        <td class="py-2.5 px-3 text-center font-bold text-slate-800 dark:text-slate-200">
                                            {{ $mStats['effective_days'] }} d
                                        </td>
                                        <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-900 dark:text-white">
                                            {{ round($mStats['total_hours'], 1) }}h
                                        </td>
                                        <td class="py-2.5 px-3 text-center font-mono font-bold">
                                            @if($mStats['overtime_hours'] > 0)
                                                <span class="text-emerald-600 dark:text-emerald-400">+{{ round($mStats['overtime_hours'], 1) }}h</span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-bold 
                                                {{ $mStats['attendance_rate'] >= 85 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : ($mStats['attendance_rate'] >= 70 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300') }}">
                                                {{ $mStats['attendance_rate'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($monthDays) + 5 }}" class="py-8 text-center text-slate-400">
                                            No employees found for this monthly report.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Monthly Employee Analysis Cards --}}
                <div class="pg-card p-5">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white mb-4">Employee Performance &amp; Attendance Breakdown</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($employees as $emp)
                            @php
                                $mStats = $monthlyEmployeeStats[$emp->id] ?? [
                                    'present_days' => 0, 'late_days' => 0, 'half_days' => 0,
                                    'absent_days' => 0, 'leave_days' => 0, 'effective_days' => 0,
                                    'total_hours' => 0, 'overtime_hours' => 0, 'attendance_rate' => 0,
                                ];
                            @endphp
                            <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center">
                                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-xs text-slate-900 dark:text-white block">{{ $emp->name }}</span>
                                            <span class="text-[11px] text-slate-400 capitalize">{{ $emp->role }}</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-black px-2 py-0.5 rounded-full {{ $mStats['attendance_rate'] >= 85 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' }}">
                                        {{ $mStats['attendance_rate'] }}% Turnout
                                    </span>
                                </div>

                                {{-- Progress bar --}}
                                <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $mStats['attendance_rate'] }}%"></div>
                                </div>

                                <div class="grid grid-cols-4 gap-2 mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-800 text-center">
                                    <div>
                                        <span class="text-[11px] text-slate-400 block uppercase">Present</span>
                                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $mStats['present_days'] }} d</span>
                                    </div>
                                    <div>
                                        <span class="text-[11px] text-slate-400 block uppercase">Late</span>
                                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400">{{ $mStats['late_days'] }} d</span>
                                    </div>
                                    <div>
                                        <span class="text-[11px] text-slate-400 block uppercase">Total Hrs</span>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 font-mono">{{ round($mStats['total_hours'], 1) }}h</span>
                                    </div>
                                    <div>
                                        <span class="text-[11px] text-slate-400 block uppercase">Overtime</span>
                                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-mono">+{{ round($mStats['overtime_hours'], 1) }}h</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            @endif

        </div>
    </div>

    {{-- Manual Attendance Entry / Edit Modal --}}
    <div id="manualAttendanceModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white" id="modalTitle">Record Employee Attendance</h3>
                <x-close-button onclick="document.getElementById('manualAttendanceModal').classList.add('hidden')" />
            </div>

            <form method="POST" action="{{ route('attendance.store') }}" class="space-y-4 mt-4">
                @csrf
                <input type="hidden" name="tab" value="{{ $tab }}">

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Employee *</label>
                    <select aria-label="Employee" name="user_id" id="modalUserId" required class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ ucfirst($emp->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Date *</label>
                        <input aria-label="Date" type="date" name="date" id="modalDate" value="{{ $selectedDate }}" required class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status *</label>
                        <select aria-label="Status" name="status" id="modalStatus" required onchange="adjustModalHoursByStatus(this.value)" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                            <option value="present">Present (Full Day)</option>
                            <option value="late">Late Arrival</option>
                            <option value="half_day">Half Day</option>
                            <option value="absent">Absent</option>
                            <option value="on_leave">On Leave</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Clock In</label>
                        <input aria-label="Clock In" type="time" name="clock_in" id="modalClockIn" value="09:00" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Clock Out</label>
                        <input aria-label="Clock Out" type="time" name="clock_out" id="modalClockOut" value="18:00" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Total Hours</label>
                        <input aria-label="Total Hours" type="number" step="0.25" name="total_hours" id="modalTotalHours" placeholder="e.g. 8.0" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Overtime (Hrs)</label>
                        <input aria-label="Overtime (Hrs)" type="number" step="0.25" name="overtime_hours" id="modalOvertimeHours" placeholder="e.g. 1.5" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Location</label>
                    <select aria-label="Location" name="location_type" id="modalLocation" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                        <option value="office">Office</option>
                        <option value="on_site">On-Site Client</option>
                        <option value="remote">Remote / Field</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Remarks / Notes</label>
                    <input aria-label="Remarks / Notes" type="text" name="notes" id="modalNotes" placeholder="Optional notes (e.g. Approved leave, Client site installation)" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('manualAttendanceModal').classList.add('hidden')" class="px-5 py-2.5 min-h-[44px] rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 min-h-[44px] rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md shadow-blue-500/25">Save Record</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk / Range Attendance Fill Modal --}}
    <div id="bulkAttendanceModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Bulk / Range Attendance Fill</h3>
                <x-close-button onclick="document.getElementById('bulkAttendanceModal').classList.add('hidden')" />
            </div>

            <form method="POST" action="{{ route('attendance.batch-store') }}" class="space-y-4 mt-4">
                @csrf
                <input type="hidden" name="mode" value="bulk_range">

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Select Employee *</label>
                    <select aria-label="Select Employee" name="user_id" id="bulkUserId" required class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ ucfirst($emp->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Start Date *</label>
                        <input aria-label="Start Date" type="date" name="start_date" id="bulkStartDate" value="{{ $selectedDate }}" required class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">End Date *</label>
                        <input aria-label="End Date" type="date" name="end_date" id="bulkEndDate" value="{{ $selectedDate }}" required class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status *</label>
                        <select aria-label="Status" name="status" required class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                            <option value="present">Present (Full Day)</option>
                            <option value="late">Late Arrival</option>
                            <option value="half_day">Half Day</option>
                            <option value="on_leave">Approved Leave</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Daily Hours</label>
                        <input aria-label="Daily Hours" type="number" step="0.25" name="daily_hours" value="8.0" placeholder="e.g. 8.0" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="skip_sundays" value="1" checked class="rounded text-blue-600 focus:ring-blue-500">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Skip Sundays (Leave weekends untracked)</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('bulkAttendanceModal').classList.add('hidden')" class="px-5 py-2.5 min-h-[44px] rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 min-h-[44px] rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md shadow-blue-500/25">Apply Range Fill</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('modalTitle').innerText = 'Record Employee Attendance';
            document.getElementById('modalDate').value = '{{ $selectedDate }}';
            document.getElementById('modalStatus').value = 'present';
            document.getElementById('modalClockIn').value = '09:00';
            document.getElementById('modalClockOut').value = '18:00';
            document.getElementById('modalTotalHours').value = '8.0';
            document.getElementById('modalOvertimeHours').value = '0';
            document.getElementById('modalLocation').value = 'office';
            document.getElementById('modalNotes').value = '';
            document.getElementById('manualAttendanceModal').classList.remove('hidden');
        }

        function openEditModal(emp, att, date) {
            document.getElementById('modalUserId').value = emp.id;
            document.getElementById('modalDate').value = date;
            document.getElementById('modalTitle').innerText = 'Edit Attendance: ' + emp.name + ' (' + date + ')';

            if (att) {
                document.getElementById('modalStatus').value = att.status || 'present';
                document.getElementById('modalClockIn').value = att.clock_in ? att.clock_in.substring(0, 5) : '';
                document.getElementById('modalClockOut').value = att.clock_out ? att.clock_out.substring(0, 5) : '';
                document.getElementById('modalTotalHours').value = att.total_hours || '';
                document.getElementById('modalOvertimeHours').value = att.overtime_hours || '';
                document.getElementById('modalLocation').value = att.location_type || 'office';
                document.getElementById('modalNotes').value = att.notes || '';
            } else {
                document.getElementById('modalStatus').value = 'present';
                document.getElementById('modalClockIn').value = '09:00';
                document.getElementById('modalClockOut').value = '18:00';
                document.getElementById('modalTotalHours').value = '8.0';
                document.getElementById('modalOvertimeHours').value = '0';
                document.getElementById('modalLocation').value = 'office';
                document.getElementById('modalNotes').value = '';
            }

            document.getElementById('manualAttendanceModal').classList.remove('hidden');
        }

        function openQuickWeekFillModal(emp, startDate, endDate) {
            document.getElementById('bulkUserId').value = emp.id;
            document.getElementById('bulkStartDate').value = startDate;
            document.getElementById('bulkEndDate').value = endDate;
            document.getElementById('bulkAttendanceModal').classList.remove('hidden');
        }

        function adjustModalHoursByStatus(status) {
            const inInput = document.getElementById('modalClockIn');
            const outInput = document.getElementById('modalClockOut');
            const hrsInput = document.getElementById('modalTotalHours');
            const otInput = document.getElementById('modalOvertimeHours');

            if (status === 'present') {
                inInput.value = '09:00';
                outInput.value = '18:00';
                hrsInput.value = '8.0';
                otInput.value = '0';
            } else if (status === 'late') {
                inInput.value = '10:00';
                outInput.value = '18:00';
                hrsInput.value = '8.0';
                otInput.value = '0';
            } else if (status === 'half_day') {
                inInput.value = '09:00';
                outInput.value = '13:00';
                hrsInput.value = '4.0';
                otInput.value = '0';
            } else if (status === 'absent' || status === 'on_leave') {
                inInput.value = '';
                outInput.value = '';
                hrsInput.value = '0';
                otInput.value = '0';
            }
        }
    </script>
</x-app-layout>
