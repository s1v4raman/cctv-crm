{{-- SecureVision Modern SaaS Sidebar Navigation Component --}}
<aside class="w-full h-full flex flex-col justify-between shrink-0 bg-[var(--card-bg)] text-[var(--text-main)] select-none overflow-hidden transition-all duration-300">
    
    {{-- Sidebar Top: Brand Logo matching 56px fixed frame --}}
    <div class="crm-sidebar-header h-[56px] min-h-[56px] max-h-[56px] shrink-0 flex items-center justify-between px-4 border-b border-[var(--card-border)] bg-[var(--card-bg)] text-[var(--text-main)] transition-colors duration-200">
        <a href="{{ auth()->user()->role === 'technician' ? route('technician.dashboard') : route('dashboard') }}" class="flex items-center gap-2.5 group overflow-hidden">
            <img src="{{ asset('logos/logo_' . ltrim(auth()->user()->theme_accent ?? '2563eb', '#') . '.png') }}" alt="Precision IT Systems" class="crm-brand-logo h-8 w-8 object-contain group-hover:scale-105 transition-transform shrink-0" onerror="this.src='{{ asset('logo.png') }}'">
            <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="min-w-0">
                <span class="block text-sm font-bold tracking-tight text-[var(--text-main)] truncate">Precision IT</span>
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-[var(--text-muted)] font-mono truncate">CRM SUITE</span>
            </div>
        </a>
        
        {{-- Close mobile drawer button --}}
        <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Navigation Menu Items: Scrollable flex-1 container for all modules --}}
    <nav class="flex-1 min-h-0 overflow-y-auto px-3.5 py-3.5 space-y-4 custom-sidebar-scroll">
        
        {{-- Technician Navigation --}}
        @if(auth()->user()->role === 'technician')
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Technician Portal</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    {{-- Field Work Orders (Menu/Tasks list icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('technician.dashboard') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('technician.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('technician.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="6" y2="6"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="12" y2="12"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="18" y2="18"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Field Work Orders</span>
                    </a>

                    {{-- Projects & Sites (Briefcase icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('projects.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('projects.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('projects.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect width="20" height="14" x="2" y="7" rx="3"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="2" x2="22" y1="13" y2="13"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Projects &amp; Sites</span>
                    </a>

                    {{-- Installation Sites (Location pin icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('sites.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('sites.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('sites.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.5-5.5-7-9.5-7-13a7 7 0 0 1 14 0c0 3.5-2.5 7.5-7 13z"/>
                            <circle cx="12" cy="8" r="2.5"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Installation Sites</span>
                    </a>

                    {{-- Daily Wage Workers (Team/Users icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('workers.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('workers.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('workers.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Daily Wage Workers</span>
                    </a>
                </div>
            </div>

        {{-- Admin & Staff Comprehensive Navigation --}}
        @else
            
            {{-- 1. Main Overview --}}
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Overview</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    {{-- Dashboard (Home icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('dashboard') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('dashboard') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5L12 3l9 7.5V20a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-9.5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 22V12h6v10"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Dashboard</span>
                    </a>

                    {{-- Calendar & Dispatch (Calendar with dots icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('calendar.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('calendar.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('calendar.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect width="18" height="18" x="3" y="4" rx="3" ry="3"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="16" x2="16" y1="2" y2="6"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="8" x2="8" y1="2" y2="6"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="3" x2="21" y1="10" y2="10"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01M16 17h.01"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Calendar & Dispatch</span>
                    </a>
                </div>
            </div>

            {{-- 2. Sales & Pipeline --}}
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Sales & Pipeline</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    {{-- Leads & Inquiries (User outline icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('leads.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('leads.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('leads.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="7" r="4.5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.5 21a6.5 6.5 0 0 1 13 0"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Leads & Inquiries</span>
                    </a>

                    {{-- Site Surveys (Location pin icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('site-surveys.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('site-surveys.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('site-surveys.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.5-5.5-7-9.5-7-13a7 7 0 0 1 14 0c0 3.5-2.5 7.5-7 13z"/>
                            <circle cx="12" cy="8" r="2.5"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Site Surveys</span>
                    </a>

                    {{-- Deals & Quotations (Edit/Pencil note icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('quotations.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('quotations.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('quotations.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 4H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h13a2 2 0 0 0 2-2v-6"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Deals & Quotations</span>
                    </a>

                    {{-- CCTV Storage Estimator (Lightbulb icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('estimator.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('estimator.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('estimator.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18h6m-4 4h2M12 2a7 7 0 0 0-4.5 12.35c.95.83 1.5 2 1.5 3.15h6c0-1.15.55-2.32 1.5-3.15A7 7 0 0 0 12 2z"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">CCTV Storage Estimator</span>
                    </a>
                </div>
            </div>

            {{-- 3. Field Operations & Support --}}
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Field Operations</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    {{-- Projects & Sites (Briefcase icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('projects.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('projects.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('projects.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect width="20" height="14" x="2" y="7" rx="3"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="2" x2="22" y1="13" y2="13"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Projects &amp; Sites</span>
                    </a>

                    {{-- Installation Sites (Location pin icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('sites.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('sites.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('sites.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.5-5.5-7-9.5-7-13a7 7 0 0 1 14 0c0 3.5-2.5 7.5-7 13z"/>
                            <circle cx="12" cy="8" r="2.5"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Installation Sites</span>
                    </a>

                    {{-- Workers & Daily Wages (Team/Users icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('workers.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('workers.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('workers.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Workers &amp; Daily Wages</span>
                    </a>

                    @if(auth()->user()->isAdmin())
                    {{-- Sunday Payday Hub (Download icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('wages.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('wages.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('wages.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline stroke-linecap="round" stroke-linejoin="round" points="7 10 12 15 17 10"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="12" x2="12" y1="15" y2="3"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Sunday Payday Hub</span>
                    </a>
                    @endif

                    {{-- Ready to Invoice (DCs) (Document icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('documents.to-invoice') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('documents.to-invoice') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('documents.to-invoice') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline stroke-linecap="round" stroke-linejoin="round" points="14 2 14 8 20 8"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="16" x2="8" y1="13" y2="13"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="16" x2="8" y1="17" y2="17"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Ready to Invoice (DCs)</span>
                    </a>

                    {{-- Tasks & Field Jobs (Menu/Checklist lines icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('jobs.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('jobs.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('jobs.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="6" y2="6"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="12" y2="12"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="18" y2="18"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Tasks & Field Jobs</span>
                    </a>

                    {{-- Cameras & Assets (Eye icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('equipment.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('equipment.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('equipment.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                            <circle cx="12" cy="12" r="3.2"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Cameras & Assets</span>
                    </a>
                </div>
            </div>

            {{-- 4. Inventory & Procurement --}}
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Inventory & Procurement</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    @if(auth()->user()->isAdmin())
                    {{-- Products & Pricing (Bento 4-squares icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('products.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('products.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}"
                       title="Product Catalogue & Pricing (Admin Only)">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('products.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect width="7" height="7" x="3" y="3" rx="2"/>
                            <rect width="7" height="7" x="14" y="3" rx="2"/>
                            <rect width="7" height="7" x="14" y="14" rx="2"/>
                            <rect width="7" height="7" x="3" y="14" rx="2"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Products &amp; Pricing</span>
                    </a>
                    @endif

                    {{-- Purchase Orders (PO) (Download icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('purchase-orders.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('purchase-orders.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('purchase-orders.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline stroke-linecap="round" stroke-linejoin="round" points="7 10 12 15 17 10"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="12" x2="12" y1="15" y2="3"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Purchase Orders (PO)</span>
                    </a>

                    {{-- Suppliers & Vendors (Globe icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('suppliers.index') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('suppliers.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('suppliers.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="2" x2="22" y1="12" y2="12"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Suppliers & Vendors</span>
                    </a>
                </div>
            </div>

            {{-- 5. Employee & Workforce (Clean Single Link) --}}
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Workspace</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    {{-- Employee & Workspace (User outline icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('employee.hub') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('employee.*') || request()->routeIs('attendance.*') || request()->routeIs('leaves.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}"
                       title="Employee & Workspace">
                        <svg class="w-5 h-5 shrink-0 {{ (request()->routeIs('employee.*') || request()->routeIs('attendance.*') || request()->routeIs('leaves.*')) ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="7" r="4.5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.5 21a6.5 6.5 0 0 1 13 0"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Employee & Workspace</span>
                    </a>
                </div>
            </div>

            {{-- 6. Finance & Accounting (Clean Single Link) --}}
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Finance</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    {{-- Finance & Accounting (Briefcase icon) --}}
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('finance.hub') }}" 
                       class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('finance.*') || request()->routeIs('invoices.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}"
                       title="Finance & Accounting">
                        <svg class="w-5 h-5 shrink-0 {{ (request()->routeIs('finance.*') || request()->routeIs('invoices.*')) ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect width="20" height="14" x="2" y="7" rx="3"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                            <line stroke-linecap="round" stroke-linejoin="round" x1="2" x2="22" y1="13" y2="13"/>
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Finance & Accounting</span>
                    </a>
                </div>
            </div>

            {{-- 7. Analytics & Intelligence (Admin Only) --}}
            @if(auth()->user()->isAdmin())
                <div>
                    <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Analytics & Automation</div>
                    <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                    <div class="space-y-1">
                        {{-- Automated Alerts & SMS (Megaphone icon) --}}
                        <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('alerts.index') }}" 
                           class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('alerts.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('alerts.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3 11 18-5v12L3 14v-3z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>
                            </svg>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Automated Alerts & SMS</span>
                        </a>

                        {{-- Executive Analytics (Sliders/Controls icon) --}}
                        <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('analytics.index') }}" 
                           class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('analytics.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('analytics.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="6" y2="6"/>
                                <circle cx="8" cy="6" r="2.5"/>
                                <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="12" y2="12"/>
                                <circle cx="16" cy="12" r="2.5"/>
                                <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="18" y2="18"/>
                                <circle cx="10" cy="18" r="2.5"/>
                            </svg>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Executive Analytics</span>
                        </a>

                        {{-- User Accounts & Settings (Sliders/Controls icon) --}}
                        <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('admin.users.index') }}" 
                           class="group flex items-center gap-3 px-3 py-2 text-sm transition-colors rounded-lg {{ request()->routeIs('admin.users.*') ? 'crm-active-link bg-[#ebebed] dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-[#f1f2f4] active:bg-[#ebebed] dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.users.*') ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="6" y2="6"/>
                                <circle cx="8" cy="6" r="2.5"/>
                                <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="12" y2="12"/>
                                <circle cx="16" cy="12" r="2.5"/>
                                <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="18" y2="18"/>
                                <circle cx="10" cy="18" r="2.5"/>
                            </svg>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">User Accounts &amp; Settings</span>
                        </a>
                    </div>
                </div>
            @endif
        @endif

    </nav>

    {{-- Sidebar Bottom: Comfortable User Profile & Quick Logout --}}
    <div class="crm-sidebar-footer shrink-0 p-3.5 border-t border-[var(--card-border)] bg-[var(--card-bg)] text-[var(--text-main)] transition-colors">
        <div class="flex items-center justify-between gap-2.5" :class="sidebarCollapsed ? 'lg:justify-center' : ''">
            <div class="flex items-center gap-3 min-w-0">
                @if(Auth::user()->avatar_url)
                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-9 h-9 rounded-full object-cover shrink-0 shadow-xs ring-2 ring-white dark:ring-slate-800">
                @else
                    <div class="w-9 h-9 rounded-full text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs ring-2 ring-white dark:ring-slate-800 crm-user-avatar" style="background-color: var(--crm-accent, #2563eb);">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0 truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">
                    <span class="block text-sm font-semibold text-[var(--text-main)] truncate">{{ Auth::user()->name }}</span>
                    <span class="block text-xs text-blue-600 dark:text-blue-400 font-semibold truncate">{{ Auth::user()->role_title }}</span>
                </div>
            </div>

            <div class="flex items-center gap-1 shrink-0" :class="sidebarCollapsed ? 'lg:hidden' : ''">
                <a href="{{ route('profile.edit') }}" title="Profile Settings" class="p-2 rounded-lg text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition !bg-transparent !border-0 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="6" y2="6"/>
                        <circle cx="8" cy="6" r="2"/>
                        <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="12" y2="12"/>
                        <circle cx="16" cy="12" r="2"/>
                        <line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="18" y2="18"/>
                        <circle cx="10" cy="18" r="2"/>
                    </svg>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Log Out" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer !bg-transparent !border-0 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

</aside>