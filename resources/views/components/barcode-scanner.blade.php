{{-- Reusable Dual Laptop & Mobile Phone Remote Barcode Scanner Component --}}
@props([
    'modalId' => 'barcode-scanner-modal',
    'targetInputId' => null,
    'title' => null,
])
@php
    $title ??= '📸 ' . __('CCTV Hardware & Barcode Scanner');
@endphp

@php
    $lanIp = gethostbyname(gethostname());
    if ($lanIp === '127.0.0.1' || empty($lanIp)) {
        $lanIp = '192.168.1.34'; // Detected active WiFi IP
    }
@endphp

<div id="{{ $modalId }}"
     style="display:none;"
     class="barcode-modal-wrapper fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
     data-lan-ip="{{ $lanIp }}">
    
    <div class="crm-card bg-white dark:bg-slate-900 rounded-2xl max-w-xl w-full p-4 sm:p-6 shadow-2xl border border-slate-200 dark:border-slate-800 my-auto transition-all flex flex-col space-y-4">
        
        {{-- Modal Top Bar --}}
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Scan Barcode, Serial Number, or MAC Address tag') }}</p>
                </div>
            </div>
            <x-close-button onclick="closeBarcodeScanner('{{ $modalId }}')" :label="__('Close')" />
        </div>

        {{-- Camera Viewport Container --}}
        <div class="relative bg-black rounded-2xl overflow-hidden w-full h-[260px] sm:h-[300px] flex items-center justify-center border border-slate-200 dark:border-slate-800 shadow-inner">
            <video id="{{ $modalId }}-video" autoplay playsinline muted class="w-full h-full object-cover"></video>
            <canvas id="{{ $modalId }}-canvas" style="display:none;"></canvas>

            {{-- Scanning Reticle & Aim Animation --}}
            <div class="absolute w-[240px] h-[160px] border-2 border-dashed border-blue-400/80 rounded-2xl pointer-events-none flex flex-col justify-between p-2 box-border shadow-[0_0_0_4000px_rgba(0,0,0,0.55)]">
                <div class="flex justify-between">
                    <div class="w-4 h-4 border-t-3 border-l-3 border-blue-500 rounded-tl-lg"></div>
                    <div class="w-4 h-4 border-t-3 border-r-3 border-blue-500 rounded-tr-lg"></div>
                </div>
                <div class="laser-scanner-line w-full h-0.5 bg-red-500 shadow-[0_0_12px_#ef4444] animate-pulse"></div>
                <div class="flex justify-between">
                    <div class="w-4 h-4 border-b-3 border-l-3 border-blue-500 rounded-bl-lg"></div>
                    <div class="w-4 h-4 border-b-3 border-r-3 border-blue-500 rounded-br-lg"></div>
                </div>
            </div>

            {{-- Camera Status Overlay Pill --}}
            <div id="{{ $modalId }}-status"
                 class="absolute bottom-3 inset-x-3 text-center bg-slate-900/85 backdrop-blur text-white text-xs font-semibold py-1.5 px-3 rounded-full border border-white/10 shadow-sm mx-auto max-w-xs">
                {{ __('Initializing camera...') }}
            </div>
        </div>

        {{-- Camera Controls & Photo Upload --}}
        <div class="flex items-center justify-between flex-wrap gap-2 pt-1">
            <div class="flex items-center gap-2">
                <button type="button" id="{{ $modalId }}-torch-btn" onclick="toggleTorch('{{ $modalId }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.516 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
                    <span>{{ __('Torch') }}</span>
                </button>
                <button type="button" id="{{ $modalId }}-flip-btn" onclick="switchCamera('{{ $modalId }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    <span>{{ __('Flip Cam') }}</span>
                </button>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="snapBarcodeCurrentFrame('{{ $modalId }}')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                    <span>{{ __('Snap Frame') }}</span>
                </button>
                <label class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 !text-white text-xs font-bold shadow-sm transition cursor-pointer"
                       style="color: #ffffff !important;">
                    <svg class="w-3.5 h-3.5 !text-white" fill="none" stroke="#ffffff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('Upload Image') }}</span>
                    <input type="file" accept="image/*" onchange="handleBarcodeImageUpload(event, '{{ $modalId }}')" style="display:none;">
                </label>
            </div>
        </div>

        {{-- Direct Manual Serial Input Row inside Modal --}}
        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 space-y-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block">
                {{ __('Or Enter Serial Number Manually') }}
            </label>
            <div class="flex items-center gap-2">
                <input type="text" id="{{ $modalId }}-manual-input"
                       placeholder="{{ __('Type or paste serial barcode (e.g. SN-98213)...') }}"
                       onkeydown="if(event.key === 'Enter') { event.preventDefault(); const val = this.value.trim(); if(val) { handleBarcodeDetected('{{ $modalId }}', val); closeBarcodeScanner('{{ $modalId }}'); } }"
                       class="flex-1 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500">
                <button type="button"
                        onclick="const val = document.getElementById('{{ $modalId }}-manual-input').value.trim(); if(val) { handleBarcodeDetected('{{ $modalId }}', val); closeBarcodeScanner('{{ $modalId }}'); }"
                        class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white text-xs sm:text-sm font-bold shadow-sm transition cursor-pointer shrink-0"
                        style="color: #ffffff !important;">
                    <span style="color: #ffffff !important;">{{ __('Lookup') }}</span>
                </button>
            </div>
        </div>

        {{-- Modal Action Footer --}}
        <div class="pt-2 flex items-center justify-end">
            <button type="button" onclick="closeBarcodeScanner('{{ $modalId }}')"
                    class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition cursor-pointer">
                {{ __('Cancel') }}
            </button>
        </div>

    </div>
