<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    skills: Array,
    categories: Array,
    skillCount: Number,
});

const search = ref('');
const activeCategory = ref('');

const groupedSkills = computed(() => {
    const map = {};
    const q = search.value.trim().toLowerCase();

    for (const cat of props.categories) {
        map[cat.name] = [];
    }

    for (const s of props.skills) {
        if (!q || s.name.toLowerCase().includes(q)) {
            const catName = s.category?.name ?? 'Uncategorized';
            if (!map[catName]) map[catName] = [];
            map[catName].push(s);
        }
    }

    return Object.fromEntries(
        Object.entries(map)
            .filter(([cat, list]) =>
                !activeCategory.value || cat === activeCategory.value
            )
            .filter(([, list]) => list.length),
    );
});

const visibleCount = computed(() => Object.values(groupedSkills.value).flat().length);
</script>

<template>
    <Head title="Skills" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    Skills
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ visibleCount }} of {{ skillCount }} skills, grouped by category
                </p>
            </div>
        </template>

        <div class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <TextInput v-model="search" type="search" placeholder="Search skills..." class="w-full max-w-md" />

                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="activeCategory = ''"
                            class="rounded-full px-3 py-1.5 text-xs font-medium transition-colors"
                            :class="activeCategory === '' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        >
                            All
                        </button>
                        <button
                            v-for="cat in categories"
                            :key="cat.id"
                            @click="activeCategory = activeCategory === cat.name ? '' : cat.name"
                            class="rounded-full px-3 py-1.5 text-xs font-medium transition-colors"
                            :class="activeCategory === cat.name ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        >
                            {{ cat.name }}
                        </button>
                    </div>
                </div>

                <div v-if="Object.keys(groupedSkills).length" class="space-y-8">
                    <div v-for="(list, cat) in groupedSkills" :key="cat">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">
                            {{ cat }}
                            <span class="ml-2 text-xs font-normal text-gray-400">{{ list.length }}</span>
                        </h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="skill in list"
                                :key="skill.id"
                                class="rounded-xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 shadow-sm"
                            >
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ skill.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No skills match your search.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>