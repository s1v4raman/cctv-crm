<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-[0_0_10px_#3b82f6]"></span>
                    Employee Salary Master &amp; Wage Structures
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">Monthly, weekly, and per-day base salary rates, allowances, deductions &amp; bank accounts</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.analytics') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 text-xs font-extrabold shadow-md shadow-amber-500/20 transition">
                    📈 Financial Salary Analytics
                </a>
                <a href="{{ route('finance.payroll.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition">
                    💵 Payroll Generation
                </a>
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

            {{-- Employee Category Sub-Navigation --}}
            <x-employee-subnav active="salaries" />

            {{-- Financial Macro Metrics --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="pg-card p-4 border-l-4 border-l-blue-600">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Monthly Payroll Run-Rate</span>
                    <span class="text-2xl font-black text-slate-900 dark:text-white mt-1 block font-mono">
                        ₹{{ number_format($totalMonthlyPayroll, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 block">Base monthly internal commitment</span>
                </div>
                <div class="pg-card p-4 border-l-4 border-l-emerald-600">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Weekly Salary Budget</span>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block font-mono">
                        ₹{{ number_format($totalWeeklyPayroll, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 block">Estimated weekly wage requirement</span>
                </div>
                <div class="pg-card p-4 border-l-4 border-l-amber-600">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Per-Day Labor Cost</span>
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 block font-mono">
                        ₹{{ number_format($totalDailyPayroll, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 block">Daily team operational burn</span>
                </div>
                <div class="pg-card p-4 border-l-4 border-l-purple-600">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Salaried Headcount</span>
                    <span class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1 block">
                        {{ $configuredCount }} / {{ $employees->total() }}
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 block">Avg Monthly: ₹{{ number_format($avgMonthlySalary, 0) }}</span>
                </div>
            </div>

            {{-- Filter & Search Bar --}}
            <div class="pg-card p-4 flex flex-wrap items-center justify-between gap-4">
                <form method="GET" action="{{ route('finance.salaries.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           placeholder="Search employee name or email..." 
                           class="text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3.5 py-1.5 text-slate-800 dark:text-slate-200 w-56 sm:w-64">

                    <select name="role" onchange="this.form.submit()" class="text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-slate-800 dark:text-slate-200">
                        <option value="">All Roles</option>
                        <option value="technician" @selected($roleFilter === 'technician')>Field Technicians</option>
                        <option value="staff" @selected($roleFilter === 'staff')>Staff / Engineers</option>
                        <option value="admin" @selected($roleFilter === 'admin')>Admins</option>
                    </select>

                    @if($search !== '' || $roleFilter)
                        <a href="{{ route('finance.salaries.index') }}" class="text-xs text-blue-600 dark:text-blue-400 font-bold hover:underline">
                            Clear Filters
                        </a>
                    @endif
                </form>

                <span class="text-xs text-slate-400">
                    Showing {{ $employees->count() }} of {{ $employees->total() }} employees
                </span>
            </div>

            {{-- Salary Master Table --}}
            <div class="pg-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-4">Employee</th>
                                <th class="py-3 px-4">Role</th>
                                <th class="py-3 px-4">Base Monthly</th>
                                <th class="py-3 px-4">Weekly Rate</th>
                                <th class="py-3 px-4">Daily Rate</th>
                                <th class="py-3 px-4">Allowances</th>
                                <th class="py-3 px-4">Deductions</th>
                                <th class="py-3 px-4">Payment Method</th>
                                <th class="py-3 px-4 text-right">Configure</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($employees as $emp)
                                @php
                                    $sal = $emp->salaryStructure;
                                @endphp
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-850/40 transition">
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
                                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                            {{ $emp->role === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : '' }}
                                            {{ $emp->role === 'technician' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300' : '' }}
                                            {{ $emp->role === 'staff' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                        ">
                                            {{ ucfirst($emp->role) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                        @if($sal && $sal->base_salary_monthly > 0)
                                            ₹{{ number_format($sal->base_salary_monthly, 2) }}
                                        @else
                                            <span class="text-amber-600 dark:text-amber-400 font-normal">Not Set</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                                        @if($sal && $sal->weekly_rate > 0)
                                            ₹{{ number_format($sal->weekly_rate, 2) }}
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-slate-700 dark:text-slate-300">
                                        @if($sal && $sal->daily_rate > 0)
                                            ₹{{ number_format($sal->daily_rate, 2) }}
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-slate-600 dark:text-slate-400">
                                        @if($sal && $sal->total_allowances > 0)
                                            +₹{{ number_format($sal->total_allowances, 2) }}
                                        @else
                                            <span class="text-slate-400">₹0</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-rose-600 dark:text-rose-400">
                                        @if($sal && $sal->deductions > 0)
                                            -₹{{ number_format($sal->deductions, 2) }}
                                        @else
                                            <span class="text-slate-400">₹0</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 capitalize text-slate-600 dark:text-slate-400">
                                        {{ $sal ? str_replace('_', ' ', $sal->payment_method) : 'Bank Transfer' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <button type="button" 
                                                onclick="openSalaryModal({{ json_encode($emp) }}, {{ json_encode($sal) }})"
                                                class="px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 text-blue-600 dark:text-blue-400 font-bold text-xs transition">
                                            ⚙️ Edit Rates
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
                    <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Salary Structure Configuration Modal --}}
    <div id="salaryModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white" id="salaryModalTitle">Configure Salary Structure</h3>
                    <p class="text-[11px] text-slate-400" id="salaryModalSub">Set monthly base, daily and weekly wages</p>
                </div>
                <button type="button" onclick="document.getElementById('salaryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form id="salaryForm" method="POST" action="" class="space-y-4 mt-4">
                @csrf
                <div class="p-3 bg-blue-50/50 dark:bg-blue-950/30 rounded-xl border border-blue-100 dark:border-blue-900/40 text-[11px] text-blue-700 dark:text-blue-300">
                    💡 <strong>Smart Auto-Calculate:</strong> Enter Base Monthly Salary — Daily Rate (Monthly / 26) and Weekly Rate (Daily × 6) will automatically pre-populate!
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Base Monthly Salary (₹) *</label>
                    <input type="number" step="100" name="base_salary_monthly" id="fBaseMonthly" required placeholder="e.g. 35000" oninput="recalcRates()" class="w-full text-xs font-bold font-mono rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-900 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Daily Rate (₹)</label>
                        <input type="number" step="10" name="daily_rate" id="fDailyRate" placeholder="e.g. 1346.15" class="w-full text-xs font-semibold font-mono rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Weekly Rate (₹)</label>
                        <input type="number" step="50" name="weekly_rate" id="fWeeklyRate" placeholder="e.g. 8076.92" class="w-full text-xs font-semibold font-mono rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Hourly Base (₹)</label>
                        <input type="number" step="1" name="hourly_rate" id="fHourlyRate" placeholder="e.g. 168.27" class="w-full text-xs font-semibold font-mono rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Overtime / Hr (₹)</label>
                        <input type="number" step="1" name="overtime_hourly_rate" id="fOvertimeHourlyRate" placeholder="e.g. 210.00" class="w-full text-xs font-semibold font-mono rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Travel / Site Allowance (₹)</label>
                        <input type="number" step="50" name="travel_allowance" id="fTravelAllowance" placeholder="0.00" class="w-full text-xs font-semibold font-mono rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Monthly Deductions (₹)</label>
                        <input type="number" step="50" name="deductions" id="fDeductions" placeholder="0.00" class="w-full text-xs font-semibold font-mono rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                        <select name="payment_method" id="fPaymentMethod" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                            <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                            <option value="upi">UPI / GPay</option>
                            <option value="cash">Cash Voucher</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">UPI ID (if applicable)</label>
                        <input type="text" name="upi_id" id="fUpiId" placeholder="e.g. mobile@upi" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Bank Name</label>
                        <input type="text" name="bank_name" id="fBankName" placeholder="HDFC, SBI" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Account No.</label>
                        <input type="text" name="bank_account_number" id="fBankAcc" placeholder="1234567890" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">IFSC Code</label>
                        <input type="text" name="bank_ifsc" id="fBankIfsc" placeholder="HDFC0001234" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Salary Structure Notes</label>
                    <input type="text" name="notes" id="fNotes" placeholder="e.g. Certified on-site field engineer scale" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('salaryModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/25">Save Salary Structure</button>
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
