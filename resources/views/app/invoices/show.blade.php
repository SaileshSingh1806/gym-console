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
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        @media print {
            body {
                background: white !important;
                color: #0f172a !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .invoice-card {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 15px !important;
                border-radius: 0 !important;
            }
            @page {
                size: A4;
                margin: 8mm;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen py-6 px-3 sm:px-6 flex flex-col items-center antialiased">

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
            $termsList = array_filter(array_map('trim', explode("\n", $invSettings['terms'])));
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
    @endphp

    <!-- Top Action Toolbar -->
    <div class="no-print w-full max-w-3xl flex flex-wrap items-center justify-between gap-3 mb-5">
        <a href="{{ route('app.members.show', $member->id) }}" 
           class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-950 border border-slate-300 text-xs font-bold transition-all shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Member</span>
        </a>

        <div class="flex items-center gap-2.5">
            <button type="button" 
                    onclick="window.print()" 
                    class="px-5 py-2.5 rounded-xl bg-slate-950 hover:bg-slate-800 text-white font-black text-xs flex items-center gap-2 shadow-lg shadow-black/20 transition-all cursor-pointer">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Invoice</span>
            </button>

            <button type="button" 
                    onclick="window.print()" 
                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/30 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Download PDF</span>
            </button>
        </div>
    </div>

    <!-- ==================== INVOICE A4 SHEET ==================== -->
    <div class="invoice-card relative w-full max-w-3xl bg-white text-slate-900 rounded-3xl shadow-2xl p-6 sm:p-10 border border-slate-200/90 flex flex-col justify-between overflow-hidden">
        
        <!-- Top Accent Color Stripe -->
        <div class="h-2 w-full bg-slate-950 absolute top-0 left-0 right-0"></div>

        <!-- Faded Center Background Watermark -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.035] select-none z-0">
            @if(!empty($logoUrl))
                <img src="{{ $logoUrl }}" alt="" class="w-96 h-96 object-contain grayscale">
            @else
                <div class="text-center font-black tracking-widest text-7xl uppercase text-slate-900 rotate-[-15deg]">
                    {{ $gymName }}
                </div>
            @endif
        </div>

        <div class="relative z-10 space-y-6">
            
            <!-- ══════════════════════════════════════════
                 1. HEADER: BRAND & INVOICE META
            ══════════════════════════════════════════ -->
            <div class="flex flex-col sm:flex-row items-start justify-between gap-4 pt-2">
                <!-- Gym Brand (Left) -->
                <div class="flex items-start gap-3.5 max-w-md">
                    @if(!empty($logoUrl))
                        <img src="{{ $logoUrl }}" alt="{{ $gymName }}" class="h-16 w-16 object-contain rounded-xl bg-slate-950 p-1.5 border border-slate-800 shadow-md shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-slate-950 text-white flex flex-col items-center justify-center font-black text-xs tracking-tighter shadow-md shrink-0 p-1 text-center">
                            <span class="text-amber-400 text-sm">🏋️</span>
                            <span class="text-[9px] uppercase leading-none font-bold">GYM</span>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight uppercase leading-none">{{ $gymName }}</h1>
                        <p class="text-[11px] sm:text-xs text-slate-600 mt-1.5 leading-snug font-medium">{{ $gymAddress }}</p>
                        <div class="flex flex-wrap items-center gap-x-2 text-[11px] text-slate-600 mt-1 font-medium">
                            @if(!empty($gymPhone))<span>Phone: {{ $gymPhone }}</span>@endif
                            @if(!empty($gymPhone) && !empty($gymEmail))<span>|</span>@endif
                            @if(!empty($gymEmail))<span>Email: {{ $gymEmail }}</span>@endif
                        </div>
                        @if(!empty($gymWeb))
                            <p class="text-[11px] text-slate-600 font-medium">Website: {{ $gymWeb }}</p>
                        @endif
                    </div>
                </div>

                <!-- Invoice Meta (Right) -->
                <div class="sm:text-right flex flex-col items-start sm:items-end shrink-0 w-full sm:w-auto pt-2 sm:pt-0">
                    <span class="inline-block px-4 py-1 rounded-lg bg-slate-950 text-white font-black text-xs tracking-wider uppercase shadow-sm">
                        {{ $invoiceTitle }}
                    </span>
                    <p class="text-xs font-bold text-slate-900 mt-2 font-mono">Invoice No: <span class="text-slate-900 font-extrabold">{{ $invoiceNo }}</span></p>
                    <p class="text-xs text-slate-600 mt-0.5 font-medium">Date: {{ $invoiceDate }}</p>
                    <span class="inline-block mt-2 px-3.5 py-0.5 rounded-full border border-emerald-500 bg-emerald-50 text-emerald-700 text-[11px] font-black uppercase tracking-wider">
                        {{ $statusText }}
                    </span>
                </div>
            </div>

            <!-- ══════════════════════════════════════════
                 2. TWO CARDS: MEMBER & PLAN DETAILS
            ══════════════════════════════════════════ -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                
                <!-- Card 1: MEMBER DETAILS (BILLED TO) -->
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/90 border border-slate-200/90 space-y-2.5">
                    <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span>
                        <span>MEMBER DETAILS (BILLED TO)</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500 font-medium col-span-1">Full Name:</span>
                        <span class="font-bold text-slate-950 col-span-2">{{ $member->full_name }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500 font-medium col-span-1">Member ID:</span>
                        <span class="font-bold font-mono text-slate-950 col-span-2">{{ $member->member_code }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500 font-medium col-span-1">Phone No:</span>
                        <span class="font-bold text-slate-950 col-span-2">{{ $member->phone }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500 font-medium col-span-1">Payment Mode:</span>
                        <span class="font-bold uppercase text-slate-950 col-span-2">{{ $payment->payment_method ?? 'UPI' }}</span>
                    </div>
                </div>

                <!-- Card 2: MEMBERSHIP & PLAN DETAILS -->
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/90 border border-slate-200/90 space-y-2.5">
                    <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span>
                        <span>MEMBERSHIP &amp; PLAN DETAILS</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500 font-medium col-span-1">Package Duration:</span>
                        <span class="font-bold text-slate-950 col-span-2">{{ $durationFormatted }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500 font-medium col-span-1">Start Date:</span>
                        <span class="font-bold text-slate-950 col-span-2">{{ $startDate }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500 font-medium col-span-1">Expiry Date:</span>
                        <span class="font-bold text-slate-950 col-span-2">{{ $endDate }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500 font-medium col-span-1">Status:</span>
                        <span class="font-bold text-emerald-600 col-span-2">{{ $membershipStatus }}</span>
                    </div>
                </div>

            </div>

            <!-- ══════════════════════════════════════════
                 3. SERVICE / ITEM DESCRIPTION TABLE
            ══════════════════════════════════════════ -->
            <div>
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-950 text-white uppercase text-[11px] font-extrabold tracking-wider">
                            <th class="py-3 px-3 rounded-l-xl w-10 text-center">#</th>
                            <th class="py-3 px-3">SERVICE / ITEM DESCRIPTION</th>
                            <th class="py-3 px-3 text-center">DURATION</th>
                            <th class="py-3 px-3 text-right">RATE (INR)</th>
                            <th class="py-3 px-3 text-right rounded-r-xl">AMOUNT (INR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="text-slate-900 font-semibold">
                            <td class="py-4 px-3 text-center text-slate-500 font-bold">1</td>
                            <td class="py-4 px-3">
                                <span class="font-black text-slate-950 text-sm block">{{ $plan->name ?? 'Gym Membership' }}</span>
                                @if($payment->notes)
                                    <span class="text-[11px] text-slate-500 block mt-0.5">{{ $payment->notes }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-3 text-center font-bold text-slate-800">{{ $durationFormatted }}</td>
                            <td class="py-4 px-3 text-right font-bold text-slate-800">{{ $currency }}{{ number_format($payment->amount, 0) }}</td>
                            <td class="py-4 px-3 text-right font-black text-slate-950 text-sm">{{ $currency }}{{ number_format($payment->amount, 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ══════════════════════════════════════════
                 4. TWO BOXES: PAYMENT DETAIL & SUMMARY
            ══════════════════════════════════════════ -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                
                <!-- Left: PAYMENT DETAIL & VERIFICATION -->
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/90 border border-slate-200/90 space-y-2.5">
                    <div class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-2">
                        PAYMENT DETAIL &amp; VERIFICATION
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500 font-medium">Payment Mode:</span>
                        <span class="font-bold text-slate-950 col-span-2 uppercase">{{ $payment->payment_method ?? 'UPI' }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500 font-medium">Payment Status:</span>
                        <span class="font-bold text-emerald-600 col-span-2">Completed</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500 font-medium">Payment Verification:</span>
                        <span class="font-bold text-emerald-600 col-span-2">{{ $verificationText }}</span>
                    </div>
                    @if($payment->transaction_reference)
                        <div class="grid grid-cols-3 gap-1 pt-1 border-t border-slate-200/60">
                            <span class="text-slate-500 font-medium">Txn Ref ID:</span>
                            <span class="font-mono text-slate-700 col-span-2 font-semibold">{{ $payment->transaction_reference }}</span>
                        </div>
                    @endif
                </div>

                <!-- Right: TOTALS SUMMARY -->
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/90 border border-slate-200/90 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 font-medium">Subtotal</span>
                        <span class="font-bold text-slate-900">{{ $currency }}{{ number_format($payment->amount, 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 font-medium">Taxes &amp; Gym Surcharge</span>
                        <span class="font-bold text-slate-700">{{ $currency }}0 (Inclusive)</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200/80">
                        <span class="text-sm font-black text-slate-950">Total Amount</span>
                        <span class="text-sm font-black text-slate-950">{{ $currency }}{{ number_format($payment->amount, 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-black text-slate-700">Amount Received</span>
                        <span class="text-sm font-black text-emerald-600">{{ $currency }}{{ number_format($payment->amount, 0) }}</span>
                    </div>
                    
                    <!-- Balance Due Pill Box -->
                    <div class="mt-2 p-2.5 rounded-xl bg-emerald-100/60 border border-emerald-300 flex items-center justify-between text-emerald-900">
                        <span class="font-black text-xs uppercase tracking-wider">Balance Due</span>
                        <span class="font-black text-sm">{{ $currency }}0</span>
                    </div>
                </div>

            </div>

            <!-- ══════════════════════════════════════════
                 5. TERMS & CONDITIONS
            ══════════════════════════════════════════ -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 text-[10.5px] sm:text-[11px] text-slate-600 leading-relaxed space-y-1.5">
                <div class="text-xs font-black text-slate-900 uppercase tracking-wider mb-1.5">
                    TERMS &amp; CONDITIONS
                </div>
                <ol class="list-decimal pl-4 space-y-1">
                    @foreach($termsList as $term)
                        <li>{{ $term }}</li>
                    @endforeach
                </ol>
            </div>

            <!-- ══════════════════════════════════════════
                 6. FOOTER: HELPLINE & SIGNATURE SEAL
            ══════════════════════════════════════════ -->
            <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                
                <!-- Left: Thank you & Contacts -->
                <div class="text-left space-y-1">
                    <p class="font-extrabold text-xs text-slate-950">{{ $thankYouText }}</p>
                    <div class="text-[10.5px] text-slate-600 flex flex-wrap items-center gap-x-2 font-medium">
                        @if(!empty($helpline))<span>Official Helpline: {{ $helpline }}</span>@endif
                        @if(!empty($helpline) && !empty($footerEmail))<span>|</span>@endif
                        @if(!empty($footerEmail))<span>Email: {{ $footerEmail }}</span>@endif
                        @if(!empty($footerWeb))<span>|</span><span>Web: {{ $footerWeb }}</span>@endif
                    </div>
                </div>

                <!-- Right: Official Seal / Signature -->
                <div class="flex flex-col items-center sm:items-end shrink-0">
                    @if(!empty($sealUrl))
                        <img src="{{ $sealUrl }}" alt="Seal" class="h-16 w-16 object-contain">
                    @else
                        <!-- Vector Circular Seal Matching Image -->
                        <div class="w-16 h-16 rounded-full border-2 border-slate-400 border-dashed flex flex-col items-center justify-center text-center p-1 text-slate-700 select-none">
                            <span class="text-[7.5px] font-black uppercase tracking-tighter leading-none">{{ substr($gymName, 0, 14) }}</span>
                            <span class="text-[7px] text-slate-500 font-bold leading-none mt-0.5">★ SEAL ★</span>
                            <span class="text-[6.5px] font-semibold text-slate-400 leading-none mt-0.5">VERIFIED</span>
                        </div>
                    @endif
                    <span class="text-[10px] font-bold text-slate-500 mt-1 uppercase tracking-wider">{{ $sealText }}</span>
                </div>

            </div>

            <!-- ══════════════════════════════════════════
                 7. VERY BOTTOM TAGLINE BANNER
            ══════════════════════════════════════════ -->
            @if(!empty($tagline))
                <div class="pt-3 border-t border-slate-200 text-center">
                    <p class="text-xs font-black tracking-widest text-slate-900 uppercase">{{ $tagline }}</p>
                </div>
            @endif

        </div>

    </div>

</body>
</html>
