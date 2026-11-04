<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">
                    {{ __('Field Engineer Workstation') }}
                </h1>
                <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    {{ __('Welcome') }}, <span class="font-semibold text-slate-700 dark:text-slate-300">{{ auth()->user()->name }}</span> &bull; {{ __('Active Technician Portal') }}
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <x-language-switch />

                {{-- Technician Self Attendance Clock In / Clock Out --}}
                @php
                    $myAttendance = $myAttendanceToday ?? \App\Models\EmployeeAttendance::where('user_id', auth()->id())->whereDate('date', now()->toDateString())->first();
                @endphp
                @if(!$myAttendance || !$myAttendance->clock_in)
                    <form method="POST" action="{{ route('attendance.clock-in') }}" class="inline-flex items-center">
                        @csrf
                        <input type="hidden" name="location_type" value="on_site">
                        <button type="submit" class="btn-md inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                            <span>{{ __('Clock In') }}</span>
                        </button>
                    </form>
                @elseif(!$myAttendance->clock_out)
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-emerald-800 dark:text-emerald-300 font-semibold">
                            {{ __('In: :time', ['time' => \Carbon\Carbon::parse($myAttendance->clock_in)->format('h:i A')]) }}
                        </span>
                        <form method="POST" action="{{ route('attendance.clock-out') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-2 py-0.5 rounded bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px] shadow-xs transition cursor-pointer">
                                {{ __('Clock Out') }}
                            </button>
                        </form>
                    </div>
                @else
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300">
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ __('Shift Done (:hoursh)', ['hours' => $myAttendance->total_hours]) }}</span>
                    </div>
                @endif

                <button type="button" onclick="openAttendanceModal()" 
                   class="btn-md inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>{{ __('Log Worker Attendance') }}</span>
                </button>
                <a href="{{ route('finance.expenses.index') }}" 
                   class="btn-md inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium text-xs hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition">
                    <svg class="w-3.5 h-3.5 text-slate-600 dark:text-slate-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ __('Travel & Fuel Claims') }}</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 max-w-7xl mx-auto space-y-6">

        @if (session('error') || $errors->any())
            <div class="p-3.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-semibold space-y-1">
                @if (session('error'))
                    <div>{{ session('error') }}</div>
                @endif
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if (session('status'))
            <div class="p-3.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        {{-- Stats Bar (4 Clean Metric Cards Matching Admin Standard) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            
            {{-- Assigned Projects --}}
            <button type="button" onclick="switchTechTab('projects', document.getElementById('tab-btn-projects'))" 
                    class="p-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs text-left hover:border-blue-500/50 transition group cursor-pointer">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">{{ __('Assigned Projects') }}</span>
                <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ $assignedProjects->count() }}</div>
                <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                    <span>{{ __('Active deployment sites') }}</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">&rarr;</span>
                </div>
            </button>

            {{-- Active Jobs --}}
            <button type="button" onclick="switchTechTab('jobs', document.getElementById('tab-btn-jobs'))"
                    class="p-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs text-left hover:border-indigo-500/50 transition group cursor-pointer">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">{{ __('Active Installation Jobs') }}</span>
                <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">{{ $activeJobs->count() }}</div>
                <div class="text-[11px] text-slate-400 mt-1">{{ __('Pending & in-progress work') }}</div>
            </button>

            {{-- Site Surveys --}}
            <button type="button" onclick="switchTechTab('surveys', document.getElementById('tab-btn-surveys'))"
                    class="p-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs text-left hover:border-amber-500/50 transition group cursor-pointer">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">{{ __('Pending Surveys') }}</span>
                <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition">{{ $activeSurveys->count() }}</div>
                <div class="text-[11px] text-slate-400 mt-1">{{ __('Site inspections scheduled') }}</div>
            </button>

            {{-- Completed Work --}}
            <button type="button" onclick="switchTechTab('history', document.getElementById('tab-btn-history'))"
                    class="p-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs text-left hover:border-emerald-500/50 transition group cursor-pointer">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">{{ __('Completed Work') }}</span>
                <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">{{ $completedJobs->count() + $completedSurveys->count() }}</div>
                <div class="text-[11px] text-slate-400 mt-1">{{ __('Successfully resolved tasks') }}</div>
            </button>

        </div>

        {{-- On-Site Hardware Barcode Scanner Quick Tool (Clean Unified Admin Styling) --}}
        <div class="rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.008v.008H6.75V6.75zM6.75 16.5h.008v.008H6.75V16.5zM16.5 6.75h.008v.008H16.5V6.75zM13.5 13.5h.008v.008H13.5V13.5zM13.5 19.5h.008v.008H13.5V19.5zM19.5 13.5h.008v.008H19.5V13.5zM16.5 16.5h.008v.008H16.5V16.5zM19.5 19.5h.008v.008H19.5V19.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">{{ __('On-Site Hardware Barcode Scanner') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Scan camera, NVR or terminal serial barcode to verify warranty and client allocation') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" onclick="openBarcodeScanner('tech-barcode-modal', null, handleTechScannedCode)"
                            class="btn-md inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ __('Open Camera Scanner') }}</span>
                    </button>
                    <a href="{{ route('equipment.create') }}"
                       class="btn-md inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-medium border border-slate-300 dark:border-slate-700 shadow-xs transition">
                        <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>{{ __('Register Asset') }}</span>
                    </a>
                </div>
            </div>

            {{-- Scanned Lookup Result Card --}}
            <div id="tech-scan-result" class="hidden mt-3 p-3.5 rounded-lg bg-slate-900 border border-slate-700"></div>
        </div>

        <x-barcode-scanner modalId="tech-barcode-modal" :title="__('Hardware Scanner')" />

        {{-- Unified Filter Navigation Tabs (Admin Style) --}}
        <div class="border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2 overflow-x-auto pb-px">
                <button id="tab-btn-projects" onclick="switchTechTab('projects', this)"
                        class="tech-tab-btn active px-3.5 py-2 text-xs font-semibold border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 inline-flex items-center gap-1.5 whitespace-nowrap transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/></svg>
                    <span>{{ __('Projects & Sites') }}</span>
                    @if($assignedProjects->count() > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300">
                            {{ $assignedProjects->count() }}
                        </span>
                    @endif
                </button>

                <button id="tab-btn-jobs" onclick="switchTechTab('jobs', this)"
                        class="tech-tab-btn px-3.5 py-2 text-xs font-semibold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 inline-flex items-center gap-1.5 whitespace-nowrap transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.204a4.5 4.5 0 00-6.364-6.364l-.707.707a4.5 4.5 0 006.364 6.364l.707-.707z"/></svg>
                    <span>{{ __('Installation Jobs') }}</span>
                    @if($activeJobs->count() > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300">
                            {{ $activeJobs->count() }}
                        </span>
                    @endif
                </button>

                <button id="tab-btn-surveys" onclick="switchTechTab('surveys', this)"
                        class="tech-tab-btn px-3.5 py-2 text-xs font-semibold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 inline-flex items-center gap-1.5 whitespace-nowrap transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                    <span>{{ __('Site Surveys') }}</span>
                    @if($activeSurveys->count() > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300">
                            {{ $activeSurveys->count() }}
                        </span>
                    @endif
                </button>

                <button id="tab-btn-history" onclick="switchTechTab('history', this)"
                        class="tech-tab-btn px-3.5 py-2 text-xs font-semibold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 inline-flex items-center gap-1.5 whitespace-nowrap transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ __('Completed History') }}</span>
                </button>
            </div>
        </div>

        {{-- 1. TAB: Assigned Projects & Sites --}}
        <div id="tech-tab-projects" class="tech-tab-pane space-y-4">
            @forelse($assignedProjects as $prj)
                <div class="rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                    <div class="px-4 py-3 bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $prj->title }}</h3>
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-medium bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                    {{ $prj->project_code }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ __('Type') }}: <strong class="text-slate-700 dark:text-slate-300">{{ __($prj->project_type_label) }}</strong> &bull; {{ __('Client') }}: {{ $prj->company_name ?? ($prj->site?->client_name ?? __('Client')) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold capitalize bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                {{ __(\App\Models\Project::statusOptions()[$prj->status] ?? str_replace('_', ' ', $prj->status)) }}
                            </span>
                        </div>
                    </div>

                    <div class="p-4 space-y-3">
                        @if($prj->site)
                            <div class="text-xs text-slate-600 dark:text-slate-300 flex items-start gap-1.5">
                                <span class="font-bold text-slate-700 dark:text-slate-200 shrink-0">{{ __('Site Address:') }}</span>
                                <span><strong>{{ $prj->site->name }}</strong> &mdash; {{ $prj->site->address }}, {{ $prj->site->city }} {{ $prj->site->state }}</span>
                            </div>
                        @endif

                        @if($prj->contact_person || $prj->contact_phone)
                            <div class="text-xs text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                                <span class="font-bold text-slate-700 dark:text-slate-200">{{ __('Site Contact:') }}</span>
                                <span>{{ $prj->contact_person ?? __('Manager') }}</span>
                                @if($prj->contact_phone)
                                    <a href="tel:{{ $prj->contact_phone }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">
                                        ({{ $prj->contact_phone }})
                                    </a>
                                @endif
                            </div>
                        @endif

                        {{-- Project Metrics Grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 text-xs">
                            <div>
                                <span class="text-slate-500 dark:text-slate-400 block text-[11px]">{{ __('Hardware Specs') }}</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">
                                    @if($prj->project_type === 'hardware_attendance')
                                        {{ $prj->hardware_specs['terminal_count'] ?? 0 }} {{ __('Terminals') }} &bull; {{ $prj->hardware_specs['camera_count'] ?? 0 }} {{ __('Cams') }}
                                    @elseif($prj->project_type === 'networking')
                                        {{ $prj->requirements['network_drops'] ?? __('N/A') }} {{ __('Drops') }} &bull; {{ $prj->requirements['wifi_aps'] ?? __('N/A') }} {{ __('APs') }}
                                    @elseif($prj->project_type === 'software_web')
                                        {{ $prj->requirements['tech_stack'] ?? __('Web App') }}
                                    @else
                                        {{ $prj->requirements['cctv_count'] ?? __('N/A') }} {{ __('Cams') }} &bull; {{ $prj->requirements['cabling_metres'] ?? __('N/A') }}{{ __('m') }}
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400 block text-[11px]">{{ __('BOM Materials') }}</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ __(':count Items', ['count' => $prj->materials->count()]) }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400 block text-[11px]">{{ __('IP Devices') }}</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ __(':count Configured', ['count' => $prj->ipDevices->count()]) }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400 block text-[11px]">{{ __('Assigned Role') }}</span>
                                <span class="font-bold text-blue-600 dark:text-blue-400">
                                    {{ $prj->lead_technician_id == auth()->id() ? __('Lead Engineer') : __('Technician') }}
                                </span>
                            </div>
                        </div>

                        {{-- Configured IP Devices Chips --}}
                        @if($prj->ipDevices->count() > 0)
                            <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800">
                                <div class="flex items-center justify-between text-xs mb-2">
                                    <span class="font-bold text-slate-700 dark:text-slate-300">
                                        {{ __('IP Devices (:count registered)', ['count' => $prj->ipDevices->count()]) }}
                                    </span>
                                    <a href="{{ route('projects.show', [$prj, 'tab' => 'devices']) }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                        {{ __('Manage Devices') }} &rarr;
                                    </a>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($prj->ipDevices->take(4) as $dev)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">
                                            <strong class="font-mono text-blue-600 dark:text-blue-400">{{ $dev->ip_address }}</strong>
                                            <span class="text-slate-500 truncate max-w-[150px]">{{ $dev->device_name }}</span>
                                        </span>
                                    @endforeach
                                    @if($prj->ipDevices->count() > 4)
                                        <span class="text-xs text-slate-400 self-center">{{ __('+:count more', ['count' => $prj->ipDevices->count() - 4]) }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="pt-2 flex items-center justify-between flex-wrap gap-2 border-t border-slate-100 dark:border-slate-800/80">
                            <div class="flex items-center gap-2 flex-wrap">
                                @if($prj->contact_phone)
                                    <a href="tel:{{ $prj->contact_phone }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs hover:bg-slate-50 dark:hover:bg-slate-800 shadow-xs transition">
                                        <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        <span>{{ __('Call Client') }}</span>
                                    </a>
                                @endif
                                @if($prj->site?->navigate_url)
                                    <a href="{{ $prj->site->navigate_url }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs hover:bg-slate-50 dark:hover:bg-slate-800 shadow-xs transition">
                                        <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>{{ __('GPS Directions') }}</span>
                                    </a>
                                @endif
                                <button type="button" onclick="openAttendanceModal('{{ $prj->id }}')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 text-xs font-semibold hover:bg-blue-100 dark:hover:bg-blue-900/80 shadow-xs transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    <span>{{ __('Punch Attendance') }}</span>
                                </button>
                                <a href="{{ route('projects.show', [$prj, 'tab' => 'work_days']) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs hover:bg-slate-50 dark:hover:bg-slate-800 shadow-xs transition">
                                    <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ __('Daily Attendance') }}</span>
                                </a>
                                <a href="{{ route('projects.show', [$prj, 'tab' => 'devices']) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs hover:bg-slate-50 dark:hover:bg-slate-800 shadow-xs transition">
                                    <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                                    <span>{{ __('IP Terminals') }}</span>
                                </a>
                            </div>
                            <a href="{{ route('projects.show', $prj) }}" class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 hover:underline text-xs font-semibold">
                                <span>{{ __('Open Project Workspace') }}</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 px-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <svg class="w-12 h-12 mx-auto text-slate-400 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ __('No Projects Assigned') }}</h4>
                    <p class="text-xs text-slate-400 mt-1 mb-4">{{ __('When you are assigned as lead or team technician, projects appear here.') }}</p>
                    <a href="{{ route('projects.index') }}" class="btn-secondary text-xs">
                        {{ __('Browse All Projects') }}
                    </a>
                </div>
            @endforelse
        </div>

        {{-- 2. TAB: Installation Jobs --}}
        <div id="tech-tab-jobs" class="tech-tab-pane hidden space-y-4">
            @forelse($activeJobs as $job)
                @php $lead = $job->quotation?->lead; @endphp
                <div class="rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                    <div class="px-4 py-3 bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $job->job_no ?? 'JOB-' . $job->id }}</h3>
                                <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">&bull; {{ $lead?->customer_name ?? __('N/A') }}</span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Assigned Date') }}: {{ $job->created_at->translatedFormat('d M Y') }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold capitalize bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            {{ __(ucfirst(str_replace('_', ' ', $job->status))) }}
                        </span>
                    </div>

                    <div class="p-4 space-y-3">
                        <div class="text-xs text-slate-600 dark:text-slate-300 flex items-start gap-1.5">
                            <span class="font-bold text-slate-700 dark:text-slate-200 shrink-0">{{ __('Site Address:') }}</span>
                            <span>{{ $lead?->site_address ?? $lead?->address ?? __('Address not specified') }}</span>
                        </div>

                        <div class="text-xs text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ __('Customer Phone:') }}</span>
                            @if($lead?->phone)
                                <a href="tel:{{ $lead->phone }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">{{ $lead->phone }}</a>
                            @else
                                <span>—</span>
                            @endif
                        </div>

                        @if($job->installation_notes)
                            <div class="text-xs text-slate-600 dark:text-slate-300">
                                <span class="font-bold text-slate-700 dark:text-slate-200">{{ __('Work Notes:') }}</span>
                                <span>{{ $job->installation_notes }}</span>
                            </div>
                        @endif

                        {{-- Update Status Form --}}
                        <form method="POST" action="{{ route('technician.jobs.updateStatus', $job) }}" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                            @csrf
                            <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="crm-label text-xs">{{ __('Update Status') }}</label>
                                        <select aria-label="{{ __('Update Status') }}" name="status" class="crm-select text-xs w-full">
                                            <option value="pending"   @selected($job->status === 'pending')>{{ __('Pending') }}</option>
                                            <option value="scheduled" @selected($job->status === 'scheduled')>{{ __('Scheduled') }}</option>
                                            <option value="assigned"  @selected($job->status === 'assigned')>{{ __('Assigned (In Progress)') }}</option>
                                            <option value="completed" @selected($job->status === 'completed')>{{ __('Completed') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="crm-label text-xs">{{ __('Completion Notes / Logs') }}</label>
                                        <x-tamil-input>
                                            <input aria-label="{{ __('Completion Notes / Logs') }}" type="text" name="notes" class="crm-input text-xs w-full" value="{{ $job->installation_notes }}" placeholder="{{ __('Describe installation status or findings...') }}">
                                        </x-tamil-input>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="btn-primary text-xs">
                                        {{ __('Save Status') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 px-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <svg class="w-12 h-12 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ __('No Active Installation Jobs') }}</h4>
                    <p class="text-xs text-slate-400 mt-1">{{ __('You have no pending or scheduled installation jobs assigned right now.') }}</p>
                </div>
            @endforelse
        </div>

        {{-- 3. TAB: Site Surveys --}}
        <div id="tech-tab-surveys" class="tech-tab-pane hidden space-y-4">
            @forelse($activeSurveys as $survey)
                @php $lead = $survey->lead; @endphp
                <div class="rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                    <div class="px-4 py-3 bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Site Survey: :customer', ['customer' => $lead?->customer_name]) }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Inspection Date') }}: {{ $survey->survey_date?->translatedFormat('d M Y') ?? __('Immediate') }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            {{ __('Pending Visit') }}
                        </span>
                    </div>

                    <div class="p-4 space-y-3">
                        <div class="text-xs text-slate-600 dark:text-slate-300 flex items-start gap-1.5">
                            <span class="font-bold text-slate-700 dark:text-slate-200 shrink-0">{{ __('Site Address:') }}</span>
                            <span>{{ $survey->site_address ?: ($lead?->site_address ?: __('Not specified')) }}</span>
                        </div>
                        <div class="text-xs text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ __('Contact:') }}</span>
                            <span>{{ $survey->contact_person ?: $lead?->customer_name }} ({{ $survey->contact_phone ?: $lead?->phone }})</span>
                        </div>
                        @if($survey->visit_notes)
                            <div class="text-xs text-slate-600 dark:text-slate-300">
                                <span class="font-bold text-slate-700 dark:text-slate-200">{{ __('Notes:') }}</span>
                                <span>{{ $survey->visit_notes }}</span>
                            </div>
                        @endif

                        <div class="pt-2 flex items-center justify-between flex-wrap gap-2 border-t border-slate-100 dark:border-slate-800/80">
                            <div class="flex items-center gap-2">
                                @if($survey->contact_phone || $lead?->phone)
                                    <a href="tel:{{ $survey->contact_phone ?: $lead?->phone }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs hover:bg-slate-50 dark:hover:bg-slate-800 shadow-xs transition">
                                        <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        <span>{{ __('Call') }}</span>
                                    </a>
                                @endif
                                @if($survey->site_address || $lead?->site_address)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($survey->site_address ?: $lead?->site_address) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs hover:bg-slate-50 dark:hover:bg-slate-800 shadow-xs transition">
                                        <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>{{ __('Maps') }}</span>
                                    </a>
                                @endif
                            </div>
                            <a href="{{ route('technician.surveys.show', $survey) }}" class="btn-md inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">
                                <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <span>{{ __('Conduct Survey') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 px-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <svg class="w-12 h-12 mx-auto text-slate-400 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ __('No Pending Site Surveys') }}</h4>
                    <p class="text-xs text-slate-400 mt-1">{{ __('You have no upcoming site inspection visits scheduled.') }}</p>
                </div>
            @endforelse
        </div>

        {{-- 4. TAB: Completed History --}}
        <div id="tech-tab-history" class="tech-tab-pane hidden space-y-4">
            
            {{-- Completed Jobs Card --}}
            <div class="rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="px-4 py-3 bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Completed Installation Jobs') }}</h3>
                    <span class="text-xs text-slate-500 font-semibold">{{ __(':count jobs', ['count' => $completedJobs->count()]) }}</span>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($completedJobs as $job)
                        @php $lead = $job->quotation?->lead; @endphp
                        <div class="p-3.5 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <div>
                                <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $job->job_no ?? 'JOB-' . $job->id }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $lead?->customer_name ?? __('N/A') }} &middot; {{ __('Completed') }} {{ $job->updated_at->translatedFormat('d M Y') }}</div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                {{ __('Completed') }}
                            </span>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">
                            {{ __('No completed installation jobs recorded yet.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Completed Surveys Card --}}
            <div class="rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="px-4 py-3 bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Completed Site Surveys') }}</h3>
                    <span class="text-xs text-slate-500 font-semibold">{{ __(':count surveys', ['count' => $completedSurveys->count()]) }}</span>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($completedSurveys as $survey)
                        <div class="p-3.5 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <div>
                                <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $survey->lead?->customer_name ?? __('Lead #:id', ['id' => $survey->lead_id]) }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $survey->lead?->site_address ?? __('Site') }} &middot; {{ __('Completed') }} {{ $survey->updated_at->translatedFormat('d M Y') }}</div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                {{ __('Completed') }}
                            </span>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">
                            {{ __('No completed site surveys recorded yet.') }}
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <script>
        const techScanText = {{ Js::from([
            'querying' => __('Querying database for S/N :code...'),
            'underWarranty' => __('Under Warranty'),
            'warrantyExpired' => __('Warranty Expired'),
            'customer' => __('Customer'),
            'location' => __('Location'),
            'siteAsset' => __('Site Asset'),
            'warrantyExpiry' => __('Warranty Expiry'),
            'viewHistory' => __('View Asset History'),
            'catalogItem' => __('Catalog Inventory Item'),
            'model' => __('Model'),
            'stock' => __('Stock'),
            'available' => __(':count available'),
            'installAtSite' => __('Install at Client Site'),
            'notFound' => __('No hardware found for S/N: :code'),
            'notRegistered' => __('This hardware serial has not yet been registered in the system.'),
            'registerNew' => __('Register New Asset'),
            'lookupError' => __('Error looking up serial: :error'),
            'na' => __('N/A'),
        ]) }};

        function switchTechTab(name, btn) {
            document.querySelectorAll('.tech-tab-pane').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tech-tab-btn').forEach(el => {
                el.classList.remove('active', 'border-blue-600', 'text-blue-600', 'dark:text-blue-400');
                el.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400');
            });
            const targetPane = document.getElementById('tech-tab-' + name);
            if (targetPane) targetPane.classList.remove('hidden');
            if (btn) {
                btn.classList.add('active', 'border-blue-600', 'text-blue-600', 'dark:text-blue-400');
                btn.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400');
            }
        }

        async function handleTechScannedCode(code) {
            const container = document.getElementById('tech-scan-result');
            if (!container) return;

            container.classList.remove('hidden');
            container.innerHTML = `<div class="text-slate-300 text-xs flex items-center gap-2">
                <span class="inline-block animate-spin">&#9696;</span>
                <span>${techScanText.querying.replace(':code', '<strong>' + code + '</strong>')}</span>
            </div>`;

            try {
                const response = await fetch(`{{ route('equipment.lookup') }}?serial=${encodeURIComponent(code)}`);
                const data = await response.json();

                if (data.found && data.type === 'installed_equipment') {
                    const warrantyBadge = data.is_under_warranty 
                        ? `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-900/60 text-emerald-300 border border-emerald-700">${techScanText.underWarranty}</span>`
                        : `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-900/60 text-rose-300 border border-rose-700">${techScanText.warrantyExpired}</span>`;

                    container.innerHTML = `
                        <div class="flex justify-between items-start flex-wrap gap-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1.5">
                                    <h4 class="text-sm font-bold text-white">${data.equipment_name}</h4>
                                    ${warrantyBadge}
                                </div>
                                <div class="text-xs text-slate-300 space-y-1">
                                    <div>${techScanText.customer}: <strong class="text-white">${data.customer_name}</strong> (${data.customer_phone})</div>
                                    <div>${techScanText.location}: <strong class="text-white">${data.location_tag || techScanText.siteAsset}</strong> &middot; S/N: <code class="text-blue-300">${data.serial_number}</code></div>
                                    <div>${techScanText.warrantyExpiry}: <strong>${data.mfg_warranty_expiry}</strong></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="${data.show_url}" class="border border-slate-700 text-slate-200 text-xs font-semibold px-3 py-1.5 rounded-lg transition hover:bg-slate-800">
                                    ${techScanText.viewHistory} &rarr;
                                </a>
                            </div>
                        </div>
                    `;
                } else if (data.found && data.type === 'catalog_product') {
                    container.innerHTML = `
                        <div class="flex justify-between items-center flex-wrap gap-3">
                            <div>
                                <div class="text-[10px] font-bold text-blue-300 uppercase tracking-wide">${techScanText.catalogItem}</div>
                                <h4 class="text-sm font-bold text-white mt-0.5">${data.product_name}</h4>
                                <div class="text-xs text-slate-300 mt-1">SKU: ${data.sku} &middot; ${techScanText.model}: ${data.model_no || techScanText.na} &middot; ${techScanText.stock}: <strong class="text-emerald-400">${techScanText.available.replace(':count', data.stock_quantity)}</strong></div>
                            </div>
                            <a href="{{ route('equipment.create') }}?product_id=${data.id}&serial=${encodeURIComponent(code)}" class="btn-primary text-xs">
                                ${techScanText.installAtSite} &rarr;
                            </a>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="flex justify-between items-center flex-wrap gap-3">
                            <div>
                                <div class="text-xs font-bold text-rose-400">${techScanText.notFound.replace(':code', code)}</div>
                                <div class="text-xs text-slate-400 mt-0.5">${techScanText.notRegistered}</div>
                            </div>
                            <a href="{{ route('equipment.create') }}?serial=${encodeURIComponent(code)}" class="btn-primary text-xs">
                                ${techScanText.registerNew} &rarr;
                            </a>
                        </div>
                    `;
                }
            } catch (e) {
                container.innerHTML = `<div class="text-rose-400 text-xs">${techScanText.lookupError.replace(':error', e.message)}</div>`;
            }
        }
    </script>

    {{-- TWO-STEP SPLIT GUIDED ATTENDANCE MODAL COMPONENT --}}
    <x-mark-attendance-modal :workers="$activeWorkers" :projects="$activeProjects" />
</x-app-layout>
