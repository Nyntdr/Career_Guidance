<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import OnboardingWizard from './Dashboard/Partials/OnboardingWizard.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    stats: Object,
    topMatch: Object,
    careerMatches: Array,
    skillsToDevelop: Array,
    profileSummary: Object,
    recentActivity: Array,
});

const userName = computed(() => usePage().props.auth.user?.name ?? 'there');
const onboarding = computed(() => usePage().props.onboarding ?? false);
const showOnboarding = ref(onboarding.value);
const hasData = computed(() => props.stats?.skillsAssessed > 0 || props.stats?.careerPaths > 0);

function regenerate() {
    router.post(route('recommendations.generate'), {}, { preserveScroll: true });
}

const priorityColors = {
    High: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    Medium: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    Low: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
};

const closeOnboarding = () => {
    showOnboarding.value = false;
};

const interests = computed(() => {
    if (!props.profileSummary?.interests) return [];
    return Array.isArray(props.profileSummary.interests)
        ? props.profileSummary.interests.filter(i => i !== 'Not specified')
        : [];
});
</script>

<template>
    <Head title="Home Dashboard" />

    <AuthenticatedLayout>
        <Modal :show="showOnboarding" max-width="2xl" :closeable="false">
            <OnboardingWizard @complete="closeOnboarding" />
        </Modal>

        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    Dashboard
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Welcome back, {{ userName }} — here's your career guidance overview
                </p>
            </div>
        </template>

        <div class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Stats -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Career paths</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.careerPaths }}+</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Matched to your profile</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Avg. compatibility</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.avgCompatibility }}%</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Across top matches</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Skills assessed</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.skillsAssessed }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">From your profile</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Roadmap steps</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.roadmapSteps }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">In your action plan</p>
                    </div>
                </div>

                <!-- Empty State (no data at all) -->
                <div v-if="!hasData" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/40">
                        <svg class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Start building your profile</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Add your skills and career information to get personalized career matches and recommendations.
                    </p>
                    <div class="mt-6 flex items-center justify-center gap-3">
                        <Link :href="route('profile.edit')" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition-colors">
                            Go to Profile
                        </Link>
                        <button @click="showOnboarding = true" class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Start Onboarding
                        </button>
                    </div>
                </div>

                <div v-if="hasData" class="grid gap-8 lg:grid-cols-3">
                    <!-- Top Match -->
                    <div class="lg:col-span-2">
                        <div v-if="topMatch" class="overflow-hidden rounded-2xl border border-indigo-100 dark:border-indigo-800 bg-gradient-to-br from-indigo-600 to-violet-700 p-6 text-white shadow-lg">
                            <p class="text-sm font-medium text-indigo-200">Top career match</p>
                            <h3 class="mt-2 text-3xl font-bold">{{ topMatch.title }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-indigo-100">
                                {{ topMatch.description }}
                            </p>

                            <div class="mt-5">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-indigo-200">Compatibility</span>
                                    <span class="font-semibold">{{ topMatch.compatibility }}%</span>
                                </div>
                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-white/20">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-violet-300 to-indigo-200 transition-all"
                                        :style="{ width: `${topMatch.compatibility}%` }"
                                    />
                                </div>
                            </div>

                            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                <div class="rounded-xl bg-white/10 p-4">
                                    <p class="text-xs text-indigo-200">Salary range</p>
                                    <p class="mt-1 font-semibold">{{ topMatch.salaryRange }}</p>
                                </div>
                                <div class="rounded-xl bg-white/10 p-4">
                                    <p class="text-xs text-indigo-200">Job outlook</p>
                                    <p class="mt-1 font-semibold">{{ topMatch.growth }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="overflow-hidden rounded-2xl border border-dashed border-gray-300 dark:border-gray-600 p-8 text-center">
                            <p class="text-gray-500 dark:text-gray-400">Add more skills to see your top career match here.</p>
                            <Link :href="route('profile.edit')" class="mt-2 inline-block text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                Update your skills
                            </Link>
                        </div>

                        <!-- Career Matches -->
                        <div class="mt-6 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">All career matches</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ranked by skill compatibility score</p>
                                </div>
                                <div v-if="careerMatches?.length" class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="regenerate"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 dark:border-indigo-700 px-3 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Regenerate
                                    </button>
                                    <Link
                                        :href="route('recommendations.index')"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
                                    >
                                        View details
                                    </Link>
                                </div>
                            </div>

                            <div v-if="careerMatches?.length" class="mt-5 space-y-4">
                                <div
                                    v-for="match in careerMatches"
                                    :key="match.title"
                                    class="flex items-center gap-4 rounded-xl border border-gray-100 dark:border-gray-700 p-4 transition hover:border-indigo-200 dark:hover:border-indigo-700 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20"
                                >
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/60 text-sm font-bold text-indigo-700 dark:text-indigo-300"
                                    >
                                        {{ match.score }}%
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ match.title }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ match.category }}</p>
                                    </div>
                                    <div class="hidden w-32 sm:block">
                                        <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                            <div
                                                class="h-full rounded-full bg-gradient-to-r from-indigo-400 to-violet-500"
                                                :style="{ width: `${match.score}%` }"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="mt-5 py-6 text-center">
                                <p class="text-sm text-gray-400 dark:text-gray-500">No career matches yet. Add skills to get started.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Profile Summary -->
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Your profile</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Headline</p>
                                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ profileSummary?.goals ?? 'Not set' }}</p>
                                </div>
                                <div v-if="interests.length">
                                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Interests</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span
                                            v-for="interest in interests"
                                            :key="interest"
                                            class="rounded-full bg-indigo-50 dark:bg-indigo-900/40 px-3 py-1 text-xs font-medium text-indigo-700 dark:text-indigo-300"
                                        >
                                            {{ interest }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Education</p>
                                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ profileSummary?.education ?? 'Not specified' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Skills to Develop -->
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Skills to develop</h3>
                                <Link :href="route('roadmap.index')" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                    View roadmap
                                </Link>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Priority gaps for your top match</p>

                            <div v-if="skillsToDevelop?.length" class="mt-4 space-y-4">
                                <div
                                    v-for="skill in skillsToDevelop"
                                    :key="skill.name"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ skill.name }}</span>
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="priorityColors[skill.priority]"
                                        >
                                            {{ skill.priority }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                            <div
                                                class="h-full rounded-full bg-indigo-500"
                                                :style="{ width: `${Math.min(skill.current, 100)}%` }"
                                            />
                                        </div>
                                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ Math.round(skill.current) }}%</span>
                                    </div>
                                </div>
                            </div>

                            <p v-else class="mt-4 text-sm text-gray-400 dark:text-gray-500">
                                No skill gaps identified. Great job!
                            </p>
                        </div>

                        <!-- Recent Activity -->
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent activity</h3>
                            <ul v-if="recentActivity?.length" class="mt-4 space-y-3">
                                <li
                                    v-for="(item, index) in recentActivity"
                                    :key="index"
                                    class="flex gap-3 text-sm"
                                >
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-indigo-400" />
                                    <div>
                                        <p class="text-gray-700 dark:text-gray-300">{{ item.action }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ item.date }}</p>
                                    </div>
                                </li>
                            </ul>
                            <p v-else class="mt-4 text-sm text-gray-400 dark:text-gray-500">
                                No recent activity.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
