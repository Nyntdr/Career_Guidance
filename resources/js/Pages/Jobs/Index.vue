<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    jobs: Array,
    stats: Object,
});

function scoreColor(score) {
    if (score >= 70) return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
    if (score >= 40) return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
    return 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
}
</script>

<template>
    <Head title="Recommended Jobs" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    Recommended Jobs
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Jobs matched to your skills — {{ stats.skillsAssessed }} skills assessed, {{ stats.totalJobs }} postings open
                </p>
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
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Job postings</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.totalJobs }}</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Matched jobs</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.matchedJobs }}</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Best match</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.topScore }}%</p>
                    </div>
                </div>

                <div v-if="stats.skillsAssessed === 0" class="grid gap-4 sm:grid-cols-4">
                    <div v-for="n in 4" :key="n" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <div class="h-3 w-24 rounded bg-gray-200 dark:bg-gray-700 animate-pulse" />
                        <div class="mt-2 h-8 w-16 rounded bg-gray-200 dark:bg-gray-700 animate-pulse" />
                    </div>
                </div>

                <div v-if="stats.skillsAssessed === 0" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add skills to your profile to get job matches</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Head to your profile to assess your current skills and unlock personalized job recommendations.</p>
                    <Link :href="route('profile.edit')" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                        Update my profile
                    </Link>
                    <Link :href="route('resume.index')" class="mt-3 inline-flex items-center gap-2 rounded-lg border border-indigo-200 dark:border-indigo-800 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors">
                        Upload a resume instead
                    </Link>
                </div>

                <div v-else-if="jobs.length === 0" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No job matches found yet. New postings appear here as they come in.</p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="job in jobs"
                        :key="job.id"
                        class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ job.title }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ job.company }}
                                    <span v-if="job.location"> · {{ job.location }}</span>
                                </p>
                            </div>
                            <span
                                class="shrink-0 rounded-full px-3 py-1 text-sm font-bold"
                                :class="scoreColor(job.score)"
                            >
                                {{ job.score }}% match
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span v-if="job.seniority_level" class="rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 px-2.5 py-0.5">{{ job.seniority_level }}</span>
                            <span v-if="job.posted_at" class="rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 px-2.5 py-0.5">Posted {{ job.posted_at }}</span>
                        </div>

                        <div v-if="job.matched_skills.length" class="mt-4">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">You match</p>
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                <span v-for="skill in job.matched_skills" :key="skill" class="rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 px-2.5 py-0.5 text-xs">
                                    {{ skill }}
                                </span>
                            </div>
                        </div>

                        <div v-if="job.missing_skills.length" class="mt-3">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Gaps to address</p>
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                <span v-for="skill in job.missing_skills" :key="skill" class="rounded-full bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 px-2.5 py-0.5 text-xs">
                                    {{ skill }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>