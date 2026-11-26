<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payslip;
use App\Models\Tenant;
use App\Models\Trainer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Generate unique sequential payslip number for a tenant and month (e.g. PAY-202611-001).
     */
    public function generatePayslipNumber(Tenant $tenant, int $year, int $month): string
    {
        $prefix = sprintf('PAY-%04d%02d-', $year, $month);

        $lastPayslip = Payslip::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('payslip_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if ($lastPayslip) {
            $lastSequence = (int) substr($lastPayslip->payslip_number, strlen($prefix));
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix . sprintf('%03d', $nextSequence);
    }

    /**
     * Get all eligible active staff and trainers for a tenant.
     */
    public function getEligibleStaff(Tenant $tenant, ?int $branchId = null): Collection
    {
        $query = User::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with(['branches', 'trainerProfile']);

        if ($branchId) {
            $query->whereHas('branches', fn($b) => $b->where('branches.id', $branchId));
        }

        return $query->get()->filter(function (User $user) {
            // Exclude platform super admins or external entities
            return ! $user->isSuperAdmin();
        });
    }

    /**
     * Extract default salary configuration from staff metadata or trainer profile.
     */
    public function getStaffSalaryConfig(User $user): array
    {
        $meta = (array) ($user->metadata ?? []);
        $trainer = $user->trainerProfile ?? Trainer::where('tenant_id', $user->tenant_id)->where('user_id', $user->id)->first();

        $baseSalary = 0.00;
        if (! empty($meta['monthly_salary']) && is_numeric($meta['monthly_salary'])) {
            $baseSalary = (float) $meta['monthly_salary'];
        } elseif ($trainer && is_numeric($trainer->salary)) {
            $baseSalary = (float) $trainer->salary;
        }

        return [
            'base_salary' => $baseSalary,
            'payout_type' => $meta['payout_type'] ?? ($trainer?->salary_type ?? 'Fixed Monthly'),
            'bank_name' => $meta['bank_name'] ?? null,
            'account_number' => $meta['account_number'] ?? ($meta['bank_account_no'] ?? null),
            'ifsc_code' => $meta['ifsc_code'] ?? ($meta['bank_ifsc'] ?? null),
            'upi_id' => $meta['upi_id'] ?? null,
            'designation' => $meta['designation'] ?? ($user->role ? ucwords(str_replace('_', ' ', $user->role)) : 'Staff Member'),
        ];
    }

    /**
     * Create or update a single staff payslip.
     */
    public function createOrUpdatePayslip(Tenant $tenant, User $staff, int $month, int $year, array $data, ?User $creator = null): Payslip
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = (clone $startDate)->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $salaryConfig = $this->getStaffSalaryConfig($staff);

        $basicSalary = isset($data['basic_salary']) ? (float) $data['basic_salary'] : $salaryConfig['base_salary'];
        $allowances = (array) ($data['allowances'] ?? []);
        $deductions = (array) ($data['deductions'] ?? []);

        // Filter empty entries
        $cleanAllowances = [];
        $totalAllowances = 0.00;
        foreach ($allowances as $item) {
            if (! empty($item['name']) && isset($item['amount']) && is_numeric($item['amount']) && (float) $item['amount'] > 0) {
                $amt = round((float) $item['amount'], 2);
                $cleanAllowances[] = ['name' => trim($item['name']), 'amount' => $amt];
                $totalAllowances += $amt;
            }
        }

        $cleanDeductions = [];
        $totalDeductions = 0.00;
        foreach ($deductions as $item) {
            if (! empty($item['name']) && isset($item['amount']) && is_numeric($item['amount']) && (float) $item['amount'] > 0) {
                $amt = round((float) $item['amount'], 2);
                $cleanDeductions[] = ['name' => trim($item['name']), 'amount' => $amt];
                $totalDeductions += $amt;
            }
        }

        $grossSalary = round($basicSalary + $totalAllowances, 2);
        $netSalary = max(0.00, round($grossSalary - $totalDeductions, 2));

        $workingDays = isset($data['working_days']) ? (int) $data['working_days'] : $daysInMonth;
        $leavesTaken = isset($data['leaves_taken']) ? (int) $data['leaves_taken'] : 0;
        $presentDays = max(0, $workingDays - $leavesTaken);

        $payslip = Payslip::where('tenant_id', $tenant->id)
            ->where('user_id', $staff->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (! $payslip) {
            $payslip = new Payslip();
            $payslip->tenant_id = $tenant->id;
            $payslip->user_id = $staff->id;
            $payslip->month = $month;
            $payslip->year = $year;
            $payslip->payslip_number = $this->generatePayslipNumber($tenant, $year, $month);
            $payslip->created_by = $creator?->id;
            $payslip->status = Payslip::STATUS_DRAFT;
        }

        $payslip->branch_id = $data['branch_id'] ?? $staff->branches->first()?->id ?? null;
        $payslip->pay_period_start = $startDate->toDateString();
        $payslip->pay_period_end = $endDate->toDateString();
        $payslip->basic_salary = $basicSalary;
        $payslip->allowances = $cleanAllowances;
        $payslip->deductions = $cleanDeductions;
        $payslip->gross_salary = $grossSalary;
        $payslip->total_deductions = $totalDeductions;
        $payslip->net_salary = $netSalary;
        $payslip->working_days = $workingDays;
        $payslip->present_days = $presentDays;
        $payslip->leaves_taken = $leavesTaken;
        $payslip->late_days = isset($data['late_days']) ? (int) $data['late_days'] : 0;

        // Bank / UPI details
        $payslip->bank_name = $data['bank_name'] ?? $salaryConfig['bank_name'];
        $payslip->account_number = $data['account_number'] ?? $salaryConfig['account_number'];
        $payslip->ifsc_code = $data['ifsc_code'] ?? $salaryConfig['ifsc_code'];
        $payslip->upi_id = $data['upi_id'] ?? $salaryConfig['upi_id'];
        $payslip->notes = $data['notes'] ?? $payslip->notes;

        if (! empty($data['status'])) {
            $payslip->status = strtoupper((string) $data['status']);
        }

        $payslip->save();

        // Persist bank & UPI details to staff profile for future monthly runs
        if (! empty($data['bank_name']) || ! empty($data['account_number']) || ! empty($data['ifsc_code']) || ! empty($data['upi_id'])) {
            $currentMeta = (array) ($staff->metadata ?? []);
            if (! empty($data['bank_name'])) $currentMeta['bank_name'] = trim($data['bank_name']);
            if (! empty($data['account_number'])) {
                $currentMeta['account_number'] = trim($data['account_number']);
                $currentMeta['bank_account_no'] = trim($data['account_number']);
            }
            if (! empty($data['ifsc_code'])) {
                $currentMeta['ifsc_code'] = strtoupper(trim($data['ifsc_code']));
                $currentMeta['bank_ifsc'] = strtoupper(trim($data['ifsc_code']));
            }
            if (! empty($data['upi_id'])) $currentMeta['upi_id'] = trim($data['upi_id']);
            $staff->metadata = $currentMeta;
            $staff->saveQuietly();
        }

        return $payslip;
    }

    /**
     * One-click Bulk Run to generate drafts for all eligible staff for a specific month.
     */
    public function bulkGeneratePayroll(Tenant $tenant, int $month, int $year, ?int $branchId = null, ?User $creator = null): array
    {
        $staffMembers = $this->getEligibleStaff($tenant, $branchId);
        $created = 0;
        $skipped = 0;

        foreach ($staffMembers as $staff) {
            $existing = Payslip::where('tenant_id', $tenant->id)
                ->where('user_id', $staff->id)
                ->where('month', $month)
                ->where('year', $year)
                ->exists();

            if ($existing) {
                $skipped++;
                continue;
            }

            $salaryConfig = $this->getStaffSalaryConfig($staff);

            $this->createOrUpdatePayslip($tenant, $staff, $month, $year, [
                'basic_salary' => $salaryConfig['base_salary'],
                'allowances' => [],
                'deductions' => [],
                'branch_id' => $branchId,
                'status' => Payslip::STATUS_DRAFT,
            ], $creator);

            $created++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'total_staff' => $staffMembers->count(),
        ];
    }

    /**
     * Mark a payslip as Paid and automatically record it in Gym's Expenses.
     */
    public function markAsPaid(Payslip $payslip, array $paymentData, User $paidByUser, bool $recordExpense = true): Payslip
    {
        return DB::transaction(function () use ($payslip, $paymentData, $paidByUser, $recordExpense) {
            $payslip->status = Payslip::STATUS_PAID;
            $payslip->paid_at = Carbon::parse($paymentData['paid_at'] ?? now());
            $payslip->payment_method = strtoupper((string) ($paymentData['payment_method'] ?? Payslip::METHOD_UPI));
            $payslip->transaction_reference = $paymentData['transaction_reference'] ?? null;
            if (! empty($paymentData['notes'])) {
                $payslip->notes = $paymentData['notes'];
            }

            // Sync with Gym Expenses if enabled and not already linked
            if ($recordExpense && ! $payslip->expense_id && $payslip->net_salary > 0) {
                // Find or create 'Trainer & Staff Salaries' expense category for this tenant
                $category = ExpenseCategory::firstOrCreate(
                    [
                        'tenant_id' => $payslip->tenant_id,
                        'name' => 'Trainer & Staff Salaries',
                    ],
                    [
                        'description' => 'Monthly staff, trainer and employee payroll payouts',
                        'is_active' => true,
                    ]
                );

                $staffName = $payslip->user?->name ?? 'Staff';
                $period = $payslip->formatted_period;

                $expense = Expense::create([
                    'tenant_id' => $payslip->tenant_id,
                    'branch_id' => $payslip->branch_id,
                    'expense_category_id' => $category->id,
                    'title' => "Salary - {$staffName} ({$period})",
                    'amount' => $payslip->net_salary,
                    'expense_date' => $payslip->paid_at->toDateString(),
                    'payment_method' => $payslip->payment_method,
                    'notes' => "Payslip: {$payslip->payslip_number}" . ($payslip->transaction_reference ? " | Ref: {$payslip->transaction_reference}" : ''),
                ]);

                $payslip->expense_id = $expense->id;
            }

            $payslip->save();

            return $payslip;
        });
    }

    /**
     * Calculate monthly overview metrics for Gym Owner dashboard.
     */
    public function getMonthlyMetrics(Tenant $tenant, int $month, int $year, ?int $branchId = null): array
    {
        $query = Payslip::where('tenant_id', $tenant->id)
            ->where('month', $month)
            ->where('year', $year);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $payslips = $query->get();

        $totalPayroll = (float) $payslips->where('status', '!=', Payslip::STATUS_CANCELLED)->sum('net_salary');
        $paidAmount = (float) $payslips->where('status', Payslip::STATUS_PAID)->sum('net_salary');
        $pendingAmount = max(0.00, $totalPayroll - $paidAmount);

        $paidCount = $payslips->where('status', Payslip::STATUS_PAID)->count();
        $pendingCount = $payslips->whereIn('status', [Payslip::STATUS_DRAFT, Payslip::STATUS_APPROVED])->count();
        $totalCount = $payslips->count();

        $eligibleStaffCount = $this->getEligibleStaff($tenant, $branchId)->count();
        $unprocessedStaffCount = max(0, $eligibleStaffCount - $totalCount);

        return [
            'total_payroll' => $totalPayroll,
            'paid_amount' => $paidAmount,
            'pending_amount' => $pendingAmount,
            'paid_count' => $paidCount,
            'pending_count' => $pendingCount,
            'total_count' => $totalCount,
            'eligible_staff_count' => $eligibleStaffCount,
            'unprocessed_staff_count' => $unprocessedStaffCount,
        ];
    }
}
