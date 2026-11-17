@props([
    'modalId' => 'hardwareScannerModal',
    'projects' => collect(),
    'leads' => collect(),
])
@php
    if ($projects->isEmpty()) {
        try {
            $projects = \App\Models\Project::whereNull('deleted_at')->orderBy('name')->get();
        } catch (\Throwable $e) {
            $projects = collect();
        }
    }
    if ($leads->isEmpty()) {
        try {
            $leads = \App\Models\Lead::orderBy('customer_name')->get();
        } catch (\Throwable $e) {
            $leads = collect();
        }
    }
    $lanIp = \App\Http\Controllers\MobileScannerSyncController::detectLanIp();
@endphp

{{-- Local Offline Barcode & QR Decoding Engine (ZXing MultiFormat) --}}
<script src="{{ asset('vendor/zxing/zxing.min.js') }}"></script>

{{-- HARDWARE BARCODE SCANNER & ASSET REGISTRY MULTI-MODE MODAL --}}
<div id="{{ $modalId }}" 
     class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 hidden overflow-y-auto"
     data-lan-ip="{{ $lanIp }}"
     x-data="hardwareScannerModalComponent()"
     x-init="initModal()"
     @keydown.escape.window="closeModal()">

    <div class="crm-card bg-white dark:bg-slate-900 rounded-2xl max-w-xl w-full p-4 sm:p-6 shadow-2xl border border-slate-200 dark:border-slate-800 my-auto transition-all flex flex-col space-y-4">
        
        {{-- Modal Top Header Bar --}}
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>{{ __('Hardware Barcode Scanner & Analysis') }}</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Scan device barcodes via Mobile Camera or Barcode Scanner Machine, analyze specs & register asset') }}</p>
                </div>
            </div>
            <x-close-button onclick="closeHardwareScannerModal()" :label="__('Close')" />
        </div>

        {{-- Clean Modern Segmented Control: Mobile Scan | Barcode Machine | Analysis & S/N | Register Asset --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-1 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 text-xs select-none">
            
            {{-- 1. Mobile Scan --}}
            <button type="button" @click="switchTab('mobile')"
                    class="py-2.5 px-3 rounded-lg font-bold transition flex items-center justify-center gap-1.5 cursor-pointer text-center"
                    :class="activeTab === 'mobile' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                    :style="activeTab === 'mobile' ? 'background-color: #2563eb !important; color: #ffffff !important;' : ''">
                <span>📱 {{ __('Mobile Scan') }}</span>
            </button>

            {{-- 2. Barcode Machine Scan --}}
            <button type="button" @click="switchTab('scanner_gun')"
                    class="py-2.5 px-3 rounded-lg font-bold transition flex items-center justify-center gap-1.5 cursor-pointer text-center"
                    :class="activeTab === 'scanner_gun' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                    :style="activeTab === 'scanner_gun' ? 'background-color: #2563eb !important; color: #ffffff !important;' : ''">
                <span>⚡ {{ __('Barcode Machine') }}</span>
            </button>

            {{-- 3. Hardware Analysis & S/N --}}
            <button type="button" @click="switchTab('lookup')"
                    class="py-2.5 px-3 rounded-lg font-bold transition flex items-center justify-center gap-1.5 cursor-pointer text-center"
                    :class="activeTab === 'lookup' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                    :style="activeTab === 'lookup' ? 'background-color: #2563eb !important; color: #ffffff !important;' : ''">
                <span>🔍 {{ __('Analysis & S/N') }}</span>
                <span x-show="hasResult" class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            </button>

            {{-- 4. Register Asset --}}
            <button type="button" @click="switchTab('register')"
                    class="py-2.5 px-3 rounded-lg font-bold transition flex items-center justify-center gap-1.5 cursor-pointer text-center"
                    :class="activeTab === 'register' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                    :style="activeTab === 'register' ? 'background-color: #2563eb !important; color: #ffffff !important;' : ''">
                <span>💾 {{ __('Register Asset') }}</span>
            </button>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- SECTION 0: MOBILE SCAN (SMARTPHONE QR SYNC / PHONE CAMERA)  --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'mobile'" class="space-y-4">
            
            {{-- Laptop / Desktop View: Clean QR Code Pairing Card --}}
            <div x-show="!isMobile" class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto shadow-xs text-xl">
                    📱
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Scan Hardware with Smartphone Camera') }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                        {{ __('Scan this QR code with your mobile camera. Point your phone at the hardware device, barcode, or S/N sticker — data will automatically appear on this screen for analysis & save.') }}
                    </p>
                </div>

                {{-- QR Code Display & Live Link --}}
                <div class="flex flex-col items-center justify-center py-2">
                    <div class="p-3 bg-white rounded-2xl border-2 border-slate-200 dark:border-slate-700 shadow-md inline-block">
                        <img id="{{ $modalId }}-qr-img" :src="mobileQrUrl" alt="Mobile Scanner QR Code" class="w-44 h-44 object-contain rounded-lg">
                    </div>

                    {{-- Dynamic Connection Pill --}}
                    <div class="mt-3">
                        <template x-if="mobileStatus === 'waiting'">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                                <span>{{ __('Waiting for smartphone connection...') }}</span>
                            </span>
                        </template>
                        <template x-if="mobileStatus === 'connected'">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>{{ __('Phone Linked! Point phone camera at hardware barcode...') }}</span>
                            </span>
                        </template>
                        <template x-if="mobileStatus === 'scanned'">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700">
                                <span>{{ __('✓ Scanned! Visualizing hardware data on screen...') }}</span>
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Host / IP Configuration --}}
                <div class="mt-2 p-3 bg-white dark:bg-slate-900/80 rounded-xl text-left space-y-2 border border-slate-200 dark:border-slate-700/80">
                    <div class="flex items-center justify-between">
                        <label class="text-[11px] font-bold text-slate-700 dark:text-slate-300">
                            {{ __('Server Wi-Fi Address / Host:') }}
                        </label>
                        <span class="text-[10px] text-slate-500">{{ __('Phone must be on same Wi-Fi') }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <input type="text" x-model="customHostAndPort"
                               class="flex-1 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 px-2.5 py-1.5 font-mono text-slate-900 dark:text-slate-100 focus:ring-1 focus:ring-blue-500">
                        <button type="button" @click="updateQrUrl()"
                                class="btn-primary px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer shrink-0"
                                style="background-color: #2563eb !important; color: #ffffff !important;">
                            <span>{{ __('Update QR') }}</span>
                        </button>
                    </div>
                    <div class="pt-1 flex items-center justify-between text-xs flex-wrap gap-2">
                        <a :href="mobileUrl" target="_blank" class="text-blue-600 dark:text-blue-400 font-bold hover:underline inline-flex items-center gap-1">
                            <span>{{ __('Open Mobile Scanner Directly in New Tab') }} &rarr;</span>
                        </a>
                        <button type="button" @click="switchTab('scanner_gun')" class="text-slate-600 dark:text-slate-400 hover:underline cursor-pointer">
                            {{ __('Using a Handheld Barcode Scanner Gun?') }} &rarr;
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile Phone View: Direct Phone Camera Scanner --}}
            <div x-show="isMobile" class="space-y-3">
                <input type="file" id="{{ $modalId }}-phone-camera-input" accept="image/*" capture="environment" @change="uploadImage($event)" class="hidden">

                <div id="{{ $modalId }}-phone-viewport"
                     @click="triggerCameraSnap()"
                     class="relative bg-slate-950 rounded-2xl overflow-hidden w-full h-[260px] flex items-center justify-center border border-slate-800 shadow-inner select-none cursor-pointer">
                    
                    <img id="{{ $modalId }}-phone-preview" class="w-full h-full object-contain hidden bg-slate-950" alt="Scanned Preview">

                    <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center z-10 bg-gradient-to-b from-slate-900/90 to-slate-950/95">
                        <div class="w-16 h-16 rounded-2xl bg-cyan-500/20 border-2 border-cyan-400 text-cyan-300 flex items-center justify-center text-3xl mb-3 shadow-[0_0_20px_rgba(34,211,238,0.3)] animate-pulse">
                            📷
                        </div>
                        <div class="text-sm font-bold text-white mb-1">
                            {{ __('Tap to Open Phone Camera') }}
                        </div>
                        <div class="text-xs text-slate-300 max-w-[240px]">
                            {{ __('Point phone camera directly at CCTV hardware barcode or S/N sticker') }}
                        </div>
                        <button type="button" @click.stop="triggerCameraSnap()"
                                class="mt-3 px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold text-xs shadow-lg inline-flex items-center gap-1.5 cursor-pointer">
                            <span>📷 {{ __('Scan Hardware with Camera') }}</span>
                        </button>
                    </div>
                </div>

                <button type="button" @click="triggerCameraSnap()"
                        class="w-full flex items-center justify-center space-x-2 min-h-[46px] py-2.5 px-4 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white rounded-xl text-sm font-bold shadow-md shadow-blue-900/40 cursor-pointer active:scale-98 transition-all">
                    <span>📷 {{ __('Open Phone Camera & Scan Tag') }}</span>
                </button>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- SECTION 1: HARDWARE ANALYSIS & SERIAL NUMBER LOOKUP         --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'lookup'" class="space-y-4">
            
            {{-- Scanned from Mobile Notification Banner --}}
            <template x-if="lastScannedViaMobile">
                <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <div class="text-xs text-emerald-900 dark:text-emerald-200">
                            <strong>{{ __('Scanned via Mobile Camera:') }}</strong>
                            <code class="font-mono bg-white dark:bg-slate-900 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 font-bold text-xs" x-text="lastQueriedCode"></code>
                        </div>
                    </div>
                    <button type="button" @click="rearmMobileScanner()"
                            class="px-3 py-1 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition cursor-pointer shadow-xs"
                            style="background-color: #059669 !important; color: #ffffff !important;">
                        <span>{{ __('Scan Next Device (Phone)') }}</span>
                    </button>
                </div>
            </template>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block">
                    {{ __('Hardware Serial Number, Barcode, or MAC Address') }} <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="text" x-model="lookupInput" 
                           placeholder="{{ __('Type or scan barcode (e.g. SN-88412)...') }}"
                           @keydown.enter.prevent="executeLookup()"
                           class="flex-1 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3.5 py-2 min-h-[38px] font-mono focus:ring-2 focus:ring-blue-500">
                    <button type="button" @click="executeLookup()"
                            :disabled="isLoading"
                            class="btn-primary inline-flex items-center justify-center px-4 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer shrink-0 disabled:opacity-50"
                            style="background-color: #2563eb !important; color: #ffffff !important;">
                        <span x-show="!isLoading" style="color: #ffffff !important;">{{ __('Analyze') }}</span>
                        <span x-show="isLoading" style="color: #ffffff !important;">{{ __('Searching...') }}</span>
                    </button>
                </div>
            </div>

            {{-- Quick Shortcuts Row --}}
            <div class="flex items-center justify-between flex-wrap gap-2 pt-1 text-xs">
                <button type="button" @click="switchTab('mobile')"
                        class="inline-flex items-center gap-1.5 text-blue-600 dark:text-blue-400 font-bold hover:underline cursor-pointer">
                    <span>{{ __('📱 Pair Mobile Phone Camera') }}</span>
                </button>

                <button type="button" @click="prefillAndRegister(lookupInput)"
                        class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 font-semibold hover:text-slate-900 dark:hover:text-white cursor-pointer">
                    <span>{{ __('Register New Asset Directly') }}</span>
                </button>
            </div>

            {{-- Live Lookup & Analysis Result Box --}}
            <div id="hw-modal-result" class="min-h-[60px]">
                <template x-if="isLoading">
                    <div class="flex items-center justify-center py-8 space-x-2 text-slate-500">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-ping"></span>
                        <span class="text-xs font-semibold">{{ __('Analyzing hardware database & catalog...') }}</span>
                    </div>
                </template>

                <template x-if="!isLoading && hasResult">
                    <div>
                        {{-- 1. Installed Customer Asset Found --}}
                        <template x-if="resultData && resultData.found && resultData.type === 'installed_equipment'">
                            <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800/80 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                                <h4 class="text-sm font-black text-slate-900 dark:text-white" x-text="resultData.equipment_name"></h4>
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                                      :class="resultData.is_under_warranty ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700' : 'bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-700'"
                                                      x-text="resultData.is_under_warranty ? '{{ __('Under Warranty') }}' : '{{ __('Warranty Expired') }}'"></span>
                                            </div>
                                            <div class="text-xs text-slate-600 dark:text-slate-300 space-y-1">
                                                <div>{{ __('Customer') }}: <strong class="text-slate-900 dark:text-white" x-text="resultData.customer_name"></strong> (<span x-text="resultData.customer_phone"></span>)</div>
                                                <div>{{ __('Location') }}: <strong class="text-slate-900 dark:text-white" x-text="resultData.location_tag || '{{ __('Site Asset') }}'"></strong> &middot; S/N: <code class="font-mono bg-white dark:bg-slate-900 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 text-blue-600 dark:text-blue-400 font-bold" x-text="resultData.serial_number"></code></div>
                                                <div x-show="resultData.mac_address">{{ __('MAC') }}: <code class="font-mono bg-white dark:bg-slate-900 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 font-bold" x-text="resultData.mac_address"></code></div>
                                                <div>{{ __('Warranty Expiry') }}: <strong x-text="resultData.mfg_warranty_expiry"></strong></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a :href="resultData.show_url" 
                                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition shadow-xs">
                                            <span>{{ __('View Asset History') }}</span> &rarr;
                                        </a>
                                        <button type="button" @click="rearmScanner()"
                                                class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs cursor-pointer inline-flex items-center gap-1"
                                                style="background-color: #059669 !important; color: #ffffff !important;">
                                            <span>{{ __('Scan Next Device') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- 2. Catalog Product in Inventory Found --}}
                        <template x-if="resultData && resultData.found && resultData.type === 'catalog_product'">
                            <div class="p-4 sm:p-5 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/80 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                                        </div>
                                        <div>
                                            <span class="text-[10px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wide block">{{ __('Catalog Inventory Item') }}</span>
                                            <h4 class="text-sm font-black text-slate-900 dark:text-white mt-0.5" x-text="resultData.product_name"></h4>
                                            <div class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                                SKU: <strong class="font-mono text-slate-800 dark:text-slate-200" x-text="resultData.sku"></strong> &middot; 
                                                {{ __('Model') }}: <span x-text="resultData.model_no || 'N/A'"></span> &middot; 
                                                {{ __('Stock Available') }}: <strong class="text-emerald-600 dark:text-emerald-400 font-bold" x-text="resultData.stock_quantity"></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <button type="button" @click="prefillAndRegister(lastQueriedCode, resultData.product_name, resultData.id)"
                                                class="btn-primary inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer"
                                                style="background-color: #2563eb !important; color: #ffffff !important;">
                                            <span style="color: #ffffff !important;">{{ __('Register & Save Asset') }} &rarr;</span>
                                        </button>
                                        <button type="button" @click="rearmScanner()"
                                                class="px-3 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold cursor-pointer">
                                            <span>{{ __('Scan Next Device') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- 3. Hardware Not Found in DB (New Unregistered Hardware) --}}
                        <template x-if="resultData && !resultData.found">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 sm:p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">
                                             <span class="font-mono bg-amber-200/60 dark:bg-amber-900/80 px-2 py-0.5 rounded text-amber-950 dark:text-amber-100 font-black" x-text="lastQueriedCode"></span>
                                        </h4>
                                        <p class="text-xs text-amber-800/80 dark:text-amber-300/80 mt-0.5">{{ __('This hardware device is not yet registered. You can analyze specifications below and register it immediately.') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <button type="button" @click="prefillAndRegister(lastQueriedCode)"
                                            class="btn-primary inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer"
                                            style="background-color: #2563eb !important; color: #ffffff !important;">
                                        <span style="color: #ffffff !important;">{{ __('Register & Save Now') }} &rarr;</span>
                                    </button>
                                    <button type="button" @click="rearmScanner()"
                                            class="px-3 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold cursor-pointer">
                                        <span>{{ __('Scan Next Device') }}</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- SECTION 1: BARCODE MACHINE SCAN (USB / BLUETOOTH SCANNER GUN) --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'scanner_gun'" class="space-y-4">
            
            <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-4 text-center">
                
                {{-- Scanner Icon --}}
                <div class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto shadow-xs text-2xl">
                    ⚡
                </div>

                <div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Barcode Machine & Scanner Gun Station') }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
                        {{ __('Connect your USB or Bluetooth barcode scanner machine / gun (Honeywell, Zebra, TVS, etc.). Pull the trigger on the hardware barcode to scan instantly into the webpage.') }}
                    </p>
                </div>

                {{-- Dedicated High-Speed Scanner Gun Input Box --}}
                <div class="max-w-md mx-auto space-y-2">
                    <div class="relative">
                        <input type="text"
                               x-ref="gunInput"
                               x-model="gunInput"
                               @keydown.enter.prevent="processGunScan()"
                               placeholder="{{ __('Pull scanner gun trigger or type S/N...') }}"
                               class="w-full text-center font-mono font-bold text-sm sm:text-base py-3 px-4 rounded-xl border-2 border-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm focus:ring-4 focus:ring-blue-500/20 focus:outline-none tracking-wider">
                    </div>

                    <div class="flex items-center justify-center gap-2">
                        <button type="button" @click="processGunScan()"
                                class="btn-primary py-2.5 px-5 rounded-xl text-xs font-bold shadow-xs cursor-pointer inline-flex items-center gap-1.5"
                                style="background-color: #2563eb !important; color: #ffffff !important;">
                            <span>{{ __('Verify & Analyze Hardware') }} &rarr;</span>
                        </button>
                        <button type="button" @click="gunInput = ''; if($refs.gunInput) $refs.gunInput.focus()"
                                class="btn-secondary py-2.5 px-3 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-700 cursor-pointer">
                            <span>{{ __('Clear') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Active Listener Status Badge --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ __('Barcode Machine Listener Active — Ready for Trigger Pull') }}</span>
                </div>

                {{-- Supported Barcode Standards Footer --}}
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700/80 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-center gap-4 flex-wrap">
                    <span>✓ Code 128 / Code 39</span>
                    <span>✓ QR Code & DataMatrix</span>
                    <span>✓ EAN-13 / UPC</span>
                    <span>✓ CCTV Serial Tags (Hikvision, CP Plus, Dahua)</span>
                </div>

            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- SECTION 3: REGISTER NEW HARDWARE ASSET FORM (SAVE)          --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'register'" class="space-y-4">
            
            <form method="POST" action="{{ route('equipment.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="status" value="active">
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                <input type="hidden" name="product_id" x-model="registerProductId">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    
                    {{-- Serial Number (Pre-filled from scan or manual) --}}
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block">
                            {{ __('Serial Number / Barcode') }} <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="serial_number" x-model="registerSerial" required
                               placeholder="{{ __('e.g. SN-9842183') }}"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3.5 py-2.5 font-mono focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- MAC Address (Optional) --}}
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block">
                            {{ __('MAC / IP Address (Optional)') }}
                        </label>
                        <input type="text" name="mac_address" x-model="registerMac"
                               placeholder="{{ __('e.g. BC:A8:A6:11:22:33') }}"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3.5 py-2.5 font-mono focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Equipment Name / Model --}}
                    <div class="space-y-1 sm:col-span-2">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block">
                            {{ __('Equipment Name / Model') }} <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="equipment_name" x-model="registerName" required
                               placeholder="{{ __('e.g. CP Plus 4MP Dome Camera') }}"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Customer Site / Project --}}
                    <div class="sm:col-span-2 space-y-1">
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-0">
                                {{ __('Customer Site / Project') }} <span class="text-rose-500">*</span>
                            </label>
                            <button type="button" 
                                    onclick="triggerAddCustomerSiteFromScanner()" 
                                    class="text-xs font-bold px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white transition cursor-pointer shadow-xs" 
                                    style="color: #ffffff !important;">
                                <span class="!text-white font-bold" style="color: #ffffff !important;">Add New Customer Site</span>
                            </button>
                        </div>
                        <select id="hw_scanner_lead_id_{{ $modalId }}" name="lead_id" required
                                class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('-- Select Customer Site or Active Project --') }}</option>
                            @if($projects->isNotEmpty())
                                <optgroup label="{{ __('Active Installation Projects') }}">
                                    @foreach($projects as $prj)
                                        @if($prj->lead_id)
                                            <option value="{{ $prj->lead_id }}">
                                                {{ $prj->project_code ?? $prj->code }} - {{ $prj->title }} ({{ $prj->lead?->customer_name ?? 'Client' }})
                                            </option>
                                        @endif
                                    @endforeach
                                </optgroup>
                            @endif

                            @if($leads->isNotEmpty())
                                <optgroup label="{{ __('Customer Accounts') }}">
                                    @foreach($leads as $ld)
                                        <option value="{{ $ld->id }}">
                                            {{ $ld->customer_name }} {{ $ld->company_name ? "({$ld->company_name})" : '' }} &bull; {{ $ld->city ?? 'Site' }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>

                    {{-- Location Tag / Room --}}
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block">
                            {{ __('Location Tag / Room') }}
                        </label>
                        <input type="text" name="location_tag" placeholder="{{ __('e.g. Main Gate, Server Room Rack') }}"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Installation Date --}}
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block">
                            {{ __('Installation Date') }}
                        </label>
                        <input type="date" name="installation_date" value="{{ now()->format('Y-m-d') }}"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Service Notes --}}
                    <div class="sm:col-span-2 space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block">
                            {{ __('Technical Notes (Optional)') }}
                        </label>
                        <input type="text" name="notes" placeholder="{{ __('e.g. Mounted on south wall, 12V DC power connected') }}"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500">
                    </div>

                </div>

                {{-- Submit Buttons Row --}}
                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
                    <button type="button" @click="switchTab('lookup')"
                            class="inline-flex items-center justify-center px-4 py-2 min-h-[38px] rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-bold transition cursor-pointer">
                        <span>{{ __('Back to Analysis') }}</span>
                    </button>

                    <button type="submit"
                            class="btn-primary inline-flex items-center justify-center px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold shadow-sm transition cursor-pointer"
                            style="background-color: #2563eb !important; color: #ffffff !important;">
                        <span style="color: #ffffff !important;">{{ __('Save & Register Asset') }}</span>
                    </button>
                </div>

            </form>

        </div>

    </div>
