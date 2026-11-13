<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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

    protected static function booted(): void
    {
        static::creating(function (MemberPayment $payment) {
            if (empty($payment->invoice_number)) {
                $tenantId = $payment->tenant_id ?? auth()->user()?->tenant_id;
                $payment->invoice_number = self::generateInvoiceNumber($tenantId ? (int) $tenantId : null);
            }
            if (empty($payment->received_by_user_id) && auth()->check()) {
                $payment->received_by_user_id = auth()->id();
            }
            if (empty($payment->payment_date)) {
                $payment->payment_date = now()->toDateString();
            }
        });
    }

    /**
     * Generate a collision-safe invoice/receipt number scoped to a tenant.
     */
    public static function generateInvoiceNumber(?int $tenantId = null): string
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        $invSettings = $tenant?->settings['invoice'] ?? [];
        $rawPrefix = !empty($invSettings['invoice_prefix']) ? $invSettings['invoice_prefix'] : 'INV-';

        // Clean prefix (e.g. "#ARM-" -> "ARM-", "#FIT-" -> "FIT-", "INV-" -> "INV-")
        $prefix = ltrim(trim($rawPrefix), '#');
        if (!str_ends_with($prefix, '-') && !str_ends_with($prefix, '/')) {
            $prefix .= '-';
        }

        $baseQuery = static::withoutGlobalScopes();
        if ($tenantId) {
            $baseQuery->where('tenant_id', $tenantId);
        }
        $count = $baseQuery->count();
        $seq = $count + 1;

        do {
            $candidate = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            $existsQuery = static::withoutGlobalScopes();
            if ($tenantId) {
                $existsQuery->where('tenant_id', $tenantId);
            }
            $exists = $existsQuery->where('invoice_number', $candidate)->exists();
            if (!$exists) {
                return $candidate;
            }
            $seq++;
        } while ($seq < $count + 10000);

        return $prefix . strtoupper(Str::random(6));
    }

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
        if (!empty($this->invoice_number)) {
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

        $gymName = !empty($invSettings['gym_name']) ? $invSettings['gym_name'] : ($tenant?->name ?? 'Gym');
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
        $currency = $tenant?->currency_symbol ?? '₹';
        $method = strtoupper((string) ($this->payment_method ?? 'CASH'));

        $membership = $this->membership ?? $member?->activeMembership ?? $member?->memberships()->latest()->first();
        $plan = $membership?->plan;
        $planName = $plan?->name ?? 'Gym Membership';
        $duration = $plan ? ($plan->duration_value . ' ' . ucfirst($plan->duration_type ?? 'Months')) : '';

        $publicUrl = $this->public_invoice_url;

        // Financial calculations matching Invoice Sheet exactly
        $discountAmount = $membership ? (float) ($membership->discount ?? 0) : 0;
        if ($membership && (float)$membership->final_amount > 0) {
            $totalPackageAmount = (float) $membership->final_amount;
            $totalPaidSoFar = (float) ($membership->paid_amount > 0 ? $membership->paid_amount : $this->amount);
        } elseif ($plan && (float)$plan->price > 0) {
            $totalPackageAmount = max(0, (float)$plan->price - $discountAmount);
            $totalPaidSoFar = (float) ($membership?->paid_amount > 0 ? $membership->paid_amount : $this->amount);
        } else {
            $totalPackageAmount = (float) $this->amount;
            $totalPaidSoFar = (float) $this->amount;
        }

        $balanceDue = max(0, $totalPackageAmount - $totalPaidSoFar);
        $amountReceived = min($totalPackageAmount, $totalPaidSoFar);

        // Professional, clean business receipt format matching the invoice exactly
        $lines = [];
        $lines[] = "*{$gymName}*";
        $lines[] = "PAYMENT RECEIPT & TAX INVOICE";
        $lines[] = "--------------------------------";
        $lines[] = "Dear " . ($member?->full_name ?? 'Member') . ",";
        $lines[] = "Thank you for your payment. Here are your transaction details:";
        $lines[] = "";
        $lines[] = "• Receipt No: {$invoiceNo}";
        $lines[] = "• Date: {$date}";
        $lines[] = "• Plan: {$planName}" . (!empty($duration) ? " ({$duration})" : "");
        $lines[] = "• Payment Mode: {$method}";
        $lines[] = "• Amount Paid (This Receipt): {$currency}" . number_format((float) $this->amount, 2);

        if ($totalPackageAmount > (float) $this->amount || $membership) {
            $lines[] = "• Total Package Fee: {$currency}" . number_format($totalPackageAmount, 2);
            $lines[] = "• Total Amount Received: {$currency}" . number_format($amountReceived, 2);
        }

        if ($balanceDue > 0) {
            $lines[] = "• Balance Due: {$currency}" . number_format($balanceDue, 2) . " (Partial Payment)";
        } else {
            $lines[] = "• Balance Due: {$currency}0.00 (PAID IN FULL)";
        }

        if ($membership && $membership->start_date && $membership->end_date) {
            $lines[] = "• Membership Validity: {$membership->start_date->format('d M Y')} to {$membership->end_date->format('d M Y')}";
        }

        $lines[] = "";
        $lines[] = "View & Download Official Invoice:";
        $lines[] = $publicUrl;

        if (!empty($gymPhone)) {
            $lines[] = "";
            $lines[] = "Front Desk Helpline: {$gymPhone}";
        }

        $msg = implode("\n", $lines);

        return 'https://api.whatsapp.com/send?phone=' . $phone . '&text=' . rawurlencode($msg);
    }
}
