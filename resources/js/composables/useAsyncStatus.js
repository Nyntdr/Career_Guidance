import { ref } from 'vue';

export function useAsyncStatus() {
    const visible = ref(false);
    const label = ref('Working');
    const messages = ref([]);
    const role = ref('');
    const isRunning = ref(false);

    function dismiss() {
        visible.value = false;
    }

    async function run(fn, opts = {}) {
        if (isRunning.value) return;

        isRunning.value = true;
        label.value = opts.label || 'Working';
        messages.value = opts.messages || [];
        role.value = opts.role || '';
        visible.value = true;

        const startTime = Date.now();

        try {
            return await fn();
        } finally {
            const elapsedMs = Date.now() - startTime;
            const remainingMs = Math.max(0, 400 - elapsedMs);

            if (remainingMs > 0) {
                await new Promise((resolve) => setTimeout(resolve, remainingMs));
            }

            visible.value = false;
            isRunning.value = false;
        }
    }

    return {
        visible,
        label,
        messages,
        role,
        isRunning,
        run,
        dismiss,
    };
}
