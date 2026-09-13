<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Income & Expenditure Account - {{ $tenant->name ?? 'PowerFit Gym' }} - {{ $periodLabel }}</title>
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
            .statement-paper {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 10px !important;
            }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen py-8 px-4 font-sans antialiased flex flex-col items-center">

    <!-- Top Action Toolbar -->
    <div class="no-print w-full max-w-4xl flex items-center justify-between mb-6">
        <a href="{{ route('app.finance.balance-sheet') }}" 
           class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 text-xs font-bold transition-all shadow-sm">
            ← Back to Balance Sheet
        </a>

        <div class="flex items-center gap-2.5">
            <button type="button" 
                    onclick="window.print()" 
                    class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Statement</span>
            </button>

            <button type="button" 
                    onclick="window.print()" 
                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/30 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Download PDF</span>
            </button>
        </div>
    </div>

    <!-- Printable Statement Paper -->
    <div class="statement-paper w-full max-w-4xl bg-white text-black rounded-lg shadow-2xl p-8 sm:p-10 border border-slate-300 space-y-5 text-xs">
        
        <!-- Top Title & Meta Block -->
        <div class="space-y-3">
            <h1 class="text-xl sm:text-2xl font-black text-black tracking-tight">
                Income &amp; Expenditure &mdash; {{ $tenant->name ?? 'PowerFit Gym' }} &mdash; {{ $periodLabel }}
            </h1>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs font-medium pt-1">
                <div>
                    <span class="font-bold text-black">Business</span>
                    <div class="text-slate-800">{{ $tenant->name ?? 'PowerFit Gym' }}</div>
                </div>
                <div>
                    <span class="font-bold text-black">GSTIN</span>
                    <div class="text-slate-800 font-mono">{{ $tenant->gst_number ?? '' }}</div>
                </div>
                <div>
                    <span class="font-bold text-black">Report</span>
                    <div class="text-slate-800">Income &amp; Expenditure &mdash; {{ $tenant->name ?? 'PowerFit Gym' }}</div>
                </div>
                <div>
                    <span class="font-bold text-black">Period</span>
                    <div class="text-slate-800">{{ $periodLabel }}</div>
                </div>
            </div>

            <div class="border-b-2 border-black pt-1"></div>
        </div>

        <!-- Gym Identity Box -->
        <div class="border border-black p-4 text-center space-y-0.5">
            <h2 class="text-base font-black tracking-widest text-black uppercase">{{ $tenant->name ?? 'POWERFIT GYM' }}</h2>
            <p class="text-xs text-slate-800">{{ $activeBranch->address ?? ($tenant->address ?? '123 Fitness Street, Health City') }}</p>
            <p class="text-[11px] text-slate-700">Tel: {{ $tenant->phone ?? '080 6940 9814' }} | {{ $tenant->email ?? 'info@powerfitgym.com' }}</p>
        </div>

        <!-- Black Header Banner -->
        <div class="bg-black text-white text-center py-2.5 px-4">
            <div class="font-black text-sm tracking-wider uppercase">INCOME AND EXPENDITURE ACCOUNT</div>
            <div class="text-[10px] text-slate-300 mt-0.5">For the period {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
        </div>

        <!-- T-Account Table (Expenditure vs Income) -->
        @php
            $expList = [];
            foreach ($itemizedExpenses as $name => $amount) {
                if ($amount > 0) {
                    $expList[] = ['name' => $name, 'amount' => $amount];
                }
            }

            $incList = [];
            if ($membershipIncome > 0) {
                $incList[] = ['name' => 'Membership Subscriptions', 'amount' => $membershipIncome];
            }
            if (($serviceIncome ?? 0) > 0) {
                $incList[] = ['name' => 'Gym Services & Amenities (Lockers, Spa)', 'amount' => $serviceIncome];
            }
            if (($ptIncome ?? 0) > 0) {
                $incList[] = ['name' => 'Personal Training (PT) Packages', 'amount' => $ptIncome];
            }
            if ($posSales > 0) {
                $incList[] = ['name' => 'Sale of Goods (POS)', 'amount' => $posSales];
            }
            if (empty($incList)) {
                $incList[] = ['name' => 'Total Receipts', 'amount' => $totalIncome];
            }

            $rowCount = max(count($expList), count($incList), 3);
        @endphp

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-slate-100 border-b border-black">
                        <th class="border-r border-black py-2 px-3 text-left font-black uppercase w-5/12">EXPENDITURE</th>
                        <th class="border-r border-black py-2 px-3 text-right font-black uppercase w-1/12">₹</th>
                        <th class="border-r border-black py-2 px-3 text-left font-black uppercase w-5/12">INCOME</th>
                        <th class="py-2 px-3 text-right font-black uppercase w-1/12">₹</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/40">
                    @for($i = 0; $i < $rowCount; $i++)
                        <tr>
                            <!-- Left: Expenditure -->
                            <td class="border-r border-black py-2 px-3 align-top font-medium">
                                @if(isset($expList[$i]))
                                    {{ $expList[$i]['name'] }}
                                @elseif($i === 0 && count($expList) === 0)
                                    <span class="italic text-slate-700">Nil</span>
                                @endif
                            </td>
                            <td class="border-r border-black py-2 px-3 text-right align-top font-mono">
                                @if(isset($expList[$i]))
                                    {{ number_format($expList[$i]['amount'], 2) }}
                                @elseif($i === 0 && count($expList) === 0)
                                    -
                                @endif
                            </td>

                            <!-- Right: Income -->
                            <td class="border-r border-black py-2 px-3 align-top font-medium">
                                @if(isset($incList[$i]))
                                    {{ $incList[$i]['name'] }}
                                @endif
                            </td>
                            <td class="py-2 px-3 text-right align-top font-mono">
                                @if(isset($incList[$i]))
                                    {{ number_format($incList[$i]['amount'], 2) }}
                                @endif
                            </td>
                        </tr>
                    @endfor

                    <!-- Subtotals Row: Total Expenditure (A) & Total Income (B) -->
                    <tr class="border-t-2 border-black font-bold bg-slate-50">
                        <td class="border-r border-black py-2 px-3 font-black">Total Expenditure (A)</td>
                        <td class="border-r border-black py-2 px-3 text-right font-black font-mono">
                            {{ $totalExpenses > 0 ? number_format($totalExpenses, 2) : '-' }}
                        </td>
                        <td class="border-r border-black py-2 px-3 font-black">Total Income (B)</td>
                        <td class="py-2 px-3 text-right font-black font-mono">
                            {{ number_format($totalIncome, 2) }}
                        </td>
                    </tr>

                    <!-- Surplus & Right Total -->
                    <tr class="border-t border-black">
                        <td class="border-r border-black py-2 px-3 font-bold italic bg-slate-100">
                            Surplus (Excess of Income over Expenditure)
                        </td>
                        <td class="border-r border-black py-2 px-3 text-right font-bold font-mono bg-slate-100">
                            {{ number_format($netProfit, 2) }}
                        </td>
                        <td class="border-r border-black py-2 px-3 font-black uppercase bg-black text-white">
                            TOTAL
                        </td>
                        <td class="py-2 px-3 text-right font-black font-mono bg-black text-white">
                            {{ number_format($totalIncome, 2) }}
                        </td>
                    </tr>

                    <!-- Left Total Row -->
                    <tr class="border-t border-black">
                        <td class="border-r border-black py-2 px-3 font-black uppercase bg-black text-white">
                            TOTAL
                        </td>
                        <td class="border-r border-black py-2 px-3 text-right font-black font-mono bg-black text-white">
                            {{ number_format($totalIncome, 2) }}
                        </td>
                        <td class="border-r border-black py-2 px-3 bg-white"></td>
                        <td class="py-2 px-3 bg-white"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary Box -->
        <div class="border border-black p-3.5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1 text-xs">
                <div class="font-bold text-black">Summary:</div>
                <div class="text-slate-800">Total Income (B): ₹ {{ number_format($totalIncome, 2) }}</div>
                <div class="text-slate-800">Total Expenditure (A): ₹ {{ $totalExpenses > 0 ? number_format($totalExpenses, 2) : '-' }}</div>
                <div class="font-bold text-black">Surplus (B-A): ₹ {{ number_format($netProfit, 2) }}</div>
            </div>
            <div class="text-left sm:text-right text-[11px] text-slate-700 italic flex flex-col justify-end space-y-0.5">
                <div>Amount in Indian Rupees (₹)</div>
                <div>Figures rounded to nearest rupee</div>
            </div>
        </div>

        <!-- Signatures & Authorization Box -->
        <div class="border border-black">
            <div class="grid grid-cols-3 divide-x divide-black text-center p-4 min-h-[90px] items-end">
                <div class="space-y-1">
                    <div class="border-t border-black mx-4"></div>
                    <div class="font-bold text-xs">Prepared By</div>
                </div>
                <div class="space-y-1">
                    <div class="border-t border-black mx-4"></div>
                    <div class="font-bold text-xs">Checked By</div>
                </div>
                <div class="space-y-0.5">
                    <div class="border-t border-black mx-4"></div>
                    <div class="font-bold text-xs">For {{ $tenant->name ?? 'PowerFit Gym' }}</div>
                    <div class="text-[10px] text-slate-600">(Authorised Signatory)</div>
                </div>
            </div>

            <!-- Footer Place/Date Bar -->
            <div class="border-t border-black grid grid-cols-2 divide-x divide-black px-4 py-2 text-[11px] bg-slate-50">
                <div>
                    <span>Place: ___________________</span>
                    <span class="ml-4">Date: {{ now()->format('d/m/Y') }}</span>
                </div>
                <div class="text-right text-slate-600">
                    This is a computer generated statement
                </div>
            </div>
        </div>

        <!-- System Generated Print Footer -->
        <div class="pt-2 border-t border-slate-400 text-[10px] text-slate-500 flex justify-between items-center">
            <span>Generated {{ now()->format('d M Y, H:i') }} &middot; This is a system-generated report.</span>
            <span>Page 1 of 1</span>
        </div>
    </div>

</body>
</html>