</div>

<style>
@keyframes scanLaser {
    0% { transform: translateY(-55px); opacity: 0.3; }
    50% { transform: translateY(55px); opacity: 1; }
    100% { transform: translateY(-55px); opacity: 0.3; }
}
</style>

<script>
window.barcodeScannerText = {{ Js::from([
    'notSupported' => __('Camera not supported. Please use Chrome on Android or Safari on iOS.'),
    'mobileCamera' => __('Mobile Rear Camera'),
    'laptopCamera' => __('Laptop Integrated Camera'),
    'active' => __('Active: :camera... Hold steady over barcode.'),
    'blockedOrUnavailable' => __('⚠️ Camera blocked or unavailable. Click "📁 Upload Image" or check permissions.'),
    'blocked' => __('🚫 Camera blocked! Click 🔒 in your address bar and set Camera to "Allow", then click Retry.'),
    'inUseShort' => __('⚠️ Camera in use by another app (Zoom/Teams). Please close it and click Retry.'),
    'noCamera' => __('⚠️ No camera hardware found on this computer. Use "📁 Upload Image".'),
    'permissionBlocked' => __("🚫 Camera Permission is Blocked in your browser!\n\nTo allow it:\n1. Look at the address bar at the top (where http://127.0.0.1:8000 is written).\n2. Click the Lock / Tune icon (🔒 or 🎚️) on the left of the URL.\n3. Change \"Camera\" permission from \"Block\" to \"Allow\".\n4. Re-open this scanner or reload the page.\n\nYou can also click \"📁 Upload Image\" below to choose a photo directly from your computer."),
    'inUse' => __("⚠️ Camera is currently in use by another application (Zoom, Teams, Skype, etc.).\n\nPlease close the other app and click \"🔄 Flip Cam\" to retry, or use \"📁 Upload Image\"."),
    'notConnected' => __("⚠️ Camera stream is not connected.\n\nPlease click \"📁 Upload Image\" to scan from a photo file, or ensure camera permissions are allowed in your browser."),
    'waitingFrame' => __('⏳ Waiting for camera frame...'),
    'snapping' => __('⚡ Snapping frame and scanning barcode...'),
    'manualSnap' => __('Could not auto-read barcode from current snap. Enter S/N manually or try again:'),
    'holdCloser' => __('Hold barcode closer and center in red line, then tap Snap again.'),
    'processingUpload' => __('Processing uploaded image...'),
    'manualPhoto' => __('Could not auto-read barcode from photo. Enter S/N manually:'),
    'unreadableImage' => __('Could not read barcode from image. Please try again or use Mobile Scan.'),
    'noTorch' => __('Torch / Flashlight is not supported on this webcam/device.'),
    'torchUnavailable' => __('Torch control unavailable on this camera stream.'),
    'switching' => __('🔄 Switching camera...'),
    'toggling' => __('🔄 Toggling camera mode... (1 physical camera detected)'),
]) }};
window.barcodeScannerState = window.barcodeScannerState || {};

function isMobileScannerDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
           (window.innerWidth <= 768 && ('ontouchstart' in window));
}

async function getBarcodeVideoDevices() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return [];
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        return devices.filter(d => d.kind === 'videoinput');
    } catch (e) {
        return [];
    }
}

function openBarcodeScanner(modalId, targetInputId = null, onDetectedCallback = null, defaultTab = 'laptop') {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.style.display = 'flex';

    window.barcodeScannerState[modalId] = {
        targetInputId: targetInputId,
        callback: onDetectedCallback,
        stream: null,
        animFrame: null,
        currentFacingMode: isMobileScannerDevice() ? 'environment' : 'user',
        videoDevices: [],
        currentDeviceIndex: 0,
        isSwitching: false,
        torchOn: false,
        track: null,
        activeTab: 'laptop',
        pollInterval: null
    };

    // Auto-start camera immediately (works on desktop & mobile browser)
    startBarcodeCamera(modalId);
}

function closeBarcodeScanner(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.style.display = 'none';

    const state = window.barcodeScannerState[modalId];
    if (state) {
        if (state.animFrame) {
            cancelAnimationFrame(state.animFrame);
            state.animFrame = null;
        }
        if (state.stream) {
            state.stream.getTracks().forEach(t => t.stop());
            state.stream = null;
        }
        state.track = null;
        const video = document.getElementById(`${modalId}-video`);
        if (video) video.srcObject = null;
        if (state.pollInterval) {
            clearInterval(state.pollInterval);
            state.pollInterval = null;
        }
    }
}

function switchScannerTab(modalId, tab) {
    // Legacy function kept for compatibility
    startBarcodeCamera(modalId);
}

async function startBarcodeCamera(modalId, preferDeviceId = null) {
    const state = window.barcodeScannerState[modalId];
    if (!state) return;

    const video = document.getElementById(`${modalId}-video`);
    const status = document.getElementById(`${modalId}-status`);
    const flipBtn = document.getElementById(`${modalId}-flip-btn`);

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        if (status) status.innerText = barcodeScannerText.notSupported;
        return;
    }

    try {
        // Cancel active scanning frame loop
        if (state.animFrame) {
            cancelAnimationFrame(state.animFrame);
            state.animFrame = null;
        }

        // Cleanly terminate active stream and disconnect video element
        if (state.stream) {
            state.stream.getTracks().forEach(t => t.stop());
            state.stream = null;
            state.track = null;
        }
        if (video) {
            video.srcObject = null;
        }

        // Allow camera hardware driver a brief moment (80ms) to release
        await new Promise(r => setTimeout(r, 80));

        let stream = null;
        const isMobile = isMobileScannerDevice();

        // 1. Select constraints based on device type
        let primaryConstraints;
        if (preferDeviceId) {
            primaryConstraints = {
                video: { deviceId: { exact: preferDeviceId }, width: { ideal: 1280 }, height: { ideal: 720 } }
            };
        } else if (isMobile) {
            primaryConstraints = {
                video: {
                    facingMode: { ideal: state.currentFacingMode || 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };
        } else {
            // Laptop / Desktop PC: Avoid restrictive facingMode to prevent OverconstrainedError on webcams
            primaryConstraints = {
                video: { width: { ideal: 1280 }, height: { ideal: 720 } }
            };
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia(primaryConstraints);
        } catch (strictErr) {
            console.warn('Primary camera constraints failed, attempting basic { video: true }...', strictErr);
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
            } catch (fallbackErr) {
                throw fallbackErr;
            }
        }

        state.stream = stream;
        state.track = stream.getVideoTracks()[0];
        state.lastError = null;
        video.srcObject = stream;

        // Ensure video is actively decoding frames
        await new Promise((resolve) => {
            if (video.videoWidth > 0 && video.readyState >= 2) {
                resolve();
            } else {
                const onReady = () => {
                    video.removeEventListener('loadeddata', onReady);
                    video.removeEventListener('canplay', onReady);
                    resolve();
                };
                video.addEventListener('loadeddata', onReady);
                video.addEventListener('canplay', onReady);
                setTimeout(resolve, 800);
            }
        });

        try {
            await video.play();
        } catch (playErr) {
            console.warn('Video play error:', playErr);
        }

        // Enumerate devices once stream permission is granted
        const devices = await getBarcodeVideoDevices();
        state.videoDevices = devices;
        if (state.track) {
            const currentSettings = state.track.getSettings ? state.track.getSettings() : {};
            const currentId = currentSettings.deviceId;
            if (currentId) {
                const foundIdx = devices.findIndex(d => d.deviceId === currentId);
                if (foundIdx !== -1) {
                    state.currentDeviceIndex = foundIdx;
                }
            }
        }

        const label = (state.track && state.track.label)
            ? state.track.label
            : (isMobile ? barcodeScannerText.mobileCamera : barcodeScannerText.laptopCamera);

        if (status) {
            status.innerHTML = `<span style="color:#4ade80;">●</span> ${barcodeScannerText.active.replace(':camera', label.substring(0, 32))}`;
        }

        scanBarcodeLoop(modalId);
    } catch (err) {
        console.warn('Camera stream error:', err);
        state.lastError = err.name || 'Error';
        let errorMsg = barcodeScannerText.blockedOrUnavailable;
        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            errorMsg = barcodeScannerText.blocked;
        } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
            errorMsg = barcodeScannerText.inUseShort;
        } else if (err.name === 'NotFoundError') {
            errorMsg = barcodeScannerText.noCamera;
        }
        if (status) {
            status.innerHTML = `<span style="color:#fca5a5;">${errorMsg}</span>`;
        }
    } finally {
        state.isSwitching = false;
        if (flipBtn) {
            flipBtn.disabled = false;
            flipBtn.style.opacity = '1';
        }
    }
}

