<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    recommendations: Array,
    stats: Object,
});

const generatingId = ref(null);

function scoreColor(score) {
    if (score >= 70) return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
    if (score >= 40) return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
    return 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
}

function regenerate() {
    router.post(route('recommendations.generate'), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {},
    });
}

function explain(id) {
    generatingId.value = id;
    router.post(route('recommendations.rationale', id), {}, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            generatingId.value = null;
        },
    });
}

function formatGenerated(value) {
    if (!value) return 'Not generated yet';
    return new Date(value).toLocaleString();
}
</script>

<template>
    <Head title="Career Recommendations" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                        Career Recommendations
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Personalized roles ranked for your skills and career goals
                    </p>
                </div>
                <button
                    type="button"
                    @click="regenerate"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Regenerate
                </button>
            </div>
        </template>

        <div class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-4">
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Skills assessed</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.skillsAssessed }}</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Recommendations</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.recommendations }}</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Best match</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.topMatch }}%</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Last generated</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ formatGenerated(stats.generatedAt) }}</p>
                    </div>
                </div>

                <div v-if="stats.skillsAssessed === 0" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add skills to unlock personalized recommendations</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">We rank career roles by your current skill set and career goals.</p>
                    <Link :href="route('profile.edit')" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                        Update my profile
                    </Link>
                </div>

                <div v-else-if="recommendations.length === 0" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">No recommendations yet</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Run a scan to match your skills against available career roles.</p>
                    <button
                        type="button"
                        @click="regenerate"
                        class="mt-5 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
                    >
                        Generate recommendations
                    </button>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="rec in recommendations"
                        :key="rec.id"
                        class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ rec.title }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ rec.category }}
                                    <span v-if="rec.salary_range !== 'Not specified'"> · {{ rec.salary_range }}</span>
                                </p>
                            </div>
                            <span
                                class="shrink-0 rounded-full px-3 py-1 text-sm font-bold"
                                :class="scoreColor(rec.match_score)"
                            >
                                {{ rec.match_score }}% match
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span v-if="rec.growth_outlook" class="rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 px-2.5 py-0.5">
                                {{ rec.growth_outlook }}
                            </span>
                        </div>

                        <div v-if="rec.rationale" class="mt-4 rounded-xl bg-indigo-50 dark:bg-gray-700/40 p-4 text-sm text-gray-700 dark:text-gray-300">
                            <p>{{ rec.rationale }}</p>
                            <button
                                v-if="rec.rationale_source !== 'llm'"
                                type="button"
                                @click="explain(rec.id)"
                                class="mt-3 inline-flex items-center gap-1.5 rounded-md border border-indigo-200 bg-white px-2.5 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-100 dark:border-gray-600 dark:bg-gray-800 dark:text-indigo-300 dark:hover:bg-gray-700"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                {{ generatingId === rec.id ? 'Generating…' : 'Explain with AI' }}
                            </button>
                        </div>

                        <div v-if="rec.matched_skills.length" class="mt-4">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">You match</p>
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                <span v-for="skill in rec.matched_skills" :key="skill" class="rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 px-2.5 py-0.5 text-xs">
                                    {{ skill }}
                                </span>
                            </div>
                        </div>

                        <div v-if="rec.missing_skills.length" class="mt-3">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Growth areas</p>
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                <span v-for="skill in rec.missing_skills" :key="skill" class="rounded-full bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 px-2.5 py-0.5 text-xs">
                                    {{ skill }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-3 text-sm">
                            <Link :href="route('careers.show', rec.role_id)" class="font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                View role →
                            </Link>
                            <Link :href="route('roadmap.index')" class="font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                Build roadmap
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>