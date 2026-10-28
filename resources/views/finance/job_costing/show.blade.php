<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('finance.job-costing.index') }}" class="back-link">&larr; Back</a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-1">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        Job Costing: {{ $costing['job_no'] }}
                    </h2>
                    <div class="flex items-center gap-2 text-xs mt-1">
                        <span class="font-bold {{ $costing['gross_margin_percent'] >= 20 ? 'text-emerald-600' : ($costing['gross_margin_percent'] >= 5 ? 'text-amber-600' : 'text-red-600') }}">
                            {{ $costing['gross_margin_percent'] }}% Margin
                        </span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="text-slate-600 dark:text-slate-300">Client: {{ $costing['customer_name'] }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('finance.job-costing.pdf', $job->id) }}" class="btn-secondary">
                        P&amp;L PDF
                    </a>

                    <button @click="$dispatch('open-modal', 'edit-costing-modal')" type="button" class="btn-primary">
                        Edit Costing
                    </button>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        @if (session('status'))
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 rounded text-xs font-medium text-emerald-900 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <!-- Visual Project P&L Waterfall Banner -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
            <h2 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-3">Project Profitability Summary</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <!-- 1. Revenue -->
                <div class="p-3 bg-slate-50 dark:bg-slate-800/80 rounded border border-slate-200 dark:border-slate-700">
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Taxable Revenue</div>
                    <div class="text-base font-bold text-slate-900 dark:text-white mt-1">&#8377;{{ number_format($costing['revenue_taxable'], 2) }}</div>
                    <div class="text-[11px] text-slate-400">Gross: &#8377;{{ number_format($costing['revenue_gross'], 2) }}</div>
                </div>

                <!-- 2. Less Hardware COGS -->
                <div class="p-3 bg-slate-50 dark:bg-slate-800/80 rounded border border-slate-200 dark:border-slate-700">
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">(-) Hardware COGS</div>
                    <div class="text-base font-bold text-slate-900 dark:text-white mt-1">&#8377;{{ number_format($costing['hardware_cogs'], 2) }}</div>
                    <div class="text-[11px] text-slate-400">{{ count($costing['hardware_items']) }} Items</div>
                </div>

                <!-- 3. Less Labor Cost -->
                <div class="p-3 bg-slate-50 dark:bg-slate-800/80 rounded border border-slate-200 dark:border-slate-700">
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">(-) Labor Cost</div>
                    <div class="text-base font-bold text-slate-900 dark:text-white mt-1">&#8377;{{ number_format($costing['labor_cost'], 2) }}</div>
                    <div class="text-[11px] text-slate-400">{{ $costing['labor_hours'] }} hrs @ &#8377;{{ $costing['hourly_rate'] }}/hr</div>
                </div>

                <!-- 4. Less Travel & Overheads -->
                <div class="p-3 bg-slate-50 dark:bg-slate-800/80 rounded border border-slate-200 dark:border-slate-700">
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">(-) Direct Expenses</div>
                    <div class="text-base font-bold text-slate-900 dark:text-white mt-1">&#8377;{{ number_format($costing['field_expenses'] + $costing['other_direct_costs'], 2) }}</div>
                    <div class="text-[11px] text-slate-400">Claims &amp; Rentals</div>
                </div>

                <!-- 5. Net Gross Profit -->
                <div class="p-3 bg-slate-50 dark:bg-slate-800/80 rounded border border-slate-200 dark:border-slate-700 col-span-2 sm:col-span-1">
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">(=) Net Gross Profit</div>
                    <div class="text-base font-bold {{ $costing['gross_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mt-1">&#8377;{{ number_format($costing['gross_profit'], 2) }}</div>
                    <div class="text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ $costing['gross_margin_percent'] }}% Margin</div>
                </div>
            </div>
        </div>

        <!-- Section 1: Itemized Hardware Bill of Materials (BOM) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Itemized Hardware Material Costs</h2>
                <div class="text-xs text-slate-600 dark:text-slate-400">
                    Total Hardware Cost: &#8377;{{ number_format($costing['hardware_cogs'], 2) }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 text-[11px] font-bold">
                        <tr>
                            <th class="py-3 px-3">Product / Item Description</th>
                            <th class="py-3 px-3">HSN Code</th>
                            <th class="py-3 px-3 text-center">Qty</th>
                            <th class="py-3 px-3 text-right">Unit Buy Cost (&#8377;)</th>
                            <th class="py-3 px-3 text-right">Selling Price (&#8377;)</th>
                            <th class="py-3 px-3 text-right">Total Cost (&#8377;)</th>
                            <th class="py-3 px-3 text-right">Total Selling (&#8377;)</th>
                            <th class="py-3 px-3 text-right">Item Profit (&#8377;)</th>
                            <th class="py-3 px-3 text-center">Margin %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse($costing['hardware_items'] as $item)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $item['name'] }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $item['sku'] }}</div>
                                </td>
                                <td class="py-3 px-3 font-medium text-slate-600 dark:text-slate-400">{{ $item['hsn_code'] }}</td>
                                <td class="py-3 px-3 text-center font-medium">{{ $item['quantity'] }} {{ $item['unit'] }}</td>
                                <td class="py-3 px-3 text-right">&#8377;{{ number_format($item['unit_cost'], 2) }}</td>
                                <td class="py-3 px-3 text-right">&#8377;{{ number_format($item['unit_price'], 2) }}</td>
                                <td class="py-3 px-3 text-right">&#8377;{{ number_format($item['total_cost'], 2) }}</td>
                                <td class="py-3 px-3 text-right font-medium text-slate-900 dark:text-white">
                                    &#8377;{{ number_format($item['total_selling'], 2) }}
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                    &#8377;{{ number_format($item['item_margin'], 2) }}
                                </td>
                                <td class="py-3 px-3 text-center font-bold {{ $item['margin_percent'] >= 20 ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $item['margin_percent'] }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400 text-xs">
                                    No itemized quotation lines found. Standard estimated hardware cost used.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 2: Labor & Overhead Costs -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Labor Costs Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Technician Labor Cost Breakdown</h3>
                    <button @click="$dispatch('open-modal', 'edit-costing-modal')" class="text-xs text-blue-600 hover:underline">
                        Edit Hours
                    </button>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Assigned Technician</span>
                        <span class="font-medium text-slate-900 dark:text-white">{{ $costing['technician_name'] }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Labor Hours Logged</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">
                            {{ $costing['labor_hours'] }} Hours 
                            @if($costing['is_hours_estimated'])
                                <span class="text-[11px] text-amber-500 font-normal">(Auto-estimated)</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Hourly Labor Rate</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">&#8377;{{ number_format($costing['hourly_rate'], 2) }} / hr</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-t border-slate-100 dark:border-slate-800 font-bold">
                        <span class="text-slate-700 dark:text-slate-300">Total Labor Expense</span>
                        <span class="text-sm text-slate-900 dark:text-white">&#8377;{{ number_format($costing['labor_cost'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Additional Overheads & Notes Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Additional Project Costs &amp; Overheads</h3>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Travel / Fuel Claims</span>
                        <span class="font-medium text-slate-900 dark:text-white">&#8377;{{ number_format($costing['field_expenses'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Other Direct Costs</span>
                        <span class="font-medium text-slate-900 dark:text-white">&#8377;{{ number_format($costing['other_direct_costs'], 2) }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-1">Costing Audit Notes:</span>
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs">
                            {{ $costing['costing_notes'] ?: 'No additional overhead notes logged.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Tagged Field Travel & Materials Claims -->
        @if(count($costing['expense_claims_list']) > 0)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Field Claims Linked to this Project</h3>
                    <div class="text-xs text-slate-600 dark:text-slate-400">
                        Total Claims: &#8377;{{ number_format($costing['field_expenses'], 2) }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600 dark:text-slate-400">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 text-[11px] font-bold">
                            <tr>
                                <th class="py-3 px-3">Claim No</th>
                                <th class="py-3 px-3">Date</th>
                                <th class="py-3 px-3">Employee</th>
                                <th class="py-3 px-3">Category</th>
                                <th class="py-3 px-3">Description</th>
                                <th class="py-3 px-3 text-right">Amount (&#8377;)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                            @foreach($costing['expense_claims_list'] as $claim)
                                <tr>
                                    <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">{{ $claim['claim_no'] }}</td>
                                    <td class="py-3 px-3 text-slate-500">{{ $claim['date'] }}</td>
                                    <td class="py-3 px-3 font-medium">{{ $claim['employee'] }}</td>
                                    <td class="py-3 px-3">{{ $claim['category'] }}</td>
                                    <td class="py-3 px-3 text-slate-500 max-w-xs truncate">{{ $claim['description'] }}</td>
                                    <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                                        &#8377;{{ number_format($claim['amount'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- MODAL: EDIT LABOR HOURS & PROJECT OVERHEAD COSTS -->
    <x-modal name="edit-costing-modal" :show="false" maxWidth="lg">
        <form method="POST" action="{{ route('finance.job-costing.update', $job->id) }}" class="p-6 space-y-4">
            @csrf
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Edit Labor Time &amp; Project Overheads</h3>
                <button type="button" @click="$dispatch('close-modal', 'edit-costing-modal')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Actual On-Site Labor Hours Logged</label>
                    <input aria-label="Actual On-Site Labor Hours Logged" type="number" step="0.5" name="labor_hours_logged" value="{{ old('labor_hours_logged', $job->labor_hours_logged) }}" placeholder="8.0"
                           class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    <p class="text-[11px] text-slate-400 mt-1">Leave blank to auto-estimate from camera count & scope.</p>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Custom Labor Hourly Rate (&#8377;/hr)</label>
                    <input aria-label="Custom Labor Hourly Rate (&#8377;/hr)" type="number" step="1.0" name="custom_hourly_rate" value="{{ old('custom_hourly_rate', $job->custom_hourly_rate) }}" placeholder="250.00"
                           class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    <p class="text-[11px] text-slate-400 mt-1">Leave blank to use technician's base salary hourly rate.</p>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Other Direct Project Costs (&#8377;)</label>
                    <input aria-label="Other Direct Project Costs (&#8377;)" type="number" step="0.01" name="other_direct_costs" value="{{ old('other_direct_costs', $job->other_direct_costs) }}" placeholder="1500.00"
                           class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    <p class="text-[11px] text-slate-400 mt-1">Scaffolding rentals, scissor lift hire, sub-contractor fees.</p>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Costing Audit Notes</label>
                    <textarea aria-label="Costing Audit Notes" name="costing_notes" rows="3" placeholder="Explain additional project expenses or subcontracting..."
                              class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs px-3 py-2 text-slate-900 dark:text-white focus:outline-none">{{ old('costing_notes', $job->costing_notes) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="$dispatch('close-modal', 'edit-costing-modal')"
                        class="px-4 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 rounded transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 text-xs font-medium text-white bg-[#2563eb] hover:bg-blue-700 rounded transition">
                    Update Cost Sheet
                </button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
