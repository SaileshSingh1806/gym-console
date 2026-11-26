<x-app-layout header="Gym Memberships & Plans">

    @push('styles')
    <style>
        .membership-workspace { min-width:0; font-variant-numeric:tabular-nums; }
        .membership-workspace .mp-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; }
        .membership-workspace .mp-tabs { display:flex; gap:20px; border-bottom:1px solid rgb(var(--c-line)); }
        .membership-workspace .mp-tab { display:flex; align-items:center; gap:8px; padding:10px 2px; border-bottom:2px solid transparent; color:rgb(var(--c-fg-muted)); font-size:13px; font-weight:500; cursor:pointer; }
        .membership-workspace .mp-tab[aria-selected="true"] { color:rgb(var(--c-accent-fg)); border-bottom-color:rgb(var(--c-accent)); }
        .membership-workspace .mp-grid { display:grid; grid-template-columns:minmax(0,1.3fr) minmax(0,1fr); gap:12px; align-items:start; }
        .membership-workspace .mp-panel { background:rgb(var(--c-surface)); border:1px solid rgb(var(--c-line)); border-radius:12px; min-width:0; overflow:hidden; }
        .membership-workspace .mp-panel-header { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border-bottom:1px solid rgb(var(--c-line)); }
        .membership-workspace .mp-row { width:100%; display:flex; align-items:center; gap:10px; padding:13px 14px; text-align:left; border-bottom:1px solid rgb(var(--c-line)); border-left:2px solid transparent; cursor:pointer; }
        .membership-workspace .mp-row:last-child { border-bottom:0; }
        .membership-workspace .mp-row:hover { background:rgb(var(--c-raised)); }
        .membership-workspace .mp-row[aria-pressed="true"] { border-left-color:rgb(var(--c-accent)); background:rgb(var(--c-accent)/.06); }
        .membership-workspace .mp-row-icon { display:grid; place-items:center; width:34px; height:34px; flex-shrink:0; border-radius:8px; background:rgb(var(--c-raised)); color:rgb(var(--c-fg-muted)); }
        .membership-workspace .mp-row-title { font-size:14px; font-weight:500; overflow-wrap:anywhere; }
        .membership-workspace .mp-detail { padding:16px; min-width:0; }
        .membership-workspace .mp-detail h4 { font-size:16px; line-height:1.4; }
        .membership-workspace .mp-detail .mp-price { font-size:24px; font-weight:600; color:rgb(var(--c-fg)); }
        .membership-workspace .mp-detail .mp-actions { display:flex; flex-wrap:wrap; gap:8px; padding-top:12px; border-top:1px solid rgb(var(--c-line)); }
        .membership-workspace .mp-action { display:inline-flex; align-items:center; justify-content:center; gap:6px; min-height:36px; padding:7px 12px; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; border:1px solid rgb(var(--action-tone)/.24); color:rgb(var(--action-tone)); background:rgb(var(--action-tone)/.10); }
        .membership-workspace .mp-action:hover { background:rgb(var(--action-tone)/.18); }
        .membership-workspace .mp-pause { --action-tone:var(--c-warning); }
        .membership-workspace .mp-resume { --action-tone:var(--c-success); }
        .membership-workspace .mp-delete { --action-tone:var(--c-danger); }
        .membership-workspace .mp-contracts table th { font-size:12px; padding:10px 12px; white-space:nowrap; }
        .membership-workspace .mp-contracts table td { padding:12px; font-size:13px; }
        .membership-workspace .mp-contracts table :is(th,td):nth-child(4),.membership-workspace .mp-contracts table :is(th,td):nth-child(5) { text-align:right; }
        .membership-workspace .mp-contracts thead { background:rgb(var(--c-raised)); }
        .membership-workspace .mp-contracts .mp-scroll { overflow:auto; max-height:min(70vh,760px); }
        .membership-workspace .mp-contracts thead { position:sticky; top:0; z-index:1; }
        @media(max-width:900px) { .membership-workspace .mp-grid { grid-template-columns:minmax(0,1fr); } }
        @media(max-width:640px) { .membership-workspace .mp-header .ds-btn { width:100%; } .membership-workspace .mp-tabs { gap:14px; } .membership-workspace .mp-detail { padding:12px; } }
    </style>
    @endpush

    <div class="space-y-4 membership-workspace" x-data="{ 
        showNewModal: {{ $errors->any() ? 'true' : 'false' }}, 
        editPlan: null,
        workspaceTab: {{ request()->has($memberships->getPageName()) ? "'subscriptions'" : "'plans'" }},
        selectedPlanId: {{ (int) ($plans->first()?->id ?? 0) }}, 
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
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 text-xs space-y-1">
                <div class="font-semibold flex items-center gap-2">
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

        <div class="mp-header">
            <div><h1 class="ds-title">Memberships</h1><p class="ds-caption mt-1">Manage membership plans and member subscriptions.</p></div>
            @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('plans.create') || auth()->user()->hasPermission('memberships.create') || auth()->user()->hasPermission('memberships.manage'))
                <button @click="showNewModal = true; setNewPlanType('single'); newPrice = ''; newDurationValue = 1; newDurationType = 'months'" class="ds-btn ds-btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Create plan</span>
                </button>
            @endif
        </div>
        <div class="mp-tabs" role="tablist" aria-label="Membership workspace">
            <button type="button" role="tab" class="mp-tab" :aria-selected="workspaceTab === 'plans'" @click="workspaceTab = 'plans'">Membership plans <span class="ds-badge ds-badge-neutral">{{ $plans->count() }}</span></button>
            <button type="button" role="tab" class="mp-tab" :aria-selected="workspaceTab === 'subscriptions'" @click="workspaceTab = 'subscriptions'">Member subscriptions <span class="ds-badge ds-badge-neutral">{{ $memberships->total() }}</span></button>
        </div>
        <div x-show="workspaceTab === 'plans'" x-cloak>
            @if($plans->isNotEmpty())
            <div class="mp-grid">
                <section class="mp-panel" aria-label="Plan catalogue">
                    <div class="mp-panel-header"><h2 class="ds-heading">Plan catalogue</h2><span class="ds-caption">{{ $plans->count() }} plans</span></div>
                    @foreach($plans as $cataloguePlan)
                    <button type="button" class="mp-row" :aria-pressed="selectedPlanId === {{ (int) $cataloguePlan->id }}" @click="selectedPlanId = {{ (int) $cataloguePlan->id }}">
                        <span class="mp-row-icon"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/></svg></span>
                        <span class="flex-1 min-w-0"><span class="mp-row-title block">{{ $cataloguePlan->name }}</span><span class="ds-caption block capitalize">{{ $cataloguePlan->duration_value }} {{ $cataloguePlan->duration_type }} · {{ $cataloguePlan->plan_type ?? 'single' }} · {{ $cataloguePlan->max_members ?? 1 }} member(s)</span></span>
                        <span class="text-right shrink-0"><span class="block text-sm font-semibold text-fg">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($cataloguePlan->price, 0) }}</span><span class="ds-caption {{ $cataloguePlan->is_active ? 'text-success' : 'text-warning' }}">{{ $cataloguePlan->is_active ? 'Active' : 'Paused' }}</span></span>
                        <svg class="w-4 h-4 text-fg-muted shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                    @endforeach
                </section>
                <section class="mp-panel" aria-label="Selected plan details">
                    <div class="mp-panel-header"><h2 class="ds-heading">Plan details</h2></div>
                    @foreach($plans as $plan)
                    
                <div class="mp-detail" x-show="selectedPlanId === {{ (int) $plan->id }}" x-cloak>
                    <div>
                        <div class="flex justify-between items-start gap-2 mb-2">
                            <div class="min-w-0">
                                <h4 class="font-semibold text-fg dark:text-white text-sm leading-tight ">{{ $plan->name }}</h4>
                                <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                    @if($plan->plan_type === 'duo' || $plan->max_members === 2)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                                            <svg class="w-3 h-3 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                            <span>Duo (2 Persons)</span>
                                        </span>
                                    @elseif($plan->plan_type === 'family')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-purple-50 dark:bg-purple-500/15 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-500/30">
                                            <svg class="w-3 h-3 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span>Family ({{ $plan->max_members ?? 4 }} Persons)</span>
                                        </span>
                                    @elseif($plan->plan_type === 'group' || ($plan->max_members ?? 1) > 2)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                                            <svg class="w-3 h-3 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span>Group ({{ $plan->max_members ?? 5 }} Persons)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                            <svg class="w-3 h-3 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>Single (1 Person)</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($plan->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/15 dark:text-amber-400 dark:border-amber-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Paused
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-fg-muted dark:text-fg-muted mb-2 ">{{ $plan->description ?? 'Standard full gym floor access membership plan.' }}</p>

                        @if(! $plan->is_active)
                            <div class="mb-3 px-2.5 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-700 dark:text-amber-300 font-medium flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>Paused (Hidden from new enrollment)</span>
                            </div>
                        @endif

                        <div class="p-3 rounded-xl bg-raised border border-line dark:border-line mb-3 space-y-1.5 text-xs text-fg-muted dark:text-fg-muted">
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold">Total Fee:</span>
                                <div class="text-right">
                                    <span class="mp-price">
                                        {{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($plan->price, 0) }}
                                    </span>
                                </div>
                            </div>
                            @if(($plan->max_members ?? 1) > 1)
                                <div class="flex justify-between items-center text-xs text-emerald-600 dark:text-emerald-400 font-semibold bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded-lg border border-emerald-200/60 dark:border-emerald-500/20">
                                    <span>Per Person:</span>
                                    <span>{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($plan->price / ($plan->max_members ?: 1), 0) }} / person</span>
                                </div>
                            @endif
                            <div class="flex justify-between border-t border-line dark:border-line pt-1.5 text-xs">
                                <span class="text-fg-muted dark:text-fg-muted font-medium">Duration:</span>
                                <span class="font-semibold text-fg dark:text-white capitalize">{{ $plan->duration_value }} {{ $plan->duration_type }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-fg-muted dark:text-fg-muted font-medium">Capacity:</span>
                                <span class="font-semibold text-fg dark:text-white">{{ $plan->max_members ?? 1 }} {{ ($plan->max_members ?? 1) > 1 ? 'People' : 'Person' }}</span>
                            </div>
                            @if($plan->tax_rate > 0)
                                <div class="flex justify-between text-xs">
                                    <span class="text-fg-muted dark:text-fg-muted">Tax Rate:</span>
                                    <span class="font-semibold text-fg-muted dark:text-fg-muted">{{ $plan->tax_rate }}%</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('plans.edit') || auth()->user()->hasPermission('memberships.edit') || auth()->user()->hasPermission('plans.create') || auth()->user()->hasPermission('memberships.manage'))
                        <div class="mp-actions">
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
                            ]) }}" class="ds-btn ds-btn-primary flex-1">
                                Edit plan
                            </button>

                            <!-- Pause / Resume Toggle Button -->
                            <form action="{{ route('app.membership-plans.toggle-status', $plan->id) }}" method="POST" class="inline">
                                @csrf
                                @if($plan->is_active)
                                    <button type="submit" 
                                            title="Pause this membership package (hide from new enrollments)" 
                                            class="mp-action mp-pause">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Pause</span>
                                    </button>
                                @else
                                    <button type="submit" 
                                            title="Resume this membership package (show in new enrollments)" 
                                            class="mp-action mp-resume">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Resume</span>
                                    </button>
                                @endif
                            </form>

                            @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('plans.delete') || auth()->user()->hasPermission('memberships.delete') || auth()->user()->hasPermission('memberships.manage'))
                            <form action="{{ route('app.membership-plans.delete', $plan->id) }}" method="POST" 
                                  data-confirm="Are you sure you want to delete membership plan '{{ addslashes($plan->name) }}'? Existing active member subscriptions with this plan will remain valid." 
                                  data-confirm-title="Delete Membership Plan" 
                                  data-confirm-btn="Yes, Delete Plan" 
                                  data-confirm-type="danger" 
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mp-action mp-delete" title="Delete Plan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    @endif
                </div>
            
                    @endforeach
                </section>
            </div>
            @else
                
                <div class="col-span-full p-8 text-center bg-surface dark:bg-surface border border-line dark:border-line rounded-xl space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 mx-auto flex items-center justify-center font-semibold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <h4 class="text-sm font-semibold text-fg dark:text-white">No Membership Plans Configured</h4>
                    <p class="text-xs text-fg-muted dark:text-fg-muted max-w-md mx-auto">
                        Create membership packages (Single, Duo, Family, or Custom Groups) so you can enroll new members.
                    </p>
                    @if(auth()->user()->hasPermission('memberships.manage'))
                        <button @click="showNewModal = true; setNewPlanType('single')" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-xs transition cursor-pointer">
                            Create First Plan
                        </button>
                    @endif
                </div>
            
            @endif
        </div>
        <!-- Active Member Contracts: Mobile Cards (block md:hidden) & Desktop Table (hidden md:block) -->
        <div x-show="workspaceTab === 'subscriptions'" x-cloak class="mp-panel mp-contracts">
            <div class="mp-panel-header">
                <h3 class="ds-heading">Member subscriptions</h3>
                <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted">{{ $memberships->total() }} total</span>
            </div>

            <!-- Mobile Cards View (block md:hidden) -->
            <div class="block md:hidden divide-y divide-line dark:divide-line">
                @forelse($memberships as $ms)
                    @php
                        $isPaidFull = (float)$ms->paid_amount >= (float)$ms->final_amount;
                        $remainingDue = max(0, (float)$ms->final_amount - (float)$ms->paid_amount);
                    @endphp
                    <div class="p-4 space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <a href="{{ route('app.members.show', $ms->member_id) }}" class="font-semibold text-fg dark:text-white text-xs hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                    {{ $ms->member?->full_name ?? ('Member #' . $ms->member_id) }}
                                </a>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold text-xs">{{ $ms->plan?->name ?? 'Standard Plan' }}</span>
                                    @if(($ms->plan?->plan_type ?? 'single') !== 'single')
                                        <span class="text-xs text-fg-muted capitalize">({{ $ms->plan?->plan_type }})</span>
                                    @endif
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ms->status === 'ACTIVE' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : 'bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' }}">
                                {{ $ms->status }}
                            </span>
                        </div>

                        <div class="p-2.5 rounded-xl bg-raised dark:bg-raised border border-line dark:border-line text-xs space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-fg-muted dark:text-fg-muted">Validity:</span>
                                <span class="font-medium text-fg dark:text-fg">{{ $ms->start_date ? $ms->start_date->format('M d, Y') : '—' }} → {{ $ms->end_date ? $ms->end_date->format('M d, Y') : '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-1 border-t border-line dark:border-line">
                                <span class="text-fg-muted dark:text-fg-muted">Total Contract:</span>
                                <span class="font-semibold text-fg dark:text-white">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($ms->final_amount, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-fg-muted dark:text-fg-muted">Paid:</span>
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($ms->paid_amount, 2) }}</span>
                            </div>
                            @if($remainingDue > 0)
                                <div class="flex items-center justify-between text-xs text-amber-600 dark:text-amber-400 font-semibold pt-1 border-t border-line dark:border-line">
                                    <span>Remaining Due:</span>
                                    <span>{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($remainingDue, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-fg-muted text-xs">No member contracts found.</div>
                @endforelse
            </div>

            <!-- Desktop Table View (hidden md:block) -->
            <div class="hidden md:block mp-scroll">
                <table class="w-full text-left text-xs min-w-[650px]">
                    <thead class="bg-raised dark:bg-slate-950/80 border-b border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs">
                        <tr>
                            <th class="py-2.5 px-3.5 font-semibold">Member</th>
                            <th class="py-2.5 px-3.5 font-semibold">Plan & Category</th>
                            <th class="py-2.5 px-3.5 font-semibold">Duration Period</th>
                            <th class="py-2.5 px-3.5 font-semibold">Contract Amount</th>
                            <th class="py-2.5 px-3.5 font-semibold">Paid Amount</th>
                            <th class="py-2.5 px-3.5 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                        @forelse($memberships as $ms)
                            <tr class="hover:bg-raised dark:hover:bg-raised transition">
                                <td class="py-2.5 px-3.5 font-semibold text-fg dark:text-white text-xs">
                                    <a href="{{ route('app.members.show', $ms->member_id) }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                        {{ $ms->member?->full_name ?? ('Member #' . $ms->member_id) }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-3.5">
                                    <span class="text-amber-600 dark:text-amber-400 font-medium text-xs">{{ $ms->plan?->name ?? 'Standard Plan' }}</span>
                                    @if(($ms->plan?->plan_type ?? 'single') !== 'single')
                                        <span class="block text-xs text-fg-muted dark:text-fg-muted capitalize">({{ $ms->plan?->plan_type }} plan)</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3.5 text-fg-muted dark:text-fg-muted text-xs">
                                    {{ $ms->start_date ? $ms->start_date->format('M d, Y') : '—' }} → {{ $ms->end_date ? $ms->end_date->format('M d, Y') : '—' }}
                                </td>
                                <td class="py-2.5 px-3.5 font-semibold text-fg dark:text-white text-xs">
                                    {{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($ms->final_amount, 2) }}
                                </td>
                                <td class="py-2.5 px-3.5 text-emerald-600 dark:text-emerald-400 font-semibold text-xs">
                                    {{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($ms->paid_amount, 2) }}
                                </td>
                                <td class="py-2.5 px-3.5">
                                    <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                        {{ $ms->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-fg-muted text-xs">No member contracts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($memberships->hasPages())
                <div class="p-3 border-t border-line dark:border-line bg-raised dark:bg-slate-950/40">
                    {{ $memberships->links() }}
                </div>
            @endif
        </div>

        @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('plans.create') || auth()->user()->hasPermission('plans.edit') || auth()->user()->hasPermission('memberships.create') || auth()->user()->hasPermission('memberships.edit') || auth()->user()->hasPermission('memberships.manage'))
            <!-- Create Membership Plan Modal -->
            <div x-show="showNewModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
                <div class="bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-fg rounded-xl max-w-lg w-full max-h-[92vh] overflow-y-auto p-5 sm:p-7" @click.away="showNewModal = false">
                    <div class="flex justify-between items-center mb-4 border-b border-line dark:border-line pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-semibold text-xs">
                                +
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-fg dark:text-white">Create Membership Package</h3>
                                <p class="text-xs text-fg-muted dark:text-fg-muted">Set up pricing, duration, and group capacity</p>
                            </div>
                        </div>
                        <button @click="showNewModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-white cursor-pointer text-lg font-semibold">✕</button>
                    </div>

                    <form action="{{ route('app.membership-plans.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Plan Category Selection (Single / Duo / Family / Group) -->
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-2">Package Category / Group Capacity <span class="text-rose-500">*</span></label>
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
                                    <span class="text-xs text-fg-muted dark:text-fg-muted">1 Person</span>
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
                                    <span class="text-xs text-fg-muted dark:text-fg-muted">2 Persons</span>
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
                                    <span class="text-xs text-fg-muted dark:text-fg-muted">3-4 Persons</span>
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
                                    <span class="text-xs text-fg-muted dark:text-fg-muted">5+ Persons</span>
                                </label>
                            </div>

                            <!-- Custom Capacity Input when Group or Family is selected -->
                            <template x-if="newPlanType === 'group' || newPlanType === 'family'">
                                <div class="mt-3 p-3 rounded-xl bg-amber-50/70 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-amber-900 dark:text-amber-200">Total Persons / Members Included</label>
                                            <p class="text-xs text-amber-700 dark:text-amber-400">e.g. Set <strong>5</strong> for a 5-people group package</p>
                                        </div>
                                        <div class="w-24">
                                            <input type="number" 
                                                   x-model="newMaxMembers" 
                                                   min="2" 
                                                   max="50" 
                                                   class="w-full px-3 py-1.5 rounded-lg bg-surface dark:bg-surface border border-amber-300 dark:border-amber-500/40 text-fg dark:text-white font-semibold text-center text-sm focus:border-amber-500 focus:outline-none">
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <input type="hidden" name="max_members" :value="newPlanType === 'single' ? 1 : (newPlanType === 'duo' ? 2 : (newMaxMembers || 1))">
                        </div>

                        <!-- Plan Title -->
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Package Name / Title <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="e.g. Group Yearly 5-Pax, Duo 1-Month" class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Package Description</label>
                            <input type="text" name="description" placeholder="e.g. Complete gym floor access for 5 group members with locker access" class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                        </div>

                        <!-- Quick Duration Presets & Inputs -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted">Validity / Duration <span class="text-rose-500">*</span></label>
                                <!-- Presets -->
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="newDurationValue = 1; newDurationType = 'months'" class="px-2 py-0.5 rounded text-xs font-semibold bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted cursor-pointer">1M</button>
                                    <button type="button" @click="newDurationValue = 3; newDurationType = 'months'" class="px-2 py-0.5 rounded text-xs font-semibold bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted cursor-pointer">3M</button>
                                    <button type="button" @click="newDurationValue = 6; newDurationType = 'months'" class="px-2 py-0.5 rounded text-xs font-semibold bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted cursor-pointer">6M</button>
                                    <button type="button" @click="newDurationValue = 1; newDurationType = 'years'" class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 hover:bg-amber-200 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 cursor-pointer">1 Year</button>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input type="number" name="duration_value" x-model="newDurationValue" required min="1" class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                </div>
                                <div>
                                    <select name="duration_type" x-model="newDurationType" class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
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
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Package Price ({{ auth()->user()->tenant?->currency_symbol ?? '₹' }}) <span class="text-rose-500">*</span></label>
                                <input type="number" step="0.01" name="price" x-model="newPrice" required placeholder="e.g. 35000" class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs font-semibold focus:border-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Tax Rate (%)</label>
                                <input type="number" step="0.01" name="tax_rate" value="0.00" class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Live Price Breakdown Banner -->
                        <template x-if="newPrice > 0 && newMaxMembers > 1">
                            <div class="p-3 rounded-xl border border-emerald-500/20 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-semibold">✓</span>
                                    <span class="font-medium text-fg-muted dark:text-fg-muted">
                                        Per Person Cost: <strong class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}<span x-text="Math.round(newPrice / (newMaxMembers || 1)).toLocaleString()"></span> / person</strong>
                                    </span>
                                </div>
                                <span class="text-xs font-semibold text-fg-muted" x-text="newMaxMembers + ' Persons Package'"></span>
                            </div>
                        </template>

                        <div class="pt-1">
                            <label class="flex items-center gap-2 text-xs text-fg-muted dark:text-fg-muted cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="rounded bg-raised dark:bg-surface border-line dark:border-line text-amber-500 focus:ring-0">
                                <span class="text-xs font-semibold">Active & Available for Enrollment</span>
                            </label>
                            <p class="text-xs text-fg-muted dark:text-fg-muted ml-5 mt-0.5">Uncheck to save as Paused (will not appear during new member enrollments).</p>
                        </div>

                        <div class="flex justify-end gap-2.5 pt-3 border-t border-line dark:border-line">
                            <button type="button" @click="showNewModal = false" class="px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line text-xs font-semibold cursor-pointer">Cancel</button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-xs transition-all cursor-pointer">Save Package</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Membership Plan Modal -->
            <div x-show="editPlan !== null" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
                <div class="bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-fg rounded-xl max-w-lg w-full max-h-[92vh] overflow-y-auto p-5 sm:p-7" @click.away="editPlan = null">
                    <div class="flex justify-between items-center mb-4 border-b border-line dark:border-line pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-semibold text-xs">
                                ✎
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-fg dark:text-white">Edit Membership Package</h3>
                                <p class="text-xs text-fg-muted dark:text-fg-muted">Update rates, validity, or group capacity</p>
                            </div>
                        </div>
                        <button @click="editPlan = null" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-lg font-semibold cursor-pointer">✕</button>
                    </div>

                    <template x-if="editPlan !== null">
                        <form :action="'/app/membership-plans/' + editPlan.id" method="POST" class="space-y-4">
                            @csrf

                            <!-- Plan Category Selection in Edit Modal -->
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-2">Package Category / Group Capacity <span class="text-rose-500">*</span></label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <label class="p-3 rounded-xl border flex flex-col items-center text-center cursor-pointer transition-all group"
                                           :class="editPlan.plan_type === 'single' ? 'bg-emerald-50 dark:bg-emerald-500/15 border-emerald-500 text-emerald-700 dark:text-emerald-300 ring-2 ring-emerald-500/30 font-bold' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                        <input type="radio" name="plan_type" value="single" x-model="editPlan.plan_type" :checked="editPlan.plan_type === 'single'" @change="setEditPlanType('single')" class="sr-only">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                             :class="editPlan.plan_type === 'single' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </div>
                                        <span class="text-xs">Single</span>
                                        <span class="text-xs text-fg-muted dark:text-fg-muted">1 Person</span>
                                    </label>
                                    <label class="p-3 rounded-xl border flex flex-col items-center text-center cursor-pointer transition-all group"
                                           :class="editPlan.plan_type === 'duo' ? 'bg-indigo-50 dark:bg-indigo-500/15 border-indigo-500 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/30 font-bold' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                        <input type="radio" name="plan_type" value="duo" x-model="editPlan.plan_type" :checked="editPlan.plan_type === 'duo'" @change="setEditPlanType('duo')" class="sr-only">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                             :class="editPlan.plan_type === 'duo' ? 'bg-indigo-500/20 text-indigo-600 dark:text-indigo-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        </div>
                                        <span class="text-xs">Duo</span>
                                        <span class="text-xs text-fg-muted dark:text-fg-muted">2 Persons</span>
                                    </label>
                                    <label class="p-3 rounded-xl border flex flex-col items-center text-center cursor-pointer transition-all group"
                                           :class="editPlan.plan_type === 'family' ? 'bg-purple-50 dark:bg-purple-500/15 border-purple-500 text-purple-700 dark:text-purple-300 ring-2 ring-purple-500/30 font-bold' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                        <input type="radio" name="plan_type" value="family" x-model="editPlan.plan_type" :checked="editPlan.plan_type === 'family'" @change="setEditPlanType('family')" class="sr-only">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                             :class="editPlan.plan_type === 'family' ? 'bg-purple-500/20 text-purple-600 dark:text-purple-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </div>
                                        <span class="text-xs">Family</span>
                                        <span class="text-xs text-fg-muted dark:text-fg-muted">3-4 Persons</span>
                                    </label>
                                    <label class="p-3 rounded-xl border flex flex-col items-center text-center cursor-pointer transition-all group"
                                           :class="editPlan.plan_type === 'group' ? 'bg-amber-50 dark:bg-amber-500/15 border-amber-500 text-amber-700 dark:text-amber-300 ring-2 ring-amber-500/30 font-bold' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                        <input type="radio" name="plan_type" value="group" x-model="editPlan.plan_type" :checked="editPlan.plan_type === 'group'" @change="setEditPlanType('group')" class="sr-only">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                             :class="editPlan.plan_type === 'group' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        </div>
                                        <span class="text-xs">Group</span>
                                        <span class="text-xs text-fg-muted dark:text-fg-muted">5+ Persons</span>
                                    </label>
                                </div>

                                <!-- Custom Capacity Input for Edit -->
                                <template x-if="editPlan.plan_type === 'group' || editPlan.plan_type === 'family'">
                                    <div class="mt-3 p-3 rounded-xl bg-amber-50/70 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <label class="block text-xs font-semibold text-amber-900 dark:text-amber-200">Total Persons / Members Included</label>
                                                <p class="text-xs text-amber-700 dark:text-amber-400">e.g. Set <strong>5</strong> for a 5-people group package</p>
                                            </div>
                                            <div class="w-24">
                                                <input type="number" 
                                                       x-model="editPlan.max_members" 
                                                       min="2" 
                                                       max="50" 
                                                       class="w-full px-3 py-1.5 rounded-lg bg-surface dark:bg-surface border border-amber-300 dark:border-amber-500/40 text-fg dark:text-white font-semibold text-center text-sm focus:border-amber-500 focus:outline-none">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <input type="hidden" name="max_members" :value="editPlan.plan_type === 'single' ? 1 : (editPlan.plan_type === 'duo' ? 2 : (editPlan.max_members || 1))">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Package Name / Title <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" x-model="editPlan.name" required class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Description</label>
                                <input type="text" name="description" x-model="editPlan.description" class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                            </div>

                            <!-- Duration with presets -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted">Validity / Duration <span class="text-rose-500">*</span></label>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="editPlan.duration_value = 1; editPlan.duration_type = 'months'" class="px-2 py-0.5 rounded text-xs font-semibold bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted cursor-pointer">1M</button>
                                        <button type="button" @click="editPlan.duration_value = 3; editPlan.duration_type = 'months'" class="px-2 py-0.5 rounded text-xs font-semibold bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted cursor-pointer">3M</button>
                                        <button type="button" @click="editPlan.duration_value = 6; editPlan.duration_type = 'months'" class="px-2 py-0.5 rounded text-xs font-semibold bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted cursor-pointer">6M</button>
                                        <button type="button" @click="editPlan.duration_value = 1; editPlan.duration_type = 'years'" class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 hover:bg-amber-200 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 cursor-pointer">1 Year</button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <input type="number" name="duration_value" x-model="editPlan.duration_value" required min="1" class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <select name="duration_type" x-model="editPlan.duration_type" class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                            <option value="months">Months</option>
                                            <option value="years">Years</option>
                                            <option value="days">Days</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Package Price ({{ auth()->user()->tenant?->currency_symbol ?? '₹' }}) <span class="text-rose-500">*</span></label>
                                    <input type="number" step="0.01" name="price" x-model="editPlan.price" required class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs font-semibold focus:border-amber-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Tax Rate (%)</label>
                                    <input type="number" step="0.01" name="tax_rate" x-model="editPlan.tax_rate" class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                </div>
                            </div>

                            <!-- Live Price Breakdown Banner in Edit -->
                            <template x-if="editPlan.price > 0 && editPlan.max_members > 1">
                                <div class="p-3 rounded-xl border border-emerald-500/20 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-semibold">✓</span>
                                        <span class="font-medium text-fg-muted dark:text-fg-muted">
                                            Per Person Cost: <strong class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}<span x-text="Math.round(editPlan.price / (editPlan.max_members || 1)).toLocaleString()"></span> / person</strong>
                                        </span>
                                    </div>
                                    <span class="text-xs font-semibold text-fg-muted" x-text="editPlan.max_members + ' Persons Package'"></span>
                                </div>
                            </template>

                            <div class="pt-1">
                                <label class="flex items-center gap-2 text-xs text-fg-muted dark:text-fg-muted cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" x-model="editPlan.is_active" class="rounded bg-raised dark:bg-surface border-line dark:border-line text-amber-500 focus:ring-0">
                                    <span class="text-xs font-semibold">Active & Available for Enrollment</span>
                                </label>
                                <p class="text-xs text-fg-muted dark:text-fg-muted ml-5 mt-0.5">Uncheck to pause this plan. Paused plans will not appear during member enrollment.</p>
                            </div>

                            <div class="flex justify-end gap-2.5 pt-3 border-t border-line dark:border-line">
                                <button type="button" @click="editPlan = null" class="px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line text-xs font-semibold cursor-pointer">Cancel</button>
                                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-xs transition-all cursor-pointer">Update Package</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
