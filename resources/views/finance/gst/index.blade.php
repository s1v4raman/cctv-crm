<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-lg shadow-amber-500/20 text-white">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-black tracking-tight text-slate-900 dark:text-white sm:text-2xl">
                            GST & Tax Compliance Center
                        </h2>
                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-300 dark:border-amber-800 rounded-full">
                            GSTR-1 & ITC Hub
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ $companyGst['trade_name'] }} • GSTIN: <strong class="text-slate-700 dark:text-slate-200 font-mono">{{ $companyGst['gstin'] }}</strong> • State: {{ $companyGst['state'] }} ({{ $companyGst['state_code'] }})
                    </p>
                </div>
            </div>

            <!-- Month Filter & Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                <form method="GET" action="{{ route('finance.gst.index') }}" class="flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-1 shadow-sm">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    <div class="flex items-center px-2.5 gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Period:</span>
                    </div>
                    <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()"
                           class="bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-lg border-0 py-1.5 px-3 focus:ring-2 focus:ring-amber-500 cursor-pointer">
                </form>

                <!-- Export Menu Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.outside="open = false" type="button"
                            class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Export Return</span>
                        <svg class="w-3.5 h-3.5 ml-0.5 opacity-70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" x-cloak
                         class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl py-2 z-50 animate-in fade-in zoom-in-95 duration-100">
                        <div class="px-3.5 py-1.5 text-[10px] font-black tracking-wider uppercase text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 mb-1">
                            Official GST Exports ({{ Carbon\Carbon::parse($selectedMonth.'-01')->format('M Y') }})
                        </div>
                        <a href="{{ route('finance.gst.export-json', ['month' => $selectedMonth]) }}" class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-amber-50 dark:hover:bg-amber-950/40 hover:text-amber-700 transition">
                            <span class="p-1.5 bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 rounded-lg text-[10px] font-black">JSON</span>
                            <div>
                                <div class="font-bold">GSTR-1 Portal JSON</div>
                                <div class="text-[10px] text-slate-400">Direct upload to gst.gov.in</div>
                            </div>
                        </a>
                        <a href="{{ route('finance.gst.export-csv', ['month' => $selectedMonth]) }}" class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-700 transition">
                            <span class="p-1.5 bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 rounded-lg text-[10px] font-black">CSV</span>
                            <div>
                                <div class="font-bold">GSTR-1 Sales Report</div>
                                <div class="text-[10px] text-slate-400">B2B, B2C & HSN Excel format</div>
                            </div>
                        </a>
                        <a href="{{ route('finance.gst.export-itc-csv', ['month' => $selectedMonth]) }}" class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-700 transition">
                            <span class="p-1.5 bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 rounded-lg text-[10px] font-black">ITC</span>
                            <div>
                                <div class="font-bold">GSTR-2B / ITC Purchases</div>
                                <div class="text-[10px] text-slate-400">Vendor POs & Expense claims</div>
                            </div>
                        </a>
                        <div class="border-t border-slate-100 dark:border-slate-800/60 my-1"></div>
                        <a href="{{ route('finance.gst.export-pdf', ['month' => $selectedMonth]) }}" class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-700 transition">
                            <span class="p-1.5 bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 rounded-lg text-[10px] font-black">PDF</span>
                            <div>
                                <div class="font-bold">Tax Audit Package PDF</div>
                                <div class="text-[10px] text-slate-400">Ready for CA & management</div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Record Filing Button -->
                <button @click="$dispatch('open-modal', 'record-filing-modal')" type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Record Filing & Challan</span>
                </button>

                <!-- Settings Button -->
                <button @click="$dispatch('open-modal', 'company-gst-modal')" type="button" title="GST Settings"
                        class="p-2 text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        {{-- Finance Category Sub-Navigation --}}
        <x-finance-subnav active="gst" />

        @if (session('status'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl flex items-center justify-between shadow-sm animate-in fade-in duration-200">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-emerald-100 dark:bg-emerald-900/80 text-emerald-700 dark:text-emerald-300 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-xs md:text-sm font-bold text-emerald-900 dark:text-emerald-200">{{ session('status') }}</span>
                </div>
            </div>
        @endif

        <!-- Filing Status Bar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 md:p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Return Period Status</span>
                    <span class="text-xs text-slate-300 dark:text-slate-600">•</span>
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ Carbon\Carbon::parse($selectedMonth.'-01')->format('F Y') }}</span>
                </div>
                <h3 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white flex flex-wrap items-center gap-3">
                    <span>GST Return Summary</span>
                    @if($filing && $filing->gstr3b_status === 'filed')
                        <span class="px-3 py-1 text-xs font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/40 rounded-full flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            Return Filed & Discharged
                        </span>
                    @elseif($filing && $filing->gstr3b_status === 'reconciled')
                        <span class="px-3 py-1 text-xs font-black uppercase tracking-wider bg-blue-50 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 border border-blue-200 dark:border-blue-500/40 rounded-full">
                            Reconciled & Ready to File
                        </span>
                    @else
                        <span class="px-3 py-1 text-xs font-black uppercase tracking-wider bg-amber-50 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/40 rounded-full">
                            Draft Computation Pending
                        </span>
                    @endif
                </h3>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 text-right">
                    <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">GSTR-1 Status</div>
                    <div class="text-xs font-black text-slate-900 dark:text-white uppercase mt-0.5">
                        {{ $filing ? ucfirst($filing->gstr1_status) : 'Pending' }}
                    </div>
                </div>
                <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 text-right">
                    <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">GSTR-3B Status</div>
                    <div class="text-xs font-black text-slate-900 dark:text-white uppercase mt-0.5">
                        {{ $filing ? ucfirst($filing->gstr3b_status) : 'Pending' }}
                    </div>
                </div>
                @if($filing && $filing->challan_no)
                    <div class="px-4 py-2.5 bg-emerald-50 dark:bg-emerald-950/60 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 text-right">
                        <div class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">Challan Ref</div>
                        <div class="text-xs font-mono font-black text-emerald-900 dark:text-emerald-300 mt-0.5">{{ $filing->challan_no }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Total Turnover -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-blue-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gross Sales Turnover</span>
                    <span class="p-2 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                    ₹{{ number_format($gstr1Data['total_gross_turnover'], 2) }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-between">
                    <span>Taxable: ₹{{ number_format($gstr1Data['total_taxable_turnover'], 2) }}</span>
                    <span class="font-bold text-blue-600 dark:text-blue-400">{{ $gstr1Data['total_invoices_count'] }} Invoices</span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
            </div>

            <!-- 2. Output Tax Collected -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-indigo-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Output GST (Sales)</span>
                    <span class="p-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-2">
                    ₹{{ number_format($gstr1Data['total_output_tax'], 2) }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-between">
                    <span>CGST+SGST: ₹{{ number_format($gstr1Data['total_output_cgst'] + $gstr1Data['total_output_sgst'], 2) }}</span>
                    <span>IGST: ₹{{ number_format($gstr1Data['total_output_igst'], 2) }}</span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
            </div>

            <!-- 3. Input Tax Credit (ITC Available) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-emerald-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Input Tax Credit (ITC)</span>
                    <span class="p-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
                    ₹{{ number_format($itcData['grand_total_itc'], 2) }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-between">
                    <span>POs: ₹{{ number_format($itcData['total_po_itc'], 2) }}</span>
                    <span>Expenses: ₹{{ number_format($itcData['total_expense_itc'], 2) }}</span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-600"></div>
            </div>

            <!-- 4. Net Tax Payable (Cash) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-amber-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Net Cash Tax Payable</span>
                    <span class="p-2 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-2">
                    ₹{{ number_format($gstr3bData['net_tax_payable_cash']['total'], 2) }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-between">
                    <span>After ITC Set-off</span>
                    @if($gstr3bData['itc_carry_forward']['total'] > 0)
                        <span class="text-emerald-600 font-bold">ITC C/F: ₹{{ number_format($gstr3bData['itc_carry_forward']['total'], 2) }}</span>
                    @else
                        <span class="text-amber-600 font-bold">To Pay in Cash</span>
                    @endif
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-600"></div>
            </div>
        </div>

        <!-- Navigation Tabs Container -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-4 mb-6">
                @php
                    $tabs = [
                        'overview' => ['label' => 'GSTR-3B Tax Computation & Set-Off', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                        'gstr1'    => ['label' => 'GSTR-1 Outward Sales (' . count($gstr1Data['b2b_invoices']) . ' B2B / ' . count($gstr1Data['b2c_small_summary']) . ' B2C)', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
                        'itc'      => ['label' => 'Input Tax Credit (' . count($itcData['purchase_orders']) . ' POs)', 'icon' => 'M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11'],
                        'hsn'      => ['label' => 'Table 12 HSN Summary (' . count($gstr1Data['hsn_summary']) . ' HSNs)', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                        'filings'  => ['label' => 'Filing & Challan Audit Log', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ];
                @endphp

                @foreach($tabs as $key => $tab)
                    <a href="{{ route('finance.gst.index', ['month' => $selectedMonth, 'tab' => $key]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition {{ $activeTab === $key ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' : 'bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}"/></svg>
                        <span>{{ $tab['label'] }}</span>
                    </a>
                @endforeach
            </div>

            <!-- TAB 1: GSTR-3B NET LIABILITY & SET-OFF MATRIX -->
            @if($activeTab === 'overview')
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">GSTR-3B Tax Computation & Set-Off Matrix</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Automated Indian GST offset rules (IGST -> CGST -> SGST sequence)</p>
                        </div>
                    </div>

                    <!-- Matrix Table -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-3 px-4">Tax Component / Head</th>
                                    <th class="py-3 px-4 text-right">Integrated Tax (IGST)</th>
                                    <th class="py-3 px-4 text-right">Central Tax (CGST)</th>
                                    <th class="py-3 px-4 text-right">State / UT Tax (SGST)</th>
                                    <th class="py-3 px-4 text-right bg-slate-100/80 dark:bg-slate-800 text-slate-900 dark:text-white">Total (₹)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                <!-- Row 1: Output Tax Liability -->
                                <tr class="bg-indigo-50/40 dark:bg-indigo-950/20 font-semibold">
                                    <td class="py-3.5 px-4 font-bold text-indigo-900 dark:text-indigo-300 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                        <span>(A) Total Output Tax Liability (Sales)</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono">₹{{ number_format($gstr3bData['output_tax']['igst'], 2) }}</td>
                                    <td class="py-3.5 px-4 text-right font-mono">₹{{ number_format($gstr3bData['output_tax']['cgst'], 2) }}</td>
                                    <td class="py-3.5 px-4 text-right font-mono">₹{{ number_format($gstr3bData['output_tax']['sgst'], 2) }}</td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-indigo-700 dark:text-indigo-400 bg-indigo-100/40 dark:bg-indigo-950/40">
                                        ₹{{ number_format($gstr3bData['output_tax']['total'], 2) }}
                                    </td>
                                </tr>

                                <!-- Row 2: Input Tax Credit Available -->
                                <tr class="bg-emerald-50/40 dark:bg-emerald-950/20 font-semibold">
                                    <td class="py-3.5 px-4 font-bold text-emerald-900 dark:text-emerald-300 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>(B) Eligible Input Tax Credit (Purchases & Expenses)</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-emerald-700 dark:text-emerald-400">₹{{ number_format($gstr3bData['input_tax_credit']['igst'], 2) }}</td>
                                    <td class="py-3.5 px-4 text-right font-mono text-emerald-700 dark:text-emerald-400">₹{{ number_format($gstr3bData['input_tax_credit']['cgst'], 2) }}</td>
                                    <td class="py-3.5 px-4 text-right font-mono text-emerald-700 dark:text-emerald-400">₹{{ number_format($gstr3bData['input_tax_credit']['sgst'], 2) }}</td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-100/40 dark:bg-emerald-950/40">
                                        ₹{{ number_format($gstr3bData['input_tax_credit']['total'], 2) }}
                                    </td>
                                </tr>

                                <!-- Row 3: Net Cash Tax Payable -->
                                <tr class="bg-amber-50 dark:bg-amber-950/40 font-black text-slate-900 dark:text-white">
                                    <td class="py-4 px-4 text-amber-900 dark:text-amber-300 flex items-center gap-2 text-sm">
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>(C) Net Tax Payable in Cash (A - B after Set-off)</span>
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono text-amber-700 dark:text-amber-400 text-sm">
                                        ₹{{ number_format($gstr3bData['net_tax_payable_cash']['igst'], 2) }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono text-amber-700 dark:text-amber-400 text-sm">
                                        ₹{{ number_format($gstr3bData['net_tax_payable_cash']['cgst'], 2) }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono text-amber-700 dark:text-amber-400 text-sm">
                                        ₹{{ number_format($gstr3bData['net_tax_payable_cash']['sgst'], 2) }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono text-base font-black text-amber-800 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/60">
                                        ₹{{ number_format($gstr3bData['net_tax_payable_cash']['total'], 2) }}
                                    </td>
                                </tr>

                                <!-- Row 4: Closing ITC Carry Forward -->
                                <tr class="text-slate-500 dark:text-slate-400 text-[11px]">
                                    <td class="py-3 px-4 font-semibold">(D) Closing ITC Balance (Carry Forward to Next Month)</td>
                                    <td class="py-3 px-4 text-right font-mono">₹{{ number_format($gstr3bData['itc_carry_forward']['igst'], 2) }}</td>
                                    <td class="py-3 px-4 text-right font-mono">₹{{ number_format($gstr3bData['itc_carry_forward']['cgst'], 2) }}</td>
                                    <td class="py-3 px-4 text-right font-mono">₹{{ number_format($gstr3bData['itc_carry_forward']['sgst'], 2) }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                        ₹{{ number_format($gstr3bData['itc_carry_forward']['total'], 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Guidelines Banner -->
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 space-y-1.5">
                        <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Filing Instructions & Compliance Deadlines</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 pl-1 text-[11px]">
                            <li><strong>GSTR-1 Due Date:</strong> 11th of every month (for Monthly filers) or 13th of month following quarter (QRMP).</li>
                            <li><strong>GSTR-3B Due Date:</strong> 20th of every month (or 22nd/24th based on state QRMP categories).</li>
                            <li>Download the <strong>GSTR-1 JSON</strong> from the export menu and upload directly into the GST Offline Tool or government portal.</li>
                        </ul>
                    </div>
                </div>
            @endif

            <!-- TAB 2: GSTR-1 OUTWARD SUPPLIES -->
            @if($activeTab === 'gstr1')
                <div class="space-y-8">
                    <!-- 1. Table 4: B2B Invoices -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-400 rounded-md text-[10px] font-black">TABLE 4</span>
                                    <span>B2B Invoices (Registered Clients with GSTIN)</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Total: {{ count($gstr1Data['b2b_invoices']) }} invoice(s)</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="py-3 px-3">Invoice No</th>
                                        <th class="py-3 px-3">Date</th>
                                        <th class="py-3 px-3">Customer / Company</th>
                                        <th class="py-3 px-3">Customer GSTIN</th>
                                        <th class="py-3 px-3">Place of Supply</th>
                                        <th class="py-3 px-3 text-right">Taxable (₹)</th>
                                        <th class="py-3 px-3 text-right">CGST (₹)</th>
                                        <th class="py-3 px-3 text-right">SGST (₹)</th>
                                        <th class="py-3 px-3 text-right">IGST (₹)</th>
                                        <th class="py-3 px-3 text-right font-bold">Total (₹)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                    @forelse($gstr1Data['b2b_invoices'] as $b2b)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                            <td class="py-3 px-3 font-mono font-bold text-blue-600 dark:text-blue-400">
                                                <a href="{{ route('invoices.show', $b2b['invoice_id']) }}" class="hover:underline">
                                                    {{ $b2b['invoice_no'] }}
                                                </a>
                                            </td>
                                            <td class="py-3 px-3 text-slate-500 whitespace-nowrap">{{ $b2b['invoice_date'] }}</td>
                                            <td class="py-3 px-3 font-semibold">{{ $b2b['customer_name'] }}</td>
                                            <td class="py-3 px-3 font-mono font-bold text-slate-800 dark:text-slate-200">{{ $b2b['customer_gstin'] }}</td>
                                            <td class="py-3 px-3">
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $b2b['is_intra_state'] ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400' : 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400' }}">
                                                    {{ $b2b['pos_state'] }} ({{ $b2b['pos_code'] }})
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($b2b['taxable_value'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($b2b['cgst'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($b2b['sgst'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($b2b['igst'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                                ₹{{ number_format($b2b['total_value'], 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="py-8 text-center text-slate-400 text-xs">
                                                No B2B Invoices recorded for this period.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 2. Table 7: B2C Small Summary -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-400 rounded-md text-[10px] font-black">TABLE 7</span>
                                    <span>B2C Small Supplies (Unregistered / Retail Customers)</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Consolidated Place of Supply & Tax Rate aggregation</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="py-3 px-3">Place of Supply</th>
                                        <th class="py-3 px-3">Supply Type</th>
                                        <th class="py-3 px-3 text-center">Tax Rate</th>
                                        <th class="py-3 px-3 text-center">Invoices</th>
                                        <th class="py-3 px-3 text-right">Taxable Value (₹)</th>
                                        <th class="py-3 px-3 text-right">CGST (₹)</th>
                                        <th class="py-3 px-3 text-right">SGST (₹)</th>
                                        <th class="py-3 px-3 text-right">IGST (₹)</th>
                                        <th class="py-3 px-3 text-right font-bold">Total Value (₹)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                    @forelse($gstr1Data['b2c_small_summary'] as $b2cs)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                            <td class="py-3 px-3 font-bold">{{ $b2cs['pos_state'] }} ({{ $b2cs['pos_code'] }})</td>
                                            <td class="py-3 px-3">
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold {{ $b2cs['is_intra_state'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400' }}">
                                                    {{ $b2cs['is_intra_state'] ? 'Intra-State' : 'Inter-State' }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 text-center font-bold">{{ $b2cs['tax_rate'] }}%</td>
                                            <td class="py-3 px-3 text-center">{{ $b2cs['invoice_count'] }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($b2cs['taxable_value'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($b2cs['cgst'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($b2cs['sgst'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($b2cs['igst'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                                ₹{{ number_format($b2cs['total_value'], 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="py-8 text-center text-slate-400 text-xs">
                                                No B2C Invoices recorded for this period.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 3: INPUT TAX CREDIT (ITC & PURCHASES) -->
            @if($activeTab === 'itc')
                <div class="space-y-8">
                    <!-- Vendor POs -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 rounded-md text-[10px] font-black">GSTR-2B</span>
                                    <span>Vendor Inward Supplies (Purchase Orders)</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Total PO ITC: ₹{{ number_format($itcData['total_po_itc'], 2) }}</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="py-3 px-3">PO Number</th>
                                        <th class="py-3 px-3">Vendor Bill No</th>
                                        <th class="py-3 px-3">Date</th>
                                        <th class="py-3 px-3">Supplier Name</th>
                                        <th class="py-3 px-3">Supplier GSTIN</th>
                                        <th class="py-3 px-3 text-right">Taxable (₹)</th>
                                        <th class="py-3 px-3 text-right">CGST (₹)</th>
                                        <th class="py-3 px-3 text-right">SGST (₹)</th>
                                        <th class="py-3 px-3 text-right">IGST (₹)</th>
                                        <th class="py-3 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">Claimable ITC (₹)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                    @forelse($itcData['purchase_orders'] as $po)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                            <td class="py-3 px-3 font-mono font-bold text-blue-600 dark:text-blue-400">
                                                <a href="{{ route('purchase-orders.show', $po['po_id']) }}" class="hover:underline">
                                                    {{ $po['po_number'] }}
                                                </a>
                                            </td>
                                            <td class="py-3 px-3 font-mono text-slate-600 dark:text-slate-300">{{ $po['supplier_inv_no'] }}</td>
                                            <td class="py-3 px-3 text-slate-500 whitespace-nowrap">{{ $po['supplier_inv_date'] }}</td>
                                            <td class="py-3 px-3 font-semibold">{{ $po['supplier_name'] }}</td>
                                            <td class="py-3 px-3 font-mono text-xs">{{ $po['supplier_gstin'] }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($po['taxable_value'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($po['cgst'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($po['sgst'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($po['igst'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                                ₹{{ number_format($po['itc_amount'], 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="py-8 text-center text-slate-400 text-xs">
                                                No Purchase Orders recorded for this period.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Expenses ITC -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 rounded-md text-[10px] font-black">EXPENSES</span>
                                    <span>Hardware Tools & Site Materials (ITC Eligible)</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Total Expense ITC: ₹{{ number_format($itcData['total_expense_itc'], 2) }}</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="py-3 px-3">Claim No</th>
                                        <th class="py-3 px-3">Date</th>
                                        <th class="py-3 px-3">Employee</th>
                                        <th class="py-3 px-3">Category</th>
                                        <th class="py-3 px-3">Description</th>
                                        <th class="py-3 px-3 text-right">Taxable (₹)</th>
                                        <th class="py-3 px-3 text-right">CGST (₹)</th>
                                        <th class="py-3 px-3 text-right">SGST (₹)</th>
                                        <th class="py-3 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">Claimable ITC (₹)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                    @forelse($itcData['expenses'] as $exp)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                            <td class="py-3 px-3 font-mono font-bold text-slate-800 dark:text-slate-200">{{ $exp['claim_no'] }}</td>
                                            <td class="py-3 px-3 text-slate-500 whitespace-nowrap">{{ $exp['expense_date'] }}</td>
                                            <td class="py-3 px-3 font-semibold">{{ $exp['employee_name'] }}</td>
                                            <td class="py-3 px-3 text-slate-600 dark:text-slate-300">{{ $exp['category'] }}</td>
                                            <td class="py-3 px-3 text-slate-500 max-w-xs truncate">{{ $exp['description'] }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($exp['taxable_value'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($exp['cgst'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono">₹{{ number_format($exp['sgst'], 2) }}</td>
                                            <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                                ₹{{ number_format($exp['itc_amount'], 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="py-8 text-center text-slate-400 text-xs">
                                                No eligible hardware expense claims for this period.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 4: TABLE 12 HSN SUMMARY -->
            @if($activeTab === 'hsn')
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Table 12: HSN-Wise Summary of Outward Supplies</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Item classification for CCTV cameras (8525), monitors (8528), cables (8544), and installation services (9987)</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-3 px-4">HSN / SAC Code</th>
                                    <th class="py-3 px-4">Item Description</th>
                                    <th class="py-3 px-4 text-center">UQC</th>
                                    <th class="py-3 px-4 text-center">Total Qty</th>
                                    <th class="py-3 px-4 text-center">Tax Rate</th>
                                    <th class="py-3 px-4 text-right">Taxable Value (₹)</th>
                                    <th class="py-3 px-4 text-right">CGST (₹)</th>
                                    <th class="py-3 px-4 text-right">SGST (₹)</th>
                                    <th class="py-3 px-4 text-right">IGST (₹)</th>
                                    <th class="py-3 px-4 text-right font-bold">Total Value (₹)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                @forelse($gstr1Data['hsn_summary'] as $hsn)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-3.5 px-4 font-mono font-black text-amber-600 dark:text-amber-400">
                                            {{ $hsn['hsn_code'] }}
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                            {{ $hsn['description'] }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-mono font-bold">{{ $hsn['uqc'] }}</td>
                                        <td class="py-3.5 px-4 text-center font-bold">{{ number_format($hsn['total_qty'], 1) }}</td>
                                        <td class="py-3.5 px-4 text-center font-bold">{{ $hsn['tax_rate'] }}%</td>
                                        <td class="py-3.5 px-4 text-right font-mono font-bold">₹{{ number_format($hsn['taxable_value'], 2) }}</td>
                                        <td class="py-3.5 px-4 text-right font-mono">₹{{ number_format($hsn['cgst'], 2) }}</td>
                                        <td class="py-3.5 px-4 text-right font-mono">₹{{ number_format($hsn['sgst'], 2) }}</td>
                                        <td class="py-3.5 px-4 text-right font-mono">₹{{ number_format($hsn['igst'], 2) }}</td>
                                        <td class="py-3.5 px-4 text-right font-mono font-black text-slate-900 dark:text-white">
                                            ₹{{ number_format($hsn['total_value'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="py-8 text-center text-slate-400 text-xs">
                                            No HSN line items found for this period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- TAB 5: FILING & CHALLAN HISTORY -->
            @if($activeTab === 'filings')
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">GST Return Filing & Challan Audit Log</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Track GSTR-1, GSTR-3B filings, Challan Identification Numbers (CIN), and tax receipts</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-3 px-4">Period</th>
                                    <th class="py-3 px-4 text-center">GSTR-1</th>
                                    <th class="py-3 px-4 text-center">GSTR-3B</th>
                                    <th class="py-3 px-4 text-right">Turnover (₹)</th>
                                    <th class="py-3 px-4 text-right">Tax Paid (₹)</th>
                                    <th class="py-3 px-4">Challan / CIN</th>
                                    <th class="py-3 px-4">Filing Date</th>
                                    <th class="py-3 px-4">Filed By</th>
                                    <th class="py-3 px-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                @forelse($filingHistory as $hist)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            {{ $hist->formatted_period }} ({{ $hist->period }})
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $hist->gstr1_status === 'filed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400' }}">
                                                {{ $hist->gstr1_status }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $hist->gstr3b_status === 'filed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400' }}">
                                                {{ $hist->gstr3b_status }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono">₹{{ number_format($hist->total_turnover, 2) }}</td>
                                        <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                            ₹{{ number_format($hist->tax_paid, 2) }}
                                        </td>
                                        <td class="py-3.5 px-4 font-mono text-[11px]">
                                            <div>{{ $hist->challan_no ?: '—' }}</div>
                                            @if($hist->cin_number)
                                                <div class="text-[10px] text-slate-400 font-normal">CIN: {{ $hist->cin_number }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">
                                            {{ $hist->filing_date?->format('d-m-Y') ?: '—' }}
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-600 dark:text-slate-400">
                                            {{ $hist->filer?->name ?: 'System Admin' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a href="{{ route('finance.gst.index', ['month' => $hist->period]) }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                                View →
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-8 text-center text-slate-400 text-xs">
                                            No past filing records logged yet. Click "Record Filing & Challan" to log your returns.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL 1: COMPANY GST & LEGAL PROFILE -->
    <x-modal name="company-gst-modal" :show="false" maxWidth="2xl">
        <form method="POST" action="{{ route('finance.gst.settings.update') }}" class="p-6 space-y-5">
            @csrf
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="p-2 bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Company GST & Business Profile</h3>
                </div>
                <button type="button" @click="$dispatch('close-modal', 'company-gst-modal')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Company Trade Name *</label>
                    <input type="text" name="company_trade_name" value="{{ old('company_trade_name', $companyGst['trade_name']) }}" required
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-semibold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Legal Registered Name</label>
                    <input type="text" name="company_legal_name" value="{{ old('company_legal_name', $companyGst['legal_name']) }}"
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-semibold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Company GSTIN (15 Digits) *</label>
                    <input type="text" name="company_gstin" value="{{ old('company_gstin', $companyGst['gstin']) }}" maxlength="15" required
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono font-bold uppercase">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Company PAN</label>
                    <input type="text" name="company_pan" value="{{ old('company_pan', $companyGst['pan']) }}" maxlength="10"
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono font-bold uppercase">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Principal State *</label>
                    <input type="text" name="company_state" value="{{ old('company_state', $companyGst['state']) }}" required
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-semibold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">State Code (2 Digits) *</label>
                    <input type="text" name="company_state_code" value="{{ old('company_state_code', $companyGst['state_code']) }}" maxlength="2" required
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono font-bold">
                </div>

                <div class="md:col-span-2">
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Registered Business Address</label>
                    <textarea name="company_address" rows="2"
                              class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-medium">{{ old('company_address', $companyGst['address']) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="$dispatch('close-modal', 'company-gst-modal')"
                        class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-md transition">
                    Save GST Profile
                </button>
            </div>
        </form>
    </x-modal>

    <!-- MODAL 2: RECORD RETURN FILING & CHALLAN -->
    <x-modal name="record-filing-modal" :show="false" maxWidth="2xl">
        <form method="POST" action="{{ route('finance.gst.filing.record') }}" class="p-6 space-y-5">
            @csrf
            <input type="hidden" name="period" value="{{ $selectedMonth }}">

            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="p-2 bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Record Filing & Payment Challan</h3>
                        <p class="text-xs text-slate-500">Period: {{ Carbon\Carbon::parse($selectedMonth.'-01')->format('F Y') }}</p>
                    </div>
                </div>
                <button type="button" @click="$dispatch('close-modal', 'record-filing-modal')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">GSTR-1 Status *</label>
                    <select name="gstr1_status" required class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-semibold">
                        <option value="pending" {{ ($filing?->gstr1_status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="reconciled" {{ ($filing?->gstr1_status ?? '') === 'reconciled' ? 'selected' : '' }}>Reconciled (Ready)</option>
                        <option value="filed" {{ ($filing?->gstr1_status ?? '') === 'filed' ? 'selected' : '' }}>Filed on Portal</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">GSTR-3B Status *</label>
                    <select name="gstr3b_status" required class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-semibold">
                        <option value="pending" {{ ($filing?->gstr3b_status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="reconciled" {{ ($filing?->gstr3b_status ?? '') === 'reconciled' ? 'selected' : '' }}>Reconciled (Ready)</option>
                        <option value="filed" {{ ($filing?->gstr3b_status ?? '') === 'filed' ? 'selected' : '' }}>Filed & Discharged</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Cash Tax Paid (₹)</label>
                    <input type="number" step="0.01" name="tax_paid" value="{{ old('tax_paid', $filing?->tax_paid ?? $gstr3bData['net_tax_payable_cash']['total']) }}"
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono font-bold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Mode</label>
                    <select name="payment_mode" class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-semibold">
                        <option value="online_portal">GST Portal E-Payment (Net Banking/UPI)</option>
                        <option value="neft_rtgs">NEFT / RTGS Challan</option>
                        <option value="over_counter">Over the Counter (Bank Counter)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">GST PMT-06 Challan Number</label>
                    <input type="text" name="challan_no" value="{{ old('challan_no', $filing?->challan_no) }}" placeholder="e.g. 2409330001234"
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono font-semibold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Challan Identification Number (CIN)</label>
                    <input type="text" name="cin_number" value="{{ old('cin_number', $filing?->cin_number) }}" placeholder="17 Digit CIN number from bank"
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono font-semibold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Filing / Payment Date</label>
                    <input type="date" name="filing_date" value="{{ old('filing_date', $filing?->filing_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-semibold">
                </div>

                <div class="md:col-span-2">
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Filing Audit Notes</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Filed by CA Sharma & Associates. ARN: AA3309260012345"
                              class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-medium">{{ old('notes', $filing?->notes) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="$dispatch('close-modal', 'record-filing-modal')"
                        class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition">
                    Save Filing Record
                </button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
