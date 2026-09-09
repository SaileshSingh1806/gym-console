<x-admin-layout header="Subscriptions & Payment Reconciliation">
    <div class="space-y-8" x-data="{ showManualPayModal: false, editSub: null }">
        <!-- Action Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Tenant SaaS Subscriptions</h3>
                <p class="text-xs text-slate-400">Manage plan terms, subscription state overrides, and offline bank transfer payments</p>
            </div>
            <button @click="showManualPayModal = true" class="px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-red-500/10">
                + Record Manual Payment
            </button>
        </div>

        <!-- Subscriptions Table -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 font-semibold">Tenant Gym</th>
                            <th class="py-3.5 px-4 font-semibold">Current Plan</th>
                            <th class="py-3.5 px-4 font-semibold">Billing Cycle</th>
                            <th class="py-3.5 px-4 font-semibold">Status</th>
                            <th class="py-3.5 px-4 font-semibold">Expires / Renews</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($subscriptions as $sub)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="py-3 px-4 font-bold text-white">
                                    {{ $sub->tenant->name }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-amber-400">
                                    {{ $sub->plan->name }}
                                </td>
                                <td class="py-3 px-4 uppercase text-[10px] text-slate-400">
                                    {{ $sub->billing_cycle }}
                                </td>
                                <td class="py-3 px-4">
                                    @php
                                        $badge = match($sub->status) {
                                            'ACTIVE' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'TRIAL' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                            default => 'bg-red-500/10 text-red-400 border-red-500/20',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badge }}">
                                        {{ $sub->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-400">
                                    {{ $sub->ends_at ? $sub->ends_at->format('M d, Y') : ($sub->trial_ends_at ? $sub->trial_ends_at->format('M d, Y') . ' (Trial)' : '—') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button @click="editSub = {{ json_encode([
                                        'id' => $sub->id,
                                        'tenant_name' => $sub->tenant->name,
                                        'plan_id' => $sub->plan_id,
                                        'billing_cycle' => $sub->billing_cycle,
                                        'status' => $sub->status,
                                        'ends_at' => $sub->ends_at?->toDateString(),
                                        'trial_ends_at' => $sub->trial_ends_at?->toDateString(),
                                    ]) }}" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 text-[10px] font-semibold">
                                        Modify Subscription
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">
                                    No tenant subscriptions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subscriptions->hasPages())
                <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                    {{ $subscriptions->links() }}
                </div>
            @endif
        </div>

        <!-- Platform Invoices Table -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 bg-slate-950/40">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Generated SaaS Platform Invoices</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Invoice #</th>
                            <th class="py-3 px-4">Gym Tenant</th>
                            <th class="py-3 px-4">Invoice Date</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($invoices as $inv)
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-3 px-4 font-mono font-bold text-red-400">{{ $inv->invoice_number }}</td>
                                <td class="py-3 px-4 font-bold text-white">{{ $inv->tenant->name }}</td>
                                <td class="py-3 px-4 text-slate-400">{{ $inv->invoice_date->format('M d, Y') }}</td>
                                <td class="py-3 px-4 font-bold text-white">
                                    {{ $inv->currency === 'INR' ? '₹' : ($inv->currency === 'EUR' ? '€' : ($inv->currency === 'GBP' ? '£' : '$')) }}{{ number_format($inv->total, 2) }}
                                    <span class="text-[10px] text-slate-400 font-normal">({{ $inv->currency }})</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        {{ $inv->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-500">No invoices logged.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Subscription Modal -->
        <div x-show="editSub !== null" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl" @click.away="editSub = null">
                <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-white">Modify Subscription</h3>
                    <button @click="editSub = null" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <template x-if="editSub !== null">
                    <form :action="'/admin/subscriptions/' + editSub.id" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <span class="text-xs text-slate-400 block mb-1">Gym: <strong class="text-white" x-text="editSub.tenant_name"></strong></span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Status</label>
                            <select name="status" x-model="editSub.status" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
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
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Plan</label>
                            <select name="plan_id" x-model="editSub.plan_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Billing Cycle</label>
                            <select name="billing_cycle" x-model="editSub.billing_cycle" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Subscription End / Renewal Date</label>
                            <input type="date" name="ends_at" x-model="editSub.ends_at" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                            <button type="button" @click="editSub = null" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 text-white font-bold text-xs">Update Status</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <!-- Record Manual Payment Modal -->
        <div x-show="showManualPayModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl" @click.away="showManualPayModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-white">Record Offline SaaS Payment</h3>
                    <button @click="showManualPayModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form action="{{ route('admin.subscriptions.manual-payment') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Select Gym *</label>
                        <select name="tenant_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            @foreach($gyms as $g)
                                <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->slug }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Select Plan *</label>
                        <select name="plan_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (₹{{ number_format($p->price_monthly, 2) }}/mo)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Amount (₹ / $) *</label>
                            <input type="number" step="0.01" name="amount" required placeholder="1499.00" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Billing Cycle</label>
                            <select name="billing_cycle" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Payment Method</label>
                        <input type="text" name="payment_method" value="Bank Wire / Manual" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Notes</label>
                        <input type="text" name="notes" placeholder="Invoice # or receipt reference" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                        <button type="button" @click="showManualPayModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 text-white font-bold text-xs">Activate Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
