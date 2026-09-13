<div x-data="confirmModalComponent()" 
     @keydown.escape.window="open = false" 
     style="display: contents;">
    <div x-show="open" 
         x-cloak
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[99999] overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl space-y-5 text-center transform transition-all"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.away="open = false">
            
            <!-- Glowing Status Icon -->
            <div x-show="type === 'danger'" class="w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-400 flex items-center justify-center mx-auto shadow-lg shadow-rose-500/10">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div x-show="type === 'warning'" class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/25 text-amber-400 flex items-center justify-center mx-auto shadow-lg shadow-amber-500/10">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div x-show="type === 'primary'" class="w-14 h-14 rounded-2xl bg-indigo-500/10 border border-indigo-500/25 text-indigo-400 flex items-center justify-center mx-auto shadow-lg shadow-indigo-500/10">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>

            <!-- Title & Message -->
            <div class="space-y-2">
                <h3 class="text-base font-black text-white tracking-tight" x-text="title"></h3>
                <p class="text-xs text-slate-400 leading-relaxed" x-text="message"></p>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-center gap-3 pt-2">
                <button type="button" @click="open = false" class="flex-1 py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition-all cursor-pointer">
                    <span x-text="cancelText"></span>
                </button>

                <button type="button" @click="confirm()" 
                        :class="type === 'danger' ? 'bg-rose-600 hover:bg-rose-500 shadow-rose-600/25' : (type === 'warning' ? 'bg-amber-600 hover:bg-amber-500 shadow-amber-600/25' : 'bg-indigo-600 hover:bg-indigo-500 shadow-indigo-600/25')"
                        class="flex-1 py-2.5 px-4 rounded-xl text-white font-black text-xs shadow-lg transition-all cursor-pointer">
                    <span x-text="confirmText"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmModalComponent() {
    return {
        open: false,
        title: 'Confirm Action',
        message: 'Are you sure you want to proceed?',
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel',
        type: 'danger',
        targetForm: null,
        callback: null,
        
        init() {
            window.openUiConfirmModal = (options) => {
                this.title = options.title || (options.type === 'warning' ? 'Warning' : 'Confirm Deletion');
                this.message = options.message || 'Are you sure you want to perform this action?';
                this.confirmText = options.confirmText || (options.type === 'danger' ? 'Yes, Delete' : 'Confirm');
                this.cancelText = options.cancelText || 'Cancel';
                this.type = options.type || 'danger';
                this.targetForm = options.form || null;
                this.callback = options.onConfirm || null;
                this.open = true;
            };

            window.confirmAction = (event, message, title = 'Confirm Deletion', confirmText = 'Yes, Delete', type = 'danger') => {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                const form = event && event.target ? (event.target.tagName === 'FORM' ? event.target : event.target.closest('form')) : null;
                window.openUiConfirmModal({
                    title: title,
                    message: message,
                    confirmText: confirmText,
                    type: type,
                    onConfirm: () => {
                        if (form) {
                            form.dataset.confirmed = 'true';
                            form.dataset.submitting = 'true';
                            form.submit();
                        }
                    }
                });
                return false;
            };
        },
        confirm() {
            this.open = false;
            if (typeof this.callback === 'function') {
                this.callback();
            } else if (this.targetForm) {
                if (this.targetForm.dataset.submitting === 'true') return;
                this.targetForm.dataset.confirmed = 'true';
                this.targetForm.dataset.submitting = 'true';
                this.targetForm.submit();
            }
        }
    };
}

// Global Form Handling & Double Submit Protection
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (!form || form.tagName !== 'FORM') return;
    
    const confirmMsg = form.getAttribute('data-confirm') || form.dataset.confirm;
    if (confirmMsg && form.dataset.confirmed !== 'true') {
        e.preventDefault();
        e.stopImmediatePropagation();
        if (typeof window.openUiConfirmModal === 'function') {
            window.openUiConfirmModal({
                title: form.getAttribute('data-confirm-title') || 'Confirm Deletion',
                message: confirmMsg,
                confirmText: form.getAttribute('data-confirm-btn') || 'Yes, Delete',
                cancelText: form.getAttribute('data-cancel-btn') || 'Cancel',
                type: form.getAttribute('data-confirm-type') || 'danger',
                onConfirm: () => {
                    form.dataset.confirmed = 'true';
                    form.dataset.submitting = 'true';
                    form.submit();
                }
            });
        }
        return false;
    }

    if (form.dataset.submitting === 'true') {
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
    }

    form.dataset.submitting = 'true';

    const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    submitBtns.forEach(function(btn) {
        btn.disabled = true;
        btn.classList.add('opacity-60', 'cursor-not-allowed', 'pointer-events-none');
        const span = btn.querySelector('span');
        if (span && !span.dataset.originalText) {
            span.dataset.originalText = span.innerText;
            span.innerText = 'Processing...';
        }
    });
}, true);
</script>
