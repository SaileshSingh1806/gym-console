@extends('emails.layouts.master', [
    'subject' => '🚨 New Support Ticket #' . $ticket->ticket_number . ' - ' . ($ticket->tenant->name ?? 'Gym Tenant'),
    'headerSubtitle' => 'Super Admin Helpdesk Alert'
])

@section('content')
    <h1 style="margin: 0 0 12px 0; font-size: 20px; font-weight: 800; color: #ffffff; line-height: 26px;">
        New Support Ticket Raised 🔔
    </h1>
    
    <p style="margin: 0 0 20px 0; font-size: 14px; color: #cbd5e1; line-height: 22px;">
        A new support ticket has been submitted by <strong>{{ $ticket->user->name ?? 'Gym Owner' }}</strong> from <strong>{{ $ticket->tenant->name ?? 'Gym Tenant' }}</strong>.
    </p>

    <!-- Ticket Summary Box -->
    <div style="background-color: #0f172a; border: 1px solid #334155; border-radius: 16px; padding: 20px; margin-bottom: 24px;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px;">
            <tr>
                <td style="padding: 6px 0; color: #94a3b8; width: 35%;">Ticket ID:</td>
                <td style="padding: 6px 0; color: #818cf8; font-weight: 800; font-family: monospace;">{{ $ticket->ticket_number }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Subject:</td>
                <td style="padding: 6px 0; color: #ffffff; font-weight: 700;">{{ $ticket->subject }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Gym Organization:</td>
                <td style="padding: 6px 0; color: #ffffff;">{{ $ticket->tenant->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Sender Email:</td>
                <td style="padding: 6px 0; color: #cbd5e1; font-family: monospace;">{{ $ticket->user->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Priority & Category:</td>
                <td style="padding: 6px 0;">
                    <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; background-color: rgba(239, 68, 68, 0.2); color: #f87171; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                        {{ $ticket->priority }}
                    </span>
                    <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; background-color: #334155; color: #cbd5e1; font-size: 11px; text-transform: uppercase; margin-left: 6px;">
                        {{ $ticket->category }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Message Snippet -->
    <div style="background-color: #0b0f19; border-left: 4px solid #6366f1; border-radius: 8px; padding: 16px; margin-bottom: 28px;">
        <p style="margin: 0 0 6px 0; font-size: 12px; font-weight: 700; color: #818cf8; text-transform: uppercase;">Ticket Message:</p>
        <p style="margin: 0; font-size: 13px; color: #e2e8f0; line-height: 20px; white-space: pre-line;">{{ $initialMessage }}</p>
    </div>

    <!-- CTA Button -->
    <div style="text-align: center; margin-bottom: 16px;">
        <a href="{{ route('admin.tickets.show', $ticket->id) }}" style="display: inline-block; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-weight: 800; font-size: 14px; box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.4);">
            Open &amp; Reply in Admin Desk &rarr;
        </a>
    </div>
@endsection
