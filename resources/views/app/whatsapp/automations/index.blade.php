<x-app-layout header="">
    <div class="space-y-6" x-data="{ configModal: false, activeAuto: {} }">
        <!-- Alerts -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-fg-muted hover:text-fg-muted text-sm cursor-pointer">✕</button>
            </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-surface dark:bg-surface p-5 rounded-xl border border-line dark:border-line">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-fg dark:text-white tracking-tight flex items-center gap-2.5">
                    <span>Automated WhatsApp Workflows</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">Cron Powered</span>
                </h1>
                <p class="text-xs text-fg-muted dark:text-fg-muted mt-1 font-medium">Trigger automatic WhatsApp notifications for gym registrations, expirations, receipts, and birthdays.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('app.whatsapp.templates.index') }}" class="px-4 py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 text-fg-muted dark:text-fg-muted font-semibold text-xs transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Manage Templates</span>
                </a>
            </div>
        </div>

        <!-- Automations Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($automations as $auto)
                <div class="bg-surface dark:bg-surface rounded-xl border border-line dark:border-line p-5 flex flex-col justify-between hover:border-emerald-500/40 transition-all">
                    <div>
                        <!-- Header with Toggle -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <h3 class="font-semibold text-sm text-fg dark:text-white">{{ $auto->name }}</h3>
                                <div class="text-xs text-fg-muted mt-0.5">
                                    Trigger: <code class="font-semibold text-fg-muted dark:text-fg-muted">{{ $auto->trigger_event }}</code>
                                </div>
                            </div>

                            <form action="{{ route('app.whatsapp.automations.toggle', $auto->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $auto->is_active ? 'bg-emerald-600' : 'bg-slate-200 dark:bg-slate-700' }}" role="switch" title="{{ $auto->is_active ? 'Click to deactivate' : 'Click to activate' }}">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out {{ $auto->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </form>
                        </div>

                        <!-- Config Details -->
                        <div class="p-3.5 rounded-xl bg-raised dark:bg-raised border border-line dark:border-line space-y-2 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-fg-muted">Category:</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $auto->message_category === 'MARKETING' ? 'bg-purple-500/10 text-purple-600' : 'bg-blue-500/10 text-blue-600' }}">
                                    {{ $auto->message_category }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-fg-muted">Paired Meta Template:</span>
                                <span class="font-mono font-semibold text-fg dark:text-white truncate max-w-[140px]">
                                    {{ $auto->template?->name ?? 'None Paired' }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-fg-muted">Timing / Offset:</span>
                                <span class="font-semibold text-fg-muted dark:text-fg-muted">
                                    {{ $auto->days_offset == 0 ? 'Day of event' : ($auto->days_offset < 0 ? abs($auto->days_offset).' days before' : $auto->days_offset.' days after') }} at {{ $auto->send_time }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center pt-1 border-t border-line dark:border-line text-xs">
                                <span class="text-fg-muted">Last Executed:</span>
                                <span class="text-fg-muted">{{ $auto->last_run_at ? $auto->last_run_at->diffForHumans() : 'Never' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 pt-3 border-t border-line dark:border-line flex items-center justify-between gap-2">
                        <button type="button" @click="activeAuto = {{ json_encode($auto) }}; configModal = true" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline cursor-pointer">
                            Configure Settings &rarr;
                        </button>

                        <form action="{{ route('app.whatsapp.automations.run-now', $auto->id) }}" method="POST" onsubmit="return confirm('Execute this automation immediately for all eligible pending entities?')">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-surface dark:bg-raised text-fg font-semibold text-xs hover:bg-raised transition-colors cursor-pointer" title="Run manual check now">
                                Run Now ⚡
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Recent Execution Logs Table -->
        <div class="bg-surface dark:bg-surface rounded-xl border border-line dark:border-line p-5 space-y-4">
            <h3 class="text-sm font-semibold text-fg dark:text-white">Recent Automation Execution Audit Log</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-line dark:border-line text-fg-muted font-semibold text-xs">
                            <th class="py-2.5 px-3">Automation</th>
                            <th class="py-2.5 px-3">Recipient</th>
                            <th class="py-2.5 px-3">Entity</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3">Skip / Failure Reason</th>
                            <th class="py-2.5 px-3 text-right">Executed At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-line">
                        @forelse($recentLogs as $log)
                            <tr>
                                <td class="py-2.5 px-3 font-semibold text-fg dark:text-white">{{ $log->automation?->name }}</td>
                                <td class="py-2.5 px-3 font-mono font-semibold">{{ $log->contact?->phone }}</td>
                                <td class="py-2.5 px-3 text-fg-muted">{{ $log->entity_type }} #{{ $log->entity_id }}</td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase
                                        {{ $log->status === 'sent' ? 'bg-emerald-500/10 text-emerald-600' : '' }}
                                        {{ $log->status === 'skipped' ? 'bg-amber-500/10 text-amber-600' : '' }}
                                        {{ $log->status === 'failed' ? 'bg-rose-500/10 text-rose-600' : '' }}
                                    ">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-fg-muted text-xs">{{ $log->skip_reason ?: ($log->error_message ?: '—') }}</td>
                                <td class="py-2.5 px-3 text-right text-fg-muted">{{ $log->executed_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-fg-muted">No automation logs recorded yet. Automations run automatically via scheduler.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Configure Automation Modal -->
        <div x-show="configModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4 backdrop-blur-xs" x-cloak>
            <div @click.away="configModal = false" class="bg-surface dark:bg-surface rounded-xl max-w-md w-full p-6 border border-line dark:border-line space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-line dark:border-line">
                    <h3 class="text-base font-semibold text-fg dark:text-white" x-text="'Configure: ' + activeAuto.name"></h3>
                    <button type="button" @click="configModal = false" class="text-fg-muted hover:text-fg-muted text-lg cursor-pointer">✕</button>
                </div>

                <form :action="'{{ url('/app/whatsapp/automations') }}/' + activeAuto.id" method="POST" class="space-y-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Paired Meta Approved Template</label>
                        <select name="template_id" x-model="activeAuto.template_id" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-raised border border-line dark:border-line outline-none dark:text-white font-mono font-semibold">
                            <option value="">-- No Template Paired --</option>
                            @foreach($templates as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->category }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Days Offset</label>
                            <input type="number" name="days_offset" :value="activeAuto.days_offset" placeholder="e.g. -3 or 1" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-raised border border-line dark:border-line outline-none dark:text-white">
                            <span class="text-xs text-fg-muted">-3 = 3 days before, +1 = 1 day after</span>
                        </div>
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Send Time (HH:mm)</label>
                            <input type="time" name="send_time" :value="activeAuto.send_time || '10:00'" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-raised border border-line dark:border-line outline-none dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Message Policy Category</label>
                        <select name="message_category" x-model="activeAuto.message_category" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-raised border border-line dark:border-line outline-none dark:text-white">
                            <option value="UTILITY">UTILITY (Transactional / Reminders / Receipts)</option>
                            <option value="MARKETING">MARKETING (Promotional / Renewal Offers - Requires Strict Opt-In)</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-line dark:border-line">
                        <button type="button" @click="configModal = false" class="px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted font-semibold">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

