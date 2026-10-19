@php
    $platformAppName = \App\Models\Setting::getGlobal('app_name', 'Gym Console');
    $platformSupportEmail = \App\Models\Setting::getGlobal('support_email', 'support@gymconsole.com');
    $platformSupportPhone = \App\Models\Setting::getGlobal('support_phone', '+91 9974995179');
    $platformLogo = \App\Models\Setting::getGlobal('logo_url');
    $cleanPhone = preg_replace('/[^0-9]/', '', (string)$platformSupportPhone);
    $whatsAppUrl = !empty($cleanPhone) 
        ? 'https://wa.me/' . $cleanPhone . '?text=' . urlencode('Hi ' . $platformAppName . ' Support, I have a query regarding my gym portal setup (' . $tenant->name . ').') 
        : 'https://wa.me/919974995179';
@endphp

@extends('emails.layouts.master', [
    'isPlatformMail' => true,
    'subject' => '🎉 Welcome to ' . $platformAppName . ' - Your Gym Portal Credentials for ' . $tenant->name,
    'headerSubtitle' => 'Multi-Branch Gym Management & Access Control Platform',
    'brandName' => $platformAppName,
    'brandLogo' => $platformLogo,
    'supportEmail' => $platformSupportEmail,
    'supportPhone' => $platformSupportPhone,
])

@section('content')
    <!-- Title Greeting -->
    <h1 style="margin: 0 0 12px 0; font-size: 22px; font-weight: 800; color: #ffffff; line-height: 28px;">
        Welcome aboard, {{ $owner->name }}! 🎉
    </h1>
    
    <p style="margin: 0 0 24px 0; font-size: 14px; color: #cbd5e1; line-height: 22px;">
        Your gym management portal for <strong>{{ $tenant->name }}</strong> has been configured and is ready for use. You can now manage memberships, staff, biometric access control, CRM leads, and multi-branch operations.
    </p>

    <!-- Credentials Highlight Box -->
    <div style="background-color: #0f172a; border: 1px solid #334155; border-radius: 16px; padding: 20px; margin-bottom: 24px;">
        <h3 style="margin: 0 0 14px 0; font-size: 13px; font-weight: 800; color: #818cf8; text-transform: uppercase; letter-spacing: 0.5px;">
            🔑 Your Gym Owner Login Credentials
        </h3>
        
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px;">
            <tr>
                <td style="padding: 6px 0; color: #94a3b8; width: 35%;">Gym Portal URL:</td>
                <td style="padding: 6px 0; color: #ffffff; font-weight: 600;">
                    <a href="{{ $loginUrl ?? url('/login') }}" style="color: #60a5fa; text-decoration: none;">{{ $loginUrl ?? url('/login') }}</a>
                </td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Login Email:</td>
                <td style="padding: 6px 0; color: #ffffff; font-weight: 700; font-family: monospace;">{{ $owner->email }}</td>
            </tr>
            @if(isset($rawPassword) && !empty($rawPassword))
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Password:</td>
                <td style="padding: 6px 0; color: #38bdf8; font-weight: 700; font-family: monospace;">{{ $rawPassword }}</td>
            </tr>
            @else
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Password:</td>
                <td style="padding: 6px 0; color: #cbd5e1; font-style: italic;">(The password set during registration)</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Gym Organization:</td>
                <td style="padding: 6px 0; color: #ffffff; font-weight: 600;">{{ $tenant->name }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Subscription Plan:</td>
                <td style="padding: 6px 0; color: #34d399; font-weight: 700;">
                    {{ $plan->name ?? 'Plan' }} 
                    @if($tenant->status === 'TRIAL')
                        <span style="font-size: 10px; background-color: rgba(251, 191, 36, 0.2); color: #fbbf24; padding: 2px 6px; border-radius: 6px; text-transform: uppercase;">Trial Active</span>
                    @elseif($tenant->status === 'ACTIVE')
                        <span style="font-size: 10px; background-color: rgba(34, 197, 94, 0.2); color: #4ade80; padding: 2px 6px; border-radius: 6px; text-transform: uppercase;">Active ({{ ucfirst($tenant->activeSubscription->billing_cycle ?? 'Yearly') }})</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- CTA Button -->
    <div style="text-align: center; margin-bottom: 24px;">
        <a href="{{ $loginUrl ?? url('/login') }}" style="display: inline-block; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-weight: 800; font-size: 14px; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4); letter-spacing: 0.3px;">
            🚀 Sign In to Gym Console &rarr;
        </a>
    </div>

    <!-- WhatsApp Support Box -->
    <div style="background-color: #0b1f14; border: 1px solid #16a34a; border-radius: 16px; padding: 18px 20px; margin-bottom: 26px; text-align: center;">
        <div style="font-size: 14px; font-weight: 800; color: #4ade80; margin-bottom: 4px;">
            💬 Need Quick Help Setting Up Your Gym?
        </div>
        <p style="margin: 0 0 12px 0; font-size: 12px; color: #cbd5e1; line-height: 18px;">
            Our specialist onboarding team is available on WhatsApp to assist with Excel member data import, staff roles, and biometric device integration.
        </p>
        <a href="{{ $whatsAppUrl }}" target="_blank" style="display: inline-block; background-color: #22c55e; color: #022c15; text-decoration: none; padding: 10px 24px; border-radius: 10px; font-weight: 800; font-size: 13px; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);">
            Chat with Support on WhatsApp ({{ $platformSupportPhone }}) &rarr;
        </a>
    </div>

    <!-- Quick Start Steps -->
    <div style="border-top: 1px solid #1e293b; padding-top: 20px; margin-bottom: 20px;">
        <h4 style="margin: 0 0 12px 0; font-size: 13px; font-weight: 700; color: #ffffff; text-transform: uppercase;">
            📋 Quick Start Checklist:
        </h4>
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px; color: #cbd5e1; line-height: 20px;">
            <tr>
                <td style="padding: 4px 0; vertical-align: top; width: 24px; color: #818cf8; font-weight: 800;">1.</td>
                <td style="padding: 4px 0;"><strong>Configure Gym Profile:</strong> Update operating hours, GST/tax details, and upload your gym logo in <em>Settings &rarr; Business Info</em>.</td>
            </tr>
            <tr>
                <td style="padding: 4px 0; vertical-align: top; color: #818cf8; font-weight: 800;">2.</td>
                <td style="padding: 4px 0;"><strong>Add Membership Plans:</strong> Create your monthly, quarterly, and annual subscription packages.</td>
            </tr>
            <tr>
                <td style="padding: 4px 0; vertical-align: top; color: #818cf8; font-weight: 800;">3.</td>
                <td style="padding: 4px 0;"><strong>Biometric Attendance & Access Control:</strong> Hook up Hikvision facial terminals, eSSL, or ZKTeco turnstile gates under <em>Settings &rarr; Biometric & IoT Devices</em>.</td>
            </tr>
            <tr>
                <td style="padding: 4px 0; vertical-align: top; color: #818cf8; font-weight: 800;">4.</td>
                <td style="padding: 4px 0;"><strong>Enroll Members & Staff:</strong> Start adding your members, trainers, and front-desk managers.</td>
            </tr>
        </table>
    </div>

    <!-- Security Tip -->
    <div style="background-color: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.25); border-radius: 12px; padding: 12px 16px; font-size: 11px; color: #fef08a; line-height: 16px;">
        💡 <strong>Security Tip:</strong> We recommend changing your temporary password upon your first successful login from your account settings.
    </div>
@endsection

