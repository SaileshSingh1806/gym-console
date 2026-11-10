<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\MemberPtPackage;
use App\Models\Membership;
use App\Models\PlatformInvoice;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSuperAdminMetrics(): array
    {
        $totalGyms = Tenant::count();
        $activeGyms = Tenant::where('status', 'ACTIVE')->count();
        $trialGyms = Tenant::where('status', 'TRIAL')->count();
        $suspendedGyms = Tenant::where('status', 'SUSPENDED')->count();
        $cancelledGyms = Tenant::where('status', 'CANCELLED')->count();

        // Calculate SaaS ARR & Invoiced Platform Revenue from active subscriptions of active tenants (deduplicated per tenant)
        $activeSubs = Subscription::with('plan')
            ->where('status', 'ACTIVE')
            ->whereIn('tenant_id', Tenant::where('status', 'ACTIVE')->pluck('id'))
            ->get()
            ->unique('tenant_id');

        $arr = 0.0;
        foreach ($activeSubs as $sub) {
            if ($sub->plan) {
                $arr += (float) ($sub->plan->price_yearly ?: ($sub->plan->price_monthly * 12));
            }
        }

        // Calculate Total Invoiced Platform Revenue from Platform Invoices
        $invoicedRevenue = (float) PlatformInvoice::where('status', 'PAID')->sum('total');
        $totalRevenue = $invoicedRevenue > 0 ? $invoicedRevenue : $arr;

        $newCustomersThisMonth = Tenant::where('created_at', '>=', now()->startOfMonth())->count();

        return [
            'total_gyms' => $totalGyms,
            'active_gyms' => $activeGyms,
            'trial_gyms' => $trialGyms,
            'suspended_gyms' => $suspendedGyms,
            'cancelled_gyms' => $cancelledGyms,
            'arr' => round($arr, 2),
            'mrr' => round($arr / 12, 2),
            'total_revenue' => round($totalRevenue, 2),
            'active_subscriptions_count' => $activeSubs->count(),
            'new_customers_this_month' => $newCustomersThisMonth,
        ];
    }

    public function getTenantDashboardMetrics(?Tenant $tenant = null, ?int $branchId = null): array
    {
        if (! $tenant) {
            $tenant = TenantContext::getTenant() ?? Tenant::first();
        }

        if (! $tenant) {
            return [
                'total_members' => 0,
                'active_members' => 0,
                'inactive_members' => 0,
                'expired_members' => 0,
                'today_revenue' => 0.0,
                'month_revenue' => 0.0,
                'today_expense' => 0.0,
                'month_expense' => 0.0,
                'net_profit' => 0.0,
                'trends' => ['labels' => [], 'revenue' => [], 'prev_revenue' => [], 'joins' => [], 'attendance' => []],
                'chart' => ['labels' => [], 'income' => [], 'expenses' => [], 'profit' => []],
                'expiring_members_list' => [],
                'birthday_members_list' => [],
                'churn_risk_members' => [],
                'leads_followup_list' => [],
                'due_members_list' => [],
                'recent_renewals_list' => [],
                'dormant_members_list' => [],
                'active_pt' => 0,
                'expired_pt' => 0,
                'exhausted_pt' => 0,
                'pt_financials' => [
                    'month_collected' => 0.0,
                    'month_billed' => 0.0,
                    'pending_dues' => 0.0,
                    'total_trainer_commission' => 0.0,
                    'total_gym_profit' => 0.0,
                    'gym_share_percent' => 60.0,
                    'trainer_share_percent' => 40.0,
                    'total_sessions_conducted' => 0,
                    'total_sessions_remaining' => 0,
                    'trainers' => [],
                    'active_packages_count' => 0,
                ],
            ];
        }

        $membersQuery = Member::query();
        $paymentsQuery = MemberPayment::query();
        $expensesQuery = Expense::query();
        $membershipsQuery = Membership::query();
        $attendanceQuery = Attendance::where('date', now()->toDateString());

        if ($branchId) {
            $membersQuery->where('branch_id', $branchId);
            $paymentsQuery->where('branch_id', $branchId);
            $expensesQuery->where('branch_id', $branchId);
            $membershipsQuery->where('branch_id', $branchId);
            $attendanceQuery->where('branch_id', $branchId);
        }

        $totalMembers = (clone $membersQuery)->count();
        $activeMembers = (clone $membersQuery)->where('status', 'ACTIVE')->count();
        $inactiveMembers = (clone $membersQuery)->where('status', 'INACTIVE')->count();
        $expiredMembers = (clone $membersQuery)->where('status', 'EXPIRED')->count();

        $todayRevenue = (float) (clone $paymentsQuery)->whereDate('payment_date', now()->toDateString())->sum('amount');
        $monthRevenue = (float) (clone $paymentsQuery)->where('payment_date', '>=', now()->startOfMonth()->toDateString())->sum('amount');

        $todayExpense = (float) (clone $expensesQuery)->whereDate('expense_date', now()->toDateString())->sum('amount');
        $monthExpense = (float) (clone $expensesQuery)->where('expense_date', '>=', now()->startOfMonth()->toDateString())->sum('amount');
        $netProfit = $monthRevenue - $monthExpense;

        // Last 6 months Income vs Expense trend
        $chartLabels = [];
        $chartIncome = [];
        $chartExpense = [];
        $chartProfit = [];

        for ($i = 5; $i >= 0; $i--) {
            $dt = now()->subMonths($i);
            $start = $dt->copy()->startOfMonth()->toDateString();
            $end = $dt->copy()->endOfMonth()->toDateString();

            $inc = (float) (clone $paymentsQuery)->whereBetween('payment_date', [$start, $end])->sum('amount');
            $exp = (float) (clone $expensesQuery)->whereBetween('expense_date', [$start, $end])->sum('amount');

            $chartLabels[] = $dt->format('M Y');
            $chartIncome[] = round($inc, 2);
            $chartExpense[] = round($exp, 2);
            $chartProfit[] = round($inc - $exp, 2);
        }

        $expiringIn7Days = (clone $membershipsQuery)
            ->where('status', 'ACTIVE')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();

        // Churn risk: active members whose membership expires in <= 7 days and have not checked in for 7+ days (or expiring within 7 days)
        $churnRiskQuery = (clone $membershipsQuery)
            ->with(['member.activeMembership.plan', 'plan'])
            ->where('status', 'ACTIVE')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->whereHas('member', function ($q) {
                $q->whereDoesntHave('attendances', function ($attQ) {
                    $attQ->where('date', '>=', now()->subDays(7)->toDateString());
                });
            });

        $churnRisk = (clone $churnRiskQuery)->count();
        $churnRiskMembersList = (clone $churnRiskQuery)->orderBy('end_date', 'asc')->get();

        if ($churnRiskMembersList->isEmpty()) {
            $churnRiskFallback = (clone $membershipsQuery)
                ->with(['member.activeMembership.plan', 'plan'])
                ->where('status', 'ACTIVE')
                ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                ->orderBy('end_date', 'asc');
            $churnRisk = (clone $churnRiskFallback)->count();
            $churnRiskMembersList = (clone $churnRiskFallback)->get();
        }

        $birthdaysToday = (clone $membersQuery)
            ->whereNotNull('dob')
            ->whereMonth('dob', now()->month)
            ->whereDay('dob', now()->day)
            ->count();

        $dormantMembers = (clone $membersQuery)
            ->where('status', 'ACTIVE')
            ->whereDoesntHave('attendances', function ($q) {
                $q->where('date', '>=', now()->subDays(14)->toDateString());
            })
            ->count();

        // Leads & CRM metrics
        $leadsQuery = Lead::query();
        if ($tenant && $tenant->id) {
            $leadsQuery->where('tenant_id', $tenant->id);
        }
        if ($branchId) {
            $leadsQuery->where('branch_id', $branchId);
        }
        $activeLeads = (clone $leadsQuery)
            ->whereNotIn('status', ['CONVERTED', 'LOST', 'converted', 'lost', 'PAID', 'paid'])
            ->whereNotIn('stage', ['CONVERTED', 'LOST', 'converted', 'lost', 'PAID', 'paid'])
            ->count();
        $todayEnquiries = (clone $leadsQuery)->whereDate('created_at', now()->toDateString())->count();
        $todayFollowups = (clone $leadsQuery)->whereDate('follow_up_date', now()->toDateString())->count();
        $overdueFollowups = (clone $leadsQuery)
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '<', now()->toDateString())
            ->whereNotIn('status', ['CONVERTED', 'LOST', 'converted', 'lost', 'PAID', 'paid'])
            ->whereNotIn('stage', ['CONVERTED', 'LOST', 'converted', 'lost', 'PAID', 'paid'])
            ->count();
        $upcomingTrials = (clone $leadsQuery)
            ->where(function ($q) {
                $q->whereIn('stage', ['TRIAL', 'trial', 'TRIAL_SCHEDULED', 'trial_scheduled'])
                    ->orWhereIn('status', ['TRIAL', 'trial', 'TRIAL_SCHEDULED', 'trial_scheduled'])
                    ->orWhere(function ($tq) {
                        $tq->whereNotNull('trial_date')
                            ->where('trial_date', '>=', now()->toDateString())
                            ->where('trial_status', '!=', 'completed')
                            ->where('trial_status', '!=', 'cancelled');
                    });
            })
            ->count();

        // PT (Personal Training) metrics
        $ptPackagesQuery = MemberPtPackage::query();
        if ($tenant && $tenant->id) {
            $ptPackagesQuery->where('tenant_id', $tenant->id);
        }
        if ($branchId) {
            $ptPackagesQuery->where('branch_id', $branchId);
        }

        $activePt = (clone $ptPackagesQuery)
            ->where('status', 'ACTIVE')
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('total_sessions')
                    ->orWhereRaw('used_sessions < total_sessions');
            })
            ->count();

        $expiredPt = (clone $ptPackagesQuery)
            ->where(function ($q) {
                $q->where('status', 'EXPIRED')
                    ->orWhere(function ($sub) {
                        $sub->where('status', 'ACTIVE')
                            ->whereNotNull('end_date')
                            ->where('end_date', '<', now()->toDateString());
                    });
            })
            ->count();

        $exhaustedPt = (clone $ptPackagesQuery)
            ->where(function ($q) {
                $q->whereIn('status', ['COMPLETED', 'EXHAUSTED'])
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('total_sessions')
                            ->whereRaw('used_sessions >= total_sessions');
                    });
            })
            ->count();

        // Detail lists for interactive widgets and modals
        $expiringMembersList = (clone $membershipsQuery)
            ->with(['member', 'plan'])
            ->where('status', 'ACTIVE')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->orderBy('end_date', 'asc')
            ->take(8)
            ->get();

        $recentlyExpiredList = (clone $membershipsQuery)
            ->with(['member', 'plan'])
            ->where(function ($q) {
                $q->where('status', 'EXPIRED')
                    ->orWhere('end_date', '<', now()->toDateString());
            })
            ->latest('end_date')
            ->take(8)
            ->get();

        $birthdayMembersList = (clone $membersQuery)
            ->whereNotNull('dob')
            ->whereMonth('dob', now()->month)
            ->whereDay('dob', now()->day)
            ->take(8)
            ->get();

        $recentMembersList = (clone $membersQuery)
            ->with(['activeMembership.plan'])
            ->latest('created_at')
            ->take(6)
            ->get();

        $leadFollowupsList = (clone $leadsQuery)
            ->whereNotNull('follow_up_date')
            ->orderBy('follow_up_date', 'asc')
            ->take(6)
            ->get();

        // Due Members List with calculated remaining due balance
        $dueMembersList = (clone $membershipsQuery)
            ->with(['member', 'plan', 'payments'])
            ->where('status', 'ACTIVE')
            ->whereRaw('COALESCE(final_amount, price) > COALESCE(paid_amount, 0)')
            ->latest('created_at')
            ->take(8)
            ->get()
            ->map(function ($m) {
                $pkgPrice = (float) ($m->final_amount ?: $m->price);
                $paid = (float) ($m->paid_amount ?: $m->payments->sum('amount'));
                $m->due_amount = max(0, $pkgPrice - $paid);

                return $m;
            })
            ->filter(fn ($m) => $m->due_amount > 0)
            ->values();

        // Recent Renewals: memberships renewed this month for existing members
        $recentRenewalsList = (clone $membershipsQuery)
            ->with(['member', 'plan'])
            ->where('created_at', '>=', now()->startOfMonth())
            ->whereHas('member', function ($q) {
                $q->where('created_at', '<', now()->startOfMonth());
            })
            ->latest('created_at')
            ->take(8)
            ->get();

        // Dormant Members List (14+ days absent)
        $dormantMembersList = (clone $membersQuery)
            ->where('status', 'ACTIVE')
            ->whereDoesntHave('attendances', function ($q) {
                $q->where('date', '>=', now()->subDays(14)->toDateString());
            })
            ->with(['activeMembership.plan'])
            ->take(8)
            ->get();

        // Financial & Dues Calculations
        $allDuesTotal = (float) (clone $membershipsQuery)->where('status', 'ACTIVE')->sum('price');
        $allPaymentsSum = (float) (clone $paymentsQuery)->sum('amount');
        $allDuesRemaining = max(0, $allDuesTotal - $allPaymentsSum);
        $dueMembersCount = (clone $membershipsQuery)->where('status', 'ACTIVE')->count();
        $monthBilledTotal = (float) (clone $membershipsQuery)
            ->where(function ($q) {
                $q->where('created_at', '>=', now()->startOfMonth())
                    ->orWhere('start_date', '>=', now()->startOfMonth()->toDateString());
            })
            ->sum(DB::raw('COALESCE(final_amount, price)'));

        $activeMemberships = (clone $membershipsQuery)->with('payments')->where('status', 'ACTIVE')->get();
        $totalActivePackageValue = 0.0;
        $allDuesRemaining = 0.0;
        $dueMembersCount = 0;

        foreach ($activeMemberships as $m) {
            $pkgPrice = (float) ($m->final_amount ?: $m->price);
            $paid = (float) ($m->paid_amount ?: $m->payments->sum('amount'));
            $due = max(0, $pkgPrice - $paid);

            $totalActivePackageValue += $pkgPrice;
            $allDuesRemaining += $due;
            if ($due > 0) {
                $dueMembersCount++;
            }
        }

        if ($monthBilledTotal <= 0) {
            $monthBilledTotal = $totalActivePackageValue;
        }
        $monthBilledTotal = max($monthBilledTotal, $monthRevenue + $allDuesRemaining);

        // New Clients: registered this month
        $newClientsCount = (clone $membersQuery)->where('created_at', '>=', now()->startOfMonth())->count();
        $renewalsCount = (clone $membershipsQuery)->where('created_at', '>=', now()->startOfMonth())->where('created_at', '>', now()->subMonths(1))->count();

        // Renewals: Memberships created/renewed this month for members who joined before this month (or have prior memberships)
        $renewalsCount = (clone $membershipsQuery)
            ->where('created_at', '>=', now()->startOfMonth())
            ->whereHas('member', function ($q) {
                $q->where('created_at', '<', now()->startOfMonth());
            })
            ->count();

        $todayAttendanceCount = (clone $attendanceQuery)->count();
        $currentlyInside = (clone $attendanceQuery)->whereNull('check_out')->count();

        $recentPayments = (clone $paymentsQuery)
            ->with(['member', 'receivedBy', 'membership.plan'])
            ->latest('payment_date')
            ->take(6)
            ->get();

        $recentAttendance = (clone $attendanceQuery)
            ->with(['member', 'device'])
            ->latest('check_in')
            ->take(6)
            ->get();

        // Recent daily trends for the 4 dashboard charts
        $trendLabels = [];
        $trendRevenue = [];
        $trendPrevRevenue = [];
        $trendJoins = [];
        $trendAttendance = [];

        for ($i = 5; $i >= 0; $i--) {
            $dayDate = now()->subDays($i)->toDateString();
            $dayLabel = now()->subDays($i)->format('d M');
            $prevDayDate = now()->subDays($i + 7)->toDateString();

            $dayRev = (float) (clone $paymentsQuery)->whereDate('payment_date', $dayDate)->sum('amount');
            $prevDayRev = (float) (clone $paymentsQuery)->whereDate('payment_date', $prevDayDate)->sum('amount');
            $dayJoins = (clone $membersQuery)->whereDate('created_at', $dayDate)->count();
            $dayAtt = (clone $attendanceQuery)->whereDate('date', $dayDate)->count();

            $trendLabels[] = $dayLabel;
            $trendRevenue[] = $dayRev;
            $trendPrevRevenue[] = $prevDayRev;
            $trendJoins[] = $dayJoins;
            $trendAttendance[] = $dayAtt;
        }

        // ----------------------------------------------------
        // Monthly Personal Training (PT) Financial Overview
        // ----------------------------------------------------
        $selectedPtMonth = request('pt_month');
        if ($selectedPtMonth && preg_match('/^\d{4}-\d{2}$/', $selectedPtMonth)) {
            $monthCarbon = \Carbon\Carbon::createFromFormat('Y-m', $selectedPtMonth)->startOfMonth();
            $monthStart = $monthCarbon->copy()->startOfMonth()->toDateString();
            $monthEnd = $monthCarbon->copy()->endOfMonth()->toDateString();
            $isCurrentMonth = $monthCarbon->isCurrentMonth();
        } else {
            $monthCarbon = now()->startOfMonth();
            $monthStart = $monthCarbon->copy()->startOfMonth()->toDateString();
            $monthEnd = $monthCarbon->copy()->endOfMonth()->toDateString();
            $isCurrentMonth = true;
        }

        $availableMonths = [];
        for ($i = 0; $i < 12; $i++) {
            $c = now()->subMonths($i);
            $key = $c->format('Y-m');
            $label = $i === 0 ? 'This Month (' . $c->format('M Y') . ')' : ($i === 1 ? 'Last Month (' . $c->format('M Y') . ')' : $c->format('M Y'));
            $availableMonths[$key] = $label;
        }

        $monthPtPaymentsQuery = (clone $paymentsQuery)
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->where('notes', 'like', '%PT Package%');

        $monthPtCollectedFromPayments = (float) (clone $monthPtPaymentsQuery)->sum('amount');

        $allActiveOrRecentPtQuery = (clone $ptPackagesQuery)
            ->with(['trainer', 'ptPlan', 'member']);

        if ($isCurrentMonth) {
            $allActiveOrRecentPtQuery->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('created_at', [$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59'])
                  ->orWhere('status', 'ACTIVE');
            });
        } else {
            $allActiveOrRecentPtQuery->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('created_at', [$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59'])
                  ->orWhereBetween('start_date', [$monthStart, $monthEnd]);
            });
        }

        $allActiveOrRecentPt = $allActiveOrRecentPtQuery->get();

        $selectedMonthPackages = $allActiveOrRecentPt->filter(function ($pkg) use ($monthStart, $monthEnd, $isCurrentMonth) {
            $created = $pkg->created_at ? $pkg->created_at->toDateString() : null;
            if ($created && $created >= $monthStart && $created <= $monthEnd) {
                return true;
            }
            if ($pkg->start_date && $pkg->start_date >= $monthStart && $pkg->start_date <= $monthEnd) {
                return true;
            }
            return $isCurrentMonth && $pkg->status === 'ACTIVE';
        });

        $monthPtCollectedFromPackages = (float) $selectedMonthPackages->sum('paid_amount');
        $monthPtTotalCollected = max($monthPtCollectedFromPackages, $monthPtCollectedFromPayments);
        $monthPtTotalBilled = (float) $selectedMonthPackages->sum('final_amount');
        $monthPtPendingDues = max(0, $monthPtTotalBilled - $monthPtTotalCollected);

        $trainersPtBreakdown = [];
        $totalTrainerCommission = 0.0;
        $totalGymPtProfit = 0.0;
        $totalSessionsConducted = 0;
        $totalSessionsRemaining = 0;

        foreach ($selectedMonthPackages as $pkg) {
            $trainer = $pkg->trainer;
            $trainerId = $trainer ? $trainer->id : 0;
            $trainerName = $trainer ? $trainer->full_name : 'Unassigned Trainer';
            $trainerSpecialization = $trainer?->specialization ?? 'Personal Trainer';

            $commPercent = (float) (
                $pkg->trainer_commission_percent
                ?? $trainer?->commission_percent
                ?? $pkg->ptPlan?->trainer_commission_percent
                ?? 40.0
            );
            $paidAmt = (float) $pkg->paid_amount;

            $trainerCommission = round(($paidAmt * $commPercent) / 100, 2);
            $gymProfit = round($paidAmt - $trainerCommission, 2);

            $sessionsDone = (int) $pkg->used_sessions;
            $sessionsLeft = max(0, (int) ($pkg->total_sessions - $pkg->used_sessions));

            $totalTrainerCommission += $trainerCommission;
            $totalGymPtProfit += $gymProfit;
            $totalSessionsConducted += $sessionsDone;
            $totalSessionsRemaining += $sessionsLeft;

            if (!isset($trainersPtBreakdown[$trainerId])) {
                $trainersPtBreakdown[$trainerId] = [
                    'id' => $trainerId,
                    'name' => $trainerName,
                    'specialization' => $trainerSpecialization,
                    'photo_path' => $trainer?->photo_path,
                    'active_clients' => 0,
                    'total_collected' => 0.0,
                    'trainer_commission' => 0.0,
                    'gym_profit' => 0.0,
                    'commission_percent' => $commPercent,
                    'sessions_conducted' => 0,
                    'sessions_remaining' => 0,
                ];
            }

            if ($pkg->status === 'ACTIVE') {
                $trainersPtBreakdown[$trainerId]['active_clients']++;
            }
            $trainersPtBreakdown[$trainerId]['total_collected'] += $paidAmt;
            $trainersPtBreakdown[$trainerId]['trainer_commission'] += $trainerCommission;
            $trainersPtBreakdown[$trainerId]['gym_profit'] += $gymProfit;
            $trainersPtBreakdown[$trainerId]['sessions_conducted'] += $sessionsDone;
            $trainersPtBreakdown[$trainerId]['sessions_remaining'] += $sessionsLeft;
        }

        uasort($trainersPtBreakdown, fn($a, $b) => $b['total_collected'] <=> $a['total_collected']);

        $gymSharePercent = $monthPtTotalCollected > 0 ? round(($totalGymPtProfit / max(1, $monthPtTotalCollected)) * 100, 1) : 60.0;
        $trainerSharePercent = round(100.0 - $gymSharePercent, 1);

        $ptFinancials = [
            'month_collected' => $monthPtTotalCollected,
            'month_billed' => $monthPtTotalBilled,
            'pending_dues' => $monthPtPendingDues,
            'total_trainer_commission' => $totalTrainerCommission,
            'total_gym_profit' => $totalGymPtProfit,
            'gym_share_percent' => $gymSharePercent,
            'trainer_share_percent' => $trainerSharePercent,
            'total_sessions_conducted' => $totalSessionsConducted,
            'total_sessions_remaining' => $totalSessionsRemaining,
            'trainers' => array_values($trainersPtBreakdown),
            'active_packages_count' => $selectedMonthPackages->count(),
            'selected_month' => $monthCarbon->format('Y-m'),
            'selected_month_label' => $monthCarbon->format('M Y'),
            'available_months' => $availableMonths,
            'is_current_month' => $isCurrentMonth,
        ];

        return [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'inactive_members' => $inactiveMembers,
            'expired_members' => $expiredMembers,
            'churn_risk' => $churnRisk,
            'churn_risk_members' => $churnRiskMembersList,
            'churn_risk_list' => $churnRiskMembersList,
            'birthdays_today' => $birthdaysToday,
            'dormant_members' => $dormantMembers,
            'active_pt' => $activePt,
            'expired_pt' => $expiredPt,
            'exhausted_pt' => $exhaustedPt,
            'pt_financials' => $ptFinancials,
            'active_leads' => $activeLeads,
            'today_enquiries' => $todayEnquiries,
            'today_followups' => $todayFollowups,
            'overdue_followups' => $overdueFollowups,
            'upcoming_trials' => $upcomingTrials,
            'today_revenue' => $todayRevenue,
            'month_revenue' => $monthRevenue,
            'month_billed_total' => $monthBilledTotal,
            'total_billed_amount' => $totalActivePackageValue,
            'total_billed_count' => $activeMemberships->count(),
            'total_collected_amount' => $allPaymentsSum,
            'total_payments_count' => (clone $paymentsQuery)->count(),
            'today_expense' => $todayExpense,
            'month_expense' => $monthExpense,
            'net_profit' => $netProfit,
            'expiring_soon' => $expiringIn7Days,
            'today_attendance' => $todayAttendanceCount,
            'currently_inside' => $currentlyInside,
            'recent_payments' => $recentPayments,
            'recent_attendance' => $recentAttendance,
            'expiring_members_list' => $expiringMembersList,
            'recently_expired_list' => $recentlyExpiredList,
            'birthday_members_list' => $birthdayMembersList,
            'recent_members_list' => $recentMembersList,
            'lead_followups_list' => $leadFollowupsList,
            'due_members_list' => $dueMembersList,
            'recent_renewals_list' => $recentRenewalsList,
            'dormant_members_list' => $dormantMembersList,
            'all_dues_remaining' => $allDuesRemaining,
            'due_members_count' => $dueMembersCount,
            'new_clients_count' => $newClientsCount,
            'renewals_count' => $renewalsCount,
            'trends' => [
                'labels' => $trendLabels,
                'revenue' => $trendRevenue,
                'prev_revenue' => $trendPrevRevenue,
                'joins' => $trendJoins,
                'attendance' => $trendAttendance,
            ],
            'chart' => [
                'labels' => $chartLabels,
                'income' => $chartIncome,
                'expenses' => $chartExpense,
                'profit' => $chartProfit,
            ],
        ];
    }
}
