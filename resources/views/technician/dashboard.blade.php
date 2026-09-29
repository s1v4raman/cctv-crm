<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight font-heading">
                        Field Engineer Workstation
                    </h2>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Active Shifts
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Welcome back, <strong class="text-slate-800 dark:text-slate-200 font-semibold">{{ auth()->user()->name }}</strong> — here are your real-time on-site assignments.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('finance.expenses.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300 font-bold text-xs shadow-xs hover:bg-purple-100 transition">
                    <span>🛵 Travel &amp; Fuel Claims</span>
                </a>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-[#2563eb] dark:text-blue-400 font-bold text-xs shadow-xs">
                    <span>⚡ FIELD TECHNICIAN</span>
                </span>
            </div>
        </div>
    </x-slot>

    <style>
        .tech-wrap {
            background: #f8fafc;
            min-height: calc(100vh - 4.5rem);
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
            padding: 0.65rem 1.25rem;
            font-size: 0.85rem;
            font-weight: 600;
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
            color: #1e293b;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Card System */
        .tech-card {
            background: #ffffff;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 1.25rem;
            transition: all 0.2s ease;
        }
        .tech-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.06);
        }
        .card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
        }
        .card-head-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .card-icon {
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        .card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
        }
        .card-sub {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 0.1rem;
        }
        .card-body {
            padding: 1.25rem;
        }

        /* Status Badges */
        .badge {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .badge-pending     { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .badge-scheduled   { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
        .badge-assigned    { background: #f5f3ff; color: #7c3aed; border: 1px solid #ede9fe; }
        .badge-in_progress { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }
        .badge-completed   { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-resolved    { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-cancelled   { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }

        .priority-badge {
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .p-critical { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }
        .p-high     { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .p-medium   { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }
        .p-low      { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        /* Info rows */
        .info-row {
            display: flex;
            gap: 0.5rem;
            align-items: flex-start;
            margin-bottom: 0.5rem;
            font-size: 0.82rem;
            color: #334155;
        }
        .info-label {
            font-weight: 600;
            color: #64748b;
            min-width: 6.5rem;
        }

        /* Form area */
        .update-form {
            background: #f8fafc;
            border-radius: 0.75rem;
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
            border-radius: 0.6rem;
            padding: 0.55rem 0.8rem;
            font-size: 0.82rem;
            color: #0f172a;
            outline: none;
            background: #ffffff;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        .form-select:focus, .form-textarea:focus, .form-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .form-textarea {
            min-height: 60px;
            resize: vertical;
            font-family: inherit;
        }

        .btn-submit {
            padding: 0.55rem 1.25rem;
            font-size: 0.82rem;
            font-weight: 700;
            border: none;
            border-radius: 0.6rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .btn-green  { background: #059669; color: #ffffff; }
        .btn-green:hover  { background: #047857; }
        .btn-blue { background: #2563eb; color: #ffffff; }
        .btn-blue:hover { background: #1d4ed8; }

        /* Alert */
        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
            font-weight: 600;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }
        .empty-state svg {
            width: 3rem;
            height: 3rem;
            margin: 0 auto 0.75rem;
            color: #cbd5e1;
        }
        .empty-state h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.3rem;
        }

        html.dark .tech-wrap { background: #060913; }
        html.dark .tab-bar { border-bottom-color: #1e293b; }
        html.dark .tab-btn { color: #94a3b8; }
        html.dark .tab-btn.active { color: #3b82f6; border-bottom-color: #3b82f6; }
        html.dark .tab-btn:hover:not(.active) { color: #f8fafc; }

        html.dark .tech-card {
            background: #0f172a;
            border-color: #1e293b;
            color: #f8fafc;
        }
        html.dark .tech-card:hover {
            border-color: #334155;
        }
        html.dark .card-head {
            background: #0f172a;
            border-bottom-color: #1e293b;
        }
        html.dark .card-title {
            color: #ffffff;
        }
        html.dark .card-sub {
            color: #94a3b8;
        }
        html.dark .info-row {
            color: #cbd5e1;
        }
        html.dark .info-label {
            color: #94a3b8;
        }
        html.dark .update-form {
            background: #0b1120;
            border-color: #1e293b;
        }
        html.dark .form-label {
            color: #cbd5e1;
        }
        html.dark .form-select,
        html.dark .form-textarea,
        html.dark .form-input {
            background: #060913;
            border-color: #334155;
            color: #ffffff;
        }
        html.dark .empty-state h4 {
            color: #ffffff;
        }

        a.phone-link {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }
        a.phone-link:hover { text-decoration: underline; }
    </style>

    <div class="tech-wrap">
        <div class="tech-inner">

            @if (session('status'))
                <div class="alert-success">✅ {{ session('status') }}</div>
            @endif

            {{-- Stats Bar (4 Clean Cards with Pastel Accents) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
                
                {{-- Active Repairs --}}
                <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold">
                            🚨
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-900/50">
                            Urgent
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $activeTickets->count() }}</div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Active Repairs &middot; Breakdown tickets</div>
                    </div>
                </div>

                {{-- Active Jobs --}}
                <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-[#2563eb] dark:text-blue-400 flex items-center justify-center font-bold">
                            🏗️
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50">
                            In Field
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $activeJobs->count() }}</div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Active Jobs &middot; Pending / Scheduled</div>
                    </div>
                </div>

                {{-- AMC Visits Due --}}
                <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                            🔧
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50">
                            Maintenance
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $activeVisits->count() }}</div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">AMC Visits Due &middot; Awaiting service</div>
                    </div>
                </div>

                {{-- Resolved / Done --}}
                <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                            ✓
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50">
                            Completed
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $completedJobs->count() + $completedVisits->count() + $completedTickets->count() }}</div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Resolved Tasks &middot; Total Completed</div>
                    </div>
                </div>

            </div>

            {{-- On-Site Mobile Barcode & Serial Scanner Quick Tool --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 mb-6 shadow-sm">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 flex items-center justify-center text-2xl flex-shrink-0">
                            📷
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">On-Site Hardware Barcode Scanner</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Scan camera/NVR serial barcode or QR to check warranty, view history, or raise an RMA claim</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <button type="button" onclick="openBarcodeScanner('tech-barcode-modal', null, handleTechScannedCode)"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-colors">
                            📸 Open Camera Scanner
                        </button>
                        <a href="{{ route('equipment.create') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition-colors">
                            + Register Asset
                        </a>
                    </div>
                </div>

                {{-- Scanned Lookup Result Card (Dynamically Rendered via JS) --}}
                <div id="tech-scan-result" class="hidden mt-4 p-4 rounded-xl bg-slate-900/95 border border-slate-700"></div>
            </div>

            <x-barcode-scanner modalId="tech-barcode-modal" title="📸 On-Site CCTV Hardware Scanner" />

            {{-- Tab Bar --}}
            <div class="tab-bar">
                <button class="tab-btn active" onclick="switchTab('tickets', this)">
                    🚨 Repair Tickets
                    @if($activeTickets->count() > 0)
                        <span class="bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $activeTickets->count() }}</span>
                    @endif
                </button>
                <button class="tab-btn" onclick="switchTab('surveys', this)">
                    📐 Site Surveys
                    @if($activeSurveys->count() > 0)
                        <span class="bg-blue-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $activeSurveys->count() }}</span>
                    @endif
                </button>
                <button class="tab-btn" onclick="switchTab('jobs', this)">
                    🏗️ Installation Jobs
                    @if($activeJobs->count() > 0)
                        <span class="bg-indigo-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $activeJobs->count() }}</span>
                    @endif
                </button>
                <button class="tab-btn" onclick="switchTab('visits', this)">
                    🔧 AMC Visits
                    @if($activeVisits->count() > 0)
                        <span class="bg-amber-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $activeVisits->count() }}</span>
                    @endif
                </button>
                <button class="tab-btn" onclick="switchTab('history', this)">
                    📋 History
                </button>
            </div>

            {{-- Tab: Site Surveys --}}
            <div id="tab-surveys" class="tab-content">
                @forelse($activeSurveys as $survey)
                    @php $lead = $survey->lead; @endphp
                    <div class="tech-card">
                        <div class="card-head">
                            <div class="card-head-left">
                                <div class="card-icon bg-purple-50 text-purple-600">📐</div>
                                <div>
                                    <div class="card-title">Site Survey: {{ $lead->customer_name }}</div>
                                    <div class="card-sub">Inspection Date: {{ $survey->survey_date->format('d M Y') }}</div>
                                </div>
                            </div>
                            <span class="badge badge-pending">Pending Visit</span>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="info-label">📍 Address:</span>
                                <span>{{ $survey->site_address ?: ($lead->site_address ?: 'Not specified') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">👤 Contact:</span>
                                <span>{{ $survey->contact_person ?: $lead->customer_name }} ({{ $survey->contact_phone ?: $lead->phone }})</span>
                            </div>
                            @if($survey->visit_notes)
                                <div class="info-row">
                                    <span class="info-label">📝 Admin Note:</span>
                                    <span>{{ $survey->visit_notes }}</span>
                                </div>
                            @endif

                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    @if($survey->contact_phone || $lead->phone)
                                        <a href="tel:{{ $survey->contact_phone ?: $lead->phone }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-semibold hover:bg-blue-100 transition-colors">
                                            📞 Call
                                        </a>
                                    @endif
                                    @if($survey->site_address || $lead->site_address)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($survey->site_address ?: $lead->site_address) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition-colors">
                                            📍 Maps
                                        </a>
                                    @endif
                                </div>
                                <a href="{{ route('technician.surveys.show', $survey) }}" class="btn-submit btn-blue">
                                    📐 Conduct Survey & Upload Photos →
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

            {{-- Tab: Service & Repair Tickets --}}
            <div id="tab-tickets" class="tab-content active">
                @forelse($activeTickets as $ticket)
                    @php $lead = $ticket->lead; @endphp
                    <div class="tech-card">
                        <div class="card-head">
                            <div class="card-head-left">
                                <div class="card-icon bg-rose-50 text-rose-600">🚨</div>
                                <div>
                                    <div class="card-title">{{ $ticket->ticket_no }} — {{ $ticket->title }}</div>
                                    <div class="card-sub">{{ $lead->customer_name }} &middot; {{ $ticket->issue_type_label }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="priority-badge p-{{ $ticket->priority }}">{{ $ticket->priority }}</span>
                                <span class="badge badge-{{ $ticket->status }}">{{ $ticket->status_label }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="info-label">📍 Site Address:</span>
                                <span>
                                    {{ $lead->site_address }}
                                    @if($lead->site_address)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($lead->site_address) }}" target="_blank" class="ml-2 inline-flex items-center gap-1 text-xs font-semibold text-[#2563eb] hover:underline">
                                            🗺️ Directions &rarr;
                                        </a>
                                    @endif
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">📞 Customer:</span>
                                <a href="tel:{{ $lead->phone }}" class="phone-link">📞 {{ $lead->phone }}</a>
                            </div>
                            <div class="info-row">
                                <span class="info-label">📅 Schedule:</span>
                                <span>{{ $ticket->scheduled_date?->format('d M Y') ?? 'Not scheduled' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">⚠️ Complaint:</span>
                                <span class="text-slate-700 font-medium bg-rose-50 p-2.5 rounded-xl border border-rose-100 flex-1">{{ $ticket->description }}</span>
                            </div>

                            <form method="POST" action="{{ route('technician.service-tickets.update', $ticket) }}">
                                @csrf
                                <div class="update-form">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <label class="form-label">Ticket Status</label>
                                            <select name="status" class="form-select font-semibold">
                                                <option value="in_progress" @selected($ticket->status === 'in_progress')>🟡 In Progress / Investigating</option>
                                                <option value="resolved" @selected($ticket->status === 'resolved')>🟢 Resolved / Fixed</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label">Replacement Parts Used (Optional)</label>
                                            <input type="text" name="parts_replaced" value="{{ $ticket->parts_replaced }}" class="form-input" placeholder="e.g. 1x Power Supply, 2x BNC Connectors">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Troubleshooting Observations & Root Cause</label>
                                        <textarea name="troubleshooting_notes" class="form-textarea" placeholder="Diagnosis, voltage tests, cable check, root cause...">{{ $ticket->troubleshooting_notes }}</textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Resolution / Action Taken</label>
                                        <textarea name="resolution_notes" class="form-textarea" placeholder="Actions taken to restore video feed, recording, or power...">{{ $ticket->resolution_notes }}</textarea>
                                    </div>

                                    <div class="flex items-center justify-between mt-3 flex-wrap gap-2">
                                        <button type="submit" class="btn-submit btn-green">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Save Status Update
                                        </button>
                                        <a href="{{ route('jcr.create-ticket', $ticket) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#2563eb] hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-colors">
                                            ✍️ Customer Sign-off (JCR) &rarr;
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="tech-card">
                        <div class="empty-state">
                            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4>No Active Repair Tickets</h4>
                            <p class="text-xs text-slate-400">You have no pending breakdown repair tickets assigned.</p>
                        </div>
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
                                <div class="card-icon bg-blue-50 text-[#2563eb]">🏗️</div>
                                <div>
                                    <div class="card-title">{{ $job->job_no ?? 'JOB-' . $job->id }}</div>
                                    <div class="card-sub">{{ $lead?->customer_name ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <span class="badge badge-{{ $job->status }}">{{ ucfirst($job->status) }}</span>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="info-label">📍 Site:</span>
                                <span>{{ $lead?->site_address ?? $lead?->address ?? 'Address not specified' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">📞 Contact:</span>
                                @if($lead?->phone)
                                    <a href="tel:{{ $lead->phone }}" class="phone-link">{{ $lead->phone }}</a>
                                @else
                                    <span>—</span>
                                @endif
                            </div>
                            <div class="info-row">
                                <span class="info-label">📅 Assigned:</span>
                                <span>{{ $job->created_at->format('d M Y') }}</span>
                            </div>
                            @if($job->installation_notes)
                                <div class="info-row">
                                    <span class="info-label">📝 Notes:</span>
                                    <span>{{ $job->installation_notes }}</span>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('technician.jobs.updateStatus', $job) }}">
                                @csrf
                                <div class="update-form">
                                    <label class="form-label">Update Status</label>
                                    <select name="status" class="form-select mb-3">
                                        <option value="pending"   @selected($job->status === 'pending')>Pending</option>
                                        <option value="scheduled" @selected($job->status === 'scheduled')>Scheduled</option>
                                        <option value="assigned"  @selected($job->status === 'assigned')>Assigned (In Progress)</option>
                                        <option value="completed" @selected($job->status === 'completed')>Completed</option>
                                    </select>
                                    <label class="form-label">Completion Notes (optional)</label>
                                    <textarea name="notes" class="form-textarea mb-3" placeholder="Describe what was installed, tested, or any issues…">{{ $job->installation_notes }}</textarea>
                                    
                                    <div class="flex items-center justify-between mt-3 flex-wrap gap-2">
                                        <button type="submit" class="btn-submit btn-blue">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Save Status
                                        </button>
                                        <a href="{{ route('jcr.create-job', $job) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-colors">
                                            ✍️ Complete & Sign-off (JCR) &rarr;
                                        </a>
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

            {{-- Tab: AMC Visits --}}
            <div id="tab-visits" class="tab-content">
                @forelse($activeVisits as $visit)
                    @php $lead = $visit->amcContract?->lead; @endphp
                    <div class="tech-card">
                        <div class="card-head">
                            <div class="card-head-left">
                                <div class="card-icon bg-amber-50 text-amber-600">🔧</div>
                                <div>
                                    <div class="card-title">{{ $visit->amcContract?->contract_no ?? 'AMC-' . $visit->id }}</div>
                                    <div class="card-sub">{{ $lead?->customer_name ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @php $due = \Carbon\Carbon::parse($visit->scheduled_date); @endphp
                                @if($due->isPast())
                                    <span class="badge" style="background:#fee2e2;color:#991b1b">⚠️ Overdue</span>
                                @elseif($due->isToday())
                                    <span class="badge" style="background:#fef9c3;color:#854d0e">📅 Today</span>
                                @else
                                    <span class="badge badge-pending">{{ $due->format('d M') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="info-label">📅 Scheduled:</span>
                                <span>{{ $due->format('d M Y') }} ({{ $due->diffForHumans() }})</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">📍 Site:</span>
                                <span>{{ $lead?->site_address ?? $lead?->address ?? 'Address not specified' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">📞 Contact:</span>
                                @if($lead?->phone)
                                    <a href="tel:{{ $lead->phone }}" class="phone-link">{{ $lead->phone }}</a>
                                @else
                                    <span>—</span>
                                @endif
                            </div>
                            <div class="info-row">
                                <span class="info-label">📋 Contract:</span>
                                <span>{{ ucfirst($visit->amcContract?->frequency ?? '—') }} maintenance &middot; Valid until {{ $visit->amcContract?->end_date?->format('d M Y') ?? '—' }}</span>
                            </div>

                            <form method="POST" action="{{ route('technician.amc-visits.complete', $visit) }}">
                                @csrf
                                <div class="update-form">
                                    <label class="form-label">Service Report / Completion Notes <span class="text-rose-500">*</span></label>
                                    <textarea name="completion_notes" class="form-textarea mb-3" placeholder="Describe what was serviced, checked, cleaned, or replaced…" required></textarea>
                                    
                                    <div class="flex items-center justify-between mt-3 flex-wrap gap-2">
                                        <button type="submit" class="btn-submit btn-green">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Quick Complete
                                        </button>
                                        <a href="{{ route('jcr.create-visit', $visit) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition-colors">
                                            ✍️ Customer Sign-off (JCR) &rarr;
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="tech-card">
                        <div class="empty-state">
                            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4>No Pending AMC Visits</h4>
                            <p class="text-xs text-slate-400">You have no pending maintenance visits scheduled.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Tab: History --}}
            <div id="tab-history" class="tab-content">

                {{-- Resolved Repair Tickets --}}
                <div class="tech-card">
                    <div class="card-head">
                        <div class="card-head-left">
                            <div class="card-icon bg-emerald-50 text-emerald-600">🛡️</div>
                            <div>
                                <div class="card-title">Resolved Repair Tickets</div>
                                <div class="card-sub">{{ $completedTickets->count() }} repairs resolved</div>
                            </div>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($completedTickets as $ticket)
                            <div class="p-4 flex items-start justify-between gap-3 hover:bg-slate-50 transition-colors">
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-slate-800">{{ $ticket->ticket_no }} — {{ $ticket->title }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $ticket->lead->customer_name }} &middot; Resolved {{ $ticket->resolved_at?->format('d M Y') ?? '—' }}</div>
                                    @if($ticket->resolution_notes)
                                        <div class="text-xs text-emerald-700 mt-1">✓ {{ Str::limit($ticket->resolution_notes, 90) }}</div>
                                    @endif
                                </div>
                                <span class="badge badge-resolved">Resolved</span>
                            </div>
                        @empty
                            <div class="empty-state">
                                <p class="text-xs text-slate-400">No resolved repair tickets yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Completed Jobs --}}
                <div class="tech-card">
                    <div class="card-head">
                        <div class="card-head-left">
                            <div class="card-icon bg-purple-50 text-purple-600">✅</div>
                            <div>
                                <div class="card-title">Completed Installation Jobs</div>
                                <div class="card-sub">{{ $completedJobs->count() }} jobs done</div>
                            </div>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($completedJobs as $job)
                            @php $lead = $job->quotation?->lead; @endphp
                            <div class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50 transition-colors">
                                <div>
                                    <div class="text-sm font-semibold text-slate-800">{{ $job->job_no ?? 'JOB-' . $job->id }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $lead?->customer_name ?? 'N/A' }} &middot; {{ $job->updated_at->format('d M Y') }}</div>
                                </div>
                                <span class="badge badge-completed">Completed</span>
                            </div>
                        @empty
                            <div class="empty-state">
                                <p class="text-xs text-slate-400">No completed installation jobs yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Completed Visits --}}
                <div class="tech-card">
                    <div class="card-head">
                        <div class="card-head-left">
                            <div class="card-icon bg-blue-50 text-blue-600">🛠️</div>
                            <div>
                                <div class="card-title">Completed AMC Visits</div>
                                <div class="card-sub">{{ $completedVisits->count() }} visits serviced</div>
                            </div>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($completedVisits as $visit)
                            @php $lead = $visit->amcContract?->lead; @endphp
                            <div class="p-4 flex items-start justify-between gap-3 hover:bg-slate-50 transition-colors">
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-slate-800">{{ $visit->amcContract?->contract_no ?? 'AMC-' . $visit->id }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $lead?->customer_name ?? 'N/A' }} &middot; Serviced {{ $visit->completed_at?->format('d M Y') ?? '—' }}</div>
                                    @if($visit->completion_notes)
                                        <div class="text-xs text-slate-500 mt-1 italic">{{ Str::limit($visit->completion_notes, 80) }}</div>
                                    @endif
                                </div>
                                <span class="badge badge-{{ $visit->status }}">{{ ucfirst($visit->status) }}</span>
                            </div>
                        @empty
                            <div class="empty-state">
                                <p class="text-xs text-slate-400">No completed AMC visits yet.</p>
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
            container.innerHTML = `<div class="flex items-center gap-2 text-blue-200 text-xs">
                <span class="animate-spin">⏳</span> Querying CRM database for S/N <strong>${code}</strong>...
            </div>`;

            try {
                const response = await fetch(`{{ route('equipment.lookup') }}?serial=${encodeURIComponent(code)}`);
                const data = await response.json();

                if (data.found && data.type === 'installed_equipment') {
                    const warrantyBadge = data.is_under_warranty 
                        ? `<span class="bg-emerald-800 text-emerald-200 text-[10px] font-bold px-2 py-0.5 rounded-full">✓ UNDER WARRANTY</span>`
                        : `<span class="bg-rose-900 text-rose-200 text-[10px] font-bold px-2 py-0.5 rounded-full">⚠ WARRANTY EXPIRED</span>`;

                    container.innerHTML = `
                        <div class="flex justify-between items-start flex-wrap gap-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="text-sm font-bold text-white">${data.equipment_name}</h4>
                                    ${warrantyBadge}
                                </div>
                                <div class="text-xs text-slate-300 space-y-0.5">
                                    <div>Customer: <strong class="text-white">${data.customer_name}</strong> (${data.customer_phone})</div>
                                    <div>Location: <strong class="text-white">${data.location_tag || 'Site Asset'}</strong> &middot; S/N: <code class="text-blue-300 bg-white/10 px-1.5 py-0.5 rounded">${data.serial_number}</code></div>
                                    <div>Warranty Ends: <strong>${data.mfg_warranty_expiry}</strong></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="${data.rma_url}" class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg inline-flex items-center gap-1 transition-colors">
                                    ⚡ Raise RMA Claim
                                </a>
                                <a href="${data.show_url}" class="bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition-colors">
                                    🔍 View Asset History
                                </a>
                            </div>
                        </div>
                    `;
                } else if (data.found && data.type === 'catalog_product') {
                    container.innerHTML = `
                        <div class="flex justify-between items-center flex-wrap gap-3">
                            <div>
                                <div class="text-[10px] font-bold text-blue-300 uppercase tracking-wider">Warehouse Catalog Product</div>
                                <h4 class="text-sm font-bold text-white mt-0.5">${data.product_name}</h4>
                                <div class="text-xs text-slate-300">SKU: ${data.sku} &middot; Model: ${data.model_no || 'N/A'} &middot; Stock: <strong class="text-emerald-400">${data.stock_quantity} in stock</strong></div>
                            </div>
                            <a href="{{ route('equipment.create') }}?product_id=${data.id}&serial=${encodeURIComponent(code)}" class="bg-[#2563eb] hover:bg-blue-600 text-white text-xs font-bold px-3.5 py-2 rounded-xl transition-colors">
                                + Install at Client Site
                            </a>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="flex justify-between items-center flex-wrap gap-3">
                            <div>
                                <div class="text-xs font-bold text-rose-400">❌ No record found for S/N: ${code}</div>
                                <div class="text-xs text-slate-300">This hardware serial has not yet been registered to a customer site.</div>
                            </div>
                            <a href="{{ route('equipment.create') }}?serial=${encodeURIComponent(code)}" class="bg-[#2563eb] hover:bg-blue-600 text-white text-xs font-bold px-3.5 py-2 rounded-xl transition-colors">
                                + Register New Asset Now
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
