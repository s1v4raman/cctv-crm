<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Workers</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('wages.index') }}" class="px-3 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 text-sm font-medium rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition" style="border-radius: 4px;">
                    Sunday Payday Console
                </a>
                <a href="{{ route('workers.create') }}" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition" style="border-radius: 4px;">
                    + Add Worker
                </a>
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
            .stat-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        .stat-card {
            background: #ffffff;
            border-radius: 4px;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
        }
        html.dark .stat-card {
            background: #0f172a;
            border-color: #1e293b;
        }
        .stat-label { font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 0.25rem; }
        html.dark .stat-label { color: #94a3b8; }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: #0f172a; line-height: 1; }
        html.dark .stat-value { color: #ffffff; }

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

        .filter-bar {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; flex-wrap: wrap;
            background: #ffffff;
        }
        html.dark .filter-bar {
            background: #0b1120;
            border-color: #1e293b;
        }
        .filter-input {
            height: 38px;
            padding: 0 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 14px;
            background: #ffffff;
            color: #1e293b;
        }
        html.dark .filter-input {
            background: #060913;
            border-color: #334155;
            color: #f1f5f9;
        }
    </style>

    <div class="py-6 bg-slate-50 dark:bg-[#060913] min-h-screen">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-5 p-4 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-sm font-medium" style="border-radius: 4px;">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-label">Active Workforce</div>
                    <div class="stat-value">{{ $activeWorkersCount }} <span class="text-sm font-normal text-slate-400">/ {{ $totalWorkersCount }}</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Unpaid Wage Liability</div>
                    <div class="stat-value text-rose-600 dark:text-rose-400">₹{{ number_format($totalOutstandingLiability, 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Payday Settlement</div>
                    <div class="stat-value text-slate-800 dark:text-slate-200 text-xl pt-1">Sunday Cycle</div>
                </div>
            </div>

            {{-- Table & Filters Card --}}
            <div class="pg-card">
                <form method="GET" action="{{ route('workers.index') }}" class="filter-bar">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search worker name, phone..." class="filter-input flex-1 min-w-[200px]">
                    
                    <select name="status" onchange="this.form.submit()" class="filter-input">
                        <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Workers</option>
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Workers</option>
                        <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>

                    <button type="submit" class="px-4 text-white text-sm font-semibold rounded bg-blue-600 hover:bg-blue-700 transition" style="height: 38px; border-radius: 4px;">
                        Search
                    </button>
                    @if($search || $statusFilter !== 'active')
                        <a href="{{ route('workers.index') }}" class="px-3 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center" style="height: 38px; border-radius: 4px;">
                            Reset
                        </a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Worker Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Phone Number</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Daily Rate</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Skills</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Current Balance</th>
                                <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Status</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                            @forelse($workers as $worker)
                                @php
                                    $balance = $worker->getRunningBalance();
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a href="{{ route('workers.show', $worker) }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 hover:underline">
                                            {{ $worker->name }}
                                        </a>
                                        <div class="text-xs text-slate-500">{{ $worker->attendances_count }} days logged</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($worker->phone)
                                            <a href="tel:{{ $worker->phone }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $worker->phone }}</a>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-900 dark:text-slate-200">
                                        ₹{{ number_format($worker->daily_rate, 2) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($worker->skills && is_array($worker->skills))
                                            <span class="text-slate-600 dark:text-slate-400">{{ implode(', ', array_map('ucfirst', $worker->skills)) }}</span>
                                        @else
                                            <span class="text-slate-400 italic">General Assistant</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        @if($balance > 0)
                                            <span class="font-bold text-rose-600 dark:text-rose-400">
                                                ₹{{ number_format($balance, 2) }}
                                            </span>
                                        @else
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                                ₹0.00
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="font-bold {{ $worker->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500' }}">
                                            {{ $worker->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right font-medium text-sm">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <a href="{{ route('workers.show', $worker) }}" class="text-blue-600 dark:text-blue-400 hover:underline">View</a>
                                            <span class="text-slate-300 dark:text-slate-600">|</span>
                                            <a href="{{ route('workers.edit', $worker) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                            @if(auth()->user()->isAdmin())
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <form method="POST" action="{{ route('workers.destroy', $worker) }}" class="inline" onsubmit="return confirm('Delete worker {{ addslashes($worker->name) }}? This cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline cursor-pointer bg-transparent border-0 p-0 font-medium">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                        No workers found matching your filter criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($workers->hasPages())
                <div class="mt-4">
                    {{ $workers->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
