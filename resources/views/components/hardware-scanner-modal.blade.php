@props([
    'modalId' => 'hardwareScannerModal',
    'projects' => collect(),
    'leads' => collect(),
])

{{-- HARDWARE BARCODE SCANNER & ASSET REGISTRY MULTI-MODE MODAL --}}
<div id="{{ $modalId }}" 
     class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 hidden overflow-y-auto"
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
                        <span>{{ __('Hardware Barcode Scanner & Registry') }}</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('On-site serial verification, barcode scanner & asset registration') }}</p>
                </div>
            </div>
            <x-close-button onclick="closeHardwareScannerModal()" :label="__('Close')" />
        </div>

        {{-- Mode Selector Stepper / Tabs (Matching Attendance Modal Design) --}}
        <div class="bg-slate-50 dark:bg-slate-800/60 p-1.5 rounded-xl border border-slate-200 dark:border-slate-700/60 flex items-center gap-1.5">
            {{-- Mode 1: Lookup --}}
            <button type="button" @click="switchTab('lookup')"
                    class="flex-1 py-2 px-3 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center gap-1.5 cursor-pointer"
                    :class="activeTab === 'lookup' ? 'bg-blue-600 !text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-700/60'"
                    :style="activeTab === 'lookup' ? 'color: #ffffff !important;' : ''">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <span>{{ __('Serial Lookup') }}</span>
            </button>

            {{-- Mode 2: Camera Scanner --}}
            <button type="button" @click="switchTab('camera')"
                    class="flex-1 py-2 px-3 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center gap-1.5 cursor-pointer"
                    :class="activeTab === 'camera' ? 'bg-blue-600 !text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-700/60'"
                    :style="activeTab === 'camera' ? 'color: #ffffff !important;' : ''">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                <span>{{ __('Live Scanner') }}</span>
            </button>

            {{-- Mode 3: Register Asset --}}
            <button type="button" @click="switchTab('register')"
                    class="flex-1 py-2 px-3 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center gap-1.5 cursor-pointer"
                    :class="activeTab === 'register' ? 'bg-blue-600 !text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-700/60'"
                    :style="activeTab === 'register' ? 'color: #ffffff !important;' : ''">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>{{ __('Register Asset') }}</span>
            </button>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- SECTION 1: SERIAL NUMBER LOOKUP                             --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'lookup'" class="space-y-4">
            
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block">
                    {{ __('Hardware Serial Number, Barcode, or MAC Address') }} <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="text" x-model="lookupInput" 
                           placeholder="{{ __('Type or paste serial barcode (e.g. SN-88412)...') }}"
                           @keydown.enter.prevent="executeLookup()"
                           class="flex-1 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3.5 py-2 min-h-[38px] focus:ring-2 focus:ring-blue-500">
                    <button type="button" @click="executeLookup()"
                            :disabled="isLoading"
                            class="inline-flex items-center justify-center px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white text-xs sm:text-sm font-bold shadow-sm transition cursor-pointer shrink-0 disabled:opacity-50"
                            style="color: #ffffff !important;">
                        <span x-show="!isLoading" style="color: #ffffff !important;">{{ __('Lookup') }}</span>
                        <span x-show="isLoading" style="color: #ffffff !important;">{{ __('Searching...') }}</span>
                    </button>
                </div>
            </div>

            {{-- Quick Shortcuts Row --}}
            <div class="flex items-center justify-between flex-wrap gap-2 pt-1 text-xs">
                <button type="button" @click="switchTab('camera')"
                        class="inline-flex items-center gap-1.5 text-blue-600 dark:text-blue-400 font-bold hover:underline cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                    <span>{{ __('Launch Live Camera Scanner') }}</span>
                </button>

                <button type="button" @click="prefillAndRegister(lookupInput)"
                        class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 font-semibold hover:text-slate-900 dark:hover:text-white cursor-pointer">
                    <span>{{ __('+ Register New Asset Directly') }}</span>
                </button>
            </div>

            {{-- Live Lookup Result Box --}}
            <div id="hw-modal-result" class="min-h-[60px]">
                <template x-if="hasResult">
                    <div>
                        {{-- 1. Installed Equipment Found --}}
                        <template x-if="resultData && resultData.found && resultData.type === 'installed_equipment'">
                            <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 space-y-3">
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
                                                <div>{{ __('Warranty Expiry') }}: <strong x-text="resultData.mfg_warranty_expiry"></strong></div>
                                            </div>
                                        </div>
                                    </div>
                                    <a :href="resultData.show_url" 
                                       class="self-start sm:self-auto inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition shadow-xs">
                                        <span>{{ __('View Asset History') }}</span> &rarr;
                                    </a>
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
                                    <button type="button" @click="prefillAndRegister(lastQueriedCode, resultData.product_name, resultData.id)"
                                            class="self-start sm:self-auto inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white font-bold text-xs sm:text-sm shadow-sm transition cursor-pointer"
                                            style="color: #ffffff !important;">
                                        <span style="color: #ffffff !important;">{{ __('Install At Site') }} &rarr;</span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        {{-- 3. Hardware Not Found in DB --}}
                        <template x-if="resultData && !resultData.found">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 sm:p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">
                                            {{ __('No hardware found for S/N:') }} <span class="font-mono bg-amber-200/60 dark:bg-amber-900/80 px-2 py-0.5 rounded text-amber-950 dark:text-amber-100 font-black" x-text="lastQueriedCode"></span>
                                        </h4>
                                        <p class="text-xs text-amber-800/80 dark:text-amber-300/80 mt-0.5">{{ __('This hardware serial has not yet been registered in the system.') }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="prefillAndRegister(lastQueriedCode)"
                                        class="self-start sm:self-auto inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white font-bold text-xs sm:text-sm shadow-sm transition cursor-pointer"
                                        style="color: #ffffff !important;">
                                    <span style="color: #ffffff !important;">{{ __('+ Register This S/N Now') }} &rarr;</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- SECTION 2: LIVE CAMERA BARCODE SCANNER                      --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'camera'" class="space-y-4">
            
            {{-- Camera Viewport Container --}}
            <div class="relative bg-black rounded-2xl overflow-hidden w-full h-[260px] sm:h-[300px] flex items-center justify-center border border-slate-200 dark:border-slate-800 shadow-inner">
                <video id="hw-scanner-video" autoplay playsinline muted class="w-full h-full object-cover"></video>
                <canvas id="hw-scanner-canvas" style="display:none;"></canvas>

                {{-- Scanning Reticle & Animated Laser --}}
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
                <div id="hw-scanner-status"
                     class="absolute bottom-3 inset-x-3 text-center bg-slate-900/85 backdrop-blur text-white text-xs font-semibold py-1.5 px-3 rounded-full border border-white/10 shadow-sm mx-auto max-w-xs">
                    {{ __('Initializing camera...') }}
                </div>
            </div>

            {{-- Camera Controls & Photo Upload --}}
            <div class="flex items-center justify-between flex-wrap gap-2 pt-1">
                <div class="flex items-center gap-2">
                    <button type="button" @click="toggleTorch()"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.516 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
                        <span>{{ __('Torch') }}</span>
                    </button>
                    <button type="button" @click="switchCamera()"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        <span>{{ __('Flip Cam') }}</span>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="snapFrame()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                        <span>{{ __('Snap Frame') }}</span>
                    </button>
                    <label class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 !text-white text-xs font-bold shadow-sm transition cursor-pointer"
                           style="color: #ffffff !important;">
                        <svg class="w-3.5 h-3.5 !text-white" fill="none" stroke="#ffffff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('Upload Image') }}</span>
                        <input type="file" accept="image/*" @change="uploadImage($event)" style="display:none;">
                    </label>
                </div>
            </div>

            <div class="pt-2 text-center">
                <button type="button" @click="switchTab('lookup')" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                    &larr; {{ __('Switch back to manual serial lookup') }}
                </button>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- SECTION 3: REGISTER NEW HARDWARE ASSET FORM                 --}}
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

                    {{-- Equipment Name / Model --}}
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block">
                            {{ __('Equipment Name / Model') }} <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="equipment_name" x-model="registerName" required
                               placeholder="{{ __('e.g. CP Plus 4MP Dome Camera') }}"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Customer Site / Project --}}
                    <div class="sm:col-span-2 space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block">
                            {{ __('Customer Site / Project') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="lead_id" required
                                class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 text-slate-900 dark:text-white px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('-- Select Customer Site or Active Project --') }}</option>
                            @if($projects->isNotEmpty())
                                <optgroup label="{{ __('Active Installation Projects') }}">
                                    @foreach($projects as $prj)
                                        @if($prj->lead_id)
                                            <option value="{{ $prj->lead_id }}" {{ $loop->first ? 'selected' : '' }}>
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
                        &larr; {{ __('Back to Lookup') }}
                    </button>

                    <button type="submit"
                            class="inline-flex items-center justify-center px-5 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white text-xs sm:text-sm font-bold shadow-sm transition cursor-pointer"
                            style="color: #ffffff !important;">
                        <span style="color: #ffffff !important;">{{ __('Save & Register Asset') }} &rarr;</span>
                    </button>
                </div>

            </form>

        </div>

    </div>
