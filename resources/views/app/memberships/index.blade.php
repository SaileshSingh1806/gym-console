<x-app-layout header="Gym Memberships & Plans">
    <div class="space-y-4" x-data="{ 
        showNewModal: {{ $errors->any() ? 'true' : 'false' }}, 
        editPlan: null, 
        newPlanType: 'single', 
        newMaxMembers: 1, 
        newPrice: '', 
        newDurationValue: 1, 
        newDurationType: 'months',
        setNewPlanType(type) {
            this.newPlanType = type;
            if (type === 'single') this.newMaxMembers = 1;
            else if (type === 'duo') this.newMaxMembers = 2;
            else if (type === 'family') this.newMaxMembers = 4;
            else if (type === 'group') this.newMaxMembers = 5;
        },
        setEditPlanType(type) {
            if (!this.editPlan) return;
            this.editPlan.plan_type = type;
            if (type === 'single') this.editPlan.max_members = 1;
            else if (type === 'duo') this.editPlan.max_members = 2;
            else if (type === 'family') this.editPlan.max_members = 4;
            else if (type === 'group' && (!this.editPlan.max_members || this.editPlan.max_members < 2)) this.editPlan.max_members = 5;
        }
    }">
        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 text-xs space-y-1">
                <div class="font-black flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Please fix the following errors to save the membership plan:</span>
                </div>
                <ul class="list-disc list-inside pl-6 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Top Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center font-bold shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight leading-none">Gym Membership Packages</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Configure packages for Single, Duo (2 Persons), Family, and Custom Groups (e.g. ₹35,000 for 5 People).</p>
                </div>
            </div>
            @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('plans.create') || auth()->user()->hasPermission('memberships.create') || auth()->user()->hasPermission('memberships.manage'))
                <button @click="showNewModal = true; setNewPlanType('single'); newPrice = ''; newDurationValue = 1; newDurationType = 'months'" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center gap-2 shadow-sm transition-all hover:scale-[1.02] cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Membership Plan</span>
                </button>
            @endif
        </div>

        <!-- Membership Plans Available -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5">
            @forelse($plans as $plan)
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col justify-between hover:border-amber-500/50 dark:hover:border-amber-500/40 transition shadow-sm group">
                    <div>
                        <div class="flex justify-between items-start gap-2 mb-2">
                            <div class="min-w-0">
                                <h4 class="font-black text-slate-900 dark:text-white text-sm leading-tight truncate group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ $plan->name }}</h4>
                                <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                    @if($plan->plan_type === 'duo' || $plan->max_members === 2)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                                            <svg class="w-3 h-3 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                            <span>Duo (2 Persons)</span>
                                        </span>
                                    @elseif($plan->plan_type === 'family')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 dark:bg-purple-500/15 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-500/30">
                                            <svg class="w-3 h-3 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span>Family ({{ $plan->max_members ?? 4 }} Persons)</span>
                                        </span>
                                    @elseif($plan->plan_type === 'group' || ($plan->max_members ?? 1) > 2)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                                            <svg class="w-3 h-3 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span>Group ({{ $plan->max_members ?? 5 }} Persons)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                            <svg class="w-3 h-3 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>Single (1 Person)</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $plan->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : 'bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-500 dark:border-slate-700' }} font-bold">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3 line-clamp-2 min-h-[32px]">{{ $plan->description ?? 'Standard full gym floor access membership plan.' }}</p>

                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 mb-3 space-y-1.5 text-xs text-slate-700 dark:text-slate-300">
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Total Fee:</span>
                                <div class="text-right">
                                    <span class="text-lg font-black text-amber-600 dark:text-amber-400 leading-none">
                                        {{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($plan->price, 0) }}
                                    </span>
                                </div>
                            </div>
                            @if(($plan->max_members ?? 1) > 1)
                                <div class="flex justify-between items-center text-[11px] text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded-lg border border-emerald-200/60 dark:border-emerald-500/20">
                                    <span>Per Person:</span>
                                    <span>{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($plan->price / ($plan->max_members ?: 1), 0) }} / person</span>
                                </div>
                            @endif
                            <div class="flex justify-between border-t border-slate-200 dark:border-slate-800/80 pt-1.5 text-xs">
                                <span class="text-slate-500 dark:text-slate-400 font-medium">Duration:</span>
                                <span class="font-bold text-slate-900 dark:text-white capitalize">{{ $plan->duration_value }} {{ $plan->duration_type }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400 font-medium">Capacity:</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $plan->max_members ?? 1 }} {{ ($plan->max_members ?? 1) > 1 ? 'People' : 'Person' }}</span>
                            </div>
                            @if($plan->tax_rate > 0)
                                <div class="flex justify-between text-[11px]">
                                    <span class="text-slate-500 dark:text-slate-400">Tax Rate:</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $plan->tax_rate }}%</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('plans.edit') || auth()->user()->hasPermission('memberships.edit') || auth()->user()->hasPermission('plans.create') || auth()->user()->hasPermission('memberships.manage'))
                        <div class="flex items-center gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                            <button @click="editPlan = {{ json_encode([
                                'id' => $plan->id,
                                'name' => $plan->name,
                                'description' => $plan->description,
                                'plan_type' => $plan->plan_type ?? 'single',
                                'max_members' => (int) ($plan->max_members ?? 1),
                                'duration_type' => $plan->duration_type,
                                'duration_value' => $plan->duration_value,
                                'price' => (float)$plan->price,
                                'tax_rate' => (float)$plan->tax_rate,
                                'is_active' => (bool)$plan->is_active,
                            ]) }}" class="flex-1 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-slate-700 text-xs font-bold transition cursor-pointer text-center">
                                Edit Plan
                            </button>
                            @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('plans.delete') || auth()->user()->hasPermission('memberships.delete') || auth()->user()->hasPermission('memberships.manage'))
                            <form action="{{ route('app.membership-plans.delete', $plan->id) }}" method="POST" 
                                  data-confirm="Are you sure you want to delete membership plan '{{ addslashes($plan->name) }}'? Existing active member subscriptions with this plan will remain valid." 
                                  data-confirm-title="Delete Membership Plan" 
                                  data-confirm-btn="Yes, Delete Plan" 
                                  data-confirm-type="danger" 
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-xl bg-red-50 hover:bg-red-500 hover:text-white text-red-600 border border-red-200 dark:bg-red-500/10 dark:hover:bg-red-500 dark:text-red-400 dark:border-red-500/20 text-xs font-bold transition cursor-pointer" title="Delete Plan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 mx-auto flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">No Membership Plans Configured</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                        Create membership packages (Single, Duo, Family, or Custom Groups) so you can enroll new members.
                    </p>
                    @if(auth()->user()->hasPermission('memberships.manage'))
                        <button @click="showNewModal = true; setNewPlanType('single')" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-sm transition cursor-pointer">
                            Create First Plan
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Active Member Contracts: Mobile Cards (block md:hidden) & Desktop Table (hidden md:block) -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Member Subscribed Contracts</h3>
                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">{{ $memberships->total() }} total</span>
            </div>

            <!-- Mobile Cards View (block md:hidden) -->
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($memberships as $ms)
                    @php
                        $isPaidFull = (float)$ms->paid_amount >= (float)$ms->final_amount;
                        $remainingDue = max(0, (float)$ms->final_amount - (float)$ms->paid_amount);
                    @endphp
                    <div class="p-4 space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <a href="{{ route('app.members.show', $ms->member_id) }}" class="font-bold text-slate-900 dark:text-white text-xs hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                    {{ $ms->member?->full_name ?? ('Member #' . $ms->member_id) }}
                                </a>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold text-[11px]">{{ $ms->plan?->name ?? 'Standard Plan' }}</span>
                                    @if(($ms->plan?->plan_type ?? 'single') !== 'single')
                                        <span class="text-[10px] text-slate-500 capitalize">({{ $ms->plan?->plan_type }})</span>
                                    @endif
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ms->status === 'ACTIVE' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : 'bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' }}">
                                {{ $ms->status }}
                            </span>
                        </div>

                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 text-xs space-y-1.5">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-500 dark:text-slate-400">Validity:</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $ms->start_date ? $ms->start_date->format('M d, Y') : '—' }} → {{ $ms->end_date ? $ms->end_date->format('M d, Y') : '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] pt-1 border-t border-slate-200 dark:border-slate-800/60">
                                <span class="text-slate-500 dark:text-slate-400">Total Contract:</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($ms->final_amount, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-500 dark:text-slate-400">Paid:</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($ms->paid_amount, 2) }}</span>
                            </div>
                            @if($remainingDue > 0)
                                <div class="flex items-center justify-between text-[11px] text-amber-600 dark:text-amber-400 font-bold pt-1 border-t border-slate-200 dark:border-slate-800/60">
                                    <span>Remaining Due:</span>
                                    <span>{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($remainingDue, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-500 text-xs">No member contracts found.</div>
                @endforelse
            </div>

            <!-- Desktop Table View (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[650px]">
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
                                    <a href="{{ route('app.members.show', $ms->member_id) }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                        {{ $ms->member?->full_name ?? ('Member #' . $ms->member_id) }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-3.5">
                                    <span class="text-amber-600 dark:text-amber-400 font-medium text-xs">{{ $ms->plan?->name ?? 'Standard Plan' }}</span>
                                    @if(($ms->plan?->plan_type ?? 'single') !== 'single')
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 capitalize">({{ $ms->plan?->plan_type }} plan)</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3.5 text-slate-500 dark:text-slate-400 text-xs">
                                    {{ $ms->start_date ? $ms->start_date->format('M d, Y') : '—' }} → {{ $ms->end_date ? $ms->end_date->format('M d, Y') : '—' }}
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

        @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('plans.create') || auth()->user()->hasPermission('plans.edit') || auth()->user()->hasPermission('memberships.create') || auth()->user()->hasPermission('memberships.edit') || auth()->user()->hasPermission('memberships.manage'))
            <!-- Create Membership Plan Modal -->
            <div x-show="showNewModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-200 rounded-3xl max-w-lg w-full max-h-[92vh] overflow-y-auto p-5 sm:p-7 shadow-2xl" @click.away="showNewModal = false">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-xs">
                                +
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">Create Membership Package</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Set up pricing, duration, and group capacity</p>
                            </div>
                        </div>
                        <button @click="showNewModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer text-lg font-bold">✕</button>
                    </div>

                    <form action="{{ route('app.membership-plans.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Plan Category Selection (Single / Duo / Family / Group) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Package Category / Group Capacity <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <!-- 1. Single -->
                                <label class="p-3 rounded-xl border flex flex-col items-center text-center cursor-pointer transition-all group"
                                       :class="newPlanType === 'single' ? 'bg-emerald-50 dark:bg-emerald-500/15 border-emerald-500 text-emerald-700 dark:text-emerald-300 ring-2 ring-emerald-500/30 font-bold' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <input type="radio" name="plan_type" value="single" x-model="newPlanType" :checked="newPlanType === 'single'" checked @change="setNewPlanType('single')" class="sr-only">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                         :class="newPlanType === 'single' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <span class="text-xs">Single</span>
                                    <span class="text-[9px] text-slate-500 dark:text-slate-400">1 Person</span>
                                </label>

                                <!-- 2. Duo -->
                                <label class="p-3 rounded-xl border flex flex-col items-center text-center cursor-pointer transition-all group"
                                       :class="newPlanType === 'duo' ? 'bg-indigo-50 dark:bg-indigo-500/15 border-indigo-500 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/30 font-bold' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <input type="radio" name="plan_type" value="duo" x-model="newPlanType" :checked="newPlanType === 'duo'" @change="setNewPlanType('duo')" class="sr-only">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                         :class="newPlanType === 'duo' ? 'bg-indigo-500/20 text-indigo-600 dark:text-indigo-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    </div>
                                    <span class="text-xs">Duo</span>
                                    <span class="text-[9px] text-slate-500 dark:text-slate-400">2 Persons</span>
                                </label>

                                <!-- 3. Family -->
                                <label class="p-3 rounded-xl border flex flex-col items-center text-center cursor-pointer transition-all group"
                                       :class="newPlanType === 'family' ? 'bg-purple-50 dark:bg-purple-500/15 border-purple-500 text-purple-700 dark:text-purple-300 ring-2 ring-purple-500/30 font-bold' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <input type="radio" name="plan_type" value="family" x-model="newPlanType" :checked="newPlanType === 'family'" @change="setNewPlanType('family')" class="sr-only">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                         :class="newPlanType === 'family' ? 'bg-purple-500/20 text-purple-600 dark:text-purple-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </div>
                                    <span class="text-xs">Family</span>
                                    <span class="text-[9px] text-slate-500 dark:text-slate-400">3-4 Persons</span>
                                </label>

                                <!-- 4. Group / Corporate (e.g. 5 people) -->
                                <label class="p-3 rounded-xl border flex flex-col items-center text-center cursor-pointer transition-all group"
                                       :class="newPlanType === 'group' ? 'bg-amber-50 dark:bg-amber-500/15 border-amber-500 text-amber-700 dark:text-amber-300 ring-2 ring-amber-500/30 font-bold' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <input type="radio" name="plan_type" value="group" x-model="newPlanType" :checked="newPlanType === 'group'" @change="setNewPlanType('group')" class="sr-only">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                         :class="newPlanType === 'group' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    </div>
                                    <span class="text-xs">Group</span>
                                    <span class="text-[9px] text-slate-500 dark:text-slate-400">5+ Persons</span>
                                </label>
                            </div>

                            <!-- Custom Capacity Input when Group or Family is selected -->
                            <div x-show="newPlanType === 'group' || newPlanType === 'family'" x-transition class="mt-3 p-3 rounded-xl bg-amber-50/70 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-amber-900 dark:text-amber-200">Total Persons / Members Included</label>
                                        <p class="text-[10px] text-amber-700 dark:text-amber-400">e.g. Set <strong>5</strong> for a 5-people group package</p>
                                    </div>
                                    <div class="w-24">
                                        <input type="number" 
                                               x-model="newMaxMembers" 
                                               min="2" 
                                               max="50" 
                                               class="w-full px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-500/40 text-slate-900 dark:text-white font-black text-center text-sm focus:border-amber-500 focus:outline-none shadow-sm">
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="max_members" :value="newPlanType === 'single' ? 1 : (newPlanType === 'duo' ? 2 : newMaxMembers)">
                        </div>

                        <!-- Plan Title -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Package Name / Title <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="e.g. Group Yearly 5-Pax, Duo 1-Month" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none shadow-sm">
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Package Description</label>
                            <input type="text" name="description" placeholder="e.g. Complete gym floor access for 5 group members with locker access" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none shadow-sm">
                        </div>

                        <!-- Quick Duration Presets & Inputs -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Validity / Duration <span class="text-rose-500">*</span></label>
                                <!-- Presets -->
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="newDurationValue = 1; newDurationType = 'months'" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">1M</button>
                                    <button type="button" @click="newDurationValue = 3; newDurationType = 'months'" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">3M</button>
                                    <button type="button" @click="newDurationValue = 6; newDurationType = 'months'" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">6M</button>
                                    <button type="button" @click="newDurationValue = 1; newDurationType = 'years'" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 hover:bg-amber-200 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 cursor-pointer">1 Year</button>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input type="number" name="duration_value" x-model="newDurationValue" required min="1" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none shadow-sm">
                                </div>
                                <div>
                                    <select name="duration_type" x-model="newDurationType" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none shadow-sm">
                                        <option value="months">Months</option>
                                        <option value="years">Years</option>
                                        <option value="days">Days</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Price & Tax -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Package Price ({{ auth()->user()->tenant?->currency_symbol ?? '₹' }}) <span class="text-rose-500">*</span></label>
                                <input type="number" step="0.01" name="price" x-model="newPrice" required placeholder="e.g. 35000" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs font-bold focus:border-amber-500 focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tax Rate (%)</label>
                                <input type="number" step="0.01" name="tax_rate" value="0.00" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none shadow-sm">
                            </div>
                        </div>

                        <!-- Live Price Breakdown Banner -->
                        <template x-if="newPrice > 0 && newMaxMembers > 1">
                            <div class="p-3 rounded-xl bg-gradient-to-r from-emerald-500/10 via-emerald-500/5 to-transparent border border-emerald-500/20 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">✓</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">
                                        Per Person Cost: <strong class="text-emerald-600 dark:text-emerald-400 font-black">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}<span x-text="Math.round(newPrice / (newMaxMembers || 1)).toLocaleString()"></span> / person</strong>
                                    </span>
                                </div>
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-text="newMaxMembers + ' Persons Package'"></span>
                            </div>
                        </template>

                        <div class="pt-1">
                            <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="rounded bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-amber-500 focus:ring-0">
                                <span class="text-xs font-semibold">Enable this plan for registrations</span>
                            </label>
                        </div>

                        <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="showNewModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 text-xs font-bold cursor-pointer">Cancel</button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20 transition-all cursor-pointer">Save Package</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Membership Plan Modal -->
            <div x-show="editPlan !== null" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-200 rounded-3xl max-w-lg w-full max-h-[92vh] overflow-y-auto p-5 sm:p-7 shadow-2xl" @click.away="editPlan = null">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-xs">
                                ✎
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">Edit Membership Package</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Update rates, validity, or group capacity</p>
                            </div>
                        </div>
                        <button @click="editPlan = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold cursor-pointer">✕</button>
                    </div>

                    <template x-if="editPlan !== null">
                        <form :action="'/app/membership-plans/' + editPlan.id" method="POST" class="space-y-4">
                            @csrf

                            <!-- Plan Category Selection in Edit Modal -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Package Category / Group Capacity <span class="text-rose-500">*</span></label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <label class="p-3 rounded-xl border flex flex-col items-center text-center cursor-pointer transition-all group"
                                           :class="editPlan.plan_type === 'single' ? 'bg-emerald-50 dark:bg-emerald-500/15 border-emerald-500 text-emerald-700 dark:text-emerald-300 ring-2 ring-emerald-500/30 font-bold' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                        <input type="radio" name="plan_type" value="single" x-model="editPlan.plan_type" :checked="editPlan.plan_type === 'single'" @change="setEditPlanType('single')" class="sr-only">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                             :class="editPlan.plan_type === 'single' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </div>
                                        <span class="text-xs">Single</span>
                                        <span class="text-[9px] text-slate-500 dark:text-slate-400">1 Person</span>
                                    </label>
                                    <label class="p-3 rounded-xl border flex flex-col items-center text-center cursor-pointer transition-all group"
                                           :class="editPlan.plan_type === 'duo' ? 'bg-indigo-50 dark:bg-indigo-500/15 border-indigo-500 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/30 font-bold' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                        <input type="radio" name="plan_type" value="duo" x-model="editPlan.plan_type" :checked="editPlan.plan_type === 'duo'" @change="setEditPlanType('duo')" class="sr-only">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                             :class="editPlan.plan_type === 'duo' ? 'bg-indigo-500/20 text-indigo-600 dark:text-indigo-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        </div>
                                        <span class="text-xs">Duo</span>
                                        <span class="text-[9px] text-slate-500 dark:text-slate-400">2 Persons</span>
                                    </label>
                                    <label class="p-3 rounded-xl border flex flex-col items-center text-center cursor-pointer transition-all group"
                                           :class="editPlan.plan_type === 'family' ? 'bg-purple-50 dark:bg-purple-500/15 border-purple-500 text-purple-700 dark:text-purple-300 ring-2 ring-purple-500/30 font-bold' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                        <input type="radio" name="plan_type" value="family" x-model="editPlan.plan_type" :checked="editPlan.plan_type === 'family'" @change="setEditPlanType('family')" class="sr-only">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                             :class="editPlan.plan_type === 'family' ? 'bg-purple-500/20 text-purple-600 dark:text-purple-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </div>
                                        <span class="text-xs">Family</span>
                                        <span class="text-[9px] text-slate-500 dark:text-slate-400">3-4 Persons</span>
                                    </label>
                                    <label class="p-3 rounded-xl border flex flex-col items-center text-center cursor-pointer transition-all group"
                                           :class="editPlan.plan_type === 'group' ? 'bg-amber-50 dark:bg-amber-500/15 border-amber-500 text-amber-700 dark:text-amber-300 ring-2 ring-amber-500/30 font-bold' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                        <input type="radio" name="plan_type" value="group" x-model="editPlan.plan_type" :checked="editPlan.plan_type === 'group'" @change="setEditPlanType('group')" class="sr-only">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                             :class="editPlan.plan_type === 'group' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        </div>
                                        <span class="text-xs">Group</span>
                                        <span class="text-[9px] text-slate-500 dark:text-slate-400">5+ Persons</span>
                                    </label>
                                </div>

                                <!-- Custom Capacity Input for Edit -->
                                <div x-show="editPlan.plan_type === 'group' || editPlan.plan_type === 'family'" x-transition class="mt-3 p-3 rounded-xl bg-amber-50/70 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <label class="block text-xs font-bold text-amber-900 dark:text-amber-200">Total Persons / Members Included</label>
                                            <p class="text-[10px] text-amber-700 dark:text-amber-400">e.g. Set <strong>5</strong> for a 5-people group package</p>
                                        </div>
                                        <div class="w-24">
                                            <input type="number" 
                                                   x-model="editPlan.max_members" 
                                                   min="2" 
                                                   max="50" 
                                                   class="w-full px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-500/40 text-slate-900 dark:text-white font-black text-center text-sm focus:border-amber-500 focus:outline-none shadow-sm">
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="max_members" :value="editPlan.plan_type === 'single' ? 1 : (editPlan.plan_type === 'duo' ? 2 : (editPlan.max_members || 1))">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Package Name / Title <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" x-model="editPlan.name" required class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none shadow-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                                <input type="text" name="description" x-model="editPlan.description" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none shadow-sm">
                            </div>

                            <!-- Duration with presets -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Validity / Duration <span class="text-rose-500">*</span></label>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="editPlan.duration_value = 1; editPlan.duration_type = 'months'" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">1M</button>
                                        <button type="button" @click="editPlan.duration_value = 3; editPlan.duration_type = 'months'" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">3M</button>
                                        <button type="button" @click="editPlan.duration_value = 6; editPlan.duration_type = 'months'" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">6M</button>
                                        <button type="button" @click="editPlan.duration_value = 1; editPlan.duration_type = 'years'" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 hover:bg-amber-200 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 cursor-pointer">1 Year</button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <input type="number" name="duration_value" x-model="editPlan.duration_value" required min="1" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none shadow-sm">
                                    </div>
                                    <div>
                                        <select name="duration_type" x-model="editPlan.duration_type" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none shadow-sm">
                                            <option value="months">Months</option>
                                            <option value="years">Years</option>
                                            <option value="days">Days</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Package Price ({{ auth()->user()->tenant?->currency_symbol ?? '₹' }}) <span class="text-rose-500">*</span></label>
                                    <input type="number" step="0.01" name="price" x-model="editPlan.price" required class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs font-bold focus:border-amber-500 focus:outline-none shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tax Rate (%)</label>
                                    <input type="number" step="0.01" name="tax_rate" x-model="editPlan.tax_rate" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none shadow-sm">
                                </div>
                            </div>

                            <!-- Live Price Breakdown Banner in Edit -->
                            <template x-if="editPlan.price > 0 && editPlan.max_members > 1">
                                <div class="p-3 rounded-xl bg-gradient-to-r from-emerald-500/10 via-emerald-500/5 to-transparent border border-emerald-500/20 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">✓</span>
                                        <span class="font-medium text-slate-700 dark:text-slate-300">
                                            Per Person Cost: <strong class="text-emerald-600 dark:text-emerald-400 font-black">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}<span x-text="Math.round(editPlan.price / (editPlan.max_members || 1)).toLocaleString()"></span> / person</strong>
                                        </span>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-text="editPlan.max_members + ' Persons Package'"></span>
                                </div>
                            </template>

                            <div class="pt-1">
                                <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" x-model="editPlan.is_active" class="rounded bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-amber-500 focus:ring-0">
                                    <span class="text-xs font-semibold">Active and selectable</span>
                                </label>
                            </div>

                            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                                <button type="button" @click="editPlan = null" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 text-xs font-bold cursor-pointer">Cancel</button>
                                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20 transition-all cursor-pointer">Update Package</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
