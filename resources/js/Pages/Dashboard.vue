<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import OnboardingWizard from './Dashboard/Partials/OnboardingWizard.vue';
import { Head, usePage } from '@inertiajs/vue3';
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

const priorityColors = {
    High: 'bg-red-100 text-red-700',
    Medium: 'bg-amber-100 text-amber-700',
    Low: 'bg-emerald-100 text-emerald-700',
};

const closeOnboarding = () => {
    showOnboarding.value = false;
};
</script>

<template>
    <Head title="Home Dashboard" />

    <AuthenticatedLayout>
        <Modal :show="showOnboarding" max-width="2xl" :closeable="false">
            <OnboardingWizard @complete="closeOnboarding" />
        </Modal>

        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Welcome back, {{ userName }} — here's your career guidance overview
                </p>
            </div>
        </template>

        <div class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 py-8">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Stats -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600">Career pathssss</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.careerPaths }}+</p>
                        <p class="mt-1 text-xs text-gray-500">Matched to your profile</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600">Avg. compatibility</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.avgCompatibility }}%</p>
                        <p class="mt-1 text-xs text-gray-500">Across top matches</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600">Skills assessed</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.skillsAssessed }}</p>
                        <p class="mt-1 text-xs text-gray-500">From your profile</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-indigo-600">Roadmap steps</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.roadmapSteps }}</p>
                        <p class="mt-1 text-xs text-gray-500">In your action plan</p>
                    </div>
                </div>

                <div class="grid gap-8 lg:grid-cols-3">
                    <!-- Top Match -->
                    <div class="lg:col-span-2">
                        <div class="overflow-hidden rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-600 to-violet-700 p-6 text-white shadow-lg">
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

                        <!-- Career Matches -->
                        <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900">All career matches</h3>
                            <p class="mt-1 text-sm text-gray-500">Ranked by AI compatibility score</p>

                            <div class="mt-5 space-y-4">
                                <div
                                    v-for="match in careerMatches"
                                    :key="match.title"
                                    class="flex items-center gap-4 rounded-xl border border-gray-100 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/50"
                                >
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-sm font-bold text-indigo-700"
                                    >
                                        {{ match.score }}%
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-gray-900">{{ match.title }}</p>
                                        <p class="text-sm text-gray-500">{{ match.category }}</p>
                                    </div>
                                    <div class="hidden w-32 sm:block">
                                        <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                            <div
                                                class="h-full rounded-full bg-gradient-to-r from-indigo-400 to-violet-500"
                                                :style="{ width: `${match.score}%` }"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Profile Summary -->
                        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900">Your profile</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Education</p>
                                    <p class="mt-1 text-sm text-gray-700">{{ profileSummary.education }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Interests</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span
                                            v-for="interest in profileSummary.interests"
                                            :key="interest"
                                            class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700"
                                        >
                                            {{ interest }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Career goal</p>
                                    <p class="mt-1 text-sm text-gray-700">{{ profileSummary.goals }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Skills to Develop -->
                        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900">Skills to develop</h3>
                            <p class="mt-1 text-sm text-gray-500">Priority gaps for your top match</p>

                            <div class="mt-4 space-y-4">
                                <div
                                    v-for="skill in skillsToDevelop"
                                    :key="skill.name"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-800">{{ skill.name }}</span>
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="priorityColors[skill.priority]"
                                        >
                                            {{ skill.priority }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100">
                                            <div
                                                class="h-full rounded-full bg-indigo-500"
                                                :style="{ width: `${skill.current}%` }"
                                            />
                                        </div>
                                        <span class="text-xs text-gray-400">{{ skill.current }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900">Recent activity</h3>
                            <ul class="mt-4 space-y-3">
                                <li
                                    v-for="(item, index) in recentActivity"
                                    :key="index"
                                    class="flex gap-3 text-sm"
                                >
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-indigo-400" />
                                    <div>
                                        <p class="text-gray-700">{{ item.action }}</p>
                                        <p class="text-xs text-gray-400">{{ item.date }}</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
