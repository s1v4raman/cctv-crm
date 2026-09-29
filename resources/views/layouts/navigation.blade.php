{{-- SecureVision Modern SaaS Sidebar Navigation Component --}}
<aside class="w-full h-full flex flex-col justify-between shrink-0 bg-white dark:bg-[#0f172a] text-slate-700 dark:text-slate-200 select-none overflow-hidden transition-all duration-300">
    
    {{-- Sidebar Top: Brand Logo matching Mockup --}}
    <div class="h-16 shrink-0 flex items-center justify-between px-4 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-[#0f172a] transition-colors duration-200">
        <a href="{{ auth()->user()->role === 'technician' ? route('technician.dashboard') : (auth()->user()->isCustomer() ? route('portal.dashboard') : route('dashboard')) }}" class="flex items-center gap-3 group overflow-hidden">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 p-0.5 shadow-xs group-hover:scale-105 transition-transform shrink-0">
                <div class="w-full h-full bg-blue-600 rounded-[10px] flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="min-w-0">
                <span class="text-lg font-black tracking-tight text-slate-900 dark:text-white font-heading truncate block leading-tight">CRM</span>
                <span class="block text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 truncate">SecureVision</span>
            </div>
        </a>
        
        {{-- Close mobile drawer button --}}
        <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Navigation Menu Items: Scrollable flex-1 container for all modules --}}
    <nav class="flex-1 min-h-0 overflow-y-auto px-3 py-3 space-y-4 custom-sidebar-scroll">
        
        {{-- Technician Navigation --}}
        @if(auth()->user()->role === 'technician')
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Technician Portal</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('technician.dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('technician.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('technician.*') ? '!text-white' : 'text-blue-500 dark:text-blue-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('technician.*') ? '!text-white font-bold' : '' }}">Field Work Orders</span>
                    </a>
                </div>
            </div>

        {{-- Customer Navigation --}}
        @elseif(auth()->user()->isCustomer())
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Client Self-Service</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('portal.dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.dashboard') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.dashboard') ? '!text-white' : 'text-blue-500 dark:text-blue-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('portal.dashboard') ? '!text-white font-bold' : '' }}">Overview Dashboard</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('portal.equipment') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.equipment') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.equipment') ? '!text-white' : 'text-emerald-500 dark:text-emerald-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('portal.equipment') ? '!text-white font-bold' : '' }}">My Cameras & Assets</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('portal.amc') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.amc') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.amc') ? '!text-white' : 'text-purple-500 dark:text-purple-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('portal.amc') ? '!text-white font-bold' : '' }}">AMC & Maintenance</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('portal.tickets') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.tickets*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.tickets*') ? '!text-white' : 'text-rose-500 dark:text-rose-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('portal.tickets*') ? '!text-white font-bold' : '' }}">Service & Tickets</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('portal.invoices') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.invoices*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.invoices*') ? '!text-white' : 'text-teal-500 dark:text-teal-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('portal.invoices*') ? '!text-white font-bold' : '' }}">Invoices & Payments</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('portal.quotations') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.quotations*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.quotations*') ? '!text-white' : 'text-amber-500 dark:text-amber-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('portal.quotations*') ? '!text-white font-bold' : '' }}">Quotations & Proposals</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('portal.surveys') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.surveys*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.surveys*') ? '!text-white' : 'text-cyan-500 dark:text-cyan-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('portal.surveys*') ? '!text-white font-bold' : '' }}">Site Surveys</span>
                    </a>
                </div>
            </div>

        {{-- Admin & Staff Comprehensive Navigation --}}
        @else
            
            {{-- 1. Main Overview --}}
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Overview</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('dashboard') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('dashboard') ? '!text-white' : 'text-blue-500 dark:text-blue-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('dashboard') ? '!text-white font-bold' : '' }}">Dashboard</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('calendar.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('calendar.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('calendar.*') ? '!text-white' : 'text-indigo-500 dark:text-indigo-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('calendar.*') ? '!text-white font-bold' : '' }}">Calendar & Dispatch</span>
                    </a>
                </div>
            </div>

            {{-- 2. Sales & Pipeline --}}
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Sales & Pipeline</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('leads.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('leads.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('leads.*') ? '!text-white' : 'text-violet-500 dark:text-violet-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('leads.*') ? '!text-white font-bold' : '' }}">Leads & Inquiries</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('site-surveys.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('site-surveys.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('site-surveys.*') ? '!text-white' : 'text-cyan-500 dark:text-cyan-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('site-surveys.*') ? '!text-white font-bold' : '' }}">Site Surveys</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('quotations.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('quotations.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('quotations.*') ? '!text-white' : 'text-amber-500 dark:text-amber-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('quotations.*') ? '!text-white font-bold' : '' }}">Deals & Quotations</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('estimator.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('estimator.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('estimator.*') ? '!text-white' : 'text-sky-500 dark:text-sky-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('estimator.*') ? '!text-white font-bold' : '' }}">CCTV Storage Estimator</span>
                    </a>
                </div>
            </div>

            {{-- 3. Field Operations & Support --}}
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Field Operations</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('jobs.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('jobs.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('jobs.*') ? '!text-white' : 'text-blue-500 dark:text-blue-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('jobs.*') ? '!text-white font-bold' : '' }}">Tasks & Field Jobs</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('service-tickets.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('service-tickets.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('service-tickets.*') ? '!text-white' : 'text-rose-500 dark:text-rose-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('service-tickets.*') ? '!text-white font-bold' : '' }}">Support Tickets (SLA)</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('jcr.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('jcr.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('jcr.*') ? '!text-white' : 'text-teal-500 dark:text-teal-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('jcr.*') ? '!text-white font-bold' : '' }}">Digital Reports (JCR)</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('amcs.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('amcs.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('amcs.*') ? '!text-white' : 'text-purple-500 dark:text-purple-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('amcs.*') ? '!text-white font-bold' : '' }}">AMC Contracts</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('equipment.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('equipment.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('equipment.*') ? '!text-white' : 'text-emerald-500 dark:text-emerald-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('equipment.*') ? '!text-white font-bold' : '' }}">Cameras & Assets</span>
                    </a>
                </div>
            </div>

            {{-- 4. Inventory & Procurement --}}
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Inventory & Procurement</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('inventory.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('inventory.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('inventory.*') ? '!text-white' : 'text-amber-500 dark:text-amber-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('inventory.*') ? '!text-white font-bold' : '' }}">Warehouse Inventory</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('purchase-orders.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('purchase-orders.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('purchase-orders.*') ? '!text-white' : 'text-indigo-500 dark:text-indigo-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('purchase-orders.*') ? '!text-white font-bold' : '' }}">Purchase Orders (PO)</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('suppliers.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('suppliers.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('suppliers.*') ? '!text-white' : 'text-blue-500 dark:text-blue-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('suppliers.*') ? '!text-white font-bold' : '' }}">Suppliers & Vendors</span>
                    </a>

                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('rma.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('rma.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('rma.*') ? '!text-white' : 'text-orange-500 dark:text-orange-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('rma.*') ? '!text-white font-bold' : '' }}">RMA & Warranty Claims</span>
                    </a>
                </div>
            </div>

            {{-- 5. Employee & Workforce (Clean Single Link) --}}
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Workspace</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('employee.hub') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('employee.*') || request()->routeIs('attendance.*') || request()->routeIs('leaves.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}"
                       title="Employee & Workspace">
                        <img src="{{ asset('images/employee-workspace-icon.svg') }}" alt="Employee & Workspace" class="w-5 h-5 rounded-md shadow-xs shrink-0 object-contain">
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('employee.*') || request()->routeIs('attendance.*') ? '!text-white font-bold' : '' }}">Employee & Workspace</span>
                    </a>
                </div>
            </div>

            {{-- 6. Finance & Accounting (Clean Single Link) --}}
            <div>
                <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Finance</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                <div class="space-y-1">
                    <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('finance.hub') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('finance.*') || request()->routeIs('invoices.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}"
                       title="Finance & Accounting">
                        <img src="{{ asset('images/finance-accounting-icon.svg') }}" alt="Finance & Accounting" class="w-5 h-5 rounded-md shadow-xs shrink-0 object-contain">
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('finance.*') || request()->routeIs('invoices.*') ? '!text-white font-bold' : '' }}">Finance & Accounting</span>
                    </a>
                </div>
            </div>

            {{-- 7. Analytics & Intelligence (Admin Only) --}}
            @if(auth()->user()->isAdmin())
                <div>
                    <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Analytics & Automation</div>
                <div :class="sidebarCollapsed ? 'hidden lg:block' : 'hidden'" class="my-2 border-t border-slate-100 dark:border-slate-800"></div>
                    <div class="space-y-1">
                        <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('alerts.index') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('alerts.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('alerts.*') ? '!text-white' : 'text-amber-500 dark:text-amber-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('alerts.*') ? '!text-white font-bold' : '' }}">Automated Alerts & SMS</span>
                        </a>

                        <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('analytics.index') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('analytics.index') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('analytics.index') ? '!text-white' : 'text-blue-500 dark:text-blue-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('analytics.index') ? '!text-white font-bold' : '' }}">Executive Analytics</span>
                        </a>

                        <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('analytics.technicians') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('analytics.technicians*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('analytics.technicians*') ? '!text-white' : 'text-purple-500 dark:text-purple-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('analytics.technicians*') ? '!text-white font-bold' : '' }}">Technician Performance</span>
                        </a>

                        <a :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" href="{{ route('admin.users.index') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('admin.users.*') ? '!bg-blue-600 !text-white shadow-md shadow-blue-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.users.*') ? '!text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="{{ request()->routeIs('admin.users.*') ? '!text-white font-bold' : '' }}">User Accounts &amp; Settings</span>
                        </a>
                    </div>
                </div>
            @endif
        @endif

    </nav>

    {{-- Sidebar Bottom: Compact User Profile & Quick Logout --}}
    <div class="shrink-0 p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 transition-colors">
        <div class="flex items-center justify-between gap-2" :class="sidebarCollapsed ? 'lg:justify-center' : ''">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs ring-2 ring-white dark:ring-slate-900">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0 truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">
                    <span class="block text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ Auth::user()->name }}</span>
                    <span class="block text-[10px] text-slate-400 capitalize font-medium">{{ Auth::user()->role }}</span>
                </div>
            </div>

            <div class="flex items-center gap-1 shrink-0" :class="sidebarCollapsed ? 'lg:hidden' : ''">
                <a href="{{ route('profile.edit') }}" title="Profile Settings" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Log Out" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Desktop Collapse Footer Bar (Laptop View) --}}
    <div class="hidden lg:block shrink-0 px-3 py-2 border-t border-slate-100 dark:border-slate-800 text-center">
        <button @click="toggleSidebarCollapsed()" 
                type="button" 
                class="w-full flex items-center gap-2 p-1.5 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition text-xs font-semibold cursor-pointer"
                :class="sidebarCollapsed ? 'justify-center' : 'justify-between'"
                :title="sidebarCollapsed ? 'Expand Sidebar to Full View (Ctrl+B)' : 'Collapse Sidebar to Dock (Ctrl+B)'">
            <span :class="sidebarCollapsed ? 'hidden' : 'flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400'">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
                Collapse
            </span>
            <span :class="sidebarCollapsed ? 'hidden' : 'text-[10px] text-slate-400 font-mono bg-slate-100 dark:bg-slate-800 px-1 py-0.2 rounded'">Ctrl+B</span>
            <svg :class="sidebarCollapsed ? 'w-4 h-4 text-slate-400 rotate-180' : 'hidden'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
        </button>
    </div>

</aside>