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
        <form action="{{ route('app.attendance.store') }}" method="POST" class="flex flex-col sm:flex-row items-center gap-4">
            @csrf
            <div class="flex-grow w-full">
                <select name="member_id" required class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                    <option value="">-- Select Active Member --</option>
                    @foreach($members as $m)
                        <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->member_code }}) - {{ $m->phone }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" name="action" value="check_in" class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs">
                    ✓ Check In
                </button>
                <button type="submit" name="action" value="check_out" class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs">
                    ← Check Out
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($attendance as $att)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 px-4 flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center font-bold text-amber-400 text-[10px]">
                                    {{ substr($att->member->first_name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-bold text-white block">{{ $att->member->full_name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $att->member->member_code }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-400">
                                {{ $att->date->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-emerald-400">
                                {{ $att->check_in->format('h:i:s A') }}
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">No attendance logs available.</td>
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

