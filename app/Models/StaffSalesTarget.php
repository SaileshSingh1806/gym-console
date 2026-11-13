<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffSalesTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'period_type',
        'period_key',
        'start_date',
        'end_date',
        'target_revenue',
        'target_admissions',
        'created_by_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'target_revenue' => 'decimal:2',
            'target_admissions' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Compute current achievement metrics for this target.
     *
     * @return array{
     *     target_revenue: float,
     *     achieved_revenue: float,
     *     remaining_revenue: float,
     *     revenue_pct: float,
     *     target_admissions: int,
     *     achieved_admissions: int,
     *     remaining_admissions: int,
     *     admissions_pct: float,
     *     days_total: int,
     *     days_left: int,
     *     pace_daily_needed: float,
     *     status: string
     * }
     */
    public function calculateProgress(): array
    {
        $start = Carbon::parse($this->start_date)->startOfDay();
        $end = Carbon::parse($this->end_date)->endOfDay();
        $today = now();

        // 1. Leads assigned to this staff converted in this period
        $leadsQuery = Lead::where('tenant_id', $this->tenant_id)
            ->where('assigned_to_user_id', $this->user_id)
            ->whereBetween('updated_at', [$start, $end])
            ->whereIn('stage', ['PAID', 'CONVERTED']);

        $achievedAdmissions = (int) $leadsQuery->count();
        $leadRevenue = (float) $leadsQuery->sum('estimated_value');

        // 2. Payments collected by this staff in this period (fallback or additive verification)
        $paymentRevenue = (float) MemberPayment::where('tenant_id', $this->tenant_id)
            ->where('received_by_user_id', $this->user_id)
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');

        // Total achieved revenue (use maximum of leads deal value or payments collected)
        $achievedRevenue = max($leadRevenue, $paymentRevenue);

        $targetRevenue = (float) $this->target_revenue;
        $targetAdmissions = (int) $this->target_admissions;

        $remainingRevenue = max(0, $targetRevenue - $achievedRevenue);
        $remainingAdmissions = max(0, $targetAdmissions - $achievedAdmissions);

        $revenuePct = $targetRevenue > 0 ? min(100, round(($achievedRevenue / $targetRevenue) * 100, 1)) : 0;
        $admissionsPct = $targetAdmissions > 0 ? min(100, round(($achievedAdmissions / $targetAdmissions) * 100, 1)) : 0;

        $daysTotal = max(1, $start->diffInDays($end) + 1);
        $daysLeft = $today->gt($end) ? 0 : max(0, (int) $today->diffInDays($end, false) + 1);

        $dailyNeeded = ($daysLeft > 0 && $remainingRevenue > 0) ? round($remainingRevenue / $daysLeft, 2) : 0;

        if ($revenuePct >= 100 || ($targetAdmissions > 0 && $admissionsPct >= 100)) {
            $status = 'ACHIEVED';
        } elseif ($daysLeft <= 0) {
            $status = 'EXPIRED';
        } elseif ($revenuePct >= 50) {
            $status = 'ON_TRACK';
        } else {
            $status = 'NEEDS_ATTENTION';
        }

        return [
            'target_revenue' => $targetRevenue,
            'achieved_revenue' => $achievedRevenue,
            'remaining_revenue' => $remainingRevenue,
            'revenue_pct' => $revenuePct,
            'target_admissions' => $targetAdmissions,
            'achieved_admissions' => $achievedAdmissions,
            'remaining_admissions' => $remainingAdmissions,
            'admissions_pct' => $admissionsPct,
            'days_total' => $daysTotal,
            'days_left' => $daysLeft,
            'pace_daily_needed' => $dailyNeeded,
            'status' => $status,
        ];
    }
}

