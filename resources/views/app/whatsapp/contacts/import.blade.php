<x-app-layout header="">
    <div class="space-y-6">
        <!-- Alerts -->
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-fg-muted hover:text-fg-muted text-sm cursor-pointer">✕</button>
            </div>
        @endif

        <!-- Header -->
        <div class="flex items-center justify-between bg-surface dark:bg-surface p-5 rounded-xl border border-line dark:border-line">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-fg-muted mb-1">
                    <a href="{{ route('app.whatsapp.contacts.index') }}" class="hover:text-emerald-600 transition-colors">&larr; Back to Contacts</a>
                </div>
                <h1 class="text-xl sm:text-2xl font-semibold text-fg dark:text-white tracking-tight">Bulk Import WhatsApp Contacts</h1>
                <p class="text-xs text-fg-muted dark:text-fg-muted mt-1 font-medium">Upload CSV with Indian mobile numbers (+91), validate duplicates, and preview records before saving.</p>
            </div>
            <a href="{{ route('app.whatsapp.contacts.index') }}" class="px-4 py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted font-semibold text-xs transition-colors">
                Cancel
            </a>
        </div>

        @if(!isset($preview))
            <!-- Step 1: Upload Form & Format Instructions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Upload Dropzone Form -->
                <div class="lg:col-span-2 p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line">
                    <form action="{{ route('app.whatsapp.contacts.import.preview') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block font-semibold text-sm text-fg dark:text-white mb-2">Select CSV File</label>
                            <div class="border-2 border-dashed border-line dark:border-line rounded-xl p-8 text-center hover:border-emerald-500 transition-colors bg-raised dark:bg-raised">
                                <svg class="w-12 h-12 mx-auto text-emerald-500/80 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <input type="file" name="csv_file" id="csv_file" required accept=".csv,.txt" class="block w-full text-xs text-fg-muted file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                                <p class="text-xs text-fg-muted mt-2">Maximum file size: 5MB (.csv format only)</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-line dark:border-line">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition-all flex items-center gap-2 cursor-pointer">
                                <span>Preview & Validate Data &rarr;</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Guidelines Card -->
                <div class="p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-4">
                    <h3 class="text-sm font-semibold text-fg dark:text-white">CSV Column Format</h3>
                    <p class="text-xs text-fg-muted dark:text-fg-muted">Your CSV file should include the following header row:</p>

                    <div class="p-3 rounded-xl bg-raised dark:bg-raised font-mono text-xs text-fg-muted dark:text-fg-muted overflow-x-auto">
                        name, phone, city, tags, consent
                    </div>

                    <ul class="text-xs space-y-2 text-fg-muted dark:text-fg-muted list-disc pl-4 font-medium">
                        <li><strong class="text-fg dark:text-white">name:</strong> Customer or lead full name</li>
                        <li><strong class="text-fg dark:text-white">phone:</strong> 10-digit Indian mobile number (e.g. 9876543210 or +919876543210)</li>
                        <li><strong class="text-fg dark:text-white">city:</strong> Optional (e.g. Ahmedabad)</li>
                        <li><strong class="text-fg dark:text-white">tags:</strong> Comma-separated (e.g. "vip, morning")</li>
                        <li><strong class="text-fg dark:text-white">consent:</strong> "opted_in" or "unknown"</li>
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
                    <div class="p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-center">
                        <span class="text-xs font-semibold text-fg-muted">Total Rows</span>
                        <div class="text-2xl font-semibold text-fg dark:text-white mt-0.5">{{ $preview['total_parsed'] }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-center">
                        <span class="text-xs font-semibold text-emerald-600">Valid to Import</span>
                        <div class="text-2xl font-semibold text-emerald-600 mt-0.5">{{ $preview['valid_count'] }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-center">
                        <span class="text-xs font-semibold text-amber-600">Duplicates (Skipped)</span>
                        <div class="text-2xl font-semibold text-amber-600 mt-0.5">{{ $preview['duplicate_count'] }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-center">
                        <span class="text-xs font-semibold text-rose-600">Invalid Phone Format</span>
                        <div class="text-2xl font-semibold text-rose-600 mt-0.5">{{ $preview['invalid_count'] }}</div>
                    </div>
                </div>

                <!-- Valid Contacts Preview Table -->
                <div class="bg-surface dark:bg-surface rounded-xl border border-line dark:border-line p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-fg dark:text-white">Preview of Valid Records (First 15 Shown)</h3>
                        <span class="text-xs text-emerald-600 font-semibold">{{ $preview['valid_count'] }} records will be saved</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-line dark:border-line text-fg-muted font-semibold text-xs">
                                    <th class="py-2 px-3">Name</th>
                                    <th class="py-2 px-3">Formatted Phone (+91)</th>
                                    <th class="py-2 px-3">City</th>
                                    <th class="py-2 px-3">Tags</th>
                                    <th class="py-2 px-3">Consent Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line dark:divide-line">
                                @forelse(array_slice($preview['valid_rows'], 0, 15) as $r)
                                    <tr>
                                        <td class="py-2.5 px-3 font-semibold text-fg dark:text-white">{{ $r['name'] }}</td>
                                        <td class="py-2.5 px-3 font-mono font-semibold text-emerald-600">{{ $r['phone'] }}</td>
                                        <td class="py-2.5 px-3 text-fg-muted">{{ $r['city'] ?: '—' }}</td>
                                        <td class="py-2.5 px-3">{{ implode(', ', $r['tags'] ?? []) ?: '—' }}</td>
                                        <td class="py-2.5 px-3 font-semibold text-xs">
                                            <span class="px-2 py-0.5 rounded-full {{ $r['consent_status'] === 'opted_in' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-slate-500/10 text-slate-600' }}">
                                                {{ $r['consent_status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-fg-muted">No valid records found in this file.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(!empty($preview['invalid_rows']))
                        <div class="mt-4 p-4 rounded-xl bg-rose-500/5 border border-rose-500/20 space-y-2">
                            <h4 class="text-xs font-semibold text-rose-600">Invalid Phone Numbers (Skipped Automatically)</h4>
                            <ul class="text-xs space-y-1 text-fg-muted dark:text-fg-muted">
                                @foreach(array_slice($preview['invalid_rows'], 0, 5) as $inv)
                                    <li>Row {{ $inv['row'] }}: <strong>{{ $inv['name'] }}</strong> ({{ $inv['raw_phone'] }}) &mdash; {{ $inv['reason'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($preview['valid_count'] > 0)
                        <!-- Confirm Form -->
                        <form action="{{ route('app.whatsapp.contacts.import.confirm') }}" method="POST" class="pt-4 border-t border-line dark:border-line space-y-3">
                            @csrf
                            <div>
                                <label class="block font-semibold text-xs text-fg-muted dark:text-fg-muted mb-1">Consent Evidence / Version Note</label>
                                <input type="text" name="consent_wording" value="CSV Bulk Import - Verified by Admin" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-raised border border-line dark:border-line text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <a href="{{ route('app.whatsapp.contacts.import') }}" class="px-4 py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 text-fg-muted dark:text-fg-muted font-semibold text-xs transition-colors">
                                    Upload Different File
                                </a>
                                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition-all flex items-center gap-2 cursor-pointer">
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

