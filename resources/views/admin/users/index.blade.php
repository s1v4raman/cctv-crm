<x-app-layout>
    @php
        $roles = ['admin' => 'Admins', 'staff' => 'Employees', 'technician' => 'Technicians', 'worker' => 'Workers', 'customer' => 'Customers'];
        $roleNames = ['admin' => 'Admin', 'staff' => 'Employee', 'technician' => 'Technician', 'worker' => 'Worker', 'customer' => 'Customer'];
        $views = collect(['all' => 'All Users'] + $roles)->map(fn ($label, $role) => [
            'label' => $label,
            'url' => route('admin.users.index', array_filter(['role' => $role === 'all' ? null : $role])),
            'active' => $roleFilter === $role,
        ])->values()->all();
        $filterCount = request()->filled('search') ? 1 : 0;
    @endphp

    <x-list-page key="users" :paginator="$users" :views="$views"
        :sorts="['name' => 'Name, A to Z', 'newest' => 'Newest first', 'role' => 'Role']"
        :create-url="route('admin.users.create')" create-label="New User"
        filter-title="Filter Users by" :filter-action="route('admin.users.index')"
        :keep="['role' => $roleFilter === 'all' ? null : $roleFilter, 'sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('admin.users.index', array_filter(['role' => $roleFilter === 'all' ? null : $roleFilter]))"
        :columns="['email' => 'Email', 'role' => 'Role', 'registered' => 'Registered']"
        :empty-text="$roleFilter === 'worker' ? 'No worker accounts yet.' : 'No users yet.'" filtered-text="No users match these filters.">

        <x-slot name="filters">
            <div class="ld-sub">Name or email</div>
            <input type="text" name="search" value="{{ request('search') }}" class="ld-input" aria-label="Name or email">
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 240px">Name</th>
            <th x-show="cols.email" style="min-width: 240px">Email</th>
            <th x-show="cols.role" style="min-width: 120px">Role</th>
            <th x-show="cols.registered" style="min-width: 120px">Registered</th>
        </x-slot>

        @foreach($users as $user)
            <tr>
                <x-list-pick :id="$user->id" :label="$user->name" />
                <td>
                    <a href="{{ route('admin.users.show', $user) }}" class="ld-name plain-link">{{ $user->name }}</a>
                    @if($user->id === auth()->id())<span class="text-xs" style="color: var(--ld-muted)"> (you)</span>@endif
                    @if($user->isWorker() && $user->worker)
                        <div class="text-xs mt-0.5" style="color: var(--ld-muted)">
                            {{ \App\Support\Inr::money($user->worker->daily_rate) }} a day @if($user->worker->phone) · {{ $user->worker->phone }}@endif
                            · <a href="{{ route('workers.show', $user->worker) }}" class="plain-link underline font-medium" style="color: var(--crm-accent, #2563eb)">worker profile</a>
                        </div>
                    @endif
                </td>
                <td x-show="cols.email">{{ $user->email }}</td>
                <td x-show="cols.role">{{ $roleNames[$user->role] ?? ucfirst($user->role) }}</td>
                <td x-show="cols.registered" class="whitespace-nowrap">{{ $user->created_at?->format('d/m/Y') ?? '—' }}</td>
                <td>
                    <div class="ld-row-actions">
                        <a href="{{ route('admin.users.edit', $user) }}" class="plain-link" title="Edit" aria-label="Edit {{ $user->name }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.2 5.2l3.6 3.6M4 20l4.5-1 10.3-10.3a2.5 2.5 0 00-3.5-3.5L5 15.5 4 20z"/></svg>
                        </a>
                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Delete this account ({{ $user->name }})? This cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-cancel" title="Delete" aria-label="Delete {{ $user->name }}">
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
