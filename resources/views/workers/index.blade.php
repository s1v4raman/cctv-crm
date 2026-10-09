<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Daily-Wage Workers &amp; Labor Workforce</h2>
                    <span class="text-xs font-bold uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 px-2.5 py-1 rounded border border-amber-200 dark:border-amber-500/30">Field Labor</span>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Track technician assistants, daily rates, cabling extras, running ledger balances and Sunday payouts</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('wages.index') }}" class="btn-secondary text-xs flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Sunday Payday Console</span>
                </a>
                <a href="{{ route('workers.create') }}" class="btn-primary shadow-md hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Worker</span>
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

        {{-- Top KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Active Labor Workforce</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white font-heading">{{ $activeWorkersCount }} <span class="text-xs font-normal text-slate-400">/ {{ $totalWorkersCount }}</span></div>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Unpaid Wage Liability</span>
                    <div class="text-2xl font-black text-rose-600 dark:text-rose-400 font-heading">₹{{ number_format($totalOutstandingLiability, 2) }}</div>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Payday Settlement</span>
                    <div class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1">Every Sunday Cycle</div>
                    <span class="text-[11px] text-slate-400">Monday–Saturday Logged</span>
                </div>
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="p-4 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <form method="GET" action="{{ route('workers.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="relative w-full sm:w-96">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search worker name, phone..." class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-100 placeholder-slate-400">
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-200 outline-none">
                        <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Workers</option>
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Workers</option>
                        <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>

                    <button type="submit" class="btn-secondary text-xs py-2 px-4">Search</button>
                    @if($search || $statusFilter !== 'active')
                        <a href="{{ route('workers.index') }}" class="text-xs text-rose-500 hover:underline">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Workers Table --}}
        <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 dark:text-slate-400 tracking-wider border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">Worker Name</th>
                            <th class="px-5 py-3.5">Phone Number</th>
                            <th class="px-5 py-3.5">Standard Daily Rate</th>
                            <th class="px-5 py-3.5">Skills / Specialization</th>
                            <th class="px-5 py-3.5 text-right">Current Balance</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($workers as $worker)
                            @php
                                $balance = $worker->getRunningBalance();
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ strtoupper(substr($worker->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('workers.show', $worker) }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 transition">
                                                {{ $worker->name }}
                                            </a>
                                            <div class="text-xs text-slate-400">{{ $worker->attendances_count }} days logged</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">
                                    @if($worker->phone)
                                        <a href="tel:{{ $worker->phone }}" class="hover:text-blue-600">{{ $worker->phone }}</a>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 font-bold text-slate-900 dark:text-white font-mono">
                                    ₹{{ number_format($worker->daily_rate, 2) }} <span class="text-xs font-normal text-slate-400">/day</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @if($worker->skills && is_array($worker->skills))
                                            @foreach($worker->skills as $sk)
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                                    {{ ucfirst($sk) }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-xs text-slate-400">General Assistant</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if($balance > 0)
                                        <span class="text-sm font-black font-mono text-rose-600 dark:text-rose-400">
                                            ₹{{ number_format($balance, 2) }}
                                        </span>
                                        <span class="text-[10px] block text-rose-500 font-semibold">Payable</span>
                                    @else
                                        <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                            ₹0.00
                                        </span>
                                        <span class="text-[10px] block text-slate-400">Settled</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($worker->is_active)
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('workers.show', $worker) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Ledger & Attendance History">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        </a>
                                        <a href="{{ route('workers.edit', $worker) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Worker">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400 dark:text-slate-500">
                                    <div class="text-4xl mb-2">👷</div>
                                    <p class="font-bold text-sm">No daily-wage workers registered</p>
                                    <a href="{{ route('workers.create') }}" class="btn-primary inline-flex items-center gap-1.5 mt-4 text-xs">
                                        + Register First Worker
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($workers->hasPages())
                <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $workers->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
