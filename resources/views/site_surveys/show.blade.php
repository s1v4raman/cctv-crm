<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('site-surveys.index') }}" class="back-link">&larr; Back</a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-1">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Site Survey #{{ $siteSurvey->id }}</h2>
                    @php
                        $surveyStatusColor = '#2563eb';
                        if ($siteSurvey->status === 'completed') $surveyStatusColor = '#16a34a';
                        elseif ($siteSurvey->status === 'cancelled') $surveyStatusColor = '#dc2626';
                    @endphp
                    <span class="text-sm font-bold" style="color: {{ $surveyStatusColor }};">
                        {{ ucfirst($siteSurvey->status) }}
                    </span>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @php
                        $surveyAddress = $siteSurvey->site_address ?: ($siteSurvey->lead->site_address ?? '');
                        $surveyPhone = $siteSurvey->contact_phone ?: ($siteSurvey->lead->phone ?? '');
                    @endphp
                    @if($surveyPhone)
                        <a href="tel:{{ $surveyPhone }}" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs" title="Call Contact">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>Call Contact</span>
                        </a>
                    @endif
                    @if($surveyAddress)
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($surveyAddress) }}" target="_blank" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs" title="Open in Google Maps">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Location</span>
                        </a>
                    @endif
                    <a href="{{ route('estimator.index', ['survey_id' => $siteSurvey->id, 'lead_id' => $siteSurvey->lead_id]) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Estimator & BOM</span>
                    </a>
                    <a href="{{ route('site-surveys.edit', $siteSurvey) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Edit Details</span>
                    </a>
                    <a href="{{ route('quotations.create', $siteSurvey->lead) }}" class="btn-primary text-xs inline-flex items-center gap-1.5 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Create Quotation</span>
                    </a>
                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('site-surveys.destroy', $siteSurvey) }}" data-confirm="Delete this survey and all photos?" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-secondary text-xs cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-rose-600 hover:text-rose-700 hover:border-rose-300 dark:hover:border-rose-700 shadow-2xs transition" title="Delete Survey">
                                <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Delete</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div class="max-w-[1200px] mx-auto space-y-6">
        @if(session('status'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded text-xs">
                {{ session('status') }}
            </div>
        @endif

        {{-- Details: Two-column table in crm-section --}}
        <div class="crm-section">
            <h3>Survey Details</h3>
            <table class="crm-table">
                <tbody>
                    <tr>
                        <td class="w-1/3 text-slate-500 font-bold">Customer</td>
                        <td class="w-2/3 text-slate-900 dark:text-white">
                            <a href="{{ route('leads.show', $siteSurvey->lead) }}" class="action-link">
                                {{ $siteSurvey->lead->customer_name }}
                            </a>
                            @if($siteSurvey->lead->company_name)
                                <span class="text-slate-500 text-xs">({{ $siteSurvey->lead->company_name }})</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Survey Date</td>
                        <td class="text-slate-900 dark:text-white">{{ $siteSurvey->survey_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Surveyed By</td>
                        <td class="text-slate-900 dark:text-white">{{ $siteSurvey->surveyedBy->name ?? 'None' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Premises Address</td>
                        <td class="text-slate-900 dark:text-white">
                            <div class="flex items-center justify-between gap-2">
                                <span>{{ $siteSurvey->site_address ?: ($siteSurvey->lead->site_address ?? 'Not specified') }}</span>
                                @if($siteSurvey->site_address ?: $siteSurvey->lead?->site_address)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($siteSurvey->site_address ?: $siteSurvey->lead->site_address) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-800 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 px-2 py-0.5 rounded border border-slate-300 dark:border-slate-700 shrink-0 shadow-2xs">
                                        <svg class="w-3 h-3 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Map</span>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Contact Person</td>
                        <td class="text-slate-900 dark:text-white">{{ $siteSurvey->contact_person ?: ($siteSurvey->lead->customer_name ?? 'Not specified') }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Contact Phone</td>
                        <td class="text-slate-900 dark:text-white">
                            @php $phone = $siteSurvey->contact_phone ?: $siteSurvey->lead->phone; @endphp
                            @if($phone)
                                <div class="flex items-center justify-between gap-2">
                                    <a href="tel:{{ $phone }}" class="action-link">{{ $phone }}</a>
                                    <a href="tel:{{ $phone }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-800 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 px-2 py-0.5 rounded border border-slate-300 dark:border-slate-700 shrink-0 shadow-2xs">
                                        <svg class="w-3 h-3 text-slate-800 dark:text-slate-200 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        <span>Call</span>
                                    </a>
                                </div>
                            @else
                                Not specified
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Cameras Recommended</td>
                        <td class="text-slate-900 dark:text-white font-medium">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-800 dark:text-slate-200 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                {{ $siteSurvey->camera_count_recommended ?? '0' }} Units
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Estimated Cable Length</td>
                        <td class="text-slate-900 dark:text-white">{{ $siteSurvey->cable_length_estimate ? ($siteSurvey->cable_length_estimate . ' m') : 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">DVR / NVR Location</td>
                        <td class="text-slate-900 dark:text-white">{{ $siteSurvey->dvr_location ?? 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Power / UPS Availability</td>
                        <td class="text-slate-900 dark:text-white">{{ $siteSurvey->power_availability ?? 'Not specified' }}</td>
                    </tr>
                    @if($siteSurvey->challenges)
                        <tr>
                            <td class="text-slate-500 font-bold">Challenges</td>
                            <td class="text-slate-900 dark:text-white whitespace-pre-line">{{ $siteSurvey->challenges }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-slate-500 font-bold">Technician Notes</td>
                        <td class="text-slate-900 dark:text-white whitespace-pre-line">{{ $siteSurvey->visit_notes ?: 'No notes recorded' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Photos Section --}}
        <div class="crm-section">
            <div class="flex items-center justify-between mb-3">
                <h3 class="m-0">Site Photos ({{ $siteSurvey->photos->count() }})</h3>
                <button type="button" onclick="openUploadModal()" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Upload Photo</span>
                </button>
            </div>

            @if($siteSurvey->photos->count())
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-2">
                    @foreach($siteSurvey->photos as $photo)
                        <div class="border border-slate-300 dark:border-slate-700 rounded p-1.5 bg-white dark:bg-slate-800 flex flex-col justify-between">
                            <div class="relative overflow-hidden rounded bg-slate-100 dark:bg-slate-900 h-32 flex items-center justify-center">
                                <img src="{{ $photo->url() }}" alt="{{ $photo->original_name }}"
                                     class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition"
                                     loading="lazy"
                                     onclick="openLightbox('{{ $photo->url() }}')">
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-1 text-xs">
                                <span class="text-slate-600 dark:text-slate-400 truncate flex-1" title="{{ $photo->caption ?: $photo->original_name }}">
                                    {{ $photo->caption ?: $photo->original_name }}
                                </span>
                                <form method="POST" action="{{ route('site-surveys.photos.destroy', $photo) }}" data-confirm="Remove this photo?" class="inline m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-bold px-1.5 py-0.5 bg-transparent border-0 cursor-pointer text-sm" title="Delete photo">&times;</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500 my-2">No photos uploaded.</p>
            @endif
        </div>
    </div>

    {{-- Lightbox --}}
    <div id="lightbox" class="hidden fixed inset-0 bg-slate-950/80 z-50 flex items-center justify-center p-4">
        <span onclick="closeLightbox()" class="absolute top-4 right-6 text-white text-3xl cursor-pointer">&times;</span>
        <img id="lightbox-img" src="" alt="Site photo" class="max-w-[90vw] max-h-[85vh] rounded border border-white/20">
    </div>

    {{-- Upload Photo Modal --}}
    <div id="upload-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-700 shadow-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white m-0">Upload Site Photos</h3>
                <button type="button" onclick="closeUploadModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xl cursor-pointer">&times;</button>
            </div>

            <form method="POST" action="{{ route('site-surveys.photos.upload', $siteSurvey) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Select Photos or Capture with Camera *</label>
                    <input type="file" name="photos[]" multiple accept="image/*" required class="crm-input text-xs file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-[11px] text-slate-500 mt-1">Accepts multiple images (JPG, PNG). Stored securely across cloud and local servers.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Caption (Optional)</label>
                    <input aria-label="Caption (Optional)" type="text" name="captions[]" placeholder="e.g. Main Entrance Camera Position" class="crm-input text-xs">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3">
                    <button type="button" onclick="closeUploadModal()" class="btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn-primary text-xs inline-flex items-center gap-1.5 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Upload Now</span>
                    </button>
                </div>
            </form>
        </div>
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

        function openUploadModal() {
            document.getElementById('upload-modal').classList.remove('hidden');
        }
        function closeUploadModal() {
            document.getElementById('upload-modal').classList.add('hidden');
        }
        document.getElementById('upload-modal').addEventListener('click', function(e) {
            if (e.target === this) closeUploadModal();
        });
    </script>
</x-app-layout>
