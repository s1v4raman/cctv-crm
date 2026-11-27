<?php

namespace App\Services;

use App\Models\GatewaySetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
                $port = (int) ($settings->smtp_port ?: 587);
                $encryption = strtolower((string) $settings->smtp_encryption);
                if (!in_array($encryption, ['tls', 'ssl', 'starttls'])) {
                    $encryption = null;
                }

                // If port is 465 or ssl encryption is chosen, use 'smtps' scheme for direct SSL socket connection
                $scheme = ($port === 465 || $encryption === 'ssl') ? 'smtps' : null;

                // Sanitize Google App Password if pasted with spaces (e.g. "xxxx xxxx xxxx xxxx")
                $password = (string) $settings->smtp_password;
                $cleanPassword = trim($password);
                if (preg_match('/^[a-zA-Z]{4}\s+[a-zA-Z]{4}\s+[a-zA-Z]{4}\s+[a-zA-Z]{4}$/', $cleanPassword)) {
                    $cleanPassword = str_replace(' ', '', $cleanPassword);
                }

                Config::set('mail.default', $mailerType);
                Config::set('mail.mailers.smtp.transport', 'smtp');
                Config::set('mail.mailers.smtp.scheme', $scheme);
                Config::set('mail.mailers.smtp.host', $settings->smtp_host);
                Config::set('mail.mailers.smtp.port', $port);
                Config::set('mail.mailers.smtp.encryption', $encryption);
                Config::set('mail.mailers.smtp.username', trim((string) $settings->smtp_username));
                Config::set('mail.mailers.smtp.password', $cleanPassword);
                Config::set('mail.mailers.smtp.timeout', 15);

                if (!empty($settings->smtp_from_address)) {
                    Config::set('mail.from.address', $settings->smtp_from_address);
                }

                if (!empty($settings->smtp_from_name)) {
                    Config::set('mail.from.name', $settings->smtp_from_name);
                }

                // Purge resolved mailer instance to re-initialize transport with new credentials
                try {
                    Mail::purge('smtp');
                } catch (\Throwable $e) {
                    // ignore if not initialized
                }
                app()->forgetInstance('mailer');
            }
        } catch (\Throwable $e) {
            Log::warning('MailConfigService: Failed to apply dynamic SMTP settings: ' . $e->getMessage());
        }
    }

    /**
     * Check if a live SMTP server is configured and ready for live internet delivery.
     */
    public static function isSmtpConfigured(): bool
    {
        try {
            $settings = GatewaySetting::getSettings();
            if (!empty($settings->smtp_host) && !empty($settings->smtp_username) && ($settings->smtp_mailer ?? 'smtp') !== 'log') {
                return true;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $envMailer = config('mail.default', 'log');
        $envHost = config('mail.mailers.smtp.host');
        $envUser = config('mail.mailers.smtp.username');

        return $envMailer === 'smtp' && !empty($envHost) && !empty($envUser) && $envHost !== '127.0.0.1';
    }
}
