<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Site Surveys</h2>
            <a href="{{ route('site-surveys.create') }}" class="btn-primary inline-flex items-center gap-1.5 shadow-2xs" id="btn-new-survey">
                <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Schedule Survey</span>
            </a>
        </div>
    </x-slot>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if(session('status'))
                <div class="mb-4 p-3.5 rounded border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Search & filter bar in one simple row without outer box --}}
            <form method="GET" action="{{ route('site-surveys.index') }}" class="mb-4 flex items-center gap-2.5 flex-wrap text-xs">
                <div class="relative flex-1 min-w-[220px] max-w-md flex items-center">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer, address, contact, technician…" 
                           style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important; padding-left: 36px !important; box-sizing: border-box;"
                           class="crm-input w-full text-xs border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-800 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <select name="status" onchange="this.form.submit()" 
                        style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important; min-width: 140px; box-sizing: border-box;"
                        class="crm-select text-xs border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 focus:outline-none focus:border-blue-500">
                    <option value="">All Statuses</option>
                    <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
                <button type="submit" 
                        style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important; box-sizing: border-box;"
                        class="crm-btn-primary px-4 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition inline-flex items-center gap-1.5 justify-center cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter</span>
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('site-surveys.index') }}" 
                       style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important; box-sizing: border-box;"
                       class="crm-btn-secondary px-3.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-700 dark:text-slate-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Clear</span>
                    </a>
                @endif
            </form>

                {{-- Table container with only 1px border --}}
                <div class="overflow-x-auto w-full border border-[#ddd] dark:border-slate-800 rounded-[4px] bg-white dark:bg-[#0f172a]">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th class="min-w-[200px]" style="min-width: 200px;">Customer</th>
                                <th class="whitespace-nowrap">Date</th>
                                <th>Address</th>
                                <th class="whitespace-nowrap">Technician</th>
                                <th class="text-center">Cameras</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($surveys as $survey)
                                @php
                                    $fullAddress = $survey->site_address ?: ($survey->lead->site_address ?? '—');
                                    $surveyStatusColors = [
                                        'completed' => '#16a34a',
                                        'scheduled' => '#d97706',
                                        'pending'   => '#d97706',
                                        'cancelled' => '#dc2626',
                                    ];
                                    $sColor = $surveyStatusColors[strtolower($survey->status)] ?? '#d97706';
                                @endphp
                                <tr>
                                    <td class="col-name min-w-[200px]" style="min-width: 200px;">
                                        <div class="font-semibold text-sm text-slate-900 dark:text-white">
                                            <a href="{{ route('site-surveys.show', $survey) }}">{{ $survey->lead->customer_name }}</a>
                                        </div>
                                        @if($survey->lead->company_name)
                                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $survey->lead->company_name }}</div>
                                        @endif
                                    </td>
                                    <td class="col-date whitespace-nowrap text-sm">
                                        {{ $survey->survey_date->format('d M Y') }}
                                    </td>
                                    <td class="text-sm text-slate-900 dark:text-white leading-relaxed">
                                        {{ $fullAddress }}
                                    </td>
                                    <td class="col-tech whitespace-nowrap text-sm">
                                        {{ $survey->surveyedBy->name ?? '—' }}
                                    </td>
                                    <td class="text-center text-sm">
                                        {{ $survey->camera_count_recommended ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap text-sm">
                                        <span class="status-text" style="color: {{ $sColor }}; font-weight: bold;">
                                            {{ ucfirst(strtolower($survey->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-right whitespace-nowrap text-sm table-actions">
                                        <a href="{{ route('site-surveys.show', $survey) }}" class="action-link inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-slate-700 dark:text-slate-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>View</span>
                                        </a>
                                        <a href="{{ route('site-surveys.edit', $survey) }}" class="action-link inline-flex items-center gap-1 ml-2">
                                            <svg class="w-3 h-3 text-slate-700 dark:text-slate-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            <span>Edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('site-surveys.destroy', $survey) }}" class="inline ml-2"
                                              onsubmit="return confirm('Delete this survey and all photos?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="danger-link inline-flex items-center gap-1 cursor-pointer">
                                                <svg class="w-3 h-3 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-6 text-slate-500">
                                        No site surveys yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{-- Total count shown below the table --}}
                    <div class="px-4 py-2.5 border-t border-[#ddd] dark:border-slate-800 bg-[#fbfbfb] dark:bg-slate-900/60 flex items-center justify-between text-xs text-slate-500">
                        <span class="font-medium">Total: {{ $surveys->total() }}</span>
                        @if($surveys->hasPages())
                            <div class="text-xs">
                                {{ $surveys->links() }}
                            </div>
                        @endif
                    </div>
                </div>

        </div>
    </div>
</x-app-layout>
