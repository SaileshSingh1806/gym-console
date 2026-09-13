<x-app-layout header="Staff & Team Management">
    @php
        $currency = auth()->user()->tenant?->currency_symbol ?? '₹';
        $tenant = auth()->user()->tenant;
        $nextEmpId = 'EMP' . str_pad($totalStaff + 1, 3, '0', STR_PAD_LEFT);
        
        $branchesJson = $branches->map(function ($b) {
            return [
                'id' => $b->id,
                'name' => $b->name,
            ];
        });
    @endphp

    <div x-data="{
        showAddStaffModal: false,
        showEditStaffModal: false,
        showPasswordModal: false,
        passwordStaffId: null,
        passwordStaffName: '',
        allBranches: {{ Js::from($branchesJson) }},
        
        openPasswordModal(id, name) {
            this.passwordStaffId = id;
            this.passwordStaffName = name;
            this.showPasswordModal = true;
        },
        
        // Form Data
        staffForm: {
            id: null,
            name: '',
            phone: '',
            email: '',
            role: 'receptionist',
            can_login: true,
            status: 'ACTIVE',
            password: '',
            employee_id: '{{ $nextEmpId }}',
            device_emp_no: '',
            maid_id: '',
            designation: '',
            department: '',
            employee_category: '',
            joining_date: '',
            monthly_salary: '',
            payout_type: '',
            gender: '',
            dob: '',
            anniversary: '',
            pan_card: '',
            bank_account_no: '',
            bank_ifsc: '',
            selected_branches: [{{ $branches->first()?->id ?? 1 }}],
            address: '',
            notes: ''
        },

        toggleBranch(branchId) {
            if (this.staffForm.selected_branches.includes(branchId)) {
                this.staffForm.selected_branches = this.staffForm.selected_branches.filter(id => id !== branchId);
            } else {
                this.staffForm.selected_branches.push(branchId);
            }
        },

        openAddStaff() {
            this.staffForm = {
                id: null,
                name: '',
                phone: '',
                email: '',
                role: 'receptionist',
                can_login: true,
                status: 'ACTIVE',
                password: '',
                employee_id: '{{ $nextEmpId }}',
                device_emp_no: '',
                maid_id: '',
                designation: '',
                department: '',
                employee_category: '',
                joining_date: '{{ now()->format('Y-m-d') }}',
                monthly_salary: '',
                payout_type: '',
                gender: '',
                dob: '',
                anniversary: '',
                pan_card: '',
                bank_account_no: '',
                bank_ifsc: '',
                selected_branches: [{{ $branches->first()?->id ?? 1 }}],
                address: '',
                notes: ''
            };
            this.showAddStaffModal = true;
        },

        openEditStaff(staff, meta, branchIds) {
            this.staffForm = {
                id: staff.id,
                name: staff.name || '',
                phone: staff.phone || '',
                email: staff.email || '',
                role: staff.role || 'staff',
                can_login: meta.can_login !== undefined ? meta.can_login : true,
                status: staff.status || 'ACTIVE',
                password: '',
                employee_id: meta.employee_id || ('EMP' + String(staff.id).padStart(3, '0')),
                device_emp_no: meta.device_emp_no || '',
                maid_id: meta.maid_id || '',
                designation: meta.designation || '',
                department: meta.department || '',
                employee_category: meta.employee_category || '',
                joining_date: meta.joining_date || '',
                monthly_salary: meta.monthly_salary || '',
                payout_type: meta.payout_type || '',
                gender: meta.gender || '',
                dob: meta.dob || '',
                anniversary: meta.anniversary || '',
                pan_card: meta.pan_card || '',
                bank_account_no: meta.bank_account_no || '',
                bank_ifsc: meta.bank_ifsc || '',
                selected_branches: branchIds || [],
                address: meta.address || '',
                notes: meta.notes || ''
            };
            this.showEditStaffModal = true;
        }
    }" class="space-y-4">

        <!-- Top Navigation Bar & Tabs (Staff vs Roles) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
            <div class="flex items-center gap-1.5">
                <!-- Staff Tab (Active) -->
                <a href="{{ route('app.staff.index') }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 bg-indigo-600 text-white shadow border border-indigo-500/30">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Staff Members</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-950/60 font-black">{{ $totalStaff }}</span>
                </a>

                <!-- Roles & Permissions Tab -->
                <a href="{{ route('app.roles.index') }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Roles & Permissions</span>
                </a>

                @if(isset($staffQuota))
                <span class="px-2.5 py-1 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-bold font-mono flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full {{ $staffQuota['allowed'] ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                    <span>Staff Quota: {{ $staffQuota['current'] }}/{{ $staffQuota['limit'] == -1 ? 'Unlimited' : $staffQuota['limit'] }}</span>
                </span>
                @endif
            </div>

            <!-- Action Button: Add Staff -->
            <div class="flex items-center gap-2">
                @if(isset($staffQuota) && ! $staffQuota['allowed'])
                    <a href="{{ route('app.subscription.index') }}" 
                       class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 shadow transition-all whitespace-nowrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Upgrade for More Staff</span>
                    </a>
                @else
                    <button type="button" 
                            @click="openAddStaff()" 
                            class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center gap-1.5 shadow transition-all cursor-pointer whitespace-nowrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Staff Member</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TOP KPI STATS BANNER                       -->
        <!-- ========================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Total Staff -->
            <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Team</span>
                    <span class="text-xl font-bold text-white mt-0.5 block">{{ $totalStaff }}</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>

            <!-- Active Staff -->
            <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Active Access</span>
                    <span class="text-xl font-bold text-emerald-400 mt-0.5 block">{{ $activeStaff }}</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- Managers & Front Desk -->
            <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Managers & Desk</span>
                    <span class="text-xl font-bold text-sky-400 mt-0.5 block">{{ $managerStaff }}</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
            </div>

            <!-- Trainers & Coaches -->
            <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Trainers / Coaches</span>
                    <span class="text-xl font-bold text-amber-400 mt-0.5 block">{{ $trainerStaff }}</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- FILTER TOOLBAR                             -->
        <!-- ========================================== -->
        <form method="GET" action="{{ route('app.staff.index') }}" class="flex flex-wrap items-center gap-2.5">
            <!-- Search Bar -->
            <div class="flex-1 min-w-[240px] relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Search staff name, email, phone, employee ID..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none transition-colors">
            </div>

            <!-- Role Dropdown -->
            <div>
                <select name="role" 
                        onchange="this.form.submit()" 
                        class="px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-200 text-xs font-semibold focus:border-indigo-500 focus:outline-none cursor-pointer">
                    <option value="all" {{ request('role') === 'all' || !request('role') ? 'selected' : '' }}>All Roles</option>
                    <option value="gym_owner" {{ request('role') === 'gym_owner' ? 'selected' : '' }}>Gym Owner</option>
                    <option value="gym_manager" {{ request('role') === 'gym_manager' ? 'selected' : '' }}>Gym Manager</option>
                    <option value="receptionist" {{ request('role') === 'receptionist' ? 'selected' : '' }}>Receptionist (Front Desk)</option>
                    <option value="trainer" {{ request('role') === 'trainer' ? 'selected' : '' }}>Fitness Trainer</option>
                    <option value="accountant" {{ request('role') === 'accountant' ? 'selected' : '' }}>Accountant</option>
                    <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>General Staff</option>
                </select>
            </div>

            <!-- Status Dropdown -->
            <div>
                <select name="status" 
                        onchange="this.form.submit()" 
                        class="px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-200 text-xs font-semibold focus:border-indigo-500 focus:outline-none cursor-pointer">
                    <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Status</option>
                    <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                    <option value="SUSPENDED" {{ request('status') === 'SUSPENDED' ? 'selected' : '' }}>Suspended</option>
                    <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- + Add Staff Member CTA -->
            <button type="button" 
                    @click="openAddStaff()" 
                    class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/20 transition-all cursor-pointer whitespace-nowrap ml-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Add Staff Member</span>
            </button>
        </form>

        <!-- ========================================== -->
        <!-- STAFF DIRECTORY TABLE                      -->
        <!-- ========================================== -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/80 border-b border-slate-800 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">NAME</th>
                            <th class="py-3.5 px-4">EMPLOYEE ID</th>
                            <th class="py-3.5 px-4">ROLE</th>
                            <th class="py-3.5 px-4">DESIGNATION</th>
                            <th class="py-3.5 px-4">PHONE</th>
                            <th class="py-3.5 px-4">SALARY</th>
                            <th class="py-3.5 px-4">LOGIN</th>
                            <th class="py-3.5 px-4">STATUS</th>
                            <th class="py-3.5 px-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($staffMembers as $u)
                            @php
                                $meta = $u->metadata ?? [];
                                $empId = $meta['employee_id'] ?? ('EMP' . str_pad($u->id, 3, '0', STR_PAD_LEFT));
                                $roleBadgeClass = match($u->role) {
                                    'gym_owner' => 'bg-purple-500/15 border-purple-500/30 text-purple-300',
                                    'gym_manager' => 'bg-indigo-500/15 border-indigo-500/30 text-indigo-300',
                                    'receptionist' => 'bg-slate-800 border-slate-700 text-slate-300',
                                    'trainer' => 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400',
                                    'accountant' => 'bg-sky-500/15 border-sky-500/30 text-sky-400',
                                    default => 'bg-slate-800 border-slate-700 text-slate-300'
                                };
                                $roleLabel = match($u->role) {
                                    'gym_owner' => 'Gym owner',
                                    'gym_manager' => 'Gym Manager',
                                    'receptionist' => 'Receptionist',
                                    'trainer' => 'Trainer',
                                    'accountant' => 'Accountant',
                                    default => 'Staff'
                                };
                                $branchList = $u->branches->pluck('name')->join(', ');
                                $branchIds = $u->branches->pluck('id')->toArray();
                                $canLogin = !isset($meta['can_login']) || $meta['can_login'];
                                $salaryVal = isset($meta['monthly_salary']) && floatval($meta['monthly_salary']) > 0 ? (float)$meta['monthly_salary'] : null;
                            @endphp
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <!-- NAME -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-white text-xs shrink-0 shadow-sm">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div class="font-bold text-white text-xs flex items-center gap-1.5">
                                            <span>{{ $u->name }}</span>
                                            @if($u->id === auth()->id())
                                                <span class="px-1.5 py-0.2 rounded bg-indigo-500/20 text-indigo-400 text-[9px] font-bold border border-indigo-500/30">You</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- EMPLOYEE ID -->
                                <td class="py-3.5 px-4">
                                    @if(!empty($empId))
                                        <span class="px-2 py-0.5 rounded bg-slate-800/80 border border-slate-700 font-mono text-[11px] font-semibold text-slate-300">
                                            {{ $empId }}
                                        </span>
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>

                                <!-- ROLE -->
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-semibold tracking-wide {{ $roleBadgeClass }}">
                                        {{ $roleLabel }}
                                    </span>
                                </td>

                                <!-- DESIGNATION -->
                                <td class="py-3.5 px-4 text-slate-300 text-xs">
                                    {{ $meta['designation'] ?? '—' }}
                                </td>

                                <!-- PHONE -->
                                <td class="py-3.5 px-4 text-slate-300 font-mono text-xs">
                                    {{ $u->phone ?: '—' }}
                                </td>

                                <!-- SALARY -->
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-200">
                                    @if($salaryVal !== null)
                                        {{ $currency }}{{ number_format($salaryVal, 2) }}
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>

                                <!-- LOGIN -->
                                <td class="py-3.5 px-4">
                                    @if($canLogin)
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 font-bold text-[10px] inline-flex items-center gap-1">
                                            ✓ On
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-slate-800 border border-slate-700 text-slate-400 font-bold text-[10px] inline-flex items-center gap-1">
                                            ✕ Off
                                        </span>
                                    @endif
                                </td>

                                <!-- STATUS -->
                                <td class="py-3.5 px-4">
                                    @if($u->status === 'ACTIVE')
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 font-bold text-[10px]">
                                            Active
                                        </span>
                                    @elseif($u->status === 'SUSPENDED')
                                        <span class="px-2.5 py-0.5 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-400 font-bold text-[10px]">
                                            Suspended
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full bg-slate-800 border border-slate-700 text-slate-400 font-bold text-[10px]">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <!-- ACTIONS -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Edit Button -->
                                        <button type="button" 
                                                @click="openEditStaff({{ json_encode($u) }}, {{ json_encode($meta) }}, {{ json_encode($branchIds) }})"
                                                title="Edit Staff Member" 
                                                class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>

                                        <!-- Password Reset Key Button -->
                                        <button type="button" 
                                                @click="openPasswordModal({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                                title="Reset Password" 
                                                class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-400 hover:text-amber-300 transition-colors cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                        </button>

                                        <!-- Toggle Status (Activate / Suspend) -->
                                        @if($u->id !== auth()->id() && $u->role !== 'gym_owner')
                                            <form action="{{ route('app.staff.toggle-status', $u->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        title="{{ $u->status === 'ACTIVE' ? 'Suspend Staff Account' : 'Activate Staff Account' }}" 
                                                        class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors cursor-pointer">
                                                    @if($u->status === 'ACTIVE')
                                                        <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @endif
                                                </button>
                                            </form>

                                            <!-- Delete Button -->
                                            <form action="{{ route('app.staff.delete', $u->id) }}" method="POST" 
                                                  data-confirm="Are you sure you want to remove '{{ addslashes($u->name) }}' from gym staff?" 
                                                  data-confirm-title="Remove Staff Member" 
                                                  data-confirm-btn="Yes, Remove Staff" 
                                                  data-confirm-type="danger" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        title="Delete Staff Member" 
                                                        class="p-1.5 rounded-lg bg-slate-800 hover:bg-rose-900/50 text-slate-400 hover:text-rose-400 transition-colors cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-500">
                                    <div class="text-3xl mb-2">👥</div>
                                    <p class="text-sm font-semibold text-slate-400">No staff members found.</p>
                                    <p class="text-xs text-slate-500 mt-1">Click "+ Add Staff Member" to add front desk, trainers, or managers.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($staffMembers->hasPages())
                <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                    {{ $staffMembers->links() }}
                </div>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL: ADD / EDIT STAFF MEMBER (Matches Reference Images 1 & 2)          -->
        <!-- ========================================================================= -->
        <div x-show="showAddStaffModal || showEditStaffModal" 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-sm flex items-start justify-center p-2 sm:p-4 md:p-6" 
             x-cloak>
            
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-5xl w-full p-5 sm:p-7 shadow-2xl relative my-6 text-slate-200" 
                 @click.away="showAddStaffModal = false; showEditStaffModal = false">
                
                <!-- Close Button -->
                <button type="button" 
                        @click="showAddStaffModal = false; showEditStaffModal = false" 
                        class="absolute top-4 sm:top-5 right-4 sm:top-5 p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors z-20 cursor-pointer" 
                        title="Close modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- Title Bar (Image 1 reference) -->
                <div class="border-b border-slate-800 pb-4 mb-6">
                    <h2 class="text-base font-extrabold text-white" x-text="showEditStaffModal ? 'Edit Staff Member' : 'Add Staff Member'"></h2>
                </div>

                <form :action="showEditStaffModal ? ('{{ url('app/staff') }}/' + staffForm.id) : '{{ route('app.staff.store') }}'" 
                      method="POST" 
                      x-data="{ isSubmitting: false }"
                      @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true;"
                      class="space-y-6">
                    @csrf

                    <!-- 1. BASIC INFO SECTION (Image 1) -->
                    <div class="space-y-3">
                        <h4 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">BASIC INFO</h4>
                        
                        <!-- Row 1: Full Name, Phone, Email -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Full Name <span class="text-rose-500">*</span></label>
                                <input type="text" 
                                       name="name" 
                                       x-model="staffForm.name" 
                                       required 
                                       placeholder="" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-colors">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Phone <span class="text-rose-500">*</span></label>
                                <input type="text" 
                                       name="phone" 
                                       x-model="staffForm.phone" 
                                       required 
                                       placeholder="10-digit phone" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-colors">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Email <span class="text-slate-500 text-[10px] font-normal">(Optional)</span></label>
                                <input type="email" 
                                       name="email" 
                                       x-model="staffForm.email" 
                                       placeholder="email@example.com" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-colors">
                            </div>
                        </div>

                        <!-- Row 2: Role, Login Access Checkbox, Status -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 items-center pt-1">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Role <span class="text-rose-500">*</span></label>
                                <select name="role" 
                                        x-model="staffForm.role" 
                                        required 
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none cursor-pointer">
                                    <option value="receptionist">Receptionist</option>
                                    <option value="gym_manager">Gym Manager</option>
                                    <option value="trainer">Trainer</option>
                                    <option value="accountant">Accountant</option>
                                    <option value="staff">Staff</option>
                                    <option value="gym_owner" x-show="staffForm.role === 'gym_owner'">Gym Owner</option>
                                </select>
                            </div>

                            <!-- Login Access Checkbox (with subtext) -->
                            <div class="pt-2 sm:pt-0">
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Login Access</label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" 
                                           name="can_login" 
                                           value="1" 
                                           x-model="staffForm.can_login" 
                                           class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                                    <span class="text-xs font-medium text-slate-200">Can log in to the manage panel</span>
                                </label>
                                <p class="text-[10px] text-slate-500 mt-0.5">Disable for non-login staff (housekeeping, security, etc.)</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Status</label>
                                <select name="status" 
                                        x-model="staffForm.status" 
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none cursor-pointer">
                                    <option value="ACTIVE">Active</option>
                                    <option value="INACTIVE">Inactive</option>
                                    <option value="SUSPENDED">Suspended</option>
                                </select>
                            </div>
                        </div>

                        <!-- Optional Password Input when login access is enabled -->
                        <div x-show="staffForm.can_login" class="pt-1">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">
                                <span x-text="showEditStaffModal ? 'Password (Leave blank to keep unchanged)' : 'Login Password'"></span>
                            </label>
                            <input type="password" 
                                   name="password" 
                                   x-model="staffForm.password" 
                                   :required="!showEditStaffModal && staffForm.can_login" 
                                   placeholder="Minimum 6 characters" 
                                   class="w-full md:w-1/3 px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- 2. EMPLOYMENT SECTION (Image 1) -->
                    <div class="space-y-3 pt-3 border-t border-slate-800/80">
                        <h4 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">EMPLOYMENT</h4>
                        
                        <!-- Row 1: Employee ID, Device Emp No, Maid ID -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Employee ID</label>
                                <input type="text" 
                                       name="employee_id" 
                                       x-model="staffForm.employee_id" 
                                       placeholder="EMP003" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <p class="text-[10px] text-slate-500 mt-0.5">Auto-generated if left empty</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Device Emp No <span class="text-slate-500 text-[10px]">(eSSL)</span></label>
                                <input type="text" 
                                       name="device_emp_no" 
                                       x-model="staffForm.device_emp_no" 
                                       placeholder="e.g. 4" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <p class="text-[10px] text-slate-500 mt-0.5">Number registered on eSSL device</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Maid ID <span class="text-slate-500 text-[10px]">(Optional)</span></label>
                                <input type="text" 
                                       name="maid_id" 
                                       x-model="staffForm.maid_id" 
                                       placeholder="" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Row 2: Designation, Department, Employee Category, Joining Date -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Designation</label>
                                <input type="text" 
                                       name="designation" 
                                       x-model="staffForm.designation" 
                                       placeholder="e.g. Trainer, Housekeeping" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Department</label>
                                <input type="text" 
                                       name="department" 
                                       x-model="staffForm.department" 
                                       placeholder="" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Employee Category</label>
                                <select name="employee_category" 
                                        x-model="staffForm.employee_category" 
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none cursor-pointer">
                                    <option value="">Select</option>
                                    <option value="Full Time">Full Time</option>
                                    <option value="Part Time">Part Time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Visiting">Visiting</option>
                                    <option value="Freelance">Freelance</option>
                                    <option value="Intern">Intern</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Joining Date</label>
                                <input type="date" 
                                       name="joining_date" 
                                       x-model="staffForm.joining_date" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- 3. SALARY SECTION (Image 1) -->
                    <div class="space-y-3 pt-3 border-t border-slate-800/80">
                        <h4 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">SALARY</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Monthly Salary</label>
                                <input type="number" 
                                       step="0.01" 
                                       name="monthly_salary" 
                                       x-model="staffForm.monthly_salary" 
                                       placeholder="0.00" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:border-indigo-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Payout Type</label>
                                <select name="payout_type" 
                                        x-model="staffForm.payout_type" 
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none cursor-pointer">
                                    <option value="">Select</option>
                                    <option value="Fixed Monthly">Fixed Monthly</option>
                                    <option value="Hourly / Session Based">Hourly / Session Based</option>
                                    <option value="Commission Only">Commission Only</option>
                                    <option value="Fixed + Commission">Fixed + Commission</option>
                                    <option value="Daily Wage">Daily Wage</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 4. PERSONAL SECTION (Image 1) -->
                    <div class="space-y-3 pt-3 border-t border-slate-800/80">
                        <h4 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">PERSONAL</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Gender</label>
                                <select name="gender" 
                                        x-model="staffForm.gender" 
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none cursor-pointer">
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Date of Birth</label>
                                <input type="date" 
                                       name="dob" 
                                       x-model="staffForm.dob" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:border-indigo-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Anniversary</label>
                                <input type="date" 
                                       name="anniversary" 
                                       x-model="staffForm.anniversary" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- 5. BANKING SECTION (Image 1) -->
                    <div class="space-y-3 pt-3 border-t border-slate-800/80">
                        <h4 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">BANKING</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">PAN Card</label>
                                <input type="text" 
                                       name="pan_card" 
                                       x-model="staffForm.pan_card" 
                                       placeholder="ABCDE1234F" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono uppercase focus:border-indigo-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Bank Account No.</label>
                                <input type="text" 
                                       name="bank_account_no" 
                                       x-model="staffForm.bank_account_no" 
                                       placeholder="Account number" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:border-indigo-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Bank IFSC</label>
                                <input type="text" 
                                       name="bank_ifsc" 
                                       x-model="staffForm.bank_ifsc" 
                                       placeholder="E.G. SBIN0001234" 
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono uppercase focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- 6. BRANCHES SECTION (Image 2) -->
                    <div class="space-y-2 pt-3 border-t border-slate-800/80">
                        <h4 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">BRANCHES</h4>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Assign Branches</label>

                        <div class="space-y-2">
                            <template x-for="b in allBranches" :key="b.id">
                                <div @click="toggleBranch(b.id)" 
                                     class="p-3 rounded-xl border flex items-center justify-between cursor-pointer transition-all"
                                     :class="staffForm.selected_branches.includes(b.id) 
                                        ? 'bg-indigo-950/40 border-indigo-500 text-indigo-300 shadow-sm' 
                                        : 'bg-slate-950 border-slate-800/80 text-slate-400 hover:border-slate-700'">
                                    
                                    <div class="flex items-center gap-2.5 text-xs font-semibold">
                                        <span>📍</span>
                                        <span x-text="b.name"></span>
                                    </div>

                                    <div x-show="staffForm.selected_branches.includes(b.id)" class="text-indigo-400 text-xs font-bold">
                                        ✓
                                    </div>
                                    <input type="checkbox" name="branches[]" :value="b.id" :checked="staffForm.selected_branches.includes(b.id)" class="hidden">
                                </div>
                            </template>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">Select all branches this staff member works at</p>
                    </div>

                    <!-- 7. OTHER SECTION (Image 2) -->
                    <div class="space-y-3 pt-3 border-t border-slate-800/80">
                        <h4 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">OTHER</h4>
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Address</label>
                            <textarea name="address" 
                                      x-model="staffForm.address" 
                                      rows="3" 
                                      placeholder="Home address" 
                                      class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-600 text-xs focus:border-indigo-500 focus:outline-none transition-colors"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Notes</label>
                            <textarea name="notes" 
                                      x-model="staffForm.notes" 
                                      rows="3" 
                                      placeholder="Any additional notes..." 
                                      class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-600 text-xs focus:border-indigo-500 focus:outline-none transition-colors"></textarea>
                        </div>
                    </div>

                    <!-- 8. FOOTER BUTTONS (Image 2) -->
                    <div class="flex items-center justify-start gap-3 pt-4 border-t border-slate-800">
                        <button type="button" 
                                @click="showAddStaffModal = false; showEditStaffModal = false" 
                                class="px-4 py-2 rounded-xl bg-transparent hover:bg-slate-800 text-slate-300 font-bold text-xs transition-colors cursor-pointer">
                            Cancel
                        </button>
                        
                        <button type="submit" 
                                :disabled="isSubmitting"
                                :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': isSubmitting }"
                                class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/30 transition-all cursor-pointer">
                            <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            <svg x-show="isSubmitting" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="isSubmitting ? 'Saving...' : 'Save'">Save</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL: RESET PASSWORD                                                     -->
        <!-- ========================================================================= -->
        <div x-show="showPasswordModal" 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-sm flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl relative text-slate-200" 
                 @click.away="showPasswordModal = false">
                
                <button type="button" 
                        @click="showPasswordModal = false" 
                        class="absolute top-4 right-4 p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <div class="border-b border-slate-800 pb-3 mb-4">
                    <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        <span>Reset Password</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Set a new login password for <span class="font-bold text-white" x-text="passwordStaffName"></span></p>
                </div>

                <form :action="'{{ url('app/staff') }}/' + passwordStaffId + '/reset-password'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">New Password <span class="text-rose-500">*</span></label>
                        <input type="password" 
                               name="password" 
                               required 
                               minlength="6" 
                               placeholder="Minimum 6 characters" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-colors">
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" 
                                @click="showPasswordModal = false" 
                                class="px-4 py-2 rounded-xl bg-transparent hover:bg-slate-800 text-slate-300 font-bold text-xs transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/30 transition-all cursor-pointer">
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
