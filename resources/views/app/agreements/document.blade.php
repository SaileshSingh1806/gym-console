<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Membership Agreement — {{ $member->full_name }} ({{ $member->member_code }})</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #0f172a;
            background-color: #f8fafc;
            line-height: 1.5;
            font-size: 12px;
            padding: 24px;
        }
        .container {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 36px 40px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 22px;
            padding-bottom: 24px;
            margin-bottom: 24px;
            border-bottom: 1px solid #cbd5e1;
            break-inside: avoid;
        }
        .gym-identity { display: flex; align-items: flex-start; gap: 16px; flex: 1; min-width: 0; }
        .gym-logo-frame { width: 104px; height: 104px; flex-shrink: 0; padding: 8px; border-radius: 16px; background: #0b1222; border: 1px solid #263047; box-shadow: 0 3px 6px #0f172a18; }
        .gym-logo { width: 100%; height: 100%; object-fit: contain; border-radius: 8px; }
        .gym-monogram { display: grid; place-items: center; height: 100%; font-size: 34px; font-weight: 800; color: #fbbf24; }
        .gym-details { min-width: 0; }
        .gym-details h2 { font-size: 23px; font-weight: 800; line-height: 1.2; letter-spacing: -.7px; text-transform: uppercase; overflow-wrap: anywhere; margin: 2px 0 8px; }
        .gym-address { font-size: 12px; color: #475569; line-height: 1.6; white-space: pre-line; }
        .gym-contacts { display: flex; flex-wrap: wrap; gap: 3px 12px; margin-top: 5px; font-size: 11px; font-weight: 600; overflow-wrap: anywhere; }
        .contact-label { color: #64748b; font-weight: 400; }
        .gym-website { margin-top: 3px; font-size: 11px; overflow-wrap: anywhere; }
        .document-meta { flex: 0 0 190px; text-align: right; min-width: 0; }
        .document-type { display: inline-flex; align-items: center; gap: 7px; padding: 7px 11px; border-radius: 999px; background: #0b1222; color: white; font-weight: 800; font-size: 10px; text-transform: uppercase; }
        .document-type>span { width: 7px; height: 7px; background: #fbbf24; border-radius: 50%; }
        .document-date { margin-top: 9px; font-size: 11px; font-weight: 700; }
        .document-reference { display: inline-block; max-width: 100%; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 5px; padding: 5px 7px; font-family: 'JetBrains Mono', monospace; font-size: 9px; overflow-wrap: anywhere; margin-top: 7px; }
        .document-status { display: inline-block; margin-top: 8px; border: 1px solid #fcd34d; border-radius: 999px; padding: 3px 10px; background: #fffbeb; color: #92400e; font-size: 10px; font-weight: 700; }
        @media screen and (max-width: 640px) {
            body { padding: 12px; }
            .container { padding: 24px 20px; }
            .header { flex-direction: column; gap: 18px; }
            .gym-logo-frame { width: 80px; height: 80px; border-radius: 12px; }
            .gym-details h2 { font-size: 20px; }
            .document-meta { flex-basis: auto; text-align: left; width: 100%; border-top: 1px solid #e2e8f0; padding-top: 14px; }
            .document-reference { display: block; width: fit-content; }
            .no-print-bar { gap: 12px; flex-wrap: wrap; }
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge-verified {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .section-title {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
            margin-top: 24px;
            margin-bottom: 12px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .info-item label {
            display: block;
            font-size: 10px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 2px;
        }
        .info-item span {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
        }
        .clause-list {
            list-style: none;
            margin-bottom: 12px;
        }
        .clause-list li {
            position: relative;
            padding-left: 18px;
            margin-bottom: 8px;
            font-size: 11px;
            color: #334155;
            line-height: 1.6;
        }
        .clause-list li::before {
            content: "•";
            position: absolute;
            left: 4px;
            color: #4f46e5;
            font-weight: bold;
        }
        .terrace-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 14px 18px;
            margin-top: 14px;
            margin-bottom: 14px;
        }
        .terrace-box h4 {
            color: #92400e;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }
        .terrace-box p {
            color: #78350f;
            font-size: 11px;
            line-height: 1.6;
        }
        .signature-box {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
        }
        .sig-image-container {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #ffffff;
            padding: 8px;
            max-width: 260px;
        }
        .sig-image {
            max-height: 70px;
            max-width: 100%;
            display: block;
        }
        .audit-table {
            width: 100%;
            font-size: 10px;
            border-collapse: collapse;
            margin-top: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .audit-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #edf2f7;
            font-family: 'JetBrains Mono', monospace;
        }
        .audit-table td:first-child {
            color: #64748b;
            font-weight: 600;
            width: 30%;
        }
        .no-print-bar {
            max-width: 820px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #4f46e5;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
        }
        .document-footer { margin-top: 32px; padding-top: 20px; border-top: 2px solid #0f172a; break-inside: avoid; }
        .footer-business strong { font-size: 13px; font-weight: 800; letter-spacing: .02em; }
        .footer-business p { margin-top: 5px; color: #475569; font-size: 11px; white-space: pre-line; }
        .footer-contacts { display: flex; flex-wrap: wrap; gap: 4px 18px; margin-top: 7px; font-size: 10px; color: #475569; overflow-wrap: anywhere; }
        .footer-record { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 6px 14px; border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 16px; font-size: 9px; color: #64748b; }
        .footer-reference { font-family: 'JetBrains Mono', monospace; overflow-wrap: anywhere; }
        .printed-terms { margin-bottom:22px; }
        .printed-terms-heading { padding:11px 13px; background:#f1f3f4; border-left:3px solid #a48a5a; font-size:12px; font-weight:700; margin-bottom:12px; break-after:avoid; }
        .printed-terms-heading span { color:#7c6843; margin-right:8px; }
        .printed-terms .clause-list li { padding:0 0 9px 18px; margin-bottom:9px; line-height:1.8; border-bottom:1px solid #eef0f1; break-inside:avoid; }
        .printed-terms .clause-list li::before { color:#a48a5a; }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .container {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print-bar {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
    
        @page { size: A4 portrait; margin: 14mm 14mm 16mm; }
        .container, .header, .info-grid, .signature-box, .signature-box > div, .info-item { min-width:0; }
        .container { overflow-wrap:anywhere; }
        .clause-list li { white-space:pre-wrap; }
        .audit-table { table-layout:fixed; overflow-wrap:anywhere; }
        .signature-box { gap:18px; }
        .signature-box > div:first-child { flex:1; }
        .signature-box > div:last-child { flex:0 1 32%; }
        .btn-print { background:#252b33; min-height:44px; }
        @media screen and (max-width:640px) {
            body { padding:10px; }
            .container { padding:16px 12px; border-radius:8px; }
            .gym-identity { gap:10px; width:100%; }
            .gym-logo-frame { width:58px; height:58px; padding:5px; }
            .gym-details h2 { font-size:18px; }
            .info-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; padding:10px; }
            .signature-box { flex-direction:column; align-items:stretch; }
            .signature-box > div { width:100%; max-width:100%!important; }
            .sig-image-container { margin-left:auto; }
            .no-print-bar { align-items:stretch; }
            .no-print-bar button { width:100%; }
            .clause-list li { font-size:13px; line-height:1.65; }
            .audit-table td { padding:5px; }
        }
        @media screen and (max-width:360px) { .info-grid { grid-template-columns:minmax(0,1fr); } }
        @media print {
            html,body { width:auto; height:auto; margin:0; padding:0; background:#fff; color:#17202b; font-size:10pt; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
            .container { width:100%; max-width:none; margin:0; padding:0; border:0; border-radius:0; box-shadow:none; }
            .header { gap:12px; padding-bottom:12px; margin-bottom:12px; }
            .gym-logo-frame { width:66px; height:66px; border-radius:6px; box-shadow:none; }
            .gym-details h2 { font-size:17pt; }
            .document-meta { flex-basis:140px; }
            .section-title,.printed-terms-heading { break-after:avoid; page-break-after:avoid; }
            .section-title { margin-top:16px; margin-bottom:8px; }
            .info-grid { padding:10px; gap:8px 12px; break-inside:avoid; }
            .printed-terms { break-inside:auto; margin-bottom:12px; }
            .printed-terms .clause-list li { font-size:10pt; line-height:1.55; margin-bottom:5px; padding-bottom:5px; break-inside:avoid; orphans:2; widows:2; }
            table { max-width:100%; }
            thead { display:table-header-group; }
            tr { break-inside:avoid; page-break-inside:avoid; }
            .signature-box { break-inside:avoid; page-break-inside:avoid; margin-top:16px; padding-top:12px; }
            .sig-image { max-height:65px; }
            .document-footer { margin-top:16px; padding-top:12px; }
            .page-break { break-before:auto; page-break-before:auto; }
            .terrace-box { break-inside:avoid; }
            p,li,td { orphans:2; widows:2; }
        }
    </style>
</head>
<body>

    <!-- Print Action Bar -->
    <div class="no-print-bar">
        <span style="font-size: 12px; color: #64748b; font-weight: 600;">
            Official Digital Record &bull; Reference: <code>{{ $agreement->signature_reference ?? 'GDH-AGR' }}</code>
        </span>
        <button type="button" class="btn-print" onclick="window.print()">Print / Save A4 PDF</button>
    </div>

    <div class="container">
        @php
            $tenantSettings = $tenant->settings ?? [];
            $invoiceSettings = $tenantSettings['invoice'] ?? [];
            $gymName = ($invoiceSettings['gym_name'] ?? '') ?: $tenant->name;
            $gymAddress = ($invoiceSettings['address'] ?? '') ?: (($tenantSettings['address'] ?? '') ?: ($branch?->address ?: $tenant->mainBranch?->address));
            $gymPhone = ($invoiceSettings['phone'] ?? '') ?: ($tenant->phone ?: $branch?->phone);
            $gymEmail = ($invoiceSettings['email'] ?? '') ?: ($tenant->email ?: $branch?->email);
            $gymWebsite = ($invoiceSettings['website'] ?? '') ?: (($tenantSettings['website'] ?? '') ?: $tenant->website);
        @endphp
        <header class="header">
            <div class="gym-identity">
                <div class="gym-logo-frame">
                    @if($tenant->logo_url)
                        <img src="{{ $tenant->logo_url }}" alt="{{ $gymName }} logo" class="gym-logo">
                    @else
                        <span class="gym-monogram">{{ mb_strtoupper(mb_substr($gymName, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="gym-details">
                    <h2>{{ $gymName }}</h2>
                    @if($gymAddress)<p class="gym-address">{{ $gymAddress }}</p>@endif
                    <div class="gym-contacts">
                        @if($gymPhone)<span><span class="contact-label">Phone:</span> {{ $gymPhone }}</span>@endif
                        @if($gymEmail)<span><span class="contact-label">Email:</span> {{ $gymEmail }}</span>@endif
                    </div>
                    @if($gymWebsite)<p class="gym-website"><span class="contact-label">Website:</span> {{ $gymWebsite }}</p>@endif
                </div>
            </div>
            <div class="document-meta">
                <span class="document-type"><span aria-hidden="true"></span> Member agreement</span>
                <p class="document-date"><span class="contact-label">{{ $agreement->signed_at ? 'Signed:' : 'Created:' }}</span> {{ ($agreement->signed_at ?? $agreement->created_at)?->format('d M Y') ?? 'Not recorded' }}</p>
                @if($agreement->signature_reference)<p class="document-reference">{{ $agreement->signature_reference }}</p>@endif
                <span class="document-status">
                    @if($agreement->status === \App\Models\MemberAgreement::STATUS_MEDICAL_REVIEW)
                        Health review pending
                    @elseif($agreement->signed_at)
                        Signed
                    @else
                        Awaiting signature
                    @endif
                </span>
            </div>
        </header>

        <h1 style="font-size: 16px; font-weight: 900; text-align: center; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
            {{ $template->title ?? "Member Agreement & Health Declaration" }}
        </h1>

        <!-- Member Identity & Membership Details -->
        <div class="section-title">1. Member Profile &amp; Enrollment Details</div>
        <div class="info-grid">
            <div class="info-item">
                <label>Member Full Name</label>
                <span>{{ $member->full_name }}</span>
            </div>
            <div class="info-item">
                <label>Gym Member ID</label>
                <span style="font-family: 'JetBrains Mono', monospace; color: #4f46e5;">{{ $member->member_code }}</span>
            </div>
            <div class="info-item">
                <label>Registered Phone</label>
                <span>{{ $member->phone }}</span>
            </div>
            <div class="info-item">
                <label>Membership Package</label>
                <span>{{ $activeMembership?->plan?->name ?? 'Standard Membership' }}</span>
            </div>
            <div class="info-item">
                <label>Contract Tenure</label>
                <span>
                    {{ $activeMembership?->start_date ? $activeMembership->start_date->format('d M Y') : now()->format('d M Y') }} 
                    to 
                    {{ $activeMembership?->end_date ? $activeMembership->end_date->format('d M Y') : 'Ongoing' }}
                </span>
            </div>
            <div class="info-item">
                <label>Facility Branch</label>
                <span>{{ $branch->name ?? $tenant->name }}</span>
            </div>
            <div class="info-item"><label>Date of birth</label><span>{{ $member->dob?->format('d M Y') ?? 'Not provided' }}</span></div>
            <div class="info-item"><label>Email</label><span>{{ $member->email ?: 'Not provided' }}</span></div>
            <div class="info-item"><label>Residential address</label><span>{{ $member->address ?: 'Not provided' }}</span></div>
            <div class="info-item"><label>Emergency contact</label><span>{{ $member->emergency_contact_name ?: 'Not provided' }}@if($member->emergency_contact_phone)<br>{{ $member->emergency_contact_phone }}@endif</span></div>
        </div>

        <!-- Health Declaration (PAR-Q) -->
        <div class="section-title">2. Physical Readiness &amp; Health Declaration (PAR-Q)</div>
        <p style="font-size: 11px; color: #334155; margin-bottom: 10px; line-height: 1.5;">
            The member has provided the following certified answers regarding physical readiness and medical conditions:
        </p>

        <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 14px;">
            <thead>
                <tr style="background: #f1f5f9; text-align: left; font-size: 10px; text-transform: uppercase;">
                    <th style="padding: 6px 10px; border: 1px solid #cbd5e1; width: 75%;">Health Condition / Inherent Risk Category</th>
                    <th style="padding: 6px 10px; border: 1px solid #cbd5e1; width: 25%; text-align: center;">Member Answer</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $qList = $template->health_questions ?? \App\Models\TenantAgreementTemplate::getDefaultHealthQuestions();
                    $answers = $agreement->health_declaration_answers ?? [];
                @endphp
                @foreach($qList as $qIdx => $hq)
                    @php
                        $qKey = $hq['id'] ?? ('q_' . $qIdx);
                        $ans = $answers[$qKey]['answer'] ?? 'no';
                        $exp = $answers[$qKey]['explanation'] ?? null;
                    @endphp
                    <tr>
                        <td style="padding: 6px 10px; border: 1px solid #e2e8f0;">
                            <strong>{{ $hq['question'] }}</strong>
                            @if(!empty($exp))
                                <div style="color: #92400e; font-size: 10px; margin-top: 2px;">
                                    <strong>Disclosed details:</strong> {{ $exp }}
                                </div>
                            @endif
                        </td>
                        <td style="padding: 6px 10px; border: 1px solid #e2e8f0; text-align: center; font-weight: bold; {{ $ans === 'yes' ? 'color: #b45309; background: #fffbeb;' : 'color: #047857;' }}">
                            {{ strtoupper($ans) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Health Undertaking -->
        <div style="background: #f8fafc; border-left: 3px solid #4f46e5; padding: 10px 14px; font-size: 10.5px; color: #334155; margin-bottom: 16px; line-height: 1.5;">
            <strong>Health Declaration &amp; Undertaking Accepted:</strong><br>
            &ldquo;I confirm that the health information provided by me is accurate to the best of my knowledge. I understand that gym workouts and physical fitness activities involve inherent risks, including injury, muscle strain, falls, and physical discomfort. I agree to exercise within my physical limits, follow trainer instructions, use equipment responsibly, and immediately report any medical concerns or discomfort. I understand and voluntarily accept the ordinary inherent risks associated with fitness activities, subject to the Gym&rsquo;s legal responsibilities.&rdquo;
        </div>

        @if($agreement->reviewed_at)
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 8px 12px; font-size: 10px; color: #065f46; margin-bottom: 14px;">
                <strong>Medical Review Completed:</strong> Approved by {{ $agreement->reviewedBy?->name ?? 'Gym Medical Officer' }} on {{ $agreement->reviewed_at->format('d M Y') }}.
                @if($agreement->medical_review_notes)
                    <br><em>Notes:</em> {{ $agreement->medical_review_notes }}
                @endif
            </div>
        @endif

        <!-- Terms & Conditions Sections -->
        <div class="page-break"></div>
        <div class="section-title">3. Membership Terms &amp; Conditions</div>

        @php
            $sections = $template->terms_sections ?? \App\Models\TenantAgreementTemplate::getDefaultTermsSections($agreement->terrace_rules_included);
        @endphp
        @foreach($sections as $sec)
            <div class="printed-terms">
                <h4 class="printed-terms-heading">
                    <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span> {{ mb_convert_case($sec['title'], MB_CASE_TITLE) }}
                </h4>
                <ul class="clause-list">
                    @foreach($sec['clauses'] as $clause)
                        <li>{{ $clause }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        @if($agreement->terrace_rules_included)
            <!-- Open Terrace Health & Safety Declaration -->
            <div class="terrace-box">
                <h4>Open Terrace Health &amp; Safety Declaration &bull; Strict Management Access</h4>
                <p>
                    &ldquo;I acknowledge that access to the Open Terrace Workout Area is strictly subject to prior permission from Gym Management. I agree not to enter or use the terrace without authorization and to comply with all safety instructions and access restrictions. I understand the inherent risks associated with elevated outdoor areas and accept responsibility for my own unauthorized actions, misconduct, or violation of safety rules, subject to applicable law.&rdquo;
                </p>
            </div>
        @endif

        <!-- Electronic Signature & Audit Certificate -->
        <div class="signature-box">
            <div style="max-width: 480px;">
                <div style="font-size: 11px; font-weight: 700; color: #0f172a; margin-bottom: 2px;">
                    Member Legal Electronic Signature
                </div>
                <div style="font-size: 10px; color: #64748b; line-height: 1.4;">
                    Signed electronically after profile confirmation and agreement review.
                </div>

                <table class="audit-table">
                    <tr>
                        <td>Signer Identity</td>
                        <td>{{ $member->full_name }} (Member ID: {{ $member->member_code }})</td>
                    </tr>
                    <tr>
                        <td>Signing Timestamp</td>
                        <td>{{ $agreement->signed_at ? $agreement->signed_at->format('Y-m-d H:i:s T') : '—' }}</td>
                    </tr>
                    <tr>
                        <td>Audit Transaction Ref</td>
                        <td>{{ $agreement->signature_reference }}</td>
                    </tr>
                    <tr>
                        <td>Signer IP Address</td>
                        <td>{{ $agreement->signer_ip ?? '127.0.0.1' }}</td>
                    </tr>
                    <tr>
                        <td>Audit SHA-256 Hash</td>
                        <td style="word-break: break-all; font-size: 9px;">{{ $agreement->signature_hash ?? '—' }}</td>
                    </tr>
                </table>
            </div>

            <div style="text-align: right;">
                <div class="sig-image-container">
                    @if(!empty($agreement->signature_data))
                        <img src="{{ $agreement->signature_data }}" alt="Member Signature" class="sig-image">
                    @else
                        <div style="height: 60px; line-height: 60px; font-style: italic; color: #94a3b8; text-align: center;">Digitally Signed</div>
                    @endif
                </div>
                <div style="font-size: 10px; font-weight: bold; color: #0f172a; margin-top: 4px;">
                    {{ $member->full_name }}
                </div>
                <div style="font-size: 9px; color: #64748b;">
                    Digitally Authorized Member
                </div>
            </div>
        </div>

        <footer class="document-footer">
            <div class="footer-business">
                <strong>{{ $gymName }}</strong>
                @if($gymAddress)<p>{{ $gymAddress }}</p>@endif
                <div class="footer-contacts">
                    @if($gymPhone)<span>Phone: {{ $gymPhone }}</span>@endif
                    @if($gymEmail)<span>Email: {{ $gymEmail }}</span>@endif
                    @if($gymWebsite)<span>{{ $gymWebsite }}</span>@endif
                </div>
            </div>
            <div class="footer-record">
                <span>Member agreement &amp; health declaration</span>
                @if($agreement->signature_reference)<span class="footer-reference">{{ $agreement->signature_reference }}</span>@endif
                <span>Prepared with <strong>GymDeskHub</strong></span>
            </div>
        </footer>
    </div>

</body>
</html>
