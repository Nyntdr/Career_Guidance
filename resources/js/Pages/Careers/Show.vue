<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { formatMoney } from '@/lib/format';

const settings = usePage().props.settings ?? {};

const props = defineProps({
    role: Object,
    skillsByImportance: Object,
    skillCount: Number,
    relatedRoles: Array,
    outgoingPaths: Array,
    incomingPaths: Array,
});

const growthColors = {
    Growing: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    Stable: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    Declining: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
};

const seniorityColors = {
    Entry: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    Mid: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    Senior: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
    Lead: 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
};

function formatSalary(range) {
    if (!range) return '—';
    const fmt = (n) => (n ? formatMoney(n, settings.currency_symbol, settings.currency_position) : '—');
    return `${fmt(range.min)}${range.max ? ` – ${fmt(range.max)}` : ''}`;
}

function proficiencyLabel(level) {
    const labels = { 1: 'Beginner', 2: 'Basic', 3: 'Intermediate', 4: 'Advanced', 5: 'Expert' };
    return labels[level] ?? `${level} / 5`;
}
</script>

<template>
    <Head :title="role.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('careers.index')" class="rounded-lg p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Back to careers">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">{{ role.title }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Career role overview</p>
                </div>
            </div>
        </template>

        <div class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm lg:col-span-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="rounded-full px-3 py-1 text-xs font-medium"
                                :class="seniorityColors[role.typical_seniority] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                            >
                                {{ role.typical_seniority }} seniority
                            </span>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-medium"
                                :class="growthColors[role.growth_outlook] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                            >
                                {{ role.growth_outlook }} outlook
                            </span>
                        </div>

                        <p class="mt-4 text-gray-600 dark:text-gray-300">{{ role.description }}</p>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Typical salary range</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ formatSalary(role.avg_salary_range) }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Annual (approx.)</p>
                            </div>
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Skills required</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ skillCount }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Essential + preferred</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="relatedRoles.length" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Explore other careers</h3>
                        <ul class="mt-4 space-y-3">
                            <li v-for="r in relatedRoles" :key="r.id">
                                <Link :href="route('careers.show', r.id)" class="group flex items-center justify-between rounded-lg p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                        {{ r.title }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ r.skills_count }} skills</span>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div v-if="skillsByImportance.essential.length" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Essential skills</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Required to succeed in this role</p>
                        <ul class="mt-4 space-y-2">
                            <li v-for="skill in skillsByImportance.essential" :key="skill.id" class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3">
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ skill.name }}</span>
                                <span class="shrink-0 rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 px-2.5 py-0.5 text-xs font-medium">
                                    Min {{ proficiencyLabel(skill.min_proficiency) }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div v-if="skillsByImportance.preferred.length" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Preferred skills</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Valuable differentiators</p>
                        <ul class="mt-4 space-y-2">
                            <li v-for="skill in skillsByImportance.preferred" :key="skill.id" class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3">
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ skill.name }}</span>
                                <span class="shrink-0 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 px-2.5 py-0.5 text-xs font-medium">
                                    Min {{ proficiencyLabel(skill.min_proficiency) }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div v-if="outgoingPaths.length" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Pathways from this role</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Typical next steps after {{ role.title }}</p>
                        <template v-if="outgoingPaths.length">
                            <ul class="mt-4 space-y-3">
                                <li v-for="path in outgoingPaths" :key="path.id" class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <Link :href="route('careers.show', path.to_role_id)" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                            {{ path.to_role }}
                                        </Link>
                                        <span v-if="path.typical_years" class="shrink-0 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 px-2.5 py-0.5 text-xs font-medium">
                                            ~{{ path.typical_years }} yr{{ path.typical_years === 1 ? '' : 's' }}
                                        </span>
                                    </div>
                                    <p v-if="path.notes" class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ path.notes }}</p>
                                </li>
                            </ul>
                        </template>
                        <p v-else class="mt-4 text-sm text-gray-400 dark:text-gray-500">No defined pathways from this role yet.</p>
                    </div>

                    <div v-if="incomingPaths.length" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Roles leading to this role</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">How professionals typically reach {{ role.title }}</p>
                        <template v-if="incomingPaths.length">
                            <ul class="mt-4 space-y-3">
                                <li v-for="path in incomingPaths" :key="path.id" class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <Link :href="route('careers.show', path.from_role_id)" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                            {{ path.from_role }}
                                        </Link>
                                        <span v-if="path.typical_years" class="shrink-0 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 px-2.5 py-0.5 text-xs font-medium">
                                            ~{{ path.typical_years }} yr{{ path.typical_years === 1 ? '' : 's' }}
                                        </span>
                                    </div>
                                    <p v-if="path.notes" class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ path.notes }}</p>
                                </li>
                            </ul>
                        </template>
                        <p v-else class="mt-4 text-sm text-gray-400 dark:text-gray-500">No defined pathways into this role yet.</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-dashed border-indigo-200 dark:border-gray-700 bg-white/60 dark:bg-gray-800/60 p-6 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Assess your readiness for this role and build a learning roadmap.
                    </p>
                    <Link :href="route('assessment.run', { target_role_id: role.id })" class="mt-2 inline-block text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline" method="post" as="button">
                        Run a skill-gap assessment for this role
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>