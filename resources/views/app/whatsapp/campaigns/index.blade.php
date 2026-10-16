<x-app-layout header="">
    <div class="space-y-6" x-data="{ createModal: false, testModal: false, selectedTemplateId: '{{ request('template_id', '') }}', targetType: 'all_opted_in', selectedCity: '', estimateCount: 0, estimateCost: '0.00', isEstimating: false }">
        <!-- Alerts -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 text-sm cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 text-sm cursor-pointer">✕</button>
            </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <span>Bulk WhatsApp Campaigns</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $stats['total'] }}</span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Create, preview, schedule, and launch bulk promotional broadcasts via official Meta Cloud API.</p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="createModal = true; triggerEstimate();" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>New Campaign</span>
                </button>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-center">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Campaigns</span>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ number_format($stats['total']) }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-center">
                <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Running Now</span>
                <div class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-0.5">{{ number_format($stats['running']) }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-center">
                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Completed</span>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ number_format($stats['completed']) }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-center">
                <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Total Broadcasts Dispatched</span>
                <div class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-0.5">{{ number_format($stats['total_sent']) }}</div>
            </div>
        </div>

        <!-- Campaigns Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Campaign Name</th>
                            <th class="py-3 px-4">Template</th>
                            <th class="py-3 px-4">Audience</th>
                            <th class="py-3 px-4">Recipients / Progress</th>
                            <th class="py-3 px-4">Cost (₹)</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($campaigns as $camp)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('app.whatsapp.campaigns.history', $camp->id) }}" class="font-bold text-slate-900 dark:text-white hover:text-emerald-600 transition-colors">
                                        {{ $camp->name }}
                                    </a>
                                    <div class="text-[11px] text-slate-400 mt-0.5">Created {{ $camp->created_at->format('d M Y, h:i A') }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold">
                                    {{ $camp->template?->name ?? 'N/A' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="capitalize">{{ str_replace('_', ' ', $camp->target_audience) }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center justify-between text-[11px] mb-1">
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $camp->sent_count }}/{{ $camp->total_recipients }} sent</span>
                                        <span class="text-slate-500">{{ $camp->total_recipients > 0 ? round(($camp->sent_count / $camp->total_recipients) * 100) : 0 }}%</span>
                                    </div>
                                    <div class="w-32 bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                        @php $pct = $camp->total_recipients > 0 ? ($camp->sent_count / $camp->total_recipients) * 100 : 0; @endphp
                                        <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-1">
                                        <span>Delivered: {{ $camp->delivered_count }}</span>
                                        <span>Read: {{ $camp->read_count }}</span>
                                        @if($camp->failed_count > 0)
                                            <span class="text-rose-500">Failed: {{ $camp->failed_count }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono">
                                    <div class="font-bold text-slate-900 dark:text-white">₹{{ number_format($camp->actual_cost, 2) }}</div>
                                    <div class="text-[10px] text-slate-400">Est: ₹{{ number_format($camp->estimated_cost, 2) }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                                        {{ $camp->status === 'completed' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}
                                        {{ $camp->status === 'running' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 animate-pulse' : '' }}
                                        {{ $camp->status === 'scheduled' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20' : '' }}
                                        {{ $camp->status === 'paused' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : '' }}
                                        {{ $camp->status === 'cancelled' ? 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20' : '' }}
                                        {{ $camp->status === 'failed' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : '' }}
                                    ">
                                        {{ $camp->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('app.whatsapp.campaigns.history', $camp->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition-colors" title="View Full Report">
                                            Report &rarr;
                                        </a>

                                        @if($camp->status === 'running')
                                            <form action="{{ route('app.whatsapp.campaigns.pause', $camp->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/40" title="Pause Campaign">
                                                    ⏸
                                                </button>
                                            </form>
                                        @elseif($camp->status === 'paused')
                                            <form action="{{ route('app.whatsapp.campaigns.resume', $camp->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-950/40" title="Resume Campaign">
                                                    ▶
                                                </button>
                                            </form>
                                        @endif

                                        @if(in_array($camp->status, ['draft', 'scheduled', 'running', 'paused']))
                                            <form action="{{ route('app.whatsapp.campaigns.cancel', $camp->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this campaign?')">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40" title="Cancel Campaign">
                                                    ✕
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    <p class="text-xs">No WhatsApp broadcast campaigns created yet.</p>
                                    <button type="button" @click="createModal = true" class="mt-3 px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs cursor-pointer">+ Launch First Campaign</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($campaigns->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $campaigns->links() }}
                </div>
            @endif
        </div>

        <!-- Create Campaign Modal -->
        <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4 backdrop-blur-xs" x-cloak>
            <div @click.away="createModal = false" class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Create WhatsApp Campaign</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Strictly broadcasts to documented opted-in recipients.</p>
                    </div>
                    <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
                </div>

                <form action="{{ route('app.whatsapp.campaigns.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Campaign Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Diwali Yearly Membership Offer 2026" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Select Meta Approved Template *</label>
                        <select name="template_id" x-model="selectedTemplateId" @change="triggerEstimate()" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white font-mono font-semibold">
                            <option value="">-- Choose Approved Template --</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->category }})</option>
                            @endforeach
                        </select>
                        @if($templates->isEmpty())
                            <span class="text-[10px] text-amber-500 block mt-1">No approved templates found. Please sync or create templates first.</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Target Audience</label>
                            <select name="target_type" x-model="targetType" @change="triggerEstimate()" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                                <option value="all_opted_in">All Opted-In Contacts</option>
                                <option value="member">Gym Members Only</option>
                                <option value="lead">CRM Leads Only</option>
                                <option value="external">External Leads Only</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">City Filter (Optional)</label>
                            <select name="city" x-model="selectedCity" @change="triggerEstimate()" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                                <option value="">All Locations</option>
                                @foreach($cities as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Live Audience and Meta Cost Estimator Box -->
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase">Estimated Reach</div>
                            <div class="text-lg font-black text-slate-900 dark:text-white" x-text="estimateCount + ' Contacts'">0 Contacts</div>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] font-bold text-slate-500 uppercase">Estimated Meta Charge</div>
                            <div class="text-lg font-black text-emerald-600 dark:text-emerald-400" x-text="'₹' + estimateCost">₹0.00</div>
                        </div>
                    </div>

                    <!-- Schedule vs Now -->
                    <div x-data="{ scheduleType: 'now' }" class="space-y-3">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Dispatch Timing</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer" :class="scheduleType === 'now' ? 'bg-emerald-500/10 border-emerald-500' : ''">
                                <input type="radio" name="schedule_type" value="now" x-model="scheduleType" class="text-emerald-600">
                                <span class="font-bold">Launch Immediately</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer" :class="scheduleType === 'schedule' ? 'bg-emerald-500/10 border-emerald-500' : ''">
                                <input type="radio" name="schedule_type" value="schedule" x-model="scheduleType" class="text-emerald-600">
                                <span class="font-bold">Schedule for Later</span>
                            </label>
                        </div>

                        <div x-show="scheduleType === 'schedule'" class="pt-1" x-cloak>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Scheduled Date & Time *</label>
                            <input type="datetime-local" name="scheduled_at" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none dark:text-white">
                        </div>
                    </div>

                    <!-- Test Message Action -->
                    <div class="p-3 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-500/20 flex items-center justify-between">
                        <span class="text-[11px] text-slate-600 dark:text-slate-400 font-medium">Verify template rendering on your mobile before broadcasting.</span>
                        <button type="button" @click="testModal = true" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 shrink-0">Send Test</button>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold cursor-pointer">Submit & Launch Campaign</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Live Test Message Modal -->
        <div x-show="testModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4 backdrop-blur-xs" x-cloak>
            <div @click.away="testModal = false" class="bg-white dark:bg-slate-900 rounded-2xl max-w-sm w-full p-5 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">Send Live Test WhatsApp Message</h3>
                    <button type="button" @click="testModal = false" class="text-slate-400 hover:text-slate-600 text-base cursor-pointer">✕</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Test Phone Number (Indian 10-digit)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 font-bold text-slate-400 font-mono">+91</span>
                            <input type="text" id="test_phone_number" placeholder="9876543210" class="w-full pl-12 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none font-mono dark:text-white">
                        </div>
                    </div>

                    <button type="button" onclick="dispatchLiveTestMessage()" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition-colors cursor-pointer">
                        Dispatch Live Test
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script for Live Audience Estimator & Test Dispatch -->
    <script>
        function triggerEstimate() {
            const templateId = document.querySelector('[name="template_id"]')?.value;
            const targetType = document.querySelector('[name="target_type"]')?.value || 'all_opted_in';
            const city = document.querySelector('[name="city"]')?.value || '';

            if (!templateId) return;

            fetch("{{ route('app.whatsapp.campaigns.estimate') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    template_id: templateId,
                    target_type: targetType,
                    city: city
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.count !== undefined) {
                    const alpineData = Alpine.$data(document.querySelector('[x-data]'));
                    if (alpineData) {
                        alpineData.estimateCount = data.count;
                        alpineData.estimateCost = Number(data.estimated_cost).toFixed(2);
                    }
                }
            })
            .catch(err => console.error("Estimate error:", err));
        }

        function dispatchLiveTestMessage() {
            const phone = document.getElementById('test_phone_number')?.value;
            const templateId = document.querySelector('[name="template_id"]')?.value;

            if (!phone || !templateId) {
                alert('Please select a template and enter a test mobile number.');
                return;
            }

            fetch("{{ route('app.whatsapp.campaigns.test-message') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    phone: phone,
                    template_id: templateId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Test WhatsApp Message dispatched successfully to ' + phone + '!\nWAMID: ' + (data.wamid || 'Sent'));
                } else {
                    alert('❌ Dispatch Failed: ' + (data.message || data.error_message || 'API Error'));
                }
            })
            .catch(err => alert('Request failed: ' + err.message));
        }
    </script>
</x-app-layout>

