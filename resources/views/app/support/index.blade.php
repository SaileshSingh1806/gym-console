<x-app-layout header="Help & Support">
    <div x-data="{
        showCreateModal: false,
        ticketForm: {
            subject: '',
            category: 'technical',
            priority: 'medium',
            message: ''
        }
    }" class="space-y-4 sm:space-y-6">

        <!-- Top Header & Action Banner -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-line dark:border-line pb-3">
            <div>
                <h2 class="text-base sm:text-lg font-semibold text-fg dark:text-white tracking-tight flex items-center gap-2 flex-wrap">
                    <span>Help &amp; Support Center</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">24/7 Platform Desk</span>
                </h2>
                <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Need help or facing any issue? Raise a support ticket directly to our platform support team.</p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="showCreateModal = true" 
                        class="w-full sm:w-auto px-3.5 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer ops-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Create New Ticket</span>
                </button>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="p-3.5 sm:p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center justify-between">
                <div>
                    <span class="text-xs sm:text-xs font-semibold text-fg-muted dark:text-fg-muted block">Total Tickets</span>
                    <span class="text-xl sm:text-2xl font-semibold text-fg dark:text-white mt-0.5 block">{{ $counts['total'] }}</span>
                </div>
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-raised dark:bg-raised border border-line dark:border-line text-fg-muted dark:text-fg-muted flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>

            <div class="p-3.5 sm:p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center justify-between">
                <div>
                    <span class="text-xs sm:text-xs font-semibold text-emerald-600 dark:text-emerald-400 block">Open</span>
                    <span class="text-xl sm:text-2xl font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5 block">{{ $counts['open'] }}</span>
                </div>
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <div class="p-3.5 sm:p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center justify-between">
                <div>
                    <span class="text-xs sm:text-xs font-semibold text-indigo-600 dark:text-indigo-400 block">Answered</span>
                    <span class="text-xl sm:text-2xl font-semibold text-indigo-600 dark:text-indigo-400 mt-0.5 block">{{ $counts['answered'] }}</span>
                </div>
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
            </div>

            <div class="p-3.5 sm:p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center justify-between">
                <div>
                    <span class="text-xs sm:text-xs font-semibold text-fg-muted dark:text-fg-muted block">Resolved</span>
                    <span class="text-xl sm:text-2xl font-semibold text-fg-muted dark:text-fg-muted mt-0.5 block">{{ $counts['resolved'] }}</span>
                </div>
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-raised dark:bg-raised border border-line dark:border-line text-fg-muted dark:text-fg-muted flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
        </div>

        <!-- Filter Bar & Search -->
        <div class="p-3 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar flex-nowrap pb-1 md:pb-0 text-xs font-semibold w-full md:w-auto">
                <a href="{{ route('app.support.index') }}" 
                   class="px-3 py-1.5 rounded-xl whitespace-nowrap transition-colors {{ !request('status') || request('status') === 'all' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    All Tickets
                </a>
                <a href="{{ route('app.support.index', ['status' => 'open']) }}" 
                   class="px-3 py-1.5 rounded-xl whitespace-nowrap transition-colors {{ request('status') === 'open' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Open
                </a>
                <a href="{{ route('app.support.index', ['status' => 'answered']) }}" 
                   class="px-3 py-1.5 rounded-xl whitespace-nowrap transition-colors {{ request('status') === 'answered' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Answered
                </a>
                <a href="{{ route('app.support.index', ['status' => 'resolved']) }}" 
                   class="px-3 py-1.5 rounded-xl whitespace-nowrap transition-colors {{ request('status') === 'resolved' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Resolved
                </a>
                <a href="{{ route('app.support.index', ['status' => 'closed']) }}" 
                   class="px-3 py-1.5 rounded-xl whitespace-nowrap transition-colors {{ request('status') === 'closed' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Closed
                </a>
            </div>

            <form method="GET" action="{{ route('app.support.index') }}" class="flex items-center gap-2 w-full md:w-auto">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative flex-1 sm:flex-initial">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." 
                           class="w-full sm:w-64 pl-8 pr-3 py-1.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                    <svg class="w-3.5 h-3.5 text-fg-muted dark:text-fg-muted absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg dark:border-line text-xs font-semibold transition-colors cursor-pointer shrink-0">
                    Filter
                </button>
            </form>
        </div>

        <!-- Tickets Section (Desktop Table + Mobile Cards) -->
        <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
            <!-- Mobile View: Ticket Cards (md:hidden) -->
            <div class="md:hidden divide-y divide-line dark:divide-line p-3 space-y-3">
                @forelse($tickets as $t)
                    <div class="p-3.5 bg-raised dark:bg-slate-950/60 rounded-xl border border-line dark:border-line space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <a href="{{ route('app.support.show', $t->id) }}" class="font-semibold text-fg dark:text-white text-xs hover:text-indigo-600 dark:hover:text-indigo-400 line-clamp-2">
                                    {{ $t->subject }}
                                </a>
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    <span class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $t->ticket_number }}</span>
                                    <span class="text-xs text-fg-muted">&bull;</span>
                                    <span class="text-xs text-fg-muted dark:text-fg-muted">{{ $t->created_at->format('d M, Y') }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border capitalize shrink-0 {{ $t->status_badge_color }}">
                                {{ str_replace('_', ' ', $t->status) }}
                            </span>
                        </div>

                        <p class="text-xs text-fg-muted dark:text-fg-muted line-clamp-2">
                            {{ $t->latestReply->message ?? 'No replies yet' }}
                        </p>

                        <div class="flex items-center justify-between text-xs pt-2 border-t border-line dark:border-line flex-wrap gap-2">
                            <div class="flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-raised text-fg-muted dark:text-fg-muted font-mono font-semibold">
                                    {{ str_replace('_', ' ', $t->category) }}
                                </span>
                                <span class="px-1.5 py-0.5 rounded-full font-bold border uppercase {{ $t->priority_badge_color }}">
                                    {{ $t->priority }}
                                </span>
                            </div>

                            <a href="{{ route('app.support.show', $t->id) }}" class="px-3 py-1.5 rounded-lg bg-accent text-accent-on font-semibold text-xs inline-flex items-center gap-1 ops-primary">
                                <span>View Thread</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center">
                        <div class="w-10 h-10 rounded-xl bg-raised dark:bg-raised text-fg-muted flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <h4 class="text-xs font-semibold text-fg dark:text-white">No support tickets found</h4>
                        <button @click="showCreateModal = true" class="mt-3 px-3 py-1.5 rounded-xl bg-accent text-accent-on text-xs font-semibold ops-primary">
                            + Create Support Ticket
                        </button>
                    </div>
                @endforelse
            </div>

            <!-- Desktop View: Tickets Table (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse min-w-[650px]">
                    <thead class="bg-raised dark:bg-slate-950/80 text-fg-muted dark:text-fg-muted text-xs font-semibold border-b border-line dark:border-line">
                        <tr>
                            <th class="py-3 px-4">Ticket</th>
                            <th class="py-3 px-4">Subject &amp; Details</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Priority</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Last Activity</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-line font-medium text-fg-muted dark:text-fg-muted">
                        @forelse($tickets as $t)
                        <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                            <td class="py-3 px-4">
                                <a href="{{ route('app.support.show', $t->id) }}" class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300">
                                    {{ $t->ticket_number }}
                                </a>
                                <span class="text-xs text-fg-muted dark:text-fg-muted block mt-0.5">{{ $t->created_at->format('d M, Y') }}</span>
                            </td>

                            <td class="py-3 px-4 max-w-xs">
                                <a href="{{ route('app.support.show', $t->id) }}" class="font-semibold text-fg dark:text-white hover:text-indigo-600 dark:hover:text-indigo-300 truncate block">
                                    {{ $t->subject }}
                                </a>
                                <p class="text-xs text-fg-muted dark:text-fg-muted truncate mt-0.5">
                                    {{ $t->latestReply->message ?? 'No replies yet' }}
                                </p>
                            </td>

                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted font-mono text-xs font-semibold">
                                    {{ str_replace('_', ' ', $t->category) }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $t->priority_badge_color }}">
                                    {{ $t->priority }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border capitalize {{ $t->status_badge_color }}">
                                    {{ str_replace('_', ' ', $t->status) }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-xs text-fg-muted dark:text-fg-muted">
                                <span>{{ $t->last_reply_at ? $t->last_reply_at->diffForHumans() : $t->created_at->diffForHumans() }}</span>
                                @if($t->lastReplyBy)
                                    <span class="text-xs text-fg-muted dark:text-fg-muted block">by {{ $t->lastReplyBy->name }}</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('app.support.show', $t->id) }}" 
                                   class="px-3 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xs font-semibold transition-colors inline-flex items-center gap-1">
                                    <span>View Thread</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center">
                                <div class="w-12 h-12 rounded-xl bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </div>
                                <h4 class="text-sm font-semibold text-fg dark:text-white">No support tickets found</h4>
                                <p class="text-xs text-fg-muted dark:text-fg-muted mt-1 max-w-sm mx-auto">Have a question, request, or issue with your gym software? Click "Create New Ticket" to reach out to our platform team.</p>
                                <button @click="showCreateModal = true" class="mt-4 px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold transition-all inline-flex items-center gap-1.5 cursor-pointer ops-primary">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Create Support Ticket</span>
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="p-3 border-t border-line dark:border-line bg-raised dark:bg-slate-950/40">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL: CREATE NEW SUPPORT TICKET                                          -->
        <!-- ========================================================================= -->
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
            <div @click.away="showCreateModal = false" class="w-full max-w-lg max-h-[90vh] overflow-y-auto bg-surface dark:bg-surface border border-line dark:border-line rounded-xl sm:rounded-xl space-y-0 text-fg dark:text-fg">
                <!-- Header -->
                <div class="p-4 sm:p-5 bg-indigo-50 dark:bg-indigo-600/10 border-b border-indigo-100 dark:border-indigo-500/20 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-fg dark:text-white flex items-center gap-2">
                            <span>Submit Support Ticket</span>
                        </h3>
                        <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Our support team will review and respond promptly.</p>
                    </div>
                    <button @click="showCreateModal = false" class="p-1 rounded-lg text-fg-muted hover:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Form -->
                <form action="{{ route('app.support.store') }}" method="POST" enctype="multipart/form-data" class="p-4 sm:p-5 space-y-4 text-xs">
                    @csrf

                    <!-- Subject -->
                    <div class="space-y-1">
                        <label class="font-semibold text-fg-muted dark:text-fg-muted">Ticket Subject / Title <span class="text-rose-500 dark:text-red-400">*</span></label>
                        <input type="text" name="subject" required placeholder="e.g. Issue with Device Sync or Billing Receipt" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none text-xs">
                    </div>

                    <!-- Category & Priority Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="font-semibold text-fg-muted dark:text-fg-muted">Category <span class="text-rose-500 dark:text-red-400">*</span></label>
                            <select name="category" required class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none text-xs cursor-pointer">
                                <option value="technical">Technical / Software Issue</option>
                                <option value="billing">Subscription &amp; Billing</option>
                                <option value="feature_request">Feature Request / Suggestion</option>
                                <option value="account">Account &amp; Setup</option>
                                <option value="general" selected>General Inquiry</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="font-semibold text-fg-muted dark:text-fg-muted">Priority Level <span class="text-rose-500 dark:text-red-400">*</span></label>
                            <select name="priority" required class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none text-xs cursor-pointer">
                                <option value="low">Low (General Query)</option>
                                <option value="medium" selected>Medium (Standard)</option>
                                <option value="high">High (Impacting Operations)</option>
                                <option value="urgent">Urgent (System Down / Critical)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Message Body -->
                    <div class="space-y-1">
                        <label class="font-semibold text-fg-muted dark:text-fg-muted">Detailed Message / Description <span class="text-rose-500 dark:text-red-400">*</span></label>
                        <textarea name="message" rows="4" required placeholder="Please describe the issue or question in detail..." 
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none text-xs"></textarea>
                    </div>

                    <!-- Attachment -->
                    <div class="space-y-1">
                        <label class="font-semibold text-fg-muted dark:text-fg-muted">Attachment / Screenshot <span class="text-fg-muted font-normal">(Optional, max 5MB)</span></label>
                        <input type="file" name="attachment" 
                               class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg-muted dark:text-fg-muted file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer text-xs">
                    </div>

                    <!-- Actions -->
                    <div class="pt-3 border-t border-line dark:border-line flex items-center justify-end gap-2.5">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line font-semibold transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold transition-all flex items-center gap-1.5 cursor-pointer ops-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Submit Ticket</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
