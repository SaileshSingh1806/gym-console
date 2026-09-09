<x-app-layout header="Gym Members Management">
    @php
        $currency = auth()->user()->tenant?->currency_symbol ?? '₹';
        $plansJson = $membershipPlans->mapWithKeys(function ($p) {
            return [$p->id => [
                'id' => $p->id,
                'name' => $p->name,
                'plan_type' => $p->plan_type ?? 'single',
                'max_members' => (int) ($p->max_members ?? 1),
                'price' => (float) $p->price,
                'duration_type' => $p->duration_type,
                'duration_value' => $p->duration_value,
                'tax_rate' => (float) $p->tax_rate,
            ]];
        });

        $totalMembers = $members->total();
        $activeCount = \App\Models\Member::where('status', 'ACTIVE')->count();
        $pendingFeesCount = \App\Models\Membership::where('status', 'ACTIVE')
            ->whereRaw('final_amount > paid_amount')
            ->count();
        $expiringSoonCount = \App\Models\Membership::where('status', 'ACTIVE')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();
    @endphp

    <script>
        window.__MEMBERSHIP_PLANS__ = {{ Js::from($plansJson) }};
    </script>

    <div x-data="{ 
        showAddModal: false, 
        showEditModal: false, 
        showCollectFeeModal: false,
        plans: window.__MEMBERSHIP_PLANS__ || {},
        
        // Add Member Form State
        selectedPlanId: '',
        discountAmount: 0,
        paidNowAmount: 0,
        paymentMethod: 'cash',
        get planPrice() {
            return this.selectedPlanId && this.plans[this.selectedPlanId] ? this.plans[this.selectedPlanId].price : 0;
        },
        get finalFee() {
            return Math.max(0, this.planPrice - Number(this.discountAmount || 0));
        },
        get remainingBalance() {
            return Math.max(0, this.finalFee - Number(this.paidNowAmount || 0));
        },
        onPlanChange() {
            this.paidNowAmount = this.finalFee;
        },

        // Edit Member Form State
        editMember: {
            id: null,
            first_name: '',
            last_name: '',
            phone: '',
            email: '',
            gender: 'male',
            dob: '',
            address: '',
            emergency_contact_name: '',
            emergency_contact_phone: '',
            branch_id: '',
            status: 'ACTIVE',
            membership_plan_id: '',
            notes: ''
        },
        openEditModal(member) {
            this.editMember = {
                id: member.id,
                first_name: member.first_name || '',
                last_name: member.last_name || '',
                phone: member.phone || '',
                email: member.email || '',
                gender: member.gender || 'male',
                dob: member.dob ? member.dob.substring(0, 10) : '',
                address: member.address || '',
                emergency_contact_name: member.emergency_contact_name || '',
                emergency_contact_phone: member.emergency_contact_phone || '',
                branch_id: member.branch_id || '',
                status: member.status || 'ACTIVE',
                membership_plan_id: member.active_membership ? member.active_membership.membership_plan_id : '',
                notes: member.notes || ''
            };
            this.showEditModal = true;
        },

        // Collect Fee Modal State
        feeMember: {
            id: null,
            name: '',
            plan_name: '',
            final_amount: 0,
            paid_amount: 0,
            remaining_balance: 0,
        },
        collectAmount: 0,
        collectMethod: 'cash',
        collectRef: '',
        collectNotes: '',
        openCollectFeeModal(member, membership) {
            this.feeMember = {
                id: member.id,
                name: member.first_name + ' ' + member.last_name,
                plan_name: membership?.plan?.name || 'Active Membership',
                final_amount: Number(membership?.final_amount || 0),
                paid_amount: Number(membership?.paid_amount || 0),
                remaining_balance: Math.max(0, Number(membership?.final_amount || 0) - Number(membership?.paid_amount || 0)),
            };
            this.collectAmount = this.feeMember.remaining_balance > 0 ? this.feeMember.remaining_balance : '';
            this.collectMethod = 'upi';
            this.collectRef = '';
            this.collectNotes = '';
            this.showCollectFeeModal = true;
        }
    }" class="space-y-6">

        <!-- Top Metrics Header -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-xs text-slate-400 font-medium">Total Enrolled</span>
                    <p class="text-2xl font-black text-white mt-1">{{ $totalMembers }}</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center border border-amber-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-xs text-slate-400 font-medium">Active Members</span>
                    <p class="text-2xl font-black text-emerald-400 mt-1">{{ $activeCount }}</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>

            <a href="{{ route('app.members.index', ['filter' => 'due']) }}" 
               class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between hover:border-amber-500/40 hover:bg-slate-800/60 transition-all group cursor-pointer shadow-sm">
                <div>
                    <span class="text-xs text-slate-400 font-medium">Remaining Fees Due</span>
                    <p class="text-2xl font-black text-amber-400 mt-1">{{ $pendingFeesCount }} <span class="text-xs font-normal text-slate-400">members</span></p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center border border-amber-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
            </a>

            <a href="{{ route('app.members.index', ['filter' => 'expiring']) }}" 
               class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between hover:border-amber-500/40 hover:bg-slate-800/60 transition-all group cursor-pointer shadow-sm">
                <div>
                    <span class="text-xs text-slate-400 font-medium">Expiring Soon</span>
                    <p class="text-2xl font-black text-rose-400 mt-1">{{ $expiringSoonCount }} <span class="text-xs font-normal text-slate-400">within 7d</span></p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-rose-500/10 text-rose-400 flex items-center justify-center border border-rose-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </a>
        </div>

        <!-- Quick Filter Status Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-bold no-scrollbar">
            <a href="{{ route('app.members.index') }}" 
               class="px-3.5 py-2 rounded-xl border transition-all flex items-center gap-1.5 {{ !request('status') && !request('filter') ? 'bg-indigo-600 text-white border-indigo-500 shadow-md shadow-indigo-600/20' : 'bg-slate-900 text-slate-400 border-slate-800 hover:text-white hover:bg-slate-800' }}">
                <span>All Members</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ !request('status') && !request('filter') ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-300' }}">{{ $totalMembers }}</span>
            </a>

            <a href="{{ route('app.members.index', ['status' => 'ACTIVE']) }}" 
               class="px-3.5 py-2 rounded-xl border transition-all flex items-center gap-1.5 {{ request('status') === 'ACTIVE' && !request('filter') ? 'bg-emerald-500 text-slate-950 border-emerald-400 font-extrabold shadow-md shadow-emerald-500/20' : 'bg-slate-900 text-emerald-400 border-slate-800 hover:bg-emerald-500/10' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Active Members</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('status') === 'ACTIVE' && !request('filter') ? 'bg-slate-950/30 text-slate-950 font-black' : 'bg-emerald-500/20 text-emerald-300' }}">{{ $activeCount }}</span>
            </a>

            <a href="{{ route('app.members.index', ['filter' => 'due']) }}" 
               class="px-3.5 py-2 rounded-xl border transition-all flex items-center gap-1.5 {{ request('filter') === 'due' ? 'bg-amber-500 text-slate-950 border-amber-400 font-extrabold shadow-md shadow-amber-500/20' : 'bg-slate-900 text-amber-400 border-slate-800 hover:bg-amber-500/10' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Payment Due</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('filter') === 'due' ? 'bg-slate-950/30 text-slate-950 font-black' : 'bg-amber-500/20 text-amber-300' }}">{{ $pendingFeesCount }}</span>
            </a>

            <a href="{{ route('app.members.index', ['status' => 'EXPIRED']) }}" 
               class="px-3.5 py-2 rounded-xl border transition-all flex items-center gap-1.5 {{ request('status') === 'EXPIRED' ? 'bg-red-500 text-white border-red-400 font-extrabold shadow-md shadow-red-500/20' : 'bg-slate-900 text-red-400 border-slate-800 hover:bg-red-500/10' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Expired Members</span>
            </a>

            <a href="{{ route('app.members.index', ['filter' => 'expiring']) }}" 
               class="px-3.5 py-2 rounded-xl border transition-all flex items-center gap-1.5 {{ request('filter') === 'expiring' ? 'bg-rose-500 text-white border-rose-400 font-extrabold shadow-md shadow-rose-500/20' : 'bg-slate-900 text-rose-400 border-slate-800 hover:bg-rose-500/10' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Expiring Soon (7d)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('filter') === 'expiring' ? 'bg-white/20 text-white' : 'bg-rose-500/20 text-rose-300' }}">{{ $expiringSoonCount }}</span>
            </a>

            <a href="{{ route('app.members.index', ['status' => 'INACTIVE']) }}" 
               class="px-3.5 py-2 rounded-xl border transition-all flex items-center gap-1.5 {{ request('status') === 'INACTIVE' ? 'bg-slate-700 text-white border-slate-600 font-extrabold shadow-md' : 'bg-slate-900 text-slate-400 border-slate-800 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                <span>Inactive / Dormant</span>
            </a>
        </div>

        <!-- Action Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form action="{{ route('app.members.index') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-grow max-w-2xl">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone, email, or member code..." class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none flex-grow min-w-[220px]">
                
                <select name="status" class="px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                    <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                    <option value="SUSPENDED" {{ request('status') === 'SUSPENDED' ? 'selected' : '' }}>Suspended</option>
                    <option value="EXPIRED" {{ request('status') === 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                </select>

                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Search
                </button>
                @if(request('status') || request('filter') || request('search'))
                    <a href="{{ route('app.members.index') }}" class="text-xs text-rose-400 hover:underline">Clear Filters</a>
                @endif
            </form>

            <a href="{{ route('app.members.create') }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-amber-500/20 transition-all shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Enroll New Member
            </a>
        </div>

        <!-- Members Table -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-3.5 font-semibold">Member</th>
                        <th class="py-3 px-3.5 font-semibold">Contact</th>
                        <th class="py-3 px-3 font-semibold">Branch</th>
                        <th class="py-3 px-3.5 font-semibold">Plan & Validity</th>
                        <th class="py-3 px-3.5 font-semibold">Fee & Balance</th>
                        <th class="py-3 px-2.5 font-semibold text-center">Status</th>
                        <th class="py-3 px-3.5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($members as $member)
                        @php
                            $membership = $member->activeMembership ?? $member->memberships()->latest()->first();
                            $finalAmt = (float) ($membership?->final_amount ?? 0);
                            $paidAmt = (float) ($membership?->paid_amount ?? 0);
                            $remainingAmt = max(0, $finalAmt - $paidAmt);
                            $daysLeft = $membership ? (int) now()->startOfDay()->diffInDays($membership->end_date->startOfDay(), false) : null;
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <!-- Member info with ID and Joined cleanly stacked -->
                            <td class="py-3 px-3.5">
                                <a href="{{ route('app.members.show', $member->id) }}" class="flex items-center gap-2.5 group">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-amber-500/20 to-orange-500/20 border border-amber-500/30 flex items-center justify-center font-bold text-amber-400 text-xs shadow-sm shrink-0 group-hover:scale-105 transition-transform">
                                        {{ strtoupper(substr($member->first_name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-white text-xs block leading-tight truncate group-hover:text-amber-400 transition-colors">{{ $member->full_name }}</span>
                                        <span class="text-[10px] text-amber-400 font-mono font-medium block leading-tight mt-0.5">{{ $member->member_code }}</span>
                                        <span class="text-[9px] text-slate-500 block leading-tight mt-0.5">Joined: {{ $member->join_date ? $member->join_date->format('d M Y') : 'N/A' }}</span>
                                    </div>
                                </a>
                            </td>

                            <!-- Contact -->
                            <td class="py-3 px-3.5">
                                <div class="font-medium text-white flex items-center gap-1.5 leading-tight text-xs">
                                    <span>{{ $member->phone }}</span>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}" target="_blank" title="Send WhatsApp Message" class="text-emerald-400 hover:text-emerald-300">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    </a>
                                </div>
                                <div class="text-[10px] text-slate-400 leading-tight mt-0.5 truncate max-w-[150px]">{{ $member->email ?? 'No email' }}</div>
                            </td>

                            <!-- Branch -->
                            <td class="py-3 px-3 text-slate-400">
                                <span class="px-2 py-0.5 rounded-lg bg-slate-800 text-slate-300 font-medium text-[10px] border border-slate-700/40 inline-block">
                                    {{ $member->branch->name ?? 'Main Branch' }}
                                </span>
                            </td>

                            <!-- Plan & Validity -->
                            <td class="py-3 px-3.5">
                                @if($membership && $membership->plan)
                                    <div class="font-bold text-white text-xs leading-tight">{{ $membership->plan->name }}</div>
                                    <div class="text-[10px] text-slate-400 leading-tight mt-0.5">
                                        <span>Exp: {{ $membership->end_date->format('d M Y') }}</span>
                                        @if($membership->isExpired() || $daysLeft < 0)
                                            <span class="text-rose-400 font-bold ml-0.5">(Expired)</span>
                                        @elseif($daysLeft == 0)
                                            <span class="text-amber-400 font-bold ml-0.5">(Today)</span>
                                        @else
                                            <span class="text-emerald-400 font-semibold ml-0.5">({{ $daysLeft }}d left)</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-amber-400/80 text-[10px] font-semibold italic">No Active Plan</span>
                                @endif
                            </td>

                            <!-- Fee & Balance -->
                            <td class="py-3 px-3.5">
                                @if($membership)
                                    <div>
                                        @if($remainingAmt > 0)
                                            <span class="px-2 py-0.5 rounded-lg bg-amber-500/15 border border-amber-500/30 text-amber-400 font-bold text-[11px] inline-flex items-center gap-1 shadow-sm leading-tight">
                                                <span>{{ $currency }}{{ number_format($remainingAmt, 0) }}</span>
                                                <span class="text-[9px] uppercase tracking-wider font-semibold opacity-80">DUE</span>
                                            </span>
                                            <div class="text-[10px] text-slate-400 mt-0.5 leading-tight">
                                                Paid: {{ $currency }}{{ number_format($paidAmt, 0) }} / Total: {{ $currency }}{{ number_format($finalAmt, 0) }}
                                            </div>
                                        @else
                                            <span class="px-2 py-0.5 rounded-lg bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 font-bold text-[11px] inline-flex items-center gap-1 shadow-sm leading-tight">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Paid in Full</span>
                                            </span>
                                            <div class="text-[10px] text-slate-400 mt-0.5 leading-tight">
                                                Total: {{ $currency }}{{ number_format($finalAmt, 0) }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-500 text-[11px]">—</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-2.5 text-center">
                                @php
                                    $statusBadge = match($member->status) {
                                        'ACTIVE' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        'SUSPENDED' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                        'EXPIRED' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                                        default => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold border {{ $statusBadge }} inline-block">
                                    {{ $member->status }}
                                </span>
                            </td>

                            <!-- Actions Column -->
                            <td class="py-3 px-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($membership && $remainingAmt > 0)
                                        <button type="button" @click='openCollectFeeModal({{ Js::from($member) }}, {{ Js::from($membership) }})' title="Collect Remaining Fees" class="py-1 px-2.5 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 transition-all flex items-center gap-1 text-[11px] font-bold shadow-sm shrink-0">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span>Collect {{ $currency }}{{ number_format($remainingAmt, 0) }}</span>
                                        </button>
                                    @endif

                                    <a href="{{ route('app.members.show', $member->id) }}" title="View Member Profile" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors border border-slate-700/50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    <button type="button" @click='openEditModal({{ Js::from($member) }})' title="Edit Member Details" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors border border-slate-700/50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    <form action="{{ route('app.members.delete', $member->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove member {{ addslashes($member->full_name) }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete Member" class="p-1.5 rounded-lg bg-slate-800 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition-colors border border-slate-700/50">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500">
                                    <p class="text-sm">No gym members found matching your search or filters.</p>
                                    <button @click="showAddModal = true" class="mt-3 px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs">Enroll First Member</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($members->hasPages())
                <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                    {{ $members->links() }}
                </div>
            @endif
        </div>

        <!-- 1. ENROLL NEW MEMBER MODAL (With Live Fee & Remaining Balance Calculator) -->
        <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-5" @click.away="showAddModal = false">
                <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-white">Enroll New Gym Member</h3>
                        <p class="text-xs text-slate-400">Add member details, choose package, and track fee payment</p>
                    </div>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-white text-lg">✕</button>
                </div>

                <form action="{{ route('app.members.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Personal Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">First Name *</label>
                            <input type="text" name="first_name" required placeholder="e.g. Rahul" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Last Name *</label>
                            <input type="text" name="last_name" required placeholder="e.g. Sharma" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Phone Number (WhatsApp) *</label>
                            <input type="text" name="phone" required placeholder="e.g. 9876543210" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Email Address</label>
                            <input type="email" name="email" placeholder="rahul@example.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Gender</label>
                            <select name="gender" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Date of Birth</label>
                            <input type="date" name="dob" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Assigned Branch</label>
                            <select name="branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Membership Package & Billing Box -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-amber-400 uppercase tracking-wider">Membership Plan & Fee Setup</label>
                            <a href="{{ route('app.memberships.index') }}" class="text-[11px] text-slate-400 hover:text-amber-400 font-medium">+ Manage Packages ↗</a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-300 mb-1">Select Package / Plan</label>
                                <select name="membership_plan_id" x-model="selectedPlanId" @change="onPlanChange()" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                                    <option value="">-- No plan initially (Assign Later) --</option>
                                    @foreach($membershipPlans as $mp)
                                        <option value="{{ $mp->id }}">{{ $mp->name }} — {{ $currency }}{{ number_format($mp->price, 2) }} ({{ $mp->duration_value }} {{ $mp->duration_type }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-300 mb-1">Membership Start Date</label>
                                <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                            </div>
                        </div>

                        <template x-if="selectedPlanId">
                            <div class="space-y-3 pt-2 border-t border-slate-800/80">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] text-slate-400 mb-1">Package Price</label>
                                        <div class="px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs font-bold font-mono">
                                            {{ $currency }}<span x-text="planPrice.toFixed(2)"></span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] text-slate-400 mb-1">Discount ({{ $currency }})</label>
                                        <input type="number" step="0.01" name="discount" x-model="discountAmount" @input="onPlanChange()" placeholder="0.00" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none font-mono">
                                    </div>

                                    <div>
                                        <label class="block text-[11px] text-slate-400 mb-1">Final Payable Fee</label>
                                        <div class="px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-emerald-400 text-xs font-bold font-mono">
                                            {{ $currency }}<span x-text="finalFee.toFixed(2)"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    <div>
                                        <label class="block text-[11px] font-bold text-amber-400 mb-1">Initial Payment Paid Now ({{ $currency }}) *</label>
                                        <input type="number" step="0.01" name="initial_payment_amount" x-model="paidNowAmount" placeholder="0.00" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-amber-500/50 text-white text-xs font-bold focus:border-amber-500 focus:outline-none font-mono">
                                    </div>

                                    <!-- Remaining Fees Live Preview -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-300 mb-1">Remaining / Due Balance</label>
                                        <div class="px-3 py-2 rounded-xl border text-xs font-extrabold flex items-center justify-between" :class="remainingBalance > 0 ? 'bg-amber-500/10 border-amber-500/30 text-amber-400' : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400'">
                                            <span>{{ $currency }}<span x-text="remainingBalance.toFixed(2)"></span></span>
                                            <span class="text-[10px]" x-text="remainingBalance > 0 ? 'PENDING DUE' : 'FULLY PAID'"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] text-slate-400 mb-1">Payment Method</label>
                                        <select name="payment_method" x-model="paymentMethod" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                                            <option value="cash">💵 Cash Payment</option>
                                            <option value="upi">📱 UPI / GPay / PhonePe / Paytm</option>
                                            <option value="card">💳 Credit / Debit Card</option>
                                            <option value="netbanking">🏦 Net Banking</option>
                                            <option value="cheque">📄 Cheque</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] text-slate-400 mb-1">Transaction Ref / Note</label>
                                        <input type="text" name="transaction_reference" placeholder="e.g. UPI Ref / Cash receipt" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Additional Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Address / Locality</label>
                            <input type="text" name="address" placeholder="e.g. Ring Road, Hathijan" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Emergency Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" placeholder="Emergency phone" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold hover:bg-slate-700">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/20">Enroll & Save Member</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. EDIT MEMBER MODAL (Gym Owner & Receptionist Editable) -->
        <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-5" @click.away="showEditModal = false">
                <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-white">Edit Gym Member Details</h3>
                        <p class="text-xs text-slate-400">Update personal details, membership status, and branch</p>
                    </div>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-white text-lg">✕</button>
                </div>

                <form :action="'/app/members/' + editMember.id" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">First Name *</label>
                            <input type="text" name="first_name" x-model="editMember.first_name" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Last Name *</label>
                            <input type="text" name="last_name" x-model="editMember.last_name" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Phone Number (WhatsApp) *</label>
                            <input type="text" name="phone" x-model="editMember.phone" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Email Address</label>
                            <input type="email" name="email" x-model="editMember.email" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Gender</label>
                            <select name="gender" x-model="editMember.gender" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Date of Birth</label>
                            <input type="date" name="dob" x-model="editMember.dob" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Membership Status</label>
                            <select name="status" x-model="editMember.status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="INACTIVE">INACTIVE</option>
                                <option value="SUSPENDED">SUSPENDED</option>
                                <option value="EXPIRED">EXPIRED</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Branch</label>
                            <select name="branch_id" x-model="editMember.branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Assign / Change Package Plan</label>
                            <select name="membership_plan_id" x-model="editMember.membership_plan_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                                <option value="">-- Keep Current Plan --</option>
                                @foreach($membershipPlans as $mp)
                                    <option value="{{ $mp->id }}">{{ $mp->name }} ({{ $currency }}{{ number_format($mp->price, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Address / Home Details</label>
                        <input type="text" name="address" x-model="editMember.address" placeholder="Address..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" x-model="editMember.emergency_contact_name" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Emergency Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" x-model="editMember.emergency_contact_phone" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold hover:bg-slate-700">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/20">Update Member</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. COLLECT REMAINING FEES MODAL -->
        <div x-show="showCollectFeeModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5" @click.away="showCollectFeeModal = false">
                <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-white">Collect Fee Payment</h3>
                        <p class="text-xs text-slate-400">Receive remaining balance or instalment payment</p>
                    </div>
                    <button @click="showCollectFeeModal = false" class="text-slate-400 hover:text-white text-lg">✕</button>
                </div>

                <!-- Summary Box -->
                <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-2">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">Member:</span>
                        <span class="font-bold text-white" x-text="feeMember.name"></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">Plan:</span>
                        <span class="font-semibold text-amber-400" x-text="feeMember.plan_name"></span>
                    </div>
                    <div class="flex justify-between items-center text-xs pt-1 border-t border-slate-800">
                        <span class="text-slate-400">Total Fee:</span>
                        <span class="font-mono text-slate-300">{{ $currency }}<span x-text="feeMember.final_amount.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">Already Paid:</span>
                        <span class="font-mono text-emerald-400">{{ $currency }}<span x-text="feeMember.paid_amount.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between items-center text-xs pt-1 border-t border-slate-800">
                        <span class="font-bold text-amber-400">Remaining Balance:</span>
                        <span class="font-mono font-black text-amber-400 text-sm">{{ $currency }}<span x-text="feeMember.remaining_balance.toFixed(2)"></span></span>
                    </div>
                </div>

                <form :action="'/app/members/' + feeMember.id + '/collect-fee'" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-amber-400 mb-1">Amount to Collect ({{ $currency }}) *</label>
                        <input type="number" step="0.01" name="amount" x-model="collectAmount" required min="0.01" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-amber-500/60 text-white font-mono font-bold text-sm focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Payment Method *</label>
                        <select name="payment_method" x-model="collectMethod" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                            <option value="upi">📱 UPI / GPay / PhonePe / Paytm</option>
                            <option value="cash">💵 Cash Payment</option>
                            <option value="card">💳 Credit / Debit Card</option>
                            <option value="netbanking">🏦 Net Banking</option>
                            <option value="cheque">📄 Cheque</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Transaction Reference / UTR Number</label>
                        <input type="text" name="transaction_reference" x-model="collectRef" placeholder="e.g. UPI-123456789" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Payment Notes / Receipt Remarks</label>
                        <input type="text" name="notes" x-model="collectNotes" placeholder="e.g. Second instalment remaining fee cleared" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                        <button type="button" @click="showCollectFeeModal = false" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold hover:bg-slate-700">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Record Payment Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

