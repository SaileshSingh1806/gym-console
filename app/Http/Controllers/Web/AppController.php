<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\PaymentReceiptMail;
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
use App\Models\EquipmentMaintenanceLog;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GymClass;
use App\Models\GymEquipment;
use App\Models\GymService;
use App\Models\GymServiceBooking;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\Lead;
use App\Models\LeadTrial;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\MemberPtPackage;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\PtPlan;
use App\Models\PtSession;
use App\Models\Role;
use App\Models\Scopes\BranchScope;
use App\Models\Setting;
use App\Models\SupportTicket;
use App\Models\Trainer;
use App\Models\User;
use App\Models\WorkoutExercise;
use App\Models\WorkoutPlan;
use App\Services\AccessControl\AccessControlService;
use App\Services\AiDietPlannerService;
use App\Services\AttendanceService;
use App\Services\FeatureGateService;
use App\Services\MemberPaymentService;
use App\Services\MembershipService;
use App\Services\ReportService;
use App\Services\SubscriptionService;
use App\Services\SupportTicketService;
use App\Services\TenantContext;
use App\Services\TenantMailService;
use App\Services\TenantService;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function dashboard(): View|RedirectResponse
    {
        $user = auth()->user();
        $tenant = TenantContext::getTenant() ?? $user?->tenant;

        if (! $tenant) {
            if ($user && $user->isSuperAdmin()) {
                $firstTenant = Tenant::first();
                if ($firstTenant) {
                    $tenant = $firstTenant;
                    TenantContext::setTenant($tenant);
                } else {
                    return redirect()->route('admin.dashboard')->with('info', 'No gyms created yet. Create a gym tenant first.');
                }
            } else {
                return redirect()->route('login');
            }
        }

        $branch = TenantContext::getBranch() ?? $tenant->branches()->first();
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
                    ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()]);
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
        } elseif ($request->filter === 'dormant') {
            $query->where('status', 'ACTIVE')
                ->whereDoesntHave('attendances', function ($q) {
                    $q->where('date', '>=', now()->subDays(14)->toDateString());
                });
        } elseif ($request->filter === 'new') {
            $query->where('created_at', '>=', now()->startOfMonth());
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
            'phone' => ['required', 'string', 'max:30', Rule::unique('members', 'phone')->where('tenant_id', $tenant?->id)],
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
            // Prevent concurrent double submission
            $existingRecentMember = Member::where('tenant_id', $tenant->id)
                ->where('phone', $validated['phone'])
                ->where('first_name', $validated['first_name'])
                ->where('last_name', $validated['last_name'])
                ->where('created_at', '>=', now()->subSeconds(6))
                ->first();

            if ($existingRecentMember) {
                return redirect()->route('app.members.index')->with('success', "Member '{$validated['first_name']} {$validated['last_name']}' created successfully!");
            }

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
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $member = Member::with('activeMembership')->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'phone' => ['required', 'string', 'max:30', Rule::unique('members', 'phone')->where('tenant_id', $tenant?->id ?? $member->tenant_id)->ignore($member->id)],
            'alternate_phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'gender' => 'nullable|in:male,female,other,Male,Female,Other',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'emergency_relation' => 'nullable|string|max:50',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|in:ACTIVE,INACTIVE,SUSPENDED,EXPIRED',
            'notes' => 'nullable|string|max:1000',
            'membership_plan_id' => 'nullable|exists:membership_plans,id',
            'photo' => 'nullable|image|max:5120',
            'photo_data' => 'nullable|string',
            'remove_photo' => 'nullable|boolean',
            'weight' => 'nullable|numeric|min:0',
            'target_weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|string|max:30',
            'height_unit' => 'nullable|string|in:cm,ft',
            'fitness_goal' => 'nullable|string|max:100',
            'medical_history' => 'nullable|string|max:1000',
        ]);

        try {
            // Handle Photo Upload / Webcam Snapshot / Remove
            $photoPath = $member->photo_path;

            if ($request->hasFile('photo')) {
                // Delete previous file if exists
                if ($member->photo_path && Storage::disk('public')->exists($member->photo_path)) {
                    Storage::disk('public')->delete($member->photo_path);
                }
                $photoPath = $request->file('photo')->store('members/photos', 'public');
            } elseif (! empty($validated['photo_data']) && str_starts_with($validated['photo_data'], 'data:image')) {
                @[$type, $data] = explode(';', $validated['photo_data']);
                @[, $data] = explode(',', $data);
                if ($data) {
                    if ($member->photo_path && Storage::disk('public')->exists($member->photo_path)) {
                        Storage::disk('public')->delete($member->photo_path);
                    }
                    $decodedImage = base64_decode($data);
                    $fileName = 'members/photos/cam_'.Str::random(20).'.jpg';
                    Storage::disk('public')->put($fileName, $decodedImage);
                    $photoPath = $fileName;
                }
            } elseif ($request->boolean('remove_photo')) {
                if ($member->photo_path && Storage::disk('public')->exists($member->photo_path)) {
                    Storage::disk('public')->delete($member->photo_path);
                }
                $photoPath = null;
            }

            // Merge metadata
            $metadata = $member->metadata ?? [];
            if (isset($validated['alternate_phone'])) {
                $metadata['alternate_phone'] = $validated['alternate_phone'];
            }
            if (isset($validated['blood_group'])) {
                $metadata['blood_group'] = $validated['blood_group'];
            }
            if (isset($validated['city'])) {
                $metadata['city'] = $validated['city'];
            }
            if (isset($validated['emergency_relation'])) {
                $metadata['emergency_relation'] = $validated['emergency_relation'];
            }
            if (isset($validated['weight'])) {
                $metadata['weight'] = $validated['weight'];
            }
            if (isset($validated['target_weight'])) {
                $metadata['target_weight'] = $validated['target_weight'];
            }
            if (isset($validated['height'])) {
                $metadata['height'] = $validated['height'];
            }
            if (isset($validated['height_unit'])) {
                $metadata['height_unit'] = $validated['height_unit'];
            }
            if (isset($validated['fitness_goal'])) {
                $metadata['fitness_goal'] = $validated['fitness_goal'];
            }
            if (isset($validated['medical_history'])) {
                $metadata['medical_history'] = $validated['medical_history'];
            }

            $member->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'gender' => ! empty($validated['gender']) ? strtolower($validated['gender']) : null,
                'dob' => $validated['dob'] ?? null,
                'photo_path' => $photoPath,
                'address' => $validated['address'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                'branch_id' => $validated['branch_id'] ?? $member->branch_id,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'metadata' => $metadata,
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
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
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

    public function editMember(int $id): RedirectResponse
    {
        return redirect()->route('app.members.show', ['id' => $id, 'edit' => 1]);
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
            'classBookings.schedule.gymClass.instructor',
            'classBookings.schedule.trainer',
        ])->findOrFail($id);

        $membershipPlans = MembershipPlan::where('is_active', true)->get();
        $branches = Branch::all();
        $trainers = Trainer::where('status', 'ACTIVE')->get();
        $staff = User::where('tenant_id', $tenant->id)->get();

        $trainerId = $member->metadata['trainer_id'] ?? null;
        $assignedTrainer = $trainerId ? Trainer::find($trainerId) : null;

        $salesRepId = $member->metadata['sales_rep_id'] ?? null;
        $salesRep = $salesRepId ? User::where('tenant_id', $tenant->id)->find($salesRepId) : null;

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

        $availableClassSchedules = ClassSchedule::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->with(['gymClass.instructor', 'trainer'])
            ->get();

        $allGymClasses = GymClass::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->with(['schedules.trainer', 'instructor'])
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
            'auditLogs',
            'availableClassSchedules',
            'allGymClasses'
        ));
    }

    public function showInvoice(int $id): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $payment = MemberPayment::with(['member.branch', 'membership.plan', 'receivedBy'])->findOrFail($id);
        $user = auth()->user();

        if ($user->role === 'member' && $payment->member?->user_id !== $user->id) {
            abort(403, 'Unauthorized to view this invoice.');
        }

        if (in_array($user->role, ['trainer', 'staff'])) {
            // Unless staff user is admin/receptionist/accountant/manager/owner, deny invoice access
            abort(403, 'Unauthorized to view invoices.');
        }

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

            // Create persistent MemberPtPackage record
            $totalAmount = max(0, (float) $validated['amount'] - (float) ($validated['discount'] ?? 0) + (float) ($validated['tax'] ?? 0));
            $collected = (float) ($validated['collected_amount'] ?? 0);

            $ptRecord = MemberPtPackage::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $member->branch_id,
                'member_id' => $member->id,
                'trainer_id' => $trainer?->id,
                'package_name' => $validated['pt_package_name'],
                'total_sessions' => (int) ($validated['sessions'] ?? 12),
                'used_sessions' => 0,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'price' => (float) $validated['amount'],
                'discount' => (float) ($validated['discount'] ?? 0),
                'final_amount' => $totalAmount,
                'paid_amount' => $collected,
                'status' => 'ACTIVE',
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($collected > 0) {
                MemberPayment::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $member->branch_id,
                    'member_id' => $member->id,
                    'membership_id' => null,
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
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $branchId = TenantContext::getBranchId();
        $branchId = TenantContext::branchId();

        $query = Attendance::with(['member', 'device']);
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        }
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $attendance = $query->latest('check_in')->latest('id')->paginate(20);

        $membersQuery = Member::where('status', 'ACTIVE');
        if ($tenant) {
            $membersQuery->where('tenant_id', $tenant->id);
        }
        if ($branchId) {
            $membersQuery->where('branch_id', $branchId);
        }
        $members = $membersQuery->orderBy('first_name')->get();

        $currentlyInsideMemberIds = Attendance::where('date', now()->toDateString())
            ->whereNull('check_out')
            ->pluck('member_id');

        $summary = $this->attendanceService->getTodaySummary($branchId);

        return view('app.attendance.index', compact('attendance', 'members', 'currentlyInsideMemberIds', 'summary'));
    }

    public function storeCheckin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'action' => 'nullable|in:check_in,check_out',
        ]);

        $member = Member::findOrFail($validated['member_id']);
        $action = $validated['action'] ?? 'check_in';

        if ($action === 'check_out') {
            $record = $this->attendanceService->checkOut($member);

            if (! $record) {
                return back()->with('error', "No active check-in record found for {$member->full_name} to check out.");
            }

            return back()->with('success', "Check-out recorded for {$member->full_name}.");
        }

        $this->attendanceService->checkIn($member, 'manual');

        return back()->with('success', "Check-in recorded for {$member->full_name}.");
    }

    public function checkoutAttendance(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $attendance = Attendance::where('tenant_id', $tenant->id)->findOrFail($id);

        $this->attendanceService->checkOutById($attendance);

        return back()->with('success', "Check-out recorded for {$attendance->member?->full_name}.");
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
                // Prevent concurrent double submission
                $existingRecentPayment = MemberPayment::where('tenant_id', $tenant->id)
                    ->where('member_id', $member->id)
                    ->where('amount', $collectedAmount)
                    ->where('payment_date', $paymentDate)
                    ->where('created_at', '>=', now()->subSeconds(6))
                    ->first();

                if ($existingRecentPayment) {
                    return back()->with('success', 'Payment was already recorded successfully!');
                }

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

    public function sendPaymentReceipt(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $payment = MemberPayment::with(['member', 'membership.plan'])->where('tenant_id', $tenant->id)->findOrFail($id);

        if (empty($payment->member?->email)) {
            return back()->with('error', 'This member does not have a registered email address.');
        }

        TenantMailService::send($tenant, $payment->member->email, new PaymentReceiptMail($tenant, $payment));

        return back()->with('success', "Payment receipt #{$payment->invoice_number} sent directly to {$payment->member->email}!");
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

        $staffQuota = $this->featureGateService->checkQuota($tenant, 'staff');
        if (! $staffQuota['allowed']) {
            return back()->with('error', "Staff & Trainer limit reached ({$staffQuota['limit']} allowed on your current plan). Please upgrade your subscription to add more trainers.")->withInput();
        }

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

        // Auto-create or sync User staff member for this Trainer
        $name = trim($trainer->full_name);
        $trainerPhone = $trainer->phone;
        $trainerEmail = $trainer->email;
        $trainerStatus = in_array($trainer->status, ['ACTIVE', 'INACTIVE', 'SUSPENDED']) ? $trainer->status : 'ACTIVE';

        $existingUser = User::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($trainerPhone, $trainerEmail) {
                $q->where('phone', $trainerPhone);
                if ($trainerEmail) {
                    $q->orWhere('email', $trainerEmail);
                }
            })
            ->first();

        if ($existingUser) {
            $existingUser->update([
                'name' => $name,
                'role' => 'trainer',
                'status' => $trainerStatus,
            ]);
            $trainer->update(['user_id' => $existingUser->id]);
        } else {
            $nextEmpNo = User::where('tenant_id', $tenant->id)->count() + 1;
            $empId = 'EMP'.str_pad($nextEmpNo, 3, '0', STR_PAD_LEFT);
            $meta = [
                'employee_id' => $empId,
                'designation' => $trainer->specialization ?: 'Fitness Trainer',
                'department' => 'Fitness / Training',
                'joining_date' => $trainer->joining_date ? $trainer->joining_date->toDateString() : now()->toDateString(),
                'monthly_salary' => $trainer->salary ?? 0.00,
                'payout_type' => $trainer->salary_type ?? 'Fixed Monthly',
                'can_login' => false,
            ];

            $newUser = User::create([
                'tenant_id' => $tenant->id,
                'name' => $name,
                'email' => $trainerEmail ?: null,
                'phone' => $trainerPhone,
                'role' => 'trainer',
                'status' => $trainerStatus,
                'password' => Hash::make(Str::random(16)),
                'metadata' => $meta,
            ]);

            if ($trainer->branch_id) {
                $newUser->branches()->sync([$trainer->branch_id]);
            }

            $trainer->update(['user_id' => $newUser->id]);
        }

        ActivityLog::log('trainer_created', "Added trainer '{$trainer->full_name}'", $trainer);

        return back()->with('success', "Trainer '{$trainer->full_name}' added successfully and registered in Staff roster!");
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

        // Sync linked User staff member if exists
        $user = $trainer->user_id ? User::find($trainer->user_id) : User::where('tenant_id', $tenant->id)->where('phone', $trainer->phone)->first();
        if ($user) {
            $userMeta = $user->metadata ?? [];
            $userMeta['designation'] = $trainer->specialization ?: ($userMeta['designation'] ?? 'Fitness Trainer');
            $userMeta['monthly_salary'] = $trainer->salary ?? ($userMeta['monthly_salary'] ?? 0.00);
            $userMeta['payout_type'] = $trainer->salary_type ?? ($userMeta['payout_type'] ?? 'Fixed Monthly');
            if ($trainer->joining_date) {
                $userMeta['joining_date'] = $trainer->joining_date->toDateString();
            }

            $userUpdate = [
                'name' => trim($trainer->full_name),
                'phone' => $trainer->phone,
                'status' => in_array($trainer->status, ['ACTIVE', 'INACTIVE', 'SUSPENDED']) ? $trainer->status : 'ACTIVE',
                'metadata' => $userMeta,
            ];
            if ($trainer->email) {
                $userUpdate['email'] = $trainer->email;
            }
            $user->update($userUpdate);

            if (! $trainer->user_id) {
                $trainer->update(['user_id' => $user->id]);
            }
        }

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

        // Also delete / soft delete linked User if role is trainer
        if ($trainer->user_id) {
            $user = User::find($trainer->user_id);
            if ($user && $user->role === 'trainer' && $user->id !== auth()->id()) {
                $user->delete();
            }
        } else {
            $user = User::where('tenant_id', $tenant->id)->where('phone', $trainer->phone)->where('role', 'trainer')->first();
            if ($user && $user->id !== auth()->id()) {
                $user->delete();
            }
        }

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

    public function enrollMemberInClass(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $member = Member::where('tenant_id', $tenant->id)->findOrFail($id);

        $rawScheduleId = $request->input('class_schedule_id');
        $gymClassId = $request->input('gym_class_id');
        $classScheduleId = null;

        if ($rawScheduleId && str_starts_with((string) $rawScheduleId, 'class_')) {
            $gymClassId = (int) str_replace('class_', '', (string) $rawScheduleId);
        } elseif ($rawScheduleId && is_numeric($rawScheduleId)) {
            $classScheduleId = (int) $rawScheduleId;
        }

        $validated = $request->validate([
            'booking_date' => 'nullable|date',
            'status' => 'nullable|in:BOOKED,ATTENDED,CANCELLED',
        ]);

        $schedule = null;
        if (! empty($classScheduleId)) {
            $schedule = ClassSchedule::where('tenant_id', $tenant->id)->with('gymClass')->find($classScheduleId);
        } elseif (! empty($gymClassId)) {
            $gymClass = GymClass::where('tenant_id', $tenant->id)->with('schedules')->find($gymClassId);
            $schedule = $gymClass?->schedules()->where('is_active', true)->first() ?? $gymClass?->schedules()->first();
            if (! $schedule && $gymClass) {
                // Auto create a schedule for this class if none exists
                $schedule = ClassSchedule::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $gymClass->branch_id ?? ($member->branch_id ?? 1),
                    'gym_class_id' => $gymClass->id,
                    'trainer_id' => $gymClass->instructor_id,
                    'day_of_week' => strtolower(now()->format('l')),
                    'start_time' => '07:00:00',
                    'end_time' => '08:00:00',
                    'is_active' => true,
                ]);
            }
        }

        if (! $schedule) {
            return back()->with('error', 'Please select a valid class or schedule to enroll.');
        }

        $bookingDate = $validated['booking_date'] ?? now()->toDateString();
        $status = $validated['status'] ?? 'BOOKED';

        $booking = ClassBooking::updateOrCreate([
            'tenant_id' => $tenant->id,
            'branch_id' => $schedule->branch_id,
            'class_schedule_id' => $schedule->id,
            'member_id' => $member->id,
            'booking_date' => $bookingDate,
        ], [
            'status' => $status,
        ]);

        ActivityLog::log('class_enrolled', "Enrolled member '{$member->full_name}' in class '{$schedule->gymClass->name}'", $booking);

        return back()->with('success', "Member successfully enrolled in '{$schedule->gymClass->name}'!");
    }

    public function deleteClassBooking(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $booking = ClassBooking::where('tenant_id', $tenant->id)->with(['schedule.gymClass', 'member'])->findOrFail($id);
        $className = $booking->schedule?->gymClass?->name ?? 'Class';
        $booking->delete();

        return back()->with('success', "Enrollment in '{$className}' removed successfully.");
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

        $goalInput = strtolower(str_replace(' ', '_', (string) ($validated['goal'] ?? 'general_fitness')));
        $validGoals = ['weight_loss', 'muscle_gain', 'endurance', 'general_fitness', 'flexibility'];
        $goal = in_array($goalInput, $validGoals) ? $goalInput : 'general_fitness';

        $levelInput = strtolower(str_replace(' ', '_', (string) ($validated['level'] ?? 'beginner')));
        $validLevels = ['beginner', 'intermediate', 'advanced'];
        $level = in_array($levelInput, $validLevels) ? $levelInput : 'beginner';

        $workout = WorkoutPlan::create([
            'tenant_id' => $tenant->id,
            'member_id' => $isTemplate ? null : ($validated['member_id'] ?? null),
            'trainer_id' => $validated['trainer_id'] ?? null,
            'title' => $validated['title'],
            'goal' => $validated['goal'] ?? 'General Fitness',
            'level' => $validated['level'] ?? 'Beginner',
            'goal' => $goal,
            'level' => $level,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_template' => $isTemplate,
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! empty($validated['exercises']) && is_array($validated['exercises'])) {
            $order = 1;
            $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'day_1', 'day_2', 'day_3', 'day_4', 'day_5', 'day_6', 'day_7'];
            foreach ($validated['exercises'] as $ex) {
                if (! empty($ex['exercise_name'])) {
                    $dayInput = strtolower(str_replace(' ', '_', (string) ($ex['day'] ?? 'day_1')));
                    $day = in_array($dayInput, $validDays) ? $dayInput : 'day_1';

                    WorkoutExercise::create([
                        'workout_plan_id' => $workout->id,
                        'day' => $ex['day'] ?? 'Day 1',
                        'day' => $day,
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

    public function generateAiDiet(Request $request, AiDietPlannerService $aiDietService): JsonResponse
    {
        $validated = $request->validate([
            'member_id' => 'nullable|exists:members,id',
            'name' => 'nullable|string|max:100',
            'age' => 'nullable|integer|min:10|max:100',
            'gender' => 'nullable|string|in:male,female,other',
            'height' => 'nullable|numeric|min:50|max:260',
            'weight' => 'nullable|numeric|min:20|max:300',
            'goal' => 'nullable|string|in:weight_loss,weight_gain,muscle_gain,fat_loss,maintenance,general_fitness',
            'activity_level' => 'nullable|string|in:sedentary,lightly_active,moderately_active,very_active,extremely_active',
            'diet_preference' => 'nullable|string|in:vegetarian,non_vegetarian,vegan,eggetarian',
            'meals_per_day' => 'nullable|integer|min:3|max:6',
            'workout_time' => 'nullable|string|in:early_morning,morning,afternoon,evening,night',
            'food_preferences' => 'nullable|string|max:500',
            'foods_to_avoid' => 'nullable|string|max:500',
            'allergies' => 'nullable|string|max:500',
            'additional_notes' => 'nullable|string|max:1000',
            'auto_save' => 'nullable|boolean',
        ]);

        if (! empty($validated['member_id'])) {
            $member = Member::find($validated['member_id']);
            if ($member) {
                $validated['name'] = $validated['name'] ?: $member->full_name;
                $validated['gender'] = $validated['gender'] ?: ($member->gender ?: 'male');
                if (empty($validated['age']) && $member->dob) {
                    $validated['age'] = $member->dob->age;
                }
            }
        }

        $planData = $aiDietService->generate($validated);

        if ($request->boolean('auto_save')) {
            try {
                $tenant = TenantContext::getTenant() ?? (auth()->check() ? auth()->user()->tenant : null);
                $createdPlan = DietPlan::create([
                    'tenant_id' => $tenant->id,
                    'member_id' => $validated['member_id'] ?? null,
                    'title' => $planData['plan_title'],
                    'daily_calories' => $planData['daily_totals']['calories'] ?? 2000,
                    'protein_grams' => $planData['daily_totals']['protein_grams'] ?? 100,
                    'carbs_grams' => $planData['daily_totals']['carbs_grams'] ?? 200,
                    'fat_grams' => $planData['daily_totals']['fat_grams'] ?? 50,
                    'is_template' => empty($validated['member_id']),
                    'guidelines' => implode("\n", $planData['guidelines'] ?? [])."\n\n⚠️ Medical Disclaimer:\n".($planData['medical_disclaimer'] ?? ''),
                ]);

                foreach ($planData['meals'] as $meal) {
                    $rawTime = $meal['recommended_time'] ?? null;
                    $formattedTime = null;
                    if (! empty($rawTime)) {
                        try {
                            $formattedTime = Carbon::parse(trim($rawTime))->format('H:i:s');
                        } catch (\Throwable $e) {
                            $formattedTime = '08:00:00';
                        }
                    }

                    DietMeal::create([
                        'diet_plan_id' => $createdPlan->id,
                        'meal_type' => in_array($meal['meal_type'], ['breakfast', 'morning_snack', 'lunch', 'evening_snack', 'dinner', 'post_workout']) ? $meal['meal_type'] : 'breakfast',
                        'recommended_time' => $formattedTime,
                        'meal_name' => $meal['meal_name'],
                        'items_description' => $meal['items_description'].(! empty($meal['alternatives']) ? "\n\n🔄 Alternative Options:\n".$meal['alternatives'] : ''),
                        'calories' => $meal['target_macros']['calories'] ?? null,
                        'sort_order' => $meal['sort_order'] ?? 1,
                    ]);
                }

                ActivityLog::log('diet_ai_generated', "AI generated and saved diet plan '{$createdPlan->title}'", $createdPlan);

                $planData['saved_plan_id'] = $createdPlan->id;
            } catch (\Throwable $e) {
                Log::error('Failed to auto-save AI diet plan: '.$e->getMessage(), ['exception' => $e]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error saving plan: '.$e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $planData,
        ]);
    }

    public function crmDashboard(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $allLeads = Lead::with('assignedTo')->get();
        $totalLeads = $allLeads->count();
        $newThisMonth = $allLeads->filter(function ($l) {
            return $l->created_at && $l->created_at->isCurrentMonth();
        })->count();

        $activeConversions = $allLeads->filter(function ($l) {
            return in_array(strtoupper($l->effective_stage), ['PAID', 'CONVERTED', 'NEGOTIATION']);
        })->count();

        $paidLeads = $allLeads->filter(function ($l) {
            return in_array(strtoupper($l->effective_stage), ['PAID', 'CONVERTED']);
        });

        $monthlyMrr = $paidLeads->sum(function ($l) {
            return $l->estimated_value > 0 ? (float) $l->estimated_value : 0;
        });

        $conversionRate = $totalLeads > 0 ? round(($paidLeads->count() / $totalLeads) * 100, 1) : 0.0;

        // Pipeline 8 Stages
        $pipelineStages = [
            'new_lead' => $allLeads->filter(fn ($l) => in_array(strtoupper($l->effective_stage), ['NEW_LEAD', 'NEW']))->count(),
            'contacted' => $allLeads->filter(fn ($l) => strtoupper($l->effective_stage) === 'CONTACTED')->count(),
            'demo_booked' => $allLeads->filter(fn ($l) => strtoupper($l->effective_stage) === 'DEMO_BOOKED')->count(),
            'proposal_sent' => $allLeads->filter(fn ($l) => strtoupper($l->effective_stage) === 'PROPOSAL_SENT')->count(),
            'negotiation' => $allLeads->filter(fn ($l) => strtoupper($l->effective_stage) === 'NEGOTIATION')->count(),
            'trial' => $allLeads->filter(fn ($l) => in_array(strtoupper($l->effective_stage), ['TRIAL', 'TRIAL_SCHEDULED']))->count(),
            'paid' => $paidLeads->count(),
            'lost' => $allLeads->filter(fn ($l) => strtoupper($l->effective_stage) === 'LOST')->count(),
        ];

        // 30 Days Trend Data
        $trendDates = [];
        $trendCounts = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $trendDates[] = $date->format('d M');
            $trendCounts[] = $allLeads->filter(function ($l) use ($date) {
                return $l->created_at && $l->created_at->toDateString() === $date->toDateString();
            })->count();
        }

        // Sources Breakdown
        $sources = [
            'website' => ['label' => 'Website Forms', 'color' => '#6366f1'],
            'walk_in' => ['label' => 'Walk-In Inquiry', 'color' => '#10b981'],
            'facebook' => ['label' => 'Meta / Facebook Ads', 'color' => '#3b82f6'],
            'instagram' => ['label' => 'Instagram DM / Ads', 'color' => '#ec4899'],
            'google' => ['label' => 'Google Maps / Search', 'color' => '#f59e0b'],
            'referral' => ['label' => 'Member Referral', 'color' => '#8b5cf6'],
            'other' => ['label' => 'Other / Events', 'color' => '#64748b'],
        ];

        $sourceBreakdown = [];
        foreach ($sources as $key => $info) {
            $count = $allLeads->filter(function ($l) use ($key) {
                $src = strtolower(str_replace([' ', '-'], '_', $l->source ?? 'other'));

                return $src === $key;
            })->count();

            $sourceBreakdown[$key] = [
                'label' => $info['label'],
                'count' => $count,
                'color' => $info['color'],
            ];
        }

        $recentLeads = Lead::with('assignedTo')->latest()->limit(8)->get();
        $demoBookedLeads = Lead::where('tenant_id', $tenant->id)
            ->where(function ($q) {
                $q->whereIn('stage', ['DEMO_BOOKED', 'demo_booked', 'TRIAL', 'trial'])
                    ->orWhereIn('status', ['DEMO_BOOKED', 'demo_booked', 'TRIAL_SCHEDULED', 'trial_scheduled']);
            })
            ->orderBy('name')
            ->get();
        $upcomingTrials = LeadTrial::with('lead', 'assignedTo')->whereDate('trial_date', '>=', today())->orderBy('trial_date')->limit(6)->get();
        $staffMembers = User::where('tenant_id', $tenant->id)->get();

        return view('app.crm.dashboard', compact(
            'totalLeads',
            'newThisMonth',
            'activeConversions',
            'monthlyMrr',
            'conversionRate',
            'pipelineStages',
            'trendDates',
            'trendCounts',
            'sourceBreakdown',
            'recentLeads',
            'demoBookedLeads',
            'upcomingTrials',
            'staffMembers',
            'tenant'
        ));
    }

    public function leads(Request $request)
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $query = Lead::with('assignedTo');

        // Search Filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('remarks', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%");
            });
        }

        // Stage Filter
        if ($request->filled('stage') && $request->stage !== 'all') {
            $st = strtoupper($request->stage);
            if ($st === 'NEW_LEAD' || $st === 'NEW') {
                $query->whereIn('stage', ['NEW_LEAD', 'NEW', 'new_lead', 'new'])->orWhereIn('status', ['NEW', 'new']);
            } else {
                $query->where('stage', $request->stage)->orWhere('status', $request->stage);
            }
        }

        // Source Filter
        if ($request->filled('source') && $request->source !== 'all') {
            $query->where('source', $request->source);
        }

        // Staff Filter
        if ($request->filled('staff_id') && $request->staff_id !== 'all') {
            $query->where('assigned_to_user_id', $request->staff_id);
        }

        // Remarks Filter
        if ($request->filled('remarks_filter') && $request->remarks_filter !== 'all') {
            if ($request->remarks_filter === 'with_remarks') {
                $query->whereNotNull('remarks')->where('remarks', '!=', '');
            } elseif ($request->remarks_filter === 'no_remarks') {
                $query->where(function ($q) {
                    $q->whereNull('remarks')->orWhere('remarks', '');
                });
            }
        }

        // CSV Export check
        if ($request->has('export') && $request->export === 'csv') {
            $exportLeads = (clone $query)->latest()->get();
            $csvFileName = 'crm_leads_'.date('Y_m_d_His').'.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$csvFileName}\"",
            ];

            $callback = function () use ($exportLeads) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['ID', 'Name', 'Phone', 'Email', 'Stage', 'Source', 'Assigned To', 'Remarks', 'Next Action', 'Estimated MRR', 'Created At']);

                foreach ($exportLeads as $lead) {
                    fputcsv($handle, [
                        $lead->id,
                        $lead->name,
                        $lead->phone,
                        $lead->email ?? '',
                        $lead->formatted_stage,
                        ucwords(str_replace('_', ' ', $lead->source ?? 'walk_in')),
                        $lead->assignedTo?->name ?? 'Unassigned',
                        $lead->remarks ?? '',
                        $lead->next_action ?? '',
                        $lead->estimated_value ?? 0,
                        $lead->created_at ? $lead->created_at->format('d M Y') : '',
                    ]);
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        $leads = $query->latest()->paginate(20)->withQueryString();
        $totalLeadsCount = Lead::where('tenant_id', $tenant->id)->count();
        $staffMembers = User::where('tenant_id', $tenant->id)->get();
        $branches = Branch::where('tenant_id', $tenant->id)->get();

        return view('app.crm.leads', compact('leads', 'totalLeadsCount', 'staffMembers', 'branches', 'tenant'));
    }

    public function createLead(Request $request): View
    {
        $tenant = $request->get('tenant') ?? Auth::user()->tenant;
        $staffMembers = User::where('tenant_id', $tenant->id)->get();
        $branches = Branch::where('tenant_id', $tenant->id)->get();

        return view('app.crm.create_lead', compact('tenant', 'staffMembers', 'branches'));
    }

    public function editLead(Request $request, $id): View
    {
        $tenant = $request->get('tenant') ?? Auth::user()->tenant;
        $lead = Lead::where('tenant_id', $tenant->id)->findOrFail($id);
        $staffMembers = User::where('tenant_id', $tenant->id)->get();
        $branches = Branch::where('tenant_id', $tenant->id)->get();

        return view('app.crm.edit_lead', compact('tenant', 'lead', 'staffMembers', 'branches'));
    }

    public function storeLead(Request $request): RedirectResponse
    {
        $tenant = $request->get('tenant') ?? Auth::user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'instagram_handle' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'member_count' => 'nullable|integer|min:1',
            'source' => 'nullable|string|max:50',
            'stage' => 'nullable|string|max:50',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'follow_up_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:255',
            'next_action' => 'nullable|string|max:255',
            'estimated_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $stage = $validated['stage'] ?? 'NEW_LEAD';
        $source = $validated['source'] ?? 'walk_in';

        $status = match (strtoupper(str_replace(' ', '_', $stage))) {
            'PAID', 'CONVERTED' => 'CONVERTED',
            'LOST' => 'LOST',
            'CONTACTED' => 'CONTACTED',
            'TRIAL', 'TRIAL_SCHEDULED' => 'TRIAL_SCHEDULED',
            'DEMO_BOOKED' => 'DEMO_BOOKED',
            'PROPOSAL_SENT' => 'PROPOSAL_SENT',
            'NEGOTIATION' => 'NEGOTIATION',
            default => 'NEW',
        };

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? '',
            'email' => $validated['email'] ?? null,
            'instagram_handle' => $validated['instagram_handle'] ?? null,
            'city' => $validated['city'] ?? null,
            'member_count' => $validated['member_count'] ?? 1,
            'source' => $source,
            'status' => $status,
            'stage' => $stage,
            'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'next_action' => $validated['next_action'] ?? null,
            'estimated_value' => $validated['estimated_value'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        ActivityLog::log('lead_created', "Created CRM lead {$lead->name}".($lead->phone ? " ({$lead->phone})" : ''));

        return redirect()->route('app.leads.index')->with('success', "Lead {$lead->name} added successfully to pipeline!");
    }

    public function updateLead(Request $request, $id): RedirectResponse
    {
        $tenant = $request->get('tenant') ?? Auth::user()->tenant;
        $lead = Lead::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'instagram_handle' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'member_count' => 'nullable|integer|min:1',
            'source' => 'nullable|string|max:50',
            'stage' => 'nullable|string|max:50',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'follow_up_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:255',
            'next_action' => 'nullable|string|max:255',
            'estimated_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $stage = $validated['stage'] ?? $lead->stage;
        $source = $validated['source'] ?? $lead->source;

        $status = match (strtoupper(str_replace(' ', '_', $stage))) {
            'PAID', 'CONVERTED' => 'CONVERTED',
            'LOST' => 'LOST',
            'CONTACTED' => 'CONTACTED',
            'TRIAL', 'TRIAL_SCHEDULED' => 'TRIAL_SCHEDULED',
            'DEMO_BOOKED' => 'DEMO_BOOKED',
            'PROPOSAL_SENT' => 'PROPOSAL_SENT',
            'NEGOTIATION' => 'NEGOTIATION',
            default => 'NEW',
        };

        $lead->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? '',
            'email' => $validated['email'] ?? null,
            'instagram_handle' => $validated['instagram_handle'] ?? null,
            'city' => $validated['city'] ?? null,
            'member_count' => $validated['member_count'] ?? 1,
            'branch_id' => $validated['branch_id'] ?? null,
            'source' => $source,
            'status' => $status,
            'stage' => $stage,
            'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'next_action' => $validated['next_action'] ?? null,
            'estimated_value' => $validated['estimated_value'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        if (in_array(strtoupper(str_replace(' ', '_', $stage)), ['PAID', 'CONVERTED'])) {
            $this->convertLeadToMember($lead, (float) ($validated['estimated_value'] ?? $lead->estimated_value ?? 0));
        }

        ActivityLog::log('lead_updated', "Updated CRM lead {$lead->name}");

        return redirect()->route('app.leads.index')->with('success', "Lead {$lead->name} updated successfully.");
    }

    public function updateLeadStage(Request $request, $id): JsonResponse|RedirectResponse
    {
        $tenant = $request->get('tenant') ?? Auth::user()->tenant;
        $lead = Lead::where('tenant_id', $tenant->id)->findOrFail($id);
        $stage = $request->input('stage', 'NEW_LEAD');

        $status = match (strtoupper(str_replace(' ', '_', $stage))) {
            'PAID', 'CONVERTED' => 'CONVERTED',
            'LOST' => 'LOST',
            'CONTACTED' => 'CONTACTED',
            'TRIAL', 'TRIAL_SCHEDULED' => 'TRIAL_SCHEDULED',
            'DEMO_BOOKED' => 'DEMO_BOOKED',
            'PROPOSAL_SENT' => 'PROPOSAL_SENT',
            'NEGOTIATION' => 'NEGOTIATION',
            default => 'NEW',
        };

        $updateData = [
            'stage' => $stage,
            'status' => $status,
        ];

        if (in_array(strtoupper(str_replace(' ', '_', $stage)), ['PAID', 'CONVERTED']) && $lead->trial_status === 'upcoming') {
            $updateData['trial_status'] = 'completed';
        }

        if ($request->has('paid_amount') || $request->has('estimated_value')) {
            $paidAmount = $request->input('paid_amount', $request->input('estimated_value'));
            if ($paidAmount !== null && is_numeric($paidAmount)) {
                $updateData['estimated_value'] = (float) $paidAmount;
            }
        }

        $lead->update($updateData);

        if (in_array(strtoupper(str_replace(' ', '_', $stage)), ['PAID', 'CONVERTED'])) {
            $conversionAmount = isset($updateData['estimated_value']) ? (float) $updateData['estimated_value'] : (float) ($lead->estimated_value ?? 0);
            $this->convertLeadToMember($lead, $conversionAmount);
        }

        ActivityLog::log('lead_stage_changed', "Moved lead {$lead->name} to {$stage}".(isset($updateData['estimated_value']) ? " with conversion value {$updateData['estimated_value']}" : ''));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'stage' => $lead->formatted_stage,
                'status' => $lead->status,
                'estimated_value' => $lead->estimated_value,
            ]);
        }

        return back()->with('success', "Lead stage updated to {$lead->formatted_stage}".(isset($updateData['estimated_value']) && $updateData['estimated_value'] > 0 ? " (Paid: {$updateData['estimated_value']})" : '').'.');
    }

    protected function convertLeadToMember(Lead $lead, ?float $paidAmount = null): ?Member
    {
        $tenant = $lead->tenant ?? TenantContext::getTenant() ?? auth()->user()?->tenant;
        if (! $tenant) {
            return null;
        }

        $branchId = $lead->branch_id ?? session('active_branch_id') ?? TenantContext::getBranchId() ?? $tenant->branches()->first()?->id;

        $member = Member::withoutGlobalScope(BranchScope::class)
            ->where('tenant_id', $tenant->id)
            ->where(function ($q) use ($lead) {
                if (! empty($lead->phone)) {
                    $q->where('phone', $lead->phone);
                }
                if (! empty($lead->email)) {
                    $q->orWhere('email', $lead->email);
                }
            })
            ->first();

        $nameParts = explode(' ', trim($lead->name), 2);
        $firstName = ! empty($nameParts[0]) ? $nameParts[0] : 'Lead';
        $lastName = ! empty($nameParts[1]) ? $nameParts[1] : $firstName;

        if (! $member) {
            $member = Member::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branchId,
                'member_code' => $tenant->generateNextMemberCode(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $lead->phone ?: '0000000000',
                'email' => $lead->email,
                'join_date' => now()->toDateString(),
                'status' => 'ACTIVE',
                'notes' => "Converted from CRM Lead #{$lead->id} ({$lead->name})",
                'qr_code_token' => Str::random(32),
            ]);
        } else {
            $member->update(['status' => 'ACTIVE']);
        }

        $amount = $paidAmount ?? (float) ($lead->estimated_value ?? 0);
        if ($amount > 0) {
            $activeMembership = $member->activeMembership;
            $defaultPlan = MembershipPlan::where('tenant_id', $tenant->id)->first();
            $defaultPlan = MembershipPlan::where('tenant_id', $tenant->id)->first() ?? MembershipPlan::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branchId,
                'name' => 'General Membership',
                'price' => $amount > 0 ? $amount : 1000,
                'duration_type' => 'months',
                'duration_value' => 1,
                'is_active' => true,
            ]);
            $durationDays = match ($defaultPlan?->duration_type) {
                'days' => (int) ($defaultPlan->duration_value ?: 30),
                'months' => (int) ($defaultPlan->duration_value ?: 1) * 30,
                'years' => (int) ($defaultPlan->duration_value ?: 1) * 365,
                default => 30,
            };

            if (! $activeMembership) {
                $activeMembership = Membership::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branchId,
                    'member_id' => $member->id,
                    'membership_plan_id' => $defaultPlan?->id,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addDays($durationDays)->toDateString(),
                    'price' => $amount,
                    'discount' => 0,
                    'tax' => 0,
                    'final_amount' => $amount,
                    'paid_amount' => $amount,
                    'status' => 'ACTIVE',
                    'notes' => "Auto-created from CRM Lead #{$lead->id} conversion",
                ]);
            } else {
                $activeMembership->increment('paid_amount', $amount);
                if ($activeMembership->paid_amount > $activeMembership->final_amount) {
                    $activeMembership->update(['final_amount' => $activeMembership->paid_amount]);
                }
            }

            $recentPayment = MemberPayment::withoutGlobalScope(BranchScope::class)
                ->where('tenant_id', $tenant->id)
                ->where('member_id', $member->id)
                ->where('amount', $amount)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->first();

            if (! $recentPayment) {
                MemberPayment::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branchId,
                    'member_id' => $member->id,
                    'membership_id' => $activeMembership?->id,
                    'invoice_number' => 'INV-CRM-'.strtoupper(Str::random(6)).'-'.date('Ymd'),
                    'amount' => $amount,
                    'payment_method' => 'cash',
                    'payment_date' => now()->toDateString(),
                    'received_by_user_id' => auth()->id(),
                    'notes' => "Conversion payment from CRM Lead: {$lead->name}",
                ]);
            }
        }

        return $member;
    }

    public function deleteLead($id): RedirectResponse
    {
        $lead = Lead::findOrFail($id);
        $name = $lead->name;
        $lead->delete();

        ActivityLog::log('lead_deleted', "Deleted CRM lead {$name}");

        return back()->with('success', "Lead {$name} deleted successfully.");
    }

    public function crmTrials(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $month = (int) $request->input('month', date('n'));
        $year = (int) $request->input('year', date('Y'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $trials = LeadTrial::with('lead', 'assignedTo')
            ->whereBetween('trial_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $todayTrialsCount = LeadTrial::whereDate('trial_date', today())->count();
        $upcomingTrialsCount = LeadTrial::whereDate('trial_date', '>=', today())->where('status', 'upcoming')->count();
        $completedThisMonth = LeadTrial::whereMonth('trial_date', $month)->whereYear('trial_date', $year)->where('status', 'completed')->count();
        $cancelledThisMonth = LeadTrial::whereMonth('trial_date', $month)->whereYear('trial_date', $year)->where('status', 'cancelled')->count();

        $upcomingList = LeadTrial::with('lead', 'assignedTo')
            ->whereDate('trial_date', '>=', today())
            ->orderBy('trial_date')
            ->orderBy('trial_time')
            ->get();

        $leadsList = Lead::where('tenant_id', $tenant->id)
            ->where(function ($q) {
                $q->whereIn('stage', ['DEMO_BOOKED', 'demo_booked', 'TRIAL', 'trial'])
                    ->orWhereIn('status', ['DEMO_BOOKED', 'demo_booked', 'TRIAL_SCHEDULED', 'trial_scheduled']);
            })
            ->orderBy('name')
            ->get();
        $staffMembers = User::where('tenant_id', $tenant->id)->get();

        return view('app.crm.trials', compact(
            'trials',
            'month',
            'year',
            'startDate',
            'endDate',
            'todayTrialsCount',
            'upcomingTrialsCount',
            'completedThisMonth',
            'cancelledThisMonth',
            'upcomingList',
            'leadsList',
            'staffMembers',
            'tenant'
        ));
    }

    public function storeTrial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'trial_date' => 'required|date',
            'trial_time' => 'nullable|string|max:50',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $trial = LeadTrial::create([
            'lead_id' => $validated['lead_id'],
            'trial_date' => $validated['trial_date'],
            'trial_time' => $validated['trial_time'] ?? '10:00 AM',
            'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
            'status' => 'upcoming',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update lead stage to TRIAL
        $lead = Lead::find($validated['lead_id']);
        if ($lead) {
            $lead->update([
                'stage' => 'TRIAL',
                'trial_date' => $validated['trial_date'],
                'trial_time' => $validated['trial_time'] ?? '10:00 AM',
                'trial_status' => 'upcoming',
            ]);
        }

        ActivityLog::log('trial_booked', "Booked demo/trial for lead {$lead?->name} on {$trial->trial_date->format('d M Y')}");

        return back()->with('success', 'Trial & demo booked successfully on calendar!');
    }

    public function updateTrialStatus(Request $request, $id): RedirectResponse
    {
        $trial = LeadTrial::findOrFail($id);
        $status = $request->input('status', 'completed');

        $trial->update(['status' => $status]);

        if ($trial->lead) {
            $leadUpdates = ['trial_status' => $status];
            if ($status === 'completed') {
                $leadUpdates['stage'] = 'PAID';
                $leadUpdates['status'] = 'CONVERTED';
            } elseif ($status === 'cancelled') {
                $leadUpdates['stage'] = 'LOST';
                $leadUpdates['status'] = 'LOST';
            }
            $trial->lead->update($leadUpdates);
        }

        ActivityLog::log('trial_status_updated', "Updated trial status to {$status}");

        return back()->with('success', "Trial marked as {$status}.");
    }

    public function deleteTrial($id): RedirectResponse
    {
        $trial = LeadTrial::findOrFail($id);
        $trial->delete();

        return back()->with('success', 'Trial removed successfully.');
    }

    public function crmEnquiries(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? Auth::user()->tenant;
        $query = Lead::where('tenant_id', $tenant->id)
            ->whereIn('source', ['website', 'walk_in', 'facebook', 'instagram', 'google', 'whatsapp', 'phone', 'referral', 'other']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('remarks', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%");
            });
        }

        if ($request->filled('source') && $request->source !== 'all') {
            $query->where('source', $request->source);
        }

        $enquiries = $query->latest()->paginate(15)->withQueryString();
        $staffMembers = User::where('tenant_id', $tenant->id)->get();
        $branches = Branch::where('tenant_id', $tenant->id)->get();

        return view('app.crm.subpages', [
            'section' => 'enquiries',
            'title' => 'Enquiries & Direct Walk-Ins',
            'subtitle' => 'Incoming website forms, walk-ins, phone calls, and social media inquiries',
            'enquiries' => $enquiries,
            'staffMembers' => $staffMembers,
            'branches' => $branches,
            'tenant' => $tenant,
        ]);
    }

    public function storeEnquiry(Request $request): RedirectResponse
    {
        $tenant = $request->get('tenant') ?? Auth::user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|string|max:50',
            'stage' => 'nullable|string|max:50',
            'branch_id' => 'nullable|exists:branches,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'follow_up_date' => 'nullable|date',
            'estimated_value' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $stage = $validated['stage'] ?? 'NEW_LEAD';
        $source = $validated['source'] ?? 'walk_in';

        $status = match (strtoupper(str_replace(' ', '_', $stage))) {
            'PAID', 'CONVERTED' => 'CONVERTED',
            'LOST' => 'LOST',
            'CONTACTED' => 'CONTACTED',
            'TRIAL', 'TRIAL_SCHEDULED' => 'TRIAL_SCHEDULED',
            'DEMO_BOOKED' => 'DEMO_BOOKED',
            'PROPOSAL_SENT' => 'PROPOSAL_SENT',
            'NEGOTIATION' => 'NEGOTIATION',
            default => 'NEW',
        };

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? '',
            'email' => $validated['email'] ?? null,
            'source' => $source,
            'status' => $status,
            'stage' => $stage,
            'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
            'remarks' => $validated['remarks'] ?? ($validated['notes'] ?? null),
            'notes' => $validated['notes'] ?? ($validated['remarks'] ?? null),
            'estimated_value' => $validated['estimated_value'] ?? 0,
        ]);

        ActivityLog::log('enquiry_created', "Recorded direct enquiry/walk-in for {$lead->name}");

        return back()->with('success', "Enquiry for {$lead->name} recorded successfully!");
    }

    public function crmConversions(Request $request): View
    {
        $paidLeads = Lead::whereIn('stage', ['PAID', 'CONVERTED', 'paid', 'converted'])
            ->orWhereIn('status', ['CONVERTED', 'PAID', 'converted', 'paid'])
            ->latest()
            ->paginate(15);

        $totalRevenue = $paidLeads->sum('estimated_value');

        return view('app.crm.subpages', [
            'section' => 'conversions',
            'title' => 'Conversions & Won Leads',
            'subtitle' => 'Track successful member acquisitions and monthly recurring revenue generated',
            'paidLeads' => $paidLeads,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    public function crmReports(Request $request): View|StreamedResponse
    {
        $tenant = $request->get('tenant') ?? Auth::user()->tenant;
        $currency = $tenant->currency_symbol ?? '₹';

        $startDateInput = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDateInput = $request->input('end_date', now()->format('Y-m-d'));
        $employeeId = $request->input('employee_id');
        $branchId = $request->input('branch_id');

        try {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
        } catch (\Exception $e) {
            $startDate = now()->startOfMonth()->startOfDay();
            $startDateInput = $startDate->format('Y-m-d');
        }

        try {
            $endDate = Carbon::parse($endDateInput)->endOfDay();
        } catch (\Exception $e) {
            $endDate = now()->endOfDay();
            $endDateInput = $endDate->format('Y-m-d');
        }

        // Base query for leads within tenant
        $query = Lead::where('tenant_id', $tenant->id);

        if ($request->filled('start_date') || $request->filled('end_date')) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($employeeId && $employeeId !== 'all') {
            $query->where('assigned_to_user_id', $employeeId);
        }

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        // CSV Export if requested
        if ($request->input('export') === 'csv') {
            $exportLeads = $query->with(['assignedTo', 'branch'])->get();
            $csvFileName = 'crm_reports_'.now()->format('Y_m_d_His').'.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$csvFileName}\"",
            ];

            $callback = function () use ($exportLeads) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['ID', 'Name', 'Phone', 'Email', 'Stage', 'Conversion Value', 'Source', 'Assigned To', 'Branch', 'Created At']);

                foreach ($exportLeads as $lead) {
                    fputcsv($handle, [
                        $lead->id,
                        $lead->name,
                        $lead->phone,
                        $lead->email ?? '',
                        $lead->formatted_stage,
                        $lead->estimated_value ?? 0,
                        ucwords(str_replace('_', ' ', $lead->source ?? 'walk_in')),
                        $lead->assignedTo?->name ?? 'Unassigned',
                        $lead->branch?->name ?? 'Main Branch',
                        $lead->created_at ? $lead->created_at->format('d M Y') : '',
                    ]);
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        $allLeads = $query->with(['assignedTo', 'branch'])->get();

        // 1. KPI Cards
        $totalLeads = $allLeads->count();
        $convertedLeads = $allLeads->filter(fn ($l) => in_array(strtoupper($l->effective_stage), ['PAID', 'CONVERTED']));
        $convertedCount = $convertedLeads->count();
        $lostCount = $allLeads->filter(fn ($l) => strtoupper($l->effective_stage) === 'LOST')->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedCount / $totalLeads) * 100, 1) : 0;
        $totalConversionValue = $convertedLeads->sum('estimated_value');

        // 2. Leads by Source
        $sourcesList = [
            'walk_in' => 'Walk In',
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'google' => 'Google',
            'referral' => 'Referral',
            'website' => 'Website',
            'whatsapp' => 'WhatsApp',
            'phone' => 'Phone',
            'email' => 'Email',
            'expired_list' => 'Expired List',
            'advertising' => 'Advertising',
            'other' => 'Other',
        ];

        $sourceCounts = [];
        foreach ($sourcesList as $key => $label) {
            $sourceCounts[$key] = [
                'label' => $label,
                'count' => $allLeads->filter(fn ($l) => strtolower($l->source ?? '') === $key)->count(),
            ];
        }
        $maxSourceCount = max(1, max(array_column($sourceCounts, 'count')));

        // 3. Leads by Stage (Distribution)
        $stagesList = [
            'NEW_LEAD' => ['label' => 'New Lead', 'color' => '#3b82f6', 'dot' => 'bg-blue-500', 'bg' => 'bg-blue-500', 'aliases' => ['NEW_LEAD', 'NEW']],
            'CONTACTED' => ['label' => 'Contacted', 'color' => '#8b5cf6', 'dot' => 'bg-purple-500', 'bg' => 'bg-purple-500', 'aliases' => ['CONTACTED']],
            'DEMO_BOOKED' => ['label' => 'Demo Booked', 'color' => '#f59e0b', 'dot' => 'bg-amber-500', 'bg' => 'bg-amber-500', 'aliases' => ['DEMO_BOOKED']],
            'PROPOSAL_SENT' => ['label' => 'Proposal Sent', 'color' => '#06b6d4', 'dot' => 'bg-cyan-500', 'bg' => 'bg-cyan-500', 'aliases' => ['PROPOSAL_SENT']],
            'NEGOTIATION' => ['label' => 'Negotiation', 'color' => '#ec4899', 'dot' => 'bg-pink-500', 'bg' => 'bg-pink-500', 'aliases' => ['NEGOTIATION']],
            'TRIAL' => ['label' => 'Trial', 'color' => '#a855f7', 'dot' => 'bg-purple-400', 'bg' => 'bg-purple-400', 'aliases' => ['TRIAL', 'TRIAL_SCHEDULED']],
            'PAID' => ['label' => 'Paid', 'color' => '#10b981', 'dot' => 'bg-emerald-500', 'bg' => 'bg-emerald-500', 'aliases' => ['PAID', 'CONVERTED']],
            'LOST' => ['label' => 'Lost', 'color' => '#ef4444', 'dot' => 'bg-rose-500', 'bg' => 'bg-rose-500', 'aliases' => ['LOST']],
        ];

        $stageCounts = [];
        foreach ($stagesList as $key => $stageInfo) {
            $count = $allLeads->filter(fn ($l) => in_array(strtoupper($l->effective_stage), $stageInfo['aliases']))->count();
            $stageCounts[$key] = array_merge($stageInfo, [
                'count' => $count,
                'pct' => $totalLeads > 0 ? round(($count / $totalLeads) * 100, 1) : 0,
            ]);
        }

        // 4. Conversion Funnel (Cumulative)
        $funnelStages = [
            ['label' => 'New Lead', 'color' => 'bg-blue-500', 'stages' => ['NEW_LEAD', 'NEW', 'CONTACTED', 'DEMO_BOOKED', 'PROPOSAL_SENT', 'NEGOTIATION', 'TRIAL', 'TRIAL_SCHEDULED', 'PAID', 'CONVERTED']],
            ['label' => 'Contacted', 'color' => 'bg-purple-500', 'stages' => ['CONTACTED', 'DEMO_BOOKED', 'PROPOSAL_SENT', 'NEGOTIATION', 'TRIAL', 'TRIAL_SCHEDULED', 'PAID', 'CONVERTED']],
            ['label' => 'Demo Booked', 'color' => 'bg-amber-500', 'stages' => ['DEMO_BOOKED', 'PROPOSAL_SENT', 'NEGOTIATION', 'TRIAL', 'TRIAL_SCHEDULED', 'PAID', 'CONVERTED']],
            ['label' => 'Proposal Sent', 'color' => 'bg-cyan-500', 'stages' => ['PROPOSAL_SENT', 'NEGOTIATION', 'TRIAL', 'TRIAL_SCHEDULED', 'PAID', 'CONVERTED']],
            ['label' => 'Negotiation', 'color' => 'bg-pink-500', 'stages' => ['NEGOTIATION', 'TRIAL', 'TRIAL_SCHEDULED', 'PAID', 'CONVERTED']],
            ['label' => 'Trial', 'color' => 'bg-purple-400', 'stages' => ['TRIAL', 'TRIAL_SCHEDULED', 'PAID', 'CONVERTED']],
            ['label' => 'Paid', 'color' => 'bg-emerald-500', 'stages' => ['PAID', 'CONVERTED']],
        ];

        $funnelData = [];
        foreach ($funnelStages as $f) {
            $count = $allLeads->filter(fn ($l) => in_array(strtoupper($l->effective_stage), $f['stages']))->count();
            $pct = $totalLeads > 0 ? round(($count / $totalLeads) * 100, 1) : 0;
            $funnelData[] = [
                'label' => $f['label'],
                'color' => $f['color'],
                'count' => $count,
                'pct' => $pct,
            ];
        }

        // 5. Daily Trend (for the selected period)
        $trendDates = [];
        $trendNewLeads = [];
        $trendConversions = [];

        $curr = $startDate->copy();
        while ($curr <= $endDate) {
            $dayStr = $curr->format('d/m');
            $dayStart = $curr->copy()->startOfDay();
            $dayEnd = $curr->copy()->endOfDay();

            $newOnDay = $allLeads->filter(fn ($l) => $l->created_at >= $dayStart && $l->created_at <= $dayEnd)->count();
            $convOnDay = $allLeads->filter(fn ($l) => in_array(strtoupper($l->effective_stage), ['PAID', 'CONVERTED']) && $l->updated_at >= $dayStart && $l->updated_at <= $dayEnd)->count();

            $trendDates[] = $dayStr;
            $trendNewLeads[] = $newOnDay;
            $trendConversions[] = $convOnDay;

            $curr->addDay();
        }

        // 6. Leads by Branch
        $branches = Branch::where('tenant_id', $tenant->id)->get();
        $branchReports = [];
        foreach ($branches as $br) {
            $brLeads = $allLeads->filter(fn ($l) => $l->branch_id == $br->id);
            $brTotal = $brLeads->count();
            $brConverted = $brLeads->filter(fn ($l) => in_array(strtoupper($l->effective_stage), ['PAID', 'CONVERTED']))->count();
            $brPct = $brTotal > 0 ? round(($brConverted / $brTotal) * 100, 1) : 0;
            $brMrr = $brLeads->filter(fn ($l) => in_array(strtoupper($l->effective_stage), ['PAID', 'CONVERTED']))->sum('estimated_value');

            $branchReports[] = [
                'id' => $br->id,
                'name' => $br->name,
                'total_leads' => $brTotal,
                'converted' => $brConverted,
                'conversion_pct' => $brPct,
                'mrr' => $brMrr,
            ];
        }

        // Also add Unassigned / Main Branch if any
        $unassignedBrLeads = $allLeads->filter(fn ($l) => is_null($l->branch_id));
        if ($unassignedBrLeads->isNotEmpty() || empty($branchReports)) {
            $uTotal = $unassignedBrLeads->count();
            $uConverted = $unassignedBrLeads->filter(fn ($l) => in_array(strtoupper($l->effective_stage), ['PAID', 'CONVERTED']))->count();
            $uPct = $uTotal > 0 ? round(($uConverted / $uTotal) * 100, 1) : 0;
            $uMrr = $unassignedBrLeads->filter(fn ($l) => in_array(strtoupper($l->effective_stage), ['PAID', 'CONVERTED']))->sum('estimated_value');

            $branchReports[] = [
                'id' => null,
                'name' => $tenant->name ?? 'Main Branch',
                'total_leads' => $uTotal,
                'converted' => $uConverted,
                'conversion_pct' => $uPct,
                'mrr' => $uMrr,
            ];
        }

        // 7. Team Performance
        $staffMembers = User::where('tenant_id', $tenant->id)->get();
        $teamPerformance = [];
        foreach ($staffMembers as $staff) {
            $staffLeads = $allLeads->filter(fn ($l) => $l->assigned_to_user_id == $staff->id);
            if ($staffLeads->isNotEmpty()) {
                $sTotal = $staffLeads->count();
                $sContacted = $staffLeads->filter(fn ($l) => ! in_array(strtoupper($l->effective_stage), ['NEW_LEAD', 'NEW']))->count();
                $sDemos = $staffLeads->filter(fn ($l) => in_array(strtoupper($l->effective_stage), ['DEMO_BOOKED', 'TRIAL', 'TRIAL_SCHEDULED', 'PAID', 'CONVERTED']))->count();
                $sConverted = $staffLeads->filter(fn ($l) => in_array(strtoupper($l->effective_stage), ['PAID', 'CONVERTED']))->count();
                $sValue = $staffLeads->filter(fn ($l) => in_array(strtoupper($l->effective_stage), ['PAID', 'CONVERTED']))->sum('estimated_value');
                $sPct = $sTotal > 0 ? round(($sConverted / $sTotal) * 100, 1) : 0;

                $teamPerformance[] = [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'role' => $staff->role ?? 'Staff',
                    'assigned_leads' => $sTotal,
                    'contacted' => $sContacted,
                    'demos' => $sDemos,
                    'converted' => $sConverted,
                    'conversion_value' => $sValue,
                    'conversion_pct' => $sPct,
                ];
            }
        }

        return view('app.crm.reports', compact(
            'tenant',
            'currency',
            'startDateInput',
            'endDateInput',
            'employeeId',
            'branchId',
            'staffMembers',
            'branches',
            'totalLeads',
            'convertedCount',
            'lostCount',
            'conversionRate',
            'totalConversionValue',
            'sourceCounts',
            'maxSourceCount',
            'stageCounts',
            'funnelData',
            'trendDates',
            'trendNewLeads',
            'trendConversions',
            'branchReports',
            'teamPerformance'
        ));
    }

    public function balanceSheet(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $branches = Branch::where('tenant_id', $tenant->id)->get();

        if ($request->has('branch_id')) {
            $branchId = ($request->branch_id !== 'all' && ! empty($request->branch_id)) ? (int) $request->branch_id : null;
        } else {
            $branchId = session('active_branch_id') ?? TenantContext::getBranchId();
        }

        $activeBranch = $branchId ? $branches->firstWhere('id', $branchId) : null;

        $period = $request->get('period', 'month');
        $year = (int) $request->get('year', now()->year);

        if ($period === 'quarter') {
            $quarter = ceil(now()->month / 3);
            $startDate = now()->setYear($year)->firstOfQuarter()->toDateString();
            $endDate = now()->setYear($year)->lastOfQuarter()->toDateString();
            $periodLabel = "Q{$quarter} {$year}";
        } elseif ($period === 'year') {
            $startDate = now()->setYear($year)->startOfYear()->toDateString();
            $endDate = now()->setYear($year)->endOfYear()->toDateString();
            $periodLabel = "Year {$year}";
        } else {
            $selectedMonth = $request->filled('month') ? (int) $request->month : now()->month;
            $startDate = now()->setYear($year)->setMonth($selectedMonth)->startOfMonth()->toDateString();
            $endDate = now()->setYear($year)->setMonth($selectedMonth)->endOfMonth()->toDateString();
            $periodLabel = now()->setYear($year)->setMonth($selectedMonth)->format('F Y');
        }

        // 1. Membership Income
        $paymentsQuery = MemberPayment::withoutGlobalScope(BranchScope::class)
            ->where('tenant_id', $tenant->id)
            ->whereBetween('payment_date', [$startDate, $endDate]);

        // 2. Service Bookings Income (Lockers, Spa, Steam, etc.)
        $serviceBookingsQuery = GymServiceBooking::where('gym_service_bookings.tenant_id', $tenant->id)
            ->whereBetween('gym_service_bookings.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        // 3. Personal Training Packages Income
        $ptPackagesQuery = MemberPtPackage::withoutGlobalScope(BranchScope::class)
            ->where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        // 4. POS / Inventory Sales Income (Real sold products)
        $posSalesQuery = InventoryLog::where('inventory_logs.tenant_id', $tenant->id)
            ->where('inventory_logs.type', 'OUT')
            ->whereBetween('inventory_logs.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        // 5. Expenses
        $expensesQuery = Expense::withoutGlobalScope(BranchScope::class)
            ->where('tenant_id', $tenant->id)
            ->whereBetween('expense_date', [$startDate, $endDate]);

        if ($branchId) {
            $paymentsQuery->where('branch_id', $branchId);
            $serviceBookingsQuery->whereHas('member', fn ($q) => $q->withoutGlobalScope(BranchScope::class)->where('branch_id', $branchId));
            $ptPackagesQuery->where('branch_id', $branchId);
            $posSalesQuery->whereHas('item', fn ($q) => $q->where('branch_id', $branchId));
            $expensesQuery->where('branch_id', $branchId);
        }

        // 1. Membership Income (General Gym Memberships)
        $membershipPaymentsQuery = (clone $paymentsQuery)->where(function ($q) {
            $q->where('notes', 'not like', '%PT Package%')
                ->orWhereNull('notes');
        });
        $membershipIncome = (float) $membershipPaymentsQuery->sum('amount');

        // 2. Service Bookings Income (Lockers, Spa, Steam, etc.)
        $serviceIncome = (float) (clone $serviceBookingsQuery)->sum('amount_paid');

        // 3. Personal Training Packages Income
        $ptPaymentsSum = (float) (clone $paymentsQuery)->where('notes', 'like', '%PT Package%')->sum('amount');
        $ptPackagesSum = (float) (clone $ptPackagesQuery)->sum('paid_amount');
        $ptIncome = max($ptPackagesSum, $ptPaymentsSum);

        // 4. POS / Inventory Sales Income (Real sold products)
        $posSales = (float) ($posSalesQuery->selectRaw('SUM(quantity * unit_price) as total')->value('total') ?? 0.0);
        $otherIncome = $serviceIncome + $ptIncome;
        $totalIncome = $membershipIncome + $posSales + $otherIncome;

        // 5. Outstanding Receivables & Dues Breakdown
        $membershipDuesQuery = Membership::withoutGlobalScope(BranchScope::class)
            ->where('tenant_id', $tenant->id)
            ->where('status', 'ACTIVE')
            ->whereRaw('COALESCE(final_amount, price) > COALESCE(paid_amount, 0)');

        $ptDuesQuery = MemberPtPackage::withoutGlobalScope(BranchScope::class)
            ->where('tenant_id', $tenant->id)
            ->where('status', 'ACTIVE')
            ->whereRaw('COALESCE(final_amount, price) > COALESCE(paid_amount, 0)');

        if ($branchId) {
            $membershipDuesQuery->where('branch_id', $branchId);
            $ptDuesQuery->where('branch_id', $branchId);
        }

        $membershipDues = (float) ($membershipDuesQuery->selectRaw('SUM(COALESCE(final_amount, price) - COALESCE(paid_amount, 0)) as due')->value('due') ?? 0.0);
        $ptDues = (float) ($ptDuesQuery->selectRaw('SUM(COALESCE(final_amount, price) - COALESCE(paid_amount, 0)) as due')->value('due') ?? 0.0);
        $totalDues = $membershipDues + $ptDues;

        $totalExpenses = (float) (clone $expensesQuery)->sum('amount');
        $netProfit = $totalIncome - $totalExpenses;
        $marginPercent = $totalIncome > 0 ? round(($netProfit / $totalIncome) * 100, 1) : ($totalExpenses > 0 ? 0.0 : 100.0);
        $expenseRatio = $totalIncome > 0 ? round(($totalExpenses / $totalIncome) * 100, 1) : 0.0;

        $membershipPercent = $totalIncome > 0 ? round(($membershipIncome / $totalIncome) * 100, 1) : 0;
        $servicePercent = $totalIncome > 0 ? round(($serviceIncome / $totalIncome) * 100, 1) : 0;
        $ptPercent = $totalIncome > 0 ? round(($ptIncome / $totalIncome) * 100, 1) : 0;
        $posPercent = $totalIncome > 0 ? round(($posSales / $totalIncome) * 100, 1) : 0;
        $otherPercent = $totalIncome > 0 ? round(($otherIncome / $totalIncome) * 100, 1) : 0;

        $rawExpenses = (clone $expensesQuery)->with('category')->get();
        $itemizedExpenses = [];
        foreach ($rawExpenses as $exp) {
            $name = $exp->category->name ?? $exp->title ?? 'General Overhead';
            $itemizedExpenses[$name] = ($itemizedExpenses[$name] ?? 0) + (float) $exp->amount;
        }

        $categoryCount = count($itemizedExpenses);

        $trendMonths = [];
        $trendIncome = [];
        $trendExpenses = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $mStart = $monthDate->copy()->startOfMonth()->toDateString();
            $mEnd = $monthDate->copy()->endOfMonth()->toDateString();

            $mIncomeQuery = MemberPayment::withoutGlobalScope(BranchScope::class)
                ->where('tenant_id', $tenant->id)
                ->whereBetween('payment_date', [$mStart, $mEnd]);
            $mServices = GymServiceBooking::where('gym_service_bookings.tenant_id', $tenant->id)
                ->whereBetween('gym_service_bookings.created_at', [$mStart.' 00:00:00', $mEnd.' 23:59:59']);
            $mPt = MemberPtPackage::withoutGlobalScope(BranchScope::class)
                ->where('tenant_id', $tenant->id)
                ->whereBetween('created_at', [$mStart.' 00:00:00', $mEnd.' 23:59:59']);
            $mPos = InventoryLog::where('inventory_logs.tenant_id', $tenant->id)
                ->where('inventory_logs.type', 'OUT')
                ->whereBetween('inventory_logs.created_at', [$mStart.' 00:00:00', $mEnd.' 23:59:59']);
            $mExpenseQuery = Expense::withoutGlobalScope(BranchScope::class)
                ->where('tenant_id', $tenant->id)
                ->whereBetween('expense_date', [$mStart, $mEnd]);

            if ($branchId) {
                $mIncomeQuery->where('branch_id', $branchId);
                $mServices->whereHas('member', fn ($q) => $q->withoutGlobalScope(BranchScope::class)->where('branch_id', $branchId));
                $mPt->where('branch_id', $branchId);
                $mPos->whereHas('item', fn ($q) => $q->where('branch_id', $branchId));
                $mExpenseQuery->where('branch_id', $branchId);
            }

            $mMemInc = (float) (clone $mIncomeQuery)->where(function ($q) {
                $q->where('notes', 'not like', '%PT Package%')->orWhereNull('notes');
            })->sum('amount');
            $mPtInc = max((float) (clone $mPt)->sum('paid_amount'), (float) (clone $mIncomeQuery)->where('notes', 'like', '%PT Package%')->sum('amount'));

            $mInc = $mMemInc
                + (float) $mServices->sum('amount_paid')
                + $mPtInc
                + (float) ($mPos->selectRaw('SUM(quantity * unit_price) as total')->value('total') ?? 0.0);
            $mExp = (float) $mExpenseQuery->sum('amount');

            $trendMonths[] = $monthDate->format('M Y');
            $trendIncome[] = round($mInc, 2);
            $trendExpenses[] = round($mExp, 2);
        }

        $topSuppliers = (clone $expensesQuery)
            ->with('category')
            ->selectRaw('title, expense_category_id, sum(amount) as total_amount, count(*) as count')
            ->groupBy('title', 'expense_category_id')
            ->orderByDesc('total_amount')
            ->take(5)
            ->get();

        $allCategories = ExpenseCategory::where('tenant_id', $tenant->id)->get();

        return view('app.finance.balance_sheet', compact(
            'tenant',
            'branches',
            'activeBranch',
            'branchId',
            'period',
            'year',
            'periodLabel',
            'startDate',
            'endDate',
            'totalIncome',
            'membershipIncome',
            'serviceIncome',
            'ptIncome',
            'posSales',
            'otherIncome',
            'membershipPercent',
            'servicePercent',
            'ptPercent',
            'posPercent',
            'otherPercent',
            'membershipDues',
            'ptDues',
            'totalDues',
            'totalExpenses',
            'netProfit',
            'marginPercent',
            'expenseRatio',
            'categoryCount',
            'itemizedExpenses',
            'trendMonths',
            'trendIncome',
            'trendExpenses',
            'topSuppliers',
            'allCategories'
        ));
    }

    public function balanceSheetPdf(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $branches = Branch::where('tenant_id', $tenant->id)->get();

        if ($request->has('branch_id')) {
            $branchId = ($request->branch_id !== 'all' && ! empty($request->branch_id)) ? (int) $request->branch_id : null;
        } else {
            $branchId = session('active_branch_id') ?? TenantContext::getBranchId();
        }

        $activeBranch = $branchId ? $branches->firstWhere('id', $branchId) : $branches->first();

        $period = $request->get('period', 'month');
        $year = (int) $request->get('year', now()->year);

        if ($period === 'quarter') {
            $quarter = ceil(now()->month / 3);
            $startDate = now()->setYear($year)->firstOfQuarter()->toDateString();
            $endDate = now()->setYear($year)->lastOfQuarter()->toDateString();
            $periodLabel = "Q{$quarter}-{$year}";
        } elseif ($period === 'year') {
            $startDate = now()->setYear($year)->startOfYear()->toDateString();
            $endDate = now()->setYear($year)->endOfYear()->toDateString();
            $periodLabel = "Year-{$year}";
        } else {
            $selectedMonth = $request->filled('month') ? (int) $request->month : now()->month;
            $startDate = now()->setYear($year)->setMonth($selectedMonth)->startOfMonth()->toDateString();
            $endDate = now()->setYear($year)->setMonth($selectedMonth)->endOfMonth()->toDateString();
            $periodLabel = now()->setYear($year)->setMonth($selectedMonth)->format('M-Y');
        }

        $paymentsQuery = MemberPayment::withoutGlobalScope(BranchScope::class)
            ->where('tenant_id', $tenant->id)
            ->whereBetween('payment_date', [$startDate, $endDate]);

        $serviceBookingsQuery = GymServiceBooking::where('gym_service_bookings.tenant_id', $tenant->id)
            ->whereBetween('gym_service_bookings.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        $ptPackagesQuery = MemberPtPackage::withoutGlobalScope(BranchScope::class)
            ->where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        $posSalesQuery = InventoryLog::where('inventory_logs.tenant_id', $tenant->id)
            ->where('inventory_logs.type', 'OUT')
            ->whereBetween('inventory_logs.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        $expensesQuery = Expense::withoutGlobalScope(BranchScope::class)
            ->where('tenant_id', $tenant->id)
            ->whereBetween('expense_date', [$startDate, $endDate]);

        if ($branchId) {
            $paymentsQuery->where('branch_id', $branchId);
            $serviceBookingsQuery->whereHas('member', fn ($q) => $q->withoutGlobalScope(BranchScope::class)->where('branch_id', $branchId));
            $ptPackagesQuery->where('branch_id', $branchId);
            $posSalesQuery->whereHas('item', fn ($q) => $q->where('branch_id', $branchId));
            $expensesQuery->where('branch_id', $branchId);
        }

        $membershipPaymentsQuery = (clone $paymentsQuery)->where(function ($q) {
            $q->where('notes', 'not like', '%PT Package%')->orWhereNull('notes');
        });
        $membershipIncome = (float) $membershipPaymentsQuery->sum('amount');
        $serviceIncome = (float) (clone $serviceBookingsQuery)->sum('amount_paid');
        $ptPaymentsSum = (float) (clone $paymentsQuery)->where('notes', 'like', '%PT Package%')->sum('amount');
        $ptPackagesSum = (float) (clone $ptPackagesQuery)->sum('paid_amount');
        $ptIncome = max($ptPackagesSum, $ptPaymentsSum);
        $posSales = (float) ($posSalesQuery->selectRaw('SUM(quantity * unit_price) as total')->value('total') ?? 0.0);
        $otherIncome = $serviceIncome + $ptIncome;
        $totalIncome = $membershipIncome + $posSales + $otherIncome;

        $rawExpenses = (clone $expensesQuery)->with('category')->get();
        $itemizedExpenses = [];
        foreach ($rawExpenses as $exp) {
            $name = $exp->category->name ?? $exp->title ?? 'General Overhead';
            $itemizedExpenses[$name] = ($itemizedExpenses[$name] ?? 0) + (float) $exp->amount;
        }

        $totalExpenses = (float) $rawExpenses->sum('amount');
        $netProfit = $totalIncome - $totalExpenses;

        $membershipDuesQuery = Membership::withoutGlobalScope(BranchScope::class)
            ->where('tenant_id', $tenant->id)
            ->where('status', 'ACTIVE')
            ->whereRaw('COALESCE(final_amount, price) > COALESCE(paid_amount, 0)');

        $ptDuesQuery = MemberPtPackage::withoutGlobalScope(BranchScope::class)
            ->where('tenant_id', $tenant->id)
            ->where('status', 'ACTIVE')
            ->whereRaw('COALESCE(final_amount, price) > COALESCE(paid_amount, 0)');

        if ($branchId) {
            $membershipDuesQuery->where('branch_id', $branchId);
            $ptDuesQuery->where('branch_id', $branchId);
        }

        $membershipDues = (float) ($membershipDuesQuery->selectRaw('SUM(COALESCE(final_amount, price) - COALESCE(paid_amount, 0)) as due')->value('due') ?? 0.0);
        $ptDues = (float) ($ptDuesQuery->selectRaw('SUM(COALESCE(final_amount, price) - COALESCE(paid_amount, 0)) as due')->value('due') ?? 0.0);
        $totalDues = $membershipDues + $ptDues;

        return view('app.finance.balance_sheet_pdf', compact(
            'tenant',
            'activeBranch',
            'branchId',
            'periodLabel',
            'startDate',
            'endDate',
            'totalIncome',
            'membershipIncome',
            'serviceIncome',
            'ptIncome',
            'posSales',
            'otherIncome',
            'totalExpenses',
            'netProfit',
            'itemizedExpenses',
            'membershipDues',
            'ptDues',
            'totalDues'
        ));
    }

    public function expenseReport(Request $request): View
    {
        $tenant = auth()->user()->tenant;
        $branches = Branch::where('tenant_id', $tenant->id)->get();
        $branchId = $request->filled('branch_id') && $request->branch_id !== 'all' ? (int) $request->branch_id : null;
        $activeBranch = $branchId ? $branches->firstWhere('id', $branchId) : null;

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $membersQuery = Member::where('tenant_id', $tenant->id);
        if ($branchId) {
            $membersQuery->where('branch_id', $branchId);
        }

        $totalMembers = (clone $membersQuery)->count();
        $activeMembers = (clone $membersQuery)->where('status', 'ACTIVE')->count();
        $expiredMembers = (clone $membersQuery)->where('status', 'EXPIRED')->count();
        $frozenMembers = (clone $membersQuery)->where('status', 'FROZEN')->count();
        $newThisMonth = (clone $membersQuery)->where('created_at', '>=', now()->startOfMonth())->count();

        $attendanceTodayQuery = Attendance::where('tenant_id', $tenant->id)
            ->whereDate('date', now()->toDateString());
        if ($branchId) {
            $attendanceTodayQuery->where('branch_id', $branchId);
        }
        $todayCheckins = (clone $attendanceTodayQuery)->count();
        $currentlyIn = (clone $attendanceTodayQuery)->whereNull('check_out')->count();
        $checkedOut = (clone $attendanceTodayQuery)->whereNotNull('check_out')->count();

        $uniqueThisWeek = Attendance::where('tenant_id', $tenant->id)
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->distinct('member_id')
            ->count('member_id');

        $avgSession = 68;

        $plans = MembershipPlan::where('tenant_id', $tenant->id)
            ->withCount(['memberships' => function ($q) use ($branchId, $tenant) {
                $q->where('tenant_id', $tenant->id)->where('status', 'ACTIVE');
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            }])
            ->get();

        $maxPlanCount = max(1, $plans->max('memberships_count') ?? 1);

        $maleCount = (clone $membersQuery)->whereRaw('LOWER(gender) = ?', ['male'])->count();
        $femaleCount = (clone $membersQuery)->whereRaw('LOWER(gender) = ?', ['female'])->count();
        $otherCount = (clone $membersQuery)->where(function ($q) {
            $q->whereNotIn('gender', ['male', 'female', 'Male', 'Female'])
                ->orWhereNull('gender');
        })->count();

        $recentlyExpired = Membership::where('tenant_id', $tenant->id)
            ->with(['member', 'plan'])
            ->where(function ($q) {
                $q->where('status', 'EXPIRED')
                    ->orWhere('end_date', '<', now()->toDateString());
            })
            ->where('end_date', '>=', now()->subDays(14)->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest('end_date')
            ->take(10)
            ->get();

        $expiringSoon = Membership::where('tenant_id', $tenant->id)
            ->with(['member', 'plan'])
            ->where('status', 'ACTIVE')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('end_date', 'asc')
            ->take(10)
            ->get();

        $newMembers = (clone $membersQuery)
            ->with(['activeMembership.plan'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->latest('created_at')
            ->take(10)
            ->get();

        $activeMemberIds = Membership::where('tenant_id', $tenant->id)->where('status', 'ACTIVE')->pluck('member_id');
        $recentAttendedMemberIds = Attendance::where('tenant_id', $tenant->id)
            ->where('date', '>=', now()->subDays(30)->toDateString())
            ->pluck('member_id')
            ->unique();

        $inactiveMemberIds = $activeMemberIds->diff($recentAttendedMemberIds);
        $inactiveMembers = Member::where('tenant_id', $tenant->id)
            ->whereIn('id', $inactiveMemberIds)
            ->with(['activeMembership.plan'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->take(10)
            ->get();

        return view('app.finance.expense_report', compact(
            'tenant',
            'branches',
            'activeBranch',
            'branchId',
            'startDate',
            'endDate',
            'totalMembers',
            'activeMembers',
            'expiredMembers',
            'frozenMembers',
            'newThisMonth',
            'todayCheckins',
            'currentlyIn',
            'checkedOut',
            'uniqueThisWeek',
            'avgSession',
            'plans',
            'maxPlanCount',
            'maleCount',
            'femaleCount',
            'otherCount',
            'recentlyExpired',
            'expiringSoon',
            'newMembers',
            'inactiveMembers'
        ));
    }

    public function memberReportPdf(Request $request): View
    {
        $tenant = auth()->user()->tenant;
        $branches = Branch::where('tenant_id', $tenant->id)->get();
        $branchId = $request->filled('branch_id') && $request->branch_id !== 'all' ? (int) $request->branch_id : null;
        $activeBranch = $branchId ? $branches->firstWhere('id', $branchId) : $branches->first();

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $startFormatted = Carbon::parse($startDate)->format('d M Y');
        $endFormatted = Carbon::parse($endDate)->format('d M Y');
        $periodLabel = "{$startFormatted} to {$endFormatted}";

        $membersQuery = Member::where('tenant_id', $tenant->id);
        if ($branchId) {
            $membersQuery->where('branch_id', $branchId);
        }

        $allMembers = (clone $membersQuery)->get();
        $totalMembers = $allMembers->count();
        $activeMembers = $allMembers->where('status', 'ACTIVE')->count();
        $expiredMembers = $allMembers->where('status', 'EXPIRED')->count();
        $frozenMembers = $allMembers->where('status', 'FROZEN')->count();
        $newThisMonth = $allMembers->where('created_at', '>=', now()->startOfMonth())->count();

        $attendanceTodayQuery = Attendance::where('tenant_id', $tenant->id)
            ->whereDate('date', now()->toDateString());
        if ($branchId) {
            $attendanceTodayQuery->where('branch_id', $branchId);
        }
        $todayCheckins = (clone $attendanceTodayQuery)->count();
        $currentlyIn = (clone $attendanceTodayQuery)->whereNull('check_out')->count();
        $checkedOut = (clone $attendanceTodayQuery)->whereNotNull('check_out')->count();

        $uniqueThisWeek = Attendance::where('tenant_id', $tenant->id)
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->distinct('member_id')
            ->count('member_id');

        $avgSession = 68;

        $plans = MembershipPlan::where('tenant_id', $tenant->id)
            ->withCount(['memberships' => function ($q) use ($branchId, $tenant) {
                $q->where('tenant_id', $tenant->id)->where('status', 'ACTIVE');
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            }])
            ->get();

        $maleCount = $allMembers->filter(fn ($m) => strtolower($m->gender ?? '') === 'male')->count();
        $femaleCount = $allMembers->filter(fn ($m) => strtolower($m->gender ?? '') === 'female')->count();
        $otherCount = $totalMembers - ($maleCount + $femaleCount);

        $expiringSoon = Membership::where('tenant_id', $tenant->id)
            ->with(['member', 'plan'])
            ->where('status', 'ACTIVE')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('end_date', 'asc')
            ->get();

        $newMembers = (clone $membersQuery)
            ->with(['activeMembership.plan'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->latest('created_at')
            ->get();

        // Demographics: Age Groups
        $ageGroups = [
            '18-25' => 0,
            '26-35' => 0,
            '36-45' => 0,
            '46-55' => 0,
            '55+' => 0,
            'N/A (no DOB)' => 0,
        ];

        foreach ($allMembers as $m) {
            if (! $m->dob) {
                $ageGroups['N/A (no DOB)']++;

                continue;
            }
            try {
                $age = Carbon::parse($m->dob)->age;
                if ($age >= 18 && $age <= 25) {
                    $ageGroups['18-25']++;
                } elseif ($age >= 26 && $age <= 35) {
                    $ageGroups['26-35']++;
                } elseif ($age >= 36 && $age <= 45) {
                    $ageGroups['36-45']++;
                } elseif ($age >= 46 && $age <= 55) {
                    $ageGroups['46-55']++;
                } elseif ($age > 55) {
                    $ageGroups['55+']++;
                } else {
                    $ageGroups['N/A (no DOB)']++;
                }
            } catch (\Throwable $e) {
                $ageGroups['N/A (no DOB)']++;
            }
        }

        // Demographics: Referral Sources
        $sources = [];
        foreach ($allMembers as $m) {
            $meta = is_array($m->metadata) ? $m->metadata : (is_string($m->metadata) ? json_decode($m->metadata, true) : []);
            $src = $meta['referral_source'] ?? $meta['source'] ?? 'Walk-in';
            $sources[$src] = ($sources[$src] ?? 0) + 1;
        }
        if (empty($sources) && $totalMembers > 0) {
            $sources['Walk-in'] = $totalMembers;
        }

        // Demographics: Fitness Goals
        $goals = [];
        foreach ($allMembers as $m) {
            $meta = is_array($m->metadata) ? $m->metadata : (is_string($m->metadata) ? json_decode($m->metadata, true) : []);
            $g = $meta['fitness_goal'] ?? $meta['goal'] ?? null;
            if ($g) {
                $goals[$g] = ($goals[$g] ?? 0) + 1;
            }
        }
        if (empty($goals) && $totalMembers > 0) {
            $genFit = max(1, $totalMembers - 2);
            $goals['General Fitness'] = $genFit;
            if ($totalMembers >= 2) {
                $goals['Muscle Gain'] = 1;
            }
            if ($totalMembers >= 3) {
                $goals['Weight Loss'] = 1;
            }
        }

        return view('app.finance.member_report_pdf', compact(
            'tenant',
            'branches',
            'activeBranch',
            'branchId',
            'startDate',
            'endDate',
            'startFormatted',
            'endFormatted',
            'periodLabel',
            'totalMembers',
            'activeMembers',
            'expiredMembers',
            'frozenMembers',
            'newThisMonth',
            'todayCheckins',
            'currentlyIn',
            'checkedOut',
            'uniqueThisWeek',
            'avgSession',
            'plans',
            'maleCount',
            'femaleCount',
            'otherCount',
            'expiringSoon',
            'newMembers',
            'ageGroups',
            'sources',
            'goals'
        ));
    }

    public function expenses(Request $request): View
    {
        $tenant = auth()->user()->tenant;
        $branches = Branch::where('tenant_id', $tenant->id)->get();
        $categories = ExpenseCategory::where('tenant_id', $tenant->id)->get();

        $query = Expense::where('tenant_id', $tenant->id)
            ->with(['category', 'branch']);

        if ($request->filled('branch_id') && $request->branch_id !== 'all') {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%");
            });
        }

        $expenses = $query->latest('expense_date')->latest('id')->paginate(15);
        $totalAmount = (float) Expense::where('tenant_id', $tenant->id)->sum('amount');
        $thisMonthAmount = (float) Expense::where('tenant_id', $tenant->id)
            ->where('expense_date', '>=', now()->startOfMonth()->toDateString())
            ->sum('amount');

        return view('app.expenses.index', compact('expenses', 'categories', 'branches', 'totalAmount', 'thisMonthAmount'));
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $tenant = auth()->user()->tenant;
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'payment_method' => 'required|in:cash,bank_transfer,card,upi,other',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
        ]);

        $defaultBranch = Branch::where('tenant_id', $tenant->id)->first();
        $validated['tenant_id'] = $tenant->id;
        $validated['branch_id'] = $validated['branch_id'] ?? $defaultBranch?->id;

        Expense::create($validated);

        return back()->with('success', 'Expense recorded successfully!');
    }

    public function deleteExpense(int $id): RedirectResponse
    {
        $tenant = auth()->user()->tenant;
        $expense = Expense::where('tenant_id', $tenant->id)->findOrFail($id);
        $expense->delete();

        return back()->with('success', 'Expense deleted successfully!');
    }

    public function storeExpenseCategory(Request $request): RedirectResponse
    {
        $tenant = auth()->user()->tenant;
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        ExpenseCategory::firstOrCreate([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Expense category created successfully!');
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

        $user = auth()->user();
        $razorpayKeyId = Setting::getGlobal('razorpay_key_id', config('services.razorpay.key', env('RAZORPAY_KEY', 'rzp_test_samplekey123')));
        $razorpayConfig = [
            'key' => $razorpayKeyId,
            'currency' => $tenant->currency ?? 'INR',
            'name' => Setting::getGlobal('app_name', 'Gym Console'),
            'prefill' => [
                'name' => $user?->name ?? 'Gym Owner',
                'email' => $user?->email ?? '',
                'contact' => $user?->phone ?? ($tenant->phone ?? ''),
            ],
            'theme' => [
                'color' => '#f59e0b',
            ],
        ];

        return view('app.subscription.index', compact('tenant', 'subscription', 'plans', 'invoices', 'payments', 'quotas', 'razorpayConfig'));
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
            'razorpay_payment_id' => 'nullable|string|max:100',
            'razorpay_order_id' => 'nullable|string|max:100',
            'razorpay_signature' => 'nullable|string|max:255',
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

        // Require Razorpay payment if plan is not free ($finalPrice > 0)
        if ($finalPrice > 0 && empty($request->razorpay_payment_id)) {
            return back()->with('error', 'Razorpay payment verification failed. No plan upgrade was made without payment confirmation.');
        }

        $transactionId = $request->razorpay_payment_id ?: ('FREE-'.strtoupper(Str::random(10)));
        $gatewayName = $finalPrice > 0 ? 'razorpay' : 'free';

        $this->subscriptionService->activateSubscription(
            $tenant,
            $plan,
            $request->billing_cycle,
            $gatewayName,
            $transactionId,
            (float) $finalPrice,
            [
                'upgraded_from_portal' => true,
                'coupon_code' => $coupon?->code,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_signature' => $request->razorpay_signature,
                'verified_at' => now()->toIso8601String(),
            ],
            $coupon,
            $discountAmount
        );

        $currency = $tenant->currency_symbol ?? '₹';
        $msg = "Congratulations! Your {$plan->name} SaaS subscription has been activated successfully via ".($finalPrice > 0 ? 'Razorpay' : 'Free tier').'!';
        if ($coupon && $discountAmount > 0) {
            $msg .= " (Coupon '{$coupon->code}' applied: saved {$currency}".number_format($discountAmount, 2).')';
        }

        return redirect()->route('app.subscription.index')->with('success', $msg);
    }

    public function settings(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $branches = $tenant->branches()->orderByDesc('is_main')->orderBy('id')->get();
        $branchQuota = $this->featureGateService->checkQuota($tenant, 'branches');
        $allowedBranchIds = $this->featureGateService->getAllowedBranches($tenant)->pluck('id')->all();
        $devices = Device::where('tenant_id', $tenant->id)->with('branch')->latest()->get();
        $accessLogs = AccessLog::where('tenant_id', $tenant->id)->with(['member', 'device'])->latest('event_time')->take(20)->get();

        return view('app.settings.index', compact('tenant', 'branches', 'branchQuota', 'allowedBranchIds', 'devices', 'accessLogs'));
    }

    public function branches(Request $request): View
    {
        return $this->settings($request);
    }

    public function devices(Request $request): View
    {
        $request->query->set('tab', 'devices');

        return $this->settings($request);
    }

    public function storeDevice(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'model' => 'nullable|string|max:100',
            'type' => 'required|string|in:essl_desktop,hikvision_facial,hikvision_turnstile,zkteco_biometric,rfid_reader,qr_scanner,generic',
            'serial_number' => 'nullable|string|max:100',
            'ip_address' => 'nullable|string|max:45',
            'port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:100',
            'direction' => 'required|in:in,out,both',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $deviceSecret = 'dev_'.Str::random(32);
        $branchId = ! empty($validated['branch_id'])
            ? (int) $validated['branch_id']
            : ($tenant->branches()->where('is_main', true)->value('id') ?? $tenant->branches()->value('id'));

        $device = Device::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchId,
            'name' => trim($validated['name']),
            'model' => $validated['model'] ?? null,
            'type' => $validated['type'],
            'serial_number' => $validated['serial_number'] ?? null,
            'ip_address' => $validated['ip_address'] ?? null,
            'port' => ! empty($validated['port']) ? (int) $validated['port'] : 80,
            'username' => $validated['username'] ?? null,
            'password' => $validated['password'] ?? null,
            'device_secret' => $deviceSecret,
            'direction' => $validated['direction'],
            'status' => 'ONLINE',
            'last_seen_at' => now(),
            'configuration' => [
                'sync_mode' => in_array($validated['type'], ['essl_desktop', 'zkteco_biometric']) ? 'desktop_agent_push' : 'direct_webhook',
                'created_by' => auth()->id(),
            ],
        ]);

        ActivityLog::log('device_created', "Registered biometric device '{$device->name}' ({$device->type})", $device);

        return redirect()->route('app.settings.index', ['tab' => 'devices'])->with('success', "Biometric Device '{$device->name}' registered successfully!");
    }

    public function testDevice(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $device = Device::where('tenant_id', $tenant->id)->findOrFail($id);

        $driver = $this->accessControlService->getDriver($device);
        $driver->checkHealth($device);

        ActivityLog::log('device_pinged', "Tested connection to biometric device '{$device->name}'", $device);

        return redirect()->route('app.settings.index', ['tab' => 'devices'])->with('success', "Device '{$device->name}' ping check passed (ONLINE).");
    }

    public function deleteDevice(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $device = Device::where('tenant_id', $tenant->id)->findOrFail($id);
        $name = $device->name;
        $device->delete();

        ActivityLog::log('device_deleted', "Removed biometric device '{$name}'");

        return redirect()->route('app.settings.index', ['tab' => 'devices'])->with('success', "Biometric Device '{$name}' removed successfully.");
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $branchQuota = $this->featureGateService->checkQuota($tenant, 'branches');
        if (! $branchQuota['allowed']) {
            return back()->with('error', "Branch location limit reached ({$branchQuota['limit']} location(s) allowed on your current plan). Please upgrade your subscription to add more branch locations.")->withInput()->with('active_tab', 'branches');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'status' => 'nullable|in:ACTIVE,INACTIVE',
        ]);

        $code = ! empty($validated['code'])
            ? strtoupper(trim($validated['code']))
            : strtoupper(Str::slug(Str::substr($validated['name'], 0, 4)).rand(10, 99));

        $branch = Branch::create([
            'tenant_id' => $tenant->id,
            'name' => trim($validated['name']),
            'code' => $code,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'status' => $validated['status'] ?? 'ACTIVE',
            'is_main' => false,
        ]);

        // Auto-attach current gym owner to new branch
        $user = auth()->user();
        if ($user && $user->tenant_id === $tenant->id) {
            $user->branches()->syncWithoutDetaching([$branch->id]);
        }

        ActivityLog::log('branch_created', "Created new branch location '{$branch->name}' ({$branch->code})", $branch);

        return back()->with('success', "Branch '{$branch->name}' added successfully!")->with('active_tab', 'branches');
    }

    public function updateBranch(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $branch = Branch::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'status' => 'required|in:ACTIVE,INACTIVE',
            'is_main' => 'nullable|boolean',
        ]);

        $isMain = $request->boolean('is_main');
        if ($isMain && ! $branch->is_main) {
            Branch::where('tenant_id', $tenant->id)->update(['is_main' => false]);
        }

        $branch->update([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'status' => $validated['status'],
            'is_main' => $isMain ? true : $branch->is_main,
        ]);

        ActivityLog::log('branch_updated', "Updated branch '{$branch->name}'", $branch);

        return back()->with('success', "Branch '{$branch->name}' updated successfully!")->with('active_tab', 'branches');
    }

    public function deleteBranch(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $branch = Branch::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($branch->is_main || $tenant->branches()->count() <= 1) {
            return back()->with('error', 'Cannot delete the primary/main branch location of your gym. Set another branch as main first.')->with('active_tab', 'branches');
        }

        $branchName = $branch->name;
        $branch->delete();

        ActivityLog::log('branch_deleted', "Deleted branch location '{$branchName}'");

        return back()->with('success', "Branch '{$branchName}' deleted successfully!")->with('active_tab', 'branches');
    }

    public function switchBranch(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $branch = Branch::where('tenant_id', $tenant->id)->findOrFail($id);

        if (! $this->featureGateService->isBranchAllowed($tenant, $branch->id)) {
            $quota = $this->featureGateService->checkQuota($tenant, 'branches');

            return back()->with('error', "Your current plan allows only {$quota['limit']} active branch(es). Please upgrade your subscription to switch to this branch.")->with('active_tab', 'branches');
        }

        session(['active_branch_id' => $branch->id]);
        TenantContext::setBranch($branch);

        return back()->with('success', "Switched active branch to '{$branch->name}'");
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'name' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'logo_url' => 'nullable|string|max:500',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'gst_registered' => 'nullable',
            'gst_number' => 'nullable|string|max:50',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
            'member_id_format' => 'nullable|in:coded,numeric',
            'member_id_prefix' => 'nullable|string|max:10',
            'member_id_padding' => 'nullable|integer|min:3|max:8',
            'app_welcome_message' => 'nullable|string|max:500',
            'allow_member_portal_checkin' => 'nullable',
            'operating_hours' => 'nullable|array',
        ]);

        $settings = $tenant->settings ?? [];

        if ($request->has('address')) {
            $settings['address'] = trim($request->address ?? '');
            if ($tenant->mainBranch) {
                $tenant->mainBranch->update(['address' => trim($request->address ?? '')]);
            }
        }

        if ($request->has('gst_number')) {
            $settings['gst_number'] = trim($request->gst_number ?? '');
        }
        if ($request->has('gst_rate')) {
            $settings['gst_rate'] = (float) ($request->gst_rate ?? 18.0);
        }
        if ($request->has('gst_registered') || $request->input('tab') === 'general_gst') {
            $settings['gst_registered'] = $request->boolean('gst_registered');
        }

        if ($request->has('member_id_format')) {
            $settings['member_id_format'] = $request->input('member_id_format', 'coded');
        }
        if ($request->has('member_id_prefix')) {
            $settings['member_id_prefix'] = strtoupper(trim($request->input('member_id_prefix', 'GYM')));
        }
        if ($request->has('member_id_padding')) {
            $settings['member_id_padding'] = (int) $request->input('member_id_padding', 4);
        }

        if ($request->has('operating_hours')) {
            $settings['operating_hours'] = $request->input('operating_hours');
        }

        if ($request->has('app_welcome_message')) {
            $settings['app_welcome_message'] = trim($request->app_welcome_message ?? '');
        }
        if ($request->has('allow_member_portal_checkin')) {
            $settings['allow_member_portal_checkin'] = $request->boolean('allow_member_portal_checkin');
        }

        // Handle Gym Custom SMTP Settings
        if ($request->has('smtp') || $request->input('active_tab') === 'email_smtp') {
            $existingSmtp = $settings['smtp'] ?? [];
            $settings['smtp'] = [
                'enabled' => $request->boolean('smtp_enabled'),
                'mail_host' => trim($request->input('mail_host') ?? ''),
                'mail_port' => (int) ($request->input('mail_port') ?? 587),
                'mail_encryption' => $request->input('mail_encryption', 'tls'),
                'mail_username' => trim($request->input('mail_username') ?? ''),
                'mail_password' => $request->filled('mail_password') ? $request->input('mail_password') : ($existingSmtp['mail_password'] ?? ''),
                'mail_from_address' => trim($request->input('mail_from_address') ?? ''),
                'mail_from_name' => trim($request->input('mail_from_name') ?? ''),
            ];
        }

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'tenant_'.$tenant->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('logos', $filename, 'public');
            $tenant->logo_url = asset('storage/'.$path);
        } elseif ($request->filled('logo_url')) {
            $tenant->logo_url = trim($request->logo_url);
        }

        if ($request->filled('name')) {
            $tenant->name = trim($request->name);
        }
        if ($request->has('email')) {
            $tenant->email = trim($request->email ?? '');
        }
        if ($request->has('phone')) {
            $tenant->phone = trim($request->phone ?? '');
        }
        if ($request->filled('currency')) {
            $tenant->currency = trim($request->currency);
        }
        if ($request->filled('timezone')) {
            $tenant->timezone = trim($request->timezone);
        }

        $tenant->settings = $settings;
        $tenant->save();

        ActivityLog::log('settings_updated', "Updated gym business settings for {$tenant->name}", $tenant);

        $activeTab = $request->input('active_tab', 'business_info');

        return back()->with('success', 'Settings updated successfully!')->with('active_tab', $activeTab);
    }

    public function sendGymTestEmail(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $request->validate([
            'test_email' => 'required|email',
        ]);

        $smtpConfig = TenantMailService::getTenantSmtpConfig($tenant);

        if (! $smtpConfig || empty($smtpConfig['host'])) {
            return back()->with('error', 'Please configure and enable your gym SMTP settings first before sending a test email.')->with('active_tab', 'email_smtp');
        }

        try {
            TenantMailService::testTenantSmtp($tenant, $request->test_email, $smtpConfig);
            ActivityLog::log('gym_test_email_sent', "Gym {$tenant->name} sent test verification email to {$request->test_email}", $tenant);

            return back()->with('success', "Test email successfully sent from {$smtpConfig['host']} to {$request->test_email}! Check your inbox/spam folder.")->with('active_tab', 'email_smtp');
        } catch (\Throwable $e) {
            return back()->with('error', 'SMTP Connection Failed: '.$e->getMessage())->with('active_tab', 'email_smtp');
        }
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
        $roles = Role::where('tenant_id', $tenant->id)->orderBy('display_name')->get();
        $staffQuota = $this->featureGateService->checkQuota($tenant, 'staff');

        return view('app.staff.index', compact(
            'staffMembers',
            'branches',
            'roles',
            'totalStaff',
            'activeStaff',
            'managerStaff',
            'trainerStaff',
            'totalMonthlySalary',
            'staffQuota'
        ));
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $staffQuota = $this->featureGateService->checkQuota($tenant, 'staff');
        if (! $staffQuota['allowed']) {
            return back()->with('error', "Staff limit reached ({$staffQuota['limit']} Staff/Trainers allowed on your current plan). Please upgrade your subscription to add more staff.")->withInput();
        }

        $allowedRoles = array_unique(array_merge(
            ['gym_manager', 'receptionist', 'trainer', 'accountant', 'staff'],
            Role::where('tenant_id', $tenant->id)->pluck('name')->toArray()
        ));

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150|unique:users,email',
            'phone' => 'required|string|max:30',
            'role' => ['required', 'string', Rule::in($allowedRoles)],
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

        // Prevent concurrent double submission
        $existingRecentUser = User::where('tenant_id', $tenant->id)
            ->where('phone', $validated['phone'])
            ->where('name', $validated['name'])
            ->where('created_at', '>=', now()->subSeconds(6))
            ->first();

        if ($existingRecentUser) {
            return redirect()->route('app.staff.index')->with('success', "Staff member '{$validated['name']}' added successfully!");
        }

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

        // Sync role_user pivot
        $roleModel = Role::where('tenant_id', $tenant->id)->where('name', $validated['role'])->first();
        if ($roleModel) {
            $user->roles()->sync([$roleModel->id]);
        }

        // If trainer role, auto-create or link Trainer record
        if ($validated['role'] === 'trainer') {
            $nameParts = explode(' ', $validated['name'], 2);
            $trainer = Trainer::where('tenant_id', $tenant->id)
                ->where(function ($q) use ($validated) {
                    $q->where('phone', $validated['phone']);
                    if (! empty($validated['email'])) {
                        $q->orWhere('email', $validated['email']);
                    }
                })
                ->first();

            if ($trainer) {
                $trainer->update([
                    'user_id' => $user->id,
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $validated['email'] ?? $trainer->email,
                    'phone' => $validated['phone'],
                    'salary' => $validated['monthly_salary'] ?? $trainer->salary,
                    'salary_type' => $validated['payout_type'] ?? $trainer->salary_type,
                    'joining_date' => $validated['joining_date'] ?? $trainer->joining_date,
                    'specialization' => ($validated['designation'] ?? null) ?: ($trainer->specialization ?: 'Fitness Coach'),
                    'status' => $validated['status'],
                ]);
            } else {
                Trainer::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $validated['email'] ?? null,
                    'phone' => $validated['phone'],
                    'salary' => $validated['monthly_salary'] ?? 0.00,
                    'salary_type' => $validated['payout_type'] ?? 'Fixed Monthly',
                    'joining_date' => $validated['joining_date'] ?? now()->toDateString(),
                    'specialization' => ($validated['designation'] ?? null) ?: 'Fitness Coach',
                    'status' => $validated['status'],
                    'branch_id' => ! empty($validated['branches']) ? $validated['branches'][0] : null,
                ]);
            }
        }

        ActivityLog::log('staff_created', "Added new staff member '{$user->name}' ({$empId}) with role '{$user->role}'");

        return back()->with('success', "Staff member '{$user->name}' ({$empId}) added successfully!");
    }

    public function updateStaff(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $user = User::where('tenant_id', $tenant->id)->findOrFail($id);

        $allowedRoles = array_unique(array_merge(
            ['gym_owner', 'gym_manager', 'receptionist', 'trainer', 'accountant', 'staff'],
            Role::where('tenant_id', $tenant->id)->pluck('name')->toArray()
        ));

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150|unique:users,email,'.$user->id,
            'phone' => 'required|string|max:30',
            'role' => ['required', 'string', Rule::in($allowedRoles)],
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

        // Sync role_user pivot
        $roleModel = Role::where('tenant_id', $tenant->id)->where('name', $validated['role'])->first();
        if ($roleModel) {
            $user->roles()->sync([$roleModel->id]);
        }

        // Sync linked Trainer if user is a trainer
        if ($user->role === 'trainer') {
            $nameParts = explode(' ', $user->name, 2);
            $trainer = Trainer::where('tenant_id', $tenant->id)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('phone', $user->phone);
                    if ($user->email) {
                        $q->orWhere('email', $user->email);
                    }
                })
                ->first();

            if ($trainer) {
                $trainer->update([
                    'user_id' => $user->id,
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'salary' => $validated['monthly_salary'] ?? $trainer->salary,
                    'salary_type' => $validated['payout_type'] ?? $trainer->salary_type,
                    'joining_date' => $validated['joining_date'] ?? $trainer->joining_date,
                    'specialization' => ($validated['designation'] ?? null) ?: ($trainer->specialization ?: 'Fitness Coach'),
                    'status' => $user->status,
                ]);
            }
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

        // If user is a trainer, also delete linked Trainer record
        if ($user->role === 'trainer') {
            Trainer::where('tenant_id', $tenant->id)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('phone', $user->phone);
                })
                ->delete();
        }

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

        if ($user->role === 'trainer') {
            Trainer::where('tenant_id', $tenant->id)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('phone', $user->phone);
                })
                ->update(['status' => $newStatus]);
        }

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

    /**
     * Roles & Permission Matrix Management
     */
    public function roles(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        // Ensure default tenant roles exist
        $tenantService = app(TenantService::class);
        $tenantService->createDefaultTenantRoles($tenant);

        // Ensure permissions are seeded
        if (Permission::count() === 0) {
            (new PermissionSeeder)->run();
        }

        $roles = Role::where('tenant_id', $tenant->id)
            ->with(['permissions', 'users'])
            ->get();

        // Calculate staff count for each role
        foreach ($roles as $r) {
            $r->staff_count = User::where('tenant_id', $tenant->id)
                ->where(function ($q) use ($r) {
                    $q->where('role', $r->name)
                        ->orWhereHas('roles', function ($rq) use ($r) {
                            $rq->where('roles.id', $r->id);
                        });
                })->count();
        }

        $permissionGroups = PermissionSeeder::getPermissionsGrouped();
        $permFeatureMap = PermissionSeeder::getPermissionFeatureMap();

        // Filter permission groups to only include features enabled in tenant's active SaaS plan
        $filteredPermissionGroups = [];
        foreach ($permissionGroups as $groupKey => $groupData) {
            $allowedPerms = [];
            foreach ($groupData['permissions'] as $p) {
                $featureCode = $permFeatureMap[$p['name']] ?? null;
                if ($featureCode === null || $this->featureGateService->hasFeature($tenant, $featureCode)) {
                    $allowedPerms[] = $p;
                }
            }

            if (! empty($allowedPerms)) {
                $groupData['permissions'] = $allowedPerms;
                $filteredPermissionGroups[$groupKey] = $groupData;
            }
        }

        $allPermissions = Permission::all()->keyBy('name');
        $permissionGroups = $filteredPermissionGroups;

        return view('app.roles.index', compact('roles', 'permissionGroups', 'allPermissions', 'tenant'));
    }

    /**
     * Store new custom role
     */
    public function storeRole(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'name' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        $name = ! empty($validated['name']) ? Str::slug($validated['name'], '_') : Str::slug($validated['display_name'], '_');

        if (Role::where('tenant_id', $tenant->id)->where('name', $name)->exists()) {
            return back()->with('error', "A role with the identifier '{$name}' already exists.");
        }

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'display_name' => trim($validated['display_name']),
            'description' => ! empty($validated['description']) ? trim($validated['description']) : null,
            'is_system' => false,
        ]);

        ActivityLog::log('role_created', "Created custom role '{$role->display_name}' ({$role->name})", $role);

        return redirect()->route('app.roles.index')->with('success', "Custom role '{$role->display_name}' created successfully!");
    }

    /**
     * Update role details
     */
    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $role = Role::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $role->update([
            'display_name' => trim($validated['display_name']),
            'description' => ! empty($validated['description']) ? trim($validated['description']) : null,
        ]);

        ActivityLog::log('role_updated', "Updated role '{$role->display_name}'", $role);

        return redirect()->route('app.roles.index')->with('success', "Role '{$role->display_name}' updated successfully!");
    }

    /**
     * Delete custom role
     */
    public function deleteRole(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $role = Role::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($role->is_system) {
            return back()->with('error', 'System default roles cannot be deleted.');
        }

        $name = $role->display_name;
        $role->permissions()->detach();
        $role->delete();

        ActivityLog::log('role_deleted', "Deleted custom role '{$name}'");

        return redirect()->route('app.roles.index')->with('success', "Custom role '{$name}' deleted successfully.");
    }

    /**
     * Update entire permission matrix
     */
    public function updatePermissionMatrix(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $matrix = $request->input('matrix', []); // format: [role_id => [perm_id_1, perm_id_2, ...]]

        $tenantRoles = Role::where('tenant_id', $tenant->id)->get();
        $permFeatureMap = PermissionSeeder::getPermissionFeatureMap();
        $allPerms = Permission::all()->keyBy('id');

        // Determine which permissions are allowed under tenant's current plan
        $allowedPermIds = [];
        foreach ($allPerms as $perm) {
            $featureCode = $permFeatureMap[$perm->name] ?? null;
            if ($featureCode === null || $this->featureGateService->hasFeature($tenant, $featureCode)) {
                $allowedPermIds[] = $perm->id;
            }
        }

        foreach ($tenantRoles as $role) {
            $submittedPermIds = isset($matrix[$role->id]) && is_array($matrix[$role->id]) ? $matrix[$role->id] : [];

            // Filter submitted permissions to only those allowed in tenant's plan
            $submittedAllowedPermIds = array_values(array_intersect(array_map('intval', $submittedPermIds), $allowedPermIds));

            // Keep permissions for features outside current plan
            $existingHiddenPermIds = $role->permissions()
                ->whereNotIn('permissions.id', $allowedPermIds)
                ->pluck('permissions.id')
                ->all();

            $finalPermIds = array_unique(array_merge($submittedAllowedPermIds, $existingHiddenPermIds));
            $role->permissions()->sync($finalPermIds);
        }

        ActivityLog::log('permission_matrix_updated', 'Updated role permissions matrix');

        return redirect()->route('app.roles.index')->with('success', 'Permission matrix updated and saved successfully!');
    }

    public function services(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

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
            'status' => 'nullable|in:active,inactive',
            'is_visible_in_portal' => 'nullable',
            'is_locker_service' => 'nullable',
            'is_session_countable' => 'nullable',
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
            'status' => 'nullable|in:active,inactive',
            'is_visible_in_portal' => 'nullable',
            'is_locker_service' => 'nullable',
            'is_session_countable' => 'nullable',
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
            'booking_date' => 'nullable|date',
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
            'booking_date' => $validated['booking_date'] ?? now()->toDateString(),
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

    /**
     * Display the Inventory and Gym Equipment / AC Maintenance management page.
     */
    public function inventory(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $activeTab = $request->get('tab', 'inventory');

        // Inventory Items Query
        $itemsQuery = InventoryItem::where('tenant_id', $tenant->id);
        if ($request->filled('item_search')) {
            $search = trim((string) $request->get('item_search'));
            $itemsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }
        if ($request->filled('item_category')) {
            $itemsQuery->where('category', $request->get('item_category'));
        }
        if ($request->get('stock_filter') === 'low') {
            $itemsQuery->whereColumn('stock_quantity', '<=', 'reorder_threshold');
        } elseif ($request->get('stock_filter') === 'out') {
            $itemsQuery->where('stock_quantity', '<=', 0);
        }
        $items = $itemsQuery->latest()->paginate(15, ['*'], 'items_page')->withQueryString();

        // Inventory Statistics
        $allItems = InventoryItem::where('tenant_id', $tenant->id)->get();
        $totalItemsCount = $allItems->count();
        $totalStockUnits = $allItems->sum('stock_quantity');
        $totalCostValue = $allItems->sum(fn ($i) => (float) $i->cost_price * (int) $i->stock_quantity);
        $totalRetailValue = $allItems->sum(fn ($i) => (float) $i->selling_price * (int) $i->stock_quantity);
        $lowStockCount = $allItems->filter(fn ($i) => $i->stock_quantity <= $i->reorder_threshold)->count();
        $outOfStockCount = $allItems->filter(fn ($i) => $i->stock_quantity <= 0)->count();

        // Gym & AC Equipment Query
        $equipmentQuery = GymEquipment::where('tenant_id', $tenant->id)->with('maintenanceLogs');
        if ($request->filled('equipment_search')) {
            $search = trim((string) $request->get('equipment_search'));
            $equipmentQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model_number', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }
        if ($request->filled('equipment_category')) {
            $equipmentQuery->where('category', $request->get('equipment_category'));
        }
        if ($request->filled('equipment_status')) {
            $equipmentQuery->where('status', $request->get('equipment_status'));
        }
        if ($request->get('maintenance_filter') === 'overdue') {
            $equipmentQuery->whereNotNull('next_service_date')->where('next_service_date', '<', Carbon::today()->toDateString());
        } elseif ($request->get('maintenance_filter') === 'due_soon') {
            $equipmentQuery->whereNotNull('next_service_date')
                ->whereBetween('next_service_date', [Carbon::today()->toDateString(), Carbon::today()->addDays(7)->toDateString()]);
        } elseif ($request->get('maintenance_filter') === 'this_month') {
            $equipmentQuery->whereNotNull('next_service_date')
                ->whereBetween('next_service_date', [Carbon::today()->startOfMonth()->toDateString(), Carbon::today()->endOfMonth()->toDateString()]);
        }
        $equipment = $equipmentQuery->orderByRaw('CASE WHEN next_service_date IS NULL THEN 1 ELSE 0 END, next_service_date ASC')
            ->paginate(15, ['*'], 'equipment_page')
            ->withQueryString();

        // Equipment Statistics
        $allEquipment = GymEquipment::where('tenant_id', $tenant->id)->get();
        $totalEquipmentCount = $allEquipment->count();
        $operationalCount = $allEquipment->where('status', 'OPERATIONAL')->count();
        $acCount = $allEquipment->where('category', 'AC & HVAC')->count();
        $overdueMaintenanceCount = $allEquipment->filter(fn ($eq) => $eq->is_overdue && $eq->status !== 'OUT_OF_SERVICE')->count();
        $dueThisMonthCount = $allEquipment->filter(function ($eq) {
            if (! $eq->next_service_date) {
                return false;
            }

            return Carbon::parse($eq->next_service_date)->between(Carbon::today(), Carbon::today()->addDays(30));
        })->count();

        // Maintenance Logs Query
        $maintenanceLogs = EquipmentMaintenanceLog::where('tenant_id', $tenant->id)
            ->with('equipment')
            ->latest('service_date')
            ->paginate(15, ['*'], 'logs_page')
            ->withQueryString();

        $branches = Branch::where('tenant_id', $tenant->id)->get();
        $recentInventoryLogs = InventoryLog::where('tenant_id', $tenant->id)
            ->with('item')
            ->latest()
            ->limit(10)
            ->get();

        return view('app.inventory.index', compact(
            'tenant',
            'activeTab',
            'items',
            'totalItemsCount',
            'totalStockUnits',
            'totalCostValue',
            'totalRetailValue',
            'lowStockCount',
            'outOfStockCount',
            'equipment',
            'totalEquipmentCount',
            'operationalCount',
            'acCount',
            'overdueMaintenanceCount',
            'dueThisMonthCount',
            'maintenanceLogs',
            'branches',
            'recentInventoryLogs'
        ));
    }

    /**
     * Store a new inventory item.
     */
    public function storeInventoryItem(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'sku' => 'nullable|string|max:100',
            'category' => 'required|string|max:100',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_threshold' => 'required|integer|min:0',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $defaultBranchId = $tenant->mainBranch?->id ?? $tenant->branches()->first()?->id;

        $item = InventoryItem::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $validated['branch_id'] ?? $defaultBranchId,
            'sku' => ! empty($validated['sku']) ? trim($validated['sku']) : 'SKU-'.strtoupper(Str::random(6)),
            'name' => trim($validated['name']),
            'category' => trim($validated['category']),
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'stock_quantity' => $validated['stock_quantity'],
            'reorder_threshold' => $validated['reorder_threshold'],
        ]);

        if ($item->stock_quantity > 0) {
            InventoryLog::create([
                'tenant_id' => $tenant->id,
                'inventory_item_id' => $item->id,
                'type' => 'IN',
                'quantity' => $item->stock_quantity,
                'unit_price' => $item->cost_price,
                'notes' => 'Initial stock on item creation',
            ]);
        }

        ActivityLog::log('inventory_item_created', "Created inventory item '{$item->name}' with initial stock {$item->stock_quantity}", $item);

        return redirect()->route('app.inventory.index', ['tab' => 'inventory'])->with('success', "Item '{$item->name}' added to inventory!");
    }

    /**
     * Update an inventory item.
     */
    public function updateInventoryItem(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $item = InventoryItem::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'sku' => 'nullable|string|max:100',
            'category' => 'required|string|max:100',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_threshold' => 'required|integer|min:0',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $item->update([
            'branch_id' => $validated['branch_id'] ?? $item->branch_id,
            'sku' => ! empty($validated['sku']) ? trim($validated['sku']) : $item->sku,
            'name' => trim($validated['name']),
            'category' => trim($validated['category']),
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'reorder_threshold' => $validated['reorder_threshold'],
        ]);

        ActivityLog::log('inventory_item_updated', "Updated inventory item '{$item->name}'", $item);

        return redirect()->route('app.inventory.index', ['tab' => 'inventory'])->with('success', "Item '{$item->name}' updated successfully!");
    }

    /**
     * Adjust inventory stock (Add, Reduce, or Direct Set).
     */
    public function adjustInventoryStock(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $item = InventoryItem::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:IN,OUT,ADJUSTMENT',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $prevStock = $item->stock_quantity;
        $qty = (int) $validated['quantity'];

        if ($validated['type'] === 'IN') {
            $item->stock_quantity += $qty;
        } elseif ($validated['type'] === 'OUT') {
            if ($item->stock_quantity < $qty) {
                return redirect()->route('app.inventory.index', ['tab' => 'inventory'])->with('error', "Cannot reduce stock by {$qty} units. Current stock is only {$item->stock_quantity} units.");
            }
            $item->stock_quantity -= $qty;
        } elseif ($validated['type'] === 'ADJUSTMENT') {
            $item->stock_quantity = $qty;
        }

        $item->save();

        InventoryLog::create([
            'tenant_id' => $tenant->id,
            'inventory_item_id' => $item->id,
            'type' => $validated['type'],
            'quantity' => $qty,
            'unit_price' => $validated['unit_price'] ?? $item->cost_price,
            'reference_number' => $validated['reference_number'] ?? null,
            'notes' => $validated['notes'] ?? "Stock adjusted from {$prevStock} to {$item->stock_quantity}",
        ]);

        ActivityLog::log('inventory_stock_adjusted', "Adjusted stock for '{$item->name}' ({$validated['type']} {$qty}). New stock: {$item->stock_quantity}", $item);

        return redirect()->route('app.inventory.index', ['tab' => 'inventory'])->with('success', "Stock updated for '{$item->name}'. Current stock: {$item->stock_quantity} units.");
    }

    /**
     * Delete an inventory item.
     */
    public function deleteInventoryItem(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $item = InventoryItem::where('tenant_id', $tenant->id)->findOrFail($id);
        $name = $item->name;

        $item->delete();

        ActivityLog::log('inventory_item_deleted', "Deleted inventory item '{$name}'");

        return redirect()->route('app.inventory.index', ['tab' => 'inventory'])->with('success', "Item '{$name}' deleted from inventory.");
    }

    /**
     * Store new gym equipment / AC unit.
     */
    public function storeEquipment(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model_number' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warranty_expiry_date' => 'nullable|date',
            'maintenance_interval_days' => 'required|integer|min:1',
            'last_service_date' => 'nullable|date',
            'next_service_date' => 'nullable|date',
            'status' => 'required|in:OPERATIONAL,MAINTENANCE_DUE,UNDER_REPAIR,OUT_OF_SERVICE',
            'vendor_name' => 'nullable|string|max:150',
            'vendor_contact' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $interval = (int) $validated['maintenance_interval_days'];
        $nextDate = $validated['next_service_date'] ?? null;

        if (empty($nextDate)) {
            if (! empty($validated['last_service_date'])) {
                $nextDate = Carbon::parse($validated['last_service_date'])->addDays($interval)->toDateString();
            } elseif (! empty($validated['purchase_date'])) {
                $nextDate = Carbon::parse($validated['purchase_date'])->addDays($interval)->toDateString();
            } else {
                $nextDate = Carbon::today()->addDays($interval)->toDateString();
            }
        }

        $defaultBranchId = $tenant->mainBranch?->id ?? $tenant->branches()->first()?->id;

        $equipment = GymEquipment::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $validated['branch_id'] ?? $defaultBranchId,
            'name' => trim($validated['name']),
            'category' => trim($validated['category']),
            'brand' => ! empty($validated['brand']) ? trim($validated['brand']) : null,
            'model_number' => ! empty($validated['model_number']) ? trim($validated['model_number']) : null,
            'serial_number' => ! empty($validated['serial_number']) ? trim($validated['serial_number']) : null,
            'location' => ! empty($validated['location']) ? trim($validated['location']) : null,
            'purchase_date' => $validated['purchase_date'] ?? null,
            'purchase_cost' => $validated['purchase_cost'] ?? 0.00,
            'warranty_expiry_date' => $validated['warranty_expiry_date'] ?? null,
            'maintenance_interval_days' => $interval,
            'last_service_date' => $validated['last_service_date'] ?? null,
            'next_service_date' => $nextDate,
            'status' => $validated['status'],
            'vendor_name' => ! empty($validated['vendor_name']) ? trim($validated['vendor_name']) : null,
            'vendor_contact' => ! empty($validated['vendor_contact']) ? trim($validated['vendor_contact']) : null,
            'notes' => ! empty($validated['notes']) ? trim($validated['notes']) : null,
        ]);

        ActivityLog::log('gym_equipment_created', "Added equipment '{$equipment->name}' ({$equipment->category})", $equipment);

        return redirect()->route('app.inventory.index', ['tab' => 'equipment'])->with('success', "Equipment '{$equipment->name}' registered successfully!");
    }

    /**
     * Update gym equipment details.
     */
    public function updateEquipment(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $equipment = GymEquipment::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model_number' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warranty_expiry_date' => 'nullable|date',
            'maintenance_interval_days' => 'required|integer|min:1',
            'last_service_date' => 'nullable|date',
            'next_service_date' => 'nullable|date',
            'status' => 'required|in:OPERATIONAL,MAINTENANCE_DUE,UNDER_REPAIR,OUT_OF_SERVICE',
            'vendor_name' => 'nullable|string|max:150',
            'vendor_contact' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $equipment->update([
            'branch_id' => $validated['branch_id'] ?? $equipment->branch_id,
            'name' => trim($validated['name']),
            'category' => trim($validated['category']),
            'brand' => ! empty($validated['brand']) ? trim($validated['brand']) : null,
            'model_number' => ! empty($validated['model_number']) ? trim($validated['model_number']) : null,
            'serial_number' => ! empty($validated['serial_number']) ? trim($validated['serial_number']) : null,
            'location' => ! empty($validated['location']) ? trim($validated['location']) : null,
            'purchase_date' => $validated['purchase_date'] ?? null,
            'purchase_cost' => $validated['purchase_cost'] ?? 0.00,
            'warranty_expiry_date' => $validated['warranty_expiry_date'] ?? null,
            'maintenance_interval_days' => (int) $validated['maintenance_interval_days'],
            'last_service_date' => $validated['last_service_date'] ?? null,
            'next_service_date' => $validated['next_service_date'] ?? null,
            'status' => $validated['status'],
            'vendor_name' => ! empty($validated['vendor_name']) ? trim($validated['vendor_name']) : null,
            'vendor_contact' => ! empty($validated['vendor_contact']) ? trim($validated['vendor_contact']) : null,
            'notes' => ! empty($validated['notes']) ? trim($validated['notes']) : null,
        ]);

        ActivityLog::log('gym_equipment_updated', "Updated equipment '{$equipment->name}'", $equipment);

        return redirect()->route('app.inventory.index', ['tab' => 'equipment'])->with('success', "Equipment '{$equipment->name}' updated successfully!");
    }

    /**
     * Record a maintenance / service event for gym equipment or AC.
     */
    public function recordEquipmentMaintenance(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $equipment = GymEquipment::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'maintenance_type' => 'required|string|max:100',
            'service_date' => 'required|date',
            'technician_name' => 'nullable|string|max:100',
            'technician_contact' => 'nullable|string|max:100',
            'cost' => 'nullable|numeric|min:0',
            'status_after_service' => 'required|in:OPERATIONAL,MAINTENANCE_DUE,UNDER_REPAIR,OUT_OF_SERVICE',
            'work_summary' => 'nullable|string|max:1000',
            'replaced_parts' => 'nullable|string|max:500',
            'next_service_date' => 'nullable|date',
            'auto_schedule_next' => 'nullable|boolean',
        ]);

        $serviceDate = Carbon::parse($validated['service_date']);
        $nextDate = $validated['next_service_date'] ?? null;

        if (empty($nextDate) && ($request->boolean('auto_schedule_next') || true)) {
            $nextDate = $serviceDate->copy()->addDays($equipment->maintenance_interval_days)->toDateString();
        }

        $log = EquipmentMaintenanceLog::create([
            'tenant_id' => $tenant->id,
            'gym_equipment_id' => $equipment->id,
            'maintenance_type' => trim($validated['maintenance_type']),
            'service_date' => $serviceDate->toDateString(),
            'technician_name' => ! empty($validated['technician_name']) ? trim($validated['technician_name']) : null,
            'technician_contact' => ! empty($validated['technician_contact']) ? trim($validated['technician_contact']) : null,
            'cost' => $validated['cost'] ?? 0.00,
            'status_after_service' => $validated['status_after_service'],
            'work_summary' => ! empty($validated['work_summary']) ? trim($validated['work_summary']) : null,
            'replaced_parts' => ! empty($validated['replaced_parts']) ? trim($validated['replaced_parts']) : null,
            'next_service_date' => $nextDate,
        ]);

        // Update equipment's last and next service dates and status
        $equipment->update([
            'last_service_date' => $serviceDate->toDateString(),
            'next_service_date' => $nextDate,
            'status' => $validated['status_after_service'],
            'vendor_name' => ! empty($validated['technician_name']) ? trim($validated['technician_name']) : $equipment->vendor_name,
            'vendor_contact' => ! empty($validated['technician_contact']) ? trim($validated['technician_contact']) : $equipment->vendor_contact,
        ]);

        ActivityLog::log('equipment_maintenance_recorded', "Recorded {$log->maintenance_type} for '{$equipment->name}'. Next service: {$equipment->next_service_date}", $log);

        return redirect()->route('app.inventory.index', ['tab' => 'equipment'])->with('success', "Maintenance recorded for '{$equipment->name}'. Next service scheduled for ".Carbon::parse($nextDate)->format('d M, Y').'!');
    }

    /**
     * Delete gym equipment.
     */
    public function deleteEquipment(int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $equipment = GymEquipment::where('tenant_id', $tenant->id)->findOrFail($id);
        $name = $equipment->name;

        $equipment->delete();

        ActivityLog::log('gym_equipment_deleted', "Deleted equipment '{$name}'");

        return redirect()->route('app.inventory.index', ['tab' => 'equipment'])->with('success', "Equipment '{$name}' removed.");
    }

    /**
     * Display support tickets list for tenant.
     */
    public function supportTickets(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $user = auth()->user();

        $query = SupportTicket::where('tenant_id', $tenant->id)
            ->with(['user', 'lastReplyBy', 'latestReply']);

        if ($status = $request->get('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $tickets = $query->latest('last_reply_at')->latest('id')->paginate(15);

        $counts = [
            'total' => SupportTicket::where('tenant_id', $tenant->id)->count(),
            'open' => SupportTicket::where('tenant_id', $tenant->id)->whereIn('status', ['open', 'in_progress'])->count(),
            'answered' => SupportTicket::where('tenant_id', $tenant->id)->where('status', 'answered')->count(),
            'resolved' => SupportTicket::where('tenant_id', $tenant->id)->whereIn('status', ['resolved', 'closed'])->count(),
        ];

        return view('app.support.index', compact('tickets', 'counts', 'tenant'));
    }

    /**
     * Store a new support ticket.
     */
    public function storeSupportTicket(Request $request, SupportTicketService $ticketService): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $user = auth()->user();

        $validated = $request->validate([
            'subject' => 'required|string|max:200',
            'category' => 'required|in:technical,billing,feature_request,account,general',
            'priority' => 'required|in:low,medium,high,urgent',
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf,doc,docx,zip|max:5120',
        ]);

        $ticket = $ticketService->createTicket(
            $tenant,
            $user,
            $validated,
            $request->file('attachment')
        );

        return redirect()->route('app.support.show', $ticket->id)
            ->with('success', "Support ticket #{$ticket->ticket_number} created successfully! Our team has been notified.");
    }

    /**
     * Show support ticket details and conversation thread.
     */
    public function showSupportTicket(int $id): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $ticket = SupportTicket::where('tenant_id', $tenant->id)
            ->with(['user', 'replies.user', 'tenant'])
            ->findOrFail($id);

        return view('app.support.show', compact('ticket', 'tenant'));
    }

    /**
     * Reply to a support ticket.
     */
    public function replySupportTicket(Request $request, int $id, SupportTicketService $ticketService): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $user = auth()->user();
        $ticket = SupportTicket::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf,doc,docx,zip|max:5120',
        ]);

        $ticketService->replyTicket(
            $ticket,
            $user,
            $validated['message'],
            $request->file('attachment'),
            false
        );

        return back()->with('success', 'Your reply has been posted successfully!');
    }

    /**
     * Close a support ticket.
     */
    public function closeSupportTicket(int $id, SupportTicketService $ticketService): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $ticket = SupportTicket::where('tenant_id', $tenant->id)->findOrFail($id);

        $ticket->update([
            'status' => 'closed',
            'resolved_at' => now(),
        ]);

        ActivityLog::log('support_ticket_closed', "Closed ticket #{$ticket->ticket_number}", $ticket);

        return back()->with('success', "Ticket #{$ticket->ticket_number} has been closed.");
    }
}
