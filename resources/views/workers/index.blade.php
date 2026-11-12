<x-app-layout>
    @php
        $views = [
            ['label' => __('Active Workers (:count)', ['count' => $activeWorkersCount]), 'url' => route('workers.index'), 'active' => $statusFilter === 'active'],
            ['label' => __('Inactive Workers'), 'url' => route('workers.index', ['status' => 'inactive']), 'active' => $statusFilter === 'inactive'],
            ['label' => __('All Workers (:count)', ['count' => $totalWorkersCount]), 'url' => route('workers.index', ['status' => 'all']), 'active' => $statusFilter === 'all'],
        ];
        $filterCount = $search !== '' ? 1 : 0;
    @endphp

    <x-list-page key="workers" :paginator="$workers" :views="$views"
        :sorts="['name' => 'Name, A to Z', 'rate_desc' => 'Daily rate, high to low', 'days_desc' => 'Most days logged', 'newest' => 'Newest first']"
        :create-url="auth()->user()->isOffice() ? route('workers.create') : null" :create-label="__('Add Worker')"
        :filter-title="__('Filter Workers by')" :filter-action="route('workers.index')"
        :keep="['status' => $statusFilter === 'active' ? null : $statusFilter, 'sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('workers.index', array_filter(['status' => $statusFilter === 'active' ? null : $statusFilter]))"
        :columns="['phone' => __('Phone'), 'rate' => __('Daily Rate'), 'skills' => __('Skills'), 'balance' => __('Balance'), 'status' => __('Status')]"
        :empty-text="__('No workers yet.')" :filtered-text="__('No workers match these filters.')">

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
            <a href="{{ route('workers.attendance.index') }}" class="ld-outline plain-link whitespace-nowrap shrink-0">{{ __('Attendance') }}</a>
            <button type="button" onclick="openBulkAttendanceModal()" class="ld-outline cursor-pointer whitespace-nowrap shrink-0" style="font-weight: 600;">
                <svg class="w-4 h-4 mr-1.5 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                <span>{{ __('Bulk Attendance') }}</span>
            </button>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('wages.index') }}" class="ld-outline plain-link whitespace-nowrap shrink-0">{{ __('Weekly wages') }}</a>
            @endif
        </x-slot>

        <x-slot name="filters">
            <div class="ld-sub">{{ __('Name, phone or notes') }}</div>
            <input type="text" name="search" value="{{ $search }}" class="ld-input" aria-label="{{ __('Name, phone or notes') }}">
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 220px">{{ __('Worker') }}</th>
            <th x-show="cols.phone" style="min-width: 140px">{{ __('Phone') }}</th>
            <th x-show="cols.rate" style="min-width: 110px; text-align: right">{{ __('Daily Rate') }}</th>
            <th x-show="cols.skills" style="min-width: 180px">{{ __('Skills') }}</th>
            <th x-show="cols.balance" style="min-width: 120px; text-align: right">{{ __('Balance') }}</th>
            <th x-show="cols.status" style="min-width: 90px">{{ __('Status') }}</th>
        </x-slot>

        @foreach($workers as $worker)
            @php $balance = $worker->getRunningBalance(); @endphp
            <tr>
                <x-list-pick :id="$worker->id" :label="$worker->name" />
                <td>
                    <a href="{{ route('workers.show', $worker) }}" class="ld-name plain-link">{{ $worker->name }}</a>
                    <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $worker->attendances_count }} {{ __('days logged') }}</div>
                </td>
                <td x-show="cols.phone" class="whitespace-nowrap">{{ $worker->phone ?: '—' }}</td>
                <td x-show="cols.rate" class="whitespace-nowrap" style="text-align: right">{{ \App\Support\Inr::money($worker->daily_rate) }}</td>
                <td x-show="cols.skills">{{ $worker->skills && is_array($worker->skills) ? implode(', ', array_map('ucfirst', $worker->skills)) : __('General assistant') }}</td>
                <td x-show="cols.balance" class="whitespace-nowrap" style="text-align: right">
                    <span class="{{ $balance > 0 ? 'text-amber-700 dark:text-amber-400 font-medium' : '' }}">{{ \App\Support\Inr::money($balance) }}</span>
                </td>
                <td x-show="cols.status">{!! $worker->is_active ? __('Active') : '<span style="color: var(--ld-muted)">' . __('Inactive') . '</span>' !!}</td>
                <td>
                    <div class="ld-row-actions">
                        <button type="button" onclick="openAttendanceModal(null, '{{ $worker->id }}')" class="plain-link p-1 text-slate-400 hover:text-blue-600 transition cursor-pointer" title="{{ __('Punch Attendance') }}" aria-label="{{ __('Punch Attendance for :name', ['name' => $worker->name]) }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
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

    <x-mark-attendance-modal :workers="$activeWorkersList ?? $workers" :projects="$activeProjects ?? collect()" />
</x-app-layout>
