<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('portal.dashboard') }}" class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                            {{ $siteSurvey->status === 'completed' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }}">
                            {{ $siteSurvey->status === 'completed' ? '✓ Inspection Completed' : '⏳ Scheduled Visit' }}
                        </span>
                        <span class="text-xs text-blue-600 dark:text-blue-400 font-mono font-bold">Survey #{{ $siteSurvey->id }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-0.5 font-heading">
                        Site Survey & Feasibility Report
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('portal.quotations') }}" class="px-4 py-2 rounded-xl bg-[#2563eb] hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider shadow-xs transition flex items-center gap-1.5">
                    <span>📄 Review Quotations →</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100 transition-colors duration-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Status Banner --}}
            @if($siteSurvey->status === 'completed')
                <div class="p-5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/50 rounded-2xl flex items-start gap-4 shadow-xs">
                    <div class="p-2.5 bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 rounded-xl text-base font-bold">✓</div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-emerald-900 dark:text-emerald-300 font-heading">On-Site Inspection Completed</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                            Our certified field engineer <strong class="text-slate-800 dark:text-white">{{ $siteSurvey->surveyedBy?->name ?? 'Technician' }}</strong> visited your site on <strong class="text-slate-800 dark:text-white">{{ $siteSurvey->survey_date->format('l, d M Y') }}</strong> and completed the technical assessment.
                        </p>
                    </div>
                </div>
            @else
                <div class="p-5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50 rounded-2xl flex items-start gap-4 shadow-xs">
                    <div class="p-2.5 bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 rounded-xl text-base font-bold">📅</div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-amber-900 dark:text-amber-300 font-heading">Site Survey Scheduled</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                            Engineer <strong class="text-slate-800 dark:text-white">{{ $siteSurvey->surveyedBy?->name ?? 'Field Engineer' }}</strong> is scheduled to inspect your installation site on <strong class="text-blue-600 dark:text-blue-400">{{ $siteSurvey->survey_date->format('l, d M Y') }}</strong>.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Technical Findings Overview --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Cameras Recommended</span>
                    <div class="text-3xl font-black text-slate-900 dark:text-white font-heading mt-2">{{ $siteSurvey->camera_count_recommended ? $siteSurvey->camera_count_recommended . ' Units' : 'Pending assessment' }}</div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Optimal coverage without blind spots</p>
                </div>
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estimated Cable Run</span>
                    <div class="text-3xl font-black text-blue-600 dark:text-blue-400 font-heading mt-2">{{ $siteSurvey->cable_length_estimate ? $siteSurvey->cable_length_estimate . ' Mtr' : 'Pending assessment' }}</div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">High quality CAT6 / RG-59 cabling</p>
                </div>
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Recorder Placement</span>
                    <div class="text-base font-bold text-slate-900 dark:text-white font-heading mt-3 truncate">{{ $siteSurvey->dvr_location ?: 'To be determined' }}</div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Secure NVR / Server rack spot</p>
                </div>
            </div>

            {{-- Site Observations & Technician Notes --}}
            <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-6 space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-lg">📝</span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">Site Assessment & Engineering Observations</h2>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <span class="font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-[10px] block">Installation Site Address</span>
                        <p class="text-slate-800 dark:text-white font-semibold bg-slate-50 dark:bg-slate-800/60 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 mt-1">
                            📍 {{ $siteSurvey->site_address ?: ($lead->site_address ?: 'Registered premises') }}
                        </p>
                    </div>

                    @if($siteSurvey->visit_notes)
                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-[10px] block">Engineer Recommendations & Coverage Details</span>
                            <p class="text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-200 dark:border-slate-700 mt-1 whitespace-pre-line leading-relaxed text-sm">
                                {{ $siteSurvey->visit_notes }}
                            </p>
                        </div>
                    @endif

                    @if($siteSurvey->power_availability)
                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-[10px] block">Power Supply & Backup</span>
                            <p class="text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 mt-1">
                                🔌 {{ $siteSurvey->power_availability }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Site Inspection Photos Gallery --}}
            @if($siteSurvey->photos->count() > 0)
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-6 space-y-4" x-data="{ openImage: null }">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">📷</span>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">On-Site Inspection Photos ({{ $siteSurvey->photos->count() }})</h2>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Captured by field technician</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach($siteSurvey->photos as $photo)
                            <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-xs bg-slate-50 dark:bg-slate-800 hover:border-blue-400 dark:hover:border-blue-700 transition cursor-pointer"
                                 @click="openImage = '{{ asset('storage/site-surveys/' . $photo->filename) }}'">
                                <img src="{{ asset('storage/site-surveys/' . $photo->filename) }}" alt="Site Photo" class="w-full h-36 object-cover">
                                @if($photo->caption)
                                    <div class="p-2.5 text-[11px] font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-850 border-t border-slate-200 dark:border-slate-700 truncate">
                                        {{ $photo->caption }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Lightbox Modal --}}
                    <div x-show="openImage" style="display: none;" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4" @click="openImage = null">
                        <div class="relative max-w-4xl max-h-[90vh]">
                            <img :src="openImage" class="rounded-2xl max-h-[85vh] max-w-full shadow-2xl border border-slate-700">
                            <button type="button" @click="openImage = null" class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center shadow-lg border border-slate-600">✕</button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
