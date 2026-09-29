<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_10px_#10b981]"></span>
                    Payroll Processing &amp; Disbursal Hub
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">Generate, approve, and disburse attendance-synchronized monthly, weekly, and daily payrolls</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.analytics') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 text-xs font-extrabold shadow-md shadow-amber-500/20 transition">
                    📈 Salary Analytics
                </a>
                <button type="button" onclick="openGenerateModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition">
                    ⚡ Generate Payroll
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        .pg-wrap { background:#f8fafc; min-height:100vh; padding:1.5rem 0 3rem; }
        .dark .pg-wrap { background:#060913; }
        .pg-inner { max-width:1380px; margin:0 auto; padding:0 1.25rem; }
        .pg-card {
            background:#ffffff; border-radius:1rem; border:1px solid #e2e8f0;
            box-shadow:0 1px 3px rgba(0,0,0,.04),0 4px 12px rgba(0,0,0,.02);
        }
        .dark .pg-card { background:#0f172a; border-color:#1e293b; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner space-y-6">

            @if(session('status'))
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center justify-between">
                    <span>{{ session('status') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-xs font-bold">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Employee Category Sub-Navigation --}}
            <x-employee-subnav active="payroll" />

            {{-- Summary Cards for Selected Month --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="pg-card p-4 border-l-4 border-l-blue-600">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Net Payroll Value</span>
                    <span class="text-2xl font-black text-slate-900 dark:text-white mt-1 block font-mono">
                        ₹{{ number_format($totalNetPayroll, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 block">Month of {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</span>
                </div>
                <div class="pg-card p-4 border-l-4 border-l-emerald-600">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Disbursed (Paid)</span>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block font-mono">
                        ₹{{ number_format($totalPaidPayroll, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 block">Successfully settled</span>
                </div>
                <div class="pg-card p-4 border-l-4 border-l-amber-600">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pending / Draft</span>
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 block font-mono">
                        ₹{{ number_format($totalPendingPayroll, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 block">Awaiting approval or payout</span>
                </div>
                <div class="pg-card p-4 border-l-4 border-l-purple-600">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Overtime Disbursed</span>
                    <span class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1 block font-mono">
                        ₹{{ number_format($totalOvertimePaid, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 block">Based on logged attendance</span>
                </div>
                <div class="pg-card p-4 border-l-4 border-l-teal-600">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Allowances</span>
                    <span class="text-2xl font-black text-teal-600 dark:text-teal-400 mt-1 block font-mono">
                        ₹{{ number_format($totalAllowancesPaid, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 block">Travel + special incentives</span>
                </div>
            </div>

            {{-- Filter & Generation Bar --}}
            <div class="pg-card p-4 flex flex-wrap items-center justify-between gap-4">
                <form method="GET" action="{{ route('finance.payroll.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Select Month</label>
                        <input type="month" 
                               name="month" 
                               value="{{ $selectedMonth }}" 
                               onchange="this.form.submit()"
                               class="text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3.5 py-1.5 text-slate-800 dark:text-slate-200 font-mono">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Payment Status</label>
                        <select name="status" onchange="this.form.submit()" 
                                class="text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-slate-800 dark:text-slate-200">
                            <option value="">All Statuses</option>
                            <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="paid" {{ $statusFilter === 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Employee Filter</label>
                        <select name="user_id" onchange="this.form.submit()" 
                                class="text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-slate-800 dark:text-slate-200">
                            <option value="">All Employees</option>
                            @foreach($internalUsers as $u)
                                <option value="{{ $u->id }}" {{ $employeeId == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                            @endforeach
                        </select>
                    </div>

                    @if($statusFilter || $employeeId || $selectedMonth !== now()->format('Y-m'))
                        <div class="pt-4">
                            <a href="{{ route('finance.payroll.index') }}" 
                               class="text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 underline font-medium">
                                Reset Filters
                            </a>
                        </div>
                    @endif
                </form>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="openGenerateModal()"
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition flex items-center gap-1.5">
                        <span>⚡ Run Payroll Batch</span>
                    </button>
                </div>
            </div>

            {{-- Payroll Table --}}
            <div class="pg-card overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Generated Salary Slips</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Records are calculated with attendance records, overtime hours, and salary structures</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                        {{ $payrolls->total() }} Records
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-400 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="py-3 px-4">Payroll #</th>
                                <th class="py-3 px-4">Employee</th>
                                <th class="py-3 px-4">Period</th>
                                <th class="py-3 px-4 text-center">Attendance / Days</th>
                                <th class="py-3 px-4 text-right">Basic Pay</th>
                                <th class="py-3 px-4 text-right">OT Pay</th>
                                <th class="py-3 px-4 text-right">Allowances</th>
                                <th class="py-3 px-4 text-right">Deductions</th>
                                <th class="py-3 px-4 text-right font-black">Net Salary</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($payrolls as $p)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                        {{ $p->payroll_number }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                                {{ substr($p->user?->name ?? 'U', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 dark:text-white">{{ $p->user?->name ?? 'Unknown' }}</div>
                                                <div class="text-[10px] text-slate-400">{{ ucfirst($p->user?->role ?? '') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-medium text-slate-800 dark:text-slate-200">
                                            {{ \Carbon\Carbon::parse($p->period_start)->format('d M') }} - {{ \Carbon\Carbon::parse($p->period_end)->format('d M Y') }}
                                        </div>
                                        <span class="inline-block mt-0.5 text-[9px] px-1.5 py-0.5 rounded font-bold uppercase
                                            @if($p->period_type === 'weekly') bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300
                                            @elseif($p->period_type === 'daily') bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300
                                            @else bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 @endif">
                                            {{ $p->period_type }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="font-semibold text-slate-800 dark:text-slate-200">
                                            <span class="text-emerald-600 dark:text-emerald-400">{{ $p->present_days }}P</span>
                                            @if($p->half_days > 0)
                                                <span class="text-amber-600 dark:text-amber-400 ml-1">{{ $p->half_days }}H</span>
                                            @endif
                                            @if($p->leave_days > 0)
                                                <span class="text-blue-600 dark:text-blue-400 ml-1">{{ $p->leave_days }}L</span>
                                            @endif
                                            @if($p->absent_days > 0)
                                                <span class="text-red-600 dark:text-red-400 ml-1">{{ $p->absent_days }}A</span>
                                            @endif
                                        </div>
                                        @if($p->overtime_hours > 0)
                                            <div class="text-[10px] text-purple-600 dark:text-purple-400 font-bold mt-0.5">
                                                +{{ $p->overtime_hours }}h OT
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-slate-700 dark:text-slate-300">
                                        ₹{{ number_format($p->basic_pay, 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-purple-600 dark:text-purple-400">
                                        ₹{{ number_format($p->overtime_pay, 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-emerald-600 dark:text-emerald-400">
                                        +₹{{ number_format($p->allowances, 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-red-600 dark:text-red-400">
                                        -₹{{ number_format($p->deductions, 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-black text-slate-900 dark:text-white text-sm">
                                        ₹{{ number_format($p->net_salary, 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                            @if($p->status === 'paid') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                                            @elseif($p->status === 'approved') bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300
                                            @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif">
                                            {{ ucfirst($p->status) }}
                                        </span>
                                        @if($p->payment_date)
                                            <span class="block text-[9px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($p->payment_date)->format('d M') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('finance.payroll.show', $p->id) }}"
                                               class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition text-xs font-bold"
                                               title="View & Print Official Payslip">
                                                🖨️
                                            </a>

                                            <a href="{{ route('finance.payroll.pdf', $p->id) }}"
                                               class="p-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition text-xs font-bold"
                                               title="Download PDF Payslip">
                                                📄 PDF
                                            </a>

                                            <a href="{{ route('finance.payroll.sendWhatsApp', $p->id) }}"
                                               target="_blank"
                                               class="p-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition text-xs font-bold flex items-center gap-1"
                                               title="Send via WhatsApp to {{ $p->user?->name }}">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.983.54 1.776.818 2.796.818 3.182 0 5.768-2.587 5.768-5.769.001-3.181-2.585-5.767-5.768-5.767zm9.969 5.766c0 5.495-4.474 9.969-9.969 9.969-1.748 0-3.385-.453-4.819-1.246l-5.212 1.367 1.391-5.084c-.887-1.493-1.391-3.238-1.391-5.006 0-5.495 4.474-9.969 9.969-9.969 5.495 0 9.969 4.474 9.969 9.969z"/>
                                                </svg>
                                            </a>

                                            {{-- Status Quick Modal Trigger --}}
                                            <button type="button" 
                                                    onclick="openStatusModal({{ $p->id }}, '{{ $p->payroll_number }}', '{{ $p->status }}', '{{ $p->payment_reference ?? '' }}', '{{ $p->notes ?? '' }}')"
                                                    class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-300 transition text-xs font-bold"
                                                    title="Update Status">
                                                ⚙️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="py-12 text-center text-slate-400">
                                        <div class="text-3xl mb-2">🧾</div>
                                        <p class="text-sm font-semibold">No payroll slips generated for this period.</p>
                                        <p class="text-xs text-slate-500 mt-1">Click "Run Payroll Batch" above to calculate employee salaries from attendance.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($payrolls->hasPages())
                    <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                        {{ $payrolls->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Generate Payroll Modal --}}
    <div id="generateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 w-full max-w-lg p-6 shadow-2xl mx-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                    <span>⚡</span> Attendance-Synced Payroll Generator
                </h3>
                <button type="button" onclick="closeGenerateModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('finance.payroll.generate') }}" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payroll Cycle Type</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold">
                            <input type="radio" name="period_type" value="monthly" checked onchange="adjustDateDefaults('monthly')">
                            <span>Monthly</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold">
                            <input type="radio" name="period_type" value="weekly" onchange="adjustDateDefaults('weekly')">
                            <span>Weekly</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold">
                            <input type="radio" name="period_type" value="daily" onchange="adjustDateDefaults('daily')">
                            <span>Per-Day</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Period Start</label>
                        <input type="date" id="gen_period_start" name="period_start" required
                               value="{{ \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString() }}"
                               class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Period End</label>
                        <input type="date" id="gen_period_end" name="period_end" required
                               value="{{ \Carbon\Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString() }}"
                               class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Target Employees</label>
                    <select name="user_id" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                        <option value="">All Salaried Employees (Internal)</option>
                        @foreach($internalUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Leave empty to compute salaries for all internal staff with configured salary master.</p>
                </div>

                <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900 text-blue-800 dark:text-blue-300 text-xs">
                    ℹ️ <strong>System Automation:</strong> The engine automatically tallies present days, half days, paid leaves, and overtime hours logged in the Attendance module to calculate exact basic pay, overtime incentives, and final net earnings.
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeGenerateModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition">
                        Run Batch Calculation
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Update Status Modal --}}
    <div id="statusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 w-full max-w-md p-6 shadow-2xl mx-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-base text-slate-900 dark:text-white">
                    Update Payroll <span id="statusModalNumber" class="font-mono text-blue-600"></span>
                </h3>
                <button type="button" onclick="closeStatusModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold">✕</button>
            </div>

            <form id="statusForm" method="POST" action="" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select id="modal_status" name="status" required class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                        <option value="draft">Draft</option>
                        <option value="approved">Approved (Ready for disbursal)</option>
                        <option value="paid">Paid (Disbursed)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Date (if paid)</label>
                    <input type="date" id="modal_payment_date" name="payment_date" value="{{ now()->toDateString() }}"
                           class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Reference / UTR Number</label>
                    <input type="text" id="modal_payment_reference" name="payment_reference" placeholder="e.g. UTR-982736192 or Cheque #004"
                           class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Notes</label>
                    <textarea id="modal_notes" name="notes" rows="2" placeholder="Optional audit notes..."
                              class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition">
                        Save Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openGenerateModal() {
            document.getElementById('generateModal').classList.remove('hidden');
        }
        function closeGenerateModal() {
            document.getElementById('generateModal').classList.add('hidden');
        }

        function adjustDateDefaults(cycle) {
            const today = new Date();
            const startInput = document.getElementById('gen_period_start');
            const endInput = document.getElementById('gen_period_end');

            if (cycle === 'daily') {
                const d = today.toISOString().split('T')[0];
                startInput.value = d;
                endInput.value = d;
            } else if (cycle === 'weekly') {
                const curr = new Date();
                const first = curr.getDate() - curr.getDay() + 1; // Monday
                const last = first + 5; // Saturday
                const monday = new Date(curr.setDate(first)).toISOString().split('T')[0];
                const saturday = new Date(curr.setDate(last)).toISOString().split('T')[0];
                startInput.value = monday;
                endInput.value = saturday;
            } else {
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const lastDay = new Date(year, today.getMonth() + 1, 0).getDate();
                startInput.value = `${year}-${month}-01`;
                endInput.value = `${year}-${month}-${lastDay}`;
            }
        }

        function openStatusModal(id, number, status, ref, notes) {
            document.getElementById('statusModalNumber').innerText = '#' + number;
            document.getElementById('modal_status').value = status;
            document.getElementById('modal_payment_reference').value = ref;
            document.getElementById('modal_notes').value = notes;
            document.getElementById('statusForm').action = `{{ url('/finance/payroll') }}/${id}/status`;
            document.getElementById('statusModal').classList.remove('hidden');
        }

        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
