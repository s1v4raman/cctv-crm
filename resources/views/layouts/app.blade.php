<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f8fafc]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Precision IT Systems CRM') }} | Smart Operations</title>

        <!-- Zoho CRM Theme & Realtime Dynamic Logo Engine -->
        <x-crm-theme-init :userId="auth()->id()" :userAccent="auth()->user()->theme_accent" :userMode="auth()->user()->theme_mode" :userStyle="auth()->user()->theme_style" />

        <!-- Google Fonts matching SaaS Mockup -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Tailwind CSS & Alpine.js CDN Guard (Guarantees normal styling across all HTTP servers, LAN IPs, domains & production ports) -->
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

        <style>
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
                --crm-accent: {{ auth()->user()->theme_accent ?? '#2563eb' }};
                --crm-accent-hover: {{ auth()->user()->theme_accent ?? '#2563eb' }};
            }
            .accent-bg {
                background-color: var(--crm-accent, #2563eb) !important;
            }
            .accent-text {
                color: var(--crm-accent, #2563eb) !important;
            }
            .accent-border {
                border-color: var(--crm-accent, #2563eb) !important;
            }

            /* Active sidebar navigation item */
            aside nav a.crm-active-link,
            aside nav a[class*="!bg-blue-600"],
            .crm-active-link {
                background-color: var(--crm-accent, #2563eb) !important;
                border-color: var(--crm-accent, #2563eb) !important;
                color: #ffffff !important;
            }
            aside nav a.crm-active-link:hover,
            aside nav a[class*="!bg-blue-600"]:hover {
                background-color: var(--crm-accent-hover, #2563eb) !important;
                border-color: var(--crm-accent-hover, #2563eb) !important;
            }

            /* Clicking / active button feedback */
            button:active:not(:disabled),
            .btn-primary:active,
            .btn-blue:active,
            aside nav a:active {
                background-color: var(--crm-accent-hover, #2563eb) !important;
                border-color: var(--crm-accent-hover, #2563eb) !important;
                transform: scale(0.98);
            }

            /* Brand Logo Tile & User Avatars */
            .crm-brand-tile,
            .crm-user-avatar {
                background-color: var(--crm-accent, #2563eb) !important;
                background-image: none !important;
            }
            .crm-brand-tile-inner,
            .crm-brand-tile > div {
                background-color: var(--crm-accent, #2563eb) !important;
            }
        </style>
    </head>
    <body class="h-full font-sans antialiased text-slate-800 dark:text-slate-100 bg-[#f8fafc] dark:bg-[#060913] selection:bg-blue-600 selection:text-white" 
          x-data="{ 
              sidebarOpen: false, 
              sidebarCollapsed: false,
              toggleSidebarCollapsed() {
                  this.sidebarCollapsed = !this.sidebarCollapsed;
              },
              shortcutsModalOpen: false,
              mobileNavOpen: false,
              isDark: document.documentElement.classList.contains('dark'),
              async toggleTheme() {
                  const isCurrentlyDark = document.documentElement.classList.contains('dark');
                  const newMode = isCurrentlyDark ? 'day' : 'night';
                  const currentUserId = {{ json_encode(auth()->id()) }};
                  const modeKey = currentUserId ? ('crm_mode_user_' + currentUserId) : 'crm_mode';
                  try { localStorage.setItem(modeKey, newMode); } catch(e) {}
                  if (newMode === 'night') {
                      document.documentElement.classList.add('dark');
                      this.isDark = true;
                  } else {
                      document.documentElement.classList.remove('dark');
                      this.isDark = false;
                  }
                  window.dispatchEvent(new CustomEvent('theme-changed', { 
                      detail: { userId: currentUserId, isDark: this.isDark, mode: newMode } 
                  }));
                  try {
                      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                      await fetch('/profile/theme', {
                          method: 'POST',
                          headers: {
                              'Content-Type': 'application/json',
                              'X-CSRF-TOKEN': token || '',
                              'Accept': 'application/json'
                          },
                          body: JSON.stringify({ mode: newMode })
                      });
                  } catch(e) {}
              }
          }"
          @keydown.window="
              if (($event.ctrlKey || $event.metaKey) && $event.key.toLowerCase() === 'b') {
                  $event.preventDefault();
                  toggleSidebarCollapsed();
              } else if (($event.ctrlKey || $event.metaKey) && $event.key === '/') {
                  $event.preventDefault();
                  shortcutsModalOpen = !shortcutsModalOpen;
              } else if ($event.key === '?' && !['INPUT', 'TEXTAREA'].includes($event.target.tagName)) {
                  $event.preventDefault();
                  shortcutsModalOpen = !shortcutsModalOpen;
              } else if ($event.altKey && $event.key.toLowerCase() === 't') {
                  $event.preventDefault();
                  toggleTheme();
              } else if ($event.altKey && $event.key.toLowerCase() === 'd') {
                  $event.preventDefault();
                  window.location.href = '{{ auth()->user()->role === 'technician' ? route('technician.dashboard') : (auth()->user()->isCustomer() ? route('portal.dashboard') : route('dashboard')) }}';
              } else if ($event.altKey && $event.key.toLowerCase() === 'a') {
                  $event.preventDefault();
                  window.location.href = '{{ route('attendance.index') }}';
              } else if ($event.altKey && $event.key.toLowerCase() === 'l' && {{ (auth()->user()->isAdmin() || auth()->user()->isStaff()) ? 'true' : 'false' }}) {
                  $event.preventDefault();
                  window.location.href = '{{ route('leads.index') }}';
              }
          ">
        
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
                        'lg:w-72': !sidebarCollapsed
                     }"
                     class="fixed inset-y-0 left-0 z-50 lg:sticky lg:top-0 lg:h-screen lg:z-30 w-72 shrink-0 transition-[width,transform] duration-300 ease-in-out flex flex-col bg-white dark:bg-[#0f172a] border-r border-slate-200/80 dark:border-slate-800 overflow-hidden">
                    @include('layouts.navigation')
                </div>

                {{-- Main Application Wrapper --}}
                <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-[#f8fafc] dark:bg-[#060913] transition-colors duration-200">
                    
                    {{-- Modern SaaS Top Navigation Bar (Zoho CRM Standard Comfortable Scale) --}}
                    <header class="h-16 sm:h-[70px] bg-white dark:bg-[#0f172a] border-b border-slate-200/80 dark:border-slate-800 px-4 sm:px-5 md:px-6 flex items-center justify-between sticky top-0 z-30 shadow-2xs transition-colors duration-200 relative"
                            x-data="{
                                query: '',
                                results: [],
                                open: false,
                                loading: false,
                                selectedIndex: -1,
                                mobileSearchOpen: false,
                                async search() {
                                    if (this.query.trim().length < 2) {
                                        this.results = [];
                                        this.open = false;
                                        return;
                                    }
                                    this.loading = true;
                                    this.open = true;
                                    try {
                                        const res = await fetch(`/api/global-search?q=${encodeURIComponent(this.query.trim())}`);
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
                                selectActive() {
                                    if (this.selectedIndex >= 0 && this.selectedIndex < this.results.length) {
                                        window.location.href = this.results[this.selectedIndex].url;
                                    }
                                }
                            }"
                            @keydown.escape.window="open = false; mobileSearchOpen = false"
                            @keydown.window.prevent.ctrl.k="if (window.innerWidth < 768) { mobileSearchOpen = true; $nextTick(() => $refs.mobileSearchInput.focus()); } else { $refs.searchInput.focus(); open = true; }"
                            @keydown.window.prevent.cmd.k="if (window.innerWidth < 768) { mobileSearchOpen = true; $nextTick(() => $refs.mobileSearchInput.focus()); } else { $refs.searchInput.focus(); open = true; }">
                        
                        {{-- Left side: Mobile Drawer Toggle (Hamburger Menu for Modules), Laptop View Collapse Toggle & Sleek Context Pill --}}
                        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                            {{-- Mobile Hamburger Toggle --}}
                            <button @click="sidebarOpen = !sidebarOpen" 
                                    type="button"
                                    class="lg:hidden inline-flex items-center justify-center p-2 rounded-xl text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-all shadow-2xs cursor-pointer shrink-0"
                                    aria-label="Toggle navigation menu"
                                    title="Open Modules Menu">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>

                            {{-- Laptop / Desktop Sidebar Collapse Toggle Button (Laptop View) --}}
                            <button @click="toggleSidebarCollapsed()" 
                                    type="button"
                                    class="hidden lg:inline-flex items-center justify-center p-2 rounded-xl text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-all shadow-2xs cursor-pointer shrink-0"
                                    :title="sidebarCollapsed ? 'Expand Sidebar to Full View (Ctrl+B)' : 'Collapse Sidebar to Compact Dock (Ctrl+B)'"
                                    aria-label="Toggle sidebar dock">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                                </svg>
                            </button>

                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-100 truncate flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full animate-pulse shrink-0" style="background-color: var(--crm-accent, #2563eb);"></span>
                                    <span class="tracking-tight">{{ auth()->user()->role === 'technician' ? 'Field Station' : (auth()->user()->isCustomer() ? 'Client Portal' : 'Command Center') }}</span>
                                </span>
                            </div>
                        </div>

                        {{-- Center Section: Global Omnisearch with Proper Normal Size & Wide Breathing Room --}}
                        <div class="hidden md:flex flex-1 items-center justify-center px-4 lg:px-8 max-w-xl mx-auto">
                            <div class="relative w-full max-w-sm lg:max-w-md" @click.away="open = false">
                                <div class="relative flex items-center">
                                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-400 absolute left-3.5 pointer-events-none shrink-0" style="width: 16px; height: 16px; min-width: 16px; max-width: 16px; min-height: 16px; max-height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input type="text" 
                                           x-ref="searchInput"
                                           x-model="query"
                                           @input.debounce.250ms="search()"
                                           @focus="if(query.length >= 2) open = true"
                                           @keydown.arrow-down.prevent="navigate(1)"
                                           @keydown.arrow-up.prevent="navigate(-1)"
                                           @keydown.enter.prevent="selectActive()"
                                           placeholder="Search leads, jobs, quotes... (Ctrl+K)" 
                                           class="w-full pl-10 pr-9 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl focus:bg-white dark:focus:bg-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 shadow-2xs transition-all outline-none">
                                    
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
                                     class="absolute left-0 right-0 mt-2 w-full max-h-96 overflow-y-auto bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl py-2 z-50 divide-y divide-slate-100 dark:divide-slate-800"
                                     style="display: none;">
                                    
                                    {{-- Results list --}}
                                    <template x-if="results.length > 0">
                                        <div class="py-1">
                                            <template x-for="(item, index) in results" :key="index">
                                                <a :href="item.url" 
                                                   :class="selectedIndex === index ? 'bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-amber-400' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                                                   class="flex items-start gap-3 px-3.5 py-2.5 transition text-left group block">
                                                    <div class="mt-0.5 w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center shrink-0 group-hover:bg-blue-100 dark:group-hover:bg-blue-950 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                                        <span class="text-xs font-bold" x-text="item.icon === 'user' ? '👤' : (item.icon === 'document' ? '📄' : (item.icon === 'wrench' ? '🔧' : (item.icon === 'ticket' ? '🎫' : (item.icon === 'camera' ? '📹' : (item.icon === 'receipt' ? '🧾' : '📦')))))"></span>
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
                                            <p class="text-2xl mb-1">🔍</p>
                                            <p class="text-xs font-bold text-slate-700 dark:text-slate-200">No matches found</p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">No leads, jobs, quotes or equipment matching "<span class="font-semibold text-slate-800 dark:text-slate-200" x-text="query"></span>"</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Right side: Quick Action, Theme Toggle, Notification Bell & User Dropdown --}}
                        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                            
                            {{-- Mobile Omnisearch Trigger Button (Icon button on mobile) --}}
                            <button @click="mobileSearchOpen = true; $nextTick(() => { $refs.mobileSearchInput.focus(); if (query.trim().length >= 2) open = true; })" 
                                    type="button"
                                    class="md:hidden p-2 rounded-xl text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition shrink-0 cursor-pointer"
                                    title="Search (leads, jobs, quotes)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>

                            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                {{-- Quick Create Dropdown for Admin/Staff --}}
                                <div x-data="{ open: false }" class="relative shrink-0">
                                    <button @click="open = !open" 
                                            class="crm-quick-action-btn inline-flex items-center gap-1.5 px-3 sm:px-3.5 py-2 rounded-xl font-bold text-sm transition shadow-2xs cursor-pointer"
                                            style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.12); color: var(--crm-accent, #2563eb); border: 1px solid rgba(var(--crm-accent-rgb, 37, 99, 235), 0.3);">
                                        <svg class="w-3.5 h-3.5" style="color: var(--crm-accent, #2563eb);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        <span class="hidden sm:inline">Quick Action</span>
                                        <svg class="w-3 h-3" style="color: var(--crm-accent, #2563eb);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    </button>

                                    <div x-show="open" 
                                         @click.away="open = false" 
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         class="absolute right-0 mt-2 w-60 bg-white dark:bg-[#0f172a] rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 py-2 z-50 text-sm font-semibold text-slate-700 dark:text-slate-200"
                                         style="display: none;">
                                        
                                        <a href="{{ route('leads.create') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 transition">
                                            <span class="text-blue-600 font-bold">+</span> New Customer Lead
                                        </a>
                                        <a href="{{ route('quotations.create-general') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-emerald-600 transition">
                                            <span class="text-emerald-600 font-bold">+</span> Generate Quotation
                                        </a>
                                        <a href="{{ route('service-tickets.create') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-rose-600 transition">
                                            <span class="text-rose-600 font-bold">+</span> Log Service Ticket
                                        </a>
                                        <a href="{{ route('purchase-orders.create') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-purple-600 transition">
                                            <span class="text-purple-600 font-bold">+</span> New Purchase Order
                                        </a>
                                        @if(auth()->user()->isAdmin())
                                        <a href="{{ route('products.create') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-amber-600 transition border-t border-slate-100 dark:border-slate-800">
                                            <span class="text-amber-500 font-bold">+</span> New Product / Pricing
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            @elseif(auth()->user()->isCustomer())
                                {{-- Quick Report Button for Customer (Dynamic Accent) --}}
                                <a href="{{ route('portal.tickets.create') }}" 
                                   class="crm-customer-header-action inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-white font-bold text-sm transition shadow-sm"
                                   style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb);">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    <span class="hidden sm:inline">Report Issue</span>
                                </a>
                            @endif

                            {{-- Clean Dark / Light Mode Toggle Button (Matching User Design) --}}
                            <div x-data="{ 
                                    isDark: document.documentElement.classList.contains('dark'),
                                    init() {
                                        const observer = new MutationObserver(() => {
                                            this.isDark = document.documentElement.classList.contains('dark');
                                        });
                                        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                                    },
                                    toggle() {
                                        this.isDark = !this.isDark;
                                        if (this.isDark) {
                                            document.documentElement.classList.add('dark');
                                        } else {
                                            document.documentElement.classList.remove('dark');
                                        }
                                        const mode = this.isDark ? 'dark' : 'day';
                                        try {
                                            const modeKey = window.crmModeStorageKey || 'crm_mode';
                                            localStorage.setItem(modeKey, mode);
                                        } catch(e) {}
                                        window.dispatchEvent(new CustomEvent('crm-mode-changed', { detail: { mode } }));
                                        fetch('{{ route('profile.theme') }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            },
                                            body: JSON.stringify({ mode: mode })
                                        }).catch(() => {});
                                    }
                                }" 
                                class="shrink-0">
                                <button type="button" 
                                        @click="toggle()" 
                                        class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-200 cursor-pointer shadow-xs bg-[#0F172A] hover:bg-[#1E293B] border border-slate-700/80 dark:bg-[#0F172A] dark:hover:bg-[#1E293B] dark:border-slate-700/80 text-amber-400 hover:scale-105 active:scale-95"
                                        :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
                                        aria-label="Toggle Dark/Light Mode">
                                    {{-- Sun icon in Dark Mode (Golden sun with central disc and 8 rays, matching user's image) --}}
                                    <template x-if="isDark">
                                        <svg class="w-4 h-4 text-amber-400 drop-shadow-[0_0_6px_rgba(251,191,36,0.6)]" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="12" cy="12" r="5" />
                                            <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                        </svg>
                                    </template>
                                    {{-- Moon icon in Light Mode --}}
                                    <template x-if="!isDark">
                                        <svg class="w-4 h-4 text-slate-300 hover:text-amber-400 transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                                        </svg>
                                    </template>
                                </button>
                            </div>

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
                            <div x-data="{ notifOpen: false }" class="relative">
                                <button type="button"
                                        @click="notifOpen = !notifOpen"
                                        class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition relative"
                                        title="Notifications">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    @if($notifTotalBadge > 0)
                                        <span class="absolute top-1 right-1 w-4 h-4 bg-rose-500 text-white rounded-full text-[9px] font-bold flex items-center justify-center ring-2 ring-white dark:ring-slate-900 animate-pulse">
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
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-600 dark:bg-rose-950 dark:text-rose-400">
                                                {{ $notifTotalBadge }} active
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-400">All clear ✓</span>
                                        @endif
                                    </div>

                                    {{-- Summary Badges --}}
                                    <div class="px-4 py-3 grid grid-cols-3 gap-2 border-b border-slate-100 dark:border-slate-800">
                                        <a href="{{ $card1Route }}" class="flex flex-col items-center p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition cursor-pointer">
                                            <span class="text-lg font-extrabold text-amber-600 dark:text-amber-400 leading-none">{{ $card1Count }}</span>
                                            <span class="text-[10px] font-semibold text-amber-700 dark:text-amber-300 mt-0.5 text-center leading-tight">{{ $card1Label }}</span>
                                        </a>
                                        @if($card2Color === 'rose')
                                            <a href="{{ $card2Route }}" class="flex flex-col items-center p-2 rounded-xl bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/40 transition cursor-pointer">
                                                <span class="text-lg font-extrabold text-rose-600 dark:text-rose-400 leading-none">{{ $card2Count }}</span>
                                                <span class="text-[10px] font-semibold text-rose-700 dark:text-rose-300 mt-0.5 text-center leading-tight">{{ $card2Label }}</span>
                                            </a>
                                        @else
                                            <a href="{{ $card2Route }}" class="flex flex-col items-center p-2 rounded-xl bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition cursor-pointer">
                                                <span class="text-lg font-extrabold text-blue-600 dark:text-blue-400 leading-none">{{ $card2Count }}</span>
                                                <span class="text-[10px] font-semibold text-blue-700 dark:text-blue-300 mt-0.5 text-center leading-tight">{{ $card2Label }}</span>
                                            </a>
                                        @endif
                                        @if($card3Color === 'emerald')
                                            <a href="{{ $card3Route }}" class="flex flex-col items-center p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition cursor-pointer">
                                                <span class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400 leading-none">{{ $card3Count }}</span>
                                                <span class="text-[10px] font-semibold text-emerald-700 dark:text-emerald-300 mt-0.5 text-center leading-tight">{{ $card3Label }}</span>
                                            </a>
                                        @else
                                            <a href="{{ $card3Route }}" class="flex flex-col items-center p-2 rounded-xl bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition cursor-pointer">
                                                <span class="text-lg font-extrabold text-blue-600 dark:text-blue-400 leading-none">{{ $card3Count }}</span>
                                                <span class="text-[10px] font-semibold text-blue-700 dark:text-blue-300 mt-0.5 text-center leading-tight">{{ $card3Label }}</span>
                                            </a>
                                        @endif
                                    </div>

                                    {{-- Recent Notification Logs --}}
                                    <div class="max-h-60 overflow-y-auto">
                                        @forelse($recentAlerts as $alert)
                                            <div class="px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition border-b border-slate-50 dark:border-slate-800/50 last:border-0">
                                                <div class="flex items-start gap-2.5">
                                                    {{-- Channel Icon --}}
                                                    <span class="mt-0.5 text-base leading-none flex-shrink-0">
                                                        @if($alert->channel === 'whatsapp') 💬
                                                        @elseif($alert->channel === 'email') 📧
                                                        @elseif($alert->channel === 'sms') 📱
                                                        @else 🔔 @endif
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
                                                <p class="text-2xl mb-1">🔔</p>
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

                            {{-- User Avatar Button (Initial Badge Only - Click reveals full profile data) --}}
                            <div x-data="{ open: false }" class="relative shrink-0 ml-1.5 mr-2">
                                <button @click="open = !open" 
                                        type="button"
                                        class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-base text-white shadow-md transition-all duration-200 hover:scale-105 active:scale-95 focus:outline-none ring-2 ring-slate-200/90 dark:ring-slate-700/80 cursor-pointer select-none"
                                        style="background-color: var(--crm-accent, #2563eb);"
                                        aria-label="User account menu"
                                        aria-expanded="false"
                                        :aria-expanded="open.toString()"
                                        title="{{ Auth::user()->name }} (Click to view profile)">
                                    <span class="drop-shadow-xs">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                </button>

                                <div x-show="open" 
                                     @click.away="open = false" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-1.5rem)] bg-white dark:bg-[#0f172a] rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 py-2.5 z-50 text-sm font-semibold text-slate-700 dark:text-slate-200"
                                     style="display: none;">
                                    
                                    {{-- User info header --}}
                                    <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center gap-2.5 mb-1.5">
                                            <div class="w-8 h-8 rounded-full text-white flex items-center justify-center font-bold text-xs shrink-0 crm-user-avatar" style="background-color: var(--crm-accent, #2563eb);">
                                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-bold text-slate-900 dark:text-white truncate font-heading text-sm">{{ Auth::user()->name }}</p>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                                            </div>
                                        </div>
                                        <span class="inline-block text-[9px] uppercase font-bold px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                            {{ ucfirst(Auth::user()->role) }}
                                        </span>
                                    </div>

                                    {{-- Theme & Color Palette Settings embedded in User Profile --}}
                                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60">
                                        <x-zoho-theme-customizer :inline="true" :userId="auth()->id()" :userAccent="auth()->user()->theme_accent" :userMode="auth()->user()->theme_mode" :userStyle="auth()->user()->theme_style" />
                                    </div>

                                    {{-- Quick Links --}}
                                    <div class="py-1">
                                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 transition">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            Account &amp; Security Settings
                                        </a>
                                        <a href="{{ route('home') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 transition">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                            Storefront View
                                        </a>
                                        <button @click="shortcutsModalOpen = true; open = false" type="button" class="w-full text-left flex items-center justify-between px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 transition cursor-pointer">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                <span>Keyboard Shortcuts</span>
                                            </div>
                                            <kbd class="px-1.5 py-0.2 text-[9px] font-mono font-bold bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-slate-500">?</kbd>
                                        </button>
                                    </div>

                                    {{-- Logout --}}
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 font-semibold transition border-t border-slate-100 dark:border-slate-800 cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
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
                                       class="w-full pl-9 pr-8 py-2 text-xs bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl focus:bg-white dark:focus:bg-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 shadow-2xs">
                                
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
                                                    <span class="text-xs font-bold" x-text="item.icon === 'user' ? '👤' : (item.icon === 'document' ? '📄' : (item.icon === 'wrench' ? '🔧' : (item.icon === 'ticket' ? '🎫' : (item.icon === 'camera' ? '📹' : (item.icon === 'receipt' ? '🧾' : '📦')))))"></span>
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
                                        <p class="text-2xl mb-1">🔍</p>
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
                                        <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-600 flex items-center justify-center font-bold text-xs">🕒</span>
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

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- LAPTOP VIEW: PRODUCTIVITY KEYBOARD SHORTCUTS MODAL (Ctrl+/ or ?)  --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div x-show="shortcutsModalOpen" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
                 style="display: none;">
                
                <div @click.away="shortcutsModalOpen = false" 
                     class="w-full max-w-lg bg-white dark:bg-[#0f172a] rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all">
                    
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 font-bold">⌨️</span>
                            <div>
                                <h3 class="font-extrabold text-slate-900 dark:text-white text-sm font-heading">Laptop & Desktop Productivity Shortcuts</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Power navigation tools for fast keyboard-first workflow</p>
                            </div>
                        </div>
                        <button @click="shortcutsModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 text-xs">
                        <div class="space-y-2.5">
                            <h4 class="text-[10px] uppercase font-bold tracking-wider text-slate-500 dark:text-slate-400">Navigation & Views</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/70">
                                    <span class="text-slate-700 dark:text-slate-100 font-medium">Toggle Sidebar Dock</span>
                                    <kbd class="px-2 py-0.5 font-mono text-[10px] font-bold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded shadow-2xs">Ctrl + B</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/70">
                                    <span class="text-slate-700 dark:text-slate-100 font-medium">Global Omnisearch</span>
                                    <kbd class="px-2 py-0.5 font-mono text-[10px] font-bold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded shadow-2xs">Ctrl + K</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/70">
                                    <span class="text-slate-700 dark:text-slate-100 font-medium">Dark / Light Mode</span>
                                    <kbd class="px-2 py-0.5 font-mono text-[10px] font-bold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded shadow-2xs">Alt + T</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/70">
                                    <span class="text-slate-700 dark:text-slate-100 font-medium">Shortcuts Cheatsheet</span>
                                    <kbd class="px-2 py-0.5 font-mono text-[10px] font-bold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded shadow-2xs">?</kbd>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2.5">
                            <h4 class="text-[10px] uppercase font-bold tracking-wider text-slate-500 dark:text-slate-400">Fast Access Routes</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('dashboard') }}" class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/70 hover:border-blue-400 dark:hover:border-blue-500 transition">
                                    <span class="text-slate-700 dark:text-slate-100 font-medium">Overview Dashboard</span>
                                    <kbd class="px-2 py-0.5 font-mono text-[10px] font-bold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded shadow-2xs">Alt + D</kbd>
                                </a>
                                <a href="{{ route('attendance.index') }}" class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/70 hover:border-blue-400 dark:hover:border-blue-500 transition">
                                    <span class="text-slate-700 dark:text-slate-100 font-medium">Attendance & Shifts</span>
                                    <kbd class="px-2 py-0.5 font-mono text-[10px] font-bold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded shadow-2xs">Alt + A</kbd>
                                </a>
                                <a href="{{ route('leads.index') }}" class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/70 hover:border-blue-400 dark:hover:border-blue-500 transition">
                                    <span class="text-slate-700 dark:text-slate-100 font-medium">Leads Pipeline</span>
                                    <kbd class="px-2 py-0.5 font-mono text-[10px] font-bold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded shadow-2xs">Alt + L</kbd>
                                </a>
                                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/70">
                                    <span class="text-slate-700 dark:text-slate-100 font-medium">Close Active Window</span>
                                    <kbd class="px-2 py-0.5 font-mono text-[10px] font-bold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded shadow-2xs">Esc</kbd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-3 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500 dark:text-slate-400">💡 Tip: Press <strong class="text-slate-700 dark:text-slate-300 font-semibold">Ctrl + B</strong> anytime to toggle between full sidebar and mini dock on laptops.</span>
                        <button @click="shortcutsModalOpen = false" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition cursor-pointer">Close</button>
                    </div>
                </div>
            </div>

    </body>
</html>
