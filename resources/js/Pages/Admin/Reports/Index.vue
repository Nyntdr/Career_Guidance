<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    inDemandSkills: Array,
    onboardingStats: Object,
    roleSkillsCount: Array,
});
</script>

<template>
    <Head title="Reports" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Reports</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">System analytics and insights</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Onboarding Funnel -->
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                    <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-gray-100">Onboarding Funnel</h3>
                    <div class="grid gap-6 sm:grid-cols-5">
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ onboardingStats.total_candidates }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Candidates</p>
                        </div>
                        <div class="flex items-center">
                            <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-2 rounded-full bg-indigo-500" :style="{ width: onboardingStats.profile_rate + '%' }"></div>
                            </div>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ onboardingStats.has_profile }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Has Profile ({{ onboardingStats.profile_rate }}%)</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ onboardingStats.has_education }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Added Education</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ onboardingStats.has_experience }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Added Experience</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ onboardingStats.has_skills }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Added Skills</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-8 lg:grid-cols-2">
                    <!-- In-Demand Skills -->
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                        <div class="border-b border-gray-200 dark:border-gray-700 px-5 py-4">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Top In-Demand Skills</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Skills required by the most career roles</p>
                        </div>
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div v-for="(skill, i) in inDemandSkills" :key="skill.id" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium text-gray-400 w-6">{{ i + 1 }}.</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ skill.name }}</span>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300 px-2.5 py-0.5 text-xs font-medium">{{ skill.role_count }} roles</span>
                            </div>
                            <div v-if="!inDemandSkills.length" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No skill data yet.</div>
                        </div>
                    </div>

                    <!-- Career Roles by Skill Count -->
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                        <div class="border-b border-gray-200 dark:border-gray-700 px-5 py-4">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Career Roles by Skill Count</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Roles with the most skill requirements</p>
                        </div>
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div v-for="role in roleSkillsCount" :key="role.id" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ role.title }}</span>
                                <span class="inline-flex items-center rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300 px-2.5 py-0.5 text-xs font-medium">{{ role.skills_count }} skills</span>
                            </div>
                            <div v-if="!roleSkillsCount.length" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No role data yet.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
