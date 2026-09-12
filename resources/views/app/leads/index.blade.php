<x-app-layout header="Leads CRM & Prospects">
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Prospect Inquiries & Follow-ups</h3>
        </div>

        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Lead Name</th>
                            <th class="py-3 px-4">Phone</th>
                            <th class="py-3 px-4">Source</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Next Follow-up</th>
                            <th class="py-3 px-4">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($leads as $lead)
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-3 px-4 font-bold text-white">{{ $lead->name }}</td>
                                <td class="py-3 px-4 text-slate-400">{{ $lead->phone }}</td>
                                <td class="py-3 px-4 uppercase text-[10px] text-amber-400 font-semibold">{{ str_replace('_', ' ', $lead->source) }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        {{ $lead->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-400">{{ $lead->follow_up_date ? $lead->follow_up_date->format('M d, Y') : '—' }}</td>
                                <td class="py-3 px-4 text-slate-500 text-[11px] max-w-xs truncate">{{ $lead->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-500">No prospect leads found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

@include('app.crm.leads')
