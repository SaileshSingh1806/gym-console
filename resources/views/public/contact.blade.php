<x-guest-layout title="Contact Support - Gym Console SaaS">
    <div class="py-20 max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800">
            <h1 class="text-2xl font-bold text-white mb-2">Get in Touch</h1>
            <p class="text-sm text-slate-400 mb-6">Have questions regarding enterprise deployment or Hikvision IoT hardware setup?</p>

            <form action="#" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Your Name</label>
                    <input type="text" required class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Business Email</label>
                    <input type="email" required class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Message</label>
                    <textarea rows="4" required class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none"></textarea>
                </div>
                <button type="submit" class="w-full py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold transition-colors">
                    Send Inquiry
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>

