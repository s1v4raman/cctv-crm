<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('technician.dashboard') }}" class="p-2 rounded-xl bg-slate-800/80 border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-700 shadow-sm transition">
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
                       class="px-3.5 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-amber-400 border border-slate-700 font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                        <span>📞 Call Contact</span>
                    </a>
                @endif
                @if($siteSurvey->site_address || $siteSurvey->lead->site_address)
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($siteSurvey->site_address ?: $siteSurvey->lead->site_address) }}" 
                       target="_blank" 
                       class="px-3.5 py-2 rounded-xl bg-blue-600/20 hover:bg-blue-600/30 border border-blue-500/30 text-blue-300 font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                        <span>📍 Open in Maps</span>
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
                        <span class="text-slate-500 block uppercase text-[10px] font-bold">Client / Site Name</span>
                        <span class="font-extrabold text-white text-sm font-heading">{{ $siteSurvey->lead->customer_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block uppercase text-[10px] font-bold">Contact Person & Phone</span>
                        <span class="font-bold text-slate-200">{{ $siteSurvey->contact_person ?: $siteSurvey->lead->customer_name }}</span>
                        <span class="text-amber-400 font-mono block">{{ $siteSurvey->contact_phone ?: $siteSurvey->lead->phone }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block uppercase text-[10px] font-bold">Installation Address</span>
                        <span class="font-medium text-slate-300">📍 {{ $siteSurvey->site_address ?: ($siteSurvey->lead->site_address ?: 'Address not provided') }}</span>
                    </div>
                </div>

                @if($siteSurvey->visit_notes && $siteSurvey->status !== 'completed')
                    <div class="p-3.5 rounded-xl bg-[#060913] border border-amber-500/30 text-xs">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-400 block mb-1">📋 Instructions & Customer Scope from Admin:</span>
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
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Cameras Recommended</span>
                            <div class="text-2xl font-black text-amber-400 font-mono mt-1">{{ $siteSurvey->camera_count_recommended }} Units</div>
                        </div>
                        <div class="p-4 bg-[#060913] rounded-xl border border-slate-800">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Estimated Cable Length</span>
                            <div class="text-2xl font-black text-sky-400 font-mono mt-1">{{ $siteSurvey->cable_length_estimate }} Mtr</div>
                        </div>
                        <div class="p-4 bg-[#060913] rounded-xl border border-slate-800">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">DVR / NVR Location</span>
                            <div class="text-sm font-bold text-slate-200 mt-1.5">{{ $siteSurvey->dvr_location ?: 'Main Server Rack' }}</div>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        @if($siteSurvey->power_availability)
                            <div>
                                <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px] block">Power & UPS Availability</span>
                                <p class="text-slate-200 bg-[#060913] p-3 rounded-xl border border-slate-800 mt-0.5">{{ $siteSurvey->power_availability }}</p>
                            </div>
                        @endif

                        @if($siteSurvey->challenges)
                            <div>
                                <span class="font-bold uppercase tracking-wider text-amber-400 text-[10px] block">Site Constraints / Challenges</span>
                                <p class="text-slate-200 bg-amber-950/20 p-3 rounded-xl border border-amber-500/30 mt-0.5">{{ $siteSurvey->challenges }}</p>
                            </div>
                        @endif

                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px] block">Technician Visit Notes & Recommendations</span>
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
                            <span class="text-lg">📐</span>
                            <h2 class="text-base font-extrabold text-white font-heading">Technical Findings & Recommendations</h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Recommended Cameras *</label>
                                <input type="number" name="camera_count_recommended" required min="1" placeholder="e.g. 4" value="{{ old('camera_count_recommended', $siteSurvey->camera_count_recommended ?: 4) }}"
                                    class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 py-2.5">
                                @error('camera_count_recommended') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Estimated Cable Length (Mtr) *</label>
                                <input type="number" name="cable_length_estimate" required min="1" step="0.5" placeholder="e.g. 90" value="{{ old('cable_length_estimate', $siteSurvey->cable_length_estimate ?: 50) }}"
                                    class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 py-2.5">
                                @error('cable_length_estimate') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">DVR / NVR Placement *</label>
                                <input type="text" name="dvr_location" required placeholder="e.g. 1st Floor Server Room" value="{{ old('dvr_location', $siteSurvey->dvr_location ?: 'Server Room / Office') }}"
                                    class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 py-2.5">
                                @error('dvr_location') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Power Sockets & UPS Availability</label>
                                <input type="text" name="power_availability" placeholder="e.g. 2x 230V Sockets available, connected to 1KVA UPS" value="{{ old('power_availability', $siteSurvey->power_availability) }}"
                                    class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 py-2.5">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Site Obstacles & Challenges</label>
                                <input type="text" name="challenges" placeholder="e.g. High false ceiling, masonry drilling required for outdoor entry" value="{{ old('challenges', $siteSurvey->challenges) }}"
                                    class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 py-2.5">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Detailed On-Site Observations & Recommendations *</label>
                            <textarea name="visit_notes" required rows="3" placeholder="Describe camera coverage angles, conduit routing, and storage requirements..."
                                class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20">{{ old('visit_notes', $siteSurvey->visit_notes) }}</textarea>
                            @error('visit_notes') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- On-Site Photo Upload Card --}}
                    <div class="bg-[#0f172a] rounded-2xl border border-slate-800 shadow-2xl p-6 space-y-4" x-data="photoUploader()" style="background-color: #0f172a;">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">📸</span>
                                <div>
                                    <h2 class="text-base font-extrabold text-white font-heading">Upload Site Photos & Camera Angles</h2>
                                    <p class="text-xs text-slate-400">Capture premises, entrance blind spots, server rack, and cable pathways</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-2 border-dashed border-slate-700 hover:border-amber-500/50 rounded-2xl p-6 text-center bg-[#060913] transition cursor-pointer"
                             @click="$refs.photoInput.click()">
                            <input type="file" x-ref="photoInput" name="photos[]" multiple accept="image/*" class="hidden" @change="handleFiles($event)">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-600 text-slate-950 flex items-center justify-center mx-auto mb-2 text-xl font-black shadow-lg shadow-amber-500/20">
                                📷
                            </div>
                            <p class="text-xs font-bold text-white">Click or Tap to Take/Upload Site Photos</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Supports PNG, JPG, JPEG up to 5MB each (Multiple photos allowed)</p>
                        </div>

                        {{-- Preview Grid --}}
                        <template x-if="previews.length > 0">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                                <template x-for="(item, index) in previews" :key="index">
                                    <div class="rounded-xl overflow-hidden border border-slate-700 shadow-sm bg-[#060913] relative">
                                        <img :src="item.url" class="w-full h-28 object-cover">
                                        <input type="text" :name="'captions[' + index + ']'" placeholder="Photo caption..." class="w-full text-[10px] p-1.5 bg-[#0b1120] text-white border-t border-slate-700 focus:outline-none">
                                        <button type="button" @click="removePhoto(index)" class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-rose-600 text-white text-xs font-bold flex items-center justify-center shadow">✕</button>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('technician.dashboard') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-400 font-bold text-xs hover:text-white hover:bg-slate-800 transition">
                            Cancel
                        </a>
                        <button type="submit" class="btn-amber">
                            <span>✓ Complete Site Survey & Upload Photos</span>
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
                handleFiles(event) {
                    const files = event.target.files;
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.previews.push({
                                url: e.target.result,
                                name: file.name
                            });
                        };
                        reader.readAsDataURL(file);
                    }
                },
                removePhoto(index) {
                    this.previews.splice(index, 1);
                }
            };
        }
    </script>
</x-app-layout>
