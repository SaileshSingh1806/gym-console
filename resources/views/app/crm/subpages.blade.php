<x-app-layout header="{{ $title ?? 'CRM' }}">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
    @endphp

    <div class="space-y-6">

        <!-- Notifications -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white text-xs font-bold p-1">✕</button>
            </div>
        @endif

        <!-- ==================== HEADER ==================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/15 text-indigo-400 border border-indigo-500/20 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
                        <span>{{ $title }}</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $subtitle }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('app.crm.dashboard') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white text-xs font-bold border border-slate-800 flex items-center gap-1.5 transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>CRM Dashboard</span>
                </a>
            </div>
        </div>

        <!-- ==================== CONTENT SECTION: ENQUIRIES ==================== -->
        @if($section === 'enquiries')
            <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-md">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                            <tr>
                                <th class="py-3 px-4">Prospect Name</th>
                                <th class="py-3 px-4">Contact</th>
                                <th class="py-3 px-4">Channel</th>
                                <th class="py-3 px-4">Remarks / Notes</th>
                                <th class="py-3 px-4">Received On</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            @forelse($enquiries as $enq)
                                <tr class="hover:bg-slate-800/30">
                                    <td class="py-3 px-4 font-bold text-white">{{ $enq->name }}</td>
                                    <td class="py-3 px-4">
                                        <div class="text-slate-200 font-mono">{{ $enq->phone }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $enq->email }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase">
                                            {{ str_replace('_', ' ', $enq->source) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-400">{{ $enq->remarks ?: ($enq->notes ?: 'General Enquiry') }}</td>
                                    <td class="py-3 px-4 text-slate-400 font-mono">{{ $enq->created_at ? $enq->created_at->format('d M Y') : '' }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $enq->phone) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px] inline-flex items-center gap-1 transition-all">
                                            <span>WhatsApp</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-500">No incoming prospect enquiries found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-t border-slate-800 bg-slate-950/40">
                    {{ $enquiries->links() }}
                </div>
            </div>
        @endif

        <!-- ==================== CONTENT SECTION: CONVERSIONS ==================== -->
        @if($section === 'conversions')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Converted Members</span>
                        <div class="text-2xl font-black text-emerald-400 mt-1">{{ $paidLeads->total() }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center font-bold">
                        ✓
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Value Won</span>
                        <div class="text-2xl font-black text-amber-400 mt-1">{{ $currency }}{{ number_format($totalRevenue) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center font-bold">
                        {{ $currency }}
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-md">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                            <tr>
                                <th class="py-3 px-4">Member Name</th>
                                <th class="py-3 px-4">Phone</th>
                                <th class="py-3 px-4">Acquisition Source</th>
                                <th class="py-3 px-4">Conversion Value</th>
                                <th class="py-3 px-4">Date Won</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            @forelse($paidLeads as $lead)
                                <tr class="hover:bg-slate-800/30">
                                    <td class="py-3 px-4 font-bold text-white">{{ $lead->name }}</td>
                                    <td class="py-3 px-4 text-slate-400 font-mono">{{ $lead->phone }}</td>
                                    <td class="py-3 px-4 capitalize">{{ str_replace('_', ' ', $lead->source) }}</td>
                                    <td class="py-3 px-4 font-extrabold text-amber-400">{{ $currency }}{{ number_format($lead->estimated_value ?? 0) }}</td>
                                    <td class="py-3 px-4 font-mono text-slate-400">{{ $lead->created_at ? $lead->created_at->format('d M Y') : '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-500">No conversions recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- ==================== CONTENT SECTION: REPORTS ==================== -->
        @if($section === 'reports')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Leads Acquired</div>
                    <div class="text-2xl font-black text-white mt-1">{{ $totalLeads }}</div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Won / Paid Conversions</div>
                    <div class="text-2xl font-black text-emerald-400 mt-1">{{ $paidCount }}</div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Active Pipeline Leads</div>
                    <div class="text-2xl font-black text-indigo-400 mt-1">{{ $activePipeline }}</div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

