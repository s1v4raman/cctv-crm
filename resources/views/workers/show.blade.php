<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('workers.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">{{ $worker->name }}</h2>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $worker->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800' }}">
                        {{ $worker->is_active ? 'Active Worker' : 'Inactive' }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Phone: {{ $worker->phone ?? 'Not provided' }} • Daily Rate: ₹{{ number_format($worker->daily_rate, 2) }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('wages.index') }}" class="btn-secondary text-xs">
                    Sunday Payday Console
                </a>
                <a href="{{ route('workers.edit', $worker) }}" class="btn-primary text-xs shadow-md">
                    Edit Worker
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        {{-- Financial KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Days Logged</span>
                <div class="text-2xl font-black text-slate-900 dark:text-white font-heading mt-1">{{ $totalDaysWorked }}</div>
                <span class="text-[11px] text-slate-400">Lifetime work shifts</span>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Lifetime Earned</span>
                <div class="text-2xl font-black text-slate-900 dark:text-white font-heading mt-1">₹{{ number_format($totalEarned, 2) }}</div>
                <span class="text-[11px] text-slate-400">Wages + cabling + extras</span>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Lifetime Paid</span>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-heading mt-1">₹{{ number_format($totalPaid, 2) }}</div>
                <span class="text-[11px] text-slate-400">Weekly payouts &amp; advances</span>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Outstanding Balance</span>
                <div class="text-2xl font-black font-heading mt-1 {{ $runningBalance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                    ₹{{ number_format($runningBalance, 2) }}
                </div>
                <span class="text-[11px] font-semibold {{ $runningBalance > 0 ? 'text-rose-500' : 'text-emerald-500' }}">
                    {{ $runningBalance > 0 ? 'Due for settlement' : 'Fully Settled' }}
                </span>
            </div>
        </div>

        {{-- Skills & Personal Notes Card --}}
        @if($worker->skills || $worker->notes)
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-3">
                @if($worker->skills)
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold uppercase text-slate-400 mr-2">Skills:</span>
                        @foreach($worker->skills as $sk)
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                {{ ucfirst($sk) }}
                            </span>
                        @endforeach
                    </div>
                @endif
                @if($worker->notes)
                    <div class="text-xs text-slate-600 dark:text-slate-300">
                        <span class="font-bold text-slate-400 block mb-0.5">Notes:</span>
                        {{ $worker->notes }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Ledger Timeline Tabs --}}
        <div class="space-y-4" x-data="{ activeTab: 'attendances' }">
            <div class="flex border-b border-slate-200 dark:border-slate-800 gap-6 text-sm font-bold">
                <button @click="activeTab = 'attendances'" :class="activeTab === 'attendances' ? 'border-b-2 border-blue-600 text-blue-600 pb-3' : 'text-slate-400 pb-3 hover:text-slate-600'">
                    Attendance &amp; Work Shifts ({{ $attendances->total() }})
                </button>
                <button @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'border-b-2 border-blue-600 text-blue-600 pb-3' : 'text-slate-400 pb-3 hover:text-slate-600'">
                    Wage Settlements &amp; Advances ({{ $payments->total() }})
                </button>
            </div>

            {{-- Attendances Table --}}
            <div x-show="activeTab === 'attendances'" class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="px-5 py-3.5">Work Date</th>
                                <th class="px-5 py-3.5">Project</th>
                                <th class="px-5 py-3.5">Shift</th>
                                <th class="px-5 py-3.5">Daily Rate</th>
                                <th class="px-5 py-3.5">Cabling Metres</th>
                                <th class="px-5 py-3.5">Extras</th>
                                <th class="px-5 py-3.5 text-right">Total Shift Amount</th>
                                <th class="px-5 py-3.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($attendances as $att)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                    <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white font-mono text-xs">
                                        {{ $att->workDay->work_date->format('d M Y') }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <a href="{{ route('projects.show', $att->workDay->project) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ $att->workDay->project->project_code }}
                                        </a>
                                        <div class="text-xs text-slate-400 truncate max-w-xs">{{ $att->workDay->project->title }}</div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $att->attendance_type === 'full_day' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' }}">
                                            {{ $att->attendance_type === 'full_day' ? 'Full Day' : 'Half Day' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-xs">
                                        ₹{{ number_format($att->daily_rate_snapshot, 2) }}
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-xs">
                                        @if($att->cabling_metres > 0)
                                            {{ $att->cabling_metres }} m <span class="text-slate-400">(+₹{{ number_format($att->cabling_amount, 2) }})</span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-xs">
                                        @if($att->extra_amount > 0)
                                            <span class="text-emerald-600 font-bold font-mono">+₹{{ number_format($att->extra_amount, 2) }}</span>
                                            <span class="text-[10px] text-slate-400 block">{{ $att->extra_description }}</span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold font-mono text-slate-900 dark:text-white">
                                        ₹{{ number_format($att->total_amount, 2) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        @if($att->is_paid)
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                                Paid &amp; Locked
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                                Unpaid
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-8 text-center text-slate-400 text-xs">
                                        No attendance shifts recorded for this worker yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($attendances->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
                        {{ $attendances->links() }}
                    </div>
                @endif
            </div>

            {{-- Payments & Advances Table --}}
            <div x-show="activeTab === 'payments'" x-cloak class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="px-5 py-3.5">Payment Date</th>
                                <th class="px-5 py-3.5">Type</th>
                                <th class="px-5 py-3.5">Payment Mode</th>
                                <th class="px-5 py-3.5">Week Bounds</th>
                                <th class="px-5 py-3.5">Settled By</th>
                                <th class="px-5 py-3.5">Notes</th>
                                <th class="px-5 py-3.5 text-right">Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($payments as $pmt)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                    <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-900 dark:text-white">
                                        {{ $pmt->payment_date->format('d M Y') }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="px-2 py-0.5 text-xs font-bold rounded {{ $pmt->payment_type === 'wage' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' }}">
                                            {{ ucfirst($pmt->payment_type) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs uppercase font-bold text-slate-500">
                                        {{ $pmt->payment_mode }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-400 font-mono">
                                        {{ $pmt->week_start ? Carbon\Carbon::parse($pmt->week_start)->format('d M') . ' – ' . Carbon\Carbon::parse($pmt->week_end)->format('d M') : '—' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-700 dark:text-slate-300">
                                        {{ $pmt->settledBy?->name ?? 'System' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-500 max-w-xs truncate">
                                        {{ $pmt->reference_notes ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-black font-mono text-emerald-600 dark:text-emerald-400">
                                        ₹{{ number_format($pmt->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-slate-400 text-xs">
                                        No payments or advance payouts recorded for this worker.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($payments->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
