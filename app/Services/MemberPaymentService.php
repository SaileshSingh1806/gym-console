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
            $invoiceNumber = 'RCP-'.strtoupper(Str::random(6)).'-'.date('Ymd');

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
                $newPaid = (float) $membership->paid_amount + $amount;
                $membership->update(['paid_amount' => $newPaid]);
            }

            ActivityLog::log('member_payment_received', "Received payment of \${$amount} via {$paymentMethod} from {$member->full_name}", $payment);

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
