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
    $lanIp = \App\Http\Controllers\MobileScannerSyncController::detectLanIp();
@endphp

{{-- Local Offline Barcode & QR Decoding Engine (ZXing MultiFormat) --}}
<script src="{{ asset('vendor/zxing/zxing.min.js') }}"></script>

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
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Scan Barcode, Serial Number (S/N), or MAC Address tag') }}</p>
                </div>
            </div>
            <x-close-button onclick="closeBarcodeScanner('{{ $modalId }}')" :label="__('Close')" />
        </div>

        {{-- Plain text tabs with 2px underline — project style guide --}}
        <div class="flex border-b border-slate-200 dark:border-slate-700 select-none">
            <button type="button" id="{{ $modalId }}-tab-mobile" onclick="switchScannerMode('{{ $modalId }}', 'mobile')"
                    class="px-4 py-2.5 text-sm font-semibold border-b-2 border-[var(--crm-accent,#2563eb)] text-[var(--crm-accent,#2563eb)] -mb-px cursor-pointer transition-colors">
                <span id="{{ $modalId }}-tab-mobile-text">{{ __('Mobile Scan') }}</span>
            </button>
            <button type="button" id="{{ $modalId }}-tab-laptop" onclick="switchScannerMode('{{ $modalId }}', 'machine')"
                    class="px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-slate-600 dark:text-slate-400 -mb-px cursor-pointer hover:text-slate-900 dark:hover:text-slate-100 transition-colors">
                <span id="{{ $modalId }}-tab-laptop-text">{{ __('Barcode Machine') }}</span>
            </button>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- PANE 1: MOBILE BASED SCAN (PHONE QR SYNC / CAMERA)         --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div id="{{ $modalId }}-mobile-pane" class="space-y-4">
            
            {{-- Desktop / Laptop View: QR Code Pairing Card --}}
            <div id="{{ $modalId }}-mobile-desktop-view" class="p-4 rounded-[4px] bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-center space-y-3">
                <div class="flex items-center justify-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-[var(--crm-accent,#2563eb)] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 3h3m-3 3h3"/></svg>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Scan Hardware with Smartphone Camera') }}</h4>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    {{ __('Scan this QR code with your mobile camera. Point your phone at the hardware device, barcode, or S/N sticker — data will automatically appear on this webpage for analysis & save.') }}
                </p>

                {{-- QR Code Display --}}
                <div class="flex flex-col items-center justify-center py-2">
                    <div class="p-2 bg-white rounded-[4px] border border-slate-200 dark:border-slate-700 inline-block">
                        <img id="{{ $modalId }}-qr-img" src="" alt="Mobile Scanner QR Code" class="w-44 h-44 object-contain">
                    </div>
                    <div id="{{ $modalId }}-mobile-status" class="mt-2 text-xs font-semibold text-blue-700 dark:text-blue-300">
                        {{ __('Waiting for phone connection...') }}
                    </div>
                </div>

                {{-- Host / IP Configuration --}}
                <div class="mt-2 p-3 bg-white dark:bg-slate-900/80 rounded-[4px] text-left space-y-2 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <label for="{{ $modalId }}-custom-ip" class="text-[11px] font-bold text-slate-700 dark:text-slate-300">
                            {{ __('Server Wi-Fi Address / Host:') }}
                        </label>
                        <span class="text-[10px] text-slate-500">{{ __('Phone must be on same Wi-Fi') }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <input type="text" id="{{ $modalId }}-custom-ip" value="{{ $lanIp }}:{{ request()->getPort() ?: 8000 }}"
                               placeholder="e.g. 192.168.29.168:8000"
                               class="flex-1 text-xs rounded-[4px] border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 px-2.5 py-1.5 font-mono text-slate-900 dark:text-slate-100 focus:ring-1 focus:ring-blue-500">
                        <button type="button" onclick="updateScannerQrUrl('{{ $modalId }}')"
                                class="btn-primary px-3 h-[30px] rounded-[4px] text-xs font-bold cursor-pointer shrink-0">
                            <span>{{ __('Update QR') }}</span>
                        </button>
                    </div>
                    <div class="pt-1 flex items-center justify-between text-xs flex-wrap gap-2">
                        <a id="{{ $modalId }}-direct-mobile-link" href="#" target="_blank"
                           class="text-blue-600 dark:text-blue-400 font-bold hover:underline inline-flex items-center gap-1">
                            <span>{{ __('Open Mobile Scanner Directly in New Tab') }} &rarr;</span>
                        </a>
                        <button type="button" onclick="switchScannerMode('{{ $modalId }}', 'machine')" class="text-slate-600 dark:text-slate-400 hover:underline cursor-pointer">
                            {{ __('Using a Barcode Scanner Gun?') }} &rarr;
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile Phone Direct View: Native Live Camera Stream --}}
            <div id="{{ $modalId }}-mobile-phone-view" style="display:none;" class="space-y-3">
                <div class="relative bg-black rounded-[4px] overflow-hidden w-full aspect-[4/3] flex items-center justify-center border border-slate-800 select-none cursor-pointer"
                     onclick="startMobileDirectCamera('{{ $modalId }}', true)">
                    <video id="{{ $modalId }}-mobile-video" autoplay playsinline muted class="w-full h-full object-cover"></video>

                    {{-- Captured photo preview for HTTP camera fallback --}}
                    <img id="{{ $modalId }}-mobile-cam-preview" class="hidden w-full h-full object-cover" alt="Captured Barcode">
                    <input type="file" id="{{ $modalId }}-mobile-capture-fallback" accept="image/*" capture="environment" style="display:none;" onchange="handleMobileDirectImageCapture(event, '{{ $modalId }}')">

                    {{-- Reticle — corner marks only, no laser sweep --}}
                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center p-4">
                        <div class="relative w-full max-w-[240px] h-[130px] border border-blue-500/40 shadow-[0_0_0_9999px_rgba(3,7,18,0.65)] flex flex-col justify-between p-2">
                            <div class="flex justify-between w-full">
                                <div class="w-4 h-4 border-t-2 border-l-2 border-cyan-400"></div>
                                <div class="w-4 h-4 border-t-2 border-r-2 border-cyan-400"></div>
                            </div>
                            <div class="flex justify-between w-full">
                                <div class="w-4 h-4 border-b-2 border-l-2 border-cyan-400"></div>
                                <div class="w-4 h-4 border-b-2 border-r-2 border-cyan-400"></div>
                            </div>
                        </div>
                    </div>

                    <div id="{{ $modalId }}-mobile-cam-status" class="absolute bottom-2 left-2 right-2 text-center text-xs font-semibold px-2.5 py-1 bg-slate-900/90 text-slate-200">
                        {{ __('Point camera at CCTV barcode or S/N sticker...') }}
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="startMobileDirectCamera('{{ $modalId }}', true)"
                            class="flex-1 h-[38px] sm:h-[52px] px-4 rounded-[4px] btn-primary font-bold text-xs inline-flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                        <span>{{ __('Start / Re-scan Camera') }}</span>
                    </button>
                    <button type="button" onclick="switchMobileDirectCamera('{{ $modalId }}')"
                            class="h-[38px] sm:h-[52px] px-3 rounded-[4px] text-slate-700 dark:text-slate-300 font-semibold text-xs border border-slate-300 dark:border-slate-700 cursor-pointer inline-flex items-center gap-1">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        <span>{{ __('Flip') }}</span>
                    </button>
                    <button type="button" onclick="switchScannerMode('{{ $modalId }}', 'machine')"
                            class="h-[38px] sm:h-[52px] px-3 rounded-[4px] text-slate-700 dark:text-slate-300 font-semibold text-xs border border-slate-300 dark:border-slate-700 cursor-pointer">
                        <span>{{ __('Scanner Gun') }}</span>
                    </button>
                </div>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- PANE 2: BARCODE MACHINE SCAN (USB / BLUETOOTH SCANNER GUN)  --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div id="{{ $modalId }}-laptop-pane" style="display:none;" class="space-y-4">
            
            <div class="p-4 rounded-[4px] bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-4 text-center">

                {{-- Scanner Gun Header --}}
                <div class="flex items-center justify-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-[var(--crm-accent,#2563eb)] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/></svg>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Barcode Machine & Scanner Gun Station') }}</h4>
                </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
                        {{ __('Connect your USB or Bluetooth barcode scanner machine / gun (Honeywell, Zebra, TVS, etc.). Pull the trigger on the hardware barcode to scan instantly into the webpage.') }}
                    </p>

                {{-- Dedicated High-Speed Scanner Gun Input Box --}}
                <div class="max-w-md mx-auto space-y-2">
                    <div class="relative">
                        <input type="text" id="{{ $modalId }}-manual-input"
                               placeholder="{{ __('Pull scanner gun trigger or type S/N...') }}"
                               onkeydown="if(event.key === 'Enter') { event.preventDefault(); const val = this.value.trim(); if(val) { handleBarcodeDetected('{{ $modalId }}', val); closeBarcodeScanner('{{ $modalId }}'); } }"
                               class="w-full text-center font-mono font-bold text-sm sm:text-base h-[38px] sm:h-[52px] px-4 rounded-[4px] border-2 border-[var(--crm-accent,#2563eb)] bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/20 focus:outline-none tracking-wider">
                    </div>

                    <div class="flex items-center justify-center gap-2">
                        <button type="button"
                                onclick="const val = document.getElementById('{{ $modalId }}-manual-input').value.trim(); if(val) { handleBarcodeDetected('{{ $modalId }}', val); closeBarcodeScanner('{{ $modalId }}'); }"
                                class="btn-primary h-[38px] px-5 rounded-[4px] text-xs font-bold cursor-pointer inline-flex items-center gap-1.5">
                            <span>{{ __('Apply Barcode / S/N') }} &rarr;</span>
                        </button>
                        <button type="button" onclick="const el = document.getElementById('{{ $modalId }}-manual-input'); if(el) { el.value = ''; el.focus(); }"
                                class="h-[38px] px-3 rounded-[4px] text-xs font-semibold text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-700 cursor-pointer">
                            <span>{{ __('Clear') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Active Listener Status — plain colored text, no pill --}}
                <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                    {{ __('Barcode Machine Listener Active — Ready for Trigger Pull') }}
                </p>

                {{-- Photo Fallback Button --}}
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs flex-wrap gap-2">
                    <label class="h-[30px] px-3 rounded-[4px] border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer inline-flex items-center gap-1.5 transition">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        <span>{{ __('Upload Barcode Photo / Image') }}</span>
                        <input type="file" accept="image/*" onchange="handleBarcodeImageUpload(event, '{{ $modalId }}')" style="display:none;">
                    </label>

                    <button type="button" onclick="switchScannerMode('{{ $modalId }}', 'mobile')" class="text-blue-600 dark:text-blue-400 font-bold hover:underline cursor-pointer">
                        <span>{{ __('Switch to Mobile Phone Scan') }} &rarr;</span>
                    </button>
                </div>

            </div>

        </div>

        {{-- Scanned Result Preview & Multi-Attribute Feedback --}}
        <div id="{{ $modalId }}-detected-pill" style="display:none;" class="p-3.5 bg-emerald-50 dark:bg-emerald-950/70 border border-emerald-400 dark:border-emerald-700 rounded-[4px] text-xs text-emerald-900 dark:text-emerald-100 flex flex-col gap-2">
            <div class="flex items-center justify-between border-b border-emerald-200 dark:border-emerald-800/80 pb-1.5">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    <span class="font-bold text-emerald-900 dark:text-emerald-100 text-sm">{{ __('All Data Captured in 1 Scan!') }}</span>
                </div>
                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">{{ __('Applied') }}</span>
            </div>
            <div id="{{ $modalId }}-multi-preview" class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-xs font-mono">
                <!-- Dynamically populated tags for S/N, MAC, EAN, Model -->
            </div>
            <div id="{{ $modalId }}-detected-text" class="font-mono text-[11px] text-emerald-800 dark:text-emerald-300 truncate"></div>
        </div>

        {{-- Modal Action Footer --}}
        <div class="pt-2 flex items-center justify-end">
            <button type="button" onclick="closeBarcodeScanner('{{ $modalId }}')"
                    class="h-[38px] px-4 rounded-[4px] border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold cursor-pointer">
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
    'blockedOrUnavailable' => __('Camera unavailable. Click "Upload Image" or use Barcode Scanner.'),
    'blocked' => __('Camera blocked. Click tune/lock icon in address bar and allow camera access.'),
    'inUseShort' => __('Camera in use by another app. Please close other app and retry.'),
    'noCamera' => __('No camera found on computer. Use "Upload Image" or Barcode Scanner.'),
    'permissionBlocked' => __("Camera Permission is Blocked in your browser.\n\nTo allow it:\n1. Click the lock/settings icon on the left of your URL address bar.\n2. Change Camera permission to Allow.\n3. Reload the page.\n\nYou can also click Upload Image to scan a photo file."),
    'inUse' => __("Camera is currently in use by another application.\n\nPlease close the other app and retry, or use Upload Image."),
    'notConnected' => __("Camera stream is not connected.\n\nPlease click Upload Image to scan from a photo file, or allow camera permissions."),
    'waitingFrame' => __('Waiting for camera frame...'),
    'snapping' => __('Snapping frame and scanning barcode...'),
    'manualSnap' => __('Could not read barcode from snap. Enter S/N manually or retry:'),
    'holdCloser' => __('Hold barcode closer and center in reticle, then tap Snap again.'),
    'processingUpload' => __('Processing uploaded image...'),
    'manualPhoto' => __('Could not read barcode from photo. Enter S/N manually:'),
    'unreadableImage' => __('Could not read barcode from image. Please try again or use Barcode Scanner.'),
    'noTorch' => __('Torch is not supported on this webcam/device.'),
    'torchUnavailable' => __('Torch control unavailable on this camera stream.'),
    'switching' => __('Switching camera...'),
    'toggling' => __('Toggling camera mode...'),
]) }};
window.barcodeScannerState = window.barcodeScannerState || {};

