<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Online Payment | {{ config('app.name', 'CCTV Security Solutions') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f8fafc; color: #1e293b; }
        * { box-shadow: none !important; }
        .tab-btn.active {
            background-color: #2563eb;
            color: #ffffff;
        }
    </style>
</head>
<body class="min-h-screen py-8 px-4 flex flex-col items-center justify-center bg-slate-50">

    <div class="w-full max-w-xl">
        {{-- Brand Bar --}}
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-lg font-bold text-slate-900 tracking-tight">{{ config('app.name') === 'Laravel' ? 'Precision IT Systems' : config('app.name') }}</h1>
                <p class="text-xs text-slate-500">256-Bit SSL Encrypted Payment Gateway</p>
            </div>
            <div class="text-emerald-700 text-xs font-bold">
                Instant Clearance
            </div>
        </div>

        {{-- Main Checkout Card --}}
        <div class="bg-white border border-slate-300 rounded-[4px] p-6 relative">
            
            {{-- Bill Summary Box --}}
            <div class="bg-slate-50 rounded-[4px] p-4 border border-slate-200 mb-5">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <span class="text-xs font-semibold text-slate-500">
                            {{ $invoice ? 'Tax Invoice' : 'Security Quotation' }}
                        </span>
                        <h2 class="text-base font-bold text-slate-900">
                            {{ $invoice ? $invoice->invoice_no : $quotation->quotation_no }}
                        </h2>
                    </div>
                    <span id="badge-plan-status" class="text-xs font-bold text-blue-600">
                        {{ $invoice ? ucfirst($invoice->status) : 'Advance Payment' }}
                    </span>
                </div>

                <div class="text-xs text-slate-600 mb-3">
                    Customer: <strong class="text-slate-900">{{ $lead->customer_name }}</strong> · {{ $lead->phone }}
                </div>

                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-200 text-xs">
                    <div>
                        <span class="text-slate-500">Total Project Value:</span>
                        <div class="font-bold text-slate-900">₹{{ number_format((float)$totalAmount, 2) }}</div>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-500" id="summary-payable-label">Selected Amount:</span>
                        <div class="text-lg font-bold text-emerald-700" id="summary-payable-val">₹{{ number_format((float)$payableAmount, 2) }}</div>
                    </div>
                </div>
            </div>

            {{-- Flexible Payment Plan Options (Advance vs Full Total) --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    1. Choose Payment Option
                </label>
                <div class="grid grid-cols-2 gap-3">
                    {{-- Advance Deposit --}}
                    <button type="button" id="plan-btn-advance" onclick="selectPaymentPlan('advance')" 
                            class="flex flex-col text-left p-3 rounded-[4px] border-2 border-blue-600 bg-blue-50/30 transition-colors cursor-pointer">
                        <div class="flex items-center justify-between w-full mb-1">
                            <span class="text-xs font-bold text-blue-800">Pay Advance (50%)</span>
                            <span class="text-xs font-bold text-blue-600">Active</span>
                        </div>
                        <div class="text-base font-bold text-slate-900">₹{{ number_format((float)$advanceAmount, 2) }}</div>
                        <span class="text-xs text-slate-500 mt-0.5">Booking deposit before work starts</span>
                    </button>

                    {{-- Full Payment --}}
                    <button type="button" id="plan-btn-full" onclick="selectPaymentPlan('full')" 
                            class="flex flex-col text-left p-3 rounded-[4px] border border-slate-300 bg-white hover:border-slate-400 transition-colors cursor-pointer">
                        <div class="flex items-center justify-between w-full mb-1">
                            <span class="text-xs font-bold text-slate-700">Pay Full Total (100%)</span>
                            <span id="plan-radio-full" class="text-xs text-slate-400 font-normal">Select</span>
                        </div>
                        <div class="text-base font-bold text-slate-900">₹{{ number_format((float)$fullAmount, 2) }}</div>
                        <span class="text-xs text-slate-500 mt-0.5">Complete project payment</span>
                    </button>
                </div>
            </div>

            {{-- Custom Amount Input --}}
            <div class="mb-5 bg-slate-50 p-3 rounded-[4px] border border-slate-200">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-sm font-medium text-slate-700">
                        Amount to Pay (₹)
                    </label>
                    <span class="text-xs text-slate-500">Max: ₹{{ number_format((float)$fullAmount, 2) }}</span>
                </div>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-sm font-bold text-slate-500">₹</span>
                    <input type="number" step="0.01" min="1" max="{{ $fullAmount }}" id="pay-amount-input" value="{{ $payableAmount }}"
                           class="w-full pl-7 pr-20 py-2 bg-white border border-slate-300 rounded-[4px] text-sm font-bold text-slate-900 focus:outline-none focus:border-blue-600">
                    <button type="button" onclick="selectPaymentPlan('full')" class="absolute right-1.5 top-1 px-2.5 py-1 rounded-[4px] bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-medium border border-slate-300">
                        Set Full
                    </button>
                </div>
            </div>

            {{-- 2. Choose Payment Method Tabs --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    2. Select Payment Method
                </label>
                <div class="grid grid-cols-3 gap-1 p-1 bg-slate-100 rounded-[4px] border border-slate-200">
                    <button type="button" onclick="switchPaymentTab('razorpay', this)" class="tab-btn active py-2 px-2 text-xs font-medium rounded-[4px] transition-colors text-center">
                        Online (Cards/UPI)
                    </button>
                    <button type="button" onclick="switchPaymentTab('upi', this)" class="tab-btn py-2 px-2 text-xs font-medium text-slate-600 hover:text-slate-900 rounded-[4px] transition-colors text-center">
                        Direct UPI QR Code
                    </button>
                    <button type="button" onclick="switchPaymentTab('cash', this)" class="tab-btn py-2 px-2 text-xs font-medium text-slate-600 hover:text-slate-900 rounded-[4px] transition-colors text-center">
                        Pay by Cash
                    </button>
                </div>
            </div>

            {{-- Tab 1: Razorpay Gateway Checkout --}}
            <div id="tab-razorpay" class="payment-tab-content pt-1">
                <div class="bg-blue-50 border border-blue-200 rounded-[4px] p-3 mb-4 text-xs text-blue-900">
                    <strong class="font-bold">Instant Payment Confirmation:</strong>
                    Google Pay, PhonePe, Paytm, all Credit/Debit Cards, NetBanking &amp; Wallets.
                </div>

                <div class="flex justify-end">
                    <button type="button" id="razorpay-pay-btn" onclick="initiateRazorpayPayment()"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-[4px] flex items-center justify-center gap-2 transition-colors cursor-pointer">
                        <span id="btn-spinner" class="hidden">...</span>
                        <span id="razorpay-btn-label">Pay ₹{{ number_format((float)$payableAmount, 2) }} Online Now</span>
                    </button>
                </div>

                <div class="mt-4 flex items-center justify-center gap-3 text-slate-500 text-xs">
                    <span>256-Bit SSL</span>
                    <span>•</span>
                    <span>RBI Compliant</span>
                    <span>•</span>
                    <span>Instant Receipt</span>
                </div>
            </div>

            {{-- Tab 2: Dynamic UPI QR Code & Intent Apps --}}
            <div id="tab-upi" class="payment-tab-content hidden pt-1">
                <div class="text-center bg-slate-50 rounded-[4px] p-4 border border-slate-200 mb-4">
                    <p class="text-xs text-slate-600 mb-2">Scan with Google Pay, PhonePe, Paytm, or BHIM</p>
                    
                    <div class="inline-block p-2 bg-white rounded-[4px] border border-slate-300 mb-2.5">
                        <img id="upi-qr-image" src="{{ $upiPayload['qr_image_url'] }}" alt="UPI Payment QR Code" class="w-44 h-44 mx-auto">
                    </div>

                    <div class="text-xs font-mono text-slate-700 bg-white py-1 px-3 rounded-[4px] inline-flex items-center gap-2 border border-slate-300 mb-3">
                        <span id="vpa-display">{{ $upiPayload['vpa'] }}</span>
                        <button type="button" onclick="copyVpa()" class="text-blue-600 hover:underline font-bold">Copy</button>
                    </div>

                    {{-- Deep Link for Mobile Devices --}}
                    <div class="pt-3 border-t border-slate-200 flex flex-wrap justify-center gap-2">
                        <a id="upi-intent-link" href="{{ $upiPayload['upi_url'] }}" class="px-4 py-2 rounded-[4px] bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold inline-flex items-center gap-1.5">
                            Open UPI App Directly
                        </a>
                    </div>
                </div>

                {{-- Submit UTR / Reference after manual UPI Scan --}}
                <div class="bg-slate-50 rounded-[4px] p-3 border border-slate-200">
                    <h4 class="text-xs font-bold text-slate-700 mb-1.5">Paid via UPI Scanner? Submit UTR for instant verification:</h4>
                    <div class="flex gap-2">
                        <input type="text" id="manual-utr-input" placeholder="e.g. 12-digit UTR: 421987654321" class="flex-1 px-3 py-1.5 bg-white border border-slate-300 rounded-[4px] text-xs text-slate-900 focus:outline-none focus:border-blue-600">
                        <button type="button" onclick="submitManualUtr()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-[4px]">
                            Verify
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tab 3: Pay by Cash on Site / Installation Handover --}}
            <div id="tab-cash" class="payment-tab-content hidden pt-1">
                <div class="bg-slate-50 border border-slate-200 rounded-[4px] p-4 mb-4 text-xs text-slate-700">
                    <div class="text-slate-900 font-bold text-sm mb-1.5">
                        Cash Payment on Site / Handover
                    </div>
                    <p class="leading-relaxed text-slate-600 mb-3">
                        No online payment required right now. You can pay in cash directly to our certified technician upon arrival at your site or after the CCTV system setup is complete.
                    </p>
                    <div class="bg-white p-3 rounded-[4px] border border-slate-200 space-y-1 text-slate-700 text-xs">
                        <div class="flex justify-between"><span class="text-slate-500">Site Location:</span><span class="font-bold text-slate-900 text-right">{{ $lead->site_address ?: 'Customer Address' }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Cash Amount:</span><strong class="text-slate-900" id="cash-amount-display">₹{{ number_format((float)$payableAmount, 2) }}</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Receipt:</span><span class="text-slate-900">Official printed &amp; digital receipt given on site</span></div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="button" id="cash-confirm-btn" onclick="submitCashPaymentChoice()"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-[4px] flex items-center justify-center gap-2 transition-colors cursor-pointer">
                        <span id="cash-btn-spinner" class="hidden">...</span>
                        <span id="cash-btn-label">Confirm Booking with Cash on Installation</span>
                    </button>
                </div>
            </div>

            {{-- Success Card (Hidden by default) --}}
            <div id="payment-success-card" class="hidden absolute inset-0 bg-white p-8 flex flex-col items-center justify-center text-center z-20 rounded-[4px]">
                <div class="text-emerald-600 font-bold text-2xl mb-2">
                    Payment Successful
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1" id="success-heading">Payment Confirmed</h3>
                <p class="text-xs text-slate-500 mb-4" id="success-receipt-info">Receipt generated and dispatched via WhatsApp and Email.</p>

                <div class="bg-slate-50 border border-slate-200 rounded-[4px] p-4 w-full max-w-sm mb-6 text-left text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-200"><span class="text-slate-500">Receipt / Ref No:</span><strong class="text-slate-900" id="success-receipt-no">-</strong></div>
                    <div class="flex justify-between py-1 border-b border-slate-200"><span class="text-slate-500">Amount:</span><strong class="text-slate-900 font-bold" id="success-amount">-</strong></div>
                    <div class="flex justify-between py-1 border-b border-slate-200"><span class="text-slate-500">Payment Mode:</span><strong class="text-slate-900 font-bold" id="success-method">-</strong></div>
                    <div class="flex justify-between py-1"><span class="text-slate-500">Status:</span><span class="text-emerald-700 font-bold" id="success-txn-id">Confirmed</span></div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full max-w-sm">
                    <a id="download-receipt-link" href="#" target="_blank" class="flex-1 py-2 px-4 rounded-[4px] bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs text-center">
                        View Receipt
                    </a>
                    <a href="{{ route('home') }}" class="py-2 px-4 rounded-[4px] bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 font-bold text-xs text-center">
                        Back to Store
                    </a>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="mt-4 text-center text-xs text-slate-500">
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
                btnAdv.className = "flex flex-col text-left p-3 rounded-[4px] border-2 border-blue-600 bg-blue-50/30 transition-colors cursor-pointer";
                btnFull.className = "flex flex-col text-left p-3 rounded-[4px] border border-slate-300 bg-white hover:border-slate-400 transition-colors cursor-pointer";
                radioFull.className = "text-xs text-slate-400 font-normal";
                radioFull.innerText = "Select";
            } else {
                document.getElementById('pay-amount-input').value = fullAmount;
                btnFull.className = "flex flex-col text-left p-3 rounded-[4px] border-2 border-blue-600 bg-blue-50/30 transition-colors cursor-pointer";
                btnAdv.className = "flex flex-col text-left p-3 rounded-[4px] border border-slate-300 bg-white hover:border-slate-400 transition-colors cursor-pointer";
                radioFull.className = "text-xs font-bold text-blue-600";
                radioFull.innerText = 'Active';
            }
            updatePayDisplays();
        }

        function switchPaymentTab(tab, btn) {
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active');
                b.classList.remove('bg-blue-600');
                b.classList.remove('text-white');
                b.classList.add('text-slate-600');
            });
            document.querySelectorAll('.payment-tab-content').forEach(c => c.classList.add('hidden'));
            btn.classList.add('active');
            btn.classList.add('bg-blue-600');
            btn.classList.add('text-white');
            btn.classList.remove('text-slate-600');
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
                        color: '#2563eb'
                    },
                    modal: {
                        ondismiss: function() {
                            btn.disabled = false;
                            spinner.classList.add('hidden');
                        }
                    }
                };

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
            document.getElementById('success-heading').innerText = isCash ? 'Booking Confirmed (Cash on Site)' : 'Payment Successful';
            document.getElementById('success-receipt-info').innerText = isCash 
                ? 'Your site installation has been scheduled. Cash will be collected by our technician on site.' 
                : 'Receipt generated and dispatched via WhatsApp and Email.';
            document.getElementById('success-receipt-no').innerText = data.payment.receipt_no;
            document.getElementById('success-amount').innerText = '₹' + parseFloat(data.payment.amount).toFixed(2);
            document.getElementById('success-method').innerText = isCash ? 'Cash on Site / Installation' : (data.payment.method === 'upi' ? 'Direct UPI Transfer' : 'Online Razorpay');
            document.getElementById('success-txn-id').innerText = data.payment.reference_no || data.payment.gateway_payment_id || (isCash ? 'Pending Site Collection' : 'Confirmed');
            document.getElementById('download-receipt-link').href = `/payments/${data.payment.id}/receipt`;
            document.getElementById('payment-success-card').classList.remove('hidden');
        }
    </script>
</body>
</html>
