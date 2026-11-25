@props([
    'workers' => collect(),
    'projects' => collect(),
    'selectedDate' => null,
    'defaultProjectId' => null,
    'defaultWorkerId' => null,
    'todayAttendances' => null,
    'assignedProjectIds' => null,
])

@php
    $targetDate = $selectedDate ?? now()->toDateString();
    if ($todayAttendances === null) {
        $todayAttendances = \App\Models\ProjectWorkerAttendance::with(['worker', 'workDay.project', 'logger'])
            ->whereHas('workDay', function($q) use ($targetDate) {
                $q->whereDate('work_date', $targetDate);
            })->get()->keyBy('worker_id');
    }

    $alreadyLoggedMap = [];
    foreach ($todayAttendances as $wId => $att) {
        $alreadyLoggedMap[$wId] = [
            'worker_id' => $wId,
            'worker_name' => $att->worker?->name ?? 'Worker',
            'attendance_type' => $att->attendance_type === 'half' ? __('Half Day') : __('Full Day'),
            'logger_name' => $att->logger?->name ?? __('Technician'),
            'project_title' => $att->workDay?->project?->title ?? __('Assigned Site'),
            'check_in_at' => $att->check_in_at ? $att->check_in_at->format('h:i A') : '',
        ];
    }

    $availableWorkers = $workers->filter(fn($w) => !isset($alreadyLoggedMap[$w->id]));
    $loggedWorkers = $workers->filter(fn($w) => isset($alreadyLoggedMap[$w->id]));

    // Resolve assigned project IDs for the signed-in technician
    if ($assignedProjectIds === null && auth()->check()) {
        $uId = auth()->id();
        $assignedProjectIds = \App\Models\Project::where('lead_technician_id', $uId)
            ->orWhere('assigned_to', $uId)
            ->orWhere('created_by', $uId)
            ->orWhereHas('tasks', fn($tq) => $tq->where('assigned_to', $uId))
            ->pluck('id')
            ->all();
    } else {
        $assignedProjectIds = $assignedProjectIds ?? [];
    }

    // Ensure site is loaded on projects
    if ($projects->isNotEmpty() && !$projects->first()->relationLoaded('site')) {
        $projects->load('site');
    }

    // Split projects into assigned to this technician vs all other available sites
    $assignedProjectsList = $projects->filter(fn($p) => in_array($p->id, $assignedProjectIds))->values();
    $otherProjectsList = $projects->filter(fn($p) => !in_array($p->id, $assignedProjectIds))->values();

    // Default select technician's assigned site first if available
    if (!$defaultProjectId && $assignedProjectsList->isNotEmpty()) {
        $defaultProjectId = $assignedProjectsList->first()->id;
    }

    // Helper to format project title with site location
    $formatProjectSiteLabel = function($p, $isAssigned = false) {
        $locParts = [];
        if ($p->site?->name) {
            $locParts[] = $p->site->name;
        }
        $addr = $p->site?->address ?: $p->site_address;
        if ($addr) {
            $locParts[] = Str::limit($addr, 32);
        } elseif ($p->site?->city) {
            $locParts[] = $p->site->city;
        }

        $locStr = count($locParts) ? implode(', ', $locParts) : ($p->customer_name ?: __('Project Site'));
        $prefix = $isAssigned ? '⭐ [Assigned Site] ' : '';
        return "{$prefix}{$p->title} — 📍 {$locStr}";
    };
@endphp

