<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale, PointElement, LineElement,
    BarElement, ArcElement, Title, Tooltip, Legend, Filler,
} from 'chart.js';
import { Line, Doughnut, Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    stats: Object,
    recentUsers: Array,
    skillCategoryCounts: Array,
    userRegistrationsChart: Object,
    roleDistributionChart: Object,
});

const lineChartData = computed(() => ({
    labels: props.userRegistrationsChart?.labels ?? [],
    datasets: [{
        label: 'New Users',
        backgroundColor: 'rgba(99, 102, 241, 0.1)',
        borderColor: 'rgb(99, 102, 241)',
        pointBackgroundColor: 'rgb(99, 102, 241)',
        pointBorderColor: '#fff',
        fill: true,
        tension: 0.4,
        data: props.userRegistrationsChart?.data ?? [],
    }],
}));

const lineChartOptions = {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false }, ticks: { maxTicksLimit: 6 } },
        y: { beginAtZero: true, ticks: { precision: 0 } },
    },
};

const doughnutData = computed(() => ({
    labels: props.roleDistributionChart?.labels ?? [],
    datasets: [{
        data: props.roleDistributionChart?.data ?? [],
        backgroundColor: ['rgb(34, 197, 94)', 'rgb(59, 130, 246)', 'rgb(168, 85, 247)'],
        borderWidth: 0,
    }],
}));

const doughnutOptions = {
    responsive: true,
    plugins: {
        legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } },
    },
    cutout: '65%',
};

const barData = computed(() => ({
    labels: props.skillCategoryCounts.map(c => c.name),
    datasets: [{
        label: 'Skills',
        backgroundColor: 'rgba(99, 102, 241, 0.7)',
        borderColor: 'rgb(99, 102, 241)',
        borderWidth: 1,
        data: props.skillCategoryCounts.map(c => c.skills_count),
    }],
}));

const barOptions = {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, ticks: { precision: 0 } },
    },
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Admin Dashboard</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">System overview and management</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Stat Cards -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link :href="route('admin.users.index')" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Total Users</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.totalUsers }}</p>
                    </Link>
                    <Link :href="route('admin.users.index', { role: 'admin' })" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Admins</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.totalAdmins }}</p>
                    </Link>
                    <Link :href="route('admin.career-roles.index')" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Career Roles</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.totalCareerRoles }}</p>
                    </Link>
                    <Link :href="route('admin.skills.index')" class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all">
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Skills</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.totalSkills }}</p>
                    </Link>
                </div>

                <!-- Charts -->
                <div class="grid gap-8 lg:grid-cols-3">
                    <div class="lg:col-span-2 rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-gray-100">User Registrations (12 months)</h3>
                        <Line v-if="lineChartData.labels.length" :data="lineChartData" :options="lineChartOptions" class="max-h-64" />
                        <p v-else class="text-sm text-gray-400 py-8 text-center">No registration data yet</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-gray-100">User Roles</h3>
                        <Doughnut v-if="doughnutData.labels.length" :data="doughnutData" :options="doughnutOptions" class="max-h-56 mx-auto" />
                        <p v-else class="text-sm text-gray-400 py-8 text-center">No data</p>
                    </div>
                </div>

                <!-- Skills Bar -->
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                    <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-gray-100">Skills by Category</h3>
                    <Bar v-if="barData.labels.length" :data="barData" :options="barOptions" class="max-h-64" />
                    <p v-else class="text-sm text-gray-400 py-8 text-center">No skills data yet</p>
                </div>

                <!-- Quick Actions -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-100">Quick Actions</h3>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <Link :href="route('admin.users.index')" class="flex items-center gap-4 rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all group">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Add User</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Create a new user account</p>
                            </div>
                        </Link>
                        <Link :href="route('admin.skills.index')" class="flex items-center gap-4 rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all group">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 group-hover:scale-110 transition-transform">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Add Skill</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Add a new skill to the library</p>
                            </div>
                        </Link>
                        <Link :href="route('admin.career-roles.index')" class="flex items-center gap-4 rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all group">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Add Career Role</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Define a new role with skill reqs</p>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Recent Users + Skills by Category -->
                <div class="grid gap-8 lg:grid-cols-3">
                    <!-- Recent Users -->
                    <div class="lg:col-span-2 rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-5 py-4">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Recent Users</h3>
                            <Link :href="route('admin.users.index')" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">View all</Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800/50">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Role</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Joined</th>
                                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="user in recentUsers" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="whitespace-nowrap px-5 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</td>
                                        <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-600 dark:text-gray-400">{{ user.email }}</td>
                                        <td class="whitespace-nowrap px-5 py-3">
                                            <span :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': user.role === 'user',
                                                'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': user.role === 'admin',
                                            }" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">
                                                {{ user.role }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-600 dark:text-gray-400">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                                        <td class="whitespace-nowrap px-5 py-3 text-right text-sm">
                                            <Link :href="route('admin.users.profile', user.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Profile</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Skills by Category -->
                    <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                        <div class="border-b border-gray-200 dark:border-gray-700 px-5 py-4">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Skill Counts</h3>
                        </div>
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div v-for="cat in skillCategoryCounts" :key="cat.id" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <span class="text-sm text-gray-800 dark:text-gray-200">{{ cat.name }}</span>
                                <span class="inline-flex items-center rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300 px-2.5 py-0.5 text-xs font-medium">{{ cat.skills_count }}</span>
                            </div>
                            <div v-if="!skillCategoryCounts.length" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No skills found.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
