<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.show', $project) }}" class="p-2 rounded-xl bg-white dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-white border border-slate-200 dark:border-slate-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Edit Project #{{ $project->project_code }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Update project scope, progress, company specifications, and deadlines</p>
                </div>
            </div>
            <a href="{{ route('projects.show', $project) }}" class="btn-secondary text-xs py-2 px-3">
                View Project &rarr;
            </a>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
        <form method="POST" action="{{ route('projects.update', $project) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Validation Errors Banner --}}
            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm">
                    <div class="font-bold mb-1 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Please correct the following errors:
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 1. Primary Information --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">1</span>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Company & Project Identity</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Company Name --}}
                    <div>
                        <label for="company_name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Company / Client Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="company_name" name="company_name" value="{{ old('company_name', $project->company_name) }}" required
                               class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Project Title --}}
                    <div>
                        <label for="title" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Project Title <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="title" name="title" value="{{ old('title', $project->title) }}" required
                               class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Project Code --}}
                    <div>
                        <label for="project_code" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Project Code <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="project_code" name="project_code" value="{{ old('project_code', $project->project_code) }}" required
                               class="w-full text-sm font-mono rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Associated Lead --}}
                    <div>
                        <label for="lead_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Associated CRM Lead
                        </label>
                        <select id="lead_id" name="lead_id" class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="">-- No specific lead linked --</option>
                            @foreach($leads as $lead)
                                <option value="{{ $lead->id }}" {{ old('lead_id', $project->lead_id) == $lead->id ? 'selected' : '' }}>
                                    {{ $lead->customer_name }} @if($lead->company) ({{ $lead->company }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Site Address --}}
                <div>
                    <label for="site_address" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Site Installation Address
                    </label>
                    <input type="text" id="site_address" name="site_address" value="{{ old('site_address', $project->site_address) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- 2. Project Classification & Domain Specs (Hardware & Software) --}}
            <div x-data="{ projectType: '{{ old('project_type', $project->project_type ?? 'hardware_attendance') }}' }" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-5">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">2</span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Project Classification & Technical Architecture</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Configure parameters for Terminal Camera Attendance, CCTV Hardware, or Webpage Engineering</p>
                        </div>
                    </div>
                </div>

                {{-- Type Radio / Selector Grid --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Select Project Module / Type <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        {{-- Hardware: Terminal Camera Attendance --}}
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all"
                               :class="projectType === 'hardware_attendance' ? 'border-purple-500 bg-purple-50/50 dark:bg-purple-950/30 ring-2 ring-purple-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-purple-300'">
                            <input type="radio" name="project_type" value="hardware_attendance" x-model="projectType" class="sr-only">
                            <span class="text-xs font-bold text-purple-700 dark:text-purple-300 flex items-center gap-1.5 mb-1">
                                <span>⏱</span> Terminal Attendance
                            </span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">Biometric & AI facial recognition terminal systems</span>
                        </label>

                        {{-- Hardware: CCTV --}}
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all"
                               :class="projectType === 'hardware_cctv' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-950/30 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-blue-300'">
                            <input type="radio" name="project_type" value="hardware_cctv" x-model="projectType" class="sr-only">
                            <span class="text-xs font-bold text-blue-700 dark:text-blue-300 flex items-center gap-1.5 mb-1">
                                <span>📸</span> CCTV Hardware
                            </span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">IP Cameras, NVR, PoE cabling & site surveillance</span>
                        </label>

                        {{-- Software: Webpage --}}
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all"
                               :class="projectType === 'software_web' ? 'border-cyan-500 bg-cyan-50/50 dark:bg-cyan-950/30 ring-2 ring-cyan-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-cyan-300'">
                            <input type="radio" name="project_type" value="software_web" x-model="projectType" class="sr-only">
                            <span class="text-xs font-bold text-cyan-700 dark:text-cyan-300 flex items-center gap-1.5 mb-1">
                                <span>🌐</span> Webpage & App
                            </span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">Web application, client portal, APIs & dashboards</span>
                        </label>

                        {{-- Hybrid --}}
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all"
                               :class="projectType === 'hybrid' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/30 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300'">
                            <input type="radio" name="project_type" value="hybrid" x-model="projectType" class="sr-only">
                            <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300 flex items-center gap-1.5 mb-1">
                                <span>⚡</span> Hybrid (Turnkey)
                            </span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">Both physical hardware and dedicated web platform</span>
                        </label>
                    </div>
                </div>

                {{-- Conditional Hardware Specifications Panel --}}
                <div x-show="projectType === 'hardware_attendance' || projectType === 'hardware_cctv' || projectType === 'hybrid'"
                     x-transition class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-4">
                    <div class="flex items-center gap-2 text-xs font-bold text-purple-700 dark:text-purple-300 uppercase tracking-wider">
                        <span>⏱ Terminal & Camera Hardware Parameters</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Attendance Terminals Count</label>
                            <input type="number" min="0" name="hardware_specs[terminal_count]" value="{{ old('hardware_specs.terminal_count', $project->hardware_specs['terminal_count'] ?? 0) }}"
                                   placeholder="e.g. 4 Terminals"
                                   class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Camera Channels Count</label>
                            <input type="number" min="0" name="hardware_specs[camera_count]" value="{{ old('hardware_specs.camera_count', $project->hardware_specs['camera_count'] ?? 0) }}"
                                   placeholder="e.g. 16 Cameras"
                                   class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Device Brand / Model</label>
                            <input type="text" name="hardware_specs[device_brand]" value="{{ old('hardware_specs.device_brand', $project->hardware_specs['device_brand'] ?? '') }}"
                                   placeholder="e.g. ZKTeco ProFace X / Hikvision MinMoe"
                                   class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Terminal IP Subnet / Network</label>
                            <input type="text" name="hardware_specs[terminal_ip]" value="{{ old('hardware_specs.terminal_ip', $project->hardware_specs['terminal_ip'] ?? '') }}"
                                   placeholder="e.g. 192.168.1.50 - 192.168.1.60"
                                   class="w-full text-xs font-mono rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Attendance Sync Mode</label>
                            <select name="hardware_specs[attendance_sync_mode]" class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                                <option value="face_recognition" {{ old('hardware_specs.attendance_sync_mode', $project->hardware_specs['attendance_sync_mode'] ?? '') === 'face_recognition' ? 'selected' : '' }}>AI Face Recognition Terminal</option>
                                <option value="biometric_fingerprint" {{ old('hardware_specs.attendance_sync_mode', $project->hardware_specs['attendance_sync_mode'] ?? '') === 'biometric_fingerprint' ? 'selected' : '' }}>Biometric Fingerprint Scanner</option>
                                <option value="rfid_card" {{ old('hardware_specs.attendance_sync_mode', $project->hardware_specs['attendance_sync_mode'] ?? '') === 'rfid_card' ? 'selected' : '' }}>RFID Proximity Card Terminal</option>
                                <option value="n_a" {{ old('hardware_specs.attendance_sync_mode', $project->hardware_specs['attendance_sync_mode'] ?? '') === 'n_a' ? 'selected' : '' }}>Not Applicable (CCTV Only)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Conditional Software Specifications Panel --}}
                <div x-show="projectType === 'software_web' || projectType === 'hybrid'"
                     x-transition class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-4">
                    <div class="flex items-center gap-2 text-xs font-bold text-cyan-700 dark:text-cyan-300 uppercase tracking-wider">
                        <span>🌐 Software Architecture & Webpage Parameters</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Live / Staging Webpage URL</label>
                            <input type="url" name="software_specs[webpage_url]" value="{{ old('software_specs.webpage_url', $project->software_specs['webpage_url'] ?? '') }}"
                                   placeholder="https://portal.clientcompany.com"
                                   class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Source Repository URL</label>
                            <input type="url" name="software_specs[repository_url]" value="{{ old('software_specs.repository_url', $project->software_specs['repository_url'] ?? '') }}"
                                   placeholder="https://github.com/organization/project"
                                   class="w-full text-xs font-mono rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tech Stack (comma separated)</label>
                            <input type="text" name="software_specs[tech_stack]" value="{{ old('software_specs.tech_stack', $project->software_specs['tech_stack'] ?? '') }}"
                                   placeholder="Laravel, Tailwind CSS, Alpine.js, REST API"
                                   class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Deployment Server / Host</label>
                            <input type="text" name="software_specs[deployment_server]" value="{{ old('software_specs.deployment_server', $project->software_specs['deployment_server'] ?? '') }}"
                                   placeholder="Ubuntu 24.04 LTS / Nginx / Cloudflare"
                                   class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Status, Progress & Priority --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">3</span>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Status, Schedule & Progress</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Status --}}
                    <div>
                        <label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Project Status <span class="text-rose-500">*</span>
                        </label>
                        <select id="status" name="status" required class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="in_progress" {{ old('status', $project->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>Done / Completed</option>
                            <option value="incompleted" {{ old('status', $project->status) === 'incompleted' ? 'selected' : '' }}>Incompleted / Pending</option>
                            <option value="on_hold" {{ old('status', $project->status) === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="cancelled" {{ old('status', $project->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    {{-- Priority --}}
                    <div>
                        <label for="priority" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Priority <span class="text-rose-500">*</span>
                        </label>
                        <select id="priority" name="priority" required class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="medium" {{ old('priority', $project->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority', $project->priority) === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ old('priority', $project->priority) === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="low" {{ old('priority', $project->priority) === 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>

                    {{-- Completion Percentage --}}
                    <div>
                        <label for="progress_percentage" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Progress (% Complete)
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" id="progress_percentage" name="progress_percentage" min="0" max="100" value="{{ old('progress_percentage', $project->progress_percentage) }}"
                                   class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <span class="text-xs font-bold text-slate-500">%</span>
                        </div>
                    </div>

                    {{-- Start Date --}}
                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Start Date
                        </label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '') }}"
                               class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Deadline --}}
                    <div>
                        <label for="deadline" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Target Deadline
                        </label>
                        <input type="date" id="deadline" name="deadline" value="{{ old('deadline', $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('Y-m-d') : '') }}"
                               class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Lead Technician / Assigned Engineer --}}
                    <div>
                        <label for="assigned_to" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Lead Engineer / Assignee
                        </label>
                        <select id="assigned_to" name="assigned_to" class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Unassigned --</option>
                            @foreach($teamMembers as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to', $project->assigned_to) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst($user->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Financials --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label for="budget" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Estimated Budget Valuation (₹)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">₹</span>
                            <input type="number" step="0.01" id="budget" name="budget" value="{{ old('budget', $project->budget) }}"
                                   class="w-full pl-8 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label for="actual_cost" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Actual Incurred Cost (₹)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">₹</span>
                            <input type="number" step="0.01" id="actual_cost" name="actual_cost" value="{{ old('actual_cost', $project->actual_cost) }}"
                                   class="w-full pl-8 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Contact Person & Scope Description --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-teal-50 dark:bg-teal-900/40 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-xs">4</span>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Client Contact & Technical Scope</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="contact_person" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Contact Person
                        </label>
                        <input type="text" id="contact_person" name="contact_person" value="{{ old('contact_person', $project->contact_person) }}"
                               class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="contact_phone" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Phone / Mobile
                        </label>
                        <input type="tel" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $project->contact_phone) }}"
                               class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="contact_email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Email Address
                        </label>
                        <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $project->contact_email) }}"
                               class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Scope of Work & Requirements
                    </label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">{{ old('description', $project->description) }}</textarea>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Internal Notes
                    </label>
                    <textarea id="notes" name="notes" rows="2"
                              class="w-full text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">{{ old('notes', $project->notes) }}</textarea>
                </div>
            </div>

            {{-- 5. Attach Additional Files & PDFs --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">5</span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Upload Additional Documents & PDFs</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Add more blueprints, contracts, completion sign-offs</p>
                        </div>
                    </div>
                </div>

                <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-6 text-center hover:border-blue-500 transition-colors bg-slate-50/50 dark:bg-slate-800/30">
                    <div class="flex text-sm justify-center leading-6 text-slate-600 dark:text-slate-400">
                        <label for="documents" class="relative cursor-pointer rounded-lg bg-white dark:bg-slate-800 px-3 py-1 font-semibold text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-600 hover:text-blue-500 border border-slate-200 dark:border-slate-700">
                            <span>Browse Additional Files</span>
                            <input id="documents" name="documents[]" type="file" multiple class="sr-only" accept=".pdf,.doc,.docx,.xls,.xlsx,.dwg,.jpg,.jpeg,.png,.zip">
                        </label>
                        <p class="pl-2 pt-1 text-xs">or drag and drop here</p>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">PDF, CAD, Images up to 30MB each.</p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('projects.show', $project) }}" class="btn-secondary text-sm px-5">
                    Cancel
                </a>
                <button type="submit" class="btn-amber text-sm px-6 font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
