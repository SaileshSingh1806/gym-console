<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewSupportTicketAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
        public string $initialMessage
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Support Ticket {$this->ticket->ticket_number}] {$this->ticket->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tickets.new_ticket_admin',
        );
    }
}
