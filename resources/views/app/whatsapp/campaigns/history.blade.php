<x-app-layout header="">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-surface dark:bg-surface p-5 rounded-xl border border-line dark:border-line">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-fg-muted mb-1">
                    <a href="{{ route('app.whatsapp.campaigns.index') }}" class="hover:text-emerald-600 transition-colors">&larr; Back to Campaigns</a>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-semibold text-fg dark:text-white tracking-tight">{{ $campaign->name }}</h1>
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
                <p class="text-xs text-fg-muted dark:text-fg-muted mt-1 font-medium">
                    Template: <span class="font-semibold text-fg-muted dark:text-fg font-mono">{{ $campaign->template?->name }}</span> &bull; Launched: {{ $campaign->started_at ? $campaign->started_at->format('d M Y, h:i A') : 'Pending' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if($campaign->status === 'running')
                    <form action="{{ route('app.whatsapp.campaigns.pause', $campaign->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 text-white font-semibold text-xs hover:bg-amber-600 cursor-pointer">Pause Campaign</button>
                    </form>
                @elseif($campaign->status === 'paused')
                    <form action="{{ route('app.whatsapp.campaigns.resume', $campaign->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold text-xs hover:bg-emerald-700 cursor-pointer">Resume Campaign</button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Metric KPI Funnel Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-3.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-center">
                <span class="text-xs font-semibold text-fg-muted">Target Recipients</span>
                <div class="text-xl font-semibold text-fg dark:text-white mt-0.5">{{ number_format($campaign->total_recipients) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-blue-500/10 border border-blue-500/20 text-center">
                <span class="text-xs font-semibold text-blue-600">Dispatched</span>
                <div class="text-xl font-semibold text-blue-600 mt-0.5">{{ number_format($campaign->sent_count) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-center">
                <span class="text-xs font-semibold text-emerald-600">Delivered</span>
                <div class="text-xl font-semibold text-emerald-600 mt-0.5">{{ number_format($campaign->delivered_count) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-center">
                <span class="text-xs font-semibold text-indigo-600">Read (Blue Ticks)</span>
                <div class="text-xl font-semibold text-indigo-600 mt-0.5">{{ number_format($campaign->read_count) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-center">
                <span class="text-xs font-semibold text-rose-600">Failed</span>
                <div class="text-xl font-semibold text-rose-600 mt-0.5">{{ number_format($campaign->failed_count) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-surface text-fg dark:bg-raised text-center">
                <span class="text-xs font-semibold text-fg-muted">Meta Charges (₹)</span>
                <div class="text-xl font-semibold text-emerald-400 mt-0.5">₹{{ number_format($campaign->actual_cost, 2) }}</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line">
            <form action="{{ route('app.whatsapp.campaigns.history', $campaign->id) }}" method="GET" class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="w-full sm:w-72 relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search recipient phone or name..." class="w-full pl-9 pr-4 py-2 rounded-xl bg-raised dark:bg-raised border border-line dark:border-line text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                    <svg class="w-4 h-4 text-fg-muted absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <select name="status" class="px-3 py-2 rounded-xl bg-raised dark:bg-raised border border-line dark:border-line text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                        <option value="">All Recipient Statuses</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>

                    <button type="submit" class="px-4 py-2 rounded-xl bg-surface text-fg font-semibold text-xs hover:bg-raised cursor-pointer">Filter</button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('app.whatsapp.campaigns.history', $campaign->id) }}" class="px-3 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted text-xs font-semibold">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Recipients Table -->
        <div class="bg-surface dark:bg-surface rounded-xl border border-line dark:border-line overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-line dark:border-line bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted font-semibold text-xs">
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
                    <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted font-medium">
                        @forelse($recipients as $r)
                            <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                <td class="py-3 px-4 font-semibold text-fg dark:text-white">
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
                                <td class="py-3 px-4 font-mono text-xs text-fg-muted">
                                    {{ $r->wamid ? substr($r->wamid, 0, 16).'...' : '—' }}
                                </td>
                                <td class="py-3 px-4 text-fg-muted">
                                    {{ $r->sent_at ? $r->sent_at->format('d M, h:i A') : '—' }}
                                </td>
                                <td class="py-3 px-4 text-fg-muted">
                                    {{ $r->delivered_at ? $r->delivered_at->format('d M, h:i A') : '—' }}
                                </td>
                                <td class="py-3 px-4 text-fg-muted">
                                    {{ $r->read_at ? $r->read_at->format('d M, h:i A') : '—' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($r->error_message)
                                        <span class="text-rose-600 dark:text-rose-400 font-semibold text-xs" title="{{ $r->error_message }}">
                                            {{ Str::limit($r->error_message, 40) }}
                                        </span>
                                    @else
                                        <span class="text-fg-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-fg-muted">
                                    No recipient logs found for this filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($recipients->hasPages())
                <div class="p-4 border-t border-line dark:border-line">
                    {{ $recipients->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

