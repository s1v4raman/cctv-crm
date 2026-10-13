<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ auth()->user()->isTechnician() ? route('technician.dashboard') : route('jcr.index') }}" class="back-link">&larr; Back</a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-1">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white font-mono">{{ $jobCompletionReport->report_no }}</h2>
                    <div class="flex items-center gap-2 text-xs mt-1">
                        <span class="status-won">Verified &amp; Signed</span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="text-slate-600 dark:text-slate-300">Signed on {{ $jobCompletionReport->completion_date->format('d M Y, h:i A') }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/[^0-9]/', '', $jobCompletionReport->lead->phone ?? '') }}&text={{ urlencode('Hello ' . ($jobCompletionReport->lead->customer_name ?? 'Customer') . ', here is your signed CCTV Job Completion Certificate: ' . route('jcr.public-pdf', $jobCompletionReport)) }}" target="_blank" class="btn-secondary">
                        WhatsApp
                    </a>
                    <a href="{{ route('jcr.download-pdf', $jobCompletionReport) }}" class="btn-primary">
                        Certificate PDF
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="max-w-[1200px] mx-auto space-y-6">
        @if (session('status'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded text-xs">
                {{ session('status') }}
            </div>
        @endif

        {{-- Section 1: Overview Details (Two-column detail table) --}}
        <div class="crm-section">
            <h3>Handover Report Details</h3>
            <table class="crm-table">
                <tbody>
                    <tr>
                        <td class="w-1/3 text-slate-500 font-bold">Report Number</td>
                        <td class="w-2/3 text-slate-900 dark:text-white font-mono">{{ $jobCompletionReport->report_no }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Customer</td>
                        <td class="text-slate-900 dark:text-white">
                            <a href="{{ route('leads.show', $jobCompletionReport->lead) }}" class="action-link">
                                {{ $jobCompletionReport->lead->customer_name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Site Address</td>
                        <td class="text-slate-900 dark:text-white">{{ $jobCompletionReport->lead->site_address ?? 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Job Reference</td>
                        <td class="text-slate-900 dark:text-white">{{ $jobCompletionReport->job_type_label }} ({{ $jobCompletionReport->reference_no }})</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Field Engineer</td>
                        <td class="text-slate-900 dark:text-white">{{ $jobCompletionReport->technician->name }} ({{ $jobCompletionReport->technician->email }})</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Handover Date</td>
                        <td class="text-slate-900 dark:text-white">{{ $jobCompletionReport->completion_date->format('d M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">QA Checklist Score</td>
                        <td class="text-slate-900 dark:text-white font-bold">{{ $jobCompletionReport->passed_checklist_count }} / 8 Passed ({{ $jobCompletionReport->checklist_percentage }}%)</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Customer Rating</td>
                        <td class="text-slate-900 dark:text-white font-bold">{{ $jobCompletionReport->customer_rating }} / 5</td>
                    </tr>
                    @if($jobCompletionReport->work_summary)
                        <tr>
                            <td class="text-slate-500 font-bold">Work Completed Summary</td>
                            <td class="text-slate-900 dark:text-white whitespace-pre-line">{{ $jobCompletionReport->work_summary }}</td>
                        </tr>
                    @endif
                    @if($jobCompletionReport->customer_feedback)
                        <tr>
                            <td class="text-slate-500 font-bold">Customer Feedback</td>
                            <td class="text-slate-900 dark:text-white whitespace-pre-line">"{{ $jobCompletionReport->customer_feedback }}"</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Section 2: QA Handover Audit Checklist Table (5.3) --}}
        <div class="crm-section">
            <h3>Quality Assurance Handover Audit</h3>
            @php
                $checks = [
                    ['Camera Alignment & View Angles', $jobCompletionReport->all_cameras_positioned],
                    ['Storage & Continuous Recording Test', $jobCompletionReport->recording_configured],
                    ['Client Mobile App Remote View Setup', $jobCompletionReport->remote_mobile_app_setup],
                    ['Power Backup & Voltage Inspection', $jobCompletionReport->power_backup_tested],
                    ['Cable Trunking & Neat Dressing', $jobCompletionReport->cables_dressed_and_trunked],
                    ['Customer Playback & Controls Training', $jobCompletionReport->client_training_completed],
                    ['Installation Work Area Cleaned', $jobCompletionReport->work_area_cleaned],
                    ['Warranty Card & Login Credentials Given', $jobCompletionReport->warranty_card_handed],
                ];
            @endphp
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Quality Standard Check</th>
                        <th class="nowrap">Verification Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checks as $chk)
                    <tr>
                        <td>{{ $chk[0] }}</td>
                        <td class="nowrap font-bold">
                            @if($chk[1])
                                <span class="status-won">Passed</span>
                            @else
                                <span class="text-slate-400">Not Applicable</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Section 3: Photographic Proof (if any) --}}
        @if($jobCompletionReport->photos->count() > 0)
            <div class="crm-section">
                <h3>Handover Photographic Proof</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-2">
                    @foreach($jobCompletionReport->photos as $photo)
                        <div class="border border-slate-300 dark:border-slate-700 rounded p-1 bg-white dark:bg-slate-800">
                            <img src="{{ asset('storage/jcr-photos/' . $photo->filename) }}" alt="Handover Photo" class="w-full h-32 object-cover">
                            @if($photo->caption)
                                <div class="p-1 text-xs text-slate-700 dark:text-slate-300 truncate">{{ $photo->caption }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Section 4: Signatures & Authorizations --}}
        <div class="crm-section">
            <h3>Signatures & Authorizations</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">Customer Signature</h4>
                    <div class="border border-slate-300 dark:border-slate-700 rounded p-4 bg-white flex items-center justify-center min-h-[120px]">
                        <img src="{{ $jobCompletionReport->customer_signature }}" alt="Customer Signature" class="max-h-24 max-w-full object-contain">
                    </div>
                    <div class="mt-2 text-xs text-slate-700 dark:text-slate-300">
                        <strong>{{ $jobCompletionReport->signer_name }}</strong>
                        <div>{{ $jobCompletionReport->signer_designation ?? 'Authorized Signer' }} &bull; {{ $jobCompletionReport->signer_phone ?? '' }}</div>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">Lead Field Engineer</h4>
                    <div class="border border-slate-300 dark:border-slate-700 rounded p-4 bg-white dark:bg-slate-800 flex flex-col items-center justify-center min-h-[120px] text-center">
                        <strong class="text-sm text-slate-900 dark:text-white">{{ $jobCompletionReport->technician->name }}</strong>
                        <span class="text-xs text-slate-500">Certified Field Engineer</span>
                    </div>
                    <div class="mt-2 text-xs text-slate-700 dark:text-slate-300">
                        <div>Job Handover Completed &bull; {{ $jobCompletionReport->completion_date->format('d M Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
