<x-app-layout header="CRM Dashboard">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
        $currentUser = $currentUser ?? auth()->user();
    @endphp

    <div class="space-y-6" x-data="{
        showLeadModal: false,
        showTrialModal: false,
        showTargetModal: false,
        leadPlanId: '',
        leadEstValue: 0,
        onLeadPlanChange(event) {
            const opt = event.target.options[event.target.selectedIndex];
            const price = opt ? opt.getAttribute('data-price') : 0;
            this.leadPlanId = event.target.value;
            this.leadEstValue = price ? Number(price) : 0;
        },
        targetForm: {
            user_id: '',
            name: '',
            period_type: 'monthly',
            start_date: '{{ now()->startOfMonth()->format('Y-m-d') }}',
            end_date: '{{ now()->endOfMonth()->format('Y-m-d') }}',
            target_revenue: '',
            target_admissions: '',
            notes: ''
        },
        openSetTarget(staffId, staffName, currentRev, currentAdm) {
            this.targetForm.user_id = staffId;
            this.targetForm.name = staffName;
            this.targetForm.target_revenue = currentRev > 0 ? currentRev : '';
            this.targetForm.target_admissions = currentAdm > 0 ? currentAdm : '';
            this.showTargetModal = true;
        }
    }">
        <!-- Top Action Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20 flex items-center justify-center shadow-inner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                            <span>CRM Dashboard</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Track your leads, pipeline, and conversions at a glance</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse"></span>
                    <span>{{ $conversionRate }}% Conversion</span>
                </span>

                <button type="button" @click="openSetTarget('', '', '', '')" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                    <span>🎯 Staff Targets</span>
                </button>

                <a href="{{ route('app.leads.create') }}" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold flex items-center gap-1.5 shadow-md shadow-indigo-600/25 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Lead</span>
                </a>

                <button type="button" @click="showTrialModal = true" class="px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:hover:text-white text-xs font-bold border border-purple-200 dark:border-slate-700 flex items-center gap-1.5 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Book Demo</span>
                </button>

                <a href="{{ route('app.leads.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800 dark:text-slate-300 dark:hover:text-white text-xs font-bold border border-slate-200 dark:border-slate-800 flex items-center gap-1.5 transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>All Leads</span>
                </a>

                <a href="{{ route('app.crm.reports') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800 dark:text-slate-300 dark:hover:text-white text-xs font-bold border border-slate-200 dark:border-slate-800 flex items-center gap-1.5 transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>View Reports</span>
                </a>
            </div>
        </div>

        <!-- 4 Top KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Leads -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all flex items-center justify-between shadow-sm group">
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Leads</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $totalLeads }}</div>
                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">All registered prospects</div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>

            <!-- New This Month -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all flex items-center justify-between shadow-sm group">
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">New This Month</span>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">{{ $newThisMonth }}</div>
                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Acquired in {{ date('F') }}</div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
            </div>

            <!-- Active Conversions -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all flex items-center justify-between shadow-sm group">
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active Conversions</span>
                    <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight">{{ $activeConversions }}</div>
                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Won &amp; In negotiation</div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                </div>
            </div>

            <!-- Monthly MRR -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all flex items-center justify-between shadow-sm group">
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Monthly MRR</span>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight">{{ $currency }}{{ number_format($monthlyMrr) }}</div>
                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">From converted leads</div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-500/20 flex items-center justify-center shrink-0 font-black text-sm">
                    {{ $currency }}
                </div>
            </div>
        </div>

        <!-- ==================== SALES TARGET PROGRESS WIDGET ==================== -->
        @if(isset($myTargetProgress) && ($myTargetProgress['target_revenue'] > 0 || $myTargetProgress['target_admissions'] > 0))
            <!-- Personal Staff Target Widget -->
            <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-indigo-900 via-slate-900 to-slate-950 text-white border border-indigo-500/30 shadow-xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-indigo-500/20 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-400/20 text-amber-300 flex items-center justify-center text-base">
                            🎯
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-white tracking-wide flex items-center gap-2">
                                <span>Your Sales &amp; Admission Target</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-500/30 text-indigo-300 font-bold uppercase tracking-wider font-mono">
                                    {{ now()->format('M Y') }}
                                </span>
                            </h3>
                            <p class="text-[11px] text-indigo-200/80">Track your monthly closures, target quota, and remaining dues</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2.5 py-1 rounded-xl font-bold font-mono {{ $myTargetProgress['revenue_pct'] >= 100 ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                            {{ $myTargetProgress['days_left'] }} Days Left
                        </span>
                        <span class="text-xs px-2.5 py-1 rounded-xl font-mono font-black {{ $myTargetProgress['revenue_pct'] >= 100 ? 'bg-emerald-500 text-white' : 'bg-indigo-600 text-white' }}">
                            {{ $myTargetProgress['revenue_pct'] }}% Achieved
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Target Revenue -->
                    <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 space-y-0.5">
                        <span class="text-[10px] text-indigo-200 uppercase tracking-wider font-bold block">Assigned Goal</span>
                        <div class="text-xl font-black text-white font-mono">
                            {{ $currency }}{{ number_format($myTargetProgress['target_revenue']) }}
                        </div>
                        <span class="text-[11px] text-indigo-300">{{ $myTargetProgress['target_admissions'] }} Admissions Goal</span>
                    </div>

                    <!-- Achieved -->
                    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 space-y-0.5">
                        <span class="text-[10px] text-emerald-300 uppercase tracking-wider font-bold block">Achieved So Far</span>
                        <div class="text-xl font-black text-emerald-400 font-mono">
                            {{ $currency }}{{ number_format($myTargetProgress['achieved_revenue']) }}
                        </div>
                        <span class="text-[11px] text-emerald-300 font-bold">{{ $myTargetProgress['achieved_admissions'] }} Closed Admissions</span>
                    </div>

                    <!-- Remaining Due -->
                    <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30 space-y-0.5">
                        <span class="text-[10px] text-amber-300 uppercase tracking-wider font-bold block">Remaining Due</span>
                        <div class="text-xl font-black text-amber-400 font-mono">
                            {{ $currency }}{{ number_format($myTargetProgress['remaining_revenue']) }}
                        </div>
                        <span class="text-[11px] text-amber-300 font-medium">{{ $myTargetProgress['remaining_admissions'] }} Admissions remaining</span>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="space-y-1.5 pt-1">
                    <div class="flex items-center justify-between text-xs text-indigo-200 font-mono">
                        <span>Pace Requirement: {{ $myTargetProgress['pace_daily_needed'] > 0 ? ($currency . number_format($myTargetProgress['pace_daily_needed']) . '/day to achieve goal') : 'Goal Completed! 🎉' }}</span>
                        <span class="font-bold text-white">{{ $myTargetProgress['revenue_pct'] }}%</span>
                    </div>
                    <div class="w-full bg-white/10 rounded-full h-3 overflow-hidden p-0.5 border border-white/10">
                        <div class="h-full rounded-full transition-all duration-700 {{ $myTargetProgress['revenue_pct'] >= 100 ? 'bg-emerald-400 shadow-lg shadow-emerald-500/50' : ($myTargetProgress['revenue_pct'] >= 50 ? 'bg-gradient-to-r from-indigo-500 to-indigo-400' : 'bg-gradient-to-r from-amber-500 to-amber-400') }}"
                             style="width: {{ min(100, $myTargetProgress['revenue_pct']) }}%"></div>
                    </div>
                </div>
            </div>
        @elseif($currentUser?->isGymOwner() || $currentUser?->isSuperAdmin() || !empty($teamTargetLeaderboard))
            <!-- Gym Owner / Manager View: Staff Targets Leaderboard -->
            <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold shadow-inner">
                            🎯
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <span>Staff Sales &amp; Admissions Targets</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 font-mono font-bold">{{ now()->format('F Y') }}</span>
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Live target tracking and quota leaderboard for sales reps and front desk admission staff</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="openSetTarget('', '', '', '')" class="px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Set Staff Target</span>
                        </button>
                        <a href="{{ route('app.crm.reports') }}#targets" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all">
                            Full Analytics ➔
                        </a>
                    </div>
                </div>

                @if(!empty($teamTargetLeaderboard))
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($teamTargetLeaderboard as $sb)
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 dark:text-white text-xs truncate">{{ $sb['name'] }}</div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 capitalize">{{ $sb['role'] }}</div>
                                    </div>
                                    @if($sb['has_target'])
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold {{ $sb['revenue_pct'] >= 100 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' }}">
                                            {{ $sb['revenue_pct'] }}% Done
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                            No Target Set
                                        </span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-3 gap-1.5 text-center font-mono">
                                    <div class="p-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                                        <span class="text-[9px] text-slate-400 block uppercase">Target</span>
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $currency }}{{ number_format($sb['target_revenue']) }}</span>
                                    </div>
                                    <div class="p-1 rounded-lg bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-500/20">
                                        <span class="text-[9px] text-emerald-600 dark:text-emerald-400 block uppercase">Achieved</span>
                                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $currency }}{{ number_format($sb['achieved_revenue']) }}</span>
                                    </div>
                                    <div class="p-1 rounded-lg bg-white dark:bg-slate-900 border border-amber-200 dark:border-amber-500/20">
                                        <span class="text-[9px] text-amber-600 dark:text-amber-400 block uppercase">Due</span>
                                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400">{{ $currency }}{{ number_format($sb['remaining_revenue']) }}</span>
                                    </div>
                                </div>

                                @if($sb['has_target'])
                                    <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full transition-all {{ $sb['revenue_pct'] >= 100 ? 'bg-emerald-500' : 'bg-indigo-600' }}"
                                             style="width: {{ min(100, $sb['revenue_pct']) }}%"></div>
                                    </div>

                                    <div class="flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400">
                                        <span>Admissions: <strong>{{ $sb['achieved_admissions'] }}</strong> / {{ $sb['target_admissions'] }}</span>
                                        <button type="button" @click="openSetTarget({{ $sb['user_id'] }}, '{{ addslashes($sb['name']) }}', {{ $sb['target_revenue'] }}, {{ $sb['target_admissions'] }})" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline cursor-pointer">Edit</button>
                                    </div>
                                @else
                                    <div class="pt-1 flex items-center justify-between">
                                        <span class="text-[10px] text-slate-500">Won: {{ $sb['achieved_admissions'] }} members</span>
                                        <button type="button" @click="openSetTarget({{ $sb['user_id'] }}, '{{ addslashes($sb['name']) }}', 0, 0)" class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-[10px] font-black shadow-xs transition-all cursor-pointer">
                                            + Set Target
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-dashed border-slate-300 dark:border-slate-800 text-center space-y-3">
                        <div class="w-10 h-10 mx-auto rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center text-lg">
                            🎯
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900 dark:text-white">No sales representatives or front desk staff added yet</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Add staff members with receptionist or sales roles under Team &amp; Staff to track their monthly revenue and admission targets.</p>
                        </div>
                        <div class="flex items-center justify-center gap-2 pt-1">
                            <a href="{{ route('app.staff.index') }}" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-sm transition-all">
                                Go to Team &amp; Staff ➔
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Pipeline Overview Funnel -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    <span>Pipeline Overview</span>
                </h3>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5">
                <a href="{{ route('app.leads.index', ['stage' => 'NEW_LEAD']) }}" class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-cyan-300 dark:hover:border-slate-700 transition-all text-center relative overflow-hidden group shadow-sm">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-cyan-400"></div>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1 group-hover:scale-105 transition-transform">{{ $pipelineStages['new_lead'] }}</div>
                    <div class="text-[9px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1 truncate">New Lead</div>
                </a>

                <a href="{{ route('app.leads.index', ['stage' => 'CONTACTED']) }}" class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-blue-300 dark:hover:border-slate-700 transition-all text-center relative overflow-hidden group shadow-sm">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-blue-500"></div>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1 group-hover:scale-105 transition-transform">{{ $pipelineStages['contacted'] }}</div>
                    <div class="text-[9px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1 truncate">Contacted</div>
                </a>

                <a href="{{ route('app.leads.index', ['stage' => 'DEMO_BOOKED']) }}" class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-amber-300 dark:hover:border-slate-700 transition-all text-center relative overflow-hidden group shadow-sm">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-amber-400"></div>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1 group-hover:scale-105 transition-transform">{{ $pipelineStages['demo_booked'] }}</div>
                    <div class="text-[9px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1 truncate">Demo Booked</div>
                </a>

                <a href="{{ route('app.leads.index', ['stage' => 'PROPOSAL_SENT']) }}" class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-teal-300 dark:hover:border-slate-700 transition-all text-center relative overflow-hidden group shadow-sm">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-teal-400"></div>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1 group-hover:scale-105 transition-transform">{{ $pipelineStages['proposal_sent'] }}</div>
                    <div class="text-[9px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1 truncate">Proposal Sent</div>
                </a>

                <a href="{{ route('app.leads.index', ['stage' => 'NEGOTIATION']) }}" class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-pink-300 dark:hover:border-slate-700 transition-all text-center relative overflow-hidden group shadow-sm">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-pink-500"></div>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1 group-hover:scale-105 transition-transform">{{ $pipelineStages['negotiation'] }}</div>
                    <div class="text-[9px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1 truncate">Negotiation</div>
                </a>

                <a href="{{ route('app.leads.index', ['stage' => 'TRIAL']) }}" class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-purple-300 dark:hover:border-slate-700 transition-all text-center relative overflow-hidden group shadow-sm">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-purple-500"></div>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1 group-hover:scale-105 transition-transform">{{ $pipelineStages['trial'] }}</div>
                    <div class="text-[9px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1 truncate">Trial</div>
                </a>

                <a href="{{ route('app.leads.index', ['stage' => 'PAID']) }}" class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-emerald-300 dark:hover:border-slate-700 transition-all text-center relative overflow-hidden group shadow-sm">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-400"></div>
                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1 group-hover:scale-105 transition-transform">{{ $pipelineStages['paid'] }}</div>
                    <div class="text-[9px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1 truncate">Paid</div>
                </a>

                <a href="{{ route('app.leads.index', ['stage' => 'LOST']) }}" class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all text-center relative overflow-hidden group shadow-sm">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-slate-400 dark:bg-slate-600"></div>
                    <div class="text-xl font-black text-slate-500 dark:text-slate-400 mt-1 group-hover:scale-105 transition-transform">{{ $pipelineStages['lost'] }}</div>
                    <div class="text-[9px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1 truncate">Lost</div>
                </a>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- Left: Lead Trend (30 Days) Area Chart -->
            <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                        <span>Lead Trend (30 days)</span>
                    </h3>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ count($trendDates) }} Days</span>
                </div>

                @php
                    $maxCount = max(1, max($trendCounts));
                    $points = [];
                    $width = 500;
                    $height = 140;
                    $count = count($trendCounts);
                    foreach ($trendCounts as $idx => $val) {
                        $x = ($idx / max(1, $count - 1)) * $width;
                        $y = $height - (($val / $maxCount) * ($height - 20)) - 10;
                        $points[] = "{$x},{$y}";
                    }
                    $pointsString = implode(' ', $points);
                    $lastX = $width;
                    $areaString = "0,{$height} {$pointsString} {$lastX},{$height}";
                @endphp

                <div class="relative pt-2">
                    <svg viewBox="0 0 500 150" class="w-full h-44 overflow-visible">
                        <defs>
                            <linearGradient id="trendGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#6366f1" stop-opacity="0.30"/>
                                <stop offset="100%" stop-color="#6366f1" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>

                        <line x1="0" y1="140" x2="500" y2="140" stroke="currentColor" class="text-slate-200 dark:text-slate-800" stroke-width="1"/>
                        <line x1="0" y1="75" x2="500" y2="75" stroke="currentColor" class="text-slate-200 dark:text-slate-800" stroke-width="1" stroke-dasharray="3 3"/>
                        <line x1="0" y1="10" x2="500" y2="10" stroke="currentColor" class="text-slate-200 dark:text-slate-800" stroke-width="1" stroke-dasharray="3 3"/>

                        <polygon points="{{ $areaString }}" fill="url(#trendGrad)"/>
                        <polyline points="{{ $pointsString }}" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>

                        @foreach($trendCounts as $idx => $val)
                            @php
                                $x = ($idx / max(1, $count - 1)) * $width;
                                $y = $height - (($val / $maxCount) * ($height - 20)) - 10;
                            @endphp
                            @if($val > 0 || $idx % 5 === 0)
                                <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $val > 0 ? 3.5 : 2 }}" fill="{{ $val > 0 ? '#6366f1' : '#94a3b8' }}" stroke="#ffffff" class="dark:stroke-slate-900" stroke-width="1.5"/>
                            @endif
                        @endforeach
                    </svg>

                    <div class="flex justify-between text-[9px] text-slate-500 dark:text-slate-400 font-mono mt-2">
                        <span>{{ $trendDates[0] ?? '' }}</span>
                        <span>{{ $trendDates[7] ?? '' }}</span>
                        <span>{{ $trendDates[14] ?? '' }}</span>
                        <span>{{ $trendDates[21] ?? '' }}</span>
                        <span>{{ $trendDates[29] ?? '' }}</span>
                    </div>
                </div>
            </div>

            <!-- Right: Lead Sources Donut Chart -->
            <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                        <span>Lead Sources</span>
                    </h3>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $totalLeads }} Total</span>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-6 pt-1">
                    <!-- SVG Donut Chart -->
                    <div class="relative w-40 h-40 shrink-0">
                        @php
                            $circumference = 2 * pi() * 45;
                            $currentOffset = 0;
                            $validSourcesTotal = max(1, $totalLeads);
                        @endphp
                        <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                            <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" class="text-slate-100 dark:text-slate-800" stroke-width="12"/>

                            @foreach($sourceBreakdown as $k => $item)
                                @if($item['count'] > 0)
                                    @php
                                        $segment = ($item['count'] / $validSourcesTotal) * $circumference;
                                        $dashArray = "{$segment} " . ($circumference - $segment);
                                    @endphp
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="{{ $item['color'] }}" stroke-width="12"
                                            stroke-dasharray="{{ $dashArray }}"
                                            stroke-dashoffset="-{{ $currentOffset }}"
                                            stroke-linecap="round"
                                            class="transition-all duration-500"/>
                                    @php
                                        $currentOffset += $segment;
                                    @endphp
                                @endif
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-xl font-black text-slate-900 dark:text-white leading-tight">{{ $totalLeads }}</span>
                            <span class="text-[8.5px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Leads</span>
                        </div>
                    </div>

                    <!-- Legend list -->
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs flex-1 w-full">
                        @foreach($sourceBreakdown as $k => $item)
                            <div class="flex items-center justify-between text-[11px] py-0.5">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $item['color'] }}"></span>
                                    <span class="text-slate-600 dark:text-slate-300 truncate">{{ $item['label'] }}</span>
                                </div>
                                <span class="font-bold text-slate-900 dark:text-white ml-2 font-mono">{{ $item['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom: Recent Leads & Upcoming Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- Recent Leads Card -->
            <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-3.5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Recent Leads</span>
                    </h3>
                    <a href="{{ route('app.leads.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 flex items-center gap-1 transition-colors">
                        <span>View All</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($recentLeads as $lead)
                        @php
                            $st = strtoupper(str_replace(' ', '_', $lead->effective_stage));
                            $badgeClass = match($st) {
                                'PAID', 'CONVERTED' => 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/30',
                                'TRIAL', 'TRIAL_SCHEDULED' => 'bg-purple-50 dark:bg-purple-500/15 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-500/30',
                                'CONTACTED' => 'bg-blue-50 dark:bg-blue-500/15 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-500/30',
                                'DEMO_BOOKED' => 'bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/30',
                                default => 'bg-cyan-50 dark:bg-cyan-500/15 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/30',
                            };
                        @endphp
                        <div class="py-2.5 flex items-center justify-between gap-3 text-xs hover:bg-slate-50 dark:hover:bg-slate-800/30 px-2 rounded-xl transition-colors">
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900 dark:text-white truncate flex items-center gap-2">
                                    <span>{{ $lead->name }}</span>
                                    @if($lead->remarks)
                                        <span class="text-[10px] text-slate-500 font-normal truncate">({{ $lead->remarks }})</span>
                                    @endif
                                </div>
                                <div class="text-[10.5px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-2">
                                    <span>{{ $lead->phone }}</span>
                                    <span>•</span>
                                    <span class="text-slate-400 dark:text-slate-500 uppercase text-[9px]">{{ str_replace('_', ' ', $lead->source ?? 'walk_in') }}</span>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="px-2 py-0.5 rounded-lg text-[9px] font-extrabold border {{ $badgeClass }}">
                                    {{ $lead->formatted_stage }}
                                </span>
                                <div class="text-[9.5px] text-slate-400 dark:text-slate-500 font-mono mt-1">
                                    {{ $lead->created_at ? $lead->created_at->format('d M') : '' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 dark:text-slate-500 text-xs">
                            No leads recorded yet. <a href="{{ route('app.leads.create') }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Click here to Add Lead</a>.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Upcoming Actions & Trials Card -->
            <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-3.5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Upcoming Actions &amp; Demos</span>
                    </h3>
                    <a href="{{ route('app.crm.trials') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 flex items-center gap-1 transition-colors">
                        <span>Calendar</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($upcomingTrials as $trial)
                        <div class="py-2.5 flex items-center justify-between gap-3 text-xs hover:bg-slate-50 dark:hover:bg-slate-800/30 px-2 rounded-xl transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-500/20 flex flex-col items-center justify-center shrink-0">
                                    <span class="text-[10px] font-black leading-none">{{ $trial->trial_date ? $trial->trial_date->format('d') : '12' }}</span>
                                    <span class="text-[8px] uppercase font-bold text-purple-600 dark:text-purple-300 leading-none mt-0.5">{{ $trial->trial_date ? $trial->trial_date->format('M') : 'Sep' }}</span>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-slate-900 dark:text-white truncate">{{ $trial->lead?->name ?? 'Prospect Demo' }}</h4>
                                    <div class="text-[10.5px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1.5">
                                        <span class="text-purple-600 dark:text-purple-400 font-mono">{{ $trial->trial_time ?? '10:00 AM' }}</span>
                                        <span>•</span>
                                        <span>{{ $trial->lead?->phone }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-500/20 uppercase">
                                    {{ $trial->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-950 flex items-center justify-center mx-auto text-slate-500 dark:text-slate-600 border border-slate-200 dark:border-slate-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p>No upcoming demos scheduled.</p>
                            <button type="button" @click="showTrialModal = true" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[11px] transition-all cursor-pointer">
                                + Book Demo Session
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Modal: Add Lead -->
        <div x-show="showLeadModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="showLeadModal = false">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span>Add New Lead</span>
                    </h3>
                    <button type="button" @click="showLeadModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs font-bold p-1 cursor-pointer">&#10005;</button>
                </div>

                <form action="{{ route('app.leads.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">First Name *</label>
                            <input type="text" name="first_name" required placeholder="e.g. Rahul" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Last Name *</label>
                            <input type="text" name="last_name" required placeholder="e.g. Sharma" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Phone Number *</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-3 text-xs font-bold text-slate-400 dark:text-slate-500">+91</span>
                                <input type="tel" name="phone" required placeholder="9876543210" class="w-full pl-12 pr-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                            <input type="email" name="email" placeholder="rahul@example.com" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Lead Stage</label>
                            <select name="stage" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="NEW_LEAD">New Lead</option>
                                <option value="CONTACTED">Contacted</option>
                                <option value="DEMO_BOOKED">Demo Booked</option>
                                <option value="PROPOSAL_SENT">Proposal Sent</option>
                                <option value="NEGOTIATION">Negotiation</option>
                                <option value="TRIAL">Trial</option>
                                <option value="PAID">Paid / Converted</option>
                                <option value="LOST">Lost</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Lead Source</label>
                            <select name="source" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="website">Website</option>
                                <option value="walk_in">Walk In</option>
                                <option value="facebook">Facebook</option>
                                <option value="instagram">Instagram</option>
                                <option value="google">Google</option>
                                <option value="referral">Referral</option>
                                <option value="expired_list">Expired List</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="phone">Phone Call</option>
                                <option value="advertising">Advertising</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Assigned Staff</label>
                            @php
                                $cUser = auth()->user();
                                $defQuickStaffId = ($cUser && !$cUser->isGymOwner() && !$cUser->isSuperAdmin() && $cUser->isStaff()) ? $cUser->id : null;
                            @endphp
                            <select name="assigned_to_user_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="">-- Unassigned --</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}" {{ $defQuickStaffId == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }} ({{ ucfirst($staff->role ?? 'Staff') }}) {{ ($cUser && $cUser->id === $staff->id) ? '- You' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Pitched Membership</label>
                                <span x-show="leadEstValue > 0" x-cloak class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $currency }}<span x-text="Number(leadEstValue).toLocaleString('en-IN')"></span>
                                </span>
                            </div>
                            <select name="membership_plan_id" 
                                    x-model="leadPlanId" 
                                    @change="onLeadPlanChange($event)"
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none cursor-pointer">
                                <option value="" data-price="">-- Select Pitched Package --</option>
                                @foreach($membershipPlans ?? [] as $plan)
                                    <option value="{{ $plan->id }}" data-price="{{ $plan->price }}">
                                        {{ $plan->name }} ({{ $plan->duration_value }} {{ ucfirst($plan->duration_type ?? 'Months') }}) • {{ $currency }}{{ number_format($plan->price) }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="estimated_value" :value="leadEstValue">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Remarks / Next Action</label>
                        <input type="text" name="remarks" placeholder="e.g. Call later, Interested in weight loss" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showLeadModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25 cursor-pointer">Create Lead</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Book Demo / Trial -->
        <div x-show="showTrialModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="showTrialModal = false">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span>🗓️ Book Demo / Trial Session</span>
                    </h3>
                    <button type="button" @click="showTrialModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs font-bold p-1 cursor-pointer">&#10005;</button>
                </div>

                <form action="{{ route('app.crm.trials.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select Lead (Demo Booked) *</label>
                        <select name="lead_id" required class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <option value="">-- Choose Lead --</option>
                            @forelse($demoBookedLeads ?? $recentLeads as $ld)
                                <option value="{{ $ld->id }}">{{ $ld->name }} ({{ $ld->phone }}) - {{ $ld->formatted_stage }}</option>
                            @empty
                                <option value="" disabled>No leads currently in 'Demo Booked' stage</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Trial Date *</label>
                            <input type="date" name="trial_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Trial Time</label>
                            <input type="text" name="trial_time" value="10:00 AM" placeholder="e.g. 01:03 PM" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Assigned Coach / Trainer</label>
                        <select name="assigned_to_user_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <option value="">-- Any Available Coach --</option>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Notes / Instructions</label>
                        <textarea name="notes" rows="2" placeholder="e.g. Free 1-day pass, interested in functional training" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showTrialModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25 cursor-pointer">Book Demo Session</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== SET STAFF SALES TARGET MODAL ==================== -->
        <div x-show="showTargetModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
             @keydown.escape.window="showTargetModal = false">
            <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 space-y-4"
                 @click.outside="showTargetModal = false">
                
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🎯</span>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">Set Sales &amp; Admission Target</h3>
                            <p class="text-[11px] text-slate-500" x-text="targetForm.name ? ('Assigning target to: ' + targetForm.name) : 'Assign target to sales rep or receptionist'"></p>
                        </div>
                    </div>
                    <button type="button" @click="showTargetModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold">✕</button>
                </div>

                <form method="POST" action="{{ route('app.crm.targets.save') }}" class="space-y-3.5">
                    @csrf
                    
                    <!-- Staff Select -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select Staff Member *</label>
                        <select name="user_id" x-model="targetForm.user_id" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <option value="">-- Choose Staff Member --</option>
                            @foreach($staffMembers as $stf)
                                <option value="{{ $stf->id }}">{{ $stf->name }} ({{ ucfirst($stf->role ?? 'Staff') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Target Period Type -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Target Period</label>
                        <select name="period_type" x-model="targetForm.period_type" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <option value="monthly">Monthly Target ({{ now()->format('F Y') }})</option>
                            <option value="weekly">Weekly Target</option>
                            <option value="custom">Custom Date Range</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3" x-show="targetForm.period_type === 'custom'">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Start Date</label>
                            <input type="date" name="start_date" x-model="targetForm.start_date" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">End Date</label>
                            <input type="date" name="end_date" x-model="targetForm.end_date" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs">
                        </div>
                    </div>

                    <!-- Revenue Target -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Target Revenue ({{ $currency }}) *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-xs font-bold text-slate-400">{{ $currency }}</span>
                            <input type="number" step="0.01" min="0" name="target_revenue" x-model="targetForm.target_revenue" required placeholder="e.g. 50000" class="w-full pl-7 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-mono font-bold focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Admission Target -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Target Closed Admissions (Members Count) *</label>
                        <input type="number" min="0" step="1" name="target_admissions" x-model="targetForm.target_admissions" required placeholder="e.g. 15" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-mono font-bold focus:border-indigo-500 focus:outline-none">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Incentive / Remarks (Optional)</label>
                        <input type="text" name="notes" placeholder="e.g. 5% commission on achieving 100% quota" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showTargetModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30">
                            Save Target
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

