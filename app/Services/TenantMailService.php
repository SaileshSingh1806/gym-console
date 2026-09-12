<?php

namespace App\Services;

use App\Mail\TestDiagnosticMail;
use App\Models\Tenant;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TenantMailService
{
    /**
     * Get SMTP configuration for a given tenant.
     *
     * @return array{host: string, port: int, encryption: string, username: string, password: string, from_address: string, from_name: string}|null
     */
    public static function getTenantSmtpConfig(?Tenant $tenant): ?array
    {
        if (! $tenant) {
            return null;
        }

        $settings = $tenant->settings ?? [];
        $smtp = $settings['smtp'] ?? [];

        $isEnabled = ! empty($smtp['enabled']) || ! empty($smtp['smtp_enabled']);
        $host = trim($smtp['mail_host'] ?? $smtp['host'] ?? '');

        if (! $isEnabled || empty($host)) {
            return null;
        }

        return [
            'host' => $host,
            'port' => (int) ($smtp['mail_port'] ?? $smtp['port'] ?? 587),
            'encryption' => $smtp['mail_encryption'] ?? $smtp['encryption'] ?? 'tls',
            'username' => trim($smtp['mail_username'] ?? $smtp['username'] ?? ''),
            'password' => $smtp['mail_password'] ?? $smtp['password'] ?? '',
            'from_address' => trim($smtp['mail_from_address'] ?? $smtp['from_address'] ?? $tenant->email ?? config('mail.from.address')),
            'from_name' => trim($smtp['mail_from_name'] ?? $smtp['from_name'] ?? $tenant->name ?? config('mail.from.name')),
        ];
    }

    /**
     * Configure a dynamic mailer transport for the tenant and dispatch the mailable.
     * Falls back to the system default mailer if tenant custom SMTP fails or is unconfigured.
     */
    public static function send(Tenant $tenant, string|array $to, Mailable $mailable): bool
    {
        $smtpConfig = self::getTenantSmtpConfig($tenant);

        if ($smtpConfig && ! empty($smtpConfig['host'])) {
            try {
                $encryption = ($smtpConfig['encryption'] === 'none') ? null : $smtpConfig['encryption'];
                $mailerKey = 'tenant_smtp_'.$tenant->id;

                Config::set("mail.mailers.{$mailerKey}", [
                    'transport' => 'smtp',
                    'host' => $smtpConfig['host'],
                    'port' => (int) $smtpConfig['port'],
                    'encryption' => $encryption,
                    'username' => $smtpConfig['username'],
                    'password' => $smtpConfig['password'],
                    'timeout' => 10,
                    'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
                ]);

                if (! empty($smtpConfig['from_address'])) {
                    $mailable->from($smtpConfig['from_address'], $smtpConfig['from_name'] ?? $tenant->name);
                }

                Mail::mailer($mailerKey)->to($to)->send($mailable);

                return true;
            } catch (\Throwable $e) {
                Log::warning("Tenant {$tenant->id} ({$tenant->name}) custom SMTP dispatch to {$to} failed, attempting system default fallback: ".$e->getMessage());
            }
        }

        // Fallback to default system mailer
        try {
            Mail::to($to)->send($mailable);

            return true;
        } catch (\Throwable $e) {
            Log::error("System email dispatch to {$to} failed: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Test tenant's SMTP configuration directly and throw exception on failure.
     */
    public static function testTenantSmtp(Tenant $tenant, string $testEmail, array $smtpConfig): void
    {
        $encryption = ($smtpConfig['encryption'] === 'none') ? null : ($smtpConfig['encryption'] ?? 'tls');
        $mailerKey = 'tenant_test_smtp_'.$tenant->id.'_'.time();

        Config::set("mail.mailers.{$mailerKey}", [
            'transport' => 'smtp',
            'host' => $smtpConfig['host'],
            'port' => (int) $smtpConfig['port'],
            'encryption' => $encryption,
            'username' => $smtpConfig['username'],
            'password' => $smtpConfig['password'],
            'timeout' => 10,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ]);

        $mailable = new TestDiagnosticMail($testEmail);

        if (! empty($smtpConfig['from_address'])) {
            $mailable->from($smtpConfig['from_address'], $smtpConfig['from_name'] ?? $tenant->name);
        }

        Mail::mailer($mailerKey)->to($testEmail)->send($mailable);
    }
}

