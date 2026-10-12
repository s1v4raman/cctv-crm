<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#222222]">User Accounts Management</h1>
            </div>
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2 rounded bg-[#2563eb] hover:bg-[#1d4ed8] text-white text-sm font-normal transition">
                + Add User
            </a>
        </div>
    </x-slot>

    <style>
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

        .filter-tabs {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            flex-wrap: wrap;
        }
        html.dark .filter-tabs {
            background: #0b1120;
            border-color: #1e293b;
        }
        .filter-tab {
            padding: 0.4rem 0.85rem;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            transition: all 0.15s;
            border: 1px solid transparent;
        }
        .filter-tab:hover {
            color: #1e293b;
            background: #e2e8f0;
        }
        html.dark .filter-tab:hover {
            color: #ffffff;
            background: rgba(255,255,255,0.05);
        }
        .filter-tab.active {
            background: #2563eb;
            color: #ffffff !important;
            font-weight: 600;
        }

        .p-table { width: 100%; border-collapse: collapse; }
        .p-table thead { background: #f8fafc; }
        html.dark .p-table thead { background: #0b1120; }
        .p-table thead th {
            padding: 0.75rem 1rem;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            text-align: left;
            white-space: nowrap;
            border-bottom: 1px solid #e2e8f0;
        }
        html.dark .p-table thead th {
            color: #94a3b8;
            border-color: #1e293b;
        }
        .p-table tbody tr { border-bottom: 1px solid #f1f5f9; }
        html.dark .p-table tbody tr { border-color: #1e293b; }
        .p-table tbody tr:hover { background: #f8fafc; }
        html.dark .p-table tbody tr:hover { background: rgba(30,41,59,0.4); }
        .p-table tbody td { padding: 0.75rem 1rem; vertical-align: middle; font-size: 14px; }

        .cell-name { font-weight: 600; color: #0f172a; }
        html.dark .cell-name { color: #f8fafc; }
        .cell-email { font-size: 13px; color: #2563eb; }
        html.dark .cell-email { color: #38bdf8; }
        .cell-date { font-size: 13px; color: #64748b; }
        html.dark .cell-date { color: #94a3b8; }
    </style>

    <div class="py-6 bg-slate-50 dark:bg-[#060913] min-h-screen">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="mb-5 p-4 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-sm font-medium" style="border-radius: 4px;">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 p-4 rounded bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-500/30 text-rose-800 dark:text-rose-300 text-sm font-medium" style="border-radius: 4px;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="pg-card">
                <!-- Filters tabs -->
                <div class="filter-tabs">
                    <a href="{{ route('admin.users.index', ['role' => 'all']) }}" class="filter-tab {{ $roleFilter === 'all' ? 'active' : '' }}">All Accounts</a>
                    <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="filter-tab {{ $roleFilter === 'admin' ? 'active' : '' }}">Admins</a>
                    <a href="{{ route('admin.users.index', ['role' => 'staff']) }}" class="filter-tab {{ $roleFilter === 'staff' ? 'active' : '' }}">Employees</a>
                    <a href="{{ route('admin.users.index', ['role' => 'technician']) }}" class="filter-tab {{ $roleFilter === 'technician' ? 'active' : '' }}">Technicians</a>
                    <a href="{{ route('admin.users.index', ['role' => 'customer']) }}" class="filter-tab {{ $roleFilter === 'customer' ? 'active' : '' }}">Customers</a>
                </div>

                @if($users->isEmpty())
                    <div class="p-8 text-center text-slate-500 text-sm">
                        No accounts found matching the selected filter criteria.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="p-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Registered Date</th>
                                    <th style="text-align:right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-full text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs ring-2 ring-white dark:ring-slate-800 crm-user-avatar" style="background-color: var(--crm-accent, #2563eb);">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <a href="{{ route('admin.users.show', $user) }}" class="cell-name hover:text-blue-600 dark:hover:text-blue-400 font-semibold transition">
                                                        {{ $user->name }}
                                                    </a>
                                                    @if($user->id === auth()->id())
                                                        <span class="ml-1 text-[10px] font-bold text-blue-500">(You)</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="cell-email">{{ $user->email }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $roleColor = 'text-slate-700 dark:text-slate-300';
                                                if ($user->isAdmin()) $roleColor = 'text-purple-600 dark:text-purple-400';
                                                elseif ($user->isStaff()) $roleColor = 'text-blue-600 dark:text-blue-400';
                                                elseif ($user->isTechnician()) $roleColor = 'text-amber-600 dark:text-amber-400';
                                                else $roleColor = 'text-emerald-600 dark:text-emerald-400';
                                            @endphp
                                            <span class="font-bold {{ $roleColor }}">
                                                {{ $user->role === 'staff' ? 'Employee' : ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="cell-date">{{ $user->created_at?->format('d M Y') ?? 'N/A' }}</span>
                                        </td>
                                        <td style="text-align:right" class="whitespace-nowrap text-sm">
                                            <div class="inline-flex items-center gap-2 justify-end">
                                                <a href="{{ route('admin.users.show', $user) }}" class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium">
                                                    View
                                                </a>
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">Edit</a>
                                                @if($user->id !== auth()->id())
                                                    <span class="text-slate-300 dark:text-slate-600">|</span>
                                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                                          onsubmit="return confirm('Delete this account ({{ addslashes($user->name) }})? This action cannot be undone.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-rose-600 dark:text-rose-400 hover:underline cursor-pointer bg-transparent border-0 p-0 font-medium">Delete</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($users->hasPages())
                        <div class="p-4 border-t border-slate-200 dark:border-slate-800">{{ $users->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
