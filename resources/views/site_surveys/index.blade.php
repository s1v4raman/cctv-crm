<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Site Surveys & Audits</h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-amber-500/20 text-amber-600 dark:text-amber-400 px-2 py-0.5 rounded border border-amber-500/30">Field Inspection</span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pre-installation field evaluations, premises blueprints, and recommended hardware specs</p>
            </div>
            <a href="{{ route('site-surveys.create') }}" class="btn-amber" id="btn-new-survey">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Schedule Survey
            </a>
        </div>
    </x-slot>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if(session('status'))
                <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2">
                    <span>✅ {{ session('status') }}</span>
                </div>
            @endif

            <div class="pg-card">
                {{-- Search & filter bar --}}
                <form method="GET" action="{{ route('site-surveys.index') }}" class="p-4 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b1120] flex items-center gap-2 flex-wrap">
                    <div class="relative flex-1 min-w-[220px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer, address, contact, technician…" class="w-full text-sm min-h-[44px] rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-[#0f172a] text-slate-800 dark:text-white px-3.5 py-2.5">
                    </div>
                    <select name="status" onchange="this.form.submit()" class="text-sm min-h-[44px] rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-[#0f172a] text-slate-800 dark:text-white px-3.5 py-2.5">
                        <option value="">All Statuses</option>
                        <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                        <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                    <button type="submit" class="btn-amber min-h-[44px] px-5 py-2.5 text-sm font-bold rounded-xl transition shadow">
                    Search
                </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('site-surveys.index') }}" class="btn-secondary min-h-[44px] px-4 py-2.5 text-sm font-semibold rounded-xl transition">
                            Clear
                        </a>
                    @endif
                </form>

                @if($surveys->count())
                    <div class="overflow-x-auto">
                        <table class="surveys-table w-full">
                            <thead>
                                <tr>
                                    <th>Customer / Lead</th>
                                    <th>Scheduled Date</th>
                                    <th>Customer Site Address</th>
                                    <th>Assigned Field Tech</th>
                                    <th class="text-center">Cameras Rec.</th>
                                    <th class="text-center">Photos</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($surveys as $survey)
                                    <tr>
                                        <td>
                                            <div class="cell-name">
                                                <a href="{{ route('site-surveys.show', $survey) }}">{{ $survey->lead->customer_name }}</a>
                                            </div>
                                            @if($survey->lead->company_name)
                                                <div class="cell-sub">{{ $survey->lead->company_name }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-semibold text-xs text-slate-900 dark:text-white">{{ $survey->survey_date->format('d M Y') }}</div>
                                            <div class="cell-sub">{{ $survey->survey_date->diffForHumans() }}</div>
                                        </td>
                                        <td class="max-w-[220px] truncate text-xs text-slate-600 dark:text-slate-400">
                                            📍 {{ $survey->site_address ?: ($survey->lead->site_address ?? '—') }}
                                        </td>
                                        <td>
                                            <span class="font-semibold text-xs text-blue-600 dark:text-sky-400">{{ $survey->surveyedBy->name ?? '—' }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($survey->camera_count_recommended)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-extrabold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                                    {{ $survey->camera_count_recommended }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 text-xs">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($survey->photos->count())
                                                <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                    📷 {{ $survey->photos->count() }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 text-xs">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $survey->status }}">{{ ucfirst($survey->status) }}</span>
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-end gap-1.5">
                                                <a href="{{ route('site-surveys.show', $survey) }}" class="btn-ticket">
                                                    View Audit
                                                </a>
                                                <a href="{{ route('site-surveys.edit', $survey) }}" class="btn-edit">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('site-surveys.destroy', $survey) }}"
                                                      onsubmit="return confirm('Delete this survey and all photos?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-del">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($surveys->hasPages())
                        <div class="pg-links">
                            {{ $surveys->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-16 px-4">
                        <svg class="w-12 h-12 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 9.75h.008v.008H3V9.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM18 6v12M6 6v12"/>
                        </svg>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">No site surveys scheduled yet</h4>
                        <p class="text-xs text-slate-500 mt-1">
                            <a href="{{ route('site-surveys.create') }}" class="text-blue-600 font-semibold hover:underline">Schedule the first survey →</a>
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
