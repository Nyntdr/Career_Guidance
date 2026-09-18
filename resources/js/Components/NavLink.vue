<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out'
        : 'group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes" :aria-current="active ? 'page' : undefined">
        <slot name="icon" />
        <slot />
    </Link>
</template>