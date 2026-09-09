<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Device;
use App\Models\DietPlan;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GymClass;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Trainer;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\AccessControl\AccessControlService;
use App\Services\AttendanceService;
use App\Services\FeatureGateService;
use App\Services\MemberPaymentService;
use App\Services\MembershipService;
use App\Services\ReportService;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AppController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected MembershipService $membershipService,
        protected MemberPaymentService $paymentService,
        protected AttendanceService $attendanceService,
        protected AccessControlService $accessControlService,
        protected SubscriptionService $subscriptionService,
        protected FeatureGateService $featureGateService
    ) {}

    public function dashboard(): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $branch = TenantContext::getBranch();
        $metrics = $this->reportService->getTenantDashboardMetrics($tenant, $branch?->id);

        $subscription = $tenant->activeSubscription;
        $plan = $subscription?->plan;
        $quotas = [
            'members' => $this->featureGateService->checkQuota($tenant, 'members'),
            'branches' => $this->featureGateService->checkQuota($tenant, 'branches'),
            'staff' => $this->featureGateService->checkQuota($tenant, 'staff'),
        ];

        return view('app.dashboard', compact('tenant', 'branch', 'metrics', 'subscription', 'plan', 'quotas'));
    }

    public function members(Request $request): View
    {
        $query = Member::with(['branch', 'activeMembership.plan', 'activeMembership.payments', 'memberships.plan']);

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('member_code', 'like', "%{$s}%");
            });
        }

        if ($request->status) {
            $status = strtoupper($request->status);
            $query->where('status', $status);
        }

        if ($request->filter === 'expiring') {
            $query->whereHas('activeMembership', function ($q) {
                $q->where('status', 'ACTIVE')
                    ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()]);
            });
        } elseif ($request->filter === 'churn') {
            $query->whereHas('activeMembership', function ($q) {
                $q->where('status', 'ACTIVE')
                    ->whereBetween('end_date', [now()->toDateString(), now()->addDays(3)->toDateString()]);
            });
        } elseif ($request->filter === 'due' || $request->filter === 'pending_fee') {
            $query->where(function ($subQ) {
                $subQ->whereHas('activeMembership', function ($q) {
                    $q->whereRaw('final_amount > paid_amount');
                })->orWhereHas('memberships', function ($q) {
                    $q->whereRaw('final_amount > paid_amount');
                });
            });
        } elseif ($request->filter === 'birthday') {
            $query->whereNotNull('dob')
                ->whereMonth('dob', now()->month)
                ->whereDay('dob', now()->day);
        }

        $members = $query->latest()->paginate(15)->withQueryString();
        $branches = Branch::all();
        $membershipPlans = MembershipPlan::where('is_active', true)->get();

        return view('app.members.index', compact('members', 'branches', 'membershipPlans'));
    }

    public function createMember(): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $branches = Branch::all();
        $membershipPlans = MembershipPlan::where('is_active', true)->get();
        $trainers = Trainer::where('status', 'ACTIVE')->get();
        $staff = User::where('tenant_id', $tenant->id)->get();

        return view('app.members.create', compact('branches', 'membershipPlans', 'trainers', 'staff'));
    }

    public function lookupMemberPhone(Request $request): JsonResponse
    {
        $phone = trim((string) $request->input('phone'));
        if (! $phone) {
            return response()->json(['found' => false]);
        }

        $member = Member::where('phone', $phone)
            ->orWhere('phone', 'like', "%{$phone}%")
            ->with(['branch', 'activeMembership.plan'])
            ->first();

        if (! $member) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'member' => [
                'id' => $member->id,
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'full_name' => $member->full_name,
                'phone' => $member->phone,
                'email' => $member->email,
                'gender' => $member->gender,
                'dob' => $member->dob?->format('Y-m-d'),
                'address' => $member->address,
                'photo_path' => $member->photo_path,
                'branch_name' => $member->branch?->name ?? 'Main Branch',
                'branch_id' => $member->branch_id,
                'status' => $member->status,
                'active_plan' => $member->activeMembership?->plan?->name,
                'active_plan_end' => $member->activeMembership?->end_date?->format('d M Y'),
            ],
        ]);
    }

    public function storeMember(Request $request): RedirectResponse
    {
        $mode = $request->input('mode', 'new');
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        if ($mode === 'existing') {
            $validated = $request->validate([
                'existing_member_id' => 'required|exists:members,id',
                'branch_id' => 'nullable|exists:branches,id',
                'membership_plan_id' => 'nullable|exists:membership_plans,id',
                'start_date' => 'nullable|date',
                'discount' => 'nullable|numeric|min:0',
                'initial_payment_amount' => 'nullable|numeric|min:0',
                'payment_method' => 'nullable|string|in:cash,upi,card,netbanking,cheque',
                'transaction_reference' => 'nullable|string|max:100',
                'notes' => 'nullable|string|max:500',
            ]);

            try {
                $member = Member::findOrFail($validated['existing_member_id']);
                if (! empty($validated['branch_id'])) {
                    $member->update(['branch_id' => $validated['branch_id'], 'status' => 'ACTIVE']);
                }

                if (! empty($validated['membership_plan_id'])) {
                    $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);
                    $membershipOptions = [
                        'start_date' => $validated['start_date'] ?? now()->toDateString(),
                        'discount' => (float) ($validated['discount'] ?? 0),
                        'notes' => $validated['notes'] ?? 'Assigned from existing member screen',
                    ];
                    $membership = $this->membershipService->assignMembership($member, $plan, $membershipOptions);

                    $initialPay = (float) ($validated['initial_payment_amount'] ?? 0);
                    if ($initialPay > 0) {
                        $this->paymentService->recordPayment(
                            $member,
                            $initialPay,
                            $validated['payment_method'] ?? 'cash',
                            $membership,
                            $validated['transaction_reference'] ?? null,
                            $validated['notes'] ?? 'Initial enrollment fee payment'
                        );
                    }
                }

                return redirect()->route('app.members.index')->with('success', "Member '{$member->full_name}' assigned to gym successfully!");
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage())->withInput();
            }
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'alternate_phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'gender' => 'nullable|in:male,female,other,Male,Female,Other',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'whatsapp_disabled' => 'nullable|boolean',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'emergency_relation' => 'nullable|string|max:50',
            'branch_id' => 'nullable|exists:branches,id',
            'membership_plan_id' => 'nullable|exists:membership_plans,id',
            'join_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'lead_source' => 'nullable|string|max:100',
            'trainer_id' => 'nullable|string|max:50',
            'sales_rep_id' => 'nullable|string|max:50',
            'locker_number' => 'nullable|string|max:50',
            'max_daily_entries' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'target_weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|string|max:30',
            'height_unit' => 'nullable|string|in:cm,ft',
            'fitness_goal' => 'nullable|string|max:100',
            'medical_history' => 'nullable|string|max:1000',
            'discount' => 'nullable|numeric|min:0',
            'initial_payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:cash,upi,card,netbanking,cheque',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:5120',
            'photo_data' => 'nullable|string',
        ]);

        try {
            // Handle Photo Upload / Base64 Snapshot
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('members/photos', 'public');
            } elseif (! empty($validated['photo_data']) && str_starts_with($validated['photo_data'], 'data:image')) {
                @[$type, $data] = explode(';', $validated['photo_data']);
                @[, $data] = explode(',', $data);
                if ($data) {
                    $decodedImage = base64_decode($data);
                    $fileName = 'members/photos/cam_'.Str::random(20).'.jpg';
                    Storage::disk('public')->put($fileName, $decodedImage);
                    $photoPath = $fileName;
                }
            }

            // Handle ID Documents
            $savedDocuments = [];
            if ($request->has('id_documents') && is_array($request->id_documents)) {
                foreach ($request->id_documents as $idx => $doc) {
                    if (! empty($doc['number']) || ! empty($doc['type'])) {
                        $frontPath = null;
                        $backPath = null;
                        if ($request->hasFile("id_documents.{$idx}.front")) {
                            $frontPath = $request->file("id_documents.{$idx}.front")->store('members/documents', 'public');
                        }
                        if ($request->hasFile("id_documents.{$idx}.back")) {
                            $backPath = $request->file("id_documents.{$idx}.back")->store('members/documents', 'public');
                        }
                        $savedDocuments[] = [
                            'type' => $doc['type'] ?? 'Aadhaar Card',
                            'number' => $doc['number'] ?? '',
                            'front_path' => $frontPath,
                            'back_path' => $backPath,
                        ];
                    }
                }
            }

            $metadata = [
                'alternate_phone' => $validated['alternate_phone'] ?? null,
                'blood_group' => $validated['blood_group'] ?? null,
                'city' => $validated['city'] ?? null,
                'whatsapp_disabled' => $request->boolean('whatsapp_disabled'),
                'lead_source' => $validated['lead_source'] ?? null,
                'trainer_id' => $validated['trainer_id'] ?? null,
                'sales_rep_id' => $validated['sales_rep_id'] ?? null,
                'locker_number' => $validated['locker_number'] ?? null,
                'max_daily_entries' => $validated['max_daily_entries'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'target_weight' => $validated['target_weight'] ?? null,
                'height' => $validated['height'] ?? null,
                'height_unit' => $validated['height_unit'] ?? 'cm',
                'fitness_goal' => $validated['fitness_goal'] ?? null,
                'medical_history' => $validated['medical_history'] ?? null,
                'emergency_relation' => $validated['emergency_relation'] ?? null,
                'id_documents' => $savedDocuments,
            ];

            $createPayload = array_merge($validated, [
                'gender' => strtolower($validated['gender'] ?? 'male'),
                'photo_path' => $photoPath,
                'join_date' => $validated['join_date'] ?? $validated['start_date'] ?? now()->toDateString(),
                'metadata' => $metadata,
            ]);

            $member = $this->membershipService->createMember($tenant, $createPayload);
            $enrolledGroupCount = 0;

            if (! empty($validated['membership_plan_id'])) {
                $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);
                $membershipOptions = [
                    'start_date' => $validated['start_date'] ?? $validated['join_date'] ?? now()->toDateString(),
                    'discount' => (float) ($validated['discount'] ?? 0),
                    'notes' => $validated['notes'] ?? null,
                ];
                $membership = $this->membershipService->assignMembership($member, $plan, $membershipOptions);

                $initialPay = (float) ($validated['initial_payment_amount'] ?? 0);
                if ($initialPay > 0) {
                    $this->paymentService->recordPayment(
                        $member,
                        $initialPay,
                        $validated['payment_method'] ?? 'cash',
                        $membership,
                        $validated['transaction_reference'] ?? null,
                        $validated['notes'] ?? 'Initial enrollment fee payment'
                    );
                }

                // Handle Duo / Family group members enrollment
                if (in_array($plan->plan_type, ['duo', 'family']) && is_array($request->input('group_members'))) {
                    $maxAdditional = ($plan->plan_type === 'duo') ? 1 : ($plan->max_members > 1 ? $plan->max_members - 1 : 3);
                    $groupInputs = array_slice($request->input('group_members'), 0, $maxAdditional);

                    foreach ($groupInputs as $gIdx => $gData) {
                        $gFirstName = trim($gData['first_name'] ?? '');
                        if (empty($gFirstName)) {
                            continue;
                        }

                        $gLastName = trim($gData['last_name'] ?? '');
                        $gPhone = trim($gData['phone'] ?? '') ?: $member->phone;
                        $gEmail = trim($gData['email'] ?? '') ?: null;
                        $gGender = strtolower(trim($gData['gender'] ?? 'male'));
                        $gDob = ! empty($gData['dob']) ? $gData['dob'] : null;
                        $gRelation = trim($gData['relation'] ?? ($plan->plan_type === 'duo' ? 'Duo Partner' : 'Family Member'));

                        $gMetadata = [
                            'relation' => $gRelation,
                            'group_primary_member_id' => $member->id,
                            'plan_type' => $plan->plan_type,
                            'linked_to' => $member->full_name,
                        ];

                        $gMemberPayload = [
                            'branch_id' => $member->branch_id,
                            'first_name' => $gFirstName,
                            'last_name' => $gLastName,
                            'phone' => $gPhone,
                            'email' => $gEmail,
                            'gender' => $gGender,
                            'dob' => $gDob,
                            'emergency_contact_name' => $member->full_name,
                            'emergency_contact_phone' => $member->phone,
                            'join_date' => $member->join_date,
                            'status' => 'ACTIVE',
                            'notes' => "Enrolled under {$plan->name} ({$plan->plan_type}) with primary member {$member->full_name} ({$member->member_code})",
                            'metadata' => $gMetadata,
                        ];

                        $gMember = $this->membershipService->createMember($tenant, $gMemberPayload);

                        Membership::create([
                            'tenant_id' => $tenant->id,
                            'branch_id' => $member->branch_id,
                            'member_id' => $gMember->id,
                            'membership_plan_id' => $plan->id,
                            'start_date' => $membership->start_date,
                            'end_date' => $membership->end_date,
                            'price' => 0.00,
                            'discount' => 0.00,
                            'tax' => 0.00,
                            'final_amount' => 0.00,
                            'paid_amount' => 0.00,
                            'status' => 'ACTIVE',
                            'notes' => "Linked to {$plan->name} (Primary: {$member->full_name})",
                        ]);

                        $enrolledGroupCount++;
                    }
                }
            }

            if ($enrolledGroupCount > 0) {
                return redirect()->route('app.members.index')->with('success', "Primary member '{$member->full_name}' and {$enrolledGroupCount} group member(s) enrolled successfully under {$plan->name}!");
            }

            return redirect()->route('app.members.index')->with('success', "Member '{$member->full_name}' enrolled successfully!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function updateMember(Request $request, int $id): RedirectResponse
    {
        $member = Member::with('activeMembership')->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|in:ACTIVE,INACTIVE,SUSPENDED,EXPIRED',
            'notes' => 'nullable|string|max:500',
            'membership_plan_id' => 'nullable|exists:membership_plans,id',
        ]);

        $member->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'address' => $validated['address'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'branch_id' => $validated['branch_id'] ?? $member->branch_id,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! empty($validated['membership_plan_id'])) {
            $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);
            if (! $member->activeMembership || $member->activeMembership->membership_plan_id != $plan->id) {
                $this->membershipService->assignMembership($member, $plan, [
                    'start_date' => now()->toDateString(),
                ]);
            }
        }

        ActivityLog::log('member_updated', "Updated details for member {$member->full_name} ({$member->member_code})", $member);

        return back()->with('success', "Member '{$member->full_name}' details updated successfully!");
    }

    public function collectMemberFee(Request $request, int $id): RedirectResponse
    {
        $member = Member::with('activeMembership')->findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,upi,card,netbanking,cheque',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $membership = $member->activeMembership ?? $member->memberships()->latest()->first();

        $this->paymentService->recordPayment(
            $member,
            (float) $validated['amount'],
            $validated['payment_method'],
            $membership,
            $validated['transaction_reference'] ?? null,
            $validated['notes'] ?? 'Remaining fee balance payment'
        );

        $currency = auth()->user()->tenant?->currency_symbol ?? '₹';

        return back()->with('success', "Payment of {$currency}".number_format($validated['amount'], 2)." collected successfully from {$member->full_name}!");
    }

    public function deleteMember(int $id): RedirectResponse
    {
        $member = Member::findOrFail($id);
        $name = $member->full_name;
        $member->delete();

        ActivityLog::log('member_deleted', "Deleted member {$name}");

        return back()->with('success', "Member '{$name}' deleted successfully.");
    }

    public function showMember(int $id): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $member = Member::with([
            'branch',
            'activeMembership.plan',
            'activeMembership.payments',
            'memberships.plan',
            'memberships.payments',
            'payments.membership.plan',
            'attendance' => fn ($q) => $q->latest()->take(30),
            'workoutPlans',
            'dietPlans',
        ])->findOrFail($id);

        $membershipPlans = MembershipPlan::where('is_active', true)->get();
        $branches = Branch::all();
        $trainers = Trainer::where('status', 'ACTIVE')->get();
        $staff = User::where('tenant_id', $tenant->id)->get();

        $trainerId = $member->metadata['trainer_id'] ?? null;
        $assignedTrainer = $trainerId ? Trainer::find($trainerId) : null;

        $salesRepId = $member->metadata['sales_rep_id'] ?? null;
        $salesRep = $salesRepId ? User::find($salesRepId) : null;

        $activeMembership = $member->activeMembership ?? $member->memberships()->latest()->first();
        $daysLeft = $activeMembership ? (int) now()->startOfDay()->diffInDays($activeMembership->end_date->startOfDay(), false) : null;

        $joinDate = $member->join_date ?? $member->created_at;
        $monthsActive = $joinDate ? max(1, (int) $joinDate->diffInMonths(now())) : 1;

        $lastAttendance = $member->attendance()->latest()->first();

        $auditLogs = ActivityLog::where('subject_type', Member::class)
            ->where('subject_id', $member->id)
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        return view('app.members.show', compact(
            'member',
            'activeMembership',
            'daysLeft',
            'monthsActive',
            'lastAttendance',
            'membershipPlans',
            'branches',
            'trainers',
            'staff',
            'assignedTrainer',
            'salesRep',
            'auditLogs'
        ));
    }

    public function showInvoice(int $id): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $payment = MemberPayment::with(['member.branch', 'membership.plan', 'receivedBy'])->findOrFail($id);
        $member = $payment->member;
        $membership = $payment->membership ?? $member->activeMembership ?? $member->memberships()->latest()->first();
        $plan = $membership?->plan;

        return view('app.invoices.show', compact('payment', 'member', 'membership', 'plan', 'tenant'));
    }

    public function toggleFreezeMember(int $id): RedirectResponse
    {
        $member = Member::findOrFail($id);
        if ($member->status === 'SUSPENDED') {
            $member->update(['status' => 'ACTIVE']);
            $msg = "Member '{$member->full_name}' account has been reactivated / un-frozen.";
        } else {
            $member->update(['status' => 'SUSPENDED']);
            $msg = "Member '{$member->full_name}' account has been frozen.";
        }

        return back()->with('success', $msg);
    }

    public function addMemberSubscription(Request $request, int $id): RedirectResponse
    {
        $member = Member::findOrFail($id);

        $validated = $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'start_date' => 'nullable|date',
            'discount' => 'nullable|numeric|min:0',
            'initial_payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:cash,upi,card,netbanking,cheque',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);
            $membershipOptions = [
                'start_date' => $validated['start_date'] ?? now()->toDateString(),
                'discount' => (float) ($validated['discount'] ?? 0),
                'notes' => $validated['notes'] ?? 'Added from member profile',
            ];
            $membership = $this->membershipService->assignMembership($member, $plan, $membershipOptions);

            $initialPay = (float) ($validated['initial_payment_amount'] ?? 0);
            if ($initialPay > 0) {
                $this->paymentService->recordPayment(
                    $member,
                    $initialPay,
                    $validated['payment_method'] ?? 'cash',
                    $membership,
                    $validated['transaction_reference'] ?? null,
                    $validated['notes'] ?? 'Subscription payment'
                );
            }

            return back()->with('success', "New subscription '{$plan->name}' assigned to '{$member->full_name}' successfully!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function addPtPackage(Request $request, int $id): RedirectResponse
    {
        $member = Member::findOrFail($id);
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'trainer_id' => 'nullable|exists:trainers,id',
            'pt_package_name' => 'required|string|max:150',
            'sessions' => 'nullable|integer|min:1',
            'validity_days' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'collected_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:cash,upi,card,netbanking,cheque',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $trainer = ! empty($validated['trainer_id']) ? Trainer::find($validated['trainer_id']) : null;
            $startDate = Carbon::parse($validated['start_date']);
            $validity = (int) ($validated['validity_days'] ?? 30);
            $endDate = $startDate->copy()->addDays($validity);

            $meta = $member->metadata ?? [];
            $ptPackages = $meta['pt_packages'] ?? [];

            $ptEntry = [
                'id' => uniqid('pt_'),
                'package_name' => $validated['pt_package_name'],
                'trainer_id' => $trainer?->id,
                'trainer_name' => $trainer?->full_name ?? 'Assigned Trainer',
                'sessions' => (int) ($validated['sessions'] ?? 12),
                'remaining_sessions' => (int) ($validated['sessions'] ?? 12),
                'validity_days' => $validity,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'amount' => (float) $validated['amount'],
                'discount' => (float) ($validated['discount'] ?? 0),
                'tax' => (float) ($validated['tax'] ?? 0),
                'total' => max(0, (float) $validated['amount'] - (float) ($validated['discount'] ?? 0) + (float) ($validated['tax'] ?? 0)),
                'paid' => (float) ($validated['collected_amount'] ?? 0),
                'status' => 'ACTIVE',
                'created_at' => now()->toDateTimeString(),
            ];

            if ($trainer) {
                $meta['trainer_id'] = $trainer->id;
            }

            array_unshift($ptPackages, $ptEntry);
            $meta['pt_packages'] = $ptPackages;
            $member->update(['metadata' => $meta]);

            $collected = (float) ($validated['collected_amount'] ?? 0);
            if ($collected > 0) {
                MemberPayment::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $member->branch_id,
                    'member_id' => $member->id,
                    'membership_id' => $member->activeMembership?->id,
                    'amount' => $collected,
                    'payment_date' => now()->toDateString(),
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'transaction_reference' => $validated['transaction_reference'] ?? null,
                    'receipt_number' => 'RCP'.date('Ymd').rand(100, 999),
                    'received_by' => auth()->id(),
                    'notes' => "PT Package: {$validated['pt_package_name']} (".($trainer ? $trainer->full_name : 'Trainer').')',
                ]);
            }

            ActivityLog::log('pt_package_assigned', "Assigned PT Package '{$validated['pt_package_name']}' to {$member->full_name}");

            return back()->with('success', "Personal Training package assigned to '{$member->full_name}' successfully!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function storeMemberMeasurement(Request $request, int $id): RedirectResponse
    {
        $member = Member::findOrFail($id);

        $validated = $request->validate([
            'date' => 'required|date',
            'weight' => 'nullable|numeric|min:0',
            'chest' => 'nullable|numeric|min:0',
            'shoulders' => 'nullable|numeric|min:0',
            'waist' => 'nullable|numeric|min:0',
            'hips' => 'nullable|numeric|min:0',
            'bicep_left' => 'nullable|numeric|min:0',
            'bicep_right' => 'nullable|numeric|min:0',
            'thigh_left' => 'nullable|numeric|min:0',
            'thigh_right' => 'nullable|numeric|min:0',
            'calves' => 'nullable|numeric|min:0',
            'neck' => 'nullable|numeric|min:0',
            'body_fat' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $meta = $member->metadata ?? [];
        $measurements = $meta['measurements'] ?? [];

        $newEntry = [
            'id' => uniqid(),
            'date' => $validated['date'],
            'weight' => $validated['weight'] ?? null,
            'chest' => $validated['chest'] ?? null,
            'shoulders' => $validated['shoulders'] ?? null,
            'waist' => $validated['waist'] ?? null,
            'hips' => $validated['hips'] ?? null,
            'bicep_left' => $validated['bicep_left'] ?? null,
            'bicep_right' => $validated['bicep_right'] ?? null,
            'thigh_left' => $validated['thigh_left'] ?? null,
            'thigh_right' => $validated['thigh_right'] ?? null,
            'calves' => $validated['calves'] ?? null,
            'neck' => $validated['neck'] ?? null,
            'body_fat' => $validated['body_fat'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'logged_by' => auth()->user()->name ?? 'Staff',
            'created_at' => now()->toDateTimeString(),
        ];

        array_unshift($measurements, $newEntry);
        $meta['measurements'] = $measurements;

        if (! empty($validated['weight'])) {
            $meta['weight'] = $validated['weight'];
        }

        $member->metadata = $meta;
        $member->save();

        ActivityLog::log('measurement_logged', "Logged body measurements for member {$member->full_name}", $member);

        return back()->with('success', "Body measurements logged successfully for {$member->full_name}!");
    }

    public function memberships(): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $memberships = Membership::with(['member', 'plan', 'branch'])->latest()->paginate(15);
        $plans = MembershipPlan::latest()->get();
        $members = Member::where('status', 'ACTIVE')->get();

        return view('app.memberships.index', compact('memberships', 'plans', 'members'));
    }

    public function storeMembershipPlan(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'plan_type' => 'required|in:single,duo,family',
            'max_members' => 'nullable|integer|min:1|max:20',
            'duration_type' => 'required|in:days,months,years',
            'duration_value' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $maxMembers = match ($validated['plan_type'] ?? 'single') {
            'duo' => 2,
            'family' => 4,
            default => 1,
        };
        $validated['max_members'] = ! empty($validated['max_members']) ? (int) $validated['max_members'] : $maxMembers;
        $validated['tenant_id'] = $tenant->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        MembershipPlan::create($validated);

        return back()->with('success', "Membership Plan '{$validated['name']}' created successfully!");
    }

    public function updateMembershipPlan(Request $request, int $id): RedirectResponse
    {
        $plan = MembershipPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'plan_type' => 'required|in:single,duo,family',
            'max_members' => 'nullable|integer|min:1|max:20',
            'duration_type' => 'required|in:days,months,years',
            'duration_value' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $maxMembers = match ($validated['plan_type'] ?? 'single') {
            'duo' => 2,
            'family' => 4,
            default => 1,
        };
        $validated['max_members'] = ! empty($validated['max_members']) ? (int) $validated['max_members'] : $maxMembers;
        $validated['is_active'] = $request->boolean('is_active');

        $plan->update($validated);

        return back()->with('success', "Membership Plan '{$plan->name}' updated successfully!");
    }

    public function deleteMembershipPlan(int $id): RedirectResponse
    {
        $plan = MembershipPlan::findOrFail($id);

        if ($plan->memberships()->where('status', 'ACTIVE')->exists()) {
            return back()->with('error', 'Cannot delete a membership plan that is currently assigned to active members.');
        }

        $plan->delete();

        return back()->with('success', "Membership Plan '{$plan->name}' deleted successfully.");
    }

    public function attendance(Request $request): View
    {
        $attendance = Attendance::with(['member', 'device'])->latest('check_in')->paginate(20);
        $members = Member::where('status', 'ACTIVE')->get();
        $summary = $this->attendanceService->getTodaySummary();

        return view('app.attendance.index', compact('attendance', 'members', 'summary'));
    }

    public function storeCheckin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'action' => 'required|in:check_in,check_out',
        ]);

        $member = Member::findOrFail($validated['member_id']);

        if ($validated['action'] === 'check_out') {
            $this->attendanceService->checkOut($member);

            return back()->with('success', "Check-out recorded for {$member->full_name}.");
        }

        $this->attendanceService->checkIn($member, 'manual');

        return back()->with('success', "Check-in recorded for {$member->full_name}.");
    }

    public function payments(): View
    {
        $payments = MemberPayment::with(['member', 'receivedBy'])->latest('payment_date')->paginate(15);
        $members = Member::all();

        return view('app.payments.index', compact('payments', 'members'));
    }

    public function storePayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,card,pos,upi,bank_transfer,online',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $member = Member::findOrFail($validated['member_id']);
        $membership = $member->activeMembership;

        $this->paymentService->recordPayment(
            $member,
            (float) $validated['amount'],
            $validated['payment_method'],
            $membership,
            $validated['transaction_reference'],
            $validated['notes']
        );

        return back()->with('success', "Payment of \${$validated['amount']} recorded successfully!");
    }

    public function trainers(): View
    {
        $trainers = Trainer::with('branch')->latest()->paginate(15);
        $branches = Branch::all();

        return view('app.trainers.index', compact('trainers', 'branches'));
    }

    public function storeTrainer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'specialization' => 'nullable|string|max:150',
            'hourly_rate' => 'nullable|numeric|min:0',
            'bio' => 'nullable|string|max:500',
        ]);

        Trainer::create($validated);

        return back()->with('success', 'Trainer added successfully!');
    }

    public function classes(): View
    {
        $classes = GymClass::with(['branch', 'schedules.trainer'])->latest()->paginate(15);
        $trainers = Trainer::where('status', 'ACTIVE')->get();

        return view('app.classes.index', compact('classes', 'trainers'));
    }

    public function workouts(): View
    {
        $plans = WorkoutPlan::with(['member', 'trainer', 'exercises'])->latest()->paginate(15);
        $members = Member::where('status', 'ACTIVE')->get();
        $trainers = Trainer::where('status', 'ACTIVE')->get();

        return view('app.workouts.index', compact('plans', 'members', 'trainers'));
    }

    public function diets(): View
    {
        $plans = DietPlan::with(['member', 'trainer', 'meals'])->latest()->paginate(15);
        $members = Member::where('status', 'ACTIVE')->get();
        $trainers = Trainer::where('status', 'ACTIVE')->get();

        return view('app.diets.index', compact('plans', 'members', 'trainers'));
    }

    public function leads(): View
    {
        $leads = Lead::with('assignedTo')->latest()->paginate(15);

        return view('app.leads.index', compact('leads'));
    }

    public function expenses(): View
    {
        $expenses = Expense::with('category')->latest('expense_date')->paginate(15);
        $categories = ExpenseCategory::all();

        return view('app.expenses.index', compact('expenses', 'categories'));
    }

    public function inventory(): View
    {
        $items = InventoryItem::latest()->paginate(15);

        return view('app.inventory.index', compact('items'));
    }

    public function devices(): View
    {
        $devices = Device::with('branch')->latest()->paginate(15);
        $branches = Branch::all();
        $logs = AccessLog::with(['member', 'device'])->latest('event_time')->take(20)->get();

        return view('app.devices.index', compact('devices', 'branches', 'logs'));
    }

    public function storeDevice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'model' => 'nullable|string|max:100',
            'type' => 'required|in:hikvision_facial,hikvision_turnstile,rfid_reader,qr_scanner,generic_biometric',
            'serial_number' => 'nullable|string|max:100',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer',
            'direction' => 'required|in:in,out,both',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $validated['device_secret'] = 'dev_'.Str::random(24);
        Device::create($validated);

        return back()->with('success', 'Access control device registered successfully!');
    }

    public function testDevice(int $id): RedirectResponse
    {
        $device = Device::findOrFail($id);
        $driver = $this->accessControlService->getDriver($device);
        $driver->checkHealth($device);

        return back()->with('success', "Device ping check completed! Status: {$device->status}");
    }

    public function subscription(): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $subscription = $tenant->activeSubscription ?? $tenant->latestSubscription;
        $plans = Plan::with('features')->where('is_active', true)->orderBy('sort_order')->get();
        $invoices = $tenant->subscriptions()->with('invoices')->get()->pluck('invoices')->flatten();
        $payments = $tenant->subscriptions()->with('payments')->get()->pluck('payments')->flatten();

        $quotas = [
            'members' => $this->featureGateService->checkQuota($tenant, 'members'),
            'branches' => $this->featureGateService->checkQuota($tenant, 'branches'),
            'staff' => $this->featureGateService->checkQuota($tenant, 'staff'),
        ];

        return view('app.subscription.index', compact('tenant', 'subscription', 'plans', 'invoices', 'payments', 'quotas'));
    }

    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'plan_id' => 'nullable|exists:plans,id',
            'billing_cycle' => 'nullable|in:monthly,yearly',
        ]);

        $code = strtoupper(trim($request->code));
        $coupon = Coupon::with('plan')->where('code', $code)->first();

        if (! $coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or non-existent promo coupon code.',
            ], 422);
        }

        if (! $coupon->is_active) {
            return response()->json(['valid' => false, 'message' => 'This promo coupon is inactive or disabled.'], 422);
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return response()->json(['valid' => false, 'message' => 'This promo coupon offer has not started yet.'], 422);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json(['valid' => false, 'message' => 'This promo coupon has expired.'], 422);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['valid' => false, 'message' => 'This promo coupon has reached its maximum redemption limit.'], 422);
        }

        return response()->json([
            'valid' => true,
            'code' => $coupon->code,
            'name' => $coupon->name,
            'discount_type' => $coupon->discount_type,
            'discount_value' => (float) $coupon->discount_value,
            'plan_id' => $coupon->plan_id,
            'plan_name' => $coupon->plan?->name,
            'min_amount' => (float) $coupon->min_amount,
            'max_discount_amount' => $coupon->max_discount_amount ? (float) $coupon->max_discount_amount : null,
            'message' => "Coupon '{$coupon->code}' applied successfully!",
        ]);
    }

    public function upgradePlan(Request $request): RedirectResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
            'coupon_code' => 'nullable|string|max:30',
        ]);

        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $plan = Plan::findOrFail($request->plan_id);
        $originalPrice = $request->billing_cycle === 'yearly' ? (float) $plan->price_yearly : (float) $plan->price_monthly;

        $coupon = null;
        $discountAmount = 0.0;
        $finalPrice = $originalPrice;

        if ($request->filled('coupon_code')) {
            $code = strtoupper(trim($request->coupon_code));
            $foundCoupon = Coupon::where('code', $code)->first();

            if ($foundCoupon) {
                $check = $foundCoupon->isValid($plan, $originalPrice);
                if ($check['valid']) {
                    $coupon = $foundCoupon;
                    $discountAmount = $coupon->calculateDiscount($originalPrice);
                    $finalPrice = max(0, $originalPrice - $discountAmount);
                }
            }
        }

        $this->subscriptionService->activateSubscription(
            $tenant,
            $plan,
            $request->billing_cycle,
            'manual_online',
            'TXN-UPG-'.strtoupper(Str::random(10)),
            (float) $finalPrice,
            ['upgraded_from_web' => true, 'coupon_code' => $coupon?->code],
            $coupon,
            $discountAmount
        );

        $currency = $tenant->currency_symbol ?? '₹';
        $msg = "Plan activated for {$plan->name} successfully!";
        if ($coupon && $discountAmount > 0) {
            $msg .= " (Coupon '{$coupon->code}' applied: saved {$currency}".number_format($discountAmount, 2).')';
        }

        return redirect()->route('app.subscription.index')->with('success', $msg);
    }

    public function settings(): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $branches = $tenant->branches;

        return view('app.settings.index', compact('tenant', 'branches'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
        ]);

        $tenant->update($validated);

        return back()->with('success', 'Gym business profile updated successfully!');
    }

    public function saveThemeSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mode' => 'nullable|string',
            'accent' => 'nullable|string',
            'sidebarCaption' => 'nullable|boolean',
            'direction' => 'nullable|string',
            'layoutWidth' => 'nullable|string',
            'fontFamily' => 'nullable|string',
            'fontSize' => 'nullable|string',
        ]);

        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            Setting::setGlobal('admin_theme_settings', $validated, 'json');
        } elseif ($user->tenant_id) {
            Setting::set('theme_settings', $validated, 'json', $user->tenant_id);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Theme settings saved successfully!',
            'theme' => $validated,
        ]);
    }
}
