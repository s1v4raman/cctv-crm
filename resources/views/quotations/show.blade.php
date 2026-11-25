<x-app-layout>
    @php
        $isTechnician = auth()->user()?->isTechnician();
        $isTechnicianRequest = $quotation->status === 'pending_approval' && ($quotation->creator?->isTechnician() || ($quotation->created_by && $quotation->created_by !== auth()->id()));
        $statusColors = [
            'draft'            => ['badge' => 'border-slate-600 bg-slate-800/80 text-slate-300', 'bar' => '#64748b', 'glow' => 'rgba(100,116,139,0.3)'],
            'pending_approval' => ['badge' => 'border-amber-500/30 bg-amber-500/10 text-amber-500', 'bar' => '#f59e0b', 'glow' => 'rgba(245,158,11,0.3)'],
            'approved'         => ['badge' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400', 'bar' => '#10b981', 'glow' => 'rgba(16,185,129,0.3)'],
            'sent'             => ['badge' => 'border-sky-500/30 bg-sky-500/10 text-sky-400', 'bar' => 'var(--crm-accent, #2563eb)', 'glow' => 'rgba(var(--crm-accent-rgb, 37, 99, 235), 0.3)'],
            'accepted'         => ['badge' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400', 'bar' => '#34d399', 'glow' => 'rgba(52,211,153,0.3)'],
            'rejected'         => ['badge' => 'border-rose-500/30 bg-rose-500/10 text-rose-400', 'bar' => '#f43f5e', 'glow' => 'rgba(244,63,94,0.3)'],
            'expired'          => ['badge' => 'border-amber-500/30 bg-amber-500/10 text-amber-400', 'bar' => '#f59e0b', 'glow' => 'rgba(245,158,11,0.3)'],
        ];
        $sc = $statusColors[$quotation->status] ?? $statusColors['draft'];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <a href="{{ $isTechnician ? route('technician.dashboard') : route('quotations.index') }}" class="back-link">
                    &larr; Back
                </a>
                <div class="flex items-center gap-3 mt-1">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        Quotation {{ $quotation->quotation_no }}
                    </h2>
                    @php
                        $qStatusColor = 'var(--text-muted)';
                        if ($quotation->status === 'pending_approval') $qStatusColor = '#d97706';
                        elseif ($quotation->status === 'approved') $qStatusColor = '#059669';
                        elseif ($quotation->status === 'sent') $qStatusColor = 'var(--link-color)';
                        elseif ($quotation->status === 'accepted') $qStatusColor = 'var(--status-success-fg)';
                        elseif ($quotation->status === 'rejected') $qStatusColor = 'var(--status-danger-fg)';
                        elseif ($quotation->status === 'expired') $qStatusColor = 'var(--status-warning-fg)';
                    @endphp
                    <span class="text-sm font-bold" style="color: {{ $qStatusColor }};">
                        @if($quotation->status === 'pending_approval')
                            Pending Admin Approval
                        @elseif($quotation->status === 'approved')
                            Approved by Management
                        @else
                            {{ ucfirst($quotation->status) }}
                        @endif
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if($quotation->status === 'pending_approval' && $isTechnician)
                    {{-- Technician locked indicator while waiting for admin approval --}}
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs font-semibold shadow-2xs">
                        <svg class="w-4 h-4 text-amber-500 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ __('Awaiting Admin Price Approval (Sharing Locked)') }}</span>
                    </div>
                @else
                    @php
                        $linkedProject = $quotation->project();
                    @endphp

                    {{-- Next Work Action: Convert to Project or View Existing Project --}}
                    @if($linkedProject)
                        <a href="{{ route('projects.show', $linkedProject) }}" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold transition shadow-xs cursor-pointer hover:opacity-95" 
                           style="background-color: #059669 !important; color: #ffffff !important;" 
                           title="Open Project Execution Workspace">
                            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                            <span style="color: #ffffff !important; font-weight: 700;">View Project #{{ $linkedProject->project_code }} &rarr;</span>
                        </a>
                    @else
                        <a href="{{ route('projects.create', ['quotation_id' => $quotation->id, 'lead_id' => $quotation->lead_id]) }}" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold transition shadow-xs cursor-pointer hover:opacity-95" 
                           style="background-color: #2563eb !important; color: #ffffff !important;" 
                           title="Convert this quotation directly into a Project and begin execution">
                            <span style="color: #ffffff !important; font-weight: 700;">Add Project</span>
                        </a>
                    @endif

                    {{-- Action Modals & Links --}}
                    <button type="button" onclick="openWhatsAppModal()" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-bold transition shadow-xs cursor-pointer hover:opacity-95" style="background-color: #059669 !important; color: #ffffff !important;" title="Send Quotation via WhatsApp">
                        <span style="color: #ffffff !important; font-weight: 700;">Send WhatsApp PDF (₹0 Direct)</span>
                    </button>
                    <button type="button" onclick="openEmailModal()" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-bold transition shadow-xs cursor-pointer hover:opacity-95" style="background-color: #0284c7 !important; color: #ffffff !important;" title="Email Quotation with Attached PDF">
                        <span style="color: #ffffff !important; font-weight: 700;">Email Quotation (PDF Attached)</span>
                    </button>
                @endif

                {{-- Save Contact to Phone (Google Contacts Web Auto-Sync) --}}
                <button type="button"
                   onclick="openCustomerGoogleContacts()"
                   class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs font-semibold"
                   title="Save contact directly to Google Contacts to auto-sync with Phone & WhatsApp">
                    <span>Save Contact to Phone</span>
                </button>

                {{-- Admin & Staff Approval Action: Only visible when a field technician submitted a quotation request awaiting approval --}}
                @if($isTechnicianRequest && auth()->user()->isOffice())
                    <form method="POST" action="{{ route('quotations.approve', $quotation) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-primary text-xs cursor-pointer inline-flex items-center gap-1.5 !bg-emerald-600 hover:!bg-emerald-700 !text-white shadow-xs" style="background-color: #059669 !important; color: #ffffff !important;">
                            <svg class="w-3.5 h-3.5 !text-white shrink-0" fill="none" stroke="#ffffff" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('Approve Quotation') }}</span>
                        </button>
                    </form>
                @endif

                @if(in_array($quotation->status, ['draft', 'pending_approval', 'approved', 'sent']) && (auth()->user()->isOffice() || auth()->id() === $quotation->created_by))
                    <a href="{{ route('quotations.edit', $quotation) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs font-semibold">
                        <svg class="w-3.5 h-3.5 text-black dark:text-slate-200 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Edit Scope</span>
                    </a>
                    @if(auth()->user()->isAdmin() && in_array($quotation->status, ['draft', 'pending_approval']))
                    <form method="POST" action="{{ route('quotations.destroy', $quotation) }}"
                          data-confirm="Delete {{ $quotation->quotation_no }}?" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-secondary text-xs cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-rose-600 hover:text-rose-700 hover:border-rose-300 dark:hover:border-rose-700 shadow-2xs transition" title="Delete Quotation">
                            <svg class="w-3.5 h-3.5 text-black dark:text-slate-200 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Delete</span>
                        </button>
                    </form>
                    @endif
                @endif

                @if(in_array($quotation->status, ['sent', 'approved']))
                    <form method="POST" action="{{ route('quotations.accept', $quotation) }}" class="inline">
                        @csrf
                        <button type="submit" data-confirm="Confirm customer accepted?" class="btn-primary text-xs cursor-pointer inline-flex items-center gap-1.5 !bg-emerald-600 hover:!bg-emerald-700 !text-white shadow-2xs" style="background-color: #059669 !important; color: #ffffff !important;">
                            <svg class="w-3.5 h-3.5 !text-white shrink-0" fill="none" stroke="#ffffff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="!text-white font-bold" style="color: #ffffff !important;">Mark Accepted</span>
                        </button>
                    </form>
                    <button type="button" onclick="document.getElementById('reject-modal').classList.add('open')" class="btn-secondary text-xs cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-rose-600 hover:text-rose-700 hover:border-rose-300 dark:hover:border-rose-700 shadow-2xs transition">
                        <svg class="w-3.5 h-3.5 text-black dark:text-slate-200 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Mark Rejected</span>
                    </button>
                @endif

                @if($quotation->status === 'accepted')
                    <a href="{{ route('quotations.procurement-check', $quotation) }}" 
                       class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs font-bold text-amber-700 dark:text-amber-400 border-amber-300 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-amber-950/40"
                       title="{{ __('Inspect Zero & Low Stock Deficit for this Quotation') }}">
                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>{{ __('Stock & Procurement Shortage') }}</span>
                    </a>

                    @if($quotation->installationJob)
                        <a href="{{ route('jobs.show', $quotation->installationJob) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-black dark:text-slate-200 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>View Job</span>
                        </a>
                    @else
                        @if(auth()->user()->isOffice())
                        <form method="POST" action="{{ route('jobs.store', $quotation) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-primary text-xs cursor-pointer inline-flex items-center gap-1.5 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-black dark:text-slate-200 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                <span>Create Job</span>
                            </button>
                        </form>
                        @endif
                    @endif
                @endif
            </div>
        </div>
    </x-slot>

    <style>
        #reject-modal.open, #email-pdf-modal.open, #whatsapp-share-modal.open {
            display: flex !important;
        }
        @media print {
            body { background: #fff !important; color: #000 !important; font-size: 11pt; }
            nav, header, .btn, .btn-remove, #reject-modal, #email-pdf-modal, #whatsapp-share-modal, a[href*="quotations"], a[href*="leads"] {
                display: none !important;
            }
            .crm-section { border: none !important; }
        }
    </style>

    <div class="pg-wrap py-6">
        <div class="pg-inner space-y-6">
            {{-- Guided Workflow Pipeline: Direct Next Step Action Banner --}}
            <x-workflow-stage-banner stage="quotation" :model="$quotation" />
            @php
                $isTechnician = auth()->user()?->isTechnician();
                $isTechnicianRequest = $quotation->status === 'pending_approval' && ($quotation->creator?->isTechnician() || ($quotation->created_by && $quotation->created_by !== auth()->id()));
            @endphp

            @if (session('status'))
                <div class="p-3 rounded border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-3 rounded border border-rose-300 bg-rose-50 text-rose-800 text-xs">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Pending Approval Alert Banner: ONLY displayed when a field technician submitted a quotation request awaiting Admin review & approval --}}
            @if($isTechnicianRequest)
                @if(!$isTechnician)
                    <div class="p-4 sm:p-5 rounded-2xl border border-amber-300 dark:border-amber-800 bg-amber-50/90 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200 text-xs shadow-xs space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <strong class="font-bold text-sm text-slate-900 dark:text-white">{{ __('Field Quotation Awaiting Admin Review & Approval') }}</strong>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-200/80 dark:bg-amber-900/80 text-amber-900 dark:text-amber-200">
                                        {{ __('Proposed Layout: Format :fmt (:name)', ['fmt' => $quotation->pdf_format ?? '1', 'name' => $quotation->pdf_format_name]) }}
                                    </span>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                    {{ __('Submitted on-site by') }} <strong class="text-slate-800 dark:text-slate-100">{{ $quotation->creator->name ?? __('Technician') }}</strong>. {{ __('Review the equipment scope and pricing below. Confirm or update the PDF format before approving.') }}
                                </p>
                            </div>
                        </div>

                        @if(auth()->user()->isOffice())
                            <form method="POST" action="{{ route('quotations.approve', $quotation) }}" class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-slate-900/90 border border-amber-200 dark:border-amber-800/80 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                @csrf
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 flex-wrap">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-xs text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('Confirm PDF Format:') }}</span>
                                        <div class="inline-flex items-center p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 gap-1">
                                            @foreach(\App\Models\Quotation::FORMATS as $key => $title)
                                                @php $isFmtSelected = ($quotation->pdf_format ?? '1') == $key; @endphp
                                                <label class="px-2.5 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition-all flex items-center gap-1.5 {{ $isFmtSelected ? 'bg-white dark:bg-slate-900 text-emerald-700 dark:text-emerald-400 shadow-xs border border-emerald-500/40' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                                    <div class="w-3.5 h-3.5 rounded-full border-2 flex items-center justify-center shrink-0 {{ $isFmtSelected ? 'border-emerald-600 bg-emerald-600' : 'border-slate-400 dark:border-slate-500 bg-transparent' }}">
                                                        @if($isFmtSelected)
                                                            <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                                        @endif
                                                    </div>
                                                    <input type="radio" name="pdf_format" value="{{ $key }}" {{ $isFmtSelected ? 'checked' : '' }} class="sr-only">
                                                    <span>F{{ $key }}: {{ $title }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-xs text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('Pricing Mode:') }}</span>
                                        <div class="inline-flex items-center p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 gap-1">
                                            <label class="px-2.5 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition-all flex items-center gap-1.5 {{ !$quotation->isTotalCostOnly() ? 'bg-white dark:bg-slate-900 text-blue-700 dark:text-blue-400 shadow-xs border border-blue-500/40' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                                <div class="w-3.5 h-3.5 rounded-full border-2 flex items-center justify-center shrink-0 {{ !$quotation->isTotalCostOnly() ? 'border-blue-600 bg-blue-600' : 'border-slate-400 dark:border-slate-500 bg-transparent' }}">
                                                    @if(!$quotation->isTotalCostOnly())
                                                        <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                                    @endif
                                                </div>
                                                <input type="radio" name="pricing_mode" value="itemized" {{ !$quotation->isTotalCostOnly() ? 'checked' : '' }} class="sr-only">
                                                <span>With Product Cost</span>
                                            </label>
                                            <label class="px-2.5 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition-all flex items-center gap-1.5 {{ $quotation->isTotalCostOnly() ? 'bg-white dark:bg-slate-900 text-purple-700 dark:text-purple-400 shadow-xs border border-purple-500/40' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                                <div class="w-3.5 h-3.5 rounded-full border-2 flex items-center justify-center shrink-0 {{ $quotation->isTotalCostOnly() ? 'border-purple-600 bg-purple-600' : 'border-slate-400 dark:border-slate-500 bg-transparent' }}">
                                                    @if($quotation->isTotalCostOnly())
                                                        <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                                    @endif
                                                </div>
                                                <input type="radio" name="pricing_mode" value="total_only" {{ $quotation->isTotalCostOnly() ? 'checked' : '' }} class="sr-only">
                                                <span>Total Cost Only</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] !text-white font-bold text-xs sm:text-sm inline-flex items-center justify-center gap-2 shadow-sm transition cursor-pointer shrink-0" style="color: #ffffff !important; background-color: #059669 !important;">
                                    <svg class="w-4 h-4 !text-white shrink-0" fill="none" stroke="#ffffff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('Approve Format & Quotation') }}</span>
                                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="#ffffff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    {{-- Technician View while waiting for office approval --}}
                    <div class="p-4 rounded-2xl border border-amber-300 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200 text-xs flex items-start gap-3 shadow-xs">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <strong class="font-bold text-sm text-slate-900 dark:text-white">{{ __('Submitted for Office Review & Pricing') }}</strong>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-200/60 dark:bg-amber-900/80 text-amber-900 dark:text-amber-200">
                                    {{ __('Proposed Format: :fmt (:name)', ['fmt' => $quotation->pdf_format ?? '1', 'name' => $quotation->pdf_format_name]) }}
                                </span>
                            </div>
                            <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                                {{ __('Your equipment list and proposed format have been submitted to office management. As soon as admin reviews and approves the quotation, WhatsApp and Email dispatch will automatically unlock.') }}
                            </p>
                        </div>
                    </div>
                @endif
            @endif

            @if($quotation->status === 'rejected' && $quotation->rejection_reason)
                <div class="crm-section border-rose-300 bg-rose-50 text-rose-800 text-xs">
                    <p class="font-bold mb-1">Rejection Reason</p>
                    <p>{{ $quotation->rejection_reason }}</p>
                </div>
            @endif

            {{-- Method Button Strip for Selecting Layout Design & Document Type (Matching User Screenshot Spec) --}}
            @php
                $activeFmt = $quotation->pdf_format ?? '1';
                $activePricing = $quotation->pricing_mode ?? 'itemized';
                $activeDocType = $quotation->doc_type ?? 'quotation';
                $activeOptionKey = $activeDocType === 'invoice' ? 'tax_invoice' : ($activePricing === 'total_only' ? 'total_only' : 'itemized');
            @endphp
            <div class="crm-section p-3.5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/90 dark:border-slate-700 shadow-xs space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        {{-- 1. Layout Company Selector --}}
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>{{ __('Layout:') }}</span>
                            </span>
                            <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-700/60 rounded-xl border border-slate-200 dark:border-slate-600 text-xs font-semibold shadow-inner gap-1">
                                <button type="button" 
                                        onclick="setShowLayoutFormat('1')" 
                                        id="show-tab-fmt-1"
                                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer {{ $activeFmt == '1' ? 'bg-[#ca8a04] hover:bg-[#b45309] text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-600/60' }}"
                                        title="Format 1: Precision IT Systems (Executive Modern)">
                                    <span class="tab-indicator w-2 h-2 rounded-full bg-black dark:bg-white shrink-0 {{ $activeFmt == '1' ? '' : 'hidden' }}"></span>
                                    <span>Format 1 (Precision)</span>
                                </button>
                                <button type="button" 
                                        onclick="setShowLayoutFormat('2')" 
                                        id="show-tab-fmt-2"
                                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer {{ $activeFmt == '2' ? 'bg-[#ca8a04] hover:bg-[#b45309] text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-600/60' }}"
                                        title="Format 2: NP Solutions (Technical Detailed)">
                                    <span class="tab-indicator w-2 h-2 rounded-full bg-black dark:bg-white shrink-0 {{ $activeFmt == '2' ? '' : 'hidden' }}"></span>
                                    <span>Format 2 (NP Solutions)</span>
                                </button>
                                <button type="button" 
                                        onclick="setShowLayoutFormat('3')" 
                                        id="show-tab-fmt-3"
                                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer {{ $activeFmt == '3' ? 'bg-[#ca8a04] hover:bg-[#b45309] text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-600/60' }}"
                                        title="Format 3: Linepix GST Invoice">
                                    <span class="tab-indicator w-2 h-2 rounded-full bg-black dark:bg-white shrink-0 {{ $activeFmt == '3' ? '' : 'hidden' }}"></span>
                                    <span>Format 3 (Linepix)</span>
                                </button>
                            </div>
                        </div>

                        <div class="h-6 w-[1px] bg-slate-300 dark:bg-slate-600 hidden md:block"></div>

                        {{-- 2. Document Type / Pricing Mode Selector --}}
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ __('Pricing Mode:') }}</span>
                            </span>
                            <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-700/60 rounded-xl border border-slate-200 dark:border-slate-600 text-xs font-semibold shadow-inner gap-1">
                                <button type="button" 
                                        onclick="setShowDocOption('quotation', 'itemized')" 
                                        id="show-tab-mode-itemized"
                                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer {{ $activeOptionKey === 'itemized' ? 'bg-[#ca8a04] hover:bg-[#b45309] text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-600/60' }}"
                                        title="{{ __('PR Quotation with itemized product costs & totals') }}">
                                    <span class="tab-indicator w-2 h-2 rounded-full bg-black dark:bg-white shrink-0 {{ $activeOptionKey === 'itemized' ? '' : 'hidden' }}"></span>
                                    <span>With Product Cost & Total</span>
                                </button>
                                <button type="button" 
                                        onclick="setShowDocOption('quotation', 'total_only')" 
                                        id="show-tab-mode-total_only"
                                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer {{ $activeOptionKey === 'total_only' ? 'bg-[#ca8a04] hover:bg-[#b45309] text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-600/60' }}"
                                        title="{{ __('PR Quotation with individual prices hidden; turnkey grand total only') }}">
                                    <span class="tab-indicator w-2 h-2 rounded-full bg-black dark:bg-white shrink-0 {{ $activeOptionKey === 'total_only' ? '' : 'hidden' }}"></span>
                                    <span>Total Cost Only</span>
                                </button>
                                <button type="button" 
                                        onclick="setShowDocOption('invoice', 'itemized')" 
                                        id="show-tab-mode-tax_invoice"
                                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer {{ $activeOptionKey === 'tax_invoice' ? 'bg-[#ca8a04] hover:bg-[#b45309] text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-600/60' }}"
                                        title="{{ __('Official Tax Invoice matching sample layout with HSN, GST breakdown & bank details') }}">
                                    <span class="tab-indicator w-2 h-2 rounded-full bg-black dark:bg-white shrink-0 {{ $activeOptionKey === 'tax_invoice' ? '' : 'hidden' }}"></span>
                                    <span>Tax Invoice</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Action Preview & Download Buttons --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <a id="show-preview-btn" 
                           href="{{ route('quotations.preview', [$quotation, $activeFmt, 'pricing_mode' => $activePricing, 'doc_type' => $activeDocType]) }}" 
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition cursor-pointer"
                           title="Open live preview in selected layout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span id="show-preview-btn-text">{{ $activeDocType === 'invoice' ? __('Preview Tax Invoice') : __('Preview Quotation') }}</span>
                        </a>

                        <a id="show-download-btn" 
                           href="{{ route('quotations.download-pdf', [$quotation, $activeFmt, 'pricing_mode' => $activePricing, 'doc_type' => $activeDocType]) }}" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 shadow-2xs transition"
                           title="Download PDF directly in active format">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>{{ __('Download PDF') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 1. Specifications & Overview: Two-column table inside standard section --}}
            <div class="crm-section">
                <h3>Specifications &amp; Overview</h3>
                <div class="overflow-x-auto">
                    <table class="crm-table w-full">
                        <tbody>
                            <tr>
                                <td class="w-2/5 sm:w-1/4" style="color: var(--text-muted); font-weight: 500;">Quotation Identifier</td>
                                <td style="color: var(--text-main); font-weight: 500;" class="break-words">{{ $quotation->quotation_no }}</td>
                            </tr>
                            <tr>
                                <td class="w-2/5 sm:w-1/4" style="color: var(--text-muted); font-weight: 500;">Date Issued</td>
                                <td style="color: var(--text-main); font-weight: 500;">{{ $quotation->quotation_date?->format('d M Y') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="w-2/5 sm:w-1/4" style="color: var(--text-muted); font-weight: 500;">Valid Until</td>
                                <td style="color: var(--text-main); font-weight: 500;">{{ $quotation->valid_until?->format('d M Y') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="w-2/5 sm:w-1/4" style="color: var(--text-muted); font-weight: 500;">Customer</td>
                                <td style="color: var(--text-main); font-weight: 500;" class="break-words">
                                    <a href="{{ route('leads.show', $quotation->lead) }}" class="action-link">
                                        {{ $quotation->lead->customer_name }}
                                    </a>
                                </td>
                            </tr>
                            @if($quotation->lead->site_address)
                            <tr>
                                <td class="w-2/5 sm:w-1/4" style="color: var(--text-muted); font-weight: 500;">Installation Facility Address</td>
                                <td style="color: var(--text-main); font-weight: 500;" class="break-words">{{ $quotation->lead->site_address }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. Line Items Table & Pricing Management --}}
            <div class="crm-section">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="!mb-0">Line Items ({{ $quotation->items->count() }})</h3>
                            @if($quotation->isTotalCostOnly())
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                    Customer Mode: Total Cost Only
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                    Customer Mode: With Product Cost
                                </span>
                            @endif
                        </div>
                        @if(!$isTechnician)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ __('Update unit prices and quantities below. Zero-cost items added by technicians are highlighted for review.') }}
                            </p>
                        @else
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ __('Equipment and hardware scope for site installation.') }}
                            </p>
                        @endif
                        @if($quotation->isTotalCostOnly())
                            <div class="mt-2 p-2 rounded-lg bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/80 text-[11px] text-purple-900 dark:text-purple-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span><strong>Lump Sum Mode Active:</strong> Customer-facing PDF, WhatsApp and Email present a consolidated Turnkey CCTV Solution package and grand total (₹{{ number_format($quotation->total, 2) }}). Line item prices are hidden from the client to prevent parts price-shopping.</span>
                            </div>
                        @endif
                    </div>
                    @if(!$isTechnician && in_array($quotation->status, ['draft', 'pending_approval', 'approved', 'sent']))
                        <div class="flex items-center gap-2">
                            <a href="{{ route('quotations.edit', $quotation) }}" class="btn-primary text-xs font-bold px-4 py-2 rounded-xl shadow-xs" style="background-color: #2563eb !important; color: #ffffff !important;">
                                <span>{{ __('Edit') }}</span>
                            </a>
                        </div>
                    @endif
                </div>

                @if(!$isTechnician && in_array($quotation->status, ['draft', 'pending_approval', 'approved', 'sent']))
                    {{-- Admin quick pricing & quantity editor form --}}
                    <form method="POST" action="{{ route('quotations.update-pricing', $quotation) }}" id="quotation-pricing-form">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="crm-table w-full">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item / Product</th>
                                        <th>Description</th>
                                        <th>HSN/SAC</th>
                                        <th class="text-right">Qty</th>
                                        <th>Unit</th>
                                        <th class="text-right">Unit Price (₹)</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($quotation->items as $i => $item)
                                        @php
                                            $resolvedHsn = $item->hsn_code ?: ($item->product?->hsn_code ?: $item->resolveHsnCode(true));
                                            $rawHsn = $item->hsn_code ?: ($item->product?->hsn_code ?? '');
                                        @endphp
                                        <tr class="{{ (float)$item->unit_price == 0 ? 'bg-amber-50/50 dark:bg-amber-950/20' : '' }}" id="quotation-item-row-{{ $item->id }}">
                                            <td>{{ $i + 1 }}</td>
                                            <td class="font-bold text-slate-900 dark:text-white">
                                                <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                                                {{ $item->item_name }}
                                            </td>
                                            <td class="text-xs text-slate-600 dark:text-slate-300">{{ $item->description ?? '—' }}</td>
                                            <td style="white-space: nowrap;">
                                                @if(auth()->user()->isAdmin())
                                                    <div class="inline-flex items-center gap-1.5">
                                                        <input type="text"
                                                               name="items[{{ $i }}][hsn_code]"
                                                               value="{{ $rawHsn }}"
                                                               maxlength="8"
                                                               pattern="[0-9]{2,8}"
                                                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)"
                                                               placeholder="{{ $resolvedHsn }}"
                                                               class="w-24 px-2 py-1 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-mono font-semibold focus:border-blue-500 focus:outline-none"
                                                               title="HSN/SAC Code (Max 8 digits)">
                                                        <button type="button"
                                                                onclick="openHsnEditModal({{ $item->id }}, '{{ addslashes($item->item_name) }}', '{{ $rawHsn }}', {{ $item->product_id ? 'true' : 'false' }})"
                                                                class="p-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900/40 rounded transition cursor-pointer"
                                                                title="Manage HSN/SAC Code">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                                        </button>
                                                        @if(!empty($rawHsn))
                                                            <button type="button"
                                                                    onclick="confirmDeleteHsn({{ $item->id }}, '{{ addslashes($item->item_name) }}')"
                                                                    class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-900/40 rounded transition cursor-pointer"
                                                                    title="Delete HSN/SAC Code">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @else
                                                    {{-- Employee & Technician: Read-only badge --}}
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                        {{ $resolvedHsn ?: '—' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <input type="number" step="0.01" min="0.01" name="items[{{ $i }}][quantity]" value="{{ $item->quantity }}" class="w-20 text-right px-2 py-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold focus:border-blue-500 focus:outline-none">
                                            </td>
                                            <td>{{ $item->unit ?? '—' }}</td>
                                            <td class="text-right" style="white-space: nowrap;">
                                                <div class="inline-flex items-center gap-1 justify-end">
                                                    <span class="text-slate-400 font-bold">₹</span>
                                                    <input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_price]" value="{{ $item->unit_price }}" class="w-28 text-right px-2 py-1 rounded-lg border {{ (float)$item->unit_price == 0 ? 'border-amber-500 ring-2 ring-amber-400 bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200' : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white' }} text-xs font-bold focus:border-blue-500 focus:outline-none">
                                                </div>
                                                @if((float)$item->unit_price == 0)
                                                    <span class="block text-[10px] text-amber-600 dark:text-amber-400 font-bold text-right mt-0.5">⚠️ Price Required</span>
                                                @endif
                                            </td>
                                            <td class="text-right font-bold text-slate-900 dark:text-white" style="white-space: nowrap;">₹{{ number_format((float)$item->total, 2) }}</td>
                                            <td class="text-right">
                                                <button type="submit" form="delete-item-{{ $item->id }}" class="danger-link text-xs cursor-pointer">Remove</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-6 text-slate-500 text-sm">
                                                No items on this quotation yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Totals Summary & Quick Update Footer --}}
                        <div class="mt-4 pt-4 border-t border-[#ddd] flex flex-col md:flex-row justify-between gap-6 text-sm">
                            <div class="flex-1 space-y-3">
                                @if($quotation->notes)
                                    <div class="p-3 rounded border border-[#ddd] bg-[#f9f9f9] text-xs text-slate-700 leading-relaxed">
                                        <span class="font-bold block mb-1">Notes &amp; Scope:</span>
                                        {{ $quotation->notes }}
                                    </div>
                                @endif
                                <div class="flex items-center gap-3 pt-2">
                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-[0.98] !text-white font-bold text-xs sm:text-sm inline-flex items-center gap-2 shadow-sm transition cursor-pointer" style="background-color: #2563eb !important; color: #ffffff !important;">
                                        <svg class="w-4 h-4 !text-white" fill="none" stroke="#ffffff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        <span class="!text-white font-bold">{{ __('Update Prices & Recalculate') }}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="w-full md:w-80 space-y-2.5 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/80">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-500 dark:text-slate-400 font-semibold">Subtotal</span>
                                    <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format((float)$quotation->subtotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <label class="text-slate-500 dark:text-slate-400 font-semibold">Discount (₹)</label>
                                    <input type="number" step="0.01" min="0" name="discount" value="{{ $quotation->discount }}" class="w-28 text-right px-2 py-1 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                                </div>
                                <div class="flex justify-between items-center text-xs pb-2 border-b border-slate-200 dark:border-slate-700">
                                    <label class="text-slate-500 dark:text-slate-400 font-semibold">GST (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="tax_percent" value="{{ $quotation->tax_percent }}" class="w-20 text-right px-2 py-1 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                                </div>
                                <div class="flex justify-between items-center text-sm font-black text-slate-900 dark:text-white pt-1">
                                    <span>Grand Total</span>
                                    <span class="text-base text-blue-600 dark:text-blue-400">₹{{ number_format((float)$quotation->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Hidden individual delete item forms to avoid form nesting --}}
                    @foreach ($quotation->items as $item)
                        <form id="delete-item-{{ $item->id }}" method="POST" action="{{ route('quotation-items.destroy', $item) }}" data-confirm="Remove '{{ $item->item_name }}' from quotation?" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @endforeach

                @else
                    {{-- Standard view for Technicians and Completed/Accepted Quotes --}}
                    @php
                        $showPricesToViewer = true;
                    @endphp
                    <div class="overflow-x-auto">
                        <table class="crm-table w-full">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item / Product</th>
                                    <th>Description</th>
                                    <th>HSN/SAC</th>
                                    <th class="text-right">Qty</th>
                                    <th>Unit</th>
                                    @if($showPricesToViewer)
                                        <th class="text-right">Unit Price</th>
                                        <th class="text-right">Subtotal</th>
                                    @endif
                                    @if(!$isTechnician && in_array($quotation->status, ['draft', 'pending_approval', 'approved', 'sent']))
                                        <th class="text-right">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($quotation->items as $i => $item)
                                    @php
                                        $resolvedHsn = $item->hsn_code ?: ($item->product?->hsn_code ?: $item->resolveHsnCode(true));
                                    @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td class="font-bold text-slate-900 dark:text-white">{{ $item->item_name }}</td>
                                        <td>{{ $item->description ?? '—' }}</td>
                                        <td>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                {{ $resolvedHsn ?: '—' }}
                                            </span>
                                        </td>
                                        <td class="text-right font-medium">{{ $item->quantity }}</td>
                                        <td>{{ $item->unit ?? '—' }}</td>
                                        @if($showPricesToViewer)
                                            <td class="text-right" style="white-space: nowrap;">₹{{ number_format((float)$item->unit_price, 2) }}</td>
                                            <td class="text-right font-bold text-slate-900 dark:text-white" style="white-space: nowrap;">₹{{ number_format((float)$item->total, 2) }}</td>
                                        @endif
                                        @if(!$isTechnician && in_array($quotation->status, ['draft', 'pending_approval', 'approved', 'sent']))
                                            <td class="text-right">
                                                <form method="POST" action="{{ route('quotation-items.destroy', $item) }}"
                                                      data-confirm="Remove this item?" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="danger-link text-xs cursor-pointer">Remove</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showPricesToViewer ? 9 : 7 }}" class="text-center py-6 text-slate-500 text-sm">
                                            No items on this quotation yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals Summary --}}
                    <div class="mt-4 pt-4 border-t border-[#ddd] flex flex-col md:flex-row justify-between gap-6 text-sm">
                        <div class="flex-1">
                            @if($quotation->notes)
                                <div class="p-3 rounded border border-[#ddd] bg-[#f9f9f9] text-xs text-slate-700 leading-relaxed">
                                    <span class="font-bold block mb-1">Notes &amp; Scope:</span>
                                    {{ $quotation->notes }}
                                </div>
                            @endif
                        </div>
                        @if($showPricesToViewer)
                        <div class="w-full md:w-72 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Subtotal</span>
                                <span class="font-medium text-slate-900">₹{{ number_format((float)$quotation->subtotal, 2) }}</span>
                            </div>
                            @if($quotation->discount > 0)
                            <div class="flex justify-between text-red-600">
                                <span>Discount</span>
                                <span class="font-medium">−₹{{ number_format((float)$quotation->discount, 2) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between pb-2 border-b border-[#ddd]">
                                <span class="text-slate-500">GST ({{ $quotation->tax_percent }}%)</span>
                                <span class="font-medium text-slate-900">₹{{ number_format((float)$quotation->tax_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-base font-bold text-slate-900">
                                <span>Grand Total</span>
                                <span>₹{{ number_format((float)$quotation->total, 2) }}</span>
                            </div>
                            @if($isTechnician && in_array($quotation->status, ['approved', 'sent']))
                                <div class="pt-2">
                                    <div class="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-[11px] text-emerald-800 dark:text-emerald-300 font-bold flex items-center gap-1.5">
                                        <span>✅</span>
                                        <span>{{ __('Approved by Admin. Ready to share quotation with customer.') }}</span>
                                    </div>
                                </div>
                            @elseif($isTechnician && $quotation->status === 'pending_approval')
                                <div class="pt-2">
                                    <div class="p-2.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-[11px] text-amber-800 dark:text-amber-300 font-semibold flex items-center gap-1.5">
                                        <span>⏳</span>
                                        <span>{{ __('Quotation recorded with on-site pricing. Awaiting final Admin review before customer sharing.') }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- 3. Audit Trail --}}
            <div class="crm-section">
                <h3>Audit Trail &amp; Lifecycle History</h3>
                <div class="overflow-x-auto">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th>Status Event</th>
                                <th>Remarks</th>
                                <th>Changed By</th>
                                <th class="text-right">Date &amp; Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($quotation->statusHistories as $h)
                                <tr>
                                    <td class="font-medium text-slate-900">
                                        {{ $h->old_status ? ucfirst($h->old_status) . ' → ' : 'Created as ' }}{{ ucfirst($h->new_status) }}
                                    </td>
                                    <td>{{ $h->remarks ?: '—' }}</td>
                                    <td style="white-space: nowrap;">{{ $h->changedBy->name ?? 'System' }}</td>
                                    <td class="text-right" style="white-space: nowrap;">{{ $h->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-6 text-slate-500 text-sm">
                                        No status changes recorded yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="reject-modal" style="display:none;position:fixed;inset:0;z-index:50;background:rgba(6,9,19,0.8);backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:1rem;">
        <div class="bg-[#0F172A] border border-rose-500/30 rounded-2xl p-6 max-w-md w-full shadow-2xl">
            <h4 class="text-lg font-bold text-white uppercase font-heading mb-1">Reject Quotation</h4>
            <p class="text-xs text-slate-400 mb-4">Please provide the reason why the customer rejected this quotation.</p>
            <form method="POST" action="{{ route('quotations.reject', $quotation) }}">
                @csrf
                <textarea aria-label="Rejection reason" name="rejection_reason" class="w-full bg-[#060913] border border-slate-700 rounded-xl p-3 text-sm text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none min-h-[100px] mb-4" required
                          placeholder="e.g. Price too high, customer chose another vendor…"></textarea>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('reject-modal').classList.remove('open')"
                            class="btn-md min-h-[44px] px-5 py-2.5 rounded-xl border border-slate-700 bg-slate-800 text-slate-300 text-sm font-semibold hover:bg-slate-700 transition">Cancel</button>
                    <button type="submit" class="btn-md min-h-[44px] px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-bold transition">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Send PDF via System Email App & SMTP Modal --}}
    <div id="email-pdf-modal" style="display:none;"
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full my-auto flex flex-col max-h-[90vh] overflow-hidden text-slate-800 dark:text-slate-200">
            
            {{-- Fixed Modal Header (Never cut off or hidden) --}}
            <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0 bg-white dark:bg-slate-900">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-tight">{{ __('Email Quotation to Customer') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Send quotation proposal via your system email app (Outlook, Windows Mail) or server') }}</p>
                    </div>
                </div>
                <x-close-button onclick="closeEmailModal()" />
            </div>

            {{-- Scrollable Form Content Body --}}
            <div class="p-4 sm:p-6 overflow-y-auto space-y-4 flex-1">
                
                {{-- Live Attached PDF Status Card --}}
                <div class="p-4 rounded-xl bg-gradient-to-r from-blue-50/90 to-indigo-50/90 dark:from-blue-950/40 dark:to-indigo-950/40 border border-blue-200 dark:border-blue-800/80 flex items-start justify-between gap-3 flex-wrap">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0 mt-0.5">
                            PDF
                        </div>
                        <div class="text-xs space-y-0.5">
                            <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                                <span id="email-pdf-attached-name">Quotation_{{ $quotation->quotation_no }}.pdf</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                    {{ __('Auto-Attached') }}
                                </span>
                            </div>
                            <div class="text-slate-600 dark:text-slate-300" id="email-pdf-attached-desc">
                                Format: <strong class="text-blue-600 dark:text-blue-400" id="email-active-format-label">{{ $quotation->pdf_format_name }} (Format {{ $quotation->pdf_format ?? '1' }})</strong> &bull; Total: <strong>₹{{ number_format((float)$quotation->total, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0 text-xs">
                        <a id="btn-email-preview-pdf" href="{{ route('quotations.preview', [$quotation, $quotation->pdf_format ?: '1']) }}"
                           class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold border border-slate-300 dark:border-slate-600 shadow-xs inline-flex items-center gap-1 transition" title="Preview Live Webpage">
                            <span>👁️ {{ __('Preview') }}</span>
                        </a>
                        <button type="button" onclick="downloadEmailModalPdf()"
                                class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold border border-slate-300 dark:border-slate-600 shadow-xs inline-flex items-center gap-1 transition cursor-pointer" title="Download PDF to computer">
                            <span>⬇️ {{ __('Download') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Recipient Customer Email --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5 shrink-0 inline-block mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                            {{ __('Recipient Customer Email') }} <span class="text-rose-500">*</span>
                        </label>
                        <button type="button" onclick="copyModalRecipientEmail(this)" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 cursor-pointer">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span>{{ __('Copy Email') }}</span>
                        </button>
                    </div>
                    <input type="email" id="email-modal-recipient" name="email" required 
                           value="{{ old('email', $quotation->lead?->email) }}" 
                           placeholder="customer@gmail.com" 
                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>

                {{-- Subject Line --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0 inline-block mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        {{ __('Subject Line') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="email-modal-subject" name="subject" required 
                           value="{{ $quotation->getEmailSubject() }}" 
                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>

                {{-- Quotation Format & Pricing Method Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            <svg class="w-3.5 h-3.5 shrink-0 inline-block mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ __('PDF Template Style') }}
                        </label>
                        <select id="email-modal-format" name="pdf_format" onchange="onEmailModalOptionsChange()"
                                class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="1" {{ ($quotation->pdf_format ?? '1') == '1' ? 'selected' : '' }}>Executive Modern (Format 1)</option>
                            <option value="2" {{ ($quotation->pdf_format ?? '1') == '2' ? 'selected' : '' }}>Technical Detailed (Format 2)</option>
                            <option value="3" {{ ($quotation->pdf_format ?? '1') == '3' ? 'selected' : '' }}>Linepix GST Invoice (Format 3)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            <svg class="w-3.5 h-3.5 shrink-0 inline-block mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ __('Pricing Breakdown') }}
                        </label>
                        <select id="email-modal-pricing" name="pricing_mode" onchange="onEmailModalOptionsChange()"
                                class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="itemized" {{ ($quotation->pricing_mode ?? 'itemized') == 'itemized' ? 'selected' : '' }}>With Product Cost &amp; Total (Itemized)</option>
                            <option value="total_only" {{ ($quotation->pricing_mode ?? 'itemized') == 'total_only' ? 'selected' : '' }}>Total Cost Only (Turnkey Package)</option>
                        </select>
                    </div>
                </div>

                {{-- Custom Cover Note --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0 inline-block mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        {{ __('Custom Cover Note for Customer (Optional)') }}
                    </label>
                    <textarea id="email-modal-custom-note" rows="2" 
                              placeholder="{{ __('e.g. As discussed during our site survey, includes 2-year warranty and free installation...') }}" 
                              class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"></textarea>
                </div>

                {{-- Quick Webmail & Email App Switcher Strip --}}
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ __('Choose Where to Send:') }}</span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800">
                            📎 {{ __('PDF Attached Automatically') }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                        <button type="button" onclick="executeEmailAppDispatch('gmail')"
                                class="allow-brand-icon py-2.5 px-3 rounded-lg bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/60 text-slate-800 dark:text-slate-200 font-bold border border-slate-300 dark:border-slate-600 transition flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                                title="Opens Gmail Web compose with quotation summary">
                            <svg class="w-4 h-4 shrink-0 allow-brand-icon" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill="#4285F4" d="M45,16.2l-5,2.75v19.55c0,1.38-1.12,2.5-2.5,2.5h-5.5V26.8l13-9.6V16.2z"/>
                                <path fill="#34A853" d="M16,41h-5.5c-1.38,0-2.5-1.12-2.5-2.5V18.95l-5-2.75V17.2l13,9.6V41z"/>
                                <path fill="#FBBC05" d="M37.5,7h-27C9.12,7,8,8.12,8,9.5v5.5l16,11.8l16-11.8V9.5C40,8.12,38.88,7,37.5,7z"/>
                                <path fill="#EA4335" d="M40,9.5L24,21.3L8,9.5V8.5C8,7.12,9.12,6,10.5,6h27C38.88,6,40,7.12,40,8.5V9.5z"/>
                                <path fill="#C5221F" d="M24,26.8L8,15v3.95l16,11.85l16-11.85V15L24,26.8z"/>
                            </svg>
                            <span>{{ __('Gmail Web') }}</span>
                        </button>
                        <button type="button" onclick="executeEmailAppDispatch('outlook')"
                                class="allow-brand-icon py-2.5 px-3 rounded-lg bg-white dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-blue-950/60 text-slate-800 dark:text-slate-200 font-bold border border-slate-300 dark:border-slate-600 transition flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                                title="Opens Microsoft Outlook with PDF attached automatically">
                            <svg class="w-4 h-4 shrink-0 allow-brand-icon" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="2" y="5" width="18" height="22" rx="3" fill="#0078D4"/>
                                <path d="M11 11a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 7.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z" fill="#ffffff"/>
                                <rect x="15" y="8" width="15" height="16" rx="2" fill="#28A8EA"/>
                                <path d="M15 10l7.5 5.5L30 10" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>{{ __('Outlook (Auto-Attach PDF)') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Quick SMTP Setup Drawer (One-Time Setup for Direct PDF Dispatch) --}}
                @php
                    $quickGatewaySettings = \App\Models\GatewaySetting::getSettings();
                    $quickSmtpReady = \App\Services\MailConfigService::isSmtpConfigured();
                @endphp
                <div id="email-quick-smtp-drawer" class="{{ $quickSmtpReady ? 'hidden' : '' }} p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700/60 text-xs space-y-2.5 transition">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-amber-900 dark:text-amber-200 flex items-center gap-1.5">
                            ⚙️ {{ __('One-Time Email Server Setup (Send with attached PDF directly to inbox)') }}
                        </span>
                        <button type="button" onclick="toggleQuickSmtpDrawer()" class="text-amber-700 dark:text-amber-300 underline font-semibold cursor-pointer">
                            {{ $quickSmtpReady ? __('Edit Credentials') : __('Hide') }}
                        </button>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 text-[11px] leading-relaxed">
                        {{ __('To send quotation emails with the PDF attached directly from your Gmail / Google Workspace account, enter your Gmail address and 16-character Google App Password once:') }}
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-700 dark:text-slate-300 mb-1">{{ __('Sender Gmail Address') }}</label>
                            <input type="email" id="quick-smtp-user" value="{{ $quickGatewaySettings->smtp_username ?: 'sivaraman02032003@gmail.com' }}" placeholder="yourname@gmail.com" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-700 dark:text-slate-300 mb-1">{{ __('Google App Password (16 chars)') }}</label>
                            <input type="password" id="quick-smtp-pass" value="{{ $quickGatewaySettings->smtp_password ?: '' }}" placeholder="xxxx xxxx xxxx xxxx" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono">
                        </div>
                    </div>
                    <div class="text-[10px] text-slate-500">
                        {{ __('Don\'t have an App Password? Go to') }} <a href="https://myaccount.google.com/apppasswords" target="_blank" class="underline text-blue-600 font-bold">{{ __('Google Account > App Passwords') }}</a> {{ __('to generate one in 30 seconds.') }}
                    </div>
                </div>

            </div>

            {{-- Fixed Modal Footer (Always visible, never clipped) --}}
            <div class="p-4 sm:p-5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-900/90 flex items-center justify-between shrink-0 flex-wrap gap-2.5 rounded-b-2xl">
                <div>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('alerts.gateways') }}" target="_blank" class="action-link text-xs inline-flex items-center gap-1.5 text-slate-500 hover:text-blue-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ __('SMTP Settings') }}</span>
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-2.5 flex-wrap">
                    <button type="button" onclick="closeEmailModal()" class="btn-secondary py-2 px-4 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 cursor-pointer">
                        {{ __('Cancel') }}
                    </button>

                    {{-- PRIMARY: Solution A - Direct Server Dispatch (100% Automatic Native PDF Attachment) --}}
                    <button type="button" onclick="executeServerSmtpDispatch()" id="btn-server-smtp-dispatch"
                            class="btn-primary py-2.5 px-6 rounded-xl text-xs sm:text-sm font-bold shadow-md cursor-pointer inline-flex items-center gap-2 transition"
                            style="background-color: #2563eb !important; color: #ffffff !important;"
                            title="Directly compile Quotation PDF and attach it to customer email via Server">
                        <span style="color: #ffffff !important; font-weight: 700;">{{ __('Send Email (PDF Attached)') }}</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- Send Quotation via WhatsApp & Multi-Channel Modal --}}
    <div id="whatsapp-share-modal" style="display:none;position:fixed;inset:0;z-index:50;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:1.25rem;">
        <div class="bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl p-6 sm:p-7 max-w-2xl w-full max-h-[94vh] overflow-y-auto text-slate-900 dark:text-slate-100">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-black dark:text-white shrink-0">
                        <x-icon.whatsapp :badge="true" class="w-6 h-6 text-black dark:text-white" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-tight">WhatsApp Proposal Dispatcher</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Quotation #{{ $quotation->quotation_no }} &bull; Total: <strong>₹{{ number_format((float)$quotation->total, 2) }}</strong></p>
                    </div>
                </div>
                <x-close-button onclick="closeWhatsAppModal()" />
            </div>

            {{-- Meta WhatsApp Business Window & Pricing Rate Card --}}
            <div class="mb-5 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/40 dark:to-teal-950/30 border border-emerald-200 dark:border-emerald-800/80 p-4 sm:p-5 space-y-3.5 shadow-2xs">
                
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                            WA
                        </div>
                        <div>
                            <h4 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span>{{ __('WhatsApp Document Dispatch Policy & Pricing') }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/70 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700">
                                    {{ __('Official Meta Rate') }}
                                </span>
                            </h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ __('Automatic Quotation PDF document delivery directly into customer chat with no link and no manual file attach') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/95 dark:bg-slate-900/95 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ now()->format('M Y') }}: <strong id="wa-header-month-count" class="font-mono text-emerald-700 dark:text-emerald-400 font-black">{{ $whatsappMonthlyCount ?? 0 }}</strong> / 1,000 {{ __('sent') }}</span>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white shadow-xs">
                            <span>{{ __('₹0 Free Tier Active') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Live Monthly Usage Tracker --}}
                @php
                    $monthSent = (int)($whatsappMonthlyCount ?? 0);
                    $freeLimit = 1000;
                    $freeRemaining = max(0, $freeLimit - $monthSent);
                    $progressPct = min(100, max(1, round(($monthSent / $freeLimit) * 100)));
                @endphp
                <div class="rounded-xl bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-800/80 p-3.5 shadow-2xs space-y-2">
                    <div class="flex items-center justify-between text-xs flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                                📊
                            </span>
                            <div>
                                <div class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5 text-xs">
                                    <span>{{ __('Messages Sent in WhatsApp this Month:') }}</span>
                                    <strong class="font-mono text-emerald-600 dark:text-emerald-400 text-sm font-black" id="wa-banner-sent-count">{{ $monthSent }}</strong>
                                    <span class="text-slate-500 font-normal">/ {{ number_format($freeLimit) }} ({{ now()->format('F Y') }})</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ __('Every WhatsApp quotation message & document dispatched is automatically tracked and counted.') }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-[11px]">
                            <span class="px-2.5 py-1 rounded-full font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span id="wa-banner-remaining-count">{{ number_format($freeRemaining) }}</span> {{ __('Free Messages Remaining') }}
                            </span>
                        </div>
                    </div>

                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                        <div id="wa-banner-progress-bar" class="h-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-500" style="width: {{ $progressPct }}%"></div>
                    </div>
                </div>

                {{-- Official Rate Comparison Table --}}
                <div class="overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/70 bg-white dark:bg-slate-900 shadow-2xs">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-emerald-100/60 dark:bg-emerald-950/80 text-emerald-950 dark:text-emerald-200 font-extrabold border-b border-emerald-200 dark:border-emerald-800">
                                <th class="py-2.5 px-3 sm:px-4">{{ __('How the PDF goes out') }}</th>
                                <th class="py-2.5 px-3 sm:px-4">{{ __('Rate used') }}</th>
                                <th class="py-2.5 px-3 sm:px-4 text-right">{{ __('Cost for 500 messages') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 dark:divide-emerald-900/50">
                            <tr class="bg-emerald-50/40 dark:bg-emerald-950/20 font-medium">
                                <td class="py-2.5 px-3 sm:px-4 text-slate-800 dark:text-slate-200">
                                    <div class="font-bold flex items-center gap-1.5 text-emerald-700 dark:text-emerald-300">
                                        <span>✓</span>
                                        <span>{{ __('Inside the 24-hour window, after the customer messaged you first') }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ __('New customer sends "Hi" or messages your number: quotation PDF is sent directly as an attached document.') }}
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 sm:px-4 text-slate-700 dark:text-slate-300">
                                    <span class="inline-block font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/60 px-2 py-0.5 rounded text-[11px]">
                                        {{ __('First 1,000 non-template messages per business number each month are free') }}
                                    </span>
                                    <div class="mt-1.5 flex items-center gap-2 text-[11px] font-semibold text-slate-700 dark:text-slate-200 flex-wrap">
                                        <span class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-400">
                                            <span>●</span>
                                            <span>{{ __('Sent this month:') }}</span>
                                            <strong id="wa-table-sent-count" class="font-mono font-bold">{{ $monthSent }}</strong>
                                        </span>
                                        <span class="text-slate-400 dark:text-slate-500">•</span>
                                        <span class="text-slate-600 dark:text-slate-400">
                                            {{ __('Remaining free:') }} <strong id="wa-table-remaining-count" class="font-mono text-emerald-600 dark:text-emerald-400">{{ number_format($freeRemaining) }}</strong>
                                        </span>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 sm:px-4 text-right font-black text-emerald-600 dark:text-emerald-400 font-mono text-sm">
                                    ₹0
                                </td>
                            </tr>
                            <tr class="text-slate-600 dark:text-slate-400">
                                <td class="py-2.5 px-3 sm:px-4">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('Device Direct App Share (Mobile / Desktop WhatsApp App)') }}</div>
                                    <div class="text-[11px] text-slate-500">{{ __('Opens WhatsApp directly with the actual PDF document file attached ready to send.') }}</div>
                                </td>
                                <td class="py-2.5 px-3 sm:px-4">
                                    {{ __('Direct peer-to-peer document transfer (Unlimited free)') }}
                                </td>
                                <td class="py-2.5 px-3 sm:px-4 text-right font-bold text-slate-700 dark:text-slate-300 font-mono">
                                    ₹0
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- 24h Window Workflow Tip --}}
                <div class="flex items-center gap-2 text-[11px] text-emerald-900 dark:text-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                    <span>
                        <strong>{{ __('Auto-Attach Workflow:') }}</strong>
                        {{ __('When a customer messages you "Hi" or you select a saved contact, click "Send Quotation PDF" to open their chat with the PDF document already attached. Hit send with zero links and zero manual file uploads.') }}
                    </span>
                </div>

            </div>

            {{-- Step 1: PDF Template Style & Pricing Breakdown Option Dropdowns (Image 1 Style) --}}
            <div class="mb-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                {{ __('PDF Template Style') }}
                            </span>
                            <a id="btn-wa-preview-selected-pdf" href="{{ route('quotations.preview', [$quotation, $quotation->pdf_format ?? '1', 'pricing_mode' => $quotation->pricing_mode ?? 'itemized']) }}" class="action-link text-[11px] font-semibold inline-flex items-center gap-1 normal-case tracking-normal text-blue-600 hover:text-blue-700" title="Preview Live Webpage">
                                <span>👁️ {{ __('Preview') }}</span>
                            </a>
                        </label>
                        <select id="wa-modal-format" name="wa_pdf_format" onchange="onWhatsAppOptionsChange()"
                                class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-xs cursor-pointer">
                            <option value="1" {{ ($quotation->pdf_format ?? '1') == '1' ? 'selected' : '' }}>Executive Modern (Format 1)</option>
                            <option value="2" {{ ($quotation->pdf_format ?? '1') == '2' ? 'selected' : '' }}>Technical Detailed (Format 2)</option>
                            <option value="3" {{ ($quotation->pdf_format ?? '1') == '3' ? 'selected' : '' }}>Linepix GST Invoice (Format 3)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ __('Pricing Breakdown') }}
                            </span>
                            <span id="wa-active-pricing-badge" class="text-[10px] px-2 py-0.5 rounded font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                {{ $quotation->pricing_mode_name }}
                            </span>
                        </label>
                        <select id="wa-modal-pricing" name="wa_pricing_mode" onchange="onWhatsAppOptionsChange()"
                                class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-xs cursor-pointer">
                            <option value="itemized" {{ ($quotation->pricing_mode ?? 'itemized') == 'itemized' ? 'selected' : '' }}>With Product Cost &amp; Total (Itemized)</option>
                            <option value="total_only" {{ ($quotation->pricing_mode ?? 'itemized') == 'total_only' ? 'selected' : '' }}>Total Cost Only (Turnkey Package)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 2: Customer Contact & Target Mobile --}}
            <div class="mb-5 bg-slate-50/80 dark:bg-slate-800/40 p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/60">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        2. Customer Contact &amp; Target Mobile
                    </label>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1">
                        <span>Target:</span>
                        <strong class="font-mono text-emerald-600 dark:text-emerald-400" id="wa-phone-preview-badge">{{ $quotation->getWhatsAppPhone() ?: 'Customer Number' }}</strong>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Customer Name</label>
                        <input aria-label="Customer Name" type="text" id="wa-customer-name" value="{{ $quotation->lead?->customer_name ?? 'Valued Customer' }}"
                               placeholder="e.g. John Doe"
                               oninput="onWhatsAppNameInput(this.value)"
                               class="crm-input text-xs py-2 bg-white dark:bg-slate-800">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                            WhatsApp Phone <span class="required-marker text-rose-500">*</span>
                        </label>
                        <input aria-label="WhatsApp Phone" type="tel" id="wa-customer-phone" value="{{ $quotation->getWhatsAppPhone() ?: $quotation->lead?->phone }}" placeholder="e.g. 9876543210"
                               oninput="onWhatsAppPhoneInput(this.value)"
                               class="crm-input text-xs py-2 bg-white dark:bg-slate-800 font-mono">
                    </div>
                </div>
            </div>

            {{-- Step 3: Message Text with Clickable Toggle (Default: PDF Only) --}}
            <div class="mb-5 bg-white dark:bg-slate-900/60 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700/80 shadow-2xs transition-all" id="wa-message-preview-container">
                <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <label class="crm-label font-bold mb-0 flex items-center gap-1.5 text-xs text-slate-800 dark:text-slate-200">
                            <svg class="w-4 h-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            <span>3. WhatsApp Proposal Message Preview</span>
                        </label>

                        {{-- The Clickable Toggle Button: Default OFF (Quotation PDF Only) --}}
                        <button type="button" 
                                id="btn-toggle-include-msg" 
                                onclick="toggleWhatsAppMessageInclusion()" 
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold transition border cursor-pointer bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 shadow-2xs"
                                title="{{ __('Toggle to include or exclude proposal text message on WhatsApp') }}">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400" id="indicator-include-msg"></span>
                            <span id="label-include-msg">Share Message: <strong class="text-slate-800 dark:text-slate-200">OFF (PDF Only Default)</strong></span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="copyWhatsAppCustomText()" id="btn-copy-wa-text" class="action-link text-xs inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span>Copy Message</span>
                        </button>
                    </div>
                </div>

                {{-- Status Banner showing Default PDF Only vs Message Included --}}
                <div id="wa-mode-notice-banner" class="mb-2 px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-[11px] text-slate-600 dark:text-slate-400 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span id="wa-mode-badge" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">Default Mode</span>
                        <span id="wa-mode-desc">Shares <strong>Quotation PDF File Only</strong> (clean chat without proposal text).</span>
                    </div>
                    <span id="wa-mode-hint" class="text-slate-400 dark:text-slate-500 text-[10px]">Click button above to include text</span>
                </div>

                <textarea aria-label="WhatsApp message" id="wa-custom-message" rows="4" class="crm-input h-auto py-2.5 font-mono text-xs leading-relaxed bg-slate-50/80 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700">{{ $quotation->getWhatsAppFormattedMessage($quotation->pdf_format ?: '1') }}</textarea>
            </div>

            {{-- Action Buttons Footer --}}
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" onclick="closeWhatsAppModal()" class="btn-secondary text-xs py-2 px-3 rounded-lg text-slate-600 dark:text-slate-300">
                        <span>Cancel</span>
                    </button>
                    <button type="button" onclick="downloadSelectedPdfOnly()" class="btn-secondary text-xs py-2 px-3 rounded-lg flex items-center justify-center font-medium" title="Download quotation PDF file to disk">
                        <span>Download PDF</span>
                    </button>
                    <button type="button" onclick="shareViaNativeWebShare()" class="btn-secondary text-xs py-2 px-3.5 rounded-lg flex items-center justify-center font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 hover:border-blue-300 transition" title="Share quotation PDF file directly via OS share dialog">
                        <span class="font-bold">Share PDF File</span>
                    </button>
                </div>

                <div class="flex items-center gap-2 justify-end">
                    <button type="button" id="btn-wa-web-dispatch" onclick="openWhatsAppDirect('web')" class="btn-primary text-xs sm:text-sm py-2 px-4 rounded-xl font-bold flex items-center justify-center gap-2 shadow-xs transition cursor-pointer" style="background-color: #059669 !important; color: #ffffff !important;" title="Auto-copies quotation for Ctrl+V paste and opens customer chat on WhatsApp Web">
                        <span style="color: #ffffff !important;">Web WhatsApp</span>
                    </button>
                    <button type="button" id="btn-wa-app-dispatch" onclick="openWhatsAppDirect('app')" class="btn-secondary text-xs sm:text-sm py-2 px-4 rounded-xl font-bold flex items-center justify-center gap-2 hover:border-emerald-500 hover:text-emerald-600 transition shadow-xs cursor-pointer" title="Auto-copies quotation for Ctrl+V paste and opens customer chat on WhatsApp Desktop App">
                        <span>Desktop App</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
    @php
        $waItemsJson = json_encode($quotation->items->map(function($it) {
            return [
                'name'  => (string) $it->item_name,
                'qty'   => (float) $it->quantity,
                'unit'  => '₹' . number_format((float) $it->unit_price, 2),
                'total' => '₹' . number_format((float) $it->total_price, 2),
            ];
        })->values()->all(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        $waCompanyJson = json_encode([
            'name'  => $quotation->company?->trade_name ?? 'PRECISION IT SYSTEMS',
            'phone' => $quotation->company?->phone ?? '96772 57774',
            'email' => $quotation->company?->email ?? 'precisionitsystem@gmail.com',
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        $waSubtotalStr   = '₹' . number_format((float) $quotation->subtotal, 2);
        $waTaxAmountStr  = '₹' . number_format((float) $quotation->tax_amount, 2);
        $waGrandTotalStr = '₹' . number_format((float) $quotation->total, 2);
    @endphp

    // State for Quotation WhatsApp Dispatcher
    const waQuotationData = {
        id: {{ $quotation->id }},
        quoteNo: @json($quotation->quotation_no),
        customerName: @json($quotation->lead?->customer_name ?? 'Customer'),
        customerEmail: @json($quotation->lead?->email ?? ''),
        customerAddress: @json($quotation->lead?->site_address ?? ''),
        defaultPhone: @json($quotation->getWhatsAppPhone()),
        selectedFormat: @json($quotation->pdf_format ?: '1'),
        selectedPricing: @json($quotation->pricing_mode ?: 'itemized'),
        date: @json($quotation->quotation_date?->format('d M Y') ?: now()->format('d M Y')),
        validUntil: @json($quotation->valid_until?->format('d M Y') ?: ''),
        subtotal: @json($waSubtotalStr),
        taxPercent: @json($quotation->tax_percent . '%'),
        taxAmount: @json($waTaxAmountStr),
        grandTotal: @json($waGrandTotalStr),
        items: {!! $waItemsJson !!},
        company: {!! $waCompanyJson !!},
        messages: {
            '1_itemized': @json($quotation->getWhatsAppFormattedMessage('1', 'itemized')),
            '1_total_only': @json($quotation->getWhatsAppFormattedMessage('1', 'total_only')),
            '2_itemized': @json($quotation->getWhatsAppFormattedMessage('2', 'itemized')),
            '2_total_only': @json($quotation->getWhatsAppFormattedMessage('2', 'total_only')),
            '3_itemized': @json($quotation->getWhatsAppFormattedMessage('3', 'itemized')),
            '3_total_only': @json($quotation->getWhatsAppFormattedMessage('3', 'total_only')),
        },
        vcardUrl: @json(route('quotations.vcard', $quotation)),
        sendUrl: @json(route('quotations.send-whatsapp', $quotation))
    };

    function onWhatsAppOptionsChange() {
        const formatSelect = document.getElementById('wa-modal-format');
        const pricingSelect = document.getElementById('wa-modal-pricing');
        const format = formatSelect ? formatSelect.value : (waQuotationData.selectedFormat || '1');
        const pricing = pricingSelect ? pricingSelect.value : (waQuotationData.selectedPricing || 'itemized');

        waQuotationData.selectedFormat = format;
        waQuotationData.selectedPricing = pricing;

        // Update active pricing badge
        const badge = document.getElementById('wa-active-pricing-badge');
        if (badge) {
            badge.innerText = pricing === 'total_only' ? 'Total Cost Only' : 'With Product Cost';
        }

        // Update live PDF Preview Link
        const previewBtn = document.getElementById('btn-wa-preview-selected-pdf');
        if (previewBtn) {
            previewBtn.href = `/quotations/${waQuotationData.id}/preview/${format}?pricing_mode=${encodeURIComponent(pricing)}`;
        }

        // Update Message textarea
        const key = `${format}_${pricing}`;
        const msgBox = document.getElementById('wa-custom-message');
        if (msgBox && waQuotationData.messages[key]) {
            msgBox.value = waQuotationData.messages[key];
        }
    }

    let waIncludeMessage = false; // Default: false (Quotation PDF file ONLY)

    function toggleWhatsAppMessageInclusion() {
        waIncludeMessage = !waIncludeMessage;
        updateWhatsAppMessageToggleUI();
    }

    function updateWhatsAppMessageToggleUI() {
        const btn = document.getElementById('btn-toggle-include-msg');
        const indicator = document.getElementById('indicator-include-msg');
        const label = document.getElementById('label-include-msg');
        const bannerBadge = document.getElementById('wa-mode-badge');
        const bannerDesc = document.getElementById('wa-mode-desc');
        const bannerHint = document.getElementById('wa-mode-hint');
        const msgBox = document.getElementById('wa-custom-message');
        const container = document.getElementById('wa-message-preview-container');
        const btnWeb = document.getElementById('btn-wa-web-dispatch');

        if (waIncludeMessage) {
            if (btn) btn.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold transition border cursor-pointer bg-emerald-600 text-white border-emerald-600 ring-2 ring-emerald-500/20 shadow-xs';
            if (indicator) indicator.className = 'w-2.5 h-2.5 rounded-full bg-white';
            if (label) label.innerHTML = 'Share Message: <strong>ON (Text + PDF)</strong>';
            if (bannerBadge) {
                bannerBadge.innerText = 'Active';
                bannerBadge.className = 'px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white';
            }
            if (bannerDesc) bannerDesc.innerHTML = 'Proposal message text will be pre-filled into WhatsApp chat alongside the PDF file.';
            if (bannerHint) bannerHint.innerText = 'Click button above to switch to PDF only';
            if (msgBox) {
                msgBox.classList.remove('opacity-60', 'bg-slate-50/80', 'dark:bg-slate-800/60');
                msgBox.classList.add('bg-white', 'dark:bg-slate-800');
            }
            if (container) {
                container.classList.remove('border-slate-200', 'dark:border-slate-700/80');
                container.classList.add('border-emerald-500/50', 'dark:border-emerald-500/50', 'ring-1', 'ring-emerald-500/20');
            }
            if (btnWeb) btnWeb.innerHTML = '<span style="color: #ffffff !important;">Web WhatsApp (Text + PDF)</span>';
        } else {
            if (btn) btn.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold transition border cursor-pointer bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 shadow-2xs';
            if (indicator) indicator.className = 'w-2.5 h-2.5 rounded-full bg-slate-400';
            if (label) label.innerHTML = 'Share Message: <strong class="text-slate-800 dark:text-slate-200">OFF (PDF Only Default)</strong>';
            if (bannerBadge) {
                bannerBadge.innerText = 'Default Mode';
                bannerBadge.className = 'px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300';
            }
            if (bannerDesc) bannerDesc.innerHTML = 'Shares <strong>Quotation PDF File Only</strong> (clean chat without proposal text).';
            if (bannerHint) bannerHint.innerText = 'Click button above to include text';
            if (msgBox) {
                msgBox.classList.add('opacity-60', 'bg-slate-50/80', 'dark:bg-slate-800/60');
                msgBox.classList.remove('bg-white', 'dark:bg-slate-800');
            }
            if (container) {
                container.classList.add('border-slate-200', 'dark:border-slate-700/80');
                container.classList.remove('border-emerald-500/50', 'dark:border-emerald-500/50', 'ring-1', 'ring-emerald-500/20');
            }
            if (btnWeb) btnWeb.innerHTML = '<span style="color: #ffffff !important;">Web WhatsApp</span>';
        }
    }

    function openWhatsAppModal() {
        const modal = document.getElementById('whatsapp-share-modal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('open');
            waIncludeMessage = false;
            updateWhatsAppMessageToggleUI();
            onWhatsAppOptionsChange();
            syncCustomerStatusUI();
        }
    }

    function shareWhatsAppWithPdf(btn) {
        openWhatsAppModal();
    }

    function closeWhatsAppModal() {
        const modal = document.getElementById('whatsapp-share-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('open');
        }
    }

    function onWhatsAppFormatChange(format) {
        const formatSelect = document.getElementById('wa-modal-format');
        if (formatSelect) {
            formatSelect.value = format;
        }
        onWhatsAppOptionsChange();
    }

    function onWhatsAppNameInput(val) {
        waQuotationData.customerName = val.trim() || 'Customer';
        syncCustomerStatusUI();
    }

    function onWhatsAppPhoneInput(val) {
        const phone = getNormalizedPhone();
        const badge = document.getElementById('wa-phone-preview-badge');
        if (badge) {
            badge.innerText = phone || 'Customer Number';
        }
        syncCustomerStatusUI();
    }

    function getNormalizedPhone() {
        const input = document.getElementById('wa-customer-phone');
        let phone = (input ? input.value : (waQuotationData.defaultPhone || '')).replace(/[^0-9]/g, '');
        if (phone.startsWith('0') && phone.length === 11) {
            phone = '91' + phone.substring(1);
        } else if (phone.length === 10) {
            phone = '91' + phone;
        }
        return phone;
    }

    function getCustomerSafeName() {
        const nameInput = document.getElementById('wa-customer-name');
        const rawName = (nameInput ? nameInput.value : '').trim() || waQuotationData.customerName || 'customer';
        return rawName.toLowerCase().replace(/[^a-z0-9_-]/g, '_').replace(/_+/g, '_');
    }

    function getQuotationPdfFilename() {
        const safeName = getCustomerSafeName();
        return `Quotation_${waQuotationData.quoteNo}_${safeName}.pdf`;
    }

    function syncCustomerStatusUI() {
        const phone = getNormalizedPhone();
        const badge = document.getElementById('wa-phone-preview-badge');
        if (badge) {
            badge.innerText = phone || 'Customer Number';
        }
        const btnText = document.getElementById('btn-wa-dispatch-text');
        if (btnText) {
            btnText.innerText = 'Share PDF & Open WhatsApp Chat';
        }
    }

    function openCustomerGoogleContacts() {
        const contactData = {
            name: @json($quotation->lead?->customer_name ?? 'Customer'),
            phone: @json($quotation->getWhatsAppPhone() ?: ($quotation->lead?->phone ?? '')),
            email: @json($quotation->lead?->email ?? ''),
            address: @json($quotation->lead?->site_address ?? ''),
            company: @json($quotation->company?->trade_name ?? config('app.name', 'Precision IT Systems'))
        };

        if (window.triggerSaveContactDirect) {
            window.triggerSaveContactDirect(contactData);
        } else {
            const parts = (contactData.name || '').trim().split(/\s+/);
            const firstName = parts[0] || '';
            const lastName = parts.slice(1).join(' ') || '';
            let phoneClean = (contactData.phone || '').trim();
            if (phoneClean && !phoneClean.startsWith('+')) {
                const digits = phoneClean.replace(/[^0-9]/g, '');
                if (digits.length === 10) phoneClean = '+91' + digits;
                else if (digits.length > 10) phoneClean = '+' + digits;
            }
            const params = new URLSearchParams();
            if (firstName) params.set('givenname', firstName);
            if (lastName) params.set('familyname', lastName);
            if (phoneClean) params.set('phone', phoneClean);
            if (contactData.email) params.set('email', contactData.email);

            window.open('https://contacts.google.com/new?' + params.toString(), '_blank');
        }
    }
    window.openCustomerGoogleContacts = openCustomerGoogleContacts;

    function saveCustomerContactFromModal() {
        const phone = getNormalizedPhone();
        const nameInput = document.getElementById('wa-customer-name');
        const name = nameInput ? nameInput.value.trim() : (waQuotationData.customerName || 'Customer');

        if (!phone) {
            crmAlert('Please enter a valid customer phone number first.');
            return;
        }

        if (window.triggerSaveContactDirect) {
            window.triggerSaveContactDirect({
                name: name,
                phone: phone,
                email: waQuotationData.customerEmail || '',
                address: waQuotationData.customerAddress || '',
                company: waQuotationData.company?.name || 'Precision IT Systems'
            });
        }
    }

    // Alias for backward compatibility
    window.quickSaveCustomerContact = openCustomerGoogleContacts;

    async function copyWhatsAppCustomText() {
        const msgBox = document.getElementById('wa-custom-message');
        const text = msgBox ? msgBox.value : '';
        const btn = document.getElementById('btn-copy-wa-text');
        try {
            await navigator.clipboard.writeText(text);
            if (btn) {
                const prev = btn.innerHTML;
                btn.innerHTML = 'Copied!';
                setTimeout(() => { btn.innerHTML = prev; }, 2000);
            }
        } catch (e) {
            crmAlert('Could not copy to clipboard. Please select text manually.');
        }
    }

    function downloadSelectedPdfOnly() {
        const format = waQuotationData.selectedFormat || '1';
        const pricing = waQuotationData.selectedPricing || 'itemized';
        const pdfUrl = `/quotations/${waQuotationData.id}/pdf/${format}?pricing_mode=${encodeURIComponent(pricing)}`;
        const filename = getQuotationPdfFilename();

        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = filename;
        // Do NOT use target="_blank" here so browser does not block WhatsApp chat as a second popup
        document.body.appendChild(link);
        link.click();
        setTimeout(() => link.remove(), 600);
    }

    function showWhatsAppAttachmentBanner(filename) {
        let existing = document.getElementById('wa-attachment-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'wa-attachment-toast';
        toast.className = 'fixed bottom-6 right-6 z-[100] max-w-sm bg-slate-900 text-white rounded-2xl shadow-2xl p-4 border border-slate-700 flex items-start gap-3 transition-all duration-300 transform translate-y-0 opacity-100';
        toast.innerHTML = `
            <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="flex-1 text-xs">
                <div class="font-bold text-sm text-white flex items-center gap-2">
                    <span>PDF Proposal Ready</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-bold">Downloaded</span>
                </div>
                <p class="text-slate-300 mt-1 leading-relaxed">
                    Customer chat is open! The official quotation PDF <strong class="text-emerald-300">${filename}</strong> is downloaded in your browser tray. Drag the PDF file directly into chat (or click 📎 Document) and click <strong>Send</strong>.
                </p>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white p-1 text-base leading-none">&times;</button>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            if (toast && toast.parentElement) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 400);
            }
        }, 12000);
    }

    async function sendViaWhatsAppCloudApi() {
        const btn = document.getElementById('btn-cloud-api-dispatch');
        const originalHtml = btn ? btn.innerHTML : '';
        const format = waQuotationData.selectedFormat || '1';
        const pricing = waQuotationData.selectedPricing || 'itemized';
        const phone = getNormalizedPhone();

        if (!phone || phone.length < 10) {
            crmAlert('Please enter a valid WhatsApp phone number for the customer (e.g. 9876543210).');
            return;
        }

        const msgBox = document.getElementById('wa-custom-message');
        const caption = (msgBox ? msgBox.value : '') || waQuotationData.messages[`${format}_${pricing}`] || '';

        if (btn) btn.innerHTML = `<span>⏳ Delivering PDF via Cloud API...</span>`;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/quotations/${waQuotationData.id}/send-whatsapp-document`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    phone: phone,
                    format: format,
                    pricing_mode: pricing,
                    caption: caption
                })
            });
            const data = await res.json();
            if (data.success) {
                if (btn) btn.innerHTML = `<span>✓ PDF Delivered to Customer Chat!</span>`;
                setTimeout(() => {
                    closeWhatsAppModal();
                    if (btn) btn.innerHTML = originalHtml;
                }, 2200);
            } else if (data.requires_setup) {
                crmAlert('Meta WhatsApp Cloud API requires WHATSAPP_PHONE_NUMBER_ID and WHATSAPP_ACCESS_TOKEN in .env.\n\nOpening direct customer chat with pre-downloaded PDF for immediate sending...');
                if (btn) btn.innerHTML = originalHtml;
                executeWhatsAppShareWithPdf();
            } else {
                crmAlert(data.error || data.message || 'WhatsApp Cloud API dispatch note. Opening direct customer WhatsApp chat with auto-downloaded PDF...');
                if (btn) btn.innerHTML = originalHtml;
                executeWhatsAppShareWithPdf();
            }
        } catch (e) {
            if (btn) btn.innerHTML = originalHtml;
            executeWhatsAppShareWithPdf();
        }
    }

    async function executeWhatsAppShareWithPdf() {
        const btn = document.getElementById('btn-do-whatsapp-dispatch');
        const originalHtml = btn ? btn.innerHTML : '';
        const format = waQuotationData.selectedFormat || '1';
        const pricing = waQuotationData.selectedPricing || 'itemized';
        const phone = getNormalizedPhone();

        if (!phone || phone.length < 10) {
            crmAlert('Please enter a valid WhatsApp phone number for the customer (e.g. 9876543210).');
            const phoneInput = document.getElementById('wa-customer-phone');
            if (phoneInput) {
                phoneInput.focus();
                phoneInput.classList.add('ring-2', 'ring-rose-500');
                setTimeout(() => phoneInput.classList.remove('ring-2', 'ring-rose-500'), 3000);
            }
            return;
        }

        const nameInput = document.getElementById('wa-customer-name');
        const name = (nameInput ? nameInput.value : '').trim() || waQuotationData.customerName || 'Customer';
        const msgBox = document.getElementById('wa-custom-message');
        const message = (msgBox ? msgBox.value : '') || waQuotationData.messages[`${format}_${pricing}`] || '';
        const filename = getQuotationPdfFilename();
        const encodedText = encodeURIComponent(message);

        if (btn) btn.innerHTML = `<span>⏳ Preparing PDF & Opening Chat...</span>`;

        // 1. Automatically download the PDF proposal file into browser downloads tray
        downloadSelectedPdfOnly();

        // 2. Fetch the PDF blob and prime clipboard so user can press Ctrl + V in chat
        try {
            const pdfResponse = await fetch(`/quotations/${waQuotationData.id}/pdf/${format}?pricing_mode=${encodeURIComponent(pricing)}`);
            const pdfBlob = await pdfResponse.blob();
            if (window.ClipboardItem && navigator.clipboard && navigator.clipboard.write) {
                try {
                    const item = new ClipboardItem({ [pdfBlob.type]: pdfBlob });
                    await navigator.clipboard.write([item]);
                } catch (clipErr) {
                    if (message && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(message).catch(() => {});
                    }
                }
            } else if (message && navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(message).catch(() => {});
            }
        } catch (e) {
            if (message && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(message).catch(() => {});
            }
        }

        // 3. Direct customer deep-link: opens chat for BOTH saved and brand new customers with zero search dialog!
        const targetUrl = waIncludeMessage
            ? `https://api.whatsapp.com/send?phone=${phone}&text=${encodedText}`
            : `https://api.whatsapp.com/send?phone=${phone}`;
        let waTab = window.open(targetUrl, '_blank');
        if (!waTab) {
            window.location.href = targetUrl;
        }

        // 4. Automatically save/sync customer details into CRM contact list immediately
        const params = new URLSearchParams({
            format: format,
            pricing_mode: pricing,
            phone: phone,
            customer_name: name,
            custom_message: message
        });
        fetch(`${waQuotationData.sendUrl}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(res => res.json()).then(data => {
            if (data.success) {
                waQuotationData.customerName = name;
                waQuotationData.defaultPhone = phone;
                syncCustomerStatusUI();
                if (data.monthly_count !== undefined) {
                    updateWhatsAppMonthlyCounters(data.monthly_count);
                }
            }
        }).catch(e => console.warn('Contact sync skipped', e));

        // 4. Trigger PDF download with slight delay so browser treats it as file download without blocking the WhatsApp tab
        setTimeout(() => {
            downloadSelectedPdfOnly();
        }, 500);

        // 5. Automatically copy proposal text to clipboard
        if (message && navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(message).catch(() => {});
        }

        // 6. Visual notification banner & button feedback
        showWhatsAppAttachmentBanner(filename);

        if (btn) {
            btn.innerHTML = `<span>✓ Opening WhatsApp Chat...</span>`;
            setTimeout(() => {
                if (btn) btn.innerHTML = originalHtml;
            }, 3500);
        }
    }

    function updateWhatsAppMonthlyCounters(newCount) {
        if (newCount === undefined || newCount === null) return;
        const count = parseInt(newCount, 10);
        const limit = 1000;
        const remaining = Math.max(0, limit - count);
        const percentage = Math.min(100, Math.max(1, Math.round((count / limit) * 100)));

        const elHeader = document.getElementById('wa-header-month-count');
        if (elHeader) elHeader.innerText = count;

        const elBannerSent = document.getElementById('wa-banner-sent-count');
        if (elBannerSent) elBannerSent.innerText = count;

        const elBannerRemaining = document.getElementById('wa-banner-remaining-count');
        if (elBannerRemaining) elBannerRemaining.innerText = remaining.toLocaleString();

        const elProgressBar = document.getElementById('wa-banner-progress-bar');
        if (elProgressBar) elProgressBar.style.width = percentage + '%';

        const elTableSent = document.getElementById('wa-table-sent-count');
        if (elTableSent) elTableSent.innerText = count;

        const elTableRemaining = document.getElementById('wa-table-remaining-count');
        if (elTableRemaining) elTableRemaining.innerText = remaining.toLocaleString();
    }

    async function openWhatsAppDirect(type) {
        const format = waQuotationData.selectedFormat || '1';
        const pricing = waQuotationData.selectedPricing || 'itemized';
        const phone = getNormalizedPhone();
        if (!phone || phone.length < 10) {
            crmAlert('Please enter a valid WhatsApp phone number for the customer (e.g. 9876543210).');
            const phoneInput = document.getElementById('wa-customer-phone');
            if (phoneInput) {
                phoneInput.focus();
                phoneInput.classList.add('ring-2', 'ring-rose-500');
                setTimeout(() => phoneInput.classList.remove('ring-2', 'ring-rose-500'), 3000);
            }
            return;
        }

        const nameInput = document.getElementById('wa-customer-name');
        const name = (nameInput ? nameInput.value : '').trim() || waQuotationData.customerName || 'Customer';
        const msgBox = document.getElementById('wa-custom-message');
        const message = (msgBox ? msgBox.value : '') || waQuotationData.messages[`${format}_${pricing}`] || '';
        const filename = getQuotationPdfFilename();
        const encodedText = encodeURIComponent(message);

        // 1. Automatically download the exact chosen Quotation PDF file (Format 1, 2, or 4) into browser downloads
        downloadSelectedPdfOnly();

        // 2. Copy the formatted proposal text to clipboard ONLY IF enabled
        if (waIncludeMessage && message && navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(message).catch(() => {});
        }

        // 3. Open WhatsApp chat directly with customer's phone preloaded
        let url = '';
        if (type === 'web') {
            url = waIncludeMessage
                ? `https://web.whatsapp.com/send?phone=${phone}&text=${encodedText}`
                : `https://web.whatsapp.com/send?phone=${phone}`;
        } else if (type === 'app') {
            url = waIncludeMessage
                ? `whatsapp://send?phone=${phone}&text=${encodedText}`
                : `whatsapp://send?phone=${phone}`;
        }

        const waTab = window.open(url, '_blank');
        if (!waTab && type === 'web') {
            window.location.href = url;
        }

        // 4. Save customer to CRM contact list
        const params = new URLSearchParams({
            format: format,
            pricing_mode: pricing,
            phone: phone,
            customer_name: name,
            custom_message: waIncludeMessage ? message : ''
        });
        fetch(`${waQuotationData.sendUrl}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(res => res.json()).then(data => {
            if (data.success) {
                waQuotationData.customerName = name;
                waQuotationData.defaultPhone = phone;
                syncCustomerStatusUI();
                if (data.monthly_count !== undefined) {
                    updateWhatsAppMonthlyCounters(data.monthly_count);
                }
            }
        }).catch(console.warn);

        // 5. Show guidance toast
        showWhatsAppAttachmentBanner(filename);
    }

    async function shareViaNativeWebShare() {
        const format = waQuotationData.selectedFormat || '1';
        const pricing = waQuotationData.selectedPricing || 'itemized';
        const filename = getQuotationPdfFilename();
        const msgBox = document.getElementById('wa-custom-message');
        const message = (msgBox ? msgBox.value : '') || waQuotationData.messages[`${format}_${pricing}`] || '';

        try {
            const pdfResponse = await fetch(`/quotations/${waQuotationData.id}/pdf/${format}?pricing_mode=${encodeURIComponent(pricing)}`);
            const pdfBlob = await pdfResponse.blob();
            const pdfFile = new File([pdfBlob], filename, { type: 'application/pdf' });

            const shareData = {
                files: [pdfFile],
                title: `Quotation #${waQuotationData.quoteNo}`
            };
            if (waIncludeMessage && message) {
                shareData.text = message;
            }

            if (navigator.canShare && navigator.canShare(shareData)) {
                await navigator.share(shareData);

                // Log dispatch & update monthly count in CRM
                const shareParams = new URLSearchParams({
                    format: format,
                    pricing_mode: pricing,
                    phone: getNormalizedPhone() || waQuotationData.defaultPhone || '',
                    customer_name: (document.getElementById('wa-customer-name')?.value || '').trim() || waQuotationData.customerName,
                    custom_message: waIncludeMessage ? message : ''
                });
                fetch(`${waQuotationData.sendUrl}?${shareParams.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                }).then(res => res.json()).then(data => {
                    if (data.monthly_count !== undefined) {
                        updateWhatsAppMonthlyCounters(data.monthly_count);
                    }
                }).catch(() => {});
            } else {
                crmAlert('Native OS file sharing is not supported by this browser. Downloaded PDF automatically instead.');
                downloadSelectedPdfOnly();
            }
        } catch (e) {
            if (e.name !== 'AbortError') {
                console.warn('Native share cancelled or failed', e);
            }
        }
    }

    function shareWhatsAppWithPdf(format = '1') {
        return executeWhatsAppShareWithPdf();
    }

    // Email Quotation Modal Management & Multi-Client Dispatch System
    const emailQuotationData = {
        id: {{ $quotation->id }},
        quoteNo: @json($quotation->quotation_no),
        customerName: @json($quotation->lead?->customer_name ?? 'Customer'),
        defaultEmail: @json($quotation->lead?->email ?? ''),
        formatNames: {
            '1': 'Executive Modern (Format 1)',
            '2': 'Technical Detailed (Format 2)',
            '3': 'Linepix GST Invoice (Format 3)'
        }
    };

    function onEmailModalOptionsChange() {
        const formatSelect = document.getElementById('email-modal-format');
        const pricingSelect = document.getElementById('email-modal-pricing');
        const format = formatSelect ? formatSelect.value : '1';
        const pricing = pricingSelect ? pricingSelect.value : 'itemized';

        const formatLabel = document.getElementById('email-active-format-label');
        if (formatLabel) {
            const pricingLabel = pricing === 'total_only' ? 'Turnkey Total' : 'Itemized';
            formatLabel.innerText = `${emailQuotationData.formatNames[format] || 'Format ' + format} &bull; ${pricingLabel}`;
        }

        const previewBtn = document.getElementById('btn-email-preview-pdf');
        if (previewBtn) {
            previewBtn.href = `/quotations/${emailQuotationData.id}/preview/${format}?pricing_mode=${encodeURIComponent(pricing)}`;
        }
    }

    function downloadEmailModalPdf() {
        const formatSelect = document.getElementById('email-modal-format');
        const pricingSelect = document.getElementById('email-modal-pricing');
        const format = formatSelect ? formatSelect.value : '1';
        const pricing = pricingSelect ? pricingSelect.value : 'itemized';
        const filename = `Quotation_${emailQuotationData.quoteNo}_${getCustomerSafeName()}.pdf`;

        const link = document.createElement('a');
        link.href = `/quotations/${emailQuotationData.id}/pdf/${format}?pricing_mode=${encodeURIComponent(pricing)}`;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        setTimeout(() => link.remove(), 500);
    }

    function copyModalRecipientEmail(btn = null) {
        const emailInput = document.getElementById('email-modal-recipient');
        const email = emailInput ? emailInput.value.trim() : '';
        if (!email) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(email).then(() => {
                if (btn) {
                    const span = btn.querySelector('span');
                    if (span) {
                        const orig = span.innerText;
                        span.innerText = '✓ Copied!';
                        setTimeout(() => span.innerText = orig, 2000);
                    }
                }
            }).catch(() => {});
        }
    }

    function showEmailAppGuideToast(email, filename, clientType = 'outlook') {
        let existing = document.getElementById('email-app-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'email-app-toast';
        toast.className = 'fixed bottom-6 right-6 z-[100] max-w-sm bg-slate-900 text-white rounded-2xl shadow-2xl p-4 border border-blue-500/40 flex items-start gap-3 transition-all duration-300';
        toast.innerHTML = `
            <div class="w-8 h-8 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div class="flex-1 text-xs">
                <div class="font-bold text-sm text-white flex items-center gap-1.5">
                    <span>${clientType === 'gmail' ? 'Gmail Web Ready' : 'Outlook Draft Ready'}</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-semibold">📎 Auto-Attached</span>
                </div>
                <p class="text-slate-300 mt-1 leading-relaxed">
                    ${clientType === 'gmail' 
                        ? `Opened Gmail compose to <strong class="text-white">${email}</strong> with pre-filled proposal. The PDF is ready in your browser.`
                        : `Draft with <strong class="text-white">${filename}</strong> automatically attached. Opening in Outlook—customer email has been automatically copied to your clipboard!`
                    }
                </p>
                ${clientType === 'outlook' ? `
                <div class="mt-2.5 pt-2 border-t border-slate-700/80 flex items-center justify-between gap-2">
                    <span class="text-[11px] text-amber-300 font-mono truncate max-w-[180px]">${email}</span>
                    <button type="button" onclick="navigator.clipboard.writeText('${email}'); this.innerText='✓ Copied!';" class="px-2 py-1 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-[10px] font-bold shrink-0 transition cursor-pointer">
                        Paste / Copy Email
                    </button>
                </div>
                ` : ''}
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white p-1 text-base leading-none cursor-pointer">&times;</button>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            if (toast && toast.parentElement) {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 400);
            }
        }, 12000);
    }

    function toggleQuickSmtpDrawer(forceOpen = null) {
        const drawer = document.getElementById('email-quick-smtp-drawer');
        if (!drawer) return;
        if (forceOpen === true) {
            drawer.classList.remove('hidden');
        } else if (forceOpen === false) {
            drawer.classList.add('hidden');
        } else {
            drawer.classList.toggle('hidden');
        }
    }

    async function executeEmailAppDispatch(clientType = 'outlook') {
        const emailInput = document.getElementById('email-modal-recipient');
        const email = emailInput ? emailInput.value.trim() : '';

        if (!email || !email.includes('@')) {
            crmAlert('Please enter a valid customer email address.');
            if (emailInput) {
                emailInput.focus();
                emailInput.classList.add('ring-2', 'ring-rose-500');
                setTimeout(() => emailInput.classList.remove('ring-2', 'ring-rose-500'), 3000);
            }
            return;
        }

        const subjectInput = document.getElementById('email-modal-subject');
        const subject = subjectInput ? subjectInput.value.trim() : `Official CCTV Quotation #${emailQuotationData.quoteNo} - Precision IT Systems`;

        const formatSelect = document.getElementById('email-modal-format');
        const pricingSelect = document.getElementById('email-modal-pricing');
        const format = formatSelect ? formatSelect.value : '1';
        const pricingMode = pricingSelect ? pricingSelect.value : 'itemized';

        const noteInput = document.getElementById('email-modal-custom-note');
        const customNote = noteInput ? noteInput.value.trim() : '';

        const safeCustomer = getCustomerSafeName();
        const filename = `Quotation_${emailQuotationData.quoteNo}_${safeCustomer}.pdf`;

        // Build clean email message body (Strictly proposal details, NO download links!)
        const dateStr = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        const formatName = emailQuotationData.formatNames[format] || `Format ${format}`;

        let body = `Dear ${emailQuotationData.customerName || 'Valued Customer'},\n\n`
                 + `Thank you for contacting Precision IT Systems. Please find your official ${formatName} details below:\n\n`
                 + `Quotation No: #${emailQuotationData.quoteNo}\n`
                 + `Date: ${dateStr}\n`
                 + `Valid Until: 15 days from issue\n`
                 + `Pricing Breakdown: ${pricingMode === 'total_only' ? 'Turnkey Total Cost' : 'Itemized with Product Costs'}\n\n`;

        if (customNote) {
            body += `Special Note:\n${customNote}\n\n`;
        }

        body += `----------------------------------------------\n`
              + `📎 The official signed PDF proposal document (${filename}) is attached to this email.\n`
              + `----------------------------------------------\n\n`
              + `Please feel free to reply directly to this email or call us if you have any questions.\n\n`
              + `Best Regards,\n`
              + `Precision IT Systems`;

        // 1. Outlook / Native Email App:
        if (clientType === 'outlook') {
            // Automatically copy customer email directly to system clipboard
            // When Outlook opens, user can instantly paste (Ctrl+V) into the To: field if needed!
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(email).catch(() => {});
            }

            // Trigger RFC-822 .eml draft file with embedded Base64 PDF attachment & pre-filled To: header!
            const emlUrl = `/quotations/${emailQuotationData.id}/download-eml?`
                + `email=${encodeURIComponent(email)}`
                + `&subject=${encodeURIComponent(subject)}`
                + `&pdf_format=${encodeURIComponent(format)}`
                + `&pricing_mode=${encodeURIComponent(pricingMode)}`
                + `&custom_note=${encodeURIComponent(customNote)}`;

            const downloadLink = document.createElement('a');
            downloadLink.href = emlUrl;
            downloadLink.download = `Draft_${filename.replace(/\.pdf$/i, '')}.eml`;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            setTimeout(() => downloadLink.remove(), 600);
            showEmailAppGuideToast(email, filename, 'outlook');
        } else {
            // Gmail Web
            downloadEmailModalPdf();
            const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(email)}&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            window.open(gmailUrl, '_blank');
            showEmailAppGuideToast(email, filename, 'gmail');

            // Copy clean proposal draft text to clipboard for Gmail
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(body).catch(() => {});
            }
        }

        // Background sync to CRM
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const formData = new FormData();
        formData.append('email', email);
        formData.append('subject', subject);
        formData.append('pdf_format', format);
        formData.append('pricing_mode', pricingMode);
        formData.append('custom_message', customNote);

        fetch(`/quotations/${emailQuotationData.id}/send-email`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        }).catch(() => {});

        setTimeout(() => closeEmailModal(), 1200);
    }

    async function executeServerSmtpDispatch() {
        const btn = document.getElementById('btn-server-smtp-dispatch');
        const originalHtml = btn ? btn.innerHTML : '';
        const emailInput = document.getElementById('email-modal-recipient');
        const email = emailInput ? emailInput.value.trim() : '';

        if (!email || !email.includes('@')) {
            crmAlert('Please enter a valid customer email address.');
            if (emailInput) emailInput.focus();
            return;
        }

        const subjectInput = document.getElementById('email-modal-subject');
        const subject = subjectInput ? subjectInput.value.trim() : `Official CCTV Quotation #${emailQuotationData.quoteNo}`;
        const formatSelect = document.getElementById('email-modal-format');
        const pricingSelect = document.getElementById('email-modal-pricing');
        const format = formatSelect ? formatSelect.value : '1';
        const pricingMode = pricingSelect ? pricingSelect.value : 'itemized';
        const noteInput = document.getElementById('email-modal-custom-note');
        const customNote = noteInput ? noteInput.value.trim() : '';

        // Check if quick SMTP credentials are provided
        const smtpUser = document.getElementById('quick-smtp-user')?.value.trim();
        const smtpPass = document.getElementById('quick-smtp-pass')?.value.trim();

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<span style="color:#ffffff !important;">Compiling & Sending PDF...</span>`;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const formData = new FormData();
            formData.append('email', email);
            formData.append('subject', subject);
            formData.append('pdf_format', format);
            formData.append('pricing_mode', pricingMode);
            formData.append('custom_message', customNote);

            if (smtpUser && smtpPass) {
                formData.append('smtp_username', smtpUser);
                formData.append('smtp_password', smtpPass);
            }

            const res = await fetch(`/quotations/${emailQuotationData.id}/send-email`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                if (btn) btn.innerHTML = `<span style="color:#ffffff !important;">✓ Email Dispatched (PDF Attached)!</span>`;
                setTimeout(() => {
                    closeEmailModal();
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                    window.location.reload();
                }, 1500);
            } else {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
                if (data.is_log_mode) {
                    toggleQuickSmtpDrawer(true);
                    const passInput = document.getElementById('quick-smtp-pass');
                    if (passInput) passInput.focus();
                    crmAlert('To send emails with the PDF attached directly to the customer, please enter your Gmail address and 16-character Google App Password in the setup box shown above.');
                } else {
                    crmAlert(data.message || 'Server email dispatch failed. ' + (data.error || 'Please check SMTP settings.'));
                }
            }
        } catch (e) {
            crmAlert('Server email connection failed: ' + (e.message || 'Network error'));
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    }

    function closeEmailModal() {
        const modal = document.getElementById('email-pdf-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('open');
        }
    }

    function openEmailModal() {
        const modal = document.getElementById('email-pdf-modal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('open');
            onEmailModalOptionsChange();
        }
    }

    async function copyQuotationEmailText() {
        const btn = document.getElementById('btn-copy-email-text');
        const emailBody = @json($quotation->getEmailFormattedBody());
        try {
            await navigator.clipboard.writeText(emailBody);
            if (btn) {
                const prev = btn.innerHTML;
                btn.innerHTML = 'Copied to Clipboard!';
                setTimeout(() => { btn.innerHTML = prev; }, 2000);
            }
        } catch (e) {
            crmAlert('Could not auto-copy to clipboard. Please copy manually.');
        }
    }

    // Close modals on backdrop click
    document.getElementById('reject-modal')?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
    document.getElementById('email-pdf-modal')?.addEventListener('click', function(e) {
        if (e.target === this) closeEmailModal();
    });
    document.getElementById('whatsapp-share-modal')?.addEventListener('click', function(e) {
        if (e.target === this) closeWhatsAppModal();
    });

    document.getElementById('btn-whatsapp-share')?.addEventListener('click', function(e) {
        e.preventDefault();
        openWhatsAppModal();
    });

    document.querySelectorAll('[onclick*="email-pdf-modal"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openEmailModal();
        });
    });

    @if(request()->has('open_email'))
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => openEmailModal(), 150);
    });
    @endif

    // Interactive Layout & Document Type Method Buttons Handler
    let showCurrentFormat = '{{ $quotation->pdf_format ?? '1' }}';
    let showCurrentPricingMode = '{{ $quotation->pricing_mode ?? 'itemized' }}';
    let showCurrentDocType = '{{ $quotation->doc_type ?? 'quotation' }}';
    const showQuotationId = '{{ $quotation->id }}';

    function setShowLayoutFormat(fmt) {
        showCurrentFormat = fmt;
        ['1', '2', '3'].forEach(f => {
            const tab = document.getElementById(`show-tab-fmt-${f}`);
            if (!tab) return;
            const dot = tab.querySelector('.tab-indicator');
            if (f === fmt) {
                tab.className = 'px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer bg-[#ca8a04] hover:bg-[#b45309] text-white font-bold shadow-xs';
                if (dot) dot.classList.remove('hidden');
            } else {
                tab.className = 'px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-600/60';
                if (dot) dot.classList.add('hidden');
            }
        });
        updateShowActionLinks();
        persistShowFormat();
    }

    function setShowDocOption(docType, mode) {
        showCurrentDocType = docType;
        showCurrentPricingMode = mode;
        const activeKey = docType === 'invoice' ? 'tax_invoice' : (mode === 'total_only' ? 'total_only' : 'itemized');
        ['itemized', 'total_only', 'tax_invoice'].forEach(k => {
            const tab = document.getElementById(`show-tab-mode-${k}`);
            if (!tab) return;
            const dot = tab.querySelector('.tab-indicator');
            if (k === activeKey) {
                tab.className = 'px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer bg-[#ca8a04] hover:bg-[#b45309] text-white font-bold shadow-xs';
                if (dot) dot.classList.remove('hidden');
            } else {
                tab.className = 'px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-600/60';
                if (dot) dot.classList.add('hidden');
            }
        });
        updateShowActionLinks();
        persistShowFormat();
    }

    function updateShowActionLinks() {
        const queryParams = `pricing_mode=${encodeURIComponent(showCurrentPricingMode)}&doc_type=${encodeURIComponent(showCurrentDocType)}`;
        const previewUrl = `/quotations/${showQuotationId}/preview/${showCurrentFormat}?${queryParams}`;
        const downloadUrl = `/quotations/${showQuotationId}/download-pdf/${showCurrentFormat}?${queryParams}`;

        const previewBtn = document.getElementById('show-preview-btn');
        const previewText = document.getElementById('show-preview-btn-text');
        const downloadBtn = document.getElementById('show-download-btn');
        const headerDownloadBtn = document.getElementById('header-download-pdf-btn');

        if (previewBtn) previewBtn.href = previewUrl;
        if (downloadBtn) downloadBtn.href = downloadUrl;
        if (headerDownloadBtn) headerDownloadBtn.href = downloadUrl;
        if (previewText) {
            previewText.innerText = showCurrentDocType === 'invoice' ? 'Preview Tax Invoice' : 'Preview Quotation';
        }

        // Also sync modal format dropdowns
        const waFormat = document.getElementById('wa-modal-format');
        if (waFormat) waFormat.value = showCurrentFormat;
        const emailFormat = document.getElementById('email-modal-format');
        if (emailFormat) emailFormat.value = showCurrentFormat;
    }

    function persistShowFormat() {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('pdf_format', showCurrentFormat);
        formData.append('pricing_mode', showCurrentPricingMode);
        formData.append('doc_type', showCurrentDocType);
        fetch(`/quotations/${showQuotationId}/set-format`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).catch(err => console.log('Sync error:', err));
    }
    </script>

    @if(auth()->user()->isAdmin())
    {{-- Admin HSN/SAC Code Quick Manager Modal --}}
    <div id="hsn-manager-modal" style="display:none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-slate-900 rounded-[4px] border border-slate-200 dark:border-slate-800 shadow-xl max-w-md w-full p-5 space-y-4 text-slate-900 dark:text-slate-100">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Manage HSN / SAC Code') }}</h3>
                <button type="button" onclick="closeHsnEditModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block mb-0.5">{{ __('Item / Product:') }}</span>
                <span id="hsn-modal-item-name" class="font-bold text-sm text-slate-900 dark:text-white block"></span>
            </div>

            <form id="hsn-modal-form" method="POST" onsubmit="submitHsnForm(event)" class="space-y-4">
                @csrf
                <div>
                    <label for="hsn-modal-input" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('HSN / SAC Code (2 to 8 digits only):') }}
                    </label>
                    <input type="text"
                           id="hsn-modal-input"
                           name="hsn_code"
                           maxlength="8"
                           pattern="[0-9]{2,8}"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)"
                           placeholder="e.g. 85258900"
                           class="w-full font-mono font-bold text-sm px-3 h-[38px] rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        {{ __('Numbers only. Maximum 8 digits allowed (no 9+ digits).') }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="hsn-modal-update-product" name="update_product" value="1" checked
                           class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="hsn-modal-update-product" class="text-xs text-slate-700 dark:text-slate-300 cursor-pointer select-none">
                        {{ __('Update master product catalog (apply to all products project-wide)') }}
                    </label>
                </div>

                <div id="hsn-modal-error" class="hidden text-xs font-semibold text-rose-600 dark:text-rose-400"></div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" id="hsn-modal-delete-btn" onclick="executeHsnDelete()"
                            class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 hover:underline cursor-pointer">
                        {{ __('Delete HSN Code') }}
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeHsnEditModal()"
                                class="h-[38px] px-3 rounded-[4px] border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                class="btn-primary h-[38px] px-4 rounded-[4px] text-xs font-bold cursor-pointer">
                            {{ __('Save HSN Code') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentHsnItemId = null;
        function openHsnEditModal(itemId, itemName, currentHsn, hasProduct) {
            currentHsnItemId = itemId;
            document.getElementById('hsn-modal-item-name').innerText = itemName;
            const input = document.getElementById('hsn-modal-input');
            input.value = currentHsn || '';
            document.getElementById('hsn-modal-error').classList.add('hidden');
            document.getElementById('hsn-modal-error').innerText = '';

            const delBtn = document.getElementById('hsn-modal-delete-btn');
            if (currentHsn) {
                delBtn.classList.remove('hidden');
            } else {
                delBtn.classList.add('hidden');
            }

            document.getElementById('hsn-manager-modal').style.display = 'flex';
            setTimeout(() => input.focus(), 50);
        }

        function closeHsnEditModal() {
            document.getElementById('hsn-manager-modal').style.display = 'none';
            currentHsnItemId = null;
        }

        async function submitHsnForm(e) {
            e.preventDefault();
            if (!currentHsnItemId) return;

            const inputVal = document.getElementById('hsn-modal-input').value.trim();
            const updateProduct = document.getElementById('hsn-modal-update-product').checked;
            const errBox = document.getElementById('hsn-modal-error');

            if (inputVal && (!/^[0-9]{2,8}$/.test(inputVal))) {
                errBox.innerText = 'The HSN/SAC code must be numeric and between 2 to 8 digits maximum (no 9+ digits).';
                errBox.classList.remove('hidden');
                return;
            }

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/quotation-items/${currentHsnItemId}/hsn`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        hsn_code: inputVal,
                        update_product: updateProduct ? 1 : 0
                    })
                });

                const data = await res.json();
                if (!res.ok) {
                    errBox.innerText = data.message || (data.errors && data.errors.hsn_code ? data.errors.hsn_code[0] : 'Error updating HSN code.');
                    errBox.classList.remove('hidden');
                    return;
                }

                closeHsnEditModal();
                window.location.reload();
            } catch (err) {
                errBox.innerText = 'Request failed. Please check network connection.';
                errBox.classList.remove('hidden');
            }
        }

        async function confirmDeleteHsn(itemId, itemName) {
            if (!confirm(`Are you sure you want to delete / clear the HSN/SAC code for "${itemName}"?`)) return;
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/quotation-items/${itemId}/hsn`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    }
                });
                window.location.reload();
            } catch (e) {
                alert('Failed to delete HSN code.');
            }
        }

        function executeHsnDelete() {
            if (!currentHsnItemId) return;
            const itemName = document.getElementById('hsn-modal-item-name').innerText;
            confirmDeleteHsn(currentHsnItemId, itemName);
        }
    </script>
    @endif

</x-app-layout>