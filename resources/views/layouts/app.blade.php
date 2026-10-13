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

            /* TomSelect Clean Single Border Specification */
            .ts-wrapper,
            .select-wrap,
            .input-box,
            .form-group:has(> .ts-wrapper),
            .form-group:has(> select) {
                border: none !important;
                padding: 0 !important;
                border-radius: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
            }
            .ts-control {
                height: 38px !important;
                min-height: 38px !important;
                max-height: 38px !important;
                border-radius: 4px !important;
                background-color: #ffffff !important;
                border: 1px solid #cccccc !important;
                box-sizing: border-box !important;
                font-size: 14px !important;
                padding: 0 10px !important;
                display: flex !important;
                align-items: center !important;
                box-shadow: none !important;
            }
            .ts-control input,
            .ts-wrapper .ts-control input,
            .ts-wrapper.single .ts-control input {
                border: none !important;
                border-width: 0 !important;
                outline: none !important;
                box-shadow: none !important;
                background: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                height: 100% !important;
                font-size: 14px !important;
                color: #222222 !important;
            }
            .ts-wrapper .ts-control .item,
            .ts-wrapper.single .ts-control .item {
                border: none !important;
                background: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 14px !important;
                color: #222222 !important;
            }
            html.dark .ts-control {
                background-color: #0f172a !important;
                border-color: #475569 !important;
                color: #f8fafc !important;
            }
            html.dark .ts-control input,
            html.dark .ts-wrapper .ts-control input,
            html.dark .ts-wrapper .ts-control .item {
                color: #f8fafc !important;
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

                {{-- Sidebar Container: Fixed on mobile, sticky on desktop, 220px wide, white, 1px solid #ddd right border --}}
                <div :class="{
                        'translate-x-0': sidebarOpen,
                        '-translate-x-full lg:translate-x-0': !sidebarOpen,
                        'lg:w-20': sidebarCollapsed,
                        'lg:w-[220px]': !sidebarCollapsed
                     }"
                     class="fixed inset-y-0 left-0 z-50 lg:sticky lg:top-0 lg:h-screen lg:z-30 w-[220px] shrink-0 transition-[width,transform] duration-200 ease-in-out flex flex-col bg-white dark:bg-[#0f172a] border-r border-[#ddd] dark:border-slate-800 overflow-hidden">
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

                    {{-- Fixed Top Bar: 56px high, white, bottom border 1px solid #ddd, sticky top: 0, z-index: 10 --}}
                    <header class="h-[56px] min-h-[56px] max-h-[56px] bg-white dark:bg-[#0f172a] border-b border-[#ddd] dark:border-slate-800 px-5 flex items-center justify-between sticky top-0 z-10"
                            x-data="globalHeader()"
                            @theme-mode-changed.window="isDarkMode = $event.detail.isDark"
                            @theme-changed.window="if ($event.detail && typeof $event.detail.isDark === 'boolean') isDarkMode = $event.detail.isDark"
                            @sidebar-collapsed-changed.window="sidebarCollapsed = $event.detail.collapsed"
                            @keydown.escape.window="open = false; mobileSearchOpen = false"
                            @keydown.window.prevent.ctrl.k="handleShortcut(window.innerWidth < 768)"
                            @keydown.window.prevent.cmd.k="handleShortcut(window.innerWidth < 768)">
                        
                        {{-- 1. Page Name (Left) --}}
                        <div class="flex items-center gap-2 shrink-0">
                            {{-- Mobile Hamburger Toggle --}}
                            <button @click="sidebarOpen = !sidebarOpen" 
                                    type="button"
                                    class="lg:hidden inline-flex items-center justify-center p-1.5 rounded text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer shrink-0"
                                    aria-label="Toggle navigation menu">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>

                            <span class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                {{ request()->routeIs('dashboard') || request()->routeIs('portal.dashboard') ? 'Dashboard' : (request()->routeIs('projects.*') ? 'Projects' : (request()->routeIs('leads.*') ? 'Leads' : (request()->routeIs('jobs.*') ? 'Tasks & Field Jobs' : (request()->routeIs('service-tickets.*') || request()->routeIs('portal.tickets*') ? 'Support Tickets' : (request()->routeIs('quotations.*') || request()->routeIs('portal.quotations*') ? 'Quotations' : (request()->routeIs('calendar.*') ? 'Calendar' : (request()->routeIs('inventory.*') ? 'Inventory' : (request()->routeIs('invoices.*') || request()->routeIs('portal.invoices*') ? 'Invoices' : (request()->routeIs('site-surveys.*') ? 'Site Surveys' : (request()->routeIs('amc.*') ? 'AMC Contracts' : (request()->routeIs('equipment.*') ? 'Hardware' : (request()->routeIs('suppliers.*') ? 'Suppliers' : (request()->routeIs('reports.*') ? 'Reports' : 'Dashboard'))))))))))))) }}
                            </span>
                        </div>

                        {{-- 2. One Search Box (Center) --}}
                        <div class="hidden md:flex flex-1 items-center justify-center px-4 max-w-md mx-auto">
                            <div class="relative w-full" @click.away="open = false">
                                <div class="relative flex items-center">
                                    <svg class="w-4 h-4 text-[#666] dark:text-slate-400 absolute left-3.5 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
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
                                           style="padding-left: 2.5rem !important; height: 38px !important; border: 1px solid #ccc !important; border-radius: 4px !important; box-shadow: none !important;"
                                           class="w-full text-xs bg-white dark:bg-slate-900 border border-[#ccc] dark:border-slate-700 rounded text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none">
                                    
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

                                {{-- Search Dropdown Results --}}
                                <div x-show="open && query.trim().length >= 2 && !mobileSearchOpen"
                                     class="absolute left-0 right-0 mt-1 w-full max-h-96 overflow-y-auto bg-white dark:bg-[#0f172a] border border-[#ddd] dark:border-slate-700 rounded py-1 z-50 divide-y divide-slate-100 dark:divide-slate-800"
                                     style="display: none;">
                                    <template x-if="results.length > 0">
                                        <div class="py-1">
                                            <template x-for="(item, index) in results" :key="index">
                                                <a :href="item.url" 
                                                   :class="selectedIndex === index ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                                                   class="flex items-start gap-3 px-3 py-2 transition text-left group block">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center justify-between gap-1">
                                                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="item.title"></p>
                                                            <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0" x-text="item.badge"></span>
                                                        </div>
                                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate" x-text="item.subtitle"></p>
                                                    </div>
                                                </a>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!loading && results.length === 0">
                                        <div class="p-4 text-center text-xs text-slate-500">
                                            No matches found
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        @php
                            $headerNotifs = \App\Services\HeaderNotificationService::getNotificationsForUser(Auth::user());
                        @endphp

                        {{-- 3. Topbar Controls: Search, Theme Toggle, Notification Bell & Pill Profile (Right) --}}
                        <div class="flex items-center gap-2.5 shrink-0">
                            {{-- Mobile search trigger icon --}}
                            <button @click="mobileSearchOpen = true; $nextTick(() => { $refs.mobileSearchInput.focus(); if (query.trim().length >= 2) open = true; })" 
                                    type="button"
                                    class="md:hidden p-1.5 text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition shrink-0 cursor-pointer"
                                    title="Search">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>

                            {{-- Universal 1-Click Dark/Light Mode Button on Every Page for All Users --}}
                            <button @click="toggleThemeMode()" 
                                    type="button" 
                                    id="header-theme-toggle-btn"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 transition cursor-pointer shrink-0" 
                                    :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'" 
                                    aria-label="Toggle dark/light mode">
                                {{-- Sun Icon (shown when dark mode is active) --}}
                                <svg x-show="isDarkMode" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="5" fill="currentColor"/>
                                    <line x1="12" y1="1" x2="12" y2="3"/>
                                    <line x1="12" y1="21" x2="12" y2="23"/>
                                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                                    <line x1="1" y1="12" x2="3" y2="12"/>
                                    <line x1="21" y1="12" x2="23" y2="12"/>
                                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                                </svg>
                                {{-- Moon Icon (shown when light mode is active) --}}
                                <svg x-show="!isDarkMode" class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                                </svg>
                            </button>

                            {{-- Modern Notification Bell & Popover Module --}}
                            <div x-data="{
                                notifOpen: false,
                                unreadCount: {{ $headerNotifs['unread_count'] }},
                                markAllRead() {
                                    this.unreadCount = 0;
                                    try { localStorage.setItem('crm_notif_read_timestamp', Date.now()); } catch(e){}
                                }
                            }" class="relative">
                                <button @click="notifOpen = !notifOpen" 
                                        type="button" 
                                        id="header-notification-btn"
                                        class="relative inline-flex items-center justify-center w-8 h-8 rounded border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 transition cursor-pointer shrink-0" 
                                        title="Notifications & System Alerts" 
                                        aria-label="View notifications">
                                    <svg class="w-4 h-4 text-slate-600 dark:text-slate-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <span x-show="unreadCount > 0" 
                                          x-text="unreadCount" 
                                          class="absolute -top-1 -right-1 flex items-center justify-center min-w-[17px] h-[17px] px-1 text-[9px] font-black text-white bg-rose-500 rounded-full ring-2 ring-white dark:ring-slate-900 leading-none">
                                    </span>
                                </button>

                                {{-- Notification Popover Dropdown --}}
                                <div x-show="notifOpen" 
                                     @click.away="notifOpen = false" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-[#0f172a] rounded-xl border border-[#ddd] dark:border-slate-700 shadow-xl py-0 z-50 overflow-hidden text-xs text-slate-700 dark:text-slate-200"
                                     style="display: none;">
                                    
                                    {{-- Popover Header --}}
                                    <div class="px-4 py-3 border-b border-[#ddd] dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/80 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 dark:text-white">Notifications</span>
                                            <span x-show="unreadCount > 0" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400" x-text="unreadCount + ' new'"></span>
                                        </div>
                                        <button type="button" @click="markAllRead()" class="text-[11px] font-medium text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                            Mark all as read
                                        </button>
                                    </div>

                                    {{-- Items List --}}
                                    <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/80">
                                        @forelse($headerNotifs['items'] as $item)
                                            <a href="{{ $item['url'] }}" 
                                               @click="notifOpen = false"
                                               class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition group block">
                                                
                                                {{-- Icon badge --}}
                                                <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center 
                                                    @if($item['icon'] === 'ticket') bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900
                                                    @elseif($item['icon'] === 'inventory') bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900
                                                    @elseif($item['icon'] === 'job') bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900
                                                    @elseif($item['icon'] === 'log') bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900
                                                    @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700
                                                    @endif">
                                                    @if($item['icon'] === 'ticket')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                                    @elseif($item['icon'] === 'inventory')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                    @elseif($item['icon'] === 'job')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                    @elseif($item['icon'] === 'log')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @endif
                                                </div>

                                                {{-- Details --}}
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between gap-1">
                                                        <p class="font-bold text-slate-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ $item['title'] }}</p>
                                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 shrink-0">{{ $item['time'] }}</span>
                                                    </div>
                                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $item['description'] }}</p>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="p-6 text-center text-slate-400">
                                                <p class="text-xs">No pending notifications</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    {{-- Footer Action Link --}}
                                    <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border-t border-[#ddd] dark:border-slate-800 text-center">
                                        @if(Auth::user()->isAdmin() || Auth::user()->isStaff())
                                            <a href="{{ route('alerts.index') }}" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                                View Automated Alerts Engine →
                                            </a>
                                        @elseif(Auth::user()->isTechnician())
                                            <a href="{{ route('technician.dashboard') }}" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                                View All Assigned Work Orders →
                                            </a>
                                        @else
                                            <a href="{{ route('portal.tickets') }}" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                                View Support Tickets →
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Pill-Shaped User Profile Capsule & Dropdown Menu --}}
                            <div x-data="{ userMenuOpen: false }" class="relative">
                                <button @click="userMenuOpen = !userMenuOpen" 
                                        type="button"
                                        id="header-user-profile-pill"
                                        class="flex items-center gap-2 pl-1 pr-2.5 py-1 rounded-full border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition cursor-pointer select-none shadow-2xs">
                                    @if(Auth::user()->avatar_url)
                                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-7 h-7 rounded-full object-cover shrink-0 ring-1 ring-slate-200 dark:ring-slate-700">
                                    @else
                                        <div class="w-7 h-7 rounded-full text-white flex items-center justify-center font-bold text-xs shrink-0" style="background-color: var(--crm-accent, #2563eb);">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="text-left hidden sm:block min-w-0 pr-0.5">
                                        <span class="block text-xs font-bold text-slate-800 dark:text-slate-100 leading-tight truncate max-w-[120px]">{{ Auth::user()->name }}</span>
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 font-medium leading-tight truncate">{{ Auth::user()->role_title }}</span>
                                    </div>
                                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0 transition-transform duration-150" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                {{-- User dropdown menu (contains settings, theme switcher, logout) --}}
                                <div x-show="userMenuOpen" 
                                     @click.away="userMenuOpen = false" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-60 bg-white dark:bg-[#0f172a] rounded-xl border border-[#ddd] dark:border-slate-700 shadow-xl py-1 z-50 text-xs font-medium text-slate-700 dark:text-slate-200 overflow-hidden"
                                     style="display: none;">
                                    
                                    {{-- User identity card in menu --}}
                                    <div class="px-3.5 py-2.5 border-b border-[#ddd] dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 flex items-center gap-3">
                                        @if(Auth::user()->avatar_url)
                                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-9 h-9 rounded-full object-cover shrink-0 ring-1 ring-slate-200 dark:ring-slate-700">
                                        @else
                                            <div class="w-9 h-9 rounded-full text-white flex items-center justify-center font-bold text-xs shrink-0" style="background-color: var(--crm-accent, #2563eb);">
                                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-slate-900 dark:text-white truncate leading-tight">{{ Auth::user()->name }}</p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate leading-tight">{{ Auth::user()->email }}</p>
                                            <span class="inline-block mt-1 text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-200/70 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                {{ Auth::user()->role_title }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="py-1">
                                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>Profile Settings &amp; Photo</span>
                                        </a>

                                        @if(Auth::user()->isAdmin())
                                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="6" y2="6"/><circle cx="8" cy="6" r="2.5"/><line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="12" y2="12"/><circle cx="16" cy="12" r="2.5"/><line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="18" y2="18"/><circle cx="10" cy="18" r="2.5"/></svg>
                                                <span>Manage User Accounts</span>
                                            </a>
                                        @endif

                                        <button type="button" @click="toggleThemeMode()" class="w-full text-left flex items-center justify-between px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition cursor-pointer">
                                            <span class="flex items-center gap-2.5">
                                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                                                <span>Toggle Theme</span>
                                            </span>
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300" x-text="isDarkMode ? 'Night' : 'Day'"></span>
                                        </button>
                                    </div>

                                    <form method="POST" action="{{ route('logout') }}" class="border-t border-[#ddd] dark:border-slate-800">
                                        @csrf
                                        <button type="submit" class="w-full text-left flex items-center gap-2.5 px-3.5 py-2 text-rose-600 dark:text-rose-400 font-semibold hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            <span>Log Out</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Mobile Omnisearch Full-Width Overlay --}}
                        <div x-show="mobileSearchOpen"
                             x-cloak
                             class="absolute inset-0 bg-white dark:bg-[#0f172a] z-50 px-3 flex items-center gap-2 border-b border-[#ddd] dark:border-slate-800 md:hidden"
                             style="display: none;">
                            
                            <div class="relative flex-1 flex items-center">
                                <input type="text" 
                                       x-ref="mobileSearchInput"
                                       x-model="query"
                                       @input.debounce.250ms="search()"
                                       @focus="if(query.length >= 2) open = true"
                                       @keydown.arrow-down.prevent="navigate(1)"
                                       @keydown.arrow-up.prevent="navigate(-1)"
                                       @keydown.enter.prevent="selectActive()"
                                       placeholder="Search..." 
                                       style="height: 38px !important; border: 1px solid #ccc !important; border-radius: 4px !important;"
                                       class="w-full px-3 text-xs bg-white dark:bg-slate-900 border border-[#ccc] dark:border-slate-700 rounded text-slate-800 dark:text-white placeholder-slate-400">
                            </div>

                            <button @click="mobileSearchOpen = false; open = false;" 
                                    type="button" 
                                    class="px-2 py-1 rounded text-xs font-semibold text-slate-600 dark:text-slate-300 shrink-0">
                                Cancel
                            </button>
                        </div>
                    </header>

                    {{-- Main Content Area: Background #f5f5f5, padding 20px, maximum width 1200px --}}
                    <main class="flex-1 overflow-y-auto bg-[#f5f5f5] dark:bg-[#060913] p-[20px] transition-colors duration-200 pb-24 lg:pb-8">
                        <div class="max-w-[1200px] mx-auto w-full">
                            {{-- Page Heading (One h2 at top of content area, with main action button on right) --}}
                            @isset($header)
                                <div class="mb-[20px]">
                                    {{ $header }}
                                </div>
                            @endisset

                            {{ $slot }}
                        </div>
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
