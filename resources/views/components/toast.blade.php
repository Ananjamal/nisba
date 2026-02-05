<div x-data="toastManager()" @toast.window="show($event.detail)" class="fixed top-6 left-1/2 -translate-x-1/2 z-[9999] pointer-events-none">
    <template x-for="(toast, index) in toasts" :key="toast.id">
        <div x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
            class="mb-3 pointer-events-auto">

            <div class="bg-white rounded-[1.5rem] shadow-elevated border px-6 py-4 flex items-center gap-4 min-w-[320px] max-w-md"
                :class="{
                    'border-green-200': toast.type === 'success',
                    'border-red-200': toast.type === 'error',
                    'border-yellow-200': toast.type === 'warning',
                    'border-primary-200': toast.type === 'info'
                }">

                <!-- Icon -->
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0"
                    :class="{
                        'bg-green-100 text-green-600': toast.type === 'success',
                        'bg-red-100 text-red-600': toast.type === 'error',
                        'bg-yellow-100 text-yellow-600': toast.type === 'warning',
                        'bg-primary-100 text-primary-600': toast.type === 'info'
                    }">
                    <!-- Success Icon -->
                    <svg x-show="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <!-- Error Icon -->
                    <svg x-show="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <!-- Warning Icon -->
                    <svg x-show="toast.type === 'warning'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <!-- Info Icon -->
                    <svg x-show="toast.type === 'info'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <!-- Message -->
                <p class="flex-1 text-sm font-bold text-slate-900" x-text="toast.message"></p>

                <!-- Close Button -->
                <button @click="removeToast(toast.id)"
                    class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>

<script>
    function toastManager() {
        return {
            toasts: [],
            nextId: 1,

            show(detail) {
                const id = this.nextId++;
                const toast = {
                    id,
                    type: detail.type || 'info',
                    message: detail.message,
                    visible: false
                };

                this.toasts.push(toast);

                // Show toast with slight delay for animation
                setTimeout(() => {
                    const toastIndex = this.toasts.findIndex(t => t.id === id);
                    if (toastIndex !== -1) {
                        this.toasts[toastIndex].visible = true;
                    }
                }, 10);

                // Auto remove after 3 seconds
                setTimeout(() => {
                    this.removeToast(id);
                }, 3000);
            },

            removeToast(id) {
                const toastIndex = this.toasts.findIndex(t => t.id === id);
                if (toastIndex !== -1) {
                    this.toasts[toastIndex].visible = false;
                    // Remove from array after animation completes
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 300);
                }
            }
        };
    }
</script>