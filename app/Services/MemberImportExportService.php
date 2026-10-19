<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Tenant;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberImportExportService
{
    /**
     * Stream CSV export of gym members matching given filters.
     */
    public static function exportCsv(Tenant $tenant, array $filters = []): StreamedResponse
    {
        $query = Member::where('tenant_id', $tenant->id)
            ->with(['branch', 'activeMembership.plan', 'activeMembership.payments', 'memberships.plan']);

        // Search filter
        if (! empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('member_code', 'like', "%{$s}%");
            });
        }

        // Status filter
        if (! empty($filters['status'])) {
            $status = strtoupper(trim($filters['status']));
            $query->where('status', $status);
        }

        // Branch filter
        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', (int) $filters['branch_id']);
        }

        // Special quick filters
        if (! empty($filters['filter'])) {
            $filter = $filters['filter'];
            if ($filter === 'expiring' || $filter === 'churn') {
                $query->whereHas('activeMembership', function ($q) {
                    $q->where('status', 'ACTIVE')
                        ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()]);
                });
            } elseif ($filter === 'due' || $filter === 'pending_fee') {
                $query->where(function ($subQ) {
                    $subQ->whereHas('activeMembership', function ($q) {
                        $q->whereRaw('final_amount > paid_amount');
                    })->orWhereHas('memberships', function ($q) {
                        $q->whereRaw('final_amount > paid_amount');
                    });
                });
            } elseif ($filter === 'birthday') {
                $query->whereNotNull('dob')
                    ->whereMonth('dob', now()->month)
                    ->whereDay('dob', now()->day);
            } elseif ($filter === 'dormant') {
                $query->where('status', 'ACTIVE')
                    ->whereDoesntHave('attendances', function ($q) {
                        $q->where('date', '>=', now()->subDays(14)->toDateString());
                    });
            }
        }

        $currency = $tenant->currency_symbol ?? '₹';
        $timestamp = now()->format('Y_m_d_His');
        $fileName = 'gym_members_export_' . $timestamp . '.csv';

        return response()->streamDownload(function () use ($query, $currency) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Header Row
            fputcsv($output, [
                'Member ID / Code',
                'First Name',
                'Last Name',
                'Full Name',
                'Mobile Phone',
                'Alternate Phone',
                'Email',
                'Gender',
                'Date of Birth',
                'Blood Group',
                'City',
                'Residential Address',
                'Emergency Contact Name',
                'Emergency Contact Phone',
                'Emergency Relation',
                'Join Date',
                'Status',
                'Branch Name',
                'Active Plan',
                'Plan Expiry Date',
                "Remaining Due ({$currency})",
                'Notes',
                'Registered At',
            ]);

            $query->orderBy('id', 'desc')->chunk(250, function ($members) use ($output) {
                foreach ($members as $member) {
                    $meta = is_array($member->metadata) ? $member->metadata : [];
                    $membership = $member->activeMembership ?? $member->memberships()->latest()->first();
                    $finalAmt = (float) ($membership?->final_amount ?? 0);
                    $paidAmt = (float) ($membership?->paid_amount ?? 0);
                    $remainingDue = max(0, $finalAmt - $paidAmt);

                    fputcsv($output, [
                        $member->member_code ?? '',
                        $member->first_name ?? '',
                        $member->last_name ?? '',
                        $member->full_name,
                        $member->phone ?? '',
                        $meta['alternate_phone'] ?? '',
                        $member->email ?? '',
                        ucfirst($member->gender ?? 'male'),
                        $member->dob ? $member->dob->format('Y-m-d') : '',
                        $meta['blood_group'] ?? '',
                        $meta['city'] ?? '',
                        $member->address ?? '',
                        $member->emergency_contact_name ?? '',
                        $member->emergency_contact_phone ?? '',
                        $meta['emergency_relation'] ?? '',
                        $member->join_date ? $member->join_date->format('Y-m-d') : '',
                        $member->status ?? 'ACTIVE',
                        $member->branch?->name ?? 'Main Branch',
                        $membership?->plan?->name ?? 'No Active Plan',
                        $membership?->end_date ? $membership->end_date->format('Y-m-d') : '',
                        number_format($remainingDue, 2, '.', ''),
                        $member->notes ?? '',
                        $member->created_at ? $member->created_at->format('Y-m-d H:i') : '',
                    ]);
                }
            });

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Download a clean CSV template with explanatory column headers and sample data.
     */
    public static function downloadSampleCsv(): StreamedResponse
    {
        $fileName = 'gymconsole_members_sample_template.csv';

        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header line
            fputcsv($output, [
                'first_name',
                'last_name',
                'phone',
                'email',
                'gender',
                'dob',
                'blood_group',
                'city',
                'address',
                'emergency_contact_name',
                'emergency_contact_phone',
                'join_date',
                'status',
                'notes',
                'member_code',
            ]);

            // Sample Rows
            fputcsv($output, [
                'Aarav',
                'Sharma',
                '9876543210',
                'aarav.sharma@example.com',
                'male',
                '1996-08-14',
                'B+',
                'Mumbai',
                'Flat 402, Sunshine Heights, Andheri West',
                'Rajesh Sharma',
                '9876543211',
                now()->format('Y-m-d'),
                'ACTIVE',
                'Prefers morning workout sessions',
                '',
            ]);

            fputcsv($output, [
                'Priya',
                'Patel',
                '9974995179',
                'priya.patel@example.com',
                'female',
                '1998-11-23',
                'O+',
                'Ahmedabad',
                '12 Satellite Road, Bodakdev',
                'Sanjay Patel',
                '9974995180',
                now()->format('Y-m-d'),
                'ACTIVE',
                'Joined for cardio & strength training',
                '',
            ]);

            fputcsv($output, [
                'Vikram',
                'Singh',
                '9123456780',
                'vikram.singh@example.com',
                'male',
                '1992-04-18',
                'A+',
                'Delhi',
                'B-14 Sector 62, Noida',
                'Anita Singh',
                '9123456781',
                now()->format('Y-m-d'),
                'ACTIVE',
                'Enrolled via fitness camp referral',
                '',
            ]);

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Parse and validate CSV data before persisting.
     */
    public static function previewCsv(string $filePath, Tenant $tenant, ?int $defaultBranchId = null): array
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            throw new Exception('Uploaded CSV file could not be read.');
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception('Unable to open CSV file stream.');
        }

        // Read header
        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            throw new Exception('Uploaded CSV file appears to be completely empty.');
        }

        // Map column headers to standard keys
        $headerMap = [];
        foreach ($header as $idx => $rawCol) {
            // Normalize header names: trim BOM, symbols, reduce multiple underscores
            $clean = strtolower(trim(preg_replace('/_+/', '_', preg_replace('/[^a-zA-Z0-9]/', '_', (string) $rawCol)), '_'));
            if (! empty($clean)) {
                $headerMap[$clean] = $idx;
                $noUnderscore = str_replace('_', '', $clean);
                $headerMap[$noUnderscore] = $idx;
            }
        }

        // Existing phone numbers in this tenant to flag duplicates
        $existingPhones = Member::where('tenant_id', $tenant->id)
            ->pluck('phone', 'id')
            ->toArray();

        // Normalized phone lookup: ['+919974995179' => member_id, '9974995179' => member_id]
        $existingPhoneMap = [];
        foreach ($existingPhones as $id => $phone) {
            $norm = self::normalizePhone($phone);
            if ($norm) {
                $existingPhoneMap[$norm] = $id;
                $existingPhoneMap[substr($norm, -10)] = $id;
            }
        }

        $validRows = [];
        $duplicateRows = [];
        $invalidRows = [];
        $seenInCsv = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            // Skip entirely empty lines
            if (empty(array_filter($row, fn($val) => trim((string)$val) !== ''))) {
                continue;
            }

            // Extract values with exhaustive candidate aliases
            $firstName = self::extractCol($row, $headerMap, ['first_name', 'firstname', 'first', 'given_name']);
            $lastName = self::extractCol($row, $headerMap, ['last_name', 'lastname', 'last', 'surname', 'family_name']) ?? '';
            $fullName = self::extractCol($row, $headerMap, ['full_name', 'fullname', 'name', 'member_name', 'membername']);

            $rawPhone = self::extractCol($row, $headerMap, [
                'mobile_phone', 'mobilephone', 'phone', 'mobile', 'mobile_no', 'mobileno',
                'phone_number', 'phonenumber', 'contact', 'contact_number', 'contactnumber',
                'contact_no', 'contactno', 'whatsapp', 'primary_phone'
            ]) ?? '';

            $altPhone = self::extractCol($row, $headerMap, ['alternate_phone', 'alternatephone', 'alt_phone', 'altphone', 'secondary_phone']);
            $email = self::extractCol($row, $headerMap, ['email', 'email_address', 'emailaddress', 'mail', 'email_id', 'emailid']);
            $gender = self::extractCol($row, $headerMap, ['gender', 'sex']) ?? 'male';
            $dob = self::extractCol($row, $headerMap, ['date_of_birth', 'dateofbirth', 'dob', 'birth_date', 'birthdate', 'birth_day']);
            $bloodGroup = self::extractCol($row, $headerMap, ['blood_group', 'bloodgroup', 'blood', 'blood_type']);
            $city = self::extractCol($row, $headerMap, ['city', 'town', 'district', 'location']);
            $address = self::extractCol($row, $headerMap, ['residential_address', 'residentialaddress', 'address', 'home_address', 'resident_address', 'street', 'area']);
            $emergName = self::extractCol($row, $headerMap, ['emergency_contact_name', 'emergencycontactname', 'emergency_name', 'emergency_contact', 'emergency_person']);
            $emergPhone = self::extractCol($row, $headerMap, ['emergency_contact_phone', 'emergencycontactphone', 'emergency_phone', 'emergency_mobile', 'emergency_number', 'emergency_no']);
            $emergRelation = self::extractCol($row, $headerMap, ['emergency_relation', 'emergencyrelation', 'relation', 'relationship']);
            $joinDate = self::extractCol($row, $headerMap, ['join_date', 'joindate', 'joining_date', 'date_joined', 'admission_date', 'start_date', 'registered_at', 'registered_date']);
            $status = strtoupper(trim(self::extractCol($row, $headerMap, ['status', 'member_status', 'state']) ?? 'ACTIVE'));
            $branchName = self::extractCol($row, $headerMap, ['branch_name', 'branchname', 'branch']);
            $planName = self::extractCol($row, $headerMap, ['active_plan', 'activeplan', 'plan_name', 'planname', 'plan', 'membership_plan']);
            $planExpiryDate = self::extractCol($row, $headerMap, ['plan_expiry_date', 'planexpirydate', 'expiry_date', 'expirydate', 'end_date', 'enddate']);
            $remainingDue = self::extractCol($row, $headerMap, ['remaining_due', 'remainingdue', 'due', 'balance', 'pending_fee']);
            $notes = self::extractCol($row, $headerMap, ['notes', 'remarks', 'comment', 'description']);
            $memberCode = self::extractCol($row, $headerMap, ['member_code', 'membercode', 'member_id_code', 'memberidcode', 'member_id', 'memberid', 'code', 'id_number', 'member_no', 'memberno']);

            // Fallback for names: If first_name is empty but full_name is present, split it
            if (empty($firstName) && ! empty($fullName)) {
                $nameParts = explode(' ', trim($fullName), 2);
                $firstName = $nameParts[0];
                if (empty($lastName)) {
                    $lastName = $nameParts[1] ?? '';
                }
            } elseif (empty($firstName) && empty($fullName) && ! empty($lastName)) {
                $firstName = $lastName;
                $lastName = '';
            }

            // Validation: First Name required
            if (empty($firstName)) {
                $invalidRows[] = [
                    'row' => $rowNum,
                    'name' => 'Unknown',
                    'phone' => $rawPhone,
                    'reason' => 'First Name is missing or empty.',
                ];
                continue;
            }

            // Validation: Phone required & formatted
            $normalizedPhone = self::normalizePhone($rawPhone);
            if (! $normalizedPhone) {
                $invalidRows[] = [
                    'row' => $rowNum,
                    'name' => trim("{$firstName} {$lastName}"),
                    'phone' => $rawPhone,
                    'reason' => 'Invalid phone number (must be a valid 10-digit mobile number).',
                ];
                continue;
            }

            // Validation: Email format check if provided
            if (! empty($email) && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalidRows[] = [
                    'row' => $rowNum,
                    'name' => trim("{$firstName} {$lastName}"),
                    'phone' => $normalizedPhone,
                    'reason' => "Invalid email address format ('{$email}').",
                ];
                continue;
            }

            // Check Duplicate: in CSV
            $tenDigit = substr($normalizedPhone, -10);
            if (isset($seenInCsv[$tenDigit])) {
                $duplicateRows[] = [
                    'row' => $rowNum,
                    'name' => trim("{$firstName} {$lastName}"),
                    'phone' => $normalizedPhone,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'gender' => $gender,
                    'dob' => self::parseDate($dob),
                    'address' => $address,
                    'emergency_contact_name' => $emergName,
                    'emergency_contact_phone' => self::normalizePhone($emergPhone),
                    'notes' => $notes,
                    'reason' => "Phone number is duplicated in row #{$seenInCsv[$tenDigit]} of this CSV.",
                    'is_db_duplicate' => false,
                ];
                continue;
            }
            $seenInCsv[$tenDigit] = $rowNum;

            // Check Duplicate: in Database
            if (isset($existingPhoneMap[$tenDigit])) {
                $duplicateRows[] = [
                    'row' => $rowNum,
                    'name' => trim("{$firstName} {$lastName}"),
                    'phone' => $normalizedPhone,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'gender' => $gender,
                    'dob' => self::parseDate($dob),
                    'address' => $address,
                    'emergency_contact_name' => $emergName,
                    'emergency_contact_phone' => self::normalizePhone($emergPhone),
                    'notes' => $notes,
                    'reason' => 'Member with this mobile number already exists in your gym database.',
                    'is_db_duplicate' => true,
                    'existing_member_id' => $existingPhoneMap[$tenDigit],
                ];
                continue;
            }

            // Normalize gender
            $cleanGender = match (strtolower(trim($gender))) {
                'female', 'f' => 'female',
                'other', 'o' => 'other',
                default => 'male',
            };

            // Normalize status
            if (! in_array($status, ['ACTIVE', 'INACTIVE', 'SUSPENDED', 'EXPIRED'])) {
                $status = 'ACTIVE';
            }

            // Parse dates safely
            $parsedDob = self::parseDate($dob);
            $parsedJoinDate = self::parseDate($joinDate) ?? now()->toDateString();

            $validRows[] = [
                'row' => $rowNum,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $normalizedPhone,
                'alternate_phone' => self::normalizePhone($altPhone),
                'email' => ! empty($email) ? strtolower(trim($email)) : null,
                'gender' => $cleanGender,
                'dob' => $parsedDob,
                'blood_group' => $bloodGroup,
                'city' => $city,
                'address' => $address,
                'emergency_contact_name' => $emergName,
                'emergency_contact_phone' => self::normalizePhone($emergPhone),
                'emergency_relation' => $emergRelation,
                'join_date' => $parsedJoinDate,
                'status' => $status,
                'branch_name' => $branchName,
                'plan_name' => $planName,
                'plan_expiry_date' => self::parseDate($planExpiryDate),
                'remaining_due' => ! empty($remainingDue) ? (float) $remainingDue : null,
                'notes' => $notes,
                'member_code' => ! empty($memberCode) ? trim($memberCode) : null,
                'branch_id' => $defaultBranchId,
            ];
        }

        fclose($handle);

        return [
            'total_parsed' => count($validRows) + count($duplicateRows) + count($invalidRows),
            'valid_count' => count($validRows),
            'duplicate_count' => count($duplicateRows),
            'invalid_count' => count($invalidRows),
            'valid_rows' => $validRows,
            'duplicate_rows' => $duplicateRows,
            'invalid_rows' => $invalidRows,
        ];
    }

    /**
     * Persist validated rows into database in a safe transaction.
     */
    public static function commitImport(
        array $rows,
        Tenant $tenant,
        ?int $defaultBranchId = null,
        string $duplicateStrategy = 'skip',
        array $duplicateRows = []
    ): array {
        $featureGate = app(FeatureGateService::class);
        $featureGate->ensureWithinQuota($tenant, 'members');

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        // Resolve default branch
        $branchId = $defaultBranchId ?: ($tenant->mainBranch?->id ?: Branch::where('tenant_id', $tenant->id)->value('id'));

        DB::transaction(function () use (
            $rows,
            $tenant,
            $branchId,
            $duplicateStrategy,
            $duplicateRows,
            &$imported,
            &$updated,
            &$skipped
        ) {
            // Process Valid Rows
            foreach ($rows as $row) {
                $code = $row['member_code'] ?? null;
                if (empty($code)) {
                    $code = self::generateUniqueMemberCode($tenant);
                } else {
                    // If given code already exists, generate a fresh unique code
                    if (Member::where('tenant_id', $tenant->id)->where('member_code', $code)->exists()) {
                        $code = self::generateUniqueMemberCode($tenant);
                    }
                }

                // Resolve specific branch if branch_name was provided in CSV
                $memberBranchId = $row['branch_id'] ?? $branchId;
                if (! empty($row['branch_name'])) {
                    $matchedBranchId = Branch::where('tenant_id', $tenant->id)
                        ->where('name', trim($row['branch_name']))
                        ->value('id');
                    if ($matchedBranchId) {
                        $memberBranchId = $matchedBranchId;
                    }
                }

                $metadata = [
                    'blood_group' => $row['blood_group'] ?? null,
                    'city' => $row['city'] ?? null,
                    'alternate_phone' => $row['alternate_phone'] ?? null,
                    'emergency_relation' => $row['emergency_relation'] ?? null,
                    'import_source' => 'csv_bulk',
                    'imported_at' => now()->toIso8601String(),
                ];

                $member = Member::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $memberBranchId,
                    'member_code' => $code,
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'] ?? '',
                    'email' => $row['email'] ?? null,
                    'phone' => $row['phone'],
                    'gender' => $row['gender'] ?? 'male',
                    'dob' => $row['dob'] ?? null,
                    'address' => $row['address'] ?? null,
                    'emergency_contact_name' => $row['emergency_contact_name'] ?? null,
                    'emergency_contact_phone' => $row['emergency_contact_phone'] ?? null,
                    'join_date' => $row['join_date'] ?? now()->toDateString(),
                    'status' => $row['status'] ?? 'ACTIVE',
                    'notes' => $row['notes'] ?? null,
                    'metadata' => $metadata,
                    'qr_code_token' => Str::random(32),
                ]);

                // Optional: Auto-link membership plan if plan_name was provided and exists
                if (! empty($row['plan_name'])) {
                    $matchedPlan = MembershipPlan::where('tenant_id', $tenant->id)
                        ->where(function ($q) use ($row) {
                            $q->where('name', trim($row['plan_name']))
                              ->orWhere('name', 'like', '%' . trim($row['plan_name']) . '%');
                        })
                        ->first();

                    if ($matchedPlan) {
                        try {
                            $membershipOptions = [
                                'start_date' => $row['join_date'] ?? now()->toDateString(),
                                'notes' => 'Imported via CSV member import',
                            ];
                            $membership = app(MembershipService::class)->assignMembership($member, $matchedPlan, $membershipOptions);

                            if (! empty($row['plan_expiry_date'])) {
                                $membership->update(['end_date' => $row['plan_expiry_date']]);
                            }

                            if (isset($row['remaining_due']) && is_numeric($row['remaining_due'])) {
                                $due = (float) $row['remaining_due'];
                                $finalAmt = (float) $membership->final_amount;
                                $paid = max(0, $finalAmt - $due);
                                $membership->update(['paid_amount' => $paid]);
                            }
                        } catch (\Exception $e) {
                            // If plan assignment fails (e.g. invalid date), continue member creation
                        }
                    }
                }

                $imported++;
            }

            // If duplicate strategy is 'update', update existing members
            if ($duplicateStrategy === 'update' && ! empty($duplicateRows)) {
                foreach ($duplicateRows as $dup) {
                    if (empty($dup['is_db_duplicate']) || empty($dup['existing_member_id'])) {
                        $skipped++;
                        continue;
                    }

                    $existing = Member::where('tenant_id', $tenant->id)->find($dup['existing_member_id']);
                    if (! $existing) {
                        $skipped++;
                        continue;
                    }

                    $updateData = [];
                    if (! empty($dup['first_name'])) {
                        $updateData['first_name'] = $dup['first_name'];
                    }
                    if (isset($dup['last_name'])) {
                        $updateData['last_name'] = $dup['last_name'];
                    }
                    if (! empty($dup['email'])) {
                        $updateData['email'] = $dup['email'];
                    }
                    if (! empty($dup['gender'])) {
                        $updateData['gender'] = $dup['gender'];
                    }
                    if (! empty($dup['dob'])) {
                        $updateData['dob'] = $dup['dob'];
                    }
                    if (! empty($dup['address'])) {
                        $updateData['address'] = $dup['address'];
                    }
                    if (! empty($dup['emergency_contact_name'])) {
                        $updateData['emergency_contact_name'] = $dup['emergency_contact_name'];
                    }
                    if (! empty($dup['emergency_contact_phone'])) {
                        $updateData['emergency_contact_phone'] = $dup['emergency_contact_phone'];
                    }
                    if (! empty($dup['notes'])) {
                        $updateData['notes'] = $dup['notes'];
                    }

                    if (! empty($updateData)) {
                        $existing->update($updateData);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                }
            } else {
                $skipped += count($duplicateRows);
            }

            ActivityLog::log(
                'members_bulk_imported',
                "Bulk imported {$imported} members" . ($updated > 0 ? " and updated {$updated} existing members" : '') . ' via CSV.',
                auth()->user()
            );
        });

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /**
     * Generate unique member code within tenant.
     */
    private static function generateUniqueMemberCode(Tenant $tenant): string
    {
        $code = $tenant->generateNextMemberCode();
        $counter = 1;

        while (Member::where('tenant_id', $tenant->id)->where('member_code', $code)->exists()) {
            $prefix = strtoupper(trim($tenant->member_id_prefix ?: 'GYM'));
            $year = now()->format('y');
            $padding = max(3, min(8, $tenant->member_id_padding ?: 4));
            $nextNum = $tenant->members()->count() + 1 + $counter;
            $code = "{$prefix}{$year}" . str_pad((string) $nextNum, $padding, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $code;
    }

    /**
     * Normalize Indian or international phone number to clean standard string.
     */
    public static function normalizePhone(?string $rawPhone): ?string
    {
        if (empty($rawPhone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $rawPhone);
        $digits = ltrim($digits, '0');

        // Check if prefixed with India country code 91
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }

        // Must be a 10-digit mobile number starting with 6, 7, 8, or 9
        if (strlen($digits) === 10 && preg_match('/^[6-9]\d{9}$/', $digits)) {
            return $digits;
        }

        return null;
    }

    /**
     * Parse date from various common CSV formats (Y-m-d, d-m-Y, d/m/Y).
     */
    public static function parseDate(?string $rawDate): ?string
    {
        if (empty($rawDate)) {
            return null;
        }

        $rawDate = trim($rawDate);
        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'm/d/Y', 'Y/m/d', 'j-n-Y', 'j/n/Y'];

        foreach ($formats as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $rawDate);
                if ($d && $d->year > 1920 && $d->year < 2100) {
                    return $d->format('Y-m-d');
                }
            } catch (\Exception) {
                // try next format
            }
        }

        try {
            $d = Carbon::parse($rawDate);
            if ($d && $d->year > 1920 && $d->year < 2100) {
                return $d->format('Y-m-d');
            }
        } catch (\Exception) {
            // failed
        }

        return null;
    }

    /**
     * Helper to extract a column value from a CSV row by matching candidate column names.
     */
    private static function extractCol(array $row, array $headerMap, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (isset($headerMap[$candidate]) && isset($row[$headerMap[$candidate]])) {
                $val = trim((string) $row[$headerMap[$candidate]]);
                if ($val !== '') {
                    return $val;
                }
            }
        }

        return null;
    }
}

