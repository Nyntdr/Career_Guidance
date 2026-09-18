<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    targetUser: Object,
});

const p = props.targetUser.profile;
</script>

<template>
    <Head :title="targetUser.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">{{ targetUser.name }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ targetUser.email }} &middot; <span class="capitalize">{{ targetUser.role }}</span></p>
                </div>
                <Link :href="route('admin.users.index')" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Back to Users</Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Career Profile -->
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-4">Career Profile</h3>
                    <div v-if="p" class="grid grid-cols-2 gap-4 text-sm">
                        <div><span class="text-gray-500 dark:text-gray-400">Headline:</span> <span class="text-gray-900 dark:text-gray-100">{{ p.headline ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Current Title:</span> <span class="text-gray-900 dark:text-gray-100">{{ p.current_title ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Years Experience:</span> <span class="text-gray-900 dark:text-gray-100">{{ p.years_experience ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Location:</span> <span class="text-gray-900 dark:text-gray-100">{{ p.location ?? '-' }}</span></div>
                        <div class="col-span-2"><span class="text-gray-500 dark:text-gray-400">Bio:</span> <span class="text-gray-900 dark:text-gray-100">{{ p.bio ?? '-' }}</span></div>
                    </div>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">No profile data.</p>
                </div>

                <!-- Skills -->
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-4">Skills ({{ p?.user_skills?.length ?? 0 }})</h3>
                    <div v-if="p?.user_skills?.length" class="flex flex-wrap gap-2">
                        <span v-for="us in p.user_skills" :key="us.id" class="inline-flex items-center gap-1 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-300 px-3 py-1 text-sm">
                            {{ us.skill?.name ?? 'Unknown' }}
                            <span class="text-xs opacity-70">({{ us.proficiency_level }}/5)</span>
                        </span>
                    </div>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">No skills added.</p>
                </div>

                <!-- Education -->
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-4">Education ({{ p?.education?.length ?? 0 }})</h3>
                    <div v-if="p?.education?.length" class="space-y-3">
                        <div v-for="edu in p.education" :key="edu.id" class="border-b border-gray-100 dark:border-gray-700 pb-3 last:border-0 last:pb-0">
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ edu.degree }} in {{ edu.field_of_study }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ edu.institution }} &middot; {{ edu.start_date }} - {{ edu.end_date ?? 'Present' }}</p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">No education entries.</p>
                </div>

                <!-- Work Experience -->
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-4">Work Experience ({{ p?.work_experience?.length ?? 0 }})</h3>
                    <div v-if="p?.work_experience?.length" class="space-y-3">
                        <div v-for="exp in p.work_experience" :key="exp.id" class="border-b border-gray-100 dark:border-gray-700 pb-3 last:border-0 last:pb-0">
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ exp.title }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ exp.company }} &middot; {{ exp.start_date }} - {{ exp.end_date ?? 'Present' }}</p>
                            <p v-if="exp.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ exp.description }}</p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">No work experience entries.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
