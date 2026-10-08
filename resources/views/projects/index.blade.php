<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Project Handling & Tracking</h2>
                    <span class="text-xs font-bold uppercase tracking-wider bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-2.5 py-1 rounded border border-indigo-200 dark:border-indigo-500/30">Operations Hub</span>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Track ongoing, completed, and pending company projects with attached blueprints and documentation</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('projects.export-csv', request()->all()) }}" class="btn-secondary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export CSV
                </a>
                @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                <a href="{{ route('projects.create') }}" class="btn-amber">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    + New Project
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

        {{-- Flash Alerts --}}
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 dark:hover:text-white">&times;</button>
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 dark:hover:text-white">&times;</button>
            </div>
        @endif

        {{-- 1. KPI Metrics Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5 sm:gap-4">
            {{-- Total Projects --}}
            <a href="{{ route('projects.index') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:border-blue-500/50 transition-all group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Projects</span>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-slate-900 dark:text-white font-heading">{{ $totalProjectsCount }}</span>
                    <span class="text-xs text-slate-400">All registered</span>
                </div>
            </a>

            {{-- In Progress --}}
            <a href="{{ route('projects.index', array_merge(request()->query(), ['status' => 'in_progress'])) }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:border-amber-500/50 transition-all group {{ $statusFilter === 'in_progress' ? 'ring-2 ring-amber-500/50' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">In Progress</span>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-400 font-heading">{{ $inProgressCount }}</span>
                    <span class="text-xs text-slate-400">Active Work</span>
                </div>
            </a>

            {{-- Done / Completed --}}
            <a href="{{ route('projects.index', array_merge(request()->query(), ['status' => 'completed'])) }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:border-emerald-500/50 transition-all group {{ $statusFilter === 'completed' ? 'ring-2 ring-emerald-500/50' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Done / Completed</span>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-heading">{{ $completedCount }}</span>
                    <span class="text-xs text-slate-400">Delivered</span>
                </div>
            </a>

            {{-- Incomplete / Pending --}}
            <a href="{{ route('projects.index', array_merge(request()->query(), ['status' => 'incompleted'])) }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:border-rose-500/50 transition-all group {{ $statusFilter === 'incompleted' ? 'ring-2 ring-rose-500/50' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Incomplete</span>
                    <span class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-rose-600 dark:text-rose-400 font-heading">{{ $incompletedCount }}</span>
                    <span class="text-xs text-slate-400">Needs Action</span>
                </div>
            </a>

            {{-- Total Budget Valuation --}}
            <div class="col-span-2 lg:col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Valuation</span>
                    <span class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <span class="text-sm font-extrabold">₹</span>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-purple-600 dark:text-purple-400 font-heading">₹{{ number_format($totalValuation, 0) }}</span>
                </div>
            </div>
        </div>

        {{-- Unified Filter & Domain Segmentation Hub --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 sm:p-5 space-y-4">
            
            {{-- Top Row: Category Tabs (Segmented Control) --}}
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </div>
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-heading">Project Modules</span>
                </div>

                {{-- Segmented Track Pills --}}
                <div class="flex items-center gap-1.5 flex-wrap">
                    {{-- All --}}
                    @php $isAllActive = ($typeFilter === 'all' || !$typeFilter); @endphp
                    <a href="{{ route('projects.index', array_merge(request()->query(), ['type' => 'all'])) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                       style="{{ $isAllActive ? 'background-color: var(--crm-accent, #2563eb); color: #ffffff !important; box-shadow: 0 4px 10px -2px rgba(37, 99, 235, 0.35);' : 'background-color: #f1f5f9; color: #475569 !important;' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                        <span style="{{ $isAllActive ? 'color: #ffffff !important;' : '' }}">All</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                              style="{{ $isAllActive ? 'background-color: rgba(255,255,255,0.25); color: #ffffff !important;' : 'background-color: #e2e8f0; color: #475569;' }}">
                            {{ $totalProjectsCount }}
                        </span>
                    </a>

                    {{-- Terminal Attendance --}}
                    @php $isAttendanceActive = ($typeFilter === 'hardware_attendance'); @endphp
                    <a href="{{ route('projects.index', array_merge(request()->query(), ['type' => 'hardware_attendance'])) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                       style="{{ $isAttendanceActive ? 'background-color: #9333ea; color: #ffffff !important; box-shadow: 0 4px 10px -2px rgba(147, 51, 234, 0.35);' : 'background-color: #f1f5f9; color: #475569 !important;' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span style="{{ $isAttendanceActive ? 'color: #ffffff !important;' : '' }}">Terminal Attendance</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                              style="{{ $isAttendanceActive ? 'background-color: rgba(255,255,255,0.25); color: #ffffff !important;' : 'background-color: #f3e8ff; color: #7e22ce;' }}">
                            {{ $hardwareAttendanceCount }}
                        </span>
                    </a>

                    {{-- CCTV Surveillance --}}
                    @php $isCctvActive = ($typeFilter === 'hardware_cctv'); @endphp
                    <a href="{{ route('projects.index', array_merge(request()->query(), ['type' => 'hardware_cctv'])) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                       style="{{ $isCctvActive ? 'background-color: #2563eb; color: #ffffff !important; box-shadow: 0 4px 10px -2px rgba(37, 99, 235, 0.35);' : 'background-color: #f1f5f9; color: #475569 !important;' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span style="{{ $isCctvActive ? 'color: #ffffff !important;' : '' }}">CCTV Surveillance</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                              style="{{ $isCctvActive ? 'background-color: rgba(255,255,255,0.25); color: #ffffff !important;' : 'background-color: #dbeafe; color: #1d4ed8;' }}">
                            {{ $hardwareCctvCount }}
                        </span>
                    </a>

                    {{-- Web & Apps --}}
                    @php $isWebActive = ($typeFilter === 'software_web'); @endphp
                    <a href="{{ route('projects.index', array_merge(request()->query(), ['type' => 'software_web'])) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                       style="{{ $isWebActive ? 'background-color: #0891b2; color: #ffffff !important; box-shadow: 0 4px 10px -2px rgba(8, 145, 178, 0.35);' : 'background-color: #f1f5f9; color: #475569 !important;' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        <span style="{{ $isWebActive ? 'color: #ffffff !important;' : '' }}">Web & Apps</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                              style="{{ $isWebActive ? 'background-color: rgba(255,255,255,0.25); color: #ffffff !important;' : 'background-color: #cffafe; color: #0e7490;' }}">
                            {{ $softwareWebCount }}
                        </span>
                    </a>

                    {{-- Hybrid Turnkey --}}
                    @php $isHybridActive = ($typeFilter === 'hybrid'); @endphp
                    <a href="{{ route('projects.index', array_merge(request()->query(), ['type' => 'hybrid'])) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                       style="{{ $isHybridActive ? 'background-color: #d97706; color: #ffffff !important; box-shadow: 0 4px 10px -2px rgba(217, 119, 6, 0.35);' : 'background-color: #f1f5f9; color: #475569 !important;' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span style="{{ $isHybridActive ? 'color: #ffffff !important;' : '' }}">Hybrid Turnkey</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                              style="{{ $isHybridActive ? 'background-color: rgba(255,255,255,0.25); color: #ffffff !important;' : 'background-color: #fef3c7; color: #b45309;' }}">
                            {{ $hybridCount }}
                        </span>
                    </a>
                </div>
            </div>

            {{-- Domain Intelligence Stat Tiles (Refined Backgrounds & High-Contrast Typography) --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                {{-- Hardware Capital --}}
                <div class="p-3.5 rounded-xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Hardware Capital</span>
                        <span class="w-6 h-6 rounded-md bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </span>
                    </div>
                    <div class="mt-2">
                        <span class="text-lg sm:text-xl font-black text-slate-900 dark:text-white font-heading">₹{{ number_format($hardwareValuation, 0) }}</span>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Surveillance & Equipment</p>
                    </div>
                </div>

                {{-- Software / Web Value --}}
                <div class="p-3.5 rounded-xl bg-cyan-50/50 dark:bg-cyan-950/20 border border-cyan-100 dark:border-cyan-900/40 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-cyan-700 dark:text-cyan-400 uppercase tracking-wider">Software / Web Value</span>
                        <span class="w-6 h-6 rounded-md bg-cyan-100 dark:bg-cyan-900/50 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        </span>
                    </div>
                    <div class="mt-2">
                        <span class="text-lg sm:text-xl font-black text-slate-900 dark:text-white font-heading">₹{{ number_format($softwareValuation, 0) }}</span>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Web Apps & Portals</p>
                    </div>
                </div>

                {{-- Attendance Terminals --}}
                <div class="p-3.5 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-900/40 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-purple-700 dark:text-purple-400 uppercase tracking-wider">Attendance Terminals</span>
                        <span class="w-6 h-6 rounded-md bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-lg sm:text-xl font-black text-slate-900 dark:text-white font-heading">{{ $hardwareAttendanceCount }}</span>
                        <span class="text-xs font-bold text-purple-700 dark:text-purple-400">Deployments</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Active field biometric units</p>
                </div>

                {{-- Software Portals --}}
                <div class="p-3.5 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Software Portals</span>
                        <span class="w-6 h-6 rounded-md bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-lg sm:text-xl font-black text-slate-900 dark:text-white font-heading">{{ $softwareWebCount }}</span>
                        <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">Active Sites</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Live customer web domains</p>
                </div>
            </div>

            {{-- Search & Refinement Form --}}
            <form method="GET" action="{{ route('projects.index') }}" class="pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                <input type="hidden" name="type" value="{{ $typeFilter }}">
                
                {{-- Search Box --}}
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="search" name="search" value="{{ $search }}"
                           placeholder="Search company, project title, or code..." 
                           style="padding-left: 2.5rem !important;"
                           class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                </div>

                {{-- Status Filter --}}
                <div class="w-full sm:w-44">
                    <select name="status" class="w-full py-2.5 px-3 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-800 dark:text-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Done / Completed</option>
                        <option value="incompleted" {{ $statusFilter === 'incompleted' ? 'selected' : '' }}>Incomplete</option>
                        <option value="on_hold" {{ $statusFilter === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                {{-- Priority Filter --}}
                <div class="w-full sm:w-40">
                    <select name="priority" class="w-full py-2.5 px-3 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-800 dark:text-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                        <option value="all" {{ $priorityFilter === 'all' ? 'selected' : '' }}>All Priorities</option>
                        <option value="urgent" {{ $priorityFilter === 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                        <option value="high" {{ $priorityFilter === 'high' ? 'selected' : '' }}>🟠 High</option>
                        <option value="medium" {{ $priorityFilter === 'medium' ? 'selected' : '' }}>🟡 Medium</option>
                        <option value="low" {{ $priorityFilter === 'low' ? 'selected' : '' }}>🟢 Low</option>
                    </select>
                </div>

                {{-- Submit and Reset Buttons --}}
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="text-xs font-bold py-2.5 px-4 rounded-xl inline-flex items-center justify-center gap-1.5 transition-all shadow-sm"
                            style="background-color: var(--crm-accent, #2563eb); color: #ffffff !important;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span style="color: #ffffff !important;">Filter</span>
                    </button>
                    @if($search || $statusFilter !== 'all' || $priorityFilter !== 'all' || ($typeFilter && $typeFilter !== 'all'))
                    <a href="{{ route('projects.index') }}" 
                       class="text-xs font-semibold py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white hover:bg-slate-50 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" 
                       title="Clear all filters">
                        Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- 3. Projects List / Grid --}}
        @if($projects->isEmpty())
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800 p-12 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 dark:text-indigo-400 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800 dark:text-white">No projects found</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    @if($search)
                        No project matches your search for "<span class="font-semibold text-slate-700 dark:text-slate-300">{{ $search }}</span>". Try searching by another company name or clear filters.
                    @else
                        No projects match the selected criteria. Start by registering your first installation or web project.
                    @endif
                </p>
                <div class="mt-5 flex justify-center gap-3">
                    @if($search || $statusFilter !== 'all' || $priorityFilter !== 'all' || ($typeFilter && $typeFilter !== 'all'))
                        <a href="{{ route('projects.index') }}" class="btn-secondary text-xs py-2 px-4">Reset Filter</a>
                    @endif
                    <a href="{{ route('projects.create') }}" class="btn-amber text-xs py-2 px-4">+ Create Project</a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($projects as $project)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all flex flex-col justify-between overflow-hidden group">
                        {{-- Card Header --}}
                        <div class="p-5 pb-3">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $project->project_code }}
                                    </span>
                                    <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full border {{ $project->status_badge_classes }}">
                                        {{ $project->status_label }}
                                    </span>
                                </div>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md border {{ $project->priority_badge_classes }}">
                                    {{ ucfirst($project->priority) }}
                                </span>
                            </div>

                            {{-- Project Type Pill --}}
                            <div class="mb-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-extrabold px-2.5 py-0.5 rounded-md border {{ $project->project_type_badge_classes }}">
                                    @if($project->project_type === 'hardware_attendance')
                                        ⏱ Terminal Attendance
                                    @elseif($project->project_type === 'software_web')
                                        🌐 Webpage & App
                                    @elseif($project->project_type === 'hybrid')
                                        ⚡ Hybrid Turnkey
                                    @else
                                        📸 CCTV Hardware
                                    @endif
                                </span>
                            </div>

                            {{-- Company & Project Title --}}
                            <div>
                                <div class="flex items-center gap-1.5 text-xs font-extrabold text-blue-600 dark:text-blue-400">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span class="truncate" title="{{ $project->company_name }}">{{ $project->company_name }}</span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white mt-0.5 line-clamp-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    <a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a>
                                </h3>
                                @if($project->site_address)
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-1 flex items-center gap-1">
                                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                        {{ $project->site_address }}
                                    </p>
                                @endif
                            </div>

                            {{-- Domain Specific Technical Chip --}}
                            @if(!empty($project->hardware_specs) && ($project->hardware_specs['terminal_count'] ?? 0 || $project->hardware_specs['camera_count'] ?? 0))
                                <div class="mt-2.5 p-2 rounded-xl bg-purple-50/70 dark:bg-purple-950/30 border border-purple-200/60 dark:border-purple-800/40 text-[11px] flex items-center justify-between text-purple-800 dark:text-purple-300">
                                    <span class="font-semibold flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $project->hardware_specs['terminal_count'] ?? 0 }} Terminals • {{ $project->hardware_specs['camera_count'] ?? 0 }} Cameras
                                    </span>
                                    @if(!empty($project->hardware_specs['device_brand']))
                                        <span class="text-[10px] bg-purple-100 dark:bg-purple-900/60 px-1.5 py-0.5 rounded font-bold truncate max-w-[120px]">{{ $project->hardware_specs['device_brand'] }}</span>
                                    @endif
                                </div>
                            @elseif(!empty($project->software_specs) && (!empty($project->software_specs['webpage_url']) || !empty($project->software_specs['tech_stack'])))
                                <div class="mt-2.5 p-2 rounded-xl bg-cyan-50/70 dark:bg-cyan-950/30 border border-cyan-200/60 dark:border-cyan-800/40 text-[11px] flex items-center justify-between text-cyan-800 dark:text-cyan-300">
                                    <span class="font-semibold truncate flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                        {{ $project->software_specs['webpage_url'] ? Str::limit($project->software_specs['webpage_url'], 26) : Str::limit($project->software_specs['tech_stack'], 26) }}
                                    </span>
                                    <span class="text-[10px] bg-cyan-100 dark:bg-cyan-900/60 px-1.5 py-0.5 rounded font-bold">Web Stack</span>
                                </div>
                            @endif

                            {{-- Progress Bar --}}
                            <div class="mt-3.5">
                                <div class="flex items-center justify-between text-xs mb-1 font-semibold">
                                    <span class="text-slate-600 dark:text-slate-400">Completion</span>
                                    <span class="text-slate-900 dark:text-white font-bold">{{ $project->progress_percentage }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300 {{ $project->progress_bar_color }}" style="width: {{ $project->progress_percentage }}%"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Metadata & Documents badge --}}
                        <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                            {{-- Documents Pill --}}
                            <div class="flex items-center gap-1.5 font-semibold {{ $project->documents_count > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>{{ $project->documents_count }} {{ Str::plural('Document', $project->documents_count) }}</span>
                            </div>

                            {{-- Budget or Deadline --}}
                            <div class="text-right">
                                @if($project->budget)
                                    <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($project->budget, 0) }}</span>
                                @elseif($project->deadline)
                                    <span class="text-slate-500 dark:text-slate-400">Due {{ \Carbon\Carbon::parse($project->deadline)->format('M d') }}</span>
                                @else
                                    <span class="text-slate-400">No Budget</span>
                                @endif
                            </div>
                        </div>

                        {{-- Card Actions Footer --}}
                        <div class="p-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2 bg-white dark:bg-slate-900">
                            {{-- Quick 1-click status dropdown --}}
                            <form action="{{ route('projects.updateStatus', $project) }}" method="POST" class="inline-flex">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="text-xs py-1.5 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold cursor-pointer focus:ring-1 focus:ring-blue-500">
                                    <option value="in_progress" {{ $project->status === 'in_progress' ? 'selected' : '' }}>• In Progress</option>
                                    <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>✓ Done / Completed</option>
                                    <option value="incompleted" {{ $project->status === 'incompleted' ? 'selected' : '' }}>! Incompleted</option>
                                    <option value="on_hold" {{ $project->status === 'on_hold' ? 'selected' : '' }}>⏸ On Hold</option>
                                </select>
                            </form>

                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('projects.show', $project) }}" class="btn-secondary text-xs py-1.5 px-3 font-bold" title="Open Project & View Documents">
                                    View & Files
                                </a>
                                @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                <a href="{{ route('projects.edit', $project) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Edit Project">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $projects->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
