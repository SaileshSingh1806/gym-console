<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\WhatsappAutomation;
use App\Models\WhatsappAutomationLog;
use App\Models\WhatsappCampaign;
use App\Models\WhatsappCampaignRecipient;
use App\Models\WhatsappContact;
use App\Models\WhatsappContactConsent;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use App\Services\TenantContext;
use App\Services\WhatsApp\WhatsAppAutomationService;
use App\Services\WhatsApp\WhatsAppCampaignService;
use App\Services\WhatsApp\WhatsAppCloudApiService;
use App\Services\WhatsApp\WhatsAppContactService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WhatsAppController extends Controller
{
    protected function getTenant(): Tenant
    {
        return TenantContext::getTenant() ?? auth()->user()->tenant;
    }

    /**
     * MODULE 1: Dashboard
     */
    public function dashboard(): View
    {
        $tenant = $this->getTenant();
        $api = new WhatsAppCloudApiService($tenant);

        // Core KPI Metrics
        $totalContacts = WhatsappContact::where('tenant_id', $tenant->id)->count();
        $optedInContacts = WhatsappContact::where('tenant_id', $tenant->id)->where('consent_status', 'opted_in')->count();
        $optedOutContacts = WhatsappContact::where('tenant_id', $tenant->id)->where('consent_status', 'opted_out')->count();
        $activeCampaigns = WhatsappCampaign::where('tenant_id', $tenant->id)->whereIn('status', ['running', 'scheduled'])->count();

        // Message Metrics
        $messagesSent = WhatsappMessage::where('tenant_id', $tenant->id)->where('direction', 'outbound')->count();
        $deliveredMessages = WhatsappMessage::where('tenant_id', $tenant->id)->whereIn('status', ['delivered', 'read'])->count();
        $readMessages = WhatsappMessage::where('tenant_id', $tenant->id)->where('status', 'read')->count();
        $failedMessages = WhatsappMessage::where('tenant_id', $tenant->id)->where('status', 'failed')->count();
        $customerReplies = WhatsappMessage::where('tenant_id', $tenant->id)->where('direction', 'inbound')->count();

        // Calculate delivery rate & read rate
        $deliveryRate = $messagesSent > 0 ? round(($deliveredMessages / $messagesSent) * 100, 1) : 0;
        $readRate = $deliveredMessages > 0 ? round(($readMessages / $deliveredMessages) * 100, 1) : 0;

        // Recent Campaigns
        $recentCampaigns = WhatsappCampaign::with('template')
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->limit(5)
            ->get();

        // Recent Incoming Messages
        $recentReplies = WhatsappMessage::with('contact')
            ->where('tenant_id', $tenant->id)
            ->where('direction', 'inbound')
            ->latest('id')
            ->limit(5)
            ->get();

        // Check API status
        $isConfigured = $api->isConfigured();

        return view('app.whatsapp.dashboard', compact(
            'totalContacts',
            'optedInContacts',
            'optedOutContacts',
            'activeCampaigns',
            'messagesSent',
            'deliveredMessages',
            'readMessages',
            'failedMessages',
            'customerReplies',
            'deliveryRate',
            'readRate',
            'recentCampaigns',
            'recentReplies',
            'isConfigured'
        ));
    }

    /**
     * MODULE 2: Contact Management
     */
    public function contacts(Request $request): View
    {
        $tenant = $this->getTenant();
        $query = WhatsappContact::where('tenant_id', $tenant->id)->latest('id');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('city', 'like', "%{$s}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('consent_status')) {
            $query->where('consent_status', $request->consent_status);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $contacts = $query->paginate(20)->withQueryString();

        $cities = WhatsappContact::where('tenant_id', $tenant->id)
            ->whereNotNull('city')
            ->distinct()
            ->pluck('city');

        $stats = [
            'total' => WhatsappContact::where('tenant_id', $tenant->id)->count(),
            'opted_in' => WhatsappContact::where('tenant_id', $tenant->id)->where('consent_status', 'opted_in')->count(),
            'unknown' => WhatsappContact::where('tenant_id', $tenant->id)->where('consent_status', 'unknown')->count(),
            'opted_out' => WhatsappContact::where('tenant_id', $tenant->id)->where('consent_status', 'opted_out')->count(),
            'members' => WhatsappContact::where('tenant_id', $tenant->id)->where('type', 'member')->count(),
            'leads' => WhatsappContact::where('tenant_id', $tenant->id)->where('type', 'lead')->count(),
        ];

        return view('app.whatsapp.contacts.index', compact('contacts', 'cities', 'stats'));
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $tenant = $this->getTenant();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:25',
            'city' => 'nullable|string|max:100',
            'type' => 'required|in:member,lead,external',
            'tags' => 'nullable|string',
            'consent_status' => 'required|in:opted_in,unknown,opted_out',
            'consent_source' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $normalizedPhone = WhatsAppContactService::normalizeIndianPhoneNumber($validated['phone']);
        if (! $normalizedPhone) {
            return back()->with('error', 'Invalid Indian phone number. Please enter a valid 10-digit mobile number.')->withInput();
        }

        // Prevent duplicates in tenant
        $exists = WhatsappContact::where('tenant_id', $tenant->id)->where('phone', $normalizedPhone)->exists();
        if ($exists) {
            return back()->with('error', "Contact with phone number {$normalizedPhone} already exists.")->withInput();
        }

        $tags = [];
        if (! empty($validated['tags'])) {
            $tags = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        }

        $contact = WhatsappContact::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'phone' => $normalizedPhone,
            'city' => $validated['city'] ?? null,
            'type' => $validated['type'],
            'source' => 'manual',
            'tags' => $tags,
            'consent_status' => $validated['consent_status'],
            'consent_updated_at' => $validated['consent_status'] !== 'unknown' ? now() : null,
            'status' => 'active',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Audit consent
        if ($validated['consent_status'] !== 'unknown') {
            WhatsAppContactService::updateConsent(
                contact: $contact,
                newStatus: $validated['consent_status'],
                source: $validated['consent_source'] ?: 'Manual Admin Entry',
                wordingVersion: 'Direct verbal or written opt-in provided to gym admin',
                notes: 'Recorded during manual contact creation',
                userId: auth()->id()
            );
        }

        return back()->with('success', "Contact '{$contact->name}' ({$contact->phone}) added successfully.");
    }

    public function updateContact(Request $request, int $id): RedirectResponse
    {
        $tenant = $this->getTenant();
        $contact = WhatsappContact::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'type' => 'required|in:member,lead,external',
            'tags' => 'nullable|string',
            'consent_status' => 'required|in:opted_in,unknown,opted_out',
            'notes' => 'nullable|string',
        ]);

        $tags = [];
        if (! empty($validated['tags'])) {
            $tags = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        }

        $oldConsent = $contact->consent_status;

        $contact->update([
            'name' => $validated['name'],
            'city' => $validated['city'] ?? null,
            'type' => $validated['type'],
            'tags' => $tags,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($oldConsent !== $validated['consent_status']) {
            WhatsAppContactService::updateConsent(
                contact: $contact,
                newStatus: $validated['consent_status'],
                source: 'Admin Contact Profile Edit',
                wordingVersion: 'Staff updated consent status directly',
                notes: 'Updated from edit contact modal',
                userId: auth()->id()
            );
        }

        return back()->with('success', "Contact '{$contact->name}' updated successfully.");
    }

    public function deleteContact(int $id): RedirectResponse
    {
        $tenant = $this->getTenant();
        $contact = WhatsappContact::where('tenant_id', $tenant->id)->findOrFail($id);
        $name = $contact->name;
        $contact->delete();

        return back()->with('success', "Contact '{$name}' deleted successfully.");
    }

    public function updateConsent(Request $request, int $id): RedirectResponse
    {
        $tenant = $this->getTenant();
        $contact = WhatsappContact::where('tenant_id', $tenant->id)->findOrFail($id);

        $request->validate([
            'consent_status' => 'required|in:opted_in,unknown,opted_out',
            'notes' => 'nullable|string|max:255',
        ]);

        WhatsAppContactService::updateConsent(
            contact: $contact,
            newStatus: $request->consent_status,
            source: 'Admin Quick Toggle',
            wordingVersion: 'Consent modified by admin from contact list',
            notes: $request->notes ?: 'Direct status update',
            userId: auth()->id()
        );

        return back()->with('success', "Consent status for '{$contact->name}' updated to {$request->consent_status}.");
    }

    public function syncMembers(): RedirectResponse
    {
        $tenant = $this->getTenant();
        $result = WhatsAppContactService::syncGymMembers($tenant);

        return back()->with('success', "Synchronized {$result['synced']} gym members into WhatsApp contacts. ({$result['skipped']} skipped due to missing/invalid phone numbers).");
    }

    public function syncLeads(): RedirectResponse
    {
        $tenant = $this->getTenant();
        $result = WhatsAppContactService::syncCrmLeads($tenant);

        return back()->with('success', "Synchronized {$result['synced']} CRM leads into WhatsApp contacts. ({$result['skipped']} skipped).");
    }

    public function exportContacts(Request $request): StreamedResponse
    {
        $tenant = $this->getTenant();
        $contacts = WhatsappContact::where('tenant_id', $tenant->id)->get();

        $fileName = 'whatsapp_contacts_'.now()->format('Y_m_d_His').'.csv';

        return response()->streamDownload(function () use ($contacts) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Phone', 'City', 'Type', 'Source', 'Consent Status', 'Tags', 'Created At']);

            foreach ($contacts as $c) {
                fputcsv($handle, [
                    $c->id,
                    $c->name,
                    $c->phone,
                    $c->city,
                    $c->type,
                    $c->source,
                    $c->consent_status,
                    is_array($c->tags) ? implode(', ', $c->tags) : '',
                    $c->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    /**
     * MODULE 2: Import Contacts View & Flow
     */
    public function importContacts(): View
    {
        return view('app.whatsapp.contacts.import');
    }

    public function processImportPreview(Request $request): View|RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $tenant = $this->getTenant();
        $file = $request->file('csv_file');

        try {
            $preview = WhatsAppContactService::previewCsvImport($file->getRealPath(), $tenant);

            // Store preview in session temporarily
            session(['whatsapp_import_preview' => $preview]);

            return view('app.whatsapp.contacts.import', compact('preview'));
        } catch (Exception $e) {
            return back()->with('error', 'Error reading CSV file: '.$e->getMessage());
        }
    }

    public function confirmImport(Request $request): RedirectResponse
    {
        $preview = session('whatsapp_import_preview');
        if (empty($preview) || empty($preview['valid_rows'])) {
            return redirect()->route('app.whatsapp.contacts.import')->with('error', 'No valid contact data to import.');
        }

        $tenant = $this->getTenant();
        $wordingVersion = $request->input('consent_wording', 'CSV Upload - Admin Verified');

        $count = WhatsAppContactService::commitImport(
            contacts: $preview['valid_rows'],
            tenant: $tenant,
            userId: auth()->id(),
            wordingVersion: $wordingVersion
        );

        session()->forget('whatsapp_import_preview');

        return redirect()->route('app.whatsapp.contacts.index')->with('success', "Successfully imported {$count} contacts into WhatsApp Management.");
    }

    /**
     * MODULE 4: Message Templates
     */
    public function templates(Request $request): View
    {
        $tenant = $this->getTenant();
        $query = WhatsappTemplate::where('tenant_id', $tenant->id)->latest('id');

        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        if ($request->filled('category')) {
            $query->where('category', strtoupper($request->category));
        }

        $templates = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => WhatsappTemplate::where('tenant_id', $tenant->id)->count(),
            'approved' => WhatsappTemplate::where('tenant_id', $tenant->id)->where('status', 'APPROVED')->count(),
            'pending' => WhatsappTemplate::where('tenant_id', $tenant->id)->where('status', 'PENDING')->count(),
            'rejected' => WhatsappTemplate::where('tenant_id', $tenant->id)->where('status', 'REJECTED')->count(),
        ];

        return view('app.whatsapp.templates.index', compact('templates', 'stats'));
    }

    public function syncTemplates(): RedirectResponse
    {
        $tenant = $this->getTenant();
        $api = new WhatsAppCloudApiService($tenant);

        if (! $api->isConfigured()) {
            return back()->with('error', 'Please configure WhatsApp WABA ID and Access Token in Settings before fetching templates.');
        }

        $result = $api->fetchTemplates();

        if (! ($result['success'] ?? false)) {
            return back()->with('error', 'Failed to fetch templates from Meta: '.($result['message'] ?? 'Unknown error'));
        }

        $synced = 0;
        foreach ($result['templates'] as $metaTpl) {
            $name = $metaTpl['name'] ?? '';
            if (empty($name)) {
                continue;
            }

            // Extract body text, header, footer, buttons
            $bodyText = '';
            $headerType = 'NONE';
            $headerContent = null;
            $footerText = null;
            $buttons = [];

            foreach ($metaTpl['components'] ?? [] as $comp) {
                $type = strtoupper($comp['type'] ?? '');
                if ($type === 'BODY') {
                    $bodyText = $comp['text'] ?? '';
                } elseif ($type === 'HEADER') {
                    $headerType = strtoupper($comp['format'] ?? 'TEXT');
                    $headerContent = $comp['text'] ?? null;
                } elseif ($type === 'FOOTER') {
                    $footerText = $comp['text'] ?? null;
                } elseif ($type === 'BUTTONS') {
                    $buttons = $comp['buttons'] ?? [];
                }
            }

            WhatsappTemplate::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => $name,
                    'language' => $metaTpl['language'] ?? 'en',
                ],
                [
                    'waba_template_id' => $metaTpl['id'] ?? null,
                    'category' => strtoupper($metaTpl['category'] ?? 'MARKETING'),
                    'status' => strtoupper($metaTpl['status'] ?? 'PENDING'),
                    'header_type' => in_array($headerType, ['NONE', 'TEXT', 'IMAGE', 'VIDEO', 'DOCUMENT']) ? $headerType : 'TEXT',
                    'header_content' => $headerContent,
                    'body_text' => $bodyText,
                    'footer_text' => $footerText,
                    'buttons' => $buttons,
                    'meta_raw_data' => $metaTpl,
                    'rejection_reason' => $metaTpl['rejected_reason'] ?? null,
                ]
            );

            $synced++;
        }

        return back()->with('success', "Synchronized {$synced} templates from Meta WhatsApp Business Account.");
    }

    public function createTemplate(Request $request): RedirectResponse
    {
        $tenant = $this->getTenant();

        $validated = $request->validate([
            'name' => 'required|string|regex:/^[a-z0-9_]+$/|max:512',
            'category' => 'required|in:MARKETING,UTILITY',
            'language' => 'required|string|max:10',
            'header_type' => 'required|in:NONE,TEXT,IMAGE,VIDEO,DOCUMENT',
            'header_text' => 'nullable|string|max:60',
            'body_text' => 'required|string|max:1024',
            'footer_text' => 'nullable|string|max:60',
        ]);

        $api = new WhatsAppCloudApiService($tenant);

        // Build Meta payload
        $components = [];

        if ($validated['header_type'] === 'TEXT' && ! empty($validated['header_text'])) {
            $components[] = [
                'type' => 'HEADER',
                'format' => 'TEXT',
                'text' => $validated['header_text'],
            ];
        } elseif (in_array($validated['header_type'], ['IMAGE', 'VIDEO', 'DOCUMENT'])) {
            $components[] = [
                'type' => 'HEADER',
                'format' => $validated['header_type'],
            ];
        }

        $components[] = [
            'type' => 'BODY',
            'text' => $validated['body_text'],
        ];

        if (! empty($validated['footer_text'])) {
            $components[] = [
                'type' => 'FOOTER',
                'text' => $validated['footer_text'],
            ];
        }

        $metaPayload = [
            'name' => strtolower($validated['name']),
            'category' => $validated['category'],
            'language' => $validated['language'],
            'components' => $components,
        ];

        $result = $api->createTemplate($metaPayload);

        // Save local record
        WhatsappTemplate::create([
            'tenant_id' => $tenant->id,
            'name' => strtolower($validated['name']),
            'language' => $validated['language'],
            'category' => $validated['category'],
            'status' => ($result['success'] ?? false) ? 'PENDING' : 'REJECTED',
            'header_type' => $validated['header_type'],
            'header_content' => $validated['header_text'] ?? null,
            'body_text' => $validated['body_text'],
            'footer_text' => $validated['footer_text'] ?? null,
            'meta_raw_data' => $result['data'] ?? null,
            'rejection_reason' => ! ($result['success'] ?? false) ? ($result['message'] ?? 'Meta error') : null,
        ]);

        if ($result['success'] ?? false) {
            return back()->with('success', "Template '{$validated['name']}' submitted to Meta for approval (Status: PENDING).");
        }

        return back()->with('warning', "Template saved locally, but Meta rejected submission: ".($result['message'] ?? 'Unknown error'));
    }

    /**
     * MODULE 5: Bulk WhatsApp Campaigns
     */
    public function campaigns(): View
    {
        $tenant = $this->getTenant();

        $campaigns = WhatsappCampaign::with('template', 'creator')
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->paginate(15);

        $templates = WhatsappTemplate::where('tenant_id', $tenant->id)
            ->where('status', 'APPROVED')
            ->get();

        $cities = WhatsappContact::where('tenant_id', $tenant->id)
            ->whereNotNull('city')
            ->distinct()
            ->pluck('city');

        $stats = [
            'total' => WhatsappCampaign::where('tenant_id', $tenant->id)->count(),
            'running' => WhatsappCampaign::where('tenant_id', $tenant->id)->where('status', 'running')->count(),
            'completed' => WhatsappCampaign::where('tenant_id', $tenant->id)->where('status', 'completed')->count(),
            'total_sent' => (int) WhatsappCampaign::where('tenant_id', $tenant->id)->sum('sent_count'),
        ];

        return view('app.whatsapp.campaigns.index', compact('campaigns', 'templates', 'cities', 'stats'));
    }

    public function estimateAudience(Request $request): JsonResponse
    {
        $tenant = $this->getTenant();
        $templateId = (int) $request->template_id;
        $template = WhatsappTemplate::where('tenant_id', $tenant->id)->find($templateId);

        if (! $template) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $filters = [
            'type' => $request->target_type,
            'city' => $request->city,
        ];

        $estimate = WhatsAppCampaignService::estimateAudienceAndCost($template, $filters, $tenant);

        return response()->json($estimate);
    }

    public function sendTestMessage(Request $request): JsonResponse
    {
        $tenant = $this->getTenant();
        $templateId = (int) $request->template_id;
        $testPhone = trim((string) $request->phone);

        $normalized = WhatsAppContactService::normalizeIndianPhoneNumber($testPhone);
        if (! $normalized) {
            return response()->json(['success' => false, 'message' => 'Please enter a valid 10-digit Indian test mobile number.'], 422);
        }

        $template = WhatsappTemplate::where('tenant_id', $tenant->id)->findOrFail($templateId);

        $variables = $request->input('variables', ['Test Recipient', 'ARMOUR 24-7 GYM']);

        $result = WhatsAppCampaignService::sendTestMessage($template, $normalized, $variables);

        return response()->json($result);
    }

    public function storeCampaign(Request $request): RedirectResponse
    {
        $tenant = $this->getTenant();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'template_id' => 'required|exists:whatsapp_templates,id',
            'target_type' => 'nullable|in:all_opted_in,member,lead,external',
            'city' => 'nullable|string',
            'schedule_type' => 'required|in:now,schedule',
            'scheduled_at' => 'nullable|required_if:schedule_type,schedule|date|after:now',
        ]);

        $template = WhatsappTemplate::where('tenant_id', $tenant->id)->findOrFail($validated['template_id']);

        $filters = [
            'type' => $validated['target_type'] ?? 'all_opted_in',
            'city' => $validated['city'] ?? null,
        ];

        $scheduledAt = ($validated['schedule_type'] === 'schedule') ? $validated['scheduled_at'] : null;

        try {
            $campaign = WhatsAppCampaignService::createCampaign(
                name: $validated['name'],
                template: $template,
                filters: $filters,
                scheduledAt: $scheduledAt,
                tenant: $tenant,
                userId: auth()->id()
            );

            if ($validated['schedule_type'] === 'now') {
                WhatsAppCampaignService::launchCampaign($campaign);
                return redirect()->route('app.whatsapp.campaigns.history', $campaign->id)->with('success', "Campaign '{$campaign->name}' launched successfully! Queued for {$campaign->total_recipients} opted-in recipients.");
            }

            return redirect()->route('app.whatsapp.campaigns.index')->with('success', "Campaign '{$campaign->name}' scheduled for {$campaign->scheduled_at->format('d M Y, h:i A')}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function pauseCampaign(int $id): RedirectResponse
    {
        $tenant = $this->getTenant();
        $campaign = WhatsappCampaign::where('tenant_id', $tenant->id)->findOrFail($id);

        try {
            WhatsAppCampaignService::pauseCampaign($campaign);
            return back()->with('success', "Campaign '{$campaign->name}' paused.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function resumeCampaign(int $id): RedirectResponse
    {
        $tenant = $this->getTenant();
        $campaign = WhatsappCampaign::where('tenant_id', $tenant->id)->findOrFail($id);

        try {
            WhatsAppCampaignService::resumeCampaign($campaign);
            return back()->with('success', "Campaign '{$campaign->name}' resumed.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancelCampaign(int $id): RedirectResponse
    {
        $tenant = $this->getTenant();
        $campaign = WhatsappCampaign::where('tenant_id', $tenant->id)->findOrFail($id);

        try {
            WhatsAppCampaignService::cancelCampaign($campaign);
            return back()->with('success', "Campaign '{$campaign->name}' cancelled.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function campaignHistory(Request $request, int $id): View
    {
        $tenant = $this->getTenant();
        $campaign = WhatsappCampaign::with('template', 'creator')
            ->where('tenant_id', $tenant->id)
            ->findOrFail($id);

        $query = WhatsappCampaignRecipient::with('contact')
            ->where('campaign_id', $campaign->id)
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('phone', 'like', "%{$s}%")
                    ->orWhereHas('contact', function ($cq) use ($s) {
                        $cq->where('name', 'like', "%{$s}%");
                    });
            });
        }

        $recipients = $query->paginate(25)->withQueryString();

        return view('app.whatsapp.campaigns.history', compact('campaign', 'recipients'));
    }

    /**
     * MODULE 7: WhatsApp 2-Column Live Inbox
     */
    public function inbox(Request $request): View
    {
        $tenant = $this->getTenant();

        $conversationsQuery = WhatsappConversation::with('contact')
            ->where('tenant_id', $tenant->id)
            ->latest('last_message_at');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $conversationsQuery->where(function ($q) use ($s) {
                $q->where('phone', 'like', "%{$s}%")
                    ->orWhereHas('contact', function ($cq) use ($s) {
                        $cq->where('name', 'like', "%{$s}%");
                    });
            });
        }

        if ($request->get('filter') === 'unread') {
            $conversationsQuery->where('unread_count', '>', 0);
        }

        $conversations = $conversationsQuery->paginate(20)->withQueryString();

        // Selected Conversation
        $activeConversation = null;
        $activeMessages = collect();

        if ($request->filled('conversation_id')) {
            $activeConversation = WhatsappConversation::with('contact')
                ->where('tenant_id', $tenant->id)
                ->find((int) $request->conversation_id);
        } elseif ($conversations->isNotEmpty()) {
            $activeConversation = $conversations->first();
        }

        if ($activeConversation) {
            $activeMessages = WhatsappMessage::where('conversation_id', $activeConversation->id)
                ->oldest('id')
                ->get();

            // Clear unread count when opening
            if ($activeConversation->unread_count > 0) {
                $activeConversation->update(['unread_count' => 0]);
            }
        }

        $approvedTemplates = WhatsappTemplate::where('tenant_id', $tenant->id)
            ->where('status', 'APPROVED')
            ->get();

        return view('app.whatsapp.inbox.index', compact('conversations', 'activeConversation', 'activeMessages', 'approvedTemplates'));
    }

    public function sendInboxTextMessage(Request $request, int $id): RedirectResponse
    {
        $tenant = $this->getTenant();
        $conversation = WhatsappConversation::with('contact')->where('tenant_id', $tenant->id)->findOrFail($id);

        $request->validate([
            'message' => 'required|string|max:4096',
        ]);

        // CRITICAL META POLICY: Verify 24-hr customer service window
        if (! $conversation->isCustomerServiceWindowOpen()) {
            return back()->with('error', 'The 24-hour customer service window for this contact has expired. Meta policy requires an approved Template message to re-initiate communication.');
        }

        $api = new WhatsAppCloudApiService($tenant);
        $result = $api->sendTextMessage($conversation->phone, $request->message);

        if ($result['success'] ?? false) {
            WhatsappMessage::create([
                'tenant_id' => $tenant->id,
                'conversation_id' => $conversation->id,
                'contact_id' => $conversation->contact_id,
                'direction' => 'outbound',
                'type' => 'text',
                'message_body' => $request->message,
                'wamid' => $result['wamid'] ?? null,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $conversation->update([
                'last_message_at' => now(),
                'last_message_preview' => substr($request->message, 0, 100),
            ]);

            return back()->with('success', 'Message sent successfully.');
        }

        return back()->with('error', 'Failed to send message: '.($result['error_message'] ?? 'API error'));
    }

    public function sendInboxTemplateMessage(Request $request, int $id): RedirectResponse
    {
        $tenant = $this->getTenant();
        $conversation = WhatsappConversation::with('contact')->where('tenant_id', $tenant->id)->findOrFail($id);

        $request->validate([
            'template_id' => 'required|exists:whatsapp_templates,id',
        ]);

        $template = WhatsappTemplate::where('tenant_id', $tenant->id)->findOrFail($request->template_id);

        if (! $template->isApproved()) {
            return back()->with('error', "Template '{$template->name}' is not approved by Meta.");
        }

        $api = new WhatsAppCloudApiService($tenant);

        $variables = [$conversation->contact?->name ?: 'Member'];
        $components = WhatsAppCampaignService::buildTemplateComponents($template, $variables);

        $result = $api->sendTemplateMessage(
            to: $conversation->phone,
            templateName: $template->name,
            languageCode: $template->language ?: 'en',
            components: $components
        );

        if ($result['success'] ?? false) {
            WhatsappMessage::create([
                'tenant_id' => $tenant->id,
                'conversation_id' => $conversation->id,
                'contact_id' => $conversation->contact_id,
                'direction' => 'outbound',
                'type' => 'template',
                'message_body' => $template->body_text,
                'wamid' => $result['wamid'] ?? null,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $conversation->update([
                'last_message_at' => now(),
                'last_message_preview' => "Template: {$template->name}",
            ]);

            return back()->with('success', "Template '{$template->name}' sent successfully.");
        }

        return back()->with('error', 'Failed to send template: '.($result['error_message'] ?? 'API error'));
    }

    public function toggleInboxOptOut(int $id): RedirectResponse
    {
        $tenant = $this->getTenant();
        $conversation = WhatsappConversation::with('contact')->where('tenant_id', $tenant->id)->findOrFail($id);
        $contact = $conversation->contact;

        $newStatus = ($contact->consent_status === 'opted_out') ? 'opted_in' : 'opted_out';

        WhatsAppContactService::updateConsent(
            contact: $contact,
            newStatus: $newStatus,
            source: 'Admin Inbox Toggle',
            wordingVersion: 'Direct toggle inside WhatsApp inbox',
            notes: 'Changed by staff from inbox UI',
            userId: auth()->id()
        );

        return back()->with('success', "Contact consent updated to '{$newStatus}'. Future marketing messages will be ".($newStatus === 'opted_out' ? 'suppressed' : 'allowed').'.');
    }

    /**
     * MODULE 6: Automatic WhatsApp Messages
     */
    public function automations(): View
    {
        $tenant = $this->getTenant();

        // Seed default 7 automations if not present
        $this->ensureDefaultAutomationsExist($tenant);

        $automations = WhatsappAutomation::with('template', 'logs')
            ->where('tenant_id', $tenant->id)
            ->get();

        $templates = WhatsappTemplate::where('tenant_id', $tenant->id)
            ->where('status', 'APPROVED')
            ->get();

        $recentLogs = WhatsappAutomationLog::with('automation', 'contact')
            ->where('tenant_id', $tenant->id)
            ->latest('executed_at')
            ->limit(20)
            ->get();

        return view('app.whatsapp.automations.index', compact('automations', 'templates', 'recentLogs'));
    }

    public function toggleAutomation(int $id): RedirectResponse
    {
        $tenant = $this->getTenant();
        $automation = WhatsappAutomation::where('tenant_id', $tenant->id)->findOrFail($id);

        $automation->is_active = ! $automation->is_active;
        $automation->save();

        $state = $automation->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Automation '{$automation->name}' {$state}.");
    }

    public function updateAutomation(Request $request, int $id): RedirectResponse
    {
        $tenant = $this->getTenant();
        $automation = WhatsappAutomation::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'template_id' => 'nullable|exists:whatsapp_templates,id',
            'days_offset' => 'nullable|integer',
            'send_time' => 'nullable|string',
            'message_category' => 'required|in:UTILITY,MARKETING',
        ]);

        $automation->update([
            'template_id' => $validated['template_id'] ?? null,
            'days_offset' => $validated['days_offset'] ?? 0,
            'send_time' => $validated['send_time'] ?? '10:00',
            'message_category' => $validated['message_category'],
        ]);

        return back()->with('success', "Automation '{$automation->name}' updated successfully.");
    }

    public function triggerAutomationRunNow(int $id): RedirectResponse
    {
        $tenant = $this->getTenant();
        $automation = WhatsappAutomation::where('tenant_id', $tenant->id)->findOrFail($id);

        $api = new WhatsAppCloudApiService($tenant);
        $result = WhatsAppAutomationService::processAutomation($automation, $api);

        return back()->with('success', "Automation executed: {$result['sent']} message(s) sent, {$result['skipped']} skipped.");
    }

    protected function ensureDefaultAutomationsExist(Tenant $tenant): void
    {
        $defaults = [
            [
                'name' => 'New Member Welcome Message',
                'trigger_event' => 'member_created',
                'message_category' => 'UTILITY',
                'days_offset' => 0,
                'send_time' => '10:00',
            ],
            [
                'name' => 'Membership Expiry Reminder (3 Days Before)',
                'trigger_event' => 'membership_expiring',
                'message_category' => 'UTILITY',
                'days_offset' => -3,
                'send_time' => '10:00',
            ],
            [
                'name' => 'Membership Renewal Reminder (Post-Expiry)',
                'trigger_event' => 'membership_expired',
                'message_category' => 'MARKETING',
                'days_offset' => 1,
                'send_time' => '11:00',
            ],
            [
                'name' => 'Payment Confirmation & Receipt',
                'trigger_event' => 'payment_received',
                'message_category' => 'UTILITY',
                'days_offset' => 0,
                'send_time' => '10:00',
            ],
            [
                'name' => 'Member Birthday Greetings',
                'trigger_event' => 'member_birthday',
                'message_category' => 'UTILITY',
                'days_offset' => 0,
                'send_time' => '09:00',
            ],
            [
                'name' => 'New Lead Welcome & Enquiry Follow-up',
                'trigger_event' => 'lead_created',
                'message_category' => 'UTILITY',
                'days_offset' => 0,
                'send_time' => '10:00',
            ],
        ];

        foreach ($defaults as $def) {
            WhatsappAutomation::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'trigger_event' => $def['trigger_event'],
                ],
                [
                    'name' => $def['name'],
                    'message_category' => $def['message_category'],
                    'days_offset' => $def['days_offset'],
                    'send_time' => $def['send_time'],
                    'is_active' => false, // Inactive until staff pairs with an approved template
                ]
            );
        }
    }

    /**
     * MODULE 3 & SETTINGS: WhatsApp Cloud API Credentials & Configuration
     */
    public function settings(): View
    {
        $tenant = $this->getTenant();
        $tenantId = $tenant->id;

        $credentials = [
            'waba_id' => Setting::get('whatsapp_business_account_id', config('services.whatsapp.business_account_id'), $tenantId),
            'phone_number_id' => Setting::get('whatsapp_phone_number_id', config('services.whatsapp.phone_number_id'), $tenantId),
            'access_token' => Setting::get('whatsapp_access_token', config('services.whatsapp.access_token'), $tenantId),
            'graph_version' => Setting::get('whatsapp_graph_version', config('services.whatsapp.graph_version', 'v22.0'), $tenantId) ?: 'v22.0',
            'webhook_verify_token' => Setting::get('whatsapp_webhook_verify_token', config('services.whatsapp.webhook_verify_token', 'armour_gym_secure_webhook_token_2026'), $tenantId),
            'app_secret' => Setting::get('whatsapp_app_secret', config('services.whatsapp.app_secret'), $tenantId),
            'marketing_rate' => Setting::get('whatsapp_marketing_rate', config('services.whatsapp.marketing_rate', 0.85), $tenantId),
            'utility_rate' => Setting::get('whatsapp_utility_rate', config('services.whatsapp.utility_rate', 0.35), $tenantId),
        ];

        $webhookUrl = url('/api/whatsapp/webhook');

        $api = new WhatsAppCloudApiService($tenant);
        $isConfigured = $api->isConfigured();

        return view('app.whatsapp.settings.index', compact('credentials', 'webhookUrl', 'isConfigured'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $tenant = $this->getTenant();
        $tenantId = $tenant->id;

        $validated = $request->validate([
            'waba_id' => 'nullable|string|max:255',
            'phone_number_id' => 'nullable|string|max:255',
            'access_token' => 'nullable|string',
            'graph_version' => 'nullable|string|max:20',
            'webhook_verify_token' => 'nullable|string|max:255',
            'app_secret' => 'nullable|string|max:255',
            'marketing_rate' => 'nullable|numeric|min:0',
            'utility_rate' => 'nullable|numeric|min:0',
        ]);

        Setting::set('whatsapp_business_account_id', trim((string) ($validated['waba_id'] ?? '')), 'string', $tenantId);
        Setting::set('whatsapp_phone_number_id', trim((string) ($validated['phone_number_id'] ?? '')), 'string', $tenantId);

        // Only update access token and app secret if explicitly provided (not empty)
        if (! empty($validated['access_token'])) {
            Setting::set('whatsapp_access_token', trim($validated['access_token']), 'string', $tenantId);
        }

        if (! empty($validated['app_secret'])) {
            Setting::set('whatsapp_app_secret', trim($validated['app_secret']), 'string', $tenantId);
        }

        Setting::set('whatsapp_graph_version', trim((string) ($validated['graph_version'] ?? 'v22.0')), 'string', $tenantId);
        Setting::set('whatsapp_webhook_verify_token', trim((string) ($validated['webhook_verify_token'] ?? 'armour_gym_secure_webhook_token_2026')), 'string', $tenantId);
        Setting::set('whatsapp_marketing_rate', (float) ($validated['marketing_rate'] ?? 0.85), 'string', $tenantId);
        Setting::set('whatsapp_utility_rate', (float) ($validated['utility_rate'] ?? 0.35), 'string', $tenantId);

        return back()->with('success', 'WhatsApp Cloud API settings saved successfully.');
    }

    public function testConnection(): JsonResponse
    {
        $tenant = $this->getTenant();
        $api = new WhatsAppCloudApiService($tenant);

        $result = $api->testConnection();

        return response()->json($result);
    }
}

