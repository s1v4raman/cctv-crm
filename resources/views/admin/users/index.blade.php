<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_10px_#f59e0b]"></span>
                    User Accounts Management
                </h2>
                <p class="mt-1 text-sm text-slate-400">Manage administrator, staff employee, field technician, and client portal access accounts</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn-amber">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Account
            </a>
        </div>
    </x-slot>

    <style>
        /* .pg-wrap uses global app.css */
        /* .pg-inner uses global app.css */

        .pg-card {
            background:#0f172a;
            border-radius:1rem;
            border:1px solid rgba(255,255,255,0.07);
            box-shadow:0 10px 25px -5px rgba(0,0,0,0.5);
            overflow:hidden;
        }

        .alert-success {
            background-color: rgba(16,185,129,0.12);
            border: 1px solid rgba(16,185,129,0.3);
            color: #34d399;
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .alert-error {
            background-color: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.3);
            color: #f87171;
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Filter bar */
        .filter-tabs {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            background: #0b1120;
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: 0.45rem 0.95rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.15s;
        }
        .filter-tab:hover {
            background: rgba(255,255,255,0.05);
            color: #ffffff;
        }
        .filter-tab.active {
            background: linear-gradient(135deg, var(--crm-accent, #2563eb), var(--crm-accent-hover, #1d4ed8));
            color: #ffffff !important;
            font-weight: 700;
            box-shadow: 0 2px 8px var(--crm-accent-shadow, rgba(37, 99, 235, 0.3));
        }

        /* Table */
        .p-table { width:100%; border-collapse:collapse; }
        .p-table thead { background:#0b1120; }
        .p-table thead th {
            padding:.85rem 1.25rem;
            font-size:.72rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.05em; color:#94a3b8; text-align:left; white-space:nowrap;
            border-bottom:1px solid rgba(255,255,255,0.06);
        }
        .p-table tbody tr { border-bottom:1px solid rgba(255,255,255,0.04); transition:background .12s; }
        .p-table tbody tr:last-child { border-bottom:none; }
        .p-table tbody tr:hover { background:rgba(30,41,59,0.5); }
        .p-table tbody td { padding:1rem 1.25rem; vertical-align:middle; font-size:.85rem; }

        .cell-name { font-size:.9rem; font-weight:700; color:#f8fafc; }
        .cell-email  { font-size:.78rem; color:#38bdf8; font-family:ui-monospace,monospace; }
        .cell-date { font-size:.8rem; color:#94a3b8; }
        
        .badge-role { display:inline-flex; padding:.2rem .65rem; border-radius:999px; font-size:.7rem; font-weight:700; }
        .badge-admin { background:rgba(168,85,247,0.15); color:#c084fc; border:1px solid rgba(168,85,247,0.3); }
        .badge-staff { background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); }
        .badge-technician { background:rgba(245,158,11,0.15); color:#fbbf24; border:1px solid rgba(245,158,11,0.3); }
        .badge-customer { background:rgba(16,185,129,0.15); color:#34d399; border:1px solid rgba(16,185,129,0.3); }

        /* Action buttons */
        .btn-edit {
            display:inline-flex; align-items:center; gap:.3rem;
            padding:.35rem .75rem; border-radius:.5rem;
            font-size:.75rem; font-weight:600;
            background:rgba(56,189,248,0.1); color:#38bdf8;
            border:1px solid rgba(56,189,248,0.3); text-decoration:none;
            transition:all .15s;
        }
        .btn-edit:hover { background:rgba(56,189,248,0.2); color:#7dd3fc; }
        .btn-del {
            display:inline-flex; align-items:center; gap:.3rem;
            padding:.35rem .75rem; border-radius:.5rem;
            font-size:.75rem; font-weight:600;
            background:rgba(239,68,68,0.1); color:#f87171;
            border:1px solid rgba(239,68,68,0.3); cursor:pointer;
            transition:all .15s; font-family:inherit;
        }
        .btn-del:hover { background:rgba(239,68,68,0.2); color:#fca5a5; }
        
        .btn-amber {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.15rem;
            border-radius: 0.65rem;
            font-size: 0.82rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--crm-accent, #2563eb), var(--crm-accent-hover, #1d4ed8));
            color: #ffffff !important;
            border: none;
            box-shadow: 0 4px 14px var(--crm-accent-shadow, rgba(37, 99, 235, 0.25));
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-amber * { color: #ffffff !important; }
        .btn-amber:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px var(--crm-accent-shadow, rgba(37, 99, 235, 0.4));
            color: #ffffff !important;
        }
        
        .empty-state { text-align:center; padding:3rem 1rem; color:#64748b; font-size:.85rem; }
        .pg-links { padding:1rem 1.25rem; border-top:1px solid rgba(255,255,255,0.06); }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">
            
            @if (session('status'))
                <div class="alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-error">
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
                    <div class="empty-state">
                        <p class="font-semibold text-slate-300 mb-1">No Accounts Found</p>
                        <p class="text-sm text-slate-500">There are no accounts matching the selected filter criteria.</p>
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
                                            <div class="cell-name">{{ $user->name }}</div>
                                        </td>
                                        <td>
                                            <div class="cell-email">{{ $user->email }}</div>
                                        </td>
                                        <td>
                                            <span class="badge-role 
                                                {{ $user->isAdmin() ? 'badge-admin' : ($user->isStaff() ? 'badge-staff' : ($user->isTechnician() ? 'badge-technician' : 'badge-customer')) }}">
                                                {{ $user->role === 'staff' ? 'Employee (Staff)' : ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="cell-date">{{ $user->created_at?->format('M d, Y') ?? 'N/A' }}</span>
                                        </td>
                                        <td style="text-align:right">
                                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:.5rem">
                                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-edit">
                                                    <svg style="width:.75rem;height:.75rem" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                                    Edit
                                                </a>
                                                
                                                @if($user->id !== auth()->id())
                                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                          onsubmit="return confirm('Are you sure you want to delete this account ({{ addslashes($user->name) }})? This action cannot be undone.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-del">
                                                            <svg style="width:.75rem;height:.75rem" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                                            Delete
                                                        </button>
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
                        <div class="pg-links">{{ $users->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
