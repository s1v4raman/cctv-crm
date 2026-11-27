<x-app-layout>
    <x-slot name="header">
        <div class="tech-top-header sm:h-auto sm:border-none sm:p-0 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div class="flex items-center gap-3">
                <a aria-label="{{ __('Back') }}" href="{{ route('technician.dashboard') }}" class="tech-icon-btn rounded-xl bg-slate-800/80 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 shadow-sm transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="tech-badge {{ $siteSurvey->status === 'completed' ? 'tech-badge-completed' : 'tech-badge-assigned' }}">
                            {{ $siteSurvey->status === 'completed' ? __('Completed') : __('Pending Visit') }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono">{{ __('Survey #:id', ['id' => $siteSurvey->id]) }}</span>
                    </div>
                    <h1 class="text-lg sm:text-2xl font-bold text-white tracking-tight mt-0.5 truncate">
                        {{ __('Site Survey: :customer', ['customer' => $siteSurvey->lead->customer_name]) }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <x-language-switch />
                @if($siteSurvey->contact_phone || $siteSurvey->lead->phone)
                    <a href="tel:{{ $siteSurvey->contact_phone ?: $siteSurvey->lead->phone }}" 
                       class="tech-btn-secondary !h-10 sm:!h-10 text-xs px-3"
                       style="height: 40px !important; min-height: 40px !important;">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>{{ __('Call') }}</span>
                    </a>
                @endif
                @if($siteSurvey->site_address || $siteSurvey->lead->site_address)
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($siteSurvey->site_address ?: $siteSurvey->lead->site_address) }}" 
                       target="_blank" 
                       class="tech-btn-secondary !h-10 sm:!h-10 text-xs px-3"
                       style="height: 40px !important; min-height: 40px !important;">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ __('Navigate') }}</span>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="tech-mobile-scope py-4 sm:py-6 bg-[#F8FAFC] dark:bg-slate-950 min-h-screen text-slate-800 dark:text-slate-200">
        <div class="tech-mobile-container max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Customer & Site Overview Card (16px radius, key-value display) --}}
            <div class="tech-card crm-card bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-[#1E3A8A] dark:text-blue-400 uppercase tracking-wider">{{ __('Premises & Contact Details') }}</h3>
                </div>
                <div class="space-y-1 text-xs">
                    <div class="tech-kv-row">
                        <span class="tech-kv-label">{{ __('Client / Site') }}</span>
                        <span class="tech-kv-value font-bold text-slate-900 dark:text-white">{{ $siteSurvey->lead->customer_name }}</span>
                    </div>
                    <div class="tech-kv-row">
                        <span class="tech-kv-label">{{ __('Contact & Phone') }}</span>
                        <span class="tech-kv-value">
                            {{ $siteSurvey->contact_person ?: $siteSurvey->lead->customer_name }}
                            @if($siteSurvey->contact_phone || $siteSurvey->lead->phone)
                                &bull; <a href="tel:{{ $siteSurvey->contact_phone ?: $siteSurvey->lead->phone }}" class="text-[#1E3A8A] dark:text-blue-400 font-mono">{{ $siteSurvey->contact_phone ?: $siteSurvey->lead->phone }}</a>
                            @endif
                        </span>
                    </div>
                    <div class="tech-kv-row" style="border-bottom: none !important;">
                        <span class="tech-kv-label">{{ __('Installation Site') }}</span>
                        <span class="tech-kv-value text-slate-700 dark:text-slate-300">{{ $siteSurvey->site_address ?: ($siteSurvey->lead->site_address ?: __('Address not provided')) }}</span>
                    </div>
                </div>

                @if($siteSurvey->visit_notes && $siteSurvey->status !== 'completed')
                    <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-xs">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 block mb-1">{{ __('Instructions from Office:') }}</span>
                        <p class="text-slate-800 dark:text-slate-200 font-medium whitespace-pre-line">{{ $siteSurvey->visit_notes }}</p>
                    </div>
                @endif
            </div>

            @if($siteSurvey->status === 'completed')
                {{-- Completed Inspection Report Card --}}
                <div class="bg-[#0f172a] rounded-2xl border border-emerald-500/40 shadow-2xl p-6 space-y-6" style="background-color: #0f172a;">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-slate-800">
                        <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-sm">✓</div>
                        <div>
                            <h2 class="text-base font-extrabold text-white font-heading">{{ __('Site Survey Completed') }}</h2>
                            <p class="text-xs text-slate-400">{{ __('Inspection conducted on :date', ['date' => $siteSurvey->survey_date->translatedFormat('l, d M Y')]) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 bg-[#060913] rounded-xl border border-slate-800">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Cameras Recommended') }}</span>
                            <div class="text-2xl font-black text-amber-400 font-mono mt-1">{{ $siteSurvey->camera_count_recommended }} {{ __('Units') }}</div>
                        </div>
                        <div class="p-4 bg-[#060913] rounded-xl border border-slate-800">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Estimated Cable Length') }}</span>
                            <div class="text-2xl font-black text-sky-400 font-mono mt-1">{{ $siteSurvey->cable_length_estimate }} {{ __('Mtr') }}</div>
                        </div>
                        <div class="p-4 bg-[#060913] rounded-xl border border-slate-800">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('DVR / NVR Location') }}</span>
                            <div class="text-sm font-bold text-slate-200 mt-1.5">{{ $siteSurvey->dvr_location ?: __('Main Server Rack') }}</div>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        @if($siteSurvey->power_availability)
                            <div>
                                <span class="font-bold uppercase tracking-wider text-slate-400 text-[11px] block">{{ __('Power & UPS Availability') }}</span>
                                <p class="text-slate-200 bg-[#060913] p-3 rounded-xl border border-slate-800 mt-0.5">{{ $siteSurvey->power_availability }}</p>
                            </div>
                        @endif

                        @if($siteSurvey->challenges)
                            <div>
                                <span class="font-bold uppercase tracking-wider text-amber-400 text-[11px] block">{{ __('Site Constraints / Challenges') }}</span>
                                <p class="text-slate-200 bg-amber-950/20 p-3 rounded-xl border border-amber-500/30 mt-0.5">{{ $siteSurvey->challenges }}</p>
                            </div>
                        @endif

                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-400 text-[11px] block">{{ __('Technician Visit Notes & Recommendations') }}</span>
                            <p class="text-slate-200 bg-[#060913] p-3.5 rounded-xl border border-slate-800 mt-0.5 whitespace-pre-line leading-relaxed">{{ $siteSurvey->visit_notes }}</p>
                        </div>
                    </div>

                    {{-- Uploaded Photos Gallery --}}
                    @if($siteSurvey->photos->count() > 0)
                        <div class="pt-4 border-t border-slate-800">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">{{ __('Uploaded Site Inspection Photos (:count)', ['count' => $siteSurvey->photos->count()]) }}</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach($siteSurvey->photos as $photo)
                                    <div class="rounded-xl overflow-hidden border border-slate-700 shadow-md bg-[#060913]">
                                        <img src="{{ asset('storage/site-surveys/' . $photo->filename) }}" alt="{{ __('Site Photo') }}" class="w-full h-32 object-cover">
                                        @if($photo->caption)
                                            <div class="p-2 text-[11px] font-medium text-slate-300 truncate bg-[#0b1120] border-t border-slate-800">
                                                {{ $photo->caption }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                {{-- Technician Survey Execution & Photo Upload Form --}}
                <form method="POST" action="{{ route('technician.surveys.complete', $siteSurvey) }}" enctype="multipart/form-data" class="space-y-6" id="tech-survey-form">
                    @csrf

                    <div class="tech-card crm-card bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-4">
                        <div class="flex items-center gap-2 pb-3 border-b border-slate-200 dark:border-slate-800">
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Technical Findings & Recommendations') }}</h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="survey_cameras" class="tech-label">{{ __('Recommended Cameras *') }}</label>
                                    <x-voice-mic-button target="#survey_cameras" />
                                </div>
                                <input id="survey_cameras" aria-label="{{ __('Recommended Cameras') }}" type="number" name="camera_count_recommended" required min="1" placeholder="{{ __('e.g. 4') }}" value="{{ old('camera_count_recommended', $siteSurvey->camera_count_recommended ?: 4) }}"
                                    class="tech-input text-sm">
                                @error('camera_count_recommended') <span class="tech-error-text">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="survey_cable" class="tech-label">{{ __('Estimated Cable Length (Mtr) *') }}</label>
                                    <x-voice-mic-button target="#survey_cable" />
                                </div>
                                <input id="survey_cable" aria-label="{{ __('Estimated Cable Length (Mtr)') }}" type="number" name="cable_length_estimate" required min="1" step="0.5" placeholder="{{ __('e.g. 90') }}" value="{{ old('cable_length_estimate', $siteSurvey->cable_length_estimate ?: 50) }}"
                                    class="tech-input text-sm">
                                @error('cable_length_estimate') <span class="tech-error-text">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="survey_dvr" class="tech-label">{{ __('DVR / NVR Placement *') }}</label>
                                    <x-voice-mic-button target="#survey_dvr" />
                                </div>
                                <input id="survey_dvr" aria-label="{{ __('DVR / NVR Placement') }}" type="text" name="dvr_location" required placeholder="{{ __('e.g. 1st Floor Server Room') }}" value="{{ old('dvr_location', $siteSurvey->dvr_location ?: 'Server Room / Office') }}"
                                    class="tech-input text-sm">
                                @error('dvr_location') <span class="tech-error-text">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="survey_power" class="tech-label">{{ __('Power Sockets & UPS Availability') }}</label>
                                    <x-voice-mic-button target="#survey_power" />
                                </div>
                                <input id="survey_power" aria-label="{{ __('Power Sockets & UPS Availability') }}" type="text" name="power_availability" placeholder="{{ __('e.g. 2x 230V Sockets available, connected to 1KVA UPS') }}" value="{{ old('power_availability', $siteSurvey->power_availability) }}"
                                    class="tech-input text-sm">
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="survey_challenges" class="tech-label">{{ __('Site Obstacles & Challenges') }}</label>
                                    <x-voice-mic-button target="#survey_challenges" />
                                </div>
                                <input id="survey_challenges" aria-label="{{ __('Site Obstacles & Challenges') }}" type="text" name="challenges" placeholder="{{ __('e.g. High false ceiling, masonry drilling required') }}" value="{{ old('challenges', $siteSurvey->challenges) }}"
                                    class="tech-input text-sm">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="survey_notes" class="tech-label">{{ __('Detailed Observations & Recommendations *') }}</label>
                                <x-voice-mic-button target="#survey_notes" />
                            </div>
                            <textarea id="survey_notes" aria-label="{{ __('Detailed Observations & Recommendations') }}" name="visit_notes" required rows="3" placeholder="{{ __('Describe camera coverage angles, conduit routing, and storage requirements...') }}"
                                class="tech-textarea text-sm">{{ old('visit_notes', $siteSurvey->visit_notes) }}</textarea>
                            @error('visit_notes') <span class="tech-error-text">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- On-Site Photo Upload Card --}}
                    <div class="tech-card crm-card bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-4" x-data="photoUploader()">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Site Photos & Evidence') }}</h2>
                                <p class="text-xs text-[#64748B]">{{ __('Capture premises, entrance blind spots, server rack, and cable pathways') }}</p>
                            </div>
                        </div>

                        {{-- Final form input that holds all accumulated photos (camera + gallery) --}}
                        <input type="file" x-ref="finalInput" name="photos[]" multiple accept="image/*" class="hidden">

                        {{-- Hidden inputs for camera capture & gallery selection --}}
                        <input type="file" x-ref="cameraInput" accept="image/*" capture="environment" class="hidden" @change="handleFiles($event)">
                        <input type="file" x-ref="galleryInput" multiple accept="image/*" class="hidden" @change="handleFiles($event)">

                        {{-- Interactive Upload & Camera Zone --}}
                        <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-[#1E3A8A] rounded-2xl p-6 text-center bg-[#F8FAFC] dark:bg-slate-900 transition">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-[#1E3A8A] dark:text-blue-400 flex items-center justify-center mx-auto mb-3 border border-blue-200 dark:border-blue-800">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1">{{ __('Take Photos or Choose from Gallery') }}</p>
                            <p class="text-xs text-[#64748B] mb-4">{{ __('Supports PNG, JPG, JPEG up to 5MB each (Multiple photos allowed)') }}</p>

                            {{-- Dual Action Buttons: Take Photo & Select Images --}}
                            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                                <button type="button" @click="openCamera()"
                                        class="tech-btn-primary sm:w-auto px-5"
                                        style="background-color: #1E3A8A !important; color: #ffffff !important;">
                                    <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="#ffffff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>{{ __('Capture Photo') }}</span>
                                </button>

                                <button type="button" @click="$refs.galleryInput.click()"
                                        class="tech-btn-secondary sm:w-auto px-5">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ __('Choose from Gallery') }}</span>
                                </button>
                            </div>
                        </div>

                        {{-- Preview Grid: 2-column photo grid on mobile with 12px gutter --}}
                        <template x-if="previews.length > 0">
                            <div class="space-y-2 pt-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Ready to Upload') }} (<span x-text="previews.length"></span> {{ __('photos') }})</p>
                                    <button type="button" @click="clearAll()" class="text-xs text-[#DC2626] font-medium hover:underline">{{ __('Clear all') }}</button>
                                </div>
                                <div class="tech-photo-grid">
                                    <template x-for="(item, index) in previews" :key="index">
                                        <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm bg-white dark:bg-slate-900 relative">
                                            <img :src="item.url" class="tech-photo-thumb">
                                            <input aria-label="{{ __('Photo caption...') }}" type="text" :name="'captions[' + index + ']'" placeholder="{{ __('Photo caption...') }}" class="w-full text-xs p-2 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white border-t border-slate-200 dark:border-slate-700 focus:outline-none">
                                            <x-close-button size="sm" tone="dark" label="{{ __('Remove photo') }}" @click="removePhoto(index)" class="absolute top-1.5 right-1.5" />
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- In-Browser Live Webcam / Camera Modal (Desktop & Fallback) --}}
                        <div x-show="cameraModalOpen" x-cloak
                             class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
                            <div class="bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 rounded-2xl w-full max-w-lg p-5 space-y-4 shadow-2xl relative"
                                 @click.away="closeCamera()">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        {{ __('Live Camera Viewfinder') }}
                                    </h3>
                                    <x-close-button tone="dark" label="{{ __('Close camera') }}" @click="closeCamera()" />
                                </div>

                                <div class="relative bg-black rounded-xl overflow-hidden aspect-video flex items-center justify-center border border-slate-800">
                                    <video x-ref="videoElement" autoplay playsinline class="w-full h-full object-cover"></video>
                                    <canvas x-ref="canvasElement" class="hidden"></canvas>
                                </div>

                                <div class="flex items-center justify-between pt-2 gap-2">
                                    <button type="button" @click="closeCamera()" class="tech-btn-secondary text-xs sm:w-auto px-4">
                                        {{ __('Close') }}
                                    </button>
                                    <button type="button" @click="snapFromWebcam()" class="tech-btn-primary text-xs sm:w-auto px-5"
                                            style="background-color: #1E3A8A !important; color: #ffffff !important;">
                                        <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="#ffffff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>{{ __('Capture & Add Photo') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Primary Action: 52px height, full-width on mobile --}}
                    <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4">
                        <a href="{{ route('technician.dashboard') }}" class="tech-btn-secondary w-full sm:w-auto">
                            <span>{{ __('Cancel') }}</span>
                        </a>
                        <button type="submit" class="tech-btn-primary w-full sm:w-auto"
                                style="background-color: #1E3A8A !important; color: #ffffff !important;">
                            <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="#ffffff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ __('Submit and Complete Survey') }}</span>
                        </button>
                    </div>
                </form>
            @endif

        </div>
    </div>

    <script>
        function photoUploader() {
            return {
                previews: [],
                dataTransfer: new DataTransfer(),
                cameraModalOpen: false,
                stream: null,

                init() {
                    this.syncInput();
                },

                syncInput() {
                    if (this.$refs.finalInput) {
                        this.$refs.finalInput.files = this.dataTransfer.files;
                    }
                },

                openCamera() {
                    const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                    if (isMobile && this.$refs.cameraInput) {
                        this.$refs.cameraInput.click();
                        return;
                    }

                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({ 
                            video: { facingMode: { ideal: 'environment' } } 
                        }).then(s => {
                            this.stream = s;
                            this.cameraModalOpen = true;
                            this.$nextTick(() => {
                                if (this.$refs.videoElement) {
                                    this.$refs.videoElement.srcObject = s;
                                }
                            });
                        }).catch(err => {
                            if (this.$refs.cameraInput) {
                                this.$refs.cameraInput.click();
                            }
                        });
                    } else if (this.$refs.cameraInput) {
                        this.$refs.cameraInput.click();
                    }
                },

                closeCamera() {
                    if (this.stream) {
                        this.stream.getTracks().forEach(t => t.stop());
                        this.stream = null;
                    }
                    this.cameraModalOpen = false;
                },

                snapFromWebcam() {
                    const video = this.$refs.videoElement;
                    const canvas = this.$refs.canvasElement;
                    if (!video || !canvas) return;

                    canvas.width = video.videoWidth || 1280;
                    canvas.height = video.videoHeight || 720;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    canvas.toBlob((blob) => {
                        if (!blob) return;
                        const file = new File([blob], 'site_photo_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                        this.addFile(file);
                        this.closeCamera();
                    }, 'image/jpeg', 0.92);
                },

                handleFiles(event) {
                    const files = event.target.files;
                    if (!files || files.length === 0) return;
                    for (let i = 0; i < files.length; i++) {
                        this.addFile(files[i]);
                    }
                    event.target.value = '';
                },

                addFile(file) {
                    this.dataTransfer.items.add(file);
                    this.syncInput();

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.previews.push({
                            url: e.target.result,
                            name: file.name
                        });
                    };
                    reader.readAsDataURL(file);
                },

                removePhoto(index) {
                    this.previews.splice(index, 1);
                    const newDt = new DataTransfer();
                    const allFiles = this.dataTransfer.files;
                    for (let i = 0; i < allFiles.length; i++) {
                        if (i !== index) {
                            newDt.items.add(allFiles[i]);
                        }
                    }
                    this.dataTransfer = newDt;
                    this.syncInput();
                },

                clearAll() {
                    this.previews = [];
                    this.dataTransfer = new DataTransfer();
                    this.syncInput();
                }
            };
        }
    </script>

    {{-- Universal Voice Dictation & Speech Recognition Engine provided globally via app layout --}}
</x-app-layout>