/**
 * Universal Hardware Barcode & Label Parser for CCTV/Security Devices:
 * Extracts Serial Number (S/N), MAC address, EAN barcode, and Model Number from composite labels in a single scan.
 */
window.parseDeviceBarcode = function(rawCode) {
    if (!rawCode) return { serial: '', mac: '', model: '', ean: '', isMacOnly: false, isSerialOnly: false, isComposite: false, raw: '' };
    const raw = String(rawCode).trim();
    let serial = '';
    let mac = '';
    let model = '';
    let ean = '';

    function isValidMac(str) {
        if (!str) return false;
        const s = String(str).replace(/^MAC[:\s=-]*/i, '').trim();
        // Standard colon or hyphen separated 6 hex pairs: e.g. F8:20:97:E3:33:91 or F8-20-97-E3-33-91
        if (/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/.test(s)) return true;
        // Dot notation 3 quads: e.g. f820.97e3.3391
        if (/^[0-9A-Fa-f]{4}\.[0-9A-Fa-f]{4}\.[0-9A-Fa-f]{4}$/.test(s)) return true;
        // Plain 12 hex chars: must contain only 0-9 and A-F, and MUST have at least one hex letter A-F (to prevent purely numeric 12-digit barcodes/EANs from falsely being treated as MAC)
        if (/^[0-9A-Fa-f]{12}$/.test(s) && /[A-Fa-f]/.test(s)) return true;
        return false;
    }

    function normalizeMac(str) {
        if (!str) return '';
        const clean = String(str).replace(/^MAC[:\s=-]*/i, '').replace(/[^0-9A-Fa-f]/g, '').toUpperCase();
        if (clean.length === 12) {
            return clean.match(/.{1,2}/g).join(':');
        }
        return str.toUpperCase();
    }

    function normalizeSerial(str) {
        if (!str) return '';
        return String(str).trim()
            .replace(/^(?:S\/N|SN|SERIAL(?:\s*NO)?|NO)[:\s=_-]*/i, '')
            .trim();
    }

    // 1. JSON parsing for modern smart QR codes
    if ((raw.startsWith('{') && raw.endsWith('}')) || (raw.startsWith('[') && raw.endsWith(']'))) {
        try {
            const data = JSON.parse(raw);
            const item = Array.isArray(data) ? data[0] : data;
            if (item && typeof item === 'object') {
                serial = normalizeSerial(item.serial || item.sn || item.s_n || item.serialNumber || item.serial_number || item.no || '');
                mac = normalizeMac(item.mac || item.macAddress || item.mac_address || '');
                model = item.model || item.modelNo || item.model_no || item.prod || item.item || '';
                ean = item.ean || item.upc || item.barcode || '';
            }
        } catch (e) {}
    }

    // 2. Query String / URL Parameter parsing
    if (!serial && !mac && (raw.includes('?') || (raw.includes('=') && (raw.includes('&') || raw.includes(';'))))) {
        try {
            const qs = raw.replace(/^.*\?/, '');
            const params = new URLSearchParams(qs.replace(/;/g, '&'));
            if (params.has('sn')) serial = normalizeSerial(params.get('sn'));
            else if (params.has('serial')) serial = normalizeSerial(params.get('serial'));
            else if (params.has('s_n')) serial = normalizeSerial(params.get('s_n'));

            if (params.has('mac')) mac = normalizeMac(params.get('mac'));
            else if (params.has('mac_address')) mac = normalizeMac(params.get('mac_address'));

            if (params.has('model')) model = params.get('model');
            else if (params.has('mdl')) model = params.get('mdl');

            if (params.has('ean')) ean = params.get('ean');
            else if (params.has('upc')) ean = params.get('upc');
            else if (params.has('barcode')) ean = params.get('barcode');
        } catch (e) {}
    }

    // 3. Explicit Multi-Attribute Regex Detection across raw text
    // Explicit MAC regex:
    if (!mac) {
        const explicitMacRegex = /(?:MAC[:\s=-]*)?((?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2})\b/i;
        const macMatch = raw.match(explicitMacRegex);
        if (macMatch && macMatch[1]) {
            mac = normalizeMac(macMatch[1]);
        } else {
            const plainMacWithPrefix = /(?:MAC[:\s=-]+)([0-9A-Fa-f]{12})\b/i;
            const plainMatch = raw.match(plainMacWithPrefix);
            if (plainMatch && plainMatch[1]) {
                mac = normalizeMac(plainMatch[1]);
            }
        }
    }

    // Explicit S/N regex
    if (!serial) {
        const explicitSnRegex = /(?:S\/N|SN|SERIAL(?:\s*NO)?|NO)[:\s=-]+([A-Za-z0-9\-_]+)/i;
        const snMatch = raw.match(explicitSnRegex);
        if (snMatch && snMatch[1]) {
            serial = snMatch[1].trim();
        }
    }

    // Explicit EAN / Barcode regex
    if (!ean) {
        const explicitEanRegex = /(?:EAN|UPC|BARCODE)[:\s=-]+(\d{12,14})\b/i;
        const eanMatch = raw.match(explicitEanRegex);
        if (eanMatch && eanMatch[1]) {
            ean = eanMatch[1].trim();
        }
    }

    // Explicit Model regex
    if (!model) {
        const explicitModelRegex = /(?:MODEL|MDL|PROD|ITEM|PART)[:\s=-]+([A-Za-z0-9\-_\.\/]+)/i;
        const modelMatch = raw.match(explicitModelRegex);
        if (modelMatch && modelMatch[1]) {
            model = modelMatch[1].trim();
        }
    }

    // 4. Multiline or Delimited Chunking (Split by newline, semicolon, comma, pipe, tab, or spaces)
    const chunks = (raw.includes('\n') || raw.includes(';') || raw.includes(',') || raw.includes('|') || raw.includes('\t') || raw.includes(' '))
        ? raw.split(/[\r\n,;|/\t\s]+/).map(c => c.trim()).filter(Boolean)
        : [raw];
    if (chunks.length > 1) {
        for (const chunk of chunks) {
            // Check MAC chunk
            if (!mac && isValidMac(chunk)) {
                mac = normalizeMac(chunk);
                continue;
            }
            // Check EAN chunk (12-14 digits)
            if (!ean && /^\d{12,14}$/.test(chunk)) {
                ean = chunk;
                continue;
            }
            // Check S/N chunk
            if (!serial && /^(?:S\/N|SN|SERIAL)[:\s=_-]*/i.test(chunk)) {
                serial = normalizeSerial(chunk);
                continue;
            }
            // Check Model chunk
            if (!model && /^(?:MODEL|MDL|PROD)[:\s=_-]*/i.test(chunk)) {
                model = chunk.replace(/^(?:MODEL|MDL|PROD)[:\s=_-]*/i, '').trim();
                continue;
            }
            // CCTV Model Prefix detection (Hikvision, CP PLUS, Dahua, Uniview, etc.)
            if (!model && /^(?:DS-|CP-|DH-|IPC-|HWI-|UNV-|NVR|DVR)/i.test(chunk)) {
                model = chunk;
                continue;
            }
            // If serial still missing and chunk looks like S/N (6-24 alphanumeric chars)
            if (!serial && !isValidMac(chunk) && !/^\d{12,14}$/.test(chunk) && /^[A-Za-z0-9\-_]{6,26}$/.test(chunk)) {
                serial = normalizeSerial(chunk);
                continue;
            }
        }
    }

    // 5. Standalone / Unprefixed Barcode Identification Fallback
    if (!serial && !mac && !ean) {
        if (isValidMac(raw)) {
            mac = normalizeMac(raw);
        } else if (/^\d{12,14}$/.test(raw)) {
            ean = raw;
        } else {
            serial = normalizeSerial(raw);
        }
    } else if (mac && !serial) {
        let remainder = raw
            .replace(/(?:MAC(?:\s*[:=-]|\s+)?)((?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2})/i, '')
            .replace(/(?:MAC(?:\s*[:=-]|\s+))([0-9A-Fa-f]{12})/i, '')
            .replace(/MAC/i, '')
            .replace(/[,\s;|/]+/g, ' ')
            .trim();
        if (remainder && !isValidMac(remainder)) {
            if (/^\d{12,14}$/.test(remainder)) {
                ean = remainder;
            } else {
                serial = normalizeSerial(remainder);
            }
        }
    }

    // Validate that mac does not contain non-hex characters
    if (mac && !isValidMac(mac)) {
        mac = '';
    }

    const isMacOnly = Boolean(mac && !serial);
    const isSerialOnly = Boolean(serial && !mac);
    const isComposite = Boolean(serial && mac);

    return {
        serial,
        mac,
        model,
        ean,
        isMacOnly,
        isSerialOnly,
        isComposite,
        raw
    };
};

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
        activeTab: defaultTab,
        pollInterval: null
    };

    // Reset detected pill
    const detectedPill = document.getElementById(`${modalId}-detected-pill`);
    if (detectedPill) detectedPill.style.display = 'none';

    if (defaultTab === 'hardware' || defaultTab === 'machine' || defaultTab === 'laptop') {
        switchScannerMode(modalId, 'machine');
    } else {
        switchScannerMode(modalId, 'mobile');
    }
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
        if (state.mobileStream) {
            state.mobileStream.getTracks().forEach(t => t.stop());
            state.mobileStream = null;
        }
        state.track = null;
        if (state.pollInterval) {
            clearInterval(state.pollInterval);
            state.pollInterval = null;
        }
    }
}

