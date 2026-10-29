<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewaySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_trade_name',
        'company_legal_name',
        'company_gstin',
        'company_pan',
        'company_state',
        'company_state_code',
        'company_address',
        'active_sms_gateway',
        'twilio_account_sid',
        'twilio_auth_token',
        'twilio_from_number',
        'msg91_auth_key',
        'msg91_sender_id',
        'msg91_flow_id',
        'msg91_dlt_te_id',
        'webhook_url',
        'webhook_secret',
        'webhook_method',
        'webhook_headers',
        'webhook_payload_template',
        'ticket_auto_sms_enabled',
        'ticket_auto_whatsapp_enabled',
        'otp_sms_enabled',
        'otp_whatsapp_enabled',
        'otp_expiry_minutes',
        'auto_receipt_whatsapp_enabled',
        'auto_receipt_email_enabled',
        'smtp_mailer',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_address',
        'smtp_from_name',
    ];

    protected $casts = [
        'ticket_auto_sms_enabled'       => 'boolean',
        'ticket_auto_whatsapp_enabled'  => 'boolean',
        'otp_sms_enabled'               => 'boolean',
        'otp_whatsapp_enabled'          => 'boolean',
        'otp_expiry_minutes'            => 'integer',
        'auto_receipt_whatsapp_enabled' => 'boolean',
        'auto_receipt_email_enabled'    => 'boolean',
    ];

    /**
     * Get or create the singleton instance of gateway settings.
     */
    public static function getSettings(): self
    {
        $settings = self::first();

        if (!$settings) {
            $settings = self::create([
                'active_sms_gateway'            => config('services.sms.active_gateway', 'log'),
                'twilio_account_sid'            => config('services.twilio.sid'),
                'twilio_auth_token'             => config('services.twilio.token'),
                'twilio_from_number'            => config('services.twilio.from'),
                'msg91_auth_key'                => config('services.msg91.auth_key'),
                'msg91_sender_id'               => config('services.msg91.sender_id'),
                'msg91_flow_id'                 => config('services.msg91.flow_id'),
                'webhook_url'                   => config('services.webhook.url'),
                'webhook_method'                => 'POST',
                'ticket_auto_sms_enabled'       => true,
                'ticket_auto_whatsapp_enabled'  => true,
                'otp_sms_enabled'               => true,
                'otp_whatsapp_enabled'          => true,
                'otp_expiry_minutes'            => 10,
                'auto_receipt_whatsapp_enabled' => true,
                'auto_receipt_email_enabled'    => true,
            ]);
        }

        return $settings;
    }
}
