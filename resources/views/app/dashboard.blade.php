<x-app-layout header="Dashboard">
    @push('styles')
    <style>
        .dashboard-overview { container-type: inline-size; font-family: Inter, system-ui, sans-serif; font-size: 14px; line-height: 1.5; }
        .dashboard-overview .dashboard-actions { display:flex; flex-wrap:wrap; overflow:visible; margin-inline:0; padding-inline:0; }
        .dashboard-overview .dashboard-actions .ds-btn { min-height:36px; height:36px; font-size:14px; font-weight:500; }
        .dashboard-overview .ds-stat-grid > :is(a,button,div) { min-width:0; min-height:76px; padding:12px 14px; border-radius:0; box-shadow:none; }
        .dashboard-overview .ds-caption, .dashboard-overview .ds-caption .truncate { white-space:normal; overflow:visible; text-overflow:clip; letter-spacing:0; text-transform:none; font-size:12px; }
        .dashboard-overview .ds-kpi { font-size:24px; font-weight:600; letter-spacing:-.02em; line-height:1.35; white-space:normal; overflow-wrap:anywhere; }
        .dashboard-overview .ds-badge { border-radius:999px; }
        .dashboard-overview .ds-select { height:36px; }
        .dashboard-overview .ds-select:focus-visible { outline:none; border-color:rgb(var(--c-accent)); }
        .dashboard-overview .owner-metrics { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .dashboard-overview .owner-finance { grid-template-columns:minmax(0,1fr); }
        .dashboard-overview .owner-charts, .dashboard-overview .owner-lists { display:grid; grid-template-columns:minmax(0,1fr); gap:16px; }
        .dashboard-overview .owner-charts > *, .dashboard-overview .owner-lists > * { min-width:0; }
        .dashboard-overview .owner-chart { background:rgb(var(--c-surface)); border:1px solid rgb(var(--c-line)); border-radius:12px; padding:16px; }
        .dashboard-overview .owner-chart-plot { height:210px; position:relative; }
        .dashboard-overview .owner-list { padding:14px 16px; }
        .dashboard-overview .owner-list h4 { font-size:14px; font-weight:600; color:rgb(var(--c-fg)); }
        .dashboard-overview .owner-list-rows { max-height:252px; overflow-y:auto; }
        .dashboard-overview .owner-list-row { display:flex; align-items:center; justify-content:space-between; gap:8px; padding:10px 0; border-bottom:1px solid rgb(var(--c-line)); font-size:14px; }
        .dashboard-overview .owner-list-row:last-child { border-bottom:0; }
        .dashboard-overview .owner-list-row:hover { background:rgb(var(--c-raised)); }
        .dashboard-overview .owner-list a:not([href^="https://wa.me"]) { color:rgb(var(--c-accent-fg)); }
        .dashboard-overview .owner-list a:not([href^="https://wa.me"]):hover { text-decoration:underline; }
        .dashboard-overview .owner-list .owner-list-rows > div:only-child { border-bottom:0; }
        .dashboard-overview .owner-finance > div { padding:12px 14px; }
        .dashboard-overview .owner-finance .ds-caption { white-space:normal; }
        .dashboard-overview .owner-dues { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .dashboard-overview svg[stroke-width="2"], .dashboard-overview svg [stroke-width="2"], .dashboard-overview svg [stroke-width="2.5"] { stroke-width:1.75; }
        .dashboard-overview .owner-status-grid { display:grid; gap:10px; }
        .dashboard-overview .owner-status-card { --metric-tone:var(--c-fg-muted); display:flex; flex-direction:column; align-items:stretch; justify-content:center; gap:5px; min-width:0; min-height:104px; padding:12px 14px; text-align:left; background:rgb(var(--c-surface)); border:1px solid rgb(var(--c-line)); border-top:2px solid rgb(var(--metric-tone)); border-radius:12px; cursor:pointer; }
        .dashboard-overview .owner-status-card:hover { background:rgb(var(--c-raised)); border-color:rgb(var(--metric-tone) / .7); }
        .dashboard-overview .owner-tone-success { --metric-tone:var(--c-success); }
        .dashboard-overview .owner-tone-danger { --metric-tone:var(--c-danger); }
        .dashboard-overview .owner-tone-warning { --metric-tone:var(--c-warning); }
        .dashboard-overview .owner-tone-info { --metric-tone:var(--c-info); }
        .dashboard-overview .owner-status-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; color:rgb(var(--c-fg-muted)); font-size:12px; font-weight:500; }
        .dashboard-overview .owner-status-icon { color:rgb(var(--metric-tone)); }
        .dashboard-overview .owner-status-value { color:rgb(var(--metric-tone)); }
        .dashboard-overview .owner-tone-neutral .owner-status-value { color:rgb(var(--c-fg)); }
        .dashboard-overview .owner-status-description { font-size:12px; color:rgb(var(--c-fg-muted)); }
        .dashboard-overview .owner-finance > div { padding:14px 16px; gap:10px; }
        .dashboard-overview .owner-finance .finance-label { display:flex; align-items:center; gap:8px; font-size:14px; font-weight:500; color:rgb(var(--c-fg-muted)); }
        .dashboard-overview .owner-finance .finance-label svg { flex-shrink:0; }
        .dashboard-overview .owner-finance .finance-link { display:inline-flex; align-items:center; gap:5px; align-self:flex-start; }
        .dashboard-overview .owner-finance .finance-secondary .ds-kpi { font-size:20px; }
        .dashboard-overview .owner-finance .finance-collected .ds-kpi { color:rgb(var(--c-success)); }
        .dashboard-overview .owner-finance .finance-pending .ds-kpi { color:rgb(var(--c-warning)); }
        .dashboard-overview .owner-finance .finance-count { font-size:12px; color:rgb(var(--c-fg-muted)); margin-top:3px; }
        .dashboard-overview .owner-due-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
        .dashboard-overview .owner-due-card { --metric-tone:var(--c-fg-muted); display:flex; flex-direction:column; gap:7px; padding:12px 14px; min-width:0; background:rgb(var(--c-surface)); border:1px solid rgb(var(--c-line)); border-radius:12px; text-align:left; }
        .dashboard-overview .owner-due-card.owner-tone-warning { --metric-tone:var(--c-warning); border-top:2px solid rgb(var(--c-warning)); }
        .dashboard-overview .owner-due-card.owner-tone-danger { --metric-tone:var(--c-danger); border-top:2px solid rgb(var(--c-danger)); }
        .dashboard-overview .owner-due-card:hover { background:rgb(var(--c-raised)); border-color:rgb(var(--c-line-strong)); }
        .dashboard-overview .owner-due-meta { display:flex; flex-direction:column; align-items:flex-start; gap:5px; margin-top:auto; }
        .dashboard-overview .owner-due-count { color:rgb(var(--metric-tone)); background:rgb(var(--metric-tone) / .12); padding:3px 8px; border-radius:999px; font-size:12px; font-weight:500; }
        .dashboard-overview .owner-pt-header, .dashboard-overview .owner-pt-footer { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; padding:12px 16px; }
        .dashboard-overview .owner-pt-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1px; background:rgb(var(--c-line)); border-block:1px solid rgb(var(--c-line)); }
        .dashboard-overview .owner-pt-cell { --metric-tone:var(--c-fg-muted); background:rgb(var(--c-surface)); min-width:0; display:flex; flex-direction:column; gap:8px; padding:14px 16px; }
        .dashboard-overview .owner-pt-cell.owner-tone-success { --metric-tone:var(--c-success); }
        .dashboard-overview .owner-pt-cell.owner-tone-warning { --metric-tone:var(--c-warning); }
        .dashboard-overview .owner-whatsapp-action { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; flex-shrink:0; border-radius:8px; background:rgba(37,211,102,.12); color:#128C4B; border:1px solid rgba(37,211,102,.22); transition:background-color .15s ease,color .15s ease; }
        html.dark .dashboard-overview .owner-whatsapp-action { color:#25D366; }
        .dashboard-overview .owner-whatsapp-action:hover { background:#25D366; color:#10351F; }
        .dashboard-overview .owner-whatsapp-action:focus-visible { outline:2px solid #25D366; outline-offset:2px; }
        @container (min-width:800px) {
            .dashboard-overview .owner-due-grid, .dashboard-overview .owner-pt-grid { grid-template-columns:repeat(4,minmax(0,1fr)); }
        }
        @container (max-width:400px) {
            .dashboard-overview .owner-due-card, .dashboard-overview .owner-pt-cell { padding:10px 12px; }
            .dashboard-overview .owner-due-card .ds-kpi, .dashboard-overview .owner-pt-cell .ds-kpi { font-size:20px; }
        }
        @container (min-width:600px) {
            .dashboard-overview .owner-metrics { grid-template-columns:repeat(3,minmax(0,1fr)); }
            .dashboard-overview .owner-finance { grid-template-columns:repeat(3,minmax(0,1fr)); }
            .dashboard-overview .owner-charts, .dashboard-overview .owner-lists { grid-template-columns:repeat(2,minmax(0,1fr)); }
        }
        @container (min-width:1050px) {
            .dashboard-overview .owner-members { grid-template-columns:repeat(6,minmax(0,1fr)); }
            .dashboard-overview .owner-sales { grid-template-columns:repeat(5,minmax(0,1fr)); }
            .dashboard-overview .owner-finance { grid-template-columns:repeat(3,minmax(0,1fr)); }
            .dashboard-overview .owner-dues { grid-template-columns:repeat(4,minmax(0,1fr)); }
            .dashboard-overview .owner-lists { grid-template-columns:repeat(3,minmax(0,1fr)); }
        }
        @container (max-width:400px) {
            .dashboard-overview .dashboard-actions .ds-btn { flex:1 1 calc(50% - 8px); white-space:normal; padding-inline:8px; font-size:12px; }
            .dashboard-overview .owner-chart-plot { height:180px; }
        }
    </style>
    @endpush
    @php
        $currency = auth()->user()->tenant?->currency_symbol ?? '₹';
        $tenantName = $tenant->name ?? 'GymDeskHub';
        $greeting = (now()->hour < 12 ? 'Good morning' : (now()->hour < 17 ? 'Good afternoon' : 'Good evening'));
    @endphp

    <div x-data="{ 
            showChurnModal: false, 
            showExpiringModal: false, 
            showBirthdayModal: false,
            showAiSummaryModal: false,
            showTutorialsModal: false,
            finPeriod: 'mtd',
            finScope: 'all',
            trendPeriod: '30d',
            currency: {{ Js::from($currency) }},
            financialPeriods: {{ Js::from($metrics['financial_periods'] ?? []) }},
            trendsByPeriod: {{ Js::from($metrics['trends_by_period'] ?? []) }},
            getFin(key) {
                const p = this.financialPeriods[this.finPeriod] || {};
                const s = p[this.finScope] || p;
                return s[key] ?? 0;
            },
            getFinBreakdown(metric) {
                const p = this.financialPeriods[this.finPeriod] || {};
                const a = p['all'] || p;
                if (this.finScope === 'all') {
                    const g = a['gym_' + metric] ?? 0;
                    const pt = a['pt_' + metric] ?? 0;
                    if (pt > 0) {
                        return 'Gym ' + this.formatShort(g) + ' • PT ' + this.formatShort(pt);
                    }
                }
                return null;
            },
            getBilledUrl() {
                if (this.finScope === 'pt') {
                    return '{{ route('app.pt.index') }}';
                }
                return '{{ route('app.memberships.index') }}?date_filter=' + this.finPeriod;
            },
            getCollectedUrl() {
                let url = '{{ route('app.payments.index') }}?date_filter=' + this.finPeriod;
                if (this.finScope === 'pt') url += '&type=pt';
                else if (this.finScope === 'gym') url += '&type=gym';
                return url;
            },
            getPendingUrl() {
                if (this.finScope === 'pt') {
                    return '{{ route('app.pt.index') }}';
                }
                return '{{ route('app.members.index') }}?filter=due';
            },
            getNewClientsUrl() {
                return '{{ route('app.members.index') }}?filter=new&date_filter=' + this.finPeriod;
            },
            getRenewalsUrl() {
                return '{{ route('app.memberships.index') }}?date_filter=' + this.finPeriod;
            },
            formatShort(v) {
                const n = Number(v || 0);
                if (n >= 100000) return this.currency + (n / 100000).toFixed(2).replace(/\.00$/, '') + 'L';
                if (n >= 1000) return '₹' + (n / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
                return '₹' + n;
            },
            formatMoney(val) {
                const n = Number(val || 0);
                return this.currency + n.toLocaleString('en-IN', { maximumFractionDigits: 0 });
            },
            setTrendPeriod(p) {
                this.trendPeriod = p;
                if (typeof window.updateDashboardTrendCharts === 'function') {
                    window.updateDashboardTrendCharts(p);
                }
            }
         }" 
         class="dashboard-overview space-y-5 pb-8">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="ds-title truncate">{{ $tenantName }}</h1>
                    @if(auth()->user()->tenant?->isSubscriptionExpired())
                        <span class="ds-badge ds-badge-danger">Subscription expired</span>
                    @else
                        <span class="ds-badge ds-badge-success">Active</span>
                    @endif
                </div>
                <p class="text-sm text-fg-muted mt-1">{{ $greeting }}, {{ auth()->user()->name }}</p>
            </div>

            <div class="inline-flex items-center gap-2 h-8 px-3 rounded-lg border border-line bg-surface text-xs text-fg-muted self-start sm:self-auto">
                <x-icon name="building" :size="14" />
                <span class="truncate">Branch: <span class="text-fg font-medium">{{ $branch->name ?? 'Main branch' }}</span></span>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="dashboard-actions flex items-center gap-2 overflow-x-auto no-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0">
            @if(auth()->user()->hasPermission('members.create') || auth()->user()->isGymOwner())
                <a href="{{ route('app.members.create') }}" class="ds-btn ds-btn-primary shrink-0 whitespace-nowrap">
                    <x-icon name="user-add" :size="16" /><span>Add member</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('attendance.checkin') || auth()->user()->hasPermission('attendance.view') || auth()->user()->isGymOwner())
                <a href="{{ route('app.attendance.index') }}" class="ds-btn ds-btn-secondary shrink-0 whitespace-nowrap">
                    <x-icon name="check" :size="16" /><span>Mark attendance</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('payments.record') || auth()->user()->isGymOwner())
                <a href="{{ route('app.payments.index') }}" class="ds-btn ds-btn-secondary shrink-0 whitespace-nowrap">
                    <x-icon name="currency" :size="16" /><span>Record payment</span>
                </a>
            @endif

            @if(auth()->user()->hasFeature('crm_leads') && (auth()->user()->hasPermission('crm.leads.create') || auth()->user()->hasPermission('crm.view') || auth()->user()->isGymOwner()))
                <a href="{{ route('app.leads.index') }}" class="ds-btn ds-btn-secondary shrink-0 whitespace-nowrap">
                    <x-icon name="plus" :size="16" /><span>Add lead</span>
                </a>
            @endif

            @if(auth()->user()->hasFeature('inventory_stock') && (auth()->user()->hasPermission('inventory.create') || auth()->user()->hasPermission('inventory.view') || auth()->user()->isGymOwner()))
                <a href="{{ route('app.inventory.index') }}" class="ds-btn ds-btn-secondary shrink-0 whitespace-nowrap">
                    <x-icon name="cart" :size="16" /><span>Product sale</span>
                </a>
            @endif

            @if(auth()->user()->hasFeature('reports_finance') && (auth()->user()->hasPermission('reports.balance_sheet') || auth()->user()->hasPermission('expenses.view') || auth()->user()->isGymOwner()))
                <a href="{{ route('app.reports.index') }}" class="ds-btn ds-btn-secondary shrink-0 whitespace-nowrap">
                    <x-icon name="chart-bar" :size="16" /><span>Reports</span>
                </a>
                <a href="{{ route('app.expenses.index') }}" class="ds-btn ds-btn-secondary shrink-0 whitespace-nowrap">
                    <x-icon name="receipt" :size="16" /><span>Expenses</span>
                </a>
            @endif

            @if(auth()->user()->hasFeature('hikvision_iot') && (auth()->user()->hasPermission('devices.view') || auth()->user()->isGymOwner()))
                <a href="{{ route('app.settings.index', ['tab' => 'devices']) }}" class="ds-btn ds-btn-secondary shrink-0 whitespace-nowrap">
                    <x-icon name="fingerprint" :size="16" /><span>Biometric devices</span>
                </a>
            @endif

            <button type="button" @click="showTutorialsModal = true" class="ds-btn ds-btn-ghost shrink-0 whitespace-nowrap cursor-pointer">
                <x-icon name="book" :size="16" /><span>Quick guide</span>
            </button>
        </div>

        <!-- Members: health and retention -->
        @if(auth()->user()->hasPermission('dashboard.member_stats') || auth()->user()->isGymOwner())
        <section class="dashboard-metrics space-y-3" aria-label="Members">
            <h2 class="ds-heading">Members</h2>
            <div class="owner-status-grid owner-metrics owner-members">
                <a href="{{ route('app.members.index', ['status' => 'ACTIVE']) }}" class="owner-status-card owner-tone-success">
                    <span class="owner-status-heading"><span>Active members</span><x-icon name="check" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['active_members'] }}</span>
                    <span class="owner-status-description">Membership active</span>
                </a>
                <a href="{{ route('app.members.index', ['status' => 'EXPIRED']) }}" class="owner-status-card owner-tone-danger">
                    <span class="owner-status-heading"><span>Expired members</span><x-icon name="x" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['expired_members'] }}</span>
                    <span class="owner-status-description">Renewal required</span>
                </a>
                <button type="button" @click="showChurnModal = true" class="owner-status-card owner-tone-danger">
                    <span class="owner-status-heading"><span>Churn risk</span><x-icon name="alert" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['churn_risk'] }}</span>
                    <span class="owner-status-description">Needs attention</span>
                </button>
                <button type="button" @click="showExpiringModal = true" class="owner-status-card owner-tone-warning">
                    <span class="owner-status-heading"><span>Expiring soon</span><x-icon name="calendar" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['expiring_soon'] }}</span>
                    <span class="owner-status-description">Upcoming renewals</span>
                </button>
                <a href="{{ route('app.members.index', ['filter' => 'dormant']) }}" class="owner-status-card owner-tone-neutral">
                    <span class="owner-status-heading"><span>Dormant members</span><x-icon name="user" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['dormant_members'] }}</span>
                    <span class="owner-status-description">Inactive attendance</span>
                </a>
                <button type="button" @click="showBirthdayModal = true" class="owner-status-card owner-tone-info">
                    <span class="owner-status-heading"><span>Birthdays today</span><x-icon name="calendar" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['birthdays_today'] }}</span>
                    <span class="owner-status-description">Member engagement</span>
                </button>
            </div>
        </section>
        @endif

        {{-- Personal training --}}
        @if(auth()->user()->hasFeature('personal_training') && auth()->user()->hasPermission('pt.view'))
        <section class="space-y-3" aria-label="Personal training">
            <h2 class="ds-heading">Personal training</h2>
            <div class="owner-status-grid owner-metrics">
                <a href="{{ route('app.pt.index', ['tab' => 'packages', 'status' => 'ACTIVE']) }}" class="owner-status-card owner-tone-success">
                    <span class="owner-status-heading"><span>Active packages</span><x-icon name="check" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['active_pt'] }}</span>
                    <span class="owner-status-description">Currently active</span>
                </a>
                <a href="{{ route('app.pt.index', ['tab' => 'packages', 'status' => 'EXPIRED']) }}" class="owner-status-card owner-tone-danger">
                    <span class="owner-status-heading"><span>Expired packages</span><x-icon name="x" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['expired_pt'] }}</span>
                    <span class="owner-status-description">Renewal required</span>
                </a>
                <a href="{{ route('app.pt.index', ['tab' => 'packages', 'status' => 'COMPLETED']) }}" class="owner-status-card owner-tone-warning">
                    <span class="owner-status-heading"><span>Exhausted packages</span><x-icon name="clock" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['exhausted_pt'] }}</span>
                    <span class="owner-status-description">Sessions exhausted</span>
                </a>
            </div>
        </section>
        @endif

        <!-- Sales and CRM -->
        @if(auth()->user()->hasFeature('crm_leads') && auth()->user()->hasPermission('crm.view'))
        <section class="space-y-3" aria-label="Sales and CRM">
            <h2 class="ds-heading">Sales &amp; CRM</h2>
            <div class="owner-status-grid owner-metrics owner-sales">
                <a href="{{ route('app.leads.index') }}" class="owner-status-card owner-tone-success">
                    <span class="owner-status-heading"><span>Active leads</span><x-icon name="users" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['active_leads'] }}</span>
                    <span class="owner-status-description">Open leads</span>
                </a>
                <a href="{{ route('app.leads.index') }}" class="owner-status-card owner-tone-neutral">
                    <span class="owner-status-heading"><span>Today's enquiries</span><x-icon name="message" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['today_enquiries'] }}</span>
                    <span class="owner-status-description">New enquiries</span>
                </a>
                <a href="{{ route('app.leads.index') }}" class="owner-status-card owner-tone-warning">
                    <span class="owner-status-heading"><span>Follow-ups today</span><x-icon name="clock" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['today_followups'] }}</span>
                    <span class="owner-status-description">Scheduled today</span>
                </a>
                <a href="{{ route('app.leads.index') }}" class="owner-status-card owner-tone-danger">
                    <span class="owner-status-heading"><span>Overdue follow-ups</span><x-icon name="alert" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['overdue_followups'] }}</span>
                    <span class="owner-status-description">Needs attention</span>
                </a>
                <a href="{{ route('app.leads.index') }}" class="owner-status-card owner-tone-info">
                    <span class="owner-status-heading"><span>Upcoming trials</span><x-icon name="calendar" :size="18" class="owner-status-icon" /></span>
                    <span class="ds-kpi owner-status-value">{{ $metrics['upcoming_trials'] }}</span>
                    <span class="owner-status-description">Scheduled trials</span>
                </a>
            </div>
        </section>
        @endif

        <!-- Finance -->
        @if(auth()->user()->hasPermission('dashboard.financials') || auth()->user()->isGymOwner())
        <section class="space-y-4" aria-label="Finance">
            <div class="ds-card">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 p-4 border-b border-line">
                    <div class="min-w-0">
                        <h2 class="ds-heading">Financial overview</h2>
                        <p class="text-xs text-fg-muted mt-0.5" x-text="finScope === 'all' ? 'Memberships and personal training combined' : (finScope === 'pt' ? 'Personal training only' : 'Gym memberships only')">Memberships and personal training combined</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <div class="inline-flex p-0.5 rounded-lg bg-raised border border-line text-xs font-medium" role="group" aria-label="Scope">
                            <button type="button" @click="finScope = 'all'"
                                    :class="finScope === 'all' ? 'bg-surface text-fg border-line-strong' : 'text-fg-muted hover:text-fg border-transparent'"
                                    class="px-3 h-7 rounded-md border transition-colors cursor-pointer">All</button>
                            <button type="button" @click="finScope = 'gym'"
                                    :class="finScope === 'gym' ? 'bg-surface text-fg border-line-strong' : 'text-fg-muted hover:text-fg border-transparent'"
                                    class="px-3 h-7 rounded-md border transition-colors cursor-pointer">Gym</button>
                            <button type="button" @click="finScope = 'pt'"
                                    :class="finScope === 'pt' ? 'bg-surface text-fg border-line-strong' : 'text-fg-muted hover:text-fg border-transparent'"
                                    class="px-3 h-7 rounded-md border transition-colors cursor-pointer">PT</button>
                        </div>

                        <select x-model="finPeriod" aria-label="Financial overview period" class="ds-select !w-auto !text-sm cursor-pointer">
                            <option value="mtd">This month</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="last_month">Last month</option>
                            <option value="30d">Last 30 days</option>
                            <option value="90d">Last 90 days</option>
                            <option value="6m">Last 6 months</option>
                            <option value="ytd">This year</option>
                            <option value="all">All time</option>
                        </select>
                    </div>
                </div>

                <div class="grid owner-finance gap-px bg-line rounded-b-xl overflow-hidden">
                    <div class="bg-surface flex flex-col justify-between min-w-0 finance-billed">
                        <div class="min-w-0">
                            <div class="finance-label"><x-icon name="receipt" :size="18" /> <span>Billed</span></div>
                            <div class="ds-kpi mt-1" x-text="formatMoney(getFin('total_billed'))">{{ $currency }}{{ number_format($metrics['month_billed_total'] ?? $metrics['total_billed_amount'] ?? 0, 0) }}</div>
                            <div class="finance-count"><span class="tabular-nums" x-text="getFin('billed_count')">{{ $metrics['total_billed_count'] ?? 0 }}</span> billing records</div>
                            <div class="finance-count" x-show="getFinBreakdown('billed')" x-text="getFinBreakdown('billed')"></div>
                        </div>
                        <a :href="getBilledUrl()" class="finance-link text-xs font-medium text-accent-fg hover:underline">View details <x-icon name="arrow-right" :size="13" /></a>
                    </div>
                    <div class="bg-surface flex flex-col justify-between min-w-0 finance-collected">
                        <div class="min-w-0">
                            <div class="finance-label"><x-icon name="check" :size="18" class="text-success" /> <span>Collected</span></div>
                            <div class="ds-kpi mt-1" x-text="formatMoney(getFin('collected'))">{{ $currency }}{{ number_format($metrics['month_revenue'] ?? 0, 0) }}</div>
                            <div class="finance-count"><span class="tabular-nums" x-text="getFin('collected_count')">{{ $metrics['total_payments_count'] ?? 0 }}</span> payments</div>
                            <div class="finance-count" x-show="getFinBreakdown('collected')" x-text="getFinBreakdown('collected')"></div>
                        </div>
                        <a :href="getCollectedUrl()" class="finance-link text-xs font-medium text-accent-fg hover:underline">View details <x-icon name="arrow-right" :size="13" /></a>
                    </div>
                    <div class="bg-surface flex flex-col justify-between min-w-0 finance-pending">
                        <div class="min-w-0">
                            <div class="finance-label"><x-icon name="clock" :size="18" class="text-warning" /> <span>Pending</span></div>
                            <div class="ds-kpi mt-1" x-text="formatMoney(getFin('pending_dues'))">{{ $currency }}{{ number_format($metrics['all_dues_remaining'], 0) }}</div>
                            <div class="finance-count"><span class="tabular-nums" x-text="getFin('pending_count')">{{ $metrics['due_members_count'] }}</span> members with dues</div>
                            <div class="finance-count" x-show="getFinBreakdown('pending')" x-text="getFinBreakdown('pending')"></div>
                        </div>
                        <a :href="getPendingUrl()" class="finance-link text-xs font-medium text-accent-fg hover:underline">View details <x-icon name="arrow-right" :size="13" /></a>
                    </div>
                    <div class="bg-surface flex flex-col justify-between min-w-0 finance-product finance-secondary">
                        <div class="min-w-0">
                            <div class="finance-label"><x-icon name="cart" :size="18" /> <span>Product sales</span></div>
                            <div class="ds-kpi mt-1" x-text="formatMoney(getFin('product_sales'))">{{ $currency }}{{ number_format($metrics['product_sales_mtd'] ?? 0, 0) }}</div>
                        </div>
                        <a href="{{ route('app.inventory.index') }}" class="finance-link text-xs font-medium text-accent-fg hover:underline">View details <x-icon name="arrow-right" :size="13" /></a>
                    </div>
                    <div class="bg-surface flex flex-col justify-between min-w-0 finance-clients finance-secondary">
                        <div class="min-w-0">
                            <div class="finance-label"><x-icon name="user-add" :size="18" /> <span>New clients</span></div>
                            <div class="ds-kpi mt-1" x-text="getFin('new_clients')">{{ $metrics['new_clients_count'] }}</div>
                        </div>
                        <a :href="getNewClientsUrl()" class="finance-link text-xs font-medium text-accent-fg hover:underline">View details <x-icon name="arrow-right" :size="13" /></a>
                    </div>
                    <div class="bg-surface flex flex-col justify-between min-w-0 finance-renewals finance-secondary">
                        <div class="min-w-0">
                            <div class="finance-label"><x-icon name="refresh" :size="18" /> <span>Renewals</span></div>
                            <div class="ds-kpi mt-1" x-text="getFin('renewals')">{{ $metrics['renewals_count'] }}</div>
                        </div>
                        <a :href="getRenewalsUrl()" class="finance-link text-xs font-medium text-accent-fg hover:underline">View details <x-icon name="arrow-right" :size="13" /></a>
                    </div>
                </div>
            </div>

            <!-- Dues -->
            <section class="space-y-3" aria-label="Outstanding dues">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="ds-heading">Outstanding dues</h2>
                    <a href="{{ route('app.members.index', ['filter' => 'due']) }}" class="text-xs font-medium text-accent-fg hover:underline">View all dues <x-icon name="arrow-right" :size="13" class="inline-block align-middle" /></a>
                </div>
                <div class="owner-due-grid">
                    @php
                        $dueCells = [
                            ['All dues', $metrics['all_dues_remaining'], $metrics['due_members_count'], 'neutral', 'wallet', 'Total outstanding'],
                            ['This month', $metrics['month_dues_remaining'] ?? 0, $metrics['month_dues_count'] ?? 0, 'neutral', 'calendar', 'Current month'],
                            ['Due today', $metrics['today_dues_remaining'] ?? 0, $metrics['today_dues_count'] ?? 0, 'warning', 'clock', 'Requires attention today'],
                            ['Defaulters', $metrics['defaulters_dues_remaining'] ?? 0, $metrics['defaulters_count'] ?? 0, 'danger', 'alert', 'Overdue by more than 7 days'],
                        ];
                    @endphp
                    @foreach($dueCells as [$dueLabel, $dueAmount, $dueCount, $dueTone, $dueIcon, $dueDescription])
                        <a href="{{ route('app.members.index', ['filter' => 'due']) }}"
                           class="owner-due-card owner-tone-{{ ($dueCount > 0 || $dueAmount > 0) ? $dueTone : 'neutral' }}">
                            <span class="owner-status-heading">
                                <span>{{ $dueLabel }}</span>
                                <x-icon :name="$dueIcon" :size="18" class="owner-status-icon" />
                            </span>
                            <span class="ds-kpi owner-status-value">{{ $currency }}{{ number_format($dueAmount, 0) }}</span>
                            <span class="owner-due-meta">
                                <span class="owner-due-count tabular-nums">{{ $dueCount }} {{ $dueCount == 1 ? 'member' : 'members' }}</span>
                                <span class="owner-status-description">{{ $dueDescription }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        </section>
        @endif

        <!-- Personal training financial overview -->
        @php
            $canSeePtFinancials = auth()->user()->isGymOwner()
                || auth()->user()->isSuperAdmin();
        @endphp
        @if($canSeePtFinancials)
            @php
                $ptFin = $metrics['pt_financials'] ?? [
                    'month_collected' => 0,
                    'pending_dues' => 0,
                    'total_trainer_commission' => 0,
                    'total_gym_profit' => 0,
                ];
                $ptOverview = [
                    ['PT collection', $ptFin['month_collected'] ?? 0, 'wallet', 'success'],
                    ['Pending dues', $ptFin['pending_dues'] ?? 0, 'clock', ($ptFin['pending_dues'] ?? 0) > 0 ? 'warning' : 'neutral'],
                    ['Gym earnings', $ptFin['total_gym_profit'] ?? 0, 'building', 'neutral'],
                    ['Trainer payouts', $ptFin['total_trainer_commission'] ?? 0, 'users', 'neutral'],
                ];
            @endphp
            <section id="pt-financial-section" class="ds-card overflow-hidden" aria-label="Personal training financial overview">
                <div class="owner-pt-header">
                    <div class="flex items-center gap-2 min-w-0">
                        <x-icon name="users" :size="18" class="text-fg-muted" />
                        <h2 class="ds-heading">Personal training overview</h2>
                    </div>
                    <form method="GET" action="{{ route('app.dashboard') }}#pt-financial-section" class="flex items-center">
                        @if(request('branch_id'))
                            <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                        @endif
                        <select name="pt_month" aria-label="Personal training financial month" onchange="this.form.submit()" class="ds-select !w-auto">
                            @forelse($ptFin['available_months'] ?? [] as $mVal => $mLabel)
                                <option value="{{ $mVal }}" {{ ($ptFin['selected_month'] ?? now()->format('Y-m')) === $mVal ? 'selected' : '' }}>{{ $mLabel }}</option>
                            @empty
                                <option value="{{ $ptFin['selected_month'] ?? now()->format('Y-m') }}">{{ now()->format('F Y') }}</option>
                            @endforelse
                        </select>
                    </form>
                </div>
                <div class="owner-pt-grid">
                    @foreach($ptOverview as [$ptLabel, $ptAmount, $ptIcon, $ptTone])
                        <div class="owner-pt-cell owner-tone-{{ $ptTone }}">
                            <span class="owner-status-heading">
                                <span>{{ $ptLabel }}</span>
                                <x-icon :name="$ptIcon" :size="18" class="owner-status-icon" />
                            </span>
                            <span class="ds-kpi owner-status-value">{{ $currency }}{{ number_format($ptAmount, 0) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="owner-pt-footer">
                    <span class="ds-caption">Monthly personal training financials</span>
                    <a href="{{ route('app.pt.index') }}" class="finance-link text-xs font-medium text-accent-fg hover:underline inline-flex items-center gap-1">View PT details <x-icon name="arrow-right" :size="14" /></a>
                </div>
            </section>
        @endif

        <!-- ROW 5: TRENDS & INSIGHTS (NEW) 4 ANALYTICS CHARTS -->
        @php
            $canSeeRevTrends = auth()->user()->hasPermission('dashboard.financials') || auth()->user()->isGymOwner();
            $canSeeGrowthTrends = auth()->user()->hasPermission('dashboard.member_stats') || auth()->user()->isGymOwner();
            $canSeeAttTrends = auth()->user()->hasPermission('dashboard.attendance_stats') || auth()->user()->isGymOwner();
            $canSeeAnyTrends = $canSeeRevTrends || $canSeeGrowthTrends || $canSeeAttTrends;
        @endphp
        @if($canSeeAnyTrends)
        <div class="space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 sm:gap-2.5">
                    <svg class="w-5 h-5 text-fg-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    <h3 class="font-semibold text-fg text-sm sm:text-base">Trends and insights</h3>
                    
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <div class="flex items-center bg-raised p-0.5 sm:p-1 rounded-xl border border-line text-xs font-semibold">
                        <button type="button" @click="setTrendPeriod('30d')" :class="trendPeriod === '30d' ? 'bg-surface text-fg border border-line-strong' : 'text-fg-muted hover:text-fg border border-transparent'" class="px-2.5 sm:px-3 py-1 rounded-lg transition-all cursor-pointer text-xs">30 days</button>
                        <button type="button" @click="setTrendPeriod('90d')" :class="trendPeriod === '90d' ? 'bg-accent text-accent-on ' : 'text-fg-muted hover:text-fg '" class="px-2.5 sm:px-3 py-1 rounded-lg transition-all cursor-pointer text-xs">90 days</button>
                        <button type="button" @click="setTrendPeriod('6m')" :class="trendPeriod === '6m' ? 'bg-accent text-accent-on ' : 'text-fg-muted hover:text-fg '" class="px-2.5 sm:px-3 py-1 rounded-lg transition-all cursor-pointer text-xs">6 months</button>
                        <button type="button" @click="setTrendPeriod('1y')" :class="trendPeriod === '1y' ? 'bg-accent text-accent-on ' : 'text-fg-muted hover:text-fg '" class="px-2.5 sm:px-3 py-1 rounded-lg transition-all cursor-pointer text-xs">1 year</button>
                    </div>
                    <button type="button" @click="showAiSummaryModal = true" class="px-3 py-1.5 rounded-xl text-fg font-semibold text-xs flex items-center gap-1.5 hover:opacity-95 cursor-pointer">
                        <span>AI summary</span>
                    </button>
                </div>
            </div>

            <!-- 4 Charts Grid (Responsive layout based on role) -->
            <div class="owner-charts">

                <!-- Chart 1: Revenue -->
                @if($canSeeRevTrends)
                <div class="owner-chart space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-fg-muted block">Revenue</span>
                            <span class="text-xl sm:text-2xl font-semibold text-fg tabular-nums" x-text="formatMoney(trendsByPeriod[trendPeriod]?.total_revenue ?? {{ (float) ($metrics['month_revenue'] ?? 0) }})">{{ $currency }}{{ number_format($metrics['month_revenue'], 2) }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-raised text-fg-muted border border-line text-xs font-semibold">
                            Live revenue
                        </span>
                    </div>
                    <div class="owner-chart-plot">
                        <canvas id="chartRevenue"></canvas>
                    </div>
                </div>
                @endif

                <!-- Chart 2: Member growth -->
                @if($canSeeGrowthTrends)
                <div class="owner-chart space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-fg-muted block">Member growth</span>
                            <span class="text-xl sm:text-2xl font-semibold text-fg">{{ $metrics['active_members'] }} active</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-raised text-fg-muted border border-line text-xs font-semibold tabular-nums">
                            <span x-text="trendsByPeriod[trendPeriod]?.total_joins ?? {{ (int) ($metrics['new_clients_count'] ?? 0) }}">{{ $metrics['new_clients_count'] }}</span> joins
                        </span>
                    </div>
                    <div class="owner-chart-plot">
                        <canvas id="chartGrowth"></canvas>
                    </div>
                </div>
                @endif

                <!-- Chart 3: Daily attendance -->
                @if($canSeeAttTrends)
                <div class="owner-chart space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-fg-muted block">Daily attendance</span>
                            <span class="text-xl sm:text-2xl font-semibold text-fg">{{ $metrics['today_attendance'] }}/day</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-raised text-fg-muted border border-line text-xs font-semibold tabular-nums">
                            {{ !empty($metrics['avg_attendance_minutes']) ? 'Avg: ' . $metrics['avg_attendance_minutes'] . ' min' : 'Avg: —' }}
                        </span>
                    </div>
                    <div class="owner-chart-plot">
                        <canvas id="chartAttendance"></canvas>
                    </div>
                </div>
                @endif

                @if((auth()->user()->isGymOwner() || auth()->user()->isSuperAdmin()) && $canSeeRevTrends)
                <!-- Chart 4: Revenue vs expenses (Gym Owner Only) -->
                <div class="owner-chart space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-fg-muted block">Revenue vs expenses</span>
                            <span class="text-xl sm:text-2xl font-semibold text-fg">{{ $currency }}{{ number_format($metrics['net_profit'], 2) }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full {{ $metrics['net_profit'] >= 0 ? 'bg-raised text-fg-muted border border-line ' : 'bg-raised text-fg-muted border border-line ' }} text-xs font-semibold">
                            {{ $metrics['net_profit'] >= 0 ? 'Profitable' : 'Deficit' }}
                        </span>
                    </div>
                    <div class="owner-chart-plot">
                        <canvas id="chartRevVsExp"></canvas>
                    </div>
                </div>
                @endif

            </div>
        </div>
        @endif

        <!-- ROW 6: LIVE OPERATIONAL WIDGET CARDS (3x2 Grid) -->
        <div class="owner-lists">

            <!-- Widget 1: Expiring this week (with WhatsApp buttons) -->
            @if(auth()->user()->hasPermission('dashboard.member_stats') || auth()->user()->hasPermission('members.view') || auth()->user()->isGymOwner())
            <div class="ds-card owner-list flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-line">
                        <h4 class="font-semibold text-xs text-fg-muted flex items-center gap-1.5">
                            <span> Expiring this week</span>
                            <span class="px-1.5 py-0.2 rounded-full bg-raised text-fg-muted text-xs">({{ count($metrics['expiring_members_list']) }})</span>
                        </h4>
                        <div class="flex items-center gap-1.5">
                            <button type="button" class="px-2 py-1 rounded bg-raised text-fg-muted hover:bg-raised hover:text-fg text-xs font-semibold flex items-center gap-1 transition-all cursor-pointer">
                                <span> Send all</span>
                            </button>
                            <a href="{{ route('app.members.index', ['filter' => 'expiring']) }}" class="text-xs font-semibold text-fg-muted hover:text-fg">View all</a>
                        </div>
                    </div>

                    <div class="owner-list-rows">
                        @forelse($metrics['expiring_members_list'] as $exp)
                            <div class="owner-list-row">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    @if($exp->member?->avatar_url)
                                        <img src="{{ $exp->member->avatar_url }}" alt="{{ $exp->member->full_name }}" loading="lazy" class="w-7 h-7 rounded-full object-cover border border-line shrink-0">
                                    @else
                                        <div class="w-7 h-7 rounded-full bg-raised text-fg-muted flex items-center justify-center font-semibold text-xs shrink-0">
                                            {{ substr($exp->member?->first_name ?? 'M', 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-semibold text-fg leading-tight truncate">{{ $exp->member?->full_name }}</div>
                                        <div class="text-xs text-fg-muted truncate">{{ $exp->plan?->name ?? 'Standard' }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    @php
                                        $daysLeft = max(0, (int) round(\Carbon\Carbon::parse($exp->end_date)->startOfDay()->diffInDays(now()->startOfDay())));
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-raised text-fg-muted border border-line whitespace-nowrap">
                                        {{ $daysLeft }}d
                                    </span>
                                    @if($exp->member?->phone)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $exp->member->phone) }}?text=Hi%20{{ urlencode($exp->member->first_name) }},%20your%20gym%20membership%20at%20{{ urlencode($tenantName) }}%20is%20expiring%20on%20{{ $exp->end_date }}.%20Please%20renew%20to%20continue%20your%20workouts!"
                                           target="_blank"
                                           title="Send WhatsApp reminder" aria-label="Send WhatsApp reminder" rel="noopener noreferrer" class="owner-whatsapp-action">
                                            <svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2a9.92 9.92 0 0 0-8.56 14.94L2 22l5.22-1.37A9.95 9.95 0 1 0 12.04 2Zm0 1.72a8.23 8.23 0 0 1 0 16.46 8.2 8.2 0 0 1-4.18-1.14l-.3-.18-3.1.81.83-3.02-.2-.31a8.23 8.23 0 0 1 6.95-12.62Z"/><path d="M9.1 7.3c-.17-.4-.34-.41-.51-.42h-.44c-.16 0-.41.06-.63.3-.22.24-.83.81-.83 1.97s.85 2.29.97 2.45c.12.16 1.67 2.67 4.12 3.63 2.04.8 2.45.64 2.89.6.44-.04 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28l-1.67-.81c-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1.02-.38-1.94-1.2-.72-.64-1.2-1.43-1.34-1.67-.14-.24-.02-.37.1-.49.1-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42L9.1 7.3Z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-fg-muted text-xs">
                                <span>No memberships expiring this week.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <!-- Widget 2: Birthdays today -->
            @if(auth()->user()->hasPermission('dashboard.member_stats') || auth()->user()->hasPermission('members.view') || auth()->user()->isGymOwner())
            <div class="ds-card owner-list flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-line">
                        <h4 class="font-semibold text-xs text-fg-muted flex items-center gap-1.5">
                            <span> Birthdays today</span>
                            <span class="px-1.5 py-0.2 rounded-full bg-raised text-fg-muted text-xs">({{ count($metrics['birthday_members_list']) }})</span>
                        </h4>
                        <button type="button" class="text-xs font-semibold text-fg-muted hover:text-fg cursor-pointer"> Templates</button>
                    </div>

                    <div class="owner-list-rows">
                        @forelse($metrics['birthday_members_list'] as $bday)
                            <div class="owner-list-row">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-full bg-raised text-fg-muted flex items-center justify-center font-semibold text-xs shrink-0">

                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-fg leading-tight truncate">{{ $bday->full_name }}</div>
                                        <div class="text-xs text-fg-muted truncate">{{ $bday->phone ?? 'Member' }}</div>
                                    </div>
                                </div>
                                @if($bday->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $bday->phone) }}?text=Happy%20Birthday%20{{ urlencode($bday->first_name) }}!%20Wishing%20you%20a%20healthy%20and%20fit%20year%20ahead%20from%20{{ urlencode($tenantName) }}!%20" target="_blank" class="px-2 py-1 rounded bg-raised text-fg-muted hover:bg-raised hover:text-fg text-xs font-semibold flex items-center gap-1 transition-all shrink-0">
                                        <span>Wish</span>
                                    </a>
                                @endif
                            </div>
                        @empty
                            <div class="py-12 flex flex-col items-center justify-center text-center text-fg-muted text-xs">
                                <span class="text-2xl mb-1"></span>
                                <span>No birthdays today.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <!-- Widget 3: Recently expired -->
            @if(auth()->user()->hasPermission('dashboard.member_stats') || auth()->user()->hasPermission('members.view') || auth()->user()->isGymOwner())
            <div class="ds-card owner-list flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-line">
                        <h4 class="font-semibold text-xs text-fg-muted flex items-center gap-1.5">
                            <span> Recently expired</span>
                            <span class="px-1.5 py-0.2 rounded-full bg-raised text-fg-muted text-xs">({{ count($metrics['recently_expired_list']) }})</span>
                        </h4>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('app.members.index', ['status' => 'EXPIRED']) }}" class="text-xs font-semibold text-fg-muted hover:text-fg">View all</a>
                        </div>
                    </div>

                    <div class="owner-list-rows">
                        @forelse($metrics['recently_expired_list'] as $recExp)
                            <div class="owner-list-row">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    @if($recExp->member?->avatar_url)
                                        <img src="{{ $recExp->member->avatar_url }}" alt="{{ $recExp->member->full_name }}" loading="lazy" class="w-7 h-7 rounded-full object-cover border border-line shrink-0">
                                    @else
                                        <div class="w-7 h-7 rounded-full bg-raised text-fg-muted flex items-center justify-center font-semibold text-xs shrink-0">
                                            {{ substr($recExp->member?->first_name ?? 'M', 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-semibold text-fg leading-tight truncate">{{ $recExp->member?->full_name }}</div>
                                        <div class="text-xs text-fg-muted truncate">{{ $recExp->plan?->name ?? 'Membership' }}</div>
                                    </div>
                                </div>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $recExp->member?->phone ?? '') }}?text=Hi%20{{ urlencode($recExp->member?->first_name ?? 'there') }},%20we%20miss%20you%20at%20{{ urlencode($tenantName) }}!%20Get%20special%20renewal%20offers%20today." target="_blank" class="px-2 py-1 rounded bg-raised text-fg-muted hover:bg-raised hover:text-fg text-xs font-semibold transition-all shrink-0">
                                    Reach Out
                                </a>
                            </div>
                        @empty
                            <div class="py-12 flex flex-col items-center justify-center text-center text-fg-muted text-xs">
                                <div class="w-10 h-10 rounded-full bg-raised text-fg-muted flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>No recently expired memberships.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <!-- Widget 4: Recent payments -->
            @if(auth()->user()->hasPermission('dashboard.financials') || auth()->user()->hasPermission('payments.view') || auth()->user()->isGymOwner())
            <div class="ds-card owner-list flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-line">
                        <h4 class="font-semibold text-xs text-fg-muted flex items-center gap-1.5">
                            <span>₹ Recent payments</span>
                        </h4>
                        <a href="{{ route('app.payments.index') }}" class="text-xs font-semibold text-fg-muted hover:text-fg">View all</a>
                    </div>

                    <div class="owner-list-rows">
                        @forelse($metrics['recent_payments'] as $pay)
                            <div class="owner-list-row">
                                <div class="min-w-0">
                                    <div class="font-semibold text-fg-muted leading-tight truncate">{{ $pay->member?->full_name ?? 'Member Payment' }}</div>
                                    <div class="text-xs text-fg-muted truncate">{{ $pay->membership?->plan?->name ?? 'Plan Fee' }}</div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-semibold text-fg-muted">{{ $currency }}{{ number_format($pay->amount, 2) }}</div>
                                    <div class="text-xs font-medium text-fg-muted">{{ \Carbon\Carbon::parse($pay->payment_date)->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-fg-muted text-xs">No payments recorded yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <!-- Widget 5: Recent members -->
            @if(auth()->user()->hasPermission('dashboard.member_stats') || auth()->user()->hasPermission('members.view') || auth()->user()->isGymOwner())
            <div class="ds-card owner-list flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-line">
                        <h4 class="font-semibold text-xs text-fg-muted flex items-center gap-1.5">
                            <span> Recent members</span>
                        </h4>
                        <a href="{{ route('app.members.index') }}" class="text-xs font-semibold text-fg-muted hover:text-fg">View all</a>
                    </div>

                    <div class="owner-list-rows">
                        @forelse($metrics['recent_members_list'] as $rm)
                            <div class="owner-list-row">
                                <div class="min-w-0">
                                    <div class="font-semibold text-fg-muted leading-tight truncate">{{ $rm->full_name }}</div>
                                    <div class="text-xs text-fg-muted truncate">{{ $rm->phone }}</div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-fg text-xs font-semibold">{{ $rm->activeMembership?->plan?->name ?? 'Standard' }}</div>
                                    <div class="text-xs font-medium text-fg-muted">{{ $rm->created_at->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-fg-muted text-xs">No members enrolled yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <!-- Widget 6: Lead follow-ups -->
            @if(auth()->user()->hasFeature('crm_leads') && (auth()->user()->hasPermission('crm.view') || auth()->user()->isGymOwner()))
            <div class="ds-card owner-list flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-line">
                        <h4 class="font-semibold text-xs text-fg-muted flex items-center gap-1.5">
                            <span> Lead follow-ups</span>
                            @if($metrics['overdue_followups'] > 0)
                                <span class="px-1.5 py-0.2 rounded bg-raised text-fg-muted text-xs font-semibold">1 overdue</span>
                            @endif
                        </h4>
                        <a href="{{ route('app.leads.index') }}" class="text-xs font-semibold text-fg-muted hover:text-fg">View all</a>
                    </div>

                    <div class="owner-list-rows">
                        @forelse($metrics['lead_followups_list'] as $lead)
                            <div class="owner-list-row">
                                <div class="min-w-0">
                                    <div class="font-semibold text-fg-muted leading-tight truncate">{{ $lead->name }}</div>
                                    <div class="text-xs text-fg-muted truncate">{{ $lead->phone }}</div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    @if($lead->follow_up_date && \Carbon\Carbon::parse($lead->follow_up_date)->isPast())
                                        <span class="text-xs text-fg-muted font-semibold flex items-center gap-1">
                                             <span></span> {{ \Carbon\Carbon::parse($lead->follow_up_date)->format('d M') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-fg-muted font-medium">
                                             {{ $lead->follow_up_date ? \Carbon\Carbon::parse($lead->follow_up_date)->format('d M') : 'Pending' }}
                                        </span>
                                    @endif
                                    <a href="{{ route('app.leads.index') }}" class="px-2 py-1 rounded bg-raised hover:bg-raised text-fg text-xs font-semibold">
                                        Follow up
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-fg-muted text-xs">No pending lead follow-ups.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <!-- Widget 7: Due payments (Pending Balance) -->
            @if(auth()->user()->hasPermission('dashboard.financials') || auth()->user()->hasPermission('payments.view') || auth()->user()->isGymOwner())
            <div class="ds-card owner-list flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-line">
                        <h4 class="font-semibold text-xs text-fg-muted flex items-center gap-1.5">
                            <span> Due payments</span>
                            <span class="px-1.5 py-0.2 rounded-full bg-raised text-fg-muted text-xs">({{ count($metrics['due_members_list']) }})</span>
                        </h4>
                        <a href="{{ route('app.members.index', ['filter' => 'due']) }}" class="text-xs font-semibold text-fg-muted hover:text-fg">View all</a>
                    </div>

                    <div class="owner-list-rows">
                        @forelse($metrics['due_members_list'] as $dm)
                            <div class="owner-list-row">
                                <div class="min-w-0">
                                    <div class="font-semibold text-fg leading-tight truncate">{{ $dm->member?->full_name }}</div>
                                    <div class="text-xs text-fg-muted truncate">{{ $dm->plan?->name ?? 'Membership' }}</div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <div class="text-right">
                                        <div class="font-semibold text-fg-muted">{{ $currency }}{{ number_format($dm->due_amount ?? 0, 2) }}</div>
                                        <div class="text-xs font-medium text-fg-muted">Pending</div>
                                    </div>
                                    @if($dm->member?->phone)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $dm->member->phone) }}?text=Hi%20{{ urlencode($dm->member->first_name) }},%20this%20is%20a%20gentle%20reminder%20that%20your%20membership%20fee%20balance%20of%20{{ $currency }}{{ number_format($dm->due_amount ?? 0, 2) }}%20is%20pending%20at%20{{ urlencode($tenantName) }}.%20Please%20clear%20it%20at%20your%20earliest%20convenience." target="_blank" title="Send WhatsApp reminder" aria-label="Send WhatsApp reminder" rel="noopener noreferrer" class="owner-whatsapp-action">
                                            <svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2a9.92 9.92 0 0 0-8.56 14.94L2 22l5.22-1.37A9.95 9.95 0 1 0 12.04 2Zm0 1.72a8.23 8.23 0 0 1 0 16.46 8.2 8.2 0 0 1-4.18-1.14l-.3-.18-3.1.81.83-3.02-.2-.31a8.23 8.23 0 0 1 6.95-12.62Z"/><path d="M9.1 7.3c-.17-.4-.34-.41-.51-.42h-.44c-.16 0-.41.06-.63.3-.22.24-.83.81-.83 1.97s.85 2.29.97 2.45c.12.16 1.67 2.67 4.12 3.63 2.04.8 2.45.64 2.89.6.44-.04 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28l-1.67-.81c-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1.02-.38-1.94-1.2-.72-.64-1.2-1.43-1.34-1.67-.14-.24-.02-.37.1-.49.1-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42L9.1 7.3Z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-12 flex flex-col items-center justify-center text-center text-fg-muted text-xs">
                                <div class="w-10 h-10 rounded-full bg-raised text-fg-muted flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>All dues clear! No pending payments.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <!-- Widget 8: Recent renewals -->
            @if(auth()->user()->hasPermission('dashboard.financials') || auth()->user()->hasPermission('memberships.view') || auth()->user()->isGymOwner())
            <div class="ds-card owner-list flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-line">
                        <h4 class="font-semibold text-xs text-fg-muted flex items-center gap-1.5">
                            <span> Recent renewals</span>
                            <span class="px-1.5 py-0.2 rounded-full bg-raised text-fg-muted text-xs">({{ count($metrics['recent_renewals_list']) }})</span>
                        </h4>
                        <a href="{{ route('app.memberships.index') }}" class="text-xs font-semibold text-fg-muted hover:text-fg">View all</a>
                    </div>

                    <div class="owner-list-rows">
                        @forelse($metrics['recent_renewals_list'] as $rn)
                            <div class="owner-list-row">
                                <div class="min-w-0">
                                    <div class="font-semibold text-fg-muted leading-tight truncate">{{ $rn->member?->full_name }}</div>
                                    <div class="text-xs text-fg-muted truncate">{{ $rn->plan?->name ?? 'Standard Plan' }}</div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-semibold text-fg">{{ $currency }}{{ number_format($rn->final_amount ?: $rn->price, 2) }}</div>
                                    <div class="text-xs text-fg-muted font-semibold">{{ $rn->created_at->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-fg-muted text-xs">No renewals recorded this month yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <!-- Widget 9: Live attendance -->
            @if(auth()->user()->hasPermission('dashboard.attendance_stats') || auth()->user()->hasPermission('attendance.view') || auth()->user()->isGymOwner())
            <div class="ds-card owner-list flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-line">
                        <h4 class="font-semibold text-xs text-fg-muted flex items-center gap-1.5">
                            <span> Today's Check-ins</span>
                            <span class="px-1.5 py-0.2 rounded-full bg-raised text-fg-muted text-xs">({{ $metrics['today_attendance'] }})</span>
                        </h4>
                        <a href="{{ route('app.attendance.index') }}" class="text-xs font-semibold text-fg-muted hover:text-fg">View all</a>
                    </div>

                    <div class="owner-list-rows">
                        @forelse($metrics['recent_attendance'] as $att)
                            <div class="owner-list-row">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    @if($att->member?->avatar_url)
                                        <img src="{{ $att->member->avatar_url }}" alt="{{ $att->member->full_name }}" loading="lazy" class="w-7 h-7 rounded-full object-cover border border-line shrink-0">
                                    @else
                                        <div class="w-7 h-7 rounded-full bg-raised text-fg-muted flex items-center justify-center font-semibold text-xs shrink-0">
                                            {{ substr($att->member?->first_name ?? 'M', 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-semibold text-fg leading-tight truncate">{{ $att->member?->full_name }}</div>
                                        <div class="text-xs text-fg-muted truncate">{{ \Carbon\Carbon::parse($att->check_in)->format('h:i A') }}</div>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    @if($att->check_out)
                                        <span class="px-2 py-0.5 rounded-full bg-raised text-fg-muted text-xs font-semibold">Checked Out</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-raised text-fg-muted border border-line text-xs font-semibold">In Gym</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-fg-muted text-xs">No check-ins logged today yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <!-- Widget 10: Dormant members (>14 Days Absent) -->
            @if(auth()->user()->hasPermission('dashboard.member_stats') || auth()->user()->hasPermission('members.view') || auth()->user()->isGymOwner())
            <div class="ds-card owner-list flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-line">
                        <h4 class="font-semibold text-xs text-fg-muted flex items-center gap-1.5">
                            <span> Dormant members</span>
                            <span class="px-1.5 py-0.2 rounded-full bg-raised text-fg-muted text-xs">({{ count($metrics['dormant_members_list']) }})</span>
                        </h4>
                        <a href="{{ route('app.members.index', ['status' => 'ACTIVE']) }}" class="text-xs font-semibold text-fg-muted hover:text-fg">View all</a>
                    </div>

                    <div class="owner-list-rows">
                        @forelse($metrics['dormant_members_list'] as $dmMember)
                            <div class="owner-list-row">
                                <div class="min-w-0">
                                    <div class="font-semibold text-fg-muted leading-tight truncate">{{ $dmMember->full_name }}</div>
                                    <div class="text-xs text-fg-muted font-medium truncate">>14 days inactive</div>
                                </div>
                                @if($dmMember->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $dmMember->phone) }}?text=Hi%20{{ urlencode($dmMember->first_name) }},%20we%20miss%20you%20at%20{{ urlencode($tenantName) }}!%20Keep%20up%20your%20fitness%20streak%20and%20come%20in%20for%20a%20workout%20today!" target="_blank" class="px-2 py-1 rounded bg-raised text-fg-muted hover:bg-raised hover:text-fg text-xs font-semibold transition-all shrink-0">
                                        Re-engage
                                    </a>
                                @endif
                            </div>
                        @empty
                            <div class="py-8 text-center text-fg-muted text-xs">Great! All active members are attending regularly.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

        </div>

        <!-- ========================================================================= -->
        <!-- POPUP MODALS (Matching High Contrast Theme) -->
        <!-- ========================================================================= -->

        <!-- 1. Churn Risk Alert Modal -->
        <div x-show="showChurnModal"
             x-cloak
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-surface overflow-y-auto">
            <div @click.away="showChurnModal = false"
                 class="w-full max-w-2xl bg-surface border border-line rounded-xl sm:rounded-3xl overflow-hidden space-y-0 text-fg max-h-[90vh] flex flex-col">
                <!-- Modal Header -->
                <div class="p-4 sm:p-5 bg-raised border-b border-line flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-raised text-fg-muted flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm sm:text-base font-semibold text-fg-muted">Churn Risk Alert</h3>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-raised text-fg-muted">
                                    {{ count($metrics['churn_risk_members'] ?? $metrics['churn_risk_list'] ?? []) }} Members
                                </span>
                            </div>
                            <p class="text-xs  text-fg-muted font-medium truncate">Members inactive for 7+ days with membership expiring soon</p>
                        </div>
                    </div>
                    <button @click="showChurnModal = false" class="p-1.5 text-fg-subtle hover:text-fg rounded-lg cursor-pointer shrink-0"></button>
                </div>

                <!-- Modal Body -->
                <div class="p-4 sm:p-5 space-y-4 overflow-y-auto">
                    <!-- High Churn Info Box -->
                    <div class="p-3 sm:p-3.5 rounded-xl bg-raised border border-line text-xs flex items-start gap-2.5 sm:gap-3">
                        <div class="p-1 rounded-lg bg-raised text-fg-muted mt-0.5 shrink-0">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="space-y-1 text-fg-muted font-medium text-xs">
                            <p><strong class="text-fg-muted font-semibold">Why is this urgent?</strong> Inactivity right before renewal is the #1 indicator of dropouts. Reach out immediately to retain these members.</p>
                        </div>
                    </div>

                    <!-- Member Table List -->
                    <div class="overflow-x-auto max-h-80 rounded-xl border border-line">
                        <table class="w-full text-left text-xs min-w-[500px]">
                            <thead class="bg-raised border-b border-line text-fg-muted text-xs font-semibold sticky top-0">
                                <tr>
                                    <th class="py-2.5 px-3.5">Member Details</th>
                                    <th class="py-2.5 px-3.5">Current Plan</th>
                                    <th class="py-2.5 px-3.5">Expires</th>
                                    <th class="py-2.5 px-3.5 text-right">Quick Outreach</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line text-fg font-medium">
                                @forelse($metrics['churn_risk_members'] ?? $metrics['churn_risk_list'] ?? [] as $crm)
                                    <tr class="hover:bg-raised transition-colors">
                                        <td class="py-3 px-3.5">
                                            <div class="font-semibold text-fg text-xs">
                                                <a href="{{ route('app.members.show', $crm->member_id) }}" class="hover:text-fg-muted hover:underline">
                                                    {{ $crm->member?->full_name }}
                                                </a>
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="text-xs font-mono font-semibold text-fg-muted bg-raised px-1.5 py-0.2 rounded border border-line">{{ $crm->member?->member_code }}</span>
                                                <span class="text-xs text-fg-muted">{{ $crm->member?->phone }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3.5 text-fg-muted font-semibold text-xs">
                                            {{ $crm->plan?->name ?? 'Standard Plan' }}
                                        </td>
                                        <td class="py-3 px-3.5 text-fg-muted whitespace-nowrap text-xs">
                                            {{ \Carbon\Carbon::parse($crm->end_date)->format('d M Y') }}
                                        </td>
                                        <td class="py-3 px-3.5 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                @if($crm->member?->phone)
                                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $crm->member->phone) }}?text={{ urlencode('Hi ' . ($crm->member?->full_name ?? 'there') . ', we missed you at ' . ($tenantName ?? 'the gym') . '! Your membership expires on ' . \Carbon\Carbon::parse($crm->end_date)->format('d M') . '. Renew today for an exclusive loyalty discount!') }}"
                                                       target="_blank"
                                                       title="WhatsApp Outreach"
                                                       class="px-2.5 py-1.5 rounded-xl bg-raised text-fg-muted hover:bg-raised hover:text-fg font-semibold text-xs transition-all inline-flex items-center gap-1.5">
                                                        <span> WhatsApp</span>
                                                    </a>
                                                @endif
                                                <a href="{{ route('app.members.show', $crm->member_id) }}"
                                                   title="View Profile"
                                                   class="p-1.5 rounded-xl bg-raised text-fg-muted hover:bg-raised hover:text-fg transition-all inline-flex items-center">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-10 text-center text-fg-muted">
                                            <div class="w-12 h-12 rounded-full bg-raised text-fg-muted flex items-center justify-center mx-auto mb-2">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <p class="text-sm font-semibold text-fg">No churn risk members detected</p>
                                            <p class="text-xs text-fg-muted mt-0.5">All expiring members are attending regularly.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-3.5 sm:p-4 bg-raised border-t border-line flex items-center justify-between text-xs shrink-0">
                    <span class="text-fg-muted font-medium truncate">{{ count($metrics['churn_risk_members'] ?? $metrics['churn_risk_list'] ?? []) }} detected</span>
                    <a href="{{ route('app.members.index', ['filter' => 'churn']) }}" class="text-fg-muted font-semibold hover:underline flex items-center gap-1 shrink-0">
                        <span>View directory</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Expiring Soon Modal -->
        <div x-show="showExpiringModal"
             x-cloak
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-surface overflow-y-auto">
            <div @click.away="showExpiringModal = false"
                 class="w-full max-w-xl bg-surface border border-line rounded-xl sm:rounded-3xl overflow-hidden space-y-0 text-fg max-h-[90vh] flex flex-col">
                <!-- Modal Header -->
                <div class="p-4 sm:p-5 bg-raised border-b border-line flex items-start justify-between shrink-0">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-raised text-fg-muted flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm sm:text-base font-semibold text-fg-muted">Expiring Soon</h3>
                            <p class="text-xs  text-fg-muted font-medium truncate">Members expiring within 7 days</p>
                        </div>
                    </div>
                    <button @click="showExpiringModal = false" class="p-1.5 text-fg-subtle hover:text-fg rounded-lg cursor-pointer shrink-0"></button>
                </div>

                <!-- Modal Body: Member Table List -->
                <div class="p-4 overflow-x-auto max-h-96">
                    <table class="w-full text-left text-xs min-w-[460px]">
                        <thead class="bg-raised border-b border-line text-fg-muted text-xs font-semibold">
                            <tr>
                                <th class="py-2.5 px-3">Member</th>
                                <th class="py-2.5 px-3">Plan</th>
                                <th class="py-2.5 px-3">Expires</th>
                                <th class="py-2.5 px-3">Days</th>
                                <th class="py-2.5 px-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-fg font-medium">
                            @forelse($metrics['expiring_members_list'] as $exp)
                                <tr>
                                    <td class="py-2.5 px-3">
                                        <div class="font-semibold text-fg">{{ $exp->member?->full_name }}</div>
                                        <div class="text-xs text-fg-muted">{{ $exp->member?->phone }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-fg-muted font-semibold">{{ $exp->plan?->name ?? 'Standard Plan' }}</td>
                                    <td class="py-2.5 px-3 text-fg-muted whitespace-nowrap">{{ \Carbon\Carbon::parse($exp->end_date)->format('d M Y') }}</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        @php $dl = max(0, (int) round(\Carbon\Carbon::parse($exp->end_date)->startOfDay()->diffInDays(now()->startOfDay()))); @endphp
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-raised text-fg-muted whitespace-nowrap inline-block">
                                            {{ $dl }}d left
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        @if($exp->member?->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $exp->member->phone) }}" target="_blank" class="px-2 py-1 rounded bg-raised text-fg-muted hover:bg-raised hover:text-fg font-semibold text-xs transition-all">
                                                WhatsApp
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-fg-muted font-medium">No members expiring within 7 days.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Modal Footer -->
                <div class="p-3.5 sm:p-4 bg-raised border-t border-line flex items-center justify-between text-xs shrink-0">
                    <span class="text-fg-muted font-medium truncate">{{ count($metrics['expiring_members_list']) }} expiring</span>
                    <a href="{{ route('app.members.index', ['filter' => 'expiring']) }}" class="text-fg-muted font-semibold hover:underline flex items-center gap-1 shrink-0">
                        <span>View all</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 3. Birthdays Today Modal -->
        <div x-show="showBirthdayModal"
             x-cloak
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-surface overflow-y-auto">
            <div @click.away="showBirthdayModal = false"
                 class="w-full max-w-xl bg-surface border border-line rounded-xl sm:rounded-3xl overflow-hidden space-y-0 text-fg max-h-[90vh] flex flex-col">
                <!-- Modal Header -->
                <div class="p-4 sm:p-5 bg-raised border-b border-line flex items-start justify-between shrink-0">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-raised text-fg-muted flex items-center justify-center text-lg sm:text-xl shrink-0">

                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm sm:text-base font-semibold text-fg-muted">Birthdays Today</h3>
                            <p class="text-xs  text-fg-muted font-medium truncate">Celebrate and send wishes to members born today</p>
                        </div>
                    </div>
                    <button @click="showBirthdayModal = false" class="p-1.5 text-fg-subtle hover:text-fg rounded-lg cursor-pointer shrink-0"></button>
                </div>

                <!-- Modal Body: Member Table List -->
                <div class="p-4 overflow-x-auto max-h-96">
                    <table class="w-full text-left text-xs min-w-[420px]">
                        <thead class="bg-raised border-b border-line text-fg-muted text-xs font-semibold">
                            <tr>
                                <th class="py-2.5 px-3">Member</th>
                                <th class="py-2.5 px-3">Contact</th>
                                <th class="py-2.5 px-3">Date of Birth</th>
                                <th class="py-2.5 px-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-fg font-medium">
                            @forelse($metrics['birthday_members_list'] ?? [] as $bm)
                                <tr>
                                    <td class="py-2.5 px-3">
                                        <div class="font-semibold text-fg">{{ $bm->full_name }}</div>
                                        <div class="text-xs text-fg-muted font-mono font-semibold">{{ $bm->member_code }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-fg-muted">{{ $bm->phone }}</td>
                                    <td class="py-2.5 px-3 text-fg-muted">{{ $bm->dob ? \Carbon\Carbon::parse($bm->dob)->format('d M') : 'Today' }}</td>
                                    <td class="py-2.5 px-3">
                                        @if($bm->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $bm->phone) }}?text={{ urlencode('Wishing you a very Happy Birthday from all of us at ' . ($tenantName ?? 'GymDeskHub') . '!  Stay fit and healthy!') }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-raised text-fg-muted hover:bg-raised hover:text-fg font-semibold text-xs transition-all inline-flex items-center gap-1">
                                                <span> Send Wish</span>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-fg-muted font-medium">No member birthdays today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Modal Footer -->
                <div class="p-3.5 sm:p-4 bg-raised border-t border-line flex items-center justify-between text-xs shrink-0">
                    <span class="text-fg-muted font-medium truncate">{{ count($metrics['birthday_members_list'] ?? []) }} birthdays</span>
                    <a href="{{ route('app.members.index', ['filter' => 'birthday']) }}" class="text-fg-muted font-semibold hover:underline flex items-center gap-1 shrink-0">
                        <span>View in members</span>
                    </a>
                </div>
            </div>
        </div>


        <!-- 4. AI Executive Summary Modal -->
        @php
            $topPtTrainer = !empty($metrics['pt_financials']['trainers']) ? $metrics['pt_financials']['trainers'][0] : null;
            $collectionBase = max(1, (float) ($metrics['month_revenue'] ?? 0) + (float) ($metrics['all_dues_remaining'] ?? 0));
            $collectionRatePct = round(((float) ($metrics['month_revenue'] ?? 0) / $collectionBase) * 100);
        @endphp
        <div x-show="showAiSummaryModal"
             x-cloak
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-surface overflow-y-auto">
            <div @click.away="showAiSummaryModal = false"
                 class="w-full max-w-2xl bg-surface border border-line rounded-xl sm:rounded-3xl overflow-hidden text-fg max-h-[90vh] flex flex-col">
                <div class="p-4 sm:p-5 border-b border-line flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-fg">Gym Performance Summary</h3>
                        <p class="text-xs text-fg-muted mt-0.5">Live operational &amp; revenue snapshot for {{ $tenantName }}</p>
                    </div>
                    <button type="button" @click="showAiSummaryModal = false" class="p-1.5 text-fg-subtle hover:text-fg rounded-lg cursor-pointer"></button>
                </div>
                <div class="p-4 sm:p-6 space-y-3.5 overflow-y-auto text-xs sm:text-sm">
                    <div class="p-3.5 rounded-xl bg-raised border border-line space-y-1">
                        <div class="font-semibold text-fg-muted">Revenue &amp; Profitability</div>
                        <p class="text-fg-muted text-xs leading-relaxed">
                            This month's collection is <strong>{{ $currency }}{{ number_format($metrics['month_revenue'] ?? 0, 0) }}</strong> (including <strong>{{ $currency }}{{ number_format($metrics['product_sales_mtd'] ?? 0, 0) }}</strong> product sales) with net profit of <strong>{{ $currency }}{{ number_format($metrics['net_profit'] ?? 0, 0) }}</strong>. Collection efficiency stands at <strong>{{ $collectionRatePct }}%</strong>.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-raised border border-line space-y-1">
                        <div class="font-semibold text-fg-muted">Dues &amp; Renewals Priority</div>
                        <p class="text-fg-muted text-xs leading-relaxed">
                            <strong>{{ $metrics['due_members_count'] ?? 0 }}</strong> members have pending dues totaling <strong>{{ $currency }}{{ number_format($metrics['all_dues_remaining'] ?? 0, 0) }}</strong> (<strong>{{ $metrics['defaulters_count'] ?? 0 }}</strong> overdue &gt;7 days). <strong>{{ $metrics['expiring_soon'] ?? 0 }}</strong> memberships expire within 7 days and <strong>{{ $metrics['churn_risk'] ?? 0 }}</strong> are flagged for churn risk.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-raised border border-line space-y-1">
                        <div class="font-semibold text-fg-muted">Sales, Attendance &amp; Coaching</div>
                        <p class="text-fg-muted text-xs leading-relaxed">
                            Active base: <strong>{{ $metrics['active_members'] ?? 0 }}</strong> members with <strong>{{ $metrics['new_clients_count'] ?? 0 }}</strong> new joins this month. CRM has <strong>{{ $metrics['overdue_followups'] ?? 0 }}</strong> overdue follow-ups and <strong>{{ $metrics['today_followups'] ?? 0 }}</strong> scheduled today.
                            @if($topPtTrainer)
                                Top PT performer: <strong>{{ $topPtTrainer['name'] }}</strong> ({{ $currency }}{{ number_format($topPtTrainer['total_collected'], 0) }} collected across {{ $topPtTrainer['active_clients'] }} clients).
                            @endif
                        </p>
                    </div>
                </div>
                <div class="p-3.5 sm:p-4 bg-raised border-t border-line flex justify-end gap-2 shrink-0">
                    <a href="{{ route('app.members.index', ['filter' => 'due']) }}" class="px-3.5 py-2 rounded-xl bg-accent hover:bg-fg-muted text-slate-950 text-xs font-semibold">Follow Up Dues</a>
                    <button type="button" @click="showAiSummaryModal = false" class="px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold cursor-pointer">Close</button>
                </div>
            </div>
        </div>

        <!-- 5. Quick Guide / Tutorials Modal -->
        <div x-show="showTutorialsModal"
             x-cloak
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-surface overflow-y-auto">
            <div @click.away="showTutorialsModal = false"
                 class="w-full max-w-xl bg-surface border border-line rounded-xl sm:rounded-3xl overflow-hidden text-fg max-h-[90vh] flex flex-col">
                <div class="p-4 sm:p-5 bg-raised border-b border-line flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-fg">Gym Operations Quick Guide</h3>
                        <p class="text-xs text-fg-muted mt-0.5">Jump directly to common daily workflows</p>
                    </div>
                    <button type="button" @click="showTutorialsModal = false" class="p-1.5 text-fg-subtle hover:text-fg rounded-lg cursor-pointer"></button>
                </div>
                <div class="p-4 sm:p-5 space-y-2.5 overflow-y-auto text-xs">
                    <a href="{{ route('app.members.create') }}" class="block p-3 rounded-xl bg-raised border border-line hover:border-line transition-colors">
                        <div class="font-semibold text-fg">1. Onboard a New Member &amp; Collect Fee</div>
                        <div class="text-fg-muted mt-0.5">Create member profile, assign plan, discount, and record partial or full payment.</div>
                    </a>
                    <a href="{{ route('app.workouts.index') }}" class="block p-3 rounded-xl bg-raised border border-line hover:border-line transition-colors">
                        <div class="font-semibold text-fg">2. Build Workout Routines (Strength + Cardio/Timed)</div>
                        <div class="text-fg-muted mt-0.5">Mix Exercise Library video demos, custom movements, and timed cardio like Treadmill 15 min.</div>
                    </a>
                    <a href="{{ route('app.diets.index') }}" class="block p-3 rounded-xl bg-raised border border-line hover:border-line transition-colors">
                        <div class="font-semibold text-fg">3. Create Diet &amp; Nutrition Templates</div>
                        <div class="text-fg-muted mt-0.5">Combine Indian food library items with custom meals and macro targets.</div>
                    </a>
                    <a href="{{ route('app.settings.index', ['tab' => 'devices']) }}" class="block p-3 rounded-xl bg-raised border border-line hover:border-line transition-colors">
                        <div class="font-semibold text-fg">4. Connect Biometric / QR Attendance</div>
                        <div class="text-fg-muted mt-0.5">Configure Hikvision / Essl push webhook or use the Attendance QR check-in scanner.</div>
                    </a>
                </div>
                <div class="p-3.5 sm:p-4 bg-raised border-t border-line flex justify-end shrink-0">
                    <button type="button" @click="showTutorialsModal = false" class="px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold cursor-pointer">Done</button>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts Core Javascript Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart === 'undefined') {
                return;
            }

            // Chart colours come from the design tokens so charts follow the active theme.
            const token = (name, alpha = 1) => {
                const v = getComputedStyle(document.documentElement).getPropertyValue(name).trim().replace(/\s+/g, ' ');
                return 'rgb(' + v + ' / ' + alpha + ')';
            };
            Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
            Chart.defaults.font.size = 12;

            const chartConfig = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { color: token('--c-fg-subtle'), font: { size: 12 } } },
                    y: { grid: { color: token('--c-line') }, border: { display: false }, ticks: { color: token('--c-fg-subtle'), font: { size: 12 } }, beginAtZero: true }
                }
            };

            const trendsByPeriod = {{ Js::from($metrics['trends_by_period'] ?? []) }};
            const trendData = trendsByPeriod['30d'] || {{ Js::from($metrics['trends'] ?? ['labels' => [], 'revenue' => [], 'prev_revenue' => [], 'joins' => [], 'attendance' => []]) }};
            const monthlyChartData = {{ Js::from($metrics['chart'] ?? ['labels' => [], 'income' => [], 'expenses' => [], 'profit' => []]) }};
            window._dashCharts = {};

            // 1. Revenue Comparison Chart
            const ctxRev = document.getElementById('chartRevenue');
            if (ctxRev) {
                window._dashCharts.revenue = new Chart(ctxRev, {
                    type: 'line',
                    data: {
                        labels: trendData.labels || [],
                        datasets: [
                            {
                                label: 'Current Revenue',
                                data: trendData.revenue || [],
                                borderColor: token('--c-accent'),
                                backgroundColor: token('--c-accent', 0.08),
                                fill: false,
                                tension: 0.4,
                                borderWidth: 2.5,
                                pointRadius: 0
                            },
                            {
                                label: 'Previous Period',
                                data: trendData.prev_revenue || [],
                                borderColor: token('--c-fg-subtle'),
                                borderDash: [4, 4],
                                tension: 0.4,
                                borderWidth: 1.5,
                                pointRadius: 0
                            }
                        ]
                    },
                    options: chartConfig
                });
            }

            // 2. Member growth Chart
            const ctxGrowth = document.getElementById('chartGrowth');
            if (ctxGrowth) {
                window._dashCharts.growth = new Chart(ctxGrowth, {
                    type: 'line',
                    data: {
                        labels: trendData.labels || [],
                        datasets: [
                            {
                                label: 'New joins',
                                data: trendData.joins || [],
                                borderColor: token('--c-accent'),
                                borderWidth: 2,
                                pointRadius: 0,
                                tension: .25,
                                fill: false
                            }
                        ]
                    },
                    options: chartConfig
                });
            }

            // 3. Daily attendance Chart
            const ctxAtt = document.getElementById('chartAttendance');
            if (ctxAtt) {
                window._dashCharts.attendance = new Chart(ctxAtt, {
                    type: 'bar',
                    data: {
                        labels: trendData.labels || [],
                        datasets: [
                            {
                                label: 'Attendance',
                                data: trendData.attendance || [],
                                backgroundColor: token('--c-accent', 0.8),
                                borderRadius: 3,
                                maxBarThickness: 24
                            }
                        ]
                    },
                    options: chartConfig
                });
            }

            window.updateDashboardTrendCharts = function (period) {
                const next = trendsByPeriod[period] || trendData;
                if (window._dashCharts.revenue) {
                    window._dashCharts.revenue.data.labels = next.labels || [];
                    window._dashCharts.revenue.data.datasets[0].data = next.revenue || [];
                    window._dashCharts.revenue.data.datasets[1].data = next.prev_revenue || [];
                    window._dashCharts.revenue.update();
                }
                if (window._dashCharts.growth) {
                    window._dashCharts.growth.data.labels = next.labels || [];
                    window._dashCharts.growth.data.datasets[0].data = next.joins || [];
                    window._dashCharts.growth.update();
                }
                if (window._dashCharts.attendance) {
                    window._dashCharts.attendance.data.labels = next.labels || [];
                    window._dashCharts.attendance.data.datasets[0].data = next.attendance || [];
                    window._dashCharts.attendance.update();
                }
            };

            // 4. Revenue vs expenses Chart (6 Months)
            const ctxRevExp = document.getElementById('chartRevVsExp');
            if (ctxRevExp) {
                new Chart(ctxRevExp, {
                    type: 'bar',
                    data: {
                        labels: monthlyChartData.labels || [],
                        datasets: [
                            {
                                label: 'Revenue',
                                data: monthlyChartData.income || [],
                                backgroundColor: token('--c-accent', 0.85),
                                borderRadius: 4
                            },
                            {
                                label: 'Expenses',
                                data: monthlyChartData.expenses || [],
                                backgroundColor: token('--c-fg-subtle', 0.65),
                                borderRadius: 4
                            }
                        ]
                    },
                    options: chartConfig
                });
            }
        });
    </script>
</x-app-layout>
