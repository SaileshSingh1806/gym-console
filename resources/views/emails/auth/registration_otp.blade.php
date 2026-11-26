@php
    $platformAppName = \App\Models\Setting::getGlobal('app_name', 'GymDeskHub');
    $platformSupportEmail = \App\Models\Setting::getGlobal('support_email', 'support@gymdeskhub.com');
    $platformSupportPhone = \App\Models\Setting::getGlobal('support_phone', '+91 9974995179');
    $platformLogo = \App\Models\Setting::getGlobal('logo_url');
@endphp

@extends('emails.layouts.master', [
    'isPlatformMail' => true,
    'subject' => $otp . ' is your ' . $platformAppName . ' verification code',
    'headerSubtitle' => 'Account Registration & Email Verification',
    'brandName' => $platformAppName,
    'brandLogo' => $platformLogo,
    'supportEmail' => $platformSupportEmail,
    'supportPhone' => $platformSupportPhone,
])

@section('content')
    <!-- Title Greeting -->
    <h1 style="margin: 0 0 12px 0; font-size: 22px; font-weight: 800; color: #ffffff; line-height: 28px;">
        Verify Your Email Address
    </h1>
    
    <p style="margin: 0 0 20px 0; font-size: 14px; color: #cbd5e1; line-height: 22px;">
        Hi <strong>{{ $ownerName }}</strong>,<br>
        Thank you for starting registration for <strong>{{ $gymName }}</strong> on {{ $platformAppName }}. Please enter the verification code below on the registration screen to verify your email and activate your account.
    </p>

    <!-- OTP Code Highlight Box -->
    <div style="background-color: #151618; border: 1px solid #334155; border-radius: 16px; padding: 26px 20px; margin: 24px 0; text-align: center;">
        <span style="display: block; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px;">
            ONE-TIME VERIFICATION CODE
        </span>
        
        <div style="font-family: 'Courier New', Courier, monospace, monospace; font-size: 38px; font-weight: 900; color: #f59e0b; letter-spacing: 8px; line-height: 1.2; text-shadow: 0 0 20px rgba(245, 158, 11, 0.3);">
            {{ $otp }}
        </div>
        
        <span style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-top: 12px;">
            ⏳ Valid for <strong>10 minutes</strong>. Do not share this code with anyone.
        </span>
    </div>

    <!-- Security & Help Note -->
    <div style="background-color: rgba(30, 41, 59, 0.4); border-left: 3px solid #6366f1; border-radius: 0 10px 10px 0; padding: 12px 16px; margin-bottom: 24px;">
        <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 18px;">
            <strong style="color: #cbd5e1;">Didn't request this code?</strong> If you did not initiate this registration for {{ $gymName }}, please ignore this email or contact support at <a href="mailto:{{ $platformSupportEmail }}" style="color: #818cf8; text-decoration: none;">{{ $platformSupportEmail }}</a>.
        </p>
    </div>

    <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 20px;">
        Regards,<br>
        <strong>Team {{ $platformAppName }}</strong>
    </p>
@endsection

