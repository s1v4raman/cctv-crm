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
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Work Summary / Observations</label>
                        <textarea name="work_summary" rows="2" class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5" placeholder="e.g. Installed 4x 5MP Dome Cameras, configured 1TB HDD recording, setup Hik-Connect on 2 mobile devices..."></textarea>
                    </div>
                </div>

                {{-- SECTION 2: Handover Proof Photos --}}
                <div class="bg-[#0f172a] rounded-2xl p-6 shadow-2xl border border-slate-800" style="background-color: #0f172a;">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 font-bold text-xs">2</span>
                        <h4 class="text-base font-extrabold text-white font-heading">Handover Proof Photos (Optional)</h4>
                    </div>
                    <p class="text-xs text-slate-400 mb-3">Attach photos of the DVR rack, camera views, or completion proof.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="photoUploadContainer">
                        <div class="p-4 border-2 border-dashed border-slate-700 hover:border-amber-500/50 rounded-xl bg-[#060913] flex flex-col items-center justify-center text-center transition">
                            <input type="file" name="photos[]" accept="image/*" capture="environment" class="text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-amber-400 hover:file:bg-slate-700 cursor-pointer">
                            <input type="text" name="photo_captions[]" placeholder="Photo caption (e.g. DVR Rack Setup)" class="mt-2.5 text-xs w-full rounded-xl border-slate-700 bg-[#0b1120] text-white p-2">
                            <input type="hidden" name="photo_types[]" value="rack_setup">
                        </div>
                        <div class="p-4 border-2 border-dashed border-slate-700 hover:border-amber-500/50 rounded-xl bg-[#060913] flex flex-col items-center justify-center text-center transition">
                            <input type="file" name="photos[]" accept="image/*" capture="environment" class="text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-amber-400 hover:file:bg-slate-700 cursor-pointer">
                            <input type="text" name="photo_captions[]" placeholder="Photo caption (e.g. Live Monitor View)" class="mt-2.5 text-xs w-full rounded-xl border-slate-700 bg-[#0b1120] text-white p-2">
                            <input type="hidden" name="photo_types[]" value="camera_view">
                        </div>
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
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Customer Comments / Feedback (Optional)</label>
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
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Signer Name <span class="text-amber-400">*</span></label>
                            <input type="text" name="signer_name" value="{{ old('signer_name', $lead?->customer_name) }}" required class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Designation / Role</label>
                            <input type="text" name="signer_designation" value="{{ old('signer_designation') }}" placeholder="e.g. Owner, Store Manager" class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Phone Number</label>
                            <input type="text" name="signer_phone" value="{{ old('signer_phone', $lead?->phone) }}" class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                        </div>
                    </div>

                    {{-- Signature Canvas Box --}}
                    <div class="mb-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold text-white flex items-center gap-1.5 font-heading">
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

    {{-- Interactive Signature Canvas & Star Rating Script --}}
    <script>
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
