<x-admin-layout header="Gym Businesses &amp; Tenant Control">
    <div class="space-y-6" x-data="{ showNewModal: false, editGym: null, deleteGymModal: null, getTrialDate(days) { const d = new Date(); d.setDate(d.getDate() + days); return d.toISOString().split('T')[0]; } }">
        <!-- Quick Metrics Overview Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between transition-colors">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">Total Platform Members</span>
                    <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ $gymStats['totalPlatformMembersCount'] ?? 0 }}</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block mt-1">Across all onboarded gyms</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-bold">
                    👥
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between transition-colors">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">Contracted SaaS Value</span>
                    <span class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">₹{{ number_format($gymStats['totalPlatformRevenue'] ?? 0, 2) }}</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block mt-1">Active recurring subscriptions</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold">
                    💰
                </div>
            </div>
        </div>

        <!-- Top Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form action="{{ route('admin.gyms') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-grow max-w-3xl">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search gym name, slug, email, phone..." class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-red-500 focus:outline-none flex-grow min-w-[200px] shadow-sm">
                <select name="status" class="px-3 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-800 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-sm cursor-pointer" onchange="this.form.submit()">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <form action="{{ route('admin.gyms') }}" method="GET" class="flex flex-wrap items-center gap-2 sm:gap-3 flex-grow max-w-3xl w-full sm:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search gym name, slug, email, phone..." class="px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-red-500 focus:outline-none flex-grow min-w-[150px] shadow-sm">
                <select name="status" class="px-3 py-2 sm:py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-800 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-sm cursor-pointer" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                    <option value="TRIAL" {{ request('status') === 'TRIAL' ? 'selected' : '' }}>Trial</option>
                    <option value="SUSPENDED" {{ request('status') === 'SUSPENDED' ? 'selected' : '' }}>Suspended</option>
                    <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <select name="plan_id" class="px-3 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-800 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-sm cursor-pointer" onchange="this.form.submit()">
                <select name="plan_id" class="px-3 py-2 sm:py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-800 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-sm cursor-pointer" onchange="this.form.submit()">
                    <option value="">All Plans</option>
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}" {{ request('plan_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-white dark:border-slate-700 text-xs font-semibold shadow-sm transition-colors cursor-pointer">
                <button type="submit" class="px-4 py-2 sm:py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-white dark:border-slate-700 text-xs font-semibold shadow-sm transition-colors cursor-pointer">
                    Filter
                </button>
            </form>

            <button @click="showNewModal = true" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-red-600/20 shrink-0 transition-colors cursor-pointer">
            <button @click="showNewModal = true" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-red-600/20 shrink-0 transition-colors cursor-pointer">
                + Register New Gym
            </button>
        </div>

        <!-- Gyms Table -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                <table class="w-full text-left text-xs min-w-[750px]">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 font-semibold">Gym Business &amp; Tenant</th>
                            <th class="py-3.5 px-4 font-semibold">Owner &amp; Contact</th>
                            <th class="py-3.5 px-4 font-semibold">SaaS Plan &amp; Inclusions</th>
                            <th class="py-3.5 px-4 font-semibold">Status</th>
                            <th class="py-3.5 px-4 font-semibold">Subscription Validity</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @forelse($gyms as $gym)
                            @php
                                $ownerUser = $gym->users->where('role', 'gym_owner')->first() ?? $gym->users->first();
                                $sub = $gym->activeSubscription;
                                $plan = $sub?->plan;
                                $daysRemaining = $sub?->ends_at ? (int) now()->diffInDays($sub->ends_at, false) : null;
                                $planPrice = (float) ($sub?->billing_cycle === 'yearly' ? ($plan?->price_yearly ?? 0) : ($plan?->price_monthly ?? 0));
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 flex items-center justify-center font-black text-amber-600 dark:text-amber-400 text-sm shrink-0 shadow-sm">
                                            {{ strtoupper(substr($gym->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="font-black text-slate-900 dark:text-white text-sm block leading-tight">{{ $gym->name }}</span>
                                            <div class="flex flex-wrap items-center gap-1.5 mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                                <span class="font-mono text-red-600 dark:text-red-400 bg-red-500/10 px-1.5 py-0.5 rounded border border-red-500/20">/{{ $gym->slug }}</span>
                                                <span>&bull;</span>
                                                <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $gym->branches->count() }} branch(es)</span>
                                                <span>&bull;</span>
                                                <span class="text-amber-600 dark:text-amber-400 font-bold">{{ $gym->currency }} ({{ $gym->currency_symbol ?? '₹' }})</span>
                                            </div>
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-0.5">Joined: {{ $gym->created_at->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="text-slate-900 dark:text-white font-bold text-xs block">{{ $ownerUser?->name ?? 'Gym Owner' }}</span>
                                    <span class="text-[11px] text-slate-600 dark:text-slate-300 block mt-0.5 font-mono">{{ $gym->email }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono block">{{ $gym->phone ?? ($ownerUser?->phone ?? 'No phone') }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span class="font-black text-amber-600 dark:text-amber-400 text-xs">{{ $plan?->name ?? 'Free Trial' }}</span>
                                        @if($planPrice > 0)
                                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">
                                                {{ $gym->currency_symbol ?? '₹' }}{{ number_format($planPrice, 2) }}<span class="text-[9px] uppercase font-normal text-slate-400">/{{ $sub?->billing_cycle ?? 'yr' }}</span>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-1.5 text-[10px] text-slate-500 dark:text-slate-400">
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 font-medium">
                                            👥 Members: <strong class="text-slate-900 dark:text-white">{{ $gym->members_count }}</strong> / {{ $plan && $plan->member_limit > 0 ? number_format($plan->member_limit) : '∞' }}
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 font-medium">
                                            🏢 Branches: <strong class="text-slate-900 dark:text-white">{{ $gym->branches->count() }}</strong> / {{ $plan && $plan->branch_limit > 0 ? $plan->branch_limit : '∞' }}
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 font-medium">
                                            👔 Staff: <strong class="text-slate-900 dark:text-white">{{ $gym->users_count }}</strong> / {{ $plan && $plan->staff_limit > 0 ? $plan->staff_limit : '∞' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $badge = match($gym->status) {
                                            'ACTIVE' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                            'TRIAL' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                            'SUSPENDED' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                                            default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-300 dark:border-slate-700',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold border {{ $badge }} inline-block">
                                        {{ $gym->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($sub && $sub->ends_at)
                                        <span class="text-slate-900 dark:text-white font-bold block text-xs">{{ $sub->ends_at->format('M d, Y') }}</span>
                                        <span class="text-[10px] {{ $daysRemaining !== null && $daysRemaining < 15 ? 'text-rose-500 dark:text-rose-400 font-bold' : 'text-slate-500 dark:text-slate-400' }} block mt-0.5">
                                            {{ $daysRemaining !== null ? max(0, $daysRemaining) . ' days remaining' : '' }}
                                        </span>
                                    @elseif($gym->trial_ends_at)
                                        <span class="text-amber-600 dark:text-amber-400 font-semibold block text-xs">{{ $gym->trial_ends_at->format('M d, Y') }}</span>
                                        <span class="text-[10px] text-amber-600 dark:text-amber-500 block mt-0.5 font-medium">Trial Period</span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 text-xs">No active cycle</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- 1-Click Impersonation -->
                                        <form action="{{ route('admin.gyms.impersonate', $gym->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" title="Login as Gym Owner" class="px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500 hover:text-slate-950 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-xs font-bold transition-all shadow-sm cursor-pointer">
                                                ⚡ Impersonate
                                            </button>
                                        </form>

                                        <!-- Edit Modal Trigger -->
                                        <button @click="editGym = {{ json_encode([
                                            'id' => $gym->id,
                                            'name' => $gym->name,
                                            'slug' => $gym->slug,
                                            'email' => $gym->email,
                                            'phone' => $gym->phone,
                                            'currency' => $gym->currency,
                                            'timezone' => $gym->timezone,
                                            'status' => $gym->status,
                                            'trial_ends_at' => $gym->trial_ends_at?->toDateString(),
                                            'plan_id' => $gym->activeSubscription?->plan_id,
                                            'billing_cycle' => $gym->activeSubscription?->billing_cycle ?? 'yearly',
                                        ]) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-300 dark:border-slate-700/60 transition-all cursor-pointer">
                                            Edit
                                        </button>

                                        <!-- Delete Gym Trigger -->
                                        <button type="button" @click="deleteGymModal = { id: {{ $gym->id }}, name: {{ Js::from($gym->name) }}, slug: {{ Js::from($gym->slug) }}, typedName: '' }" class="px-2.5 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-600 hover:text-white text-rose-600 dark:text-rose-400 text-xs font-bold border border-rose-500/20 transition-all cursor-pointer">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                    <div class="text-3xl mb-2">🏢</div>
                                    <p class="font-bold text-slate-700 dark:text-slate-400 text-sm">No gym businesses found</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-600 mt-1">Try adjusting your search criteria or register a new gym business.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($gyms->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                    {{ $gyms->links() }}
                </div>
            @endif
        </div>

        <!-- Edit Gym Modal -->
        <div x-show="editGym !== null" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 flex items-center justify-center p-4 backdrop-blur-sm" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-xl w-full p-6 shadow-2xl transition-colors" @click.away="editGym = null">
        <div x-show="editGym !== null" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl max-w-xl w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 shadow-2xl transition-colors" @click.away="editGym = null">
                <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Gym Tenant Details</h3>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Edit Gym Tenant Details</h3>
                    <button @click="editGym = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">✕</button>
                </div>

                <template x-if="editGym !== null">
                    <form :action="'/admin/gyms/' + editGym.id" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Gym Name *</label>
                                <input type="text" name="name" x-model="editGym.name" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Slug (Identifier) *</label>
                                <input type="text" name="slug" x-model="editGym.slug" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                                <input type="email" name="email" x-model="editGym.email" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone</label>
                                <input type="text" name="phone" x-model="editGym.phone" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status / Mode</label>
                                <select name="status" x-model="editGym.status" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                                <select name="status" x-model="editGym.status" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                    <option value="TRIAL">🟡 TRIAL (Free Trial)</option>
                                    <option value="ACTIVE">🟢 ACTIVE (Paid Subscription)</option>
                                    <option value="PAST_DUE">🟠 PAST DUE</option>
                                    <option value="SUSPENDED">🔴 SUSPENDED</option>
                                    <option value="CANCELLED">⚫ CANCELLED</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Assigned Plan</label>
                                <select name="plan_id" x-model="editGym.plan_id" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                                <select name="plan_id" x-model="editGym.plan_id" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                    @foreach($plans as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Billing Cycle</label>
                                <select name="billing_cycle" x-model="editGym.billing_cycle" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                                <select name="billing_cycle" x-model="editGym.billing_cycle" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                    <option value="yearly">📅 Yearly (Annual)</option>
                                    <option value="monthly">🗓️ Monthly</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Currency</label>
                                <select name="currency" x-model="editGym.currency" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                                <select name="currency" x-model="editGym.currency" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                    <option value="INR">INR (₹) - Indian Rupee</option>
                                    <option value="USD">USD ($) - US Dollar</option>
                                    <option value="EUR">EUR (€) - Euro</option>
                                    <option value="GBP">GBP (£) - British Pound</option>
                                    <option value="AED">AED (د.إ) - UAE Dirham</option>
                                    <option value="CAD">CAD ($) - Canadian Dollar</option>
                                    <option value="AUD">AUD ($) - Australian Dollar</option>
                                    <option value="SGD">SGD ($) - Singapore Dollar</option>
                                    <option value="SAR">SAR (﷼) - Saudi Riyal</option>
                                </select>
                                <input type="hidden" name="timezone" x-model="editGym.timezone">
                            </div>
                        </div>

                        <!-- Free Trial Duration Controls -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2 sm:space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Set Custom Trial Expiry (If in Trial mode)</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">Overrides plan default</span>
                            </div>
                            <input type="date" name="trial_ends_at" x-model="editGym.trial_ends_at" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="editGym = null" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 text-xs font-semibold cursor-pointer">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-md shadow-red-600/20 cursor-pointer">Save Changes</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <!-- Add New Gym Modal -->
        <div x-show="showNewModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 flex items-center justify-center p-4 backdrop-blur-sm" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-xl w-full p-6 shadow-2xl transition-colors" @click.away="showNewModal = false">
        <div x-show="showNewModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl max-w-xl w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 shadow-2xl transition-colors" @click.away="showNewModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Register New Gym Tenant</h3>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Register New Gym Tenant</h3>
                    <button @click="showNewModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">✕</button>
                </div>

                <form action="{{ route('admin.gyms.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Gym Name *</label>
                            <input type="text" name="gym_name" required placeholder="Olympus Gym" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Primary Branch Name</label>
                            <input type="text" name="branch_name" placeholder="Central Branch" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Owner Name *</label>
                            <input type="text" name="owner_name" required placeholder="Alex Turner" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Owner Email *</label>
                            <input type="email" name="email" required placeholder="alex@olympus.com" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone</label>
                            <input type="text" name="phone" placeholder="+1 555-0100" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Owner Password *</label>
                            <input type="password" name="password" required value="password" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">SaaS Plan *</label>
                            <select name="plan_id" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                            <select name="plan_id" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Billing Cycle *</label>
                            <select name="billing_cycle" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                            <select name="billing_cycle" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                <option value="yearly" selected>📅 Yearly (Annual)</option>
                                <option value="monthly">🗓️ Monthly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status / Mode</label>
                            <select name="status" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                            <select name="status" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                                <option value="TRIAL">🟡 TRIAL (Free Trial)</option>
                                <option value="ACTIVE" selected>🟢 ACTIVE (Paid Activation)</option>
                                <option value="SUSPENDED">🔴 SUSPENDED</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Currency</label>
                            <select name="currency" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
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

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Trial Ends At (Leave empty for default plan trial)</label>
                        <input type="date" name="trial_ends_at" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showNewModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 text-xs font-semibold cursor-pointer">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-md shadow-red-600/20 cursor-pointer">Create Gym Tenant</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- DELETE GYM CONFIRMATION MODAL -->
        <!-- ========================================== -->
        <div x-show="deleteGymModal !== null" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/85 flex items-center justify-center p-4 backdrop-blur-sm" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-rose-500/40 rounded-3xl max-w-md w-full p-6 shadow-2xl relative transition-colors" @click.away="deleteGymModal = null">
        <div x-show="deleteGymModal !== null" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-rose-500/40 rounded-2xl sm:rounded-3xl max-w-md w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 shadow-2xl relative transition-colors" @click.away="deleteGymModal = null">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-rose-500/10 dark:bg-red-500/20 text-rose-600 dark:text-red-400 flex items-center justify-center font-bold text-lg shrink-0 border border-rose-500/30">
                        ⚠️
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Permanently Delete Gym</h3>
                        <p class="text-[11px] text-rose-600 dark:text-red-400 font-semibold">Irreversible action &amp; complete data wipe</p>
                    </div>
                </div>

                <template x-if="deleteGymModal !== null">
                    <form :action="'/admin/gyms/' + deleteGymModal.id" method="POST" class="space-y-4">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="confirm_gym_name" :value="deleteGymModal.typedName">

                        <div class="p-3.5 rounded-2xl bg-rose-50 dark:bg-red-950/40 border border-rose-200 dark:border-red-500/20 text-xs text-slate-700 dark:text-slate-300 space-y-2">
                            <p class="leading-relaxed">
                                You are about to permanently delete <strong class="text-slate-900 dark:text-white" x-text="deleteGymModal.name"></strong> and all its associated data from the database.
                            </p>
                            <ul class="list-disc list-inside text-[11px] text-rose-700 dark:text-red-300/90 space-y-0.5 font-medium">
                                <li>All enrolled members &amp; payment receipts</li>
                                <li>Attendance logs, biometric records &amp; access logs</li>
                                <li>Staff accounts, roles &amp; trainer assignments</li>
                                <li>Invoices, expenses, inventory &amp; equipment logs</li>
                            </ul>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                To confirm deletion, type <span class="text-amber-600 dark:text-amber-400 font-mono select-all font-black" x-text="deleteGymModal.name"></span> below:
                            </label>
                            <input type="text" 
                                   x-model="deleteGymModal.typedName" 
                                   :placeholder="deleteGymModal.name" 
                                   required 
                                   autofocus 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-bold text-xs focus:border-red-500 focus:outline-none placeholder-slate-400 dark:placeholder-slate-600">
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="deleteGymModal = null" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 font-semibold text-xs transition-colors cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" 
                                    :disabled="deleteGymModal.typedName.trim().toLowerCase() !== deleteGymModal.name.trim().toLowerCase() && deleteGymModal.typedName.trim().toLowerCase() !== deleteGymModal.slug.trim().toLowerCase()"
                                    class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs shadow-lg shadow-red-600/20 transition-all flex items-center gap-1.5 cursor-pointer">
                                <span>🗑️ Permanently Delete Gym</span>
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
</x-admin-layout>
