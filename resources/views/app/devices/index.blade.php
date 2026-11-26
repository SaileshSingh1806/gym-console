<x-app-layout header="Biometric Devices & IoT Access Control">
    <div class="space-y-8" x-data="{ showModal: false }">
        <!-- Devices Grid Header -->
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
            <div>
                <h3 class="text-sm font-semibold text-fg dark:text-white">Connected Biometric Terminals, Turnstiles &amp; Readers</h3>
                <p class="text-xs text-fg-muted dark:text-fg-muted">Universal integration for eSSL, Hikvision, Realtime, ZKTeco, Mantra, facial terminals &amp; turnstile gates</p>
            </div>
            <button @click="showModal = true" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-xs flex items-center gap-2 transition">
                + Register Hardware Device
            </button>
        </div>

        <!-- Devices List -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($devices as $device)
                <div class="p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center font-semibold text-sm">
                                    📹
                                </div>
                                <div>
                                    <h4 class="font-semibold text-fg dark:text-white text-sm">{{ $device->name }}</h4>
                                    <span class="text-xs text-fg-muted dark:text-fg-muted">{{ $device->model ?? 'Biometric Terminal' }} • {{ $device->branch->name ?? 'Main Branch' }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $device->status === 'ONLINE' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' }}">
                                ● {{ $device->status }}
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-raised dark:bg-slate-950/80 border border-line dark:border-line text-xs space-y-1.5 mb-4 text-fg-muted dark:text-fg-muted">
                            <div class="flex justify-between">
                                <span class="text-fg-muted dark:text-fg-muted">IP Address:</span>
                                <span class="font-mono text-fg dark:text-fg">{{ $device->ip_address ?? 'Local Webhook Mode' }}:{{ $device->port }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-fg-muted dark:text-fg-muted">Direction:</span>
                                <span class="font-semibold text-amber-600 dark:text-amber-400">{{ $device->direction }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-fg-muted dark:text-fg-muted">Device Secret Token:</span>
                                <span class="font-mono text-xs text-fg-muted dark:text-fg-muted truncate max-w-[180px]">{{ $device->device_secret ?? 'Auto-generated' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2 border-t border-line dark:border-line">
                        <form action="{{ route('app.devices.test', $device->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg hover:text-fg dark:hover:text-white border border-line dark:border-line text-xs font-semibold transition">
                                Ping Health Check
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-2 p-8 text-center bg-surface dark:bg-surface border border-line dark:border-line rounded-xl text-fg-muted dark:text-fg-muted text-xs">
                    No biometric devices configured. Click "+ Register Hardware Device" to connect eSSL, Hikvision, Realtime, or ZKTeco machines.
                </div>
            @endforelse
        </div>

        <!-- Recent Access Logs Table -->
        <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
            <div class="p-4 border-b border-line dark:border-line bg-raised dark:bg-slate-950/40 flex justify-between items-center">
                <h3 class="text-xs font-semibold text-fg dark:text-white">Real-Time Access Control Logs</h3>
                <span class="text-xs text-fg-muted dark:text-fg-muted">Live hardware telemetry</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[600px]">
                    <thead class="bg-raised dark:bg-slate-950/60 border-b border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs">
                        <tr>
                            <th class="py-3 px-4 font-semibold">Event Time</th>
                            <th class="py-3 px-4 font-semibold">Member</th>
                            <th class="py-3 px-4 font-semibold">Device</th>
                            <th class="py-3 px-4 font-semibold">Event Type</th>
                            <th class="py-3 px-4 font-semibold">Access Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                        @forelse($logs as $log)
                            <tr class="hover:bg-raised dark:hover:bg-raised transition">
                                <td class="py-3 px-4 text-fg-muted dark:text-fg-muted font-mono text-xs">
                                    {{ $log->event_time->format('h:i:s A - M d') }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-fg dark:text-white">
                                    {{ $log->member->full_name ?? 'Unknown / Guest' }}
                                </td>
                                <td class="py-3 px-4 text-fg-muted dark:text-fg-muted">
                                    {{ $log->device->name ?? 'Scanner' }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-amber-600 dark:text-amber-400 text-xs">
                                    {{ $log->event_type }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $log->access_status === 'GRANTED' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' }}">
                                        {{ $log->access_status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-fg-muted dark:text-fg-muted">No hardware access logs recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Device Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm p-3 sm:p-4 flex items-center justify-center">
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl max-w-md w-full max-h-[90vh] flex flex-col text-fg dark:text-fg overflow-hidden" @click.away="showModal = false">
                <div class="p-3.5 sm:p-4 border-b border-line dark:border-line flex items-center justify-between shrink-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-fg dark:text-white flex items-center gap-1.5">
                        <span class="p-1 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </span>
                        Register IoT Access Hardware
                    </h3>
                    <button @click="showModal = false" type="button" class="text-fg-muted hover:text-fg-muted dark:hover:text-white p-1 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.devices.store') }}" method="POST" class="p-4 sm:p-5 space-y-3.5 overflow-y-auto flex-1">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Device Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Front Gate Facial Scanner" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Device Type</label>
                        <select name="type" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                            <option value="essl_desktop">eSSL Biometric (Desktop Middleware / LAN)</option>
                            <option value="hikvision_facial">Hikvision Facial Terminal (ISAPI / MinMoe)</option>
                            <option value="zkteco_biometric">ZKTeco / Realtime Biometric Push (ADMS)</option>
                            <option value="hikvision_turnstile">Turnstile Barrier Gate</option>
                            <option value="rfid_reader">RFID Card Scanner</option>
                            <option value="qr_scanner">Dynamic QR Code Scanner</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">IP Address</label>
                            <input type="text" name="ip_address" placeholder="192.168.1.100" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Port</label>
                            <input type="number" name="port" value="80" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Device Username</label>
                            <input type="text" name="username" value="admin" placeholder="admin" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs font-mono focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Device Password</label>
                            <input type="password" name="password" placeholder="Hikvision device password" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs font-mono focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Direction</label>
                            <select name="direction" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                <option value="in">Entry Only</option>
                                <option value="out">Exit Only</option>
                                <option value="both">Both (Bi-directional)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Branch</label>
                            <select name="branch_id" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-line dark:border-line shrink-0">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted border border-line dark:border-line text-xs font-semibold transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-xs transition">Register Device</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
