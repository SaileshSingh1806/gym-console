<x-admin-layout header="SaaS Global Platform Metrics & Control">
    <div class="space-y-8" x-data="{ showNewGymModal: false, showNewPlanModal: false }">
        
        <!-- Quick Action & Command Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm dark:shadow-xl dark:shadow-black/20 transition-colors">
            <div>
                <div class="flex items-center gap-2.5 mb-1.5 flex-wrap">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight">Platform Command Center &bull; Administrator Controls</h2>
                    <span class="px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 text-[10px] font-bold font-mono">SUPER ADMIN</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Platform Administrator Controls: Total system control over tenant gyms, platform users, active tickets, and annual revenue.</p>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                <button @click="showNewGymModal = true" class="px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-2xl bg-red-500 hover:bg-red-600 text-white font-bold text-xs flex items-center gap-1.5 sm:gap-2 shadow-lg shadow-red-500/20 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Register Gym</span>
                </button>
                <button @click="showNewPlanModal = true" class="px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-slate-700/60 font-bold text-xs flex items-center gap-1.5 sm:gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Plan</span>
                </button>
            </div>
        </div>

        <!-- TOP 4 PRIMARY METRIC CARDS (Custom requested metrics) -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
            
            <!-- Card 1: Total Gym Businesses (User requested: Vahi Rakho) -->
            <a href="{{ route('admin.gyms') }}" class="p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-indigo-500 shadow-sm hover:shadow-md dark:hover:shadow-indigo-500/5 transition-all group block">
                <div class="flex items-center justify-between mb-2 sm:mb-3">
                    <span class="text-[10px] sm:text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gym Businesses</span>
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl sm:rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20 flex items-center justify-center text-xs sm:text-base group-hover:scale-110 transition-transform shrink-0">
                        🏢
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white leading-none mb-1.5 sm:mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                    {{ $metrics['total_gyms'] }}
                </div>
                <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-1 sm:gap-1.5 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $metrics['active_gyms'] }} Active</span>
                    <span class="text-slate-400 hidden sm:inline">&bull;</span>
                    <span class="text-amber-600 dark:text-amber-400 font-semibold">{{ $metrics['trial_gyms'] }} Trial</span>
                </div>
            </a>

            <!-- Card 2: Total Platform Users (User requested: Platform user kitne hai total) -->
            <a href="{{ route('admin.users') }}" class="p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-blue-500 shadow-sm hover:shadow-md dark:hover:shadow-blue-500/5 transition-all group block">
                <div class="flex items-center justify-between mb-2 sm:mb-3">
                    <span class="text-[10px] sm:text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Platform Users</span>
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl sm:rounded-2xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20 flex items-center justify-center text-xs sm:text-base group-hover:scale-110 transition-transform shrink-0">
                        👥
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white leading-none mb-1.5 sm:mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                    {{ $totalUsers }}
                </div>
                <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-1 sm:gap-1.5 font-medium">
                    <span class="text-blue-600 dark:text-blue-300 font-semibold">{{ $totalStaffCount }} Staff</span>
                    <span class="text-slate-400 hidden sm:inline">&bull;</span>
                    <span class="text-slate-600 dark:text-slate-400">{{ $superAdminCount }} Admin</span>
                </div>
            </a>

            <!-- Card 3: Support Tickets (User requested: Ticket kitne hai) -->
            <a href="{{ route('admin.tickets.index') }}" class="p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-rose-500 shadow-sm hover:shadow-md dark:hover:shadow-rose-500/5 transition-all group block">
                <div class="flex items-center justify-between mb-2 sm:mb-3">
                    <span class="text-[10px] sm:text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Support Tickets</span>
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl sm:rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 flex items-center justify-center text-xs sm:text-base group-hover:scale-110 transition-transform shrink-0">
                        🎫
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black {{ $openTicketsCount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }} leading-none mb-1.5 sm:mb-2 group-hover:text-rose-500 dark:group-hover:text-rose-300 transition-colors">
                    {{ $openTicketsCount }} <span class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 font-normal">Open</span>
                </div>
                <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-1 sm:gap-1.5 font-medium">
                    @if($urgentTicketsCount > 0)
                        <span class="text-rose-600 dark:text-rose-400 font-bold">⚠️ {{ $urgentTicketsCount }} Urgent</span>
                        <span class="text-slate-400 hidden sm:inline">&bull;</span>
                    @endif
                    <span>{{ $totalTicketsCount }} Total</span>
                </div>
            </a>

            <!-- Card 4: Annual Platform Revenue (ARR) (Pure Yearly) -->
            <a href="{{ route('admin.subscriptions') }}" class="p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-emerald-500 shadow-sm hover:shadow-md dark:hover:shadow-emerald-500/5 transition-all group block">
                <div class="flex items-center justify-between mb-2 sm:mb-3">
                    <span class="text-[10px] sm:text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Annual ARR</span>
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl sm:rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 flex items-center justify-center text-xs sm:text-base group-hover:scale-110 transition-transform shrink-0">
                        💰
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-emerald-600 dark:text-emerald-400 leading-none mb-1.5 sm:mb-2 group-hover:text-emerald-500 dark:group-hover:text-emerald-300 transition-colors truncate">
                    ₹{{ number_format($metrics['arr'] ?? $metrics['total_revenue'], 0) }}
                </div>
                <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 font-medium truncate">
                    <span>Invoiced: <strong class="text-slate-900 dark:text-white">₹{{ number_format($metrics['total_revenue'], 0) }}</strong></span>
                </div>
            </a>

        </div>

        <!-- QUICK SYSTEM VITALS STRIP -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 shadow-xs">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-500/20 flex items-center justify-center text-sm shrink-0">
                    🏋️
                </div>
                <div>
                    <div class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white">{{ number_format($totalMembers) }}</div>
                    <div class="text-[9px] sm:text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Gym Members</div>
                </div>
            </div>

            <div class="flex items-center gap-2.5 sm:gap-3">
                <div class="w-8 h-8 rounded-xl bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-200 dark:border-teal-500/20 flex items-center justify-center text-sm shrink-0">
                    📍
                </div>
                <div>
                    <div class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white">{{ number_format($totalBranchesCount) }}</div>
                    <div class="text-[9px] sm:text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Gym Branches</div>
                </div>
            </div>

            <div class="flex items-center gap-2.5 sm:gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20 flex items-center justify-center text-sm shrink-0">
                    📜
                </div>
                <div>
                    <div class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white">{{ $activeSubscriptionsCount }}</div>
                    <div class="text-[9px] sm:text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Subscriptions</div>
                </div>
            </div>

            <div class="flex items-center gap-2.5 sm:gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 flex items-center justify-center text-sm shrink-0">
                    ⚡
                </div>
                <div>
                    <div class="text-xs sm:text-sm font-extrabold text-emerald-600 dark:text-emerald-400">100% Active</div>
                    <div class="text-[9px] sm:text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">System Health</div>
                </div>
            </div>
        </div>

        <!-- PLATFORM OPERATIONS & FINANCIAL HUB -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left: Real-Time SaaS Financial Trail & Invoices (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Recent Invoices Table -->
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm dark:shadow-xl">
                    <div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/40 flex justify-between items-center">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">💳</span>
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Recent SaaS Platform Invoices & Collections</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Live transaction trail of annual gym subscriptions and renewals</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.subscriptions') }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline transition-colors flex items-center gap-1">
                            <span>Manage Subscriptions</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs min-w-[650px]">
                            <thead class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-3.5 px-4 font-bold">Invoice #</th>
                                    <th class="py-3.5 px-4 font-bold">Subscriber (Gym Owner & Gym)</th>
                                    <th class="py-3.5 px-4 font-bold">Subscribed Plan</th>
                                    <th class="py-3.5 px-4 font-bold">Amount</th>
                                    <th class="py-3.5 px-4 font-bold">Date</th>
                                    <th class="py-3.5 px-4 font-bold text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                                @forelse($recentInvoices as $inv)
                                    @php
                                        $owner = $inv->tenant?->users?->firstWhere('role', 'gym_owner') ?? $inv->tenant?->users?->first();
                                    @endphp
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                        <td class="py-3.5 px-4 font-mono font-bold text-red-600 dark:text-red-400">
                                            {{ $inv->invoice_number }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="font-bold text-slate-900 dark:text-white block text-xs">{{ $owner?->name ?? 'Gym Owner' }}</span>
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block">{{ $owner?->email ?? $inv->tenant?->email }}</span>
                                            <span class="inline-flex items-center gap-1 text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">
                                                🏢 {{ $inv->tenant?->name ?? 'Gym Facility' }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-bold text-[10px]">
                                                {{ $inv->subscription?->plan?->name ?? 'SaaS Plan' }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 block mt-0.5 font-medium">
                                                {{ ucfirst($inv->subscription?->billing_cycle ?? 'yearly') }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 font-black text-slate-900 dark:text-white">
                                            {{ $inv->currency === 'INR' ? '₹' : ($inv->currency === 'EUR' ? '€' : ($inv->currency === 'GBP' ? '£' : '$')) }}{{ number_format($inv->total, 2) }}
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            {{ $inv->invoice_date?->format('d M Y') ?? '—' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                {{ $inv->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 text-center text-slate-400 dark:text-slate-500">
                                            <div class="text-2xl mb-1">🧾</div>
                                            <p class="font-semibold text-xs">No platform invoices recorded yet.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-950/50 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span>Showing latest {{ count($recentInvoices) }} transaction records</span>
                        <a href="{{ route('admin.subscriptions') }}" class="text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-semibold">Reconcile All &rarr;</a>
                    </div>
                </div>

                <!-- Active Gym Businesses Directory -->
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm dark:shadow-xl">
                    <div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/40 flex justify-between items-center">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">🏢</span>
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Registered Gym Tenants</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Quick roster of gyms onboarded on the platform</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.gyms') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline transition-colors flex items-center gap-1">
                            <span>View All Gyms</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($recentGyms as $gym)
                            @php
                                $gymOwner = $gym->users?->firstWhere('role', 'gym_owner') ?? $gym->users?->first();
                            @endphp
                            <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-black text-slate-900 dark:text-white text-sm">{{ $gym->name }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold {{ $gym->status === 'ACTIVE' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' }}">
                                            {{ $gym->status }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex flex-wrap items-center gap-2">
                                        <span>Owner: <strong class="text-slate-800 dark:text-slate-200">{{ $gymOwner?->name ?? 'Admin' }}</strong> ({{ $gymOwner?->email ?? $gym->email }})</span>
                                        <span>&bull;</span>
                                        <span>Branches: <strong class="text-slate-800 dark:text-slate-200">{{ $gym->branches?->count() ?? 1 }}</strong></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 self-end sm:self-auto">
                                    <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-amber-600 dark:text-amber-400 font-bold text-[10px]">
                                        {{ $gym->activeSubscription?->plan?->name ?? 'Free / Trial' }}
                                    </span>
                                    <a href="{{ route('admin.gyms.impersonate', $gym->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-transparent font-bold text-[10px] transition-all">
                                        Open Console ↗
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs">No gyms registered yet.</div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- Right: Action Center & Support Tickets (1 col) -->
            <div class="space-y-6">
                
                <!-- Support Tickets Action Center -->
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 shadow-sm dark:shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🎫</span>
                            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Support Desk</h3>
                        </div>
                        <a href="{{ route('admin.tickets.index') }}" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline font-bold">
                            View All ({{ $totalTicketsCount }}) &rarr;
                        </a>
                    </div>

                    <div class="space-y-3">
                        @if($openSupportTickets->isNotEmpty())
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-2">Active Tickets</span>
                                @foreach($openSupportTickets as $tck)
                                    <a href="{{ route('admin.tickets.show', $tck->id) }}" class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 block transition-all mb-2">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="font-bold text-slate-900 dark:text-white text-xs truncate">{{ $tck->subject }}</span>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $tck->priority === 'urgent' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' }}">
                                                {{ $tck->priority }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400">
                                            <span>{{ $tck->tenant?->name ?? 'Gym Tenant' }}</span>
                                            <span class="font-mono text-slate-400 dark:text-slate-500">#{{ $tck->ticket_number }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="py-6 text-center text-slate-400 space-y-1">
                                <div class="text-2xl">🟢</div>
                                <p class="text-xs font-bold text-slate-900 dark:text-white">All Tickets Resolved</p>
                                <p class="text-[11px] text-slate-500">No open customer support tickets pending response.</p>
                            </div>
                        @endif

                        @if($expiringSubscriptions->isNotEmpty())
                            <div class="pt-3 border-t border-slate-200 dark:border-slate-800/80">
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-2">Upcoming Renewals</span>
                                @foreach($expiringSubscriptions as $expSub)
                                    @php
                                        $dLeft = (int) now()->diffInDays($expSub->ends_at, false);
                                    @endphp
                                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs mb-2">
                                        <div>
                                            <span class="font-bold text-slate-900 dark:text-white block">{{ $expSub->tenant->name }}</span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ $expSub->plan?->name }} • Annual</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $dLeft < 7 ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' }}">
                                            {{ $dLeft }}d left
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Platform Diagnostics & Core Engine Status -->
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 shadow-sm dark:shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <span>🛡️</span>
                            <span>Platform Engine Status</span>
                        </h3>
                        <a href="{{ route('admin.settings') }}" class="text-[10px] text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-semibold">Settings &rarr;</a>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
                            <span class="text-slate-700 dark:text-slate-300 flex items-center gap-2 font-medium">
                                <span>🤖</span>
                                <span>Gemini AI Engine</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                🟢 Operational
                            </span>
                        </div>

                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
                            <span class="text-slate-700 dark:text-slate-300 flex items-center gap-2 font-medium">
                                <span>📧</span>
                                <span>SMTP Mail Gateway</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                🟢 Active
                            </span>
                        </div>

                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
                            <span class="text-slate-700 dark:text-slate-300 flex items-center gap-2 font-medium">
                                <span>💳</span>
                                <span>Payment Reconciliation</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                🟢 Live
                            </span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Add Gym Modal -->
        <div x-show="showNewGymModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl max-w-xl w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 shadow-2xl" @click.away="showNewGymModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Register New Gym Tenant</h3>
                    <button @click="showNewGymModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">✕</button>
                </div>

                <form action="{{ route('admin.gyms.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Gym Name *</label>
                            <input type="text" name="gym_name" required placeholder="Titan Fitness Club" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Primary Branch Name</label>
                            <input type="text" name="branch_name" placeholder="Downtown Flagship" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Owner Name *</label>
                            <input type="text" name="owner_name" required placeholder="John Owner" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Owner Email *</label>
                            <input type="email" name="email" required placeholder="owner@titan.com" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                            <input type="text" name="phone" placeholder="+1 555-0100" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Owner Password *</label>
                            <input type="password" name="password" required value="password" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">SaaS Plan *</label>
                            <select name="plan_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Billing Cycle *</label>
                            <select name="billing_cycle" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                <option value="yearly" selected>📅 Yearly (Annual)</option>
                                <option value="monthly">🗓️ Monthly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status / Mode</label>
                            <select name="status" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                <option value="ACTIVE" selected>🟢 ACTIVE (Paid)</option>
                                <option value="TRIAL">🟡 TRIAL (Free)</option>
                                <option value="SUSPENDED">🔴 SUSPENDED</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Currency</label>
                            <select name="currency" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                <option value="INR" selected>INR (₹) - Indian Rupee</option>
                                <option value="USD">USD ($) - US Dollar</option>
                                <option value="EUR">EUR (€) - Euro</option>
                                <option value="GBP">GBP (£) - British Pound</option>
                                <option value="AED">AED (د.إ) - UAE Dirham</option>
                                <option value="CAD">CAD ($) - Canadian Dollar</option>
                                <option value="AUD">AUD ($) - Australian Dollar</option>
                                <option value="SGD">SGD ($) - Singapore Dollar</option>
                                <option value="SAR">SAR (﷼) - Saudi Riyal</option>
                            </select>
                            <input type="hidden" name="timezone" value="Asia/Kolkata">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showNewGymModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white font-bold text-xs shadow-md shadow-red-500/20 cursor-pointer">Create Gym & Account</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Plan Modal -->
        <div x-show="showNewPlanModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl max-w-xl w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 shadow-2xl" @click.away="showNewPlanModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Create SaaS Subscription Plan</h3>
                    <button @click="showNewPlanModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">✕</button>
                </div>

                <form action="{{ route('admin.plans.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Plan Name *</label>
                            <input type="text" name="name" required placeholder="Scale Plan" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Slug *</label>
                            <input type="text" name="slug" required placeholder="scale" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Yearly / Annual Price (₹ / $) *</label>
                            <input type="number" step="0.01" name="price_yearly" required placeholder="12000.00" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Trial Days</label>
                            <input type="number" name="trial_days" value="14" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Member Limit (-1 = unltd)</label>
                            <input type="number" name="member_limit" value="1000" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Branch Limit</label>
                            <input type="number" name="branch_limit" value="5" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Staff Limit</label>
                            <input type="number" name="staff_limit" value="15" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showNewPlanModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white font-bold text-xs shadow-md shadow-red-500/20 cursor-pointer">Save SaaS Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
