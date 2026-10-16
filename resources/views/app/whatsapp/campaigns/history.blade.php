<x-app-layout header="">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-500 mb-1">
                    <a href="{{ route('app.whatsapp.campaigns.index') }}" class="hover:text-emerald-600 transition-colors">&larr; Back to Campaigns</a>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $campaign->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                        {{ $campaign->status === 'completed' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}
                        {{ $campaign->status === 'running' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 animate-pulse' : '' }}
                        {{ $campaign->status === 'draft' ? 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20' : '' }}
                        {{ $campaign->status === 'paused' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : '' }}
                        {{ $campaign->status === 'cancelled' ? 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20' : '' }}
                        {{ $campaign->status === 'failed' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : '' }}
                    ">
                        {{ $campaign->status }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">
                    Template: <span class="font-bold text-slate-700 dark:text-slate-200 font-mono">{{ $campaign->template?->name }}</span> &bull; Launched: {{ $campaign->started_at ? $campaign->started_at->format('d M Y, h:i A') : 'Pending' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if($campaign->status === 'running')
                    <form action="{{ route('app.whatsapp.campaigns.pause', $campaign->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 text-white font-bold text-xs hover:bg-amber-600 cursor-pointer">Pause Campaign</button>
                    </form>
                @elseif($campaign->status === 'paused')
                    <form action="{{ route('app.whatsapp.campaigns.resume', $campaign->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 cursor-pointer">Resume Campaign</button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Metric KPI Funnel Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-center">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Target Recipients</span>
                <div class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ number_format($campaign->total_recipients) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-blue-500/10 border border-blue-500/20 text-center">
                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Dispatched</span>
                <div class="text-xl font-black text-blue-600 mt-0.5">{{ number_format($campaign->sent_count) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-center">
                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Delivered</span>
                <div class="text-xl font-black text-emerald-600 mt-0.5">{{ number_format($campaign->delivered_count) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-center">
                <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">Read (Blue Ticks)</span>
                <div class="text-xl font-black text-indigo-600 mt-0.5">{{ number_format($campaign->read_count) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-center">
                <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Failed</span>
                <div class="text-xl font-black text-rose-600 mt-0.5">{{ number_format($campaign->failed_count) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-900 text-white dark:bg-slate-800 text-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Meta Charges (₹)</span>
                <div class="text-xl font-black text-emerald-400 mt-0.5">₹{{ number_format($campaign->actual_cost, 2) }}</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <form action="{{ route('app.whatsapp.campaigns.history', $campaign->id) }}" method="GET" class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="w-full sm:w-72 relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search recipient phone or name..." class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <select name="status" class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                        <option value="">All Recipient Statuses</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>

                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 cursor-pointer">Filter</button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('app.whatsapp.campaigns.history', $campaign->id) }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Recipients Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Recipient</th>
                            <th class="py-3 px-4">Mobile Number</th>
                            <th class="py-3 px-4">Meta Status</th>
                            <th class="py-3 px-4">Meta WAMID</th>
                            <th class="py-3 px-4">Sent At</th>
                            <th class="py-3 px-4">Delivered At</th>
                            <th class="py-3 px-4">Read At</th>
                            <th class="py-3 px-4">Error / Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($recipients as $r)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                    {{ $r->contact?->name ?? 'Contact' }}
                                </td>
                                <td class="py-3 px-4 font-mono font-semibold">
                                    {{ $r->phone }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                                        {{ $r->status === 'read' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20' : '' }}
                                        {{ $r->status === 'delivered' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}
                                        {{ $r->status === 'sent' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20' : '' }}
                                        {{ $r->status === 'failed' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : '' }}
                                        {{ $r->status === 'pending' || $r->status === 'queued' ? 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20' : '' }}
                                    ">
                                        {{ $r->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono text-[10px] text-slate-400">
                                    {{ $r->wamid ? substr($r->wamid, 0, 16).'...' : '—' }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    {{ $r->sent_at ? $r->sent_at->format('d M, h:i A') : '—' }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    {{ $r->delivered_at ? $r->delivered_at->format('d M, h:i A') : '—' }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    {{ $r->read_at ? $r->read_at->format('d M, h:i A') : '—' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($r->error_message)
                                        <span class="text-rose-600 dark:text-rose-400 font-semibold text-[11px]" title="{{ $r->error_message }}">
                                            {{ Str::limit($r->error_message, 40) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    No recipient logs found for this filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($recipients->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $recipients->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

