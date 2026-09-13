<x-admin-layout header="Ticket #{{ $ticket->ticket_number }}">
<div class="space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tickets.index') }}" 
               class="p-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white border border-slate-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-xs font-bold text-red-400">{{ $ticket->ticket_number }}</span>
                    <h1 class="text-xl font-black text-white tracking-tight">{{ $ticket->subject }}</h1>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-400 mt-1">
                    <span>From <strong>{{ $ticket->tenant->name ?? 'Gym Tenant' }}</strong></span>
                    <span>•</span>
                    <span>By {{ $ticket->user->name ?? 'User' }} ({{ $ticket->user->email ?? '' }})</span>
                    <span>•</span>
                    <span>{{ $ticket->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('admin.tickets.delete', $ticket->id) }}" method="POST"
                  data-confirm="Are you sure you want to delete ticket #{{ $ticket->ticket_number }} ({{ $ticket->subject }})? All conversation history will be permanently removed."
                  data-confirm-title="Delete Support Ticket"
                  data-confirm-btn="Delete Ticket"
                  data-confirm-type="danger">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs font-bold border border-rose-500/30 transition-colors cursor-pointer flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Delete Ticket</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Left 3 Cols: Conversation Timeline & Admin Reply Box -->
        <div class="lg:col-span-3 space-y-6">
            
            <!-- Message Thread -->
            <div class="space-y-4">
                @foreach($ticket->replies as $reply)
                <div class="p-5 rounded-2xl border {{ $reply->is_admin_reply ? 'bg-red-950/20 border-red-500/30 shadow-lg shadow-red-950/20' : 'bg-slate-900 border-slate-800' }} space-y-3">
                    <!-- Reply Header -->
                    <div class="flex items-center justify-between gap-3 border-b {{ $reply->is_admin_reply ? 'border-red-500/20' : 'border-slate-800' }} pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl {{ $reply->is_admin_reply ? 'bg-gradient-to-tr from-red-500 to-rose-600 text-white' : 'bg-slate-800 text-slate-300' }} flex items-center justify-center font-bold text-xs shadow-sm">
                                {{ substr($reply->user->name ?? ($reply->is_admin_reply ? 'Admin' : 'User'), 0, 1) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-white text-xs">{{ $reply->user->name ?? ($reply->is_admin_reply ? 'Super Admin' : 'User') }}</span>
                                    @if($reply->is_admin_reply)
                                        <span class="px-2 py-0.2 rounded-full text-[9px] font-black bg-red-500/20 text-red-400 border border-red-500/30 uppercase tracking-wider">Super Admin</span>
                                    @else
                                        <span class="px-2 py-0.2 rounded-full text-[9px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">Gym Owner</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-500 block">{{ $reply->created_at->format('d M Y, h:i A') }} ({{ $reply->created_at->diffForHumans() }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Message Body -->
                    <div class="text-xs text-slate-200 leading-relaxed whitespace-pre-line">
                        {{ $reply->message }}
                    </div>

                    <!-- Attachment -->
                    @if($reply->attachment_path)
                    <div class="pt-2 border-t {{ $reply->is_admin_reply ? 'border-red-500/20' : 'border-slate-800/80' }}">
                        <a href="{{ asset('storage/'.$reply->attachment_path) }}" target="_blank" 
                           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-950/80 hover:bg-slate-950 border border-slate-800 text-red-400 hover:text-red-300 text-xs font-semibold transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span>{{ $reply->attachment_name ?? 'Download Attachment' }}</span>
                        </a>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Admin Reply Box -->
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        <span>Post Official Response</span>
                    </h3>
                    <span class="text-[11px] text-slate-400">An automated email notification will be dispatched to the gym owner.</span>
                </div>

                <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="font-bold text-slate-300 block mb-1.5">Response Message <span class="text-red-400">*</span></label>
                        <textarea name="message" rows="5" required placeholder="Type the official support response to the gym owner..." 
                                  class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-red-500 focus:outline-none text-xs"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="font-bold text-slate-300">Update Ticket Status To</label>
                            <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none text-xs cursor-pointer">
                                <option value="answered" selected>Answered (Waiting for Gym Owner)</option>
                                <option value="in_progress">In Progress (Investigating)</option>
                                <option value="resolved">Resolved (Problem Solved)</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="font-bold text-slate-300">Attachment (Optional)</label>
                            <input type="file" name="attachment" 
                                   class="w-full px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700 cursor-pointer text-xs">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold text-xs shadow-lg shadow-red-500/20 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Send Response & Notify Gym</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Col: Ticket & Tenant Meta Card -->
        <div class="space-y-6">
            
            <!-- Quick Status & Priority Updater Card -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-4 text-xs">
                <h4 class="text-xs font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-2.5">
                    Ticket Controls
                </h4>

                <form action="{{ route('admin.tickets.status', $ticket->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="space-y-1">
                        <label class="text-[10px] text-slate-400 uppercase font-bold">Status</label>
                        <select name="status" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="answered" {{ $ticket->status === 'answered' ? 'selected' : '' }}>Answered</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] text-slate-400 uppercase font-bold">Priority</label>
                        <select name="priority" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none cursor-pointer">
                            <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition-colors">
                        Update Status & Priority
                    </button>
                </form>
            </div>

            <!-- Gym / Tenant Details Card -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-4 text-xs">
                <h4 class="text-xs font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-2.5">
                    Tenant Information
                </h4>

                <div class="space-y-2.5">
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase block font-semibold">Gym / Tenant</span>
                        <p class="font-bold text-white text-sm">{{ $ticket->tenant->name ?? 'N/A' }}</p>
                        <p class="text-[10px] text-slate-400 font-mono">{{ $ticket->tenant->slug ?? '' }}</p>
                    </div>

                    <div>
                        <span class="text-[10px] text-slate-400 uppercase block font-semibold">Active SaaS Plan</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 inline-block mt-0.5">
                            {{ $ticket->tenant->activeSubscription?->plan?->name ?? 'No Active Plan' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[10px] text-slate-400 uppercase block font-semibold">Ticket Creator</span>
                        <p class="font-bold text-white">{{ $ticket->user->name ?? 'N/A' }}</p>
                        <p class="text-[11px] text-slate-400">{{ $ticket->user->email ?? 'N/A' }}</p>
                        @if($ticket->user?->phone)
                            <p class="text-[11px] text-slate-400">{{ $ticket->user->phone }}</p>
                        @endif
                    </div>

                    <div class="border-t border-slate-800 pt-3 flex items-center justify-between">
                        <form action="{{ route('admin.gyms.impersonate', $ticket->tenant_id) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="w-full py-2 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-400 font-bold text-xs border border-indigo-500/30 transition-colors flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                <span>Impersonate Gym Portal</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-admin-layout>
