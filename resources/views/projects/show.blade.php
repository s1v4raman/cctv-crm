<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <a href="{{ route('projects.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">{{ $project->title }}</h2>
                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                        {{ $project->project_code }}
                    </span>
                    {!! $project->company_badge !!}
                    {!! $project->status_badge !!}
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-400 mt-1 flex-wrap">
                    @if($project->site)
                        <span>Site: <a href="{{ route('sites.show', $project->site) }}" class="font-semibold text-slate-700 dark:text-slate-300 hover:underline">{{ $project->site->name }}</a></span>
                        <span>&bull;</span>
                    @endif
                    <span>Lead Tech: <strong class="text-slate-700 dark:text-slate-300">{{ $project->leadTechnician?->name ?? 'Unassigned' }}</strong></span>
                    <span>&bull;</span>
                    <span>Type: <strong class="text-slate-700 dark:text-slate-300">{{ $project->project_type_label }}</strong></span>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if(auth()->user()->isAdmin() || ($project->created_by === auth()->id() && in_array($project->status, ['draft', 'pending_approval'])))
                    <a href="{{ route('projects.edit', $project) }}" class="btn-secondary text-xs">Edit Info</a>
                @endif

                @if(auth()->user()->isAdmin())
                    {{-- Status transition dropdown (Admin only per matrix) --}}
                    <form method="POST" action="{{ route('projects.updateStatus', $project) }}" class="inline-block">
                        @csrf
                        @method('PATCH')
                        <select name="status" onchange="this.form.submit()" class="px-2.5 py-1.5 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(['draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'approved' => 'Approved', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'on_hold' => 'On Hold', 'cancelled' => 'Cancelled'] as $sVal => $sLbl)
                                <option value="{{ $sVal }}" {{ $project->status === $sVal ? 'selected' : '' }}>{{ $sLbl }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6"
         x-data="{
             currentTab: '{{ $activeTab }}',
             approveModalOpen: false,
             rejectModalOpen: false,
             materialModalOpen: false,
             documentModalOpen: false,
             workDayModalOpen: false,
             deviceModalOpen: false,
             bulkDeviceModalOpen: false,

             // Device password reveal state
             revealedPasswords: {},
             async revealPassword(deviceId) {
                 if (this.revealedPasswords[deviceId]) {
                     delete this.revealedPasswords[deviceId];
                     return;
                 }
                 try {
                     const res = await fetch(`/projects/{{ $project->id }}/devices/${deviceId}/reveal`, {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         }
                     });
                     const data = await res.json();
                     if (data.success) {
                         this.revealedPasswords[deviceId] = data.password;
                     }
                 } catch (e) {
                     alert('Error revealing password');
                 }
             }
         }">

        {{-- Flash Alerts --}}
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
            </div>
        @endif

        {{-- Admin Approval / Rejection Banner --}}
        @if($project->status === 'pending_approval')
            <div class="p-5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-700/60 shadow-md flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shrink-0">
                        ⏳
                    </div>
                    <div>
                        <h4 class="font-extrabold text-amber-900 dark:text-amber-200 text-sm">Project Pending Administrator Approval</h4>
                        <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                            Created by {{ $project->creator?->name ?? 'Staff' }} on {{ $project->created_at->format('d M Y') }}. Assign an executing company and per-metre cabling rate to approve.
                        </p>
                    </div>
                </div>

                @if(auth()->user()->isAdmin())
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" @click="rejectModalOpen = true" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 text-rose-600 border border-rose-300 dark:border-rose-800 hover:bg-rose-50 transition">
                            Reject
                        </button>
                        <button type="button" @click="approveModalOpen = true" class="px-4 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md transition">
                            ✓ Approve Project
                        </button>
                    </div>
                @else
                    <span class="text-xs font-semibold text-amber-600 italic">Waiting for Admin sign-off</span>
                @endif
            </div>
        @elseif($project->status === 'rejected')
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs">
                <strong>Project Rejected:</strong> {{ $project->rejection_reason ?? 'No reason recorded.' }}
            </div>
        @endif

        {{-- 5-Tab Navigation Bar --}}
        <div class="border-b border-slate-200 dark:border-slate-800">
            <nav class="flex space-x-6 text-sm font-bold">
                <button type="button" @click="currentTab = 'overview'" :class="currentTab === 'overview' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 pb-3' : 'text-slate-400 pb-3 hover:text-slate-600'">
                    1. Overview
                </button>
                <button type="button" @click="currentTab = 'materials'" :class="currentTab === 'materials' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 pb-3' : 'text-slate-400 pb-3 hover:text-slate-600'">
                    2. Materials ({{ $project->materials->count() }})
                </button>
                <button type="button" @click="currentTab = 'documents'" :class="currentTab === 'documents' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 pb-3' : 'text-slate-400 pb-3 hover:text-slate-600'">
                    3. Documents &bull; DCs ({{ $project->documents->count() }})
                    @if($uninvoicedDcCount > 0)
                        <span class="ml-1 px-1.5 py-0.5 text-[10px] rounded-full bg-amber-100 text-amber-700 font-bold">{{ $uninvoicedDcCount }} unbilled</span>
                    @endif
                </button>
                <button type="button" @click="currentTab = 'work_days'" :class="currentTab === 'work_days' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 pb-3' : 'text-slate-400 pb-3 hover:text-slate-600'">
                    4. Work Days &bull; Wages ({{ $project->workDays->count() }})
                </button>
                <button type="button" @click="currentTab = 'ip_devices'" :class="currentTab === 'ip_devices' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 pb-3' : 'text-slate-400 pb-3 hover:text-slate-600'">
                    5. IP Devices ({{ $project->ipDevices->count() }})
                </button>
            </nav>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: OVERVIEW                                                  --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left 2 Cols: Site, Requirements, Finance --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Site Location Card --}}
                    @if($project->site)
                        <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                <h3 class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    Installation Site Details
                                </h3>
                                <a href="{{ $project->site->navigate_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Get GPS Directions</span>
                                </a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-xs text-slate-400 block">Site Name</span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $project->site->name }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Client Contact</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $project->site->client_name ?? '—' }} ({{ $project->site->client_phone ?? 'No phone' }})</span>
                                </div>
                                <div class="sm:col-span-2">
                                    <span class="text-xs text-slate-400 block">Address</span>
                                    <span class="text-slate-700 dark:text-slate-300">{{ $project->site->address }}, {{ $project->site->city }} {{ $project->site->pincode }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Scope & Technical Requirements --}}
                    <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                                @if($project->project_type === 'software_web')
                                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                                    <span>Web Engineering &amp; Application Scope</span>
                                @elseif($project->project_type === 'networking')
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <span>Network Cabling &amp; Infrastructure Scope</span>
                                @elseif($project->project_type === 'access_control')
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                    <span>Access Control &amp; Security Scope</span>
                                @elseif($project->project_type === 'hardware_attendance')
                                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                                    <span>Biometric Attendance Scope</span>
                                @elseif($project->project_type === 'hybrid')
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                    <span>Hybrid Turnkey Scope &amp; Architecture</span>
                                @else
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                    <span>CCTV Surveillance Scope &amp; Specs</span>
                                @endif
                            </h3>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/70 dark:border-slate-700">
                                {{ $project->project_type_label }}
                            </span>
                        </div>

                        {{-- Dynamic Scope Metric Grid based on Project Type --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                            @if($project->project_type === 'software_web')
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Architecture</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $project->requirements['app_architecture'] ?? 'Modular Web App' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Primary Tech Stack</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $project->requirements['tech_stack'] ?? 'Laravel, Tailwind, Alpine' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Cloud Hosting</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['cloud_hosting'] ?? 'Cloud Server' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 sm:col-span-2">
                                    <span class="text-xs text-slate-400 block">Core Modules</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['core_modules'] ?? 'Auth, Dashboard, REST API' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Domain &amp; SSL</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['domain_ssl'] ?? 'Configured' }}</span>
                                </div>

                            @elseif($project->project_type === 'networking')
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Network Drops</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ $project->requirements['network_drops'] ?? 'N/A' }} Drops</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Wi-Fi Access Points</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ $project->requirements['wifi_access_points'] ?? 'N/A' }} APs</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Est. Cabling</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ $project->requirements['cabling_metres'] ?? 'N/A' }} m</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Server Rack Size</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['server_rack_size'] ?? 'N/A' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Switch Specification</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['switch_spec'] ?? 'N/A' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Router / Gateway</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['router_gateway'] ?? 'N/A' }}</span>
                                </div>

                            @elseif($project->project_type === 'access_control')
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Controlled Doors</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ $project->requirements['doors_count'] ?? 'N/A' }} Doors</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Auth Method</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $project->requirements['auth_method'] ?? 'N/A' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Lock Hardware</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['lock_type'] ?? 'N/A' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Exit Device</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['exit_device'] ?? 'N/A' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Controller Spec</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['controller_spec'] ?? 'N/A' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Power Backup</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['backup_battery'] ?? 'N/A' }}</span>
                                </div>

                            @elseif($project->project_type === 'hardware_attendance')
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">User Capacity</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ $project->requirements['user_capacity'] ?? ($project->requirements['terminal_users'] ?? 'N/A') }} Users</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Comm. Protocol</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['comm_protocol'] ?? ($project->requirements['communication'] ?? 'TCP/IP Network') }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">HRMS Sync</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['hrms_software'] ?? 'Automated Sync' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Mounting Location</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['mounting_location'] ?? 'Entry / Reception' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 sm:col-span-2">
                                    <span class="text-xs text-slate-400 block">Battery &amp; Backup</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['power_backup'] ?? ($project->requirements['power_battery'] ?? 'Internal Battery') }}</span>
                                </div>

                            @elseif($project->project_type === 'hybrid')
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Hardware Endpoints</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ $project->requirements['hardware_endpoints'] ?? 'N/A' }} Devices</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Edge Gateway</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['edge_gateway'] ?? 'IoT Hub' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Storage / Server</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['server_storage'] ?? 'Micro-Server' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 sm:col-span-2">
                                    <span class="text-xs text-slate-400 block">Software Platform</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['software_platform'] ?? 'Web & Mobile' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Alert Channels</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['alert_channels'] ?? 'Real-time' }}</span>
                                </div>

                            @else
                                {{-- Default CCTV Surveillance Scope --}}
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">CCTV Cameras</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ $project->requirements['cctv_count'] ?? 'N/A' }} Nos</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">NVR Channels</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ $project->requirements['nvr_channels'] ?? 'N/A' }} Ch</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Est. Cabling</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ $project->requirements['cabling_metres'] ?? 'N/A' }} m</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Power Supply</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['power_supply'] ?? 'N/A' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Storage HDD</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['storage_hdd'] ?? 'N/A' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Display / Screen</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['display_screen'] ?? 'N/A' }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Additional dynamic custom attributes if any other keys exist in requirements --}}
                        @php
                            $knownKeys = [
                                'cctv_count', 'nvr_channels', 'cabling_metres', 'power_supply', 'display_screen', 'storage_hdd',
                                'network_drops', 'wifi_access_points', 'server_rack_size', 'switch_spec', 'router_gateway',
                                'app_architecture', 'tech_stack', 'cloud_hosting', 'core_modules', 'domain_ssl',
                                'doors_count', 'auth_method', 'lock_type', 'exit_device', 'controller_spec', 'backup_battery',
                                'user_capacity', 'comm_protocol', 'hrms_software', 'mounting_location', 'power_backup',
                                'hardware_endpoints', 'software_platform', 'edge_gateway', 'server_storage', 'alert_channels',
                                'special_requirements'
                            ];
                            $customRequirements = is_array($project->requirements) 
                                ? array_diff_key($project->requirements, array_flip($knownKeys))
                                : [];
                        @endphp

                        @if(!empty($customRequirements))
                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Custom Scope Specifications</span>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @foreach($customRequirements as $cKey => $cVal)
                                        <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800">
                                            <span class="text-[10px] text-slate-400 uppercase block font-semibold">{{ ucwords(str_replace('_', ' ', $cKey)) }}</span>
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ is_array($cVal) ? json_encode($cVal) : $cVal }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($project->requirements['special_requirements']) || $project->description)
                            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300">
                                <span class="font-bold text-slate-400 block mb-1">Client Special Instructions:</span>
                                <p class="whitespace-pre-line">{{ $project->requirements['special_requirements'] ?? $project->description }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Software & Web Application Portal Panel --}}
                    @if($project->project_type === 'software_web' || $project->project_type === 'hybrid' || !empty($project->software_specs['webpage_url']) || !empty($project->software_specs['repository_url']))
                        <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                <span class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                                    <span>Web Application &amp; Deployment Links</span>
                                </span>
                                @if(!empty($project->software_specs['webpage_url']))
                                    <a href="{{ $project->software_specs['webpage_url'] }}" target="_blank" rel="noopener noreferrer" class="btn-primary text-xs py-1 px-2.5 flex items-center gap-1 shadow-xs">
                                        <span>Open Live Site</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @endif
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-[11px] text-slate-400 block font-semibold mb-1">Live / Production Webpage</span>
                                    @if(!empty($project->software_specs['webpage_url']))
                                        <a href="{{ $project->software_specs['webpage_url'] }}" target="_blank" rel="noopener noreferrer" class="font-mono text-cyan-600 dark:text-cyan-400 hover:underline break-all">
                                            {{ $project->software_specs['webpage_url'] }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">Not set</span>
                                    @endif
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-[11px] text-slate-400 block font-semibold mb-1">Source Repository</span>
                                    @if(!empty($project->software_specs['repository_url']))
                                        <a href="{{ $project->software_specs['repository_url'] }}" target="_blank" rel="noopener noreferrer" class="font-mono text-blue-600 dark:text-blue-400 hover:underline break-all">
                                            {{ $project->software_specs['repository_url'] }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">Not set</span>
                                    @endif
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-[11px] text-slate-400 block font-semibold mb-1">Primary Tech Stack</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $project->software_specs['tech_stack'] ?? ($project->requirements['tech_stack'] ?? 'Laravel & Tailwind') }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-[11px] text-slate-400 block font-semibold mb-1">Deployment Infrastructure</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $project->software_specs['deployment_server'] ?? ($project->requirements['cloud_hosting'] ?? 'Cloud Hosting') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Hardware Terminal & Attendance Specs --}}
                    @if($project->project_type === 'hardware_attendance' || !empty($project->hardware_specs['terminal_count']) || !empty($project->hardware_specs['camera_count']) || !empty($project->hardware_specs['device_brand']))
                        <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                <span class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                    <span>Terminal &amp; Camera Hardware Specifications</span>
                                </span>
                                <button type="button" @click="currentTab = 'ip_devices'" class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hover:underline flex items-center gap-1">
                                    <span>Open IP Devices Register ({{ $project->ipDevices->count() }}) &rarr;</span>
                                </button>
                            </h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block font-medium">Terminals Count</span>
                                    <span class="font-extrabold text-indigo-600 dark:text-indigo-400 text-base">{{ $project->hardware_specs['terminal_count'] ?? 0 }} Units</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block font-medium">Cameras Count</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ $project->hardware_specs['camera_count'] ?? 0 }} Units</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block font-medium">Device Brand / Model</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->hardware_specs['device_brand'] ?? 'ZKTeco / Hikvision' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block font-medium">Terminal IP Subnet</span>
                                    <span class="font-mono text-slate-800 dark:text-slate-200 text-xs">{{ $project->hardware_specs['terminal_ip'] ?? 'DHCP / Auto' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 sm:col-span-2">
                                    <span class="text-xs text-slate-400 block font-medium">Attendance Sync Mode</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ ucwords(str_replace('_', ' ', $project->hardware_specs['attendance_sync_mode'] ?? 'AI Face Recognition')) }}</span>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                                <button type="button" @click="deviceModalOpen = true" class="btn-primary text-xs py-1 px-3 shadow-xs">
                                    + Add Terminal / Camera to IP Register
                                </button>
                                <a href="{{ route('attendance.index') }}" class="text-xs text-blue-600 dark:text-blue-400 font-semibold hover:underline flex items-center gap-1">
                                    <span>View Attendance &amp; Shift Hub</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Right 1 Col: Team Assignment, Financials & Audit Log --}}
                <div class="space-y-6">

                    {{-- Assigned Team & Technical Execution Card --}}
                    <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                Project Team &amp; Assignment
                            </h3>
                            @if(auth()->user()->isAdmin() || ($project->created_by === auth()->id() && in_array($project->status, ['draft', 'pending_approval'])))
                                <a href="{{ route('projects.edit', $project) }}" class="text-xs text-blue-600 dark:text-blue-400 font-semibold hover:underline">
                                    Edit &rarr;
                                </a>
                            @endif
                        </div>

                        <div class="space-y-3 text-xs">
                            {{-- Lead Technician --}}
                            <div class="p-3 rounded-xl bg-blue-50/50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40">
                                <span class="text-[10px] text-blue-600 dark:text-blue-400 uppercase tracking-wider block font-bold mb-1">🛠️ Lead Technician (In-Charge)</span>
                                @if($project->leadTechnician)
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-bold text-slate-900 dark:text-white text-sm block">{{ $project->leadTechnician->name }}</span>
                                            <span class="text-slate-500 text-[11px]">{{ $project->leadTechnician->email }}</span>
                                        </div>
                                        @if($project->leadTechnician->phone)
                                            <a href="tel:{{ $project->leadTechnician->phone }}" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] shadow-xs">
                                                📞 Call
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">No Lead Technician Assigned</span>
                                @endif
                            </div>

                            {{-- Assigned Staff / Employee --}}
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-bold mb-1">👤 Assigned Staff / Employee</span>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white text-xs block">{{ $project->assignedUser?->name ?? 'Unassigned' }}</span>
                                        <span class="text-[11px] text-slate-500">{{ $project->assignedUser ? ucfirst($project->assignedUser->role) : '—' }}</span>
                                    </div>
                                    @if($project->creator)
                                        <div class="text-right">
                                            <span class="text-[10px] text-slate-400 block">Created By</span>
                                            <span class="font-semibold text-slate-700 dark:text-slate-300 text-[11px]">{{ $project->creator->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Executing Entity --}}
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-bold mb-1">🏢 Executing Entity</span>
                                @if($project->company)
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-bold text-slate-900 dark:text-white text-xs block">{{ $project->company->name }}</span>
                                            <span class="text-[11px] text-emerald-600 font-semibold">✓ Admin Approved</span>
                                        </div>
                                        @if($project->approved_on)
                                            <span class="text-[10px] text-slate-400 font-mono">{{ \Carbon\Carbon::parse($project->approved_on)->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-amber-600 font-semibold italic text-xs">Pending Admin Company Assignment</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Financial Stats Card --}}
                    <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-sm">Financial Breakdown</h3>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-xs text-slate-400">Budget Estimate</span>
                                <span class="font-bold font-mono">₹{{ number_format($project->budget, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-xs text-slate-400">Approved Value</span>
                                <span class="font-bold font-mono text-emerald-600">₹{{ number_format($project->approved_value ?? $project->budget, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-xs text-slate-400">Per-Metre Cabling Rate</span>
                                <span class="font-bold font-mono text-blue-600">₹{{ number_format($project->per_metre_rate ?? 8.0, 2) }}/m</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-xs text-slate-400">Total Labor Spent</span>
                                <span class="font-bold font-mono text-amber-600">₹{{ number_format($totalLaborCost, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Audit Trail Log --}}
                    <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-3">
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-sm">Project History &amp; Audit Trail</h3>

                        <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                            @forelse($project->auditLogs as $log)
                                <div class="text-xs border-l-2 border-blue-500 pl-3 py-1">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $log->action_label }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $log->notes }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $log->user?->name ?? 'System' }} &bull; {{ $log->created_at->diffForHumans() }}</div>
                                </div>
                            @empty
                                <div class="text-xs text-slate-400 italic">No activity logged yet.</div>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: MATERIALS (BOM)                                           --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'materials'" x-cloak class="space-y-6">
            <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Bill of Materials (BOM)</h3>
                        <p class="text-xs text-slate-500">Hardware, cabling and consumables allocated for this deployment</p>
                    </div>

                    <button type="button" @click="materialModalOpen = true" class="btn-primary text-xs shadow-md">
                        + Add Material Item
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="px-5 py-3.5">Item Name</th>
                                <th class="px-5 py-3.5 text-center">Quantity</th>
                                <th class="px-5 py-3.5">Unit</th>
                                <th class="px-5 py-3.5">Procurement Source</th>
                                <th class="px-5 py-3.5">Local Shop Name</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:border-slate-800">
                            @forelse($project->materials as $mat)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                    <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white">
                                        {{ $mat->item_name }}
                                        @if($mat->notes)
                                            <span class="text-xs text-slate-400 block font-normal">{{ $mat->notes }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-center font-bold font-mono">
                                        {{ $mat->quantity }}
                                    </td>
                                    <td class="px-5 py-3.5 uppercase text-xs font-semibold">
                                        {{ $mat->unit }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $mat->source === 'warehouse' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300' : 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' }}">
                                            {{ $mat->source === 'warehouse' ? 'Warehouse Dispatch' : 'Local Purchase' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-700 dark:text-slate-300">
                                        {{ $mat->shop_name ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        @if(auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('projects.materials.destroy', ['project' => $project, 'material' => $mat]) }}" onsubmit="return confirm('Remove this material item?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold">
                                                Delete
                                            </button>
                                        </form>
                                        @else
                                            <span class="text-slate-400 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-slate-400 text-xs">
                                        No materials recorded in the Bill of Materials.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 3: DOCUMENTS & DELIVERY CHALLANS                             --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'documents'" x-cloak class="space-y-6">
            <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Documents &amp; Delivery Challans</h3>
                        <p class="text-xs text-slate-500">Official dispatch challans, site blueprints, sign-offs &amp; customer invoices</p>
                    </div>

                    <button type="button" @click="documentModalOpen = true" class="btn-primary text-xs shadow-md">
                        + Upload Document / DC
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="px-5 py-3.5">Type &amp; Title</th>
                                <th class="px-5 py-3.5">DC Number &amp; Date</th>
                                <th class="px-5 py-3.5">Items Summary</th>
                                <th class="px-5 py-3.5 text-center">Invoice Status</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:border-slate-800">
                            @forelse($project->documents as $doc)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $doc->title }}</div>
                                        <div class="text-xs text-slate-400">{{ $doc->document_type_label }} &bull; {{ $doc->formatted_file_size }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-xs">
                                        @if($doc->dc_number)
                                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $doc->dc_number }}</span>
                                            <div class="text-[11px] text-slate-400">{{ $doc->dc_date ? $doc->dc_date->format('d M Y') : '' }}</div>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-xs max-w-xs">
                                        {{ $doc->items_summary ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        @if($doc->document_type === 'delivery_challan')
                                            @if($doc->is_invoiced)
                                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                                    Invoiced: {{ $doc->invoice_number }}
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                                    Ready to Invoice
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('projects.documents.view', $doc) }}" target="_blank" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 transition" title="Preview">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <a href="{{ route('projects.documents.download', $doc) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 transition" title="Download">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>
                                            @if(auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('projects.documents.destroy', $doc) }}" onsubmit="return confirm('Delete this document?')" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-slate-400 text-xs">
                                        No documents or Delivery Challans uploaded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 4: WORK DAYS & DAILY WAGES                                   --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'work_days'" x-cloak class="space-y-6">

            {{-- Summary KPI Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                    <span class="text-xs font-semibold uppercase text-slate-400">Total Man-Days</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $totalManDays }} Days</div>
                </div>
                <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                    <span class="text-xs font-semibold uppercase text-slate-400">Cabling Metres Done</span>
                    <div class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1 font-mono">{{ $totalCablingMetres }} m</div>
                </div>
                <div class="p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                    <span class="text-xs font-semibold uppercase text-slate-400">Labor Wages Spent</span>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 font-mono">₹{{ number_format($totalLaborCost, 2) }}</div>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Work Day Logs &amp; Attendance Records</h3>
                @if(!auth()->user()->isStaff())
                    <button type="button" @click="workDayModalOpen = true" class="btn-primary text-xs shadow-md">
                        + Log Daily Work Day
                    </button>
                @endif
            </div>

            <div class="space-y-4">
                @forelse($project->workDays as $wd)
                    <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                        <div class="px-5 py-3.5 bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="font-black text-sm font-mono text-slate-900 dark:text-white">
                                    📅 {{ $wd->work_date->format('d M Y (l)') }}
                                </span>
                                @if($wd->notes)
                                    <span class="text-xs text-slate-500 italic">"{{ $wd->notes }}"</span>
                                @endif
                            </div>

                            @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('projects.work-days.destroy', ['project' => $project, 'workDay' => $wd]) }}" onsubmit="return confirm('Remove this work day log?')" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-semibold">Delete Work Day</button>
                            </form>
                            @endif
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                                <thead class="text-xs uppercase font-bold text-slate-400 border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th class="px-5 py-2.5">Worker Name</th>
                                        <th class="px-5 py-2.5">Shift Type</th>
                                        <th class="px-5 py-2.5">Rate Snapshot</th>
                                        <th class="px-5 py-2.5">Cabling Metres</th>
                                        <th class="px-5 py-2.5">Extras</th>
                                        <th class="px-5 py-2.5 text-right">Row Total</th>
                                        <th class="px-5 py-2.5 text-center">Settlement Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($wd->attendances as $att)
                                        <tr>
                                            <td class="px-5 py-3 font-bold text-slate-900 dark:text-white">
                                                {{ $att->worker->name }}
                                            </td>
                                            <td class="px-5 py-3">
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $att->attendance_type === 'full_day' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' }}">
                                                    {{ $att->attendance_type === 'full_day' ? 'Full Day' : 'Half Day' }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3 font-mono text-xs">
                                                ₹{{ number_format($att->daily_rate_snapshot, 2) }}
                                            </td>
                                            <td class="px-5 py-3 font-mono text-xs">
                                                {{ $att->cabling_metres > 0 ? $att->cabling_metres . ' m (+₹' . number_format($att->cabling_amount, 2) . ')' : '—' }}
                                            </td>
                                            <td class="px-5 py-3 text-xs">
                                                {{ $att->extra_amount > 0 ? '+₹' . number_format($att->extra_amount, 2) . ' (' . $att->extra_description . ')' : '—' }}
                                            </td>
                                            <td class="px-5 py-3 text-right font-bold font-mono text-slate-900 dark:text-white">
                                                ₹{{ number_format($att->total_amount, 2) }}
                                            </td>
                                            <td class="px-5 py-3 text-center">
                                                @if($att->is_paid)
                                                    <div class="inline-flex items-center gap-1">
                                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                                            🔒 Paid &amp; Locked
                                                        </span>
                                                        @if(auth()->user()->isAdmin())
                                                            <form method="POST" action="{{ route('projects.attendances.unlock', $att) }}" class="inline-block" onsubmit="return confirm('Unlock this attendance record?')">
                                                                @csrf
                                                                <button type="submit" class="text-[10px] text-rose-500 hover:underline">Unlock</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                                        Unpaid
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 text-slate-400 text-xs">
                        No daily work shifts logged yet. Click "+ Log Daily Work Day" to record technician &amp; worker shifts.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 5: IP DEVICES REGISTER                                       --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'ip_devices'" x-cloak class="space-y-6">
            <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Network IP Devices Register</h3>
                        <p class="text-xs text-slate-500">Camera addressing, ports, RTSP streams &amp; encrypted credentials</p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('projects.devices.export', $project) }}" class="btn-secondary text-xs flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Export CSV</span>
                        </a>
                        @if(!auth()->user()->isStaff())
                            <button type="button" @click="bulkDeviceModalOpen = true" class="btn-secondary text-xs flex items-center gap-1">
                                ⚡ Bulk Sequential Generator
                            </button>
                            <button type="button" @click="deviceModalOpen = true" class="btn-primary text-xs shadow-md">
                                + Add Device
                            </button>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="px-5 py-3.5">Device Name</th>
                                <th class="px-5 py-3.5">IP Address</th>
                                <th class="px-5 py-3.5">Ports</th>
                                <th class="px-5 py-3.5">Username</th>
                                <th class="px-5 py-3.5">Password</th>
                                <th class="px-5 py-3.5 text-center">Status</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($project->ipDevices as $dev)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $dev->device_name }}</div>
                                        <div class="text-xs text-slate-400">{{ $dev->device_type ?? 'Camera' }} &bull; {{ $dev->location ?? 'Main Area' }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 font-mono font-bold text-xs text-blue-600 dark:text-blue-400">
                                        {{ $dev->ip_address }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs font-mono">
                                        Web: {{ $dev->web_port ?? 80 }} &bull; RTSP: {{ $dev->rtsp_port ?? 554 }}
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-xs">
                                        {{ $dev->username ?? 'admin' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs font-mono">
                                        <div class="flex items-center gap-2">
                                            <span x-text="revealedPasswords[{{ $dev->id }}] ? revealedPasswords[{{ $dev->id }}] : '••••••••'"></span>
                                            <button type="button" @click="revealPassword({{ $dev->id }})" class="text-slate-400 hover:text-blue-600 cursor-pointer" title="Reveal Password (Logged to Audit Trail)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                            {{ ucfirst($dev->status ?? 'Configured') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        @if(auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('projects.devices.destroy', ['project' => $project, 'device' => $dev]) }}" onsubmit="return confirm('Delete this IP device?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold">
                                                Delete
                                            </button>
                                        </form>
                                        @else
                                            <span class="text-slate-400 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center text-slate-400 text-xs">
                                        No IP devices configured yet. Use "⚡ Bulk Sequential Generator" to auto-populate camera IPs.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- MODALS SECTION                                                   --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}

        {{-- Admin Approval Modal --}}
        <div x-show="approveModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="approveModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Approve &amp; Assign Executing Company</h3>
                    <button type="button" @click="approveModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('projects.approve', $project) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Select Executing Company *</label>
                        <select name="company_id" required class="w-full px-3.5 py-2.5 text-sm font-bold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($companies as $comp)
                                <option value="{{ $comp->id }}" {{ $project->company_id == $comp->id ? 'selected' : '' }}>
                                    {{ $comp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Per-Metre Cabling Rate (₹/m) *</label>
                        <input type="number" step="0.5" name="per_metre_rate" value="{{ old('per_metre_rate', $project->per_metre_rate ?? 8.0) }}" required class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Approved Project Value (₹)</label>
                        <input type="number" step="0.5" name="approved_value" value="{{ old('approved_value', $project->budget) }}" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Customer PO Number</label>
                        <input type="text" name="po_number" value="{{ old('po_number', $project->po_number) }}" placeholder="e.g. PO-892" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-mono">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="approveModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Confirm Approval</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Admin Rejection Modal --}}
        <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="rejectModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Reject Project</h3>
                    <button type="button" @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('projects.reject', $project) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Rejection Reason *</label>
                        <textarea name="rejection_reason" rows="3" required placeholder="Specify why this project cannot be approved..." class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="rejectModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-rose-600 text-white hover:bg-rose-700 shadow-md">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Add Material Modal --}}
        <div x-show="materialModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="materialModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Add Material Line Item</h3>
                    <button type="button" @click="materialModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('projects.materials.store', $project) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Item Description *</label>
                        <input type="text" name="item_name" required placeholder="e.g. 4-Channel SMPS Power Supply" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Quantity *</label>
                            <input type="number" step="any" min="0.01" name="quantity" required placeholder="1" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-bold font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Unit *</label>
                            <select name="unit" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                                <option value="nos">nos</option>
                                <option value="mtr">mtr</option>
                                <option value="box">box</option>
                                <option value="roll">roll</option>
                                <option value="pkt">pkt</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Sourcing Channel *</label>
                        <select name="source" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                            <option value="warehouse">Warehouse Stock Dispatch</option>
                            <option value="local_purchase">Direct Local Purchase</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Shop Name (if local purchase)</label>
                        <input type="text" name="shop_name" placeholder="Vendor / Hardware shop name" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="materialModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Add Item</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Upload Document / Delivery Challan Modal --}}
        <div x-show="documentModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="documentModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Upload Document / Delivery Challan</h3>
                    <button type="button" @click="documentModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('projects.documents.store', $project) }}" enctype="multipart/form-data" class="space-y-4"
                      x-data="{ docType: 'delivery_challan' }">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Document Type *</label>
                        <select name="document_type" x-model="docType" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-semibold">
                            <option value="delivery_challan">Delivery Challan (DC)</option>
                            <option value="purchase_order">Customer Purchase Order (PO)</option>
                            <option value="site_drawing">Site Blueprint / Wiring Drawing</option>
                            <option value="completion_signoff">Completion Sign-off Sheet</option>
                            <option value="invoice">Customer Invoice</option>
                            <option value="other">Other Document</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Document Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Delivery Challan #DC-892 (Camera &amp; Cables)" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div x-show="docType === 'delivery_challan'" class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">DC Number</label>
                            <input type="text" name="dc_number" placeholder="DC-2026-0042" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">DC Date</label>
                            <input type="date" name="dc_date" value="{{ now()->toDateString() }}" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                    </div>

                    <div x-show="docType === 'delivery_challan'">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Items Summary in Challan</label>
                        <textarea name="items_summary" rows="2" placeholder="e.g. 4x Dome Camera, 1x NVR, 150m CAT6 Cable" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Attach File (PDF, Image, up to 50MB) *</label>
                        <input type="file" name="file" required class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="documentModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Upload Document</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Log Daily Work Day Modal --}}
        <div x-show="workDayModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="workDayModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4 max-h-[90vh] overflow-y-auto"
                 x-data="{
                     workersList: {{ $workers->toJson() }},
                     rows: [
                         { worker_id: '', attendance_type: 'full_day', daily_rate: 0, cabling_metres: 0, extra_amount: 0, extra_description: '', row_total: 0 }
                     ],
                     addRow() {
                         this.rows.push({ worker_id: '', attendance_type: 'full_day', daily_rate: 0, cabling_metres: 0, extra_amount: 0, extra_description: '', row_total: 0 });
                     },
                     removeRow(i) {
                         this.rows.splice(i, 1);
                     },
                     updateRate(row) {
                         const found = this.workersList.find(w => w.id == row.worker_id);
                         if (found) {
                             row.daily_rate = found.daily_rate;
                             this.calcRow(row);
                         }
                     },
                     calcRow(row) {
                         const base = (row.attendance_type === 'half_day') ? (row.daily_rate / 2) : row.daily_rate;
                         const cabling = (parseFloat(row.cabling_metres) || 0) * {{ (float) ($project->per_metre_rate ?? 8.0) }};
                         const extra = parseFloat(row.extra_amount) || 0;
                         row.row_total = base + cabling + extra;
                     }
                 }">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">Log Daily Labor Work Day</h3>
                        <p class="text-xs text-slate-400">Snapshot worker rates &amp; calculate cabling metres extras (₹{{ $project->per_metre_rate ?? 8.0 }}/m)</p>
                    </div>
                    <button type="button" @click="workDayModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('projects.work-days.store', $project) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Work Shift Date *</label>
                            <input type="date" name="work_date" value="{{ now()->toDateString() }}" required class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">General Shift Notes</label>
                            <input type="text" name="notes" placeholder="e.g. Ground floor cabling completed" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase text-slate-400">Worker Attendance Rows</span>
                            <button type="button" @click="addRow()" class="btn-secondary text-xs">+ Add Worker</button>
                        </div>

                        <template x-for="(r, idx) in rows" :key="idx">
                            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40 space-y-2">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Worker *</label>
                                        <select :name="`attendances[${idx}][worker_id]`" x-model="r.worker_id" @change="updateRate(r)" required class="w-full px-2 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none font-bold">
                                            <option value="">Select Worker...</option>
                                            <template x-for="w in workersList" :key="w.id">
                                                <option :value="w.id" x-text="`${w.name} (₹${w.daily_rate}/d)`"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Shift *</label>
                                        <select :name="`attendances[${idx}][attendance_type]`" x-model="r.attendance_type" @change="calcRow(r)" class="w-full px-2 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                                            <option value="full_day">Full Day</option>
                                            <option value="half_day">Half Day</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Cabling Metres</label>
                                        <input type="number" step="1" min="0" :name="`attendances[${idx}][cabling_metres]`" x-model="r.cabling_metres" @input="calcRow(r)" placeholder="0" class="w-full px-2 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg font-mono outline-none">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-end">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Extra Amount (₹)</label>
                                        <input type="number" step="0.5" min="0" :name="`attendances[${idx}][extra_amount]`" x-model="r.extra_amount" @input="calcRow(r)" placeholder="0" class="w-full px-2 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg font-mono outline-none">
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Extra Reason</label>
                                        <input type="text" :name="`attendances[${idx}][extra_description]`" x-model="r.extra_description" placeholder="Travel / overtime..." class="w-full px-2 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-mono">
                                            Total: <strong class="text-slate-900 dark:text-white" x-text="`₹${r.row_total.toFixed(2)}`"></strong>
                                        </div>
                                        <button type="button" @click="removeRow(idx)" class="text-rose-500 hover:text-rose-700 text-xs font-bold" x-show="rows.length > 1">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="workDayModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Save Work Day Log</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Add Single IP Device Modal --}}
        <div x-show="deviceModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="deviceModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Add IP Device</h3>
                    <button type="button" @click="deviceModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('projects.devices.store', $project) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Device Name *</label>
                        <input type="text" name="device_name" required placeholder="e.g. CAM-01 Front Gate" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-bold">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">IP Address *</label>
                            <input type="text" name="ip_address" required placeholder="192.168.1.101" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono font-bold outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Device Type</label>
                            <input type="text" name="device_type" placeholder="Dome Camera" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Web Port</label>
                            <input type="number" name="web_port" value="80" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">RTSP Port</label>
                            <input type="number" name="rtsp_port" value="554" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Username</label>
                            <input type="text" name="username" value="admin" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Password (Encrypted)</label>
                            <input type="password" name="password" placeholder="••••••••" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="deviceModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Save Device</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bulk Sequential Generator Modal --}}
        <div x-show="bulkDeviceModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="bulkDeviceModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">Bulk Sequential IP Generator</h3>
                        <p class="text-xs text-slate-400">Generate multiple sequential camera IP rows in 1-click</p>
                    </div>
                    <button type="button" @click="bulkDeviceModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('projects.devices.bulk', $project) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Start IP Address *</label>
                        <input type="text" name="start_ip" value="192.168.1.101" required placeholder="192.168.1.101" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Total Devices Count *</label>
                            <input type="number" min="1" max="100" name="count" value="16" required class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Device Prefix</label>
                            <input type="text" name="name_prefix" value="Camera" required placeholder="Camera" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Device Type</label>
                        <input type="text" name="device_type" value="IP Camera" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Default Username</label>
                            <input type="text" name="username" value="admin" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Default Password</label>
                            <input type="password" name="password" placeholder="Pass123" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="bulkDeviceModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Generate All Rows</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
