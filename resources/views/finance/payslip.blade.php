<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4 print:hidden">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-[0_0_10px_#3b82f6]"></span>
                    Salary Payslip: {{ $payroll->payroll_number }}
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">Official statement of earnings and attendance deductions</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('finance.payroll.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-700 shadow-2xs transition">
                    ← Back to Payroll
                </a>
                <a href="{{ route('finance.payroll.pdf', $payroll) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-100 dark:hover:bg-white !text-white dark:!text-slate-900 text-xs font-bold shadow-xs transition">
                    <svg class="w-4 h-4 !text-white dark:!text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="!text-white dark:!text-slate-900">Download PDF</span>
                </a>
                <button type="button" 
                        onclick="document.getElementById('whatsappModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 !text-white text-xs font-bold shadow-xs transition">
                    <svg class="w-4 h-4 fill-current !text-white" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.983.54 1.776.818 2.796.818 3.182 0 5.768-2.587 5.768-5.769.001-3.181-2.585-5.767-5.768-5.767zm9.969 5.766c0 5.495-4.474 9.969-9.969 9.969-1.748 0-3.385-.453-4.819-1.246l-5.212 1.367 1.391-5.084c-.887-1.493-1.391-3.238-1.391-5.006 0-5.495 4.474-9.969 9.969-9.969 5.495 0 9.969 4.474 9.969 9.969z"/>
                    </svg>
                    <span class="!text-white">Send via WhatsApp</span>
                </button>
                <button onclick="window.print()"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 !text-white text-xs font-bold shadow-xs transition">
                    <span class="!text-white">🖨️ Print</span>
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        .pg-wrap { background:#f8fafc; min-height:100vh; padding:1.5rem 0 3rem; }
        .dark .pg-wrap { background:#060913; }
        .pg-inner { max-width:860px; margin:0 auto; padding:0 1.25rem; }

        @media print {
            body { background: #ffffff !important; color: #000000 !important; }
            header, nav, .print\:hidden { display: none !important; }
            .pg-wrap { padding: 0 !important; background: transparent !important; }
            .pg-inner { max-width: 100% !important; padding: 0 !important; }
            .payslip-sheet {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 0 !important;
                padding: 2rem !important;
            }
        }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">
            <div class="payslip-sheet bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-8 shadow-xl space-y-8">
                
                {{-- Header & Company Brand --}}
                <div class="flex items-start justify-between border-b border-slate-200 dark:border-slate-800 pb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-black text-xl shadow-md">
                            📹
                        </div>
                        <div>
                            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">CCTV CRM &amp; SECURITY SYSTEMS</h1>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Electronic Surveillance &amp; Technical Services Private Limited</p>
                            <p class="text-[11px] text-slate-400">GSTIN: 33AAAAA0000A1Z5 | Reg: DL-772910</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                            @if($payroll->status === 'paid') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                            @elseif($payroll->status === 'approved') bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300
                            @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif">
                            PAYSLIP • {{ strtoupper($payroll->status) }}
                        </span>
                        <div class="font-mono font-bold text-sm text-slate-900 dark:text-white mt-1.5">{{ $payroll->payroll_number }}</div>
                        <div class="text-[11px] text-slate-400">Generated: {{ $payroll->created_at->format('d M Y') }}</div>
                    </div>
                </div>

                {{-- Employee & Period Grid --}}
                <div class="grid grid-cols-2 gap-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-xs">
                    <div class="space-y-1.5">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Employee Information</div>
                        <div class="font-bold text-sm text-slate-900 dark:text-white">{{ $payroll->user?->name }}</div>
                        <div class="text-slate-600 dark:text-slate-300"><span class="text-slate-400">Designation / Role:</span> <span class="font-semibold">{{ ucfirst($payroll->user?->role) }}</span></div>
                        <div class="text-slate-600 dark:text-slate-300"><span class="text-slate-400">Email:</span> {{ $payroll->user?->email }}</div>
                        <div class="text-slate-600 dark:text-slate-300"><span class="text-slate-400">Phone:</span> {{ $payroll->user?->phone ?? 'N/A' }}</div>
                    </div>
                    <div class="space-y-1.5 border-l border-slate-200 dark:border-slate-700 pl-6">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pay Period &amp; Disbursement</div>
                        <div class="text-slate-600 dark:text-slate-300"><span class="text-slate-400">Cycle Type:</span> <span class="font-bold uppercase text-blue-600">{{ $payroll->period_type }}</span></div>
                        <div class="text-slate-600 dark:text-slate-300"><span class="text-slate-400">Period:</span> <span class="font-semibold">{{ \Carbon\Carbon::parse($payroll->period_start)->format('d M Y') }} — {{ \Carbon\Carbon::parse($payroll->period_end)->format('d M Y') }}</span></div>
                        <div class="text-slate-600 dark:text-slate-300"><span class="text-slate-400">Payment Date:</span> {{ $payroll->payment_date ? \Carbon\Carbon::parse($payroll->payment_date)->format('d M Y') : 'Pending Payout' }}</div>
                        @if($payroll->payment_reference)
                            <div class="text-slate-600 dark:text-slate-300"><span class="text-slate-400">Ref / UTR:</span> <span class="font-mono font-semibold">{{ $payroll->payment_reference }}</span></div>
                        @endif
                    </div>
                </div>

                {{-- Bank & Payment Info --}}
                @php $salaryMaster = $payroll->user?->salaryStructure; @endphp
                @if($salaryMaster)
                    <div class="grid grid-cols-4 gap-4 p-3 rounded-xl border border-slate-200 dark:border-slate-800 text-[11px]">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Disbursal Mode</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200 capitalize">{{ $salaryMaster->payment_method }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Bank Name</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $salaryMaster->bank_name ?: 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Account #</span>
                            <span class="font-mono font-semibold text-slate-800 dark:text-slate-200">{{ $salaryMaster->bank_account_number ?: 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">IFSC / UPI</span>
                            <span class="font-mono font-semibold text-slate-800 dark:text-slate-200">{{ $salaryMaster->bank_ifsc ?: ($salaryMaster->upi_id ?: 'N/A') }}</span>
                        </div>
                    </div>
                @endif

                {{-- Attendance Summary Box --}}
                <div class="space-y-2">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Attendance &amp; Working Days Tally</h3>
                    <div class="grid grid-cols-6 gap-2 text-center p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase font-bold">Period Days</span>
                            <span class="font-black text-slate-800 dark:text-slate-200 text-sm">{{ $payroll->working_days }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-emerald-600 block uppercase font-bold">Present</span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm">{{ $payroll->present_days }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-amber-600 block uppercase font-bold">Half-Days</span>
                            <span class="font-black text-amber-600 dark:text-amber-400 text-sm">{{ $payroll->half_days }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-blue-600 block uppercase font-bold">Paid Leave</span>
                            <span class="font-black text-blue-600 dark:text-blue-400 text-sm">{{ $payroll->leave_days }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-red-500 block uppercase font-bold">Absent</span>
                            <span class="font-black text-red-500 dark:text-red-400 text-sm">{{ $payroll->absent_days }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-purple-600 block uppercase font-bold">Overtime</span>
                            <span class="font-black text-purple-600 dark:text-purple-400 text-sm">{{ $payroll->overtime_hours }} hrs</span>
                        </div>
                    </div>
                </div>

                {{-- Earnings vs Deductions Table --}}
                <div class="grid grid-cols-2 gap-6">
                    {{-- Earnings --}}
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                        <div class="bg-emerald-50 dark:bg-emerald-950/40 px-4 py-2 text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider border-b border-emerald-100 dark:border-emerald-900">
                            Earnings
                        </div>
                        <div class="p-4 space-y-2.5 text-xs text-slate-700 dark:text-slate-300">
                            <div class="flex items-center justify-between">
                                <span>Basic Wage / Earned Salary</span>
                                <span class="font-mono font-semibold">₹{{ number_format($payroll->basic_pay, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Overtime Pay ({{ $payroll->overtime_hours }}h)</span>
                                <span class="font-mono font-semibold text-purple-600 dark:text-purple-400">₹{{ number_format($payroll->overtime_pay, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Allowances (Travel &amp; Special)</span>
                                <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">₹{{ number_format($payroll->allowances, 2) }}</span>
                            </div>
                            <div class="border-t border-slate-100 dark:border-slate-800 pt-2 flex items-center justify-between font-bold text-slate-900 dark:text-white">
                                <span>Gross Earnings</span>
                                <span class="font-mono">₹{{ number_format($payroll->basic_pay + $payroll->overtime_pay + $payroll->allowances, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Deductions --}}
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                        <div class="bg-red-50 dark:bg-red-950/40 px-4 py-2 text-xs font-bold text-red-800 dark:text-red-300 uppercase tracking-wider border-b border-red-100 dark:border-red-900">
                            Deductions
                        </div>
                        <div class="p-4 space-y-2.5 text-xs text-slate-700 dark:text-slate-300">
                            <div class="flex items-center justify-between">
                                <span>Statutory / Tax / Advance Deductions</span>
                                <span class="font-mono font-semibold text-red-600 dark:text-red-400">₹{{ number_format($payroll->deductions, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-400">
                                <span>Absence Penalties</span>
                                <span class="font-mono font-semibold">₹0.00</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-400">
                                <span>Other Withholdings</span>
                                <span class="font-mono font-semibold">₹0.00</span>
                            </div>
                            <div class="border-t border-slate-100 dark:border-slate-800 pt-2 flex items-center justify-between font-bold text-slate-900 dark:text-white">
                                <span>Total Deductions</span>
                                <span class="font-mono text-red-600 dark:text-red-400">₹{{ number_format($payroll->deductions, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Net Pay Banner --}}
                <div class="p-5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 text-white flex items-center justify-between shadow-lg">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">Net Payable Salary</span>
                        <div class="text-3xl font-black font-mono mt-1">₹{{ number_format($payroll->net_salary, 2) }}</div>
                        <span class="text-[11px] opacity-75 mt-0.5 block">Direct bank transfer / disbursed per company payroll policy</span>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-bold opacity-80 uppercase">Status</div>
                        <div class="text-xl font-black capitalize">{{ $payroll->status }}</div>
                    </div>
                </div>

                {{-- Signatures & Legal Note --}}
                <div class="pt-8 grid grid-cols-2 gap-8 text-xs text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-800">
                    <div>
                        <p class="italic text-[11px]">This is a computer-generated salary slip and requires no physical seal when electronically verified.</p>
                        <p class="text-[10px] text-slate-400 mt-2">Questions regarding deductions or attendance should be submitted to HR / Accounts within 7 days.</p>
                    </div>
                    <div class="flex items-end justify-between gap-4 pt-6">
                        <div class="text-center w-36">
                            <div class="border-b border-slate-400 pb-1 font-semibold text-slate-800 dark:text-slate-200">{{ $payroll->creator?->name ?? 'Admin / HR' }}</div>
                            <span class="text-[10px] text-slate-400 uppercase tracking-wider block mt-1">Prepared By</span>
                        </div>
                        <div class="text-center w-36">
                            <div class="border-b border-slate-400 pb-1 font-semibold text-slate-800 dark:text-slate-200">{{ $payroll->user?->name }}</div>
                            <span class="text-[10px] text-slate-400 uppercase tracking-wider block mt-1">Employee Sign</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- WhatsApp Dispatch Modal --}}
    <div id="whatsappModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.983.54 1.776.818 2.796.818 3.182 0 5.768-2.587 5.768-5.769.001-3.181-2.585-5.767-5.768-5.767zm9.969 5.766c0 5.495-4.474 9.969-9.969 9.969-1.748 0-3.385-.453-4.819-1.246l-5.212 1.367 1.391-5.084c-.887-1.493-1.391-3.238-1.391-5.006 0-5.495 4.474-9.969 9.969-9.969 5.495 0 9.969 4.474 9.969 9.969z"/>
                        </svg>
                    </span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white font-heading">
                        Send Payslip via WhatsApp
                    </h3>
                </div>
                <button type="button" onclick="document.getElementById('whatsappModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">
                    &times;
                </button>
            </div>

            <form action="{{ route('finance.payroll.sendWhatsApp', $payroll) }}" method="GET" target="_blank" class="space-y-4 text-xs">
                @php
                    $detectedPhone = $payroll->getEmployeePhone();
                @endphp

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Recipient Mobile Number (with Country Code)</label>
                    <input type="text" name="phone" value="{{ $detectedPhone ?: '918789076658' }}" placeholder="e.g. 919876543210" required 
                           class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                    <p class="text-[11px] text-slate-400 mt-1">Recipient: <strong>{{ $payroll->user?->name }}</strong> ({{ $payroll->user?->role }})</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Message Preview</label>
                    <textarea name="custom_message" rows="6" class="w-full text-[11px] font-mono rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">{{ $payroll->getWhatsAppFormattedMessage() }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('whatsappModal').classList.add('hidden')" class="btn-secondary min-h-[44px] px-4 py-2.5 text-sm font-semibold rounded-xl text-slate-600 hover:text-slate-800 dark:text-slate-400">
                        Cancel
                    </button>
                    <button type="submit" onclick="setTimeout(() => document.getElementById('whatsappModal').classList.add('hidden'), 500)" class="btn-primary min-h-[44px] px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition flex items-center gap-2">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.983.54 1.776.818 2.796.818 3.182 0 5.768-2.587 5.768-5.769.001-3.181-2.585-5.767-5.768-5.767zm9.969 5.766c0 5.495-4.474 9.969-9.969 9.969-1.748 0-3.385-.453-4.819-1.246l-5.212 1.367 1.391-5.084c-.887-1.493-1.391-3.238-1.391-5.006 0-5.495 4.474-9.969 9.969-9.969 5.495 0 9.969 4.474 9.969 9.969z"/>
                        </svg>
                        <span>Open in WhatsApp</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
