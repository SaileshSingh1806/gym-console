<?php

namespace App\Console\Commands;

use App\Mail\TestDiagnosticMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestMailCommand extends Command
{
    protected $signature = 'mail:test {email? : The recipient email address}';

    protected $description = 'Send a diagnostic test email to verify mail server and SMTP delivery';

    public function handle(): int
    {
        $email = $this->argument('email') ?? config('mail.from.address') ?? 'test@example.com';

        $this->info("📧 Attempting to dispatch test email to: {$email}");
        $this->line('Driver: '.config('mail.default'));
        $this->line('Host: '.config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port'));
        $this->line('From: '.config('mail.from.address').' ('.config('mail.from.name').')');

        try {
            Mail::to($email)->send(new TestDiagnosticMail($email));
            $this->info("✅ Test email dispatched successfully to {$email}!");

            if (config('mail.default') === 'log') {
                $this->comment('ℹ️ Notice: MAIL_MAILER is set to "log". The email was written to storage/logs/laravel.log.');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Failed to deliver test email: '.$e->getMessage());
            $this->line($e->getTraceAsString());

            return self::FAILURE;
        }
    }
}
