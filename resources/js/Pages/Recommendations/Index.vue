<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AiLoadingOverlay from '@/Components/AiLoadingOverlay.vue';
import SpinnerIcon from '@/Components/SpinnerIcon.vue';
import { useAsyncStatus } from '@/composables/useAsyncStatus';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    recommendations: Array,
    hasAssessmentMap: {
        type: Object,
        default: () => ({}),
    },
    llmEnabled: Boolean,
    stats: Object,
});

const generatingId = ref(null);
const regenerating = ref(false);

const autoGenerating = ref(false);
const autoGenerateRan = ref(false);

const asyncStatus = useAsyncStatus();

onMounted(() => {
    if (props.recommendations.length || props.stats.skillsAssessed <= 0) return;
    if (autoGenerateRan.value) return;

    autoGenerateRan.value = true;
    autoGenerating.value = true;

    router.post(route('recommendations.generate'), {}, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            autoGenerating.value = false;
        },
    });
});

function scoreColor(score) {
    if (score >= 70) return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
    if (score >= 40) return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
    return 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
}

function regenerate() {
    if (regenerating.value || generatingId.value !== null || autoGenerating.value) return;
    regenerating.value = true;
    asyncStatus.run(
        () => new Promise((resolve) => {
            router.post(route('recommendations.generate'), {}, {
                preserveScroll: true,
                preserveState: true,
                onFinish: () => {
                    regenerating.value = false;
                    resolve();
                },
            });
        }),
        {
            label: 'Rebuilding your recommendations',
            messages: [
                'Ranking roles against your skills…',
                'Scoring skill overlap…',
                'Adding a career-path bonus…',
                'Almost there…',
            ],
        }
    );
}

function explain(rec) {
    const recId = typeof rec === 'object' ? rec.id : rec;
    const recRole = typeof rec === 'object' ? rec.title : '';

    if (generatingId.value !== null || regenerating.value) return;
    generatingId.value = recId;

    asyncStatus.run(
        () => new Promise((resolve) => {
            router.post(route('recommendations.rationale', recId), {}, {
                preserveScroll: true,
                preserveState: true,
                onFinish: () => {
                    generatingId.value = null;
                    resolve();
                },
            });
        }),
        {
            label: 'Generating your explanation',
            role: recRole,
            messages: [
                'Sending your profile to the AI…',
                'Reading your skills against {role}…',
                'Writing your explanation…',
                'Almost there — polishing the wording…',
                'Taking longer than usual. This can take up to a minute.',
            ],
        }
    );
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
                    :disabled="regenerating || generatingId !== null"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60 transition-colors"
                >
                    <SpinnerIcon v-if="regenerating" size="h-4 w-4" />
                    <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ regenerating ? 'Regenerating…' : 'Regenerate' }}
                </button>
            </div>
        </template>

        <div class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-4" :class="{ 'opacity-60 transition-opacity': regenerating }">
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

                <div v-if="stats.skillsAssessed > 0" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-indigo-50/60 dark:bg-gray-800 p-4">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            <p class="font-medium text-gray-800 dark:text-gray-100">How we match you</p>
                            <p class="mt-1">
                                Scores blend your skill overlap with each role (70% essential / 30% preferred weighting)
                                plus a semantic similarity pass and a small career-path bonus — all deterministic, not a black box.
                                <span v-if="llmEnabled">"Explain with AI" adds a friendly summary generated by an LLM for that role.</span>
                                <span v-else>Each card's explanation is generated from your matched skills, growth areas, and the role's outlook.</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="stats.skillsAssessed === 0" class="grid gap-4 sm:grid-cols-4">
                    <div v-for="n in 4" :key="n" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <div class="h-3 w-24 rounded bg-gray-200 dark:bg-gray-700 animate-pulse" />
                        <div class="mt-2 h-8 w-16 rounded bg-gray-200 dark:bg-gray-700 animate-pulse" />
                    </div>
                </div>

                <div v-if="stats.skillsAssessed === 0" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add skills to unlock personalized recommendations</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">We rank career roles by your current skill set and career goals.</p>
                    <Link :href="route('profile.edit')" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                        Update my profile
                    </Link>
                    <Link :href="route('resume.index')" class="mt-3 inline-flex items-center gap-2 rounded-lg border border-indigo-200 dark:border-indigo-800 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors">
                        Upload a resume instead
                    </Link>
                </div>

                <div v-else-if="!recommendations.length" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div v-for="n in 3" :key="n" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-3">
                                    <div class="h-4 w-32 rounded bg-gray-200 dark:bg-gray-700 animate-pulse" />
                                    <div class="h-3 w-24 rounded bg-gray-200 dark:bg-gray-700 animate-pulse" />
                                </div>
                                <div class="h-7 w-14 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse" />
                            </div>
                            <div class="mt-4 space-y-2">
                                <div class="h-3 w-full rounded bg-gray-200 dark:bg-gray-700 animate-pulse" />
                                <div class="h-3 w-3/4 rounded bg-gray-200 dark:bg-gray-700 animate-pulse" />
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-3 py-4">
                        <svg class="h-5 w-5 animate-spin text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Analyzing your skills against available career roles…</p>
                    </div>
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
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p>{{ rec.rationale }}</p>
                                <span
                                    :class="rec.rationale_source === 'llm'
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                                        : 'bg-gray-100 text-gray-600 dark:bg-gray-600/40 dark:text-gray-300'"
                                    class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                >
                                    {{ rec.rationale_source === 'llm' ? 'AI-generated' : 'Standard explanation' }}
                                </span>
                            </div>
                            <button
                                v-if="llmEnabled && rec.rationale_source !== 'llm'"
                                type="button"
                                @click="explain(rec)"
                                :disabled="generatingId !== null || regenerating"
                                class="mt-3 inline-flex items-center gap-1.5 rounded-md border border-indigo-200 bg-white px-2.5 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-indigo-300 dark:hover:bg-gray-700"
                            >
                                <SpinnerIcon v-if="generatingId === rec.id" size="h-3.5 w-3.5" />
                                <svg v-else class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <Link :href="route('roadmap.index', { role: rec.role_id })" class="font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                {{ hasAssessmentMap?.[rec.role_id] ? 'View roadmap' : 'Build roadmap' }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <AiLoadingOverlay
            :visible="asyncStatus.visible.value"
            :messages="asyncStatus.messages.value"
            :label="asyncStatus.label.value"
            :role="asyncStatus.role.value"
            @dismiss="asyncStatus.dismiss"
        />
    </AuthenticatedLayout>
</template>