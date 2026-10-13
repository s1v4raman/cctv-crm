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
                <a href="{{ route('quotations.pdf', [$quotation, '1']) }}" class="btn-secondary text-xs">
                    Format 1
                </a>
                <a href="{{ route('quotations.pdf', [$quotation, '2']) }}" class="btn-secondary text-xs">
                    Format 2
                </a>
                <a href="{{ route('quotations.pdf', [$quotation, '3']) }}" class="btn-secondary text-xs">
                    Format 3
                </a>

                {{-- Action Modals & Links --}}
                <button type="button" onclick="openWhatsAppModal()" class="btn-secondary text-xs cursor-pointer">
                    Send on WhatsApp
                </button>
                <button type="button" onclick="openEmailModal()" class="btn-secondary text-xs cursor-pointer">
                    Email
                </button>
                <a href="{{ route('quotations.vcard', $quotation) }}" class="btn-secondary text-xs">
                    Save Contact to Phone
                </a>

                @if($quotation->status === 'draft')
                    <a href="{{ route('quotations.edit', $quotation) }}" class="btn-secondary text-xs">
                        Edit Details
                    </a>
                    <form method="POST" action="{{ route('quotations.markSent', $quotation) }}" class="inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-primary text-xs cursor-pointer">
                            Mark as Sent
                        </button>
                    </form>
                    <form method="POST" action="{{ route('quotations.destroy', $quotation) }}"
                          onsubmit="return confirm('Delete {{ addslashes($quotation->quotation_no) }}?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="danger-link text-xs ml-2 cursor-pointer">
                            Delete
                        </button>
                    </form>
                @endif

                @if($quotation->status === 'sent')
                    <form method="POST" action="{{ route('quotations.accept', $quotation) }}" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Confirm customer accepted?')" class="btn-primary text-xs cursor-pointer">
                            Mark Accepted
                        </button>
                    </form>
                    <button type="button" onclick="document.getElementById('reject-modal').classList.add('open')" class="danger-link text-xs ml-2 cursor-pointer">
                        Mark Rejected
                    </button>
                @endif

                @if($quotation->status === 'accepted')
                    @if($quotation->installationJob)
                        <a href="{{ route('jobs.show', $quotation->installationJob) }}" class="btn-secondary text-xs">
                            View Job
                        </a>
                    @else
                        <form method="POST" action="{{ route('jobs.store', $quotation) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-primary text-xs cursor-pointer">
                                Create Job
                            </button>
                        </form>
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
                <table class="crm-table w-full">
                    <tbody>
                        <tr>
                            <td style="width: 25%; color: #555; font-weight: 500;">Quotation Identifier</td>
                            <td style="color: #222; font-weight: 500;">{{ $quotation->quotation_no }}</td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Date Issued</td>
                            <td style="color: #222; font-weight: 500;">{{ $quotation->quotation_date?->format('d M Y') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Valid Until</td>
                            <td style="color: #222; font-weight: 500;">{{ $quotation->valid_until?->format('d M Y') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Customer</td>
                            <td style="color: #222; font-weight: 500;">
                                <a href="{{ route('leads.show', $quotation->lead) }}" class="action-link">
                                    {{ $quotation->lead->customer_name }}
                                </a>
                            </td>
                        </tr>
                        @if($quotation->lead->site_address)
                        <tr>
                            <td style="color: #555; font-weight: 500;">Installation Facility Address</td>
                            <td style="color: #222; font-weight: 500;">{{ $quotation->lead->site_address }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
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
                            @foreach ($quotation->items as $i => $item)
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
                                                  onsubmit="return confirm('Remove this item?')" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="danger-link text-xs cursor-pointer">Remove</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
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
            <h4 class="text-lg font-bold text-white uppercase font-['Outfit'] mb-1">Reject Quotation</h4>
            <p class="text-xs text-slate-400 mb-4">Please provide the reason why the customer rejected this quotation.</p>
            <form method="POST" action="{{ route('quotations.reject', $quotation) }}">
                @csrf
                <textarea name="rejection_reason" class="w-full bg-[#060913] border border-slate-700 rounded-xl p-3 text-sm text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none min-h-[100px] mb-4" required
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
    <div id="email-pdf-modal" style="display:none;position:fixed;inset:0;z-index:50;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;padding:1rem;">
        <div class="bg-white dark:bg-[#0F172A] border border-[#ccc] dark:border-slate-700 rounded p-6 max-w-xl w-full max-h-[90vh] overflow-y-auto text-slate-800 dark:text-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-[#ddd] mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Email Quotation</h3>
                    <p class="text-xs text-[#555]">Server SMTP Delivery or Outlook Compose</p>
                </div>
                <button type="button" onclick="closeEmailModal()" class="text-slate-500 hover:text-slate-800 dark:hover:text-white text-lg">✕</button>
            </div>

            {{-- 1-Click External Email Clients --}}
            <div class="mb-4">
                <label class="crm-label font-bold mb-2 block">External Email Clients</label>
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <a href="{{ $quotation->getOutlookComposeUrl() }}" target="_blank" class="btn-secondary text-xs text-center py-2">
                        Outlook 365
                    </a>
                    <a href="{{ $quotation->getMailtoUrl() }}" class="btn-secondary text-xs text-center py-2">
                        Default Mail App
                    </a>
                </div>
                <button type="button" onclick="copyQuotationEmailText()" id="btn-copy-email-text" class="btn-secondary text-xs w-full py-2">
                    Copy Email Message &amp; PDF Link
                </button>
            </div>

            <div class="border-t border-[#ddd] pt-3 mb-3 text-xs font-semibold text-[#555]">
                Or Send via Direct SMTP
            </div>

            <form method="POST" action="{{ route('quotations.send-email', $quotation) }}">
                @csrf

                <div class="mb-3">
                    <label class="crm-label">
                        Recipient Email <span class="required-marker">*</span>
                    </label>
                    <input type="email" name="email" required value="{{ old('email', $quotation->lead->email) }}" placeholder="client@example.com" class="crm-input">
                </div>

                <div class="mb-3">
                    <label class="crm-label">
                        Subject <span class="required-marker">*</span>
                    </label>
                    <input type="text" name="subject" required value="{{ $quotation->getEmailSubject() }}" class="crm-input">
                </div>

                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="crm-label">PDF Template Style</label>
                        <select name="pdf_format" class="crm-select">
                            <option value="1">Executive Modern (Format 1)</option>
                            <option value="2">Technical Detailed (Format 2)</option>
                            <option value="3">Classic Formal (Format 3)</option>
                        </select>
                    </div>
                    <div>
                        <label class="crm-label">Attachment</label>
                        <div class="crm-input flex items-center text-xs text-slate-600 bg-slate-50 dark:bg-slate-800">
                            Quotation-{{ $quotation->quotation_no }}.pdf
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="crm-label">Custom Notes for Customer</label>
                    <textarea name="custom_message" rows="2" placeholder="e.g. As discussed during our survey, includes 2-year AMC..." class="crm-input h-auto py-2"></textarea>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-[#ddd]">
                    <a href="{{ route('alerts.gateways') }}" target="_blank" class="action-link text-xs">
                        SMTP Settings
                    </a>
                    <div class="flex gap-2">
                        <button type="button" onclick="closeEmailModal()" class="btn-secondary">Cancel</button>
                        <button type="submit" class="btn-primary">
                            Send Email
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Send Quotation via WhatsApp & Multi-Channel Modal --}}
    <div id="whatsapp-share-modal" style="display:none;position:fixed;inset:0;z-index:50;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;padding:1rem;">
        <div class="bg-white dark:bg-[#0F172A] border border-[#ccc] dark:border-slate-700 rounded p-6 max-w-2xl w-full max-h-[94vh] overflow-y-auto text-slate-900 dark:text-slate-100">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-3 border-b border-[#ddd] mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">WhatsApp Proposal Dispatcher</h3>
                    <span class="text-xs text-[#555]">Quotation #{{ $quotation->quotation_no }} &bull; Total: <strong>₹{{ number_format((float)$quotation->total, 2) }}</strong></span>
                </div>
                <button type="button" onclick="closeWhatsAppModal()" class="text-slate-500 hover:text-slate-800 dark:hover:text-white text-lg">✕</button>
            </div>

            {{-- Condition Status --}}
            @php
                $hasStoredPhone = !empty($quotation->getWhatsAppPhone());
            @endphp
            <div id="wa-customer-status-banner" class="mb-4 text-xs flex items-center justify-between gap-3 pb-3 border-b border-[#eee]">
                <div>
                    <span class="font-bold">Status:</span>
                    <span id="wa-status-title" class="{{ $hasStoredPhone ? 'status-won' : 'status-lost' }} font-bold">
                        {{ $hasStoredPhone ? 'Customer Contact Ready & Verified' : 'Customer Phone Number Not Stored' }}
                    </span>
                    <span id="wa-status-desc" class="text-[#555] block mt-0.5">
                        {{ $hasStoredPhone 
                            ? 'Target WhatsApp chat will open for ' . ($quotation->lead?->customer_name ?? 'Customer') . ' (' . $quotation->getWhatsAppPhone() . ').' 
                            : 'Enter mobile number below. Saving will update the CRM customer record.' }}
                    </span>
                </div>
                <a href="{{ route('quotations.vcard', $quotation) }}" id="btn-header-vcard" class="btn-secondary text-xs">
                    vCard
                </a>
            </div>

            {{-- Step 1: Format Selection --}}
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="crm-label font-bold">
                        1. Choose Quotation PDF Format &amp; Style
                    </label>
                    <a id="btn-preview-selected-pdf" href="{{ route('quotations.public-pdf', ['quotation' => $quotation, 'format' => '1']) }}" target="_blank" class="action-link text-xs">
                        Preview Live PDF
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-3" id="wa-format-selector-grid">
                    <label class="flex items-center gap-2 p-2.5 rounded border border-[#ccc] dark:border-slate-700 bg-white dark:bg-slate-800 cursor-pointer text-xs">
                        <input type="radio" name="wa_pdf_format" value="1" checked class="accent-blue-600" onchange="onWhatsAppFormatChange('1')">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">Executive Modern</div>
                            <div class="text-[11px] text-[#555]">Format 1 (Standard)</div>
                        </div>
                    </label>

                    <label class="flex items-center gap-2 p-2.5 rounded border border-[#ccc] dark:border-slate-700 bg-white dark:bg-slate-800 cursor-pointer text-xs">
                        <input type="radio" name="wa_pdf_format" value="2" class="accent-blue-600" onchange="onWhatsAppFormatChange('2')">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">Technical Detailed</div>
                            <div class="text-[11px] text-[#555]">Format 2</div>
                        </div>
                    </label>

                    <label class="flex items-center gap-2 p-2.5 rounded border border-[#ccc] dark:border-slate-700 bg-white dark:bg-slate-800 cursor-pointer text-xs">
                        <input type="radio" name="wa_pdf_format" value="3" class="accent-blue-600" onchange="onWhatsAppFormatChange('3')">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">Classic Formal</div>
                            <div class="text-[11px] text-[#555]">Format 3</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Step 2: Contact Details --}}
            <div class="mb-4">
                <label class="crm-label font-bold mb-2 block">
                    2. Customer Contact &amp; Target Mobile
                </label>

                <div class="grid grid-cols-2 gap-3 mb-2">
                    <div>
                        <label class="crm-label">Customer Name</label>
                        <input type="text" id="wa-customer-name" value="{{ $quotation->lead?->customer_name ?? 'Valued Customer' }}"
                               placeholder="e.g. John Doe"
                               oninput="onWhatsAppNameInput(this.value)"
                               class="crm-input">
                    </div>
                    <div>
                        <label class="crm-label">WhatsApp Phone <span class="required-marker">*</span></label>
                        <input type="tel" id="wa-customer-phone" value="{{ $quotation->getWhatsAppPhone() ?: $quotation->lead?->phone }}" placeholder="e.g. 9876543210"
                               oninput="onWhatsAppPhoneInput(this.value)"
                               class="crm-input">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <span class="text-[#555]">Target: <strong><span id="wa-phone-preview-badge">{{ $quotation->getWhatsAppPhone() ?: 'Customer Number' }}</span></strong></span>
                    <button type="button" onclick="quickSaveCustomerContact()" id="btn-quick-save-contact" class="btn-secondary text-xs">
                        Save Contact
                    </button>
                </div>
            </div>

            {{-- Step 3: Message Text --}}
            <div class="mb-4">
                <div class="flex items-center justify-between mb-1">
                    <label class="crm-label font-bold">
                        3. WhatsApp Proposal Message Preview
                    </label>
                    <button type="button" onclick="copyWhatsAppCustomText()" id="btn-copy-wa-text" class="action-link text-xs">
                        Copy Message
                    </button>
                </div>
                <textarea id="wa-custom-message" rows="4" class="crm-input h-auto py-2">{{ $quotation->getWhatsAppFormattedMessage('1') }}</textarea>
            </div>

            {{-- Action Buttons --}}
            <div class="pt-3 border-t border-[#ddd] flex items-center justify-between flex-wrap gap-2">
                <div class="flex gap-2">
                    <button type="button" onclick="openWhatsAppDirect('web')" class="btn-secondary text-xs">
                        Web WhatsApp
                    </button>
                    <button type="button" onclick="openWhatsAppDirect('app')" class="btn-secondary text-xs">
                        Desktop App
                    </button>
                    <button type="button" onclick="downloadSelectedPdfOnly()" class="btn-secondary text-xs">
                        Download PDF
                    </button>
                </div>

                <div class="flex gap-2">
                    <button type="button" onclick="closeWhatsAppModal()" class="btn-secondary">Cancel</button>
                    <button type="button" id="btn-do-whatsapp-dispatch" onclick="executeWhatsAppShareWithPdf()" class="btn-primary">
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
                btn.innerHTML = 'Copied to Clipboard!';
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