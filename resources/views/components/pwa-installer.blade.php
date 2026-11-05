{{-- PWA Registration & Mobile Install Banner --}}
<div x-data="pwaAppInstaller()" x-cloak class="relative z-50">

    {{-- Offline Floating Notification Bar --}}
    <div x-show="!isOnline" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="-translate-y-full opacity-0"
         class="fixed top-0 inset-x-0 bg-amber-500 text-slate-950 px-4 py-2 text-xs font-bold text-center flex items-center justify-center gap-2 shadow-lg z-50">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a5 5 0 010-7.072m0 0l2.829 2.829M3 3l18 18M3.636 18.364a9 9 0 010-12.728m0 0l2.829 2.829"/>
        </svg>
        <span>{{ __('Offline Mode: Running from local cache. Any saved work will sync when signal returns.') }}</span>
    </div>

    {{-- Online Restored Notification Bar --}}
    <div x-show="showRestoredNotification" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="-translate-y-full opacity-0"
         class="fixed top-0 inset-x-0 bg-emerald-600 text-white px-4 py-2 text-xs font-bold text-center flex items-center justify-center gap-2 shadow-lg z-50">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ __('Connection Restored: Real-time sync active.') }}</span>
    </div>

    {{-- Mobile "Install App" Action Banner --}}
    <div x-show="canInstall && !isDismissed && !isStandalone" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0"
         class="fixed bottom-3 inset-x-3 sm:max-w-md sm:mx-auto p-3.5 rounded-2xl bg-slate-900/95 dark:bg-slate-900/95 text-white border border-blue-500/30 backdrop-blur-md shadow-2xl shadow-black/60 flex items-center gap-3 z-40">
        
        <img src="{{ asset('icons/icon-192x192.png') }}" alt="App Icon" class="w-11 h-11 rounded-xl shrink-0 shadow-md border border-slate-700/60 object-cover">
        
        <div class="flex-1 min-w-0">
            <div class="text-xs font-bold text-white tracking-tight flex items-center gap-1.5 truncate">
                <span>Precision IT Field App</span>
                <span class="px-1.5 py-0.2 rounded text-[9px] bg-blue-600/80 font-bold uppercase tracking-wider">PWA</span>
            </div>
            <div class="text-[11px] text-slate-300 truncate">
                {{ __('Install to home screen for 1-tap offline use') }}
            </div>
        </div>

        <div class="flex items-center gap-1.5 shrink-0">
            <button @click="installApp()" 
                    class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 active:bg-blue-700 text-white font-bold text-xs shadow-md transition cursor-pointer">
                {{ __('Install') }}
            </button>
            <button @click="dismissBanner()" 
                    class="p-1 rounded-lg text-slate-400 hover:text-white transition cursor-pointer"
                    title="{{ __('Dismiss') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    function pwaAppInstaller() {
        return {
            deferredPrompt: null,
            canInstall: false,
            isStandalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
            isDismissed: localStorage.getItem('pwa_install_dismissed') === 'true',
            isOnline: navigator.onLine,
            showRestoredNotification: false,

            init() {
                // 1. Register Service Worker
                if ('serviceWorker' in navigator) {
                    window.addEventListener('load', () => {
                        navigator.serviceWorker.register('/field/sw.js', { scope: '/field/' })
                            .then((reg) => {
                                // Service Worker registered with scope: /field/
                            })
                            .catch((err) => {
                                console.warn('Service Worker registration failed:', err);
                            });
                    });
                }

                // 2. Intercept beforeinstallprompt for native install button
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;
                    this.canInstall = true;
                });

                // 3. Detect when app has been installed
                window.addEventListener('appinstalled', () => {
                    this.canInstall = false;
                    this.deferredPrompt = null;
                    // console.log('PWA installed successfully');
                });

                // 4. Listen for Network Status changes
                window.addEventListener('online', () => {
                    this.isOnline = true;
                    this.showRestoredNotification = true;
                    setTimeout(() => {
                        this.showRestoredNotification = false;
                    }, 3500);
                });

                window.addEventListener('offline', () => {
                    this.isOnline = false;
                });
            },

            async installApp() {
                if (!this.deferredPrompt) {
                    alert('To install on iOS: tap the Share button in Safari, then select "Add to Home Screen".');
                    return;
                }
                this.deferredPrompt.prompt();
                const { outcome } = await this.deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    this.canInstall = false;
                }
                this.deferredPrompt = null;
            },

            dismissBanner() {
                this.isDismissed = true;
                localStorage.setItem('pwa_install_dismissed', 'true');
            }
        };
    }
</script>