</div>

<script>
    function hardwareScannerModalComponent() {
        return {
            activeTab: 'lookup', // 'lookup', 'camera', 'register'
            lookupInput: '',
            isLoading: false,
            hasResult: false,
            resultData: null,
            lastQueriedCode: '',
            
            // Register form pre-fill state
            registerSerial: '',
            registerName: '',
            registerProductId: '',

            // Camera scanner internal state
            stream: null,
            animFrame: null,
            currentFacingMode: 'environment',
            torchOn: false,
            track: null,

            initModal() {
                window.openHardwareScannerModal = (tab = 'lookup') => {
                    const modal = document.getElementById('{{ $modalId }}');
                    if (modal) {
                        modal.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                        this.switchTab(tab);
                    }
                };

                window.closeHardwareScannerModal = () => {
                    this.closeModal();
                };
            },

            closeModal() {
                const modal = document.getElementById('{{ $modalId }}');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                    this.stopCamera();
                }
            },

            switchTab(t) {
                if (this.activeTab === 'camera' && t !== 'camera') {
                    this.stopCamera();
                }
                this.activeTab = t;
                if (t === 'camera') {
                    this.$nextTick(() => this.startCamera());
                }
            },

            prefillAndRegister(serial = '', name = '', productId = '') {
                this.registerSerial = (serial || '').trim();
                if (name) this.registerName = name;
                if (productId) this.registerProductId = productId;
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
                } catch (e) {
                    this.resultData = { found: false, error: e.message };
                    this.hasResult = true;
                } finally {
                    this.isLoading = false;
                }
            },

            async startCamera() {
                const statusEl = document.getElementById('hw-scanner-status');
                const video = document.getElementById('hw-scanner-video');
                if (!video) return;

                if (statusEl) statusEl.innerText = '{{ __('Connecting to camera...') }}';

                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: this.currentFacingMode,
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        },
                        audio: false
                    });

                    video.srcObject = this.stream;
                    await video.play();

                    const tracks = this.stream.getVideoTracks();
                    this.track = tracks[0] || null;

                    if (statusEl) statusEl.innerText = '{{ __('Point camera at hardware barcode or QR tag') }}';
                    this.scanLoop();
                } catch (err) {
                    if (statusEl) statusEl.innerText = '{{ __('Camera unavailable. Use manual lookup or image upload.') }}';
                }
            },

            stopCamera() {
                if (this.animFrame) {
                    cancelAnimationFrame(this.animFrame);
                    this.animFrame = null;
                }
                if (this.stream) {
                    this.stream.getTracks().forEach(t => t.stop());
                    this.stream = null;
                }
                this.track = null;
                const video = document.getElementById('hw-scanner-video');
                if (video) video.srcObject = null;
            },

            async toggleTorch() {
                if (!this.track) return;
                try {
                    const capabilities = this.track.getCapabilities();
                    if (capabilities.torch) {
                        this.torchOn = !this.torchOn;
                        await this.track.applyConstraints({ advanced: [{ torch: this.torchOn }] });
                    }
                } catch (e) {}
            },

            async switchCamera() {
                this.stopCamera();
                this.currentFacingMode = this.currentFacingMode === 'environment' ? 'user' : 'environment';
                await this.startCamera();
            },

            async scanLoop() {
                const video = document.getElementById('hw-scanner-video');
                if (!video || !this.stream || video.readyState < 2) {
                    this.animFrame = requestAnimationFrame(() => this.scanLoop());
                    return;
                }

                if ('BarcodeDetector' in window) {
                    try {
                        const detector = new BarcodeDetector({
                            formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'qr_code', 'upc_a', 'upc_e', 'data_matrix']
                        });
                        const barcodes = await detector.detect(video);
                        if (barcodes.length > 0) {
                            const detectedVal = barcodes[0].rawValue;
                            this.onBarcodeDetected(detectedVal);
                            return; // Stop scan loop
                        }
                    } catch (e) {}
                }

                this.animFrame = requestAnimationFrame(() => this.scanLoop());
            },

            onBarcodeDetected(code) {
                this.beep();
                this.lookupInput = code;
                this.stopCamera();
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
                    gain.gain.value = 0.1;
                    osc.start();
                    setTimeout(() => { osc.stop(); ctx.close(); }, 120);
                } catch (e) {}
            },

            async snapFrame() {
                const video = document.getElementById('hw-scanner-video');
                const canvas = document.getElementById('hw-scanner-canvas');
                if (!video || !canvas) return;

                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                if ('BarcodeDetector' in window) {
                    try {
                        const detector = new BarcodeDetector({
                            formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'qr_code', 'upc_a', 'upc_e', 'data_matrix']
                        });
                        const barcodes = await detector.detect(canvas);
                        if (barcodes.length > 0) {
                            this.onBarcodeDetected(barcodes[0].rawValue);
                            return;
                        }
                    } catch (e) {}
                }

                const statusEl = document.getElementById('hw-scanner-status');
                if (statusEl) statusEl.innerText = '{{ __('No barcode found in current frame. Hold closer and steady.') }}';
            },

            async uploadImage(event) {
                const file = event.target.files[0];
                if (!file) return;

                const img = new Image();
                img.onload = async () => {
                    const canvas = document.getElementById('hw-scanner-canvas') || document.createElement('canvas');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);

                    if ('BarcodeDetector' in window) {
                        try {
                            const detector = new BarcodeDetector({
                                formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'qr_code', 'upc_a', 'upc_e', 'data_matrix']
                            });
                            const barcodes = await detector.detect(canvas);
                            if (barcodes.length > 0) {
                                this.onBarcodeDetected(barcodes[0].rawValue);
                                return;
                            }
                        } catch (e) {}
                    }
                    alert('{{ __('No barcode detected in uploaded photo. Please try another image or type serial manually.') }}');
                };
                img.src = URL.createObjectURL(file);
            }
        };
    }
</script>

