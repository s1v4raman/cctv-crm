<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Weekly Wages</h2>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('workers.attendance.index') }}" class="btn-secondary">
                    Attendance Maintenance
                </a>
                <a href="{{ route('workers.index') }}" class="btn-secondary">
                    Workers
                </a>
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
            .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .stat-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); }
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
             analysisModalOpen: false,
             selectedWorkerId: null,
             selectedWorkerName: '',
             selectedTotalDue: 0,
             partAmount: 0,
             selectedAnalysis: null,
             photoModalUrl: null,
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
             },
             openAnalysis(data) {
                 this.selectedAnalysis = data;
                 this.analysisModalOpen = true;
             },
             openPhoto(url) {
                 this.photoModalUrl = url;
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
                    <div class="stat-label">Sunday Cash Due</div>
                    <div class="stat-value text-rose-600 dark:text-rose-400">₹{{ number_format($totalDueAll, 2) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Paid This Week</div>
                    <div class="stat-value text-emerald-600 dark:text-emerald-400">₹{{ number_format($totalWagesPaidAll, 2) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Brought Forward</div>
                    <div class="stat-value text-slate-800 dark:text-slate-100">₹{{ number_format($totalBroughtForwardAll, 2) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Earned This Week</div>
                    <div class="stat-value text-indigo-600 dark:text-indigo-400">₹{{ number_format($totalEarnedAll, 2) }}</div>
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
                                <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Cameras</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Brought Forward</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Earned This Week</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Advances</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Paid / Settled</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Total Payable</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Settlement Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                            @forelse($wageRows as $row)
                                @php
                                    $w = $row['worker'];
                                    $due = $row['total_due'];

                                    $rowData = [
                                        'id' => $w->id,
                                        'name' => $w->name,
                                        'phone' => $w->phone ?? '—',
                                        'worker_id' => $w->id,
                                        'worker_name' => $w->name,
                                        'worker_phone' => $w->phone ?? '—',
                                        'daily_rate' => (float)$w->daily_rate,
                                        'days_worked' => $row['days_worked'],
                                        'total_metres' => (float)$row['total_metres'],
                                        'total_cameras' => (int)($row['total_cameras'] ?? 0),
                                        'prior_days_worked' => (int)($row['prior_days_worked'] ?? 0),
                                        'prior_metres' => (float)($row['prior_metres'] ?? 0),
                                        'prior_cameras' => (int)($row['prior_cameras'] ?? 0),
                                        'brought_forward' => (float)$row['brought_forward'],
                                        'earned' => (float)$row['earned'],
                                        'advances' => (float)$row['advances'],
                                        'wages_paid' => (float)$row['wages_paid'],
                                        'total_due' => (float)$due,
                                        'is_carried_forward' => (bool)$row['is_carried_forward'],
                                        'carried_forward_balance' => (float)$row['carried_forward_balance'],
                                        'already_settled' => (bool)$row['already_settled'],
                                        'attendances' => $row['attendances']->map(function($a) {
                                            return [
                                                'id' => $a->id,
                                                'date' => $a->workDay->work_date->format('d M Y (D)'),
                                                'project_code' => $a->workDay->project->project_code ?? 'PRJ',
                                                'project_title' => $a->workDay->project->title ?? 'Project',
                                                'project_id' => $a->workDay->project_id,
                                                'attendance_type' => $a->attendance_type === 'full_day' ? 'Full Day' : 'Half Day',
                                                'daily_rate' => (float)$a->daily_rate_snapshot,
                                                'cabling_metres' => (float)$a->cabling_metres,
                                                'cabling_amount' => (float)$a->cabling_amount,
                                                'cameras' => (int)$a->cameras_installed,
                                                'extra_amount' => (float)$a->extra_amount,
                                                'extra_description' => $a->extra_description,
                                                'total_amount' => (float)$a->total_amount,
                                                'photo_url' => $a->photo_url,
                                                'is_paid' => (bool)$a->is_paid,
                                                'notes' => $a->notes ?: $a->work_summary,
                                            ];
                                        })->values(),
                                        'prior_attendances' => ($row['prior_attendances'] ?? collect())->map(function($a) {
                                            return [
                                                'id' => $a->id,
                                                'date' => $a->workDay->work_date->format('d M Y (D)'),
                                                'project_code' => $a->workDay->project->project_code ?? 'PRJ',
                                                'project_title' => $a->workDay->project->title ?? 'Project',
                                                'project_id' => $a->workDay->project_id,
                                                'attendance_type' => $a->attendance_type === 'full_day' ? 'Full Day' : 'Half Day',
                                                'daily_rate' => (float)$a->daily_rate_snapshot,
                                                'cabling_metres' => (float)$a->cabling_metres,
                                                'cabling_amount' => (float)$a->cabling_amount,
                                                'cameras' => (int)$a->cameras_installed,
                                                'extra_amount' => (float)$a->extra_amount,
                                                'extra_description' => $a->extra_description,
                                                'total_amount' => (float)$a->total_amount,
                                                'photo_url' => $a->photo_url,
                                                'is_paid' => (bool)$a->is_paid,
                                                'notes' => $a->notes ?: $a->work_summary,
                                            ];
                                        })->values(),
                                        'advances_list' => ($row['worker_advances'] ?? collect())->map(function($adv) {
                                            return [
                                                'id' => $adv->id,
                                                'date' => $adv->payment_date ? $adv->payment_date->format('d M Y') : '—',
                                                'amount' => (float)$adv->amount,
                                                'mode' => strtoupper($adv->payment_mode ?? 'cash'),
                                                'notes' => $adv->reference_notes ?: ($adv->notes ?: 'Advance'),
                                            ];
                                        })->values(),
                                        'payouts_list' => ($row['worker_payouts'] ?? collect())->map(function($p) {
                                            return [
                                                'id' => $p->id,
                                                'date' => $p->payment_date ? $p->payment_date->format('d M Y') : '—',
                                                'amount' => (float)$p->amount,
                                                'mode' => strtoupper($p->payment_mode ?? 'cash'),
                                                'notes' => $p->reference_notes ?: ($p->notes ?: 'Wage Payout'),
                                            ];
                                        })->values(),
                                    ];
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div>
                                                @if($w)
                                                    <a href="{{ route('workers.show', $w) }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 hover:underline">
                                                        {{ $w->name }}
                                                    </a>
                                                    @if(method_exists($w, 'trashed') && $w->trashed())
                                                        <span class="text-[9px] px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-medium">Archived</span>
                                                    @endif
                                                    <div class="text-xs text-slate-500 font-mono">₹{{ number_format($w->daily_rate, 2) }}/day</div>
                                                @else
                                                    <span class="font-bold text-slate-500">Worker #{{ $row['worker_id'] ?? '—' }}</span>
                                                @endif
                                            </div>
                                            <button type="button" @click='openAnalysis(@json($rowData))' class="px-2 py-0.5 text-[11px] font-semibold rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/60 transition cursor-pointer" title="View Shift Analysis, Verified Photos, and Advances">
                                                Analysis ({{ $row['days_worked'] > 0 ? $row['days_worked'] : ($row['prior_days_worked'] > 0 ? $row['prior_days_worked'] . ' b/f' : 0) }})
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium text-slate-900 dark:text-slate-200">
                                        <button type="button" @click='openAnalysis(@json($rowData))' class="font-mono font-bold hover:underline hover:text-blue-600 cursor-pointer">
                                            @if($row['days_worked'] > 0)
                                                {{ $row['days_worked'] }}
                                                @if(($row['prior_days_worked'] ?? 0) > 0)
                                                    <span class="text-[10px] font-semibold text-blue-600 block">(+{{ $row['prior_days_worked'] }} b/f)</span>
                                                @endif
                                            @elseif(($row['prior_days_worked'] ?? 0) > 0)
                                                <span class="text-slate-800 dark:text-slate-200">0</span>
                                                <span class="text-[10px] font-bold text-blue-600 block leading-tight" title="Completed in previous shift, rolled over as Brought Forward">(+{{ $row['prior_days_worked'] }} b/f)</span>
                                            @else
                                                <span class="text-slate-400">0</span>
                                            @endif
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-400 font-mono">
                                        @if($row['total_metres'] > 0)
                                            <span class="font-semibold text-slate-900 dark:text-slate-200">{{ $row['total_metres'] }} m</span>
                                            @if(($row['prior_metres'] ?? 0) > 0)
                                                <span class="text-[10px] text-blue-600 block">(+{{ $row['prior_metres'] }}m b/f)</span>
                                            @endif
                                        @elseif(($row['prior_metres'] ?? 0) > 0)
                                            <span class="font-semibold text-blue-600 dark:text-blue-400 block" title="Completed in previous shift, rolled over as Brought Forward">{{ $row['prior_metres'] }} m</span>
                                            <span class="text-[10px] text-slate-400 block">(b/f shift)</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono">
                                        @if($row['total_cameras'] > 0)
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $row['total_cameras'] }}</span>
                                            @if(($row['prior_cameras'] ?? 0) > 0)
                                                <span class="text-[10px] text-blue-600 block">(+{{ $row['prior_cameras'] }} b/f)</span>
                                            @endif
                                        @elseif(($row['prior_cameras'] ?? 0) > 0)
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400 block" title="Installed in previous shift, rolled over as Brought Forward">{{ $row['prior_cameras'] }}</span>
                                            <span class="text-[10px] text-slate-400 block">(b/f shift)</span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-400 font-mono">
                                        {{ $row['brought_forward'] > 0 ? '₹' . number_format($row['brought_forward'], 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-emerald-600 dark:text-emerald-400 font-mono">
                                        <button type="button" @click='openAnalysis(@json($rowData))' class="hover:underline font-mono font-semibold {{ $row['earned'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                            {{ $row['earned'] > 0 ? '₹' . number_format($row['earned'], 2) : '—' }}
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium font-mono">
                                        @if($row['advances'] > 0)
                                            <button type="button" @click='openAnalysis(@json($rowData))' class="text-amber-600 dark:text-amber-400 hover:underline font-bold cursor-pointer" title="Click to view advance breakdown">
                                                -₹{{ number_format($row['advances'], 2) }}
                                            </button>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono">
                                        @if($row['is_carried_forward'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300">
                                                Carried Fwd
                                            </span>
                                        @elseif($row['wages_paid'] > 0)
                                            <button type="button" @click='openAnalysis(@json($rowData))' class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                                ₹{{ number_format($row['wages_paid'], 2) }}
                                            </button>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold font-mono text-sm {{ $due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                                        @if($row['is_carried_forward'])
                                            <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 block">Deferred to next week</span>
                                            <span class="text-xs text-slate-400 font-mono">₹{{ number_format($row['carried_forward_balance'], 2) }}</span>
                                        @else
                                            ₹{{ number_format($due, 2) }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <button type="button" @click="openAdvance({{ $w->id }}, '{{ addslashes($w->name) }}')" class="text-amber-600 hover:underline bg-transparent border-0 p-0 font-medium cursor-pointer">
                                                Advance
                                            </button>

                                            @if($row['is_carried_forward'])
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                    Carried Forward
                                                </span>
                                                <form method="POST" action="{{ route('wages.settle') }}" class="inline ml-1" data-confirm="Disburse settlement of ₹{{ number_format($row['carried_forward_balance'], 2) }} now to {{ $w->name }}?">
                                                    @csrf
                                                    <input type="hidden" name="worker_id" value="{{ $w->id }}">
                                                    <input type="hidden" name="settlement_action" value="pay_full">
                                                    <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">
                                                    <input type="hidden" name="payment_mode" value="cash">
                                                    <button type="submit" class="text-emerald-600 hover:underline text-xs bg-transparent border-0 p-0 font-bold cursor-pointer">
                                                        Pay Now
                                                    </button>
                                                </form>
                                            @elseif($due > 0)
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <form method="POST" action="{{ route('wages.settle') }}" class="inline" data-confirm="Disburse full settlement of ₹{{ number_format($due, 2) }} to {{ $w->name }}?">
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
                                                <form method="POST" action="{{ route('wages.settle') }}" class="inline" data-confirm="Carry forward remaining balance of ₹{{ number_format($due, 2) }} to next week?">
                                                    @csrf
                                                    <input type="hidden" name="worker_id" value="{{ $w->id }}">
                                                    <input type="hidden" name="settlement_action" value="carry_forward">
                                                    <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">
                                                    <input type="hidden" name="payment_mode" value="cash">
                                                    <button type="submit" class="text-slate-500 hover:underline bg-transparent border-0 p-0 font-medium cursor-pointer">
                                                        Carry Fwd
                                                    </button>
                                                </form>
                                            @elseif($row['already_settled'] || $row['wages_paid'] > 0)
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                                    ✓ Settled
                                                </span>
                                            @else
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <span class="text-slate-400 text-xs">
                                                    No Shifts
                                                </span>
                                            @endif

                                            <span class="text-slate-300 dark:text-slate-600">|</span>
                                            <button type="button" @click='openAnalysis(@json($rowData))' class="text-blue-600 hover:underline bg-transparent border-0 p-0 text-xs font-semibold cursor-pointer">
                                                Shifts &amp; Photos
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-slate-500">
                                        No active workers found in system.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($wageRows) > 0)
                            <tfoot class="bg-slate-50 dark:bg-slate-800/80 font-bold border-t border-slate-200 dark:border-slate-700">
                                <tr>
                                    <td class="px-4 py-3 text-slate-800 dark:text-slate-200">Totals</td>
                                    <td class="px-4 py-3 text-center text-slate-800 dark:text-slate-200">{{ collect($wageRows)->sum('days_worked') }}</td>
                                    <td class="px-4 py-3 text-center text-slate-800 dark:text-slate-200 font-mono">{{ collect($wageRows)->sum('total_metres') > 0 ? collect($wageRows)->sum('total_metres') . ' m' : '—' }}</td>
                                    <td class="px-4 py-3 text-center text-slate-800 dark:text-slate-200 font-mono">{{ collect($wageRows)->sum('total_cameras') > 0 ? collect($wageRows)->sum('total_cameras') : '—' }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-700 dark:text-slate-300">₹{{ number_format($totalBroughtForwardAll, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-emerald-600 dark:text-emerald-400">₹{{ number_format($totalEarnedAll, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-amber-600 dark:text-amber-400">-₹{{ number_format($totalAdvancesAll, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-emerald-600 dark:text-emerald-400">₹{{ number_format($totalWagesPaidAll, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-rose-600 dark:text-rose-400">₹{{ number_format($totalDueAll, 2) }}</td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- This Week's Payout & Settlement History --}}
            @if(isset($recentPayments) && $recentPayments->isNotEmpty())
                <div class="pg-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-800/40">
                        <h3 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2.5">
                            <span>This Week's Payout & Settlement History</span>
                            <span class="px-2.5 py-0.5 text-xs rounded-full bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 font-medium border border-slate-200 dark:border-slate-700 shadow-xs">
                                {{ $recentPayments->count() }} records
                            </span>
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800/60">
                                <tr>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Date & Time</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Worker</th>
                                    <th class="px-5 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Type</th>
                                    <th class="px-5 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Mode</th>
                                    <th class="px-5 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Amount Paid</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Notes / Reason</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Settled By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                                @foreach($recentPayments as $p)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                        <td class="px-5 py-3 whitespace-nowrap text-xs font-mono text-slate-600 dark:text-slate-400">
                                            {{ $p->created_at ? $p->created_at->format('d M Y, h:i A') : $p->payment_date->format('d M Y') }}
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap font-medium text-slate-900 dark:text-white">
                                            @if($p->worker_id)
                                                <a href="{{ route('workers.show', $p->worker_id) }}" class="hover:text-blue-600 hover:underline">
                                                    {{ $p->worker?->name ?? 'Worker #' . $p->worker_id }}
                                                </a>
                                            @else
                                                <span class="text-slate-500">{{ $p->worker?->name ?? 'Unknown Worker' }}</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-center whitespace-nowrap">
                                            @if($p->amount == 0 && (str_contains(strtolower($p->reference_notes ?? ''), 'carried forward') || str_contains(strtolower($p->reference_notes ?? ''), 'carry_forward')))
                                                <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300">
                                                    Carry Forward
                                                </span>
                                            @elseif($p->payment_type === 'advance')
                                                <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300">
                                                    Advance
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300">
                                                    Wage Payout
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-center whitespace-nowrap uppercase text-xs font-semibold text-slate-600 dark:text-slate-400">
                                            {{ $p->payment_mode ?? 'cash' }}
                                        </td>
                                        <td class="px-5 py-3 text-right whitespace-nowrap font-mono font-bold {{ $p->amount > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                            ₹{{ number_format($p->amount, 2) }}
                                        </td>
                                        <td class="px-5 py-3 text-xs text-slate-600 dark:text-slate-400 max-w-xs truncate" title="{{ $p->reference_notes }}">
                                            {{ $p->reference_notes ?: '—' }}
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap text-xs text-slate-500">
                                            {{ $p->settledBy?->name ?? 'System' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

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

            {{-- Worker Shift, Photo & Financial Analysis Modal --}}
            <div x-show="analysisModalOpen" 
                 x-cloak 
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
                 @keydown.escape.window="if (photoModalUrl) { photoModalUrl = null; } else if (analysisModalOpen) { analysisModalOpen = false; }">
                <div @click.away="if (!photoModalUrl) analysisModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-lg max-w-4xl w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-start justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="font-bold text-slate-900 dark:text-white text-lg" x-text="selectedAnalysis?.name"></h3>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    Base: ₹<span x-text="selectedAnalysis?.daily_rate?.toFixed(2)"></span>/day
                                </span>
                                <span x-show="selectedAnalysis?.phone" class="text-xs text-slate-500 font-mono" x-text="selectedAnalysis?.phone"></span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Detailed work shifts, punch-in verification photos, advance payouts, and settlement ledger for current week.
                            </p>
                        </div>
                        <button type="button" @click="analysisModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-2xl leading-none">&times;</button>
                    </div>

                    {{-- Summary Bento KPI Cards --}}
                    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
                        <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider block">Shifts Worked</span>
                            <span class="text-xl font-bold font-mono text-slate-900 dark:text-white" x-text="selectedAnalysis?.days_worked ?? 0"></span>
                            <span class="text-[11px] text-slate-400 block" x-show="selectedAnalysis?.total_metres > 0">
                                <span x-text="selectedAnalysis?.total_metres"></span>m cabling
                            </span>
                            <span class="text-[11px] text-blue-600 block" x-show="selectedAnalysis?.prior_days_worked > 0">
                                +<span x-text="selectedAnalysis?.prior_days_worked"></span> b/f shifts
                            </span>
                        </div>
                        <div class="p-3 rounded-lg bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800">
                            <span class="text-[11px] font-medium text-indigo-600 uppercase tracking-wider block">Cameras Installed</span>
                            <span class="text-xl font-bold font-mono text-indigo-700 dark:text-indigo-400" x-text="selectedAnalysis?.total_cameras ?? 0"></span>
                            <span class="text-[11px] text-indigo-600 block" x-show="selectedAnalysis?.prior_cameras > 0">
                                +<span x-text="selectedAnalysis?.prior_cameras"></span> in b/f shift
                            </span>
                        </div>
                        <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800">
                            <span class="text-[11px] font-medium text-emerald-600 uppercase tracking-wider block">Total Earned</span>
                            <span class="text-xl font-bold font-mono text-emerald-700 dark:text-emerald-400">
                                ₹<span x-text="selectedAnalysis?.earned?.toFixed(2) ?? '0.00'"></span>
                            </span>
                            <span class="text-[11px] text-emerald-600 block" x-show="selectedAnalysis?.brought_forward > 0">
                                +₹<span x-text="selectedAnalysis?.brought_forward?.toFixed(2)"></span> b/f
                            </span>
                        </div>
                        <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                            <span class="text-[11px] font-medium text-amber-600 uppercase tracking-wider block">Advances</span>
                            <span class="text-xl font-bold font-mono text-amber-700 dark:text-amber-400">
                                -₹<span x-text="selectedAnalysis?.advances?.toFixed(2) ?? '0.00'"></span>
                            </span>
                            <span class="text-[11px] text-amber-600 block">Deducted</span>
                        </div>
                        <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800">
                            <span class="text-[11px] font-medium text-blue-600 uppercase tracking-wider block">Paid / Settled</span>
                            <span class="text-xl font-bold font-mono text-blue-700 dark:text-blue-400">
                                ₹<span x-text="selectedAnalysis?.wages_paid?.toFixed(2) ?? '0.00'"></span>
                            </span>
                            <span class="text-[11px] text-blue-600 block">Disbursed</span>
                        </div>
                        <div class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 col-span-2 sm:col-span-1">
                            <span class="text-[11px] font-medium text-rose-600 uppercase tracking-wider block">Net Cost to Pay</span>
                            <span class="text-xl font-bold font-mono text-rose-700 dark:text-rose-400">
                                ₹<span x-text="selectedAnalysis?.total_due?.toFixed(2) ?? '0.00'"></span>
                            </span>
                            <span class="text-[11px] text-rose-600 block">Pending</span>
                        </div>
                    </div>

                    {{-- Section 1: Daily Shifts & Punch Photo Verification (This Week) --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                <span>Daily Attendance &amp; Shift Verification (This Week)</span>
                                <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 dark:bg-slate-800 font-normal text-slate-600 dark:text-slate-400" x-text="(selectedAnalysis?.attendances?.length ?? 0) + ' shift(s)'"></span>
                            </h4>
                            <span class="text-xs text-slate-500">Click photo thumbnail to view full punch selfie</span>
                        </div>

                        <div class="border border-slate-200 dark:border-slate-800 rounded overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 font-semibold text-slate-700 dark:text-slate-300">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Date &amp; Project</th>
                                        <th class="px-3 py-2 text-center">Shift Type</th>
                                        <th class="px-3 py-2 text-center">Punch Photo</th>
                                        <th class="px-3 py-2 text-center">Cameras</th>
                                        <th class="px-3 py-2 text-right">Base Wage</th>
                                        <th class="px-3 py-2 text-right">Cabling Work</th>
                                        <th class="px-3 py-2 text-right">Extras</th>
                                        <th class="px-3 py-2 text-right font-bold">Day Total</th>
                                        <th class="px-3 py-2 text-center">Status / Payout Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                                    <template x-for="att in selectedAnalysis?.attendances" :key="att.id">
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                <div class="font-semibold text-slate-900 dark:text-white" x-text="att.date"></div>
                                                <div class="text-[11px] text-slate-500">
                                                    <span class="font-mono" x-text="att.project_code"></span> — <span x-text="att.project_title"></span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-center whitespace-nowrap">
                                                <span :class="att.attendance_type === 'Full Day' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-950/70 dark:text-purple-300'" class="px-2 py-0.5 rounded text-[11px] font-semibold" x-text="att.attendance_type"></span>
                                            </td>
                                            <td class="px-3 py-2 text-center whitespace-nowrap">
                                                <template x-if="att.photo_url">
                                                    <button type="button" @click.stop="openPhoto(att.photo_url)" class="inline-block relative group cursor-pointer border border-slate-200 dark:border-slate-700 rounded overflow-hidden shadow-xs hover:border-blue-500 transition" title="Click to view full photo">
                                                        <img :src="att.photo_url" alt="Punch selfie" class="w-10 h-10 object-cover">
                                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[10px] font-bold transition">
                                                            View
                                                        </div>
                                                    </button>
                                                </template>
                                                <template x-if="!att.photo_url">
                                                    <span class="text-slate-400 italic text-[11px]">No photo</span>
                                                </template>
                                            </td>
                                            <td class="px-3 py-2 text-center font-mono font-bold text-indigo-600 whitespace-nowrap">
                                                <span x-text="att.cameras > 0 ? att.cameras + ' cams' : '—'"></span>
                                            </td>
                                            <td class="px-3 py-2 text-right font-mono font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                                ₹<span x-text="att.daily_rate.toFixed(2)"></span>
                                            </td>
                                            <td class="px-3 py-2 text-right font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                                <template x-if="att.cabling_metres > 0">
                                                    <div>
                                                        <span x-text="att.cabling_metres"></span>m
                                                        <span class="text-emerald-600 font-semibold" x-show="att.cabling_amount > 0">
                                                            (+₹<span x-text="att.cabling_amount.toFixed(2)"></span>)
                                                        </span>
                                                    </div>
                                                </template>
                                                <template x-if="!att.cabling_metres || att.cabling_metres == 0">
                                                    <span>—</span>
                                                </template>
                                            </td>
                                            <td class="px-3 py-2 text-right font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                                <template x-if="att.extra_amount > 0">
                                                    <span class="text-emerald-600 font-semibold" :title="att.extra_description">
                                                        +₹<span x-text="att.extra_amount.toFixed(2)"></span>
                                                    </span>
                                                </template>
                                                <template x-if="!att.extra_amount || att.extra_amount == 0">
                                                    <span>—</span>
                                                </template>
                                            </td>
                                            <td class="px-3 py-2 text-right font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                                ₹<span x-text="att.total_amount.toFixed(2)"></span>
                                            </td>
                                            <td class="px-3 py-2 text-center whitespace-nowrap">
                                                <template x-if="att.is_paid">
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300">
                                                        <span>✓ Paid &amp; Settled</span>
                                                    </span>
                                                </template>
                                                <template x-if="!att.is_paid">
                                                    <form :action="'{{ url('/wages/attendances') }}/' + att.id + '/pay'" method="POST" class="inline" data-confirm="Disburse daily shift payout for this date?">
                                                        @csrf
                                                        <input type="hidden" name="payment_mode" value="cash">
                                                        <button type="submit" class="px-2.5 py-1 rounded text-[11px] font-bold bg-blue-600 hover:bg-blue-700 text-white transition cursor-pointer shadow-xs">
                                                            Pay Day Shift (₹<span x-text="att.total_amount.toFixed(0)"></span>)
                                                        </button>
                                                    </form>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="!selectedAnalysis?.attendances || selectedAnalysis?.attendances.length === 0">
                                        <tr>
                                            <td colspan="9" class="px-4 py-6 text-center text-slate-400">
                                                No attendance shifts recorded for this worker in the selected week.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Section 2: Prior Unpaid Work Shifts Contributing to Brought Forward --}}
                    <template x-if="selectedAnalysis?.prior_attendances && selectedAnalysis?.prior_attendances.length > 0">
                        <div class="space-y-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-bold">ℹ</span>
                                    <span>Prior Work Shifts &amp; Work Report (Brought Forward: ₹<span x-text="selectedAnalysis?.brought_forward?.toFixed(2)"></span>)</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 font-semibold" x-text="selectedAnalysis?.prior_attendances.length + ' prior shift(s)'"></span>
                                </h4>
                                <span class="text-xs text-blue-600 font-medium">Work completed before this week cycle, rolled over for payment</span>
                            </div>

                            <div class="border border-blue-200 dark:border-blue-900/60 rounded overflow-x-auto bg-blue-50/20">
                                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-xs">
                                    <thead class="bg-blue-50 dark:bg-blue-950/60 font-semibold text-slate-700 dark:text-slate-300">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Date &amp; Project</th>
                                            <th class="px-3 py-2 text-center">Shift Type</th>
                                            <th class="px-3 py-2 text-center">Punch Photo</th>
                                            <th class="px-3 py-2 text-center">Cameras</th>
                                            <th class="px-3 py-2 text-right">Base Wage</th>
                                            <th class="px-3 py-2 text-right">Cabling Work</th>
                                            <th class="px-3 py-2 text-right">Extras</th>
                                            <th class="px-3 py-2 text-right font-bold">Day Total</th>
                                            <th class="px-3 py-2 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                                        <template x-for="patt in selectedAnalysis?.prior_attendances" :key="patt.id">
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <div class="font-semibold text-slate-900 dark:text-white" x-text="patt.date"></div>
                                                    <div class="text-[11px] text-slate-500">
                                                        <span class="font-mono" x-text="patt.project_code"></span> — <span x-text="patt.project_title"></span>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                                    <span :class="patt.attendance_type === 'Full Day' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-950/70 dark:text-purple-300'" class="px-2 py-0.5 rounded text-[11px] font-semibold" x-text="patt.attendance_type"></span>
                                                </td>
                                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                                    <template x-if="patt.photo_url">
                                                        <button type="button" @click.stop="openPhoto(patt.photo_url)" class="inline-block relative group cursor-pointer border border-slate-200 dark:border-slate-700 rounded overflow-hidden shadow-xs hover:border-blue-500 transition" title="Click to view full photo">
                                                            <img :src="patt.photo_url" alt="Punch selfie" class="w-10 h-10 object-cover">
                                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[10px] font-bold transition">
                                                                View
                                                            </div>
                                                        </button>
                                                    </template>
                                                    <template x-if="!patt.photo_url">
                                                        <span class="text-slate-400 italic text-[11px]">No photo</span>
                                                    </template>
                                                </td>
                                                <td class="px-3 py-2 text-center font-mono font-bold text-indigo-600 whitespace-nowrap">
                                                    <span x-text="patt.cameras > 0 ? patt.cameras + ' cams' : '—'"></span>
                                                </td>
                                                <td class="px-3 py-2 text-right font-mono font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                                    ₹<span x-text="patt.daily_rate.toFixed(2)"></span>
                                                </td>
                                                <td class="px-3 py-2 text-right font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                                    <template x-if="patt.cabling_metres > 0">
                                                        <div>
                                                            <span x-text="patt.cabling_metres"></span>m
                                                            <span class="text-emerald-600 font-semibold" x-show="patt.cabling_amount > 0">
                                                                (+₹<span x-text="patt.cabling_amount.toFixed(2)"></span>)
                                                            </span>
                                                        </div>
                                                    </template>
                                                    <template x-if="!patt.cabling_metres || patt.cabling_metres == 0">
                                                        <span>—</span>
                                                    </template>
                                                </td>
                                                <td class="px-3 py-2 text-right font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                                    <template x-if="patt.extra_amount > 0">
                                                        <span class="text-emerald-600 font-semibold" :title="patt.extra_description">
                                                            +₹<span x-text="patt.extra_amount.toFixed(2)"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!patt.extra_amount || patt.extra_amount == 0">
                                                        <span>—</span>
                                                    </template>
                                                </td>
                                                <td class="px-3 py-2 text-right font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                                    ₹<span x-text="patt.total_amount.toFixed(2)"></span>
                                                </td>
                                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300">
                                                        Brought Forward
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    {{-- Section 2: Advances & Previous Settlements Lists --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        {{-- Advances List --}}
                        <div class="space-y-2">
                            <h5 class="font-bold text-xs text-slate-700 dark:text-slate-300 flex items-center justify-between">
                                <span>Advances Deducted (This Week)</span>
                                <span class="font-mono text-amber-600 font-bold" x-text="'-₹' + (selectedAnalysis?.advances?.toFixed(2) ?? '0.00')"></span>
                            </h5>
                            <div class="border border-slate-200 dark:border-slate-800 rounded max-h-36 overflow-y-auto">
                                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                                        <template x-for="adv in selectedAnalysis?.advances_list" :key="adv.id">
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                                <td class="px-3 py-1.5 font-medium text-slate-700 dark:text-slate-300" x-text="adv.date"></td>
                                                <td class="px-3 py-1.5 text-center uppercase font-mono text-slate-500 text-[10px]" x-text="adv.mode"></td>
                                                <td class="px-3 py-1.5 text-right font-mono font-bold text-amber-600">-₹<span x-text="adv.amount.toFixed(2)"></span></td>
                                            </tr>
                                        </template>
                                        <template x-if="!selectedAnalysis?.advances_list || selectedAnalysis?.advances_list.length === 0">
                                            <tr>
                                                <td colspan="3" class="px-3 py-3 text-center text-slate-400 text-xs">No advances taken this week</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Wage Payouts List --}}
                        <div class="space-y-2">
                            <h5 class="font-bold text-xs text-slate-700 dark:text-slate-300 flex items-center justify-between">
                                <span>Wage Payouts / Settlements (This Week)</span>
                                <span class="font-mono text-emerald-600 font-bold" x-text="'₹' + (selectedAnalysis?.wages_paid?.toFixed(2) ?? '0.00')"></span>
                            </h5>
                            <div class="border border-slate-200 dark:border-slate-800 rounded max-h-36 overflow-y-auto">
                                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                                        <template x-for="payout in selectedAnalysis?.payouts_list" :key="payout.id">
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                                <td class="px-3 py-1.5 font-medium text-slate-700 dark:text-slate-300" x-text="payout.date"></td>
                                                <td class="px-3 py-1.5 text-center uppercase font-mono text-slate-500 text-[10px]" x-text="payout.mode"></td>
                                                <td class="px-3 py-1.5 text-right font-mono font-bold text-emerald-600">₹<span x-text="payout.amount.toFixed(2)"></span></td>
                                            </tr>
                                        </template>
                                        <template x-if="!selectedAnalysis?.payouts_list || selectedAnalysis?.payouts_list.length === 0">
                                            <tr>
                                                <td colspan="3" class="px-3 py-3 text-center text-slate-400 text-xs">No settlements recorded yet</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer Actions --}}
                    <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="analysisModalOpen = false" class="px-4 py-2 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            Close
                        </button>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="openAdvance(selectedAnalysis.id, selectedAnalysis.name)" class="px-3 py-2 rounded bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 text-sm font-semibold transition">
                                + Record Advance
                            </button>

                            <template x-if="selectedAnalysis?.total_due > 0">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="openPayPart(selectedAnalysis.id, selectedAnalysis.name, selectedAnalysis.total_due)" class="px-3 py-2 rounded bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-700 hover:bg-blue-100 text-sm font-semibold transition">
                                        Pay Part
                                    </button>

                                    <form method="POST" action="{{ route('wages.settle') }}" class="inline" data-confirm="Disburse full remaining weekly settlement?">
                                        @csrf
                                        <input type="hidden" name="worker_id" :value="selectedAnalysis.id">
                                        <input type="hidden" name="settlement_action" value="pay_full">
                                        <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">
                                        <input type="hidden" name="payment_mode" value="cash">
                                        <button type="submit" class="px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow transition">
                                            Pay Full (₹<span x-text="selectedAnalysis?.total_due?.toFixed(2)"></span>)
                                        </button>
                                    </form>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>

                {{-- Full Size Punch Photo Lightbox (Displayed directly in front of the analysis modal) --}}
                <div x-show="photoModalUrl !== null" 
                     x-cloak 
                     class="fixed inset-0 flex items-center justify-center p-4 bg-black/85 backdrop-blur-sm" 
                     style="z-index: 99999 !important;"
                     @click.self="photoModalUrl = null">
                    <div class="relative max-w-2xl w-full bg-slate-900 rounded-xl p-4 border border-slate-700 shadow-2xl flex flex-col items-center animate-in fade-in zoom-in-95 duration-150">
                        {{-- Lightbox Header --}}
                        <div class="flex items-center justify-between w-full pb-3 mb-3 border-b border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <h4 class="text-sm font-bold text-white">Punch-In Selfie Verification</h4>
                            </div>
                            <div class="flex items-center gap-3">
                                <a :href="photoModalUrl" target="_blank" class="text-xs text-blue-400 hover:text-blue-300 hover:underline flex items-center gap-1 font-mono" title="Open full resolution in new tab">
                                    <span>Open Full</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <button type="button" @click.stop="photoModalUrl = null" class="w-7 h-7 rounded-full bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white flex items-center justify-center text-lg font-bold transition cursor-pointer" title="Close Preview (Esc)">
                                    &times;
                                </button>
                            </div>
                        </div>

                        {{-- Lightbox Image Box --}}
                        <div class="w-full flex items-center justify-center bg-black/50 rounded-lg p-2 overflow-hidden border border-slate-800">
                            <img :src="photoModalUrl" alt="Punch Verification Photo" class="max-h-[70vh] w-auto max-w-full object-contain rounded">
                        </div>

                        {{-- Lightbox Footer --}}
                        <div class="mt-3 flex items-center justify-between w-full text-xs text-slate-400">
                            <span>Selfie photo recorded at shift check-in</span>
                            <button type="button" @click.stop="photoModalUrl = null" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded text-xs font-semibold transition cursor-pointer">
                                Close Preview
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>

