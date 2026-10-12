<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Site Surveys</h2>
            <a href="{{ route('site-surveys.create') }}" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition" id="btn-new-survey">
                + Schedule Survey
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

            @if($surveys->count())
                {{-- Table container with only 1px border --}}
                <div class="overflow-x-auto w-full border border-[#ddd] dark:border-slate-800 rounded-[4px] bg-white dark:bg-[#0f172a]">
                    <table class="surveys-table w-full">
                        <thead>
                            <tr>
                                <th class="min-w-[200px]" style="min-width: 200px;">Customer</th>
                                <th>Date</th>
                                <th>Address</th>
                                <th>Technician</th>
                                <th class="text-center">Cameras</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($surveys as $survey)
                                @php
                                    $fullAddress = $survey->site_address ?: ($survey->lead->site_address ?? '—');
                                @endphp
                                <tr>
                                    <td class="min-w-[200px]" style="min-width: 200px;">
                                        <div class="font-semibold text-sm text-slate-900 dark:text-white">
                                            <a href="{{ route('site-surveys.show', $survey) }}" class="hover:text-blue-600 hover:underline">{{ $survey->lead->customer_name }}</a>
                                        </div>
                                        @if($survey->lead->company_name)
                                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $survey->lead->company_name }}</div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">
                                        {{ $survey->survey_date->format('d M Y') }}
                                    </td>
                                    <td class="text-sm text-slate-900 dark:text-white max-w-[260px] leading-relaxed" title="{{ $fullAddress }}">
                                        {{ $fullAddress }}
                                    </td>
                                    <td class="whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                        {{ $survey->surveyedBy->name ?? '—' }}
                                    </td>
                                    <td class="text-center text-sm text-slate-900 dark:text-white">
                                        {{ $survey->camera_count_recommended ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap text-sm font-medium">
                                        @if($survey->status === 'completed')
                                            <span class="text-emerald-600 dark:text-emerald-400">Completed</span>
                                        @elseif($survey->status === 'cancelled')
                                            <span class="text-rose-600 dark:text-rose-400">Cancelled</span>
                                        @else
                                            <span class="text-amber-600 dark:text-amber-400">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-right whitespace-nowrap text-sm">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <a href="{{ route('site-surveys.show', $survey) }}" class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:underline">View</a>
                                            <span class="text-slate-300 dark:text-slate-600">|</span>
                                            <a href="{{ route('site-surveys.edit', $survey) }}" class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:underline">Edit</a>
                                            <span class="text-slate-300 dark:text-slate-600">|</span>
                                            <form method="POST" action="{{ route('site-surveys.destroy', $survey) }}" class="inline"
                                                  onsubmit="return confirm('Delete this survey and all photos?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-700 dark:text-red-500 hover:underline cursor-pointer bg-transparent border-0 p-0 font-medium">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($surveys->hasPages())
                    <div class="pg-links mt-3">
                        {{ $surveys->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-16 px-4 border border-[#ddd] dark:border-slate-800 rounded-[4px] bg-white dark:bg-[#0f172a]">
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
</x-app-layout>
