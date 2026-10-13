<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                    Payroll
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.analytics') }}" class="btn-secondary">
                    Analytics
                </a>
                <button type="button" onclick="openGenerateModal()" class="btn-primary">
                    Generate Payroll
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">

        @if(session('status'))
            <div class="p-3 rounded border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 text-xs font-medium flex items-center justify-between">
                <span>{{ session('status') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-3 rounded border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300 text-xs font-medium">
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
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Net Payroll Value</span>
                <span class="text-xl font-bold text-slate-900 dark:text-white mt-1 block">
                    &#8377;{{ number_format($totalNetPayroll, 2) }}
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">Month of {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</span>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Total Disbursed</span>
                <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 block">
                    &#8377;{{ number_format($totalPaidPayroll, 2) }}
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">Successfully settled</span>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Pending / Draft</span>
                <span class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1 block">
                    &#8377;{{ number_format($totalPendingPayroll, 2) }}
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">Awaiting approval or payout</span>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Overtime Disbursed</span>
                <span class="text-xl font-bold text-slate-900 dark:text-white mt-1 block">
                    &#8377;{{ number_format($totalOvertimePaid, 2) }}
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">Logged attendance</span>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Total Allowances</span>
                <span class="text-xl font-bold text-slate-900 dark:text-white mt-1 block">
                    &#8377;{{ number_format($totalAllowancesPaid, 2) }}
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">Travel &amp; incentives</span>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
            <form method="GET" action="{{ route('finance.payroll.index') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-300 block mb-1">Select Month</label>
                    <input type="month" 
                           name="month" 
                           value="{{ $selectedMonth }}" 
                           onchange="this.form.submit()"
                           class="text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-300 block mb-1">Payment Status</label>
                    <select name="status" onchange="this.form.submit()" 
                            class="text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ $statusFilter === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-300 block mb-1">Employee</label>
                    <select name="user_id" onchange="this.form.submit()" 
                            class="text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        <option value="">All Employees</option>
                        @foreach($internalUsers as $u)
                            <option value="{{ $u->id }}" {{ $employeeId == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                        @endforeach
                    </select>
                </div>

                @if($statusFilter || $employeeId || $selectedMonth !== now()->format('Y-m'))
                    <div>
                        <a href="{{ route('finance.payroll.index') }}" 
                           class="text-xs text-blue-600 hover:underline font-medium inline-block pb-2">
                            Reset Filters
                        </a>
                    </div>
                @endif
            </form>
        </div>

        {{-- Payroll Table --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-900 dark:text-white text-sm">Generated Salary Slips</h2>
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    {{ $payrolls->total() }} Records
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 text-[11px] font-bold">
                        <tr>
                            <th class="py-3 px-3">Payroll #</th>
                            <th class="py-3 px-3">Employee</th>
                            <th class="py-3 px-3">Period</th>
                            <th class="py-3 px-3 text-center">Attendance</th>
                            <th class="py-3 px-3 text-right">Basic Pay</th>
                            <th class="py-3 px-3 text-right">OT Pay</th>
                            <th class="py-3 px-3 text-right">Allowances</th>
                            <th class="py-3 px-3 text-right">Deductions</th>
                            <th class="py-3 px-3 text-right font-bold">Net Salary</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($payrolls as $p)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">
                                    {{ $p->payroll_number }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $p->user?->name ?? 'Unknown' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ ucfirst($p->user?->role ?? '') }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="text-slate-800 dark:text-slate-200">
                                        {{ \Carbon\Carbon::parse($p->period_start)->format('d M') }} - {{ \Carbon\Carbon::parse($p->period_end)->format('d M Y') }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">{{ ucfirst($p->period_type) }}</div>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div>
                                        <span class="text-emerald-600 dark:text-emerald-400 font-medium">{{ $p->present_days }}P</span>
                                        @if($p->half_days > 0)
                                            <span class="text-amber-600 dark:text-amber-400 ml-1 font-medium">{{ $p->half_days }}H</span>
                                        @endif
                                        @if($p->leave_days > 0)
                                            <span class="text-blue-600 dark:text-blue-400 ml-1 font-medium">{{ $p->leave_days }}L</span>
                                        @endif
                                        @if($p->absent_days > 0)
                                            <span class="text-rose-600 dark:text-rose-400 ml-1 font-medium">{{ $p->absent_days }}A</span>
                                        @endif
                                    </div>
                                    @if($p->overtime_hours > 0)
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            +{{ $p->overtime_hours }}h OT
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right text-slate-700 dark:text-slate-300">
                                    &#8377;{{ number_format($p->basic_pay, 2) }}
                                </td>
                                <td class="py-3 px-3 text-right text-slate-700 dark:text-slate-300">
                                    &#8377;{{ number_format($p->overtime_pay, 2) }}
                                </td>
                                <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400">
                                    +&#8377;{{ number_format($p->allowances, 2) }}
                                </td>
                                <td class="py-3 px-3 text-right text-rose-600 dark:text-rose-400">
                                    -&#8377;{{ number_format($p->deductions, 2) }}
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                                    &#8377;{{ number_format($p->net_salary, 2) }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($p->status === 'paid')
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Paid</span>
                                    @elseif($p->status === 'approved')
                                        <span class="font-bold text-blue-600 dark:text-blue-400">Approved</span>
                                    @else
                                        <span class="font-bold text-amber-600 dark:text-amber-400">Draft</span>
                                    @endif
                                    @if($p->payment_date)
                                        <div class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($p->payment_date)->format('d M') }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('finance.payroll.show', $p->id) }}" class="text-blue-600 hover:underline">View</a>
                                        <span class="text-slate-300 dark:text-slate-600">|</span>
                                        <a href="{{ route('finance.payroll.pdf', $p->id) }}" class="text-blue-600 hover:underline">PDF</a>
                                        <span class="text-slate-300 dark:text-slate-600">|</span>
                                        <a href="{{ route('finance.payroll.sendWhatsApp', $p->id) }}" target="_blank" class="text-blue-600 hover:underline">WhatsApp</a>
                                        <span class="text-slate-300 dark:text-slate-600">|</span>
                                        <button type="button" 
                                                onclick="openStatusModal({{ $p->id }}, '{{ $p->payroll_number }}', '{{ $p->status }}', '{{ $p->payment_reference ?? '' }}', '{{ $p->notes ?? '' }}')"
                                                class="text-blue-600 hover:underline">
                                            Status
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-8 text-center text-slate-400">
                                    No payroll slips generated for this period. Click "Generate Payroll" above to run batch calculations.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payrolls->hasPages())
                <div class="pt-3 border-t border-slate-200 dark:border-slate-800">
                    {{ $payrolls->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- Generate Payroll Modal --}}
    <div id="generateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden">
        <div class="bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 w-full max-w-lg p-6 mx-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                    Payroll Generator
                </h3>
                <button type="button" onclick="closeGenerateModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">✕</button>
            </div>

            <form method="POST" action="{{ route('finance.payroll.generate') }}" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Payroll Cycle Type</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center gap-2 p-2 rounded border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-medium">
                            <input type="radio" name="period_type" value="monthly" checked onchange="adjustDateDefaults('monthly')">
                            <span>Monthly</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-medium">
                            <input type="radio" name="period_type" value="weekly" onchange="adjustDateDefaults('weekly')">
                            <span>Weekly</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-medium">
                            <input type="radio" name="period_type" value="daily" onchange="adjustDateDefaults('daily')">
                            <span>Per-Day</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Period Start</label>
                        <input type="date" id="gen_period_start" name="period_start" required
                               value="{{ \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString() }}"
                               class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Period End</label>
                        <input type="date" id="gen_period_end" name="period_end" required
                               value="{{ \Carbon\Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString() }}"
                               class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Target Employees</label>
                    <select name="user_id" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        <option value="">All Salaried Employees</option>
                        @foreach($internalUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="closeGenerateModal()" class="px-4 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 rounded transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-medium text-white bg-[#2563eb] hover:bg-blue-700 rounded transition">
                        Run Batch Calculation
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Update Status Modal --}}
    <div id="statusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden">
        <div class="bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 w-full max-w-md p-6 mx-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                    Update Payroll <span id="statusModalNumber" class="text-blue-600"></span>
                </h3>
                <button type="button" onclick="closeStatusModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">✕</button>
            </div>

            <form id="statusForm" method="POST" action="" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select id="modal_status" name="status" required class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        <option value="draft">Draft</option>
                        <option value="approved">Approved</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Date</label>
                    <input type="date" id="modal_payment_date" name="payment_date" value="{{ now()->toDateString() }}"
                           class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Reference / UTR #</label>
                    <input type="text" id="modal_payment_reference" name="payment_reference" placeholder="UTR-982736192"
                           class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Notes</label>
                    <textarea id="modal_notes" name="notes" rows="2" placeholder="Audit notes..."
                              class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 rounded transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-medium text-white bg-[#2563eb] hover:bg-blue-700 rounded transition">
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
