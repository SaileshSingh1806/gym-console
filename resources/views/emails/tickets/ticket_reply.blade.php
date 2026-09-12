@extends('emails.layouts.master', [
    'subject' => '💬 Ticket Update: #' . $ticket->ticket_number . ' - ' . $ticket->subject,
    'headerSubtitle' => 'Helpdesk Conversation Update'
])

@section('content')
    <h1 style="margin: 0 0 12px 0; font-size: 20px; font-weight: 800; color: #ffffff; line-height: 26px;">
        Support Ticket Update 💬
    </h1>
    
    <p style="margin: 0 0 20px 0; font-size: 14px; color: #cbd5e1; line-height: 22px;">
        Hello <strong>{{ $isAdminSender ? ($ticket->user->name ?? 'Gym Owner') : 'Super Admin' }}</strong>, a new response has been posted on ticket <strong style="color: #818cf8; font-family: monospace;">#{{ $ticket->ticket_number }}</strong>.
    </p>

    <!-- Reply Box -->
    <div style="background-color: #0f172a; border-left: 4px solid {{ $isAdminSender ? '#6366f1' : '#38bdf8' }}; border-radius: 12px; border-top: 1px solid #334155; border-right: 1px solid #334155; border-bottom: 1px solid #334155; padding: 20px; margin-bottom: 24px;">
        <div style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #1e293b; display: flex; justify-content: space-between;">
            <strong style="color: #ffffff; font-size: 13px;">
                {{ $reply->user->name ?? ($isAdminSender ? 'Support Team' : 'User') }}
                @if($isAdminSender)
                    <span style="display: inline-block; padding: 1px 6px; border-radius: 4px; background-color: rgba(99, 102, 241, 0.2); color: #a5b4fc; font-size: 10px; text-transform: uppercase;">Agent</span>
                @endif
            </strong>
            <span style="color: #94a3b8; font-size: 11px;">{{ $reply->created_at->format('d M Y, h:i A') }}</span>
        </div>
        <p style="margin: 0; font-size: 13px; color: #f1f5f9; line-height: 22px; white-space: pre-line;">{{ $reply->message }}</p>
    </div>

    <!-- CTA Button -->
    <div style="text-align: center; margin-bottom: 16px;">
        @if($isAdminSender)
            <a href="{{ route('app.support.show', $ticket->id) }}" style="display: inline-block; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 12px; font-weight: 800; font-size: 13px; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);">
                View Ticket &amp; Reply &rarr;
            </a>
        @else
            <a href="{{ route('admin.tickets.show', $ticket->id) }}" style="display: inline-block; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 12px; font-weight: 800; font-size: 13px; box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.4);">
                Open Ticket in Admin Desk &rarr;
            </a>
        @endif
    </div>
@endsection
