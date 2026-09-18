<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Member Report — {{ $tenant->name ?? 'PowerFit Gym' }} — {{ $periodLabel }}</title>
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
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 5px 8px;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen py-8 px-4 font-sans antialiased flex flex-col items-center">

    <!-- Top Action Toolbar -->
    <div class="no-print w-full max-w-4xl flex items-center justify-between mb-6">
        <a href="{{ route('app.finance.member-report', request()->query()) }}" 
           class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 text-xs font-bold transition-all shadow-sm">
            ← Back to Member Report
        </a>

        <div class="flex items-center gap-2.5">
            <button type="button" 
                    onclick="window.print()" 
                    class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Report</span>
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
    <div class="statement-paper w-full max-w-4xl bg-white text-black rounded-lg shadow-2xl p-8 sm:p-10 border border-slate-300 space-y-6 text-xs leading-normal">
        
        <!-- Header & Business Details -->
        <div class="space-y-3">
            <h1 class="text-xl sm:text-2xl font-black text-black tracking-tight">
                Member Report &mdash; {{ $tenant->name ?? 'PowerFit Gym' }} &mdash; {{ $periodLabel }}
            </h1>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs font-medium pt-1">
                <div>
                    <span class="font-bold text-black">Business</span>
                    <div class="text-slate-900">{{ $tenant->name ?? 'PowerFit Gym' }}</div>
                </div>
                <div>
                    <span class="font-bold text-black">GSTIN</span>
                    <div class="text-slate-900 font-mono">{{ $tenant->gst_number ?? '' }}</div>
                </div>
                <div>
                    <span class="font-bold text-black">Report</span>
                    <div class="text-slate-900">Member Report &mdash; {{ $tenant->name ?? 'PowerFit Gym' }}</div>
                </div>
                <div>
                    <span class="font-bold text-black">Period</span>
                    <div class="text-slate-900">{{ $periodLabel }}</div>
                </div>
            </div>

            <div class="border-b-2 border-black pt-1"></div>
        </div>

        <!-- 1. Key Metrics -->
        <div class="space-y-2">
            <h3 class="font-bold text-sm text-black uppercase tracking-wide">Key Metrics</h3>
            <table class="text-left text-xs">
                <thead>
                    <tr class="text-[10px] uppercase font-bold text-slate-900 bg-slate-50">
                        <th class="py-2 px-3 border border-black">TOTAL MEMBERS</th>
                        <th class="py-2 px-3 border border-black">ACTIVE</th>
                        <th class="py-2 px-3 border border-black">EXPIRED</th>
                        <th class="py-2 px-3 border border-black">FROZEN</th>
                        <th class="py-2 px-3 border border-black">NEW THIS MONTH</th>
                        <th class="py-2 px-3 border border-black">TODAY'S CHECK-INS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="font-black text-sm text-black">
                        <td class="py-2.5 px-3 border border-black">{{ $totalMembers }}</td>
                        <td class="py-2.5 px-3 border border-black">{{ $activeMembers }}</td>
                        <td class="py-2.5 px-3 border border-black">{{ $expiredMembers }}</td>
                        <td class="py-2.5 px-3 border border-black">{{ $frozenMembers }}</td>
                        <td class="py-2.5 px-3 border border-black">{{ $newThisMonth }}</td>
                        <td class="py-2.5 px-3 border border-black">{{ $todayCheckins }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 2. Attendance Summary -->
        <div class="space-y-2">
            <h3 class="font-bold text-sm text-black uppercase tracking-wide">Attendance Summary</h3>
            <table class="text-left text-xs">
                <thead>
                    <tr class="text-[10px] uppercase font-bold text-slate-900 bg-slate-50">
                        <th class="py-2 px-3 border border-black">TODAY CHECK-INS</th>
                        <th class="py-2 px-3 border border-black">CURRENTLY IN</th>
                        <th class="py-2 px-3 border border-black">CHECKED OUT</th>
                        <th class="py-2 px-3 border border-black">WEEKLY UNIQUE</th>
                        <th class="py-2 px-3 border border-black">AVG SESSION</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="font-black text-sm text-black">
                        <td class="py-2.5 px-3 border border-black">{{ $todayCheckins }}</td>
                        <td class="py-2.5 px-3 border border-black">{{ $currentlyIn }}</td>
                        <td class="py-2.5 px-3 border border-black">{{ $checkedOut }}</td>
                        <td class="py-2.5 px-3 border border-black">{{ $uniqueThisWeek }}</td>
                        <td class="py-2.5 px-3 border border-black">{{ $avgSession }} min</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 3. Membership Breakdown -->
        <div class="space-y-4">
            <h3 class="font-bold text-sm text-black uppercase tracking-wide border-b border-black pb-1">Membership Breakdown</h3>
            
            <!-- Plan Distribution -->
            <div class="space-y-1.5">
                <h4 class="font-bold text-xs text-black">Plan Distribution</h4>
                <table class="text-xs">
                    <thead>
                        <tr class="text-[10px] uppercase font-bold text-slate-900 bg-slate-50">
                            <th class="py-1.5 px-3 border border-black text-left w-1/2">Plan</th>
                            <th class="py-1.5 px-3 border border-black text-right w-1/4">Members</th>
                            <th class="py-1.5 px-3 border border-black text-right w-1/4">Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalAssigned = $plans->sum('memberships_count');
                        @endphp
                        @forelse($plans as $p)
                            @php
                                $share = $totalAssigned > 0 ? round(($p->memberships_count / $totalAssigned) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td class="py-1 px-3 border border-black text-left font-medium">{{ $p->name }}</td>
                                <td class="py-1 px-3 border border-black text-right font-medium">{{ $p->memberships_count }}</td>
                                <td class="py-1 px-3 border border-black text-right text-slate-800">{{ $share }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-3 px-3 border border-black text-center text-slate-500">No active membership plans found.</td>
                            </tr>
                        @endforelse
                        <tr class="font-bold bg-slate-50">
                            <td class="py-1.5 px-3 border border-black text-left">Total</td>
                            <td class="py-1.5 px-3 border border-black text-right">{{ $totalAssigned }}</td>
                            <td class="py-1.5 px-3 border border-black text-right">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Gender Distribution -->
            <div class="space-y-1.5 pt-1">
                <h4 class="font-bold text-xs text-black">Gender Distribution</h4>
                <table class="text-left text-xs">
                    <thead>
                        <tr class="text-[10px] uppercase font-bold text-slate-900 bg-slate-50">
                            <th class="py-1.5 px-3 border border-black w-1/3">MALE</th>
                            <th class="py-1.5 px-3 border border-black w-1/3">FEMALE</th>
                            <th class="py-1.5 px-3 border border-black w-1/3">OTHER / N/A</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="font-black text-sm text-black">
                            <td class="py-2 px-3 border border-black">{{ $maleCount }}</td>
                            <td class="py-2 px-3 border border-black">{{ $femaleCount }}</td>
                            <td class="py-2 px-3 border border-black">{{ $otherCount }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Expiring Soon -->
        <div class="space-y-2">
            <h3 class="font-bold text-sm text-black uppercase tracking-wide">
                Expiring Soon &mdash; Next 7 Days ({{ $expiringSoon->count() }})
            </h3>
            <table class="text-xs">
                <thead>
                    <tr class="text-[10px] uppercase font-bold text-slate-900 bg-slate-50">
                        <th class="py-1.5 px-3 border border-black text-left">Name</th>
                        <th class="py-1.5 px-3 border border-black text-left">Phone</th>
                        <th class="py-1.5 px-3 border border-black text-left">Plan</th>
                        <th class="py-1.5 px-3 border border-black text-left">Expires On</th>
                        <th class="py-1.5 px-3 border border-black text-right">Days Left</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expiringSoon as $exp)
                        @php
                            $dl = max(0, \Carbon\Carbon::parse($exp->end_date)->diffInDays(now()));
                            $dl = max(0, (int) round(\Carbon\Carbon::parse($exp->end_date)->startOfDay()->diffInDays(now()->startOfDay())));
                        @endphp
                        <tr>
                            <td class="py-1 px-3 border border-black font-bold text-black">{{ $exp->member?->full_name }}</td>
                            <td class="py-1 px-3 border border-black font-mono text-slate-800">{{ $exp->member?->phone }}</td>
                            <td class="py-1 px-3 border border-black text-slate-800">{{ $exp->plan?->name ?? 'Standard' }}</td>
                            <td class="py-1 px-3 border border-black text-slate-800">{{ \Carbon\Carbon::parse($exp->end_date)->format('d M Y') }}</td>
                            <td class="py-1 px-3 border border-black text-right font-bold text-black">{{ $dl }}d</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-3 px-3 border border-black text-center text-slate-500">No memberships expiring within the next 7 days.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 5. New Members in Period -->
        <div class="space-y-2">
            <h3 class="font-bold text-sm text-black uppercase tracking-wide">
                New Members &mdash; {{ $periodLabel }} ({{ $newMembers->count() }})
            </h3>
            <table class="text-xs">
                <thead>
                    <tr class="text-[10px] uppercase font-bold text-slate-900 bg-slate-50">
                        <th class="py-1.5 px-3 border border-black text-left">Name</th>
                        <th class="py-1.5 px-3 border border-black text-left">Phone</th>
                        <th class="py-1.5 px-3 border border-black text-left">Plan</th>
                        <th class="py-1.5 px-3 border border-black text-left">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($newMembers as $nm)
                        <tr>
                            <td class="py-1 px-3 border border-black font-bold text-black">{{ $nm->full_name }}</td>
                            <td class="py-1 px-3 border border-black font-mono text-slate-800">{{ $nm->phone }}</td>
                            <td class="py-1 px-3 border border-black text-slate-800">{{ $nm->activeMembership?->plan?->name ?? 'No Plan' }}</td>
                            <td class="py-1 px-3 border border-black text-slate-800">{{ $nm->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-3 px-3 border border-black text-center text-slate-500">No new members registered in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 6. Demographics -->
        <div class="space-y-4 pt-2">
            <h3 class="font-bold text-sm text-black uppercase tracking-wide border-b border-black pb-1">Demographics</h3>

            <!-- Age Groups -->
            <div class="space-y-1.5">
                <h4 class="font-bold text-xs text-black">Age Groups</h4>
                <table class="text-xs">
                    <thead>
                        <tr class="text-[10px] uppercase font-bold text-slate-900 bg-slate-50">
                            <th class="py-1.5 px-3 border border-black text-left w-2/3">Age Group</th>
                            <th class="py-1.5 px-3 border border-black text-right w-1/3">Members</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ageGroups as $grp => $cnt)
                            <tr>
                                <td class="py-1 px-3 border border-black text-left font-medium">{{ $grp }}</td>
                                <td class="py-1 px-3 border border-black text-right font-medium">{{ $cnt }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Referral Sources -->
            <div class="space-y-1.5 pt-1">
                <h4 class="font-bold text-xs text-black">Referral Sources</h4>
                <table class="text-xs">
                    <thead>
                        <tr class="text-[10px] uppercase font-bold text-slate-900 bg-slate-50">
                            <th class="py-1.5 px-3 border border-black text-left w-2/3">Source</th>
                            <th class="py-1.5 px-3 border border-black text-right w-1/3">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sources as $src => $cnt)
                            <tr>
                                <td class="py-1 px-3 border border-black text-left font-medium">{{ $src }}</td>
                                <td class="py-1 px-3 border border-black text-right font-medium">{{ $cnt }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-2 px-3 border border-black text-center text-slate-500">No referral source data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Fitness Goals -->
            <div class="space-y-1.5 pt-1">
                <h4 class="font-bold text-xs text-black">Fitness Goals</h4>
                <table class="text-xs">
                    <thead>
                        <tr class="text-[10px] uppercase font-bold text-slate-900 bg-slate-50">
                            <th class="py-1.5 px-3 border border-black text-left w-2/3">Goal</th>
                            <th class="py-1.5 px-3 border border-black text-right w-1/3">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($goals as $g => $cnt)
                            <tr>
                                <td class="py-1 px-3 border border-black text-left font-medium">{{ $g }}</td>
                                <td class="py-1 px-3 border border-black text-right font-medium">{{ $cnt }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-2 px-3 border border-black text-center text-slate-500">No fitness goals data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- System-generated Footer -->
        <div class="pt-6 border-t border-slate-300 text-center text-[10px] text-slate-600">
            Generated {{ now()->format('d M Y, H:i') }} &middot; This is a system-generated report.
        </div>

    </div>

</body>
</html>

