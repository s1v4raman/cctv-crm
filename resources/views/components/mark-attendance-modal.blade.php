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
                    <span>Mark Worker Attendance</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Two-step guided check-in &amp; work performance logging</p>
            </div>
            <x-close-button onclick="closeAttendanceModal()" />
        </div>

        {{-- Stepper Progress Indicator --}}
        <div class="mt-3.5 mb-4 bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700/60 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2" id="step-pill-1">
                <span id="step-badge-1" class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs bg-blue-600 text-white shadow-xs">1</span>
                <div>
                    <div class="font-bold text-slate-900 dark:text-white leading-tight" id="step-title-1">Step 1: Shift &amp; Photo</div>
                    <div class="text-[10px] text-slate-400">Worker &amp; site verification</div>
                </div>
            </div>

            <div class="flex-1 mx-3 flex items-center">
                <div id="step-connector-line" class="h-0.5 w-full bg-slate-200 dark:bg-slate-700 transition-colors"></div>
            </div>

            <div class="flex items-center gap-2 opacity-60" id="step-pill-2">
                <span id="step-badge-2" class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">2</span>
                <div>
                    <div class="font-bold text-slate-700 dark:text-slate-300 leading-tight" id="step-title-2">Step 2: Work Output</div>
                    <div class="text-[10px] text-slate-400">Cabling &amp; summary metrics</div>
                </div>
            </div>
        </div>

        {{-- Form Container --}}
        <form id="mark-attendance-form" method="POST" action="{{ route('workers.attendance.store') }}" enctype="multipart/form-data" onsubmit="return handleAttendanceSubmit(event)">
            @csrf
            <input type="hidden" name="photo_data" id="webcam-photo-data">

            {{-- ══════════════════════════════════════════════════════ --}}
            {{-- STEP 1: SHIFT SELECTION & PHOTO VERIFICATION           --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            <div id="att-step-1" class="space-y-4">
                <div id="modal-validation-error-step1" class="hidden p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs"></div>

                {{-- Worker & Project Selectors --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="modal-worker-select" class="crm-label text-xs">Worker <span class="required-marker">*</span></label>
                        <select name="worker_id" id="modal-worker-select" onchange="onWorkerSelectChange(this)" required class="crm-select text-xs w-full">
                            <option value="">Select Worker...</option>
                            @foreach($workers as $w)
                                <option value="{{ $w->id }}" data-photo="{{ $w->photo_url ?? '' }}" data-name="{{ $w->name }}" data-rate="{{ number_format($w->daily_rate, 0) }}" @selected(old('worker_id', $defaultWorkerId) == $w->id)>
                                    {{ $w->name }} (&#8377;{{ number_format($w->daily_rate, 0) }}/day)
                                </option>
                            @endforeach
                        </select>
                        <div id="modal-worker-preview-box" class="flex items-center gap-2 mt-1.5 p-1.5 rounded bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hidden">
                            <img id="modal-worker-img" alt="Worker" class="w-6 h-6 rounded-full object-cover border border-slate-300 dark:border-slate-600">
                            <span class="text-[11px] text-slate-700 dark:text-slate-300 font-semibold" id="modal-worker-name-preview"></span>
                        </div>
                    </div>

                    <div>
                        <label for="att-project-id" class="crm-label text-xs">Project Site <span class="required-marker">*</span></label>
                        <select id="att-project-id" name="project_id" required class="crm-select text-xs w-full">
                            <option value="">Select Project Site...</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" @selected(old('project_id', $defaultProjectId) == $p->id)>
                                    {{ Str::limit($p->title, 28) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Date, Shift Type, Punch Time --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="modal-work-date" class="crm-label text-xs">Work Date <span class="required-marker">*</span></label>
                        <input type="date" name="work_date" id="modal-work-date" value="{{ $selectedDate ?? now()->toDateString() }}" required class="crm-input text-xs w-full">
                    </div>

                    <div>
                        <label for="att-attendance-type" class="crm-label text-xs">Shift Type <span class="required-marker">*</span></label>
                        <select id="att-attendance-type" name="attendance_type" required class="crm-select text-xs w-full">
                            <option value="full" selected>Full Day (1.0x)</option>
                            <option value="half">Half Day (0.5x)</option>
                        </select>
                    </div>

                    <div>
                        <label for="att-check-in-time" class="crm-label text-xs">Punch Time</label>
                        <input id="att-check-in-time" type="time" name="check_in_time" value="{{ now()->format('H:i') }}" class="crm-input text-xs w-full">
                    </div>
                </div>

                {{-- PHOTO CAPTURE SECTION --}}
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">
                            Attendance Photo (Selfie / Work Site)
                        </label>
                        <span class="text-[10px] text-slate-400 font-normal">Optional but Recommended</span>
                    </div>

                    {{-- Mode Switch Tabs --}}
                    <div class="inline-flex items-center p-1 rounded-lg bg-slate-200/70 dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700 text-xs mb-3">
                        <button type="button" onclick="setPhotoTab('camera')" id="tab-btn-camera" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md font-semibold text-xs transition bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                            <span>Live Camera</span>
                        </button>
                        <button type="button" onclick="setPhotoTab('file')" id="tab-btn-file" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md font-semibold text-xs transition text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            <span>Upload File</span>
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
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Live Camera Standby</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Click "Start Camera" below to preview worker selfie</p>
                            </div>

                            {{-- Active Video Feed (Hidden until Start Camera is clicked) --}}
                            <video id="webcam-video" autoplay playsinline class="w-full h-48 sm:h-52 object-cover hidden"></video>
                            <canvas id="webcam-canvas" class="hidden"></canvas>

                            {{-- Snapped Preview Surface (Replaces video feed completely upon snap) --}}
                            <div id="camera-snapped-container" class="hidden flex flex-col items-center justify-center p-3.5 w-full">
                                <div class="relative">
                                    <img id="camera-snapped-img" alt="Captured Selfie" class="w-36 h-36 object-cover rounded-xl border-2 border-emerald-500 shadow-md">
                                    <span class="absolute -top-2 -right-2 bg-emerald-600 text-white rounded-full p-1 shadow">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    </span>
                                </div>
                                <div class="mt-2.5 flex items-center gap-2">
                                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Photo Snapped &amp; Ready</span>
                                    <button type="button" onclick="retakeWebcamPhoto()" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 hover:underline cursor-pointer">
                                        (Retake)
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Camera Action Controls --}}
                        <div class="flex items-center justify-between mt-2.5 flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="startWebcam()" id="btn-start-camera" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold text-xs hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                                    <span>Start Camera</span>
                                </button>
                                <button type="button" onclick="snapWebcamPhoto()" id="btn-snap-camera" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition cursor-pointer hidden">
                                    <span>📸 Snap Photo</span>
                                </button>
                            </div>
                            <span id="camera-status-text" class="text-[11px] text-slate-500 dark:text-slate-400">Click Start Camera to take selfie</span>
                        </div>
                    </div>

                    {{-- Mode 2: File Upload --}}
                    <div id="photo-tab-file" class="hidden">
                        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-600 text-center flex flex-col items-center justify-center">
                            <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                            </svg>
                            <label for="modal-photo-input" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                Browse device photo or gallery
                            </label>
                            <input aria-label="Site photo" type="file" name="photo" id="modal-photo-input" accept="image/*" class="mt-2 text-xs text-slate-600 dark:text-slate-300 file:mr-2.5 file:py-1.5 file:px-3 file:rounded file:border file:border-slate-300 dark:file:border-slate-600 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-200 hover:file:bg-slate-200 dark:hover:file:bg-slate-700 cursor-pointer w-full max-w-xs">
                            <p class="text-[10px] text-slate-400 mt-1">JPEG, PNG or WebP up to 10MB</p>
                        </div>
                        <div id="file-preview-wrap" class="mt-3 hidden text-center">
                            <img id="file-preview-img" alt="Preview" class="w-32 h-32 object-cover rounded-xl border-2 border-blue-500 shadow-md mx-auto">
                            <span class="inline-block mt-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">✓ File selected &amp; ready</span>
                        </div>
                    </div>
                </div>

                {{-- Step 1 Actions --}}
                <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" onclick="closeAttendanceModal()" class="btn-secondary text-xs">
                        Cancel
                    </button>
                    <button type="button" onclick="goToAttendanceStep(2)" class="btn-primary text-xs inline-flex items-center gap-1.5">
                        <span>Next: Work Output &amp; Metrics</span>
                        <span>&rarr;</span>
                    </button>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════ --}}
            {{-- STEP 2: WORK PERFORMANCE METRICS & SUMMARY            --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            <div id="att-step-2" class="hidden space-y-4">
                <div id="modal-validation-error-step2" class="hidden p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs"></div>

                {{-- Step 1 Summary Banner Confirmation Card --}}
                <div class="p-3.5 rounded-lg bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/80 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <img id="summary-worker-thumb" src="" alt="Worker" class="w-10 h-10 rounded-full object-cover border-2 border-blue-400 shrink-0 hidden">
                        <div id="summary-worker-avatar-fallback" class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                            WK
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white" id="summary-worker-name">Selected Worker</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400" id="summary-project-details">Project Site &bull; Date</div>
                        </div>
                    </div>
                    <button type="button" onclick="goToAttendanceStep(1)" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                        <span>&larr; Retake Photo / Edit</span>
                    </button>
                </div>

                {{-- Cabling Metres & Cameras Installed --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="att-cabling-metres" class="crm-label text-xs">Cabling Metres Done</label>
                        <input id="att-cabling-metres" type="number" step="0.1" min="0" name="cabling_metres" placeholder="e.g. 75.0" class="crm-input text-xs w-full">
                        <p class="text-[10px] text-slate-400 mt-0.5">Physical cable pulled today (m)</p>
                    </div>

                    <div>
                        <label for="att-cameras-installed" class="crm-label text-xs">Cameras Installed</label>
                        <input id="att-cameras-installed" type="number" min="0" name="cameras_installed" placeholder="e.g. 4" class="crm-input text-xs w-full">
                        <p class="text-[10px] text-slate-400 mt-0.5">Units mounted/configured today</p>
                    </div>
                </div>

                {{-- Extra Allowance & Reason --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="att-extra-amount" class="crm-label text-xs">Extra Allowance (₹)</label>
                        <input id="att-extra-amount" type="number" step="any" min="0" name="extra_amount" placeholder="e.g. 150" class="crm-input text-xs w-full">
                    </div>
                    <div>
                        <label for="att-extra-description" class="crm-label text-xs">Extra Reason</label>
                        <input id="att-extra-description" type="text" name="extra_description" placeholder="e.g. Night shift / Travel allowance" class="crm-input text-xs w-full">
                    </div>
                </div>

                {{-- Work Notes --}}
                <div>
                    <label for="att-notes" class="crm-label text-xs">Work Notes / Summary</label>
                    <textarea id="att-notes" name="notes" rows="2" placeholder="Summary of work performed, site conditions, testing details..." class="crm-input text-xs w-full"></textarea>
                </div>

                {{-- Step 2 Actions --}}
                <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" onclick="goToAttendanceStep(1)" class="btn-secondary text-xs inline-flex items-center gap-1">
                        <span>&larr; Back to Step 1</span>
                    </button>
                    <button type="submit" id="btn-save-attendance" class="btn-primary text-xs inline-flex items-center gap-1.5 font-semibold">
                        <span>✓ Save Attendance</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    let globalWebcamStream = null;

    function openAttendanceModal(projectIdOrDate, workerId, dateParam) {
        let selectedProjId = null;
        let selectedWorkerId = null;
        let selectedDateVal = null;

        if (projectIdOrDate) {
            // Check if argument is a date string (YYYY-MM-DD) or numeric ID
            if (typeof projectIdOrDate === 'string' && projectIdOrDate.includes('-')) {
                selectedDateVal = projectIdOrDate;
            } else {
                selectedProjId = projectIdOrDate;
            }
        }
        if (workerId) selectedWorkerId = workerId;
        if (dateParam) selectedDateVal = dateParam;

        if (selectedProjId) {
            const projSelect = document.getElementById('att-project-id');
            if (projSelect) projSelect.value = selectedProjId;
        }

        if (selectedWorkerId) {
            const workerSelect = document.getElementById('modal-worker-select');
            if (workerSelect) {
                workerSelect.value = selectedWorkerId;
                onWorkerSelectChange(workerSelect);
            }
        }

        if (selectedDateVal) {
            const dateInput = document.getElementById('modal-work-date');
            if (dateInput) dateInput.value = selectedDateVal;
        }

        goToAttendanceStep(1);
        document.getElementById('markAttendanceModal').classList.remove('hidden');
    }

    function closeAttendanceModal() {
        stopWebcam();
        document.getElementById('markAttendanceModal').classList.add('hidden');
    }

    function goToAttendanceStep(step) {
        const step1 = document.getElementById('att-step-1');
        const step2 = document.getElementById('att-step-2');
        const err1 = document.getElementById('modal-validation-error-step1');
        const err2 = document.getElementById('modal-validation-error-step2');

        const pill1 = document.getElementById('step-pill-1');
        const pill2 = document.getElementById('step-pill-2');
        const badge1 = document.getElementById('step-badge-1');
        const badge2 = document.getElementById('step-badge-2');
        const line = document.getElementById('step-connector-line');

        if (step === 2) {
            const workerSelect = document.getElementById('modal-worker-select');
            const projectSelect = document.getElementById('att-project-id');

            if (!workerSelect || !workerSelect.value) {
                if (err1) {
                    err1.textContent = 'Please select a Worker before proceeding to Step 2.';
                    err1.classList.remove('hidden');
                }
                if (workerSelect) workerSelect.focus();
                return;
            }

            if (!projectSelect || !projectSelect.value) {
                if (err1) {
                    err1.textContent = 'Please select a Project Site before proceeding to Step 2.';
                    err1.classList.remove('hidden');
                }
                if (projectSelect) projectSelect.focus();
                return;
            }

            if (err1) err1.classList.add('hidden');

            // Populate Step 2 Confirmation Card
            const workerOpt = workerSelect.options[workerSelect.selectedIndex];
            const projOpt = projectSelect.options[projectSelect.selectedIndex];
            const dateVal = document.getElementById('modal-work-date').value;
            const shiftType = document.getElementById('att-attendance-type').value === 'half' ? 'Half Day (0.5x)' : 'Full Day (1.0x)';
            const photoData = document.getElementById('webcam-photo-data').value;
            const profilePhoto = workerOpt.getAttribute('data-photo');

            document.getElementById('summary-worker-name').textContent = workerOpt.getAttribute('data-name') || workerOpt.text;
            document.getElementById('summary-project-details').textContent = `${projOpt.text.trim()} • ${dateVal} • ${shiftType}`;

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
                fallback.textContent = (workerOpt.getAttribute('data-name') || 'WK').substring(0, 2).toUpperCase();
            }

            // Transition Panes
            step1.classList.add('hidden');
            step2.classList.remove('hidden');

            // Update Stepper Visuals
            badge1.innerHTML = '&#10003;';
            badge1.className = 'w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs bg-emerald-600 text-white shadow-xs';
            line.className = 'h-0.5 w-full bg-blue-600 dark:bg-blue-500 transition-colors';
            pill2.className = 'flex items-center gap-2';
            badge2.className = 'w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs bg-blue-600 text-white shadow-xs';
        } else {
            // Step 1
            if (err2) err2.classList.add('hidden');
            step2.classList.add('hidden');
            step1.classList.remove('hidden');

            badge1.innerHTML = '1';
            badge1.className = 'w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs bg-blue-600 text-white shadow-xs';
            line.className = 'h-0.5 w-full bg-slate-200 dark:bg-slate-700 transition-colors';
            pill2.className = 'flex items-center gap-2 opacity-60';
            badge2.className = 'w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300';
        }
    }

    function onWorkerSelectChange(sel) {
        const opt = sel.options[sel.selectedIndex];
        const previewBox = document.getElementById('modal-worker-preview-box');
        const img = document.getElementById('modal-worker-img');
        const namePreview = document.getElementById('modal-worker-name-preview');

        if (opt && opt.value) {
            const photo = opt.getAttribute('data-photo');
            const name = opt.getAttribute('data-name');
            const rate = opt.getAttribute('data-rate');
            if (namePreview) namePreview.textContent = `${name} (₹${rate}/day)`;
            if (photo && img) {
                img.src = photo;
                img.classList.remove('hidden');
            } else if (img) {
                img.classList.add('hidden');
            }
            if (previewBox) previewBox.classList.remove('hidden');
        } else {
            if (previewBox) previewBox.classList.add('hidden');
        }
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
            if (status) status.innerHTML = '<span class="text-emerald-600 dark:text-emerald-400 font-semibold">🟢 Camera active</span> - Frame face & snap photo';
            if (startBtn) startBtn.classList.add('hidden');
            if (snapBtn) snapBtn.classList.remove('hidden');
        } catch (err) {
            if (status) status.innerHTML = '<span class="text-rose-500 font-semibold">⚠️ Camera unavailable:</span> ' + (err.message || 'Permission denied');
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
        if (status) status.innerHTML = '<span class="text-emerald-600 dark:text-emerald-400 font-semibold">✓ Selfie captured!</span> Ready to proceed';

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
            if (status) status.textContent = 'Click Start Camera to take selfie';
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
        const workerSelect = document.getElementById('modal-worker-select');
        const projectSelect = document.getElementById('att-project-id');
        const errorBox = document.getElementById('modal-validation-error-step2');
        const saveBtn = document.getElementById('btn-save-attendance');

        if (!workerSelect || !workerSelect.value || !projectSelect || !projectSelect.value) {
            goToAttendanceStep(1);
            return false;
        }

        if (errorBox) errorBox.classList.add('hidden');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="inline-block animate-spin mr-1.5">&#9696;</span> Saving Attendance...';
        }
        return true;
    }
</script>

