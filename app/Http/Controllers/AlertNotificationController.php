<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Services\AlertNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class AlertNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $channel = $request->input('channel');
        $event = $request->input('event');
        $status = $request->input('status');
        $search = trim((string) $request->input('search', ''));

        $query = NotificationLog::with('creator')->latest();

        if ($channel) {
            $query->where('channel', $channel);
        }

        if ($event) {
            $query->where('event_type', $event);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%")
                  ->orWhere('recipient_email', 'like', "%{$search}%")
                  ->orWhere('message_body', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => NotificationLog::count(),
            'whatsapp' => NotificationLog::where('channel', 'whatsapp')->count(),
            'sms'      => NotificationLog::where('channel', 'sms')->count(),
            'email'    => NotificationLog::where('channel', 'email')->count(),
            'failed'   => NotificationLog::where('status', 'failed')->count(),
        ];

        $leads = Lead::orderBy('customer_name')->get();

        return view('alerts.index', compact('logs', 'stats', 'leads'));
    }

    public function templates(): View
    {
        // Ensure default templates are seeded
        foreach (NotificationTemplate::defaultTemplates() as $key => $data) {
            NotificationTemplate::getTemplate($key);
        }

        $templates = NotificationTemplate::orderBy('category')->orderBy('title')->get();

        return view('alerts.templates', compact('templates'));
    }

    public function updateTemplate(Request $request, NotificationTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'whatsapp_template'   => ['required', 'string', 'max:2500'],
            'sms_template'        => ['nullable', 'string', 'max:500'],
            'email_subject'       => ['required', 'string', 'max:255'],
            'email_body'          => ['required', 'string', 'max:5000'],
            'is_whatsapp_enabled' => ['nullable', 'boolean'],
            'is_sms_enabled'      => ['nullable', 'boolean'],
            'is_email_enabled'    => ['nullable', 'boolean'],
        ]);

        $template->update([
            'whatsapp_template'   => $validated['whatsapp_template'],
            'sms_template'        => $validated['sms_template'] ?? null,
            'email_subject'       => $validated['email_subject'],
            'email_body'          => $validated['email_body'],
            'is_whatsapp_enabled' => (bool) ($validated['is_whatsapp_enabled'] ?? false),
            'is_sms_enabled'      => (bool) ($validated['is_sms_enabled'] ?? false),
            'is_email_enabled'    => (bool) ($validated['is_email_enabled'] ?? false),
        ]);

        return back()->with('status', "✅ Template '{$template->title}' updated successfully.");
    }

    public function sendManualBroadcast(Request $request, AlertNotificationService $alertService): RedirectResponse
    {
        $validated = $request->validate([
            'channel'        => ['required', 'in:whatsapp,sms,email'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'phone'          => ['nullable', 'required_if:channel,whatsapp,sms', 'string', 'max:25'],
            'email'          => ['nullable', 'required_if:channel,email', 'email', 'max:150'],
            'subject'        => ['nullable', 'string', 'max:255'],
            'message'        => ['required', 'string', 'max:2000'],
        ]);

        $log = $alertService->sendManualBroadcast(
            $validated['channel'],
            $validated['recipient_name'],
            $validated['phone'] ?? null,
            $validated['email'] ?? null,
            $validated['message'],
            $validated['subject'] ?? null
        );

        if ($validated['channel'] === 'whatsapp' && $log->action_url) {
            return redirect()->away($log->action_url);
        }

        return redirect()->route('alerts.index')
            ->with('status', "✅ Alert dispatched via {$validated['channel']} to {$validated['recipient_name']}.");
    }

    public function runAutomatedSweep(): RedirectResponse
    {
        Artisan::call('alerts:send-reminders');

        return redirect()->route('alerts.index')
            ->with('status', "⚡ Automated CRM reminder sweep executed successfully! Check the audit log below.");
    }

    public function gateways(): View
    {
        $settings = \App\Models\GatewaySetting::getSettings();
        return view('alerts.gateways', compact('settings'));
    }

    public function updateGateways(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'active_sms_gateway'           => ['required', 'in:log,twilio,msg91,custom_webhook'],
            'twilio_account_sid'           => ['nullable', 'string', 'max:255'],
            'twilio_auth_token'            => ['nullable', 'string', 'max:255'],
            'twilio_from_number'           => ['nullable', 'string', 'max:50'],
            'msg91_auth_key'               => ['nullable', 'string', 'max:255'],
            'msg91_sender_id'              => ['nullable', 'string', 'max:50'],
            'msg91_flow_id'                => ['nullable', 'string', 'max:100'],
            'msg91_dlt_te_id'              => ['nullable', 'string', 'max:100'],
            'webhook_url'                  => ['nullable', 'url', 'max:500'],
            'webhook_secret'               => ['nullable', 'string', 'max:255'],
            'webhook_method'               => ['required', 'in:POST,GET,PUT'],
            'webhook_headers'              => ['nullable', 'string'],
            'webhook_payload_template'     => ['nullable', 'string'],
            'ticket_auto_sms_enabled'       => ['nullable', 'boolean'],
            'ticket_auto_whatsapp_enabled'  => ['nullable', 'boolean'],
            'otp_sms_enabled'               => ['nullable', 'boolean'],
            'otp_whatsapp_enabled'          => ['nullable', 'boolean'],
            'otp_expiry_minutes'            => ['required', 'integer', 'min:1', 'max:60'],
            'enable_online_payments'        => ['nullable', 'boolean'],
            'razorpay_key_id'               => ['nullable', 'string', 'max:255'],
            'razorpay_key_secret'           => ['nullable', 'string', 'max:255'],
            'razorpay_webhook_secret'       => ['nullable', 'string', 'max:255'],
            'upi_vpa_id'                    => ['nullable', 'string', 'max:255'],
            'upi_merchant_name'             => ['nullable', 'string', 'max:255'],
            'auto_receipt_whatsapp_enabled' => ['nullable', 'boolean'],
            'auto_receipt_email_enabled'    => ['nullable', 'boolean'],
            'smtp_mailer'                   => ['nullable', 'string', 'in:smtp,log,sendmail'],
            'smtp_host'                     => ['nullable', 'string', 'max:255'],
            'smtp_port'                     => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username'                 => ['nullable', 'string', 'max:255'],
            'smtp_password'                 => ['nullable', 'string', 'max:255'],
            'smtp_encryption'               => ['nullable', 'string', 'max:50'],
            'smtp_from_address'             => ['nullable', 'string', 'email', 'max:255'],
            'smtp_from_name'                => ['nullable', 'string', 'max:255'],
        ]);

        $settings = \App\Models\GatewaySetting::getSettings();
        $settings->update([
            'active_sms_gateway'            => $validated['active_sms_gateway'],
            'twilio_account_sid'            => $validated['twilio_account_sid'] ?? null,
            'twilio_auth_token'             => $validated['twilio_auth_token'] ?? null,
            'twilio_from_number'            => $validated['twilio_from_number'] ?? null,
            'msg91_auth_key'                => $validated['msg91_auth_key'] ?? null,
            'msg91_sender_id'               => $validated['msg91_sender_id'] ?? null,
            'msg91_flow_id'                 => $validated['msg91_flow_id'] ?? null,
            'msg91_dlt_te_id'               => $validated['msg91_dlt_te_id'] ?? null,
            'webhook_url'                   => $validated['webhook_url'] ?? null,
            'webhook_secret'                => $validated['webhook_secret'] ?? null,
            'webhook_method'                => $validated['webhook_method'],
            'webhook_headers'               => $validated['webhook_headers'] ?? null,
            'webhook_payload_template'      => $validated['webhook_payload_template'] ?? null,
            'ticket_auto_sms_enabled'       => (bool) ($validated['ticket_auto_sms_enabled'] ?? false),
            'ticket_auto_whatsapp_enabled'  => (bool) ($validated['ticket_auto_whatsapp_enabled'] ?? false),
            'otp_sms_enabled'               => (bool) ($validated['otp_sms_enabled'] ?? false),
            'otp_whatsapp_enabled'          => (bool) ($validated['otp_whatsapp_enabled'] ?? false),
            'otp_expiry_minutes'            => (int) $validated['otp_expiry_minutes'],
            'enable_online_payments'        => (bool) ($validated['enable_online_payments'] ?? false),
            'razorpay_key_id'               => $validated['razorpay_key_id'] ?? null,
            'razorpay_key_secret'           => $validated['razorpay_key_secret'] ?? null,
            'razorpay_webhook_secret'       => $validated['razorpay_webhook_secret'] ?? null,
            'upi_vpa_id'                    => $validated['upi_vpa_id'] ?? null,
            'upi_merchant_name'             => $validated['upi_merchant_name'] ?? null,
            'auto_receipt_whatsapp_enabled' => (bool) ($validated['auto_receipt_whatsapp_enabled'] ?? false),
            'auto_receipt_email_enabled'    => (bool) ($validated['auto_receipt_email_enabled'] ?? false),
            'smtp_mailer'                   => $validated['smtp_mailer'] ?? 'smtp',
            'smtp_host'                     => $validated['smtp_host'] ?? null,
            'smtp_port'                     => $validated['smtp_port'] ?? 587,
            'smtp_username'                 => $validated['smtp_username'] ?? null,
            'smtp_password'                 => $validated['smtp_password'] ?? null,
            'smtp_encryption'               => $validated['smtp_encryption'] ?? 'tls',
            'smtp_from_address'             => $validated['smtp_from_address'] ?? null,
            'smtp_from_name'                => $validated['smtp_from_name'] ?? null,
        ]);

        return back()->with('status', "✅ Gateway, SMTP Mail & Online Payment settings updated successfully!");
    }

    public function testSmsGateway(Request $request, \App\Services\SmsGatewayService $smsGateway): RedirectResponse
    {
        $validated = $request->validate([
            'test_phone'   => ['required', 'string', 'max:25'],
            'test_message' => ['required', 'string', 'max:500'],
        ]);

        $result = $smsGateway->sendSms($validated['test_phone'], $validated['test_message'], ['event' => 'gateway_test']);

        if ($result['success']) {
            return back()->with('status', "🎉 Test SMS dispatched successfully via gateway [{$result['gateway']}]! Ref ID: {$result['reference_id']}");
        }

        return back()->with('error', "❌ Gateway dispatch failed [{$result['gateway']}]: " . ($result['error'] ?? 'Unknown error'));
    }

    public function testOtp(Request $request, \App\Services\OtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'otp_phone' => ['required', 'string', 'max:25'],
            'otp_name'  => ['nullable', 'string', 'max:100'],
        ]);

        $result = $otpService->generateAndSendOtp(
            $validated['otp_phone'],
            $validated['otp_name'] ?: 'Test User'
        );

        $msg = "🔐 Test OTP [{$result['otp']}] generated! (Valid until {$result['expires_at']->format('H:i:s')}).";
        if ($result['sms_sent']) {
            $msg .= " Dispatched via SMS ({$result['gateway']}).";
        } elseif ($result['error']) {
            $msg .= " SMS warning: {$result['error']}";
        }

        return back()->with('status', $msg)->with('whatsapp_test_url', $result['whatsapp_url']);
    }

    /**
     * Test and verify real-time email delivery via configured SMTP gateway.
     */
    public function testEmailGateway(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        $targetEmail = $validated['test_email'];
        \App\Services\MailConfigService::applyDynamicMailConfig();

        $settings = \App\Models\GatewaySetting::getSettings();
        $fromName = $settings->smtp_from_name ?: config('mail.from.name', 'CCTV Security Solutions');
        $fromAddress = $settings->smtp_from_address ?: config('mail.from.address', 'support@cctvcrm.com');
        $host = $settings->smtp_host ?: config('mail.mailers.smtp.host', '127.0.0.1');

        try {
            \Illuminate\Support\Facades\Mail::to($targetEmail)->send(
                new \App\Mail\SmtpVerificationTestMail($targetEmail, $fromName, $fromAddress, $host)
            );

            return redirect()->route('alerts.gateways')->with('status', "🎉 Live verification email successfully delivered to [{$targetEmail}] via SMTP host ({$host})!");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("SMTP test failed: " . $e->getMessage());
            return redirect()->route('alerts.gateways')->with('error', "❌ SMTP Email verification failed: " . $e->getMessage() . ". (Tip for Outlook/Office365: Use smtp.office365.com, port 587, TLS, and an App Password if 2FA is enabled).");
        }
    }
}
