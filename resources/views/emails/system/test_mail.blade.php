@extends('emails.layouts.master', [
    'subject' => '✅ Test Email Delivery Successful - Gym Console',
    'headerSubtitle' => 'Email System Diagnostics & Verification'
])

@section('content')
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="display: inline-block; width: 48px; height: 48px; line-height: 48px; border-radius: 24px; background-color: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 24px; margin-bottom: 8px;">
            ✓
        </div>
        <h1 style="margin: 0; font-size: 20px; font-weight: 800; color: #ffffff;">
            Test Email Delivered Successfully!
        </h1>
        <p style="margin: 4px 0 0 0; font-size: 13px; color: #94a3b8;">
            Your Gym Console mail configuration and SMTP connection are working properly.
        </p>
    </div>

    <!-- Diagnostic Info Box -->
    <div style="background-color: #0f172a; border: 1px solid #334155; border-radius: 16px; padding: 20px; margin-bottom: 20px;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px;">
            <tr>
                <td style="padding: 6px 0; color: #94a3b8; width: 40%;">Mailer Driver:</td>
                <td style="padding: 6px 0; color: #38bdf8; font-weight: 700; font-family: monospace;">{{ config('mail.default') }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Sender Address:</td>
                <td style="padding: 6px 0; color: #ffffff; font-family: monospace;">{{ config('mail.from.address') }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Sender Name:</td>
                <td style="padding: 6px 0; color: #ffffff;">{{ config('mail.from.name') }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Recipient:</td>
                <td style="padding: 6px 0; color: #34d399; font-weight: 700; font-family: monospace;">{{ $recipientEmail }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Timestamp:</td>
                <td style="padding: 6px 0; color: #cbd5e1;">{{ now()->format('d M Y, h:i:s A e') }}</td>
            </tr>
        </table>
    </div>

    <p style="margin: 0; font-size: 12px; color: #94a3b8; text-align: center;">
        All automated transactional templates (Tenant Welcome, Member Cards, Invoices, Receipts, and Support Tickets) will use this delivery pipeline.
    </p>
@endsection

