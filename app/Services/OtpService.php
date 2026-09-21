<?php

namespace App\Services;

use App\Models\GatewaySetting;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class OtpService
{
    protected SmsGatewayService $smsGateway;
    protected AlertNotificationService $alertService;

    public function __construct(SmsGatewayService $smsGateway, AlertNotificationService $alertService)
    {
        $this->smsGateway = $smsGateway;
        $this->alertService = $alertService;
    }

    /**
     * Generate and send an instant OTP via SMS and/or WhatsApp.
     *
     * @return array ['otp' => string, 'expires_at' => \Carbon\Carbon, 'sms_sent' => bool, 'whatsapp_url' => string, 'gateway' => string, 'error' => ?string]
     */
    public function generateAndSendOtp(string $phone, string $recipientName = 'Customer', ?string $email = null): array
    {
        $settings = GatewaySetting::getSettings();
        $expiryMinutes = $settings->otp_expiry_minutes ?: 10;
        $cleanPhone = $this->smsGateway->formatPhoneNumber($phone);

        // Generate 6-digit numeric OTP
        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes($expiryMinutes);

        // Cache OTP with expiry
        Cache::put("otp_{$cleanPhone}", [
            'otp'        => $otp,
            'expires_at' => $expiresAt,
            'name'       => $recipientName,
        ], $expiresAt);

        $template = NotificationTemplate::getTemplate('otp_verification');
        $variables = [
            'customer_name'  => $recipientName,
            'otp_code'       => $otp,
            'expiry_minutes' => $expiryMinutes,
        ];

        $smsMessage = $this->alertService->renderTemplate($template->sms_template, $variables);
        $whatsappMessage = $this->alertService->renderTemplate($template->whatsapp_template, $variables);

        $smsResult = ['success' => false, 'gateway' => 'none', 'error' => null];

        // 1. Send via SMS if enabled
        if ($settings->otp_sms_enabled) {
            $smsResult = $this->smsGateway->sendSms($cleanPhone, $smsMessage, ['otp' => $otp, 'event' => 'otp_verification']);

            NotificationLog::create([
                'channel'         => 'sms',
                'event_type'      => 'otp_verification',
                'recipient_type'  => 'customer',
                'recipient_name'  => $recipientName,
                'recipient_phone' => $cleanPhone,
                'recipient_email' => $email,
                'subject'         => 'SMS: ' . $template->title,
                'message_body'    => $smsMessage,
                'action_url'      => null,
                'status'          => $smsResult['success'] ? 'sent' : 'failed',
                'error_message'   => $smsResult['error'] ?? null,
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);
        }

        // 2. Generate WhatsApp URL
        $whatsappUrl = $this->alertService->generateWhatsAppUrl($cleanPhone, $whatsappMessage);

        if ($settings->otp_whatsapp_enabled) {
            NotificationLog::create([
                'channel'         => 'whatsapp',
                'event_type'      => 'otp_verification',
                'recipient_type'  => 'customer',
                'recipient_name'  => $recipientName,
                'recipient_phone' => $cleanPhone,
                'recipient_email' => $email,
                'subject'         => 'WhatsApp: ' . $template->title,
                'message_body'    => $whatsappMessage,
                'action_url'      => $whatsappUrl,
                'status'          => 'sent',
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);
        }

        return [
            'otp'          => $otp,
            'expires_at'   => $expiresAt,
            'sms_sent'     => $smsResult['success'],
            'whatsapp_url' => $whatsappUrl,
            'gateway'      => $smsResult['gateway'] ?? 'none',
            'error'        => $smsResult['error'] ?? null,
        ];
    }

    /**
     * Verify a submitted OTP code for a given phone number.
     */
    public function verifyOtp(string $phone, string $code): bool
    {
        $cleanPhone = $this->smsGateway->formatPhoneNumber($phone);
        $cached = Cache::get("otp_{$cleanPhone}");

        if (!$cached || !is_array($cached)) {
            return false;
        }

        if (trim($cached['otp']) === trim($code)) {
            Cache::forget("otp_{$cleanPhone}");
            return true;
        }

        return false;
    }
}
