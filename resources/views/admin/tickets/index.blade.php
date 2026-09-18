<x-admin-layout header="Support Tickets & Helpdesk">
<div class="space-y-6">

    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5 flex-wrap">
                <span>Support Helpdesk & Tickets</span>
                @if($counts['open'] > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20">
                        {{ $counts['open'] }} Action Required
                    </span>
                @endif
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage and respond to support tickets submitted by gym owners and tenants across the platform.</p>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">
        <div class="p-3 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between transition-colors">
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total</span>
            <span class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $counts['total'] }}</span>
        </div>

        <div class="p-3 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-red-500/30 flex flex-col justify-between shadow-xs transition-colors">
            <span class="text-[10px] font-bold text-red-600 dark:text-red-400 uppercase tracking-wider">Open</span>
            <span class="text-xl sm:text-2xl font-black text-red-600 dark:text-red-400 mt-1">{{ $counts['open'] }}</span>
        </div>

        <div class="p-3 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-amber-500/30 flex flex-col justify-between shadow-xs transition-colors">
            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">In Progress</span>
            <span class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $counts['in_progress'] }}</span>
        </div>

        <div class="p-3 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-indigo-500/30 flex flex-col justify-between shadow-xs transition-colors">
            <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Answered</span>
            <span class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">{{ $counts['answered'] }}</span>
        </div>

        <div class="p-3 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-teal-500/30 flex flex-col justify-between shadow-xs transition-colors">
            <span class="text-[10px] font-bold text-teal-600 dark:text-teal-400 uppercase tracking-wider">Resolved</span>
            <span class="text-xl sm:text-2xl font-black text-teal-600 dark:text-teal-400 mt-1">{{ $counts['resolved'] }}</span>
        </div>

        <div class="p-3 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-rose-500/30 flex flex-col justify-between shadow-xs transition-colors">
            <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Urgent</span>
            <span class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ $counts['urgent'] }}</span>
        </div>
    </div>

    <!-- Filter Bar & Search Toolbar -->
    <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-3 transition-colors">
        <!-- Status Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar flex-nowrap pb-1 lg:pb-0 text-xs font-semibold w-full lg:w-auto">
            <a href="{{ route('admin.tickets.index') }}" 
               class="px-3 py-1.5 rounded-xl transition-colors whitespace-nowrap shrink-0 {{ !request('status') || request('status') === 'all' ? 'bg-red-600 text-white font-bold shadow-md shadow-red-600/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                All ({{ $counts['total'] }})
            </a>
            <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}" 
               class="px-3 py-1.5 rounded-xl transition-colors whitespace-nowrap shrink-0 {{ request('status') === 'open' ? 'bg-red-600 text-white font-bold shadow-md shadow-red-600/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Open ({{ $counts['open'] }})
            </a>
            <a href="{{ route('admin.tickets.index', ['status' => 'in_progress']) }}" 
               class="px-3 py-1.5 rounded-xl transition-colors whitespace-nowrap shrink-0 {{ request('status') === 'in_progress' ? 'bg-red-600 text-white font-bold shadow-md shadow-red-600/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                In Progress ({{ $counts['in_progress'] }})
            </a>
            <a href="{{ route('admin.tickets.index', ['status' => 'answered']) }}" 
               class="px-3 py-1.5 rounded-xl transition-colors whitespace-nowrap shrink-0 {{ request('status') === 'answered' ? 'bg-red-600 text-white font-bold shadow-md shadow-red-600/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Answered ({{ $counts['answered'] }})
            </a>
            <a href="{{ route('admin.tickets.index', ['status' => 'resolved']) }}" 
               class="px-3 py-1.5 rounded-xl transition-colors whitespace-nowrap shrink-0 {{ request('status') === 'resolved' ? 'bg-red-600 text-white font-bold shadow-md shadow-red-600/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Resolved ({{ $counts['resolved'] }})
            </a>
        </div>

        <!-- Filters Form -->
        <form method="GET" action="{{ route('admin.tickets.index') }}" class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <!-- Gym Tenant Dropdown -->
            <select name="tenant_id" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-800 dark:text-slate-300 text-xs font-semibold focus:border-red-500 focus:outline-none cursor-pointer">
                <option value="">All Gyms / Tenants</option>
                @foreach($gyms as $g)
                    <option value="{{ $g->id }}" {{ request('tenant_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                @endforeach
            </select>

            <!-- Priority Dropdown -->
            <select name="priority" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-800 dark:text-slate-300 text-xs font-semibold focus:border-red-500 focus:outline-none cursor-pointer">
                <option value="all">All Priorities</option>
                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
            </select>

            <!-- Search -->
            <div class="relative flex-1 sm:flex-initial">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ticket #, subject..." 
                       class="w-full sm:w-52 pl-8 pr-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-red-500 focus:outline-none">
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-300 dark:border-slate-700 transition-colors cursor-pointer shrink-0">
                Search
            </button>
        </form>
    </div>

    <!-- Tickets List (Mobile Cards + Desktop Table) -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs dark:shadow-xl transition-colors">
        
        <!-- Mobile View: Tickets Card List (md:hidden) -->
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800/60 p-3 space-y-3">
            @forelse($tickets as $t)
                <div class="p-3.5 bg-slate-50 dark:bg-slate-950/60 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3 {{ $t->priority === 'urgent' && in_array($t->status, ['open', 'in_progress']) ? 'ring-1 ring-red-500/40' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <a href="{{ route('admin.tickets.show', $t->id) }}" class="font-bold text-slate-900 dark:text-white text-xs hover:text-red-600 line-clamp-2">
                                {{ $t->subject }}
                            </a>
                            <div class="flex items-center gap-1.5 mt-1 flex-wrap text-[11px]">
                                <span class="font-bold text-slate-800 dark:text-slate-200">🏢 {{ $t->tenant->name ?? 'Gym Tenant' }}</span>
                                <span class="text-slate-400">&bull;</span>
                                <span class="font-mono text-red-600 dark:text-red-400 font-bold">{{ $t->ticket_number }}</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border capitalize shrink-0 {{ $t->status_badge_color }}">
                            {{ str_replace('_', ' ', $t->status) }}
                        </span>
                    </div>

                    <p class="text-[11px] text-slate-600 dark:text-slate-400 line-clamp-2">
                        {{ $t->latestReply->message ?? 'No messages' }}
                    </p>

                    <div class="flex items-center justify-between text-[10px] pt-2 border-t border-slate-200 dark:border-slate-800/60 flex-wrap gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono uppercase font-semibold">
                                {{ str_replace('_', ' ', $t->category) }}
                            </span>
                            <span class="px-1.5 py-0.5 rounded-full font-bold border uppercase {{ $t->priority_badge_color }}">
                                {{ $t->priority }}
                            </span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('admin.tickets.show', $t->id) }}" class="px-2.5 py-1 rounded-lg bg-red-600 text-white font-bold text-[11px] inline-flex items-center gap-1">
                                <span>Manage</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-xs text-slate-400">
                    No support tickets matching the filter criteria.
                </div>
            @endforelse
        </div>

        <!-- Desktop View: Tickets Table (hidden md:block) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[750px]">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Ticket</th>
                        <th class="py-3.5 px-4 font-semibold">Gym / Tenant</th>
                        <th class="py-3.5 px-4 font-semibold">Subject & Details</th>
                        <th class="py-3.5 px-4 font-semibold">Category</th>
                        <th class="py-3.5 px-4 font-semibold">Priority</th>
                        <th class="py-3.5 px-4 font-semibold">Status</th>
                        <th class="py-3.5 px-4 font-semibold">Last Activity</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium text-slate-700 dark:text-slate-300">
                    @forelse($tickets as $t)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors {{ $t->priority === 'urgent' && in_array($t->status, ['open', 'in_progress']) ? 'bg-red-50/50 dark:bg-red-950/10' : '' }}">
                        <td class="py-3.5 px-4">
                            <a href="{{ route('admin.tickets.show', $t->id) }}" class="font-mono text-xs font-bold text-red-600 dark:text-red-400 hover:underline">
                                {{ $t->ticket_number }}
                            </a>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-0.5">{{ $t->created_at->format('d M, Y') }}</span>
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="font-bold text-slate-900 dark:text-white block">{{ $t->tenant->name ?? 'Deleted Gym' }}</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block">{{ $t->user->name ?? 'User' }} ({{ $t->user->email ?? '' }})</span>
                        </td>

                        <td class="py-3.5 px-4 max-w-xs">
                            <a href="{{ route('admin.tickets.show', $t->id) }}" class="font-bold text-slate-900 dark:text-white hover:text-red-600 dark:hover:text-red-400 truncate block">
                                {{ $t->subject }}
                            </a>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                {{ $t->latestReply->message ?? 'No messages' }}
                            </p>
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono text-[10px] uppercase font-semibold border border-slate-200 dark:border-slate-700">
                                {{ str_replace('_', ' ', $t->category) }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $t->priority_badge_color }}">
                                {{ $t->priority }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border capitalize {{ $t->status_badge_color }}">
                                {{ str_replace('_', ' ', $t->status) }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-[11px] text-slate-500 dark:text-slate-400">
                            <span>{{ $t->last_reply_at ? $t->last_reply_at->diffForHumans() : $t->created_at->diffForHumans() }}</span>
                            @if($t->lastReplyBy)
                                <span class="text-[9px] text-slate-400 dark:text-slate-500 block">by {{ $t->lastReplyBy->name }}</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.tickets.show', $t->id) }}" 
                                   class="px-3 py-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 text-xs font-bold transition-colors inline-flex items-center gap-1">
                                    <span>Manage</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>

                                <form action="{{ route('admin.tickets.delete', $t->id) }}" method="POST"
                                      data-confirm="Are you sure you want to permanently delete ticket #{{ $t->ticket_number }} ({{ $t->subject }})?"
                                      data-confirm-title="Delete Ticket"
                                      data-confirm-btn="Delete Ticket"
                                      data-confirm-type="danger"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Ticket" class="p-1.5 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-500 hover:text-red-600 dark:bg-slate-800 dark:text-slate-400 dark:hover:text-rose-400 dark:hover:bg-rose-500/20 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 px-4 text-center text-slate-400 dark:text-slate-500">
                            No support tickets matching the filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="p-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
</x-admin-layout>
