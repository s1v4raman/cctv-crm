<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Workers</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('workers.attendance.index') }}" class="btn-secondary">
                    Attendance Maintenance
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('wages.index') }}" class="btn-secondary">
                    Sunday Payday Console
                </a>
                @endif
                @if(auth()->user()->isOffice())
                <a href="{{ route('workers.create') }}" class="btn-primary">
                    Add Worker
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="max-w-[1200px] mx-auto space-y-6">
        @if (session('status'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded text-xs">
                {{ session('status') }}
            </div>
        @endif

        {{-- Sign-up requests from the worker login page --}}
        @if (auth()->user()->isAdmin() && $pendingWorkers->isNotEmpty())
            <div class="crm-section p-0" id="signup-requests">
                <div class="px-4 py-3 border-b border-[var(--card-border)]">
                    <h3 class="text-sm font-bold text-[var(--text-main)]">Sign-up requests ({{ $pendingWorkers->count() }})</h3>
                    <p class="text-xs text-[var(--text-muted)] mt-0.5">These people signed up on the worker login page. Approve them to let them sign in and see their attendance and work.</p>
                </div>
                <table class="crm-table crm-responsive">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Mobile</th>
                            <th scope="col">Requested</th>
                            <th scope="col" class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingWorkers as $request)
                            <tr>
                                <td class="font-semibold">{{ $request->name }}</td>
                                <td>{{ $request->phone }}</td>
                                <td>{{ $request->created_at->format('d M Y, h:i A') }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('workers.approve', $request) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-primary text-xs">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('workers.reject', $request) }}" class="inline"
                                          data-confirm="Reject the sign-up request from {{ $request->name }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-secondary text-xs text-rose-700 dark:text-rose-300">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Filter Section (Single Filter Row) --}}
        <div class="crm-section p-3">
            <form data-auto-submit method="GET" action="{{ route('workers.index') }}" class="flex items-center gap-2 flex-wrap">
                <input aria-label="Search worker name or phone" type="text" name="search" value="{{ request('search') }}" placeholder="Search worker name or phone…" 
                       style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                       class="crm-input flex-1 min-w-[220px]">

                <select aria-label="Status" name="status" 
                        style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                        class="crm-select min-w-[140px]">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>

                <button type="submit" 
                        style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                        class="crm-btn-primary px-4 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition">
                    Search
                </button>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('workers.index') }}" 
                       style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                       class="crm-btn-secondary px-3 inline-flex items-center text-xs">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Table Section (5.3) --}}
        <div class="crm-section p-0 overflow-x-auto">
            <table class="crm-table crm-responsive">
                <thead>
                    <tr>
                        <th class="col-name">Worker Name</th>
                        <th class="nowrap">Phone Number</th>
                        <th class="nowrap">Daily Rate</th>
                        <th>Skills</th>
                        <th>Current Balance</th>
                        <th class="nowrap">Status</th>
                        <th class="nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workers as $worker)
                    @php
                        $balance = $worker->getRunningBalance();
                    @endphp
                    <tr>
                        <td class="col-name">
                            <div class="flex items-center gap-2.5">
                                @if($worker->photo_url)
                                    <img src="{{ $worker->photo_url }}" alt="{{ $worker->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-300 shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-300 dark:border-slate-600">
                                        {{ strtoupper(substr($worker->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('workers.show', $worker) }}" class="action-link font-bold">
                                        {{ $worker->name }}
                                    </a>
                                    <div class="text-xs text-slate-500">{{ $worker->attendances_count }} days logged</div>
                                </div>
                            </div>
                        </td>
                        <td class="nowrap">
                            @if($worker->phone)
                                <a href="tel:{{ $worker->phone }}" class="action-link">{{ $worker->phone }}</a>
                            @else
                                Not specified
                            @endif
                        </td>
                        <td class="nowrap font-bold text-slate-900 dark:text-white">
                            &#8377;{{ number_format($worker->daily_rate, 2) }}
                        </td>
                        <td>
                            @if($worker->skills && is_array($worker->skills))
                                {{ implode(', ', array_map('ucfirst', $worker->skills)) }}
                            @else
                                General Assistant
                            @endif
                        </td>
                        <td class="font-bold {{ $balance > 0 ? 'text-red-600' : 'text-slate-900 dark:text-white' }}">
                            &#8377;{{ number_format($balance, 2) }}
                        </td>
                        <td class="nowrap">
                            @if($worker->is_active)
                                <span class="status-won">Active</span>
                            @else
                                <span class="status-lost">Inactive</span>
                            @endif
                        </td>
                        <td class="nowrap">
                            <a href="{{ route('workers.show', $worker) }}" class="action-link">View</a>
                            @if(auth()->user()->isOffice())
                            <a href="{{ route('workers.edit', $worker) }}" class="action-link">Edit</a>
                            @endif
                            @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('workers.destroy', $worker) }}" class="inline" data-confirm="Delete worker {{ $worker->name }}? This cannot be undone.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="danger-link p-0 bg-transparent border-0 cursor-pointer text-xs">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-6 text-slate-500 text-sm">
                            <x-empty-message empty="No workers yet" filtered="No workers match these filters." :filters="['search', 'status']" />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            {{-- Total count shown below the table --}}
            <div class="px-4 py-2.5 border-t border-[#ddd] dark:border-slate-800 bg-[#fbfbfb] dark:bg-slate-900/60 flex items-center justify-between text-xs text-slate-500">
                <span class="font-medium">Total: {{ $workers->total() }}</span>
                @if($workers->hasPages())
                    <div class="text-xs">
                        {{ $workers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
