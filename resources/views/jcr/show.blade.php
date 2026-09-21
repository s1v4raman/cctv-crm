<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black tracking-wider text-white uppercase font-['Outfit']">📄 {{ $jobCompletionReport->report_no }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                        ✓ SIGNED & CERTIFIED
                    </span>
                </div>
                <p class="text-xs text-slate-400 font-mono mt-1">Signed on {{ $jobCompletionReport->completion_date->format('d M Y, h:i A') }} by {{ $jobCompletionReport->signer_name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('jcr.download-pdf', $jobCompletionReport) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Certificate PDF
                </a>
                <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/[^0-9]/', '', $jobCompletionReport->lead->phone ?? '') }}&text={{ urlencode('Hello ' . ($jobCompletionReport->lead->customer_name ?? 'Customer') . ', here is your signed CCTV Job Completion Certificate: ' . route('jcr.public-pdf', $jobCompletionReport)) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md transition">
                    💬 WhatsApp
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#060913] min-h-screen text-slate-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm font-semibold flex items-center gap-3">
                    <span class="text-base">✓</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Main Certificate Container --}}
            <div class="bg-[#0F172A] rounded-2xl shadow-xl border border-white/10 overflow-hidden mb-6">
                
                {{-- Certificate Top Banner --}}
                <div class="p-6 bg-gradient-to-r from-slate-900 via-[#0B1120] to-amber-950/40 text-white border-b border-white/10">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-amber-400">Official CCTV Handover Certificate</span>
                            <h2 class="text-xl sm:text-2xl font-black mt-0.5 tracking-tight font-['Outfit'] uppercase">Job Completion Report</h2>
                            <p class="text-xs text-slate-400 font-mono mt-1">Ref: {{ $jobCompletionReport->job_type_label }} ({{ $jobCompletionReport->reference_no }})</p>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">QA Pass Score</div>
                            <div class="text-2xl font-black text-emerald-400 font-['Outfit']">{{ $jobCompletionReport->passed_checklist_count }} / 8 <span class="text-xs text-slate-400 font-mono font-normal">({{ $jobCompletionReport->checklist_percentage }}%)</span></div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    {{-- Metadata Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-[#060913] border border-white/10 mb-6 text-xs font-mono">
                        <div>
                            <span class="text-slate-400 font-bold block uppercase tracking-wider text-[10px]">Client / Premise</span>
                            <strong class="text-white text-sm block mt-0.5">{{ $jobCompletionReport->lead->customer_name }}</strong>
                            <span class="text-slate-400 text-[11px]">{{ $jobCompletionReport->lead->site_address ?? $jobCompletionReport->lead->address ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-bold block uppercase tracking-wider text-[10px]">Attending Technician</span>
                            <strong class="text-amber-400 text-sm block mt-0.5">{{ $jobCompletionReport->technician->name }}</strong>
                            <span class="text-slate-400 text-[11px]">{{ $jobCompletionReport->technician->email }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-bold block uppercase tracking-wider text-[10px]">Certified Handover Date</span>
                            <strong class="text-slate-200 text-sm block mt-0.5">{{ $jobCompletionReport->completion_date->format('d M Y') }}</strong>
                            <span class="text-slate-400 text-[11px]">{{ $jobCompletionReport->completion_date->format('h:i A') }}</span>
                        </div>
                    </div>

                    {{-- Work Summary --}}
                    @if($jobCompletionReport->work_summary)
                        <div class="mb-6">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 font-['Outfit']">Work Completed / Engineering Observations</h4>
                            <div class="p-4 rounded-xl bg-[#060913] border border-white/10 text-xs text-slate-300 font-mono leading-relaxed">
                                {{ $jobCompletionReport->work_summary }}
                            </div>
                        </div>
                    @endif

                    {{-- Quality Checklist Audit Table --}}
                    <div class="mb-6">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 font-['Outfit']">Quality Assurance Handover Audit</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                            @php
                                $checks = [
                                    ['📷 Camera Alignment & View Angles', $jobCompletionReport->all_cameras_positioned],
                                    ['💾 Storage & Continuous Recording Test', $jobCompletionReport->recording_configured],
                                    ['📱 Client Mobile App Remote View Setup', $jobCompletionReport->remote_mobile_app_setup],
                                    ['⚡ Power Backup & Voltage Inspection', $jobCompletionReport->power_backup_tested],
                                    ['🛡️ Cable Trunking & Neat Dressing', $jobCompletionReport->cables_dressed_and_trunked],
                                    ['🎓 Customer Playback & Controls Training', $jobCompletionReport->client_training_completed],
                                    ['🧹 Installation Work Area Cleaned', $jobCompletionReport->work_area_cleaned],
                                    ['📜 Warranty Card & Login Credentials Given', $jobCompletionReport->warranty_card_handed],
                                ];
                            @endphp

                            @foreach($checks as $chk)
                                <div class="flex items-center justify-between p-3 rounded-xl border {{ $chk[1] ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-slate-900 border-slate-800 text-slate-500' }}">
                                    <span class="font-medium text-xs">{{ $chk[0] }}</span>
                                    <span class="font-mono font-bold text-[10px] {{ $chk[1] ? 'text-emerald-400' : 'text-slate-500' }}">
                                        {{ $chk[1] ? '✓ VERIFIED' : '—' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Proof Photos --}}
                    @if($jobCompletionReport->photos->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 font-['Outfit']">Handover Photographic Proof</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($jobCompletionReport->photos as $photo)
                                    <div class="rounded-xl overflow-hidden border border-white/10 bg-[#060913]">
                                        <img src="{{ asset('storage/jcr-photos/' . $photo->filename) }}" alt="Handover Photo" class="w-full h-32 object-cover">
                                        @if($photo->caption)
                                            <div class="p-2 text-[10px] font-mono text-slate-400 truncate">{{ $photo->caption }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Customer Feedback Box --}}
                    <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 mb-6">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-amber-300 font-['Outfit'] uppercase">Customer Satisfaction Rating</span>
                            <div class="text-amber-400 text-sm font-bold">
                                @for($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= $jobCompletionReport->customer_rating ? '★' : '☆' }}</span>
                                @endfor
                                <span class="text-xs text-amber-300 ml-1 font-mono">({{ $jobCompletionReport->customer_rating }}/5)</span>
                            </div>
                        </div>
                        @if($jobCompletionReport->customer_feedback)
                            <p class="text-xs text-slate-300 italic mt-1 font-mono">"{{ $jobCompletionReport->customer_feedback }}"</p>
                        @endif
                    </div>

                    {{-- Digital Signatures Section --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-white/10">
                        {{-- Customer Signature --}}
                        <div class="p-4 rounded-xl bg-[#060913] border border-white/10 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-mono uppercase tracking-widest text-slate-400 block mb-2">Customer E-Signature</span>
                                <div class="bg-white rounded-lg p-2 flex items-center justify-center min-h-[100px]">
                                    <img src="{{ $jobCompletionReport->customer_signature }}" alt="Customer Signature" class="max-h-24 max-w-full object-contain">
                                </div>
                            </div>
                            <div class="mt-3 text-xs">
                                <strong class="text-white block font-['Outfit']">{{ $jobCompletionReport->signer_name }}</strong>
                                <span class="text-slate-400 block text-[11px] font-mono">{{ $jobCompletionReport->signer_designation ?? 'Authorized Signer' }} &bull; {{ $jobCompletionReport->signer_phone ?? '' }}</span>
                                <span class="text-[10px] text-emerald-400 font-mono block mt-0.5">Digitally signed & verified</span>
                            </div>
                        </div>

                        {{-- Technician Signature / Confirmation --}}
                        <div class="p-4 rounded-xl bg-[#060913] border border-white/10 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-mono uppercase tracking-widest text-slate-400 block mb-2">Lead Field Engineer</span>
                                <div class="bg-slate-900 rounded-lg p-3 border border-slate-800 flex flex-col items-center justify-center min-h-[100px] text-center">
                                    <span class="text-2xl mb-1">🛠️</span>
                                    <span class="text-xs font-bold text-white font-['Outfit']">{{ $jobCompletionReport->technician->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">Certified Field Engineer</span>
                                </div>
                            </div>
                            <div class="mt-3 text-xs">
                                <strong class="text-white block font-['Outfit']">Job Handover Completed</strong>
                                <span class="text-slate-400 block text-[11px] font-mono">Certified CCTV Handover Standard</span>
                                <span class="text-[10px] text-slate-500 font-mono block mt-0.5">{{ $jobCompletionReport->completion_date->format('d M Y, h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Navigation Back --}}
            <div class="flex items-center justify-between text-xs text-slate-400 font-mono">
                <a href="{{ auth()->user()->isTechnician() ? route('technician.dashboard') : route('jcr.index') }}" class="font-bold text-amber-400 hover:text-amber-300">
                    &larr; Back to {{ auth()->user()->isTechnician() ? 'Technician Workstation' : 'Completion Reports' }}
                </a>
                <span>Report ID: #{{ $jobCompletionReport->id }}</span>
            </div>

        </div>
    </div>
</x-app-layout>
