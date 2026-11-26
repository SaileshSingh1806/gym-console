<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Models\User;
use App\Services\PayrollService;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function __construct(
        protected PayrollService $payrollService
    ) {}

    /**
     * Display Payroll & Salary overview dashboard.
     */
    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $this->ensureAuthorized();

        $selectedMonth = (int) $request->input('month', now()->month);
        $selectedYear = (int) $request->input('year', now()->year);
        $statusFilter = $request->input('status', 'all');
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;

        $query = Payslip::where('tenant_id', $tenant->id)
            ->where('month', $selectedMonth)
            ->where('year', $selectedYear)
            ->with(['user.branches', 'user.trainerProfile', 'branch', 'expense']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($statusFilter === 'paid') {
            $query->where('status', Payslip::STATUS_PAID);
        } elseif ($statusFilter === 'pending') {
            $query->whereIn('status', [Payslip::STATUS_DRAFT, Payslip::STATUS_APPROVED]);
        } elseif ($statusFilter === 'cancelled') {
            $query->where('status', Payslip::STATUS_CANCELLED);
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('payslip_number', 'like', "%{$search}%")
                    ->orWhere('transaction_reference', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $payslips = $query->latest('id')->paginate(20)->withQueryString();

        $metrics = $this->payrollService->getMonthlyMetrics($tenant, $selectedMonth, $selectedYear, $branchId);
        $eligibleStaff = $this->payrollService->getEligibleStaff($tenant, $branchId);

        // Prepare staff payload for client-side modal selector
        $staffJson = $eligibleStaff->map(function (User $u) {
            $cfg = $this->payrollService->getStaffSalaryConfig($u);
            return [
                'id' => $u->id,
                'name' => $u->name,
                'phone' => $u->phone ?? '',
                'role' => $cfg['designation'],
                'base_salary' => $cfg['base_salary'],
                'payout_type' => $cfg['payout_type'],
                'bank_name' => $cfg['bank_name'] ?? '',
                'account_number' => $cfg['account_number'] ?? '',
                'ifsc_code' => $cfg['ifsc_code'] ?? '',
                'upi_id' => $cfg['upi_id'] ?? '',
                'initials' => strtoupper(substr($u->name, 0, 1)),
            ];
        })->values();

        $branches = $tenant->branches()->get(['id', 'name']);

        return view('app.payroll.index', compact(
            'payslips',
            'metrics',
            'selectedMonth',
            'selectedYear',
            'statusFilter',
            'branchId',
            'branches',
            'staffJson',
            'tenant'
        ));
    }

    /**
     * Create or update a staff payslip.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $this->ensureAuthorized();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|between:2020,2035',
            'basic_salary' => 'required|numeric|min:0',
            'working_days' => 'nullable|integer|between:1,31',
            'leaves_taken' => 'nullable|integer|between:0,31',
            'late_days' => 'nullable|integer|between:0,31',
            'allowances' => 'nullable|array',
            'allowances.*.name' => 'nullable|string|max:100',
            'allowances.*.amount' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|array',
            'deductions.*.name' => 'nullable|string|max:100',
            'deductions.*.amount' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'ifsc_code' => 'nullable|string|max:50',
            'upi_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:DRAFT,APPROVED,PAID',
        ]);

        $staff = User::where('tenant_id', $tenant->id)->findOrFail($validated['user_id']);

        $payslip = $this->payrollService->createOrUpdatePayslip(
            $tenant,
            $staff,
            $validated['month'],
            $validated['year'],
            $validated,
            auth()->user()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Payslip {$payslip->payslip_number} generated successfully.",
                'payslip' => $payslip,
            ]);
        }

        return redirect()->route('app.payroll.index', [
            'month' => $validated['month'],
            'year' => $validated['year'],
        ])->with('success', "Payslip {$payslip->payslip_number} for {$staff->name} saved successfully.");
    }

    /**
     * One-click Bulk Run to generate draft payslips for all active staff for the month.
     */
    public function bulkGenerate(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $this->ensureAuthorized();

        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|between:2020,2035',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $result = $this->payrollService->bulkGeneratePayroll(
            $tenant,
            $validated['month'],
            $validated['year'],
            $validated['branch_id'] ?? null,
            auth()->user()
        );

        $monthName = date('F Y', mktime(0, 0, 0, $validated['month'], 1, $validated['year']));

        return redirect()->route('app.payroll.index', [
            'month' => $validated['month'],
            'year' => $validated['year'],
        ])->with('success', "Payroll Run for {$monthName} completed: {$result['created']} draft payslips generated, {$result['skipped']} existing records preserved.");
    }

    /**
     * Mark payslip as Paid with payment mode and transaction reference.
     */
    public function markPaid(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $this->ensureAuthorized();

        $validated = $request->validate([
            'payment_method' => 'required|string|in:UPI,BANK_TRANSFER,CASH,CHEQUE',
            'transaction_reference' => 'nullable|string|max:100',
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'record_expense' => 'nullable|boolean',
        ]);

        $payslip = Payslip::where('tenant_id', $tenant->id)->findOrFail($id);

        $this->payrollService->markAsPaid(
            $payslip,
            $validated,
            auth()->user(),
            (bool) ($validated['record_expense'] ?? true)
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Payslip {$payslip->payslip_number} marked as Paid.",
                'payslip' => $payslip,
            ]);
        }

        return back()->with('success', "Payslip {$payslip->payslip_number} marked as Paid.");
    }

    /**
     * Display printable, official A4 payslip document.
     */
    public function document(int $id): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $this->ensureAuthorized();

        $payslip = Payslip::where('tenant_id', $tenant->id)
            ->with(['user.branches', 'user.trainerProfile', 'branch', 'tenant'])
            ->findOrFail($id);

        $staffConfig = $this->payrollService->getStaffSalaryConfig($payslip->user);

        return view('app.payroll.payslip-document', compact(
            'payslip',
            'staffConfig',
            'tenant'
        ));
    }

    /**
     * Delete or cancel a payslip.
     */
    public function destroy(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $this->ensureAuthorized();

        $payslip = Payslip::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($payslip->isPaid()) {
            return back()->with('error', 'Paid payslips cannot be deleted directly. Please contact support or cancel settlement first.');
        }

        $payslip->delete();

        return back()->with('success', "Payslip {$payslip->payslip_number} has been deleted.");
    }

    /**
     * Check authorization for payroll actions.
     */
    protected function ensureAuthorized(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        if ($user->isGymOwner() || $user->isSuperAdmin() || $user->role === 'gym_manager' || $user->role === 'accountant') {
            return;
        }

        if ($user->hasAnyPermission(['staff.view', 'staff.create', 'expenses.view', 'reports.balance_sheet'])) {
            return;
        }

        abort(403, 'Unauthorized access to Payroll and Salary management.');
    }
}
