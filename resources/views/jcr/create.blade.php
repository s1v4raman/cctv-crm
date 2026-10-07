<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-emerald-500 p-0.5 shadow-lg shadow-amber-500/20 flex items-center justify-center">
                    <div class="w-full h-full bg-[#060913] rounded-[10px] flex items-center justify-center text-amber-400 font-bold">
                        ✍️
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-black leading-tight text-white font-heading tracking-tight">
                        Digital Job Sign-Off & Customer Handover
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-400">Quality inspection checklist, photo proof & on-site e-signature</p>
                </div>
            </div>
            <a href="{{ auth()->user()->isTechnician() ? route('technician.dashboard') : url()->previous() }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-800/80 text-xs font-bold text-slate-300 hover:text-white hover:bg-slate-700 transition shadow-sm">
                &larr; Back
            </a>
        </div>
    </x-slot>

    <style>
        .jcr-canvas-container {
            border: 2px dashed #334155;
            border-radius: 1rem;
            background: #060913;
            touch-action: none;
            position: relative;
            box-shadow: inset 0 2px 8px 0 rgba(0, 0, 0, 0.4);
            transition: border-color 0.2s;
        }
        .jcr-canvas-container:hover, .jcr-canvas-container:focus-within {
            border-color: #f59e0b;
        }
        .star-rating label {
            cursor: pointer;
            transition: transform 0.15s ease, color 0.15s ease;
        }
        .star-rating label:hover {
            transform: scale(1.15);
        }
        .chk-card {
            transition: all 0.2s ease;
            cursor: pointer;
            user-select: none;
        }
        .chk-card:hover {
            transform: translateY(-1px);
        }
        .chk-card input:checked + div {
            background-color: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.4);
        }
        .chk-card input:checked + div .chk-icon {
            background-color: #10b981;
            color: #020617;
        }
    </style>

    <div class="py-6 bg-[#060913] min-h-screen text-slate-200" style="background-color: #060913;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Error alerts --}}
            @if ($errors->any())
                <div class="mb-5 p-4 rounded-2xl bg-rose-950/40 border border-rose-500/40 text-rose-300 text-xs shadow-lg">
                    <div class="font-bold flex items-center gap-2 mb-1">
                        <span>⚠️</span> Please fix the following errors:
                    </div>
                    <ul class="list-disc pl-5 space-y-0.5 text-slate-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Site / Job Overview Card --}}
            <div class="bg-[#0f172a] rounded-2xl p-6 shadow-2xl border border-slate-800" style="background-color: #0f172a;">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-800">
                    <div>
                        <span class="inline-block text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded bg-amber-500/20 text-amber-400 border border-amber-500/30 mb-1.5">
                            {{ $title }}
                        </span>
                        <h3 class="text-xl font-extrabold text-white font-heading">{{ $lead?->customer_name ?? 'Customer Handover' }}</h3>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Field Engineer</span>
                        <p class="text-sm font-extrabold text-amber-400">{{ auth()->user()->name }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 text-xs text-slate-300">
                    <div class="flex items-start gap-2 bg-[#060913] p-3 rounded-xl border border-slate-800">
                        <span class="text-base">📍</span>
                        <div>
                            <strong class="text-white block font-bold text-[11px]">Site Location:</strong>
                            <span class="text-slate-400 text-xs">{{ $lead?->site_address ?? $lead?->address ?? 'Not specified' }}</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 bg-[#060913] p-3 rounded-xl border border-slate-800">
                        <span class="text-base">📞</span>
                        <div>
                            <strong class="text-white block font-bold text-[11px]">Customer Phone:</strong>
                            <span class="text-amber-400 font-mono text-xs">{{ $lead?->phone ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 bg-[#060913] p-3 rounded-xl border border-slate-800">
                        <span class="text-base">📅</span>
                        <div>
                            <strong class="text-white block font-bold text-[11px]">Sign-Off Timestamp:</strong>
                            <span class="text-slate-400 font-mono text-xs">{{ now()->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Form --}}
            <form id="jcrForm" method="POST" action="{{ route('jcr.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="lead_id" value="{{ $lead?->id }}">
                @if($job)
                    <input type="hidden" name="installation_job_id" value="{{ $job->id }}">
                @endif
                @if($ticket)
                    <input type="hidden" name="service_ticket_id" value="{{ $ticket->id }}">
                @endif
                @if($visit)
                    <input type="hidden" name="amc_visit_id" value="{{ $visit->id }}">
                @endif

                {{-- Hidden inputs to receive Base64 Signature Data --}}
                <input type="hidden" id="customerSignatureInput" name="customer_signature" value="">
                <input type="hidden" id="technicianSignatureInput" name="technician_signature" value="">

                {{-- SECTION 1: Quality Inspection Handover Checklist --}}
                <div class="bg-[#0f172a] rounded-2xl p-6 shadow-2xl border border-slate-800" style="background-color: #0f172a;">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-bold text-xs">1</span>
                        <h4 class="text-base font-extrabold text-white font-heading">Quality Assurance & Handover Checklist</h4>
                    </div>
                    <p class="text-xs text-slate-400 mb-4">Please verify each item below before asking the customer to sign.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @php
                            $checklistItems = [
                                'all_cameras_positioned'     => ['📷 Camera Alignment', 'All cameras mounted securely and field of view adjusted.'],
                                'recording_configured'       => ['💾 Recording & Storage', 'Continuous/motion recording verified on DVR/NVR hard drive.'],
                                'remote_mobile_app_setup'    => ['📱 Mobile App Viewing', 'Customer phone app connected and live streams verified.'],
                                'power_backup_tested'        => ['⚡ Power Supply & UPS', 'Power supplies checked, DC voltage tested, cables tagged.'],
                                'cables_dressed_and_trunked' => ['🛡️ Cable Dressing', 'Cables neatly routed through conduits/casing without loose wires.'],
                                'client_training_completed'  => ['🎓 User Training', 'Customer instructed on playback search, backup, and app controls.'],
                                'work_area_cleaned'          => ['🧹 Clean Site', 'Drill dust, wire cut-offs, and packaging cleaned and removed.'],
                                'warranty_card_handed'       => ['📜 Warranty & Documentation', 'Handover notes, login credentials, and warranty terms explained.'],
                            ];
                        @endphp

                        @foreach($checklistItems as $key => $details)
                            <label class="chk-card relative block">
                                <input type="checkbox" name="{{ $key }}" value="1" checked class="sr-only">
                                <div class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-800 bg-[#060913] transition">
                                    <span class="chk-icon flex-shrink-0 w-5 h-5 rounded-md bg-slate-800 text-slate-500 flex items-center justify-center text-xs font-bold transition">
                                        ✓
                                    </span>
                                    <div>
                                        <div class="text-xs font-bold text-white">{{ $details[0] }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $details[1] }}</div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-800">
                        <label class="block text-sm font-semibold text-slate-200 mb-1.5">Work Summary / Observations</label>
                        <textarea name="work_summary" rows="2" class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5" placeholder="e.g. Installed 4x 5MP Dome Cameras, configured 1TB HDD recording, setup Hik-Connect on 2 mobile devices..."></textarea>
                    </div>
                </div>

                {{-- SECTION 2: Handover Proof Photos --}}
                <div class="bg-[#0f172a] rounded-2xl p-6 shadow-2xl border border-slate-800" style="background-color: #0f172a;">
                    <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 font-bold text-xs">2</span>
                            <h4 class="text-base font-extrabold text-white font-heading">Handover Proof Photos (Optional)</h4>
                        </div>
                        <span class="text-xs text-amber-400 font-bold flex items-center gap-1.5">
                            <span>📷</span> Live Camera & File Upload Supported
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mb-4">Take photos directly using laptop webcam or mobile phone camera, or select photos from your device.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="photoUploadContainer">
                        {{-- Slot 0: DVR Rack Setup --}}
                        <div class="photo-slot-card p-4 rounded-xl border border-slate-700/80 transition relative flex flex-col justify-between" id="slot-0" style="background:#060913 !important;border-color:#334155;">
                            <div class="flex items-center justify-between mb-2.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm">🗄️</span>
                                    <span class="slot-title" style="font-size:.8rem;font-weight:800;color:#ffffff !important;">DVR / Server Rack Setup</span>
                                </div>
                                <span style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;padding:.2rem .5rem;border-radius:.4rem;background:rgba(59,130,246,0.15);color:#60a5fa !important;border:1px solid rgba(59,130,246,0.3);">Rack Setup</span>
                            </div>

                            {{-- Empty State --}}
                            <div class="slot-empty-state rounded-xl p-4 text-center flex flex-col items-center justify-center" style="border:2px dashed #475569;background:#0b1120;">
                                <div style="width:2.75rem;height:2.75rem;border-radius:.75rem;background:#1e293b;color:#f59e0b;display:flex;align-items:center;justify-content:center;font-size:1.25rem;margin:0 auto .5rem auto;border:1px solid #334155;">
                                    📷
                                </div>
                                <div style="font-size:.85rem;font-weight:800;color:#ffffff !important;margin-bottom:.25rem;">DVR Rack / Setup Photo</div>
                                <p style="font-size:.75rem;color:#cbd5e1 !important;margin-bottom:.85rem;">Snap live with webcam/phone camera or choose file.</p>
                                
                                <div class="flex items-center justify-center gap-2.5 flex-wrap w-full">
                                    <button type="button" onclick="openPhotoCameraModal('slot-0', 'DVR Rack Setup')"
                                            style="padding:.55rem 1rem;background:linear-gradient(135deg,#f59e0b,#d97706);color:#020617 !important;font-weight:800;font-size:.78rem;border-radius:.65rem;border:none;display:inline-flex;align-items:center;gap:.45rem;cursor:pointer;box-shadow:0 4px 12px rgba(245,158,11,0.3);text-decoration:none;">
                                        <span style="font-size:.95rem;">📷</span>
                                        <span style="color:#020617 !important;font-weight:800;display:inline-block;">Take Photo (Camera)</span>
                                    </button>
                                    <label style="padding:.55rem 1rem;background:#2563eb !important;color:#ffffff !important;font-weight:800;font-size:.78rem;border-radius:.65rem;border:1px solid #1d4ed8;display:inline-flex;align-items:center;gap:.45rem;cursor:pointer;box-shadow:0 4px 12px rgba(37,99,235,0.3);text-decoration:none;">
                                        <span style="font-size:.95rem;">📁</span>
                                        <span style="color:#ffffff !important;font-weight:800;display:inline-block;">Choose File</span>
                                        <input type="file" name="photos[]" accept="image/*" onchange="handlePhotoFileSelected(this, 'slot-0')" style="display:none;" class="photo-file-input">
                                    </label>
                                </div>
                            </div>

                            {{-- Filled State (Preview) --}}
                            <div class="slot-filled-state hidden flex-col">
                                <div class="w-full h-44 rounded-xl overflow-hidden relative border border-slate-700 bg-black group">
                                    <img src="" class="slot-preview-img w-full h-full object-cover">
                                    <div class="absolute top-2 left-2 flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/90 text-white font-bold text-[10px] shadow">✓ Photo Attached</span>
                                        <span class="slot-file-size px-2 py-0.5 rounded-full bg-slate-900/80 text-slate-300 font-mono text-[10px] border border-slate-700"></span>
                                    </div>
                                    <div class="absolute bottom-2 inset-x-2 flex items-center justify-center gap-2">
                                        <button type="button" onclick="openPhotoCameraModal('slot-0', 'DVR Rack Setup')"
                                                style="padding:.35rem .75rem;background:#0f172a !important;color:#f59e0b !important;border:1px solid #f59e0b;border-radius:.5rem;font-size:.72rem;font-weight:800;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;">
                                            📷 Retake
                                        </button>
                                        <label style="padding:.35rem .75rem;background:#0f172a !important;color:#60a5fa !important;border:1px solid #3b82f6;border-radius:.5rem;font-size:.72rem;font-weight:800;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;">
                                            📁 Change
                                            <input type="file" accept="image/*" onchange="handlePhotoFileSelected(this, 'slot-0')" style="display:none;">
                                        </label>
                                        <button type="button" onclick="removeSlotPhoto('slot-0')"
                                                style="padding:.35rem .75rem;background:#450a0a !important;color:#fca5a5 !important;border:1px solid #ef4444;border-radius:.5rem;font-size:.72rem;font-weight:800;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;">
                                            🗑️ Remove
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2.5">
                                <input type="text" name="photo_captions[]" value="DVR Rack Setup" placeholder="Photo caption (e.g. DVR Rack Setup)"
                                       style="font-size:.78rem;width:100%;border-radius:.75rem;border:1px solid #334155;background:#0b1120 !important;color:#ffffff !important;padding:.6rem .75rem;margin-top:.4rem;" class="slot-caption">
                                <input type="hidden" name="photo_types[]" value="rack_setup" class="slot-type">
                            </div>
                        </div>

                        {{-- Slot 1: Live Monitor View --}}
                        <div class="photo-slot-card p-4 rounded-xl border border-slate-700/80 transition relative flex flex-col justify-between" id="slot-1" style="background:#060913 !important;border-color:#334155;">
                            <div class="flex items-center justify-between mb-2.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm">🖥️</span>
                                    <span class="slot-title" style="font-size:.8rem;font-weight:800;color:#ffffff !important;">Live Camera / Monitor View</span>
                                </div>
                                <span style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;padding:.2rem .5rem;border-radius:.4rem;background:rgba(245,158,11,0.15);color:#fbbf24 !important;border:1px solid rgba(245,158,11,0.3);">Camera View</span>
                            </div>

                            {{-- Empty State --}}
                            <div class="slot-empty-state rounded-xl p-4 text-center flex flex-col items-center justify-center" style="border:2px dashed #475569;background:#0b1120;">
                                <div style="width:2.75rem;height:2.75rem;border-radius:.75rem;background:#1e293b;color:#f59e0b;display:flex;align-items:center;justify-content:center;font-size:1.25rem;margin:0 auto .5rem auto;border:1px solid #334155;">
                                    📷
                                </div>
                                <div style="font-size:.85rem;font-weight:800;color:#ffffff !important;margin-bottom:.25rem;">Monitor / Camera Angle View</div>
                                <p style="font-size:.75rem;color:#cbd5e1 !important;margin-bottom:.85rem;">Snap live with webcam/phone camera or choose file.</p>
                                
                                <div class="flex items-center justify-center gap-2.5 flex-wrap w-full">
                                    <button type="button" onclick="openPhotoCameraModal('slot-1', 'Live Monitor View')"
                                            style="padding:.55rem 1rem;background:linear-gradient(135deg,#f59e0b,#d97706);color:#020617 !important;font-weight:800;font-size:.78rem;border-radius:.65rem;border:none;display:inline-flex;align-items:center;gap:.45rem;cursor:pointer;box-shadow:0 4px 12px rgba(245,158,11,0.3);text-decoration:none;">
                                        <span style="font-size:.95rem;">📷</span>
                                        <span style="color:#020617 !important;font-weight:800;display:inline-block;">Take Photo (Camera)</span>
                                    </button>
                                    <label style="padding:.55rem 1rem;background:#2563eb !important;color:#ffffff !important;font-weight:800;font-size:.78rem;border-radius:.65rem;border:1px solid #1d4ed8;display:inline-flex;align-items:center;gap:.45rem;cursor:pointer;box-shadow:0 4px 12px rgba(37,99,235,0.3);text-decoration:none;">
                                        <span style="font-size:.95rem;">📁</span>
                                        <span style="color:#ffffff !important;font-weight:800;display:inline-block;">Choose File</span>
                                        <input type="file" name="photos[]" accept="image/*" onchange="handlePhotoFileSelected(this, 'slot-1')" style="display:none;" class="photo-file-input">
                                    </label>
                                </div>
                            </div>

                            {{-- Filled State (Preview) --}}
                            <div class="slot-filled-state hidden flex-col">
                                <div class="w-full h-44 rounded-xl overflow-hidden relative border border-slate-700 bg-black group">
                                    <img src="" class="slot-preview-img w-full h-full object-cover">
                                    <div class="absolute top-2 left-2 flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/90 text-white font-bold text-[10px] shadow">✓ Photo Attached</span>
                                        <span class="slot-file-size px-2 py-0.5 rounded-full bg-slate-900/80 text-slate-300 font-mono text-[10px] border border-slate-700"></span>
                                    </div>
                                    <div class="absolute bottom-2 inset-x-2 flex items-center justify-center gap-2">
                                        <button type="button" onclick="openPhotoCameraModal('slot-1', 'Live Monitor View')"
                                                style="padding:.35rem .75rem;background:#0f172a !important;color:#f59e0b !important;border:1px solid #f59e0b;border-radius:.5rem;font-size:.72rem;font-weight:800;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;">
                                            📷 Retake
                                        </button>
                                        <label style="padding:.35rem .75rem;background:#0f172a !important;color:#60a5fa !important;border:1px solid #3b82f6;border-radius:.5rem;font-size:.72rem;font-weight:800;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;">
                                            📁 Change
                                            <input type="file" accept="image/*" onchange="handlePhotoFileSelected(this, 'slot-1')" style="display:none;">
                                        </label>
                                        <button type="button" onclick="removeSlotPhoto('slot-1')"
                                                style="padding:.35rem .75rem;background:#450a0a !important;color:#fca5a5 !important;border:1px solid #ef4444;border-radius:.5rem;font-size:.72rem;font-weight:800;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;">
                                            🗑️ Remove
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2.5">
                                <input type="text" name="photo_captions[]" value="Live Monitor View" placeholder="Photo caption (e.g. Live Monitor View)"
                                       style="font-size:.78rem;width:100%;border-radius:.75rem;border:1px solid #334155;background:#0b1120 !important;color:#ffffff !important;padding:.6rem .75rem;margin-top:.4rem;" class="slot-caption">
                                <input type="hidden" name="photo_types[]" value="camera_view" class="slot-type">
                            </div>
                        </div>
                    </div>

                    {{-- Add More Photos Action Bar --}}
                    <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between flex-wrap gap-2">
                        <button type="button" onclick="addNewPhotoSlot()"
                                style="padding:.5rem 1rem;background:#1e293b !important;color:#f59e0b !important;border:1px solid #475569;border-radius:.75rem;font-weight:800;font-size:.75rem;display:inline-flex;align-items:center;gap:.4rem;cursor:pointer;">
                            <span style="font-size:.9rem;font-weight:900;">+</span>
                            <span>Add Another Proof Photo</span>
                        </button>
                        <span style="font-size:.72rem;color:#94a3b8 !important;">Up to 10 photos • Supported on Laptop Webcam and Mobile Phone Cameras</span>
                    </div>
                </div>

                {{-- SECTION 3: Customer Feedback & 5-Star Rating --}}
                <div class="bg-[#0f172a] rounded-2xl p-6 shadow-2xl border border-slate-800" style="background-color: #0f172a;">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 font-bold text-xs">3</span>
                        <h4 class="text-base font-extrabold text-white font-heading">Customer Feedback & Satisfaction Rating</h4>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-xl bg-[#060913] border border-amber-500/30 mb-4">
                        <div>
                            <span class="text-xs font-bold text-white block">How satisfied are you with today's installation/service?</span>
                            <span class="text-[11px] text-slate-400">Tap stars to rate (1 = Poor, 5 = Excellent)</span>
                        </div>
                        <div class="star-rating flex items-center gap-2 text-2xl select-none" id="starContainer">
                            <span class="star cursor-pointer text-amber-400 hover:scale-125 transition-transform" data-value="1">★</span>
                            <span class="star cursor-pointer text-amber-400 hover:scale-125 transition-transform" data-value="2">★</span>
                            <span class="star cursor-pointer text-amber-400 hover:scale-125 transition-transform" data-value="3">★</span>
                            <span class="star cursor-pointer text-amber-400 hover:scale-125 transition-transform" data-value="4">★</span>
                            <span class="star cursor-pointer text-amber-400 hover:scale-125 transition-transform" data-value="5">★</span>
                            <input type="hidden" id="customer_rating" name="customer_rating" value="5">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-200 mb-1.5">Customer Comments / Feedback (Optional)</label>
                        <textarea name="customer_feedback" rows="2" class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5" placeholder="e.g. Excellent work, neat wiring, camera angles are very clear!"></textarea>
                    </div>
                </div>

                {{-- SECTION 4: Customer Details & Touch Signature Pad --}}
                <div class="bg-[#0f172a] rounded-2xl p-6 shadow-2xl border border-slate-800" style="background-color: #0f172a;">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-purple-500/20 text-purple-400 border border-purple-500/30 font-bold text-xs">4</span>
                        <h4 class="text-base font-extrabold text-white font-heading">Customer Digital Sign-Off</h4>
                    </div>
                    <p class="text-xs text-slate-400 mb-4">I hereby confirm that the CCTV installation/service has been completed to my full satisfaction.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">Signer Name <span class="text-amber-400">*</span></label>
                            <input type="text" name="signer_name" value="{{ old('signer_name', $lead?->customer_name) }}" required class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">Designation / Role</label>
                            <input type="text" name="signer_designation" value="{{ old('signer_designation') }}" placeholder="e.g. Owner, Store Manager" class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">Phone Number</label>
                            <input type="text" name="signer_phone" value="{{ old('signer_phone', $lead?->phone) }}" class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                        </div>
                    </div>

                    {{-- Signature Canvas Box --}}
                    <div class="mb-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-semibold text-white flex items-center gap-1.5 font-heading">
                                <span>✍️</span> Customer Signature (Sign on screen below) <span class="text-amber-400">*</span>
                            </label>
                            <button type="button" id="clearCustomerSig" class="text-xs font-bold text-rose-400 hover:text-rose-300 transition">
                                ↺ Clear Signature
                            </button>
                        </div>
                        <div class="jcr-canvas-container">
                            <canvas id="customerCanvas" width="700" height="180" class="w-full h-44 rounded-2xl cursor-crosshair"></canvas>
                            <div id="sigPlaceholder" class="absolute inset-0 pointer-events-none flex items-center justify-center text-slate-500 text-xs font-mono">
                                Sign with finger, stylus, or mouse here
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ auth()->user()->isTechnician() ? route('technician.dashboard') : url()->previous() }}"
                       class="px-5 py-2.5 rounded-xl border border-slate-700 text-xs font-bold text-slate-400 hover:text-white hover:bg-slate-800 transition">
                        Cancel
                    </a>
                    <button type="submit" id="submitJcrBtn"
                            class="btn-amber">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Confirm & Generate Signed JCR Certificate</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- LIVE CAMERA MODAL FOR LAPTOP & MOBILE PHOTO CAPTURE --}}
    <div id="jcrCameraModal" style="position:fixed;inset:0;z-index:99999;background:rgba(2,6,23,0.88);backdrop-filter:blur(8px);display:none;align-items:center;justify-content:center;padding:1rem;">
        <div style="background:#0f172a;border:1px solid #334155;border-radius:1.25rem;max-width:32rem;width:100%;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.7);display:flex;flex-direction:column;">
            {{-- Modal Header --}}
            <div style="padding:1rem 1.25rem;border-bottom:1px solid #1e293b;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <span style="font-size:1.25rem;">📷</span>
                    <div>
                        <h3 style="font-size:.95rem;font-weight:800;color:#fff;margin:0;">Take Proof Photo</h3>
                        <p id="jcrModalTargetTitle" style="font-size:.75rem;color:#f59e0b;margin:0;font-weight:600;">DVR Rack Setup</p>
                    </div>
                </div>
                <button type="button" onclick="closeJcrCameraModal()" style="width:2rem;height:2rem;border-radius:999px;background:#1e293b;border:1px solid #334155;color:#94a3b8;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:bold;">
                    ✕
                </button>
            </div>

            {{-- Viewfinder Box --}}
            <div style="position:relative;width:100%;height:320px;background:#000;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                {{-- Live Video --}}
                <video id="jcrCameraVideo" autoplay playsinline muted style="width:100%;height:100%;object-fit:cover;"></video>
                
                {{-- Snapshot Preview (shown after snapping) --}}
                <img id="jcrCameraSnapshotImg" style="display:none;width:100%;height:100%;object-fit:cover;">

                {{-- Hidden Canvas for frame grab --}}
                <canvas id="jcrCameraCanvas" style="display:none;"></canvas>

                {{-- Framing HUD Overlay (corner brackets) --}}
                <div id="jcrViewfinderOverlay" style="position:absolute;inset:20px;pointer-events:none;border-radius:12px;display:flex;flex-direction:column;justify-content:space-between;">
                    <div style="display:flex;justify-content:space-between;">
                        <div style="width:24px;height:24px;border-top:3px solid #f59e0b;border-left:3px solid #f59e0b;border-top-left-radius:8px;"></div>
                        <div style="width:24px;height:24px;border-top:3px solid #f59e0b;border-right:3px solid #f59e0b;border-top-right-radius:8px;"></div>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <div style="width:24px;height:24px;border-bottom:3px solid #f59e0b;border-left:3px solid #f59e0b;border-bottom-left-radius:8px;"></div>
                        <div style="width:24px;height:24px;border-bottom:3px solid #f59e0b;border-right:3px solid #f59e0b;border-bottom-right-radius:8px;"></div>
                    </div>
                </div>

                {{-- Top Viewfinder Controls --}}
                <div style="position:absolute;top:12px;left:12px;right:12px;display:flex;justify-content:space-between;align-items:center;pointer-events:auto;z-index:15;">
                    <button type="button" id="jcrFlipCamBtn" onclick="switchJcrCamera()"
                            style="padding:.4rem .75rem;background:rgba(15,23,42,0.85);backdrop-filter:blur(4px);color:#fff;border:1px solid rgba(255,255,255,0.2);border-radius:.6rem;font-size:.75rem;font-weight:700;display:inline-flex;align-items:center;gap:.35rem;cursor:pointer;transition:all 0.2s;">
                        🔄 Flip Cam
                    </button>
                    <button type="button" id="jcrTorchBtn" onclick="toggleJcrTorch()"
                            style="padding:.4rem .75rem;background:rgba(15,23,42,0.85);backdrop-filter:blur(4px);color:#fff;border:1px solid rgba(255,255,255,0.2);border-radius:.6rem;font-size:.75rem;font-weight:700;display:inline-flex;align-items:center;gap:.35rem;cursor:pointer;transition:all 0.2s;">
                        💡 Torch
                    </button>
                </div>

                {{-- Fallback File Upload Overlay if Camera Blocked --}}
                <div id="jcrModalFallbackUpload" style="display:none;position:absolute;inset:0;background:rgba(15,23,42,0.95);flex-direction:column;align-items:center;justify-content:center;padding:1.5rem;text-align:center;z-index:20;">
                    <div style="font-size:2.5rem;margin-bottom:0.5rem;">📁</div>
                    <h4 style="color:#fff;font-size:1rem;font-weight:800;margin-bottom:0.25rem;">Camera Blocked or In Use</h4>
                    <p style="color:#94a3b8;font-size:0.75rem;margin-bottom:1.25rem;max-width:280px;">Webcam cannot be opened. You can select a photo directly from your laptop or phone:</p>
                    <label style="padding:.65rem 1.25rem;background:#2563eb;color:#fff;font-weight:800;font-size:.82rem;border-radius:.75rem;cursor:pointer;display:inline-flex;align-items:center;gap:.5rem;box-shadow:0 4px 15px rgba(37,99,235,0.4);">
                        <span>📁 Choose Photo from Files</span>
                        <input type="file" accept="image/*" onchange="handleModalFallbackUpload(event)" style="display:none;">
                    </label>
                    <button type="button" onclick="startJcrCamera()" style="margin-top:1rem;background:none;border:none;color:#f59e0b;font-size:0.75rem;font-weight:700;text-decoration:underline;cursor:pointer;">
                        🔄 Retry Camera Access
                    </button>
                </div>

                {{-- Status Pill --}}
                <div id="jcrCameraStatusPill"
                     style="position:absolute;bottom:12px;left:12px;right:12px;text-align:center;background:rgba(15,23,42,0.85);color:#e2e8f0;font-size:.75rem;font-weight:600;padding:.35rem .75rem;border-radius:999px;border:1px solid rgba(255,255,255,0.15);backdrop-filter:blur(4px);z-index:15;">
                    Initializing camera...
                </div>
            </div>

            {{-- Footer Action Bar --}}
            <div style="padding:1rem 1.25rem;background:#0b1120;border-top:1px solid #1e293b;">
                {{-- Live Mode Controls --}}
                <div id="jcrLiveModeControls" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                    <button type="button" onclick="closeJcrCameraModal()"
                            style="padding:.5rem 1rem;background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:.75rem;font-size:.75rem;font-weight:700;cursor:pointer;">
                        Cancel
                    </button>

                    {{-- Big Shutter Button --}}
                    <button type="button" onclick="snapJcrPhoto()"
                            style="padding:.65rem 1.5rem;background:linear-gradient(135deg, #f59e0b, #d97706);color:#0f172a;border:none;border-radius:999px;font-size:.85rem;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;gap:.5rem;box-shadow:0 0 20px rgba(245,158,11,0.4);transition:all .15s ease;">
                        <span style="font-size:1.1rem;">📸</span> Snap Photo
                    </button>

                    <button type="button" onclick="switchJcrCamera()"
                            style="padding:.5rem 1rem;background:#1e293b;color:#e2e8f0;border:1px solid #334155;border-radius:.75rem;font-size:.75rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;">
                        🔄 Flip
                    </button>
                </div>

                {{-- Review / Confirmation Mode Controls --}}
                <div id="jcrReviewModeControls" style="display:none;align-items:center;justify-content:space-between;gap:1rem;">
                    <button type="button" onclick="retakeJcrPhoto()"
                            style="padding:.55rem 1.25rem;background:#1e293b;color:#cbd5e1;border:1px solid #334155;border-radius:.75rem;font-size:.8rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;">
                        🔄 Retake Photo
                    </button>

                    <button type="button" onclick="confirmJcrPhoto()"
                            style="padding:.55rem 1.5rem;background:linear-gradient(135deg, #10b981, #059669);color:#fff;border:none;border-radius:.75rem;font-size:.8rem;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;box-shadow:0 0 15px rgba(16,185,129,0.35);">
                        ✓ Use This Photo
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Interactive Scripts: Star Rating, Digital Signature, Camera Capture & Photo Manager --}}
    <script>
        // Device Detection Helper
        function isMobileClientDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
                   (window.innerWidth <= 768 && ('ontouchstart' in window));
        }

        // JCR Camera State
        const jcrCameraState = {
            activeSlotId: null,
            stream: null,
            track: null,
            videoDevices: [],
            currentDeviceIndex: 0,
            currentFacingMode: isMobileClientDevice() ? 'environment' : 'user',
            isSwitching: false,
            torchOn: false,
            snappedBlob: null,
            snappedDataUrl: null
        };

        let slotCounter = 2; // Slots 0 and 1 are default

        async function getJcrVideoDevices() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return [];
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                return devices.filter(d => d.kind === 'videoinput');
            } catch (e) {
                return [];
            }
        }

        async function openPhotoCameraModal(slotId, slotTitle) {
            jcrCameraState.activeSlotId = slotId;
            jcrCameraState.snappedBlob = null;
            jcrCameraState.snappedDataUrl = null;
            jcrCameraState.currentFacingMode = isMobileClientDevice() ? 'environment' : 'user';

            const modal = document.getElementById('jcrCameraModal');
            const titleEl = document.getElementById('jcrModalTargetTitle');
            if (titleEl) titleEl.innerText = slotTitle || 'Proof Photo';

            document.getElementById('jcrLiveModeControls').style.display = 'flex';
            document.getElementById('jcrReviewModeControls').style.display = 'none';
            const video = document.getElementById('jcrCameraVideo');
            const snapshotImg = document.getElementById('jcrCameraSnapshotImg');
            const overlay = document.getElementById('jcrViewfinderOverlay');
            const fallbackUpload = document.getElementById('jcrModalFallbackUpload');
            const status = document.getElementById('jcrCameraStatusPill');

            if (video) video.style.display = 'block';
            if (snapshotImg) { snapshotImg.style.display = 'none'; snapshotImg.src = ''; }
            if (overlay) overlay.style.display = 'flex';
            if (fallbackUpload) fallbackUpload.style.display = 'none';
            if (status) status.innerText = 'Connecting to camera...';

            if (modal) modal.style.display = 'flex';

            await startJcrCamera();
        }

        async function closeJcrCameraModal() {
            const modal = document.getElementById('jcrCameraModal');
            if (modal) modal.style.display = 'none';

            if (jcrCameraState.stream) {
                jcrCameraState.stream.getTracks().forEach(t => t.stop());
                jcrCameraState.stream = null;
            }
            jcrCameraState.track = null;
            const video = document.getElementById('jcrCameraVideo');
            if (video) video.srcObject = null;
        }

        async function startJcrCamera(preferDeviceId = null) {
            const video = document.getElementById('jcrCameraVideo');
            const status = document.getElementById('jcrCameraStatusPill');
            const flipBtn = document.getElementById('jcrFlipCamBtn');
            const fallbackUpload = document.getElementById('jcrModalFallbackUpload');

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (status) status.innerText = 'Camera access not supported on this browser.';
                if (fallbackUpload) fallbackUpload.style.display = 'flex';
                return;
            }

            try {
                if (jcrCameraState.stream) {
                    jcrCameraState.stream.getTracks().forEach(t => t.stop());
                    jcrCameraState.stream = null;
                    jcrCameraState.track = null;
                }
                if (video) video.srcObject = null;

                // Brief tick to release OS driver lock
                await new Promise(r => setTimeout(r, 80));

                let stream = null;
                const isMobile = isMobileClientDevice();

                let primaryConstraints;
                if (preferDeviceId) {
                    primaryConstraints = {
                        video: { deviceId: { exact: preferDeviceId }, width: { ideal: 1280 }, height: { ideal: 720 } }
                    };
                } else if (isMobile) {
                    primaryConstraints = {
                        video: {
                            facingMode: { ideal: jcrCameraState.currentFacingMode || 'environment' },
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        }
                    };
                } else {
                    // Laptop/Desktop PC: direct resolution without restrictive facingMode
                    primaryConstraints = {
                        video: { width: { ideal: 1280 }, height: { ideal: 720 } }
                    };
                }

                try {
                    stream = await navigator.mediaDevices.getUserMedia(primaryConstraints);
                } catch (strictErr) {
                    console.warn('Strict constraints failed, falling back to { video: true }...', strictErr);
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({ video: true });
                    } catch (fallbackErr) {
                        throw fallbackErr;
                    }
                }

                jcrCameraState.stream = stream;
                jcrCameraState.track = stream.getVideoTracks()[0];
                jcrCameraState.lastError = null;
                video.srcObject = stream;

                // Ensure video is actively decoding frames
                await new Promise((resolve) => {
                    if (video.videoWidth > 0 && video.readyState >= 2) {
                        resolve();
                    } else {
                        const onReady = () => {
                            video.removeEventListener('loadeddata', onReady);
                            video.removeEventListener('canplay', onReady);
                            resolve();
                        };
                        video.addEventListener('loadeddata', onReady);
                        video.addEventListener('canplay', onReady);
                        setTimeout(resolve, 800);
                    }
                });

                try {
                    await video.play();
                } catch (playErr) {
                    console.warn('Video play error:', playErr);
                }

                if (fallbackUpload) fallbackUpload.style.display = 'none';

                const devices = await getJcrVideoDevices();
                jcrCameraState.videoDevices = devices;
                if (jcrCameraState.track) {
                    const settings = jcrCameraState.track.getSettings ? jcrCameraState.track.getSettings() : {};
                    if (settings.deviceId) {
                        const idx = devices.findIndex(d => d.deviceId === settings.deviceId);
                        if (idx !== -1) jcrCameraState.currentDeviceIndex = idx;
                    }
                }

                const label = (jcrCameraState.track && jcrCameraState.track.label)
                    ? jcrCameraState.track.label
                    : (isMobile ? 'Mobile Camera' : 'Laptop Integrated Camera');
                if (status) {
                    status.innerHTML = `<span style="color:#4ade80;">●</span> Active: ${label.substring(0, 32)}... Ready to snap.`;
                }
            } catch (err) {
                console.warn('Camera stream error:', err);
                jcrCameraState.lastError = err.name || 'Error';
                let errorMsg = '⚠️ Camera blocked or in use. Use "Choose Photo from Files" below.';
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    errorMsg = '🚫 Camera permission blocked! Click 🔒 in address bar and allow camera access.';
                } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                    errorMsg = '⚠️ Camera in use by another app (Zoom/Teams). Please close it and retry.';
                }
                if (status) {
                    status.innerHTML = `<span style="color:#fca5a5;">${errorMsg}</span>`;
                }
                if (fallbackUpload) fallbackUpload.style.display = 'flex';
            } finally {
                jcrCameraState.isSwitching = false;
                if (flipBtn) { flipBtn.disabled = false; flipBtn.style.opacity = '1'; }
            }
        }

        async function switchJcrCamera() {
            if (jcrCameraState.isSwitching) return;
            jcrCameraState.isSwitching = true;
            const flipBtn = document.getElementById('jcrFlipCamBtn');
            const status = document.getElementById('jcrCameraStatusPill');
            if (flipBtn) { flipBtn.disabled = true; flipBtn.style.opacity = '0.5'; }
            if (status) status.innerText = '🔄 Switching camera...';

            // Reset review mode back to live preview if currently showing snapshot
            const video = document.getElementById('jcrCameraVideo');
            const snapshotImg = document.getElementById('jcrCameraSnapshotImg');
            const overlay = document.getElementById('jcrViewfinderOverlay');
            if (snapshotImg) { snapshotImg.style.display = 'none'; snapshotImg.src = ''; }
            if (video) video.style.display = 'block';
            if (overlay) overlay.style.display = 'flex';
            document.getElementById('jcrLiveModeControls').style.display = 'flex';
            document.getElementById('jcrReviewModeControls').style.display = 'none';

            let devices = await getJcrVideoDevices();
            if (!devices || devices.length === 0) devices = jcrCameraState.videoDevices || [];
            else jcrCameraState.videoDevices = devices;

            // Toggle facing mode flag
            jcrCameraState.currentFacingMode = (jcrCameraState.currentFacingMode === 'user') ? 'environment' : 'user';

            if (devices.length > 1) {
                jcrCameraState.currentDeviceIndex = (jcrCameraState.currentDeviceIndex + 1) % devices.length;
                const target = devices[jcrCameraState.currentDeviceIndex];
                await startJcrCamera(target.deviceId);
            } else {
                if (devices.length === 1 && status) {
                    status.innerText = '🔄 Toggling camera mode... (1 physical camera detected)';
                }
                await startJcrCamera(null);
            }
        }

        function toggleJcrTorch() {
            const btn = document.getElementById('jcrTorchBtn');
            if (jcrCameraState.track && jcrCameraState.track.applyConstraints) {
                jcrCameraState.torchOn = !jcrCameraState.torchOn;
                jcrCameraState.track.applyConstraints({
                    advanced: [{ torch: jcrCameraState.torchOn }]
                }).then(() => {
                    if (btn) {
                        btn.style.background = jcrCameraState.torchOn ? '#eab308' : 'rgba(15,23,42,0.85)';
                        btn.style.color = jcrCameraState.torchOn ? '#0f172a' : '#fff';
                    }
                }).catch(() => {
                    jcrCameraState.torchOn = false;
                    alert('Torch / Flashlight not supported on this webcam/device.');
                });
            } else {
                alert('Torch control unavailable on this camera stream.');
            }
        }

        async function snapJcrPhoto() {
            const video = document.getElementById('jcrCameraVideo');
            const canvas = document.getElementById('jcrCameraCanvas');
            const snapshotImg = document.getElementById('jcrCameraSnapshotImg');
            const overlay = document.getElementById('jcrViewfinderOverlay');
            const status = document.getElementById('jcrCameraStatusPill');

            if (!jcrCameraState.stream) {
                if (jcrCameraState.lastError === 'NotAllowedError' || jcrCameraState.lastError === 'PermissionDeniedError') {
                    alert('🚫 Camera Permission is Blocked in your browser!\n\nTo allow it:\n1. Look at the address bar (http://127.0.0.1:8000).\n2. Click the Lock/Tune icon (🔒) on the left.\n3. Change "Camera" permission to "Allow".\n4. Re-open camera or reload the page.\n\nYou can also click "📁 Choose Photo from Files" to upload directly from your computer.');
                } else {
                    alert('⚠️ Camera stream is not connected.\n\nPlease check browser permissions or use "📁 Choose Photo from Files".');
                }
                const fallbackUpload = document.getElementById('jcrModalFallbackUpload');
                if (fallbackUpload) fallbackUpload.style.display = 'flex';
                return;
            }

            if (!video || !canvas) return;

            // Gracefully wait up to 400ms if stream is active but first frame is still decoding
            if (video.videoWidth === 0 || video.readyState < 2) {
                if (status) status.innerText = '⏳ Initializing frame...';
                await new Promise(r => setTimeout(r, 400));
            }

            const width = video.videoWidth || 1280;
            const height = video.videoHeight || 720;
            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, width, height);

            // Audio shutter beep
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(520, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.18, audioCtx.currentTime);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.09);
            } catch (e) {}

            const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
            jcrCameraState.snappedDataUrl = dataUrl;

            canvas.toBlob(blob => {
                jcrCameraState.snappedBlob = blob;
            }, 'image/jpeg', 0.92);

            video.pause();
            video.style.display = 'none';
            if (snapshotImg) {
                snapshotImg.src = dataUrl;
                snapshotImg.style.display = 'block';
            }
            if (overlay) overlay.style.display = 'none';

            document.getElementById('jcrLiveModeControls').style.display = 'none';
            document.getElementById('jcrReviewModeControls').style.display = 'flex';

            if (status) status.innerText = '📸 Photo captured! Review and tap "Use This Photo" or "Retake".';
        }

        function retakeJcrPhoto() {
            const video = document.getElementById('jcrCameraVideo');
            const snapshotImg = document.getElementById('jcrCameraSnapshotImg');
            const overlay = document.getElementById('jcrViewfinderOverlay');
            const status = document.getElementById('jcrCameraStatusPill');

            if (snapshotImg) {
                snapshotImg.style.display = 'none';
                snapshotImg.src = '';
            }
            if (video) {
                video.style.display = 'block';
                video.play();
            }
            if (overlay) overlay.style.display = 'flex';

            document.getElementById('jcrLiveModeControls').style.display = 'flex';
            document.getElementById('jcrReviewModeControls').style.display = 'none';

            if (status) status.innerText = 'Position camera and tap Snap Photo';
        }

        function confirmJcrPhoto() {
            const slotId = jcrCameraState.activeSlotId;
            if (!slotId || !jcrCameraState.snappedBlob) return;

            const slot = document.getElementById(slotId);
            if (!slot) return;

            const fileInput = slot.querySelector('.photo-file-input');
            const filename = 'camera_' + slotId + '_' + Date.now() + '.jpg';
            const file = new File([jcrCameraState.snappedBlob], filename, { type: 'image/jpeg', lastModified: Date.now() });

            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
            } catch (e) {
                console.warn('DataTransfer not available', e);
            }

            // Update Card UI
            renderSlotPreview(slotId, jcrCameraState.snappedDataUrl, file.size);
            closeJcrCameraModal();
        }

        function handleModalFallbackUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            const slotId = jcrCameraState.activeSlotId;
            if (!slotId) return;

            const slot = document.getElementById(slotId);
            if (slot) {
                const mainInput = slot.querySelector('.photo-file-input');
                if (mainInput) {
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        mainInput.files = dt.files;
                    } catch (e) {}
                }
                const reader = new FileReader();
                reader.onload = e => {
                    renderSlotPreview(slotId, e.target.result, file.size);
                    closeJcrCameraModal();
                };
                reader.readAsDataURL(file);
            }
        }

        function handlePhotoFileSelected(input, slotId) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const slot = document.getElementById(slotId);
            if (!slot) return;

            // Sync to main slot input if triggered from change button
            const mainInput = slot.querySelector('.photo-file-input');
            if (mainInput && mainInput !== input) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    mainInput.files = dt.files;
                } catch (e) {}
            }

            const reader = new FileReader();
            reader.onload = e => {
                renderSlotPreview(slotId, e.target.result, file.size);
            };
            reader.readAsDataURL(file);
        }

        function renderSlotPreview(slotId, imageSrc, fileSize) {
            const slot = document.getElementById(slotId);
            if (!slot) return;

            const emptyState = slot.querySelector('.slot-empty-state');
            const filledState = slot.querySelector('.slot-filled-state');
            const previewImg = slot.querySelector('.slot-preview-img');
            const sizeBadge = slot.querySelector('.slot-file-size');

            if (emptyState) emptyState.classList.add('hidden');
            if (filledState) filledState.classList.remove('hidden');
            if (previewImg) previewImg.src = imageSrc;
            if (sizeBadge && fileSize) {
                sizeBadge.innerText = (fileSize / 1024).toFixed(1) + ' KB';
            }
        }

        function removeSlotPhoto(slotId) {
            const slot = document.getElementById(slotId);
            if (!slot) return;

            const mainInput = slot.querySelector('.photo-file-input');
            if (mainInput) mainInput.value = '';

            const emptyState = slot.querySelector('.slot-empty-state');
            const filledState = slot.querySelector('.slot-filled-state');
            const previewImg = slot.querySelector('.slot-preview-img');

            if (filledState) filledState.classList.add('hidden');
            if (emptyState) emptyState.classList.remove('hidden');
            if (previewImg) previewImg.src = '';
        }

        function addNewPhotoSlot() {
            const container = document.getElementById('photoUploadContainer');
            if (!container) return;

            const totalSlots = container.querySelectorAll('.photo-slot-card').length;
            if (totalSlots >= 10) {
                alert('Maximum of 10 proof photos reached.');
                return;
            }

            const newId = 'slot-' + slotCounter;
            const slotIndex = totalSlots + 1;
            slotCounter++;

            const slotDiv = document.createElement('div');
            slotDiv.className = 'photo-slot-card p-4 rounded-xl border border-slate-700/80 transition relative flex flex-col justify-between';
            slotDiv.id = newId;
            slotDiv.style.background = '#060913 !important';
            slotDiv.style.borderColor = '#334155';

            slotDiv.innerHTML = `
                <div class="flex items-center justify-between mb-2.5">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm">📸</span>
                        <span class="slot-title" style="font-size:.8rem;font-weight:800;color:#ffffff !important;">Proof Photo #${slotIndex}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;padding:.2rem .5rem;border-radius:.4rem;background:rgba(139,92,246,0.15);color:#a78bfa !important;border:1px solid rgba(139,92,246,0.3);">Extra Proof</span>
                        <button type="button" onclick="deleteSlot('${newId}')" style="color:#94a3b8;font-size:.85rem;font-weight:bold;cursor:pointer;background:none;border:none;">✕</button>
                    </div>
                </div>

                <div class="slot-empty-state rounded-xl p-4 text-center flex flex-col items-center justify-center" style="border:2px dashed #475569;background:#0b1120;">
                    <div style="width:2.75rem;height:2.75rem;border-radius:.75rem;background:#1e293b;color:#f59e0b;display:flex;align-items:center;justify-content:center;font-size:1.25rem;margin:0 auto .5rem auto;border:1px solid #334155;">
                        📷
                    </div>
                    <div style="font-size:.85rem;font-weight:800;color:#ffffff !important;margin-bottom:.25rem;">Capture or Upload Photo</div>
                    <p style="font-size:.75rem;color:#cbd5e1 !important;margin-bottom:.85rem;">Snap live with webcam/phone camera or choose file.</p>
                    
                    <div class="flex items-center justify-center gap-2.5 flex-wrap w-full">
                        <button type="button" onclick="openPhotoCameraModal('${newId}', 'Proof Photo #${slotIndex}')"
                                style="padding:.55rem 1rem;background:linear-gradient(135deg,#f59e0b,#d97706);color:#020617 !important;font-weight:800;font-size:.78rem;border-radius:.65rem;border:none;display:inline-flex;align-items:center;gap:.45rem;cursor:pointer;box-shadow:0 4px 12px rgba(245,158,11,0.3);text-decoration:none;">
                            <span style="font-size:.95rem;">📷</span>
                            <span style="color:#020617 !important;font-weight:800;display:inline-block;">Take Photo (Camera)</span>
                        </button>
                        <label style="padding:.55rem 1rem;background:#2563eb !important;color:#ffffff !important;font-weight:800;font-size:.78rem;border-radius:.65rem;border:1px solid #1d4ed8;display:inline-flex;align-items:center;gap:.45rem;cursor:pointer;box-shadow:0 4px 12px rgba(37,99,235,0.3);text-decoration:none;">
                            <span style="font-size:.95rem;">📁</span>
                            <span style="color:#ffffff !important;font-weight:800;display:inline-block;">Choose File</span>
                            <input type="file" name="photos[]" accept="image/*" onchange="handlePhotoFileSelected(this, '${newId}')" style="display:none;" class="photo-file-input">
                        </label>
                    </div>
                </div>

                <div class="slot-filled-state hidden flex-col">
                    <div class="w-full h-44 rounded-xl overflow-hidden relative border border-slate-700 bg-black group">
                        <img src="" class="slot-preview-img w-full h-full object-cover">
                        <div class="absolute top-2 left-2 flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/90 text-white font-bold text-[10px] shadow">✓ Photo Attached</span>
                            <span class="slot-file-size px-2 py-0.5 rounded-full bg-slate-900/80 text-slate-300 font-mono text-[10px] border border-slate-700"></span>
                        </div>
                        <div class="absolute bottom-2 inset-x-2 flex items-center justify-center gap-2">
                            <button type="button" onclick="openPhotoCameraModal('${newId}', 'Proof Photo #${slotIndex}')"
                                    style="padding:.35rem .75rem;background:#0f172a !important;color:#f59e0b !important;border:1px solid #f59e0b;border-radius:.5rem;font-size:.72rem;font-weight:800;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;">
                                📷 Retake
                            </button>
                            <label style="padding:.35rem .75rem;background:#0f172a !important;color:#60a5fa !important;border:1px solid #3b82f6;border-radius:.5rem;font-size:.72rem;font-weight:800;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;">
                                📁 Change
                                <input type="file" accept="image/*" onchange="handlePhotoFileSelected(this, '${newId}')" style="display:none;">
                            </label>
                            <button type="button" onclick="removeSlotPhoto('${newId}')"
                                    style="padding:.35rem .75rem;background:#450a0a !important;color:#fca5a5 !important;border:1px solid #ef4444;border-radius:.5rem;font-size:.72rem;font-weight:800;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;">
                                🗑️ Remove
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-2.5">
                    <input type="text" name="photo_captions[]" value="" placeholder="Photo caption (e.g. Cable Pathway / Outdoor View)"
                           style="font-size:.78rem;width:100%;border-radius:.75rem;border:1px solid #334155;background:#0b1120 !important;color:#ffffff !important;padding:.6rem .75rem;margin-top:.4rem;" class="slot-caption">
                    <input type="hidden" name="photo_types[]" value="general" class="slot-type">
                </div>
            `;

            container.appendChild(slotDiv);
        }

        function deleteSlot(slotId) {
            const slot = document.getElementById(slotId);
            if (slot) slot.remove();
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Star rating handler
            const stars = document.querySelectorAll('#starContainer .star');
            const ratingInput = document.getElementById('customer_rating');

            stars.forEach(star => {
                star.addEventListener('click', function () {
                    const rating = parseInt(this.getAttribute('data-value'));
                    ratingInput.value = rating;
                    stars.forEach(s => {
                        const val = parseInt(s.getAttribute('data-value'));
                        s.style.color = val <= rating ? '#f59e0b' : '#334155';
                    });
                });
            });

            // HTML5 Signature Pad Logic
            const canvas = document.getElementById('customerCanvas');
            const ctx = canvas.getContext('2d');
            const placeholder = document.getElementById('sigPlaceholder');
            const clearBtn = document.getElementById('clearCustomerSig');
            const form = document.getElementById('jcrForm');
            const signatureInput = document.getElementById('customerSignatureInput');
            let isDrawing = false;
            let hasDrawn = false;

            // Set canvas stroke style - cyber electric cyan for dark mode signature
            ctx.strokeStyle = '#38bdf8';
            ctx.lineWidth = 3.0;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            function getCoordinates(e) {
                const rect = canvas.getBoundingClientRect();
                const scaleX = canvas.width / rect.width;
                const scaleY = canvas.height / rect.height;

                if (e.touches && e.touches.length > 0) {
                    return {
                        x: (e.touches[0].clientX - rect.left) * scaleX,
                        y: (e.touches[0].clientY - rect.top) * scaleY
                    };
                }
                return {
                    x: (e.clientX - rect.left) * scaleX,
                    y: (e.clientY - rect.top) * scaleY
                };
            }

            function startDraw(e) {
                isDrawing = true;
                placeholder.style.display = 'none';
                const coords = getCoordinates(e);
                ctx.beginPath();
                ctx.moveTo(coords.x, coords.y);
                e.preventDefault();
            }

            function draw(e) {
                if (!isDrawing) return;
                hasDrawn = true;
                const coords = getCoordinates(e);
                ctx.lineTo(coords.x, coords.y);
                ctx.stroke();
                e.preventDefault();
            }

            function stopDraw() {
                isDrawing = false;
            }

            // Mouse events
            canvas.addEventListener('mousedown', startDraw);
            canvas.addEventListener('mousemove', draw);
            window.addEventListener('mouseup', stopDraw);

            // Touch events
            canvas.addEventListener('touchstart', startDraw, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            window.addEventListener('touchend', stopDraw);

            // Clear Button
            clearBtn.addEventListener('click', function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasDrawn = false;
                placeholder.style.display = 'flex';
                signatureInput.value = '';
            });

            // Form Submit Interceptor
            form.addEventListener('submit', function (e) {
                if (!hasDrawn) {
                    e.preventDefault();
                    alert('⚠️ Please ask the customer to sign in the signature box before submitting.');
                    canvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
                // Save data URL
                signatureInput.value = canvas.toDataURL('image/png');
            });
        });
    </script>
</x-app-layout>
