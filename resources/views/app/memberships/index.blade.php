<x-app-layout header="Memberships">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            .membership-workspace { font-family: Inter, system-ui, -apple-system, sans-serif; font-size: 14px; line-height: 1.5; }
            .membership-workspace .membership-plan { min-width: 0; }
            .membership-workspace table { font-variant-numeric: tabular-nums; }
            .membership-workspace input:not([type="radio"]):not([type="checkbox"]):not([type="hidden"]),
            .membership-workspace select, .membership-workspace textarea { min-height: 40px; }
            .membership-workspace :is(button,a,input,select,textarea):focus-visible,
            .membership-workspace label:has(input:focus-visible) { outline: 2px solid currentColor; outline-offset: 3px; }
            .membership-workspace button { min-height: 36px; }
            .membership-workspace .membership-plan button { white-space: nowrap; }
            .membership-workspace .membership-plan > div:last-child { flex-wrap: wrap; }
            .membership-workspace {
                --mw-surface: #ffffff; --mw-text: #202b36; --mw-muted: #63717e; --mw-line: #e0e6eb; --mw-hover: #f5f7fa; --mw-accent: #345b79; --mw-soft: #edf3f8;
                --mw-button: #345b79; --mw-button-text: #fff; --mw-active: #23734e;
            }
            .dark .membership-workspace {
                --mw-surface: #1b222b; --mw-text: #e8edf2; --mw-muted: #a5b2c0; --mw-line: #35404d; --mw-hover: #232d38; --mw-accent: #a8c9e5; --mw-soft: #26394a;
                --mw-button: #a8c9e5; --mw-button-text: #182938; --mw-active: #8bcba8;
            }
            .membership-workspace .membership-panel { background: var(--mw-surface); color: var(--mw-text); border: 1px solid var(--mw-line); border-radius: 6px; }
            .membership-workspace .membership-plan-row { display: grid; grid-template-columns: minmax(135px,2fr) minmax(85px,1fr) minmax(75px,.85fr) minmax(105px,1.1fr) minmax(70px,.75fr) minmax(190px,1.5fr); gap: 12px; align-items: center; padding: 11px 16px; border-bottom: 1px solid var(--mw-line); font-size: 13px; }
            .membership-workspace .membership-plan-row:last-child { border-bottom: 0; }
            .membership-workspace .membership-plan-head { color: var(--mw-muted); font-weight: 500; font-size: 12px; background: var(--mw-hover); border-radius: 6px 6px 0 0; padding-block: 9px; }
            .membership-workspace .membership-muted { color: var(--mw-muted); }
            .membership-workspace .membership-action, .membership-workspace .membership-primary { display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 5px 9px; min-height: 30px; border: 1px solid var(--mw-line); border-radius: 5px; color: var(--mw-text); font-size: 12px; font-weight: 500; background: transparent; cursor: pointer; white-space: nowrap; }
            .membership-workspace .membership-action:hover { background: var(--mw-hover); }
            .membership-workspace .membership-delete:hover { color: #dc2626; }
            .membership-workspace .membership-primary { background: var(--mw-button); color: var(--mw-button-text); border-color: transparent; padding: 7px 13px; min-height: 34px; font-size: 13px; }
            .membership-workspace .membership-status { display: inline-flex; align-items: center; gap: 6px; color: var(--mw-muted); font-size: 12px; white-space: nowrap; }
            .membership-workspace .membership-status.is-active { color: var(--mw-active); }
            .membership-workspace .membership-status i { width: 6px; height: 6px; background: currentColor; border-radius: 50%; }
            .membership-workspace .membership-mobile-label { display: none; }
            @media (max-width: 1280px) and (min-width: 768px) {
                .membership-workspace .membership-plan-list { overflow-x: auto; }
                .membership-workspace .membership-plan-row { min-width: 800px; }
            }
            @media (max-width: 767px) {
                .membership-workspace .membership-plan-head { display: none; }
                .membership-workspace .membership-plan-row { grid-template-columns: repeat(2,minmax(0,1fr)); gap: 9px 14px; padding: 12px; }
                .membership-workspace .membership-plan-row > :first-child, .membership-workspace .membership-row-actions { grid-column: 1 / -1; }
                .membership-workspace .membership-mobile-label { display: block; font-size: 11px; color: var(--mw-muted); margin-bottom: 2px; }
                .membership-workspace .membership-row-actions { padding-top: 5px; }
            }
            .membership-workspace .membership-plan-list { overflow-x: auto; }
            .membership-workspace .membership-plan-row:not(.membership-plan-head):hover { background: var(--mw-hover); }
            .membership-workspace .membership-plan-head { font-weight: 600; }
            .membership-workspace .membership-primary:hover { filter: brightness(.93); }
            .membership-workspace .membership-action { border-color: transparent; color: var(--mw-muted); }
            .membership-workspace .membership-action:hover { background: var(--mw-soft); color: var(--mw-accent); }
            .membership-workspace .membership-delete:hover { color: #c24141; }
            .membership-workspace .membership-section-header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding: 13px 16px; border-bottom:1px solid var(--mw-line); }
            .membership-workspace .membership-section-header h3 { color:var(--mw-text); font-size:15px; font-weight:600; letter-spacing:-.015em; }
            .membership-workspace .membership-panel table { color:var(--mw-text); font-size:13px; }
            .membership-workspace .membership-panel table thead { background:var(--mw-hover); color:var(--mw-muted); border-color:var(--mw-line); }
            .membership-workspace .membership-panel table th { font-size:12px; font-weight:600; white-space:nowrap; }
            .membership-workspace .membership-panel table td { font-size:13px; }
            .membership-workspace .membership-panel table tbody tr:hover { background:var(--mw-hover); }
            .membership-workspace .membership-panel a { color:var(--mw-accent); }
            .membership-workspace .membership-panel a:hover { text-decoration:underline; }
            .membership-workspace .membership-dialog { background:var(--mw-surface); color:var(--mw-text); border-color:var(--mw-line); }
            .membership-workspace .membership-dialog :is(input:not([type="radio"]):not([type="checkbox"]):not([type="hidden"]),select,textarea) { background:var(--mw-surface); color:var(--mw-text); border-color:var(--mw-line); border-radius:6px; font-size:14px; }
            .membership-workspace .membership-dialog :is(input,select,textarea):focus { border-color:var(--mw-accent); }
            .membership-workspace .membership-dialog label:has(input[type="radio"]:checked) { background:var(--mw-soft); border-color:var(--mw-accent); color:var(--mw-accent); box-shadow:none; }
            .membership-workspace .membership-dialog button[type="submit"] { background:var(--mw-button); color:var(--mw-button-text); box-shadow:none; }
            .membership-workspace .membership-dialog input[type="checkbox"] { accent-color:var(--mw-button); }
            .membership-workspace [x-cloak] { display: none !important; }
        </style>
    @endpush
    <div class="membership-workspace space-y-4" x-data="{ 
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
            <div class="p-4 rounded-lg bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 text-sm space-y-1">
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

        <!-- Top Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 py-0">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="text-xl font-semibold tracking-tight" style="color:var(--mw-text)">Membership plans</h3>
                    <p class="text-sm membership-muted mt-1">Manage membership pricing, duration and availability.</p>
                </div>
            </div>
            @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('plans.create') || auth()->user()->hasPermission('memberships.create') || auth()->user()->hasPermission('memberships.manage'))
                <button @click="showNewModal = true; setNewPlanType('single'); newPrice = ''; newDurationValue = 1; newDurationType = 'months'" class="membership-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Create plan</span>
                </button>
            @endif
        </div>

        <!-- Membership plans: compact management list -->
        <section class="membership-panel" aria-label="Membership plans">
            <div class="membership-section-header"><h3>Plans <span class="membership-muted text-xs font-normal ml-1">{{ count($plans) }}</span></h3><span class="membership-muted text-xs">Enrollment availability</span></div>
            <div class="membership-plan-list">
                <div class="membership-plan-row membership-plan-head" aria-hidden="true">
                    <span>Plan</span><span>Duration</span><span>Capacity</span><span>Price</span><span>Status</span><span>Actions</span>
                </div>
                @forelse($plans as $plan)
                    <div class="membership-plan-row">
                        <div class="min-w-0">
                            <div class="font-semibold break-words">{{ $plan->name }}</div>
                            <div class="membership-muted text-xs mt-0.5">{{ ucfirst($plan->plan_type ?? 'single') }}</div>
                            @if($plan->description)
                                <div class="membership-muted text-xs mt-1 break-words">{{ $plan->description }}</div>
                            @endif
                        </div>
                        <div><span class="membership-mobile-label">Duration</span>{{ $plan->duration_value }} {{ $plan->duration_type }}</div>
                        <div><span class="membership-mobile-label">Capacity</span>{{ $plan->max_members ?? 1 }} {{ ($plan->max_members ?? 1) > 1 ? 'members' : 'member' }}</div>
                        <div class="tabular-nums">
                            <span class="membership-mobile-label">Price</span>
                            <div class="font-semibold">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($plan->price, 2) }}</div>
                            @if(($plan->max_members ?? 1) > 1)
                                <div class="membership-muted text-xs">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($plan->price / ($plan->max_members ?: 1), 2) }} / member</div>
                            @endif
                            @if($plan->tax_rate > 0)
                                <div class="membership-muted text-xs">{{ $plan->tax_rate }}% tax</div>
                            @endif
                        </div>
                        <div><span class="membership-mobile-label">Status</span><span class="membership-status {{ $plan->is_active ? 'is-active' : '' }}"><i aria-hidden="true"></i>{{ $plan->is_active ? 'Active' : 'Paused' }}</span></div>
                        <div class="membership-row-actions">
                    @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('plans.edit') || auth()->user()->hasPermission('memberships.edit') || auth()->user()->hasPermission('plans.create') || auth()->user()->hasPermission('memberships.manage'))
                        <div class="flex items-center gap-1.5 whitespace-nowrap">
                            <button type="button" @click="editPlan = {{ json_encode([
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
                            ]) }}" class="membership-action">
                                Edit
                            </button>

                            <!-- Pause / Resume Toggle Button -->
                            <form action="{{ route('app.membership-plans.toggle-status', $plan->id) }}" method="POST" class="inline">
                                @csrf
                                @if($plan->is_active)
                                    <button type="submit" 
                                            title="Pause this membership package (hide from new enrollments)" 
                                            class="membership-action">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Pause</span>
                                    </button>
                                @else
                                    <button type="submit" 
                                            title="Resume this membership package (show in new enrollments)" 
                                            class="membership-action">
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
                                <button type="submit" class="membership-action membership-delete" title="Delete Plan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    @endif

                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center membership-muted text-sm">No membership plans yet.</div>
                @endforelse
            </div>
        </section>

        <!-- Active Member Contracts: Mobile Cards (block md:hidden) & Desktop Table (hidden md:block) -->
        <div class="membership-panel overflow-hidden">
            <div class="membership-section-header">
                <h3 class="font-semibold">Member subscriptions</h3>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $memberships->total() }} total</span>
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
                                <a href="{{ route('app.members.show', $ms->member_id) }}" class="font-medium text-slate-900 dark:text-white text-sm hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                    {{ $ms->member?->full_name ?? ('Member #' . $ms->member_id) }}
                                </a>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold text-xs">{{ $ms->plan?->name ?? 'Standard Plan' }}</span>
                                    @if(($ms->plan?->plan_type ?? 'single') !== 'single')
                                        <span class="text-xs text-slate-500 capitalize">({{ $ms->plan?->plan_type }})</span>
                                    @endif
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $ms->status === 'ACTIVE' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : 'bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' }}">
                                {{ $ms->status }}
                            </span>
                        </div>

                        <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 text-sm space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400">Validity:</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $ms->start_date ? $ms->start_date->format('M d, Y') : '—' }} → {{ $ms->end_date ? $ms->end_date->format('M d, Y') : '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200 dark:border-slate-800/60">
                                <span class="text-slate-500 dark:text-slate-400">Total amount:</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($ms->final_amount, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400">Paid:</span>
                                <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($ms->paid_amount, 2) }}</span>
                            </div>
                            @if($remainingDue > 0)
                                <div class="flex items-center justify-between text-xs text-amber-600 dark:text-amber-400 font-medium pt-1 border-t border-slate-200 dark:border-slate-800/60">
                                    <span>Balance due:</span>
                                    <span>{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($remainingDue, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-500 text-sm">No member subscriptions found.</div>
                @endforelse
            </div>

            <!-- Desktop Table View (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[800px]">
                    <thead class="bg-slate-50 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 text-xs">
                        <tr>
                            <th class="py-2.5 px-3.5 font-semibold">Member</th>
                            <th class="py-2.5 px-3.5 font-semibold">Plan</th>
                            <th class="py-2.5 px-3.5 font-semibold">Membership period</th>
                            <th class="py-2.5 px-3.5 font-semibold">Total amount</th>
                            <th class="py-2.5 px-3.5 font-semibold">Paid</th>
                            <th class="py-2.5 px-3.5 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @forelse($memberships as $ms)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="py-2.5 px-3.5 font-medium text-slate-900 dark:text-white text-sm">
                                    <a href="{{ route('app.members.show', $ms->member_id) }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                        {{ $ms->member?->full_name ?? ('Member #' . $ms->member_id) }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-3.5">
                                    <span class="text-slate-700 dark:text-slate-300 font-medium text-sm">{{ $ms->plan?->name ?? 'Standard Plan' }}</span>
                                    @if(($ms->plan?->plan_type ?? 'single') !== 'single')
                                        <span class="block text-xs text-slate-500 dark:text-slate-400 capitalize">({{ $ms->plan?->plan_type }} plan)</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3.5 text-slate-500 dark:text-slate-400 text-sm">
                                    {{ $ms->start_date ? $ms->start_date->format('M d, Y') : '—' }} → {{ $ms->end_date ? $ms->end_date->format('M d, Y') : '—' }}
                                </td>
                                <td class="py-2.5 px-3.5 font-medium text-slate-900 dark:text-white text-sm">
                                    {{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($ms->final_amount, 2) }}
                                </td>
                                <td class="py-2.5 px-3.5 text-slate-700 dark:text-slate-300 font-semibold text-sm">
                                    {{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($ms->paid_amount, 2) }}
                                </td>
                                <td class="py-2.5 px-3.5">
                                    <span class="px-2 py-0.5 rounded-md text-sm font-medium {{ $ms->status === 'ACTIVE' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : 'bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' }}">
                                        {{ $ms->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-500 text-sm">No member subscriptions found.</td>
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
            <div x-show="showNewModal" role="dialog" aria-modal="true" aria-label="Create plan" @keydown.escape.window="showNewModal = false" class="fixed inset-0 z-50 overflow-y-auto bg-black/70  flex items-center justify-center p-3 sm:p-4" x-cloak>
                <div class="membership-dialog bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-200 rounded-lg max-w-xl w-full max-h-[92vh] overflow-y-auto p-5 sm:p-7 shadow-2xl" @click.away="showNewModal = false">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-semibold text-sm">
                                +
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Create plan</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Set pricing, duration and member capacity.</p>
                            </div>
                        </div>
                        <button type="button" aria-label="Close create plan" @click="showNewModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer text-lg font-medium">✕</button>
                    </div>

                    <form action="{{ route('app.membership-plans.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Plan Category Selection (Single / Duo / Family / Group) -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Plan type <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <!-- 1. Single -->
                                <label class="p-3 rounded-lg border flex flex-col items-center text-center cursor-pointer transition-all group"
                                       :class="newPlanType === 'single' ? 'bg-slate-100 dark:bg-slate-800 border-amber-500 text-slate-900 dark:text-white ring-1 ring-amber-500 font-medium' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <input type="radio" name="plan_type" value="single" x-model="newPlanType" :checked="newPlanType === 'single'" checked @change="setNewPlanType('single')" class="sr-only">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                         :class="newPlanType === 'single' ? 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <span class="text-sm">Single</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">1 Person</span>
                                </label>

                                <!-- 2. Duo -->
                                <label class="p-3 rounded-lg border flex flex-col items-center text-center cursor-pointer transition-all group"
                                       :class="newPlanType === 'duo' ? 'bg-slate-100 dark:bg-slate-800 border-amber-500 text-slate-900 dark:text-white ring-1 ring-amber-500 font-medium' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <input type="radio" name="plan_type" value="duo" x-model="newPlanType" :checked="newPlanType === 'duo'" @change="setNewPlanType('duo')" class="sr-only">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                         :class="newPlanType === 'duo' ? 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    </div>
                                    <span class="text-sm">Duo</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">2 Persons</span>
                                </label>

                                <!-- 3. Family -->
                                <label class="p-3 rounded-lg border flex flex-col items-center text-center cursor-pointer transition-all group"
                                       :class="newPlanType === 'family' ? 'bg-slate-100 dark:bg-slate-800 border-amber-500 text-slate-900 dark:text-white ring-1 ring-amber-500 font-medium' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <input type="radio" name="plan_type" value="family" x-model="newPlanType" :checked="newPlanType === 'family'" @change="setNewPlanType('family')" class="sr-only">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                         :class="newPlanType === 'family' ? 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </div>
                                    <span class="text-sm">Family</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">3-4 Persons</span>
                                </label>

                                <!-- 4. Group / Corporate (e.g. 5 people) -->
                                <label class="p-3 rounded-lg border flex flex-col items-center text-center cursor-pointer transition-all group"
                                       :class="newPlanType === 'group' ? 'bg-slate-100 dark:bg-slate-800 border-amber-500 text-slate-900 dark:text-white ring-1 ring-amber-500 font-medium' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <input type="radio" name="plan_type" value="group" x-model="newPlanType" :checked="newPlanType === 'group'" @change="setNewPlanType('group')" class="sr-only">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                         :class="newPlanType === 'group' ? 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    </div>
                                    <span class="text-sm">Group</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">5+ Persons</span>
                                </label>
                            </div>

                            <!-- Custom Capacity Input when Group or Family is selected -->
                            <template x-if="newPlanType === 'group' || newPlanType === 'family'">
                                <div class="mt-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-800 dark:text-slate-200">Member capacity</label>
                                            <p class="text-xs text-slate-600 dark:text-slate-300">e.g. Set <strong>5</strong> for a 5-people group package</p>
                                        </div>
                                        <div class="w-24">
                                            <input type="number" 
                                                   x-model="newMaxMembers" 
                                                   min="2" 
                                                   max="50" 
                                                   class="w-full px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-500/40 text-slate-900 dark:text-white font-semibold text-center text-sm focus:border-amber-500 focus:outline-none ">
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <input type="hidden" name="max_members" :value="newPlanType === 'single' ? 1 : (newPlanType === 'duo' ? 2 : (newMaxMembers || 1))">
                        </div>

                        <!-- Plan Title -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Plan name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="e.g. Annual membership" class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-amber-500 focus:outline-none ">
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                            <input type="text" name="description" placeholder="e.g. Complete gym floor access for 5 group members with locker access" class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-amber-500 focus:outline-none ">
                        </div>

                        <!-- Quick Duration Presets & Inputs -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Duration <span class="text-rose-500">*</span></label>
                                <!-- Presets -->
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="newDurationValue = 1; newDurationType = 'months'" class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">1M</button>
                                    <button type="button" @click="newDurationValue = 3; newDurationType = 'months'" class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">3M</button>
                                    <button type="button" @click="newDurationValue = 6; newDurationType = 'months'" class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">6M</button>
                                    <button type="button" @click="newDurationValue = 1; newDurationType = 'years'" class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">1 Year</button>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input type="number" name="duration_value" x-model="newDurationValue" required min="1" class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-sm focus:border-amber-500 focus:outline-none ">
                                </div>
                                <div>
                                    <select name="duration_type" x-model="newDurationType" class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-sm focus:border-amber-500 focus:outline-none ">
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
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Plan price ({{ auth()->user()->tenant?->currency_symbol ?? '₹' }}) <span class="text-rose-500">*</span></label>
                                <input type="number" step="0.01" name="price" x-model="newPrice" required placeholder="e.g. 35000" class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm font-medium focus:border-amber-500 focus:outline-none ">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tax Rate (%)</label>
                                <input type="number" step="0.01" name="tax_rate" value="0.00" class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-sm focus:border-amber-500 focus:outline-none ">
                            </div>
                        </div>

                        <!-- Live Price Breakdown Banner -->
                        <template x-if="newPrice > 0 && newMaxMembers > 1">
                            <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-emerald-500/20 flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center font-medium">✓</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">
                                        Price per member: <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}<span x-text="Math.round(newPrice / (newMaxMembers || 1)).toLocaleString()"></span> / person</strong>
                                    </span>
                                </div>
                                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider" x-text="newMaxMembers + ' Persons Package'"></span>
                            </div>
                        </template>

                        <div class="pt-1">
                            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="rounded bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-amber-500 focus:ring-0">
                                <span class="text-sm font-semibold">Available for enrollment</span>
                            </label>
                            <p class="text-xs text-slate-500 dark:text-slate-400 ml-5 mt-0.5">Uncheck to save as Paused (will not appear during new member enrollments).</p>
                        </div>

                        <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="showNewModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 text-sm font-medium cursor-pointer">Cancel</button>
                            <button type="submit" class="px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-sm   transition-all cursor-pointer">Create plan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Membership Plan Modal -->
            <div x-show="editPlan !== null" class="fixed inset-0 z-50 overflow-y-auto bg-black/70  flex items-center justify-center p-3 sm:p-4" x-cloak>
                <div class="membership-dialog bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-200 rounded-lg max-w-xl w-full max-h-[92vh] overflow-y-auto p-5 sm:p-7 shadow-2xl" @click.away="editPlan = null">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-semibold text-sm">
                                ✎
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Edit plan</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Update rates, validity, or group capacity</p>
                            </div>
                        </div>
                        <button type="button" aria-label="Close edit plan" @click="editPlan = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-medium cursor-pointer">✕</button>
                    </div>

                    <template x-if="editPlan !== null">
                        <form :action="'/app/membership-plans/' + editPlan.id" method="POST" class="space-y-4">
                            @csrf

                            <!-- Plan Category Selection in Edit Modal -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Plan type <span class="text-rose-500">*</span></label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <label class="p-3 rounded-lg border flex flex-col items-center text-center cursor-pointer transition-all group"
                                           :class="editPlan.plan_type === 'single' ? 'bg-slate-100 dark:bg-slate-800 border-amber-500 text-slate-900 dark:text-white ring-1 ring-amber-500 font-medium' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                        <input type="radio" name="plan_type" value="single" x-model="editPlan.plan_type" :checked="editPlan.plan_type === 'single'" @change="setEditPlanType('single')" class="sr-only">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                             :class="editPlan.plan_type === 'single' ? 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </div>
                                        <span class="text-sm">Single</span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">1 Person</span>
                                    </label>
                                    <label class="p-3 rounded-lg border flex flex-col items-center text-center cursor-pointer transition-all group"
                                           :class="editPlan.plan_type === 'duo' ? 'bg-slate-100 dark:bg-slate-800 border-amber-500 text-slate-900 dark:text-white ring-1 ring-amber-500 font-medium' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                        <input type="radio" name="plan_type" value="duo" x-model="editPlan.plan_type" :checked="editPlan.plan_type === 'duo'" @change="setEditPlanType('duo')" class="sr-only">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                             :class="editPlan.plan_type === 'duo' ? 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        </div>
                                        <span class="text-sm">Duo</span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">2 Persons</span>
                                    </label>
                                    <label class="p-3 rounded-lg border flex flex-col items-center text-center cursor-pointer transition-all group"
                                           :class="editPlan.plan_type === 'family' ? 'bg-slate-100 dark:bg-slate-800 border-amber-500 text-slate-900 dark:text-white ring-1 ring-amber-500 font-medium' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                        <input type="radio" name="plan_type" value="family" x-model="editPlan.plan_type" :checked="editPlan.plan_type === 'family'" @change="setEditPlanType('family')" class="sr-only">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                             :class="editPlan.plan_type === 'family' ? 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </div>
                                        <span class="text-sm">Family</span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">3-4 Persons</span>
                                    </label>
                                    <label class="p-3 rounded-lg border flex flex-col items-center text-center cursor-pointer transition-all group"
                                           :class="editPlan.plan_type === 'group' ? 'bg-slate-100 dark:bg-slate-800 border-amber-500 text-slate-900 dark:text-white ring-1 ring-amber-500 font-medium' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                        <input type="radio" name="plan_type" value="group" x-model="editPlan.plan_type" :checked="editPlan.plan_type === 'group'" @change="setEditPlanType('group')" class="sr-only">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-1 transition-colors"
                                             :class="editPlan.plan_type === 'group' ? 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' : 'bg-slate-200 dark:bg-slate-900 text-slate-500 dark:text-slate-400'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        </div>
                                        <span class="text-sm">Group</span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">5+ Persons</span>
                                    </label>
                                </div>

                                <!-- Custom Capacity Input for Edit -->
                                <template x-if="editPlan.plan_type === 'group' || editPlan.plan_type === 'family'">
                                    <div class="mt-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-800 dark:text-slate-200">Member capacity</label>
                                                <p class="text-xs text-slate-600 dark:text-slate-300">e.g. Set <strong>5</strong> for a 5-people group package</p>
                                            </div>
                                            <div class="w-24">
                                                <input type="number" 
                                                       x-model="editPlan.max_members" 
                                                       min="2" 
                                                       max="50" 
                                                       class="w-full px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-500/40 text-slate-900 dark:text-white font-semibold text-center text-sm focus:border-amber-500 focus:outline-none ">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <input type="hidden" name="max_members" :value="editPlan.plan_type === 'single' ? 1 : (editPlan.plan_type === 'duo' ? 2 : (editPlan.max_members || 1))">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Plan name <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" x-model="editPlan.name" required class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-amber-500 focus:outline-none ">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                                <input type="text" name="description" x-model="editPlan.description" class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-amber-500 focus:outline-none ">
                            </div>

                            <!-- Duration with presets -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Duration <span class="text-rose-500">*</span></label>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="editPlan.duration_value = 1; editPlan.duration_type = 'months'" class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">1M</button>
                                        <button type="button" @click="editPlan.duration_value = 3; editPlan.duration_type = 'months'" class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">3M</button>
                                        <button type="button" @click="editPlan.duration_value = 6; editPlan.duration_type = 'months'" class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">6M</button>
                                        <button type="button" @click="editPlan.duration_value = 1; editPlan.duration_type = 'years'" class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer">1 Year</button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <input type="number" name="duration_value" x-model="editPlan.duration_value" required min="1" class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-sm focus:border-amber-500 focus:outline-none ">
                                    </div>
                                    <div>
                                        <select name="duration_type" x-model="editPlan.duration_type" class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-sm focus:border-amber-500 focus:outline-none ">
                                            <option value="months">Months</option>
                                            <option value="years">Years</option>
                                            <option value="days">Days</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Plan price ({{ auth()->user()->tenant?->currency_symbol ?? '₹' }}) <span class="text-rose-500">*</span></label>
                                    <input type="number" step="0.01" name="price" x-model="editPlan.price" required class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm font-medium focus:border-amber-500 focus:outline-none ">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tax Rate (%)</label>
                                    <input type="number" step="0.01" name="tax_rate" x-model="editPlan.tax_rate" class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-sm focus:border-amber-500 focus:outline-none ">
                                </div>
                            </div>

                            <!-- Live Price Breakdown Banner in Edit -->
                            <template x-if="editPlan.price > 0 && editPlan.max_members > 1">
                                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-emerald-500/20 flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center font-medium">✓</span>
                                        <span class="font-medium text-slate-700 dark:text-slate-300">
                                            Price per member: <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}<span x-text="Math.round(editPlan.price / (editPlan.max_members || 1)).toLocaleString()"></span> / person</strong>
                                        </span>
                                    </div>
                                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider" x-text="editPlan.max_members + ' Persons Package'"></span>
                                </div>
                            </template>

                            <div class="pt-1">
                                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" x-model="editPlan.is_active" class="rounded bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-amber-500 focus:ring-0">
                                    <span class="text-sm font-semibold">Available for enrollment</span>
                                </label>
                                <p class="text-xs text-slate-500 dark:text-slate-400 ml-5 mt-0.5">Uncheck to pause this plan. Paused plans will not appear during member enrollment.</p>
                            </div>

                            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                                <button type="button" @click="editPlan = null" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 text-sm font-medium cursor-pointer">Cancel</button>
                                <button type="submit" class="px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-sm   transition-all cursor-pointer">Save changes</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
