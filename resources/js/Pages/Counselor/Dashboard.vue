<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    candidates: Array,
});
</script>

<template>
    <Head title="Counselor Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    Counselor Dashboard
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Candidates under your guidance
                </p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Candidates</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Newest registered candidates</p>

                    <div v-if="candidates.length" class="mt-5 space-y-3">
                        <div
                            v-for="c in candidates"
                            :key="c.id"
                            class="flex items-center justify-between rounded-lg border border-gray-100 dark:border-gray-700 p-4"
                        >
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ c.name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ c.email }}</p>
                            </div>
                            <div class="text-right text-sm">
                                <p class="text-gray-500 dark:text-gray-400">{{ c.skillsCount }} skills</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ c.joined }}</p>
                            </div>
                        </div>
                    </div>

                    <p v-else class="mt-5 text-sm text-gray-400 dark:text-gray-500">No candidates registered yet.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
