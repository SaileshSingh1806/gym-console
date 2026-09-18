<x-admin-layout header="SaaS Global Platform Settings">
    <div class="space-y-6" x-data="{ 
        activeTab: '{{ session('activeTab', request('tab', 'general')) }}',
        showSmtpPassword: false,
        showRazorpaySecret: false,
        showStripeSecret: false,
        showGeminiKey: false,
        showCurrentPassword: false,
        showNewPassword: false,
        showConfirmPassword: false,
        geminiApiKey: '{{ $settings['gemini_api_key'] ?? config('services.gemini.api_key', '') }}',
        geminiModel: '{{ $settings['gemini_model'] ?? config('services.gemini.model', 'gemini-1.5-flash') }}',
        testingGemini: false,
        geminiTestStatus: null,
        geminiTestMsg: '',
        logoPreview: '{{ $settings['logo_url'] ?? '' }}',
        faviconPreview: '{{ $settings['favicon_url'] ?? '' }}',
        ogPreview: '{{ $settings['og_image_url'] ?? '' }}',
        handleFileSelect(event, target) {
            const file = event.target.files[0];
            if (file) {
                this[target] = URL.createObjectURL(file);
            }
        },
        async testGemini() {
            this.testingGemini = true;
            this.geminiTestStatus = null;
            this.geminiTestMsg = '';
            try {
                const res = await fetch('{{ route('admin.settings.gemini.test') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        gemini_api_key: this.geminiApiKey,
                        gemini_model: this.geminiModel
                    })
                });
                const data = await res.json();
                this.geminiTestStatus = data.success ? 'success' : 'error';
                this.geminiTestMsg = data.message || (data.success ? 'Google Gemini AI connected successfully!' : 'Connection failed');
            } catch (e) {
                this.geminiTestStatus = 'error';
                this.geminiTestMsg = 'Network error or unable to reach server.';
            } finally {
                this.testingGemini = false;
            }
        }
    }">

        <!-- Navigation Tabs Bar -->
        <div class="p-2 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-2 overflow-x-auto no-scrollbar flex-nowrap pb-2 transition-colors">
            <button type="button" @click="activeTab = 'general'" :class="activeTab === 'general' ? 'bg-red-600 text-white font-bold shadow-lg shadow-red-600/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>🏢 General & Branding</span>
            </button>

            <button type="button" @click="activeTab = 'email'" :class="activeTab === 'email' ? 'bg-red-600 text-white font-bold shadow-lg shadow-red-600/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>✉️ Email / SMTP</span>
            </button>

            <button type="button" @click="activeTab = 'payment'" :class="activeTab === 'payment' ? 'bg-red-600 text-white font-bold shadow-lg shadow-red-600/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>💳 Payment Gateways</span>
            </button>

            <button type="button" @click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'bg-red-600 text-white font-bold shadow-lg shadow-red-600/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>🔍 Site SEO & Analytics</span>
            </button>

            <button type="button" @click="activeTab = 'ai'" :class="activeTab === 'ai' ? 'bg-gradient-to-r from-purple-600 via-indigo-600 to-pink-600 text-white font-bold shadow-lg shadow-purple-600/25' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap shrink-0">
                <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>✨ AI & Google Gemini</span>
                @if(!empty($settings['gemini_api_key']) || !empty(config('services.gemini.api_key')))
                    <span class="px-1.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 text-[9px] font-bold">ACTIVE</span>
                @endif
            </button>

            <button type="button" @click="activeTab = 'security'" :class="activeTab === 'security' ? 'bg-red-600 text-white font-bold shadow-lg shadow-red-600/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>🔒 Security & Password</span>
            </button>
        </div>

        <!-- TAB 1: General & Branding Settings -->
        <div x-show="activeTab === 'general'" class="space-y-6" x-cloak>
            <form action="{{ route('admin.settings.general') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <!-- Identity Card -->
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs dark:shadow-xl space-y-6 transition-colors">
                    <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Platform Identity & Brand Assets</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Configure your SaaS platform name, tagline, logo and favicon</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Platform / App Name *</label>
                            <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? 'Gym Console') }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-1">Displayed in browser title, header navbar, and invoices</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Platform Tagline</label>
                            <input type="text" name="app_tagline" value="{{ old('app_tagline', $settings['app_tagline'] ?? '') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-1">Short branding slogan for marketing pages</span>
                        </div>
                    </div>

                    <!-- Logo & Favicon Upload -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        <!-- Logo Upload -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-3">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Platform Logo (Main Header)</label>
                            
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                                    <template x-if="logoPreview">
                                        <img :src="logoPreview" alt="Logo Preview" class="max-h-full max-w-full object-contain p-2">
                                    </template>
                                    <template x-if="!logoPreview">
                                        <div class="text-center text-slate-400 dark:text-slate-500 text-[10px] font-bold">NO LOGO</div>
                                    </template>
                                </div>
                                <div class="space-y-2 flex-1">
                                    <input type="file" name="logo" @change="handleFileSelect($event, 'logoPreview')" accept="image/*" class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-red-600 file:text-white hover:file:bg-red-500 cursor-pointer">
                                    <input type="text" name="logo_url" value="{{ old('logo_url', $settings['logo_url'] ?? '') }}" @input="logoPreview = $event.target.value" placeholder="Or enter direct Logo Image URL..." class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-[11px] focus:border-red-500 focus:outline-none">
                                </div>
                            </div>
                        </div>

                        <!-- Favicon Upload -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-3">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Platform Favicon (Browser Tab Icon)</label>
                            
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                                    <template x-if="faviconPreview">
                                        <img :src="faviconPreview" alt="Favicon Preview" class="w-8 h-8 object-contain">
                                    </template>
                                    <template x-if="!faviconPreview">
                                        <div class="text-center text-slate-400 dark:text-slate-500 text-[10px] font-bold">NO FAVICON</div>
                                    </template>
                                </div>
                                <div class="space-y-2 flex-1">
                                    <input type="file" name="favicon" @change="handleFileSelect($event, 'faviconPreview')" accept=".ico,.png,.svg,.jpg,.jpeg" class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-red-600 file:text-white hover:file:bg-red-500 cursor-pointer">
                                    <input type="text" name="favicon_url" value="{{ old('favicon_url', $settings['favicon_url'] ?? '') }}" @input="faviconPreview = $event.target.value" placeholder="Or enter direct Favicon URL..." class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-[11px] focus:border-red-500 focus:outline-none">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support & System Settings -->
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs dark:shadow-xl space-y-6 transition-colors">
                    <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Contact & Localization Settings</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Default currency, timezone, and customer support channels</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Support Email *</label>
                            <input type="email" name="support_email" value="{{ old('support_email', $settings['support_email'] ?? 'support@gymconsole.com') }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Support Phone</label>
                            <input type="text" name="support_phone" value="{{ old('support_phone', $settings['support_phone'] ?? '+91 98765 43210') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Default Currency *</label>
                            <select name="default_currency" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                                <option value="INR" {{ ($settings['default_currency'] ?? 'INR') === 'INR' ? 'selected' : '' }}>INR (₹) - Indian Rupee</option>
                                <option value="USD" {{ ($settings['default_currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                                <option value="EUR" {{ ($settings['default_currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                                <option value="GBP" {{ ($settings['default_currency'] ?? '') === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                                <option value="AED" {{ ($settings['default_currency'] ?? '') === 'AED' ? 'selected' : '' }}>AED (د.إ) - UAE Dirham</option>
                                <option value="CAD" {{ ($settings['default_currency'] ?? '') === 'CAD' ? 'selected' : '' }}>CAD ($) - Canadian Dollar</option>
                                <option value="AUD" {{ ($settings['default_currency'] ?? '') === 'AUD' ? 'selected' : '' }}>AUD ($) - Australian Dollar</option>
                                <option value="SAR" {{ ($settings['default_currency'] ?? '') === 'SAR' ? 'selected' : '' }}>SAR (﷼) - Saudi Riyal</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Platform Timezone *</label>
                            <select name="default_timezone" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                                <option value="Asia/Kolkata" {{ ($settings['default_timezone'] ?? 'Asia/Kolkata') === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST +5:30)</option>
                                <option value="UTC" {{ ($settings['default_timezone'] ?? '') === 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="America/New_York" {{ ($settings['default_timezone'] ?? '') === 'America/New_York' ? 'selected' : '' }}>America/New York (EST/EDT)</option>
                                <option value="America/Los_Angeles" {{ ($settings['default_timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los Angeles (PST/PDT)</option>
                                <option value="Europe/London" {{ ($settings['default_timezone'] ?? '') === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT/BST)</option>
                                <option value="Europe/Paris" {{ ($settings['default_timezone'] ?? '') === 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris (CET/CEST)</option>
                                <option value="Asia/Dubai" {{ ($settings['default_timezone'] ?? '') === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST +4)</option>
                                <option value="Asia/Singapore" {{ ($settings['default_timezone'] ?? '') === 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore (SGT +8)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Footer Copyright Text</label>
                        <input type="text" name="footer_copyright" value="{{ old('footer_copyright', $settings['footer_copyright'] ?? '© '.date('Y').' Gym Console SaaS. All rights reserved.') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-lg shadow-red-600/25 transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save General Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 2: Email / SMTP Settings -->
        <div x-show="activeTab === 'email'" class="space-y-6" x-cloak>
            <form action="{{ route('admin.settings.email') }}" method="POST" class="space-y-6">
                @csrf
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs dark:shadow-xl space-y-6 transition-colors">
                    <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Global SMTP Mail Server Configuration</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Configure the mail transport engine used for transactional receipts, password resets, and automated alerts</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mail Mailer Engine *</label>
                            <select name="mail_mailer" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                                <option value="smtp" {{ ($settings['mail_mailer'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP (Recommended)</option>
                                <option value="sendmail" {{ ($settings['mail_mailer'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="log" {{ ($settings['mail_mailer'] ?? '') === 'log' ? 'selected' : '' }}>Log Driver (Testing / Dev)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">SMTP Host *</label>
                            <input type="text" name="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? 'smtp.gmail.com') }}" required placeholder="smtp.gmail.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">SMTP Port *</label>
                            <input type="number" name="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}" required placeholder="587" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">SMTP Username</label>
                            <input type="text" name="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" placeholder="apikey or admin@domain.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">SMTP Password / App Secret</label>
                            <div class="relative">
                                <input :type="showSmtpPassword ? 'text' : 'password'" name="mail_password" value="{{ old('mail_password', $settings['mail_password'] ?? '') }}" placeholder="••••••••••••" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none pr-10 shadow-xs">
                                <button type="button" @click="showSmtpPassword = !showSmtpPassword" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs cursor-pointer">
                                    <span x-show="!showSmtpPassword">👁️</span>
                                    <span x-show="showSmtpPassword">🙈</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Encryption Protocol *</label>
                            <select name="mail_encryption" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                                <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                                <option value="ssl" {{ ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                <option value="none" {{ ($settings['mail_encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">From Email Address *</label>
                            <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? 'noreply@gymconsole.com') }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">From Sender Name *</label>
                            <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? 'Gym Console Platform') }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-lg shadow-red-600/25 transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save Email Settings</span>
                    </button>
                </div>
            </form>

            <!-- Test Email Dispatch Card -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs dark:shadow-xl space-y-4 transition-colors">
                <div class="border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Send Test Diagnostic Email</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Verify your SMTP credentials by dispatching a real-time test notification message</p>
                </div>

                <form action="{{ route('admin.settings.email.test') }}" method="POST" class="flex flex-wrap items-center gap-3">
                    @csrf
                    <div class="flex-grow max-w-md">
                        <input type="email" name="test_email" value="{{ auth()->user()->email }}" required placeholder="recipient@example.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                    </div>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-300 dark:border-slate-700 transition-colors shadow-xs cursor-pointer">
                        🚀 Send Diagnostic Email
                    </button>
                </form>
            </div>
        </div>

        <!-- TAB 3: Payment Gateways (Razorpay & Stripe) -->
        <div x-show="activeTab === 'payment'" class="space-y-6" x-cloak>
            <form action="{{ route('admin.settings.payment') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Razorpay Gateway Card -->
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs dark:shadow-xl space-y-6 transition-colors">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black text-sm">
                                RZ
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Razorpay Payment Gateway (India / UPI / Cards)</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Primary gateway for SaaS subscription plans & renewal invoices</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="razorpay_enabled" value="1" {{ !empty($settings['razorpay_enabled']) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-2 text-xs font-bold text-slate-700 dark:text-slate-300">Enabled</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Razorpay Environment</label>
                            <select name="razorpay_mode" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                                <option value="sandbox" {{ ($settings['razorpay_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>🟡 Test / Sandbox</option>
                                <option value="live" {{ ($settings['razorpay_mode'] ?? '') === 'live' ? 'selected' : '' }}>🟢 Live / Production</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Razorpay Key ID</label>
                            <input type="text" name="razorpay_key_id" value="{{ old('razorpay_key_id', $settings['razorpay_key_id'] ?? '') }}" placeholder="rzp_test_..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none font-mono shadow-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Razorpay Key Secret</label>
                            <div class="relative">
                                <input :type="showRazorpaySecret ? 'text' : 'password'" name="razorpay_key_secret" value="{{ old('razorpay_key_secret', $settings['razorpay_key_secret'] ?? '') }}" placeholder="••••••••••••" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none font-mono pr-10 shadow-xs">
                                <button type="button" @click="showRazorpaySecret = !showRazorpaySecret" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs cursor-pointer">
                                    <span x-show="!showRazorpaySecret">👁️</span>
                                    <span x-show="showRazorpaySecret">🙈</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Webhook Secret</label>
                            <input type="text" name="razorpay_webhook_secret" value="{{ old('razorpay_webhook_secret', $settings['razorpay_webhook_secret'] ?? '') }}" placeholder="whsec_..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none font-mono shadow-xs">
                        </div>
                    </div>
                </div>

                <!-- Stripe Gateway Card -->
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs dark:shadow-xl space-y-6 transition-colors">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center font-black text-sm">
                                ST
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Stripe Payments (Global & International Cards)</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Accept international credit cards and multi-currency billing</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="stripe_enabled" value="1" {{ !empty($settings['stripe_enabled']) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-2 text-xs font-bold text-slate-700 dark:text-slate-300">Enabled</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Stripe Publishable Key</label>
                            <input type="text" name="stripe_key" value="{{ old('stripe_key', $settings['stripe_key'] ?? '') }}" placeholder="pk_test_..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none font-mono shadow-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Stripe Secret Key</label>
                            <div class="relative">
                                <input :type="showStripeSecret ? 'text' : 'password'" name="stripe_secret" value="{{ old('stripe_secret', $settings['stripe_secret'] ?? '') }}" placeholder="sk_test_..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none font-mono pr-10 shadow-xs">
                                <button type="button" @click="showStripeSecret = !showStripeSecret" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs cursor-pointer">
                                    <span x-show="!showStripeSecret">👁️</span>
                                    <span x-show="showStripeSecret">🙈</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Stripe Webhook Secret</label>
                            <input type="text" name="stripe_webhook_secret" value="{{ old('stripe_webhook_secret', $settings['stripe_webhook_secret'] ?? '') }}" placeholder="whsec_..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none font-mono shadow-xs">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-lg shadow-red-600/25 transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save Payment Gateways</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 4: Site SEO & Analytics -->
        <div x-show="activeTab === 'seo'" class="space-y-6" x-cloak>
            <form action="{{ route('admin.settings.seo') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs dark:shadow-xl space-y-6 transition-colors">
                    <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Search Engine Optimization (SEO) & Social Graph</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Configure global search meta tags, OpenGraph sharing cards, and marketing pixels</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Global Meta Title *</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $settings['meta_title'] ?? 'Gym Console - Multi-Tenant Gym Management SaaS') }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meta Description *</label>
                            <textarea name="meta_description" rows="3" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">{{ old('meta_description', $settings['meta_description'] ?? 'Complete gym membership management, biometric access control with Hikvision, POS billing, and multi-tenant SaaS dashboard.') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meta Keywords</label>
                            <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $settings['meta_keywords'] ?? 'gym software, gym management saas, gym attendance system, biometric access control') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs">
                        </div>

                        <!-- OpenGraph Image -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-3">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">OpenGraph Social Share Image (1200x630px recommended)</label>
                            <div class="flex items-center gap-4">
                                <div class="w-32 h-18 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                                    <template x-if="ogPreview">
                                        <img :src="ogPreview" alt="OG Preview" class="max-h-full max-w-full object-cover">
                                    </template>
                                    <template x-if="!ogPreview">
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold">1200x630 OG</span>
                                    </template>
                                </div>
                                <div class="space-y-2 flex-1">
                                    <input type="file" name="og_image" @change="handleFileSelect($event, 'ogPreview')" accept="image/*" class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-red-600 file:text-white hover:file:bg-red-500 cursor-pointer">
                                    <input type="text" name="og_image_url" value="{{ old('og_image_url', $settings['og_image_url'] ?? '') }}" @input="ogPreview = $event.target.value" placeholder="Or enter direct Social Image URL..." class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-[11px] focus:border-red-500 focus:outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Google Analytics Measurement ID</label>
                                <input type="text" name="google_analytics_id" value="{{ old('google_analytics_id', $settings['google_analytics_id'] ?? '') }}" placeholder="G-XXXXXXXXXX" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none font-mono shadow-xs">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Facebook Pixel ID</label>
                                <input type="text" name="facebook_pixel_id" value="{{ old('facebook_pixel_id', $settings['facebook_pixel_id'] ?? '') }}" placeholder="123456789012345" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none font-mono shadow-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Custom Header Scripts (Inside &lt;head&gt;)</label>
                                <textarea name="custom_header_scripts" rows="4" placeholder="<script>...</script>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none font-mono shadow-xs">{{ old('custom_header_scripts', $settings['custom_header_scripts'] ?? '') }}</textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Custom Footer Scripts (Before &lt;/body&gt;)</label>
                                <textarea name="custom_footer_scripts" rows="4" placeholder="<script>...</script>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none font-mono shadow-xs">{{ old('custom_footer_scripts', $settings['custom_footer_scripts'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-lg shadow-red-600/25 transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save SEO & Analytics</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 5: AI & Google Gemini Settings -->
        <div x-show="activeTab === 'ai'" class="space-y-6" x-cloak>
            <form action="{{ route('admin.settings.ai') }}" method="POST" class="space-y-6">
                @csrf
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs dark:shadow-xl space-y-6 transition-colors">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-500 via-indigo-500 to-pink-500 text-white flex items-center justify-center font-black text-sm shadow-md shadow-purple-500/20">
                                ✨
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Google Gemini Artificial Intelligence Platform</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Powers automatic AI Diet Generation, Workout Routine Planner, and Smart Gym Chatbot</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="gemini_enabled" value="1" {{ !empty($settings['gemini_enabled']) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-purple-600 peer-checked:to-pink-600"></div>
                            <span class="ml-2 text-xs font-bold text-slate-700 dark:text-slate-300">Enabled</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Google Gemini API Key *</label>
                            <div class="relative">
                                <input :type="showGeminiKey ? 'text' : 'password'" name="gemini_api_key" x-model="geminiApiKey" placeholder="AIzaSy..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-purple-500 focus:outline-none font-mono pr-10 shadow-xs">
                                <button type="button" @click="showGeminiKey = !showGeminiKey" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs cursor-pointer">
                                    <span x-show="!showGeminiKey">👁️</span>
                                    <span x-show="showGeminiKey">🙈</span>
                                </button>
                            </div>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-1">Get your free API key from Google AI Studio (aistudio.google.com)</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">AI Foundation Model *</label>
                            <select name="gemini_model" x-model="geminiModel" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-purple-500 focus:outline-none shadow-xs">
                                <option value="gemini-1.5-flash">Gemini 1.5 Flash (Ultra Fast & Recommended for SaaS)</option>
                                <option value="gemini-1.5-pro">Gemini 1.5 Pro (Complex Reasoning & Deep Diet Plans)</option>
                                <option value="gemini-2.0-flash-exp">Gemini 2.0 Flash Experimental</option>
                                <option value="gemini-2.5-flash">Gemini 2.5 Flash</option>
                            </select>
                        </div>
                    </div>

                    <!-- Connection Diagnostic Status -->
                    <div class="p-4 rounded-2xl bg-purple-500/5 dark:bg-purple-950/20 border border-purple-500/20 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🧠</span>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white">Real-Time Gemini API Health Test</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Performs live handshake with Google DeepMind generative endpoints</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <template x-if="geminiTestStatus === 'success'">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20" x-text="geminiTestMsg"></span>
                            </template>
                            <template x-if="geminiTestStatus === 'error'">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20" x-text="geminiTestMsg"></span>
                            </template>
                            <button type="button" @click="testGemini()" :disabled="testingGemini" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-md shadow-purple-600/20 transition-all cursor-pointer flex items-center gap-2">
                                <span x-show="testingGemini" class="animate-spin">⏳</span>
                                <span x-text="testingGemini ? 'Testing...' : '⚡ Test Connection'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-purple-600 via-indigo-600 to-pink-600 hover:opacity-90 text-white font-bold text-xs shadow-lg shadow-purple-600/25 transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save Gemini AI Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 6: Security & Super Admin Password Settings -->
        <div x-show="activeTab === 'security'" class="space-y-6" x-cloak>
            <form action="{{ route('admin.settings.password') }}" method="POST" class="space-y-6">
                @csrf
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs dark:shadow-xl space-y-6 transition-colors">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center font-black text-sm">
                                🔐
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Super Admin Account Security & Password</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Change your master Super Admin login password securely</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                Logged in as: {{ auth()->user()->email }}
                            </span>
                        </div>
                    </div>

                    <!-- Current Super Admin Account Vitals -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-red-500 to-rose-600 text-white font-bold flex items-center justify-center text-sm shadow-xs">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 dark:text-white">{{ auth()->user()->name }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 font-mono text-[10px] font-bold">
                                ROLE: SUPER_ADMIN
                            </span>
                            <span class="px-2.5 py-1 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-bold text-[10px]">
                                STATUS: ACTIVE
                            </span>
                        </div>
                    </div>

                    <!-- Password Change Inputs -->
                    <div class="space-y-4 max-w-xl">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Current Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showCurrentPassword ? 'text' : 'password'" name="current_password" required placeholder="Enter current master password" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none pr-10 shadow-xs">
                                <button type="button" @click="showCurrentPassword = !showCurrentPassword" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs cursor-pointer">
                                    <span x-show="!showCurrentPassword">👁️</span>
                                    <span x-show="showCurrentPassword">🙈</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                New Master Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showNewPassword ? 'text' : 'password'" name="password" required minlength="8" placeholder="Enter new strong password (min 8 characters)" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none pr-10 shadow-xs">
                                <button type="button" @click="showNewPassword = !showNewPassword" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs cursor-pointer">
                                    <span x-show="!showNewPassword">👁️</span>
                                    <span x-show="showNewPassword">🙈</span>
                                </button>
                            </div>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-1">Must be at least 8 characters long and different from current password</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Confirm New Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation" required minlength="8" placeholder="Re-enter new password" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none pr-10 shadow-xs">
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs cursor-pointer">
                                    <span x-show="!showConfirmPassword">👁️</span>
                                    <span x-show="showConfirmPassword">🙈</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-lg shadow-red-600/25 transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Update Master Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
