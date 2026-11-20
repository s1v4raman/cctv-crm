<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class WhatsAppCloudService
{
    /**
     * Check if WhatsApp Cloud API credentials are configured.
     */
    public static function isConfigured(): bool
    {
        return !empty(self::getPhoneNumberId()) &&
               !empty(self::getAccessToken());
    }

    public static function getPhoneNumberId(): ?string
    {
        return config('services.whatsapp.phone_number_id') ?: env('WHATSAPP_PHONE_NUMBER_ID');
    }

    public static function getAccessToken(): ?string
    {
        return config('services.whatsapp.access_token') ?: env('WHATSAPP_ACCESS_TOKEN');
    }

    public static function getApiVersion(): string
    {
        return config('services.whatsapp.api_version') ?: env('WHATSAPP_API_VERSION', 'v20.0');
    }

    /**
     * Normalize phone number to standard international format without '+' (e.g. 919876543210).
     */
    public static function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '0') && strlen($clean) === 11) {
            $clean = substr($clean, 1);
        }

        // Default 10-digit Indian phone numbers to 91 country code
        if (strlen($clean) === 10) {
            $clean = '91' . $clean;
        }

        return $clean;
    }

    /**
     * Send Quotation PDF Document directly into customer WhatsApp chat via WhatsApp Cloud API.
     *
     * Inside the 24-hour customer service window (e.g. after customer messaged "Hi"):
     * Meta charges ₹0 for service conversations (first 1,000 conversations/month are free).
     *
     * @return array{success: bool, error: ?string, message_id: ?string, requires_setup: bool}
     */
    public static function sendQuotationDocument(
        Quotation $quotation,
        string $phone,
        ?string $caption = null,
        string $format = '1',
        ?string $pricingMode = null
    ): array {
        $cleanPhone = self::normalizePhone($phone);

        if (strlen($cleanPhone) < 10) {
            return [
                'success'        => false,
                'error'          => 'Invalid phone number. Please enter a 10-digit mobile number.',
                'message_id'     => null,
                'requires_setup' => false,
            ];
        }

        if (!self::isConfigured()) {
            return [
                'success'        => false,
                'error'          => 'WhatsApp Cloud API is not yet configured. Please set WHATSAPP_PHONE_NUMBER_ID and WHATSAPP_ACCESS_TOKEN in .env, or use Direct App Share.',
                'message_id'     => null,
                'requires_setup' => true,
            ];
        }

        $phoneNumberId = self::getPhoneNumberId();
        $accessToken   = self::getAccessToken();
        $apiVersion    = self::getApiVersion();

        $pricingMode = $pricingMode ?: ($quotation->pricing_mode ?? 'itemized');

        $pdfUrl = URL::signedRoute('quotations.public-pdf', [
            'quotation'    => $quotation,
            'format'       => $format,
            'pricing_mode' => $pricingMode,
        ]);

        $customerName = $quotation->lead?->customer_name ?? 'Valued Customer';
        $filename = "Quotation-{$quotation->quotation_no}.pdf";

        $captionText = $caption ?: "Dear {$customerName}, please find your official proposal Quotation #{$quotation->quotation_no} attached.\nTotal: ₹" . number_format((float) $quotation->total, 2);

        try {
            // 1. Generate PDF in-memory to upload directly to Meta Media API (bypasses localhost URL barrier)
            $pdfContent = null;
            try {
                $quotation->loadMissing(['lead', 'items']);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('quotations.pdf.format' . $format, compact('quotation'));
                $pdfContent = $pdf->output();
            } catch (\Throwable $pdfErr) {
                Log::warning("WhatsAppCloudService: PDF render fallback: " . $pdfErr->getMessage());
            }

            $mediaId = null;
            if ($pdfContent) {
                $mediaRes = Http::withToken($accessToken)
                    ->timeout(30)
                    ->attach('file', $pdfContent, $filename, ['Content-Type' => 'application/pdf'])
                    ->post("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/media", [
                        'messaging_product' => 'whatsapp',
                        'type'              => 'application/pdf',
                    ]);

                if ($mediaRes->successful()) {
                    $mediaId = $mediaRes->json('id');
                } else {
                    Log::warning("WhatsAppCloudService: Meta media upload error: " . $mediaRes->body());
                }
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'to'                => $cleanPhone,
                'type'              => 'document',
            ];

            if ($mediaId) {
                $payload['document'] = [
                    'id'       => $mediaId,
                    'filename' => $filename,
                    'caption'  => $captionText,
                ];
            } else {
                $payload['document'] = [
                    'link'     => $pdfUrl,
                    'filename' => $filename,
                    'caption'  => $captionText,
                ];
            }

            $response = Http::withToken($accessToken)
                ->timeout(20)
                ->post("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages", $payload);

            if ($response->successful()) {
                $body = $response->json();
                $messageId = $body['messages'][0]['id'] ?? null;

                // 1. Update quotation status to sent
                if (in_array($quotation->status, ['draft', 'approved'])) {
                    $prevStatus = $quotation->status;
                    $quotation->update([
                        'status'  => 'sent',
                        'sent_at' => now(),
                    ]);

                    $quotation->statusHistories()->create([
                        'changed_by'  => Auth::id(),
                        'from_status' => $prevStatus,
                        'to_status'   => 'sent',
                        'notes'       => "Quotation PDF document sent directly to customer WhatsApp ({$cleanPhone}) via Meta Cloud API. Rate: ₹0 (Inside 24h window).",
                    ]);
                }

                // 2. Log in NotificationLog
                NotificationLog::create([
                    'channel'         => 'whatsapp',
                    'event_type'      => 'quotation_sent',
                    'recipient_type'  => 'customer',
                    'recipient_name'  => $customerName,
                    'recipient_phone' => $cleanPhone,
                    'recipient_email' => $quotation->lead?->email,
                    'subject'         => "Quotation #{$quotation->quotation_no} (Format {$format})",
                    'message_body'    => $captionText,
                    'action_url'      => $pdfUrl,
                    'status'          => 'sent',
                    'reference_type'  => get_class($quotation),
                    'reference_id'    => $quotation->id,
                    'sent_at'         => now(),
                    'created_by'      => Auth::id(),
                ]);

                return [
                    'success'        => true,
                    'error'          => null,
                    'message_id'     => $messageId,
                    'requires_setup' => false,
                ];
            }

            $errorMsg = $response->json()['error']['message'] ?? $response->body();
            Log::error("WhatsApp Cloud API Document Dispatch Error: " . $errorMsg);

            return [
                'success'        => false,
                'error'          => $errorMsg,
                'message_id'     => null,
                'requires_setup' => false,
            ];
        } catch (\Throwable $e) {
            Log::error("WhatsApp Cloud API Exception: " . $e->getMessage());

            return [
                'success'        => false,
                'error'          => $e->getMessage(),
                'message_id'     => null,
                'requires_setup' => false,
            ];
        }
    }
}
