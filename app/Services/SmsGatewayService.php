<?php

namespace App\Services;

use App\Models\GatewaySetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsGatewayService
{
    /**
     * Dispatch an SMS message using the active configured gateway driver.
     *
     * @return array ['success' => bool, 'gateway' => string, 'reference_id' => ?string, 'error' => ?string]
     */
    public function sendSms(string $phone, string $message, array $extraParams = []): array
    {
        $settings = GatewaySetting::getSettings();
        $gateway = $settings->active_sms_gateway ?: 'log';

        $formattedPhone = $this->formatPhoneNumber($phone);

        try {
            switch ($gateway) {
                case 'twilio':
                    return $this->sendViaTwilio($settings, $formattedPhone, $message);

                case 'msg91':
                    return $this->sendViaMsg91($settings, $formattedPhone, $message, $extraParams);

                case 'custom_webhook':
                    return $this->sendViaWebhook($settings, $formattedPhone, $message, $extraParams);

                case 'log':
                default:
                    return $this->sendViaLog($formattedPhone, $message);
            }
        } catch (\Throwable $e) {
            Log::error("SMS Gateway Dispatch Failed [{$gateway}]: " . $e->getMessage());
            return [
                'success'      => false,
                'gateway'      => $gateway,
                'reference_id' => null,
                'error'        => $e->getMessage(),
            ];
        }
    }

    /**
     * Dispatch SMS via Twilio API.
     */
    protected function sendViaTwilio(GatewaySetting $settings, string $phone, string $message): array
    {
        $sid = $settings->twilio_account_sid ?: config('services.twilio.sid');
        $token = $settings->twilio_auth_token ?: config('services.twilio.token');
        $from = $settings->twilio_from_number ?: config('services.twilio.from');

        if (!$sid || !$token || !$from) {
            return [
                'success'      => false,
                'gateway'      => 'twilio',
                'reference_id' => null,
                'error'        => 'Twilio credentials (Account SID, Auth Token, From Number) are not configured.',
            ];
        }

        $e164Phone = str_starts_with($phone, '+') ? $phone : '+' . $phone;

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To'   => $e164Phone,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success'      => true,
                'gateway'      => 'twilio',
                'reference_id' => $data['sid'] ?? null,
                'error'        => null,
            ];
        }

        $errorMessage = $response->json('message') ?: "Twilio API error HTTP {$response->status()}";
        return [
            'success'      => false,
            'gateway'      => 'twilio',
            'reference_id' => null,
            'error'        => $errorMessage,
        ];
    }

    /**
     * Dispatch SMS via MSG91 API.
     */
    protected function sendViaMsg91(GatewaySetting $settings, string $phone, string $message, array $extraParams = []): array
    {
        $authKey = $settings->msg91_auth_key ?: config('services.msg91.auth_key');
        $senderId = $settings->msg91_sender_id ?: config('services.msg91.sender_id');
        $flowId = $settings->msg91_flow_id ?: config('services.msg91.flow_id');

        if (!$authKey) {
            return [
                'success'      => false,
                'gateway'      => 'msg91',
                'reference_id' => null,
                'error'        => 'MSG91 Auth Key is not configured.',
            ];
        }

        $cleanPhone = ltrim($phone, '+');

        if ($flowId) {
            // MSG91 Flow API
            $payload = [
                'template_id' => $flowId,
                'short_url'   => '0',
                'recipients'  => [
                    [
                        'mobiles' => $cleanPhone,
                        'message' => $message,
                        'otp'     => $extraParams['otp'] ?? '',
                    ],
                ],
            ];

            if ($senderId) {
                $payload['sender'] = $senderId;
            }

            $response = Http::withHeaders([
                'authkey'      => $authKey,
                'content-type' => 'application/json',
            ])->post('https://control.msg91.com/api/v5/flow/', $payload);
        } else {
            // MSG91 Standard Send SMS API
            $response = Http::withHeaders([
                'authkey' => $authKey,
            ])->post('https://api.msg91.com/api/v2/sendsms', [
                'sender' => $senderId ?: 'CCTVCR',
                'route'  => '4',
                'country'=> '91',
                'sms'    => [
                    [
                        'message' => $message,
                        'to'      => [$cleanPhone],
                    ],
                ],
            ]);
        }

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success'      => true,
                'gateway'      => 'msg91',
                'reference_id' => $data['request_id'] ?? $data['message'] ?? 'sent',
                'error'        => null,
            ];
        }

        return [
            'success'      => false,
            'gateway'      => 'msg91',
            'reference_id' => null,
            'error'        => "MSG91 error HTTP {$response->status()}: " . $response->body(),
        ];
    }

    /**
     * Dispatch SMS / Ticket event via Custom Webhook / Generic HTTP REST Endpoint.
     */
    protected function sendViaWebhook(GatewaySetting $settings, string $phone, string $message, array $extraParams = []): array
    {
        $url = $settings->webhook_url;
        if (!$url) {
            return [
                'success'      => false,
                'gateway'      => 'custom_webhook',
                'reference_id' => null,
                'error'        => 'Custom Webhook URL is not configured.',
            ];
        }

        $method = strtoupper($settings->webhook_method ?: 'POST');
        $headers = [];

        if ($settings->webhook_headers) {
            $decodedHeaders = json_decode($settings->webhook_headers, true);
            if (is_array($decodedHeaders)) {
                $headers = $decodedHeaders;
            }
        }

        if ($settings->webhook_secret) {
            $headers['X-Signature'] = hash_hmac('sha256', $message, $settings->webhook_secret);
        }

        $payload = [
            'recipient_phone' => $phone,
            'message'         => $message,
            'event'           => $extraParams['event'] ?? 'sms_alert',
            'timestamp'       => now()->toIso8601String(),
            'metadata'        => $extraParams,
        ];

        // If custom payload template provided, substitute tags
        if ($settings->webhook_payload_template) {
            $renderedTemplate = $settings->webhook_payload_template;
            $renderedTemplate = str_replace('{phone}', $phone, $renderedTemplate);
            $renderedTemplate = str_replace('{message}', addslashes($message), $renderedTemplate);
            $renderedTemplate = str_replace('{otp}', $extraParams['otp'] ?? '', $renderedTemplate);
            $decoded = json_decode($renderedTemplate, true);
            if ($decoded) {
                $payload = $decoded;
            }
        }

        $httpRequest = Http::withHeaders($headers);

        if ($method === 'GET') {
            $response = $httpRequest->get($url, ['phone' => $phone, 'message' => $message]);
        } else {
            $response = $httpRequest->post($url, $payload);
        }

        if ($response->successful()) {
            return [
                'success'      => true,
                'gateway'      => 'custom_webhook',
                'reference_id' => 'wh-' . substr(md5($response->body()), 0, 10),
                'error'        => null,
            ];
        }

        return [
            'success'      => false,
            'gateway'      => 'custom_webhook',
            'reference_id' => null,
            'error'        => "Webhook returned HTTP {$response->status()}: " . substr($response->body(), 0, 200),
        ];
    }

    /**
     * Dispatch SMS to Laravel Log (Development & Staging Simulation).
     */
    protected function sendViaLog(string $phone, string $message): array
    {
        Log::info("📨 [SMS Gateway: LOG SIMULATOR] Recipient: {$phone} | Message: {$message}");
        return [
            'success'      => true,
            'gateway'      => 'log',
            'reference_id' => 'sim-' . uniqid(),
            'error'        => null,
        ];
    }

    /**
     * Format phone number to clean digits with country code default (91).
     */
    public function formatPhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($clean) === 10) {
            $clean = '91' . $clean;
        }

        return $clean;
    }
}
