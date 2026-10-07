<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl shadow-lg shadow-rose-500/20 text-white">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-black tracking-tight text-slate-900 dark:text-white sm:text-2xl">
                            Accounts Payable & 3-Way Matching
                        </h2>
                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-300 dark:border-rose-800 rounded-full">
                            Procurement AP Hub
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Supplier AP aging (0-30, 31-60, 61-90, 90+ days), DPO metrics & 3-Way verification (PO vs GRN Inward vs Vendor Bill)
                    </p>
                </div>
            </div>

            <!-- Action Buttons: Record Payment, CSVs, PDF -->
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" @click="$dispatch('open-pay-modal', { supplierId: '', poId: '', amount: '', supplierName: '' })"
                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Record Disbursement</span>
                </button>

                <a href="{{ route('finance.payables.export-csv') }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>AP Aging CSV</span>
                </a>

                <a href="{{ route('finance.payables.export-three-way-csv') }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>3-Way Match CSV</span>
                </a>

                <a href="{{ route('finance.payables.export-pdf') }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Download AP PDF</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6" x-data="{ 
        activeTab: 'aging', 
        payModal: false, 
        modalSupplierId: '', 
        modalPoId: '', 
        modalAmount: '', 
        modalSupplierName: '',
        modalPoNumber: ''
    }"
    @open-pay-modal.window="
        payModal = true;
        modalSupplierId = $event.detail.supplierId || '';
        modalPoId = $event.detail.poId || '';
        modalAmount = $event.detail.amount || '';
        modalSupplierName = $event.detail.supplierName || '';
        modalPoNumber = $event.detail.poNumber || '';
    ">
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

        @if ($errors->any())
            <div class="p-4 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/80 rounded-2xl flex items-center justify-between shadow-sm animate-in fade-in duration-200">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-rose-100 dark:bg-rose-900/80 text-rose-700 dark:text-rose-300 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </span>
                    <div class="text-xs md:text-sm font-bold text-rose-900 dark:text-rose-200">
                        @foreach ($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Finance Category Sub-Navigation --}}
        <x-finance-subnav active="payables" />

        <!-- KPI Metric Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            <!-- 1. Total Outstanding Payables -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-rose-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Payables</span>
                    <span class="p-1.5 bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    </span>
                </div>
                <div class="text-xl font-black text-slate-900 dark:text-white mt-2">
                    ₹{{ number_format($summary['total_payable_balance'], 2) }}
                </div>
                <div class="mt-2 text-[11px] font-medium text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>{{ $summary['unpaid_pos_count'] }} Open POs</span>
                    <span class="font-bold text-rose-600 dark:text-rose-400">{{ $summary['suppliers_count'] }} Vendors</span>
                </div>
            </div>

            <!-- 2. Current (0-30 Days) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-blue-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">0–30 Days (Current)</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">Normal</span>
                </div>
                <div class="text-xl font-black text-blue-600 dark:text-blue-400 mt-2">
                    ₹{{ number_format($summary['current_0_30'], 2) }}
                </div>
                <div class="mt-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    {{ $summary['total_payable_balance'] > 0 ? round(($summary['current_0_30'] / $summary['total_payable_balance']) * 100, 1) : 0 }}% of Total AP
                </div>
            </div>

            <!-- 3. Overdue (31-60 Days) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-amber-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">31–60 Days</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">Overdue</span>
                </div>
                <div class="text-xl font-black text-amber-600 dark:text-amber-400 mt-2">
                    ₹{{ number_format($summary['overdue_31_60'], 2) }}
                </div>
                <div class="mt-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    {{ $summary['total_payable_balance'] > 0 ? round(($summary['overdue_31_60'] / $summary['total_payable_balance']) * 100, 1) : 0 }}% of Total AP
                </div>
            </div>

            <!-- 4. Critical (61-90 Days) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-orange-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">61–90 Days</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-300">Critical</span>
                </div>
                <div class="text-xl font-black text-orange-600 dark:text-orange-400 mt-2">
                    ₹{{ number_format($summary['critical_61_90'], 2) }}
                </div>
                <div class="mt-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    {{ $summary['total_payable_balance'] > 0 ? round(($summary['critical_61_90'] / $summary['total_payable_balance']) * 100, 1) : 0 }}% of Total AP
                </div>
            </div>

            <!-- 5. 90+ Days Critical -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-rose-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">90+ Days</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">High Risk</span>
                </div>
                <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-2">
                    ₹{{ number_format($summary['high_risk_90_plus'], 2) }}
                </div>
                <div class="mt-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    {{ $summary['total_payable_balance'] > 0 ? round(($summary['high_risk_90_plus'] / $summary['total_payable_balance']) * 100, 1) : 0 }}% of Total AP
                </div>
            </div>

            <!-- 6. Days Payable Outstanding (DPO) & 3-Way Match Rate -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-indigo-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">DPO & 3-Way Health</span>
                    <span class="p-1.5 bg-indigo-50 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-2 flex items-baseline gap-1">
                    {{ $summary['dpo_days'] }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">Days DPO</span>
                </div>
                <div class="mt-2 text-[11px] font-medium text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                    <span>✓ {{ $summary['three_way_match_pass_rate'] }}% 3-Way Verified</span>
                </div>
            </div>
        </div>

        <!-- Tab Controls & Filter Bar -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-3">
            <div class="flex flex-wrap items-center gap-2">
                <button @click="activeTab = 'aging'"
                        :class="activeTab === 'aging' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-4 py-2 text-xs font-bold rounded-xl transition">
                    Supplier AP Aging Matrix ({{ count($suppliers) }} Vendors)
                </button>
                <button @click="activeTab = 'three_way'"
                        :class="activeTab === 'three_way' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                    <span>3-Way Matching Inspector (PO vs GRN vs Bill)</span>
                    <span class="px-1.5 py-0.5 text-[10px] font-black rounded-full bg-emerald-500 text-white">{{ $summary['three_way_stats']['matched'] }}/{{ $summary['total_pos_count'] }}</span>
                </button>
                <button @click="activeTab = 'disbursements'"
                        :class="activeTab === 'disbursements' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                    <span>Payment Scheduler & Payouts</span>
                    @if(count($disbursements) > 0)
                        <span class="px-1.5 py-0.5 text-[10px] font-black rounded-full bg-amber-500 text-white">{{ count($disbursements) }}</span>
                    @endif
                </button>
            </div>
        </div>

        <!-- TAB 1: Supplier AP Aging Matrix Table -->
        <div x-show="activeTab === 'aging'" class="space-y-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Vendor Aging & Credit Terms Matrix</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Supplier outstanding payables categorized across standard accounting aging intervals</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100/50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase font-black tracking-wider text-[10px]">
                                <th class="p-3.5">Supplier / Company</th>
                                <th class="p-3.5">Contact & GSTIN</th>
                                <th class="p-3.5 text-center">Unpaid POs</th>
                                <th class="p-3.5 text-right">0–30 Days</th>
                                <th class="p-3.5 text-right">31–60 Days</th>
                                <th class="p-3.5 text-right">61–90 Days</th>
                                <th class="p-3.5 text-right">90+ Days</th>
                                <th class="p-3.5 text-right font-black text-slate-900 dark:text-white">Net Payable</th>
                                <th class="p-3.5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                            @forelse($suppliers as $s)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                    <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $s['supplier_name'] }}</span>
                                            @if($s['days_90_plus'] > 0)
                                                <span class="px-1.5 py-0.5 text-[9px] font-black rounded-md bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">Overdue Risk</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-3.5 text-slate-600 dark:text-slate-400">
                                        <div class="flex flex-col">
                                            <span>{{ $s['phone'] }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">GST: {{ $s['gst_number'] }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ $s['unpaid_po_count'] }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 text-right font-semibold text-blue-600 dark:text-blue-400">
                                        {{ $s['days_0_30'] > 0 ? '₹' . number_format($s['days_0_30'], 2) : '-' }}
                                    </td>
                                    <td class="p-3.5 text-right font-semibold text-amber-600 dark:text-amber-400">
                                        {{ $s['days_31_60'] > 0 ? '₹' . number_format($s['days_31_60'], 2) : '-' }}
                                    </td>
                                    <td class="p-3.5 text-right font-semibold text-orange-600 dark:text-orange-400">
                                        {{ $s['days_61_90'] > 0 ? '₹' . number_format($s['days_61_90'], 2) : '-' }}
                                    </td>
                                    <td class="p-3.5 text-right font-semibold text-rose-600 dark:text-rose-400">
                                        {{ $s['days_90_plus'] > 0 ? '₹' . number_format($s['days_90_plus'], 2) : '-' }}
                                    </td>
                                    <td class="p-3.5 text-right font-black text-slate-900 dark:text-white">
                                        ₹{{ number_format($s['total_due'], 2) }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('finance.payables.supplier', $s['supplier_id']) }}"
                                               class="px-2.5 py-1 text-[11px] font-bold bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900 text-rose-700 dark:text-rose-300 rounded-lg transition"
                                               title="View Statement of Account Ledger">
                                                Ledger
                                            </a>
                                            <a href="{{ route('finance.payables.supplier.pdf', $s['supplier_id']) }}"
                                               class="p-1 text-slate-500 hover:text-slate-900 dark:hover:text-white transition"
                                               title="Download Statement PDF">
                                                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            </a>
                                            <button type="button" @click="$dispatch('open-pay-modal', { supplierId: '{{ $s['supplier_id'] }}', supplierName: '{{ addslashes($s['supplier_name']) }}', amount: '{{ $s['total_due'] }}' })"
                                                    class="p-1 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 transition"
                                                    title="Disburse Payout">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span class="font-bold text-sm text-slate-900 dark:text-white">Zero Outstanding Accounts Payable</span>
                                            <p class="text-xs">All vendor procurement purchase orders have been fully paid and settled.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: 3-Way Matching Inspector Table -->
        <div x-show="activeTab === 'three_way'" class="space-y-4" style="display: none;">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-800/40">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">3-Way Matching Verification Ledger</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Reconciling Purchase Order (PO), Warehouse Goods Receipt (GRN), and Vendor Tax Bill</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100/50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase font-black tracking-wider text-[10px]">
                                <th class="p-3.5">PO #</th>
                                <th class="p-3.5">Supplier</th>
                                <th class="p-3.5 text-center">Ordered Qty</th>
                                <th class="p-3.5 text-center">GRN Received</th>
                                <th class="p-3.5">Supplier Bill #</th>
                                <th class="p-3.5 text-right">PO Total</th>
                                <th class="p-3.5 text-center">3-Way Match Status</th>
                                <th class="p-3.5">Audit Assessment</th>
                                <th class="p-3.5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                            @forelse($threeWayAudit as $item)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                    <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                                        <a href="{{ route('purchase-orders.show', $item['po_id']) }}" class="text-rose-600 dark:text-rose-400 hover:underline">
                                            {{ $item['po_number'] }}
                                        </a>
                                        <div class="text-[10px] text-slate-400 font-normal">{{ $item['order_date'] }}</div>
                                    </td>
                                    <td class="p-3.5 text-slate-800 dark:text-slate-200 font-semibold">
                                        {{ $item['supplier_name'] }}
                                    </td>
                                    <td class="p-3.5 text-center font-bold text-slate-900 dark:text-white">
                                        {{ $item['total_ordered_qty'] }} units
                                    </td>
                                    <td class="p-3.5 text-center font-bold {{ $item['total_received_qty'] >= $item['total_ordered_qty'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ $item['total_received_qty'] }} units
                                    </td>
                                    <td class="p-3.5 text-slate-700 dark:text-slate-300">
                                        @if($item['has_bill'])
                                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $item['bill_number'] }}</span>
                                            @if($item['bill_date'])
                                                <div class="text-[10px] text-slate-400">{{ $item['bill_date'] }}</div>
                                            @endif
                                        @else
                                            <span class="text-amber-600 dark:text-amber-400 font-semibold">Bill Pending</span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-right font-black text-slate-900 dark:text-white">
                                        ₹{{ number_format($item['total_amount'], 2) }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <span class="px-2.5 py-1 text-[10px] font-black rounded-full border {{ $item['match_badge_class'] }}">
                                            {{ $item['match_label'] }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 text-slate-600 dark:text-slate-400 max-w-xs text-[11px]">
                                        {{ $item['match_description'] }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <a href="{{ route('purchase-orders.show', $item['po_id']) }}"
                                           class="px-2.5 py-1 text-[11px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg transition">
                                            Inspect
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                        No purchase orders found for 3-way matching inspection.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: Pending Disbursements & Payment Scheduler Table -->
        <div x-show="activeTab === 'disbursements'" class="space-y-4" style="display: none;">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Pending Vendor Payment Disbursements</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Scheduled payouts due for procurement orders with 1-click disbursement recording</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100/50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase font-black tracking-wider text-[10px]">
                                <th class="p-3.5">PO #</th>
                                <th class="p-3.5">Supplier Name</th>
                                <th class="p-3.5">Bill Reference</th>
                                <th class="p-3.5">Due Date</th>
                                <th class="p-3.5 text-center">Aging Status</th>
                                <th class="p-3.5 text-right">PO Total</th>
                                <th class="p-3.5 text-right font-black text-rose-600 dark:text-rose-400">Balance Payable</th>
                                <th class="p-3.5 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                            @forelse($disbursements as $d)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                    <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                                        <a href="{{ route('purchase-orders.show', $d['po_id']) }}" class="text-rose-600 dark:text-rose-400 hover:underline">
                                            {{ $d['po_number'] }}
                                        </a>
                                    </td>
                                    <td class="p-3.5 text-slate-800 dark:text-slate-200 font-semibold">
                                        {{ $d['supplier_name'] }}
                                        @if($d['supplier_phone'] && $d['supplier_phone'] !== 'N/A')
                                            <div class="text-[10px] text-slate-400 font-normal">{{ $d['supplier_phone'] }}</div>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-slate-700 dark:text-slate-300">
                                        {{ $d['bill_number'] }}
                                    </td>
                                    <td class="p-3.5 text-slate-600 dark:text-slate-400">
                                        {{ $d['due_date'] }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        @if($d['is_overdue'])
                                            <span class="px-2 py-0.5 text-xs font-black rounded-full bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                                {{ $d['days_overdue'] }}d Overdue
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                                                Within Credit Terms
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-right font-medium text-slate-700 dark:text-slate-300">
                                        ₹{{ number_format($d['total_amount'], 2) }}
                                    </td>
                                    <td class="p-3.5 text-right font-black text-rose-600 dark:text-rose-400">
                                        ₹{{ number_format($d['balance_due'], 2) }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <button type="button" @click="$dispatch('open-pay-modal', { supplierId: '{{ $d['supplier_id'] }}', poId: '{{ $d['po_id'] }}', supplierName: '{{ addslashes($d['supplier_name']) }}', poNumber: '{{ $d['po_number'] }}', amount: '{{ $d['balance_due'] }}' })"
                                                class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Disburse Pay</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                        No pending vendor disbursements due at this time.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 1-Click Vendor Payment Disbursement Modal -->
        <div x-show="payModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" style="display: none;"
             @keydown.escape.window="payModal = false">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4"
                 @click.away="payModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">Record Vendor Disbursement</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="modalSupplierName ? (modalSupplierName + (modalPoNumber ? ' (' + modalPoNumber + ')' : '')) : 'Supplier Payment'"></p>
                        </div>
                    </div>
                    <button @click="payModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('finance.payables.payment') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="purchase_order_id" :value="modalPoId">

                    <template x-if="!modalSupplierId">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Select Supplier / Vendor *</label>
                            <select name="supplier_id" required
                                    class="w-full px-3.5 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">-- Choose Vendor / Supplier --</option>
                                @foreach($allSuppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->company_name ?: $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </template>
                    <template x-if="modalSupplierId">
                        <input type="hidden" name="supplier_id" :value="modalSupplierId">
                    </template>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Disbursement Amount (₹) *</label>
                            <input type="number" step="0.01" min="0.01" name="amount" :value="modalAmount" required
                                   class="w-full px-3.5 py-2 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Payment Date *</label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                   class="w-full px-3.5 py-2 text-xs font-medium rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Payment Method *</label>
                            <select name="payment_method" required
                                    class="w-full px-3.5 py-2 text-xs font-medium rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="neft_rtgs">NEFT / RTGS (Bank Transfer)</option>
                                <option value="upi">UPI (GPay / PhonePe)</option>
                                <option value="cheque">Bank Cheque</option>
                                <option value="bank_transfer">Net Banking Transfer</option>
                                <option value="cash">Cash Settlement</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Bank Ref / UTR / Cheque #</label>
                            <input type="text" name="transaction_reference" placeholder="e.g. UTR12345678"
                                   class="w-full px-3.5 py-2 text-xs font-medium rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Payment Remarks / Notes</label>
                        <textarea name="notes" rows="2" placeholder="Optional settlement notes..."
                                  class="w-full px-3.5 py-2 text-xs font-medium rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="payModal = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-md transition">
                            Confirm Disbursement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
