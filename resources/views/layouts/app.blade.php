@php
    $cookieMode = request()->cookie('crm_mode');
    $effectiveMode = $cookieMode ?: (auth()->check() ? auth()->user()->theme_mode : 'auto');
    $isDarkServer = in_array($effectiveMode, ['night', 'dark']);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f5f5f5] {{ $isDarkServer ? 'dark bg-[#060913]' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Precision IT Systems CRM') }} | Smart Operations</title>

        <!-- Dynamic Logo Engine -->
        <x-crm-theme-init :userId="auth()->id()" :userAccent="auth()->user()->theme_accent" :userMode="auth()->user()->theme_mode" :userStyle="auth()->user()->theme_style" />

        <!-- Tailwind CSS & Alpine.js CDN Guard -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Arial', 'sans-serif'],
                            heading: ['Arial', 'sans-serif'],
                            mono: ['Arial', 'sans-serif'],
                        },
                        colors: {
                            brand: {
                                blue: '#2563eb',
                                blueHover: '#1d4ed8',
                                lightBg: '#f5f5f5',
                            }
                        }
                    }
                }
            }
        </script>

        <!-- Scripts & Styles via Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            html {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }
            html.dark {
                color-scheme: dark;
            }

            .custom-sidebar-scroll {
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: #94a3b8 transparent;
            }
            .custom-sidebar-scroll::-webkit-scrollbar {
                width: 5px;
            }
            .custom-sidebar-scroll::-webkit-scrollbar-track {
                background: transparent;
            }
            .custom-sidebar-scroll::-webkit-scrollbar-thumb {
                background-color: #cbd5e1;
                border-radius: 9999px;
            }
            .dark .custom-sidebar-scroll::-webkit-scrollbar-thumb {
                background-color: #334155;
            }
            .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
                background-color: #94a3b8;
            }

            :root {
                --crm-accent: #2563eb;
                --crm-accent-hover: #1d4ed8;
            }

            /* Modern Stat Card Surface */
            .stat-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 1rem 1.25rem;
                transition: all 0.2s ease-in-out;
            }
            html.dark .stat-card {
                background: #0f172a;
                border-color: #1e293b;
            }

            /* Sidebar Module Navigation Click & Active State */
            aside nav a.crm-active-link,
            .crm-active-link {
                background-color: #ebebed;
                border-radius: 8px;
                color: #0f172a;
                font-weight: 600;
            }
            html.dark aside nav a.crm-active-link,
            html.dark .crm-active-link {
                background-color: #1e293b;
                color: #f8fafc;
            }
            aside nav a:hover {
                background-color: #f1f2f4;
            }
            html.dark aside nav a:hover {
                background-color: #1e293b80;
            }
            aside nav a:active {
                background-color: #ebebed;
            }

            /* User Avatars & Brand Tiles: Perfect round circle, crisp white letter */
            .crm-user-avatar {
                border-radius: 9999px !important;
                color: #ffffff !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-weight: 700 !important;
                user-select: none !important;
            }

            /* Active button feedback */
            button:active:not(:disabled),
            .btn-primary:active,
            .btn-blue:active {
                transform: scale(0.98);
            }

            /* Clean primary buttons */
            .btn-primary,
            .crm-btn-primary {
                background-color: var(--crm-accent, #2563eb);
                border: 1px solid var(--crm-accent, #2563eb);
                color: #ffffff !important;
                border-radius: 8px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                transition: background-color 0.15s ease, border-color 0.15s ease;
            }
            .btn-primary:hover,
            .crm-btn-primary:hover {
                background-color: var(--crm-accent-hover, #1d4ed8);
                border-color: var(--crm-accent-hover, #1d4ed8);
                color: #ffffff !important;
            }

            .btn-secondary,
            .crm-btn-secondary {
                background-color: #ffffff;
                border: 1px solid #cbd5e1;
                color: #334155;
                border-radius: 8px;
                font-weight: 500;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                transition: background-color 0.15s ease;
            }
            html.dark .btn-secondary,
            html.dark .crm-btn-secondary {
                background-color: #1e293b;
                border-color: #334155;
                color: #f1f5f9;
            }
            .btn-secondary:hover,
            .crm-btn-secondary:hover {
                background-color: #f8fafc;
            }
            html.dark .btn-secondary:hover,
            html.dark .crm-btn-secondary:hover {
                background-color: #334155;
            }

            /* TomSelect Clean Fit */
            .ts-control {
                min-height: 38px;
                border-radius: 8px;
                background-color: #ffffff;
                border: 1px solid #cbd5e1;
                box-sizing: border-box;
                font-size: 14px;
                padding: 0 0.75rem;
                display: flex;
                align-items: center;
            }
            html.dark .ts-control {
                background-color: #0f172a;
                border-color: #334155;
                color: #f8fafc;
            }
        </style>

        <script>
            // Precision IT Systems Global CRM State & Shortcuts Engine
            window.crmSidebar = {
                get collapsed() {
                    return localStorage.getItem('crm_sidebar_collapsed') === 'true';
                },
                set collapsed(val) {
                    localStorage.setItem('crm_sidebar_collapsed', val ? 'true' : 'false');
                },
                toggle() {
                    const next = !this.collapsed;
                    this.collapsed = next;
                    try {
                        if (window.Alpine && window.Alpine.store && window.Alpine.store('sidebar')) {
                            window.Alpine.store('sidebar').collapsed = next;
                        }
                    } catch(e) {}
                    window.dispatchEvent(new CustomEvent('sidebar-collapsed-changed', { detail: { collapsed: next } }));
                    return next;
                }
            };
            window.toggleSidebarCollapsed = function() {
                return window.crmSidebar.toggle();
            };

            window.crmShortcuts = {
                toggle() {
                    window.dispatchEvent(new CustomEvent('shortcuts-toggle-action'));
                },
                open() {
                    window.dispatchEvent(new CustomEvent('shortcuts-open'));
                },
                close() {
                    window.dispatchEvent(new CustomEvent('shortcuts-close'));
                }
            };
            window.crmOpenShortcuts = function() {
                window.crmShortcuts.open();
            };
            window.crmToggleShortcuts = function() {
                return window.crmShortcuts.toggle();
            };

            if (typeof window.crmToggleTheme !== 'function') {
                window.crmToggleTheme = function() {
                    if (typeof window.applyCrmMode === 'function') {
                        const isCurrentlyDark = document.documentElement.classList.contains('dark');
                        return window.applyCrmMode(isCurrentlyDark ? 'day' : 'night', true);
                    }
                    const isDark = document.documentElement.classList.toggle('dark');
                    return isDark;
                };
            }

            // Bulletproof keyboard shortcuts listener (Active on ALL pages)
            window.addEventListener('keydown', function(e) {
                const activeTag = document.activeElement ? document.activeElement.tagName.toUpperCase() : '';
                const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeTag) || document.activeElement?.isContentEditable;

                // 1. Ctrl+B / Cmd+B: Toggle Sidebar (Always active across all pages)
                if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.crmSidebar.toggle();
                    return;
                }

                // 2. Ctrl+K / Cmd+K: Focus Global Search (Always active)
                if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (window.innerWidth < 768) {
                        window.dispatchEvent(new CustomEvent('open-mobile-search'));
                    } else {
                        const searchInput = document.querySelector('[x-ref="searchInput"], #job-search, input[name="search"], #searchModalInput');
                        if (searchInput) {
                            searchInput.focus();
                            searchInput.select?.();
                        }
                    }
                    return;
                }

                // 3. Shortcuts Modal: '?' (when not typing in form field) OR Ctrl+/
                const isQuestionKey = (e.key === '?' || (e.shiftKey && (e.key === '/' || e.code === 'Slash')));
                const isCtrlSlash = (e.ctrlKey || e.metaKey) && (e.key === '/' || e.code === 'Slash');
                if (isCtrlSlash || (isQuestionKey && !isInput)) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.crmShortcuts.toggle();
                    return;
                }

                // 4. Escape: Close open modals, search popups, mobile drawer
                if (e.key === 'Escape') {
                    window.dispatchEvent(new CustomEvent('shortcuts-close'));
                    window.dispatchEvent(new CustomEvent('close-all-modals'));
                    const productDropdown = document.getElementById('quotation-product-dropdown');
                    if (productDropdown) productDropdown.classList.add('hidden');
                    return;
                }

                // 5. Alt+T: Toggle Theme (Dark / Light)
                if (e.altKey && (e.key === 't' || e.key === 'T')) {
                    e.preventDefault();
                    window.crmToggleTheme();
                    return;
                }

                // 6. Alt Navigation Shortcuts (when not typing in form field):
                if (!isInput && e.altKey) {
                    if (e.key === 'd' || e.key === 'D') {
                        e.preventDefault();
                        window.location.href = '{{ auth()->user()->role === 'technician' ? route('technician.dashboard') : (auth()->user()->isCustomer() ? route('portal.dashboard') : route('dashboard')) }}';
                    } else if (e.key === 'a' || e.key === 'A') {
                        e.preventDefault();
                        window.location.href = '{{ route('attendance.index') }}';
                    } else if (e.key === 'l' || e.key === 'L') {
                        @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                            e.preventDefault();
                            window.location.href = '{{ route('leads.index') }}';
                        @endif
                    }
                }
            });
        </script>
    </head>
    <body class="h-full font-sans antialiased text-slate-800 dark:text-slate-100 bg-[#f8fafc] dark:bg-[#060913] selection:bg-blue-600 selection:text-white" 
          x-data="{ 
              sidebarOpen: false, 
              sidebarCollapsed: window.crmSidebar ? window.crmSidebar.collapsed : false,
              toggleSidebarCollapsed() {
                  this.sidebarCollapsed = window.crmSidebar.toggle();
              },
              shortcutsModalOpen: false,
              mobileNavOpen: false,
              isDark: document.documentElement.classList.contains('dark'),
              toggleTheme() {
                  this.isDark = window.crmToggleTheme();
              }
          }"
          @theme-mode-changed.window="isDark = $event.detail.isDark"
          @theme-changed.window="if ($event.detail && typeof $event.detail.isDark === 'boolean') isDark = $event.detail.isDark"
          @sidebar-collapsed-changed.window="sidebarCollapsed = $event.detail.collapsed"
          @shortcuts-toggle-action.window="shortcutsModalOpen = !shortcutsModalOpen"
          @shortcuts-toggle.window="shortcutsModalOpen = (typeof $event.detail?.open === 'boolean') ? $event.detail.open : !shortcutsModalOpen"
          @shortcuts-open.window="shortcutsModalOpen = true"
          @shortcuts-close.window="shortcutsModalOpen = false"
          @open-mobile-search.window="mobileSearchOpen = true; $nextTick(() => $refs.mobileSearchInput?.focus())">
        
            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- SECUREVISION UNIFIED ENTERPRISE SAAS CRM SUITE (ALL ROLES)        --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div class="crm-app-container min-h-screen flex bg-[#f8fafc] dark:bg-[#060913] text-slate-800 dark:text-slate-100 transition-colors duration-200">
                
                {{-- Mobile Drawer Overlay --}}
                <div x-show="sidebarOpen" 
                     x-cloak
                     x-transition:enter="transition-opacity ease-linear duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-linear duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="sidebarOpen = false" 
                     class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden" 
                     style="display: none;"></div>

                {{-- Sidebar Container: Fixed on mobile, sticky on desktop with complete offscreen translation & collapse support --}}
                <div :class="{
                        'translate-x-0 shadow-2xl': sidebarOpen,
                        '-translate-x-full lg:translate-x-0 shadow-none': !sidebarOpen,
                        'lg:w-20': sidebarCollapsed,
                        'lg:w-64': !sidebarCollapsed
                     }"
                     class="fixed inset-y-0 left-0 z-50 lg:sticky lg:top-0 lg:h-screen lg:z-30 w-64 shrink-0 transition-[width,transform] duration-300 ease-in-out flex flex-col bg-white dark:bg-[#0f172a] border-r border-slate-200/80 dark:border-slate-800 overflow-hidden">
                    @include('layouts.navigation')
                </div>

                {{-- Main Application Wrapper --}}
                <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-[#f8fafc] dark:bg-[#060913] transition-colors duration-200">
                    
                    {{-- Modern SaaS Top Navigation Bar matching Image 3 --}}
                    <script>
                        function globalHeader() {
                            return {
                                sidebarCollapsed: window.crmSidebar ? window.crmSidebar.collapsed : false,
                                query: '',
                                results: [],
                                open: false,
                                loading: false,
                                selectedIndex: -1,
                                mobileSearchOpen: false,
                                isDarkMode: document.documentElement.classList.contains('dark'),
                                async search() {
                                    if (this.query.trim().length < 2) {
                                        this.results = [];
                                        this.open = false;
                                        return;
                                    }
                                    this.loading = true;
                                    this.open = true;
                                    try {
                                        const res = await fetch('/api/global-search?q=' + encodeURIComponent(this.query.trim()));
                                        const data = await res.json();
                                        this.results = data.results || [];
                                    } catch (err) {
                                        this.results = [];
                                    } finally {
                                        this.loading = false;
                                    }
                                },
                                clear() {
                                    this.query = '';
                                    this.results = [];
                                    this.open = false;
                                    this.selectedIndex = -1;
                                },
                                navigate(step) {
                                    if (!this.results.length) return;
                                    this.selectedIndex = (this.selectedIndex + step + this.results.length) % this.results.length;
                                },
                                toggleThemeMode() {
                                    this.isDarkMode = window.crmToggleTheme();
                                },
                                setThemeMode(mode) {
                                    if (typeof window.applyCrmMode === 'function') {
                                        this.isDarkMode = window.applyCrmMode(mode, true);
                                    } else {
                                        this.isDarkMode = window.crmToggleTheme();
                                    }
                                },
                                selectActive() {
                                    if (this.selectedIndex >= 0 && this.selectedIndex < this.results.length) {
                                        window.location.href = this.results[this.selectedIndex].url;
                                    }
                                },
                                handleShortcut(isMobile) {
                                    if (isMobile) {
                                        this.mobileSearchOpen = true;
                                        this.$nextTick(() => this.$refs.mobileSearchInput?.focus());
                                    } else {
                                        this.$refs.searchInput?.focus();
                                        this.open = true;
                                    }
                                }
                            };
                        }
                    </script>

                    {{-- Modern SaaS Top Navigation Bar --}}
                    <header class="h-14 sm:h-16 bg-white dark:bg-[#0f172a] border-b border-slate-200/80 dark:border-slate-800 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-40 transition-colors duration-200"
                            x-data="globalHeader()"
                            @theme-mode-changed.window="isDarkMode = $event.detail.isDark"
                            @theme-changed.window="if ($event.detail && typeof $event.detail.isDark === 'boolean') isDarkMode = $event.detail.isDark"
                            @sidebar-collapsed-changed.window="sidebarCollapsed = $event.detail.collapsed"
                            @keydown.escape.window="open = false; mobileSearchOpen = false"
                            @keydown.window.prevent.ctrl.k="handleShortcut(window.innerWidth < 768)"
                            @keydown.window.prevent.cmd.k="handleShortcut(window.innerWidth < 768)">
                        
                        {{-- Left side: Mobile Drawer Toggle & Clean Breadcrumb Navigation matching Image 3 --}}
                        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                            {{-- Mobile Hamburger Toggle --}}
                            <button @click="sidebarOpen = !sidebarOpen" 
                                    type="button"
                                    class="lg:hidden inline-flex items-center justify-center p-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer shrink-0"
                                    aria-label="Toggle navigation menu">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>

                            {{-- Navigation Chevrons & Title matching Image 3 --}}
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="hidden sm:flex items-center gap-0.5 text-slate-400 dark:text-slate-500">
                                    <button type="button" onclick="window.history.back()" class="p-1 hover:text-slate-700 dark:hover:text-slate-200 transition rounded" title="Back">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <button type="button" onclick="window.history.forward()" class="p-1 hover:text-slate-700 dark:hover:text-slate-200 transition rounded" title="Forward">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                                <span class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">
                                    {{ request()->routeIs('dashboard') || request()->routeIs('portal.dashboard') ? 'Dashboard' : (request()->routeIs('projects.*') ? 'Projects' : (request()->routeIs('leads.*') ? 'Leads' : (request()->routeIs('jobs.*') ? 'Tasks & Field Jobs' : (request()->routeIs('service-tickets.*') || request()->routeIs('portal.tickets*') ? 'Support Tickets' : (request()->routeIs('quotations.*') || request()->routeIs('portal.quotations*') ? 'Quotations' : (request()->routeIs('calendar.*') ? 'Calendar' : (request()->routeIs('inventory.*') ? 'Inventory' : (request()->routeIs('invoices.*') || request()->routeIs('portal.invoices*') ? 'Invoices' : (request()->routeIs('site-surveys.*') ? 'Site Surveys' : (request()->routeIs('amc.*') ? 'AMC Contracts' : (request()->routeIs('equipment.*') ? 'Hardware' : (request()->routeIs('suppliers.*') ? 'Suppliers' : (request()->routeIs('reports.*') ? 'Reports' : 'Dashboard'))))))))))))) }}
                                </span>
                            </div>
                        </div>

                        {{-- Center Section: Global Search --}}
                        <div class="hidden md:flex flex-1 items-center justify-center px-4 lg:px-8 max-w-xl mx-auto">
                            <div class="relative w-full max-w-sm lg:max-w-md" @click.away="open = false">
                                <div class="relative flex items-center">
                                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3.5 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="7"/>
                                        <line stroke-linecap="round" stroke-linejoin="round" x1="21" x2="16.65" y1="21" y2="16.65"/>
                                    </svg>
                                    <input type="text" 
                                           x-ref="searchInput"
                                           x-model="query"
                                           @input.debounce.250ms="search()"
                                           @focus="if(query.length >= 2) open = true"
                                           @keydown.arrow-down.prevent="navigate(1)"
                                           @keydown.arrow-up.prevent="navigate(-1)"
                                           @keydown.enter.prevent="selectActive()"
                                           placeholder="Search leads, jobs, quotes, equipment..." 
                                           style="padding-left: 2.75rem !important;"
                                           class="w-full pl-11 pr-10 py-1.5 text-xs sm:text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200/90 dark:border-slate-700 rounded-lg focus:bg-white dark:focus:bg-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-white placeholder-slate-400 transition-all outline-none">
                                    
                                    {{-- Clear button or loading spinner --}}
                                    <div class="absolute right-2.5 flex items-center">
                                        <template x-if="loading">
                                            <svg class="animate-spin h-3.5 w-3.5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="!loading && query.length > 0">
                                            <button @click="clear()" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold leading-none p-0.5 cursor-pointer">
                                                &times;
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Desktop Dropdown Results --}}
                                <div x-show="open && query.trim().length >= 2 && !mobileSearchOpen"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                                     class="absolute left-0 right-0 mt-2 w-full max-h-96 overflow-y-auto bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl py-2 z-50 divide-y divide-slate-100 dark:divide-slate-800"
                                     style="display: none;">
                                    
                                    {{-- Results list --}}
                                    <template x-if="results.length > 0">
                                        <div class="py-1">
                                            <template x-for="(item, index) in results" :key="index">
                                                <a :href="item.url" 
                                                   :class="selectedIndex === index ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                                                   class="flex items-start gap-3 px-3.5 py-2.5 transition text-left group block">
                                                    <div class="mt-0.5 w-6 h-6 text-slate-400 dark:text-slate-500 flex items-center justify-center shrink-0">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                            <polyline stroke-linecap="round" stroke-linejoin="round" points="14 2 14 8 20 8"/>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center justify-between gap-1 mb-0.5">
                                                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="item.title"></p>
                                                            <span class="text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0" x-text="item.badge"></span>
                                                        </div>
                                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate" x-text="item.subtitle"></p>
                                                        <span class="text-[9px] font-semibold text-blue-600 dark:text-sky-400" x-text="item.type"></span>
                                                    </div>
                                                </a>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Empty State --}}
                                    <template x-if="!loading && results.length === 0">
                                        <div class="p-6 text-center">
                                            
                                            <p class="text-xs font-bold text-slate-700 dark:text-slate-200">No matches found</p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">No leads, jobs, quotes or equipment matching "<span class="font-semibold text-slate-800 dark:text-slate-200" x-text="query"></span>"</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Right side: Header Actions --}}
                        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                            
                            {{-- Mobile Omnisearch Trigger Button (Icon button on mobile) --}}
                            <button @click="mobileSearchOpen = true; $nextTick(() => { $refs.mobileSearchInput.focus(); if (query.trim().length >= 2) open = true; })" 
                                    type="button"
                                    class="md:hidden p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition shrink-0 cursor-pointer"
                                    title="Search">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>

                            {{-- Notification Bell (Role-Aware & Live) --}}
                            @php
                                $currentUser = auth()->user();
                                if ($currentUser->isCustomer()) {
                                    $custLead = $currentUser->getCustomerLead();
                                    $leadId = $custLead?->id;
                                    $card1Count = $leadId ? \App\Models\ServiceTicket::where('lead_id', $leadId)->whereIn('status', ['open','pending','in_progress'])->count() : 0;
                                    $card1Label = 'Active Tickets';
                                    $card1Route = route('portal.tickets');
                                    $card1Color = 'amber';

                                    $card2Count = $leadId ? \App\Models\Quotation::where('lead_id', $leadId)->where('status', 'sent')->count() : 0;
                                    $card2Label = 'Quotations';
                                    $card2Route = route('portal.quotations');
                                    $card2Color = 'blue';

                                    $card3Count = $leadId ? \App\Models\Invoice::where('lead_id', $leadId)->whereIn('status', ['pending','unpaid','overdue'])->count() : 0;
                                    $card3Label = 'Due Invoices';
                                    $card3Route = route('portal.invoices');
                                    $card3Color = 'emerald';

                                    $notifTotalBadge = $card1Count + $card2Count + $card3Count;
                                    $footerUrl = route('portal.tickets');
                                    $footerText = 'Go to Support Tickets →';

                                    $recentAlerts = \App\Models\NotificationLog::where(function($q) use ($currentUser, $custLead) {
                                        $q->where('recipient_email', $currentUser->email);
                                        if ($custLead && $custLead->phone && $custLead->phone !== 'Pending update') {
                                            $q->orWhere('recipient_phone', $custLead->phone);
                                        }
                                    })->latest()->limit(6)->get();
                                } elseif ($currentUser->role === 'technician') {
                                    $techId = $currentUser->id;
                                    $card1Count = \App\Models\ServiceTicket::where('assigned_technician_id', $techId)->whereIn('status', ['open','pending','in_progress'])->count();
                                    $card1Label = 'My Tickets';
                                    $card1Route = route('technician.dashboard');
                                    $card1Color = 'amber';

                                    $card2Count = \App\Models\InstallationJob::where('assigned_technician_id', $techId)->whereIn('status', ['scheduled','in_progress','assigned'])->count();
                                    $card2Label = 'Field Jobs';
                                    $card2Route = route('technician.dashboard');
                                    $card2Color = 'blue';

                                    $card3Count = \App\Models\AmcVisit::where('assigned_technician_id', $techId)->where('status', 'scheduled')->count();
                                    $card3Label = 'AMC Visits';
                                    $card3Route = route('technician.dashboard');
                                    $card3Color = 'emerald';

                                    $notifTotalBadge = $card1Count + $card2Count + $card3Count;
                                    $footerUrl = route('technician.dashboard');
                                    $footerText = 'Technician Workstation →';

                                    $recentAlerts = \App\Models\NotificationLog::where('recipient_email', $currentUser->email)->latest()->limit(6)->get();
                                } else {
                                    // Admin & Internal Staff
                                    $card1Count = \App\Models\ServiceTicket::whereIn('status', ['open','pending'])->count();
                                    $card1Label = 'Open Tickets';
                                    $card1Route = route('service-tickets.index');
                                    $card1Color = 'amber';

                                    $card2Count = \App\Models\NotificationLog::where('status','failed')->whereDate('created_at','>=',now()->subDays(7))->count();
                                    $card2Label = 'Failed Alerts';
                                    $card2Route = route('alerts.index');
                                    $card2Color = 'rose';

                                    $card3Count = \App\Models\PurchaseOrder::where('status','pending')->count();
                                    $card3Label = 'Pending POs';
                                    $card3Route = route('purchase-orders.index');
                                    $card3Color = 'blue';

                                    $notifTotalBadge = $card1Count + $card2Count + $card3Count;
                                    $footerUrl = route('alerts.index');
                                    $footerText = 'View All Alert Logs →';

                                    $recentAlerts = \App\Models\NotificationLog::latest()->limit(6)->get();
                                }
                            @endphp
                            {{-- Notification Bell (Role-Aware & Live) --}}
                            <div x-data="{ notifOpen: false }" class="relative">
                                <button type="button"
                                        @click="notifOpen = !notifOpen"
                                        class="relative p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer select-none"
                                        aria-label="View notifications">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                    </svg>
                                    @if($notifTotalBadge > 0)
                                        <span class="absolute top-1.5 right-1.5 w-4 h-4 bg-rose-600 text-white rounded-full text-[9px] font-bold flex items-center justify-center ring-2 ring-white dark:ring-slate-900 shadow-xs">
                                            {{ $notifTotalBadge > 9 ? '9+' : $notifTotalBadge }}
                                        </span>
                                    @endif
                                </button>

                                {{-- Notification Dropdown Panel --}}
                                <div x-show="notifOpen"
                                     @click.away="notifOpen = false"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
                                     x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                                     class="absolute right-0 mt-2 w-80 bg-white dark:bg-[#0f172a] rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 z-50 overflow-hidden"
                                     style="display:none;">

                                    {{-- Header --}}
                                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white">Notifications</span>
                                        @if($notifTotalBadge > 0)
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                {{ $notifTotalBadge }} active
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-400">All clear ✓</span>
                                        @endif
                                    </div>

                                    {{-- Summary Badges --}}
                                    <div class="px-4 py-3 grid grid-cols-3 gap-2 border-b border-slate-100 dark:border-slate-800">
                                        <a href="{{ $card1Route }}" class="flex flex-col items-center p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                                            <span class="text-lg font-bold text-slate-900 dark:text-white leading-none">{{ $card1Count }}</span>
                                            <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400 mt-1 text-center leading-tight">{{ $card1Label }}</span>
                                        </a>
                                        <a href="{{ $card2Route }}" class="flex flex-col items-center p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                                            <span class="text-lg font-bold text-slate-900 dark:text-white leading-none">{{ $card2Count }}</span>
                                            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 mt-1 text-center leading-tight">{{ $card2Label }}</span>
                                        </a>
                                        <a href="{{ $card3Route }}" class="flex flex-col items-center p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                                            <span class="text-lg font-bold text-slate-900 dark:text-white leading-none">{{ $card3Count }}</span>
                                            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 mt-1 text-center leading-tight">{{ $card3Label }}</span>
                                        </a>
                                    </div>

                                    {{-- Recent Notification Logs --}}
                                    <div class="max-h-60 overflow-y-auto">
                                        @forelse($recentAlerts as $alert)
                                            <div class="px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition border-b border-slate-50 dark:border-slate-800/50 last:border-0">
                                                <div class="flex items-start gap-2.5">
                                                    {{-- Channel Icon --}}
                                                    <span class="mt-0.5 text-base leading-none flex-shrink-0">
                                                        <span class="font-mono font-bold text-[10px] text-blue-600 dark:text-blue-400">[{{ strtoupper($alert->channel) }}]</span>
                                                    </span>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $alert->recipient_name }}</p>
                                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ Str::limit($alert->message_body ?? $alert->subject, 55) }}</p>
                                                        <div class="flex items-center gap-2 mt-0.5">
                                                            <span class="text-[10px] text-slate-400">{{ $alert->created_at->diffForHumans() }}</span>
                                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full
                                                                {{ $alert->status === 'sent' || $alert->status === 'delivered' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : '' }}
                                                                {{ $alert->status === 'failed' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400' : '' }}
                                                                {{ $alert->status === 'queued' ? 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-400' : '' }}
                                                                {{ !in_array($alert->status, ['sent','delivered','failed','queued']) ? 'bg-slate-100 text-slate-600' : '' }}
                                                            ">{{ ucfirst($alert->status) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="px-4 py-6 text-center text-slate-400 dark:text-slate-500 text-xs">
                                                <p class="font-semibold">No notifications yet</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    {{-- Footer --}}
                                    <div class="px-4 py-2.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60">
                                        <a href="{{ $footerUrl }}" class="block text-center text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ $footerText }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- High-Clarity SaaS Theme Switcher (Dual Pill Segmented Switch) --}}
                            <button type="button"
                                    @click="toggleThemeMode()"
                                    class="inline-flex items-center gap-1 p-1 rounded-xl border border-slate-200/90 dark:border-slate-700/80 bg-slate-100/90 dark:bg-slate-800/90 hover:bg-slate-200/70 dark:hover:bg-slate-700/60 transition shadow-2xs cursor-pointer select-none"
                                    :title="isDarkMode ? 'Current: Dark Mode (Click to switch to Light Mode)' : 'Current: Light Mode (Click to switch to Dark Mode)'"
                                    aria-label="Toggle theme mode">
                                {{-- Light Pill Option --}}
                                <span @click.stop="setThemeMode('day')"
                                      class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold transition-all duration-150 cursor-pointer"
                                      :class="!isDarkMode ? 'bg-white text-slate-800 shadow-xs ring-1 ring-slate-200/70' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300'">
                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                    </svg>
                                    <span class="hidden sm:inline text-[11px] leading-none">Light</span>
                                </span>
                                {{-- Dark Pill Option --}}
                                <span @click.stop="setThemeMode('night')"
                                      class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold transition-all duration-150 cursor-pointer"
                                      :class="isDarkMode ? 'bg-slate-900 text-white shadow-xs ring-1 ring-slate-700' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300'">
                                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                                    </svg>
                                    <span class="hidden sm:inline text-[11px] leading-none">Dark</span>
                                </span>
                            </button>

                            {{-- User Avatar Button (Initial Badge Only - Click reveals full profile popover) --}}
                            <div x-data="{ open: false }" class="relative shrink-0 ml-0.5">
                                <button @click="open = !open" 
                                        type="button"
                                        class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm text-white shadow-xs transition-all duration-150 hover:opacity-95 active:scale-95 focus:outline-none cursor-pointer select-none ring-2 ring-slate-200/90 dark:ring-slate-700/80 crm-user-avatar"
                                        style="background-color: var(--crm-accent, #2563eb);"
                                        aria-label="User account menu"
                                        aria-expanded="false"
                                        :aria-expanded="open.toString()"
                                        title="{{ Auth::user()->name }} (Click to view profile menu)">
                                    <span class="text-white font-bold leading-none select-none">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                </button>

                                <div x-show="open" 
                                     @click.away="open = false" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-1.5rem)] bg-white dark:bg-[#0f172a] rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 py-2.5 z-50 text-sm font-semibold text-slate-700 dark:text-slate-200"
                                     style="display: none;">
                                    
                                    {{-- User info header --}}
                                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center gap-3 mb-2">
                                            <div class="w-10 h-10 rounded-full text-white flex items-center justify-center font-bold text-sm shrink-0 crm-user-avatar ring-2 ring-white dark:ring-slate-800 shadow-sm" style="background-color: var(--crm-accent, #2563eb);">
                                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-bold text-slate-900 dark:text-white truncate font-heading text-sm">{{ Auth::user()->name }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                                {{ Auth::user()->isAdmin() ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 border border-purple-200 dark:border-purple-800' : '' }}
                                                {{ Auth::user()->isStaff() ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800' : '' }}
                                                {{ Auth::user()->isTechnician() ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800' : '' }}
                                                {{ Auth::user()->isCustomer() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : '' }}
                                            ">
                                                {{ ucfirst(Auth::user()->role) }}
                                            </span>
                                            <span class="text-[11px] text-slate-400">• Active</span>
                                        </div>
                                    </div>

                                    {{-- Quick Links --}}
                                    <div class="py-1">
                                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition text-xs font-semibold">
                                            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span>My Account &amp; Profile Settings</span>
                                        </a>

                                        @if(Auth::user()->isAdmin())
                                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition text-xs font-semibold">
                                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                                <span>Manage User Accounts</span>
                                            </a>
                                        @endif

                                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition text-xs font-semibold">
                                            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5L12 3l9 7.5V20a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-9.5z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 22V12h6v10"/>
                                            </svg>
                                            <span>Storefront View</span>
                                        </a>

                                        {{-- Quick Theme Switch inside dropdown --}}
                                        <button type="button" @click="toggleThemeMode()" class="w-full text-left flex items-center justify-between px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition text-xs font-semibold cursor-pointer">
                                            <span class="flex items-center gap-2.5">
                                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                                </svg>
                                                <span>Theme Mode</span>
                                            </span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300" x-text="isDarkMode ? 'Night' : 'Day'"></span>
                                        </button>
                                    </div>

                                    {{-- Logout --}}
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-semibold transition border-t border-slate-100 dark:border-slate-800 cursor-pointer text-xs">
                                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Log Out
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>

                        {{-- Mobile Omnisearch Full-Width Overlay (Takes over header smoothly when active on mobile) --}}
                        <div x-show="mobileSearchOpen"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="absolute inset-0 bg-white dark:bg-[#0f172a] z-50 px-3 flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 md:hidden"
                             style="display: none;">
                            
                            <div class="relative flex-1 flex items-center">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" 
                                       x-ref="mobileSearchInput"
                                       x-model="query"
                                       @input.debounce.250ms="search()"
                                       @focus="if(query.length >= 2) open = true"
                                       @keydown.arrow-down.prevent="navigate(1)"
                                       @keydown.arrow-up.prevent="navigate(-1)"
                                       @keydown.enter.prevent="selectActive()"
                                       placeholder="Search leads, jobs, quotes, serials..." 
                                       style="padding-left: 2.75rem !important;"
                                       class="w-full pl-11 pr-8 py-2 text-xs bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl focus:bg-white dark:focus:bg-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 shadow-2xs">
                                
                                {{-- Clear button or loading spinner --}}
                                <div class="absolute right-2.5 flex items-center">
                                    <template x-if="loading">
                                        <svg class="animate-spin h-3.5 w-3.5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="!loading && query.length > 0">
                                        <button @click="clear()" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold leading-none p-0.5">
                                            &times;
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <button @click="mobileSearchOpen = false; open = false;" 
                                    type="button" 
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0">
                                Cancel
                            </button>

                            {{-- Mobile Dropdown Results --}}
                            <div x-show="open && query.trim().length >= 2 && mobileSearchOpen"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="absolute left-2 right-2 top-16 max-h-80 overflow-y-auto bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl py-2 z-50 divide-y divide-slate-100 dark:divide-slate-800"
                                 style="display: none;">
                                
                                <template x-if="results.length > 0">
                                    <div class="py-1">
                                        <template x-for="(item, index) in results" :key="index">
                                            <a :href="item.url" 
                                               :class="selectedIndex === index ? 'bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-amber-400' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                                               class="flex items-start gap-3 px-3.5 py-2.5 transition text-left group block">
                                                <div class="mt-0.5 w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center shrink-0 group-hover:bg-blue-100 dark:group-hover:bg-blue-950 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                                    <span class="text-xs font-bold" x-text="item.type ? item.type.substring(0, 3).toUpperCase() : 'REC'"></span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="item.title"></p>
                                                        <span class="text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0" x-text="item.badge"></span>
                                                    </div>
                                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate" x-text="item.subtitle"></p>
                                                    <span class="text-[9px] font-semibold text-blue-600 dark:text-sky-400" x-text="item.type"></span>
                                                </div>
                                            </a>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="!loading && results.length === 0">
                                    <div class="p-6 text-center">
                                        
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200">No matches found</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">No results matching "<span class="font-semibold text-slate-800 dark:text-slate-200" x-text="query"></span>"</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </header>

                    {{-- Page Header (if defined) --}}
                    @isset($header)
                        <div class="header-slot-container bg-white dark:bg-[#0f172a] border-b border-slate-200/80 dark:border-slate-800 px-4 py-4 sm:px-6 lg:px-8 shadow-xs transition-colors duration-200">
                            {{ $header }}
                        </div>
                    @endisset

                    {{-- Main Content View --}}
                    <main class="flex-1 overflow-y-auto bg-[#f8fafc] dark:bg-[#060913] transition-colors duration-200 pb-24 lg:pb-8">
                        {{ $slot }}
                    </main>
                </div>

            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- MOBILE APP-LIKE BOTTOM NAVIGATION DOCK (MOBILE VIEW ONLY)         --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-[#0f172a]/95 backdrop-blur-md border-t border-slate-200/90 dark:border-slate-800 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] px-3 py-1.5 transition-colors">
                <div class="max-w-md mx-auto flex items-center justify-between">
                    @if(auth()->user()->isCustomer())
                        {{-- Customer Mobile Dock --}}
                        <a href="{{ route('portal.dashboard') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('portal.dashboard') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span class="text-[10px] tracking-tight">Home</span>
                        </a>

                        <a href="{{ route('portal.equipment') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('portal.equipment') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span class="text-[10px] tracking-tight">Cameras</span>
                        </a>

                        {{-- Elevated Center Action: Report Ticket --}}
                        <a href="{{ route('portal.tickets.create') }}" 
                           class="-mt-5 flex flex-col items-center group">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/35 border-2 border-white dark:border-[#0f172a] active:scale-95 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <span class="text-[9px] font-bold text-blue-600 dark:text-blue-400 mt-0.5">Report</span>
                        </a>

                        <a href="{{ route('portal.tickets') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('portal.tickets*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span class="text-[10px] tracking-tight">Tickets</span>
                        </a>

                        <button @click="sidebarOpen = true" 
                                type="button"
                                class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span class="text-[10px] tracking-tight">Menu</span>
                        </button>

                    @elseif(auth()->user()->role === 'technician')
                        {{-- Technician Mobile Dock --}}
                        <a href="{{ route('technician.dashboard') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('technician.dashboard') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                            <span class="text-[10px] tracking-tight">Jobs</span>
                        </a>

                        <button @click="sidebarOpen = true" 
                                type="button"
                                class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span class="text-[10px] tracking-tight">Modules</span>
                        </button>

                        {{-- Elevated Center Action: Shift & Attendance --}}
                        <a href="{{ route('attendance.index') }}" 
                           class="-mt-5 flex flex-col items-center group">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/35 border-2 border-white dark:border-[#0f172a] active:scale-95 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">Shift</span>
                        </a>

                        <a href="{{ route('service-tickets.index') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('service-tickets.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            <span class="text-[10px] tracking-tight">Tickets</span>
                        </a>

                        <a href="{{ route('profile.edit') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('profile.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-[10px] tracking-tight">Profile</span>
                        </a>

                    @else
                        {{-- Admin & Staff Mobile Dock --}}
                        <a href="{{ route('dashboard') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span class="text-[10px] tracking-tight">Home</span>
                        </a>

                        <button @click="sidebarOpen = true" 
                                type="button"
                                class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span class="text-[10px] tracking-tight">Modules</span>
                        </button>

                        {{-- Elevated Center Action Button (+) with Mobile Quick Action Sheet --}}
                        <div x-data="{ mobileActionOpen: false }" class="relative -mt-5">
                            <button @click="mobileActionOpen = !mobileActionOpen" 
                                    type="button"
                                    class="w-12 h-12 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/35 border-2 border-white dark:border-[#0f172a] active:scale-95 transition-transform cursor-pointer"
                                    title="Quick Action">
                                <svg class="w-6 h-6 transition-transform duration-200" :class="mobileActionOpen ? 'rotate-45' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </button>
                            <span class="block text-center text-[9px] font-bold text-blue-600 dark:text-blue-400 mt-0.5">Create</span>

                            {{-- Mobile Quick Action Bottom Sheet --}}
                            <div x-show="mobileActionOpen" 
                                 @click.away="mobileActionOpen = false" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                                 class="absolute bottom-16 left-1/2 -translate-x-1/2 w-64 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl p-2 z-50 text-xs font-semibold divide-y divide-slate-100 dark:divide-slate-800"
                                 style="display: none;">
                                <div class="px-3 py-1.5 font-bold text-slate-400 uppercase text-[10px] tracking-wider">Quick Actions</div>
                                <div class="py-1">
                                    <a href="{{ route('leads.create') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-blue-600">
                                        <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-600 flex items-center justify-center font-bold text-xs">+</span>
                                        New Customer Lead
                                    </a>
                                    <a href="{{ route('quotations.create-general') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-emerald-600">
                                        <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-600 flex items-center justify-center font-bold text-xs">+</span>
                                        Generate Quotation
                                    </a>
                                    <a href="{{ route('service-tickets.create') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-rose-600">
                                        <span class="w-6 h-6 rounded-lg bg-rose-100 dark:bg-rose-950 text-rose-600 flex items-center justify-center font-bold text-xs">+</span>
                                        Log Service Ticket
                                    </a>
                                    <a href="{{ route('attendance.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-purple-600">
                                        <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-600 flex items-center justify-center font-bold text-xs"></span>
                                        Attendance & Shift
                                    </a>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('attendance.index') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('attendance.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-[10px] tracking-tight">Roster</span>
                        </a>

                        <a href="{{ route('profile.edit') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('profile.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-[10px] tracking-tight">Profile</span>
                        </a>
                    @endif
                </div>
            </nav>

    </body>
</html>
