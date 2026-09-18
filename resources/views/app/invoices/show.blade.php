<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice - {{ $payment->receipt_number ?? ('RCP'.$payment->id) }} - {{ $tenant->name ?? 'Gym Console' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .invoice-paper {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 20px !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen py-8 px-4 font-sans antialiased flex flex-col items-center">

    @php
        $currency = $tenant->currency_symbol ?? '₹';
        $receiptNo = $payment->receipt_number ?? ($payment->invoice_number ?? ('RCP' . str_pad($payment->id, 8, '0', STR_PAD_LEFT)));
        $paymentDate = $payment->payment_date ? $payment->payment_date->format('d M Y') : $payment->created_at->format('d M Y');
        $logoUrl = $tenant->logo_url ?? ($platformSettings['logo_url'] ?? null);
    @endphp

    <!-- Top Action Toolbar -->
    <div class="no-print w-full max-w-2xl flex items-center justify-between mb-6">
        <a href="{{ route('app.members.show', $member->id) }}" 
           class="px-4 py-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800 text-xs font-bold transition-all shadow-sm">
            Close
        </a>

        <div class="flex items-center gap-2.5">
            <button type="button" 
                    onclick="window.print()" 
                    class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print</span>
            </button>

            <button type="button" 
                    onclick="window.print()" 
                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/30 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Download PDF</span>
            </button>
        </div>
    </div>

    <!-- Invoice Paper Document (A4 Proportion) -->
    <div class="invoice-paper relative w-full max-w-2xl min-h-[920px] bg-white text-slate-900 rounded-2xl shadow-2xl p-8 sm:p-12 overflow-hidden border border-slate-200 flex flex-col justify-between">
        
        <!-- Faded Center/Bottom Background Logo Watermark -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.06] select-none">
            @if(!empty($logoUrl))
                <img src="{{ $logoUrl }}" alt="" class="w-80 h-80 object-contain grayscale">
            @else
                <div class="text-center font-black tracking-widest text-7xl uppercase text-slate-900 rotate-[-15deg]">
                    {{ $tenant->name ?? 'GYM CONSOLE' }}
                </div>
            @endif
        </div>

        <div class="relative z-10 space-y-8">
            
            <!-- Header Section: Logo, Gym Info & INVOICE Meta -->
            <div class="flex items-start justify-between border-b border-slate-200 pb-6">
                <!-- Gym Brand Left -->
                <div class="flex items-center gap-3.5">
                    @if(!empty($logoUrl))
                        <img src="{{ $logoUrl }}" alt="{{ $tenant->name ?? 'Gym Logo' }}" class="h-16 w-auto max-w-[140px] object-contain shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-2xl bg-slate-950 text-amber-400 flex items-center justify-center font-black text-2xl shadow-md shrink-0">
                            🏋️
                        </div>
                    @endif
                    <div>
                        <h2 class="text-xl font-black text-slate-950 tracking-tight">{{ $tenant->name ?? 'PowerFit Gym' }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $member->branch->address ?? ($tenant->address ?? '123 Fitness Street, Health City') }}</p>
                        @if(!empty($tenant->phone) || !empty($tenant->email))
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $tenant->phone ?? '' }} @if(!empty($tenant->phone) && !empty($tenant->email)) • @endif {{ $tenant->email ?? '' }}</p>
                        @endif
                        @if(!empty($tenant->gst_number))
                            <p class="text-[10px] font-mono text-slate-500 mt-0.5 font-bold">GSTIN: {{ $tenant->gst_number }}</p>
                        @endif
                    </div>
                </div>

                <!-- Invoice Details Right -->
                <div class="text-right">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">INVOICE</h1>
                    <p class="text-xs font-mono font-bold text-slate-500 mt-1">{{ $receiptNo }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $paymentDate }}</p>
                </div>
            </div>

            <!-- Bill To & Plan Details (2 Columns) -->
            <div class="grid grid-cols-2 gap-6 text-xs border-b border-slate-200 pb-6">
                <!-- Bill To Column -->
                <div>
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">BILL TO</span>
                    <h3 class="text-sm font-black text-slate-950">{{ $member->full_name }}</h3>
                    <p class="text-xs font-mono font-semibold text-slate-500 mt-0.5">{{ $member->member_code }}</p>
                    <p class="text-xs text-slate-600 mt-0.5">{{ $member->phone }}</p>
                </div>

                <!-- Plan Details Column -->
                <div>
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">PLAN DETAILS</span>
                    <h3 class="text-sm font-black text-slate-950">{{ $plan->name ?? 'Gym Membership' }}</h3>
                    @if($membership)
                        <p class="text-xs text-slate-600 mt-0.5">{{ $membership->start_date->format('d M Y') }}</p>
                        <p class="text-xs text-slate-600 mt-0.5">{{ $membership->end_date->format('d M Y') }}</p>
                    @endif
                </div>
            </div>

            <!-- Items & Amount Table -->
            <div>
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b-2 border-slate-200 text-slate-500 font-extrabold text-[10px] uppercase tracking-wider">
                            <th class="py-3">DESCRIPTION</th>
                            <th class="py-3 text-right">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="py-4">
                                <span class="font-bold text-slate-900 block text-sm">{{ $plan->name ?? 'Gym Membership' }}</span>
                                @if($membership)
                                    <span class="text-slate-500 text-[11px] block mt-0.5">
                                        {{ $plan ? ($plan->duration_value . ' ' . $plan->duration_type . ' • ') : '' }}{{ $membership->start_date->format('d M Y') }} to {{ $membership->end_date->format('d M Y') }}
                                    </span>
                                @endif
                                @if(!empty($payment->payment_method))
                                    <span class="text-slate-400 text-[10px] block mt-0.5">Payment Method: {{ strtoupper($payment->payment_method) }} @if($payment->transaction_reference) — Ref: {{ $payment->transaction_reference }} @endif</span>
                                @endif
                            </td>
                            <td class="py-4 text-right font-black text-slate-900 text-sm">
                                {{ $currency }}{{ number_format($payment->amount, 2) }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200 text-xs">
                        <tr>
                            <td class="py-3 font-bold text-slate-600">Total</td>
                            <td class="py-3 text-right font-black text-slate-900 text-sm">
                                {{ $currency }}{{ number_format($payment->amount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 font-bold text-emerald-700">Paid</td>
                            <td class="py-2 text-right font-black text-emerald-600 text-base">
                                {{ $currency }}{{ number_format($payment->amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>

        <!-- Bottom Note / Thank You Footer -->
        <div class="relative z-10 pt-8 border-t border-slate-100 text-center mt-auto">
            <p class="text-xs font-semibold text-slate-500">Thank you for your membership!</p>
            <p class="text-[10px] text-slate-400 mt-1">Generated by {{ $tenant->name ?? 'Gym Console' }} — All rights reserved.</p>
        </div>
    </div>

</body>
</html>
