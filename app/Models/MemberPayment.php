<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberPayment extends Model
{
    use BelongsToBranch, BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'member_id',
        'membership_id',
        'invoice_number',
        'amount',
        'payment_method',
        'transaction_reference',
        'payment_date',
        'received_by_user_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class)->withTrashed();
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class)->withTrashed();
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function getReceiptNumberAttribute(): string
    {
        if (!empty($this->invoice_number) && !str_starts_with($this->invoice_number, 'RCP-') && !str_starts_with($this->invoice_number, 'ARM')) {
            return $this->invoice_number;
        }

        return 'INV-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getPublicHashAttribute(): string
    {
        return substr(hash_hmac('sha256', (string) $this->id, config('app.key') ?: 'gym-console-secret'), 0, 16);
    }

    public function getPublicInvoiceUrlAttribute(): string
    {
        return route('app.invoices.public', [
            'id' => $this->id,
            'hash' => $this->public_hash,
        ]);
    }

    public function getWhatsAppShareUrlAttribute(): string
    {
        $member = $this->member;
        $tenant = $this->tenant ?? \App\Services\TenantContext::getTenant() ?? auth()->user()?->tenant ?? \App\Models\Tenant::find($this->tenant_id);
        $invSettings = $tenant?->settings['invoice'] ?? [];
        $tenantSettings = $tenant?->settings ?? [];

        $gymName = !empty($invSettings['gym_name']) ? $invSettings['gym_name'] : ($tenant?->name ?? 'Armour 24-7 Gym');
        $gymAddress = !empty($invSettings['address']) ? $invSettings['address'] : (!empty($tenantSettings['address']) ? $tenantSettings['address'] : ($this->branch?->address ?? ($tenant?->mainBranch?->address ?? '')));
        $gymPhone = !empty($invSettings['phone']) ? $invSettings['phone'] : ($tenant?->phone ?? '');

        $rawPhone = preg_replace('/[^0-9]/', '', (string) ($member?->phone ?? ''));
        if (strlen($rawPhone) === 10) {
            $phone = '91' . $rawPhone;
        } elseif (strlen($rawPhone) === 12 && str_starts_with($rawPhone, '91')) {
            $phone = $rawPhone;
        } else {
            $phone = $rawPhone;
        }

        $invoiceNo = $this->receipt_number;
        $amount = number_format((float) $this->amount, 2);
        $date = $this->payment_date ? $this->payment_date->format('d M Y') : $this->created_at->format('d M Y');
        $currency = $tenant?->currency_symbol ?? 'Rs. ';
        $method = strtoupper((string) ($this->payment_method ?? 'CASH'));

        $membership = $this->membership ?? $member?->activeMembership ?? $member?->memberships()->latest()->first();
        $plan = $membership?->plan;
        $planName = $plan?->name ?? 'Gym Membership';
        $duration = $plan ? ($plan->duration_value . ' ' . ucfirst($plan->duration_type ?? 'Months')) : '';

        $publicUrl = $this->public_invoice_url;

        $msg = "🏋️ *{$gymName}*\n";
        if (!empty($gymAddress)) {
            $msg .= "📍 _{$gymAddress}_\n";
        }
        $msg .= "--------------------------------\n";
        $msg .= "🧾 *PAYMENT RECEIPT & TAX INVOICE*\n";
        $msg .= "--------------------------------\n\n";
        $msg .= "Dear *{$member?->full_name}*,\n";
        $msg .= "Thank you for your payment! Here are your transaction details:\n\n";
        $msg .= "📄 *Invoice No:* {$invoiceNo}\n";
        $msg .= "📅 *Date:* {$date}\n";
        $msg .= "💰 *Amount Paid:* {$currency}{$amount}\n";
        $msg .= "💳 *Payment Mode:* {$method}\n";

        if ($this->payment_method === 'split' && !empty($this->notes)) {
            $msg .= "ℹ️ *Breakdown:* {$this->notes}\n";
        }

        if (!empty($duration)) {
            $msg .= "📦 *Package:* {$planName} ({$duration})\n";
        }

        if ($membership && $membership->start_date && $membership->end_date) {
            $msg .= "⏳ *Tenure:* {$membership->start_date->format('d M Y')} to {$membership->end_date->format('d M Y')}\n";
        }

        $due = 0;
        if ($membership) {
            $due = max(0, (float) $membership->final_amount - (float) $membership->paid_amount);
        }
        if ($due > 0) {
            $msg .= "⚠️ *Remaining Balance Due:* {$currency}" . number_format($due, 2) . "\n";
        }

        $msg .= "\n🔗 *View & Download Official Invoice:*\n";
        $msg .= "{$publicUrl}\n\n";
        if (!empty($gymPhone)) {
            $msg .= "📞 Helpline / Front Desk: {$gymPhone}\n";
        }
        $msg .= "Stay Fit, Stay Strong! 💪";

        return 'https://api.whatsapp.com/send?phone=' . $phone . '&text=' . rawurlencode($msg);
    }
}
