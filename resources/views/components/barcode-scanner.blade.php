{{-- Reusable Dual Laptop & Mobile Phone Remote Barcode Scanner Component --}}
@props([
    'modalId' => 'barcode-scanner-modal',
    'targetInputId' => null,
    'title' => '📸 CCTV Hardware & Barcode Scanner',
])

@php
    $lanIp = gethostbyname(gethostname());
    if ($lanIp === '127.0.0.1' || empty($lanIp)) {
        $lanIp = '192.168.1.34'; // Detected active WiFi IP
    }
@endphp

<div id="{{ $modalId }}"
     style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);align-items:center;justify-content:center;padding:1rem;"
     class="barcode-modal-wrapper"
     data-lan-ip="{{ $lanIp }}">
    
    <div style="background:#0f172a;border-radius:1.5rem;border:1px solid #334155;max-width:540px;width:100%;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.6);display:flex;flex-direction:column;">
        
        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #1e293b;background:#1e293b/40;">
            <div style="display:flex;align-items:center;gap:.6rem;">
                <span style="font-size:1.3rem;">🔍</span>
                <div>
                    <h4 style="font-size:.95rem;font-weight:700;color:#fff;margin:0;">{{ $title }}</h4>
                    <p style="font-size:.72rem;color:#94a3b8;margin:2px 0 0;">Scan Barcode, Serial Number, or MAC Address tag</p>
                </div>
            </div>
            <button type="button" onclick="closeBarcodeScanner('{{ $modalId }}')"
                    style="background:none;border:none;color:#94a3b8;font-size:1.4rem;line-height:1;cursor:pointer;padding:.2rem .4rem;border-radius:.4rem;"
                    class="hover:text-white transition-colors">
                ✕
            </button>
        </div>



        {{-- ======================================================== --}}
        {{-- VIEW 1: LAPTOP / LOCAL WEB CAMERA SCANNER                --}}
        {{-- ======================================================== --}}
        <div id="{{ $modalId }}-view-laptop" style="display:block;">
            {{-- Camera Viewport --}}
            <div style="position:relative;background:#000;width:100%;height:310px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                <video id="{{ $modalId }}-video" autoplay playsinline muted
                       style="width:100%;height:100%;object-fit:cover;"></video>
                <canvas id="{{ $modalId }}-canvas" style="display:none;"></canvas>

                {{-- Scanning Reticle & Aim Animation --}}
                <div style="position:absolute;width:240px;height:160px;border:2px dashed #3b82f6;border-radius:12px;box-shadow:0 0 0 4000px rgba(0,0,0,0.5);pointer-events:none;display:flex;flex-direction:column;justify-content:space-between;padding:6px;box-sizing:border-box;">
                    <div style="display:flex;justify-content:space-between;">
                        <div style="width:16px;height:16px;border-top:3px solid #60a5fa;border-left:3px solid #60a5fa;border-top-left-radius:6px;"></div>
                        <div style="width:16px;height:16px;border-top:3px solid #60a5fa;border-right:3px solid #60a5fa;border-top-right-radius:6px;"></div>
                    </div>
                    {{-- Laser Scan Line --}}
                    <div class="laser-scanner-line" style="width:100%;height:2px;background:#ef4444;box-shadow:0 0 10px #ef4444;animation:scanLaser 2s infinite ease-in-out;"></div>
                    <div style="display:flex;justify-content:space-between;">
                        <div style="width:16px;height:16px;border-bottom:3px solid #60a5fa;border-left:3px solid #60a5fa;border-bottom-left-radius:6px;"></div>
                        <div style="width:16px;height:16px;border-bottom:3px solid #60a5fa;border-right:3px solid #60a5fa;border-bottom-right-radius:6px;"></div>
                    </div>
                </div>

                {{-- Status Overlay message --}}
                <div id="{{ $modalId }}-status"
                     style="position:absolute;bottom:12px;left:12px;right:12px;text-align:center;background:rgba(15,23,42,0.85);color:#e2e8f0;font-size:.75rem;font-weight:600;padding:.4rem .75rem;border-radius:999px;border:1px solid rgba(255,255,255,0.15);">
                    Initializing camera...
                </div>
            </div>

            {{-- Laptop Camera Controls & Photo Fallback --}}
            <div style="padding:1rem 1.25rem;background:#0f172a;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;border-top:1px solid #1e293b;">
                <div style="display:flex;gap:.5rem;">
                    <button type="button" id="{{ $modalId }}-torch-btn" onclick="toggleTorch('{{ $modalId }}')"
                            style="padding:.45rem .8rem;background:#1e293b;color:#f8fafc;border:1px solid #334155;border-radius:.6rem;font-size:.75rem;font-weight:700;display:inline-flex;align-items:center;gap:.35rem;cursor:pointer;">
                        💡 Torch
                    </button>
                    <button type="button" id="{{ $modalId }}-flip-btn" onclick="switchCamera('{{ $modalId }}')"
                            style="padding:.45rem .8rem;background:#1e293b;color:#f8fafc;border:1px solid #334155;border-radius:.6rem;font-size:.75rem;font-weight:700;display:inline-flex;align-items:center;gap:.35rem;cursor:pointer;transition:all 0.2s ease;">
                        🔄 Flip Cam
                    </button>
                </div>

                <div style="display:flex;gap:.5rem;align-items:center;">
                    <button type="button" onclick="snapBarcodeCurrentFrame('{{ $modalId }}')"
                            style="padding:.45rem .85rem;background:#059669;color:#fff;border-radius:.6rem;border:none;font-size:.75rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;box-shadow:0 4px 12px rgba(5,150,105,0.3);">
                        📸 Snap
                    </button>
                    <label style="padding:.45rem .85rem;background:#2563eb;color:#fff;border-radius:.6rem;font-size:.75rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;box-shadow:0 4px 12px rgba(37,99,235,0.25);">
                        📁 Upload Image
                        <input type="file" accept="image/*" onchange="handleBarcodeImageUpload(event, '{{ $modalId }}')" style="display:none;">
                    </label>
                </div>
            </div>
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
        if (status) status.innerText = 'Camera not supported. Please use Chrome on Android or Safari on iOS.';
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
            : (isMobile ? 'Mobile Rear Camera' : 'Laptop Integrated Camera');

        if (status) {
            status.innerHTML = `<span style="color:#4ade80;">●</span> Active: ${label.substring(0, 32)}... Hold steady over barcode.`;
        }

        scanBarcodeLoop(modalId);
    } catch (err) {
        console.warn('Camera stream error:', err);
        state.lastError = err.name || 'Error';
        let errorMsg = '⚠️ Camera blocked or unavailable. Click "📁 Upload Image" or check permissions.';
        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            errorMsg = '🚫 Camera blocked! Click 🔒 in your address bar and set Camera to "Allow", then click Retry.';
        } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
            errorMsg = '⚠️ Camera in use by another app (Zoom/Teams). Please close it and click Retry.';
        } else if (err.name === 'NotFoundError') {
            errorMsg = '⚠️ No camera hardware found on this computer. Use "📁 Upload Image".';
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
            alert('🚫 Camera Permission is Blocked in your browser!\n\nTo allow it:\n1. Look at the address bar at the top (where http://127.0.0.1:8000 is written).\n2. Click the Lock / Tune icon (🔒 or 🎚️) on the left of the URL.\n3. Change "Camera" permission from "Block" to "Allow".\n4. Re-open this scanner or reload the page.\n\nYou can also click "📁 Upload Image" below to choose a photo directly from your computer.');
        } else if (state && (state.lastError === 'NotReadableError' || state.lastError === 'TrackStartError')) {
            alert('⚠️ Camera is currently in use by another application (Zoom, Teams, Skype, etc.).\n\nPlease close the other app and click "🔄 Flip Cam" to retry, or use "📁 Upload Image".');
        } else {
            alert('⚠️ Camera stream is not connected.\n\nPlease click "📁 Upload Image" to scan from a photo file, or ensure camera permissions are allowed in your browser.');
        }
        return;
    }

    if (!video || !canvas) return;

    // Gracefully wait up to 500ms if stream is active but first frame is still decoding
    if (video.videoWidth === 0 || video.readyState < 2) {
        if (status) status.innerText = '⏳ Waiting for camera frame...';
        await new Promise(r => setTimeout(r, 400));
    }

    if (status) status.innerText = '⚡ Snapping frame and scanning barcode...';

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

    const manual = prompt('Could not auto-read barcode from current snap. Enter S/N manually or try again:');
    if (manual) {
        handleBarcodeDetected(modalId, manual);
    } else if (status) {
        status.innerText = 'Hold barcode closer and center in red line, then tap Snap again.';
    }
}

async function handleBarcodeImageUpload(event, modalId) {
    const file = event.target.files[0];
    if (!file) return;
    event.target.value = ''; // Reset so the same file can be selected again if needed

    const status = document.getElementById(`${modalId}-status`);
    if (status) status.innerText = 'Processing uploaded image...';

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
        
        const manualCode = prompt('Could not auto-read barcode from photo. Enter S/N manually:', file.name.replace(/\.[^/.]+$/, ''));
        if (manualCode) {
            handleBarcodeDetected(modalId, manualCode);
        } else if (status) {
            status.innerText = 'Could not read barcode from image. Please try again or use Mobile Scan.';
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
            alert('Torch / Flashlight is not supported on this webcam/device.');
        });
    } else {
        alert('Torch control unavailable on this camera stream.');
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
    if (status) status.innerText = '🔄 Switching camera...';

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
            status.innerText = '🔄 Toggling camera mode... (1 physical camera detected)';
        }
        await startBarcodeCamera(modalId, null);
    }
}
</script>