</div>

<script>
    function hardwareScannerModalComponent() {
        const detectIsMobile = () => (
            window.innerWidth < 768 ||
            /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
        );

        return {
            isMobile: detectIsMobile(),
            activeTab: 'mobile',
            gunInput: '',
            lookupInput: '',
            isLoading: false,
            hasResult: false,
            resultData: null,
            lastQueriedCode: '',
            lastScanMethod: 'mobile', // 'mobile' or 'scanner_gun'
            lastScannedViaMobile: false,

            // Mobile Phone Remote Scanner State (QR Sync)
            sessionToken: '',
            mobileUrl: '',
            mobileQrUrl: '',
            mobileStatus: 'waiting', // 'waiting', 'connected', 'scanned'
            pollInterval: null,
            customHostAndPort: '',

            // Register form pre-fill state
            registerSerial: '',
            registerName: '',
            registerMac: '',
            registerProductId: '',
            zxingReader: null,

            checkDevice() {
                this.isMobile = (
                    window.innerWidth < 768 ||
                    /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
                );
            },

            triggerCameraSnap() {
                const input = document.getElementById('{{ $modalId }}-phone-camera-input');
                if (input) input.click();
            },

            initModal() {
                this.checkDevice();

                window.openHardwareScannerModal = (tab = 'auto') => {
                    const modal = document.getElementById('{{ $modalId }}');
                    if (modal) {
                        modal.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                        this.checkDevice();

                        let targetTab = 'mobile';
                        if (tab && ['mobile', 'scanner_gun', 'lookup', 'register'].includes(tab)) {
                            targetTab = tab;
                        }
                        this.switchTab(targetTab);
                    }
                };

                window.closeHardwareScannerModal = () => {
                    this.closeModal();
                };

                window.triggerAddCustomerSiteFromScanner = () => {
                    if (typeof openQuickAddSiteModal === 'function') {
                        openQuickAddSiteModal();
                    } else {
                        window.open("{{ route('sites.create') }}", '_blank');
                    }
                };

                // Listen for custom trigger events
                window.addEventListener('open-hardware-scanner', (e) => {
                    const tab = (e.detail && e.detail.tab) ? e.detail.tab : 'auto';
                    window.openHardwareScannerModal(tab);
                });
            },

            closeModal() {
                const modal = document.getElementById('{{ $modalId }}');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                    if (this.pollInterval) {
                        clearInterval(this.pollInterval);
                        this.pollInterval = null;
                    }
                }
            },

            switchTab(t) {
                if (this.activeTab === 'mobile' && t !== 'mobile') {
                    if (this.pollInterval) {
                        clearInterval(this.pollInterval);
                        this.pollInterval = null;
                    }
                }
                this.activeTab = t;
                if (t === 'mobile' && !this.isMobile) {
                    this.$nextTick(() => this.initMobileCam());
                } else if (t === 'scanner_gun') {
                    this.$nextTick(() => {
                        if (this.$refs.gunInput) {
                            this.$refs.gunInput.focus();
                        }
                    });
                }
            },

            // ═══════════════════════════════════════════════════════════
            // BARCODE MACHINE SCANNER GUN HANDLER
            // ═══════════════════════════════════════════════════════════
            async processGunScan() {
                const code = (this.gunInput || '').trim();
                if (!code) {
                    if (this.$refs.gunInput) this.$refs.gunInput.focus();
                    return;
                }

                this.beep();
                this.lastScanMethod = 'scanner_gun';
                this.lastScannedViaMobile = false;
                this.lastQueriedCode = code;
                this.lookupInput = code;
                this.gunInput = '';

                this.switchTab('lookup');
                await this.executeLookup();
            },

            rearmScanner() {
                this.hasResult = false;
                this.resultData = null;
                this.lookupInput = '';
                this.gunInput = '';

                if (this.lastScanMethod === 'scanner_gun') {
                    this.switchTab('scanner_gun');
                    this.$nextTick(() => {
                        if (this.$refs.gunInput) this.$refs.gunInput.focus();
                    });
                } else {
                    this.rearmMobileScanner();
                }
            },

            // ═══════════════════════════════════════════════════════════
            // MOBILE PHONE SCANNER HANDLERS (QR PAIRING & SNAP)
            // ═══════════════════════════════════════════════════════════
            initMobileCam() {
                if (this.pollInterval) {
                    clearInterval(this.pollInterval);
                    this.pollInterval = null;
                }
                const token = 'sc_' + Math.random().toString(36).substring(2, 10);
                this.sessionToken = token;
                this.mobileStatus = 'waiting';

                const modal = document.getElementById('{{ $modalId }}');
                const lanIp = (modal && modal.dataset.lanIp) ? modal.dataset.lanIp : window.location.hostname;
                const port = window.location.port ? (':' + window.location.port) : '';
                if (!this.customHostAndPort) {
                    this.customHostAndPort = (lanIp + port).replace(/^https?:\/\//i, '');
                }

                const host = this.customHostAndPort;
                this.mobileUrl = `${window.location.protocol}//${host}/mobile-scanner/${token}?target=hardware&label=Hardware+Barcode`;
                this.mobileQrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=${encodeURIComponent(this.mobileUrl)}`;

                // Start polling endpoint
                this.pollInterval = setInterval(async () => {
                    try {
                        const res = await fetch(`/api/mobile-scanner/poll/${token}`);
                        const data = await res.json();
                        if (data && (data.status === 'scanned' || data.code)) {
                            this.mobileStatus = 'scanned';
                            this.beep();
                            this.lastScanMethod = 'mobile';
                            this.lastScannedViaMobile = true;
                            this.lastQueriedCode = data.code;
                            this.lookupInput = data.code;
                            await this.executeLookup();
                            this.switchTab('lookup');
                        } else if (data && data.status === 'connected') {
                            this.mobileStatus = 'connected';
                        }
                    } catch (e) {}
                }, 1000);
            },

            updateQrUrl() {
                const host = (this.customHostAndPort || '').trim().replace(/^https?:\/\//i, '');
                if (!host) return;
                this.customHostAndPort = host;
                this.mobileUrl = `${window.location.protocol}//${host}/mobile-scanner/${this.sessionToken}?target=hardware&label=Hardware+Barcode`;
                this.mobileQrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=${encodeURIComponent(this.mobileUrl)}`;
            },

            rearmMobileScanner() {
                this.hasResult = false;
                this.resultData = null;
                this.lookupInput = '';
                this.lastScannedViaMobile = false;
                this.switchTab('mobile');
            },

            prefillAndRegister(serial = '', name = '', productId = '', mac = '') {
                this.registerSerial = (serial || '').trim();
                if (name) this.registerName = name;
                if (productId) this.registerProductId = productId;
                if (mac) this.registerMac = mac;
                this.switchTab('register');
            },

            async executeLookup() {
                const code = (this.lookupInput || '').trim();
                if (!code) return;

                this.isLoading = true;
                this.lastQueriedCode = code;

                try {
                    const res = await fetch(`{{ route('equipment.lookup') }}?serial=${encodeURIComponent(code)}`);
                    const data = await res.json();
                    this.resultData = data;
                    this.hasResult = true;

                    // Automatically pre-fill register form fields
                    if (data && !data.found) {
                        this.registerSerial = data.serial || code;
                        if (data.mac) this.registerMac = data.mac;
                        this.registerName = '';
                    } else if (data && data.found) {
                        this.registerSerial = data.serial_number || code;
                        this.registerName = data.equipment_name || data.product_name || '';
                        if (data.product_id) this.registerProductId = data.product_id;
                        if (data.mac_address) this.registerMac = data.mac_address;
                    }
                } catch (e) {
                    this.resultData = { found: false, error: e.message };
                    this.hasResult = true;
                } finally {
                    this.isLoading = false;
                }
            },

            onBarcodeDetected(code) {
                if (!code) return;
                this.beep();
                if (navigator.vibrate) {
                    navigator.vibrate([120, 60, 120]);
                }
                this.lastScanMethod = 'mobile';
                this.lastScannedViaMobile = true;
                this.lookupInput = String(code).trim();
                this.switchTab('lookup');
                this.executeLookup();
            },

            beep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.frequency.value = 880;
                    gain.gain.value = 0.15;
                    osc.start();
                    setTimeout(() => { osc.stop(); ctx.close(); }, 140);
                } catch (e) {}
            },

            async uploadImage(event) {
                const file = event.target.files[0];
                if (!file) return;
                event.target.value = '';

                const previewImg = document.getElementById('{{ $modalId }}-phone-preview');
                const objectUrl = URL.createObjectURL(file);

                if (previewImg) {
                    previewImg.src = objectUrl;
                    previewImg.classList.remove('hidden');
                }

                const img = new Image();
                img.onload = async () => {
                    const canvas = document.createElement('canvas');
                    const maxDim = 1280;
                    let w = img.width || 800;
                    let h = img.height || 600;
                    if (w > maxDim || h > maxDim) {
                        if (w > h) { h = Math.round((h * maxDim) / w); w = maxDim; }
                        else { w = Math.round((w * maxDim) / h); h = maxDim; }
                    }
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, w, h);

                    let detected = null;
                    if ('BarcodeDetector' in window) {
                        try {
                            const detector = new BarcodeDetector({
                                formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'qr_code', 'upc_a', 'upc_e', 'data_matrix']
                            });
                            const barcodes = await detector.detect(canvas);
                            if (barcodes.length > 0 && barcodes[0].rawValue) {
                                detected = barcodes[0].rawValue;
                            }
                        } catch (e) {}
                    }

                    if (!detected && window.ZXing && window.ZXing.BrowserMultiFormatReader) {
                        try {
                            if (!this.zxingReader) this.zxingReader = new window.ZXing.BrowserMultiFormatReader();
                            const result = await this.zxingReader.decodeFromCanvas(canvas);
                            if (result && result.getText()) {
                                detected = result.getText();
                            }
                        } catch (e) {}
                    }

                    if (!detected && window.ZXing && window.ZXing.BrowserMultiFormatReader) {
                        try {
                            const imgData = ctx.getImageData(0, 0, w, h);
                            const d = imgData.data;
                            for (let i = 0; i < d.length; i += 4) {
                                const gray = (d[i] * 0.299 + d[i + 1] * 0.587 + d[i + 2] * 0.114);
                                const contrast = gray > 120 ? 255 : 0;
                                d[i] = contrast; d[i+1] = contrast; d[i+2] = contrast;
                            }
                            ctx.putImageData(imgData, 0, 0);
                            const result = await this.zxingReader.decodeFromCanvas(canvas);
                            if (result && result.getText()) {
                                detected = result.getText();
                            }
                        } catch (e) {}
                    }

                    if (detected) {
                        this.onBarcodeDetected(detected);
                    } else {
                        const manual = prompt('Could not auto-detect barcode. Enter S/N manually:');
                        if (manual && manual.trim()) {
                            this.onBarcodeDetected(manual.trim());
                        }
                    }
                };
                img.src = objectUrl;
            }
        };
    }
</script>
