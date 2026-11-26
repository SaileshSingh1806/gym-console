<x-app-layout header="Leads CRM & Prospects">
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-xs font-semibold text-fg dark:text-white">Prospect Inquiries & Follow-ups</h3>
        </div>

        <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-raised dark:bg-slate-950/60 border-b border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs">
                        <tr>
                            <th class="py-3 px-4">Lead Name</th>
                            <th class="py-3 px-4">Phone</th>
                            <th class="py-3 px-4">Source</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Next Follow-up</th>
                            <th class="py-3 px-4">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                        @forelse($leads as $lead)
                            <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                <td class="py-3 px-4 font-semibold text-fg dark:text-white">{{ $lead->name }}</td>
                                <td class="py-3 px-4 text-fg-muted dark:text-fg-muted">{{ $lead->phone }}</td>
                                <td class="py-3 px-4 text-xs text-amber-600 dark:text-amber-400 font-semibold">{{ str_replace('_', ' ', $lead->source) }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                        {{ $lead->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-fg-muted dark:text-fg-muted">{{ $lead->follow_up_date ? $lead->follow_up_date->format('M d, Y') : '—' }}</td>
                                <td class="py-3 px-4 text-fg-muted dark:text-fg-muted text-xs max-w-xs truncate">{{ $lead->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-fg-muted dark:text-fg-muted">No prospect leads found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
