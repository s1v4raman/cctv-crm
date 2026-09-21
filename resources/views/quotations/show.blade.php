<x-app-layout>
    @php
        $statusColors = [
            'draft'    => ['badge' => 'border-slate-600 bg-slate-800/80 text-slate-300', 'bar' => '#64748b', 'glow' => 'rgba(100,116,139,0.3)'],
            'sent'     => ['badge' => 'border-sky-500/30 bg-sky-500/10 text-sky-400', 'bar' => '#38bdf8', 'glow' => 'rgba(56,189,248,0.3)'],
            'accepted' => ['badge' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400', 'bar' => '#34d399', 'glow' => 'rgba(52,211,153,0.3)'],
            'rejected' => ['badge' => 'border-rose-500/30 bg-rose-500/10 text-rose-400', 'bar' => '#f43f5e', 'glow' => 'rgba(244,63,94,0.3)'],
            'expired'  => ['badge' => 'border-amber-500/30 bg-amber-500/10 text-amber-400', 'bar' => '#f59e0b', 'glow' => 'rgba(245,158,11,0.3)'],
        ];
        $sc = $statusColors[$quotation->status] ?? $statusColors['draft'];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-blue-600 dark:bg-blue-400 animate-ping"></span>
                    <h2 class="text-2xl font-black tracking-wider text-slate-900 dark:text-white uppercase font-['Outfit']">{{ $quotation->quotation_no }}</h2>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2 font-mono">
                    <a href="{{ route('leads.show', $quotation->lead) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-bold transition">
                        {{ $quotation->lead->customer_name }}
                    </a>
                    <span class="text-slate-400">&bull;</span>
                    <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ $quotation->lead->phone }}</span>
                </p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $sc['badge'] }}">
                    <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $sc['bar'] }}"></span>
                    {{ ucfirst($quotation->status) }}
                </span>
                <a href="{{ route('quotations.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition shadow-2xs">
                    &larr; All Quotations
                </a>
                <a href="{{ route('leads.show', $quotation->lead) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition shadow-2xs">
                    &larr; Lead File
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .cyber-card {
            background: #0F172A;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 1rem;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
            overflow: hidden;
            margin-bottom: 1.25rem;
        }
        .action-bar-glow {
            border-left: 4px solid {{ $sc['bar'] }};
            background: linear-gradient(90deg, {{ $sc['glow'] }} 0%, transparent 60%);
        }
        #reject-modal.open, #email-pdf-modal.open, #whatsapp-share-modal.open {
            display: flex !important;
        }
        @media print {
            body { background: #fff !important; color: #000 !important; font-size: 11pt; }
            nav, header, .cyber-card:first-of-type, .btn, .btn-remove, #reject-modal, #email-pdf-modal, #whatsapp-share-modal, a[href*="quotations"], a[href*="leads"] {
                display: none !important;
            }
            .cyber-card { border: none !important; box-shadow: none !important; background: #fff !important; color: #000 !important; }
            .cyber-card * { color: #000 !important; }
        }
    </style>

    <div class="py-8 bg-[#060913] min-h-screen text-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm font-semibold flex items-center gap-3">
                    <span class="text-base">✓</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-400 text-sm font-semibold flex items-center gap-3">
                    <span class="text-base">✕</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Status Action Bar --}}
            <div class="cyber-card">
                <div class="action-bar-glow p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-500 dark:text-slate-400">Current Lifecycle State</div>
                        <div class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-wider font-heading mt-0.5">
                            {{ str_replace('_',' ',$quotation->status) }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- PDF Downloads -->
                        <a href="{{ route('quotations.pdf', [$quotation, '1']) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold transition shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Format 1 (PITS)
                        </a>
                        <a href="{{ route('quotations.pdf', [$quotation, '2']) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold transition shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Format 2 (NPS)
                        </a>
                        <a href="{{ route('quotations.pdf', [$quotation, '3']) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold transition shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Format 3 (ACS)
                        </a>

                        {{-- DRAFT actions --}}
                        @if($quotation->status === 'draft')
                            <a href="{{ route('quotations.edit', $quotation) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 text-xs font-bold transition shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                Edit Quotation
                            </a>
                            <form method="POST" action="{{ route('quotations.markSent', $quotation) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#2563eb] hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                    Mark as Sent
                                </button>
                            </form>
                            <form method="POST" action="{{ route('quotations.destroy', $quotation) }}"
                                  onsubmit="return confirm('Delete {{ addslashes($quotation->quotation_no) }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-xs font-bold transition shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    Delete
                                </button>
                            </form>
                        @endif

                        {{-- DRAFT or SENT actions for accepting/rejecting --}}
                        @if(in_array($quotation->status, ['draft', 'sent']))
                            <form method="POST" action="{{ route('quotations.accept', $quotation) }}">
                                @csrf
                                <button type="submit" onclick="return confirm('Confirm customer accepted this quotation?')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    Mark Accepted
                                </button>
                            </form>
                            <button type="button" onclick="document.getElementById('reject-modal').classList.add('open')" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-xs font-bold transition shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Mark Rejected
                            </button>
                        @endif

                        {{-- ACCEPTED actions --}}
                        @if($quotation->status === 'accepted')
                            @if($quotation->installationJob)
                                <a href="{{ route('jobs.show', $quotation->installationJob) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-xs font-bold transition shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/></svg>
                                    View Job #{{ $quotation->installationJob->job_no }}
                                </a>
                            @else
                                <form method="POST" action="{{ route('jobs.store', $quotation) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold transition shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        Create Installation Job
                                    </button>
                                </form>
                            @endif
                        @endif

                    </div>
                </div>

                @if($quotation->status === 'rejected' && $quotation->rejection_reason)
                    <div class="m-5 p-4 rounded-xl border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10">
                        <p class="text-xs font-bold text-rose-700 dark:text-rose-400 uppercase font-mono tracking-wider mb-1">Rejection Reason</p>
                        <p class="text-sm text-rose-800 dark:text-rose-200">{{ $quotation->rejection_reason }}</p>
                    </div>
                @endif
            </div>

            {{-- Sharing & Communication Bar --}}
            <div class="cyber-card">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-white/5 flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 font-heading flex items-center gap-2">
                        <span class="text-blue-600 dark:text-blue-400">⚡</span> Share & Dispatch Proposal
                    </h3>
                    <span class="text-xs font-mono text-slate-500 dark:text-slate-400">1-Click Multi-Channel Actions</span>
                </div>
                <div class="p-5 flex flex-wrap gap-3">
                    
                    {{-- Unified Interactive WhatsApp Share --}}
                    <button type="button" id="btn-whatsapp-share" 
                            onclick="openWhatsAppModal()" 
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold text-xs tracking-wide transition shadow-xs">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.458 5.704 1.459h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413"/>
                        </svg>
                        Send on WhatsApp ({{ $quotation->getWhatsAppPhone() ?: 'Add Phone' }})
                    </button>

                    {{-- 1-Click Save Contact to Phone (.vcf) --}}
                    <a href="{{ route('quotations.vcard', $quotation) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 font-bold text-xs tracking-wide transition shadow-2xs">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.765z"/>
                        </svg>
                        Save Contact to Phone
                    </a>

                    {{-- Email Share with Attached PDF --}}
                    <button type="button" onclick="document.getElementById('email-pdf-modal').classList.add('open')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#2563eb] hover:bg-blue-700 text-white font-bold text-xs tracking-wide transition shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                        </svg>
                        Email PDF ({{ $quotation->lead->email ?: 'Add Email' }})
                    </button>

                    {{-- Call Customer --}}
                    <a href="tel:{{ $quotation->lead->phone }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 font-bold text-xs tracking-wide transition shadow-2xs">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.387a20.373 20.373 0 01-9.168-9.167c-.154-.44.01-1.09.387-1.373l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                        </svg>
                        Call {{ $quotation->lead->phone }}
                    </a>

                    {{-- Online Advance Payment Link --}}
                    <a href="{{ route('payment.checkout.quotation', $quotation) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs tracking-wide transition shadow-xs" title="Open or share online advance payment checkout page with customer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                        </svg>
                        Pay Advance (Razorpay / UPI)
                    </a>

                    {{-- Print PDF --}}
                    <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 font-bold text-xs tracking-wide transition shadow-2xs">
                        <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.617 0-1.11-.502-1.096-1.118L6.34 18m11.32 0A12.318 12.318 0 008.625 18M18 14a3 3 0 003-3V7a3 3 0 00-3-3H6a3 3 0 00-3 3v4a3 3 0 003 3m12 0h.008v.008H18V14zm-12 0h.008v.008H6V14z"/>
                        </svg>
                        Print PDF
                    </button>
                    
                </div>
            </div>

            {{-- Quotation Details Grid --}}
            <div class="cyber-card">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-white/5 flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 font-heading">Specifications & Overview</h3>
                    @if($quotation->status === 'draft')
                        <a href="{{ route('quotations.edit', $quotation) }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">Edit Proposal &rarr;</a>
                    @endif
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 p-6">
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-500 dark:text-slate-400">Quotation Identifier</div>
                        <div class="text-base font-bold text-slate-900 dark:text-white font-mono mt-1">{{ $quotation->quotation_no }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-500 dark:text-slate-400">Date Issued</div>
                        <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mt-1">{{ $quotation->quotation_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-500 dark:text-slate-400">Validity Horizon</div>
                        <div class="text-sm font-semibold mt-1 {{ $quotation->valid_until?->isPast() ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-700 dark:text-slate-300' }}">
                            {{ $quotation->valid_until?->format('d M Y') ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-500 dark:text-slate-400">Client / Organization</div>
                        <div class="text-sm font-bold text-slate-900 dark:text-white mt-1">
                            <a href="{{ route('leads.show', $quotation->lead) }}" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">
                                {{ $quotation->lead->customer_name }}
                            </a>
                        </div>
                    </div>
                    @if($quotation->lead->site_address)
                    <div class="col-span-2 md:col-span-4 pt-3 border-t border-slate-200 dark:border-white/5">
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-500 dark:text-slate-400">Installation Facility Address</div>
                        <div class="text-xs text-slate-700 dark:text-slate-300 mt-1 font-mono leading-relaxed">{{ $quotation->lead->site_address }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Line Items --}}
            <div class="cyber-card">
                <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit']">
                        Hardware & Service Itemization ({{ $quotation->items->count() }})
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/5 bg-[#0B1120] text-[10px] font-mono uppercase tracking-widest text-slate-400">
                                <th class="py-3 px-4">#</th>
                                <th class="py-3 px-4">Item / Product</th>
                                <th class="py-3 px-4">Description / Specs</th>
                                <th class="py-3 px-4 text-right">Qty</th>
                                <th class="py-3 px-4">Unit</th>
                                <th class="py-3 px-4 text-right">Unit Price</th>
                                <th class="py-3 px-4 text-right">Subtotal</th>
                                @if($quotation->status === 'draft') <th class="py-3 px-4 text-center">Action</th> @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-xs text-slate-300">
                            @foreach ($quotation->items as $i => $item)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3.5 px-4 text-slate-500 font-mono">{{ $i + 1 }}</td>
                                    <td class="py-3.5 px-4 font-bold text-white">{{ $item->item_name }}</td>
                                    <td class="py-3.5 px-4 text-slate-400 max-w-xs">{{ $item->description ?? '—' }}</td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-sky-400">{{ $item->quantity }}</td>
                                    <td class="py-3.5 px-4 text-slate-400">{{ $item->unit ?? '—' }}</td>
                                    <td class="py-3.5 px-4 text-right font-mono text-slate-300">₹{{ number_format((float)$item->unit_price, 2) }}</td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-amber-400">₹{{ number_format((float)$item->total, 2) }}</td>
                                    @if($quotation->status === 'draft')
                                        <td class="py-3.5 px-4 text-center">
                                            <form method="POST" action="{{ route('quotation-items.destroy', $item) }}"
                                                  onsubmit="return confirm('Remove this item?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-rose-400 hover:text-rose-300 font-semibold text-[11px] underline">Remove</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Totals --}}
                <div class="p-6 bg-[#0B1120] border-t border-white/5 flex flex-col md:flex-row justify-between gap-6">
                    <div class="flex-1">
                        @if($quotation->notes)
                            <div class="p-4 rounded-xl border border-white/10 bg-[#060913] text-xs text-slate-400 leading-relaxed">
                                <span class="font-bold text-slate-200 block mb-1 font-mono uppercase tracking-wider">Terms & Engineering Notes:</span>
                                {{ $quotation->notes }}
                            </div>
                        @endif
                    </div>
                    <div class="w-full md:w-80 bg-[#060913] border border-white/10 rounded-xl p-5">
                        <div class="flex justify-between items-center text-xs text-slate-400 mb-2">
                            <span>Subtotal</span>
                            <span class="font-mono text-slate-200">₹{{ number_format((float)$quotation->subtotal, 2) }}</span>
                        </div>
                        @if($quotation->discount > 0)
                        <div class="flex justify-between items-center text-xs text-rose-400 mb-2">
                            <span>Discount</span>
                            <span class="font-mono font-bold">−₹{{ number_format((float)$quotation->discount, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center text-xs text-slate-400 mb-3 pb-3 border-b border-white/10">
                            <span>GST ({{ $quotation->tax_percent }}%)</span>
                            <span class="font-mono text-slate-200">₹{{ number_format((float)$quotation->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-white uppercase font-mono tracking-wider">Grand Total</span>
                            <span class="text-xl font-black text-amber-400 font-mono">₹{{ number_format((float)$quotation->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status History --}}
            <div class="cyber-card">
                <div class="px-5 py-4 border-b border-white/5">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit']">Audit Trail & Lifecycle History</h3>
                </div>
                <div class="divide-y divide-white/5">
                    @forelse ($quotation->statusHistories as $h)
                        <div class="p-4 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-white flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                    {{ $h->old_status ? ucfirst($h->old_status) . ' → ' : 'Created as ' }}{{ ucfirst($h->new_status) }}
                                </div>
                                @if($h->remarks)
                                    <div class="text-[11px] text-slate-400 mt-1 pl-3.5">{{ $h->remarks }}</div>
                                @endif
                            </div>
                            <div class="text-[11px] font-mono text-slate-500 whitespace-nowrap">
                                {{ $h->changedBy->name ?? 'System' }} &bull; {{ $h->created_at->format('d M Y, h:i A') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-slate-500 text-xs font-mono">No status changes recorded.</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="reject-modal" style="display:none;position:fixed;inset:0;z-index:50;background:rgba(6,9,19,0.8);backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:1rem;">
        <div class="bg-[#0F172A] border border-rose-500/30 rounded-2xl p-6 max-w-md w-full shadow-2xl">
            <h4 class="text-lg font-bold text-white uppercase font-['Outfit'] mb-1">Reject Quotation</h4>
            <p class="text-xs text-slate-400 mb-4">Please provide the reason why the customer rejected this quotation.</p>
            <form method="POST" action="{{ route('quotations.reject', $quotation) }}">
                @csrf
                <textarea name="rejection_reason" class="w-full bg-[#060913] border border-slate-700 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none min-h-[100px] mb-4" required
                          placeholder="e.g. Price too high, customer chose another vendor…"></textarea>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('reject-modal').classList.remove('open')"
                            class="px-4 py-2 rounded-xl border border-slate-700 bg-slate-800 text-slate-300 text-xs font-bold hover:bg-slate-700 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Send PDF via Email & Outlook Modal --}}
    <div id="email-pdf-modal" style="display:none;position:fixed;inset:0;z-index:50;background:rgba(6,9,19,0.85);backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:1rem;">
        <div class="bg-[#0F172A] border border-white/10 rounded-2xl p-6 max-w-xl w-full shadow-2xl max-h-[90vh] overflow-y-auto text-slate-200">
            <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center text-lg">📧</div>
                    <div>
                        <h4 class="text-lg font-bold text-white font-['Outfit'] uppercase">Email Quotation</h4>
                        <span class="text-xs text-slate-400">Server SMTP Delivery or Outlook Compose</span>
                    </div>
                </div>
                <button type="button" onclick="closeEmailModal()" class="text-slate-400 hover:text-white text-xl">✕</button>
            </div>

            {{-- 1-Click Outlook & Quick Action Buttons --}}
            <div class="bg-[#060913] border border-white/10 rounded-xl p-4 mb-5">
                <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-3 flex items-center justify-between">
                    <span>⚡ 1-Click External Email Clients</span>
                    <span class="text-sky-400 font-bold">Includes PDF Link</span>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <a href="{{ $quotation->getOutlookComposeUrl() }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs flex items-center justify-center gap-2 transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M7.4 20.3L2 18.2V5.8L7.4 3.7v16.6zM22 6.5v11l-13 2.5V4l13 2.5z"/></svg>
                        Outlook 365
                    </a>
                    <a href="{{ $quotation->getMailtoUrl() }}" class="px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs flex items-center justify-center gap-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        Default Mail App
                    </a>
                </div>
                <button type="button" onclick="copyQuotationEmailText()" id="btn-copy-email-text" class="w-full py-2 rounded-lg border border-slate-700 bg-slate-800/60 hover:bg-slate-800 text-slate-300 text-xs font-semibold transition flex items-center justify-center gap-2">
                    📋 Copy Email Message & PDF Link
                </button>
            </div>

            <div class="relative text-center my-4">
                <hr class="border-white/10">
                <span class="relative -top-2.5 bg-[#0F172A] px-3 text-[10px] font-mono uppercase tracking-widest text-slate-500">Or Send via Direct SMTP</span>
            </div>

            <form method="POST" action="{{ route('quotations.send-email', $quotation) }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">Recipient Email *</label>
                    <input type="email" name="email" required value="{{ old('email', $quotation->lead->email) }}" placeholder="client@example.com"
                           class="w-full bg-[#060913] border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                </div>

                <div class="mb-4">
                    <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">Subject *</label>
                    <input type="text" name="subject" required value="{{ $quotation->getEmailSubject() }}"
                           class="w-full bg-[#060913] border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">PDF Template Style</label>
                        <select name="pdf_format" class="w-full bg-[#060913] border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                            <option value="1">Executive Modern (Format 1)</option>
                            <option value="2">Technical Detailed (Format 2)</option>
                            <option value="3">Classic Formal (Format 3)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">Attachment</label>
                        <div class="bg-[#060913] p-2.5 border border-dashed border-slate-700 rounded-xl text-xs text-slate-400 font-mono">
                            📎 Quotation-{{ $quotation->quotation_no }}.pdf
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">Custom Notes for Customer</label>
                    <textarea name="custom_message" rows="2" placeholder="e.g. As discussed during our survey, includes 2-year AMC..."
                              class="w-full bg-[#060913] border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-white/10">
                    <a href="{{ route('alerts.gateways') }}" target="_blank" class="text-xs text-amber-400 hover:underline font-semibold font-mono">
                        ⚙️ SMTP Settings
                    </a>
                    <div class="flex gap-3">
                        <button type="button" onclick="closeEmailModal()" class="px-4 py-2 rounded-xl border border-slate-700 bg-slate-800 text-slate-300 text-xs font-bold hover:bg-slate-700 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold transition shadow-lg shadow-sky-600/20">
                            🚀 Send Email
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Send Quotation via WhatsApp & Multi-Channel Modal --}}
    <div id="whatsapp-share-modal" style="display:none;position:fixed;inset:0;z-index:50;background:rgba(6,9,19,0.75);backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:1rem;">
        <div class="bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 max-w-2xl w-full shadow-2xl max-h-[94vh] overflow-y-auto text-slate-900 dark:text-slate-100">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.458 5.704 1.459h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-extrabold text-slate-900 dark:text-white font-heading uppercase">WhatsApp Proposal Dispatcher</h4>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">Quotation #{{ $quotation->quotation_no }} &bull; Total: <strong class="text-slate-900 dark:text-white font-bold">₹{{ number_format((float)$quotation->total, 2) }}</strong></span>
                    </div>
                </div>
                <button type="button" onclick="closeWhatsAppModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white text-xl" title="Close">✕</button>
            </div>

            {{-- Condition Status Banner --}}
            @php
                $hasStoredPhone = !empty($quotation->getWhatsAppPhone());
            @endphp
            <div id="wa-customer-status-banner" class="mb-4 p-3.5 rounded-xl border flex items-center justify-between gap-3 {{ $hasStoredPhone ? 'border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-900 dark:text-emerald-300' : 'border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 text-amber-900 dark:text-amber-300' }}">
                <div class="flex items-center gap-2.5">
                    <span id="wa-status-icon" class="text-base">{{ $hasStoredPhone ? '✅' : '⚠️' }}</span>
                    <div>
                        <div id="wa-status-title" class="text-xs font-bold uppercase tracking-wider font-mono {{ $hasStoredPhone ? 'text-emerald-900 dark:text-emerald-200' : 'text-amber-900 dark:text-amber-200' }}">
                            {{ $hasStoredPhone ? 'Customer Contact Ready & Verified' : 'Customer Phone Number Not Stored' }}
                        </div>
                        <div id="wa-status-desc" class="text-[11px] {{ $hasStoredPhone ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400' }} mt-0.5">
                            {{ $hasStoredPhone 
                                ? 'Target WhatsApp chat will open for ' . ($quotation->lead?->customer_name ?? 'Customer') . ' (' . $quotation->getWhatsAppPhone() . '). PDF will be downloaded & text pre-filled.' 
                                : 'Enter mobile number below. Saving will update the CRM customer record and open WhatsApp.' }}
                        </div>
                    </div>
                </div>
                <a href="{{ route('quotations.vcard', $quotation) }}" id="btn-header-vcard" class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-[11px] font-bold whitespace-nowrap hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-2xs">
                    📲 vCard
                </a>
            </div>

            {{-- Step 1: Format Selection --}}
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 font-heading">
                        <span class="w-4 h-4 rounded-full bg-blue-600 text-white font-black flex items-center justify-center text-[10px]">1</span>
                        Choose Quotation PDF Format & Style
                    </label>
                    <a id="btn-preview-selected-pdf" href="{{ route('quotations.public-pdf', ['quotation' => $quotation, 'format' => '1']) }}" target="_blank" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                        👁️ Preview Live PDF
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-3" id="wa-format-selector-grid">
                    <label class="wa-format-card border-2 border-blue-600 bg-blue-50/70 dark:bg-blue-950/40 rounded-xl p-3 cursor-pointer transition relative block shadow-2xs" data-format="1">
                        <input type="radio" name="wa_pdf_format" value="1" checked class="absolute top-3 right-3 accent-blue-600" onchange="onWhatsAppFormatChange('1')">
                        <div class="text-lg mb-1">🌟</div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">Executive Modern</div>
                        <div class="text-[10px] text-blue-700 dark:text-blue-400 font-mono font-bold mt-0.5">Format 1 (Standard)</div>
                    </label>

                    <label class="wa-format-card border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/40 rounded-xl p-3 cursor-pointer transition relative block" data-format="2">
                        <input type="radio" name="wa_pdf_format" value="2" class="absolute top-3 right-3 accent-blue-600" onchange="onWhatsAppFormatChange('2')">
                        <div class="text-lg mb-1">📋</div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">Technical Detailed</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">Format 2</div>
                    </label>

                    <label class="wa-format-card border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/40 rounded-xl p-3 cursor-pointer transition relative block" data-format="3">
                        <input type="radio" name="wa_pdf_format" value="3" class="absolute top-3 right-3 accent-blue-600" onchange="onWhatsAppFormatChange('3')">
                        <div class="text-lg mb-1">🏛️</div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">Classic Formal</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">Format 3</div>
                    </label>
                </div>
            </div>

            {{-- Step 2: Contact Details --}}
            <div class="bg-slate-50 dark:bg-[#060913] border border-slate-200 dark:border-slate-800 rounded-xl p-4 mb-4">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 font-heading">
                        <span class="w-4 h-4 rounded-full bg-blue-600 text-white font-black flex items-center justify-center text-[10px]">2</span>
                        Customer Contact & Target Mobile
                    </label>
                    <span class="text-[10px] font-mono font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 px-2 py-0.5 rounded border border-blue-200 dark:border-blue-800">
                        Auto-syncs CRM
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1 font-bold">Customer Name</label>
                        <input type="text" id="wa-customer-name" value="{{ $quotation->lead?->customer_name ?? 'Valued Customer' }}"
                               placeholder="e.g. John Doe"
                               oninput="onWhatsAppNameInput(this.value)"
                               class="w-full bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white font-semibold focus:border-blue-500 focus:outline-none shadow-2xs">
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1 font-bold">WhatsApp Phone *</label>
                        <input type="tel" id="wa-customer-phone" value="{{ $quotation->getWhatsAppPhone() ?: $quotation->lead?->phone }}" placeholder="e.g. 9876543210"
                               oninput="onWhatsAppPhoneInput(this.value)"
                               class="w-full bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white font-mono font-bold focus:border-blue-500 focus:outline-none shadow-2xs">
                    </div>
                </div>

                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-700 dark:text-slate-300">
                    <span class="flex items-center gap-2">
                        <span>🎯</span>
                        <span>Target: <strong><span id="wa-phone-preview-badge" class="text-blue-600 dark:text-blue-400 font-mono font-bold">{{ $quotation->getWhatsAppPhone() ?: 'Customer Number' }}</span></strong></span>
                    </span>
                    <button type="button" onclick="quickSaveCustomerContact()" id="btn-quick-save-contact" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] transition shadow-xs">
                        💾 Save Contact
                    </button>
                </div>
            </div>

            {{-- Step 3: Message Text --}}
            <div class="mb-5">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 font-heading">
                        <span class="w-4 h-4 rounded-full bg-blue-600 text-white font-black flex items-center justify-center text-[10px]">3</span>
                        WhatsApp Proposal Message Preview
                    </label>
                    <button type="button" onclick="copyWhatsAppCustomText()" id="btn-copy-wa-text" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                        📋 Copy Message
                    </button>
                </div>
                <textarea id="wa-custom-message" rows="4"
                          class="w-full bg-white dark:bg-[#060913] border border-slate-200 dark:border-slate-700 rounded-xl p-3 text-xs text-slate-900 dark:text-white font-mono focus:border-blue-500 focus:outline-none shadow-2xs">{{ $quotation->getWhatsAppFormattedMessage('1') }}</textarea>
            </div>

            {{-- Action Dispatch Buttons --}}
            <div class="flex flex-col gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" id="btn-do-whatsapp-dispatch" onclick="executeWhatsAppShareWithPdf()" 
                        class="w-full py-3 rounded-xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold text-sm uppercase tracking-wider flex items-center justify-center gap-2 shadow-sm transition">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.458 5.704 1.459h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413"/>
                    </svg>
                    <span id="btn-wa-dispatch-text">{{ $hasStoredPhone ? 'Share PDF & Open WhatsApp Chat' : 'Save Customer & Share WhatsApp PDF' }}</span>
                </button>

                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="openWhatsAppDirect('web')" class="py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-700 transition shadow-2xs">
                        🌐 Web WhatsApp
                    </button>
                    <button type="button" onclick="openWhatsAppDirect('app')" class="py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-700 transition shadow-2xs">
                        💻 Desktop App
                    </button>
                    <button type="button" onclick="downloadSelectedPdfOnly()" class="py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-700 transition shadow-2xs">
                        📥 Download PDF
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
    // State for Quotation WhatsApp Dispatcher
    const waQuotationData = {
        id: {{ $quotation->id }},
        quoteNo: @json($quotation->quotation_no),
        customerName: @json($quotation->lead?->customer_name ?? 'Customer'),
        defaultPhone: @json($quotation->getWhatsAppPhone()),
        selectedFormat: '1',
        messages: {
            '1': @json($quotation->getWhatsAppFormattedMessage('1')),
            '2': @json($quotation->getWhatsAppFormattedMessage('2')),
            '3': @json($quotation->getWhatsAppFormattedMessage('3')),
        },
        pdfUrls: {
            '1': @json(route('quotations.pdf', [$quotation, 1])),
            '2': @json(route('quotations.pdf', [$quotation, 2])),
            '3': @json(route('quotations.pdf', [$quotation, 3])),
        },
        previewUrls: {
            '1': @json(route('quotations.public-pdf', ['quotation' => $quotation, 'format' => '1'])),
            '2': @json(route('quotations.public-pdf', ['quotation' => $quotation, 'format' => '2'])),
            '3': @json(route('quotations.public-pdf', ['quotation' => $quotation, 'format' => '3'])),
        },
        vcardUrl: @json(route('quotations.vcard', $quotation)),
        sendUrl: @json(route('quotations.send-whatsapp', $quotation))
    };

    function openWhatsAppModal() {
        const modal = document.getElementById('whatsapp-share-modal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('open');
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
        waQuotationData.selectedFormat = format;

        // Update card styles
        document.querySelectorAll('#wa-format-selector-grid .wa-format-card').forEach(card => {
            const cardFormat = card.getAttribute('data-format');
            if (cardFormat === format) {
                card.className = 'wa-format-card border border-emerald-500/60 bg-emerald-500/10 rounded-xl p-3 cursor-pointer transition relative block';
            } else {
                card.className = 'wa-format-card border border-slate-700 bg-[#060913] rounded-xl p-3 cursor-pointer transition relative block';
            }
        });

        // Update live PDF Preview Link
        const previewBtn = document.getElementById('btn-preview-selected-pdf');
        if (previewBtn && waQuotationData.previewUrls[format]) {
            previewBtn.href = waQuotationData.previewUrls[format];
        }

        // Update Message textarea
        const msgBox = document.getElementById('wa-custom-message');
        if (msgBox && waQuotationData.messages[format]) {
            msgBox.value = waQuotationData.messages[format];
        }
    }

    function onWhatsAppNameInput(val) {
        waQuotationData.customerName = val.trim() || 'Customer';
        syncCustomerStatusUI();
    }

    function onWhatsAppPhoneInput(val) {
        const clean = val.replace(/[^0-9]/g, '');
        const badge = document.getElementById('wa-phone-preview-badge');
        if (badge) {
            badge.innerText = clean || 'Customer Number';
        }
        syncCustomerStatusUI();
    }

    function getNormalizedPhone() {
        const input = document.getElementById('wa-customer-phone');
        let phone = (input ? input.value : '').replace(/[^0-9]/g, '');
        if (phone.length === 10) {
            phone = '91' + phone;
        }
        return phone;
    }

    function syncCustomerStatusUI() {
        const phone = getNormalizedPhone();
        const hasPhone = phone.length >= 10;
        const banner = document.getElementById('wa-customer-status-banner');
        const icon = document.getElementById('wa-status-icon');
        const title = document.getElementById('wa-status-title');
        const desc = document.getElementById('wa-status-desc');
        const btnText = document.getElementById('btn-wa-dispatch-text');
        const nameInput = document.getElementById('wa-customer-name');
        const name = (nameInput ? nameInput.value : '').trim() || waQuotationData.customerName || 'Customer';

        if (hasPhone) {
            if (banner) {
                banner.className = 'mb-4 p-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-300 flex items-center justify-between gap-3';
            }
            if (icon) icon.innerText = '✅';
            if (title) title.innerText = 'Customer Contact Ready & Verified';
            if (desc) desc.innerText = `Target WhatsApp chat will open for ${name} (+${phone}). PDF will be downloaded & text pre-filled.`;
            if (btnText) btnText.innerText = '🚀 Share PDF & Open WhatsApp Chat';
        } else {
            if (banner) {
                banner.className = 'mb-4 p-3 rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-300 flex items-center justify-between gap-3';
            }
            if (icon) icon.innerText = '⚠️';
            if (title) title.innerText = 'Customer Phone Number Not Stored';
            if (desc) desc.innerText = 'Please enter mobile number above. Clicking below will save the customer in CRM and open WhatsApp.';
            if (btnText) btnText.innerText = '💾 Save Customer & Share WhatsApp PDF';
        }
    }

    async function quickSaveCustomerContact() {
        const btn = document.getElementById('btn-quick-save-contact');
        const originalText = btn ? btn.innerHTML : '';
        const phone = getNormalizedPhone();
        const nameInput = document.getElementById('wa-customer-name');
        const name = nameInput ? nameInput.value.trim() : '';

        if (!phone) {
            alert('Please enter a valid mobile number first.');
            return;
        }

        if (btn) btn.innerHTML = '⏳ Saving...';

        try {
            const format = waQuotationData.selectedFormat || '1';
            const params = new URLSearchParams({
                format: format,
                phone: phone,
                customer_name: name
            });
            const res = await fetch(`${waQuotationData.sendUrl}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                if (btn) btn.innerHTML = '✅ Saved!';
                syncCustomerStatusUI();
                setTimeout(() => { if (btn) btn.innerHTML = originalText; }, 2000);
            }
        } catch (e) {
            console.error('Save failed', e);
            if (btn) btn.innerHTML = '⚠️ Error';
            setTimeout(() => { if (btn) btn.innerHTML = originalText; }, 2000);
        }
    }

    async function copyWhatsAppCustomText() {
        const msgBox = document.getElementById('wa-custom-message');
        const text = msgBox ? msgBox.value : '';
        const btn = document.getElementById('btn-copy-wa-text');
        try {
            await navigator.clipboard.writeText(text);
            if (btn) {
                const prev = btn.innerHTML;
                btn.innerHTML = '✅ Copied!';
                setTimeout(() => { btn.innerHTML = prev; }, 2000);
            }
        } catch (e) {
            alert('Could not copy to clipboard. Please select text manually.');
        }
    }

    function downloadSelectedPdfOnly() {
        const format = waQuotationData.selectedFormat || '1';
        const pdfUrl = waQuotationData.pdfUrls[format];
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = `Quotation_${waQuotationData.quoteNo}_Format${format}.pdf`;
        document.body.appendChild(link);
        link.click();
        link.remove();
    }

    function executeWhatsAppShareWithPdf() {
        const btn = document.getElementById('btn-do-whatsapp-dispatch');
        const originalHtml = btn ? btn.innerHTML : '';
        const format = waQuotationData.selectedFormat || '1';
        const phone = getNormalizedPhone();
        const nameInput = document.getElementById('wa-customer-name');
        const name = nameInput ? nameInput.value.trim() : '';
        const msgBox = document.getElementById('wa-custom-message');
        const message = msgBox ? msgBox.value : waQuotationData.messages[format];

        // 1. Download PDF file immediately
        downloadSelectedPdfOnly();

        // 2. Copy message to clipboard
        if (message && navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(message).catch(() => {});
        }

        // 3. Mark sent status in CRM backend via AJAX
        const params = new URLSearchParams({
            format: format,
            phone: phone,
            customer_name: name,
            custom_message: message
        });
        fetch(`${waQuotationData.sendUrl}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).catch(e => console.warn('Status log skipped', e));

        // 4. Open WhatsApp
        const encodedText = encodeURIComponent(message);
        const targetUrl = phone 
            ? `https://api.whatsapp.com/send?phone=${phone}&text=${encodedText}`
            : `https://api.whatsapp.com/send?text=${encodedText}`;

        window.open(targetUrl, '_blank');

        // 5. Visual UI feedback
        if (btn) {
            btn.innerHTML = `<span>✓ PDF Downloaded & WhatsApp Chat Opened!</span>`;
            setTimeout(() => {
                if (btn) btn.innerHTML = originalHtml;
            }, 3500);
        }
    }

    function openWhatsAppDirect(type) {
        const format = waQuotationData.selectedFormat || '1';
        const phone = getNormalizedPhone();
        const msgBox = document.getElementById('wa-custom-message');
        const message = msgBox ? msgBox.value : waQuotationData.messages[format];
        const encodedText = encodeURIComponent(message);

        downloadSelectedPdfOnly();

        let url = '';
        if (type === 'web') {
            url = phone 
                ? `https://web.whatsapp.com/send?phone=${phone}&text=${encodedText}`
                : `https://web.whatsapp.com/send?text=${encodedText}`;
        } else if (type === 'app') {
            url = phone 
                ? `whatsapp://send?phone=${phone}&text=${encodedText}`
                : `whatsapp://send?text=${encodedText}`;
        }

        window.open(url, '_blank');
    }

    async function shareViaNativeWebShare() {
        const format = waQuotationData.selectedFormat || '1';
        const phone = getNormalizedPhone();
        const msgBox = document.getElementById('wa-custom-message');
        const message = msgBox ? msgBox.value : waQuotationData.messages[format];
        const pdfUrl = waQuotationData.previewUrls[format] || waQuotationData.pdfUrls[format];
        const filename = `Quotation_${waQuotationData.quoteNo}_Format${format}.pdf`;

        if (navigator.share) {
            try {
                let shareData = {
                    title: `Quotation #${waQuotationData.quoteNo}`,
                    text: message,
                    url: pdfUrl
                };

                try {
                    const response = await fetch(waQuotationData.pdfUrls[format]);
                    const blob = await response.blob();
                    const file = new File([blob], filename, { type: 'application/pdf' });
                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        shareData = {
                            title: `Quotation #${waQuotationData.quoteNo}`,
                            text: message,
                            files: [file]
                        };
                    }
                } catch (blobErr) {
                    console.warn('PDF blob fetch skipped', blobErr);
                }

                await navigator.share(shareData);
                return;
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.warn('Web Share API error:', err);
                }
            }
        }

        executeWhatsAppShareWithPdf();
    }

    function shareWhatsAppWithPdf(format = '1') {
        return executeWhatsAppShareWithPdf();
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
        }
    }

    async function copyQuotationEmailText() {
        const btn = document.getElementById('btn-copy-email-text');
        const emailBody = @json($quotation->getEmailFormattedBody());
        try {
            await navigator.clipboard.writeText(emailBody);
            if (btn) {
                const prev = btn.innerHTML;
                btn.innerHTML = '✅ Copied to Clipboard!';
                setTimeout(() => { btn.innerHTML = prev; }, 2000);
            }
        } catch (e) {
            alert('Could not auto-copy to clipboard. Please copy manually.');
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
    </script>

</x-app-layout>