<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Sunday Payday Weekly Wage Console</h2>
                    <span class="text-xs font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2.5 py-1 rounded border border-emerald-200 dark:border-emerald-500/30">Labor Payroll</span>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Weekly labor reconciliation: Monday–Saturday work logs, advances, running balances &amp; Sunday settlements</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('wages.signature-sheet', ['week' => $weekStart->toDateString()]) }}" target="_blank" class="btn-secondary text-xs flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-4 h-4 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Cash Signature Sheet</span>
                </a>
                <a href="{{ route('wages.export-csv', ['week' => $weekStart->toDateString()]) }}" class="btn-secondary text-xs flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-4 h-4 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6"
         x-data="{
             advanceModalOpen: false,
             payPartModalOpen: false,
             selectedWorkerId: null,
             selectedWorkerName: '',
             selectedTotalDue: 0,
             partAmount: 0,
             openAdvance(workerId, workerName) {
                 this.selectedWorkerId = workerId;
                 this.selectedWorkerName = workerName;
                 this.advanceModalOpen = true;
             },
             openPayPart(workerId, workerName, totalDue) {
                 this.selectedWorkerId = workerId;
                 this.selectedWorkerName = workerName;
                 this.selectedTotalDue = totalDue;
                 this.partAmount = totalDue;
                 this.payPartModalOpen = true;
             }
         }">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
            </div>
        @endif

        {{-- Week Cycle Selector Bar --}}
        <div class="p-4 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Payday Period:</span>
                <form method="GET" action="{{ route('wages.index') }}" class="inline-block">
                    <select name="week" onchange="this.form.submit()" class="px-3 py-2 text-sm font-bold bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($availableWeeks as $w)
                            <option value="{{ $w['val'] }}" {{ $weekStart->toDateString() === $w['val'] ? 'selected' : '' }}>
                                {{ $w['label'] }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Active Cycle: <strong class="text-slate-900 dark:text-white">{{ $weekStart->format('d M') }} (Mon) – {{ $paydaySunday->format('d M Y') }} (Sun)</strong></span>
            </div>
        </div>

        {{-- KPI Cards: Cash Total Needed & Breakdown --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-lg shadow-blue-500/20 sm:col-span-1">
                <span class="text-xs font-extrabold uppercase tracking-widest text-blue-100">Sunday Cash Payout</span>
                <div class="text-3xl font-black font-heading mt-1">₹{{ number_format($totalDueAll, 2) }}</div>
                <span class="text-xs text-blue-100 mt-1 block">Total cash required this Sunday</span>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Brought Forward (Old)</span>
                <div class="text-2xl font-black text-slate-800 dark:text-slate-200 font-heading mt-1 font-mono">₹{{ number_format($totalBroughtForwardAll, 2) }}</div>
                <span class="text-[11px] text-slate-400">Unsettled from past weeks</span>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Earned This Week</span>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-heading mt-1 font-mono">₹{{ number_format($totalEarnedAll, 2) }}</div>
                <span class="text-[11px] text-slate-400">Daily rates + cabling extras</span>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Advances Taken</span>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 font-heading mt-1 font-mono">₹{{ number_format($totalAdvancesAll, 2) }}</div>
                <span class="text-[11px] text-slate-400">Mid-week advance payouts</span>
            </div>
        </div>

        {{-- Running Ledger Table --}}
        <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Worker Running Ledger</h3>
                <span class="text-xs font-mono text-slate-400">Formula: Total Due = Brought Forward + Earned – Advances</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">Worker Name</th>
                            <th class="px-5 py-3.5 text-center">Days Worked</th>
                            <th class="px-5 py-3.5 text-center">Cabling Metres</th>
                            <th class="px-5 py-3.5 text-right">Brought Forward (₹)</th>
                            <th class="px-5 py-3.5 text-right">Earned This Week (₹)</th>
                            <th class="px-5 py-3.5 text-right">Advances (₹)</th>
                            <th class="px-5 py-3.5 text-right font-black">Total Payable (₹)</th>
                            <th class="px-5 py-3.5 text-right">Settlement Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-sans">
                        @forelse($wageRows as $row)
                            @php
                                $w = $row['worker'];
                                $due = $row['total_due'];
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ strtoupper(substr($w->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('workers.show', $w) }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 transition">
                                                {{ $w->name }}
                                            </a>
                                            <div class="text-xs text-slate-400 font-mono">₹{{ number_format($w->daily_rate, 2) }}/day</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center font-bold font-mono">
                                    {{ $row['days_worked'] }}
                                </td>
                                <td class="px-5 py-4 text-center font-mono text-xs">
                                    {{ $row['total_metres'] > 0 ? $row['total_metres'] . ' m' : '—' }}
                                </td>
                                <td class="px-5 py-4 text-right font-mono text-xs">
                                    {{ $row['brought_forward'] > 0 ? '₹' . number_format($row['brought_forward'], 2) : '—' }}
                                </td>
                                <td class="px-5 py-4 text-right font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    {{ $row['earned'] > 0 ? '₹' . number_format($row['earned'], 2) : '—' }}
                                </td>
                                <td class="px-5 py-4 text-right font-mono text-xs text-amber-600 dark:text-amber-400">
                                    {{ $row['advances'] > 0 ? '-₹' . number_format($row['advances'], 2) : '—' }}
                                </td>
                                <td class="px-5 py-4 text-right font-mono font-black text-base {{ $due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                                    ₹{{ number_format($due, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        {{-- Record Advance Button --}}
                                        <button type="button" @click="openAdvance({{ $w->id }}, '{{ addslashes($w->name) }}')" class="px-2 py-1 rounded-lg text-xs font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 transition" title="Record Advance">
                                            + Advance
                                        </button>

                                        @if($due > 0)
                                            {{-- Pay Full 1-Click Form --}}
                                            <form method="POST" action="{{ route('wages.settle') }}" class="inline-block" onsubmit="return confirm('Disburse full settlement of ₹{{ number_format($due, 2) }} to {{ addslashes($w->name) }}?')">
                                                @csrf
                                                <input type="hidden" name="worker_id" value="{{ $w->id }}">
                                                <input type="hidden" name="settlement_action" value="pay_full">
                                                <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">
                                                <input type="hidden" name="payment_mode" value="cash">
                                                <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-2xs">
                                                    Pay Full
                                                </button>
                                            </form>

                                            {{-- Pay Part Modal Trigger --}}
                                            <button type="button" @click="openPayPart({{ $w->id }}, '{{ addslashes($w->name) }}', {{ $due }})" class="px-2 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition">
                                                Pay Part
                                            </button>

                                            {{-- Carry Forward Form --}}
                                            <form method="POST" action="{{ route('wages.settle') }}" class="inline-block" onsubmit="return confirm('Carry forward entire balance of ₹{{ number_format($due, 2) }} to next week?')">
                                                @csrf
                                                <input type="hidden" name="worker_id" value="{{ $w->id }}">
                                                <input type="hidden" name="settlement_action" value="carry_forward">
                                                <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">
                                                <input type="hidden" name="payment_mode" value="cash">
                                                <button type="submit" class="px-2 py-1 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                                    Carry Fwd
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 px-2 py-1">
                                                ✓ Settled
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                    No active workers found in system.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Advance Modal --}}
        <div x-show="advanceModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="advanceModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Record Advance Payout</h3>
                    <button type="button" @click="advanceModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('wages.advance') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="worker_id" :value="selectedWorkerId">

                    <div>
                        <span class="text-xs text-slate-400">Worker</span>
                        <div class="font-bold text-slate-900 dark:text-white text-sm" x-text="selectedWorkerName"></div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Advance Amount (₹) *</label>
                        <input type="number" step="1" min="1" name="amount" required placeholder="500" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Payment Date *</label>
                        <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Disbursement Mode *</label>
                        <select name="payment_mode" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                            <option value="cash">Cash in Hand</option>
                            <option value="upi">UPI / GPay / PhonePe</option>
                            <option value="bank_transfer">Direct Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Notes / Reason</label>
                        <input type="text" name="reference_notes" placeholder="e.g. Travel allowance or medical emergency" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="advanceModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Record Advance</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Pay Part Modal --}}
        <div x-show="payPartModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="payPartModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Partial Wage Settlement</h3>
                    <button type="button" @click="payPartModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('wages.settle') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="worker_id" :value="selectedWorkerId">
                    <input type="hidden" name="settlement_action" value="pay_part">
                    <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">

                    <div>
                        <span class="text-xs text-slate-400">Worker</span>
                        <div class="font-bold text-slate-900 dark:text-white text-sm" x-text="selectedWorkerName"></div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400">Total Outstanding Payable</span>
                        <div class="font-black font-mono text-rose-600 text-lg">₹<span x-text="selectedTotalDue.toFixed(2)"></span></div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Amount to Pay Now (₹) *</label>
                        <input type="number" step="0.5" min="1" :max="selectedTotalDue" name="amount_to_pay" x-model="partAmount" required class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-[11px] text-slate-400 mt-1">Remaining balance will automatically be carried forward to next week.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Payment Mode *</label>
                        <select name="payment_mode" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="payPartModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Confirm Partial Payment</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
