<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Site Surveys</h2>
            <a href="{{ route('site-surveys.create') }}" class="btn-primary" id="btn-new-survey">
                Schedule Survey
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
            <form method="GET" action="{{ route('site-surveys.index') }}" class="mb-4 flex items-center gap-2 flex-wrap">
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer, address, contact, technician…" 
                           style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                           class="crm-input w-full text-sm border border-[#ddd] dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 focus:outline-none focus:border-slate-400">
                </div>
                <select name="status" onchange="this.form.submit()" 
                        style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                        class="crm-select text-sm border border-[#ddd] dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-800 dark:text-white px-3 focus:outline-none focus:border-slate-400">
                    <option value="">All Statuses</option>
                    <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
                <button type="submit" 
                        style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                        class="crm-btn-primary px-4 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white transition">
                    Search
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('site-surveys.index') }}" 
                       style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                       class="crm-btn-secondary px-3 border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition inline-flex items-center">
                        Clear
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
                                        <a href="{{ route('site-surveys.show', $survey) }}" class="action-link">View</a>
                                        <a href="{{ route('site-surveys.edit', $survey) }}" class="action-link">Edit</a>
                                        <form method="POST" action="{{ route('site-surveys.destroy', $survey) }}" class="inline"
                                              onsubmit="return confirm('Delete this survey and all photos?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="danger-link">Delete</button>
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
