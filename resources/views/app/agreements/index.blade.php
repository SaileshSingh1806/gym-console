<x-app-layout header="Digital Member Agreements & Health Declarations">
    <div x-data="agreementManager()" class="space-y-6">

        <!-- Top Header & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                    <span class="p-2 bg-indigo-600/10 text-indigo-600 dark:bg-indigo-400/10 dark:text-indigo-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    Digital Member Agreements & Health
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Manage paperless member onboarding, PAR-Q health declarations, electronic signatures, and legal compliance.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <button type="button" @click="openQuickGenerateModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-sm shadow-indigo-500/20 transition-all hover:scale-[1.01]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    <span>Generate Member Link</span>
                </button>

                <button type="button" @click="openBulkModal = true" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Bulk Generate</span>
                </button>

                @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('agreements.manage'))
                <a href="{{ route('app.agreements.settings') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Settings & Terms</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Total Generated -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <span>Total Generated</span>
                    <span class="p-1.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-600 dark:text-slate-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-2">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="text-xs text-slate-500 mt-1">Current Active Version: v{{ $activeTemplate->version }}</div>
            </div>

            <!-- Completed & Signed -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                    <span>Signed & Completed</span>
                    <span class="p-1.5 bg-emerald-50 dark:bg-emerald-950/40 rounded-lg text-emerald-600 dark:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ number_format($stats['completed'] ?? 0) }}</div>
                <div class="text-xs text-emerald-700 dark:text-emerald-500 font-semibold mt-1">{{ $stats['completion_rate'] ?? 0 }}% completion rate</div>
            </div>

            <!-- Pending Signature -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">
                    <span>Pending Signature</span>
                    <span class="p-1.5 bg-amber-50 dark:bg-amber-950/40 rounded-lg text-amber-600 dark:text-amber-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-2">{{ number_format($stats['pending'] ?? 0) }}</div>
                <div class="text-xs text-amber-700 dark:text-amber-500 mt-1">Awaiting member completion</div>
            </div>

            <!-- Medical Review Flagged -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">
                    <span>Medical Review Needed</span>
                    <span class="p-1.5 bg-rose-50 dark:bg-rose-950/40 rounded-lg text-rose-600 dark:text-rose-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-2">{{ number_format($stats['medical_review'] ?? 0) }}</div>
                <div class="text-xs text-rose-700 dark:text-rose-500 font-semibold mt-1">Health conditions flagged</div>
            </div>
        </div>

        <!-- Filter Tabs & Search -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <!-- Status Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 text-xs font-bold">
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
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search member, phone, ID..."
                               class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    @if(request('search'))
                    <a href="{{ route('app.agreements.index', ['status' => $statusFilter]) }}" class="px-2.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                        Clear
                    </a>
                    @endif
                </form>
            </div>

            <!-- Agreements Table -->
            <div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider font-extrabold text-[10px] border-b border-slate-200/80 dark:border-slate-800">
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
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @forelse($agreements as $ag)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <!-- Member Profile -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-bold flex items-center justify-center shrink-0 border border-indigo-200/50 overflow-hidden">
                                        @if($ag->member?->profile_photo_url)
                                            <img src="{{ $ag->member->profile_photo_url }}" alt="{{ $ag->member->full_name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($ag->member?->first_name ?? 'M', 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('app.members.show', $ag->member_id) }}" class="font-bold text-slate-900 dark:text-white hover:text-indigo-600 transition-colors block">
                                            {{ $ag->member?->full_name ?? 'Member #' . $ag->member_id }}
                                        </a>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-2">
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
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $ag->member->activeMembership->plan->name }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        Expires: {{ $ag->member->activeMembership->end_date ? \Carbon\Carbon::parse($ag->member->activeMembership->end_date)->format('d M Y') : 'Ongoing' }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">No Active Plan</span>
                                @endif
                            </td>

                            <!-- Version & Terrace Rules -->
                            <td class="py-3 px-3">
                                <div class="font-mono font-bold text-slate-700 dark:text-slate-300">v{{ $ag->version }}</div>
                                @if($ag->terrace_rules_included)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Terrace Clauses
                                    </span>
                                @else
                                    <span class="text-[10px] text-slate-400">Standard</span>
                                @endif
                            </td>

                            <!-- Agreement Status -->
                            <td class="py-3 px-3">
                                @if($ag->isCompleted())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                        Signed & Completed
                                    </span>
                                @elseif($ag->requiresMedicalReview())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        Needs Review
                                    </span>
                                @elseif($ag->status === 'link_sent' || $ag->status === 'LINK_SENT')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                                        Link Sent
                                    </span>
                                @elseif(strtoupper((string) $ag->status) === 'DECLINED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                        Declined
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                        Pending
                                    </span>
                                @endif
                            </td>

                            <!-- Health PAR-Q Flag -->
                            <td class="py-3 px-3">
                                @if($ag->has_medical_conditions)
                                    <button type="button" @click="viewHealthDetails({{ json_encode($ag) }})" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200/60 transition-colors">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Flagged Condition
                                    </button>
                                @elseif($ag->health_declaration_accepted_at)
                                    <button type="button" @click="viewHealthDetails({{ json_encode($ag) }})" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 transition-colors">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Clear (All No)
                                    </button>
                                @else
                                    <span class="text-slate-400 text-[11px] italic">Not completed</span>
                                @endif
                            </td>

                            <!-- Timeline -->
                            <td class="py-3 px-3 text-slate-500">
                                @if($ag->signed_at)
                                    <div class="font-bold text-slate-800 dark:text-slate-200">Signed</div>
                                    <div class="text-[11px]">{{ $ag->signed_at->format('d M Y, h:i A') }}</div>
                                @elseif($ag->link_sent_at)
                                    <div>Sent via {{ ucfirst($ag->link_sent_channel ?? 'link') }}</div>
                                    <div class="text-[11px]">{{ $ag->link_sent_at->format('d M Y, h:i A') }}</div>
                                @else
                                    <div>Created {{ $ag->created_at->format('d M Y') }}</div>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    @if($ag->isCompleted())
                                        <!-- View / Download PDF Document -->
                                        <a href="{{ route('app.agreements.pdf', $ag->id) }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 transition shadow-sm"
                                           title="View Official Signed Document">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>View PDF</span>
                                        </a>

                                        <!-- View Cryptographic Audit Trail -->
                                        <button type="button" @click="fetchAuditTrail({{ $ag->id }})"
                                                class="p-1.5 rounded-lg text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 dark:text-slate-300 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition"
                                                title="View Cryptographic Audit Trail & SHA-256 Reference">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        </button>
                                    @elseif($ag->requiresMedicalReview())
                                        <!-- Review Health Disclosures -->
                                        <button type="button" @click="viewHealthDetails({{ json_encode($ag) }})"
                                                class="px-2.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-colors shadow-sm">
                                            Review Medical
                                        </button>
                                    @else
                                        <!-- 1-Click Copy Link -->
                                        <button type="button" @click="copyText('{{ $ag->share_url }}')"
                                                class="p-2 rounded-xl text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 dark:text-slate-300 dark:hover:bg-slate-800 transition-colors"
                                                title="Copy Direct Agreement Link">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                        </button>

                                        <!-- WhatsApp Direct Share -->
                                        <a href="{{ $ag->whatsapp_share_url }}" target="_blank"
                                           class="p-2 rounded-xl text-emerald-600 hover:bg-emerald-50 dark:hover:bg-slate-800 transition-colors"
                                           title="Share via WhatsApp">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="font-bold text-slate-700 dark:text-slate-300">No member agreements found</div>
                                <p class="text-xs text-slate-400 mt-1">Generate a digital agreement link for new or existing members above.</p>
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
                <div class="relative bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
                    
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <span class="p-2.5 bg-gradient-to-tr from-indigo-500 to-violet-600 text-white rounded-2xl shadow-md shadow-indigo-500/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">Generate Member Agreement</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Search member and create encrypted link for mobile signing</p>
                            </div>
                        </div>
                        <button type="button" @click="openQuickGenerateModal = false" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- STATE A: No Member Selected (Search Mode) -->
                        <div x-show="!selectedMember" class="space-y-3">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                                <span>Select Gym Member</span>
                                <span class="text-[11px] font-mono text-indigo-600 dark:text-indigo-400 font-semibold" x-text="(filteredMembers.length) + ' members found'"></span>
                            </div>

                            <!-- Live Search Input -->
                            <div class="relative">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text"
                                       x-model="memberSearch"
                                       placeholder="Type name, phone (e.g. 98765...), or Member ID (e.g. ARM133)..."
                                       class="w-full text-xs rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 pl-10 pr-9 py-3 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-inner">
                                <button type="button" x-show="memberSearch" @click="memberSearch = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold p-1">
                                    &times;
                                </button>
                            </div>

                            <!-- Scrollable Member Selection List -->
                            <div class="max-h-56 overflow-y-auto space-y-1.5 pr-1 divide-y divide-slate-100 dark:divide-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/40 p-1.5">
                                <template x-for="m in filteredMembers" :key="m.id">
                                    <button type="button" @click="selectMember(m)"
                                            class="w-full text-left p-2.5 rounded-xl hover:bg-white dark:hover:bg-slate-800/80 flex items-center gap-3 transition group border border-transparent hover:border-slate-200 dark:hover:border-slate-700/60 shadow-sm hover:shadow">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-violet-600 text-white font-bold text-xs flex items-center justify-center shrink-0 overflow-hidden border border-indigo-400/30">
                                            <template x-if="m.photo">
                                                <img :src="m.photo" :alt="m.name" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!m.photo">
                                                <span x-text="m.initials"></span>
                                            </template>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-xs text-slate-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition" x-text="m.name"></span>
                                                <span class="font-mono text-[10px] font-extrabold px-1.5 py-0.2 rounded bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800" x-text="m.code"></span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5 truncate">
                                                <span x-text="m.phone"></span>
                                                <span>&bull;</span>
                                                <span class="truncate" x-text="m.plan"></span>
                                            </div>
                                        </div>
                                        <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 group-hover:translate-x-0.5 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </template>

                                <div x-show="filteredMembers.length === 0" class="p-6 text-center text-xs text-slate-400 space-y-1">
                                    <p>No members found matching "<span class="font-semibold text-slate-300" x-text="memberSearch"></span>"</p>
                                    <button type="button" @click="memberSearch = ''" class="text-indigo-500 hover:underline font-bold text-[11px]">Clear search</button>
                                </div>
                            </div>
                        </div>

                        <!-- STATE B: Member Selected (Summary Card) -->
                        <div x-show="selectedMember" class="space-y-3">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                                <span>Selected Member</span>
                                <button type="button" @click="clearSelectedMember()" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Change Member</span>
                                </button>
                            </div>

                            <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800/80 flex items-center justify-between gap-3 shadow-sm">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-extrabold text-sm flex items-center justify-center shrink-0 shadow-md shadow-indigo-600/30 overflow-hidden border border-indigo-400/30">
                                        <template x-if="selectedMember?.photo">
                                            <img :src="selectedMember.photo" :alt="selectedMember.name" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!selectedMember?.photo">
                                            <span x-text="selectedMember?.initials"></span>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-black text-slate-900 dark:text-white truncate" x-text="selectedMember?.name"></h4>
                                            <span class="font-mono text-[10px] font-extrabold px-1.5 py-0.2 rounded bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-bold" x-text="selectedMember?.code"></span>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate" x-text="selectedMember?.phone + ' • ' + selectedMember?.plan"></p>
                                    </div>
                                </div>
                                <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            </div>
                        </div>

                        <!-- STATE C: Generated Link Ready -->
                        <div x-show="generatedResult" class="p-4 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800 space-y-3">
                            <div class="text-xs font-bold text-emerald-950 dark:text-emerald-200 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Personalized Agreement Link Ready!</span>
                                </span>
                                <span class="text-[10px] bg-emerald-200/60 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200 px-2 py-0.5 rounded-full font-mono font-bold">256-bit Token</span>
                            </div>
                            <input type="text" readonly :value="generatedResult?.share_url" class="w-full text-xs font-mono bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-800 rounded-xl px-3 py-2.5 text-slate-800 dark:text-slate-200 select-all shadow-inner">
                            
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <button type="button" @click="copyText(generatedResult?.share_url)" class="inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 font-bold text-xs shadow-md transition active:scale-95">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    <span>Copy Link</span>
                                </button>
                                <a :href="generatedResult?.whatsapp_url" target="_blank" class="inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition active:scale-95">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    <span>WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Action Buttons -->
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="openQuickGenerateModal = false" class="px-4 py-2.5 text-xs font-bold rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                            Close
                        </button>
                        <button type="button"
                                @click="generateLinkForMember()"
                                :disabled="!selectedMemberId || isGenerating"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-extrabold rounded-xl transition-all shadow-md active:scale-95"
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
                <form action="{{ route('app.agreements.bulk-generate') }}" method="POST" class="relative bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
                    @csrf
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">Bulk Generate Agreements</h3>
                                <p class="text-xs text-slate-500">Batch generate for existing enrolled members</p>
                            </div>
                        </div>
                        <button type="button" @click="openBulkModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="p-3.5 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <div class="font-bold text-slate-900 dark:text-white">Target Group: Unsent Active Members</div>
                            <div class="text-slate-500 mt-1">Found <strong class="text-indigo-600">{{ count($unsentMembers) }}</strong> members without active digital agreement links.</div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Dispatch Mode</label>
                            <select name="channel" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-2.5 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
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
                        <button type="button" @click="openBulkModal = false" class="px-4 py-2 text-xs font-bold rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm" {{ count($unsentMembers) === 0 ? 'disabled' : '' }}>
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
                <div class="relative bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 bg-rose-50 dark:bg-rose-950/60 text-rose-600 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white" x-text="activeAgreementModal?.member?.first_name + '\'s Health Declaration'"></h3>
                                <p class="text-xs text-slate-500">PAR-Q 7-Question Medical Screening</p>
                            </div>
                        </div>
                        <button type="button" @click="openHealthModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
                        <template x-for="(ans, idx) in (activeAgreementModal?.health_declaration_answers || [])" :key="idx">
                            <div class="p-3.5 rounded-2xl border text-xs" :class="ans.answer === 'yes' ? 'bg-rose-50/70 border-rose-200 text-rose-950 dark:bg-rose-950/30 dark:border-rose-900 dark:text-rose-200' : 'bg-slate-50 border-slate-200/80 text-slate-700 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-300'">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="font-bold flex-1" x-text="(idx + 1) + '. ' + ans.question"></div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase shrink-0" :class="ans.answer === 'yes' ? 'bg-rose-600 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" x-text="ans.answer"></span>
                                </div>
                                <div x-show="ans.answer === 'yes' && ans.details" class="mt-2 pt-2 border-t border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300">
                                    <strong>Member Disclosure:</strong> <span x-text="ans.details"></span>
                                </div>
                            </div>
                        </template>

                        <!-- Approval Action if Medical Review Required -->
                        <div x-show="activeAgreementModal?.status === 'medical_review'" class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800 space-y-3">
                            <div class="text-xs font-bold text-amber-900 dark:text-amber-200">
                                Staff / Owner Medical Clearance
                            </div>
                            <p class="text-[11px] text-amber-800 dark:text-amber-300">
                                Review the flagged medical responses above with the head trainer. You can approve the member for customized workout routines or record advisory notes.
                            </p>
                            <form :action="'/app/agreements/' + activeAgreementModal?.id + '/medical-review'" method="POST" class="space-y-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Optional clearance notes (e.g., cleared by doctor, avoid heavy deadlifts)..." class="w-full text-xs rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-900 p-2 text-slate-900 dark:text-white">
                                <button type="submit" class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm">
                                    Approve & Clear for Training
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-2">
                        <button type="button" @click="openHealthModal = false" class="px-4 py-2 text-xs font-bold rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Cryptographic Audit Trail Modal -->
        <div x-show="openAuditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openAuditModal = false"></div>
            <div class="min-h-full flex items-center justify-center p-4">
                <div class="relative bg-white dark:bg-slate-900 rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">Cryptographic Audit Certificate</h3>
                                <p class="text-xs text-slate-500">SHA-256 Digital Signature Trail</p>
                            </div>
                        </div>
                        <button type="button" @click="openAuditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <!-- Evidence Card -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-2.5">
                            <div class="flex justify-between items-center py-1 border-b border-slate-200/60 dark:border-slate-800">
                                <span class="text-slate-500">Transaction Reference</span>
                                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400" x-text="auditData?.reference"></span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-200/60 dark:border-slate-800">
                                <span class="text-slate-500">Digital Signature Timestamp</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200" x-text="auditData?.signed_at"></span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-200/60 dark:border-slate-800">
                                <span class="text-slate-500">Signer IP Address</span>
                                <span class="font-mono text-slate-800 dark:text-slate-200" x-text="auditData?.signer_ip"></span>
                            </div>
                            <div>
                                <span class="text-slate-500 block mb-1">SHA-256 Checksum Hash</span>
                                <div class="p-2 rounded bg-slate-100 dark:bg-slate-900 font-mono text-[10px] break-all text-slate-700 dark:text-slate-300" x-text="auditData?.hash"></div>
                            </div>
                        </div>

                        <!-- Audit Events Timeline -->
                        <div>
                            <div class="font-bold text-slate-700 dark:text-slate-300 mb-2">Chronological Event Logs</div>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="(ev, idx) in (auditData?.audit_trail || [])" :key="idx">
                                    <div class="flex items-start gap-2.5 p-2 rounded-xl bg-slate-50 dark:bg-slate-950 text-[11px]">
                                        <div class="w-2 h-2 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                                        <div class="flex-1">
                                            <div class="font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[10px]" x-text="ev.event?.replace('_', ' ')"></div>
                                            <div class="text-slate-500" x-text="ev.timestamp"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-2">
                        <button type="button" @click="openAuditModal = false" class="px-4 py-2 text-xs font-bold rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
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
                        alert('Agreement link copied to clipboard!');
                    });
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
                    .then(res => res.json())
                    .then(data => {
                        this.isGenerating = false;
                        if (data.success) {
                            this.generatedResult = data;
                        }
                    })
                    .catch(err => {
                        this.isGenerating = false;
                        alert('Could not generate link. Please try again.');
                    });
                },

                viewHealthDetails(agreement) {
                    this.activeAgreementModal = agreement;
                    this.openHealthModal = true;
                },

                fetchAuditTrail(agreementId) {
                    fetch('/app/agreements/' + agreementId + '/audit', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.auditData = data;
                            this.openAuditModal = true;
                        }
                    });
                }
            }
        }
    </script>
</x-app-layout>
