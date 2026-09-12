@extends('emails.layouts.master', [
    'subject' => 'Payment Receipt #' . $payment->receipt_number . ' - ' . ($tenant->name ?? 'Gym Console'),
    'brandName' => $tenant->name ?? config('app.name', 'Gym Console'),
    'headerSubtitle' => 'Official Payment Receipt & Tax Invoice'
])

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="display: inline-block; width: 48px; height: 48px; line-height: 48px; border-radius: 24px; background-color: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 24px; margin-bottom: 8px;">
            ✓
        </div>
        <h1 style="margin: 0; font-size: 20px; font-weight: 800; color: #ffffff;">
            Payment Successful!
        </h1>
        <p style="margin: 4px 0 0 0; font-size: 13px; color: #94a3b8;">
            Thank you for your payment. Here is your official transaction receipt.
        </p>
    </div>

    <!-- Receipt Summary Box -->
    <div style="background-color: #0f172a; border: 1px solid #334155; border-radius: 16px; padding: 20px; margin-bottom: 24px;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px;">
            <tr>
                <td style="padding: 6px 0; color: #94a3b8; width: 40%;">Receipt Number:</td>
                <td style="padding: 6px 0; color: #ffffff; font-weight: 700; font-family: monospace;">{{ $payment->receipt_number }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Date & Time:</td>
                <td style="padding: 6px 0; color: #ffffff;">{{ $payment->payment_date ? $payment->payment_date->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Member Name:</td>
                <td style="padding: 6px 0; color: #ffffff; font-weight: 600;">{{ $payment->member->full_name ?? ($payment->member_name ?? 'Gym Member') }}</td>
            </tr>
            @if(isset($payment->membership->plan))
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Package / Plan:</td>
                <td style="padding: 6px 0; color: #818cf8; font-weight: 700;">{{ $payment->membership->plan->name }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Payment Method:</td>
                <td style="padding: 6px 0; color: #ffffff; text-transform: uppercase; font-weight: 600;">{{ $payment->payment_method }}</td>
            </tr>
            @if($payment->reference_number)
            <tr>
                <td style="padding: 6px 0; color: #94a3b8;">Transaction Ref:</td>
                <td style="padding: 6px 0; color: #cbd5e1; font-family: monospace;">{{ $payment->reference_number }}</td>
            </tr>
            @endif
            <tr style="border-top: 1px solid #1e293b;">
                <td style="padding: 12px 0 4px 0; color: #ffffff; font-size: 14px; font-weight: 800;">Amount Paid:</td>
                <td style="padding: 12px 0 4px 0; color: #34d399; font-size: 18px; font-weight: 900; font-family: monospace;">
                    {{ $tenant->currency_symbol ?? '₹' }}{{ number_format((float) $payment->amount, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Gym Details & Notes -->
    <div style="font-size: 12px; color: #94a3b8; line-height: 18px; background-color: rgba(15, 23, 42, 0.6); padding: 14px; border-radius: 12px; border: 1px solid #1e293b; margin-bottom: 20px;">
        <p style="margin: 0 0 4px 0; font-weight: 700; color: #ffffff;">Issued By: {{ $tenant->name }}</p>
        <p style="margin: 0;">{{ $tenant->mainBranch->address ?? ($tenant->address ?? '') }}</p>
        @if($tenant->phone)
            <p style="margin: 2px 0 0 0;">Phone: {{ $tenant->phone }}</p>
        @endif
    </div>
@endsection

