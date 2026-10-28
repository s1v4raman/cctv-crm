<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a aria-label="Back" href="{{ route('technician.dashboard') }}" class="p-2 rounded-xl bg-slate-800/80 border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-700 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider
                            {{ $siteSurvey->status === 'completed' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                            {{ $siteSurvey->status === 'completed' ? '✓ Completed' : '⏳ Pending On-Site Visit' }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono">Survey #{{ $siteSurvey->id }}</span>
                    </div>
                    <h1 class="text-xl font-black text-white font-heading tracking-tight mt-0.5">
                        Site Survey: {{ $siteSurvey->lead->customer_name }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($siteSurvey->contact_phone || $siteSurvey->lead->phone)
                    <a href="tel:{{ $siteSurvey->contact_phone ?: $siteSurvey->lead->phone }}" 
                       class="px-3.5 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs transition inline-flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>Call Contact</span>
                    </a>
                @endif
                @if($siteSurvey->site_address || $siteSurvey->lead->site_address)
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($siteSurvey->site_address ?: $siteSurvey->lead->site_address) }}" 
                       target="_blank" 
                       class="px-3.5 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 border border-slate-700 text-slate-200 font-bold text-xs transition inline-flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Open in Maps</span>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-[#060913] min-h-screen text-slate-200" style="background-color: #060913;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Customer & Site Overview Card --}}
            <div class="bg-[#0f172a] rounded-2xl border border-slate-800 shadow-2xl p-6 space-y-4" style="background-color: #0f172a;">
                <h3 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider mb-3">Premises & Contact Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 block uppercase text-[11px] font-bold">Client / Site Name</span>
                        <span class="font-extrabold text-white text-sm font-heading">{{ $siteSurvey->lead->customer_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block uppercase text-[11px] font-bold">Contact Person & Phone</span>
                        <span class="font-bold text-slate-200">{{ $siteSurvey->contact_person ?: $siteSurvey->lead->customer_name }}</span>
                        <span class="text-amber-400 font-mono block">{{ $siteSurvey->contact_phone ?: $siteSurvey->lead->phone }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block uppercase text-[11px] font-bold">Installation Address</span>
                        <span class="font-medium text-slate-300">{{ $siteSurvey->site_address ?: ($siteSurvey->lead->site_address ?: 'Address not provided') }}</span>
                    </div>
                </div>

                @if($siteSurvey->visit_notes && $siteSurvey->status !== 'completed')
                    <div class="p-3.5 rounded-xl bg-[#060913] border border-amber-500/30 text-xs">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-400 block mb-1">Instructions & Customer Scope from Admin:</span>
                        <p class="text-slate-200 font-medium whitespace-pre-line">{{ $siteSurvey->visit_notes }}</p>
                    </div>
                @endif
            </div>

            @if($siteSurvey->status === 'completed')
                {{-- Completed Inspection Report Card --}}
                <div class="bg-[#0f172a] rounded-2xl border border-emerald-500/40 shadow-2xl p-6 space-y-6" style="background-color: #0f172a;">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-slate-800">
                        <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-sm">✓</div>
                        <div>
                            <h2 class="text-base font-extrabold text-white font-heading">Site Survey Completed</h2>
                            <p class="text-xs text-slate-400">Inspection conducted on {{ $siteSurvey->survey_date->format('l, d M Y') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 bg-[#060913] rounded-xl border border-slate-800">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Cameras Recommended</span>
                            <div class="text-2xl font-black text-amber-400 font-mono mt-1">{{ $siteSurvey->camera_count_recommended }} Units</div>
                        </div>
                        <div class="p-4 bg-[#060913] rounded-xl border border-slate-800">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Estimated Cable Length</span>
                            <div class="text-2xl font-black text-sky-400 font-mono mt-1">{{ $siteSurvey->cable_length_estimate }} Mtr</div>
                        </div>
                        <div class="p-4 bg-[#060913] rounded-xl border border-slate-800">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">DVR / NVR Location</span>
                            <div class="text-sm font-bold text-slate-200 mt-1.5">{{ $siteSurvey->dvr_location ?: 'Main Server Rack' }}</div>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        @if($siteSurvey->power_availability)
                            <div>
                                <span class="font-bold uppercase tracking-wider text-slate-400 text-[11px] block">Power & UPS Availability</span>
                                <p class="text-slate-200 bg-[#060913] p-3 rounded-xl border border-slate-800 mt-0.5">{{ $siteSurvey->power_availability }}</p>
                            </div>
                        @endif

                        @if($siteSurvey->challenges)
                            <div>
                                <span class="font-bold uppercase tracking-wider text-amber-400 text-[11px] block">Site Constraints / Challenges</span>
                                <p class="text-slate-200 bg-amber-950/20 p-3 rounded-xl border border-amber-500/30 mt-0.5">{{ $siteSurvey->challenges }}</p>
                            </div>
                        @endif

                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-400 text-[11px] block">Technician Visit Notes & Recommendations</span>
                            <p class="text-slate-200 bg-[#060913] p-3.5 rounded-xl border border-slate-800 mt-0.5 whitespace-pre-line leading-relaxed">{{ $siteSurvey->visit_notes }}</p>
                        </div>
                    </div>

                    {{-- Uploaded Photos Gallery --}}
                    @if($siteSurvey->photos->count() > 0)
                        <div class="pt-4 border-t border-slate-800">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Uploaded Site Inspection Photos ({{ $siteSurvey->photos->count() }})</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach($siteSurvey->photos as $photo)
                                    <div class="rounded-xl overflow-hidden border border-slate-700 shadow-md bg-[#060913]">
                                        <img src="{{ asset('storage/site-surveys/' . $photo->filename) }}" alt="Site Photo" class="w-full h-32 object-cover">
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

                    <div class="bg-[#0f172a] rounded-2xl border border-slate-800 shadow-2xl p-6 space-y-4" style="background-color: #0f172a;">
                        <div class="flex items-center gap-2 pb-3 border-b border-slate-800">
                            
                            <h2 class="text-base font-extrabold text-white font-heading">Technical Findings & Recommendations</h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-200 mb-1.5">Recommended Cameras *</label>
                                <input aria-label="Recommended Cameras" type="number" name="camera_count_recommended" required min="1" placeholder="e.g. 4" value="{{ old('camera_count_recommended', $siteSurvey->camera_count_recommended ?: 4) }}"
                                    class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 py-2.5">
                                @error('camera_count_recommended') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-200 mb-1.5">Estimated Cable Length (Mtr) *</label>
                                <input aria-label="Estimated Cable Length (Mtr)" type="number" name="cable_length_estimate" required min="1" step="0.5" placeholder="e.g. 90" value="{{ old('cable_length_estimate', $siteSurvey->cable_length_estimate ?: 50) }}"
                                    class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 py-2.5">
                                @error('cable_length_estimate') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-200 mb-1.5">DVR / NVR Placement *</label>
                                <input aria-label="DVR / NVR Placement" type="text" name="dvr_location" required placeholder="e.g. 1st Floor Server Room" value="{{ old('dvr_location', $siteSurvey->dvr_location ?: 'Server Room / Office') }}"
                                    class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 py-2.5">
                                @error('dvr_location') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-200 mb-1.5">Power Sockets & UPS Availability</label>
                                <input aria-label="Power Sockets & UPS Availability" type="text" name="power_availability" placeholder="e.g. 2x 230V Sockets available, connected to 1KVA UPS" value="{{ old('power_availability', $siteSurvey->power_availability) }}"
                                    class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 py-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-200 mb-1.5">Site Obstacles & Challenges</label>
                                <input aria-label="Site Obstacles & Challenges" type="text" name="challenges" placeholder="e.g. High false ceiling, masonry drilling required for outdoor entry" value="{{ old('challenges', $siteSurvey->challenges) }}"
                                    class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 py-2.5">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">Detailed On-Site Observations & Recommendations *</label>
                            <textarea aria-label="Detailed On-Site Observations & Recommendations" name="visit_notes" required rows="3" placeholder="Describe camera coverage angles, conduit routing, and storage requirements..."
                                class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20">{{ old('visit_notes', $siteSurvey->visit_notes) }}</textarea>
                            @error('visit_notes') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- On-Site Photo Upload Card --}}
                    <div class="bg-[#0f172a] rounded-2xl border border-slate-800 shadow-2xl p-6 space-y-4" x-data="photoUploader()" style="background-color: #0f172a;">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <div class="flex items-center gap-2">
                                <div>
                                    <h2 class="text-base font-extrabold text-white font-heading">Upload Site Photos & Camera Angles</h2>
                                    <p class="text-xs text-slate-400">Capture premises, entrance blind spots, server rack, and cable pathways</p>
                                </div>
                            </div>
                        </div>

                        {{-- Final form input that holds all accumulated photos (camera + gallery) --}}
                        <input type="file" x-ref="finalInput" name="photos[]" multiple accept="image/*" class="hidden">

                        {{-- Hidden inputs for camera capture & gallery selection --}}
                        <input type="file" x-ref="cameraInput" accept="image/*" capture="environment" class="hidden" @change="handleFiles($event)">
                        <input type="file" x-ref="galleryInput" multiple accept="image/*" class="hidden" @change="handleFiles($event)">

                        {{-- Interactive Upload & Camera Zone --}}
                        <div class="border-2 border-dashed border-slate-700 hover:border-blue-500/50 rounded-2xl p-6 text-center bg-[#060913] transition">
                            <div class="w-12 h-12 rounded-xl bg-blue-600/20 text-blue-400 flex items-center justify-center mx-auto mb-3 border border-blue-500/30">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            
                            <p class="text-xs font-bold text-white mb-1">Take Photos or Choose from Gallery</p>
                            <p class="text-[11px] text-slate-400 mb-4">Supports PNG, JPG, JPEG up to 5MB each (Multiple photos allowed)</p>

                            {{-- Dual Action Buttons: Take Photo & Select Images --}}
                            <div class="flex flex-wrap items-center justify-center gap-3">
                                <button type="button" @click="openCamera()"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition transform active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Take Photo (Camera)</span>
                                </button>

                                <button type="button" @click="$refs.galleryInput.click()"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-bold text-xs shadow-sm transition transform active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Select from Gallery / Files</span>
                                </button>
                            </div>
                        </div>

                        {{-- Preview Grid --}}
                        <template x-if="previews.length > 0">
                            <div class="space-y-2 pt-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-300">Ready to Upload (<span x-text="previews.length"></span> photos)</p>
                                    <button type="button" @click="clearAll()" class="text-[11px] text-rose-400 hover:underline">Clear all</button>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <template x-for="(item, index) in previews" :key="index">
                                        <div class="rounded-xl overflow-hidden border border-slate-700 shadow-sm bg-[#060913] relative">
                                            <img :src="item.url" class="w-full h-28 object-cover">
                                            <input aria-label="Photo caption" type="text" :name="'captions[' + index + ']'" placeholder="Photo caption..." class="w-full text-[11px] p-1.5 bg-[#0b1120] text-white border-t border-slate-700 focus:outline-none">
                                            <button type="button" @click="removePhoto(index)" class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-rose-600 text-white text-xs font-bold flex items-center justify-center shadow">✕</button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- In-Browser Live Webcam / Camera Modal (Desktop & Fallback) --}}
                        <div x-show="cameraModalOpen" x-cloak
                             class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
                            <div class="bg-[#0f172a] border border-slate-700 rounded-2xl w-full max-w-lg p-5 space-y-4 shadow-2xl relative"
                                 @click.away="closeCamera()">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Live Camera Viewfinder
                                    </h3>
                                    <button type="button" @click="closeCamera()" class="text-slate-400 hover:text-white text-sm font-bold p-1">✕</button>
                                </div>

                                <div class="relative bg-black rounded-xl overflow-hidden aspect-video flex items-center justify-center border border-slate-800">
                                    <video x-ref="videoElement" autoplay playsinline class="w-full h-full object-cover"></video>
                                    <canvas x-ref="canvasElement" class="hidden"></canvas>
                                </div>

                                <div class="flex items-center justify-between pt-2">
                                    <button type="button" @click="closeCamera()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800">
                                        Close
                                    </button>
                                    <button type="button" @click="snapFromWebcam()" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-lg inline-flex items-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Capture & Add Photo</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('technician.dashboard') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-xs hover:text-white hover:bg-slate-800 transition">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Cancel</span>
                        </a>
                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition">
                            <svg class="w-4 h-4 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Complete Site Survey & Upload Photos</span>
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
</x-app-layout>
