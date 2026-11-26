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
        getInvoiceModeText(inv) {
            if (!inv) return 'Cash';
            const notes = inv.notes || '';
            const isSplit = (inv.method && inv.method.toLowerCase() === 'split') || notes.includes('Split Payment') || notes.includes('Split:');
            if (!isSplit) {
                const m = (inv.method || 'Cash').toUpperCase();
                return m === 'UPI' ? 'UPI' : (m === 'CASH' ? 'Cash' : (m === 'CARD' ? 'Card' : (m === 'CHEQUE' ? 'Cheque' : (m === 'BANK_TRANSFER' ? 'Bank Transfer' : inv.method))));
            }
            let raw = notes;
            if (raw.includes('Split Payment:')) raw = raw.split('Split Payment:').pop();
            else if (raw.includes('Split:')) raw = raw.split('Split:').pop();
            if (raw.includes('|')) raw = raw.split('|')[0];
            const parts = raw.split('+');
            const methods = [];
            parts.forEach(p => {
                p = p.trim();
                const match = p.match(/^([A-Za-z\s_]+)/);
                if (match) {
                    let m = match[1].trim().toUpperCase();
                    methods.push(m === 'UPI' ? 'UPI' : (m === 'CASH' ? 'Cash' : (m === 'CARD' ? 'Card' : (m === 'CHEQUE' ? 'Cheque' : (m === 'BANK_TRANSFER' ? 'Bank Transfer' : match[1].trim())))));
                }
            });
            return methods.length >= 2 ? (methods[0] + ' & ' + methods[1]) : (methods[0] || 'Cash & UPI');
        },
        getInvoiceSplitItems(inv) {
            if (!inv) return [];
            const notes = inv.notes || '';
            const isSplit = (inv.method && inv.method.toLowerCase() === 'split') || notes.includes('Split Payment') || notes.includes('Split:');
            if (!isSplit) return [];
            let raw = notes;
            if (raw.includes('Split Payment:')) raw = raw.split('Split Payment:').pop();
            else if (raw.includes('Split:')) raw = raw.split('Split:').pop();
            if (raw.includes('|')) raw = raw.split('|')[0];
            const parts = raw.split('+');
            const items = [];
            parts.forEach(p => {
                p = p.trim();
                if (!p) return;
                const m = p.match(/^([A-Za-z\s_]+?)\s*([^\d\s]*\s*[\d,]+(?:\.\d+)?)\s*(\(.*?\))?$/);
                if (m) {
                    let name = m[1].trim().toUpperCase();
                    name = name === 'UPI' ? 'UPI' : (name === 'CASH' ? 'Cash' : (name === 'CARD' ? 'Card' : (name === 'CHEQUE' ? 'Cheque' : (name === 'BANK_TRANSFER' ? 'Bank Transfer' : m[1].trim()))));
                    items.push({ method: name, amount: m[2].trim(), ref: (m[3] || '').trim() });
                }
            });
            return items;
        },

        // Edit Payment Method Modal (Gym Owner Only)
        showEditMethodModal: false,
        editPaymentId: null,
        editPaymentInvoice: '',
        editPaymentAmount: '',
        editPaymentMember: '',
        editPaymentMethod: 'cash',
        editPaymentRef: '',
        editPaymentReason: '',
        openEditMethod(id, invoice, amount, member, currentMethod, reference) {
            this.editPaymentId = id;
            this.editPaymentInvoice = invoice || '';
            this.editPaymentAmount = amount || '0.00';
            this.editPaymentMember = member || 'N/A';
            this.editPaymentMethod = currentMethod || 'cash';
            this.editPaymentRef = reference || '';
            this.editPaymentReason = '';
            this.showEditMethodModal = true;
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

        // Split Payment State
        isSplitPayment: false,
        splitMethod1: 'cash',
        splitAmount1: '',
        splitRef1: '',
        splitMethod2: 'upi',
        splitAmount2: '',
        splitRef2: '',

        toggleSplitPayment() {
            this.isSplitPayment = !this.isSplitPayment;
            if (this.isSplitPayment) {
                const total = Number(this.collectedAmount || this.computedTotal || 0);
                const half = Math.round((total / 2) * 100) / 100;
                this.splitAmount1 = half > 0 ? half : '';
                this.splitAmount2 = (total - half) > 0 ? (total - half) : '';
                this.splitMethod1 = this.splitMethod1 || 'cash';
                this.splitMethod2 = this.splitMethod2 || 'upi';
                this.updateSplitFromFirst();
            } else {
                this.collectedAmount = this.computedTotal;
            }
        },

        updateSplitFromFirst() {
            const total = Number(this.computedTotal || this.collectedAmount || 0);
            const a1 = Number(this.splitAmount1 || 0);
            if (total > 0 && a1 >= 0) {
                const rem = Math.max(0, Math.round((total - a1) * 100) / 100);
                this.splitAmount2 = rem > 0 ? rem : 0;
            }
            this.syncSplitTotal();
        },

        updateSplitFromSecond() {
            this.syncSplitTotal();
        },

        syncSplitTotal() {
            const a1 = Number(this.splitAmount1 || 0);
            const a2 = Number(this.splitAmount2 || 0);
            this.collectedAmount = Math.round((a1 + a2) * 100) / 100;
        },

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
        <!-- TOP FILTER BAR                             -->
        <!-- ========================================== -->
        <form method="GET" action="{{ route('app.payments.index') }}" class="space-y-3">
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 sm:gap-3">
                <!-- Search Input -->
                <div class="col-span-2 sm:flex-1 sm:min-w-[240px] relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-fg-muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search name, phone, email, ID, receipt..." 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none transition-colors">
                </div>

                <!-- Date Range Dropdown -->
                <div>
                    <select name="date_filter" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg-muted dark:text-fg text-xs font-semibold focus:border-accent focus:outline-none cursor-pointer">
                        <option value="all" {{ request('date_filter', 'all') === 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="month_till_date" {{ request('date_filter') === 'month_till_date' ? 'selected' : '' }}>Month Till Date</option>
                        <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="last_month" {{ request('date_filter') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ request('date_filter') === 'this_year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>

                <!-- Methods Dropdown -->
                <div>
                    <select name="method" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg-muted dark:text-fg text-xs font-semibold focus:border-accent focus:outline-none cursor-pointer">
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
                            class="w-full px-3 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg-muted dark:text-fg text-xs font-semibold focus:border-accent focus:outline-none cursor-pointer">
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
                            class="w-full px-3 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg-muted dark:text-fg text-xs font-semibold focus:border-accent focus:outline-none cursor-pointer">
                        <option value="all" {{ request('due_filter') === 'all' || !request('due_filter') ? 'selected' : '' }}>All Due Dates</option>
                        <option value="has_due" {{ request('due_filter') === 'has_due' ? 'selected' : '' }}>Has Due</option>
                        <option value="no_due" {{ request('due_filter') === 'no_due' ? 'selected' : '' }}>No Due</option>
                    </select>
                </div>

                <!-- Action Button: Record Payment -->
                <button type="button" 
                        @click="openRecordModal()" 
                        class="col-span-2 sm:col-span-1 w-full sm:w-auto px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer whitespace-nowrap sm:ml-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Record Payment</span>
                </button>
            </div>
        </form>

        <!-- ========================================== -->
        <!-- SUMMARY BANNER BAR                         -->
        <!-- ========================================== -->
        <div class="px-4 sm:px-5 py-3 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs">
            <span class="font-semibold text-fg-muted dark:text-fg-muted">
                {{ $payments->total() }} payments recorded
            </span>
            <div class="flex items-center gap-3">
                <span class="font-semibold text-fg dark:text-white">
                    Total: <strong class="text-emerald-600 dark:text-emerald-400">{{ $currency }}{{ number_format($totalCollected, 2) }}</strong>
                </span>
                <span class="text-fg-muted dark:text-fg-muted">•</span>
                <span class="font-black {{ $totalDue > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400' }}">
                    Due: <strong>{{ $currency }}{{ number_format($totalDue, 2) }}</strong>
                </span>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- PAYMENTS: MOBILE CARDS & DESKTOP TABLE     -->
        <!-- ========================================== -->
        <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
            
            <!-- Mobile Cards View (block md:hidden) -->
            <div class="block md:hidden divide-y divide-line dark:divide-line">
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

                        $isConversionNote = $p->notes && (str_contains(strtolower($p->notes), 'crm') || str_contains(strtolower($p->notes), 'conversion') || str_contains(strtolower($p->notes), 'lead'));
                        $planName = $membership?->plan?->name ?? (($p->notes && ! $isConversionNote) ? Str::limit($p->notes, 30) : 'Standard Membership');
                        $receiptNo = $p->invoice_number;
                    @endphp
                    <div class="p-4 space-y-3">
                        <!-- Top Row: Member Info + Status Badge -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <a href="{{ route('app.members.show', $member->id) }}" class="font-semibold text-fg dark:text-white text-xs hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors block truncate">
                                    {{ $member->full_name }}
                                </a>
                                <div class="flex items-center gap-1.5 mt-0.5 text-xs font-mono text-fg-muted dark:text-fg-muted">
                                    <span>{{ $member->member_code }}</span>
                                    <span>•</span>
                                    <a href="{{ route('app.invoices.show', $p->id) }}" 
                                       target="_blank" 
                                       title="View / Print Tax Invoice"
                                       class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold cursor-pointer">
                                        {{ $receiptNo }}
                                    </a>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $status === 'Completed' ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30' : ($status === 'Partial' ? 'bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30' : 'bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-500/30') }}">
                                {{ $status }}
                            </span>
                        </div>

                        <!-- Details Grid -->
                        <div class="p-2.5 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line text-xs space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-fg-muted dark:text-fg-muted">Package / Item:</span>
                                <span class="font-semibold text-fg dark:text-fg truncate max-w-[180px]">{{ $planName }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-fg-muted dark:text-fg-muted">Payment Date:</span>
                                <span class="font-mono text-fg-muted dark:text-fg-muted">{{ $p->payment_date ? $p->payment_date->format('d/m/Y') : $p->created_at->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-fg-muted dark:text-fg-muted">Method:</span>
                                <span class="px-2 py-0.5 rounded bg-raised dark:bg-raised text-xs font-semibold text-fg-muted dark:text-fg-muted">{{ $p->payment_method }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-line dark:border-line">
                                <span class="text-fg-muted dark:text-fg-muted font-medium">Amount Paid:</span>
                                <span class="text-sm font-black {{ $isReversed ? 'text-rose-600 line-through' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $isReversed ? '-' : '' }}{{ $currency }}{{ number_format($p->amount, 2) }}
                                </span>
                            </div>
                            @if($dueAmount > 0)
                                <div class="flex items-center justify-between text-xs text-rose-600 dark:text-rose-400 font-semibold">
                                    <span>Remaining Due:</span>
                                    <span>{{ $currency }}{{ number_format($dueAmount, 2) }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Action Bar -->
                        <div class="flex items-center gap-1.5 pt-1">
                            <a href="{{ route('app.invoices.show', $p->id) }}" 
                               target="_blank" 
                               title="View / Print Tax Invoice Receipt"
                               class="flex-1 py-1.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line text-xs font-semibold flex items-center justify-center gap-1 transition-colors">
                                <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Receipt</span>
                            </a>

                            <a href="{{ $p->whatsapp_share_url }}" 
                               target="_blank" 
                               title="Share Receipt on WhatsApp" 
                               class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-500/20 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            </a>

                            @if(auth()->user()->isGymOwner() || auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('payments.edit_method'))
                                <button type="button" 
                                        @click="openEditMethod({{ $p->id }}, '{{ $receiptNo }}', '{{ number_format($p->amount, 2) }}', '{{ addslashes($member?->full_name ?? 'N/A') }}', '{{ strtolower($p->payment_method ?? 'cash') }}', '{{ addslashes($p->transaction_reference ?? '') }}')"
                                        title="Gym Owner: Change Payment Mode" 
                                        class="p-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 dark:text-amber-400 dark:border-amber-500/20 transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                            @endif

                            @if($dueAmount > 0 && !$isReversed)
                                <button type="button" 
                                        @click="openCollectDue({{ $member->id }}, {{ $dueAmount }}, '{{ addslashes($planName) }}', {{ $membership?->id ?? 'null' }})"
                                        title="Collect Remaining Due" 
                                        class="px-2.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-xs flex items-center gap-1">
                                    <span>Collect Due</span>
                                </button>
                            @endif

                            @if(!$isReversed)
                                <form action="{{ route('app.payments.reverse', $p->id) }}" method="POST" 
                                      data-confirm="Are you sure you want to reverse / void payment receipt '{{ $receiptNo }}'?" 
                                      data-confirm-title="Reverse Payment" 
                                      data-confirm-btn="Yes, Reverse" 
                                      data-confirm-type="warning" 
                                      class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 dark:text-rose-400 dark:border-rose-500/20 transition-colors" title="Reverse Payment">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-fg-muted dark:text-fg-muted space-y-2">
                        <div class="text-3xl">💳</div>
                        <p class="text-sm font-semibold text-fg-muted dark:text-fg-muted">No payment receipts found.</p>
                        <p class="text-xs text-fg-muted">Tap "+ Record Payment" to collect a fee or record membership.</p>
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[700px]">
                    <thead class="bg-raised dark:bg-slate-950/70 border-b border-line dark:border-line text-xs text-fg-muted dark:text-fg-muted font-semibold">
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
                    <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
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

                                $isConversionNote = $p->notes && (str_contains(strtolower($p->notes), 'crm') || str_contains(strtolower($p->notes), 'conversion') || str_contains(strtolower($p->notes), 'lead'));
                                $planName = $membership?->plan?->name ?? (($p->notes && ! $isConversionNote) ? Str::limit($p->notes, 30) : 'Standard Membership');
                                $receiptNo = $p->invoice_number;
                            @endphp
                            <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                <!-- MEMBER Column -->
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-fg dark:text-white text-xs">
                                        <a href="{{ route('app.members.show', $member->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                            {{ $member->full_name }}
                                        </a>
                                    </div>
                                    <div class="text-xs font-mono text-fg-muted dark:text-fg-muted mt-0.5 flex items-center gap-1.5">
                                        <span>{{ $member->member_code }}</span>
                                        <span>•</span>
                                        <a href="{{ route('app.invoices.show', $p->id) }}" 
                                           target="_blank" 
                                           title="View / Print Tax Invoice"
                                           class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 font-semibold cursor-pointer underline">
                                            {{ $receiptNo }}
                                        </a>
                                    </div>
                                </td>

                                <!-- TYPE Column -->
                                <td class="py-3.5 px-4">
                                    <span class="font-medium text-fg dark:text-fg block text-xs">
                                        {{ $planName }}
                                    </span>
                                </td>

                                <!-- AMOUNT Column -->
                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-xs {{ $isReversed ? 'text-rose-600 dark:text-rose-400 line-through' : 'text-emerald-600 dark:text-emerald-400' }}">
                                        {{ $isReversed ? '-' : '' }}{{ $currency }}{{ number_format($p->amount, 2) }}
                                    </div>
                                    @if($discountAmount > 0)
                                        <div class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">
                                            Discount: {{ $currency }}{{ number_format($discountAmount, 2) }}
                                        </div>
                                    @endif
                                    @if($dueAmount > 0)
                                        <div class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-0.5">
                                            Due: {{ $currency }}{{ number_format($dueAmount, 2) }}
                                        </div>
                                    @endif
                                </td>

                                <!-- METHOD Column -->
                                <td class="py-3.5 px-4">
                                    @if($p->payment_method === 'split' || str_contains($p->notes ?? '', 'Split Payment'))
                                        <span class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-xs font-semibold" title="{{ $p->notes }}">
                                            SPLIT
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-raised dark:bg-raised border border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs font-semibold">
                                            {{ $p->payment_method }}
                                        </span>
                                    @endif
                                </td>

                                <!-- DATE Column -->
                                <td class="py-3.5 px-4 font-mono text-fg-muted dark:text-fg-muted text-xs">
                                    {{ $p->payment_date ? $p->payment_date->format('d/m/Y') : $p->created_at->format('d/m/Y') }}
                                </td>

                                <!-- STATUS Column -->
                                <td class="py-3.5 px-4">
                                    <div>
                                        @if($status === 'Completed')
                                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-semibold text-xs">
                                                Completed
                                            </span>
                                        @elseif($status === 'Partial')
                                            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-400 font-semibold text-xs">
                                                Partial
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full bg-rose-50 dark:bg-rose-500/15 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-400 font-semibold text-xs">
                                                Reversed
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- ACTIONS Column -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- View / Print Receipt -->
                                        <a href="{{ route('app.invoices.show', $p->id) }}" 
                                           target="_blank" 
                                           title="View / Print Tax Invoice Receipt ({{ $receiptNo }})" 
                                           class="p-1.5 rounded-lg bg-raised hover:bg-slate-200 text-fg-muted hover:text-indigo-600 border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:hover:text-white dark:border-line transition-colors cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </a>

                                        <!-- WhatsApp Share -->
                                        <a href="{{ $p->whatsapp_share_url }}" 
                                           target="_blank" 
                                           title="Share Receipt on WhatsApp" 
                                           class="p-1.5 rounded-lg bg-raised hover:bg-emerald-50 text-emerald-600 border border-line dark:bg-raised dark:hover:bg-emerald-500/20 dark:text-emerald-400 dark:border-line transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
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
                                                        class="p-1.5 rounded-lg bg-raised hover:bg-blue-100 text-fg-muted hover:text-blue-600 dark:bg-raised dark:hover:bg-blue-600/30 dark:text-fg-muted dark:hover:text-blue-400 transition-colors cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" 
                                                    onclick="alert('No email address registered for this member.')" 
                                                    title="No email address registered" 
                                                    class="p-1.5 rounded-lg bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted cursor-not-allowed">
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
                                                        class="p-1.5 rounded-lg bg-raised hover:bg-rose-100 text-fg-muted hover:text-rose-600 dark:bg-raised dark:hover:bg-rose-900/50 dark:text-fg-muted dark:hover:text-rose-400 transition-colors cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Edit Payment Method Button (Gym Owner Only) -->
                                        @if(auth()->user()->isGymOwner() || auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('payments.edit_method'))
                                            <button type="button" 
                                                    @click="openEditMethod({{ $p->id }}, '{{ $receiptNo }}', '{{ number_format($p->amount, 2) }}', '{{ addslashes($member?->full_name ?? 'N/A') }}', '{{ strtolower($p->payment_method ?? 'cash') }}', '{{ addslashes($p->transaction_reference ?? '') }}')"
                                                    title="Gym Owner: Change Payment Mode (Cash/UPI/Card)" 
                                                    class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 hover:text-amber-800 border border-amber-200/80 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 dark:text-amber-400 dark:border-amber-500/30 transition-colors cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                        @endif

                                        <!-- Delete Payment Receipt Button (Gym Owner Only) -->
                                        @if(auth()->user()->isGymOwner() || auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('payments.refund'))
                                            <form action="{{ route('app.payments.delete', $p->id) }}" method="POST" 
                                                  data-confirm="Are you sure you want to permanently delete payment receipt '{{ $receiptNo }}' ({{ $currency }}{{ number_format($p->amount, 2) }})? This will permanently remove this transaction from the records." 
                                                  data-confirm-title="Delete Payment Receipt" 
                                                  data-confirm-btn="Yes, Delete Receipt" 
                                                  data-confirm-type="danger" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        title="Delete Payment Receipt" 
                                                        class="p-1.5 rounded-lg bg-raised hover:bg-rose-100 text-fg-muted hover:text-rose-600 dark:bg-raised dark:hover:bg-rose-900/50 dark:text-fg-muted dark:hover:text-rose-400 transition-colors cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif

                            @if($dueAmount > 0 && !$isReversed)
                                            <button type="button" 
                                                    @click="openCollectDue({{ $member->id }}, {{ $dueAmount }}, '{{ addslashes($planName) }}', {{ $membership?->id ?? 'null' }})"
                                                    title="Collect Due Payment (Due: {{ $currency }}{{ number_format($dueAmount, 2) }})" 
                                                    class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-500 text-emerald-700 hover:text-white border border-emerald-300 dark:bg-emerald-500/20 dark:hover:bg-emerald-500 dark:text-emerald-400 dark:hover:text-slate-950 dark:border-emerald-500/40 transition-all cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-fg-muted dark:text-fg-muted">
                                    <div class="text-3xl mb-2">💳</div>
                                    <p class="text-sm font-semibold text-fg-muted dark:text-fg-muted">No payment receipts found.</p>
                                    <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">Click "+ Record Payment" to collect a fee or record membership.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="p-3 sm:p-4 border-t border-line dark:border-line bg-raised dark:bg-slate-950/50">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL: RECORD PAYMENT / INVOICE                                           -->
        <!-- ========================================================================= -->
        <div x-show="showRecordPaymentModal" 
             x-transition:enter="ease-out duration-200"
             x-transition:leave="ease-in duration-150"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" 
             style="display: none;"
             x-cloak>
            
            <div @click.away="showRecordPaymentModal = false" class="w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-fg rounded-xl p-4 sm:p-6 space-y-5 text-xs relative">
                    
                    <!-- Top-Right Close Button -->
                    <button type="button" 
                            @click="showRecordPaymentModal = false" 
                            class="absolute top-4 sm:top-6 right-4 sm:right-6 p-2 rounded-xl text-fg-muted hover:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised transition-colors z-20 cursor-pointer" 
                            title="Close modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <form action="{{ route('app.payments.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <!-- Header Section: Gym Info & INVOICE -->
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-line dark:border-line pb-5 pr-10">
                            <div>
                                <h2 class="text-base font-semibold text-fg dark:text-white">{{ $tenant->name ?? 'POWERFIT GYM' }}</h2>
                                <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">{{ $tenant->address ?? '123 Fitness Street, Health City' }}</p>
                                <p class="text-xs text-fg-muted dark:text-fg-muted">Ph: {{ $tenant->phone ?? '080 6940 9814' }}</p>
                                <p class="text-xs font-mono text-fg-muted dark:text-fg-muted">{{ $tenant->metadata['gstin'] ?? 'GSTIN: 27AAACM3025E1ZZ' }}</p>
                            </div>

                            <div class="text-left sm:text-right">
                                <h1 class="text-2xl font-semibold text-indigo-600 dark:text-indigo-400">INVOICE</h1>
                                <div class="flex items-center gap-2 mt-2 sm:justify-end">
                                    <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold">Date</span>
                                    <input type="date" 
                                           name="payment_date" 
                                           x-model="paymentDate" 
                                           class="px-3 py-1 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-xs focus:border-accent focus:outline-none">
                                </div>
                            </div>
                        </div>

                        <!-- BILL TO Section: Interactive Member Search Dropdown -->
                        <div>
                            <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block mb-1.5">BILL TO</span>
                            
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
                                           class="w-full px-4 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                                    
                                    <button type="button" 
                                            x-show="selectedMember" 
                                            @click="clearMember()" 
                                            class="absolute right-3 top-2.5 text-fg-muted hover:text-fg-muted dark:hover:text-white text-xs">
                                        ✕
                                    </button>
                                </div>

                                <!-- Dropdown List -->
                                <div x-show="showMemberDropdown && filteredMembers.length > 0" 
                                     @click.away="showMemberDropdown = false" 
                                     class="absolute z-30 left-0 right-0 mt-1 max-h-52 overflow-y-auto bg-surface dark:bg-surface border border-line dark:border-line rounded-xl divide-y divide-line dark:divide-line">
                                    <template x-for="m in filteredMembers" :key="m.id">
                                        <div @click="selectMember(m)" class="p-3 hover:bg-raised dark:hover:bg-surface cursor-pointer flex items-center justify-between transition-colors">
                                            <div>
                                                <span class="font-semibold text-fg dark:text-white text-xs" x-text="m.name"></span>
                                                <span class="text-fg-muted dark:text-fg-muted text-xs ml-1" x-text="'(' + m.code + ')'"></span>
                                                <div class="text-xs text-fg-muted dark:text-fg-muted font-mono" x-text="m.phone"></div>
                                            </div>
                                            <div class="text-right">
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold" 
                                                      :class="m.status === 'ACTIVE' ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'" 
                                                      x-text="m.status"></span>
                                                <div class="text-xs text-fg-muted dark:text-fg-muted mt-0.5" x-text="m.active_plan_name"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Selected Member Summary Pill -->
                            <template x-if="selectedMember">
                                <div class="mt-2 p-3 rounded-xl bg-raised dark:bg-slate-950/70 border border-line dark:border-line flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-semibold text-xs">Active</span>
                                        <span class="font-semibold text-fg dark:text-white text-xs" x-text="selectedMember.name + ' (' + selectedMember.code + ')'"></span>
                                        <span class="text-fg-muted dark:text-fg-muted font-mono text-xs" x-text="selectedMember.phone"></span>
                                    </div>
                                    <div x-show="selectedMember.active_due > 0" class="text-rose-600 dark:text-rose-400 font-semibold text-xs">
                                        Pending Due: {{ $currency }}<span x-text="Number(selectedMember.active_due).toFixed(2)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Item Description Table Grid -->
                        <div class="space-y-2">
                            <!-- Table Headers -->
                            <div class="hidden sm:flex items-center gap-3 text-xs font-semibold text-fg-muted dark:text-fg-muted border-b border-line dark:border-line pb-2 px-1">
                                <div class="w-6 shrink-0">#</div>
                                <div class="w-44 shrink-0">ITEM TYPE</div>
                                <div class="flex-1 min-w-0">DESCRIPTION</div>
                                <div class="w-32 shrink-0 text-right">AMOUNT ({{ $currency }})</div>
                            </div>

                            <!-- Line Item Row -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-start gap-3 p-3.5 rounded-xl bg-raised dark:bg-slate-950/70 border border-line dark:border-line">
                                <div class="hidden sm:block w-6 shrink-0 font-semibold text-fg-muted pt-2.5">1</div>
                                
                                <!-- ITEM TYPE SELECT -->
                                <div class="w-full sm:w-44 shrink-0">
                                    <label class="block sm:hidden text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Item Type</label>
                                    <select name="item_type" 
                                            x-model="itemType" 
                                            @change="
                                                if (itemType === 'membership') { onPlanChange(); }
                                                else if (itemType === 'pt') { itemAmount = 5000; calculateEndDate(); collectedAmount = computedTotal; }
                                                else if (itemType === 'due') { collectedAmount = computedTotal; }
                                                else { itemAmount = 0; collectedAmount = 0; }
                                            "
                                            class="w-full px-3 py-2 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line font-semibold text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                                        <option value="membership">Membership</option>
                                        <option value="pt">Personal Training</option>
                                        <option value="due">Collect Due Balance</option>
                                        <option value="custom">Service / Custom</option>
                                    </select>
                                </div>

                                <!-- DESCRIPTION COLUMN -->
                                <div class="flex-1 min-w-0 space-y-2.5">
                                    <label class="block sm:hidden text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Description</label>
                                    
                                    <!-- When Membership selected -->
                                    <template x-if="itemType === 'membership'">
                                        <div class="space-y-2.5">
                                            <div>
                                                <select name="membership_plan_id" 
                                                        x-model="selectedPlanId" 
                                                        @change="onPlanChange()" 
                                                        class="w-full px-3 py-2 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-medium text-xs focus:border-accent focus:outline-none">
                                                    <option value="">— Select Membership —</option>
                                                    @foreach($membershipPlans as $p)
                                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $currency }}{{ number_format($p->price, 2) }} / {{ $p->duration_value }} {{ $p->duration_type }})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="flex items-center gap-3 flex-wrap">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-fg-muted dark:text-fg-muted font-semibold text-xs shrink-0">Start</span>
                                                    <input type="date" 
                                                           name="start_date" 
                                                           x-model="startDate" 
                                                           @change="calculateEndDate()" 
                                                           class="px-2.5 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-xs focus:border-accent focus:outline-none">
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <span class="text-fg-muted dark:text-fg-muted font-semibold text-xs shrink-0">End</span>
                                                    <input type="date" 
                                                           name="end_date" 
                                                           x-model="endDate" 
                                                           class="px-2.5 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-xs focus:border-accent focus:outline-none">
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
                                                        class="w-full px-3 py-2 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-medium text-xs focus:border-accent focus:outline-none">
                                                    <option value="">— Select Personal Training —</option>
                                                    <option value="1 Month PT (12 Sessions)">1 Month PT (12 Sessions)</option>
                                                    <option value="3 Month PT (36 Sessions)">3 Month PT (36 Sessions)</option>
                                                    <option value="6 Month PT (72 Sessions)">6 Month PT (72 Sessions)</option>
                                                    <option value="Custom PT Coaching">Custom PT Coaching</option>
                                                </select>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <span class="text-fg-muted dark:text-fg-muted font-semibold text-xs shrink-0 w-16">Trainer</span>
                                                <select name="trainer_id" 
                                                        x-model="ptTrainerId" 
                                                        class="w-full px-3 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                                                    <option value="">Select Trainer</option>
                                                    @foreach($trainers as $t)
                                                        <option value="{{ $t->id }}">{{ $t->full_name }} ({{ $t->specialization ?? 'Trainer' }})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-fg-muted dark:text-fg-muted font-semibold text-xs shrink-0 w-16">Sessions</span>
                                                    <input type="number" 
                                                           name="sessions" 
                                                           x-model="ptSessions" 
                                                           min="1" 
                                                           class="w-full px-3 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-xs">
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-fg-muted dark:text-fg-muted font-semibold text-xs shrink-0">Validity</span>
                                                    <input type="number" 
                                                           name="validity_days" 
                                                           x-model="ptValidityDays" 
                                                           @input="calculateEndDate()" 
                                                           min="1" 
                                                           class="w-20 px-3 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-xs">
                                                    <span class="text-fg-muted dark:text-fg-muted text-xs">days</span>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-3 pt-0.5 flex-wrap">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-fg-muted dark:text-fg-muted font-semibold text-xs shrink-0 w-16">Start</span>
                                                    <input type="date" 
                                                           name="pt_start_date" 
                                                           x-model="startDate" 
                                                           @change="calculateEndDate()" 
                                                           class="px-2.5 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-xs">
                                                </div>
                                                <span class="text-fg-muted dark:text-fg-muted font-mono text-xs">→ Ends: <strong class="text-fg dark:text-white" x-text="endDate"></strong></span>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- When Due Clearance selected -->
                                    <template x-if="itemType === 'due'">
                                        <div class="space-y-1.5">
                                            <div class="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Due Clearance</span>
                                                    <span class="text-xs font-mono font-semibold text-emerald-600 dark:text-emerald-300">Pending Due: {{ $currency }}<span x-text="Number(itemAmount).toFixed(2)"></span></span>
                                                </div>
                                                <p class="text-xs text-fg-muted dark:text-fg-muted mt-1" x-text="customItemName"></p>
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
                                                   class="w-full px-3 py-2 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                                        </div>
                                    </template>
                                </div>

                                <!-- AMOUNT INPUT -->
                                <div class="w-full sm:w-32 shrink-0 text-left sm:text-right">
                                    <label class="block sm:hidden text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Amount ({{ $currency }})</label>
                                    <input type="number" 
                                           step="0.01" 
                                           name="amount" 
                                           x-model="itemAmount" 
                                           @input="collectedAmount = computedTotal" 
                                           placeholder="0.00" 
                                           required 
                                           class="w-full px-3 py-2 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono font-semibold text-xs text-left sm:text-right focus:border-accent focus:outline-none">
                                </div>
                            </div>

                            <!-- Add Line Item Button -->
                            <div class="pt-1">
                                <button type="button" 
                                        class="px-3.5 py-1.5 rounded-lg border border-dashed border-indigo-400 dark:border-indigo-500/40 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Add Line Item</span>
                                </button>
                            </div>
                        </div>

                        <!-- Financial Breakdown Summary (Right Aligned) -->
                        <div class="flex justify-end pt-1">
                            <div class="w-full sm:max-w-xs space-y-2 border-t border-line dark:border-line pt-3 text-xs">
                                <div class="flex items-center justify-between text-fg-muted dark:text-fg-muted">
                                    <span>Subtotal</span>
                                    <span class="font-semibold text-fg dark:text-white">{{ $currency }}<span x-text="Number(itemAmount || 0).toFixed(2)"></span></span>
                                </div>

                                <div class="flex items-center justify-between text-fg-muted dark:text-fg-muted">
                                    <span>Discount</span>
                                    <div class="flex items-center gap-1">
                                        <span>{{ $currency }}</span>
                                        <input type="number" 
                                               step="0.01" 
                                               name="discount" 
                                               x-model="discount" 
                                               @input="collectedAmount = computedTotal" 
                                               class="w-24 px-2 py-0.5 rounded bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-right text-xs focus:border-accent focus:outline-none">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-fg-muted dark:text-fg-muted">
                                    <span>Tax</span>
                                    <div class="flex items-center gap-1">
                                        <span>{{ $currency }}</span>
                                        <input type="number" 
                                               step="0.01" 
                                               name="tax" 
                                               x-model="tax" 
                                               @input="collectedAmount = computedTotal" 
                                               class="w-24 px-2 py-0.5 rounded bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-right text-xs focus:border-accent focus:outline-none">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between font-semibold text-sm text-fg dark:text-white pt-2 border-t border-line dark:border-line">
                                    <span>TOTAL</span>
                                    <span class="text-base text-indigo-600 dark:text-indigo-400 font-semibold">{{ $currency }}<span x-text="computedTotal.toFixed(2)"></span></span>
                                </div>
                            </div>
                        </div>

                        <!-- AMOUNT TO COLLECT Box -->
                        <div class="p-4 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line space-y-3">
                            <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">AMOUNT TO COLLECT</span>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-surface dark:bg-surface border border-emerald-500/40 text-emerald-600 dark:text-emerald-400 font-semibold text-base">
                                    <span>{{ $currency }}</span>
                                    <input type="number" 
                                           step="0.01" 
                                           name="collected_amount" 
                                           x-model="collectedAmount" 
                                           required 
                                           class="w-28 bg-transparent text-emerald-600 dark:text-emerald-400 font-mono font-semibold focus:outline-none">
                                </div>
                                <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold">of {{ $currency }}<span x-text="computedTotal.toFixed(2)"></span></span>
                            </div>

                            <label class="flex items-center gap-2 text-xs text-fg-muted dark:text-fg-muted font-semibold cursor-pointer pt-1">
                                <input type="checkbox" name="activate_now" value="1" checked class="w-4 h-4 rounded border-line dark:border-line bg-surface dark:bg-surface text-indigo-600 focus:ring-0">
                                <span><strong>👤+ Activate membership now:</strong> Member can start using the gym immediately despite pending dues</span>
                            </label>
                        </div>

                        <!-- PAYMENT METHOD SECTION -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">Payment Breakdown</span>
                                <button type="button" 
                                        @click="toggleSplitPayment()" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium transition-colors cursor-pointer border"
                                        :class="isSplitPayment ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700'">
                                    <span x-text="isSplitPayment ? '✓ Split Payment Active' : 'Split Payment (2 Methods)'"></span>
                                </button>
                            </div>

                            <input type="hidden" name="is_split_payment" :value="isSplitPayment ? '1' : '0'">

                            <!-- Single Payment Mode -->
                            <div x-show="!isSplitPayment" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                                <div class="sm:col-span-4">
                                    <select name="payment_method" 
                                            x-model="paymentMethod" 
                                            class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-medium text-xs focus:border-accent focus:outline-none">
                                        <option value="cash">Cash</option>
                                        <option value="upi">UPI / QR Code</option>
                                        <option value="card">Card / POS</option>
                                        <option value="netbanking">Net Banking</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-4">
                                    <input type="number" 
                                           step="0.01" 
                                           x-model="collectedAmount" 
                                           placeholder="0" 
                                           class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs tabular-nums focus:border-accent focus:outline-none">
                                </div>

                                <div class="sm:col-span-4">
                                    <input type="text" 
                                           name="transaction_reference" 
                                           x-model="txnReference" 
                                           placeholder="Ref / Txn ID (Optional)" 
                                           class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                                </div>
                            </div>

                            <!-- Dual / Split Payment Mode -->
                            <div x-show="isSplitPayment" class="p-3.5 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line space-y-3">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-fg-muted dark:text-fg-muted">Split into 2 Payment Modes</span>
                                    <span class="text-fg-muted dark:text-fg-muted text-xs">
                                        Total Paid: <strong class="text-fg dark:text-white tabular-nums font-semibold">{{ $currency }}<span x-text="Number(collectedAmount || 0).toFixed(2)"></span></strong>
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Part 1 -->
                                    <div class="p-3 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-medium text-fg-muted dark:text-fg-muted">Payment 1</span>
                                            <span class="text-xs text-fg-muted">First part</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <select name="split_method_1" 
                                                    x-model="splitMethod1" 
                                                    class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-medium focus:border-accent focus:outline-none">
                                                <option value="cash">Cash</option>
                                                <option value="upi">UPI / QR</option>
                                                <option value="card">Card / POS</option>
                                                <option value="netbanking">Net Banking</option>
                                                <option value="cheque">Cheque</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                            </select>
                                            <input type="number" 
                                                   name="split_amount_1" 
                                                   x-model="splitAmount1" 
                                                   @input="updateSplitFromFirst()" 
                                                   step="any" 
                                                   min="0" 
                                                   placeholder="Amount" 
                                                   class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white tabular-nums text-xs font-medium focus:border-accent focus:outline-none">
                                        </div>
                                        <input type="text" 
                                               name="split_ref_1" 
                                               x-model="splitRef1" 
                                               placeholder="Ref / Txn ID (Optional)" 
                                               class="w-full px-3 py-1.5 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                                        <!-- Legacy fallback hidden inputs -->
                                        <input type="hidden" name="split_cash_amount" :value="splitMethod1 === 'cash' ? splitAmount1 : (splitMethod2 === 'cash' ? splitAmount2 : 0)">
                                    </div>

                                    <!-- Part 2 (Auto-calculated, Editable) -->
                                    <div class="p-3 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-medium text-fg-muted dark:text-fg-muted">Payment 2</span>
                                            <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">Auto-remaining (Editable)</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <select name="split_method_2" 
                                                    x-model="splitMethod2" 
                                                    class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-medium focus:border-accent focus:outline-none">
                                                <option value="upi">UPI / QR</option>
                                                <option value="card">Card / POS</option>
                                                <option value="cash">Cash</option>
                                                <option value="netbanking">Net Banking</option>
                                                <option value="cheque">Cheque</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                            </select>
                                            <input type="number" 
                                                   name="split_amount_2" 
                                                   x-model="splitAmount2" 
                                                   @input="updateSplitFromSecond()" 
                                                   step="any" 
                                                   min="0" 
                                                   placeholder="Remaining" 
                                                   class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white tabular-nums text-xs font-medium focus:border-accent focus:outline-none">
                                        </div>
                                        <input type="text" 
                                               name="split_ref_2" 
                                               x-model="splitRef2" 
                                               placeholder="Ref / Txn ID (Optional)" 
                                               class="w-full px-3 py-1.5 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                                        <!-- Legacy fallback hidden inputs -->
                                        <input type="hidden" name="split_online_amount" :value="splitMethod2 !== 'cash' ? splitAmount2 : (splitMethod1 !== 'cash' ? splitAmount1 : 0)">
                                        <input type="hidden" name="split_online_method" :value="splitMethod2 !== 'cash' ? splitMethod2 : (splitMethod1 !== 'cash' ? splitMethod1 : 'upi')">
                                        <input type="hidden" name="split_online_reference" :value="splitRef2 || splitRef1 || ''">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Textarea -->
                        <div>
                            <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block mb-1">Notes</span>
                            <textarea name="notes" 
                                      x-model="notes" 
                                      rows="2" 
                                      placeholder="Additional notes..." 
                                      class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none"></textarea>
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between border-t border-line dark:border-line pt-4 gap-3">
                            <p class="text-xs text-fg-muted dark:text-fg-muted italic text-center sm:text-left">Thank you for your membership!</p>
                            <div class="flex items-center gap-3 justify-end">
                                <button type="button" 
                                        @click="showRecordPaymentModal = false" 
                                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line font-semibold text-xs transition-colors cursor-pointer text-center">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
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
        <!-- MODAL: TAX INVOICE & FEE RECEIPT PREVIEW                                   -->
        <!-- ========================================================================= -->
        <!-- ========================================================================= -->
        <!-- MODAL: TAX INVOICE & RECEIPT PREVIEW -->
        <!-- ========================================================================= -->
        <div x-show="showInvoiceModal" 
             x-transition:enter="ease-out duration-200"
             x-transition:leave="ease-in duration-150"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-md flex items-center justify-center p-2.5 sm:p-6" 
             style="display: none;"
             x-cloak>
            
            <div @click.away="showInvoiceModal = false" class="w-full max-w-3xl max-h-[94vh] overflow-y-auto space-y-2.5 sm:space-y-3">
                
                <!-- Modal Header Toolbar -->
                <div class="flex items-center justify-between px-3.5 sm:px-5 py-2.5 sm:py-3 bg-surface dark:bg-surface border border-line dark:border-line rounded-xl">
                    <div class="flex items-center gap-2 text-fg dark:text-white font-semibold text-xs sm:text-sm">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Tax Invoice &amp; Receipt Preview</span>
                    </div>

                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <a :href="'/app/invoices/' + selectedInvoice.id" 
                           target="_blank" 
                           title="Open in new tab"
                           class="p-1.5 rounded-lg text-fg-muted hover:text-fg dark:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        <button type="button" 
                                @click="showInvoiceModal = false" 
                                title="Close"
                                class="p-1.5 rounded-lg text-fg-muted hover:text-fg dark:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Action Bar Toolbar -->
                <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3">
                    <button type="button" 
                            @click="showInvoiceModal = false" 
                            class="px-3.5 sm:px-5 py-1.5 sm:py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line font-semibold text-xs transition-all cursor-pointer">
                        Close
                    </button>

                    <a :href="'/app/invoices/' + selectedInvoice.id + '?print=1'" 
                       target="_blank" 
                       class="px-4 sm:px-6 py-1.5 sm:py-2 rounded-xl bg-surface hover:bg-raised text-white font-semibold text-xs flex items-center gap-1.5 transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Print Invoice</span>
                    </a>

                    <a :href="selectedInvoice.whatsapp_url" 
                       target="_blank" 
                       class="px-4 sm:px-6 py-1.5 sm:py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5 transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>Share on WhatsApp</span>
                    </a>

                    <form :action="'/app/payments/' + selectedInvoice.id + '/send-email'" method="POST" class="inline"
                          data-confirm="Send official payment receipt email directly to this member?"
                          data-confirm-title="Send Email Receipt"
                          data-confirm-btn="Yes, Send Email"
                          data-confirm-type="info">
                        @csrf
                        <button type="submit" 
                                class="px-4 sm:px-6 py-1.5 sm:py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs flex items-center gap-1.5 transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Send Email</span>
                        </button>
                    </form>

                    <a :href="'/app/invoices/' + selectedInvoice.id" 
                       target="_blank" 
                       class="px-4 sm:px-6 py-1.5 sm:py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs flex items-center gap-1.5 transition-all ops-primary">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Download PDF</span>
                    </a>
                </div>

                <!-- Printable Invoice Sheet (A4 Proportion) -->
                @php
                    $tenantSettings = $tenant->settings ?? [];
                    $invSettings = $tenantSettings['invoice'] ?? [];
                    $gymName = !empty($invSettings['gym_name']) ? $invSettings['gym_name'] : ($tenant->name ?? 'Gym');
                    $gymAddress = !empty($invSettings['address']) ? $invSettings['address'] : (!empty($tenantSettings['address']) ? $tenantSettings['address'] : ($tenant->mainBranch?->address ?? ($tenant->address ?? '')));
                    $gymPhone = !empty($invSettings['phone']) ? $invSettings['phone'] : ($tenant->phone ?? '');
                    $gymEmail = !empty($invSettings['email']) ? $invSettings['email'] : ($tenant->email ?? '');
                    $gymWeb = !empty($invSettings['website']) ? $invSettings['website'] : ($tenant->website ?? '');
                    $invoiceTitle = !empty($invSettings['title']) ? $invSettings['title'] : 'TAX INVOICE';
                    $statusText = !empty($invSettings['status_text']) ? $invSettings['status_text'] : 'PAID IN FULL';
                    $verificationText = !empty($invSettings['verification_text']) ? $invSettings['verification_text'] : 'Verified & Recorded';

                    // Terms
                    $defaultTerms = [
                        "Fees once paid are strictly non-refundable and non-transferable under any circumstances.",
                        "Membership is non-transferable and valid exclusively for the registered individual and specified tenure.",
                        "Members are required to carry clean training footwear, workout towel, and follow gym etiquette at all times.",
                        "Management reserves the right to adjust facility operating hours and enforce safety protocols.",
                        "Any unpaid dues must be cleared on or before the specified due date to maintain uninterrupted facility access.",
                        "All memberships and personal training sessions must be utilized strictly within the stipulated validity period; unutilized sessions or duration will automatically lapse upon expiry.",
                        "Members confirm they are medically fit for physical exercise, and management accepts no liability for any pre-existing health conditions, injuries, or emergencies during workouts.",
                        "Members are solely responsible for their personal belongings, cash, and valuables, and management accepts no liability for any loss, theft, or damage within the facility or lockers.",
                        "Gym equipment, weights, and accessories must be handled with care and re-racked after use, and any deliberate misuse or damage to facility property will be charged to the member.",
                        "Management strictly enforces zero tolerance against misconduct, harassment, or rule violations, which will result in immediate termination of membership without refund."
                    ];
                    if (!empty($invSettings['terms'])) {
                        $rawTerms = array_filter(array_map('trim', explode("\n", $invSettings['terms'])));
                        $termsList = array_map(function($t) {
                            return preg_replace('/^\s*\d+[\.\)]\s*/', '', $t);
                        }, $rawTerms);
                    } else {
                        $termsList = $defaultTerms;
                    }

                    $thankYouText = !empty($invSettings['thank_you']) ? $invSettings['thank_you'] : ("Thank you for training with " . $gymName . "!");
                    $helpline = !empty($invSettings['helpline']) ? $invSettings['helpline'] : $gymPhone;
                    $footerEmail = !empty($invSettings['footer_email']) ? $invSettings['footer_email'] : $gymEmail;
                    $footerWeb = !empty($invSettings['footer_web']) ? $invSettings['footer_web'] : $gymWeb;
                    $tagline = !empty(trim($invSettings['tagline'] ?? '')) ? $invSettings['tagline'] : '';
                    $sealText = !empty($invSettings['seal_text']) ? $invSettings['seal_text'] : "Authorized Signature / Seal";
                    $sealUrl = $invSettings['seal_url'] ?? null;
                    $signatureUrl = $invSettings['signature_url'] ?? null;
                    $signatoryName = $invSettings['signatory_name'] ?? null;
                @endphp

                <div class="invoice-card relative w-full max-w-3xl bg-surface text-fg rounded-xl p-4 sm:p-6 lg:p-7 border border-line flex flex-col justify-between min-h-0 sm:min-h-[1040px] print:min-h-[275mm] mx-auto">
                    
                    <!-- Faded Center Background Logo Watermark -->
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.035] select-none z-0">
                        @if(!empty($logoUrl))
                            <img src="{{ $logoUrl }}" alt="" class="w-[320px] sm:w-[400px] h-[320px] sm:h-[400px] object-contain grayscale">
                        @else
                            <div class="text-center font-semibold text-6xl sm:text-8xl text-fg rotate-[-12deg]">
                                {{ $gymName }}
                            </div>
                        @endif
                    </div>

                    <div class="relative z-10 flex flex-col justify-between h-full min-h-[inherit] flex-1">
                        
                        <!-- MAIN TOP CONTENT: Sections 1 through 5 -->
                        <div class="space-y-3 sm:space-y-3.5">
                            <!-- 1. Header Section: Brand & Invoice Meta -->
                            <div class="flex flex-col sm:flex-row items-start justify-between gap-3 pt-1 border-b border-line pb-3">
                                <div class="flex items-start gap-3.5 max-w-lg">
                                    @if(!empty($logoUrl))
                                        <img src="{{ $logoUrl }}" alt="{{ $gymName }}" class="h-14 w-14 sm:h-16 sm:w-16 object-contain rounded-xl bg-surface p-1 sm:p-1.5 border border-line shrink-0">
                                    @else
                                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-surface text-white flex flex-col items-center justify-center font-semibold text-xs tracking-tight shrink-0 p-1 text-center">
                                            <span class="text-amber-400 text-xl">🏋️</span>
                                            <span class="text-xs sm:text-xs leading-none font-semibold mt-0.5">GYM</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-semibold text-slate-950 tracking-tight leading-tight">{{ $gymName }}</h2>
                                        <p class="text-xs sm:text-sm text-fg-muted mt-0.5 leading-snug font-medium">{{ $gymAddress }}</p>
                                        <p class="text-xs sm:text-sm text-fg-muted mt-0.5 leading-snug font-medium">
                                            <span>Phone: <strong class="text-fg font-semibold">{{ $gymPhone }}</strong></span>
                                            @if(!empty($gymEmail))
                                                <span class="mx-1 text-fg-muted">|</span>
                                                <span>Email: <strong class="text-fg font-semibold">{{ $gymEmail }}</strong></span>
                                            @endif
                                        </p>
                                        @if(!empty($gymWeb))
                                            <p class="text-xs sm:text-sm text-fg-muted leading-snug font-medium">
                                                <span>Website: <strong class="text-fg font-semibold">{{ $gymWeb }}</strong></span>
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="sm:text-right flex flex-col items-start sm:items-end shrink-0 w-full sm:w-auto">
                                    <span class="inline-block px-5 py-1.5 rounded-xl bg-surface text-white font-semibold text-xs sm:text-sm">
                                        {{ $invoiceTitle }}
                                    </span>
                                    <p class="text-xs sm:text-sm text-fg-muted mt-1.5 font-medium">Invoice No: <span class="text-slate-950 font-semibold font-mono" x-text="selectedInvoice.receipt_no"></span></p>
                                    <p class="text-xs sm:text-sm text-fg-muted mt-0.5 font-medium" x-text="'Date: ' + selectedInvoice.date"></p>
                                    <span class="inline-block mt-1 px-3.5 py-0.5 rounded-full border border-emerald-400 bg-emerald-50/60 text-emerald-600 text-xs sm:text-xs font-semibold">
                                        {{ $statusText }}
                                    </span>
                                </div>
                            </div>

                            <!-- 2. Two Cards Row: Member & Plan Details -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <!-- Card 1: Member Details -->
                                <div class="p-3 sm:p-3.5 rounded-xl bg-raised border border-line space-y-1">
                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-fg mb-1">
                                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block shrink-0"></span>
                                        <span>MEMBER DETAILS (BILLED TO)</span>
                                    </div>
                                    <div class="grid grid-cols-12 gap-1 text-xs">
                                        <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Full Name:</span>
                                        <span class="font-semibold text-fg col-span-7 sm:col-span-8 truncate" x-text="selectedInvoice.member_name"></span>
                                    </div>
                                    <div class="grid grid-cols-12 gap-1 text-xs">
                                        <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Member ID:</span>
                                        <span class="font-semibold text-fg col-span-7 sm:col-span-8" x-text="selectedInvoice.member_code"></span>
                                    </div>
                                    <div class="grid grid-cols-12 gap-1 text-xs">
                                        <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Phone No:</span>
                                        <span class="font-semibold text-fg col-span-7 sm:col-span-8" x-text="selectedInvoice.member_phone"></span>
                                    </div>
                                    <div class="grid grid-cols-12 gap-1 text-xs">
                                        <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Payment Mode:</span>
                                        <span class="font-semibold text-fg col-span-7 sm:col-span-8" x-text="getInvoiceModeText(selectedInvoice)"></span>
                                    </div>
                                </div>

                                <!-- Card 2: Membership & Plan Details -->
                                <div class="p-3 sm:p-3.5 rounded-xl bg-raised border border-line space-y-1">
                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-fg mb-1">
                                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block shrink-0"></span>
                                        <span>MEMBERSHIP &amp; PLAN DETAILS</span>
                                    </div>
                                    <div class="grid grid-cols-12 gap-1 text-xs">
                                        <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Package Duration:</span>
                                        <span class="font-semibold text-fg col-span-7 sm:col-span-8" x-text="selectedInvoice.plan_duration || '12 Months'"></span>
                                    </div>
                                    <div class="grid grid-cols-12 gap-1 text-xs">
                                        <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Start Date:</span>
                                        <span class="font-semibold text-fg col-span-7 sm:col-span-8" x-text="selectedInvoice.date"></span>
                                    </div>
                                    <div class="grid grid-cols-12 gap-1 text-xs">
                                        <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Plan Name:</span>
                                        <span class="font-semibold text-fg col-span-7 sm:col-span-8" x-text="selectedInvoice.plan_name"></span>
                                    </div>
                                    <div class="grid grid-cols-12 gap-1 text-xs">
                                        <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Status:</span>
                                        <span class="font-semibold text-emerald-600 col-span-7 sm:col-span-8">Active Membership</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Table -->
                            <div class="w-full overflow-x-auto">
                                <table class="w-full min-w-[340px] text-left border-collapse">
                                    <thead>
                                        <tr class="bg-surface text-white text-xs sm:text-xs font-semibold">
                                            <th class="py-2.5 px-3 rounded-l-lg w-8 sm:w-10 text-center">#</th>
                                            <th class="py-2.5 px-3">SERVICE / ITEM DESCRIPTION</th>
                                            <th class="py-2.5 px-3 text-center">DURATION</th>
                                            <th class="py-2.5 px-3 text-right">RATE (INR)</th>
                                            <th class="py-2.5 px-3 text-right rounded-r-lg">AMOUNT (INR)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        <tr class="text-fg">
                                            <td class="py-3 px-3 text-center text-fg-muted font-medium text-xs">1</td>
                                            <td class="py-3 px-3">
                                                <span class="font-semibold text-slate-950 text-xs sm:text-sm block" x-text="selectedInvoice.plan_name"></span>
                                            </td>
                                            <td class="py-3 px-3 text-center font-medium text-fg-muted text-xs" x-text="selectedInvoice.plan_duration || '12 Months'"></td>
                                            <td class="py-3 px-3 text-right font-medium text-fg-muted text-xs">{{ $currency }}<span x-text="selectedInvoice.amount"></span></td>
                                            <td class="py-3 px-3 text-right font-semibold text-slate-950 text-xs sm:text-sm">{{ $currency }}<span x-text="selectedInvoice.amount"></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 4. Bottom Split: Verification & Summary -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div class="p-3 sm:p-3.5 rounded-xl bg-raised border border-line space-y-1">
                                    <div class="text-xs font-semibold text-fg mb-1">
                                        PAYMENT DETAIL &amp; VERIFICATION
                                    </div>
                                    <div class="grid grid-cols-12 gap-1 text-xs">
                                        <span class="text-fg-muted font-normal col-span-6 sm:col-span-5">Payment Mode:</span>
                                        <span class="font-semibold text-fg col-span-6 sm:col-span-7" x-text="getInvoiceModeText(selectedInvoice)"></span>
                                    </div>
                                    <template x-for="item in getInvoiceSplitItems(selectedInvoice)" :key="item.method">
                                        <div class="grid grid-cols-12 gap-1 text-xs py-0.5">
                                            <span class="text-fg-muted font-normal col-span-6 sm:col-span-5" x-text="item.method + ':'"></span>
                                            <span class="font-semibold text-fg col-span-6 sm:col-span-7">
                                                <span x-text="item.amount"></span>
                                                <span class="text-xs font-normal text-fg-muted ml-1" x-show="item.ref" x-text="item.ref"></span>
                                            </span>
                                        </div>
                                    </template>
                                    <div class="grid grid-cols-12 gap-1 text-xs">
                                        <span class="text-fg-muted font-normal col-span-6 sm:col-span-5">Payment Status:</span>
                                        <span class="font-semibold text-emerald-600 col-span-6 sm:col-span-7">Completed</span>
                                    </div>
                                    <div class="grid grid-cols-12 gap-1 text-xs">
                                        <span class="text-fg-muted font-normal col-span-6 sm:col-span-5">Payment Verification:</span>
                                        <span class="font-semibold text-emerald-600 col-span-6 sm:col-span-7">{{ $verificationText }}</span>
                                    </div>
                                </div>

                                <div class="p-3 sm:p-3.5 rounded-xl bg-raised border border-line space-y-1">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-fg-muted font-normal">Subtotal</span>
                                        <span class="font-medium text-fg">{{ $currency }}<span x-text="selectedInvoice.amount"></span></span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-fg-muted font-normal">Taxes &amp; Gym Surcharge</span>
                                        <span class="font-normal text-fg-muted">{{ $currency }}0 (Inclusive)</span>
                                    </div>
                                    <div class="flex items-center justify-between pt-1 border-t border-line">
                                        <span class="text-xs sm:text-sm font-semibold text-slate-950">Total Amount</span>
                                        <span class="text-xs sm:text-sm font-semibold text-slate-950">{{ $currency }}<span x-text="selectedInvoice.amount"></span></span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-fg-muted font-normal">Amount Received</span>
                                        <span class="font-semibold text-emerald-600">{{ $currency }}<span x-text="selectedInvoice.amount"></span></span>
                                    </div>
                                    <div class="mt-1 p-2 rounded-lg bg-emerald-50/80 border border-emerald-200/70 flex items-center justify-between text-emerald-950">
                                        <span class="font-semibold text-xs text-emerald-950">Balance Due</span>
                                        <span class="font-semibold text-xs sm:text-sm text-emerald-600">{{ $currency }}0</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Terms & Conditions -->
                            <div class="p-3 sm:p-3.5 rounded-xl bg-raised border border-line text-xs text-fg-muted leading-snug space-y-1">
                                <div class="text-xs font-semibold text-fg mb-1">
                                    TERMS &amp; CONDITIONS
                                </div>
                                <ol class="list-decimal pl-4 space-y-0.5 text-xs sm:text-xs text-fg-muted leading-relaxed font-normal">
                                    @foreach($termsList as $term)
                                        <li>{{ $term }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>

                        <!-- FOOTER: Sections 6 & 7 (Anchored at the bottom with generous whitespace after Terms) -->
                        <div class="mt-auto pt-6 sm:pt-8 space-y-2.5 sm:space-y-3">
                            <!-- 6. Footer Helpline & Seal -->
                            <div class="pt-2.5 sm:pt-3 border-t border-line flex flex-col sm:flex-row items-center justify-between gap-2.5 sm:gap-3">
                                <div class="text-left space-y-0.5 sm:space-y-1">
                                    <p class="font-semibold text-xs sm:text-sm text-slate-950">{{ $thankYouText }}</p>
                                    <div class="text-xs sm:text-xs text-fg-muted flex flex-wrap items-center gap-x-2 gap-y-0.5 font-normal mt-0.5">
                                        @if(!empty($helpline))<span>Official Helpline: <strong class="text-fg font-medium">{{ $helpline }}</strong></span>@endif
                                        @if(!empty($helpline) && !empty($footerEmail))<span>|</span>@endif
                                        @if(!empty($footerEmail))<span>Email: <strong class="text-fg font-medium">{{ $footerEmail }}</strong></span>@endif
                                        @if(!empty($footerWeb))<span>|</span><span>Web: <strong class="text-fg font-medium">{{ $footerWeb }}</strong></span>@endif
                                    </div>
                                </div>

                                <!-- Right: Official Seal / Stamp & Signature -->
                                <div class="flex flex-col items-center justify-center shrink-0 min-w-[140px] sm:min-w-[160px] text-center">
                                    <div class="relative flex items-center justify-center w-24 h-18 sm:w-28 sm:h-20 select-none mx-auto">
                                        @if(!empty($sealUrl))
                                            <img src="{{ $sealUrl }}" alt="Stamp" class="h-14 w-14 sm:h-16 sm:w-16 object-contain drop-shadow-xs">
                                        @else
                                            <svg class="w-14 h-14 sm:w-16 sm:h-16 text-fg-muted drop-shadow-xs" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="70" cy="70" r="65" stroke="currentColor" stroke-width="2.5" />
                                                <circle cx="70" cy="70" r="58" stroke="currentColor" stroke-width="1.2" stroke-dasharray="3 2" />
                                                <path id="pay-seal-top" d="M 24 70 A 46 46 0 0 1 116 70" fill="none" />
                                                <text font-size="10.5" font-weight="900" fill="currentColor" letter-spacing="1.5">
                                                    <textPath href="#pay-seal-top" startOffset="50%" text-anchor="middle">
                                                        {{ strtoupper(substr($gymName, 0, 16)) }}
                                                    </textPath>
                                                </text>
                                                <path id="pay-seal-bottom" d="M 116 70 A 46 46 0 0 1 24 70" fill="none" />
                                                <text font-size="9" font-weight="800" fill="currentColor" letter-spacing="1.5">
                                                    <textPath href="#pay-seal-bottom" startOffset="50%" text-anchor="middle">
                                                        ★ VERIFIED ★
                                                    </textPath>
                                                </text>
                                                <line x1="32" y1="58" x2="108" y2="58" stroke="currentColor" stroke-width="1.5" />
                                                <text x="70" y="72" font-size="9.5" font-weight="900" fill="currentColor" text-anchor="middle" letter-spacing="1.5">
                                                    AHMEDABAD
                                                </text>
                                                <line x1="32" y1="78" x2="108" y2="78" stroke="currentColor" stroke-width="1.5" />
                                            </svg>
                                        @endif

                                        @if(!empty($signatureUrl))
                                            <img src="{{ $signatureUrl }}" alt="Signature" 
                                                 class="absolute inset-0 m-auto h-12 w-24 sm:h-14 sm:w-28 object-contain mix-blend-multiply rotate-[-6deg] drop-shadow-xs pointer-events-none z-10">
                                        @endif
                                    </div>
                                    <span class="text-xs sm:text-xs font-semibold text-fg-muted mt-1 text-center block w-full">{{ $sealText }}</span>
                                    @if(!empty($signatoryName))
                                        <span class="text-xs sm:text-xs font-semibold text-fg-muted text-center block w-full">{{ $signatoryName }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- 7. Tagline -->
                            @if(!empty(trim($tagline)))
                            <div class="pt-2 sm:pt-2.5 border-t border-line text-center">
                                <p class="text-xs sm:text-xs font-semibold text-slate-950">{{ $tagline }}</p>
                            </div>
                            @endif
                        </div>

                    </div>

                    </div>

                </div>

            </div>
        </div>

        <!-- ==================== EDIT PAYMENT METHOD MODAL (GYM OWNER ONLY) ==================== -->
        <div x-show="showEditMethodModal" 
             x-transition:enter="ease-out duration-200"
             x-transition:leave="ease-in duration-150"
             class="fixed inset-0 z-[100] overflow-y-auto bg-black/75 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" 
             style="display: none;"
             x-cloak
             @keydown.escape.window="showEditMethodModal = false">
            
            <div @click.away="showEditMethodModal = false" 
                 class="relative w-full max-w-lg overflow-hidden rounded-xl bg-surface dark:bg-surface text-left border border-line dark:border-line">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-line dark:border-line bg-amber-500/5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-semibold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-semibold text-fg dark:text-white">Correct Payment Method</h3>
                                <p class="text-xs text-amber-700 dark:text-amber-400 font-medium">Owner Authorization Required &bull; Audited Action</p>
                            </div>
                        </div>
                        <button type="button" 
                                @click="showEditMethodModal = false" 
                                class="p-1.5 rounded-lg text-fg-muted hover:text-fg-muted dark:hover:text-fg hover:bg-raised dark:hover:bg-raised transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Form -->
                    <form :action="'{{ url('app/payments') }}/' + editPaymentId + '/update-method'" method="POST" class="p-5 space-y-4">
                        @csrf

                        <!-- Payment Receipt Summary Card -->
                        <div class="p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line space-y-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-fg-muted dark:text-fg-muted">Receipt / Invoice:</span>
                                <span class="font-mono font-semibold text-fg dark:text-white" x-text="editPaymentInvoice"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-fg-muted dark:text-fg-muted">Member:</span>
                                <span class="font-semibold text-fg dark:text-white" x-text="editPaymentMember"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-fg-muted dark:text-fg-muted">Amount Paid:</span>
                                <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">{{ $currency }}<span x-text="editPaymentAmount"></span></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-fg-muted dark:text-fg-muted">Current Method:</span>
                                <span class="px-2 py-0.5 rounded bg-slate-200 dark:bg-raised font-semibold text-xs text-fg-muted dark:text-fg-muted" x-text="editPaymentMethod"></span>
                            </div>
                        </div>

                        <!-- Select New Payment Method -->
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">
                                Select Correct Payment Method <span class="text-rose-500">*</span>
                            </label>
                            <select name="payment_method" 
                                    x-model="editPaymentMethod" 
                                    required 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-amber-500 focus:outline-none cursor-pointer">
                                <option value="cash">💵 Cash</option>
                                <option value="upi">📱 UPI / QR Code</option>
                                <option value="card">💳 Card (POS Machine)</option>
                                <option value="netbanking">🌐 Net Banking</option>
                                <option value="cheque">📄 Cheque</option>
                                <option value="bank_transfer">🏦 Bank Transfer (NEFT/IMPS)</option>
                            </select>
                        </div>

                        <!-- Transaction Reference / UTR -->
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">
                                Transaction Reference / UTR No. <span class="text-fg-muted font-normal">(Optional)</span>
                            </label>
                            <input type="text" 
                                   name="transaction_reference" 
                                   x-model="editPaymentRef" 
                                   placeholder="e.g. UPI Ref 3241908291, POS Slip #, Cheque No." 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-mono focus:border-amber-500 focus:outline-none">
                        </div>

                        <!-- Reason for Correction -->
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">
                                Reason for Change <span class="text-fg-muted font-normal">(Saved in Audit Log)</span>
                            </label>
                            <input type="text" 
                                   name="change_reason" 
                                   x-model="editPaymentReason" 
                                   placeholder="e.g. Staff mistakenly selected Cash instead of UPI" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>

                        <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-700 dark:text-amber-400 flex items-start gap-2">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Changing this method will instantly update the tax invoice, receipt printout, and daily drawer reconciliation.</span>
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-line dark:border-line">
                            <button type="button" 
                                    @click="showEditMethodModal = false" 
                                    class="px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted text-xs font-semibold transition-colors cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-xs transition-colors cursor-pointer flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Save Corrected Method</span>
                            </button>
                        </div>
                    </form>

            </div>
        </div>

    </div>
</x-app-layout>
