<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Weekly Wages</h2>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('wages.signature-sheet', ['week' => $weekStart->toDateString()]) }}" target="_blank" class="btn-secondary">
                    Signature Sheet
                </a>
                <a href="{{ route('wages.export-csv', ['week' => $weekStart->toDateString()]) }}" class="btn-secondary">
                    Export CSV
                </a>
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
        .stat-value { font-size: 1.6rem; font-weight: 700; color: #0f172a; line-height: 1; }
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

    <div class="py-6 bg-slate-50 dark:bg-[#060913] min-h-screen"
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
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-sm font-medium" style="border-radius: 4px;">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-500/30 text-rose-800 dark:text-rose-300 text-sm font-medium" style="border-radius: 4px;">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Week Cycle Selector Bar --}}
            <div class="pg-card p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Payday Period:</span>
                    <form method="GET" action="{{ route('wages.index') }}" class="inline-block">
                        <select name="week" onchange="this.form.submit()" class="px-3 py-1.5 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-slate-800 dark:text-white" style="border-radius: 4px; height: 38px;">
                            @foreach($availableWeeks as $w)
                                <option value="{{ $w['val'] }}" {{ $weekStart->toDateString() === $w['val'] ? 'selected' : '' }}>
                                    {{ $w['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Active Cycle: <strong class="text-slate-900 dark:text-white">{{ $weekStart->format('d M') }} (Mon) – {{ $paydaySunday->format('d M Y') }} (Sun)</strong>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-label">Sunday Cash Payout</div>
                    <div class="stat-value text-blue-600 dark:text-blue-400">₹{{ number_format($totalDueAll, 2) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Brought Forward</div>
                    <div class="stat-value">₹{{ number_format($totalBroughtForwardAll, 2) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Earned This Week</div>
                    <div class="stat-value text-emerald-600 dark:text-emerald-400">₹{{ number_format($totalEarnedAll, 2) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Advances Taken</div>
                    <div class="stat-value text-amber-600 dark:text-amber-400">₹{{ number_format($totalAdvancesAll, 2) }}</div>
                </div>
            </div>

            {{-- Running Ledger Table --}}
            <div class="pg-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Worker Name</th>
                                <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Days Worked</th>
                                <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Cabling (m)</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Brought Forward</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Earned This Week</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Advances</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Total Payable</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Settlement Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                            @forelse($wageRows as $row)
                                @php
                                    $w = $row['worker'];
                                    $due = $row['total_due'];
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a href="{{ route('workers.show', $w) }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 hover:underline">
                                            {{ $w->name }}
                                        </a>
                                        <div class="text-xs text-slate-500 font-mono">₹{{ number_format($w->daily_rate, 2) }}/day</div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium text-slate-900 dark:text-slate-200">
                                        {{ $row['days_worked'] }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-400 font-mono">
                                        {{ $row['total_metres'] > 0 ? $row['total_metres'] . ' m' : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-400 font-mono">
                                        {{ $row['brought_forward'] > 0 ? '₹' . number_format($row['brought_forward'], 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-emerald-600 dark:text-emerald-400 font-mono">
                                        {{ $row['earned'] > 0 ? '₹' . number_format($row['earned'], 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-amber-600 dark:text-amber-400 font-mono">
                                        {{ $row['advances'] > 0 ? '-₹' . number_format($row['advances'], 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold font-mono text-sm {{ $due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                                        ₹{{ number_format($due, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <button type="button" @click="openAdvance({{ $w->id }}, '{{ addslashes($w->name) }}')" class="text-amber-600 hover:underline bg-transparent border-0 p-0 font-medium cursor-pointer">
                                                Advance
                                            </button>

                                            @if($due > 0)
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <form method="POST" action="{{ route('wages.settle') }}" class="inline" onsubmit="return confirm('Disburse full settlement of ₹{{ number_format($due, 2) }} to {{ addslashes($w->name) }}?')">
                                                    @csrf
                                                    <input type="hidden" name="worker_id" value="{{ $w->id }}">
                                                    <input type="hidden" name="settlement_action" value="pay_full">
                                                    <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">
                                                    <input type="hidden" name="payment_mode" value="cash">
                                                    <button type="submit" class="text-emerald-600 hover:underline bg-transparent border-0 p-0 font-bold cursor-pointer">
                                                        Pay Full
                                                    </button>
                                                </form>

                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <button type="button" @click="openPayPart({{ $w->id }}, '{{ addslashes($w->name) }}', {{ $due }})" class="text-blue-600 hover:underline bg-transparent border-0 p-0 font-medium cursor-pointer">
                                                    Pay Part
                                                </button>

                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <form method="POST" action="{{ route('wages.settle') }}" class="inline" onsubmit="return confirm('Carry forward balance of ₹{{ number_format($due, 2) }} to next week?')">
                                                    @csrf
                                                    <input type="hidden" name="worker_id" value="{{ $w->id }}">
                                                    <input type="hidden" name="settlement_action" value="carry_forward">
                                                    <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">
                                                    <input type="hidden" name="payment_mode" value="cash">
                                                    <button type="submit" class="text-slate-500 hover:underline bg-transparent border-0 p-0 font-medium cursor-pointer">
                                                        Carry Fwd
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                                    Settled
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                        No active workers found in system.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Advance Modal --}}
            <div x-show="advanceModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60">
                <div @click.away="advanceModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 space-y-4" style="border-radius: 4px;">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">Record Advance Payout</h3>
                        <button type="button" @click="advanceModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('wages.advance') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="worker_id" :value="selectedWorkerId">

                        <div>
                            <span class="text-xs text-slate-500">Worker</span>
                            <div class="font-bold text-slate-900 dark:text-white text-sm" x-text="selectedWorkerName"></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Advance Amount (₹) *</label>
                            <input type="number" step="1" min="1" name="amount" required placeholder="500" class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-slate-900 dark:text-white font-mono" style="border-radius: 4px; height: 38px;">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Date *</label>
                            <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-slate-900 dark:text-white" style="border-radius: 4px; height: 38px;">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Disbursement Mode *</label>
                            <select name="payment_mode" class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-slate-900 dark:text-white" style="border-radius: 4px; height: 38px;">
                                <option value="cash">Cash in Hand</option>
                                <option value="upi">UPI / GPay / PhonePe</option>
                                <option value="bank_transfer">Direct Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes / Reason</label>
                            <input type="text" name="reference_notes" placeholder="e.g. Travel allowance" class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-slate-900 dark:text-white" style="border-radius: 4px; height: 38px;">
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="advanceModalOpen = false" class="px-3 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition" style="border-radius: 4px;">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition" style="border-radius: 4px;">Record Advance</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Pay Part Modal --}}
            <div x-show="payPartModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60">
                <div @click.away="payPartModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 space-y-4" style="border-radius: 4px;">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">Partial Wage Settlement</h3>
                        <button type="button" @click="payPartModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('wages.settle') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="worker_id" :value="selectedWorkerId">
                        <input type="hidden" name="settlement_action" value="pay_part">
                        <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">

                        <div>
                            <span class="text-xs text-slate-500">Worker</span>
                            <div class="font-bold text-slate-900 dark:text-white text-sm" x-text="selectedWorkerName"></div>
                        </div>

                        <div>
                            <span class="text-xs text-slate-500">Total Outstanding Payable</span>
                            <div class="font-bold font-mono text-rose-600 text-lg">₹<span x-text="selectedTotalDue.toFixed(2)"></span></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Amount to Pay Now (₹) *</label>
                            <input type="number" step="0.5" min="1" :max="selectedTotalDue" name="amount_to_pay" x-model="partAmount" required class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-slate-900 dark:text-white font-mono" style="border-radius: 4px; height: 38px;">
                            <p class="text-xs text-slate-500 mt-1">Remaining balance will automatically be carried forward to next week.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Mode *</label>
                            <select name="payment_mode" class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-slate-900 dark:text-white" style="border-radius: 4px; height: 38px;">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="payPartModalOpen = false" class="px-3 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition" style="border-radius: 4px;">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition" style="border-radius: 4px;">Confirm Partial Payment</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
