<x-app-layout header="Agreement & Legal Settings">

    @push('styles')
    <style>
    .agreement-builder { min-width:0; }
    .agreement-builder .ab-panel { background:rgb(var(--c-surface));border:1px solid rgb(var(--c-line));border-radius:12px;min-width:0; }
    .agreement-builder .ab-layout { display:grid;grid-template-columns:minmax(180px, .75fr) minmax(0,2fr);gap:12px;align-items:start; }
    .agreement-builder .ab-nav { padding:8px; }
    .agreement-builder .ab-row { width:100%;display:flex;align-items:center;gap:8px;padding:10px;text-align:left;border-radius:8px;color:rgb(var(--c-fg-muted)); }
    .agreement-builder .ab-row[aria-pressed="true"] { background:rgb(var(--c-accent)/.10);color:rgb(var(--c-accent-fg)); }
    .agreement-builder .ab-row:hover { background:rgb(var(--c-raised)); }
    .agreement-builder .ab-input { width:100%;border:1px solid rgb(var(--c-line-strong));border-radius:8px;background:rgb(var(--c-canvas));color:rgb(var(--c-fg));padding:9px 11px;font-size:14px; }
    .agreement-builder .ab-input:focus { outline:2px solid rgb(var(--c-accent)/.4);border-color:rgb(var(--c-accent)); }
    .agreement-builder .ab-label { display:block;color:rgb(var(--c-fg-muted));font-size:12px;font-weight:500;margin-bottom:5px; }
    .agreement-builder .ab-actions { display:flex;flex-wrap:wrap;gap:8px;align-items:center; }
    .agreement-builder .ab-clause { padding:12px;border:1px solid rgb(var(--c-line));border-radius:8px; }
    .agreement-builder .ab-preview { background:#fff;color:#17181b;padding:24px;border-radius:8px;overflow-wrap:anywhere; }
    .agreement-builder .ab-preview p { white-space:pre-wrap; }
    @media(max-width:760px){.agreement-builder .ab-layout{grid-template-columns:minmax(0,1fr);}.agreement-builder .ab-nav{max-height:250px;overflow:auto;}}
    </style>
    @endpush
    @php
        $initialSections = old('terms_sections', $builderDraft['terms_sections'] ?? $activeTemplate->terms_sections ?? []);
        $starterSections = \App\Models\TenantAgreementTemplate::getDefaultTermsSections(false);
        $terraceSection = collect(\App\Models\TenantAgreementTemplate::getDefaultTermsSections(true))->firstWhere('key', 'open_terrace_rules');
    @endphp
    <div x-data="agreementBuilder({{ \Illuminate\Support\Js::from($initialSections) }}, {{ \Illuminate\Support\Js::from($starterSections) }}, {{ \Illuminate\Support\Js::from($terraceSection) }}, {{ \Illuminate\Support\Js::from(old('title', $builderDraft['title'] ?? $activeTemplate->title)) }}, {{ \Illuminate\Support\Js::from((bool) old('open_terrace_enabled', $builderDraft['open_terrace_enabled'] ?? $activeTemplate->open_terrace_enabled)) }})" class="agreement-builder space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><a href="{{ route('app.agreements.index') }}" class="ds-caption">Agreements / Settings</a><h1 class="ds-title mt-1">Agreement builder</h1><p class="ds-caption mt-1">Create and publish your gym's membership policies.</p></div>
            <a href="{{ route('app.agreements.index') }}" class="ds-btn ds-btn-secondary">Back to agreements</a>
        </div>
        @if($errors->any())
            <div class="ab-panel p-4 text-danger" role="alert"><strong>Please review your agreement.</strong><ul class="list-disc pl-5 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <div class="ab-panel p-4 flex flex-wrap items-center justify-between gap-3">
            <div><span class="ds-badge ds-badge-success">Active · v{{ $activeTemplate->version }}.0</span><h2 class="ds-heading mt-2">{{ $activeTemplate->title }}</h2><p class="ds-caption mt-1">Published {{ $activeTemplate->created_at->format('d M Y, h:i A') }}. Previously signed documents remain unchanged.</p></div>
            @if($builderDraft)<span class="ds-badge ds-badge-neutral">Session draft loaded</span>@endif
        </div>
        <form action="{{ route('app.agreements.settings.save') }}" method="POST" class="space-y-4" @submit="if (!valid()) { $event.preventDefault(); error = 'Enter a title and complete every section and clause.'; } else if ($event.submitter?.value === 'publish' && !window.confirm('Publish a new agreement version? Existing signed agreements will remain unchanged.')) { $event.preventDefault(); }">
            @csrf
            <input type="hidden" name="builder_payload" value="1">
            <input type="hidden" name="open_terrace_enabled" :value="terraceEnabled ? 1 : 0">
            <template x-for="(sec, i) in sections" :key="sec.uid">
                <div>
                    <input type="hidden" :name="'terms_sections['+i+'][title]'" :value="sec.title">
                    <input type="hidden" :name="'terms_sections['+i+'][summary]'" :value="sec.summary">
                    <template x-for="(clause,j) in sec.clauses" :key="clause.uid">
                        <input type="hidden" :name="'terms_sections['+i+'][clauses]['+j+']'" :value="clause.text">
                    </template>
                </div>
            </template>
            <div class="ab-panel p-4 space-y-3">
                <div class="ab-actions justify-between">
                    <h2 class="ds-heading">Document editor</h2>
                    <div class="ab-actions">
                        <button type="button" class="ds-btn ds-btn-secondary" @click="if(confirm('Replace unsaved sections with the default template?')) resetSections(starter)">Default template</button>
                        <button type="button" class="ds-btn ds-btn-secondary" @click="if(confirm('Replace unsaved sections with a blank agreement?')) resetSections([{title:'New section',summary:'',clauses:['']}])">Start from scratch</button>
                        <button type="button" class="ds-btn ds-btn-secondary" @click="preview = !preview" x-text="preview ? 'Edit document' : 'Preview'"></button>
                    </div>
                </div>
                <label class="ab-label" for="agreement-title">Document title</label>
                <input id="agreement-title" name="title" class="ab-input" maxlength="150" required x-model="title">
            </div>
            <div class="ab-layout" x-show="!preview">
                <aside class="ab-panel ab-nav space-y-2">
                    <div class="flex justify-between items-center px-2 py-1"><h2 class="ds-heading">Sections</h2><span class="ds-caption" x-text="sections.length"></span></div>
                    <template x-for="(sec,i) in sections" :key="sec.uid">
                        <button type="button" class="ab-row" :aria-pressed="selected === i" @click="selected = i"><span x-text="i+1"></span><span class="flex-1 min-w-0"><span class="block font-medium break-words" x-text="sec.title || 'Untitled section'"></span><span class="ds-caption" x-text="sec.clauses.length+' clauses'"></span></span></button>
                    </template>
                    <button type="button" class="ds-btn ds-btn-secondary w-full" :disabled="sections.length >= 29" @click="addSection()">+ Add section</button>
                </aside>
                <section class="ab-panel p-4 space-y-4">
                    <template x-if="sections[selected]">
                        <div class="space-y-4">
                            <div class="ab-actions justify-between"><h2 class="ds-heading" x-text="'Section '+(selected+1)"></h2><div class="ab-actions"><button type="button" class="ds-btn ds-btn-secondary" :disabled="selected===0" @click="moveSection(-1)">Move up</button><button type="button" class="ds-btn ds-btn-secondary" :disabled="selected===sections.length-1" @click="moveSection(1)">Move down</button></div></div>
                            <label class="ab-label">Section heading<input class="ab-input mt-1" maxlength="150" x-model="sections[selected].title"></label>
                            <label class="ab-label">Section summary (optional)<textarea class="ab-input mt-1" rows="2" maxlength="1000" x-model="sections[selected].summary"></textarea></label>
                            <h3 class="ds-heading">Terms &amp; conditions</h3>
                            <template x-for="(clause,j) in sections[selected].clauses" :key="clause.uid">
                                <div class="ab-clause space-y-2">
                                    <div class="ab-actions justify-between"><span class="ds-caption" x-text="'Clause '+(j+1)"></span><div class="ab-actions"><button type="button" class="ds-btn ds-btn-secondary" :disabled="j===0" @click="moveClause(j,-1)">↑</button><button type="button" class="ds-btn ds-btn-secondary" :disabled="j===sections[selected].clauses.length-1" @click="moveClause(j,1)">↓</button><button type="button" class="text-danger text-xs" :disabled="sections[selected].clauses.length<=1" @click="sections[selected].clauses.splice(j,1)">Remove</button></div></div>
                                    <textarea class="ab-input" rows="3" maxlength="3000" :aria-label="'Clause '+(j+1)" x-model="clause.text"></textarea>
                                </div>
                            </template>
                            <button type="button" class="ds-btn ds-btn-secondary w-full" :disabled="sections[selected].clauses.length>=30" @click="addClause()">+ Add clause</button>
                            <button type="button" class="text-danger text-xs" :disabled="sections.length<=1" @click="removeSection()">Remove section</button>
                        </div>
                    </template>
                </section>
            </div>
            <div class="ab-panel p-4 space-y-3">
                <h2 class="ds-heading">Facility-specific policies</h2>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="terraceEnabled"> Include open terrace safety rules</label>
                <p class="ds-caption">Access requires management authorization. System-managed terrace clauses are appended automatically.</p>
                <div x-show="terraceEnabled" class="space-y-2"><template x-for="clause in terrace.clauses" :key="clause"><p class="text-xs text-fg-muted" x-text="clause"></p></template></div>
            </div>
            <div x-show="preview" x-cloak class="ab-panel p-4">
                <div class="ab-preview space-y-4">
                    <h2 class="text-lg font-semibold text-center">{{ $tenant->name }}</h2><h3 class="text-base font-semibold text-center" x-text="title"></h3>
                    <template x-for="(sec,i) in sections" :key="sec.uid">
                        <div class="space-y-2"><h4 class="font-semibold" x-text="(i+1)+'. '+sec.title"></h4><p class="text-sm" x-text="sec.summary"></p><template x-for="clause in sec.clauses" :key="clause.uid"><p class="text-sm" x-text="clause.text"></p></template></div>
                    </template>
                    <div x-show="terraceEnabled" class="space-y-2"><h4 class="font-semibold" x-text="terrace.title"></h4><template x-for="clause in terrace.clauses" :key="clause"><p class="text-sm" x-text="clause"></p></template></div>
                    <p class="text-xs">Preview of terms only. The existing health declaration and signing steps remain separate.</p>
                </div>
            </div>
            <div class="ab-panel p-4 space-y-3">
                <p class="ds-caption">Publishing creates a new immutable version. Review your terms before publishing. Drafts are saved in your current login session only.</p>
                <p x-show="error" x-text="error" class="text-danger text-sm" role="alert"></p>
                <div class="ab-actions justify-end"><button type="submit" name="intent" value="draft" class="ds-btn ds-btn-secondary">Save draft</button><button type="submit" name="intent" value="publish" class="ds-btn ds-btn-primary">Publish version {{ $activeTemplate->version + 1 }}.0</button></div>
            </div>
        </form>
        <!-- Section 3: Version History Table -->
        <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-line dark:border-line">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-fg dark:text-white">Agreement Version History</h3>
                        <p class="text-xs text-fg-muted">Immutable ledger of published agreement templates</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-line dark:border-line">
                <table class="w-full text-left text-xs">
                    <thead class="bg-raised dark:bg-raised text-fg-muted font-semibold text-xs">
                        <tr>
                            <th class="py-3 px-4">Version</th>
                            <th class="py-3 px-3">Title</th>
                            <th class="py-3 px-3">Open Terrace Rules</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3">Published At</th>
                            <th class="py-3 px-4">Published By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-line">
                        @foreach($templateHistory as $tmpl)
                        <tr class="hover:bg-raised dark:hover:bg-raised">
                            <td class="py-3 px-4 font-mono font-semibold text-indigo-600 dark:text-indigo-400">
                                v{{ $tmpl->version }}.0
                            </td>
                            <td class="py-3 px-3 font-semibold text-fg dark:text-fg">
                                {{ $tmpl->title }}
                            </td>
                            <td class="py-3 px-3">
                                @if($tmpl->open_terrace_enabled)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded">
                                        Enabled
                                    </span>
                                @else
                                    <span class="text-fg-muted text-xs">Disabled</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($tmpl->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-raised dark:bg-raised text-fg-muted">
                                        Archived
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-fg-muted">
                                {{ $tmpl->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="py-3 px-4 text-fg-muted dark:text-fg-muted">
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
    function agreementBuilder(initial, starter, terrace, title, enabled) {
        let nextId = 0;
        const normalize = data => (Array.isArray(data) ? data : []).filter(s => s.key !== 'open_terrace_rules').map(s => ({
            uid: ++nextId, title: s.title || '', summary: s.summary || '',
            clauses: (Array.isArray(s.clauses) && s.clauses.length ? s.clauses : ['']).map(text => ({uid: ++nextId, text: String(text ?? '')}))
        }));
        return {
            title, starter, terrace, terraceEnabled: enabled, sections: normalize(initial), selected: 0, preview: false, error: '',
            init() { if (!this.sections.length) this.addSection(); },
            resetSections(data) { this.sections = normalize(data); this.selected = 0; this.error = ''; },
            addSection() { this.sections.push(...normalize([{title:'New section',summary:'',clauses:['']} ])); this.selected=this.sections.length-1; },
            removeSection() { if(this.sections.length>1 && confirm('Remove this section and its clauses?')) { this.sections.splice(this.selected,1);this.selected=Math.max(0,this.selected-1); } },
            moveSection(delta) { const i=this.selected, j=i+delta; if(j<0||j>=this.sections.length)return; const item=this.sections.splice(i,1)[0];this.sections.splice(j,0,item);this.selected=j; },
            addClause() { this.sections[this.selected].clauses.push({uid:++nextId,text:''}); },
            moveClause(i,delta) { const list=this.sections[this.selected].clauses,j=i+delta;if(j<0||j>=list.length)return;const item=list.splice(i,1)[0];list.splice(j,0,item); },
            valid() { return this.title.trim() && this.sections.length>0 && this.sections.every(s=>s.title.trim() && s.clauses.length>0 && s.clauses.every(c=>c.text.trim())); }
        };
    }
    </script>
</x-app-layout>