async function scanBarcodeLoop(modalId) {
    const state = window.barcodeScannerState[modalId];
    const video = document.getElementById(`${modalId}-video`);
    if (!video || !state || !state.stream) return;

    if ('BarcodeDetector' in window) {
        try {
            const detector = new BarcodeDetector({
                formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'qr_code', 'upc_a', 'upc_e', 'data_matrix']
            });
            const barcodes = await detector.detect(video);
            if (barcodes.length > 0 && barcodes[0].rawValue) {
                handleBarcodeDetected(modalId, barcodes[0].rawValue);
                return;
            }
        } catch (e) {}
    }

    state.animFrame = requestAnimationFrame(() => scanBarcodeLoop(modalId));
}

function handleBarcodeDetected(modalId, code) {
    const cleanCode = code.trim();
    const state = window.barcodeScannerState[modalId];

    // Audio beep on detection
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.15);
    } catch (e) {}

    // Haptic feedback
    if (navigator.vibrate) {
        navigator.vibrate([90, 40, 90]);
    }

    // Populate target input on laptop screen
    if (state && state.targetInputId) {
        const input = document.getElementById(state.targetInputId);
        if (input) {
            input.value = cleanCode;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));

            // Add glowing highlight effect to input
            input.style.transition = 'all 0.3s ease';
            input.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.45)';
            input.style.borderColor = '#10b981';
            setTimeout(() => {
                input.style.boxShadow = '';
                input.style.borderColor = '';
            }, 3000);
        }
    }

    // Call callback if specified
    if (state && typeof state.callback === 'function') {
        state.callback(cleanCode);
    } else if (typeof window.onBarcodeScanned === 'function') {
        window.onBarcodeScanned(cleanCode, modalId);
    }

    setTimeout(() => {
        closeBarcodeScanner(modalId);
    }, 600);
}

async function snapBarcodeCurrentFrame(modalId) {
    const state = window.barcodeScannerState[modalId];
    const video = document.getElementById(`${modalId}-video`);
    const status = document.getElementById(`${modalId}-status`);
    const canvas = document.getElementById(`${modalId}-canvas`);

    if (!state || !state.stream) {
        if (state && (state.lastError === 'NotAllowedError' || state.lastError === 'PermissionDeniedError')) {
            crmAlert(barcodeScannerText.permissionBlocked);
        } else if (state && (state.lastError === 'NotReadableError' || state.lastError === 'TrackStartError')) {
            crmAlert(barcodeScannerText.inUse);
        } else {
            crmAlert(barcodeScannerText.notConnected);
        }
        return;
    }

    if (!video || !canvas) return;

    // Gracefully wait up to 500ms if stream is active but first frame is still decoding
    if (video.videoWidth === 0 || video.readyState < 2) {
        if (status) status.innerText = barcodeScannerText.waitingFrame;
        await new Promise(r => setTimeout(r, 400));
    }

    if (status) status.innerText = barcodeScannerText.snapping;

    const width = video.videoWidth || 1280;
    const height = video.videoHeight || 720;
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, width, height);

    if ('BarcodeDetector' in window) {
        try {
            const detector = new BarcodeDetector({
                formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'qr_code', 'upc_a', 'upc_e', 'data_matrix']
            });
            const barcodes = await detector.detect(canvas);
            if (barcodes.length > 0 && barcodes[0].rawValue) {
                handleBarcodeDetected(modalId, barcodes[0].rawValue);
                return;
            }
        } catch (e) {
            console.warn('BarcodeDetector error on snap:', e);
        }
    }

    const manual = prompt(barcodeScannerText.manualSnap);
    if (manual) {
        handleBarcodeDetected(modalId, manual);
    } else if (status) {
        status.innerText = barcodeScannerText.holdCloser;
    }
}

