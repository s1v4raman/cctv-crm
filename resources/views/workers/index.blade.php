<x-app-layout>
    @php
        $views = [
            ['label' => 'Active Workers (' . $activeWorkersCount . ')', 'url' => route('workers.index'), 'active' => $statusFilter === 'active'],
            ['label' => 'Inactive Workers', 'url' => route('workers.index', ['status' => 'inactive']), 'active' => $statusFilter === 'inactive'],
            ['label' => 'All Workers (' . $totalWorkersCount . ')', 'url' => route('workers.index', ['status' => 'all']), 'active' => $statusFilter === 'all'],
        ];
        $filterCount = $search !== '' ? 1 : 0;
    @endphp

    <x-list-page key="workers" :paginator="$workers" :views="$views"
        :sorts="['name' => 'Name, A to Z', 'rate_desc' => 'Daily rate, high to low', 'days_desc' => 'Most days logged', 'newest' => 'Newest first']"
        :create-url="auth()->user()->isOffice() ? route('workers.create') : null" create-label="Add Worker"
        filter-title="Filter Workers by" :filter-action="route('workers.index')"
        :keep="['status' => $statusFilter === 'active' ? null : $statusFilter, 'sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('workers.index', array_filter(['status' => $statusFilter === 'active' ? null : $statusFilter]))"
        :columns="['phone' => 'Phone', 'rate' => 'Daily Rate', 'skills' => 'Skills', 'balance' => 'Balance', 'status' => 'Status']"
        empty-text="No workers yet." filtered-text="No workers match these filters.">

        @if (auth()->user()->isAdmin() && $pendingWorkers->isNotEmpty())
            <x-slot name="above">
                {{-- Sign-up requests from the worker login page --}}
                <div id="signup-requests" class="my-3 rounded border" style="border-color: var(--ld-border)">
                    <div class="px-4 py-2.5 border-b" style="border-color: var(--ld-line)">
                        <h3 class="text-sm font-semibold">Sign-up requests ({{ $pendingWorkers->count() }})</h3>
                        <p class="text-xs mt-0.5" style="color: var(--ld-muted)">These people signed up on the worker login page. Approve them to let them sign in and see their attendance and work.</p>
                    </div>
                    @foreach ($pendingWorkers as $request)
                        <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-2.5 border-b last:border-b-0 text-sm" style="border-color: var(--ld-line)">
                            <div>
                                <span class="font-medium">{{ $request->name }}</span>
                                <span class="ml-2" style="color: var(--ld-muted)">{{ $request->phone }} · {{ $request->created_at->format('d/m/Y, h:i A') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('workers.approve', $request) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-primary">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('workers.reject', $request) }}" data-confirm="Reject the sign-up request from {{ $request->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary">Reject</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-slot>
        @endif

        <x-slot name="actions">
            <a href="{{ route('workers.attendance.index') }}" class="ld-outline plain-link">Attendance</a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('wages.index') }}" class="ld-outline plain-link">Weekly wages</a>
            @endif
        </x-slot>

        <x-slot name="filters">
            <div class="ld-sub">Name, phone or notes</div>
            <input type="text" name="search" value="{{ $search }}" class="ld-input" aria-label="Name, phone or notes">
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 220px">Worker</th>
            <th x-show="cols.phone" style="min-width: 140px">Phone</th>
            <th x-show="cols.rate" style="min-width: 110px; text-align: right">Daily Rate</th>
            <th x-show="cols.skills" style="min-width: 180px">Skills</th>
            <th x-show="cols.balance" style="min-width: 120px; text-align: right">Balance</th>
            <th x-show="cols.status" style="min-width: 90px">Status</th>
        </x-slot>

        @foreach($workers as $worker)
            @php $balance = $worker->getRunningBalance(); @endphp
            <tr>
                <x-list-pick :id="$worker->id" :label="$worker->name" />
                <td>
                    <a href="{{ route('workers.show', $worker) }}" class="ld-name plain-link">{{ $worker->name }}</a>
                    <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $worker->attendances_count }} {{ Str::plural('day', $worker->attendances_count) }} logged</div>
                </td>
                <td x-show="cols.phone" class="whitespace-nowrap">{{ $worker->phone ?: '—' }}</td>
                <td x-show="cols.rate" class="whitespace-nowrap" style="text-align: right">{{ \App\Support\Inr::money($worker->daily_rate) }}</td>
                <td x-show="cols.skills">{{ $worker->skills && is_array($worker->skills) ? implode(', ', array_map('ucfirst', $worker->skills)) : 'General assistant' }}</td>
                <td x-show="cols.balance" class="whitespace-nowrap" style="text-align: right">
                    <span class="{{ $balance > 0 ? 'text-amber-700 dark:text-amber-400 font-medium' : '' }}">{{ \App\Support\Inr::money($balance) }}</span>
                </td>
                <td x-show="cols.status">{!! $worker->is_active ? 'Active' : '<span style="color: var(--ld-muted)">Inactive</span>' !!}</td>
                <td>
                    <div class="ld-row-actions">
                        @if(auth()->user()->isOffice())
                            <a href="{{ route('workers.edit', $worker) }}" class="plain-link" title="Edit" aria-label="Edit {{ $worker->name }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.2 5.2l3.6 3.6M4 20l4.5-1 10.3-10.3a2.5 2.5 0 00-3.5-3.5L5 15.5 4 20z"/></svg>
                            </a>
                        @endif
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('workers.destroy', $worker) }}" data-confirm="Delete worker {{ $worker->name }}? This cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-cancel" title="Delete" aria-label="Delete {{ $worker->name }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-list-page>
</x-app-layout>
