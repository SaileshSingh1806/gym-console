<x-admin-layout header="Platform overview">
    <style>
        .gdh-admin { --ad-surface:#fff; --ad-muted:#647180; --ad-text:#18181b; --ad-line:#e4e4e7; --ad-hover:#f7f8fa; font-family:inherit; font-size:14px; line-height:1.5; }
        .dark .gdh-admin { --ad-surface:#151618; --ad-muted:#a1a8b0; --ad-text:#f1f3f5; --ad-line:#292c30; --ad-hover:#1c1e21; }
        .gdh-admin .ad-panel,.gdh-admin .ad-metrics,.gdh-admin .ad-vitals { background:var(--ad-surface); border:1px solid var(--ad-line); border-radius:8px; color:var(--ad-text); }
        .gdh-admin .ad-metrics { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; background:transparent; border:0; }
        .gdh-admin .ad-metrics>a { min-width:0; padding:13px 14px; border:1px solid var(--ad-line); border-top:1px solid #87919c; border-radius:10px; background:var(--ad-surface); color:var(--ad-text); }
        .gdh-admin .ad-metrics>a:nth-child(1) { border-top-color:#34b88a; } .gdh-admin .ad-metrics>a:nth-child(2) { border-top-color:#87919c; } .gdh-admin .ad-metrics>a:nth-child(3) { border-top-color:#e05b5b; } .gdh-admin .ad-metrics>a:nth-child(4) { border-top-color:#e6ae30; }
        .gdh-admin .ad-metrics>a:nth-child(1) .ad-value { color:#16865d; }
        .dark .gdh-admin .ad-metrics>a:nth-child(1) .ad-value { color:#34b88a; }
        .gdh-admin .ad-metrics>a:nth-child(3) .ad-value.has-urgent { color:#b42318; }
        .dark .gdh-admin .ad-metrics>a:nth-child(3) .ad-value.has-urgent { color:#f87171; }
        .gdh-admin .ad-metrics>a:nth-child(4) .ad-value { color:var(--ad-text); }
        .gdh-admin .ad-panel a.ad-link { color:var(--ad-text); }
        .gdh-admin .ad-panel .ad-link:hover { color:var(--theme-accent,#f5a623); }
        .gdh-admin .ad-panel .ad-invoice { color:var(--ad-text); font-weight:500; }
        .gdh-admin .ad-panel .ad-plan { color:var(--ad-muted); border:1px solid var(--ad-line); border-radius:4px; padding:2px 6px; font-size:11px; background:transparent; }
        .gdh-admin .ad-panel .ad-inner { background:var(--ad-surface); border-color:var(--ad-line); }
        .gdh-admin .ad-panel .ad-inner:hover { background:var(--ad-hover); }
        .gdh-admin .ad-panel .ad-section-footer { background:var(--ad-surface); border-color:var(--ad-line); }
        .gdh-admin .ad-metrics>a:hover { background:var(--ad-hover); }
        .gdh-admin .ad-label { color:var(--ad-muted); font-size:12px; font-weight:500; }
        .gdh-admin .ad-value { font-size:24px; font-weight:600; line-height:1.3; letter-spacing:-.035em; font-variant-numeric:tabular-nums; overflow-wrap:anywhere; margin:6px 0; }
        .gdh-admin .ad-meta { font-size:12px; color:var(--ad-muted); }
        .gdh-admin .ad-vitals { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); }
        .gdh-admin .ad-vitals>div { padding:10px 16px; border-right:1px solid var(--ad-line); }
        .gdh-admin .ad-vitals>div:last-child { border:0; }
        .gdh-admin .ad-panel h3 { font-size:14px; font-weight:600; letter-spacing:-.015em; }
        .gdh-admin .ad-panel table { font-size:13px; font-variant-numeric:tabular-nums; }
        .gdh-admin .ad-panel th { font-size:12px; font-weight:600; white-space:nowrap; }
        .gdh-admin .ad-panel thead { background:var(--ad-hover); color:var(--ad-muted); }
        .gdh-admin .ad-link { color:var(--ad-text); font-weight:500; }
        .gdh-admin .ad-link:hover { text-decoration:underline; color:var(--theme-accent,#f59e0b); }
        .gdh-admin .ad-primary,.gdh-admin .ad-secondary { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:36px; border-radius:6px; padding:8px 13px; font-size:13px; font-weight:500; cursor:pointer; }
        .gdh-admin .ad-primary { background:var(--theme-accent,#f59e0b); color:#10151c; }
        @supports(color:contrast-color(red)) { .gdh-admin .ad-primary { color:contrast-color(var(--theme-accent,#f59e0b)); } }
        .gdh-admin .ad-primary:hover { filter:brightness(.92); }
        .gdh-admin .ad-secondary { border:1px solid var(--ad-line); color:var(--ad-text); background:var(--ad-surface); }
        .gdh-admin .ad-secondary:hover { background:var(--ad-hover); }
        .gdh-admin .ad-status { display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:500; color:var(--ad-muted); }
        .gdh-admin .ad-status:before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; }
        .gdh-admin .ad-status.is-good { color:#24734d; } .dark .gdh-admin .ad-status.is-good { color:#8bcba8; }
        .gdh-admin .ad-status.is-bad { color:#b42318; } .dark .gdh-admin .ad-status.is-bad { color:#fda4a4; }
        .gdh-admin .ad-dialog input:not([type=hidden]),.gdh-admin .ad-dialog select { min-height:40px; font-size:14px; }
        .gdh-admin :focus-visible { outline:2px solid var(--theme-accent,#f59e0b); outline-offset:3px; }
        @media(max-width:1200px) { .gdh-admin .ad-metrics,.gdh-admin .ad-vitals { grid-template-columns:repeat(2,minmax(0,1fr)); } .gdh-admin .ad-vitals>div:nth-child(2) { border-right:0; } .gdh-admin .ad-vitals>div:nth-child(-n+2) { border-bottom:1px solid var(--ad-line); } }
        @media(max-width:400px) { .gdh-admin .ad-metrics>a,.gdh-admin .ad-vitals>div { padding:11px; } .gdh-admin .ad-value { font-size:20px; } }
    </style>
    <div class="gdh-admin space-y-4" x-data="{ showNewGymModal: false, showNewPlanModal: false }">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold tracking-tight">Overview</h2>
                <p class="ad-meta mt-1">Monitor gym accounts, subscriptions and support activity.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="showNewPlanModal = true" class="ad-secondary">Create plan</button>
                <button type="button" @click="showNewGymModal = true" class="ad-primary"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>Register gym</button>
            </div>
        </div>
        <section class="ad-metrics" aria-label="Platform metrics">
            <a href="{{ route('admin.gyms') }}">
                <div class="ad-label">Gym businesses</div>
                <div class="ad-value">{{ $metrics['total_gyms'] }}</div>
                <div class="ad-meta">{{ $metrics['active_gyms'] }} active · {{ $metrics['trial_gyms'] }} trial</div>
            </a>
            <a href="{{ route('admin.users') }}">
                <div class="ad-label">Platform users</div>
                <div class="ad-value">{{ $totalUsers }}</div>
                <div class="ad-meta">{{ $gymOwnersCount }} owners · {{ $totalStaffCount }} staff</div>
            </a>
            <a href="{{ route('admin.tickets.index') }}">
                <div class="ad-label">Open support tickets</div>
                <div class="ad-value {{ $urgentTicketsCount > 0 ? 'has-urgent' : '' }}">{{ $openTicketsCount }}</div>
                <div class="ad-meta">@if($urgentTicketsCount > 0)<span class="ad-status is-bad">{{ $urgentTicketsCount }} urgent</span> · @endif {{ $totalTicketsCount }} total</div>
            </a>
            <a href="{{ route('admin.subscriptions') }}">
                <div class="ad-label">Annual recurring revenue</div>
                <div class="ad-value">₹{{ number_format($metrics['arr'] ?? $metrics['total_revenue'], 0) }}</div>
                <div class="ad-meta">Invoiced: ₹{{ number_format($metrics['total_revenue'], 0) }}</div>
            </a>
        </section>
        <section class="ad-vitals" aria-label="Platform activity">
            <div><div class="ad-label">Gym members</div><div class="font-semibold tabular-nums mt-1">{{ number_format($totalMembers) }}</div></div>
            <div><div class="ad-label">Branches</div><div class="font-semibold tabular-nums mt-1">{{ number_format($totalBranchesCount) }}</div></div>
            <div><div class="ad-label">Active subscriptions</div><div class="font-semibold tabular-nums mt-1">{{ $activeSubscriptionsCount }}</div></div>
            <div><div class="ad-label">System health</div><div class="ad-meta mt-1">Not monitored</div></div>
        </section>

        <!-- PLATFORM OPERATIONS & FINANCIAL HUB -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            
            <!-- Left: Real-Time Financial Trail & Invoices (2 cols) -->
            <div class="lg:col-span-2 space-y-4">
                
                <!-- Recent Invoices Table -->
                <div class="ad-panel overflow-hidden ">
                    <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800  flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-0">
                        <div class="flex items-center gap-2.5">
                            
                            <div>
                                <h3 class="text-xs font-medium text-slate-900 dark:text-white ">Recent invoices</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Subscription payments and renewals</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.subscriptions') }}" class="text-xs font-medium ad-link hover:underline transition-colors flex items-center gap-1 self-start sm:self-auto">
                            <span>View subscriptions</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs min-w-[650px]">
                            <thead class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400  text-xs">
                                <tr>
                                    <th class="py-2.5 px-3 font-medium">Invoice #</th>
                                    <th class="py-2.5 px-3 font-medium">Subscriber</th>
                                    <th class="py-2.5 px-3 font-medium">Plan</th>
                                    <th class="py-2.5 px-3 font-medium">Amount</th>
                                    <th class="py-2.5 px-3 font-medium">Date</th>
                                    <th class="py-2.5 px-3 font-medium text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                                @forelse($recentInvoices as $inv)
                                    @php
                                        $owner = $inv->tenant?->users?->firstWhere('role', 'gym_owner') ?? $inv->tenant?->users?->first();
                                    @endphp
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                        <td class="py-2.5 px-3 ad-invoice">
                                             {{ $inv->invoice_number }}
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="font-medium text-slate-900 dark:text-white block text-xs">{{ $owner?->name ?? 'Gym Owner' }}</span>
                                            <span class="text-xs text-slate-500 dark:text-slate-400 block">{{ $owner?->email ?? $inv->tenant?->email }}</span>
                                            <span class="inline-flex items-center gap-1 text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                                                {{ $inv->tenant?->name ?? 'Gym Facility' }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="ad-plan">
                                                {{ $inv->subscription?->plan?->name ?? 'Subscription Plan' }}
                                            </span>
                                            <span class="text-xs text-slate-400 block mt-0.5 font-medium">
                                                {{ ucfirst($inv->subscription?->billing_cycle ?? 'yearly') }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-white">
                                            {{ $inv->currency === 'INR' ? '₹' : ($inv->currency === 'EUR' ? '€' : ($inv->currency === 'GBP' ? '£' : '$')) }}{{ number_format($inv->total, 2) }}
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400">
                                            {{ $inv->invoice_date?->format('d M Y') ?? '—' }}
                                        </td>
                                        <td class="py-2.5 px-3 text-right">
                                            <span class="ad-status {{ in_array(strtoupper((string) $inv->status), ['PAID', 'COMPLETED']) ? 'is-good' : (in_array(strtoupper((string) $inv->status), ['FAILED', 'OVERDUE']) ? 'is-bad' : '') }}">
                                                {{ $inv->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 text-center text-slate-400 dark:text-slate-500">
                                            
                                            <p class="font-semibold text-xs">No platform invoices recorded yet.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 ad-section-footer border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span>Showing latest {{ count($recentInvoices) }} transaction records</span>
                        <a href="{{ route('admin.subscriptions') }}" class="text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-semibold">Reconcile All &rarr;</a>
                    </div>
                </div>

                <!-- Active Gym Businesses Directory -->
                <div class="ad-panel overflow-hidden ">
                    <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800  flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-0">
                        <div class="flex items-center gap-2.5">
                            
                            <div>
                                <h3 class="text-xs font-medium text-slate-900 dark:text-white ">Recent gyms</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Recently registered gym accounts</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.gyms') }}" class="text-xs font-medium ad-link hover:underline transition-colors flex items-center gap-1 self-start sm:self-auto">
                            <span>View all gyms</span>
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
                                        <span class="font-semibold text-slate-900 dark:text-white text-sm">{{ $gym->name }}</span>
                                        <span class="ad-status {{ $gym->status === 'ACTIVE' ? 'is-good' : ($gym->status === 'SUSPENDED' ? 'is-bad' : '') }}">
                                            {{ $gym->status }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex flex-wrap items-center gap-2">
                                        <span>Owner: <strong class="text-slate-800 dark:text-slate-200">{{ $gymOwner?->name ?? 'Admin' }}</strong> ({{ $gymOwner?->email ?? $gym->email }})</span>
                                        <span>&bull;</span>
                                        <span>Branches: <strong class="text-slate-800 dark:text-slate-200">{{ $gym->branches?->count() ?? 1 }}</strong></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 self-end sm:self-auto">
                                    <span class="ad-plan">
                                        {{ $gym->activeSubscription?->plan?->name ?? 'Free / Trial' }}
                                    </span>
                                    <a href="{{ route('admin.gyms.impersonate', $gym->id) }}" class="px-3 py-1.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-transparent font-medium text-xs transition-all">
                                        Open console
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
            <div class="space-y-4">
                
                <!-- Support Tickets Action Center -->
                <div class="ad-panel p-5  space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            
                            <h3 class="text-xs font-medium text-slate-900 dark:text-white ">Support tickets</h3>
                        </div>
                        <a href="{{ route('admin.tickets.index') }}" class="text-xs ad-link hover:underline font-medium">
                            View all ({{ $totalTicketsCount }}) &rarr;
                        </a>
                    </div>

                    <div class="space-y-3">
                        @if($openSupportTickets->isNotEmpty())
                            <div>
                                <span class="text-xs font-medium text-slate-400 dark:text-slate-500  block mb-2">Active Tickets</span>
                                @foreach($openSupportTickets as $tck)
                                    <a href="{{ route('admin.tickets.show', $tck->id) }}" class="p-3 rounded-md ad-inner border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 block transition-all mb-2">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="font-medium text-slate-900 dark:text-white text-xs truncate">{{ $tck->subject }}</span>
                                            <span class="px-1.5 py-0.5 rounded text-xs font-medium uppercase {{ $tck->priority === 'urgent' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' }}">
                                                {{ $tck->priority }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                            <span>{{ $tck->tenant?->name ?? 'Gym Tenant' }}</span>
                                            <span class="font-mono text-slate-400 dark:text-slate-500">#{{ $tck->ticket_number }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="py-6 text-center text-slate-400 space-y-1">
                                
                                <p class="text-xs font-medium text-slate-900 dark:text-white">No open tickets</p>
                                <p class="text-xs text-slate-500">No tickets awaiting a response.</p>
                            </div>
                        @endif

                        @if($expiringSubscriptions->isNotEmpty())
                            <div class="pt-3 border-t border-slate-200 dark:border-slate-800/80">
                                <span class="text-xs font-medium text-slate-400 dark:text-slate-500  block mb-2">Upcoming Renewals</span>
                                @foreach($expiringSubscriptions as $expSub)
                                    @php
                                        $dLeft = (int) now()->diffInDays($expSub->ends_at, false);
                                    @endphp
                                    <div class="p-3 rounded-md ad-inner border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs mb-2">
                                        <div>
                                            <span class="font-medium text-slate-900 dark:text-white block">{{ $expSub->tenant->name }}</span>
                                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $expSub->plan?->name }} • Annual</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $dLeft < 7 ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' }}">
                                            {{ $dLeft }}d left
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Platform Diagnostics & Core Engine Status -->
                <div class="ad-panel p-5  space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                        <h3 class="text-xs font-medium text-slate-900 dark:text-white  flex items-center gap-2">
                            
                            <span>Service status</span>
                        </h3>
                        <a href="{{ route('admin.settings') }}" class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-semibold">Settings &rarr;</a>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="p-3 rounded-md ad-inner border border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
                            <span class="text-slate-700 dark:text-slate-300 flex items-center gap-2 font-medium">
                                
                                <span>Gemini AI Engine</span>
                            </span>
                            <span class="ad-status">
                                Not monitored
                            </span>
                        </div>

                        <div class="p-3 rounded-md ad-inner border border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
                            <span class="text-slate-700 dark:text-slate-300 flex items-center gap-2 font-medium">
                                
                                <span>SMTP Mail Gateway</span>
                            </span>
                            <span class="ad-status">
                                Not monitored
                            </span>
                        </div>

                        <div class="p-3 rounded-md ad-inner border border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
                            <span class="text-slate-700 dark:text-slate-300 flex items-center gap-2 font-medium">
                                
                                <span>Payment Reconciliation</span>
                            </span>
                            <span class="ad-status">
                                Not monitored
                            </span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Add Gym Modal -->
        <div x-show="showNewGymModal" role="dialog" aria-modal="true" aria-label="Register gym" @keydown.escape.window="showNewGymModal = false" class="fixed inset-0 z-50 overflow-y-auto bg-black/70  flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="ad-dialog bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xl w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 shadow-2xl" @click.away="showNewGymModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-sm sm:text-base font-medium text-slate-900 dark:text-white">Register New Gym Tenant</h3>
                    <button @click="showNewGymModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">✕</button>
                </div>

                <form action="{{ route('admin.gyms.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Gym Name *</label>
                            <input type="text" name="gym_name" required placeholder="Titan Fitness Club" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Primary Branch Name</label>
                            <input type="text" name="branch_name" placeholder="Downtown Flagship" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Owner Name *</label>
                            <input type="text" name="owner_name" required placeholder="John Owner" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Owner Email *</label>
                            <input type="email" name="email" required placeholder="owner@titan.com" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                            <input type="text" name="phone" placeholder="+1 555-0100" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Owner Password *</label>
                            <input type="password" name="password" required value="password" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Subscription Plan *</label>
                            <select name="plan_id" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Billing Cycle *</label>
                            <select name="billing_cycle" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                <option value="yearly" selected>📅 Yearly (Annual)</option>
                                <option value="monthly">🗓️ Monthly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status / Mode</label>
                            <select name="status" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                <option value="ACTIVE" selected>ACTIVE (Paid)</option>
                                <option value="TRIAL">TRIAL (Free)</option>
                                <option value="SUSPENDED">SUSPENDED</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Currency</label>
                            <select name="currency" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
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

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showNewGymModal = false" class="w-full sm:w-auto px-4 py-2 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer">Cancel</button>
                        <button type="submit" class="ad-primary">Create Gym & Account</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Plan Modal -->
        <div x-show="showNewPlanModal" role="dialog" aria-modal="true" aria-label="Create subscription plan" @keydown.escape.window="showNewPlanModal = false" class="fixed inset-0 z-50 overflow-y-auto bg-black/70  flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="ad-dialog bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xl w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 shadow-2xl" @click.away="showNewPlanModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-sm sm:text-base font-medium text-slate-900 dark:text-white">Create Subscription Plan</h3>
                    <button @click="showNewPlanModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer p-1">✕</button>
                </div>

                <form action="{{ route('admin.plans.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Plan Name *</label>
                            <input type="text" name="name" required placeholder="Scale Plan" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Slug *</label>
                            <input type="text" name="slug" required placeholder="scale" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Yearly / Annual Price (₹ / $) *</label>
                            <input type="number" step="0.01" name="price_yearly" required placeholder="12000.00" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Trial Days</label>
                            <input type="number" name="trial_days" value="14" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Member Limit (-1 = unltd)</label>
                            <input type="number" name="member_limit" value="1000" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Branch Limit</label>
                            <input type="number" name="branch_limit" value="5" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Staff Limit</label>
                            <input type="number" name="staff_limit" value="15" class="w-full px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showNewPlanModal = false" class="w-full sm:w-auto px-4 py-2 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer">Cancel</button>
                        <button type="submit" class="ad-primary">Save Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
