@extends('emails.layouts.master', [
    'subject' => 'Welcome to ' . ($tenant->name ?? 'our Gym') . ' - Your Member Access Card',
    'brandName' => $tenant->name ?? config('app.name', 'Gym Console'),
    'headerSubtitle' => 'Member Access & Fitness Protocol'
])

@section('content')
    <h1 style="margin: 0 0 12px 0; font-size: 22px; font-weight: 800; color: #ffffff; line-height: 28px;">
        Welcome to {{ $tenant->name }}, {{ $member->first_name }}! 🏋️
    </h1>
    
    <p style="margin: 0 0 24px 0; font-size: 14px; color: #cbd5e1; line-height: 22px;">
        Your membership profile has been successfully registered. Here are your access and identification details for checking in at the gym.
    </p>

    <!-- Member Details Box -->
    <div style="background-color: #0f172a; border: 1px solid #334155; border-radius: 16px; padding: 20px; margin-bottom: 24px;">
        <h3 style="margin: 0 0 14px 0; font-size: 13px; font-weight: 800; color: #818cf8; text-transform: uppercase; letter-spacing: 0.5px;">
            🪪 Member Identification Card
        </h3>
        
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px;">
            <tr>
                <td style="padding: 6px 0; color: #94a3b8; width: 35%;">Member Name:</td>
                <td style="padding: 6px 0; color: #ffffff; font-weight: 700;">{{ $member->full_name }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Member Code / ID:</td>
                <td style="padding: 6px 0; color: #38bdf8; font-weight: 800; font-family: monospace; font-size: 14px;">{{ $member->member_code ?? 'GYM'.str_pad($member->id, 4, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Registered Branch:</td>
                <td style="padding: 6px 0; color: #ffffff; font-weight: 600;">{{ $member->branch->name ?? 'Main Branch' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Contact Number:</td>
                <td style="padding: 6px 0; color: #ffffff; font-family: monospace;">{{ $member->phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Join Date:</td>
                <td style="padding: 6px 0; color: #ffffff;">{{ $member->join_date ? $member->join_date->format('d M Y') : now()->format('d M Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Status:</td>
                <td style="padding: 6px 0; color: #34d399; font-weight: 700; text-transform: uppercase;">{{ $member->status }}</td>
            </tr>
        </table>
    </div>

    <!-- Gym Facilities & Guidelines -->
    <div style="background-color: rgba(79, 70, 229, 0.08); border: 1px solid rgba(79, 70, 229, 0.25); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
        <h4 style="margin: 0 0 8px 0; font-size: 12px; font-weight: 700; color: #a5b4fc; text-transform: uppercase;">
            📌 Gym Check-in Guidelines:
        </h4>
        <ul style="margin: 0; padding-left: 20px; font-size: 12px; color: #cbd5e1; line-height: 20px;">
            <li>Please carry a clean gym towel and indoor sports shoes.</li>
            <li>Use your Member ID or biometric scan at the entrance turnstile for automated check-in.</li>
            <li>Reach out to our certified trainers on the gym floor for equipment guidance and workout assistance.</li>
        </ul>
    </div>

    <p style="margin: 0; font-size: 13px; color: #94a3b8; text-align: center;">
        Have a great workout session! See you on the gym floor! 💪
    </p>
@endsection

