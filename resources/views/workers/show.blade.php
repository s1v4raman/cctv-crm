<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('workers.index') }}" class="back-link">&larr; Back</a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-1">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $worker->name }}</h2>
                    <span class="text-sm font-bold {{ $worker->is_active ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $worker->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('wages.index') }}" class="btn-secondary">
                        Sunday Payday Console
                    </a>
                    <a href="{{ route('workers.edit', $worker) }}" class="btn-secondary">
                        Edit Details
                    </a>
                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('workers.destroy', $worker) }}" onsubmit="return confirm('Are you sure you want to delete worker {{ addslashes($worker->name) }}?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger-link p-0 bg-transparent border-0 cursor-pointer text-xs ml-2">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div class="max-w-[1200px] mx-auto space-y-6">
        @if (session('status'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded text-xs">
                {{ session('status') }}
            </div>
        @endif

        {{-- Stat Cards: Gray label and bold number only (5.5) --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="stat-card">
                <div class="stat-label">Days Logged</div>
                <div class="stat-value">{{ $totalDaysWorked }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Total Lifetime Earned</div>
                <div class="stat-value">&#8377;{{ number_format($totalEarned, 0) }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Total Lifetime Paid</div>
                <div class="stat-value">&#8377;{{ number_format($totalPaid, 0) }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Outstanding Balance</div>
                <div class="stat-value">&#8377;{{ number_format($runningBalance, 0) }}</div>
            </div>
        </div>

        {{-- Section 1: Worker Profile Two-column table (5.5) --}}
        <div class="crm-section">
            <h3>Worker Information</h3>
            <table class="crm-table">
                <tbody>
                    <tr>
                        <td class="w-1/3 text-slate-500 font-bold">Worker Name</td>
                        <td class="w-2/3 text-slate-900 dark:text-white">{{ $worker->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Phone Number</td>
                        <td class="text-slate-900 dark:text-white">
                            @if($worker->phone)
                                <a href="tel:{{ $worker->phone }}" class="action-link">{{ $worker->phone }}</a>
                            @else
                                Not specified
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Daily Wage Rate</td>
                        <td class="text-slate-900 dark:text-white">&#8377;{{ number_format($worker->daily_rate, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Skills</td>
                        <td class="text-slate-900 dark:text-white">
                            @if($worker->skills && is_array($worker->skills))
                                {{ implode(', ', array_map('ucfirst', $worker->skills)) }}
                            @else
                                General Assistant
                            @endif
                        </td>
                    </tr>
                    @if($worker->notes)
                        <tr>
                            <td class="text-slate-500 font-bold">Notes</td>
                            <td class="text-slate-900 dark:text-white whitespace-pre-line">{{ $worker->notes }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Section 2: Attendance & Work Shifts (5.3) --}}
        <div class="crm-section">
            <h3>Attendance & Work Shifts ({{ $attendances->total() }})</h3>
            <div class="overflow-x-auto">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th class="nowrap">Work Date</th>
                            <th class="nowrap">Project</th>
                            <th class="nowrap">Shift</th>
                            <th class="nowrap">Daily Rate</th>
                            <th class="nowrap">Cabling Metres</th>
                            <th>Extras</th>
                            <th>Total Amount</th>
                            <th class="nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $att)
                        <tr>
                            <td class="nowrap font-mono">
                                {{ $att->workDay->work_date->format('d M Y') }}
                            </td>
                            <td class="nowrap">
                                <a href="{{ route('projects.show', $att->workDay->project) }}" class="action-link">
                                    {{ $att->workDay->project->project_code }}
                                </a>
                            </td>
                            <td class="nowrap">
                                {{ $att->attendance_type === 'full_day' ? 'Full Day' : 'Half Day' }}
                            </td>
                            <td class="nowrap">&#8377;{{ number_format($att->daily_rate_snapshot, 2) }}</td>
                            <td class="nowrap">
                                @if($att->cabling_metres > 0)
                                    {{ $att->cabling_metres }} m (+&#8377;{{ number_format($att->cabling_amount, 2) }})
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($att->extra_amount > 0)
                                    +&#8377;{{ number_format($att->extra_amount, 2) }} ({{ $att->extra_description }})
                                @else
                                    —
                                @endif
                            </td>
                            <td class="font-bold text-slate-900 dark:text-white">&#8377;{{ number_format($att->total_amount, 2) }}</td>
                            <td class="nowrap">
                                @if($att->is_paid)
                                    <span class="status-won">Paid</span>
                                @else
                                    <span class="status-quoted">Unpaid</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 text-slate-500 text-sm">
                                No attendance shifts yet
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attendances->hasPages())
                <div class="mt-4">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>

        {{-- Section 3: Wage Settlements & Advances (5.3) --}}
        <div class="crm-section">
            <h3>Wage Settlements & Advances ({{ $payments->total() }})</h3>
            <div class="overflow-x-auto">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th class="nowrap">Payment Date</th>
                            <th class="nowrap">Type</th>
                            <th class="nowrap">Payment Mode</th>
                            <th class="nowrap">Week Bounds</th>
                            <th>Settled By</th>
                            <th>Notes</th>
                            <th>Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $pmt)
                        <tr>
                            <td class="nowrap font-mono">
                                {{ $pmt->payment_date->format('d M Y') }}
                            </td>
                            <td class="nowrap font-bold text-slate-900 dark:text-white capitalize">
                                {{ $pmt->payment_type }}
                            </td>
                            <td class="nowrap uppercase">
                                {{ $pmt->payment_mode }}
                            </td>
                            <td class="nowrap">
                                {{ $pmt->week_start ? Carbon\Carbon::parse($pmt->week_start)->format('d M') . ' – ' . Carbon\Carbon::parse($pmt->week_end)->format('d M') : '—' }}
                            </td>
                            <td>
                                {{ $pmt->settledBy?->name ?? 'System' }}
                            </td>
                            <td>
                                {{ $pmt->reference_notes ?? '—' }}
                            </td>
                            <td class="font-bold text-slate-900 dark:text-white">
                                &#8377;{{ number_format($pmt->amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-slate-500 text-sm">
                                No wage settlements yet
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="mt-4">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
