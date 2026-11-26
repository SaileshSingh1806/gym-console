<x-app-layout header="">
    <div class="h-[calc(100vh-8.5rem)] flex flex-col space-y-4">
        <!-- Alerts -->
        @if(session('success'))
            <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between text-xs">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-fg-muted hover:text-fg-muted cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between text-xs">
                <span>{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-fg-muted hover:text-fg-muted cursor-pointer">✕</button>
            </div>
        @endif

        <!-- 2-Column WhatsApp Live Inbox Container -->
        <div class="flex-1 bg-surface dark:bg-surface rounded-xl border border-line dark:border-line overflow-hidden flex flex-col md:flex-row">
            
            <!-- Left Column: Conversation Directory -->
            <div class="w-full md:w-80 lg:w-96 border-r border-line dark:border-line flex flex-col bg-raised dark:bg-slate-900/50">
                <!-- Directory Search & Header -->
                <div class="p-4 border-b border-line dark:border-line space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold text-sm text-fg dark:text-white flex items-center gap-2">
                            <span>WhatsApp Chats</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        </h2>
                        <a href="{{ route('app.whatsapp.inbox') }}" class="text-xs font-semibold text-fg-muted hover:text-emerald-600">Refresh</a>
                    </div>

                    <form action="{{ route('app.whatsapp.inbox') }}" method="GET" class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer or mobile..." class="w-full pl-8 pr-3 py-1.5 rounded-xl bg-surface dark:bg-raised border border-line dark:border-line text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-fg">
                        <svg class="w-3.5 h-3.5 text-fg-muted absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </form>

                    <!-- Filter Tabs -->
                    <div class="flex items-center gap-2 text-xs font-semibold">
                        <a href="{{ route('app.whatsapp.inbox') }}" class="px-2.5 py-1 rounded-lg {{ !request()->has('filter') ? 'bg-slate-900 text-white dark:bg-slate-800' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50' }}">All</a>
                        <a href="{{ route('app.whatsapp.inbox', ['filter' => 'unread']) }}" class="px-2.5 py-1 rounded-lg {{ request('filter') === 'unread' ? 'bg-slate-900 text-white dark:bg-slate-800' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50' }}">Unread Only</a>
                    </div>
                </div>

                <!-- Conversation List -->
                <div class="flex-1 overflow-y-auto divide-y divide-line dark:divide-line">
                    @forelse($conversations as $conv)
                        <a href="{{ route('app.whatsapp.inbox', ['conversation_id' => $conv->id]) }}" class="p-3.5 flex items-start gap-3 hover:bg-white dark:hover:bg-slate-800/60 transition-colors {{ $activeConversation && $activeConversation->id === $conv->id ? 'bg-white dark:bg-slate-800 border-l-4 border-emerald-500 shadow-xs' : '' }}">
                            <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold text-sm flex items-center justify-center shrink-0 border border-emerald-500/20">
                                {{ substr($conv->contact?->name ?? 'U', 0, 1) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1">
                                    <h4 class="font-semibold text-xs text-fg dark:text-white truncate">{{ $conv->contact?->name ?? 'Contact' }}</h4>
                                    <span class="text-xs text-fg-muted shrink-0">{{ $conv->last_message_at ? $conv->last_message_at->diffForHumans(null, true) : '' }}</span>
                                </div>

                                <div class="font-mono text-xs text-fg-muted">{{ $conv->phone }}</div>

                                <p class="text-xs text-fg-muted dark:text-fg-muted truncate mt-0.5">
                                    {{ $conv->last_message_preview ?: 'No messages yet' }}
                                </p>
                            </div>

                            @if($conv->unread_count > 0)
                                <span class="px-1.5 py-0.5 rounded-full bg-emerald-500 text-slate-950 font-semibold text-xs shrink-0">{{ $conv->unread_count }}</span>
                            @endif
                        </a>
                    @empty
                        <div class="p-8 text-center text-fg-muted text-xs">
                            No conversations match your search.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right Column: Live Chat Pane -->
            @if($activeConversation)
                <div class="flex-1 flex flex-col bg-raised dark:bg-slate-950/40">
                    <!-- Chat Header with Customer Service Window Timer -->
                    <div class="p-4 bg-surface dark:bg-surface border-b border-line dark:border-line flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-600 font-semibold text-sm flex items-center justify-center shrink-0 border border-emerald-500/20">
                                {{ substr($activeConversation->contact?->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-sm text-fg dark:text-white">{{ $activeConversation->contact?->name ?? 'Contact' }}</h3>
                                    <span class="font-mono text-xs text-fg-muted">{{ $activeConversation->phone }}</span>
                                </div>
                                <div class="text-xs text-fg-muted mt-0.5 flex items-center gap-2">
                                    <span>Type: <strong class="text-fg-muted dark:text-fg-muted">{{ $activeConversation->contact?->type ?? 'external' }}</strong></span>
                                    <span>&bull;</span>
                                    <span>Consent: 
                                        @if($activeConversation->contact?->consent_status === 'opted_in')
                                            <span class="text-emerald-500 font-semibold">Opted In</span>
                                        @elseif($activeConversation->contact?->consent_status === 'opted_out')
                                            <span class="text-rose-500 font-semibold">Opted Out (STOP)</span>
                                        @else
                                            <span class="text-amber-500 font-semibold">Unknown</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- 24-hr Service Window & Consent Toggle -->
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($activeConversation->isCustomerServiceWindowOpen())
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5" title="Free-form text message replies are permitted within 24 hours of customer's last message">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    24h Customer Window Active
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center gap-1.5" title="Meta requires an approved template message when customer has not messaged in the last 24 hours">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    24h Window Expired (Use Template)
                                </span>
                            @endif

                            <form action="{{ route('app.whatsapp.inbox.toggle-optout', $activeConversation->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-colors cursor-pointer {{ $activeConversation->contact?->consent_status === 'opted_out' ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                                    {{ $activeConversation->contact?->consent_status === 'opted_out' ? 'Opt-In Contact' : 'Revoke / Opt-Out' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Chat Stream -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-3" id="chat-messages-container">
                        @forelse($activeMessages as $msg)
                            @if($msg->direction === 'inbound')
                                <!-- Inbound Bubble (Left) -->
                                <div class="flex items-start gap-2 max-w-[80%]">
                                    <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl rounded-tl-xs p-3.5 text-xs text-fg dark:text-fg space-y-1">
                                        <div class="whitespace-pre-line leading-relaxed">{{ $msg->message_body }}</div>
                                        <div class="text-xs text-fg-muted text-right">{{ $msg->created_at->format('h:i A') }}</div>
                                    </div>
                                </div>
                            @else
                                <!-- Outbound Bubble (Right) -->
                                <div class="flex items-start justify-end gap-2 ml-auto max-w-[80%]">
                                    <div class="bg-emerald-600 text-white rounded-xl rounded-tr-xs p-3.5 text-xs space-y-1">
                                        @if($msg->type === 'template')
                                            <div class="text-xs font-semibold text-emerald-200 pb-1 border-b border-emerald-500/40">Template Broadcast</div>
                                        @endif
                                        <div class="whitespace-pre-line leading-relaxed">{{ $msg->message_body }}</div>
                                        <div class="text-xs text-emerald-200 flex items-center justify-end gap-1">
                                            <span>{{ $msg->created_at->format('h:i A') }}</span>
                                            @if($msg->status === 'read')
                                                <span class="text-white font-semibold" title="Read by customer">✓✓</span>
                                            @elseif($msg->status === 'delivered')
                                                <span class="text-emerald-200" title="Delivered">✓✓</span>
                                            @elseif($msg->status === 'sent')
                                                <span class="text-emerald-300" title="Sent">✓</span>
                                            @elseif($msg->status === 'failed')
                                                <span class="text-rose-200 font-semibold" title="{{ $msg->error_message }}">⚠️</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="h-full flex items-center justify-center text-fg-muted text-xs">
                                No messages in this conversation yet.
                            </div>
                        @endforelse
                    </div>

                    <!-- Message Reply Composer -->
                    <div class="p-3.5 bg-surface dark:bg-surface border-t border-line dark:border-line">
                        @if($activeConversation->isCustomerServiceWindowOpen())
                            <!-- Free-form text reply form -->
                            <form action="{{ route('app.whatsapp.inbox.reply-text', $activeConversation->id) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <input type="text" name="message" required placeholder="Type your WhatsApp reply..." class="flex-1 px-4 py-2.5 rounded-xl bg-raised dark:bg-raised border border-line dark:border-line text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                                    <span>Send</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </form>
                        @else
                            <!-- 24h Window Expired: Require Meta Template Selection -->
                            <form action="{{ route('app.whatsapp.inbox.reply-template', $activeConversation->id) }}" method="POST" class="space-y-2">
                                @csrf
                                <div class="flex items-center justify-between text-xs text-amber-600 dark:text-amber-400 font-medium">
                                    <span>⚠️ 24-hr customer service window closed. Meta requires sending an approved template to re-open chat.</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select name="template_id" required class="flex-1 px-3 py-2 rounded-xl bg-raised dark:bg-raised border border-line dark:border-line text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white font-mono font-semibold">
                                        <option value="">-- Choose Approved Meta Template --</option>
                                        @foreach($approvedTemplates as $at)
                                            <option value="{{ $at->id }}">{{ $at->name }} ({{ $at->category }})</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-surface text-fg hover:bg-raised font-semibold text-xs cursor-pointer">
                                        Send Template
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                <!-- No Active Conversation Selected -->
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-fg-muted">
                    <svg class="w-16 h-16 mx-auto text-fg-muted dark:text-fg-muted mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <h3 class="text-sm font-semibold text-fg-muted dark:text-fg-muted">Select a conversation</h3>
                    <p class="text-xs text-fg-muted mt-1">Choose a customer from the left directory to inspect conversation and reply.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Auto scroll to bottom of chat container
        const container = document.getElementById('chat-messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    </script>
</x-app-layout>