function triggerMobileDirectCapture(modalId) {
    const input = document.getElementById(`${modalId}-mobile-capture-fallback`);
    if (input) {
        input.value = '';
        input.click();
    }
}
window.triggerMobileDirectCapture = triggerMobileDirectCapture;

async function handleMobileDirectImageCapture(event, modalId) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;
    const status = document.getElementById(`${modalId}-mobile-cam-status`);
    const preview = document.getElementById(`${modalId}-mobile-cam-preview`);
    const video = document.getElementById(`${modalId}-mobile-video`);
    
    if (preview) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    }
    if (video) video.classList.add('hidden');
    if (status) status.innerText = 'Analyzing barcode from camera...';

    // Decode image directly and invoke handleBarcodeDetected
    handleBarcodeImageUpload(event, modalId);
}
window.handleMobileDirectImageCapture = handleMobileDirectImageCapture;

async function startMobileDirectCamera(modalId, isUserGesture = true) {
    const state = window.barcodeScannerState[modalId];
    const video = document.getElementById(`${modalId}-mobile-video`);
    const status = document.getElementById(`${modalId}-mobile-cam-status`);
    const preview = document.getElementById(`${modalId}-mobile-cam-preview`);
    if (!video) return;

    const hasGetUserMedia = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

    if (!hasGetUserMedia) {
        if (isUserGesture) {
            if (status) status.innerText = 'Opening mobile camera...';
            triggerMobileDirectCapture(modalId);
        } else {
            if (status) status.innerText = 'Camera Ready — Tap to scan barcode';
        }
        return;
    }

    try {
        if (state && state.mobileStream) {
            state.mobileStream.getTracks().forEach(t => t.stop());
            state.mobileStream = null;
        }

        const facing = (state && state.facingMode) || 'environment';
        const stream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: { ideal: facing },
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            },
            audio: false
        });

        if (state) state.mobileStream = stream;
        video.srcObject = stream;
        video.classList.remove('hidden');
        if (preview) preview.classList.add('hidden');
        await video.play();
        if (status) status.innerText = 'Point camera at CCTV barcode...';

        const scanLoop = async () => {
            const modal = document.getElementById(modalId);
            if (!modal || modal.style.display === 'none' || (state && state.activeTab !== 'mobile')) {
                stream.getTracks().forEach(t => t.stop());
                if (state) state.mobileStream = null;
                return;
            }

            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                let detected = null;
                if ('BarcodeDetector' in window) {
                    try {
                        const detector = new BarcodeDetector({
                            formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'qr_code', 'upc_a', 'upc_e', 'data_matrix']
                        });
                        const barcodes = await detector.detect(video);
                        if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                            detected = barcodes[0].rawValue;
                        }
                    } catch (e) {}
                }

                if (!detected && window.ZXing && window.ZXing.BrowserMultiFormatReader) {
                    try {
                        const reader = new window.ZXing.BrowserMultiFormatReader();
                        const res = await reader.decodeFromVideoElement(video);
                        if (res && res.getText()) detected = res.getText();
                    } catch (e) {}
                }

                if (detected) {
                    stream.getTracks().forEach(t => t.stop());
                    if (state) state.mobileStream = null;
                    handleBarcodeDetected(modalId, detected);
                    return;
                }
            }

            requestAnimationFrame(scanLoop);
        };
        requestAnimationFrame(scanLoop);
    } catch (err) {
        console.warn('Failed to start mobile direct stream, falling back to camera snap:', err);
        if (isUserGesture) {
            if (status) status.innerText = 'Opening mobile camera...';
            triggerMobileDirectCapture(modalId);
        } else {
            if (status) status.innerText = 'Camera Ready — Tap to scan barcode';
        }
    }
}
window.startMobileDirectCamera = startMobileDirectCamera;

