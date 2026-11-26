<x-app-layout header="Ticket #{{ $ticket->ticket_number }}">
    <div x-data="{ showCloseModal: false }" class="w-full space-y-4 sm:space-y-6">

        <!-- Top Navigation / Breadcrumb -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-line dark:border-line pb-3">
            <div class="flex items-start sm:items-center gap-2 min-w-0">
                <a href="{{ route('app.support.index') }}" 
                   class="p-2 rounded-xl bg-surface dark:bg-surface hover:bg-raised dark:hover:bg-raised text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-fg border border-line dark:border-line transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $ticket->ticket_number }}</span>
                        <h2 class="text-base font-semibold text-fg dark:text-white tracking-tight break-words">{{ $ticket->subject }}</h2>
                    </div>
                    <span class="text-xs text-fg-muted dark:text-fg-muted">Created {{ $ticket->created_at->format('d M Y, h:i A') }} ({{ $ticket->created_at->diffForHumans() }})</span>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if(!in_array($ticket->status, ['resolved', 'closed']))
                    <form id="closeTicketForm" action="{{ route('app.support.close', $ticket->id) }}" method="POST">
                        @csrf
                    </form>
                    <button type="button" @click="showCloseModal = true" class="w-full sm:w-auto px-3.5 py-2 rounded-xl bg-surface hover:bg-raised text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:hover:text-fg text-xs font-semibold dark:border-line transition-colors cursor-pointer flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Mark Resolved &amp; Close</span>
                    </button>
                @else
                    <span class="px-3 py-1.5 rounded-xl bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted text-xs font-semibold border border-line dark:border-line">
                        Ticket {{ ucfirst($ticket->status) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- Left 3 Cols: Message Thread & Reply Box -->
            <div class="lg:col-span-3 space-y-4">
                
                <!-- Conversation Timeline -->
                <div class="space-y-4">
                    @foreach($ticket->replies as $reply)
                    <div class="p-4 sm:p-5 rounded-2xl border {{ $reply->is_admin_reply ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-500/30 shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 shadow-sm' }} space-y-3 overflow-hidden">
                        <!-- Reply Header -->
                        <div class="flex items-center justify-between gap-3 border-b {{ $reply->is_admin_reply ? 'border-indigo-100 dark:border-indigo-500/20' : 'border-slate-100 dark:border-slate-800' }} pb-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-xl {{ $reply->is_admin_reply ? 'bg-gradient-to-tr from-indigo-500 to-purple-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }} flex items-center justify-center font-bold text-xs shadow-sm shrink-0">
                                    {{ substr($reply->user->name ?? ($reply->is_admin_reply ? 'Admin' : 'User'), 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-semibold text-fg dark:text-white text-xs truncate">{{ $reply->user->name ?? ($reply->is_admin_reply ? 'Support Team' : 'User') }}</span>
                                        @if($reply->is_admin_reply)
                                            <span class="px-2 py-0.2 rounded-full text-xs font-semibold bg-indigo-500/15 text-indigo-700 dark:text-indigo-400 border border-indigo-500/30">Support Agent</span>
                                        @else
                                            <span class="px-2 py-0.2 rounded-full text-xs font-semibold bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted border border-line dark:border-line">Gym Member / Owner</span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-fg-muted dark:text-fg-muted block">{{ $reply->created_at->format('d M Y, h:i A') }} ({{ $reply->created_at->diffForHumans() }})</span>
                                </div>
                            </div>
                        </div>

                        <!-- Reply Body -->
                        <div class="text-xs text-fg-muted dark:text-fg leading-relaxed whitespace-pre-line break-words overflow-hidden">
                            {{ $reply->message }}
                        </div>

                        <!-- Attachment (if any) -->
                        @if($reply->attachment_path)
                        <div class="pt-2 border-t {{ $reply->is_admin_reply ? 'border-indigo-100 dark:border-indigo-500/20' : 'border-slate-100 dark:border-slate-800/80' }}">
                            <a href="{{ asset('storage/'.$reply->attachment_path) }}" target="_blank" 
                               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-raised dark:bg-slate-950/80 hover:bg-raised dark:hover:bg-surface border border-line dark:border-line text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-xs font-semibold transition-colors max-w-full truncate">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span class="truncate">{{ $reply->attachment_name ?? 'Download Attachment' }}</span>
                            </a>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                <!-- Post Reply Form -->
                @if($ticket->status !== 'closed')
                <div class="p-4 sm:p-5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-3">
                    <h3 class="text-xs font-semibold text-fg dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        <span>Post a Reply</span>
                    </h3>

                    <form action="{{ route('app.support.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
                        @csrf
                        <div>
                            <textarea name="message" rows="4" required placeholder="Type your response or additional information here..." 
                                      class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none text-xs"></textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                            <div class="flex-1 max-w-sm">
                                <input type="file" name="attachment" 
                                       class="w-full text-fg-muted dark:text-fg-muted file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-raised dark:file:bg-raised file:text-fg-muted dark:file:text-fg-muted hover:file:bg-slate-200 dark:hover:file:bg-slate-700 cursor-pointer text-xs">
                            </div>

                            <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer ops-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span>Send Reply</span>
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-center text-xs text-fg-muted dark:text-fg-muted">
                    This ticket has been marked as closed. If you still have issues, you can submit a new ticket.
                </div>
                @endif
            </div>

            <!-- Right Col: Ticket Meta Sidebar -->
            <div class="space-y-4">
                <div class="p-4 sm:p-5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-4 text-xs">
                    <h4 class="text-xs font-semibold text-fg dark:text-white border-b border-line dark:border-line pb-2.5">
                        Ticket Info
                    </h4>

                    <!-- Status -->
                    <div class="space-y-1">
                        <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold block">Status</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border capitalize inline-block {{ $ticket->status_badge_color }}">
                            {{ str_replace('_', ' ', $ticket->status) }}
                        </span>
                    </div>

                    <!-- Priority -->
                    <div class="space-y-1">
                        <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold block">Priority</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider inline-block {{ $ticket->priority_badge_color }}">
                            {{ $ticket->priority }}
                        </span>
                    </div>

                    <!-- Category -->
                    <div class="space-y-1">
                        <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold block">Category</span>
                        <span class="px-2 py-0.5 rounded-md bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted font-mono text-xs font-semibold inline-block">
                            {{ str_replace('_', ' ', $ticket->category) }}
                        </span>
                    </div>

                    <!-- Creator -->
                    <div class="space-y-1">
                        <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold block">Submitted By</span>
                        <p class="font-semibold text-fg dark:text-white">{{ $ticket->user->name ?? 'User' }}</p>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">{{ $ticket->user->email ?? '' }}</p>
                    </div>

                    <!-- Gym -->
                    <div class="space-y-1 border-t border-line dark:border-line pt-3">
                        <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold block">Gym Organization</span>
                        <p class="font-semibold text-fg dark:text-white">{{ $ticket->tenant->name ?? 'Gym' }}</p>
                    </div>

                    <!-- Timeline -->
                    <div class="space-y-1 border-t border-line dark:border-line pt-3 text-xs text-fg-muted dark:text-fg-muted">
                        <p><strong class="text-fg-muted dark:text-fg-muted">Opened:</strong> {{ $ticket->created_at->format('d M Y, h:i A') }}</p>
                        @if($ticket->resolved_at)
                            <p><strong class="text-emerald-600 dark:text-emerald-400">Resolved:</strong> {{ $ticket->resolved_at->format('d M Y, h:i A') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== RESOLVE & CLOSE CONFIRMATION MODAL ==================== -->
        <div x-show="showCloseModal" x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="showCloseModal = false"
                 class="w-full max-w-md max-h-[90vh] overflow-y-auto bg-surface dark:bg-surface border border-line dark:border-line rounded-xl sm:rounded-xl p-4 sm:p-6 space-y-4 sm:space-y-5"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 dark:border-emerald-500/30 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-fg dark:text-white tracking-tight">Mark Ticket as Resolved?</h3>
                        <p class="text-xs text-fg-muted dark:text-fg-muted mt-1 leading-relaxed">
                            Are you sure you want to mark ticket <strong class="text-indigo-600 dark:text-indigo-400 font-mono">#{{ $ticket->ticket_number }}</strong> as resolved and closed?
                        </p>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-raised dark:bg-slate-950/70 border border-line dark:border-line text-xs text-fg-muted dark:text-fg-muted flex items-center gap-2">
                    <svg class="w-4 h-4 text-fg-muted dark:text-fg-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>All messages and attachments will remain accessible in read-only mode.</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showCloseModal = false"
                            class="px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:hover:text-white text-xs font-semibold transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" form="closeTicketForm"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition-all cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Yes, Close Ticket</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
