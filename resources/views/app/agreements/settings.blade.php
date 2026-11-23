<x-app-layout header="Agreement & Legal Settings">
    <div x-data="agreementSettingsManager({{ json_encode($activeTemplate->terms_sections ?? []) }}, {{ $activeTemplate->open_terrace_enabled ? 'true' : 'false' }})" class="space-y-6">

        <!-- Breadcrumbs & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('app.agreements.index') }}" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Agreements
                    </a>
                    <span>/</span>
                    <span class="text-slate-800 dark:text-slate-200">Legal & Terms Configuration</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                    <span class="p-2 bg-indigo-600/10 text-indigo-600 dark:bg-indigo-400/10 dark:text-indigo-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    Agreement & Safety Policy Settings
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Configure gym membership agreements, terrace safety clauses, and publish legally immutable document versions.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('app.agreements.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition-colors">
                    Back to Agreements
                </a>
            </div>
        </div>

        <!-- Currently Active Version Card -->
        <div class="bg-gradient-to-r from-indigo-900 to-slate-900 text-white rounded-3xl p-6 shadow-xl border border-indigo-800/40 relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 mb-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        LIVE & ACTIVE
                    </div>
                    <h2 class="text-xl font-black">{{ $activeTemplate->title }} (Version {{ $activeTemplate->version }})</h2>
                    <p class="text-xs text-indigo-200/80 mt-1 max-w-xl">
                        Published on {{ $activeTemplate->created_at->format('d M Y, h:i A') }} {{ $activeTemplate->publisher ? 'by ' . $activeTemplate->publisher->name : '' }}.
                        Existing members who signed this agreement stay locked to this exact version for legal compliance.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <div class="text-xs text-indigo-200">Open Terrace Rules</div>
                        <div class="font-bold text-sm {{ $activeTemplate->open_terrace_enabled ? 'text-emerald-400' : 'text-slate-400' }}">
                            {{ $activeTemplate->open_terrace_enabled ? 'Included (Strict Access)' : 'Disabled' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form: Publish New Immutable Version -->
        <form action="{{ route('app.agreements.settings.save') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Facility & Open Terrace Configuration -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-5">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="p-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Open Terrace Workout Facility</h3>
                        <p class="text-xs text-slate-500">Configure elevated outdoor terrace access safety rules</p>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>Open Terrace Facility Available in Gym?</span>
                            <span class="text-[10px] uppercase font-black px-2 py-0.5 rounded-full" :class="terraceEnabled ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-400'" x-text="terraceEnabled ? 'Enabled' : 'Disabled'"></span>
                        </label>
                        <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                            When enabled, strict safety clauses (Clauses 27–30) and the terrace declaration paragraph are automatically incorporated into published agreements.
                            <br>
                            <span class="text-amber-600 dark:text-amber-400 font-semibold">Note: Enabling does NOT automatically grant terrace access to members. Access remains strictly restricted and requires explicit authorization from Gym Management.</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <input type="hidden" name="open_terrace_enabled" :value="terraceEnabled ? 1 : 0">
                        <button type="button" @click="terraceEnabled = !terraceEnabled"
                                class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                :class="terraceEnabled ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-700'">
                            <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                  :class="terraceEnabled ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                </div>

                <!-- Preview of Terrace Clauses when enabled -->
                <div x-show="terraceEnabled" class="p-4 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-800 space-y-2.5">
                    <div class="text-xs font-bold text-emerald-900 dark:text-emerald-200 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Terrace Safety Clauses (Clauses 27-30 Included in Agreement):
                    </div>
                    <ul class="text-[11px] text-emerald-800 dark:text-emerald-300 space-y-1.5 pl-4 list-disc">
                        <li><strong>27. Prior Authorization Required:</strong> Access is strictly restricted and permitted only with prior management authorization. Members are not entitled to automatic terrace access.</li>
                        <li><strong>28. Strict Prohibition:</strong> Unauthorized entry, use without permission, or during restricted hours is strictly prohibited and may result in immediate membership termination.</li>
                        <li><strong>29. Safety & Conduct:</strong> Strict adherence to safety instructions. Climbing or leaning on railings, dangerous activities, or misusing equipment is strictly prohibited.</li>
                        <li><strong>30. Revocation Right:</strong> Management reserves the right to restrict, suspend, or revoke terrace access at any time due to safety, weather, or operational needs.</li>
                    </ul>
                </div>
            </div>

            <!-- Section 2: Agreement Document Title & Sections -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-5">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="p-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Agreement Terms & Conditions Sections</h3>
                        <p class="text-xs text-slate-500">Organized into readable, expandable sections for quick member review</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Official Document Title</label>
                    <input type="text" name="title" value="{{ old('title', $activeTemplate->title) }}" required
                           class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-2.5 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-bold">
                </div>

                <!-- Accordion of Sections -->
                <div class="space-y-3">
                    <template x-for="(sec, sIdx) in sections" :key="sIdx">
                        <div class="border border-slate-200/80 dark:border-slate-800 rounded-2xl overflow-hidden bg-slate-50/50 dark:bg-slate-950/50">
                            <input type="hidden" :name="'terms_sections[' + sIdx + '][title]'" :value="sec.title">
                            <input type="hidden" :name="'terms_sections[' + sIdx + '][summary]'" :value="sec.summary">
                            
                            <div class="p-4 flex items-center justify-between cursor-pointer select-none" @click="sec.expanded = !sec.expanded">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 text-xs font-black flex items-center justify-center shrink-0" x-text="sIdx + 1"></span>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-white" x-text="sec.title"></div>
                                        <div class="text-[11px] text-slate-500" x-text="sec.summary"></div>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-slate-400 transition-transform" :class="sec.expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>

                            <div x-show="sec.expanded" class="p-4 pt-0 border-t border-slate-200/60 dark:border-slate-800 space-y-2">
                                <div class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Clauses:</div>
                                <div class="space-y-1.5 pl-2">
                                    <template x-for="(clause, cIdx) in (sec.clauses || [])" :key="cIdx">
                                        <div class="text-[11px] text-slate-600 dark:text-slate-400 p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800" x-text="clause">
                                            <input type="hidden" :name="'terms_sections[' + sIdx + '][clauses][' + cIdx + ']'" :value="clause">
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Publish Action Warning Banner -->
                <div class="p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800 flex items-start gap-3">
                    <svg class="w-5 h-5 text-indigo-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-xs text-indigo-900 dark:text-indigo-200">
                        <strong class="font-bold">Immutable Legal Versioning:</strong> When you publish, a new version (v{{ $activeTemplate->version + 1 }}.0) will be created. All newly enrolled members will sign this new version. Existing signed member documents remain permanently sealed to the exact version they signed.
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('app.agreements.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition-all hover:scale-[1.01]">
                        Publish New Agreement Version (v{{ $activeTemplate->version + 1 }}.0)
                    </button>
                </div>
            </div>
        </form>

        <!-- Section 3: Version History Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Agreement Version History</h3>
                        <p class="text-xs text-slate-500">Immutable ledger of published agreement templates</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider font-extrabold text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Version</th>
                            <th class="py-3 px-3">Title</th>
                            <th class="py-3 px-3">Open Terrace Rules</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3">Published At</th>
                            <th class="py-3 px-4">Published By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($templateHistory as $tmpl)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                            <td class="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                v{{ $tmpl->version }}.0
                            </td>
                            <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $tmpl->title }}
                            </td>
                            <td class="py-3 px-3">
                                @if($tmpl->open_terrace_enabled)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded">
                                        Enabled
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[10px]">Disabled</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($tmpl->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500">
                                        Archived
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-500">
                                {{ $tmpl->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                {{ $tmpl->publisher?->name ?? 'System' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        function agreementSettingsManager(sectionsData, initialTerraceEnabled) {
            return {
                terraceEnabled: initialTerraceEnabled,
                sections: (sectionsData || []).map(sec => ({
                    ...sec,
                    expanded: false
                }))
            };
        }
    </script>
</x-app-layout>
