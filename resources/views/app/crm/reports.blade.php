<x-app-layout header="CRM Reports">
    <div class="space-y-6">

        <!-- ==================== TOP ACTION HEADER ==================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span>CRM Reports</span>
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pipeline velocity, conversion funnels & sales efficiency</p>
                </div>
            </div>
        </div>

        <!-- ==================== FILTER TOOLBAR ==================== -->
        <form method="GET" action="{{ route('app.crm.reports') }}" class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 shadow-sm">
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-700 dark:text-slate-300">
                <!-- Date Range -->
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-500 dark:text-slate-400 text-[11px]">Date Range:</span>
                    <input type="date" name="start_date" value="{{ $startDateInput }}" class="px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                    <span class="text-slate-400 dark:text-slate-500 text-[11px]">to</span>
                    <input type="date" name="end_date" value="{{ $endDateInput }}" class="px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                </div>

                <!-- Employee Filter -->
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-500 dark:text-slate-400 text-[11px]">Employee:</span>
                    <select name="employee_id" class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                        <option value="all" {{ !$employeeId || $employeeId === 'all' ? 'selected' : '' }}>All employees</option>
                        @foreach($staffMembers as $staff)
                            <option value="{{ $staff->id }}" {{ $employeeId == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Branch Filter -->
                @if($branches->count() > 0)
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-slate-500 dark:text-slate-400 text-[11px]">Branch:</span>
                        <select name="branch_id" class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <option value="all" {{ !$branchId || $branchId === 'all' ? 'selected' : '' }}>All Branches</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Apply & Reset Buttons -->
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold flex items-center gap-1.5 shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Apply</span>
                </button>

                <a href="{{ route('app.crm.reports') }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:hover:text-white text-xs font-bold transition-all">
                    Reset
                </a>
            </div>

            <!-- Right: Gym Badge & CSV Export -->
            <div class="flex items-center gap-2.5">
                <span class="px-3 py-1.5 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 font-bold text-xs flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>{{ $tenant->name ?? 'Gym Console' }}</span>
                </span>

                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border border-slate-200 dark:bg-slate-950 dark:hover:bg-slate-800 dark:text-slate-300 dark:hover:text-white dark:border-slate-800 text-xs font-bold flex items-center gap-1.5 transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export CSV</span>
                </a>
            </div>
        </form>

        <!-- ==================== 4 TOP KPI METRIC CARDS ==================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- TOTAL LEADS -->
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center gap-4 shadow-sm">
                <div class="w-11 h-11 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ number_format($totalLeads) }}</div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Total Leads</div>
                </div>
            </div>

            <!-- CONVERTED -->
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center gap-4 shadow-sm">
                <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">{{ number_format($convertedCount) }}</div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Converted</div>
                </div>
            </div>

            <!-- LOST -->
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center gap-4 shadow-sm">
                <div class="w-11 h-11 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-rose-600 dark:text-rose-400 tracking-tight">{{ number_format($lostCount) }}</div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Lost</div>
                </div>
            </div>

            <!-- CONVERSION RATE -->
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center gap-4 shadow-sm">
                <div class="w-11 h-11 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center shrink-0 font-black text-sm">
                    %
                </div>
                <div>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight">{{ $conversionRate }}%</div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Conversion Rate</div>
                </div>
            </div>
        </div>

        <!-- ==================== 2-COLUMN ANALYTICS: SOURCES & STAGES ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- LEFT: Leads by Source -->
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        <span>Leads by Source</span>
                    </h2>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $totalLeads }} Total</span>
                </div>

                <div class="space-y-3">
                    @foreach($sourceCounts as $src)
                        @php
                            $barPct = $src['count'] > 0 ? max(4, round(($src['count'] / $maxSourceCount) * 100)) : 0;
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $src['label'] }}</span>
                                <span class="font-bold font-mono text-slate-900 dark:text-white text-xs">{{ $src['count'] }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 overflow-hidden">
                                <div class="h-full rounded-full bg-slate-700 dark:bg-slate-200 transition-all duration-500" style="width: {{ $barPct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- RIGHT: Leads by Stage (Current Distribution) -->
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Leads by Stage</span>
                    </h2>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Current distribution</span>
                </div>

                <div class="space-y-3.5">
                    @foreach($stageCounts as $stg)
                        @php
                            $stgPct = $stg['count'] > 0 ? max(5, $stg['pct']) : 0;
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $stg['dot'] }}"></span>
                                    <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $stg['label'] }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-white font-mono text-[11px] font-bold">
                                    {{ $stg['count'] }}
                                </span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 overflow-hidden">
                                <div class="h-full rounded-full {{ $stg['bg'] }} transition-all duration-500" style="width: {{ $stgPct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ==================== SECTION 3: CONVERSION FUNNEL ==================== -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Conversion Funnel</span>
                </h2>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Leads that reached at least each stage</span>
            </div>

            <div class="space-y-4 pt-1">
                @foreach($funnelData as $fnl)
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $fnl['label'] }}</span>
                            <span class="font-mono text-xs font-bold text-slate-900 dark:text-white">
                                {{ $fnl['count'] }} <span class="text-slate-500 dark:text-slate-400 font-normal">({{ $fnl['pct'] }}%)</span>
                            </span>
                        </div>
                        <div class="h-5 rounded-lg bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 overflow-hidden relative">
                            <div class="h-full rounded-lg {{ $fnl['color'] }} transition-all duration-500 flex items-center px-2" style="width: {{ max(4, $fnl['pct']) }}%">
                                @if($fnl['count'] > 0)
                                    <span class="text-[10px] font-black text-white font-mono drop-shadow">{{ $fnl['count'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ==================== SECTION 4: LEAD & CONVERSION TREND ==================== -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
                <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    <span>Lead &amp; Conversion Trend</span>
                </h2>
                <div class="flex items-center gap-4 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        <span class="text-slate-600 dark:text-slate-400 text-[11px]">New Leads</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-slate-600 dark:text-slate-400 text-[11px]">Conversions</span>
                    </div>
                </div>
            </div>

            <div class="h-64 w-full pt-2">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- ==================== SECTION 5: LEADS BY BRANCH ==================== -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Leads by Branch</span>
                </h2>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">All Branches, {{ \Carbon\Carbon::parse($startDateInput)->format('d M') }} – {{ \Carbon\Carbon::parse($endDateInput)->format('d M Y') }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                        <tr>
                            <th class="py-3 px-4">Branch</th>
                            <th class="py-3 px-4 text-center">Leads</th>
                            <th class="py-3 px-4 text-center">Converted</th>
                            <th class="py-3 px-4 text-center">Conversion %</th>
                            <th class="py-3 px-4 text-right">Conversion Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @php
                            $totalBranchLeads = 0;
                            $totalBranchConverted = 0;
                            $totalBranchMrr = 0;
                        @endphp
                        @forelse($branchReports as $br)
                            @php
                                $totalBranchLeads += $br['total_leads'];
                                $totalBranchConverted += $br['converted'];
                                $totalBranchMrr += $br['mrr'];
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">{{ $br['name'] }}</td>
                                <td class="py-3 px-4 text-center font-mono font-semibold">{{ $br['total_leads'] }}</td>
                                <td class="py-3 px-4 text-center font-mono font-semibold text-emerald-600 dark:text-emerald-400">{{ $br['converted'] }}</td>
                                <td class="py-3 px-4 text-center font-mono font-semibold text-amber-600 dark:text-amber-400">{{ $br['conversion_pct'] }}%</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $currency }}{{ number_format((float) $br['mrr']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 dark:text-slate-500 text-xs">
                                    No branch data available for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 font-bold text-xs text-slate-900 dark:text-white">
                        <tr>
                            <td class="py-3 px-4">Total</td>
                            <td class="py-3 px-4 text-center font-mono">{{ $totalBranchLeads }}</td>
                            <td class="py-3 px-4 text-center font-mono text-emerald-600 dark:text-emerald-400">{{ $totalBranchConverted }}</td>
                            <td class="py-3 px-4 text-center font-mono text-amber-600 dark:text-amber-400">
                                {{ $totalBranchLeads > 0 ? round(($totalBranchConverted / $totalBranchLeads) * 100, 1) : 0 }}%
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-emerald-600 dark:text-emerald-400">
                                {{ $currency }}{{ number_format((float) $totalBranchMrr) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ==================== SECTION 6: TEAM PERFORMANCE ==================== -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    <span>Team Performance</span>
                </h2>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ count($teamPerformance) }} Active staff members</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                        <tr>
                            <th class="py-3 px-4">Staff Member</th>
                            <th class="py-3 px-4 text-center">Assigned Leads</th>
                            <th class="py-3 px-4 text-center">Contacted</th>
                            <th class="py-3 px-4 text-center">Demos Booked</th>
                            <th class="py-3 px-4 text-center">Converted</th>
                            <th class="py-3 px-4 text-center">Conversion %</th>
                            <th class="py-3 px-4 text-right">Conversion Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @forelse($teamPerformance as $tp)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $tp['name'] }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 capitalize">{{ $tp['role'] }}</div>
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-semibold">{{ $tp['assigned_leads'] }}</td>
                                <td class="py-3 px-4 text-center font-mono font-semibold text-blue-600 dark:text-blue-400">{{ $tp['contacted'] }}</td>
                                <td class="py-3 px-4 text-center font-mono font-semibold text-amber-600 dark:text-amber-400">{{ $tp['demos'] }}</td>
                                <td class="py-3 px-4 text-center font-mono font-semibold text-emerald-600 dark:text-emerald-400">{{ $tp['converted'] }}</td>
                                <td class="py-3 px-4 text-center font-mono font-semibold text-purple-600 dark:text-purple-400">{{ $tp['conversion_pct'] }}%</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $currency }}{{ number_format((float) $tp['conversion_value']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400 dark:text-slate-500 text-xs">
                                    No team data for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Chart.js Script for Trend Graph -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('trendChart');
            if (!ctx) return;

            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(51, 65, 85, 0.25)' : 'rgba(203, 213, 225, 0.4)';
            const tickColor = isDark ? '#64748b' : '#94a3b8';

            const dates = @json($trendDates);
            const newLeadsData = @json($trendNewLeads);
            const conversionsData = @json($trendConversions);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'New Leads',
                            data: newLeadsData,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 2.5,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#3b82f6',
                        },
                        {
                            label: 'Conversions',
                            data: conversionsData,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 2.5,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#10b981',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#ffffff',
                            bodyColor: '#cbd5e1',
                            borderColor: '#334155',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: true,
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: tickColor,
                                font: {
                                    size: 10,
                                    family: 'ui-monospace, monospace'
                                },
                                maxTicksLimit: 12,
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: tickColor,
                                font: {
                                    size: 10,
                                    family: 'ui-monospace, monospace'
                                },
                                stepSize: 1,
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>

