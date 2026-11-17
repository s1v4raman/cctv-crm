<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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

        <!-- Target Field Notification -->
        <div class="mb-3 px-3 py-2 rounded-xl bg-blue-950/40 border border-blue-800/40 flex items-center justify-between">
            <div class="flex items-center space-x-2 text-xs">
                <span class="text-blue-400">Scanning for:</span>
                <span class="font-bold text-white bg-blue-900/50 px-2 py-0.5 rounded text-[11px]">{{ $label }}</span>
            </div>
            <span class="text-[11px] text-slate-400">Instant Sync</span>
        </div>

        <!-- Camera Viewport Card -->
        <div id="scannerViewportCard" onclick="triggerMobileCameraSnap()" 
             class="relative w-full aspect-[4/3] rounded-2xl bg-black overflow-hidden border border-slate-800 shadow-2xl flex items-center justify-center cursor-pointer select-none">
            
            <!-- Direct Hardware Camera Input -->
            <input type="file" id="mobileCameraInput" accept="image/*" capture="environment" onchange="handleMobileImageUpload(event)" class="hidden">

            <!-- Live HTML5 Video Feed (Used when HTTPS or getUserMedia is available) -->
            <video id="scannerVideo" autoplay playsinline muted class="w-full h-full object-cover hidden"></video>

            <!-- Snapped / Uploaded Image View (Shows real image instead of blank black screen!) -->
            <img id="scannerImagePreview" class="w-full h-full object-contain hidden bg-slate-950" alt="Scanned Barcode Preview">
            <canvas id="scannerCanvas" class="hidden"></canvas>

            <!-- Tap to Open Camera Prompt (Shown when live stream is unavailable over HTTP) -->
            <div id="tapToScanPrompt" class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center z-10 bg-gradient-to-b from-slate-900/90 to-slate-950/95">
                <div class="w-16 h-16 rounded-2xl bg-cyan-500/20 border-2 border-cyan-400 text-cyan-300 flex items-center justify-center text-3xl mb-3 shadow-[0_0_20px_rgba(34,211,238,0.3)] animate-pulse">
                    📷
                </div>
                <div class="text-sm font-bold text-white mb-1">Tap to Open Phone Camera</div>
                <div class="text-xs text-slate-300 max-w-[220px]">Point camera at CCTV barcode, S/N or MAC sticker to scan & sync</div>
                <div class="mt-3 px-4 py-1.5 rounded-xl bg-cyan-500 text-slate-950 font-bold text-xs shadow-lg inline-flex items-center gap-1.5">
                    <span>Scan Product Barcode</span>
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

        <!-- Camera Control Bar -->
        <div class="grid grid-cols-2 gap-2 mt-3">
            <!-- Snap Photo Fallback -->
            <button type="button" onclick="triggerMobileCameraSnap()"
                    class="flex items-center justify-center space-x-2 min-h-[44px] py-2.5 px-3 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-semibold border border-slate-700/80 cursor-pointer shadow-md active:scale-98 transition-all">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Snap Barcode</span>
            </button>

            <!-- Manual Entry / Type S/N Button -->
            <button type="button" onclick="openManualEntryPrompt()"
                class="flex items-center justify-center space-x-2 min-h-[44px] py-2.5 px-3 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-semibold border border-slate-700/80 shadow-md active:scale-98 transition-all cursor-pointer">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Type S/N Manually</span>
            </button>
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
            checkAndInitCamera();
            sendHeartbeat();
            setInterval(sendHeartbeat, 15000);
        });

        function triggerMobileCameraSnap() {
            const fileInput = document.getElementById('mobileCameraInput');
            if (fileInput) fileInput.click();
        }

        async function checkAndInitCamera() {
            const video = document.getElementById('scannerVideo');
            const tapPrompt = document.getElementById('tapToScanPrompt');
            const torchBtn = document.getElementById('torchBtn');
            const flipBtn = document.getElementById('mobileFlipBtn');
            const status = document.getElementById('scannerStatusPill');

            // On mobile phones over HTTP (insecure context), getUserMedia is blocked by browser policy.
            // Detect this gracefully and enable the high-performance Camera Snap interface.
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                isLiveCameraSupported = false;
                if (tapPrompt) tapPrompt.classList.remove('hidden');
                if (video) video.classList.add('hidden');
                if (status) status.innerText = 'Tap viewfinder above to scan barcode with camera';
                return;
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: { ideal: currentFacingMode },
                        width: { ideal: 1920 },
                        height: { ideal: 1080 }
                    }
                });

                isLiveCameraSupported = true;
                currentStream = stream;
                videoTrack = stream.getVideoTracks()[0];
                video.srcObject = stream;
                video.classList.remove('hidden');
                if (tapPrompt) tapPrompt.classList.add('hidden');
                if (torchBtn) torchBtn.classList.remove('hidden');
                if (flipBtn) flipBtn.classList.remove('hidden');

                await video.play();
                if (status) status.innerText = 'Point camera at CCTV barcode or S/N...';
                startLiveScanLoop();
            } catch (err) {
                console.warn('Live camera stream not permitted, switching to Camera Viewfinder:', err);
                isLiveCameraSupported = false;
                if (tapPrompt) tapPrompt.classList.remove('hidden');
                if (video) video.classList.add('hidden');
                if (status) status.innerText = 'Tap viewfinder above to scan barcode with camera';
            }
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

        // Multi-engine Barcode Decoder: BarcodeDetector + ZXing JS with high-res auto-scaling & contrast enhancement
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

            // 2. ZXing BrowserMultiFormatReader (Universal fallback)
            let reader = null;
            if (window.ZXing && window.ZXing.BrowserMultiFormatReader) {
                try {
                    reader = new ZXing.BrowserMultiFormatReader();
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
            }

            // 3. High-res Photo Pre-processing (downscale 12MP+ camera photos to 1280px & contrast-boost for CCTV serial tags)
            if (reader && sourceElement instanceof HTMLImageElement) {
                try {
                    const canvas = document.createElement('canvas');
                    const maxDim = 1280;
                    let w = sourceElement.naturalWidth || sourceElement.width || 800;
                    let h = sourceElement.naturalHeight || sourceElement.height || 600;
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
                    ctx.drawImage(sourceElement, 0, 0, w, h);

                    try {
                        const res = await reader.decodeFromCanvas(canvas);
                        if (res && res.getText()) return res.getText();
                    } catch (e) {}

                    // High-contrast B&W pass
                    const imgData = ctx.getImageData(0, 0, w, h);
                    const d = imgData.data;
                    for (let i = 0; i < d.length; i += 4) {
                        const gray = (d[i] * 0.299 + d[i + 1] * 0.587 + d[i + 2] * 0.114);
                        const contrast = gray > 120 ? 255 : 0;
                        d[i] = contrast;
                        d[i + 1] = contrast;
                        d[i + 2] = contrast;
                    }
                    ctx.putImageData(imgData, 0, 0);

                    try {
                        const res = await reader.decodeFromCanvas(canvas);
                        if (res && res.getText()) return res.getText();
                    } catch (e) {}
                } catch (e) {}
            }

            return null;
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
                const detectedCode = await decodeBarcodeFromSource(img);
                if (detectedCode) {
                    onBarcodeScanned(detectedCode);
                } else {
                    if (status) status.innerText = 'Could not detect barcode clearly. Tap to re-take.';
                    // If automatic recognition failed, give user manual option with prefill
                    const manual = prompt('Could not auto-detect barcode. Enter S/N from photo manually (or Cancel to re-take):');
                    if (manual && manual.trim()) {
                        onBarcodeScanned(manual.trim());
                    }
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
                await fetch(`/api/mobile-scanner/heartbeat/${SESSION_TOKEN}`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });
            } catch (e) {}
        }
    </script>
</body>
</html>
