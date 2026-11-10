<x-app-layout header="Gym Feedback & Suggestions">
    <div x-data="{
        category: 'suggestion',
        rating: 5,
        comments: '',
        is_anonymous: false,
        submitting: false,
        setRating(r) { this.rating = r; }
    }" class="max-w-2xl mx-auto space-y-5 pb-20 pt-2">

        <!-- Top Header Card -->
        <div class="p-5 sm:p-6 rounded-3xl bg-[#171A22] border border-neutral-800 shadow-xl text-white space-y-2">
            <div class="flex items-center gap-2">
                <span class="text-2xl">💬</span>
            </div>
            <h1 class="text-lg sm:text-xl font-black">Gym Feedback &amp; Suggestions</h1>
            <p class="text-xs text-slate-300 leading-relaxed">
                Share your suggestions, report equipment issues, or give general feedback for <span class="font-bold text-[#C5A880]">{{ $tenant->name }}</span>.
            </p>
        </div>

        <!-- Feedback Submission Card -->
        <div class="p-5 sm:p-6 rounded-3xl bg-[#171A22] border border-neutral-800 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <span>📝</span>
                <span>Share Your Thoughts</span>
            </h2>

            <form action="{{ route('app.feedback.store') }}" method="POST" @submit="submitting = true" class="space-y-4">
                @csrf

                <!-- Category Selector Chips -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-2">Category *</label>
                    <input type="hidden" name="category" :value="category">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <button type="button" 
                                @click="category = 'suggestion'" 
                                :class="category === 'suggestion' ? 'bg-[#C5A880] text-neutral-950 font-black border-[#C5A880] shadow-xs' : 'bg-[#0F1115]/70 text-slate-300 border-neutral-800'"
                                class="p-2.5 rounded-xl border text-xs text-left transition-all flex items-center gap-2 cursor-pointer">
                            <span>💡</span>
                            <span>Suggestion</span>
                        </button>
                        <button type="button" 
                                @click="category = 'equipment'" 
                                :class="category === 'equipment' ? 'bg-[#C5A880] text-neutral-950 font-black border-[#C5A880] shadow-xs' : 'bg-[#0F1115]/70 text-slate-300 border-neutral-800'"
                                class="p-2.5 rounded-xl border text-xs text-left transition-all flex items-center gap-2 cursor-pointer">
                            <span>🏋️</span>
                            <span>Equipment</span>
                        </button>
                        <button type="button" 
                                @click="category = 'hygiene'" 
                                :class="category === 'hygiene' ? 'bg-[#C5A880] text-neutral-950 font-black border-[#C5A880] shadow-xs' : 'bg-[#0F1115]/70 text-slate-300 border-neutral-800'"
                                class="p-2.5 rounded-xl border text-xs text-left transition-all flex items-center gap-2 cursor-pointer">
                            <span>🧹</span>
                            <span>Cleanliness</span>
                        </button>
                        <button type="button" 
                                @click="category = 'trainer'" 
                                :class="category === 'trainer' ? 'bg-[#C5A880] text-neutral-950 font-black border-[#C5A880] shadow-xs' : 'bg-[#0F1115]/70 text-slate-300 border-neutral-800'"
                                class="p-2.5 rounded-xl border text-xs text-left transition-all flex items-center gap-2 cursor-pointer">
                            <span>👨‍🏫</span>
                            <span>Trainer / Staff</span>
                        </button>
                        <button type="button" 
                                @click="category = 'complaint'" 
                                :class="category === 'complaint' ? 'bg-[#C5A880] text-neutral-950 font-black border-[#C5A880] shadow-xs' : 'bg-[#0F1115]/70 text-slate-300 border-neutral-800'"
                                class="p-2.5 rounded-xl border text-xs text-left transition-all flex items-center gap-2 cursor-pointer">
                            <span>⚠️</span>
                            <span>Complaint</span>
                        </button>
                        <button type="button" 
                                @click="category = 'general'" 
                                :class="category === 'general' ? 'bg-[#C5A880] text-neutral-950 font-black border-[#C5A880] shadow-xs' : 'bg-[#0F1115]/70 text-slate-300 border-neutral-800'"
                                class="p-2.5 rounded-xl border text-xs text-left transition-all flex items-center gap-2 cursor-pointer">
                            <span>💬</span>
                            <span>General / Other</span>
                        </button>
                    </div>
                </div>

                <!-- Rating Stars -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">How is your gym experience? (Optional)</label>
                    <input type="hidden" name="rating" :value="rating">
                    <div class="flex items-center gap-2">
                        <template x-for="r in [1, 2, 3, 4, 5]" :key="r">
                            <button type="button" 
                                    @click="setRating(r)" 
                                    class="text-2xl transition-transform hover:scale-125 cursor-pointer"
                                    :class="r <= rating ? 'text-[#C5A880]' : 'text-slate-700'">
                                ★
                            </button>
                        </template>
                        <span class="text-xs font-black text-[#C5A880] ml-2" x-text="rating + ' / 5 Stars'"></span>
                    </div>
                </div>

                <!-- Feedback Text -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Your Feedback / Suggestion *</label>
                    <textarea name="comments" x-model="comments" required rows="4" 
                              placeholder="Describe your feedback, suggestion, or issue in detail..." 
                              class="w-full px-3.5 py-2.5 rounded-2xl bg-[#0F1115]/70 border border-neutral-800 text-xs sm:text-sm text-white focus:outline-none focus:border-[#C5A880] resize-none leading-relaxed"></textarea>
                </div>

                <!-- Anonymous Toggle -->
                <div class="p-3 rounded-2xl bg-[#0F1115]/70 border border-neutral-800 flex items-center justify-between gap-3">
                    <div class="space-y-0.5">
                        <span class="text-xs font-bold text-slate-200 block">Submit Anonymously</span>
                        <span class="text-[11px] text-slate-400 block">Hide your name and phone number from gym management</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" name="is_anonymous" value="1" x-model="is_anonymous" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C5A880]"></div>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        :disabled="submitting || !comments.trim()" 
                        class="w-full py-3.5 rounded-2xl bg-[#C5A880] hover:bg-[#b5966d] active:scale-[0.98] text-neutral-950 font-black text-xs sm:text-sm transition-all cursor-pointer flex items-center justify-center gap-2 shadow-lg shadow-[#C5A880]/25 disabled:opacity-50">
                    <span x-text="submitting ? 'Submitting...' : 'Submit Feedback'"></span>
                </button>
            </form>
        </div>

        <!-- My Feedback History -->
        <div class="space-y-3">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">Your Submitted Feedback History</h3>

            @if($myFeedbacks->isEmpty())
                <div class="p-6 rounded-2xl bg-[#171A22] border border-neutral-800 text-center text-xs text-slate-400">
                    You haven't submitted any feedback yet. Your feedback helps make the gym better for everyone!
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach($myFeedbacks as $item)
                        @php
                            $statusBadge = match($item->status) {
                                'new', 'pending', 'open' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                                'under_review'           => 'bg-blue-500/15 text-blue-400 border-blue-500/30',
                                'action_taken'           => 'bg-indigo-500/15 text-indigo-400 border-indigo-500/30',
                                'resolved', 'closed'     => 'bg-[#C5A880]/15 text-[#C5A880] border-[#C5A880]/30',
                                default                  => 'bg-slate-800 text-slate-300 border-slate-700',
                            };
                        @endphp
                        <div class="p-4 rounded-2xl bg-[#171A22] border border-neutral-800 shadow-xs space-y-2">
                            <div class="flex items-center justify-between gap-2 flex-wrap text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-white capitalize">{{ $item->category }}</span>
                                    @if($item->rating)
                                        <span class="text-[#C5A880] font-bold">{{ $item->rating }} ★</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border uppercase {{ $statusBadge }}">
                                        {{ str_replace('_', ' ', $item->status) }}
                                    </span>
                                    <span class="text-[11px] text-slate-400">{{ $item->created_at->format('d M Y') }}</span>
                                </div>
                            </div>

                            <p class="text-xs text-slate-300 italic">
                                &ldquo;{{ $item->comments }}&rdquo;
                            </p>

                            @if(!empty($item->admin_notes))
                                <div class="p-2.5 rounded-xl bg-[#C5A880]/10 border border-[#C5A880]/25 text-xs text-[#C5A880] space-y-0.5">
                                    <span class="font-bold block">✓ Message from Gym Management:</span>
                                    <p class="text-[11px] text-slate-200 pl-3">{{ $item->admin_notes }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($myFeedbacks->hasPages())
                    <div class="pt-2">
                        {{ $myFeedbacks->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</x-app-layout>

