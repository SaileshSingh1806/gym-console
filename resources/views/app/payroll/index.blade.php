<x-app-layout header="Staff Payroll & Payslips">
    <div x-data="payrollManager({{ \Illuminate\Support\Js::from($staffJson) }})" class="space-y-6">

        <!-- Top Header & Period Switcher -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-fg dark:text-white flex items-center gap-2.5">
                    <span class="p-2 text-fg rounded-xl bg-surface border border-line">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                    <span>Staff Payroll &amp; Salary Slips</span>
                </h1>
                <p class="text-sm text-fg-muted dark:text-fg-muted mt-1">
                    Manage monthly salaries, generate certified digital payslips, and settle payouts via UPI or Bank Transfer.
                </p>
            </div>

            <!-- Month/Year Filter & Header Actions -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Month & Year Selector Form -->
                <form method="GET" action="{{ route('app.payroll.index') }}" class="flex items-center gap-1.5 bg-raised dark:bg-surface p-1.5 rounded-xl border border-line dark:border-line">
                    <select name="month" onchange="this.form.submit()" class="text-xs font-semibold rounded-xl border-none bg-surface dark:bg-raised text-fg dark:text-fg py-1.5 px-3 focus:ring-0">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $selectedMonth === $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>

                    <select name="year" onchange="this.form.submit()" class="text-xs font-semibold rounded-xl border-none bg-surface dark:bg-raised text-fg dark:text-fg py-1.5 px-3 focus:ring-0">
                        @for($y = now()->year + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>

                    @if(count($branches) > 1)
                        <select name="branch_id" onchange="this.form.submit()" class="text-xs font-semibold rounded-xl border-none bg-surface dark:bg-raised text-fg dark:text-fg py-1.5 px-3 focus:ring-0">
                            <option value="">All Branches</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $branchId === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </form>

                <!-- Bulk Run Button -->
                <button type="button" @click="openBulkModal = true" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-surface hover:bg-raised dark:bg-raised dark:hover:bg-slate-750 text-fg font-semibold text-xs transition">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Run Monthly Payroll</span>
                </button>

                <!-- Create Custom Payslip Button -->
                <button type="button" @click="openCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-fg font-semibold text-xs transition bg-surface border border-line">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Payslip</span>
                </button>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Net Payroll -->
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl p-5 space-y-2">
                <div class="flex items-center justify-between text-xs font-semibold text-fg-muted">
                    <span>Total Payroll</span>
                    <span class="p-1.5 bg-raised dark:bg-raised rounded-xl text-fg-muted dark:text-fg-muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-semibold text-fg dark:text-white tracking-tight">
                    ₹{{ number_format($metrics['total_payroll'], 2) }}
                </div>
                <div class="text-xs text-fg-muted flex items-center justify-between">
                    <span>{{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}</span>
                    <span class="font-semibold text-fg-muted dark:text-fg-muted">{{ $metrics['total_count'] }} Payslips</span>
                </div>
            </div>

            <!-- Total Paid Amount -->
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl p-5 space-y-2">
                <div class="flex items-center justify-between text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                    <span>Settled &amp; Paid</span>
                    <span class="p-1.5 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl text-emerald-600 dark:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-semibold text-emerald-600 dark:text-emerald-400 tracking-tight">
                    ₹{{ number_format($metrics['paid_amount'], 2) }}
                </div>
                <div class="text-xs text-emerald-700 dark:text-emerald-500 font-semibold flex items-center justify-between">
                    <span>{{ $metrics['paid_count'] }} Staff Settled</span>
                    <span>{{ $metrics['total_payroll'] > 0 ? round(($metrics['paid_amount'] / $metrics['total_payroll']) * 100) : 0 }}% Disbursed</span>
                </div>
            </div>

            <!-- Pending Payouts -->
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl p-5 space-y-2">
                <div class="flex items-center justify-between text-xs font-semibold text-amber-600 dark:text-amber-400">
                    <span>Pending Payout</span>
                    <span class="p-1.5 bg-amber-50 dark:bg-amber-950/40 rounded-xl text-amber-600 dark:text-amber-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-semibold text-amber-600 dark:text-amber-400 tracking-tight">
                    ₹{{ number_format($metrics['pending_amount'], 2) }}
                </div>
                <div class="text-xs text-amber-700 dark:text-amber-500 font-semibold flex items-center justify-between">
                    <span>{{ $metrics['pending_count'] }} Awaiting Payment</span>
                    <span>Ready for Settlement</span>
                </div>
            </div>

            <!-- Staff Covered -->
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl p-5 space-y-2">
                <div class="flex items-center justify-between text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                    <span>Staff Coverage</span>
                    <span class="p-1.5 bg-indigo-50 dark:bg-indigo-950/40 rounded-xl text-indigo-600 dark:text-indigo-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-semibold text-indigo-600 dark:text-indigo-400 tracking-tight">
                    {{ $metrics['total_count'] }} <span class="text-sm font-normal text-fg-muted">/ {{ $metrics['eligible_staff_count'] }}</span>
                </div>
                <div class="text-xs text-fg-muted flex items-center justify-between">
                    <span>{{ $metrics['unprocessed_staff_count'] }} Unprocessed</span>
                    <span>Active Team</span>
                </div>
            </div>
        </div>

        <!-- Filter Tabs & Search Bar -->
        <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl p-4 space-y-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <!-- Status Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 text-xs font-semibold">
                    <a href="{{ route('app.payroll.index', array_merge(request()->query(), ['status' => 'all'])) }}"
                       class="px-3.5 py-2 rounded-xl transition-colors whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        All ({{ $metrics['total_count'] }})
                    </a>
                    <a href="{{ route('app.payroll.index', array_merge(request()->query(), ['status' => 'pending'])) }}"
                       class="px-3.5 py-2 rounded-xl transition-colors whitespace-nowrap {{ $statusFilter === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        Pending ({{ $metrics['pending_count'] }})
                    </a>
                    <a href="{{ route('app.payroll.index', array_merge(request()->query(), ['status' => 'paid'])) }}"
                       class="px-3.5 py-2 rounded-xl transition-colors whitespace-nowrap {{ $statusFilter === 'paid' ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        Settled &amp; Paid ({{ $metrics['paid_count'] }})
                    </a>
                </div>

                <!-- Search Input -->
                <form method="GET" action="{{ route('app.payroll.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    @if($branchId)
                        <input type="hidden" name="branch_id" value="{{ $branchId }}">
                    @endif

                    <div class="relative w-full md:w-72">
                        <svg class="w-4 h-4 text-fg-muted absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search staff, phone, payslip..."
                               class="w-full text-xs rounded-xl border border-line dark:border-line bg-raised dark:bg-surface pl-9 pr-4 py-2 text-fg dark:text-white placeholder-fg-subtle focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    @if(request('search'))
                        <a href="{{ route('app.payroll.index', ['month' => $selectedMonth, 'year' => $selectedYear, 'status' => $statusFilter]) }}" class="px-2.5 py-2 rounded-xl text-xs font-semibold bg-raised hover:bg-slate-200 dark:bg-raised text-fg-muted dark:text-fg-muted">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Payslips Records Table -->
            <div class="overflow-x-auto rounded-xl border border-line dark:border-line">
                <table class="w-full text-left text-xs">
                    <thead class="bg-raised dark:bg-raised text-fg-muted font-semibold text-xs border-b border-line dark:border-line">
                        <tr>
                            <th class="py-3 px-4">Staff Member</th>
                            <th class="py-3 px-3">Payslip Number</th>
                            <th class="py-3 px-3">Days</th>
                            <th class="py-3 px-3">Gross Earnings</th>
                            <th class="py-3 px-3">Deductions</th>
                            <th class="py-3 px-3">Net Take-Home</th>
                            <th class="py-3 px-3">Status &amp; Settlement</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-line font-medium">
                        @forelse($payslips as $ps)
                            <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                <!-- Staff Profile -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full text-fg font-semibold text-xs flex items-center justify-center shrink-0 border border-indigo-400/30 overflow-hidden bg-surface border-line">
                                            @if($ps->user?->avatar_url)
                                                <img src="{{ $ps->user->avatar_url }}" alt="{{ $ps->user->name }}" class="w-full h-full object-cover">
                                            @else
                                                <span>{{ strtoupper(substr($ps->user?->name ?? 'S', 0, 1)) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-semibold text-fg dark:text-white block">
                                                {{ $ps->user?->name }}
                                            </div>
                                            <div class="text-xs text-fg-muted flex items-center gap-1.5 mt-0.5">
                                                <span class="font-medium">{{ $ps->user?->role ? ucwords(str_replace('_', ' ', $ps->user->role)) : 'Staff' }}</span>
                                                <span>&bull;</span>
                                                <span>{{ $ps->user?->phone }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Payslip Number -->
                                <td class="py-3 px-3">
                                    <div class="font-mono font-semibold text-indigo-600 dark:text-indigo-400">{{ $ps->payslip_number }}</div>
                                    <div class="text-xs text-fg-muted">{{ $ps->formatted_period }}</div>
                                </td>

                                <!-- Working / Paid Days -->
                                <td class="py-3 px-3">
                                    <div class="font-semibold text-fg dark:text-fg">{{ $ps->present_days }} / {{ $ps->working_days }} Days</div>
                                    <div class="text-xs text-fg-muted flex flex-wrap items-center gap-1 mt-0.5">
                                        @if($ps->leaves_taken > 0)
                                            <span class="text-rose-500 font-semibold bg-rose-50 dark:bg-rose-950/40 px-1.5 py-0.2 rounded border border-rose-200/50">{{ $ps->leaves_taken }} Absent</span>
                                        @endif
                                        @if($ps->late_days > 0)
                                            <span class="text-amber-600 font-semibold bg-amber-50 dark:bg-amber-950/40 px-1.5 py-0.2 rounded border border-amber-200/50">{{ $ps->late_days }} Late</span>
                                        @endif
                                        @if($ps->leaves_taken === 0 && $ps->late_days === 0)
                                            <span class="text-emerald-600 dark:text-emerald-400 font-medium">100% Punctual</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Gross -->
                                <td class="py-3 px-3 font-mono font-semibold text-fg-muted dark:text-fg-muted">
                                    ₹{{ number_format((float) $ps->gross_salary, 2) }}
                                </td>

                                <!-- Deductions -->
                                <td class="py-3 px-3 font-mono text-rose-600 dark:text-rose-400 font-semibold">
                                    @if($ps->total_deductions > 0)
                                        -₹{{ number_format((float) $ps->total_deductions, 2) }}
                                    @else
                                        <span class="text-fg-muted">₹0.00</span>
                                    @endif
                                </td>

                                <!-- Net Pay -->
                                <td class="py-3 px-3">
                                    <div class="font-mono font-semibold text-sm text-fg dark:text-white">
                                        ₹{{ number_format((float) $ps->net_salary, 2) }}
                                    </div>
                                </td>

                                <!-- Status & Method -->
                                <td class="py-3 px-3">
                                    @if($ps->isPaid())
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Paid ({{ $ps->payment_method }})
                                        </span>
                                        @if($ps->paid_at)
                                            <div class="text-xs text-fg-muted mt-0.5">{{ $ps->paid_at->format('d M Y') }}</div>
                                        @endif
                                    @elseif($ps->isApproved())
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                                            Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                            Draft
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <!-- View / Print Document -->
                                        <a href="{{ route('app.payroll.document', $ps->id) }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-750 text-fg-muted dark:text-fg border border-line dark:border-line transition"
                                           title="View & Print Certified Payslip">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Payslip</span>
                                        </a>

                                        <!-- Direct WhatsApp Share -->
                                        <a href="{{ $ps->whatsapp_share_url }}" target="_blank"
                                           class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:hover:bg-raised border border-transparent hover:border-emerald-200 dark:hover:border-emerald-900 transition"
                                           title="Send Payslip on WhatsApp">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                        </a>

                                        <!-- Mark as Paid Modal Trigger -->
                                        @if(! $ps->isPaid())
                                            <button type="button" @click="openPayModal({{ json_encode($ps) }})"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition"
                                                    title="Settle & Mark Paid">
                                                <span>Pay</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-fg-muted space-y-2">
                                    <div class="w-12 h-12 rounded-full bg-raised dark:bg-raised text-fg-muted mx-auto flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </div>
                                    <p class="font-semibold text-sm text-fg-muted dark:text-fg-muted">No payslips found for this period</p>
                                    <p class="text-xs text-fg-muted max-w-sm mx-auto">
                                        Click <strong>Run Monthly Payroll</strong> to auto-generate salary drafts for all active staff, or click <strong>Create Payslip</strong>.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($payslips->hasPages())
                <div class="pt-2">
                    {{ $payslips->links() }}
                </div>
            @endif
        </div>

        <!-- ======================================================== -->
        <!-- 1. CREATE / EDIT PAYSLIP MODAL                           -->
        <!-- ======================================================== -->
        <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" @click="openCreateModal = false"></div>
            <div class="min-h-full flex items-center justify-center p-4">
                <div class="relative bg-surface dark:bg-surface rounded-xl max-w-2xl w-full p-6 sm:p-8 border border-line dark:border-line space-y-6">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-line dark:border-line">
                        <div class="flex items-center gap-3">
                            <span class="p-2.5 text-fg rounded-xl bg-surface border border-line">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-fg dark:text-white">Create Staff Payslip</h3>
                                <p class="text-xs text-fg-muted">Calculate basic salary, custom allowances, and deductions</p>
                            </div>
                        </div>
                        <button type="button" @click="openCreateModal = false" class="p-1 rounded-xl text-fg-muted hover:text-fg-muted dark:hover:text-fg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('app.payroll.store') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <input type="hidden" name="year" value="{{ $selectedYear }}">

                        <!-- Staff Member Selector -->
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted">Select Staff Member</label>
                            
                            <!-- Search Input -->
                            <div class="relative" x-show="!selectedStaff">
                                <svg class="w-4 h-4 text-fg-muted absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" x-model="staffSearch" placeholder="Type staff name, phone, or designation..."
                                       class="w-full text-xs rounded-xl border border-line dark:border-line bg-raised dark:bg-surface pl-10 pr-4 py-2.5 text-fg dark:text-white focus:ring-2 focus:ring-indigo-500">
                                
                                <div class="max-h-40 overflow-y-auto mt-1 rounded-xl border border-line dark:border-line bg-surface dark:bg-surface divide-y divide-line dark:divide-line">
                                    <template x-for="st in filteredStaff" :key="st.id">
                                        <button type="button" @click="chooseStaff(st)" class="w-full text-left p-2.5 hover:bg-indigo-50 dark:hover:bg-raised flex items-center justify-between text-xs transition">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-semibold flex items-center justify-center text-xs" x-text="st.initials"></span>
                                                <div>
                                                    <span class="font-semibold text-fg dark:text-white" x-text="st.name"></span>
                                                    <span class="text-xs text-fg-muted block" x-text="st.role + ' &bull; ' + st.phone"></span>
                                                </div>
                                            </div>
                                            <span class="font-mono font-semibold text-indigo-600 dark:text-indigo-400" x-text="'₹' + Number(st.base_salary).toLocaleString()"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Selected Staff Pill -->
                            <div x-show="selectedStaff" class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 flex items-center justify-between">
                                <input type="hidden" name="user_id" :value="selectedStaff?.id">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-full bg-indigo-600 text-white font-semibold flex items-center justify-center text-xs" x-text="selectedStaff?.initials"></span>
                                    <div>
                                        <h4 class="font-semibold text-xs text-fg dark:text-white" x-text="selectedStaff?.name"></h4>
                                        <p class="text-xs text-fg-muted" x-text="selectedStaff?.role + ' &bull; ' + selectedStaff?.phone"></p>
                                    </div>
                                </div>
                                <button type="button" @click="selectedStaff = null" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Change</button>
                            </div>
                        </div>

                        <!-- Salary & Days Row -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Basic Salary (₹)</label>
                                <input type="number" step="0.01" name="basic_salary" x-model.number="form.basic_salary" required
                                       class="w-full text-xs font-mono font-semibold rounded-xl border border-line dark:border-line bg-raised dark:bg-surface p-2.5 text-fg dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Working Days</label>
                                <input type="number" name="working_days" x-model.number="form.working_days"
                                       class="w-full text-xs font-semibold rounded-xl border border-line dark:border-line bg-raised dark:bg-surface p-2.5 text-fg dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Leaves Taken (Unpaid)</label>
                                <input type="number" name="leaves_taken" x-model.number="form.leaves_taken"
                                       class="w-full text-xs font-semibold rounded-xl border border-line dark:border-line bg-raised dark:bg-surface p-2.5 text-fg dark:text-white">
                            </div>
                        </div>

                        <!-- Allowances Section -->
                        <div class="space-y-2 pt-2 border-t border-line dark:border-line">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-fg dark:text-fg text-xs">Allowances &amp; Incentives</span>
                                <button type="button" @click="addAllowance()" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline flex items-center gap-1">
                                    <span>+ Add Allowance</span>
                                </button>
                            </div>

                            <template x-for="(allw, idx) in form.allowances" :key="idx">
                                <div class="flex items-center gap-2">
                                    <input type="text" :name="'allowances[' + idx + '][name]'" x-model="allw.name" placeholder="E.g. PT Commission, HRA, Bonus"
                                           class="flex-1 text-xs rounded-xl border border-line dark:border-line bg-raised dark:bg-surface p-2 text-fg dark:text-white">
                                    <input type="number" step="0.01" :name="'allowances[' + idx + '][amount]'" x-model.number="allw.amount" placeholder="Amount (₹)"
                                           class="w-28 text-xs font-mono font-semibold rounded-xl border border-line dark:border-line bg-raised dark:bg-surface p-2 text-fg dark:text-white">
                                    <button type="button" @click="removeAllowance(idx)" class="text-rose-500 hover:text-rose-700 p-1">&times;</button>
                                </div>
                            </template>
                        </div>

                        <!-- Deductions Section -->
                        <div class="space-y-2 pt-2 border-t border-line dark:border-line">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-fg dark:text-fg text-xs">Deductions &amp; Recoveries</span>
                                <button type="button" @click="addDeduction()" class="text-rose-600 dark:text-rose-400 font-semibold hover:underline flex items-center gap-1">
                                    <span>+ Add Deduction</span>
                                </button>
                            </div>

                            <template x-for="(ded, idx) in form.deductions" :key="idx">
                                <div class="flex items-center gap-2">
                                    <input type="text" :name="'deductions[' + idx + '][name]'" x-model="ded.name" placeholder="E.g. Advance Recovery, LOP, Penalties"
                                           class="flex-1 text-xs rounded-xl border border-line dark:border-line bg-raised dark:bg-surface p-2 text-fg dark:text-white">
                                    <input type="number" step="0.01" :name="'deductions[' + idx + '][amount]'" x-model.number="ded.amount" placeholder="Amount (₹)"
                                           class="w-28 text-xs font-mono font-semibold rounded-xl border border-line dark:border-line bg-raised dark:bg-surface p-2 text-fg dark:text-white">
                                    <button type="button" @click="removeDeduction(idx)" class="text-rose-500 hover:text-rose-700 p-1">&times;</button>
                                </div>
                            </template>
                        </div>

                        <!-- Net Calculation Summary Card -->
                        <div class="p-4 rounded-xl bg-surface text-fg flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-fg-muted block">Calculated Net Salary</span>
                                <div class="text-xs text-fg-muted">
                                    Gross: ₹<span x-text="calculateGross().toLocaleString('en-IN', {minimumFractionDigits: 2})"></span> &bull; 
                                    Deductions: ₹<span x-text="calculateDeductions().toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                                </div>
                            </div>
                            <div class="text-xl font-semibold font-mono text-cyan-400">
                                ₹<span x-text="calculateNet().toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                            </div>
                        </div>

                        <!-- Bank & UPI optional fields -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted mb-1">Bank Name / IFSC</label>
                                <input type="text" name="bank_name" x-model="form.bank_name" placeholder="HDFC Bank"
                                       class="w-full text-xs rounded-xl border border-line dark:border-line bg-raised dark:bg-surface p-2 text-fg dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted mb-1">UPI ID</label>
                                <input type="text" name="upi_id" x-model="form.upi_id" placeholder="name@upi"
                                       class="w-full text-xs rounded-xl border border-line dark:border-line bg-raised dark:bg-surface p-2 text-fg dark:text-white">
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-line dark:border-line">
                            <button type="button" @click="openCreateModal = false" class="px-4 py-2.5 text-xs font-semibold rounded-xl text-fg-muted hover:bg-raised dark:text-fg-muted dark:hover:bg-raised">
                                Cancel
                            </button>
                            <button type="submit" :disabled="!selectedStaff" class="px-5 py-2.5 text-xs font-semibold rounded-xl bg-accent hover:bg-accent disabled:opacity-50 text-accent-on transition ops-primary">
                                Generate Payslip
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 2. BULK RUN PAYROLL MODAL                                -->
        <!-- ======================================================== -->
        <div x-show="openBulkModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" @click="openBulkModal = false"></div>
            <div class="min-h-full flex items-center justify-center p-4">
                <div class="relative bg-surface dark:bg-surface rounded-xl max-w-md w-full p-6 border border-line dark:border-line space-y-5">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </span>
                        <div>
                            <h3 class="text-base font-semibold text-fg dark:text-white">Run Monthly Payroll</h3>
                            <p class="text-xs text-fg-muted">Auto-create drafts for {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}</p>
                        </div>
                    </div>

                    <p class="text-xs text-fg-muted dark:text-fg-muted leading-relaxed">
                        This will generate draft payslips for all <strong>{{ $metrics['eligible_staff_count'] }} eligible team members</strong> using their configured basic salaries. Any existing payslips for this month will be preserved.
                    </p>

                    <form method="POST" action="{{ route('app.payroll.bulk-generate') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                        @if($branchId)
                            <input type="hidden" name="branch_id" value="{{ $branchId }}">
                        @endif

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="openBulkModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl text-fg-muted hover:bg-raised dark:text-fg-muted dark:hover:bg-raised">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-semibold rounded-xl bg-accent hover:bg-accent text-accent-on transition ops-primary">
                                Start Payroll Run
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 3. MARK AS PAID / SETTLEMENT MODAL                      -->
        <!-- ======================================================== -->
        <div x-show="openPaymentModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" @click="openPaymentModal = false"></div>
            <div class="min-h-full flex items-center justify-center p-4">
                <div class="relative bg-surface dark:bg-surface rounded-xl max-w-md w-full p-6 border border-line dark:border-line space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-line dark:border-line">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-fg dark:text-white">Record Salary Settlement</h3>
                                <p class="text-xs text-fg-muted" x-text="activePayslip?.payslip_number"></p>
                            </div>
                        </div>
                        <button type="button" @click="openPaymentModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-fg">&times;</button>
                    </div>

                    <!-- Target Staff Summary -->
                    <div class="p-3.5 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-emerald-800 dark:text-emerald-400 block">Net Payout Amount</span>
                            <div class="text-lg font-semibold font-mono text-emerald-950 dark:text-emerald-200">
                                ₹<span x-text="Number(activePayslip?.net_salary || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold text-fg dark:text-white" x-text="activePayslip?.user?.name"></span>
                            <span class="text-xs text-fg-muted block" x-text="activePayslip?.upi_id || 'Direct Payout'"></span>
                        </div>
                    </div>

                    <!-- Form -->
                    <form :action="'/app/payroll/' + (activePayslip?.id || 0) + '/mark-paid'" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Settlement Method</label>
                            <select name="payment_method" class="w-full text-xs rounded-xl border border-line dark:border-line bg-raised dark:bg-surface p-2.5 text-fg dark:text-white font-semibold">
                                <option value="UPI">UPI (Google Pay / PhonePe / Paytm)</option>
                                <option value="BANK_TRANSFER">Bank Transfer (NEFT / IMPS / RTGS)</option>
                                <option value="CASH">Cash Settlement</option>
                                <option value="CHEQUE">Cheque</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Transaction Ref / UTR / Cheque No.</label>
                            <input type="text" name="transaction_reference" placeholder="E.g. UPI/20261125/9482019"
                                   class="w-full text-xs font-mono rounded-xl border border-line dark:border-line bg-raised dark:bg-surface p-2.5 text-fg dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Payment Date</label>
                            <input type="date" name="paid_at" value="{{ now()->toDateString() }}"
                                   class="w-full text-xs rounded-xl border border-line dark:border-line bg-raised dark:bg-surface p-2.5 text-fg dark:text-white font-semibold">
                        </div>

                        <!-- Auto-sync with Gym Expense checkbox -->
                        <label class="flex items-start gap-2.5 p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line cursor-pointer select-none">
                            <input type="checkbox" name="record_expense" value="1" checked class="mt-0.5 rounded border-line text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs text-fg-muted dark:text-fg-muted leading-snug">
                                Automatically record in Gym <strong>Expenses</strong> under <em>"Trainer &amp; Staff Salaries"</em>
                            </span>
                        </label>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="openPaymentModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl text-fg-muted hover:bg-raised dark:text-fg-muted dark:hover:bg-raised">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition">
                                Confirm Settlement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- Alpine.js Payroll Controller -->
    <script>
        function payrollManager(staffList) {
            return {
                staffList: staffList || [],
                staffSearch: '',
                selectedStaff: null,
                openCreateModal: false,
                openBulkModal: false,
                openPaymentModal: false,
                activePayslip: null,

                form: {
                    basic_salary: 0,
                    working_days: 30,
                    leaves_taken: 0,
                    allowances: [],
                    deductions: [],
                    bank_name: '',
                    upi_id: '',
                },

                get filteredStaff() {
                    if (!this.staffSearch.trim()) return this.staffList.slice(0, 15);
                    const q = this.staffSearch.toLowerCase().trim();
                    return this.staffList.filter(s => 
                        s.name.toLowerCase().includes(q) ||
                        s.phone.includes(q) ||
                        s.role.toLowerCase().includes(q)
                    ).slice(0, 15);
                },

                chooseStaff(st) {
                    this.selectedStaff = st;
                    this.form.basic_salary = Number(st.base_salary) || 0;
                    this.form.bank_name = st.bank_name || '';
                    this.form.upi_id = st.upi_id || '';
                    this.staffSearch = '';
                },

                addAllowance() {
                    this.form.allowances.push({ name: '', amount: 0 });
                },

                removeAllowance(idx) {
                    this.form.allowances.splice(idx, 1);
                },

                addDeduction() {
                    this.form.deductions.push({ name: '', amount: 0 });
                },

                removeDeduction(idx) {
                    this.form.deductions.splice(idx, 1);
                },

                calculateGross() {
                    const basic = Number(this.form.basic_salary) || 0;
                    const allw = this.form.allowances.reduce((acc, curr) => acc + (Number(curr.amount) || 0), 0);
                    return basic + allw;
                },

                calculateDeductions() {
                    return this.form.deductions.reduce((acc, curr) => acc + (Number(curr.amount) || 0), 0);
                },

                calculateNet() {
                    return Math.max(0, this.calculateGross() - this.calculateDeductions());
                },

                openPayModal(ps) {
                    this.activePayslip = ps;
                    this.openPaymentModal = true;
                }
            };
        }
    </script>
</x-app-layout>
