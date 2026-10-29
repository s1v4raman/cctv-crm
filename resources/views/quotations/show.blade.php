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
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <a href="{{ route('quotations.index') }}" class="back-link">
                    &larr; Back
                </a>
                <div class="flex items-center gap-3 mt-1">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        Quotation {{ $quotation->quotation_no }}
                    </h2>
                    @php
                        $qStatusColor = '#64748b';
                        if ($quotation->status === 'sent') $qStatusColor = '#0284c7';
                        elseif ($quotation->status === 'accepted') $qStatusColor = '#16a34a';
                        elseif ($quotation->status === 'rejected') $qStatusColor = '#dc2626';
                        elseif ($quotation->status === 'expired') $qStatusColor = '#d97706';
                    @endphp
                    <span class="text-sm font-bold" style="color: {{ $qStatusColor }};">
                        {{ ucfirst($quotation->status) }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                {{-- PDF Downloads --}}
                <a href="{{ route('quotations.pdf', [$quotation, '1']) }}" title="Download the Executive Modern quotation PDF" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v6h6"/></svg>
                    <span>Modern PDF</span>
                </a>
                <a href="{{ route('quotations.pdf', [$quotation, '2']) }}" title="Download the Technical Detailed quotation PDF" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Detailed PDF</span>
                </a>
                <a href="{{ route('quotations.pdf', [$quotation, '3']) }}" title="Download the Classic Formal quotation PDF" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <span>Classic PDF</span>
                </a>

                {{-- Action Modals & Links --}}
                <button type="button" onclick="openWhatsAppModal()" class="btn-secondary text-xs cursor-pointer inline-flex items-center gap-1.5 shadow-2xs">
                    <x-icon.whatsapp class="w-4 h-4 text-black dark:text-white shrink-0" />
                    <span>Send on WhatsApp</span>
                </button>
                <button type="button" onclick="openEmailModal()" class="btn-secondary text-xs cursor-pointer inline-flex items-center gap-1.5 shadow-2xs">
                    <x-icon.email class="w-4 h-4 text-black dark:text-white shrink-0" />
                    <span>Email</span>
                </button>
                <a href="{{ route('quotations.vcard', $quotation) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Save Contact to Phone</span>
                </a>

                @if($quotation->status === 'draft')
                    <a href="{{ route('quotations.edit', $quotation) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Edit Details</span>
                    </a>
                    <form method="POST" action="{{ route('quotations.markSent', $quotation) }}" class="inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-primary text-xs cursor-pointer inline-flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Mark as Sent</span>
                        </button>
                    </form>
                    @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('quotations.destroy', $quotation) }}"
                          data-confirm="Delete {{ $quotation->quotation_no }}?" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-secondary text-xs cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-rose-600 hover:text-rose-700 hover:border-rose-300 dark:hover:border-rose-700 shadow-2xs transition" title="Delete Quotation">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Delete</span>
                        </button>
                    </form>
                    @endif
                @endif

                @if($quotation->status === 'sent')
                    <form method="POST" action="{{ route('quotations.accept', $quotation) }}" class="inline">
                        @csrf
                        <button type="submit" data-confirm="Confirm customer accepted?" class="btn-primary text-xs cursor-pointer inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Mark Accepted</span>
                        </button>
                    </form>
                    <button type="button" onclick="document.getElementById('reject-modal').classList.add('open')" class="btn-secondary text-xs cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-rose-600 hover:text-rose-700 hover:border-rose-300 dark:hover:border-rose-700 shadow-2xs transition">
                        <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Mark Rejected</span>
                    </button>
                @endif

                @if($quotation->status === 'accepted')
                    @if($quotation->installationJob)
                        <a href="{{ route('jobs.show', $quotation->installationJob) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>View Job</span>
                        </a>
                    @else
                        @if(auth()->user()->isOffice())
                        <form method="POST" action="{{ route('jobs.store', $quotation) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-primary text-xs cursor-pointer inline-flex items-center gap-1.5 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
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

            @if($quotation->status === 'rejected' && $quotation->rejection_reason)
                <div class="crm-section border-rose-300 bg-rose-50 text-rose-800 text-xs">
                    <p class="font-bold mb-1">Rejection Reason</p>
                    <p>{{ $quotation->rejection_reason }}</p>
                </div>
            @endif

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

            {{-- 2. Line Items Table --}}
            <div class="crm-section">
                <h3>Line Items ({{ $quotation->items->count() }})</h3>
                <div class="overflow-x-auto">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item / Product</th>
                                <th>Description</th>
                                <th class="text-right">Qty</th>
                                <th>Unit</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Subtotal</th>
                                @if($quotation->status === 'draft')
                                    <th class="text-right">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($quotation->items as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="font-bold text-slate-900">{{ $item->item_name }}</td>
                                    <td>{{ $item->description ?? '—' }}</td>
                                    <td class="text-right font-medium">{{ $item->quantity }}</td>
                                    <td>{{ $item->unit ?? '—' }}</td>
                                    <td class="text-right" style="white-space: nowrap;">₹{{ number_format((float)$item->unit_price, 2) }}</td>
                                    <td class="text-right font-bold text-slate-900" style="white-space: nowrap;">₹{{ number_format((float)$item->total, 2) }}</td>
                                    @if($quotation->status === 'draft')
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
                                    <td colspan="{{ $quotation->status === 'draft' ? 8 : 7 }}" class="text-center py-6 text-slate-500 text-sm">
                                        No items on this quotation yet. Edit the quotation to add items.
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
                                <span class="font-bold block mb-1">Notes &amp; Terms:</span>
                                {{ $quotation->notes }}
                            </div>
                        @endif
                    </div>
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
                    </div>
                </div>
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
                            class="min-h-[44px] px-5 py-2.5 rounded-xl border border-slate-700 bg-slate-800 text-slate-300 text-sm font-semibold hover:bg-slate-700 transition">Cancel</button>
                    <button type="submit" class="min-h-[44px] px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-bold transition">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Send PDF via Email & Outlook Modal --}}
    <div id="email-pdf-modal" style="display:none;position:fixed;inset:0;z-index:50;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:1.25rem;">
        <div class="bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl p-6 sm:p-7 max-w-xl w-full max-h-[90vh] overflow-y-auto text-slate-800 dark:text-slate-200">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-black dark:text-white shrink-0">
                        <x-icon.email class="w-5 h-5 text-black dark:text-white" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-tight">Email Quotation</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Server SMTP Delivery or Outlook Desktop Compose</p>
                    </div>
                </div>
                <button type="button" onclick="closeEmailModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer" aria-label="Close modal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- 1-Click External Email Clients --}}
            <div class="mb-5">
                <label class="crm-label">
                    <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    External Email Clients
                </label>
                <div class="grid grid-cols-2 gap-2.5 mb-2.5">
                    <a href="{{ $quotation->getOutlookComposeUrl() }}" target="_blank" class="btn-secondary text-xs text-center py-2.5 px-3 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Outlook 365</span>
                    </a>
                    <a href="{{ $quotation->getMailtoUrl() }}" class="btn-secondary text-xs text-center py-2.5 px-3 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <span>Default Mail App</span>
                    </a>
                </div>
                <button type="button" onclick="copyQuotationEmailText()" id="btn-copy-email-text" class="btn-secondary text-xs w-full py-2.5 px-4 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    <span>Copy Email Message &amp; PDF Link</span>
                </button>
            </div>

            <div class="relative flex items-center my-5">
                <div class="flex-grow border-t border-slate-200 dark:border-slate-800"></div>
                <span class="flex-shrink mx-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Or Send via Direct SMTP
                </span>
                <div class="flex-grow border-t border-slate-200 dark:border-slate-800"></div>
            </div>

            <form method="POST" action="{{ route('quotations.send-email', $quotation) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="crm-label">
                        <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                        Recipient Email <span class="required-marker text-rose-500">*</span>
                    </label>
                    <input aria-label="Recipient Email" type="email" name="email" required value="{{ old('email', $quotation->lead->email) }}" placeholder="client@example.com" class="crm-input">
                </div>

                <div>
                    <label class="crm-label">
                        <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        Subject <span class="required-marker text-rose-500">*</span>
                    </label>
                    <input aria-label="Subject" type="text" name="subject" required value="{{ $quotation->getEmailSubject() }}" class="crm-input">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="crm-label">
                            <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            PDF Template Style
                        </label>
                        <select aria-label="PDF Template Style" name="pdf_format" class="crm-select">
                            <option value="1">Executive Modern (Format 1)</option>
                            <option value="2">Technical Detailed (Format 2)</option>
                            <option value="3">Classic Formal (Format 3)</option>
                        </select>
                    </div>
                    <div>
                        <label class="crm-label">
                            <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            Attachment
                        </label>
                        <div class="crm-input flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/80">
                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M7 2v20h10V8l-6-6H7zm8 7V3.5L18.5 7H15z"/></svg>
                            <span class="truncate">Quotation-{{ $quotation->quotation_no }}.pdf</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="crm-label">
                        <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Custom Notes for Customer
                    </label>
                    <textarea aria-label="Custom Notes for Customer" name="custom_message" rows="2" placeholder="e.g. As discussed during our survey, includes 2-year AMC..." class="crm-input h-auto py-2.5"></textarea>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('alerts.gateways') }}" target="_blank" class="action-link text-xs inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>SMTP Settings</span>
                    </a>
                    @endif
                    <div class="flex items-center gap-2.5">
                        <button type="button" onclick="closeEmailModal()" class="btn-secondary text-xs py-2 px-4">Cancel</button>
                        <button type="submit" class="btn-primary text-xs py-2 px-5 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Send Email</span>
                        </button>
                    </div>
                </div>
            </form>
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
                <button type="button" onclick="closeWhatsAppModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer" aria-label="Close modal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Condition Status --}}
            @php
                $hasStoredPhone = !empty($quotation->getWhatsAppPhone());
            @endphp
            <div id="wa-customer-status-banner" class="mb-5 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 text-xs flex items-center justify-between gap-3">
                <div class="flex items-start gap-2.5">
                    <span class="w-2 h-2 rounded-full {{ $hasStoredPhone ? 'bg-emerald-500 ring-4 ring-emerald-100 dark:ring-emerald-950' : 'bg-rose-500 ring-4 ring-rose-100 dark:ring-rose-950' }} mt-1 shrink-0"></span>
                    <div>
                        <div class="flex items-center gap-1.5 font-bold {{ $hasStoredPhone ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            <span id="wa-status-title">{{ $hasStoredPhone ? 'Customer Contact Ready & Verified' : 'Customer Phone Number Not Stored' }}</span>
                        </div>
                        <span id="wa-status-desc" class="text-slate-500 dark:text-slate-400 block mt-0.5 leading-relaxed">
                            {{ $hasStoredPhone 
                                ? 'Target WhatsApp chat will open for ' . ($quotation->lead?->customer_name ?? 'Customer') . ' (' . $quotation->getWhatsAppPhone() . ').' 
                                : 'Enter mobile number below. Saving will update the CRM customer record.' }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('quotations.vcard', $quotation) }}" id="btn-header-vcard" class="btn-secondary text-xs py-2 px-3 flex items-center justify-center gap-1.5 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                    <span>Download vCard</span>
                </a>
            </div>

            {{-- Step 1: Format Selection --}}
            <div class="mb-5">
                <div class="flex items-center justify-between mb-2.5">
                    <label class="crm-label font-bold">
                        <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                        1. Choose Quotation PDF Format &amp; Style
                    </label>
                    <a id="btn-preview-selected-pdf" href="{{ URL::signedRoute('quotations.public-pdf', ['quotation' => $quotation, 'format' => '1']) }}" target="_blank" class="action-link text-xs inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Preview Live PDF</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" id="wa-format-selector-grid">
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 hover:border-blue-500 transition cursor-pointer text-xs">
                        <input type="radio" name="wa_pdf_format" value="1" checked class="accent-blue-600 mt-0.5 shrink-0" onchange="onWhatsAppFormatChange('1')">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white leading-tight">Executive Modern</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Format 1 (Standard)</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 hover:border-blue-500 transition cursor-pointer text-xs">
                        <input type="radio" name="wa_pdf_format" value="2" class="accent-blue-600 mt-0.5 shrink-0" onchange="onWhatsAppFormatChange('2')">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white leading-tight">Technical Detailed</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Format 2</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 hover:border-blue-500 transition cursor-pointer text-xs">
                        <input type="radio" name="wa_pdf_format" value="3" class="accent-blue-600 mt-0.5 shrink-0" onchange="onWhatsAppFormatChange('3')">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white leading-tight">Classic Formal</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Format 3</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Step 2: Contact Details --}}
            <div class="mb-5">
                <label class="crm-label font-bold mb-2">
                    <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    2. Customer Contact &amp; Target Mobile
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2.5">
                    <div>
                        <label class="crm-label">
                            <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Customer Name
                        </label>
                        <input aria-label="Customer Name" type="text" id="wa-customer-name" value="{{ $quotation->lead?->customer_name ?? 'Valued Customer' }}"
                               placeholder="e.g. John Doe"
                               oninput="onWhatsAppNameInput(this.value)"
                               class="crm-input">
                    </div>
                    <div>
                        <label class="crm-label">
                            <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            WhatsApp Phone <span class="required-marker text-rose-500">*</span>
                        </label>
                        <input aria-label="WhatsApp Phone" type="tel" id="wa-customer-phone" value="{{ $quotation->getWhatsAppPhone() ?: $quotation->lead?->phone }}" placeholder="e.g. 9876543210"
                               oninput="onWhatsAppPhoneInput(this.value)"
                               class="crm-input">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <span class="text-slate-500 dark:text-slate-400">Target Chat: <strong class="text-slate-800 dark:text-slate-200"><span id="wa-phone-preview-badge">{{ $quotation->getWhatsAppPhone() ?: 'Customer Number' }}</span></strong></span>
                    <button type="button" onclick="quickSaveCustomerContact()" id="btn-quick-save-contact" class="btn-secondary text-xs py-1.5 px-3 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        <span>Save Contact</span>
                    </button>
                </div>
            </div>

            {{-- Step 3: Message Text --}}
            <div class="mb-5">
                <div class="flex items-center justify-between mb-2">
                    <label class="crm-label font-bold">
                        <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        3. WhatsApp Proposal Message Preview
                    </label>
                    <button type="button" onclick="copyWhatsAppCustomText()" id="btn-copy-wa-text" class="action-link text-xs inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span>Copy Message</span>
                    </button>
                </div>
                <textarea id="wa-custom-message" rows="4" class="crm-input h-auto py-2.5 font-mono text-xs leading-relaxed">{{ $quotation->getWhatsAppFormattedMessage('1') }}</textarea>
            </div>

            {{-- Action Buttons --}}
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2.5">
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openWhatsAppDirect('web')" class="btn-secondary text-xs py-2 px-3 flex items-center justify-center gap-1.5">
                        <x-icon.whatsapp class="w-3.5 h-3.5 text-black dark:text-white" />
                        <span>Web WhatsApp</span>
                    </button>
                    <button type="button" onclick="openWhatsAppDirect('app')" class="btn-secondary text-xs py-2 px-3 flex items-center justify-center gap-1.5">
                        <x-icon.whatsapp class="w-3.5 h-3.5 text-black dark:text-white" />
                        <span>Desktop App</span>
                    </button>
                    <button type="button" onclick="downloadSelectedPdfOnly()" class="btn-secondary text-xs py-2 px-3 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Download PDF</span>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeWhatsAppModal()" class="btn-secondary text-xs py-2 px-4">Cancel</button>
                    <button type="button" id="btn-do-whatsapp-dispatch" onclick="executeWhatsAppShareWithPdf()" class="btn-primary text-xs py-2 px-5 flex items-center justify-center gap-2">
                        <x-icon.whatsapp :badge="true" class="w-4 h-4 text-white" />
                        <span id="btn-wa-dispatch-text">Send on WhatsApp</span>
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
            '1': @json(URL::signedRoute('quotations.public-pdf', ['quotation' => $quotation, 'format' => '1'])),
            '2': @json(URL::signedRoute('quotations.public-pdf', ['quotation' => $quotation, 'format' => '2'])),
            '3': @json(URL::signedRoute('quotations.public-pdf', ['quotation' => $quotation, 'format' => '3'])),
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
                banner.className = 'mb-4 text-xs flex items-center justify-between gap-3 pb-3 border-b border-[#eee]';
            }
            if (title) {
                title.className = 'status-won font-bold';
                title.innerText = 'Customer Contact Ready & Verified';
            }
            if (desc) desc.innerText = `Target WhatsApp chat will open for ${name} (+${phone}). PDF will be downloaded & text pre-filled.`;
            if (btnText) btnText.innerText = 'Share PDF & Open WhatsApp Chat';
        } else {
            if (banner) {
                banner.className = 'mb-4 text-xs flex items-center justify-between gap-3 pb-3 border-b border-[#eee]';
            }
            if (title) {
                title.className = 'status-lost font-bold';
                title.innerText = 'Customer Phone Number Not Stored';
            }
            if (desc) desc.innerText = 'Please enter mobile number above. Clicking below will save the customer in CRM and open WhatsApp.';
            if (btnText) btnText.innerText = 'Save Customer & Share WhatsApp PDF';
        }
    }

    async function quickSaveCustomerContact() {
        const btn = document.getElementById('btn-quick-save-contact');
        const originalText = btn ? btn.innerHTML : '';
        const phone = getNormalizedPhone();
        const nameInput = document.getElementById('wa-customer-name');
        const name = nameInput ? nameInput.value.trim() : '';

        if (!phone) {
            crmAlert('Please enter a valid mobile number first.');
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
                if (btn) btn.innerHTML = 'Saved!';
                syncCustomerStatusUI();
                setTimeout(() => { if (btn) btn.innerHTML = originalText; }, 2000);
            }
        } catch (e) {
            console.error('Save failed', e);
            if (btn) btn.innerHTML = 'Error';
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
                btn.innerHTML = 'Copied!';
                setTimeout(() => { btn.innerHTML = prev; }, 2000);
            }
        } catch (e) {
            crmAlert('Could not copy to clipboard. Please select text manually.');
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
    </script>

</x-app-layout>