<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <title>Tax Invoice - {{ $payment->receipt_number ?? ('ARM-' . $payment->id) }} - {{ $tenant->name ?? 'GymDeskHub' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
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
            margin: 8mm 10mm;
        }
        @media print {
            html, body {
                width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                overflow: visible !important;
            }
            .no-print {
                display: none !important;
            }
            .invoice-card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                max-width: 100% !important;
                width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                margin: 0 auto !important;
                padding: 10px 16px !important;
                border-radius: 6px !important;
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
<body x-data="{ showEditMethodModal: false, selectedMethod: '{{ strtolower($payment->payment_method ?? 'cash') }}', txnRef: '{{ addslashes($payment->transaction_reference ?? '') }}', changeReason: '' }" class="bg-slate-100 text-slate-900 min-h-screen py-3 sm:py-6 px-2 sm:px-6 flex flex-col items-center antialiased overflow-x-hidden">

    @php
        $tenantSettings = $tenant->settings ?? [];
        $invSettings = $tenantSettings['invoice'] ?? [];
        $currency = $tenant->currency_symbol ?? 'Rs. ';
        $logoUrl = $tenant->logo_url ?? ($platformSettings['logo_url'] ?? null);

        // Header details
        $gymName = !empty($invSettings['gym_name']) ? $invSettings['gym_name'] : ($tenant->name ?? 'Gym');
        $gymAddress = !empty($invSettings['address']) ? $invSettings['address'] : (!empty($tenantSettings['address']) ? $tenantSettings['address'] : ($member->branch->address ?? ($tenant->mainBranch?->address ?? ($tenant->address ?? ''))));
        $gymPhone = !empty($invSettings['phone']) ? $invSettings['phone'] : ($tenant->phone ?? '');
        $gymEmail = !empty($invSettings['email']) ? $invSettings['email'] : ($tenant->email ?? '');
        $gymWeb = !empty($invSettings['website']) ? $invSettings['website'] : ($tenant->website ?? '');
        $invoiceTitle = !empty($invSettings['title']) ? $invSettings['title'] : 'TAX INVOICE';
        
        // Short Clean Invoice Number (e.g. INV-0087, INV-0001)
        $cleanPrefix = !empty($invSettings['prefix']) ? $invSettings['prefix'] : 'INV-';
        $rawNum = $payment->invoice_number ?? '';
        if (!empty($rawNum) && !str_starts_with($rawNum, 'RCP-') && !str_starts_with($rawNum, 'ARM')) {
            $invoiceNo = $rawNum;
        } else {
            $prefixFormatted = str_ends_with($cleanPrefix, '-') ? $cleanPrefix : ($cleanPrefix . '-');
            $invoiceNo = $prefixFormatted . str_pad((string)$payment->id, 4, '0', STR_PAD_LEFT);
        }
        
        $invoiceDate = $payment->payment_date ? $payment->payment_date->format('d M Y') : $payment->created_at->format('d M Y');
        $verificationText = !empty($invSettings['verification_text']) ? $invSettings['verification_text'] : 'Verified & Recorded';

        // Financial calculations based on Membership Package
        $discountAmount = $membership ? (float) ($membership->discount ?? 0) : 0;
        
        if ($membership && (float)$membership->final_amount > 0) {
            $totalPackageAmount = (float) $membership->final_amount;
            $baseRate = (float) ($membership->price > 0 ? $membership->price : ($totalPackageAmount + $discountAmount));
            $totalPaidSoFar = (float) ($membership->paid_amount > 0 ? $membership->paid_amount : $payment->amount);
        } elseif ($plan && (float)$plan->price > 0) {
            $baseRate = (float) $plan->price;
            $totalPackageAmount = max(0, $baseRate - $discountAmount);
            $totalPaidSoFar = (float) ($membership?->paid_amount > 0 ? $membership->paid_amount : $payment->amount);
        } else {
            $baseRate = (float) $payment->amount;
            $totalPackageAmount = (float) $payment->amount;
            $totalPaidSoFar = (float) $payment->amount;
        }

        $balanceDue = max(0, $totalPackageAmount - $totalPaidSoFar);
        $amountReceivedDisplay = min($totalPackageAmount, $totalPaidSoFar);

        // Status pill & badge styling
        if ($balanceDue > 0) {
            $statusText = 'PARTIAL PAYMENT (DUE: ' . $currency . number_format($balanceDue, 0) . ')';
            $statusBadgeClass = 'border-amber-400 bg-amber-50 text-amber-800 font-extrabold';
            $statusDotClass = 'bg-amber-500';
        } else {
            $statusText = !empty($invSettings['status_text']) ? $invSettings['status_text'] : 'PAID IN FULL';
            $statusBadgeClass = 'border-emerald-400 bg-emerald-50 text-emerald-700';
            $statusDotClass = 'bg-emerald-500';
        }

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
        $tagline = !empty(trim($invSettings['tagline'] ?? '')) ? $invSettings['tagline'] : '';
        $sealText = !empty($invSettings['seal_text']) ? $invSettings['seal_text'] : "Authorized Signature / Seal";
        $sealUrl = $invSettings['seal_url'] ?? null;
        $signatureUrl = $invSettings['signature_url'] ?? null;
        $signatoryName = $invSettings['signatory_name'] ?? null;
    @endphp

    @php
        $isCustomerView = request()->routeIs('app.invoices.public') || !auth()->check();
    @endphp

    <!-- Top Action Toolbar -->
    <div class="no-print w-full max-w-3xl flex flex-col sm:flex-row items-center justify-between gap-2.5 sm:gap-3 mb-3 sm:mb-4">
        @auth
            @if(!request()->routeIs('app.invoices.public'))
                <a href="{{ route('app.members.show', $member->id) }}" 
                   class="w-full sm:w-auto px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-950 border border-slate-300 text-xs font-bold transition-all shadow-sm inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back to Member</span>
                </a>
            @else
                <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-start">
                    <span class="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-black text-xs shadow-sm">
                        {{ $gymName }}
                    </span>
                    <span class="text-[11px] font-bold text-slate-500">
                        {{ $invoiceNo }}
                    </span>
                </div>
            @endif
        @else
            <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-start">
                <span class="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-black text-xs shadow-sm">
                    {{ $gymName }}
                </span>
                <span class="text-[11px] font-bold text-slate-500">
                    {{ $invoiceNo }}
                </span>
            </div>
        @endauth

        @if($isCustomerView)
            <!-- Customer / Public Link: ONLY Download Option (No WhatsApp, No Print button) -->
            <div class="flex items-center w-full sm:w-auto justify-end">
                <button type="button" 
                        onclick="window.print()" 
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/25 hover:shadow-indigo-600/40 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download Invoice</span>
                </button>
            </div>
        @else
            <!-- Console / Staff View: WhatsApp + Print + PDF -->
            <div class="flex items-center gap-2 w-full sm:w-auto justify-stretch sm:justify-end">
                <a href="{{ $payment->whatsapp_share_url }}" 
                   target="_blank" 
                   class="flex-1 sm:flex-none justify-center px-3 sm:px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs flex items-center gap-1.5 shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    <span>WhatsApp</span>
                </a>

                @auth
                    @if((auth()->user()->isGymOwner() || auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('payments.edit_method')) && !request()->routeIs('app.invoices.public'))
                        <button type="button" 
                                @click="showEditMethodModal = true" 
                                class="px-3 sm:px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center justify-center gap-1.5 shadow-md transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Change Payment Mode</span>
                        </button>
                    @endif
                @endauth

                <button type="button" 
                        onclick="window.print()" 
                        class="px-3 sm:px-4 py-2 rounded-xl bg-slate-950 hover:bg-slate-800 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-md transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print</span>
                </button>

                <button type="button" 
                        onclick="window.print()" 
                        class="px-3 sm:px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-md shadow-indigo-600/20 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>PDF</span>
                </button>
            </div>
        @endif
    </div>

    <!-- ==================== INVOICE A4 SHEET ==================== -->
    <div class="invoice-card relative w-full max-w-3xl bg-white text-slate-900 rounded-2xl shadow-xl p-3.5 sm:p-6 lg:p-7 border border-slate-200 flex flex-col justify-between min-h-0 sm:min-h-[1040px] print:min-h-0 mx-auto">
        
        <!-- Faded Center Background Watermark -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.035] select-none z-0">
            @if(!empty($logoUrl))
                <img src="{{ $logoUrl }}" alt="" class="w-[280px] sm:w-[400px] h-[280px] sm:h-[400px] object-contain grayscale">
            @else
                <div class="text-center font-black tracking-widest text-5xl sm:text-8xl uppercase text-slate-900 rotate-[-12deg]">
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
            <div class="flex flex-col sm:flex-row items-start justify-between gap-3 sm:gap-4 pt-1 border-b border-slate-200/90 pb-3 sm:pb-3.5">
                <!-- Gym Brand (Left) -->
                <div class="flex items-start gap-2.5 sm:gap-3.5 w-full sm:max-w-lg min-w-0">
                    @if(!empty($logoUrl))
                        <div class="w-14 h-14 sm:w-20 sm:h-20 rounded-xl sm:rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 p-1 sm:p-1.5 border border-slate-800 shadow-md flex items-center justify-center shrink-0">
                            <img src="{{ $logoUrl }}" alt="{{ $gymName }}" class="w-full h-full object-contain rounded-lg sm:rounded-xl">
                        </div>
                    @else
                        <div class="w-14 h-14 sm:w-20 sm:h-20 rounded-xl sm:rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 text-white flex flex-col items-center justify-center font-black text-xs tracking-tight shadow-md shrink-0 p-1 text-center border border-slate-800">
                            <span class="text-amber-400 text-xl sm:text-2xl">🏋️</span>
                            <span class="text-[8px] sm:text-[9px] uppercase leading-none font-bold mt-1 text-slate-300">GYM</span>
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <h1 class="text-base sm:text-2xl lg:text-[26px] font-black text-slate-950 tracking-tight uppercase leading-tight">{{ $gymName }}</h1>
                        <p class="text-[11px] sm:text-xs text-slate-600 mt-0.5 sm:mt-1 leading-snug font-medium line-clamp-2">{{ $gymAddress }}</p>
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10.5px] sm:text-xs text-slate-600 mt-1 font-medium">
                            <span class="inline-flex items-center gap-1">
                                <span class="text-slate-400">Phone:</span>
                                <strong class="text-slate-900 font-semibold">{{ $gymPhone }}</strong>
                            </span>
                            @if(!empty($gymEmail))
                                <span class="text-slate-300">|</span>
                                <span class="inline-flex items-center gap-1 truncate max-w-[190px] sm:max-w-[220px]">
                                    <span class="text-slate-400">Email:</span>
                                    <strong class="text-slate-900 font-semibold truncate">{{ $gymEmail }}</strong>
                                </span>
                            @endif
                        </div>
                        @if(!empty($gymWeb))
                            <div class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 font-medium">
                                <span>Website: <strong class="text-slate-800 font-semibold">{{ $gymWeb }}</strong></span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Invoice Meta (Right on desktop, 2-column flex on mobile) -->
                <div class="w-full sm:w-auto flex flex-row sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 shrink-0 self-stretch sm:self-start">
                    <div class="flex flex-col items-start sm:items-end gap-1">
                        <!-- Tax Invoice Badge -->
                        <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-lg sm:rounded-xl bg-slate-950 text-white shadow-xs">
                            <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-amber-400"></span>
                            <span class="font-black text-[10px] sm:text-xs tracking-wider uppercase">{{ $invoiceTitle }}</span>
                        </div>
                        <!-- Invoice Date -->
                        <div class="flex items-center gap-1 sm:gap-2 text-[10.5px] sm:text-xs">
                            <span class="text-slate-500 font-medium">Date:</span>
                            <span class="font-bold text-slate-800">{{ $invoiceDate }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1">
                        <!-- Clean Short Invoice Number -->
                        <div class="flex items-center gap-1 sm:gap-2">
                            <span class="text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-wider hidden xs:inline">No:</span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-300 text-slate-950 font-black font-mono text-xs sm:text-sm tracking-wider shadow-xs">
                                {{ $invoiceNo }}
                            </span>
                        </div>
                        <!-- Status Pill -->
                        <div>
                            <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-0.5 rounded-full border {{ $statusBadgeClass }} text-[9.5px] sm:text-xs font-black uppercase tracking-wider shadow-xs">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusDotClass }}"></span>
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════
                 2. TWO CARDS: MEMBER & PLAN DETAILS
            ══════════════════════════════════════════ -->
            @php
                $rawNotes = $payment->notes ?? '';
                $isSplit = ($payment->payment_method === 'split' || str_contains($rawNotes, 'Split Payment') || str_contains($rawNotes, 'Split:'));
                
                // Determine friendly payment mode text without the word "SPLIT"
                $modeText = strtoupper($payment->payment_method ?? 'UPI');
                $breakdownText = null;

                if ($isSplit) {
                    $breakdownText = $rawNotes;
                    if (str_contains($breakdownText, 'Split Payment:')) {
                        $parts = explode('Split Payment:', $breakdownText);
                        $breakdownText = trim(end($parts));
                    } elseif (str_contains($breakdownText, 'Split:')) {
                        $parts = explode('Split:', $breakdownText);
                        $breakdownText = trim(end($parts));
                    }
                    $breakdownText = preg_replace('/^Split[:\s-]*/i', '', trim($breakdownText));

                    if (preg_match('/([A-Za-z]+)\s*(?:₹|Rs\.?|\$)?[\d,.]*.*?\+\s*([A-Za-z]+)/i', $rawNotes, $matches)) {
                        $modeText = strtoupper($matches[1]) . ' + ' . strtoupper($matches[2]);
                    } elseif (preg_match('/Cash.*?\\+\\s*([A-Za-z]+)/i', $rawNotes, $matches)) {
                        $modeText = 'CASH + ' . strtoupper($matches[1]);
                    } else {
                        $modeText = 'SPLIT PAYMENT';
                    }
                }

                // Clean note for Service Description table - NEVER show split details under service
                $cleanNote = null;
                if (! $isSplit) {
                    $lower = strtolower($rawNotes);
                    $isBoilerplate = str_contains($lower, 'enrollment') || 
                                     str_contains($lower, 'imported') || 
                                     str_contains($lower, 'csv') || 
                                     str_contains($lower, 'fee balance') || 
                                     str_contains($lower, 'subscription payment') ||
                                     str_contains($lower, 'conversion') ||
                                     str_contains($lower, 'crm') ||
                                     str_contains($lower, 'lead') ||
                                     str_contains($lower, 'auto-created');
                    if (! $isBoilerplate && ! empty($rawNotes)) {
                        $cleanNote = $rawNotes;
                    }
                }
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-4">
                
                <!-- Card 1: MEMBER DETAILS (BILLED TO) -->
                <div class="p-2.5 sm:p-3.5 rounded-xl sm:rounded-2xl bg-slate-50/90 border border-slate-100/90 space-y-1">
                    <div class="flex items-center gap-1.5 text-[11px] sm:text-xs font-black text-slate-900 uppercase tracking-wider mb-1">
                        <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-red-500 inline-block shrink-0"></span>
                        <span>MEMBER DETAILS (BILLED TO)</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-[11px] sm:text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Full Name:</span>
                        <span class="font-bold text-slate-900 col-span-7 sm:col-span-8 truncate">{{ $member->full_name }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-[11px] sm:text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Member ID:</span>
                        <span class="font-bold text-slate-900 col-span-7 sm:col-span-8">{{ $member->member_code }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-[11px] sm:text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Phone No:</span>
                        <span class="font-bold text-slate-900 col-span-7 sm:col-span-8">{{ $member->phone }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-[11px] sm:text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Payment Mode:</span>
                        <span class="font-bold uppercase text-slate-900 col-span-7 sm:col-span-8 inline-flex items-center gap-1.5">
                            <span>{{ $modeText }}</span>
                            @auth
                                @if((auth()->user()->isGymOwner() || auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('payments.edit_method')) && !request()->routeIs('app.invoices.public'))
                                    <button type="button" 
                                            @click="showEditMethodModal = true" 
                                            title="Gym Owner: Change Payment Mode" 
                                            class="no-print px-1.5 py-0.5 rounded bg-amber-100 hover:bg-amber-200 text-amber-900 text-[10px] font-bold cursor-pointer inline-flex items-center gap-0.5 transition-colors">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        <span>Edit</span>
                                    </button>
                                @endif
                            @endauth
                        </span>
                    </div>
                </div>

                <!-- Card 2: MEMBERSHIP & PLAN DETAILS -->
                <div class="p-2.5 sm:p-3.5 rounded-xl sm:rounded-2xl bg-slate-50/90 border border-slate-100/90 space-y-1">
                    <div class="flex items-center gap-1.5 text-[11px] sm:text-xs font-black text-slate-900 uppercase tracking-wider mb-1">
                        <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-red-500 inline-block shrink-0"></span>
                        <span>MEMBERSHIP &amp; PLAN DETAILS</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-[11px] sm:text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Duration:</span>
                        <span class="font-bold text-slate-900 col-span-7 sm:col-span-8">{{ $durationFormatted }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-[11px] sm:text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Start Date:</span>
                        <span class="font-bold text-slate-900 col-span-7 sm:col-span-8">{{ $startDate }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-[11px] sm:text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Expiry Date:</span>
                        <span class="font-bold text-slate-900 col-span-7 sm:col-span-8">{{ $endDate }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-1 text-[11px] sm:text-xs">
                        <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Status:</span>
                        <span class="font-bold text-emerald-600 col-span-7 sm:col-span-8">{{ $membershipStatus }}</span>
                    </div>
                </div>

            </div>

            <!-- ══════════════════════════════════════════
                 3. SERVICE / ITEM DESCRIPTION TABLE
            ══════════════════════════════════════════ -->

            <!-- Mobile Card View (< 640px, Hidden in Print) -->
            <div class="block sm:hidden print:hidden rounded-xl bg-slate-950 text-white p-3 space-y-2 shadow-sm">
                <div class="flex items-start justify-between gap-2 border-b border-slate-800 pb-2">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded bg-slate-800 text-[10px] font-bold text-slate-300">#1</span>
                            <span class="font-black text-xs text-white truncate block">{{ $plan->name ?? 'Gym Membership' }}</span>
                        </div>
                        @if(!empty($cleanNote))
                            <span class="text-[10.5px] text-amber-300/90 block mt-1 leading-snug">{{ $cleanNote }}</span>
                        @endif
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-amber-400/10 border border-amber-400/20 text-amber-300 text-[10px] font-black uppercase tracking-wider shrink-0">
                        {{ $durationFormatted }}
                    </span>
                </div>

                <div class="flex items-center justify-between text-xs pt-0.5">
                    <div class="text-slate-400 text-[11px]">
                        <span>Rate: </span>
                        <span class="text-slate-200 font-semibold">{{ $currency }}{{ number_format($baseRate, 0) }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[9.5px] text-slate-400 uppercase tracking-wider block">Total Amount</span>
                        <span class="text-sm font-black text-amber-400">{{ $currency }}{{ number_format($totalPackageAmount, 0) }}</span>
                    </div>
                </div>
            </div>

            <!-- Desktop & Print Table View -->
            <div class="hidden sm:block print:block w-full overflow-x-auto">
                <table class="w-full text-left border-collapse">
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
                                @if(!empty($cleanNote))
                                    <span class="text-[11px] text-slate-500 block mt-0.5">{{ $cleanNote }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center font-medium text-slate-700 text-xs">{{ $durationFormatted }}</td>
                            <td class="py-3 px-3 text-right font-medium text-slate-700 text-xs">{{ $currency }}{{ number_format($baseRate, 0) }}</td>
                            <td class="py-3 px-3 text-right font-bold text-slate-950 text-xs sm:text-sm">{{ $currency }}{{ number_format($totalPackageAmount, 0) }}</td>
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
                        <span class="font-bold text-slate-900 col-span-6 sm:col-span-7 uppercase">
                            {{ $modeText }}
                        </span>
                    </div>
                    @if($isSplit && !empty($breakdownText))
                        <div class="py-1 px-2.5 rounded-lg bg-amber-50 border border-amber-200 text-[11px] text-amber-900 font-bold my-1">
                            <span class="text-[9px] text-amber-700 uppercase tracking-wider block font-extrabold">Payment Breakdown:</span>
                            <span>{{ $breakdownText }}</span>
                        </div>
                    @endif

                    @php
                        $allPayments = $membership ? $membership->payments()->orderBy('payment_date', 'asc')->orderBy('id', 'asc')->get() : collect([$payment]);
                    @endphp

                    @if($allPayments->count() > 1)
                        <div class="pt-1.5 mt-1 border-t border-slate-200/80 space-y-1">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Payment Installments:</span>
                            @foreach($allPayments as $idx => $pItem)
                                <div class="flex items-center justify-between text-[11px] py-0.5">
                                    <span class="text-slate-600 font-medium">
                                        #{{ $idx + 1 }} • {{ strtoupper($pItem->payment_method) }} ({{ $pItem->payment_date ? $pItem->payment_date->format('d M Y') : $pItem->created_at->format('d M Y') }}):
                                    </span>
                                    <span class="font-bold text-slate-900">{{ $currency }}{{ number_format($pItem->amount, 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="grid grid-cols-12 gap-1 text-xs pt-1">
                        <span class="text-slate-500 font-normal col-span-6 sm:col-span-5">Payment Status:</span>
                        <span class="font-bold {{ $balanceDue > 0 ? 'text-amber-600' : 'text-emerald-600' }} col-span-6 sm:col-span-7">
                            {{ $balanceDue > 0 ? 'Partially Paid' : 'Completed' }}
                        </span>
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
                        <span class="font-medium text-slate-900">{{ $currency }}{{ number_format($baseRate, 0) }}</span>
                    </div>
                    @if($discountAmount > 0)
                        <div class="flex items-center justify-between text-xs text-rose-600 font-medium">
                            <span>Discount</span>
                            <span>-{{ $currency }}{{ number_format($discountAmount, 0) }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-normal">Taxes &amp; Gym Surcharge</span>
                        <span class="font-normal text-slate-600">{{ $currency }}0 (Inclusive)</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200">
                        <span class="text-xs sm:text-sm font-bold text-slate-950">Total Amount</span>
                        <span class="text-xs sm:text-sm font-black text-slate-950">{{ $currency }}{{ number_format($totalPackageAmount, 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-normal">Amount Received</span>
                        <span class="font-bold text-emerald-600">{{ $currency }}{{ number_format($amountReceivedDisplay, 0) }}</span>
                    </div>
                    
                    <!-- Balance Due Box -->
                    @if($balanceDue > 0)
                        <div class="mt-1 p-2 rounded-lg bg-amber-50 border border-amber-300 flex items-center justify-between text-amber-950">
                            <span class="font-bold text-xs uppercase tracking-wider text-amber-900">Balance Due</span>
                            <span class="font-black text-xs sm:text-sm text-amber-600">{{ $currency }}{{ number_format($balanceDue, 0) }}</span>
                        </div>
                    @else
                        <div class="mt-1 p-2 rounded-lg bg-emerald-50/80 border border-emerald-200/70 flex items-center justify-between text-emerald-950">
                            <span class="font-bold text-xs uppercase tracking-wider text-emerald-950">Balance Due</span>
                            <span class="font-black text-xs sm:text-sm text-emerald-600">{{ $currency }}0</span>
                        </div>
                    @endif
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
                    <div class="text-center sm:text-left space-y-0.5 sm:space-y-1 w-full sm:w-auto">
                        <p class="font-bold text-xs sm:text-sm text-slate-950">{{ $thankYouText }}</p>
                        <div class="text-[10px] sm:text-xs text-slate-600 flex flex-wrap items-center justify-center sm:justify-start gap-x-2 gap-y-0.5 font-normal mt-0.5">
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
                @if(!empty(trim($tagline)))
                <div class="pt-2 sm:pt-2.5 border-t border-slate-200 text-center">
                    <p class="text-[11px] sm:text-xs font-black tracking-widest text-slate-950 uppercase">{{ $tagline }}</p>
                </div>
                @endif
            </div>

        </div>

    </div>

    @auth
        @if((auth()->user()->isGymOwner() || auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('payments.edit_method')) && !request()->routeIs('app.invoices.public'))
            <!-- ==================== EDIT PAYMENT METHOD MODAL ==================== -->
            <div x-show="showEditMethodModal" 
                 x-cloak 
                 class="no-print fixed inset-0 z-50 overflow-y-auto"
                 @keydown.escape.window="showEditMethodModal = false">
                
                <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" 
                     @click="showEditMethodModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
                    <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all border border-slate-200"
                         @click.stop>
                        
                        <!-- Header -->
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 bg-amber-500/10">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-700 flex items-center justify-center font-black">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-sm sm:text-base font-black text-slate-900">Change Payment Mode</h3>
                                    <p class="text-[11px] text-amber-800 font-medium">Gym Owner Only &bull; Instant Invoice Sync</p>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="showEditMethodModal = false" 
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Form -->
                        <form action="{{ route('app.payments.update-method', $payment->id) }}" method="POST" class="p-5 space-y-4">
                            @csrf

                            <!-- Info Box -->
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Invoice:</span>
                                    <strong class="font-mono text-slate-900">{{ $invoiceNo }}</strong>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Member:</span>
                                    <strong class="text-slate-900">{{ $member->full_name }}</strong>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Amount Paid:</span>
                                    <strong class="font-mono text-emerald-600 font-bold">{{ $currency }}{{ number_format($payment->amount, 2) }}</strong>
                                </div>
                            </div>

                            <!-- Mode Select -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Select Correct Payment Mode <span class="text-rose-500">*</span>
                                </label>
                                <select name="payment_method" 
                                        x-model="selectedMethod" 
                                        required 
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold focus:border-amber-500 focus:outline-none cursor-pointer">
                                    <option value="cash">💵 Cash</option>
                                    <option value="upi">📱 UPI / QR Code</option>
                                    <option value="card">💳 Card (POS Machine)</option>
                                    <option value="netbanking">🌐 Net Banking</option>
                                    <option value="cheque">📄 Cheque</option>
                                    <option value="bank_transfer">🏦 Bank Transfer (NEFT/IMPS)</option>
                                </select>
                            </div>

                            <!-- Ref -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Transaction Ref / UTR No. <span class="text-slate-400 font-normal">(Optional)</span>
                                </label>
                                <input type="text" 
                                       name="transaction_reference" 
                                       x-model="txnRef" 
                                       placeholder="e.g. UPI Ref, POS Auth Code" 
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-mono focus:border-amber-500 focus:outline-none">
                            </div>

                            <!-- Reason -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Reason for Correction <span class="text-slate-400 font-normal">(Optional)</span>
                                </label>
                                <input type="text" 
                                       name="change_reason" 
                                       x-model="changeReason" 
                                       placeholder="e.g. Member paid by UPI, was mistakenly saved as Cash" 
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:border-amber-500 focus:outline-none">
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200">
                                <button type="button" 
                                        @click="showEditMethodModal = false" 
                                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs transition-colors shadow-md cursor-pointer flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Update Invoice Mode</span>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        @endif
    @endauth

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
