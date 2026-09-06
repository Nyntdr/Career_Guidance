<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    scan: Object,
    keywords: Array,
    details: Array,
});

const categoryLabels = {
    keyword: 'Keywords',
    structure: 'Structure',
    readability: 'Readability',
    formatting: 'Formatting',
};

const severityColors = {
    low: 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
    medium: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    high: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
};

const gaugeColor = (score) => {
    if (score >= 80) return 'bg-emerald-500';
    if (score >= 60) return 'bg-amber-500';
    return 'bg-red-500';
};

const ringColor = (score) => {
    if (score >= 80) return '#10b981';
    if (score >= 60) return '#f59e0b';
    return '#ef4444';
};

const summary = (score) => {
    if (score >= 80) return 'Excellent match — your resume is strong for this role.';
    if (score >= 60) return 'Good fit — a few tweaks will push you higher.';
    if (score >= 40) return 'Average — significant keyword and structure gaps remain.';
    return 'Weak match — review the suggestions below before applying.';
};
</script>

<template>
    <Head title="ATS Scan Results" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    ATS Scan Results
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Resume v{{ scan.resume.version }} vs
                    <Link :href="route('jobs.index')" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                        {{ scan.job.title }}
                    </Link>
                    at {{ scan.job.company }}
                </p>
            </div>
        </template>

        <div class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Overall score -->
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-8 text-center shadow-sm">
                    <p class="text-sm font-medium uppercase tracking-wider text-gray-400">Overall match</p>
                    <div class="relative mx-auto mt-4 h-40 w-40">
                        <div
                            class="flex h-40 w-40 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700"
                            :style="{ background: `conic-gradient(${ringColor(scan.overall_score)} ${scan.overall_score}%, transparent 0)` }"
                        >
                            <div class="flex h-32 w-32 items-center justify-center rounded-full bg-white dark:bg-gray-800">
                                <span class="text-4xl font-bold" :class="scan.overall_score >= 80 ? 'text-emerald-500' : scan.overall_score >= 60 ? 'text-amber-500' : 'text-red-500'">
                                    {{ scan.overall_score }}%
                                </span>
                            </div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">{{ summary(scan.overall_score) }}</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Scanned on {{ scan.created_at }}</p>
                </div>

                <!-- Component scores -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="component in [
                        { label: 'Keyword match', value: scan.keyword_match_score, hint: '50% weight' },
                        { label: 'Structure', value: scan.structure_score, hint: '15% weight' },
                        { label: 'Formatting', value: scan.formatting_score, hint: '15% weight' },
                        { label: 'Readability', value: scan.readability_score, hint: '20% weight' },
                    ]" :key="component.label" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <div class="flex items-baseline justify-between">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ component.label }}</p>
                            <span class="text-2xl font-bold" :class="component.value >= 80 ? 'text-emerald-500' : component.value >= 60 ? 'text-amber-500' : 'text-red-500'">
                                {{ component.value }}%
                            </span>
                        </div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-full rounded-full transition-all" :class="gaugeColor(component.value)" :style="{ width: `${component.value}%` }" />
                        </div>
                        <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ component.hint }}</p>
                    </div>
                </div>

                <!-- Keywords -->
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Keyword analysis</h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Skill keywords required by the job, with mention frequency in your resume.
                    </p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="kw in keywords"
                            :key="kw.keyword"
                            class="rounded-xl border p-4"
                            :class="kw.matched
                                ? 'border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20'
                                : 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20'"
                        >
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ kw.keyword }}</p>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="kw.matched
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                                        : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'"
                                >
                                    {{ kw.matched ? 'Found' : 'Missing' }}
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Resume ×{{ kw.frequency_in_resume }} · Job description ×{{ kw.frequency_in_jd }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Job required/preferred -->
                <div v-if="scan.job.required_skills" class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Required skills</h3>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span
                                v-for="skill in scan.job.required_skills.required || []"
                                :key="skill"
                                class="rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300"
                            >
                                {{ skill }}
                            </span>
                            <span v-if="!(scan.job.required_skills.required || []).length" class="text-xs text-gray-400">None listed</span>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Preferred skills</h3>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span
                                v-for="skill in scan.job.required_skills.preferred || []"
                                :key="skill"
                                class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300"
                            >
                                {{ skill }}
                            </span>
                            <span v-if="!(scan.job.required_skills.preferred || []).length" class="text-xs text-gray-400">None listed</span>
                        </div>
                    </div>
                </div>

                <!-- Suggestions -->
                <div v-if="details.length" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">How to improve</h3>
                    <div class="mt-4 space-y-3">
                        <div
                            v-for="(detail, index) in details"
                            :key="index"
                            class="flex items-start gap-3 rounded-xl border border-gray-200 dark:border-gray-700 p-4"
                        >
                            <span class="mt-0.5 rounded-full px-2.5 py-0.5 text-xs font-medium" :class="severityColors[detail.severity] || 'bg-gray-100 text-gray-600'">
                                {{ detail.severity }}
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ detail.detail }}</p>
                                <p v-if="detail.suggestion" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ categoryLabels[detail.category] || detail.category }}: {{ detail.suggestion }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>