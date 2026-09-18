<x-app-layout header="Gym Memberships & Plans">
    <div class="space-y-4" x-data="{ showNewModal: false, editPlan: null, newPlanType: 'single' }">
        <!-- Top Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5 shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight leading-none">Gym Membership Packages</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Subscription plans and rates for single, duo (2 persons), and family (4 persons).</p>
                </div>
            </div>
            @if(auth()->user()->hasPermission('memberships.manage'))
                <button @click="showNewModal = true; newPlanType = 'single'" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 shadow-sm transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Create Plan
                </button>
            @endif
        </div>

        <!-- Membership Plans Available -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            @forelse($plans as $plan)
                <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition shadow-sm">
                    <div>
                        <div class="flex justify-between items-start gap-2 mb-1.5">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white text-xs leading-tight">{{ $plan->name }}</h4>
                                <div class="mt-1">
                                    @if($plan->plan_type === 'duo')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                                            <svg class="w-2.5 h-2.5 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                            <span>Duo (2 Persons)</span>
                                        </span>
                                    @elseif($plan->plan_type === 'family')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-purple-50 dark:bg-purple-500/15 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-500/30">
                                            <svg class="w-2.5 h-2.5 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span>Family (4 Persons)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                            <svg class="w-2.5 h-2.5 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>Single (1 Person)</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[9px] {{ $plan->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : 'bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-500 dark:border-slate-700' }} font-bold">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2 line-clamp-2 min-h-[26px]">{{ $plan->description ?? 'Standard gym access membership' }}</p>

                        <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 mb-2.5 space-y-1 text-xs text-slate-700 dark:text-slate-300">
                            <div class="flex justify-between items-baseline">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">Plan Fee:</span>
                                <span class="text-base font-extrabold text-amber-600 dark:text-amber-400">
                                    {{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($plan->price, 2) }}
                                </span>
                            </div>
                            <div class="flex justify-between border-t border-slate-200 dark:border-slate-800/60 pt-1 text-[11px]">
                                <span class="text-slate-500 dark:text-slate-400">Duration:</span>
                                <span class="font-bold text-slate-900 dark:text-white capitalize">{{ $plan->duration_value }} {{ $plan->duration_type }}</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-slate-500 dark:text-slate-400">Capacity:</span>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $plan->max_members ?? ($plan->plan_type === 'duo' ? 2 : ($plan->plan_type === 'family' ? 4 : 1)) }} {{ ($plan->max_members ?? 1) > 1 ? 'Persons' : 'Person' }}</span>
                            </div>
                            @if($plan->tax_rate > 0)
                                <div class="flex justify-between text-[11px]">
                                    <span class="text-slate-500 dark:text-slate-400">Tax Rate:</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $plan->tax_rate }}%</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if(auth()->user()->hasPermission('memberships.manage'))
                        <div class="flex items-center gap-1.5 pt-2 border-t border-slate-200 dark:border-slate-800">
                            <button @click="editPlan = {{ json_encode([
                                'id' => $plan->id,
                                'name' => $plan->name,
                                'description' => $plan->description,
                                'plan_type' => $plan->plan_type ?? 'single',
                                'max_members' => (int) ($plan->max_members ?? 1),
                                'duration_type' => $plan->duration_type,
                                'duration_value' => $plan->duration_value,
                                'price' => $plan->price,
                                'tax_rate' => $plan->tax_rate,
                                'is_active' => (bool)$plan->is_active,
                            ]) }}" class="flex-1 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-slate-700 text-[11px] font-semibold transition cursor-pointer">
                                Edit Plan
                            </button>
                            <form action="{{ route('app.membership-plans.delete', $plan->id) }}" method="POST" 
                                  data-confirm="Are you sure you want to delete membership plan '{{ addslashes($plan->name) }}'? Existing active member subscriptions with this plan will remain valid." 
                                  data-confirm-title="Delete Membership Plan" 
                                  data-confirm-btn="Yes, Delete Plan" 
                                  data-confirm-type="danger" 
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 rounded-lg bg-red-50 hover:bg-red-500 hover:text-white text-red-600 border border-red-200 dark:bg-red-500/10 dark:hover:bg-red-500 dark:text-red-400 dark:border-red-500/20 text-xs font-bold transition cursor-pointer" title="Delete Plan">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-full p-6 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl space-y-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 mx-auto flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">No Membership Plans Configured</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                        Create your first membership package (Single, Duo, or Family) so you can enroll new members.
                    </p>
                    @if(auth()->user()->hasPermission('memberships.manage'))
                        <button @click="showNewModal = true; newPlanType = 'single'" class="px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-sm transition cursor-pointer">
                            + Create Your First Plan
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Active Member Contracts Table -->
        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Member Subscribed Contracts</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-2.5 px-3.5 font-semibold">Member</th>
                            <th class="py-2.5 px-3.5 font-semibold">Plan & Category</th>
                            <th class="py-2.5 px-3.5 font-semibold">Duration Period</th>
                            <th class="py-2.5 px-3.5 font-semibold">Contract Amount</th>
                            <th class="py-2.5 px-3.5 font-semibold">Paid Amount</th>
                            <th class="py-2.5 px-3.5 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @forelse($memberships as $ms)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="py-2.5 px-3.5 font-bold text-slate-900 dark:text-white text-xs">
                                    {{ $ms->member->full_name }}
                                </td>
                                <td class="py-2.5 px-3.5">
                                    <span class="text-amber-600 dark:text-amber-400 font-medium text-xs">{{ $ms->plan->name }}</span>
                                    @if(($ms->plan->plan_type ?? 'single') !== 'single')
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 capitalize">({{ $ms->plan->plan_type }} plan)</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3.5 text-slate-500 dark:text-slate-400 text-xs">
                                    {{ $ms->start_date->format('M d, Y') }} → {{ $ms->end_date->format('M d, Y') }}
                                </td>
                                <td class="py-2.5 px-3.5 font-bold text-slate-900 dark:text-white text-xs">
                                    {{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($ms->final_amount, 2) }}
                                </td>
                                <td class="py-2.5 px-3.5 text-emerald-600 dark:text-emerald-400 font-semibold text-xs">
                                    {{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($ms->paid_amount, 2) }}
                                </td>
                                <td class="py-2.5 px-3.5">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                        {{ $ms->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-500 text-xs">No member contracts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($memberships->hasPages())
                <div class="p-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                    {{ $memberships->links() }}
                </div>
            @endif
        </div>

        @if(auth()->user()->hasPermission('memberships.manage'))
            <!-- Create Membership Plan Modal -->
            <div x-show="showNewModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3" x-cloak>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-200 rounded-xl max-w-md w-full p-4 shadow-2xl" @click.away="showNewModal = false">
                    <div class="flex justify-between items-center mb-3 border-b border-slate-200 dark:border-slate-800 pb-2.5">
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white">Create New Membership Plan</h3>
                        <button @click="showNewModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">✕</button>
                    </div>

                <form action="{{ route('app.membership-plans.store') }}" method="POST" class="space-y-3">
                    @csrf

                    <!-- Plan Category Selection (Single / Duo / Family) -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Plan Type / Capacity *</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="p-2.5 rounded-lg border flex flex-col items-center text-center cursor-pointer transition group"
                                   :class="newPlanType === 'single' ? 'bg-emerald-50 dark:bg-emerald-500/15 border-emerald-500 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-500/30' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700 hover:text-slate-900 dark:hover:text-slate-200'">
                                <input type="radio" name="plan_type" value="single" x-model="newPlanType" class="sr-only">
                                <div class="w-6 h-6 rounded-md flex items-center justify-center mb-1 transition-colors"
                                     :class="newPlanType === 'single' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-300'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <span class="text-xs font-bold">Single</span>
                                <span class="text-[9px] text-slate-500 dark:text-slate-400">1 Person</span>
                            </label>
                            <label class="p-2.5 rounded-lg border flex flex-col items-center text-center cursor-pointer transition group"
                                   :class="newPlanType === 'duo' ? 'bg-indigo-50 dark:bg-indigo-500/15 border-indigo-500 text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-500/30' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700 hover:text-slate-900 dark:hover:text-slate-200'">
                                <input type="radio" name="plan_type" value="duo" x-model="newPlanType" class="sr-only">
                                <div class="w-6 h-6 rounded-md flex items-center justify-center mb-1 transition-colors"
                                     :class="newPlanType === 'duo' ? 'bg-indigo-500/20 text-indigo-600 dark:text-indigo-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-300'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                                <span class="text-xs font-bold">Duo</span>
                                <span class="text-[9px] text-slate-500 dark:text-slate-400">2 Persons</span>
                            </label>
                            <label class="p-2.5 rounded-lg border flex flex-col items-center text-center cursor-pointer transition group"
                                   :class="newPlanType === 'family' ? 'bg-purple-50 dark:bg-purple-500/15 border-purple-500 text-purple-700 dark:text-purple-300 ring-1 ring-purple-500/30' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700 hover:text-slate-900 dark:hover:text-slate-200'">
                                <input type="radio" name="plan_type" value="family" x-model="newPlanType" class="sr-only">
                                <div class="w-6 h-6 rounded-md flex items-center justify-center mb-1 transition-colors"
                                     :class="newPlanType === 'family' ? 'bg-purple-500/20 text-purple-600 dark:text-purple-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-300'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <span class="text-xs font-bold">Family</span>
                                <span class="text-[9px] text-slate-500 dark:text-slate-400">4 Persons</span>
                            </label>
                        </div>
                        <input type="hidden" name="max_members" :value="newPlanType === 'duo' ? 2 : (newPlanType === 'family' ? 4 : 1)">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Plan Title *</label>
                        <input type="text" name="name" required placeholder="e.g. Monthly Duo, Standard 1M" class="w-full px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                        <input type="text" name="description" placeholder="Includes gym floor access" class="w-full px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Duration Value *</label>
                            <input type="number" name="duration_value" required value="1" min="1" class="w-full px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Duration Type *</label>
                            <select name="duration_type" class="w-full px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                <option value="months" selected>Months</option>
                                <option value="days">Days</option>
                                <option value="years">Years</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Price ({{ auth()->user()->tenant?->currency_symbol ?? '₹' }}) *</label>
                            <input type="number" step="0.01" name="price" required placeholder="1500.00" class="w-full px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Tax Rate (%)</label>
                            <input type="number" step="0.01" name="tax_rate" value="0.00" class="w-full px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-amber-500 focus:ring-0">
                            <span class="text-[11px]">Enable this plan for registrations</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2.5 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showNewModal = false" class="px-3.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 text-xs font-semibold">Cancel</button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-sm">Save Plan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Membership Plan Modal -->
        <div x-show="editPlan !== null" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-200 rounded-xl max-w-md w-full p-4 shadow-2xl" @click.away="editPlan = null">
                <div class="flex justify-between items-center mb-3 border-b border-slate-200 dark:border-slate-800 pb-2.5">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white">Edit Membership Plan</h3>
                    <button @click="editPlan = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">✕</button>
                </div>

                <template x-if="editPlan !== null">
                    <form :action="'/app/membership-plans/' + editPlan.id" method="POST" class="space-y-3">
                        @csrf

                        <!-- Plan Category Selection in Edit Modal -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Plan Type / Capacity *</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="p-2.5 rounded-lg border flex flex-col items-center text-center cursor-pointer transition group"
                                       :class="editPlan.plan_type === 'single' ? 'bg-emerald-50 dark:bg-emerald-500/15 border-emerald-500 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-500/30' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700 hover:text-slate-900 dark:hover:text-slate-200'">
                                    <input type="radio" name="plan_type" value="single" x-model="editPlan.plan_type" @change="editPlan.max_members = 1" class="sr-only">
                                    <div class="w-6 h-6 rounded-md flex items-center justify-center mb-1 transition-colors"
                                         :class="editPlan.plan_type === 'single' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-300'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <span class="text-xs font-bold">Single</span>
                                    <span class="text-[9px] text-slate-500 dark:text-slate-400">1 Person</span>
                                </label>
                                <label class="p-2.5 rounded-lg border flex flex-col items-center text-center cursor-pointer transition group"
                                       :class="editPlan.plan_type === 'duo' ? 'bg-indigo-50 dark:bg-indigo-500/15 border-indigo-500 text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-500/30' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700 hover:text-slate-900 dark:hover:text-slate-200'">
                                    <input type="radio" name="plan_type" value="duo" x-model="editPlan.plan_type" @change="editPlan.max_members = 2" class="sr-only">
                                    <div class="w-6 h-6 rounded-md flex items-center justify-center mb-1 transition-colors"
                                         :class="editPlan.plan_type === 'duo' ? 'bg-indigo-500/20 text-indigo-600 dark:text-indigo-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-300'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    </div>
                                    <span class="text-xs font-bold">Duo</span>
                                    <span class="text-[9px] text-slate-500 dark:text-slate-400">2 Persons</span>
                                </label>
                                <label class="p-2.5 rounded-lg border flex flex-col items-center text-center cursor-pointer transition group"
                                       :class="editPlan.plan_type === 'family' ? 'bg-purple-50 dark:bg-purple-500/15 border-purple-500 text-purple-700 dark:text-purple-300 ring-1 ring-purple-500/30' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700 hover:text-slate-900 dark:hover:text-slate-200'">
                                    <input type="radio" name="plan_type" value="family" x-model="editPlan.plan_type" @change="editPlan.max_members = 4" class="sr-only">
                                    <div class="w-6 h-6 rounded-md flex items-center justify-center mb-1 transition-colors"
                                         :class="editPlan.plan_type === 'family' ? 'bg-purple-500/20 text-purple-600 dark:text-purple-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-300'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </div>
                                    <span class="text-xs font-bold">Family</span>
                                    <span class="text-[9px] text-slate-500 dark:text-slate-400">4 Persons</span>
                                </label>
                            </div>
                            <input type="hidden" name="max_members" :value="editPlan.plan_type === 'duo' ? 2 : (editPlan.plan_type === 'family' ? 4 : 1)">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Plan Title *</label>
                            <input type="text" name="name" x-model="editPlan.name" required class="w-full px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                            <input type="text" name="description" x-model="editPlan.description" class="w-full px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Duration Value *</label>
                                <input type="number" name="duration_value" x-model="editPlan.duration_value" required min="1" class="w-full px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Duration Type *</label>
                                <select name="duration_type" x-model="editPlan.duration_type" class="w-full px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                    <option value="months">Months</option>
                                    <option value="days">Days</option>
                                    <option value="years">Years</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Price ({{ auth()->user()->tenant?->currency_symbol ?? '₹' }}) *</label>
                                <input type="number" step="0.01" name="price" x-model="editPlan.price" required class="w-full px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Tax Rate (%)</label>
                                <input type="number" step="0.01" name="tax_rate" x-model="editPlan.tax_rate" class="w-full px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="pt-1">
                            <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" x-model="editPlan.is_active" class="rounded bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-amber-500 focus:ring-0">
                                <span class="text-[11px]">Active and selectable</span>
                            </label>
                        </div>

                        <div class="flex justify-end gap-2 pt-2.5 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="editPlan = null" class="px-3.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 text-xs font-semibold cursor-pointer">Cancel</button>
                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-sm cursor-pointer">Update Plan</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
