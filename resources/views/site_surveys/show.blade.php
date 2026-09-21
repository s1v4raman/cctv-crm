<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('site-surveys.index') }}" class="p-2 rounded-xl bg-[#0F172A] border border-white/10 text-slate-400 hover:text-white hover:border-amber-400/40 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl sm:text-2xl font-black text-white font-heading tracking-tight">
                            Site Survey Audit #{{ $siteSurvey->id }}
                        </h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                            @if($siteSurvey->status === 'completed') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                            @elseif($siteSurvey->status === 'cancelled') bg-rose-500/20 text-rose-400 border border-rose-500/30
                            @else bg-amber-500/20 text-amber-400 border border-amber-500/30 @endif">
                            ● {{ ucfirst($siteSurvey->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Client: <strong class="text-white">{{ $siteSurvey->lead->customer_name }}</strong> &middot; Scheduled: {{ $siteSurvey->survey_date->format('d M Y') }} &middot; Engineer: {{ $siteSurvey->surveyedBy->name ?? '—' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('site-surveys.edit', $siteSurvey) }}" class="px-3.5 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                    <span>✏️ Edit Schedule</span>
                </a>
                <a href="{{ route('estimator.index', ['survey_id' => $siteSurvey->id, 'lead_id' => $siteSurvey->lead_id]) }}" 
                   class="px-3.5 py-2 rounded-xl bg-purple-500/15 hover:bg-purple-500/25 text-purple-400 border border-purple-500/30 text-xs font-bold transition flex items-center gap-1.5">
                    <span>🧮 Estimator & BOM</span>
                </a>
                <a href="{{ route('quotations.create', $siteSurvey->lead) }}" 
                   class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-extrabold text-xs shadow-lg shadow-amber-500/20 transition flex items-center gap-1.5">
                    <span>📄 Create Quotation</span>
                </a>
                <a href="{{ route('site-surveys.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800/60 hover:bg-slate-800 text-slate-300 text-xs font-semibold border border-slate-700 transition">
                    ← Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-[#060913] min-h-screen text-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('status'))
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-2">
                    <span>✓ {{ session('status') }}</span>
                </div>
            @endif

            {{-- Pending Status Notice --}}
            @if($siteSurvey->status === 'pending')
                <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-950/40 via-[#0F172A] to-[#0F172A] border border-amber-500/40 flex items-start gap-4 shadow-xl">
                    <div class="p-3 bg-amber-500 text-slate-950 rounded-xl shadow-lg shadow-amber-500/20 text-lg font-bold shrink-0">⏳</div>
                    <div>
                        <h4 class="text-sm font-extrabold text-amber-300 font-heading">Awaiting On-Site Technician Inspection</h4>
                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                            Assigned to <strong class="text-white">{{ $siteSurvey->surveyedBy->name ?? 'Field Engineer' }}</strong> for site inspection on <strong class="text-amber-400">{{ $siteSurvey->survey_date->format('l, d M Y') }}</strong>. Once visited, the technician will submit camera recommendations, cable measurements, and on-site photos via their portal.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Schedule & Customer Overview --}}
            <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl overflow-hidden">
                <div class="px-6 py-4 bg-[#0B1120] border-b border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📋</span>
                        <h3 class="text-sm font-extrabold text-white font-heading">Survey Schedule & Client Information</h3>
                    </div>
                    <span class="text-xs text-slate-400 font-mono">Survey ID #{{ $siteSurvey->id }}</span>
                </div>

                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Customer Name</div>
                        <div class="text-sm font-bold text-white mt-1">{{ $siteSurvey->lead->customer_name }}</div>
                        @if($siteSurvey->lead->company_name)
                            <div class="text-xs text-slate-400 mt-0.5">{{ $siteSurvey->lead->company_name }}</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Scheduled Survey Date</div>
                        <div class="text-sm font-bold text-amber-400 mt-1">{{ $siteSurvey->survey_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Assigned Field Engineer</div>
                        <div class="text-sm font-bold text-sky-400 mt-1">{{ $siteSurvey->surveyedBy->name ?? '—' }} ({{ ucfirst($siteSurvey->surveyedBy->role ?? 'Staff') }})</div>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Premises Inspection Address</div>
                        <div class="text-sm font-medium text-slate-200 mt-1 flex items-center gap-1.5">
                            <span class="text-amber-400">📍</span> {{ $siteSurvey->site_address ?: ($siteSurvey->lead->site_address ?? 'Not specified') }}
                        </div>
                    </div>
                    @if($siteSurvey->contact_person || $siteSurvey->contact_phone || $siteSurvey->lead->phone)
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">On-Site Contact Person</div>
                            <div class="text-sm font-medium text-slate-200 mt-1">{{ $siteSurvey->contact_person ?: $siteSurvey->lead->customer_name }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Contact Phone</div>
                            <div class="text-sm font-bold text-amber-400 font-mono mt-1">
                                @php $phone = $siteSurvey->contact_phone ?: $siteSurvey->lead->phone; @endphp
                                @if($phone)
                                    <a href="tel:{{ $phone }}" class="hover:underline">📞 {{ $phone }}</a>
                                @else — @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Instructions / Customer Scope --}}
            @if($siteSurvey->visit_notes)
                <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl overflow-hidden">
                    <div class="px-6 py-4 bg-[#0B1120] border-b border-white/5 flex items-center gap-2">
                        <span class="text-base">📝</span>
                        <h3 class="text-sm font-extrabold text-white font-heading">Instructions & Customer Requirements Scope</h3>
                    </div>
                    <div class="p-6 text-xs text-slate-300 bg-[#060913] mx-6 mb-6 rounded-xl border border-slate-800 leading-relaxed whitespace-pre-wrap">
                        {{ $siteSurvey->visit_notes }}
                    </div>
                </div>
            @endif

            {{-- Technical Findings Submitted by Field Engineer --}}
            @if($siteSurvey->status === 'completed' || $siteSurvey->camera_count_recommended)
                <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl overflow-hidden">
                    <div class="px-6 py-4 bg-[#0B1120] border-b border-white/5 flex items-center gap-2">
                        <span class="text-base">🔍</span>
                        <h3 class="text-sm font-extrabold text-white font-heading">On-Site Technical Findings & Sizing</h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl bg-[#060913] border border-slate-800">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Cameras Recommended</div>
                                <div class="text-2xl font-black text-amber-400 font-heading mt-1">
                                    {{ $siteSurvey->camera_count_recommended ?? '—' }} <span class="text-xs font-bold text-slate-400">Units</span>
                                </div>
                            </div>
                            <div class="p-4 rounded-xl bg-[#060913] border border-slate-800">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Cable Length Estimate</div>
                                <div class="text-2xl font-black text-sky-400 font-heading mt-1">
                                    @if($siteSurvey->cable_length_estimate)
                                        {{ number_format($siteSurvey->cable_length_estimate, 1) }} <span class="text-xs font-bold text-slate-400">m</span>
                                    @else —
                                    @endif
                                </div>
                            </div>
                            <div class="p-4 rounded-xl bg-[#060913] border border-slate-800">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">DVR / NVR Rack Location</div>
                                <div class="text-sm font-bold text-white mt-1.5">{{ $siteSurvey->dvr_location ?? '—' }}</div>
                            </div>
                        </div>

                        @if($siteSurvey->power_availability)
                            <div class="p-4 rounded-xl bg-[#060913] border border-slate-800">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">⚡ Power Availability & UPS Connectivity</div>
                                <div class="text-xs text-slate-300 leading-relaxed">{{ $siteSurvey->power_availability }}</div>
                            </div>
                        @endif

                        @if($siteSurvey->challenges)
                            <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-amber-400 mb-1">⚠️ Site Obstacles & Challenges</div>
                                <div class="text-xs text-slate-200 leading-relaxed">{{ $siteSurvey->challenges }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Inspection Photo Gallery --}}
            <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl overflow-hidden">
                <div class="px-6 py-4 bg-[#0B1120] border-b border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📷</span>
                        <h3 class="text-sm font-extrabold text-white font-heading">Site Inspection Photos ({{ $siteSurvey->photos->count() }})</h3>
                    </div>
                </div>
                <div class="p-6">
                    @if($siteSurvey->photos->count())
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            @foreach($siteSurvey->photos as $photo)
                                <div class="rounded-xl overflow-hidden border border-slate-800 bg-[#060913] group relative">
                                    <img src="{{ $photo->url() }}" alt="{{ $photo->original_name }}"
                                         class="w-full h-40 object-cover cursor-pointer group-hover:scale-105 transition duration-300"
                                         onclick="openLightbox('{{ $photo->url() }}')">
                                    @if($photo->caption)
                                        <div class="p-2.5 text-[11px] font-semibold text-slate-300 bg-[#0F172A] truncate">{{ $photo->caption }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 text-center text-slate-500 text-xs">
                            <svg class="w-10 h-10 mx-auto text-slate-600 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                            </svg>
                            No inspection photos uploaded yet. The assigned technician will capture site photos during their visit.
                        </div>
                    @endif
                </div>
            </div>

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

