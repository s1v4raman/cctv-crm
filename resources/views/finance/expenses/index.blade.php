<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        Expense claims
                    </h2>
                    @if($canManage && $pendingCount > 0)
                        <span class="text-xs font-bold text-[#d97706]">
                            ({{ $pendingCount }} Pending: ₹{{ number_format($pendingTotalAmount, 2) }})
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if(auth()->user()->isAdmin())
                <a href="{{ route('finance.payroll.index') }}" class="btn-secondary">
                    Payroll
                </a>
                @endif

                <button type="button" 
                        onclick="document.getElementById('submitExpenseModal').classList.remove('hidden')"
                        class="btn-primary cursor-pointer">
                    New Claim
                </button>
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
            border-color: var(--text-main);
        }
        .stat-label { font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 0.25rem; }
        html.dark .stat-label { color: var(--text-muted); }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: var(--text-main); line-height: 1; }
        html.dark .stat-value { color: #ffffff; }

        .pg-card {
            background: #ffffff;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        html.dark .pg-card {
            background: #0f172a;
            border-color: var(--text-main);
        }
    </style>

    <div class="py-6 bg-slate-50 dark:bg-[#060913] min-h-[calc(100vh-4.5rem)] text-slate-800 dark:text-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-employee-subnav />

            {{-- Flash Notification Status --}}
            @if(session('status'))
                <div class="p-4 rounded bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between" style="border-radius: 4px;">
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-semibold flex items-center justify-between" style="border-radius: 4px;">
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- 1. Macro KPIs Summary Grid --}}
            <div class="stat-grid">
                {{-- Total Claimed This Month --}}
                <div class="stat-card">
                    <div class="stat-label">Claims This Month</div>
                    <div class="stat-value text-blue-600 dark:text-blue-400">₹{{ number_format($totalClaimedMonth, 2) }}</div>
                    <div class="mt-2 text-xs text-slate-500">
                        {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}
                    </div>
                </div>

                {{-- Pending Approvals --}}
                <div class="stat-card">
                    <div class="stat-label">Pending Review</div>
                    <div class="stat-value text-amber-600 dark:text-amber-400">₹{{ number_format($pendingTotalAmount, 2) }}</div>
                    <div class="mt-2 text-xs text-slate-500">
                        {{ $pendingCount }} claim(s) waiting
                    </div>
                </div>

                {{-- Disbursed / Paid Amount --}}
                <div class="stat-card">
                    <div class="stat-label">Settled / Paid</div>
                    <div class="stat-value text-emerald-600 dark:text-emerald-400">₹{{ number_format($totalPaidMonth, 2) }}</div>
                    <div class="mt-2 text-xs text-slate-500">
                        Approved: ₹{{ number_format($totalApprovedMonth, 2) }}
                    </div>
                </div>

                {{-- Fuel & Travel Distance Sum --}}
                <div class="stat-card">
                    <div class="stat-label">Fuel &amp; Travel Log</div>
                    <div class="stat-value text-purple-600 dark:text-purple-400">{{ number_format($totalKmMonth, 1) }} KM</div>
                    <div class="mt-2 text-xs text-slate-500">
                        Fuel Paid: ₹{{ number_format($totalFuelMonth, 2) }}
                    </div>
                </div>
            </div>

            {{-- 2. Admin Pending Approval & Disbursal Queue --}}
            @if($canManage && $pendingClaims->count() > 0)
                <div id="pendingQueueSection" class="pg-card mb-6" style="border-radius: 4px;">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                Pending Expense Claims Review ({{ $pendingClaims->count() }})
                            </h3>
                        </div>
                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400">Total: ₹{{ number_format($pendingTotalAmount, 2) }}</span>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($pendingClaims as $c)
                            @php
                                $isPendingTarget = (request('claim_id') == $c->id || request('claim_no') == $c->claim_no);
                            @endphp
                            <div id="pending-claim-{{ $c->id }}" class="p-4 {{ $isPendingTarget ? 'bg-amber-50 dark:bg-amber-950/40 ring-2 ring-amber-400 rounded-lg' : 'hover:bg-slate-50 dark:hover:bg-slate-800/40' }} transition flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div class="space-y-1 max-w-2xl">
                                    <div class="flex items-center gap-2 flex-wrap text-sm">
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $c->user?->name }}</span>
                                        <span class="text-xs text-slate-500 uppercase">
                                            ({{ $c->user?->role }})
                                        </span>
                                        <span class="font-bold text-blue-600 dark:text-blue-400 text-xs">
                                            {{ $c->category_label }}
                                        </span>
                                        <span class="font-mono text-xs text-slate-400">#{{ $c->claim_no }}</span>
                                    </div>

                                    <div class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-2 flex-wrap">
                                        <span>Date: <strong>{{ $c->expense_date->format('d M Y') }}</strong></span>
                                        @if($c->travel_distance_km > 0)
                                            <span>&bull;</span>
                                            <span><strong>{{ $c->travel_distance_km }} KM</strong> ({{ $c->travel_from }} &rarr; {{ $c->travel_to }})</span>
                                        @endif
                                        @if($c->site)
                                            <span>&bull;</span>
                                            <span class="text-blue-600 dark:text-blue-400 font-bold">📍 Site: <strong>{{ $c->site->name }}</strong></span>
                                        @elseif($c->project)
                                            <span>&bull;</span>
                                            <span class="text-indigo-600 dark:text-indigo-400 font-bold">🏢 Project: <strong>{{ $c->project->title }}</strong></span>
                                        @elseif($c->installationJob)
                                            <span>&bull;</span>
                                            <span>Job: <strong>{{ $c->installationJob->job_no }}</strong></span>
                                        @endif
                                        @if($c->serviceTicket)
                                            <span>&bull;</span>
                                            <span>Ticket: <strong>{{ $c->serviceTicket->ticket_no }}</strong></span>
                                        @endif
                                    </div>

                                    <p class="text-xs text-slate-700 dark:text-slate-300 p-2 bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800" style="border-radius: 4px;">
                                        {{ $c->description }}
                                    </p>

                                    @if($c->receipt_path)
                                        <div class="pt-0.5">
                                            <a href="{{ asset('storage/' . $c->receipt_path) }}" target="_blank" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                                View Attached Receipt
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-3 flex-shrink-0">
                                    <div class="text-right">
                                        <div class="text-base font-bold text-slate-900 dark:text-white">
                                            ₹{{ number_format((float) $c->amount, 2) }}
                                        </div>
                                    </div>

                                    {{-- Edit Trigger Button (Admin / Owner) --}}
                                    <button type="button" 
                                            onclick='openEditClaimModal(@json($c))'
                                            class="px-2.5 py-1.5 rounded border border-blue-300 dark:border-blue-700 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 text-xs font-semibold transition" style="border-radius: 4px;" title="Edit Claim Amount / KM / Details">
                                        Edit
                                    </button>

                                    {{-- Approve Form --}}
                                    @if(auth()->user()->canDecideFor($c->user_id))
                                    <form action="{{ route('finance.expenses.approve', $c) }}" method="POST" data-confirm="Approve expense claim #{{ $c->claim_no }} for ₹{{ number_format((float) $c->amount, 2) }}?">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="btn-md px-3 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition" style="border-radius: 4px;">
                                            Approve
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Reject Trigger Button --}}
                                    @if(auth()->user()->canDecideFor($c->user_id))
                                    <button type="button" 
                                            onclick="openRejectClaimModal('{{ $c->id }}', '{{ $c->claim_no }}', '{{ addslashes($c->user?->name) }}', '₹{{ number_format((float) $c->amount, 2) }}')"
                                            class="px-3 py-1.5 rounded border border-rose-300 dark:border-rose-700 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-xs font-semibold transition" style="border-radius: 4px;">
                                        Reject
                                    </button>
                                    @endif

                                    {{-- Admin Delete Claim --}}
                                    @if(auth()->user()->isOffice())
                                    <form action="{{ route('finance.expenses.destroy', $c) }}" method="POST" data-confirm="Permanently delete claim #{{ $c->claim_no }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1.5 rounded border border-slate-300 dark:border-slate-700 text-slate-500 hover:text-rose-600 hover:border-rose-300 text-xs font-semibold transition" style="border-radius: 4px;" title="Delete Claim">
                                            Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 3. Main Tab Navigation (My Claims vs Workforce All Claims) --}}
            <div class="pg-card">
                <div class="border-b border-slate-200 dark:border-slate-800 px-4 pt-3 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('finance.expenses.index', ['tab' => 'my_claims', 'month' => $selectedMonth]) }}" 
                           class="px-3 py-1.5 text-xs font-semibold rounded transition {{ $tab === 'my_claims' ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}" style="border-radius: 4px;">
                            My Claims ({{ $myClaims->total() }})
                        </a>

                        @if($canManage)
                            <a href="{{ route('finance.expenses.index', ['tab' => 'all_claims', 'month' => $selectedMonth]) }}" 
                               class="px-3 py-1.5 text-xs font-semibold rounded transition {{ $tab === 'all_claims' ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}" style="border-radius: 4px;">
                                Workforce Claims Ledger ({{ $allClaims->total() }})
                            </a>
                        @endif
                    </div>

                    {{-- Filters --}}
                    <form method="GET" action="{{ route('finance.expenses.index') }}" class="flex items-center gap-2 pb-2 flex-wrap">
                        <input type="hidden" name="tab" value="{{ $tab }}">

                        <input aria-label="Month" type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()" class="text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3" style="height: 38px; border-radius: 4px;">

                        <select aria-label="Category" name="category" onchange="this.form.submit()" class="text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3" style="height: 38px; border-radius: 4px;">
                            <option value="">All Categories</option>
                            <option value="fuel_travel" {{ $categoryFilter === 'fuel_travel' ? 'selected' : '' }}>Fuel &amp; Travel</option>
                            <option value="hardware_tools" {{ $categoryFilter === 'hardware_tools' ? 'selected' : '' }}>Hardware Tools</option>
                            <option value="food_lodging" {{ $categoryFilter === 'food_lodging' ? 'selected' : '' }}>Food &amp; Lodging</option>
                            <option value="toll_parking" {{ $categoryFilter === 'toll_parking' ? 'selected' : '' }}>Toll &amp; Parking</option>
                            <option value="emergency_materials" {{ $categoryFilter === 'emergency_materials' ? 'selected' : '' }}>Emergency Supplies</option>
                            <option value="other" {{ $categoryFilter === 'other' ? 'selected' : '' }}>Other</option>
                        </select>

                        <select aria-label="Status" name="status" onchange="this.form.submit()" class="text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-1.5 pl-3 pr-8">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="paid" {{ $statusFilter === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>

                        @if($tab === 'all_claims' && $canManage)
                            <select aria-label="Employee" name="employee_id" onchange="this.form.submit()" class="text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-1.5 pl-3 pr-8">
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
                                                    <a href="{{ asset('storage/' . $c->receipt_path) }}" target="_blank" class="text-[11px] text-blue-600 dark:text-blue-400 font-bold hover:underline">
                                                        Receipt Attached
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <div class="font-semibold text-slate-900 dark:text-white">{{ $c->expense_date->format('d M Y') }}</div>
                                            @if($c->travel_distance_km > 0)
                                                <div class="text-[11px] text-purple-600 dark:text-purple-400 font-bold mt-0.5">
                                                    {{ $c->travel_distance_km }} KM ({{ $c->travel_from }} &rarr; {{ $c->travel_to }})
                                                </div>
                                            @endif
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xs truncate mt-0.5" title="{{ $c->description }}">
                                                {{ $c->description }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            @if($c->site)
                                                <div class="font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1">
                                                    <span>📍</span> {{ $c->site->name }}
                                                </div>
                                                <div class="text-[11px] text-slate-400 truncate max-w-[200px]" title="{{ $c->site->address }}">{{ $c->site->address }}</div>
                                            @elseif($c->project)
                                                <div class="font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                                                    <span>🏢</span> {{ $c->project->title }}
                                                </div>
                                                <div class="text-[11px] text-slate-400 truncate max-w-[200px]" title="{{ $c->project->site_address ?? $c->project->company_name }}">{{ $c->project->site_address ?? $c->project->company_name }}</div>
                                            @elseif($c->installationJob)
                                                <div class="font-bold text-slate-800 dark:text-slate-200">🔧 {{ $c->installationJob->job_no }}</div>
                                                <div class="text-[11px] text-slate-400">{{ $c->installationJob->quotation?->lead?->customer_name }}</div>
                                            @elseif($c->serviceTicket)
                                                <div class="font-bold text-slate-800 dark:text-slate-200">🎫 {{ $c->serviceTicket->ticket_no }}</div>
                                                <div class="text-[11px] text-slate-400">{{ $c->serviceTicket->title }}</div>
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
                                                <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">
                                                    Disbursed via {{ ucfirst(str_replace('_', ' ', $c->payment_method)) }}
                                                </div>
                                            @endif
                                            @if($c->rejection_reason)
                                                <div class="text-[11px] text-rose-600 dark:text-rose-400 font-semibold mt-0.5">
                                                    {{ $c->rejection_reason }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-right whitespace-nowrap">
                                            @if($c->status === 'pending')
                                                <form action="{{ route('finance.expenses.cancel', $c) }}" method="POST" data-confirm="Cancel this pending claim?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 text-sm font-semibold underline">
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
                @if($tab === 'all_claims' && $canManage)
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
                                    @php
                                        $isAllTarget = (request('claim_id') == $ac->id || request('claim_no') == $ac->claim_no);
                                    @endphp
                                    <tr id="claim-row-{{ $ac->id }}" class="{{ $isAllTarget ? 'bg-amber-50 dark:bg-amber-950/40 ring-2 ring-amber-400' : 'hover:bg-slate-50/50 dark:hover:bg-slate-800/30' }} transition">
                                        <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                            <div class="font-bold text-slate-900 dark:text-white">{{ $ac->user?->name }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $ac->user?->email }} &bull; <strong class="uppercase">{{ $ac->user?->role }}</strong></div>
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5 font-mono font-bold text-slate-900 dark:text-white">
                                                <span>#{{ $ac->claim_no }}</span>
                                                @if($isAllTarget)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-white animate-pulse">Alert Match</span>
                                                @endif
                                            </div>
                                            <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $ac->category_badge_class }}">
                                                {{ $ac->category_label }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="font-semibold text-slate-900 dark:text-white">{{ $ac->expense_date->format('d M Y') }}</div>
                                            @if($ac->travel_distance_km > 0)
                                                <div class="text-[11px] text-purple-600 dark:text-purple-400 font-bold">
                                                    {{ $ac->travel_distance_km }} KM ({{ $ac->travel_from }} &rarr; {{ $ac->travel_to }})
                                                </div>
                                            @endif
                                            <div class="text-[11px] text-slate-500 line-clamp-1">{{ $c->description ?? $ac->description }}</div>
                                            @if($ac->site)
                                                <div class="text-[11px] text-blue-600 dark:text-blue-400 font-bold flex items-center gap-1 mt-0.5">
                                                    <span>📍</span> {{ $ac->site->name }} @if($ac->site->client_name)({{ $ac->site->client_name }})@endif
                                                </div>
                                            @elseif($ac->project)
                                                <div class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold flex items-center gap-1 mt-0.5">
                                                    <span>🏢</span> {{ $ac->project->title }}
                                                </div>
                                            @elseif($ac->installationJob)
                                                <div class="text-[11px] text-slate-700 dark:text-slate-300 font-semibold mt-0.5">
                                                    <span>🔧</span> Job #{{ $ac->installationJob->job_no }}
                                                </div>
                                            @endif
                                            @if($ac->receipt_path)
                                                <a href="{{ asset('storage/' . $ac->receipt_path) }}" target="_blank" class="text-[11px] text-blue-600 dark:text-blue-400 font-bold hover:underline">
                                                    Receipt Proof
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
                                                <div class="text-[11px] text-emerald-600 font-bold mt-0.5">
                                                    Disbursed via {{ ucfirst(str_replace('_', ' ', $ac->payment_method)) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                                {{-- Edit Claim (Admin or Owner) --}}
                                                <button type="button" 
                                                        onclick='openEditClaimModal(@json($ac))'
                                                        class="min-h-[34px] px-2.5 py-1.5 bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 rounded-lg text-xs font-semibold transition" title="Edit Claim">
                                                    Edit
                                                </button>

                                                @if($ac->status === 'pending' && auth()->user()->canDecideFor($ac->user_id))
                                                    <form action="{{ route('finance.expenses.approve', $ac) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="min-h-[34px] px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    <button type="button" 
                                                            onclick="openRejectClaimModal('{{ $ac->id }}', '{{ $ac->claim_no }}', '{{ addslashes($ac->user?->name) }}', '₹{{ number_format((float) $ac->amount, 2) }}')"
                                                            class="min-h-[34px] px-2.5 py-1.5 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-xs font-semibold hover:bg-rose-100 transition">
                                                        Reject
                                                    </button>
                                                @elseif($ac->status === 'approved' && auth()->user()->canDecideFor($ac->user_id))
                                                    <button type="button" 
                                                            onclick="openPayClaimModal('{{ $ac->id }}', '{{ $ac->claim_no }}', '{{ addslashes($ac->user?->name) }}', '{{ number_format((float) $ac->amount, 2) }}')"
                                                            class="min-h-[34px] px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1">
                                                        <span>Disburse</span>
                                                    </button>
                                                @endif

                                                {{-- Delete Claim (Admin) --}}
                                                @if(auth()->user()->isOffice())
                                                <form action="{{ route('finance.expenses.destroy', $ac) }}" method="POST" data-confirm="Permanently delete claim #{{ $ac->claim_no }}?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="min-h-[34px] px-2 py-1.5 rounded border border-slate-300 dark:border-slate-700 text-slate-500 hover:text-rose-600 hover:border-rose-300 text-xs font-semibold transition" title="Delete Claim">
                                                        ✕
                                                    </button>
                                                </form>
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
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 font-heading">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>File Travel / Field Expense Claim</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Submit fuel travel (KM) or on-site operational expense claim</p>
                </div>
                <button type="button" 
                        onclick="document.getElementById('submitExpenseModal').classList.add('hidden')" 
                        class="inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 dark:text-slate-300 dark:hover:text-white transition shadow-xs cursor-pointer" 
                        title="Close (Esc)" 
                        aria-label="Close">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ route('finance.expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs" x-data="formSteps">
                @csrf
                <x-form-stepper :steps="['Expense & amount', 'Purpose & receipt']" />

                <div data-step x-show="step === 1" class="space-y-4">
                    @if($canManage)
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Claimant (Employee / Technician) *</label>
                        <select aria-label="Claimant" name="user_id" required class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                            @foreach($workforce as $w)
                                <option value="{{ $w->id }}" {{ $w->id === auth()->id() ? 'selected' : '' }}>
                                    {{ $w->name }} ({{ ucfirst($w->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Category --}}
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Expense Category *</label>
                            <select aria-label="Expense Category" name="expense_category" id="expenseCategorySelect" onchange="toggleKmSection(this.value)" required class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
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
                            <input aria-label="Expense Date" type="date" name="expense_date" required value="{{ now()->toDateString() }}" 
                                   class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                        </div>
                    </div>

                    {{-- Interactive KM Calculator Strip (for Fuel & Travel) --}}
                    <div id="kmCalculatorSection" class="p-3.5 rounded-xl bg-purple-50/70 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-900/50 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-purple-900 dark:text-purple-300 flex items-center gap-1.5">
                                Smart Travel KM Calculator
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-purple-700 dark:text-purple-400 font-semibold">Standard: ₹6.00 / KM</span>
                                <button type="button" onclick="resetKmCalculator()" title="Reset / Clear KM Calculator" class="text-[11px] text-purple-600 dark:text-purple-400 hover:text-purple-900 dark:hover:text-purple-200 font-semibold inline-flex items-center gap-1 px-2 py-0.5 rounded hover:bg-purple-100 dark:hover:bg-purple-900/50 transition cursor-pointer">
                                    <span>✕ Reset</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <input aria-label="From Location (e.g. Office / HSR Layout)" type="text" name="travel_from" placeholder="From Location (e.g. Office / HSR Layout)" 
                                       class="w-full text-sm min-h-[44px] rounded-xl border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                            </div>
                            <div>
                                <input aria-label="To Client Site (e.g. Cyber City Tower)" type="text" name="travel_to" placeholder="To Client Site (e.g. Cyber City Tower)" 
                                       class="w-full text-sm min-h-[44px] rounded-xl border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 items-center">
                            <div>
                                <label class="block text-sm font-semibold text-purple-800 dark:text-purple-300 mb-1">Total Kilometers</label>
                                <input aria-label="Total Kilometers" type="number" step="0.1" name="travel_distance_km" id="kmInput" oninput="calculateKmAmount()" placeholder="e.g. 25.5" 
                                       class="w-full text-sm min-h-[44px] rounded-xl border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-purple-800 dark:text-purple-300 mb-1">Rate / KM (₹)</label>
                                <input aria-label="Rate / KM (₹)" type="number" step="0.5" name="rate_per_km" id="rateInput" value="6.0" oninput="calculateKmAmount()" 
                                       class="w-full text-sm min-h-[44px] rounded-xl border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                            </div>
                        </div>
                    </div>

                    {{-- Amount Field --}}
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Claim Amount (₹) *</label>
                        <input aria-label="Claim Amount (₹)" type="number" step="0.01" name="amount" id="claimAmountInput" required placeholder="0.00" 
                               class="w-full text-sm font-bold rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white p-2.5">
                    </div>
                </div>

                <div data-step x-show="step === 2" x-cloak class="space-y-4">
                    {{-- Installation Site / Project Link & Service Ticket --}}
                    <div x-data="{ showNewSiteForm: false }" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold text-slate-700 dark:text-slate-300 text-sm">
                                📍 Installation Site / Facility (Optional)
                            </label>
                            <button type="button" @click="showNewSiteForm = !showNewSiteForm" 
                                    class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                <span x-text="showNewSiteForm ? '✕ Close Form' : '+ Add New Installation Site'"></span>
                            </button>
                        </div>

                        <select name="linked_target" id="adminExpenseSiteSelect" onchange="onAdminSiteSelected(this)"
                                class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                            <option value="">-- None / General Field & Office Expense --</option>

                            {{-- Admin-Assigned Projects --}}
                            @if(isset($assignedProjects) && $assignedProjects->isNotEmpty())
                                <optgroup label="🏢 Admin-Assigned Projects & Installation Sites">
                                    @foreach($assignedProjects as $prj)
                                        @php
                                            $pSite = $prj->site;
                                            $pAddress = $prj->site_address ?: ($pSite?->address ?: 'Customer Location');
                                            $pClient = $prj->company_name ?: ($pSite?->client_name ?: 'Client');
                                            $pPhone = $prj->contact_phone ?: ($pSite?->phone ?: '');
                                        @endphp
                                        <option value="project:{{ $prj->id }}" 
                                                data-type="project"
                                                data-title="{{ $prj->title }} ({{ $prj->project_code }})" 
                                                data-client="{{ $pClient }}" 
                                                data-address="{{ $pAddress }}" 
                                                data-phone="{{ $pPhone }}" 
                                                data-status="Admin Assigned Project ({{ ucfirst(str_replace('_', ' ', $prj->status)) }})">
                                            🏢 {{ $prj->project_code }} — {{ $prj->title }} [{{ $pClient }}]
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            {{-- Registered Installation Sites --}}
                            @if(isset($availableSites) && $availableSites->isNotEmpty())
                                <optgroup label="📍 Registered Installation Sites">
                                    @foreach($availableSites as $st)
                                        <option value="site:{{ $st->id }}" 
                                                data-type="site"
                                                data-title="{{ $st->name }}" 
                                                data-client="{{ $st->client_name ?? '' }}" 
                                                data-address="{{ $st->address ?? '' }}" 
                                                data-phone="{{ $st->phone ?? '' }}" 
                                                data-status="Registered Installation Site ({{ $st->site_code ?? 'SITE' }})">
                                            📍 {{ $st->name }} @if($st->client_name)({{ $st->client_name }})@endif
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            {{-- Active Installation Jobs --}}
                            @if(isset($activeJobs) && $activeJobs->isNotEmpty())
                                <optgroup label="🔧 Field Installation Jobs">
                                    @foreach($activeJobs as $job)
                                        @php $jLead = $job->quotation?->lead; @endphp
                                        <option value="job:{{ $job->id }}" 
                                                data-type="job"
                                                data-title="{{ $job->job_no }}" 
                                                data-client="{{ $jLead?->customer_name ?? 'Customer' }}" 
                                                data-address="{{ $jLead?->site_address ?? ($jLead?->address ?? '') }}" 
                                                data-phone="{{ $jLead?->phone ?? '' }}" 
                                                data-status="Installation Job ({{ ucfirst($job->status) }})">
                                            🔧 {{ $job->job_no }} — {{ $jLead?->customer_name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>

                        {{-- Live Display of Selected Installation Site Data --}}
                        <div id="adminSiteDetailsCard" class="hidden p-3 rounded-xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/70 text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span id="adminCardBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-600 text-white shadow-xs">
                                    Admin Assigned Installation Site
                                </span>
                                <button type="button" onclick="setAdminSiteAsDestination()" class="text-[11px] text-blue-700 dark:text-blue-300 font-bold hover:underline cursor-pointer">
                                    Set as Travel Destination →
                                </button>
                            </div>
                            <div class="font-bold text-slate-900 dark:text-white text-xs mt-1" id="adminCardTitle">--</div>
                            <div class="text-[11px] text-slate-600 dark:text-slate-300 flex items-start gap-1" id="adminCardAddressRow">
                                <svg class="w-3.5 h-3.5 shrink-0 text-slate-400 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                <span id="adminCardAddress">--</span>
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-3 pt-0.5" id="adminCardMeta">
                                <span id="adminCardClient">--</span>
                                <span id="adminCardPhone">--</span>
                            </div>
                        </div>

                        {{-- Inline Add New Installation Site Panel --}}
                        <div x-show="showNewSiteForm" x-cloak class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-blue-300 dark:border-blue-700 space-y-2.5 shadow-sm">
                            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-1.5">
                                <span class="text-xs font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1">
                                    ➕ Register New Installation Site for Claim
                                </span>
                                <span class="text-[10px] text-slate-400">Stores site in CRM & links to this allowance</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-0.5">Site / Facility Name *</label>
                                    <input type="text" name="new_site_name" id="adminInlineNewSiteName" placeholder="e.g. Reliance Smart Point - Koramangala" 
                                           class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-0.5">Client / Organization</label>
                                    <input type="text" name="new_client_name" id="adminInlineNewClientName" placeholder="e.g. Reliance Retail Ltd" 
                                           class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-0.5">Installation Site Address *</label>
                                <input type="text" name="new_site_address" id="adminInlineNewSiteAddress" placeholder="e.g. #42, 80 Feet Road, 4th Block, Koramangala, Bengaluru" 
                                       class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 items-center">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-0.5">Site Contact Phone (Optional)</label>
                                    <input type="tel" name="new_site_phone" id="adminInlineNewSitePhone" placeholder="e.g. 9876543210" 
                                           class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2">
                                </div>
                                <div class="pt-4 text-right">
                                    <button type="button" onclick="quickSaveAdminNewSite()" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                        ✓ Create & Select Site
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 dark:text-slate-400 mb-1">Link to Service Ticket (Optional)</label>
                            <select aria-label="Link to Service Ticket (Optional)" name="service_ticket_id" class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                                <option value="">None / General</option>
                                @foreach($activeTickets as $ticket)
                                    <option value="{{ $ticket->id }}">{{ $ticket->ticket_no }} - {{ $ticket->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Description (Optional) --}}
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Description / Purpose (Optional)</label>
                        <textarea aria-label="Description / Purpose" name="description" rows="2" placeholder="State site location, work done, or items purchased (optional)..." 
                                  class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"></textarea>
                    </div>

                    {{-- Receipt Upload --}}
                    <div>
                        <label for="f-receipt" class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Receipt / Invoice Photo (Recommended)</label>
                        <input id="f-receipt" type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png,.webp" 
                               class="w-full">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('submitExpenseModal').classList.add('hidden')" 
                            class="btn-md inline-flex items-center gap-1.5 px-4 py-2.5 min-h-[44px] text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 shadow-xs transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Cancel</span>
                    </button>
                    <x-form-step-buttons />
                    <template x-if="isLast()">
                        <button type="submit" class="btn-md px-6 py-2.5 min-h-[44px] text-sm font-bold text-white rounded-xl shadow-md transition cursor-pointer" style="background-color: var(--crm-accent, #2563eb);">
                            Submit Claim
                        </button>
                    </template>
                </div>
            </form>
        </div>
    </div>

    {{-- Admin Reject Claim Modal --}}
    <div id="rejectClaimModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-rose-200 dark:border-rose-900 space-y-4">
            <div class="flex items-center justify-between border-b border-rose-100 dark:border-rose-900/40 pb-3">
                <h3 class="text-base font-bold text-rose-600 dark:text-rose-400 flex items-center gap-2 font-heading">
                    Reject Expense Claim
                </h3>
                <button type="button" 
                        onclick="document.getElementById('rejectClaimModal').classList.add('hidden')" 
                        class="inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 dark:text-slate-300 dark:hover:text-white transition shadow-xs cursor-pointer" 
                        title="Close" 
                        aria-label="Close">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
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
                    <textarea aria-label="Rejection Reason" name="rejection_reason" rows="3" required placeholder="e.g. Receipt photo unclear, kilometers exceeded standard route..." 
                              class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('rejectClaimModal').classList.add('hidden')" 
                            class="btn-md inline-flex items-center gap-1.5 px-4 py-2.5 min-h-[44px] text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 shadow-xs transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Cancel</span>
                    </button>
                    <button type="submit" class="btn-md px-6 py-2.5 min-h-[44px] text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md transition">
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
                    Disburse Claim Reimbursement
                </h3>
                <button type="button" 
                        onclick="document.getElementById('payClaimModal').classList.add('hidden')" 
                        class="inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 dark:text-slate-300 dark:hover:text-white transition shadow-xs cursor-pointer" 
                        title="Close" 
                        aria-label="Close">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div id="payClaimInfo" class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs">
                <!-- Injected via JS -->
            </div>

            <form id="payClaimForm" method="POST" action="" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Disbursal Method *</label>
                    <select aria-label="Disbursal Method" name="payment_method" required class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                        <option value="upi">UPI Instant Transfer (GPay / PhonePe / Paytm)</option>
                        <option value="bank_transfer">Direct Bank Transfer (NEFT / IMPS)</option>
                        <option value="cash">Petty Cash Payment</option>
                        <option value="payroll_addition">Include in Monthly Salary Payslip</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Transaction / UTR Reference (Optional)</label>
                    <input aria-label="Transaction / UTR Reference (Optional)" type="text" name="payment_reference" placeholder="e.g. UPI-99881234 / NEFT-HDFC-991" 
                           class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('payClaimModal').classList.add('hidden')" 
                            class="btn-md inline-flex items-center gap-1.5 px-4 py-2.5 min-h-[44px] text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 shadow-xs transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Cancel</span>
                    </button>
                    <button type="submit" class="btn-md px-6 py-2.5 min-h-[44px] text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition">
                        Confirm Disbursal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Admin / Management Edit Claim Modal --}}
    <div id="editExpenseModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 font-heading">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span id="editModalTitle">Edit Claim / Allowance</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Admin can adjust KM, cost per KM, amount, or site allowance details</p>
                </div>
                <button type="button" 
                        onclick="document.getElementById('editExpenseModal').classList.add('hidden')" 
                        class="inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 dark:text-slate-300 dark:hover:text-white transition shadow-xs cursor-pointer" 
                        title="Close" 
                        aria-label="Close">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="editExpenseForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                {{-- Claimant Summary Info --}}
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="text-[11px] text-slate-500 uppercase font-semibold">Claimant</div>
                        <div id="editClaimantName" class="font-bold text-slate-900 dark:text-white text-sm">--</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[11px] text-slate-500 uppercase font-semibold">Claim Reference</div>
                        <div id="editClaimNo" class="font-mono font-bold text-blue-600 dark:text-blue-400">#--</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Category --}}
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Expense Category *</label>
                        <select aria-label="Expense Category" name="expense_category" id="editExpenseCategory" onchange="toggleEditKmSection(this.value)" required class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
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
                        <input aria-label="Expense Date" type="date" name="expense_date" id="editExpenseDate" required 
                               class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                    </div>
                </div>

                {{-- Interactive KM Calculator Strip (for Fuel & Travel) --}}
                <div id="editKmCalculatorSection" class="p-3.5 rounded-xl bg-purple-50/70 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-900/50 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-purple-900 dark:text-purple-300 flex items-center gap-1.5">
                            Travel Distance &amp; KM Rate
                        </span>
                        <span class="text-[11px] text-purple-700 dark:text-purple-400 font-semibold">Live Auto-Calculation</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-purple-800 dark:text-purple-300 mb-1">From Location</label>
                            <input aria-label="From Location" type="text" name="travel_from" id="editTravelFrom" placeholder="From Location" 
                                   class="w-full text-sm min-h-[40px] rounded-xl border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-purple-800 dark:text-purple-300 mb-1">To Site / Destination</label>
                            <input aria-label="To Destination" type="text" name="travel_to" id="editTravelTo" placeholder="To Site / Destination" 
                                   class="w-full text-sm min-h-[40px] rounded-xl border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 items-center">
                        <div>
                            <label class="block text-xs font-semibold text-purple-800 dark:text-purple-300 mb-1">Distance (KM)</label>
                            <input aria-label="Distance KM" type="number" step="0.1" name="travel_distance_km" id="editKmInput" oninput="calculateEditKmAmount()" placeholder="0.0" 
                                   class="w-full text-sm min-h-[40px] rounded-xl border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-purple-800 dark:text-purple-300 mb-1">Rate / KM (₹)</label>
                            <input aria-label="Rate / KM (₹)" type="number" step="0.5" name="rate_per_km" id="editRateInput" oninput="calculateEditKmAmount()" 
                                   class="w-full text-sm min-h-[40px] rounded-xl border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                        </div>
                    </div>
                </div>

                {{-- Amount and Status --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Claim / Allowance Amount (₹) *</label>
                        <input aria-label="Claim Amount (₹)" type="number" step="0.01" name="amount" id="editClaimAmountInput" required placeholder="0.00" 
                               class="w-full text-sm font-bold rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white p-2.5">
                        <p class="text-[10px] text-slate-400 mt-0.5">Admin edit here updates the allowance on the technician's portal immediately.</p>
                    </div>

                    @if(auth()->user()->isOffice())
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Claim Status *</label>
                        <select aria-label="Status" name="status" id="editStatus" required class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                            <option value="pending">Pending Review</option>
                            <option value="approved">Approved</option>
                            <option value="paid">Paid / Disbursed</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    @endif
                </div>

                {{-- Link to Site / Project / Job / Ticket --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-600 dark:text-slate-400 mb-1">📍 Installation Site / Project</label>
                        <select name="linked_target" id="editLinkedTarget" class="w-full text-sm min-h-[40px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                            <option value="">None / General Expense</option>
                            @if(isset($assignedProjects) && $assignedProjects->isNotEmpty())
                                <optgroup label="🏢 Admin-Assigned Projects">
                                    @foreach($assignedProjects as $prj)
                                        <option value="project:{{ $prj->id }}">🏢 {{ $prj->project_code }} — {{ $prj->title }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if(isset($availableSites) && $availableSites->isNotEmpty())
                                <optgroup label="📍 Registered Sites">
                                    @foreach($availableSites as $st)
                                        <option value="site:{{ $st->id }}">📍 {{ $st->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if(isset($activeJobs) && $activeJobs->isNotEmpty())
                                <optgroup label="🔧 Installation Jobs">
                                    @foreach($activeJobs as $job)
                                        <option value="job:{{ $job->id }}">🔧 {{ $job->job_no }} — {{ $job->quotation?->lead?->customer_name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-600 dark:text-slate-400 mb-1">Service Ticket Link</label>
                        <select aria-label="Service Ticket" name="service_ticket_id" id="editServiceTicketId" class="w-full text-sm min-h-[40px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                            <option value="">None / General</option>
                            @foreach($activeTickets as $ticket)
                                <option value="{{ $ticket->id }}">{{ $ticket->ticket_no }} - {{ $ticket->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Description & Admin Notes --}}
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Description / Purpose (Optional)</label>
                    <textarea aria-label="Description" name="description" id="editDescription" rows="2" placeholder="State site location, work done, or items purchased (optional)..." 
                              class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"></textarea>
                </div>

                @if(auth()->user()->isOffice())
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Management / Admin Notes</label>
                    <textarea aria-label="Admin Notes" name="admin_notes" id="editAdminNotes" rows="2" placeholder="e.g. Adjusted KM according to Google Maps odometer / approved with site allowance" 
                              class="w-full text-sm min-h-[44px] rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"></textarea>
                </div>
                @endif

                {{-- Receipt Attachment --}}
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Update Receipt / Proof Photo</label>
                    <div id="currentReceiptDiv" class="mb-2 hidden">
                        <a id="editReceiptLink" href="#" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 font-bold hover:underline inline-flex items-center gap-1">
                            <span>📄 View Current Uploaded Receipt</span>
                        </a>
                    </div>
                    <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('editExpenseModal').classList.add('hidden')" 
                            class="btn-md inline-flex items-center gap-1.5 px-4 py-2.5 min-h-[44px] text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 shadow-xs transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Cancel</span>
                    </button>
                    <button type="submit" class="btn-md px-6 py-2.5 min-h-[44px] text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition">
                        Update Claim
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function resetKmCalculator() {
            const from = document.querySelector('input[name="travel_from"]');
            const to = document.querySelector('input[name="travel_to"]');
            const km = document.getElementById('kmInput');
            const rate = document.getElementById('rateInput');
            const claim = document.getElementById('claimAmountInput');
            if (from) from.value = '';
            if (to) to.value = '';
            if (km) km.value = '';
            if (rate) rate.value = '6.0';
            if (claim) claim.value = '';
        }
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

        function toggleEditKmSection(category) {
            const kmSection = document.getElementById('editKmCalculatorSection');
            if (category === 'fuel_travel') {
                kmSection.classList.remove('hidden');
            } else {
                kmSection.classList.add('hidden');
            }
        }

        function calculateEditKmAmount() {
            const km = parseFloat(document.getElementById('editKmInput').value) || 0;
            const rate = parseFloat(document.getElementById('editRateInput').value) || 6.0;
            if (km > 0) {
                const total = (km * rate).toFixed(2);
                document.getElementById('editClaimAmountInput').value = total;
            }
        }

        function onAdminSiteSelected(sel) {
            const opt = sel.options[sel.selectedIndex];
            const card = document.getElementById('adminSiteDetailsCard');
            if (!opt || !opt.value) {
                card.classList.add('hidden');
                return;
            }
            const title = opt.getAttribute('data-title') || opt.text;
            const client = opt.getAttribute('data-client') || '';
            const address = opt.getAttribute('data-address') || '';
            const phone = opt.getAttribute('data-phone') || '';
            const status = opt.getAttribute('data-status') || 'Installation Site';

            document.getElementById('adminCardTitle').textContent = title;
            document.getElementById('adminCardBadge').textContent = status;
            document.getElementById('adminCardAddress').textContent = address || 'No address registered';
            document.getElementById('adminCardClient').textContent = client ? ('Client: ' + client) : '';
            document.getElementById('adminCardPhone').textContent = phone ? ('📞 ' + phone) : '';
            card.classList.remove('hidden');

            const toInput = document.getElementById('travelToInput');
            if (toInput && (!toInput.value || toInput.value.trim() === '')) {
                toInput.value = address || title;
            }
        }

        function setAdminSiteAsDestination() {
            const sel = document.getElementById('adminExpenseSiteSelect');
            const opt = sel.options[sel.selectedIndex];
            if (opt && opt.value) {
                const address = opt.getAttribute('data-address') || opt.getAttribute('data-title');
                const toInput = document.getElementById('travelToInput');
                if (toInput && address) {
                    toInput.value = address;
                    toInput.focus();
                }
            }
        }

        async function quickSaveAdminNewSite() {
            const name = document.getElementById('adminInlineNewSiteName').value.trim();
            const address = document.getElementById('adminInlineNewSiteAddress').value.trim();
            const client = document.getElementById('adminInlineNewClientName').value.trim();
            const phone = document.getElementById('adminInlineNewSitePhone').value.trim();

            if (!name || !address) {
                alert('Please enter both Site Name and Address to register an installation site.');
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                    || document.querySelector('input[name="_token"]')?.value;

                const response = await fetch('/sites', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        name: name,
                        address: address,
                        client_name: client,
                        contact_person: client || 'Site Manager',
                        phone: phone || '-'
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    const newId = data.site ? data.site.id : data.id;
                    const newName = data.site ? data.site.name : name;

                    const sel = document.getElementById('adminExpenseSiteSelect');
                    const newOpt = document.createElement('option');
                    newOpt.value = `site:${newId}`;
                    newOpt.textContent = `📍 ${newName} (${client || 'Site'})`;
                    newOpt.setAttribute('data-type', 'site');
                    newOpt.setAttribute('data-title', newName);
                    newOpt.setAttribute('data-client', client);
                    newOpt.setAttribute('data-address', address);
                    newOpt.setAttribute('data-phone', phone);
                    newOpt.setAttribute('data-status', 'Registered Installation Site');

                    sel.appendChild(newOpt);
                    sel.value = `site:${newId}`;
                    onAdminSiteSelected(sel);

                    alert(`Installation site "${newName}" created and selected!`);
                } else {
                    alert('Site details stored for this claim.');
                }
            } catch (err) {
                alert('Site details stored for this claim.');
            }
        }

        function openEditClaimModal(claim) {
            const modal = document.getElementById('editExpenseModal');
            const form = document.getElementById('editExpenseForm');
            
            form.action = `/finance/expenses/${claim.id}`;
            document.getElementById('editModalTitle').textContent = `Edit Claim #${claim.claim_no}`;
            document.getElementById('editClaimNo').textContent = `#${claim.claim_no}`;
            document.getElementById('editClaimantName').textContent = claim.user ? claim.user.name : (claim.claimant_name || 'Staff');

            document.getElementById('editExpenseCategory').value = claim.expense_category || 'fuel_travel';
            toggleEditKmSection(claim.expense_category || 'fuel_travel');

            // Format date YYYY-MM-DD
            if (claim.expense_date) {
                const d = new Date(claim.expense_date);
                if (!isNaN(d.getTime())) {
                    document.getElementById('editExpenseDate').value = d.toISOString().split('T')[0];
                } else if (typeof claim.expense_date === 'string') {
                    document.getElementById('editExpenseDate').value = claim.expense_date.substring(0, 10);
                }
            }

            document.getElementById('editTravelFrom').value = claim.travel_from || '';
            document.getElementById('editTravelTo').value = claim.travel_to || '';
            document.getElementById('editKmInput').value = claim.travel_distance_km || '';
            document.getElementById('editRateInput').value = claim.rate_per_km || '6.0';
            document.getElementById('editClaimAmountInput').value = claim.amount || '';
            
            const editStatus = document.getElementById('editStatus');
            if (editStatus) {
                editStatus.value = claim.status || 'pending';
            }

            document.getElementById('editDescription').value = claim.description || '';
            
            const editAdminNotes = document.getElementById('editAdminNotes');
            if (editAdminNotes) {
                editAdminNotes.value = claim.admin_notes || '';
            }

            const editTarget = document.getElementById('editLinkedTarget');
            if (editTarget) {
                if (claim.project_id) {
                    editTarget.value = `project:${claim.project_id}`;
                } else if (claim.site_id) {
                    editTarget.value = `site:${claim.site_id}`;
                } else if (claim.installation_job_id) {
                    editTarget.value = `job:${claim.installation_job_id}`;
                } else {
                    editTarget.value = '';
                }
            }

            const editTicket = document.getElementById('editServiceTicketId');
            if (editTicket) {
                editTicket.value = claim.service_ticket_id || '';
            }

            const receiptDiv = document.getElementById('currentReceiptDiv');
            const receiptLink = document.getElementById('editReceiptLink');
            if (claim.receipt_path) {
                receiptDiv.classList.remove('hidden');
                receiptLink.href = `/storage/${claim.receipt_path}`;
            } else {
                receiptDiv.classList.add('hidden');
            }

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

        // Auto-focus target claim if navigating from Alerts or Notifications
        document.addEventListener('DOMContentLoaded', function () {
            const targetClaimId = @js(request('claim_id'));
            const targetClaimNo = @js(request('claim_no'));
            if (targetClaimId) {
                const target = document.getElementById('pending-claim-' + targetClaimId) || document.getElementById('claim-row-' + targetClaimId);
                if (target) {
                    setTimeout(() => {
                        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 150);
                }
            } else if (targetClaimNo) {
                const target = document.querySelector('[data-claim-no="' + targetClaimNo + '"]');
                if (target) {
                    setTimeout(() => {
                        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 150);
                }
            }
        });
    </script>
</x-app-layout>
