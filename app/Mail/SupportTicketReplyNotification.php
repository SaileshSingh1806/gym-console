<?php

namespace App\Mail;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketReplyNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
        public SupportTicketReply $reply,
        public bool $isAdminSender
    ) {}

    public function envelope(): Envelope
    {
        $prefix = $this->isAdminSender ? 'Response on Ticket' : 'New Reply on Ticket';

        return new Envelope(
            subject: "Re: [{$this->ticket->ticket_number}] {$this->ticket->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tickets.ticket_reply',
        );
    }
}
