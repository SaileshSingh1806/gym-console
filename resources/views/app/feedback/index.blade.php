<x-app-layout header="Member Feedback Desk">
    <div x-data="{
        selectedFeedback: null,
        showDetailModal: false,
        statusForm: {
            status: 'under_review',
            admin_notes: ''
        },
        openDetail(item) {
            this.selectedFeedback = item;
            this.statusForm.status = item.status;
            this.statusForm.admin_notes = item.admin_notes || '';
            this.showDetailModal = true;
        }
    }" class="space-y-4 sm:space-y-6">

        <!-- Header / Banner -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
            <div>
                <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2 flex-wrap">
                    <span>Member Feedback &amp; Suggestions Desk</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">Direct from Members</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Review feedback, suggestions, equipment complaints, and coaching ratings submitted directly by your gym members.</p>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-xs">
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Total</span>
                    <span class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-0.5 block">{{ $counts['total'] }}</span>
                </div>
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                    💬
                </div>
            </div>

            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-xs">
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider block">New / Pending</span>
                    <span class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 mt-0.5 block">{{ $counts['pending'] }}</span>
                </div>
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center shrink-0">
                    ⏳
                </div>
            </div>

            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-xs">
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider block">Under Review</span>
                    <span class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400 mt-0.5 block">{{ $counts['reviewed'] }}</span>
                </div>
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 flex items-center justify-center shrink-0">
                    🔍
                </div>
            </div>

            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-xs">
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">Resolved</span>
                    <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5 block">{{ $counts['resolved'] }}</span>
                </div>
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0">
                    ✓
                </div>
            </div>

            <div class="col-span-2 sm:col-span-1 p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-xs">
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-amber-500 uppercase tracking-wider block">Avg Rating</span>
                    <div class="flex items-center gap-1 mt-0.5">
                        <span class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">{{ $counts['avg_rating'] }}</span>
                        <span class="text-amber-400 text-sm">★</span>
                    </div>
                </div>
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
                    ⭐
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar flex-nowrap pb-1 md:pb-0 text-xs font-semibold w-full md:w-auto">
                <a href="{{ route('app.feedback.index') }}" 
                   class="px-3 py-1.5 rounded-xl whitespace-nowrap transition-colors {{ !request('status') || request('status') === 'all' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    All
                </a>
                <a href="{{ route('app.feedback.index', array_merge(request()->query(), ['status' => 'new'])) }}" 
                   class="px-3 py-1.5 rounded-xl whitespace-nowrap transition-colors {{ request('status') === 'new' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    New
                </a>
                <a href="{{ route('app.feedback.index', array_merge(request()->query(), ['status' => 'under_review'])) }}" 
                   class="px-3 py-1.5 rounded-xl whitespace-nowrap transition-colors {{ request('status') === 'under_review' ? 'bg-blue-600 text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Under Review
                </a>
                <a href="{{ route('app.feedback.index', array_merge(request()->query(), ['status' => 'action_taken'])) }}" 
                   class="px-3 py-1.5 rounded-xl whitespace-nowrap transition-colors {{ request('status') === 'action_taken' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Action Taken
                </a>
                <a href="{{ route('app.feedback.index', array_merge(request()->query(), ['status' => 'resolved'])) }}" 
                   class="px-3 py-1.5 rounded-xl whitespace-nowrap transition-colors {{ request('status') === 'resolved' ? 'bg-emerald-600 text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Resolved
                </a>
            </div>

            <!-- Search Input -->
            <form method="GET" action="{{ route('app.feedback.index') }}" class="flex items-center gap-2 w-full md:w-auto">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative flex-1 md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search feedback or member..." 
                           class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-bold hover:bg-slate-800 transition-colors shrink-0 cursor-pointer">
                    Filter
                </button>
            </form>
        </div>

        <!-- Feedbacks List / Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
            @if($feedbacks->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto text-2xl">
                        💬
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">No feedback received yet</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                        When gym members submit suggestions, cleanliness notices, or coaching reviews from their Member App, they will appear here.
                    </p>
                </div>
            @else
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($feedbacks as $item)
                        @php
                            $catIcon = match($item->category) {
                                'suggestion' => '💡',
                                'equipment'  => '🏋️',
                                'hygiene'    => '🧹',
                                'trainer'    => '👨‍🏫',
                                'complaint'  => '⚠️',
                                default      => '💬',
                            };
                            $catBadge = match($item->category) {
                                'suggestion' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                'equipment'  => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                'hygiene'    => 'bg-teal-500/10 text-teal-600 dark:text-teal-400 border-teal-500/20',
                                'trainer'    => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
                                'complaint'  => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                                default      => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                            };
                            $statusBadge = match($item->status) {
                                'new', 'pending', 'open' => 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/30',
                                'under_review'           => 'bg-blue-500/15 text-blue-600 dark:text-blue-400 border-blue-500/30',
                                'action_taken'           => 'bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border-indigo-500/30',
                                'resolved', 'closed'     => 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/30',
                                default                  => 'bg-slate-100 text-slate-600 border-slate-200',
                            };
                        @endphp
                        <div class="p-4 sm:p-5 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors flex flex-col md:flex-row md:items-start justify-between gap-4">
                            <div class="space-y-2 min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-bold border capitalize {{ $catBadge }}">
                                        <span>{{ $catIcon }}</span>
                                        <span>{{ str_replace('_', ' ', $item->category) }}</span>
                                    </span>

                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border uppercase {{ $statusBadge }}">
                                        {{ str_replace('_', ' ', $item->status) }}
                                    </span>

                                    @if($item->rating)
                                        <div class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-500 border border-amber-500/20 text-xs font-black">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="{{ $i <= $item->rating ? 'text-amber-400' : 'text-slate-300 dark:text-slate-700' }}">★</span>
                                            @endfor
                                            <span class="ml-1 text-[11px]">{{ $item->rating }}/5</span>
                                        </div>
                                    @endif

                                    <span class="text-[11px] text-slate-400 font-medium">
                                        {{ $item->created_at->diffForHumans() }} ({{ $item->created_at->format('d M Y, h:i A') }})
                                    </span>
                                </div>

                                <!-- Feedback Comments -->
                                <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 leading-relaxed font-medium bg-slate-50/50 dark:bg-slate-800/30 p-3 rounded-xl border border-slate-100 dark:border-slate-800/80">
                                    &ldquo;{{ $item->comments }}&rdquo;
                                </p>

                                <!-- Member Info -->
                                <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                                    @if($item->is_anonymous)
                                        <span class="inline-flex items-center gap-1 font-semibold text-slate-600 dark:text-slate-400">
                                            <span>🕵️</span>
                                            <span>Submitted Anonymously</span>
                                        </span>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-[10px] flex items-center justify-center">
                                                {{ strtoupper(substr($item->member_name, 0, 1)) }}
                                            </div>
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $item->member_name }}</span>
                                            @if($item->member_phone)
                                                <span class="text-slate-400 font-mono">{{ $item->member_phone }}</span>
                                            @endif
                                            @if($item->member_id)
                                                <a href="{{ route('app.members.show', $item->member_id) }}" class="text-amber-500 hover:underline text-[11px] font-semibold">View Profile &rarr;</a>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Owner Resolution Note if exists -->
                                @if(!empty($item->admin_notes))
                                    <div class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-800 dark:text-emerald-300 space-y-0.5">
                                        <div class="font-bold flex items-center gap-1.5">
                                            <span>✓ Management Response / Action:</span>
                                        </div>
                                        <p class="text-[11px] text-emerald-700 dark:text-emerald-400 pl-4">{{ $item->admin_notes }}</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 shrink-0 self-end md:self-start">
                                <button type="button" 
                                        @click="openDetail({{ Js::from($item) }})"
                                        class="px-3 py-1.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-xs hover:bg-slate-800 transition-all flex items-center gap-1.5 cursor-pointer shadow-xs">
                                    <span>⚙️ Update Status</span>
                                </button>

                                <form action="{{ route('app.feedback.delete', $item->id) }}" method="POST" onsubmit="return confirm('Delete this feedback entry?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($feedbacks->hasPages())
                    <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                        {{ $feedbacks->links() }}
                    </div>
                @endif
            @endif
        </div>

        <!-- Management Status & Response Modal -->
        <div x-show="showDetailModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4" x-cloak>
            <div class="w-full max-w-lg rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 space-y-4" @click.away="showDetailModal = false">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">⚙️</span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Take Action on Member Feedback</h3>
                            <p class="text-[11px] text-slate-400">Update status or record internal/member resolution notes</p>
                        </div>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="p-1 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 text-lg">&times;</button>
                </div>

                <template x-if="selectedFeedback">
                    <div class="space-y-3">
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-700 dark:text-slate-300 capitalize" x-text="selectedFeedback.category"></span>
                                <span class="text-[11px] text-amber-500 font-black" x-text="selectedFeedback.rating ? selectedFeedback.rating + ' ★' : ''"></span>
                            </div>
                            <p class="text-slate-800 dark:text-slate-200 italic" x-text="'&ldquo;' + selectedFeedback.comments + '&rdquo;'"></p>
                            <span class="text-[10px] text-slate-400 block pt-1" x-text="'By: ' + selectedFeedback.member_name"></span>
                        </div>

                        <form :action="'{{ url('app/feedback') }}/' + selectedFeedback.id + '/status'" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Feedback Status</label>
                                <select name="status" x-model="statusForm.status" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                                    <option value="new">New / Received</option>
                                    <option value="under_review">Under Review</option>
                                    <option value="action_taken">Action Taken (In Progress)</option>
                                    <option value="resolved">Resolved &amp; Completed</option>
                                    <option value="closed">Closed / Archived</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Resolution / Response Note</label>
                                <textarea name="admin_notes" x-model="statusForm.admin_notes" rows="3" placeholder="e.g. AC cooling repaired on 11th Nov. Thank you for notifying us!" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500 resize-none"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="showDetailModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition-all shadow-md shadow-amber-500/20 cursor-pointer">
                                    Save Status
                                </button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </div>

    </div>
</x-app-layout>

