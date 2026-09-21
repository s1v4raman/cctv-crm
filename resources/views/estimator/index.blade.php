<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center text-slate-950 shadow-lg shadow-amber-500/20 font-black">
                    ⚡
                </div>
                <div>
                    <h2 class="text-2xl font-black tracking-wider text-white uppercase font-['Outfit'] flex items-center gap-2">
                        CCTV Storage & Sizing Estimator
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">Auto-BOM Engine</span>
                    </h2>
                    <p class="text-xs text-slate-400 font-mono">
                        Calculate exact HDD storage, network PoE load, cabling rolls & auto-generate official Quotations in 1-click.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 print:hidden">
                <button type="button" onclick="window.print()" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white text-xs font-bold transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Print Spec Sheet</span>
                </button>
                <a href="{{ route('quotations.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700/60 bg-slate-800/40 text-slate-400 hover:text-white text-xs font-semibold transition">
                    <span>&larr; Back to Quotations</span>
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Main Calculator Container with Alpine.js Reactive State --}}
    <div x-data="cctvEstimatorApp()" x-init="init()" class="py-8 bg-[#060913] min-h-screen text-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. Project Context & Preset Controls --}}
            <div class="bg-[#0F172A] rounded-2xl p-6 border border-white/10 shadow-xl">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                    
                    {{-- Lead Selector --}}
                    <div class="md:col-span-5">
                        <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5 flex items-center justify-between">
                            <span>Target Customer / Lead <span class="text-amber-400">*</span></span>
                            @if(!empty($initialData['survey_id']))
                                <span class="text-[10px] text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded font-mono font-semibold">Loaded from Site Survey #{{ $initialData['survey_id'] }}</span>
                            @endif
                        </label>
                        <select x-model="selectedLeadId" class="w-full text-xs rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-2.5">
                            <option value="">-- Select Customer / Lead (or General Estimate) --</option>
                            @foreach($leads as $lead)
                                <option value="{{ $lead->id }}">
                                    {{ $lead->customer_name }} ({{ $lead->phone }}) - {{ ucfirst($lead->status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- System Topology Switch --}}
                    <div class="md:col-span-4">
                        <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">
                            System Architecture
                        </label>
                        <div class="grid grid-cols-2 gap-2 bg-[#060913] p-1.5 rounded-xl border border-slate-800">
                            <button type="button" 
                                    @click="systemType = 'ip'; recalculateAll()"
                                    :class="systemType === 'ip' ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:text-white font-semibold'"
                                    class="py-2 text-xs rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>🌐 IP Network (NVR)</span>
                            </button>
                            <button type="button" 
                                    @click="systemType = 'analog'; recalculateAll()"
                                    :class="systemType === 'analog' ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:text-white font-semibold'"
                                    class="py-2 text-xs rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>📹 HD Analog (DVR)</span>
                            </button>
                        </div>
                    </div>

                    {{-- Quick Template Presets --}}
                    <div class="md:col-span-3">
                        <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">
                            Quick Setup Presets
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="loadPreset('home_4')" 
                                    class="flex-1 py-2 px-2 text-[11px] font-bold rounded-xl border border-slate-700 bg-slate-800/80 hover:bg-amber-500/10 hover:border-amber-500/30 hover:text-amber-400 text-slate-300 transition">
                                4-Cam Home
                            </button>
                            <button type="button" @click="loadPreset('shop_8')" 
                                    class="flex-1 py-2 px-2 text-[11px] font-bold rounded-xl border border-slate-700 bg-slate-800/80 hover:bg-amber-500/10 hover:border-amber-500/30 hover:text-amber-400 text-slate-300 transition">
                                8-Cam Retail
                            </button>
                            <button type="button" @click="loadPreset('factory_16')" 
                                    class="flex-1 py-2 px-2 text-[11px] font-bold rounded-xl border border-slate-700 bg-slate-800/80 hover:bg-amber-500/10 hover:border-amber-500/30 hover:text-amber-400 text-slate-300 transition">
                                16-Cam Plant
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            {{-- 2. Main Workspace Grid: Config Matrix (Left) + Engineering Telemetry Gauges (Right) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- Left: Camera Specifications & Zones Matrix (7 Cols) --}}
                <div class="lg:col-span-7 space-y-6">
                    
                    {{-- Global Recording Policy --}}
                    <div class="bg-[#0F172A] rounded-2xl p-6 border border-white/10 shadow-xl">
                        <div class="flex items-center justify-between pb-3 border-b border-white/5 mb-4">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit'] flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                Global Retention & Recording Profile
                            </h3>
                            <span class="text-xs font-mono text-slate-400">All Camera Zones</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">
                                    Target Retention Period (Days)
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="range" min="7" max="180" step="1" x-model.number="retentionDays" @input="recalculateAll()" class="w-full accent-amber-400 h-2 bg-slate-800 rounded-lg cursor-pointer">
                                    <div class="w-24 text-center font-bold text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-lg text-xs font-mono border border-amber-500/30">
                                        <span x-text="retentionDays"></span> <span>Days</span>
                                    </div>
                                </div>
                                <div class="flex justify-between text-[10px] font-mono text-slate-500 mt-1.5 px-1">
                                    <span @click="retentionDays = 15; recalculateAll()" class="cursor-pointer hover:text-amber-400">15d</span>
                                    <span @click="retentionDays = 30; recalculateAll()" class="cursor-pointer hover:text-amber-400 font-bold text-slate-300">30d (Std)</span>
                                    <span @click="retentionDays = 60; recalculateAll()" class="cursor-pointer hover:text-amber-400">60d</span>
                                    <span @click="retentionDays = 90; recalculateAll()" class="cursor-pointer hover:text-amber-400">90d</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">
                                    Default Cable Distance (Meters / Point)
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="number" min="5" max="250" x-model.number="defaultDistanceMeters" @input="updateAllZoneDistances()" class="w-full text-xs font-mono rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-2.5">
                                    <span class="text-xs text-slate-400 font-mono shrink-0">Mtr/Cam</span>
                                </div>
                                <p class="text-[10px] text-slate-500 font-mono mt-1">Includes riser and drop slack margins.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Camera Groups / Zones Builder --}}
                    <div class="bg-[#0F172A] rounded-2xl p-6 border border-white/10 shadow-xl space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-white/5">
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit'] flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span>
                                    Camera Deployment Zones
                                </h3>
                                <p class="text-xs text-slate-400 font-mono">Configure camera resolutions, codecs & locations</p>
                            </div>
                            <button type="button" @click="addCameraZone()" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 hover:bg-amber-500/20 text-xs font-bold transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Camera Group</span>
                            </button>
                        </div>

                        {{-- Zones List --}}
                        <div class="space-y-4">
                            <template x-for="(zone, index) in cameraZones" :key="zone.id">
                                <div class="p-4 rounded-xl bg-[#060913] border border-white/10 space-y-3 transition hover:border-slate-600">
                                    
                                    {{-- Zone Header Row --}}
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2 flex-1">
                                            <span class="w-6 h-6 rounded-full bg-slate-800 text-amber-400 text-xs font-mono font-bold flex items-center justify-center shrink-0 border border-slate-700" x-text="index + 1"></span>
                                            <input type="text" x-model="zone.name" class="text-xs font-bold text-white bg-transparent border-0 border-b border-dashed border-slate-700 focus:ring-0 focus:border-amber-400 p-0 w-full" placeholder="Zone name (e.g., Outdoor Perimeter)">
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1.5 bg-[#0F172A] px-2.5 py-1 rounded-lg border border-slate-700">
                                                <label class="text-[11px] font-mono text-slate-400">Qty:</label>
                                                <input type="number" min="1" max="128" x-model.number="zone.quantity" @input="recalculateAll()" class="w-12 text-center font-bold text-xs p-0 border-0 bg-transparent text-amber-400 focus:ring-0">
                                            </div>

                                            <button type="button" @click="removeCameraZone(index)" :disabled="cameraZones.length === 1" class="text-slate-500 hover:text-rose-400 disabled:opacity-30 p-1 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Zone Parameters Matrix --}}
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-1">
                                        
                                        <div>
                                            <label class="block text-[10px] font-mono font-bold text-slate-400 uppercase">Resolution</label>
                                            <select x-model="zone.resolution" @change="recalculateAll()" class="w-full text-xs rounded-lg border border-slate-700 py-1.5 px-2 bg-[#0F172A] text-white focus:border-amber-400 focus:outline-none">
                                                <option value="2MP">2 MP (1080p FHD)</option>
                                                <option value="3MP">3 MP (2K)</option>
                                                <option value="4MP">4 MP (2.5K Ultra)</option>
                                                <option value="5MP">5 MP (3K Pro)</option>
                                                <option value="8MP">8 MP (4K UHD)</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-mono font-bold text-slate-400 uppercase">Compression</label>
                                            <select x-model="zone.codec" @change="recalculateAll()" class="w-full text-xs rounded-lg border border-slate-700 py-1.5 px-2 bg-[#0F172A] text-white focus:border-amber-400 focus:outline-none">
                                                <option value="H265_PLUS">H.265+ (Smart 70%)</option>
                                                <option value="H265">H.265 (HEVC 50%)</option>
                                                <option value="H264_PLUS">H.264+ (Enhanced)</option>
                                                <option value="H264">H.264 (Standard)</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-mono font-bold text-slate-400 uppercase">Frame Rate</label>
                                            <select x-model.number="zone.fps" @change="recalculateAll()" class="w-full text-xs rounded-lg border border-slate-700 py-1.5 px-2 bg-[#0F172A] text-white focus:border-amber-400 focus:outline-none">
                                                <option value="12">12 FPS (Storage Saver)</option>
                                                <option value="15">15 FPS (Commercial)</option>
                                                <option value="20">20 FPS (Smooth)</option>
                                                <option value="25">25 FPS (Real-time PAL)</option>
                                                <option value="30">30 FPS (Full Real-time)</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-mono font-bold text-slate-400 uppercase">Recording Mode</label>
                                            <select x-model.number="zone.hoursPerDay" @change="recalculateAll()" class="w-full text-xs rounded-lg border border-slate-700 py-1.5 px-2 bg-[#0F172A] text-white focus:border-amber-400 focus:outline-none">
                                                <option value="24">24/7 Continuous (24h)</option>
                                                <option value="16">Extended Motion (16h)</option>
                                                <option value="12">Office / Store (12h)</option>
                                                <option value="8">Motion Only (8h)</option>
                                            </select>
                                        </div>

                                    </div>

                                    {{-- Zone Sub-metrics Bar --}}
                                    <div class="flex items-center justify-between text-[11px] bg-[#0F172A] px-3 py-1.5 rounded-lg border border-slate-800 text-slate-400 font-mono">
                                        <div class="flex items-center gap-3">
                                            <span>Bitrate: <strong class="text-sky-400" x-text="zone.bitrateKbps + ' Kbps'"></strong></span>
                                            <span class="text-slate-600">|</span>
                                            <span>Daily / Cam: <strong class="text-slate-200" x-text="zone.dailyGbPerCam + ' GB/day'"></strong></span>
                                        </div>
                                        <div>
                                            <span>Group Storage: <strong class="text-amber-400 font-bold" x-text="zone.totalStorageGb + ' GB (' + (zone.totalStorageGb/1024).toFixed(2) + ' TB)'"></strong></span>
                                        </div>
                                    </div>

                                </div>
                            </template>
                        </div>
                    </div>

                </div>

                {{-- Right: Engineering Telemetry, Sizing & Sizing Outputs (5 Cols) --}}
                <div class="lg:col-span-5 space-y-5">
                    
                    {{-- 1. Storage Calculation Summary Card --}}
                    <div class="bg-[#0F172A] border border-amber-500/30 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl"></div>
                        
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-white/10">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                                </span>
                                <h4 class="text-sm font-bold uppercase tracking-wider text-white font-['Outfit']">Storage Sizing Output</h4>
                            </div>
                            <span class="text-[11px] font-mono font-bold px-2.5 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/30" x-text="totalCameras + ' Cameras Total'"></span>
                        </div>

                        <div class="space-y-3.5 font-mono">
                            <div class="flex items-baseline justify-between text-xs">
                                <span class="text-slate-400">Total Raw Space Needed:</span>
                                <span class="text-slate-200 font-bold" x-text="rawStorageGb.toFixed(0) + ' GB'"></span>
                            </div>

                            <div class="flex items-baseline justify-between">
                                <span class="text-xs text-slate-400">With 10% Headroom Buffer:</span>
                                <span class="text-2xl font-black text-amber-400 font-['Outfit']" x-text="requiredStorageTb.toFixed(2) + ' TB'"></span>
                            </div>

                            <div class="bg-[#060913] border border-white/10 p-4 rounded-xl space-y-1">
                                <div class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Recommended HDD Configuration:</div>
                                <div class="text-sm font-bold text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span x-text="recommendedHddPlan"></span>
                                </div>
                                <p class="text-[10px] text-slate-500">Enterprise 24/7 Surveillance Grade (5400-7200 RPM)</p>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Power, Network & Cabling Telemetry --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        {{-- Bandwidth & PoE / SMPS --}}
                        <div class="bg-[#0F172A] rounded-2xl p-4 border border-white/10 shadow-xl space-y-2">
                            <div class="flex items-center gap-2 text-xs font-bold text-white font-['Outfit'] uppercase">
                                <span class="p-1 rounded bg-amber-500/10 text-amber-400">⚡</span>
                                Power & Network
                            </div>

                            <div class="space-y-2 pt-1 text-xs font-mono">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Throughput:</span>
                                    <span class="font-bold text-white" x-text="totalBandwidthMbps.toFixed(2) + ' Mbps'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400" x-text="systemType === 'ip' ? 'PoE Draw:' : 'SMPS Draw:'"></span>
                                    <span class="font-bold text-amber-400" x-text="totalPowerWatts + ' W'"></span>
                                </div>
                                <div class="pt-2 border-t border-white/5 text-[11px]">
                                    <span class="text-slate-500 block">Switch/PSU:</span>
                                    <strong class="text-sky-400" x-text="recommendedPowerGear"></strong>
                                </div>
                            </div>
                        </div>

                        {{-- Cabling & Hardware Accessories --}}
                        <div class="bg-[#0F172A] rounded-2xl p-4 border border-white/10 shadow-xl space-y-2">
                            <div class="flex items-center gap-2 text-xs font-bold text-white font-['Outfit'] uppercase">
                                <span class="p-1 rounded bg-emerald-500/10 text-emerald-400">🔌</span>
                                Cabling & Rack
                            </div>

                            <div class="space-y-2 pt-1 text-xs font-mono">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Estimated Run:</span>
                                    <span class="font-bold text-white" x-text="totalCableMeters + ' Mtr'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Cable Rolls:</span>
                                    <span class="font-bold text-emerald-400" x-text="cableRollsCount + ' Roll(s)'"></span>
                                </div>
                                <div class="pt-2 border-t border-white/5 text-[11px]">
                                    <span class="text-slate-500 block">Enclosure:</span>
                                    <strong class="text-sky-400" x-text="recommendedRackSize"></strong>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- 3. Hardware Recommendation Summary Checklist --}}
                    <div class="bg-[#0F172A] rounded-2xl p-4 border border-white/10 text-xs space-y-2.5 font-mono">
                        <div class="font-bold text-white flex items-center justify-between font-['Outfit'] uppercase">
                            <span>Bill of Materials Blueprint</span>
                            <span class="text-[10px] px-2 py-0.5 rounded font-mono font-bold uppercase bg-amber-500/10 text-amber-400 border border-amber-500/30">Auto-Sized</span>
                        </div>
                        <ul class="space-y-1.5 text-slate-300">
                            <li class="flex items-center justify-between">
                                <span class="text-slate-400">&bull; Recorder Channels:</span>
                                <strong class="text-white" x-text="recommendedRecorder"></strong>
                            </li>
                            <li class="flex items-center justify-between">
                                <span class="text-slate-400">&bull; Connectors Required:</span>
                                <strong class="text-amber-400" x-text="connectorsCount + (systemType === 'ip' ? ' RJ45' : ' BNC+DC')"></strong>
                            </li>
                            <li class="flex items-center justify-between">
                                <span class="text-slate-400">&bull; Weatherproof Backboxes:</span>
                                <strong class="text-white" x-text="totalCameras + ' Units'"></strong>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>

            {{-- 3. Interactive Bill of Materials (BOM) & Direct Quotation Generator --}}
            <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl overflow-hidden">
                
                {{-- BOM Header & Actions --}}
                <div class="p-5 bg-[#0B1120] border-b border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-white font-['Outfit'] flex items-center gap-2">
                            <span>📦 Itemized Bill of Materials (BOM)</span>
                            <span class="text-[10px] font-mono font-semibold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">Auto-Matched Catalog</span>
                        </h3>
                        <p class="text-slate-400 text-xs font-mono mt-0.5">Edit quantities, replace products, or adjust unit rates prior to generating the formal quote.</p>
                    </div>

                    <div class="flex items-center gap-2 print:hidden">
                        <button type="button" @click="addCustomBomItem()" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700 text-xs font-bold transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span>Add Custom Item</span>
                        </button>
                        <button type="button" @click="syncBomFromCalculations()" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition shadow-lg shadow-amber-500/20">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Refresh BOM</span>
                        </button>
                    </div>
                </div>

                {{-- BOM Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-white/5 bg-[#060913] text-[10px] font-mono uppercase tracking-widest text-slate-400">
                                <th class="py-3 px-4 w-12 text-center">#</th>
                                <th class="py-3 px-4">Item & Description</th>
                                <th class="py-3 px-4 w-28 text-center">Qty</th>
                                <th class="py-3 px-4 w-20 text-center">Unit</th>
                                <th class="py-3 px-4 w-32 text-right">Unit Rate (₹)</th>
                                <th class="py-3 px-4 w-32 text-right">Total (₹)</th>
                                <th class="py-3 px-4 w-12 text-center print:hidden"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-slate-300">
                            <template x-for="(item, index) in bomItems" :key="item.id">
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3 px-4 text-center font-mono text-slate-500" x-text="index + 1"></td>
                                    
                                    <td class="py-3 px-4">
                                        <div class="space-y-1">
                                            <input type="text" x-model="item.item_name" class="w-full text-xs font-bold text-white rounded-lg border border-slate-700 bg-[#060913] focus:border-amber-400 focus:outline-none p-1.5">
                                            <input type="text" x-model="item.description" placeholder="Specification details / notes" class="w-full text-[11px] text-slate-400 rounded-lg border border-slate-800 bg-[#060913] focus:border-amber-400 focus:outline-none p-1 font-mono">
                                        </div>
                                    </td>

                                    <td class="py-3 px-4 text-center">
                                        <input type="number" min="0.1" step="any" x-model.number="item.quantity" @input="updateBomTotals()" class="w-20 text-center text-xs font-mono font-bold text-sky-400 rounded-lg border border-slate-700 bg-[#060913] focus:border-amber-400 focus:outline-none p-1.5">
                                    </td>

                                    <td class="py-3 px-4 text-center">
                                        <input type="text" x-model="item.unit" class="w-16 text-center text-xs rounded-lg border border-slate-700 bg-[#060913] focus:border-amber-400 focus:outline-none p-1.5 font-mono text-slate-300">
                                    </td>

                                    <td class="py-3 px-4 text-right">
                                        <input type="number" min="0" step="any" x-model.number="item.unit_price" @input="updateBomTotals()" class="w-28 text-right text-xs font-mono rounded-lg border border-slate-700 bg-[#060913] focus:border-amber-400 focus:outline-none p-1.5 text-slate-200">
                                    </td>

                                    <td class="py-3 px-4 text-right font-mono font-bold text-amber-400 text-xs">
                                        ₹<span x-text="formatNumber(item.quantity * item.unit_price)"></span>
                                    </td>

                                    <td class="py-3 px-4 text-center print:hidden">
                                        <button type="button" @click="removeBomItem(index)" class="text-rose-400 hover:text-rose-300 p-1 transition" title="Delete item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Financials & Conversion Action Bar --}}
                <div class="p-6 bg-[#0B1120] border-t border-white/5">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
                        
                        {{-- Notes & Terms --}}
                        <div class="lg:col-span-6 space-y-2">
                            <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400">Quotation Remarks / Engineering Notes</label>
                            <textarea x-model="quotationNotes" rows="4" class="w-full text-xs font-mono rounded-xl border border-slate-700 bg-[#060913] focus:border-amber-400 focus:outline-none p-3 text-slate-300" placeholder="e.g., Includes 1-year warranty, Cat6 cable conduits, and mobile app configuration."></textarea>
                        </div>

                        {{-- Calculation Totals Summary --}}
                        <div class="lg:col-span-6 space-y-4">
                            <div class="bg-[#060913] p-5 rounded-xl border border-white/10 space-y-2.5 text-xs font-mono">
                                <div class="flex justify-between text-slate-400">
                                    <span>BOM Subtotal:</span>
                                    <span class="font-bold text-white">₹<span x-text="formatNumber(bomSubtotal)"></span></span>
                                </div>
                                
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Discount (₹):</span>
                                    <input type="number" min="0" x-model.number="bomDiscount" @input="updateBomTotals()" class="w-24 text-right text-xs rounded-lg border border-slate-700 bg-[#0F172A] p-1 text-white focus:border-amber-400 focus:outline-none">
                                </div>

                                <div class="flex items-center justify-between text-slate-400">
                                    <span>GST / Tax (%):</span>
                                    <input type="number" min="0" max="28" x-model.number="bomTaxPercent" @input="updateBomTotals()" class="w-20 text-right text-xs rounded-lg border border-slate-700 bg-[#0F172A] p-1 text-white focus:border-amber-400 focus:outline-none">
                                </div>

                                <div class="flex justify-between text-slate-400">
                                    <span>GST Tax Amount:</span>
                                    <span class="font-bold text-white">₹<span x-text="formatNumber(bomTaxAmount)"></span></span>
                                </div>

                                <div class="pt-3 border-t border-white/10 flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase tracking-wider text-white font-['Outfit']">Grand Total:</span>
                                    <span class="text-xl font-black text-amber-400 font-mono">₹<span x-text="formatNumber(bomGrandTotal)"></span></span>
                                </div>
                            </div>

                            {{-- 1-Click Convert to Quotation Action --}}
                            <div class="flex items-center justify-end gap-3 print:hidden">
                                <button type="button" 
                                        @click="submitQuotationConversion()" 
                                        :disabled="isSubmitting || !selectedLeadId || bomItems.length === 0"
                                        class="w-full py-3.5 px-5 rounded-xl font-black text-sm uppercase tracking-wider bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20 transition-all">
                                    <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <svg x-show="isSubmitting" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span x-text="isSubmitting ? 'Generating Quotation...' : '📄 1-Click Convert BOM to Official Quotation'"></span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Script with Calculator Engine & Catalog Auto-Matching --}}
    <script>
        function cctvEstimatorApp() {
            return {
                products: @json($products),
                selectedLeadId: '{{ $initialData['lead_id'] ?? '' }}',
                systemType: 'ip',
                retentionDays: 30,
                defaultDistanceMeters: {{ $initialData['cable_length'] ? round($initialData['cable_length'] / max(1, $initialData['camera_count'] ?? 1)) : 25 }},
                
                cameraZones: [
                    {
                        id: 1,
                        name: 'Outdoor Perimeter & Gate',
                        quantity: {{ $initialData['camera_count'] ?? 4 }},
                        resolution: '4MP',
                        codec: 'H265_PLUS',
                        fps: 20,
                        hoursPerDay: 24,
                        distanceMeters: 25,
                        bitrateKbps: 0,
                        dailyGbPerCam: 0,
                        totalStorageGb: 0
                    }
                ],

                totalCameras: 0,
                rawStorageGb: 0,
                requiredStorageTb: 0,
                recommendedHddPlan: '',
                totalBandwidthMbps: 0,
                totalPowerWatts: 0,
                recommendedPowerGear: '',
                totalCableMeters: 0,
                cableRollsCount: 0,
                recommendedRackSize: '',
                recommendedRecorder: '',
                connectorsCount: 0,

                bomItems: [],
                bomSubtotal: 0,
                bomDiscount: 0,
                bomTaxPercent: 18,
                bomTaxAmount: 0,
                bomGrandTotal: 0,
                quotationNotes: 'Auto-engineered via CCTV Auto-BOM Engine. Includes 1-year product warranty & site commissioning.',
                isSubmitting: false,

                init() {
                    this.recalculateAll();
                    this.syncBomFromCalculations();
                },

                loadPreset(type) {
                    if (type === 'home_4') {
                        this.systemType = 'ip';
                        this.retentionDays = 15;
                        this.cameraZones = [
                            { id: 1, name: 'Home Outdoor Bullet', quantity: 2, resolution: '4MP', codec: 'H265_PLUS', fps: 20, hoursPerDay: 24, distanceMeters: 20, bitrateKbps: 0, dailyGbPerCam: 0, totalStorageGb: 0 },
                            { id: 2, name: 'Living Room Dome', quantity: 2, resolution: '2MP', codec: 'H265_PLUS', fps: 15, hoursPerDay: 12, distanceMeters: 15, bitrateKbps: 0, dailyGbPerCam: 0, totalStorageGb: 0 }
                        ];
                    } else if (type === 'shop_8') {
                        this.systemType = 'ip';
                        this.retentionDays = 30;
                        this.cameraZones = [
                            { id: 1, name: 'Store Floor Dome', quantity: 6, resolution: '4MP', codec: 'H265_PLUS', fps: 20, hoursPerDay: 16, distanceMeters: 25, bitrateKbps: 0, dailyGbPerCam: 0, totalStorageGb: 0 },
                            { id: 2, name: 'Cash Counter & Entrance', quantity: 2, resolution: '5MP', codec: 'H265_PLUS', fps: 25, hoursPerDay: 24, distanceMeters: 20, bitrateKbps: 0, dailyGbPerCam: 0, totalStorageGb: 0 }
                        ];
                    } else if (type === 'factory_16') {
                        this.systemType = 'ip';
                        this.retentionDays = 30;
                        this.cameraZones = [
                            { id: 1, name: 'Perimeter Bullet', quantity: 8, resolution: '4MP', codec: 'H265_PLUS', fps: 20, hoursPerDay: 24, distanceMeters: 45, bitrateKbps: 0, dailyGbPerCam: 0, totalStorageGb: 0 },
                            { id: 2, name: 'Production Floor Dome', quantity: 6, resolution: '4MP', codec: 'H265_PLUS', fps: 20, hoursPerDay: 24, distanceMeters: 30, bitrateKbps: 0, dailyGbPerCam: 0, totalStorageGb: 0 },
                            { id: 3, name: 'Main Gate PTZ Camera', quantity: 2, resolution: '4MP', codec: 'H265_PLUS', fps: 25, hoursPerDay: 24, distanceMeters: 50, bitrateKbps: 0, dailyGbPerCam: 0, totalStorageGb: 0 }
                        ];
                    }
                    this.recalculateAll();
                    this.syncBomFromCalculations();
                },

                addCameraZone() {
                    const nextId = this.cameraZones.length + 1;
                    this.cameraZones.push({
                        id: Date.now(),
                        name: 'Zone ' + nextId + ' Cameras',
                        quantity: 2,
                        resolution: '4MP',
                        codec: 'H265_PLUS',
                        fps: 20,
                        hoursPerDay: 24,
                        distanceMeters: this.defaultDistanceMeters,
                        bitrateKbps: 0,
                        dailyGbPerCam: 0,
                        totalStorageGb: 0
                    });
                    this.recalculateAll();
                    this.syncBomFromCalculations();
                },

                removeCameraZone(index) {
                    if (this.cameraZones.length > 1) {
                        this.cameraZones.splice(index, 1);
                        this.recalculateAll();
                        this.syncBomFromCalculations();
                    }
                },

                updateAllZoneDistances() {
                    this.cameraZones.forEach(z => z.distanceMeters = this.defaultDistanceMeters);
                    this.recalculateAll();
                    this.syncBomFromCalculations();
                },

                getBitrateKbps(resolution, codec, fps) {
                    let baseBitrate = 4096;
                    switch(resolution) {
                        case '2MP': baseBitrate = 4096; break;
                        case '3MP': baseBitrate = 6144; break;
                        case '4MP': baseBitrate = 8192; break;
                        case '5MP': baseBitrate = 10240; break;
                        case '8MP': baseBitrate = 16384; break;
                    }

                    const fpsMultiplier = (fps / 25) * 0.85 + 0.15;
                    let bitrate = baseBitrate * fpsMultiplier;

                    switch(codec) {
                        case 'H264': bitrate *= 1.0; break;
                        case 'H264_PLUS': bitrate *= 0.70; break;
                        case 'H265': bitrate *= 0.50; break;
                        case 'H265_PLUS': bitrate *= 0.30; break;
                    }

                    return Math.round(bitrate);
                },

                recalculateAll() {
                    let totalCams = 0;
                    let rawGb = 0;
                    let totalThroughputKbps = 0;
                    let totalCable = 0;

                    this.cameraZones.forEach(zone => {
                        const qty = Math.max(1, parseInt(zone.quantity) || 1);
                        const bitrate = this.getBitrateKbps(zone.resolution, zone.codec, zone.fps);
                        zone.bitrateKbps = bitrate;

                        const dailyGb = (bitrate * 3600 * (zone.hoursPerDay || 24)) / (8 * 1024 * 1024);
                        zone.dailyGbPerCam = parseFloat(dailyGb.toFixed(2));

                        const zoneStorageGb = dailyGb * this.retentionDays * qty;
                        zone.totalStorageGb = parseFloat(zoneStorageGb.toFixed(1));

                        totalCams += qty;
                        rawGb += zoneStorageGb;
                        totalThroughputKbps += (bitrate * qty);
                        totalCable += (qty * (zone.distanceMeters || this.defaultDistanceMeters));
                    });

                    this.totalCameras = totalCams;
                    this.rawStorageGb = rawGb;
                    
                    const bufferedGb = rawGb * 1.10;
                    this.requiredStorageTb = bufferedGb / 1024;
                    this.totalBandwidthMbps = totalThroughputKbps / 1024;

                    this.recommendHdd(this.requiredStorageTb);

                    if (this.systemType === 'ip') {
                        this.totalPowerWatts = totalCams * 12;
                        if (totalCams <= 4) this.recommendedPowerGear = '4-Port PoE Switch (65W)';
                        else if (totalCams <= 8) this.recommendedPowerGear = '8-Port PoE Switch (120W)';
                        else if (totalCams <= 16) this.recommendedPowerGear = '16-Port PoE Switch (250W)';
                        else this.recommendedPowerGear = '24-Port Gigabit PoE Switch (370W)';
                    } else {
                        this.totalPowerWatts = totalCams * 6;
                        if (totalCams <= 4) this.recommendedPowerGear = '4-Channel 5A SMPS Power Supply';
                        else if (totalCams <= 8) this.recommendedPowerGear = '8-Channel 10A SMPS Power Supply';
                        else this.recommendedPowerGear = '16-Channel 20A SMPS Power Supply';
                    }

                    if (totalCams <= 4) this.recommendedRecorder = (this.systemType === 'ip' ? '4 Channel 4K NVR' : '4 Channel HD DVR');
                    else if (totalCams <= 8) this.recommendedRecorder = (this.systemType === 'ip' ? '8 Channel 4K NVR' : '8 Channel HD DVR');
                    else if (totalCams <= 16) this.recommendedRecorder = (this.systemType === 'ip' ? '16 Channel 4K NVR' : '16 Channel HD DVR');
                    else this.recommendedRecorder = (this.systemType === 'ip' ? '32 Channel 4K NVR' : '32 Channel HD DVR');

                    this.totalCableMeters = Math.ceil(totalCable);
                    this.cableRollsCount = Math.max(1, Math.ceil(totalCable / 305));
                    this.connectorsCount = (totalCams * 2) + Math.ceil(totalCams * 0.4);

                    if (totalCams <= 4) this.recommendedRackSize = '2U Wall Mount Rack';
                    else if (totalCams <= 8) this.recommendedRackSize = '4U Wall Mount Rack';
                    else if (totalCams <= 16) this.recommendedRackSize = '6U Wall Mount Rack';
                    else this.recommendedRackSize = '9U Server Wall Rack';
                },

                recommendHdd(storageTb) {
                    if (storageTb <= 1.0) {
                        this.recommendedHddPlan = '1 × 1 TB Surveillance Hard Disk';
                    } else if (storageTb <= 2.0) {
                        this.recommendedHddPlan = '1 × 2 TB Surveillance Hard Disk';
                    } else if (storageTb <= 4.0) {
                        this.recommendedHddPlan = '1 × 4 TB Surveillance Hard Disk';
                    } else if (storageTb <= 6.0) {
                        this.recommendedHddPlan = '1 × 6 TB Surveillance Hard Disk';
                    } else if (storageTb <= 8.0) {
                        this.recommendedHddPlan = '2 × 4 TB (8 TB Total) Surveillance HDDs';
                    } else if (storageTb <= 12.0) {
                        this.recommendedHddPlan = '2 × 6 TB (12 TB Total) Surveillance HDDs';
                    } else {
                        const hdd4Count = Math.ceil(storageTb / 4);
                        this.recommendedHddPlan = `${hdd4Count} × 4 TB (${hdd4Count * 4} TB) Surveillance HDDs`;
                    }
                },

                findCatalogProduct(keywords) {
                    const keys = Array.isArray(keywords) ? keywords : [keywords];
                    return this.products.find(p => {
                        const name = (p.name + ' ' + (p.sku || '') + ' ' + (p.description || '')).toLowerCase();
                        return keys.every(k => name.includes(k.toLowerCase()));
                    });
                },

                syncBomFromCalculations() {
                    const items = [];

                    this.cameraZones.forEach((zone, idx) => {
                        let matchedCam = null;
                        if (this.systemType === 'ip') {
                            matchedCam = this.findCatalogProduct([zone.resolution, 'bullet']) || 
                                         this.findCatalogProduct([zone.resolution]) || 
                                         this.findCatalogProduct(['ip', 'camera']);
                        } else {
                            matchedCam = this.findCatalogProduct([zone.resolution, 'bullet']) || 
                                         this.findCatalogProduct(['bullet']) || 
                                         this.findCatalogProduct(['camera']);
                        }

                        items.push({
                            id: 'cam_' + zone.id,
                            product_id: matchedCam ? matchedCam.id : null,
                            item_name: matchedCam ? matchedCam.name : `${zone.resolution} ${this.systemType === 'ip' ? 'IP' : 'HD'} CCTV Camera (${zone.name})`,
                            description: `${zone.resolution} IR Night Vision CCTV Camera for ${zone.name}`,
                            quantity: zone.quantity,
                            unit: 'Nos',
                            unit_price: matchedCam ? parseFloat(matchedCam.unit_price) : (zone.resolution === '4MP' ? 3500 : (zone.resolution === '2MP' ? 2200 : 4500))
                        });
                    });

                    let chCount = this.totalCameras <= 4 ? '4' : (this.totalCameras <= 8 ? '8' : (this.totalCameras <= 16 ? '16' : '32'));
                    let matchedRecorder = null;
                    if (this.systemType === 'ip') {
                        matchedRecorder = this.findCatalogProduct(['nvr', chCount + 'ch']) || this.findCatalogProduct(['nvr', chCount]) || this.findCatalogProduct(['nvr']);
                    } else {
                        matchedRecorder = this.findCatalogProduct(['dvr', chCount + 'ch']) || this.findCatalogProduct(['dvr', chCount]) || this.findCatalogProduct(['dvr']);
                    }

                    items.push({
                        id: 'recorder_' + Date.now(),
                        product_id: matchedRecorder ? matchedRecorder.id : null,
                        item_name: matchedRecorder ? matchedRecorder.name : `${chCount} Channel 4K ${this.systemType === 'ip' ? 'NVR' : 'DVR'} Standalone Recorder`,
                        description: `H.265+ Compression, HDMI/VGA output, Mobile app monitoring`,
                        quantity: 1,
                        unit: 'Nos',
                        unit_price: matchedRecorder ? parseFloat(matchedRecorder.unit_price) : (chCount === '4' ? 4500 : (chCount === '8' ? 8500 : 13500))
                    });

                    let hddTb = this.requiredStorageTb <= 1.0 ? '1tb' : (this.requiredStorageTb <= 2.0 ? '2tb' : (this.requiredStorageTb <= 4.0 ? '4tb' : '6tb'));
                    let hddQty = this.requiredStorageTb > 6 ? Math.ceil(this.requiredStorageTb / 4) : 1;
                    if (this.requiredStorageTb > 6) hddTb = '4tb';
                    
                    let matchedHdd = this.findCatalogProduct(['hard disk', hddTb]) || this.findCatalogProduct(['hdd', hddTb]) || this.findCatalogProduct(['hdd']) || this.findCatalogProduct(['surveillance']);

                    items.push({
                        id: 'hdd_' + Date.now(),
                        product_id: matchedHdd ? matchedHdd.id : null,
                        item_name: matchedHdd ? matchedHdd.name : `${hddTb.toUpperCase()} Surveillance Grade Hard Disk`,
                        description: `24/7 Continuous video recording HDD (SATA 6Gb/s)`,
                        quantity: hddQty,
                        unit: 'Nos',
                        unit_price: matchedHdd ? parseFloat(matchedHdd.unit_price) : (hddTb === '1tb' ? 4800 : (hddTb === '2tb' ? 6500 : (hddTb === '4tb' ? 9800 : 14500)))
                    });

                    if (this.systemType === 'ip') {
                        let switchPorts = this.totalCameras <= 4 ? '8' : (this.totalCameras <= 8 ? '8' : (this.totalCameras <= 16 ? '16' : '24'));
                        let matchedSwitch = this.findCatalogProduct(['poe', switchPorts]) || this.findCatalogProduct(['poe']) || this.findCatalogProduct(['switch']);
                        items.push({
                            id: 'poe_' + Date.now(),
                            product_id: matchedSwitch ? matchedSwitch.id : null,
                            item_name: matchedSwitch ? matchedSwitch.name : `${switchPorts} Port PoE Network Switch`,
                            description: `10/100/1000 Mbps Gigabit PoE Switch with Uplink`,
                            quantity: 1,
                            unit: 'Nos',
                            unit_price: matchedSwitch ? parseFloat(matchedSwitch.unit_price) : (switchPorts === '8' ? 5500 : 10500)
                        });
                    } else {
                        let matchedPsu = this.findCatalogProduct(['power supply']) || this.findCatalogProduct(['12v']);
                        items.push({
                            id: 'psu_' + Date.now(),
                            product_id: matchedPsu ? matchedPsu.id : null,
                            item_name: matchedPsu ? matchedPsu.name : '12V CCTV SMPS Multi-Channel Power Supply',
                            description: 'Regulated SMPS power supply with individual channel fuses',
                            quantity: 1,
                            unit: 'Nos',
                            unit_price: matchedPsu ? parseFloat(matchedPsu.unit_price) : 950
                        });
                    }

                    let matchedCable = this.systemType === 'ip' ? (this.findCatalogProduct(['cat6']) || this.findCatalogProduct(['cable'])) : (this.findCatalogProduct(['coaxial']) || this.findCatalogProduct(['cable']));
                    items.push({
                        id: 'cable_' + Date.now(),
                        product_id: matchedCable ? matchedCable.id : null,
                        item_name: matchedCable ? matchedCable.name : (this.systemType === 'ip' ? 'CAT6 High Speed Network Cable' : '3+1 Coaxial CCTV Cable'),
                        description: `Pure copper cable laying and conduit piping`,
                        quantity: this.totalCableMeters,
                        unit: 'Mtr',
                        unit_price: matchedCable ? parseFloat(matchedCable.unit_price) : (this.systemType === 'ip' ? 22 : 25)
                    });

                    let matchedConnector = this.systemType === 'ip' ? this.findCatalogProduct(['rj45']) : this.findCatalogProduct(['bnc']);
                    items.push({
                        id: 'conn_' + Date.now(),
                        product_id: matchedConnector ? matchedConnector.id : null,
                        item_name: matchedConnector ? matchedConnector.name : (this.systemType === 'ip' ? 'RJ45 Modular Connectors' : 'BNC + DC Power Connectors'),
                        description: 'Gold plated high precision cable connectors',
                        quantity: this.connectorsCount,
                        unit: 'Nos',
                        unit_price: matchedConnector ? parseFloat(matchedConnector.unit_price) : (this.systemType === 'ip' ? 15 : 25)
                    });

                    let matchedJbox = this.findCatalogProduct(['junction', 'box']) || this.findCatalogProduct(['jbox']);
                    items.push({
                        id: 'jbox_' + Date.now(),
                        product_id: matchedJbox ? matchedJbox.id : null,
                        item_name: matchedJbox ? matchedJbox.name : 'Weatherproof CCTV Camera Junction Boxes',
                        description: 'IP65 waterproof mounting base with cable gland',
                        quantity: this.totalCameras,
                        unit: 'Nos',
                        unit_price: matchedJbox ? parseFloat(matchedJbox.unit_price) : 180
                    });

                    items.push({
                        id: 'labor_' + Date.now(),
                        product_id: null,
                        item_name: 'Professional Installation, Testing & Mobile App Setup',
                        description: `Camera mounting, cable alignment, angle calibration & mobile configuration (${this.totalCameras} Points)`,
                        quantity: this.totalCameras,
                        unit: 'Points',
                        unit_price: 350
                    });

                    this.bomItems = items;
                    this.updateBomTotals();
                },

                addCustomBomItem() {
                    this.bomItems.push({
                        id: 'custom_' + Date.now(),
                        product_id: null,
                        item_name: 'Custom Hardware / Service Item',
                        description: '',
                        quantity: 1,
                        unit: 'Nos',
                        unit_price: 1000
                    });
                    this.updateBomTotals();
                },

                removeBomItem(index) {
                    this.bomItems.splice(index, 1);
                    this.updateBomTotals();
                },

                updateBomTotals() {
                    let sub = 0;
                    this.bomItems.forEach(item => {
                        const q = parseFloat(item.quantity) || 0;
                        const p = parseFloat(item.unit_price) || 0;
                        sub += (q * p);
                    });

                    this.bomSubtotal = sub;
                    const discount = Math.min(this.bomSubtotal, parseFloat(this.bomDiscount) || 0);
                    const taxable = this.bomSubtotal - discount;
                    const taxPercent = parseFloat(this.bomTaxPercent) || 0;
                    this.bomTaxAmount = taxable * (taxPercent / 100);
                    this.bomGrandTotal = taxable + this.bomTaxAmount;
                },

                formatNumber(val) {
                    return Number(val || 0).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                async submitQuotationConversion() {
                    if (!this.selectedLeadId) {
                        alert('Please select a Target Customer / Lead at the top before generating the Quotation.');
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    if (this.bomItems.length === 0) {
                        alert('Bill of Materials cannot be empty.');
                        return;
                    }

                    this.isSubmitting = true;

                    const payload = {
                        lead_id: this.selectedLeadId,
                        quotation_date: new Date().toISOString().split('T')[0],
                        valid_until: new Date(Date.now() + 15 * 86400000).toISOString().split('T')[0],
                        discount: this.bomDiscount,
                        tax_percent: this.bomTaxPercent,
                        notes: this.quotationNotes,
                        items: this.bomItems.map(item => ({
                            product_id: item.product_id,
                            item_name: item.item_name,
                            description: item.description,
                            quantity: item.quantity,
                            unit: item.unit,
                            unit_price: item.unit_price
                        }))
                    };

                    try {
                        const response = await fetch('{{ route('estimator.convert') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            window.location.href = data.redirect_url;
                        } else {
                            alert(data.message || 'Error generating quotation. Please check the inputs.');
                            this.isSubmitting = false;
                        }
                    } catch (err) {
                        console.error(err);
                        alert('An unexpected network error occurred while generating the quotation.');
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>

    <style>
        @media print {
            body { background: white !important; color: black !important; }
            header, aside, .print\:hidden, nav { display: none !important; }
            .bg-\[\#060913\], .bg-\[\#0F172A\], .bg-\[\#0B1120\] { background: white !important; color: black !important; }
            .shadow-xl, .shadow-lg { box-shadow: none !important; }
            .border { border: 1px solid #e2e8f0 !important; }
            input, select, textarea { border: none !important; background: transparent !important; color: black !important; }
        }
    </style>
</x-app-layout>
