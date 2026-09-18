<x-admin-layout header="Subscriptions & Payment Reconciliation">
    <div class="space-y-8" x-data="{ showManualPayModal: false, editSub: null }">
        <!-- Action Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Tenant SaaS Subscriptions</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Manage plan terms, subscription state overrides, and offline bank transfer payments</p>
            </div>
            <button @click="showManualPayModal = true" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-red-600/20 transition-all cursor-pointer">
                + Record Manual Payment
            </button>
        </div>

        <!-- Subscriptions Table & Mobile Cards -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs dark:shadow-xl transition-colors">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Tenant Gym Subscriptions</h3>
                </div>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                    {{ $subscriptions->total() }} Tenants
                </span>
            </div>

            <!-- Mobile View: Subscriptions Cards List (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800/60 p-3 space-y-3">
                @forelse($subscriptions as $sub)
                    @php
                        $owner = $sub->tenant?->users?->firstWhere('role', 'gym_owner') ?? $sub->tenant?->users?->first();
                        $latestInvoice = $sub->invoices->first() ?? ($sub->tenant_id ? \App\Models\PlatformInvoice::where('tenant_id', $sub->tenant_id)->latest('invoice_date')->first() : null);
                        $daysLeft = $sub->ends_at ? (int) now()->diffInDays($sub->ends_at, false) : null;
                        $badge = match($sub->status) {
                            'ACTIVE' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                            'TRIAL' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                            default => 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20',
                        };
                    @endphp
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-950/60 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white text-sm">{{ $sub->tenant->name }}</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $owner?->name ?? 'Owner' }} &bull; {{ $owner?->email ?? $sub->tenant->email }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $badge }} shrink-0">
                                {{ $sub->status }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200 dark:border-slate-800/60">
                            <div>
                                <span class="font-black text-amber-600 dark:text-amber-400">{{ $sub->plan->name }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $sub->tenant->currency_symbol ?? '₹' }}{{ number_format($sub->billing_cycle === 'yearly' ? $sub->plan->price_yearly : $sub->plan->price_monthly, 2) }}/{{ $sub->billing_cycle }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] text-slate-700 dark:text-slate-300 font-semibold block">
                                    {{ $sub->ends_at ? $sub->ends_at->format('M d, Y') : ($sub->trial_ends_at ? $sub->trial_ends_at->format('M d, Y') . ' (Trial)' : '—') }}
                                </span>
                                @if($sub->ends_at && $daysLeft !== null)
                                    <span class="text-[10px] {{ $daysLeft < 15 ? 'text-rose-500 font-bold' : 'text-slate-400' }} block">
                                        {{ max(0, $daysLeft) }} days left
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="pt-1">
                            <button @click="editSub = {{ json_encode([
                                'id' => $sub->id,
                                'tenant_name' => $sub->tenant->name,
                                'plan_id' => $sub->plan_id,
                                'billing_cycle' => $sub->billing_cycle,
                                'status' => $sub->status,
                                'ends_at' => $sub->ends_at?->toDateString(),
                                'trial_ends_at' => $sub->trial_ends_at?->toDateString(),
                            ]) }}" class="w-full py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 text-xs font-bold transition-all text-center cursor-pointer">
                                ⚙️ Modify Plan / Status
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">
                        No tenant subscriptions found.
                    </div>
                @endforelse
            </div>

            <!-- Desktop View: Subscriptions Table (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[700px]">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 font-semibold">Tenant Gym</th>
                            <th class="py-3.5 px-4 font-semibold">Subscribed Plan & Inclusions</th>
                            <th class="py-3.5 px-4 font-semibold">Billing & Payment</th>
                            <th class="py-3.5 px-4 font-semibold">Status</th>
                            <th class="py-3.5 px-4 font-semibold">Validity / Renews</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @forelse($subscriptions as $sub)
                            @php
                                $owner = $sub->tenant?->users?->firstWhere('role', 'gym_owner') ?? $sub->tenant?->users?->first();
                                $latestInvoice = $sub->invoices->first() ?? ($sub->tenant_id ? \App\Models\PlatformInvoice::where('tenant_id', $sub->tenant_id)->latest('invoice_date')->first() : null);
                                $daysLeft = $sub->ends_at ? (int) now()->diffInDays($sub->ends_at, false) : null;
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 dark:text-white block text-sm">{{ $sub->tenant->name }}</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">
                                        {{ $owner?->name ?? 'Owner' }} &bull; {{ $owner?->email ?? $sub->tenant->email }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $sub->tenant->phone ?? '' }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span class="font-black text-amber-600 dark:text-amber-400 text-xs">{{ $sub->plan->name }}</span>
                                        @if($sub->plan->is_popular)
                                            <span class="px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[9px] font-bold">Popular</span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-[10px] text-slate-500 dark:text-slate-400">
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 text-slate-700 dark:text-slate-300">
                                            👥 Max Members: <strong>{{ $sub->plan->member_limit > 0 ? number_format($sub->plan->member_limit) : 'Unlimited' }}</strong>
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 text-slate-700 dark:text-slate-300">
                                            🏢 Branches: <strong>{{ $sub->plan->branch_limit > 0 ? $sub->plan->branch_limit : 'Unlimited' }}</strong>
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 text-slate-700 dark:text-slate-300">
                                            👔 Staff: <strong>{{ $sub->plan->staff_limit > 0 ? $sub->plan->staff_limit : 'Unlimited' }}</strong>
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white text-xs">
                                        {{ $sub->tenant->currency_symbol ?? '₹' }}{{ number_format($sub->billing_cycle === 'yearly' ? $sub->plan->price_yearly : $sub->plan->price_monthly, 2) }}
                                        <span class="text-[10px] uppercase text-slate-400 font-normal">/ {{ $sub->billing_cycle }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                        <span>Gateway: <strong class="text-slate-700 dark:text-slate-300">{{ ucfirst($sub->gateway_name ?? 'Razorpay') }}</strong></span>
                                        @if($latestInvoice)
                                            <span>&bull;</span>
                                            <span class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">{{ $latestInvoice->invoice_number }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $badge = match($sub->status) {
                                            'ACTIVE' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                             'TRIAL' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                            default => 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold border {{ $badge }} inline-block">
                                        {{ $sub->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="text-slate-900 dark:text-white font-semibold block text-xs">
                                        {{ $sub->ends_at ? $sub->ends_at->format('M d, Y') : ($sub->trial_ends_at ? $sub->trial_ends_at->format('M d, Y') . ' (Trial)' : '—') }}
                                    </span>
                                    @if($sub->ends_at)
                                        <span class="text-[10px] {{ $daysLeft !== null && $daysLeft < 15 ? 'text-rose-500 dark:text-rose-400 font-bold' : 'text-slate-400' }} block mt-0.5">
                                            {{ $daysLeft !== null ? max(0, $daysLeft) . ' days remaining' : '' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <button @click="editSub = {{ json_encode([
                                        'id' => $sub->id,
                                        'tenant_name' => $sub->tenant->name,
                                        'plan_id' => $sub->plan_id,
                                        'billing_cycle' => $sub->billing_cycle,
                                        'status' => $sub->status,
                                        'ends_at' => $sub->ends_at?->toDateString(),
                                        'trial_ends_at' => $sub->trial_ends_at?->toDateString(),
                                    ]) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-300 dark:border-slate-700/60 cursor-pointer">
                                        Modify Plan
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                    No tenant subscriptions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subscriptions->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                    {{ $subscriptions->links() }}
                </div>
            @endif
        </div>

        <!-- Platform Invoices Table & Mobile Cards -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs dark:shadow-xl transition-colors">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Generated SaaS Platform Invoices (Gym Owner Subscriptions)</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Official platform billing receipts issued to gym owners for software licenses and subscriptions</p>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 w-fit">
                    {{ $invoices->total() }} Invoices Logged
                </span>
            </div>

            <!-- Mobile View: Platform Invoices Cards (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800/60 p-3 space-y-3">
                @forelse($invoices as $inv)
                    @php
                        $owner = $inv->tenant?->users?->firstWhere('role', 'gym_owner') ?? $inv->tenant?->users?->first();
                    @endphp
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-950/60 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-red-600 dark:text-red-400 text-xs">{{ $inv->invoice_number }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                {{ $inv->status }}
                            </span>
                        </div>
                        <div>
                            <span class="font-bold text-slate-900 dark:text-white text-xs block">{{ $owner?->name ?? 'Gym Owner' }}</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block">🏢 {{ $inv->tenant?->name ?? 'Gym Facility' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200 dark:border-slate-800/60">
                            <div>
                                <span class="font-semibold text-amber-600 dark:text-amber-400 text-xs">{{ $inv->subscription?->plan?->name ?? 'Plan' }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $inv->invoice_date->format('M d, Y') }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-slate-900 dark:text-white text-sm">
                                    {{ $inv->currency === 'INR' ? '₹' : ($inv->currency === 'EUR' ? '€' : ($inv->currency === 'GBP' ? '£' : '$')) }}{{ number_format($inv->total, 2) }}
                                </span>
                                <span class="text-[10px] text-slate-400 block">{{ ucfirst($inv->payment?->gateway ?? ($inv->subscription?->gateway_name ?? 'Razorpay')) }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">
                        No SaaS platform invoices logged.
                    </div>
                @endforelse
            </div>

            <!-- Desktop View: Platform Invoices Table (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[750px]">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 font-semibold">Invoice #</th>
                            <th class="py-3.5 px-4 font-semibold">Subscriber (Gym Owner & Gym)</th>
                            <th class="py-3.5 px-4 font-semibold">Subscribed SaaS Plan</th>
                            <th class="py-3.5 px-4 font-semibold">Payment Method & Ref</th>
                            <th class="py-3.5 px-4 font-semibold">Invoice Date</th>
                            <th class="py-3.5 px-4 font-semibold">Amount Paid</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @forelse($invoices as $inv)
                            @php
                                $owner = $inv->tenant?->users?->firstWhere('role', 'gym_owner') ?? $inv->tenant?->users?->first();
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-bold text-red-600 dark:text-red-400 block">{{ $inv->invoice_number }}</span>
                                    <span class="text-[10px] text-slate-400 font-medium">Billed System License</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white text-xs">
                                        {{ $owner?->name ?? 'Gym Owner' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-0.5">
                                        <span>{{ $owner?->email ?? $inv->tenant?->email }}</span>
                                        @if($owner?->phone || $inv->tenant?->phone)
                                            <span>&bull;</span>
                                            <span class="font-mono">{{ $owner?->phone ?? $inv->tenant?->phone }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 text-[10px] font-medium">
                                            🏢 {{ $inv->tenant?->name ?? 'Gym Facility' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-bold text-[10px] inline-block">
                                        {{ $inv->subscription?->plan?->name ?? 'Growth Tier' }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5 uppercase font-medium">
                                        Billing: {{ $inv->subscription?->billing_cycle ?? 'yearly' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-slate-900 dark:text-white font-semibold text-[11px]">
                                        {{ ucfirst($inv->payment?->gateway ?? ($inv->subscription?->gateway_name ?? 'Razorpay / UPI')) }}
                                    </div>
                                    <div class="text-[10px] font-mono text-slate-400 truncate max-w-[140px] mt-0.5" title="{{ $inv->payment?->gateway_payment_id ?? ($inv->subscription?->gateway_subscription_id ?? 'TXN-DIRECT') }}">
                                        {{ $inv->payment?->gateway_payment_id ?? ($inv->subscription?->gateway_subscription_id ?? 'TXN-DIRECT') }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                    <span class="text-slate-900 dark:text-white font-medium block">{{ $inv->invoice_date->format('M d, Y') }}</span>
                                    @if($inv->paid_at)
                                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-mono">Paid {{ $inv->paid_at->format('M d') }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white text-xs">
                                        {{ $inv->currency === 'INR' ? '₹' : ($inv->currency === 'EUR' ? '€' : ($inv->currency === 'GBP' ? '£' : '$')) }}{{ number_format($inv->total, 2) }}
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-normal font-mono">({{ $inv->currency }})</span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-block">
                                        {{ $inv->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                    No SaaS platform invoices logged.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>

        <!-- Edit Subscription Modal -->
        <div x-show="editSub !== null" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl max-w-md w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 shadow-2xl transition-colors" @click.away="editSub = null">
                <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Modify Subscription</h3>
                    <button @click="editSub = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer p-1">✕</button>
                </div>

                <template x-if="editSub !== null">
                    <form :action="'/admin/subscriptions/' + editSub.id" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <span class="text-xs text-slate-500 dark:text-slate-400 block mb-1">Gym: <strong class="text-slate-900 dark:text-white" x-text="editSub.tenant_name"></strong></span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                            <select name="status" x-model="editSub.status" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="TRIAL">TRIAL</option>
                                <option value="PAST_DUE">PAST DUE</option>
                                <option value="GRACE_PERIOD">GRACE PERIOD</option>
                                <option value="SUSPENDED">SUSPENDED</option>
                                <option value="CANCELLED">CANCELLED</option>
                                <option value="EXPIRED">EXPIRED</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Plan</label>
                            <select name="plan_id" x-model="editSub.plan_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Billing Cycle</label>
                            <select name="billing_cycle" x-model="editSub.billing_cycle" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Subscription End / Renewal Date</label>
                            <input type="date" name="ends_at" x-model="editSub.ends_at" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="editSub = null" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer">Cancel</button>
                            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-md shadow-red-600/20 cursor-pointer">Update Status</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <!-- Record Manual Payment Modal -->
        <div x-show="showManualPayModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl max-w-md w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 shadow-2xl transition-colors" @click.away="showManualPayModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Record Offline SaaS Payment</h3>
                    <button @click="showManualPayModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer p-1">✕</button>
                </div>

                <form action="{{ route('admin.subscriptions.manual-payment') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Gym *</label>
                        <select name="tenant_id" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                            @foreach($gyms as $g)
                                <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->slug }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Plan *</label>
                        <select name="plan_id" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (₹{{ number_format($p->price_yearly, 2) }}/yr)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Amount (₹ / $) *</label>
                            <input type="number" step="0.01" name="amount" required placeholder="12000.00" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Billing Cycle</label>
                            <select name="billing_cycle" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="yearly" selected>Yearly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                        <input type="text" name="payment_method" value="Bank Wire / Manual" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Notes</label>
                        <input type="text" name="notes" placeholder="Invoice # or receipt reference" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showManualPayModal = false" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-md shadow-red-600/20 cursor-pointer">Activate Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
