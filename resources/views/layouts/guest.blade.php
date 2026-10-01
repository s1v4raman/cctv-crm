<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Precision IT Systems') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans bg-[#060913] text-slate-100 min-h-screen flex flex-col justify-between antialiased selection:bg-amber-500 selection:text-slate-950 relative overflow-x-hidden">
        <!-- Ambient Cyber Glows -->
        <div class="fixed -top-40 -left-40 w-96 h-96 bg-amber-500/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="fixed -bottom-40 -right-40 w-96 h-96 bg-sky-500/10 rounded-full blur-[120px] pointer-events-none"></div>

        <!-- Minimal Brand Header -->
        <header class="border-b border-slate-800/80 bg-[#0B1120]/80 backdrop-blur-md px-6 py-4 flex justify-between items-center z-10">
            <a href="/" class="flex items-center gap-3 group">
                <img src="{{ asset('logo.png') }}" alt="Precision IT Systems" class="h-10 w-auto object-contain bg-white/95 p-1 rounded-xl">
                <div>
                    <span class="font-heading font-black text-base text-white tracking-tight group-hover:text-amber-400 transition-colors">Precision IT <span class="text-amber-400">Systems</span></span>
                    <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 font-mono">Security Operations</span>
                </div>
            </a>
            <a href="/" class="text-xs text-slate-400 hover:text-white transition flex items-center gap-1.5 font-semibold">
                <span>← Back to Storefront</span>
            </a>
        </header>

        <main class="flex-1 flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 z-10">
            <div class="w-full sm:max-w-md bg-[#0F172A]/90 backdrop-blur-xl border border-white/10 p-8 rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.6)] relative">
                <div class="absolute inset-x-0 -top-px h-px bg-gradient-to-r from-transparent via-amber-400/50 to-transparent"></div>
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-slate-800/80 bg-[#060913] py-4 text-center text-xs text-slate-500 z-10">
            © {{ date('Y') }} {{ config('app.name', 'Precision IT Systems') }}. All rights reserved.
        </footer>
    </body>
</html>
