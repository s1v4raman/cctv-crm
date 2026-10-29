<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-slate-900 dark:text-white">Employees</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $totalEmployees }} {{ Str::plural('person', $totalEmployees) }} · {{ $presentCount }} present today</p>
            </div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.users.create', ['return_to' => route('employee.hub')]) }}" class="btn-primary">Add employee</a>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6 pb-12">
        <x-employee-subnav />

        {{-- Headline figures: who works here, what is waiting on someone, and pay (admin only) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="p-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="text-xs text-slate-500 dark:text-slate-400">People</div>
                <div class="text-2xl font-semibold text-slate-900 dark:text-white mt-1">{{ $totalEmployees }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $technicianCount }} {{ Str::plural('technician', $technicianCount) }} · {{ $staffCount }} staff · {{ $adminCount }} admin</div>
            </div>

            <div class="p-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="text-xs text-slate-500 dark:text-slate-400">Waiting for approval</div>
                <div class="text-2xl font-semibold text-slate-900 dark:text-white mt-1">{{ $pendingLeavesCount + $pendingExpensesCount }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    <a href="{{ route('leaves.index') }}" class="hover:underline">{{ $pendingLeavesCount }} leave {{ Str::plural('request', $pendingLeavesCount) }}</a> ·
                    <a href="{{ route('finance.expenses.index') }}" class="hover:underline">{{ $pendingExpensesCount }} expense {{ Str::plural('claim', $pendingExpensesCount) }}</a>
                </div>
            </div>

            @if($canSeePay)
            <div class="p-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="text-xs text-slate-500 dark:text-slate-400">Monthly salaries</div>
                <div class="text-2xl font-semibold text-slate-900 dark:text-white mt-1">{{ \App\Support\Inr::format($monthlySalaryBurn) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">about {{ \App\Support\Inr::format($monthlySalaryBurn / 26) }} a working day</div>
            </div>

            <div class="p-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="text-xs text-slate-500 dark:text-slate-400">Payroll for {{ now()->format('F') }}</div>
                <div class="text-2xl font-semibold mt-1 {{ $isPayrollGenerated ? 'text-slate-900 dark:text-white' : 'text-amber-700 dark:text-amber-400' }}">{{ $isPayrollGenerated ? 'Ready' : 'Not run yet' }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    @if($isPayrollGenerated)
                        {{ $payrollPaidCount }} paid · {{ \App\Support\Inr::format($payrollTotalAmount) }} net
                    @else
                        <a href="{{ route('finance.payroll.index') }}" class="hover:underline">Run payroll</a>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Today and the last week --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="p-5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-baseline justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Today</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ now()->format('l, j F') }}</p>
                    </div>
                    <a href="{{ route('attendance.index') }}" class="text-xs action-link">Mark attendance</a>
                </div>

                <dl class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    @foreach([
                        ['Present',    $presentCount,  'bg-emerald-500'],
                        ['Late',       $lateCount,     'bg-amber-400'],
                        ['On leave',   $leaveCount,    'bg-blue-400'],
                        ['Absent',     $absentCount,   'bg-rose-500'],
                        ['Not marked', $unmarkedCount, 'bg-slate-300 dark:bg-slate-600'],
                    ] as [$label, $count, $dot])
                    <div class="flex items-center justify-between py-2">
                        <dt class="flex items-center gap-2 text-slate-600 dark:text-slate-300"><span class="w-2 h-2 rounded-full {{ $dot }}"></span>{{ $label }}</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $count }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>

            <div class="lg:col-span-2 p-5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-baseline justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Last 7 days</h3>
                    <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Present</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-400"></span>Absent</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-400"></span>On leave</span>
                    </div>
                </div>
                @if(collect($attendanceTrend)->sum(fn ($d) => $d['present'] + $d['absent'] + $d['on_leave']) === 0)
                    <p class="h-36 flex items-center justify-center text-sm text-slate-500 dark:text-slate-400">No attendance marked in the last 7 days.</p>
                @else
                <div class="flex items-end gap-2 h-36">
                    @php $base = max(1, $totalEmployees); @endphp
                    @foreach($attendanceTrend as $day)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full flex flex-col-reverse gap-px rounded-t overflow-hidden" style="height:120px" title="{{ $day['date'] }}: {{ $day['present'] }} present, {{ $day['absent'] }} absent, {{ $day['on_leave'] }} on leave">
                            <div class="bg-emerald-500 w-full" style="height:{{ round($day['present'] / $base * 120) }}px"></div>
                            <div class="bg-rose-400 w-full" style="height:{{ round($day['absent'] / $base * 120) }}px"></div>
                            <div class="bg-blue-400 w-full" style="height:{{ round($day['on_leave'] / $base * 120) }}px"></div>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $day['date'] }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        @if($canSeePay && $monthlySalaryBurn <= 0)
            <div class="rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                No salaries set yet, so there is nothing to show for pay. <a href="{{ route('finance.salaries.index') }}" class="action-link">Set salaries</a>
            </div>
        @elseif($canSeePay)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="p-5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-baseline justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Salaries by role</h3>
                    <a href="{{ route('finance.salaries.index') }}" class="text-xs action-link">Edit salaries</a>
                </div>
                <dl class="space-y-3 text-sm">
                    @foreach([
                        ['Admin',      $salaryByRole['admin'],      $adminCount],
                        ['Staff',      $salaryByRole['staff'],      $staffCount],
                        ['Technician', $salaryByRole['technician'], $technicianCount],
                    ] as [$roleLabel, $roleAmt, $roleCount])
                    @php $rolePct = $monthlySalaryBurn > 0 ? round($roleAmt / $monthlySalaryBurn * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between">
                            <dt class="text-slate-600 dark:text-slate-300">{{ $roleLabel }} <span class="text-slate-400">({{ $roleCount }})</span></dt>
                            <dd class="font-medium text-slate-900 dark:text-white">{{ \App\Support\Inr::format($roleAmt) }} <span class="text-xs font-normal text-slate-400">· {{ $rolePct }}%</span></dd>
                        </div>
                        <div class="mt-1 h-1 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="bg-slate-400 dark:bg-slate-500 h-full" style="width:{{ $rolePct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                    <div class="flex justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                        <dt class="text-slate-600 dark:text-slate-300">Total a month</dt>
                        <dd class="font-semibold text-slate-900 dark:text-white">{{ \App\Support\Inr::format($monthlySalaryBurn) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="lg:col-span-2 p-5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-baseline justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Payroll, last 6 months</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Net pay each month</p>
                    </div>
                    <a href="{{ route('finance.payroll.index') }}" class="text-xs action-link">Open payroll</a>
                </div>
                @php $maxPayroll = max(1, $payrollHistory->max('total')); @endphp
                <div class="flex items-end gap-3 h-36">
                    @foreach($payrollHistory as $ph)
                    @php $barH = $ph['total'] > 0 ? max(4, round($ph['total'] / $maxPayroll * 100)) : 2; @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $ph['total'] > 0 ? \App\Support\Inr::format($ph['total']) : '' }}</span>
                        <div class="w-full rounded-t {{ $ph['total'] > 0 ? 'bg-blue-500' : 'bg-slate-200 dark:bg-slate-700' }}" style="height:{{ $barH }}px"></div>
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $ph['month'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ═══════════════════ ROW 4: EMPLOYEE ROSTER TABLE ═══════════════════ --}}
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-5 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Employees today</h3>
                    <p class="text-[11px] text-slate-400">{{ $totalEmployees }} employees · attendance as of {{ now()->format('d M Y') }}</p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('attendance.index') }}" class="text-[11px] font-bold text-emerald-600 hover:underline">Mark attendance</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr>
                            <th class="text-left px-5 py-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">#</th>
                            <th class="text-left px-4 py-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Employee</th>
                            <th class="text-left px-4 py-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Role</th>
                            <th class="text-left px-4 py-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Today</th>
                            <th class="text-left px-4 py-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Clock In</th>
                            <th class="text-left px-4 py-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Clock Out</th>
                            @if($canSeePay)
                            <th class="text-right px-4 py-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Salary/mo</th>
                            @endif
                            @if($canSeePay)
                            <th class="text-right px-5 py-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Actions</th>
                            @endif
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
                            <td class="px-5 py-3 text-slate-400 font-mono text-[11px]">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white font-bold text-[11px] flex-shrink-0">
                                        {{ strtoupper(substr($emp['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 dark:text-white text-xs">{{ $emp['name'] }}</div>
                                        <div class="text-slate-400 text-[11px]">{{ $emp['email'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold uppercase {{ $roleColor }}">
                                    {{ ucfirst($emp['role']) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $statusStyles[0] }}">
                                    {{ $statusStyles[1] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300 font-mono text-[11px]">{{ $emp['clock_in'] }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300 font-mono text-[11px]">{{ $emp['clock_out'] }}</td>
                            @if($canSeePay)
                            <td class="px-4 py-3 text-right">
                                @if($emp['salary'] > 0)
                                    <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($emp['salary'], 0) }}</span>
                                @else
                                    <span class="text-slate-400 italic">—</span>
                                @endif
                            </td>
                            @endif
                            @if($canSeePay)
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- View Profile --}}
                                    <a aria-label="View Employee Profile" href="{{ route('admin.users.show', $emp['id']) }}" 
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-800 transition" 
                                       title="View Employee Profile">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    
                                    {{-- Edit Account --}}
                                    <a aria-label="Edit Employee" href="{{ route('admin.users.edit', ['user' => $emp['id'], 'return_to' => route('employee.hub')]) }}" 
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-slate-800 transition" 
                                       title="Edit Employee">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    {{-- Delete Account (blocked for self) --}}
                                    @if($emp['id'] !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', ['user' => $emp['id'], 'return_to' => route('employee.hub')]) }}" 
                                              data-confirm="Are you sure you want to delete employee {{ $emp['name'] }}? This action is permanent." 
                                              class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button aria-label="Delete Employee" type="submit" 
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 transition cursor-pointer" 
                                                    title="Delete Employee">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══════════════════ ROW 5: RECENT LEAVES + QUICK ACTIONS ═══════════════════ --}}
        <div>

            {{-- Recent Leave Requests --}}
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Recent Leave Requests</h3>
                        <p class="text-[11px] text-slate-400">{{ $pendingLeavesCount }} pending approval</p>
                    </div>
                    <a href="{{ route('leaves.index') }}" class="text-[11px] font-bold text-amber-600 hover:underline">View All →</a>
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
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-[11px] flex-shrink-0">
                                {{ strtoupper(substr($leave->user?->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-slate-900 dark:text-white">{{ $leave->user?->name ?? 'Unknown' }}</div>
                                <div class="text-[11px] text-slate-400">
                                    {{ ucfirst(str_replace('_', ' ', $leave->leave_type ?? 'Leave')) }}
                                    · {{ \Carbon\Carbon::parse($leave->start_date ?? $leave->created_at)->format('d M') }}
                                    @if(!empty($leave->end_date)) – {{ \Carbon\Carbon::parse($leave->end_date)->format('d M') }} @endif
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $leaveStatus[0] }}">{{ $leaveStatus[1] }}</span>
                    </div>
                    @empty
                    <div class="flex flex-col items-center justify-center py-10 text-slate-400">
                        <svg class="w-8 h-8 mb-2 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span class="text-xs">No leave requests found</span>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
