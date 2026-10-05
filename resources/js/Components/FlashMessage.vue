<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const toasts = ref([]);

let counter = 0;

const styles = {
    success: {
        bar: 'bg-emerald-500',
        icon: 'text-emerald-500',
        ring: 'ring-emerald-200 dark:ring-emerald-900',
    },
    error: {
        bar: 'bg-red-500',
        icon: 'text-red-500',
        ring: 'ring-red-200 dark:ring-red-900',
    },
    warning: {
        bar: 'bg-amber-500',
        icon: 'text-amber-500',
        ring: 'ring-amber-200 dark:ring-amber-900',
    },
    info: {
        bar: 'bg-indigo-500',
        icon: 'text-indigo-500',
        ring: 'ring-indigo-200 dark:ring-indigo-900',
    },
};

const flash = computed(() => usePage().props.flash ?? {});

const icons = {
    success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
    warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};

function push(type, message) {
    const id = ++counter;
    toasts.value.push({ id, type, message });

    setTimeout(() => dismiss(id), 6000);
}

function dismiss(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id);
}

watch(
    flash,
    (value) => {
        const order = ['success', 'error', 'warning', 'info'];
        for (const type of order) {
            const message = value[type];
            if (message) {
                push(type, message);
            }
        }
    },
    { immediate: true },
);
</script>

<template>
    <div
        aria-live="polite"
        aria-atomic="false"
        class="pointer-events-none fixed right-4 top-4 z-[60] flex w-full max-w-sm flex-col gap-3"
    >
        <TransitionGroup
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0 translate-x-4"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100 translate-x-0"
            leave-to-class="opacity-0 translate-x-4"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto relative flex items-start gap-3 overflow-hidden rounded-xl bg-white dark:bg-gray-800 p-4 shadow-lg ring-1"
                :class="styles[toast.type].ring"
                role="status"
            >

                <svg class="mt-0.5 h-5 w-5 shrink-0" :class="styles[toast.type].icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons[toast.type]" />
                </svg>
                <p class="flex-1 text-sm text-gray-700 dark:text-gray-200">{{ toast.message }}</p>
                <button
                    type="button"
                    class="shrink-0 rounded-md p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                    :aria-label="`Dismiss notification`"
                    @click="dismiss(toast.id)"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>