<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Online Payment | {{ config('app.name', 'CCTV Security Solutions') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0b0f19; color: #f8fafc; }
        .glass-panel {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
        }
        .tab-btn.active {
            background: #4f46e5;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }
    </style>
</head>
<body class="min-h-screen py-8 px-4 flex flex-col items-center justify-center">

    <div class="w-full max-w-xl">
        {{-- Brand Bar --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-xl shadow-lg shadow-indigo-500/30">
                    🛡️
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white tracking-tight">{{ config('app.name') === 'Laravel' ? 'Precision IT Systems' : config('app.name') }}</h1>
                    <p class="text-xs text-slate-400">256-Bit SSL Encrypted Payment Gateway</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-950/80 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Instant Clearance
            </div>
        </div>

        {{-- Main Checkout Card --}}
        <div class="glass-panel rounded-3xl p-6 sm:p-8 relative overflow-hidden">
            
            {{-- Bill Summary Box --}}
            <div class="bg-slate-900/90 rounded-2xl p-5 border border-slate-800 mb-6">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="text-[10px] uppercase font-extrabold tracking-wider text-indigo-400">
                            {{ $invoice ? 'Tax Invoice' : 'Security Quotation' }}
                        </span>
                        <h2 class="text-base font-bold text-white">
                            {{ $invoice ? $invoice->invoice_no : $quotation->quotation_no }}
                        </h2>
                    </div>
                    <span id="badge-plan-status" class="px-2.5 py-1 rounded-lg text-xs font-extrabold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        {{ $invoice ? ucfirst($invoice->status) : 'Advance Payment' }}
                    </span>
                </div>

                <div class="text-xs text-slate-400 mb-4">
                    Customer: <strong class="text-slate-200">{{ $lead->customer_name }}</strong> · {{ $lead->phone }}
                </div>

                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-800 text-xs">
                    <div>
                        <span class="text-slate-500">Total Project Value:</span>
                        <div class="font-bold text-slate-200">₹{{ number_format((float)$totalAmount, 2) }}</div>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-500" id="summary-payable-label">Selected Amount:</span>
                        <div class="text-xl font-black text-emerald-400" id="summary-payable-val">₹{{ number_format((float)$payableAmount, 2) }}</div>
                    </div>
                </div>
            </div>

            {{-- Flexible Payment Plan Options (Advance vs Full Total) --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-300 mb-1.5">
                    1. Choose Payment Option
                </label>
                <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                    {{-- Advance Deposit Pill --}}
                    <button type="button" id="plan-btn-advance" onclick="selectPaymentPlan('advance')" 
                            class="flex flex-col text-left p-3.5 rounded-2xl border-2 border-indigo-500 bg-indigo-500/10 transition-all cursor-pointer">
                        <div class="flex items-center justify-between w-full mb-1">
                            <span class="text-xs font-bold text-indigo-300">💳 Pay Advance (50%)</span>
                            <span class="w-3.5 h-3.5 rounded-full border-2 border-indigo-400 bg-indigo-400 flex items-center justify-center text-[9px] text-slate-950 font-black">✓</span>
                        </div>
                        <div class="text-base font-black text-white">₹{{ number_format((float)$advanceAmount, 2) }}</div>
                        <span class="text-[10px] text-slate-400 mt-0.5">Booking deposit before work starts</span>
                    </button>

                    {{-- Full Payment Pill --}}
                    <button type="button" id="plan-btn-full" onclick="selectPaymentPlan('full')" 
                            class="flex flex-col text-left p-3.5 rounded-2xl border-2 border-slate-800 bg-slate-900/50 hover:border-slate-700 transition-all cursor-pointer">
                        <div class="flex items-center justify-between w-full mb-1">
                            <span class="text-xs font-bold text-slate-300">💰 Pay Full Total (100%)</span>
                            <span id="plan-radio-full" class="w-3.5 h-3.5 rounded-full border-2 border-slate-600 bg-transparent"></span>
                        </div>
                        <div class="text-base font-black text-white">₹{{ number_format((float)$fullAmount, 2) }}</div>
                        <span class="text-[10px] text-slate-400 mt-0.5">Complete project payment</span>
                    </button>
                </div>
            </div>

            {{-- Custom Amount Input (Collapsible/Editable) --}}
            <div class="mb-6 bg-slate-900/40 p-3 rounded-2xl border border-slate-800/80">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-sm font-semibold text-slate-300">
                        Amount to Pay (₹):
                    </label>
                    <span class="text-[10px] text-slate-500">Max: ₹{{ number_format((float)$fullAmount, 2) }}</span>
                </div>
                <div class="relative">
                    <span class="absolute left-4 top-2.5 text-base font-bold text-slate-400">₹</span>
                    <input type="number" step="0.01" min="1" max="{{ $fullAmount }}" id="pay-amount-input" value="{{ $payableAmount }}"
                           class="w-full pl-8 pr-24 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-base font-black text-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                    <button type="button" onclick="selectPaymentPlan('full')" class="absolute right-2 top-1.5 px-2.5 py-1 rounded-lg bg-indigo-950 text-indigo-300 hover:bg-indigo-900 text-[11px] font-bold border border-indigo-700/50">
                        Set Full
                    </button>
                </div>
            </div>

            {{-- 2. Choose Payment Method Tabs --}}
            <div class="mb-3">
                <label class="block text-sm font-semibold text-slate-300 mb-1.5">
                    2. Select Payment Method
                </label>
                <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-900/90 rounded-xl border border-slate-800">
                    <button type="button" onclick="switchPaymentTab('razorpay', this)" class="tab-btn active py-2.5 px-2 text-[11px] font-bold rounded-lg transition-all flex items-center justify-center gap-1 text-center">
                        ⚡ Online (Cards/UPI)
                    </button>
                    <button type="button" onclick="switchPaymentTab('upi', this)" class="tab-btn py-2.5 px-2 text-[11px] font-bold text-slate-400 hover:text-white rounded-lg transition-all flex items-center justify-center gap-1 text-center">
                        📱 Direct UPI QR Code
                    </button>
                    <button type="button" onclick="switchPaymentTab('cash', this)" class="tab-btn py-2.5 px-2 text-[11px] font-bold text-slate-400 hover:text-white rounded-lg transition-all flex items-center justify-center gap-1 text-center">
                        💵 Pay by Cash
                    </button>
                </div>
            </div>

            {{-- Tab 1: Razorpay Gateway Checkout --}}
            <div id="tab-razorpay" class="payment-tab-content pt-2">
                <div class="bg-indigo-950/40 border border-indigo-800/40 rounded-2xl p-3.5 mb-4 text-xs text-indigo-200 flex items-start gap-2.5">
                    <span class="text-base">⚡</span>
                    <div>
                        <strong class="text-white">Instant Payment Confirmation:</strong>
                        Google Pay, PhonePe, Paytm, all Credit/Debit Cards, NetBanking (50+ banks) & Wallets.
                    </div>
                </div>

                <button type="button" id="razorpay-pay-btn" onclick="initiateRazorpayPayment()"
                        class="w-full py-3.5 px-5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-extrabold text-sm rounded-2xl shadow-xl shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all transform active:scale-95">
                    <span id="btn-spinner" class="hidden animate-spin">⏳</span>
                    <span id="razorpay-btn-label">Pay ₹{{ number_format((float)$payableAmount, 2) }} Online Now</span>
                    <span class="text-indigo-200">→</span>
                </button>

                <div class="mt-3 flex items-center justify-center gap-3 text-slate-500 text-[11px]">
                    <span>🔒 256-Bit SSL</span>
                    <span>•</span>
                    <span>RBI Compliant</span>
                    <span>•</span>
                    <span>Instant Receipt</span>
                </div>
            </div>

            {{-- Tab 2: Dynamic UPI QR Code & Intent Apps --}}
            <div id="tab-upi" class="payment-tab-content hidden pt-2">
                <div class="text-center bg-slate-900/90 rounded-2xl p-5 border border-slate-800 mb-4">
                    <p class="text-xs text-slate-400 mb-2">Scan with Google Pay, PhonePe, Paytm, or BHIM</p>
                    
                    <div class="inline-block p-2.5 bg-white rounded-2xl shadow-lg mb-2.5">
                        <img id="upi-qr-image" src="{{ $upiPayload['qr_image_url'] }}" alt="UPI Payment QR Code" class="w-44 h-44 mx-auto">
                    </div>

                    <div class="text-xs font-mono text-slate-400 bg-slate-950 py-1.5 px-3 rounded-lg inline-flex items-center gap-2 border border-slate-800 mb-3">
                        <span id="vpa-display">{{ $upiPayload['vpa'] }}</span>
                        <button type="button" onclick="copyVpa()" class="text-indigo-400 hover:text-indigo-300 font-bold">Copy</button>
                    </div>

                    {{-- Deep Link for Mobile Devices --}}
                    <div class="pt-3 border-t border-slate-800 flex flex-wrap justify-center gap-2">
                        <a id="upi-intent-link" href="{{ $upiPayload['upi_url'] }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-md">
                            📱 Open UPI App Directly
                        </a>
                    </div>
                </div>

                {{-- Submit UTR / Reference after manual UPI Scan --}}
                <div class="bg-slate-900/60 rounded-2xl p-4 border border-slate-800">
                    <h4 class="text-xs font-bold text-slate-300 mb-1.5">Paid via UPI Scanner? Submit UTR for instant verification:</h4>
                    <div class="flex gap-2">
                        <input type="text" id="manual-utr-input" placeholder="e.g. 12-digit UTR: 421987654321" class="flex-1 px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white">
                        <button type="button" onclick="submitManualUtr()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl font-bold">
                            Verify
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tab 3: Pay by Cash on Site / Installation Handover --}}
            <div id="tab-cash" class="payment-tab-content hidden pt-2">
                <div class="bg-emerald-950/40 border border-emerald-800/40 rounded-2xl p-4 mb-4 text-xs text-emerald-200">
                    <div class="flex items-center gap-2 text-white font-bold text-sm mb-1.5">
                        <span>💵</span>
                        <span>Cash Payment on Site / Handover</span>
                    </div>
                    <p class="leading-relaxed text-emerald-100/90 mb-3">
                        No online payment required right now. You can pay in <strong>Cash directly to our certified technician / installation engineer</strong> upon arrival at your site or after the CCTV system setup is complete.
                    </p>
                    <div class="bg-slate-950/70 p-3 rounded-xl border border-emerald-900/50 space-y-1 text-slate-300 text-xs">
                        <div class="flex justify-between"><span class="text-slate-400">Site Location:</span><span class="font-bold text-slate-200 text-right">{{ $lead->site_address ?: 'Customer Address' }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-400">Cash Amount:</span><strong class="text-emerald-400" id="cash-amount-display">₹{{ number_format((float)$payableAmount, 2) }}</strong></div>
                        <div class="flex justify-between"><span class="text-slate-400">Receipt:</span><span class="text-slate-200">Official printed & digital receipt given on site</span></div>
                    </div>
                </div>

                <button type="button" id="cash-confirm-btn" onclick="submitCashPaymentChoice()"
                        class="w-full py-3.5 px-5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 text-white font-black text-sm rounded-2xl shadow-xl shadow-emerald-600/25 flex items-center justify-center gap-2 transition-all transform active:scale-95">
                    <span id="cash-btn-spinner" class="hidden animate-spin">⏳</span>
                    <span id="cash-btn-label">✓ Confirm Booking with Cash on Installation</span>
                </button>
            </div>

            {{-- Success Modal / Card (Hidden by default) --}}
            <div id="payment-success-card" class="hidden absolute inset-0 bg-slate-950/98 p-8 flex flex-col items-center justify-center text-center z-20">
                <div class="w-16 h-16 rounded-full bg-emerald-500/20 border-2 border-emerald-500 flex items-center justify-center text-3xl mb-4 text-emerald-400">
                    ✓
                </div>
                <h3 class="text-xl font-black text-white mb-1" id="success-heading">Payment Successful!</h3>
                <p class="text-xs text-slate-400 mb-4" id="success-receipt-info">Receipt generated & dispatched via WhatsApp & Email.</p>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 w-full max-w-sm mb-6 text-left text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-800"><span class="text-slate-500">Receipt / Ref No:</span><strong class="text-slate-200" id="success-receipt-no">-</strong></div>
                    <div class="flex justify-between py-1 border-b border-slate-800"><span class="text-slate-500">Amount:</span><strong class="text-emerald-400 font-bold" id="success-amount">-</strong></div>
                    <div class="flex justify-between py-1 border-b border-slate-800"><span class="text-slate-500">Payment Mode:</span><strong class="text-indigo-300 font-bold" id="success-method">-</strong></div>
                    <div class="flex justify-between py-1"><span class="text-slate-500">Status:</span><code class="text-emerald-300 text-[10px] font-bold" id="success-txn-id">Confirmed</code></div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full max-w-sm">
                    <a id="download-receipt-link" href="#" target="_blank" class="flex-1 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs text-center shadow-lg shadow-emerald-600/30">
                        📄 View Receipt / Acknowledgement
                    </a>
                    @if(auth()->check() && auth()->user()->isCustomer())
                    <a href="{{ route('portal.dashboard') }}" class="py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs text-center">
                        Back to Portal
                    </a>
                    @endif
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="mt-6 text-center text-xs text-slate-500">
            Powered by {{ config('app.name', 'CCTV CRM') }} Secure Billing Gateway · Support: billing@precisionit.com
        </div>
    </div>

    <script>
        const invoiceId = @json($invoice?->id);
        const quotationId = @json($quotation?->id);
        const advanceAmount = {{ (float)$advanceAmount }};
        const fullAmount = {{ (float)$fullAmount }};
        const customerName = @json($lead->customer_name);
        const customerEmail = @json($lead->email ?? '');
        const customerPhone = @json($lead->phone ?? '');
        const vpaId = @json($upiPayload['vpa']);
        const merchantName = @json($upiPayload['merchant']);

        let currentPlan = 'advance';

        function selectPaymentPlan(plan) {
            currentPlan = plan;
            const btnAdv = document.getElementById('plan-btn-advance');
            const btnFull = document.getElementById('plan-btn-full');
            const radioFull = document.getElementById('plan-radio-full');

            if (plan === 'advance') {
                document.getElementById('pay-amount-input').value = advanceAmount;
                btnAdv.className = "flex flex-col text-left p-3.5 rounded-2xl border-2 border-indigo-500 bg-indigo-500/10 transition-all cursor-pointer";
                btnFull.className = "flex flex-col text-left p-3.5 rounded-2xl border-2 border-slate-800 bg-slate-900/50 hover:border-slate-700 transition-all cursor-pointer";
                radioFull.className = "w-3.5 h-3.5 rounded-full border-2 border-slate-600 bg-transparent";
            } else {
                document.getElementById('pay-amount-input').value = fullAmount;
                btnFull.className = "flex flex-col text-left p-3.5 rounded-2xl border-2 border-emerald-500 bg-emerald-500/10 transition-all cursor-pointer";
                btnAdv.className = "flex flex-col text-left p-3.5 rounded-2xl border-2 border-slate-800 bg-slate-900/50 hover:border-slate-700 transition-all cursor-pointer";
                radioFull.className = "w-3.5 h-3.5 rounded-full border-2 border-emerald-400 bg-emerald-400 flex items-center justify-center text-[9px] text-slate-950 font-black";
                radioFull.innerText = '✓';
            }
            updatePayDisplays();
        }

        function switchPaymentTab(tab, btn) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.payment-tab-content').forEach(c => c.classList.add('hidden'));
            btn.classList.add('active');
            document.getElementById('tab-' + tab).classList.remove('hidden');
        }

        document.getElementById('pay-amount-input').addEventListener('input', updatePayDisplays);

        function updatePayDisplays() {
            const amt = parseFloat(document.getElementById('pay-amount-input').value) || 0;
            const formatted = '₹' + amt.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            document.getElementById('summary-payable-val').innerText = formatted;
            document.getElementById('razorpay-btn-label').innerText = `Pay ${formatted} Online Now`;
            document.getElementById('cash-amount-display').innerText = formatted;

            updateUpiQr();
        }

        function updateUpiQr() {
            const amt = parseFloat(document.getElementById('pay-amount-input').value) || 1;
            const ref = invoiceId ? `INV-${invoiceId}` : `QT-${quotationId}`;
            const upiUrl = `upi://pay?pa=${encodeURIComponent(vpaId)}&pn=${encodeURIComponent(merchantName)}&am=${amt.toFixed(2)}&cu=INR&tn=${encodeURIComponent('Payment for ' + ref)}&tr=${ref}`;
            document.getElementById('upi-qr-image').src = `https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=${encodeURIComponent(upiUrl)}`;
            document.getElementById('upi-intent-link').href = upiUrl;
        }

        function copyVpa() {
            navigator.clipboard.writeText(vpaId);
            alert('UPI ID copied to clipboard: ' + vpaId);
        }

        async function initiateRazorpayPayment() {
            const amount = parseFloat(document.getElementById('pay-amount-input').value);
            if (!amount || amount <= 0) {
                alert('Please enter a valid payment amount.');
                return;
            }

            const btn = document.getElementById('razorpay-pay-btn');
            const spinner = document.getElementById('btn-spinner');
            btn.disabled = true;
            spinner.classList.remove('hidden');

            try {
                // 1. Create order on server
                const orderRes = await fetch('{{ route('payment.create-order') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        invoice_id: invoiceId,
                        quotation_id: quotationId,
                        amount: amount
                    })
                });

                const orderData = await orderRes.json();

                if (!orderData.success) {
                    throw new Error(orderData.message || 'Failed to initialize payment session.');
                }

                // 2. Open Razorpay Checkout Modal
                const options = {
                    key: orderData.key_id,
                    amount: orderData.amount_paise,
                    currency: orderData.currency,
                    name: merchantName,
                    description: invoiceId ? `Invoice Payment #${invoiceId}` : `Quotation Payment #${quotationId}`,
                    image: 'https://cdn-icons-png.flaticon.com/512/900/900782.png',
                    order_id: orderData.order_id,
                    handler: async function (response) {
                        await verifyAndConfirmPayment(response, amount, orderData.receipt, 'razorpay');
                    },
                    prefill: {
                        name: customerName,
                        email: customerEmail,
                        contact: customerPhone
                    },
                    theme: {
                        color: '#4f46e5'
                    },
                    modal: {
                        ondismiss: function() {
                            btn.disabled = false;
                            spinner.classList.add('hidden');
                        }
                    }
                };

                // If sandbox demo order, provide immediate mock payment callback
                if (orderData.is_mock) {
                    const simulateSuccess = confirm(`[DEMO / TEST GATEWAY MODE]\n\nSimulate successful online payment of ₹${amount} for order ${orderData.order_id}?`);
                    if (simulateSuccess) {
                        await verifyAndConfirmPayment({
                            razorpay_order_id: orderData.order_id,
                            razorpay_payment_id: 'pay_demo_' + Math.random().toString(36).substring(2, 10),
                            razorpay_signature: 'sim_sig_' + Math.random().toString(36).substring(2, 10)
                        }, amount, orderData.receipt, 'razorpay');
                        return;
                    }
                }

                const rzp = new Razorpay(options);
                rzp.open();

            } catch (err) {
                alert('Payment error: ' + err.message);
                btn.disabled = false;
                spinner.classList.add('hidden');
            }
        }

        async function verifyAndConfirmPayment(rzpResponse, amount, receiptNo, method) {
            try {
                const verifyRes = await fetch('{{ route('payment.verify') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        invoice_id: invoiceId,
                        quotation_id: quotationId,
                        amount: amount,
                        receipt_no: receiptNo,
                        razorpay_order_id: rzpResponse?.razorpay_order_id,
                        razorpay_payment_id: rzpResponse?.razorpay_payment_id,
                        razorpay_signature: rzpResponse?.razorpay_signature,
                        method: method || 'razorpay'
                    })
                });

                const result = await verifyRes.json();

                if (result.success) {
                    showPaymentSuccess(result, method);
                } else {
                    alert('Payment verification failed: ' + result.message);
                }
            } catch (e) {
                alert('Verification server error: ' + e.message);
            }
        }

        async function submitManualUtr() {
            const utr = document.getElementById('manual-utr-input').value.trim();
            if (!utr) {
                alert('Please enter your 12-digit UPI UTR / Transaction Reference number.');
                return;
            }
            const amount = parseFloat(document.getElementById('pay-amount-input').value);

            try {
                const res = await fetch('{{ route('payment.verify') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        invoice_id: invoiceId,
                        quotation_id: quotationId,
                        amount: amount,
                        method: 'upi',
                        reference_no: utr
                    })
                });

                const result = await res.json();
                if (result.success) {
                    showPaymentSuccess(result, 'upi');
                } else {
                    alert('Error recording UPI transfer: ' + result.message);
                }
            } catch (e) {
                alert('Server error: ' + e.message);
            }
        }

        async function submitCashPaymentChoice() {
            const amount = parseFloat(document.getElementById('pay-amount-input').value);
            if (!amount || amount <= 0) {
                alert('Please enter a valid amount.');
                return;
            }

            const btn = document.getElementById('cash-confirm-btn');
            const spinner = document.getElementById('cash-btn-spinner');
            btn.disabled = true;
            spinner.classList.remove('hidden');

            try {
                const res = await fetch('{{ route('payment.verify') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        invoice_id: invoiceId,
                        quotation_id: quotationId,
                        amount: amount,
                        method: 'cash',
                        reference_no: 'COD-' + Math.floor(100000 + Math.random() * 900000)
                    })
                });

                const result = await res.json();
                if (result.success) {
                    showPaymentSuccess(result, 'cash');
                } else {
                    alert('Error confirming cash order: ' + result.message);
                    btn.disabled = false;
                    spinner.classList.add('hidden');
                }
            } catch (e) {
                alert('Server error: ' + e.message);
                btn.disabled = false;
                spinner.classList.add('hidden');
            }
        }

        function showPaymentSuccess(data, method) {
            const isCash = (method === 'cash');
            document.getElementById('success-heading').innerText = isCash ? 'Booking Confirmed (Cash on Site)!' : 'Payment Successful!';
            document.getElementById('success-receipt-info').innerText = isCash 
                ? 'Your site installation has been scheduled. Cash will be collected by our engineer on site.' 
                : 'Receipt generated & dispatched via WhatsApp & Email.';
            document.getElementById('success-receipt-no').innerText = data.payment.receipt_no;
            document.getElementById('success-amount').innerText = '₹' + parseFloat(data.payment.amount).toFixed(2);
            document.getElementById('success-method').innerText = isCash ? 'Cash on Site / Installation' : (data.payment.method === 'upi' ? 'Direct UPI Transfer' : 'Online Razorpay');
            document.getElementById('success-txn-id').innerText = data.payment.reference_no || data.payment.gateway_payment_id || (isCash ? 'Pending Site Collection' : 'Completed');
            document.getElementById('download-receipt-link').href = `/payments/${data.payment.id}/receipt`;
            document.getElementById('payment-success-card').classList.remove('hidden');
        }
    </script>
</body>
</html>
