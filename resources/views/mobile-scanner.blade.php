<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Mobile Barcode & Asset Scanner | Precision CCTV CRM</title>

    <!-- Compiled Tailwind (standalone.css) -->
    @vite(['resources/css/standalone.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <!-- Local Offline Barcode Decoding Engine (ZXing MultiFormat) for Mobile Web -->
    <script src="{{ asset('vendor/zxing/zxing.min.js') }}"></script>

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
            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold shadow-md shadow-blue-500/30 text-base">
                📱
            </div>
            <div>
                <h1 class="text-xs font-bold tracking-tight text-white flex items-center gap-1.5">
                    <span>Mobile Barcode Scanner</span>
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="Online"></span>
                </h1>
                <p class="text-[11px] text-slate-400 font-mono truncate max-w-[170px]">
                    Session: <span class="text-blue-400 font-bold">{{ $token }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-1.5">
            <span id="connectionBadge" class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-950/80 text-emerald-400 border border-emerald-800/60 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Laptop Linked</span>
            </span>
        </div>
    </header>

    <!-- Main Scanner Interface -->
    <main class="flex-grow flex flex-col p-3 max-w-md mx-auto w-full">

        @if(!empty($pairingError))
            <div class="my-6 p-5 rounded-2xl bg-rose-950/70 border border-rose-700 text-rose-100 text-center space-y-3">
                <div class="text-2xl">🔒</div>
                <h2 class="text-sm font-bold text-white">Pairing Session Unavailable</h2>
                <p class="text-xs text-rose-200">{{ $pairingError }}</p>
            </div>
        @else

        <!-- Target Field Notification -->
        <div class="mb-3 px-3 py-2 rounded-xl bg-blue-950/40 border border-blue-800/40 flex items-center justify-between">
            <div class="flex items-center space-x-2 text-xs">
                <span class="text-blue-400">Scanning for:</span>
                <span class="font-bold text-white bg-blue-900/50 px-2 py-0.5 rounded text-[11px]">{{ $label }}</span>
            </div>
            <span class="text-[11px] text-slate-400">Instant Sync</span>
        </div>

        <!-- Camera Viewport Card -->
        <div id="scannerViewportCard" onclick="startLiveCameraScanner()" 
             class="relative w-full aspect-[4/3] rounded-2xl bg-black overflow-hidden border border-slate-800 shadow-2xl flex items-center justify-center cursor-pointer select-none">
            
            <!-- Live HTML5 Video Feed (Continuous Real-time Viewfinder) -->
            <video id="scannerVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>

            <!-- Image Preview when photo is captured (Camera Fallback for Insecure HTTP) -->
            <img id="scannerImagePreview" class="hidden w-full h-full object-cover" alt="Captured Barcode">

            <!-- Native Mobile Camera Input for HTTP / Insecure Contexts -->
            <input type="file" id="mobileCameraInput" accept="image/*" capture="environment" class="hidden" onchange="handleMobileImageUpload(event)">

            <!-- Canvas for Frame Decoding -->
            <canvas id="scannerCanvas" class="hidden"></canvas>

            <!-- Tap to Open Camera Prompt (Initial Viewport Overlay) -->
            <div id="tapToScanPrompt" class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center z-10 bg-gradient-to-b from-slate-900/90 to-slate-950/95">
                <div class="w-16 h-16 rounded-2xl bg-cyan-500/20 border-2 border-cyan-400 text-cyan-300 flex items-center justify-center text-3xl mb-3 shadow-[0_0_20px_rgba(34,211,238,0.3)] animate-pulse">
                    📷
                </div>
                <div class="text-sm font-bold text-white mb-1">Tap to Scan CCTV Barcode</div>
                <div class="text-xs text-slate-300 max-w-[220px]">Point camera directly at CCTV barcode, S/N or MAC sticker</div>
                <div class="mt-3 px-4 py-1.5 rounded-xl bg-cyan-500 text-slate-950 font-bold text-xs shadow-lg inline-flex items-center gap-1.5">
                    <span>⚡ Scan Barcode with Camera</span>
                </div>
            </div>

            <!-- Holographic Reticle Overlay -->
            <div id="reticleOverlay" class="absolute inset-0 pointer-events-none flex items-center justify-center p-6">
                <div class="relative w-full max-w-[260px] h-[150px] rounded-xl border border-blue-500/40 shadow-[0_0_0_9999px_rgba(3,7,18,0.65)] flex flex-col justify-between p-2">
                    
                    <!-- Top Corners -->
                    <div class="flex justify-between w-full">
                        <div class="w-5 h-5 border-t-2 border-l-2 border-cyan-400 rounded-tl-lg"></div>
                        <div class="w-5 h-5 border-t-2 border-r-2 border-cyan-400 rounded-tr-lg"></div>
                    </div>

                    <!-- Scanning Laser Sweep Line -->
                    <div class="laser-sweep-line absolute left-2 right-2 h-0.5 bg-gradient-to-r from-transparent via-cyan-400 to-transparent shadow-[0_0_12px_#22d3ee]"></div>

                    <!-- Bottom Corners -->
                    <div class="flex justify-between w-full">
                        <div class="w-5 h-5 border-b-2 border-l-2 border-cyan-400 rounded-bl-lg"></div>
                        <div class="w-5 h-5 border-b-2 border-r-2 border-cyan-400 rounded-br-lg"></div>
                    </div>
                </div>
            </div>

            <!-- Live Status Ribbon in Camera Viewport -->
            <div id="scannerStatusPill" class="absolute bottom-3 left-3 right-3 text-center text-xs font-semibold px-3 py-1.5 rounded-full bg-slate-900/90 backdrop-blur-sm border border-slate-700/80 text-slate-200 z-20">
                Point at CCTV Barcode / S/N sticker...
            </div>

            <!-- Flash Torch Overlay Button (live stream only) -->
            <button type="button" id="torchBtn" onclick="event.stopPropagation(); toggleMobileTorch()"
                class="hidden absolute top-3 right-3 w-10 h-10 rounded-full bg-slate-900/70 border border-slate-700/60 text-white flex items-center justify-center shadow-lg active:scale-95 transition-all z-20">
                💡
            </button>

            <!-- Flip Camera Overlay Button (live stream only) -->
            <button type="button" id="mobileFlipBtn" onclick="event.stopPropagation(); switchMobileCamera()"
                class="hidden absolute top-3 left-3 w-10 h-10 rounded-full bg-slate-900/70 border border-slate-700/60 text-white flex items-center justify-center shadow-lg active:scale-95 transition-all z-20">
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
            <button type="button" onclick="resumeScanning()" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-600 text-white text-[11px] font-bold rounded-lg shadow cursor-pointer">
                Scan Next
            </button>
        </div>

        <!-- In-App Manual Entry & Scanner Assistant Card (Replaces ugly browser prompt) -->
        <div id="manualAssistantCard" class="hidden my-3 p-3.5 rounded-2xl bg-slate-900 border border-amber-500/40 text-left space-y-2.5 shadow-xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-amber-400 text-sm">⚠️</span>
                    <span class="text-xs font-bold text-white">Barcode not detected automatically</span>
                </div>
                <button type="button" onclick="triggerMobileCameraSnap()" class="text-[11px] text-cyan-400 font-bold hover:underline">
                    📸 Re-take Photo
                </button>
            </div>
            <p class="text-[11px] text-slate-300">
                You can re-take closer to the barcode sticker, or enter the printed S/N or MAC below to transmit instantly:
            </p>
            <div class="flex items-center gap-1.5">
                <input type="text" id="manualSnInput" placeholder="Enter S/N, MAC, or EAN"
                       onkeydown="if(event.key === 'Enter') submitManualCodeFromCard()"
                       class="flex-1 text-xs rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 font-mono text-white focus:outline-none focus:border-blue-500 uppercase">
                <button type="button" onclick="submitManualCodeFromCard()"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-500 text-white shadow-md cursor-pointer transition shrink-0">
                    Send to Laptop
                </button>
            </div>
        </div>

        <!-- Camera Control Bar -->
        <div class="space-y-2 mt-3">
            <!-- Primary Live Hardware Camera Trigger -->
            <button type="button" onclick="startLiveCameraScanner()"
                    class="w-full flex items-center justify-center space-x-2 min-h-[46px] py-3 px-4 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-900/40 cursor-pointer active:scale-98 transition-all">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Live Barcode Scanner (Point Camera)</span>
            </button>

            <div class="grid grid-cols-2 gap-2">
                <!-- Re-scan Live -->
                <button type="button" onclick="startLiveCameraScanner()"
                        class="flex items-center justify-center space-x-1.5 min-h-[40px] py-2 px-3 bg-slate-900 hover:bg-slate-800 text-slate-200 rounded-xl text-xs font-semibold border border-slate-800 cursor-pointer shadow-xs active:scale-98 transition-all">
                    <span>⚡ Re-scan Barcode</span>
                </button>

                <!-- Manual Entry / Type S/N Button -->
                <button type="button" onclick="openManualEntryPrompt()"
                    class="flex items-center justify-center space-x-1.5 min-h-[40px] py-2 px-3 bg-slate-900 hover:bg-slate-800 text-slate-200 rounded-xl text-xs font-semibold border border-slate-800 cursor-pointer shadow-xs active:scale-98 transition-all">
                    <span>⌨️ Type S/N Manually</span>
                </button>
            </div>
        </div>

        <!-- Optional Live 60fps Video Stream Helper Card (For Wi-Fi HTTP Android Chrome) -->
        <div x-data="{ open: false }" class="mt-3 p-3 rounded-xl bg-slate-900/60 border border-slate-800 text-xs">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-slate-400 hover:text-slate-200 text-left font-semibold">
                <span class="flex items-center gap-1.5">
                    <span class="text-cyan-400">💡</span>
                    <span>Want 60fps continuous live video on mobile?</span>
                </span>
                <span x-text="open ? '▲' : '▼'" class="text-[10px]"></span>
            </button>
            <div x-show="open" x-cloak class="mt-2.5 pt-2.5 border-t border-slate-800 text-slate-300 space-y-1.5 text-[11px]">
                <p>Because mobile browsers restrict live camera video feeds over insecure HTTP LAN Wi-Fi, you can enable it in Chrome for Android:</p>
                <ol class="list-decimal list-inside space-y-1 text-slate-400 pl-1">
                    <li>Open a new tab and go to: <code class="text-cyan-400 bg-slate-950 px-1.5 py-0.5 rounded select-all font-mono">chrome://flags/#unsafely-treat-insecure-origin-as-secure</code></li>
                    <li>Add this server address: <code class="text-cyan-400 bg-slate-950 px-1.5 py-0.5 rounded select-all font-mono">{{ url('/') }}</code></li>
                    <li>Set to <strong>Enabled</strong> and tap <strong>Relaunch</strong>.</li>
                </ol>
                <p class="text-[10px] text-slate-500 pt-1">Even without flags, the <strong>Scan Hardware with Camera</strong> button above decodes all CCTV barcodes and S/N tags using the camera.</p>
            </div>
        </div>

        <!-- Recent Scans on this session -->
        <div class="mt-4 pt-3 border-t border-slate-800/80 flex-grow">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Recent Scans This Session</h3>
                <span class="text-[11px] text-slate-500 font-mono" id="scanCountBadge">0 items</span>
            </div>

            <div id="recentScansList" class="space-y-2 max-h-40 overflow-y-auto pr-1">
                <div id="emptyScansNotice" class="text-center py-4 text-xs text-slate-500">
                    No barcodes scanned yet. Center barcode inside the box above.
                </div>
            </div>
        </div>

        @endif

    </main>

    <!-- Bottom Instructions Footer -->
    <footer class="bg-slate-900/60 border-t border-slate-800/80 px-4 py-2.5 text-center text-[11px] text-slate-500">
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
        let isLiveCameraSupported = false;

        document.addEventListener('DOMContentLoaded', () => {
            // Attempt auto-starting camera stream immediately (pass false so file dialog is not popped without user gesture)
            startLiveCameraScanner(false);
            sendHeartbeat();
            setInterval(sendHeartbeat, 15000);
        });

        function playBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(1760, ctx.currentTime); // 1760Hz crisp high pitch
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.15);
            } catch (e) {}
        }

        function triggerMobileCameraSnap() {
            const input = document.getElementById('mobileCameraInput');
            if (input) {
                input.value = '';
                input.click();
            }
        }

        async function startLiveCameraScanner(isUserGesture = true) {
            const video = document.getElementById('scannerVideo');
            const tapPrompt = document.getElementById('tapToScanPrompt');
            const torchBtn = document.getElementById('torchBtn');
            const flipBtn = document.getElementById('mobileFlipBtn');
            const status = document.getElementById('scannerStatusPill');

            const hasGetUserMedia = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

            if (!hasGetUserMedia) {
                isLiveCameraSupported = false;
                if (tapPrompt) tapPrompt.classList.remove('hidden');
                if (isUserGesture) {
                    if (status) status.innerText = 'Opening mobile camera...';
                    triggerMobileCameraSnap();
                } else {
                    if (status) status.innerText = 'Camera Ready — Tap button to scan barcode';
                }
                return;
            }

            try {
                if (currentStream) {
                    currentStream.getTracks().forEach(t => t.stop());
                    currentStream = null;
                }

                if (status) status.innerText = 'Connecting camera...';

                let stream = null;
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: { ideal: currentFacingMode },
                            width: { ideal: 1920 },
                            height: { ideal: 1080 }
                        },
                        audio: false
                    });
                } catch (e1) {
                    // Fallback to simpler constraints
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: currentFacingMode },
                        audio: false
                    });
                }

                isLiveCameraSupported = true;
                currentStream = stream;
                videoTrack = stream.getVideoTracks()[0];
                video.srcObject = stream;
                video.classList.remove('hidden');
                const previewImg = document.getElementById('scannerImagePreview');
                if (previewImg) previewImg.classList.add('hidden');
                if (tapPrompt) tapPrompt.classList.add('hidden');
                if (torchBtn) torchBtn.classList.remove('hidden');
                if (flipBtn) flipBtn.classList.remove('hidden');

                await video.play();
                isScanningActive = true;
                if (status) status.innerText = 'Point camera directly at CCTV barcode or S/N...';
                startLiveScanLoop();
            } catch (err) {
                console.warn('Live camera stream not permitted or failed, falling back to camera snap:', err);
                isLiveCameraSupported = false;
                if (isUserGesture) {
                    if (status) status.innerText = 'Opening camera...';
                    triggerMobileCameraSnap();
                } else {
                    if (tapPrompt) tapPrompt.classList.remove('hidden');
                    if (status) status.innerText = 'Camera Ready — Tap button to scan barcode';
                }
            }
        }

        async function checkAndInitCamera() {
            return startLiveCameraScanner(true);
        }

        function startLiveScanLoop() {
            if (!isLiveCameraSupported || !isScanningActive) return;

            const video = document.getElementById('scannerVideo');
            if (video && video.readyState === video.HAVE_ENOUGH_DATA) {
                // Try decoding current video frame
                decodeBarcodeFromSource(video).then(code => {
                    if (code && isScanningActive) {
                        onBarcodeScanned(code);
                    } else if (isScanningActive) {
                        animationFrameId = requestAnimationFrame(startLiveScanLoop);
                    }
                }).catch(() => {
                    if (isScanningActive) {
                        animationFrameId = requestAnimationFrame(startLiveScanLoop);
                    }
                });
            } else {
                animationFrameId = requestAnimationFrame(startLiveScanLoop);
            }
        }

        function getZXingReader() {
            if (!window.ZXing || !window.ZXing.BrowserMultiFormatReader) return null;
            try {
                const hints = new Map();
                if (window.ZXing.DecodeHintType) {
                    hints.set(window.ZXing.DecodeHintType.TRY_HARDER, true);
                    if (window.ZXing.BarcodeFormat) {
                        hints.set(window.ZXing.DecodeHintType.POSSIBLE_FORMATS, [
                            window.ZXing.BarcodeFormat.CODE_128,
                            window.ZXing.BarcodeFormat.CODE_39,
                            window.ZXing.BarcodeFormat.CODE_93,
                            window.ZXing.BarcodeFormat.EAN_13,
                            window.ZXing.BarcodeFormat.EAN_8,
                            window.ZXing.BarcodeFormat.UPC_A,
                            window.ZXing.BarcodeFormat.UPC_E,
                            window.ZXing.BarcodeFormat.QR_CODE,
                            window.ZXing.BarcodeFormat.DATA_MATRIX,
                            window.ZXing.BarcodeFormat.ITF
                        ]);
                    }
                }
                return new window.ZXing.BrowserMultiFormatReader(hints);
            } catch (e) {
                return new window.ZXing.BrowserMultiFormatReader();
            }
        }

        function createRotatedCanvas(sourceCanvas, angle) {
            const w = sourceCanvas.width;
            const h = sourceCanvas.height;
            const rot = document.createElement('canvas');
            if (angle === 90 || angle === 270) {
                rot.width = h;
                rot.height = w;
            } else {
                rot.width = w;
                rot.height = h;
            }
            const ctx = rot.getContext('2d');
            ctx.translate(rot.width / 2, rot.height / 2);
            ctx.rotate((angle * Math.PI) / 180);
            ctx.drawImage(sourceCanvas, -w / 2, -h / 2);
            return rot;
        }

        // Multi-engine Barcode Decoder: BarcodeDetector + Multi-scale / Multi-angle ZXing JS + Server-side OpenCV fallback
        async function decodeBarcodeFromSource(sourceElement) {
            // 1. Native BarcodeDetector (Chrome Android where available)
            if ('BarcodeDetector' in window) {
                try {
                    const detector = new BarcodeDetector({
                        formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'qr_code', 'upc_a', 'upc_e', 'data_matrix']
                    });
                    const barcodes = await detector.detect(sourceElement);
                    if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                        return barcodes[0].rawValue;
                    }
                } catch (e) {}
            }

            const reader = getZXingReader();
            if (!reader) return null;

            // Direct attempt
            try {
                let result = null;
                if (sourceElement instanceof HTMLImageElement) {
                    result = await reader.decodeFromImageElement(sourceElement);
                } else if (sourceElement instanceof HTMLVideoElement) {
                    result = await reader.decodeFromVideoElement(sourceElement);
                }
                if (result && result.getText()) {
                    return result.getText();
                }
            } catch (e) {}

            if (!(sourceElement instanceof HTMLImageElement)) return null;

            // 2. High-precision Multi-scale and Multi-rotation decoding
            try {
                const nw = sourceElement.naturalWidth || sourceElement.width || 800;
                const nh = sourceElement.naturalHeight || sourceElement.height || 600;

                // Test crops: [sx, sy, sw, sh, targetMaxDim]
                const cropConfigs = [
                    // Center 65% crop (where phone cameras usually frame the barcode)
                    [Math.round(nw * 0.175), Math.round(nh * 0.175), Math.round(nw * 0.65), Math.round(nh * 0.65), 1200],
                    // Full image scaled to 1400px
                    [0, 0, nw, nh, 1400],
                    // Top half (sometimes sticker is in top half)
                    [0, 0, nw, Math.round(nh * 0.6), 1200],
                    // Bottom half
                    [0, Math.round(nh * 0.4), nw, Math.round(nh * 0.6), 1200]
                ];

                const angles = [0, 90, 270, 180];

                for (const [sx, sy, sw, sh, maxDim] of cropConfigs) {
                    let tw = sw;
                    let th = sh;
                    if (tw > maxDim || th > maxDim) {
                        if (tw > th) {
                            th = Math.round((th * maxDim) / tw);
                            tw = maxDim;
                        } else {
                            tw = Math.round((tw * maxDim) / th);
                            th = maxDim;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = tw;
                    canvas.height = th;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(sourceElement, sx, sy, sw, sh, 0, 0, tw, th);

                    for (const angle of angles) {
                        const testCanvas = (angle === 0) ? canvas : createRotatedCanvas(canvas, angle);
                        try {
                            const res = await reader.decodeFromCanvas(testCanvas);
                            if (res && res.getText()) return res.getText();
                        } catch (e) {}
                    }

                    // Contrast auto-stretch pass
                    const imgData = ctx.getImageData(0, 0, tw, th);
                    const d = imgData.data;
                    let min = 255, max = 0;
                    for (let i = 0; i < d.length; i += 4) {
                        const g = 0.299 * d[i] + 0.587 * d[i+1] + 0.114 * d[i+2];
                        if (g < min) min = g;
                        if (g > max) max = g;
                    }
                    const range = (max - min) || 1;
                    for (let i = 0; i < d.length; i += 4) {
                        const g = 0.299 * d[i] + 0.587 * d[i+1] + 0.114 * d[i+2];
                        const norm = Math.round(((g - min) / range) * 255);
                        d[i] = norm;
                        d[i+1] = norm;
                        d[i+2] = norm;
                    }
                    ctx.putImageData(imgData, 0, 0);

                    for (const angle of angles) {
                        const testCanvas = (angle === 0) ? canvas : createRotatedCanvas(canvas, angle);
                        try {
                            const res = await reader.decodeFromCanvas(testCanvas);
                            if (res && res.getText()) return res.getText();
                        } catch (e) {}
                    }
                }
            } catch (e) {
                console.warn('Multi-scale client decode error:', e);
            }

            return null;
        }

        function showManualAssistantCard() {
            const card = document.getElementById('manualAssistantCard');
            if (card) {
                card.classList.remove('hidden');
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                const input = document.getElementById('manualSnInput');
                if (input) input.focus();
            }
        }

        function hideManualAssistantCard() {
            const card = document.getElementById('manualAssistantCard');
            if (card) card.classList.add('hidden');
        }

        function submitManualCodeFromCard() {
            const input = document.getElementById('manualSnInput');
            if (!input || !input.value.trim()) return;
            const code = input.value.trim();
            hideManualAssistantCard();
            onBarcodeScanned(code);
        }

        // Handle camera photo snap from mobile
        async function handleMobileImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            event.target.value = '';

            const status = document.getElementById('scannerStatusPill');
            const previewImg = document.getElementById('scannerImagePreview');
            const tapPrompt = document.getElementById('tapToScanPrompt');
            const video = document.getElementById('scannerVideo');

            if (status) status.innerText = 'Analyzing photo for barcode...';

            // Show captured photo immediately in viewfinder (prevents blank screen!)
            const objectUrl = URL.createObjectURL(file);
            if (previewImg) {
                previewImg.src = objectUrl;
                previewImg.classList.remove('hidden');
            }
            if (tapPrompt) tapPrompt.classList.add('hidden');
            if (video) video.classList.add('hidden');

            const img = new Image();
            img.src = objectUrl;
            img.onload = async () => {
                // 1. Try enhanced client-side decode
                let detectedCode = await decodeBarcodeFromSource(img);

                // 2. If client-side misses, try server-side Python OpenCV decode
                if (!detectedCode) {
                    if (status) status.innerText = 'Enhancing image with server engine...';
                    try {
                        const formData = new FormData();
                        formData.append('token', SESSION_TOKEN);
                        formData.append('image', file);
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const serverRes = await fetch('/api/mobile-scanner/analyze-image', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            body: formData
                        });
                        const serverData = await serverRes.json();
                        if (serverData && serverData.success && serverData.code) {
                            detectedCode = serverData.code;
                        }
                    } catch (err) {
                        console.warn('Server barcode decode error:', err);
                    }
                }

                if (detectedCode) {
                    hideManualAssistantCard();
                    onBarcodeScanned(detectedCode);
                } else {
                    if (status) status.innerText = 'Could not detect barcode clearly. Check assistant below.';
                    showManualAssistantCard();
                }
            };
        }

        async function onBarcodeScanned(rawCode) {
            if (!rawCode) return;
            isScanningActive = false;
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }

            const cleanCode = String(rawCode).trim();

            if (navigator.vibrate) {
                navigator.vibrate([120, 60, 120]);
            }
            playBeep();

            // Show UI confirmation
            const display = document.getElementById('scannedCodeDisplay');
            if (display) display.innerText = cleanCode;
            const toast = document.getElementById('successToast');
            if (toast) toast.classList.remove('hidden');

            const status = document.getElementById('scannerStatusPill');
            if (status) status.innerText = `✓ Scanned: ${cleanCode}`;

            // Push to laptop server session
            await pushCodeToLaptop(cleanCode);

            // Add to recent list
            addToRecentScans(cleanCode);
        }

        async function pushCodeToLaptop(code) {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await fetch('/api/mobile-scanner/push', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
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
            const toast = document.getElementById('successToast');
            if (toast) toast.classList.add('hidden');
            
            const previewImg = document.getElementById('scannerImagePreview');
            if (previewImg) previewImg.classList.add('hidden');

            const tapPrompt = document.getElementById('tapToScanPrompt');
            if (!isLiveCameraSupported && tapPrompt) {
                tapPrompt.classList.remove('hidden');
            }

            const status = document.getElementById('scannerStatusPill');
            if (status) status.innerText = 'Point at CCTV Barcode / S/N sticker...';

            isScanningActive = true;
            if (isLiveCameraSupported) {
                startLiveScanLoop();
            } else {
                // User clicked "Scan Next", immediately open camera for next device!
                triggerMobileCameraSnap();
            }
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
                        <span class="text-cyan-400 font-mono text-xs font-bold">${escapeHtml(item)}</span>
                    </div>
                    <button type="button" onclick="pushCodeToLaptop('${escapeHtml(item)}'); alert('Re-sent to laptop!');"
                        class="px-2.5 py-1 rounded bg-blue-600/30 hover:bg-blue-600 text-blue-300 hover:text-white text-[11px] font-bold transition-colors cursor-pointer">
                        Re-Send
                    </button>
                </div>
            `).join('');
        }

        function escapeHtml(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function openManualEntryPrompt() {
            const manual = prompt('Enter Hardware Serial Number or MAC Address manually:');
            if (manual && manual.trim()) {
                onBarcodeScanned(manual.trim());
            }
        }

        function toggleMobileTorch() {
            if (videoTrack && videoTrack.applyConstraints) {
                isTorchOn = !isTorchOn;
                videoTrack.applyConstraints({
                    advanced: [{ torch: isTorchOn }]
                }).then(() => {
                    const btn = document.getElementById('torchBtn');
                    if (btn) btn.style.background = isTorchOn ? '#eab308' : 'rgba(15, 23, 42, 0.7)';
                }).catch(() => {});
            }
        }

        async function switchMobileCamera() {
            if (!isLiveCameraSupported) return;
            currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
            if (currentStream) {
                currentStream.getTracks().forEach(t => t.stop());
            }
            await checkAndInitCamera();
        }

        async function sendHeartbeat() {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                await fetch(`/api/mobile-scanner/heartbeat/${SESSION_TOKEN}`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
            } catch (e) {}
        }
    </script>
</body>
</html>
