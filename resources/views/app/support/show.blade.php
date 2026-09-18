<x-app-layout header="Ticket #{{ $ticket->ticket_number }}">
    <div x-data="{ showCloseModal: false }" class="w-full space-y-4">

        <!-- Top Navigation / Breadcrumb -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.support.index') }}" 
                   class="p-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $ticket->ticket_number }}</span>
                        <h2 class="text-base font-black text-slate-900 dark:text-white tracking-tight">{{ $ticket->subject }}</h2>
                    </div>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Created {{ $ticket->created_at->format('d M Y, h:i A') }} ({{ $ticket->created_at->diffForHumans() }})</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if(!in_array($ticket->status, ['resolved', 'closed']))
                <form id="closeTicketForm" action="{{ route('app.support.close', $ticket->id) }}" method="POST">
                    @csrf
                </form>
                <button type="button" @click="showCloseModal = true" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:hover:text-white text-xs font-bold dark:border-slate-700/60 transition-colors cursor-pointer flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Mark Resolved &amp; Close</span>
                </button>
                @else
                    <span class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold border border-slate-200 dark:border-slate-700">
                        Ticket {{ ucfirst($ticket->status) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <!-- Left 3 Cols: Message Thread & Reply Box -->
            <div class="lg:col-span-3 space-y-4">
                
                <!-- Conversation Timeline -->
                <div class="space-y-4">
                    @foreach($ticket->replies as $reply)
                    <div class="p-4 sm:p-5 rounded-2xl border {{ $reply->is_admin_reply ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-500/30 shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 shadow-sm' }} space-y-3">
                        <!-- Reply Header -->
                        <div class="flex items-center justify-between gap-3 border-b {{ $reply->is_admin_reply ? 'border-indigo-100 dark:border-indigo-500/20' : 'border-slate-100 dark:border-slate-800' }} pb-2.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl {{ $reply->is_admin_reply ? 'bg-gradient-to-tr from-indigo-500 to-purple-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }} flex items-center justify-center font-bold text-xs shadow-sm">
                                    {{ substr($reply->user->name ?? ($reply->is_admin_reply ? 'Admin' : 'User'), 0, 1) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 dark:text-white text-xs">{{ $reply->user->name ?? ($reply->is_admin_reply ? 'Support Team' : 'User') }}</span>
                                        @if($reply->is_admin_reply)
                                            <span class="px-2 py-0.2 rounded-full text-[9px] font-black bg-indigo-500/15 text-indigo-700 dark:text-indigo-400 border border-indigo-500/30 uppercase tracking-wider">Support Agent</span>
                                        @else
                                            <span class="px-2 py-0.2 rounded-full text-[9px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Gym Member / Owner</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ $reply->created_at->format('d M Y, h:i A') }} ({{ $reply->created_at->diffForHumans() }})</span>
                                </div>
                            </div>
                        </div>

                        <!-- Reply Body -->
                        <div class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed whitespace-pre-line">
                            {{ $reply->message }}
                        </div>

                        <!-- Attachment (if any) -->
                        @if($reply->attachment_path)
                        <div class="pt-2 border-t {{ $reply->is_admin_reply ? 'border-indigo-100 dark:border-indigo-500/20' : 'border-slate-100 dark:border-slate-800/80' }}">
                            <a href="{{ asset('storage/'.$reply->attachment_path) }}" target="_blank" 
                               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950/80 hover:bg-slate-100 dark:hover:bg-slate-950 border border-slate-200 dark:border-slate-800 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-xs font-semibold transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span>{{ $reply->attachment_name ?? 'Download Attachment' }}</span>
                            </a>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                <!-- Post Reply Form -->
                @if($ticket->status !== 'closed')
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        <span>Post a Reply</span>
                    </h3>

                    <form action="{{ route('app.support.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
                        @csrf
                        <div>
                            <textarea name="message" rows="4" required placeholder="Type your response or additional information here..." 
                                      class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none text-xs"></textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                            <div class="flex-1 max-w-sm">
                                <input type="file" name="attachment" 
                                       class="w-full text-slate-600 dark:text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200 dark:hover:file:bg-slate-700 cursor-pointer text-xs">
                            </div>

                            <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span>Send Reply</span>
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400">
                    This ticket has been marked as closed. If you still have issues, you can submit a new ticket.
                </div>
                @endif
            </div>

            <!-- Right Col: Ticket Meta Sidebar -->
            <div class="space-y-4">
                <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 text-xs">
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-2.5">
                        Ticket Info
                    </h4>

                    <!-- Status -->
                    <div class="space-y-1">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-semibold block">Status</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border capitalize inline-block {{ $ticket->status_badge_color }}">
                            {{ str_replace('_', ' ', $ticket->status) }}
                        </span>
                    </div>

                    <!-- Priority -->
                    <div class="space-y-1">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-semibold block">Priority</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider inline-block {{ $ticket->priority_badge_color }}">
                            {{ $ticket->priority }}
                        </span>
                    </div>

                    <!-- Category -->
                    <div class="space-y-1">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-semibold block">Category</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono text-[10px] uppercase font-semibold inline-block">
                            {{ str_replace('_', ' ', $ticket->category) }}
                        </span>
                    </div>

                    <!-- Creator -->
                    <div class="space-y-1">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-semibold block">Submitted By</span>
                        <p class="font-bold text-slate-900 dark:text-white">{{ $ticket->user->name ?? 'User' }}</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $ticket->user->email ?? '' }}</p>
                    </div>

                    <!-- Gym -->
                    <div class="space-y-1 border-t border-slate-100 dark:border-slate-800 pt-3">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-semibold block">Gym Organization</span>
                        <p class="font-bold text-slate-900 dark:text-white">{{ $ticket->tenant->name ?? 'Gym' }}</p>
                    </div>

                    <!-- Timeline -->
                    <div class="space-y-1 border-t border-slate-100 dark:border-slate-800 pt-3 text-[10px] text-slate-500 dark:text-slate-400">
                        <p><strong class="text-slate-700 dark:text-slate-300">Opened:</strong> {{ $ticket->created_at->format('d M Y, h:i A') }}</p>
                        @if($ticket->resolved_at)
                            <p><strong class="text-emerald-600 dark:text-emerald-400">Resolved:</strong> {{ $ticket->resolved_at->format('d M Y, h:i A') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== RESOLVE & CLOSE CONFIRMATION MODAL ==================== -->
        <div x-show="showCloseModal" x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="showCloseModal = false"
                 class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl overflow-hidden p-6 space-y-5"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 dark:border-emerald-500/30 flex items-center justify-center shrink-0 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">Mark Ticket as Resolved?</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                            Are you sure you want to mark ticket <strong class="text-indigo-600 dark:text-indigo-400 font-mono">#{{ $ticket->ticket_number }}</strong> as resolved and closed?
                        </p>
                    </div>
                </div>

                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800/80 text-xs text-slate-600 dark:text-slate-400 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>All messages and attachments will remain accessible in read-only mode.</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showCloseModal = false"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:hover:text-white text-xs font-bold transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" form="closeTicketForm"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/25 transition-all cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Yes, Close Ticket</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
