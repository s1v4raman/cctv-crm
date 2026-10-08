<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-[#060913]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Portfolio Analysis & Milestones | Precision IT Systems</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Compiled Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind CDN fallback for guaranteed rendering anywhere -->
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
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-[#060913] selection:bg-blue-600 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 dark:bg-[#0b1120]/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between gap-4">
            
            <!-- Brand Lockup -->
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="Precision IT Systems" class="h-10 w-10 object-contain rounded-lg shadow-xs" onerror="this.style.display='none'">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-base sm:text-lg font-heading tracking-tight text-slate-900 dark:text-white leading-tight">
                            Precision IT <span class="text-blue-600 dark:text-blue-400">Systems</span>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Live Analysis</span>
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Project Operations, Hardware &amp; Software Deliverables</p>
                </div>
            </div>

            <!-- Share & Action Controls -->
            <div class="flex items-center gap-2.5">
                <button type="button" 
                        onclick="copyShareLink()" 
                        id="copyBtn"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white shadow-xs transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-1.757l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                    </svg>
                    <span id="copyBtnText">Copy Share Link</span>
                </button>

                <button type="button" 
                        onclick="document.documentElement.classList.toggle('dark')" 
                        title="Toggle Dark Mode"
                        class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors">
                    <svg class="w-4 h-4 dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
                    <svg class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Welcome Banner -->
        <div class="rounded-2xl p-5 sm:p-6 bg-gradient-to-r from-blue-900 to-indigo-900 text-white shadow-sm relative overflow-hidden">
            <div class="relative z-10 max-w-3xl">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-white/15 text-blue-100 border border-white/20 mb-2">
                    Executive Stakeholder Review
                </span>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold font-heading tracking-tight leading-tight">
                    Project Operations &amp; Deployment Analysis
                </h1>
                <p class="mt-1.5 text-xs sm:text-sm text-blue-100/90 leading-relaxed">
                    Interactive technical portfolio review for client installations, CCTV hardware deployments, and custom software delivery. All internal financial ledgers and prospect databases remain secure and isolated.
                </p>
            </div>
        </div>

        <!-- 1. Global KPI Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5 shadow-xs">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Projects</div>
                <div class="mt-1 text-2xl font-bold font-heading text-slate-900 dark:text-white">{{ $totalProjectsCount }}</div>
                <div class="mt-1 text-[11px] text-slate-400">Entire active portfolio</div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 border-l-4 border-l-blue-500 rounded-xl p-3.5 shadow-xs">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">In Progress</div>
                <div class="mt-1 text-2xl font-bold font-heading text-blue-600 dark:text-blue-400">{{ $inProgressCount }}</div>
                <div class="mt-1 text-[11px] text-slate-400">Under active field work</div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 border-l-4 border-l-emerald-500 rounded-xl p-3.5 shadow-xs">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Completed</div>
                <div class="mt-1 text-2xl font-bold font-heading text-emerald-600 dark:text-emerald-400">{{ $completedCount }}</div>
                <div class="mt-1 text-[11px] text-slate-400">Delivered & verified</div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 border-l-4 border-l-amber-500 rounded-xl p-3.5 shadow-xs">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">On Hold / Pending</div>
                <div class="mt-1 text-2xl font-bold font-heading text-amber-600 dark:text-amber-400">{{ $incompletedCount }}</div>
                <div class="mt-1 text-[11px] text-slate-400">Awaiting client site/signoff</div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 border-l-4 border-l-purple-500 rounded-xl p-3.5 shadow-xs col-span-2 sm:col-span-1">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Portfolio Valuation</div>
                <div class="mt-1 text-2xl font-bold font-heading text-purple-600 dark:text-purple-400 font-mono">
                    ₹{{ number_format($totalValuation, 0) }}
                </div>
                <div class="mt-1 text-[11px] text-slate-400">Aggregated contractual value</div>
            </div>
        </div>

        <!-- 2. Hardware vs Software Segmentation Breakdown -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">📹 Hardware (CCTV & Security)</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400">{{ $hardwareCctvCount }} Projects</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">IP Cameras, NVR storage, PoE switches & cable infrastructure.</p>
                <div class="text-sm font-bold font-mono text-slate-900 dark:text-white">Valuation: ₹{{ number_format($hardwareValuation, 0) }}</div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">⏱ Biometric & Attendance</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">{{ $hardwareAttendanceCount }} Projects</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Fingerprint & facial recognition access control terminals.</p>
                <div class="text-sm font-bold font-mono text-slate-900 dark:text-white">Field Integration Ready</div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">💻 Custom Software & VMS</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400">{{ $softwareWebCount }} Projects</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Cloud video management, AI telemetry & client dashboards.</p>
                <div class="text-sm font-bold font-mono text-slate-900 dark:text-white">Valuation: ₹{{ number_format($softwareValuation, 0) }}</div>
            </div>
        </div>

        <!-- 3. Projects List & Filter Toolbar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
            
            <!-- Filters -->
            <div class="p-3.5 sm:p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <form method="GET" action="{{ route('projects.public-analysis') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                    <div class="relative flex-1 min-w-[240px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}" 
                               placeholder="Search company, project name, or specs…" 
                               class="w-full pl-9 pr-3.5 py-2 text-xs sm:text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    </div>

                    <select name="status" onchange="this.form.submit()" class="py-2 pl-3 pr-8 text-xs sm:text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-200">
                        <option value="all">All Statuses</option>
                        <option value="in_progress" @selected($statusFilter === 'in_progress')>In Progress</option>
                        <option value="completed" @selected($statusFilter === 'completed')>Completed</option>
                        <option value="on_hold" @selected($statusFilter === 'on_hold')>On Hold</option>
                    </select>

                    <select name="type" onchange="this.form.submit()" class="py-2 pl-3 pr-8 text-xs sm:text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-200">
                        <option value="all">All Types</option>
                        <option value="hardware_cctv" @selected($typeFilter === 'hardware_cctv')>CCTV Hardware</option>
                        <option value="hardware_attendance" @selected($typeFilter === 'hardware_attendance')>Biometric Attendance</option>
                        <option value="software_web" @selected($typeFilter === 'software_web')>Software / Web</option>
                    </select>

                    <button type="submit" class="px-3.5 py-2 text-xs sm:text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                        Filter
                    </button>

                    @if($search || $statusFilter !== 'all' || $typeFilter !== 'all')
                        <a href="{{ route('projects.public-analysis') }}" class="px-3 py-2 text-xs font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors text-center">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Table of Projects -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="py-3 px-4">Project / Client</th>
                            <th class="py-3 px-4">Type &amp; Domain</th>
                            <th class="py-3 px-4">Technical Specs</th>
                            <th class="py-3 px-4">Timeline</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Budget</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs sm:text-sm">
                        @forelse ($projects as $project)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                        <span>{{ $project->title }}</span>
                                        @if($project->project_code)
                                            <span class="font-mono text-[10px] text-slate-400 font-normal">#{{ $project->project_code }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ $project->company_name ?: ($project->lead?->customer_name ?: 'General Client') }}
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $project->project_type_badge_classes }}">
                                        {{ $project->project_type_label }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-xs text-slate-600 dark:text-slate-300">
                                    @php $specs = $project->hardware_specs ?? []; @endphp
                                    @if(in_array($project->project_type, ['hardware_cctv', 'hardware_attendance']))
                                        <div class="space-y-0.5">
                                            @if(!empty($specs['camera_count']))
                                                <span>{{ $specs['camera_count'] }} Cameras</span>
                                            @endif
                                            @if(!empty($specs['nvr_channels']))
                                                <span class="text-slate-400">• {{ $specs['nvr_channels'] }}-Ch NVR</span>
                                            @endif
                                            @if(!empty($specs['storage_retention_days']))
                                                <div class="text-[11px] text-slate-500">{{ $specs['storage_retention_days'] }} Days Backup</div>
                                            @endif
                                            @if(empty($specs['camera_count']) && empty($specs['nvr_channels']) && empty($specs['storage_retention_days']))
                                                <span class="text-xs text-slate-500">{{ Str::limit($project->description ?: 'Hardware installation scope', 45) }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-xs text-slate-600 dark:text-slate-400">
                                            {{ Str::limit($project->description ?: 'Custom deliverables and deployment scope', 45) }}
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    @if($project->deadline)
                                        <div class="font-medium text-slate-800 dark:text-slate-200">
                                            Due: {{ $project->deadline->format('d M Y') }}
                                        </div>
                                    @endif
                                    <div class="text-[11px] text-slate-400">
                                        Started: {{ $project->start_date?->format('d M Y') ?? $project->created_at->format('d M Y') }}
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @php
                                        $statusBadge = match($project->status) {
                                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-400 dark:border-emerald-800',
                                            'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-400 dark:border-blue-800',
                                            'on_hold' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-400 dark:border-amber-800',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border uppercase tracking-wider {{ $statusBadge }}">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                    @if($project->budget)
                                        ₹{{ number_format($project->budget, 0) }}
                                    @else
                                        <span class="text-slate-400 font-sans text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-500 dark:text-slate-400 text-sm">
                                    No projects matched the search or filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($projects->hasPages())
                <div class="p-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                    {{ $projects->links() }}
                </div>
            @endif
        </div>

        <!-- Privacy & Security Guarantee -->
        <div class="p-4 rounded-xl bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                <span><strong>Protected Read-Only View:</strong> Customer billing ledgers, staff payroll, and confidential leads are isolated and excluded.</span>
            </div>
            <span class="font-mono text-[11px] text-slate-400">Precision IT Systems • Operations Review</span>
        </div>
    </main>

    <!-- Copy Link Notification Script -->
    <script>
    function copyShareLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            const btnText = document.getElementById('copyBtnText');
            const originalText = btnText.innerText;
            btnText.innerText = 'Copied to Clipboard! ✓';
            setTimeout(() => {
                btnText.innerText = originalText;
            }, 2500);
        }).catch(() => {
            prompt('Copy this link:', window.location.href);
        });
    }
    </script>
</body>
</html>
