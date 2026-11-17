<x-app-layout>
    <x-slot name="header">
        <div>
            @if(auth()->user()->isTechnician())
                <a href="{{ route('technician.dashboard') }}" onclick="if (history.length > 1) { history.back(); return false; }" class="back-link">&larr; Back</a>
            @else
                <a href="{{ route('suppliers.index') }}" class="back-link">&larr; Back</a>
            @endif
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-1">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $supplier->name }}</h2>
                    <div class="flex items-center gap-2 text-xs mt-1">
                        @if($supplier->is_active)
                            <span class="status-won">Active</span>
                        @else
                            <span class="status-lost">Inactive</span>
                        @endif
                        @if($supplier->company_name)
                            <span class="text-slate-400">&bull;</span>
                            <span class="text-slate-600 dark:text-slate-300">{{ $supplier->company_name }}</span>
                        @endif
                        @if($supplier->city)
                            <span class="text-slate-400">&bull;</span>
                            <span class="text-slate-500">{{ $supplier->city }}{{ $supplier->state ? ', ' . $supplier->state : '' }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @if(auth()->user()->isOffice())
                        <button type="button" onclick="openSupplierPayment('', {{ (float) $supplier->balancePayable() }})" class="btn-primary px-3.5 py-2 rounded-lg text-xs font-bold shadow-xs cursor-pointer inline-flex items-center gap-1.5 transition" style="background-color: #2563eb !important; color: #ffffff !important;">
                            <span>Record Paid Amount</span>
                        </button>
                    @endif
                    @if($supplier->phone)
                        <button type="button" onclick="openSupplierWhatsApp()" class="px-3.5 py-2 rounded-lg text-xs font-bold transition shadow-xs cursor-pointer inline-flex items-center gap-1.5" style="background-color: #059669 !important; color: #ffffff !important;">
                            <span>WhatsApp</span>
                        </button>
                    @endif
                    @if($supplier->email)
                        <button type="button" onclick="openSupplierEmail()" class="px-3.5 py-2 rounded-lg text-xs font-bold transition shadow-xs cursor-pointer inline-flex items-center gap-1.5" style="background-color: #0284c7 !important; color: #ffffff !important;">
                            <span>Email</span>
                        </button>
                    @endif
                    @if(auth()->user()->isOffice())
                        <a href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}" class="px-3.5 py-2 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 border border-slate-300 dark:border-slate-700 transition shadow-xs">
                            <span>New Purchase Order</span>
                        </a>
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="px-3.5 py-2 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 border border-slate-300 dark:border-slate-700 transition shadow-xs">
                            <span>Edit</span>
                        </a>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm="Are you sure you want to delete supplier {{ $supplier->name }}?" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3.5 py-2 rounded-lg text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition shadow-xs cursor-pointer border-0">
                                <span>Delete</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $validOrders = $supplier->purchaseOrders->where('status', '!=', 'cancelled');
        $totalSpend = (float) $validOrders->sum('total');
        $totalPaid = (float) $supplier->purchaseOrders->sum('amount_paid');
        $balanceDue = max(0.0, $totalSpend - $totalPaid);
        $unpaidOrders = $supplier->purchaseOrders->whereIn('payment_status', ['unpaid', 'partially_paid'])->where('status', '!=', 'cancelled');
        $unpaidOrdersCount = $unpaidOrders->count();
        $ordersInTransit = $supplier->purchaseOrders->whereIn('status', ['ordered', 'partially_received']);
        $ordersInTransitCount = $ordersInTransit->count();
        $ordersInTransitValue = (float) $ordersInTransit->sum('total');

        $poMap = $supplier->purchaseOrders->mapWithKeys(function($p) {
            $due = max(0.0, (float) $p->total - (float) $p->amount_paid);
            return [(string)$p->id => [
                'id' => $p->id,
                'po_number' => $p->po_number,
                'total' => (float) $p->total,
                'amount_paid' => (float) $p->amount_paid,
                'due' => $due,
                'status' => $p->status,
                'order_date' => $p->order_date ? $p->order_date->format('d M Y') : '—',
            ]];
        });

        $paymentMap = $supplier->vendorPayments->mapWithKeys(function($pay) {
            return [(string)$pay->id => [
                'id' => $pay->id,
                'ref' => $pay->payment_reference,
                'date' => $pay->payment_date ? $pay->payment_date->format('d M Y') : '—',
                'amount' => (float) $pay->amount,
                'method' => strtoupper(str_replace('_', ' / ', $pay->payment_method ?: 'Bank Transfer')),
                'utr' => $pay->transaction_reference ?: '',
                'po' => $pay->purchaseOrder?->po_number ?: '',
                'notes' => $pay->notes ?: '',
            ]];
        });
    @endphp

    <div x-data="{
        showWhatsAppModal: false,
        showEmailModal: false,
        showPaymentModal: false,
        selectedPoId: '',
        selectedPaymentId: '',
        emailPoId: '',
        emailPaymentId: '',
        paymentAmount: '',
        paymentMethod: 'neft_rtgs',
        paymentDate: '{{ now()->format('Y-m-d') }}',
        paymentRef: '',
        paymentNotes: '',
        templateType: 'stock_inquiry',
        customMsg: '1. [Model / SKU] — Qty: 10 Nos\n2. [Model / SKU] — Qty: 5 Nos',
        emailTemplateType: 'stock_inquiry',
        emailSubject: 'Stock & Price Inquiry: Availability & Quotation Request — Precision IT Systems',
        emailMsg: 'Dear {{ $supplier->contact_person ?: ($supplier->company_name ?: $supplier->name) }},\n\nGreetings from Precision IT Systems.\n\nKindly share current stock availability, best dealer quotation, and dispatch lead times for the following items/SKUs:\n\n1. [Product Model / SKU] — Qty: 10 Nos\n2. [Product Model / SKU] — Qty: 4 Nos\n\nDelivery Destination: Central Warehouse, Precision IT Systems\nRequired Timeline: Immediate / Within 2-3 Business Days\n\nPlease confirm availability, GST rates, and delivery schedule.',
        copiedKey: null,
        poList: @js($poMap),
        paymentList: @js($paymentMap),
        onWhatsAppTemplateChange() {
            if (this.templateType === 'stock_inquiry' || this.templateType === 'stock_check') {
                this.customMsg = '1. [Model / SKU] — Qty: 10 Nos\n2. [Model / SKU] — Qty: 5 Nos';
            } else if (this.templateType === 'payment') {
                this.customMsg = 'Please share the updated statement of account or outstanding invoice copies for reconciliation and payment scheduling.';
            } else if (this.templateType === 'payment_confirmation') {
                if (this.selectedPaymentId && this.paymentList[this.selectedPaymentId]) {
                    const p = this.paymentList[this.selectedPaymentId];
                    this.customMsg = `Amount: ₹${Number(p.amount).toFixed(2)}\nDate: ${p.date}\nPayment Mode: ${p.method}${p.utr ? '\nUTR / Ref: ' + p.utr : ''}${p.po ? '\nAgainst PO: ' + p.po : ''}`;
                } else {
                    this.customMsg = 'Amount Paid: ₹0.00\nPayment Mode: NEFT / RTGS\nUTR / Ref: UTR12345678';
                }
            } else if (this.templateType === 'po_dispatch') {
                if (this.selectedPoId && this.poList[this.selectedPoId]) {
                    const po = this.poList[this.selectedPoId];
                    this.customMsg = `Order: PO #${po.po_number}\nTotal Amount: ₹${Number(po.total).toFixed(2)}\nDelivery expected as per PO instructions.`;
                } else {
                    this.customMsg = 'Please find attached our official Purchase Order.';
                }
            } else {
                this.customMsg = '';
            }
        },
        onEmailTemplateChange() {
            const contactName = @js($supplier->contact_person ?: ($supplier->company_name ?: $supplier->name));
            if (this.emailTemplateType === 'stock_inquiry') {
                this.emailSubject = 'Stock & Price Inquiry: Availability & Quotation Request — Precision IT Systems';
                this.emailMsg = `Dear ${contactName},\n\nGreetings from Precision IT Systems.\n\nKindly share current stock availability, best dealer quotation, and dispatch lead times for the following items/SKUs:\n\n1. [Product Model / SKU] — Qty: 10 Nos\n2. [Product Model / SKU] — Qty: 4 Nos\n\nDelivery Destination: Central Warehouse, Precision IT Systems\nRequired Timeline: Immediate / Within 2-3 Business Days\n\nPlease confirm availability, GST rates, and delivery schedule.`;
            } else if (this.emailTemplateType === 'po_dispatch') {
                const po = this.emailPoId && this.poList[this.emailPoId] ? this.poList[this.emailPoId] : null;
                this.emailSubject = po ? `Official Purchase Order #${po.po_number} — Precision IT Systems` : 'Official Purchase Order — Precision IT Systems';
                this.emailMsg = `Dear ${contactName},\n\nPlease find our official Purchase Order details below. Kindly acknowledge receipt, confirm delivery schedule, and dispatch as per instructions.`;
            } else if (this.emailTemplateType === 'payment_confirmation') {
                const pay = this.emailPaymentId && this.paymentList[this.emailPaymentId] ? this.paymentList[this.emailPaymentId] : null;
                const amountStr = pay ? `₹${Number(pay.amount).toFixed(2)}` : '';
                const utrStr = pay && pay.utr ? ` (UTR: ${pay.utr})` : '';
                this.emailSubject = `Payment Confirmation & Remittance Advice: ${amountStr}${utrStr} — Precision IT Systems`;
                this.emailMsg = `Dear ${contactName},\n\nWe have remitted payment to your registered bank account. Please find the payment details below and confirm receipt / reconcile your ledger.`;
            } else {
                this.emailSubject = 'Procurement Inquiry / Communique — Precision IT Systems';
                this.emailMsg = `Dear ${contactName},\n\nGreetings from Precision IT Systems.\n\nWe are writing regarding our procurement collaboration. Kindly update us on current product availability and pricing schedules.\n\nBest regards,\nPrecision IT Systems Operations Team`;
            }
        },
        openPaymentModal(poId = '', dueAmount = 0) {
            this.selectedPoId = poId ? String(poId) : '';
            if (poId && this.poList[String(poId)]) {
                this.paymentAmount = Number(this.poList[String(poId)].due).toFixed(2);
            } else if (dueAmount > 0) {
                this.paymentAmount = Number(dueAmount).toFixed(2);
            } else {
                this.paymentAmount = '';
            }
            this.paymentDate = '{{ now()->format('Y-m-d') }}';
            this.paymentRef = '';
            this.paymentNotes = '';
            this.showPaymentModal = true;
            const m = document.getElementById('supplier-payment-modal');
            if (m) m.style.display = 'flex';
        },
        onPoChange() {
            if (this.selectedPoId && this.poList[this.selectedPoId]) {
                const po = this.poList[this.selectedPoId];
                if (po.due > 0) {
                    this.paymentAmount = Number(po.due).toFixed(2);
                }
            }
        },
        copyText(text, key) {
            if (!text) return;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text);
            } else {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                try {
                    document.execCommand('copy');
                } catch (err) {}
                document.body.removeChild(textarea);
            }
            this.copiedKey = key;
            setTimeout(() => { if (this.copiedKey === key) this.copiedKey = null; }, 2000);
        }
    }"
    @open-supplier-whatsapp.window="showWhatsAppModal = true"
    @close-supplier-whatsapp.window="showWhatsAppModal = false"
    @open-supplier-email.window="showEmailModal = true"
    @close-supplier-email.window="showEmailModal = false"
    @open-supplier-payment.window="openPaymentModal($event.detail.poId, $event.detail.dueAmount)"
    @close-supplier-payment.window="showPaymentModal = false">

    <div class="max-w-[1200px] mx-auto space-y-6">
        @if (session('status'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs font-medium">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-rose-500 text-rose-700 dark:text-rose-300 rounded-lg text-xs font-medium">
                {{ session('error') }}
            </div>
        @endif

        {{-- Vendor Financial Ledger & Outstanding Balance Card --}}
        <div class="crm-section mb-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-4 flex-wrap gap-3">
                <div>
                    <h3 style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;" class="text-base font-bold text-slate-900 dark:text-white">
                        Vendor Financial Ledger & Procurement Overview
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Real-time spend, payment status, and order tracking for {{ $supplier->company_name ?: $supplier->name }}.</p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @if($balanceDue > 0)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                            ₹{{ number_format($balanceDue, 2) }} Payment Pending
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                            All Invoices Cleared
                        </span>
                    @endif
                    @if(auth()->user()->isOffice())
                        <button type="button" @click="openPaymentModal('', {{ $balanceDue }})" class="px-3.5 py-2 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-xs cursor-pointer">
                            <span>Record Payment</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- 4 Real-Time Procurement Metric Cards Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- 1. Total Spend / Purchases (₹) --}}
                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Spend / Purchases</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">Total POs</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1.5">₹{{ number_format($totalSpend, 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">Lifetime procurement ({{ $validOrders->count() }} active orders)</div>
                </div>

                {{-- 2. Total Paid (₹) --}}
                <div class="p-4 rounded-xl border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/40 dark:bg-emerald-950/20 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Total Paid</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300">Cleared</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-emerald-700 dark:text-emerald-400 mt-1.5">₹{{ number_format($totalPaid, 2) }}</div>
                    <div class="text-[11px] text-emerald-600/80 dark:text-emerald-500 mt-1">Recorded via bank & offline transfers</div>
                </div>

                {{-- 3. Outstanding Balance / Unpaid POs (₹) --}}
                <div class="p-4 rounded-xl border {{ $balanceDue > 0 ? 'border-amber-300 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-950/30' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900' }} shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold {{ $balanceDue > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400' }} uppercase tracking-wider">Outstanding Balance</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $balanceDue > 0 ? 'bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">Due</span>
                    </div>
                    <div class="text-2xl font-bold font-mono {{ $balanceDue > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-900 dark:text-white' }} mt-1.5">₹{{ number_format($balanceDue, 2) }}</div>
                    <div class="text-[11px] {{ $balanceDue > 0 ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-slate-500' }} mt-1">
                        {{ $unpaidOrdersCount }} unpaid / partial PO(s)
                    </div>
                </div>

                {{-- 4. Active Orders in Transit --}}
                <div class="p-4 rounded-xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/40 dark:bg-blue-950/20 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Active Orders in Transit</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300">In Transit</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-blue-700 dark:text-blue-400 mt-1.5">{{ $ordersInTransitCount }} <span class="text-sm font-normal text-slate-600 dark:text-slate-400">order(s)</span></div>
                    <div class="text-[11px] text-blue-600/80 dark:text-blue-400 mt-1">₹{{ number_format($ordersInTransitValue, 2) }} pending delivery</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Supplier Profile Details --}}
            <div class="lg:col-span-2 crm-section">
                <h3>Supplier & Company Information</h3>
                <table class="crm-table">
                    <tbody>
                        <tr>
                            <td class="w-1/3 text-slate-500 font-bold">Supplier Name</td>
                            <td class="w-2/3 text-slate-900 dark:text-white font-semibold">{{ $supplier->name }}</td>
                        </tr>
                        @if($supplier->company_name)
                            <tr>
                                <td class="text-slate-500 font-bold">Company Name</td>
                                <td class="text-slate-900 dark:text-white">{{ $supplier->company_name }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-slate-500 font-bold">Contact Person</td>
                            <td class="text-slate-900 dark:text-white">{{ $supplier->contact_person ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-slate-500 font-bold">Phone Number</td>
                            <td class="text-slate-900 dark:text-white">
                                @if($supplier->phone)
                                    <a href="tel:{{ $supplier->phone }}" class="action-link font-mono">{{ $supplier->phone }}</a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @if($supplier->email)
                            <tr>
                                <td class="text-slate-500 font-bold">Email Address</td>
                                <td class="text-slate-900 dark:text-white">
                                    <a href="mailto:{{ $supplier->email }}" class="action-link">{{ $supplier->email }}</a>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-slate-500 font-bold">GSTIN / Tax ID</td>
                            <td class="text-slate-900 dark:text-white font-mono">{{ $supplier->gst_number ?: 'Unregistered' }}</td>
                        </tr>
                        <tr>
                            <td class="text-slate-500 font-bold">Payment Terms</td>
                            <td class="text-slate-900 dark:text-white">{{ $supplier->payment_terms ?: 'Standard Net 30' }}</td>
                        </tr>
                        <tr>
                            <td class="text-slate-500 font-bold">Address & Warehouse</td>
                            <td class="text-slate-900 dark:text-white">
                                {{ $supplier->address ?: 'Not specified' }}{{ $supplier->city ? ", {$supplier->city}" : '' }}{{ $supplier->state ? ", {$supplier->state}" : '' }}
                            </td>
                        </tr>
                        @if($supplier->notes)
                            <tr>
                                <td class="text-slate-500 font-bold">Internal Vendor Notes</td>
                                <td class="text-slate-900 dark:text-white whitespace-pre-line">{{ $supplier->notes }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Bank Account & Payment Details Card (For Offline Transfers) --}}
            <div class="space-y-6">
                <div class="crm-section">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-3.5 flex-wrap gap-2">
                        <div>
                            <h3 style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">Bank & Payment Details</h3>
                            <span class="text-[11px] text-slate-500 font-medium">1-Click Copy for Vendor Bank Transfers</span>
                        </div>
                        @if($supplier->hasBankDetails())
                            <button type="button" @click="copyText(@js($supplier->formattedBankDetails()), 'all')" 
                                    class="px-2.5 py-1 rounded text-xs font-bold transition border cursor-pointer"
                                    :class="copiedKey === 'all' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border-slate-300 dark:border-slate-700'">
                                <span x-text="copiedKey === 'all' ? 'All Copied!' : 'Copy All Details'"></span>
                            </button>
                        @endif
                    </div>

                    @if($supplier->hasBankDetails())
                        <div class="space-y-3 text-xs">
                            {{-- Beneficiary Name --}}
                            <div>
                                <div class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">Beneficiary / Account Name</div>
                                <div class="flex items-center justify-between mt-1 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $supplier->company_name ?: $supplier->name }}</span>
                                    <button type="button" @click="copyText('{{ addslashes($supplier->company_name ?: $supplier->name) }}', 'name')" 
                                            class="text-[11px] font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 cursor-pointer">
                                        <span x-text="copiedKey === 'name' ? 'Copied!' : 'Copy'"></span>
                                    </button>
                                </div>
                            </div>

                            {{-- Bank Name --}}
                            @if($supplier->bank_name)
                                <div>
                                    <div class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">Bank Name</div>
                                    <div class="flex items-center justify-between mt-1 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                                        <span class="font-semibold text-slate-900 dark:text-white">{{ $supplier->bank_name }}</span>
                                        <button type="button" @click="copyText('{{ addslashes($supplier->bank_name) }}', 'bank')" 
                                                class="text-[11px] font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 cursor-pointer">
                                            <span x-text="copiedKey === 'bank' ? 'Copied!' : 'Copy'"></span>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            {{-- Account Number --}}
                            @if($supplier->bank_account_number)
                                <div>
                                    <div class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">Bank Account Number</div>
                                    <div class="flex items-center justify-between mt-1 p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                                        <span class="font-mono font-bold text-sm text-slate-900 dark:text-white tracking-wider">{{ $supplier->bank_account_number }}</span>
                                        <button type="button" @click="copyText('{{ $supplier->bank_account_number }}', 'acc')" 
                                                class="px-2 py-0.5 rounded text-[11px] font-bold transition border cursor-pointer"
                                                :class="copiedKey === 'acc' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-slate-700 text-blue-600 dark:text-blue-300 border-slate-300 dark:border-slate-600 hover:bg-blue-50'">
                                            <span x-text="copiedKey === 'acc' ? 'Copied!' : 'Copy A/C'"></span>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            {{-- IFSC Code & Branch --}}
                            <div class="grid grid-cols-2 gap-3">
                                @if($supplier->bank_ifsc)
                                    <div>
                                        <div class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">IFSC Code</div>
                                        <div class="flex items-center justify-between mt-1 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                                            <span class="font-mono font-bold text-slate-900 dark:text-white uppercase">{{ $supplier->bank_ifsc }}</span>
                                            <button type="button" @click="copyText('{{ strtoupper($supplier->bank_ifsc) }}', 'ifsc')" 
                                                    class="text-[11px] font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 cursor-pointer">
                                                <span x-text="copiedKey === 'ifsc' ? 'Copied!' : 'Copy'"></span>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if($supplier->bank_branch)
                                    <div>
                                        <div class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">Branch</div>
                                        <div class="mt-1 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white truncate font-medium">
                                            {{ $supplier->bank_branch }}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- UPI ID / VPA --}}
                            @if($supplier->upi_id)
                                <div>
                                    <div class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">UPI ID / VPA</div>
                                    <div class="flex items-center justify-between mt-1 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                                        <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $supplier->upi_id }}</span>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" @click="copyText('{{ $supplier->upi_id }}', 'upi')" 
                                                    class="text-[11px] font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 cursor-pointer">
                                                <span x-text="copiedKey === 'upi' ? 'Copied!' : 'Copy'"></span>
                                            </button>
                                            <a href="upi://pay?pa={{ urlencode($supplier->upi_id) }}&pn={{ urlencode($supplier->company_name ?: $supplier->name) }}&cu=INR" 
                                               class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                                                Pay
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Payment Link / QR Portal --}}
                            @if($supplier->payment_link)
                                <div>
                                    <div class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">Payment Link / Portal URL</div>
                                    <div class="flex items-center justify-between mt-1 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                                        <a href="{{ $supplier->payment_link }}" target="_blank" class="font-mono text-xs text-blue-600 hover:underline truncate max-w-[180px]">
                                            {{ $supplier->payment_link }}
                                        </a>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <button type="button" @click="copyText('{{ $supplier->payment_link }}', 'link')" 
                                                    class="text-[11px] font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 cursor-pointer">
                                                <span x-text="copiedKey === 'link' ? 'Copied!' : 'Copy'"></span>
                                            </button>
                                            <a href="{{ $supplier->payment_link }}" target="_blank" 
                                               class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-600 text-white hover:bg-blue-700 transition">
                                                Open
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="py-6 text-center text-xs text-slate-500">
                            <div>No bank or payment details registered yet.</div>
                            @if(auth()->user()->isOffice())
                                <div class="mt-2.5">
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition inline-block">
                                        Add Bank & Payment Details
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Supplied Categories & Brands --}}
                <div class="crm-section">
                    <h3 class="mb-3">Supplied Hardware & Brands</h3>
                    <div class="space-y-3">
                        <div>
                            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Product Categories</div>
                            @if(!empty($supplier->categories) && count($supplier->categories) > 0)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($supplier->categories as $cat)
                                        <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800/50">
                                            {{ $cat }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-xs text-slate-500">General Hardware Supplier</div>
                            @endif
                        </div>

                        <div>
                            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Brands Supplied</div>
                            @if($supplier->brands_supplied)
                                <div class="text-xs text-slate-800 dark:text-slate-200 leading-relaxed font-medium">
                                    {{ $supplier->brands_supplied }}
                                </div>
                            @else
                                <div class="text-xs text-slate-500">Not specified</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Work & Procurement Orders (Cost vs Paid Amount Tracking) --}}
        <div class="crm-section">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-4 flex-wrap gap-2">
                <div>
                    <h3 style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">Work & Procurement Orders</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Track ordered hardware cost, offline amount paid, and outstanding balance.</p>
                </div>
                @if(auth()->user()->isOffice())
                    <button type="button" @click="openPaymentModal('', {{ $supplier->balancePayable() }})" class="px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-xs cursor-pointer border border-blue-600 inline-flex items-center" style="background-color: #2563eb !important; color: #ffffff !important;">
                        <span>Record Paid Amount</span>
                    </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th class="nowrap">PO Number</th>
                            <th class="nowrap">Order Date</th>
                            <th>Line Items</th>
                            <th class="text-right">Order Cost (₹)</th>
                            <th class="text-right">Paid Amount (₹)</th>
                            <th class="text-right">Remaining Due (₹)</th>
                            <th class="nowrap">Delivery Status</th>
                            <th class="nowrap">Payment Status</th>
                            <th class="nowrap text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier->purchaseOrders as $po)
                            @php
                                $due = max(0.0, (float) $po->total - (float) $po->amount_paid);
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                                <td class="nowrap">
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="action-link font-mono font-bold">
                                        {{ $po->po_number }}
                                    </a>
                                </td>
                                <td class="nowrap text-xs text-slate-600 dark:text-slate-300">
                                    {{ $po->order_date ? $po->order_date->format('d M Y') : '—' }}
                                </td>
                                <td class="text-xs text-slate-700 dark:text-slate-300 max-w-xs truncate">
                                    {{ $po->items->count() }} item(s) ({{ $po->items->pluck('item_name')->take(2)->join(', ') }})
                                </td>
                                <td class="text-right font-mono font-bold text-slate-900 dark:text-white">
                                    ₹{{ number_format((float) $po->total, 2) }}
                                </td>
                                <td class="text-right font-mono font-bold text-emerald-700 dark:text-emerald-400">
                                    ₹{{ number_format((float) $po->amount_paid, 2) }}
                                </td>
                                <td class="text-right font-mono font-bold {{ $due > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-400' }}">
                                    ₹{{ number_format($due, 2) }}
                                </td>
                                <td class="nowrap">
                                    <span class="status-{{ $po->status === 'received' ? 'completed' : ($po->status === 'cancelled' ? 'cancelled' : 'progress') }}">
                                        {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                                    </span>
                                </td>
                                <td class="nowrap">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold font-mono {{ $po->payment_status === 'paid' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60' : ($po->payment_status === 'partially_paid' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700') }}">
                                        {{ ucfirst(str_replace('_', ' ', $po->payment_status)) }}
                                    </span>
                                </td>
                                <td class="nowrap text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('purchase-orders.show', $po) }}" class="px-2.5 py-1 rounded text-xs font-bold bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 text-white transition">
                                            <span>View</span>
                                        </a>
                                        <a href="{{ route('purchase-orders.pdf', $po) }}" class="px-2.5 py-1 rounded text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 transition" title="Download PO PDF">
                                            <span>PDF</span>
                                        </a>
                                        @if(auth()->user()->isOffice() && $due > 0)
                                            <button type="button" @click="openPaymentModal({{ $po->id }}, {{ $due }})" class="px-2.5 py-1 rounded text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs cursor-pointer" title="Direct Record Payment for this PO">
                                                <span>Record Payment</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-6 text-slate-500 text-sm">
                                    No purchase orders recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Payment History Log (Offline Payment Records) --}}
        <div class="crm-section">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-4 flex-wrap gap-2">
                <div>
                    <h3 style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">Payment History Log</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Audit log of all manual and bank payments recorded for this supplier.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th class="nowrap">Payment Ref</th>
                            <th class="nowrap">Payment Date</th>
                            <th class="nowrap">Linked PO</th>
                            <th class="text-right">Amount Paid (₹)</th>
                            <th class="nowrap">Payment Method</th>
                            <th>UTR / Cheque Ref</th>
                            <th>Remarks / Notes</th>
                            <th class="nowrap">Recorded By</th>
                            <th class="nowrap text-center">Share Advice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier->vendorPayments as $payment)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition text-xs">
                                <td class="nowrap font-mono font-bold text-slate-900 dark:text-white">
                                    {{ $payment->payment_reference }}
                                </td>
                                <td class="nowrap text-slate-600 dark:text-slate-300">
                                    {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : '—' }}
                                </td>
                                <td class="nowrap">
                                    @if($payment->purchaseOrder)
                                        <a href="{{ route('purchase-orders.show', $payment->purchaseOrder) }}" class="font-mono font-bold text-blue-600 hover:underline">
                                            {{ $payment->purchaseOrder->po_number }}
                                        </a>
                                    @else
                                        <span class="text-slate-500 italic">General Ledger</span>
                                    @endif
                                </td>
                                <td class="text-right font-mono font-bold text-emerald-700 dark:text-emerald-400">
                                    ₹{{ number_format((float) $payment->amount, 2) }}
                                </td>
                                <td class="nowrap font-medium text-slate-700 dark:text-slate-300">
                                    {{ strtoupper(str_replace('_', ' / ', $payment->payment_method)) }}
                                </td>
                                <td class="font-mono text-slate-800 dark:text-slate-200">
                                    {{ $payment->transaction_reference ?: '—' }}
                                </td>
                                <td class="text-slate-600 dark:text-slate-400 max-w-xs truncate">
                                    {{ $payment->notes ?: '—' }}
                                </td>
                                <td class="nowrap text-slate-500">
                                    {{ $payment->recordedBy?->name ?: 'System' }}
                                </td>
                                <td class="nowrap text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($supplier->phone)
                                            <form method="POST" action="{{ route('vendor-payments.shareWhatsApp', $payment) }}" target="_blank" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs cursor-pointer border-0" title="Send Payment Advice & UTR on WhatsApp">
                                                    <span>WhatsApp</span>
                                                </button>
                                            </form>
                                        @endif
                                        @if($supplier->email)
                                            <form method="POST" action="{{ route('vendor-payments.sendEmail', $payment) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white transition shadow-xs cursor-pointer border-0" title="Email Remittance Advice to Vendor">
                                                    <span>Email</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-6 text-slate-500 text-sm">
                                    No offline payment records logged yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Record Offline Paid Amount Modal --}}
    <div id="supplier-payment-modal" x-show="showPaymentModal" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="showPaymentModal = false; $el.style.display = 'none';">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden"
             @click.away="showPaymentModal = false; $root.style.display = 'none';">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900/60">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white" style="margin: 0; padding: 0; border: none;">Record Vendor Payment</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Link offline payment to a specific Purchase Order or General Ledger</p>
                </div>
                <button type="button" @click="showPaymentModal = false; document.getElementById('supplier-payment-modal').style.display = 'none';" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg leading-none cursor-pointer">&times;</button>
            </div>
            <form method="POST" action="{{ route('suppliers.recordPayment', $supplier) }}" @submit="showPaymentModal = false; document.getElementById('supplier-payment-modal').style.display = 'none';">
                @csrf
                <div class="p-5 space-y-4 text-xs">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-bold text-slate-700 dark:text-slate-200">Target Purchase Order (Optional)</label>
                            <span class="text-[11px] text-blue-600 dark:text-blue-400 font-semibold" x-show="selectedPoId && poList[selectedPoId]">
                                Due: ₹<span x-text="Number(poList[selectedPoId]?.due || 0).toFixed(2)"></span>
                            </span>
                        </div>
                        <select name="purchase_order_id" x-model="selectedPoId" @change="onPoChange()" class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-mono">
                            <option value="">-- General Account Payment (Not tied to single PO) --</option>
                            @foreach($supplier->purchaseOrders as $po)
                                @php
                                    $rem = max(0.0, (float) $po->total - (float) $po->amount_paid);
                                @endphp
                                <option value="{{ $po->id }}">
                                    {{ $po->po_number }} &mdash; Total: ₹{{ number_format((float) $po->total, 2) }} (Due: ₹{{ number_format($rem, 2) }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Linking to a PO automatically updates its paid amount and payment status.</p>
                    </div>

                    {{-- Live Selected PO Banner --}}
                    <template x-if="selectedPoId && poList[selectedPoId]">
                        <div class="p-3 rounded-xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-[11px] space-y-1">
                            <div class="flex items-center justify-between font-bold text-blue-900 dark:text-blue-200">
                                <span x-text="'PO #' + poList[selectedPoId].po_number"></span>
                                <span x-text="'Ordered: ' + poList[selectedPoId].order_date"></span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 font-mono">
                                <span>Total: ₹<span x-text="Number(poList[selectedPoId].total).toFixed(2)"></span></span>
                                <span>Paid: ₹<span x-text="Number(poList[selectedPoId].amount_paid).toFixed(2)"></span></span>
                                <span class="font-bold text-amber-700 dark:text-amber-400">Due: ₹<span x-text="Number(poList[selectedPoId].due).toFixed(2)"></span></span>
                            </div>
                        </div>
                    </template>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Paid Amount (₹) *</label>
                            <input type="number" step="0.01" min="0.01" name="amount" x-model="paymentAmount" required placeholder="0.00" class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono text-sm font-bold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Payment Date *</label>
                            <input type="date" name="payment_date" x-model="paymentDate" required class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Payment Method *</label>
                            <select name="payment_method" x-model="paymentMethod" required class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs">
                                <option value="neft_rtgs">NEFT / RTGS</option>
                                <option value="bank_transfer">Bank Transfer (IMPS)</option>
                                <option value="upi">UPI / QR Code</option>
                                <option value="cheque">Cheque</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">UTR / Cheque / Ref No.</label>
                            <input type="text" name="transaction_reference" x-model="paymentRef" placeholder="e.g. UTR12345678 / CHQ-0045" class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Payment Remarks / Work Notes</label>
                        <textarea name="notes" x-model="paymentNotes" rows="2" placeholder="e.g. Cleared 50% advance for 4MP dome cameras delivery..." class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs"></textarea>
                    </div>
                </div>

                <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-end gap-2">
                    <button type="button" @click="showPaymentModal = false; document.getElementById('supplier-payment-modal').style.display = 'none';" class="btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-xs cursor-pointer">Save Payment Record</button>
                </div>
            </form>
        </div>
    </div>

    {{-- WhatsApp Communique Modal (Icon-free buttons) --}}
    <div id="supplier-whatsapp-modal" x-show="showWhatsAppModal" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="showWhatsAppModal = false; $el.style.display = 'none';">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden"
             @click.away="showWhatsAppModal = false; $root.style.display = 'none';">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-emerald-50/60 dark:bg-emerald-950/30">
                <div class="text-emerald-800 dark:text-emerald-300 font-bold text-sm">
                    1-Click Vendor WhatsApp Inquiry & Advice
                </div>
                <button type="button" @click="showWhatsAppModal = false; document.getElementById('supplier-whatsapp-modal').style.display = 'none';" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg leading-none cursor-pointer">&times;</button>
            </div>
            <form method="POST" action="{{ route('suppliers.shareWhatsApp', $supplier) }}" target="_blank" @submit="showWhatsAppModal = false; document.getElementById('supplier-whatsapp-modal').style.display = 'none';">
                @csrf
                <div class="p-5 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Supplier Mobile Number *</label>
                        <input type="text" name="phone" value="{{ $supplier->phone }}" required class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Pre-built 1-Click Inquiry Template</label>
                        <select name="template_type" x-model="templateType" @change="onWhatsAppTemplateChange()" class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold">
                            <option value="stock_inquiry">Stock & Price Inquiry (Send SKU List for Quotation)</option>
                            <option value="po_dispatch">PO Dispatch (Share Official PO & PDF)</option>
                            <option value="payment_confirmation">Payment Confirmation (Send UTR & Remittance Advice)</option>
                            <option value="payment">Statement of Account & Balance Summary</option>
                            <option value="inquiry">General Vendor Follow-up</option>
                        </select>
                    </div>

                    {{-- Target PO Selector for PO Dispatch --}}
                    <div x-show="templateType === 'po_dispatch'" class="p-3 rounded-xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 space-y-1.5">
                        <label class="block font-bold text-blue-900 dark:text-blue-200">Target Purchase Order *</label>
                        <select name="purchase_order_id" x-model="selectedPoId" @change="onWhatsAppTemplateChange()" class="w-full px-3 py-1.5 border rounded-lg border-blue-300 dark:border-blue-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-mono">
                            <option value="">-- Choose PO to Dispatch --</option>
                            @foreach($supplier->purchaseOrders as $po)
                                <option value="{{ $po->id }}">{{ $po->po_number }} &mdash; Total: ₹{{ number_format((float) $po->total, 2) }} ({{ $po->items->count() }} items)</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-blue-700 dark:text-blue-300">An official signed PDF link is automatically generated and attached to the WhatsApp dispatch message.</p>
                    </div>

                    {{-- Target Payment Selector for Payment Confirmation --}}
                    <div x-show="templateType === 'payment_confirmation'" class="p-3 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 space-y-1.5">
                        <label class="block font-bold text-emerald-900 dark:text-emerald-200">Select Payment Record *</label>
                        <select name="vendor_payment_id" x-model="selectedPaymentId" @change="onWhatsAppTemplateChange()" class="w-full px-3 py-1.5 border rounded-lg border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-mono">
                            <option value="">-- Choose Payment Record --</option>
                            @foreach($supplier->vendorPayments as $pay)
                                <option value="{{ $pay->id }}">{{ $pay->payment_reference }} &mdash; ₹{{ number_format((float) $pay->amount, 2) }} (UTR: {{ $pay->transaction_reference ?: 'N/A' }})</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-emerald-700 dark:text-emerald-300">Auto-injects amount, UTR / transaction ID, payment mode, and date into remittance advice.</p>
                    </div>

                    {{-- Dynamic SKU Quick-Add Chips for Stock & Price Inquiry --}}
                    <div x-show="templateType === 'stock_inquiry' || templateType === 'stock_check'" class="space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-700 dark:text-slate-200">Quick-Add Hardware SKU Presets:</span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <button type="button" @click="customMsg += (customMsg ? '\n' : '') + '• 2MP Dome IP Camera — Qty: 10 Nos'" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 cursor-pointer">+ 2MP Camera</button>
                            <button type="button" @click="customMsg += (customMsg ? '\n' : '') + '• 8CH 4K NVR Recorder — Qty: 2 Nos'" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 cursor-pointer">+ 8CH NVR</button>
                            <button type="button" @click="customMsg += (customMsg ? '\n' : '') + '• 4TB Surveillance HDD — Qty: 4 Nos'" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 cursor-pointer">+ 4TB HDD</button>
                            <button type="button" @click="customMsg += (customMsg ? '\n' : '') + '• Cat6 UTP 305m Cable Box — Qty: 2 Boxes'" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 cursor-pointer">+ Cat6 Cable</button>
                            <button type="button" @click="customMsg += (customMsg ? '\n' : '') + '• 8-Port Gigabit PoE Switch — Qty: 2 Nos'" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 cursor-pointer">+ PoE Switch</button>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">
                            <span x-text="templateType === 'stock_inquiry' ? 'Product SKU List / Inquiry Scope *' : 'Custom Notes / Message'"></span>
                        </label>
                        <textarea name="message" x-model="customMsg" rows="4" placeholder="Enter product SKUs, models, quantities, or special instructions..." class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono text-xs"></textarea>
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-end gap-2">
                    <button type="button" @click="showWhatsAppModal = false; document.getElementById('supplier-whatsapp-modal').style.display = 'none';" class="btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs cursor-pointer">
                        Open WhatsApp & Send
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Email Dispatch Modal (Icon-free buttons) --}}
    <div id="supplier-email-modal" x-show="showEmailModal" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="showEmailModal = false; $el.style.display = 'none';">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden"
             @click.away="showEmailModal = false; $root.style.display = 'none';">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-blue-50/60 dark:bg-blue-950/30">
                <div class="text-blue-800 dark:text-blue-300 font-bold text-sm">
                    1-Click Vendor Email Dispatch & Inquiry
                </div>
                <button type="button" @click="showEmailModal = false; document.getElementById('supplier-email-modal').style.display = 'none';" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg leading-none cursor-pointer">&times;</button>
            </div>
            <form method="POST" action="{{ route('suppliers.sendEmail', $supplier) }}" @submit="showEmailModal = false; document.getElementById('supplier-email-modal').style.display = 'none';">
                @csrf
                <div class="p-5 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Recipient Email *</label>
                        <input type="email" name="email" value="{{ $supplier->email }}" required class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Pre-built 1-Click Email Template</label>
                        <select name="template_type" x-model="emailTemplateType" @change="onEmailTemplateChange()" class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold">
                            <option value="stock_inquiry">Stock & Price Inquiry (SKU List & Availability)</option>
                            <option value="po_dispatch">PO Dispatch (Purchase Order Details & Signed PDF)</option>
                            <option value="payment_confirmation">Payment Confirmation (Remittance Advice & UTR)</option>
                            <option value="inquiry">General Vendor Inquiry</option>
                        </select>
                    </div>

                    {{-- Target PO Selector for Email PO Dispatch --}}
                    <div x-show="emailTemplateType === 'po_dispatch'" class="p-3 rounded-xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 space-y-1.5">
                        <label class="block font-bold text-blue-900 dark:text-blue-200">Select Purchase Order *</label>
                        <select name="purchase_order_id" x-model="emailPoId" @change="onEmailTemplateChange()" class="w-full px-3 py-1.5 border rounded-lg border-blue-300 dark:border-blue-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-mono">
                            <option value="">-- Choose PO --</option>
                            @foreach($supplier->purchaseOrders as $po)
                                <option value="{{ $po->id }}">{{ $po->po_number }} &mdash; Total: ₹{{ number_format((float) $po->total, 2) }} ({{ $po->items->count() }} items)</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Target Payment Selector for Email Payment Confirmation --}}
                    <div x-show="emailTemplateType === 'payment_confirmation'" class="p-3 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 space-y-1.5">
                        <label class="block font-bold text-emerald-900 dark:text-emerald-200">Select Payment Record *</label>
                        <select name="vendor_payment_id" x-model="emailPaymentId" @change="onEmailTemplateChange()" class="w-full px-3 py-1.5 border rounded-lg border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-mono">
                            <option value="">-- Choose Payment Record --</option>
                            @foreach($supplier->vendorPayments as $pay)
                                <option value="{{ $pay->id }}">{{ $pay->payment_reference }} &mdash; ₹{{ number_format((float) $pay->amount, 2) }} (UTR: {{ $pay->transaction_reference ?: 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Email Subject *</label>
                        <input type="text" name="subject" x-model="emailSubject" required class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Message Content / SKU Specifications *</label>
                        <textarea name="message_body" x-model="emailMsg" rows="5" required class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono text-xs"></textarea>
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-end gap-2">
                    <button type="button" @click="showEmailModal = false; document.getElementById('supplier-email-modal').style.display = 'none';" class="btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-xs cursor-pointer">
                        Send Official Email
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <script>
        function openSupplierWhatsApp() {
            window.dispatchEvent(new CustomEvent('open-supplier-whatsapp'));
            const m = document.getElementById('supplier-whatsapp-modal');
            if (m) m.style.display = 'flex';
        }
        function closeSupplierWhatsApp() {
            window.dispatchEvent(new CustomEvent('close-supplier-whatsapp'));
            const m = document.getElementById('supplier-whatsapp-modal');
            if (m) m.style.display = 'none';
        }
        function openSupplierEmail() {
            window.dispatchEvent(new CustomEvent('open-supplier-email'));
            const m = document.getElementById('supplier-email-modal');
            if (m) m.style.display = 'flex';
        }
        function closeSupplierEmail() {
            window.dispatchEvent(new CustomEvent('close-supplier-email'));
            const m = document.getElementById('supplier-email-modal');
            if (m) m.style.display = 'none';
        }
        function openSupplierPayment(poId = '', dueAmount = 0) {
            window.dispatchEvent(new CustomEvent('open-supplier-payment', { detail: { poId, dueAmount } }));
            const m = document.getElementById('supplier-payment-modal');
            if (m) m.style.display = 'flex';
        }
        function closeSupplierPayment() {
            window.dispatchEvent(new CustomEvent('close-supplier-payment'));
            const m = document.getElementById('supplier-payment-modal');
            if (m) m.style.display = 'none';
        }

        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSupplierWhatsApp();
                closeSupplierEmail();
                closeSupplierPayment();
            }
        });
    </script>
</x-app-layout>
