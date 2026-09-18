<x-app-layout header="Hikvision Biometric Devices & IoT Access Control">
    <div class="space-y-8" x-data="{ showModal: false }">
        <!-- Devices Grid Header -->
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Connected Biometric Terminals & Turnstiles</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Hardware abstraction layer for facial recognition, RFID readers, and barrier gates</p>
            </div>
            <button @click="showModal = true" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-sm transition">
                + Register Hardware Device
            </button>
        </div>

        <!-- Devices List -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($devices as $device)
                <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                    <div>
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center font-bold text-sm">
                                    📹
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-white text-sm">{{ $device->name }}</h4>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ $device->model ?? 'Hikvision Terminal' }} • {{ $device->branch->name ?? 'Main Branch' }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $device->status === 'ONLINE' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' }}">
                                ● {{ $device->status }}
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 text-xs space-y-1.5 mb-4 text-slate-700 dark:text-slate-300">
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">IP Address:</span>
                                <span class="font-mono text-slate-800 dark:text-slate-200">{{ $device->ip_address ?? 'Local Webhook Mode' }}:{{ $device->port }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Direction:</span>
                                <span class="uppercase font-semibold text-amber-600 dark:text-amber-400">{{ $device->direction }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Device Secret Token:</span>
                                <span class="font-mono text-[10px] text-slate-500 dark:text-slate-400 truncate max-w-[180px]">{{ $device->device_secret ?? 'Auto-generated' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                        <form action="{{ route('app.devices.test', $device->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700 text-xs font-semibold transition">
                                Ping Health Check
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-2 p-8 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-500 dark:text-slate-400 text-xs shadow-sm">
                    No biometric devices configured. Click "+ Register Hardware Device" to connect Hikvision facial terminals.
                </div>
            @endforelse
        </div>

        <!-- Recent Access Logs Table -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 flex justify-between items-center">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Real-Time Access Control Logs</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Live hardware telemetry</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4 font-semibold">Event Time</th>
                            <th class="py-3 px-4 font-semibold">Member</th>
                            <th class="py-3 px-4 font-semibold">Device</th>
                            <th class="py-3 px-4 font-semibold">Event Type</th>
                            <th class="py-3 px-4 font-semibold">Access Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="py-3 px-4 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                    {{ $log->event_time->format('h:i:s A - M d') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                    {{ $log->member->full_name ?? 'Unknown / Guest' }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                                    {{ $log->device->name ?? 'Scanner' }}
                                </td>
                                <td class="py-3 px-4 uppercase font-semibold text-amber-600 dark:text-amber-400 text-[11px]">
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
                                <td colspan="5" class="py-8 text-center text-slate-400 dark:text-slate-500">No hardware access logs recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Device Modal -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl text-slate-900 dark:text-slate-200" @click.away="showModal = false">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Register IoT Access Hardware</h3>
                <form action="{{ route('app.devices.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Device Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Front Gate Facial Scanner" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Device Type</label>
                        <select name="type" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                            <option value="hikvision_facial">Hikvision Facial Terminal (ISAPI)</option>
                            <option value="hikvision_turnstile">Hikvision Turnstile Barrier Gate</option>
                            <option value="rfid_reader">RFID Card Scanner</option>
                            <option value="qr_scanner">Dynamic QR Code Scanner</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">IP Address</label>
                            <input type="text" name="ip_address" placeholder="192.168.1.100" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Port</label>
                            <input type="number" name="port" value="80" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Direction</label>
                            <select name="direction" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                <option value="in">Entry Only</option>
                                <option value="out">Exit Only</option>
                                <option value="both">Both (Bi-directional)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Branch</label>
                            <select name="branch_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-semibold transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition">Register Device</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
