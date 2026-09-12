<?php

namespace App\Mail;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantSubscriptionInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public Plan $plan,
        public string $billingCycle = 'monthly',
        public ?string $transactionId = null,
        public ?float $amountPaid = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚡ SaaS Subscription Active: {$this->plan->name} - {$this->tenant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscriptions.invoice',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
