<x-admin-layout header="SaaS Global Platform Settings">
    <div class="space-y-6" x-data="{ 
        activeTab: '{{ session('activeTab', request('tab', 'general')) }}',
        showSmtpPassword: false,
        showRazorpaySecret: false,
        showStripeSecret: false,
        showGeminiKey: false,
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
        <div class="p-2 rounded-2xl bg-slate-900 border border-slate-800 flex flex-wrap gap-2">
            <button type="button" @click="activeTab = 'general'" :class="activeTab === 'general' ? 'bg-red-600 text-white font-bold shadow-lg shadow-red-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'" class="px-5 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>🏢 General &amp; Branding</span>
            </button>

            <button type="button" @click="activeTab = 'email'" :class="activeTab === 'email' ? 'bg-red-600 text-white font-bold shadow-lg shadow-red-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'" class="px-5 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>✉️ Email / SMTP</span>
            </button>

            <button type="button" @click="activeTab = 'payment'" :class="activeTab === 'payment' ? 'bg-red-600 text-white font-bold shadow-lg shadow-red-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'" class="px-5 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>💳 Payment Gateways (Razorpay)</span>
            </button>

            <button type="button" @click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'bg-red-600 text-white font-bold shadow-lg shadow-red-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'" class="px-5 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>🔍 Site SEO &amp; Analytics</span>
            </button>

            <button type="button" @click="activeTab = 'ai'" :class="activeTab === 'ai' ? 'bg-gradient-to-r from-purple-600 via-indigo-600 to-pink-600 text-white font-bold shadow-lg shadow-purple-600/25' : 'text-slate-400 hover:text-white hover:bg-slate-800'" class="px-5 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>✨ AI &amp; Google Gemini</span>
                @if(!empty($settings['gemini_api_key']) || !empty(config('services.gemini.api_key')))
                    <span class="px-1.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[9px] font-bold">ACTIVE</span>
                @endif
            </button>
        </div>

        <!-- TAB 1: General & Branding Settings -->
        <div x-show="activeTab === 'general'" class="space-y-6" x-cloak>
            <form action="{{ route('admin.settings.general') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <!-- Identity Card -->
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-6">
                    <div class="border-b border-slate-800 pb-4">
                        <h3 class="text-base font-bold text-white">Platform Identity & Brand Assets</h3>
                        <p class="text-xs text-slate-400">Configure your SaaS platform name, tagline, logo and favicon</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Platform / App Name *</label>
                            <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? 'Gym Console') }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            <span class="text-[10px] text-slate-500 block mt-1">Displayed in browser title, header navbar, and invoices</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Platform Tagline</label>
                            <input type="text" name="app_tagline" value="{{ old('app_tagline', $settings['app_tagline'] ?? '') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            <span class="text-[10px] text-slate-500 block mt-1">Short branding slogan for marketing pages</span>
                        </div>
                    </div>

                    <!-- Logo & Favicon Upload -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        <!-- Logo Upload -->
                        <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-3">
                            <label class="block text-xs font-semibold text-slate-300">Platform Logo (Main Header)</label>
                            
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center overflow-hidden shrink-0">
                                    <template x-if="logoPreview">
                                        <img :src="logoPreview" alt="Logo Preview" class="max-h-full max-w-full object-contain p-2">
                                    </template>
                                    <template x-if="!logoPreview">
                                        <div class="text-center text-slate-500 text-[10px] font-bold">NO LOGO</div>
                                    </template>
                                </div>
                                <div class="space-y-2 flex-1">
                                    <input type="file" name="logo" @change="handleFileSelect($event, 'logoPreview')" accept="image/*" class="block w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-red-600 file:text-white hover:file:bg-red-500 cursor-pointer">
                                    <input type="text" name="logo_url" value="{{ old('logo_url', $settings['logo_url'] ?? '') }}" @input="logoPreview = $event.target.value" placeholder="Or enter direct Logo Image URL..." class="w-full px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-[11px] focus:border-red-500 focus:outline-none">
                                </div>
                            </div>
                        </div>

                        <!-- Favicon Upload -->
                        <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-3">
                            <label class="block text-xs font-semibold text-slate-300">Platform Favicon (Browser Tab Icon)</label>
                            
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center overflow-hidden shrink-0">
                                    <template x-if="faviconPreview">
                                        <img :src="faviconPreview" alt="Favicon Preview" class="w-8 h-8 object-contain">
                                    </template>
                                    <template x-if="!faviconPreview">
                                        <div class="text-center text-slate-500 text-[10px] font-bold">NO FAVICON</div>
                                    </template>
                                </div>
                                <div class="space-y-2 flex-1">
                                    <input type="file" name="favicon" @change="handleFileSelect($event, 'faviconPreview')" accept=".ico,.png,.svg,.jpg,.jpeg" class="block w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-red-600 file:text-white hover:file:bg-red-500 cursor-pointer">
                                    <input type="text" name="favicon_url" value="{{ old('favicon_url', $settings['favicon_url'] ?? '') }}" @input="faviconPreview = $event.target.value" placeholder="Or enter direct Favicon URL..." class="w-full px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-[11px] focus:border-red-500 focus:outline-none">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support & System Settings -->
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-6">
                    <div class="border-b border-slate-800 pb-4">
                        <h3 class="text-base font-bold text-white">Contact & Localization Settings</h3>
                        <p class="text-xs text-slate-400">Default currency, timezone, and customer support channels</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Support Email *</label>
                            <input type="email" name="support_email" value="{{ old('support_email', $settings['support_email'] ?? 'support@gymconsole.com') }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Support Phone</label>
                            <input type="text" name="support_phone" value="{{ old('support_phone', $settings['support_phone'] ?? '+91 98765 43210') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Default Currency *</label>
                            <select name="default_currency" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="INR" {{ ($settings['default_currency'] ?? 'INR') === 'INR' ? 'selected' : '' }}>INR (₹) - Indian Rupee</option>
                                <option value="USD" {{ ($settings['default_currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                                <option value="EUR" {{ ($settings['default_currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                                <option value="GBP" {{ ($settings['default_currency'] ?? '') === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                                <option value="AED" {{ ($settings['default_currency'] ?? '') === 'AED' ? 'selected' : '' }}>AED (د.إ) - UAE Dirham</option>
                                <option value="SAR" {{ ($settings['default_currency'] ?? '') === 'SAR' ? 'selected' : '' }}>SAR (﷼) - Saudi Riyal</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Default Timezone *</label>
                            <select name="default_timezone" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="Asia/Kolkata" {{ ($settings['default_timezone'] ?? 'Asia/Kolkata') === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST +5:30)</option>
                                <option value="UTC" {{ ($settings['default_timezone'] ?? '') === 'UTC' ? 'selected' : '' }}>UTC (GMT +0:00)</option>
                                <option value="America/New_York" {{ ($settings['default_timezone'] ?? '') === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                                <option value="Europe/London" {{ ($settings['default_timezone'] ?? '') === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                                <option value="Asia/Dubai" {{ ($settings['default_timezone'] ?? '') === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST +4:00)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Footer Copyright Text</label>
                        <input type="text" name="footer_copyright" value="{{ old('footer_copyright', $settings['footer_copyright'] ?? '') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-xs hover:brightness-110 shadow-xl shadow-red-600/20 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save General & Branding Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 2: Email / SMTP Settings -->
        <div x-show="activeTab === 'email'" class="space-y-6" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- SMTP Form -->
                <div class="lg:col-span-2 p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-6">
                    <div class="border-b border-slate-800 pb-4">
                        <h3 class="text-base font-bold text-white">SMTP Email Server Configuration</h3>
                        <p class="text-xs text-slate-400">Configure outbound email delivery for gym registration, receipts, invoices, and password resets</p>
                    </div>

                    <form action="{{ route('admin.settings.email') }}" method="POST" class="space-y-4 text-xs">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1.5">Mail Driver *</label>
                                <select name="mail_mailer" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none">
                                    <option value="smtp" {{ ($settings['mail_mailer'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP (Standard)</option>
                                    <option value="sendmail" {{ ($settings['mail_mailer'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    <option value="log" {{ ($settings['mail_mailer'] ?? '') === 'log' ? 'selected' : '' }}>Log (Dev Testing)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-300 mb-1.5">SMTP Host *</label>
                                <input type="text" name="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? 'smtp.gmail.com') }}" required placeholder="e.g. smtp.gmail.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-300 mb-1.5">SMTP Port *</label>
                                <input type="number" name="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}" required placeholder="587 / 465" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1.5">SMTP Username</label>
                                <input type="text" name="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" placeholder="SMTP Login Email" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none">
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="font-semibold text-slate-300">SMTP Password</label>
                                    <button type="button" @click="showSmtpPassword = !showSmtpPassword" class="text-[10px] text-slate-400 hover:text-white" x-text="showSmtpPassword ? 'Hide' : 'Show'"></button>
                                </div>
                                <input :type="showSmtpPassword ? 'text' : 'password'" name="mail_password" value="{{ old('mail_password', $settings['mail_password'] ?? '') }}" placeholder="••••••••••••" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none font-mono">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-300 mb-1.5">Encryption *</label>
                                <select name="mail_encryption" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none">
                                    <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                                    <option value="ssl" {{ ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                    <option value="none" {{ ($settings['mail_encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1.5">From Address (Sender Email) *</label>
                                <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? 'noreply@gymconsole.com') }}" required placeholder="noreply@gymconsole.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-300 mb-1.5">From Name (Sender Display Name) *</label>
                                <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? 'Gym Console Platform') }}" required placeholder="Gym Console Platform" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-800">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold shadow-lg shadow-red-600/20">
                                Save SMTP Email Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Test Email Card -->
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
                    <div class="border-b border-slate-800 pb-3">
                        <h4 class="text-sm font-bold text-white flex items-center gap-2">
                            <span>🚀</span> Test SMTP Connection
                        </h4>
                        <p class="text-[11px] text-slate-400">Send an instant test email to verify your mail server configuration.</p>
                    </div>

                    <form action="{{ route('admin.settings.email.test') }}" method="POST" class="space-y-4 text-xs">
                        @csrf
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1.5">Recipient Email Address</label>
                            <input type="email" name="test_email" value="{{ auth()->user()->email }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none">
                        </div>

                        <button type="submit" class="w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Send Test Email Now</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 3: Payment Gateways Settings (Razorpay Integration) -->
        <div x-show="activeTab === 'payment'" class="space-y-6" x-cloak>
            <form action="{{ route('admin.settings.payment') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Razorpay Gateway Card (Primary Indian Payment Gateway) -->
                <div class="p-6 rounded-3xl bg-slate-900 border-2 border-amber-500/40 shadow-2xl space-y-6 relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-blue-600/20 border border-blue-500/40 flex items-center justify-center text-blue-400 font-extrabold text-sm">
                                RZP
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white flex items-center gap-2">
                                    <span>Razorpay Payment Gateway</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-500/20 text-blue-300 border border-blue-500/30">INR & UPI Ready</span>
                                </h3>
                                <p class="text-xs text-slate-400">Accept UPI, Credit/Debit Cards, NetBanking, and Wallets in Indian Rupees (₹)</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="razorpay_enabled" id="razorpay_enabled" value="1" {{ !empty($settings['razorpay_enabled']) ? 'checked' : '' }} class="rounded text-amber-500 focus:ring-0">
                            <label for="razorpay_enabled" class="text-xs font-bold text-slate-200 cursor-pointer">Enable Razorpay</label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1.5">Environment / Mode *</label>
                            <select name="razorpay_mode" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none">
                                <option value="sandbox" {{ ($settings['razorpay_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>🟡 Sandbox (Test Mode)</option>
                                <option value="live" {{ ($settings['razorpay_mode'] ?? '') === 'live' ? 'selected' : '' }}>🟢 Live Production Mode</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1.5">Razorpay Key ID *</label>
                            <input type="text" name="razorpay_key_id" value="{{ old('razorpay_key_id', $settings['razorpay_key_id'] ?? '') }}" placeholder="rzp_test_... or rzp_live_..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="font-semibold text-slate-300">Razorpay Key Secret *</label>
                                <button type="button" @click="showRazorpaySecret = !showRazorpaySecret" class="text-[10px] text-slate-400 hover:text-white" x-text="showRazorpaySecret ? 'Hide' : 'Show'"></button>
                            </div>
                            <input :type="showRazorpaySecret ? 'text' : 'password'" name="razorpay_key_secret" value="{{ old('razorpay_key_secret', $settings['razorpay_key_secret'] ?? '') }}" placeholder="Secret Key" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="text-xs">
                        <label class="block font-semibold text-slate-300 mb-1.5">Razorpay Webhook Secret (Optional)</label>
                        <input type="text" name="razorpay_webhook_secret" value="{{ old('razorpay_webhook_secret', $settings['razorpay_webhook_secret'] ?? '') }}" placeholder="Enter secret configured in Razorpay Webhooks dashboard" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:border-red-500 focus:outline-none">
                        <span class="text-[10px] text-slate-500 block mt-1">Webhook URL: <code class="text-amber-400 font-mono">{{ url('/api/v1/webhooks/razorpay') }}</code></span>
                    </div>
                </div>

                <!-- Stripe Gateway Card (International Payments) -->
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-indigo-400 font-extrabold text-sm">
                                STR
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white">Stripe Payment Gateway</h3>
                                <p class="text-xs text-slate-400">Accept Global Credit/Debit Cards (USD, EUR, GBP, AUD, CAD)</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="stripe_enabled" id="stripe_enabled" value="1" {{ !empty($settings['stripe_enabled']) ? 'checked' : '' }} class="rounded text-indigo-500 focus:ring-0">
                            <label for="stripe_enabled" class="text-xs font-bold text-slate-200 cursor-pointer">Enable Stripe</label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1.5">Stripe Publishable Key</label>
                            <input type="text" name="stripe_key" value="{{ old('stripe_key', $settings['stripe_key'] ?? '') }}" placeholder="pk_test_..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="font-semibold text-slate-300">Stripe Secret Key</label>
                                <button type="button" @click="showStripeSecret = !showStripeSecret" class="text-[10px] text-slate-400 hover:text-white" x-text="showStripeSecret ? 'Hide' : 'Show'"></button>
                            </div>
                            <input :type="showStripeSecret ? 'text' : 'password'" name="stripe_secret" value="{{ old('stripe_secret', $settings['stripe_secret'] ?? '') }}" placeholder="sk_test_..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="text-xs">
                        <label class="block font-semibold text-slate-300 mb-1.5">Stripe Webhook Signing Secret</label>
                        <input type="text" name="stripe_webhook_secret" value="{{ old('stripe_webhook_secret', $settings['stripe_webhook_secret'] ?? '') }}" placeholder="whsec_..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:border-red-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-xs hover:brightness-110 shadow-xl shadow-red-600/20 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save Payment Gateways Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 4: Site SEO & Analytics Settings -->
        <div x-show="activeTab === 'seo'" class="space-y-6" x-cloak>
            <form action="{{ route('admin.settings.seo') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <!-- SEO Meta Tags Card -->
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-6">
                    <div class="border-b border-slate-800 pb-4">
                        <h3 class="text-base font-bold text-white">Search Engine Optimization (SEO) Meta Tags</h3>
                        <p class="text-xs text-slate-400">Optimize how Gym Console appears in Google searches and social media shares</p>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1.5">SEO Meta Title *</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $settings['meta_title'] ?? '') }}" required placeholder="e.g. Gym Console - Leading Multi-Tenant Gym Management SaaS" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none">
                            <span class="text-[10px] text-slate-500 block mt-1">Recommended: 50-60 characters for highest Google ranking</span>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1.5">Meta Description *</label>
                            <textarea name="meta_description" rows="3" required placeholder="Describe what Gym Console provides to gym owners..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
                            <span class="text-[10px] text-slate-500 block mt-1">Recommended: 150-160 characters</span>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1.5">Meta Keywords (Comma separated)</label>
                            <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $settings['meta_keywords'] ?? '') }}" placeholder="gym software, gym attendance, biometric access, saas billing" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-red-500 focus:outline-none">
                        </div>

                        <!-- OpenGraph Image -->
                        <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-3">
                            <label class="block font-semibold text-slate-300">Social Share Image (OpenGraph / Twitter Card)</label>
                            <div class="flex items-center gap-4">
                                <div class="w-28 h-16 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center overflow-hidden shrink-0">
                                    <template x-if="ogPreview">
                                        <img :src="ogPreview" alt="OG Preview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!ogPreview">
                                        <span class="text-[10px] text-slate-500 font-bold">1200x630 PX</span>
                                    </template>
                                </div>
                                <div class="space-y-2 flex-1">
                                    <input type="file" name="og_image" @change="handleFileSelect($event, 'ogPreview')" accept="image/*" class="block w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-red-600 file:text-white hover:file:bg-red-500 cursor-pointer">
                                    <input type="text" name="og_image_url" value="{{ old('og_image_url', $settings['og_image_url'] ?? '') }}" @input="ogPreview = $event.target.value" placeholder="Or enter OG Image URL..." class="w-full px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-[11px] focus:border-red-500 focus:outline-none">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tracking & Custom Scripts Card -->
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-6">
                    <div class="border-b border-slate-800 pb-4">
                        <h3 class="text-base font-bold text-white">Analytics, Tracking & Custom Scripts</h3>
                        <p class="text-xs text-slate-400">Add Google Analytics, Facebook Pixel, live chats, or custom tracking code</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1.5">Google Analytics Measurement ID</label>
                            <input type="text" name="google_analytics_id" value="{{ old('google_analytics_id', $settings['google_analytics_id'] ?? '') }}" placeholder="e.g. G-XXXXXXXXXX" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1.5">Facebook / Meta Pixel ID</label>
                            <input type="text" name="facebook_pixel_id" value="{{ old('facebook_pixel_id', $settings['facebook_pixel_id'] ?? '') }}" placeholder="e.g. 123456789012345" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1.5">Custom Header Scripts (<code class="text-red-400">&lt;head&gt;</code>)</label>
                            <textarea name="custom_header_scripts" rows="4" placeholder="<!-- Google Tag Manager, Meta Pixel code, Fonts, etc. -->" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-[11px] focus:border-red-500 focus:outline-none">{{ old('custom_header_scripts', $settings['custom_header_scripts'] ?? '') }}</textarea>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1.5">Custom Footer Scripts (<code class="text-red-400">&lt;body&gt;</code>)</label>
                            <textarea name="custom_footer_scripts" rows="4" placeholder="<!-- Live Chat widgets (Tawk.to, Crisp), Retargeting scripts, etc. -->" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-[11px] focus:border-red-500 focus:outline-none">{{ old('custom_footer_scripts', $settings['custom_footer_scripts'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-xs hover:brightness-110 shadow-xl shadow-red-600/20 flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save SEO &amp; Analytics Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 5: AI & Google Gemini Global Platform Settings -->
        <div x-show="activeTab === 'ai'" class="space-y-6" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form (2 Cols) -->
                <div class="lg:col-span-2 p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-600 via-indigo-600 to-pink-600 text-white flex items-center justify-center text-lg shadow-lg shadow-purple-600/30">
                                ✨
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white flex items-center gap-2">
                                    <span>Google Gemini AI Platform Integration</span>
                                    <span class="px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 text-[10px] font-mono font-bold">API v1beta</span>
                                </h3>
                                <p class="text-xs text-slate-400">Configure global Gemini API key here. All gym tenants with diet feature will automatically use this key.</p>
                            </div>
                        </div>

                        <div>
                            @if(!empty($settings['gemini_api_key']) || !empty(config('services.gemini.api_key')))
                                <span class="px-3 py-1 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>GEMINI ACTIVE</span>
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400 text-xs font-bold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    <span>KEY NOT CONFIGURED</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('admin.settings.ai') }}" method="POST" class="space-y-5 text-xs">
                        @csrf

                        <!-- Enable Toggle -->
                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-white mb-0.5">Enable Google Gemini AI Platform-Wide</h4>
                                <p class="text-[11px] text-slate-400">
                                    When enabled, all gym tenants with Diet &amp; Nutrition permissions will have full access to personalized AI diet generation.
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="gemini_enabled" value="1" {{ !empty($settings['gemini_enabled']) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <!-- Gemini API Key Input -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">
                                    Google Gemini API Key *
                                </label>
                                <button type="button" @click="showGeminiKey = !showGeminiKey" class="text-[11px] text-purple-400 hover:text-purple-300 font-medium cursor-pointer" x-text="showGeminiKey ? 'Hide Key' : 'Show Key'"></button>
                            </div>
                            <div class="relative">
                                <input :type="showGeminiKey ? 'text' : 'password'" 
                                       name="gemini_api_key" 
                                       x-model="geminiApiKey"
                                       value="{{ old('gemini_api_key', $settings['gemini_api_key'] ?? '') }}"
                                       placeholder="AIzaSy..." 
                                       class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs font-mono focus:border-purple-500 focus:outline-none pr-10">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                    🔑
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Get your free key from <a href="https://aistudio.google.com/" target="_blank" class="text-purple-400 hover:underline">Google AI Studio (aistudio.google.com)</a>.
                            </p>
                        </div>

                        <!-- Gemini Model Selector -->
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">
                                Preferred Gemini AI Model *
                            </label>
                            <select name="gemini_model" x-model="geminiModel" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-purple-500 focus:outline-none cursor-pointer">
                                <option value="gemini-2.5-flash" {{ ($settings['gemini_model'] ?? 'gemini-2.5-flash') === 'gemini-2.5-flash' ? 'selected' : '' }}>gemini-2.5-flash (Recommended: High-Speed &amp; Advanced Multimodal Reasoning)</option>
                                <option value="gemini-2.0-flash" {{ ($settings['gemini_model'] ?? '') === 'gemini-2.0-flash' ? 'selected' : '' }}>gemini-2.0-flash (Ultra Fast &amp; High Accuracy)</option>
                                <option value="gemini-2.5-pro" {{ ($settings['gemini_model'] ?? '') === 'gemini-2.5-pro' ? 'selected' : '' }}>gemini-2.5-pro (Deep Reasoning &amp; Clinical Metabolic Customization)</option>
                                <option value="gemini-3.5-flash" {{ ($settings['gemini_model'] ?? '') === 'gemini-3.5-flash' ? 'selected' : '' }}>gemini-3.5-flash (Next-Gen Intelligence &amp; Speed)</option>
                                <option value="gemini-3.8-flash" {{ ($settings['gemini_model'] ?? '') === 'gemini-3.8-flash' ? 'selected' : '' }}>gemini-3.8-flash (Advanced Next-Gen Performance)</option>
                                <option value="gemini-3.1-flash-lite" {{ ($settings['gemini_model'] ?? '') === 'gemini-3.1-flash-lite' ? 'selected' : '' }}>gemini-3.1-flash-lite (Ultra Lightweight &amp; Lowest Latency)</option>
                                <option value="gemini-2.0-flash-lite" {{ ($settings['gemini_model'] ?? '') === 'gemini-2.0-flash-lite' ? 'selected' : '' }}>gemini-2.0-flash-lite (Cost-Effective &amp; High RPM)</option>
                                <option value="gemini-1.5-flash" {{ ($settings['gemini_model'] ?? '') === 'gemini-1.5-flash' ? 'selected' : '' }}>gemini-1.5-flash (Standard Series)</option>
                                <option value="gemini-1.5-pro-latest" {{ ($settings['gemini_model'] ?? '') === 'gemini-1.5-pro-latest' ? 'selected' : '' }}>gemini-1.5-pro-latest (Legacy Pro Series)</option>
                            </select>
                        </div>

                        <!-- Test Status Banner -->
                        <div x-show="geminiTestStatus" x-cloak class="p-3.5 rounded-2xl border text-xs flex items-start gap-2.5"
                             :class="geminiTestStatus === 'success' ? 'bg-emerald-950/40 border-emerald-500/40 text-emerald-300' : 'bg-rose-950/40 border-rose-500/40 text-rose-300'">
                            <span class="text-base" x-text="geminiTestStatus === 'success' ? '✅' : '❌'"></span>
                            <div class="flex-1">
                                <p class="font-bold" x-text="geminiTestStatus === 'success' ? 'Connection Succeeded!' : 'Connection Failed'"></p>
                                <p class="mt-0.5 text-[11px] opacity-90" x-text="geminiTestMsg"></p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-slate-800">
                            <button type="button" 
                                    @click="testGemini()" 
                                    :disabled="testingGemini || !geminiApiKey"
                                    class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold transition-all flex items-center gap-2 cursor-pointer border border-slate-700">
                                <span x-show="testingGemini" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <span x-show="!testingGemini">⚡</span>
                                <span x-text="testingGemini ? 'Testing Connection...' : 'Test Gemini Connection'"></span>
                            </button>

                            <button type="submit" 
                                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 via-indigo-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold text-xs shadow-lg shadow-purple-600/30 transition-all cursor-pointer flex items-center gap-2">
                                <span>Save AI Settings</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Side Box Guide (1 Col) -->
                <div class="space-y-6">
                    <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-3.5">
                        <h4 class="text-xs font-extrabold text-white flex items-center gap-2">
                            <span>🔑</span> Free API Key in 4 Simple Steps
                        </h4>
                        <ol class="text-[11px] text-slate-300 space-y-2.5 pl-1 leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-purple-500/20 text-purple-400 font-bold text-[10px] flex items-center justify-center shrink-0">1</span>
                                <span>Open <a href="https://aistudio.google.com/" target="_blank" class="text-purple-400 font-bold hover:underline">aistudio.google.com</a></span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-purple-500/20 text-purple-400 font-bold text-[10px] flex items-center justify-center shrink-0">2</span>
                                <span>Sign in with your Google Account</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-purple-500/20 text-purple-400 font-bold text-[10px] flex items-center justify-center shrink-0">3</span>
                                <span>Click <strong>"Get API Key"</strong> &rarr; <strong>"Create API Key"</strong></span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-purple-500/20 text-purple-400 font-bold text-[10px] flex items-center justify-center shrink-0">4</span>
                                <span>Paste the key here and click <strong>Save AI Settings</strong></span>
                            </li>
                        </ol>
                    </div>

                    <div class="p-5 rounded-3xl bg-slate-900/70 border border-slate-800/80 space-y-3">
                        <h4 class="text-xs font-extrabold text-white flex items-center gap-2">
                            <span>🏢</span> Multi-Tenant Advantage
                        </h4>
                        <div class="text-[11px] text-slate-400 space-y-2 leading-relaxed">
                            <p class="flex items-start gap-2">
                                <span class="text-purple-400 font-bold">✓ Centralized:</span>
                                <span>Super Admin configures the Gemini API key once, and all gym owners across the platform get AI Diet features automatically.</span>
                            </p>
                            <p class="flex items-start gap-2">
                                <span class="text-indigo-400 font-bold">✓ Age &amp; Multi-Factor:</span>
                                <span>Gemini adapts macro ratios, BMR/TDEE, pre/post workout timing, and Indian diets based on age, weight, and health conditions.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

