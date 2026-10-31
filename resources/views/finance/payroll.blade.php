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
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-4">
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Net pay for {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</span>
                <span class="text-xl font-semibold text-slate-900 dark:text-white mt-1 block">{{ \App\Support\Inr::format($totalNetPayroll) }}</span>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-4">
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Paid</span>
                <span class="text-xl font-semibold text-slate-900 dark:text-white mt-1 block">{{ \App\Support\Inr::format($totalPaidPayroll) }}</span>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-4">
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Still to pay</span>
                <span class="text-xl font-semibold {{ $totalPendingPayroll > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-900 dark:text-white' }} mt-1 block">{{ \App\Support\Inr::format($totalPendingPayroll) }}</span>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
            <form method="GET" action="{{ route('finance.payroll.index') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-300 block mb-1">Select Month</label>
                    <input aria-label="Select Month" type="month" 
                           name="month" 
                           value="{{ $selectedMonth }}" 
                           onchange="this.form.submit()"
                           class="text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-300 block mb-1">Payment Status</label>
                    <select aria-label="Payment Status" name="status" onchange="this.form.submit()" 
                            class="text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ $statusFilter === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-300 block mb-1">Employee</label>
                    <select aria-label="Employee" name="user_id" onchange="this.form.submit()" 
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
                <h2 class="font-bold text-slate-900 dark:text-white text-sm">Salary slips</h2>
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    {{ $payrolls->total() }} {{ Str::plural('slip', $payrolls->total()) }}
                </span>
            </div>

            {{-- Rupees without paise unless the amount has some --}}
            @php $money = fn ($v) => \App\Support\Inr::format($v, fmod((float) $v, 1) != 0.0 ? 2 : 0); @endphp
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="text-xs text-slate-500 dark:text-slate-400">
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <th class="py-2 px-3 font-medium">Employee</th>
                            <th class="py-2 px-3 font-medium">Days</th>
                            <th class="py-2 px-3 font-medium text-right">Basic</th>
                            <th class="py-2 px-3 font-medium text-right">Overtime</th>
                            <th class="py-2 px-3 font-medium text-right">Allowances</th>
                            <th class="py-2 px-3 font-medium text-right">Deductions</th>
                            <th class="py-2 px-3 font-medium text-right">Net pay</th>
                            <th class="py-2 px-3 font-medium">Status</th>
                            <th class="py-2 px-3 font-medium"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($payrolls as $p)
                            @php
                                $start = \Carbon\Carbon::parse($p->period_start);
                                $end = \Carbon\Carbon::parse($p->period_end);
                                $period = $start->isSameMonth($end) ? $start->format('j') . '–' . $end->format('j M') : $start->format('j M') . '–' . $end->format('j M');
                                $days = collect([
                                    ($p->present_days + 0) . ' present',
                                    $p->half_days > 0 ? ($p->half_days + 0) . ' half' : null,
                                    $p->leave_days > 0 ? ($p->leave_days + 0) . ' leave' : null,
                                    $p->absent_days > 0 ? ($p->absent_days + 0) . ' absent' : null,
                                ])->filter()->implode(' · ');
                            @endphp
                            <tr class="align-top">
                                <td class="py-2.5 px-3">
                                    <div class="font-medium text-slate-900 dark:text-white whitespace-nowrap">{{ $p->user?->name ?? 'Unknown' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $p->payroll_number }} · {{ $period }}</div>
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="whitespace-nowrap">{{ $days }}</div>
                                    @if($p->overtime_hours > 0)
                                        <div class="text-xs text-slate-500 dark:text-slate-400">+{{ $p->overtime_hours + 0 }}h overtime</div>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-right whitespace-nowrap">{{ $money($p->basic_pay) }}</td>
                                <td class="py-2.5 px-3 text-right whitespace-nowrap">{{ $p->overtime_pay > 0 ? $money($p->overtime_pay) : '—' }}</td>
                                <td class="py-2.5 px-3 text-right whitespace-nowrap">{{ $p->allowances > 0 ? '+ ' . $money($p->allowances) : '—' }}</td>
                                <td class="py-2.5 px-3 text-right whitespace-nowrap">{{ $p->deductions > 0 ? '− ' . $money($p->deductions) : '—' }}</td>
                                <td class="py-2.5 px-3 text-right whitespace-nowrap font-semibold text-slate-900 dark:text-white">{{ $money($p->net_salary) }}</td>
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    <button type="button" title="Change status"
                                            onclick="openStatusModal({{ $p->id }}, '{{ $p->payroll_number }}', '{{ $p->status }}', '{{ $p->payment_reference ?? '' }}', '{{ $p->notes ?? '' }}')"
                                            class="inline-flex items-center gap-1 hover:underline {{ $p->status === 'paid' ? 'text-slate-900 dark:text-white' : ($p->status === 'approved' ? 'text-slate-600 dark:text-slate-300' : 'text-amber-700 dark:text-amber-400') }}">
                                        {{ $p->status === 'paid' ? 'Paid' : ($p->status === 'approved' ? 'Approved' : 'Draft') }}
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    @if($p->payment_date)
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse($p->payment_date)->format('j M') }}</div>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="flex items-center gap-3 whitespace-nowrap text-sm">
                                        <a href="{{ route('finance.payroll.show', $p->id) }}">View</a>
                                        <a href="{{ route('finance.payroll.pdf', $p->id) }}">PDF</a>
                                        <a href="{{ route('finance.payroll.sendWhatsApp', $p->id) }}" target="_blank" class="inline-flex items-center gap-1">
                                            <x-icon.whatsapp class="w-3.5 h-3.5 text-black dark:text-white shrink-0" />
                                            <span>WhatsApp</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    No salary slips for this month yet. Use <strong>Generate Payroll</strong> to create them from attendance and salaries.
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
                        <input aria-label="Period Start" type="date" id="gen_period_start" name="period_start" required
                               value="{{ \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString() }}"
                               class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Period End</label>
                        <input aria-label="Period End" type="date" id="gen_period_end" name="period_end" required
                               value="{{ \Carbon\Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString() }}"
                               class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Target Employees</label>
                    <select aria-label="Target Employees" name="user_id" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
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
                    <button type="submit" class="px-4 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition">
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
                    <select aria-label="Status" id="modal_status" name="status" required class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        <option value="draft">Draft</option>
                        <option value="approved">Approved</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Date</label>
                    <input aria-label="Payment Date" type="date" id="modal_payment_date" name="payment_date" value="{{ now()->toDateString() }}"
                           class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Reference / UTR #</label>
                    <input aria-label="Payment Reference / UTR #" type="text" id="modal_payment_reference" name="payment_reference" placeholder="UTR-982736192"
                           class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Notes</label>
                    <textarea aria-label="Notes" id="modal_notes" name="notes" rows="2" placeholder="Audit notes..."
                              class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 rounded transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition">
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
