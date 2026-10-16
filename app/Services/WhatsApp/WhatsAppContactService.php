<?php

namespace App\Services\WhatsApp;

use App\Models\Lead;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\WhatsappContact;
use App\Models\WhatsappContactConsent;
use App\Services\TenantContext;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppContactService
{
    /**
     * Normalize Indian mobile numbers into standard E.164 format (+91XXXXXXXXXX).
     *
     * @return string|null Formatted phone number or null if invalid
     */
    public static function normalizeIndianPhoneNumber(?string $rawPhone): ?string
    {
        if (empty($rawPhone)) {
            return null;
        }

        // Strip non-digit characters
        $digits = preg_replace('/\D/', '', $rawPhone);

        // Strip leading zeros
        $digits = ltrim($digits, '0');

        // Check if prefixed with India country code 91
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }

        // Validate strictly 10-digit Indian mobile starting with 6, 7, 8, or 9
        if (strlen($digits) === 10 && preg_match('/^[6-9]\d{9}$/', $digits)) {
            return '+91'.$digits;
        }

        return null;
    }

    /**
     * Preview CSV contact import before persisting to database.
     */
    public static function previewCsvImport(string $filePath, ?Tenant $tenant = null): array
    {
        $tenantId = $tenant?->id ?? TenantContext::tenantId();
        $validRows = [];
        $invalidRows = [];
        $duplicateRows = [];

        if (! file_exists($filePath) || ! is_readable($filePath)) {
            throw new Exception('Uploaded file is not readable.');
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception('Unable to open CSV file.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            throw new Exception('CSV file is empty.');
        }

        // Normalize header keys
        $headerMap = [];
        foreach ($header as $idx => $colName) {
            $colClean = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $colName))));
            $headerMap[$colClean] = $idx;
        }

        // Existing numbers in this tenant to flag duplicates
        $existingPhones = WhatsappContact::where('tenant_id', $tenantId)
            ->pluck('phone')
            ->flip()
            ->toArray();

        $seenInCsv = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (empty(array_filter($row))) {
                continue;
            }

            $name = self::extractCol($row, $headerMap, ['name', 'full_name', 'customer_name', 'member_name']) ?? 'Contact '.$rowNum;
            $rawPhone = self::extractCol($row, $headerMap, ['phone', 'mobile', 'whatsapp', 'phone_number', 'contact']) ?? '';
            $city = self::extractCol($row, $headerMap, ['city', 'location', 'branch']);
            $tagsRaw = self::extractCol($row, $headerMap, ['tags', 'tag', 'group']);
            $consentRaw = strtolower(trim((string) (self::extractCol($row, $headerMap, ['consent', 'optin', 'opt_in']) ?? '')));

            $normalizedPhone = self::normalizeIndianPhoneNumber($rawPhone);

            if (! $normalizedPhone) {
                $invalidRows[] = [
                    'row' => $rowNum,
                    'name' => $name,
                    'raw_phone' => $rawPhone,
                    'reason' => 'Invalid Indian mobile number (must be 10 digits starting with 6-9)',
                ];
                continue;
            }

            if (isset($existingPhones[$normalizedPhone]) || isset($seenInCsv[$normalizedPhone])) {
                $duplicateRows[] = [
                    'row' => $rowNum,
                    'name' => $name,
                    'phone' => $normalizedPhone,
                    'reason' => 'Phone number already exists in contacts or duplicated in CSV',
                ];
                continue;
            }

            $seenInCsv[$normalizedPhone] = true;

            $tags = [];
            if (! empty($tagsRaw)) {
                $tags = array_values(array_filter(array_map('trim', explode(',', $tagsRaw))));
            }

            // CRITICAL POLICY: Imported contacts NEVER automatically default to opted_in unless explicitly proven
            $consentStatus = 'unknown';
            if (in_array($consentRaw, ['opted_in', 'opted-in', 'optin', 'yes', 'true', '1'])) {
                $consentStatus = 'opted_in';
            } elseif (in_array($consentRaw, ['opted_out', 'opted-out', 'stop', 'no', '0'])) {
                $consentStatus = 'opted_out';
            }

            $validRows[] = [
                'name' => $name,
                'phone' => $normalizedPhone,
                'city' => $city,
                'tags' => $tags,
                'consent_status' => $consentStatus,
            ];
        }

        fclose($handle);

        return [
            'total_parsed' => count($validRows) + count($invalidRows) + count($duplicateRows),
            'valid_count' => count($validRows),
            'invalid_count' => count($invalidRows),
            'duplicate_count' => count($duplicateRows),
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
            'duplicate_rows' => $duplicateRows,
        ];
    }

    /**
     * Persist validated contacts into database with audit log.
     */
    public static function commitImport(array $contacts, ?Tenant $tenant = null, ?int $userId = null, string $wordingVersion = 'CSV Bulk Upload'): int
    {
        $tenantId = $tenant?->id ?? TenantContext::tenantId();
        $importedCount = 0;

        DB::transaction(function () use ($contacts, $tenantId, $userId, $wordingVersion, &$importedCount) {
            foreach ($contacts as $c) {
                $phone = self::normalizeIndianPhoneNumber($c['phone'] ?? '');
                if (! $phone) {
                    continue;
                }

                $contact = WhatsappContact::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'phone' => $phone,
                    ],
                    [
                        'name' => trim($c['name'] ?? 'Contact'),
                        'city' => ! empty($c['city']) ? trim($c['city']) : null,
                        'type' => 'external',
                        'source' => 'csv_import',
                        'tags' => $c['tags'] ?? [],
                        'consent_status' => $c['consent_status'] ?? 'unknown',
                        'consent_updated_at' => ($c['consent_status'] ?? 'unknown') !== 'unknown' ? now() : null,
                        'status' => 'active',
                    ]
                );

                if (($c['consent_status'] ?? 'unknown') !== 'unknown') {
                    WhatsappContactConsent::create([
                        'tenant_id' => $tenantId,
                        'contact_id' => $contact->id,
                        'consent_status' => $c['consent_status'],
                        'source' => 'csv_import_documented',
                        'wording_version' => $wordingVersion,
                        'notes' => 'Bulk CSV import with user verified consent.',
                        'created_by_user_id' => $userId,
                        'recorded_at' => now(),
                    ]);
                }

                $importedCount++;
            }
        });

        return $importedCount;
    }

    /**
     * Synchronize existing gym members into WhatsApp contacts table without duplicating member data.
     */
    public static function syncGymMembers(?Tenant $tenant = null): array
    {
        $tenantId = $tenant?->id ?? TenantContext::tenantId();
        $members = Member::where('tenant_id', $tenantId)->get();

        $synced = 0;
        $skipped = 0;

        foreach ($members as $member) {
            $phone = self::normalizeIndianPhoneNumber($member->phone);
            if (! $phone) {
                $skipped++;
                continue;
            }

            $contact = WhatsappContact::firstOrNew([
                'tenant_id' => $tenantId,
                'phone' => $phone,
            ]);

            $isNew = ! $contact->exists;

            $contact->fill([
                'tenant_id' => $tenantId,
                'branch_id' => $member->branch_id,
                'member_id' => $member->id,
                'name' => $member->full_name ?: ($contact->name ?? 'Gym Member'),
                'city' => $member->address ? substr($member->address, 0, 100) : $contact->city,
                'type' => 'member',
                'source' => $contact->source ?? 'member_sync',
                'status' => $member->status === 'ACTIVE' ? 'active' : 'active',
            ]);

            // Existing members registered in gym with terms can be assigned opt-in consent if new
            if ($isNew && empty($contact->consent_status)) {
                $contact->consent_status = 'opted_in';
                $contact->consent_updated_at = now();
            }

            $contact->save();

            if ($isNew) {
                WhatsappContactConsent::create([
                    'tenant_id' => $tenantId,
                    'contact_id' => $contact->id,
                    'consent_status' => 'opted_in',
                    'source' => 'gym_membership_registration',
                    'wording_version' => 'Member Terms of Service & Communications',
                    'notes' => 'Imported via Gym Member Synchronizer.',
                    'recorded_at' => now(),
                ]);
            }

            $synced++;
        }

        return ['synced' => $synced, 'skipped' => $skipped];
    }

    /**
     * Synchronize existing CRM leads into WhatsApp contacts.
     */
    public static function syncCrmLeads(?Tenant $tenant = null): array
    {
        $tenantId = $tenant?->id ?? TenantContext::tenantId();
        $leads = Lead::where('tenant_id', $tenantId)->get();

        $synced = 0;
        $skipped = 0;

        foreach ($leads as $lead) {
            $phone = self::normalizeIndianPhoneNumber($lead->phone);
            if (! $phone) {
                $skipped++;
                continue;
            }

            $contact = WhatsappContact::firstOrNew([
                'tenant_id' => $tenantId,
                'phone' => $phone,
            ]);

            $isNew = ! $contact->exists;

            // Do not downgrade a member to a lead if already linked
            $type = ($contact->type === 'member') ? 'member' : 'lead';

            $contact->fill([
                'tenant_id' => $tenantId,
                'branch_id' => $lead->branch_id,
                'lead_id' => $lead->id,
                'name' => $lead->name ?: ($contact->name ?? 'Gym Lead'),
                'city' => $lead->city ?: $contact->city,
                'type' => $type,
                'source' => $contact->source ?? 'lead_sync',
                'tags' => array_unique(array_merge($contact->tags ?? [], [$lead->source ?: 'lead'])),
            ]);

            if ($isNew && empty($contact->consent_status)) {
                // Inbound gym enquiries have implicit inquiry consent (marked opted_in)
                $contact->consent_status = 'opted_in';
                $contact->consent_updated_at = now();
            }

            $contact->save();

            if ($isNew) {
                WhatsappContactConsent::create([
                    'tenant_id' => $tenantId,
                    'contact_id' => $contact->id,
                    'consent_status' => 'opted_in',
                    'source' => 'crm_lead_inquiry',
                    'wording_version' => 'Inquiry & Lead Follow-up Consent',
                    'notes' => 'Synchronized from CRM Leads module.',
                    'recorded_at' => now(),
                ]);
            }

            $synced++;
        }

        return ['synced' => $synced, 'skipped' => $skipped];
    }

    /**
     * Update consent status with an immutable audit record.
     */
    public static function updateConsent(
        WhatsappContact $contact,
        string $newStatus,
        string $source,
        ?string $wordingVersion = null,
        ?string $notes = null,
        ?int $userId = null
    ): void {
        $contact->consent_status = $newStatus;
        $contact->consent_updated_at = now();
        $contact->save();

        WhatsappContactConsent::create([
            'tenant_id' => $contact->tenant_id,
            'contact_id' => $contact->id,
            'consent_status' => $newStatus,
            'source' => $source,
            'wording_version' => $wordingVersion,
            'notes' => $notes,
            'created_by_user_id' => $userId,
            'recorded_at' => now(),
        ]);
    }

    /**
     * Helper to extract a column value from a CSV row by matching candidate column names.
     */
    private static function extractCol(array $row, array $headerMap, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (isset($headerMap[$candidate]) && isset($row[$headerMap[$candidate]])) {
                $val = trim($row[$headerMap[$candidate]]);
                if ($val !== '') {
                    return $val;
                }
            }
        }

        return null;
    }
}

