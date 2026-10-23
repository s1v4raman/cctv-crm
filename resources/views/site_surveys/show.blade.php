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
                    <a href="{{ route('estimator.index', ['survey_id' => $siteSurvey->id, 'lead_id' => $siteSurvey->lead_id]) }}" class="btn-secondary">
                        Estimator & BOM
                    </a>
                    <a href="{{ route('site-surveys.edit', $siteSurvey) }}" class="btn-secondary">
                        Edit Details
                    </a>
                    <a href="{{ route('quotations.create', $siteSurvey->lead) }}" class="btn-primary">
                        Create Quotation
                    </a>
                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('site-surveys.destroy', $siteSurvey) }}" onsubmit="return confirm('Delete this survey and all photos?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger-link p-0 bg-transparent border-0 cursor-pointer text-xs ml-2">Delete</button>
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
                        <td class="text-slate-900 dark:text-white">{{ $siteSurvey->site_address ?: ($siteSurvey->lead->site_address ?? 'Not specified') }}</td>
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
                                <a href="tel:{{ $phone }}" class="action-link">{{ $phone }}</a>
                            @else
                                Not specified
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Cameras Recommended</td>
                        <td class="text-slate-900 dark:text-white">{{ $siteSurvey->camera_count_recommended ?? '0' }}</td>
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
                <button type="button" onclick="openUploadModal()" class="btn-secondary text-xs">
                    + Upload Photo
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
                                <form method="POST" action="{{ route('site-surveys.photos.destroy', $photo) }}" onsubmit="return confirm('Remove this photo?');" class="inline m-0">
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
                    <input type="text" name="captions[]" placeholder="e.g. Main Entrance Camera Position" class="crm-input text-xs">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" onclick="closeUploadModal()" class="btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn-primary text-xs">Upload Now</button>
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
