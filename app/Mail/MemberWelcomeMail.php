<?php

namespace App\Mail;

use App\Models\Member;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public Member $member
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🏋️ Welcome to {$this->tenant->name} - Your Member Access Details",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.members.welcome',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
