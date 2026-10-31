<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $ownerName,
        public string $gymName
    ) {}

    public function envelope(): Envelope
    {
        $appName = Setting::getGlobal('app_name', config('app.name', 'GymDeskHub'));

        return new Envelope(
            subject: "🔐 {$this->otp} is your {$appName} verification code",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.registration_otp',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

