<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black tracking-wider text-white uppercase font-['Outfit'] flex items-center gap-2">
                    <span class="text-amber-400">🔌</span> SMS & Webhook Gateway Integration
                </h2>
                <p class="mt-1 text-xs text-slate-400 font-mono">Connect Twilio, MSG91, or custom Webhook gateways for instant SMS, OTPs & ticket status alerts</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('alerts.templates') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700 text-xs font-bold transition">
                    ⚙️ Templates
                </a>
                <a href="{{ route('alerts.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700/60 bg-slate-800/40 text-slate-400 hover:text-white text-xs font-semibold transition">
                    &larr; Notification Logs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#060913] min-h-screen text-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Alerts --}}
            @if (session('status'))
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-2">
                        <span>✓ {{ session('status') }}</span>
                    </div>
                    @if(session('whatsapp_test_url'))
                        <a href="{{ session('whatsapp_test_url') }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold transition">
                            💬 Open Test WhatsApp Chat &rarr;
                        </a>
                    @endif
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm font-semibold flex items-center gap-2 shadow-xl">
                    <span>✕ {{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs space-y-1">
                    <span class="font-bold uppercase font-mono">Please correct the following:</span>
                    <ul class="list-disc pl-5 font-mono">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left 2 Cols: Main Configuration Form --}}
                <div class="lg:col-span-2 space-y-6">
                    <form method="POST" action="{{ route('alerts.gateways.update') }}">
                        @csrf
                        @method('PUT')

                        {{-- Card 1: Active Gateway Selector --}}
                        <div class="bg-[#0F172A] rounded-2xl p-6 border border-white/10 shadow-xl space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-white/5">
                                <div>
                                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">1. Active SMS & Alert Gateway</h3>
                                    <p class="text-xs text-slate-400 font-mono mt-0.5">Choose which service provider dispatches SMS and real-time triggers.</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold uppercase {{ $settings->active_sms_gateway === 'log' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' }}">
                                    Active: {{ strtoupper(str_replace('_', ' ', $settings->active_sms_gateway)) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                {{-- Option: Log --}}
                                <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition {{ $settings->active_sms_gateway === 'log' ? 'border-amber-400 bg-amber-500/10' : 'border-slate-700 bg-[#060913] hover:border-slate-500' }}">
                                    <input type="radio" name="active_sms_gateway" value="log" {{ $settings->active_sms_gateway === 'log' ? 'checked' : '' }} class="sr-only">
                                    <span class="text-xs font-bold text-white">📝 Log Simulator</span>
                                    <span class="text-[10px] text-slate-400 font-mono mt-1">Local file testing</span>
                                </label>

                                {{-- Option: Twilio --}}
                                <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition {{ $settings->active_sms_gateway === 'twilio' ? 'border-amber-400 bg-amber-500/10' : 'border-slate-700 bg-[#060913] hover:border-slate-500' }}">
                                    <input type="radio" name="active_sms_gateway" value="twilio" {{ $settings->active_sms_gateway === 'twilio' ? 'checked' : '' }} class="sr-only">
                                    <span class="text-xs font-bold text-white">🌐 Twilio API</span>
                                    <span class="text-[10px] text-slate-400 font-mono mt-1">Global SMS & OTP</span>
                                </label>

                                {{-- Option: MSG91 --}}
                                <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition {{ $settings->active_sms_gateway === 'msg91' ? 'border-amber-400 bg-amber-500/10' : 'border-slate-700 bg-[#060913] hover:border-slate-500' }}">
                                    <input type="radio" name="active_sms_gateway" value="msg91" {{ $settings->active_sms_gateway === 'msg91' ? 'checked' : '' }} class="sr-only">
                                    <span class="text-xs font-bold text-white">🇮🇳 MSG91 Flow</span>
                                    <span class="text-[10px] text-slate-400 font-mono mt-1">India DLT Compliant</span>
                                </label>

                                {{-- Option: Custom Webhook --}}
                                <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition {{ $settings->active_sms_gateway === 'custom_webhook' ? 'border-amber-400 bg-amber-500/10' : 'border-slate-700 bg-[#060913] hover:border-slate-500' }}">
                                    <input type="radio" name="active_sms_gateway" value="custom_webhook" {{ $settings->active_sms_gateway === 'custom_webhook' ? 'checked' : '' }} class="sr-only">
                                    <span class="text-xs font-bold text-white">⚡ Webhook</span>
                                    <span class="text-[10px] text-slate-400 font-mono mt-1">Fast2SMS / Zapier</span>
                                </label>
                            </div>
                        </div>

                        {{-- Card 2: Twilio Credentials --}}
                        <div class="bg-[#0F172A] rounded-2xl p-6 border border-white/10 shadow-xl space-y-4">
                            <div class="flex items-center gap-2 pb-3 border-b border-white/5">
                                <span class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center font-bold text-xs border border-rose-500/20">TW</span>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">2. Twilio Gateway Credentials</h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Account SID</label>
                                    <input type="text" name="twilio_account_sid" value="{{ old('twilio_account_sid', $settings->twilio_account_sid) }}" placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                           class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Auth Token</label>
                                    <input type="password" name="twilio_auth_token" value="{{ old('twilio_auth_token', $settings->twilio_auth_token) }}" placeholder="••••••••••••••••••••••••"
                                           class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">From Phone Number (E.164 format)</label>
                                    <input type="text" name="twilio_from_number" value="{{ old('twilio_from_number', $settings->twilio_from_number) }}" placeholder="+1234567890"
                                           class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                </div>
                            </div>
                        </div>

                        {{-- Card 3: MSG91 Credentials --}}
                        <div class="bg-[#0F172A] rounded-2xl p-6 border border-white/10 shadow-xl space-y-4">
                            <div class="flex items-center gap-2 pb-3 border-b border-white/5">
                                <span class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/20">M9</span>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">3. MSG91 Gateway Credentials</h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Auth Key</label>
                                    <input type="password" name="msg91_auth_key" value="{{ old('msg91_auth_key', $settings->msg91_auth_key) }}" placeholder="MSG91 Auth Key"
                                           class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Sender ID (6 Chars DLT)</label>
                                    <input type="text" name="msg91_sender_id" value="{{ old('msg91_sender_id', $settings->msg91_sender_id) }}" placeholder="e.g. CCTVCR" maxlength="6"
                                           class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Flow ID (Template Flow ID)</label>
                                    <input type="text" name="msg91_flow_id" value="{{ old('msg91_flow_id', $settings->msg91_flow_id) }}" placeholder="e.g. 648392a8..."
                                           class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">DLT Template / TE ID (Optional)</label>
                                    <input type="text" name="msg91_dlt_te_id" value="{{ old('msg91_dlt_te_id', $settings->msg91_dlt_te_id) }}" placeholder="e.g. 1201160..."
                                           class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                </div>
                            </div>
                        </div>

                        {{-- Card 4: Custom Webhook / HTTP REST API --}}
                        <div class="bg-[#0F172A] rounded-2xl p-6 border border-white/10 shadow-xl space-y-4">
                            <div class="flex items-center gap-2 pb-3 border-b border-white/5">
                                <span class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/20">WH</span>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">4. Custom Webhook / Generic HTTP Gateway</h3>
                            </div>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                    <div class="sm:col-span-3">
                                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Webhook Endpoint URL</label>
                                        <input type="url" name="webhook_url" value="{{ old('webhook_url', $settings->webhook_url) }}" placeholder="https://api.example.com/sms/send"
                                               class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">HTTP Method</label>
                                        <select name="webhook_method" class="w-full text-xs rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-2.5">
                                            <option value="POST" {{ $settings->webhook_method === 'POST' ? 'selected' : '' }}>POST</option>
                                            <option value="GET" {{ $settings->webhook_method === 'GET' ? 'selected' : '' }}>GET</option>
                                            <option value="PUT" {{ $settings->webhook_method === 'PUT' ? 'selected' : '' }}>PUT</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Webhook Secret / HMAC Key</label>
                                        <input type="text" name="webhook_secret" value="{{ old('webhook_secret', $settings->webhook_secret) }}" placeholder="Optional secret for X-Signature header"
                                               class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Custom HTTP Headers (JSON)</label>
                                        <input type="text" name="webhook_headers" value="{{ old('webhook_headers', $settings->webhook_headers) }}" placeholder='{"Authorization": "Bearer key..."}'
                                               class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Custom JSON Payload Template (Optional)</label>
                                    <textarea name="webhook_payload_template" rows="2" placeholder='{"recipient": "{phone}", "text": "{message}", "code": "{otp}"}'
                                              class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">{{ old('webhook_payload_template', $settings->webhook_payload_template) }}</textarea>
                                    <span class="text-[10px] text-slate-400 font-mono">Supported placeholders: <code class="text-amber-400">{phone}</code>, <code class="text-amber-400">{message}</code>, <code class="text-amber-400">{otp}</code></span>
                                </div>
                            </div>
                        </div>

                        {{-- Card 5: Automation Triggers & OTP Settings --}}
                        <div class="bg-[#0F172A] rounded-2xl p-6 border border-white/10 shadow-xl space-y-4">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit'] pb-3 border-b border-white/5">5. Automation Triggers & Instant OTP Rules</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-700 bg-[#060913] cursor-pointer">
                                    <input type="checkbox" name="ticket_auto_sms_enabled" value="1" {{ $settings->ticket_auto_sms_enabled ? 'checked' : '' }}
                                           class="mt-1 rounded border-slate-700 text-amber-500 focus:ring-0">
                                    <div>
                                        <span class="text-xs font-bold text-white">Auto-SMS on Ticket Events</span>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">Send SMS when ticket is created, assigned, or resolved.</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-700 bg-[#060913] cursor-pointer">
                                    <input type="checkbox" name="ticket_auto_whatsapp_enabled" value="1" {{ $settings->ticket_auto_whatsapp_enabled ? 'checked' : '' }}
                                           class="mt-1 rounded border-slate-700 text-amber-500 focus:ring-0">
                                    <div>
                                        <span class="text-xs font-bold text-white">Auto-WhatsApp on Ticket Events</span>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">Generate WhatsApp alert links on ticket status changes.</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-700 bg-[#060913] cursor-pointer">
                                    <input type="checkbox" name="otp_sms_enabled" value="1" {{ $settings->otp_sms_enabled ? 'checked' : '' }}
                                           class="mt-1 rounded border-slate-700 text-amber-500 focus:ring-0">
                                    <div>
                                        <span class="text-xs font-bold text-white">Instant OTP via SMS</span>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">Dispatch 6-digit verification codes via SMS.</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-700 bg-[#060913] cursor-pointer">
                                    <input type="checkbox" name="otp_whatsapp_enabled" value="1" {{ $settings->otp_whatsapp_enabled ? 'checked' : '' }}
                                           class="mt-1 rounded border-slate-700 text-amber-500 focus:ring-0">
                                    <div>
                                        <span class="text-xs font-bold text-white">Instant OTP via WhatsApp</span>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">Support direct WhatsApp verification links.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Card 6: Online Payment Gateway & Universal UPI Settings --}}
                        <div class="bg-[#0F172A] rounded-2xl p-6 border border-white/10 shadow-xl space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-white/5">
                                <div>
                                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">6. Online Payment Gateway (Razorpay & UPI)</h3>
                                    <p class="text-xs text-slate-400 font-mono mt-0.5">Enable credit/debit card, netbanking, and dynamic UPI QR links on Invoices & Quotations.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="enable_online_payments" value="1" {{ $settings->enable_online_payments ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-slate-950 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-700 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                    <span class="ml-2 text-xs font-mono font-bold text-slate-300">Live Checkout</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Razorpay Key ID</label>
                                    <input type="text" name="razorpay_key_id" value="{{ old('razorpay_key_id', $settings->razorpay_key_id) }}" placeholder="rzp_live_... / rzp_test_..."
                                           class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Razorpay Key Secret</label>
                                    <input type="password" name="razorpay_key_secret" value="{{ old('razorpay_key_secret', $settings->razorpay_key_secret) }}" placeholder="••••••••••••••••"
                                           class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Razorpay Webhook Secret (Optional)</label>
                                    <input type="text" name="razorpay_webhook_secret" value="{{ old('razorpay_webhook_secret', $settings->razorpay_webhook_secret) }}" placeholder="Secret for /webhook/razorpay"
                                           class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">UPI VPA ID (Virtual Address)</label>
                                    <input type="text" name="upi_vpa_id" value="{{ old('upi_vpa_id', $settings->upi_vpa_id) }}" placeholder="cctvbusiness@okhdfcbank"
                                           class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Merchant / Payee Name (Shown in UPI Apps)</label>
                                    <input type="text" name="upi_merchant_name" value="{{ old('upi_merchant_name', $settings->upi_merchant_name) }}" placeholder="Precision IT Security Solutions"
                                           class="w-full text-xs rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-2.5">
                                </div>
                            </div>

                            <div class="pt-3 border-t border-white/5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-700 bg-[#060913] cursor-pointer">
                                    <input type="checkbox" name="auto_receipt_whatsapp_enabled" value="1" {{ $settings->auto_receipt_whatsapp_enabled ? 'checked' : '' }}
                                           class="mt-1 rounded border-slate-700 text-amber-500 focus:ring-0">
                                    <div>
                                        <span class="text-xs font-bold text-white">Auto-WhatsApp Payment Receipts</span>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">Generate receipt confirmation with PDF link immediately after customer payment.</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-700 bg-[#060913] cursor-pointer">
                                    <input type="checkbox" name="auto_receipt_email_enabled" value="1" {{ $settings->auto_receipt_email_enabled ? 'checked' : '' }}
                                           class="mt-1 rounded border-slate-700 text-amber-500 focus:ring-0">
                                    <div>
                                        <span class="text-xs font-bold text-white">Auto-Email Payment Receipts</span>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">Deliver official tax receipt PDF directly to customer billing email.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Card 7: SMTP Mail Gateway --}}
                        <div class="bg-[#0F172A] rounded-2xl p-6 border border-white/10 shadow-xl space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-white/5">
                                <div>
                                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">7. SMTP & Microsoft Outlook / Gmail Mail Gateway</h3>
                                    <p class="text-xs text-slate-400 font-mono mt-0.5">Configure real-time outgoing mail delivery for Quotation PDFs and Payment Receipts.</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold uppercase {{ !empty($settings->smtp_host) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                                    {{ !empty($settings->smtp_host) ? 'SMTP Configured' : 'Using Local / .env' }}
                                </span>
                            </div>

                            {{-- 1-Click Fast Presets --}}
                            <div class="bg-[#060913] p-4 rounded-xl border border-white/10 space-y-2">
                                <span class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-wider block">1-Click Fast Presets:</span>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" onclick="applyMailPreset('outlook')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/30 text-sky-400 text-xs font-bold transition">
                                        📧 Microsoft Outlook 365 (smtp.office365.com)
                                    </button>
                                    <button type="button" onclick="applyMailPreset('gmail')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-xs font-bold transition">
                                        🌐 Gmail / Workspace (smtp.gmail.com)
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Mailer Driver</label>
                                    <select name="smtp_mailer" id="smtp_mailer"
                                            class="w-full text-xs rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-2.5">
                                        <option value="smtp" {{ ($settings->smtp_mailer ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP (Recommended)</option>
                                        <option value="log" {{ ($settings->smtp_mailer ?? '') === 'log' ? 'selected' : '' }}>Log (Local file testing)</option>
                                        <option value="sendmail" {{ ($settings->smtp_mailer ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">SMTP Host Server</label>
                                    <input type="text" name="smtp_host" id="smtp_host" value="{{ $settings->smtp_host }}"
                                           placeholder="smtp.office365.com"
                                           class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">SMTP Port</label>
                                    <input type="number" name="smtp_port" id="smtp_port" value="{{ $settings->smtp_port ?? 587 }}"
                                           placeholder="587"
                                           class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">SMTP Username</label>
                                    <input type="text" name="smtp_username" id="smtp_username" value="{{ $settings->smtp_username }}"
                                           placeholder="sales@yourdomain.com"
                                           class="w-full text-xs rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">SMTP Password</label>
                                    <input type="password" name="smtp_password" id="smtp_password" value="{{ $settings->smtp_password }}"
                                           placeholder="••••••••••••"
                                           class="w-full text-xs rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Encryption</label>
                                    <select name="smtp_encryption" id="smtp_encryption"
                                            class="w-full text-xs rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-2.5">
                                        <option value="tls" {{ ($settings->smtp_encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>STARTTLS (Port 587)</option>
                                        <option value="ssl" {{ ($settings->smtp_encryption ?? '') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                        <option value="null" {{ ($settings->smtp_encryption ?? '') === 'null' ? 'selected' : '' }}>None</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Sender Email Address (From)</label>
                                    <input type="email" name="smtp_from_address" id="smtp_from_address" value="{{ $settings->smtp_from_address }}"
                                           placeholder="quotations@yourcompany.com"
                                           class="w-full text-xs rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Sender Display Name (From Name)</label>
                                    <input type="text" name="smtp_from_name" id="smtp_from_name" value="{{ $settings->smtp_from_name }}"
                                           placeholder="CCTV Security Engineering"
                                           class="w-full text-xs rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-2.5">
                                </div>
                            </div>
                        </div>

                        {{-- Save Button --}}
                        <div class="mt-6 flex justify-end">
                            <button type="submit"
                                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20 transition">
                                💾 Save Gateway Settings
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Right Col: Live Testing Consoles --}}
                <div class="space-y-6">

                    {{-- Live SMTP Email Test --}}
                    <div class="bg-[#0F172A] rounded-2xl p-6 border border-white/10 shadow-xl space-y-4">
                        <div class="flex items-center gap-2 pb-3 border-b border-white/5">
                            <span class="text-lg">📧</span>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">Test Real-Time Email Delivery</h3>
                        </div>
                        <p class="text-xs text-slate-400 font-mono">Dispatch a live test email to verify your SMTP connection.</p>

                        <form method="POST" action="{{ route('alerts.gateways.test-email') }}" class="space-y-3.5">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-1.5">Target Test Email</label>
                                <input type="email" name="test_email" required placeholder="customer@example.com"
                                       class="w-full text-xs rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-2.5">
                            </div>

                            <button type="submit"
                                    class="w-full py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-emerald-500/20 transition">
                                ⚡ Send Verification Email
                            </button>
                        </form>
                    </div>

                    {{-- Live SMS / Webhook Test --}}
                    <div class="bg-[#0F172A] rounded-2xl p-6 border border-white/10 shadow-xl space-y-4">
                        <div class="flex items-center gap-2 pb-3 border-b border-white/5">
                            <span class="text-lg">🧪</span>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">Test Live SMS / Webhook</h3>
                        </div>
                        <p class="text-xs text-slate-400 font-mono">Trigger a live message test via <strong class="text-amber-400">{{ strtoupper($settings->active_sms_gateway) }}</strong>.</p>

                        <form method="POST" action="{{ route('alerts.gateways.test-sms') }}" class="space-y-3.5">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-1.5">Recipient Phone</label>
                                <input type="text" name="test_phone" required placeholder="9876543210"
                                       class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-1.5">Test Message Body</label>
                                <textarea name="test_message" rows="2" required
                                          class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">Test notification from CCTV CRM Gateway.</textarea>
                            </div>

                            <button type="submit"
                                    class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs uppercase tracking-wider transition border border-slate-700">
                                🚀 Send Test SMS
                            </button>
                        </form>
                    </div>

                    {{-- Instant OTP Generator --}}
                    <div class="bg-[#0F172A] rounded-2xl p-6 border border-white/10 shadow-xl space-y-4">
                        <div class="flex items-center gap-2 pb-3 border-b border-white/5">
                            <span class="text-lg">🔐</span>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">Instant OTP Test Engine</h3>
                        </div>
                        <p class="text-xs text-slate-400 font-mono">Generate a 6-digit OTP and send via active gateway.</p>

                        <form method="POST" action="{{ route('alerts.gateways.test-otp') }}" class="space-y-3.5">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-1.5">Phone Number</label>
                                <input type="text" name="otp_phone" required placeholder="9876543210"
                                       class="w-full text-sm font-mono min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-1.5">Recipient Name</label>
                                <input type="text" name="otp_name" placeholder="John Doe"
                                       class="w-full text-xs rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-2.5">
                            </div>

                            <button type="submit"
                                    class="w-full py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20 transition">
                                ⚡ Send Test OTP
                            </button>
                        </form>
                    </div>

                    <script>
                    function applyMailPreset(type) {
                        if (type === 'outlook') {
                            document.getElementById('smtp_mailer').value = 'smtp';
                            document.getElementById('smtp_host').value = 'smtp.office365.com';
                            document.getElementById('smtp_port').value = 587;
                            document.getElementById('smtp_encryption').value = 'tls';
                        } else if (type === 'gmail') {
                            document.getElementById('smtp_mailer').value = 'smtp';
                            document.getElementById('smtp_host').value = 'smtp.gmail.com';
                            document.getElementById('smtp_port').value = 587;
                            document.getElementById('smtp_encryption').value = 'tls';
                        }
                    }
                    </script>

                    {{-- Quick Reminders Trigger --}}
                    <div class="bg-gradient-to-br from-amber-500/10 via-[#0F172A] to-amber-500/5 border border-amber-500/30 text-white rounded-2xl p-6 shadow-xl space-y-3">
                        <h4 class="text-sm font-bold uppercase tracking-wider font-['Outfit'] text-amber-400">⏰ Automated Reminder Sweep</h4>
                        <p class="text-xs text-slate-300 font-mono">
                            Scans active quotations (3 days to expiry), AMC contracts (15 days to expiration), and scheduled maintenance visits.
                        </p>
                        <form method="POST" action="{{ route('alerts.run-sweep') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full py-2.5 rounded-xl text-white font-bold text-xs uppercase tracking-wider transition shadow-sm"
                                    style="background-color: var(--crm-accent, #2563eb);">
                                ⚡ Run Reminder Sweep Now
                            </button>
                        </form>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
