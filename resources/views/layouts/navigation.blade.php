{{-- SecureVision Modern SaaS Sidebar Navigation Component --}}
<aside class="w-64 h-full flex flex-col justify-between shrink-0 bg-white dark:bg-[#0f172a] text-slate-700 dark:text-slate-200 select-none overflow-hidden">
    
    {{-- Sidebar Top: Brand Logo matching Mockup --}}
    <div class="h-16 shrink-0 flex items-center justify-between px-5 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-[#0f172a] transition-colors duration-200">
        <a href="{{ auth()->user()->role === 'technician' ? route('technician.dashboard') : (auth()->user()->isCustomer() ? route('portal.dashboard') : route('dashboard')) }}" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 p-0.5 shadow-xs group-hover:scale-105 transition-transform">
                <div class="w-full h-full bg-blue-600 rounded-[10px] flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div>
                <span class="text-lg font-black tracking-tight text-slate-900 dark:text-white font-heading">CRM</span>
                <span class="block text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">SecureVision</span>
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
                <div class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400">Technician Portal</div>
                <div class="space-y-1">
                    <a href="{{ route('technician.dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('technician.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('technician.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                        <span>Field Work Orders</span>
                    </a>
                </div>
            </div>

        {{-- Customer Navigation --}}
        @elseif(auth()->user()->isCustomer())
            <div>
                <div class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Client Self-Service</div>
                <div class="space-y-1">
                    <a href="{{ route('portal.dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.dashboard') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.dashboard') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Overview Dashboard</span>
                    </a>

                    <a href="{{ route('portal.equipment') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.equipment') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.equipment') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span>My Cameras & Assets</span>
                    </a>

                    <a href="{{ route('portal.amc') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.amc') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.amc') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>AMC & Maintenance</span>
                    </a>

                    <a href="{{ route('portal.tickets') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.tickets*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.tickets*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Service & Tickets</span>
                    </a>

                    <a href="{{ route('portal.invoices') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.invoices*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.invoices*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        <span>Invoices & Payments</span>
                    </a>

                    <a href="{{ route('portal.quotations') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.quotations*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.quotations*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <span>Quotations & Proposals</span>
                    </a>

                    <a href="{{ route('portal.surveys') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('portal.surveys*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('portal.surveys*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Site Surveys</span>
                    </a>
                </div>
            </div>

        {{-- Admin & Staff Comprehensive Navigation --}}
        @else
            
            {{-- 1. Main Overview --}}
            <div>
                <div class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Overview</div>
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('calendar.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('calendar.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('calendar.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Calendar & Dispatch</span>
                    </a>
                </div>
            </div>

            {{-- 2. Sales & Pipeline --}}
            <div>
                <div class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Sales & Pipeline</div>
                <div class="space-y-1">
                    <a href="{{ route('leads.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('leads.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('leads.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Leads & Inquiries</span>
                    </a>

                    <a href="{{ route('site-surveys.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('site-surveys.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('site-surveys.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        <span>Site Surveys</span>
                    </a>

                    <a href="{{ route('quotations.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('quotations.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('quotations.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Deals & Quotations</span>
                    </a>

                    <a href="{{ route('estimator.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('estimator.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('estimator.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>CCTV Storage Estimator</span>
                    </a>
                </div>
            </div>

            {{-- 3. Field Operations & Support --}}
            <div>
                <div class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Field Operations</div>
                <div class="space-y-1">
                    <a href="{{ route('jobs.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('jobs.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('jobs.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span>Tasks & Field Jobs</span>
                    </a>

                    <a href="{{ route('service-tickets.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('service-tickets.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('service-tickets.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Support Tickets (SLA)</span>
                    </a>

                    <a href="{{ route('jcr.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('jcr.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('jcr.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Digital Reports (JCR)</span>
                    </a>

                    <a href="{{ route('amcs.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('amcs.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('amcs.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>AMC Contracts</span>
                    </a>

                    <a href="{{ route('equipment.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('equipment.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('equipment.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span>Cameras & Assets</span>
                    </a>
                </div>
            </div>

            {{-- 4. Inventory & Procurement --}}
            <div>
                <div class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Inventory & Procurement</div>
                <div class="space-y-1">
                    <a href="{{ route('inventory.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('inventory.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('inventory.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Warehouse Inventory</span>
                    </a>

                    <a href="{{ route('purchase-orders.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('purchase-orders.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('purchase-orders.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Purchase Orders (PO)</span>
                    </a>

                    <a href="{{ route('suppliers.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('suppliers.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('suppliers.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Suppliers & Vendors</span>
                    </a>

                    <a href="{{ route('rma.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('rma.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('rma.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>RMA & Warranty Claims</span>
                    </a>
                </div>
            </div>

            {{-- 5. Workforce & Finance --}}
            <div>
                <div class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Workforce & Finance</div>
                <div class="space-y-1">
                    <a href="{{ route('attendance.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('attendance.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('attendance.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="flex items-center justify-between w-full">
                            <span>Staff Attendance</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        </div>
                    </a>

                    <a href="{{ route('invoices.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('invoices.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('invoices.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Invoices & Billing</span>
                    </a>

                    <a href="{{ route('finance.salaries.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('finance.salaries.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('finance.salaries.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Employee Salaries</span>
                    </a>

                    <a href="{{ route('finance.payroll.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('finance.payroll.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('finance.payroll.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        <span>Payroll & Payouts</span>
                    </a>

                    <a href="{{ route('finance.analytics') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('finance.analytics') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('finance.analytics') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                        <span>Salary Burn Analytics</span>
                    </a>
                </div>
            </div>

            {{-- 6. Analytics & Intelligence --}}
            <div>
                <div class="px-3 mb-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Analytics & Automation</div>
                <div class="space-y-1">
                    <a href="{{ route('alerts.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('alerts.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('alerts.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        <span>Automated Alerts & SMS</span>
                    </a>

                    <a href="{{ route('analytics.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('analytics.index') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('analytics.index') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Executive Analytics</span>
                    </a>

                    <a href="{{ route('analytics.technicians') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('analytics.technicians*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('analytics.technicians*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Technician Performance</span>
                    </a>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-[#2563eb] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>User Accounts &amp; Settings</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

    </nav>

    {{-- Sidebar Bottom: Compact User Profile & Quick Logout --}}
    <div class="shrink-0 p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 transition-colors">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0 truncate">
                    <span class="block text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ Auth::user()->name }}</span>
                    <span class="block text-[10px] text-slate-400 capitalize font-medium">{{ Auth::user()->role }}</span>
                </div>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                <a href="{{ route('profile.edit') }}" title="Profile Settings" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Log Out" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

</aside>