<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Precision IT Systems') }}</title>

        <!-- Zoho CRM Theme & Realtime Dynamic Logo Recoloring Engine -->
        <x-crm-theme-init />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Tailwind CSS & Alpine.js CDN Guard (Guarantees normal styling across all HTTP servers & environments) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                            heading: ['"Outfit"', 'sans-serif'],
                        },
                        colors: {
                            brand: {
                                blue: '#2563eb',
                                blueHover: '#1d4ed8',
                                lightBg: '#eff4fc',
                            }
                        }
                    }
                }
            }
        </script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Scripts & Styles via Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans bg-[#060913] text-slate-100 min-h-screen flex flex-col justify-between antialiased selection:bg-blue-600 selection:text-white relative overflow-x-hidden">
        <!-- Ambient Cyber Glows -->
        <div class="fixed -top-40 -left-40 w-96 h-96 bg-blue-600/15 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="fixed -bottom-40 -right-40 w-96 h-96 bg-sky-500/15 rounded-full blur-[140px] pointer-events-none"></div>

        <!-- Minimal Brand Header -->
        <header class="border-b border-slate-800/80 bg-[#0B1120]/80 backdrop-blur-md px-6 py-3.5 flex justify-between items-center z-10">
            <a href="/" class="flex items-center gap-3 group">
                <img src="{{ asset('logo.png') }}" alt="Precision IT Systems" class="crm-brand-logo h-12 w-12 object-contain group-hover:scale-105 transition-transform drop-shadow-xs">
                <div>
                    <span class="font-heading font-black text-lg text-white tracking-tight group-hover:text-blue-400 transition-colors">Precision IT <span class="crm-brand-accent-text text-blue-500">Systems</span></span>
                    <span class="block text-xs font-bold uppercase tracking-widest text-slate-400 font-mono">Security Operations</span>
                </div>
            </a>
            <a href="/" class="text-sm text-slate-400 hover:text-white transition flex items-center gap-1.5 font-semibold">
                <span>← Back to Storefront</span>
            </a>
        </header>

        <main class="flex-1 flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 z-10">
            <div class="w-full sm:max-w-md bg-[#0F172A]/90 backdrop-blur-xl border border-white/10 p-8 rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.6)] relative">
                <div class="absolute inset-x-0 -top-px h-px bg-gradient-to-r from-transparent via-blue-500/50 to-transparent"></div>
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-slate-800/80 bg-[#060913] py-4 text-center text-sm text-slate-500 z-10">
            © {{ date('Y') }} {{ config('app.name', 'Precision IT Systems') }}. All rights reserved.
        </footer>
    </body>
</html>
