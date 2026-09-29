<script setup>
import { computed } from 'vue';

const props = defineProps({
    form: {
        type: Object,
        default: null,
    },
    errors: {
        type: [Object, Array],
        default: null,
    },
});

const errorList = computed(() => {
    if (props.form && props.form.errors) {
        return Object.entries(props.form.errors).map(([field, message]) => ({
            field,
            message,
        }));
    }
    if (Array.isArray(props.errors)) {
        return props.errors;
    }
    if (props.errors && typeof props.errors === 'object') {
        return Object.entries(props.errors).map(([field, message]) => ({
            field,
            message,
        }));
    }
    return [];
});

function scrollToField(field) {
    const el = document.querySelector(
        `[data-error="${field}"], [name="${field}"], #${field}`
    );
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (typeof el.focus === 'function') {
            el.focus();
        }
    }
}
</script>

<template>
    <div
        v-if="errorList.length"
        role="alert"
        aria-live="assertive"
        class="rounded-xl border border-red-200 dark:border-red-800/80 bg-red-50 dark:bg-red-950/40 p-4 mb-6 shadow-sm"
    >
        <div class="flex items-start gap-3">
            <svg class="h-5 w-5 text-red-500 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="space-y-1">
                <h4 class="text-sm font-semibold text-red-800 dark:text-red-200">
                    {{ errorList.length }} {{ errorList.length === 1 ? 'problem' : 'problems' }} found with your submission:
                </h4>
                <ul class="list-disc list-inside text-xs text-red-700 dark:text-red-300 space-y-0.5">
                    <li v-for="err in errorList" :key="err.field">
                        <button
                            type="button"
                            @click="scrollToField(err.field)"
                            class="underline hover:text-red-900 dark:hover:text-red-100 font-medium focus:outline-none focus:ring-1 focus:ring-red-500 rounded"
                        >
                            {{ err.message }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
