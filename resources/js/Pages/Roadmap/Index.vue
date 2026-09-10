<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { formatMoney } from '@/lib/format';

const settings = usePage().props.settings ?? {};

const props = defineProps({
    items: Array,
    roles: Array,
    stats: Object,
    hasAssessment: Boolean,
    isStale: Boolean,
});

const runForm = useForm({
    target_role_id: '',
});

const priorityColors = {
    High: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    Medium: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    Low: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
};

const statusOptions = ['not_started', 'in_progress', 'completed'];
const statusLabels = {
    not_started: 'Not started',
    in_progress: 'In progress',
    completed: 'Completed',
};

function submitRun() {
    runForm.post(route('assessment.run'));
}

function setStatus(item, status) {
    if (!item.progress_id || item.status === status) return;
    router.patch(route('roadmap.progress.update', item.progress_id), { status });
}

function formatCost(cost) {
    const n = Number(cost);
    if (!cost || n === 0) return 'Free';
    return formatMoney(n, settings.currency_symbol, settings.currency_position);
}
</script>

<template>
    <Head title="Skill Development Roadmap" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    Skill Development Roadmap
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Persisted skill gaps, recommended resources, and your progress
                </p>
            </div>
        </template>

        <div class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Empty state: run an assessment -->
                <div v-if="!hasAssessment" class="mx-auto max-w-xl rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/40">
                        <svg class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">No assessment yet</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Run a skill-gap assessment against a target career role to build your personalized learning roadmap.
                    </p>

                    <form @submit.prevent="submitRun" class="mt-6 space-y-4 text-left">
                        <div>
                            <InputLabel for="target_role" value="Target career role" />
                            <select
                                id="target_role"
                                v-model="runForm.target_role_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Best match (based on your skills)</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.title }}</option>
                            </select>
                            <InputError :message="runForm.errors.target_role_id" class="mt-2" />
                        </div>
                        <PrimaryButton :disabled="runForm.processing">
                            Run assessment
                        </PrimaryButton>
                    </form>
                </div>

                <!-- Assessment present -->
                <template v-else>
                    <!-- Stale assessment notice -->
                    <div v-if="isStale" class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Your skills have changed since this assessment</p>
                                <p class="mt-0.5 text-xs text-amber-700 dark:text-amber-300">Re-run the assessment to refresh your gaps and recommendations.</p>
                            </div>
                        </div>
                        <button
                            @click="runForm.post(route('assessment.run'))"
                            class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-600 transition-colors"
                        >
                            Re-run now
                        </button>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Readiness</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.readiness }}%</p>
                            <p v-if="stats.targetRole" class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">for {{ stats.targetRole }}</p>
                        </div>
                        <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Open gaps</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.gapCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">In progress</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.inProgress }}</p>
                        </div>
                        <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Completed</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.completed }}</p>
                        </div>
                        <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm flex flex-col">
                            <p class="flex-1 text-sm font-medium text-indigo-600 dark:text-indigo-400">Re-run assessment</p>
                            <button
                                @click="runForm.post(route('assessment.run'))"
                                class="mt-2 inline-flex w-fit items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 transition-colors"
                            >
                                Run again
                            </button>
                        </div>
                    </div>

                    <div v-if="items.length" class="space-y-6">
                        <div
                            v-for="item in items"
                            :key="item.id"
                            class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm"
                            :class="{ 'opacity-60': item.status === 'completed' }"
                        >
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ item.skill }}</h3>
                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="priorityColors[item.priority] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'">
                                            {{ item.priority }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Level {{ item.current_level }} / {{ item.required_level }} required
                                    </p>
                                </div>

                                <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden">
                                    <button
                                        v-for="status in statusOptions"
                                        :key="status"
                                        @click="setStatus(item, status)"
                                        class="px-3 py-1.5 text-xs font-medium transition-colors"
                                        :class="item.status === status
                                            ? (status === 'completed' ? 'bg-emerald-600 text-white' : status === 'in_progress' ? 'bg-amber-500 text-white' : 'bg-gray-400 text-white')
                                            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                    >
                                        {{ statusLabels[status] }}
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                <div
                                    class="h-full rounded-full bg-indigo-500 transition-all"
                                    :style="{ width: `${Math.min((item.current_level / Math.max(item.required_level, 1)) * 100, 100)}%` }"
                                />
                            </div>

                            <div v-if="item.recommendations.length" class="mt-5">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Recommended resources</p>
                                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <a
                                        v-for="rec in item.recommendations"
                                        :key="rec.title"
                                        :href="rec.url || '#'"
                                        :target="rec.url ? '_blank' : undefined"
                                        :rel="rec.url ? 'noopener noreferrer' : undefined"
                                        class="group rounded-xl border border-gray-200 dark:border-gray-700 p-4 transition hover:border-indigo-300 dark:hover:border-indigo-700"
                                    >
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                            {{ rec.title }}
                                        </p>
                                        <p v-if="rec.provider" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ rec.provider }}</p>
                                        <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                            <span class="rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 px-2 py-0.5">{{ formatCost(rec.cost) }}</span>
                                            <span v-if="rec.duration_hours" class="ml-2">{{ rec.duration_hours }} hrs</span>
                                        </p>
                                    </a>
                                </div>
                            </div>

                            <p v-else class="mt-5 text-sm text-gray-400 dark:text-gray-500">
                                No learning resources match this skill yet.
                            </p>
                        </div>
                    </div>

                    <div v-else class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center shadow-sm">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No skill gaps found for your target. Great job!</p>
                        <Link :href="route('careers.index')" class="mt-3 inline-block text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                            Explore other careers
                        </Link>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>