<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Mobile Barcode & Asset Scanner | PathSoft CCTV</title>

    <!-- Tailwind Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        @keyframes laserSweep {
            0% { top: 10%; opacity: 0.3; }
            50% { top: 85%; opacity: 1; }
            100% { top: 10%; opacity: 0.3; }
        }
        .laser-sweep-line {
            animation: laserSweep 1.8s infinite ease-in-out;
        }
    </style>
</head>
<body class="h-full flex flex-col bg-slate-950 text-white overflow-x-hidden select-none">

    <!-- Top App Bar -->
    <header class="bg-slate-900/90 backdrop-blur-md border-b border-slate-800 px-4 py-3 sticky top-0 z-40 flex items-center justify-between">
        <div class="flex items-center space-x-2.5">
            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold shadow-md shadow-blue-500/30">
                📱
            </div>
            <div>
                <h1 class="text-xs font-bold tracking-tight text-white flex items-center gap-1.5">
                    <span>Mobile Barcode Scanner</span>
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="Online"></span>
                </h1>
                <p class="text-[10px] text-slate-400 font-mono truncate max-w-[170px]">
                    Session: <span class="text-blue-400 font-bold">{{ $token }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-1.5">
            <span id="connectionBadge" class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-950/80 text-emerald-400 border border-emerald-800/60 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Laptop Linked</span>
            </span>
        </div>
    </header>

    <!-- Main Scanner Interface -->
    <main class="flex-grow flex flex-col p-3 max-w-md mx-auto w-full">

        <!-- Target Field Notification -->
        <div class="mb-3 px-3 py-2 rounded-xl bg-blue-950/40 border border-blue-800/40 flex items-center justify-between">
            <div class="flex items-center space-x-2 text-xs">
                <span class="text-blue-400">🎯 Scanning for:</span>
                <span class="font-bold text-white bg-blue-900/50 px-2 py-0.5 rounded text-[11px]">{{ $label }}</span>
            </div>
            <span class="text-[10px] text-slate-400">Instant Sync</span>
        </div>

        <!-- Camera Viewport Card -->
        <div class="relative w-full aspect-[4/3] rounded-2xl bg-black overflow-hidden border border-slate-800 shadow-2xl flex items-center justify-center">
            
            <!-- Live HTML5 Video Feed -->
            <video id="scannerVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
            <canvas id="scannerCanvas" class="hidden"></canvas>

            <!-- Holographic Reticle Overlay -->
            <div class="absolute inset-0 pointer-events-none flex items-center justify-center p-6">
                <div class="relative w-full max-w-[260px] h-[150px] rounded-xl border border-blue-500/40 shadow-[0_0_0_9999px_rgba(3,7,18,0.65)] flex flex-col justify-between p-2">
                    
                    <!-- Top Corners -->
                    <div class="flex justify-between w-full">
                        <div class="w-5 h-5 border-t-2 border-l-2 border-cyan-400 rounded-tl-lg"></div>
                        <div class="w-5 h-5 border-t-2 border-r-2 border-cyan-400 rounded-tr-lg"></div>
                    </div>

                    <!-- Scanning Laser -->
                    <div class="laser-sweep-line absolute left-2 right-2 h-0.5 bg-gradient-to-r from-transparent via-cyan-400 to-transparent shadow-[0_0_12px_#22d3ee]"></div>

                    <!-- Bottom Corners -->
                    <div class="flex justify-between w-full">
                        <div class="w-5 h-5 border-b-2 border-l-2 border-cyan-400 rounded-bl-lg"></div>
                        <div class="w-5 h-5 border-b-2 border-r-2 border-cyan-400 rounded-br-lg"></div>
                    </div>
                </div>
            </div>

            <!-- Live Status Ribbon in Camera -->
            <div id="scannerStatusPill" class="absolute bottom-3 left-3 right-3 text-center text-xs font-semibold px-3 py-1.5 rounded-full bg-slate-900/80 backdrop-blur-sm border border-slate-700/60 text-slate-200">
                Point at CCTV Barcode / S/N sticker...
            </div>

            <!-- Flash Torch Overlay Button (floating in top right of camera) -->
            <button type="button" id="torchBtn" onclick="toggleMobileTorch()"
                class="absolute top-3 right-3 w-10 h-10 rounded-full bg-slate-900/70 border border-slate-700/60 text-white flex items-center justify-center shadow-lg active:scale-95 transition-all">
                💡
            </button>

            <!-- Flip Camera Overlay Button (floating in top left of camera) -->
            <button type="button" id="mobileFlipBtn" onclick="switchMobileCamera()"
                class="absolute top-3 left-3 w-10 h-10 rounded-full bg-slate-900/70 border border-slate-700/60 text-white flex items-center justify-center shadow-lg active:scale-95 transition-all">
                🔄
            </button>
        </div>

        <!-- Success Toast Notification on Scan -->
        <div id="successToast" class="hidden my-3 p-3.5 rounded-xl bg-emerald-950/90 border border-emerald-600/80 text-emerald-200 shadow-xl flex items-center justify-between animate-bounce">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow">
                    ✓
                </div>
                <div>
                    <div class="text-xs font-bold text-white">Transmitted to Laptop!</div>
                    <div class="text-xs font-mono text-emerald-300 font-bold" id="scannedCodeDisplay">---</div>
                </div>
            </div>
            <button type="button" onclick="resumeScanning()" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-600 text-white text-[11px] font-bold rounded-lg shadow">
                Next
            </button>
        </div>

        <!-- Camera Control Bar -->
        <div class="grid grid-cols-2 gap-2 mt-3">
            <!-- Snap Photo Fallback -->
            <label class="flex items-center justify-center space-x-2 min-h-[44px] py-2.5 px-3 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-semibold border border-slate-700/80 cursor-pointer shadow-md active:scale-98 transition-all">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Snap / Upload Photo</span>
                <input type="file" accept="image/*" onchange="handleMobileImageUpload(event)" class="hidden">
            </label>

            <!-- Manual Entry / Type S/N Button -->
            <button type="button" onclick="openManualEntryPrompt()"
                class="flex items-center justify-center space-x-2 min-h-[44px] py-2.5 px-3 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-semibold border border-slate-700/80 shadow-md active:scale-98 transition-all">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Type S/N Manually</span>
            </button>
        </div>

        <!-- Recent Scans on this session -->
        <div class="mt-4 pt-3 border-t border-slate-800/80 flex-grow">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Recent Scans This Session</h3>
                <span class="text-[10px] text-slate-500 font-mono" id="scanCountBadge">0 items</span>
            </div>

            <div id="recentScansList" class="space-y-2 max-h-40 overflow-y-auto pr-1">
                <div id="emptyScansNotice" class="text-center py-4 text-xs text-slate-500">
                    No barcodes scanned yet. Center barcode inside the box above.
                </div>
            </div>
        </div>

    </main>

    <!-- Bottom Instructions Footer -->
    <footer class="bg-slate-900/60 border-t border-slate-800/80 px-4 py-2.5 text-center text-[10px] text-slate-500">
        Connected to CCTV CRM ERP • Scanning automatically fills your laptop screen.
    </footer>

    <!-- Interactive Script for Camera & Sync -->
    <script>
        const SESSION_TOKEN = @json($token);
        const TARGET_NAME = @json($target);
        let currentStream = null;
        let videoTrack = null;
        let isTorchOn = false;
        let currentFacingMode = 'environment';
        let isScanningActive = true;
        let animationFrameId = null;
        let recentScans = [];
        let cachedMobileVideoDevices = [];
        let currentMobileDeviceIndex = 0;
        let isSwitchingCamera = false;

        async function getMobileVideoDevices() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return [];
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                return devices.filter(d => d.kind === 'videoinput');
            } catch (e) {
                return [];
            }
        }

        // Initialize camera on page load
        document.addEventListener('DOMContentLoaded', () => {
            initMobileCamera();
            sendHeartbeat();
            setInterval(sendHeartbeat, 15000); // Heartbeat every 15s
        });

        async function initMobileCamera(preferDeviceId = null) {
            const video = document.getElementById('scannerVideo');
            const status = document.getElementById('scannerStatusPill');
            const flipBtn = document.getElementById('mobileFlipBtn');

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (status) status.innerText = 'Live camera not supported. Use "Snap / Upload Photo" below.';
                return;
            }

            try {
                if (animationFrameId) {
                    cancelAnimationFrame(animationFrameId);
                    animationFrameId = null;
                }
                if (currentStream) {
                    currentStream.getTracks().forEach(t => t.stop());
                    currentStream = null;
                    videoTrack = null;
                }
                if (video) {
                    video.srcObject = null;
                }

                // Allow OS camera driver to release hardware lock
                await new Promise(r => setTimeout(r, 80));

                let constraints;
                if (preferDeviceId) {
                    constraints = {
                        video: {
                            deviceId: { exact: preferDeviceId },
                            width: { ideal: 1920 },
                            height: { ideal: 1080 }
                        }
                    };
                } else {
                    constraints = {
                        video: {
                            facingMode: { ideal: currentFacingMode },
                            width: { ideal: 1920 },
                            height: { ideal: 1080 }
                        }
                    };
                }

                let stream;
                try {
                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                } catch (strictErr) {
                    console.warn('Strict constraints failed, attempting fallback...', strictErr);
                    if (preferDeviceId) {
                        try {
                            stream = await navigator.mediaDevices.getUserMedia({
                                video: { deviceId: preferDeviceId }
                            });
                        } catch (e1) {
                            stream = await navigator.mediaDevices.getUserMedia({
                                video: { facingMode: { ideal: currentFacingMode } }
                            });
                        }
                    } else {
                        try {
                            stream = await navigator.mediaDevices.getUserMedia({
                                video: { facingMode: { ideal: currentFacingMode } }
                            });
                        } catch (e2) {
                            stream = await navigator.mediaDevices.getUserMedia({ video: true });
                        }
                    }
                }

                currentStream = stream;
                videoTrack = stream.getVideoTracks()[0];
                video.srcObject = stream;
                await video.play();

                const devices = await getMobileVideoDevices();
                cachedMobileVideoDevices = devices;
                if (videoTrack) {
                    const settings = videoTrack.getSettings ? videoTrack.getSettings() : {};
                    if (settings.deviceId) {
                        const idx = devices.findIndex(d => d.deviceId === settings.deviceId);
                        if (idx !== -1) currentMobileDeviceIndex = idx;
                    }
                }

                const label = (videoTrack && videoTrack.label) ? videoTrack.label : (currentFacingMode === 'user' ? 'Front Camera' : 'Back Camera');
                if (status) status.innerText = `Active: ${label.substring(0, 30)}... Point at barcode`;
                isScanningActive = true;
                startScanLoop();
            } catch (err) {
                console.warn('Camera stream error:', err);
                if (status) status.innerText = 'Camera access blocked. Tap "Snap / Upload Photo".';
            } finally {
                isSwitchingCamera = false;
                if (flipBtn) {
                    flipBtn.disabled = false;
                    flipBtn.style.opacity = '1';
                }
            }
        }

        async function startScanLoop() {
            if (!isScanningActive) return;
            const video = document.getElementById('scannerVideo');

            if (video && video.readyState === video.HAVE_ENOUGH_DATA) {
                if ('BarcodeDetector' in window) {
                    try {
                        const detector = new BarcodeDetector({
                            formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'qr_code', 'upc_a', 'upc_e', 'data_matrix']
                        });
                        const barcodes = await detector.detect(video);
                        if (barcodes.length > 0 && barcodes[0].rawValue) {
                            onBarcodeScanned(barcodes[0].rawValue);
                            return;
                        }
                    } catch (e) {}
                }
            }

            animationFrameId = requestAnimationFrame(startScanLoop);
        }

        async function onBarcodeScanned(rawValue) {
            const cleanCode = rawValue.trim();
            if (!cleanCode) return;

            isScanningActive = false;
            if (animationFrameId) cancelAnimationFrame(animationFrameId);

            // Play audio beep & vibration
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(950, ctx.currentTime);
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.12);
            } catch (e) {}

            if (navigator.vibrate) {
                navigator.vibrate([120, 60, 120]);
            }

            // Show UI confirmation
            document.getElementById('scannedCodeDisplay').innerText = cleanCode;
            document.getElementById('successToast').classList.remove('hidden');

            // Push to server
            await pushCodeToLaptop(cleanCode);

            // Add to recent list
            addToRecentScans(cleanCode);
        }

        async function pushCodeToLaptop(code) {
            try {
                const response = await fetch('/api/mobile-scanner/push', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        token: SESSION_TOKEN,
                        code: code,
                        label: TARGET_NAME
                    })
                });
                const res = await response.json();
                console.log('Push response:', res);
            } catch (err) {
                console.error('Failed to push code:', err);
            }
        }

        function resumeScanning() {
            document.getElementById('successToast').classList.add('hidden');
            isScanningActive = true;
            startScanLoop();
        }

        function addToRecentScans(code) {
            if (!recentScans.includes(code)) {
                recentScans.unshift(code);
                if (recentScans.length > 5) recentScans.pop();
            }

            const list = document.getElementById('recentScansList');
            const notice = document.getElementById('emptyScansNotice');
            const badge = document.getElementById('scanCountBadge');
            if (notice) notice.remove();

            if (badge) badge.innerText = `${recentScans.length} item(s)`;

            list.innerHTML = recentScans.map(item => `
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-900 border border-slate-800">
                    <div class="flex items-center space-x-2">
                        <span class="text-blue-400 font-mono text-xs font-bold">${escapeHtml(item)}</span>
                    </div>
                    <button type="button" onclick="pushCodeToLaptop('${escapeHtml(item)}'); alert('Re-sent to laptop!');"
                        class="px-2.5 py-1 rounded bg-blue-600/30 hover:bg-blue-600 text-blue-300 hover:text-white text-[10px] font-bold transition-colors">
                        Re-Send
                    </button>
                </div>
            `).join('');
        }

        function escapeHtml(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // Snap / Upload Photo handler
        async function handleMobileImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            event.target.value = '';

            const status = document.getElementById('scannerStatusPill');
            if (status) status.innerText = 'Analyzing photo for barcode...';

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
                            onBarcodeScanned(barcodes[0].rawValue);
                            return;
                        }
                    } catch (e) {}
                }

                // If detector can't read directly, prompt technician
                const manual = prompt('Could not auto-read barcode. Enter code from photo:', file.name.replace(/\.[^/.]+$/, ''));
                if (manual) {
                    onBarcodeScanned(manual);
                } else if (status) {
                    status.innerText = 'Unable to decode. Please try another angle.';
                }
            };
        }

        // Manual Entry
        function openManualEntryPrompt() {
            const manual = prompt('Enter Hardware Serial Number or MAC Address manually:');
            if (manual && manual.trim()) {
                onBarcodeScanned(manual.trim());
            }
        }

        // Flash Torch
        function toggleMobileTorch() {
            if (videoTrack && videoTrack.applyConstraints) {
                isTorchOn = !isTorchOn;
                videoTrack.applyConstraints({
                    advanced: [{ torch: isTorchOn }]
                }).then(() => {
                    const btn = document.getElementById('torchBtn');
                    if (btn) btn.style.background = isTorchOn ? '#eab308' : 'rgba(15, 23, 42, 0.7)';
                }).catch(() => {
                    alert('Flashlight torch not supported on this device/browser.');
                });
            } else {
                alert('Torch control unavailable on this camera stream.');
            }
        }

        // Flip Camera
        async function switchMobileCamera() {
            if (isSwitchingCamera) return;
            isSwitchingCamera = true;

            const flipBtn = document.getElementById('mobileFlipBtn');
            const status = document.getElementById('scannerStatusPill');
            if (flipBtn) {
                flipBtn.disabled = true;
                flipBtn.style.opacity = '0.5';
            }
            if (status) status.innerText = '🔄 Switching camera...';

            let devices = await getMobileVideoDevices();
            if (!devices || devices.length === 0) {
                devices = cachedMobileVideoDevices || [];
            } else {
                cachedMobileVideoDevices = devices;
            }

            if (devices.length > 1) {
                currentMobileDeviceIndex = (currentMobileDeviceIndex + 1) % devices.length;
                const nextDevice = devices[currentMobileDeviceIndex];
                currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
                await initMobileCamera(nextDevice.deviceId);
            } else {
                currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
                if (devices.length === 1 && status) {
                    status.innerText = '🔄 Toggling camera mode... (1 physical camera detected)';
                }
                await initMobileCamera(null);
            }
        }

        // Heartbeat
        async function sendHeartbeat() {
            try {
                await fetch(`/api/mobile-scanner/heartbeat/${SESSION_TOKEN}`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });
            } catch (e) {}
        }
    </script>
</body>
</html>
