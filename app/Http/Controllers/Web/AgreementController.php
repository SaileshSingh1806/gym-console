<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberAgreement;
use App\Models\TenantAgreementTemplate;
use App\Services\MemberAgreementService;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AgreementController extends Controller
{
    public function __construct(
        protected MemberAgreementService $agreementService
    ) {}

    /**
     * Agreements & Health Declarations Management Dashboard.
     */
    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $stats = $this->agreementService->getDashboardStats($tenant);

        $query = MemberAgreement::where('tenant_id', $tenant->id)
            ->with(['member.activeMembership.plan', 'member.branch', 'template', 'reviewedBy']);

        // Filter by Status Tab
        $statusFilter = $request->input('status', 'all');
        if ($statusFilter === 'completed') {
            $query->where(function ($q) {
                $q->whereIn('status', [MemberAgreement::STATUS_COMPLETED, 'completed', 'COMPLETED'])
                    ->orWhereNotNull('signed_at');
            });
        } elseif ($statusFilter === 'pending') {
            $query->whereIn('status', [MemberAgreement::STATUS_PENDING, MemberAgreement::STATUS_LINK_SENT, 'pending', 'link_sent', 'PENDING', 'LINK_SENT'])
                ->whereNull('signed_at');
        } elseif ($statusFilter === 'medical_review') {
            $query->whereIn('status', [MemberAgreement::STATUS_MEDICAL_REVIEW, 'medical_review', 'MEDICAL_REVIEW_REQUIRED']);
        } elseif ($statusFilter === 'expired') {
            $query->whereIn('status', [MemberAgreement::STATUS_EXPIRED, 'expired', 'EXPIRED']);
        }

        // Search Filter (Member Name, Phone, Member ID)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('member_code', 'like', "%{$search}%");
            });
        }

        $agreements = $query->latest('id')->paginate(15)->withQueryString();

        // Also fetch members for the quick-send selector with search & plans
        $unsentMembers = Member::where('tenant_id', $tenant->id)
            ->with(['activeMembership.plan'])
            ->orderBy('first_name')
            ->get();

        $membersJson = $unsentMembers->map(function ($m) {
            return [
                'id' => $m->id,
                'name' => $m->full_name,
                'code' => $m->member_code ?? ('#' . $m->id),
                'phone' => $m->phone ?? '',
                'plan' => $m->activeMembership?->plan?->name ?? 'Standard Active Plan',
                'photo' => $m->profile_photo_url ?? null,
                'initials' => strtoupper(substr($m->first_name ?? 'M', 0, 1)),
            ];
        });

        $activeTemplate = $this->agreementService->getOrCreateActiveTemplate($tenant);

        return view('app.agreements.index', compact(
            'agreements',
            'stats',
            'statusFilter',
            'unsentMembers',
            'membersJson',
            'activeTemplate',
            'tenant'
        ));
    }

    /**
     * Agreement Settings & Template Versioning.
     */
    public function settings(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $activeTemplate = $this->agreementService->getOrCreateActiveTemplate($tenant);

        $templateHistory = TenantAgreementTemplate::where('tenant_id', $tenant->id)
            ->with('publisher')
            ->latest('version')
            ->get();

        $builderDraft = $request->session()->get('agreement_builder_draft.'.$tenant->id.'.'.auth()->id());

        return view('app.agreements.settings', compact('activeTemplate', 'templateHistory', 'tenant', 'builderDraft'));
    }

    /**
     * Publish a new immutable agreement version with updated terms or terrace rules.
     */
    public function saveSettings(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        // The builder submits structured sections; the legacy publish endpoint also
        // accepts requests without sections to retain its default-template behavior.
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'open_terrace_enabled' => 'nullable|boolean',
            'terms_sections' => 'nullable|array|min:1|max:30',
            'terms_sections.*' => 'array:title,summary,clauses,key',
            'terms_sections.*.title' => 'required|string|max:150',
            'terms_sections.*.summary' => 'nullable|string|max:1000',
            'terms_sections.*.key' => 'nullable|string|max:100',
            'terms_sections.*.clauses' => 'required|array|min:1|max:30',
            'terms_sections.*.clauses.*' => 'required|string|max:3000',
            'builder_payload' => 'nullable|boolean',
            'intent' => 'nullable|in:draft,publish',
        ]);

        if ($request->boolean('builder_payload') && empty($validated['terms_sections'])) {
            return back()->withErrors(['terms_sections' => 'Add at least one section with a clause.'])->withInput();
        }

        $terraceEnabled = $request->boolean('open_terrace_enabled');
        $sections = $validated['terms_sections'] ?? TenantAgreementTemplate::getDefaultTermsSections(false);
        // Facility-specific rules are maintained by the system, not editable clauses.
        $sections = collect($sections)->reject(fn ($section) => ($section['key'] ?? null) === 'open_terrace_rules')
            ->map(fn ($section) => [
                'title' => trim($section['title']),
                'summary' => trim($section['summary'] ?? ''),
                'clauses' => array_values(array_map('trim', $section['clauses'])),
            ])->values()->all();

        if (empty($sections)) {
            return back()->withErrors(['terms_sections' => 'Add at least one custom agreement section.'])->withInput();
        }

        if (($validated['intent'] ?? 'publish') === 'draft') {
            $request->session()->put('agreement_builder_draft.'.$tenant->id.'.'.auth()->id(), [
                'title' => $validated['title'],
                'open_terrace_enabled' => $terraceEnabled,
                'terms_sections' => $sections,
            ]);

            return redirect()->route('app.agreements.settings')->with('success', 'Agreement draft saved for this session. The published agreement is unchanged.');
        }

        if ($terraceEnabled) {
            $sections[] = collect(TenantAgreementTemplate::getDefaultTermsSections(true))->firstWhere('key', 'open_terrace_rules');
        }

        $newTemplate = $this->agreementService->publishNewTemplateVersion($tenant, [
            'title' => $validated['title'],
            'open_terrace_enabled' => $terraceEnabled,
            'terms_sections' => $sections,
            'health_questions' => $this->agreementService->getOrCreateActiveTemplate($tenant)->health_questions ?? TenantAgreementTemplate::getDefaultHealthQuestions(),
        ], auth()->user());

        $request->session()->forget('agreement_builder_draft.'.$tenant->id.'.'.auth()->id());

        return redirect()->route('app.agreements.settings')->with('success', "Agreement Version {$newTemplate->version} published successfully! Existing signed agreements remain safely locked to their original version.");
    }

    /**
     * Generate or fetch agreement link for a member.
     */
    public function generateForMember(Request $request, int $memberId): JsonResponse|RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $member = Member::where('tenant_id', $tenant->id)->findOrFail($memberId);

        $agreement = $this->agreementService->getOrCreateAgreementForMember($member, true, 'manual_copy');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'agreement_id' => $agreement->id,
                'share_url' => $agreement->share_url,
                'whatsapp_url' => $agreement->whatsapp_share_url,
                'sms_url' => $agreement->sms_share_url,
                'status' => $agreement->status,
            ]);
        }

        return back()->with('success', "Agreement link generated for {$member->full_name}!")
            ->with('share_agreement_url', $agreement->share_url)
            ->with('whatsapp_agreement_url', $agreement->whatsapp_share_url);
    }

    /**
     * Send agreement link via WhatsApp or SMS.
     */
    public function sendLink(Request $request, int $agreementId): JsonResponse|RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $agreement = MemberAgreement::where('tenant_id', $tenant->id)->findOrFail($agreementId);

        $channel = $request->input('channel', 'whatsapp');
        $this->agreementService->recordLinkSent($agreement, $channel);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Agreement link dispatched via {$channel}!",
                'whatsapp_url' => $agreement->whatsapp_share_url,
                'sms_url' => $agreement->sms_share_url,
                'share_url' => $agreement->share_url,
            ]);
        }

        return back()->with('success', "Agreement link sent to {$agreement->member?->full_name} via {$channel}!")
            ->with('whatsapp_agreement_url', $agreement->whatsapp_share_url);
    }

    /**
     * Bulk generate agreements for selected members.
     */
    public function bulkGenerate(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:members,id',
            'channel' => 'nullable|string|in:whatsapp,sms,manual_copy',
        ]);

        $channel = $request->input('channel', 'whatsapp');
        $memberIds = $request->input('member_ids', []);

        $result = $this->agreementService->bulkGenerateAgreements($tenant, $memberIds, true, $channel);

        return back()->with('success', "Processed {$result['total_processed']} members: {$result['created']} new agreement links generated, {$result['already_active']} already active.");
    }

    /**
     * Staff/Gym Owner approves medical review for a member.
     */
    public function medicalReview(Request $request, int $id): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $agreement = MemberAgreement::where('tenant_id', $tenant->id)->findOrFail($id);

        $notes = $request->input('notes');
        $this->agreementService->approveMedicalReview($agreement, auth()->user(), $notes);

        return back()->with('success', "Medical review approved for {$agreement->member?->full_name}!");
    }

    /**
     * Download or view agreement document.
     */
    public function downloadPdf(int $id): Response
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $agreement = MemberAgreement::where('tenant_id', $tenant->id)
            ->with(['member.activeMembership.plan', 'member.branch', 'template'])
            ->findOrFail($id);

        $member = $agreement->member;
        $template = $agreement->template ?? $this->agreementService->getOrCreateActiveTemplate($tenant);
        $activeMembership = $member->activeMembership ?? $member->memberships()->latest('id')->first();
        $branch = $member->branch ?? $tenant->mainBranch;

        $html = view('app.agreements.document', compact(
            'agreement',
            'tenant',
            'member',
            'template',
            'activeMembership',
            'branch'
        ))->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => "inline; filename=\"Agreement_{$member->member_code}.html\"",
        ]);
    }

    /**
     * View audit history modal data.
     */
    public function viewAudit(int $id): JsonResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $agreement = MemberAgreement::where('tenant_id', $tenant->id)->findOrFail($id);

        return response()->json([
            'success' => true,
            'reference' => $agreement->signature_reference,
            'hash' => $agreement->signature_hash,
            'signer_ip' => $agreement->signer_ip,
            'signer_user_agent' => $agreement->signer_user_agent,
            'signed_at' => $agreement->signed_at?->format('d M Y, h:i:s A T'),
            'audit_trail' => $agreement->audit_trail ?? [],
        ]);
    }
}
