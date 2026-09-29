<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/employee-workspace-icon.svg') }}" alt="Employee & Workspace" class="w-12 h-12 rounded-2xl shadow-lg shadow-emerald-500/25 object-contain">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                            Workforce Analytics Hub
                        </span>
                        <span class="text-xs text-slate-400 font-medium">• {{ $totalEmployees }} Active Staff</span>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Employee & Workforce Analytics</h1>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('attendance.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition shadow-sm hover:opacity-90" style="background-color: #059669 !important; color: #ffffff !important; border: 1px solid #047857 !important;">
                    <svg class="w-3.5 h-3.5" style="color: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span style="color: #ffffff !important; font-weight: 700 !important;">Attendance</span>
                </a>
                <a href="{{ route('finance.salaries.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition shadow-sm hover:opacity-90" style="background-color: #0d9488 !important; color: #ffffff !important; border: 1px solid #0f766e !important;">
                    <svg class="w-3.5 h-3.5" style="color: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span style="color: #ffffff !important; font-weight: 700 !important;">Salaries</span>
                </a>
                <a href="{{ route('finance.payroll.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition shadow-sm hover:opacity-90" style="background-color: #4f46e5 !important; color: #ffffff !important; border: 1px solid #4338ca !important;">
                    <svg class="w-3.5 h-3.5" style="color: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    <span style="color: #ffffff !important; font-weight: 700 !important;">Payroll</span>
                </a>
                <a href="{{ route('finance.hub') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-sm hover:opacity-90" style="background-color: #2563eb !important; color: #ffffff !important; border: 1px solid #1d4ed8 !important;">
                    <svg class="w-3.5 h-3.5" style="color: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span style="color: #ffffff !important; font-weight: 700 !important;">Finance Hub</span>
                    <svg class="w-3.5 h-3.5" style="color: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6 pb-12">
        <x-employee-subnav />

        {{-- ═══════════════════ ROW 1: KPI STATS ═══════════════════ --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            {{-- Total Workforce --}}
            <div class="col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Staff</span>
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $totalEmployees }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $technicianCount }} Techs · {{ $staffCount }} Staff · {{ $adminCount }} Admin</div>
            </div>

            {{-- Present Today --}}
            <div class="col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Present</span>
                    <div class="w-7 h-7 rounded-lg bg-teal-50 dark:bg-teal-950/50 text-teal-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $presentCount }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $lateCount }} late · {{ $unmarkedCount }} unmarked</div>
            </div>

            {{-- Absent --}}
            <div class="col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Absent</span>
                    <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ $absentCount }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $leaveCount }} on leave today</div>
            </div>

            {{-- Salary Burn --}}
            <div class="col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Monthly Burn</span>
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-xl font-black text-slate-900 dark:text-white">₹{{ number_format($monthlySalaryBurn / 1000, 0) }}K</div>
                <div class="text-[10px] text-slate-400 mt-0.5">₹{{ number_format($monthlySalaryBurn / 26, 0) }}/day burn rate</div>
            </div>

            {{-- Pending Actions --}}
            <div class="col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pending</span>
                    <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $pendingLeavesCount + $pendingExpensesCount }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $pendingLeavesCount }} leaves · {{ $pendingExpensesCount }} expenses</div>
            </div>

            {{-- Payroll Status --}}
            <div class="col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Payroll</span>
                    <div class="w-7 h-7 rounded-lg {{ $isPayrollGenerated ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-600' }} flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    </div>
                </div>
                <div class="text-sm font-black {{ $isPayrollGenerated ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $isPayrollGenerated ? 'Generated' : 'Pending Run' }}
                </div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $payrollPaidCount }} paid · ₹{{ number_format($payrollTotalAmount, 0) }}</div>
            </div>
        </div>

        {{-- ═══════════════════ ROW 2: CHARTS + ATTENDANCE TREND ═══════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Attendance Breakdown Donut --}}
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Today's Attendance</h3>
                        <p class="text-[11px] text-slate-400">{{ now()->format('D, d M Y') }}</p>
                    </div>
                    <a href="{{ route('attendance.index') }}" class="text-[10px] font-bold text-emerald-600 hover:underline">View Full →</a>
                </div>

                {{-- Visual bar breakdown --}}
                @php
                    $total = max(1, $totalEmployees);
                    $pPct  = round($presentCount / $total * 100);
                    $lPct  = round($lateCount / $total * 100);
                    $aPct  = round($absentCount / $total * 100);
                    $lvPct = round($leaveCount / $total * 100);
                    $uPct  = round($unmarkedCount / $total * 100);
                @endphp
                <div class="flex rounded-full overflow-hidden h-4 mb-4 gap-px">
                    @if($presentCount)  <div class="bg-emerald-500 transition-all" style="width:{{ $pPct }}%" title="Present: {{ $presentCount }}"></div> @endif
                    @if($lateCount)     <div class="bg-amber-400 transition-all"   style="width:{{ $lPct }}%"  title="Late: {{ $lateCount }}"></div> @endif
                    @if($leaveCount)    <div class="bg-purple-500 transition-all"  style="width:{{ $lvPct }}%" title="On Leave: {{ $leaveCount }}"></div> @endif
                    @if($absentCount)   <div class="bg-rose-500 transition-all"    style="width:{{ $aPct }}%"  title="Absent: {{ $absentCount }}"></div> @endif
                    @if($unmarkedCount) <div class="bg-slate-300 dark:bg-slate-700 transition-all flex-1"      title="Unmarked: {{ $unmarkedCount }}"></div> @endif
                </div>

                <div class="space-y-2">
                    @foreach([
                        ['Present',   $presentCount,   'bg-emerald-500', 'text-emerald-600'],
                        ['Late',      $lateCount,      'bg-amber-400',   'text-amber-600'],
                        ['On Leave',  $leaveCount,     'bg-purple-500',  'text-purple-600'],
                        ['Absent',    $absentCount,    'bg-rose-500',    'text-rose-600'],
                        ['Unmarked',  $unmarkedCount,  'bg-slate-300',   'text-slate-500'],
                    ] as [$label, $count, $dot, $text])
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $dot }}"></span>
                            <span class="text-xs text-slate-600 dark:text-slate-300">{{ $label }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold {{ $text }}">{{ $count }}</span>
                            <span class="text-[10px] text-slate-400 w-7 text-right">{{ $total > 0 ? round($count / $total * 100) : 0 }}%</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- 7-Day Attendance Trend Chart --}}
            <div class="lg:col-span-2 p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">7-Day Attendance Trend</h3>
                        <p class="text-[11px] text-slate-400">Daily present vs absent vs leave</p>
                    </div>
                    <div class="flex items-center gap-3 text-[10px] font-semibold">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Present</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500"></span>Absent</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-purple-500"></span>Leave</span>
                    </div>
                </div>
                <div class="flex items-end gap-2 h-36">
                    @foreach($attendanceTrend as $day)
                    @php $dayTotal = max(1, $day['present'] + $day['absent'] + $day['on_leave'] + $day['unmarked']); @endphp
                    <div class="flex-1 flex flex-col items-center gap-1 group">
                        <div class="w-full flex flex-col-reverse gap-px rounded-t overflow-hidden" style="height:120px">
                            <div class="bg-emerald-500/80 w-full rounded-t transition-all" style="height:{{ $dayTotal > 0 ? round($day['present'] / $totalEmployees * 120) : 0 }}px" title="Present: {{ $day['present'] }}"></div>
                            <div class="bg-rose-400/70 w-full"    style="height:{{ $dayTotal > 0 ? round($day['absent'] / $totalEmployees * 120) : 0 }}px" title="Absent: {{ $day['absent'] }}"></div>
                            <div class="bg-purple-400/70 w-full"  style="height:{{ $dayTotal > 0 ? round($day['on_leave'] / $totalEmployees * 120) : 0 }}px" title="Leave: {{ $day['on_leave'] }}"></div>
                        </div>
                        <span class="text-[9px] text-slate-400 font-medium text-center">{{ $day['date'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ═══════════════════ ROW 3: SALARY ANALYTICS ═══════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Salary By Role --}}
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Salary by Role</h3>
                        <p class="text-[11px] text-slate-400">Monthly commitment breakdown</p>
                    </div>
                    <span class="text-xs font-black text-indigo-600">₹{{ number_format($monthlySalaryBurn / 1000, 1) }}K</span>
                </div>
                @foreach([
                    ['Admin',      $salaryByRole['admin'],      'bg-indigo-500',  'text-indigo-600',  $adminCount],
                    ['Staff',      $salaryByRole['staff'],      'bg-teal-500',    'text-teal-600',    $staffCount],
                    ['Technician', $salaryByRole['technician'], 'bg-emerald-500', 'text-emerald-600', $technicianCount],
                ] as [$roleLabel, $roleAmt, $barColor, $textColor, $roleCount])
                @php $rolePct = $monthlySalaryBurn > 0 ? round($roleAmt / $monthlySalaryBurn * 100) : 0; @endphp
                <div class="mb-3">
                    <div class="flex justify-between items-center mb-1">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $barColor }}"></span>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $roleLabel }}</span>
                            <span class="text-[10px] text-slate-400">({{ $roleCount }})</span>
                        </div>
                        <span class="text-xs font-bold {{ $textColor }}">₹{{ number_format($roleAmt, 0) }}</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="{{ $barColor }} h-full rounded-full transition-all duration-700" style="width:{{ $rolePct }}%"></div>
                    </div>
                    <div class="text-right text-[10px] text-slate-400 mt-0.5">{{ $rolePct }}% of total</div>
                </div>
                @endforeach
            </div>

            {{-- Payroll 6-Month History --}}
            <div class="lg:col-span-2 p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Payroll History (6 Months)</h3>
                        <p class="text-[11px] text-slate-400">Net salary disbursed per month</p>
                    </div>
                    <a href="{{ route('finance.payroll.index') }}" class="text-[10px] font-bold text-indigo-600 hover:underline">Manage Payroll →</a>
                </div>
                @php $maxPayroll = max(1, $payrollHistory->max('total')); @endphp
                <div class="flex items-end gap-3 h-32">
                    @foreach($payrollHistory as $ph)
                    @php $barH = $ph['total'] > 0 ? max(4, round($ph['total'] / $maxPayroll * 112)) : 4; @endphp
                    <div class="flex-1 flex flex-col items-center gap-1.5 group">
                        <span class="text-[9px] text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity font-mono">
                            ₹{{ number_format($ph['total'] / 1000, 0) }}K
                        </span>
                        <div class="w-full rounded-t-lg {{ $ph['total'] > 0 ? 'bg-indigo-500' : 'bg-slate-200 dark:bg-slate-700' }} transition-all"
                             style="height:{{ $barH }}px" title="₹{{ number_format($ph['total'], 0) }}"></div>
                        <span class="text-[9px] text-slate-400 font-medium text-center">{{ $ph['month'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ═══════════════════ ROW 4: EMPLOYEE ROSTER TABLE ═══════════════════ --}}
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Employee Directory & Today's Status</h3>
                    <p class="text-[11px] text-slate-400">{{ $totalEmployees }} employees · attendance as of {{ now()->format('d M Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('attendance.index') }}" class="text-[10px] font-bold text-emerald-600 hover:underline">Mark Attendance →</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr>
                            <th class="text-left px-5 py-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">#</th>
                            <th class="text-left px-4 py-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Employee</th>
                            <th class="text-left px-4 py-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Role</th>
                            <th class="text-left px-4 py-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Today</th>
                            <th class="text-left px-4 py-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Clock In</th>
                            <th class="text-left px-4 py-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Clock Out</th>
                            <th class="text-right px-5 py-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Salary/mo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($employeeRoster as $i => $emp)
                        @php
                            $statusStyles = match($emp['status']) {
                                'present'  => ['bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400', 'Present'],
                                'half_day' => ['bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-400', 'Half Day'],
                                'late'     => ['bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400', 'Late'],
                                'absent'   => ['bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400', 'Absent'],
                                'on_leave', 'leave' => ['bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-400', 'On Leave'],
                                default    => ['bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400', 'Unmarked'],
                            };
                            $roleColor = match($emp['role']) {
                                'admin'      => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300',
                                'staff'      => 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300',
                                'technician' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
                                default      => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-3 text-slate-400 font-mono text-[10px]">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0">
                                        {{ strtoupper(substr($emp['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 dark:text-white text-xs">{{ $emp['name'] }}</div>
                                        <div class="text-slate-400 text-[10px]">{{ $emp['email'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $roleColor }}">
                                    {{ ucfirst($emp['role']) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $statusStyles[0] }}">
                                    {{ $statusStyles[1] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300 font-mono text-[11px]">{{ $emp['clock_in'] }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300 font-mono text-[11px]">{{ $emp['clock_out'] }}</td>
                            <td class="px-5 py-3 text-right">
                                @if($emp['salary'] > 0)
                                    <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($emp['salary'], 0) }}</span>
                                @else
                                    <span class="text-slate-400 italic">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══════════════════ ROW 5: RECENT LEAVES + QUICK ACTIONS ═══════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Recent Leave Requests --}}
            <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Recent Leave Requests</h3>
                        <p class="text-[11px] text-slate-400">{{ $pendingLeavesCount }} pending approval</p>
                    </div>
                    <a href="{{ route('leaves.index') }}" class="text-[10px] font-bold text-amber-600 hover:underline">View All →</a>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentLeaves as $leave)
                    @php
                        $leaveStatus = match($leave->status) {
                            'approved'  => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400', 'Approved'],
                            'rejected'  => ['bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400', 'Rejected'],
                            default     => ['bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400', 'Pending'],
                        };
                    @endphp
                    <div class="flex items-center justify-between px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0">
                                {{ strtoupper(substr($leave->user?->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-slate-900 dark:text-white">{{ $leave->user?->name ?? 'Unknown' }}</div>
                                <div class="text-[10px] text-slate-400">
                                    {{ ucfirst(str_replace('_', ' ', $leave->leave_type ?? 'Leave')) }}
                                    · {{ \Carbon\Carbon::parse($leave->start_date ?? $leave->created_at)->format('d M') }}
                                    @if(!empty($leave->end_date)) – {{ \Carbon\Carbon::parse($leave->end_date)->format('d M') }} @endif
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $leaveStatus[0] }}">{{ $leaveStatus[1] }}</span>
                    </div>
                    @empty
                    <div class="flex flex-col items-center justify-center py-10 text-slate-400">
                        <svg class="w-8 h-8 mb-2 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span class="text-xs">No leave requests found</span>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Quick Action Panel --}}
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Quick Actions</h3>
                    <p class="text-[11px] text-slate-400">Workforce management shortcuts</p>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('attendance.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-950/30 group transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">Mark Attendance</div>
                            <div class="text-[10px] text-slate-400">Daily punch-in log</div>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('leaves.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-amber-50 dark:hover:bg-amber-950/30 group transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950/50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">Manage Leaves</div>
                            <div class="text-[10px] text-slate-400">{{ $pendingLeavesCount }} pending</div>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('finance.salaries.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-teal-50 dark:hover:bg-teal-950/30 group transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-teal-100 dark:bg-teal-950/50 text-teal-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">Salary Master</div>
                            <div class="text-[10px] text-slate-400">{{ $configuredSalariesCount }}/{{ $totalEmployees }} configured</div>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('finance.payroll.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/30 group transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">Run Payroll</div>
                            <div class="text-[10px] text-slate-400 {{ $isPayrollGenerated ? 'text-emerald-500' : 'text-amber-500' }}">
                                {{ $isPayrollGenerated ? '✓ Generated' : '⚠ Pending Run' }}
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('finance.expenses.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-pink-50 dark:hover:bg-pink-950/30 group transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-pink-100 dark:bg-pink-950/50 text-pink-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">Expense Claims</div>
                            <div class="text-[10px] text-slate-400">{{ $pendingExpensesCount }} pending · ₹{{ number_format($pendingExpensesAmount, 0) }}</div>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
