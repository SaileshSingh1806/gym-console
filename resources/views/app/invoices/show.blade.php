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
            margin: 5mm 6mm;
        }
        @media print {
            html, body {
                height: 100% !important;
                min-height: 100% !important;
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
                height: 285mm !important;
                min-height: 285mm !important;
                margin: 0 !important;
                padding: 14px 18px !important;
                border-radius: 8px !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
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
        $tagline = !empty($invSettings['tagline']) ? $invSettings['tagline'] : "BIGGER SPACE | BIGGER FACILITIES | STRONGER YOU";
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
    <div class="invoice-card relative w-full max-w-3xl bg-white text-slate-900 rounded-2xl shadow-xl p-4 sm:p-6 lg:p-7 border border-slate-200/90 overflow-hidden flex flex-col justify-between min-h-0 sm:min-h-[1050px] print:min-h-[285mm] mx-auto">
        
        <!-- Top Accent Color Stripe -->
        <div class="h-2 w-full bg-slate-950 absolute top-0 left-0 right-0"></div>

        <!-- Faded Center Background Watermark -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.03] select-none z-0">
            @if(!empty($logoUrl))
                <img src="{{ $logoUrl }}" alt="" class="w-[280px] sm:w-[340px] h-[280px] sm:h-[340px] object-contain grayscale">
            @else
                <div class="text-center font-black tracking-widest text-5xl sm:text-7xl uppercase text-slate-900 rotate-[-15deg]">
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
            <div class="flex flex-col sm:flex-row items-start justify-between gap-3 pt-1 border-b border-slate-200/80 pb-3">
                <!-- Gym Brand (Left) -->
                <div class="flex items-start gap-3 max-w-lg">
                    @if(!empty($logoUrl))
                        <img src="{{ $logoUrl }}" alt="{{ $gymName }}" class="h-12 w-12 sm:h-14 sm:w-14 lg:h-16 lg:w-16 object-contain rounded-xl bg-slate-950 p-1 sm:p-1.5 border border-slate-800 shadow-sm shrink-0">
                    @else
                        <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl bg-slate-950 text-white flex flex-col items-center justify-center font-black text-xs tracking-tight shadow-sm shrink-0 p-1 text-center">
                            <span class="text-amber-400 text-lg sm:text-xl">🏋️</span>
                            <span class="text-[8px] sm:text-[9px] uppercase leading-none font-bold mt-0.5">GYM</span>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-950 tracking-tight uppercase leading-tight">{{ $gymName }}</h1>
                        <p class="text-[11px] sm:text-xs lg:text-sm text-slate-600 mt-0.5 sm:mt-1 leading-snug font-medium">{{ $gymAddress }}</p>
                        <div class="flex flex-wrap items-center gap-x-2 sm:gap-x-2.5 gap-y-0.5 text-[11px] sm:text-xs text-slate-600 mt-0.5 sm:mt-1 font-medium">
                            @if(!empty($gymPhone))<span>Phone: <strong class="text-slate-800">{{ $gymPhone }}</strong></span>@endif
                            @if(!empty($gymPhone) && !empty($gymEmail))<span>|</span>@endif
                            @if(!empty($gymEmail))<span>Email: <strong class="text-slate-800">{{ $gymEmail }}</strong></span>@endif
                        </div>
                        @if(!empty($gymWeb))
                            <p class="text-[11px] sm:text-xs text-slate-600 font-medium mt-0.5">Website: <strong class="text-slate-800">{{ $gymWeb }}</strong></p>
                        @endif
                    </div>
                </div>

                <!-- Invoice Meta (Right) -->
                <div class="sm:text-right flex flex-col items-start sm:items-end shrink-0 w-full sm:w-auto">
                    <span class="inline-block px-3 sm:px-4 py-0.5 sm:py-1 rounded-lg bg-slate-950 text-white font-black text-[11px] sm:text-xs tracking-wider uppercase shadow-sm">
                        {{ $invoiceTitle }}
                    </span>
                    <p class="text-xs sm:text-sm font-bold text-slate-900 mt-1 sm:mt-1.5">Invoice No: <span class="text-slate-950 font-black text-sm sm:text-base">{{ $invoiceNo }}</span></p>
                    <p class="text-xs sm:text-sm text-slate-600 mt-0.5 font-medium">Date: <strong class="text-slate-800">{{ $invoiceDate }}</strong></p>
                    <span class="inline-block mt-1 px-3 sm:px-3.5 py-0.5 rounded-full border border-emerald-500 bg-emerald-50 text-emerald-700 text-[10px] sm:text-xs font-black uppercase tracking-wider">
                        {{ $statusText }}
                    </span>
                </div>
            </div>

            <!-- ══════════════════════════════════════════
                 2. TWO CARDS: MEMBER & PLAN DETAILS
            ══════════════════════════════════════════ -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                
                <!-- Card 1: MEMBER DETAILS (BILLED TO) -->
                <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50/95 border border-slate-200/90 space-y-1 sm:space-y-1.5 shadow-xs">
                    <div class="flex items-center gap-1.5 text-xs font-black text-slate-900 uppercase tracking-wider mb-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block shrink-0"></span>
                        <span>MEMBER DETAILS (BILLED TO)</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        <span class="text-slate-500 font-semibold col-span-1">Full Name:</span>
                        <span class="font-bold text-slate-950 col-span-2 truncate">{{ $member->full_name }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        <span class="text-slate-500 font-semibold col-span-1">Member ID:</span>
                        <span class="font-bold text-slate-950 col-span-2">{{ $member->member_code }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        <span class="text-slate-500 font-semibold col-span-1">Phone No:</span>
                        <span class="font-bold text-slate-950 col-span-2">{{ $member->phone }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        <span class="text-slate-500 font-semibold col-span-1">Payment Mode:</span>
                        <span class="font-bold uppercase text-slate-950 col-span-2">{{ $payment->payment_method ?? 'UPI' }}</span>
                    </div>
                </div>

                <!-- Card 2: MEMBERSHIP & PLAN DETAILS -->
                <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50/95 border border-slate-200/90 space-y-1 sm:space-y-1.5 shadow-xs">
                    <div class="flex items-center gap-1.5 text-xs font-black text-slate-900 uppercase tracking-wider mb-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block shrink-0"></span>
                        <span>MEMBERSHIP &amp; PLAN DETAILS</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        <span class="text-slate-500 font-semibold col-span-1">Package Duration:</span>
                        <span class="font-bold text-slate-950 col-span-2">{{ $durationFormatted }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        <span class="text-slate-500 font-semibold col-span-1">Start Date:</span>
                        <span class="font-bold text-slate-950 col-span-2">{{ $startDate }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        <span class="text-slate-500 font-semibold col-span-1">Expiry Date:</span>
                        <span class="font-bold text-slate-950 col-span-2">{{ $endDate }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        <span class="text-slate-500 font-semibold col-span-1">Status:</span>
                        <span class="font-bold text-emerald-600 col-span-2">{{ $membershipStatus }}</span>
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
                            <th class="py-2 sm:py-2.5 px-2.5 sm:px-3.5 rounded-l-lg w-8 sm:w-10 text-center">#</th>
                            <th class="py-2 sm:py-2.5 px-2.5 sm:px-3.5">SERVICE / ITEM DESCRIPTION</th>
                            <th class="py-2 sm:py-2.5 px-2.5 sm:px-3.5 text-center">DURATION</th>
                            <th class="py-2.5 sm:py-2.5 px-2.5 sm:px-3.5 text-right">RATE (INR)</th>
                            <th class="py-2.5 sm:py-2.5 px-2.5 sm:px-3.5 text-right rounded-r-lg">AMOUNT (INR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="text-slate-900 font-semibold">
                            <td class="py-2.5 sm:py-3 px-2.5 sm:px-3.5 text-center text-slate-500 font-bold text-xs">1</td>
                            <td class="py-2.5 sm:py-3 px-2.5 sm:px-3.5">
                                <span class="font-black text-slate-950 text-xs sm:text-sm block">{{ $plan->name ?? 'Gym Membership' }}</span>
                                @if($payment->notes)
                                    <span class="text-xs text-slate-500 block mt-0.5">{{ $payment->notes }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 sm:py-3 px-2.5 sm:px-3.5 text-center font-bold text-slate-800 text-xs">{{ $durationFormatted }}</td>
                            <td class="py-2.5 sm:py-3 px-2.5 sm:px-3.5 text-right font-bold text-slate-800 text-xs">{{ $currency }}{{ number_format($payment->amount, 0) }}</td>
                            <td class="py-2.5 sm:py-3 px-2.5 sm:px-3.5 text-right font-black text-slate-950 text-xs sm:text-sm">{{ $currency }}{{ number_format($payment->amount, 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ══════════════════════════════════════════
                 4. TWO BOXES: PAYMENT DETAIL & SUMMARY
            ══════════════════════════════════════════ -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                
                <!-- Left: PAYMENT DETAIL & VERIFICATION -->
                <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50/95 border border-slate-200/90 space-y-1 sm:space-y-1.5 shadow-xs">
                    <div class="text-xs font-black text-slate-900 uppercase tracking-wider mb-1">
                        PAYMENT DETAIL &amp; VERIFICATION
                    </div>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        <span class="text-slate-500 font-semibold">Payment Mode:</span>
                        <span class="font-bold text-slate-950 col-span-2 uppercase">{{ $payment->payment_method ?? 'UPI' }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        <span class="text-slate-500 font-semibold">Payment Status:</span>
                        <span class="font-bold text-emerald-600 col-span-2">Completed</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        <span class="text-slate-500 font-semibold">Verification:</span>
                        <span class="font-bold text-emerald-600 col-span-2">{{ $verificationText }}</span>
                    </div>
                    @if($payment->transaction_reference)
                        <div class="grid grid-cols-3 gap-1 pt-1 border-t border-slate-200/80 text-xs">
                            <span class="text-slate-500 font-semibold">Txn Ref ID:</span>
                            <span class="text-slate-900 col-span-2 font-bold">{{ $payment->transaction_reference }}</span>
                        </div>
                    @endif
                </div>

                <!-- Right: TOTALS SUMMARY -->
                <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50/95 border border-slate-200/90 space-y-1 sm:space-y-1.5 shadow-xs">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-semibold">Subtotal</span>
                        <span class="font-bold text-slate-900">{{ $currency }}{{ number_format($payment->amount, 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-semibold">Taxes &amp; Surcharge</span>
                        <span class="font-bold text-slate-700">{{ $currency }}0 (Inclusive)</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200">
                        <span class="text-xs sm:text-sm font-black text-slate-950">Total Amount</span>
                        <span class="text-xs sm:text-sm font-black text-slate-950">{{ $currency }}{{ number_format($payment->amount, 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold text-slate-700">Amount Received</span>
                        <span class="text-xs sm:text-sm font-black text-emerald-600">{{ $currency }}{{ number_format($payment->amount, 0) }}</span>
                    </div>
                    
                    <!-- Balance Due Box -->
                    <div class="mt-1 p-1.5 sm:p-2 rounded-lg bg-emerald-100/70 border border-emerald-300 flex items-center justify-between text-emerald-950">
                        <span class="font-black text-[10px] sm:text-xs uppercase tracking-wider">Balance Due</span>
                        <span class="font-black text-xs sm:text-sm">{{ $currency }}0</span>
                    </div>
                </div>

            </div>

            <!-- ══════════════════════════════════════════
                 5. TERMS & CONDITIONS
            ══════════════════════════════════════════ -->
            <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/90 text-xs text-slate-700 leading-snug space-y-1">
                <div class="text-xs font-black text-slate-900 uppercase tracking-wider mb-0.5">
                    TERMS &amp; CONDITIONS
                </div>
                <ol class="list-decimal pl-4 space-y-0.5 sm:space-y-1 text-[11px] sm:text-xs text-slate-600 leading-normal">
                    @foreach($termsList as $term)
                        <li>{{ $term }}</li>
                    @endforeach
                </ol>
            </div>
            </div>

            <!-- FOOTER: Sections 6 & 7 (Anchored at bottom with spacing after Terms) -->
            <div class="mt-auto pt-4 sm:pt-6 space-y-2.5 sm:space-y-3">
                <!-- ══════════════════════════════════════════
                     6. FOOTER: HELPLINE & SIGNATURE SEAL
                ══════════════════════════════════════════ -->
                <div class="pt-3 sm:pt-3.5 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-2.5 sm:gap-3">
                    
                    <!-- Left: Thank you & Contacts -->
                    <div class="text-left space-y-0.5 sm:space-y-1">
                        <p class="font-black text-xs sm:text-sm text-slate-950">{{ $thankYouText }}</p>
                        <div class="text-[11px] sm:text-xs text-slate-600 flex flex-wrap items-center gap-x-2 sm:gap-x-2.5 font-medium mt-0.5">
                            @if(!empty($helpline))<span>Official Helpline: <strong class="text-slate-800">{{ $helpline }}</strong></span>@endif
                            @if(!empty($helpline) && !empty($footerEmail))<span>|</span>@endif
                            @if(!empty($footerEmail))<span>Email: <strong class="text-slate-800">{{ $footerEmail }}</strong></span>@endif
                            @if(!empty($footerWeb))<span>|</span><span>Web: <strong class="text-slate-800">{{ $footerWeb }}</strong></span>@endif
                        </div>
                    </div>

                    <!-- Right: Official Seal / Stamp & Signature -->
                    <div class="flex flex-col items-center sm:items-end shrink-0 min-w-[120px] sm:min-w-[140px]">
                        <div class="relative flex items-center justify-center w-20 h-16 sm:w-24 sm:h-20 select-none">
                            @if(!empty($sealUrl))
                                <!-- Uploaded Stamp / Seal Image -->
                                <img src="{{ $sealUrl }}" alt="Stamp" class="h-14 w-14 sm:h-16 sm:w-16 object-contain drop-shadow-xs">
                            @else
                                <!-- Vector Circular Seal Matching Image -->
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full border border-slate-400 border-dashed flex flex-col items-center justify-center text-center p-1 text-slate-700 select-none">
                                    <span class="text-[7px] sm:text-[8px] font-black uppercase tracking-tighter leading-none">{{ substr($gymName, 0, 14) }}</span>
                                    <span class="text-[6.5px] sm:text-[7px] text-slate-500 font-bold leading-none mt-0.5">★ SEAL ★</span>
                                    <span class="text-[6px] sm:text-[6.5px] font-semibold text-slate-400 leading-none mt-0.5">VERIFIED</span>
                                </div>
                            @endif

                            @if(!empty($signatureUrl))
                                <!-- Uploaded Signature Image (Directly Overlapping on TOP of stamp with blend-mode multiply) -->
                                <img src="{{ $signatureUrl }}" alt="Signature" 
                                     class="absolute inset-0 m-auto h-12 w-24 sm:h-14 sm:w-28 object-contain mix-blend-multiply rotate-[-6deg] drop-shadow-xs pointer-events-none z-10">
                            @endif
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold text-slate-600 mt-0.5 uppercase tracking-wider text-center sm:text-right">{{ $sealText }}</span>
                        @if(!empty($signatoryName))
                            <span class="text-[10px] sm:text-xs font-bold text-slate-500">{{ $signatoryName }}</span>
                        @endif
                    </div>

                </div>

                <!-- ══════════════════════════════════════════
                     7. VERY BOTTOM TAGLINE BANNER
                ══════════════════════════════════════════ -->
                @if(!empty($tagline))
                    <div class="pt-2 sm:pt-2.5 border-t border-slate-200 text-center">
                        <p class="text-[10px] sm:text-xs font-black tracking-widest text-slate-950 uppercase">{{ $tagline }}</p>
                    </div>
                @endif
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
