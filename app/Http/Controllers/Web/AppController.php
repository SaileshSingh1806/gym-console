<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\Coupon;
use App\Models\Device;
use App\Models\DietMeal;
use App\Models\DietPlan;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GymClass;
use App\Models\GymService;
use App\Models\GymServiceBooking;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\MemberPtPackage;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Plan;
use App\Models\PtPlan;
use App\Models\PtSession;
use App\Models\Setting;
use App\Models\Trainer;
use App\Models\User;
use App\Models\WorkoutExercise;
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
use Illuminate\Support\Facades\Hash;
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

    public function payments(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $query = MemberPayment::with(['member.branch', 'membership.plan', 'receivedBy'])->latest('payment_date')->latest('id');

        // Search filter (member name, phone, email, member_code, invoice_number, transaction_reference)
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                    ->orWhere('transaction_reference', 'like', "%{$s}%")
                    ->orWhereHas('member', function ($mq) use ($s) {
                        $mq->where('first_name', 'like', "%{$s}%")
                            ->orWhere('last_name', 'like', "%{$s}%")
                            ->orWhere('phone', 'like', "%{$s}%")
                            ->orWhere('email', 'like', "%{$s}%")
                            ->orWhere('member_code', 'like', "%{$s}%");
                    });
            });
        }

        // Date range filter
        $dateFilter = $request->get('date_filter', 'month_till_date');
        if ($dateFilter === 'today') {
            $query->whereDate('payment_date', now()->toDateString());
        } elseif ($dateFilter === 'yesterday') {
            $query->whereDate('payment_date', now()->subDay()->toDateString());
        } elseif ($dateFilter === 'this_week') {
            $query->whereBetween('payment_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
        } elseif ($dateFilter === 'month_till_date') {
            $query->whereBetween('payment_date', [now()->startOfMonth()->toDateString(), now()->toDateString()]);
        } elseif ($dateFilter === 'last_month') {
            $query->whereBetween('payment_date', [now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()]);
        } elseif ($dateFilter === 'this_year') {
            $query->whereBetween('payment_date', [now()->startOfYear()->toDateString(), now()->toDateString()]);
        }

        // Method filter
        if ($request->filled('method') && $request->method !== 'all') {
            $query->where('payment_method', strtolower($request->method));
        }

        // Status filter (completed, partial, reversed)
        if ($request->filled('status') && $request->status !== 'all') {
            $st = strtolower($request->status);
            if ($st === 'reversed') {
                $query->where('notes', 'like', '%[REVERSED]%');
            } elseif ($st === 'partial') {
                $query->where('notes', 'not like', '%[REVERSED]%')
                    ->whereHas('membership', function ($mq) {
                        $mq->whereColumn('paid_amount', '<', 'final_amount');
                    });
            } elseif ($st === 'completed') {
                $query->where('notes', 'not like', '%[REVERSED]%')
                    ->where(function ($q) {
                        $q->whereNull('membership_id')
                            ->orWhereHas('membership', function ($mq) {
                                $mq->whereColumn('paid_amount', '>=', 'final_amount');
                            });
                    });
            }
        }

        // Due dates filter (has_due, no_due)
        if ($request->filled('due_filter') && $request->due_filter !== 'all') {
            $dueF = strtolower($request->due_filter);
            if ($dueF === 'has_due') {
                $query->whereHas('membership', function ($mq) {
                    $mq->whereColumn('paid_amount', '<', 'final_amount');
                });
            } elseif ($dueF === 'no_due') {
                $query->where(function ($q) {
                    $q->whereNull('membership_id')
                        ->orWhereHas('membership', function ($mq) {
                            $mq->whereColumn('paid_amount', '>=', 'final_amount');
                        });
                });
            }
        }

        // Clone for aggregates
        $aggQuery = clone $query;
        $totalCollected = (float) (clone $aggQuery)->where('notes', 'not like', '%[REVERSED]%')->sum('amount');

        // Total Due calculation across filtered records
        $allPaymentsForDue = (clone $query)->with('membership')->get();
        $totalDue = 0.0;
        $countedMemberships = [];
        foreach ($allPaymentsForDue as $pay) {
            if ($pay->membership && ! isset($countedMemberships[$pay->membership_id])) {
                $countedMemberships[$pay->membership_id] = true;
                $due = max(0, (float) $pay->membership->final_amount - (float) $pay->membership->paid_amount);
                $totalDue += $due;
            }
        }

        $payments = $query->paginate(20)->withQueryString();

        $membershipPlans = MembershipPlan::where('is_active', true)->get();
        $trainers = Trainer::where('status', 'ACTIVE')->get();
        $members = Member::with(['activeMembership.plan', 'branch'])->where('status', '!=', 'DELETED')->get();

        return view('app.payments.index', compact(
            'payments',
            'members',
            'membershipPlans',
            'trainers',
            'totalCollected',
            'totalDue'
        ));
    }

    public function storePayment(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'membership_id' => 'nullable|exists:memberships,id',
            'payment_date' => 'nullable|date',
            'item_type' => 'nullable|string|in:membership,pt,service,custom,due',
            'membership_plan_id' => 'nullable|exists:membership_plans,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'pt_package_name' => 'nullable|string|max:150',
            'trainer_id' => 'nullable|exists:trainers,id',
            'sessions' => 'nullable|integer|min:1',
            'validity_days' => 'nullable|integer|min:1',
            'custom_item_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'collected_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,pos,upi,bank_transfer,netbanking,cheque,online',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'activate_now' => 'nullable|boolean',
        ]);

        try {
            $member = Member::with(['activeMembership', 'memberships'])->findOrFail($validated['member_id']);
            $itemType = $validated['item_type'] ?? 'membership';
            $membership = null;
            $paymentDate = $validated['payment_date'] ?? now()->toDateString();
            $collectedAmount = (float) $validated['collected_amount'];
            $notes = $validated['notes'] ?? '';
            $itemLabel = 'Fee Payment';

            if (! empty($validated['membership_id'])) {
                $membership = Membership::where('member_id', $member->id)->find($validated['membership_id']);
            }

            if ($itemType === 'membership' && ! empty($validated['membership_plan_id'])) {
                $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);
                $startDate = $validated['start_date'] ?? now()->toDateString();

                if (! empty($validated['end_date'])) {
                    $endDate = $validated['end_date'];
                } else {
                    $start = Carbon::parse($startDate);
                    $val = (int) $plan->duration_value;
                    $end = match ($plan->duration_type) {
                        'days' => $start->copy()->addDays($val),
                        'years' => $start->copy()->addYears($val),
                        default => $start->copy()->addMonths($val),
                    };
                    $endDate = $end->toDateString();
                }

                $price = (float) $validated['amount'];
                $discount = (float) ($validated['discount'] ?? 0);
                $tax = (float) ($validated['tax'] ?? 0);
                $finalAmount = max(0, $price - $discount + $tax);

                $membership = Membership::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $member->branch_id,
                    'member_id' => $member->id,
                    'membership_plan_id' => $plan->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'price' => $price,
                    'discount' => $discount,
                    'tax' => $tax,
                    'final_amount' => $finalAmount,
                    'paid_amount' => 0.00,
                    'status' => 'ACTIVE',
                    'notes' => $notes ?: "Plan: {$plan->name}",
                ]);

                $member->update(['status' => 'ACTIVE']);
                $itemLabel = "Membership: {$plan->name}";
            } elseif ($itemType === 'pt' && ! empty($validated['pt_package_name'])) {
                $trainer = ! empty($validated['trainer_id']) ? Trainer::find($validated['trainer_id']) : null;
                $startDate = Carbon::parse($validated['start_date'] ?? now()->toDateString());
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
                    'paid' => $collectedAmount,
                    'status' => 'ACTIVE',
                    'created_at' => now()->toDateTimeString(),
                ];

                if ($trainer) {
                    $meta['trainer_id'] = $trainer->id;
                }

                array_unshift($ptPackages, $ptEntry);
                $meta['pt_packages'] = $ptPackages;
                $member->update(['metadata' => $meta]);

                $membership = $membership ?? $member->activeMembership;
                $itemLabel = "PT Package: {$validated['pt_package_name']}".($trainer ? " ({$trainer->full_name})" : '');
            } elseif ($itemType === 'due') {
                $membership = $membership ?? $member->activeMembership ?? $member->memberships()->latest()->first();
                $itemLabel = ! empty($validated['custom_item_name']) ? $validated['custom_item_name'] : 'Due Payment Clearance';
            } else {
                $membership = $membership ?? $member->activeMembership ?? $member->memberships()->latest()->first();
                $itemLabel = ! empty($validated['custom_item_name']) ? $validated['custom_item_name'] : 'Fee Payment / General Item';
            }

            if ($collectedAmount > 0) {
                $invoiceNumber = 'RCP'.date('Ymd').rand(1000, 9999);
                $notesCombined = trim($itemLabel.($notes ? " | {$notes}" : ''));

                $payment = MemberPayment::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $member->branch_id,
                    'member_id' => $member->id,
                    'membership_id' => $membership?->id,
                    'invoice_number' => $invoiceNumber,
                    'amount' => $collectedAmount,
                    'payment_method' => $validated['payment_method'],
                    'transaction_reference' => $validated['transaction_reference'] ?? null,
                    'payment_date' => $paymentDate,
                    'received_by_user_id' => auth()->id(),
                    'notes' => $notesCombined,
                ]);

                if ($membership) {
                    $newPaid = (float) $membership->paid_amount + $collectedAmount;
                    $membership->update(['paid_amount' => $newPaid]);
                }

                ActivityLog::log('member_payment_received', 'Recorded payment of '.($tenant->currency_symbol ?? '₹').number_format($collectedAmount, 2)." from {$member->full_name} ({$invoiceNumber})", $payment);
            }

            $currency = $tenant->currency_symbol ?? '₹';

            return back()->with('success', "Payment of {$currency}".number_format($collectedAmount, 2)." recorded and receipt issued for {$member->full_name}!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function reversePayment(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $payment = MemberPayment::with('membership')->findOrFail($id);

        if ($payment->notes && str_contains($payment->notes, '[REVERSED]')) {
            return back()->with('error', 'This payment receipt is already marked as reversed.');
        }

        if ($payment->membership) {
            $newPaid = max(0, (float) $payment->membership->paid_amount - (float) $payment->amount);
            $payment->membership->update(['paid_amount' => $newPaid]);
        }

        $payment->update([
            'notes' => '[REVERSED] '.($payment->notes ?? ''),
        ]);

        $currency = $tenant->currency_symbol ?? '₹';
        ActivityLog::log('payment_reversed', "Reversed payment receipt {$payment->invoice_number} of {$currency}".number_format($payment->amount, 2), $payment);

        return back()->with('success', "Receipt {$payment->invoice_number} reversed successfully.");
    }

    public function personalTraining(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $branch = TenantContext::getBranch();
        $tab = $request->get('tab', 'packages');

        // Top KPI Metrics for Packages
        $activePackagesCount = MemberPtPackage::where('tenant_id', $tenant->id)
            ->where('status', 'ACTIVE')
            ->where('end_date', '>=', now()->toDateString())
            ->count();

        $expiring7DaysCount = MemberPtPackage::where('tenant_id', $tenant->id)
            ->where('status', 'ACTIVE')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();

        $revenueMtd = (float) MemberPtPackage::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('paid_amount');

        // Filtered Packages Query
        $packagesQuery = MemberPtPackage::with(['member.branch', 'trainer', 'ptPlan'])
            ->where('tenant_id', $tenant->id)
            ->latest('id');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $packagesQuery->where(function ($q) use ($s) {
                $q->where('package_name', 'like', "%{$s}%")
                    ->orWhereHas('member', function ($mq) use ($s) {
                        $mq->where('first_name', 'like', "%{$s}%")
                            ->orWhere('last_name', 'like', "%{$s}%")
                            ->orWhere('phone', 'like', "%{$s}%")
                            ->orWhere('member_code', 'like', "%{$s}%");
                    })
                    ->orWhereHas('trainer', function ($tq) use ($s) {
                        $tq->where('first_name', 'like', "%{$s}%")
                            ->orWhere('last_name', 'like', "%{$s}%");
                    });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $status = strtoupper($request->status);
            if ($status === 'EXPIRING') {
                $packagesQuery->where('status', 'ACTIVE')
                    ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()]);
            } else {
                $packagesQuery->where('status', $status);
            }
        }

        if ($request->filled('trainer_id') && $request->trainer_id !== 'all') {
            $packagesQuery->where('trainer_id', $request->trainer_id);
        }

        if ($request->boolean('only_expiring')) {
            $packagesQuery->where('status', 'ACTIVE')
                ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()]);
        }

        $packages = $packagesQuery->paginate(15, ['*'], 'packages_page')->withQueryString();

        // PT Sessions KPI & Data (Matching Image 3)
        $todayScheduledCount = PtSession::where('tenant_id', $tenant->id)
            ->whereDate('session_date', now()->toDateString())
            ->where('status', 'SCHEDULED')
            ->count();

        $todayCompletedCount = PtSession::where('tenant_id', $tenant->id)
            ->whereDate('session_date', now()->toDateString())
            ->where('status', 'COMPLETED')
            ->count();

        $thisMonthSessionsCount = PtSession::where('tenant_id', $tenant->id)
            ->whereBetween('session_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->where('status', 'COMPLETED')
            ->count();

        $noShowsMonthCount = PtSession::where('tenant_id', $tenant->id)
            ->whereBetween('session_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->where('status', 'NO_SHOW')
            ->count();

        $upcomingSessions = PtSession::with(['member', 'trainer', 'memberPtPackage'])
            ->where('tenant_id', $tenant->id)
            ->where('status', 'SCHEDULED')
            ->whereDate('session_date', '>=', now()->toDateString())
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->take(10)
            ->get();

        $recentCompletedSessions = PtSession::with(['member', 'trainer', 'memberPtPackage'])
            ->where('tenant_id', $tenant->id)
            ->where('status', 'COMPLETED')
            ->orderByDesc('completed_at')
            ->orderByDesc('session_date')
            ->take(10)
            ->get();

        $allSessions = PtSession::with(['member', 'trainer', 'memberPtPackage'])
            ->where('tenant_id', $tenant->id)
            ->latest('session_date')
            ->latest('start_time')
            ->paginate(15, ['*'], 'sessions_page')
            ->withQueryString();

        // Plans Query
        $plans = PtPlan::where('tenant_id', $tenant->id)
            ->withCount('memberPtPackages')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        // Active Trainers & Members for Dropdowns & Modals
        $trainers = Trainer::where('tenant_id', $tenant->id)
            ->with(['assignedMembers'])
            ->withCount([
                'assignedMembers',
                'ptSessions as today_sessions_count' => function ($q) {
                    $q->whereDate('session_date', now()->toDateString());
                },
            ])
            ->orderBy('first_name')
            ->get();

        $members = Member::where('tenant_id', $tenant->id)
            ->where('status', 'ACTIVE')
            ->with('branch')
            ->orderBy('first_name')
            ->get();

        $activeMemberPackages = MemberPtPackage::where('tenant_id', $tenant->id)
            ->where('status', 'ACTIVE')
            ->where('end_date', '>=', now()->toDateString())
            ->with(['member', 'trainer'])
            ->get();

        $branches = Branch::where('tenant_id', $tenant->id)->get();

        return view('app.personal-training.index', compact(
            'tenant',
            'tab',
            'activePackagesCount',
            'expiring7DaysCount',
            'revenueMtd',
            'todayScheduledCount',
            'todayCompletedCount',
            'thisMonthSessionsCount',
            'noShowsMonthCount',
            'upcomingSessions',
            'recentCompletedSessions',
            'allSessions',
            'packages',
            'plans',
            'trainers',
            'members',
            'activeMemberPackages',
            'branches'
        ));
    }

    public function storePtPlan(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'sort_order' => 'nullable|integer',
            'description' => 'nullable|string|max:1000',
            'total_sessions' => 'required|integer|min:1',
            'validity_days' => 'required|integer|min:1',
            'default_price' => 'required|numeric|min:0',
            'trainer_commission_percent' => 'nullable|numeric|min:0|max:100',
            'sac_code' => 'nullable|string|max:20',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $validated['tenant_id'] = $tenant->id;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['sac_code'] = ! empty($validated['sac_code']) ? trim($validated['sac_code']) : null;
        $validated['gst_rate'] = ! empty($validated['gst_rate']) ? (float) $validated['gst_rate'] : 0.00;
        $validated['price_includes_gst'] = $request->has('price_includes_gst') && $validated['gst_rate'] > 0;
        $validated['is_active'] = $request->has('is_active');
        $validated['includes_gate_pass'] = $request->has('includes_gate_pass');
        $validated['show_on_mobile_app'] = $request->has('show_on_mobile_app');

        $plan = PtPlan::create($validated);

        ActivityLog::log('pt_plan_created', "Created PT Plan '{$plan->name}' with {$plan->total_sessions} sessions", $plan);

        return redirect()->route('app.pt.index', ['tab' => 'plans'])->with('success', "PT Plan '{$plan->name}' created successfully!");
    }

    public function updatePtPlan(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $plan = PtPlan::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'sort_order' => 'nullable|integer',
            'description' => 'nullable|string|max:1000',
            'total_sessions' => 'required|integer|min:1',
            'validity_days' => 'required|integer|min:1',
            'default_price' => 'required|numeric|min:0',
            'trainer_commission_percent' => 'nullable|numeric|min:0|max:100',
            'sac_code' => 'nullable|string|max:20',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['sac_code'] = ! empty($validated['sac_code']) ? trim($validated['sac_code']) : null;
        $validated['gst_rate'] = ! empty($validated['gst_rate']) ? (float) $validated['gst_rate'] : 0.00;
        $validated['price_includes_gst'] = $request->has('price_includes_gst') && $validated['gst_rate'] > 0;
        $validated['is_active'] = $request->has('is_active');
        $validated['includes_gate_pass'] = $request->has('includes_gate_pass');
        $validated['show_on_mobile_app'] = $request->has('show_on_mobile_app');

        $plan->update($validated);

        ActivityLog::log('pt_plan_updated', "Updated PT Plan '{$plan->name}'", $plan);

        return redirect()->route('app.pt.index', ['tab' => 'plans'])->with('success', "PT Plan '{$plan->name}' updated successfully!");
    }

    public function togglePtPlan(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $plan = PtPlan::where('tenant_id', $tenant->id)->findOrFail($id);

        $plan->update(['is_active' => ! $plan->is_active]);
        $statusText = $plan->is_active ? 'activated' : 'deactivated';

        ActivityLog::log('pt_plan_toggled', "PT Plan '{$plan->name}' was {$statusText}", $plan);

        return redirect()->route('app.pt.index', ['tab' => 'plans'])->with('success', "PT Plan '{$plan->name}' {$statusText}.");
    }

    public function deletePtPlan(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $plan = PtPlan::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($plan->memberPtPackages()->where('status', 'ACTIVE')->exists()) {
            return redirect()->route('app.pt.index', ['tab' => 'plans'])->with('error', "Cannot delete PT Plan '{$plan->name}' because it is assigned to active member packages.");
        }

        $name = $plan->name;
        $plan->delete();

        ActivityLog::log('pt_plan_deleted', "Deleted PT Plan '{$name}'");

        return redirect()->route('app.pt.index', ['tab' => 'plans'])->with('success', "PT Plan '{$name}' deleted successfully.");
    }

    public function assignPtPackage(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'pt_plan_id' => 'nullable|exists:pt_plans,id',
            'trainer_id' => 'nullable|exists:trainers,id',
            'package_name' => 'required|string|max:150',
            'total_sessions' => 'required|integer|min:1',
            'validity_days' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:cash,upi,card,netbanking,cheque',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $member = Member::where('tenant_id', $tenant->id)->findOrFail($validated['member_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $validityDays = (int) $validated['validity_days'];
        $endDate = $startDate->copy()->addDays($validityDays);

        $price = (float) $validated['price'];
        $discount = (float) ($validated['discount'] ?? 0);
        $finalAmount = max(0, $price - $discount);
        $paidAmount = (float) ($validated['paid_amount'] ?? 0);

        $package = MemberPtPackage::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $member->branch_id,
            'member_id' => $member->id,
            'trainer_id' => $validated['trainer_id'] ?? null,
            'pt_plan_id' => $validated['pt_plan_id'] ?? null,
            'package_name' => $validated['package_name'],
            'total_sessions' => (int) $validated['total_sessions'],
            'used_sessions' => 0,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'price' => $price,
            'discount' => $discount,
            'final_amount' => $finalAmount,
            'paid_amount' => $paidAmount,
            'status' => 'ACTIVE',
            'notes' => $validated['notes'] ?? null,
        ]);

        // If payment collected, record MemberPayment receipt
        if ($paidAmount > 0) {
            $invoiceNumber = 'RCP-PT-'.date('Ymd').rand(1000, 9999);
            $paymentMethod = $validated['payment_method'] ?? 'upi';

            $payment = MemberPayment::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $member->branch_id,
                'member_id' => $member->id,
                'invoice_number' => $invoiceNumber,
                'amount' => $paidAmount,
                'payment_method' => $paymentMethod,
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'payment_date' => $startDate->toDateString(),
                'received_by_user_id' => auth()->id(),
                'notes' => "PT Package: {$package->package_name}".(! empty($validated['notes']) ? " | {$validated['notes']}" : ''),
            ]);

            ActivityLog::log('pt_payment_received', 'Recorded PT payment of '.($tenant->currency_symbol ?? '₹').number_format($paidAmount, 2)." for {$member->full_name} ({$invoiceNumber})", $payment);
        }

        ActivityLog::log('pt_package_assigned', "Assigned PT Package '{$package->package_name}' to {$member->full_name}", $package);

        return redirect()->route('app.pt.index', ['tab' => 'packages'])->with('success', "Personal Training package '{$package->package_name}' assigned to {$member->full_name} successfully!");
    }

    public function logPtSession(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $package = MemberPtPackage::where('tenant_id', $tenant->id)->with('member')->findOrFail($id);

        $sessionsToLog = (int) $request->input('count', 1);
        $newUsed = min($package->total_sessions, $package->used_sessions + $sessionsToLog);

        $updateData = ['used_sessions' => $newUsed];
        if ($newUsed >= $package->total_sessions) {
            $updateData['status'] = 'COMPLETED';
        }

        $package->update($updateData);

        // Also record a completed session log in pt_sessions table
        PtSession::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $package->branch_id,
            'member_id' => $package->member_id,
            'trainer_id' => $package->trainer_id,
            'member_pt_package_id' => $package->id,
            'session_date' => now()->toDateString(),
            'start_time' => now()->toTimeString(),
            'duration_minutes' => 60,
            'status' => 'COMPLETED',
            'focus_area' => 'PT Workout Session',
            'completed_at' => now(),
        ]);

        ActivityLog::log('pt_session_logged', "Logged {$sessionsToLog} PT session(s) for {$package->member?->full_name} on package '{$package->package_name}' (Used: {$newUsed}/{$package->total_sessions})", $package);

        $statusMsg = $newUsed >= $package->total_sessions ? ' All sessions completed!' : " ({$package->remaining_sessions} remaining)";

        return back()->with('success', "Session logged for {$package->member?->full_name}!{$statusMsg}");
    }

    public function cancelPtPackage(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $package = MemberPtPackage::where('tenant_id', $tenant->id)->findOrFail($id);

        $package->update(['status' => 'CANCELLED']);

        ActivityLog::log('pt_package_cancelled', "Cancelled PT Package '{$package->package_name}' for member ID {$package->member_id}", $package);

        return back()->with('success', "PT Package '{$package->package_name}' cancelled.");
    }

    public function storePtSession(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'trainer_id' => 'nullable|exists:trainers,id',
            'member_pt_package_id' => 'nullable|exists:member_pt_packages,id',
            'session_date' => 'required|date',
            'start_time' => 'required|string',
            'duration_minutes' => 'nullable|integer|min:15|max:180',
            'focus_area' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:500',
        ]);

        $member = Member::where('tenant_id', $tenant->id)->findOrFail($validated['member_id']);

        $session = PtSession::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $member->branch_id,
            'member_id' => $member->id,
            'trainer_id' => $validated['trainer_id'] ?? null,
            'member_pt_package_id' => $validated['member_pt_package_id'] ?? null,
            'session_date' => $validated['session_date'],
            'start_time' => $validated['start_time'],
            'duration_minutes' => (int) ($validated['duration_minutes'] ?? 60),
            'status' => 'SCHEDULED',
            'focus_area' => $validated['focus_area'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        ActivityLog::log('pt_session_scheduled', "Scheduled PT session for {$member->full_name} on {$session->session_date->format('d M Y')}", $session);

        return redirect()->route('app.pt.index', ['tab' => 'sessions'])->with('success', "PT Session scheduled for {$member->full_name} on {$session->session_date->format('d M Y')}!");
    }

    public function completePtSession(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $session = PtSession::where('tenant_id', $tenant->id)->with(['member', 'memberPtPackage'])->findOrFail($id);

        $session->update([
            'status' => 'COMPLETED',
            'completed_at' => now(),
        ]);

        if ($session->memberPtPackage && $session->memberPtPackage->status === 'ACTIVE') {
            $pkg = $session->memberPtPackage;
            $newUsed = min($pkg->total_sessions, $pkg->used_sessions + 1);
            $update = ['used_sessions' => $newUsed];
            if ($newUsed >= $pkg->total_sessions) {
                $update['status'] = 'COMPLETED';
            }
            $pkg->update($update);
        }

        ActivityLog::log('pt_session_completed', "Marked PT session completed for {$session->member?->full_name}", $session);

        return back()->with('success', "PT Session for {$session->member?->full_name} marked as completed!");
    }

    public function updatePtSessionStatus(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $session = PtSession::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:SCHEDULED,COMPLETED,NO_SHOW,CANCELLED',
        ]);

        $session->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'COMPLETED' ? now() : null,
        ]);

        return back()->with('success', "PT Session status updated to {$validated['status']}.");
    }

    public function trainers(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $query = Trainer::where('tenant_id', $tenant->id)
            ->with(['assignedMembers'])
            ->withCount([
                'assignedMembers',
                'ptSessions as today_sessions_count' => function ($q) {
                    $q->whereDate('session_date', now()->toDateString());
                },
            ]);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('specialization', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $trainers = $query->orderBy('first_name')->get();

        $members = Member::where('tenant_id', $tenant->id)
            ->where('status', 'ACTIVE')
            ->with('branch')
            ->orderBy('first_name')
            ->get();

        $branches = Branch::where('tenant_id', $tenant->id)->get();

        return view('app.trainers.index', compact('trainers', 'members', 'tenant', 'branches'));
    }

    public function storeTrainer(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        if ($request->filled('full_name') && ! $request->filled('first_name')) {
            $parts = explode(' ', trim($request->input('full_name')), 2);
            $request->merge([
                'first_name' => $parts[0],
                'last_name' => $parts[1] ?? '',
            ]);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'specialization' => 'nullable|string|max:150',
            'certification' => 'nullable|string|max:150',
            'hourly_rate' => 'nullable|numeric|min:0',
            'salary' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|string|max:50',
            'salary_pay_day' => 'nullable|string|max:50',
            'joining_date' => 'nullable|date',
            'status' => 'nullable|in:ACTIVE,INACTIVE,SUSPENDED',
            'bio' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:3072',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $validated['tenant_id'] = $tenant->id;
        $validated['last_name'] = $validated['last_name'] ?? '';
        $validated['status'] = $validated['status'] ?? 'ACTIVE';
        $validated['salary'] = $validated['salary'] ?? 0.00;
        $validated['salary_type'] = $validated['salary_type'] ?? 'Fixed Monthly';
        $validated['salary_pay_day'] = $validated['salary_pay_day'] ?? '1st of every month';
        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('trainers', 'public');
        }

        $trainer = Trainer::create($validated);

        ActivityLog::log('trainer_created', "Added trainer '{$trainer->full_name}'", $trainer);

        return back()->with('success', "Trainer '{$trainer->full_name}' added successfully!");
    }

    public function updateTrainer(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $trainer = Trainer::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($request->filled('full_name') && ! $request->filled('first_name')) {
            $parts = explode(' ', trim($request->input('full_name')), 2);
            $request->merge([
                'first_name' => $parts[0],
                'last_name' => $parts[1] ?? '',
            ]);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'specialization' => 'nullable|string|max:150',
            'certification' => 'nullable|string|max:150',
            'hourly_rate' => 'nullable|numeric|min:0',
            'salary' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|string|max:50',
            'salary_pay_day' => 'nullable|string|max:50',
            'joining_date' => 'nullable|date',
            'status' => 'nullable|in:ACTIVE,INACTIVE,SUSPENDED',
            'bio' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:3072',
            'assigned_member_ids' => 'nullable|array',
            'assigned_member_ids.*' => 'exists:members,id',
        ]);

        $validated['last_name'] = $validated['last_name'] ?? '';
        $validated['status'] = $validated['status'] ?? 'ACTIVE';
        $validated['salary'] = $validated['salary'] ?? 0.00;
        $validated['salary_type'] = $validated['salary_type'] ?? 'Fixed Monthly';
        $validated['salary_pay_day'] = $validated['salary_pay_day'] ?? '1st of every month';
        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('photo')) {
            if ($trainer->photo_path) {
                Storage::disk('public')->delete($trainer->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('trainers', 'public');
        }

        $trainer->update($validated);

        // Update assigned members
        if ($request->has('assigned_member_ids_submitted')) {
            $assignedIds = $request->input('assigned_member_ids', []);

            // Unassign members previously assigned to this trainer who are not in list
            Member::where('tenant_id', $tenant->id)
                ->where('trainer_id', $trainer->id)
                ->whereNotIn('id', $assignedIds)
                ->update(['trainer_id' => null]);

            // Assign selected members
            if (! empty($assignedIds)) {
                Member::where('tenant_id', $tenant->id)
                    ->whereIn('id', $assignedIds)
                    ->update(['trainer_id' => $trainer->id]);
            }
        }

        ActivityLog::log('trainer_updated', "Updated trainer details for '{$trainer->full_name}'", $trainer);

        return back()->with('success', "Trainer '{$trainer->full_name}' updated successfully!");
    }

    public function deleteTrainer(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $trainer = Trainer::where('tenant_id', $tenant->id)->findOrFail($id);

        // Unlink assigned members
        Member::where('tenant_id', $tenant->id)
            ->where('trainer_id', $trainer->id)
            ->update(['trainer_id' => null]);

        $name = $trainer->full_name;
        $trainer->delete();

        ActivityLog::log('trainer_deleted', "Deleted trainer '{$name}'");

        return back()->with('success', "Trainer '{$name}' deleted successfully.");
    }

    public function assignTrainerMembers(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $trainer = Trainer::where('tenant_id', $tenant->id)->findOrFail($id);

        $memberIds = $request->input('member_ids', []);

        Member::where('tenant_id', $tenant->id)
            ->whereIn('id', $memberIds)
            ->update(['trainer_id' => $trainer->id]);

        return back()->with('success', 'Members assigned to trainer successfully!');
    }

    public function classes(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $branch = TenantContext::getBranch() ?? auth()->user()->branch;

        $classes = GymClass::where('tenant_id', $tenant->id)
            ->with(['instructor', 'schedules.trainer', 'schedules.bookings.member'])
            ->withCount(['schedules'])
            ->latest()
            ->get();

        $todayDay = strtolower(now()->format('l'));

        $todaySchedules = ClassSchedule::where('tenant_id', $tenant->id)
            ->where('day_of_week', $todayDay)
            ->where('is_active', true)
            ->with(['gymClass.instructor', 'trainer', 'bookings.member'])
            ->withCount(['bookings' => function ($q) {
                $q->whereDate('booking_date', now()->toDateString())
                    ->whereIn('status', ['BOOKED', 'ATTENDED']);
            }])
            ->orderBy('start_time')
            ->get();

        $allSchedules = ClassSchedule::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->with(['gymClass.instructor', 'trainer', 'bookings.member'])
            ->orderBy('start_time')
            ->get();

        $bookingRequests = ClassBooking::where('tenant_id', $tenant->id)
            ->with(['classSchedule.gymClass', 'member', 'classSchedule.trainer'])
            ->latest()
            ->take(50)
            ->get();

        $trainers = Trainer::where('tenant_id', $tenant->id)
            ->where('status', 'ACTIVE')
            ->orderBy('first_name')
            ->get();

        $members = Member::where('tenant_id', $tenant->id)
            ->where('status', 'ACTIVE')
            ->orderBy('first_name')
            ->get();

        $branches = Branch::where('tenant_id', $tenant->id)->get();

        return view('app.classes.index', compact(
            'classes',
            'todaySchedules',
            'allSchedules',
            'bookingRequests',
            'trainers',
            'members',
            'branches',
            'tenant'
        ));
    }

    public function storeClass(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $branch = TenantContext::getBranch() ?? auth()->user()->branch ?? Branch::where('tenant_id', $tenant->id)->first();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'class_type' => 'required|string|max:100',
            'instructor_id' => 'nullable|exists:trainers,id',
            'fee' => 'nullable|numeric|min:0',
            'validity_days' => 'nullable|integer|min:1',
            'total_sessions' => 'nullable|integer|min:1',
            'cancellation_hours' => 'nullable|integer|min:0',
            'sac_code' => 'nullable|string|max:20',
            'gst_rate' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:1',
            'duration_minutes' => 'nullable|integer|min:1',
            'room_location' => 'nullable|string|max:100',
            'status' => 'nullable|in:ACTIVE,INACTIVE',
            'description' => 'nullable|string|max:1000',
            'thumbnail' => 'nullable|image|max:3072',
            'schedules' => 'nullable|array',
            'schedules.*.day_of_week' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'schedules.*.start_time' => 'nullable|string',
            'schedules.*.end_time' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $tenant->id;
        $validated['branch_id'] = $branch?->id ?? 1;
        $validated['fee'] = $validated['fee'] ?? 0.00;
        $validated['validity_days'] = $validated['validity_days'] ?? 30;
        $validated['cancellation_hours'] = $validated['cancellation_hours'] ?? 4;
        $validated['sac_code'] = ! empty($validated['sac_code']) ? trim($validated['sac_code']) : null;
        $validated['gst_rate'] = ! empty($validated['gst_rate']) ? (float) $validated['gst_rate'] : 0.00;
        $validated['capacity'] = $validated['capacity'] ?? 20;
        $validated['duration_minutes'] = $validated['duration_minutes'] ?? 60;
        $validated['status'] = $validated['status'] ?? 'ACTIVE';
        $validated['is_active'] = ($validated['status'] === 'ACTIVE');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['price_includes_gst'] = $request->has('price_includes_gst') && $validated['gst_rate'] > 0;

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail_path'] = $request->file('thumbnail')->store('classes', 'public');
        }

        $schedulesInput = $request->input('schedules', []);
        unset($validated['schedules']);

        $gymClass = GymClass::create($validated);

        if (! empty($schedulesInput) && is_array($schedulesInput)) {
            foreach ($schedulesInput as $sch) {
                if (! empty($sch['day_of_week']) && ! empty($sch['start_time'])) {
                    $startTime = $sch['start_time'];
                    $endTime = $sch['end_time'] ?? null;
                    if (! $endTime) {
                        $endTime = Carbon::createFromTimeString($startTime)->addMinutes($gymClass->duration_minutes)->format('H:i');
                    }

                    ClassSchedule::create([
                        'tenant_id' => $tenant->id,
                        'branch_id' => $gymClass->branch_id,
                        'gym_class_id' => $gymClass->id,
                        'trainer_id' => $gymClass->instructor_id,
                        'day_of_week' => strtolower($sch['day_of_week']),
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'room_or_studio' => null,
                        'is_active' => true,
                    ]);
                }
            }
        }

        ActivityLog::log('class_created', "Created group class '{$gymClass->name}'", $gymClass);

        return back()->with('success', "Class '{$gymClass->name}' created successfully!");
    }

    public function updateClass(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $gymClass = GymClass::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'class_type' => 'required|string|max:100',
            'instructor_id' => 'nullable|exists:trainers,id',
            'fee' => 'nullable|numeric|min:0',
            'validity_days' => 'nullable|integer|min:1',
            'total_sessions' => 'nullable|integer|min:1',
            'cancellation_hours' => 'nullable|integer|min:0',
            'sac_code' => 'nullable|string|max:20',
            'gst_rate' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:1',
            'duration_minutes' => 'nullable|integer|min:1',
            'room_location' => 'nullable|string|max:100',
            'status' => 'nullable|in:ACTIVE,INACTIVE',
            'description' => 'nullable|string|max:1000',
            'thumbnail' => 'nullable|image|max:3072',
            'schedules' => 'nullable|array',
            'schedules.*.day_of_week' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'schedules.*.start_time' => 'nullable|string',
            'schedules.*.end_time' => 'nullable|string',
        ]);

        $validated['fee'] = $validated['fee'] ?? 0.00;
        $validated['validity_days'] = $validated['validity_days'] ?? 30;
        $validated['cancellation_hours'] = $validated['cancellation_hours'] ?? 4;
        $validated['sac_code'] = ! empty($validated['sac_code']) ? trim($validated['sac_code']) : null;
        $validated['gst_rate'] = ! empty($validated['gst_rate']) ? (float) $validated['gst_rate'] : 0.00;
        $validated['capacity'] = $validated['capacity'] ?? 20;
        $validated['duration_minutes'] = $validated['duration_minutes'] ?? 60;
        $validated['status'] = $validated['status'] ?? 'ACTIVE';
        $validated['is_active'] = ($validated['status'] === 'ACTIVE');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['price_includes_gst'] = $request->has('price_includes_gst') && $validated['gst_rate'] > 0;

        if ($request->hasFile('thumbnail')) {
            if ($gymClass->thumbnail_path) {
                Storage::disk('public')->delete($gymClass->thumbnail_path);
            }
            $validated['thumbnail_path'] = $request->file('thumbnail')->store('classes', 'public');
        }

        $schedulesInput = $request->input('schedules', []);
        unset($validated['schedules']);

        $gymClass->update($validated);

        // Sync schedules if provided
        if ($request->has('schedules_submitted')) {
            ClassSchedule::where('gym_class_id', $gymClass->id)->delete();
            if (! empty($schedulesInput) && is_array($schedulesInput)) {
                foreach ($schedulesInput as $sch) {
                    if (! empty($sch['day_of_week']) && ! empty($sch['start_time'])) {
                        $startTime = $sch['start_time'];
                        $endTime = $sch['end_time'] ?? null;
                        if (! $endTime) {
                            $endTime = Carbon::createFromTimeString($startTime)->addMinutes($gymClass->duration_minutes)->format('H:i');
                        }

                        ClassSchedule::create([
                            'tenant_id' => $tenant->id,
                            'branch_id' => $gymClass->branch_id,
                            'gym_class_id' => $gymClass->id,
                            'trainer_id' => $gymClass->instructor_id,
                            'day_of_week' => strtolower($sch['day_of_week']),
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'room_or_studio' => null,
                            'is_active' => true,
                        ]);
                    }
                }
            }
        }

        ActivityLog::log('class_updated', "Updated group class '{$gymClass->name}'", $gymClass);

        return back()->with('success', "Class '{$gymClass->name}' updated successfully!");
    }

    public function deleteClass(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $gymClass = GymClass::where('tenant_id', $tenant->id)->findOrFail($id);

        $name = $gymClass->name;
        if ($gymClass->thumbnail_path) {
            Storage::disk('public')->delete($gymClass->thumbnail_path);
        }
        $gymClass->delete();

        ActivityLog::log('class_deleted', "Deleted group class '{$name}'");

        return back()->with('success', "Class '{$name}' deleted successfully.");
    }

    public function bookClassSchedule(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $schedule = ClassSchedule::where('tenant_id', $tenant->id)->with('gymClass')->findOrFail($id);

        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'booking_date' => 'nullable|date',
        ]);

        $bookingDate = $validated['booking_date'] ?? now()->toDateString();

        $booking = ClassBooking::firstOrCreate([
            'tenant_id' => $tenant->id,
            'branch_id' => $schedule->branch_id,
            'class_schedule_id' => $schedule->id,
            'member_id' => $validated['member_id'],
            'booking_date' => $bookingDate,
        ], [
            'status' => 'BOOKED',
        ]);

        return back()->with('success', "Booking confirmed for class '{$schedule->gymClass->name}'!");
    }

    public function updateClassBookingStatus(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $booking = ClassBooking::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:BOOKED,ATTENDED,CANCELLED,NO_SHOW',
        ]);

        $booking->update(['status' => $validated['status']]);

        return back()->with('success', "Booking status updated to {$validated['status']}.");
    }

    public function workouts(): View
    {
        $plans = WorkoutPlan::with(['member', 'trainer', 'exercises'])->latest()->paginate(15);
        $members = Member::where('status', 'ACTIVE')->orderBy('first_name')->get();
        $trainers = Trainer::where('status', 'ACTIVE')->orderBy('first_name')->get();

        return view('app.workouts.index', compact('plans', 'members', 'trainers'));
    }

    public function storeWorkout(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'member_id' => 'nullable|exists:members,id',
            'trainer_id' => 'nullable|exists:trainers,id',
            'goal' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_template' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'exercises' => 'nullable|array',
            'exercises.*.exercise_name' => 'required_with:exercises|string|max:255',
            'exercises.*.day' => 'nullable|string',
            'exercises.*.sets' => 'nullable|integer|min:1',
            'exercises.*.reps' => 'nullable|string',
            'exercises.*.weight' => 'nullable|string',
            'exercises.*.rest_seconds' => 'nullable|integer',
            'exercises.*.notes' => 'nullable|string',
        ]);

        $isTemplate = $request->boolean('is_template') || empty($validated['member_id']);

        $workout = WorkoutPlan::create([
            'tenant_id' => $tenant->id,
            'member_id' => $isTemplate ? null : ($validated['member_id'] ?? null),
            'trainer_id' => $validated['trainer_id'] ?? null,
            'title' => $validated['title'],
            'goal' => $validated['goal'] ?? 'General Fitness',
            'level' => $validated['level'] ?? 'Beginner',
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_template' => $isTemplate,
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! empty($validated['exercises']) && is_array($validated['exercises'])) {
            $order = 1;
            foreach ($validated['exercises'] as $ex) {
                if (! empty($ex['exercise_name'])) {
                    WorkoutExercise::create([
                        'workout_plan_id' => $workout->id,
                        'day' => $ex['day'] ?? 'Day 1',
                        'exercise_name' => $ex['exercise_name'],
                        'sets' => $ex['sets'] ?? 3,
                        'reps' => $ex['reps'] ?? '10-12',
                        'weight' => $ex['weight'] ?? null,
                        'rest_seconds' => $ex['rest_seconds'] ?? 60,
                        'notes' => $ex['notes'] ?? null,
                        'sort_order' => $order++,
                    ]);
                }
            }
        }

        ActivityLog::log('workout_created', "Created workout routine '{$workout->title}'", $workout);

        return back()->with('success', "Workout routine '{$workout->title}' created successfully!");
    }

    public function deleteWorkout(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $workout = WorkoutPlan::where('tenant_id', $tenant->id)->findOrFail($id);

        $title = $workout->title;
        WorkoutExercise::where('workout_plan_id', $workout->id)->delete();
        $workout->delete();

        ActivityLog::log('workout_deleted', "Deleted workout routine '{$title}'");

        return back()->with('success', "Workout routine '{$title}' deleted successfully!");
    }

    public function diets(): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $plans = DietPlan::with(['member', 'trainer', 'meals'])->latest()->paginate(15);
        $members = Member::where('status', 'ACTIVE')->orderBy('first_name')->get();
        $trainers = Trainer::where('status', 'ACTIVE')->orderBy('first_name')->get();

        $totalPlans = DietPlan::count();
        $memberPlansCount = DietPlan::whereNotNull('member_id')->where('is_template', false)->count();
        $templatePlansCount = DietPlan::where('is_template', true)->count();

        return view('app.diets.index', compact('plans', 'members', 'trainers', 'tenant', 'totalPlans', 'memberPlansCount', 'templatePlansCount'));
    }

    public function storeDiet(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'member_id' => 'nullable|exists:members,id',
            'trainer_id' => 'nullable|exists:trainers,id',
            'is_template' => 'nullable|boolean',
            'daily_calories' => 'nullable|integer|min:0|max:15000',
            'protein_grams' => 'nullable|integer|min:0|max:1000',
            'carbs_grams' => 'nullable|integer|min:0|max:1500',
            'fat_grams' => 'nullable|integer|min:0|max:1000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'guidelines' => 'nullable|string',
            'meals' => 'nullable|array',
            'meals.*.meal_type' => 'nullable|string|in:breakfast,morning_snack,lunch,evening_snack,dinner,post_workout',
            'meals.*.recommended_time' => 'nullable|string',
            'meals.*.meal_name' => 'nullable|string|max:255',
            'meals.*.items_description' => 'nullable|string',
            'meals.*.calories' => 'nullable|integer|min:0',
        ]);

        $isTemplate = $request->boolean('is_template') || empty($validated['member_id']);

        $plan = DietPlan::create([
            'tenant_id' => $tenant->id,
            'member_id' => $isTemplate ? null : ($validated['member_id'] ?? null),
            'trainer_id' => $validated['trainer_id'] ?? null,
            'title' => $validated['title'],
            'daily_calories' => $validated['daily_calories'] ?? null,
            'protein_grams' => $validated['protein_grams'] ?? null,
            'carbs_grams' => $validated['carbs_grams'] ?? null,
            'fat_grams' => $validated['fat_grams'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_template' => $isTemplate,
            'guidelines' => $validated['guidelines'] ?? null,
        ]);

        if (! empty($validated['meals']) && is_array($validated['meals'])) {
            $order = 1;
            foreach ($validated['meals'] as $mealData) {
                if (! empty($mealData['meal_name']) || ! empty($mealData['items_description'])) {
                    DietMeal::create([
                        'diet_plan_id' => $plan->id,
                        'meal_type' => $mealData['meal_type'] ?? 'breakfast',
                        'recommended_time' => $mealData['recommended_time'] ?? null,
                        'meal_name' => $mealData['meal_name'] ?? 'Meal',
                        'items_description' => $mealData['items_description'] ?? null,
                        'calories' => $mealData['calories'] ?? null,
                        'sort_order' => $order++,
                    ]);
                }
            }
        }

        ActivityLog::log('diet_created', "Created diet plan '{$plan->title}'", $plan);

        return back()->with('success', "Diet plan '{$plan->title}' created successfully!");
    }

    public function updateDiet(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $plan = DietPlan::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'member_id' => 'nullable|exists:members,id',
            'trainer_id' => 'nullable|exists:trainers,id',
            'is_template' => 'nullable|boolean',
            'daily_calories' => 'nullable|integer|min:0|max:15000',
            'protein_grams' => 'nullable|integer|min:0|max:1000',
            'carbs_grams' => 'nullable|integer|min:0|max:1500',
            'fat_grams' => 'nullable|integer|min:0|max:1000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'guidelines' => 'nullable|string',
            'meals' => 'nullable|array',
            'meals.*.meal_type' => 'nullable|string|in:breakfast,morning_snack,lunch,evening_snack,dinner,post_workout',
            'meals.*.recommended_time' => 'nullable|string',
            'meals.*.meal_name' => 'nullable|string|max:255',
            'meals.*.items_description' => 'nullable|string',
            'meals.*.calories' => 'nullable|integer|min:0',
        ]);

        $isTemplate = $request->boolean('is_template') || empty($validated['member_id']);

        $plan->update([
            'member_id' => $isTemplate ? null : ($validated['member_id'] ?? null),
            'trainer_id' => $validated['trainer_id'] ?? null,
            'title' => $validated['title'],
            'daily_calories' => $validated['daily_calories'] ?? null,
            'protein_grams' => $validated['protein_grams'] ?? null,
            'carbs_grams' => $validated['carbs_grams'] ?? null,
            'fat_grams' => $validated['fat_grams'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_template' => $isTemplate,
            'guidelines' => $validated['guidelines'] ?? null,
        ]);

        // Re-sync meals
        DietMeal::where('diet_plan_id', $plan->id)->delete();
        if (! empty($validated['meals']) && is_array($validated['meals'])) {
            $order = 1;
            foreach ($validated['meals'] as $mealData) {
                if (! empty($mealData['meal_name']) || ! empty($mealData['items_description'])) {
                    DietMeal::create([
                        'diet_plan_id' => $plan->id,
                        'meal_type' => $mealData['meal_type'] ?? 'breakfast',
                        'recommended_time' => $mealData['recommended_time'] ?? null,
                        'meal_name' => $mealData['meal_name'] ?? 'Meal',
                        'items_description' => $mealData['items_description'] ?? null,
                        'calories' => $mealData['calories'] ?? null,
                        'sort_order' => $order++,
                    ]);
                }
            }
        }

        ActivityLog::log('diet_updated', "Updated diet plan '{$plan->title}'", $plan);

        return back()->with('success', "Diet plan '{$plan->title}' updated successfully!");
    }

    public function deleteDiet(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $plan = DietPlan::where('tenant_id', $tenant->id)->findOrFail($id);

        $title = $plan->title;
        DietMeal::where('diet_plan_id', $plan->id)->delete();
        $plan->delete();

        ActivityLog::log('diet_deleted', "Deleted diet plan '{$title}'");

        return back()->with('success', "Diet plan '{$title}' deleted successfully!");
    }

    public function seedStarterDiets(): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $presets = [
            [
                'title' => 'Fat Loss & Shred (1,600 kcal) - High Protein',
                'daily_calories' => 1600,
                'protein_grams' => 140,
                'carbs_grams' => 150,
                'fat_grams' => 45,
                'is_template' => true,
                'guidelines' => 'Drink at least 3.5 to 4 liters of water daily. Avoid added sugars, sugary beverages, and refined flour. Maintain consistent sleep 7-8 hours.',
                'meals' => [
                    [
                        'meal_type' => 'breakfast',
                        'recommended_time' => '08:00',
                        'meal_name' => 'Oats Bowl & Egg Whites',
                        'items_description' => '40g rolled oats cooked in water/almond milk, 4 boiled egg whites, 1 pinch cinnamon, 5 almonds',
                        'calories' => 350,
                    ],
                    [
                        'meal_type' => 'morning_snack',
                        'recommended_time' => '11:00',
                        'meal_name' => 'Fruit & Green Tea',
                        'items_description' => '1 medium green apple or seasonal papaya (150g) + 1 cup warm green tea',
                        'calories' => 110,
                    ],
                    [
                        'meal_type' => 'lunch',
                        'recommended_time' => '13:30',
                        'meal_name' => 'Grilled Chicken / Soya Bowl',
                        'items_description' => '100g cooked brown rice, 150g grilled chicken breast or air-fried tofu/soya chunks, 1 bowl cucumber tomato salad',
                        'calories' => 520,
                    ],
                    [
                        'meal_type' => 'evening_snack',
                        'recommended_time' => '17:00',
                        'meal_name' => 'Pre-Workout Energizer',
                        'items_description' => '1 scoop whey protein in water or 1 cup black coffee + 1 slice whole wheat toast with 1 tsp peanut butter',
                        'calories' => 200,
                    ],
                    [
                        'meal_type' => 'dinner',
                        'recommended_time' => '20:30',
                        'meal_name' => 'Light Clean Dinner',
                        'items_description' => '1-2 whole wheat phulkas, 1 bowl yellow dal, 100g sautéed broccoli/zucchini or grilled paneer',
                        'calories' => 420,
                    ],
                ],
            ],
            [
                'title' => 'Lean Muscle Mass (2,500 kcal) - Hypertrophy',
                'daily_calories' => 2500,
                'protein_grams' => 180,
                'carbs_grams' => 280,
                'fat_grams' => 70,
                'is_template' => true,
                'guidelines' => 'Eat every 3-3.5 hours. Ensure 5g creatine monohydrate with post-workout meal. Hydrate with minimum 4 liters daily.',
                'meals' => [
                    [
                        'meal_type' => 'breakfast',
                        'recommended_time' => '08:30',
                        'meal_name' => 'Power Breakfast & Shake',
                        'items_description' => '3 whole eggs + 2 egg whites omelette, 2 multigrain bread slices, 1 banana with 1 glass milk',
                        'calories' => 600,
                    ],
                    [
                        'meal_type' => 'morning_snack',
                        'recommended_time' => '11:30',
                        'meal_name' => 'Nuts & Greek Yogurt',
                        'items_description' => '150g Greek yogurt / curd, 20g mixed walnuts and almonds, 1 tsp chia seeds',
                        'calories' => 280,
                    ],
                    [
                        'meal_type' => 'lunch',
                        'recommended_time' => '13:45',
                        'meal_name' => 'High Carb & Protein Lunch Bowl',
                        'items_description' => '200g basmati or brown rice, 180g roasted chicken or paneer, 1 bowl dal tadka, fresh salad',
                        'calories' => 750,
                    ],
                    [
                        'meal_type' => 'post_workout',
                        'recommended_time' => '18:00',
                        'meal_name' => 'Anabolic Window Recovery',
                        'items_description' => '1.5 scoops whey isolate with chilled water, 1 large banana, 2 dates',
                        'calories' => 320,
                    ],
                    [
                        'meal_type' => 'dinner',
                        'recommended_time' => '21:00',
                        'meal_name' => 'Night Recovery Fuel',
                        'items_description' => '3 whole wheat rotis, 150g fish fillet / paneer bhurji, 1 big bowl mixed vegetable sabzi',
                        'calories' => 550,
                    ],
                ],
            ],
            [
                'title' => 'Pure Vegetarian Fitness (2,000 kcal)',
                'daily_calories' => 2000,
                'protein_grams' => 130,
                'carbs_grams' => 230,
                'fat_grams' => 60,
                'is_template' => true,
                'guidelines' => 'Focus on combining legumes with cereals for complete amino acid profiles. Add lemon to meals to boost non-heme iron absorption.',
                'meals' => [
                    [
                        'meal_type' => 'breakfast',
                        'recommended_time' => '08:30',
                        'meal_name' => 'Besan Chilla & Sprouts',
                        'items_description' => '2 paneer-stuffed besan chillas, 1 bowl steamed moong sprouts with lemon & chaat masala, green chutney',
                        'calories' => 450,
                    ],
                    [
                        'meal_type' => 'morning_snack',
                        'recommended_time' => '11:30',
                        'meal_name' => 'Nutty Fruit Mix',
                        'items_description' => '1 apple, 15g soaked almonds, 5 walnuts',
                        'calories' => 220,
                    ],
                    [
                        'meal_type' => 'lunch',
                        'recommended_time' => '13:30',
                        'meal_name' => 'Dal, Paneer & Brown Rice Bowl',
                        'items_description' => '150g brown rice or 2 multigrain rotis, 150g low-fat paneer curry, 1 bowl rajma or chana, crunchy salad',
                        'calories' => 620,
                    ],
                    [
                        'meal_type' => 'evening_snack',
                        'recommended_time' => '17:30',
                        'meal_name' => 'Roasted Chana & Whey Shake',
                        'items_description' => '1 scoop plant/whey protein, 30g roasted roasted chana (Bengal gram)',
                        'calories' => 260,
                    ],
                    [
                        'meal_type' => 'dinner',
                        'recommended_time' => '20:30',
                        'meal_name' => 'Soya / Tofu Curry & Rotis',
                        'items_description' => '2 whole wheat phulkas, 100g nutri soya chunks or tofu in light tomato gravy, cucumber salad',
                        'calories' => 450,
                    ],
                ],
            ],
        ];

        foreach ($presets as $preset) {
            $plan = DietPlan::create([
                'tenant_id' => $tenant->id,
                'member_id' => null,
                'trainer_id' => null,
                'title' => $preset['title'],
                'daily_calories' => $preset['daily_calories'],
                'protein_grams' => $preset['protein_grams'],
                'carbs_grams' => $preset['carbs_grams'],
                'fat_grams' => $preset['fat_grams'],
                'is_template' => true,
                'guidelines' => $preset['guidelines'],
            ]);

            $order = 1;
            foreach ($preset['meals'] as $meal) {
                DietMeal::create([
                    'diet_plan_id' => $plan->id,
                    'meal_type' => $meal['meal_type'],
                    'recommended_time' => $meal['recommended_time'],
                    'meal_name' => $meal['meal_name'],
                    'items_description' => $meal['items_description'],
                    'calories' => $meal['calories'],
                    'sort_order' => $order++,
                ]);
            }
        }

        ActivityLog::log('diet_seeded', 'Seeded starter diet templates');

        return back()->with('success', 'Starter diet templates created successfully! You can now assign them or send them via WhatsApp.');
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

    // Staff & Team Management (for Gym Owners & Managers)
    public function staff(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $query = User::where('tenant_id', $tenant->id)->with('branches')->latest('id');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $staffMembers = $query->paginate(15)->withQueryString();

        // Aggregates
        $allTenantUsers = User::where('tenant_id', $tenant->id)->get();
        $totalStaff = $allTenantUsers->count();
        $activeStaff = $allTenantUsers->where('status', 'ACTIVE')->count();
        $managerStaff = $allTenantUsers->whereIn('role', ['gym_manager', 'receptionist'])->count();
        $trainerStaff = $allTenantUsers->where('role', 'trainer')->count();
        $totalMonthlySalary = $allTenantUsers->sum(function ($u) {
            $salary = $u->metadata['monthly_salary'] ?? 0;

            return is_numeric($salary) ? (float) $salary : 0;
        });

        $branches = $tenant->branches ?? Branch::all();

        return view('app.staff.index', compact(
            'staffMembers',
            'branches',
            'totalStaff',
            'activeStaff',
            'managerStaff',
            'trainerStaff',
            'totalMonthlySalary'
        ));
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150|unique:users,email',
            'phone' => 'required|string|max:30',
            'role' => 'required|string|in:gym_manager,receptionist,trainer,accountant,staff',
            'status' => 'required|in:ACTIVE,INACTIVE,SUSPENDED',
            'can_login' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
            'employee_id' => 'nullable|string|max:50',
            'device_emp_no' => 'nullable|string|max:50',
            'maid_id' => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'employee_category' => 'nullable|string|max:50',
            'joining_date' => 'nullable|date',
            'monthly_salary' => 'nullable|numeric|min:0',
            'payout_type' => 'nullable|string|max:50',
            'gender' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'anniversary' => 'nullable|date',
            'pan_card' => 'nullable|string|max:50',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:50',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $canLogin = $request->boolean('can_login', true);
        $empId = ! empty($validated['employee_id']) ? $validated['employee_id'] : ('EMP'.str_pad((User::where('tenant_id', $tenant->id)->count() + 1), 3, '0', STR_PAD_LEFT));

        $metadata = [
            'employee_id' => $empId,
            'device_emp_no' => $validated['device_emp_no'] ?? null,
            'maid_id' => $validated['maid_id'] ?? null,
            'designation' => $validated['designation'] ?? null,
            'department' => $validated['department'] ?? null,
            'employee_category' => $validated['employee_category'] ?? null,
            'joining_date' => $validated['joining_date'] ?? null,
            'monthly_salary' => $validated['monthly_salary'] ?? null,
            'payout_type' => $validated['payout_type'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'anniversary' => $validated['anniversary'] ?? null,
            'pan_card' => $validated['pan_card'] ?? null,
            'bank_account_no' => $validated['bank_account_no'] ?? null,
            'bank_ifsc' => $validated['bank_ifsc'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'can_login' => $canLogin,
        ];

        $pwd = ! empty($validated['password']) ? Hash::make($validated['password']) : Hash::make(Str::random(12));

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'password' => $pwd,
            'metadata' => $metadata,
        ]);

        if (! empty($validated['branches'])) {
            $user->branches()->sync($validated['branches']);
        }

        // If trainer role, auto-create or link Trainer record
        if ($validated['role'] === 'trainer') {
            $nameParts = explode(' ', $validated['name'], 2);
            Trainer::firstOrCreate(
                ['tenant_id' => $tenant->id, 'phone' => $validated['phone']],
                [
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? 'Trainer',
                    'email' => $validated['email'] ?? null,
                    'specialization' => $validated['designation'] ?? 'Fitness Coach',
                    'status' => 'ACTIVE',
                ]
            );
        }

        ActivityLog::log('staff_created', "Added new staff member '{$user->name}' ({$empId}) with role '{$user->role}'");

        return back()->with('success', "Staff member '{$user->name}' ({$empId}) added successfully!");
    }

    public function updateStaff(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $user = User::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150|unique:users,email,'.$user->id,
            'phone' => 'required|string|max:30',
            'role' => 'required|string|in:gym_owner,gym_manager,receptionist,trainer,accountant,staff',
            'status' => 'required|in:ACTIVE,INACTIVE,SUSPENDED',
            'can_login' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
            'employee_id' => 'nullable|string|max:50',
            'device_emp_no' => 'nullable|string|max:50',
            'maid_id' => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'employee_category' => 'nullable|string|max:50',
            'joining_date' => 'nullable|date',
            'monthly_salary' => 'nullable|numeric|min:0',
            'payout_type' => 'nullable|string|max:50',
            'gender' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'anniversary' => 'nullable|date',
            'pan_card' => 'nullable|string|max:50',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:50',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $canLogin = $request->boolean('can_login', true);
        $currentMeta = $user->metadata ?? [];
        $empId = ! empty($validated['employee_id']) ? $validated['employee_id'] : ($currentMeta['employee_id'] ?? ('EMP'.str_pad($user->id, 3, '0', STR_PAD_LEFT)));

        $metadata = array_merge($currentMeta, [
            'employee_id' => $empId,
            'device_emp_no' => $validated['device_emp_no'] ?? null,
            'maid_id' => $validated['maid_id'] ?? null,
            'designation' => $validated['designation'] ?? null,
            'department' => $validated['department'] ?? null,
            'employee_category' => $validated['employee_category'] ?? null,
            'joining_date' => $validated['joining_date'] ?? null,
            'monthly_salary' => $validated['monthly_salary'] ?? null,
            'payout_type' => $validated['payout_type'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'anniversary' => $validated['anniversary'] ?? null,
            'pan_card' => $validated['pan_card'] ?? null,
            'bank_account_no' => $validated['bank_account_no'] ?? null,
            'bank_ifsc' => $validated['bank_ifsc'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'can_login' => $canLogin,
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
            'metadata' => $metadata,
        ];

        // Do not change role of primary gym owner
        if ($user->role !== 'gym_owner') {
            $updateData['role'] = $validated['role'];
        }

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        if (isset($validated['branches'])) {
            $user->branches()->sync($validated['branches']);
        }

        ActivityLog::log('staff_updated', "Updated staff member details for '{$user->name}'");

        return back()->with('success', "Staff member '{$user->name}' updated successfully!");
    }

    public function deleteStaff(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $user = User::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own logged-in account.');
        }

        if ($user->role === 'gym_owner') {
            return back()->with('error', 'The primary Gym Owner account cannot be deleted.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::log('staff_deleted', "Deleted staff member '{$name}'");

        return back()->with('success', "Staff member '{$name}' deleted successfully.");
    }

    public function toggleStaffStatus(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $user = User::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change the status of your own account.');
        }

        $newStatus = $user->status === 'ACTIVE' ? 'SUSPENDED' : 'ACTIVE';
        $user->update(['status' => $newStatus]);

        ActivityLog::log('staff_status_changed', "Changed status of staff '{$user->name}' to {$newStatus}");

        return back()->with('success', "Status for '{$user->name}' changed to {$newStatus}.");
    }

    public function resetStaffPassword(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $user = User::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::log('staff_password_reset', "Reset password for staff member '{$user->name}'");

        return back()->with('success', "Password for '{$user->name}' updated successfully!");
    }

    public function services(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        // Auto-seed starter services if empty for this gym
        if (GymService::where('tenant_id', $tenant->id)->count() === 0) {
            $starterServices = [
                [
                    'name' => 'Body Massage',
                    'amount' => 600,
                    'duration_minutes' => 60,
                    'timeslot_availability' => '10:00 AM - 8:00 PM',
                    'description' => 'Professional therapeutic massage to help with muscle recovery and relaxation.',
                    'status' => 'active',
                    'is_visible_in_portal' => true,
                    'is_locker_service' => false,
                    'is_session_countable' => false,
                    'session_count' => 1,
                ],
                [
                    'name' => 'Locker Rental',
                    'amount' => 300,
                    'duration_minutes' => 0,
                    'timeslot_availability' => 'Monthly',
                    'description' => 'Secure personal locker for your belongings during workouts.',
                    'status' => 'active',
                    'is_visible_in_portal' => false,
                    'is_locker_service' => true,
                    'is_session_countable' => false,
                    'session_count' => 1,
                ],
                [
                    'name' => 'Nutrition Consultation',
                    'amount' => 350,
                    'duration_minutes' => 45,
                    'timeslot_availability' => 'By Appointment',
                    'description' => 'Personalized diet plans and nutrition guidance from our certified nutritionists.',
                    'status' => 'active',
                    'is_visible_in_portal' => true,
                    'is_locker_service' => false,
                    'is_session_countable' => false,
                    'session_count' => 1,
                ],
                [
                    'name' => 'Personal Training',
                    'amount' => 500,
                    'duration_minutes' => 60,
                    'timeslot_availability' => 'By Appointment',
                    'description' => 'One-on-one training sessions with certified fitness experts tailored to your goals.',
                    'status' => 'active',
                    'is_visible_in_portal' => true,
                    'is_locker_service' => false,
                    'is_session_countable' => false,
                    'session_count' => 1,
                ],
                [
                    'name' => 'Sauna',
                    'amount' => 200,
                    'duration_minutes' => 30,
                    'timeslot_availability' => '9:00 AM - 9:00 PM',
                    'description' => 'Relax and detoxify in our premium sauna facility. Helps improve circulation and reduce stress.',
                    'status' => 'active',
                    'is_visible_in_portal' => true,
                    'is_locker_service' => false,
                    'is_session_countable' => false,
                    'session_count' => 1,
                ],
                [
                    'name' => 'Steam bath',
                    'amount' => 512,
                    'duration_minutes' => 45,
                    'timeslot_availability' => '9:00 AM - 9:00 PM',
                    'description' => 'Steam bath sessions for muscle relaxation, post-workout rejuvenation, and detox.',
                    'status' => 'active',
                    'is_visible_in_portal' => true,
                    'is_locker_service' => false,
                    'is_session_countable' => true,
                    'session_count' => 2,
                ],
                [
                    'name' => 'Towel Service',
                    'amount' => 50,
                    'duration_minutes' => 0,
                    'timeslot_availability' => 'Daily',
                    'description' => 'Fresh towels provided for your convenience during each visit.',
                    'status' => 'active',
                    'is_visible_in_portal' => true,
                    'is_locker_service' => false,
                    'is_session_countable' => false,
                    'session_count' => 1,
                ],
            ];

            foreach ($starterServices as $svc) {
                $svc['tenant_id'] = $tenant->id;
                GymService::create($svc);
            }
        }

        $services = GymService::where('tenant_id', $tenant->id)->latest()->get();
        $allBookings = GymServiceBooking::where('tenant_id', $tenant->id)
            ->with(['member', 'service'])
            ->latest('booking_date')
            ->latest('id')
            ->paginate(20);

        $bookingRequests = GymServiceBooking::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->with(['member', 'service'])
            ->latest()
            ->get();

        $members = Member::where('status', 'ACTIVE')->orderBy('first_name')->get();

        return view('app.services.index', compact('services', 'allBookings', 'bookingRequests', 'members'));
    }

    public function storeService(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
            'timeslot_availability' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'is_visible_in_portal' => 'nullable|boolean',
            'is_locker_service' => 'nullable|boolean',
            'is_session_countable' => 'nullable|boolean',
            'session_count' => 'nullable|integer|min:1',
        ]);

        $service = GymService::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'duration_minutes' => $validated['duration_minutes'] ?? 60,
            'timeslot_availability' => $validated['timeslot_availability'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'is_visible_in_portal' => $request->boolean('is_visible_in_portal', true),
            'is_locker_service' => $request->boolean('is_locker_service', false),
            'is_session_countable' => $request->boolean('is_session_countable', false),
            'session_count' => $request->boolean('is_session_countable') ? (int) ($validated['session_count'] ?? 1) : 1,
        ]);

        ActivityLog::log('service_created', "Created gym service '{$service->name}'", $service);

        return back()->with('success', "Service '{$service->name}' added successfully!");
    }

    public function updateService(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $service = GymService::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
            'timeslot_availability' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'is_visible_in_portal' => 'nullable|boolean',
            'is_locker_service' => 'nullable|boolean',
            'is_session_countable' => 'nullable|boolean',
            'session_count' => 'nullable|integer|min:1',
        ]);

        $service->update([
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'duration_minutes' => $validated['duration_minutes'] ?? 60,
            'timeslot_availability' => $validated['timeslot_availability'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'is_visible_in_portal' => $request->boolean('is_visible_in_portal', false),
            'is_locker_service' => $request->boolean('is_locker_service', false),
            'is_session_countable' => $request->boolean('is_session_countable', false),
            'session_count' => $request->boolean('is_session_countable') ? (int) ($validated['session_count'] ?? 1) : 1,
        ]);

        ActivityLog::log('service_updated', "Updated gym service '{$service->name}'", $service);

        return back()->with('success', "Service '{$service->name}' updated successfully!");
    }

    public function toggleServiceVisibility(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $service = GymService::where('tenant_id', $tenant->id)->findOrFail($id);

        $service->update([
            'is_visible_in_portal' => ! $service->is_visible_in_portal,
        ]);

        $state = $service->is_visible_in_portal ? 'Visible in portal' : 'Hidden from portal';
        ActivityLog::log('service_visibility_changed', "Changed visibility of service '{$service->name}' to {$state}");

        return back()->with('success', "Service '{$service->name}' is now {$state}.");
    }

    public function deleteService(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $service = GymService::where('tenant_id', $tenant->id)->findOrFail($id);

        $name = $service->name;
        $service->delete();

        ActivityLog::log('service_deleted', "Deleted gym service '{$name}'");

        return back()->with('success', "Service '{$name}' deleted successfully.");
    }

    public function storeServiceBooking(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'gym_service_id' => 'required|exists:gym_services,id',
            'booking_date' => 'required|date',
            'booking_time' => 'nullable|string',
            'amount_paid' => 'nullable|numeric|min:0',
            'locker_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $service = GymService::findOrFail($validated['gym_service_id']);
        $totalSessions = $service->is_session_countable ? max(1, $service->session_count) : 1;
        $amountPaid = isset($validated['amount_paid']) ? (float) $validated['amount_paid'] : (float) $service->amount;

        $booking = GymServiceBooking::create([
            'tenant_id' => $tenant->id,
            'member_id' => $validated['member_id'],
            'gym_service_id' => $validated['gym_service_id'],
            'booking_date' => $validated['booking_date'],
            'booking_time' => ! empty($validated['booking_time']) ? $validated['booking_time'] : null,
            'amount_paid' => $amountPaid,
            'total_sessions' => $totalSessions,
            'sessions_left' => $totalSessions,
            'locker_number' => $validated['locker_number'] ?? null,
            'status' => 'active',
            'notes' => $validated['notes'] ?? null,
        ]);

        ActivityLog::log('service_booking_created', "Created booking for '{$service->name}'", $booking);

        return back()->with('success', "Service '{$service->name}' booked successfully for member!");
    }

    public function deductServiceSession(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $booking = GymServiceBooking::where('tenant_id', $tenant->id)->with(['member', 'service'])->findOrFail($id);

        if ($booking->sessions_left <= 0) {
            return back()->with('error', 'No sessions remaining on this booking.');
        }

        $booking->sessions_left -= 1;
        if ($booking->sessions_left === 0) {
            $booking->status = 'completed';
        }
        $booking->save();

        ActivityLog::log('service_session_deducted', "Deducted 1 session of '{$booking->service->name}' for {$booking->member->full_name}. Remaining: {$booking->sessions_left}", $booking);

        return back()->with('success', "1 session deducted. Remaining sessions: {$booking->sessions_left}/{$booking->total_sessions}");
    }

    public function updateBookingStatus(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $booking = GymServiceBooking::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:active,pending,completed,cancelled',
            'locker_number' => 'nullable|string|max:50',
        ]);

        $booking->status = $validated['status'];
        if ($request->filled('locker_number')) {
            $booking->locker_number = $validated['locker_number'];
        }
        $booking->save();

        ActivityLog::log('service_booking_status_updated', "Updated booking #{$booking->id} status to {$booking->status}", $booking);

        return back()->with('success', "Booking status updated to '{$booking->status}'.");
    }

    public function deleteServiceBooking(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $booking = GymServiceBooking::where('tenant_id', $tenant->id)->findOrFail($id);

        $booking->delete();

        ActivityLog::log('service_booking_deleted', "Deleted service booking #{$id}");

        return back()->with('success', 'Service booking deleted successfully.');
    }
}
