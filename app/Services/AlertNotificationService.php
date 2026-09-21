<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlertNotificationService
{
    protected SmsGatewayService $smsGateway;

    public function __construct(SmsGatewayService $smsGateway)
    {
        $this->smsGateway = $smsGateway;
    }

    /**
     * Dispatch an automated alert across requested channels (WhatsApp, SMS, Email).
     */
    public function sendAlert(
        string $eventKey,
        $recipient,
        array $variables = [],
        ?Model $reference = null,
        array $channels = ['whatsapp', 'sms', 'email']
    ): array {
        $template = NotificationTemplate::getTemplate($eventKey);

        $recipientName = is_object($recipient) ? ($recipient->customer_name ?? $recipient->name ?? 'Customer') : ($variables['customer_name'] ?? 'Customer');
        $recipientPhone = is_object($recipient) ? ($recipient->phone ?? null) : ($variables['recipient_phone'] ?? null);
        $recipientEmail = is_object($recipient) ? ($recipient->email ?? null) : ($variables['recipient_email'] ?? null);
        $recipientType = is_object($recipient) && method_exists($recipient, 'isTechnician') && $recipient->isTechnician() ? 'technician' : 'customer';

        $logs = [];

        // 1. WhatsApp Channel
        if (in_array('whatsapp', $channels) && $template->is_whatsapp_enabled && $recipientPhone) {
            $message = $this->renderTemplate($template->whatsapp_template, $variables);
            $actionUrl = $this->generateWhatsAppUrl($recipientPhone, $message);

            $logs[] = NotificationLog::create([
                'channel'         => 'whatsapp',
                'event_type'      => $eventKey,
                'recipient_type'  => $recipientType,
                'recipient_name'  => $recipientName,
                'recipient_phone' => $recipientPhone,
                'recipient_email' => $recipientEmail,
                'subject'         => "WhatsApp: " . $template->title,
                'message_body'    => $message,
                'action_url'      => $actionUrl,
                'status'          => 'sent',
                'reference_type'  => $reference ? get_class($reference) : null,
                'reference_id'    => $reference?->id,
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);
        }

        // 2. SMS Channel (Dispatched via configured active SMS Gateway)
        if (in_array('sms', $channels) && $template->is_sms_enabled && $recipientPhone && $template->sms_template) {
            $message = $this->renderTemplate($template->sms_template, $variables);
            $smsResult = $this->smsGateway->sendSms($recipientPhone, $message, [
                'event'        => $eventKey,
                'reference_id' => $reference?->id,
            ]);

            $logs[] = NotificationLog::create([
                'channel'         => 'sms',
                'event_type'      => $eventKey,
                'recipient_type'  => $recipientType,
                'recipient_name'  => $recipientName,
                'recipient_phone' => $recipientPhone,
                'recipient_email' => $recipientEmail,
                'subject'         => "SMS: " . $template->title,
                'message_body'    => $message,
                'action_url'      => null,
                'status'          => $smsResult['success'] ? 'sent' : 'failed',
                'error_message'   => $smsResult['error'] ?? null,
                'reference_type'  => $reference ? get_class($reference) : null,
                'reference_id'    => $reference?->id,
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);
        }

        // 3. Email Channel
        if (in_array('email', $channels) && $template->is_email_enabled && $recipientEmail) {
            $subject = $this->renderTemplate($template->email_subject, $variables);
            $body = $this->renderTemplate($template->email_body, $variables);

            try {
                \App\Services\MailConfigService::applyDynamicMailConfig();

                Mail::html($body, function ($msg) use ($recipientEmail, $recipientName, $subject) {
                    $msg->to($recipientEmail, $recipientName)->subject($subject);
                });
                $emailStatus = 'sent';
                $errorMessage = null;
            } catch (\Throwable $e) {
                Log::warning("Email alert dispatch failed: " . $e->getMessage());
                $emailStatus = 'failed';
                $errorMessage = $e->getMessage();
            }

            $logs[] = NotificationLog::create([
                'channel'         => 'email',
                'event_type'      => $eventKey,
                'recipient_type'  => $recipientType,
                'recipient_name'  => $recipientName,
                'recipient_phone' => $recipientPhone,
                'recipient_email' => $recipientEmail,
                'subject'         => $subject,
                'message_body'    => strip_tags(str_replace('<br>', "\n", $body)),
                'action_url'      => $variables['link'] ?? null,
                'status'          => $emailStatus,
                'error_message'   => $errorMessage,
                'reference_type'  => $reference ? get_class($reference) : null,
                'reference_id'    => $reference?->id,
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);
        }

        return $logs;
    }

    /**
     * Send ad-hoc direct broadcast message.
     */
    public function sendManualBroadcast(
        string $channel,
        string $recipientName,
        ?string $phone,
        ?string $email,
        string $message,
        ?string $subject = null
    ): NotificationLog {
        $actionUrl = null;
        $status = 'sent';
        $errorMessage = null;

        if ($channel === 'whatsapp' && $phone) {
            $actionUrl = $this->generateWhatsAppUrl($phone, $message);
        } elseif ($channel === 'sms' && $phone) {
            $smsResult = $this->smsGateway->sendSms($phone, $message, ['event' => 'custom_broadcast']);
            $status = $smsResult['success'] ? 'sent' : 'failed';
            $errorMessage = $smsResult['error'] ?? null;
        } elseif ($channel === 'email' && $email) {
            try {
                Mail::html(nl2br(e($message)), function ($msg) use ($email, $recipientName, $subject) {
                    $msg->to($email, $recipientName)->subject($subject ?: 'Important Alert from CCTV CRM');
                });
                $status = 'sent';
                $errorMessage = null;
            } catch (\Throwable $e) {
                $status = 'failed';
                $errorMessage = $e->getMessage();
            }
        }

        return NotificationLog::create([
            'channel'         => $channel,
            'event_type'      => 'custom_broadcast',
            'recipient_type'  => 'customer',
            'recipient_name'  => $recipientName,
            'recipient_phone' => $phone,
            'recipient_email' => $email,
            'subject'         => $subject ?: ucfirst($channel) . " Broadcast",
            'message_body'    => $message,
            'action_url'      => $actionUrl,
            'status'          => $status,
            'error_message'   => $errorMessage,
            'sent_at'         => now(),
            'created_by'      => Auth::id(),
        ]);
    }

    /**
     * Substitute {variable_name} tags with actual data values.
     */
    public function renderTemplate(string $template, array $variables): string
    {
        $rendered = $template;
        foreach ($variables as $key => $value) {
            $rendered = str_replace('{' . $key . '}', (string) $value, $rendered);
        }
        return $rendered;
    }

    /**
     * Build WhatsApp Web & App Click-to-Chat URL.
     */
    public function generateWhatsAppUrl(string $phone, string $message): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        return 'https://api.whatsapp.com/send?phone=' . $cleanPhone . '&text=' . urlencode($message);
    }
}
