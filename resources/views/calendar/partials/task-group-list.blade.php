@php
    $groupMeta = [
        'Overdue' => [
            'badge'  => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border-rose-300 dark:border-rose-800',
            'accent' => 'border-l-4 border-l-rose-500',
            'icon'   => '<svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> ',
        ],
        'Today' => [
            'badge'  => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border-blue-300 dark:border-blue-800',
            'accent' => 'border-l-4 border-l-blue-500',
            'icon'   => '',
        ],
        'Tomorrow' => [
            'badge'  => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border-purple-300 dark:border-purple-800',
            'accent' => 'border-l-4 border-l-purple-500',
            'icon'   => '',
        ],
        'This week' => [
            'badge'  => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 border-sky-300 dark:border-sky-800',
            'accent' => 'border-l-4 border-l-sky-500',
            'icon'   => '',
        ],
        'Later' => [
            'badge'  => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700',
            'accent' => 'border-l-4 border-l-slate-400',
            'icon'   => '⏳',
        ],
    ];
@endphp

<div class="space-y-6">
    @foreach(['Overdue', 'Today', 'Tomorrow', 'This week', 'Later'] as $groupName)
        @php
            $groupTasks = $groupList[$groupName] ?? collect();
            $meta = $groupMeta[$groupName];
        @endphp

        <div class="bg-white dark:bg-[#0f172a] rounded-[4px] border border-[var(--card-border)] dark:border-slate-700 overflow-hidden shadow-xs">
            
            {{-- Group Header --}}
            <div class="px-5 py-3.5 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    {{-- Icons are the fixed markup in $groupMeta above, never user input --}}
                    <span class="text-base">{!! $meta['icon'] !!}</span>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $groupName }}</h3>
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-[4px] border {{ $meta['badge'] }}">
                        {{ $groupTasks->count() }} {{ Str::plural('Task', $groupTasks->count()) }}
                    </span>
                </div>
            </div>

            {{-- Group Tasks List --}}
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($groupTasks as $task)
                    <div class="p-4 sm:p-5 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition {{ $meta['accent'] }}">
                        
                        {{-- Rejection Notice (if task was rejected) --}}
                        @if(!empty($task['rejection_reason']))
                            <div class="mb-3.5 p-3 rounded-[4px] bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800/80 text-rose-800 dark:text-rose-200 text-xs">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-2">
                                        <span class="text-base"><svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                                        <div>
                                            <div class="font-bold">
                                                Rejected by {{ $task['rejected_user']['name'] ?? 'Assignee' }}
                                                @if(!empty($task['rejected_at']))
                                                    <span class="font-normal text-rose-600 dark:text-rose-400">on {{ $task['rejected_at'] }}</span>
                                                @endif
                                            </div>
                                            <p class="mt-0.5 text-[11px] leading-relaxed italic">
                                                &ldquo;{{ $task['rejection_reason'] }}&rdquo;
                                            </p>
                                            <p class="mt-1 text-[11px] font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">
                                                Routed to Administrator for reassignment
                                            </p>
                                        </div>
                                    </div>

                                    @if($canManageTasks)
                                        <button type="button" 
                                                @click="openReassignModal({{ json_encode($task) }})" 
                                                class="px-3 py-1.5 rounded-[4px] bg-rose-600 text-white text-[11px] font-bold hover:bg-rose-700 transition shrink-0 cursor-pointer shadow-xs">
                                            Reassign Task
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3.5">
                            
                            {{-- Left: Task Details --}}
                            <div class="space-y-1.5 flex-1 min-w-0">
                                
                                {{-- Badges row --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-[4px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $task['phase_name'] ?: 'General' }}
                                    </span>

                                    {{-- Priority Badge --}}
                                    <span class="text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-[4px]
                                        {{ $task['priority'] === 'urgent' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : 
                                           ($task['priority'] === 'high' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 
                                           ($task['priority'] === 'medium' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300')) }}">
                                        {{ ucfirst($task['priority']) }}
                                    </span>

                                    {{-- Status Badge --}}
                                    <span class="text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-[4px]
                                        {{ $task['status'] === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' :
                                           ($task['status'] === 'in_progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' :
                                           ($task['status'] === 'rejected' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300')) }}">
                                        {{ str_replace('_', ' ', ucfirst($task['status'])) }}
                                    </span>

                                    {{-- Shift badge --}}
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-[4px] bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/60 inline-flex items-center gap-1">
                                        <span><svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z"/></svg></span>
                                        <span>{{ $task['shift'] }}</span>
                                    </span>

                                    {{-- Accepted indicator --}}
                                    @if(!empty($task['accepted_at']))
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-[4px] bg-emerald-50 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 inline-flex items-center gap-1">
                                            <span>✓ Accepted</span>
                                            <span class="font-normal">({{ $task['accepted_at'] }})</span>
                                        </span>
                                    @endif
                                </div>

                                {{-- Title (Work) --}}
                                <h4 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                                    {{ $task['title'] }}
                                </h4>

                                {{-- Description / Work Scope --}}
                                @if(!empty($task['description']))
                                    <p class="text-xs text-slate-600 dark:text-slate-300 line-clamp-2 leading-relaxed">
                                        {{ $task['description'] }}
                                    </p>
                                @endif

                                {{-- Meta row --}}
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-500 dark:text-slate-400 pt-1">
                                    
                                    {{-- Project Name & Client (STRICTLY HIDDEN from Workers) --}}
                                    @if(empty($task['is_worker_view']) && !empty($task['project_name']))
                                        <div class="inline-flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            <span>{{ $task['project_name'] }}</span>
                                            @if(!empty($task['client']))
                                                <span class="text-slate-400">({{ $task['client'] }})</span>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Date --}}
                                    <div class="inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                        <span>Due: {{ $task['due_date'] ?? ($task['start_date'] ?? 'No date') }}</span>
                                    </div>

                                    {{-- Location --}}
                                    @if(!empty($task['location']))
                                        <div class="inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span class="truncate max-w-[220px]">{{ $task['location'] }}</span>
                                        </div>
                                    @endif

                                    {{-- Hours --}}
                                    @if(!empty($task['estimated_hours']))
                                        <div class="inline-flex items-center gap-1 font-semibold text-slate-700 dark:text-slate-300">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            <span>{{ $task['estimated_hours'] }} hrs</span>
                                        </div>
                                    @endif

                                    {{-- Assignee (Non-workers only) --}}
                                    @if(empty($task['is_worker_view']) && !empty($task['assigned_user']))
                                        <div class="inline-flex items-center gap-1.5 font-medium">
                                            <div class="w-5 h-5 rounded-[4px] bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 font-bold flex items-center justify-center text-[11px]">
                                                {{ substr($task['assigned_user']['name'], 0, 1) }}
                                            </div>
                                            <span>{{ $task['assigned_user']['name'] }}</span>
                                        </div>
                                    @endif
                                </div>

                            </div>

                            {{-- Right Actions: Accept, Reject, Assign/Reassign --}}
                            <div class="flex items-center gap-2 shrink-0 pt-2 lg:pt-0">
                                
                                {{-- Accept & Reject Buttons for Assignee (Technician, Employee, Worker) --}}
                                @if((int)$task['assigned_to'] === (int)auth()->id())
                                    @if(empty($task['accepted_at']) && empty($task['rejected_at']) && $task['status'] !== 'completed')
                                        {{-- Accept Button --}}
                                        <form method="POST" action="{{ route('calendar.tasks.accept', $task['id']) }}">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-3.5 py-1.5 rounded-[4px] bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                <span>Accept</span>
                                            </button>
                                        </form>

                                        {{-- Reject Button (Opens Modal requiring reason) --}}
                                        <button type="button" 
                                                @click="openRejectModal({{ json_encode($task) }})"
                                                class="px-3 py-1.5 rounded-[4px] border border-rose-300 dark:border-rose-800/80 text-rose-700 dark:text-rose-300 bg-rose-50/60 dark:bg-rose-950/40 hover:bg-rose-100 text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Reject</span>
                                        </button>
                                    @endif
                                @endif

                                {{-- Admin-only Assign / Reassign Button --}}
                                @if($canManageTasks)
                                    <button type="button" 
                                            @click="openReassignModal({{ json_encode($task) }})"
                                            class="px-3 py-1.5 rounded-[4px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition flex items-center gap-1 cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>{{ empty($task['assigned_to']) ? 'Assign' : 'Reassign' }}</span>
                                    </button>
                                @endif

                            </div>

                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-xs font-medium">
                        No tasks in {{ $groupName }}.
                    </div>
                @endforelse
            </div>

        </div>
    @endforeach
</div>
