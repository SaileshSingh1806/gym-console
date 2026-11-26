<x-app-layout header="Member Report">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
    @endphp

    <div class="space-y-6 w-full">
        <!-- ==================== HEADER & TOP CONTROLS ==================== -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-surface dark:bg-slate-900/60 p-4 sm:p-5 rounded-xl border border-line dark:border-line">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-semibold text-fg dark:text-white tracking-tight flex items-center gap-2">
                        <span>Member Report</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-fg-muted dark:text-fg-muted mt-0.5">Comprehensive membership, attendance, and facility analytics</p>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <form action="{{ route('app.finance.member-report') }}" method="GET" class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-1.5 sm:gap-2 bg-raised dark:bg-surface border border-line dark:border-line rounded-xl px-3 py-2 text-xs text-fg-muted dark:text-fg-muted w-full sm:w-auto">
                    <span class="text-fg-muted dark:text-fg-muted font-medium shrink-0">Dates:</span>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="bg-transparent border-0 text-fg dark:text-white text-xs p-0 focus:ring-0 focus:outline-none min-w-0 flex-1">
                    <span class="text-fg-muted dark:text-fg-muted shrink-0">&mdash;</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="bg-transparent border-0 text-fg dark:text-white text-xs p-0 focus:ring-0 focus:outline-none min-w-0 flex-1">
                </div>

                <button type="submit" class="px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold transition-all cursor-pointer ops-primary">
                    Apply
                </button>

                <a href="{{ route('app.finance.member-report') }}" class="px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line text-xs font-semibold transition-all">
                    Reset
                </a>

                <!-- Gym Badge -->
                <div class="px-3.5 py-2 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 border border-indigo-500/20 dark:border-indigo-500/25 text-indigo-600 dark:text-indigo-400 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span class="truncate">{{ $tenant->name ?? 'PowerFit Gym' }}</span>
                </div>

                <!-- Branch Filter -->
                <select name="branch_id" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs font-semibold focus:border-accent focus:outline-none cursor-pointer">
                    <option value="all" {{ empty($branchId) ? 'selected' : '' }}>🌐 All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>

                <!-- Download PDF -->
                <a href="{{ route('app.finance.member-report.pdf', request()->query()) }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Download PDF</span>
                </a>
            </form>
        </div>

        <!-- ==================== 6 TOP KPI CARDS ==================== -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            <!-- 1. Total Members -->
            <div class="p-3.5 sm:p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center gap-2.5 sm:gap-3.5 hover:border-line dark:hover:border-line transition-colors">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 dark:border-indigo-500/25 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl sm:text-2xl font-semibold text-fg dark:text-white tracking-tight leading-none truncate">{{ $totalMembers }}</div>
                    <div class="text-xs sm:text-xs text-fg-muted dark:text-fg-muted font-semibold mt-1 truncate">Total Members</div>
                </div>
            </div>

            <!-- 2. Active -->
            <div class="p-3.5 sm:p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center gap-2.5 sm:gap-3.5 hover:border-line dark:hover:border-line transition-colors">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 dark:border-emerald-500/25 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl sm:text-2xl font-semibold text-emerald-600 dark:text-emerald-400 tracking-tight leading-none truncate">{{ $activeMembers }}</div>
                    <div class="text-xs sm:text-xs text-fg-muted dark:text-fg-muted font-semibold mt-1 truncate">Active</div>
                </div>
            </div>

            <!-- 3. Expired -->
            <div class="p-3.5 sm:p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center gap-2.5 sm:gap-3.5 hover:border-line dark:hover:border-line transition-colors">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-rose-500/10 dark:bg-red-500/15 text-rose-600 dark:text-red-400 border border-rose-500/20 dark:border-red-500/25 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl sm:text-2xl font-semibold text-rose-600 dark:text-red-400 tracking-tight leading-none truncate">{{ $expiredMembers }}</div>
                    <div class="text-xs sm:text-xs text-fg-muted dark:text-fg-muted font-semibold mt-1 truncate">Expired</div>
                </div>
            </div>

            <!-- 4. Frozen -->
            <div class="p-3.5 sm:p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center gap-2.5 sm:gap-3.5 hover:border-line dark:hover:border-line transition-colors">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-amber-500/10 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/20 dark:border-amber-500/25 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m9-9H3m15.364 6.364l-12.728-12.728m12.728 0L5.636 18.364"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl sm:text-2xl font-semibold text-amber-600 dark:text-amber-400 tracking-tight leading-none truncate">{{ $frozenMembers }}</div>
                    <div class="text-xs sm:text-xs text-fg-muted dark:text-fg-muted font-semibold mt-1 truncate">Frozen</div>
                </div>
            </div>

            <!-- 5. New This Month -->
            <div class="p-3.5 sm:p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center gap-2.5 sm:gap-3.5 hover:border-line dark:hover:border-line transition-colors">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-purple-500/10 dark:bg-purple-500/15 text-purple-600 dark:text-purple-400 border border-purple-500/20 dark:border-purple-500/25 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl sm:text-2xl font-semibold text-purple-600 dark:text-purple-400 tracking-tight leading-none truncate">{{ $newThisMonth }}</div>
                    <div class="text-xs sm:text-xs text-fg-muted dark:text-fg-muted font-semibold mt-1 truncate">New Month</div>
                </div>
            </div>

            <!-- 6. Today's Check-ins -->
            <div class="p-3.5 sm:p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center gap-2.5 sm:gap-3.5 hover:border-line dark:hover:border-line transition-colors">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-sky-500/10 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-500/20 dark:border-sky-500/25 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl sm:text-2xl font-semibold text-sky-600 dark:text-sky-400 tracking-tight leading-none truncate">{{ $todayCheckins }}</div>
                    <div class="text-xs sm:text-xs text-fg-muted dark:text-fg-muted font-semibold mt-1 truncate">Check-Ins</div>
                </div>
            </div>
        </div>

        <!-- ==================== 2-COLUMN MIDDLE SECTION ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Plan Distribution & Gender Split (5 cols) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Plan Distribution -->
                <div class="p-4 sm:p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-5">
                    <div class="flex items-center gap-2.5 border-b border-line dark:border-line pb-4">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-fg dark:text-white">Plan Distribution</h3>
                    </div>

                    <div class="space-y-4 max-h-80 overflow-y-auto pr-1">
                        @forelse($plans as $plan)
                            @php
                                $cnt = $plan->memberships_count ?? 0;
                                $pct = $maxPlanCount > 0 ? round(($cnt / $maxPlanCount) * 100) : 0;
                            @endphp
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs sm:text-sm">
                                    <span class="text-fg-muted dark:text-fg-muted font-semibold">{{ $plan->name }}</span>
                                    <span class="font-semibold text-fg dark:text-white">{{ $cnt }} <span class="text-fg-muted dark:text-fg-muted text-xs font-normal">members</span></span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-raised dark:bg-surface overflow-hidden border border-line dark:border-line">
                                    <div class="h-full bg-indigo-600 dark:bg-indigo-500 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-fg-muted text-center py-6 text-sm">No active membership plans found.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Gender Split -->
                <div class="p-4 sm:p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-5">
                    <div class="flex items-center gap-2.5 border-b border-line dark:border-line pb-4">
                        <div class="w-8 h-8 rounded-xl bg-purple-500/10 dark:bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-fg dark:text-white">Gender Split</h3>
                    </div>

                    <div class="grid grid-cols-3 gap-2.5 sm:gap-3.5">
                        <!-- Male -->
                        <div class="p-3 sm:p-4 rounded-xl bg-sky-50 dark:bg-slate-950/80 border border-sky-100 dark:border-line text-center">
                            <div class="text-2xl sm:text-3xl font-semibold text-sky-600 dark:text-sky-400 leading-none">{{ $maleCount }}</div>
                            <div class="text-xs sm:text-xs font-semibold text-fg-muted dark:text-fg-muted mt-1.5 truncate">Male</div>
                        </div>

                        <!-- Female -->
                        <div class="p-3 sm:p-4 rounded-xl bg-pink-50 dark:bg-pink-500/10 border border-pink-100 dark:border-pink-500/20 text-center">
                            <div class="text-2xl sm:text-3xl font-semibold text-pink-600 dark:text-pink-400 leading-none">{{ $femaleCount }}</div>
                            <div class="text-xs sm:text-xs font-semibold text-pink-700 dark:text-pink-300 mt-1.5 truncate">Female</div>
                        </div>

                        <!-- Other -->
                        <div class="p-3 sm:p-4 rounded-xl bg-raised dark:bg-slate-950/80 border border-line dark:border-line text-center">
                            <div class="text-2xl sm:text-3xl font-semibold text-purple-600 dark:text-purple-400 leading-none">{{ $otherCount }}</div>
                            <div class="text-xs sm:text-xs font-semibold text-fg-muted dark:text-fg-muted mt-1.5 truncate">Other / NA</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Attendance Overview (7 cols) -->
            <div class="lg:col-span-7 p-4 sm:p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex flex-col justify-between space-y-6">
                <div class="flex items-center gap-2.5 border-b border-line dark:border-line pb-4">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-fg dark:text-white">Attendance Overview</h3>
                </div>

                <div class="grid grid-cols-3 gap-2.5 sm:gap-3.5">
                    <!-- Today Check-Ins -->
                    <div class="p-3 sm:p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-100 dark:border-emerald-500/25 text-center">
                        <div class="text-2xl sm:text-3xl font-semibold text-emerald-600 dark:text-emerald-400 leading-none">{{ $todayCheckins }}</div>
                        <div class="text-xs sm:text-xs font-semibold text-emerald-700 dark:text-emerald-300 mt-1.5 truncate">Today Check-Ins</div>
                    </div>

                    <!-- Currently In -->
                    <div class="p-3 sm:p-4 rounded-xl bg-amber-50 dark:bg-amber-500/15 border border-amber-100 dark:border-amber-500/25 text-center">
                        <div class="text-2xl sm:text-3xl font-semibold text-amber-600 dark:text-amber-400 leading-none">{{ $currentlyIn }}</div>
                        <div class="text-xs sm:text-xs font-semibold text-amber-700 dark:text-amber-300 mt-1.5 truncate">Currently In</div>
                    </div>

                    <!-- Checked Out -->
                    <div class="p-3 sm:p-4 rounded-xl bg-sky-50 dark:bg-sky-500/15 border border-sky-100 dark:border-sky-500/25 text-center">
                        <div class="text-2xl sm:text-3xl font-semibold text-sky-600 dark:text-sky-400 leading-none">{{ $checkedOut }}</div>
                        <div class="text-xs sm:text-xs font-semibold text-sky-700 dark:text-sky-300 mt-1.5 truncate">Checked Out</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Unique This Week -->
                    <div class="p-4 sm:p-5 rounded-xl bg-raised dark:bg-slate-950/80 border border-line dark:border-line text-center">
                        <div class="text-2xl sm:text-3xl lg:text-4xl font-semibold text-purple-600 dark:text-purple-400 leading-none">{{ $uniqueThisWeek }}</div>
                        <div class="text-xs font-semibold text-fg-muted dark:text-fg-muted mt-2">Weekly Unique Members</div>
                    </div>

                    <!-- Avg Session -->
                    <div class="p-4 sm:p-5 rounded-xl bg-raised dark:bg-slate-950/80 border border-line dark:border-line text-center">
                        <div class="text-2xl sm:text-3xl lg:text-4xl font-semibold text-pink-600 dark:text-pink-400 leading-none">{{ $avgSession }} min</div>
                        <div class="text-xs font-semibold text-fg-muted dark:text-fg-muted mt-2">Avg Workout Duration</div>
                    </div>
                </div>

                <!-- Attendance by Source -->
                <div class="pt-4 border-t border-line dark:border-line space-y-2.5">
                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted">Attendance Channel Breakup (30 Days)</span>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-fg-muted dark:text-fg-muted font-semibold">QR Code &amp; Turnstile Logs</span>
                            <span class="font-semibold text-fg dark:text-white">100%</span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-raised dark:bg-surface overflow-hidden border border-line dark:border-line">
                            <div class="h-full bg-indigo-600 dark:bg-indigo-500 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== LOWER DETAILED MEMBER LISTS ==================== -->
        <div class="space-y-6">
            <!-- 1. Recently Expired (Last 14 Days) -->
            <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
                <div class="px-6 py-4 border-b border-line dark:border-line flex items-center justify-between bg-raised dark:bg-slate-950/60">
                    <div class="flex items-center gap-2.5 text-rose-600 dark:text-red-400 text-sm font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Recently Expired Members <span class="text-fg-muted dark:text-fg-muted font-medium text-xs">(Last 14 Days — {{ $recentlyExpired->count() }})</span></span>
                    </div>
                </div>

                @if($recentlyExpired->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[550px] text-left text-xs sm:text-sm">
                            <thead class="bg-raised dark:bg-slate-950/40 text-fg-muted dark:text-fg-muted text-xs font-semibold border-b border-line dark:border-line">
                                <tr>
                                    <th class="py-3 px-5">Member Name</th>
                                    <th class="py-3 px-5">Contact Phone</th>
                                    <th class="py-3 px-5">Membership Plan</th>
                                    <th class="py-3 px-5 text-right">Expired On</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                                @foreach($recentlyExpired as $exp)
                                    <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                        <td class="py-3.5 px-5 font-semibold text-fg dark:text-white">{{ $exp->member->full_name ?? ($exp->member->name ?? 'N/A') }}</td>
                                        <td class="py-3.5 px-5 font-mono text-fg-muted dark:text-fg-muted">{{ $exp->member->phone ?? 'N/A' }}</td>
                                        <td class="py-3.5 px-5">{{ $exp->plan->name ?? 'Standard Plan' }}</td>
                                        <td class="py-3.5 px-5 text-right text-rose-600 dark:text-red-400 font-mono font-semibold">{{ $exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('d M Y') : 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center text-sm text-fg-muted">
                        No recently expired memberships recorded in the last 14 days.
                    </div>
                @endif
            </div>

            <!-- 2. Expiring Soon (Next 7 Days) -->
            <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
                <div class="px-6 py-4 border-b border-line dark:border-line flex items-center justify-between bg-raised dark:bg-slate-950/60">
                    <div class="flex items-center gap-2.5 text-amber-600 dark:text-amber-400 text-sm font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Expiring Soon <span class="text-fg-muted dark:text-fg-muted font-medium text-xs">(Next 7 Days — {{ $expiringSoon->count() }})</span></span>
                    </div>
                </div>

                @if($expiringSoon->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[550px] text-left text-xs sm:text-sm">
                            <thead class="bg-raised dark:bg-slate-950/40 text-fg-muted dark:text-fg-muted text-xs font-semibold border-b border-line dark:border-line">
                                <tr>
                                    <th class="py-3 px-5">Member Name</th>
                                    <th class="py-3 px-5">Contact Phone</th>
                                    <th class="py-3 px-5">Membership Plan</th>
                                    <th class="py-3 px-5">Expires On</th>
                                    <th class="py-3 px-5 text-right">Days Left</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                                @foreach($expiringSoon as $exp)
                                    @php
                                        $daysLeft = max(0, (int) round(\Carbon\Carbon::parse($exp->end_date)->startOfDay()->diffInDays(now()->startOfDay())));
                                    @endphp
                                    <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                        <td class="py-3.5 px-5 font-semibold text-fg dark:text-white">{{ $exp->member->full_name ?? ($exp->member->name ?? 'N/A') }}</td>
                                        <td class="py-3.5 px-5 font-mono text-fg-muted dark:text-fg-muted">{{ $exp->member->phone ?? 'N/A' }}</td>
                                        <td class="py-3.5 px-5">{{ $exp->plan->name ?? 'Standard Plan' }}</td>
                                        <td class="py-3.5 px-5 font-mono text-fg-muted dark:text-fg-muted">{{ \Carbon\Carbon::parse($exp->end_date)->format('d M Y') }}</td>
                                        <td class="py-3.5 px-5 text-right">
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-500/10 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/20 dark:border-amber-500/25">
                                                {{ $daysLeft }}d
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center text-sm text-fg-muted">
                        No memberships expiring in the next 7 days.
                    </div>
                @endif
            </div>

            <!-- 3. New Members (Date Range) -->
            <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
                <div class="px-6 py-4 border-b border-line dark:border-line flex items-center justify-between bg-raised dark:bg-slate-950/60">
                    <div class="flex items-center gap-2.5 text-emerald-600 dark:text-emerald-400 text-sm font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>New Members Enrolled <span class="text-fg-muted dark:text-fg-muted font-medium text-xs">({{ \Carbon\Carbon::parse($startDate)->format('d M') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }} &bull; {{ $newMembers->count() }})</span></span>
                    </div>
                </div>

                @if($newMembers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[550px] text-left text-xs sm:text-sm">
                            <thead class="bg-raised dark:bg-slate-950/40 text-fg-muted dark:text-fg-muted text-xs font-semibold border-b border-line dark:border-line">
                                <tr>
                                    <th class="py-3 px-5">Member Name</th>
                                    <th class="py-3 px-5">Contact Phone</th>
                                    <th class="py-3 px-5">Assigned Plan</th>
                                    <th class="py-3 px-5 text-right">Join Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                                @foreach($newMembers as $m)
                                    <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                        <td class="py-3.5 px-5 font-semibold text-fg dark:text-white">{{ $m->full_name ?? ($m->name ?? 'N/A') }}</td>
                                        <td class="py-3.5 px-5 font-mono text-fg-muted dark:text-fg-muted">{{ $m->phone }}</td>
                                        <td class="py-3.5 px-5">{{ $m->activeMembership?->plan?->name ?? $m->memberships->sortByDesc('id')->first()?->plan?->name ?? 'No Active Plan' }}</td>
                                        <td class="py-3.5 px-5 text-right font-mono text-fg-muted dark:text-fg-muted">{{ $m->created_at ? $m->created_at->format('d M Y') : '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center text-sm text-fg-muted">
                        No new members enrolled in the selected date range.
                    </div>
                @endif
            </div>

            <!-- 4. Inactive Members (30+ days no visit) -->
            <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
                <div class="px-6 py-4 border-b border-line dark:border-line flex items-center justify-between bg-raised dark:bg-slate-950/60">
                    <div class="flex items-center gap-2.5 text-fg-muted dark:text-fg-muted text-sm font-semibold">
                        <svg class="w-5 h-5 text-fg-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Inactive Members <span class="text-fg-muted dark:text-fg-muted font-medium text-xs">(Active plan, 0 visits in 30+ days &bull; {{ $inactiveMembers->count() }})</span></span>
                    </div>
                </div>

                @if($inactiveMembers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[550px] text-left text-xs sm:text-sm">
                            <thead class="bg-raised dark:bg-slate-950/40 text-fg-muted dark:text-fg-muted text-xs font-semibold border-b border-line dark:border-line">
                                <tr>
                                    <th class="py-3 px-5">Member Name</th>
                                    <th class="py-3 px-5">Contact Phone</th>
                                    <th class="py-3 px-5">Current Plan</th>
                                    <th class="py-3 px-5 text-right">Quick Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                                @foreach($inactiveMembers as $m)
                                    <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                        <td class="py-3.5 px-5 font-semibold text-fg dark:text-white">{{ $m->full_name ?? ($m->name ?? 'N/A') }}</td>
                                        <td class="py-3.5 px-5 font-mono text-fg-muted dark:text-fg-muted">{{ $m->phone }}</td>
                                        <td class="py-3.5 px-5">{{ $m->activeMembership?->plan?->name ?? $m->memberships->sortByDesc('id')->first()?->plan?->name ?? 'Active Plan' }}</td>
                                        <td class="py-3.5 px-5 text-right">
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $m->phone) }}?text=Hey%20{{ urlencode($m->first_name ?? $m->name) }},%20we%20miss%20you%20at%20{{ urlencode($tenant->name ?? 'the gym') }}!" target="_blank" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs inline-flex items-center gap-1.5 transition-all">
                                                <span>WhatsApp Nudge</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center text-sm text-fg-muted">
                        No inactive members with zero check-ins in 30+ days.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
