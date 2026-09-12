<?php

namespace App\Mail;

use App\Models\MemberPayment;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public MemberPayment $payment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🧾 Payment Receipt #{$this->payment->receipt_number} - {$this->tenant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payments.receipt',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
