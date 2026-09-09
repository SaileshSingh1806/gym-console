<x-admin-layout header="SaaS Global Platform Metrics & Control">
    <div class="space-y-8" x-data="{ showNewGymModal: false, showNewPlanModal: false }">
        <!-- Quick Action Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-white">Platform Administrator Controls</h2>
                <p class="text-xs text-slate-400">Total system control over all tenant gyms, subscriptions, plans, and users.</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="showNewGymModal = true" class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-red-500/10">
                    + Register Gym
                </button>
                <button @click="showNewPlanModal = true" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs">
                    + Create Plan
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Monthly Recurring Revenue</span>
                <span class="text-3xl font-extrabold text-white">₹{{ number_format($metrics['mrr'], 2) }}</span>
                <span class="text-xs text-emerald-400 block mt-1 font-medium">ARR: ₹{{ number_format($metrics['mrr'] * 12, 2) }}</span>
            </div>

            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Total Gym Businesses</span>
                <span class="text-3xl font-extrabold text-white">{{ $metrics['total_gyms'] }}</span>
                <span class="text-xs text-amber-400 block mt-1 font-medium">{{ $metrics['active_gyms'] }} Active • {{ $metrics['trial_gyms'] }} Trial</span>
            </div>

            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Total Platform Members</span>
                <span class="text-3xl font-extrabold text-white">{{ $totalMembers }}</span>
                <span class="text-xs text-slate-400 block mt-1">Across all gyms</span>
            </div>

            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Platform Invoiced Total</span>
                <span class="text-3xl font-extrabold text-emerald-400">₹{{ number_format($metrics['total_revenue'], 2) }}</span>
                <span class="text-xs text-slate-400 block mt-1">Total paid subscriptions</span>
            </div>
        </div>

        <!-- Recent Gyms Table -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 bg-slate-950/40 flex justify-between items-center">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Registered Gym Tenants</h3>
                <a href="{{ route('admin.gyms') }}" class="text-xs text-red-400 hover:underline">Manage All Gyms →</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Gym Name</th>
                            <th class="py-3 px-4">Owner Email</th>
                            <th class="py-3 px-4">SaaS Plan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($recentGyms as $g)
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-3 px-4 font-bold text-white">
                                    {{ $g->name }}
                                </td>
                                <td class="py-3 px-4 text-slate-400">{{ $g->email }}</td>
                                <td class="py-3 px-4 font-semibold text-amber-400">
                                    {{ $g->activeSubscription->plan->name ?? 'Free Trial' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $g->status === 'ACTIVE' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($g->status === 'TRIAL' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20') }}">
                                        {{ $g->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 flex items-center gap-2">
                                    <form action="{{ route('admin.gyms.impersonate', $g->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded bg-amber-500/10 hover:bg-amber-500 hover:text-slate-950 text-amber-400 border border-amber-500/20 text-[10px] font-bold transition-all">
                                            ⚡ Impersonate
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.gyms', ['search' => $g->slug]) }}" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 text-[10px] font-semibold">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">
                                    No gyms registered yet. Click "+ Register Gym" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Gym Modal -->
        <div x-show="showNewGymModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-xl w-full p-6 shadow-2xl" @click.away="showNewGymModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-white">Register New Gym Tenant</h3>
                    <button @click="showNewGymModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form action="{{ route('admin.gyms.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Gym Name *</label>
                            <input type="text" name="gym_name" required placeholder="Titan Fitness Club" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Primary Branch Name</label>
                            <input type="text" name="branch_name" placeholder="Downtown Flagship" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Owner Name *</label>
                            <input type="text" name="owner_name" required placeholder="John Owner" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Owner Email *</label>
                            <input type="email" name="email" required placeholder="owner@titan.com" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Phone Number</label>
                            <input type="text" name="phone" placeholder="+1 555-0100" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Owner Password *</label>
                            <input type="password" name="password" required value="password" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">SaaS Plan *</label>
                            <select name="plan_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (${{ number_format($p->price_monthly, 0) }}/mo)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Initial Status</label>
                            <select name="status" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="TRIAL">TRIAL</option>
                                <option value="SUSPENDED">SUSPENDED</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Currency</label>
                            <select name="currency" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
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

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                        <button type="button" @click="showNewGymModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold text-xs">Create Gym & Account</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Plan Modal -->
        <div x-show="showNewPlanModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-xl w-full p-6 shadow-2xl" @click.away="showNewPlanModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-white">Create SaaS Subscription Plan</h3>
                    <button @click="showNewPlanModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form action="{{ route('admin.plans.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Plan Name *</label>
                            <input type="text" name="name" required placeholder="Scale Plan" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Slug *</label>
                            <input type="text" name="slug" required placeholder="scale" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Monthly Price ($) *</label>
                            <input type="number" step="0.01" name="price_monthly" required placeholder="99.00" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Yearly Price ($) *</label>
                            <input type="number" step="0.01" name="price_yearly" required placeholder="990.00" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Member Limit (-1 = unltd)</label>
                            <input type="number" name="member_limit" value="1000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Branch Limit</label>
                            <input type="number" name="branch_limit" value="5" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Staff Limit</label>
                            <input type="number" name="staff_limit" value="15" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Trial Days</label>
                        <input type="number" name="trial_days" value="14" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                        <button type="button" @click="showNewPlanModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold text-xs">Save SaaS Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
