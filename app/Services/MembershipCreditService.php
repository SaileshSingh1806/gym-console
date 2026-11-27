<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Membership;
use App\Models\MemberPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class MembershipCreditService
{
    public static function cents($amount): int
    {
        return (int) round((float) $amount * 100, 0, PHP_ROUND_HALF_UP);
    }

    public static function eligible(MemberPayment $payment): bool
    {
        $membership = $payment->membership;
        $plan = $membership?->plan;
        return $membership && ! $membership->trashed() && $plan
            && (int) $membership->tenant_id === (int) $payment->tenant_id
            && (int) $membership->member_id === (int) $payment->member_id
            && $plan->duration_type === 'days' && (int) $plan->duration_value <= 7
            && (int) $plan->duration_value > 0
            && ! in_array($membership->status, ['CANCELLED', 'TRANSFERRED'], true)
            && ! preg_match('/REVERSED|CANCELLED|TRANSFERRED|PT Package|Personal Training/i', $payment->notes ?? '')
            && self::cents($payment->amount) > 0
            && self::cents($membership->paid_amount) >= self::cents($membership->final_amount);
    }

    public static function available(Member $member): array
    {
        if (! Schema::hasTable('membership_payment_credits')) return [];
        $payments = $member->payments()->where('tenant_id', $member->tenant_id)->with('membership.plan')->whereNotNull('membership_id')->get();
        $used = DB::table('membership_payment_credits')->where('tenant_id', $member->tenant_id)
            ->where('member_id', $member->id)->selectRaw('source_payment_id, SUM(amount_cents) as used')
            ->groupBy('source_payment_id')->pluck('used', 'source_payment_id');
        $targets = DB::table('membership_payment_credits')->where('tenant_id', $member->tenant_id)->where('member_id', $member->id)->pluck('target_membership_id')->all();
        return $payments->filter(fn ($p) => self::eligible($p) && ! in_array($p->membership_id, $targets))->map(function ($p) use ($used) {
            return [
                'id' => $p->id,
                'receipt' => $p->receipt_number,
                'plan' => $p->membership->plan->name,
                'date' => $p->payment_date?->format('d M Y'),
                'available' => max(0, self::cents($p->amount) - (int) ($used[$p->id] ?? 0)),
            ];
        })->filter(fn ($p) => $p['available'] > 0)->values()->all();
    }

    public static function protectedPayment(MemberPayment $payment): bool
    {
        return Schema::hasTable('membership_payment_credits') && DB::table('membership_payment_credits')
            ->where('tenant_id', $payment->tenant_id)->where(function ($q) use ($payment) {
                $q->where('source_payment_id', $payment->id);
                if ($payment->membership_id) $q->orWhere('target_membership_id', $payment->membership_id);
            })->exists();
    }

    public static function protectedMembership(Membership $membership): bool
    {
        return Schema::hasTable('membership_payment_credits') && DB::table('membership_payment_credits')
            ->where('tenant_id', $membership->tenant_id)->where(function ($q) use ($membership) {
                $q->where('target_membership_id', $membership->id)
                  ->orWhereIn('source_payment_id', $membership->payments()->select('id'));
            })->exists();
    }

    public static function protectedMember(Member $member): bool
    {
        return Schema::hasTable('membership_payment_credits') && DB::table('membership_payment_credits')
            ->where('tenant_id', $member->tenant_id)->where('member_id', $member->id)->exists();
    }

    public static function apply(Member $member, Membership $target, array $ids, string $mode, int $requested, string $reason, string $requestId): int
    {
        if ($mode === 'none') return 0;
        if (! Schema::hasTable('membership_payment_credits')) {
            throw ValidationException::withMessages(['credit_mode' => 'Credit adjustment is not available until the database migration is applied.']);
        }
        $payments = $member->payments()->where('tenant_id', $member->tenant_id)->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
        if ($payments->count() !== count($ids)) {
            throw ValidationException::withMessages(['credit_ids' => 'One or more receipts are unavailable.']);
        }
        $available = [];
        foreach ($payments as $payment) {
            $payment->load('membership.plan');
            if (DB::table('membership_payment_credits')->where('target_membership_id', $payment->membership_id)->exists()) {
                throw ValidationException::withMessages(['credit_ids' => 'A membership already settled using credit cannot be reused as a credit source.']);
            }
            if (! self::eligible($payment) || $payment->membership_id === $target->id) {
                throw ValidationException::withMessages(['credit_ids' => 'Only fully paid, eligible short-duration membership receipts can be adjusted.']);
            }
            $used = (int) DB::table('membership_payment_credits')->where('source_payment_id', $payment->id)->sum('amount_cents');
            $available[$payment->id] = max(0, self::cents($payment->amount) - $used);
        }
        $total = array_sum($available);
        $credit = $mode === 'full' ? $total : $requested;
        if ($credit <= 0 || $credit > $total || $credit > self::cents($target->final_amount)) {
            throw ValidationException::withMessages(['credit_amount' => 'Adjustment exceeds available credit or membership fee.']);
        }
        $left = $credit;
        foreach ($available as $id => $amount) {
            $allocated = min($left, $amount);
            if ($allocated <= 0) continue;
            DB::table('membership_payment_credits')->insert([
                'tenant_id' => $member->tenant_id,
                'member_id' => $member->id,
                'source_payment_id' => $id,
                'target_membership_id' => $target->id,
                'amount_cents' => $allocated,
                'reason' => $reason,
                'request_id' => $requestId,
                'created_by_user_id' => auth()->id(),
                'created_at' => now(),
            ]);
            $left -= $allocated;
        }
        $target->update(['paid_amount' => number_format($credit / 100, 2, '.', '')]);
        return $credit;
    }
}
