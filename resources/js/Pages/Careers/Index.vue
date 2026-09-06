<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { formatMoney } from '@/lib/format';

const settings = usePage().props.settings ?? {};

const props = defineProps({
    roles: Object,
    filters: Object,
});

const search = ref(props.filters.search ?? '');

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

watch(search, (val) => {
    router.get(route('careers.index'), { search: val || '' }, { preserveState: true, replace: true });
});

function formatSalary(range) {
    if (!range) return '—';
    const fmt = (n) => (n ? formatMoney(n, settings.currency_symbol, settings.currency_position) : '—');
    return `${fmt(range.min)}${range.max ? ` – ${fmt(range.max)}` : ''}`;
}
</script>

<template>
    <Head title="Careers" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    Careers
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Explore career roles and the skills they require
                </p>
            </div>
        </template>

        <div class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <TextInput v-model="search" type="search" placeholder="Search careers..." class="w-full max-w-md" />
                </div>

                <div v-if="roles.data.length" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="role in roles.data"
                        :key="role.id"
                        :href="route('careers.show', role.id)"
                        class="group rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm transition-all hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-700"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                {{ role.title }}
                            </h3>
                            <span
                                class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="seniorityColors[role.typical_seniority] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                            >
                                {{ role.typical_seniority }}
                            </span>
                        </div>

                        <p class="mt-2 line-clamp-3 text-sm text-gray-500 dark:text-gray-400">
                            {{ role.description }}
                        </p>

                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Salary range</span>
                                <span class="font-medium text-gray-800 dark:text-gray-100">{{ formatSalary(role.avg_salary_range) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Skills required</span>
                                <span class="font-medium text-gray-800 dark:text-gray-100">{{ role.skills_count }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Growth outlook</span>
                                <span
                                    class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="growthColors[role.growth_outlook] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                                >
                                    {{ role.growth_outlook }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-else class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No career roles found.</p>
                </div>

                <div v-if="roles.links && roles.links.length > 3" class="mt-6 flex items-center justify-between">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Showing {{ roles.from }} to {{ roles.to }} of {{ roles.total }} results</p>
                    <div class="flex gap-1">
                        <Link v-for="link in roles.links" :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="{ 'bg-indigo-600 text-white': link.active, 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700': !link.active, 'opacity-50 cursor-not-allowed': !link.url }"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>