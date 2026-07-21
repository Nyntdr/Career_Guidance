<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-violet-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 px-4 py-8"
    >
        <div class="mb-6 text-center">
            <Link href="/" class="inline-flex flex-col items-center gap-3">
                <ApplicationLogo class="h-16 w-16" />
                <span class="text-xl font-bold text-gray-800 dark:text-gray-100">CareerGuide AI</span>
            </Link>
        </div>

        <div
            class="w-full max-w-md overflow-hidden rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 px-8 py-8 shadow-xl shadow-indigo-100/50 dark:shadow-gray-900/50"
        >
            <slot />
        </div>
    </div>
</template>