async function handleBarcodeImageUpload(event, modalId) {
    const file = event.target.files[0];
    if (!file) return;
    event.target.value = ''; // Reset so the same file can be selected again if needed

    const status = document.getElementById(`${modalId}-status`);
    if (status) status.innerText = barcodeScannerText.processingUpload;

    const img = new Image();
    img.src = URL.createObjectURL(file);
    img.onload = async () => {
        if ('BarcodeDetector' in window) {
            try {
                const detector = new BarcodeDetector({
                    formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'qr_code', 'upc_a', 'upc_e', 'data_matrix']
                });
                const barcodes = await detector.detect(img);
                if (barcodes.length > 0 && barcodes[0].rawValue) {
                    handleBarcodeDetected(modalId, barcodes[0].rawValue);
                    return;
                }
            } catch (e) {}
        }
        
        const manualCode = prompt(barcodeScannerText.manualPhoto, file.name.replace(/\.[^/.]+$/, ''));
        if (manualCode) {
            handleBarcodeDetected(modalId, manualCode);
        } else if (status) {
            status.innerText = barcodeScannerText.unreadableImage;
        }
    };
}

function toggleTorch(modalId) {
    const state = window.barcodeScannerState[modalId];
    const btn = document.getElementById(`${modalId}-torch-btn`);
    if (state && state.track && state.track.applyConstraints) {
        state.torchOn = !state.torchOn;
        state.track.applyConstraints({
            advanced: [{ torch: state.torchOn }]
        }).then(() => {
            if (btn) {
                btn.style.background = state.torchOn ? '#eab308' : '#1e293b';
                btn.style.color = state.torchOn ? '#0f172a' : '#f8fafc';
                btn.style.borderColor = state.torchOn ? '#ca8a04' : '#334155';
            }
        }).catch(() => {
            state.torchOn = false;
            crmAlert(barcodeScannerText.noTorch);
        });
    } else {
        crmAlert(barcodeScannerText.torchUnavailable);
    }
}

async function switchCamera(modalId) {
    const state = window.barcodeScannerState[modalId];
    if (!state || state.isSwitching) return;
    state.isSwitching = true;

    const status = document.getElementById(`${modalId}-status`);
    const flipBtn = document.getElementById(`${modalId}-flip-btn`);
    if (flipBtn) {
        flipBtn.disabled = true;
        flipBtn.style.opacity = '0.5';
    }
    if (status) status.innerText = barcodeScannerText.switching;

    // Fetch latest devices
    let devices = await getBarcodeVideoDevices();
    if (!devices || devices.length === 0) {
        devices = state.videoDevices || [];
    } else {
        state.videoDevices = devices;
    }

    if (devices.length > 1) {
        // Multi-camera device: cycle to next physical deviceId
        state.currentDeviceIndex = (state.currentDeviceIndex + 1) % devices.length;
        const targetDevice = devices[state.currentDeviceIndex];
        // Toggle logical facing mode flag
        state.currentFacingMode = state.currentFacingMode === 'environment' ? 'user' : 'environment';
        await startBarcodeCamera(modalId, targetDevice.deviceId);
    } else {
        // Single webcam detected or browser hasn't exposed device IDs
        state.currentFacingMode = state.currentFacingMode === 'environment' ? 'user' : 'environment';
        if (devices.length === 1 && status) {
            status.innerText = barcodeScannerText.toggling;
        }
        await startBarcodeCamera(modalId, null);
    }
}
</script>
