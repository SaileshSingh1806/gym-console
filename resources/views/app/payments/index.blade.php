<x-app-layout header="Payments">
    @php
        $currency = auth()->user()->tenant?->currency_symbol ?? '₹';
        $tenant = auth()->user()->tenant;
        $logoUrl = $tenant?->logo_url ?? ($platformSettings['logo_url'] ?? null);

        $plansJson = $membershipPlans->mapWithKeys(function ($p) {
            return [$p->id => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'duration_type' => $p->duration_type,
                'duration_value' => (int) $p->duration_value,
                'tax_rate' => (float) $p->tax_rate,
            ]];
        });

        $membersJson = $members->map(function ($m) {
            $activeSub = $m->activeMembership ?? $m->memberships->first();
            return [
                'id' => $m->id,
                'name' => $m->full_name,
                'code' => $m->member_code,
                'phone' => $m->phone,
                'email' => $m->email ?? '',
                'status' => $m->status,
                'active_plan_id' => $activeSub?->membership_plan_id ?? null,
                'active_plan_name' => $activeSub?->plan?->name ?? 'No active plan',
                'active_due' => $activeSub ? max(0, (float) $activeSub->final_amount - (float) $activeSub->paid_amount) : 0,
            ];
        });
    @endphp

    <script>
        window.__PAYMENT_PLANS__ = {{ Js::from($plansJson) }};
        window.__PAYMENT_MEMBERS__ = {{ Js::from($membersJson) }};
    </script>

    <div x-data="{
        showRecordPaymentModal: false,
        showInvoiceModal: false,
        
        // Members & Plans Data
        allMembers: window.__PAYMENT_MEMBERS__ || [],
        plans: window.__PAYMENT_PLANS__ || {},

        // Invoice Preview Modal State
        selectedInvoice: {
            id: null,
            receipt_no: '',
            date: '',
            amount: '0.00',
            method: 'CASH',
            ref: '',
            plan_name: '',
            plan_duration: '',
            member_name: '',
            member_code: '',
            member_phone: '',
            member_email: '',
            total: '0.00',
            paid: '0.00'
        },
        openInvoice(inv) {
            this.selectedInvoice = inv;
            this.showInvoiceModal = true;
        },

        // Record Payment Modal Form State
        memberSearchQuery: '',
        showMemberDropdown: false,
        selectedMember: null,
        selectedMembershipId: null,

        paymentDate: '{{ now()->format('Y-m-d') }}',
        itemType: 'membership',
        selectedPlanId: '',
        startDate: '{{ now()->format('Y-m-d') }}',
        endDate: '',
        
        // PT Fields
        ptPackageName: '',
        ptTrainerId: '',
        ptSessions: 12,
        ptValidityDays: 30,

        // Custom Item
        customItemName: '',

        // Financials
        itemAmount: 0,
        discount: 0,
        tax: 0,
        collectedAmount: 0,
        paymentMethod: 'cash',
        txnReference: '',
        notes: '',

        get filteredMembers() {
            if (!this.memberSearchQuery) return this.allMembers.slice(0, 10);
            let q = this.memberSearchQuery.toLowerCase();
            return this.allMembers.filter(m => 
                m.name.toLowerCase().includes(q) || 
                m.phone.toLowerCase().includes(q) || 
                m.code.toLowerCase().includes(q)
            ).slice(0, 10);
        },

        selectMember(member) {
            this.selectedMember = member;
            this.memberSearchQuery = member.name + ' (' + member.code + ')';
            this.showMemberDropdown = false;
        },

        clearMember() {
            this.selectedMember = null;
            this.selectedMembershipId = null;
            this.memberSearchQuery = '';
        },

        onPlanChange() {
            if (this.selectedPlanId && this.plans[this.selectedPlanId]) {
                let p = this.plans[this.selectedPlanId];
                this.itemAmount = p.price;
                this.calculateEndDate();
                this.collectedAmount = this.computedTotal;
            }
        },

        calculateEndDate() {
            if (!this.startDate) return;
            let d = new Date(this.startDate);
            if (this.itemType === 'membership' && this.selectedPlanId && this.plans[this.selectedPlanId]) {
                let p = this.plans[this.selectedPlanId];
                if (p.duration_type === 'days') {
                    d.setDate(d.getDate() + p.duration_value);
                } else if (p.duration_type === 'years') {
                    d.setFullYear(d.getFullYear() + p.duration_value);
                } else {
                    d.setMonth(d.getMonth() + p.duration_value);
                }
                this.endDate = d.toISOString().split('T')[0];
            } else if (this.itemType === 'pt') {
                d.setDate(d.getDate() + Number(this.ptValidityDays || 30));
                this.endDate = d.toISOString().split('T')[0];
            }
        },

        get computedTotal() {
            return Math.max(0, Number(this.itemAmount || 0) - Number(this.discount || 0) + Number(this.tax || 0));
        },

        openRecordModal() {
            this.selectedMember = null;
            this.selectedMembershipId = null;
            this.memberSearchQuery = '';
            this.itemType = 'membership';
            this.selectedPlanId = '';
            this.startDate = '{{ now()->format('Y-m-d') }}';
            this.endDate = '';
            this.itemAmount = 0;
            this.discount = 0;
            this.tax = 0;
            this.collectedAmount = 0;
            this.paymentMethod = 'cash';
            this.txnReference = '';
            this.notes = '';
            this.showRecordPaymentModal = true;
        },

        openCollectDue(memberId, dueAmount, planName, membershipId = null) {
            let m = this.allMembers.find(x => x.id === memberId);
            if (m) {
                this.selectMember(m);
            } else {
                this.selectedMember = {
                    id: memberId,
                    name: '',
                    code: '',
                    phone: '',
                    status: 'ACTIVE',
                    active_due: dueAmount
                };
            }
            this.itemType = 'due';
            this.selectedMembershipId = membershipId;
            this.customItemName = 'Due Payment: ' + (planName || 'Membership Balance');
            this.itemAmount = dueAmount;
            this.discount = 0;
            this.tax = 0;
            this.collectedAmount = dueAmount;
            this.paymentMethod = 'cash';
            this.txnReference = '';
            this.notes = 'Clearance of pending due amount for ' + (planName || 'membership');
            this.paymentDate = '{{ now()->format('Y-m-d') }}';
            this.showRecordPaymentModal = true;
        }
    }" class="space-y-4">

        <!-- ========================================== -->
        <!-- TOP FILTER BAR (Matches Reference Image 1) -->
        <!-- ========================================== -->
        <form method="GET" action="{{ route('app.payments.index') }}" class="space-y-3">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search Input -->
                <div class="flex-1 min-w-[260px] relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search name, phone, email, ID, receipt..." 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none transition-colors">
                </div>

                <!-- Date Range Dropdown -->
                <div>
                    <select name="date_filter" 
                            onchange="this.form.submit()" 
                            class="px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-200 text-xs font-semibold focus:border-indigo-500 focus:outline-none cursor-pointer">
                        <option value="month_till_date" {{ request('date_filter', 'month_till_date') === 'month_till_date' ? 'selected' : '' }}>Month Till Date</option>
                        <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="last_month" {{ request('date_filter') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ request('date_filter') === 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="all" {{ request('date_filter') === 'all' ? 'selected' : '' }}>All Time</option>
                    </select>
                </div>

                <!-- Methods Dropdown -->
                <div>
                    <select name="method" 
                            onchange="this.form.submit()" 
                            class="px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-200 text-xs font-semibold focus:border-indigo-500 focus:outline-none cursor-pointer">
                        <option value="all" {{ request('method') === 'all' || !request('method') ? 'selected' : '' }}>All Methods</option>
                        <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ request('method') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="upi" {{ request('method') === 'upi' ? 'selected' : '' }}>UPI</option>
                        <option value="netbanking" {{ request('method') === 'netbanking' ? 'selected' : '' }}>Net Banking</option>
                        <option value="cheque" {{ request('method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>

                <!-- Status Dropdown -->
                <div>
                    <select name="status" 
                            onchange="this.form.submit()" 
                            class="px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-200 text-xs font-semibold focus:border-indigo-500 focus:outline-none cursor-pointer">
                        <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Status</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="reversed" {{ request('status') === 'reversed' ? 'selected' : '' }}>Reversed</option>
                    </select>
                </div>

                <!-- Due Dates Dropdown -->
                <div>
                    <select name="due_filter" 
                            onchange="this.form.submit()" 
                            class="px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-200 text-xs font-semibold focus:border-indigo-500 focus:outline-none cursor-pointer">
                        <option value="all" {{ request('due_filter') === 'all' || !request('due_filter') ? 'selected' : '' }}>All Due Dates</option>
                        <option value="has_due" {{ request('due_filter') === 'has_due' ? 'selected' : '' }}>Has Due</option>
                        <option value="no_due" {{ request('due_filter') === 'no_due' ? 'selected' : '' }}>No Due</option>
                    </select>
                </div>

                <!-- Action Button: Record Payment -->
                <button type="button" 
                        @click="openRecordModal()" 
                        class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer whitespace-nowrap ml-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Record Payment</span>
                </button>
            </div>
        </form>

        <!-- ========================================== -->
        <!-- SUMMARY BANNER BAR (Image 1 reference)     -->
        <!-- ========================================== -->
        <div class="px-5 py-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between text-xs shadow-sm">
            <span class="font-bold text-slate-300">
                {{ $payments->total() }} payments
            </span>
            <div class="flex items-center gap-3">
                <span class="font-black text-white">
                    Total: <strong class="text-emerald-400">{{ $currency }}{{ number_format($totalCollected, 2) }}</strong>
                </span>
                <span class="text-slate-600">•</span>
                <span class="font-black {{ $totalDue > 0 ? 'text-rose-400' : 'text-slate-400' }}">
                    Due: <strong>{{ $currency }}{{ number_format($totalDue, 2) }}</strong>
                </span>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- PAYMENTS TABLE (Image 1 reference)         -->
        <!-- ========================================== -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/70 border-b border-slate-800 text-[10px] text-slate-400 font-extrabold uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">MEMBER</th>
                            <th class="py-3.5 px-4">TYPE</th>
                            <th class="py-3.5 px-4">AMOUNT</th>
                            <th class="py-3.5 px-4">METHOD</th>
                            <th class="py-3.5 px-4">DATE</th>
                            <th class="py-3.5 px-4">STATUS</th>
                            <th class="py-3.5 px-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($payments as $p)
                            @php
                                $member = $p->member;
                                $membership = $p->membership;
                                $isReversed = $p->notes && str_contains($p->notes, '[REVERSED]');
                                
                                $dueAmount = 0;
                                $discountAmount = 0;
                                if ($membership) {
                                    $dueAmount = max(0, (float) $membership->final_amount - (float) $membership->paid_amount);
                                    $discountAmount = (float) $membership->discount;
                                }

                                $status = 'Completed';
                                if ($isReversed) {
                                    $status = 'Reversed';
                                } elseif ($dueAmount > 0) {
                                    $status = 'Partial';
                                }

                                $planName = $membership?->plan?->name ?? ($p->notes ? Str::limit($p->notes, 30) : 'Standard Membership');
                                $receiptNo = $p->invoice_number;
                            @endphp
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <!-- MEMBER Column -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-white text-xs">
                                        <a href="{{ route('app.members.show', $member->id) }}" class="hover:text-indigo-400 transition-colors">
                                            {{ $member->full_name }}
                                        </a>
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-400 mt-0.5 flex items-center gap-1.5">
                                        <span>{{ $member->member_code }}</span>
                                        <span>•</span>
                                        <button type="button" 
                                                @click="openInvoice({
                                                    id: {{ $p->id }},
                                                    receipt_no: '{{ $receiptNo }}',
                                                    date: '{{ $p->payment_date ? $p->payment_date->format('d M Y') : $p->created_at->format('d M Y') }}',
                                                    amount: '{{ number_format($p->amount, 2) }}',
                                                    method: '{{ strtoupper($p->payment_method) }}',
                                                    ref: '{{ $p->transaction_reference ?? '' }}',
                                                    plan_name: '{{ $membership?->plan?->name ?? ($p->notes ?? 'Gym Service') }}',
                                                    plan_duration: '{{ $membership?->plan ? ($membership->plan->duration_value . ' ' . $membership->plan->duration_type) : '' }}',
                                                    member_name: '{{ addslashes($member->full_name) }}',
                                                    member_code: '{{ $member->member_code }}',
                                                    member_phone: '{{ $member->phone }}',
                                                    member_email: '{{ $member->email ?? '' }}',
                                                    total: '{{ number_format($membership?->final_amount ?? $p->amount, 2) }}',
                                                    paid: '{{ number_format($p->amount, 2) }}'
                                                })"
                                                class="text-indigo-400 hover:text-indigo-300 font-semibold cursor-pointer underline">
                                            {{ $receiptNo }}
                                        </button>
                                    </div>
                                </td>

                                <!-- TYPE Column -->
                                <td class="py-3.5 px-4">
                                    <span class="font-medium text-slate-200 block text-xs">
                                        {{ $planName }}
                                    </span>
                                </td>

                                <!-- AMOUNT Column -->
                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-xs {{ $isReversed ? 'text-rose-400 line-through' : 'text-emerald-400' }}">
                                        {{ $isReversed ? '-' : '' }}{{ $currency }}{{ number_format($p->amount, 2) }}
                                    </div>
                                    @if($discountAmount > 0)
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            Discount: {{ $currency }}{{ number_format($discountAmount, 2) }}
                                        </div>
                                    @endif
                                    @if($dueAmount > 0)
                                        <div class="text-[10px] text-rose-400 font-bold mt-0.5">
                                            Due: {{ $currency }}{{ number_format($dueAmount, 2) }}
                                        </div>
                                    @endif
                                </td>

                                <!-- METHOD Column -->
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-full bg-slate-800 border border-slate-700 text-slate-300 text-[10px] font-semibold uppercase">
                                        {{ $p->payment_method }}
                                    </span>
                                </td>

                                <!-- DATE Column -->
                                <td class="py-3.5 px-4 font-mono text-slate-300 text-xs">
                                    {{ $p->payment_date ? $p->payment_date->format('d/m/Y') : $p->created_at->format('d/m/Y') }}
                                </td>

                                <!-- STATUS Column -->
                                <td class="py-3.5 px-4">
                                    <div>
                                        @if($status === 'Completed')
                                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 font-bold text-[10px]">
                                                Completed
                                            </span>
                                        @elseif($status === 'Partial')
                                            <span class="px-2.5 py-0.5 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-400 font-bold text-[10px]">
                                                Partial
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-400 font-bold text-[10px]">
                                                Reversed
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- ACTIONS Column (Image 1 reference icons) -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- View / Print Receipt -->
                                        <button type="button" 
                                                @click="openInvoice({
                                                    id: {{ $p->id }},
                                                    receipt_no: '{{ $receiptNo }}',
                                                    date: '{{ $p->payment_date ? $p->payment_date->format('d M Y') : $p->created_at->format('d M Y') }}',
                                                    amount: '{{ number_format($p->amount, 2) }}',
                                                    method: '{{ strtoupper($p->payment_method) }}',
                                                    ref: '{{ $p->transaction_reference ?? '' }}',
                                                    plan_name: '{{ $membership?->plan?->name ?? ($p->notes ?? 'Gym Service') }}',
                                                    plan_duration: '{{ $membership?->plan ? ($membership->plan->duration_value . ' ' . $membership->plan->duration_type) : '' }}',
                                                    member_name: '{{ addslashes($member->full_name) }}',
                                                    member_code: '{{ $member->member_code }}',
                                                    member_phone: '{{ $member->phone }}',
                                                    member_email: '{{ $member->email ?? '' }}',
                                                    total: '{{ number_format($membership?->final_amount ?? $p->amount, 2) }}',
                                                    paid: '{{ number_format($p->amount, 2) }}'
                                                })"
                                                title="View / Print Tax Invoice Receipt" 
                                                class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </button>

                                        <!-- WhatsApp Share -->
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}?text={{ urlencode('Payment Receipt from ' . ($tenant->name ?? 'Gym') . ': ' . $currency . number_format($p->amount, 2) . ' received on ' . ($p->payment_date ? $p->payment_date->format('d/m/Y') : '') . ' (Receipt No: ' . $receiptNo . ')') }}" 
                                           target="_blank" 
                                           title="Send Receipt via WhatsApp" 
                                           class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-emerald-400 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        </a>

                                        <!-- Email Receipt (Direct Send) -->
                                        @if(!empty($member->email))
                                            <form action="{{ route('app.payments.send-email', $p->id) }}" method="POST" class="inline"
                                                  data-confirm="Are you sure you want to directly send the official payment receipt for '{{ $receiptNo }}' to {{ $member->email }}?"
                                                  data-confirm-title="Send Email Receipt"
                                                  data-confirm-btn="Yes, Send Email"
                                                  data-confirm-type="info">
                                                @csrf
                                                <button type="submit" 
                                                        title="Direct Send Email Receipt ({{ $member->email }})" 
                                                        class="p-1.5 rounded-lg bg-slate-800 hover:bg-blue-600/30 text-slate-300 hover:text-blue-400 transition-colors cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" 
                                                    onclick="alert('No email address registered for this member.')" 
                                                    title="No email address registered" 
                                                    class="p-1.5 rounded-lg bg-slate-800/40 text-slate-600 cursor-not-allowed">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            </button>
                                        @endif

                                        <!-- Reverse / Void Button -->
                                        @if(!$isReversed)
                                            <form action="{{ route('app.payments.reverse', $p->id) }}" method="POST" 
                                                  data-confirm="Are you sure you want to reverse / void payment receipt '{{ $receiptNo }}' ({{ $currency }}{{ number_format($p->amount, 2) }})? This will deduct the paid amount from the member's account." 
                                                  data-confirm-title="Reverse / Void Payment" 
                                                  data-confirm-btn="Yes, Reverse Payment" 
                                                  data-confirm-type="warning" 
                                                  class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        title="Reverse / Cancel Payment" 
                                                        class="p-1.5 rounded-lg bg-slate-800 hover:bg-rose-900/50 text-slate-400 hover:text-rose-400 transition-colors cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Collect Due Payment (Only for members who have due) -->
                                        @if($dueAmount > 0 && !$isReversed)
                                            <button type="button" 
                                                    @click="openCollectDue({{ $member->id }}, {{ $dueAmount }}, '{{ addslashes($planName) }}', {{ $membership?->id ?? 'null' }})"
                                                    title="Collect Due Payment (Due: {{ $currency }}{{ number_format($dueAmount, 2) }})" 
                                                    class="p-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border border-emerald-500/40 transition-all cursor-pointer shadow-sm shadow-emerald-500/20">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500">
                                    <div class="text-3xl mb-2">💳</div>
                                    <p class="text-sm font-semibold text-slate-400">No payment receipts found.</p>
                                    <p class="text-xs text-slate-500 mt-1">Click "+ Record Payment" to collect a fee or record membership.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="p-4 border-t border-slate-800 bg-slate-950/50">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL: RECORD PAYMENT / INVOICE (Image 3 exact reference design)          -->
        <!-- ========================================================================= -->
        <div x-show="showRecordPaymentModal" 
             x-transition:enter="ease-out duration-200"
             x-transition:leave="ease-in duration-150"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-md p-3 sm:p-6" 
             style="display: none;">
            
            <div class="min-h-full flex items-center justify-center py-4 sm:py-6">
                <div @click.away="showRecordPaymentModal = false" class="w-full max-w-3xl bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-5 sm:p-8 space-y-6 text-xs relative">
                    
                    <!-- Top-Right Close Button -->
                    <button type="button" 
                            @click="showRecordPaymentModal = false" 
                            class="absolute top-4 sm:top-6 right-4 sm:right-6 p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors z-20 cursor-pointer" 
                            title="Close modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <form action="{{ route('app.payments.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <!-- Header Section: Gym Info & INVOICE -->
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-slate-800/80 pb-5 pr-10">
                            <div>
                                <h2 class="text-base font-black text-white tracking-wide uppercase">{{ $tenant->name ?? 'POWERFIT GYM' }}</h2>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $tenant->address ?? '123 Fitness Street, Health City' }}</p>
                                <p class="text-[11px] text-slate-400">Ph: {{ $tenant->phone ?? '080 6940 9814' }}</p>
                                <p class="text-[11px] font-mono text-slate-500">{{ $tenant->metadata['gstin'] ?? 'GSTIN: 27AAACM3025E1ZZ' }}</p>
                            </div>

                            <div class="text-left sm:text-right">
                                <h1 class="text-2xl font-black text-indigo-400 tracking-wider uppercase">INVOICE</h1>
                                <div class="flex items-center gap-2 mt-2 sm:justify-end">
                                    <span class="text-xs text-slate-400 font-semibold">Date</span>
                                    <input type="date" 
                                           name="payment_date" 
                                           x-model="paymentDate" 
                                           class="px-3 py-1 rounded-lg bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:border-indigo-500 focus:outline-none">
                                </div>
                            </div>
                        </div>

                        <!-- BILL TO Section: Interactive Member Search Dropdown -->
                        <div>
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1.5">BILL TO</span>
                            
                            <!-- Search Member Input with Auto-Suggest -->
                            <div class="relative">
                                <input type="hidden" name="member_id" :value="selectedMember ? selectedMember.id : ''" required>
                                <input type="hidden" name="membership_id" :value="selectedMembershipId">

                                <div class="relative">
                                    <input type="text" 
                                           x-model="memberSearchQuery" 
                                           @focus="showMemberDropdown = true" 
                                           @input="showMemberDropdown = true" 
                                           placeholder="Search member by name, phone, ID..." 
                                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                                    
                                    <button type="button" 
                                            x-show="selectedMember" 
                                            @click="clearMember()" 
                                            class="absolute right-3 top-2.5 text-slate-400 hover:text-white text-xs">
                                        ✕
                                    </button>
                                </div>

                                <!-- Dropdown List -->
                                <div x-show="showMemberDropdown && filteredMembers.length > 0" 
                                     @click.away="showMemberDropdown = false" 
                                     class="absolute z-30 left-0 right-0 mt-1 max-h-52 overflow-y-auto bg-slate-950 border border-slate-800 rounded-xl shadow-2xl divide-y divide-slate-800/60">
                                    <template x-for="m in filteredMembers" :key="m.id">
                                        <div @click="selectMember(m)" class="p-3 hover:bg-slate-900 cursor-pointer flex items-center justify-between transition-colors">
                                            <div>
                                                <span class="font-bold text-white text-xs" x-text="m.name"></span>
                                                <span class="text-slate-400 text-[11px] ml-1" x-text="'(' + m.code + ')'"></span>
                                                <div class="text-[10px] text-slate-500 font-mono" x-text="m.phone"></div>
                                            </div>
                                            <div class="text-right">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" 
                                                      :class="m.status === 'ACTIVE' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-slate-800 text-slate-400'" 
                                                      x-text="m.status"></span>
                                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="m.active_plan_name"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Selected Member Summary Pill -->
                            <template x-if="selectedMember">
                                <div class="mt-2 p-3 rounded-xl bg-slate-950/70 border border-slate-800 flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 font-black text-[10px]">Active</span>
                                        <span class="font-bold text-white text-xs" x-text="selectedMember.name + ' (' + selectedMember.code + ')'"></span>
                                        <span class="text-slate-400 font-mono text-xs" x-text="selectedMember.phone"></span>
                                    </div>
                                    <div x-show="selectedMember.active_due > 0" class="text-rose-400 font-bold text-xs">
                                        Pending Due: {{ $currency }}<span x-text="Number(selectedMember.active_due).toFixed(2)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Item Description Table Grid (Responsive Design) -->
                        <div class="space-y-2">
                            <!-- Table Headers (Visible on tablet/desktop) -->
                            <div class="hidden sm:flex items-center gap-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-800 pb-2 px-1">
                                <div class="w-6 shrink-0">#</div>
                                <div class="w-44 shrink-0">ITEM TYPE</div>
                                <div class="flex-1 min-w-0">DESCRIPTION</div>
                                <div class="w-32 shrink-0 text-right">AMOUNT ({{ $currency }})</div>
                            </div>

                            <!-- Line Item Row (Stacks gracefully on mobile) -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-start gap-3 p-3.5 rounded-xl bg-slate-950/70 border border-slate-800/80">
                                <div class="hidden sm:block w-6 shrink-0 font-bold text-slate-400 pt-2.5">1</div>
                                
                                <!-- ITEM TYPE SELECT -->
                                <div class="w-full sm:w-44 shrink-0">
                                    <label class="block sm:hidden text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Item Type</label>
                                    <select name="item_type" 
                                            x-model="itemType" 
                                            @change="
                                                if (itemType === 'membership') { onPlanChange(); }
                                                else if (itemType === 'pt') { itemAmount = 5000; calculateEndDate(); collectedAmount = computedTotal; }
                                                else if (itemType === 'due') { collectedAmount = computedTotal; }
                                                else { itemAmount = 0; collectedAmount = 0; }
                                            "
                                            class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 font-bold text-white text-xs focus:border-indigo-500 focus:outline-none">
                                        <option value="membership">Membership</option>
                                        <option value="pt">Personal Training</option>
                                        <option value="due">Collect Due Balance</option>
                                        <option value="custom">Service / Custom</option>
                                    </select>
                                </div>

                                <!-- DESCRIPTION COLUMN (Dynamic based on Item Type) -->
                                <div class="flex-1 min-w-0 space-y-2.5">
                                    <label class="block sm:hidden text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Description</label>
                                    
                                    <!-- When Membership selected -->
                                    <template x-if="itemType === 'membership'">
                                        <div class="space-y-2.5">
                                            <div>
                                                <select name="membership_plan_id" 
                                                        x-model="selectedPlanId" 
                                                        @change="onPlanChange()" 
                                                        class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 text-white font-medium text-xs focus:border-indigo-500 focus:outline-none">
                                                    <option value="">— Select Membership —</option>
                                                    @foreach($membershipPlans as $p)
                                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $currency }}{{ number_format($p->price, 2) }} / {{ $p->duration_value }} {{ $p->duration_type }})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="flex items-center gap-3 flex-wrap">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 font-semibold text-xs shrink-0">Start</span>
                                                    <input type="date" 
                                                           name="start_date" 
                                                           x-model="startDate" 
                                                           @change="calculateEndDate()" 
                                                           class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:border-indigo-500 focus:outline-none">
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 font-semibold text-xs shrink-0">End</span>
                                                    <input type="date" 
                                                           name="end_date" 
                                                           x-model="endDate" 
                                                           class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:border-indigo-500 focus:outline-none">
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- When Personal Training selected -->
                                    <template x-if="itemType === 'pt'">
                                        <div class="space-y-2.5">
                                            <div>
                                                <select name="pt_package_name" 
                                                        x-model="ptPackageName" 
                                                        @change="
                                                            if (ptPackageName === '1 Month PT (12 Sessions)') { ptSessions = 12; ptValidityDays = 30; itemAmount = 5000; }
                                                            else if (ptPackageName === '3 Month PT (36 Sessions)') { ptSessions = 36; ptValidityDays = 90; itemAmount = 12000; }
                                                            else if (ptPackageName === '6 Month PT (72 Sessions)') { ptSessions = 72; ptValidityDays = 180; itemAmount = 22000; }
                                                            calculateEndDate();
                                                            collectedAmount = computedTotal;
                                                        "
                                                        class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 text-white font-medium text-xs focus:border-indigo-500 focus:outline-none">
                                                    <option value="">— Select Personal Training —</option>
                                                    <option value="1 Month PT (12 Sessions)">1 Month PT (12 Sessions)</option>
                                                    <option value="3 Month PT (36 Sessions)">3 Month PT (36 Sessions)</option>
                                                    <option value="6 Month PT (72 Sessions)">6 Month PT (72 Sessions)</option>
                                                    <option value="Custom PT Coaching">Custom PT Coaching</option>
                                                </select>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <span class="text-slate-400 font-semibold text-xs shrink-0 w-16">Trainer</span>
                                                <select name="trainer_id" 
                                                        x-model="ptTrainerId" 
                                                        class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                                    <option value="">Select Trainer</option>
                                                    @foreach($trainers as $t)
                                                        <option value="{{ $t->id }}">{{ $t->full_name }} ({{ $t->specialization ?? 'Trainer' }})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 font-semibold text-xs shrink-0 w-16">Sessions</span>
                                                    <input type="number" 
                                                           name="sessions" 
                                                           x-model="ptSessions" 
                                                           min="1" 
                                                           class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-white font-mono text-xs">
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 font-semibold text-xs shrink-0">Validity</span>
                                                    <input type="number" 
                                                           name="validity_days" 
                                                           x-model="ptValidityDays" 
                                                           @input="calculateEndDate()" 
                                                           min="1" 
                                                           class="w-20 px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-white font-mono text-xs">
                                                    <span class="text-slate-400 text-xs">days</span>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-3 pt-0.5 flex-wrap">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 font-semibold text-xs shrink-0 w-16">Start</span>
                                                    <input type="date" 
                                                           name="pt_start_date" 
                                                           x-model="startDate" 
                                                           @change="calculateEndDate()" 
                                                           class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-white font-mono text-xs">
                                                </div>
                                                <span class="text-slate-400 font-mono text-xs">→ Ends: <strong class="text-white" x-text="endDate"></strong></span>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- When Due Clearance selected -->
                                    <template x-if="itemType === 'due'">
                                        <div class="space-y-1.5">
                                            <div class="p-2.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-bold text-emerald-400">Due Clearance</span>
                                                    <span class="text-[11px] font-mono font-bold text-emerald-300">Pending Due: {{ $currency }}<span x-text="Number(itemAmount).toFixed(2)"></span></span>
                                                </div>
                                                <p class="text-[11px] text-slate-300 mt-1" x-text="customItemName"></p>
                                            </div>
                                            <input type="hidden" name="custom_item_name" :value="customItemName">
                                        </div>
                                    </template>

                                    <!-- When Custom / Service selected -->
                                    <template x-if="itemType === 'custom'">
                                        <div>
                                            <input type="text" 
                                                   name="custom_item_name" 
                                                   x-model="customItemName" 
                                                   placeholder="e.g. Locker Rent, Steam Bath, Supplement Protein..." 
                                                   class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                        </div>
                                    </template>
                                </div>

                                <!-- AMOUNT INPUT -->
                                <div class="w-full sm:w-32 shrink-0 text-left sm:text-right">
                                    <label class="block sm:hidden text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Amount ({{ $currency }})</label>
                                    <input type="number" 
                                           step="0.01" 
                                           name="amount" 
                                           x-model="itemAmount" 
                                           @input="collectedAmount = computedTotal" 
                                           placeholder="0.00" 
                                           required 
                                           class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 text-white font-mono font-bold text-xs text-left sm:text-right focus:border-indigo-500 focus:outline-none">
                                </div>
                            </div>

                            <!-- Add Line Item Button (Visual match to reference) -->
                            <div class="pt-1">
                                <button type="button" 
                                        class="px-3.5 py-1.5 rounded-lg border border-dashed border-indigo-500/40 text-indigo-400 hover:text-indigo-300 hover:border-indigo-400 text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Add Line Item</span>
                                </button>
                            </div>
                        </div>

                        <!-- Financial Breakdown Summary (Right Aligned) -->
                        <div class="flex justify-end pt-1">
                            <div class="w-full sm:max-w-xs space-y-2 border-t border-slate-800 pt-3 text-xs">
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Subtotal</span>
                                    <span class="font-bold text-white">{{ $currency }}<span x-text="Number(itemAmount || 0).toFixed(2)"></span></span>
                                </div>

                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Discount</span>
                                    <div class="flex items-center gap-1">
                                        <span>{{ $currency }}</span>
                                        <input type="number" 
                                               step="0.01" 
                                               name="discount" 
                                               x-model="discount" 
                                               @input="collectedAmount = computedTotal" 
                                               class="w-24 px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-white font-mono text-right text-xs focus:border-indigo-500 focus:outline-none">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Tax</span>
                                    <div class="flex items-center gap-1">
                                        <span>{{ $currency }}</span>
                                        <input type="number" 
                                               step="0.01" 
                                               name="tax" 
                                               x-model="tax" 
                                               @input="collectedAmount = computedTotal" 
                                               class="w-24 px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-white font-mono text-right text-xs focus:border-indigo-500 focus:outline-none">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between font-black text-sm text-white pt-2 border-t border-slate-800">
                                    <span>TOTAL</span>
                                    <span class="text-base text-indigo-400 font-bold">{{ $currency }}<span x-text="computedTotal.toFixed(2)"></span></span>
                                </div>
                            </div>
                        </div>

                        <!-- AMOUNT TO COLLECT Box (Matches Image 3) -->
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-3">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">AMOUNT TO COLLECT</span>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 border border-emerald-500/40 text-emerald-400 font-black text-base">
                                    <span>{{ $currency }}</span>
                                    <input type="number" 
                                           step="0.01" 
                                           name="collected_amount" 
                                           x-model="collectedAmount" 
                                           required 
                                           class="w-28 bg-transparent text-emerald-400 font-mono font-black focus:outline-none">
                                </div>
                                <span class="text-xs text-slate-400 font-semibold">of {{ $currency }}<span x-text="computedTotal.toFixed(2)"></span></span>
                            </div>

                            <label class="flex items-center gap-2 text-xs text-slate-300 font-semibold cursor-pointer pt-1">
                                <input type="checkbox" name="activate_now" value="1" checked class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-0">
                                <span><strong>👤+ Activate membership now:</strong> Member can start using the gym immediately despite pending dues</span>
                            </label>
                        </div>

                        <!-- PAYMENT METHOD (Responsive Layout) -->
                        <div class="space-y-2">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">PAYMENT METHOD</span>
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                                <div class="sm:col-span-4">
                                    <select name="payment_method" 
                                            x-model="paymentMethod" 
                                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-bold text-xs focus:border-indigo-500 focus:outline-none">
                                        <option value="cash">Cash</option>
                                        <option value="upi">UPI / QR Code</option>
                                        <option value="card">Card / POS Machine</option>
                                        <option value="netbanking">Net Banking</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-3">
                                    <input type="number" 
                                           step="0.01" 
                                           x-model="collectedAmount" 
                                           placeholder="0" 
                                           class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:border-indigo-500 focus:outline-none">
                                </div>

                                <div class="sm:col-span-4">
                                    <input type="text" 
                                           name="transaction_reference" 
                                           x-model="txnReference" 
                                           placeholder="Ref / Txn ID (Optional)" 
                                           class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-600 text-xs font-mono focus:border-indigo-500 focus:outline-none">
                                </div>

                                <div class="sm:col-span-1">
                                    <button type="button" 
                                            class="w-full h-full py-2 px-2 rounded-xl border border-dashed border-indigo-500/40 text-indigo-400 text-xs font-bold whitespace-nowrap hover:bg-slate-800 transition-colors flex items-center justify-center cursor-pointer">
                                        + Split
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Textarea -->
                        <div>
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Notes</span>
                            <textarea name="notes" 
                                      x-model="notes" 
                                      rows="2" 
                                      placeholder="Additional notes..." 
                                      class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-600 text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                        </div>

                        <!-- Footer Actions (Responsive) -->
                        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between border-t border-slate-800 pt-4 gap-3">
                            <p class="text-xs text-slate-400 italic text-center sm:text-left">Thank you for your membership!</p>
                            <div class="flex items-center gap-3 justify-end">
                                <button type="button" 
                                        @click="showRecordPaymentModal = false" 
                                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition-colors cursor-pointer text-center">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    <span>Record Payment</span>
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL: TAX INVOICE & FEE RECEIPT PREVIEW (A4 Layout with Watermark)       -->
        <!-- ========================================================================= -->
        <div x-show="showInvoiceModal" 
             x-transition:enter="ease-out duration-200"
             x-transition:leave="ease-in duration-150"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-md p-4 sm:p-6" 
             style="display: none;">
            
            <div class="min-h-full flex items-center justify-center py-6">
                <div @click.away="showInvoiceModal = false" class="w-full max-w-2xl space-y-3">
                    
                    <!-- Modal Header Toolbar -->
                    <div class="flex items-center justify-between px-5 py-3 bg-slate-900 border border-slate-800 rounded-2xl shadow-xl">
                        <div class="flex items-center gap-2 text-white font-black text-xs">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Invoice Preview</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <a :href="'/app/invoices/' + selectedInvoice.id" 
                               target="_blank" 
                               title="Open in new tab"
                               class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <button type="button" 
                                    @click="showInvoiceModal = false" 
                                    title="Close"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Action Bar Toolbar (Close, Print, Send Email, Download PDF) -->
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" 
                                @click="showInvoiceModal = false" 
                                class="px-5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs shadow-md transition-all cursor-pointer">
                            Close
                        </button>

                        <button type="button" 
                                onclick="window.print()" 
                                class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Print</span>
                        </button>

                        <form :action="'/app/payments/' + selectedInvoice.id + '/send-email'" method="POST" class="inline"
                              data-confirm="Send official payment receipt email directly to this member?"
                              data-confirm-title="Send Email Receipt"
                              data-confirm-btn="Yes, Send Email"
                              data-confirm-type="info">
                            @csrf
                            <button type="submit" 
                                    class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-black text-xs flex items-center gap-1.5 shadow-lg shadow-blue-600/30 transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>Send Email</span>
                            </button>
                        </form>

                        <a :href="'/app/invoices/' + selectedInvoice.id" 
                           target="_blank" 
                           class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/30 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download PDF</span>
                        </a>
                    </div>

                    <!-- Printable Invoice Sheet (A4 Proportion) -->
                    <div class="relative w-full min-h-[820px] bg-white text-slate-900 rounded-2xl shadow-2xl p-8 sm:p-12 overflow-hidden border border-slate-200 text-xs flex flex-col justify-between">
                        
                        <!-- Faded Background Logo Watermark -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.06] select-none">
                            @if(!empty($logoUrl))
                                <img src="{{ $logoUrl }}" alt="" class="w-72 h-72 object-contain grayscale">
                            @else
                                <div class="text-center font-black tracking-widest text-7xl uppercase text-slate-900 rotate-[-15deg]">
                                    {{ $tenant->name ?? 'GYM CONSOLE' }}
                                </div>
                            @endif
                        </div>

                        <div class="relative z-10 space-y-8">
                            
                            <!-- Header Section: Logo, Gym Info & INVOICE Meta -->
                            <div class="flex items-start justify-between border-b border-slate-200 pb-6">
                                <div class="flex items-center gap-3.5">
                                    @if(!empty($logoUrl))
                                        <img src="{{ $logoUrl }}" alt="{{ $tenant->name ?? 'Gym Logo' }}" class="h-16 w-auto max-w-[140px] object-contain shrink-0">
                                    @else
                                        <div class="w-14 h-14 rounded-2xl bg-slate-950 text-amber-400 flex items-center justify-center font-black text-2xl shadow-md shrink-0">
                                            🏋️
                                        </div>
                                    @endif
                                    <div>
                                        <h2 class="text-xl font-black text-slate-950 tracking-tight">{{ $tenant->name ?? 'PowerFit Gym' }}</h2>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $tenant->address ?? '123 Fitness Street, Health City' }}</p>
                                        @if(!empty($tenant->phone) || !empty($tenant->email))
                                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $tenant->phone ?? '' }} @if(!empty($tenant->phone) && !empty($tenant->email)) • @endif {{ $tenant->email ?? '' }}</p>
                                        @endif
                                        @if(!empty($tenant->gst_number))
                                            <p class="text-[10px] font-mono text-slate-500 mt-0.5 font-bold">GSTIN: {{ $tenant->gst_number }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right">
                                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">INVOICE</h1>
                                    <p class="text-xs font-mono font-bold text-slate-600 mt-1" x-text="selectedInvoice.receipt_no"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="selectedInvoice.date"></p>
                                </div>
                            </div>

                            <!-- Bill To & Plan Details (2 Columns) -->
                            <div class="grid grid-cols-2 gap-6 border-b border-slate-200 pb-6">
                                <!-- Bill To Column -->
                                <div>
                                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">BILL TO</span>
                                    <h3 class="text-sm font-black text-slate-950" x-text="selectedInvoice.member_name"></h3>
                                    <p class="text-xs font-mono font-semibold text-slate-500 mt-0.5" x-text="selectedInvoice.member_code"></p>
                                    <p class="text-xs text-slate-600 mt-0.5" x-text="selectedInvoice.member_phone"></p>
                                    <p class="text-xs text-slate-500 mt-0.5" x-show="selectedInvoice.member_email" x-text="selectedInvoice.member_email"></p>
                                </div>

                                <!-- Plan Details Column -->
                                <div>
                                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">PLAN DETAILS</span>
                                    <h3 class="text-sm font-black text-slate-950" x-text="selectedInvoice.plan_name"></h3>
                                    <p class="text-xs text-slate-600 mt-0.5" x-show="selectedInvoice.plan_duration" x-text="selectedInvoice.plan_duration"></p>
                                </div>
                            </div>

                            <!-- Items & Amount Table -->
                            <div>
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="border-b-2 border-slate-200 text-slate-500 font-extrabold text-[10px] uppercase tracking-wider">
                                            <th class="py-3">DESCRIPTION</th>
                                            <th class="py-3 text-right">AMOUNT</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr>
                                            <td class="py-4">
                                                <span class="font-bold text-slate-900 block text-sm" x-text="selectedInvoice.plan_name"></span>
                                                <span class="text-slate-500 text-[11px] block mt-0.5" x-show="selectedInvoice.plan_duration" x-text="selectedInvoice.plan_duration"></span>
                                                <span class="text-slate-400 text-[10px] block mt-0.5">
                                                    Payment Method: <span class="font-semibold text-slate-600 uppercase" x-text="selectedInvoice.method"></span>
                                                    <span x-show="selectedInvoice.ref" x-text="' — Ref: ' + selectedInvoice.ref"></span>
                                                </span>
                                            </td>
                                            <td class="py-4 text-right font-black text-slate-900 text-sm">
                                                {{ $currency }}<span x-text="selectedInvoice.amount"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="border-t-2 border-slate-200 text-xs">
                                        <tr>
                                            <td class="py-3 font-bold text-slate-600">Total</td>
                                            <td class="py-3 text-right font-black text-slate-900 text-sm">
                                                {{ $currency }}<span x-text="selectedInvoice.total || selectedInvoice.amount"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 font-bold text-emerald-700">Paid</td>
                                            <td class="py-2 text-right font-black text-emerald-600 text-base">
                                                {{ $currency }}<span x-text="selectedInvoice.amount"></span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                        </div>

                        <!-- Bottom Note / Thank You Footer -->
                        <div class="relative z-10 pt-8 border-t border-slate-100 text-center mt-auto">
                            <p class="text-xs font-semibold text-slate-500">Thank you for your membership!</p>
                            <p class="text-[10px] text-slate-400 mt-1">Generated by {{ $tenant->name ?? 'Gym Console' }} — All rights reserved.</p>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>
