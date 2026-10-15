<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#222222]">
                    Field Engineer Workstation
                </h1>
                <div class="mt-0.5 text-xs text-[#555555]">Welcome, {{ auth()->user()->name }}</div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('finance.expenses.index') }}" 
                   class="inline-flex items-center px-3 py-1.5 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium text-xs hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    Travel &amp; Fuel Claims
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .tech-wrap {
            padding: 1.5rem 0 3rem;
        }
        .tech-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Tab System */
        .tab-bar {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 0;
            overflow-x: auto;
        }
        .tab-btn {
            padding: 0.65rem 1rem;
            font-size: 0.82rem;
            font-weight: 500;
            border: none;
            background: none;
            cursor: pointer;
            color: #64748b;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all 0.2s;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .tab-btn.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
            font-weight: 700;
        }
        .tab-btn:hover:not(.active) {
            color: var(--text-main);
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Card System */
        .tech-card {
            background: #ffffff;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            margin-bottom: 1.25rem;
        }
        .card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }
        .card-head-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .card-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-main);
        }
        .card-sub {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.1rem;
        }
        .card-body {
            padding: 1.25rem;
        }

        /* Statuses */
        .status-text {
            font-weight: 700;
            font-size: 0.75rem;
        }

        /* Info rows */
        .info-row {
            display: flex;
            gap: 0.5rem;
            align-items: flex-start;
            margin-bottom: 0.5rem;
            font-size: 0.82rem;
            color: var(--card-border);
        }
        .info-label {
            font-weight: 600;
            color: #64748b;
            min-width: 6.5rem;
        }

        /* Form area */
        .update-form {
            background: #f8fafc;
            border-radius: 4px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid #e2e8f0;
        }
        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.35rem;
            display: block;
        }
        .form-select, .form-textarea, .form-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 0.55rem 0.8rem;
            font-size: 0.82rem;
            color: var(--text-main);
            outline: none;
            background: #ffffff;
            box-sizing: border-box;
        }
        .form-textarea {
            min-height: 60px;
            resize: vertical;
            font-family: inherit;
        }

        .btn-submit {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #ffffff;
            color: var(--card-border);
        }
        .btn-submit:hover {
            background: #f8fafc;
            color: var(--text-main);
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 0.75rem 1rem;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
        }
        .empty-state svg {
            width: 2.5rem;
            height: 2.5rem;
            margin: 0 auto 0.75rem;
            color: var(--text-muted);
        }
        .empty-state h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.3rem;
        }

        html.dark .tab-bar { border-bottom-color: var(--text-main); }
        html.dark .tab-btn { color: var(--text-muted); }
        html.dark .tab-btn.active { color: #3b82f6; border-bottom-color: #3b82f6; }
        html.dark .tab-btn:hover:not(.active) { color: var(--text-main); }

        html.dark .tech-card {
            background: #0f172a;
            border-color: var(--text-main);
            color: var(--text-main);
        }
        html.dark .card-head {
            background: #0f172a;
            border-bottom-color: var(--text-main);
        }
        html.dark .card-title {
            color: #ffffff;
        }
        html.dark .card-sub {
            color: var(--text-muted);
        }
        html.dark .info-row {
            color: var(--text-muted);
        }
        html.dark .info-label {
            color: var(--text-muted);
        }
        html.dark .update-form {
            background: #0b1120;
            border-color: var(--text-main);
        }
        html.dark .form-label {
            color: var(--text-muted);
        }
        html.dark .form-select,
        html.dark .form-textarea,
        html.dark .form-input {
            background: #060913;
            border-color: var(--card-border);
            color: #ffffff;
        }
        html.dark .empty-state h4 {
            color: #ffffff;
        }
    </style>

    <div class="tech-wrap">
        <div class="tech-inner">

            @if (session('status'))
                <div class="alert-success">{{ session('status') }}</div>
            @endif

            {{-- Stats Bar (4 Clean Metric Cards) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                
                {{-- Infrastructure Projects & ACS Sites --}}
                <div onclick="switchTab('projects', document.querySelector('.tab-bar .tab-btn:first-child'))" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 cursor-pointer">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Assigned Projects</span>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $assignedProjects->count() }}</div>
                </div>

                {{-- Active Jobs --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Active Installation Jobs</span>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $activeJobs->count() }}</div>
                </div>

                {{-- Site Surveys --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Pending Surveys</span>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $activeSurveys->count() }}</div>
                </div>

                {{-- Resolved / Done --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Completed Work</span>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $completedJobs->count() + $completedSurveys->count() }}</div>
                </div>

            </div>

            {{-- On-Site Hardware Barcode Scanner Quick Tool --}}
            <div class="bg-white border border-[#dddddd] rounded p-4 mb-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-[#222222]">On-Site Hardware Barcode Scanner</h3>
                        <p class="text-xs text-[#555555] mt-0.5">Scan serial barcode to check warranty and asset details</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" onclick="openBarcodeScanner('tech-barcode-modal', null, handleTechScannedCode)"
                                class="inline-flex items-center px-3 py-1.5 rounded bg-[#2563eb] hover:bg-[#1d4ed8] text-white text-sm font-normal transition cursor-pointer">
                            Open Camera Scanner
                        </button>
                        <a href="{{ route('equipment.create') }}"
                           class="inline-flex items-center px-3 py-1.5 rounded bg-white hover:bg-[#f5f5f5] text-[#222222] text-sm font-normal border border-[#cccccc] transition">
                            Register Asset
                        </a>
                    </div>
                </div>

                {{-- Scanned Lookup Result Card (Dynamically Rendered via JS) --}}
                <div id="tech-scan-result" class="hidden mt-4 p-3 rounded bg-slate-900 border border-slate-700"></div>
            </div>

            <x-barcode-scanner modalId="tech-barcode-modal" title="Hardware Scanner" />

            {{-- Tab Bar --}}
            <div class="tab-bar">
                <button class="tab-btn active" onclick="switchTab('projects', this)">
                    Projects &amp; Sites
                    @if($assignedProjects->count() > 0)
                        <span class="font-bold text-blue-600 dark:text-blue-400 ml-1">({{ $assignedProjects->count() }})</span>
                    @endif
                </button>
                <button class="tab-btn" onclick="switchTab('jobs', this)">
                    Installation Jobs
                    @if($activeJobs->count() > 0)
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 ml-1">({{ $activeJobs->count() }})</span>
                    @endif
                </button>
                <button class="tab-btn" onclick="switchTab('surveys', this)">
                    Site Surveys
                    @if($activeSurveys->count() > 0)
                        <span class="font-bold text-blue-600 dark:text-blue-400 ml-1">({{ $activeSurveys->count() }})</span>
                    @endif
                </button>
                <button class="tab-btn" onclick="switchTab('history', this)">
                    History
                </button>
            </div>

            {{-- Tab: Assigned Projects & Sites --}}
            <div id="tab-projects" class="tab-content active">
                @forelse($assignedProjects as $prj)
                    <div class="tech-card">
                        <div class="card-head">
                            <div class="card-head-left">
                                <div>
                                    <div class="card-title flex items-center gap-2 flex-wrap">
                                        <span>{{ $prj->title }}</span>
                                        <span class="text-xs font-mono font-medium text-slate-500">
                                            ({{ $prj->project_code }})
                                        </span>
                                    </div>
                                    <div class="card-sub">
                                        Type: <strong>{{ $prj->project_type_label }}</strong> &bull; Client: {{ $prj->company_name ?? ($prj->site?->client_name ?? 'Client') }}
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="font-bold text-xs capitalize text-blue-600 dark:text-blue-400">{{ str_replace('_', ' ', $prj->status) }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($prj->site)
                                <div class="info-row">
                                    <span class="info-label">Site:</span>
                                    <span><strong>{{ $prj->site->name }}</strong> ({{ $prj->site->address }}, {{ $prj->site->city }})</span>
                                </div>
                            @endif

                            @if($prj->contact_person || $prj->contact_phone)
                                <div class="info-row">
                                    <span class="info-label">Contact:</span>
                                    <span>{{ $prj->contact_person }} @if($prj->contact_phone) (<a href="tel:{{ $prj->contact_phone }}" class="text-blue-600 hover:underline">{{ $prj->contact_phone }}</a>) @endif</span>
                                </div>
                            @endif

                            {{-- Project Metrics Snapshot --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 my-3 p-3 rounded bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 text-xs">
                                <div>
                                    <span class="text-slate-500 block text-[11px]">Hardware Specs</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">
                                        @if($prj->project_type === 'hardware_attendance')
                                            {{ $prj->hardware_specs['terminal_count'] ?? 0 }} Terminals &bull; {{ $prj->hardware_specs['camera_count'] ?? 0 }} Cams
                                        @elseif($prj->project_type === 'networking')
                                            {{ $prj->requirements['network_drops'] ?? 'N/A' }} Drops &bull; {{ $prj->requirements['wifi_aps'] ?? 'N/A' }} APs
                                        @elseif($prj->project_type === 'software_web')
                                            {{ $prj->requirements['tech_stack'] ?? 'Web App' }}
                                        @else
                                            {{ $prj->requirements['cctv_count'] ?? 'N/A' }} Cams &bull; {{ $prj->requirements['cabling_metres'] ?? 'N/A' }}m
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block text-[11px]">BOM Materials</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $prj->materials->count() }} Items</span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block text-[11px]">IP Devices</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $prj->ipDevices->count() }} Configured</span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block text-[11px]">Role</span>
                                    <span class="font-bold text-blue-600 dark:text-blue-400">
                                        {{ $prj->lead_technician_id == auth()->id() ? 'Lead Engineer' : 'Technician' }}
                                    </span>
                                </div>
                            </div>

                            @if($prj->ipDevices->count() > 0)
                                <div class="mt-3 p-3 rounded bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800">
                                    <div class="flex items-center justify-between text-xs mb-2">
                                        <span class="font-bold text-slate-700 dark:text-slate-300">
                                            Devices ({{ $prj->ipDevices->count() }} registered)
                                        </span>
                                        <a href="{{ route('projects.show', [$prj, 'tab' => 'devices']) }}" class="text-xs text-blue-600 hover:underline">
                                            Manage Devices &rarr;
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
                                            <span class="text-xs text-slate-400 self-center">+{{ $prj->ipDevices->count() - 4 }} more</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($prj->contact_phone)
                                        <a href="tel:{{ $prj->contact_phone }}" class="px-2.5 py-1 rounded border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                            Call Client
                                        </a>
                                    @endif
                                    @if($prj->site?->navigate_url)
                                        <a href="{{ $prj->site->navigate_url }}" target="_blank" class="px-2.5 py-1 rounded border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                            GPS Directions
                                        </a>
                                    @endif
                                    <a href="{{ route('projects.show', [$prj, 'tab' => 'work_days']) }}" class="px-2.5 py-1 rounded border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                        Daily Attendance
                                    </a>
                                    <a href="{{ route('projects.show', [$prj, 'tab' => 'devices']) }}" class="px-2.5 py-1 rounded border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                        IP Terminals
                                    </a>
                                </div>
                                <a href="{{ route('projects.show', $prj) }}" class="text-blue-600 hover:underline text-xs font-semibold">
                                    View Project Details &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <h4>No Projects Assigned</h4>
                        <p class="text-xs text-slate-400 mb-4">When assigned to a project, it will appear here.</p>
                        <div class="flex justify-center gap-3">
                            <a href="{{ route('projects.index') }}" class="px-3 py-1.5 rounded border border-slate-300 dark:border-slate-700 text-xs">
                                Browse All Projects
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Tab: Site Surveys --}}
            <div id="tab-surveys" class="tab-content">
                @forelse($activeSurveys as $survey)
                    @php $lead = $survey->lead; @endphp
                    <div class="tech-card">
                        <div class="card-head">
                            <div class="card-head-left">
                                <div>
                                    <div class="card-title">Site Survey: {{ $lead->customer_name }}</div>
                                    <div class="card-sub">Inspection Date: {{ $survey->survey_date->format('d M Y') }}</div>
                                </div>
                            </div>
                            <span class="font-bold text-amber-600 dark:text-amber-400 text-xs">Pending Visit</span>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="info-label">Address:</span>
                                <span>{{ $survey->site_address ?: ($lead->site_address ?: 'Not specified') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Contact:</span>
                                <span>{{ $survey->contact_person ?: $lead->customer_name }} ({{ $survey->contact_phone ?: $lead->phone }})</span>
                            </div>
                            @if($survey->visit_notes)
                                <div class="info-row">
                                    <span class="info-label">Note:</span>
                                    <span>{{ $survey->visit_notes }}</span>
                                </div>
                            @endif

                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    @if($survey->contact_phone || $lead->phone)
                                        <a href="tel:{{ $survey->contact_phone ?: $lead->phone }}" class="px-2.5 py-1 rounded border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs hover:bg-slate-50 transition">
                                            Call
                                        </a>
                                    @endif
                                    @if($survey->site_address || $lead->site_address)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($survey->site_address ?: $lead->site_address) }}" target="_blank" class="px-2.5 py-1 rounded border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs hover:bg-slate-50 transition">
                                            Maps
                                        </a>
                                    @endif
                                </div>
                                <a href="{{ route('technician.surveys.show', $survey) }}" class="px-3 py-1.5 rounded bg-[#2563eb] text-white text-xs font-medium hover:bg-blue-700 transition">
                                    Conduct Survey &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <h4>No Pending Site Surveys</h4>
                        <p class="text-xs text-slate-400">You have no upcoming site inspection visits scheduled.</p>
                    </div>
                @endforelse
            </div>



            {{-- Tab: Installation Jobs --}}
            <div id="tab-jobs" class="tab-content">
                @forelse($activeJobs as $job)
                    @php
                        $lead = $job->quotation?->lead;
                    @endphp
                    <div class="tech-card">
                        <div class="card-head">
                            <div class="card-head-left">
                                <div>
                                    <div class="card-title">{{ $job->job_no ?? 'JOB-' . $job->id }}</div>
                                    <div class="card-sub">{{ $lead?->customer_name ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <span class="font-bold text-xs capitalize text-blue-600">{{ ucfirst($job->status) }}</span>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="info-label">Site:</span>
                                <span>{{ $lead?->site_address ?? $lead?->address ?? 'Address not specified' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Contact:</span>
                                @if($lead?->phone)
                                    <a href="tel:{{ $lead->phone }}" class="text-blue-600 hover:underline">{{ $lead->phone }}</a>
                                @else
                                    <span>—</span>
                                @endif
                            </div>
                            <div class="info-row">
                                <span class="info-label">Assigned:</span>
                                <span>{{ $job->created_at->format('d M Y') }}</span>
                            </div>
                            @if($job->installation_notes)
                                <div class="info-row">
                                    <span class="info-label">Notes:</span>
                                    <span>{{ $job->installation_notes }}</span>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('technician.jobs.updateStatus', $job) }}">
                                @csrf
                                <div class="update-form">
                                    <label class="form-label">Update Status</label>
                                    <select name="status" class="form-select mb-3" style="height: 38px;">
                                        <option value="pending"   @selected($job->status === 'pending')>Pending</option>
                                        <option value="scheduled" @selected($job->status === 'scheduled')>Scheduled</option>
                                        <option value="assigned"  @selected($job->status === 'assigned')>Assigned (In Progress)</option>
                                        <option value="completed" @selected($job->status === 'completed')>Completed</option>
                                    </select>
                                    <label class="form-label">Completion Notes (optional)</label>
                                    <textarea name="notes" class="form-textarea mb-3" placeholder="Describe what was installed, tested, or any issues…">{{ $job->installation_notes }}</textarea>
                                                                     <div class="flex items-center justify-between mt-3 flex-wrap gap-2">
                                        <button type="submit" class="btn-submit">
                                            Save Status
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="tech-card">
                        <div class="empty-state">
                            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4>No Active Jobs</h4>
                            <p class="text-xs text-slate-400">You have no pending or scheduled installation jobs at the moment.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Tab: History --}}
            <div id="tab-history" class="tab-content">

                {{-- Completed Jobs --}}
                <div class="tech-card">
                    <div class="card-head">
                        <div class="card-head-left">
                            <div>
                                <div class="card-title">Completed Installation Jobs</div>
                                <div class="card-sub">{{ $completedJobs->count() }} jobs done</div>
                            </div>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($completedJobs as $job)
                            @php $lead = $job->quotation?->lead; @endphp
                            <div class="p-3 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <div>
                                    <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $job->job_no ?? 'JOB-' . $job->id }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $lead?->customer_name ?? 'N/A' }} &middot; {{ $job->updated_at->format('d M Y') }}</div>
                                </div>
                                <span class="font-bold text-xs text-emerald-600 dark:text-emerald-400">Completed</span>
                            </div>
                        @empty
                            <div class="p-4 text-center text-xs text-slate-400">
                                No completed installation jobs yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Completed Site Surveys --}}
                <div class="tech-card">
                    <div class="card-head">
                        <div class="card-head-left">
                            <div>
                                <div class="card-title">Completed Site Surveys</div>
                                <div class="card-sub">{{ $completedSurveys->count() }} surveys completed</div>
                            </div>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($completedSurveys as $survey)
                            <div class="p-3 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <div>
                                    <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $survey->lead?->customer_name ?? 'Lead #' . $survey->lead_id }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $survey->lead?->site_address ?? 'Site' }} &middot; Completed {{ $survey->updated_at->format('d M Y') }}</div>
                                </div>
                                <span class="font-bold text-xs text-emerald-600 dark:text-emerald-400">Completed</span>
                            </div>
                        @empty
                            <div class="p-4 text-center text-xs text-slate-400">
                                No completed site surveys yet.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script>
        function switchTab(name, btn) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + name).classList.add('active');
            btn.classList.add('active');
        }

        async function handleTechScannedCode(code) {
            const container = document.getElementById('tech-scan-result');
            if (!container) return;

            container.classList.remove('hidden');
            container.innerHTML = `<div class="text-slate-300 text-xs">
                Querying database for S/N <strong>${code}</strong>...
            </div>`;

            try {
                const response = await fetch(`{{ route('equipment.lookup') }}?serial=${encodeURIComponent(code)}`);
                const data = await response.json();

                if (data.found && data.type === 'installed_equipment') {
                    const warrantyBadge = data.is_under_warranty 
                        ? `<span class="text-emerald-400 text-xs font-bold">Under Warranty</span>`
                        : `<span class="text-rose-400 text-xs font-bold">Warranty Expired</span>`;

                    container.innerHTML = `
                        <div class="flex justify-between items-start flex-wrap gap-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="text-sm font-bold text-white">${data.equipment_name}</h4>
                                    ${warrantyBadge}
                                </div>
                                <div class="text-xs text-slate-300 space-y-0.5">
                                    <div>Customer: <strong class="text-white">${data.customer_name}</strong> (${data.customer_phone})</div>
                                    <div>Location: <strong class="text-white">${data.location_tag || 'Site Asset'}</strong> &middot; S/N: <code class="text-blue-300">${data.serial_number}</code></div>
                                    <div>Warranty Ends: <strong>${data.mfg_warranty_expiry}</strong></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="${data.show_url}" class="border border-slate-700 text-slate-200 text-xs font-medium px-3 py-1.5 rounded transition hover:bg-slate-800">
                                    View Asset History
                                </a>
                            </div>
                        </div>
                    `;
                } else if (data.found && data.type === 'catalog_product') {
                    container.innerHTML = `
                        <div class="flex justify-between items-center flex-wrap gap-3">
                            <div>
                                <div class="text-[10px] font-bold text-blue-300 uppercase">Catalog Product</div>
                                <h4 class="text-sm font-bold text-white mt-0.5">${data.product_name}</h4>
                                <div class="text-xs text-slate-300">SKU: ${data.sku} &middot; Model: ${data.model_no || 'N/A'} &middot; Stock: <strong class="text-emerald-400">${data.stock_quantity} in stock</strong></div>
                            </div>
                            <a href="{{ route('equipment.create') }}?product_id=${data.id}&serial=${encodeURIComponent(code)}" class="bg-[#2563eb] text-white text-xs font-medium px-3.5 py-1.5 rounded transition">
                                Install at Client Site
                            </a>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="flex justify-between items-center flex-wrap gap-3">
                            <div>
                                <div class="text-xs font-bold text-rose-400">No record found for S/N: ${code}</div>
                                <div class="text-xs text-slate-300">This hardware serial has not yet been registered.</div>
                            </div>
                            <a href="{{ route('equipment.create') }}?serial=${encodeURIComponent(code)}" class="bg-[#2563eb] text-white text-xs font-medium px-3.5 py-1.5 rounded transition">
                                Register New Asset
                            </a>
                        </div>
                    `;
                }
            } catch (e) {
                container.innerHTML = `<div class="text-rose-400 text-xs">Error looking up serial: ${e.message}</div>`;
            }
        }
    </script>
</x-app-layout>
