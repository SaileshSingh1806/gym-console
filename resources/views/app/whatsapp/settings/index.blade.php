<x-app-layout header="">
    <div class="space-y-6">
        <!-- Alerts -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 text-sm cursor-pointer">✕</button>
            </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <span>WhatsApp Cloud API Configuration</span>
                    @if($isConfigured)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">Configured ✓</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20">Setup Pending</span>
                    @endif
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Configure official Meta Graph API credentials for direct sending without 3rd party subscription fees.</p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="testConnection()" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors flex items-center gap-2 cursor-pointer shadow-sm">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Test API Connection</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Settings Form (2 Cols) -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <form action="{{ route('app.whatsapp.settings.update') }}" method="POST" class="space-y-5 text-xs">
                    @csrf

                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider mb-1">Meta Business Account Credentials</h3>
                        <p class="text-slate-500 text-[11px]">Obtained from your Meta Developers Portal app dashboard (WhatsApp product settings).</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">WhatsApp Business Account ID (WABA ID)</label>
                            <input type="text" name="waba_id" value="{{ $credentials['waba_id'] }}" placeholder="e.g. 102938475610293" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Phone Number ID *</label>
                            <input type="text" name="phone_number_id" value="{{ $credentials['phone_number_id'] }}" required placeholder="e.g. 546372819203948" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">System User Permanent Access Token *</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="access_token" value="{{ $credentials['access_token'] }}" placeholder="EAA..." class="w-full px-3 py-2 pr-10 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                            <button type="button" @click="show = !show" class="absolute right-3 top-2 text-slate-400 hover:text-slate-600 text-xs cursor-pointer" x-text="show ? 'Hide' : 'Show'"></button>
                        </div>
                        <span class="text-[10px] text-slate-400 mt-1 block">Generate a permanent System User Token with <code>whatsapp_business_messaging</code> and <code>whatsapp_business_management</code> permissions.</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Meta App Secret (for Webhook Signature Validation)</label>
                            <div class="relative" x-data="{ showSecret: false }">
                                <input :type="showSecret ? 'text' : 'password'" name="app_secret" value="{{ $credentials['app_secret'] }}" placeholder="App Secret from Meta Basic Settings" class="w-full px-3 py-2 pr-10 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                                <button type="button" @click="showSecret = !showSecret" class="absolute right-3 top-2 text-slate-400 hover:text-slate-600 text-xs cursor-pointer" x-text="showSecret ? 'Hide' : 'Show'"></button>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Used to verify X-Hub-Signature-256 on incoming webhook events.</span>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Graph API Version</label>
                            <input type="text" name="graph_version" value="{{ $credentials['graph_version'] }}" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider mb-1">Meta Conversation Rates (INR ₹)</h3>
                        <p class="text-slate-500 text-[11px] mb-3">Estimated rates per 24-hr customer conversation window charged directly by Meta.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Marketing Conversation Rate (₹)</label>
                                <input type="number" step="0.01" name="marketing_rate" value="{{ $credentials['marketing_rate'] }}" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Utility Conversation Rate (₹)</label>
                                <input type="number" step="0.01" name="utility_rate" value="{{ $credentials['utility_rate'] }}" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                            Save WhatsApp Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Webhook Setup & Ngrok Instructions (1 Col) -->
            <div class="space-y-6">
                <!-- Webhook Credentials Card -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Meta Webhook Configuration</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Configure these exact values in your Meta Developer App under <strong>WhatsApp &rarr; Configuration &rarr; Webhook</strong>:</p>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Callback URL</label>
                            <div class="flex items-center gap-1.5">
                                <input type="text" readonly id="webhook_url_input" value="{{ $webhookUrl }}" class="flex-1 px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[11px] text-slate-800 dark:text-white select-all">
                                <button type="button" onclick="copyWebhookUrl()" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs cursor-pointer">
                                    Copy
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Verify Token</label>
                            <input type="text" readonly value="{{ $credentials['webhook_verify_token'] }}" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[11px] text-slate-800 dark:text-white select-all">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Webhook Subscription Fields</label>
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 font-mono text-[11px] text-emerald-600 dark:text-emerald-400 font-bold">
                                messages
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Check the <code>messages</code> box in the webhook subscription field list.</span>
                        </div>
                    </div>
                </div>

                <!-- Local Testing with Ngrok Card -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <span>Localhost Testing via ngrok</span>
                        <span class="px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-500 text-[10px] font-black uppercase">Local Dev</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Since Meta webhooks require a public HTTPS URL, use ngrok for local development:</p>

                    <div class="p-3 rounded-xl bg-slate-950 text-emerald-400 font-mono text-[11px] space-y-1">
                        <div># 1. Start your tunnel:</div>
                        <div class="text-white font-bold">ngrok http 8000</div>
                    </div>

                    <p class="text-xs text-slate-600 dark:text-slate-400">Then in Meta Developer Portal, paste your ngrok forwarding URL:</p>
                    <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800 font-mono text-[11px] text-slate-700 dark:text-slate-300 break-all">
                        https://xxxx.ngrok-free.app/api/whatsapp/webhook
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script for Live Test Connection & URL Copying -->
    <script>
        function copyWebhookUrl() {
            const input = document.getElementById('webhook_url_input');
            input.select();
            document.execCommand('copy');
            alert('Webhook URL copied to clipboard: ' + input.value);
        }

        function testConnection() {
            fetch("{{ route('app.whatsapp.settings.test-connection') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Meta API Success!\n\n' + data.message + '\n\nVerified Name: ' + (data.data?.verified_name || 'N/A') + '\nPhone: ' + (data.data?.display_phone_number || 'N/A') + '\nQuality: ' + (data.data?.quality_rating || 'N/A'));
                } else {
                    alert('❌ Connection Test Failed:\n\n' + data.message);
                }
            })
            .catch(err => {
                alert('Request failed: ' + err.message);
            });
        }
    </script>
</x-app-layout>

