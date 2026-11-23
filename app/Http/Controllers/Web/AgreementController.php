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
            $query->where('status', MemberAgreement::STATUS_COMPLETED);
        } elseif ($statusFilter === 'pending') {
            $query->whereIn('status', [MemberAgreement::STATUS_PENDING, MemberAgreement::STATUS_LINK_SENT]);
        } elseif ($statusFilter === 'medical_review') {
            $query->where('status', MemberAgreement::STATUS_MEDICAL_REVIEW);
        } elseif ($statusFilter === 'expired') {
            $query->where('status', MemberAgreement::STATUS_EXPIRED);
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

        // Also fetch members who haven't been sent an agreement yet for the quick-send selector
        $pendingMemberIds = MemberAgreement::where('tenant_id', $tenant->id)->pluck('member_id')->toArray();
        $unsentMembers = Member::where('tenant_id', $tenant->id)
            ->whereNotIn('id', $pendingMemberIds)
            ->orderBy('first_name')
            ->take(100)
            ->get();

        $activeTemplate = $this->agreementService->getOrCreateActiveTemplate($tenant);

        return view('app.agreements.index', compact(
            'agreements',
            'stats',
            'statusFilter',
            'unsentMembers',
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

        return view('app.agreements.settings', compact('activeTemplate', 'templateHistory', 'tenant'));
    }

    /**
     * Publish a new immutable agreement version with updated terms or terrace rules.
     */
    public function saveSettings(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'open_terrace_enabled' => 'nullable|boolean',
            'terms_sections' => 'nullable|array',
            'terms_sections.*.title' => 'required|string',
            'terms_sections.*.summary' => 'nullable|string',
            'terms_sections.*.clauses' => 'required|array',
        ]);

        $terraceEnabled = $request->boolean('open_terrace_enabled');
        $sections = $validated['terms_sections'] ?? TenantAgreementTemplate::getDefaultTermsSections($terraceEnabled);

        // Ensure terrace clauses are included if terrace is enabled
        if ($terraceEnabled) {
            $hasTerrace = collect($sections)->contains('key', 'open_terrace_rules');
            if (! $hasTerrace) {
                $sections[] = [
                    'key' => 'open_terrace_rules',
                    'title' => 'Open Terrace Access & Safety Rules',
                    'summary' => 'Strict authorization requirements, weather protocols, safety restrictions, and management discretion for elevated terrace workout zones.',
                    'clauses' => [
                        '27. Access to the Open Terrace Workout Area is strictly restricted and permitted only with prior authorization from Gym Management. Members are not entitled to automatic terrace access as part of their membership.',
                        '28. Unauthorized entry, use of terrace facilities without permission, or entry during restricted hours is strictly prohibited and may result in immediate suspension or termination of membership.',
                        '29. Members must strictly follow all safety instructions. Climbing or leaning on railings, entering restricted areas, performing dangerous activities, or misusing equipment is strictly prohibited.',
                        '30. Gym Management reserves the right to restrict, suspend, or revoke terrace access at any time due to safety concerns, weather conditions, maintenance, misconduct, or operational requirements. Members must immediately comply with such instructions.',
                    ],
                ];
            }
        }

        $newTemplate = $this->agreementService->publishNewTemplateVersion($tenant, [
            'title' => $validated['title'],
            'open_terrace_enabled' => $terraceEnabled,
            'terms_sections' => $sections,
        ], auth()->user());

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
