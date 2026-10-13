<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('alerts.index') }}" class="back-link">&larr; Notification Logs</a>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    SMS &amp; Webhook Gateway Integration
                </h2>
                <p class="text-xs text-gray-500 dark:text-slate-400">Connect Twilio, MSG91, or custom Webhook gateways for instant SMS, OTPs &amp; ticket status alerts</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('alerts.templates') }}" class="btn-secondary text-xs h-[38px]">
                    Templates
                </a>
                <a href="{{ route('alerts.index') }}" class="btn-secondary text-xs h-[38px]">
                    &larr; Logs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f5f5f5] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Alerts --}}
            @if (session('status'))
                <div class="p-4 rounded-[4px] bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-sm font-semibold flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span>✓ {{ session('status') }}</span>
                    </div>
                    @if(session('whatsapp_test_url'))
                        <a href="{{ session('whatsapp_test_url') }}" target="_blank"
                           class="btn-primary text-xs h-[32px] px-3">
                            Open Test WhatsApp Chat &rarr;
                        </a>
                    @endif
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-[4px] bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-500/30 text-rose-800 dark:text-rose-300 text-sm font-semibold flex items-center gap-2">
                    <span>✕ {{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-[4px] bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs space-y-1">
                    <span class="font-bold">Please correct the following:</span>
                    <ul class="list-disc pl-5">
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
                        <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-6 border border-[#ddd] dark:border-slate-800 space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-[#eee] dark:border-slate-800">
                                <div>
                                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">1. Active SMS &amp; Alert Gateway</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Choose which service provider dispatches SMS and real-time triggers.</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-[4px] text-xs font-semibold {{ $settings->active_sms_gateway === 'log' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200' }}">
                                    Active: {{ strtoupper(str_replace('_', ' ', $settings->active_sms_gateway)) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                {{-- Option: Log --}}
                                <label class="relative flex flex-col p-3 rounded-[4px] border cursor-pointer transition {{ $settings->active_sms_gateway === 'log' ? 'border-[#2563eb] bg-blue-50/50 dark:bg-blue-950/20' : 'border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-900 hover:border-slate-400' }}">
                                    <input type="radio" name="active_sms_gateway" value="log" {{ $settings->active_sms_gateway === 'log' ? 'checked' : '' }} class="sr-only">
                                    <span class="text-xs font-bold text-slate-900 dark:text-white">Log Simulator</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Local file testing</span>
                                </label>

                                {{-- Option: Twilio --}}
                                <label class="relative flex flex-col p-3 rounded-[4px] border cursor-pointer transition {{ $settings->active_sms_gateway === 'twilio' ? 'border-[#2563eb] bg-blue-50/50 dark:bg-blue-950/20' : 'border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-900 hover:border-slate-400' }}">
                                    <input type="radio" name="active_sms_gateway" value="twilio" {{ $settings->active_sms_gateway === 'twilio' ? 'checked' : '' }} class="sr-only">
                                    <span class="text-xs font-bold text-slate-900 dark:text-white">Twilio API</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Global SMS &amp; OTP</span>
                                </label>

                                {{-- Option: MSG91 --}}
                                <label class="relative flex flex-col p-3 rounded-[4px] border cursor-pointer transition {{ $settings->active_sms_gateway === 'msg91' ? 'border-[#2563eb] bg-blue-50/50 dark:bg-blue-950/20' : 'border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-900 hover:border-slate-400' }}">
                                    <input type="radio" name="active_sms_gateway" value="msg91" {{ $settings->active_sms_gateway === 'msg91' ? 'checked' : '' }} class="sr-only">
                                    <span class="text-xs font-bold text-slate-900 dark:text-white">MSG91 Flow</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">India DLT Compliant</span>
                                </label>

                                {{-- Option: Custom Webhook --}}
                                <label class="relative flex flex-col p-3 rounded-[4px] border cursor-pointer transition {{ $settings->active_sms_gateway === 'custom_webhook' ? 'border-[#2563eb] bg-blue-50/50 dark:bg-blue-950/20' : 'border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-900 hover:border-slate-400' }}">
                                    <input type="radio" name="active_sms_gateway" value="custom_webhook" {{ $settings->active_sms_gateway === 'custom_webhook' ? 'checked' : '' }} class="sr-only">
                                    <span class="text-xs font-bold text-slate-900 dark:text-white">Webhook</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Fast2SMS / Zapier</span>
                                </label>
                            </div>
                        </div>

                        {{-- Card 2: Twilio Credentials --}}
                        <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-6 border border-[#ddd] dark:border-slate-800 space-y-4">
                            <div class="flex items-center gap-2 pb-3 border-b border-[#eee] dark:border-slate-800">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">2. Twilio Gateway Credentials</h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="crm-label">Account SID</label>
                                    <input type="text" name="twilio_account_sid" value="{{ old('twilio_account_sid', $settings->twilio_account_sid) }}" placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                           class="crm-input font-mono">
                                </div>
                                <div>
                                    <label class="crm-label">Auth Token</label>
                                    <input type="password" name="twilio_auth_token" value="{{ old('twilio_auth_token', $settings->twilio_auth_token) }}" placeholder="••••••••••••••••••••••••"
                                           class="crm-input font-mono">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="crm-label">From Phone Number (E.164 format)</label>
                                    <input type="text" name="twilio_from_number" value="{{ old('twilio_from_number', $settings->twilio_from_number) }}" placeholder="+1234567890"
                                           class="crm-input font-mono">
                                </div>
                            </div>
                        </div>

                        {{-- Card 3: MSG91 Credentials --}}
                        <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-6 border border-[#ddd] dark:border-slate-800 space-y-4">
                            <div class="flex items-center gap-2 pb-3 border-b border-[#eee] dark:border-slate-800">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">3. MSG91 Gateway Credentials</h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="crm-label">Auth Key</label>
                                    <input type="password" name="msg91_auth_key" value="{{ old('msg91_auth_key', $settings->msg91_auth_key) }}" placeholder="MSG91 Auth Key"
                                           class="crm-input font-mono">
                                </div>
                                <div>
                                    <label class="crm-label">Sender ID (6 Chars DLT)</label>
                                    <input type="text" name="msg91_sender_id" value="{{ old('msg91_sender_id', $settings->msg91_sender_id) }}" placeholder="e.g. CCTVCR" maxlength="6"
                                           class="crm-input font-mono">
                                </div>
                                <div>
                                    <label class="crm-label">Flow ID (Template Flow ID)</label>
                                    <input type="text" name="msg91_flow_id" value="{{ old('msg91_flow_id', $settings->msg91_flow_id) }}" placeholder="e.g. 648392a8..."
                                           class="crm-input font-mono">
                                </div>
                                <div>
                                    <label class="crm-label">DLT Template / TE ID (Optional)</label>
                                    <input type="text" name="msg91_dlt_te_id" value="{{ old('msg91_dlt_te_id', $settings->msg91_dlt_te_id) }}" placeholder="e.g. 1201160..."
                                           class="crm-input font-mono">
                                </div>
                            </div>
                        </div>

                        {{-- Card 4: Custom Webhook / HTTP REST API --}}
                        <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-6 border border-[#ddd] dark:border-slate-800 space-y-4">
                            <div class="flex items-center gap-2 pb-3 border-b border-[#eee] dark:border-slate-800">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">4. Custom Webhook / Generic HTTP Gateway</h3>
                            </div>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                    <div class="sm:col-span-3">
                                        <label class="crm-label">Webhook Endpoint URL</label>
                                        <input type="url" name="webhook_url" value="{{ old('webhook_url', $settings->webhook_url) }}" placeholder="https://api.example.com/sms/send"
                                               class="crm-input font-mono">
                                    </div>
                                    <div>
                                        <label class="crm-label">HTTP Method</label>
                                        <select name="webhook_method" class="crm-select">
                                            <option value="POST" {{ $settings->webhook_method === 'POST' ? 'selected' : '' }}>POST</option>
                                            <option value="GET" {{ $settings->webhook_method === 'GET' ? 'selected' : '' }}>GET</option>
                                            <option value="PUT" {{ $settings->webhook_method === 'PUT' ? 'selected' : '' }}>PUT</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="crm-label">Webhook Secret / HMAC Key</label>
                                        <input type="text" name="webhook_secret" value="{{ old('webhook_secret', $settings->webhook_secret) }}" placeholder="Optional secret for X-Signature header"
                                               class="crm-input font-mono">
                                    </div>
                                    <div>
                                        <label class="crm-label">Custom HTTP Headers (JSON)</label>
                                        <input type="text" name="webhook_headers" value="{{ old('webhook_headers', $settings->webhook_headers) }}" placeholder='{"Authorization": "Bearer key..."}'
                                               class="crm-input font-mono">
                                    </div>
                                </div>

                                <div>
                                    <label class="crm-label">Custom JSON Payload Template (Optional)</label>
                                    <textarea name="webhook_payload_template" rows="2" placeholder='{"recipient": "{phone}", "text": "{message}", "code": "{otp}"}'
                                              class="crm-input font-mono" style="height: auto;">{{ old('webhook_payload_template', $settings->webhook_payload_template) }}</textarea>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">Supported placeholders: <code>{phone}</code>, <code>{message}</code>, <code>{otp}</code></span>
                                </div>
                            </div>
                        </div>

                        {{-- Card 5: Automation Triggers & OTP Settings --}}
                        <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-6 border border-[#ddd] dark:border-slate-800 space-y-4">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white pb-3 border-b border-[#eee] dark:border-slate-800">5. Automation Triggers &amp; Instant OTP Rules</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="flex items-start gap-3 p-3.5 rounded-[4px] border border-[#ddd] dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 cursor-pointer">
                                    <input type="checkbox" name="ticket_auto_sms_enabled" value="1" {{ $settings->ticket_auto_sms_enabled ? 'checked' : '' }}
                                           class="mt-0.5 rounded-[4px] border-[#ccc] text-[#2563eb]">
                                    <div>
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">Auto-SMS on Ticket Events</span>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Send SMS when ticket is created, assigned, or resolved.</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-3.5 rounded-[4px] border border-[#ddd] dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 cursor-pointer">
                                    <input type="checkbox" name="ticket_auto_whatsapp_enabled" value="1" {{ $settings->ticket_auto_whatsapp_enabled ? 'checked' : '' }}
                                           class="mt-0.5 rounded-[4px] border-[#ccc] text-[#2563eb]">
                                    <div>
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">Auto-WhatsApp on Ticket Events</span>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Generate WhatsApp alert links on ticket status changes.</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-3.5 rounded-[4px] border border-[#ddd] dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 cursor-pointer">
                                    <input type="checkbox" name="otp_sms_enabled" value="1" {{ $settings->otp_sms_enabled ? 'checked' : '' }}
                                           class="mt-0.5 rounded-[4px] border-[#ccc] text-[#2563eb]">
                                    <div>
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">Instant OTP via SMS</span>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Dispatch 6-digit verification codes via SMS.</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-3.5 rounded-[4px] border border-[#ddd] dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 cursor-pointer">
                                    <input type="checkbox" name="otp_whatsapp_enabled" value="1" {{ $settings->otp_whatsapp_enabled ? 'checked' : '' }}
                                           class="mt-0.5 rounded-[4px] border-[#ccc] text-[#2563eb]">
                                    <div>
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">Instant OTP via WhatsApp</span>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Support direct WhatsApp verification links.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Card 6: Online Payment Gateway & Universal UPI Settings --}}
                        <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-6 border border-[#ddd] dark:border-slate-800 space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-[#eee] dark:border-slate-800">
                                <div>
                                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">6. Online Payment Gateway (Razorpay &amp; UPI)</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Enable credit/debit card, netbanking, and dynamic UPI QR links on Invoices &amp; Quotations.</p>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="enable_online_payments" value="1" {{ $settings->enable_online_payments ? 'checked' : '' }} class="rounded-[4px] border-[#ccc] text-[#2563eb]">
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Live Checkout</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="crm-label">Razorpay Key ID</label>
                                    <input type="text" name="razorpay_key_id" value="{{ old('razorpay_key_id', $settings->razorpay_key_id) }}" placeholder="rzp_live_... / rzp_test_..."
                                           class="crm-input font-mono">
                                </div>
                                <div>
                                    <label class="crm-label">Razorpay Key Secret</label>
                                    <input type="password" name="razorpay_key_secret" value="{{ old('razorpay_key_secret', $settings->razorpay_key_secret) }}" placeholder="••••••••••••••••"
                                           class="crm-input font-mono">
                                </div>
                                <div>
                                    <label class="crm-label">Razorpay Webhook Secret (Optional)</label>
                                    <input type="text" name="razorpay_webhook_secret" value="{{ old('razorpay_webhook_secret', $settings->razorpay_webhook_secret) }}" placeholder="Secret for /webhook/razorpay"
                                           class="crm-input font-mono">
                                </div>
                                <div>
                                    <label class="crm-label">UPI VPA ID (Virtual Address)</label>
                                    <input type="text" name="upi_vpa_id" value="{{ old('upi_vpa_id', $settings->upi_vpa_id) }}" placeholder="cctvbusiness@okhdfcbank"
                                           class="crm-input font-mono">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="crm-label">Merchant / Payee Name (Shown in UPI Apps)</label>
                                    <input type="text" name="upi_merchant_name" value="{{ old('upi_merchant_name', $settings->upi_merchant_name) }}" placeholder="Precision IT Security Solutions"
                                           class="crm-input">
                                </div>
                            </div>

                            <div class="pt-3 border-t border-[#eee] dark:border-slate-800 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="flex items-start gap-3 p-3.5 rounded-[4px] border border-[#ddd] dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 cursor-pointer">
                                    <input type="checkbox" name="auto_receipt_whatsapp_enabled" value="1" {{ $settings->auto_receipt_whatsapp_enabled ? 'checked' : '' }}
                                           class="mt-0.5 rounded-[4px] border-[#ccc] text-[#2563eb]">
                                    <div>
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">Auto-WhatsApp Payment Receipts</span>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Generate receipt confirmation with PDF link immediately after customer payment.</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 p-3.5 rounded-[4px] border border-[#ddd] dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 cursor-pointer">
                                    <input type="checkbox" name="auto_receipt_email_enabled" value="1" {{ $settings->auto_receipt_email_enabled ? 'checked' : '' }}
                                           class="mt-0.5 rounded-[4px] border-[#ccc] text-[#2563eb]">
                                    <div>
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">Auto-Email Payment Receipts</span>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Deliver official tax receipt PDF directly to customer billing email.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Card 7: SMTP Mail Gateway --}}
                        <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-6 border border-[#ddd] dark:border-slate-800 space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-[#eee] dark:border-slate-800">
                                <div>
                                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">7. SMTP Email Configuration</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Send transaction receipts, dispatch schedules, and PDF quotations via official company email.</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="applyMailPreset('outlook')" class="btn-secondary text-[11px] h-[30px] px-2.5">Office 365</button>
                                    <button type="button" onclick="applyMailPreset('gmail')" class="btn-secondary text-[11px] h-[30px] px-2.5">Gmail</button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="crm-label">Mailer Driver</label>
                                    <select name="smtp_mailer" id="smtp_mailer" class="crm-select">
                                        <option value="smtp" {{ ($settings->smtp_mailer ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP (Live Delivery)</option>
                                        <option value="log" {{ ($settings->smtp_mailer ?? '') === 'log' ? 'selected' : '' }}>Log (Local file testing)</option>
                                        <option value="sendmail" {{ ($settings->smtp_mailer ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="crm-label">SMTP Host Server</label>
                                    <input type="text" name="smtp_host" id="smtp_host" value="{{ $settings->smtp_host }}"
                                           placeholder="smtp.office365.com"
                                           class="crm-input font-mono">
                                </div>
                                <div>
                                    <label class="crm-label">SMTP Port</label>
                                    <input type="number" name="smtp_port" id="smtp_port" value="{{ $settings->smtp_port ?? 587 }}"
                                           placeholder="587"
                                           class="crm-input font-mono">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="crm-label">SMTP Username</label>
                                    <input type="text" name="smtp_username" id="smtp_username" value="{{ $settings->smtp_username }}"
                                           placeholder="sales@yourdomain.com"
                                           class="crm-input font-mono">
                                </div>
                                <div>
                                    <label class="crm-label">SMTP Password</label>
                                    <input type="password" name="smtp_password" id="smtp_password" value="{{ $settings->smtp_password }}"
                                           placeholder="••••••••••••"
                                           class="crm-input font-mono">
                                </div>
                                <div>
                                    <label class="crm-label">Encryption</label>
                                    <select name="smtp_encryption" id="smtp_encryption" class="crm-select">
                                        <option value="tls" {{ ($settings->smtp_encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>STARTTLS (Port 587)</option>
                                        <option value="ssl" {{ ($settings->smtp_encryption ?? '') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                        <option value="null" {{ ($settings->smtp_encryption ?? '') === 'null' ? 'selected' : '' }}>None</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="crm-label">Sender Email Address (From)</label>
                                    <input type="email" name="smtp_from_address" id="smtp_from_address" value="{{ $settings->smtp_from_address }}"
                                           placeholder="quotations@yourcompany.com"
                                           class="crm-input font-mono">
                                </div>
                                <div>
                                    <label class="crm-label">Sender Display Name (From Name)</label>
                                    <input type="text" name="smtp_from_name" id="smtp_from_name" value="{{ $settings->smtp_from_name }}"
                                           placeholder="CCTV Security Engineering"
                                           class="crm-input">
                                </div>
                            </div>
                        </div>

                        {{-- Save Button --}}
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn-primary h-[38px] px-6 text-xs font-semibold">
                                Save Gateway Settings
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Right Col: Live Testing Consoles --}}
                <div class="space-y-6">

                    {{-- Live SMTP Email Test --}}
                    <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-6 border border-[#ddd] dark:border-slate-800 space-y-4">
                        <div class="pb-3 border-b border-[#eee] dark:border-slate-800">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Test Real-Time Email Delivery</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Dispatch a live test email to verify your SMTP connection.</p>
                        </div>

                        <form method="POST" action="{{ route('alerts.gateways.test-email') }}" class="space-y-3.5">
                            @csrf
                            <div>
                                <label class="crm-label">Target Test Email</label>
                                <input type="email" name="test_email" required placeholder="customer@example.com"
                                       class="crm-input">
                            </div>

                            <button type="submit" class="btn-secondary w-full h-[38px] text-xs font-semibold">
                                Send Verification Email
                            </button>
                        </form>
                    </div>

                    {{-- Live SMS / Webhook Test --}}
                    <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-6 border border-[#ddd] dark:border-slate-800 space-y-4">
                        <div class="pb-3 border-b border-[#eee] dark:border-slate-800">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Test Live SMS / Webhook</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Trigger a live message test via <strong>{{ strtoupper($settings->active_sms_gateway) }}</strong>.</p>
                        </div>

                        <form method="POST" action="{{ route('alerts.gateways.test-sms') }}" class="space-y-3.5">
                            @csrf
                            <div>
                                <label class="crm-label">Recipient Phone</label>
                                <input type="text" name="test_phone" required placeholder="9876543210"
                                       class="crm-input font-mono">
                            </div>

                            <div>
                                <label class="crm-label">Test Message Body</label>
                                <textarea name="test_message" rows="2" required class="crm-input" style="height: auto;">Test notification from CCTV CRM Gateway.</textarea>
                            </div>

                            <button type="submit" class="btn-secondary w-full h-[38px] text-xs font-semibold">
                                Send Test SMS
                            </button>
                        </form>
                    </div>

                    {{-- Instant OTP Generator --}}
                    <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-6 border border-[#ddd] dark:border-slate-800 space-y-4">
                        <div class="pb-3 border-b border-[#eee] dark:border-slate-800">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Instant OTP Test Engine</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Generate a 6-digit OTP and send via active gateway.</p>
                        </div>

                        <form method="POST" action="{{ route('alerts.gateways.test-otp') }}" class="space-y-3.5">
                            @csrf
                            <div>
                                <label class="crm-label">Phone Number</label>
                                <input type="text" name="otp_phone" required placeholder="9876543210"
                                       class="crm-input font-mono">
                            </div>

                            <div>
                                <label class="crm-label">Recipient Name</label>
                                <input type="text" name="otp_name" placeholder="John Doe"
                                       class="crm-input">
                            </div>

                            <button type="submit" class="btn-secondary w-full h-[38px] text-xs font-semibold">
                                Send Test OTP
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
                    <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-6 border border-[#ddd] dark:border-slate-800 space-y-3">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Automated Reminder Sweep</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400">
                            Scans active quotations (3 days to expiry), AMC contracts (15 days to expiration), and scheduled maintenance visits.
                        </p>
                        <form method="POST" action="{{ route('alerts.run-sweep') }}">
                            @csrf
                            <button type="submit" class="btn-primary w-full h-[38px] text-xs font-semibold">
                                Run Reminder Sweep Now
                            </button>
                        </form>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
