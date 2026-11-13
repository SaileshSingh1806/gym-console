<?php

namespace App\Services;

use App\Mail\PaymentReceiptMail;
use App\Models\ActivityLog;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\Membership;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MemberPaymentService
{
    public function recordPayment(
        Member $member,
        float $amount,
        string $paymentMethod,
        ?Membership $membership = null,
        ?string $reference = null,
        ?string $notes = null
    ): MemberPayment {
        return DB::transaction(function () use ($member, $amount, $paymentMethod, $membership, $reference, $notes) {
            $invoiceNumber = MemberPayment::generateInvoiceNumber((int) $member->tenant_id);

            $payment = MemberPayment::create([
                'tenant_id' => $member->tenant_id,
                'branch_id' => $member->branch_id,
                'member_id' => $member->id,
                'membership_id' => $membership?->id,
                'invoice_number' => $invoiceNumber,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'transaction_reference' => $reference,
                'payment_date' => now()->toDateString(),
                'received_by_user_id' => auth()->id(),
                'notes' => $notes,
            ]);

            if ($membership) {
                $newPaid = min((float) $membership->final_amount, (float) $membership->paid_amount + $amount);
                $membership->update(['paid_amount' => $newPaid]);
            }

            $currency = $member->tenant?->currency_symbol ?? '₹';
            $payMethodFormatted = strtoupper($paymentMethod);
            $receiptLabel = $payment->receipt_number ?? $invoiceNumber;

            ActivityLog::log(
                'member_payment_received',
                "Collected payment of {$currency}" . number_format($amount, 2) . " via {$payMethodFormatted} ({$receiptLabel}) from {$member->full_name}",
                $payment,
                ['payment_id' => $payment->id, 'member_id' => $member->id, 'amount' => $amount, 'method' => $paymentMethod]
            );

            if (! empty($member->email)) {
                $tenant = $member->tenant;
                if ($tenant) {
                    TenantMailService::send($tenant, $member->email, new PaymentReceiptMail($tenant, $payment));
                }
            }

            return $payment;
        });
    }
}
