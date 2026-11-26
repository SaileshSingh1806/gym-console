<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payslip — {{ $payslip->payslip_number }} &middot; {{ $payslip->user?->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:Inter,Arial,sans-serif; background:#edf0f3; color:#182435; font-size:12px; line-height:1.6; padding:28px 20px 48px; }
        button,a { -webkit-tap-highlight-color:transparent; }
        :focus-visible { outline:2px solid #536e91; outline-offset:4px; }
        .action-bar { max-width:900px; margin:0 auto 18px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
        .action-btn { display:inline-flex; align-items:center; justify-content:center; min-height:44px; padding:10px 16px; border-radius:6px; font:500 12px Inter,Arial,sans-serif; text-decoration:none; cursor:pointer; border:1px solid #cdd5de; background:white; color:#354459; }
        .btn-primary { background:#1c2b40; border-color:#1c2b40; color:white; }
        .btn-primary:hover { background:#2e425d; }
        .btn-secondary:hover,.btn-whatsapp:hover { background:#f6f8fa; }
        .payslip-sheet { max-width:900px; margin:auto; background:white; border:1px solid #dce1e7; border-radius:8px; padding:40px 44px 24px; box-shadow:0 6px 24px #1c2b4006; }
        .gym-header { display:flex; justify-content:space-between; align-items:flex-start; gap:24px; padding-bottom:25px; border-bottom:1px solid #dce1e7; }
        .gym-identity { display:flex; gap:16px; align-items:flex-start; min-width:0; flex:1; }
        .gym-logo-box { width:88px; height:88px; border:0; border-radius:0; display:grid; place-items:center; flex-shrink:0; background:transparent; font-size:26px; font-weight:600; }
        .gym-logo-box img { width:100%; height:100%; object-fit:contain; display:block; }
        .gym-brand { min-width:0; }
        .gym-brand h1 { font-size:20px; line-height:1.3; letter-spacing:-.5px; font-weight:700; }
        .gym-brand p { font-size:11px; color:#667286; margin-top:4px; overflow-wrap:anywhere; }
        .document-title-box { text-align:right; flex-shrink:0; }
        .document-badge { display:inline-block; padding:3px 9px; border-radius:4px; font-size:10px; font-weight:600; margin-bottom:8px; }
        .badge-paid { color:#276549; background:#eef6f0; }
        .badge-approved { color:#355979; background:#eef3f9; }
        .badge-draft { color:#856224; background:#fbf5e9; }
        .badge-cancelled { color:#9b3838; background:#fbefef; }
        .payslip-number { font-size:11px; font-weight:600; letter-spacing:.04em; }
        .payslip-period { color:#667286; font-size:11px; margin-top:3px; }
        .document-heading { display:flex; justify-content:space-between; align-items:baseline; gap:12px; padding:24px 0 18px; }
        .document-heading h2 { font-size:26px; font-weight:600; letter-spacing:-.8px; line-height:1.2; }
        .document-heading p { font-size:12px; color:#667286; }
        .info-matrix { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:20px; padding:20px 0 24px; border-top:1px solid #e5e9ee; margin-bottom:8px; }
        .attendance-row { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; padding:16px 0; margin-bottom:24px; border-top:1px solid #e5e9ee; border-bottom:1px solid #e5e9ee; }
        .attendance-row .value { font-size:17px; font-variant-numeric:tabular-nums; }
        .info-item .label { display:block; font-size:10px; font-weight:500; color:#768093; margin-bottom:5px; }
        .info-item .value { display:block; font-size:12px; font-weight:500; line-height:1.65; overflow-wrap:anywhere; }
        .salary-table-container { border:1px solid #dce1e7; border-radius:6px; overflow:hidden; margin-bottom:20px; }
        .split-grid { display:grid; grid-template-columns:1fr 1fr; }
        .split-col { border-right:1px solid #dce1e7; }
        .split-col:last-child { border-right:0; }
        .salary-table { width:100%; border-collapse:collapse; }
        .salary-table th { padding:12px 16px; text-align:left; background:#f3f5f8; border-bottom:1px solid #dce1e7; color:#4d5c70; font-size:10px; font-weight:600; }
        .salary-table td { padding:12px 16px; border-bottom:1px solid #edf0f3; font-size:12px; vertical-align:top; }
        .salary-table .amount { text-align:right; white-space:nowrap; font-variant-numeric:tabular-nums; }
        .subtotal-row { background:#fafbfc; border-top:1px solid #dce1e7; }
        .subtotal-row td { font-weight:600!important; font-size:11px; }
        .net-salary-banner { display:flex; justify-content:space-between; align-items:center; gap:24px; padding:21px 24px; background:#f1f4f8; border-left:3px solid #263d5a; margin-bottom:24px; break-inside:avoid; }
        .net-salary-banner .title { font-size:12px; font-weight:600; }
        .net-salary-banner .words { color:#667286; font-size:11px; margin-top:5px; }
        .net-salary-banner .amount { font-size:30px; font-weight:600; letter-spacing:-1px; white-space:nowrap; font-variant-numeric:tabular-nums; }
        .settlement-card { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:20px; padding:0 0 20px; margin-bottom:20px; border-bottom:1px solid #e5e9ee; break-inside:avoid; }
        .signatures-grid { display:flex; justify-content:space-between; align-items:flex-end; gap:20px; padding-top:14px; margin-top:18px; break-inside:avoid; }
        .signature-box { width:170px; text-align:left; }
        .signature-line { height:32px; border-bottom:1px solid #a8b3c0; margin-bottom:8px; }
        .authorized-signatory { text-align:center; }
        .signature-artwork { position:relative; display:grid; place-items:center; height:90px; margin-bottom:8px; }
        .signature-artwork img { object-fit:contain; max-width:100%; }
        .seal-image { width:80px; height:80px; }
        .signature-image { position:absolute; width:140px; height:65px; mix-blend-mode:multiply; }
        .signatory-name { font-size:10px; color:#667286; margin-top:3px; }
        .signature-title { font-size:10px; color:#667286; }
        .document-footer { margin-top:24px; padding-top:12px; border-top:1px solid #e5e9ee; display:flex; justify-content:space-between; gap:12px; color:#7b8594; font-size:9px; }
        @media screen and (max-width:680px) {
            body { padding:16px 10px; }
            .payslip-sheet { padding:24px 20px; }
            .gym-header { flex-direction:column; gap:18px; }
            .document-title-box { display:flex; flex-wrap:wrap; align-items:center; gap:10px; text-align:left; }
            .document-badge { margin:0; }
            .info-matrix,.attendance-row { grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
            .split-grid { grid-template-columns:1fr; }
            .split-col { border-right:0; border-bottom:1px solid #dce1e7; }
            .split-col:last-child { border-bottom:0; }
            .net-salary-banner { align-items:flex-start; flex-direction:column; gap:10px; padding:18px; }
            .net-salary-banner .amount { font-size:28px; }
            .settlement-card { grid-template-columns:1fr 1fr; }
            .signatures-grid { flex-wrap:wrap; }
            .signature-box { width:43%; }
            .signature-center { order:3; width:100%; max-width:none!important; text-align:left!important; }
            .document-heading { align-items:flex-start; flex-direction:column; gap:6px; }
            .action-bar>div { display:flex; flex-wrap:wrap; gap:8px; }
        }
        @page { size:A4 portrait; margin:0; }
        @media print {
            body { background:white; padding:0; font-size:11px; }
            .action-bar { display:none!important; }
            .payslip-sheet { width:210mm; max-width:100%; min-height:296mm; padding:14mm 12mm 12mm; border:0; border-radius:0; box-shadow:none; display:flex; flex-direction:column; }
            .gym-logo-box { width:24mm; height:24mm; }
            .signatures-grid { margin-top:auto; padding-top:16mm; }
            .attendance-row,.settlement-card,.net-salary-banner { flex-shrink:0; break-inside:avoid; }
            .gym-header,.info-matrix,.salary-table tr,.document-footer { break-inside:avoid; }
            .salary-table th,.net-salary-banner,.document-badge { print-color-adjust:exact; -webkit-print-color-adjust:exact; }
            .gym-brand h1 { font-size:20px; }
            .info-matrix { gap:14px; }
            .salary-table td { padding:9px 12px; }
        }
    </style>
</head>
<body>
    @php
        $invoiceSettings = $tenant->settings['invoice'] ?? [];
        $sealUrl = $invoiceSettings['seal_url'] ?? null;
        $signatureUrl = $invoiceSettings['signature_url'] ?? null;
        $signatoryName = $invoiceSettings['signatory_name'] ?? null;
        $bankName = trim($payslip->bank_name ?: ($staffConfig['bank_name'] ?? ''));
        $accountNumber = trim($payslip->account_number ?: ($staffConfig['account_number'] ?? ''));
        $ifscCode = trim($payslip->ifsc_code ?: ($staffConfig['ifsc_code'] ?? ''));
        $upiId = trim($payslip->upi_id ?: ($staffConfig['upi_id'] ?? ''));
        $hasBankDetails = $bankName !== '' || $accountNumber !== '' || $ifscCode !== '';
    @endphp

    <!-- Top Action Bar (Hidden on Print) -->
    <div class="action-bar">
        <a href="{{ route('app.payroll.index', ['month' => $payslip->month, 'year' => $payslip->year]) }}" class="action-btn btn-secondary">
            &larr; Back to Payroll
        </a>

        <div style="display:flex; gap:10px;">
            <a href="{{ $payslip->whatsapp_share_url }}" target="_blank" rel="noopener noreferrer" class="action-btn btn-whatsapp">
                <span>Share via WhatsApp</span>
            </a>
            <button type="button" onclick="window.print()" class="action-btn btn-primary">
                <span>Print / Save PDF</span>
            </button>
        </div>
    </div>

    <!-- Printable Sheet -->
    <article class="payslip-sheet">
        <!-- Gym Header -->
        <header class="gym-header">
            <div class="gym-identity">
                <div class="gym-logo-box">
                    @if($tenant->logo_url)
                        <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}">
                    @else
                        {{ strtoupper(substr($tenant->name, 0, 1)) }}
                    @endif
                </div>
                <div class="gym-brand">
                    <h1>{{ $tenant->name }}</h1>
                    <p>
                        {{ $payslip->branch?->name ?? 'Main Facility' }}
                        @if($tenant->address) &bull; {{ $tenant->address }} @endif
                    </p>
                    <p>
                        @if($tenant->phone) Phone: {{ $tenant->phone }} @endif
                        @if($tenant->email) &bull; {{ $tenant->email }} @endif
                        @if($tenant->gstin) &bull; GSTIN: {{ $tenant->gstin }} @endif
                    </p>
                </div>
            </div>

            <div class="document-title-box">
                @if($payslip->isPaid())
                    <span class="document-badge badge-paid">Paid</span>
                @elseif($payslip->isApproved())
                    <span class="document-badge badge-approved">Approved</span>
                @elseif($payslip->isCancelled())
                    <span class="document-badge badge-cancelled">Cancelled</span>
                @else
                    <span class="document-badge badge-draft">Draft</span>
                @endif
                <div class="payslip-number">{{ $payslip->payslip_number }}</div>
                <div class="payslip-period">{{ $payslip->formatted_period }}</div>
            </div>
        </header>

        <div class="document-heading"><h2>Salary statement</h2><p>Pay period &middot; {{ $payslip->formatted_period }}</p></div>
        <!-- Employee Summary Matrix -->
        <section class="info-matrix">
            <div class="info-item">
                <span class="label">Employee Name</span>
                <span class="value">{{ $payslip->user?->name }}</span>
            </div>
            <div class="info-item">
                <span class="label">Designation / Role</span>
                <span class="value">{{ $staffConfig['designation'] ?? 'Fitness Staff' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Pay Period</span>
                <span class="value">{{ $payslip->pay_period_start->format('d M') }} &ndash; {{ $payslip->pay_period_end->format('d M Y') }}</span>
            </div>
            <div class="info-item">
                <span class="label">Mobile Number</span>
                <span class="value">{{ $payslip->user?->phone ?? 'Not provided' }}</span>
            </div>
            @if($hasBankDetails)
                <div class="info-item">
                    <span class="label">Bank Name</span>
                    <span class="value">{{ $bankName ?: 'Not provided' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Account Number</span>
                    <span class="value">{{ $accountNumber !== '' ? str_repeat('*', max(0, strlen($accountNumber) - 4)) . substr($accountNumber, -4) : 'Not provided' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">IFSC Code</span>
                    <span class="value">{{ $ifscCode ?: 'Not provided' }}</span>
                </div>
            @elseif($upiId !== '')
                <div class="info-item">
                    <span class="label">UPI Payment ID</span>
                    <span class="value">{{ $upiId }}</span>
                </div>
            @endif
        </section>
        <section class="attendance-row" aria-label="Attendance summary">
            <div class="info-item"><span class="label">Working days</span><span class="value">{{ $payslip->working_days }}</span></div>
            <div class="info-item"><span class="label">Days present</span><span class="value">{{ $payslip->present_days }}</span></div>
            <div class="info-item"><span class="label">Leave days</span><span class="value">{{ $payslip->leaves_taken }}</span></div>
            <div class="info-item"><span class="label">Late marks</span><span class="value">{{ $payslip->late_days }}</span></div>
        </section>

        <!-- Earnings vs Deductions Table -->
        <div class="salary-table-container">
            <div class="split-grid">
                <!-- Earnings Column -->
                <div class="split-col">
                    <table class="salary-table">
                        <thead>
                            <tr>
                                <th>Earnings</th>
                                <th class="amount">INR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Basic Salary</td>
                                <td class="amount">{{ number_format((float)$payslip->basic_salary, 2) }}</td>
                            </tr>
                            @forelse($payslip->allowances ?? [] as $allw)
                                <tr>
                                    <td>{{ $allw['name'] }}</td>
                                    <td class="amount">{{ number_format((float)$allw['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td style="color:#94a3b8; font-style:italic;">No additional allowances</td>
                                    <td class="amount" style="color:#94a3b8;">0.00</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="subtotal-row">
                                <td style="font-weight:800;">Gross earnings (A)</td>
                                <td class="amount" style="font-weight:900;">₹{{ number_format((float)$payslip->gross_salary, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Deductions Column -->
                <div class="split-col">
                    <table class="salary-table">
                        <thead>
                            <tr>
                                <th>Deductions</th>
                                <th class="amount">INR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payslip->deductions ?? [] as $ded)
                                <tr>
                                    <td>{{ $ded['name'] }}</td>
                                    <td class="amount">{{ number_format((float)$ded['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td style="color:#94a3b8; font-style:italic;">No salary deductions applied</td>
                                    <td class="amount" style="color:#94a3b8;">0.00</td>
                                </tr>
                            @endforelse
                            @if(empty($payslip->deductions))
                                <tr><td colspan="2" style="height:32px;"></td></tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="subtotal-row">
                                <td style="font-weight:800;">Total deductions (B)</td>
                                <td class="amount" style="font-weight:900; color:#b91c1c;">₹{{ number_format((float)$payslip->total_deductions, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Net Salary Highlight Banner -->
        <div class="net-salary-banner">
            <div>
                <span class="title">Net pay (A &minus; B)</span>
                <div class="words">{{ $payslip->net_salary_in_words }}</div>
            </div>
            <div class="amount">
                ₹{{ number_format((float)$payslip->net_salary, 2) }}
            </div>
        </div>

        <!-- Payment Settlement Details -->
        @if($payslip->isPaid())
            <div class="settlement-card">
                <div class="info-item">
                    <span class="label">Settlement Mode</span>
                    <span class="value">{{ str_replace('_', ' ', $payslip->payment_method) }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Payment Date</span>
                    <span class="value">{{ $payslip->paid_at ? $payslip->paid_at->format('d M Y, h:i A') : 'Completed' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">UTR / Transaction Ref</span>
                    <span class="value font-mono">{{ $payslip->transaction_reference ?: 'N/A' }}</span>
                </div>
            </div>
        @endif

        @if($payslip->notes)
            <div style="margin-bottom:24px; padding:12px 16px; background:#f8fafc; border-left:3px solid #64748b; font-size:12px; color:#475569;">
                <strong>Payroll Notes:</strong> {{ $payslip->notes }}
            </div>
        @endif

        <!-- Signatures & Authority -->
        <footer class="signatures-grid">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Employee Signature</div>
            </div>

            <div class="signature-center" style="text-align:center; font-size:11px; color:#64748b; max-width:280px;">
                <p style="font-weight:700; color:#334155; text-transform:uppercase; letter-spacing:0.5px;">{{ $tenant->name }}</p>
            </div>

            <div class="signature-box authorized-signatory">
                @if($sealUrl || $signatureUrl)
                    <div class="signature-artwork">
                        @if($sealUrl)
                            <img src="{{ $sealUrl }}" alt="Gym stamp" class="seal-image">
                        @endif
                        @if($signatureUrl)
                            <img src="{{ $signatureUrl }}" alt="Authorized signature" class="signature-image">
                        @endif
                    </div>
                @else
                    <div class="signature-line"></div>
                @endif
                <div class="signature-title">Authorized Signatory</div>
                @if($signatoryName)
                    <div class="signatory-name">{{ $signatoryName }}</div>
                @endif
            </div>
        </footer>
        <div class="document-footer"><span>{{ $payslip->payslip_number }} &middot; {{ $payslip->formatted_period }}</span></div>
    </article>

</body>
</html>
