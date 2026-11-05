<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>{{ __('Offline Mode') }} - Precision IT Systems Field App</title>
    
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/field-192.png') }}">
    <link rel="manifest" href="{{ url('/field/manifest.webmanifest') }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PrecisionField">

    @vite(['resources/css/standalone.css', 'resources/js/app.js'])

    <style>
        :root {
            --crm-accent: #2563eb;
        }
        body {
            background: linear-gradient(180deg, #0b1120 0%, #0f172a 100%);
            color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 antialiased selection:bg-blue-600 selection:text-white">

    <div class="w-full max-w-md mx-auto text-center space-y-6">
        
        <!-- App Brand -->
        <div class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/60 text-xs font-semibold text-slate-300">
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
            <span>Precision IT Systems &bull; Field Operations</span>
        </div>

        <!-- Offline Visual -->
        <div class="relative mx-auto w-24 h-24 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center shadow-xl shadow-black/40">
            <svg class="w-12 h-12 text-amber-400/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a5 5 0 010-7.072m0 0l2.829 2.829M3 3l18 18M3.636 18.364a9 9 0 010-12.728m0 0l2.829 2.829"/>
            </svg>
            <span class="absolute -bottom-1 -right-1 px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-bold uppercase tracking-wider">
                Offline
            </span>
        </div>

        <!-- Text Description -->
        <div class="space-y-2">
            <h1 class="text-2xl font-bold text-white tracking-tight">
                {{ __('No Internet Connection') }}
            </h1>
            <p class="text-sm text-slate-400 max-w-sm mx-auto leading-relaxed">
                {{ __('The field workstation is currently in offline mode. Your session and device data are preserved. We will automatically reconnect as soon as cellular or Wi-Fi signal returns.') }}
            </p>
            <p class="text-xs text-slate-500 font-medium">
                இணைய இணைப்பு இல்லை. சமிக்ஞை கிடைத்தவுடன் பக்கம் தானாகவே மீண்டும் தொடங்கும்.
            </p>
        </div>

        <!-- Live Network Status Monitor -->
        <div class="p-3.5 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between gap-3 text-left">
            <div class="flex items-center gap-3">
                <div class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse shrink-0"></div>
                <div>
                    <div class="text-xs font-semibold text-slate-200" id="network-status-text">
                        Waiting for connection...
                    </div>
                    <div class="text-[11px] text-slate-500">
                        Auto-sync listening on device
                    </div>
                </div>
            </div>
            <button onclick="window.location.reload()" 
                    class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 active:bg-blue-700 text-white font-semibold text-xs transition cursor-pointer shrink-0">
                {{ __('Retry Now') }}
            </button>
        </div>

        <!-- Quick Access Shortcuts -->
        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-2">
            <a href="{{ route('worker.portal') }}" 
               class="w-full sm:w-auto px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium border border-slate-700 transition">
                &larr; {{ __('Worker Portal') }}
            </a>
            <a href="{{ route('technician.dashboard') }}" 
               class="w-full sm:w-auto px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium border border-slate-700 transition">
                {{ __('Technician Workstation') }} &rarr;
            </a>
        </div>

    </div>

    <script>
        // Automatic reconnection detection
        function checkOnline() {
            if (navigator.onLine) {
                document.getElementById('network-status-text').textContent = 'Connection restored! Reloading...';
                setTimeout(() => window.location.reload(), 600);
            }
        }

        window.addEventListener('online', checkOnline);

        // Periodically ping to verify true internet availability
        setInterval(() => {
            if (navigator.onLine) {
                fetch('/favicon.png?check=' + Date.now(), { method: 'HEAD', cache: 'no-store' })
                    .then(response => {
                        if (response.ok) {
                            window.location.reload();
                        }
                    })
                    .catch(() => {});
            }
        }, 4000);
    </script>
</body>
</html>

