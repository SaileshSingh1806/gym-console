<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tax Invoice - {{ $payment->receipt_number ?? ('ARM-' . $payment->id) }} - {{ $tenant->name ?? 'Gym Console' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        *, *:before, *:after {
            box-sizing: border-box;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        @page {
            size: A4 portrait;
            margin: 0;
        }
        @media print {
            html, body {
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                overflow: hidden !important;
            }
            .no-print {
                display: none !important;
            }
            .invoice-card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                max-width: 100% !important;
                width: 100% !important;
                height: 280mm !important;
                max-height: 282mm !important;
                margin: 5mm auto !important;
                padding: 12px 18px !important;
                border-radius: 8px !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
                page-break-before: avoid !important;
                break-before: avoid !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen py-4 sm:py-6 px-3 sm:px-6 flex flex-col items-center antialiased">

    @php
        $tenantSettings = $tenant->settings ?? [];
        $invSettings = $tenantSettings['invoice'] ?? [];
        $currency = $tenant->currency_symbol ?? 'Rs. ';
        $logoUrl = $tenant->logo_url ?? ($platformSettings['logo_url'] ?? null);

        // Header details
        $gymName = !empty($invSettings['gym_name']) ? $invSettings['gym_name'] : ($tenant->name ?? 'ARMOUR 24-7 GYM');
        $gymAddress = !empty($invSettings['address']) ? $invSettings['address'] : ($member->branch->address ?? ($tenant->address ?? '6th Floor, Shalin Square, Nr Hathijan Circle, Ahmedabad - 382445'));
        $gymPhone = !empty($invSettings['phone']) ? $invSettings['phone'] : ($tenant->phone ?? '+91 83065 30583');
        $gymEmail = !empty($invSettings['email']) ? $invSettings['email'] : ($tenant->email ?? 'management@armour247gym.com');
        $gymWeb = !empty($invSettings['website']) ? $invSettings['website'] : 'www.armour247gym.com';
        $invoiceTitle = !empty($invSettings['title']) ? $invSettings['title'] : 'TAX INVOICE';
        
        // Prefix & Receipt Number
        $prefix = !empty($invSettings['prefix']) ? $invSettings['prefix'] : '#ARM-';
        $rawNum = $payment->receipt_number ?? ($payment->invoice_number ?? str_pad($payment->id, 6, '0', STR_PAD_LEFT));
        $invoiceNo = str_starts_with($rawNum, '#') ? $rawNum : ($prefix . $rawNum);
        
        $invoiceDate = $payment->payment_date ? $payment->payment_date->format('d M Y') : $payment->created_at->format('d M Y');
        $statusText = !empty($invSettings['status_text']) ? $invSettings['status_text'] : 'PAID IN FULL';
        $verificationText = !empty($invSettings['verification_text']) ? $invSettings['verification_text'] : 'Verified & Recorded';

        // Plan Duration calculation
        $durationFormatted = '12 Months';
        if ($plan) {
            $durationFormatted = $plan->duration_value . ' ' . ucfirst($plan->duration_type ?? 'Months');
        } elseif ($membership && $membership->start_date && $membership->end_date) {
            $diffMonths = $membership->start_date->diffInMonths($membership->end_date);
            $durationFormatted = $diffMonths > 0 ? ($diffMonths . ' Months') : '1 Month';
        }

        $startDate = $membership && $membership->start_date ? $membership->start_date->format('d M Y') : $invoiceDate;
        $endDate = $membership && $membership->end_date ? $membership->end_date->format('d M Y') : '12 Oct 2027';
        $membershipStatus = ($membership && $membership->status === 'ACTIVE') ? 'Active Membership' : 'Active Membership';

        // Terms
        $defaultTerms = [
            "Fees once paid are strictly non-refundable and non-transferable under any circumstances.",
            "Membership is non-transferable and valid exclusively for the registered individual and specified tenure.",
            "Members are required to carry clean training footwear, workout towel, and follow gym etiquette at all times.",
            "Management reserves the right to adjust facility operating hours and enforce safety protocols.",
            "Any unpaid dues must be cleared on or before the specified due date to maintain uninterrupted facility access."
        ];
        if (!empty($invSettings['terms'])) {
            $rawTerms = array_filter(array_map('trim', explode("\n", $invSettings['terms'])));
            $termsList = array_map(function($t) {
                return preg_replace('/^\s*\d+[\.\)]\s*/', '', $t);
            }, $rawTerms);
        } else {
            $termsList = $defaultTerms;
        }

        // Footer & Tagline
        $thankYouText = !empty($invSettings['thank_you']) ? $invSettings['thank_you'] : ("Thank you for training with " . $gymName . "!");
        $helpline = !empty($invSettings['helpline']) ? $invSettings['helpline'] : $gymPhone;
        $footerEmail = !empty($invSettings['footer_email']) ? $invSettings['footer_email'] : $gymEmail;
        $footerWeb = !empty($invSettings['footer_web']) ? $invSettings['footer_web'] : $gymWeb;
        $tagline = !empty(trim($invSettings['tagline'] ?? '')) ? $invSettings['tagline'] : "BIGGER SPACE | BIGGER FACILITIES | STRONGER YOU";
        $sealText = !empty($invSettings['seal_text']) ? $invSettings['seal_text'] : "Authorized Signature / Seal";
        $sealUrl = $invSettings['seal_url'] ?? null;
        $signatureUrl = $invSettings['signature_url'] ?? null;
        $signatoryName = $invSettings['signatory_name'] ?? null;
    @endphp

    <!-- Top Action Toolbar -->
    <div class="no-print w-full max-w-3xl flex flex-wrap items-center justify-between gap-3 mb-4">
        <a href="{{ route('app.members.show', $member->id) }}" 
           class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-950 border border-slate-300 text-xs font-bold transition-all shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Member</span>
        </a>

        <div class="flex items-center gap-2.5">
            <button type="button" 
                    onclick="window.print()" 
                    class="px-4 py-2 rounded-xl bg-slate-950 hover:bg-slate-800 text-white font-black text-xs flex items-center gap-2 shadow-lg shadow-black/20 transition-all cursor-pointer">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Invoice</span>
            </button>

            <button type="button" 
                    onclick="window.print()" 
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/30 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Download PDF</span>
            </button>
        </div>
    </div>

    <!-- ==================== INVOICE A4 SHEET ==================== -->
    <div class="invoice-card relative w-full max-w-3xl bg-white text-slate-900 rounded-2xl shadow-xl p-4 sm:p-6 lg:p-7 border border-slate-200 flex flex-col justify-between min-h-0 sm:min-h-[1040px] print:min-h-[275mm] mx-auto">
        
        <!-- Faded Center Background Watermark -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.035] select-none z-0">
            @if(!empty($logoUrl))
                <img src="{{ $logoUrl }}" alt="" class="w-[320px] sm:w-[400px] h-[320px] sm:h-[400px] object-contain grayscale">
            @else
                <div class="text-center font-black tracking-widest text-6xl sm:text-8xl uppercase text-slate-900 rotate-[-12deg]">
                    {{ $gymName }}
                </div>
            @endif
        </div>

        <div class="relative z-10 flex flex-col justify-between h-full min-h-[inherit] flex-1">
            
            <!-- MAIN TOP CONTENT: Sections 1 through 5 -->
            <div class="space-y-3 sm:space-y-3.5">
            
            <!-- ══════════════════════════════════════════
                 1. HEADER: BRAND & INVOICE META
            ══════════════════════════════════════════ -->
            <div class="flex flex-col sm:flex-row items-start justify-between gap-3 pt-1 border-b border-slate-200 pb-3">
                <!-- Gym Brand (Left) -->
                <div class="flex items-start gap-3.5 max-w-lg">
                    @if(!empty($logoUrl))
                        <img src="{{ $logoUrl }}" alt="{{ $gymName }}" class="h-14 w-14 sm:h-16 sm:w-16 object-contain rounded-xl bg-slate-950 p-1 sm:p-1.5 border border-slate-800 shadow-sm shrink-0">
                    @else
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-slate-950 text-white flex flex-col items-center justify-center font-black text-xs tracking-tight shadow-sm shrink-0 p-1 text-center">
                            <span class="text-amber-400 text-xl">🏋️</span>
                            <span class="text-[8px] sm:text-[9px] uppercase leading-none font-bold mt-0.5">GYM</span>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-950 tracking-tight uppercase leading-tight">{{ $gymName }}</h1>
                        <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-snug font-medium">{{ $gymAddress }}</p>
                        <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-snug font-medium">
                            <span>Phone: <strong class="text-slate-800 font-semibold">{{ $gymPhone }}</strong></span>
                            @if(!empty($gymEmail))
                                <span class="mx-1 text-slate-400">|</span>
                                <span>Email: <strong class="text-slate-800 font-semibold">{{ $gymEmail }}</strong></span>
                            @endif
                        </p>
                        @if(!empty($gymWeb))
                            <p class="text-xs sm:text-sm text-slate-600 leading-snug font-medium">
                                <span>Website: <strong class="text-slate-800 font-semibold">{{ $gymWeb }}</strong></span>
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Invoice Meta (Right) -->
                <div class="sm:text-right flex flex-col items-start sm:items-end shrink-0 w-full sm:w-auto">
                    <span class="inline-block px-5 py-1.5 rounded-xl bg-slate-950 text-white font-black text-xs sm:text-sm tracking-wider uppercase shadow-xs">
                        {{ $invoiceTitle }}
                    </span>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1.5 font-medium">Invoice No: <span class="text-slate-950 font-bold font-mono">{{ $invoiceNo }}</span></p>
                    <p class="text-xs sm:text-sm text-slate-600 mt-0.5 font-medium">Date: <span class="text-slate-800 font-medium">{{ $invoiceDate }}</span></p>
                    <span class="inline-block mt-1 px-3.5 py-0.5 rounded-full border border-emerald-400 bg-emerald-50/60 text-emerald-600 text-[10px] sm:text-xs font-bold uppercase tracking-wider">
                        {{ $statusText }}
                    </span>
                </div>
            </div>

            <!-- ══════════════════════════════════════════
                 2. TWO CARDS: MEMBER & PLAN DETAILS
            ══════════════════════════════════════════ -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Card 1: MEMBER DETAILS (BILLED TO) -->
                <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50/90 border border-slate-100/90 space-y-1">
                    <div class="flex items-center gap-1.5 text-xs font-black text-slate-900 uppercase tracking-wider mb-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block shrink-0"></span>
                        <span>MEMBER DETAILS (BILLED TO)</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Full Name:</span>
                        <span class="font-bold text-slate-900 col-span-7 sm:col-span-8 truncate">{{ $member->full_name }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Member ID:</span>
                        <span class="font-bold text-slate-900 col-span-7 sm:col-span-8">{{ $member->member_code }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Phone No:</span>
                        <span class="font-bold text-slate-900 col-span-7 sm:col-span-8">{{ $member->phone }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Payment Mode:</span>
                        <span class="font-bold uppercase text-slate-900 col-span-7 sm:col-span-8">{{ $payment->payment_method ?? 'UPI' }}</span>
                    </div>
                </div>

                <!-- Card 2: MEMBERSHIP & PLAN DETAILS -->
                <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50/90 border border-slate-100/90 space-y-1">
                    <div class="flex items-center gap-1.5 text-xs font-black text-slate-900 uppercase tracking-wider mb-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block shrink-0"></span>
                        <span>MEMBERSHIP &amp; PLAN DETAILS</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Package Duration:</span>
                        <span class="font-bold text-slate-900 col-span-7 sm:col-span-8">{{ $durationFormatted }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Start Date:</span>
                        <span class="font-bold text-slate-900 col-span-7 sm:col-span-8">{{ $startDate }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Expiry Date:</span>
                        <span class="font-bold text-slate-900 col-span-7 sm:col-span-8">{{ $endDate }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Status:</span>
                        <span class="font-bold text-emerald-600 col-span-7 sm:col-span-8">{{ $membershipStatus }}</span>
                    </div>
                </div>

            </div>

            <!-- ══════════════════════════════════════════
                 3. SERVICE / ITEM DESCRIPTION TABLE
            ══════════════════════════════════════════ -->
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[340px] text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-950 text-white uppercase text-[11px] sm:text-xs font-black tracking-wider">
                            <th class="py-2.5 px-3 rounded-l-lg w-8 sm:w-10 text-center">#</th>
                            <th class="py-2.5 px-3">SERVICE / ITEM DESCRIPTION</th>
                            <th class="py-2.5 px-3 text-center">DURATION</th>
                            <th class="py-2.5 px-3 text-right">RATE (INR)</th>
                            <th class="py-2.5 px-3 text-right rounded-r-lg">AMOUNT (INR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="text-slate-900">
                            <td class="py-3 px-3 text-center text-slate-500 font-medium text-xs">1</td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-950 text-xs sm:text-sm block">{{ $plan->name ?? 'Gym Membership' }}</span>
                                @if($payment->notes)
                                    <span class="text-[11px] text-slate-500 block mt-0.5">{{ $payment->notes }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center font-medium text-slate-700 text-xs">{{ $durationFormatted }}</td>
                            <td class="py-3 px-3 text-right font-medium text-slate-700 text-xs">{{ $currency }}{{ number_format($payment->amount, 0) }}</td>
                            <td class="py-3 px-3 text-right font-bold text-slate-950 text-xs sm:text-sm">{{ $currency }}{{ number_format($payment->amount, 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ══════════════════════════════════════════
                 4. TWO BOXES: PAYMENT DETAIL & SUMMARY
            ══════════════════════════════════════════ -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Left: PAYMENT DETAIL & VERIFICATION -->
                <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50/90 border border-slate-100/90 space-y-1">
                    <div class="text-xs font-black text-slate-900 uppercase tracking-wider mb-1">
                        PAYMENT DETAIL &amp; VERIFICATION
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-xs">
                        <span class="text-slate-500 font-normal col-span-6 sm:col-span-5">Payment Mode:</span>
                        <span class="font-bold text-slate-900 col-span-6 sm:col-span-7 uppercase">{{ $payment->payment_method ?? 'UPI' }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-xs">
                        <span class="text-slate-500 font-normal col-span-6 sm:col-span-5">Payment Status:</span>
                        <span class="font-bold text-emerald-600 col-span-6 sm:col-span-7">Completed</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-xs">
                        <span class="text-slate-500 font-normal col-span-6 sm:col-span-5">Payment Verification:</span>
                        <span class="font-bold text-emerald-600 col-span-6 sm:col-span-7">{{ $verificationText }}</span>
                    </div>
                    @if($payment->transaction_reference)
                        <div class="grid grid-cols-12 gap-1 pt-0.5 border-t border-slate-200/80 text-xs">
                            <span class="text-slate-500 font-normal col-span-6 sm:col-span-5">Txn Ref ID:</span>
                            <span class="text-slate-900 col-span-6 sm:col-span-7 font-bold">{{ $payment->transaction_reference }}</span>
                        </div>
                    @endif
                </div>

                <!-- Right: TOTALS SUMMARY -->
                <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50/90 border border-slate-100/90 space-y-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-normal">Subtotal</span>
                        <span class="font-medium text-slate-900">{{ $currency }}{{ number_format($payment->amount, 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-normal">Taxes &amp; Gym Surcharge</span>
                        <span class="font-normal text-slate-600">{{ $currency }}0 (Inclusive)</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200">
                        <span class="text-xs sm:text-sm font-bold text-slate-950">Total Amount</span>
                        <span class="text-xs sm:text-sm font-bold text-slate-950">{{ $currency }}{{ number_format($payment->amount, 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-normal">Amount Received</span>
                        <span class="font-bold text-emerald-600">{{ $currency }}{{ number_format($payment->amount, 0) }}</span>
                    </div>
                    
                    <!-- Balance Due Box -->
                    <div class="mt-1 p-2 rounded-lg bg-emerald-50/80 border border-emerald-200/70 flex items-center justify-between text-emerald-950">
                        <span class="font-bold text-xs uppercase tracking-wider text-emerald-950">Balance Due</span>
                        <span class="font-black text-xs sm:text-sm text-emerald-600">{{ $currency }}0</span>
                    </div>
                </div>

            </div>

            <!-- ══════════════════════════════════════════
                 5. TERMS & CONDITIONS
            ══════════════════════════════════════════ -->
            <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50/90 border border-slate-100/90 text-xs text-slate-700 leading-snug space-y-1">
                <div class="text-xs font-black text-slate-900 uppercase tracking-wider mb-1">
                    TERMS &amp; CONDITIONS
                </div>
                <ol class="list-decimal pl-4 space-y-0.5 text-[10.5px] sm:text-[11px] text-slate-600 leading-relaxed font-normal">
                    @foreach($termsList as $term)
                        <li>{{ $term }}</li>
                    @endforeach
                </ol>
            </div>
            </div>

            <!-- FOOTER: Sections 6 & 7 (Anchored at the bottom with generous whitespace after Terms) -->
            <div class="mt-auto pt-6 sm:pt-8 space-y-2.5 sm:space-y-3">
                <!-- ══════════════════════════════════════════
                 6. FOOTER: HELPLINE & SIGNATURE SEAL
                ══════════════════════════════════════════ -->
                <div class="pt-2.5 sm:pt-3 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-2.5 sm:gap-3">
                    
                    <!-- Left: Thank you & Contacts -->
                    <div class="text-left space-y-0.5 sm:space-y-1">
                        <p class="font-bold text-xs sm:text-sm text-slate-950">{{ $thankYouText }}</p>
                        <div class="text-[10px] sm:text-xs text-slate-600 flex flex-wrap items-center gap-x-2 gap-y-0.5 font-normal mt-0.5">
                            @if(!empty($helpline))<span>Official Helpline: <strong class="text-slate-800 font-medium">{{ $helpline }}</strong></span>@endif
                            @if(!empty($helpline) && !empty($footerEmail))<span>|</span>@endif
                            @if(!empty($footerEmail))<span>Email: <strong class="text-slate-800 font-medium">{{ $footerEmail }}</strong></span>@endif
                            @if(!empty($footerWeb))<span>|</span><span>Web: <strong class="text-slate-800 font-medium">{{ $footerWeb }}</strong></span>@endif
                        </div>
                    </div>

                    <!-- Right: Official Seal / Stamp & Signature -->
                    <div class="flex flex-col items-center justify-center shrink-0 min-w-[140px] sm:min-w-[160px] text-center">
                        <div class="relative flex items-center justify-center w-24 h-18 sm:w-28 sm:h-20 select-none mx-auto">
                            @if(!empty($sealUrl))
                                <!-- Uploaded Stamp / Seal Image -->
                                <img src="{{ $sealUrl }}" alt="Stamp" class="h-14 w-14 sm:h-16 sm:w-16 object-contain drop-shadow-xs">
                            @else
                                <!-- Vector Circular Seal Matching Reference Design -->
                                <svg class="w-14 h-14 sm:w-16 sm:h-16 text-slate-700 drop-shadow-xs" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="70" cy="70" r="65" stroke="currentColor" stroke-width="2.5" />
                                    <circle cx="70" cy="70" r="58" stroke="currentColor" stroke-width="1.2" stroke-dasharray="3 2" />
                                    <path id="inv-seal-top" d="M 24 70 A 46 46 0 0 1 116 70" fill="none" />
                                    <text font-size="10.5" font-weight="900" fill="currentColor" letter-spacing="1.5">
                                        <textPath href="#inv-seal-top" startOffset="50%" text-anchor="middle">
                                            {{ strtoupper(substr($gymName, 0, 16)) }}
                                        </textPath>
                                    </text>
                                    <path id="inv-seal-bottom" d="M 116 70 A 46 46 0 0 1 24 70" fill="none" />
                                    <text font-size="9" font-weight="800" fill="currentColor" letter-spacing="1.5">
                                        <textPath href="#inv-seal-bottom" startOffset="50%" text-anchor="middle">
                                            ★ VERIFIED ★
                                        </textPath>
                                    </text>
                                    <line x1="32" y1="58" x2="108" y2="58" stroke="currentColor" stroke-width="1.5" />
                                    <text x="70" y="72" font-size="9.5" font-weight="900" fill="currentColor" text-anchor="middle" letter-spacing="1.5">
                                        AHMEDABAD
                                    </text>
                                    <line x1="32" y1="78" x2="108" y2="78" stroke="currentColor" stroke-width="1.5" />
                                </svg>
                            @endif

                            @if(!empty($signatureUrl))
                                <!-- Uploaded Signature Image (Directly Overlapping on TOP of stamp) -->
                                <img src="{{ $signatureUrl }}" alt="Signature" 
                                     class="absolute inset-0 m-auto h-12 w-24 sm:h-14 sm:w-28 object-contain mix-blend-multiply rotate-[-6deg] drop-shadow-xs pointer-events-none z-10">
                            @endif
                        </div>
                        <span class="text-[10px] sm:text-xs font-semibold text-slate-600 mt-1 text-center block w-full">{{ $sealText }}</span>
                        @if(!empty($signatoryName))
                            <span class="text-[9px] sm:text-[10px] font-semibold text-slate-500 text-center block w-full">{{ $signatoryName }}</span>
                        @endif
                    </div>

                </div>

                <!-- ══════════════════════════════════════════
                     7. VERY BOTTOM TAGLINE BANNER
                ══════════════════════════════════════════ -->
                <div class="pt-2 sm:pt-2.5 border-t border-slate-200 text-center">
                    <p class="text-[11px] sm:text-xs font-black tracking-widest text-slate-950 uppercase">{{ !empty(trim($tagline)) ? $tagline : 'BIGGER SPACE | BIGGER FACILITIES | STRONGER YOU' }}</p>
                </div>
            </div>

        </div>

    </div>

    <script>
        if (window.location.search.includes('print=1') || window.location.search.includes('autoprint=1')) {
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.print();
                }, 300);
            });
        }
    </script>

</body>
</html>
