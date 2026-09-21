<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewaySetting extends Model
{
    use HasFactory;

    protected $table = 'gateway_settings';

    protected $fillable = [
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
        'enable_online_payments',
        'razorpay_key_id',
        'razorpay_key_secret',
        'razorpay_webhook_secret',
        'upi_vpa_id',
        'upi_merchant_name',
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
        'enable_online_payments'        => 'boolean',
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
                'enable_online_payments'        => true,
                'razorpay_key_id'               => config('services.razorpay.key_id', 'rzp_test_cctvcrm_demo'),
                'razorpay_key_secret'           => config('services.razorpay.key_secret', 'rzp_test_secret_demo'),
                'upi_vpa_id'                    => config('services.upi.vpa_id', 'cctvsecurity@okhdfcbank'),
                'upi_merchant_name'             => config('services.upi.merchant_name', 'CCTV Security Solutions'),
                'auto_receipt_whatsapp_enabled' => true,
                'auto_receipt_email_enabled'    => true,
            ]);
        }

        return $settings;
    }
}
