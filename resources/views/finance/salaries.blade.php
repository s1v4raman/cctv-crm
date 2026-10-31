<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                    Salaries
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.analytics') }}" class="btn-secondary">
                    Analytics
                </a>
                <a href="{{ route('finance.payroll.index') }}" class="btn-primary">
                    Payroll
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">

        @if(session('status'))
            <div class="p-3 rounded border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 text-xs font-medium flex items-center justify-between">
                <span>{{ session('status') }}</span>
                <x-close-button size="sm" tone="inherit" label="Dismiss message" onclick="this.parentElement.remove()" />
            </div>
        @endif

        {{-- Employee Category Sub-Navigation --}}
        <x-employee-subnav active="salaries" />

        {{-- Financial Macro Metrics --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-4">
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Monthly salaries</span>
                <span class="text-xl font-semibold text-slate-900 dark:text-white mt-1 block">{{ \App\Support\Inr::format($totalMonthlyPayroll) }}</span>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-4">
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Salary set</span>
                <span class="text-xl font-semibold text-slate-900 dark:text-white mt-1 block">{{ $configuredCount }} of {{ $employees->total() }} people</span>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 flex flex-wrap items-center justify-between gap-4">
            <form method="GET" action="{{ route('finance.salaries.index') }}" class="flex flex-wrap items-center gap-3">
                <input aria-label="Search employee name" type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Search employee name..." 
                       class="text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white w-56 sm:w-64 focus:outline-none" style="height: 38px;">

                <select aria-label="Role" name="role" onchange="this.form.submit()" class="text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    <option value="">All Roles</option>
                    <option value="technician" @selected($roleFilter === 'technician')>Field Technicians</option>
                    <option value="staff" @selected($roleFilter === 'staff')>Staff / Engineers</option>
                    <option value="admin" @selected($roleFilter === 'admin')>Admins</option>
                </select>

                @if($search !== '' || $roleFilter)
                    <a href="{{ route('finance.salaries.index') }}" class="text-xs text-blue-600 hover:underline">
                        Clear Filters
                    </a>
                @endif
            </form>

            <span class="text-xs text-slate-500 dark:text-slate-400">
                Showing {{ $employees->count() }} of {{ $employees->total() }} employees
            </span>
        </div>

        {{-- Salary Master Table --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 text-[11px] font-bold">
                        <tr>
                            <th class="py-3 px-3">Employee</th>
                            <th class="py-3 px-3">Role</th>
                            <th class="py-3 px-3 text-right">Base Monthly</th>
                            <th class="py-3 px-3 text-right">Weekly Rate</th>
                            <th class="py-3 px-3 text-right">Daily Rate</th>
                            <th class="py-3 px-3 text-right">Allowances</th>
                            <th class="py-3 px-3 text-right">Deductions</th>
                            <th class="py-3 px-3">Payment Method</th>
                            <th class="py-3 px-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($employees as $emp)
                            @php
                                $sal = $emp->salaryStructure;
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $emp->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $emp->email }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="text-slate-700 dark:text-slate-300">
                                        {{ ucfirst($emp->role) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                                    @if($sal && $sal->base_salary_monthly > 0)
                                        &#8377;{{ number_format($sal->base_salary_monthly, 2) }}
                                    @else
                                        <span class="text-slate-500 dark:text-slate-400 font-normal">Not set</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400">
                                    @if($sal && $sal->weekly_rate > 0)
                                        &#8377;{{ number_format($sal->weekly_rate, 2) }}
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right text-slate-700 dark:text-slate-300">
                                    @if($sal && $sal->daily_rate > 0)
                                        &#8377;{{ number_format($sal->daily_rate, 2) }}
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400">
                                    @if($sal && $sal->total_allowances > 0)
                                        +&#8377;{{ number_format($sal->total_allowances, 2) }}
                                    @else
                                        <span class="text-slate-400">&#8377;0</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right text-rose-600 dark:text-rose-400">
                                    @if($sal && $sal->deductions > 0)
                                        -&#8377;{{ number_format($sal->deductions, 2) }}
                                    @else
                                        <span class="text-slate-400">&#8377;0</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 capitalize text-slate-600 dark:text-slate-400">
                                    {{ $sal ? str_replace('_', ' ', $sal->payment_method) : 'Bank Transfer' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <button type="button" 
                                            onclick="openSalaryModal({{ json_encode($emp) }}, {{ json_encode($sal) }})"
                                            class="text-blue-600 hover:underline">
                                        Edit Rates
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    No internal staff records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($employees->hasPages())
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- Salary Structure Configuration Modal --}}
    <div id="salaryModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
        <div class="bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white" id="salaryModalTitle">Configure Salary Structure</h3>
                    <p class="text-xs text-slate-500" id="salaryModalSub">Set monthly base, daily and weekly wages</p>
                </div>
                <x-close-button onclick="document.getElementById('salaryModal').classList.add('hidden')" />
            </div>

            <form id="salaryForm" method="POST" action="" class="space-y-3 mt-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Base Monthly Salary (&#8377;) *</label>
                    <input aria-label="Base Monthly Salary (&#8377;)" type="number" step="100" name="base_salary_monthly" id="fBaseMonthly" required placeholder="35000" oninput="recalcRates()" class="w-full text-xs font-bold rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Daily Rate (&#8377;)</label>
                        <input aria-label="Daily Rate (&#8377;)" type="number" step="10" name="daily_rate" id="fDailyRate" placeholder="1346.15" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Weekly Rate (&#8377;)</label>
                        <input aria-label="Weekly Rate (&#8377;)" type="number" step="50" name="weekly_rate" id="fWeeklyRate" placeholder="8076.92" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Hourly Base (&#8377;)</label>
                        <input aria-label="Hourly Base (&#8377;)" type="number" step="1" name="hourly_rate" id="fHourlyRate" placeholder="168.27" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Overtime / Hr (&#8377;)</label>
                        <input aria-label="Overtime / Hr (&#8377;)" type="number" step="1" name="overtime_hourly_rate" id="fOvertimeHourlyRate" placeholder="210.00" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Travel / Site Allowance (&#8377;)</label>
                        <input aria-label="Travel / Site Allowance (&#8377;)" type="number" step="50" name="travel_allowance" id="fTravelAllowance" placeholder="0.00" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Monthly Deductions (&#8377;)</label>
                        <input aria-label="Monthly Deductions (&#8377;)" type="number" step="50" name="deductions" id="fDeductions" placeholder="0.00" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                        <select aria-label="Payment Method" name="payment_method" id="fPaymentMethod" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                            <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                            <option value="upi">UPI / GPay</option>
                            <option value="cash">Cash Voucher</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">UPI ID</label>
                        <input aria-label="UPI ID" type="text" name="upi_id" id="fUpiId" placeholder="mobile@upi" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Bank Name</label>
                        <input aria-label="Bank Name" type="text" name="bank_name" id="fBankName" placeholder="HDFC, SBI" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Account No.</label>
                        <input aria-label="Account No." type="text" name="bank_account_number" id="fBankAcc" placeholder="1234567890" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">IFSC Code</label>
                        <input aria-label="IFSC Code" type="text" name="bank_ifsc" id="fBankIfsc" placeholder="HDFC0001234" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Notes</label>
                    <input aria-label="Notes" type="text" name="notes" id="fNotes" placeholder="Notes" class="w-full text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('salaryModal').classList.add('hidden')" class="px-4 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 rounded transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition">Save Salary Structure</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openSalaryModal(emp, sal) {
            document.getElementById('salaryModalTitle').innerText = 'Salary Structure: ' + emp.name;
            document.getElementById('salaryModalSub').innerText = 'Role: ' + emp.role.toUpperCase() + ' • ' + emp.email;
            document.getElementById('salaryForm').action = '/finance/salaries/' + emp.id;

            if (sal) {
                document.getElementById('fBaseMonthly').value = sal.base_salary_monthly || '';
                document.getElementById('fDailyRate').value = sal.daily_rate || '';
                document.getElementById('fWeeklyRate').value = sal.weekly_rate || '';
                document.getElementById('fHourlyRate').value = sal.hourly_rate || '';
                document.getElementById('fOvertimeHourlyRate').value = sal.overtime_hourly_rate || '';
                document.getElementById('fTravelAllowance').value = sal.travel_allowance || '';
                document.getElementById('fDeductions').value = sal.deductions || '';
                document.getElementById('fPaymentMethod').value = sal.payment_method || 'bank_transfer';
                document.getElementById('fUpiId').value = sal.upi_id || '';
                document.getElementById('fBankName').value = sal.bank_name || '';
                document.getElementById('fBankAcc').value = sal.bank_account_number || '';
                document.getElementById('fBankIfsc').value = sal.bank_ifsc || '';
                document.getElementById('fNotes').value = sal.notes || '';
            } else {
                document.getElementById('fBaseMonthly').value = '';
                document.getElementById('fDailyRate').value = '';
                document.getElementById('fWeeklyRate').value = '';
                document.getElementById('fHourlyRate').value = '';
                document.getElementById('fOvertimeHourlyRate').value = '';
                document.getElementById('fTravelAllowance').value = '';
                document.getElementById('fDeductions').value = '';
                document.getElementById('fPaymentMethod').value = 'bank_transfer';
                document.getElementById('fUpiId').value = '';
                document.getElementById('fBankName').value = '';
                document.getElementById('fBankAcc').value = '';
                document.getElementById('fBankIfsc').value = '';
                document.getElementById('fNotes').value = '';
            }

            document.getElementById('salaryModal').classList.remove('hidden');
        }

        function recalcRates() {
            const monthly = parseFloat(document.getElementById('fBaseMonthly').value);
            if (!isNaN(monthly) && monthly > 0) {
                const daily = (monthly / 26).toFixed(2);
                const weekly = (daily * 6).toFixed(2);
                const hourly = (daily / 8).toFixed(2);
                const ot = (hourly * 1.25).toFixed(2);

                document.getElementById('fDailyRate').value = daily;
                document.getElementById('fWeeklyRate').value = weekly;
                document.getElementById('fHourlyRate').value = hourly;
                document.getElementById('fOvertimeHourlyRate').value = ot;
            }
        }
    </script>
</x-app-layout>