function switchMobileDirectCamera(modalId) {
    const state = window.barcodeScannerState[modalId];
    if (!state) return;
    state.facingMode = (state.facingMode === 'user') ? 'environment' : 'user';
    startMobileDirectCamera(modalId, true);
}
window.switchMobileDirectCamera = switchMobileDirectCamera;

function switchScannerMode(modalId, mode) {
    const state = window.barcodeScannerState[modalId];
    if (!state) return;
    const isMachine = (mode === 'machine' || mode === 'hardware' || mode === 'laptop');
    state.activeTab = isMachine ? 'machine' : 'mobile';

    const laptopTab = document.getElementById(`${modalId}-tab-laptop`);
    const mobileTab = document.getElementById(`${modalId}-tab-mobile`);
    const laptopPane = document.getElementById(`${modalId}-laptop-pane`);
    const mobilePane = document.getElementById(`${modalId}-mobile-pane`);
    const desktopView = document.getElementById(`${modalId}-mobile-desktop-view`);
    const phoneView = document.getElementById(`${modalId}-mobile-phone-view`);

    if (isMachine) {
        // Machine / Scanner Gun Tab Active
        if (laptopTab) {
            laptopTab.style.cssText = '';
            laptopTab.className = "px-4 py-2.5 text-sm font-semibold border-b-2 border-[var(--crm-accent,#2563eb)] text-[var(--crm-accent,#2563eb)] -mb-px cursor-pointer transition-colors";
        }
        if (mobileTab) {
            mobileTab.style.cssText = '';
            mobileTab.className = "px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-slate-600 dark:text-slate-400 -mb-px cursor-pointer hover:text-slate-900 dark:hover:text-slate-100 transition-colors";
        }
        if (laptopPane) laptopPane.style.display = 'block';
        if (mobilePane) mobilePane.style.display = 'none';

        if (state.pollInterval) {
            clearInterval(state.pollInterval);
            state.pollInterval = null;
        }

        // Auto-focus hardware barcode scanner gun input
        setTimeout(() => {
            const manualInp = document.getElementById(`${modalId}-manual-input`);
            if (manualInp) {
                manualInp.focus();
                manualInp.select();
            }
        }, 120);
    } else {
        // Mobile Based Scan Tab Active
        if (laptopTab) {
            laptopTab.style.cssText = '';
            laptopTab.className = "px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-slate-600 dark:text-slate-400 -mb-px cursor-pointer hover:text-slate-900 dark:hover:text-slate-100 transition-colors";
        }
        if (mobileTab) {
            mobileTab.style.cssText = '';
            mobileTab.className = "px-4 py-2.5 text-sm font-semibold border-b-2 border-[var(--crm-accent,#2563eb)] text-[var(--crm-accent,#2563eb)] -mb-px cursor-pointer transition-colors";
        }
        if (laptopPane) laptopPane.style.display = 'none';
        if (mobilePane) mobilePane.style.display = 'block';

        // Check if on a mobile browser or laptop/desktop
        const onMobileDevice = isMobileScannerDevice();
        if (onMobileDevice) {
            if (desktopView) desktopView.style.display = 'none';
            if (phoneView) phoneView.style.display = 'block';
            setTimeout(() => startMobileDirectCamera(modalId, false), 150);
        } else {
            if (desktopView) desktopView.style.display = 'block';
            if (phoneView) phoneView.style.display = 'none';
        }

        // Stop laptop camera stream
        if (state.animFrame) {
            cancelAnimationFrame(state.animFrame);
            state.animFrame = null;
        }
        if (state.stream) {
            state.stream.getTracks().forEach(t => t.stop());
            state.stream = null;
            state.track = null;
        }
        const video = document.getElementById(`${modalId}-video`);
        if (video) video.srcObject = null;

        // Request server-generated pairing token and render QR locally
        const modal = document.getElementById(modalId);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch('{{ route("scanner.pairings.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({})
        })
        .then(r => r.ok ? r.json() : Promise.reject(new Error('Pairing failed')))
        .then(async pairing => {
            const sessionToken = pairing.token;
            state.sessionToken = sessionToken;
            const baseUrl = pairing.url || `${window.location.origin}/mobile-scanner/${sessionToken}`;
            const sep = baseUrl.includes('?') ? '&' : '?';
            const mobileUrl = `${baseUrl}${sep}target=${encodeURIComponent(state.targetInputId || 'serial_number')}`;

            const qrImg = document.getElementById(`${modalId}-qr-img`);
            if (qrImg && window.QRCode && typeof window.QRCode.toDataURL === 'function') {
                qrImg.src = await window.QRCode.toDataURL(mobileUrl, { width: 220, margin: 1 });
            }
            const directLink = document.getElementById(`${modalId}-direct-mobile-link`);
            if (directLink) {
                directLink.href = mobileUrl;
            }

            if (state.pollInterval) clearInterval(state.pollInterval);
            const mobileStatus = document.getElementById(`${modalId}-mobile-status`);
            state.pollInterval = setInterval(async () => {
                try {
                    const res = await fetch(`/api/mobile-scanner/poll/${sessionToken}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    const scannedCode = (Array.isArray(data.codes) && data.codes.length > 0)
                        ? data.codes[data.codes.length - 1].code
                        : data.code;
                    if (data && data.status === 'scanned' && scannedCode) {
                        clearInterval(state.pollInterval);
                        state.pollInterval = null;
                        if (mobileStatus) {
                            mobileStatus.innerHTML = `<span class="text-emerald-600 dark:text-emerald-400 font-bold">Scanned from phone.</span>`;
                        }
                        handleBarcodeDetected(modalId, scannedCode);
                    } else if (data && data.status === 'connected') {
                        if (mobileStatus) {
                            mobileStatus.innerHTML = `<span class="text-emerald-600 dark:text-emerald-400 font-semibold">Phone linked. Point camera at barcode.</span>`;
                        }
                    }
                } catch (err) {}
            }, 1200);
        })
        .catch(() => {});
    }
}

async function updateScannerQrUrl(modalId) {
    const state = window.barcodeScannerState[modalId];
    if (!state || !state.sessionToken) return;
    const modal = document.getElementById(modalId);
    const customIpEl = document.getElementById(`${modalId}-custom-ip`);
    let hostAndPort = customIpEl && customIpEl.value.trim() ? customIpEl.value.trim() : '';
    if (!hostAndPort) {
        const lanIp = (modal && modal.dataset.lanIp) ? modal.dataset.lanIp : window.location.hostname;
        const port = window.location.port ? (':' + window.location.port) : '';
        hostAndPort = lanIp + port;
    }
    hostAndPort = hostAndPort.replace(/^https?:\/\//i, '');

    const mobileUrl = `${window.location.protocol}//${hostAndPort}/mobile-scanner/${state.sessionToken}?target=${encodeURIComponent(state.targetInputId || 'serial_number')}`;
    const qrImg = document.getElementById(`${modalId}-qr-img`);
    if (qrImg && window.QRCode && typeof window.QRCode.toDataURL === 'function') {
        qrImg.src = await window.QRCode.toDataURL(mobileUrl, { width: 220, margin: 1 });
    }
    const directLink = document.getElementById(`${modalId}-direct-mobile-link`);
    if (directLink) {
        directLink.href = mobileUrl;
    }
    const mobileStatus = document.getElementById(`${modalId}-mobile-status`);
    if (mobileStatus) {
        mobileStatus.innerHTML = `<span class="text-blue-600 dark:text-blue-400 font-semibold">QR updated! Scan with phone...</span>`;
    }
}

// Explicit global exposure on window
window.openBarcodeScanner = openBarcodeScanner;
window.closeBarcodeScanner = closeBarcodeScanner;
window.switchScannerMode = switchScannerMode;
window.updateScannerQrUrl = updateScannerQrUrl;

function switchScannerTab(modalId, tab) {
    switchScannerMode(modalId, tab);
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

let zxingMultiReader = null;
let isDecodingFrame = false;
let lastDecodeTimestamp = 0;

function getZxingReader() {
    if (!zxingMultiReader && window.ZXing && window.ZXing.BrowserMultiFormatReader) {
        try {
            zxingMultiReader = new window.ZXing.BrowserMultiFormatReader();
        } catch (e) {
            console.warn('Could not instantiate ZXing reader:', e);
        }
    }
    return zxingMultiReader;
}

async function scanBarcodeLoop(modalId) {
    const state = window.barcodeScannerState[modalId];
    const video = document.getElementById(`${modalId}-video`);
    if (!video || !state || !state.stream) return;

    const now = Date.now();
    // Throttle decoding attempts to once every 120ms to keep CPU silky smooth
    if (!isDecodingFrame && (now - lastDecodeTimestamp > 120) && video.readyState >= 2 && video.videoWidth > 0) {
        isDecodingFrame = true;
        lastDecodeTimestamp = now;

        try {
            // 1. First attempt: Native BarcodeDetector (Chrome Android / macOS where available)
            if ('BarcodeDetector' in window) {
                try {
                    const detector = new BarcodeDetector({
                        formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'qr_code', 'upc_a', 'upc_e', 'data_matrix']
                    });
                    const barcodes = await detector.detect(video);
                    if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                        isDecodingFrame = false;
                        handleBarcodeDetected(modalId, barcodes[0].rawValue);
                        return;
                    }
                } catch (e) {}
            }

            // 2. Universal fallback: ZXing BrowserMultiFormatReader (PC, Windows, Linux, Mac laptop webcams)
            const reader = getZxingReader();
            if (reader) {
                try {
                    const result = await reader.decodeFromVideoElement(video);
                    if (result && result.getText()) {
                        isDecodingFrame = false;
                        handleBarcodeDetected(modalId, result.getText());
                        return;
                    }
                } catch (zxErr) {
                    // NotFoundException is normal when no barcode is in view
                }
            }
        } catch (err) {
            console.debug('Scan frame error:', err);
        } finally {
            isDecodingFrame = false;
        }
    }

    state.animFrame = requestAnimationFrame(() => scanBarcodeLoop(modalId));
}

function highlightScannerInput(input) {
    if (!input) return;
    input.style.transition = 'all 0.3s ease';
    input.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.45)';
    input.style.borderColor = '#10b981';
    setTimeout(() => {
        input.style.boxShadow = '';
        input.style.borderColor = '';
    }, 3000);
}

async function handleBarcodeDetected(modalId, code) {
    const cleanCode = (code || '').trim();
    if (!cleanCode) return;
    const state = window.barcodeScannerState[modalId];
    const targetId = state ? state.targetInputId : null;
    const parsed = window.parseDeviceBarcode(cleanCode);

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

    const serialInput = document.getElementById('serial_number_input') || (targetId === 'serial_number_input' ? document.getElementById(targetId) : null);
    const macInput = document.getElementById('mac_address_input') || (targetId === 'mac_address_input' ? document.getElementById(targetId) : null);

    const detectedPill = document.getElementById(`${modalId}-detected-pill`);
    const detectedText = document.getElementById(`${modalId}-detected-text`);
    const statusOverlay = document.getElementById(`${modalId}-status`);

    const isStrictMac = (str) => {
        if (!str) return false;
        const s = String(str).replace(/^MAC[:\s=-]*/i, '').trim();
        return /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/.test(s) ||
               (/^[0-9A-Fa-f]{12}$/.test(s) && /[A-Fa-f]/.test(s));
    };

    const isStrictEan = (str) => {
        return /^\d{12,14}$/.test(String(str).trim());
    };

    // SINGLE-SCAN MULTI-DATA POPULATION:
    // Strictly assign S/N, MAC, and EAN to their proper fields without cross-pollution
    if (parsed.mac && isStrictMac(parsed.mac)) {
        if (macInput) {
            macInput.value = parsed.mac;
            macInput.dispatchEvent(new Event('input', { bubbles: true }));
            macInput.dispatchEvent(new Event('change', { bubbles: true }));
            highlightScannerInput(macInput);
        }
    } else if (isStrictMac(cleanCode)) {
        let normalized = cleanCode.replace(/^MAC[:\s=-]*/i, '').trim().toUpperCase();
        if (normalized.length === 12 && !normalized.includes(':')) {
            normalized = normalized.match(/.{1,2}/g).join(':');
        }
        if (macInput) {
            macInput.value = normalized;
            macInput.dispatchEvent(new Event('input', { bubbles: true }));
            macInput.dispatchEvent(new Event('change', { bubbles: true }));
            highlightScannerInput(macInput);
        }
    }

    if (parsed.serial && !isStrictMac(parsed.serial)) {
        if (serialInput) {
            if (!isStrictEan(parsed.serial) || !serialInput.value.trim()) {
                serialInput.value = parsed.serial;
                serialInput.dispatchEvent(new Event('input', { bubbles: true }));
                serialInput.dispatchEvent(new Event('change', { bubbles: true }));
                highlightScannerInput(serialInput);
            }
        } else if (targetId && targetId !== 'mac_address_input') {
            const el = document.getElementById(targetId);
            if (el) {
                el.value = parsed.serial;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
                highlightScannerInput(el);
            }
        }
    } else if (!isStrictMac(cleanCode)) {
        const cleanRaw = cleanCode.replace(/^(?:S\/N|SN|SERIAL)[:\s=-]*/i, '').trim();
        if (serialInput) {
            if (!isStrictEan(cleanRaw) || !serialInput.value.trim()) {
                serialInput.value = cleanRaw;
                serialInput.dispatchEvent(new Event('input', { bubbles: true }));
                serialInput.dispatchEvent(new Event('change', { bubbles: true }));
                highlightScannerInput(serialInput);
            }
        } else if (targetId && targetId !== 'mac_address_input') {
            const el = document.getElementById(targetId);
            if (el) {
                el.value = cleanRaw;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
                highlightScannerInput(el);
            }
        }
    }

    // Automatic Product & Equipment Name Lookup via API
    let lookupResult = null;
    const queryParams = new URLSearchParams();
    if (parsed.serial) queryParams.set('serial', parsed.serial);
    if (parsed.mac) queryParams.set('mac', parsed.mac);
    if (parsed.ean) queryParams.set('ean', parsed.ean);
    if (parsed.model) queryParams.set('model', parsed.model);
    if (!parsed.serial && !parsed.mac && !parsed.ean) queryParams.set('serial', cleanCode);

    try {
        const lookupRes = await fetch(`/api/equipment/lookup-serial?${queryParams.toString()}`);
        if (lookupRes.ok) {
            lookupResult = await lookupRes.json();
            if (lookupResult) {
                const autoName = lookupResult.equipment_name || lookupResult.product_name || lookupResult.model || parsed.model;
                const nameInput = document.getElementById('equipment_name_input') || document.querySelector('input[name="equipment_name"]');
                if (nameInput && autoName) {
                    nameInput.value = autoName;
                    nameInput.dispatchEvent(new Event('input', { bubbles: true }));
                    nameInput.dispatchEvent(new Event('change', { bubbles: true }));
                    highlightScannerInput(nameInput);
                }

                if (serialInput && (lookupResult.serial || lookupResult.serial_number) && !serialInput.value) {
                    serialInput.value = lookupResult.serial || lookupResult.serial_number;
                    serialInput.dispatchEvent(new Event('input', { bubbles: true }));
                    serialInput.dispatchEvent(new Event('change', { bubbles: true }));
                    highlightScannerInput(serialInput);
                }

                const resMac = lookupResult.mac || lookupResult.mac_address;
                if (macInput && resMac && !macInput.value) {
                    macInput.value = resMac;
                    macInput.dispatchEvent(new Event('input', { bubbles: true }));
                    macInput.dispatchEvent(new Event('change', { bubbles: true }));
                    highlightScannerInput(macInput);
                }

                const productSelect = document.querySelector('select[name="product_id"]');
                if (productSelect && lookupResult.product_id && !productSelect.value) {
                    productSelect.value = lookupResult.product_id;
                    productSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }
    } catch (e) {}

    // Fallback model population from parsed barcode
    if (parsed.model) {
        const nameInput = document.getElementById('equipment_name_input') || document.querySelector('input[name="equipment_name"]');
        if (nameInput && !nameInput.value) {
            nameInput.value = parsed.model;
            nameInput.dispatchEvent(new Event('input', { bubbles: true }));
            nameInput.dispatchEvent(new Event('change', { bubbles: true }));
            highlightScannerInput(nameInput);
        }
    }

    // Multi-attribute preview inside modal
    const previewContainer = document.getElementById(`${modalId}-multi-preview`);
    if (previewContainer) {
        previewContainer.innerHTML = '';
        if (parsed.serial) {
            previewContainer.innerHTML += `<div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 shadow-xs"><div class="text-[10px] text-slate-500 font-sans font-bold uppercase">Hardware S/N</div><div class="text-xs font-bold text-emerald-950 dark:text-emerald-200 truncate">${parsed.serial}</div></div>`;
        }
        if (parsed.mac) {
            previewContainer.innerHTML += `<div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 shadow-xs"><div class="text-[10px] text-slate-500 font-sans font-bold uppercase">MAC Address</div><div class="text-xs font-bold text-emerald-950 dark:text-emerald-200 truncate">${parsed.mac}</div></div>`;
        }
        if (parsed.ean) {
            previewContainer.innerHTML += `<div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 shadow-xs"><div class="text-[10px] text-slate-500 font-sans font-bold uppercase">EAN / Barcode</div><div class="text-xs font-bold text-emerald-950 dark:text-emerald-200 truncate">${parsed.ean}</div></div>`;
        }
        const modelDisplay = (lookupResult && (lookupResult.equipment_name || lookupResult.product_name)) ? (lookupResult.equipment_name || lookupResult.product_name) : parsed.model;
        if (modelDisplay) {
            previewContainer.innerHTML += `<div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 shadow-xs"><div class="text-[10px] text-slate-500 font-sans font-bold uppercase">Product Model</div><div class="text-xs font-bold text-emerald-950 dark:text-emerald-200 truncate">${modelDisplay}</div></div>`;
        }
    }

    if (detectedText) {
        detectedText.innerText = `Captured Tag: ${cleanCode.substring(0, 60)}${cleanCode.length > 60 ? '...' : ''}`;
    }
    if (detectedPill) {
        detectedPill.style.display = 'flex';
    }

    // Call custom callback if provided
    if (state && typeof state.callback === 'function') {
        state.callback(cleanCode, parsed, lookupResult, targetId);
    } else if (typeof window.onBarcodeScanned === 'function') {
        window.onBarcodeScanned(cleanCode, modalId, parsed, lookupResult, targetId);
    }

    // Single-scan complete: close modal after brief 950ms visual confirmation
    setTimeout(() => {
        closeBarcodeScanner(modalId);
    }, 950);
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

    // ZXing canvas frame decoding fallback
    const snapReader = getZxingReader();
    if (snapReader) {
        try {
            const result = await snapReader.decodeFromCanvas(canvas);
            if (result && result.getText()) {
                handleBarcodeDetected(modalId, result.getText());
                return;
            }
        } catch (e) {}
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

        // ZXing image decoding fallback
        const uploadReader = getZxingReader();
        if (uploadReader) {
            try {
                const result = await uploadReader.decodeFromImageElement(img);
                if (result && result.getText()) {
                    handleBarcodeDetected(modalId, result.getText());
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
