@props([
    'workers' => collect(),
    'projects' => collect(),
    'selectedDate' => null,
    'defaultProjectId' => null,
    'defaultWorkerId' => null,
])

{{-- MARK WORKER ATTENDANCE TWO-STEP SPLIT MODAL --}}
<div id="markAttendanceModal" class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-3 sm:p-4 hidden overflow-y-auto backdrop-blur-xs">
    <div class="bg-white dark:bg-slate-900 rounded-xl max-w-xl w-full p-5 sm:p-6 shadow-2xl border border-slate-200 dark:border-slate-800 my-auto transition-all">
        
        {{-- Modal Top Bar --}}
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ __('Mark Worker Attendance') }}</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Three-step guided check-in & work performance logging') }}</p>
            </div>
            <x-close-button onclick="closeAttendanceModal()" :label="__('Close')" />
        </div>

        {{-- Stepper Progress Indicator --}}
        <div class="mt-3 mb-3 bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700/60 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2" id="step-pill-1">
                <span id="step-badge-1" class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs bg-blue-600 text-white shadow-xs shrink-0">1</span>
                <div>
                    <div class="font-bold text-slate-900 dark:text-white leading-tight" id="step-title-1">{{ __('Step 1: Shift') }}</div>
                    <div class="text-[10px] text-slate-400 hidden sm:block">{{ __('Worker & site verification') }}</div>
                </div>
            </div>

            <div class="flex-1 mx-2 sm:mx-3 flex items-center">
                <div id="step-connector-line-1" class="h-0.5 w-full bg-slate-200 dark:bg-slate-700 transition-colors"></div>
            </div>

            <div class="flex items-center gap-2 opacity-60" id="step-pill-2">
                <span id="step-badge-2" class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 shrink-0">2</span>
                <div>
                    <div class="font-bold text-slate-700 dark:text-slate-300 leading-tight" id="step-title-2">{{ __('Step 2: Photo') }}</div>
                    <div class="text-[10px] text-slate-400 hidden sm:block">{{ __('Optional but Recommended') }}</div>
                </div>
            </div>

            <div class="flex-1 mx-2 sm:mx-3 flex items-center">
                <div id="step-connector-line-2" class="h-0.5 w-full bg-slate-200 dark:bg-slate-700 transition-colors"></div>
            </div>

            <div class="flex items-center gap-2 opacity-60" id="step-pill-3">
                <span id="step-badge-3" class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 shrink-0">3</span>
                <div>
                    <div class="font-bold text-slate-700 dark:text-slate-300 leading-tight" id="step-title-3">{{ __('Step 3: Work Output') }}</div>
                    <div class="text-[10px] text-slate-400 hidden sm:block">{{ __('Cabling & summary metrics') }}</div>
                </div>
            </div>
        </div>

        {{-- Form Container --}}
        <form id="mark-attendance-form" method="POST" action="{{ route('workers.attendance.store') }}" enctype="multipart/form-data" onsubmit="return handleAttendanceSubmit(event)">
            @csrf
            <input type="hidden" name="photo_data" id="webcam-photo-data">

            {{-- ══════════════════════════════════════════════════════ --}}
            {{-- STEP 1: SHIFT SELECTION                                --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            <div id="att-step-1" class="space-y-3">
                <div id="modal-validation-error-step1" class="hidden p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs"></div>

                {{-- Mode Switcher: Single Worker vs Bulk Workers --}}
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('Attendance Mode') }}:</span>
                    <div class="inline-flex p-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold">
                        <button type="button" id="att-mode-btn-single" onclick="setAttendanceMode('single')" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md transition-all bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            <span>{{ __('Single Worker') }}</span>
                        </button>
                        <button type="button" id="att-mode-btn-bulk" onclick="setAttendanceMode('bulk')" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer">
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
                            <select name="worker_id" id="modal-worker-select" class="crm-select text-xs w-full">
                                <option value="">{{ __('Select Worker...') }}</option>
                                @foreach($workers as $w)
                                    <option value="{{ $w->id }}" data-photo="{{ $w->photo_url ?? '' }}" data-name="{{ $w->name }}" data-rate="{{ number_format($w->daily_rate, 0) }}" @selected(old('worker_id', $defaultWorkerId) == $w->id)>
                                        {{ $w->name }} (&#8377;{{ number_format($w->daily_rate, 0) }}/{{ __('day') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="att-project-id" class="crm-label text-xs">{{ __('Project Site') }} <span class="required-marker">*</span></label>
                            <select id="att-project-id" name="project_id" required class="crm-select text-xs w-full">
                                <option value="">{{ __('Select Project Site...') }}</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" @selected(old('project_id', $defaultProjectId) == $p->id)>
                                        {{ Str::limit($p->title, 28) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- MODE 2: BULK MULTI-WORKER SELECTOR --}}
                <div id="bulk-worker-container" class="hidden space-y-3">
                    <div>
                        <label for="att-project-id-bulk-ref" class="crm-label text-xs">{{ __('Project Site') }} <span class="required-marker">*</span></label>
                        <select onchange="syncProjectSelect(this.value)" id="att-project-id-bulk" class="crm-select text-xs w-full">
                            <option value="">{{ __('Select Project Site for all selected workers...') }}</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" @selected(old('project_id', $defaultProjectId) == $p->id)>
                                    {{ Str::limit($p->title, 34) }}
                                </option>
                            @endforeach
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

                        {{-- Realtime Search Filter --}}
                        <div class="relative mb-2">
                            <svg class="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                            <input type="text" id="bulk-worker-search" oninput="filterBulkWorkers(this.value)" placeholder="{{ __('Search worker by name or phone...') }}" class="crm-input text-xs w-full pl-8 py-1.5 bg-white dark:bg-slate-900">
                        </div>

                        {{-- Worker Clickable Checkbox List --}}
                        <div id="bulk-worker-list" class="max-h-48 sm:max-h-56 overflow-y-auto rounded-lg border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-slate-800/80 p-1">
                            @foreach($workers as $w)
                                <label class="flex items-center justify-between p-2 rounded-md hover:bg-blue-50/70 dark:hover:bg-blue-950/40 cursor-pointer transition select-none group bulk-worker-item" data-worker-name="{{ strtolower($w->name) }}" data-worker-phone="{{ $w->phone }}">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <input type="checkbox" name="worker_ids[]" value="{{ $w->id }}" data-name="{{ $w->name }}" data-rate="{{ (float) $w->daily_rate }}" data-photo="{{ $w->photo_url ?? '' }}" onchange="updateBulkCounter()" class="bulk-worker-check w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 cursor-pointer">
                                        @if($w->photo_url)
                                            <img src="{{ $w->photo_url }}" alt="{{ $w->name }}" class="w-7 h-7 rounded-full object-cover shrink-0 border border-slate-200 dark:border-slate-700">
                                        @else
                                            <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ strtoupper(mb_substr($w->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ $w->name }}</span>
                                            <span class="block text-[10px] text-slate-400 truncate">{{ $w->phone ?: __('No phone') }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0 ml-2">
                                        <span class="inline-block text-xs font-bold text-slate-900 dark:text-slate-100">&#8377;{{ number_format($w->daily_rate, 0) }}</span>
                                        <span class="block text-[10px] text-slate-400">/{{ __('day') }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        {{-- Bulk Summary Line --}}
                        <div class="mt-2 flex items-center justify-between text-xs text-slate-600 dark:text-slate-300 pt-1">
                            <span>{{ __('Estimated Daily Base Wage:') }}</span>
                            <span class="font-bold text-slate-900 dark:text-white" id="bulk-total-wage">&#8377;0</span>
                        </div>
                    </div>
                </div>

                {{-- Date, Shift Type, Punch Time --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="modal-work-date" class="crm-label text-xs">{{ __('Work Date') }} <span class="required-marker">*</span></label>
                        <input type="date" name="work_date" id="modal-work-date" value="{{ $selectedDate ?? now()->toDateString() }}" required class="crm-input text-xs w-full">
                    </div>

                    <div>
                        <label for="att-attendance-type" class="crm-label text-xs">{{ __('Shift Type') }} <span class="required-marker">*</span></label>
                        <select id="att-attendance-type" name="attendance_type" onchange="updateBulkCounter()" required class="crm-select text-xs w-full">
                            <option value="full" selected>{{ __('Full Day (1.0x)') }}</option>
                            <option value="half">{{ __('Half Day (0.5x)') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="att-check-in-time" class="crm-label text-xs">{{ __('Punch Time') }}</label>
                        <input id="att-check-in-time" type="time" name="check_in_time" value="{{ now()->format('H:i') }}" class="crm-input text-xs w-full">
                    </div>
                </div>

                {{-- Step 1 Actions --}}
                <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" onclick="closeAttendanceModal()" class="btn-secondary text-xs">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" onclick="goToAttendanceStep(2)" class="btn-primary text-xs inline-flex items-center gap-1.5">
                        <span>{{ __('Next: Photo') }}</span>
                        <span>&rarr;</span>
                    </button>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════ --}}
            {{-- STEP 2: PHOTO VERIFICATION                             --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            <div id="att-step-2" class="hidden space-y-3">
                {{-- PHOTO CAPTURE SECTION --}}
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">
                            {{ __('Attendance Photo (Selfie / Work Site)') }}
                        </label>
                        <span class="text-[10px] text-slate-400 font-normal">{{ __('Optional but Recommended') }}</span>
                    </div>

                    {{-- Mode Switch Tabs --}}
                    <div class="inline-flex items-center p-1 rounded-lg bg-slate-200/70 dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700 text-xs mb-3">
                        <button type="button" onclick="setPhotoTab('camera')" id="tab-btn-camera" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md font-semibold text-xs transition bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                            <span>{{ __('Live Camera') }}</span>
                        </button>
                        <button type="button" onclick="setPhotoTab('file')" id="tab-btn-file" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md font-semibold text-xs transition text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            <span>{{ __('Upload File') }}</span>
                        </button>
                    </div>

                    {{-- Mode 1: Live Camera Stream --}}
                    <div id="photo-tab-camera">
                        <div class="relative bg-slate-100/90 dark:bg-slate-800/80 rounded-xl overflow-hidden min-h-[185px] flex items-center justify-center border border-slate-200 dark:border-slate-700">
                            {{-- Camera Standby Placeholder (Shown when camera is off/idle) --}}
                            <div id="camera-idle-placeholder" class="text-center p-5 flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/40 border border-blue-200 dark:border-blue-700/60 flex items-center justify-center text-blue-600 dark:text-blue-400 mb-2 shadow-xs">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ __('Live Camera Standby') }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Click "Start Camera" below to preview worker selfie') }}</p>
                            </div>

                            {{-- Active Video Feed (Hidden until Start Camera is clicked) --}}
                            <video id="webcam-video" autoplay playsinline class="w-full h-48 sm:h-52 object-cover hidden"></video>
                            <canvas id="webcam-canvas" class="hidden"></canvas>

                            {{-- Snapped Preview Surface (Replaces video feed completely upon snap) --}}
                            <div id="camera-snapped-container" class="hidden flex flex-col items-center justify-center p-3.5 w-full">
                                <div class="relative">
                                    <img id="camera-snapped-img" alt="{{ __('Captured Selfie') }}" class="w-28 h-28 object-cover rounded-xl border-2 border-emerald-500 shadow-md">
                                    <span class="absolute -top-2 -right-2 bg-emerald-600 text-white rounded-full p-1 shadow">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    </span>
                                </div>
                                <div class="mt-2.5 flex items-center gap-2">
                                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">{{ __('Photo Snapped & Ready') }}</span>
                                    <button type="button" onclick="retakeWebcamPhoto()" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 hover:underline cursor-pointer">
                                        ({{ __('Retake') }})
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Camera Action Controls --}}
                        <div class="flex items-center justify-between mt-2.5 flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="startWebcam()" id="btn-start-camera" class="btn-md inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold text-xs hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                                    <span>{{ __('Start Camera') }}</span>
                                </button>
                                <button type="button" onclick="snapWebcamPhoto()" id="btn-snap-camera" class="btn-md inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition cursor-pointer hidden">
                                    <span>📸 {{ __('Snap Photo') }}</span>
                                </button>
                            </div>
                            <span id="camera-status-text" class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Click Start Camera to take selfie') }}</span>
                        </div>
                    </div>

                    {{-- Mode 2: File Upload --}}
                    <div id="photo-tab-file" class="hidden">
                        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-600 text-center flex flex-col items-center justify-center">
                            <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                            </svg>
                            <label for="modal-photo-input" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                {{ __('Browse device photo or gallery') }}
                            </label>
                            <label for="modal-photo-input" class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900 transition shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ __('Choose Photo') }}</span>
                            </label>
                            <input aria-label="{{ __('Site photo') }}" type="file" name="photo" id="modal-photo-input" accept="image/*" class="sr-only hidden">
                            <p class="text-[10px] text-slate-400 mt-1">{{ __('JPEG, PNG or WebP up to 10MB') }}</p>
                        </div>
                        <div id="file-preview-wrap" class="mt-3 hidden flex items-center justify-center gap-3">
                            <img id="file-preview-img" alt="{{ __('Preview') }}" class="w-16 h-16 object-cover rounded-xl border-2 border-blue-500 shadow-md">
                            <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">✓ {{ __('File selected & ready') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Step 2 Actions --}}
                <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" onclick="goToAttendanceStep(1)" class="btn-secondary text-xs inline-flex items-center gap-1">
                        <span>&larr; {{ __('Back') }}</span>
                    </button>
                    <button type="button" onclick="goToAttendanceStep(3)" class="btn-primary text-xs inline-flex items-center gap-1.5">
                        <span>{{ __('Next: Work Output & Metrics') }}</span>
                        <span>&rarr;</span>
                    </button>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════ --}}
            {{-- STEP 3: WORK PERFORMANCE METRICS & SUMMARY            --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            <div id="att-step-3" class="hidden space-y-3">
                <div id="modal-validation-error-step2" class="hidden p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs"></div>

                {{-- Step 1 Summary Banner Confirmation Card --}}
                <div id="single-summary-card" class="p-3 rounded-lg bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/80 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <img id="summary-worker-thumb" src="" alt="{{ __('Worker') }}" class="w-9 h-9 rounded-full object-cover border-2 border-blue-400 shrink-0 hidden">
                        <div id="summary-worker-avatar-fallback" class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                            WK
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white" id="summary-worker-name">{{ __('Selected Worker') }}</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400" id="summary-project-details">{{ __('Project Site') }} &bull; {{ __('Date') }}</div>
                        </div>
                    </div>
                    <button type="button" onclick="goToAttendanceStep(1)" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                        <span>&larr; {{ __('Change Worker / Site') }}</span>
                    </button>
                </div>

                {{-- Bulk Workers Confirmation Card --}}
                <div id="bulk-summary-card" class="p-3 rounded-lg bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/80 hidden">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                            <span class="text-xs font-bold text-slate-900 dark:text-white" id="bulk-summary-title">0 {{ __('Workers Selected') }}</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400" id="bulk-summary-project"></span>
                        </div>
                        <button type="button" onclick="goToAttendanceStep(1)" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            {{ __('Edit Selection') }}
                        </button>
                    </div>
                    <div id="bulk-summary-chips" class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto"></div>
                    <div class="mt-2 pt-2 border-t border-blue-200/60 dark:border-blue-800/60 flex items-center justify-between text-xs">
                        <span class="text-slate-600 dark:text-slate-300">{{ __('Combined Estimated Wage:') }}</span>
                        <strong id="bulk-summary-total-wage" class="text-blue-700 dark:text-blue-300 font-bold">&#8377;0</strong>
                    </div>
                </div>

                {{-- Cabling Metres & Cameras Installed --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="att-cabling-metres" class="crm-label text-xs">{{ __('Cabling Metres Done') }}</label>
                        <input id="att-cabling-metres" type="number" step="0.1" min="0" name="cabling_metres" placeholder="{{ __('e.g. 75.0') }}" class="crm-input text-xs w-full">
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Physical cable pulled today (m)') }}</p>
                    </div>

                    <div>
                        <label for="att-cameras-installed" class="crm-label text-xs">{{ __('Cameras Installed') }}</label>
                        <input id="att-cameras-installed" type="number" min="0" name="cameras_installed" placeholder="{{ __('e.g. 4') }}" class="crm-input text-xs w-full">
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Units mounted/configured today') }}</p>
                    </div>
                </div>

                {{-- Extra Allowance & Reason --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="att-extra-amount" class="crm-label text-xs">{{ __('Extra Allowance (₹)') }}</label>
                        <input id="att-extra-amount" type="number" step="any" min="0" name="extra_amount" placeholder="{{ __('e.g. 150') }}" class="crm-input text-xs w-full">
                    </div>
                    <div>
                        <label for="att-extra-description" class="crm-label text-xs">{{ __('Extra Reason') }}</label>
                        <x-tamil-input>
                            <input id="att-extra-description" type="text" name="extra_description" placeholder="{{ __('e.g. Night shift / Travel allowance') }}" class="crm-input text-xs w-full">
                        </x-tamil-input>
                    </div>
                </div>

                {{-- Work Notes --}}
                <div>
                    <label for="att-notes" class="crm-label text-xs">{{ __('Work Notes / Summary') }}</label>
                    <x-tamil-input>
                        <textarea id="att-notes" name="notes" rows="2" placeholder="{{ __('Summary of work performed, site conditions, testing details...') }}" class="crm-input text-xs w-full"></textarea>
                    </x-tamil-input>
                </div>

                {{-- Step 3 Actions --}}
                <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" onclick="goToAttendanceStep(2)" class="btn-secondary text-xs inline-flex items-center gap-1">
                        <span>&larr; {{ __('Back') }}</span>
                    </button>
                    <button type="submit" id="btn-save-attendance" class="btn-primary text-xs inline-flex items-center gap-1.5 font-semibold">
                        <span>✓ {{ __('Save Attendance') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const attendanceModalText = {{ Js::from([
        'pickWorker' => __('Please select a Worker before proceeding to Step 2.'),
        'pickAtLeastOneWorker' => __('Please select at least one Worker before proceeding to Step 2.'),
        'pickProject' => __('Please select a Project Site before proceeding to Step 2.'),
        'halfDay' => __('Half Day (0.5x)'),
        'fullDay' => __('Full Day (1.0x)'),
        'perDay' => __('day'),
        'cameraActive' => __('Camera active'),
        'frameFace' => __('Frame face & snap photo'),
        'cameraUnavailable' => __('Camera unavailable:'),
        'permissionDenied' => __('Permission denied'),
        'selfieCaptured' => __('Photo captured!'),
        'readyToProceed' => __('Ready to proceed'),
        'clickStart' => __('Click Start Camera to take photo'),
        'saving' => __('Saving Attendance...'),
        'workersSelected' => __('workers selected'),
    ]) }};
    let globalWebcamStream = null;
    let currentAttendanceMode = 'single';

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
            btnBulk.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-md transition-all bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs cursor-pointer";
            btnSingle.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-md transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer";
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
            btnSingle.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-md transition-all bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs cursor-pointer";
            btnBulk.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-md transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer";
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

    function toggleAllBulkWorkers(selectAll) {
        const items = document.querySelectorAll('.bulk-worker-item');
        items.forEach(item => {
            if (item.style.display !== 'none') {
                const cb = item.querySelector('.bulk-worker-check');
                if (cb) cb.checked = selectAll;
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
        const allChecks = document.querySelectorAll('.bulk-worker-check');
        const badge = document.getElementById('bulk-worker-count-badge');
        const wageEl = document.getElementById('bulk-total-wage');
        const shiftMultiplier = (document.getElementById('att-attendance-type')?.value === 'half') ? 0.5 : 1.0;

        let totalRate = 0;
        checks.forEach(c => {
            const r = parseFloat(c.getAttribute('data-rate') || 0);
            totalRate += r;
        });

        const effectiveWage = totalRate * shiftMultiplier;

        if (badge) {
            badge.textContent = `${checks.length} / ${allChecks.length} ${attendanceModalText.workersSelected}`;
        }
        if (wageEl) {
            wageEl.innerHTML = `&#8377;${Math.round(effectiveWage).toLocaleString()}`;
        }
    }

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
            if (projSelect) projSelect.value = selectedProjId;
            if (projSelectBulk) projSelectBulk.value = selectedProjId;
        }

        if (selectedDateVal) {
            const dateInput = document.getElementById('modal-work-date');
            if (dateInput) dateInput.value = selectedDateVal;
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
                }
            }
        }

        goToAttendanceStep(1);
        document.getElementById('markAttendanceModal').classList.remove('hidden');
    }

    function openBulkAttendanceModal(projectIdOrDate, workerIdsArray, dateParam) {
        openAttendanceModal(projectIdOrDate, workerIdsArray, dateParam, true);
    }

    function closeAttendanceModal() {
        stopWebcam();
        document.getElementById('markAttendanceModal').classList.add('hidden');
    }

    function goToAttendanceStep(step) {
        const err1 = document.getElementById('modal-validation-error-step1');
        const err2 = document.getElementById('modal-validation-error-step2');

        if (step > 1) {
            const projectSelect = document.getElementById('att-project-id');

            if (currentAttendanceMode === 'single') {
                const workerSelect = document.getElementById('modal-worker-select');
                if (!workerSelect || !workerSelect.value) {
                    goToAttendanceStep(1);
                    if (err1) {
                        err1.textContent = attendanceModalText.pickWorker;
                        err1.classList.remove('hidden');
                    }
                    if (workerSelect) workerSelect.focus();
                    return;
                }
            } else {
                const checks = document.querySelectorAll('.bulk-worker-check:checked');
                if (!checks.length) {
                    goToAttendanceStep(1);
                    if (err1) {
                        err1.textContent = attendanceModalText.pickAtLeastOneWorker;
                        err1.classList.remove('hidden');
                    }
                    return;
                }
            }

            if (!projectSelect || !projectSelect.value) {
                goToAttendanceStep(1);
                if (err1) {
                    err1.textContent = attendanceModalText.pickProject;
                    err1.classList.remove('hidden');
                }
                if (projectSelect) projectSelect.focus();
                return;
            }

            if (err1) err1.classList.add('hidden');
        } else if (err2) {
            err2.classList.add('hidden');
        }

        if (step === 3) {
            const projectSelect = document.getElementById('att-project-id');
            const projOpt = projectSelect ? projectSelect.options[projectSelect.selectedIndex] : null;
            const dateVal = document.getElementById('modal-work-date')?.value || '';
            const shiftType = (document.getElementById('att-attendance-type')?.value === 'half') ? attendanceModalText.halfDay : attendanceModalText.fullDay;
            const singleCard = document.getElementById('single-summary-card');
            const bulkCard = document.getElementById('bulk-summary-card');

            if (currentAttendanceMode === 'single') {
                singleCard.classList.remove('hidden');
                bulkCard.classList.add('hidden');

                const workerSelect = document.getElementById('modal-worker-select');
                const workerOpt = workerSelect ? workerSelect.options[workerSelect.selectedIndex] : null;
                const photoData = document.getElementById('webcam-photo-data')?.value;
                const profilePhoto = workerOpt ? workerOpt.getAttribute('data-photo') : null;

                if (workerOpt) {
                    document.getElementById('summary-worker-name').textContent = workerOpt.getAttribute('data-name') || workerOpt.text;
                }
                if (projOpt) {
                    document.getElementById('summary-project-details').textContent = `${projOpt.text.trim()} • ${dateVal} • ${shiftType}`;
                }

                const thumb = document.getElementById('summary-worker-thumb');
                const fallback = document.getElementById('summary-worker-avatar-fallback');

                if (photoData) {
                    thumb.src = photoData;
                    thumb.classList.remove('hidden');
                    fallback.classList.add('hidden');
                } else if (profilePhoto) {
                    thumb.src = profilePhoto;
                    thumb.classList.remove('hidden');
                    fallback.classList.add('hidden');
                } else {
                    thumb.classList.add('hidden');
                    fallback.classList.remove('hidden');
                    fallback.textContent = workerOpt ? (workerOpt.getAttribute('data-name') || 'WK').substring(0, 2).toUpperCase() : 'WK';
                }
            } else {
                singleCard.classList.add('hidden');
                bulkCard.classList.remove('hidden');

                const checks = document.querySelectorAll('.bulk-worker-check:checked');
                const chipsContainer = document.getElementById('bulk-summary-chips');
                chipsContainer.innerHTML = '';

                let totalWage = 0;
                const shiftMultiplier = (document.getElementById('att-attendance-type')?.value === 'half') ? 0.5 : 1.0;

                checks.forEach(c => {
                    const name = c.getAttribute('data-name');
                    const rate = parseFloat(c.getAttribute('data-rate') || 0);
                    totalWage += rate * shiftMultiplier;

                    const chip = document.createElement('span');
                    chip.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-800 text-slate-800 dark:text-slate-200 shadow-2xs';
                    chip.innerHTML = `<span>✓</span> <span>${name}</span>`;
                    chipsContainer.appendChild(chip);
                });

                document.getElementById('bulk-summary-title').textContent = `${checks.length} ${attendanceModalText.workersSelected}`;
                if (projOpt) {
                    document.getElementById('bulk-summary-project').textContent = `• ${projOpt.text.trim()} • ${dateVal} • ${shiftType}`;
                }
                document.getElementById('bulk-summary-total-wage').innerHTML = `&#8377;${Math.round(totalWage).toLocaleString()}`;
            }
        }

        // Transition Panes & Stepper Visuals
        const badgeBase = 'w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs shrink-0 ';
        [1, 2, 3].forEach((n) => {
            document.getElementById(`att-step-${n}`).classList.toggle('hidden', n !== step);
            document.getElementById(`step-pill-${n}`).className = n > step ? 'flex items-center gap-2 opacity-60' : 'flex items-center gap-2';
            const badge = document.getElementById(`step-badge-${n}`);
            badge.innerHTML = n < step ? '&#10003;' : n;
            badge.className = badgeBase + (n < step
                ? 'bg-emerald-600 text-white shadow-xs'
                : n === step ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300');
        });
        [1, 2].forEach((n) => {
            document.getElementById(`step-connector-line-${n}`).className = 'h-0.5 w-full transition-colors ' + (n < step ? 'bg-blue-600 dark:bg-blue-500' : 'bg-slate-200 dark:bg-slate-700');
        });
    }

    function setPhotoTab(mode) {
        const tabFile = document.getElementById('photo-tab-file');
        const tabCamera = document.getElementById('photo-tab-camera');
        const btnFile = document.getElementById('tab-btn-file');
        const btnCamera = document.getElementById('tab-btn-camera');

        if (mode === 'file') {
            tabFile.classList.remove('hidden');
            tabCamera.classList.add('hidden');
            btnFile.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-md font-semibold text-xs transition bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs cursor-pointer";
            btnCamera.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-md font-semibold text-xs transition text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer";
            stopWebcam();
        } else {
            tabFile.classList.add('hidden');
            tabCamera.classList.remove('hidden');
            btnCamera.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-md font-semibold text-xs transition bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs cursor-pointer";
            btnFile.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-md font-semibold text-xs transition text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer";
        }
    }

    async function startWebcam() {
        const video = document.getElementById('webcam-video');
        const placeholder = document.getElementById('camera-idle-placeholder');
        const snappedContainer = document.getElementById('camera-snapped-container');
        const status = document.getElementById('camera-status-text');
        const startBtn = document.getElementById('btn-start-camera');
        const snapBtn = document.getElementById('btn-snap-camera');

        try {
            globalWebcamStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
            video.srcObject = globalWebcamStream;
            video.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
            if (snappedContainer) snappedContainer.classList.add('hidden');
            if (status) status.innerHTML = '<span class="text-emerald-600 dark:text-emerald-400 font-semibold">🟢 ' + attendanceModalText.cameraActive + '</span> - ' + attendanceModalText.frameFace;
            if (startBtn) startBtn.classList.add('hidden');
            if (snapBtn) snapBtn.classList.remove('hidden');
        } catch (err) {
            if (status) status.innerHTML = '<span class="text-rose-500 font-semibold">⚠️ ' + attendanceModalText.cameraUnavailable + '</span> ' + (err.message || attendanceModalText.permissionDenied);
        }
    }

    function snapWebcamPhoto() {
        const video = document.getElementById('webcam-video');
        const canvas = document.getElementById('webcam-canvas');
        const dataInput = document.getElementById('webcam-photo-data');
        const snappedContainer = document.getElementById('camera-snapped-container');
        const previewImg = document.getElementById('camera-snapped-img');
        const status = document.getElementById('camera-status-text');

        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const base64 = canvas.toDataURL('image/jpeg', 0.85);
        dataInput.value = base64;
        previewImg.src = base64;

        video.classList.add('hidden');
        snappedContainer.classList.remove('hidden');
        if (status) status.innerHTML = '<span class="text-emerald-600 dark:text-emerald-400 font-semibold">✓ ' + attendanceModalText.selfieCaptured + '</span> ' + attendanceModalText.readyToProceed;

        stopWebcam(true);
    }

    function retakeWebcamPhoto() {
        const dataInput = document.getElementById('webcam-photo-data');
        if (dataInput) dataInput.value = '';
        startWebcam();
    }

    function stopWebcam(keepSnapped = false) {
        if (globalWebcamStream) {
            globalWebcamStream.getTracks().forEach(t => t.stop());
            globalWebcamStream = null;
        }
        const video = document.getElementById('webcam-video');
        const placeholder = document.getElementById('camera-idle-placeholder');
        const snappedContainer = document.getElementById('camera-snapped-container');
        const startBtn = document.getElementById('btn-start-camera');
        const snapBtn = document.getElementById('btn-snap-camera');
        const status = document.getElementById('camera-status-text');

        if (video) video.classList.add('hidden');
        if (snapBtn) snapBtn.classList.add('hidden');
        if (startBtn) startBtn.classList.remove('hidden');

        if (!keepSnapped) {
            if (placeholder) placeholder.classList.remove('hidden');
            if (snappedContainer) snappedContainer.classList.add('hidden');
            if (status) status.textContent = attendanceModalText.clickStart;
        }
    }

    document.getElementById('modal-photo-input')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(evt) {
                const tempImg = new Image();
                tempImg.onload = function() {
                    const canvas = document.createElement('canvas');
                    const maxDim = 800;
                    let w = tempImg.width;
                    let h = tempImg.height;
                    if (w > maxDim || h > maxDim) {
                        if (w > h) {
                            h = Math.round((h * maxDim) / w);
                            w = maxDim;
                        } else {
                            w = Math.round((w * maxDim) / h);
                            h = maxDim;
                        }
                    }
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(tempImg, 0, 0, w, h);
                    const compressedBase64 = canvas.toDataURL('image/jpeg', 0.85);

                    const dataInput = document.getElementById('webcam-photo-data');
                    if (dataInput) dataInput.value = compressedBase64;

                    const wrap = document.getElementById('file-preview-wrap');
                    const img = document.getElementById('file-preview-img');
                    if (img && wrap) {
                        img.src = compressedBase64;
                        wrap.classList.remove('hidden');
                    }
                };
                tempImg.src = evt.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    function handleAttendanceSubmit(e) {
        const projectSelect = document.getElementById('att-project-id');
        const errorBox = document.getElementById('modal-validation-error-step2');
        const saveBtn = document.getElementById('btn-save-attendance');

        if (currentAttendanceMode === 'single') {
            const workerSelect = document.getElementById('modal-worker-select');
            if (!workerSelect || !workerSelect.value || !projectSelect || !projectSelect.value) {
                goToAttendanceStep(1);
                return false;
            }
        } else {
            const checks = document.querySelectorAll('.bulk-worker-check:checked');
            if (!checks.length || !projectSelect || !projectSelect.value) {
                goToAttendanceStep(1);
                return false;
            }
        }

        if (errorBox) errorBox.classList.add('hidden');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="inline-block animate-spin mr-1.5">&#9696;</span> ' + attendanceModalText.saving;
        }
        return true;
    }
</script>

