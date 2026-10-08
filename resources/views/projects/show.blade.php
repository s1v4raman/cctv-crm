<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.index') }}" class="p-2 rounded-xl bg-white dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-white border border-slate-200 dark:border-slate-700 transition-colors" title="Back to Projects">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            {{ $project->project_code }}
                        </span>
                        <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">
                            {{ $project->title }}
                        </h2>
                        <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full border {{ $project->status_badge_classes }}">
                            {{ $project->status_label }}
                        </span>
                        <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full border {{ $project->project_type_badge_classes }}">
                            {{ $project->project_type_label }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 mt-0.5 text-xs text-blue-600 dark:text-blue-400 font-bold">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Company: {{ $project->company_name }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                <a href="{{ route('projects.edit', $project) }}" class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Edit Project
                </a>
                <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this project and all its uploaded files?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 rounded-xl text-rose-600 hover:text-white hover:bg-rose-600 border border-rose-200 dark:border-rose-900/50 transition-colors" title="Delete Project">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
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
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 dark:hover:text-white">&times;</button>
            </div>
        @endif

        {{-- Status Quick-Switcher Strip & Progress Control --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex-1 w-full">
                    <div class="flex items-center justify-between text-xs font-bold mb-2">
                        <span class="text-slate-600 dark:text-slate-400 uppercase tracking-wider">Overall Project Progress</span>
                        <span class="text-base text-slate-900 dark:text-white font-extrabold">{{ $project->progress_percentage }}% Completed</span>
                    </div>
                    <div class="w-full h-3 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $project->progress_bar_color }}" style="width: {{ $project->progress_percentage }}%"></div>
                    </div>
                </div>

                {{-- 1-Click Status Switcher Buttons --}}
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Set In Progress --}}
                    @if($project->status !== 'in_progress')
                    <form action="{{ route('projects.updateStatus', $project) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="in_progress">
                        <button type="submit" class="px-4 py-2.5 rounded-xl min-h-[42px] text-sm font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 hover:bg-amber-100 border border-amber-300 dark:border-amber-700 transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Set In Progress
                        </button>
                    </form>
                    @endif

                    {{-- Mark Done / Completed --}}
                    @if($project->status !== 'completed')
                    <form action="{{ route('projects.updateStatus', $project) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="px-4 py-2.5 rounded-xl min-h-[42px] text-sm font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 border border-emerald-300 dark:border-emerald-700 transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Mark as Done (100%)
                        </button>
                    </form>
                    @endif

                    {{-- Mark Incompleted / Action Needed --}}
                    @if($project->status !== 'incompleted')
                    <form action="{{ route('projects.updateStatus', $project) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="incompleted">
                        <button type="submit" class="px-4 py-2.5 rounded-xl min-h-[42px] text-sm font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 hover:bg-rose-100 border border-rose-300 dark:border-rose-700 transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Mark Incomplete
                        </button>
                    </form>
                    @endif

                    {{-- Put On Hold --}}
                    @if($project->status !== 'on_hold')
                    <form action="{{ route('projects.updateStatus', $project) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="on_hold">
                        <button type="submit" class="px-4 py-2.5 rounded-xl min-h-[42px] text-sm font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 border border-slate-300 dark:border-slate-700 transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Put On Hold
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- 2-Column Main Layout: Overview on Left, Documents & PDFs Hub on Right --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- LEFT COLUMN: Project Details & Information (5 Cols) --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- Company & Client Details Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 space-y-4">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Client & Site Information</h3>
                        <span class="text-xs font-mono text-slate-500">ID: #{{ $project->id }}</span>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-slate-400 block font-medium">Company Name:</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $project->company_name }}</span>
                        </div>

                        @if($project->site_address)
                        <div>
                            <span class="text-slate-400 block font-medium">Installation Site Address:</span>
                            <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $project->site_address }}</span>
                        </div>
                        @endif

                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div>
                                <span class="text-slate-400 block font-medium">Contact Person:</span>
                                <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $project->contact_person ?? 'Not specified' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block font-medium">Phone / Mobile:</span>
                                @if($project->contact_phone)
                                    <a href="tel:{{ $project->contact_phone }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">{{ $project->contact_phone }}</a>
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </div>
                        </div>

                        @if($project->contact_email)
                        <div>
                            <span class="text-slate-400 block font-medium">Email:</span>
                            <a href="mailto:{{ $project->contact_email }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">{{ $project->contact_email }}</a>
                        </div>
                        @endif

                        @if($project->lead)
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block font-medium">Linked CRM Lead:</span>
                            <a href="{{ route('leads.show', $project->lead) }}" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">
                                {{ $project->lead->customer_name }} &rarr;
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Hardware & Terminal Camera Specifications Card --}}
                @if(in_array($project->project_type, ['hardware_attendance', 'hardware_cctv', 'hybrid']) || !empty($project->hardware_specs))
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-purple-200 dark:border-purple-900/40 shadow-sm p-5 space-y-4">
                    <div class="border-b border-purple-100 dark:border-purple-900/40 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 flex items-center justify-center font-bold text-xs">
                                ⏱
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Terminal & Camera Hardware</h3>
                                <p class="text-[11px] text-slate-400">Attendance Terminals & Surveillance Network</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                            Hardware Ops
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-purple-50/60 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-900/30">
                            <span class="text-slate-400 block font-medium text-[11px]">Attendance Terminals:</span>
                            <span class="text-lg font-black text-purple-700 dark:text-purple-300 font-heading">
                                {{ $project->hardware_specs['terminal_count'] ?? 0 }} Units
                            </span>
                        </div>
                        <div class="p-3 rounded-xl bg-blue-50/60 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30">
                            <span class="text-slate-400 block font-medium text-[11px]">Camera Channels:</span>
                            <span class="text-lg font-black text-blue-700 dark:text-blue-300 font-heading">
                                {{ $project->hardware_specs['camera_count'] ?? 0 }} Cams
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        @if(!empty($project->hardware_specs['device_brand']))
                        <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 font-medium">Terminal Brand / Model:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $project->hardware_specs['device_brand'] }}</span>
                        </div>
                        @endif

                        @if(!empty($project->hardware_specs['terminal_ip']))
                        <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 font-medium">IP Subnet / Address:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $project->hardware_specs['terminal_ip'] }}</span>
                        </div>
                        @endif

                        <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 font-medium">Punch Sync Mode:</span>
                            <span class="font-bold text-purple-600 dark:text-purple-400 uppercase text-[11px]">
                                {{ str_replace('_', ' ', $project->hardware_specs['attendance_sync_mode'] ?? 'Face Recognition') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-1">
                            <span class="text-slate-400 font-medium">Cloud / CRM Sync:</span>
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Synchronized
                            </span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-purple-100 dark:border-purple-900/40">
                        <a href="{{ route('attendance.index') }}" class="w-full py-2 px-3 rounded-xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/40 dark:hover:bg-purple-900/60 border border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300 font-bold text-xs flex items-center justify-center gap-1.5 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Launch Employee Attendance Hub &rarr;
                        </a>
                    </div>
                </div>
                @endif

                {{-- Software Architecture & Webpage Stack Card --}}
                @if(in_array($project->project_type, ['software_web', 'hybrid']) || !empty($project->software_specs))
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-cyan-200 dark:border-cyan-900/40 shadow-sm p-5 space-y-4">
                    <div class="border-b border-cyan-100 dark:border-cyan-900/40 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-cyan-100 dark:bg-cyan-900/40 text-cyan-700 dark:text-cyan-300 flex items-center justify-center font-bold text-xs">
                                🌐
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Webpage & Software Stack</h3>
                                <p class="text-[11px] text-slate-400">Web App, Client Portal & Integrations</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800">
                            Software Web
                        </span>
                    </div>

                    <div class="space-y-3 text-xs">
                        @if(!empty($project->software_specs['webpage_url']))
                        <div>
                            <span class="text-slate-400 block font-medium mb-1">Webpage / Portal URL:</span>
                            <a href="{{ $project->software_specs['webpage_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-cyan-600 dark:text-cyan-400 font-bold hover:underline break-all">
                                <span>{{ $project->software_specs['webpage_url'] }}</span>
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                        @endif

                        @if(!empty($project->software_specs['repository_url']))
                        <div>
                            <span class="text-slate-400 block font-medium mb-1">Source Repository:</span>
                            <a href="{{ $project->software_specs['repository_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-slate-700 dark:text-slate-300 font-mono hover:text-cyan-500 break-all">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                                <span>{{ $project->software_specs['repository_url'] }}</span>
                            </a>
                        </div>
                        @endif

                        @if(!empty($project->software_specs['tech_stack']))
                        <div>
                            <span class="text-slate-400 block font-medium mb-1.5">Tech Stack:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach(explode(',', $project->software_specs['tech_stack']) as $tech)
                                    <span class="px-2 py-0.5 rounded-md bg-cyan-50 dark:bg-cyan-950/60 text-cyan-800 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800 text-[11px] font-bold">
                                        {{ trim($tech) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if(!empty($project->software_specs['deployment_server']))
                        <div>
                            <span class="text-slate-400 block font-medium">Hosting / Server:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $project->software_specs['deployment_server'] }}</span>
                        </div>
                        @endif

                        @if(!empty($project->software_specs['milestones']) && is_array($project->software_specs['milestones']))
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block font-medium mb-1.5">Software Milestones:</span>
                            <div class="space-y-1.5">
                                @foreach($project->software_specs['milestones'] as $m)
                                    <div class="flex items-center justify-between text-[11px] p-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/60">
                                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $m['name'] ?? 'Milestone' }}</span>
                                        <span class="font-bold uppercase text-[9px] px-1.5 py-0.5 rounded {{ ($m['status'] ?? '') === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : (($m['status'] ?? '') === 'in_progress' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300') }}">
                                            {{ $m['status'] ?? 'Pending' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Schedule & Financials Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3">
                        Timeline & Financials
                    </h3>

                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 block font-medium">Start Date:</span>
                            <span class="text-slate-800 dark:text-slate-200 font-bold">
                                {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M, Y') : 'Not set' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Target Deadline:</span>
                            <span class="text-slate-800 dark:text-slate-200 font-bold">
                                {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d M, Y') : 'Not set' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Budget Valuation:</span>
                            <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400">
                                {{ $project->budget ? '₹' . number_format($project->budget, 2) : '₹0.00' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Actual Cost:</span>
                            <span class="text-base font-extrabold text-slate-800 dark:text-slate-200">
                                {{ $project->actual_cost ? '₹' . number_format($project->actual_cost, 2) : '₹0.00' }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
                        <span class="text-slate-400 block font-medium mb-1">Lead Engineer / Assignee:</span>
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-extrabold flex items-center justify-center text-xs">
                                {{ substr($project->assignedUser->name ?? 'U', 0, 1) }}
                            </span>
                            <span class="text-slate-900 dark:text-white font-bold">{{ $project->assignedUser->name ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Scope Description & Notes --}}
                @if($project->description || $project->notes)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3">
                        Technical Scope & Notes
                    </h3>
                    @if($project->description)
                    <div>
                        <span class="text-xs text-slate-400 block font-medium mb-1">Scope Description:</span>
                        <p class="text-xs text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl">
                            {{ $project->description }}
                        </p>
                    </div>
                    @endif
                    @if($project->notes)
                    <div>
                        <span class="text-xs text-slate-400 block font-medium mb-1">Internal Notes:</span>
                        <p class="text-xs text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 p-3 rounded-xl">
                            {{ $project->notes }}
                        </p>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- RIGHT COLUMN: Project Documents & PDFs Hub (7 Cols) --}}
            <div class="lg:col-span-7 space-y-6">

                {{-- Document Upload Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Upload Project Documents & PDFs</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Attach blueprints, CAD floor plans, contracts, site sign-offs</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('projects.documents.upload', $project) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="document_title" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Document Title / Category (Optional)
                                </label>
                                <input type="text" id="document_title" name="document_title" placeholder="e.g. CCTV Blueprint Layout Rev 2"
                                       class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="upload_notes" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Notes (Optional)
                                </label>
                                <input type="text" id="upload_notes" name="notes" placeholder="e.g. Approved by client site supervisor"
                                       class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-6 text-center hover:border-blue-500 transition-colors bg-slate-50/50 dark:bg-slate-800/30">
                            <svg class="mx-auto h-10 w-10 text-slate-400 mb-3" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex items-center justify-center gap-3 flex-wrap">
                                <label for="project_files" class="cursor-pointer rounded-xl bg-white dark:bg-slate-800 px-4 py-2.5 font-bold text-sm text-blue-600 hover:text-blue-500 border border-slate-300 dark:border-slate-700 shadow-sm">
                                    <span>📎 Select PDF / File(s)</span>
                                    <input id="project_files" name="documents[]" type="file" multiple required class="sr-only" accept=".pdf,.doc,.docx,.xls,.xlsx,.dwg,.jpg,.jpeg,.png,.zip">
                                </label>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">PDF, CAD, Word, Excel, Images up to 30MB each</p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-amber">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Upload & Attach Files
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Attached Documents List --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Project Documents & Attached PDFs</h3>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $project->documents->count() }}
                            </span>
                        </div>
                    </div>

                    @if($project->documents->isEmpty())
                        <div class="text-center py-8 text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-xs font-semibold">No documents or PDFs uploaded yet</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">Use the upload box above to attach architectural drawings, blueprints, or handover reports.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($project->documents as $doc)
                                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-800/40">
                                    {{-- File Info --}}
                                    <div class="flex items-start gap-3 min-w-0">
                                        {{-- File Icon badge --}}
                                        @if($doc->isPdf())
                                            <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/40 flex flex-col items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                <span class="text-[9px] font-black uppercase">PDF</span>
                                            </div>
                                        @elseif($doc->isImage())
                                            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/40 flex flex-col items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="text-[9px] font-black uppercase">IMG</span>
                                            </div>
                                        @else
                                            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900/40 flex flex-col items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span class="text-[9px] font-black uppercase">DOC</span>
                                            </div>
                                        @endif

                                        {{-- Title and Details --}}
                                        <div class="min-w-0">
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate" title="{{ $doc->file_name }}">
                                                {{ $doc->title }}
                                            </h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 font-mono truncate mt-0.5">
                                                {{ $doc->file_name }} &bull; {{ $doc->formatted_file_size }}
                                            </p>
                                            <div class="text-xs text-slate-400 flex items-center gap-2 mt-0.5">
                                                <span>{{ $doc->created_at->format('d M, Y H:i') }}</span>
                                                @if($doc->uploader)
                                                    <span>&bull; Uploaded by {{ $doc->uploader->name }}</span>
                                                @endif
                                            </div>
                                            @if($doc->notes)
                                                <p class="text-xs text-slate-600 dark:text-slate-300 italic mt-1 bg-white dark:bg-slate-900 px-2.5 py-1.5 rounded border border-slate-200 dark:border-slate-800">
                                                    {{ $doc->notes }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Actions: View PDF, Download, Delete --}}
                                    <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">
                                        @if($doc->isPdf() || $doc->isImage())
                                            <a href="{{ route('projects.documents.view', [$project, $doc]) }}" target="_blank"
                                               class="btn-secondary text-blue-600 dark:text-blue-400" title="Preview in browser">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                View PDF
                                            </a>
                                        @endif

                                        <a href="{{ route('projects.documents.download', [$project, $doc]) }}"
                                           class="btn-secondary" title="Download file">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Download
                                        </a>

                                        @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                        <form action="{{ route('projects.documents.destroy', [$project, $doc]) }}" method="POST" onsubmit="return confirm('Delete document \'{{ $doc->file_name }}\'?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-slate-200 dark:border-slate-700 transition-colors" title="Delete file">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
