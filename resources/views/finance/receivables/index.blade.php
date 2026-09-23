<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg shadow-indigo-500/20 text-white">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-black tracking-tight text-slate-900 dark:text-white sm:text-2xl">
                            Accounts Receivable & Debtors Aging
                        </h2>
                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-800 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-800 rounded-full">
                            Dunning Engine
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Debtors aging analysis (0-30, 31-60, 61-90, 90+ days), DSO metrics & 1-click WhatsApp/SMS dunning with Razorpay links
                    </p>
                </div>
            </div>

            <!-- Action Buttons: Bulk Sweep, Export CSV, Export PDF -->
            <div class="flex flex-wrap items-center gap-2.5">
                <form action="{{ route('finance.receivables.sweep') }}" method="POST" onsubmit="return confirm('Trigger automated payment reminder sweep for all overdue invoices? (Invoices notified within last 3 days will be safely skipped)');">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-3.5 py-2 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Run Dunning Sweep</span>
                    </button>
                </form>

                <a href="{{ route('finance.receivables.export-csv') }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>CSV Export</span>
                </a>

                <a href="{{ route('finance.receivables.export-pdf') }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-900 hover:bg-black dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold rounded-xl shadow-md transition">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Download Aging PDF</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6" x-data="{ activeTab: 'debtors', reminderModal: false, reminderInvoice: null, reminderCustomer: '', reminderBalance: '', reminderRoute: '', reminderChannel: 'all' }">
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

        @if (session('error'))
            <div class="p-4 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/80 rounded-2xl flex items-center justify-between shadow-sm animate-in fade-in duration-200">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-rose-100 dark:bg-rose-900/80 text-rose-700 dark:text-rose-300 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </span>
                    <span class="text-xs md:text-sm font-bold text-rose-900 dark:text-rose-200">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- KPI Metric Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            <!-- 1. Total Outstanding -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-indigo-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Receivables</span>
                    <span class="p-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-xl font-black text-slate-900 dark:text-white mt-2">
                    ₹{{ number_format($summary['total_outstanding'], 2) }}
                </div>
                <div class="mt-2 text-[11px] font-medium text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>{{ $summary['total_invoices_count'] }} Unpaid Invoices</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $summary['total_debtors_count'] }} Debtors</span>
                </div>
            </div>

            <!-- 2. Current (0-30 Days) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-emerald-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">0–30 Days</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Normal</span>
                </div>
                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
                    ₹{{ number_format($summary['current_0_30'], 2) }}
                </div>
                <div class="mt-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    {{ $summary['total_outstanding'] > 0 ? round(($summary['current_0_30'] / $summary['total_outstanding']) * 100, 1) : 0 }}% of receivables
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
                    {{ $summary['total_outstanding'] > 0 ? round(($summary['overdue_31_60'] / $summary['total_outstanding']) * 100, 1) : 0 }}% of receivables
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
                    {{ $summary['total_outstanding'] > 0 ? round(($summary['critical_61_90'] / $summary['total_outstanding']) * 100, 1) : 0 }}% of receivables
                </div>
            </div>

            <!-- 5. High Risk (90+ Days) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-rose-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">90+ Days</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">Bad Debt Risk</span>
                </div>
                <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-2">
                    ₹{{ number_format($summary['high_risk_90_plus'], 2) }}
                </div>
                <div class="mt-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    {{ $summary['total_outstanding'] > 0 ? round(($summary['high_risk_90_plus'] / $summary['total_outstanding']) * 100, 1) : 0 }}% of receivables
                </div>
            </div>

            <!-- 6. Days Sales Outstanding (DSO) -->
            <div class="bg-gradient-to-br from-indigo-900 to-slate-900 text-white border border-indigo-800/50 rounded-3xl p-5 shadow-sm relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-indigo-300 uppercase tracking-wider">DSO (Collection Speed)</span>
                    <span class="p-1.5 bg-indigo-800/60 text-indigo-200 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-white mt-2">
                    {{ $summary['dso_days'] }} <span class="text-xs font-normal text-indigo-300">Days</span>
                </div>
                <div class="mt-2 text-[11px] font-medium text-indigo-200/80">
                    {{ $summary['dso_days'] <= 45 ? '✓ Healthy cash velocity' : ($summary['dso_days'] <= 75 ? '⚠ Moderate collection delay' : '🚨 High collection drag') }}
                </div>
            </div>
        </div>

        <!-- Tab Navigation & Filters -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <button @click="activeTab = 'debtors'"
                        :class="activeTab === 'debtors' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-4 py-2 text-xs font-bold rounded-xl transition">
                    Debtors Aging Matrix ({{ count($debtors) }} Clients)
                </button>
                <button @click="activeTab = 'overdue'"
                        :class="activeTab === 'overdue' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                    <span>Overdue Invoices & Dunning Dispatcher</span>
                    @if($summary['overdue_invoices_count'] > 0)
                        <span class="px-1.5 py-0.5 text-[10px] font-black rounded-full bg-rose-500 text-white">{{ $summary['overdue_invoices_count'] }}</span>
                    @endif
                </button>
            </div>
        </div>

        <!-- TAB 1: Debtors Aging Matrix Table -->
        <div x-show="activeTab === 'debtors'" class="space-y-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Customer Receivables & Aging Buckets</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Chronological debtor breakdown across standard accounting aging intervals</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100/50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase font-black tracking-wider text-[10px]">
                                <th class="p-3.5">Customer / Organization</th>
                                <th class="p-3.5">Contact Details</th>
                                <th class="p-3.5 text-center">Open Bills</th>
                                <th class="p-3.5 text-right">0–30 Days</th>
                                <th class="p-3.5 text-right">31–60 Days</th>
                                <th class="p-3.5 text-right">61–90 Days</th>
                                <th class="p-3.5 text-right">90+ Days</th>
                                <th class="p-3.5 text-right font-black text-slate-900 dark:text-white">Total Due</th>
                                <th class="p-3.5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                            @forelse($debtors as $debtor)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                    <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $debtor['customer_name'] }}</span>
                                            @if($debtor['days_90_plus'] > 0)
                                                <span class="px-1.5 py-0.5 text-[9px] font-black rounded-md bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">High Risk</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-3.5 text-slate-600 dark:text-slate-400">
                                        <div class="flex flex-col">
                                            <span>{{ $debtor['phone'] ?: 'No Phone' }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $debtor['email'] ?: 'No Email' }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ $debtor['unpaid_invoices_count'] }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                        {{ $debtor['days_0_30'] > 0 ? '₹' . number_format($debtor['days_0_30'], 2) : '-' }}
                                    </td>
                                    <td class="p-3.5 text-right font-semibold text-amber-600 dark:text-amber-400">
                                        {{ $debtor['days_31_60'] > 0 ? '₹' . number_format($debtor['days_31_60'], 2) : '-' }}
                                    </td>
                                    <td class="p-3.5 text-right font-semibold text-orange-600 dark:text-orange-400">
                                        {{ $debtor['days_61_90'] > 0 ? '₹' . number_format($debtor['days_61_90'], 2) : '-' }}
                                    </td>
                                    <td class="p-3.5 text-right font-semibold text-rose-600 dark:text-rose-400">
                                        {{ $debtor['days_90_plus'] > 0 ? '₹' . number_format($debtor['days_90_plus'], 2) : '-' }}
                                    </td>
                                    <td class="p-3.5 text-right font-black text-slate-900 dark:text-white">
                                        ₹{{ number_format($debtor['total_due'], 2) }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            @if($debtor['lead_id'])
                                                <a href="{{ route('finance.receivables.customer', $debtor['lead_id']) }}"
                                                   class="px-2.5 py-1 text-[11px] font-bold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-lg transition"
                                                   title="View Statement of Account Ledger">
                                                    Ledger
                                                </a>
                                                <a href="{{ route('finance.receivables.customer.pdf', $debtor['lead_id']) }}"
                                                   class="p-1 text-slate-500 hover:text-slate-900 dark:hover:text-white transition"
                                                   title="Download Statement PDF">
                                                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                </a>
                                            @else
                                                <span class="text-[10px] text-slate-400 italic">No Lead Profile</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span class="font-bold text-sm text-slate-900 dark:text-white">Zero Outstanding Receivables</span>
                                            <p class="text-xs">All customer invoices are fully settled and paid up to date.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: Overdue Invoices & Dunning Dispatcher -->
        <div x-show="activeTab === 'overdue'" class="space-y-4" style="display: none;">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-800/40">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Overdue Invoices & 1-Click Dunning Dispatcher</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Direct WhatsApp, SMS & Email automated payment reminders with instant online payment checkout</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100/50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase font-black tracking-wider text-[10px]">
                                <th class="p-3.5">Invoice #</th>
                                <th class="p-3.5">Customer</th>
                                <th class="p-3.5">Issue Date</th>
                                <th class="p-3.5">Due Date</th>
                                <th class="p-3.5 text-center">Days Overdue</th>
                                <th class="p-3.5 text-right">Invoice Total</th>
                                <th class="p-3.5 text-right font-black text-rose-600 dark:text-rose-400">Balance Due</th>
                                <th class="p-3.5">Last Reminder</th>
                                <th class="p-3.5 text-center">Send Dunning</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                            @forelse($overdueInvoices as $inv)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                    <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                                        <a href="{{ route('invoices.show', $inv['invoice_id']) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                            {{ $inv['invoice_no'] }}
                                        </a>
                                    </td>
                                    <td class="p-3.5 text-slate-800 dark:text-slate-200 font-semibold">
                                        {{ $inv['customer_name'] }}
                                        @if(!empty($inv['customer_phone']) && $inv['customer_phone'] !== 'N/A')
                                            <div class="text-[10px] text-slate-400 font-normal">{{ $inv['customer_phone'] }}</div>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-slate-600 dark:text-slate-400">
                                        {{ $inv['invoice_date'] ?: '-' }}
                                    </td>
                                    <td class="p-3.5 text-slate-600 dark:text-slate-400">
                                        {{ $inv['due_date'] ?: '-' }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <span class="px-2 py-0.5 text-xs font-black rounded-full {{ $inv['bucket_badge_class'] }}">
                                            {{ $inv['days_overdue'] }}d Overdue
                                        </span>
                                    </td>
                                    <td class="p-3.5 text-right font-medium text-slate-700 dark:text-slate-300">
                                        ₹{{ number_format($inv['total_amount'], 2) }}
                                    </td>
                                    <td class="p-3.5 text-right font-black text-rose-600 dark:text-rose-400">
                                        ₹{{ number_format($inv['balance_due'], 2) }}
                                    </td>
                                    <td class="p-3.5 text-slate-500 dark:text-slate-400 text-[11px]">
                                        {{ $inv['last_reminder_at'] }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <!-- 1-Click Trigger Modal -->
                                            <button @click="reminderModal = true; reminderInvoice = '{{ $inv['invoice_no'] }}'; reminderCustomer = '{{ addslashes($inv['customer_name']) }}'; reminderBalance = '₹{{ number_format($inv['balance_due'], 2) }}'; reminderRoute = '{{ route('finance.receivables.reminder', $inv['invoice_id']) }}';"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] rounded-lg shadow-xs transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                <span>Send Reminder</span>
                                            </button>

                                            <!-- Direct Razorpay Payment Link -->
                                            <a href="{{ $inv['checkout_url'] }}" target="_blank"
                                               class="p-1 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 transition"
                                               title="Open Online Payment Portal">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span class="font-bold text-sm text-slate-900 dark:text-white">No Overdue Invoices Found</span>
                                            <p class="text-xs">Great job! All customer accounts are either settled or within their agreed credit terms.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(is_object($overdueInvoices) && method_exists($overdueInvoices, 'links'))
                    <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                        {{ $overdueInvoices->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- 1-Click Reminder Modal -->
        <div x-show="reminderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" style="display: none;"
             @keydown.escape.window="reminderModal = false">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4"
                 @click.away="reminderModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">Send Dunning Reminder</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="reminderInvoice"></p>
                        </div>
                    </div>
                    <button @click="reminderModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/50 p-3.5 rounded-2xl space-y-1 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Customer:</span>
                        <span class="font-bold text-slate-900 dark:text-white" x-text="reminderCustomer"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Outstanding Balance:</span>
                        <span class="font-black text-rose-600 dark:text-rose-400" x-text="reminderBalance"></span>
                    </div>
                </div>

                <form :action="reminderRoute" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Notification Channel</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer text-xs font-semibold">
                                <input type="radio" name="channel" value="all" checked class="text-indigo-600 focus:ring-indigo-500">
                                <span>🚀 All (WhatsApp + SMS + Email)</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer text-xs font-semibold">
                                <input type="radio" name="channel" value="whatsapp" class="text-indigo-600 focus:ring-indigo-500">
                                <span>💬 WhatsApp Direct</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer text-xs font-semibold">
                                <input type="radio" name="channel" value="sms" class="text-indigo-600 focus:ring-indigo-500">
                                <span>📱 SMS Text</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer text-xs font-semibold">
                                <input type="radio" name="channel" value="email" class="text-indigo-600 focus:ring-indigo-500">
                                <span>✉️ Email Notice</span>
                            </label>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-500 dark:text-slate-400 italic">
                        * The reminder will contain a secure, 1-click Razorpay payment link allowing the customer to settle the balance instantly via UPI, NetBanking, or Credit Card.
                    </p>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="reminderModal = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs rounded-xl shadow-md transition">
                            Dispatch Reminder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
