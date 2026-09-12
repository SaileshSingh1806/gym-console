@extends('emails.layouts.master', [
    'subject' => '⚡ SaaS Subscription Active: ' . $plan->name . ' - ' . ($tenant->name ?? 'Gym Console'),
    'headerSubtitle' => 'Gym Console SaaS Plan Invoice'
])

@section('content')
    <h1 style="margin: 0 0 12px 0; font-size: 22px; font-weight: 800; color: #ffffff; line-height: 28px;">
        Subscription Plan Active! ⚡
    </h1>
    
    <p style="margin: 0 0 24px 0; font-size: 14px; color: #cbd5e1; line-height: 22px;">
        Your Gym Console SaaS subscription for <strong>{{ $tenant->name }}</strong> has been successfully updated and activated.
    </p>

    <!-- Subscription Invoice Box -->
    <div style="background-color: #0f172a; border: 1px solid #334155; border-radius: 16px; padding: 20px; margin-bottom: 24px;">
        <h3 style="margin: 0 0 14px 0; font-size: 13px; font-weight: 800; color: #818cf8; text-transform: uppercase; letter-spacing: 0.5px;">
            📄 Subscription Invoice Details
        </h3>
        
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px;">
            <tr>
                <td style="padding: 6px 0; color: #94a3b8; width: 40%;">Selected Plan:</td>
                <td style="padding: 6px 0; color: #ffffff; font-weight: 800; font-size: 15px; color: #a5b4fc;">{{ $plan->name }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Billing Cycle:</td>
                <td style="padding: 6px 0; color: #ffffff; text-transform: capitalize; font-weight: 600;">{{ $billingCycle ?? 'Monthly' }}</td>
            </tr>
            @if(isset($transactionId))
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Transaction ID:</td>
                <td style="padding: 6px 0; color: #38bdf8; font-family: monospace;">{{ $transactionId }}</td>
            </tr>
            @endif
            @if(isset($amountPaid))
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Amount Paid:</td>
                <td style="padding: 6px 0; color: #34d399; font-weight: 800; font-family: monospace;">{{ $tenant->currency_symbol ?? '₹' }}{{ number_format((float) $amountPaid, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Status:</td>
                <td style="padding: 6px 0; color: #34d399; font-weight: 700; text-transform: uppercase;">ACTIVE</td>
            </tr>
        </table>
    </div>

    <!-- CTA Button -->
    <div style="text-align: center; margin-bottom: 24px;">
        <a href="{{ url('/app/subscription') }}" style="display: inline-block; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 12px; font-weight: 800; font-size: 13px; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);">
            Manage Subscription & Quotas &rarr;
        </a>
    </div>
@endsection

