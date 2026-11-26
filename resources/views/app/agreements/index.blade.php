<x-app-layout header="Digital Member Agreements & Health Declarations">
    @push('styles')
    <style>
        .agreement-workspace { --ag-surface:rgb(var(--c-surface)); --ag-line:rgb(var(--c-line)); --ag-muted:rgb(var(--c-fg-muted)); --ag-text:rgb(var(--c-fg)); --ag-soft:rgb(var(--c-raised)); }
        .agreement-page-header { display:flex; align-items:flex-end; justify-content:space-between; gap:24px; padding:8px 0 6px; }
        .agreement-eyebrow { font-size:11px; font-weight:600; letter-spacing:.12em; text-transform:uppercase; color:var(--ag-muted); margin-bottom:8px; }
        .agreement-page-header h1 { font-size:clamp(23px,2.4vw,30px); font-weight:600; letter-spacing:-.8px; line-height:1.25; color:var(--ag-text); }
        .agreement-description { font-size:13px; line-height:1.7; color:var(--ag-muted); margin-top:9px; }
        .agreement-header-actions { display:flex; flex-wrap:wrap; gap:8px; flex-shrink:0; }
        .agreement-button { display:inline-flex; align-items:center; justify-content:center; gap:9px; min-height:40px; padding:9px 14px; border:1px solid var(--ag-line); border-radius:7px; background:var(--ag-surface); color:var(--ag-text); font-size:12px; font-weight:600; white-space:nowrap; }
        .agreement-button:hover { background:var(--ag-soft); }
        .agreement-button-primary { background:rgb(var(--c-accent)); border-color:rgb(var(--c-accent)); color:#fff; }
        .agreement-button-primary:hover { background:rgb(var(--c-accent)); }
        .agreement-overview { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); border:1px solid var(--ag-line); border-radius:10px; background:var(--ag-surface); overflow:hidden; }
        .agreement-metric { padding:22px 24px; border-right:1px solid var(--ag-line); }
        .agreement-metric:last-child { border-right:0; }
        .agreement-metric>span { color:var(--ag-muted); font-size:12px; font-weight:500; }
        .agreement-metric strong { display:block; margin-top:8px; font-size:32px; line-height:1.2; letter-spacing:-1px; font-weight:600; font-variant-numeric:tabular-nums; color:var(--ag-text); }
        .agreement-metric p { margin-top:7px; font-size:11px; color:var(--ag-muted); }
        .agreement-metric a { display:inline-flex; gap:10px; margin-top:14px; color:var(--ag-muted); font-size:11px; }
        .agreement-metric a:hover { color:rgb(var(--c-accent)); }
        .agreement-table { min-width:1020px; }
        .agreement-table th { font-size:10px; font-weight:600; letter-spacing:.06em; padding-top:14px; padding-bottom:14px; }
        .agreement-table td { padding-top:18px; padding-bottom:18px; font-size:12px; }
        .agreement-table td:first-child { min-width:210px; }
        .agreement-table td:last-child a,.agreement-table td:last-child button { min-height:34px; min-width:34px; }
        .agreement-feedback { display:flex; justify-content:space-between; gap:16px; padding:12px 16px; border:1px solid var(--ag-line); border-left:3px solid rgb(var(--c-accent)); border-radius:6px; color:var(--ag-text); background:var(--ag-surface); font-size:13px; }
        .agreement-feedback.is-error { border-left-color:rgb(var(--c-danger)); }
        .agreement-workspace :focus-visible { outline:2px solid rgb(var(--c-accent)); outline-offset:3px; }
        .agreement-summary-heading { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:-12px; }
        .agreement-summary-heading h2 { color:var(--ag-text); font-size:15px; font-weight:600; }
        .agreement-summary-heading>span { font-size:12px; color:var(--ag-muted); }
        .agreement-overview { gap:14px; background:transparent; border:0; border-radius:0; overflow:visible; }
        .agreement-metric { position:relative; display:flex; flex-direction:column; padding:23px; border:1px solid var(--ag-line); border-radius:12px; background:var(--ag-surface); }
        .agreement-metric:last-child { border-right:1px solid var(--ag-line); }
        .metric-label { display:flex; align-items:center; justify-content:space-between; gap:10px; color:var(--ag-muted); font-size:13px; font-weight:500; }
        .metric-indicator { width:8px; height:8px; border-radius:50%; background:#8794a7; flex-shrink:0; }
        .metric-completed .metric-indicator { background:rgb(var(--c-success)); }
        .metric-pending .metric-indicator { background:rgb(var(--c-warning)); }
        .metric-health .metric-indicator { background:rgb(var(--c-danger)); }
        .metric-all { border-top:3px solid rgb(var(--c-accent)); padding-top:21px; }
        .agreement-metric strong { font-size:38px; margin-top:16px; }
        .agreement-metric p { font-size:12px; line-height:1.6; margin-top:8px; min-height:38px; }
        .agreement-metric a { font-size:12px; font-weight:500; padding-top:12px; margin-top:auto; border-top:1px solid var(--ag-line); justify-content:space-between; }
        .metric-progress { appearance:none; width:100%; height:4px; margin:0 0 12px; border:0; border-radius:4px; overflow:hidden; background:var(--ag-line); }
        .metric-progress::-webkit-progress-bar { background:var(--ag-line); }
        .metric-progress::-webkit-progress-value { background:rgb(var(--c-success)); }
        .metric-progress::-moz-progress-bar { background:rgb(var(--c-success)); }
        .agreement-row-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:8px; min-width:240px; }
        .agreement-table td:last-child a,.agreement-table td:last-child button,.agreement-table td:nth-child(5) button { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:44px; min-width:44px; padding:10px 14px; font-size:13px; font-weight:600; line-height:1.35; border-radius:8px; white-space:nowrap; }
        .agreement-table td:last-child a,.agreement-table td:last-child button { border:1px solid var(--ag-line); }
        .agreement-table td button svg,.agreement-table td:last-child a svg { width:17px; height:17px; flex-shrink:0; }
        .agreement-workspace .fixed button[type=submit] { min-height:44px; font-size:13px; }
        @media(max-width:1200px) { .agreement-page-header { flex-direction:column; align-items:flex-start; gap:18px; } }
        @media(max-width:640px) { .agreement-overview { grid-template-columns:repeat(2,minmax(0,1fr)); } .agreement-metric { padding:18px; border-bottom:1px solid var(--ag-line); } .agreement-metric:nth-child(2) { border-right:1px solid var(--ag-line); } .agreement-metric:nth-child(n+3) { border-bottom:1px solid var(--ag-line); } .agreement-header-actions { width:100%; } .agreement-header-actions .agreement-button-primary { flex:1; } .agreement-records { padding:12px; } }
        @media(max-width:640px) { .agreement-overview { gap:10px; } .agreement-metric { padding:16px; } .metric-all { padding-top:14px; } .metric-label { font-size:12px; } .agreement-metric strong { font-size:32px; } .agreement-summary-heading>span { display:none; } }
    </style>
    @endpush

    <div @keydown.escape.window="openQuickGenerateModal = false; openBulkModal = false; openHealthModal = false; openAuditModal = false" x-data="agreementManager()" class="agreement-workspace space-y-6">

        <header class="agreement-page-header">
            <div><p class="agreement-eyebrow">Member records</p><h1>Agreements &amp; health declarations</h1><p class="agreement-description">Track signatures, review health responses and manage member documents.</p></div>
            <div class="agreement-header-actions">
                @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('agreements.manage'))
                    <a href="{{ route('app.agreements.settings') }}" class="agreement-button">Manage terms</a>
                @endif
                <button type="button" @click="openBulkModal = true" class="agreement-button">Bulk generate</button>
                <button type="button" @click="openQuickGenerateModal = true" class="agreement-button agreement-button-primary"><span aria-hidden="true">+</span> Create agreement</button>
            </div>
        </header>
        <div x-cloak x-show="feedback" class="agreement-feedback" :class="{ 'is-error': feedbackError }" role="status"><span x-text="feedback"></span><button type="button" @click="feedback = ''" aria-label="Dismiss notification">&times;</button></div>
        <div class="agreement-summary-heading"><h2>Agreement overview</h2><span>Across all member records</span></div>
        <section class="agreement-overview" aria-label="Agreement overview">
            @foreach([
                ['label' => 'Total agreements', 'value' => $stats['total'] ?? 0, 'detail' => 'Generated member records', 'filter' => 'all'],
                ['label' => 'Signed', 'value' => $stats['completed'] ?? 0, 'detail' => ($stats['completion_rate'] ?? 0).'% of agreements', 'filter' => 'completed'],
                ['label' => 'Awaiting signature', 'value' => $stats['pending'] ?? 0, 'detail' => 'Follow up with members', 'filter' => 'pending'],
                ['label' => 'Health disclosures', 'value' => $stats['medical_review'] ?? 0, 'detail' => 'Includes reviewed declarations', 'filter' => null],
            ] as $metric)
                <div class="agreement-metric metric-{{ $metric['filter'] ?? 'health' }}"><div class="metric-label"><span>{{ $metric['label'] }}</span><span class="metric-indicator" aria-hidden="true"></span></div><strong>{{ number_format($metric['value']) }}</strong><p>{{ $metric['detail'] }}</p>
                    @if($metric['filter'] === 'completed')<progress class="metric-progress" max="100" value="{{ max(0, min(100, $stats['completion_rate'] ?? 0)) }}" aria-label="Signed agreements percentage"></progress>@endif
                    @if($metric['filter'])<a href="{{ route('app.agreements.index', ['status' => $metric['filter']]) }}" aria-label="View {{ strtolower($metric['label']) }}">View records <span aria-hidden="true">&rarr;</span></a>@endif
                </div>
            @endforeach
        </section>
        <!-- Filter Tabs & Search -->
        <div class="agreement-records bg-surface dark:bg-surface border border-line dark:border-line rounded-xl p-4 space-y-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <!-- Status Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 text-xs font-semibold">
                    <a href="{{ route('app.agreements.index', ['status' => 'all', 'search' => request('search')]) }}"
                       class="px-3.5 py-2 rounded-xl transition-colors whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        All ({{ $stats['total'] ?? 0 }})
                    </a>
                    <a href="{{ route('app.agreements.index', ['status' => 'pending', 'search' => request('search')]) }}"
                       class="px-3.5 py-2 rounded-xl transition-colors whitespace-nowrap {{ $statusFilter === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        Pending Signature ({{ $stats['pending'] ?? 0 }})
                    </a>
                    <a href="{{ route('app.agreements.index', ['status' => 'completed', 'search' => request('search')]) }}"
                       class="px-3.5 py-2 rounded-xl transition-colors whitespace-nowrap {{ $statusFilter === 'completed' ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        Completed ({{ $stats['completed'] ?? 0 }})
                    </a>
                    <a href="{{ route('app.agreements.index', ['status' => 'medical_review', 'search' => request('search')]) }}"
                       class="px-3.5 py-2 rounded-xl transition-colors whitespace-nowrap {{ $statusFilter === 'medical_review' ? 'bg-rose-600 text-white' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        Medical Review ({{ $stats['medical_review'] ?? 0 }})
                    </a>
                </div>

                <!-- Search Input -->
                <form method="GET" action="{{ route('app.agreements.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <div class="relative w-full md:w-72">
                        <svg class="w-4 h-4 text-fg-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" aria-label="Search agreements by member, phone or ID" placeholder="Search name, phone or member ID"
                               class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-line dark:border-line bg-raised dark:bg-surface text-fg dark:text-white placeholder-fg-subtle focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="agreement-button">Search</button>
                    @if(request('search'))
                    <a href="{{ route('app.agreements.index', ['status' => $statusFilter]) }}" class="px-2.5 py-2 rounded-xl text-xs font-semibold bg-raised hover:bg-slate-200 dark:bg-raised text-fg-muted dark:text-fg-muted">
                        Clear
                    </a>
                    @endif
                </form>
            </div>

            <!-- Agreements Table -->
            <div class="overflow-x-auto rounded-xl border border-line dark:border-line">
                <table class="agreement-table w-full text-left text-sm">
                    <thead class="bg-raised dark:bg-raised text-fg-muted font-semibold text-xs border-b border-line dark:border-line">
                        <tr>
                            <th class="py-3 px-4">Member</th>
                            <th class="py-3 px-3">Plan & Dates</th>
                            <th class="py-3 px-3">Version & Rules</th>
                            <th class="py-3 px-3">Agreement Status</th>
                            <th class="py-3 px-3">Health PAR-Q</th>
                            <th class="py-3 px-3">Timeline</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-line font-medium">
                        @forelse($agreements as $ag)
                        <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                            <!-- Member Profile -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-semibold flex items-center justify-center shrink-0 border border-indigo-200/50 overflow-hidden">
                                        @if($ag->member?->avatar_url)
                                            <img src="{{ $ag->member->avatar_url }}" alt="{{ $ag->member->full_name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($ag->member?->first_name ?? 'M', 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('app.members.show', $ag->member_id) }}" class="font-semibold text-fg dark:text-white hover:text-indigo-600 transition-colors block">
                                            {{ $ag->member?->full_name ?? 'Member #' . $ag->member_id }}
                                        </a>
                                        <div class="text-xs text-fg-muted flex items-center gap-2">
                                            <span class="font-mono text-indigo-600 dark:text-indigo-400 font-semibold">{{ $ag->member?->member_code ?? 'ID' }}</span>
                                            <span>•</span>
                                            <span>{{ $ag->member?->phone }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Membership Plan -->
                            <td class="py-3 px-3">
                                @if($ag->member?->activeMembership?->plan)
                                    <div class="font-semibold text-fg dark:text-fg">{{ $ag->member->activeMembership->plan->name }}</div>
                                    <div class="text-xs text-fg-muted">
                                        Expires: {{ $ag->member->activeMembership->end_date ? \Carbon\Carbon::parse($ag->member->activeMembership->end_date)->format('d M Y') : 'Ongoing' }}
                                    </div>
                                @else
                                    <span class="text-fg-muted italic">No Active Plan</span>
                                @endif
                            </td>

                            <!-- Version & Terrace Rules -->
                            <td class="py-3 px-3">
                                <div class="font-mono font-semibold text-fg-muted dark:text-fg-muted">v{{ $ag->version }}</div>
                                @if($ag->terrace_rules_included)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Terrace Clauses
                                    </span>
                                @else
                                    <span class="text-xs text-fg-muted">Standard</span>
                                @endif
                            </td>

                            <!-- Agreement Status -->
                            <td class="py-3 px-3">
                                @if($ag->isCompleted())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Signed & Completed
                                    </span>
                                @elseif($ag->requiresMedicalReview())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        Needs Review
                                    </span>
                                @elseif($ag->status === 'link_sent' || $ag->status === 'LINK_SENT')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                                        Link Sent
                                    </span>
                                @elseif(strtoupper((string) $ag->status) === 'DECLINED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-raised text-fg-muted dark:bg-raised dark:text-fg-muted">
                                        Declined
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                        Pending
                                    </span>
                                @endif
                            </td>

                            <!-- Health PAR-Q Flag -->
                            <td class="py-3 px-3">
                                @if($ag->has_medical_conditions)
                                    <button type="button" @click="viewHealthDetails({{ json_encode($ag) }})" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200/60 transition-colors">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Health disclosure
                                    </button>
                                @elseif($ag->health_declaration_accepted_at)
                                    <button type="button" @click="viewHealthDetails({{ json_encode($ag) }})" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 transition-colors">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        No conditions reported
                                    </button>
                                @else
                                    <span class="text-fg-muted text-xs italic">Not completed</span>
                                @endif
                            </td>

                            <!-- Timeline -->
                            <td class="py-3 px-3 text-fg-muted">
                                @if($ag->signed_at)
                                    <div class="font-semibold text-fg dark:text-fg">Signed</div>
                                    <div class="text-xs">{{ $ag->signed_at->format('d M Y, h:i A') }}</div>
                                @elseif($ag->link_sent_at)
                                    <div>Sent via {{ ucfirst($ag->link_sent_channel ?? 'link') }}</div>
                                    <div class="text-xs">{{ $ag->link_sent_at->format('d M Y, h:i A') }}</div>
                                @else
                                    <div>Created {{ $ag->created_at->format('d M Y') }}</div>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3 px-4 text-right">
                                <div class="agreement-row-actions">
                                    @if($ag->isCompleted())
                                        <!-- View / Download PDF Document -->
                                        <a href="{{ route('app.agreements.pdf', $ag->id) }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 transition"
                                           title="View Official Signed Document">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>View document</span>
                                        </a>

                                        <!-- View Agreement History -->
                                        <button type="button" @click="fetchAuditTrail({{ $ag->id }})"
                                                class="p-1.5 rounded-lg text-fg-muted hover:text-emerald-600 hover:bg-emerald-50 dark:text-fg-muted dark:hover:bg-raised border border-line dark:border-line transition"
                                                title="View Agreement History & SHA-256 Reference">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        <span>History</span>
                                        </button>
                                    @elseif($ag->requiresMedicalReview())
                                        <!-- Review Health Disclosures -->
                                        <button type="button" @click="viewHealthDetails({{ json_encode($ag) }})"
                                                class="px-2.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs transition-colors">
                                            Review health
                                        </button>
                                    @else
                                        <!-- 1-Click Copy Link -->
                                        <button type="button" @click="copyText('{{ $ag->share_url }}')"
                                                class="p-2 rounded-xl text-fg-muted hover:text-indigo-600 hover:bg-indigo-50 dark:text-fg-muted dark:hover:bg-raised transition-colors"
                                                title="Copy Direct Agreement Link">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                        <span>Copy link</span>
                                        </button>

                                        <!-- WhatsApp Direct Share -->
                                        <a href="{{ $ag->whatsapp_share_url }}" target="_blank"
                                           class="p-2 rounded-xl text-emerald-600 hover:bg-emerald-50 dark:hover:bg-raised transition-colors"
                                           title="Share via WhatsApp">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                        <span>WhatsApp</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-fg-muted">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-raised dark:bg-raised flex items-center justify-center text-fg-muted">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="font-semibold text-fg-muted dark:text-fg-muted">No member agreements found</div>
                                <p class="text-xs text-fg-muted mt-1">Generate a digital agreement link for new or existing members above.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($agreements->hasPages())
            <div class="pt-2">
                {{ $agreements->links() }}
            </div>
            @endif
        </div>

        <!-- 1. Quick Generate Modal with Real-time Search -->
        <div x-show="openQuickGenerateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" @click="openQuickGenerateModal = false"></div>
            <div class="min-h-full flex items-center justify-center p-4">
                <div class="relative bg-surface dark:bg-surface rounded-xl max-w-lg w-full p-6 sm:p-7 border border-line dark:border-line space-y-5">
                    
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-line dark:border-line">
                        <div class="flex items-center gap-3">
                            <span class="p-2.5 bg-accent text-accent-on rounded-xl ops-primary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-fg dark:text-white tracking-tight">Generate Member Agreement</h3>
                                <p class="text-xs text-fg-muted dark:text-fg-muted">Choose a member to create their signing link.</p>
                            </div>
                        </div>
                        <button type="button" @click="openQuickGenerateModal = false" class="p-1 rounded-xl text-fg-muted hover:text-fg-muted dark:hover:text-fg hover:bg-raised dark:hover:bg-raised transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- STATE A: No Member Selected (Search Mode) -->
                        <div x-show="!selectedMember" class="space-y-3">
                            <div class="flex items-center justify-between text-xs font-semibold text-fg-muted dark:text-fg-muted">
                                <span>Select Gym Member</span>
                                <span class="text-xs font-mono text-indigo-600 dark:text-indigo-400 font-semibold" x-text="(filteredMembers.length) + ' members found'"></span>
                            </div>

                            <!-- Live Search Input -->
                            <div class="relative">
                                <svg class="w-4 h-4 text-fg-muted absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text"
                                       x-model="memberSearch"
                                       placeholder="Type name, phone (e.g. 98765...), or Member ID (e.g. ARM133)..."
                                       class="w-full text-xs rounded-xl border border-line dark:border-line bg-raised dark:bg-surface pl-10 pr-9 py-3 text-fg dark:text-white placeholder-fg-subtle focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-accent transition">
                                <button type="button" x-show="memberSearch" @click="memberSearch = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-fg-muted hover:text-fg-muted dark:hover:text-fg text-xs font-semibold p-1">
                                    &times;
                                </button>
                            </div>

                            <!-- Scrollable Member Selection List -->
                            <div class="max-h-56 overflow-y-auto space-y-1.5 pr-1 divide-y divide-line dark:divide-line rounded-xl border border-line dark:border-line bg-raised dark:bg-slate-950/40 p-1.5">
                                <template x-for="m in filteredMembers" :key="m.id">
                                    <button type="button" @click="selectMember(m)"
                                            class="w-full text-left p-2.5 rounded-xl hover:bg-surface dark:hover:bg-raised flex items-center gap-3 transition group border border-transparent hover:border-line dark:hover:border-line">
                                        <div class="w-9 h-9 rounded-full bg-indigo-600 text-white font-semibold text-xs flex items-center justify-center shrink-0 overflow-hidden border border-indigo-400/30">
                                            <template x-if="m.photo">
                                                <img :src="m.photo" :alt="m.name" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!m.photo">
                                                <span x-text="m.initials"></span>
                                            </template>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-xs text-fg dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition" x-text="m.name"></span>
                                                <span class="font-mono text-xs font-semibold px-1.5 py-0.2 rounded bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800" x-text="m.code"></span>
                                            </div>
                                            <div class="text-xs text-fg-muted dark:text-fg-muted flex items-center gap-2 mt-0.5 truncate">
                                                <span x-text="m.phone"></span>
                                                <span>&bull;</span>
                                                <span class="truncate" x-text="m.plan"></span>
                                            </div>
                                        </div>
                                        <svg class="w-4 h-4 text-fg-muted group-hover:text-indigo-600 dark:group-hover:text-indigo-400 group-hover:translate-x-0.5 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </template>

                                <div x-show="filteredMembers.length === 0" class="p-6 text-center text-xs text-fg-muted space-y-1">
                                    <p>No members found matching "<span class="font-semibold text-fg-muted" x-text="memberSearch"></span>"</p>
                                    <button type="button" @click="memberSearch = ''" class="text-indigo-500 hover:underline font-semibold text-xs">Clear search</button>
                                </div>
                            </div>
                        </div>

                        <!-- STATE B: Member Selected (Summary Card) -->
                        <div x-show="selectedMember" class="space-y-3">
                            <div class="flex items-center justify-between text-xs font-semibold text-fg-muted dark:text-fg-muted">
                                <span>Selected Member</span>
                                <button type="button" @click="clearSelectedMember()" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Change Member</span>
                                </button>
                            </div>

                            <div class="p-4 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800/80 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white font-semibold text-sm flex items-center justify-center shrink-0 overflow-hidden border border-indigo-400/30">
                                        <template x-if="selectedMember?.photo">
                                            <img :src="selectedMember.photo" :alt="selectedMember.name" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!selectedMember?.photo">
                                            <span x-text="selectedMember?.initials"></span>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-semibold text-fg dark:text-white truncate" x-text="selectedMember?.name"></h4>
                                            <span class="font-mono text-xs font-semibold px-1.5 py-0.2 rounded bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300" x-text="selectedMember?.code"></span>
                                        </div>
                                        <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5 truncate" x-text="selectedMember?.phone + ' • ' + selectedMember?.plan"></p>
                                    </div>
                                </div>
                                <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            </div>
                        </div>

                        <!-- STATE C: Generated Link Ready -->
                        <div x-show="generatedResult" class="p-4 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800 space-y-3">
                            <div class="text-xs font-semibold text-emerald-950 dark:text-emerald-200 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Personalized Agreement Link Ready!</span>
                                </span>
                                <span class="text-xs bg-emerald-200/60 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200 px-2 py-0.5 rounded-full font-mono font-semibold">256-bit Token</span>
                            </div>
                            <input type="text" readonly :value="generatedResult?.share_url" class="w-full text-xs font-mono bg-surface dark:bg-surface border border-emerald-200 dark:border-emerald-800 rounded-xl px-3 py-2.5 text-fg dark:text-fg select-all">
                            
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <button type="button" @click="copyText(generatedResult?.share_url)" class="inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-surface hover:bg-raised dark:bg-surface dark:hover:bg-raised text-white dark:text-fg font-semibold text-xs transition active:scale-95">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    <span>Copy Link</span>
                                </button>
                                <a :href="generatedResult?.whatsapp_url" target="_blank" class="inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition active:scale-95">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    <span>WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Action Buttons -->
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-line dark:border-line">
                        <button type="button" @click="openQuickGenerateModal = false" class="px-4 py-2.5 text-xs font-semibold rounded-xl text-fg-muted hover:bg-raised dark:text-fg-muted dark:hover:bg-raised transition">
                            Close
                        </button>
                        <button type="button"
                                @click="generateLinkForMember()"
                                :disabled="!selectedMemberId || isGenerating"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold rounded-xl transition-all active:scale-95"
                                :class="selectedMemberId ? 'bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white shadow-indigo-500/25 cursor-pointer' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed'">
                            <svg x-show="!isGenerating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            <span x-text="isGenerating ? 'Generating Link...' : 'Generate Encrypted Link'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Bulk Generate Modal -->
        <div x-show="openBulkModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openBulkModal = false"></div>
            <div class="min-h-full flex items-center justify-center p-4">
                <form action="{{ route('app.agreements.bulk-generate') }}" method="POST" class="relative bg-surface dark:bg-surface rounded-xl max-w-md w-full p-6 border border-line dark:border-line space-y-5">
                    @csrf
                    <div class="flex items-center justify-between pb-3 border-b border-line dark:border-line">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-fg dark:text-white">Bulk Generate Agreements</h3>
                                <p class="text-xs text-fg-muted">Batch generate for existing enrolled members</p>
                            </div>
                        </div>
                        <button type="button" @click="openBulkModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-fg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="p-3.5 bg-raised dark:bg-surface rounded-xl border border-line dark:border-line">
                            <div class="font-semibold text-fg dark:text-white">Members in this batch</div>
                            <div class="text-fg-muted mt-1">Found <strong class="text-indigo-600">{{ count($unsentMembers) }}</strong> members available. Existing active agreements are reused.</div>
                        </div>

                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Dispatch Mode</label>
                            <select name="channel" class="w-full rounded-xl border border-line dark:border-line bg-raised dark:bg-surface p-2.5 text-fg dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="manual_copy">Manual Copy (Prepare links for front desk sharing)</option>
                                <option value="whatsapp">WhatsApp Link Batch</option>
                                <option value="sms">SMS Notification</option>
                            </select>
                        </div>

                        <!-- Hidden array of IDs -->
                        @foreach($unsentMembers as $um)
                            <input type="hidden" name="member_ids[]" value="{{ $um->id }}">
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="openBulkModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl text-fg-muted hover:bg-raised dark:text-fg-muted dark:hover:bg-raised">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white" {{ count($unsentMembers) === 0 ? 'disabled' : '' }}>
                            Generate {{ count($unsentMembers) }} Agreement Links
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. Health Declaration PAR-Q Details Modal -->
        <div x-show="openHealthModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openHealthModal = false"></div>
            <div class="min-h-full flex items-center justify-center p-4">
                <div class="relative bg-surface dark:bg-surface rounded-xl max-w-lg w-full p-6 border border-line dark:border-line space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-line dark:border-line">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 bg-rose-50 dark:bg-rose-950/60 text-rose-600 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-fg dark:text-white" x-text="activeAgreementModal?.member?.first_name + '\'s Health Declaration'"></h3>
                                <p class="text-xs text-fg-muted">Member-reported health information</p>
                            </div>
                        </div>
                        <button type="button" aria-label="Close health declaration" @click="openHealthModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-fg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
                        <template x-for="(ans, idx) in (activeAgreementModal?.health_declaration_answers || [])" :key="idx">
                            <div class="p-3.5 rounded-xl border text-xs" :class="ans.answer === 'yes' ? 'bg-rose-50/70 border-rose-200 text-rose-950 dark:bg-rose-950/30 dark:border-rose-900 dark:text-rose-200' : 'bg-slate-50 border-slate-200/80 text-slate-700 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-300'">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="font-semibold flex-1" x-text="(idx + 1) + '. ' + ans.question"></div>
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold shrink-0" :class="ans.answer === 'yes' ? 'bg-rose-600 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" x-text="ans.answer"></span>
                                </div>
                                <div x-show="ans.answer === 'yes' && ans.details" class="mt-2 pt-2 border-t border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300">
                                    <strong>Member Disclosure:</strong> <span x-text="ans.details"></span>
                                </div>
                            </div>
                        </template>

                        <!-- Approval Action if Medical Review Required -->
                        <div x-show="['medical_review', 'MEDICAL_REVIEW_REQUIRED'].includes(activeAgreementModal?.status)" class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800 space-y-3">
                            <div class="text-xs font-semibold text-amber-900 dark:text-amber-200">
                                Staff review
                            </div>
                            <p class="text-xs text-amber-800 dark:text-amber-300">
                                Review the member's responses and record any follow-up or medical advice received. This records a staff review, not medical clearance.
                            </p>
                            <form :action="'/app/agreements/' + activeAgreementModal?.id + '/medical-review'" method="POST" class="space-y-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Review notes and any advice received from a qualified clinician" class="w-full text-xs rounded-xl border border-amber-300 dark:border-amber-700 bg-surface dark:bg-surface p-2 text-fg dark:text-white">
                                <button type="submit" class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold">
                                    Record review
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-2">
                        <button type="button" @click="openHealthModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl text-fg-muted hover:bg-raised dark:text-fg-muted dark:hover:bg-raised">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Agreement History Modal -->
        <div x-show="openAuditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openAuditModal = false"></div>
            <div class="min-h-full flex items-center justify-center p-4">
                <div class="relative bg-surface dark:bg-surface rounded-xl max-w-xl w-full p-6 border border-line dark:border-line space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-line dark:border-line">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-fg dark:text-white">Agreement history</h3>
                                <p class="text-xs text-fg-muted">Signature details and recorded activity</p>
                            </div>
                        </div>
                        <button type="button" aria-label="Close agreement history" @click="openAuditModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-fg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <!-- Evidence Card -->
                        <div class="p-4 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line space-y-2.5">
                            <div class="flex justify-between items-center py-1 border-b border-line dark:border-line">
                                <span class="text-fg-muted">Transaction Reference</span>
                                <span class="font-mono font-semibold text-indigo-600 dark:text-indigo-400" x-text="auditData?.reference"></span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-line dark:border-line">
                                <span class="text-fg-muted">Digital Signature Timestamp</span>
                                <span class="font-semibold text-fg dark:text-fg" x-text="auditData?.signed_at"></span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-line dark:border-line">
                                <span class="text-fg-muted">Signer IP Address</span>
                                <span class="font-mono text-fg dark:text-fg" x-text="auditData?.signer_ip"></span>
                            </div>
                            <div>
                                <span class="text-fg-muted block mb-1">SHA-256 Checksum Hash</span>
                                <div class="p-2 rounded bg-raised dark:bg-surface font-mono text-xs break-all text-fg-muted dark:text-fg-muted" x-text="auditData?.hash"></div>
                            </div>
                        </div>

                        <!-- Audit Events Timeline -->
                        <div>
                            <div class="font-semibold text-fg-muted dark:text-fg-muted mb-2">Activity</div>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="(ev, idx) in (auditData?.audit_trail || [])" :key="idx">
                                    <div class="flex items-start gap-2.5 p-2 rounded-xl bg-raised dark:bg-surface text-xs">
                                        <div class="w-2 h-2 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                                        <div class="flex-1">
                                            <div class="font-semibold text-fg dark:text-fg text-xs" x-text="ev.event?.replace('_', ' ')"></div>
                                            <div class="text-fg-muted" x-text="ev.timestamp"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-2">
                        <button type="button" @click="openAuditModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl text-fg-muted hover:bg-raised dark:text-fg-muted dark:hover:bg-raised">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>




    <!-- Alpine.js Page Controller -->
    <script>
        function agreementManager() {
            return {
                openQuickGenerateModal: false,
                openBulkModal: false,
                openHealthModal: false,
                openAuditModal: false,
                membersList: @json($membersJson ?? []),
                memberSearch: '',
                selectedMember: null,
                selectedMemberId: '',
                isGenerating: false,
                generatedResult: null,
                activeAgreementModal: null,
                auditData: null,
                feedback: '',
                feedbackError: false,

                get filteredMembers() {
                    if (!this.memberSearch || !this.memberSearch.trim()) {
                        return this.membersList.slice(0, 40);
                    }
                    const q = this.memberSearch.toLowerCase().trim();
                    return this.membersList.filter(m =>
                        (m.name && m.name.toLowerCase().includes(q)) ||
                        (m.code && m.code.toLowerCase().includes(q)) ||
                        (m.phone && m.phone.toLowerCase().includes(q)) ||
                        (m.plan && m.plan.toLowerCase().includes(q))
                    ).slice(0, 40);
                },

                selectMember(m) {
                    this.selectedMember = m;
                    this.selectedMemberId = m.id;
                    this.memberSearch = '';
                    this.generatedResult = null;
                },

                clearSelectedMember() {
                    this.selectedMember = null;
                    this.selectedMemberId = '';
                    this.memberSearch = '';
                    this.generatedResult = null;
                },

                copyText(text) {
                    if (!text) return;
                    navigator.clipboard.writeText(text).then(() => {
                        this.feedbackError = false; this.feedback = 'Agreement link copied.';
                    }).catch(() => { this.feedbackError = true; this.feedback = 'Copy failed. Open the agreement and copy its address.'; });
                },

                generateLinkForMember() {
                    if (!this.selectedMemberId) return;
                    this.isGenerating = true;

                    fetch('/app/agreements/generate/' + this.selectedMemberId, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(async res => { const data = await res.json(); if (!res.ok || !data.success) throw new Error(data.message || 'Request failed'); return data; })
                    .then(data => {
                        this.isGenerating = false;
                        if (data.success) {
                            this.generatedResult = data;
                        }
                    })
                    .catch(err => {
                        this.isGenerating = false;
                        this.feedbackError = true; this.feedback = 'Could not generate the agreement. Please try again.';
                    });
                },

                viewHealthDetails(agreement) {
                    const questions = agreement.template?.health_questions || [];
                    const answers = agreement.health_declaration_answers || {};
                    this.activeAgreementModal = { ...agreement, health_declaration_answers: Object.entries(answers).map(([id, answer]) => ({
                        ...answer,
                        question: answer.question || questions.find(question => question.id === id)?.question || id.replaceAll('_', ' '),
                        details: answer.explanation || answer.details || ''
                    })) };
                    this.openHealthModal = true;
                },

                fetchAuditTrail(agreementId) {
                    fetch('/app/agreements/' + agreementId + '/audit', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(async res => { const data = await res.json(); if (!res.ok || !data.success) throw new Error(data.message || 'Request failed'); return data; })
                    .then(data => {
                        if (data.success) {
                            this.auditData = data;
                            this.openAuditModal = true;
                        }
                    }).catch(() => { this.feedbackError = true; this.feedback = 'Could not load agreement history. Please try again.'; });
                }
            }
        }
    </script>
</x-app-layout>
