<x-app-layout header="Member Report">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
    @endphp

    <div class="space-y-6 max-w-7xl mx-auto">
        <!-- ==================== HEADER & TOP CONTROLS ==================== -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-900/60 p-5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center shadow-inner shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span>Member Report</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Comprehensive membership, attendance, and facility analytics</p>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <form action="{{ route('app.finance.member-report') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-700 dark:text-slate-300">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Dates:</span>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="bg-transparent border-0 text-slate-900 dark:text-white text-xs p-0 focus:ring-0 focus:outline-none">
                    <span class="text-slate-400 dark:text-slate-500">&mdash;</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="bg-transparent border-0 text-slate-900 dark:text-white text-xs p-0 focus:ring-0 focus:outline-none">
                </div>

                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                    Apply
                </button>

                <a href="{{ route('app.finance.member-report') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 text-xs font-bold transition-all">
                    Reset
                </a>

                <!-- Gym Badge -->
                <div class="px-3.5 py-2 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 border border-indigo-500/20 dark:border-indigo-500/25 text-indigo-600 dark:text-indigo-400 text-xs font-bold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>{{ $tenant->name ?? 'PowerFit Gym' }}</span>
                </div>

                <!-- Branch Filter -->
                <select name="branch_id" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold focus:border-indigo-500 focus:outline-none cursor-pointer">
                    <option value="all" {{ empty($branchId) ? 'selected' : '' }}>🌐 All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>

                <!-- Download PDF -->
                <a href="{{ route('app.finance.member-report.pdf', request()->query()) }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-2 shadow-md shadow-emerald-600/25 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Download PDF</span>
                </a>
            </form>
        </div>

        <!-- ==================== 6 TOP KPI CARDS ==================== -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <!-- 1. Total Members -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center gap-3.5 shadow-sm hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                <div class="w-11 h-11 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 dark:border-indigo-500/25 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-none">{{ $totalMembers }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider mt-1">Total Members</div>
                </div>
            </div>

            <!-- 2. Active -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center gap-3.5 shadow-sm hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                <div class="w-11 h-11 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 dark:border-emerald-500/25 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight leading-none">{{ $activeMembers }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider mt-1">Active</div>
                </div>
            </div>

            <!-- 3. Expired -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center gap-3.5 shadow-sm hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                <div class="w-11 h-11 rounded-xl bg-rose-500/10 dark:bg-red-500/15 text-rose-600 dark:text-red-400 border border-rose-500/20 dark:border-red-500/25 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-rose-600 dark:text-red-400 tracking-tight leading-none">{{ $expiredMembers }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider mt-1">Expired</div>
                </div>
            </div>

            <!-- 4. Frozen -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center gap-3.5 shadow-sm hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                <div class="w-11 h-11 rounded-xl bg-amber-500/10 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/20 dark:border-amber-500/25 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m9-9H3m15.364 6.364l-12.728-12.728m12.728 0L5.636 18.364"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight leading-none">{{ $frozenMembers }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider mt-1">Frozen</div>
                </div>
            </div>

            <!-- 5. New This Month -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center gap-3.5 shadow-sm hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                <div class="w-11 h-11 rounded-xl bg-purple-500/10 dark:bg-purple-500/15 text-purple-600 dark:text-purple-400 border border-purple-500/20 dark:border-purple-500/25 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-purple-600 dark:text-purple-400 tracking-tight leading-none">{{ $newThisMonth }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider mt-1">New This Month</div>
                </div>
            </div>

            <!-- 6. Today's Check-ins -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center gap-3.5 shadow-sm hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                <div class="w-11 h-11 rounded-xl bg-sky-500/10 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-500/20 dark:border-sky-500/25 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-sky-600 dark:text-sky-400 tracking-tight leading-none">{{ $todayCheckins }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider mt-1">Today Check-Ins</div>
                </div>
            </div>
        </div>

        <!-- ==================== 2-COLUMN MIDDLE SECTION ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Plan Distribution & Gender Split (5 cols) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Plan Distribution -->
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-800/80 pb-4">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Plan Distribution</h3>
                    </div>

                    <div class="space-y-4 max-h-80 overflow-y-auto pr-1">
                        @forelse($plans as $plan)
                            @php
                                $cnt = $plan->memberships_count ?? 0;
                                $pct = round(($cnt / $maxPlanCount) * 100);
                            @endphp
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs sm:text-sm">
                                    <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $plan->name }}</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white">{{ $cnt }} <span class="text-slate-400 dark:text-slate-500 text-xs font-normal">members</span></span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-950 overflow-hidden border border-slate-200 dark:border-slate-800">
                                    <div class="h-full bg-indigo-600 dark:bg-indigo-500 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-500 text-center py-6 text-sm">No active membership plans found.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Gender Split -->
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-800/80 pb-4">
                        <div class="w-8 h-8 rounded-xl bg-purple-500/10 dark:bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Gender Split</h3>
                    </div>

                    <div class="grid grid-cols-3 gap-3.5">
                        <!-- Male -->
                        <div class="p-4 rounded-2xl bg-sky-50 dark:bg-slate-950/80 border border-sky-100 dark:border-slate-800 text-center">
                            <div class="text-3xl font-black text-sky-600 dark:text-sky-400 leading-none">{{ $maleCount }}</div>
                            <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1.5">Male</div>
                        </div>

                        <!-- Female -->
                        <div class="p-4 rounded-2xl bg-pink-50 dark:bg-pink-500/10 border border-pink-100 dark:border-pink-500/20 text-center">
                            <div class="text-3xl font-black text-pink-600 dark:text-pink-400 leading-none">{{ $femaleCount }}</div>
                            <div class="text-xs font-bold text-pink-700 dark:text-pink-300 uppercase tracking-wider mt-1.5">Female</div>
                        </div>

                        <!-- Other -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 text-center">
                            <div class="text-3xl font-black text-purple-600 dark:text-purple-400 leading-none">{{ $otherCount }}</div>
                            <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1.5">Other / NA</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Attendance Overview (7 cols) -->
            <div class="lg:col-span-7 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-6">
                <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-800/80 pb-4">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Attendance Overview</h3>
                </div>

                <div class="grid grid-cols-3 gap-3.5">
                    <!-- Today Check-Ins -->
                    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-100 dark:border-emerald-500/25 text-center">
                        <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 leading-none">{{ $todayCheckins }}</div>
                        <div class="text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider mt-1.5">Today Check-Ins</div>
                    </div>

                    <!-- Currently In -->
                    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-500/15 border border-amber-100 dark:border-amber-500/25 text-center">
                        <div class="text-3xl font-black text-amber-600 dark:text-amber-400 leading-none">{{ $currentlyIn }}</div>
                        <div class="text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider mt-1.5">Currently In</div>
                    </div>

                    <!-- Checked Out -->
                    <div class="p-4 rounded-2xl bg-sky-50 dark:bg-sky-500/15 border border-sky-100 dark:border-sky-500/25 text-center">
                        <div class="text-3xl font-black text-sky-600 dark:text-sky-400 leading-none">{{ $checkedOut }}</div>
                        <div class="text-xs font-bold text-sky-700 dark:text-sky-300 uppercase tracking-wider mt-1.5">Checked Out</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Unique This Week -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 text-center">
                        <div class="text-3xl sm:text-4xl font-black text-purple-600 dark:text-purple-400 leading-none">{{ $uniqueThisWeek }}</div>
                        <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-2">Weekly Unique Members</div>
                    </div>

                    <!-- Avg Session -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 text-center">
                        <div class="text-3xl sm:text-4xl font-black text-pink-600 dark:text-pink-400 leading-none">{{ $avgSession }} min</div>
                        <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-2">Avg Workout Duration</div>
                    </div>
                </div>

                <!-- Attendance by Source -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 space-y-2.5">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Attendance Channel Breakup (30 Days)</span>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-slate-700 dark:text-slate-300 font-semibold">QR Code &amp; Turnstile Logs</span>
                            <span class="font-extrabold text-slate-900 dark:text-white">100%</span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-slate-100 dark:bg-slate-950 overflow-hidden border border-slate-200 dark:border-slate-800">
                            <div class="h-full bg-indigo-600 dark:bg-indigo-500 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== LOWER DETAILED MEMBER LISTS ==================== -->
        <div class="space-y-6">
            <!-- 1. Recently Expired (Last 14 Days) -->
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/70 dark:bg-slate-950/60">
                    <div class="flex items-center gap-2.5 text-rose-600 dark:text-red-400 text-sm font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Recently Expired Members <span class="text-slate-500 dark:text-slate-400 font-medium text-xs">(Last 14 Days — {{ $recentlyExpired->count() }})</span></span>
                    </div>
                </div>

                @if($recentlyExpired->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-950/40 text-slate-600 dark:text-slate-400 uppercase text-xs font-bold border-b border-slate-200 dark:border-slate-800/60">
                                <tr>
                                    <th class="py-3 px-5">Member Name</th>
                                    <th class="py-3 px-5">Contact Phone</th>
                                    <th class="py-3 px-5">Membership Plan</th>
                                    <th class="py-3 px-5 text-right">Expired On</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                                @foreach($recentlyExpired as $exp)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-5 font-bold text-slate-900 dark:text-white">{{ $exp->member->full_name ?? ($exp->member->name ?? 'N/A') }}</td>
                                        <td class="py-3.5 px-5 font-mono text-slate-500 dark:text-slate-400">{{ $exp->member->phone ?? 'N/A' }}</td>
                                        <td class="py-3.5 px-5">{{ $exp->plan->name ?? 'Standard Plan' }}</td>
                                        <td class="py-3.5 px-5 text-right text-rose-600 dark:text-red-400 font-mono font-bold">{{ $exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('d M Y') : 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center text-sm text-slate-500">
                        No recently expired memberships recorded in the last 14 days.
                    </div>
                @endif
            </div>

            <!-- 2. Expiring Soon (Next 7 Days) -->
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/70 dark:bg-slate-950/60">
                    <div class="flex items-center gap-2.5 text-amber-600 dark:text-amber-400 text-sm font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Expiring Soon <span class="text-slate-500 dark:text-slate-400 font-medium text-xs">(Next 7 Days — {{ $expiringSoon->count() }})</span></span>
                    </div>
                </div>

                @if($expiringSoon->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-950/40 text-slate-600 dark:text-slate-400 uppercase text-xs font-bold border-b border-slate-200 dark:border-slate-800/60">
                                <tr>
                                    <th class="py-3 px-5">Member Name</th>
                                    <th class="py-3 px-5">Contact Phone</th>
                                    <th class="py-3 px-5">Membership Plan</th>
                                    <th class="py-3 px-5">Expires On</th>
                                    <th class="py-3 px-5 text-right">Days Left</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                                @foreach($expiringSoon as $exp)
                                    @php
                                        $daysLeft = max(0, \Carbon\Carbon::parse($exp->end_date)->diffInDays(now()));
                                        $daysLeft = max(0, (int) round(\Carbon\Carbon::parse($exp->end_date)->startOfDay()->diffInDays(now()->startOfDay())));
                                    @endphp
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-5 font-bold text-slate-900 dark:text-white">{{ $exp->member->full_name ?? ($exp->member->name ?? 'N/A') }}</td>
                                        <td class="py-3.5 px-5 font-mono text-slate-500 dark:text-slate-400">{{ $exp->member->phone ?? 'N/A' }}</td>
                                        <td class="py-3.5 px-5">{{ $exp->plan->name ?? 'Standard Plan' }}</td>
                                        <td class="py-3.5 px-5 font-mono text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse($exp->end_date)->format('d M Y') }}</td>
                                        <td class="py-3.5 px-5 text-right">
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold bg-amber-500/10 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/20 dark:border-amber-500/25">
                                                {{ $daysLeft }}d
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center text-sm text-slate-500">
                        No memberships expiring in the next 7 days.
                    </div>
                @endif
            </div>

            <!-- 3. New Members (Date Range) -->
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/70 dark:bg-slate-950/60">
                    <div class="flex items-center gap-2.5 text-emerald-600 dark:text-emerald-400 text-sm font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>New Members Enrolled <span class="text-slate-500 dark:text-slate-400 font-medium text-xs">({{ \Carbon\Carbon::parse($startDate)->format('d M') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }} &bull; {{ $newMembers->count() }})</span></span>
                    </div>
                </div>

                @if($newMembers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-950/40 text-slate-600 dark:text-slate-400 uppercase text-xs font-bold border-b border-slate-200 dark:border-slate-800/60">
                                <tr>
                                    <th class="py-3 px-5">Member Name</th>
                                    <th class="py-3 px-5">Contact Phone</th>
                                    <th class="py-3 px-5">Assigned Plan</th>
                                    <th class="py-3 px-5 text-right">Join Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                                @foreach($newMembers as $m)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-5 font-bold text-slate-900 dark:text-white">{{ $m->full_name ?? ($m->name ?? 'N/A') }}</td>
                                        <td class="py-3.5 px-5 font-mono text-slate-500 dark:text-slate-400">{{ $m->phone }}</td>
                                        <td class="py-3.5 px-5">{{ $m->activeMembership?->plan?->name ?? 'No Active Plan' }}</td>
                                        <td class="py-3.5 px-5 text-right font-mono text-slate-500 dark:text-slate-400">{{ $m->created_at ? $m->created_at->format('d M Y') : '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center text-sm text-slate-500">
                        No new members enrolled in the selected date range.
                    </div>
                @endif
            </div>

            <!-- 4. Inactive Members (30+ days no visit) -->
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/70 dark:bg-slate-950/60">
                    <div class="flex items-center gap-2.5 text-slate-700 dark:text-slate-300 text-sm font-bold">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Inactive Members <span class="text-slate-500 dark:text-slate-400 font-medium text-xs">(Active plan, 0 visits in 30+ days &bull; {{ $inactiveMembers->count() }})</span></span>
                    </div>
                </div>

                @if($inactiveMembers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-950/40 text-slate-600 dark:text-slate-400 uppercase text-xs font-bold border-b border-slate-200 dark:border-slate-800/60">
                                <tr>
                                    <th class="py-3 px-5">Member Name</th>
                                    <th class="py-3 px-5">Contact Phone</th>
                                    <th class="py-3 px-5">Current Plan</th>
                                    <th class="py-3 px-5 text-right">Quick Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                                @foreach($inactiveMembers as $m)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-5 font-bold text-slate-900 dark:text-white">{{ $m->full_name ?? ($m->name ?? 'N/A') }}</td>
                                        <td class="py-3.5 px-5 font-mono text-slate-500 dark:text-slate-400">{{ $m->phone }}</td>
                                        <td class="py-3.5 px-5">{{ $m->activeMembership?->plan?->name ?? 'Active Plan' }}</td>
                                        <td class="py-3.5 px-5 text-right">
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $m->phone) }}?text=Hey%20{{ urlencode($m->first_name ?? $m->name) }},%20we%20miss%20you%20at%20{{ urlencode($tenant->name ?? 'the gym') }}!" target="_blank" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs inline-flex items-center gap-1.5 transition-all shadow-sm">
                                                <span>WhatsApp Nudge</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center text-sm text-slate-500">
                        No inactive members with zero check-ins in 30+ days.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
