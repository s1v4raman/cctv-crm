<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight font-heading">
                        Field Expenses &amp; Travel Claims Hub
                    </h2>
                    @if($isAdmin && $pendingCount > 0)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-800 animate-pulse">
                            {{ $pendingCount }} Pending (₹{{ number_format($pendingTotalAmount, 2) }})
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Log fuel distance (KM), site hardware purchases &amp; travel claims with receipt uploads and 1-click approvals.
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('finance.payroll.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0f172a] text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold shadow-xs transition">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Payroll Master</span>
                </a>

                <button type="button" 
                        onclick="document.getElementById('submitExpenseModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>File Expense Claim</span>
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

            {{-- 1. Macro KPIs Summary Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Claimed This Month --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Claims This Month</span>
                        <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
                            📊
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white font-heading">₹{{ number_format($totalClaimedMonth, 2) }}</span>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex justify-between">
                        <span>Period: <strong>{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</strong></span>
                    </div>
                </div>

                {{-- Pending Approvals --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 border border-amber-200 dark:border-amber-900/60 bg-gradient-to-br from-white to-amber-50/40 dark:from-[#0f172a] dark:to-amber-950/20 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Pending Review</span>
                        <span class="p-2 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300">
                            ⏳
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-amber-700 dark:text-amber-400 font-heading">₹{{ number_format($pendingTotalAmount, 2) }}</span>
                    </div>
                    <div class="mt-2 text-[11px] text-amber-700 dark:text-amber-400 flex justify-between">
                        <span><strong>{{ $pendingCount }} claim(s)</strong> waiting</span>
                        @if($isAdmin && $pendingCount > 0)
                            <a href="#pendingQueueSection" class="font-bold underline">Review Queue &rarr;</a>
                        @endif
                    </div>
                </div>

                {{-- Disbursed / Paid Amount --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Settled / Paid</span>
                        <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                            💰
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-heading">₹{{ number_format($totalPaidMonth, 2) }}</span>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex justify-between">
                        <span>Approved Queue: ₹{{ number_format($totalApprovedMonth, 2) }}</span>
                    </div>
                </div>

                {{-- Fuel & Travel Distance Sum --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fuel &amp; Travel Log</span>
                        <span class="p-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400">
                            🛵
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-purple-600 dark:text-purple-400 font-heading">{{ number_format($totalKmMonth, 1) }} KM</span>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex justify-between">
                        <span>Fuel Paid: <strong>₹{{ number_format($totalFuelMonth, 2) }}</strong></span>
                    </div>
                </div>
            </div>

            {{-- 2. Admin Pending Approval & Disbursal Queue --}}
            @if($isAdmin && $pendingClaims->count() > 0)
                <div id="pendingQueueSection" class="bg-white dark:bg-[#0f172a] rounded-2xl border border-amber-200 dark:border-amber-900/60 shadow-md overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-amber-100 dark:border-amber-900/40 bg-amber-50/50 dark:bg-amber-950/30 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider font-heading">
                                🔔 Pending Expense Claims Review Queue ({{ $pendingClaims->count() }})
                            </h3>
                        </div>
                        <span class="text-xs font-bold text-amber-700 dark:text-amber-400">Total: ₹{{ number_format($pendingTotalAmount, 2) }}</span>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($pendingClaims as $c)
                            <div class="p-4 sm:p-5 hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div class="space-y-1.5 max-w-2xl">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-sm text-slate-900 dark:text-white">{{ $c->user?->name }}</span>
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold uppercase tracking-wider {{ $c->user?->role === 'technician' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                            {{ $c->user?->role }}
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $c->category_badge_class }}">
                                            {{ $c->category_label }}
                                        </span>
                                        <span class="font-mono text-xs text-slate-400">#{{ $c->claim_no }}</span>
                                    </div>

                                    <div class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-2 flex-wrap">
                                        <span>📅 Date: <strong>{{ $c->expense_date->format('d M Y') }}</strong></span>
                                        <span>&bull;</span>
                                        @if($c->travel_distance_km > 0)
                                            <span>🛵 <strong>{{ $c->travel_distance_km }} KM</strong> ({{ $c->travel_from }} &rarr; {{ $c->travel_to }})</span>
                                            <span>&bull;</span>
                                        @endif
                                        @if($c->installationJob)
                                            <span>🎯 Job: <strong>{{ $c->installationJob->job_no }}</strong> ({{ $c->installationJob->quotation?->lead?->customer_name }})</span>
                                            <span>&bull;</span>
                                        @endif
                                        @if($c->serviceTicket)
                                            <span>🎫 Ticket: <strong>{{ $c->serviceTicket->ticket_no }}</strong></span>
                                            <span>&bull;</span>
                                        @endif
                                        <span>Claimed: {{ $c->created_at->diffForHumans() }}</span>
                                    </div>

                                    <p class="text-xs text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-xl border border-slate-200/60 dark:border-slate-800 italic">
                                        &ldquo;{{ $c->description }}&rdquo;
                                    </p>

                                    @if($c->receipt_path)
                                        <div class="pt-1">
                                            <a href="{{ asset('storage/' . $c->receipt_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                                <span>📎 View Attached Receipt / Invoice Proof</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-3 flex-shrink-0">
                                    <div class="text-right">
                                        <div class="text-lg font-black text-slate-900 dark:text-white font-heading">
                                            ₹{{ number_format((float) $c->amount, 2) }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 uppercase font-bold">Claim Value</div>
                                    </div>

                                    {{-- Approve Form --}}
                                    <form action="{{ route('finance.expenses.approve', $c) }}" method="POST" onsubmit="return confirm('Approve expense claim #{{ $c->claim_no }} for ₹{{ number_format((float) $c->amount, 2) }}?')">
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
                                            onclick="openRejectClaimModal('{{ $c->id }}', '{{ $c->claim_no }}', '{{ addslashes($c->user?->name) }}', '₹{{ number_format((float) $c->amount, 2) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-[#0f172a] hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50 text-xs font-bold transition">
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

            {{-- 3. Main Tab Navigation (My Claims vs Workforce All Claims) --}}
            <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="border-b border-slate-200 dark:border-slate-800 px-4 sm:px-6 pt-4 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex space-x-2">
                        <a href="{{ route('finance.expenses.index', ['tab' => 'my_claims', 'month' => $selectedMonth]) }}" 
                           class="pb-3 px-3 text-xs font-bold border-b-2 transition {{ $tab === 'my_claims' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                            📝 My Claims ({{ $myClaims->total() }})
                        </a>

                        @if($isAdmin)
                            <a href="{{ route('finance.expenses.index', ['tab' => 'all_claims', 'month' => $selectedMonth]) }}" 
                               class="pb-3 px-3 text-xs font-bold border-b-2 transition {{ $tab === 'all_claims' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                                👥 Master Workforce Claims Ledger ({{ $allClaims->total() }})
                            </a>
                        @endif
                    </div>

                    {{-- Filters --}}
                    <form method="GET" action="{{ route('finance.expenses.index') }}" class="flex items-center gap-2 pb-3 flex-wrap">
                        <input type="hidden" name="tab" value="{{ $tab }}">

                        <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-1.5 px-3">

                        <select name="category" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-1.5 px-3">
                            <option value="">All Categories</option>
                            <option value="fuel_travel" {{ $categoryFilter === 'fuel_travel' ? 'selected' : '' }}>Fuel &amp; Travel</option>
                            <option value="hardware_tools" {{ $categoryFilter === 'hardware_tools' ? 'selected' : '' }}>Hardware Tools</option>
                            <option value="food_lodging" {{ $categoryFilter === 'food_lodging' ? 'selected' : '' }}>Food &amp; Lodging</option>
                            <option value="toll_parking" {{ $categoryFilter === 'toll_parking' ? 'selected' : '' }}>Toll &amp; Parking</option>
                            <option value="emergency_materials" {{ $categoryFilter === 'emergency_materials' ? 'selected' : '' }}>Emergency Supplies</option>
                            <option value="other" {{ $categoryFilter === 'other' ? 'selected' : '' }}>Other</option>
                        </select>

                        <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-1.5 px-3">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="paid" {{ $statusFilter === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>

                        @if($tab === 'all_claims' && $isAdmin)
                            <select name="employee_id" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-1.5 px-3">
                                <option value="">All Employees</option>
                                @foreach($workforce as $emp)
                                    <option value="{{ $emp->id }}" {{ $employeeFilter == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </form>
                </div>

                {{-- Tab 1: My Claims Table --}}
                @if($tab === 'my_claims')
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4 sm:px-6">Claim Details</th>
                                    <th class="py-3.5 px-4">Date &amp; Details</th>
                                    <th class="py-3.5 px-4">Project / Ticket Link</th>
                                    <th class="py-3.5 px-4 text-right">Amount</th>
                                    <th class="py-3.5 px-4">Status &amp; Settlement</th>
                                    <th class="py-3.5 px-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($myClaims as $c)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                        <td class="py-4 px-4 sm:px-6">
                                            <div class="font-mono font-bold text-slate-900 dark:text-white">#{{ $c->claim_no }}</div>
                                            <span class="inline-flex mt-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $c->category_badge_class }}">
                                                {{ $c->category_label }}
                                            </span>
                                            @if($c->receipt_path)
                                                <div class="mt-1">
                                                    <a href="{{ asset('storage/' . $c->receipt_path) }}" target="_blank" class="text-[10px] text-blue-600 dark:text-blue-400 font-bold hover:underline">
                                                        📎 Receipt Attached
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <div class="font-semibold text-slate-900 dark:text-white">{{ $c->expense_date->format('d M Y') }}</div>
                                            @if($c->travel_distance_km > 0)
                                                <div class="text-[11px] text-purple-600 dark:text-purple-400 font-bold mt-0.5">
                                                    🛵 {{ $c->travel_distance_km }} KM ({{ $c->travel_from }} &rarr; {{ $c->travel_to }})
                                                </div>
                                            @endif
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xs truncate mt-0.5" title="{{ $c->description }}">
                                                {{ $c->description }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            @if($c->installationJob)
                                                <div class="font-bold text-slate-800 dark:text-slate-200">🎯 {{ $c->installationJob->job_no }}</div>
                                                <div class="text-[10px] text-slate-400">{{ $c->installationJob->quotation?->lead?->customer_name }}</div>
                                            @elseif($c->serviceTicket)
                                                <div class="font-bold text-slate-800 dark:text-slate-200">🎫 {{ $c->serviceTicket->ticket_no }}</div>
                                                <div class="text-[10px] text-slate-400">{{ $c->serviceTicket->title }}</div>
                                            @else
                                                <span class="text-slate-400 text-xs">General / Fleet</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-right whitespace-nowrap">
                                            <div class="font-black text-sm text-slate-900 dark:text-white font-heading">
                                                ₹{{ number_format((float) $c->amount, 2) }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $c->status_badge_class }}">
                                                {{ $c->status_label }}
                                            </span>
                                            @if($c->status === 'paid')
                                                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">
                                                    Disbursed via {{ ucfirst(str_replace('_', ' ', $c->payment_method)) }}
                                                </div>
                                            @endif
                                            @if($c->rejection_reason)
                                                <div class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold mt-0.5">
                                                    {{ $c->rejection_reason }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-right whitespace-nowrap">
                                            @if($c->status === 'pending')
                                                <form action="{{ route('finance.expenses.cancel', $c) }}" method="POST" onsubmit="return confirm('Cancel this pending claim?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 text-xs font-bold underline">
                                                        Cancel Claim
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-slate-400 text-xs">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                            No expense claims found for {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}. Click "File Expense Claim" above to submit travel or hardware costs.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($myClaims->hasPages())
                        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                            {{ $myClaims->links() }}
                        </div>
                    @endif
                @endif

                {{-- Tab 2: All Workforce Claims Ledger (Admin Only) --}}
                @if($tab === 'all_claims' && $isAdmin)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4 sm:px-6">Employee</th>
                                    <th class="py-3.5 px-4">Claim # &amp; Category</th>
                                    <th class="py-3.5 px-4">Date &amp; Details</th>
                                    <th class="py-3.5 px-4 text-right">Amount</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($allClaims as $ac)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                        <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                            <div class="font-bold text-slate-900 dark:text-white">{{ $ac->user?->name }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $ac->user?->email }} &bull; <strong class="uppercase">{{ $ac->user?->role }}</strong></div>
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <div class="font-mono font-bold text-slate-900 dark:text-white">#{{ $ac->claim_no }}</div>
                                            <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $ac->category_badge_class }}">
                                                {{ $ac->category_label }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="font-semibold text-slate-900 dark:text-white">{{ $ac->expense_date->format('d M Y') }}</div>
                                            @if($ac->travel_distance_km > 0)
                                                <div class="text-[11px] text-purple-600 dark:text-purple-400 font-bold">
                                                    🛵 {{ $ac->travel_distance_km }} KM ({{ $ac->travel_from }} &rarr; {{ $ac->travel_to }})
                                                </div>
                                            @endif
                                            <div class="text-[11px] text-slate-500 line-clamp-1">{{ $ac->description }}</div>
                                            @if($ac->receipt_path)
                                                <a href="{{ asset('storage/' . $ac->receipt_path) }}" target="_blank" class="text-[10px] text-blue-600 dark:text-blue-400 font-bold hover:underline">
                                                    📎 Receipt Proof
                                                </a>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-right whitespace-nowrap">
                                            <div class="font-black text-sm text-slate-900 dark:text-white font-heading">
                                                ₹{{ number_format((float) $ac->amount, 2) }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $ac->status_badge_class }}">
                                                {{ $ac->status_label }}
                                            </span>
                                            @if($ac->status === 'paid')
                                                <div class="text-[10px] text-emerald-600 font-bold mt-0.5">
                                                    Disbursed via {{ ucfirst(str_replace('_', ' ', $ac->payment_method)) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($ac->status === 'pending')
                                                    <form action="{{ route('finance.expenses.approve', $ac) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    <button type="button" 
                                                            onclick="openRejectClaimModal('{{ $ac->id }}', '{{ $ac->claim_no }}', '{{ addslashes($ac->user?->name) }}', '₹{{ number_format((float) $ac->amount, 2) }}')"
                                                            class="px-2.5 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-xs font-bold hover:bg-rose-100 transition">
                                                        Reject
                                                    </button>
                                                @elseif($ac->status === 'approved')
                                                    <button type="button" 
                                                            onclick="openPayClaimModal('{{ $ac->id }}', '{{ $ac->claim_no }}', '{{ addslashes($ac->user?->name) }}', '{{ number_format((float) $ac->amount, 2) }}')"
                                                            class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1">
                                                        <span>💰 Disburse Pay</span>
                                                    </button>
                                                @else
                                                    <span class="text-slate-400 text-xs">—</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                            No claims found matching filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($allClaims->hasPages())
                        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                            {{ $allClaims->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>

    {{-- File Expense Claim Modal --}}
    <div id="submitExpenseModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 font-heading">
                    <span>🧾</span> File Travel / Field Expense Claim
                </h3>
                <button type="button" onclick="document.getElementById('submitExpenseModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">
                    &times;
                </button>
            </div>

            <form action="{{ route('finance.expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Category --}}
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Expense Category *</label>
                        <select name="expense_category" id="expenseCategorySelect" onchange="toggleKmSection(this.value)" required class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                            <option value="fuel_travel">Fuel &amp; Travel (Per-KM Calculation)</option>
                            <option value="hardware_tools">Hardware Tools, Cables &amp; Connectors</option>
                            <option value="food_lodging">Meals &amp; On-Site Allowance</option>
                            <option value="toll_parking">Toll Gates &amp; Parking Fees</option>
                            <option value="emergency_materials">Emergency Site Materials</option>
                            <option value="other">Other Operational Expense</option>
                        </select>
                    </div>

                    {{-- Expense Date --}}
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Expense Date *</label>
                        <input type="date" name="expense_date" required value="{{ now()->toDateString() }}" 
                               class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                    </div>
                </div>

                {{-- Interactive KM Calculator Strip (for Fuel & Travel) --}}
                <div id="kmCalculatorSection" class="p-3.5 rounded-xl bg-purple-50/70 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-900/50 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-purple-900 dark:text-purple-300 flex items-center gap-1.5">
                            <span>🛵</span> Smart Travel KM Calculator
                        </span>
                        <span class="text-[11px] text-purple-700 dark:text-purple-400 font-semibold">Standard: ₹6.00 / KM</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <input type="text" name="travel_from" placeholder="From Location (e.g. Office / HSR Layout)" 
                                   class="w-full text-xs rounded-xl border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                        </div>
                        <div>
                            <input type="text" name="travel_to" placeholder="To Client Site (e.g. Cyber City Tower)" 
                                   class="w-full text-xs rounded-xl border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 items-center">
                        <div>
                            <label class="block text-[11px] font-semibold text-purple-800 dark:text-purple-300 mb-0.5">Total Kilometers</label>
                            <input type="number" step="0.1" name="travel_distance_km" id="kmInput" oninput="calculateKmAmount()" placeholder="e.g. 25.5" 
                                   class="w-full text-xs rounded-xl border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-purple-800 dark:text-purple-300 mb-0.5">Rate / KM (₹)</label>
                            <input type="number" step="0.5" name="rate_per_km" id="rateInput" value="6.0" oninput="calculateKmAmount()" 
                                   class="w-full text-xs rounded-xl border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                        </div>
                    </div>
                </div>

                {{-- Amount Field --}}
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Claim Amount (₹) *</label>
                    <input type="number" step="0.01" name="amount" id="claimAmountInput" required placeholder="0.00" 
                           class="w-full text-sm font-bold rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white p-2.5">
                </div>

                {{-- Optional Link to Project / Service Ticket --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-600 dark:text-slate-400 mb-1">Link to Installation Job (Optional)</label>
                        <select name="installation_job_id" class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                            <option value="">None / General Expense</option>
                            @foreach($activeJobs as $job)
                                <option value="{{ $job->id }}">{{ $job->job_no }} - {{ $job->quotation?->lead?->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-600 dark:text-slate-400 mb-1">Link to Service Ticket (Optional)</label>
                        <select name="service_ticket_id" class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                            <option value="">None / General</option>
                            @foreach($activeTickets as $ticket)
                                <option value="{{ $ticket->id }}">{{ $ticket->ticket_no }} - {{ $ticket->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Description / Purpose *</label>
                    <textarea name="description" rows="2" required placeholder="State exact purpose of travel or list items bought on-site..." 
                              class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"></textarea>
                </div>

                {{-- Receipt Upload --}}
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Receipt / Invoice Photo (Recommended)</label>
                    <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png,.webp" 
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('submitExpenseModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 dark:text-slate-400">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition">
                        Submit Claim
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Admin Reject Claim Modal --}}
    <div id="rejectClaimModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-rose-200 dark:border-rose-900 space-y-4">
            <div class="flex items-center justify-between border-b border-rose-100 dark:border-rose-900/40 pb-3">
                <h3 class="text-base font-bold text-rose-600 dark:text-rose-400 flex items-center gap-2 font-heading">
                    <span>⚠️</span> Reject Expense Claim
                </h3>
                <button type="button" onclick="document.getElementById('rejectClaimModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">
                    &times;
                </button>
            </div>

            <p id="rejectClaimPrompt" class="text-xs text-slate-600 dark:text-slate-300">
                Please state the reason for rejecting this claim:
            </p>

            <form id="rejectClaimForm" method="POST" action="" class="space-y-4 text-xs">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Rejection Reason *</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="e.g. Receipt photo unclear, kilometers exceeded standard route..." 
                              class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('rejectClaimModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 dark:text-slate-400">
                        Back
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md transition">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Admin Disburse Payment Modal --}}
    <div id="payClaimModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-emerald-200 dark:border-emerald-900 space-y-4">
            <div class="flex items-center justify-between border-b border-emerald-100 dark:border-emerald-900/40 pb-3">
                <h3 class="text-base font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-2 font-heading">
                    <span>💰</span> Disburse Claim Reimbursement
                </h3>
                <button type="button" onclick="document.getElementById('payClaimModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">
                    &times;
                </button>
            </div>

            <div id="payClaimInfo" class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs">
                <!-- Injected via JS -->
            </div>

            <form id="payClaimForm" method="POST" action="" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Disbursal Method *</label>
                    <select name="payment_method" required class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                        <option value="upi">UPI Instant Transfer (GPay / PhonePe / Paytm)</option>
                        <option value="bank_transfer">Direct Bank Transfer (NEFT / IMPS)</option>
                        <option value="cash">Petty Cash Payment</option>
                        <option value="payroll_addition">Include in Monthly Salary Payslip</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Transaction / UTR Reference (Optional)</label>
                    <input type="text" name="payment_reference" placeholder="e.g. UPI-99881234 / NEFT-HDFC-991" 
                           class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('payClaimModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 dark:text-slate-400">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition">
                        Confirm Disbursal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleKmSection(category) {
            const kmSection = document.getElementById('kmCalculatorSection');
            if (category === 'fuel_travel') {
                kmSection.classList.remove('hidden');
            } else {
                kmSection.classList.add('hidden');
            }
        }

        function calculateKmAmount() {
            const km = parseFloat(document.getElementById('kmInput').value) || 0;
            const rate = parseFloat(document.getElementById('rateInput').value) || 6.0;
            if (km > 0) {
                const total = (km * rate).toFixed(2);
                document.getElementById('claimAmountInput').value = total;
            }
        }

        function openRejectClaimModal(claimId, claimNo, employeeName, amount) {
            const modal = document.getElementById('rejectClaimModal');
            const prompt = document.getElementById('rejectClaimPrompt');
            const form = document.getElementById('rejectClaimForm');

            prompt.innerHTML = `Rejecting Claim <strong>#${claimNo}</strong> (${amount}) for <strong>${employeeName}</strong>. Please provide a clear explanation:`;
            form.action = `/finance/expenses/${claimId}/reject`;
            modal.classList.remove('hidden');
        }

        function openPayClaimModal(claimId, claimNo, employeeName, amount) {
            const modal = document.getElementById('payClaimModal');
            const info = document.getElementById('payClaimInfo');
            const form = document.getElementById('payClaimForm');

            info.innerHTML = `Reimbursing Claim <strong>#${claimNo}</strong> for <strong>${employeeName}</strong> &bull; Amount: <strong class="text-emerald-700 dark:text-emerald-300 font-heading">₹${amount}</strong>`;
            form.action = `/finance/expenses/${claimId}/pay`;
            modal.classList.remove('hidden');
        }
    </script>
</x-app-layout>
