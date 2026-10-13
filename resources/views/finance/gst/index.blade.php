<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        GST & Tax Compliance Center
                    </h2>
                    <span class="text-xs font-bold text-[#d97706]">
                        GSTR-1 & ITC Hub
                    </span>
                </div>
                <p class="text-xs text-[#555555] mt-0.5">
                    {{ $companyGst['trade_name'] }} • GSTIN: <strong class="text-[#222222]">{{ $companyGst['gstin'] }}</strong>
                </p>
            </div>

            <!-- Month Filter & Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('finance.gst.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()"
                           class="crm-input text-xs py-1" style="height: 38px; width: 140px;">
                </form>

                <!-- Record Filing Button -->
                <button @click="$dispatch('open-modal', 'record-filing-modal')" type="button" class="btn-secondary">
                    Record Filing
                </button>

                <!-- Export Menu Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.outside="open = false" type="button" class="btn-primary">
                        Export Return
                    </button>

                    <div x-show="open" x-cloak
                         class="absolute right-0 mt-1 w-64 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded shadow-lg py-2 z-50">
                        <div class="px-3.5 py-1 text-xs font-semibold text-slate-400 border-b border-slate-100 dark:border-slate-800 mb-1">
                            GST Exports ({{ Carbon\Carbon::parse($selectedMonth.'-01')->format('M Y') }})
                        </div>
                        <a href="{{ route('finance.gst.export-json', ['month' => $selectedMonth]) }}" class="block px-3.5 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            <div class="font-bold">GSTR-1 Portal JSON</div>
                            <div class="text-[10px] text-slate-400">Direct upload to gst.gov.in</div>
                        </a>
                        <a href="{{ route('finance.gst.export-csv', ['month' => $selectedMonth]) }}" class="block px-3.5 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            <div class="font-bold">GSTR-1 Sales Report (CSV)</div>
                            <div class="text-[10px] text-slate-400">B2B, B2C & HSN format</div>
                        </a>
                        <a href="{{ route('finance.gst.export-itc-csv', ['month' => $selectedMonth]) }}" class="block px-3.5 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            <div class="font-bold">GSTR-2B / ITC Purchases (CSV)</div>
                            <div class="text-[10px] text-slate-400">Vendor POs & Expenses</div>
                        </a>
                        <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                        <a href="{{ route('finance.gst.export-pdf', ['month' => $selectedMonth]) }}" class="block px-3.5 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            <div class="font-bold">Tax Audit Package (PDF)</div>
                        </a>
                    </div>
                </div>

                <!-- Settings Button -->
                <button @click="$dispatch('open-modal', 'company-gst-modal')" type="button" title="GST Settings"
                        class="btn-secondary">
                    Settings
            </div>
        </div>
    </x-slot>

    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 640px) {
            .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .stat-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
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

    <div class="py-6 bg-slate-50 dark:bg-[#060913] min-h-screen">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Finance Category Sub-Navigation --}}
        <x-finance-subnav active="gst" />

        @if (session('status'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm font-medium rounded" style="border-radius: 4px;">
                {{ session('status') }}
            </div>
        @endif

        <!-- Filing Status Bar -->
        <div class="pg-card p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="text-xs text-slate-500">Period: {{ Carbon\Carbon::parse($selectedMonth.'-01')->format('F Y') }}</div>
                <div class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                    <span>GST Return Summary:</span>
                    @if($filing && $filing->gstr3b_status === 'filed')
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Return Filed & Discharged</span>
                    @elseif($filing && $filing->gstr3b_status === 'reconciled')
                        <span class="font-bold text-blue-600 dark:text-blue-400">Reconciled & Ready to File</span>
                    @else
                        <span class="font-bold text-amber-600 dark:text-amber-400">Draft Computation Pending</span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block">GSTR-1</span>
                    <span class="font-bold text-slate-900 dark:text-white uppercase">{{ $filing ? ucfirst($filing->gstr1_status) : 'Pending' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">GSTR-3B</span>
                    <span class="font-bold text-slate-900 dark:text-white uppercase">{{ $filing ? ucfirst($filing->gstr3b_status) : 'Pending' }}</span>
                </div>
                @if($filing && $filing->challan_no)
                    <div>
                        <span class="text-xs text-slate-400 block">Challan Ref</span>
                        <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $filing->challan_no }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="stat-grid">
            <!-- 1. Total Turnover -->
            <div class="stat-card">
                <div class="stat-label">Gross Sales Turnover</div>
                <div class="stat-value text-blue-600 dark:text-blue-400">
                    ₹{{ number_format($gstr1Data['total_gross_turnover'], 2) }}
                </div>
                <div class="text-xs text-slate-500 mt-2">
                    Taxable: ₹{{ number_format($gstr1Data['total_taxable_turnover'], 2) }} • {{ $gstr1Data['total_invoices_count'] }} Invoices
                </div>
            </div>

            <!-- 2. Output Tax Collected -->
            <div class="stat-card">
                <div class="stat-label">Output GST (Sales)</div>
                <div class="stat-value text-slate-900 dark:text-white">
                    ₹{{ number_format($gstr1Data['total_output_tax'], 2) }}
                </div>
                <div class="text-xs text-slate-500 mt-2">
                    CGST+SGST: ₹{{ number_format($gstr1Data['total_output_cgst'] + $gstr1Data['total_output_sgst'], 2) }} • IGST: ₹{{ number_format($gstr1Data['total_output_igst'], 2) }}
                </div>
            </div>

            <!-- 3. Input Tax Credit (ITC Available) -->
            <div class="stat-card">
                <div class="stat-label">Input Tax Credit (ITC)</div>
                <div class="stat-value text-emerald-600 dark:text-emerald-400">
                    ₹{{ number_format($itcData['grand_total_itc'], 2) }}
                </div>
                <div class="text-xs text-slate-500 mt-2">
                    POs: ₹{{ number_format($itcData['total_po_itc'], 2) }} • Expenses: ₹{{ number_format($itcData['total_expense_itc'], 2) }}
                </div>
            </div>

            <!-- 4. Net Tax Payable (Cash) -->
            <div class="stat-card">
                <div class="stat-label">Net Cash Tax Payable</div>
                <div class="stat-value text-amber-600 dark:text-amber-400">
                    ₹{{ number_format($gstr3bData['net_tax_payable_cash']['total'], 2) }}
                </div>
                <div class="text-xs text-slate-500 mt-2">
                    @if($gstr3bData['itc_carry_forward']['total'] > 0)
                        <span class="text-emerald-600 font-bold">ITC C/F: ₹{{ number_format($gstr3bData['itc_carry_forward']['total'], 2) }}</span>
                    @else
                        <span class="text-amber-600 font-bold">To Pay in Cash</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Navigation Tabs Container -->
        <div class="pg-card p-4">
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 mb-4">
                @php
                    $tabs = [
                        'overview' => ['label' => 'GSTR-3B Tax Computation & Set-Off'],
                        'gstr1'    => ['label' => 'GSTR-1 Outward Sales (' . count($gstr1Data['b2b_invoices']) . ' B2B / ' . count($gstr1Data['b2c_small_summary']) . ' B2C)'],
                        'itc'      => ['label' => 'Input Tax Credit (' . count($itcData['purchase_orders']) . ' POs)'],
                        'hsn'      => ['label' => 'Table 12 HSN Summary (' . count($gstr1Data['hsn_summary']) . ' HSNs)'],
                        'filings'  => ['label' => 'Filing & Challan Audit Log'],
                    ];
                @endphp

                @foreach($tabs as $key => $tab)
                    <a href="{{ route('finance.gst.index', ['month' => $selectedMonth, 'tab' => $key]) }}"
                       class="px-3.5 py-1.5 rounded text-xs font-semibold transition {{ $activeTab === $key ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}" style="border-radius: 4px;">
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
