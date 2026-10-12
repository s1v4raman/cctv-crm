<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('site-surveys.index') }}" class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition inline-flex items-center gap-1 mb-1">
                    ← Back
                </a>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">
                        Site Survey #{{ $siteSurvey->id }}
                    </h2>
                    <span class="text-sm font-semibold @if($siteSurvey->status === 'completed') text-emerald-600 dark:text-emerald-400 @elseif($siteSurvey->status === 'cancelled') text-rose-600 dark:text-rose-400 @else text-amber-600 dark:text-amber-400 @endif">
                        ({{ ucfirst($siteSurvey->status) }})
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('estimator.index', ['survey_id' => $siteSurvey->id, 'lead_id' => $siteSurvey->lead_id]) }}" 
                   class="px-3.5 py-1.5 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    Estimator & BOM
                </a>
                <a href="{{ route('site-surveys.edit', $siteSurvey) }}" 
                   class="px-3.5 py-1.5 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    Edit
                </a>
                <a href="{{ route('quotations.create', $siteSurvey->lead) }}" 
                   class="px-4 py-1.5 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition shadow-xs">
                    Create Quotation
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
        @if(session('status'))
            <div class="mb-5 p-3.5 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        {{-- Details: Single clean two-column table --}}
        <div class="bg-white dark:bg-[#0f172a] rounded border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
            <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Survey Details</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr>
                            <td class="w-1/3 py-3 px-5 text-slate-500 dark:text-slate-400 font-medium">Customer</td>
                            <td class="py-3 px-5 text-slate-900 dark:text-white font-medium">
                                <a href="{{ route('leads.show', $siteSurvey->lead) }}" class="hover:text-blue-600 hover:underline">
                                    {{ $siteSurvey->lead->customer_name }}@if($siteSurvey->lead->company_name) ({{ $siteSurvey->lead->company_name }})@endif
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="w-1/3 py-3 px-5 text-slate-500 dark:text-slate-400 font-medium">Survey Date</td>
                            <td class="py-3 px-5 text-slate-900 dark:text-white">
                                {{ $siteSurvey->survey_date->format('d M Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="w-1/3 py-3 px-5 text-slate-500 dark:text-slate-400 font-medium">Engineer</td>
                            <td class="py-3 px-5 text-slate-900 dark:text-white">
                                {{ $siteSurvey->surveyedBy->name ?? '—' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="w-1/3 py-3 px-5 text-slate-500 dark:text-slate-400 font-medium">Address</td>
                            <td class="py-3 px-5 text-slate-900 dark:text-white leading-relaxed">
                                {{ $siteSurvey->site_address ?: ($siteSurvey->lead->site_address ?? '—') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="w-1/3 py-3 px-5 text-slate-500 dark:text-slate-400 font-medium">Contact Person</td>
                            <td class="py-3 px-5 text-slate-900 dark:text-white">
                                {{ $siteSurvey->contact_person ?: ($siteSurvey->lead->customer_name ?? '—') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="w-1/3 py-3 px-5 text-slate-500 dark:text-slate-400 font-medium">Contact Phone</td>
                            <td class="py-3 px-5 text-slate-900 dark:text-white">
                                @php $phone = $siteSurvey->contact_phone ?: $siteSurvey->lead->phone; @endphp
                                @if($phone)
                                    <a href="tel:{{ $phone }}" class="hover:underline">{{ $phone }}</a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="w-1/3 py-3 px-5 text-slate-500 dark:text-slate-400 font-medium">Cameras Recommended</td>
                            <td class="py-3 px-5 text-slate-900 dark:text-white">
                                {{ $siteSurvey->camera_count_recommended ?? '—' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="w-1/3 py-3 px-5 text-slate-500 dark:text-slate-400 font-medium">Cable Length</td>
                            <td class="py-3 px-5 text-slate-900 dark:text-white">
                                {{ $siteSurvey->cable_length_estimate ? ($siteSurvey->cable_length_estimate . ' m') : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="w-1/3 py-3 px-5 text-slate-500 dark:text-slate-400 font-medium">DVR / NVR Location</td>
                            <td class="py-3 px-5 text-slate-900 dark:text-white">
                                {{ $siteSurvey->dvr_location ?? '—' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="w-1/3 py-3 px-5 text-slate-500 dark:text-slate-400 font-medium">Power / UPS</td>
                            <td class="py-3 px-5 text-slate-900 dark:text-white">
                                {{ $siteSurvey->power_availability ?? '—' }}
                            </td>
                        </tr>
                        @if($siteSurvey->challenges)
                        <tr>
                            <td class="w-1/3 py-3 px-5 text-slate-500 dark:text-slate-400 font-medium">Challenges</td>
                            <td class="py-3 px-5 text-slate-900 dark:text-white leading-relaxed">
                                {{ $siteSurvey->challenges }}
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td class="w-1/3 py-3 px-5 text-slate-500 dark:text-slate-400 font-medium align-top">Notes</td>
                            <td class="py-3 px-5 text-slate-900 dark:text-white leading-relaxed">
                                <p class="text-sm text-slate-900 dark:text-white whitespace-pre-wrap">{{ $siteSurvey->visit_notes ?: '—' }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Photos Section --}}
        <div class="mt-6 bg-white dark:bg-[#0f172a] rounded border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Site Photos ({{ $siteSurvey->photos->count() }})</h3>
            @if($siteSurvey->photos->count())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mt-4">
                    @foreach($siteSurvey->photos as $photo)
                        <div class="rounded border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 overflow-hidden group relative">
                            <img src="{{ $photo->url() }}" alt="{{ $photo->original_name }}"
                                 class="w-full h-40 object-cover cursor-pointer group-hover:scale-105 transition duration-200"
                                 onclick="openLightbox('{{ $photo->url() }}')">
                            @if($photo->caption)
                                <div class="p-2 text-xs text-slate-700 dark:text-slate-300 truncate">{{ $photo->caption }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">No photos uploaded.</p>
            @endif
        </div>
    </div>

    {{-- Lightbox --}}
    <div id="lightbox" class="hidden fixed inset-0 bg-slate-950/90 backdrop-blur-md z-50 flex items-center justify-center p-4">
        <span id="lightbox-close" onclick="closeLightbox()" class="absolute top-5 right-6 text-white text-3xl cursor-pointer hover:text-amber-400 transition">&times;</span>
        <img id="lightbox-img" src="" alt="Site photo" class="max-w-[90vw] max-h-[85vh] rounded-2xl shadow-2xl border border-white/10">
    </div>

    <script>
        function openLightbox(src) {
            document.getElementById('lightbox-img').src = src;
            document.getElementById('lightbox').classList.remove('hidden');
        }
        function closeLightbox() {
            document.getElementById('lightbox').classList.add('hidden');
        }
        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this) closeLightbox();
        });
    </script>
</x-app-layout>
