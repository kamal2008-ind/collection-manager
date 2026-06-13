<div
    x-data="{
        show: false,
        message: '',
        type: 'success',
        progress: 100,
        timer: null,
        interval: null,
        duration: 3000,

        showToast(event) {
            this.message = event.detail.message ?? 'Done.';
            this.type = event.detail.type ?? 'success';
            this.duration = event.detail.duration ?? 3000;
            this.progress = 100;
            this.show = true;

            clearTimeout(this.timer);
            clearInterval(this.interval);

            const stepTime = 50;
            const step = 100 / (this.duration / stepTime);

            this.interval = setInterval(() => {
                this.progress = Math.max(0, this.progress - step);
            }, stepTime);

            this.timer = setTimeout(() => {
                this.closeToast();
            }, this.duration);
        },

        closeToast() {
            this.show = false;
            clearTimeout(this.timer);
            clearInterval(this.interval);
        }
    }"
    x-on:toast.window="showToast($event)"
    x-show="show"
    x-transition.opacity.duration.200ms
    x-cloak
    class="fixed right-6 top-6 z-[9999] w-full max-w-xs"
>
    <div
        class="overflow-hidden rounded-xl border bg-white shadow-lg"
        :class="{
            'border-green-200': type === 'success',
            'border-red-200': type === 'error',
            'border-yellow-200': type === 'warning',
            'border-blue-200': type === 'info',
        }"
    >
        <div class="flex items-start gap-3 px-4 py-3">
            <div class="pt-0.5 text-lg">
                <span x-show="type === 'success'">✅</span>
                <span x-show="type === 'error'">❌</span>
                <span x-show="type === 'warning'">⚠️</span>
                <span x-show="type === 'info'">ℹ️</span>
            </div>

            <div class="min-w-0 flex-1">
                <p
                    class="text-sm font-medium text-gray-800"
                    x-text="message"
                ></p>
            </div>

            <button
                type="button"
                class="text-lg leading-none text-gray-400 hover:text-gray-700"
                @click="closeToast"
                title="Close"
            >
                ×
            </button>
        </div>

        {{-- Timeline --}}
        <div class="h-1 w-full bg-gray-100">
            <div
                class="h-full transition-all duration-75"
                :class="{
                    'bg-green-500': type === 'success',
                    'bg-red-500': type === 'error',
                    'bg-yellow-500': type === 'warning',
                    'bg-blue-500': type === 'info',
                }"
                :style="`width: ${progress}%`"
            ></div>
        </div>
    </div>
</div>
