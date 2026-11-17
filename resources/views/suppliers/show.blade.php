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
                        <button type="button" onclick="openSupplierPayment('', {{ (float) $supplier->balancePayable() }})" class="btn-primary text-xs font-bold cursor-pointer">
                            Record Paid Amount
                        </button>
                    @endif
                    @if($supplier->phone)
                        <button type="button" onclick="openSupplierWhatsApp()" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs cursor-pointer">
                            <span>WhatsApp</span>
                        </button>
                    @endif
                    @if($supplier->email)
                        <button type="button" onclick="openSupplierEmail()" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-xs cursor-pointer">
                            <span>Send Email</span>
                        </button>
                    @endif
                    @if(auth()->user()->isOffice())
                        <a href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}" class="btn-secondary text-xs">
                            New Purchase Order
                        </a>
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-secondary text-xs">
                            Edit Supplier
                        </a>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm="Are you sure you want to delete supplier {{ $supplier->name }}?" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger-link p-0 bg-transparent border-0 cursor-pointer text-xs ml-2">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div x-data="{
        showWhatsAppModal: false,
        showEmailModal: false,
        showPaymentModal: false,
        selectedPoId: '',
        paymentAmount: '',
        paymentMethod: 'neft_rtgs',
        paymentDate: '{{ now()->format('Y-m-d') }}',
        paymentRef: '',
        paymentNotes: '',
        templateType: 'inquiry',
        customMsg: '',
        copiedKey: null,
        openPaymentModal(poId = '', dueAmount = 0) {
            this.selectedPoId = poId ? String(poId) : '';
            this.paymentAmount = dueAmount > 0 ? Number(dueAmount).toFixed(2) : '';
            this.paymentDate = '{{ now()->format('Y-m-d') }}';
            this.paymentRef = '';
            this.paymentNotes = '';
            this.showPaymentModal = true;
            const m = document.getElementById('supplier-payment-modal');
            if (m) m.style.display = 'flex';
        },
        copyText(text, key) {
            if (!text) return;
            navigator.clipboard.writeText(text);
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

        {{-- Financial & Work Metrics Overview (Cost-Based Work Ledger) --}}
        @php
            $totalVolume = $supplier->totalPurchaseVolume();
            $totalPaid = (float) $supplier->purchaseOrders->sum('amount_paid');
            $balanceDue = $supplier->balancePayable();
            $ordersCount = $supplier->purchaseOrders->count();
            $unpaidOrdersCount = $supplier->purchaseOrders->whereIn('payment_status', ['unpaid', 'partially_paid'])->count();
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Procurement Cost</div>
                <div class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-1">₹{{ number_format($totalVolume, 2) }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Total value of all ordered work</div>
            </div>
            <div class="p-4 rounded-xl border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/50 dark:bg-emerald-950/20 shadow-xs">
                <div class="text-xs font-medium text-emerald-700 dark:text-emerald-400">Total Paid Amount Recorded</div>
                <div class="text-xl font-bold font-mono text-emerald-700 dark:text-emerald-400 mt-1">₹{{ number_format($totalPaid, 2) }}</div>
                <div class="text-[11px] text-emerald-600/80 dark:text-emerald-500 mt-1">Cleared via offline transfers</div>
            </div>
            <div class="p-4 rounded-xl border {{ $balanceDue > 0 ? 'border-amber-200 dark:border-amber-900/60 bg-amber-50/50 dark:bg-amber-950/20' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900' }} shadow-xs">
                <div class="text-xs font-medium {{ $balanceDue > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400' }}">Remaining Balance Due</div>
                <div class="text-xl font-bold font-mono {{ $balanceDue > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-900 dark:text-white' }} mt-1">₹{{ number_format($balanceDue, 2) }}</div>
                <div class="text-[11px] {{ $balanceDue > 0 ? 'text-amber-600/80 dark:text-amber-500' : 'text-slate-500' }} mt-1">Outstanding vendor liability</div>
            </div>
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Purchase Orders</div>
                <div class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-1">{{ $ordersCount }}</div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $unpaidOrdersCount }} order(s) pending payment</div>
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
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800 mb-3">
                        <h3 style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">Bank & Payment Details</h3>
                        <span class="text-[11px] text-slate-500 font-medium">For Offline Transfers</span>
                    </div>

                    @if($supplier->bank_name || $supplier->bank_account_number || $supplier->bank_ifsc || $supplier->upi_id)
                        <div class="space-y-3 text-xs">
                            @if($supplier->bank_name)
                                <div>
                                    <div class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">Bank Name</div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white mt-0.5">{{ $supplier->bank_name }}</div>
                                </div>
                            @endif

                            @if($supplier->bank_account_number)
                                <div>
                                    <div class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">Account Number</div>
                                    <div class="flex items-center justify-between mt-0.5 p-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                        <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $supplier->bank_account_number }}</span>
                                        <button type="button" @click="copyText('{{ $supplier->bank_account_number }}', 'acc')" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 cursor-pointer">
                                            <span x-text="copiedKey === 'acc' ? 'Copied!' : 'Copy'"></span>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-3">
                                @if($supplier->bank_ifsc)
                                    <div>
                                        <div class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">IFSC Code</div>
                                        <div class="flex items-center justify-between mt-0.5 p-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                            <span class="font-mono font-bold text-slate-900 dark:text-white uppercase">{{ $supplier->bank_ifsc }}</span>
                                            <button type="button" @click="copyText('{{ $supplier->bank_ifsc }}', 'ifsc')" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 cursor-pointer">
                                                <span x-text="copiedKey === 'ifsc' ? 'Copied!' : 'Copy'"></span>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if($supplier->bank_branch)
                                    <div>
                                        <div class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">Branch</div>
                                        <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white truncate">
                                            {{ $supplier->bank_branch }}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if($supplier->upi_id)
                                <div>
                                    <div class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">UPI ID / VPA</div>
                                    <div class="flex items-center justify-between mt-0.5 p-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                        <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $supplier->upi_id }}</span>
                                        <button type="button" @click="copyText('{{ $supplier->upi_id }}', 'upi')" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 cursor-pointer">
                                            <span x-text="copiedKey === 'upi' ? 'Copied!' : 'Copy'"></span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="py-4 text-center text-xs text-slate-500">
                            No bank details registered yet.
                            @if(auth()->user()->isOffice())
                                <div class="mt-2">
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="text-blue-600 hover:underline font-semibold">Add bank details</a>
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
                    <button type="button" @click="openPaymentModal('', {{ $supplier->balancePayable() }})" class="btn-secondary text-xs font-bold cursor-pointer">
                        Record Paid Amount
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
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('purchase-orders.show', $po) }}" class="plain-link text-xs font-semibold">
                                            View
                                        </a>
                                        @if(auth()->user()->isOffice() && $due > 0)
                                            <button type="button" @click="openPaymentModal({{ $po->id }}, {{ $due }})" class="text-xs font-bold text-blue-600 hover:underline cursor-pointer">
                                                Update Paid
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-6 text-slate-500 text-sm">
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
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white" style="margin: 0; padding: 0; border: none;">Record Offline Payment</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Update work ledger with paid amount (NEFT, RTGS, Cheque, UPI, Cash)</p>
                </div>
                <button type="button" @click="showPaymentModal = false; document.getElementById('supplier-payment-modal').style.display = 'none';" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg leading-none cursor-pointer">&times;</button>
            </div>
            <form method="POST" action="{{ route('suppliers.recordPayment', $supplier) }}" @submit="showPaymentModal = false; document.getElementById('supplier-payment-modal').style.display = 'none';">
                @csrf
                <div class="p-5 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Target Purchase Order (Optional)</label>
                        <select name="purchase_order_id" x-model="selectedPoId" class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs">
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
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Paid Amount (₹) *</label>
                            <input type="number" step="0.01" min="0.01" name="amount" x-model="paymentAmount" required placeholder="0.00" class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono text-sm">
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
                    <button type="submit" class="btn-primary text-xs font-bold">Save Payment Record</button>
                </div>
            </form>
        </div>
    </div>

    {{-- WhatsApp Communique Modal (Icon-free buttons) --}}
    <div id="supplier-whatsapp-modal" x-show="showWhatsAppModal" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="showWhatsAppModal = false; $el.style.display = 'none';">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
             @click.away="showWhatsAppModal = false; $root.style.display = 'none';">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-emerald-50/60 dark:bg-emerald-950/30">
                <div class="text-emerald-800 dark:text-emerald-300 font-bold text-sm">
                    Share with Supplier on WhatsApp
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
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Message Template</label>
                        <select name="template_type" x-model="templateType" class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            <option value="inquiry">General Vendor Inquiry & Follow-up</option>
                            <option value="payment">Statement of Account & Payment Advice</option>
                            <option value="stock_check">Stock Availability & Pricing Inquiry</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Custom Notes / Message (Optional)</label>
                        <textarea name="message" x-model="customMsg" rows="3" placeholder="Add specific equipment, part numbers or remarks..." class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
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
                    Dispatch Email to Supplier
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
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Email Subject *</label>
                        <input type="text" name="subject" value="Procurement Inquiry / Communique — Precision IT Systems" required class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Message Body *</label>
                        <textarea name="message_body" rows="5" required placeholder="Type official message, inquiries, delivery reminders or payment details..." class="w-full px-3 py-2 border rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">Dear {{ $supplier->contact_person ?: ($supplier->company_name ?: $supplier->name) }},

Greetings from Precision IT Systems.

We are writing regarding our procurement collaboration. Kindly update us on current product availability and pricing schedules.

Best regards,
Precision IT Systems Operations Team</textarea>
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
