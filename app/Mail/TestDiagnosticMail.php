<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestDiagnosticMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientEmail
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Test Email Delivery Successful - Gym Console',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.system.test_mail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
