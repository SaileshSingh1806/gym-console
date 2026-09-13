<x-app-layout header="Gym Attendance & Live Access">
    <!-- Today Attendance Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Present Today</span>
            <span class="text-3xl font-bold text-white block mt-1">{{ $summary['total_present'] }}</span>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-xs text-emerald-400 font-semibold uppercase">Currently Inside Gym</span>
            <span class="text-3xl font-bold text-emerald-400 block mt-1">{{ $summary['currently_inside'] }}</span>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-xs text-slate-400 font-semibold uppercase">Completed & Left</span>
            <span class="text-3xl font-bold text-slate-300 block mt-1">{{ $summary['checked_out'] }}</span>
        </div>
    </div>

    <!-- Manual Check-in / Checkout Box -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 mb-8">
        <h3 class="text-sm font-bold text-white mb-3">Manual Front Desk Check-in / Check-out</h3>
        <form action="{{ route('app.attendance.store') }}" method="POST" id="attendanceForm" class="flex flex-col sm:flex-row items-center gap-4">
            @csrf
            <input type="hidden" name="action" id="attendanceAction" value="check_in">
            <div class="flex-grow w-full">
                <select name="member_id" id="attendanceMemberSelect" required class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                    <option value="">-- Select Active Member --</option>
                    @foreach($members as $m)
                        @php $isInside = isset($currentlyInsideMemberIds) && $currentlyInsideMemberIds->contains($m->id); @endphp
                        <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->member_code }}) - {{ $m->phone }}{{ $isInside ? ' • [Currently Inside]' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" onclick="document.getElementById('attendanceAction').value='check_in'" name="action" value="check_in" class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Check In
                </button>
                <button type="submit" onclick="document.getElementById('attendanceAction').value='check_out'" name="action" value="check_out" class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Check Out
                </button>
            </div>
        </form>
    </div>

    <!-- Live Attendance Log Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-950/40">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Attendance Log History</h3>
            <span class="text-xs text-slate-400">{{ now()->format('l, F d, Y') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
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
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($attendance as $att)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 px-4 flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center font-bold text-amber-400 text-[10px]">
                                    {{ substr($att->member->first_name ?? 'M', 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-bold text-white block">{{ $att->member->full_name ?? 'Member' }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $att->member->member_code ?? '' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-400">
                                {{ $att->date ? $att->date->format('M d, Y') : '—' }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-emerald-400">
                                {{ $att->check_in ? $att->check_in->format('h:i:s A') : '—' }}
                            </td>
                            <td class="py-3 px-4 text-slate-400">
                                {{ $att->check_out ? $att->check_out->format('h:i:s A') : '—' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-300 border border-slate-700">
                                    {{ $att->device->name ?? ucfirst($att->method) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $att->check_out ? 'bg-slate-500/10 text-slate-400' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' }}">
                                    {{ $att->check_out ? 'Completed' : 'Inside' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if(! $att->check_out)
                                    <form action="{{ route('app.attendance.checkout', $att->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-[11px] font-bold transition-all shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Check Out
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-500 font-medium">
                                        <svg class="w-3.5 h-3.5 text-emerald-500/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Checked Out
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">No attendance logs available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendance->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $attendance->links() }}
            </div>
        @endif
    </div>
</x-app-layout>

