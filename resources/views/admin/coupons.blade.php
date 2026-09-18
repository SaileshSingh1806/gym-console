<x-admin-layout header="SaaS Promo Coupons &amp; Discount Codes">
    <div class="space-y-6" x-data="{
        showCreateModal: false,
        showEditModal: false,
        editCoupon: {
            id: null,
            code: '',
            name: '',
            description: '',
            discount_type: 'percentage',
            discount_value: '',
            plan_id: '',
            min_amount: '0.00',
            max_discount_amount: '',
            usage_limit: '',
            starts_at: '',
            expires_at: '',
            is_active: true
        },
        openEdit(coupon) {
            this.editCoupon = {
                id: coupon.id,
                code: coupon.code,
                name: coupon.name,
                description: coupon.description || '',
                discount_type: coupon.discount_type,
                discount_value: coupon.discount_value,
                plan_id: coupon.plan_id || '',
                min_amount: coupon.min_amount || '0.00',
                max_discount_amount: coupon.max_discount_amount || '',
                usage_limit: coupon.usage_limit || '',
                starts_at: coupon.starts_at ? coupon.starts_at.substring(0, 10) : '',
                expires_at: coupon.expires_at ? coupon.expires_at.substring(0, 10) : '',
                is_active: Boolean(coupon.is_active)
            };
            this.showEditModal = true;
        }
    }">

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm transition-colors">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">Total Promo Coupons</span>
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ $stats['total_coupons'] }}</span>
                <span class="text-xs text-slate-400 block mt-1">Configured in system</span>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm transition-colors">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">Active Coupons</span>
                <span class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $stats['active_coupons'] }}</span>
                <span class="text-xs text-slate-400 block mt-1">Currently redeemable</span>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm transition-colors">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">Total Redemptions</span>
                <span class="text-3xl font-extrabold text-amber-600 dark:text-amber-400">{{ $stats['total_used'] }}</span>
                <span class="text-xs text-slate-400 block mt-1">Times used across gyms</span>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm transition-colors">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">Discounts Provided</span>
                <span class="text-3xl font-extrabold text-rose-600 dark:text-rose-400">₹{{ number_format($stats['total_discount_given'], 2) }}</span>
                <span class="text-xs text-slate-400 block mt-1">Total customer savings</span>
            </div>
        </div>

        <!-- Controls & Header Bar -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4 transition-colors">
            <form action="{{ route('admin.coupons') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 sm:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search code or offer name..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-red-500 focus:outline-none">
                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:border-red-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Disabled Only</option>
                </select>

                @if(request('search') || request('status'))
                    <a href="{{ route('admin.coupons') }}" class="text-xs text-slate-500 dark:text-slate-400 hover:text-red-500 underline">Clear</a>
                @endif
            </form>

            <button @click="showCreateModal = true" class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-xs hover:brightness-110 shadow-lg shadow-red-600/20 flex items-center justify-center gap-2 cursor-pointer transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Create Promo Coupon</span>
            </button>
        </div>

        <!-- Coupons Table -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm dark:shadow-xl transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 font-semibold">Coupon Code &amp; Details</th>
                            <th class="py-3.5 px-4 font-semibold">Discount</th>
                            <th class="py-3.5 px-4 font-semibold">Applicable Plan</th>
                            <th class="py-3.5 px-4 font-semibold">Usage / Limit</th>
                            <th class="py-3.5 px-4 font-semibold">Validity</th>
                            <th class="py-3.5 px-4 font-semibold">Status</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @forelse($coupons as $coupon)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <span class="px-2.5 py-1 rounded-lg font-mono font-extrabold text-xs bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/30">
                                            {{ $coupon->code }}
                                        </span>
                                        <div>
                                            <span class="font-bold text-slate-900 dark:text-white block">{{ $coupon->name }}</span>
                                            @if($coupon->description)
                                                <span class="text-[10px] text-slate-400 block truncate max-w-xs">{{ $coupon->description }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                    @if($coupon->discount_type === 'percentage')
                                        <span class="text-emerald-600 dark:text-emerald-400">{{ number_format($coupon->discount_value, 0) }}% OFF</span>
                                        @if($coupon->max_discount_amount)
                                            <span class="text-[10px] text-slate-400 block">Up to ₹{{ number_format($coupon->max_discount_amount, 0) }}</span>
                                        @endif
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400">₹{{ number_format($coupon->discount_value, 2) }} Flat</span>
                                    @endif

                                    @if($coupon->min_amount > 0)
                                        <span class="text-[9px] text-slate-400 block">Min: ₹{{ number_format($coupon->min_amount, 0) }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($coupon->plan)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            {{ $coupon->plan->name }} Only
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            All SaaS Plans
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $coupon->used_count }}</span>
                                    <span class="text-slate-400">/ {{ $coupon->usage_limit ? $coupon->usage_limit : '∞ Unlimited' }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($coupon->expires_at)
                                        <span class="text-xs {{ $coupon->expires_at->isPast() ? 'text-red-500 font-bold' : 'text-slate-600 dark:text-slate-300' }}">
                                            {{ $coupon->expires_at->format('M d, Y') }}
                                        </span>
                                        @if($coupon->expires_at->isPast())
                                            <span class="text-[9px] text-red-500 block font-bold">Expired</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">No Expiration</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <form action="{{ route('admin.coupons.toggle', $coupon->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" title="Click to toggle status" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border transition-colors cursor-pointer {{ $coupon->is_active ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-slate-300 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                                            {{ $coupon->is_active ? '🟢 Active' : '⚪ Disabled' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click='openEdit(@json($coupon))' class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:text-white dark:hover:bg-slate-700 cursor-pointer" title="Edit Coupon">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <form action="{{ route('admin.coupons.delete', $coupon->id) }}" method="POST"
                                              data-confirm="Are you sure you want to delete coupon code '{{ $coupon->code }}'?"
                                              data-confirm-title="Delete Coupon"
                                              data-confirm-btn="Delete Coupon"
                                              data-confirm-type="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-500 hover:text-red-600 dark:bg-slate-800 dark:text-slate-400 dark:hover:text-red-400 dark:hover:bg-slate-700 cursor-pointer" title="Delete Coupon">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                    <svg class="w-12 h-12 mx-auto text-slate-400 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    <p class="font-medium text-slate-600 dark:text-slate-400">No promo coupons configured yet.</p>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Create your first coupon discount to attract new gym subscriptions!</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($coupons->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                    {{ $coupons->links() }}
                </div>
            @endif
        </div>

        <!-- Create Coupon Modal -->
        <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4 backdrop-blur-sm" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl transition-colors" @click.away="showCreateModal = false">
                <div class="flex justify-between items-center mb-6 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Create New SaaS Promo Coupon</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Add a discount code for Gym Owners when purchasing SaaS plans</p>
                    </div>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">✕</button>
                </div>

                <form action="{{ route('admin.coupons.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Coupon Code *</label>
                            <input type="text" name="code" placeholder="e.g. WELCOME50, FESTIVE20" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-mono uppercase focus:border-red-500 focus:outline-none">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-0.5">Uppercase letters &amp; numbers only</span>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Offer Title / Name *</label>
                            <input type="text" name="name" placeholder="e.g. New Gym Welcome 50% Off" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Discount Type *</label>
                            <select name="discount_type" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                                <option value="percentage">Percentage (%) Discount</option>
                                <option value="fixed">Fixed Amount (₹) Flat</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Discount Value *</label>
                            <input type="number" step="0.01" min="0.01" name="discount_value" placeholder="e.g. 20 (for 20%) or 500" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Applicable SaaS Plan</label>
                            <select name="plan_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                                <option value="">All SaaS Plans</option>
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} Only</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Min Order Amount (₹)</label>
                            <input type="number" step="0.01" min="0" name="min_amount" value="0.00" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Max Discount Cap (₹)</label>
                            <input type="number" step="0.01" min="0" name="max_discount_amount" placeholder="e.g. 1000 (Optional for %)" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Total Usage Limit</label>
                            <input type="number" min="1" name="usage_limit" placeholder="e.g. 100 (Blank = Unlimited)" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Valid From (Optional)</label>
                            <input type="date" name="starts_at" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Expiry Date (Optional)</label>
                            <input type="date" name="expires_at" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Description / Internal Notes</label>
                        <textarea name="description" rows="2" placeholder="e.g. Limited festival discount for new gym owners." class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="create_is_active" value="1" checked class="rounded text-red-600 focus:ring-0">
                        <label for="create_is_active" class="font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">Active &amp; immediately redeemable</label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 font-semibold cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold shadow-lg shadow-red-600/20 cursor-pointer">Save &amp; Create Coupon</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Coupon Modal -->
        <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4 backdrop-blur-sm" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl transition-colors" @click.away="showEditModal = false">
                <div class="flex justify-between items-center mb-6 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Promo Coupon: <span class="font-mono text-red-600 dark:text-red-400" x-text="editCoupon.code"></span></h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Update discount terms or validity</p>
                    </div>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">✕</button>
                </div>

                <form :action="'{{ url('admin/coupons') }}/' + editCoupon.id" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Coupon Code *</label>
                            <input type="text" name="code" x-model="editCoupon.code" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-mono uppercase focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Offer Title / Name *</label>
                            <input type="text" name="name" x-model="editCoupon.name" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Discount Type *</label>
                            <select name="discount_type" x-model="editCoupon.discount_type" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                                <option value="percentage">Percentage (%) Discount</option>
                                <option value="fixed">Fixed Amount (₹) Flat</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Discount Value *</label>
                            <input type="number" step="0.01" min="0.01" name="discount_value" x-model="editCoupon.discount_value" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Applicable SaaS Plan</label>
                            <select name="plan_id" x-model="editCoupon.plan_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                                <option value="">All SaaS Plans</option>
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} Only</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Min Order Amount (₹)</label>
                            <input type="number" step="0.01" min="0" name="min_amount" x-model="editCoupon.min_amount" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Max Discount Cap (₹)</label>
                            <input type="number" step="0.01" min="0" name="max_discount_amount" x-model="editCoupon.max_discount_amount" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Total Usage Limit</label>
                            <input type="number" min="1" name="usage_limit" x-model="editCoupon.usage_limit" placeholder="Blank = Unlimited" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Valid From</label>
                            <input type="date" name="starts_at" x-model="editCoupon.starts_at" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Expiry Date</label>
                            <input type="date" name="expires_at" x-model="editCoupon.expires_at" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                        <textarea name="description" x-model="editCoupon.description" rows="2" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-red-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" :checked="editCoupon.is_active" class="rounded text-red-600 focus:ring-0">
                        <label for="edit_is_active" class="font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">Active &amp; redeemable</label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 font-semibold cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold shadow-lg shadow-red-600/20 cursor-pointer">Update Coupon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
