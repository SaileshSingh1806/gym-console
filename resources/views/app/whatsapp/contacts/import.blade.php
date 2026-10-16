<x-app-layout header="">
    <div class="space-y-6">
        <!-- Alerts -->
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
        <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-500 mb-1">
                    <a href="{{ route('app.whatsapp.contacts.index') }}" class="hover:text-emerald-600 transition-colors">&larr; Back to Contacts</a>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Bulk Import WhatsApp Contacts</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Upload CSV with Indian mobile numbers (+91), validate duplicates, and preview records before saving.</p>
            </div>
            <a href="{{ route('app.whatsapp.contacts.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition-colors">
                Cancel
            </a>
        </div>

        @if(!isset($preview))
            <!-- Step 1: Upload Form & Format Instructions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Upload Dropzone Form -->
                <div class="lg:col-span-2 p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <form action="{{ route('app.whatsapp.contacts.import.preview') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block font-bold text-sm text-slate-900 dark:text-white mb-2">Select CSV File</label>
                            <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-8 text-center hover:border-emerald-500 transition-colors bg-slate-50/50 dark:bg-slate-800/30">
                                <svg class="w-12 h-12 mx-auto text-emerald-500/80 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <input type="file" name="csv_file" id="csv_file" required accept=".csv,.txt" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                                <p class="text-[11px] text-slate-400 mt-2">Maximum file size: 5MB (.csv format only)</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 cursor-pointer">
                                <span>Preview & Validate Data &rarr;</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Guidelines Card -->
                <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">CSV Column Format</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Your CSV file should include the following header row:</p>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 font-mono text-[11px] text-slate-700 dark:text-slate-300 overflow-x-auto">
                        name, phone, city, tags, consent
                    </div>

                    <ul class="text-xs space-y-2 text-slate-600 dark:text-slate-400 list-disc pl-4 font-medium">
                        <li><strong class="text-slate-900 dark:text-white">name:</strong> Customer or lead full name</li>
                        <li><strong class="text-slate-900 dark:text-white">phone:</strong> 10-digit Indian mobile number (e.g. 9876543210 or +919876543210)</li>
                        <li><strong class="text-slate-900 dark:text-white">city:</strong> Optional (e.g. Ahmedabad)</li>
                        <li><strong class="text-slate-900 dark:text-white">tags:</strong> Comma-separated (e.g. "vip, morning")</li>
                        <li><strong class="text-slate-900 dark:text-white">consent:</strong> "opted_in" or "unknown"</li>
                    </ul>

                    <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-800 dark:text-amber-300">
                        <strong>Consent Policy:</strong> Imported contacts default to <em>unknown</em> consent unless verified evidence or explicit opt-in is marked.
                    </div>
                </div>
            </div>
        @else
            <!-- Step 2: Validation Preview Screen -->
            <div class="space-y-6">
                <!-- Summary Metrics -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-center">
                        <span class="text-[11px] font-bold text-slate-500 uppercase">Total Rows</span>
                        <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ $preview['total_parsed'] }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-center">
                        <span class="text-[11px] font-bold text-emerald-600 uppercase">Valid to Import</span>
                        <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ $preview['valid_count'] }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-center">
                        <span class="text-[11px] font-bold text-amber-600 uppercase">Duplicates (Skipped)</span>
                        <div class="text-2xl font-black text-amber-600 mt-0.5">{{ $preview['duplicate_count'] }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-center">
                        <span class="text-[11px] font-bold text-rose-600 uppercase">Invalid Phone Format</span>
                        <div class="text-2xl font-black text-rose-600 mt-0.5">{{ $preview['invalid_count'] }}</div>
                    </div>
                </div>

                <!-- Valid Contacts Preview Table -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Preview of Valid Records (First 15 Shown)</h3>
                        <span class="text-xs text-emerald-600 font-bold">{{ $preview['valid_count'] }} records will be saved</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                                    <th class="py-2 px-3">Name</th>
                                    <th class="py-2 px-3">Formatted Phone (+91)</th>
                                    <th class="py-2 px-3">City</th>
                                    <th class="py-2 px-3">Tags</th>
                                    <th class="py-2 px-3">Consent Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse(array_slice($preview['valid_rows'], 0, 15) as $r)
                                    <tr>
                                        <td class="py-2.5 px-3 font-bold text-slate-900 dark:text-white">{{ $r['name'] }}</td>
                                        <td class="py-2.5 px-3 font-mono font-semibold text-emerald-600">{{ $r['phone'] }}</td>
                                        <td class="py-2.5 px-3 text-slate-500">{{ $r['city'] ?: '—' }}</td>
                                        <td class="py-2.5 px-3">{{ implode(', ', $r['tags'] ?? []) ?: '—' }}</td>
                                        <td class="py-2.5 px-3 font-bold uppercase text-[10px]">
                                            <span class="px-2 py-0.5 rounded-full {{ $r['consent_status'] === 'opted_in' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-slate-500/10 text-slate-600' }}">
                                                {{ $r['consent_status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-slate-400">No valid records found in this file.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(!empty($preview['invalid_rows']))
                        <div class="mt-4 p-4 rounded-xl bg-rose-500/5 border border-rose-500/20 space-y-2">
                            <h4 class="text-xs font-bold text-rose-600 uppercase">Invalid Phone Numbers (Skipped Automatically)</h4>
                            <ul class="text-xs space-y-1 text-slate-600 dark:text-slate-400">
                                @foreach(array_slice($preview['invalid_rows'], 0, 5) as $inv)
                                    <li>Row {{ $inv['row'] }}: <strong>{{ $inv['name'] }}</strong> ({{ $inv['raw_phone'] }}) &mdash; {{ $inv['reason'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($preview['valid_count'] > 0)
                        <!-- Confirm Form -->
                        <form action="{{ route('app.whatsapp.contacts.import.confirm') }}" method="POST" class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                            @csrf
                            <div>
                                <label class="block font-bold text-xs text-slate-700 dark:text-slate-300 mb-1">Consent Evidence / Version Note</label>
                                <input type="text" name="consent_wording" value="CSV Bulk Import - Verified by Admin" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <a href="{{ route('app.whatsapp.contacts.import') }}" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs transition-colors">
                                    Upload Different File
                                </a>
                                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Confirm & Import {{ $preview['valid_count'] }} Contacts</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

