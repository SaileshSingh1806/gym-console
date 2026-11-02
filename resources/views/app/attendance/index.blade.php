<x-app-layout header="Gym Attendance & Live Access">
    <div x-data="{
        showScannerModal: false,
        scanCode: '',
        scanning: false,
        scanMessage: '',
        scanSuccess: false,
        html5QrCode: null,
        submitFastScan(action = 'check_in') {
            let code = this.scanCode.trim();
            if (!code) return;
            this.scanning = true;
            this.scanMessage = 'Processing scan...';

            fetch('{{ route('app.attendance.qr-scan') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ code: code, action: action })
            })
            .then(res => res.json())
            .then(data => {
                this.scanning = false;
                this.scanSuccess = data.success;
                this.scanMessage = data.message;
                if (data.success) {
                    this.scanCode = '';
                    setTimeout(() => { window.location.reload(); }, 1200);
                }
            })
            .catch(err => {
                this.scanning = false;
                this.scanSuccess = false;
                this.scanMessage = 'Scan request failed: ' + err.message;
            });
        },
        startCameraScanner() {
            this.showScannerModal = true;
            this.$nextTick(() => {
                if (typeof Html5Qrcode !== 'undefined') {
                    if (this.html5QrCode) {
                        this.html5QrCode.stop().catch(() => {});
                    }
                    this.html5QrCode = new Html5Qrcode('camera-reader');
                    this.html5QrCode.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (decodedText) => {
                            this.scanCode = decodedText;
                            this.stopCameraScanner();
                            this.submitFastScan('check_in');
                        },
                        (error) => {}
                    ).catch(err => {
                        this.scanMessage = 'Camera access error: ' + err;
                    });
                }
            });
        },
        stopCameraScanner() {
            if (this.html5QrCode) {
                this.html5QrCode.stop().then(() => {
                    this.html5QrCode.clear();
                }).catch(() => {});
            }
            this.showScannerModal = false;
        }
    }" class="space-y-6">

    <!-- Top Action Bar & Standee Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Live Gym Check-In & Attendance</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Members can scan gym entrance QR, or front desk can scan member pass</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('app.attendance.standee') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-md shadow-amber-500/20 flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>🖨️ Print Desk QR Standee</span>
            </a>

            <button type="button" @click="startCameraScanner()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>📷 Scan Member Pass</span>
            </button>
        </div>
    </div>

    <!-- Quick Scan Bar (Supports USB Barcode Scanner, Web Scan, and Enter Key) -->
    <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-slate-900 to-slate-950 border border-slate-800 text-white shadow-xl space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-bold text-slate-200 uppercase tracking-wider">Fast Barcode / QR / Phone Scan</span>
            </div>
            <span class="text-[11px] text-slate-400">USB Scanner &amp; Keyboard ready</span>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <input type="text" 
                       x-model="scanCode" 
                       @keydown.enter.prevent="submitFastScan('check_in')"
                       placeholder="Scan QR or enter Member ID / Phone (e.g. ARM184, 8980986524)..." 
                       class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-emerald-500 font-mono">
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="submitFastScan('check_in')" :disabled="scanning" class="px-5 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition-all flex items-center justify-center gap-1.5 shadow-md shadow-emerald-500/20 cursor-pointer whitespace-nowrap">
                    <span x-show="scanning" class="animate-spin text-xs">⏳</span>
                    <span x-show="!scanning">⚡ Instant Check-in</span>
                </button>
                <button type="button" @click="submitFastScan('check_out')" :disabled="scanning" class="px-4 py-3 rounded-xl bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white font-bold text-xs transition-all flex items-center justify-center gap-1.5 border border-slate-700 cursor-pointer whitespace-nowrap">
                    <span>👋 Check-out</span>
                </button>
            </div>
        </div>

        <template x-if="scanMessage">
            <div class="p-2.5 rounded-xl text-xs font-medium" :class="scanSuccess ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30'" x-text="scanMessage"></div>
        </template>
    </div>

    <!-- Today Attendance Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase">Total Present Today</span>
            <span class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white block mt-1">{{ $summary['total_present'] }}</span>
        </div>
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold uppercase">Currently Inside Gym</span>
            <span class="text-2xl sm:text-3xl font-bold text-emerald-600 dark:text-emerald-400 block mt-1">{{ $summary['currently_inside'] }}</span>
        </div>
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase">Completed & Left</span>
            <span class="text-2xl sm:text-3xl font-bold text-slate-700 dark:text-slate-300 block mt-1">{{ $summary['checked_out'] }}</span>
        </div>
    </div>

    <!-- Manual Check-in / Checkout Box -->
    <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 mb-6 shadow-sm">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">Manual Front Desk Check-in / Check-out</h3>
        <form action="{{ route('app.attendance.store') }}" method="POST" id="attendanceForm" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
            @csrf
            <input type="hidden" name="action" id="attendanceAction" value="check_in">
            <div class="flex-grow w-full">
                <select name="member_id" id="attendanceMemberSelect" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                    <option value="">-- Select Active Member --</option>
                    @foreach($members as $m)
                        @php $isInside = isset($currentlyInsideMemberIds) && $currentlyInsideMemberIds->contains($m->id); @endphp
                        <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->member_code }}) - {{ $m->phone }}{{ $isInside ? ' • [Currently Inside]' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" onclick="document.getElementById('attendanceAction').value='check_in'" name="action" value="check_in" class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Check In</span>
                </button>
                <button type="submit" onclick="document.getElementById('attendanceAction').value='check_out'" name="action" value="check_out" class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Check Out</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Live Attendance Log: Mobile Cards & Desktop Table -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="p-3.5 sm:p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-1 bg-slate-50 dark:bg-slate-950/40">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Attendance Log History</h3>
            <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ now()->format('l, F d, Y') }}</span>
        </div>

        <!-- Mobile Cards View (block md:hidden) -->
        <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
            @forelse($attendance as $att)
                <div class="p-3.5 space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-amber-600 dark:text-amber-400 text-xs shrink-0">
                                {{ substr($att->member->first_name ?? 'M', 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-slate-900 dark:text-white text-xs block truncate">{{ $att->member->full_name ?? 'Member' }}</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-mono">{{ $att->member->member_code ?? '' }}</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $att->check_out ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' }}">
                            {{ $att->check_out ? 'Completed' : 'Inside' }}
                        </span>
                    </div>

                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 text-xs space-y-1.5">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-500 dark:text-slate-400">Date:</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $att->date ? $att->date->format('M d, Y') : '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-500 dark:text-slate-400">Check-in:</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $att->check_in ? $att->check_in->format('h:i:s A') : '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-500 dark:text-slate-400">Check-out:</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $att->check_out ? $att->check_out->format('h:i:s A') : '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-500 dark:text-slate-400">Method / Device:</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800">
                                {{ $att->device->name ?? ucfirst($att->method) }}
                            </span>
                        </div>
                    </div>

                    @if(! $att->check_out)
                        <form action="{{ route('app.attendance.checkout', $att->id) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 dark:bg-red-500/10 dark:hover:bg-red-500/20 dark:text-red-400 dark:border-red-500/30 text-xs font-bold transition-all shadow-sm cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>Check Out Member</span>
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="p-6 text-center text-slate-500 text-xs">No attendance logs available.</div>
            @endforelse
        </div>

        <!-- Desktop Table View (hidden md:block) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[650px]">
                <thead class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Member</th>
                        <th class="py-3.5 px-4 font-semibold">Date</th>
                        <th class="py-3.5 px-4 font-semibold">Check-in Time</th>
                        <th class="py-3.5 px-4 font-semibold">Check-out Time</th>
                        <th class="py-3.5 px-4 font-semibold">Device / Method</th>
                        <th class="py-3.5 px-4 font-semibold">Status</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($attendance as $att)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 px-4 flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-amber-600 dark:text-amber-400 text-[10px]">
                                    {{ substr($att->member->first_name ?? 'M', 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $att->member->full_name ?? 'Member' }}</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ $att->member->member_code ?? '' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                                {{ $att->date ? $att->date->format('M d, Y') : '—' }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ $att->check_in ? $att->check_in->format('h:i:s A') : '—' }}
                            </td>
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                                {{ $att->check_out ? $att->check_out->format('h:i:s A') : '—' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    {{ $att->device->name ?? ucfirst($att->method) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $att->check_out ? 'bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-400' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' }}">
                                    {{ $att->check_out ? 'Completed' : 'Inside' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if(! $att->check_out)
                                    <form action="{{ route('app.attendance.checkout', $att->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 dark:bg-red-500/10 dark:hover:bg-red-500/20 dark:text-red-400 dark:border-red-500/30 text-[11px] font-bold transition-all shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Check Out
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                                        <svg class="w-3.5 h-3.5 text-emerald-500/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Checked Out
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500 dark:text-slate-400">No attendance logs available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendance->hasPages())
            <div class="p-3.5 sm:p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                {{ $attendance->links() }}
            </div>
        @endif
    </div>

    <!-- Camera QR Scanner Modal -->
    <div x-show="showScannerModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="stopCameraScanner()" class="w-full max-w-md rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 text-center space-y-4 relative text-white">
            <button type="button" @click="stopCameraScanner()" class="absolute top-4 right-4 text-slate-400 hover:text-white text-lg cursor-pointer">&times;</button>
            <h3 class="text-base font-bold text-white flex items-center justify-center gap-2">
                <span>📷</span>
                <span>Camera QR Scanner</span>
            </h3>
            <p class="text-xs text-slate-400">Point camera at the member's digital QR pass</p>
            
            <div id="camera-reader" class="w-full aspect-square max-w-[320px] mx-auto rounded-2xl overflow-hidden bg-black border border-slate-700"></div>

            <button type="button" @click="stopCameraScanner()" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer">
                Close Scanner
            </button>
        </div>
    </div>
    </div>

    <!-- html5-qrcode scanner script -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</x-app-layout>
