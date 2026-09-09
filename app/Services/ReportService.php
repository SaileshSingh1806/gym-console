<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\Membership;
use App\Models\PlatformInvoice;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Trainer;

class ReportService
{
    public function getSuperAdminMetrics(): array
    {
        $totalGyms = Tenant::count();
        $activeGyms = Tenant::where('status', 'ACTIVE')->count();
        $trialGyms = Tenant::where('status', 'TRIAL')->count();
        $suspendedGyms = Tenant::where('status', 'SUSPENDED')->count();
        $cancelledGyms = Tenant::where('status', 'CANCELLED')->count();

        // Calculate SaaS MRR from active subscriptions
        $activeSubs = Subscription::with('plan')->where('status', 'ACTIVE')->get();
        $mrr = 0.0;
        foreach ($activeSubs as $sub) {
            if ($sub->plan) {
                $mrr += $sub->billing_cycle === 'yearly'
                    ? ((float) $sub->plan->price_yearly / 12)
                    : (float) $sub->plan->price_monthly;
            }
        }

        $totalRevenue = (float) PlatformInvoice::where('status', 'PAID')->sum('total');

        $newCustomersThisMonth = Tenant::where('created_at', '>=', now()->startOfMonth())->count();

        return [
            'total_gyms' => $totalGyms,
            'active_gyms' => $activeGyms,
            'trial_gyms' => $trialGyms,
            'suspended_gyms' => $suspendedGyms,
            'cancelled_gyms' => $cancelledGyms,
            'mrr' => round($mrr, 2),
            'total_revenue' => round($totalRevenue, 2),
            'new_customers_this_month' => $newCustomersThisMonth,
        ];
    }

    public function getTenantDashboardMetrics(Tenant $tenant, ?int $branchId = null): array
    {
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

        $churnRisk = (clone $membershipsQuery)
            ->where('status', 'ACTIVE')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(3)->toDateString()])
            ->count();

        $birthdaysToday = (clone $membersQuery)
            ->whereNotNull('dob')
            ->whereMonth('dob', now()->month)
            ->whereDay('dob', now()->day)
            ->count();

        $dormantMembers = (clone $membersQuery)
            ->whereIn('status', ['INACTIVE', 'FROZEN'])
            ->count();

        // Leads & CRM metrics
        $leadsQuery = Lead::query();
        if ($branchId) {
            $leadsQuery->where('branch_id', $branchId);
        }
        $activeLeads = (clone $leadsQuery)->whereNotIn('status', ['CONVERTED', 'LOST', 'converted', 'lost'])->count();
        $todayEnquiries = (clone $leadsQuery)->whereDate('created_at', now()->toDateString())->count();
        $todayFollowups = (clone $leadsQuery)->whereDate('follow_up_date', now()->toDateString())->count();
        $overdueFollowups = (clone $leadsQuery)
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '<', now()->toDateString())
            ->whereNotIn('status', ['CONVERTED', 'LOST', 'converted', 'lost'])
            ->count();
        $upcomingTrials = (clone $leadsQuery)->whereIn('status', ['TRIAL', 'trial', 'TRIAL_SCHEDULED'])->count();

        // PT (Personal Training) metrics
        $trainersQuery = Trainer::query();
        if ($branchId) {
            $trainersQuery->where('branch_id', $branchId);
        }
        $activePt = (clone $trainersQuery)->where('status', 'ACTIVE')->count();
        $expiredPt = (clone $trainersQuery)->where('status', 'INACTIVE')->count();
        $exhaustedPt = 0;

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

        // Financial & Dues Calculations
        $allDuesTotal = (float) (clone $membershipsQuery)->where('status', 'ACTIVE')->sum('price');
        $allPaymentsSum = (float) (clone $paymentsQuery)->sum('amount');
        $allDuesRemaining = max(0, $allDuesTotal - $allPaymentsSum);
        $dueMembersCount = (clone $membershipsQuery)->where('status', 'ACTIVE')->count();

        $newClientsCount = (clone $membersQuery)->where('created_at', '>=', now()->startOfMonth())->count();
        $renewalsCount = (clone $membershipsQuery)->where('created_at', '>=', now()->startOfMonth())->where('created_at', '>', now()->subMonths(1))->count();

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

        return [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'inactive_members' => $inactiveMembers,
            'expired_members' => $expiredMembers,
            'churn_risk' => $churnRisk,
            'birthdays_today' => $birthdaysToday,
            'dormant_members' => $dormantMembers,
            'active_pt' => $activePt,
            'expired_pt' => $expiredPt,
            'exhausted_pt' => $exhaustedPt,
            'active_leads' => $activeLeads,
            'today_enquiries' => $todayEnquiries,
            'today_followups' => $todayFollowups,
            'overdue_followups' => $overdueFollowups,
            'upcoming_trials' => $upcomingTrials,
            'today_revenue' => $todayRevenue,
            'month_revenue' => $monthRevenue,
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
