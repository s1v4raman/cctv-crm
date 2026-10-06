<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('portal.dashboard') }}" class="text-xs font-semibold hover:underline" style="color: var(--crm-accent, #2563eb);">← Back to Overview</a>
                    <span class="text-slate-400">/</span>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Feasibility</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-1 font-heading">Site Surveys & Technical Feasibility</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Review engineer inspection reports, camera placement diagrams, cabling estimates, and site photos.</p>
            </div>
            <a href="{{ route('portal.tickets.create') }}" 
               class="crm-customer-action-btn inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
               style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Request New Site Survey</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(!$hasLead || $surveys->isEmpty())
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-10 border border-slate-200/90 dark:border-slate-800 shadow-xs text-center max-w-2xl mx-auto space-y-4">
                    <div class="w-16 h-16 rounded-2xl crm-customer-icon-box flex items-center justify-center mx-auto font-bold text-2xl" style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.1); color: var(--crm-accent, #2563eb); border: 1px solid rgba(var(--crm-accent-rgb, 37, 99, 235), 0.25);">
                        📐
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading">No Site Surveys On Record</h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        A CCTV Site Survey helps our field engineers inspect your property, identify optimal camera mounting angles, eliminate blind spots, and calculate exact cable runs.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('portal.tickets.create') }}" 
                           class="crm-customer-action-btn inline-flex items-center gap-2 px-6 py-2.5 text-white font-bold text-xs rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
                           style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                            Book Free Site Survey Now →
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-6 py-3.5">Survey Reference</th>
                                    <th class="px-6 py-3.5">Inspection Date</th>
                                    <th class="px-6 py-3.5">Field Engineer</th>
                                    <th class="px-6 py-3.5">Recommended Units</th>
                                    <th class="px-6 py-3.5">Cable Run Estimate</th>
                                    <th class="px-6 py-3.5">Site Photos</th>
                                    <th class="px-6 py-3.5">Status</th>
                                    <th class="px-6 py-3.5 text-right">Report Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium text-slate-700 dark:text-slate-300">
                                @foreach($surveys as $survey)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                        <td class="px-6 py-4">
                                            <div class="font-mono text-xs font-bold" style="color: var(--crm-accent, #2563eb);">SURVEY #{{ $survey->id }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate max-w-xs">
                                                📍 {{ $survey->site_address ?: ($lead->site_address ?: 'Registered Site') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                            <div class="font-bold text-slate-900 dark:text-white">{{ $survey->survey_date ? $survey->survey_date->format('d M Y') : 'N/A' }}</div>
                                            <div class="text-[10px] text-slate-400 dark:text-slate-500">{{ $survey->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($survey->surveyedBy)
                                                <div class="font-bold text-slate-900 dark:text-white">{{ $survey->surveyedBy->name }}</div>
                                                <div class="text-[10px] text-slate-500 dark:text-slate-400">Certified Field Tech</div>
                                            @else
                                                <span class="text-slate-400 italic">Pending Assignment</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-semibold text-xs">
                                                📹 {{ $survey->camera_count_recommended ?: 'TBD' }} Cameras
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-mono font-bold text-slate-700 dark:text-slate-300">
                                            {{ $survey->cable_length_estimate ? $survey->cable_length_estimate . ' Mtrs' : 'TBD' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-300 font-semibold">
                                                📷 {{ $survey->photos->count() }} photos
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($survey->status === 'completed')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    ✓ Inspection Done
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                    ⏳ Scheduled Visit
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('portal.surveys.show', $survey) }}" 
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 font-semibold text-xs rounded-xl transition">
                                                <span>View Feasibility</span>
                                                <span>→</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($surveys, 'hasPages') && $surveys->hasPages())
                        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                            {{ $surveys->links() }}
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
