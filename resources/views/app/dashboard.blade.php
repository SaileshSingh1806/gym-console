<x-app-layout header="Dashboard">
    @php
        $currency = auth()->user()->tenant?->currency_symbol ?? '₹';
        $tenantName = $tenant->name ?? 'PowerFit Gym';
        $greeting = (now()->hour < 12 ? 'Good Morning' : (now()->hour < 17 ? 'Good Afternoon' : 'Good Evening'));
    @endphp

    <div x-data="{ 
            showChurnModal: false, 
            showExpiringModal: false, 
            showBirthdayModal: false,
            trendPeriod: '30d' 
         }" 
         class="space-y-7 pb-12">

        <!-- Gym Brand Header & Greeting -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
                    <span>{{ $tenantName }}</span>
                    @if(auth()->user()->tenant?->isSubscriptionExpired())
                        <span class="px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 border border-red-500/20 text-xs font-bold uppercase tracking-wider">EXPIRED</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold uppercase tracking-wider">ACTIVE</span>
                    @endif
                </h1>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $greeting }}, <span class="text-slate-200 font-semibold">{{ auth()->user()->name }}</span>
                </p>
            </div>

            <!-- Branch Indicator Badge -->
            <div class="flex items-center gap-2">
                <div class="px-3.5 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-slate-300 flex items-center gap-2 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Branch: <strong class="text-white">{{ $branch->name ?? 'Main Branch' }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Quick Action Shortcut Buttons Bar -->
        <div class="flex items-center gap-2.5 overflow-x-auto pb-1 text-xs font-bold no-scrollbar">
            <!-- 1. Add Member -->
            <a href="{{ route('app.members.create') }}" class="px-3.5 py-2 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 border border-emerald-500/30 flex items-center gap-2 whitespace-nowrap transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Add Member</span>
            </a>

            <!-- 2. Mark Attendance -->
            <a href="{{ route('app.attendance.index') }}" class="px-3.5 py-2 rounded-xl bg-blue-500/15 hover:bg-blue-500/25 text-blue-400 border border-blue-500/30 flex items-center gap-2 whitespace-nowrap transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Mark Attendance</span>
            </a>

            <!-- 3. Record Payment -->
            <a href="{{ route('app.payments.index') }}" class="px-3.5 py-2 rounded-xl bg-purple-500/15 hover:bg-purple-500/25 text-purple-400 border border-purple-500/30 flex items-center gap-2 whitespace-nowrap transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Record Payment</span>
            </a>

            <!-- 4. Add Lead -->
            <a href="{{ route('app.leads.index') }}" class="px-3.5 py-2 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-400 border border-amber-500/30 flex items-center gap-2 whitespace-nowrap transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Add Lead</span>
            </a>

            <!-- 5. Product Sale -->
            <a href="{{ route('app.inventory.index') }}" class="px-3.5 py-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30 flex items-center gap-2 whitespace-nowrap transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Product Sale</span>
            </a>

            <!-- 6. Reports -->
            <a href="{{ route('app.expenses.index') }}" class="px-3.5 py-2 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 text-indigo-400 border border-indigo-500/30 flex items-center gap-2 whitespace-nowrap transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Reports</span>
            </a>

            <!-- 7. Open Gate IoT -->
            <a href="{{ route('app.devices.index') }}" class="px-3.5 py-2 rounded-xl bg-teal-500/15 hover:bg-teal-500/25 text-teal-400 border border-teal-500/30 flex items-center gap-2 whitespace-nowrap transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                <span>Open Gate</span>
            </a>

            <!-- 8. Tutorials -->
            <a href="{{ route('app.settings.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700/60 flex items-center gap-2 whitespace-nowrap transition-all shadow-sm">
                <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                <span>Tutorials</span>
            </a>
        </div>

        <!-- ROW 1: MEMBER HEALTH & RETENTION METRICS (Clickable Filter Cards) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">

            <!-- 1. Active Members (Links to /app/members?status=ACTIVE) -->
            <a href="{{ route('app.members.index', ['status' => 'ACTIVE']) }}" 
               class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5 hover:border-emerald-500/50 hover:bg-slate-800/60 transition-all group">
                <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-white leading-none group-hover:text-emerald-400 transition-colors">{{ $metrics['active_members'] }}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Active Members</div>
                </div>
            </a>

            <!-- 2. Expired Members (Links to /app/members?status=EXPIRED) -->
            <a href="{{ route('app.members.index', ['status' => 'EXPIRED']) }}" 
               class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5 hover:border-red-500/50 hover:bg-slate-800/60 transition-all group">
                <div class="w-11 h-11 rounded-xl bg-red-500/10 text-red-400 flex items-center justify-center shrink-0 border border-red-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-white leading-none group-hover:text-red-400 transition-colors">{{ $metrics['expired_members'] }}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Expired Members</div>
                </div>
            </a>

            <!-- 3. Churn Risk (Opens Modal) -->
            <button type="button" 
                    @click="showChurnModal = true" 
                    class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5 hover:border-rose-500/50 hover:bg-slate-800/60 transition-all group text-left cursor-pointer">
                <div class="w-11 h-11 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center shrink-0 border border-rose-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-white leading-none group-hover:text-rose-400 transition-colors">{{ $metrics['churn_risk'] }}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Churn Risk</div>
                </div>
            </button>

            <!-- 4. Expiring Soon (Opens Expiring Modal) -->
            <button type="button" 
                    @click="showExpiringModal = true" 
                    class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5 relative overflow-hidden hover:border-amber-500/50 hover:bg-slate-800/60 transition-all group text-left cursor-pointer">
                @if($metrics['expiring_soon'] > 0)
                    <span class="absolute top-2 right-2 px-1.5 py-0.2 text-[8px] font-extrabold uppercase bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded">● ALERT</span>
                @endif
                <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-white leading-none group-hover:text-amber-400 transition-colors">{{ $metrics['expiring_soon'] }}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Expiring Soon</div>
                </div>
            </button>

            <!-- 5. Dormant Members (Links to /app/members?status=INACTIVE) -->
            <a href="{{ route('app.members.index', ['status' => 'INACTIVE']) }}" 
               class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5 hover:border-amber-500/50 hover:bg-slate-800/60 transition-all group">
                <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-white leading-none group-hover:text-amber-400 transition-colors">{{ $metrics['dormant_members'] }}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Dormant Members</div>
                </div>
            </a>

            <!-- 6. Birthdays Today (Opens Birthday Modal) -->
            <button type="button" 
                    @click="showBirthdayModal = true" 
                    class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5 hover:border-amber-500/50 hover:bg-slate-800/60 transition-all group text-left cursor-pointer">
                <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/20 group-hover:scale-105 transition-transform">
                    <span class="text-lg">🎂</span>
                </div>
                <div>
                    <div class="text-2xl font-black text-white leading-none group-hover:text-amber-400 transition-colors">{{ $metrics['birthdays_today'] }}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Birthdays Today</div>
                </div>
            </button>

        </div>

        <!-- ROW 2: PERSONAL TRAINING (PT) -->
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-xs font-bold text-purple-400 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Personal Training</span>
                </div>
                <div class="flex-1 h-px bg-slate-800/80"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('app.trainers.index') }}" class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5 hover:border-purple-500/40 transition-all">
                    <div class="w-11 h-11 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center shrink-0 border border-purple-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-white leading-none">{{ $metrics['active_pt'] }}</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Active PT Packages</div>
                    </div>
                </a>

                <a href="{{ route('app.trainers.index') }}" class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5 hover:border-red-500/40 transition-all">
                    <div class="w-11 h-11 rounded-xl bg-red-500/10 text-red-400 flex items-center justify-center shrink-0 border border-red-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-white leading-none">{{ $metrics['expired_pt'] }}</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Expired PT Packages</div>
                    </div>
                </a>

                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-white leading-none">{{ $metrics['exhausted_pt'] }}</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Exhausted PT Packages</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 3: SALES & CRM -->
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-xs font-bold text-blue-400 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Sales & CRM</span>
                </div>
                <div class="flex-1 h-px bg-slate-800/80"></div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <a href="{{ route('app.leads.index') }}" class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5 hover:border-blue-500/40 transition-all">
                    <div class="w-11 h-11 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 border border-blue-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-white leading-none">{{ $metrics['active_leads'] }}</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Active Leads</div>
                    </div>
                </a>

                <a href="{{ route('app.leads.index') }}" class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5 hover:border-emerald-500/40 transition-all">
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-white leading-none">{{ $metrics['today_enquiries'] }}</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Today's Enquiries</div>
                    </div>
                </a>

                <a href="{{ route('app.leads.index') }}" class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5 hover:border-cyan-500/40 transition-all">
                    <div class="w-11 h-11 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center shrink-0 border border-cyan-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-white leading-none">{{ $metrics['today_followups'] }}</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Follow-ups Today</div>
                    </div>
                </a>

                <a href="{{ route('app.leads.index') }}" class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5 relative overflow-hidden hover:border-rose-500/40 transition-all">
                    @if($metrics['overdue_followups'] > 0)
                        <span class="absolute top-2 right-2 px-1.5 py-0.2 text-[8px] font-extrabold uppercase bg-red-500/20 text-red-400 border border-red-500/30 rounded">● URGENT</span>
                    @endif
                    <div class="w-11 h-11 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center shrink-0 border border-rose-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-white leading-none">{{ $metrics['overdue_followups'] }}</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Overdue Follow-ups</div>
                    </div>
                </a>

                <a href="{{ route('app.leads.index') }}" class="p-4 rounded-2xl bg-slate-900 border border-slate-800/90 shadow-sm flex items-center gap-3.5 hover:border-teal-500/40 transition-all">
                    <div class="w-11 h-11 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center shrink-0 border border-teal-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-white leading-none">{{ $metrics['upcoming_trials'] }}</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Upcoming Trials</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- ROW 4: FINANCIAL OVERVIEW (6 Cards) & DUES OVERVIEW (4 Cards) -->
        <div class="space-y-6">
            
            <!-- Financial Overview 6 Cards -->
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="font-bold text-white text-base">Financial Overview</h3>
                    </div>
                    <select class="bg-slate-950 border border-slate-800 text-xs font-semibold text-slate-300 rounded-xl px-3 py-1.5 focus:outline-none">
                        <option value="mtd">Month Till Date</option>
                        <option value="6m">Last 6 Months</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <!-- 1. Total Collection -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div class="text-xl font-black text-white leading-none">{{ $currency }}{{ number_format($metrics['month_revenue'], 2) }}</div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-1">Total Collection</div>
                        </div>
                        <a href="{{ route('app.payments.index') }}" class="text-[10px] text-indigo-400 hover:underline font-semibold mt-3">View More →</a>
                    </div>

                    <!-- 2. Product Sales -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <div class="text-xl font-black text-white leading-none">0.00</div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-1">Product Sales</div>
                        </div>
                        <a href="{{ route('app.inventory.index') }}" class="text-[10px] text-purple-400 hover:underline font-semibold mt-3">View More →</a>
                    </div>

                    <!-- 3. Payments Collected -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="text-xl font-black text-emerald-400 leading-none">{{ $currency }}{{ number_format($metrics['month_revenue'], 2) }}</div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-1">Payments Collected</div>
                        </div>
                        <a href="{{ route('app.payments.index') }}" class="text-[10px] text-emerald-400 hover:underline font-semibold mt-3">View More →</a>
                    </div>

                    <!-- 4. Payments Pending -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="text-xl font-black text-amber-400 leading-none">{{ $currency }}{{ number_format($metrics['all_dues_remaining'], 2) }}</div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-1">Payments Pending</div>
                        </div>
                        <a href="{{ route('app.members.index') }}" class="text-[10px] text-amber-400 hover:underline font-semibold mt-3">View More →</a>
                    </div>

                    <!-- 5. New Clients -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            </div>
                            <div class="text-xl font-black text-white leading-none">{{ $metrics['new_clients_count'] }}</div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-1">New Client(s)</div>
                        </div>
                        <a href="{{ route('app.members.index') }}" class="text-[10px] text-blue-400 hover:underline font-semibold mt-3">View More →</a>
                    </div>

                    <!-- 6. Renewals -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div class="text-xl font-black text-white leading-none">{{ $metrics['renewals_count'] }}</div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-1">Renewals</div>
                        </div>
                        <a href="{{ route('app.memberships.index') }}" class="text-[10px] text-purple-400 hover:underline font-semibold mt-3">View More →</a>
                    </div>
                </div>
            </div>

            <!-- Dues Overview (4 Cards) -->
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center gap-2">
                    <span class="text-amber-400">📊</span>
                    <h3 class="font-bold text-white text-base">Dues Overview</h3>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- 1. All Dues -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                            </div>
                            <div>
                                <div class="text-xl font-black text-white">{{ $currency }}{{ number_format($metrics['all_dues_remaining'], 0) }}</div>
                                <div class="text-[9px] font-bold text-slate-400 uppercase">All Dues</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 font-extrabold text-xs">{{ $metrics['due_members_count'] }}</span>
                    </div>

                    <!-- 2. This Month -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <div class="text-xl font-black text-white">{{ $currency }}{{ number_format($metrics['all_dues_remaining'], 0) }}</div>
                                <div class="text-[9px] font-bold text-slate-400 uppercase">This Month</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 font-extrabold text-xs">{{ $metrics['due_members_count'] }}</span>
                    </div>

                    <!-- 3. Due Today -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </div>
                            <div>
                                <div class="text-xl font-black text-white">0</div>
                                <div class="text-[9px] font-bold text-slate-400 uppercase">Due Today</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 font-extrabold text-xs">0</span>
                    </div>

                    <!-- 4. Defaulters -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-red-500/10 text-red-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <div class="text-xl font-black text-white">{{ $currency }}{{ number_format($metrics['all_dues_remaining'] > 0 ? $metrics['all_dues_remaining'] : 0, 0) }}</div>
                                <div class="text-[9px] font-bold text-slate-400 uppercase">Defaulters</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-red-500/20 text-red-300 font-extrabold text-xs">{{ $metrics['due_members_count'] }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- ROW 5: TRENDS & INSIGHTS (NEW) 4 ANALYTICS CHARTS -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-4">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    <h3 class="font-bold text-white text-base">Trends & Insights</h3>
                    <span class="px-1.5 py-0.2 rounded bg-pink-500/20 text-pink-400 text-[9px] font-black tracking-wider uppercase border border-pink-500/30">NEW</span>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-slate-950 p-1 rounded-xl border border-slate-800 text-xs font-semibold">
                        <button @click="trendPeriod = '30d'" :class="trendPeriod === '30d' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg transition-all">30D</button>
                        <button @click="trendPeriod = '90d'" :class="trendPeriod === '90d' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg transition-all">90D</button>
                        <button @click="trendPeriod = '6m'" :class="trendPeriod === '6m' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg transition-all">6M</button>
                        <button @click="trendPeriod = '1y'" :class="trendPeriod === '1y' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg transition-all">1Y</button>
                    </div>
                    <button type="button" class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-purple-600/20 hover:opacity-95">
                        <span>✨ AI Summary</span>
                    </button>
                </div>
            </div>

            <!-- 4 Charts Grid (2x2) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Chart 1: Revenue -->
                <div class="p-5 rounded-xl bg-slate-950/60 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Revenue</span>
                            <span class="text-2xl font-black text-white">{{ $currency }}{{ number_format($metrics['month_revenue'], 2) }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold">
                            ↑ Live Revenue
                        </span>
                    </div>
                    <div class="h-44 relative">
                        <canvas id="chartRevenue"></canvas>
                    </div>
                </div>

                <!-- Chart 2: Member Growth -->
                <div class="p-5 rounded-xl bg-slate-950/60 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Member Growth</span>
                            <span class="text-2xl font-black text-white">{{ $metrics['active_members'] }} active</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold">
                            ↑ {{ $metrics['new_clients_count'] }} net joins
                        </span>
                    </div>
                    <div class="h-44 relative">
                        <canvas id="chartGrowth"></canvas>
                    </div>
                </div>

                <!-- Chart 3: Daily Attendance -->
                <div class="p-5 rounded-xl bg-slate-950/60 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Daily Attendance</span>
                            <span class="text-2xl font-black text-white">{{ $metrics['today_attendance'] }}/day</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-xs font-bold">
                            Avg: 60 min
                        </span>
                    </div>
                    <div class="h-44 relative">
                        <canvas id="chartAttendance"></canvas>
                    </div>
                </div>

                <!-- Chart 4: Revenue vs Expenses -->
                <div class="p-5 rounded-xl bg-slate-950/60 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Revenue vs Expenses</span>
                            <span class="text-2xl font-black text-white">{{ $currency }}{{ number_format($metrics['net_profit'], 2) }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full {{ $metrics['net_profit'] >= 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }} text-xs font-bold">
                            {{ $metrics['net_profit'] >= 0 ? 'Profitable' : 'Deficit' }}
                        </span>
                    </div>
                    <div class="h-44 relative">
                        <canvas id="chartRevVsExp"></canvas>
                    </div>
                </div>

            </div>
        </div>

        <!-- ROW 6: 6 LIVE OPERATIONAL WIDGET CARDS (3x2 Grid) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Widget 1: Expiring This Week (with WhatsApp buttons) -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-slate-800">
                        <h4 class="font-bold text-xs text-amber-400 flex items-center gap-1.5 uppercase">
                            <span>⚠️ Expiring This Week</span>
                            <span class="px-1.5 py-0.2 rounded-full bg-amber-500/20 text-amber-300 text-[10px]">({{ count($metrics['expiring_members_list']) }})</span>
                        </h4>
                        <div class="flex items-center gap-1.5">
                            <button type="button" class="px-2 py-1 rounded bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-white text-[10px] font-bold flex items-center gap-1 transition-all">
                                <span>🟢 Send All</span>
                            </button>
                            <a href="{{ route('app.members.index', ['filter' => 'expiring']) }}" class="text-[11px] text-slate-400 hover:text-white">View All</a>
                        </div>
                    </div>

                    <div class="space-y-2 max-h-56 overflow-y-auto">
                        @forelse($metrics['expiring_members_list'] as $exp)
                            <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-[10px]">
                                        {{ substr($exp->member?->first_name ?? 'M', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white leading-tight">{{ $exp->member?->full_name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $exp->plan?->name ?? 'Standard' }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @php
                                        $daysLeft = max(0, \Carbon\Carbon::parse($exp->end_date)->diffInDays(now()));
                                    @endphp
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        {{ $daysLeft }}d
                                    </span>
                                    @if($exp->member?->phone)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $exp->member->phone) }}?text=Hi%20{{ urlencode($exp->member->first_name) }},%20your%20gym%20membership%20at%20{{ urlencode($tenantName) }}%20is%20expiring%20on%20{{ $exp->end_date }}.%20Please%20renew%20to%20continue%20your%20workouts!" target="_blank" title="Send WhatsApp Reminder" class="p-1 rounded bg-emerald-500/20 hover:bg-emerald-500 text-emerald-400 hover:text-white transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.53 1.771.815 2.796.815 3.18 0 5.767-2.587 5.768-5.766 0-3.18-2.586-5.768-5.768-5.768zm3.393 8.167c-.145.408-.839.774-1.208.825-.331.045-.764.08-2.222-.524-1.865-.772-3.056-2.677-3.149-2.8-.093-.124-.754-.999-.754-1.905s.475-1.353.644-1.539c.169-.187.369-.234.492-.234.124 0 .247.001.355.006.113.005.263-.043.411.314.153.37.524 1.28.57 1.373.046.093.077.201.015.324-.062.124-.093.201-.185.308-.093.108-.195.241-.278.324-.093.093-.19.195-.082.38.108.186.48 1.012 1.031 1.503.711.633 1.311.83 1.496.922.185.093.293.078.401-.046.108-.124.463-.54.587-.725.124-.185.247-.154.416-.093.169.062 1.076.507 1.261.6.185.093.308.139.354.216.046.077.046.447-.099.855z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-500 text-xs">
                                <span>No memberships expiring this week.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Widget 2: Today's Birthdays -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-slate-800">
                        <h4 class="font-bold text-xs text-amber-400 flex items-center gap-1.5 uppercase">
                            <span>🎂 Today's Birthdays</span>
                            <span class="px-1.5 py-0.2 rounded-full bg-amber-500/20 text-amber-300 text-[10px]">({{ count($metrics['birthday_members_list']) }})</span>
                        </h4>
                        <button type="button" class="text-[11px] text-slate-400 hover:text-white">⚙️ Templates</button>
                    </div>

                    <div class="space-y-2 max-h-56 overflow-y-auto">
                        @forelse($metrics['birthday_members_list'] as $bday)
                            <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-pink-500/20 text-pink-400 flex items-center justify-center font-bold text-[10px]">
                                        🎂
                                    </div>
                                    <div>
                                        <div class="font-bold text-white leading-tight">{{ $bday->full_name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $bday->phone ?? 'Member' }}</div>
                                    </div>
                                </div>
                                @if($bday->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $bday->phone) }}?text=Happy%20Birthday%20{{ urlencode($bday->first_name) }}!%20Wishing%20you%20a%20healthy%20and%20fit%20year%20ahead%20from%20{{ urlencode($tenantName) }}!%20🎉" target="_blank" class="px-2 py-1 rounded bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-white text-[10px] font-bold flex items-center gap-1 transition-all">
                                        <span>Wish on WhatsApp</span>
                                    </a>
                                @endif
                            </div>
                        @empty
                            <div class="py-12 flex flex-col items-center justify-center text-center text-slate-500 text-xs">
                                <span class="text-2xl mb-1">🎂</span>
                                <span>No birthdays today.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Widget 3: Recently Expired -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-slate-800">
                        <h4 class="font-bold text-xs text-rose-400 flex items-center gap-1.5 uppercase">
                            <span>🔴 Recently Expired</span>
                            <span class="px-1.5 py-0.2 rounded-full bg-rose-500/20 text-rose-300 text-[10px]">({{ count($metrics['recently_expired_list']) }})</span>
                        </h4>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('app.members.index', ['status' => 'EXPIRED']) }}" class="text-[11px] text-slate-400 hover:text-white">View All</a>
                        </div>
                    </div>

                    <div class="space-y-2 max-h-56 overflow-y-auto">
                        @forelse($metrics['recently_expired_list'] as $recExp)
                            <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-red-500/20 text-red-400 flex items-center justify-center font-bold text-[10px]">
                                        {{ substr($recExp->member?->first_name ?? 'M', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white leading-tight">{{ $recExp->member?->full_name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $recExp->plan?->name ?? 'Membership' }}</div>
                                    </div>
                                </div>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $recExp->member?->phone ?? '') }}?text=Hi%20{{ urlencode($recExp->member?->first_name ?? 'there') }},%20we%20miss%20you%20at%20{{ urlencode($tenantName) }}!%20Get%20special%20renewal%20offers%20today." target="_blank" class="px-2 py-1 rounded bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-white text-[10px] font-bold transition-all">
                                    Reach Out
                                </a>
                            </div>
                        @empty
                            <div class="py-12 flex flex-col items-center justify-center text-center text-slate-500 text-xs">
                                <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>No recently expired memberships.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Widget 4: Recent Payments -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-slate-800">
                        <h4 class="font-bold text-xs text-emerald-400 flex items-center gap-1.5 uppercase">
                            <span>₹ Recent Payments</span>
                        </h4>
                        <a href="{{ route('app.payments.index') }}" class="text-[11px] text-slate-400 hover:text-white">View All</a>
                    </div>

                    <div class="space-y-2 max-h-56 overflow-y-auto">
                        @forelse($metrics['recent_payments'] as $pay)
                            <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-bold text-indigo-400 leading-tight">{{ $pay->member?->full_name ?? 'Member Payment' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $pay->membership?->plan?->name ?? 'Plan Fee' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-extrabold text-emerald-400">{{ $currency }}{{ number_format($pay->amount, 2) }}</div>
                                    <div class="text-[9px] text-slate-500">{{ \Carbon\Carbon::parse($pay->payment_date)->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-500 text-xs">No payments recorded yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Widget 5: Recent Members -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-slate-800">
                        <h4 class="font-bold text-xs text-blue-400 flex items-center gap-1.5 uppercase">
                            <span>👥 Recent Members</span>
                        </h4>
                        <a href="{{ route('app.members.index') }}" class="text-[11px] text-slate-400 hover:text-white">View All</a>
                    </div>

                    <div class="space-y-2 max-h-56 overflow-y-auto">
                        @forelse($metrics['recent_members_list'] as $rm)
                            <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-bold text-blue-400 leading-tight">{{ $rm->full_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $rm->phone }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-slate-300 text-[11px] font-semibold">{{ $rm->activeMembership?->plan?->name ?? 'Standard' }}</div>
                                    <div class="text-[9px] text-slate-500">{{ $rm->created_at->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-500 text-xs">No members enrolled yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Widget 6: Lead Follow-ups -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-slate-800">
                        <h4 class="font-bold text-xs text-cyan-400 flex items-center gap-1.5 uppercase">
                            <span>📞 Lead Follow-ups</span>
                            @if($metrics['overdue_followups'] > 0)
                                <span class="px-1.5 py-0.2 rounded bg-red-500/20 text-red-400 text-[9px] font-bold">1 overdue</span>
                            @endif
                        </h4>
                        <a href="{{ route('app.leads.index') }}" class="text-[11px] text-slate-400 hover:text-white">View All</a>
                    </div>

                    <div class="space-y-2 max-h-56 overflow-y-auto">
                        @forelse($metrics['lead_followups_list'] as $lead)
                            <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-bold text-blue-400 leading-tight">{{ $lead->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $lead->phone }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($lead->follow_up_date && \Carbon\Carbon::parse($lead->follow_up_date)->isPast())
                                        <span class="text-[10px] text-red-400 font-bold flex items-center gap-1">
                                            <span>●</span> {{ \Carbon\Carbon::parse($lead->follow_up_date)->format('d M') }}
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-400">
                                            {{ $lead->follow_up_date ? \Carbon\Carbon::parse($lead->follow_up_date)->format('d M') : 'Pending' }}
                                        </span>
                                    @endif
                                    <a href="{{ route('app.leads.index') }}" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 text-[10px] font-bold">
                                        Follow up
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-500 text-xs">No pending lead follow-ups.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- POPUP MODALS (Matching FitPlex Modals) -->
        <!-- ========================================================================= -->

        <!-- 1. Churn Risk Alert Modal (Screenshot 1) -->
        <div x-show="showChurnModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div @click.away="showChurnModal = false" 
                 class="w-full max-w-xl bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl space-y-0 text-slate-100">
                <!-- Modal Header -->
                <div class="p-5 bg-rose-500/10 border-b border-rose-500/20 flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-rose-400">Churn Risk Alert</h3>
                            <p class="text-xs text-rose-300/80">No visit in 7+ days and membership expiring within 7 days</p>
                        </div>
                    </div>
                    <button @click="showChurnModal = false" class="p-1.5 text-slate-400 hover:text-white rounded-lg">✕</button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-5">
                    <!-- Why is this a high risk info box -->
                    <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-2 text-xs">
                        <div class="flex items-center gap-2 text-rose-400 font-bold">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            <span>Why is this a high risk?</span>
                        </div>
                        <p class="text-slate-300 leading-relaxed">
                            These members have not checked into the gym for over a week, and their plans expire in less than 7 days. A lack of attendance right before renewal is the strongest indicator that a member is going to drop out (churn).
                        </p>
                        <div class="pt-2 border-t border-slate-800/80 space-y-1">
                            <span class="font-bold text-slate-200 block">📋 Recommended Actions:</span>
                            <ul class="space-y-1 text-slate-400 pl-1">
                                <li>📞 <strong>Call immediately:</strong> Check in on their fitness journey and ask if they need help.</li>
                                <li>💬 <strong>WhatsApp Outreach:</strong> Send a renewal reminder or special re-engagement offer.</li>
                                <li>🎁 <strong>Special Offer:</strong> Offer a personal training session or renewal perk to win them back.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Member Status / List -->
                    <div class="py-6 flex flex-col items-center justify-center text-center">
                        <div class="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-300">No churn risk members detected.</p>
                        <p class="text-xs text-slate-500 mt-0.5">Your gym member engagement is performing well.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Expiring Soon Modal (Screenshot 2) -->
        <div x-show="showExpiringModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div @click.away="showExpiringModal = false" 
                 class="w-full max-w-xl bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl space-y-0 text-slate-100">
                <!-- Modal Header -->
                <div class="p-5 bg-amber-500/10 border-b border-amber-500/20 flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-amber-400">Expiring Soon</h3>
                            <p class="text-xs text-amber-300/80">Members expiring within 7 days</p>
                        </div>
                    </div>
                    <button @click="showExpiringModal = false" class="p-1.5 text-slate-400 hover:text-white rounded-lg">✕</button>
                </div>

                <!-- Modal Body: Member Table List -->
                <div class="p-4 overflow-x-auto max-h-96">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/70 border-b border-slate-800 text-slate-400 text-[10px] uppercase">
                            <tr>
                                <th class="py-2.5 px-3">Member</th>
                                <th class="py-2.5 px-3">Plan</th>
                                <th class="py-2.5 px-3">Expires</th>
                                <th class="py-2.5 px-3">Days</th>
                                <th class="py-2.5 px-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            @forelse($metrics['expiring_members_list'] as $exp)
                                <tr>
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-white">{{ $exp->member?->full_name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $exp->member?->phone }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-300">{{ $exp->plan?->name ?? 'Standard Plan' }}</td>
                                    <td class="py-2.5 px-3 text-slate-400">{{ \Carbon\Carbon::parse($exp->end_date)->format('d M Y') }}</td>
                                    <td class="py-2.5 px-3">
                                        @php $dl = max(0, \Carbon\Carbon::parse($exp->end_date)->diffInDays(now())); @endphp
                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-500/20 text-amber-400">
                                            {{ $dl }}d
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        @if($exp->member?->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $exp->member->phone) }}" target="_blank" class="px-2 py-1 rounded bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-white font-bold text-[10px] transition-all">
                                                WhatsApp
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-500">No members expiring within 7 days.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-slate-950 border-t border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400">{{ count($metrics['expiring_members_list']) }} members expiring within 7 days</span>
                    <a href="{{ route('app.members.index', ['filter' => 'expiring']) }}" class="text-amber-400 font-bold hover:underline flex items-center gap-1">
                        <span>↗ View All</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 3. Birthdays Today Modal -->
        <div x-show="showBirthdayModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div @click.away="showBirthdayModal = false" 
                 class="w-full max-w-xl bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl space-y-0 text-slate-100">
                <!-- Modal Header -->
                <div class="p-5 bg-amber-500/10 border-b border-amber-500/20 flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl">
                            🎂
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-amber-400">Birthdays Today</h3>
                            <p class="text-xs text-amber-300/80">Celebrate and send wishes to members born today</p>
                        </div>
                    </div>
                    <button @click="showBirthdayModal = false" class="p-1.5 text-slate-400 hover:text-white rounded-lg cursor-pointer">✕</button>
                </div>

                <!-- Modal Body: Member Table List -->
                <div class="p-4 overflow-x-auto max-h-96">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/70 border-b border-slate-800 text-slate-400 text-[10px] uppercase">
                            <tr>
                                <th class="py-2.5 px-3">Member</th>
                                <th class="py-2.5 px-3">Contact</th>
                                <th class="py-2.5 px-3">Date of Birth</th>
                                <th class="py-2.5 px-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            @forelse($metrics['birthday_members_list'] ?? [] as $bm)
                                <tr>
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-white">{{ $bm->full_name }}</div>
                                        <div class="text-[10px] text-amber-400 font-mono">{{ $bm->member_code }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-300">{{ $bm->phone }}</td>
                                    <td class="py-2.5 px-3 text-slate-400">{{ $bm->dob ? \Carbon\Carbon::parse($bm->dob)->format('d M') : 'Today' }}</td>
                                    <td class="py-2.5 px-3">
                                        @if($bm->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $bm->phone) }}?text={{ urlencode('Wishing you a very Happy Birthday from all of us at ' . ($tenantName ?? 'Gym Console') . '! 🎂🎉 Stay fit and healthy!') }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-white font-bold text-[10px] transition-all inline-flex items-center gap-1">
                                                <span>💬 Send Wish</span>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-500">No member birthdays today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-slate-950 border-t border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400">{{ count($metrics['birthday_members_list'] ?? []) }} birthdays today</span>
                    <a href="{{ route('app.members.index', ['filter' => 'birthday']) }}" class="text-amber-400 font-bold hover:underline flex items-center gap-1">
                        <span>↗ View in Members</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts Core Javascript Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartConfig = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } },
                    y: { grid: { color: 'rgba(148, 163, 184, 0.08)' }, ticks: { color: '#64748b', font: { size: 10 }, beginAtZero: true } }
                }
            };

            const trendData = {{ Js::from($metrics['trends'] ?? ['labels' => [], 'revenue' => [], 'prev_revenue' => [], 'joins' => [], 'attendance' => []]) }};
            const monthlyChartData = {{ Js::from($metrics['chart'] ?? ['labels' => [], 'income' => [], 'expenses' => [], 'profit' => []]) }};

            // 1. Revenue Comparison Chart
            const ctxRev = document.getElementById('chartRevenue');
            if (ctxRev) {
                new Chart(ctxRev, {
                    type: 'line',
                    data: {
                        labels: trendData.labels || [],
                        datasets: [
                            {
                                label: 'Current Revenue',
                                data: trendData.revenue || [],
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2.5,
                                pointRadius: 3
                            },
                            {
                                label: 'Previous Period',
                                data: trendData.prev_revenue || [],
                                borderColor: '#475569',
                                borderDash: [4, 4],
                                tension: 0.4,
                                borderWidth: 1.5,
                                pointRadius: 0
                            }
                        ]
                    },
                    options: chartConfig
                });
            }

            // 2. Member Growth Chart
            const ctxGrowth = document.getElementById('chartGrowth');
            if (ctxGrowth) {
                new Chart(ctxGrowth, {
                    type: 'bar',
                    data: {
                        labels: trendData.labels || [],
                        datasets: [
                            {
                                label: 'New Joins',
                                data: trendData.joins || [],
                                backgroundColor: '#10b981',
                                borderRadius: 4
                            }
                        ]
                    },
                    options: chartConfig
                });
            }

            // 3. Daily Attendance Chart
            const ctxAtt = document.getElementById('chartAttendance');
            if (ctxAtt) {
                new Chart(ctxAtt, {
                    type: 'line',
                    data: {
                        labels: trendData.labels || [],
                        datasets: [
                            {
                                label: 'Attendance',
                                data: trendData.attendance || [],
                                borderColor: '#8b5cf6',
                                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                                fill: true,
                                tension: 0.3,
                                borderWidth: 2,
                                pointRadius: 3
                            }
                        ]
                    },
                    options: chartConfig
                });
            }

            // 4. Revenue vs Expenses Chart (6 Months)
            const ctxRevExp = document.getElementById('chartRevVsExp');
            if (ctxRevExp) {
                new Chart(ctxRevExp, {
                    type: 'bar',
                    data: {
                        labels: monthlyChartData.labels || [],
                        datasets: [
                            {
                                label: 'Revenue',
                                data: monthlyChartData.income || [],
                                backgroundColor: '#10b981',
                                borderRadius: 4
                            },
                            {
                                label: 'Expenses',
                                data: monthlyChartData.expenses || [],
                                backgroundColor: '#ef4444',
                                borderRadius: 4
                            }
                        ]
                    },
                    options: chartConfig
                });
            }
        });
    </script>
</x-app-layout>
