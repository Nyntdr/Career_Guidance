<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    resources: Object,
    types: Array,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const type = ref(props.filters.type ?? '');

const typeColors = {
    course: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
    article: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    video: 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
    book: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    certification: 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
    other: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
};

watch(search, (val) => {
    router.get(route('learning.index'), { search: val || '', type: type.value || '' }, { preserveState: true, replace: true });
});

watch(type, (val) => {
    router.get(route('learning.index'), { type: val || '', search: search.value || '' }, { preserveState: true, replace: true });
});

function formatCost(cost) {
    if (cost === null || cost === undefined) return 'Free';
    const n = Number(cost);
    return n === 0 ? 'Free' : `$${n.toLocaleString()}`;
}
</script>

<template>
    <Head title="Learning" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    Learning
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Courses, articles, books and more to build your skills
                </p>
            </div>
        </template>

        <div class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <TextInput v-model="search" type="search" placeholder="Search resources..." class="w-full max-w-md" />

                    <div v-if="types.length" class="flex flex-wrap gap-2">
                        <button
                            @click="type = ''"
                            class="rounded-full px-3 py-1.5 text-xs font-medium transition-colors"
                            :class="type === '' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        >
                            All
                        </button>
                        <button
                            v-for="t in types"
                            :key="t"
                            @click="type = type === t ? '' : t"
                            class="rounded-full px-3 py-1.5 text-xs font-medium capitalize transition-colors"
                            :class="type === t ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        >
                            {{ t }}
                        </button>
                    </div>
                </div>

                <div v-if="resources.data.length" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="resource in resources.data"
                        :key="resource.id"
                        class="flex flex-col rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <span
                                class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                :class="typeColors[resource.resource_type] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                            >
                                {{ resource.resource_type }}
                            </span>
                            <span class="shrink-0 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 px-2.5 py-0.5 text-xs font-medium">
                                {{ formatCost(resource.cost) }}
                            </span>
                        </div>

                        <h3 class="mt-3 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ resource.title }}
                        </h3>
                        <p v-if="resource.provider" class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ resource.provider }}</p>

                        <p v-if="resource.duration_hours" class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                            {{ resource.duration_hours }} hrs
                        </p>

                        <div v-if="resource.skills?.length" class="mt-4 flex flex-wrap gap-1.5">
                            <span
                                v-for="skill in resource.skills"
                                :key="skill.id"
                                class="rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 px-2 py-0.5 text-xs"
                            >
                                {{ skill.name }}
                            </span>
                        </div>

                        <div class="mt-auto pt-5">
                            <a
                                v-if="resource.url"
                                :href="resource.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors"
                            >
                                Open resource
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                            <span v-else class="text-sm text-gray-400">No external link</span>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No learning resources found.</p>
                </div>

                <div v-if="resources.links && resources.links.length > 3" class="mt-6 flex items-center justify-between">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Showing {{ resources.from }} to {{ resources.to }} of {{ resources.total }} results</p>
                    <div class="flex gap-1">
                        <Link v-for="link in resources.links" :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="{ 'bg-indigo-600 text-white': link.active, 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700': !link.active, 'opacity-50 cursor-not-allowed': !link.url }"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>