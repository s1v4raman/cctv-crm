<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                    Accounts Receivable &amp; Debtors Aging
                </h2>
            </div>

            <!-- Action Buttons: Bulk Sweep, Export CSV, Export PDF -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('finance.receivables.export-csv') }}" class="btn-secondary">
                    Export CSV
                </a>

                <a href="{{ route('finance.receivables.export-pdf') }}" class="btn-secondary">
                    Aging PDF
                </a>

                <form action="{{ route('finance.receivables.sweep') }}" method="POST" data-confirm="Trigger payment reminder sweep for all overdue invoices? (Invoices notified within last 3 days will be skipped)" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary">
                        Dunning Sweep
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 1024px) {
            .stat-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); }
        }

        .stat-card {
            background: #ffffff;
            border-radius: 4px;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
        }
        html.dark .stat-card {
            background: #0f172a;
            border-color: var(--text-main);
        }
        .stat-label { font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 0.25rem; }
        html.dark .stat-label { color: var(--text-muted); }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: var(--text-main); line-height: 1; }
        html.dark .stat-value { color: #ffffff; }

        .pg-card {
            background: #ffffff;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        html.dark .pg-card {
            background: #0f172a;
            border-color: var(--text-main);
        }
    </style>

    <div class="py-6 bg-slate-50 dark:bg-[#060913] min-h-screen" x-data="{ activeTab: 'debtors', reminderModal: false, reminderInvoice: null, reminderCustomer: '', reminderBalance: '', reminderRoute: '', reminderChannel: 'all' }">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm font-medium rounded" style="border-radius: 4px;">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 dark:bg-rose-950/50 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm font-medium rounded" style="border-radius: 4px;">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Finance Category Sub-Navigation --}}
            <x-finance-subnav active="receivables" />

            <!-- KPI Metric Cards Grid -->
            <div class="stat-grid">
                <!-- 1. Total Outstanding -->
                <div class="stat-card">
                    <div class="stat-label">Total Receivables</div>
                    <div class="stat-value text-blue-600 dark:text-blue-400">
                        ₹{{ number_format($summary['total_outstanding'], 2) }}
                    </div>
                    <div class="mt-2 text-xs text-slate-500">
                        {{ $summary['total_invoices_count'] }} Invoices • {{ $summary['total_debtors_count'] }} Debtors
                    </div>
                </div>

                <!-- 2. Current (0-30 Days) -->
                <div class="stat-card">
                    <div class="stat-label">0–30 Days</div>
                    <div class="stat-value text-emerald-600 dark:text-emerald-400">
                        ₹{{ number_format($summary['current_0_30'], 2) }}
                    </div>
                    <div class="mt-2 text-xs text-slate-500">
                        {{ $summary['total_outstanding'] > 0 ? round(($summary['current_0_30'] / $summary['total_outstanding']) * 100, 1) : 0 }}% of total
                    </div>
                </div>

                <!-- 3. Overdue (31-60 Days) -->
                <div class="stat-card">
                    <div class="stat-label">31–60 Days</div>
                    <div class="stat-value text-amber-600 dark:text-amber-400">
                        ₹{{ number_format($summary['overdue_31_60'], 2) }}
                    </div>
                    <div class="mt-2 text-xs text-slate-500">
                        {{ $summary['total_outstanding'] > 0 ? round(($summary['overdue_31_60'] / $summary['total_outstanding']) * 100, 1) : 0 }}% of total
                    </div>
                </div>

                <!-- 4. Critical (61-90 Days) -->
                <div class="stat-card">
                    <div class="stat-label">61–90 Days</div>
                    <div class="stat-value text-orange-600 dark:text-orange-400">
                        ₹{{ number_format($summary['critical_61_90'], 2) }}
                    </div>
                    <div class="mt-2 text-xs text-slate-500">
                        {{ $summary['total_outstanding'] > 0 ? round(($summary['critical_61_90'] / $summary['total_outstanding']) * 100, 1) : 0 }}% of total
                    </div>
                </div>

                <!-- 5. High Risk (90+ Days) -->
                <div class="stat-card">
                    <div class="stat-label">90+ Days</div>
                    <div class="stat-value text-rose-600 dark:text-rose-400">
                        ₹{{ number_format($summary['high_risk_90_plus'], 2) }}
                    </div>
                    <div class="mt-2 text-xs text-slate-500">
                        {{ $summary['total_outstanding'] > 0 ? round(($summary['high_risk_90_plus'] / $summary['total_outstanding']) * 100, 1) : 0 }}% of total
                    </div>
                </div>

                <!-- 6. Days Sales Outstanding (DSO) -->
                <div class="stat-card">
                    <div class="stat-label">DSO (Collection Speed)</div>
                    <div class="stat-value">
                        {{ $summary['dso_days'] }} <span class="text-xs font-normal text-slate-500">Days</span>
                    </div>
                    <div class="mt-2 text-xs {{ $summary['dso_days'] <= 45 ? 'text-emerald-600' : ($summary['dso_days'] <= 75 ? 'text-amber-600' : 'text-rose-600') }}">
                        {{ $summary['dso_days'] <= 45 ? 'Healthy velocity' : ($summary['dso_days'] <= 75 ? 'Moderate delay' : 'High delay') }}
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2">
                <button @click="activeTab = 'debtors'"
                        :class="activeTab === 'debtors' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded transition" style="border-radius: 4px;">
                    Debtors Aging Matrix ({{ count($debtors) }} Clients)
                </button>
                <button @click="activeTab = 'overdue'"
                        :class="activeTab === 'overdue' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded transition flex items-center gap-1.5" style="border-radius: 4px;">
                    <span>Overdue Invoices</span>
                    @if($summary['overdue_invoices_count'] > 0)
                        <span class="font-bold text-rose-500">({{ $summary['overdue_invoices_count'] }})</span>
                    @endif
                </button>
            </div>

            <!-- TAB 1: Debtors Aging Matrix Table -->
            <div x-show="activeTab === 'debtors'" class="space-y-4">
                <div class="pg-card">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800/60">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Customer</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Contact</th>
                                    <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Open Bills</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">0–30 Days</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">31–60 Days</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">61–90 Days</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">90+ Days</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Total Due</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                                @forelse($debtors as $debtor)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <span>{{ $debtor['customer_name'] }}</span>
                                                @if($debtor['days_90_plus'] > 0)
                                                    <span class="text-xs font-bold text-rose-600 dark:text-rose-400">High Risk</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                            <div>{{ $debtor['phone'] ?: '-' }}</div>
                                            <div class="text-xs text-slate-400">{{ $debtor['email'] ?: '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">
                                            {{ $debtor['unpaid_invoices_count'] }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-emerald-600 dark:text-emerald-400">
                                            {{ $debtor['days_0_30'] > 0 ? '₹' . number_format($debtor['days_0_30'], 2) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-amber-600 dark:text-amber-400">
                                            {{ $debtor['days_31_60'] > 0 ? '₹' . number_format($debtor['days_31_60'], 2) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-orange-600 dark:text-orange-400">
                                            {{ $debtor['days_61_90'] > 0 ? '₹' . number_format($debtor['days_61_90'], 2) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-rose-600 dark:text-rose-400">
                                            {{ $debtor['days_90_plus'] > 0 ? '₹' . number_format($debtor['days_90_plus'], 2) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">
                                            ₹{{ number_format($debtor['total_due'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap text-sm">
                                            <div class="inline-flex items-center gap-1.5 justify-end">
                                                @if($debtor['lead_id'])
                                                    <a href="{{ route('finance.receivables.customer', $debtor['lead_id']) }}"
                                                       class="text-blue-600 dark:text-blue-400 hover:underline">
                                                        Ledger
                                                    </a>
                                                    <span class="text-slate-300 dark:text-slate-600">|</span>
                                                    <a href="{{ route('finance.receivables.customer.pdf', $debtor['lead_id']) }}"
                                                       class="text-blue-600 dark:text-blue-400 hover:underline">
                                                        PDF
                                                    </a>
                                                @else
                                                    <span class="text-slate-400 italic text-xs">No Profile</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                                            No outstanding receivables found.
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
                <div class="pg-card">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800/60">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Invoice #</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Customer</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Issue Date</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Due Date</th>
                                    <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Days Overdue</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Total</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Balance Due</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Last Reminder</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                                @forelse($overdueInvoices as $inv)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                        <td class="px-4 py-3 font-bold">
                                            <a href="{{ route('invoices.show', $inv['invoice_id']) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $inv['invoice_no'] }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white font-medium">
                                            {{ $inv['customer_name'] }}
                                            @if(!empty($inv['customer_phone']) && $inv['customer_phone'] !== 'N/A')
                                                <div class="text-xs text-slate-400 font-normal">{{ $inv['customer_phone'] }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                            {{ $inv['invoice_date'] ?: '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                            {{ $inv['due_date'] ?: '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="font-bold text-rose-600 dark:text-rose-400">
                                                {{ $inv['days_overdue'] }}d Overdue
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-slate-700 dark:text-slate-300">
                                            ₹{{ number_format($inv['total_amount'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-rose-600 dark:text-rose-400">
                                            ₹{{ number_format($inv['balance_due'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-500 text-xs">
                                            {{ $inv['last_reminder_at'] }}
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap text-sm">
                                            <div class="inline-flex items-center gap-1.5 justify-end">
                                                <button @click="reminderModal = true; reminderInvoice = '{{ $inv['invoice_no'] }}'; reminderCustomer = '{{ addslashes($inv['customer_name']) }}'; reminderBalance = '₹{{ number_format($inv['balance_due'], 2) }}'; reminderRoute = '{{ route('finance.receivables.reminder', $inv['invoice_id']) }}';"
                                                        class="text-blue-600 dark:text-blue-400 hover:underline bg-transparent border-0 p-0 font-medium cursor-pointer">
                                                    Send Reminder
                                                </button>

                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <a href="{{ $inv['invoice_url'] }}#record-payment"
                                                   class="text-emerald-600 dark:text-emerald-400 hover:underline">
                                                    Record Payment
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                                            No overdue invoices found.
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
            <div x-show="reminderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60" style="display: none;"
                 @keydown.escape.window="reminderModal = false">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded max-w-md w-full p-6 space-y-4" style="border-radius: 4px;"
                     @click.away="reminderModal = false">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Send Reminder</h3>
                            <p class="text-xs text-slate-500" x-text="reminderInvoice"></p>
                        </div>
                        <button @click="reminderModal = false" class="text-slate-400 hover:text-slate-600 text-lg">
                            &times;
                        </button>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded space-y-1 text-sm" style="border-radius: 4px;">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Customer:</span>
                            <span class="font-medium text-slate-900 dark:text-white" x-text="reminderCustomer"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Outstanding:</span>
                            <span class="font-bold text-rose-600 dark:text-rose-400" x-text="reminderBalance"></span>
                        </div>
                    </div>

                    <form :action="reminderRoute" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Notification Channel</label>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <label class="flex items-center gap-2 p-2 rounded border border-slate-200 dark:border-slate-700 cursor-pointer" style="border-radius: 4px;">
                                    <input type="radio" name="channel" value="all" checked class="text-blue-600">
                                    <span>All Channels</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded border border-slate-200 dark:border-slate-700 cursor-pointer" style="border-radius: 4px;">
                                    <input type="radio" name="channel" value="whatsapp" class="text-blue-600">
                                    <span>WhatsApp</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded border border-slate-200 dark:border-slate-700 cursor-pointer" style="border-radius: 4px;">
                                    <input type="radio" name="channel" value="sms" class="text-blue-600">
                                    <span>SMS</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded border border-slate-200 dark:border-slate-700 cursor-pointer" style="border-radius: 4px;">
                                    <input type="radio" name="channel" value="email" class="text-blue-600">
                                    <span>Email</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="reminderModal = false"
                                    class="px-3 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition" style="border-radius: 4px;">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded transition" style="border-radius: 4px;">
                                Dispatch Reminder
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
