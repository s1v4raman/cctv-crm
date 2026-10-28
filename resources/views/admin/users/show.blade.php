<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.index') }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Back to Users">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-medium">
                        <a href="{{ route('admin.users.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">User Management</a>
                        <span>/</span>
                        <span class="text-slate-800 dark:text-slate-200">Profile Overview</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight font-heading mt-0.5">
                        {{ $user->name }}
                    </h1>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Account
                </a>
                @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to permanently delete this account ({{ addslashes($user->name) }})?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl border border-rose-200 dark:border-rose-900/60 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 bg-slate-50 dark:bg-[#060913] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- 1. Hero Profile Card --}}
            <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200 dark:border-slate-800/80 p-6 sm:p-8 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="flex items-start sm:items-center gap-5">
                        {{-- Circular Avatar --}}
                        <div class="w-16 h-16 sm:w-20 sm:20 rounded-full flex items-center justify-center font-bold text-2xl sm:text-3xl text-white shadow-md ring-4 ring-slate-100 dark:ring-slate-800 crm-user-avatar shrink-0"
                             style="background-color: var(--crm-accent, #2563eb);">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="space-y-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white font-heading truncate">
                                    {{ $user->name }}
                                </h2>
                                {{-- Role badge --}}
                                @php
                                    $roleBadgeClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200';
                                    if ($user->isAdmin()) $roleBadgeClass = 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 border-purple-200 dark:border-purple-800';
                                    elseif ($user->isStaff()) $roleBadgeClass = 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 border-blue-200 dark:border-blue-800';
                                    elseif ($user->isTechnician()) $roleBadgeClass = 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border-amber-200 dark:border-amber-800';
                                    elseif ($user->isWorker()) $roleBadgeClass = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
                                    elseif ($user->isCustomer()) $roleBadgeClass = 'bg-teal-100 text-teal-700 dark:bg-teal-950 dark:text-teal-300 border-teal-200 dark:border-teal-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $roleBadgeClass }}">
                                    {{ $user->role === 'staff' ? 'Employee' : ($user->role === 'worker' ? 'Field Worker' : ucfirst($user->role)) }}
                                </span>

                                @if($user->id === auth()->id())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-600 dark:bg-sky-950 dark:text-sky-400 border border-sky-200 dark:border-sky-800">
                                        You (Current User)
                                    </span>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $user->email }}</span>
                                </span>
                                <span>•</span>
                                <span>Member since {{ $user->created_at?->format('M d, Y') ?? 'N/A' }}</span>
                                <span>•</span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Active Account
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Customer Lead Quick Link (if applicable) --}}
                    @if($user->isCustomer() && $user->lead_id)
                        <div class="shrink-0 flex items-center gap-2">
                            <a href="{{ route('leads.show', $user->lead_id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                View Linked CRM Lead
                            </a>
                        </div>
                    @endif

                    {{-- Worker Hub Quick Link (if applicable) --}}
                    @if($user->isWorker() && $user->worker)
                        <div class="shrink-0 flex items-center gap-2">
                            <a href="{{ route('workers.show', $user->worker) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Open Worker Hub & Ledger
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. Role-Aware KPI Stat Badges --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @if($user->isTechnician())
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Open Tickets</span>
                            <span class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">🎫</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['open_tickets'] }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Tickets assigned & pending</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Resolved Tickets</span>
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['resolved_tickets'] }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Closed support issues</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Field Jobs</span>
                            <span class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">⚙️</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['jobs_count'] }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Installations & maintenance</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Workloads</span>
                            <span class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">📊</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['tickets_count'] + $stats['jobs_count'] }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">All service touchpoints</div>
                    </div>
                @elseif($user->isWorker())
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Daily Wage Rate</span>
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">₹</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">₹{{ number_format($stats['daily_rate'] ?? 0, 2) }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Approved per shift</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Shifts Worked</span>
                            <span class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">📅</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['shifts_count'] ?? 0 }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Project attendances logged</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Wages Paid</span>
                            <span class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">💰</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">₹{{ number_format($stats['total_paid'] ?? 0, 2) }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Disbursed wage payments</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Outstanding Balance</span>
                            <span class="w-7 h-7 rounded-lg {{ ($stats['balance'] ?? 0) > 0 ? 'bg-amber-100 text-amber-600 dark:bg-amber-950 dark:text-amber-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }} flex items-center justify-center font-bold text-xs">⏳</span>
                        </div>
                        <div class="text-2xl font-bold {{ ($stats['balance'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' }}">₹{{ number_format($stats['balance'] ?? 0, 2) }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Net liability due</div>
                    </div>
                @elseif($user->isCustomer())
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Quotations</span>
                            <span class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">📄</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['quotes_count'] }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Proposals & estimates</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Invoices</span>
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">💳</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['invoices_count'] }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Billing & payment records</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Service Tickets</span>
                            <span class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">🛠️</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['tickets_count'] }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Customer support inquiries</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Portal Access</span>
                            <span class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">🌐</span>
                        </div>
                        <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Enabled</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Customer Client Portal</div>
                    </div>
                @else
                    {{-- Admin / Employee --}}
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Role Authority</span>
                            <span class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">👑</span>
                        </div>
                        <div class="text-xl font-bold text-slate-900 dark:text-white capitalize">{{ $user->role }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">System access tier</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">System Tickets</span>
                            <span class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">🎫</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['tickets_count'] }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">All tickets in system</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Field Jobs</span>
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">⚙️</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['jobs_count'] }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Active installations</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Quotations</span>
                            <span class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">📋</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['quotes_count'] }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">CRM proposals logged</div>
                    </div>
                @endif
            </div>

            {{-- 3. Detail Columns --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left Column: Account Credentials & Specs --}}
                <div class="space-y-6">
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200 dark:border-slate-800/80 p-6 shadow-xs">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Account Details &amp; Credentials
                        </h3>

                        <dl class="space-y-3.5 text-xs">
                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
                                <dt class="text-slate-500 dark:text-slate-400">System User ID</dt>
                                <dd class="font-mono font-bold text-slate-800 dark:text-slate-200">#{{ $user->id }}</dd>
                            </div>
                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
                                <dt class="text-slate-500 dark:text-slate-400">Display Name</dt>
                                <dd class="font-semibold text-slate-900 dark:text-white">{{ $user->name }}</dd>
                            </div>
                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
                                <dt class="text-slate-500 dark:text-slate-400">Email Address</dt>
                                <dd class="font-semibold text-blue-600 dark:text-blue-400 truncate max-w-[180px]">{{ $user->email }}</dd>
                            </div>
                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
                                <dt class="text-slate-500 dark:text-slate-400">Account Role</dt>
                                <dd class="font-bold text-slate-800 dark:text-slate-200 capitalize">{{ $user->role }}</dd>
                            </div>
                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
                                <dt class="text-slate-500 dark:text-slate-400">Email Verified</dt>
                                <dd class="font-semibold text-emerald-600 dark:text-emerald-400">
                                    {{ $user->email_verified_at ? 'Verified (' . $user->email_verified_at->format('M d, Y') . ')' : 'Auto-Verified' }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
                                <dt class="text-slate-500 dark:text-slate-400">Theme Mode</dt>
                                <dd class="font-semibold text-slate-700 dark:text-slate-300 capitalize">{{ $user->theme_mode ?? 'Auto' }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Account Created</dt>
                                <dd class="text-slate-600 dark:text-slate-400">{{ $user->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Role Permissions Box --}}
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200 dark:border-slate-800/80 p-6 shadow-xs">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Role Privileges &amp; Permissions
                        </h3>
                        <ul class="space-y-2 text-xs">
                            @if($user->isAdmin())
                                <li class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Full Administrative Control (Read, Write, Delete)
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Manage User Accounts &amp; System Roles
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Product Catalog, Inventory Adjustments &amp; POs
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Financial Invoices, Payments, AMC &amp; Reports
                                </li>
                            @elseif($user->isStaff())
                                <li class="flex items-center gap-2 text-blue-700 dark:text-blue-400 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    Standard Staff / Employee Operations
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    Manage Customer Leads, Quotes &amp; Service Tickets
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    View Hardware Inventory &amp; Stock Levels
                                </li>
                                <li class="flex items-center gap-2 text-slate-400 dark:text-slate-500 line-through">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                    User Management &amp; System Settings (Admin Only)
                                </li>
                            @elseif($user->isTechnician())
                                <li class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Technician Field Workstation Access
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    View &amp; Update Assigned Service Tickets
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Field Installation Jobs &amp; AMC Scheduled Visits
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Daily Attendance Check-In / Clock-Out
                                </li>
                            @else
                                <li class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Customer Client Portal Access
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    View &amp; Approve Quotations
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    View Invoices &amp; Online Payment Gateway
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Submit Service &amp; Support Tickets
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                {{-- Right Column (2 spans): Associated CRM Records & Activity --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Customer Lead Info Card if Customer --}}
                    @if($user->isCustomer() && $user->lead)
                        <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200 dark:border-slate-800/80 p-6 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Linked CRM Customer Profile
                                </h3>
                                <a href="{{ route('leads.show', $user->lead->id) }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                    Open Full Lead →
                                </a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-slate-400 block mb-0.5">Customer / Company</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $user->lead->customer_name }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-slate-400 block mb-0.5">Phone Number</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $user->lead->phone ?? 'Not provided' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-slate-400 block mb-0.5">Address</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $user->lead->address ?? 'No address registered' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-slate-400 block mb-0.5">Lead Status</span>
                                    <span class="font-bold capitalize text-blue-600 dark:text-blue-400">{{ $user->lead->status ?? 'Active' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Activity / Workload Section --}}
                    @if($user->isTechnician())
                        {{-- Technician Recent Tickets --}}
                        <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200 dark:border-slate-800/80 p-6 shadow-xs">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading mb-4 flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                    Assigned Service Tickets
                                </span>
                                <span class="text-xs font-semibold text-slate-400">{{ $recentTickets->count() }} recent</span>
                            </h3>

                            @if($recentTickets->isEmpty())
                                <p class="text-xs text-slate-400 py-4 text-center">No service tickets assigned to this technician yet.</p>
                            @else
                                <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                    @foreach($recentTickets as $ticket)
                                        <div class="py-3 flex items-center justify-between gap-3">
                                            <div>
                                                <a href="{{ route('service-tickets.show', $ticket) }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                                    {{ $ticket->ticket_number ?? ('#ST-' . $ticket->id) }}: {{ Str::limit($ticket->issue_description ?? 'Service Issue', 40) }}
                                                </a>
                                                <p class="text-slate-400 text-[11px] mt-0.5">{{ $ticket->created_at?->diffForHumans() }}</p>
                                            </div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold uppercase
                                                {{ $ticket->status === 'resolved' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' }}">
                                                {{ ucfirst($ticket->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    @elseif($user->isCustomer())
                        {{-- Customer Recent Quotes & Invoices --}}
                        <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200 dark:border-slate-800/80 p-6 shadow-xs">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading mb-4 flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Recent Quotations &amp; Orders
                                </span>
                                <span class="text-xs font-semibold text-slate-400">{{ $recentQuotes->count() }} recent</span>
                            </h3>

                            @if($recentQuotes->isEmpty())
                                <p class="text-xs text-slate-400 py-4 text-center">No quotations created for this customer yet.</p>
                            @else
                                <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                    @foreach($recentQuotes as $quote)
                                        <div class="py-3 flex items-center justify-between gap-3">
                                            <div>
                                                <a href="{{ route('quotations.show', $quote) }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                                    {{ $quote->quotation_number ?? ('#QT-' . $quote->id) }}
                                                </a>
                                                <p class="text-slate-400 text-[11px] mt-0.5">Amount: ₹{{ number_format($quote->grand_total ?? $quote->total_amount ?? 0, 2) }} • {{ $quote->created_at?->diffForHumans() }}</p>
                                            </div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold uppercase bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                                {{ ucfirst($quote->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Admin / Employee Overview --}}
                        <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200 dark:border-slate-800/80 p-6 shadow-xs">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Account Summary &amp; Status
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-4">
                                This user has <strong class="text-slate-900 dark:text-white font-semibold">{{ ucfirst($user->role) }}</strong> status with full privileges to collaborate within the Precision IT Systems platform.
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary text-xs">
                                    Edit
                                </a>
                                <a href="{{ route('admin.users.create') }}" class="btn-secondary text-xs">
                                    New User
                                </a>
                            </div>
                        </div>
                    @endif

                </div>

            </div>
        </div>
    </div>
</x-app-layout>
