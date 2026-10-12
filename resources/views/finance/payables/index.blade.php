<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-900 dark:text-white font-heading tracking-tight">
                    Payables
                </h2>
            </div>

            <!-- Action Buttons: Record Payment, CSVs, PDF -->
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="$dispatch('open-pay-modal', { supplierId: '', poId: '', amount: '', supplierName: '' })"
                        class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded transition"
                        style="border-radius: 4px;">
                    + Record Disbursement
                </button>

                <a href="{{ route('finance.payables.export-csv') }}"
                   class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-medium rounded transition"
                   style="border-radius: 4px;">
                    AP Aging CSV
                </a>

                <a href="{{ route('finance.payables.export-three-way-csv') }}"
                   class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-medium rounded transition"
                   style="border-radius: 4px;">
                    3-Way Match CSV
                </a>

                <a href="{{ route('finance.payables.export-pdf') }}"
                   class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-medium rounded transition"
                   style="border-radius: 4px;">
                    Download AP PDF
                </a>
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
            border-color: #1e293b;
        }
        .stat-label { font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 0.25rem; }
        html.dark .stat-label { color: #94a3b8; }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #0f172a; line-height: 1; }
        html.dark .stat-value { color: #ffffff; }

        .pg-card {
            background: #ffffff;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        html.dark .pg-card {
            background: #0f172a;
            border-color: #1e293b;
        }
    </style>

    <div class="py-6 bg-slate-50 dark:bg-[#060913] min-h-screen" x-data="{ 
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
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm font-medium rounded" style="border-radius: 4px;">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-rose-50 dark:bg-rose-950/50 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm font-medium rounded" style="border-radius: 4px;">
                    @foreach ($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            @endif

            {{-- Finance Category Sub-Navigation --}}
            <x-finance-subnav active="payables" />

            <!-- KPI Metric Cards Grid -->
            <div class="stat-grid">
                <!-- 1. Total Outstanding Payables -->
                <div class="stat-card">
                    <div class="stat-label">Total Payables</div>
                    <div class="stat-value text-rose-600 dark:text-rose-400">
                        ₹{{ number_format($summary['total_payable_balance'], 2) }}
                    </div>
                    <div class="mt-2 text-xs text-slate-500">
                        {{ $summary['unpaid_pos_count'] }} POs • {{ $summary['suppliers_count'] }} Vendors
                    </div>
                </div>

                <!-- 2. Current (0-30 Days) -->
                <div class="stat-card">
                    <div class="stat-label">0–30 Days (Current)</div>
                    <div class="stat-value text-blue-600 dark:text-blue-400">
                        ₹{{ number_format($summary['current_0_30'], 2) }}
                    </div>
                    <div class="mt-2 text-xs text-slate-500">
                        {{ $summary['total_payable_balance'] > 0 ? round(($summary['current_0_30'] / $summary['total_payable_balance']) * 100, 1) : 0 }}% of Total
                    </div>
                </div>

                <!-- 3. Overdue (31-60 Days) -->
                <div class="stat-card">
                    <div class="stat-label">31–60 Days</div>
                    <div class="stat-value text-amber-600 dark:text-amber-400">
                        ₹{{ number_format($summary['overdue_31_60'], 2) }}
                    </div>
                    <div class="mt-2 text-xs text-slate-500">
                        {{ $summary['total_payable_balance'] > 0 ? round(($summary['overdue_31_60'] / $summary['total_payable_balance']) * 100, 1) : 0 }}% of Total
                    </div>
                </div>

                <!-- 4. Critical (61-90 Days) -->
                <div class="stat-card">
                    <div class="stat-label">61–90 Days</div>
                    <div class="stat-value text-orange-600 dark:text-orange-400">
                        ₹{{ number_format($summary['critical_61_90'], 2) }}
                    </div>
                    <div class="mt-2 text-xs text-slate-500">
                        {{ $summary['total_payable_balance'] > 0 ? round(($summary['critical_61_90'] / $summary['total_payable_balance']) * 100, 1) : 0 }}% of Total
                    </div>
                </div>

                <!-- 5. 90+ Days Critical -->
                <div class="stat-card">
                    <div class="stat-label">90+ Days</div>
                    <div class="stat-value text-rose-600 dark:text-rose-400">
                        ₹{{ number_format($summary['high_risk_90_plus'], 2) }}
                    </div>
                    <div class="mt-2 text-xs text-slate-500">
                        {{ $summary['total_payable_balance'] > 0 ? round(($summary['high_risk_90_plus'] / $summary['total_payable_balance']) * 100, 1) : 0 }}% of Total
                    </div>
                </div>

                <!-- 6. Days Payable Outstanding (DPO) -->
                <div class="stat-card">
                    <div class="stat-label">DPO & 3-Way Health</div>
                    <div class="stat-value">
                        {{ $summary['dpo_days'] }} <span class="text-xs font-normal text-slate-500">Days</span>
                    </div>
                    <div class="mt-2 text-xs text-emerald-600 dark:text-emerald-400">
                        {{ $summary['three_way_match_pass_rate'] }}% 3-Way Verified
                    </div>
                </div>
            </div>

            <!-- Tab Controls & Filter Bar -->
            <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2">
                <button @click="activeTab = 'aging'"
                        :class="activeTab === 'aging' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded transition" style="border-radius: 4px;">
                    Supplier AP Aging ({{ count($suppliers) }} Vendors)
                </button>
                <button @click="activeTab = 'three_way'"
                        :class="activeTab === 'three_way' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded transition flex items-center gap-1.5" style="border-radius: 4px;">
                    <span>3-Way Matching Inspector</span>
                    <span class="font-bold text-emerald-600">({{ $summary['three_way_stats']['matched'] }}/{{ $summary['total_pos_count'] }})</span>
                </button>
                <button @click="activeTab = 'disbursements'"
                        :class="activeTab === 'disbursements' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded transition flex items-center gap-1.5" style="border-radius: 4px;">
                    <span>Payment Scheduler</span>
                    @if(count($disbursements) > 0)
                        <span class="font-bold text-amber-600">({{ count($disbursements) }})</span>
                    @endif
                </button>
            </div>

            <!-- TAB 1: Supplier AP Aging Matrix Table -->
            <div x-show="activeTab === 'aging'" class="space-y-4">
                <div class="pg-card">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800/60">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Supplier</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Contact & GSTIN</th>
                                    <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Unpaid POs</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">0–30 Days</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">31–60 Days</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">61–90 Days</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">90+ Days</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Net Payable</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                                @forelse($suppliers as $s)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <span>{{ $s['supplier_name'] }}</span>
                                                @if($s['days_90_plus'] > 0)
                                                    <span class="text-xs font-bold text-rose-600 dark:text-rose-400">Overdue</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                            <div>{{ $s['phone'] }}</div>
                                            <div class="text-xs text-slate-400 font-mono">GST: {{ $s['gst_number'] }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">
                                            {{ $s['unpaid_po_count'] }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-blue-600 dark:text-blue-400">
                                            {{ $s['days_0_30'] > 0 ? '₹' . number_format($s['days_0_30'], 2) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-amber-600 dark:text-amber-400">
                                            {{ $s['days_31_60'] > 0 ? '₹' . number_format($s['days_31_60'], 2) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-orange-600 dark:text-orange-400">
                                            {{ $s['days_61_90'] > 0 ? '₹' . number_format($s['days_61_90'], 2) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-rose-600 dark:text-rose-400">
                                            {{ $s['days_90_plus'] > 0 ? '₹' . number_format($s['days_90_plus'], 2) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">
                                            ₹{{ number_format($s['total_due'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap text-sm">
                                            <div class="inline-flex items-center gap-1.5 justify-end">
                                                <a href="{{ route('finance.payables.supplier', $s['supplier_id']) }}"
                                                   class="text-blue-600 dark:text-blue-400 hover:underline">
                                                    Ledger
                                                </a>
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <a href="{{ route('finance.payables.supplier.pdf', $s['supplier_id']) }}"
                                                   class="text-blue-600 dark:text-blue-400 hover:underline">
                                                    PDF
                                                </a>
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <button type="button" @click="$dispatch('open-pay-modal', { supplierId: '{{ $s['supplier_id'] }}', supplierName: '{{ addslashes($s['supplier_name']) }}', amount: '{{ $s['total_due'] }}' })"
                                                        class="text-emerald-600 dark:text-emerald-400 hover:underline bg-transparent border-0 p-0 font-medium cursor-pointer">
                                                    Pay
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                                            No outstanding payables found.
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
                <div class="pg-card">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800/60">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">PO #</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Supplier</th>
                                    <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Ordered</th>
                                    <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Received</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Supplier Bill #</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">PO Total</th>
                                    <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">3-Way Match</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Audit Status</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                                @forelse($threeWayAudit as $item)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                        <td class="px-4 py-3 font-bold">
                                            <a href="{{ route('purchase-orders.show', $item['po_id']) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $item['po_number'] }}
                                            </a>
                                            <div class="text-xs text-slate-400 font-normal">{{ $item['order_date'] }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white font-medium">
                                            {{ $item['supplier_name'] }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-medium text-slate-900 dark:text-white">
                                            {{ $item['total_ordered_qty'] }} units
                                        </td>
                                        <td class="px-4 py-3 text-center font-medium {{ $item['total_received_qty'] >= $item['total_ordered_qty'] ? 'text-emerald-600' : 'text-amber-600' }}">
                                            {{ $item['total_received_qty'] }} units
                                        </td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                            @if($item['has_bill'])
                                                <span class="font-mono font-medium">{{ $item['bill_number'] }}</span>
                                                @if($item['bill_date'])
                                                    <div class="text-xs text-slate-400">{{ $item['bill_date'] }}</div>
                                                @endif
                                            @else
                                                <span class="text-amber-600 font-medium">Bill Pending</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-slate-900 dark:text-white">
                                            ₹{{ number_format($item['total_amount'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @php
                                                $matchColor = 'text-slate-500';
                                                if (str_contains(strtolower($item['match_label']), 'matched') || str_contains(strtolower($item['match_label']), 'pass')) $matchColor = 'text-emerald-600 dark:text-emerald-400';
                                                elseif (str_contains(strtolower($item['match_label']), 'partial')) $matchColor = 'text-amber-600 dark:text-amber-400';
                                                else $matchColor = 'text-rose-600 dark:text-rose-400';
                                            @endphp
                                            <span class="font-bold {{ $matchColor }}">
                                                {{ $item['match_label'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-xs max-w-xs">
                                            {{ $item['match_description'] }}
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap text-sm">
                                            <a href="{{ route('purchase-orders.show', $item['po_id']) }}"
                                               class="text-blue-600 dark:text-blue-400 hover:underline">
                                                Inspect
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-8 text-center text-slate-500">
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
                <div class="pg-card">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800/60">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">PO #</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Supplier Name</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Bill Ref</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Due Date</th>
                                    <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Total</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Balance Due</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                                @forelse($disbursements as $d)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                        <td class="px-4 py-3 font-bold">
                                            <a href="{{ route('purchase-orders.show', $d['po_id']) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $d['po_number'] }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white font-medium">
                                            {{ $d['supplier_name'] }}
                                            @if($d['supplier_phone'] && $d['supplier_phone'] !== 'N/A')
                                                <div class="text-xs text-slate-400 font-normal">{{ $d['supplier_phone'] }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                            {{ $d['bill_number'] }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                            {{ $d['due_date'] }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($d['is_overdue'])
                                                <span class="font-bold text-rose-600 dark:text-rose-400">
                                                    {{ $d['days_overdue'] }}d Overdue
                                                </span>
                                            @else
                                                <span class="font-medium text-blue-600 dark:text-blue-400">
                                                    Within Terms
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-slate-700 dark:text-slate-300">
                                            ₹{{ number_format($d['total_amount'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-rose-600 dark:text-rose-400">
                                            ₹{{ number_format($d['balance_due'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap text-sm">
                                            <button type="button" @click="$dispatch('open-pay-modal', { supplierId: '{{ $d['supplier_id'] }}', poId: '{{ $d['po_id'] }}', supplierName: '{{ addslashes($d['supplier_name']) }}', poNumber: '{{ $d['po_number'] }}', amount: '{{ $d['balance_due'] }}' })"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline bg-transparent border-0 p-0 font-medium cursor-pointer">
                                                Disburse Pay
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-slate-500">
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
            <div x-show="payModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60" style="display: none;"
                 @keydown.escape.window="payModal = false">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded max-w-lg w-full p-6 space-y-4" style="border-radius: 4px;"
                     @click.away="payModal = false">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Record Vendor Disbursement</h3>
                            <p class="text-xs text-slate-500" x-text="modalSupplierName ? (modalSupplierName + (modalPoNumber ? ' (' + modalPoNumber + ')' : '')) : 'Supplier Payment'"></p>
                        </div>
                        <button @click="payModal = false" class="text-slate-400 hover:text-slate-600 text-lg">
                            &times;
                        </button>
                    </div>

                    <form action="{{ route('finance.payables.payment') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="purchase_order_id" :value="modalPoId">

                        <template x-if="!modalSupplierId">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Select Supplier / Vendor *</label>
                                <select name="supplier_id" required
                                        class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white" style="border-radius: 4px; height: 38px;">
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
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Disbursement Amount (₹) *</label>
                                <input type="number" step="0.01" min="0.01" name="amount" :value="modalAmount" required
                                       class="w-full px-3 py-2 text-sm font-mono rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white" style="border-radius: 4px; height: 38px;">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Date *</label>
                                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                       class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white" style="border-radius: 4px; height: 38px;">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Method *</label>
                                <select name="payment_method" required
                                        class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white" style="border-radius: 4px; height: 38px;">
                                    <option value="neft_rtgs">NEFT / RTGS (Bank Transfer)</option>
                                    <option value="upi">UPI (GPay / PhonePe)</option>
                                    <option value="cheque">Bank Cheque</option>
                                    <option value="bank_transfer">Net Banking Transfer</option>
                                    <option value="cash">Cash Settlement</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Bank Ref / UTR / Cheque #</label>
                                <input type="text" name="transaction_reference" placeholder="e.g. UTR12345678"
                                       class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white" style="border-radius: 4px; height: 38px;">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Remarks</label>
                            <textarea name="notes" rows="2" placeholder="Optional settlement notes..."
                                      class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white" style="border-radius: 4px;"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="payModal = false"
                                    class="px-3 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition" style="border-radius: 4px;">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded transition" style="border-radius: 4px;">
                                Confirm Disbursement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
