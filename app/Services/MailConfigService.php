<?php

namespace App\Services;

use App\Models\GatewaySetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class MailConfigService
{
    /**
     * Dynamically configure Laravel's mailer from GatewaySetting or .env fallback.
     */
    public static function applyDynamicMailConfig(): void
    {
        try {
            $settings = GatewaySetting::getSettings();

            if (!empty($settings->smtp_host) && !empty($settings->smtp_username)) {
                $mailerType = $settings->smtp_mailer ?: 'smtp';

                Config::set('mail.default', $mailerType);
                Config::set('mail.mailers.smtp.transport', 'smtp');
                Config::set('mail.mailers.smtp.host', $settings->smtp_host);
                Config::set('mail.mailers.smtp.port', $settings->smtp_port ?: 587);
                Config::set('mail.mailers.smtp.encryption', $settings->smtp_encryption ?: 'tls');
                Config::set('mail.mailers.smtp.username', $settings->smtp_username);
                Config::set('mail.mailers.smtp.password', $settings->smtp_password);

                if (!empty($settings->smtp_from_address)) {
                    Config::set('mail.from.address', $settings->smtp_from_address);
                }

                if (!empty($settings->smtp_from_name)) {
                    Config::set('mail.from.name', $settings->smtp_from_name);
                }

                // Purge resolved mailer instance to re-initialize transport with new credentials
                app()->forgetInstance('mailer');
            }
        } catch (\Throwable $e) {
            Log::warning('MailConfigService: Failed to apply dynamic SMTP settings: ' . $e->getMessage());
        }
    }
}
