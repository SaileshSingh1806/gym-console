@props([
    'name' => 'dob',
    'value' => null,
    'label' => 'Date of Birth',
    'required' => false,
    'model' => null,
    'id' => null,
    'minAge' => 10,
    'maxAge' => 85,
    'showAge' => true,
    'allowTypeToggle' => true,
    'containerClass' => '',
])

@php
    $inputId = $id ?? 'dob_' . str_replace(['[', ']', '.'], '_', $name) . '_' . uniqid();
    $currentYear = (int) date('Y');
    $maxYear = $currentYear - $minAge;
    $minYear = $currentYear - $maxAge;
    $initial = old($name, $value ?? '');
    if ($initial instanceof \Carbon\CarbonInterface) {
        $initial = $initial->format('Y-m-d');
    }
@endphp

<div class="{{ $containerClass }}"
     x-data="{
        mode: 'select',
        day: '',
        month: '',
        year: '',
        typeText: '',
        rawInitial: @js($initial ?? ''),

        init() {
            if (this.rawInitial) {
                this.parseValue(this.rawInitial);
            }
        },

        parseValue(val) {
            if (val === this.fullDob) return;
            if (!val || typeof val !== 'string') {
                this.day = '';
                this.month = '';
                this.year = '';
                this.typeText = '';
                return;
            }
            val = val.trim();
            let y = '', m = '', d = '';

            if (val.includes('-')) {
                const parts = val.split('-');
                if (parts.length === 3) {
                    y = parts[0];
                    m = parts[1].padStart(2, '0');
                    d = parts[2].padStart(2, '0');
                }
            } else if (val.includes('/')) {
                const parts = val.split('/');
                if (parts.length === 3) {
                    d = parts[0].padStart(2, '0');
                    m = parts[1].padStart(2, '0');
                    y = parts[2];
                }
            }

            if (y && m && d) {
                this.year = y;
                this.month = m;
                this.day = d;
                this.typeText = `${d}/${m}/${y}`;
            }
        },

        get fullDob() {
            if (this.year && this.month && this.day) {
                const y = String(this.year).trim();
                const m = String(this.month).padStart(2, '0');
                const d = String(this.day).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }
            return '';
        },

        get age() {
            if (!this.year || !this.month || !this.day) return null;
            const y = parseInt(this.year, 10);
            const m = parseInt(this.month, 10) - 1;
            const d = parseInt(this.day, 10);
            if (isNaN(y) || isNaN(m) || isNaN(d)) return null;

            const birth = new Date(y, m, d);
            const today = new Date();
            let age = today.getFullYear() - birth.getFullYear();
            const monthDiff = today.getMonth() - birth.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            return age >= 0 && age < 120 ? age : null;
        },

        syncToModel() {
            @if($model)
            if (typeof {{ $model }} !== 'undefined') {
                {{ $model }} = this.fullDob;
            }
            @endif
        },

        onSelectChange() {
            if (this.day && this.month && this.year) {
                const d = String(this.day).padStart(2, '0');
                const m = String(this.month).padStart(2, '0');
                this.typeText = `${d}/${m}/${this.year}`;
            } else {
                this.typeText = '';
            }
            this.syncToModel();
        },

        handleTypeInput(e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 8);
            if (v.length >= 5) {
                v = v.slice(0, 2) + '/' + v.slice(2, 4) + '/' + v.slice(4);
            } else if (v.length >= 3) {
                v = v.slice(0, 2) + '/' + v.slice(2);
            }
            this.typeText = v;

            if (v.length === 10) {
                const parts = v.split('/');
                const d = parseInt(parts[0], 10);
                const m = parseInt(parts[1], 10);
                const y = parseInt(parts[2], 10);
                const currentY = new Date().getFullYear();

                if (d >= 1 && d <= 31 && m >= 1 && m <= 12 && y >= 1930 && y <= currentY) {
                    this.day = String(d).padStart(2, '0');
                    this.month = String(m).padStart(2, '0');
                    this.year = String(y);
                    this.syncToModel();
                }
            } else if (v.length === 0) {
                this.clear();
            }
        },

        clear() {
            this.day = '';
            this.month = '';
            this.year = '';
            this.typeText = '';
            this.syncToModel();
        }
     }"
     @if($model)
     x-effect="if (typeof {{ $model }} !== 'undefined' && {{ $model }} !== fullDob) { parseValue({{ $model }}); }"
     @endif>

    <!-- Header Label & Controls Row -->
    <div class="flex items-center justify-between mb-1.5">
        <label for="{{ $inputId }}_day" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
            {{ $label }}
            @if($required)
                <span class="text-rose-500 font-bold ml-0.5">*</span>
            @endif
        </label>

        <div class="flex items-center gap-2">
            <!-- Smart Live Age Badge -->
            @if($showAge)
            <template x-if="age !== null">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20 animate-fade-in shadow-xs">
                    <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="age + ' yrs old'"></span>
                </span>
            </template>
            @endif

            <!-- Clear button -->
            <button type="button" 
                    x-show="day || month || year || typeText" 
                    @click="clear()" 
                    title="Clear date"
                    class="text-[11px] text-slate-400 hover:text-rose-500 transition-colors p-0.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- Mode Toggle (Select vs Quick Type) -->
            @if($allowTypeToggle)
            <button type="button" 
                    @click="mode = (mode === 'select' ? 'type' : 'select')"
                    class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer flex items-center gap-1 px-1.5 py-0.5 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800"
                    :title="mode === 'select' ? 'Switch to typing DD/MM/YYYY' : 'Switch to dropdown selectors'">
                <template x-if="mode === 'select'">
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Type
                    </span>
                </template>
                <template x-if="mode === 'type'">
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        Pick
                    </span>
                </template>
            </button>
            @endif
        </div>
    </div>

    <!-- Hidden Input for Form Submission -->
    <input type="hidden" 
           name="{{ $name }}" 
           :value="fullDob" 
           @if($required) required @endif>

    <!-- 1. SELECT MODE (Day / Month / Year 3 clean dropdowns) -->
    <div x-show="mode === 'select'" class="grid grid-cols-12 gap-1.5 sm:gap-2">
        <!-- Day Select -->
        <div class="col-span-4 sm:col-span-3">
            <select id="{{ $inputId }}_day"
                    x-model="day" 
                    @change="onSelectChange()"
                    class="w-full px-2.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm cursor-pointer transition-colors">
                <option value="" class="text-slate-400">Day</option>
                @for($d = 1; $d <= 31; $d++)
                    @php $val = sprintf('%02d', $d); @endphp
                    <option value="{{ $val }}">{{ $val }}</option>
                @endfor
            </select>
        </div>

        <!-- Month Select -->
        <div class="col-span-4 sm:col-span-5">
            <select id="{{ $inputId }}_month"
                    x-model="month" 
                    @change="onSelectChange()"
                    class="w-full px-2.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm cursor-pointer transition-colors">
                <option value="" class="text-slate-400">Month</option>
                @php
                    $months = [
                        '01' => 'Jan (01)',
                        '02' => 'Feb (02)',
                        '03' => 'Mar (03)',
                        '04' => 'Apr (04)',
                        '05' => 'May (05)',
                        '06' => 'Jun (06)',
                        '07' => 'Jul (07)',
                        '08' => 'Aug (08)',
                        '09' => 'Sep (09)',
                        '10' => 'Oct (10)',
                        '11' => 'Nov (11)',
                        '12' => 'Dec (12)',
                    ];
                @endphp
                @foreach($months as $num => $labelMonth)
                    <option value="{{ $num }}">{{ $labelMonth }}</option>
                @endforeach
            </select>
        </div>

        <!-- Year Select (Descending order: common gym member ages at the top!) -->
        <div class="col-span-4 sm:col-span-4">
            <select id="{{ $inputId }}_year"
                    x-model="year" 
                    @change="onSelectChange()"
                    class="w-full px-2.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm cursor-pointer transition-colors">
                <option value="" class="text-slate-400">Year</option>
                @for($y = $maxYear; $y >= $minYear; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    <!-- 2. QUICK TYPE MODE (DD / MM / YYYY text input with auto mask) -->
    <div x-show="mode === 'type'" x-cloak>
        <div class="relative">
            <input type="text" 
                   x-model="typeText" 
                   @input="handleTypeInput($event)"
                   placeholder="DD / MM / YYYY (e.g. 15/08/1998)" 
                   maxlength="10"
                   class="w-full pl-3.5 pr-8 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>
</div>
