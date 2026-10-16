<x-app-layout header="">
    <div class="space-y-6">
        <!-- Success / Error Alerts -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-sm cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-sm cursor-pointer">✕</button>
            </div>
        @endif

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500 shadow-sm shrink-0">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">WhatsApp Marketing Hub</h1>
                        @if($isConfigured)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Meta Cloud API Active
                            </span>
                        @else
                            <a href="{{ route('app.whatsapp.settings') }}" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Setup Required ⚙️
                            </a>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">Official Meta WhatsApp Cloud API — Zero 3rd party middleman fees</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('app.whatsapp.campaigns.index') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>New Campaign</span>
                </a>
                <a href="{{ route('app.whatsapp.inbox') }}" class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span>WhatsApp Inbox</span>
                    @if($customerReplies > 0)
                        <span class="px-1.5 py-0.5 rounded-full bg-emerald-500 text-slate-950 font-black text-[10px]">{{ $customerReplies }}</span>
                    @endif
                </a>
                <a href="{{ route('app.whatsapp.settings') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" title="Settings">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </a>
            </div>
        </div>

        <!-- Quick Navigation Bar -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none text-xs font-bold">
            <a href="{{ route('app.whatsapp.dashboard') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white dark:bg-slate-800 dark:text-white shrink-0 shadow-sm">Dashboard</a>
            <a href="{{ route('app.whatsapp.contacts.index') }}" class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 shrink-0 transition-colors">Contacts</a>
            <a href="{{ route('app.whatsapp.contacts.import') }}" class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 shrink-0 transition-colors">Import CSV</a>
            <a href="{{ route('app.whatsapp.templates.index') }}" class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 shrink-0 transition-colors">Templates</a>
            <a href="{{ route('app.whatsapp.campaigns.index') }}" class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 shrink-0 transition-colors">Bulk Campaigns</a>
            <a href="{{ route('app.whatsapp.inbox') }}" class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 shrink-0 transition-colors">Live Inbox</a>
            <a href="{{ route('app.whatsapp.automations.index') }}" class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 shrink-0 transition-colors">Automations</a>
            <a href="{{ route('app.whatsapp.settings') }}" class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 shrink-0 transition-colors">Settings</a>
        </div>

        <!-- Metric KPI Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Contacts -->
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Contacts</span>
                    <span class="p-2 rounded-xl bg-blue-500/10 text-blue-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ number_format($totalContacts) }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                        <span class="text-emerald-500 font-bold">{{ number_format($optedInContacts) }}</span> opted in for marketing
                    </div>
                </div>
            </div>

            <!-- Opted-in Contacts (Marketing Eligible) -->
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Opted-In Audience</span>
                    <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($optedInContacts) }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                        @if($totalContacts > 0)
                            <span class="font-bold text-slate-900 dark:text-white">{{ round(($optedInContacts / $totalContacts) * 100) }}%</span> of database eligible
                        @else
                            <span>No contacts yet</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Messages Sent -->
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Messages Sent</span>
                    <span class="p-2 rounded-xl bg-purple-500/10 text-purple-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ number_format($messagesSent) }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                        <span class="text-emerald-500 font-bold">{{ $deliveryRate }}%</span> delivery rate
                    </div>
                </div>
            </div>

            <!-- Customer Inbound Replies -->
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Customer Replies</span>
                    <span class="p-2 rounded-xl bg-amber-500/10 text-amber-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl sm:text-3xl font-black text-amber-500">{{ number_format($customerReplies) }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Inbound enquiries & responses</div>
                </div>
            </div>
        </div>

        <!-- Performance Funnel & Status Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Funnel Breakdown -->
            <div class="lg:col-span-2 p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight mb-4 flex items-center justify-between">
                    <span>Message Delivery & Engagement Performance</span>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Outbound: {{ number_format($messagesSent) }}</span>
                </h3>

                <div class="space-y-4">
                    <!-- Sent -->
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-slate-700 dark:text-slate-300">1. Dispatched via Meta API</span>
                            <span class="text-slate-900 dark:text-white font-extrabold">{{ number_format($messagesSent) }} (100%)</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-blue-500 h-full rounded-full" style="width: 100%"></div>
                        </div>
                    </div>

                    <!-- Delivered -->
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-slate-700 dark:text-slate-300">2. Delivered to Recipient Phone</span>
                            <span class="text-emerald-500 font-extrabold">{{ number_format($deliveredMessages) }} ({{ $deliveryRate }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width: {{ $deliveryRate }}%"></div>
                        </div>
                    </div>

                    <!-- Read -->
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-slate-700 dark:text-slate-300">3. Read by Customer (Blue Ticks)</span>
                            <span class="text-indigo-500 font-extrabold">{{ number_format($readMessages) }} ({{ $readRate }}% of delivered)</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-indigo-500 h-full rounded-full transition-all duration-500" style="width: {{ $readRate }}%"></div>
                        </div>
                    </div>

                    <!-- Failed -->
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-slate-700 dark:text-slate-300">Failed / Rejected by Meta</span>
                            <span class="text-rose-500 font-extrabold">{{ number_format($failedMessages) }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                            @php $failRate = $messagesSent > 0 ? round(($failedMessages / $messagesSent) * 100, 1) : 0; @endphp
                            <div class="bg-rose-500 h-full rounded-full transition-all duration-500" style="width: {{ $failRate }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <div class="text-[11px] font-bold text-slate-500 uppercase">Avg Delivery Rate</div>
                        <div class="text-lg font-black text-emerald-500 mt-0.5">{{ $deliveryRate }}%</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <div class="text-[11px] font-bold text-slate-500 uppercase">Read Rate</div>
                        <div class="text-lg font-black text-indigo-500 mt-0.5">{{ $readRate }}%</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <div class="text-[11px] font-bold text-slate-500 uppercase">Active Campaigns</div>
                        <div class="text-lg font-black text-slate-900 dark:text-white mt-0.5">{{ $activeCampaigns }}</div>
                    </div>
                </div>
            </div>

            <!-- Quick Meta API Health Card -->
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight mb-2">Meta Cloud API Gateway</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Direct server-to-server connection to Meta Graph API v22.0.</p>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                            <span class="font-semibold text-slate-600 dark:text-slate-400">Connection Status:</span>
                            @if($isConfigured)
                                <span class="font-bold text-emerald-500 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Ready
                                </span>
                            @else
                                <span class="font-bold text-amber-500 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    Unconfigured
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                            <span class="font-semibold text-slate-600 dark:text-slate-400">Marketing Cost (India):</span>
                            <span class="font-bold text-slate-900 dark:text-white">₹{{ config('services.whatsapp.marketing_rate', 0.85) }} / conv</span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                            <span class="font-semibold text-slate-600 dark:text-slate-400">Utility Cost (India):</span>
                            <span class="font-bold text-slate-900 dark:text-white">₹{{ config('services.whatsapp.utility_rate', 0.35) }} / conv</span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                            <span class="font-semibold text-slate-600 dark:text-slate-400">Webhook Status:</span>
                            <span class="font-bold text-emerald-500">Listening (/api/whatsapp/webhook)</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex gap-2">
                    <button type="button" onclick="testWhatsAppApiConnection()" class="flex-1 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-white text-xs font-bold transition-colors cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Test API</span>
                    </button>
                    <a href="{{ route('app.whatsapp.settings') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-white text-xs font-bold transition-colors">
                        Settings ⚙️
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Campaigns & Recent Incoming Messages Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Campaigns -->
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">Recent Broadcast Campaigns</h3>
                    <a href="{{ route('app.whatsapp.campaigns.index') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">View All &rarr;</a>
                </div>

                @if($recentCampaigns->isEmpty())
                    <div class="p-8 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <p class="text-xs">No broadcast campaigns launched yet.</p>
                        <a href="{{ route('app.whatsapp.campaigns.index') }}" class="mt-3 inline-block px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold">Create First Campaign</a>
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($recentCampaigns as $c)
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white text-xs truncate max-w-[200px] sm:max-w-xs">{{ $c->name }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        Template: <span class="font-semibold">{{ $c->template?->name ?? 'N/A' }}</span> &bull; {{ $c->created_at->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                                        {{ $c->status === 'completed' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}
                                        {{ $c->status === 'running' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 animate-pulse' : '' }}
                                        {{ $c->status === 'draft' ? 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20' : '' }}
                                        {{ $c->status === 'failed' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : '' }}
                                    ">
                                        {{ $c->status }}
                                    </span>
                                    <div class="text-[11px] font-semibold text-slate-500 mt-1">
                                        {{ $c->sent_count }}/{{ $c->total_recipients }} sent
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Recent Customer Inbound Messages -->
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">Recent Inbound Responses</h3>
                    <a href="{{ route('app.whatsapp.inbox') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Open Inbox &rarr;</a>
                </div>

                @if($recentReplies->isEmpty())
                    <div class="p-8 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <p class="text-xs">No customer replies received yet.</p>
                        <p class="text-[11px] text-slate-400 mt-1">Incoming messages and webhook events will appear here automatically.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($recentReplies as $r)
                            <a href="{{ route('app.whatsapp.inbox', ['conversation_id' => $r->conversation_id]) }}" class="py-3 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-slate-800/40 rounded-xl px-2 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-500/10 text-emerald-600 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ substr($r->contact?->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $r->contact?->name ?? 'Contact' }} ({{ $r->contact?->phone }})</div>
                                        <div class="text-[11px] text-slate-600 dark:text-slate-400 truncate max-w-[200px] sm:max-w-xs mt-0.5">{{ $r->message_body }}</div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 text-[10px] text-slate-400">
                                    {{ $r->created_at->diffForHumans(null, true) }}
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- API Test Script -->
    <script>
        function testWhatsAppApiConnection() {
            if (!confirm('Test live connection to Meta WhatsApp Cloud API?')) return;
            
            fetch("{{ route('app.whatsapp.settings.test-connection') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Meta API Success!\n\n' + data.message + '\nVerified Name: ' + (data.data?.verified_name || 'N/A') + '\nPhone: ' + (data.data?.display_phone_number || 'N/A'));
                } else {
                    alert('❌ Connection Test Failed:\n\n' + data.message);
                }
            })
            .catch(err => {
                alert('Request failed: ' + err.message);
            });
        }
    </script>
</x-app-layout>

