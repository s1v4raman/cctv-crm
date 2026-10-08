<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Precision IT Systems - Innovative AI CCTV Cameras & Smart Security Solutions. 4K Color Night Vision, Enterprise Surveillance, Cloud VMS, and 24/7 Support.">
    <title>Precision IT Systems | Smart AI CCTV Cameras & Security Solutions</title>

    <!-- Signature Brand Colors Permanently Locked (Independent from CRM user themes) -->
    <style>
        :root {
            --crm-accent: #1366e2 !important;
            --crm-accent-hover: #0e4db0 !important;
            --crm-accent-shadow: rgba(19, 102, 226, 0.25) !important;
            --brand-blue: #1366e2 !important;
            --brand-blue-hover: #0e4db0 !important;
        }
    </style>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Scripts & Styles via Vite (Precompiled local assets guarantee normal styling even if Edge or extensions block CDNs) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind CSS Play CDN -->
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
                            blue: '#1366e2',
                            blueDark: '#0e4db0',
                            blueLight: '#eff6ff',
                            dark: '#060913',
                            surface: '#0f172a',
                            surfaceHover: '#1e293b',
                            border: '#1e293b',
                            borderLight: '#e2e8f0',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif;
        }

        /* PathSoft Elevated Service Card */
        .pathsoft-card {
            background-color: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 1.25rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        }

        .dark .pathsoft-card {
            background-color: #0f172a;
            border-color: #1e293b;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3);
        }

        .pathsoft-card:hover {
            transform: translateY(-5px);
            border-color: #3b82f6;
            box-shadow: 0 20px 35px -10px rgba(19, 102, 226, 0.15);
        }

        /* Fluid curved ribbon wave */
        .wave-bg {
            background: linear-gradient(180deg, #f0f7ff 0%, #e2efff 100%);
        }
        .dark .wave-bg {
            background: linear-gradient(180deg, #0b1329 0%, #060913 100%);
        }

        /* Smooth scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #334155;
        }
    </style>
</head>

<body class="bg-white dark:bg-[#060913] text-slate-700 dark:text-slate-200 antialiased min-h-screen flex flex-col selection:bg-blue-600 selection:text-white transition-colors duration-200">



    <!-- 2. MAIN NAVBAR (PathSoft Layout) -->
    <!-- 2. MAIN NAVBAR -->
    <header class="sticky top-0 z-50 bg-white/95 dark:bg-[#060913]/95 backdrop-blur-md border-b border-slate-100 dark:border-slate-800 transition-colors">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-3 xl:gap-6">
            
            <!-- Brand Logo & Company Name (Maximized Logo & Clean Responsive Lockup) -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 sm:gap-3.5 group shrink-0">
                <img src="{{ asset('logo.png') }}" alt="Precision IT Systems" class="crm-brand-logo h-14 w-14 sm:h-16 sm:w-16 md:h-[68px] md:w-[68px] lg:h-[72px] lg:w-[72px] object-contain group-hover:scale-105 transition-transform shrink-0 drop-shadow-xs">
                <div class="flex flex-col min-w-0">
                    <span class="text-lg sm:text-2xl lg:text-[26px] font-black font-heading tracking-tight text-slate-900 dark:text-white leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                        Precision IT <span class="crm-brand-accent-text text-blue-600 dark:text-blue-400">Systems</span>
                    </span>
                    <span class="hidden sm:block text-[10px] sm:text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                        Security &amp; AI Surveillance
                    </span>
                </div>
            </a>

            <!-- Navigation Links (Centered, seamlessly filling the gap on lg and xl viewports) -->
            <nav class="hidden lg:flex items-center space-x-3.5 xl:space-x-6 text-sm xl:text-[15px] font-semibold text-slate-600 dark:text-slate-300">
                <a href="#home" class="px-2 py-1.5 rounded-lg text-blue-600 dark:text-blue-400 font-bold hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors">Home</a>
                <a href="#about" class="px-2 py-1.5 rounded-lg hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors">About Us</a>
                <div class="relative group py-1.5">
                    <a href="#services" class="flex items-center space-x-1 px-2 py-1.5 rounded-lg hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors">
                        <span>Services</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </a>
                    <!-- Dropdown -->
                    <div class="absolute left-0 top-full hidden group-hover:block w-56 bg-white dark:bg-slate-900 shadow-xl rounded-xl border border-slate-100 dark:border-slate-800 py-2 z-50">
                        <a href="#services" class="block px-4 py-2 text-sm hover:bg-blue-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300">Corporate Solutions</a>
                        <a href="#services" class="block px-4 py-2 text-sm hover:bg-blue-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300">24/7 AMC & Support</a>
                        <a href="#services" class="block px-4 py-2 text-sm hover:bg-blue-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300">Cloud AI Video Telemetry</a>
                    </div>
                </div>
                <a href="#solutions" class="px-2 py-1.5 rounded-lg hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors">Solutions</a>
                <a href="#why-us" class="px-2 py-1.5 rounded-lg hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors">Why Us</a>
                <a href="#contact" class="px-2 py-1.5 rounded-lg hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors">Contact</a>
            </nav>

            <!-- Actions (Search, Theme Toggle, Profile/Register-Login, CTA, Mobile Menu) -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                
                <!-- 1. Search Button -->
                <button type="button" onclick="openSearchModal()"
                    class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 flex items-center justify-center transition-all cursor-pointer shrink-0"
                    title="Search Solutions & Services">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                <!-- 2. Theme Toggle Button -->
                <button type="button" id="theme-toggle" onclick="toggleTheme()"
                    class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center transition-all cursor-pointer shrink-0"
                    title="Toggle Dark / White Mode">
                    <!-- Sun Icon (visible in dark mode) -->
                    <svg id="theme-toggle-light-icon" class="w-4 h-4 hidden text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                    </svg>
                    <!-- Moon Icon (visible in light mode) -->
                    <svg id="theme-toggle-dark-icon" class="w-4 h-4 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                    </svg>
                </button>

                <!-- 3. PROPER PROFILE / ACCOUNT VIEW (Always visible, protected from overflow) -->
                <div class="relative group shrink-0">
                    @auth
                        <!-- Authenticated User Profile Initial Badge -->
                        <button type="button"
                            class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-base text-white shadow-md transition-all duration-200 hover:scale-105 active:scale-95 focus:outline-none ring-2 ring-slate-200/90 dark:ring-slate-700/80 hover:ring-blue-500/50 cursor-pointer select-none shrink-0"
                            style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);"
                            title="My Account: {{ Auth::user()->name }} (Click to view profile)">
                            <span class="drop-shadow-xs">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </button>
                    @else
                        <!-- Guest Portal / Profile Access Button -->
                        <button type="button"
                            class="flex items-center gap-1.5 px-2.5 py-1.5 sm:px-3 sm:py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 border border-slate-200/80 dark:border-slate-700 transition-all cursor-pointer shrink-0"
                            title="Sign in / Register">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-xs font-bold hidden sm:inline">Profile</span>
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    @endauth

                    <!-- Auth Dropdown Menu -->
                    <div class="absolute right-0 top-full mt-2 hidden group-hover:block w-64 bg-white dark:bg-slate-900 shadow-2xl rounded-2xl border border-slate-100 dark:border-slate-800 py-2.5 z-50 transition-all">
                        @auth
                            <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800 mb-1">
                                <span class="text-[10px] text-blue-600 font-bold block uppercase tracking-wider">Hello, {{ explode(' ', Auth::user()->name)[0] }}</span>
                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            @if(Auth::user()->isCustomer())
                                <a href="{{ route('portal.dashboard') }}" class="flex items-center space-x-2.5 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 transition-colors">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    <span>Customer Portal</span>
                                </a>
                            @elseif(Auth::user()->role === 'technician')
                                <a href="{{ route('technician.dashboard') }}" class="flex items-center space-x-2.5 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 transition-colors">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                                    <span>Technician Station</span>
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 transition-colors">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    <span>ERP Dashboard</span>
                                </a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2.5 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 transition-colors">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Profile Settings</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 pt-1 border-t border-slate-100 dark:border-slate-800">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center space-x-2.5 px-4 py-2.5 text-sm font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <span>Log Out</span>
                                </button>
                            </form>
                        @else
                            <div class="px-4 pb-2 border-b border-slate-100 dark:border-slate-800 mb-1">
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Client Access</span>
                            </div>
                            <a href="{{ route('login') }}" class="flex items-center space-x-3 px-4 py-2.5 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                </div>
                                <div>
                                    <span class="block font-bold">Client Login</span>
                                    <span class="block text-[10px] text-slate-400 font-normal">Sign in to your account</span>
                                </div>
                            </a>
                            <a href="{{ route('register') }}" class="flex items-center space-x-3 px-4 py-2.5 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-800 hover:text-emerald-600 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                </div>
                                <div>
                                    <span class="block font-bold">Register Account</span>
                                    <span class="block text-[10px] text-slate-400 font-normal">Create client profile</span>
                                </div>
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- 4. Get In Touch Primary Pill Button (Shown on md+ screens so it never pushes the profile button off) -->
                <a href="#contact" class="hidden md:inline-flex items-center justify-center px-5 py-2.5 sm:px-6 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm sm:text-base font-bold rounded-xl shadow-md shadow-blue-500/25 transition-all hover:scale-105 shrink-0">
                    Get In Touch
                </a>

                <!-- 5. Mobile Navigation Menu Toggle Button (Strictly lg:hidden so desktop doesn't clutter!) -->
                <div class="relative lg:hidden" id="navMenuDropdownContainer">
                    <button type="button" onclick="toggleNavMenuDropdown()"
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center transition-all cursor-pointer shrink-0"
                        title="Menu Options">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <!-- Clean Dropdown Menu List -->
                    <div id="navMenuDropdownList" class="absolute right-0 top-full mt-2 hidden w-64 bg-white dark:bg-slate-900 shadow-2xl rounded-2xl border border-slate-100 dark:border-slate-800 py-3 z-50 transition-all">
                        <div class="px-4 pb-2 border-b border-slate-100 dark:border-slate-800 mb-2 flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Quick Navigation</span>
                            <span class="text-[10px] font-bold text-blue-600">Precision IT</span>
                        </div>
                        <nav class="space-y-0.5 px-2">
                            <a href="#home" onclick="closeNavMenuDropdown()" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 rounded-xl transition-colors">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Home</span>
                            </a>
                            <a href="#about" onclick="closeNavMenuDropdown()" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 rounded-xl transition-colors">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>About Us</span>
                            </a>
                            <a href="#services" onclick="closeNavMenuDropdown()" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 rounded-xl transition-colors">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>Services &amp; AMC</span>
                            </a>
                            <a href="#why-us" onclick="closeNavMenuDropdown()" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 rounded-xl transition-colors">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Why Choose Us</span>
                            </a>
                            <a href="#contact" onclick="closeNavMenuDropdown(); startAuditFlow();" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 rounded-xl transition-colors">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>Request Fast Proposal</span>
                            </a>
                        </nav>
                        <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800 px-3">
                            <a href="https://wa.me/919677257774" target="_blank" class="flex items-center justify-center space-x-2 w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition-colors">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.102-.115.434-.506.549-.68.116-.173.232-.145.39-.087s1.011.477 1.184.564.289.13.332.203c.044.072.044.419-.1.824z"/></svg>
                                <span>WhatsApp Hotline</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <main class="flex-grow">
        <!-- 3. HERO SECTION (Exact PathSoft Alignment & Style) -->
        <section id="home" class="relative overflow-hidden pt-12 pb-20 lg:pt-16 lg:pb-24 bg-gradient-to-b from-blue-50/50 via-white to-white dark:from-slate-900/40 dark:via-[#060913] dark:to-[#060913]">
            <div class="max-w-7xl mx-auto px-4 sm:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    <!-- Hero Content (Left) -->
                    <div class="lg:col-span-6 space-y-6">
                        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-100/70 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                            <span>Next-Gen Security Engineering</span>
                        </div>

                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold font-heading text-slate-900 dark:text-white tracking-tight leading-[1.15]">
                            Innovative IT <br class="hidden sm:block">
                            <span class="text-blue-600">Solutions for</span> <br class="hidden sm:block">
                            Your Business
                        </h1>

                        <p class="text-base sm:text-lg text-slate-600 dark:text-slate-400 leading-relaxed max-w-xl">
                            Since our founding, we have been committed to providing high quality and sustainable security solutions, enterprise AI surveillance, and 24/7 technical surveillance for modern businesses.
                        </p>

                        <!-- Action Buttons (PathSoft exact styling) -->
                        <div class="flex flex-wrap items-center gap-4 pt-2">
                            <!-- Primary Learn More with dropdown arrow -->
                            <a href="#services" class="inline-flex items-center justify-center space-x-2 px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg shadow-lg shadow-blue-500/25 transition-all">
                                <span>Learn More</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>

                            <!-- Secondary Get In Touch with border -->
                            <a href="#contact" class="inline-flex items-center justify-center space-x-2 px-6 py-3.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 font-semibold text-sm rounded-lg transition-all shadow-sm">
                                <span>Get In Touch</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                        </div>
                    </div>                    <!-- Hero Visual (Right) - Dynamic Slide-by-Slide Showcase -->
                    <div class="lg:col-span-6 relative">
                        <div class="relative mx-auto max-w-lg lg:max-w-none">
                            <!-- Soft glowing backdrop -->
                            <div class="absolute -inset-2 bg-gradient-to-r from-blue-600/20 via-indigo-500/20 to-cyan-500/20 rounded-3xl blur-2xl -z-10 animate-pulse"></div>
                            
                            <!-- Hero Slider Main Container -->
                            <div id="heroSliderContainer" class="relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xl bg-slate-950 group select-none"
                                onmouseenter="pauseHeroSlider()" onmouseleave="resumeHeroSlider()">
                                
                                <!-- Top Progress Countdown Bar -->
                                <div class="absolute top-0 left-0 right-0 h-1 bg-white/10 z-30">
                                    <div id="heroSliderProgress" class="h-full bg-gradient-to-r from-blue-500 to-cyan-400 transition-all ease-linear" style="width: 0%;"></div>
                                </div>

                                <!-- Slide Counter & View Fullscreen Pill (Top Right) -->
                                <div class="absolute top-4 right-4 z-30 flex items-center space-x-2">
                                    <span id="heroSlideCounter" class="px-2.5 py-1 rounded-full bg-slate-900/80 backdrop-blur-md border border-white/15 text-white text-[11px] font-bold font-mono tracking-wider shadow-lg">
                                        01 / 08
                                    </span>
                                    <button type="button" onclick="openPictureLightbox(currentHeroIndex)"
                                        class="p-1.5 rounded-full bg-slate-900/80 hover:bg-blue-600 backdrop-blur-md border border-white/15 text-white/90 hover:text-white transition-all shadow-lg hover:scale-110"
                                        title="View Full Picture">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Slider Viewport (Height 380px to 460px responsive) -->
                                <div class="relative w-full h-[360px] sm:h-[420px] md:h-[460px] overflow-hidden bg-slate-950">
                                    
                                    <!-- Slide 1: CCTV Field Technician with Orange Helmet -->
                                    <div class="hero-slide absolute inset-0 transition-all duration-700 ease-out opacity-100 scale-100 z-10 cursor-pointer" onclick="openPictureLightbox(0)">
                                        <img src="{{ asset('images/slides/slide1_technician_cctv_orange_helmet.png') }}" 
                                            alt="Professional CCTV Installation Technician"
                                            class="w-full h-full object-cover object-center transform transition-transform duration-700 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                                        <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4 text-white space-y-1 pointer-events-none">
                                            <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-blue-600/90 backdrop-blur-sm text-[10px] font-bold uppercase tracking-wider text-white">
                                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-300 animate-ping"></span>
                                                <span>CCTV Engineering</span>
                                            </div>
                                            <h3 class="text-sm sm:text-base font-bold font-heading text-white drop-shadow-md line-clamp-1">
                                                Expert Outdoor CCTV Installation & Junction Wiring
                                            </h3>
                                        </div>
                                    </div>

                                    <!-- Slide 2: Technician Aligning Bullet Camera -->
                                    <div class="hero-slide absolute inset-0 transition-all duration-700 ease-out opacity-0 scale-95 z-0 pointer-events-none cursor-pointer" onclick="openPictureLightbox(1)">
                                        <img src="{{ asset('images/slides/slide2_technician_bullet_camera.jpg') }}" 
                                            alt="Bullet Camera Optical Alignment"
                                            class="w-full h-full object-cover object-center transform transition-transform duration-700 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                                        <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4 text-white space-y-1 pointer-events-none">
                                            <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-emerald-600/90 backdrop-blur-sm text-[10px] font-bold uppercase tracking-wider text-white">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                                <span>Perimeter Security</span>
                                            </div>
                                            <h3 class="text-sm sm:text-base font-bold font-heading text-white drop-shadow-md line-clamp-1">
                                                Precision IP Bullet Camera Optical Calibration
                                            </h3>
                                        </div>
                                    </div>

                                    <!-- Slide 3: Technician Mounting 360 Dome Camera -->
                                    <div class="hero-slide absolute inset-0 transition-all duration-700 ease-out opacity-0 scale-95 z-0 pointer-events-none cursor-pointer" onclick="openPictureLightbox(2)">
                                        <img src="{{ asset('images/slides/slide3_technician_dome_camera.png') }}" 
                                            alt="AI Dome Camera Setup"
                                            class="w-full h-full object-cover object-center transform transition-transform duration-700 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                                        <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4 text-white space-y-1 pointer-events-none">
                                            <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-indigo-600/90 backdrop-blur-sm text-[10px] font-bold uppercase tracking-wider text-white">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                <span>Commercial & Indoor</span>
                                            </div>
                                            <h3 class="text-sm sm:text-base font-bold font-heading text-white drop-shadow-md line-clamp-1">
                                                Vandal-Resistant 360° AI Dome Camera Setup
                                            </h3>
                                        </div>
                                    </div>

                                    <!-- Slide 4: Specialist with Multi-camera Tools -->
                                    <div class="hero-slide absolute inset-0 transition-all duration-700 ease-out opacity-0 scale-95 z-0 pointer-events-none cursor-pointer" onclick="openPictureLightbox(3)">
                                        <img src="{{ asset('images/slides/slide4_cctv_specialist_tools.png') }}" 
                                            alt="Turnkey Surveillance Equipment"
                                            class="w-full h-full object-cover object-center transform transition-transform duration-700 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                                        <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4 text-white space-y-1 pointer-events-none">
                                            <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-amber-600/90 backdrop-blur-sm text-[10px] font-bold uppercase tracking-wider text-white">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                <span>Hardware Diagnostics</span>
                                            </div>
                                            <h3 class="text-sm sm:text-base font-bold font-heading text-white drop-shadow-md line-clamp-1">
                                                Commercial Grade Multisensor Hardware & Tools
                                            </h3>
                                        </div>
                                    </div>

                                    <!-- Slide 5: AI Face Recognition Access Control Device -->
                                    <div class="hero-slide absolute inset-0 transition-all duration-700 ease-out opacity-0 scale-95 z-0 pointer-events-none cursor-pointer" onclick="openPictureLightbox(4)">
                                        <img src="{{ asset('images/slides/slide5_ai_face_recognition_attendance.png') }}" 
                                            alt="AI Face Recognition Attendance Device"
                                            class="w-full h-full object-contain bg-slate-900/90 object-center transform transition-transform duration-700 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                                        <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4 text-white space-y-1 pointer-events-none">
                                            <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-cyan-600/90 backdrop-blur-sm text-[10px] font-bold uppercase tracking-wider text-white">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                                <span>AI Biometrics</span>
                                            </div>
                                            <h3 class="text-sm sm:text-base font-bold font-heading text-white drop-shadow-md line-clamp-1">
                                                AI Face Recognition Access Control & Time Attendance
                                            </h3>
                                        </div>
                                    </div>

                                    <!-- Slide 6: Multi-Identification Methods -->
                                    <div class="hero-slide absolute inset-0 transition-all duration-700 ease-out opacity-0 scale-95 z-0 pointer-events-none cursor-pointer" onclick="openPictureLightbox(5)">
                                        <img src="{{ asset('images/slides/slide6_multi_identification_access_methods.png') }}" 
                                            alt="Multiple Biometric Identification Methods"
                                            class="w-full h-full object-contain bg-slate-900/90 object-center transform transition-transform duration-700 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                                        <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4 text-white space-y-1 pointer-events-none">
                                            <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-blue-600/90 backdrop-blur-sm text-[10px] font-bold uppercase tracking-wider text-white">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                <span>Access Control</span>
                                            </div>
                                            <h3 class="text-sm sm:text-base font-bold font-heading text-white drop-shadow-md line-clamp-1">
                                                10 Multi-Modal Identification & Security Combinations
                                            </h3>
                                        </div>
                                    </div>

                                    <!-- Slide 7: Biometric Fingerprint Terminal -->
                                    <div class="hero-slide absolute inset-0 transition-all duration-700 ease-out opacity-0 scale-95 z-0 pointer-events-none cursor-pointer" onclick="openPictureLightbox(6)">
                                        <img src="{{ asset('images/slides/slide7_biometric_fingerprint_terminal.png') }}" 
                                            alt="Standalone Fingerprint Biometric Device"
                                            class="w-full h-full object-contain bg-slate-900/90 object-center transform transition-transform duration-700 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                                        <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4 text-white space-y-1 pointer-events-none">
                                            <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-purple-600/90 backdrop-blur-sm text-[10px] font-bold uppercase tracking-wider text-white">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                <span>Door Access</span>
                                            </div>
                                            <h3 class="text-sm sm:text-base font-bold font-heading text-white drop-shadow-md line-clamp-1">
                                                High-Security Standalone Biometric Terminals
                                            </h3>
                                        </div>
                                    </div>

                                    <!-- Slide 8: Attendance Management Dashboard & Verification -->
                                    <div class="hero-slide absolute inset-0 transition-all duration-700 ease-out opacity-0 scale-95 z-0 pointer-events-none cursor-pointer" onclick="openPictureLightbox(7)">
                                        <img src="{{ asset('images/slides/slide8_attendance_management_system.png') }}" 
                                            alt="Cloud Attendance Management System"
                                            class="w-full h-full object-contain bg-slate-900/90 object-center transform transition-transform duration-700 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                                        <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4 text-white space-y-1 pointer-events-none">
                                            <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-emerald-600/90 backdrop-blur-sm text-[10px] font-bold uppercase tracking-wider text-white">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                                <span>Cloud HR Suite</span>
                                            </div>
                                            <h3 class="text-sm sm:text-base font-bold font-heading text-white drop-shadow-md line-clamp-1">
                                                Unified HR Attendance & Real-Time Check-in Suite
                                            </h3>
                                        </div>
                                    </div>

                                </div>

                                <!-- Left & Right Navigation Arrows -->
                                <button type="button" onclick="prevHeroSlide(event)" 
                                    class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-slate-900/70 hover:bg-blue-600 text-white backdrop-blur-md border border-white/20 flex items-center justify-center transition-all shadow-xl hover:scale-110"
                                    title="Previous Image">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                                <button type="button" onclick="nextHeroSlide(event)" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-slate-900/70 hover:bg-blue-600 text-white backdrop-blur-md border border-white/20 flex items-center justify-center transition-all shadow-xl hover:scale-110"
                                    title="Next Image">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>

                                <!-- Bottom Slide Dots Strip -->
                                <div class="absolute bottom-2.5 left-1/2 -translate-x-1/2 z-20 flex items-center space-x-1.5 px-3 py-1.5 rounded-full bg-slate-950/70 backdrop-blur-md border border-white/10">
                                    <button type="button" onclick="setHeroSlide(0)" class="hero-dot w-6 h-2 rounded-full bg-blue-500 transition-all" title="Slide 1"></button>
                                    <button type="button" onclick="setHeroSlide(1)" class="hero-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/80 transition-all" title="Slide 2"></button>
                                    <button type="button" onclick="setHeroSlide(2)" class="hero-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/80 transition-all" title="Slide 3"></button>
                                    <button type="button" onclick="setHeroSlide(3)" class="hero-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/80 transition-all" title="Slide 4"></button>
                                    <button type="button" onclick="setHeroSlide(4)" class="hero-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/80 transition-all" title="Slide 5"></button>
                                    <button type="button" onclick="setHeroSlide(5)" class="hero-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/80 transition-all" title="Slide 6"></button>
                                    <button type="button" onclick="setHeroSlide(6)" class="hero-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/80 transition-all" title="Slide 7"></button>
                                    <button type="button" onclick="setHeroSlide(7)" class="hero-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/80 transition-all" title="Slide 8"></button>
                                </div>
                            </div>

                            <!-- Badge Row - placed below slider, no overlap -->
                            <div class="mt-3 flex items-center gap-3 px-1">
                                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div id="heroFloatingBadgeTitle" class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider truncate">CCTV &amp; Biometrics</div>
                                    <div id="heroFloatingBadgeSub" class="text-xs text-slate-500 dark:text-slate-400 truncate">ISO 27001 Certified Security</div>
                                </div>
                                <div class="hidden sm:flex items-center gap-1.5 text-[10px] text-slate-400 dark:text-slate-500 font-medium flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Live System</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. "OUR SERVICES" SECTION (3 Floating White Cards with Circular Blue Icons) -->
        <section id="services" class="py-20 bg-slate-50/60 dark:bg-[#080d1a] border-t border-b border-slate-100 dark:border-slate-800/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-8">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-extrabold font-heading text-slate-900 dark:text-white tracking-tight">
                        Our Services
                    </h2>
                    <div class="w-12 h-1 bg-blue-600 rounded-full mx-auto mt-3 mb-4"></div>
                    <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base">
                        End-to-end intelligent security architecture, commercial surveillance engineering, and round-the-clock technical operations.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Service Card 1 -->
                    <div class="pathsoft-card p-8 text-center flex flex-col items-center">
                        <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30 mb-6">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold font-heading text-slate-900 dark:text-white mb-3">
                            Corporate Solutions
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-6 flex-grow">
                            Turnkey commercial CCTV infrastructure, thermal perimeter protection, automated access gates, and concealed cabling for enterprise premises.
                        </p>
                        <a href="#services" class="inline-flex items-center space-x-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow transition-all">
                            <span>Learn More</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </a>
                    </div>

                    <!-- Service Card 2 -->
                    <div class="pathsoft-card p-8 text-center flex flex-col items-center">
                        <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30 mb-6">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold font-heading text-slate-900 dark:text-white mb-3">
                            Call Center Solutions
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-6 flex-grow">
                            24/7 dedicated support desk, proactive telemetry health diagnostics, immediate replacement inventory, and guaranteed 2-hour technician dispatch.
                        </p>
                        <a href="#contact" class="inline-flex items-center space-x-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow transition-all">
                            <span>Learn More</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </a>
                    </div>

                    <!-- Service Card 3 -->
                    <div class="pathsoft-card p-8 text-center flex flex-col items-center">
                        <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30 mb-6">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold font-heading text-slate-900 dark:text-white mb-3">
                            Cloud Development
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-6 flex-grow">
                            Multi-site unified cloud VMS, encrypted off-site cloud backups, AI license plate recognition (ANPR), and mobile biometric event triggers.
                        </p>
                        <a href="#contact" onclick="startAuditFlow()" class="inline-flex items-center space-x-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow transition-all">
                            <span>Learn More</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. "WHY CHOOSE US" SECTION (Numbered 01 to 06) -->
        <section id="why-us" class="py-20 bg-white dark:bg-[#060913]">
            <div class="max-w-7xl mx-auto px-4 sm:px-8">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-extrabold font-heading text-slate-900 dark:text-white tracking-tight">
                        Why Choose Us
                    </h2>
                    <div class="w-12 h-1 bg-blue-600 rounded-full mx-auto mt-3 mb-4"></div>
                    <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base">
                        Leading corporate enterprises and organizations rely on our certified security engineering, speed, and precision.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-12">
                    <!-- Feature 01 -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-900/60 flex items-center justify-center text-xs font-bold font-mono shrink-0">01</span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white font-heading">High Quality Hardware</h3>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed pl-11">
                            Enterprise Sony Starvis optical sensors, IP67 weatherproof rating, and vandal-resistant IK10 metal housings engineered for harsh industrial environments.
                        </p>
                    </div>

                    <!-- Feature 02 -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-900/60 flex items-center justify-center text-xs font-bold font-mono shrink-0">02</span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white font-heading">Dedicated 24/7 Support</h3>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed pl-11">
                            Round-the-clock remote network diagnostics and dedicated certified field service engineers on call with a guaranteed 2-hour emergency site dispatch SLA.
                        </p>
                    </div>

                    <!-- Feature 03 -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-900/60 flex items-center justify-center text-xs font-bold font-mono shrink-0">03</span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white font-heading">30-Day Money-back Guarantee</h3>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed pl-11">
                            Uncompromising commitment to video clarity and uptime. 30-day complete satisfaction guarantee and hassle-free immediate hardware replacements.
                        </p>
                    </div>

                    <!-- Feature 04 -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-900/60 flex items-center justify-center text-xs font-bold font-mono shrink-0">04</span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white font-heading">Agile and Fast Working Style</h3>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed pl-11">
                            Comprehensive site survey completed within 24 hours, structured CAD wiring plans, and fast concealed cabling execution with zero workplace disruption.
                        </p>
                    </div>

                    <!-- Feature 05 -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-900/60 flex items-center justify-center text-xs font-bold font-mono shrink-0">05</span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white font-heading">Complimentary Mobile Clients</h3>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed pl-11">
                            Feature-rich iOS, Android, and desktop monitoring clients with instant AI push notifications included lifetime with zero recurring monthly subscription fees.
                        </p>
                    </div>

                    <!-- Feature 06 -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-900/60 flex items-center justify-center text-xs font-bold font-mono shrink-0">06</span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white font-heading">High Level of Usability</h3>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed pl-11">
                            Intuitive touchscreen-friendly NVR interfaces, AI human and vehicle smart search filters, instant playback timeline scrubbing, and single-click video evidence export.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. "10+ YEARS OF EXPERIENCE" & METRICS SECTION (Fluid Curved Wave Graphic) -->
        <section class="relative py-20 overflow-hidden wave-bg transition-colors">
            <!-- Fluid Wave SVG Curves matching PathSoft reference -->
            <div class="absolute inset-0 pointer-events-none opacity-40 dark:opacity-20">
                <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 1440 400" fill="none">
                    <path d="M-100 250 C 300 400, 600 100, 1000 250 C 1200 320, 1400 200, 1600 300 L 1600 400 L -100 400 Z" fill="#3b82f6" fill-opacity="0.15"/>
                    <path d="M-50 150 C 350 50, 700 350, 1100 180 C 1300 100, 1500 280, 1650 200 L 1650 400 L -50 400 Z" fill="#60a5fa" fill-opacity="0.12"/>
                </svg>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                    <!-- Left: Circular Experience Badge -->
                    <div class="lg:col-span-4 flex justify-center lg:justify-start">
                        <div class="relative w-56 h-56 rounded-full bg-gradient-to-tr from-blue-700 to-blue-500 text-white flex flex-col items-center justify-center p-6 shadow-2xl shadow-blue-600/30 border-8 border-white/80 dark:border-slate-800">
                            <!-- Outer pulsing ring accent -->
                            <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-cyan-400 border-4 border-white dark:border-slate-800"></div>
                            
                            <span class="text-6xl font-black font-heading tracking-tight leading-none">10+</span>
                            <span class="text-base font-bold uppercase tracking-wider text-blue-100 mt-2 text-center">Years Of<br>Experience</span>
                        </div>
                    </div>

                    <!-- Right: 4 Metrics Grid -->
                    <div class="lg:col-span-8">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                            <!-- Metric 1 -->
                            <div class="space-y-2 border-l-2 border-blue-500/40 pl-5">
                                <div class="text-4xl font-extrabold font-heading text-blue-600 dark:text-blue-400">2K+</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white">Installations Done</div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Enterprise AI video surveillance projects deployed across corporations & institutions.</p>
                            </div>

                            <!-- Metric 2 -->
                            <div class="space-y-2 border-l-2 border-blue-500/40 pl-5">
                                <div class="text-4xl font-extrabold font-heading text-blue-600 dark:text-blue-400">40+</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white">Consultants</div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Certified CCTV engineers and network specialists on standby.</p>
                            </div>

                            <!-- Metric 3 -->
                            <div class="space-y-2 border-l-2 border-blue-500/40 pl-5">
                                <div class="text-4xl font-extrabold font-heading text-blue-600 dark:text-blue-400">160+</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white">Employers</div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Active commercial enterprise accounts and government installations.</p>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-200/80 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400">
                            We pride ourselves on 99.9% uptime reliability, strict SLA adherence, and precision security engineering.
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- 8. CONTACT US & SITE SURVEY FORM (PathSoft Layout) -->
        <section id="contact" class="py-20 bg-white dark:bg-[#080d1a] border-t border-slate-100 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    <!-- Left: Contact Information -->
                    <div class="lg:col-span-5 space-y-6">
                        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold">
                            <span>Get In Touch</span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold font-heading text-slate-900 dark:text-white tracking-tight">
                            Consult with our Certified Security Engineers
                        </h2>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Schedule a comprehensive on-site survey, request custom enterprise quotations, or ask about our annual maintenance contracts (AMC).
                        </p>

                        <div class="space-y-4 pt-2">
                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Corporate Headquarters</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Plot No.553, Lig-1, 27th Street, Tamil Nadu Housing Board, Avadi, Chennai-600054.</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Direct Support Hotline</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        <a href="tel:+919677257774" class="hover:text-blue-600 transition-colors font-medium">+91 96772 57774</a>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Email Inquiries</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        <a href="mailto:precisionitsystem@gmail.com" class="hover:text-blue-600 transition-colors">precisionitsystem@gmail.com</a>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0 font-bold text-xs font-mono">
                                    GST
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">GSTIN</h4>
                                    <p class="text-xs font-mono font-bold text-slate-700 dark:text-slate-200">33AHLPI3531N1Z8</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Step-by-Step Proposal & Site Audit Request Module -->
                    <div class="lg:col-span-7">
                        <div class="pathsoft-card p-6 sm:p-8 border border-slate-200 dark:border-slate-800 relative transition-all" id="auditModuleCard">
                            @if(session('success'))
                                <!-- Completed / Success State -->
                                <div id="auditSuccessState" class="text-center py-6 space-y-4">
                                    <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold font-heading text-slate-900 dark:text-white">Audit Request Registered!</h3>
                                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 max-w-md mx-auto">{{ session('success') }}</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 text-left max-w-md mx-auto space-y-2">
                                        <div class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 font-bold">
                                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                            <span>Next Steps:</span>
                                        </div>
                                        <p>1. Our certified field engineer will review your facility requirements.</p>
                                        <p>2. We will call you within 2 business hours to schedule your on-site audit.</p>
                                        <p>3. You receive an itemized proposal and CAD wiring blueprint.</p>
                                    </div>
                                    <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                                        <a href="https://wa.me/919677257774?text={{ urlencode('Hello Precision IT Systems Team, I just submitted an on-site audit request. Please confirm my appointment.') }}" target="_blank"
                                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow flex items-center justify-center space-x-2">
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.102-.115.434-.506.549-.68.116-.173.232-.145.39-.087s1.011.477 1.184.564.289.13.332.203c.044.072.044.419-.1.824z"/></svg>
                                            <span>WhatsApp Follow-up</span>
                                        </a>
                                        <button type="button" onclick="resetAuditFlow()"
                                            class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl">
                                            Submit Another Request
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <div id="auditFlowWrapper" class="{{ session('success') ? 'hidden' : '' }}">
                                <!-- INITIAL STATE: ONLY BUTTON DISPLAYED -->
                                <div id="auditInitialButtonView" class="space-y-6 text-left">
                                    <div class="flex items-center justify-between">
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 uppercase tracking-wide">
                                            Free On-Site Audit
                                        </span>
                                        <span class="text-xs text-slate-400">Guaranteed 2-Hour Response</span>
                                    </div>

                                    <div>
                                        <h3 class="text-2xl font-extrabold font-heading text-slate-900 dark:text-white tracking-tight">
                                            Request a Fast Proposal / Site Audit
                                        </h3>
                                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                                            Click the button below to provide your contact info and project scope. Our certified engineers will assess your facility and deliver an itemized estimate.
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 py-2">
                                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 flex items-center space-x-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200">2-Hour Dispatch</div>
                                                <div class="text-[10px] text-slate-400">Fast engineer call</div>
                                            </div>
                                        </div>

                                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 flex items-center space-x-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Free Site Survey</div>
                                                <div class="text-[10px] text-slate-400">Zero service cost</div>
                                            </div>
                                        </div>

                                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 flex items-center space-x-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-cyan-100 dark:bg-cyan-900/40 text-cyan-600 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200">CAD Blueprint</div>
                                                <div class="text-[10px] text-slate-400">Wiring schematics</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Only Button (First Shown) -->
                                    <button type="button" onclick="startAuditFlow()"
                                        class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-xl shadow-blue-500/25 flex items-center justify-center space-x-2.5 transition-all hover:scale-[1.01] group">
                                        <span>Request a Fast Proposal / Site Audit</span>
                                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- STEP-BY-STEP FORM -->
                                <form id="auditStepForm" action="{{ route('public.inquire') }}" method="POST" class="hidden">
                                    @csrf

                                    <!-- Step Tracker Header -->
                                    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center space-x-2">
                                            <span id="stepBadge1" class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">1</span>
                                            <span id="stepTitle1" class="text-xs font-bold text-slate-900 dark:text-white">Contact Info</span>
                                        </div>
                                        <div class="h-1 flex-grow mx-4 bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                                            <div id="stepProgressBar" class="h-full bg-blue-600 transition-all duration-300 w-1/2"></div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span id="stepBadge2" class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold flex items-center justify-center">2</span>
                                            <span id="stepTitle2" class="text-xs font-medium text-slate-400">Project Scope</span>
                                        </div>
                                    </div>

                                    <!-- STEP 1: ADD CONTACT DATA -->
                                    <div id="auditStep1" class="space-y-4">
                                        <div>
                                            <h4 class="text-lg font-bold font-heading text-slate-900 dark:text-white">Step 1: Contact Information</h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Enter your name and phone number for the site inspection.</p>
                                        </div>

                                        <div class="space-y-3.5">
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Full Name *</label>
                                                <input type="text" name="customer_name" id="stepCustomerName" required placeholder="John Doe"
                                                    class="w-full px-4 py-3 text-sm min-h-[44px] rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                                <span id="nameError" class="text-[11px] text-rose-500 hidden mt-1">Please enter your full name.</span>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mobile Phone *</label>
                                                <input type="tel" name="phone" id="stepPhone" required placeholder="+91 98765 43210"
                                                    class="w-full px-4 py-3 text-sm min-h-[44px] rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                                <span id="phoneError" class="text-[11px] text-rose-500 hidden mt-1">Please enter a valid mobile number.</span>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email Address</label>
                                                <input type="email" name="email" id="stepEmail" placeholder="john@company.com"
                                                    class="w-full px-4 py-3 text-sm min-h-[44px] rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between pt-4">
                                            <button type="button" onclick="cancelAuditFlow()" class="text-xs font-medium text-slate-400 hover:text-slate-600 dark:hover:text-white">
                                                ← Cancel
                                            </button>
                                            <button type="button" onclick="goToStep2()"
                                                class="px-6 py-3 min-h-[44px] bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-md shadow-blue-500/20 flex items-center space-x-2 transition-all hover:scale-102">
                                                <span>Continue to Next Step</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- STEP 2: PROJECT SCOPE & SITE DETAILS -->
                                    <div id="auditStep2" class="hidden space-y-4">
                                        <div>
                                            <h4 class="text-lg font-bold font-heading text-slate-900 dark:text-white">Step 2: Project Scope & Site Details</h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Specify your property type, scale, and requirements.</p>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Facility Type</label>
                                                <select name="property_type"
                                                    class="w-full px-4 py-3 text-sm min-h-[44px] rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                                    <option value="Commercial Corporate Office">Commercial Corporate Office</option>
                                                    <option value="Industrial Warehouse / Factory">Industrial Warehouse / Factory</option>
                                                    <option value="Residential Villa / Society">Residential Villa / Society</option>
                                                    <option value="Retail Store / Showroom">Retail Store / Showroom</option>
                                                    <option value="Educational / Healthcare">Educational / Healthcare</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Camera Count / Scale</label>
                                                <select name="camera_scale"
                                                    class="w-full px-4 py-3 text-sm min-h-[44px] rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                                    <option value="4 Cameras Setup">1 - 4 Cameras (Small Office / Villa)</option>
                                                    <option value="8 Cameras Setup">5 - 8 Cameras (Medium Office / Store)</option>
                                                    <option value="16 Cameras Setup">9 - 16 Cameras (Factory / Building)</option>
                                                    <option value="32+ Enterprise Scale">16+ Enterprise Complex / Multi-Site</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Specific Requirements / Notes</label>
                                            <textarea name="notes" rows="3" placeholder="e.g., Need 24/7 color night vision for warehouse perimeter, gate ANPR, and cloud mobile viewing..."
                                                class="w-full px-4 py-3 text-sm min-h-[44px] rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                                        </div>

                                        <div class="flex items-center justify-between pt-4">
                                            <button type="button" onclick="goToStep1()" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition-all">
                                                ← Back to Step 1
                                            </button>
                                            <button type="submit"
                                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-500/25 flex items-center space-x-2 transition-all hover:scale-102">
                                                <span>Submit Request & Schedule Survey 🚀</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- 9. MULTI-COLUMN CORPORATE FOOTER (Exact PathSoft Structure) -->
    <footer class="bg-slate-900 text-slate-300 pt-16 pb-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 mb-12">
                <!-- Col 1: Logo & Bio -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-3.5">
                        <img src="{{ asset('logo.png') }}" alt="Precision IT Systems" class="crm-brand-logo h-16 w-16 object-contain bg-white/95 p-1 rounded-2xl shadow-sm shrink-0">
                        <div>
                            <span class="font-heading font-black text-lg text-white tracking-tight">Precision IT <span class="crm-brand-accent-text text-blue-400">Systems</span></span>
                            <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 font-mono mt-0.5">Security Operations</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        Leading provider of enterprise AI video surveillance, smart security architecture, and mission-critical 24/7 maintenance engineering across modern premises.
                    </p>
                    <div class="flex items-center space-x-3 text-slate-400 pt-2">
                        <a href="#" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Col 2: About -->
                <div class="space-y-3">
                    <h4 class="text-sm font-bold text-white font-heading uppercase tracking-wider">About</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="#about" class="hover:text-blue-400 transition-colors">About Us</a></li>
                        <li><a href="#why-us" class="hover:text-blue-400 transition-colors">Corporate Team</a></li>
                        <li><a href="#contact" class="hover:text-blue-400 transition-colors">Careers & Jobs</a></li>
                        <li><a href="#contact" class="hover:text-blue-400 transition-colors">Client Testimonials</a></li>
                        <li><a href="#why-us" class="hover:text-blue-400 transition-colors">Certifications</a></li>
                    </ul>
                </div>

                <!-- Col 3: Services -->
                <div class="space-y-3">
                    <h4 class="text-sm font-bold text-white font-heading uppercase tracking-wider">Services</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="#services" class="hover:text-blue-400 transition-colors">Corporate Solutions</a></li>
                        <li><a href="#services" class="hover:text-blue-400 transition-colors">Call Center Support</a></li>
                        <li><a href="#services" class="hover:text-blue-400 transition-colors">Cloud Development</a></li>
                        <li><a href="#services" class="hover:text-blue-400 transition-colors">4K AI Camera Setup</a></li>
                        <li><a href="#contact" class="hover:text-blue-400 transition-colors">Preventive AMC Plans</a></li>
                    </ul>
                </div>

                <!-- Col 4: Quick Links & Contact -->
                <div class="space-y-3">
                    <h4 class="text-sm font-bold text-white font-heading uppercase tracking-wider">Contact Us</h4>
                    <ul class="space-y-2.5 text-xs text-slate-400">
                        <li class="flex items-start space-x-2">
                            <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Plot No.553, Lig-1, 27th St, TNHB, Avadi, Chennai-600054</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <a href="mailto:precisionitsystem@gmail.com" class="hover:text-white transition-colors">precisionitsystem@gmail.com</a>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <a href="tel:+919677257774" class="hover:text-white transition-colors">+91 96772 57774</a>
                        </li>
                        <li class="flex items-center space-x-2">
                            <span class="text-[10px] font-mono font-bold text-blue-400">GST:</span>
                            <span class="font-mono text-[11px] text-slate-300 font-bold">33AHLPI3531N1Z8</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="pt-8 border-t border-slate-800 text-center text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} Precision IT Systems - All Rights Reserved. Smart AI CCTV Cameras & Enterprise Security Solutions.</p>
            </div>
        </div>
    </footer>

    <!-- 10. PRODUCT INQUIRY & QUOTATION MODAL -->
    <div id="inquireModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="pathsoft-card max-w-lg w-full p-6 sm:p-8 relative shadow-2xl">
            <button type="button" onclick="closeInquireModal()"
                class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="mb-5">
                <div class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-md bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                    <span>Direct Product Quotation</span>
                </div>
                <h3 class="text-xl font-bold font-heading text-slate-900 dark:text-white" id="modalProductTitle">
                    Request Quotation
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Receive an itemized quote, wiring blueprint, and enterprise project discounts within 2 business hours.
                </p>
            </div>

            <form action="{{ route('public.inquire') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="product_name" id="modalProductNameInput">
                <input type="hidden" name="model_no" id="modalProductSkuInput">

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Your Full Name *</label>
                    <input type="text" name="customer_name" required placeholder="John Doe"
                        class="w-full min-h-[44px] px-3.5 py-2.5 text-sm rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mobile Phone *</label>
                        <input type="tel" name="phone" required placeholder="+91 98765 43210"
                            class="w-full min-h-[44px] px-3.5 py-2.5 text-sm rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email Address</label>
                        <input type="email" name="email" placeholder="john@enterprise.com"
                            class="w-full min-h-[44px] px-3.5 py-2.5 text-sm rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Estimated Units / Notes</label>
                    <input type="text" name="notes" placeholder="e.g., Need 8 units with 8-ch NVR and installation"
                        class="w-full min-h-[44px] px-3.5 py-2.5 text-sm rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit"
                    class="w-full min-h-[44px] py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/25 transition-all">
                    Send Instant Quote Request
                </button>
            </form>
        </div>
    </div>

    <!-- 11. QUICK SEARCH MODAL (Triggered by Search Circle Button) -->
    <div id="searchModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-md flex items-start justify-center p-4 sm:p-8 pt-20">
        <div class="pathsoft-card max-w-2xl w-full p-6 sm:p-8 relative shadow-2xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-6">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold font-heading text-slate-900 dark:text-white">Search Solutions & Services</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Search enterprise security solutions, AMC packages, and CCTV architectures</p>
                    </div>
                </div>
                <button type="button" onclick="closeSearchModal()"
                    class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Search Form -->
            <form action="#services" method="GET" onsubmit="closeSearchModal()" class="space-y-4">
                <div class="relative">
                    <input type="text" name="q" id="searchModalInput" autofocus
                        placeholder="Type keywords (e.g. CCTV, AMC, Cloud VMS, ANPR, Access Control, Audit)..."
                        class="w-full pl-11 pr-24 py-3.5 text-sm rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button type="submit" class="absolute right-2 top-2 min-h-[38px] px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow transition-all">
                        Search
                    </button>
                </div>

                <!-- Quick Filters -->
                <div class="pt-2">
                    <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Explore Solutions</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="#services" onclick="closeSearchModal()" class="px-3 py-1 rounded-lg text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-blue-600 hover:text-white transition-colors">Corporate Solutions</a>
                        <a href="#services" onclick="closeSearchModal()" class="px-3 py-1 rounded-lg text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-blue-600 hover:text-white transition-colors">24/7 Call Center AMC</a>
                        <a href="#services" onclick="closeSearchModal()" class="px-3 py-1 rounded-lg text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-blue-600 hover:text-white transition-colors">Cloud VMS & AI</a>
                        <a href="#why-us" onclick="closeSearchModal()" class="px-3 py-1 rounded-lg text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-blue-600 hover:text-white transition-colors">Why Choose Us</a>
                        <a href="#contact" onclick="closeSearchModal(); startAuditFlow();" class="px-3 py-1 rounded-lg text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-semibold hover:bg-blue-600 hover:text-white transition-colors">Request Site Audit</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Interactive Scripts for Theme Toggle, Modals, Dropdown, and Multi-Step Audit -->
    <script>
        function syncThemeUI() {
            const isDark = document.documentElement.classList.contains('dark');
            const lightIcon = document.getElementById('theme-toggle-light-icon');
            const darkIcon = document.getElementById('theme-toggle-dark-icon');
            if (isDark) {
                if (lightIcon) lightIcon.classList.remove('hidden');
                if (darkIcon) darkIcon.classList.add('hidden');
            } else {
                if (lightIcon) lightIcon.classList.add('hidden');
                if (darkIcon) darkIcon.classList.remove('hidden');
            }
        }

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            syncThemeUI();
        }

        document.addEventListener('DOMContentLoaded', syncThemeUI);

        // Hamburger Menu Dropdown List Handler
        function toggleNavMenuDropdown() {
            const dropdown = document.getElementById('navMenuDropdownList');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        function closeNavMenuDropdown() {
            const dropdown = document.getElementById('navMenuDropdownList');
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
        }

        // Multi-Step Proposal & Audit Form Flow
        function startAuditFlow() {
            const initialView = document.getElementById('auditInitialButtonView');
            const form = document.getElementById('auditStepForm');
            if (initialView && form) {
                initialView.classList.add('hidden');
                form.classList.remove('hidden');
                goToStep1();
                
                // Scroll smoothly to form
                const container = document.getElementById('auditModuleCard');
                if (container) {
                    container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }

        function cancelAuditFlow() {
            const initialView = document.getElementById('auditInitialButtonView');
            const form = document.getElementById('auditStepForm');
            if (initialView && form) {
                form.classList.add('hidden');
                initialView.classList.remove('hidden');
            }
        }

        function resetAuditFlow() {
            const successEl = document.getElementById('auditSuccessState');
            if (successEl) successEl.classList.add('hidden');
            const wrapper = document.getElementById('auditFlowWrapper');
            if (wrapper) wrapper.classList.remove('hidden');
            cancelAuditFlow();
        }

        function goToStep1() {
            const step1 = document.getElementById('auditStep1');
            const step2 = document.getElementById('auditStep2');
            if (step1 && step2) {
                step1.classList.remove('hidden');
                step2.classList.add('hidden');
            }

            const badge1 = document.getElementById('stepBadge1');
            const title1 = document.getElementById('stepTitle1');
            const badge2 = document.getElementById('stepBadge2');
            const title2 = document.getElementById('stepTitle2');
            const progress = document.getElementById('stepProgressBar');

            if (badge1) {
                badge1.className = 'w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center';
                badge1.innerText = '1';
            }
            if (title1) title1.className = 'text-xs font-bold text-slate-900 dark:text-white';
            if (badge2) {
                badge2.className = 'w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold flex items-center justify-center';
                badge2.innerText = '2';
            }
            if (title2) title2.className = 'text-xs font-medium text-slate-400';
            if (progress) progress.style.width = '50%';
        }

        function goToStep2() {
            const nameInput = document.getElementById('stepCustomerName');
            const phoneInput = document.getElementById('stepPhone');
            const nameError = document.getElementById('nameError');
            const phoneError = document.getElementById('phoneError');

            let valid = true;
            if (!nameInput || !nameInput.value.trim()) {
                if (nameError) nameError.classList.remove('hidden');
                if (nameInput) nameInput.focus();
                valid = false;
            } else {
                if (nameError) nameError.classList.add('hidden');
            }

            if (!phoneInput || !phoneInput.value.trim() || phoneInput.value.trim().length < 7) {
                if (phoneError) phoneError.classList.remove('hidden');
                if (valid && phoneInput) phoneInput.focus();
                valid = false;
            } else {
                if (phoneError) phoneError.classList.add('hidden');
            }

            if (!valid) return;

            const step1 = document.getElementById('auditStep1');
            const step2 = document.getElementById('auditStep2');
            if (step1 && step2) {
                step1.classList.add('hidden');
                step2.classList.remove('hidden');
            }

            const badge1 = document.getElementById('stepBadge1');
            const title1 = document.getElementById('stepTitle1');
            const badge2 = document.getElementById('stepBadge2');
            const title2 = document.getElementById('stepTitle2');
            const progress = document.getElementById('stepProgressBar');

            if (badge1) {
                badge1.className = 'w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-bold flex items-center justify-center';
                badge1.innerHTML = '✓';
            }
            if (title1) title1.className = 'text-xs font-medium text-slate-400';
            if (badge2) {
                badge2.className = 'w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center';
                badge2.innerText = '2';
            }
            if (title2) title2.className = 'text-xs font-bold text-slate-900 dark:text-white';
            if (progress) progress.style.width = '100%';
        }

        // Inquire Modal
        function openInquireModal(productName, productSku) {
            const modal = document.getElementById('inquireModal');
            const titleEl = document.getElementById('modalProductTitle');
            const nameInput = document.getElementById('modalProductNameInput');
            const skuInput = document.getElementById('modalProductSkuInput');

            if (titleEl) titleEl.innerText = 'Request Quotation: ' + productName;
            if (nameInput) nameInput.value = productName;
            if (skuInput) skuInput.value = productSku;
            if (modal) modal.classList.remove('hidden');
        }

        function closeInquireModal() {
            const modal = document.getElementById('inquireModal');
            if (modal) modal.classList.add('hidden');
        }

        // Search Modal
        function openSearchModal() {
            const modal = document.getElementById('searchModal');
            if (modal) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    const input = document.getElementById('searchModalInput');
                    if (input) input.focus();
                }, 100);
            }
        }

        function closeSearchModal() {
            const modal = document.getElementById('searchModal');
            if (modal) modal.classList.add('hidden');
        }
    </script>

    <!-- 9. FULLSCREEN PICTURE LIGHTBOX MODAL (Slide-by-Slide Viewer) -->
    <div id="pictureLightboxModal" class="fixed inset-0 z-50 hidden bg-slate-950/95 backdrop-blur-xl flex flex-col justify-between p-4 sm:p-6 transition-all duration-300">
        <!-- Lightbox Header -->
        <div class="flex items-center justify-between z-20 pb-3 border-b border-white/10 max-w-7xl mx-auto w-full">
            <div class="flex items-center space-x-3">
                <span class="px-3 py-1 rounded-full bg-blue-600/90 text-white text-xs font-bold font-mono tracking-wider shadow-lg" id="lightboxCategory">
                    CCTV Engineering
                </span>
                <span class="text-white/60 text-xs font-mono font-semibold" id="lightboxCounter">1 / 8</span>
            </div>
            
            <div class="flex items-center space-x-2">
                <button type="button" onclick="toggleLightboxAutoplay()" id="lightboxAutoplayBtn"
                    class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-semibold flex items-center space-x-1.5 transition-all">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    <span id="lightboxAutoplayLabel">Auto Play</span>
                </button>
                <button type="button" onclick="closePictureLightbox()"
                    class="w-9 h-9 rounded-full bg-white/10 hover:bg-rose-600 text-white flex items-center justify-center transition-all hover:scale-110"
                    title="Close Viewer (ESC)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Lightbox Body / Image View -->
        <div class="relative flex-1 flex items-center justify-center my-2 max-w-7xl mx-auto w-full overflow-hidden">
            <!-- Prev Button -->
            <button type="button" onclick="prevLightboxSlide()"
                class="absolute left-2 sm:left-4 z-30 w-12 h-12 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white backdrop-blur-md border border-white/20 flex items-center justify-center transition-all shadow-2xl hover:scale-110">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <!-- Main Display Image -->
            <div class="relative max-h-full max-w-full flex flex-col items-center justify-center p-2">
                <img id="lightboxImage" src="" alt="" 
                    class="max-h-[62vh] sm:max-h-[70vh] w-auto max-w-full object-contain rounded-xl shadow-2xl transition-all duration-300 transform scale-100">
                <div class="mt-3 text-center max-w-2xl px-4">
                    <h3 id="lightboxTitle" class="text-base sm:text-lg font-bold font-heading text-white drop-shadow"></h3>
                    <p id="lightboxDescription" class="text-xs sm:text-sm text-slate-300 mt-1 line-clamp-2 drop-shadow"></p>
                </div>
            </div>

            <!-- Next Button -->
            <button type="button" onclick="nextLightboxSlide()"
                class="absolute right-2 sm:right-4 z-30 w-12 h-12 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white backdrop-blur-md border border-white/20 flex items-center justify-center transition-all shadow-2xl hover:scale-110">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <!-- Lightbox Footer Thumbnails Strip -->
        <div class="z-20 pt-3 border-t border-white/10 max-w-5xl mx-auto w-full overflow-x-auto pb-1">
            <div class="flex items-center justify-center space-x-2 sm:space-x-3 min-w-max px-2" id="lightboxThumbnails">
                <!-- Dynamically rendered -->
            </div>
        </div>
    </div>

    <!-- MAIN JAVASCRIPT CONTROLLERS -->
    <script>
        // -------------------------------------------------------------
        // SLIDES DATA AND CONTROLLER
        // -------------------------------------------------------------
        const heroSlidesData = [
            {
                src: "{{ asset('images/slides/slide1_technician_cctv_orange_helmet.png') }}",
                category: "CCTV Engineering",
                title: "Expert Outdoor CCTV Installation & Junction Wiring",
                desc: "Certified field engineers ensuring weather-sealed junction boxes, shielded cabling, and rugged commercial mounting.",
                badgeTitle: "CCTV Installation",
                badgeSub: "Certified Field Engineers"
            },
            {
                src: "{{ asset('images/slides/slide2_technician_bullet_camera.jpg') }}",
                category: "Perimeter Security",
                title: "Precision IP Bullet Camera Optical Calibration",
                desc: "High-definition focal tuning, weatherproof IP67 outdoor housing, and crystal-clear long-range night vision.",
                badgeTitle: "Bullet Surveillance",
                badgeSub: "IP67 Weatherproof Optics"
            },
            {
                src: "{{ asset('images/slides/slide3_technician_dome_camera.png') }}",
                category: "Commercial & Indoor",
                title: "Vandal-Resistant 360° AI Dome Camera Setup",
                desc: "Discreet ceiling and wall mounted dome sensors with AI human tracking and wide panoramic vision.",
                badgeTitle: "360° AI Dome Cameras",
                badgeSub: "Motorized PTZ & VMS"
            },
            {
                src: "{{ asset('images/slides/slide4_cctv_specialist_tools.png') }}",
                category: "Hardware Diagnostics",
                title: "Commercial Grade Multisensor Hardware & Tools",
                desc: "Turnkey hardware deployment with rapid testing meters, precision crimping, and zero-downtime maintenance.",
                badgeTitle: "Turnkey Hardware",
                badgeSub: "Multi-Sensor Tooling"
            },
            {
                src: "{{ asset('images/slides/slide5_ai_face_recognition_attendance.png') }}",
                category: "AI Biometrics",
                title: "AI Face Recognition Access Control & Time Attendance",
                desc: "5,000 face capacity, live face & mask detection, QR code scanning, and enterprise cloud SDK integration.",
                badgeTitle: "AI Face Attendance",
                badgeSub: "Live Face & Mask Detection"
            },
            {
                src: "{{ asset('images/slides/slide6_multi_identification_access_methods.png') }}",
                category: "Access Control",
                title: "10 Multi-Modal Identification & Security Combinations",
                desc: "Seamless authentication using Face Recognition, Palmprint, RFID Card, Biometric Fingerprint, and Secure Passcodes.",
                badgeTitle: "10-in-1 Access Modes",
                badgeSub: "Palm, Face, Card & Fingerprint"
            },
            {
                src: "{{ asset('images/slides/slide7_biometric_fingerprint_terminal.png') }}",
                category: "Door Access",
                title: "High-Security Standalone Biometric Terminals",
                desc: "Fast 0.2s optical scanner matching, illuminated keypad, TCP/IP LAN syncing, and emergency electronic door strike relays.",
                badgeTitle: "Biometric Terminals",
                badgeSub: "0.2s Fast Matching"
            },
            {
                src: "{{ asset('images/slides/slide8_attendance_management_system.png') }}",
                category: "Cloud HR Suite",
                title: "Unified HR Attendance & Real-Time Check-in Suite",
                desc: "Photo-verified employee check-ins, automated overtime calculations, shift scheduling, and instant payroll export.",
                badgeTitle: "Attendance Cloud",
                badgeSub: "Automated Timesheets & Reports"
            }
        ];

        let currentHeroIndex = 0;
        let heroSliderInterval = null;
        const SLIDE_DURATION = 4500; // 4.5 seconds
        let isHeroPaused = false;

        function updateHeroSliderUI() {
            const slides = document.querySelectorAll('.hero-slide');
            const dots = document.querySelectorAll('.hero-dot');
            const counter = document.getElementById('heroSlideCounter');
            const badgeTitle = document.getElementById('heroFloatingBadgeTitle');
            const badgeSub = document.getElementById('heroFloatingBadgeSub');

            slides.forEach((slide, idx) => {
                if (idx === currentHeroIndex) {
                    slide.classList.remove('opacity-0', 'scale-95', 'pointer-events-none', 'z-0');
                    slide.classList.add('opacity-100', 'scale-100', 'z-10');
                } else {
                    slide.classList.remove('opacity-100', 'scale-100', 'z-10');
                    slide.classList.add('opacity-0', 'scale-95', 'pointer-events-none', 'z-0');
                }
            });

            dots.forEach((dot, idx) => {
                if (idx === currentHeroIndex) {
                    dot.className = 'hero-dot w-6 h-2 rounded-full bg-blue-500 transition-all shadow-md shadow-blue-500/50';
                } else {
                    dot.className = 'hero-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/80 transition-all';
                }
            });

            if (counter) {
                counter.innerText = `0${currentHeroIndex + 1} / 0${heroSlidesData.length}`;
            }

            if (badgeTitle && heroSlidesData[currentHeroIndex]) {
                badgeTitle.innerText = heroSlidesData[currentHeroIndex].badgeTitle;
            }
            if (badgeSub && heroSlidesData[currentHeroIndex]) {
                badgeSub.innerText = heroSlidesData[currentHeroIndex].badgeSub;
            }

            resetHeroProgress();
        }

        function setHeroSlide(index) {
            currentHeroIndex = (index + heroSlidesData.length) % heroSlidesData.length;
            updateHeroSliderUI();
        }

        function nextHeroSlide(e) {
            if (e) e.stopPropagation();
            setHeroSlide(currentHeroIndex + 1);
        }

        function prevHeroSlide(e) {
            if (e) e.stopPropagation();
            setHeroSlide(currentHeroIndex - 1);
        }

        function resetHeroProgress() {
            const bar = document.getElementById('heroSliderProgress');
            if (bar) {
                bar.style.transition = 'none';
                bar.style.width = '0%';
                setTimeout(() => {
                    if (!isHeroPaused && bar) {
                        bar.style.transition = `width ${SLIDE_DURATION}ms linear`;
                        bar.style.width = '100%';
                    }
                }, 50);
            }
        }

        function startHeroSlider() {
            clearInterval(heroSliderInterval);
            resetHeroProgress();
            heroSliderInterval = setInterval(() => {
                if (!isHeroPaused) {
                    setHeroSlide(currentHeroIndex + 1);
                }
            }, SLIDE_DURATION);
        }

        function pauseHeroSlider() {
            isHeroPaused = true;
            const bar = document.getElementById('heroSliderProgress');
            if (bar) {
                const computedWidth = window.getComputedStyle(bar).width;
                bar.style.transition = 'none';
                bar.style.width = computedWidth;
            }
        }

        function resumeHeroSlider() {
            isHeroPaused = false;
            resetHeroProgress();
        }

        // Initialize slider on DOMContentLoaded
        document.addEventListener('DOMContentLoaded', () => {
            updateHeroSliderUI();
            startHeroSlider();
            renderLightboxThumbnails();
        });

        // -------------------------------------------------------------
        // LIGHTBOX VIEWER CONTROLLER
        // -------------------------------------------------------------
        let currentLightboxIndex = 0;
        let lightboxAutoplayInterval = null;

        function openPictureLightbox(index) {
            pauseHeroSlider();
            currentLightboxIndex = index;
            const modal = document.getElementById('pictureLightboxModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                updateLightboxUI();
            }
        }

        function closePictureLightbox() {
            const modal = document.getElementById('pictureLightboxModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
                if (lightboxAutoplayInterval) {
                    clearInterval(lightboxAutoplayInterval);
                    lightboxAutoplayInterval = null;
                    const btn = document.getElementById('lightboxAutoplayLabel');
                    if (btn) btn.innerText = 'Auto Play';
                }
                resumeHeroSlider();
            }
        }

        function updateLightboxUI() {
            const data = heroSlidesData[currentLightboxIndex];
            if (!data) return;

            const img = document.getElementById('lightboxImage');
            const cat = document.getElementById('lightboxCategory');
            const title = document.getElementById('lightboxTitle');
            const desc = document.getElementById('lightboxDescription');
            const counter = document.getElementById('lightboxCounter');

            if (img) {
                img.style.opacity = '0';
                img.style.transform = 'scale(0.96)';
                setTimeout(() => {
                    img.src = data.src;
                    img.alt = data.title;
                    img.style.opacity = '1';
                    img.style.transform = 'scale(1)';
                }, 150);
            }

            if (cat) cat.innerText = data.category;
            if (title) title.innerText = data.title;
            if (desc) desc.innerText = data.desc;
            if (counter) counter.innerText = `${currentLightboxIndex + 1} / ${heroSlidesData.length}`;

            // Highlight active thumbnail
            const thumbs = document.querySelectorAll('.lightbox-thumb');
            thumbs.forEach((thumb, idx) => {
                if (idx === currentLightboxIndex) {
                    thumb.className = 'lightbox-thumb w-14 h-10 sm:w-16 sm:h-12 rounded-lg border-2 border-blue-500 overflow-hidden cursor-pointer shadow-lg transform scale-105 transition-all opacity-100';
                } else {
                    thumb.className = 'lightbox-thumb w-14 h-10 sm:w-16 sm:h-12 rounded-lg border border-white/20 overflow-hidden cursor-pointer opacity-50 hover:opacity-90 transition-all';
                }
            });
        }

        function nextLightboxSlide() {
            currentLightboxIndex = (currentLightboxIndex + 1) % heroSlidesData.length;
            updateLightboxUI();
        }

        function prevLightboxSlide() {
            currentLightboxIndex = (currentLightboxIndex - 1 + heroSlidesData.length) % heroSlidesData.length;
            updateLightboxUI();
        }

        function renderLightboxThumbnails() {
            const container = document.getElementById('lightboxThumbnails');
            if (!container) return;

            container.innerHTML = heroSlidesData.map((slide, idx) => `
                <div class="lightbox-thumb w-14 h-10 sm:w-16 sm:h-12 rounded-lg border border-white/20 overflow-hidden cursor-pointer opacity-50 hover:opacity-90 transition-all" onclick="currentLightboxIndex = ${idx}; updateLightboxUI();">
                    <img src="${slide.src}" alt="${slide.title}" class="w-full h-full object-cover">
                </div>
            `).join('');
        }

        function toggleLightboxAutoplay() {
            const label = document.getElementById('lightboxAutoplayLabel');
            if (lightboxAutoplayInterval) {
                clearInterval(lightboxAutoplayInterval);
                lightboxAutoplayInterval = null;
                if (label) label.innerText = 'Auto Play';
            } else {
                lightboxAutoplayInterval = setInterval(() => {
                    nextLightboxSlide();
                }, 3500);
                if (label) label.innerText = 'Pause';
            }
        }

        // -------------------------------------------------------------
        // GLOBAL LISTENERS & MODALS
        // -------------------------------------------------------------
        // Override the window.onclick to also handle lightbox
        window.onclick = function(event) {
            const inquireModal = document.getElementById('inquireModal');
            const searchModal = document.getElementById('searchModal');
            const lightboxModal = document.getElementById('pictureLightboxModal');
            const menuDropdownContainer = document.getElementById('navMenuDropdownContainer');

            if (event.target === inquireModal) closeInquireModal();
            if (event.target === searchModal) closeSearchModal();
            if (event.target === lightboxModal) closePictureLightbox();

            if (menuDropdownContainer && !menuDropdownContainer.contains(event.target)) {
                closeNavMenuDropdown();
            }
        };

        // Keyboard navigation (ESC to close, Left/Right arrows to slide)
        document.addEventListener('keydown', function(event) {
            const lightboxModal = document.getElementById('pictureLightboxModal');
            const isLightboxOpen = lightboxModal && !lightboxModal.classList.contains('hidden');

            if (event.key === 'Escape') {
                if (isLightboxOpen) {
                    closePictureLightbox();
                } else {
                    closeInquireModal();
                    closeSearchModal();
                    closeNavMenuDropdown();
                }
            } else if (event.key === 'ArrowRight') {
                if (isLightboxOpen) {
                    nextLightboxSlide();
                } else {
                    nextHeroSlide();
                }
            } else if (event.key === 'ArrowLeft') {
                if (isLightboxOpen) {
                    prevLightboxSlide();
                } else {
                    prevHeroSlide();
                }
            }
        });
    </script>
</body>
</html>

