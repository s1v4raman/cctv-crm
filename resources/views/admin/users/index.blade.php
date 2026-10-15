<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">User Accounts Management</h2>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn-primary">
                Add User
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
            border-color: var(--text-main);
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
            border-color: var(--text-main);
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
            color: var(--text-main);
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
            color: var(--text-muted);
            border-color: var(--text-main);
        }
        .p-table tbody tr { border-bottom: 1px solid #f1f5f9; }
        html.dark .p-table tbody tr { border-color: var(--text-main); }
        .p-table tbody tr:hover { background: #f8fafc; }
        html.dark .p-table tbody tr:hover { background: rgba(30,41,59,0.4); }
        .p-table tbody td { padding: 0.75rem 1rem; vertical-align: middle; font-size: 14px; }

        .cell-name { font-weight: 600; color: var(--text-main); }
        html.dark .cell-name { color: var(--text-main); }
        .cell-email { font-size: 13px; color: #2563eb; }
        html.dark .cell-email { color: #38bdf8; }
        .cell-date { font-size: 13px; color: #64748b; }
        html.dark .cell-date { color: var(--text-muted); }
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
                    <a href="{{ route('admin.users.index', ['role' => 'all']) }}" class="crm-filter-tab filter-tab {{ $roleFilter === 'all' ? 'active' : '' }}">All Accounts</a>
                    <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="crm-filter-tab filter-tab {{ $roleFilter === 'admin' ? 'active' : '' }}">Admins</a>
                    <a href="{{ route('admin.users.index', ['role' => 'staff']) }}" class="crm-filter-tab filter-tab {{ $roleFilter === 'staff' ? 'active' : '' }}">Employees</a>
                    <a href="{{ route('admin.users.index', ['role' => 'technician']) }}" class="crm-filter-tab filter-tab {{ $roleFilter === 'technician' ? 'active' : '' }}">Technicians</a>
                </div>

                    <div class="overflow-x-auto">
                        <table class="p-table">
                            <thead>
                                <tr>
                                    <th class="col-name" style="min-width: 200px;">Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th class="col-date whitespace-nowrap">Registered Date</th>
                                    <th style="text-align:right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td class="col-name" style="min-width: 200px;">
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
                                                $roleColor = '#2563eb';
                                                if ($user->isAdmin()) $roleColor = '#7e22ce';
                                                elseif ($user->isStaff()) $roleColor = '#2563eb';
                                                elseif ($user->isTechnician()) $roleColor = '#d97706';
                                                else $roleColor = '#16a34a';
                                            @endphp
                                            <span class="status-text" style="color: {{ $roleColor }}; font-weight: bold;">
                                                {{ $user->role === 'staff' ? 'Employee' : ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td class="col-date whitespace-nowrap">
                                            <span class="cell-date">{{ $user->created_at?->format('d M Y') ?? 'N/A' }}</span>
                                        </td>
                                        <td style="text-align:right" class="whitespace-nowrap text-sm table-actions">
                                            <a href="{{ route('admin.users.show', $user) }}" class="action-link">View</a>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="action-link">Edit</a>
                                            @if($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                                      onsubmit="return confirm('Delete this account ({{ addslashes($user->name) }})? This action cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="danger-link">Delete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-6 text-slate-500 text-sm">
                                            No users yet
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($users->hasPages())
                        <div class="p-4 border-t border-slate-200 dark:border-slate-800">{{ $users->links() }}</div>
                    @endif
            </div>
        </div>
    </div>
</x-app-layout>
