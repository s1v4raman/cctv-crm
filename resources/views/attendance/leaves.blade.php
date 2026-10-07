<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight font-heading">
                        Employee Leave Management &amp; Approvals
                    </h2>
                    @if($isAdmin && $pendingCount > 0)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-800 animate-pulse">
                            {{ $pendingCount }} Pending Approval
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Apply for casual, sick, earned &amp; emergency leaves, track balances, and manage workforce approvals.
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('attendance.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0f172a] text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold shadow-xs transition">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Attendance Registers</span>
                </a>

                <button type="button" 
                        onclick="document.getElementById('applyLeaveModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Apply for Leave</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-[calc(100vh-4.5rem)] text-slate-800 dark:text-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Notification Status --}}
            @if(session('status'))
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-semibold flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            {{-- 1. Leave Quota KPI Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Casual Leave Card --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Casual Leave (CL)</span>
                        <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
                            🌴
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white font-heading">{{ $leaveSummary['casual']['remaining'] }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">/ {{ $leaveSummary['casual']['quota'] }} Days Left</span>
                    </div>
                    <div class="mt-2 w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                        @php
                            $casualPct = min(100, round(($leaveSummary['casual']['used'] / max(1, $leaveSummary['casual']['quota'])) * 100));
                        @endphp
                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $casualPct }}%"></div>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex justify-between">
                        <span>Used: <strong>{{ $leaveSummary['casual']['used'] }} d</strong></span>
                        <span>Year: {{ $selectedYear }}</span>
                    </div>
                </div>

                {{-- Sick Leave Card --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sick Leave (SL)</span>
                        <span class="p-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400">
                            💊
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white font-heading">{{ $leaveSummary['sick']['remaining'] }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">/ {{ $leaveSummary['sick']['quota'] }} Days Left</span>
                    </div>
                    <div class="mt-2 w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                        @php
                            $sickPct = min(100, round(($leaveSummary['sick']['used'] / max(1, $leaveSummary['sick']['quota'])) * 100));
                        @endphp
                        <div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $sickPct }}%"></div>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex justify-between">
                        <span>Used: <strong>{{ $leaveSummary['sick']['used'] }} d</strong></span>
                        <span>Medical Support</span>
                    </div>
                </div>

                {{-- Earned / Paid Leave Card --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Earned / Paid Leave</span>
                        <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                            ⭐
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white font-heading">{{ $leaveSummary['earned']['remaining'] }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">/ {{ $leaveSummary['earned']['quota'] }} Days Left</span>
                    </div>
                    <div class="mt-2 w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                        @php
                            $earnedPct = min(100, round(($leaveSummary['earned']['used'] / max(1, $leaveSummary['earned']['quota'])) * 100));
                        @endphp
                        <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $earnedPct }}%"></div>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex justify-between">
                        <span>Used: <strong>{{ $leaveSummary['earned']['used'] }} d</strong></span>
                        <span>Annual Quota</span>
                    </div>
                </div>

                {{-- Total Paid Balance Card / Admin Queue --}}
                @if($isAdmin)
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 border border-amber-200 dark:border-amber-900/60 bg-gradient-to-br from-white to-amber-50/40 dark:from-[#0f172a] dark:to-amber-950/20 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Admin Approval Queue</span>
                            <span class="p-2 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300">
                                ⏳
                            </span>
                        </div>
                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-2xl font-black text-amber-700 dark:text-amber-400 font-heading">{{ $pendingCount }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">Requests Waiting</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-[11px]">
                            <span class="text-slate-500 dark:text-slate-400">Active Out Today: <strong>{{ $activeLeavesToday->count() }}</strong></span>
                            <a href="#pendingQueueSection" class="text-amber-600 dark:text-amber-400 font-bold hover:underline">Review &rarr;</a>
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Paid Balance</span>
                            <span class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                💼
                            </span>
                        </div>
                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-2xl font-black text-slate-900 dark:text-white font-heading">{{ $leaveSummary['total_remaining_paid'] }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">Total Days Remaining</span>
                        </div>
                        <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex justify-between">
                            <span>Unpaid LWP: <strong>{{ $leaveSummary['unpaid_lwp']['used'] }} d</strong></span>
                            <span>Total Used: <strong>{{ $leaveSummary['total_used_paid'] }} d</strong></span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 2. Team Out-of-Office Today Status Strip --}}
            @if($activeLeavesToday->count() > 0)
                <div class="p-4 rounded-2xl bg-gradient-to-r from-purple-500/10 via-blue-500/10 to-emerald-500/10 border border-purple-200/80 dark:border-purple-900/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                            🏖️
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Out of Office Today ({{ now()->format('d M Y') }})</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400">
                                @foreach($activeLeavesToday as $al)
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $al->user->name }} ({{ $al->leave_type_label }}){{ !$loop->last ? ', ' : '' }}
                                    </span>
                                @endforeach
                            </p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-purple-700 dark:text-purple-400 bg-purple-100 dark:bg-purple-950/80 px-3 py-1 rounded-xl border border-purple-200 dark:border-purple-800 self-start sm:self-auto">
                        {{ $activeLeavesToday->count() }} Team Member(s) on Leave
                    </span>
                </div>
            @endif

            {{-- 3. Admin Pending Approval Queue (If Admin and has pending requests) --}}
            @if($isAdmin && $pendingRequests->count() > 0)
                <div id="pendingQueueSection" class="bg-white dark:bg-[#0f172a] rounded-2xl border border-amber-200 dark:border-amber-900/60 shadow-md overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-amber-100 dark:border-amber-900/40 bg-amber-50/50 dark:bg-amber-950/30 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider font-heading">
                                🔔 Pending Leave Approval Queue ({{ $pendingRequests->count() }})
                            </h3>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">1-Click Admin Decision</span>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($pendingRequests as $req)
                            <div class="p-4 sm:p-5 hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div class="space-y-1.5 max-w-2xl">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-sm text-slate-900 dark:text-white">{{ $req->user->name }}</span>
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold uppercase tracking-wider {{ $req->user->role === 'technician' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                            {{ $req->user->role }}
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $req->leave_type_badge_class }}">
                                            {{ $req->leave_type_label }}
                                        </span>
                                        @if($req->is_half_day)
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-sky-100 text-sky-800 dark:bg-sky-950/80 dark:text-sky-400 border border-sky-200 dark:border-sky-800">
                                                Half-Day ({{ ucfirst($req->half_day_session) }})
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-2 flex-wrap">
                                        <span>📅 <strong>{{ $req->start_date->format('d M Y') }}</strong> to <strong>{{ $req->end_date->format('d M Y') }}</strong></span>
                                        <span>&bull;</span>
                                        <span>⏳ Duration: <strong>{{ $req->days_count }} Day(s)</strong></span>
                                        <span>&bull;</span>
                                        <span>Applied: {{ $req->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-xl border border-slate-200/60 dark:border-slate-800 italic">
                                        &ldquo;{{ $req->reason }}&rdquo;
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0">
                                    {{-- Approve Form --}}
                                    <form action="{{ route('leaves.approve', $req) }}" method="POST" onsubmit="return confirm('Approve this leave request for {{ $req->user->name }}? Attendance records will be marked on_leave.')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Approve</span>
                                        </button>
                                    </form>

                                    {{-- Reject Trigger Button --}}
                                    <button type="button" 
                                            onclick="openRejectModal('{{ $req->id }}', '{{ addslashes($req->user->name) }}', '{{ $req->leave_type_label }}')"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-[#0f172a] hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50 text-xs font-bold transition">
                                        <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        <span>Reject</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 4. Main Tab Navigation (My Leaves vs All Workforce Leaves) --}}
            <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="border-b border-slate-200 dark:border-slate-800 px-4 sm:px-6 pt-4 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex space-x-2">
                        <a href="{{ route('leaves.index', ['tab' => 'my_leaves', 'year' => $selectedYear]) }}" 
                           class="pb-3 px-3 text-xs font-bold border-b-2 transition {{ $tab === 'my_leaves' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                            📝 My Leave Applications ({{ $myLeaves->total() }})
                        </a>

                        @if($isAdmin)
                            <a href="{{ route('leaves.index', ['tab' => 'all_leaves', 'year' => $selectedYear, 'month' => $selectedMonth]) }}" 
                               class="pb-3 px-3 text-xs font-bold border-b-2 transition {{ $tab === 'all_leaves' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                                👥 All Workforce Leaves Directory ({{ $allLeaves->total() }})
                            </a>
                        @endif
                    </div>

                    {{-- Filters --}}
                    <form method="GET" action="{{ route('leaves.index') }}" class="flex items-center gap-2 pb-3">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        
                        <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-1.5 px-3">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>

                        @if($tab === 'all_leaves' && $isAdmin)
                            <select name="employee_id" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-1.5 px-3">
                                <option value="">All Employees</option>
                                @foreach($workforce as $emp)
                                    <option value="{{ $emp->id }}" {{ $employeeFilter == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                                @endforeach
                            </select>

                            <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-1.5 px-3">
                        @else
                            <select name="year" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-1.5 px-3">
                                <option value="2026" {{ $selectedYear == 2026 ? 'selected' : '' }}>2026</option>
                                <option value="2025" {{ $selectedYear == 2025 ? 'selected' : '' }}>2025</option>
                            </select>
                        @endif
                    </form>
                </div>

                {{-- Tab 1: My Leaves Table --}}
                @if($tab === 'my_leaves')
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4 sm:px-6">Leave Details</th>
                                    <th class="py-3.5 px-4">Dates &amp; Duration</th>
                                    <th class="py-3.5 px-4">Reason</th>
                                    <th class="py-3.5 px-4">Status &amp; Approver</th>
                                    <th class="py-3.5 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($myLeaves as $l)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                        <td class="py-4 px-4 sm:px-6">
                                            <div class="font-bold text-slate-900 dark:text-white">
                                                #LR-{{ $l->id }}
                                            </div>
                                            <span class="inline-flex mt-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $l->leave_type_badge_class }}">
                                                {{ $l->leave_type_label }}
                                            </span>
                                            @if($l->is_half_day)
                                                <div class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold mt-0.5">
                                                    Half Day ({{ ucfirst($l->half_day_session) }})
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <div class="font-semibold text-slate-900 dark:text-white">
                                                {{ $l->start_date->format('d M Y') }}
                                                @if($l->start_date->ne($l->end_date))
                                                    <span class="text-slate-400">&rarr;</span> {{ $l->end_date->format('d M Y') }}
                                                @endif
                                            </div>
                                            <div class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5">
                                                <strong>{{ $l->days_count }} Day(s)</strong> &bull; Applied {{ $l->created_at->format('d M') }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 max-w-xs">
                                            <p class="text-slate-700 dark:text-slate-300 line-clamp-2">{{ $l->reason }}</p>
                                            @if($l->rejection_reason)
                                                <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">
                                                    Reason: {{ $l->rejection_reason }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $l->status_badge_class }}">
                                                {{ $l->status_label }}
                                            </span>
                                            @if($l->actioner)
                                                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">
                                                    By: {{ $l->actioner->name }} ({{ $l->actioned_at?->format('d M') }})
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-right whitespace-nowrap">
                                            @if($l->status === 'pending')
                                                <form action="{{ route('leaves.cancel', $l) }}" method="POST" onsubmit="return confirm('Cancel this pending leave request?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 text-sm font-semibold underline">
                                                        Cancel Request
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-slate-400 text-xs">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                            No leave applications found for {{ $selectedYear }}. Click "Apply for Leave" above to submit one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($myLeaves->hasPages())
                        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                            {{ $myLeaves->links() }}
                        </div>
                    @endif
                @endif

                {{-- Tab 2: All Workforce Leaves Directory (Admin Only) --}}
                @if($tab === 'all_leaves' && $isAdmin)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4 sm:px-6">Employee</th>
                                    <th class="py-3.5 px-4">Leave Type</th>
                                    <th class="py-3.5 px-4">Duration</th>
                                    <th class="py-3.5 px-4">Reason</th>
                                    <th class="py-3.5 px-4">Status &amp; Actioned By</th>
                                    <th class="py-3.5 px-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($allLeaves as $al)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                        <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                            <div class="font-bold text-slate-900 dark:text-white">{{ $al->user->name }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $al->user->email }} &bull; <strong class="uppercase">{{ $al->user->role }}</strong></div>
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $al->leave_type_badge_class }}">
                                                {{ $al->leave_type_label }}
                                            </span>
                                            @if($al->is_half_day)
                                                <span class="block text-[10px] text-sky-600 dark:text-sky-400 font-semibold mt-0.5">
                                                    Half Day ({{ ucfirst($al->half_day_session) }})
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <div class="font-semibold text-slate-900 dark:text-white">
                                                {{ $al->start_date->format('d M Y') }} &rarr; {{ $al->end_date->format('d M Y') }}
                                            </div>
                                            <div class="text-[11px] text-slate-500"><strong>{{ $al->days_count }} Day(s)</strong></div>
                                        </td>
                                        <td class="py-4 px-4 max-w-xs">
                                            <p class="text-slate-700 dark:text-slate-300 line-clamp-2">{{ $al->reason }}</p>
                                            @if($al->rejection_reason)
                                                <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">
                                                    Rejection Reason: {{ $al->rejection_reason }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $al->status_badge_class }}">
                                                {{ $al->status_label }}
                                            </span>
                                            @if($al->actioner)
                                                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">
                                                    By {{ $al->actioner->name }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-right whitespace-nowrap">
                                            @if($al->status === 'pending')
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <form action="{{ route('leaves.approve', $al) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="min-h-[38px] px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    <button type="button" 
                                                            onclick="openRejectModal('{{ $al->id }}', '{{ addslashes($al->user->name) }}', '{{ $al->leave_type_label }}')"
                                                            class="min-h-[38px] px-3.5 py-2 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-sm font-semibold hover:bg-rose-100 transition">
                                                        Reject
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-slate-400 text-xs">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                            No workforce leave records matching filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($allLeaves->hasPages())
                        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                            {{ $allLeaves->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>

    {{-- Apply Leave Modal --}}
    <div id="applyLeaveModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 font-heading">
                    <span>📝</span> Submit Leave Application
                </h3>
                <button type="button" onclick="document.getElementById('applyLeaveModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold">
                    &times;
                </button>
            </div>

            <form action="{{ route('leaves.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf

                {{-- Leave Type --}}
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Leave Type *</label>
                    <select name="leave_type" required class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                        <option value="casual">Casual Leave (CL) — {{ $leaveSummary['casual']['remaining'] }} days remaining</option>
                        <option value="sick">Sick Leave (SL) — {{ $leaveSummary['sick']['remaining'] }} days remaining</option>
                        <option value="earned">Earned / Paid Leave (EL) — {{ $leaveSummary['earned']['remaining'] }} days remaining</option>
                        <option value="emergency">Emergency Leave</option>
                        <option value="unpaid_lwp">Unpaid Leave (Loss of Pay)</option>
                    </select>
                </div>

                {{-- Dates --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Start Date *</label>
                        <input type="date" name="start_date" id="leaveStartDate" required value="{{ now()->toDateString() }}" 
                               class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">End Date *</label>
                        <input type="date" name="end_date" id="leaveEndDate" required value="{{ now()->toDateString() }}" 
                               class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                    </div>
                </div>

                {{-- Half Day Toggle --}}
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800 space-y-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_half_day" value="1" id="isHalfDayToggle" onchange="toggleHalfDaySession(this.checked)" class="rounded text-blue-600 focus:ring-blue-500">
                        <span class="font-bold text-slate-800 dark:text-slate-200">Is this a Half-Day Leave?</span>
                    </label>

                    <div id="halfDaySessionGroup" class="hidden pt-1">
                        <label class="block font-semibold text-slate-600 dark:text-slate-400 mb-1">Session</label>
                        <select name="half_day_session" class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                            <option value="morning">Morning Session (1st Half)</option>
                            <option value="afternoon">Afternoon Session (2nd Half)</option>
                        </select>
                    </div>
                </div>

                {{-- Reason --}}
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Reason for Leave *</label>
                    <textarea name="reason" rows="3" required placeholder="Please provide specific details for your leave request..." 
                              class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"></textarea>
                </div>

                {{-- Attachment --}}
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Attachment (Optional, e.g. Doctor's Note / Proof)</label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" 
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('applyLeaveModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 dark:text-slate-400">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 min-h-[44px] text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Admin Reject Leave Modal --}}
    <div id="rejectLeaveModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-rose-200 dark:border-rose-900 space-y-4">
            <div class="flex items-center justify-between border-b border-rose-100 dark:border-rose-900/40 pb-3">
                <h3 class="text-base font-bold text-rose-600 dark:text-rose-400 flex items-center gap-2 font-heading">
                    <span>⚠️</span> Reject Leave Application
                </h3>
                <button type="button" onclick="document.getElementById('rejectLeaveModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">
                    &times;
                </button>
            </div>

            <p id="rejectModalPrompt" class="text-xs text-slate-600 dark:text-slate-300">
                Please state the reason for rejecting this leave application:
            </p>

            <form id="rejectLeaveForm" method="POST" action="" class="space-y-4 text-xs">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Rejection Reason *</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="e.g. Critical site deployment scheduled, staff shortage..." 
                              class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('rejectLeaveModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 dark:text-slate-400">
                        Back
                    </button>
                    <button type="submit" class="px-6 py-2.5 min-h-[44px] text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md transition">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleHalfDaySession(isChecked) {
            const group = document.getElementById('halfDaySessionGroup');
            if (isChecked) {
                group.classList.remove('hidden');
                // Auto sync end date to start date for half-day
                document.getElementById('leaveEndDate').value = document.getElementById('leaveStartDate').value;
            } else {
                group.classList.add('hidden');
            }
        }

        function openRejectModal(leaveId, employeeName, leaveType) {
            const modal = document.getElementById('rejectLeaveModal');
            const prompt = document.getElementById('rejectModalPrompt');
            const form = document.getElementById('rejectLeaveForm');

            prompt.innerHTML = `Rejecting <strong>${leaveType}</strong> for <strong>${employeeName}</strong>. Please provide a clear explanation for the employee:`;
            form.action = `/attendance/leaves/${leaveId}/reject`;
            modal.classList.remove('hidden');
        }
    </script>
</x-app-layout>
