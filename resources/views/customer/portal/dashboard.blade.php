<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 text-xs font-semibold crm-customer-pill rounded-full border"
                          style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.1); color: var(--crm-accent, #2563eb); border-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.25);">
                        Customer Portal
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">Site ID: #{{ $lead ? $lead->id : 'Guest' }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-1 font-heading">
                    Welcome back, {{ auth()->user()->name }}! 👋
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    {{ $lead ? $lead->customer_name . ' • ' . ($lead->site_address ?: 'Registered Customer Site') : 'Manage your CCTV installations, warranties, maintenance & support.' }}
                </p>
            </div>
            <div class="flex items-center gap-2.5 flex-wrap">
                {{-- 1-Click Book Free Site Survey Button in Header --}}
                <button type="button" 
                        onclick="document.getElementById('siteSurveyBookingModal').classList.remove('hidden')"
                        class="crm-customer-action-btn inline-flex items-center gap-2 px-3.5 py-2 text-white font-semibold text-xs rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
                        style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Book Free Site Survey</span>
                </button>

                <a href="{{ route('portal.tickets.create') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-[#0f172a] hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50 text-xs font-semibold rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Report Issue / Breakdown</span>
                </a>

                <a href="{{ route('home') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-[#0f172a] hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/90 dark:border-slate-800 text-xs font-semibold rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" style="color: var(--crm-accent, #2563eb);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>Smart CCTV Store</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Alerts / Status Messages --}}
            @if(session('status'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl flex items-center gap-3 shadow-xs">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold text-sm">✓</div>
                    <div class="text-xs font-semibold text-emerald-900 dark:text-emerald-200">{{ session('status') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/80 rounded-2xl flex items-center gap-3 shadow-xs">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 flex items-center justify-center font-bold text-sm">!</div>
                    <div class="text-xs font-semibold text-rose-900 dark:text-rose-200">{{ session('error') }}</div>
                </div>
            @endif

            {{-- ========================================================================= --}}
            {{-- CASE 1: PREVIEW MODE (NEW CUSTOMER BEFORE ADMIN ACCEPTS REQUEST)          --}}
            {{-- ========================================================================= --}}
            @if(!$isRequestAccepted)

                {{-- Status Banner for New Registration --}}
                @if(isset($latestWorkTicket) && $latestWorkTicket)
                    <div class="p-5 bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                        <div class="flex items-start gap-3.5">
                            <div class="p-3 bg-amber-500 text-white rounded-xl shadow-xs text-lg font-bold">⏳</div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-amber-950 dark:text-amber-300 font-heading">Work Request #{{ $latestWorkTicket->ticket_no }} Under Review</h3>
                                    <span class="text-[10px] font-bold uppercase px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        {{ $latestWorkTicket->status_label }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                    Our operations manager is reviewing your site requirements. Once approved, your live CCTV camera register, warranty tracking, and AMC schedule will unlock here automatically.
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('portal.tickets.show', $latestWorkTicket) }}" 
                           class="crm-customer-action-btn px-4 py-2.5 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0 text-center cursor-pointer"
                           style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                            Track Request Live →
                        </a>
                    </div>
                @else
                    <div class="p-6 bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                        <div class="flex items-start gap-4">
                            <div class="p-3.5 text-white rounded-2xl shadow-xs text-xl font-bold" style="background-color: var(--crm-accent, #2563eb);">🚀</div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white font-heading">Welcome to Precision IT Systems Client Portal</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
                                    Submit your site installation or support requirements below. You can also calculate estimated costs, book a free engineer site survey, and inspect past deployment records.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                            <button type="button" 
                                    onclick="document.getElementById('siteSurveyBookingModal').classList.remove('hidden')"
                                    class="crm-customer-action-btn px-4 py-2.5 text-white text-xs font-bold rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
                                    style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                                📅 Book Free Survey
                            </button>
                            <a href="{{ route('home') }}" 
                               class="px-4 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs transition">
                                Explore Storefront →
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Customer Site Setup & Work / Support Request Card (Expanded) --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden" x-data="{ expanded: true }">
                    <div class="px-6 py-4 bg-slate-50/70 dark:bg-slate-900/60 border-b border-slate-200/90 dark:border-slate-800 flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl crm-customer-icon-box flex items-center justify-center font-bold text-lg border"
                                 style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.1); color: var(--crm-accent, #2563eb); border-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.25);">
                                🛠️
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 font-heading">
                                    <span>Site Details & Work / Support Request</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full crm-customer-pill border"
                                          style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.12); color: var(--crm-accent, #2563eb); border-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.25);">
                                        {{ empty($lead->site_address) ? 'Action Required' : 'Step 1' }}
                                    </span>
                                </h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Tell us what security services you need for your premises</p>
                            </div>
                        </div>
                        <button type="button" class="text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white text-xs font-semibold flex items-center gap-1">
                            <span x-text="expanded ? 'Collapse ▲' : 'Expand ▼'"></span>
                        </button>
                    </div>

                    <div class="p-6 space-y-6" x-show="expanded" x-collapse>
                        <form method="POST" action="{{ route('portal.site-work-request') }}" class="space-y-5">
                            @csrf

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Your Full Name *</label>
                                    <input type="text" name="customer_name" required value="{{ auth()->user()->name }}"
                                           class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 py-2.5 px-3">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Phone / WhatsApp *</label>
                                    <input type="tel" name="phone" required placeholder="+91 98765 43210" value="{{ $lead && $lead->phone !== 'Pending update' ? $lead->phone : '' }}"
                                           class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 py-2.5 px-3">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email Address *</label>
                                    <input type="email" name="email" required value="{{ auth()->user()->email }}"
                                           class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 py-2.5 px-3">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Installation Site / Premises Address *</label>
                                    <input type="text" name="site_address" required placeholder="e.g. Tower 3, Prestige Tech Park, Bangalore" value="{{ $lead?->site_address }}"
                                           class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 py-2.5 px-3">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Primary Request Type *</label>
                                    <select name="work_type" class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 py-2.5 px-3">
                                        <option value="cctv_installation">📹 New CCTV Camera System Installation</option>
                                        <option value="cctv_upgrade">🔄 Existing CCTV System Upgrade / 4K Transition</option>
                                        <option value="amc_service">🛡️ AMC Contract / Routine Maintenance</option>
                                        <option value="repair_breakdown">🚨 Camera Offline / NVR Breakdown Repair</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Requirement Notes / Camera Locations</label>
                                <textarea name="description" rows="3" placeholder="Describe number of cameras needed, indoor/outdoor zones, night vision requirements, or issues with existing setup..."
                                          class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 p-3"></textarea>
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">⚡ Average engineer assignment response time: &lt; 30 minutes</span>
                                <button type="submit" 
                                        class="crm-customer-action-btn px-6 py-2.5 min-h-[44px] text-white font-bold text-sm rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
                                        style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                                    Submit Request to Operations →
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- SECTION 1: PREVIOUS COMPLETED WORKS & PROJECTS SHOWCASE --}}
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--crm-accent, #2563eb);">Past Deployments & Portfolio</span>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading">Featured CCTV Projects Done by Our Team</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Inspect real-world surveillance infrastructure deployed across commercial, residential, and industrial sites.</p>
                        </div>
                        <a href="{{ route('home') }}" class="text-xs font-semibold hover:underline shrink-0" style="color: var(--crm-accent, #2563eb);">
                            Explore All Models on Storefront →
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($companyProjects as $proj)
                            <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden transition group">
                                <div class="relative h-48 overflow-hidden bg-slate-100 dark:bg-slate-950">
                                    <img src="{{ $proj['image'] }}" alt="{{ $proj['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    <div class="absolute top-3 left-3 flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold uppercase tracking-wider border border-white/10">
                                            {{ $proj['category'] }}
                                        </span>
                                        <span class="crm-customer-badge px-2.5 py-1 rounded-lg text-white text-[10px] font-bold uppercase tracking-wider shadow-xs"
                                              style="background-color: var(--crm-accent, #2563eb);">
                                            {{ $proj['badge'] }}
                                        </span>
                                    </div>
                                    <div class="absolute bottom-3 right-3 px-2 py-0.5 rounded bg-slate-900/80 border border-slate-700 text-amber-300 text-xs font-bold">
                                        {{ $proj['rating'] }}
                                    </div>
                                </div>
                                <div class="p-5 space-y-2">
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading transition">{{ $proj['title'] }}</h3>
                                    <div class="text-[11px] font-bold font-mono" style="color: var(--crm-accent, #2563eb);">{{ $proj['tech'] }}</div>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $proj['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- SECTION 2: CUTTING EDGE TECHNOLOGIES USED --}}
                <div class="space-y-4">
                    <div class="border-b border-slate-200 dark:border-slate-800 pb-3">
                        <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--crm-accent, #2563eb);">Enterprise AI Security</span>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading">Advanced AI CCTV Technologies We Deploy</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Next-generation hardware offering deep learning intrusion detection, 4K ColorVu, and perimeter tripwires.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($technologies as $tech)
                            <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs space-y-2.5 transition">
                                <div class="flex items-center justify-between">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-xl">
                                        {{ $tech['icon'] }}
                                    </div>
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full crm-customer-pill border"
                                          style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.1); color: var(--crm-accent, #2563eb); border-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.25);">
                                        {{ $tech['badge'] }}
                                    </span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white font-heading">{{ $tech['title'] }}</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $tech['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            {{-- ========================================================================= --}}
            {{-- CASE 2: ACTIVE CLIENT PORTAL DASHBOARD (AFTER ADMIN ACCEPTS REQUEST)      --}}
            {{-- ========================================================================= --}}
            @else

                {{-- Metric KPI Cards Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    {{-- 1. Installed Equipment --}}
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between transition group">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">CCTV Equipment</span>
                            <div class="w-10 h-10 rounded-xl crm-customer-icon-box border flex items-center justify-center font-bold"
                                 style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.1); color: var(--crm-accent, #2563eb); border-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.25);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-black text-slate-900 dark:text-white font-heading">{{ $totalEquipment }} <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Units</span></div>
                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    {{ $activeWarrantyCount }} In Warranty
                                </span>
                                @if($expiringSoonCount > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        {{ $expiringSoonCount }} Expiring
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('portal.equipment') }}" class="text-xs font-bold hover:underline flex items-center justify-between" style="color: var(--crm-accent, #2563eb);">
                                <span>View all installed units</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>

                    {{-- 2. AMC Status --}}
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between hover:border-emerald-300 dark:hover:border-emerald-800 transition group">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">AMC Maintenance</span>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            @if($activeAmc)
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 uppercase tracking-wider">Active AMC</span>
                                </div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 font-medium mt-1.5">
                                    Valid until: <strong class="text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($activeAmc->end_date)->format('d M Y') }}</strong>
                                </div>
                            @else
                                <div class="text-xl font-bold text-slate-800 dark:text-slate-200 font-heading">No Active AMC</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Get 24/7 priority support & PM visits</div>
                            @endif
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('portal.amc') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center justify-between">
                                <span>{{ $activeAmc ? 'View visits calendar' : 'Request AMC protection' }}</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>

                    {{-- 3. Open Service Tickets --}}
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between hover:border-rose-300 dark:hover:border-rose-800 transition group">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Service Tickets</span>
                            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-black text-slate-900 dark:text-white font-heading">{{ $openTicketsCount }}</div>
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                                {{ $openTicketsCount === 0 ? 'All cameras operational' : 'Active issue tickets being resolved' }}
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('portal.tickets') }}" class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline flex items-center justify-between">
                                <span>Track helpdesk requests</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>

                    {{-- 4. Outstanding Dues / Invoices --}}
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between hover:border-amber-300 dark:hover:border-amber-800 transition group">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pending Dues</span>
                            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-2xl font-black text-slate-900 dark:text-white font-heading">₹{{ number_format($totalDue, 2) }}</div>
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                                {{ $unpaidInvoices->count() }} invoice(s) pending payment
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('portal.invoices') }}" class="text-xs font-bold text-amber-700 dark:text-amber-400 hover:underline flex items-center justify-between">
                                <span>Download invoices & pay</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>

                </div>

                {{-- Action Banner: Pending Quotations if any --}}
                @if($pendingQuotations->count() > 0)
                    <div class="p-5 bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 rounded-2xl shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center justify-center shrink-0 text-xl font-bold">
                                📑
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-amber-950 dark:text-amber-300 font-heading">You have {{ $pendingQuotations->count() }} new CCTV quotation(s) awaiting your approval!</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">Review equipment line items, download proposal PDF, and approve online instantly.</p>
                            </div>
                        </div>
                        <a href="{{ route('portal.quotations') }}" 
                           class="crm-customer-action-btn px-5 py-2.5 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0 cursor-pointer"
                           style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                            Review Quotations →
                        </a>
                    </div>
                @endif

                {{-- Site Survey & Feasibility Assessment Card --}}
                @if(isset($siteSurveys) && $siteSurveys->isNotEmpty())
                    @php $latestSurvey = $siteSurveys->first(); @endphp
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl border {{ $latestSurvey->status === 'completed' ? 'border-emerald-200 dark:border-emerald-800/80' : 'border-amber-200 dark:border-amber-800/80' }} shadow-xs p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 {{ $latestSurvey->status === 'completed' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800' }}">
                                <span class="text-xl">📐</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading">
                                        {{ $latestSurvey->status === 'completed' ? 'Site Survey Completed & Feasibility Ready' : 'Site Survey Scheduled' }}
                                    </h3>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $latestSurvey->status === 'completed' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }}">
                                        {{ $latestSurvey->status === 'completed' ? '✓ Completed' : '⏳ Scheduled' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                                    @if($latestSurvey->status === 'completed')
                                        Inspection conducted by Engineer <strong class="text-slate-800 dark:text-slate-200">{{ $latestSurvey->surveyedBy?->name ?? 'Technician' }}</strong> • {{ $latestSurvey->camera_count_recommended ?: 0 }} Cameras Recommended • {{ $latestSurvey->photos->count() }} Site Photos Uploaded.
                                    @else
                                        Engineer <strong class="text-slate-800 dark:text-slate-200">{{ $latestSurvey->surveyedBy?->name ?? 'Technician' }}</strong> scheduled to visit on <strong class="text-slate-800 dark:text-slate-200">{{ $latestSurvey->survey_date->format('d M Y') }}</strong>.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('portal.surveys.show', $latestSurvey) }}" 
                           class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 text-xs font-bold rounded-xl shadow-xs transition shrink-0">
                            View Survey Report & Photos →
                        </a>
                    </div>
                @endif

                {{-- Main Content 2-Column Section --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Left 2 Columns: Installed Equipment Quick View --}}
                    <div class="lg:col-span-2 space-y-6">
                        
                        {{-- Installed Equipment Card --}}
                        <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-200/90 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-900/60">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">Your CCTV Cameras & Installed Assets</h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Live warranty health for equipment on your site</p>
                                </div>
                                <a href="{{ route('portal.equipment') }}" 
                                   class="px-3 py-1.5 crm-customer-pill border font-bold text-xs rounded-xl transition"
                                   style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.1); color: var(--crm-accent, #2563eb); border-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.25);">
                                    View All ({{ $totalEquipment }})
                                </a>
                            </div>

                            @if($recentEquipment->isEmpty())
                                <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                                    <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No equipment recorded for this site yet.</p>
                                </div>
                            @else
                                <div class="divide-y divide-slate-100 dark:divide-slate-800 overflow-x-auto">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                                            <tr>
                                                <th class="px-5 py-3">Equipment Name</th>
                                                <th class="px-5 py-3">Location Tag</th>
                                                <th class="px-5 py-3">Serial No</th>
                                                <th class="px-5 py-3">Mfg Warranty</th>
                                                <th class="px-5 py-3 text-right">Quick Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium text-slate-700 dark:text-slate-300">
                                            @foreach($recentEquipment as $item)
                                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                                    <td class="px-5 py-3.5">
                                                        <div class="font-bold text-slate-900 dark:text-white">{{ $item->equipment_name }}</div>
                                                        <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $item->product ? $item->product->brand . ' ' . $item->product->model_no : 'Hardware' }}</div>
                                                    </td>
                                                    <td class="px-5 py-3.5">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-[11px] border border-slate-200 dark:border-slate-700">
                                                            📍 {{ $item->location_tag ?: 'Main Site' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-5 py-3.5 font-mono text-[11px] font-bold" style="color: var(--crm-accent, #2563eb);">
                                                        {{ $item->serial_number ?: 'N/A' }}
                                                    </td>
                                                    <td class="px-5 py-3.5">
                                                        @if($item->mfg_warranty_status === 'active')
                                                             <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                                ● Active ({{ $item->mfg_days_remaining }}d left)
                                                            </span>
                                                        @elseif($item->mfg_warranty_status === 'expiring_soon')
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                                ▲ Expiring ({{ $item->mfg_days_remaining }}d)
                                                            </span>
                                                        @elseif($item->mfg_warranty_status === 'expired')
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                                ✕ Expired
                                                            </span>
                                                        @else
                                                            <span class="text-slate-400">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-5 py-3.5 text-right">
                                                        <a href="{{ route('portal.tickets.create', ['equipment_id' => $item->id]) }}" 
                                                           class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 font-bold rounded-lg text-[11px] transition">
                                                            Report Issue
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        {{-- Recent Service Tickets --}}
                        <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-200/90 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-900/60">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">Recent Service & Breakdown Tickets</h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Live engineer dispatch and repair tracking</p>
                                </div>
                                <a href="{{ route('portal.tickets') }}" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl border border-slate-200 dark:border-slate-700 transition">
                                    All Tickets
                                </a>
                            </div>

                            @if($recentTickets->isEmpty())
                                <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No support tickets reported yet.</p>
                                    <p class="text-xs text-slate-400 mt-1">If any camera goes offline or has issues, click "Report Issue" above.</p>
                                </div>
                            @else
                                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($recentTickets as $ticket)
                                        <div class="p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition flex items-center justify-between gap-4">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono text-xs font-bold" style="color: var(--crm-accent, #2563eb);">#{{ $ticket->ticket_no }}</span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                                        @if($ticket->status === 'resolved' || $ticket->status === 'closed') bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800
                                                        @elseif($ticket->status === 'cancelled') bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800
                                                        @elseif($ticket->status === 'in_progress') bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800
                                                        @elseif($ticket->status === 'assigned') crm-customer-pill border" style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.1); color: var(--crm-accent, #2563eb); border-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.25);
                                                        @else bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 @endif">
                                                        @if($ticket->status === 'cancelled') ✕ Declined
                                                        @elseif($ticket->status === 'assigned') ✓ Accepted
                                                        @elseif($ticket->status === 'open') ⏳ Under Review
                                                        @else {{ $ticket->status_label }} @endif
                                                    </span>
                                                    <span class="text-[11px] font-medium text-slate-400">• {{ $ticket->created_at->diffForHumans() }}</span>
                                                </div>
                                                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $ticket->title }}</div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                                    Assigned Engineer: <strong class="text-slate-700 dark:text-slate-200">{{ $ticket->assignedTechnician?->name ?: 'Dispatching specialist...' }}</strong>
                                                </div>
                                            </div>
                                            <a href="{{ route('portal.tickets.show', $ticket) }}" 
                                               class="crm-customer-action-btn px-3.5 py-1.5 text-white font-bold text-xs rounded-xl shadow-xs transition shrink-0 cursor-pointer"
                                               style="background-color: var(--crm-accent, #2563eb); box-shadow: 0 2px 8px var(--crm-accent-shadow, rgba(37,99,235,0.3));">
                                                View Timeline →
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </div>

                    {{-- Right Column: AMC Next Visit & Quick Actions --}}
                    <div class="space-y-6">

                        {{-- AMC Card Box --}}
                        <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $activeAmc ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Preventive AMC</span>
                                </div>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    {{ $activeAmc ? ucfirst(str_replace('_', ' ', $activeAmc->frequency)) : 'Inactive' }}
                                </span>
                            </div>

                            @if($activeAmc)
                                <div>
                                    <div class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Contract No</div>
                                    <div class="text-lg font-mono font-bold" style="color: var(--crm-accent, #2563eb);">{{ $activeAmc->contract_no }}</div>
                                </div>

                                <div class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-3.5 space-y-1">
                                    <div class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--crm-accent, #2563eb);">Next Scheduled Routine Visit</div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white">
                                        {{ $nextVisit ? \Carbon\Carbon::parse($nextVisit->scheduled_date)->format('l, d M Y') : 'All visits completed for cycle' }}
                                    </div>
                                    @if($nextVisit && $nextVisit->assignedTechnician)
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            Engineer: <strong class="text-slate-800 dark:text-slate-200">{{ $nextVisit->assignedTechnician->name }}</strong>
                                        </div>
                                    @endif
                                </div>

                                <a href="{{ route('portal.amc') }}" 
                                   class="crm-customer-action-btn block w-full py-2.5 text-center text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer"
                                   style="background-color: var(--crm-accent, #2563eb); box-shadow: 0 4px 12px var(--crm-accent-shadow, rgba(37,99,235,0.3));">
                                    View Full AMC Details & Visits
                                </a>
                            @else
                                <div class="space-y-2">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white font-heading">Protect Your Security Cameras with AMC</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                        Get scheduled quarterly lens cleaning, DVR health check, cable inspections, and priority breakdown callouts.
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('portal.amc.renew') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="crm-customer-action-btn w-full py-2.5 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer"
                                            style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                                        Request AMC Coverage Quote
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Support Hotline Box --}}
                        <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs space-y-3">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 font-heading">
                                <svg class="w-4 h-4" style="color: var(--crm-accent, #2563eb);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                CCTV Helpdesk Support
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Need emergency security assistance or immediate camera feed recovery?
                            </p>
                            <div class="space-y-2 pt-1 text-xs">
                                <div class="flex items-center justify-between p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <span class="text-slate-500 dark:text-slate-400 font-semibold">Support Desk:</span>
                                    <a href="tel:+919677257774" class="font-mono font-bold hover:underline" style="color: var(--crm-accent, #2563eb);">+91 96772 57774</a>
                                </div>
                                <div class="flex items-center justify-between p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <span class="text-slate-500 dark:text-slate-400 font-semibold">Email:</span>
                                    <a href="mailto:precisionitsystem@gmail.com" class="font-semibold hover:underline" style="color: var(--crm-accent, #2563eb);">precisionitsystem@gmail.com</a>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            @endif

        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL: 1-CLICK "BOOK FREE SITE SURVEY & ENGINEER VISIT"                   --}}
    {{-- ========================================================================= --}}
    <div id="siteSurveyBookingModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-xl w-full p-6 sm:p-8 shadow-2xl space-y-5 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl crm-customer-icon-box flex items-center justify-center font-bold text-xl border"
                         style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.1); color: var(--crm-accent, #2563eb); border-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.25);">
                        📐
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white font-heading">Book Free CCTV Site Survey</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">A certified engineer will inspect blind spots & calculate camera coverage.</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('siteSurveyBookingModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-2xl font-bold">×</button>
            </div>

            <form method="POST" action="{{ route('portal.site-work-request') }}" class="space-y-4"
                  x-data="{
                      surveyDate: '{{ date('Y-m-d', strtotime('+1 day')) }}',
                      timeSlot: 'morning',
                      premisesType: 'commercial_office',
                      cameraScope: '5_8',
                      notes: '',
                      buildSurveyDescription() {
                          let slotLabel = this.timeSlot === 'morning' ? 'Morning (10:00 AM – 01:00 PM)' : 'Afternoon (02:00 PM – 05:00 PM)';
                          let premLabel = {
                              'villa': 'Residential Villa / Independent House',
                              'apartment': 'Apartment Complex / Gated Society',
                              'commercial_office': 'Commercial Office / Tech Park Floor',
                              'factory': 'Industrial Factory / Warehouse Hub',
                              'retail': 'Retail Store / Supermarket'
                          }[this.premisesType] || 'Standard Premises';
                          
                          let scopeLabel = {
                              '2_4': '2 to 4 Cameras',
                              '5_8': '5 to 8 Cameras',
                              '9_16': '9 to 16 Cameras',
                              '17_32': '17 to 32 Cameras',
                              '33_plus': '33+ Enterprise Cameras'
                          }[this.cameraScope] || 'General Scope';

                          return '--- Free CCTV Site Survey & Engineering Booking ---\n'
                              + '1. Preferred Inspection Date: ' + this.surveyDate + '\n'
                              + '2. Preferred Time Slot: ' + slotLabel + '\n'
                              + '3. Premises Category: ' + premLabel + '\n'
                              + '4. Estimated Camera Count: ' + scopeLabel + '\n'
                              + '5. Site / GPS / Landmark Directions:\n' + (this.notes || 'Standard site inspection requested.');
                      }
                  }">
                @csrf
                <input type="hidden" name="work_type" value="cctv_installation">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Preferred Date --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Preferred Date *</label>
                        <input type="date" name="preferred_date" required min="{{ date('Y-m-d') }}" x-model="surveyDate"
                               class="w-full text-sm min-h-[44px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium py-2.5 px-3.5 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20">
                    </div>

                    {{-- Preferred Time Slot --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Time Slot Window *</label>
                        <select x-model="timeSlot" class="w-full text-sm min-h-[44px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium py-2.5 px-3.5 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20">
                            <option value="morning">🌅 Morning (10:00 AM – 01:00 PM)</option>
                            <option value="afternoon">🌇 Afternoon (02:00 PM – 05:00 PM)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Premises Type --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Premises Type *</label>
                        <select x-model="premisesType" class="w-full text-sm min-h-[44px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium py-2.5 px-3.5 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20">
                            <option value="commercial_office">🏢 Commercial Office / IT Floor</option>
                            <option value="villa">🏡 Residential Villa / House</option>
                            <option value="apartment">🏘️ Apartment Complex / Society</option>
                            <option value="factory">🏭 Industrial Factory / Warehouse</option>
                            <option value="retail">🛍️ Retail Store / Showroom</option>
                        </select>
                    </div>

                    {{-- Camera Scope --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Approximate Cameras *</label>
                        <select x-model="cameraScope" class="w-full text-sm min-h-[44px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium py-2.5 px-3.5 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20">
                            <option value="2_4">2 to 4 Cameras</option>
                            <option value="5_8" selected>5 to 8 Cameras</option>
                            <option value="9_16">9 to 16 Cameras</option>
                            <option value="17_32">17 to 32 Cameras</option>
                            <option value="33_plus">33+ Enterprise Scale</option>
                        </select>
                    </div>
                </div>

                {{-- Contact Info Row --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Your Name *</label>
                        <input type="text" name="customer_name" required value="{{ auth()->user()->name }}"
                               class="w-full text-sm min-h-[44px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium py-2.5 px-3.5 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Phone / WhatsApp *</label>
                        <input type="tel" name="phone" required placeholder="+91 98765 43210" value="{{ $lead && $lead->phone !== 'Pending update' ? $lead->phone : '' }}"
                               class="w-full text-sm min-h-[44px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium py-2.5 px-3.5 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email Address *</label>
                    <input type="email" name="email" required value="{{ auth()->user()->email }}"
                           class="w-full text-sm min-h-[44px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium py-2.5 px-3.5 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20">
                </div>

                {{-- Site Address & Directions --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Installation Site Address & Landmark *</label>
                    <input type="text" name="site_address" required placeholder="e.g. Tower 3, Tech Park, Outer Ring Road, Bengaluru" value="{{ $lead?->site_address }}"
                           class="w-full text-sm min-h-[44px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium py-2.5 px-3.5 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20">
                </div>

                {{-- Additional Notes --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">GPS Pin / Gate & Parking Directions (Optional)</label>
                    <textarea x-model="notes" rows="2" placeholder="e.g. Enter through Gate 2, ask for security desk at reception, parking available in basement..."
                              class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium p-3.5 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20"></textarea>
                </div>

                {{-- Hidden auto-compiled description --}}
                <textarea name="description" class="hidden" :value="buildSurveyDescription()"></textarea>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('siteSurveyBookingModal').classList.add('hidden')"
                            class="px-5 py-2.5 min-h-[44px] bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition border border-slate-200 dark:border-slate-700">
                        Cancel
                    </button>
                    <button type="submit"
                            class="crm-customer-action-btn px-6 py-2.5 min-h-[44px] text-white font-bold text-sm rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
                            style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                        Confirm Survey Booking →
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
