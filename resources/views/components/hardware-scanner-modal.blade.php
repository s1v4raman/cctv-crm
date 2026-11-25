@props([
    'modalId' => 'hardwareScannerModal',
    'projects' => collect(),
    'leads' => collect(),
])
@php
    $isLocalEnv = app()->environment('local');
    $lanIp = $isLocalEnv ? \App\Http\Controllers\MobileScannerSyncController::detectLanIp() : (request()?->getHost() ?? 'localhost');
    $scannerConfig = [
        'modalId' => $modalId,
        'isLocalEnv' => $isLocalEnv,
        'routes' => [
            'lookup' => route('equipment.lookup'),
            'lookupPost' => route('equipment.lookup.post'),
            'createPairing' => route('scanner.pairings.store'),
            'targets' => route('scanner.targets'),
            'store' => route('equipment.store'),
            'sitesCreate' => route('sites.create'),
        ],
    ];
@endphp

<script>
    // Cleanup candidate: thin helper kept for backward compatibility
    window.triggerAddCustomerSiteFromScanner = function() {
        if (typeof openQuickAddSiteModal === 'function') {
            openQuickAddSiteModal();
        } else {
            window.open("{{ route('sites.create') }}", '_blank');
        }
    };
</script>

{{-- HARDWARE BARCODE SCANNER & MULTI-CODE REVIEW MODAL --}}
<div id="{{ $modalId }}"
     x-data="hardwareScannerModalComponent(@js($scannerConfig))"
     x-init="initModal(); $watch('isOpen', value => { if (value && targetResults.length === 0) searchTargets(''); });"
     x-show="isOpen"
     x-cloak
     data-lan-ip="{{ $lanIp }}"
     @keydown.escape.window="if (isOpen) closeModal()"
     class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">

    <div class="crm-card bg-white dark:bg-slate-900 rounded-lg sm:rounded-[4px] max-w-xl w-full p-4 sm:p-6 border border-slate-200 dark:border-slate-800 my-auto flex flex-col space-y-4">

        {{-- Modal Top Header Bar --}}
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg sm:rounded-[4px] border border-slate-200 dark:border-slate-700 text-[var(--crm-accent,#2563eb)] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">
                        <span>{{ __('Hardware Barcode Scanner & Analysis') }}</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Scan EAN, S/N, and MAC in one session, review and save') }}</p>
                </div>
            </div>
            <x-close-button @click="closeModal()" :label="__('Close')" />
        </div>

        {{-- Plain Text Tabs with 2px Underline --}}
        <div class="flex items-center gap-4 border-b border-slate-200 dark:border-slate-700 text-xs overflow-x-auto select-none">
            <button type="button" @click="switchTab('mobile')"
                    class="py-2.5 px-1 font-semibold border-b-2 transition whitespace-nowrap cursor-pointer"
                    :class="activeTab === 'mobile' ? 'border-[var(--crm-accent,#2563eb)] text-[var(--crm-accent,#2563eb)]' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                {{ __('Mobile Scan') }}
            </button>
            <button type="button" @click="switchTab('scanner_gun')"
                    class="py-2.5 px-1 font-semibold border-b-2 transition whitespace-nowrap cursor-pointer"
                    :class="activeTab === 'scanner_gun' ? 'border-[var(--crm-accent,#2563eb)] text-[var(--crm-accent,#2563eb)]' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                {{ __('Barcode Machine') }}
            </button>
            <button type="button" @click="switchTab('lookup')"
                    class="py-2.5 px-1 font-semibold border-b-2 transition whitespace-nowrap cursor-pointer"
                    :class="activeTab === 'lookup' ? 'border-[var(--crm-accent,#2563eb)] text-[var(--crm-accent,#2563eb)]' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                {{ __('Analysis & S/N') }}
            </button>
            <button type="button" @click="switchTab('register')"
                    class="py-2.5 px-1 font-semibold border-b-2 transition whitespace-nowrap cursor-pointer"
                    :class="activeTab === 'register' ? 'border-[var(--crm-accent,#2563eb)] text-[var(--crm-accent,#2563eb)]' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                {{ __('Register Asset') }}
            </button>
        </div>

        {{-- Save Feedback Status Text --}}
        <template x-if="saveSuccess">
            <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400" x-text="saveSuccess"></p>
        </template>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- SECTION 0: MOBILE SCAN (MULTI-CODE SESSION / QR SYNC)       --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'mobile'" class="space-y-4">

            {{-- Desktop QR Pairing Card --}}
            <div x-show="!isMobile && !useDirectCamera" class="p-4 sm:p-5 rounded-lg sm:rounded-[4px] bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-center space-y-3">
                <div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Scan Hardware with Smartphone Camera') }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                        {{ __('Scan this QR code with your phone. Scanned EAN, S/N, and MAC codes stream live into the session list below.') }}
                    </p>
                </div>

                <div class="flex flex-col items-center justify-center py-1">
                    <div class="p-2.5 bg-white rounded-lg sm:rounded-[4px] border border-slate-200 dark:border-slate-700 inline-block">
                        <img id="{{ $modalId }}-qr-img" :src="mobileQrUrl" alt="Mobile Scanner QR Code" class="w-40 h-40 object-contain">
                    </div>

                    <div class="mt-2.5 text-xs font-semibold">
                        <template x-if="mobileStatus === 'waiting'">
                            <span class="text-slate-600 dark:text-slate-400">{{ __('Waiting for smartphone connection...') }}</span>
                        </template>
                        <template x-if="mobileStatus === 'connected'">
                            <span class="text-emerald-700 dark:text-emerald-400">{{ __('Phone linked. Scan barcodes on device box.') }}</span>
                        </template>
                        <template x-if="mobileStatus === 'scanned'">
                            <span class="text-emerald-700 dark:text-emerald-400">{{ __('Receiving live scans into session.') }}</span>
                        </template>
                    </div>
                </div>

                @if($isLocalEnv)
                    <div class="p-3 bg-white dark:bg-slate-900/80 rounded-lg sm:rounded-[4px] text-left space-y-2 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <label class="text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ __('Server Wi-Fi Address / Host:') }}</label>
                            <span class="text-[11px] text-slate-500">{{ __('Local dev only') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" x-model="customHostAndPort"
                                   class="flex-1 h-[52px] sm:h-[38px] text-xs rounded-lg sm:rounded-[4px] border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 px-3 font-mono text-slate-900 dark:text-slate-100">
                            <button type="button" @click="updateQrUrl()"
                                    class="btn-primary h-[52px] sm:h-[38px] px-3 rounded-lg sm:rounded-[4px] text-xs font-bold cursor-pointer shrink-0">
                                <span>{{ __('Update QR') }}</span>
                            </button>
                        </div>
                    </div>
                @endif

                <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs flex-wrap gap-2">
                    <button type="button" @click="enableDirectCamera()"
                            class="btn-primary h-[38px] px-3.5 rounded-[4px] font-bold text-xs inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                        <span>{{ __('Scan Directly with Web Camera') }}</span>
                    </button>
                    <div class="flex items-center gap-3">
                        <a :href="mobileUrl" target="_blank" class="text-[var(--crm-accent,#2563eb)] font-semibold hover:underline">
                            <span>{{ __('Open in New Tab') }}</span>
                        </a>
                        <button type="button" @click="switchTab('scanner_gun')" class="text-slate-600 dark:text-slate-400 hover:underline cursor-pointer">
                            {{ __('Scanner Gun?') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Continuous Live Camera Scanner (Mobile or Direct Webcam) --}}
            <div x-show="isMobile || useDirectCamera" class="space-y-3">
                <template x-if="cameraError">
                    <p class="text-xs font-semibold text-rose-700 dark:text-rose-400" x-text="cameraError"></p>
                </template>

                {{-- Camera Controls Bar (Device Selector / Flip / Mode Toggle) --}}
                <div class="flex items-center justify-between flex-wrap gap-2 text-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- Video Device Enumeration Dropdown --}}
                        <template x-if="videoDevices.length > 1">
                            <select x-model="selectedCameraId" @change="switchCameraDevice(selectedCameraId)"
                                    class="h-[38px] text-xs rounded-[4px] border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-2 font-medium focus:ring-1 focus:ring-blue-500">
                                <template x-for="(dev, idx) in videoDevices" :key="dev.deviceId || idx">
                                    <option :value="dev.deviceId" x-text="dev.label || ('Camera ' + (idx + 1))"></option>
                                </template>
                            </select>
                        </template>

                        {{-- Flip Front/Rear Camera --}}
                        <button type="button" @click="switchCameraFacing()"
                                class="h-[38px] px-2.5 rounded-[4px] border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold inline-flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                            <span>{{ __('Flip') }}</span>
                        </button>

                        {{-- Torch --}}
                        <button type="button" x-show="torchSupported" @click="toggleTorch()"
                                class="h-[38px] px-2.5 rounded-[4px] border border-slate-300 dark:border-slate-700 text-xs font-bold inline-flex items-center gap-1 cursor-pointer"
                                :class="torchOn ? 'border-[var(--crm-accent,#2563eb)] text-[var(--crm-accent,#2563eb)]' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                            <span>{{ __('Torch') }}</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" x-show="!isMobile && useDirectCamera" @click="disableDirectCamera()"
                                class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 underline cursor-pointer">
                            {{ __('Switch to Phone QR Sync') }}
                        </button>
                    </div>
                </div>

                {{-- Viewport --}}
                <div id="{{ $modalId }}-phone-viewport"
                     @click="startMobileCamera(true)"
                     class="relative bg-black rounded-lg sm:rounded-[4px] overflow-hidden w-full h-[240px] flex items-center justify-center border border-slate-800 select-none cursor-pointer">
                    <video id="{{ $modalId }}-phone-video" autoplay playsinline muted class="w-full h-full object-cover"></video>
                    <input type="file" id="{{ $modalId }}-phone-capture-fallback" accept="image/*" capture="environment" class="hidden" @change="uploadImage($event)">

                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center p-4">
                        <div class="w-full max-w-[240px] h-[120px] rounded-[4px] border border-white/60"></div>
                    </div>

                    <div class="absolute bottom-2 left-2 right-2 text-center text-xs font-medium px-2.5 py-1 rounded-[4px] bg-slate-900/90 text-slate-200" x-text="cameraStatus"></div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="startMobileCamera(true)"
                            class="btn-primary flex-1 h-[52px] sm:h-[38px] px-3 rounded-lg sm:rounded-[4px] font-bold text-xs inline-flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                        <span>{{ __('Start Camera') }}</span>
                    </button>
                    <button type="button" @click="finishCameraSession()"
                            class="btn-primary h-[52px] sm:h-[38px] px-4 rounded-lg sm:rounded-[4px] font-bold text-xs cursor-pointer">
                        <span>{{ __('Done') }} (<span x-text="collectedCodes.length"></span>)</span>
                    </button>
                    <button type="button" @click="switchTab('register')"
                            class="h-[52px] sm:h-[38px] px-3 rounded-lg sm:rounded-[4px] border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs cursor-pointer">
                        <span>{{ __('Enter Manually') }}</span>
                    </button>
                </div>
            </div>

            {{-- Live Collected Codes Session List --}}
            <div x-show="collectedCodes.length > 0" class="p-3 rounded-lg sm:rounded-[4px] bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ __('Collected Codes This Session') }} (<span x-text="collectedCodes.length"></span>)</span>
                    <button type="button" @click="finishCameraSession()"
                            class="btn-primary h-[38px] px-3 rounded-lg sm:rounded-[4px] font-bold text-xs cursor-pointer">
                        <span>{{ __('Review & Save') }}</span>
                    </button>
                </div>
                <div class="space-y-1 max-h-32 overflow-y-auto">
                    <template x-for="(item, idx) in collectedCodes" :key="item.id">
                        <div class="flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-lg sm:rounded-[4px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono">
                            <div class="truncate">
                                <span class="text-[11px] uppercase px-1.5 py-0.5 rounded-[4px] bg-slate-100 dark:bg-slate-800 text-slate-500 mr-1.5" x-text="item.format || item.source"></span>
                                <span class="text-slate-900 dark:text-white" x-text="item.raw"></span>
                            </div>
                            <button type="button" @click="removeCollectedCode(idx)" class="text-rose-600 hover:text-rose-800 font-bold px-1 cursor-pointer">&times;</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- SECTION 1: HARDWARE ANALYSIS & SERIAL NUMBER LOOKUP         --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'lookup'" class="space-y-4">
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block">
                    {{ __('Hardware Serial Number, Barcode, or MAC Address') }} <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="text" x-model="lookupInput"
                           placeholder="{{ __('Type or scan barcode (e.g. SN-88412)...') }}"
                           @keydown.enter.prevent="executeLookup()"
                           class="flex-1 h-[52px] sm:h-[38px] text-xs sm:text-sm rounded-lg sm:rounded-[4px] border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3 font-mono">
                    <button type="button" @click="executeLookup()" :disabled="isLoading"
                            class="btn-primary h-[52px] sm:h-[38px] px-4 rounded-lg sm:rounded-[4px] text-xs sm:text-sm font-bold cursor-pointer shrink-0 disabled:opacity-50">
                        <span x-show="!isLoading">{{ __('Analyze') }}</span>
                        <span x-show="isLoading">{{ __('Searching...') }}</span>
                    </button>
                </div>
            </div>

            <template x-if="hasResult && resultData">
                <div class="p-4 rounded-lg sm:rounded-[4px] border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 text-xs sm:text-sm space-y-3">
                    <div class="flex items-center justify-between font-bold">
                        <span class="text-slate-900 dark:text-white" x-text="resultData.equipment_name || resultData.product_name || 'Hardware Analysis'"></span>
                        <span class="text-xs font-semibold"
                              :class="resultData.is_duplicate ? 'text-rose-700 dark:text-rose-400' : (resultData.found ? 'text-emerald-700 dark:text-emerald-400' : 'text-orange-600 dark:text-orange-400')"
                              x-text="resultData.is_duplicate ? 'Duplicate S/N' : (resultData.found ? 'Matched' : 'Pick Model')"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs font-mono">
                        <div>S/N: <strong x-text="resultData.serial || lastQueriedCode || '—'"></strong></div>
                        <div x-show="resultData.mac">MAC: <strong x-text="resultData.mac"></strong></div>
                        <div x-show="resultData.ean">EAN: <strong x-text="resultData.ean"></strong></div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" @click="prefillAndRegister()"
                                class="btn-primary h-[52px] sm:h-[38px] px-3.5 rounded-lg sm:rounded-[4px] text-xs font-bold cursor-pointer">
                            <span>{{ __('Review & Register') }}</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- SECTION 2: BARCODE MACHINE SCAN (SCANNER GUN INPUT)         --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'scanner_gun'" class="space-y-4">
            <div class="p-4 rounded-lg sm:rounded-[4px] bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-center space-y-3">
                <div class="w-9 h-9 mx-auto rounded-lg sm:rounded-[4px] border border-slate-200 dark:border-slate-700 text-[var(--crm-accent,#2563eb)] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Barcode Machine Scanner Station') }}</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    {{ __('Pull the trigger on your USB or Bluetooth scanner gun for EAN, S/N, and MAC. Tap Review when finished.') }}
                </p>
                <div class="max-w-md mx-auto pt-2 flex gap-2">
                    <input type="text" x-ref="gunInput" x-model="gunInput"
                           @keydown.enter.prevent="processGunScan()"
                           placeholder="{{ __('Pull scanner gun trigger or type S/N...') }}"
                           class="flex-1 h-[52px] sm:h-[38px] text-center text-sm font-mono rounded-lg sm:rounded-[4px] border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3">
                    <button type="button" @click="switchTab('register')"
                            class="btn-primary h-[52px] sm:h-[38px] px-3.5 rounded-lg sm:rounded-[4px] text-xs font-bold cursor-pointer">
                        <span>{{ __('Review') }} (<span x-text="collectedCodes.length"></span>)</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- SECTION 3: REVIEW CARD & REGISTER NEW HARDWARE ASSET        --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'register'" class="space-y-4">

            @if($errors->any())
                <div class="text-xs text-rose-700 dark:text-rose-400 space-y-1">
                    @foreach($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <template x-if="saveError">
                <p class="text-xs font-semibold text-rose-700 dark:text-rose-400" x-text="saveError"></p>
            </template>

            {{-- Duplicate Warning --}}
            <div x-show="resultData?.is_duplicate" class="p-3 rounded-lg sm:rounded-[4px] border border-rose-300 dark:border-rose-700 text-rose-700 dark:text-rose-400 text-xs font-semibold space-y-0.5">
                <div class="font-bold uppercase tracking-wide">{{ __('Duplicate device detected') }}</div>
                <p x-text="resultData?.duplicate_warning || 'Serial or MAC is already registered.'"></p>
            </div>

            {{-- Model Candidates (Never auto-selected) --}}
            <div x-show="resultData?.model_not_found" class="p-3 rounded-lg sm:rounded-[4px] border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 text-xs space-y-2">
                <div class="flex items-center justify-between">
                    <strong class="text-slate-800 dark:text-slate-200">{{ __('Model not found — pick a candidate or enter model:') }}</strong>
                    <span class="font-semibold text-orange-600 dark:text-orange-400">Check this</span>
                </div>
                <template x-if="resultData?.candidates && resultData.candidates.length > 0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <template x-for="cand in resultData.candidates" :key="cand.id">
                            <button type="button" @click="selectCandidate(cand)"
                                    class="h-[38px] px-2.5 rounded-lg sm:rounded-[4px] text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 hover:border-[var(--crm-accent,#2563eb)] cursor-pointer">
                                <span x-text="(cand.brand ? cand.brand + ' ' : '') + cand.name + (cand.model_no ? ' (' + cand.model_no + ')' : '')"></span>
                            </button>
                        </template>
                    </div>
                </template>
            </div>

            <form id="{{ $modalId }}-register-form" method="POST" action="{{ route('equipment.store') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" name="status" value="active">
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                <input type="hidden" name="product_id" x-model="registerProductId">
                <input type="hidden" name="scan_source" x-model="scanSource">
                <input type="hidden" name="raw_scan" x-model="registerRawScan">
                <input type="hidden" name="installation_job_id" x-model="installationJobId">
                <input type="hidden" name="learn_alias_token" x-model="learnAliasToken">
                <input type="hidden" name="learn_alias_type" x-model="learnAliasType">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Model --}}
                    <div class="sm:col-span-2 space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Model') }} <span class="text-rose-500">*</span></label>
                            <span x-show="!registerName" class="text-xs font-semibold text-orange-600 dark:text-orange-400">Check this</span>
                        </div>
                        <input type="text" name="equipment_name" x-model="registerName" required
                               placeholder="{{ __('Product name and brand') }}"
                               class="w-full h-[52px] sm:h-[38px] text-xs sm:text-sm rounded-lg sm:rounded-[4px] border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3">
                    </div>

                    {{-- Serial Number --}}
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Serial Number') }} <span class="text-rose-500">*</span></label>
                            <span x-show="serialCandidates.length > 1 || !registerSerial" class="text-xs font-semibold text-orange-600 dark:text-orange-400">Check this</span>
                        </div>
                        <input type="text" name="serial_number" x-model="registerSerial" required
                               placeholder="{{ __('e.g. SN-9842183') }}"
                               class="w-full h-[52px] sm:h-[38px] text-xs sm:text-sm rounded-lg sm:rounded-[4px] border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3 font-mono">
                        <template x-if="serialCandidates.length > 1">
                            <div class="flex flex-wrap gap-1 pt-1">
                                <template x-for="sn in serialCandidates" :key="sn">
                                    <button type="button" @click="selectSerialCandidate(sn)"
                                            class="px-2 py-1 rounded-[4px] text-[11px] font-mono border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 cursor-pointer"
                                            x-text="sn"></button>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- MAC Address --}}
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('MAC Address') }}</label>
                            <span x-show="possibleMacs.length > 0 && !registerMac" class="text-xs font-semibold text-orange-600 dark:text-orange-400">Check this</span>
                        </div>
                        <input type="text" name="mac_address" x-model="registerMac"
                               placeholder="{{ __('e.g. BC:A8:A6:11:22:33') }}"
                               class="w-full h-[52px] sm:h-[38px] text-xs sm:text-sm rounded-lg sm:rounded-[4px] border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3 font-mono">
                        <template x-if="possibleMacs.length > 0">
                            <div class="flex flex-wrap gap-1 pt-1">
                                <template x-for="pm in possibleMacs" :key="pm">
                                    <button type="button" @click="confirmPossibleMac(pm)"
                                            class="px-2 py-1 rounded-[4px] text-[11px] font-mono border border-orange-400 text-orange-600 dark:text-orange-400 cursor-pointer">
                                        Confirm MAC: <span x-text="pm"></span>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- EAN --}}
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('EAN') }}</label>
                        <input type="text" name="ean" x-model="registerEan"
                               placeholder="{{ __('EAN-8 / EAN-13') }}"
                               class="w-full h-[52px] sm:h-[38px] text-xs sm:text-sm rounded-lg sm:rounded-[4px] border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3 font-mono">
                    </div>

                    {{-- Location Tag --}}
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Location Tag') }}</label>
                        <input type="text" name="location_tag" placeholder="{{ __('e.g. Main Gate') }}"
                               class="w-full h-[52px] sm:h-[38px] text-xs sm:text-sm rounded-lg sm:rounded-[4px] border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3">
                    </div>

                    {{-- Customer Site / Project (Standard Searchable Select backed by /scanner/targets) --}}
                    <div class="sm:col-span-2 space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Customer Site / Project') }} <span class="text-rose-500">*</span></label>
                            <button type="button" onclick="triggerAddCustomerSiteFromScanner()" class="text-xs font-semibold text-[var(--crm-accent,#2563eb)] hover:underline cursor-pointer">
                                {{ __('Add New Site') }}
                            </button>
                        </div>
                        <select id="hw_scanner_lead_id_{{ $modalId }}"
                                name="lead_id"
                                x-model="selectedTargetId"
                                data-searchable="true"
                                data-search-placeholder="{{ __('Search customer name, phone, address...') }}"
                                class="crm-select crm-searchable-select w-full h-[52px] sm:h-[38px] text-xs sm:text-sm rounded-lg sm:rounded-[4px]"
                                required>
                            <option value="">{{ __('-- Select Customer Site or Active Project --') }}</option>
                            <template x-if="selectedTargetId && selectedTargetLabel && !targetResults.some(i => String(i.id) === String(selectedTargetId))">
                                <option :value="selectedTargetId" x-text="selectedTargetLabel" selected></option>
                            </template>
                            <template x-for="item in targetResults" :key="item.id + '-' + item.label">
                                <option :value="item.id" x-text="item.label + (item.detail ? ' — ' + item.detail : '')" :selected="String(item.id) === String(selectedTargetId)"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Action Buttons: Cancel | Save and scan next | Save device --}}
                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2 flex-wrap">
                    <button type="button" @click="closeModal()"
                            class="h-[52px] sm:h-[38px] px-3.5 rounded-lg sm:rounded-[4px] bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold cursor-pointer">
                        <span>{{ __('Cancel') }}</span>
                    </button>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="saveAndScanNext()" :disabled="isSaving"
                                class="h-[52px] sm:h-[38px] px-3.5 rounded-lg sm:rounded-[4px] border border-[var(--crm-accent,#2563eb)] text-[var(--crm-accent,#2563eb)] text-xs font-bold cursor-pointer disabled:opacity-50">
                            <span>{{ __('Save and scan next') }}</span>
                        </button>
                        <button type="submit"
                                class="btn-primary h-[52px] sm:h-[38px] px-4 rounded-lg sm:rounded-[4px] text-xs font-bold cursor-pointer">
                            <span>{{ __('Save device') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>
