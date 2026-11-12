{{-- PWA Registration & Mobile Install Banner --}}
<div x-data="pwaAppInstaller()" x-cloak class="relative z-40">

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

    {{-- Offline & Online Restored Notification Bars (top-of-viewport, non-blocking) --}}
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

            syncGlobalState() {
                const state = {
                    canInstall: this.canInstall,
                    isStandalone: this.isStandalone,
                    isDismissed: this.isDismissed,
                    install: () => this.installApp(),
                    dismiss: () => this.dismissBanner(),
                };
                window.crmPwa = state;
                window.dispatchEvent(new CustomEvent('crm:pwa-state', { detail: state }));
            },

            init() {
                this.syncGlobalState();

                // 1. Register Service Worker
                if ('serviceWorker' in navigator) {
                    window.addEventListener('load', () => {
                        navigator.serviceWorker.register('/field/sw.js', { scope: '/field/' })
                            .then(() => {
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
                    this.syncGlobalState();
                });

                // 3. Detect when app has been installed
                window.addEventListener('appinstalled', () => {
                    this.canInstall = false;
                    this.isStandalone = true;
                    this.deferredPrompt = null;
                    this.syncGlobalState();
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
                this.syncGlobalState();
            },

            dismissBanner() {
                this.isDismissed = true;
                localStorage.setItem('pwa_install_dismissed', 'true');
                this.syncGlobalState();
            }
        };
    }
</script>

