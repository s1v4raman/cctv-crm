<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('finance.job-costing.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl font-black tracking-tight text-slate-900 dark:text-white sm:text-2xl font-mono">
                            {{ $costing['job_no'] }}
                        </h2>
                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full border {{ $costing['margin_badge_class'] }}">
                            {{ $costing['margin_label'] }} ({{ $costing['gross_margin_percent'] }}% Margin)
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Client: <strong class="text-slate-800 dark:text-slate-200">{{ $costing['customer_name'] }}</strong> • Site: {{ $costing['site_address'] }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('finance.job-costing.pdf', $job->id) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-900 hover:bg-black dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold rounded-xl shadow-md transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Download P&L Statement PDF</span>
                </a>

                <button @click="$dispatch('open-modal', 'edit-costing-modal')" type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Labor & Overheads</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
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

        <!-- Visual Project P&L Waterfall Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white rounded-3xl p-6 shadow-xl border border-slate-700/50">
            <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 mb-3">Project Profitability Waterfall (₹)</div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <!-- 1. Revenue -->
                <div class="p-3 bg-white/5 rounded-2xl border border-white/10">
                    <div class="text-[10px] text-slate-400 font-bold uppercase">Taxable Revenue</div>
                    <div class="text-lg font-black font-mono text-white mt-1">₹{{ number_format($costing['revenue_taxable'], 2) }}</div>
                    <div class="text-[9px] text-slate-400">Gross: ₹{{ number_format($costing['revenue_gross'], 2) }}</div>
                </div>

                <!-- 2. Less Hardware COGS -->
                <div class="p-3 bg-white/5 rounded-2xl border border-white/10">
                    <div class="text-[10px] text-purple-400 font-bold uppercase">(-) Hardware COGS</div>
                    <div class="text-lg font-black font-mono text-purple-300 mt-1">₹{{ number_format($costing['hardware_cogs'], 2) }}</div>
                    <div class="text-[9px] text-slate-400">{{ count($costing['hardware_items']) }} Material Items</div>
                </div>

                <!-- 3. Less Labor Cost -->
                <div class="p-3 bg-white/5 rounded-2xl border border-white/10">
                    <div class="text-[10px] text-amber-400 font-bold uppercase">(-) Labor Cost</div>
                    <div class="text-lg font-black font-mono text-amber-300 mt-1">₹{{ number_format($costing['labor_cost'], 2) }}</div>
                    <div class="text-[9px] text-slate-400">{{ $costing['labor_hours'] }} hrs @ ₹{{ $costing['hourly_rate'] }}/hr</div>
                </div>

                <!-- 4. Less Travel & Overheads -->
                <div class="p-3 bg-white/5 rounded-2xl border border-white/10">
                    <div class="text-[10px] text-rose-400 font-bold uppercase">(-) Direct Expenses</div>
                    <div class="text-lg font-black font-mono text-rose-300 mt-1">₹{{ number_format($costing['field_expenses'] + $costing['other_direct_costs'], 2) }}</div>
                    <div class="text-[9px] text-slate-400">Claims & Rentals</div>
                </div>

                <!-- 5. Net Gross Profit -->
                <div class="p-3 bg-emerald-950/80 rounded-2xl border border-emerald-500/40 col-span-2 sm:col-span-1">
                    <div class="text-[10px] text-emerald-300 font-bold uppercase">(=) Net Gross Profit</div>
                    <div class="text-xl font-black font-mono text-emerald-400 mt-1">₹{{ number_format($costing['gross_profit'], 2) }}</div>
                    <div class="text-[10px] font-bold text-emerald-300">{{ $costing['gross_margin_percent'] }}% Margin</div>
                </div>
            </div>
        </div>

        <!-- Section 1: Itemized Hardware Bill of Materials (BOM) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="p-1.5 bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400 rounded-lg text-xs font-bold">BOM</span>
                        <span>Itemized Hardware Material Costs & Product Margins</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Comparison of procurement buy cost vs customer quotation selling price</p>
                </div>
                <div class="text-xs font-bold text-purple-700 dark:text-purple-400">
                    Total Hardware Cost: ₹{{ number_format($costing['hardware_cogs'], 2) }}
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Product / Item Description</th>
                            <th class="py-3 px-4">HSN Code</th>
                            <th class="py-3 px-4 text-center">Qty</th>
                            <th class="py-3 px-4 text-right">Unit Buy Cost (₹)</th>
                            <th class="py-3 px-4 text-right">Selling Price (₹)</th>
                            <th class="py-3 px-4 text-right">Total Cost (₹)</th>
                            <th class="py-3 px-4 text-right">Total Selling (₹)</th>
                            <th class="py-3 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400">Item Profit (₹)</th>
                            <th class="py-3 px-4 text-center">Margin %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse($costing['hardware_items'] as $item)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $item['name'] }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $item['sku'] }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-amber-600 dark:text-amber-400">{{ $item['hsn_code'] }}</td>
                                <td class="py-3.5 px-4 text-center font-bold">{{ $item['quantity'] }} {{ $item['unit'] }}</td>
                                <td class="py-3.5 px-4 text-right font-mono">₹{{ number_format($item['unit_cost'], 2) }}</td>
                                <td class="py-3.5 px-4 text-right font-mono">₹{{ number_format($item['unit_price'], 2) }}</td>
                                <td class="py-3.5 px-4 text-right font-mono font-semibold text-purple-700 dark:text-purple-400">
                                    ₹{{ number_format($item['total_cost'], 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                    ₹{{ number_format($item['total_selling'], 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    ₹{{ number_format($item['item_margin'], 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold {{ $item['margin_percent'] >= 20 ? 'text-emerald-600' : 'text-amber-600' }}">
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Labor Costs Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="p-1.5 bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 rounded-lg text-xs font-bold">LABOR</span>
                        <span>Technician Labor Cost Breakdown</span>
                    </h3>
                    <button @click="$dispatch('open-modal', 'edit-costing-modal')" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                        Edit Hours →
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-50 dark:border-slate-800/60">
                        <span class="text-slate-500">Assigned Technician</span>
                        <strong class="text-slate-900 dark:text-white">{{ $costing['technician_name'] }}</strong>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50 dark:border-slate-800/60">
                        <span class="text-slate-500">Labor Hours Logged</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">
                            {{ $costing['labor_hours'] }} Hours 
                            @if($costing['is_hours_estimated'])
                                <span class="text-[10px] text-amber-500 font-normal">(Auto-estimated)</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50 dark:border-slate-800/60">
                        <span class="text-slate-500">Effective Hourly Labor Rate</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">₹{{ number_format($costing['hourly_rate'], 2) }} / hr</span>
                    </div>
                    <div class="flex items-center justify-between py-2 bg-amber-50/50 dark:bg-amber-950/20 px-3 rounded-xl">
                        <strong class="text-amber-900 dark:text-amber-300">Total Labor Expense</strong>
                        <strong class="font-mono text-sm text-amber-700 dark:text-amber-400">₹{{ number_format($costing['labor_cost'], 2) }}</strong>
                    </div>
                </div>
            </div>

            <!-- Additional Overheads & Notes Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="p-1.5 bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 rounded-lg text-xs font-bold">OVERHEAD</span>
                        <span>Additional Project Costs & Overheads</span>
                    </h3>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-50 dark:border-slate-800/60">
                        <span class="text-slate-500">Direct Travel / Fuel Claims</span>
                        <strong class="font-mono text-slate-900 dark:text-white">₹{{ number_format($costing['field_expenses'], 2) }}</strong>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50 dark:border-slate-800/60">
                        <span class="text-slate-500">Other Direct Costs (Rentals/Permits)</span>
                        <strong class="font-mono text-slate-900 dark:text-white">₹{{ number_format($costing['other_direct_costs'], 2) }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-1">Costing Audit Notes:</span>
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl text-slate-700 dark:text-slate-300 text-[11px] min-h-[48px]">
                            {{ $costing['costing_notes'] ?: 'No additional overhead notes logged.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Tagged Field Travel & Materials Claims -->
        @if(count($costing['expense_claims_list']) > 0)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="p-1.5 bg-teal-100 text-teal-700 dark:bg-teal-950/60 dark:text-teal-400 rounded-lg text-xs font-bold">EXPENSES</span>
                        <span>Field Claims Linked to this Project ({{ count($costing['expense_claims_list']) }})</span>
                    </h3>
                    <div class="text-xs font-bold text-teal-700 dark:text-teal-400">
                        Total Claims: ₹{{ number_format($costing['field_expenses'], 2) }}
                    </div>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-3 px-4">Claim No</th>
                                <th class="py-3 px-4">Date</th>
                                <th class="py-3 px-4">Employee</th>
                                <th class="py-3 px-4">Category</th>
                                <th class="py-3 px-4">Description</th>
                                <th class="py-3 px-4 text-right">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                            @foreach($costing['expense_claims_list'] as $claim)
                                <tr>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">{{ $claim['claim_no'] }}</td>
                                    <td class="py-3 px-4 text-slate-500 whitespace-nowrap">{{ $claim['date'] }}</td>
                                    <td class="py-3 px-4 font-semibold">{{ $claim['employee'] }}</td>
                                    <td class="py-3 px-4 font-medium text-teal-700 dark:text-teal-300">{{ $claim['category'] }}</td>
                                    <td class="py-3 px-4 text-slate-500 max-w-xs truncate">{{ $claim['description'] }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                        ₹{{ number_format($claim['amount'], 2) }}
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
        <form method="POST" action="{{ route('finance.job-costing.update', $job->id) }}" class="p-6 space-y-5">
            @csrf
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="p-2 bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </span>
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Edit Labor Time & Project Overheads</h3>
                </div>
                <button type="button" @click="$dispatch('close-modal', 'edit-costing-modal')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Actual On-Site Labor Hours Logged</label>
                    <input type="number" step="0.5" name="labor_hours_logged" value="{{ old('labor_hours_logged', $job->labor_hours_logged) }}" placeholder="e.g. 8.0"
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono font-bold">
                    <p class="text-[10px] text-slate-400 mt-1">Leave blank to auto-estimate from camera count & scope.</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Custom Labor Hourly Rate (₹/hr)</label>
                    <input type="number" step="1.0" name="custom_hourly_rate" value="{{ old('custom_hourly_rate', $job->custom_hourly_rate) }}" placeholder="e.g. 250.00"
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono font-bold">
                    <p class="text-[10px] text-slate-400 mt-1">Leave blank to use technician's base salary hourly rate.</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Other Direct Project Costs (₹)</label>
                    <input type="number" step="0.01" name="other_direct_costs" value="{{ old('other_direct_costs', $job->other_direct_costs) }}" placeholder="e.g. 1500.00"
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono font-bold">
                    <p class="text-[10px] text-slate-400 mt-1">Scaffolding rentals, scissor lift hire, sub-contractor fees.</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Costing Audit Notes</label>
                    <textarea name="costing_notes" rows="3" placeholder="Explain additional project expenses or subcontracting..."
                              class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-medium">{{ old('costing_notes', $job->costing_notes) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="$dispatch('close-modal', 'edit-costing-modal')"
                        class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition">
                    Update Cost Sheet
                </button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
