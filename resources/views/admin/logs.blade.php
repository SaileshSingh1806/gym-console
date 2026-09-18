<x-admin-layout header="System Telemetry & Audit Logs">
    <div class="space-y-6">
        <!-- Top Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form action="{{ route('admin.logs') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-grow max-w-xl">
                <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action (e.g. member_created, subscription_activated)..." class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-red-500 focus:outline-none flex-grow shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <form action="{{ route('admin.logs') }}" method="GET" class="flex flex-wrap items-center gap-2 sm:gap-3 flex-grow max-w-xl w-full sm:w-auto">
                <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action (e.g. member_created)..." class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-red-500 focus:outline-none flex-grow min-w-[140px] shadow-xs">
                <select name="tenant_id" class="px-3 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-800 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                    <option value="">All Gyms</option>
                    @foreach($gyms as $g)
                        <option value="{{ $g->id }}" {{ request('tenant_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-white text-xs font-semibold border border-slate-300 dark:border-slate-700 transition-colors shadow-xs cursor-pointer">
                    Filter
                </button>
            </form>

            <form action="{{ route('admin.logs.clear') }}" method="POST"
                  data-confirm="Are you sure you want to permanently purge all system activity logs? This cannot be undone."
                  data-confirm-title="Purge Activity Logs"
                  data-confirm-btn="Purge Logs"
                  data-confirm-type="danger">
                  data-confirm-type="danger"
                  class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-red-500/10 hover:bg-red-600 hover:text-white text-red-600 dark:text-red-400 font-bold text-xs border border-red-500/20 transition-all cursor-pointer">
                <button type="submit" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-red-500/10 hover:bg-red-600 hover:text-white text-red-600 dark:text-red-400 font-bold text-xs border border-red-500/20 transition-all cursor-pointer">
                    Purge All Logs
                </button>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs dark:shadow-xl transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                <table class="w-full text-left text-xs min-w-[700px]">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 font-semibold">Timestamp</th>
                            <th class="py-3.5 px-4 font-semibold">Gym Tenant</th>
                            <th class="py-3.5 px-4 font-semibold">User</th>
                            <th class="py-3.5 px-4 font-semibold">Action</th>
                            <th class="py-3.5 px-4 font-semibold">Description</th>
                            <th class="py-3.5 px-4 font-semibold">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-3 px-4 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                    {{ $log->created_at->format('M d, H:i:s') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                    {{ $log->tenant->name ?? 'Global Platform' }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                                    {{ $log->user->name ?? 'System / IoT' }}
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-red-600 dark:text-red-400 text-[11px]">
                                    {{ $log->action }}
                                </td>
                                <td class="py-3 px-4 text-slate-700 dark:text-slate-300 text-[11px] max-w-sm truncate">
                                    {{ $log->description }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-400 dark:text-slate-500 text-[10px]">
                                    {{ $log->ip_address ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                    No audit telemetry logs recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
