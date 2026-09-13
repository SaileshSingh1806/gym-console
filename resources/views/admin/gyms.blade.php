<x-admin-layout header="Gym Businesses & Tenant Control">
    <div class="space-y-6" x-data="{ showNewModal: false, editGym: null, deleteGymModal: null, getTrialDate(days) { const d = new Date(); d.setDate(d.getDate() + days); return d.toISOString().split('T')[0]; } }">
        <!-- Top Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form action="{{ route('admin.gyms') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-grow max-w-2xl">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search gym name, slug, email..." class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-red-500 focus:outline-none flex-grow">
                <select name="status" class="px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                    <option value="TRIAL" {{ request('status') === 'TRIAL' ? 'selected' : '' }}>Trial</option>
                    <option value="SUSPENDED" {{ request('status') === 'SUSPENDED' ? 'selected' : '' }}>Suspended</option>
                    <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold">
                    Filter
                </button>
            </form>

            <button @click="showNewModal = true" class="px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-red-500/10">
                + Register New Gym
            </button>
        </div>

        <!-- Gyms Table -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 font-semibold">Gym Tenant</th>
                            <th class="py-3.5 px-4 font-semibold">Owner / Email</th>
                            <th class="py-3.5 px-4 font-semibold">SaaS Plan</th>
                            <th class="py-3.5 px-4 font-semibold">Members</th>
                            <th class="py-3.5 px-4 font-semibold">Status</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($gyms as $gym)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="py-3 px-4">
                                    <span class="font-bold text-white text-sm block">{{ $gym->name }}</span>
                                    <span class="text-[10px] text-red-400 font-mono">{{ $gym->slug }} • {{ $gym->branches->count() }} branch(es) • <span class="text-amber-400 font-bold">{{ $gym->currency }} ({{ $gym->currency === 'INR' ? '₹' : ($gym->currency === 'EUR' ? '€' : ($gym->currency === 'GBP' ? '£' : ($gym->currency === 'AED' ? 'د.إ' : '$'))) }})</span></span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-slate-200 font-medium block">{{ $gym->users->where('role', 'gym_owner')->first()?->name ?? 'Owner' }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $gym->email }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-amber-400 block">{{ $gym->activeSubscription->plan->name ?? 'Free Trial' }}</span>
                                    @if($gym->activeSubscription && $gym->status === 'ACTIVE')
                                        <span class="text-[10px] text-emerald-400 font-semibold capitalize">{{ $gym->activeSubscription->billing_cycle ?? 'yearly' }} package</span>
                                    @else
                                        <span class="text-[10px] text-slate-500 capitalize">{{ $gym->activeSubscription->billing_cycle ?? 'monthly' }} cycle</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-bold text-white">
                                    {{ $gym->members_count }}
                                </td>
                                <td class="py-3 px-4">
                                    @php
                                        $badge = match($gym->status) {
                                            'ACTIVE' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'TRIAL' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                            default => 'bg-red-500/10 text-red-400 border-red-500/20',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badge }}">
                                        {{ $gym->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- 1-Click Impersonation -->
                                        <form action="{{ route('admin.gyms.impersonate', $gym->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" title="Login as Gym Owner" class="px-2.5 py-1 rounded bg-amber-500/10 hover:bg-amber-500 hover:text-slate-950 text-amber-400 border border-amber-500/20 text-[10px] font-bold transition-all">
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
                                        ]) }}" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 text-[10px] font-semibold">
                                            Edit
                                        </button>

                                        <!-- Delete Gym Trigger -->
                                        <button type="button" @click="deleteGymModal = { id: {{ $gym->id }}, name: {{ Js::from($gym->name) }}, slug: {{ Js::from($gym->slug) }}, typedName: '' }" class="px-2.5 py-1 rounded bg-red-500/10 hover:bg-red-500 hover:text-white text-red-400 text-[10px] font-semibold transition-all">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">
                                    No gyms found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($gyms->hasPages())
                <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                    {{ $gyms->links() }}
                </div>
            @endif
        </div>

        <!-- Edit Gym Modal -->
        <div x-show="editGym !== null" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-xl w-full p-6 shadow-2xl" @click.away="editGym = null">
                <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-white">Edit Gym Tenant Details</h3>
                    <button @click="editGym = null" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <template x-if="editGym !== null">
                    <form :action="'/admin/gyms/' + editGym.id" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Gym Name *</label>
                                <input type="text" name="name" x-model="editGym.name" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Slug (Identifier) *</label>
                                <input type="text" name="slug" x-model="editGym.slug" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Email</label>
                                <input type="email" name="email" x-model="editGym.email" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Phone</label>
                                <input type="text" name="phone" x-model="editGym.phone" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Status / Mode</label>
                                <select name="status" x-model="editGym.status" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                    <option value="TRIAL">🟡 TRIAL (Free Trial)</option>
                                    <option value="ACTIVE">🟢 ACTIVE (Paid Subscription)</option>
                                    <option value="PAST_DUE">🟠 PAST DUE</option>
                                    <option value="SUSPENDED">🔴 SUSPENDED</option>
                                    <option value="CANCELLED">⚫ CANCELLED</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Assigned Plan</label>
                                <select name="plan_id" x-model="editGym.plan_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                    @foreach($plans as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Billing Cycle</label>
                                <select name="billing_cycle" x-model="editGym.billing_cycle" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                    <option value="yearly">📅 Yearly (Annual)</option>
                                    <option value="monthly">🗓️ Monthly</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Currency</label>
                                <select name="currency" x-model="editGym.currency" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
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
                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-300">Set Custom Trial Expiry (If in Trial mode)</span>
                                <span class="text-[10px] text-slate-500">Overrides plan default</span>
                            </div>
                            <input type="date" name="trial_ends_at" x-model="editGym.trial_ends_at" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                            <button type="button" @click="editGym = null" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold text-xs">Save Changes</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <!-- Add New Gym Modal -->
        <div x-show="showNewModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-xl w-full p-6 shadow-2xl" @click.away="showNewModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-white">Register New Gym Tenant</h3>
                    <button @click="showNewModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form action="{{ route('admin.gyms.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Gym Name *</label>
                            <input type="text" name="gym_name" required placeholder="Olympus Gym" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Primary Branch Name</label>
                            <input type="text" name="branch_name" placeholder="Central Branch" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Owner Name *</label>
                            <input type="text" name="owner_name" required placeholder="Alex Turner" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Owner Email *</label>
                            <input type="email" name="email" required placeholder="alex@olympus.com" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Phone</label>
                            <input type="text" name="phone" placeholder="+1 555-0100" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Owner Password *</label>
                            <input type="password" name="password" required value="password" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">SaaS Plan *</label>
                            <select name="plan_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Billing Cycle *</label>
                            <select name="billing_cycle" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="yearly" selected>📅 Yearly (Annual)</option>
                                <option value="monthly">🗓️ Monthly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Status / Mode</label>
                            <select name="status" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="TRIAL">🟡 TRIAL (Free Trial)</option>
                                <option value="ACTIVE" selected>🟢 ACTIVE (Paid Activation)</option>
                                <option value="SUSPENDED">🔴 SUSPENDED</option>
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

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Trial Ends At (Leave empty for default plan trial)</label>
                        <input type="date" name="trial_ends_at" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                        <button type="button" @click="showNewModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold text-xs">Create Gym Tenant</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- DELETE GYM CONFIRMATION MODAL             -->
        <!-- Requires typing exact gym name to delete   -->
        <!-- ========================================== -->
        <div x-show="deleteGymModal !== null" class="fixed inset-0 z-50 overflow-y-auto bg-black/85 flex items-center justify-center p-4 backdrop-blur-sm" x-cloak>
            <div class="bg-slate-900 border border-red-500/40 rounded-3xl max-w-md w-full p-6 shadow-2xl relative" @click.away="deleteGymModal = null">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-red-500/20 text-red-400 flex items-center justify-center font-bold text-lg shrink-0 border border-red-500/30">
                        ⚠️
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white">Permanently Delete Gym</h3>
                        <p class="text-[11px] text-red-400 font-semibold">Irreversible action & complete data wipe</p>
                    </div>
                </div>

                <template x-if="deleteGymModal !== null">
                    <form :action="'/admin/gyms/' + deleteGymModal.id" method="POST" class="space-y-4">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="confirm_gym_name" :value="deleteGymModal.typedName">

                        <div class="p-3.5 rounded-2xl bg-red-950/40 border border-red-500/20 text-xs text-slate-300 space-y-2">
                            <p class="leading-relaxed">
                                You are about to permanently delete <strong class="text-white" x-text="deleteGymModal.name"></strong> and all its associated data from the database.
                            </p>
                            <ul class="list-disc list-inside text-[11px] text-red-300/90 space-y-0.5 font-medium">
                                <li>All enrolled members & payment receipts</li>
                                <li>Attendance logs, biometric records & access logs</li>
                                <li>Staff accounts, roles & trainer assignments</li>
                                <li>Invoices, expenses, inventory & equipment logs</li>
                            </ul>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">
                                To confirm deletion, type <span class="text-amber-400 font-mono select-all font-black" x-text="deleteGymModal.name"></span> below:
                            </label>
                            <input type="text" 
                                   x-model="deleteGymModal.typedName" 
                                   :placeholder="deleteGymModal.name" 
                                   required 
                                   autofocus 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-bold text-xs focus:border-red-500 focus:outline-none placeholder-slate-600">
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                            <button type="button" @click="deleteGymModal = null" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    :disabled="deleteGymModal.typedName.trim().toLowerCase() !== deleteGymModal.name.trim().toLowerCase() && deleteGymModal.typedName.trim().toLowerCase() !== deleteGymModal.slug.trim().toLowerCase()"
                                    class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs shadow-lg shadow-red-600/20 transition-all flex items-center gap-1.5">
                                <span>🗑️ Permanently Delete Gym</span>
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
</x-admin-layout>