{{-- MARK WORKER ATTENDANCE & SITE WORK OUTPUT SPLIT MODAL --}}
<div id="markAttendanceModal" class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-3 sm:p-4 hidden overflow-y-auto backdrop-blur-xs">
    <div class="bg-white dark:bg-slate-900 rounded-xl max-w-xl w-full p-5 sm:p-6 shadow-2xl border border-slate-200 dark:border-slate-800 my-auto transition-all">
        
        {{-- Modal Top Bar --}}
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span id="modal-heading-icon" class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span id="modal-heading-title">{{ __('Mark Worker Attendance') }}</span>
                </h3>
                <p id="modal-heading-subtitle" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ __('Worker shift check-in and attendance punch only') }}
                </p>
            </div>
            <x-close-button onclick="closeAttendanceModal()" :label="__('Close')" />
        </div>

        {{-- TWO INDEPENDENT OPERATION METHODS (SPLIT TABS) --}}
        <div class="mt-3.5 mb-4 grid grid-cols-2 gap-2 p-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
            <button type="button" id="tab-btn-attendance" onclick="setAttendanceOperationMode('attendance')" 
                    class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-bold transition-all bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                <div class="text-left">
                    <span class="block leading-tight font-bold">{{ __('1. Worker Attendance Only') }}</span>
                    <span class="text-[10px] text-slate-400 font-normal hidden sm:block">{{ __('Punch shift check-in') }}</span>
                </div>
            </button>
            <button type="button" id="tab-btn-workoutput" onclick="setAttendanceOperationMode('workoutput')" 
                    class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                <div class="text-left">
                    <span class="block leading-tight font-bold">{{ __('2. Site Work Output Only') }}</span>
                    <span class="text-[10px] text-slate-400 font-normal hidden sm:block">{{ __('Cabling & cameras performance') }}</span>
                </div>
            </button>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- METHOD 1: ATTENDANCE ONLY FORM (PUNCH WORKER SHIFTS)       --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <form id="mark-attendance-form" method="POST" action="{{ route('workers.attendance.store') }}" enctype="multipart/form-data" onsubmit="return handleAttendanceSubmit(event)" class="space-y-3">
            @csrf

            <div id="modal-validation-error-att" class="hidden p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs"></div>

            {{-- Mode Switcher: Single Worker vs Bulk Workers --}}
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('Attendance Mode') }}:</span>
                <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold gap-1">
                    <button type="button" id="att-mode-btn-single" onclick="setAttendanceMode('single')" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 min-h-[34px] rounded-lg transition-all bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        <span>{{ __('Single Worker') }}</span>
                    </button>
                    <button type="button" id="att-mode-btn-bulk" onclick="setAttendanceMode('bulk')" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 min-h-[34px] rounded-lg transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                        <span>{{ __('Bulk Workers (Multiple)') }}</span>
                    </button>
                </div>
            </div>

            {{-- MODE 1: SINGLE WORKER SELECTOR --}}
            <div id="single-worker-container">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="modal-worker-select" class="crm-label text-xs">{{ __('Worker') }} <span class="required-marker">*</span></label>
                        <select name="worker_id" id="modal-worker-select" onchange="onSingleWorkerSelected(this.value)" class="crm-select text-xs w-full">
                            <option value="">{{ __('Select Worker...') }}</option>
                            <optgroup id="single-worker-available-group" label="🟢 {{ __('Available Workers to Punch') }} ({{ $availableWorkers->count() }})">
                                @foreach($availableWorkers as $w)
                                    <option value="{{ $w->id }}" data-photo="{{ $w->photo_url ?? '' }}" data-name="{{ $w->name }}" @selected(old('worker_id', $defaultWorkerId) == $w->id)>
                                        {{ $w->name }} ({{ $w->phone ?: __('Available') }})
                                    </option>
                                @endforeach
                            </optgroup>

                            <optgroup id="single-worker-locked-group" label="📝 {{ __('Already Logged Today (Click to Update)') }} ({{ $loggedWorkers->count() }})" {{ $loggedWorkers->isEmpty() ? 'class=hidden' : '' }}>
                                @foreach($loggedWorkers as $w)
                                    @php $info = $alreadyLoggedMap[$w->id] ?? null; @endphp
                                    <option value="{{ $w->id }}" data-already-marked="true" data-name="{{ $w->name }}" data-logger="{{ $info['logger_name'] ?? '' }}" data-project="{{ $info['project_title'] ?? '' }}" class="text-slate-700 dark:text-slate-300">
                                        📝 {{ $w->name }} &mdash; {{ __('Logged (:shift) by :tech on :site [Select to Update]', ['shift' => $info['attendance_type'] ?? 'Full Day', 'tech' => $info['logger_name'] ?? 'Technician', 'site' => Str::limit($info['project_title'] ?? '', 20)]) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>

                        {{-- Notice when an already logged worker is chosen --}}
                        <div id="single-worker-already-logged-alert" class="hidden mt-2 p-2.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            <span id="single-worker-already-logged-msg"></span>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="att-project-id" class="crm-label text-xs mb-0">{{ __('Project Site') }} <span class="required-marker">*</span></label>
                            <button type="button" onclick="openQuickAddSiteModal()" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center gap-1 cursor-pointer hover:underline">
                                <span>{{ __('Add New Site Location') }}</span>
                            </button>
                        </div>
                        <select id="att-project-id" name="project_id" required class="crm-select text-xs w-full">
                            <option value="">{{ __('Select Project Site...') }}</option>
                            @if($assignedProjectsList->isNotEmpty())
                                <optgroup label="⭐ {{ __('My Assigned Project Sites') }} ({{ $assignedProjectsList->count() }})">
                                    @foreach($assignedProjectsList as $p)
                                        <option value="{{ $p->id }}" @selected(old('project_id', $defaultProjectId) == $p->id)>
                                            {{ $formatProjectSiteLabel($p, true) }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            <optgroup label="🏢 {{ __('All Project Sites & Locations') }} ({{ $otherProjectsList->count() }})">
                                @foreach($otherProjectsList as $p)
                                    <option value="{{ $p->id }}" @selected(old('project_id', $defaultProjectId) == $p->id)>
                                        {{ $formatProjectSiteLabel($p, false) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                </div>
            </div>

            {{-- MODE 2: BULK MULTI-WORKER SELECTOR --}}
            <div id="bulk-worker-container" class="hidden space-y-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="att-project-id-bulk" class="crm-label text-xs mb-0">{{ __('Project Site') }} <span class="required-marker">*</span></label>
                        <button type="button" onclick="openQuickAddSiteModal()" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center gap-1 cursor-pointer hover:underline">
                            <span>{{ __('Add New Site Location') }}</span>
                        </button>
                    </div>
                    <select onchange="syncProjectSelect(this.value)" id="att-project-id-bulk" class="crm-select text-xs w-full">
                        <option value="">{{ __('Select Project Site for all selected workers...') }}</option>
                        @if($assignedProjectsList->isNotEmpty())
                            <optgroup label="⭐ {{ __('My Assigned Project Sites') }} ({{ $assignedProjectsList->count() }})">
                                @foreach($assignedProjectsList as $p)
                                    <option value="{{ $p->id }}" @selected(old('project_id', $defaultProjectId) == $p->id)>
                                        {{ $formatProjectSiteLabel($p, true) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        <optgroup label="🏢 {{ __('All Project Sites & Locations') }} ({{ $otherProjectsList->count() }})">
                            @foreach($otherProjectsList as $p)
                                <option value="{{ $p->id }}" @selected(old('project_id', $defaultProjectId) == $p->id)>
                                    {{ $formatProjectSiteLabel($p, false) }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/40 p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">{{ __('Click Workers to Punch') }}</span>
                            <span id="bulk-worker-count-badge" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                0 / {{ $workers->count() }} {{ __('selected') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <button type="button" onclick="toggleAllBulkWorkers(true)" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline cursor-pointer">
                                {{ __('Select All') }}
                            </button>
                            <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                            <button type="button" onclick="toggleAllBulkWorkers(false)" class="text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 cursor-pointer">
                                {{ __('Clear') }}
                            </button>
                        </div>
                    </div>

                    {{-- Already Punched Workers Notice --}}
                    <div id="bulk-already-marked-notice" class="{{ count($alreadyLoggedMap) > 0 ? '' : 'hidden' }} mb-2.5 p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs text-amber-800 dark:text-amber-300 flex items-center justify-between">
                        <span class="flex items-center gap-1.5 font-medium">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            <span id="bulk-already-marked-text">{{ __(':count workers already have attendance logged today. Select any worker to update their shift or site.', ['count' => count($alreadyLoggedMap)]) }}</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-200/80 dark:bg-amber-900 text-amber-900 dark:text-amber-200 shrink-0">
                            {{ __('Editable') }}
                        </span>
                    </div>

                    {{-- Realtime Search Filter --}}
                    <div class="relative mb-2.5 flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                        </div>
                        <input type="text" id="bulk-worker-search" oninput="filterBulkWorkers(this.value)" placeholder="{{ __('Search worker by name or phone...') }}" class="crm-input text-xs w-full py-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700" style="padding-left: 2.85rem !important;">
                    </div>

                    {{-- Worker Clickable Checkbox List --}}
                    <div id="bulk-worker-list" class="max-h-48 sm:max-h-56 overflow-y-auto rounded-lg border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-slate-800/80 p-1">
                        @foreach($workers as $w)
                            @php
                                $isMarked = isset($alreadyLoggedMap[$w->id]);
                                $markInfo = $isMarked ? $alreadyLoggedMap[$w->id] : null;
                            @endphp
                            <label class="flex items-center justify-between p-2 rounded-md {{ $isMarked ? 'bg-amber-50/40 dark:bg-amber-950/20 border border-amber-200/70 dark:border-amber-800/50 hover:bg-amber-50/80 cursor-pointer' : 'hover:bg-blue-50/70 dark:hover:bg-blue-950/40 cursor-pointer' }} transition select-none group bulk-worker-item" 
                                   data-worker-id="{{ $w->id }}"
                                   data-worker-name="{{ strtolower($w->name) }}" 
                                   data-worker-phone="{{ $w->phone }}"
                                   data-already-marked="{{ $isMarked ? '1' : '0' }}">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <input type="checkbox" name="worker_ids[]" value="{{ $w->id }}" 
                                           data-name="{{ $w->name }}" 
                                           data-rate="{{ (float) $w->daily_rate }}" 
                                           data-photo="{{ $w->photo_url ?? '' }}" 
                                           onchange="updateBulkCounter()" 
                                           class="bulk-worker-check w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 cursor-pointer">
                                    @if($w->photo_url)
                                        <img src="{{ $w->photo_url }}" alt="{{ $w->name }}" class="w-7 h-7 rounded-full object-cover shrink-0 border border-slate-200 dark:border-slate-700" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'64\' height=\'64\' viewBox=\'0 0 64 64\'><rect width=\'64\' height=\'64\' rx=\'32\' fill=\'%233b82f6\'/><text x=\'50%25\' y=\'54%25\' text-anchor=\'middle\' dominant-baseline=\'middle\' fill=\'%23ffffff\' font-family=\'sans-serif\' font-size=\'22\' font-weight=\'bold\'>{{ strtoupper(mb_substr($w->name, 0, 1)) }}</text></svg>';">
                                    @else
                                        <div class="w-7 h-7 rounded-full {{ $isMarked ? 'bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200' : 'bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300' }} font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ strtoupper(mb_substr($w->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ $w->name }}</span>
                                            @if($isMarked)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">✓ {{ __('Logged') }}</span>
                                            @endif
                                        </div>
                                        <span class="block text-[10px] text-slate-400 truncate">{{ $w->phone ?: __('No phone') }}</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-2">
                                    @if($isMarked)
                                        <div class="text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <span class="inline-block text-[10px] font-bold text-amber-700 dark:text-amber-300">
                                                    {{ $markInfo['attendance_type'] }}
                                                </span>
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                                    {{ __('Update') }}
                                                </span>
                                            </div>
                                            <span class="block text-[9px] text-slate-400 dark:text-slate-500" title="{{ $markInfo['project_title'] }}">
                                                {{ __('by :tech', ['tech' => Str::limit($markInfo['logger_name'], 12)]) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="inline-block text-[11px] font-semibold text-slate-500 dark:text-slate-400">{{ __('Ready') }}</span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Date & Shift Type (Full Day Only, No Half Day Option) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="modal-work-date" class="crm-label text-xs">{{ __('Work Date') }} <span class="required-marker">*</span></label>
                    <input type="date" name="work_date" id="modal-work-date" value="{{ $selectedDate ?? now()->toDateString() }}" required class="crm-input text-xs w-full">
                </div>

                <div>
                    <label class="crm-label text-xs">{{ __('Shift Type') }}</label>
                    <input type="hidden" name="attendance_type" id="att-attendance-type" value="full">
                    <div class="h-[38px] px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-xs font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ __('Full Day Attendance (1.0x)') }}</span>
                    </div>
                </div>
            </div>

            {{-- Method 1 Action Buttons (Direct 1-Click Save) --}}
            <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="closeAttendanceModal()" class="inline-flex items-center justify-center px-4 py-2 min-h-[38px] rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs sm:text-sm hover:bg-slate-50 transition cursor-pointer">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" id="btn-save-attendance" class="inline-flex items-center justify-center gap-1.5 px-5 py-2 min-h-[38px] rounded-xl bg-[#65a30d] hover:bg-[#4d7c0f] !text-white font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer" style="color: #ffffff !important;">
                    <span class="!text-white font-bold" style="color: #ffffff !important;">✓ {{ __('Save Worker Attendance') }}</span>
                </button>
            </div>
        </form>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- METHOD 2: WORK OUTPUT ONLY FORM (CABLING & CAMERAS LOG)   --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <form id="mark-work-output-form" method="POST" action="{{ route('workers.work-output.store') }}" onsubmit="return handleWorkOutputSubmit(event)" class="hidden space-y-3">
            @csrf

            <div id="modal-validation-error-output" class="hidden p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs"></div>

            {{-- Live Status Alert for Existing Work Output --}}
            <div id="output-existing-notice" class="hidden p-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs text-blue-800 dark:text-blue-300 flex items-center justify-between">
                <span class="flex items-center gap-1.5 font-medium">
                    <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                    <span id="output-existing-msg">{{ __('Existing work output loaded for this site & date. Updates will overwrite.') }}</span>
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-200/80 dark:bg-blue-900 text-blue-900 dark:text-blue-200 shrink-0">
                    {{ __('Loaded') }}
                </span>
            </div>

            {{-- Project Site & Work Date --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="output-project-id" class="crm-label text-xs mb-0">{{ __('Project Site') }} <span class="required-marker">*</span></label>
                        <button type="button" onclick="openQuickAddSiteModal()" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center gap-1 cursor-pointer hover:underline">
                            <span>{{ __('Add New Site Location') }}</span>
                        </button>
                    </div>
                    <select id="output-project-id" name="project_id" onchange="fetchCurrentWorkOutput()" required class="crm-select text-xs w-full">
                        <option value="">{{ __('Select Project Site...') }}</option>
                        @if($assignedProjectsList->isNotEmpty())
                            <optgroup label="⭐ {{ __('My Assigned Project Sites') }} ({{ $assignedProjectsList->count() }})">
                                @foreach($assignedProjectsList as $p)
                                    <option value="{{ $p->id }}" @selected(old('project_id', $defaultProjectId) == $p->id)>
                                        {{ $formatProjectSiteLabel($p, true) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        <optgroup label="🏢 {{ __('All Project Sites & Locations') }} ({{ $otherProjectsList->count() }})">
                            @foreach($otherProjectsList as $p)
                                <option value="{{ $p->id }}" @selected(old('project_id', $defaultProjectId) == $p->id)>
                                    {{ $formatProjectSiteLabel($p, false) }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label for="output-work-date" class="crm-label text-xs">{{ __('Work Date') }} <span class="required-marker">*</span></label>
                    <input type="date" name="work_date" id="output-work-date" onchange="fetchCurrentWorkOutput()" value="{{ $selectedDate ?? now()->toDateString() }}" required class="crm-input text-xs w-full">
                </div>
            </div>

            {{-- Overall Site Output Metrics: Cabling & Cameras --}}
            <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/40 space-y-3">
                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                    <span>{{ __("Daily Site Work Metrics (Cabling & CCTV Equipment)") }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="output-cabling-metres" class="crm-label text-xs font-bold">{{ __('Total Cable Used Today (m)') }}</label>
                        <input id="output-cabling-metres" type="number" step="0.1" min="0" name="cabling_metres" placeholder="{{ __('e.g. 75.5') }}" class="crm-input text-xs w-full">
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Total length of physical cable pulled today (metres)') }}</p>
                    </div>

                    <div>
                        <label for="output-cameras-installed" class="crm-label text-xs font-bold">{{ __('Total Cameras Installed Today (units)') }}</label>
                        <input id="output-cameras-installed" type="number" min="0" name="cameras_installed" placeholder="{{ __('e.g. 4') }}" class="crm-input text-xs w-full">
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Total CCTV camera units mounted & tested today') }}</p>
                    </div>
                </div>
            </div>

            {{-- Work Notes & Summary with Voice Mic --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="output-notes" class="crm-label text-xs mb-0">{{ __('Work Output Summary & Site Remarks') }}</label>
                    <x-voice-mic-button target="#output-notes" />
                </div>
                <x-tamil-input>
                    <textarea id="output-notes" name="notes" rows="3" placeholder="{{ __('Summary of work performed, cabling routes, testing done, site condition remarks...') }}" class="crm-input text-xs w-full" style="height: auto;"></textarea>
                </x-tamil-input>
            </div>

            {{-- Method 2 Action Buttons (Direct Save Work Output) --}}
            <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="closeAttendanceModal()" class="inline-flex items-center justify-center px-4 py-2 min-h-[38px] rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs sm:text-sm hover:bg-slate-50 transition cursor-pointer">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" id="btn-save-work-output" class="inline-flex items-center justify-center gap-1.5 px-5 py-2 min-h-[38px] rounded-xl bg-[#2563eb] hover:bg-[#1d4ed8] !text-white font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer" style="color: #ffffff !important;">
                    <span class="!text-white font-bold" style="color: #ffffff !important;">⚡ {{ __('Save Site Work Output') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- QUICK ADD NEW SITE LOCATION MODAL --}}
<div id="quickAddSiteModal" class="fixed inset-0 z-[70] bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 hidden overflow-y-auto">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-slate-200 dark:border-slate-800 my-auto transition-all">
        
        {{-- Header --}}
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        {{ __('Add New Site Location') }}
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        {{ __('Register a new site & immediately assign to your active projects') }}
                    </p>
                </div>
            </div>
            <x-close-button onclick="closeQuickAddSiteModal()" :label="__('Close')" />
        </div>

        {{-- Form --}}
        <form id="quick-add-site-form" onsubmit="return submitQuickAddSite(event)" class="mt-4 space-y-3">
            <div id="quick-site-error" class="hidden p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs"></div>

            {{-- Site Name --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="quick-site-name" class="crm-label text-xs mb-0">{{ __('Site Location Name') }} <span class="required-marker">*</span></label>
                    <x-voice-mic-button target="#quick-site-name" />
                </div>
                <input type="text" id="quick-site-name" name="name" required placeholder="{{ __('e.g. Apex Tech Park - 4th Floor CCTV') }}" class="crm-input text-xs w-full">
            </div>

            {{-- Installation Facility / Site Address (Big Size) --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="quick-site-address" class="crm-label text-xs mb-0">{{ __('Installation Facility / Site Address') }} <span class="required-marker">*</span></label>
                    <x-voice-mic-button target="#quick-site-address" />
                </div>
                <x-tamil-input>
                    <textarea id="quick-site-address" name="address" required rows="3" placeholder="{{ __('e.g. #23 Maharaja 1st street, Brothers Main Road, Vanagaram, Chennai') }}" class="crm-input text-xs w-full" style="height: auto;"></textarea>
                </x-tamil-input>
            </div>

            {{-- City & State --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="quick-site-city" class="crm-label text-xs mb-0">{{ __('City / Area') }}</label>
                        <x-voice-mic-button target="#quick-site-city" />
                    </div>
                    <input type="text" id="quick-site-city" name="city" placeholder="{{ __('e.g. Chennai') }}" class="crm-input text-xs w-full">
                </div>
                <div>
                    <label for="quick-site-state" class="crm-label text-xs mb-1">{{ __('State') }}</label>
                    <input type="text" id="quick-site-state" name="state" value="Tamil Nadu" placeholder="{{ __('e.g. Tamil Nadu') }}" class="crm-input text-xs w-full">
                </div>
            </div>

            {{-- Customer / Company & Contact Phone --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="quick-site-client" class="crm-label text-xs mb-0">{{ __('Customer / Company Name') }}</label>
                        <x-voice-mic-button target="#quick-site-client" />
                    </div>
                    <input type="text" id="quick-site-client" name="client_name" placeholder="{{ __('e.g. Rajesh Kumar / Apex Solutions') }}" class="crm-input text-xs w-full">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="quick-site-phone" class="crm-label text-xs mb-0">{{ __('Phone Number (WhatsApp)') }}</label>
                        <x-voice-mic-button target="#quick-site-phone" />
                    </div>
                    <input type="tel" id="quick-site-phone" name="contact_phone" placeholder="{{ __('e.g. 9876543210') }}" class="crm-input text-xs w-full">
                </div>
            </div>

            {{-- Project Type & Google Maps Link --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="quick-site-type" class="crm-label text-xs mb-1">{{ __('Project Type') }}</label>
                    <select id="quick-site-type" name="project_type" class="crm-select text-xs w-full">
                        <option value="hardware_cctv">{{ __('CCTV Surveillance & Security') }}</option>
                        <option value="hardware_attendance">{{ __('Terminal Camera & Attendance') }}</option>
                        <option value="networking">{{ __('Network Cabling & Wi-Fi') }}</option>
                        <option value="access_control">{{ __('Biometric Access Control') }}</option>
                        <option value="hybrid">{{ __('Hybrid (Hardware + Software)') }}</option>
                    </select>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="quick-site-maps" class="crm-label text-xs mb-0">{{ __('Google Maps Link (Optional)') }}</label>
                    </div>
                    <input type="url" id="quick-site-maps" name="google_maps_url" placeholder="{{ __('https://maps.google.com/?q=...') }}" class="crm-input text-xs w-full">
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="closeQuickAddSiteModal()" class="inline-flex items-center justify-center px-4 py-2 min-h-[38px] rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-50 transition cursor-pointer">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" id="btn-quick-site-save" class="inline-flex items-center justify-center gap-1.5 px-5 py-2 min-h-[38px] rounded-xl bg-[#2563eb] hover:bg-[#1d4ed8] !text-white font-bold text-xs shadow-xs transition cursor-pointer" style="color: #ffffff !important;">
                    <svg class="w-3.5 h-3.5 !text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span id="btn-quick-site-save-text" class="!text-white font-bold" style="color: #ffffff !important;">{{ __('Save Site Location') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const attendanceModalText = {{ Js::from([
        'pickWorker' => __('Please select a Worker before saving attendance.'),
        'pickAtLeastOneWorker' => __('Please select at least one Worker before saving attendance.'),
        'pickProject' => __('Please select a Project Site before proceeding.'),
        'halfDay' => __('Half Day (0.5x)'),
        'fullDay' => __('Full Day (1.0x)'),
        'savingAttendance' => __('Saving Attendance...'),
        'savingOutput' => __('Saving Work Output...'),
        'workersSelected' => __('workers selected'),
    ]) }};

    let currentAttendanceMode = 'single';
    let currentOperationMode = 'attendance';

    // Toggle between Method 1 (Attendance Only) and Method 2 (Work Output Only)
    function setAttendanceOperationMode(mode) {
        currentOperationMode = mode;
        const tabAtt = document.getElementById('tab-btn-attendance');
        const tabOut = document.getElementById('tab-btn-workoutput');
        const formAtt = document.getElementById('mark-attendance-form');
        const formOut = document.getElementById('mark-work-output-form');
        const headingTitle = document.getElementById('modal-heading-title');
        const headingSubtitle = document.getElementById('modal-heading-subtitle');
        const headingIcon = document.getElementById('modal-heading-icon');

        if (mode === 'workoutput') {
            tabOut.className = "flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-bold transition-all bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs cursor-pointer";
            tabAtt.className = "flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer";
            formAtt.classList.add('hidden');
            formOut.classList.remove('hidden');

            if (headingTitle) headingTitle.textContent = "{{ __('Log Site Work Output') }}";
            if (headingSubtitle) headingSubtitle.textContent = "{{ __('Daily physical cabling pulled & CCTV camera metrics') }}";
            if (headingIcon) headingIcon.className = "w-2.5 h-2.5 rounded-full bg-blue-500";

            // Sync project and date from attendance form into work output form
            const attProj = document.getElementById('att-project-id')?.value;
            const attDate = document.getElementById('modal-work-date')?.value;
            const outProj = document.getElementById('output-project-id');
            const outDate = document.getElementById('output-work-date');

            if (attProj && outProj && !outProj.value) {
                outProj.value = attProj;
            }
            if (attDate && outDate) {
                outDate.value = attDate;
            }

            fetchCurrentWorkOutput();
        } else {
            tabAtt.className = "flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-bold transition-all bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs cursor-pointer";
            tabOut.className = "flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer";
            formAtt.classList.remove('hidden');
            formOut.classList.add('hidden');

            if (headingTitle) headingTitle.textContent = "{{ __('Mark Worker Attendance') }}";
            if (headingSubtitle) headingSubtitle.textContent = "{{ __('Worker shift check-in and attendance punch only') }}";
            if (headingIcon) headingIcon.className = "w-2.5 h-2.5 rounded-full bg-emerald-500";
        }
    }

    // Fetch existing daily work output metrics for project and date
    async function fetchCurrentWorkOutput() {
        const projId = document.getElementById('output-project-id')?.value;
        const dateVal = document.getElementById('output-work-date')?.value;
        const noticeBox = document.getElementById('output-existing-notice');
        const noticeMsg = document.getElementById('output-existing-msg');
        const cablingInput = document.getElementById('output-cabling-metres');
        const camerasInput = document.getElementById('output-cameras-installed');
        const notesInput = document.getElementById('output-notes');

        if (!projId || !dateVal) return;

        try {
            const res = await fetch(`{{ route('workers.work-output.check') }}?project_id=${encodeURIComponent(projId)}&date=${encodeURIComponent(dateVal)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.exists) {
                    if (cablingInput) cablingInput.value = data.cabling_metres || '';
                    if (camerasInput) camerasInput.value = data.cameras_installed || '';
                    if (notesInput && !notesInput.value) notesInput.value = data.notes || '';
                    if (noticeBox && noticeMsg) {
                        noticeMsg.textContent = `Recorded: ${data.cabling_metres}m cabling, ${data.cameras_installed} cameras (by ${data.logger_name || 'Technician'}). Saving will update this record.`;
                        noticeBox.classList.remove('hidden');
                    }
                } else {
                    if (noticeBox) noticeBox.classList.add('hidden');
                }
            }
        } catch (err) {
            console.warn('Error fetching work output:', err);
        }
    }

    function setAttendanceMode(mode) {
        currentAttendanceMode = mode;
        const btnSingle = document.getElementById('att-mode-btn-single');
        const btnBulk = document.getElementById('att-mode-btn-bulk');
        const singleContainer = document.getElementById('single-worker-container');
        const bulkContainer = document.getElementById('bulk-worker-container');
        const workerSelect = document.getElementById('modal-worker-select');
        const projSelectMain = document.getElementById('att-project-id');
        const projSelectBulk = document.getElementById('att-project-id-bulk');

        if (mode === 'bulk') {
            btnBulk.className = "inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 min-h-[34px] rounded-lg transition-all bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs cursor-pointer";
            btnSingle.className = "inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 min-h-[34px] rounded-lg transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer";
            singleContainer.classList.add('hidden');
            bulkContainer.classList.remove('hidden');
            if (workerSelect) {
                workerSelect.required = false;
                workerSelect.value = '';
            }
            if (projSelectMain && projSelectBulk) {
                projSelectBulk.value = projSelectMain.value;
            }
            updateBulkCounter();
        } else {
            btnSingle.className = "inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 min-h-[34px] rounded-lg transition-all bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs cursor-pointer";
            btnBulk.className = "inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 min-h-[34px] rounded-lg transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer";
            singleContainer.classList.remove('hidden');
            bulkContainer.classList.add('hidden');
            if (workerSelect) {
                workerSelect.required = true;
            }
        }
    }

    function syncProjectSelect(val) {
        const projMain = document.getElementById('att-project-id');
        if (projMain) projMain.value = val;
    }

    // Already logged workers map passed from server
    window.alreadyLoggedWorkers = @js($alreadyLoggedMap);

    function onSingleWorkerSelected(val) {
        const alertBox = document.getElementById('single-worker-already-logged-alert');
        const alertMsg = document.getElementById('single-worker-already-logged-msg');
        if (!alertBox || !alertMsg) return;

        if (val && window.alreadyLoggedWorkers && window.alreadyLoggedWorkers[val]) {
            const info = window.alreadyLoggedWorkers[val];
            alertMsg.innerHTML = `📝 <strong>${info.worker_name}</strong> already has attendance recorded for this date by <strong>${info.logger_name}</strong> on <em>${info.project_title}</em> (${info.attendance_type}). Saving will <strong>update</strong> their shift or site.`;
            alertBox.classList.remove('hidden');
        } else {
            alertBox.classList.add('hidden');
        }
    }

    function toggleAllBulkWorkers(selectAll) {
        const items = document.querySelectorAll('.bulk-worker-item');
        items.forEach(item => {
            if (item.style.display !== 'none') {
                const cb = item.querySelector('.bulk-worker-check');
                if (cb && !cb.disabled) {
                    cb.checked = selectAll;
                }
            }
        });
        updateBulkCounter();
    }

    function filterBulkWorkers(query) {
        const q = (query || '').toLowerCase().trim();
        const items = document.querySelectorAll('.bulk-worker-item');
        items.forEach(item => {
            const name = item.getAttribute('data-worker-name') || '';
            const phone = item.getAttribute('data-worker-phone') || '';
            const match = !q || name.includes(q) || phone.includes(q);
            item.style.display = match ? 'flex' : 'none';
        });
    }

    function updateBulkCounter() {
        const checks = document.querySelectorAll('.bulk-worker-check:checked');
        const availableChecks = document.querySelectorAll('.bulk-worker-check:not(:disabled)');
        const badge = document.getElementById('bulk-worker-count-badge');

        if (badge) {
            badge.textContent = `${checks.length} / ${availableChecks.length} ${attendanceModalText.workersSelected}`;
        }
    }

    // Dynamic Date Change Lock Sync
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('modal-work-date');
        if (dateInput) {
            dateInput.addEventListener('change', function() {
                syncAttendanceDateLock(this.value);
            });
        }
    });

    async function syncAttendanceDateLock(dateVal) {
        if (!dateVal) return;
        try {
            const res = await fetch(`{{ route('workers.attendance.check-date') }}?date=${encodeURIComponent(dateVal)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (res.ok) {
                const data = await res.json();
                window.alreadyLoggedWorkers = data.attendances || {};
                applyDateAttendanceLock();
            }
        } catch (err) {
            console.warn('Error checking date attendance:', err);
        }
    }

    function applyDateAttendanceLock() {
        const map = window.alreadyLoggedWorkers || {};
        const count = Object.keys(map).length;

        // 1. Update single worker dropdown
        const workerSelect = document.getElementById('modal-worker-select');
        if (workerSelect) {
            Array.from(workerSelect.options).forEach(opt => {
                const wId = opt.value;
                if (!wId) return;
                const origName = opt.getAttribute('data-name') || opt.text;
                if (map[wId]) {
                    const info = map[wId];
                    opt.disabled = false;
                    opt.text = `📝 ${origName} — Logged (${info.attendance_type}) by ${info.logger_name} [Select to Update]`;
                } else {
                    opt.disabled = false;
                    opt.text = origName;
                }
            });
            onSingleWorkerSelected(workerSelect.value);
        }

        // 2. Update bulk worker checklist
        const items = document.querySelectorAll('.bulk-worker-item');
        items.forEach(item => {
            const wId = item.getAttribute('data-worker-id');
            const cb = item.querySelector('.bulk-worker-check');
            if (!cb) return;

            if (map[wId]) {
                const info = map[wId];
                cb.disabled = false;
                item.classList.add('bg-amber-50/40', 'dark:bg-amber-950/20', 'border', 'border-amber-200/70', 'dark:border-amber-800/50', 'hover:bg-amber-50/80', 'cursor-pointer');
                item.classList.remove('bg-slate-100/70', 'dark:bg-slate-800/50', 'opacity-60', 'cursor-not-allowed', 'hover:bg-blue-50/70', 'dark:hover:bg-blue-950/40');

                const rightCol = item.querySelector('.text-right.shrink-0');
                if (rightCol) {
                    rightCol.innerHTML = `
                        <div class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <span class="inline-block text-[10px] font-bold text-amber-700 dark:text-amber-300">${info.attendance_type}</span>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">Update</span>
                            </div>
                            <span class="block text-[9px] text-slate-400 dark:text-slate-500">by ${info.logger_name}</span>
                        </div>
                    `;
                }
            } else {
                cb.disabled = false;
                item.classList.remove('bg-amber-50/40', 'dark:bg-amber-950/20', 'border', 'border-amber-200/70', 'dark:border-amber-800/50', 'hover:bg-amber-50/80');
                item.classList.add('hover:bg-blue-50/70', 'dark:hover:bg-blue-950/40', 'cursor-pointer');

                const rightCol = item.querySelector('.text-right.shrink-0');
                if (rightCol) {
                    rightCol.innerHTML = `<span class="inline-block text-[11px] font-semibold text-slate-500 dark:text-slate-400">Ready</span>`;
                }
            }
        });

        // 3. Update bulk notice banner
        const notice = document.getElementById('bulk-already-marked-notice');
        const noticeText = document.getElementById('bulk-already-marked-text');
        if (notice && noticeText) {
            if (count > 0) {
                notice.classList.remove('hidden');
                noticeText.textContent = `${count} workers already have attendance logged for this date. Select any worker to update their shift or site.`;
            } else {
                notice.classList.add('hidden');
            }
        }

        updateBulkCounter();
    }

    // Open Modal in Method 1 (Attendance Only)
    function openAttendanceModal(projectIdOrDate, workerIdOrArray, dateParam, isBulkMode = false) {
        let selectedProjId = null;
        let selectedWorkerId = null;
        let selectedWorkerIds = null;
        let selectedDateVal = null;

        if (Array.isArray(workerIdOrArray)) {
            isBulkMode = true;
            selectedWorkerIds = workerIdOrArray;
        } else if (workerIdOrArray) {
            selectedWorkerId = workerIdOrArray;
        }

        if (projectIdOrDate) {
            if (typeof projectIdOrDate === 'string' && projectIdOrDate.includes('-')) {
                selectedDateVal = projectIdOrDate;
            } else {
                selectedProjId = projectIdOrDate;
            }
        }
        if (dateParam) selectedDateVal = dateParam;

        if (selectedProjId) {
            const projSelect = document.getElementById('att-project-id');
            const projSelectBulk = document.getElementById('att-project-id-bulk');
            const outProj = document.getElementById('output-project-id');
            if (projSelect) projSelect.value = selectedProjId;
            if (projSelectBulk) projSelectBulk.value = selectedProjId;
            if (outProj) outProj.value = selectedProjId;
        }

        if (selectedDateVal) {
            const dateInput = document.getElementById('modal-work-date');
            const outDate = document.getElementById('output-work-date');
            if (dateInput) {
                dateInput.value = selectedDateVal;
                syncAttendanceDateLock(selectedDateVal);
            }
            if (outDate) {
                outDate.value = selectedDateVal;
            }
        }

        if (isBulkMode) {
            setAttendanceMode('bulk');
            if (selectedWorkerIds && selectedWorkerIds.length) {
                toggleAllBulkWorkers(false);
                selectedWorkerIds.forEach(id => {
                    const cb = document.querySelector(`.bulk-worker-check[value="${id}"]`);
                    if (cb) cb.checked = true;
                });
                updateBulkCounter();
            } else if (selectedWorkerId) {
                toggleAllBulkWorkers(false);
                const cb = document.querySelector(`.bulk-worker-check[value="${selectedWorkerId}"]`);
                if (cb) cb.checked = true;
                updateBulkCounter();
            }
        } else {
            setAttendanceMode('single');
            if (selectedWorkerId) {
                const workerSelect = document.getElementById('modal-worker-select');
                if (workerSelect) {
                    workerSelect.value = selectedWorkerId;
                    onSingleWorkerSelected(workerSelect.value);
                }
            }
        }

        setAttendanceOperationMode('attendance');
        document.getElementById('markAttendanceModal').classList.remove('hidden');
    }

    // Open Modal in Method 2 (Work Output Only)
    function openWorkOutputModal(projectId, dateParam) {
        if (projectId) {
            const outProj = document.getElementById('output-project-id');
            const projSelect = document.getElementById('att-project-id');
            if (outProj) outProj.value = projectId;
            if (projSelect) projSelect.value = projectId;
        }
        if (dateParam) {
            const outDate = document.getElementById('output-work-date');
            const dateInput = document.getElementById('modal-work-date');
            if (outDate) outDate.value = dateParam;
            if (dateInput) dateInput.value = dateParam;
        }

        setAttendanceOperationMode('workoutput');
        document.getElementById('markAttendanceModal').classList.remove('hidden');
    }

    function openBulkAttendanceModal(projectIdOrDate, workerIdsArray, dateParam) {
        openAttendanceModal(projectIdOrDate, workerIdsArray, dateParam, true);
    }

    function closeAttendanceModal() {
        document.getElementById('markAttendanceModal').classList.add('hidden');
    }

    // Validation & Submit Handler for Attendance Only
    function handleAttendanceSubmit(e) {
        const projectSelect = document.getElementById('att-project-id');
        const errBox = document.getElementById('modal-validation-error-att');
        const saveBtn = document.getElementById('btn-save-attendance');

        if (currentAttendanceMode === 'single') {
            const workerSelect = document.getElementById('modal-worker-select');
            if (!workerSelect || !workerSelect.value) {
                if (errBox) {
                    errBox.textContent = attendanceModalText.pickWorker;
                    errBox.classList.remove('hidden');
                }
                if (workerSelect) workerSelect.focus();
                return false;
            }
        } else {
            const checks = document.querySelectorAll('.bulk-worker-check:checked');
            if (!checks.length) {
                if (errBox) {
                    errBox.textContent = attendanceModalText.pickAtLeastOneWorker;
                    errBox.classList.remove('hidden');
                }
                return false;
            }
        }

        if (!projectSelect || !projectSelect.value) {
            if (errBox) {
                errBox.textContent = attendanceModalText.pickProject;
                errBox.classList.remove('hidden');
            }
            if (projectSelect) projectSelect.focus();
            return false;
        }

        if (errBox) errBox.classList.add('hidden');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="inline-block animate-spin mr-1.5">&#9696;</span> ' + attendanceModalText.savingAttendance;
        }
        return true;
    }

    // Validation & Submit Handler for Work Output Only
    function handleWorkOutputSubmit(e) {
        const projectSelect = document.getElementById('output-project-id');
        const errBox = document.getElementById('modal-validation-error-output');
        const saveBtn = document.getElementById('btn-save-work-output');

        if (!projectSelect || !projectSelect.value) {
            if (errBox) {
                errBox.textContent = attendanceModalText.pickProject;
                errBox.classList.remove('hidden');
            }
            if (projectSelect) projectSelect.focus();
            return false;
        }

        if (errBox) errBox.classList.add('hidden');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="inline-block animate-spin mr-1.5">&#9696;</span> ' + attendanceModalText.savingOutput;
        }
        return true;
    }

    // ═══════════════════════════════════════════════════════════════════
    // QUICK ADD NEW SITE LOCATION MODAL ACTIONS
    // ═══════════════════════════════════════════════════════════════════
    function openQuickAddSiteModal() {
        const modal = document.getElementById('quickAddSiteModal');
        if (!modal) return;
        const err = document.getElementById('quick-site-error');
        if (err) err.classList.add('hidden');
        modal.classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('quick-site-name')?.focus();
        }, 100);
    }

    function closeQuickAddSiteModal() {
        const modal = document.getElementById('quickAddSiteModal');
        if (modal) modal.classList.add('hidden');
    }

    async function submitQuickAddSite(e) {
        if (e && e.preventDefault) e.preventDefault();

        const name = document.getElementById('quick-site-name')?.value?.trim();
        const address = document.getElementById('quick-site-address')?.value?.trim();
        const city = document.getElementById('quick-site-city')?.value?.trim();
        const state = document.getElementById('quick-site-state')?.value?.trim();
        const client_name = document.getElementById('quick-site-client')?.value?.trim();
        const contact_phone = document.getElementById('quick-site-phone')?.value?.trim();
        const project_type = document.getElementById('quick-site-type')?.value;
        const google_maps_url = document.getElementById('quick-site-maps')?.value?.trim();
        const errBox = document.getElementById('quick-site-error');
        const saveBtn = document.getElementById('btn-quick-site-save');
        const saveText = document.getElementById('btn-quick-site-save-text');

        if (!name || !address) {
            if (errBox) {
                errBox.textContent = "{{ __('Please enter both the site location name and physical installation address.') }}";
                errBox.classList.remove('hidden');
            }
            return false;
        }

        if (errBox) errBox.classList.add('hidden');
        if (saveBtn) saveBtn.disabled = true;
        if (saveText) saveText.textContent = "{{ __('Saving Site Location...') }}";

        try {
            const res = await fetch("{{ route('sites.quick-create') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name,
                    address,
                    city,
                    state,
                    client_name,
                    contact_phone,
                    project_type,
                    google_maps_url
                })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                // Helper to insert option into Assigned Project Sites optgroup
                const appendOptionToSelect = (selectId, value, label) => {
                    const select = document.getElementById(selectId);
                    if (!select) return;

                    let optgroup = Array.from(select.querySelectorAll('optgroup')).find(og => 
                        og.label && (og.label.includes('My Assigned') || og.label.includes('Assigned'))
                    );

                    if (!optgroup) {
                        optgroup = document.createElement('optgroup');
                        optgroup.label = "⭐ {{ __('My Assigned Project Sites') }}";
                        const firstChild = select.querySelector('option[value=""]');
                        if (firstChild && firstChild.nextSibling) {
                            select.insertBefore(optgroup, firstChild.nextSibling);
                        } else {
                            select.appendChild(optgroup);
                        }
                    }

                    const newOpt = document.createElement('option');
                    newOpt.value = value;
                    newOpt.textContent = label;
                    newOpt.selected = true;

                    optgroup.insertBefore(newOpt, optgroup.firstChild);
                    select.value = value;
                };

                // Append to all 3 project site selectors
                appendOptionToSelect('att-project-id', data.project_id, data.label);
                appendOptionToSelect('att-project-id-bulk', data.project_id, data.label);
                appendOptionToSelect('output-project-id', data.project_id, data.label);

                // If hardware scanner modal is present on page, also append new lead/site to its dropdown
                if (data.lead_id) {
                    appendOptionToSelect('hw_scanner_lead_id', data.lead_id, data.lead_label || data.label);
                }

                // Sync bulk select value
                syncProjectSelect(data.project_id);

                // If in work output mode, fetch metrics for new site
                if (currentOperationMode === 'workoutput') {
                    fetchCurrentWorkOutput();
                }

                // Reset form
                document.getElementById('quick-add-site-form')?.reset();
                closeQuickAddSiteModal();

                // Display success notification
                if (window.toastNotification && typeof window.toastNotification.success === 'function') {
                    window.toastNotification.success(data.message || "{{ __('Site location added successfully!') }}");
                } else if (typeof showToast === 'function') {
                    showToast(data.message || "{{ __('Site location added successfully!') }}", 'success');
                } else {
                    const toast = document.createElement('div');
                    toast.className = "fixed bottom-5 right-5 z-[80] bg-emerald-600 text-white font-bold text-xs px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2 transition-all";
                    toast.innerHTML = `<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg><span>${data.message || 'Site location added successfully!'}</span>`;
                    document.body.appendChild(toast);
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        setTimeout(() => toast.remove(), 400);
                    }, 4000);
                }
            } else {
                const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : "{{ __('Failed to create site location.') }}");
                if (errBox) {
                    errBox.textContent = msg;
                    errBox.classList.remove('hidden');
                }
            }
        } catch (err) {
            console.error('Quick add site failed:', err);
            if (errBox) {
                errBox.textContent = "{{ __('An error occurred while saving the site location. Please try again.') }}";
                errBox.classList.remove('hidden');
            }
        } finally {
            if (saveBtn) saveBtn.disabled = false;
            if (saveText) saveText.textContent = "{{ __('Save Site Location') }}";
        }

        return false;
    }
</script>
