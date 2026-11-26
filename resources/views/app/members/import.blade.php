<x-app-layout header="Bulk Import Members">
    <div class="space-y-6 max-w-7xl mx-auto" x-data="{
        isDragging: false,
        fileChosen: false,
        fileName: '',
        fileSize: '',
        duplicateStrategy: '{{ $duplicateStrategy ?? 'skip' }}',
        copySuccess: false,
        handleFileSelect(e) {
            const files = e.target.files || (e.dataTransfer ? e.dataTransfer.files : []);
            if (files && files.length > 0) {
                const file = files[0];
                this.fileName = file.name;
                const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                this.fileSize = sizeMb > 0 ? sizeMb + ' MB' : Math.round(file.size / 1024) + ' KB';
                this.fileChosen = true;
                if (e.dataTransfer) {
                    this.$refs.csvInput.files = files;
                }
            }
        },
        copyHeaders() {
            const headers = 'Member ID / Code,First Name,Last Name,Full Name,Mobile Phone,Alternate Phone,Email,Gender,Date of Birth,Blood Group,City,Residential Address,Emergency Contact Name,Emergency Contact Phone,Emergency Relation,Join Date,Status,Branch Name,Active Plan,Plan Expiry Date,Remaining Due,Notes';
            navigator.clipboard.writeText(headers).then(() => {
                this.copySuccess = true;
                setTimeout(() => this.copySuccess = false, 2500);
            });
        }
    }">

        <!-- Flash Alerts -->
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-400 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-xs sm:text-sm font-semibold text-rose-200">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-fg-muted hover:text-white text-sm cursor-pointer px-2">✕</button>
            </div>
        @endif

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs sm:text-sm font-semibold text-emerald-200">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-fg-muted hover:text-white text-sm cursor-pointer px-2">✕</button>
            </div>
        @endif

        <!-- Header Hero Panel -->
        <div class="relative overflow-hidden rounded-xl bg-surface border border-line p-6 sm:p-7">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative z-10">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('app.members.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>Back to Members</span>
                        </a>
                        <span class="text-fg-muted">/</span>
                        <span class="text-xs font-medium text-fg-muted">Bulk Import</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-semibold text-white tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        </div>
                        <span>Bulk Import Members</span>
                    </h1>

                    <p class="text-xs sm:text-sm text-fg-muted max-w-2xl leading-relaxed">
                        Fast and automated batch enrollment. Upload member rosters with contact numbers, profiles, emergency contacts, and active subscriptions.
                    </p>
                </div>

                <!-- Top Actions & Flow Steps -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="{{ route('app.members.import.sample') }}" 
                       class="px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-700/80 text-fg border border-line text-xs font-semibold flex items-center gap-2 transition-all hover:border-slate-600">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Download Sample CSV</span>
                    </a>

                    <a href="{{ route('app.members.index') }}" 
                       class="px-4 py-2.5 rounded-xl bg-raised hover:bg-raised text-fg-muted hover:text-white border border-line text-xs font-semibold transition-all">
                        Cancel
                    </a>
                </div>
            </div>

            <!-- Stepper Progress Bar -->
            <div class="mt-6 pt-5 border-t border-line grid grid-cols-2 gap-4 max-w-md">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black {{ !isset($preview) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/40 ring-4 ring-indigo-500/20' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' }}">
                        @if(isset($preview))
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                            1
                        @endif
                    </div>
                    <div>
                        <div class="text-[11px] font-bold {{ !isset($preview) ? 'text-white' : 'text-slate-400' }}">Upload File</div>
                        <div class="text-xs text-fg-muted">Configure & Select CSV</div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black {{ isset($preview) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/40 ring-4 ring-indigo-500/20' : 'bg-slate-800 text-slate-500 border border-slate-700/60' }}">
                        2
                    </div>
                    <div>
                        <div class="text-[11px] font-bold {{ isset($preview) ? 'text-white' : 'text-slate-500' }}">Preview & Confirm</div>
                        <div class="text-xs text-fg-muted">Validation & Save</div>
                    </div>
                </div>
            </div>
        </div>

        @if(!isset($preview))
            <!-- STEP 1: UPLOAD FORM & GUIDELINES -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <!-- Left Column: Upload Form (7 Cols) -->
                <div class="lg:col-span-7">
                    <form action="{{ route('app.members.import.preview') }}" method="POST" enctype="multipart/form-data" 
                          class="rounded-xl bg-surface border border-line p-6 sm:p-7 space-y-6">
                        @csrf

                        <!-- Branch Selection (if multi-branch) -->
                        @if($branches->count() > 1)
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted mb-2">
                                    Default Gym Branch
                                </label>
                                <select name="branch_id" 
                                        class="w-full px-4 py-3 rounded-xl bg-surface border border-line text-white text-xs focus:border-accent focus:outline-none transition-colors">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-fg-muted mt-1.5">Members without a specific branch in the CSV will be assigned to this branch.</p>
                            </div>
                        @else
                            <input type="hidden" name="branch_id" value="{{ $branches->first()?->id }}">
                        @endif

                        <!-- Duplicate Strategy Selector -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-semibold text-fg-muted">
                                    Duplicate Handling Strategy
                                </label>
                                <span class="text-xs text-fg-muted font-medium">Matched by Mobile Number</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Option 1: Skip -->
                                <label @click="duplicateStrategy = 'skip'" 
                                       :class="duplicateStrategy === 'skip' ? 'border-indigo-500/80 bg-indigo-500/5 ring-1 ring-indigo-500/40' : 'border-slate-800 bg-slate-950/60 hover:border-slate-700'"
                                       class="p-4 rounded-xl border cursor-pointer transition-all flex items-start gap-3.5 group">
                                    <input type="radio" name="duplicate_strategy" value="skip" x-model="duplicateStrategy" class="hidden">
                                    <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 mt-0.5 transition-all"
                                         :class="duplicateStrategy === 'skip' ? 'border-indigo-500 bg-indigo-600' : 'border-slate-700 bg-slate-900'">
                                        <div class="w-1.5 h-1.5 rounded-full bg-surface" x-show="duplicateStrategy === 'skip'"></div>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="text-xs font-semibold text-white group-hover:text-indigo-300 transition-colors">Skip Duplicates</div>
                                        <div class="text-xs text-fg-muted leading-relaxed">Safely ignore any row if the phone number already exists in your gym.</div>
                                    </div>
                                </label>

                                <!-- Option 2: Update -->
                                <label @click="duplicateStrategy = 'update'" 
                                       :class="duplicateStrategy === 'update' ? 'border-indigo-500/80 bg-indigo-500/5 ring-1 ring-indigo-500/40' : 'border-slate-800 bg-slate-950/60 hover:border-slate-700'"
                                       class="p-4 rounded-xl border cursor-pointer transition-all flex items-start gap-3.5 group">
                                    <input type="radio" name="duplicate_strategy" value="update" x-model="duplicateStrategy" class="hidden">
                                    <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 mt-0.5 transition-all"
                                         :class="duplicateStrategy === 'update' ? 'border-indigo-500 bg-indigo-600' : 'border-slate-700 bg-slate-900'">
                                        <div class="w-1.5 h-1.5 rounded-full bg-surface" x-show="duplicateStrategy === 'update'"></div>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="text-xs font-semibold text-white group-hover:text-indigo-300 transition-colors">Update Existing</div>
                                        <div class="text-xs text-fg-muted leading-relaxed">Merge new address, email, notes, and plan details if the member phone exists.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Modern Drag & Drop Zone -->
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted mb-2">
                                Member CSV Document *
                            </label>

                            <div @dragover.prevent="isDragging = true"
                                 @dragleave.prevent="isDragging = false"
                                 @drop.prevent="isDragging = false; handleFileSelect($event)"
                                 :class="isDragging ? 'border-indigo-500 bg-indigo-500/10 ring-2 ring-indigo-500/30' : (fileChosen ? 'border-emerald-500/50 bg-emerald-500/5' : 'border-slate-800 bg-slate-950/60 hover:border-slate-700')"
                                 class="relative border-2 border-dashed rounded-xl p-8 sm:p-10 text-center transition-all cursor-pointer group"
                                 @click="$refs.csvInput.click()">

                                <input type="file" 
                                       name="csv_file" 
                                       x-ref="csvInput" 
                                       required 
                                       accept=".csv,.txt" 
                                       @change="handleFileSelect($event)" 
                                       class="hidden">

                                <!-- State A: No file chosen -->
                                <div x-show="!fileChosen" class="space-y-3">
                                    <div class="w-14 h-14 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center mx-auto group-hover:scale-105 transition-transform">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white">
                                            <span class="text-indigo-400 underline underline-offset-2">Click to browse file</span> or drag & drop here
                                        </p>
                                        <p class="text-xs text-fg-muted mt-1">Supports standard CSV files exported from Excel, Google Sheets, or previous gym software</p>
                                    </div>
                                    <div class="pt-1">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-raised text-xs font-semibold text-fg-muted">
                                            <svg class="w-3 h-3 text-fg-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            Max file size: 10 MB (.csv)
                                        </span>
                                    </div>
                                </div>

                                <!-- State B: File Selected Card -->
                                <div x-show="fileChosen" x-cloak class="space-y-3 py-2">
                                    <div class="w-14 h-14 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center justify-center mx-auto">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-white" x-text="fileName"></div>
                                        <div class="text-xs text-emerald-400 font-semibold mt-0.5" x-text="'Ready for validation (' + fileSize + ')'"></div>
                                    </div>
                                    <div class="pt-1">
                                        <button type="button" @click.stop="$refs.csvInput.click()" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 underline underline-offset-2">
                                            Change selected file
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Card Footer Action -->
                        <div class="pt-3 border-t border-line flex flex-col sm:flex-row items-center justify-between gap-3">
                            <a href="{{ route('app.members.import.sample') }}" class="text-xs font-semibold text-fg-muted hover:text-white transition-colors flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Download Clean Template</span>
                            </a>

                            <button type="submit" 
                                    class="w-full sm:w-auto px-6 py-3 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer ops-primary">
                                <span>Preview &amp; Validate Data</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: CSV Specification & Helper (5 Cols) -->
                <div class="lg:col-span-5 space-y-6">

                    <!-- Spec Card -->
                    <div class="rounded-xl bg-surface border border-line p-6 sm:p-7 space-y-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <h3 class="text-xs font-semibold text-white">CSV Data Structure</h3>
                            </div>
                            <button type="button" 
                                    @click="copyHeaders()" 
                                    class="px-2.5 py-1 rounded-lg bg-raised hover:bg-slate-700 text-xs font-semibold text-fg-muted hover:text-white transition-colors flex items-center gap-1 border border-line">
                                <span x-text="copySuccess ? 'Copied!' : 'Copy Headers'"></span>
                            </button>
                        </div>

                        <p class="text-xs text-fg-muted leading-relaxed">
                            Your CSV can contain standard headings in the first row. The system automatically normalizes header names and punctuation:
                        </p>

                        <!-- Code Box for Headers -->
                        <div class="p-3.5 rounded-xl bg-surface border border-line font-mono text-xs text-fg-muted leading-relaxed overflow-x-auto space-y-1">
                            <div class="text-emerald-400 font-semibold"># Recognized Column Headers</div>
                            <div class="text-fg-muted">
                                First Name, Last Name, Mobile Phone, Alternate Phone, Email, Gender, Date of Birth, Blood Group, City, Residential Address, Emergency Contact Name, Emergency Contact Phone, Emergency Relation, Join Date, Status, Branch Name, Active Plan, Plan Expiry Date, Remaining Due, Notes, Member ID / Code
                            </div>
                        </div>

                        <!-- Column Field Badges -->
                        <div class="space-y-3 pt-1">
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-950/60 border border-line">
                                <div class="w-6 h-6 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0 font-semibold text-xs mt-0.5">1</div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-white">First Name &amp; Last Name</span>
                                        <span class="px-1.5 py-0.2 rounded text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30">Required</span>
                                    </div>
                                    <p class="text-xs text-fg-muted mt-0.5">Or provide a single <code>Full Name</code> column; the system splits it automatically.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-950/60 border border-line">
                                <div class="w-6 h-6 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0 font-semibold text-xs mt-0.5">2</div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-white">Mobile Phone</span>
                                        <span class="px-1.5 py-0.2 rounded text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30">Required</span>
                                    </div>
                                    <p class="text-xs text-fg-muted mt-0.5">10-digit Indian mobile number (e.g. <code>9974995179</code> or <code>+91 99749 95179</code>).</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-950/60 border border-line">
                                <div class="w-6 h-6 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0 font-semibold text-xs mt-0.5">3</div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-white">Dates (DOB &amp; Join Date)</span>
                                        <span class="px-1.5 py-0.2 rounded text-xs font-semibold bg-raised text-fg-muted">Optional</span>
                                    </div>
                                    <p class="text-xs text-fg-muted mt-0.5">Standard date format: <code>YYYY-MM-DD</code> or <code>DD-MM-YYYY</code>.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-950/60 border border-line">
                                <div class="w-6 h-6 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0 font-semibold text-xs mt-0.5">4</div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-white">Plan &amp; Member Code</span>
                                        <span class="px-1.5 py-0.2 rounded text-xs font-semibold bg-raised text-fg-muted">Optional</span>
                                    </div>
                                    <p class="text-xs text-fg-muted mt-0.5">If member code is empty, GymDeskHub assigns the next sequential code automatically.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Sample Notice Banner -->
                        <div class="p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-xs text-indigo-200 flex items-start gap-3">
                            <span class="text-base shrink-0">💡</span>
                            <div class="leading-relaxed">
                                <strong>Fast Setup:</strong> Download our <a href="{{ route('app.members.import.sample') }}" class="underline font-semibold text-white hover:text-indigo-300">Sample Template</a>, paste your existing Excel data, and upload for one-click onboarding.
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        @else
            <!-- STEP 2: PREVIEW & CONFIRMATION SCREEN -->
            <div class="space-y-6">

                <!-- Metric Summary Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
                    <div class="p-5 rounded-xl bg-surface border border-line">
                        <span class="text-xs sm:text-xs font-semibold text-fg-muted">Total Rows Parsed</span>
                        <p class="text-2xl sm:text-3xl font-semibold text-white mt-1">{{ $preview['total_parsed'] }}</p>
                    </div>

                    <div class="p-5 rounded-xl bg-surface border border-emerald-500/30">
                        <span class="text-xs sm:text-xs font-semibold text-emerald-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span>Ready to Import</span>
                        </span>
                        <p class="text-2xl sm:text-3xl font-semibold text-emerald-400 mt-1">{{ $preview['valid_count'] }}</p>
                    </div>

                    <div class="p-5 rounded-3xl bg-slate-900 border {{ $preview['duplicate_count'] > 0 ? 'border-amber-500/40' : 'border-slate-800' }} shadow-xl">
                        <span class="text-xs sm:text-xs font-semibold text-amber-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <span>Duplicates Found</span>
                        </span>
                        <p class="text-2xl sm:text-3xl font-semibold text-amber-400 mt-1">{{ $preview['duplicate_count'] }}</p>
                    </div>

                    <div class="p-5 rounded-3xl bg-slate-900 border {{ $preview['invalid_count'] > 0 ? 'border-rose-500/40' : 'border-slate-800' }} shadow-xl">
                        <span class="text-xs sm:text-xs font-semibold text-rose-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                            <span>Invalid Rows</span>
                        </span>
                        <p class="text-2xl sm:text-3xl font-semibold text-rose-400 mt-1">{{ $preview['invalid_count'] }}</p>
                    </div>
                </div>

                <!-- Duplicate Alert Notice (if duplicates found) -->
                @if($preview['duplicate_count'] > 0)
                    <div class="p-4 sm:p-5 rounded-xl bg-amber-500/10 border border-amber-500/25 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-amber-200">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs sm:text-sm font-semibold text-white">
                                    {{ $preview['duplicate_count'] }} Duplicate Member(s) Found
                                </h4>
                                <p class="text-xs text-amber-300/90 mt-0.5">
                                    Current duplicate mode: <strong>{{ strtoupper($duplicateStrategy ?? 'skip') }}</strong>.
                                    @if(($duplicateStrategy ?? 'skip') === 'skip')
                                        Duplicate rows will be bypassed and existing member records preserved.
                                    @else
                                        Existing member details will be updated with the newly provided CSV data.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Valid Rows Preview Table -->
                <div class="rounded-xl bg-surface border border-line overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-line flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                <span>Valid Rows Preview ({{ $preview['valid_count'] }} Ready)</span>
                            </h3>
                            <p class="text-xs text-fg-muted mt-1">Review the parsed member records below before committing to the database.</p>
                        </div>
                    </div>

                    @if(count($preview['valid_rows']) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-950/80 text-fg-muted border-b border-line font-semibold text-xs">
                                        <th class="py-3 px-4">Row</th>
                                        <th class="py-3 px-4">Member ID</th>
                                        <th class="py-3 px-4">Name</th>
                                        <th class="py-3 px-4">Mobile Phone</th>
                                        <th class="py-3 px-4">Email</th>
                                        <th class="py-3 px-4">Gender</th>
                                        <th class="py-3 px-4">Date of Birth</th>
                                        <th class="py-3 px-4">City</th>
                                        <th class="py-3 px-4">Active Plan</th>
                                        <th class="py-3 px-4 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line text-fg-muted">
                                    @foreach(array_slice($preview['valid_rows'], 0, 25) as $row)
                                        <tr class="hover:bg-raised transition-colors">
                                            <td class="py-3 px-4 text-fg-muted font-mono text-xs">{{ $row['row'] }}</td>
                                            <td class="py-3 px-4 font-mono font-semibold text-indigo-400">
                                                {{ $row['member_code'] ?: 'Auto-Gen' }}
                                            </td>
                                            <td class="py-3 px-4 font-semibold text-white">
                                                {{ $row['first_name'] }} {{ $row['last_name'] }}
                                            </td>
                                            <td class="py-3 px-4 font-mono text-emerald-400 font-semibold">
                                                {{ $row['phone'] }}
                                            </td>
                                            <td class="py-3 px-4 text-fg-muted">
                                                {{ $row['email'] ?: '—' }}
                                            </td>
                                            <td class="py-3 px-4 capitalize text-fg-muted">
                                                {{ $row['gender'] }}
                                            </td>
                                            <td class="py-3 px-4 text-fg-muted">
                                                {{ $row['dob'] ?: '—' }}
                                            </td>
                                            <td class="py-3 px-4 text-fg-muted">
                                                {{ $row['city'] ?: '—' }}
                                            </td>
                                            <td class="py-3 px-4 text-fg-muted font-medium">
                                                {{ $row['plan_name'] ?: '—' }}
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                    READY
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(count($preview['valid_rows']) > 25)
                            <div class="p-3.5 text-center text-xs text-fg-muted bg-slate-950/60 border-t border-line">
                                Showing preview of first 25 records. All {{ count($preview['valid_rows']) }} records will be imported upon confirmation.
                            </div>
                        @endif
                    @else
                        <div class="p-12 text-center text-xs text-fg-muted space-y-2">
                            <div class="text-2xl">📄</div>
                            <p class="font-semibold text-white">No Valid Rows Found</p>
                            <p class="text-fg-muted">Please check your column headers and ensure mobile numbers are 10 digits.</p>
                        </div>
                    @endif
                </div>

                <!-- Invalid Rows Table (if any) -->
                @if(count($preview['invalid_rows']) > 0)
                    <div class="rounded-xl bg-surface border border-rose-900/30 overflow-hidden">
                        <div class="p-5 sm:p-6 border-b border-rose-900/30 bg-rose-500/5 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-rose-400 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                    <span>Invalid Rows ({{ count($preview['invalid_rows']) }} Skipped)</span>
                                </h3>
                                <p class="text-xs text-fg-muted mt-1">These rows contain validation errors and will be omitted from the import.</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-rose-500/5 text-fg-muted border-b border-rose-900/30 font-semibold text-xs">
                                        <th class="py-3 px-4">Row</th>
                                        <th class="py-3 px-4">Name</th>
                                        <th class="py-3 px-4">Provided Phone</th>
                                        <th class="py-3 px-4">Error Reason</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-rose-900/20 text-fg-muted">
                                    @foreach($preview['invalid_rows'] as $inv)
                                        <tr>
                                            <td class="py-3 px-4 font-mono text-fg-muted">{{ $inv['row'] }}</td>
                                            <td class="py-3 px-4 font-semibold text-white">{{ $inv['name'] }}</td>
                                            <td class="py-3 px-4 font-mono text-fg-muted">{{ $inv['phone'] ?: 'Empty' }}</td>
                                            <td class="py-3 px-4 text-rose-400 font-medium">{{ $inv['reason'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Sticky Confirmation Bar -->
                <div class="p-6 rounded-xl bg-surface border border-line flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="space-y-0.5 text-center sm:text-left">
                        <div class="text-sm font-semibold text-white">
                            Ready to finalize member import?
                        </div>
                        <div class="text-xs text-fg-muted">
                            {{ $preview['valid_count'] }} member(s) will be created in your gym database.
                        </div>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="{{ route('app.members.import') }}" 
                           class="flex-1 sm:flex-none text-center px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-700 text-fg-muted hover:text-white font-semibold text-xs transition-colors border border-line">
                            Cancel &amp; Re-upload
                        </a>

                        @if($preview['valid_count'] > 0 || ($preview['duplicate_count'] > 0 && ($duplicateStrategy ?? 'skip') === 'update'))
                            <form action="{{ route('app.members.import.confirm') }}" method="POST" class="flex-1 sm:flex-none">
                                @csrf
                                <input type="hidden" name="duplicate_strategy" value="{{ $duplicateStrategy ?? 'skip' }}">
                                <button type="submit" 
                                        class="w-full sm:w-auto px-7 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer ops-primary">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Confirm &amp; Import {{ $preview['valid_count'] }} Members</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

            </div>
        @endif

    </div>
</x-app-layout>
