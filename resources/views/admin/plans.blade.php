<x-admin-layout header="SaaS Plans & Feature Access Control">
    <div class="space-y-8" x-data="{ showNewPlanModal: false, showNewFeatureModal: false, editPlan: null }">
        <!-- Top Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Configured Subscription Tiers</h3>
                <p class="text-xs text-slate-400">Control feature gating, quotas, trial duration, and monthly/annual pricing</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="showNewFeatureModal = true" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs">
                    + Add New Feature Code
                </button>
                <button @click="showNewPlanModal = true" class="px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-red-500/10">
                    + Create SaaS Plan
                </button>
            </div>
        </div>

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($plans as $plan)
                <div class="p-6 rounded-3xl bg-slate-900 border {{ $plan->is_popular ? 'border-red-500 shadow-xl shadow-red-500/10' : 'border-slate-800' }} flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-bold text-white text-lg">{{ $plan->name }}</h4>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $plan->slug }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $plan->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-500' }}">
                                {{ $plan->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mb-4 min-h-[36px]">{{ $plan->description }}</p>

                        <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 space-y-1.5 text-xs text-slate-300 mb-4">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Monthly Price:</span>
                                <span class="font-extrabold text-white">₹{{ number_format($plan->price_monthly, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Yearly Price:</span>
                                <span class="font-extrabold text-amber-400">₹{{ number_format($plan->price_yearly, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Free Trial:</span>
                                <span class="font-semibold text-emerald-400">{{ $plan->trial_days }} Days</span>
                            </div>
                            <div class="flex justify-between border-t border-slate-800 pt-1.5">
                                <span class="text-slate-500">Member Limit:</span>
                                <span class="font-bold text-white">{{ $plan->member_limit === -1 ? 'Unlimited' : $plan->member_limit }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Branch Limit:</span>
                                <span class="font-bold text-white">{{ $plan->branch_limit }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Staff Limit:</span>
                                <span class="font-bold text-white">{{ $plan->staff_limit }}</span>
                            </div>
                        </div>

                        <!-- Included Features -->
                        <div class="mb-6">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Enabled Features:</span>
                            <div class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto">
                                @forelse($plan->features as $f)
                                    <span class="px-2 py-0.5 rounded bg-slate-800 text-[10px] text-slate-300 border border-slate-700">
                                        {{ $f->name }}
                                    </span>
                                @empty
                                    <span class="text-[10px] text-slate-500 italic">No features assigned</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-4 border-t border-slate-800">
                        <button @click="editPlan = {{ json_encode([
                            'id' => $plan->id,
                            'name' => $plan->name,
                            'slug' => $plan->slug,
                            'description' => $plan->description,
                            'price_monthly' => $plan->price_monthly,
                            'price_yearly' => $plan->price_yearly,
                            'trial_days' => $plan->trial_days,
                            'member_limit' => $plan->member_limit,
                            'branch_limit' => $plan->branch_limit,
                            'staff_limit' => $plan->staff_limit,
                            'is_active' => (bool)$plan->is_active,
                            'is_popular' => (bool)$plan->is_popular,
                            'sort_order' => $plan->sort_order,
                            'feature_ids' => $plan->features->pluck('id')->toArray(),
                        ]) }}" class="flex-1 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs">
                            Edit Plan
                        </button>
                        <form action="{{ route('admin.plans.delete', $plan->id) }}" method="POST"
                              data-confirm="Are you sure you want to delete the plan '{{ $plan->name }}'?"
                              data-confirm-title="Delete Subscription Plan"
                              data-confirm-btn="Delete Plan"
                              data-confirm-type="danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-xl bg-red-500/10 hover:bg-red-500 hover:text-white text-red-400 text-xs font-bold transition-all">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Edit Plan Modal -->
        <div x-show="editPlan !== null" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl" @click.away="editPlan = null">
                <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-white">Edit SaaS Subscription Plan</h3>
                    <button @click="editPlan = null" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <template x-if="editPlan !== null">
                    <form :action="'/admin/plans/' + editPlan.id" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Plan Name *</label>
                                <input type="text" name="name" x-model="editPlan.name" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Slug *</label>
                                <input type="text" name="slug" x-model="editPlan.slug" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Description</label>
                            <input type="text" name="description" x-model="editPlan.description" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Monthly Price (₹ / $)</label>
                                <input type="number" step="0.01" name="price_monthly" x-model="editPlan.price_monthly" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Yearly Price (₹ / $)</label>
                                <input type="number" step="0.01" name="price_yearly" x-model="editPlan.price_yearly" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Trial Days</label>
                                <input type="number" name="trial_days" x-model="editPlan.trial_days" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Member Limit (-1 unltd)</label>
                                <input type="number" name="member_limit" x-model="editPlan.member_limit" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Branch Limit</label>
                                <input type="number" name="branch_limit" x-model="editPlan.branch_limit" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Staff Limit</label>
                                <input type="number" name="staff_limit" x-model="editPlan.staff_limit" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Feature Checkbox Matrix -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Feature Gating Permissions</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 rounded-2xl bg-slate-950 border border-slate-800 max-h-56 overflow-y-auto">
                                @foreach($features as $f)
                                    <label class="flex items-start gap-2 text-xs text-slate-300 hover:text-white cursor-pointer p-1.5 rounded-lg hover:bg-slate-900/60 border border-transparent hover:border-slate-800 transition-colors">
                                        <input type="checkbox" name="features[]" value="{{ $f->id }}" :checked="editPlan.feature_ids.includes({{ $f->id }})" class="mt-0.5 rounded bg-slate-900 border-slate-700 text-red-500 focus:ring-0 cursor-pointer">
                                        <div class="leading-tight">
                                            <div class="font-bold text-white text-[11px]">{{ $f->name }}</div>
                                            <div class="text-[10px] text-indigo-400/80 font-mono">{{ $f->code }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center gap-6 py-1">
                            <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" x-model="editPlan.is_active" class="rounded bg-slate-900 border-slate-700 text-red-500 focus:ring-0">
                                Active & Available
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                                <input type="checkbox" name="is_popular" value="1" x-model="editPlan.is_popular" class="rounded bg-slate-900 border-slate-700 text-red-500 focus:ring-0">
                                Highlight as "Most Popular"
                            </label>
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                            <button type="button" @click="editPlan = null" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-red-500 text-white font-bold text-xs">Save Plan Changes</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <!-- Create Plan Modal -->
        <div x-show="showNewPlanModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl" @click.away="showNewPlanModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-white">Create New SaaS Plan</h3>
                    <button @click="showNewPlanModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form action="{{ route('admin.plans.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Plan Name *</label>
                            <input type="text" name="name" required placeholder="Growth Plan" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Slug *</label>
                            <input type="text" name="slug" required placeholder="growth" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Description</label>
                        <input type="text" name="description" placeholder="Ideal for multi-floor gyms" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Monthly Price (₹ / $) *</label>
                            <input type="number" step="0.01" name="price_monthly" required placeholder="1499.00" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Yearly Price (₹ / $) *</label>
                            <input type="number" step="0.01" name="price_yearly" required placeholder="14990.00" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Trial Days</label>
                            <input type="number" name="trial_days" value="14" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Member Limit (-1 unltd)</label>
                            <input type="number" name="member_limit" value="500" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Branch Limit</label>
                            <input type="number" name="branch_limit" value="2" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Staff Limit</label>
                            <input type="number" name="staff_limit" value="10" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Select Plan Features</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 rounded-2xl bg-slate-950 border border-slate-800 max-h-56 overflow-y-auto">
                            @foreach($features as $f)
                                <label class="flex items-start gap-2 text-xs text-slate-300 hover:text-white cursor-pointer p-1.5 rounded-lg hover:bg-slate-900/60 border border-transparent hover:border-slate-800 transition-colors">
                                    <input type="checkbox" name="features[]" value="{{ $f->id }}" checked class="mt-0.5 rounded bg-slate-900 border-slate-700 text-red-500 focus:ring-0 cursor-pointer">
                                    <div class="leading-tight">
                                        <div class="font-bold text-white text-[11px]">{{ $f->name }}</div>
                                        <div class="text-[10px] text-indigo-400/80 font-mono">{{ $f->code }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                        <button type="button" @click="showNewPlanModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-red-500 text-white font-bold text-xs">Create Plan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Feature Code Modal -->
        <div x-show="showNewFeatureModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl" @click.away="showNewFeatureModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-white">Create New Feature Code</h3>
                    <button @click="showNewFeatureModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form action="{{ route('admin.features.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Feature Name *</label>
                        <input type="text" name="name" required placeholder="e.g. WhatsApp Automation" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Feature Code (Unique Identifier) *</label>
                        <input type="text" name="code" required placeholder="whatsapp_automation" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Description</label>
                        <input type="text" name="description" placeholder="Automated WhatsApp check-in and payment reminders" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Feature Type</label>
                        <select name="type" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            <option value="boolean">Boolean (Enabled / Disabled)</option>
                            <option value="limit">Limit (Numerical Cap)</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                        <button type="button" @click="showNewFeatureModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 text-white font-bold text-xs">Register Feature</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
