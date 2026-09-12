<?php

namespace App\Mail;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GymOwnerWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public User $owner,
        public Plan $plan,
        public ?string $rawPassword = null,
        public ?string $loginUrl = null
    ) {
        $this->loginUrl = $loginUrl ?? url('/login');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🎉 Welcome to Gym Console - Your Gym Portal Credentials for {$this->tenant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenants.welcome',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
